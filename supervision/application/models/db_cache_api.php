<?php

/**
 * Library which has cache functions to get data
 *
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */
class Db_Cache_Api extends CI_Model
{
	function __construct()
	{
		$this->load->helper('custom/db_api');
	}

	private $cache;
	/**
	 * Balu A
	 * get the country details
	 * @param array $from array('k' => 'id', 'v' => 'name')
	 * @param array $condition
	 */
	function get_country_list(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '']): array
	{
		//Balu A
		return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
	}
	/**
	 * set the country details
	 * @param array $condition
	 */
	function set_country_list(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'api_country_list' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('api_country_list', '*', $condition, 0, 100000000, array('name' => 'ASC'));
		}
		return $hash_key;
	}

	/**
	 * Balu A
	 * get the country details
	 * @param array $from array('k' => 'id', 'v' => 'name')
	 * @param array $condition
	 */
	function get_current_balance(): array
	{
		//Balu A
		return $this->cache[$this->set_current_balance()];
	}

	/**
	 * set the country details
	 * @param array $condition
	 */
	function set_current_balance(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'domain_list_balance');
		if (isset($this->cache[$hash_key]) == false) {
			$domain_id = intval(get_domain_auth_id());
			$domain_details = $this->custom_db->single_table_records('domain_list', 'balance', array('origin' => $domain_id));
			$this->cache[$hash_key] = array('value' => $domain_details['data'][0]['balance']);
		}
		return $hash_key;
	}
	function get_module_list(): array
	{
		$module_details = $this->custom_db->single_table_records('meta_course_list', '*', array('status' => 1));
		$module_data = array();
		if ($module_details['status'] == SUCCESS_STATUS) {
			foreach ($module_details['data'] as $module_name) {
				$module_data[$module_name['course_id']] = $module_name['name'];
			}
		}
		return $module_data;
	}
	/**
	 * Balu A
	 * get the Admin Base Currency
	 */
	function get_admin_base_currency(): string
	{
		return $this->cache[$this->set_admin_base_currency()];
	}

	/**
	 * Set the Admin Base Currency
	 * @param array $condition
	 */
	function set_admin_base_currency(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'admin_base_currency');
		if (isset($this->cache[$hash_key]) == false) {
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
	function get_postal_code_list(array $from = ['k' => 'origin', 'v' => ['name', 'country_code']], array $condition = ['name !=' => '']): array
	{
		return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
	}

	/**
	 * get the postal code details
	 * @param array $from array('k' => 'id', 'v' => 'name')
	 * @param array $condition
	 */

	function get_country_code_list(array $from = ['k' => 'country_code', 'v' => ['name', 'country_code']], array $condition = ['name !=' => '']): array
	{
		return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
	}

	/**
	 * get the user type details
	 * @param array $from array('k' => 'id', 'v' => 'name')
	 * @param array $condition
	 */
	function get_user_type(array $from = ['k' => 'origin', 'v' => ['user_type']], array $condition = ['user_type !=' => '', 'origin !=' => ADMIN]): array
	{
		//FIXME
		if ((isset($_GET['domain_origin']) == true && intval($_GET['domain_origin']) > 0) ||
			(isset($_GET['uid']) == true && intval($_GET['uid']) > 0) &&
			$this->entity_user_id == intval($_GET['uid'])
		) {
			//DOMAIN ADMIN CREATION BY PROVAB ADMIN (GET ONLY USER TYPE ADMIN) (OR) EDIT THEIR ACCOUNT
			//checking if it is Superadmin or Sub admin

			if ($this->entity_user_type == ADMIN) {
				$condition = array('user_type !=' => '', 'origin =' =>  ADMIN);
			} elseif ($this->entity_user_type == SUB_ADMIN) {
				$condition = array('user_type !=' => '', 'origin =' =>  SUB_ADMIN);
			}
		} else if (get_domain_auth_id() > 0) {
			//DOMAIN USERS CREATION BY DOMAIN ADMIN (GET ALL USER TYPES EXCEPT ADMIN USER TYPE)
			$condition = array('user_type !=' => '', 'origin !=' =>  ADMIN);
		}
		return magical_converter($from, $this->cache[$this->set_user_type($condition)]);
	}
	/**
	 * set the country details
	 * @param array $condition
	 */
	function set_user_type(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'user_type' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('user_type', '*', $condition, 0, 100000000, array('user_type' => 'ASC'));
		}
		return $hash_key;
	}

	/**
	 * get the continent details
	 * @param array $from array('k' => 'origin', 'v' => 'name')
	 * @param array $condition
	 */
	function get_continent_list(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '']): array
	{
		return magical_converter($from, $this->cache[$this->set_continent_list($condition)]);
	}

	/**
	 * set the continet details
	 * @param array $condition
	 */
	function set_continent_list(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'api_continent_list' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('api_continent_list', '*', $condition);
		}
		return $hash_key;
	}
	/**
	 * get the city details
	 * @param array $from array('k' => 'id', 'v' => 'destination')
	 * @param array $condition
	 */
	function get_city_list(array $from = ['k' => 'origin', 'v' => 'destination'], array $condition = ['destination !=' => '']): array

	{
		return magical_converter($from, $this->cache[$this->set_city_list($condition)]);
	}

	/**
	 * set the city details
	 * @param array $condition
	 */
	function set_city_list(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'api_city_list' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('api_city_list', '*', $condition);
		}
		return $hash_key;
	}
	/**
	 * 	Get Course Type
	 */
	function get_course_type(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '']): array
	{
		return magical_converter($from, $this->cache[$this->set_course_type($condition)]);
	}

	/**
	 * get course type list
	 */
	function course_type_list(array $condition): array
	{
		return $this->cache[$this->set_course_type($condition)];
	}

	/**
	 * set the course details
	 * @param array $condition
	 */
	function set_course_type(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'meta_course_list' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('meta_course_list', '*', $condition, 0, 100000000, array('priority_number' => 'ASC'));
		}
		return $hash_key;
	}
	function get_booking_source(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '']): array
	{
		return magical_converter($from, $this->cache[$this->set_booking_source($condition)]);
	}

	/**
	 * set the booking source details
	 * @param array $condition
	 */
	function set_booking_source(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'booking_source' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('booking_source', '*', $condition);
		}
		return $hash_key;
	}

	/**
	 * 	Get Currencies
	 */
	function get_currency(array $from = ['k' => 'id', 'v' => 'country'], array $condition = ['country !=' => '']): array
	{
		return magical_converter($from, $this->cache[$this->set_currency($condition)]);
	}

	/**
	 * set theCurrency details
	 * @param array $condition
	 */
	function set_currency(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'currency_converter' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('currency_converter', '*', $condition, 0, 100000000, array('country' => 'ASC'));
		}
		return $hash_key;
	}
	function get_active_bank_list(array $from = ['k' => 'origin', 'v' => ['en_bank_name', 'account_number']], array $condition = ['status' => ACTIVE]): array

	{
		return magical_converter($from, $this->cache[$this->set_active_bank_list($condition)]);
	}

	/**
	 * set Active Banks details
	 * @param array $condition
	 */
	function set_active_bank_list(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'bank_payment_details' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('bank_payment_details', '*', $condition, 0, 100000000, array('en_bank_name' => 'ASC'));
		}
		return $hash_key;
	}

	/**
	 * 	Get airport code
	 */
	function get_airport_code_list(): array
	{
		$airport_code_list = $this->cache[$this->set_airport_code_list()];
		$code_list = '';
		if (valid_array($airport_code_list['data'])) {
			foreach ($airport_code_list['data'] as  $v) {
				$code_list[$v['city'] . ':(' . $v['code'] . ')'] = $v['city'] . '(' . $v['code'] . ')';
			}
		}
		return $code_list;
	}
	function set_airport_code_list(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'city_code_list');
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('city_code_list', '*', array(), 0, 100000000, array('priority_list' => 'DESC', 'city' => 'ASC'));
		}
		return $hash_key;
	}

	/**
	 * 	Get airport code
	 */
	function get_active_social_network_list(): array
	{
		// $data_list = '';
		// $raw_data = '';
		return $this->cache[$this->set_active_social_network_list()];
	}

	/**
	 * set airport details
	 * @param array $condition
	 */
	function set_active_social_network_list(): string
	{
		$hash_key = hash('md5', 'social_login');
		if (isset($this->cache[$hash_key]) == false) {
			$data = $this->custom_db->single_table_records('social_login', '*', array('domain_origin' => get_domain_auth_id()), 0, 10);
			$data_list = array();
			foreach ($data['data'] as  $v) {
				$data_list[$v['social_login_name']] = $v;
			}
			$this->cache[$hash_key] = $data_list;
		}
		return $hash_key;
	}
	/**
	 * Balu A
	 * get the airline details
	 * @param array $from array('k' => 'id', 'v' => 'name')
	 * @param array $condition
	 */
	function get_airline_list(array $from = ['k' => 'code', 'v' => 'name'], array $condition = ['name !=' => '']): array
	{
		//Balu A
		return magical_converter($from, $this->cache[$this->set_airline_list($condition)]);
	}

	/**
	 * set the Airline details
	 * @param array $condition
	 */
	function set_airline_list(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'airline_list' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('airline_list', '*', $condition, 0, 100000000, array('name' => 'ASC'));
		}
		return $hash_key;
	}

	/*
	 * Return Agent List with Agent ID
	 */
	function get_agent_list_with_id(array $from = ['k' => 'user_id', 'v' => 'agency_name'], array $condition = ['agency_name !=' => '', 'user_type' => B2B_USER]): array
	{
		return magical_converter($from, $this->cache[$this->set_agent_list($condition)]);
	}
	function get_ultra_agent_list_with_id(array $from = ['k' => 'user_id', 'v' => 'agency_name'], array $condition = ['agency_name !=' => '', 'user_type' => ULTRALUX_USER]): array
	{
		return magical_converter($from, $this->cache[$this->set__ultra_agent_list($condition)]);
	}

	/**
	 * set the country details
	 * @param array $condition
	 */
	function set_agent_list(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'agent_list' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('user', '*', $condition, 0, 100000000, array('agency_name' => 'ASC'));
		}
		return $hash_key;
	}
	function set__ultra_agent_list(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'agent_list1' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('user', '*', $condition, 0, 100000000, array('agency_name' => 'ASC'));
		}
		return $hash_key;
	}
	function get_eco_stays_types(array $from = ['k' => 'origin', 'v' => 'name']): array
	{
		// debug(magical_converter($from, $this->cache[$this->set_eco_stays_types()]));die;
		return magical_converter($from, $this->cache[$this->set_eco_stays_types()]);
	}

	function set_eco_stays_types(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'eco_stays_types');
		if (isset($this->cache[$hash_key]) == false) {
			$res = $this->custom_db->single_table_records('eco_stays_types', '*', array('status' => 1));
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}
	function get_suppliers(array $from = ['k' => 'user_id', 'v' => 'user']): array
	{

		return magical_converter($from, $this->cache[$this->set_suppliers()]);
	}
	function set_suppliers(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'suppliers');
		if (isset($this->cache[$hash_key]) == false) {
$data = ['status' => false, 'data' => 'No user found']; 
			$qry = "select user_id,CONCAT((first_name),(' '),(last_name)) AS user from user where user_type=" . SUPPLIER . " AND status!=-1";
			$run_qry = $this->db->query($qry)->result_array();
			if (!empty($run_qry) == true) {
				$data['status'] = true;
				$data['data'] = $run_qry;
			} else {
				$data['status'] = false;
				$data['data'] = 'No user found';
			}
			// $res = $this->custom_db->single_table_records('user', "user_id, CONCAT((first_name),(' '),(last_name)) AS user", array('user_type' => SUPPLIER));

			$this->cache[$hash_key] = $data;
		}
		//debug($hash_key);die;
		return $hash_key;
	}

	function get_all_api_country_list(array $from = ['k' => 'country_code', 'v' => 'country_name']): array
	{
		$all_api_country_list = $this->cache[$this->set_all_api_country_list()];
		return magical_converter($from, $all_api_country_list);
	}
	function set_all_api_country_list(): string
	{
		$res=[];
		$hash_key = hash('md5', __CLASS__ . 'all_api_country_list');
		if (isset($this->cache[$hash_key]) == false) {
			$this->db->select('*');
			$this->db->from('all_api_city_master');
			$this->db->order_by('country_name');
			$this->db->group_by('country_name');
			$query = $this->db->get();
			$res['status'] = SUCCESS_STATUS;
			$res['data'] = $query->result_array();
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}
	function get_eco_stays_room_types(array $from = ['k' => 'origin', 'v' => 'name']): array
	{
		return magical_converter($from, $this->cache[$this->set_eco_stays_room_types()]);
	}
	function set_eco_stays_room_types(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'eco_stays_room_types');
		if (isset($this->cache[$hash_key]) == false) {
			$res = $this->custom_db->single_table_records('eco_stays_room_types', '*', array('status' => 1));
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}
	function get_eco_stays_board_types(array $from = ['k' => 'origin', 'v' => 'name']): array
	{
		return magical_converter($from, $this->cache[$this->set_eco_stays_board_types()]);
	}
	function set_eco_stays_board_types(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'eco_stays_board_types');
		if (isset($this->cache[$hash_key]) == false) {
			$res = $this->custom_db->single_table_records('eco_stays_board_types', '*', array('status' => 1));
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}
	function get_eco_stays_amenities(array $from = ['k' => 'origin', 'v' => 'name']): array
	{
		return magical_converter($from, $this->cache[$this->set_eco_stays_amenities()]);
	}

	function set_eco_stays_amenities(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'eco_stays_amenities');
		if (isset($this->cache[$hash_key]) == false) {
			$res = $this->custom_db->single_table_records('eco_stays_amenities', '*', array('status' => 1));
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}
	function get_eco_stays_room_amenities(array $from = ['k' => 'origin', 'v' => 'name']): array
	{
		return magical_converter($from, $this->cache[$this->set_eco_stays_room_amenities()]);
	}

	function set_eco_stays_room_amenities(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'eco_stays_room_amenities');
		if (isset($this->cache[$hash_key]) == false) {
			$res = $this->custom_db->single_table_records('eco_stays_room_amenities', '*', array('status' => 1));
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}
}
