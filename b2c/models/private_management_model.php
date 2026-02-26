<?php
require_once 'abstract_management_model.php';

class Private_Management_Model extends Abstract_Management_Model
{
	private array $airline_markup = [];
	private array $hotel_markup = [];
	private array $car_markup = [];

	public function __construct()
	{
		parent::__construct('level_1');
	}

	public function get_markup(string $module_name): array
	{
		return match ($module_name) {
			'flight' => $this->airline_markup(),
			'hotel'  => $this->hotel_markup(),
			'car'    => $this->car_markup(),
			default  => ['value' => 0, 'type' => ''],
		};
	}

	public function get_convinence_fees(string $module_name, int $search_id): array
	{
		return match ($module_name) {
			'flight' => $this->airline_convinence_fees($search_id),
			'hotel'  => $this->hotel_convinence_fees($search_id),
			'car'    => $this->car_convinence_fees(),
			default  => ['value' => 0, 'type' => '', 'per_pax' => true],
		};
	}

	private function airline_convinence_fees(int $search_id): ?array
	{
		$this->load->model('flight_model');
		$search_data = $this->flight_model->get_safe_search_data($search_id);
		$is_domestic = $search_data['data']['is_domestic'] ?? false;
		$module = $is_domestic ? 'domestic_flight' : 'international_flight';
		$query = "SELECT value, value_type AS type, per_pax, convenience_fee_currency FROM convenience_fees WHERE module = '{$module}'";
		$result = $this->db->query($query)->row_array();

		return valid_array($result) ? $result : null;
	}

	private function hotel_convinence_fees(int $search_id): ?array
	{
		$this->load->model('hotel_model');
		$search_data = $this->hotel_model->get_safe_search_data($search_id);
		$is_domestic = $search_data['data']['is_domestic'] ?? false;
		$module = $is_domestic ? 'domestic_hotel' : 'international_hotel';
		$query = "SELECT value, value_type AS type, per_pax FROM convenience_fees WHERE module = '{$module}'";
		$result = $this->db->query($query)->row_array();

		return valid_array($result) ? $result : null;
	}

	private function car_convinence_fees(): ?array
	{
		$query = 'SELECT value, value_type AS type, per_pax FROM convenience_fees WHERE module = "car"';
		$result = $this->db->query($query)->row_array();

		return valid_array($result) ? $result : null;
	}

	public function airline_markup(): array
	{
		$response = [];
		if (empty($this->airline_markup)) {
			$response['specific_markup_list'] = $this->specific_domain_markup('b2c_flight');
			if (!valid_array($response['specific_markup_list'])) {
				$response['generic_markup_list'] = $this->generic_domain_markup('b2c_flight');
			}
			$this->airline_markup = $response;
		}

		return $this->airline_markup;
	}

	public function hotel_markup(): array
	{
		$response = [];
		if (empty($this->hotel_markup)) {
			$response['specific_markup_list'] = $this->specific_domain_markup('b2c_hotel');
			if (!valid_array($response['specific_markup_list'])) {
				$response['generic_markup_list'] = $this->generic_domain_markup('b2c_hotel');
			}
			$this->hotel_markup = $response;
		}

		return $this->hotel_markup;
	}

	public function car_markup(): array
	{
		$response = [];
		if (empty($this->car_markup)) {
			$response['specific_markup_list'] = $this->specific_domain_markup('b2c_car');
			if (!valid_array($response['specific_markup_list'])) {
				$response['generic_markup_list'] = $this->generic_domain_markup('b2c_car');
			}
			$this->car_markup = $response;
		}

		return $this->car_markup;
	}

	public function generic_domain_markup(string $module_type): array
	{
		$query = <<<SQL
		SELECT ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type, ML.markup_currency
		FROM markup_list AS ML
		WHERE ML.value != "" AND ML.module_type = "{$module_type}" AND
		ML.markup_level = "{$this->markup_level}" AND ML.type = "generic" AND ML.domain_list_fk = 0
		SQL;

		return $this->db->query($query)->result_array();
	}

	public function specific_domain_markup(string $module_type): array
	{
		$domain_id = get_domain_auth_id();
		$query = <<<SQL
		SELECT ML.origin AS markup_origin, ML.value, ML.value_type, ML.markup_currency
		FROM domain_list AS DL
		JOIN markup_list AS ML ON DL.origin = ML.domain_list_fk
		WHERE ML.value != "" AND ML.module_type = "{$module_type}" AND
		ML.markup_level = "{$this->markup_level}" AND ML.type = "specific" AND
		ML.domain_list_fk = {$domain_id} AND ML.reference_id = {$domain_id}
		ORDER BY DL.created_datetime DESC
		SQL;

		return $this->db->query($query)->result_array();
	}

	public function update_domain_balance(int $domain_origin, float $amount): float
	{
		$cond = ['origin' => $domain_origin];
		$details = $this->custom_db->single_table_records('domain_list', 'balance', $cond);

		if (!empty($details['status']) && $details['status'] === true) {
			$current_balance = $details['data'][0]['balance'] + $amount;
			$details['data'][0]['balance'] = $current_balance;
			$this->custom_db->update_record('domain_list', $details['data'][0], $cond);
			return $current_balance;
		}

		return 0;
	}

	public function provab_xml_logger(string $operation_name, string $app_reference, string $module, $request, $response): void
	{
		$response = [];
		if (is_array($request)) {
			$request = json_encode($request);
		}
		if (is_array($response)) {
			$response = json_encode($response);
		}

		$data = [
			'operation_name' => $operation_name,
			'app_reference' => $app_reference,
			'module' => $module,
			'request' => $request,
			'response' => $response,
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
			'created_datetime' => date('Y-m-d H:i:s')
		];

		$this->custom_db->insert_record('provab_xml_logger', $data);
	}
}
