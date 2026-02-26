<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab - Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */

class Utilities extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
	}

	function currency_converter(int $value = 0, int $id_val = 0): void
	{
	    $data = [];

	    if (intval($id_val) > 0 && intval($value) > -1) {
	        $data['value'] = $value;
	        $this->custom_db->update_record('currency_converter', $data, array('id' => $id_val));
	        return;
	    }

	    $currency_data = $this->custom_db->single_table_records('currency_converter');
	    $data['converter'] = $currency_data['data'];
	    $this->template->view('utilities/currency_converter', $data);
	}


	function auto_currency_converter():void
	{
		$data = [];
		$data_set = $this->custom_db->single_table_records('currency_converter');
		if ($data_set['status'] == true) {
			$from = COURSE_LIST_DEFAULT_CURRENCY_VALUE;
			$data['date_time'] = date('Y-m-d H:i:s');
			foreach ($data_set['data'] as $v) {
				$url = 'http://download.finance.yahoo.com/d/quotes.csv?s='.$v['country'].$from.'=X&f=nl1';
				$handle = fopen($url, 'r');
				if ($handle) {
					$currency_data = fgetcsv($handle);
					fclose($handle);
				}
				if ($currency_data != '') {
					if (isset($currency_data[0]) == true and empty($currency_data[0]) == false and isset($currency_data[1]) == true and empty($currency_data[1]) == false) {
						$data['value'] = $currency_data[1];
						$this->custom_db->update_record('currency_converter', $data, array('id' => $v['id']));
					}
				}
			}
		}
		redirect('utilities/currency_converter');
	}

	/**
	 * Load All Events Of Trip Calendar
	 */
	function trip_calendar():void
	{
		$this->template->view('utilities/trip_calendar');
	}

	function app_settings():void
	{
		$this->template->view('utilities/app_settings');
	}

	/**
	 * Show time line to user previous one month - Load Last one month by default
	 */
	function timeline():void
	{
		$this->template->view('utilities/timeline');
	}

	/**
	 * Get All The Events Between Two Dates
	 */
	function timeline_rack():void
	{
		$response = [];
		$response['status'] = FAILURE_STATUS;
		$response['data'] = array();
		$response['msg'] = '';
		$params = $this->input->get();
		$oe_start = intval($params['oe_start']);
		$event_limit = intval($params['oe_limit']);
		if ($oe_start > -1 and $event_limit > -1) {
			//Older Events
			$oe_list = $this->application_logger->get_events($oe_start, $event_limit);
			if (valid_array($oe_list) == true) {
				$response['oe_list'] = get_compressed_output($this->template->isolated_view('utilities/core_timeline', array('list' => $oe_list)));
				$response['status'] = SUCCESS_STATUS;
			}
		}
		header('Content-type:application/json');
		echo json_encode($response);
		exit;
	}

	/**
	 * Get All The Events Between Two Dates
	 */
	public function latest_timeline_events(): void
	{
	    session_write_close(); // Prevent session lock

	    $response = [
	        'status' => FAILURE_STATUS,
	        'data' => [],
	        'msg' => ''
	    ];

	    $params = $this->input->get();
	    $last_event_id = intval($params['last_event_id'] ?? -1);

	    if ($last_event_id <= -1) {
	        $this->send_json_response($response);
	        return;
	    }

	    $cond = [['TL.origin', '>', $last_event_id]];

	    // Poll until we have events
	    while ($response['status'] === false) {
	        $os_list = $this->application_logger->get_events(0, 10000000000, $cond);

	        if (valid_array($os_list)) {
	            $response['oa_list'] = get_compressed_output(
	                $this->template->isolated_view('utilities/core_timeline', ['list' => $os_list])
	            );
	            $response['status'] = SUCCESS_STATUS;
	            break; // No need to continue once data is found
	        }

	        // No events yet, pause before next poll
	        sleep(3);
	    }

	    $this->send_json_response($response);
	}

	private function send_json_response(array $response): void
	{
	    header('Content-Type: application/json');
	    echo json_encode($response);
	    exit;
	}
	/**
	 * Set Preferred currency to be used in the application
	 * @param unknown_type $currency
	 */
	function set_preferred_currency(sring $currency):void
	{
		$this->session->set_userdata(array('currency' => $currency));
		header('Content-type:application/json');
		// echo json_encode(array('status' => SUCCESS_STATUS));
		$curr_symbol = $this->currency->get_currency_symbol($currency);
		
		echo json_encode(array('status' => SUCCESS_STATUS,'currency' => $currency,'curr_symbol' => $curr_symbol));
		exit;
	}
	function change_currency_based_on_ip():void{
	    $ip_address  = '14.141.47.106';
		//$ip ='19.141.47.106';
		// $ip='2.175.255.255';
		//$ip = $_SERVER['REMOTE_ADDR'];
		$curl = curl_init();	
		//curl_setopt($ch, CURLOPT_URL, "http://ipinfo.io/{$ip}");
		curl_setopt($curl, CURLOPT_URL, "http://www.geoplugin.net/php.gp?ip=$ip_address");		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);		
		$get_resulted_data = unserialize($output);
		if(empty($this->session->userdata('currency'))){		
			$this->set_preferred_currency($get_resulted_data['geoplugin_currencyCode']);
		}
	}
}
