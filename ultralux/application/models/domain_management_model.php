<?php

declare(strict_types=1);

require_once 'abstract_management_model.php';

/**
 * @package    current domain Application
 * @subpackage Travel Portal
 * @author     Balu A
 * @version    V2
 */
class Domain_Management_Model extends Abstract_Management_Model
{
    private array $airline_markup = [];
    private array $hotel_markup = [];
    private array $car_markup = [];
    public bool $verify_domain_balance;

    public function __construct()
    {
        parent::__construct('level_4');
        $this->verify_domain_balance = (bool) $this->config->item('verify_domain_balance');
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

    public function get_agent_airline_markup_details(): array
    {
        if (empty($this->airline_markup)) {
            $this->airline_markup = [
                'specific_markup_list' => $this->specific_airline_markup_list('b2b_flight'),
                'generic_markup_list'  => $this->generic_domain_markup('b2b_flight'),
            ];
        }
        return $this->airline_markup;
    }

    public function airline_markup(): array
    {
        if (empty($this->airline_markup)) {
            $this->airline_markup = [
                'specific_markup_list' => $this->specific_airline_markup('b2b_flight'),
                'generic_markup_list'  => $this->generic_domain_markup('b2b_flight'),
            ];
        }
        return $this->airline_markup;
    }

    public function hotel_markup(): array
    {
        if (empty($this->hotel_markup)) {
            $this->hotel_markup = [
                'specific_markup_list' => '',
                'generic_markup_list'  => $this->generic_domain_markup('b2b_hotel'),
            ];
        }
        return $this->hotel_markup;
    }

    public function car_markup(): array
    {
        if (empty($this->car_markup)) {
            $this->car_markup = [
                'specific_markup_list' => '',
                'generic_markup_list'  => $this->generic_domain_markup('b2b_car'),
            ];
        }
        return $this->car_markup;
    }

    public function generic_domain_markup(string $module_type): array
    {
        $query = sprintf(
            'SELECT ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type, ML.markup_currency
			FROM markup_list AS ML
			WHERE ML.module_type = "%s" AND ML.markup_level = "%s" AND ML.type="generic"
			AND ML.domain_list_fk=%d AND ML.user_oid=%d',
            $module_type,
            $this->markup_level,
            get_domain_auth_id(),
            $this->entity_user_id
        );

        return $this->db->query($query)->result_array();
    }

    public function specific_airline_markup_list(string $module_type): array
    {
        $sub_query = sprintf(
            'SELECT AL.origin
			FROM airline_list AS AL
			JOIN markup_list AS ML ON
			ML.module_type = "%s" AND ML.markup_level = "%s" AND AL.origin=ML.reference_id AND ML.type="specific"
			AND ML.domain_list_fk != 0 AND ML.domain_list_fk=%d AND ML.user_oid=%d',
            $module_type,
            $this->markup_level,
            get_domain_auth_id(),
            $this->entity_user_id
        );

        $query = sprintf(
            'SELECT AL.origin AS airline_origin, AL.name AS airline_name, AL.code AS airline_code,
			ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type
			FROM airline_list AS AL
			LEFT JOIN markup_list AS ML ON
			ML.module_type = "%s" AND ML.markup_level = "%s" AND AL.origin=ML.reference_id AND ML.type="specific"
			AND ML.domain_list_fk != 0 AND ML.domain_list_fk=%d AND ML.user_oid=%d
			WHERE (AL.has_specific_markup=%d OR AL.origin IN (%s))
			ORDER BY AL.name ASC',
            $module_type,
            $this->markup_level,
            get_domain_auth_id(),
            $this->entity_user_id,
            ACTIVE,
            $sub_query
        );

        return $this->db->query($query)->result_array();
    }

    public function specific_airline_markup(string $module_type): array
    {
        $query = sprintf(
            'SELECT AL.origin AS airline_origin, AL.name AS airline_name, AL.code AS airline_code,
			ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type, ML.markup_currency
			FROM airline_list AS AL
			JOIN markup_list AS ML ON ML.value != ""
			AND ML.module_type = "%s" AND ML.markup_level = "%s" AND AL.origin=ML.reference_id AND ML.type="specific"
			AND ML.domain_list_fk != 0 AND ML.domain_list_fk=%d AND ML.user_oid=%d
			ORDER BY AL.name ASC',
            $module_type,
            $this->markup_level,
            get_domain_auth_id(),
            $this->entity_user_id
        );

        $markup_list = [];
        $specific_data_list = $this->db->query($query)->result_array();
        if (!empty($specific_data_list)) {
            foreach ($specific_data_list as $row) {
                $markup_list[$row['airline_code']] = $row;
            }
        }
        return $markup_list;
    }

    public function individual_airline_markup_details(string $module_type, string $airline_code): array
    {
        $query = sprintf(
            'SELECT ML.origin AS markup_list_origin, AL.origin AS airline_list_origin
			FROM airline_list AS AL
			LEFT JOIN markup_list AS ML ON
			ML.module_type = "%s" AND ML.markup_level = "%s" AND AL.origin=ML.reference_id AND ML.type="specific"
			AND ML.domain_list_fk != 0 AND ML.domain_list_fk=%d AND ML.user_oid=%d
			WHERE AL.code = "%s"',
            $module_type,
            $this->markup_level,
            get_domain_auth_id(),
            $this->entity_user_id,
            $airline_code
        );

        return $this->db->query($query)->row_array();
    }

    public function test_tables(int|string $id): array
    {
        $id = (string)$id;
        $query = sprintf(
            'SELECT * FROM master_transaction_details AS MD
			LEFT JOIN flight_booking_details AS FD ON MD.created_by_id = FD.created_by_id
			LEFT JOIN hotel_booking_details AS HD ON MD.created_by_id = HD.created_by_id
			LEFT JOIN bus_booking_details AS BD ON MD.created_by_id = BD.created_by_id
			WHERE MD.created_by_id = "%1$s" AND FD.created_by_id = "%1$s"
			AND HD.created_by_id = "%1$s" AND BD.created_by_id = "%1$s"',
            $id
        );
        return $this->db->query($query)->result_array();
    }
    public function save_master_transaction_details(array $details, string $type = ''): int
    {
        $system_transaction_id = 'DEP-' . $this->entity_user_id . time();
        $amount = $details['amount'];

        $master_transaction_details = [
            'system_transaction_id'      => $system_transaction_id,
            'domain_list_fk'             => get_domain_auth_id(),
            'transaction_type'           => $details['transaction_type'],
            'amount'                     => $amount,
            'currency'                   => $details['currency'],
            'currency_conversion_rate'   => $details['conversion_value'],
            'date_of_transaction'        => date('Y-m-d', strtotime($details['date_of_transaction'])),
            'bank'                       => $details['bank'],
            'branch'                     => $details['branch'],
            'deposited_branch'           => $details['deposited_branch'] ?? '',
            'transaction_number'         => $details['transaction_number'] ?? 'N/A',
            'status'                     => 'pending',
            'type'                       => 'ultralux',
            'remarks'                    => $details['remarks'],
            'created_datetime'           => db_current_datetime(),
            'created_by_id'              => $this->entity_user_id,
            'user_oid'                   => $this->entity_user_id,
        ];

        $insert = $this->custom_db->insert_record('master_transaction_details', $master_transaction_details);
        $insert_id = $insert['insert_id'];

        $notification_users = $this->user_model->get_admin_user_id();

        $remarks = 'Deposit Request:' . $amount . ' ' . get_application_default_currency() . '(' . $this->agency_name . ')';

        if (!empty($type) && $type == 'Credit') {
            $remarks = 'Credit Limit Request:' . $amount . ' ' . get_application_default_currency() . '(' . $this->agency_name . ')';
            $this->application_logger->credit_limit_request($remarks, ['system_transaction_id' => $system_transaction_id], $notification_users);
            return $insert_id;
        }

        $this->application_logger->balance_deposit_request($remarks, ['system_transaction_id' => $system_transaction_id], $notification_users);

        return $insert_id;
    }

    public function master_transaction_request_list(array $data_list_filt = [], string $type = ''): array
    {
        $data_list_cond = '';
        if (!empty($data_list_filt)) {
            $data_list_cond = $this->custom_db->get_custom_condition($data_list_filt);
        }

        $user_id = $this->db->escape($this->entity_user_id);
        $query = !empty($type)
            ? "SELECT * FROM master_transaction_details MTD WHERE MTD.type='ultralux' AND MTD.created_by_id={$user_id} AND transaction_type='{$type}' {$data_list_cond} ORDER BY origin DESC"
            : "SELECT * FROM master_transaction_details MTD WHERE MTD.type='b2b' AND MTD.user_oid={$user_id} AND transaction_type!='Credit' {$data_list_cond} ORDER BY origin DESC";

        return $this->db->query($query)->result_array();
    }

    public function filter_account_ledger(array $search_data): array
    {
        $from = "{$search_data['from']} 00:00:00";
        $to = "{$search_data['to']} 23:59:59";
        $user_id = $this->db->escape($this->entity_user_id);
        $query = "SELECT * FROM master_transaction_details WHERE type='b2b' AND created_by_id={$user_id} AND created_datetime BETWEEN '{$from}' AND '{$to}' ORDER BY origin DESC";

        return $this->db->query($query)->result_array();
    }

    public function verify_current_balance(float $amount, string $currency): int
    {
        if (!$this->verify_domain_balance) {
            return SUCCESS_STATUS;
        }

        if ($amount <= 0) {
            return FAILURE_STATUS;
        }

        $user_id = (int) $this->entity_user_id;
        $domain_id = $this->db->escape(get_domain_auth_id());
        $domain_key = $this->db->escape(get_domain_key());

        $query = "
            SELECT BU.balance, BU.credit_limit, BU.due_amount, CC.country as currency, CC.value as conversion_value
            FROM user U
            JOIN ultralux_user_details BU ON U.user_id = BU.user_oid
            JOIN domain_list DL ON U.domain_list_fk = DL.origin
            JOIN currency_converter CC ON CC.id = BU.currency_converter_fk
            WHERE U.status = " . ACTIVE . "
            AND U.user_id = {$user_id}
            AND DL.status = " . ACTIVE . "
            AND DL.origin = {$domain_id}
            AND DL.domain_key = {$domain_key}
        ";
        $balance_record = $this->db->query($query)->row_array();
        if ($currency != $balance_record['currency']) {
            exit('Under Construction--Currency mismatch');
        }

        $balance = $balance_record['balance'] + (float) $balance_record['credit_limit'] + (float) $balance_record['due_amount'];

        return ($balance >= $amount) ? SUCCESS_STATUS : FAILURE_STATUS;
    }


    public function update_transaction_details(
        string $transaction_type,
        string $app_reference,
        float $fare,
        float $domain_markup = 0,
        float $level_one_markup = 0,
        float $convinence = 0,
        float $discount = 0,
        string $currency = 'INR',
        float $currency_conversion_rate = 1
    ): int {

        $status = FAILURE_STATUS;
        $remarks = "$transaction_type Transaction was Successfully done";
        $amount = $this->agent_buying_price($transaction_type, $app_reference);
        $notification_users = $this->user_model->get_admin_user_id();

        $action_query_string = [
            'app_reference' => $app_reference,
            'type' => $transaction_type,
            'module' => $this->config->item('current_module')
        ];

        if ($this->verify_domain_balance) {
            if ($amount > 0) {
                $this->save_transaction_details(
                    $transaction_type,
                    $app_reference,
                    $amount,
                    $domain_markup,
                    $level_one_markup,
                    $remarks,
                    $convinence,
                    $discount,
                    $currency,
                    $currency_conversion_rate
                );

                $this->update_agent_balance(-$amount);
                $this->application_logger->transaction_status("$remarks($amount)", $action_query_string, $notification_users);
            }
        } else {
            $this->save_transaction_details(
                $transaction_type,
                $app_reference,
                $amount,
                $domain_markup,
                $level_one_markup,
                $remarks,
                $convinence,
                $discount,
                $currency,
                $currency_conversion_rate
            );
            $this->application_logger->transaction_status("$remarks($amount)", $action_query_string, $notification_users);
            $status = SUCCESS_STATUS;
        }

        return $status;
    }


    public function agent_buying_price(string $transaction_type, string $app_reference): float
    {
        $this->load->library('booking_data_formatter');

        return match ($transaction_type) {
            'flight' => $this->get_agent_price('flight_model', 'format_flight_booking_data', $app_reference),
            'hotel'  => $this->get_agent_price('hotel_model', 'format_hotel_booking_data', $app_reference),
            'car'    => $this->get_agent_price('car_model', 'format_car_booking_datas', $app_reference),
            default  => 0
        };
    }

    private function get_agent_price(string $model, string $formatter_method, string $app_reference): float
    {
        $this->load->model($model);
        $booking_data = $this->$model->get_booking_details($app_reference, '');
        $formatted = $this->booking_data_formatter->$formatter_method($booking_data, 'ultralux');
        return floatval($formatted['data']['booking_details'][0]['agent_buying_price'] ?? 0);
    }

    public function update_agent_balance(float $amount): float
    {
        $cond = ['user_oid' => (int) $this->entity_user_id];
        $details = $this->custom_db->single_table_records('ultralux_user_details', 'balance,due_amount,credit_limit', $cond);

        if ($details['status'] && !empty($details['data'][0])) {
            $data = $details['data'][0];
            $data['balance'] += $amount;

            if ($data['balance'] < 0) {
                $data['due_amount'] += $data['balance'];
                $data['balance'] = 0;
            }

            $this->custom_db->update_record('ultralux_user_details', $data, $cond);
            $this->balance_notification($data['balance']);
            return $data['balance'];
        }

        return 0.0;
    }

    public function balance_notification(float $current_balance): void
    {
        $condition = ['agent_fk' => (int) $this->entity_user_id];
        $details = $this->custom_db->single_table_records('agent_balance_alert_details', '*', $condition);

        if ($details['status'] && !empty($details['data'][0])) {
            $record = $details['data'][0];
            $threshold = (float) $record['threshold_amount'];

            if ($current_balance <= $threshold) {
                if ((int) $record['enable_sms_notification'] == ACTIVE && !empty(trim($record['mobile_number']))) {
                    // TODO: Send SMS notification
                }

                if ((int) $record['enable_email_notification'] == ACTIVE && !empty(trim($record['email_id']))) {
                    $subject = "{$this->agency_name} - Low Balance Alert";
                    $message = "Dear {$this->entity_name},<br/> 
                        <h1>Your Agent Balance is Low.</h1>
                        <h2>Agent Balance as on " . date("Y-m-d h:i:sa") . " is : " . COURSE_LIST_DEFAULT_CURRENCY_VALUE . " {$threshold}/-</h2>
                        <h3>Please Recharge Your Account to enjoy Uninterrupted Bookings. :)</h3>";

                    $this->load->library('provab_mailer');
                    $this->provab_mailer->send_mail($record['email_id'], $subject, $message);
                }
            }
        }
    }
    public function save_transaction_details(
        string $transaction_type,
        string $app_reference,
        float $fare,
        float $domain_markup,
        float $level_one_markup,
        string $remarks,
        float $convinence = 0,
        float $discount = 0,
        string $currency = 'INR',
        float $currency_conversion_rate = 1,
        int $transaction_owner_id = 0
    ): void {
        $transaction_owner_id = $transaction_owner_id > 0 ? $transaction_owner_id : (int) $this->entity_user_id;

        $transaction_log = [
            'system_transaction_id'      => date('Ymd-His') . '-S-' . rand(1, 10000),
            'transaction_type'           => $transaction_type,
            'domain_origin'              => get_domain_auth_id(),
            'app_reference'              => $app_reference,
            'fare'                       => ($fare - $domain_markup),
            'level_one_markup'           => $level_one_markup,
            'domain_markup'              => $domain_markup,
            'remarks'                    => $remarks,
            'transaction_owner_id'       => $transaction_owner_id,
            'created_by_id'              => (int) $this->entity_user_id,
            'created_datetime'           => date('Y-m-d H:i:s'),
            'convinence_fees'            => $convinence,
            'promocode_discount'         => $discount,
            'currency'                   => $currency,
            'currency_conversion_rate'   => $currency_conversion_rate,
        ];

        $total_transaction_amount = $fare;
        $balance_details = $this->get_opening_closing_balance($transaction_owner_id, $total_transaction_amount);

        $transaction_log['opening_balance'] = $balance_details['opening_balance'];
        $transaction_log['closing_balance'] = $balance_details['closing_balance'];

        $this->custom_db->insert_record('transaction_log', $transaction_log);
    }

    public function get_opening_closing_balance(int $user_oid, float $total_transaction_amount): array
    {
        $query = 'SELECT balance AS closing_balance FROM ultralux_user_details WHERE user_oid = ' . $user_oid;
        $result = $this->db->query($query)->row_array();

        $opening_balance = (float) ($result['closing_balance'] ?? 0.0);
        $transaction_effect = $total_transaction_amount < 0 ? abs($total_transaction_amount) : -$total_transaction_amount;
        $closing_balance = $opening_balance + $transaction_effect;

        return [
            'opening_balance' => round($opening_balance, 4),
            'closing_balance' => round($closing_balance, 4),
        ];
    }
    public function flight_commission_details(): array
    {
        $domain_id = get_domain_auth_id();
        $agent_id = (int) $this->entity_user_id;
        $query = 'select BFCD.* from b2b_flight_commission_details as BFCD
								where BFCD.domain_list_fk =' . get_domain_auth_id() . ' 
								and ((BFCD.agent_fk=' . intval($this->entity_user_id) . ' and BFCD.type="specific")	OR BFCD.type="generic")
								group by BFCD.agent_fk
								order by BFCD.agent_fk desc';

        return [
            'status' => SUCCESS_STATUS,
            'data'   => [
                'flight_commission_details' => $this->db->query($query)->result_array(),
            ],
        ];
    }

    public function bank_account_details(): array
    {
        $query = 'SELECT BAD.* FROM bank_account_details BAD
		        JOIN user U on U.user_id=BAD.created_by_id
		        where BAD.domain_list_fk=' . get_domain_auth_id() . ' and BAD.status=' . ACTIVE;

        $result = $this->db->query($query);
        if ($result->num_rows() > 0) {
            return ['status' => QUERY_SUCCESS, 'data' => $result->result_array()];
        }

        return ['status' => QUERY_FAILURE];
    }

    public function agent_account_ledger(array $condition = [], bool $count = false, int $offset = 0, int $limit = PHP_INT_MAX): array
    {
        $data = [];
        $condition_string = '';
        if(!empty($condition)){
            $condition_string = $this->custom_db->get_custom_condition($condition);

        }
        $user_id = (int) $this->entity_user_id;
        $transaction_activated_from_date = '2017-01-10';

        $agent_filter = " AND U.user_id = {$user_id} AND U.user_type = " . ULTRALUX_USER;

        if ($count) {
            $query = '
            SELECT COUNT(*) AS total_records 
            FROM transaction_log TL 
            JOIN user U ON U.user_id = TL.transaction_owner_id 
            WHERE TL.origin > 0 
              AND DATE(TL.created_datetime) >= "' . $transaction_activated_from_date . '" ' . $agent_filter . ' ' . $condition_string;

            $result = $this->db->query($query)->row_array();
            $data['total_records'] = (int) $result['total_records'];
            return $data;
        }

        $query = 'SELECT 
                U.agency_name, TL.*,
                CASE TL.transaction_type
                    WHEN "flight" THEN 
                        (SELECT CONCAT("LeadPax:", PD.first_name, " ", PD.last_name, " PNR: ", GROUP_CONCAT(DISTINCT(FTD.pnr))) 
                         FROM flight_booking_transaction_details FTD, flight_booking_passenger_details PD 
                         WHERE FTD.app_reference = TL.app_reference 
                           AND PD.app_reference = TL.app_reference 
                         GROUP BY FTD.app_reference)
                    WHEN "hotel" THEN 
                        (SELECT CONCAT("LeadPax:", PD.first_name, " ", PD.last_name, " Booking ID: ", HTD.booking_id, " Booking Ref.: ", HTD.booking_reference) 
                         FROM hotel_booking_details HTD, hotel_booking_pax_details PD 
                         WHERE HTD.app_reference = TL.app_reference 
                           AND PD.app_reference = TL.app_reference 
                         GROUP BY HTD.app_reference)
                    WHEN "transaction" THEN 
                        (SELECT CONCAT("Amount ", MTD.amount) 
                         FROM master_transaction_details MTD 
                         WHERE MTD.system_transaction_id = TL.app_reference 
                         GROUP BY MTD.system_transaction_id)
                END AS "REF"
            FROM transaction_log TL 
            JOIN user U ON U.user_id = TL.transaction_owner_id 
            WHERE 1=1 
              AND DATE(TL.created_datetime) >= "' . $transaction_activated_from_date . '" ' . $agent_filter . ' ' . $condition_string . ' 
            ORDER BY TL.created_datetime DESC 
            LIMIT ' . $offset . ', ' . $limit;

        $data['data'] = $this->db->query($query)->result_array();
        return $data;
    }
       public function save_markup_data(
        int $markup_origin = 0,
        string $type,
        string $module_type,
        int $reference_id,
        float $value,
        string $value_type,
        int $domain_origin,
    ): void {
        $markup_data = [
            'origin'           => $markup_origin,
            'markup_level'     => $this->markup_level,
            'type'             => strtolower($type),
            'module_type'      => strtolower($module_type),
            'reference_id'     => $reference_id,
            'value'            => $value,
            'value_type'       => strtolower($value_type),
            'domain_list_fk'   => $domain_origin,
            'user_oid'         => $this->entity_user_id ?? 0,
            'markup_currency'  => get_application_currency_preference(),
        ];

        if (!empty($markup_data['type']) && !empty($markup_data['value_type'])) {
            if ($markup_origin > 0) {
                // Update existing record
                $this->custom_db->update_record('markup_list', $markup_data, ['origin' => $markup_origin]);
                return;
            }

            // Insert new record
            $this->custom_db->insert_record('markup_list', $markup_data);
        }
    }
}
