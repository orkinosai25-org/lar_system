<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller for all AJAX activities
 *
 * @package    Provab
 * @subpackage ajax loaders
 * @author     Balu A <balu.provab@gmail.com>
 * @version    V1
 */
class Ajax extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		// Prevent direct non-AJAX access
		if (is_ajax() == false) {
			//$this->index();
		}

		// Optionally enable profiler (for debugging)
		// $this->output->enable_profiler(true);
	}

	/**
	 * Index fallback method if accessed directly
	 */
	public function index(): void {}
	/**
	 * Get city list based on country ID
	 *
	 * @param int $country_id
	 * @param int $default_select
	 * @return void Outputs JSON response
	 */
	public function get_city_list(int $country_id = 0, int $default_select = 0): void
	{
		$resp = ['data' => []];

		if ($country_id != 0) {
			$condition = ['country_code' => $country_id];
			$order_by = ['city_name' => 'asc'];
			$option_list = $this->custom_db->single_table_records(
				'all_api_city_master',
				'origin as k, city_name as v',
				$condition,
				0,
				1000000,
				$order_by
			);

			if (valid_array($option_list['data'])) {
				$resp['data'] = get_compressed_output(generate_options($option_list['data'], [$default_select]));
			}
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($resp))
			->_display();

		exit;
	}
	/**
	 * Get country list based on continent and zone
	 *
	 * @param string|int $continent_id URL-encoded or integer continent ID
	 * @param int $default_select
	 * @param int $zone_id
	 * @return void Outputs compressed options directly
	 */
	public function get_country_list(int $continent_id = 0, int $default_select = 0, int $zone_id = 0): void
	{
		$this->load->model('general_model');

		// Decode and cast continent_id to int for query
		$continent_id = (int) urldecode((string)$continent_id);

		if ($continent_id != 0) {
			$option_list = $this->general_model->get_country_list($continent_id, $zone_id);

			if (valid_array($option_list['data'])) {
				$output = get_compressed_output(generate_options($option_list['data'], [$default_select]));
				$this->output
					->set_content_type('text/plain') // Assuming this returns HTML options
					->set_output($output)
					->_display();
				exit;
			}
		}
	}
	/**
	 * Get Location List for autocomplete
	 *
	 * @param int $limit
	 * @return void Outputs JSON response
	 */
	public function location_list(int $limit = AUTO_SUGGESTION_LIMIT): void
	{
		$chars = $this->input->get('term', true); // XSS clean input
		$list = $this->general_model->get_location_list($chars, $limit);

		$temp_list = [];

		if (valid_array($list)) {
			foreach ($list as $k => $v) {
				$temp_list[] = [
					'id' => $k,
					'label' => $v['name'],
					'value' => $v['origin'],
				];
			}
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($temp_list))
			->_display();

		exit;
	}
	/**
	 * Get city list based on country ID and return HTML <option> elements
	 *
	 * @return void Outputs HTML options and exits
	 */
	public function get_city_lists(): void
	{
		$country_id = $this->input->get('country_id', true); // XSS clean input
		$get_resulted_data = $this->custom_db->single_table_records(
			'api_city_list',
			'*',
			['country' => $country_id],
			0,
			100000000,
			['destination' => 'asc']
		);
		$html = "<option value=''>Select City</option>";
		if (empty($get_resulted_data['data'])) {
			$html = "<option value=''>No City Found</option>";
		}		
		foreach ($get_resulted_data['data'] as $city) {
			$html .= '<option value="' . htmlspecialchars($city['origin'], ENT_QUOTES, 'UTF-8') . '">'
				. htmlspecialchars($city['destination'], ENT_QUOTES, 'UTF-8')
				. '</option>';
		}

		$this->output
			->set_content_type('text/html')
			->set_output($html)
			->_display();

		exit;
	}
	/**
	 * Get City List for autocomplete
	 *
	 * @param int $limit
	 * @return void Outputs JSON response
	 */
	public function city_list(int $limit = AUTO_SUGGESTION_LIMIT): void
	{
		$chars = $this->input->get('term', true); // XSS clean input
		$list = $this->general_model->get_city_list($chars, $limit);

		$temp_list = [];

		if (valid_array($list)) {
			foreach ($list as $k => $v) {
				$temp_list[] = [
					'id' => $k,
					'label' => $v['name'],
					'value' => $v['origin'],
				];
			}
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($temp_list))
			->_display();

		exit;
	}
	/**
	 * Get currency conversion value
	 *
	 * @param int $currency_origin Origin of currency, defaults to 0 (USD)
	 * @return void Outputs JSON response
	 */
	public function get_currency_value(int $currency_origin = 0): void
	{
		$data = $this->custom_db->single_table_records('currency_converter', 'value', ['id' => $currency_origin]);

		$response = 1; // default value

		if (valid_array($data['data'])) {
			$response = $data['data'][0]['value'];
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(['value' => $response]))
			->_display();

		exit;
	}
	/**
	 * Handle forgot password request
	 *
	 * @return void Outputs JSON response
	 */
	public function forgot_password(): void
	{
		$post_data = $this->input->post(null, true); // XSS cleaned input
		$email = $post_data['email'] ?? null;
		$phone = $post_data['phone'] ?? null;

		$condition = [
			'email' => provab_encrypt($email),
			'phone' => $phone,
			'status' => ACTIVE,
			'domain_list_fk' => get_domain_auth_id(),
		];

		$user_record = $this->custom_db->single_table_records('user', 'email, password, user_id, first_name, last_name', $condition);

		if ($user_record['status'] == true && valid_array($user_record['data'])) {
			// Generate a new password as a timestamp (can be improved to a random string)
			$new_password = (string) time();

			// Prepare data for email template
			$user_data = $user_record['data'][0];
			$user_data['password'] = $new_password;

			// Generate mail template
			$mail_template = $this->template->isolated_view('general/forgot_password_template', $user_data);

			// Encrypt and hash the new password for DB update
			$encrypted_password = provab_encrypt(md5(trim($new_password)));

			$update_data = ['password' => $encrypted_password];
			$condition_update = ['user_id' => intval($user_data['user_id'])];

			$this->custom_db->update_record('user', $update_data, $condition_update);

			$this->load->library('provab_mailer');
			$this->provab_mailer->send_mail($email, 'Password Reset : ' . domain_name(), $mail_template);

			$response = [
				'status' => true,
				'data' => 'Password Has Been Sent Reset Successfully and To Your Email ID',
			];
		} else {
			$response = [
				'status' => false,
				'data' => 'Please Provide Correct Data To Identify Your Account',
			];
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response))
			->_display();

		exit;
	}
	/**
	 * Load Booking Events of all the modules
	 *
	 * @return void Outputs JSON response
	 */
	public function booking_events(): void
	{
		$status = true;
		$calendar_events = [];

		// Filter events from last 90 days
		$date_limit = date('Y-m-d', strtotime(subtract_days_from_date(90)));
		$condition = [['BD.created_datetime', '>=', $this->db->escape($date_limit)]];

		if (is_active_bus_module()) {
			$calendar_events = array_merge($calendar_events, $this->bus_booking_events($condition));
		}
		if (is_active_hotel_module()) {
			$calendar_events = array_merge($calendar_events, $this->hotel_booking_events($condition));
		}
		if (is_active_airline_module()) {
			$calendar_events = array_merge($calendar_events, $this->flight_booking_events($condition));
		}
		if (is_active_transferv1_module()) {
			$calendar_events = array_merge($calendar_events, $this->transfers_booking_events($condition));
		}
		if (is_active_sightseeing_module()) {
			$calendar_events = array_merge($calendar_events, $this->sightseeing_booking_events($condition));
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(['status' => $status, 'data' => $calendar_events]))
			->_display();

		exit;
	}
	/**
	 * Hotel Booking Events Summary
	 *
	 * @param array $condition
	 * @return array
	 */
	private function hotel_booking_events(array $condition): array
	{
		$this->load->model('hotel_model');
		$data_list = $this->hotel_model->booking($condition);

		$this->load->library('booking_data_formatter');
		$table_data = $this->booking_data_formatter->format_hotel_booking_data($data_list, 'b2c');

		$booking_details = $table_data['data']['booking_details'] ?? [];
		$calendar_events = [];

		if (valid_array($booking_details)) {
			foreach ($booking_details as $v) {
				$calendar_events[] = [
					'title' => $v['app_reference'] . '-' . $v['status'],
					'start' => $v['created_datetime'],
					'tip' => $v['app_reference'] . '-PNR:' . $v['confirmation_reference'] .
						'-From:' . $v['hotel_check_in'] . ', To:' . $v['hotel_check_out'] .
						'-' . $v['status'] . '- Click To View More Details',
					'href' => hotel_voucher_url($v['app_reference'], $v['booking_source'], $v['status']),
					'add_class' => 'hand-cursor event-hand hotel-booking',
				];
			}
		}

		return $calendar_events;
	}
	/**
	 * Flight Booking Events Summary
	 *
	 * @param array $condition
	 * @return array
	 */
	private function flight_booking_events(array $condition): array
	{
		$this->load->model('flight_model');
		$data_list = $this->flight_model->booking($condition);

		$this->load->library('booking_data_formatter');
		$table_data = $this->booking_data_formatter->format_flight_booking_data($data_list, 'b2c');

		$booking_details = $table_data['data']['booking_details'] ?? [];
		$calendar_events = [];

		if (valid_array($booking_details)) {
			foreach ($booking_details as $v) {
				$calendar_events[] = [
					'title' => $v['app_reference'] . '-' . $v['status'],
					'start' => $v['created_datetime'],
					'tip' => $v['app_reference'] . ',From:' . $v['journey_from'] . ', To:' . $v['journey_to'] .
						'-' . $v['status'] . '- Click To View More Details',
					'href' => flight_voucher_url($v['app_reference'], $v['booking_source'], $v['status']),
					'add_class' => 'hand-cursor event-hand flight-booking',
				];
			}
		}

		return $calendar_events;
	}
	/**
	 * Bus Booking Events Summary
	 *
	 * @param array $condition
	 * @return array
	 */
	private function bus_booking_events(array $condition): array
	{
		$this->load->model('bus_model');
		$data_list = $this->bus_model->booking($condition);

		$this->load->library('booking_data_formatter');
		$table_data = $this->booking_data_formatter->format_bus_booking_data($data_list, 'b2c');

		$booking_details = $table_data['data']['booking_details'] ?? [];
		$calendar_events = [];

		if (valid_array($booking_details)) {
			foreach ($booking_details as $v) {
				$calendar_events[] = [
					'title' => $v['app_reference'] . '-' . $v['status'],
					'start' => $v['created_datetime'],
					'tip' => $v['app_reference'] . '-PNR:' . $v['pnr'] . '-From:' . $v['departure_from'] . ', To:' . $v['arrival_to'] . '-' . $v['status'] . '- Click To View More Details',
					'href' => bus_voucher_url($v['app_reference'], $v['booking_source'], $v['status']),
					'add_class' => 'hand-cursor event-hand bus-booking',
					// 'prepend_element' => '<i class="fa fa-bus"></i>', // Uncomment if needed
				];
			}
		}

		return $calendar_events;
	}
	/**
	 * Sightseeing Booking Events Summary
	 *
	 * @param array $condition
	 * @return array
	 */
	private function sightseeing_booking_events(array $condition): array
	{
		$this->load->model('sightseeing_model');
		$data_list = $this->sightseeing_model->booking($condition);

		$this->load->library('booking_data_formatter');
		$table_data = $this->booking_data_formatter->format_sightseeing_booking_data($data_list, 'b2c');

		$booking_details = $table_data['data']['booking_details'] ?? [];
		$calendar_events = [];

		if (valid_array($booking_details)) {
			foreach ($booking_details as $v) {
				$calendar_events[] = [
					'title' => $v['app_reference'] . '-' . $v['status'],
					'start' => $v['created_datetime'],
					'tip' => $v['app_reference'] . '-PNR:' . $v['confirmation_reference'] . '-From:' . $v['destination_name'] . ', Travel Date:' . $v['travel_date'] . '-' . $v['status'] . '- Click To View More Details',
					'href' => sightseeing_voucher_url($v['app_reference'], $v['booking_source'], $v['status']),
					'add_class' => 'hand-cursor event-hand sightseeing-booking',
					// 'prepend_element' => '<i class="fa fa-bus"></i>', // Uncomment if needed
				];
			}
		}

		return $calendar_events;
	}
	/**
	 * Transfers Booking Events Summary
	 *
	 * @param array $condition
	 * @return array
	 */
	private function transfers_booking_events(array $condition): array
	{
		$this->load->model('transferv1_model');
		$data_list = $this->transferv1_model->booking($condition);

		$this->load->library('booking_data_formatter');
		$table_data = $this->booking_data_formatter->format_transferv1_booking_data($data_list, 'b2c');

		$booking_details = $table_data['data']['booking_details'] ?? [];
		$calendar_events = [];

		if (valid_array($booking_details)) {
			foreach ($booking_details as $v) {
				$calendar_events[] = [
					'title' => $v['app_reference'] . '-' . $v['status'],
					'start' => $v['created_datetime'],
					'tip' => $v['app_reference'] . '-PNR:' . $v['confirmation_reference'] . '-From:' . $v['destination_name'] . ', Travel Date:' . $v['travel_date'] . '-' . $v['status'] . '- Click To View More Details',
					'href' => transfers_voucher_url($v['app_reference'], $v['booking_source'], $v['status']),
					'add_class' => 'hand-cursor event-hand transfers-booking',
					// 'prepend_element' => '<i class="fa fa-bus"></i>', // Uncomment if needed
				];
			}
		}

		return $calendar_events;
	}
	//---------------------------------------------------------------- Trip Events Start

	/**
	 * Load Trip Events of all the modules
	 *
	 * @return void Outputs JSON response
	 */
	public function trip_events(): void
	{
		$status = true;
		$trip_events = [];
		$start_date = date('Y-m-d', strtotime(subtract_days_from_date(30)));

		if (is_active_bus_module()) {
			$trip_events = array_merge($trip_events, $this->bus_trip_events($start_date));
		}
		if (is_active_hotel_module()) {
			$trip_events = array_merge($trip_events, $this->hotel_trip_events($start_date));
		}
		if (is_active_airline_module()) {
			$trip_events = array_merge($trip_events, $this->flight_trip_events($start_date));
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(['status' => $status, 'data' => $trip_events]))
			->_display();

		exit;
	}

	/**
	 * Hotel Trip Events
	 *
	 * @param string $start_date Date string in Y-m-d format
	 * @return array
	 */
	private function hotel_trip_events(string $start_date): array
	{
		$this->load->model('hotel_model');

		// Using CI's escape_str for safety if needed, but here db->escape returns quoted string
		$condition = [['BD.hotel_check_in', '>=', $this->db->escape($start_date)]];
		$data_list = $this->hotel_model->booking($condition);

		$trip_events = [];
		if (valid_array($data_list)) {
			$current_date = db_current_datetime();
			foreach ($data_list as $k => $v) {
				$day_label = day_count_label(get_date_difference($current_date, $v['hotel_check_in']));
				$trip_events[$k] = [
					'title' => $day_label . $v['name'] . '-' . $v['status'],
					'start' => $v['hotel_check_in'],
					'end' => $v['hotel_check_out'],
					'tip' => $v['app_reference'] . '-PNR:' . $v['confirmation_reference'] . '-From:' . $v['hotel_check_in'] . ', To:' . $v['hotel_check_out'] . '-' . $v['status'] . '- Click To View More Details',
					'href' => hotel_voucher_url($v['app_reference'], $v['booking_source'], $v['status']),
					'add_class' => 'hand-cursor event-hand ' . event_class($v['hotel_check_in']),
					'prepend_element' => '<i class="fa fa-bed hotel-booking"></i>',
				];
			}
		}

		return $trip_events;
	}
	/**
	 * Flight Trip Events
	 *
	 * @param string $start_date Date string in Y-m-d format
	 * @return array
	 */
	private function flight_trip_events(string $start_date): array
	{
		$this->load->model('flight_model');
		$condition = [['BD.journey_start', '>=', $this->db->escape($start_date)]];
		$data_list = $this->flight_model->booking($condition);

		$trip_events = [];
		if (valid_array($data_list)) {
			$current_date = db_current_datetime();
			foreach ($data_list as $k => $v) {
				$day_label = day_count_label(get_date_difference($current_date, $v['journey_start']));
				$trip_events[$k] = [
					'title' => $day_label . $v['name'] . '-' . $v['status'],
					'start' => $v['journey_start'],
					'end' => $v['journey_end'],
					'tip' => $v['app_reference'] . ',From:' . $v['journey_from'] . ', To:' . $v['journey_to'] . '-' . $v['status'] . '- Click To View More Details',
					'href' => flight_voucher_url($v['app_reference'], $v['booking_source'], $v['status']),
					'add_class' => 'hand-cursor event-hand ' . event_class($v['journey_start']),
					'prepend_element' => '<i class="fa fa-plane flight-booking"></i>',
				];
			}
		}

		return $trip_events;
	}

	/**
	 * Bus Trip Events
	 *
	 * @param string $start_date Date string in Y-m-d format
	 * @return array
	 */
	private function bus_trip_events(string $start_date): array
	{
		$this->load->model('bus_model');
		$condition = [['ID.journey_datetime', '>=', $this->db->escape($start_date)]];
		$data_list = $this->bus_model->booking($condition);

		$trip_events = [];
		if (valid_array($data_list)) {
			$current_date = db_current_datetime();
			foreach ($data_list as $k => $v) {
				$day_label = day_count_label(get_date_difference($current_date, $v['departure_datetime']));
				$trip_events[$k] = [
					'title' => $day_label . $v['name'] . '-' . $v['status'],
					'start' => $v['departure_datetime'],
					'end' => $v['arrival_datetime'],
					'tip' => $v['app_reference'] . '-PNR:' . $v['pnr'] . '-From:' . $v['departure_from'] . ', To:' . $v['arrival_to'] . '-' . $v['status'] . '- Click To View More Details',
					'href' => bus_voucher_url($v['app_reference'], $v['booking_source'], $v['status']),
					'add_class' => 'hand-cursor event-hand ' . event_class($v['departure_datetime']),
					'prepend_element' => '<i class="fa fa-bus bus-booking"></i>',
				];
			}
		}

		return $trip_events;
	}
	public function auto_suggest_agency_name(): void
	{
		$term = trim(strip_tags($this->input->get('term')));
		$result = [];

		$this->load->model('domain_management_model');
		$core_agent_details = $this->domain_management_model->auto_suggest_agency_name($term);

		foreach ($core_agent_details as $agent) {
			$result[] = [
				'label' => $agent['agency_name'] . '-' . $agent['uuid'],
				'value' => $agent['agency_name'],
			];
		}

		$this->output_compressed_data($result);
	}

	public function auto_suggest_promo_code(): void
	{
		$term = trim(strip_tags($this->input->get('term')));
		$result = [];

		$this->load->model('module_model');
		$core_promocode_details = $this->module_model->auto_suggest_promo_code($term);

		foreach ($core_promocode_details as $promo) {
			$result[] = [
				'label' => $promo['promo_code'] . '-' . ucfirst($promo['module_type']),
				'value' => $promo['promo_code'],
			];
		}

		$this->output_compressed_data($result);
	}
	/**
	 * Check if a promo code already exists or not.
	 */
	public function is_unique_promocode(): void
	{
		$promo_code = trim($this->input->get('promo_code'));
		$result = ['status' => true];

		if (!empty($promo_code)) {
			$this->load->model('module_model');
			$data = $this->module_model->is_unique_promocode($promo_code);
			if (valid_array($data)) {
				$result['status'] = false;
				$result['promo_code'] = trim($data['promo_code']);
			}
		}

		$this->output_compressed_data($result);
	}

	/**
	 * Compress and output data as JSON with gzip compression.
	 * @param array $data
	 */
	private function output_compressed_data(array $data): void
	{
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
		ob_start('ob_gzhandler');
		header('Content-Type: application/json');
		echo json_encode($data);
		ob_end_flush();
		exit;
	}

	/**
	 * Fetch supplier details by supplier ID.
	 */
	public function get_supplier_data(): void
	{
		$supplier_id = $this->input->post('supplier_id');
		if (empty($supplier_id)) {
			$this->output_compressed_data(['error' => 'Supplier ID is required']);
		}

		$supplier_data_result = $this->custom_db->single_table_records('user', '*', ['user_id' => $supplier_id]);
		if (!valid_array($supplier_data_result['data'])) {
			$this->output_compressed_data(['error' => 'Supplier not found']);
		}
		$supplier_data = $supplier_data_result['data'][0];

		$show_data = [];
		$show_data['supplier_name'] = trim(($supplier_data['first_name'] ?? '') . ' ' . ($supplier_data['last_name'] ?? '')) ?: 'Supplier not selected';
		$show_data['email'] = isset($supplier_data['email']) ? provab_decrypt($supplier_data['email']) : 'N/A';

		if (!empty($supplier_data['currency'])) {
			$currency_result = $this->custom_db->single_table_records('currency_converter', 'country', ['id' => $supplier_data['currency']]);
			$show_data['currency'] = valid_array($currency_result['data']) ? $currency_result['data'][0]['country'] : 'INR';
		} else {
			$show_data['currency'] = 'INR';
		}

		$show_data['status'] = ($supplier_data['status'] == 1) ? 'Enabled' : 'Disabled';

		$this->output_compressed_data($show_data);
	}

	/**
	 * Get city list by country ID with optional default selected option.
	 * @param int|string $country_id
	 * @param int|string $default_select
	 */
	public function get_city_list1($country_id = 0, $default_select = 0): void
	{
		$resp = ['data' => []];
		if (!empty($country_id)) {
			$condition = ['country_code' => $country_id];
			// You had an unused $order_by variable; define it if needed or remove.
			$option_list = $this->custom_db->single_table_records('all_api_city_master', 'origin as k, city_name as v', $condition, 0, 1000000 /*, $order_by */);

			if (valid_array($option_list['data'])) {
				$resp['data'] = get_compressed_output(generate_options($option_list['data'], [$default_select]));
			}
		}
		header('Content-Type: application/json');
		echo json_encode($resp);
		exit;
	}
	/**
	 * Get temporary flight list for a pilot on a given date.
	 */
	public function fdtl_temp_flight_list()
	{
		$post = $this->input->post();
		$pilot_id = isset($post['pilot_id']) ? (int)$post['pilot_id'] : 0;
		$date = isset($post['date']) ? date('Y-m-d', strtotime($post['date'])) : '';

		if ($pilot_id <= 0 || empty($date)) {
			echo json_encode(['status' => false, 'error' => 'Invalid pilot ID or date']);
			exit;
		}

		// Fetch temp flight details where pilot is either PIC or co-pilot for that date
		$query = "SELECT * FROM `fdtl_details_temp` WHERE (pilot_in_command = ? OR co_pilot = ?) AND jlb_date = ?";
		$flight_list = $this->custom_db->get_custom_query($query, [$pilot_id, $pilot_id, $date]);

		$response = '';
		if ($flight_list['status']) {
			$flights = $flight_list['data'];
			foreach ($flights as $key => $flight) {
				// Fetch flight number details
				$flight_num_query = "SELECT flight_num FROM `flight_crs_details` WHERE fsid = ?";
				$fsidd = $this->custom_db->get_custom_query($flight_num_query, [$flight['flight_number']]);
				$flights[$key]['flight_number'] = $fsidd['status'] && valid_array($fsidd['data']) ? $fsidd['data'][0]['flight_num'] : 'N/A';
			}

			$response = get_compressed_output($this->template->isolated_view('flight/temp_flight_list', ['filght_list' => $flights]));
		}

		print_r($response);
	}

	/**
	 * Check flight conflicts and insert seat temp record if valid.
	 */
	public function check_flight()
	{
		$post = $this->input->post();

		$data = [
			'sameorigin_samedate_sametime' => 0,
			'sameflightnum_samedate' => 0,
			'sameorigin_samedate_sametime_sameaircraft' => 0,
			'seat_temp_id' => 0
		];

		$dep_date = isset($post['dep_date']) ? date('Y-m-d', strtotime($post['dep_date'])) : '';
		//$arr_date = isset($post['arr_date']) ? date('Y-m-d', strtotime($post['arr_date'])) : '';
		$flight_num = $post['flight_num'] ?? '';
		$departure_airport = $post['departure_airport'] ?? '';
		$departure_time = $post['departure_time'] ?? '';
		$aircraft = $post['aircraft'] ?? '';
		$selected_days = $post['selected_days'] ?? [];

		if (empty($dep_date) || empty($flight_num) || empty($departure_airport) || empty($departure_time) || empty($aircraft) || !is_array($selected_days)) {
			echo json_encode(['error' => 'Invalid input']);
			exit;
		}

		// Check if flight number exists on given departure date range and days
		$temp1 = "SELECT * FROM `flight_crs_segment_details` WHERE flight_num = ? AND ? BETWEEN dep_from_date AND dep_to_date";
		$f_temp1 = $this->custom_db->get_custom_query($temp1, [$flight_num, $dep_date]);

		if ($f_temp1['status'] && valid_array($f_temp1['data'])) {
			$ex_date = explode(',', $f_temp1['data'][0]['days']);
			foreach ($selected_days as $day) {
				if (in_array($day, $ex_date)) {
					$data['sameflightnum_samedate'] = 1;
					break;
				}
			}
		}

		if ($data['sameflightnum_samedate'] == 1) {
			// Check same origin, same date, same time and same aircraft
			$temp2 = "SELECT * FROM `flight_crs_segment_details` WHERE origin = ? AND ? BETWEEN dep_from_date AND dep_to_date AND departure_time = ? AND aircraft = ?";
			$f_temp2 = $this->custom_db->get_custom_query($temp2, [$departure_airport, $dep_date, $departure_time, $aircraft]);
			if ($f_temp2['status'] && valid_array($f_temp2['data'])) {
				$data['sameorigin_samedate_sametime_sameaircraft'] = 1;
			}

			// Check same origin, same date, and time overlap (departure_time between departure_time and arrival_time)
			$temp3 = "SELECT * FROM `flight_crs_segment_details` WHERE origin = ? AND ? BETWEEN dep_from_date AND dep_to_date AND ? BETWEEN departure_time AND arrival_time";
			$f_temp3 = $this->custom_db->get_custom_query($temp3, [$departure_airport, $dep_date, $departure_time]);
			if ($f_temp3['status'] && valid_array($f_temp3['data'])) {
				$data['sameorigin_samedate_sametime'] = 1;
			}
		}

		// If no conflicts, insert into seat_temp table
		if (
			$data['sameorigin_samedate_sametime'] == 0
			&& $data['sameflightnum_samedate'] == 0
			&& $data['sameorigin_samedate_sametime_sameaircraft'] == 0
		) {

			$seat_data = [
				'from_seat_range' => $post['from_seat_range'] ?? '',
				'to_seat_range' => $post['to_seat_range'] ?? '',
				'range_price' => $post['range_price'] ?? '',
				'block_seat_from_range' => $post['block_seat_from_range'] ?? '',
				'block_seat_to_range' => $post['block_seat_to_range'] ?? ''
			];

			$seat_data_to_insert = ['data' => json_encode($seat_data)];
			$inserted_id = $this->custom_db->insert_record('seat_temp', $seat_data_to_insert);
			$data['seat_temp_id'] = $inserted_id['insert_id'] ?? 0;
		}

		echo json_encode($data);
	}
}
