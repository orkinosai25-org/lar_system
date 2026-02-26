<?php

class Db_Cache_Api extends CI_Model
{
    private array $cache = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('custom/db_api');
    }

    public function get_country_list(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '']): array
    {
        return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
    }

    public function get_iso_country_list(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '', 'iso_country_code !=' => '']): array
    {
        return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
    }

    public function set_country_list(array $condition): string
    {
        $hash_key = hash('md5', __CLASS__ . 'api_country_list' . json_encode($condition));
        if (!isset($this->cache[$hash_key])) {
            $this->cache[$hash_key] = $this->custom_db->single_table_records('api_country_list', '*', $condition, 0, 100000000, ['name' => 'ASC']);
        }
        return $hash_key;
    }

    public function get_iso_country_code(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['iso_country_code !=' => '', 'country_code !=' => '']): array
    {
        return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
    }

    public function get_postal_code_list(array $from = ['k' => 'origin', 'v' => ['name', 'country_code']], array $condition = ['name !=' => '']): array
    {
        return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
    }

    public function get_country_code_list(array $from = ['k' => 'country_code', 'v' => ['name', 'country_code']], array $condition = ['name !=' => '']): array
    {
        return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
    }

    public function get_user_type(array $from = ['k' => 'origin', 'v' => ['user_type']], array $condition = ['user_type !=' => '', 'origin !=' => ADMIN]): array
    {
        if ((isset($_GET['domain_origin']) && intval($_GET['domain_origin']) > 0) ||
            (isset($_GET['uid']) && intval($_GET['uid']) > 0 && $this->entity_user_id == intval($_GET['uid']))) {
            $condition = ['user_type !=' => '', 'origin =' => ADMIN];
        } elseif (get_domain_auth_id() > 0) {
            $condition = ['user_type !=' => '', 'origin !=' => ADMIN];
        }

        return magical_converter($from, $this->cache[$this->set_user_type($condition)]);
    }

    public function set_user_type(array $condition): string
    {
        $hash_key = hash('md5', __CLASS__ . 'user_type' . json_encode($condition));
        if (!isset($this->cache[$hash_key])) {
            $this->cache[$hash_key] = $this->custom_db->single_table_records('user_type', '*', $condition, 0, 100000000, ['user_type' => 'ASC']);
        }
        return $hash_key;
    }

    public function get_continent_list(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '']): array
    {
        return magical_converter($from, $this->cache[$this->set_continent_list($condition)]);
    }

    public function set_continent_list(array $condition): string
    {
        $hash_key = hash('md5', __CLASS__ . 'api_continent_list' . json_encode($condition));
        if (!isset($this->cache[$hash_key])) {
            $this->cache[$hash_key] = $this->custom_db->single_table_records('api_continent_list', '*', $condition);
        }
        return $hash_key;
    }

    public function get_bus_station_list(array $from = ['k' => 'station_id', 'v' => 'name'], array $condition = ['name !=' => '']): array
    {
        return magical_converter($from, $this->cache[$this->set_bus_station_list($condition)]);
    }

    public function set_bus_station_list(array $condition): string
    {
        $hash_key = hash('md5', __CLASS__ . 'bus_stations' . json_encode($condition));
        if (!isset($this->cache[$hash_key])) {
            $this->cache[$hash_key] = $this->custom_db->single_table_records('bus_stations', '*', $condition);
        }
        return $hash_key;
    }

    public function get_city_list(array $from = ['k' => 'origin', 'v' => 'destination'], array $condition = ['destination !=' => '']): array
    {
        return magical_converter($from, $this->set_city_list($condition));
    }

    public function set_city_list(array $condition): array
    {
        return $this->custom_db->single_table_records('api_city_list', '*', $condition);
    }

    public function get_course_type(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '']): array
    {
        return magical_converter($from, $this->cache[$this->set_course_type($condition)]);
    }

    public function course_type_list(array $condition): array
    {
        return $this->cache[$this->set_course_type($condition)];
    }

    public function set_course_type(array $condition): string
    {
        $hash_key = hash('md5', __CLASS__ . 'meta_course_list' . json_encode($condition));
        if (!isset($this->cache[$hash_key])) {
            $this->cache[$hash_key] = $this->custom_db->single_table_records('meta_course_list', '*', $condition, 0, 100000000, ['priority_number' => 'ASC']);
        }
        return $hash_key;
    }

    public function get_booking_source(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '']): array
    {
        return magical_converter($from, $this->cache[$this->set_booking_source($condition)]);
    }

    public function set_booking_source(array $condition): string
    {
        $hash_key = hash('md5', __CLASS__ . 'booking_source' . json_encode($condition));
        if (!isset($this->cache[$hash_key])) {
            $this->cache[$hash_key] = $this->custom_db->single_table_records('booking_source', '*', $condition);
        }
        return $hash_key;
    }

    public function get_active_api_booking_source(array $condition = [['name', '!=', '""']]): array
    {
        return $this->cache[$this->set_active_api_booking_source($condition)];
    }

    public function set_active_api_booking_source(array $condition): string
    {
        $condition_sql = $this->custom_db->get_custom_condition($condition);
        $hash_key = hash('md5', __CLASS__ . 'booking_source_api_config' . json_encode($condition_sql));

        if (!isset($this->cache[$hash_key])) {
            $query = "
                SELECT BS.name, BS.source_id AS booking_source, BS.authentication AS check_auth 
                FROM booking_source BS
                JOIN api_config AC ON AC.booking_source_fk = BS.origin
                JOIN domain_api_map DAM ON DAM.booking_source_fk = BS.origin
                JOIN domain_list DL ON DL.origin = DAM.domain_list_fk
                WHERE BS.booking_engine_status = " . ACTIVE . " 
                AND AC.status = " . ACTIVE . " 
                $condition_sql 
                ORDER BY BS.origin DESC
            ";
            $this->cache[$hash_key] = $this->db->query($query)->result_array();
        }

        return $hash_key;
    }
function get_currency(array $from = ['k' => 'id', 'v' => 'country'], array $condition = ['country !=' => '']) {
    return magical_converter($from, $this->cache[$this->set_currency($condition)]);
}
function set_currency(array $condition): string {
    $hash_key = hash('md5', __CLASS__ . 'currency_converter' . json_encode($condition));
    if (!isset($this->cache[$hash_key])) {
        $this->cache[$hash_key] = $this->custom_db->single_table_records('currency_converter', '*', $condition, 0, 100000000, ['country' => 'ASC']);
    }
    return $hash_key;
}
function get_active_bank_list(array $from = ['k' => 'origin', 'v' => ['en_bank_name', 'account_number']], array $condition = ['status' => ACTIVE]): mixed {
    return magical_converter($from, $this->cache[$this->set_active_bank_list($condition)]);
}
function set_active_bank_list(array $condition): string {
    $hash_key = hash('md5', __CLASS__ . 'bank_payment_details' . json_encode($condition));
    if (!isset($this->cache[$hash_key])) {
        $this->cache[$hash_key] = $this->custom_db->single_table_records('bank_payment_details', '*', $condition, 0, 100000000, ['en_bank_name' => 'ASC']);
    }
    return $hash_key;
}
function get_airport_code_list(array $condition): array {
    $airport_code_list = $this->cache[$this->set_airport_code_list($condition)];
    $code_list = [];
    if (valid_array($airport_code_list['data'])) {
        foreach ($airport_code_list['data'] as $v) {
            $code_list[$v['city'] . ':' . '(' . $v['code'] . ')'] = $v['city'] . '(' . $v['code'] . ')';
        }
    }
    return $code_list;
}
function set_airport_code_list(array $condition): string {
    $hash_key = hash('md5', __CLASS__ . 'city_code_list' . json_encode($condition));
    if (!isset($this->cache[$hash_key])) {
        $this->cache[$hash_key] = $this->custom_db->single_table_records('city_code_list', '*', $condition, 0, 100000000, ['priority_list' => 'DESC', 'city' => 'ASC']);
    }
    return $hash_key;
}
function get_airline_code_list(array $condition = []): array {
    $airport_code_list = $this->cache[$this->set_airline_code_list($condition)];
    $code_list = [];
    if (valid_array($airport_code_list['data'])) {
        foreach ($airport_code_list['data'] as $v) {
            if ($v['name'] != 'Alitalia CityLiner') {
                $code_list[$v['code']] = ucfirst(strtolower($v['name']));
            } else {
                $code_list[$v['code']] = $v['name'];
            }
        }
    }
    return $code_list;
}
function set_airline_code_list(array $condition): string {
    $hash_key = hash('md5', __CLASS__ . 'airline_list' . json_encode($condition));
    if (!isset($this->cache[$hash_key])) {
        $this->cache[$hash_key] = $this->custom_db->single_table_records('airline_list', '*', $condition, 0, 100000000, ['name' => 'ASC', 'code' => 'ASC']);
    }
    return $hash_key;
}
function get_domain_base_currency(): mixed {
    return $this->cache[$this->set_domain_base_currency()];
}
function set_domain_base_currency(): string {
    $hash_key = hash('md5', __CLASS__ . 'domain_base_currency');
    if (!isset($this->cache[$hash_key])) {
        $domain_id = intval(get_domain_auth_id());
        $query = 'select CC.country as base_currency 
                  from domain_list DL
                  JOIN currency_converter CC on CC.id=DL.currency_converter_fk
                  where origin=' . $domain_id;
        $domain_details = $this->db->query($query)->row_array();
        $this->cache[$hash_key] = $domain_details['base_currency'];
    }
    return $hash_key;
}
function get_airport_details(string $airport_code): mixed {
    return $this->cache[$this->set_airport_details($airport_code)];
}
function set_airport_details(string $airport_code): string {
    $hash_key = hash('md5', __CLASS__ . 'a_details' . $airport_code);
    if (!isset($this->cache[$hash_key])) {
        $query = 'select FA.* from flight_airport_list FA 
                  where FA.airport_code = "' . $airport_code . '"';
        $data = $this->db->query($query)->row_array();
        $this->cache[$hash_key] = valid_array($data) ? $data : false;
    }
    return $hash_key;
}
function get_airport_city_name(array $condition = []): mixed {
    return $this->cache[$this->set_airport_city_name($condition)];
}
function set_airport_city_name(array $condition): string {
    $hash_key = hash('md5', __CLASS__ . 'flight_airport_list' . json_encode($condition));
    if (!isset($this->cache[$hash_key])) {
        $data = $this->custom_db->single_table_records('flight_airport_list', '*', $condition);
        $this->cache[$hash_key] = ($data['status'] == SUCCESS_STATUS) ? (count($data['data']) > 1 ? $data['data'] : $data['data'][0]) : FAILURE_STATUS;
    }
    return $hash_key;
}
function get_airline_name(array $condition = []): mixed {
    return $this->cache[$this->set_airline_name($condition)];
}
function set_airline_name(array $condition): string {
    $hash_key = hash('md5', __CLASS__ . 'airline_list' . json_encode($condition));
    if (!isset($this->cache[$hash_key])) {
        $data = $this->custom_db->single_table_records('airline_list', '*', $condition);
        $this->cache[$hash_key] = ($data['status'] == SUCCESS_STATUS) ? (count($data['data']) > 1 ? $data['data'] : $data['data'][0]) : FAILURE_STATUS;
    }
    return $hash_key;
}
function get_travelport_flight_price_xml(string $search_id): array {
    $query = "select * from travelport_price_xml_new where serach_id='" . $search_id . "'";
    return $this->db->query($query)->result_array();
}
function get_travelport_flight_price_seat_xml(string $search_id): array {
    $query = "select * from travelport_price_xml_new where serach_id LIKE '" . $search_id . "%' order by created_date desc limit 1";
    return $this->db->query($query)->result_array();
}
function update_price_xml(string $price_xml, string $itinerary_xml, int $search_id): void {
	$data = [];
    $data['price_xml'] = $price_xml;
    $data['itinerary_xml'] = $itinerary_xml;
    $update_condition = ['serach_id' => $search_id];
    $this->custom_db->update_record('travelport_price_xml_new', $data, $update_condition);
}
function get_meals_travelport(): array {
    $query = "select * from travelport_meals_list";
    return $this->db->query($query)->result_array();
}
function get_meals_sabre(): array {
    $query = "select * from sabre_meals_list";
    return $this->db->query($query)->result_array();
}
}
