<?php
/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Api_Model
 * @author     Arjun J<arjunjgowda260389@gmail.com>
 * @version    V2
 */
class Api_Model extends CI_Model {
	/**
	 * Get active configuration
	 * 
	 * @param string $module
	 *        	- Code of module for which booking api config has to be loaded
	 * @param string $api
	 *        	- API for which config has to be browsed // Array or String
	 */
	function active_config($module, $api) {
		$source_filter = '';
		if (is_array ( $api ) == true) {
			// group to IN
			$tmp_api = '';
			foreach ( $api as $k => $v ) {
				$tmp_api .= $this->db->escape ( $v ) . ',';
			}
			$tmp_api = substr ( $tmp_api, 0, - 1 ); // remove last ,
			$source_filter = 'BS.source_id IN (' . $tmp_api . ')';
		} else {
			// Single value direct =
			$source_filter = 'BS.source_id = ' . $this->db->escape ( $api );
		}
		// Meta_course_list, booking_source, activity_source_map, api_config
		$query = 'SELECT AC.config, BS.source_id AS api,AC.remarks FROM meta_course_list MCL, booking_source BS, activity_source_map ASM, api_config AC
		WHERE MCL.origin=ASM.meta_course_list_fk AND ASM.booking_source_fk=BS.origin AND ASM.status=' . ACTIVE . ' AND MCL.status=' . ACTIVE . '
		AND BS.origin=AC.booking_source_fk AND MCL.status=' . ACTIVE . ' AND MCL.course_id = ' . $this->db->escape ( $module ) . '
		AND ' . $source_filter . ' AND AC.status=' . ACTIVE;
       
        	//echo $query;exit;
       

		$result_arr = $this->db->query ( $query )->result_array ();
		//debug($result_arr);
		if (valid_array ( $result_arr ) == true) {
			$resp = array ();
			foreach ( $result_arr as $k => $v ) {
				$v ['config'] = $this->decrypt_api_data($v ['config']);
				//debug($v);exit;
				
				if($v ['remarks'] == 'Hotelbeds-Test'){
					$v['config'] = '{
  "agent_id": "travelomatix",
  "api_url": "https://api.test.hotelbeds.com/hotel-api/1.0/",
  "api_content_url":"https://api.test.hotelbeds.com/hotel-content-api/1.0/types/",
  "api_key":"a0b9ad5415cdd05233ec69cda9ead0bc",
  "secrete":"f88d5cdec0",
  "medium_image_base_url":"http://photos.hotelbeds.com/giata/bigger/",
  "small_image_base_url":"http://photos.hotelbeds.com/giata/small/",
  "currency":"INR"
	
}';
				}
				if($v ['remarks'] == 'Amadeus Flight'){
				$v ['config'] ='{
  "WSAP": "1ASIWGURRDE",
  "Api_URL": "https://noded1.test.webservices.amadeus.com/1ASIWGURRDE",
  "Username": "WSRDEGUR",
  "Password": "G*E6zx3j",
  "POS_Type": 1,
  "AgentDutyCode": "SU",
  "RequestorType": "U",
  "Fare_MasterPricerTravelBoardSearch":"FMPTBQ_14_3_1A"
}';
}
				if($v ['remarks'] == 'carnect-Test'){
					$v['config'] = '{
  "login_id": "Accentria",
  "password": "Ac3ntr1a$$18",
  "api_url": "https://ota2007a.micronnexus-staging.com/service.asmx"
}';
				}
		
				//debug($v ['config'] );exit;
				$resp [$v ['api']] = array (
						'config' => $v ['config'],
						'remarks' => $v ['remarks'], 
				);
			}
			return $resp;
		} else {
			return false;
		}
	}
	
	/**
	 * return active api config for one api only
	 * 
	 * @param string $module
	 *        	- Code of module for which booking api config has to be loaded
	 * @param string $api
	 *        	- API for which config has to be browsed // Array or String
	 */
	function active_api_config($module, $api) {
		$data = $this->active_config ( $module, $api );
		if ($data != FAILURE_STATUS) {
			return $data [$api];
		} else {
			return false;
		}
	}
	/**
	 * 
	 * Set API Session ID
	 * @param unknown_type $booking_source_fk
	 */
	public function update_api_session_id($booking_source, $session_id)
	{
		$booking_source_details = $this->db->query('select origin from booking_source where source_id="'.trim($booking_source).'"')->row_array();
		$booking_source_fk = $booking_source_details['origin'];
		$this->custom_db->update_record('api_session_id', array('session_id' => trim($session_id), 'last_updated_datetime' => db_current_datetime()), array('booking_source_fk' => intval($booking_source_fk)));
	}
	/**
	 * 
	 * Return API Session ID
	 * @param unknown_type $booking_source_fk
	 */
	public function get_api_session_id($booking_source, $session_expiry_time)
	{
		$session_id_details = $this->db->query('select ASI.session_id from api_session_id ASI
							join booking_source BS on BS.origin=ASI.booking_source_fk 
							where BS.source_id="'.$booking_source.'" and (ASI.last_updated_datetime + INTERVAL '.intval($session_expiry_time).' MINUTE) >= "'.db_current_datetime().'"')->row_array();
		if(isset($session_id_details['session_id']) == true && empty($session_id_details['session_id']) == false){
			return $session_id_details['session_id'];
		}
	}
	/**
	 * Stores Client Requests
	 */
	public  function store_client_request($request_type='', $request='', $module_type='')
	{
		//TODO:$this->inactive_cache_services
		if($request_type !=''){
			if(is_array($request)) {
				$request = json_encode($request);
			}
			$provab_api_request_history = array();
			$provab_api_request_history['request_type'] = $request_type;
			$provab_api_request_history['request'] = $request;
			$provab_api_request_history['module_type'] = $module_type;
			$provab_api_request_history['domain_origin'] = get_domain_auth_id();
			$provab_api_request_history['created_datetime'] = date('Y-m-d H:i:s');
			
			return $this->custom_db->insert_record('provab_api_request_history',$provab_api_request_history);
		}
	}
            /**
	 * Stores Client return response
	 */
        public function store_client_return_response($request_type='', $request='', $response='')
	{
             
		//TODO:$this->inactive_cache_services
		if($request_type !=''){
			if(is_array($request)) {
				$request = json_encode($request);
			}
                        if(is_array($response)) {
				$response = json_encode($response);
			}
                        $provab_api_request_history = array();
			$provab_api_request_history['request_type'] = $request_type;
                        $provab_api_request_history['request'] = $request;
                        $provab_api_request_history['response'] = $response;
			$provab_api_request_history['domain_origin'] = get_domain_auth_id();
			$provab_api_request_history['created_datetime'] = date('Y-m-d H:i:s');
                       
			return $this->custom_db->insert_record('provab_api_return_response_history',$provab_api_request_history);
                       
		}
	}
	/**
	 * Stores API Requests
	 */
	public  function store_api_request(string $request_type, string $request, string $remarks, string $server_info='', int $search_id=0)
	{
		//TODO:$this->inactive_cache_services
		if($request_type !=''){
			if(is_array($request)) {
				$response = json_encode($request);
			}
			$provab_api_response_history = array();
			$provab_api_response_history['request_type'] = $request_type;
			$provab_api_response_history['request'] = $request;
			$provab_api_response_history['remarks'] = $remarks;
                        $provab_api_response_history['domain_origin'] = get_domain_auth_id();
                        $provab_api_response_history['search_id'] = $search_id;
			$provab_api_response_history['created_datetime'] = date('Y-m-d H:i:s');
			return $this->custom_db->insert_record('provab_api_response_history',$provab_api_response_history);
		}
	}
	/**
	 * Stores Travelport API Requests
	 */
	public function store_api_request_booking($request_type, $request, $response, $remarks){
		//TODO:$this->inactive_cache_services
		if($request_type !=''){
			if(is_array($request)) {
				$response = json_encode($request);
			}
			$provab_api_response_history = array();
			$provab_api_response_history['request_type'] = $request_type;
			$provab_api_response_history['request'] = $request;
			$provab_api_response_history['response'] = $response;
			$provab_api_response_history['remarks'] = $remarks;
                         $provab_api_response_history['domain_origin'] = get_domain_auth_id();
			$provab_api_response_history['created_datetime'] = date('Y-m-d H:i:s');
			return $this->custom_db->insert_record('provab_api_response_history',$provab_api_response_history);
		}
	}
	/**
	 * Stores API Requests
	 */
	public  function update_api_response($response, $origin, $totaltime=0, $header='')
	{
		//TODO:$this->inactive_cache_services
		if(intval($origin) > 0){
			if(is_array($response)) {
				$response = json_encode($response);
			}
			$provab_api_response_history = array();
			$provab_api_response_history['response'] = $response;
                        $provab_api_response_history['flight_api_response'] = $totaltime;
			$provab_api_response_history['response_updated_time'] = date('Y-m-d H:i:s');
			$provab_api_response_history['header'] = $header;
			$this->custom_db->update_record('provab_api_response_history',$provab_api_response_history, array('origin' => intval($origin)));
		}
	}
	/**
	 * Checks Cache is enabled for Service 
	 * Enter description here ...
	 */
	public function inactive_client_cache_services($service_name)
	{
		$inactive_cache = array('SEARCH', 'GETCALENDARFARE', 'FARERULE');
		//$inactive_cache = array();
		if(in_array(strtoupper($service_name), $inactive_cache) == true){
			return true;
		} else {
			return false;
		}
	}
	
	public function inactive_api_cache_services()
	{
		
	}
	public function decrypt_api_data($string){
		$secret_iv = SEC_VALUE;
		
		$output = false;
    	$encrypt_method = "AES-256-CBC";
		if(!empty($string)){
			$md5_key = MD5_VALUE;
			$encrypt_key = ENCRYPT_KEY;
			$decrypt_password = $this->db->query("SELECT AES_DECRYPT($encrypt_key,SHA2('".$md5_key."',512)) AS decrypt_data");
			
			$db_data = $decrypt_password->row();
			$secret_key = trim($db_data->decrypt_data);	
			$key = hash('sha256', $secret_key);
		    $iv = substr(hash('sha256', $secret_iv), 0, 16);
		   	$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
			return $output;
		}
	}
	public function get_user_count_per_day_per_user($domain_origin){

$from=date('Y-m-d').' 00:00:00';
$to=date('Y-m-d').' 23:59:59';

		$get_hits_details = $this->db->query('SELECT count(PRH.origin) as totalcount FROM `provab_api_request_history` as PRH  where PRH.created_datetime BETWEEN "'.$from.'" and "'.$to.'" and PRH.domain_origin="'.$domain_origin.'" group by PRH.domain_origin')->result_array();
		
		return $get_hits_details;
		
	}
	function get_allowed_lmit()
	{
		$session_id_details = $this->db->query('select limit_request from set_request_limit')->row_array();
		return $session_id_details;
	}
	function get_allowed_lmit_domain($domain_origin)
	{

		/*$session_id_details = $this->db->query('select limit_request from set_request_limit where domain_origin='.$domain_origin.'')->row_array();
		return $session_id_details;*/
		$session_id_details = $this->db->query('select limit_request from set_request_limit')->row_array();
		return $session_id_details;
	}
	/**
	 * 
	 * Set API Session ID
	 * @param unknown_type $booking_source_fk
	 */
	public function update_api_session_id_sabre($booking_source, $session_id, $last_updated_datetime)
	{
		
		$booking_source_details = $this->db->query('select origin from booking_source where source_id="'.trim($booking_source).'"')->row_array();
		$booking_source_fk = $booking_source_details['origin'];
		$this->custom_db->update_record('api_session_id', array('session_id' => trim($session_id), 'last_updated_datetime' => $last_updated_datetime), array('booking_source_fk' => intval($booking_source_fk)));
	}
}