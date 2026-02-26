<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 * @package    Provab
 * @subpackage Car
 * @author     Anitha J<anitha.g.provab@gmail.com>
 * @version    V1
 */

class Car extends CI_Controller
{
	private $current_module;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('car_model');
		$this->current_module = $this->config->item('current_module');
	}

	function index()
	{
		//	echo number_format(0, 2, '.', '');
	}

	/**
	 *  Anitha G
	 * Load Car Search Result
	 * @param number $search_id unique number which identifies search criteria given by user at the time of searching
	 */

	public function search(int $search_id): void
	{
		$safe_search_data = $this->car_model->get_safe_search_data($search_id);
		$active_booking_source = $this->car_model->car_booking_source();

		if (
			isset($safe_search_data['status'], $safe_search_data['data']) &&
			$safe_search_data['status'] == true &&
			function_exists('valid_array') && valid_array($active_booking_source) == true
		) {
			$safe_search_data['data']['search_id'] = abs($search_id);

			$page_params = [
				'car_search_params' => $safe_search_data['data'],
				'active_booking_source' => $active_booking_source,
				'from_currency' => function_exists('get_application_default_currency')
					? get_application_default_currency()
					: 'USD',
				'to_currency' => function_exists('get_application_currency_preference')
					? get_application_currency_preference()
					: 'USD'
			];

			$this->template->view('car/search_result_page', $page_params);
			return;
		}

		if (isset($safe_search_data['status']) && $safe_search_data['status'] == true) {
			$this->template->view('general/popup_redirect');
			return;
		}

		$this->template->view('flight/exception');
	}


	/**
	 * Load Car Request
	 * */

	public function car_details(int $search_id): void
	{
		$params = $this->input->post();
		$this->load->model('user_model');

		$safe_search_data = $this->car_model->get_safe_search_data($search_id);
		$currency_obj = new Currency([
			'module_type' => 'car',
			'from' => function_exists('get_api_data_currency') ? get_api_data_currency() : 'USD',
			'to' => function_exists('get_application_currency_preference') ? get_application_currency_preference() : 'USD',
		]);

		if (!isset($params['booking_source'])) {
			redirect(base_url());
			return;
		}

		load_car_lib($params['booking_source']);

		if (
			$params['booking_source'] !== PROVAB_CAR_BOOKING_SOURCE ||
			!isset($params['ResultIndex'], $params['op']) ||
			$params['op'] !== 'get_details' ||
			!isset($safe_search_data['status']) ||
			$safe_search_data['status'] !== true
		) {
			redirect(base_url());
			return;
		}

		$raw_car_details = $this->car_lib->get_rate_rules($params['ResultIndex']);

		$page_data = [];

		$temp_record = $this->custom_db->single_table_records('api_country_list', '*');
		$page_data['phone_code'] = $temp_record['data'] ?? [];

		$domain_record = $this->custom_db->single_table_records('domain_list', '*');
		$page_data['active_data'] = $domain_record['data'][0] ?? [];

		$total_price = $raw_car_details['data']['RateRule']['CarRuleResult'][0]['TotalCharge']['EstimatedTotalAmount'] ?? 0.0;

		$page_data['search_id'] = $search_id;
		$page_data['country_list'] = $this->db_cache_api->get_iso_country_code();
		$page_data['convenience_fees'] = $currency_obj->convenience_fees($total_price, $search_id);
		$page_data['active_payment_options'] = $this->module_model->get_active_payment_module_list();
		$page_data['pax_details'] = $this->user_model->get_current_user_details();
		$page_data['user_country_code'] = $this->entity_country_code ?? '';

		if (!isset($raw_car_details['status']) || $raw_car_details['status'] !== true) {
			return;
		}

		$car_rate_result = $raw_car_details['data'] ?? [];
		$raw_car_rate_result = $raw_car_details['data']['RateRule']['CarRuleResult'][0] ?? [];

		$page_data['raw_car_rate_result'] = $raw_car_rate_result;

		if (!isset($car_rate_result) || !function_exists('valid_array') || !valid_array($car_rate_result)) {
			redirect(base_url() . 'index.php/car/exceptio?nop=Remote IO error - Cache has no data @ Insufficient&notification=validation');
			return;
		}

		$currency_obj = new Currency([
			'module_type' => 'car',
			'from' => get_api_data_currency(),
			'to' => get_application_currency_preference(),
		]);

		$car_rate_result = $this->car_lib->car_rule_in_preferred_currency(
			$car_rate_result,
			'b2b',
			$currency_obj,
			$search_id
		);

		$this->template->view('car/car_booking_page', [
			'currency_obj' => $currency_obj,
			'car_rules' => $car_rate_result,
			'car_search_params' => $safe_search_data['data'],
			'active_booking_source' => $params['booking_source'],
			'params' => $params,
			'page_data' => $page_data
		]);
	}

	public function pre_booking(int $search_id): void
	{
		$post_params = $this->input->post();
		$valid_temp_token = unserialized_data($post_params['token'], $post_params['token_key']);

		if ($valid_temp_token == false) {
			redirect(base_url() . 'index.php/car/exception?op=Remote IO error @ Hotel Booking&notification=validation');
			return;
		}

		load_car_lib($post_params['booking_source']);

		$post_params['token'] = unserialized_data($post_params['token']);

		$currency_obj = new Currency([
			'module_type' => 'car',
			'from' => get_application_currency_preference(),
			'to' => admin_base_currency(),
		]);

		$post_params['token'] = serialized_data($post_params['token']);
		$temp_token = unserialized_data($post_params['token']);
		$temp_token['default_currency'] = admin_base_currency();

		$temp_booking = $this->module_model->serialize_temp_booking_record($post_params, CAR_BOOKING);
		$book_id = $temp_booking['book_id'] ?? null;
		$book_origin = $temp_booking['temp_booking_origin'] ?? null;
		$amount = $temp_token['TotalCharge']['EstimatedTotalAmount'] ?? 0.0;

		$currency_obj = new Currency([
			'module_type' => 'car',
			'from' => admin_base_currency(),
			'to' => admin_base_currency(),
		]);

		$convenience_fees = $currency_obj->convenience_fees($amount, $search_id);
		$promocode_discount = $post_params['promo_code_discount_val'] ?? 0;

		$email = $post_params['billing_email'] ?? '';
		$phone = $post_params['passenger_contact'] ?? '';
		$currency = $temp_token['TotalCharge']['CurrencyCode'] ?? '';
		$verification_amount = roundoff_number($amount);
		$firstname = $post_params['first_name'] ?? '';
		$productinfo = META_CAR_COURSE;

		$agent_payable_amount = $currency_obj->get_agent_paybleamount($verification_amount);
		$domain_balance_status = $this->domain_management_model->verify_current_balance($verification_amount, $currency);

		if ($domain_balance_status != true) {
			redirect(base_url() . 'index.php/car/exception?op=Amount Hotel Booking&notification=insufficient_balance');
			return;
		}

		switch ($post_params['payment_method']) {
			case PAY_NOW:
				$this->load->model('transaction');
				$pg_currency_conversion_rate = $currency_obj->payment_gateway_currency_conversion_rate();
				$this->transaction->create_payment_record(
					$book_id,
					$verification_amount,
					$firstname,
					$email,
					$phone,
					$productinfo,
					$convenience_fees,
					$promocode_discount,
					$pg_currency_conversion_rate
				);
				redirect(base_url() . 'index.php/payment_gateway/payment/' . $book_id . '/' . $book_origin);
				return;

			case PAY_AT_BANK:
				echo 'Under Construction - Remote IO Error';
				exit;
		}
	}



	/**
	 * Anitha G
	 */

	public function exception(): void
	{
		$module = META_BUS_COURSE;
		$op = $_GET['op'] ?? '';  // Using null coalescing operator to avoid undefined index warnings
		$notification = $_GET['notification'] ?? '';  // Same for notification

		// Log the exception, assuming `log_exception` handles logging the data
		$eid = $this->module_model->log_exception($module, $op, $notification);

		// Set IP log session before redirection
		$this->session->set_flashdata(['log_ip_info' => true]);

		// Redirect with the event ID
		redirect(base_url() . 'index.php/car/event_logger/' . $eid);
	}

	public function event_logger(string $eid = ''): void
	{
		$log_ip_info = $this->session->flashdata('log_ip_info');

		// Render the view with the log IP information and event ID
		$this->template->view('car/exception', ['log_ip_info' => $log_ip_info, 'eid' => $eid]);
	}

	public function booking(): void
	{
		// Render the car booking page view
		$this->template->view('car/car_booking_page');
	}

	/*
        process booking in backend until show loader
        */

	public function process_booking(string $book_id, int $temp_book_origin): void
	{
		if ($book_id === '' || $temp_book_origin === '' || $temp_book_origin <= 0) {
			redirect(base_url() . 'index.php/car/exception?op=Invalid request&notification=validation');
			return;
		}

		$page_data = [];

		// Prepare the form data
		$page_data['form_url'] = base_url() . 'index.php/car/secure_booking';
		$page_data['form_method'] = 'POST';
		$page_data['form_params']['book_id'] = $book_id;
		$page_data['form_params']['temp_book_origin'] = $temp_book_origin;

		// Render the loader view
		$this->template->view('share/loader/booking_process_loader', $page_data);
	}

	public function secure_booking(): void
	{
		$post_data = $this->input->post();

		// Validate input data
		if (
			!valid_array($post_data) ||
			!isset($post_data['book_id'], $post_data['temp_book_origin']) ||
			empty($post_data['book_id']) ||
			intval($post_data['temp_book_origin']) <= 0
		) {
			redirect(base_url() . 'index.php/car/exception?op=InvalidBooking&notification=invalid');
			return;
		}

		// Sanitize and assign variables
		$book_id = trim($post_data['book_id']);
		$temp_book_origin = intval($post_data['temp_book_origin']);

		// Retrieve the temporary booking record
		$temp_booking = $this->module_model->unserialize_temp_booking_record($book_id, $temp_book_origin);

		// Check if temp_booking is valid
		if ($temp_booking == false) {
			redirect(base_url() . 'index.php/car/exception?op=BookingNotFound&notification=No booking found');
			return;
		}

		// Load the car library based on the booking source
		load_car_lib($temp_booking['booking_source']);

		// Get the booking amount and currency
		$amount = $temp_booking['book_attributes']['token']['TotalCharge']['EstimatedTotalAmount'] ?? 0.0;
		$currency = $temp_booking['book_attributes']['token']['TotalCharge']['CurrencyCode'] ?? '';

		// Create a Currency object
		$currency_obj = new Currency([
			'module_type' => 'car',
			'from' => get_application_currency_preference(),
			'to' => admin_base_currency(),
		]);

		// Check domain balance before proceeding with the booking
		$domain_balance_status = $this->domain_management_model->verify_current_balance($amount, $currency);

		if ($domain_balance_status !== true) {
			redirect(base_url() . 'index.php/car/exception?op=InsufficientBalance&notification=Insufficient balance to proceed');
			return;
		}

		// Proceed with booking if temp_booking exists
		switch ($temp_booking['booking_source']) {
			case PROVAB_CAR_BOOKING_SOURCE:
				$booking = $this->car_lib->process_booking($book_id, $temp_booking['book_attributes']);
				break;
			default:
				$booking = null;
				break;
		}

		// Check if the booking was successful
		if (!isset($booking['status']) || $booking['status'] != SUCCESS_STATUS) {
			$msg = $booking['msg'] ?? 'Unknown error';
			redirect(base_url() . 'index.php/car/exception?op=booking_exception&notification=' . $msg);
			return;
		}

		// Add necessary data to the booking
		$booking['data']['currency_obj'] = $currency_obj;
		$booking['data']['temp_booking'] = $temp_booking;

		// Save the booking based on booking status
		$data = $this->car_lib->save_booking($book_id, $booking['data'], 'b2b');

		// Update transaction details in the domain management model
		$this->domain_management_model->update_transaction_details(
			'car',
			$book_id,
			$data['fare'],
			$data['admin_markup'],
			$data['agent_markup'],
			$data['convinence'],
			$data['discount'],
			$data['transaction_currency'],
			$data['currency_conversion_rate']
		);

		// Redirect to the voucher page
		redirect(base_url() . 'index.php/voucher/car/' . $book_id . '/' . $temp_booking['booking_source'] . '/BOOKING_CONFIRMED/show_voucher');
	}


	/**
	 * Anitha G
	 */

	public function pre_cancellation(string $app_reference, string $booking_source): void
	{
		// Validate inputs
		if (empty($app_reference) || empty($booking_source)) {
			redirect('security/log_event?event=Invalid Details');
			return;
		}

		// Initialize page data array
		$page_data = [];

		// Retrieve booking details from the model
		$booking_details = $this->car_model->get_booking_details($app_reference, $booking_source);

		// Check if booking details retrieval was successful
		if (!isset($booking_details['status']) || $booking_details['status'] != SUCCESS_STATUS) {
			redirect('security/log_event?event=Invalid Details');
			return;
		}

		// Load the necessary library for booking data formatting
		$this->load->library('booking_data_formatter');

		// Assemble booking data
		$assembled_booking_details = $this->booking_data_formatter->format_car_booking_datas($booking_details, 'b2b');

		// Add formatted data to the page data
		$page_data['data'] = $assembled_booking_details['data'];

		// Load the view for pre-cancellation
		$this->template->view('car/pre_cancellation', $page_data);
	}


	/*
	 * Anitha G
	 * Process the Booking Cancellation
	 * Full Booking Cancellation
	 *
	 */

	public function cancel_booking(string $app_reference, string $booking_source): void
	{
		if (empty($app_reference)) {
			redirect('security/log_event?event=Invalid Details');
			return;
		}

		$master_booking_details = $this->car_model->get_booking_details($app_reference, $booking_source);

		if ($master_booking_details['status'] != SUCCESS_STATUS) {
			redirect('security/log_event?event=Invalid Details');
			return;
		}

		$this->load->library('booking_data_formatter');
		$master_booking_details = $this->booking_data_formatter->format_car_booking_datas($master_booking_details, 'b2b');
		$master_booking_details = $master_booking_details['data']['booking_details'][0];

		load_car_lib($booking_source);
		$cancellation_details = $this->car_lib->cancel_booking($master_booking_details); // Invoke Cancellation Methods

		$query_string = '';
		if ($cancellation_details['status'] === false) {
			$query_string = '?error_msg=' . urlencode($cancellation_details['msg']);
		}

		redirect('car/cancellation_details/' . $app_reference . '/' . $booking_source . $query_string);
	}

	/**
	 * Anitha G
	 * Cancellation Details
	 * @param $app_reference
	 * @param $booking_source
	 */


	/**
	 * Anitha G
	 * Displays Cancellation Refund Details
	 * @param unknown_type $app_reference
	 * @param unknown_type $status
	 */
	public function cancellation_refund_details(): void
	{
		$get_data = $this->input->get();

		if (!isset($get_data['app_reference'], $get_data['booking_source'], $get_data['status']) || $get_data['status'] !== 'BOOKING_CANCELLED') {
			redirect(base_url());
			return;
		}

		$app_reference = trim($get_data['app_reference']);
		$booking_source = trim($get_data['booking_source']);
		$status = trim($get_data['status']);

		$booking_details = $this->car_model->get_booking_details($app_reference, $booking_source, $status);

		if (!isset($booking_details['status']) || $booking_details['status'] != SUCCESS_STATUS) {
			redirect(base_url());
			return;
		}

		$page_data = [
			'booking_data' => $booking_details['data']
		];
		$this->template->view('car/cancellation_refund_details', $page_data);
	}


	public function Voucher(): void
	{
		$this->template->view('voucher/car_voucher');
	}
}
