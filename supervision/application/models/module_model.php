<?php

/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */
class Module_Model extends CI_Model
{
	/**
	 * get complete domain details
	 */
	function domain_details(int $domain_origin): array
	{
		$query = 'select DL.*, group_concat(DMM.meta_course_list_fk separator "' . DB_SAFE_SEPARATOR . '") as domain_modules from domain_list AS DL left join domain_module_map DMM ON DMM.domain_list_fk=DL.origin
		where DL.origin=' . intval($domain_origin) . ' GROUP BY DL.origin';
		return $this->db->query($query)->result_array();
	}

	/**
	 * Domain Module Map Creation
	 * @param $domain_origin
	 * @param $module_origin
	 */
	function create_domain_module_map(int $domain_origin, array $module_origin): void
	{
		$domain_module_map=[];
		if (is_array($module_origin) == false) {
			$module_origin = (array)$module_origin;
		}
		$domain_module_map['domain_list_fk'] = intval($domain_origin);
		$domain_module_map['status'] = ACTIVE;
		$domain_module_map['created_by_id'] = intval($this->entity_user_id);
		$domain_module_map['created_datetime'] = date('Y-m-d H:i:s');
		foreach ($module_origin as $v) {
			$domain_module_map['meta_course_list_fk'] = intval($v);
			$this->custom_db->insert_record('domain_module_map', $domain_module_map);
		}
	}
	/**
	 * Get Module List
	 * Balu A
	 */
	function module_management(int $pk, string $course_id): array
	{
		$tmp_data = $this->db->query("SELECT MCL.*,BS.origin as booking_source FROM meta_course_list MCL
		                            LEFT JOIN activity_source_map ASM ON ASM.meta_course_list_fk=MCL.origin
		                            LEFT JOIN booking_source BS ON BS.origin=ASM.booking_source_fk 
		                            where MCL.origin=$pk AND MCL.course_id='$course_id' 
		                            group by BS.origin ");
		if ($tmp_data->num_rows() > 0) {
			$tmp_data = $tmp_data->result_array();
			$data = array('status' => QUERY_SUCCESS, 'data' => $tmp_data);
		} else {
			$data = array('status' => QUERY_FAILURE);
		}
		return $data;
	}
	/**
	 * Get booking Source List
	 * Balu A
	 */
	function booking_source_management(int $pk, string $course_id): array
	{
		$tmp_data = $this->db->query("SELECT BS.*,MCL.origin as booking_source FROM booking_source BS
		                            LEFT JOIN activity_source_map ASM ON ASM.booking_source_fk=BS.origin
		                            LEFT JOIN meta_course_list MCL ON MCL.origin=ASM.meta_course_list_fk 
		                            where BS.origin=$pk AND BS.source_id='$course_id' 
		                            group by BS.origin ");
		if ($tmp_data->num_rows() > 0) {
			$tmp_data = $tmp_data->result_array();
			$data = array('status' => QUERY_SUCCESS, 'data' => $tmp_data);
		} else {
			$data = array('status' => QUERY_FAILURE);
		}
		return $data;
	}
	function get_gst_details(): array
	{
		$tmp_data = $this->db->query("SELECT gst.*,MCL.name as name FROM gst_master gst
		                            LEFT JOIN meta_course_list MCL ON MCL.origin=gst.meta_course_list_fk where MCL.status=1");
		if ($tmp_data->num_rows() > 0) {
			$tmp_data = $tmp_data->result_array();
			$data = array('status' => QUERY_SUCCESS, 'data' => $tmp_data);
		} else {
			$data = array('status' => QUERY_FAILURE);
		}
		return $data;
	}
	/**
	 *  Getting sms checkpoint details
	 */
	function get_sms_checkpoint(): array
	{
		$query = 'select * from sms_checkpoint';
		return $this->db->query($query)->result_array();
	}
	/**
	 * update sms_checkpoint_status
	 */
	function update_sms_checkpoint_status(string $status, string $condition): void
	{
		$data = array(
			'status' => $status
		);
		$this->db->where('condition', $condition);
		$this->db->update('sms_checkpoint', $data);
	}
	/**
	 * Domain list
	 */
	function get_module_list(): array
	{
		$query = 'select * from active_modules';
		return $this->db->query($query)->result_array();
	}
	/**
	 * update module_status
	 */
	function update_module_status(string $status, int $condition): void
	{
		$data = array(
			'status' => $status
		);
		$this->db->where('origin', $condition);
		$this->db->update('active_modules', $data);
	}
	/**
	 * update social_link
	 */
	function update_social_link_status(string $status, int $condition): void
	{
		$data = array(
			'status' => $status
		);
		$this->db->where('origin', $condition);
		$this->db->update('social_links', $data);
	}
	function update_social_url(string $url, int $id): void
	{
		$data = array(
			'url_link' => $url
		);
		$this->db->where('origin', $id);
		$this->db->update('social_links', $data);
		//echo $this->db->last_query();exit;
	}
	function update_social_config(string $url, int $id): void
	{
		$data = array(
			'config' => $url
		);
		$this->db->where('origin', $id);
		$this->db->update('social_login', $data);
		echo $this->db->last_query();
		exit;
	}
	/**
	 * update social_login /status
	 */
	function update_social_login_status(string $status, int $condition): void
	{
		$data = array(
			'status' => $status
		);
		$this->db->where('origin', $condition);
		$this->db->update('social_login', $data);
	}
	function update_social_login_name(string $url, int $id): void
	{
		$data = array(
			'social_login_name' => $url
		);
		$this->db->where('origin', $id);
		$this->db->update('social_login', $data);
	}
	/**
	 * Update Hotel Top Destination
	 */
	function update_top_destination(string $status, int $origin): void
	{
		$data = array(
			'status' => $status
		);
		$this->db->where('origin', $origin);
		$this->db->update('all_api_city_master', $data);
	}
	/**
	 * Update Bus Top Destination
	 */
	function update_bus_top_destination(string $status, int $origin): void
	{
		$data = array(
			'status' => $status
		);
		$this->db->where('origin', $origin);
		$this->db->update('bus_stations', $data);
	}
	/**
	 * Update Flight Top Destination
	 */
	function update_flight_top_destination(string $status, int $origin): void
	{
		$data = array(
			'status' => $status
		);
		$this->db->where('origin', $origin);
		$this->db->update('top_flight_destinations', $data);
	}
	/**
	 * Balu A
	 * Booking source Details
	 */
	function get_course_list(array $condition = array()): array
	{
		// $filter = '';
		$filter_condition = ' WHERE 1=1 and ';
		//	if (is_domain_user() == true) {
		//$filter_condition .= ' ASM.domain_origin='.intval(get_domain_auth_id()).' and ';
		//		}
		if (valid_array($condition) == true) {
			foreach ($condition as $v) {
				$filter_condition .= implode($v) . ' and ';
			}
		}
		$filter_condition = rtrim($filter_condition, 'and ');
		$query = 'SELECT MCL.*, CONCAT(U.first_name, " ", U.last_name, "-", U.uuid) AS username, U.image as user_image
		
		FROM meta_course_list AS MCL
		 JOIN user AS U ON MCL.created_by_id=U.user_id
		
		' . $filter_condition . ' order by MCL.origin asc';
		//echo $query;exit;
		return $this->db->query($query)->result_array();
	}
	/**
	 * Balu A
	 * Booking source Details
	 */
	function get_booking_source_list(array $condition = array()): array
	{
		// $filter = '';
		$filter_condition = ' WHERE 1=1 and ';
		//	if (is_domain_user() == true) {
		$filter_condition .= ' ASM.domain_origin=' . intval(get_domain_auth_id()) . ' and ';
		//		}
		if (valid_array($condition) == true) {
			foreach ($condition as $v) {
				$filter_condition .= implode($v) . ' and ';
			}
		}
		$filter_condition = rtrim($filter_condition, 'and ');
		$query = 'SELECT BS.*,MCL.name as meta_course_name
		FROM booking_source AS BS
		 JOIN user AS U ON BS.created_by_id=U.user_id
		 LEFT JOIN activity_source_map ASM ON ASM.booking_source_fk=BS.origin
		 LEFT JOIN meta_course_list MCL ON MCL.origin=ASM.meta_course_list_fk
		' . $filter_condition . ' GROUP BY BS.source_id order by BS.origin';
		//echo $query;exit;
		return $this->db->query($query)->result_array();
	}
	/**
	 * Get active module list for domain
	 * @param $domain_key		unique origin key of domain
	 * @param $domain_auth_id	unique auth provab key for domain
	 */
	function get_active_module_list(int $domain_origin, string $domain_key): array
	{
		$active_module_list = array();
		$query = 'select group_concat(MCL.course_id separator "' . DB_SAFE_SEPARATOR . '") as domain_module from domain_module_map AS DMM, domain_list AS D, meta_course_list AS MCL
		WHERE DMM.domain_list_fk=D.origin AND DMM.meta_course_list_fk=MCL.origin AND D.origin=' . intval($domain_origin) . ' AND
		D.domain_key=' . $this->db->escape($domain_key) . ' AND DMM.status=' . intval(ACTIVE) . ' AND D.status=' . intval(ACTIVE) . ' AND MCL.status=' . intval(ACTIVE) . ' GROUP BY D.origin';

		$active_module_list = $this->db->query($query)->row_array();
		if (isset($active_module_list['domain_module'])) {
			$active_module_list = explode(DB_SAFE_SEPARATOR, $active_module_list['domain_module']);
		}

		return $active_module_list;
	}
	/**
	 * Balu A
	 */
	public function active_module_name_list(int $domain_origin, string $domain_key): array
	{
		$active_module_list = array();
		$active_module_names = array();
		$query = 'select group_concat(concat(MCL.course_id,"||",MCL.name) separator "' . DB_SAFE_SEPARATOR . '") as domain_module_name from domain_module_map AS DMM, domain_list AS D, meta_course_list AS MCL
		WHERE DMM.domain_list_fk=D.origin AND DMM.meta_course_list_fk=MCL.origin AND D.origin=' . intval($domain_origin) . ' AND
		D.domain_key=' . $this->db->escape($domain_key) . ' AND DMM.status=' . intval(ACTIVE) . ' AND D.status=' . intval(ACTIVE) . ' AND MCL.status=' . intval(ACTIVE) . ' GROUP BY D.origin';
		$active_module_list = $this->db->query($query)->row_array();
		if (isset($active_module_list['domain_module_name'])) {
			$active_module_list = explode(DB_SAFE_SEPARATOR, $active_module_list['domain_module_name']);
			foreach ($active_module_list as $v) {
				$module_details = explode('||', $v);
				$active_module_names[$module_details[0]] = $module_details[1];
			}
		}
		return $active_module_names;
	}
	/**
	 * Balu A
	 */
	public function promocode_module_options(): array
	{
		$promocode_module_options = array();
		$domain_origin = $this->session->userdata(DOMAIN_AUTH_ID);
		$domain_key = base64_decode($this->session->userdata(DOMAIN_KEY));
		$active_module_names = $this->active_module_name_list($domain_origin, $domain_key);
		$inactive_module = array(META_PACKAGE_COURSE);

		foreach ($active_module_names as $k => $v) {

			if (in_array($k, $inactive_module) == false) {
				switch ($k) {
					case META_AIRLINE_COURSE:
						$module_name_value = 'flight';
						break;
					case META_ACCOMODATION_COURSE:
						$module_name_value = 'hotel';
						break;
					case META_BUS_COURSE:
						$module_name_value = 'bus';
						break;
					case META_SIGHTSEEING_COURSE:
						$module_name_value = 'activities';
						break;
					case META_TRANSFERV1_COURSE:
						$module_name_value = 'transfers';
						break;

					case META_CAR_COURSE:
						$module_name_value = 'car';
						break;
				}

				$promocode_module_options[$module_name_value] = $v;;
			}
		}

		return $promocode_module_options;
	}
	/**
	 * Balu A
	 * @param $condition
	 * @param $count
	 * @param $offset
	 * @param $limit
	 */
	function promo_code_list(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array
	{
		$condition = $this->custom_db->get_custom_condition($condition);
		if (!$count) {
			$query = 'select * from promo_code_list where 1=1' . ' ' . $condition . ' order by status desc,origin desc limit ' . $offset . ', ' . $limit;
			return $this->db->query($query)->result_array();
		} else {
			$query = 'select count(origin) as total_records from promo_code_list where 1=1' . ' ' . $condition;

			$data = $this->db->query($query)->row_array();
			return (array)$data['total_records'];
		}
	}
	/**
	 * Balu A
	 * Check Promo Codes, is exists already or not?
	 */
	function is_unique_promocode(string $promo_code): ?array
	{
		$query = 'select * from promo_code_list where promo_code="' . trim($promo_code) . '"';
		return $this->db->query($query)->row_array();
	}
	/**
	 * Balu A
	 */
	function auto_suggest_promo_code(string $chars): array
	{
		$query = 'select promo_code, module from promo_code_list where promo_code like "%' . trim($chars) . '%"';
		return $this->db->query($query)->result_array();
	}
	public function delete_promo_code(int $origin): void
	{

		$this->db->where('origin', $origin);
		$this->db->delete('promo_code_list');
	}
	/**
	 * Anitha G
	 *
	 * Sub Admin Privillages
	 */

	function get_user_privillages(): array
	{
		$user = $this->entity_user_id;
		$user_type = $this->entity_user_type;
		$query = 'select PL.privilege_key,PL.url from privilege_list_new PL JOIN privileges AS P ON P.p_no = PL.origin where P.user_type = ' . $user_type . ' AND P.user_id = ' . $user;
		// echo $query;exit;
		$privileges = $this->db->query($query)->result_array($query);
		//foreach ()

		// debug($privileges);exit;		
		return $privileges;
	}
	function get_course_list_new(array $condition = array()): array
	{
		$CI = &get_instance();
		$this->db1 = $CI->load->database('seconddb', TRUE);

		// $filter = '';
		$filter_condition = ' WHERE 1=1 and ';

		if (valid_array($condition) == true) {
			foreach ($condition as $v) {
				$filter_condition .= implode($v) . ' and ';
			}
		}
		$filter_condition = rtrim($filter_condition, 'and ');
		//debug($filter_condition);die;
		$query = 'SELECT MCL.*, group_concat(BS.name separator "' . DB_SAFE_SEPARATOR . '") as booking_source, group_concat(BS.origin separator "' . DB_SAFE_SEPARATOR . '") as bs_origin, 
		group_concat(ASM.meta_course_list_fk separator "' . DB_SAFE_SEPARATOR . '") as meta_course,
		group_concat(concat(BS.name , " ", ASM.status) separator "' . DB_SAFE_SEPARATOR . '") as asm_status_label,
		group_concat(BS.source_id separator "' . DB_SAFE_SEPARATOR . '") as booking_source_id, group_concat(ASM.status separator "' . DB_SAFE_SEPARATOR . '") as asm_status,group_concat(ASM.status separator "' . DB_SAFE_SEPARATOR . '") as asm_status
		FROM meta_course_list AS MCL
		 LEFT JOIN activity_source_map ASM ON ASM.meta_course_list_fk=MCL.origin
		 LEFT JOIN booking_source BS ON BS.origin=ASM.booking_source_fk
		' . $filter_condition . ' GROUP BY MCL.course_id order by MCL.priority_number ASC';
		//echo $query;exit;
		return $this->db1->query($query)->result_array();
	}
}
