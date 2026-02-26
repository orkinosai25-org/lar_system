<?php if (!defined('BASEPATH'))
	exit('No direct script access allowed');

// ------------------------------------------------------------------------
/**
 * Controller for all ajax activities
 *
 * @package    Provab
 * @subpackage ajax loaders
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */
// ------------------------------------------------------------------------

class Ajax extends CI_Controller
{
	private $current_module;
	public function __construct()
	{
		parent::__construct();
		if (is_ajax() == false) {
			//$this->index();
		}
		ob_start();
		$this->current_module = $this->config->item('current_module');
	}
/**
	 * index page of application will be loaded here
	 */
	function index(): void
	{

	}

	/**
	 * get city list based on country
	 * @param $country_id
	 * @param $default_select
	 */
	function get_city_list(int $country_id = 0): void
	{
        $resp=[];
		$default_value = $this->input->post('default_value');
		if (empty($default_value) == false) {
			$default_value = json_decode($default_value);
		} else {
			$default_value = '';
		}
		$resp['data'] = array();
		if ($country_id != '') {
			$condition = array('country_code' => $country_id);
			$order_by = array('city_name' => 'asc');
			$option_list = $this->custom_db->single_table_records('all_api_city_master', 'origin as k, city_name as v', $condition, 0, 1000000, $order_by);

			if (valid_array($option_list['data'])) {
				$resp['data'] = get_compressed_output(generate_options($option_list['data'], $default_value, true));
			}
		}
		header('Content-type:application/json');
		echo json_encode($resp);
		exit;
	}

	/**
	 *
	 * @param $continent_id
	 * @param $default_select
	 * @param $zone_id
	 */
	function get_country_list(int $continent_id = 0, int $default_select = 0, int $zone_id = 0): void
	{
		$this->load->model('general_model');
		$continent_id = urldecode($continent_id);
		if (intval($continent_id) != 0) {
			$option_list = $this->general_model->get_country_list($continent_id, $zone_id);


			if (valid_array($option_list['data'])) {
				echo get_compressed_output(generate_options($option_list['data'], array($default_select)));
			}
		}
	}
    
	/**
	 *Get Location List
	 */
	function location_list(int $limit = AUTO_SUGGESTION_LIMIT): void
	{
		$chars = $_GET['term'];
		$list = $this->general_model->get_location_list($chars, $limit);
		$temp_list = '';
		if (valid_array($list) == true) {
			foreach ($list as $k => $v) {
				$temp_list[] = array('id' => $k, 'label' => $v['name'], 'value' => $v['origin']);
			}
		}
		$this->output_compressed_data($temp_list);
	}

	/**
	 *Get Location List
	 */
	function city_list(int $limit = AUTO_SUGGESTION_LIMIT): void
	{
		$chars = $_GET['term'];
		$list = $this->general_model->get_city_list($chars, $limit);
		$temp_list = '';
		if (valid_array($list) == true) {
			foreach ($list as $k => $v) {
				$temp_list[] = array('id' => $k, 'label' => $v['name'], 'value' => $v['origin']);
			}
		}
		$this->output_compressed_data($temp_list);
	}

	/**
	 * Balu A
	 * @param unknown_type $currency_origin origin of currency - default to USD
	 */
	function get_currency_value(int $currency_origin = 0): void
	{
		$data = $this->custom_db->single_table_records('currency_converter', 'value', array('id' => intval($currency_origin)));
		if (valid_array($data['data'])) {
			$response = $data['data'][0]['value'];
		} else {
			$response = 1;
		}
		header('Content-type:application/json');
		echo json_encode(array('value' => $response));
		exit;
	}
    /*
	 *
	 * Hotels City auto suggest
	 *
	 */
	function get_hotel_city_list(): void
	{
		$this->load->model('hotel_model');
		$term = $this->input->get('term'); //retrieve the search term that autocomplete sends
		$term = trim(strip_tags($term));
		$data_list = $this->hotel_model->get_hotel_city_list($term);
		if (valid_array($data_list) == false) {
			$data_list = $this->hotel_model->get_hotel_city_list('');
		}
		$suggestion_list = array();
		$result = array();
		foreach ($data_list as $city_list) {
			$suggestion_list['label'] = $city_list['city_name'] . ', ' . $city_list['country_name'] . '';
			$suggestion_list['value'] = hotel_suggestion_value($city_list['city_name'], $city_list['country_name']);
			$suggestion_list['id'] = $city_list['origin'];
			if (empty($city_list['top_destination']) == false) {
				$suggestion_list['category'] = 'Top cities';
				$suggestion_list['type'] = 'Top cities';
			} else {
				$suggestion_list['category'] = 'Search Results';
				$suggestion_list['type'] = 'Search Results';
			}
			if (intval($city_list['cache_hotels_count']) > 0) {
				$suggestion_list['count'] = $city_list['cache_hotels_count'];
			} else {
				$suggestion_list['count'] = 0;
			}
			$result[] = $suggestion_list;
		}
		$this->output_compressed_data($result);
	}

	
	/**
	 *
	 */
	function log_event_ip_info(int $eid): void
	{
		$params = $this->input->post();
		if (empty($eid) == false) {
			$this->custom_db->update_record('exception_logger', array('client_info' => serialize($params)), array('exception_id' => $eid));
		}
	}
	//---------------------------------------------------------------- Booking Events Starts
	/**
	 * Load Booking Events of all the modules
	 */
	function booking_events(): void
	{
		$status = true;
		// $data = array();
		$calendar_events = array();
		$condition = array(array('BD.created_datetime', '>=', $this->db->escape(date('Y-m-d', strtotime(subtract_days_from_date(90)))))); //of last 30 days only
		if (is_active_bus_module()) {
			$calendar_events = array_merge($calendar_events, $this->bus_booking_events($condition));

		}
		if (is_active_hotel_module()) {
			$calendar_events = array_merge($calendar_events, $this->hotel_booking_events($condition));
		}
		if (is_active_airline_module()) {
			$calendar_events = array_merge($calendar_events, $this->flight_booking_events($condition));
		}

		if (is_active_sightseeing_module()) {

			$calendar_events = array_merge($calendar_events, $this->sightseeing_booking_events($condition));
		}
		if (is_active_commute_module()) {

			$calendar_events = array_merge($calendar_events, $this->transfers_booking_events($condition));
		}


		// debug($calendar_events);exit;
		header('content-type:application/json');
		echo json_encode(array('status' => $status, 'data' => $calendar_events));
		exit;
	}
/**
	 * Hotel Booking Events Summary
	 * @param array $condition
	 */
	private function hotel_booking_events(array $condition): array
	{
		$this->load->model('hotel_model');
		$data_list = $this->hotel_model->booking($condition);
		$this->load->library('booking_data_formatter');
		$table_data = $this->booking_data_formatter->format_hotel_booking_data($data_list, 'b2b');
		$booking_details = $table_data['data']['booking_details'];
		$calendar_events = array();
		if (valid_array($booking_details) == true) {
			$key = 0;
			foreach ($booking_details as $v) {
				$calendar_events[$key]['title'] = $v['app_reference'] . '-' . $v['status'];
				$calendar_events[$key]['start'] = $v['created_datetime'];
				$calendar_events[$key]['tip'] = $v['app_reference'] . '-PNR:' . $v['confirmation_reference'] . '-From:' . $v['hotel_check_in'] . ', To:' . $v['hotel_check_out'] . '-' . $v['status'] . '- Click To View More Details';
				$calendar_events[$key]['href'] = hotel_voucher_url($v['app_reference'], $v['booking_source'], $v['status']);
				$calendar_events[$key]['add_class'] = 'hand-cursor event-hand hotel-booking';
				$key++;
			}
		}
		return $calendar_events;
	}

	/**
	 * Flight Booking Events Summary
	 * @param array $condition
	 */
	private function flight_booking_events(array $condition): array
	{
		$this->load->model('flight_model');
		$data_list = $this->flight_model->booking($condition);
		$this->load->library('booking_data_formatter');
		$table_data = $this->booking_data_formatter->format_flight_booking_data($data_list, 'b2b');
		$booking_details = $table_data['data']['booking_details'];
		$calendar_events = array();
		if (valid_array($booking_details) == true) {
			$key = 0;
			foreach ($booking_details as  $v) {
				$calendar_events[$key]['title'] = $v['app_reference'] . '-' . $v['status'];
				$calendar_events[$key]['start'] = $v['created_datetime'];
				$calendar_events[$key]['tip'] = $v['app_reference'] . ',From:' . $v['journey_from'] . ', To:' . $v['journey_to'] . '-' . $v['status'] . '- Click To View More Details';
				$calendar_events[$key]['href'] = flight_voucher_url($v['app_reference'], $v['booking_source'], $v['status']);
				$calendar_events[$key]['add_class'] = 'hand-cursor event-hand flight-booking';
				$key++;
			}
		}

		return $calendar_events;
	}

	
	//---------------------------------------------------------------- Booking Events End
	

	function user_traveller_details(): void
	{
		$term = $this->input->get('term'); //retrieve the search term that autocomplete sends
		$term = trim($term);
		$result = array();
		$this->load->model('user_model');
		$traveller_details = $this->user_model->user_traveller_details($term)->result();
		$travllers_data = array();
		foreach ($traveller_details as $traveller) {
			$travllers_data['category'] = 'Travellers';
			$travllers_data['id'] = $traveller->origin;
			$travllers_data['label'] = trim($traveller->first_name . ' ' . $traveller->last_name);
			$travllers_data['value'] = trim($traveller->first_name);
			$travllers_data['first_name'] = trim($traveller->first_name);
			$travllers_data['last_name'] = trim($traveller->last_name);
			$travllers_data['date_of_birth'] = date('Y-m-d', strtotime(trim($traveller->date_of_birth)));
			$travllers_data['email'] = trim($traveller->email);
			$travllers_data['passport_user_name'] = trim($traveller->passport_user_name);
			$travllers_data['passport_nationality'] = trim($traveller->passport_nationality);
			$travllers_data['passport_expiry_day'] = trim($traveller->passport_expiry_day);
			$travllers_data['passport_expiry_month'] = trim($traveller->passport_expiry_month);
			$travllers_data['passport_expiry_year'] = trim($traveller->passport_expiry_year);
			$travllers_data['passport_number'] = trim($traveller->passport_number);
			$travllers_data['passport_issuing_country'] = trim($traveller->passport_issuing_country);
			array_push($result, $travllers_data);
		}
		$this->output_compressed_data($result);
	}

}

	