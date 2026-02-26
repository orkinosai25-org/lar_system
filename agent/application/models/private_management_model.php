<?php
require_once 'abstract_management_model.php';

/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A
 * @version    V2
 */
class Private_Management_Model extends Abstract_Management_Model
{
    private array $airline_markup = [];
    private array $hotel_markup = [];
    private array $car_markup = [];
    private array $airline_commission = [];

    public function __construct()
    {
        parent::__construct('level_3');
    }

    /**
     * Get Convinence fees of module
     */
    public function get_convinence_fees(string $module_name, int $search_id): array
    {
        return ['value' => 0, 'type' => '', 'per_pax' => true];
    }

    /**
     * Get markup based on different modules
     */
    public function get_markup(string $module_name): array
    {
        return match ($module_name) {
            'flight' => $this->airline_markup(),
            'hotel'  => $this->hotel_markup(),
            'car'    => $this->car_markup(),
            default  => ['value' => 0, 'type' => '']
        };
    }

    public function airline_markup(): array
    {
        if (empty($this->airline_markup)) {
            $this->airline_markup = [
                'specific_markup_list' => $this->specific_airline_markup('b2b_flight'),
                'generic_markup_list' => $this->generic_domain_markup('b2b_flight')
            ];
        }
        return $this->airline_markup;
    }

    public function hotel_markup(): array
    {
        if (empty($this->hotel_markup)) {
            $specific = $this->specific_domain_markup('b2b_hotel');
            $response = ['specific_markup_list' => $specific];

            if (!valid_array($specific)) {
                $response['generic_markup_list'] = $this->generic_domain_markup('b2b_hotel');
            }

            $this->hotel_markup = $response;
        }
        return $this->hotel_markup;
    }

    public function car_markup(): array
    {
        if (empty($this->car_markup)) {
            $specific = $this->specific_domain_markup('b2b_car');
            $response = ['specific_markup_list' => $specific];

            if (!valid_array($specific)) {
                $response['generic_markup_list'] = $this->generic_domain_markup('b2b_car');
            }

            $this->car_markup = $response;
        }
        return $this->car_markup;
    }

    public function generic_domain_markup(string $module_type): array
    {
        $query = "
            SELECT ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type,
                   ML.markup_currency AS markup_currency
            FROM markup_list AS ML
            WHERE ML.value != ''
              AND ML.module_type = '{$module_type}'
              AND ML.markup_level = '{$this->markup_level}'
              AND ML.type = 'generic'
              AND ML.domain_list_fk = " . get_domain_auth_id();

        return $this->db->query($query)->result_array();
    }

    public function specific_domain_markup(string $module_type): array
    {
        $query = "
            SELECT ML.origin AS markup_origin, ML.value, ML.value_type, ML.markup_currency AS markup_currency
            FROM domain_list AS DL
            JOIN markup_list AS ML ON DL.origin = ML.domain_list_fk
            WHERE ML.value != ''
              AND ML.module_type = '{$module_type}'
              AND ML.markup_level = '{$this->markup_level}'
              AND ML.type = 'specific'
              AND ML.domain_list_fk != 0
              AND ML.reference_id = " . get_domain_auth_id() . "
              AND ML.domain_list_fk = " . get_domain_auth_id() . "
            ORDER BY DL.created_datetime DESC";

        return $this->db->query($query)->result_array();
    }

    public function specific_airline_markup(string $module_type): array
    {
        $markup_list = [];
        $query = "
            SELECT AL.origin AS airline_origin, AL.name AS airline_name, AL.code AS airline_code,
                   ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id,
                   ML.value, ML.value_type, ML.markup_currency AS markup_currency
            FROM airline_list AS AL
            JOIN markup_list AS ML ON AL.origin = ML.reference_id
            WHERE ML.value != ''
              AND ML.module_type = '{$module_type}'
              AND ML.markup_level = '{$this->markup_level}'
              AND ML.type = 'specific'
              AND ML.domain_list_fk != 0
              AND ML.domain_list_fk = " . get_domain_auth_id() . "
            ORDER BY AL.name ASC";

        $specific_data_list = $this->db->query($query)->result_array();

        if (valid_array($specific_data_list)) {
            foreach ($specific_data_list as $markup) {
                $markup_list[$markup['airline_code']] = $markup;
            }
        }

        return $markup_list;
    }

    public function update_domain_balance(int $domain_origin, float $amount): float
    {
        $cond = ['origin' => $domain_origin];
        $details = $this->custom_db->single_table_records('domain_list', 'balance', $cond);

        if ($details['status'] == true && isset($details['data'][0]['balance'])) {
            $details['data'][0]['balance'] += $amount;
            $this->custom_db->update_record('domain_list', $details['data'][0], $cond);
            return $details['data'][0]['balance'];
        }

        return 0.0;
    }

    public function provab_xml_logger(string $operation_name, string $app_reference, string $module, mixed $request, mixed $response): void
    {
        $data = [
            'operation_name' => $operation_name,
            'app_reference' => $app_reference,
            'module' => $module,
            'request' => is_array($request) ? json_encode($request) : $request,
            'response' => is_array($response) ? json_encode($response) : $response,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'created_datetime' => date('Y-m-d H:i:s')
        ];

        $this->custom_db->insert_record('provab_xml_logger', $data);
    }

    public function get_commission(string $module_name): array
    {
        return match ($module_name) {
            'flight' => $this->airline_commission(),
            default => []
        };
    }

    public function airline_commission(): array
    {
        if (empty($this->airline_commission)) {
            $this->airline_commission = [
                'admin_commission_list' => $this->admin_b2b_airline_commission_list()
            ];
        }
        return $this->airline_commission;
    }

    public function admin_b2b_airline_commission_list(): array
    {
        $domain_origin = get_domain_auth_id();
        $entity_user_id = intval($this->entity_user_id);

        $query = "
            SELECT value, value_type, commission_currency
            FROM b2b_flight_commission_details
            WHERE agent_fk IN (0, {$entity_user_id})
              AND domain_list_fk = {$domain_origin}
            ORDER BY type DESC";

        $com = $this->db->query($query)->row_array();

        if (empty($com['value'])) {
            $query_gen = "
                SELECT value, value_type, commission_currency
                FROM b2b_flight_commission_details
                WHERE agent_fk = 0
                  AND domain_list_fk = {$domain_origin}
                  AND type = 'generic'
                ORDER BY type DESC";

            $com = $this->db->query($query_gen)->row_array();
        }

        $this->value_type_to_lower_case($com);
        return $com;
    }

    private function value_type_to_lower_case(array &$row): void
    {
        if (isset($row['value_type'])) {
            $row['value_type'] = strtolower($row['value_type']);
            return;
        }

        $row['value'] = 0;
        $row['value_type'] = 'plus';
        $row['commission_currency'] = MARKUP_CURRENCY;
    }
}
