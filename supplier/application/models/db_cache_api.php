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
	public function get_country_list(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '']): mixed
	{
		//Balu A
		return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
	}

	/**
	 * set the country details
	 * @param array $condition
	 */
	public function set_country_list(array $condition): string
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
	public function get_current_balance(): mixed
	{
		//Balu A
		return $this->cache[$this->set_current_balance()];
	}

	/**
	 * set the country details
	 * @param array $condition
	 */
	public function set_current_balance(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'domain_list_balance');
		if (isset($this->cache[$hash_key]) == false) {
			$domain_id = intval(get_domain_auth_id());
			$domain_details = $this->custom_db->single_table_records('domain_list', 'balance', array('origin' => $domain_id));
			$this->cache[$hash_key] = array('value' => $domain_details['data'][0]['balance']);
		}
		return $hash_key;
	}
	/**
	 * Balu A
	 * get the Admin Base Currency
	 */
	public function get_admin_base_currency(): mixed
	{
		return $this->cache[$this->set_admin_base_currency()];
	}

	/**
	 * Set the Admin Base Currency
	 * @param array $condition
	 */
	public function set_admin_base_currency(): string
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
    /**
	 * get the postal code details
	 * @param array $from array('k' => 'id', 'v' => 'name')
	 * @param array $condition
	 */

	public function get_postal_code_list(array $from = ['k' => 'country_code', 'v' => ['name', 'country_code']], array $condition = ['name !=' => '']): mixed
	{
		return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
	}

	/**
	 * get the postal code details
	 * @param array $from array('k' => 'id', 'v' => 'name')
	 * @param array $condition
	 */

	public function get_country_code_list(array $from = ['k' => 'country_code', 'v' => ['name', 'country_code']], array $condition = ['name !=' => '']): mixed
	{
		return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
	}
	public function get_country_code_list_profile(array $condition = ['name !=' => '']): mixed
	{
		return $this->cache[$this->set_country_list($condition)];
		// return magical_converter($from, $this->cache[$this->set_country_list($condition)]);
	}

	public function get_state_list(array $from = ['k' => 'origin', 'v' => 'en_name'], array $condition = ['en_name !=' => '']): mixed
	{

		return magical_converter($from, $this->cache[$this->set_state_list($condition)]);

	}
	public function set_state_list(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'api_state_list' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('api_state_list', '*', $condition, 0, 100000000, array('en_name' => 'ASC'));
		}
		return $hash_key;
	}

	/**
	 * get the user type details
	 * @param array $from array('k' => 'id', 'v' => 'name')
	 * @param array $condition
	 */
	public function get_user_type(array $from = ['k' => 'origin', 'v' => ['user_type']], array $condition = ['user_type !=' => '', 'origin !=' => ADMIN]): mixed
	{
		//FIXME
		if (
			(isset($_GET['domain_origin']) == true && intval($_GET['domain_origin']) > 0) ||
			(isset($_GET['uid']) == true && intval($_GET['uid']) > 0) &&
			$this->entity_user_id == intval($_GET['uid'])
		) {
			//DOMAIN ADMIN CREATION BY PROVAB ADMIN (GET ONLY USER TYPE ADMIN) (OR) EDIT THEIR ACCOUNT
			//checking if it is Superadmin or Sub admin

			if ($this->entity_user_type == ADMIN) {
				$condition = array('user_type !=' => '', 'origin =' => ADMIN);
			} elseif ($this->entity_user_type == SUB_ADMIN) {
				$condition = array('user_type !=' => '', 'origin =' => SUB_ADMIN);
			}

		} else if (get_domain_auth_id() > 0) {
			//DOMAIN USERS CREATION BY DOMAIN ADMIN (GET ALL USER TYPES EXCEPT ADMIN USER TYPE)
			$condition = array('user_type !=' => '', 'origin !=' => ADMIN);
		}
		return magical_converter($from, $this->cache[$this->set_user_type($condition)]);
	}
/**
	 * set the country details
	 * @param array $condition
	 */
	public function set_user_type(array $condition): string
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
	public function get_continent_list(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '']): mixed
	{
		return magical_converter($from, $this->cache[$this->set_continent_list($condition)]);
	}

	/**
	 * set the continet details
	 * @param array $condition
	 */
	public function set_continent_list(array $condition): string
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
	public function get_city_list(array $from = ['k' => 'origin', 'v' => 'destination'], array $condition = ['destination !=' => '']): mixed
	{
		return magical_converter($from, $this->cache[$this->set_city_list($condition)]);
	}
    /**
	 * set the city details
	 * @param array $condition
	 */
	public function set_city_list(array $condition): string
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
public function get_course_type(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '']): mixed
	{
		return magical_converter($from, $this->cache[$this->set_course_type($condition)]);
	}

	/**
	 * get course type list
	 */
	public function course_type_list(array $condition): mixed
	{
		return $this->cache[$this->set_course_type($condition)];
	}

	/**
	 * set the course details
	 * @param array $condition
	 */
	public function set_course_type(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'meta_course_list' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('meta_course_list', '*', $condition, 0, 100000000, array('priority_number' => 'ASC'));
		}
		return $hash_key;
	}

	/**
	 * 	Get booking source Type
	 */
	public function get_booking_source(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = ['name !=' => '']): mixed
	{
		return magical_converter($from, $this->cache[$this->set_booking_source($condition)]);
	}
/**
	 * set the booking source details
	 * @param array $condition
	 */
	public function set_booking_source(array $condition): string
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
	public function get_currency(array $from = ['k' => 'id', 'v' => 'country'], array $condition = ['country !=' => '']): array
	{
		return magical_converter($from, $this->cache[$this->set_currency($condition)]);
	}

	/**
	 * set theCurrency details
	 * @param array $condition
	 */
	public function set_currency(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'currency_converter' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('currency_converter', '*', $condition, 0, 100000000, array('country' => 'ASC'));
		}
		return $hash_key;
	}

	
	/**
	 * 	Get airport code
	 */
	public function get_airport_code_list(): array
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
/*
	 * Return Agent List with Agent ID
	 */
	public function get_agent_list_with_id(array $from = ['k' => 'user_id', 'v' => 'agency_name'], array $condition = ['agency_name !=' => '', 'user_type' => B2B_USER]): array
	{
		return magical_converter($from, $this->cache[$this->set_agent_list($condition)]);
	}

	/**
	 * set the country details
	 * @param array $condition
	 */
	public function set_agent_list(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'agent_list' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records('user', '*', $condition, 0, 100000000, array('agency_name' => 'ASC'));
		}
		return $hash_key;
	}

	public function get_group_list(array $from = ['k' => 'origin', 'v' => 'name'], array $condition = []): array
	{
		return magical_converter($from, $this->cache[$this->set_group_list($condition)]);
	}

	public function set_group_list(array $condition): string
	{
		$hash_key = hash('md5', __CLASS__ . 'user_groups' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			$this->cache[$hash_key] = $this->custom_db->single_table_records(
				'user_groups',
				'*',
				$condition,
				0,
				100000000,
				array(
					'origin' => 'ASC'
				)
			);
		}
		return $hash_key;
	}

	public function get_distributor_list(array $from = ['k' => 'user_id', 'v' => ['uuid', 'agency_name']], array $condition = ['user_type' => DIST_USER]): array
	{
		// Arjun J Gowda
		return magical_converter($from, $this->cache[$this->set_distributor_list($condition)]);
	}

	public function set_distributor_list(array $condition): string
	{$res=[];
		$hash_key = hash('md5', __CLASS__ . 'user' . json_encode($condition));
		if (isset($this->cache[$hash_key]) == false) {
			// debug($condition);exit;
			$res['data'] = $this->db->join('dist_user_details', 'dist_user_details.user_oid = user.user_id')->where_in('user.creation_source', [
				'superadmin',
				'admin',
				'subadmin'
			])->get_where('user', $condition)->result_array();
			$res['status'] = SUCCESS_STATUS;
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}

	public function get_all_api_country_list(array $from = ['k' => 'country_code', 'v' => 'country_name']): array
	{
		$all_api_country_list = $this->cache[$this->set_all_api_country_list()];
		return magical_converter($from, $all_api_country_list);
	}
public function set_all_api_country_list(): string
	{$res=[];
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

	public function get_eco_stays_types(array $from = ['k' => 'origin', 'v' => 'name']): array
	{
		return magical_converter($from, $this->cache[$this->set_eco_stays_types()]);
	}

	public function set_eco_stays_types(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'eco_stays_types');
		if (isset($this->cache[$hash_key]) == false) {
			$res = $this->custom_db->single_table_records('eco_stays_types', '*', array('status' => 1));
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}

	public function get_eco_stays_amenities(array $from = ['k' => 'origin', 'v' => 'name']): array
	{
		return magical_converter($from, $this->cache[$this->set_eco_stays_amenities()]);
	}

	public function set_eco_stays_amenities(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'eco_stays_amenities');
		if (isset($this->cache[$hash_key]) == false) {
			$res = $this->custom_db->single_table_records('eco_stays_amenities', '*', array('status' => 1));
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}


	public function get_eco_stays_room_types(array $from = ['k' => 'origin', 'v' => 'name']): array
	{
		return magical_converter($from, $this->cache[$this->set_eco_stays_room_types()]);
	}

	public function set_eco_stays_room_types(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'eco_stays_room_types');
		if (isset($this->cache[$hash_key]) == false) {
			$res = $this->custom_db->single_table_records('eco_stays_room_types', '*', array('status' => 1));
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}

	public function get_eco_stays_room_amenities(array $from = ['k' => 'origin', 'v' => 'name']): array
	{
		return magical_converter($from, $this->cache[$this->set_eco_stays_room_amenities()]);
	}

	public function set_eco_stays_room_amenities(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'eco_stays_room_amenities');
		if (isset($this->cache[$hash_key]) == false) {
			$res = $this->custom_db->single_table_records('eco_stays_room_amenities', '*', array('status' => 1));
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}

	public function get_eco_stays_board_types(array $from = ['k' => 'origin', 'v' => 'name']): array
	{
		return magical_converter($from, $this->cache[$this->set_eco_stays_board_types()]);
	}

	public function set_eco_stays_board_types(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'eco_stays_board_types');
		if (isset($this->cache[$hash_key]) == false) {
			$res = $this->custom_db->single_table_records('eco_stays_board_types', '*', array('status' => 1));
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}

	public function get_eco_stays_room_meal_types(array $from = ['k' => 'origin', 'v' => 'name']): array
	{
		return magical_converter($from, $this->cache[$this->set_eco_stays_room_meal_types()]);
	}

	public function set_eco_stays_room_meal_types(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'eco_stays_room_meal_types');
		if (isset($this->cache[$hash_key]) == false) {
			$res = $this->custom_db->single_table_records('eco_stays_room_meal_types', '*', array('status' => 1));
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}

	public function get_suppliers(array $from = ['k' => 'user_id', 'v' => 'user']): array
	{
		return magical_converter($from, $this->cache[$this->set_suppliers()]);
	}

	public function set_suppliers(): string
	{
		$hash_key = hash('md5', __CLASS__ . 'suppliers');
		if (isset($this->cache[$hash_key]) == false) {
			$res = $this->custom_db->single_table_records('user', "user_id, CONCAT((first_name), (' '), (last_name)) AS name,  email AS email", array('user_type' => SUPPLIER));

			if($res['status'] == true){
				foreach($res['data'] as $k => $data){
					$res['data'][$k]['user'] = $data['name'] . ' - ' . provab_decrypt($data['email']);
				}
			}
			$this->cache[$hash_key] = $res;
		}
		return $hash_key;
	}

	
}