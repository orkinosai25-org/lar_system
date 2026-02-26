<?php
declare(strict_types=1);
error_reporting(E_ALL);
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package    Provab
 * @subpackage Car
 * @author     Anitha J<anitha.g.provab@gmail.com>
 * @version    V1
 */

class Car extends CI_Controller {
    private string $current_module;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('car_model');
        $this->current_module = (string) $this->config->item('current_module');
    }

    public function index(): void
    {
        // Placeholder function for the index
    }

    /**
     * Load Car Search Result
     * @param int $search_id unique number which identifies search criteria given by user at the time of searching
     */
    public function search(int $search_id): void
	{
	    $safe_search_data = $this->car_model->get_safe_search_data($search_id);
	    $active_sources = $this->car_model->car_booking_source();

	    if ($safe_search_data['status'] != true || empty($active_sources)) {
	        $view = ($safe_search_data['status'] == true) ? 'general/popup_redirect' : 'flight/exception';
	        $this->template->view($view);
	        return;
	    }
	    $safe_search_data['data']['search_id'] = abs($search_id);

	    $page_params = [
	        'car_search_params' => $safe_search_data['data'],
	        'active_booking_source' => $active_sources,
	        'from_currency' => get_application_default_currency(),
	        'to_currency' => get_application_currency_preference()
	    ];

	    $this->template->view('car/search_result_page', $page_params);
	}


    /**
     * Load Car Request
     */
    public function car_details(int $search_id): void
	{
	    $params = $this->input->post();

	    $this->load->model('user_model');
	    $safe_search_data = $this->car_model->get_safe_search_data($search_id);
	    $currency_obj = new Currency([
	        'module_type' => 'car',
	        'from' => get_api_data_currency(),
	        'to' => get_application_currency_preference()
	    ]);
	    $page_data = [];

	    if (!isset($params['booking_source'])) {
	        redirect(base_url());
	    }

	    load_car_lib($params['booking_source']);

	    if (
	        $params['booking_source'] != PROVAB_CAR_BOOKING_SOURCE ||
	        !isset($params['ResultIndex'], $params['op']) ||
	        $params['op'] != 'get_details' ||
	        $safe_search_data['status'] != true
	    ) {
	        redirect(base_url());
	    }

	    $raw_car_details = $this->car_lib->get_rate_rules($params['ResultIndex']);
	    $temp_record = $this->custom_db->single_table_records('api_country_list', '*');
	    $page_data['phone_code'] = $temp_record['data'];
	    $Domain_record = $this->custom_db->single_table_records('domain_list', '*');
	    $page_data['active_data'] = $Domain_record['data'][0];

	    $total_price = $raw_car_details['data']['RateRule']['CarRuleResult'][0]['TotalCharge']['EstimatedTotalAmount'];
	    $page_data['search_id'] = $search_id;
	    $page_data['country_list'] = $this->db_cache_api->get_iso_country_code();
	    $page_data['convenience_fees'] = $currency_obj->convenience_fees($total_price, $page_data['search_id']);
	    $page_data['active_payment_options'] = $this->module_model->get_active_payment_module_list();
	    $page_data['pax_details'] = $this->user_model->get_current_user_details();
	    $page_data['user_country_code'] = $this->entity_country_code ?? '';

	    if (!isset($raw_car_details['status']) || $raw_car_details['status'] != true) {
	        redirect(base_url() . 'index.php/car/exception?nop=Remote IO error - Cache has no data @ Insufficient&notification=validation');
	    }

	    $car_rate_result = $raw_car_details['data'];
	    $raw_car_rate_result = $raw_car_details['data']['RateRule']['CarRuleResult'][0];
	    $page_data['raw_car_rate_result'] = $raw_car_rate_result;

	    if (empty($car_rate_result)) {
	        redirect(base_url() . 'index.php/car/exception?nop=Remote IO error - Cache has no data @ Insufficient&notification=validation');
	    }

	    $currency_obj = new Currency([
	        'module_type' => 'car',
	        'from' => get_api_data_currency(),
	        'to' => get_application_currency_preference()
	    ]);
	    $car_rate_result = $this->car_lib->car_rule_in_preferred_currency($car_rate_result, 'b2c', $currency_obj, $search_id);
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

	    // Make sure token and temp token match
	    $valid_temp_token = unserialized_data($post_params['token'], $post_params['token_key']);
	    if ($valid_temp_token == false) {
	        redirect(base_url() . 'index.php/car/exception?op=Remote IO error @ Hotel Booking&notification=validation');
	    }

	    load_car_lib($post_params['booking_source']);

	    // Convert Display currency to Application default currency
	    $post_params['token'] = unserialized_data($post_params['token']);
	    $currency_obj = new Currency([
	        'module_type' => 'car',
	        'from' => get_application_currency_preference(),
	        'to' => admin_base_currency()
	    ]);

	    // Serializing the token after modification
	    $post_params['token'] = serialized_data($post_params['token']);
	    $temp_token = unserialized_data($post_params['token']);
	    $temp_token['default_currency'] = admin_base_currency();

	    // Insert to temp_booking and proceed
	    $temp_booking = $this->module_model->serialize_temp_booking_record($post_params, CAR_BOOKING);
	    $book_id = $temp_booking['book_id'];
	    $book_origin = $temp_booking['temp_booking_origin'];

	    $amount = $temp_token['TotalCharge']['EstimatedTotalAmount'];
	    $currency_obj = new Currency([
	        'module_type' => 'car',
	        'from' => admin_base_currency(),
	        'to' => admin_base_currency()
	    ]);

	    $convenience_fees = $currency_obj->convenience_fees($amount, $search_id);
	    $promocode_discount = (float)$post_params['promo_code_discount_val'];
	    //debug($promocode_discount);exit;
	    // Process payment
	    $email = $post_params['billing_email'];
	    $phone = $post_params['passenger_contact'];
	    $currency = $temp_token['TotalCharge']['CurrencyCode'];
	    $verification_amount = roundoff_number($amount);
	    $firstname = $post_params['first_name'];

	    // Check current balance before proceeding further
	    $balance_status = $this->domain_management_model->verify_current_balance($verification_amount, $currency);

	    if ($balance_status != true) {
	        redirect(base_url() . 'index.php/car/exception?op=Amount Hotel Booking&notification=insufficient_balance');
	    }

	    switch ($post_params['payment_method']) {
	        case PAY_NOW:
	            $this->load->model('transaction');
	            $pg_conversion_rate = $currency_obj->payment_gateway_currency_conversion_rate();
	            $this->transaction->create_payment_record(
	                $book_id,
	                $verification_amount,
	                $firstname,
	                $email,
	                $phone,
	                META_CAR_COURSE,
	                $convenience_fees,
	                $promocode_discount,
	                $pg_conversion_rate
	            );
	            redirect(base_url() . 'index.php/car/process_booking/' . $book_id . '/' . $book_origin);
	            break;
	        case PAY_AT_BANK:
	            echo 'Under Construction - Remote IO Error';
	            exit;
	        default:
	            redirect(base_url() . 'index.php/car/exception?op=Unknown payment method&notification=validation');
	            break;
	    }
	}


   	public function exception(): void
	{
	    $module = META_CAR_COURSE;
	    $operation = (string) filter_input(INPUT_GET, 'op', FILTER_SANITIZE_STRING);
	    $notification = (string) filter_input(INPUT_GET, 'notification', FILTER_SANITIZE_STRING);

	    $eid = $this->module_model->log_exception($module, $operation, $notification);

	    $this->session->set_flashdata(['log_ip_info' => true]);
	    redirect(base_url() . 'index.php/car/event_logger/' . $eid);
	}


    public function event_logger(string $eid = ''): void
    {
        $log_ip_info = $this->session->flashdata('log_ip_info');
        $this->template->view('car/exception', ['log_ip_info' => $log_ip_info, 'eid' => $eid]);
    }

    public function booking(): void
    {
        $this->template->view('car/car_booking_page');
    }
    public function process_booking($book_id, $temp_book_origin) {
		$page_data = [];
	    if ($book_id == '' || $temp_book_origin == '' || intval($temp_book_origin) <= 0) {
	        redirect(base_url() . 'index.php/car/exception?op=Invalid request&notification=validation');
	        return;
	    }

	    $page_data['form_url'] = base_url() . 'index.php/car/secure_booking';
	    $page_data['form_method'] = 'POST';
	    $page_data['form_params']['book_id'] = $book_id;
	    $page_data['form_params']['temp_book_origin'] = $temp_book_origin;

	    $this->template->view('share/loader/booking_process_loader', $page_data);
	}

     // Secure Booking function
    public function secure_booking(): void
	{
		
	    $post_data = $this->input->post();

	    // Validate input data
	    if (!is_array($post_data) || !isset($post_data['book_id'], $post_data['temp_book_origin']) 
	        || empty($post_data['book_id']) || (int) $post_data['temp_book_origin'] <= 0) {
	        redirect(base_url() . 'index.php/car/exception?op=InvalidBooking&notification=invalid');
	    }

	    $book_id = trim($post_data['book_id']);
	    $temp_book_origin = (int) $post_data['temp_book_origin'];

	    $this->load->model('transaction');
	    $booking_status = $this->transaction->get_payment_status($book_id);
	    $booking_status['status'] = 'accepted';

	    if ($booking_status['status'] != 'accepted') {
	        redirect(base_url() . 'index.php/car/exception?op=Payment Not Done&notification=validation');
	    }

	    // Run booking request and process booking
	    $temp_booking = $this->module_model->unserialize_temp_booking_record($book_id, $temp_book_origin);

	    load_car_lib($temp_booking['booking_source']);
	    $amount = $temp_booking['book_attributes']['token']['TotalCharge']['EstimatedTotalAmount'];
	    $currency = $temp_booking['book_attributes']['token']['TotalCharge']['CurrencyCode'];

	    $currency_obj = new Currency([
	        'module_type' => 'car',
	        'from' => get_application_currency_preference(),
	        'to' => admin_base_currency()
	    ]);

	    // Check current balance before proceeding
	    $balance_status = $this->domain_management_model->verify_current_balance($amount, $currency);
	    if (!$balance_status || !$temp_booking) {
	        return;
	    }

	    $booking = [];
	    if ($temp_booking['booking_source'] == PROVAB_CAR_BOOKING_SOURCE) {
	        $booking = $this->car_lib->process_booking($book_id, $temp_booking['book_attributes']);
	    }

	    if ($booking['status'] == SUCCESS_STATUS) {
	        $booking['data']['currency_obj'] = $currency_obj;
	        $booking['data']['temp_booking'] = $temp_booking;

	        // Save booking based on booking status and book id
	        $data = $this->car_lib->save_booking($book_id, $booking['data']);
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

	        redirect(base_url() . 'index.php/voucher/car/' . $book_id . '/' . $temp_booking['booking_source'] . '/BOOKING_CONFIRMED/show_voucher');
	    }

	    redirect(base_url() . 'index.php/car/exception?op=booking_exception&notification=' . $booking['msg']);
	}


    /**
     * Pre Cancellation method
     */
   public function pre_cancellation(string $app_reference, string $booking_source): void
	{
	    if (empty($app_reference) || empty($booking_source)) {
	        redirect('security/log_event?event=Invalid Details');
	    }

	    $booking_details = $this->car_model->get_booking_details($app_reference, $booking_source);
	    if ($booking_details['status'] != SUCCESS_STATUS) {
	        redirect('security/log_event?event=Invalid Details');
	    }
	    $page_data = [];
	    $this->load->library('booking_data_formatter');
	    $fbooking_details = $this->booking_data_formatter->format_car_booking_datas($booking_details, 'b2b');

	    $page_data['data'] = $fbooking_details['data'];
	    $this->template->view('car/pre_cancellation', $page_data);
	}

    /**
     * Cancel Booking method
     */
    public function cancel_booking(string $app_reference, string $booking_source): void
	{
	    if (empty($app_reference)) {
	        redirect('security/log_event?event=Invalid Details');
	    }

	    $booking_details = $this->car_model->get_booking_details($app_reference, $booking_source);
	    if ($booking_details['status'] != SUCCESS_STATUS) {
	        redirect('security/log_event?event=Invalid Details');
	    }

	    $this->load->library('booking_data_formatter');
	    $booking_details = $this->booking_data_formatter->format_car_booking_datas($booking_details, 'b2b');
	    $booking_details = $booking_details['data']['booking_details'][0];

	    load_car_lib($booking_source);
	    $cancellation_details = $this->car_lib->cancel_booking($booking_details);

	    $query_string = $cancellation_details['status'] == false
	        ? '?error_msg=' . $cancellation_details['msg']
	        : '';

	    redirect('car/cancellation_details/' . $app_reference . '/' . $booking_source . $query_string);
	}


    /**
     * Cancellation Details method
     */
   	public function cancellation_details(string $app_reference, string $booking_source): void
	{
	    if (empty($app_reference) || empty($booking_source)) {
	        redirect('security/log_event?event=Invalid Details');
	    }

	    $booking_details = $GLOBALS['CI']->car_model->get_booking_details($app_reference, $booking_source);
	    if ($booking_details['status'] != SUCCESS_STATUS) {
	        redirect('security/log_event?event=Invalid Details');
	    }

	    $this->load->library('booking_data_formatter');
	    $booking_details = $this->booking_data_formatter->format_car_booking_datas($booking_details, 'b2b');

	    $page_data = [
	        'data' => $booking_details['data']
	    ];

	    $this->template->view('car/cancellation_details', $page_data);
	}

    /**
     * Cancellation Refund Details method
     */
    public function cancellation_refund_details(): void
	{
	    $get_data = $this->input->get();

	    if (
	        !isset($get_data['app_reference'], $get_data['booking_source'], $get_data['status']) ||
	        $get_data['status'] != 'BOOKING_CANCELLED'
	    ) {
	        redirect(base_url());
	    }

	    $app_reference = trim($get_data['app_reference']);
	    $booking_source = trim($get_data['booking_source']);
	    $status = trim($get_data['status']);

	    $booking_details = $this->car_model->get_booking_details($app_reference, $booking_source, $status);
	    if ($booking_details['status'] != SUCCESS_STATUS) {
	        redirect(base_url());
	    }

	    $page_data = [
	        'booking_data' => $booking_details['data']
	    ];

	    $this->template->view('car/cancellation_refund_details', $page_data);
	}


    /**
     * Voucher method
     */
    public function voucher(): void
    {
        $this->template->view('voucher/car_voucher');
    }
}
