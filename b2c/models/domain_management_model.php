<?php
require_once 'abstract_management_model.php';

/**
 * @package    Current Domain Application
 * @subpackage Travel Portal
 * @author     Balu A
 * @version    V2
 */
class Domain_Management_Model extends Abstract_Management_Model
{
	private array $airline_markup = [];
	private array $hotel_markup = [];
	private array $car_markup = [];
	private bool $verify_balance;

	public function __construct()
	{
		parent::__construct('level_2');
		$this->verify_balance = (bool) $this->config->item('verify_domain_balance');
	}

	public function get_markup(string $module_name): array
	{
		return match ($module_name) {
			'flight' => $this->airline_markup(),
			'hotel'  => $this->hotel_markup(),
			'car'    => $this->car_markup(),
			default  => [],
		};
	}

	private function airline_markup(): array
	{
		if (empty($this->airline_markup)) {
			$this->airline_markup = [
				'specific_markup_list' => $this->specific_airline_markup('b2c_flight'),
				'generic_markup_list' => $this->generic_domain_markup('b2c_flight'),
			];
		}
		return $this->airline_markup;
	}

	private function hotel_markup(): array
	{
		if (empty($this->hotel_markup)) {
			$this->hotel_markup = [
				'specific_markup_list' => [],
				'generic_markup_list' => $this->generic_domain_markup('b2c_hotel'),
			];
		}
		return $this->hotel_markup;
	}

	private function car_markup(): array
	{
		if (empty($this->car_markup)) {
			$this->car_markup = [
				'specific_markup_list' => [],
				'generic_markup_list' => $this->generic_domain_markup('b2c_car'),
			];
		}
		return $this->car_markup;
	}

	private function generic_domain_markup(string $module_type): array
	{
		$domain_id = get_domain_auth_id();
		$sql = "SELECT ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type, ML.markup_currency
				FROM markup_list AS ML
				WHERE ML.value != '' AND ML.module_type = ? AND ML.markup_level = ? AND ML.type = 'generic' AND ML.domain_list_fk = ?";
		return $this->db->query($sql, [$module_type, $this->markup_level, $domain_id])->result_array();
	}

	private function specific_airline_markup(string $module_type): array
	{
		$domain_id = get_domain_auth_id();
		$sql = "SELECT AL.origin AS airline_origin, AL.name AS airline_name, AL.code AS airline_code,
					   ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type, ML.markup_currency
				FROM airline_list AS AL
				JOIN markup_list AS ML ON AL.origin = ML.reference_id
				WHERE ML.value != '' AND ML.module_type = ? AND ML.markup_level = ? AND ML.type = 'specific'
					  AND ML.domain_list_fk = ? ORDER BY AL.name ASC";

		$data = $this->db->query($sql, [$module_type, $this->markup_level, $domain_id])->result_array();
		$markup_list = [];
		if (!empty($data)) {
			foreach ($data as $row) {
				$markup_list[$row['airline_code']] = $row;
			}
		}
		return $markup_list;
	}

	public function verify_current_balance(float $amount, string $currency): string
	{
		if ($this->verify_balance && $amount > 0) {
			$sql = "SELECT DL.balance, CC.country as currency, CC.value as conversion_value
					FROM domain_list DL
					JOIN currency_converter CC ON CC.id = DL.currency_converter_fk
					WHERE DL.status = ? AND DL.origin = ? AND DL.domain_key = ?";
			$row = $this->db->query($sql, [ACTIVE, get_domain_auth_id(), get_domain_key()])->row_array();

			if ($currency === $row['currency'] && $row['balance'] >= $amount) {
				return SUCCESS_STATUS;
			}

			$this->application_logger->balance_status("Your Balance Is Very Low To Make Booking Of $amount $currency");
			return FAILURE_STATUS;
		}
		return SUCCESS_STATUS;
	}

	public function update_transaction_details(string $transaction_type, string $app_reference,float $fare, float $domain_markup, float $level_one_markup = 0, float $convinence = 0,float $discount = 0, string $currency = 'INR', float $conversion_rate = 1): string {
		$this->load->model('user_model');
		$currency = $currency ?: get_application_currency_preference();
		$amount = $fare + $level_one_markup + $convinence - $discount;
		$remarks = "$transaction_type Transaction was Successfully done";
		$notification_users = $this->user_model->get_admin_user_id();

		$action_query_string = [
			'app_reference' => $app_reference,
			'type' => $transaction_type,
			'module' => $this->config->item('current_module'),
		];

		if ($this->verify_balance) {
			echo 'We Dont Support This';
			exit;
		}
		$currency_data = [];
		$currency_data['currency'] = $currency;
		$currency_data['currency_rate'] = $conversion_rate;


		$this->save_transaction_details($transaction_type, $app_reference, $fare, $domain_markup, $level_one_markup, $convinence, $discount, $remarks, $currency_data);
		$this->application_logger->transaction_status("$remarks ($amount)", $action_query_string, $notification_users);
		return SUCCESS_STATUS;
	}

	private function save_transaction_details(string $transaction_type, string $app_reference,float $fare, float $domain_markup, float $level_one_markup, float $convinence, float $discount, string $remarks, array $currency_data = []): void {
		$currency = $currency ?: get_application_currency_preference();
		$gst = 0;
		$log = [
			'system_transaction_id' => date('Ymd-His') . '-S-' . random_int(1, 10000),
			'transaction_type' => $transaction_type,
			'domain_origin' => get_domain_auth_id(),
			'app_reference' => $app_reference,
			'fare' => $fare,
			'domain_markup' => $domain_markup,
			'level_one_markup' => $level_one_markup,
			'convinence_fees' => $convinence,
			'promocode_discount' => $discount,
			'remarks' => $remarks,
			'created_by_id' => intval($this->entity_user_id ?? 0),
			'currency' => $currency_data['currency'],
			'currency_conversion_rate' => $currency_data['currency_rate'],
			'gst' => $gst,
		];

		$this->custom_db->insert_record('transaction_log', $log);
	}
}
