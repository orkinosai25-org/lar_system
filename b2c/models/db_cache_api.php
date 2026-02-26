<?php
/**
 * Library which has cache functions to get data
 *
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A <balu.provab@gmail.com>
 * @version    V2
 */

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
            $this->cache[$hash_key] = $this->custom_db->single_table_records(
                'api_country_list', '*', $condition, 0, 100000000, ['name' => 'ASC']
            );
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

    public function get_country_code_list_profile(array $from = ['k' => 'country_code', 'v' => ['name', 'country_code']], array $condition = ['name !=' => '']): array
    {
        return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
    }

    public function get_user_type(array $from = ['k' => 'origin', 'v' => ['user_type']], array $condition = ['user_type !=' => '', 'origin !=' => ADMIN]): array
    {
        if ((isset($_GET['domain_origin']) && (int)$_GET['domain_origin'] > 0)
            || (isset($_GET['uid']) && (int)$_GET['uid'] > 0 && $this->entity_user_id === (int)$_GET['uid'])) {
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
            $this->cache[$hash_key] = $this->custom_db->single_table_records(
                'user_type', '*', $condition, 0, 100000000, ['user_type' => 'ASC']
            );
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
            $this->cache[$hash_key] = $this->custom_db->single_table_records('bus_stations_new', '*', $condition);
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
            $this->cache[$hash_key] = $this->custom_db->single_table_records(
                'meta_course_list', '*', $condition, 0, 100000000, ['priority_number' => 'ASC']
            );
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

    public function get_currency(array $from = ['k' => 'id', 'v' => 'country'], array $condition = ['country !=' => '']): array
    {
        return magical_converter($from, $this->cache[$this->set_currency($condition)]);
    }

    public function set_currency(array $condition): string
    {
        $hash_key = hash('md5', __CLASS__ . 'currency_converter' . json_encode($condition));
        if (!isset($this->cache[$hash_key])) {
            $this->cache[$hash_key] = $this->custom_db->single_table_records(
                'currency_converter', '*', $condition, 0, 100000000, ['country' => 'ASC']
            );
        }
        return $hash_key;
    }

    public function get_active_bank_list(array $from = ['k' => 'origin', 'v' => ['en_bank_name', 'account_number']], array $condition = ['status' => ACTIVE]): array
    {
        return magical_converter($from, $this->cache[$this->set_active_bank_list($condition)]);
    }

    public function set_active_bank_list(array $condition): string
    {
        $hash_key = hash('md5', __CLASS__ . 'bank_payment_details' . json_encode($condition));
        if (!isset($this->cache[$hash_key])) {
            $this->cache[$hash_key] = $this->custom_db->single_table_records(
                'bank_payment_details', '*', $condition, 0, 100000000, ['en_bank_name' => 'ASC']
            );
        }
        return $hash_key;
    }

    public function get_airport_code_list(array $condition): array
    {
        $airport_code_list = $this->cache[$this->set_airport_code_list($condition)] ?? [];
        $code_list = [];

        if (isset($airport_code_list['data']) && is_array($airport_code_list['data'])) {
            foreach ($airport_code_list['data'] as $v) {
                $key = $v['city'] . ':(' . $v['code'] . ')';
                $value = $v['city'] . '(' . $v['code'] . ')';
                $code_list[$key] = $value;
            }
        }

        return $code_list;
    }

    public function set_airport_code_list(array $condition): string
    {
        $hash_key = hash('md5', __CLASS__ . 'airport_code_list' . json_encode($condition));
        if (!isset($this->cache[$hash_key])) {
            $this->cache[$hash_key] = $this->custom_db->single_table_records('airport_code_list', '*', $condition);
        }
        return $hash_key;
    }
    /**
     * Get airline code list
     */
    public function get_airline_code_list(array $condition = []): array
    {
        $airport_code_list = $this->cache[$this->set_airline_code_list($condition)] ?? ['data' => []];
        $code_list = [];

        if (!empty($airport_code_list['data']) && is_array($airport_code_list['data'])) {
            foreach ($airport_code_list['data'] as $v) {
                $code = $v['code'] ?? '';
                $name = ucfirst(strtolower($v['name'] ?? ''));
                $code_list[$code] = $name . '-(' . $code . ')';
            }
        }

        return $code_list;
    }

    /**
     * Set airline code list
     */
    public function set_airline_code_list(array $condition): string
    {
        $hash_key = hash('md5', __CLASS__ . 'airline_list' . json_encode($condition));

        if (!isset($this->cache[$hash_key])) {
            $this->cache[$hash_key] = $this->custom_db->single_table_records(
                'airline_list', '*', $condition, 0, 100000000, ['name' => 'ASC', 'code' => 'ASC']
            );
        }

        return $hash_key;
    }

    /**
     * Get active social network list
     */
    public function get_active_social_network_list(): array
    {
        return $this->cache[$this->set_active_social_network_list()] ?? [];
    }

    /**
     * Set active social network list
     */
    public function set_active_social_network_list(): string
    {
        $hash_key = hash('md5', 'social_login');

        if (!isset($this->cache[$hash_key])) {
            $data = $this->custom_db->single_table_records(
                'social_login', '*', ['domain_origin' => get_domain_auth_id()], 0, 10
            );

            $data_list = [];
            if (!empty($data['data'])) {
                foreach ($data['data'] as $v) {
                    $name = $v['social_login_name'] ?? null;
                    if ($name !== null) {
                        $data_list[$name] = $v;
                    }
                }
            }

            $this->cache[$hash_key] = $data_list;
        }

        return $hash_key;
    }

    /**
     * Get airline list
     */
    public function get_airline_list(array $from = ['k' => 'code', 'v' => 'name'], array $condition = ['name !=' => '']): array
    {
        return magical_converter($from, $this->cache[$this->set_airline_list($condition)] ?? []);
    }

    /**
     * Set airline list
     */
    public function set_airline_list(array $condition): string
    {
        $hash_key = hash('md5', __CLASS__ . 'airline_list' . json_encode($condition));

        if (!isset($this->cache[$hash_key])) {
            $this->cache[$hash_key] = $this->custom_db->single_table_records(
                'airline_list', '*', $condition, 0, 100000000, ['name' => 'ASC']
            );
        }

        return $hash_key;
    }

    /**
     * Get admin base currency
     */
    public function get_admin_base_currency(): string
    {
        return $this->cache[$this->set_admin_base_currency()] ?? '';
    }

    /**
     * Set admin base currency
     */
    public function set_admin_base_currency(): string
    {
        $hash_key = hash('md5', __CLASS__ . 'admin_base_currency');

        if (!isset($this->cache[$hash_key])) {
            $domain_id = (int)get_domain_auth_id();
            $query = "SELECT CC.country AS base_currency 
                      FROM domain_list DL
                      JOIN currency_converter CC ON CC.id = DL.currency_converter_fk
                      WHERE origin = {$domain_id}";

            $domain_details = $this->db->query($query)->row_array();
            $this->cache[$hash_key] = $domain_details['base_currency'] ?? '';
        }

        return $hash_key;
    }

    /**
     * Get mobile code for country
     */
    public function get_mobile_code(int $country_origin): ?string
    {
        $result = $this->custom_db->single_table_records('api_country_list', 'country_code', ['origin' => $country_origin]);
        return $result['data'][0]['country_code'] ?? null;
    }
}
