<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab
 * @subpackage Hotel
 * @author     Balu A <balu.provab@gmail.com>
 * @version    V1
 */

class Hotel extends CI_Controller
{
	private string $current_module;  // Declare type for $current_module

	// Constructor doesn't have a return type in PHP
	public function __construct()
	{
		parent::__construct();

		// we need to activate hotel api which are active for current domain and load those libraries
		$this->index();

		// Load the hotel model
		$this->load->model('hotel_model');

		//$this->output->enable_profiler(TRUE);

		// Load the current module from config
		$this->current_module = $this->config->item('current_module');
	}

	// Method for index (if needed) - you can add return type hints based on your actual implementation
	public function index(): void
	{
		// Index method logic, ensure to add actual content here
	}
	/**

	 * Loads the index page of the application.
	 *
	 * This function serves as the entry point for the home or main page of the application.
	 * Any initial setup, fetching data, or views required for the index page should be handled here.
	 * 
	 * @return void
	 */



	/**
	 * Jaganaath
	 */
	function add_days_todate(): void
{
    $get_data = $this->input->get();

    // Check if 'search_id' and 'new_date' are valid
    if (!(isset($get_data['search_id']) && intval($get_data['search_id']) > 0 && isset($get_data['new_date']) && !empty($get_data['new_date']))) {
        $this->template->view('general/popup_redirect');
        return;
    }

    $search_id = intval($get_data['search_id']);
    $new_date = trim($get_data['new_date']);

    // Fetch safe search data
    $safe_search_data = $this->hotel_model->get_safe_search_data($search_id);
    $day_diff = get_date_difference($safe_search_data['data']['from_date'], $new_date);

    // Check if the data is valid
    if (!valid_array($safe_search_data) || $safe_search_data['status'] !== true) {
        $this->template->view('general/popup_redirect');
        return;
    }

    $safe_search_data = $safe_search_data['data'];
    $search_params = [];

    // Prepare search parameters
    $search_params['city'] = trim($safe_search_data['location']);
    $search_params['hotel_destination'] = '';
    $search_params['hotel_checkin'] = date('d-m-Y', strtotime($new_date)); // Adding new Date
    $search_params['hotel_checkout'] = add_days_to_date($day_diff, $safe_search_data['to_date']);
    $search_params['rooms'] = intval($safe_search_data['room_count']);
    $search_params['adult'] = $safe_search_data['adult_config'];
    $search_params['child'] = $safe_search_data['child_config'];
    $search_params['childAge_1'] = $safe_search_data['child_config'];

    // Redirect to the hotel search
    redirect(base_url() . 'index.php/general/pre_hotel_search/?' . http_build_query($search_params));
}

	/**
	 * Balu A
	 * Load Hotel Search Result
	 * @param int $search_id Unique number which identifies search criteria given by user at the time of searching
	 */
function search(int $search_id): void
{
    // Fetch safe search data
    $safe_search_data = $this->hotel_model->get_safe_search_data($search_id);

    // Get all the active booking sources
    $active_booking_source = $this->hotel_model->active_booking_source();

    // If data is invalid or no active booking sources, show popup redirect and return
    if ($safe_search_data['status'] !== true || !valid_array($active_booking_source)) {
        $this->template->view('general/popup_redirect');
        return;
    }

    // Add search_id to the data
    $safe_search_data['data']['search_id'] = abs($search_id);

    // Pass data to the template view
    $this->template->view('hotel/search_result_page', [
        'hotel_search_params' => $safe_search_data['data'],
        'active_booking_source' => $active_booking_source
    ]);
}

	/**
	 * Balu A
	 * Load Hotel Search Result
	 * @param int $search_id Unique number which identifies search criteria given by user at the time of searching
	 */
	function hotel_details_search(int $search_id): void
{
    // Fetch safe search data
    $safe_search_data = $this->hotel_model->get_safe_search_data($search_id);

    // Get all the active booking sources
    $active_booking_source = $this->hotel_model->active_booking_source();

    // If data invalid or no active booking sources, show no hotel found and return
    if ($safe_search_data['status'] !== true || !valid_array($active_booking_source)) {
        $this->template->view('hotel/no_hotel_found');
        return;
    }

    // Add search_id to the data
    $safe_search_data['data']['search_id'] = abs($search_id);

    // Loop through each active booking source
    foreach ($active_booking_source as $booking_source) {
        if ($booking_source['source_id'] !== PROVAB_HOTEL_BOOKING_SOURCE) {
            continue;
        }

        // Load the hotel library based on the booking source
        load_hotel_lib($booking_source['source_id']);

        // Fetch safe search data again
        $safe_search_data = $this->hotel_model->get_safe_search_data($search_id);

        // Get the hotel details list
        $raw_hotel_list = $this->hotel_lib->get_hoteldetails_list(abs($search_id));

        // If hotel list retrieval failed, show no hotel found and return
        if ($raw_hotel_list['status'] !== SUCCESS_STATUS) {
            $this->template->view('hotel/no_hotel_found');
            return;
        }

        $search_index = $raw_hotel_list['data']['HotelSearchResult']['HotelResults'][0]['ResultToken'];

        // Prepare the hotel details URL
        $hotel_details_url = base_url() . 'index.php/hotel/hotel_details/' . abs($search_id) . '?ResultIndex=' . urlencode($search_index) . '&booking_source=' . urlencode($booking_source['source_id']) . '&op=get_details';

        // Redirect to the hotel details page
        redirect($hotel_details_url);
    }
}

	/**
	 * Balu A
	 * Load hotel details based on booking source
	 * @param int $search_id Unique number identifying the search criteria given by the user
	 */
	function hotel_details(int $search_id): void
	{
		// Get input parameters
		$params = $this->input->get();

		// Fetch safe search data
		$safe_search_data = $this->hotel_model->get_safe_search_data($search_id);
		$safe_search_data['data']['search_id'] = abs($search_id);

		// Create Currency object with relevant parameters
		$currency_obj = new Currency([
			'module_type' => 'hotel',
			'from' => get_api_data_currency(),
			'to' => get_application_currency_preference()
		]);

		// Check if 'booking_source' is set in the parameters
		if (isset($params['booking_source'])) {

			// Load different page for different API providers based on the booking source
			load_hotel_lib($params['booking_source']);

			// Check if the required parameters are set for the API call
			if (($params['booking_source'] == PROVAB_HOTEL_BOOKING_SOURCE || $params['booking_source'] == CRS_HOTEL_BOOKING_SOURCE) &&
				isset($params['ResultIndex']) &&
				isset($params['op']) &&
				$params['op'] == 'get_details' &&
				$safe_search_data['status'] == true
			) {

				// Decode the ResultIndex and fetch hotel details
				$params['ResultIndex'] = urldecode($params['ResultIndex']);
				$raw_hotel_details = $this->hotel_lib->get_hotel_details($params['ResultIndex']);

				// If hotel details are fetched successfully
				if ($raw_hotel_details['status']) {

					// Process room price and markup if the first room price exists
					if (isset($raw_hotel_details['data']['HotelInfoResult']['HotelDetails']['first_room_details']['Price'])) {
						$HotelCode = $raw_hotel_details['data']['HotelInfoResult']['HotelDetails']['HotelCode'];

						// Apply markup for the first room's price
						$raw_hotel_details['data']['HotelInfoResult']['HotelDetails']['first_room_details']['Price'] =
							$this->hotel_lib->update_booking_markup_currency(
								$raw_hotel_details['data']['HotelInfoResult']['HotelDetails']['first_room_details']['Price'],
								$currency_obj,
								$search_id,
								true,
								true
							);

						// Add hotel images
						$image_mask = $this->hotel_model->add_hotel_images(
							$search_id,
							$raw_hotel_details['data']['HotelInfoResult']['HotelDetails']['Images'],
							$HotelCode
						);
					}

					// Render the hotel details view
					$this->template->view('hotel/tbo/tbo_hotel_details_page', [
						'currency_obj' => $currency_obj,
						'hotel_details' => $raw_hotel_details['data'],
						'hotel_search_params' => $safe_search_data['data'],
						'active_booking_source' => $params['booking_source'],
						'params' => $params
					]);
				} else {
					// Redirect to exception page if there is an error fetching hotel details
					redirect(base_url() . 'index.php/hotel/exception?op=Remote IO error @ Session Expiry&notification=session');
				}
			} else {
				// If booking source or parameters are invalid, redirect to home
				redirect(base_url());
			}
		} else {
			// If booking source is not provided, redirect to home
			redirect(base_url());
		}
	}
	/**
	 * Balu A
	 * Passenger Details page for final bookings
	 * Here we need to run booking based on API
	 * 
	 * @param int $search_id Unique number identifying the search criteria
	 */
	public function booking(int $search_id): void
	{
		$page_data = [];
		// Get POST data
		$pre_booking_params = $this->input->post();

		// Fetch safe search data
		$safe_search_data = $this->hotel_model->get_safe_search_data($search_id);
		$safe_search_data['data']['search_id'] = abs($search_id);

		// Get active payment options
		$page_data['active_payment_options'] = $this->module_model->get_active_payment_module_list();

		// Check if 'booking_source' is provided
		if (isset($pre_booking_params['booking_source'])) {
			// Load different page for different API providers based on the booking source
			$page_data['search_data'] = $safe_search_data['data'];
			load_hotel_lib($pre_booking_params['booking_source']);

			// Load user details if the user is logged in
			$this->load->model('user_model');
			$page_data['pax_details'] = [];
			$agent_details = $this->user_model->get_current_user_details();
			$page_data['agent_address'] = $agent_details[0]['address'];

			// Set headers to prevent caching
			header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");

			// Check if 'booking_source' is valid and 'op' is set to 'block_room'
			if (($pre_booking_params['booking_source'] == PROVAB_HOTEL_BOOKING_SOURCE || $pre_booking_params['booking_source'] == CRS_HOTEL_BOOKING_SOURCE) &&
				isset($pre_booking_params['op']) && $pre_booking_params['op'] == 'block_room' &&
				$safe_search_data['status'] == true
			) {

				// Unserialize token data
				$pre_booking_params['token'] = unserialized_data($pre_booking_params['token'], $pre_booking_params['token_key']);
				if ($pre_booking_params['token'] != false) {

					// Block room and fetch the result
					$room_block_details = $this->hotel_lib->block_room($pre_booking_params);

					// If room block fails, redirect with the error message
					if ($room_block_details['status'] == false) {
						redirect(base_url() . 'index.php/hotel/exception?op=' . $room_block_details['data']['msg']);
					}

					// Convert API currency data to preferred currency
					$currency_obj = new Currency([
						'module_type' => 'hotel',
						'from' => get_api_data_currency(),
						'to' => get_application_currency_preference()
					]);

					// Adjust room block details with preferred currency
					$room_block_details = $this->hotel_lib->roomblock_data_in_preferred_currency(
						$room_block_details,
						$currency_obj,
						$search_id,
						'b2b'
					);

					// Update markup and currency conversion
					$pre_booking_params = $this->hotel_lib->update_block_details(
						$room_block_details['data']['response']['BlockRoomResult'],
						$pre_booking_params,
						new Currency(['module_type' => 'hotel', 'from' => get_api_data_currency(), 'to' => get_application_currency_preference()])
					);

					// Apply markup to price summary
					$pre_booking_params['markup_price_summary'] = $this->hotel_lib->update_booking_markup_currency(
						$pre_booking_params['price_summary'],
						$currency_obj,
						$safe_search_data['data']['search_id'],
						true,
						true
					);

					// Fetch phone code record
					$phone_code_record = $this->custom_db->single_table_records('user', '*');

					// If room block is successful, proceed with rendering the booking page
					if ($room_block_details['status'] == SUCCESS_STATUS) {
						$page_data['user_country_code'] = !empty($this->entity_country_code) ? $this->entity_country_code : $phone_code_record['data'][0]['country_code'];
						$page_data['booking_source'] = $pre_booking_params['booking_source'];
						$page_data['pre_booking_params'] = $pre_booking_params;
						$page_data['pre_booking_params']['default_currency'] = get_application_default_currency();
						$page_data['iso_country_list'] = $this->db_cache_api->get_iso_country_list();
						$page_data['country_list'] = $this->db_cache_api->get_country_list();
						$page_data['currency_obj'] = $currency_obj;
						$page_data['total_price'] = $this->hotel_lib->total_price($pre_booking_params['markup_price_summary']);
						$page_data['convenience_fees'] = ceil($currency_obj->convenience_fees($page_data['total_price'], $page_data['search_data']['search_id']));
						$page_data['tax_service_sum'] = $this->hotel_lib->tax_service_sum($pre_booking_params['markup_price_summary'], $pre_booking_params['price_summary']);

						// Fetch domain and phone code data
						$Domain_record = $this->custom_db->single_table_records('domain_list', '*');
						$page_data['active_data'] = $Domain_record['data'][0];
						$temp_record = $this->custom_db->single_table_records('api_country_list', '*');
						$page_data['phone_code'] = $temp_record['data'];

						// Render the booking page view
						$this->template->view('hotel/tbo/tbo_booking_page', $page_data);
					}
				}
			} else {
				// Redirect if 'booking_source' or 'op' is invalid
				redirect(base_url());
			}
		} else {
			// Redirect if 'booking_source' is not provided
			redirect(base_url());
		}
	}
	/**
	 * Balu A
	 * Secure Booking of hotel
	 * 255 single adult static booking request 2310
	 * 261 double room static booking request 2308
	 * 
	 * @param int $search_id The search ID for the hotel
	 * @param int $static_search_result_id The static search result ID
	 * @return void
	 */
	public function pre_booking(int $search_id = 2310, int $static_search_result_id = 255): void
	{
		// Get POST parameters
		$post_params = $this->input->post();

		// Set Static Data for Billing (Override the city and zipcode)
		$post_params['billing_city'] = 'Bangalore';
		$post_params['billing_zipcode'] = '560100';

		// Deserialize and validate token
		$valid_temp_token = unserialized_data($post_params['token'], $post_params['token_key']);
		if ($valid_temp_token != false) {
			// Load hotel library for booking source
			load_hotel_lib($post_params['booking_source']);

			// Convert display currency to application default currency
			$post_params['token'] = unserialized_data($post_params['token']);
			$currency_obj = new Currency([
				'module_type' => 'hotel',
				'from' => get_application_currency_preference(),
				'to' => admin_base_currency()
			]);

			// Convert token to application currency and serialize it back
			$post_params['token'] = $this->hotel_lib->convert_token_to_application_currency($post_params['token'], $currency_obj, $this->current_module);
			$post_params['token'] = serialized_data($post_params['token']);
			$temp_token = unserialized_data($post_params['token']);

			// Insert to temp_booking table
			$temp_booking = $this->module_model->serialize_temp_booking_record($post_params, HOTEL_BOOKING);
			$book_id = $temp_booking['book_id'];
			$book_origin = $temp_booking['temp_booking_origin'];

			// Handle hotel booking source specific logic (for example: PROVAB)
			if ($post_params['booking_source'] == PROVAB_HOTEL_BOOKING_SOURCE) {
				$amount = $this->hotel_lib->total_price($temp_token['markup_price_summary']);
				$currency = $temp_token['default_currency'];
			}

			// Create a new Currency object to handle conversion to base currency
			$currency_obj = new Currency([
				'module_type' => 'hotel',
				'from' => admin_base_currency(),
				'to' => admin_base_currency()
			]);

			// Calculate convenience fees
			$convenience_fees = $currency_obj->convenience_fees($amount, $search_id);

			// Handle promocode (currently static)
			$promocode_discount = 0;

			// Prepare details for PGI
			$email = $post_params['billing_email'];
			$phone = $post_params['passenger_contact'];
			$verification_amount = ceil($amount + $convenience_fees - $promocode_discount);

			$firstname = $post_params['first_name'][0];
			$productinfo = META_ACCOMODATION_COURSE;

			// Check current balance before proceeding with payment
			$agent_payable_amount = $currency_obj->get_agent_paybleamount($verification_amount);

			// Verify domain balance status
			$domain_balance_status = $this->domain_management_model->verify_current_balance($agent_payable_amount['amount'], $agent_payable_amount['currency']);

			if ($domain_balance_status == true) {
				// Proceed with payment methods
				switch ($post_params['payment_method']) {
					case PAY_NOW:
						// Create a payment record and redirect to payment gateway
						$this->load->model('transaction');
						$pg_currency_conversion_rate = $currency_obj->payment_gateway_currency_conversion_rate();
						$this->transaction->create_payment_record($book_id, $amount, $firstname, $email, $phone, $productinfo, $convenience_fees, $promocode_discount, $pg_currency_conversion_rate);
						redirect(base_url() . 'index.php/payment_gateway/payment/' . $book_id . '/' . $book_origin);
						break;

					case PAY_AT_BANK:
						// Placeholder for bank payment method
						echo 'Under Construction - Remote IO Error';
						exit;

					default:
						// Handle unknown payment methods
						echo 'Unknown payment method';
						exit;
				}
			} else {
				// Redirect to exception page if insufficient balance
				redirect(base_url() . 'index.php/hotel/exception?op=Amount Hotel Booking&notification=insufficient_balance');
			}
		} else {
			// Redirect to exception page if token is invalid
			redirect(base_url() . 'index.php/hotel/exception?op=Remote IO error @ Hotel Booking&notification=validation');
		}
	}
	/**
	 * Process the booking in the backend until the loader is shown
	 *
	 * @param int $book_id The booking ID
	 * @param int $temp_book_origin The temporary booking origin
	 * @return void
	 */
public function process_booking(string $book_id, int $temp_book_origin): void
{
    $page_data = [];
    // Validate the input parameters
    if ($book_id == '' || $temp_book_origin == '' || $temp_book_origin <= 0) {
        // Redirect to exception page if the request is invalid
        redirect(base_url() . 'index.php/hotel/exception?op=Invalid request&notification=validation');
        return;
    }

    // Prepare data for the form submission
    $page_data['form_url'] = base_url() . 'index.php/hotel/secure_booking';
    $page_data['form_method'] = 'POST';
    $page_data['form_params']['book_id'] = $book_id;
    $page_data['form_params']['temp_book_origin'] = $temp_book_origin;

    // Show the booking process loader page
    $this->template->view('share/loader/booking_process_loader', $page_data);
}

	/**
	 * Balu A
	 * Do booking once payment is successful - Payment Gateway
	 * and issue voucher
	 * 
	 * HB11-152109-443266/1
	 * HB11-154107-854480/2
	 */
	public function secure_booking(): void
	{
		error_reporting(E_ALL);
		// Get POST data
		$post_data = $this->input->post();

		// Validate the input data
		if (
			valid_array($post_data) && isset($post_data['book_id'], $post_data['temp_book_origin']) &&
			!empty($post_data['book_id']) && intval($post_data['temp_book_origin']) > 0
		) {

			$book_id = trim($post_data['book_id']);
			$temp_book_origin = intval($post_data['temp_book_origin']);
		} else {
			// Redirect to exception page if validation fails
			redirect(base_url() . 'index.php/hotel/exception?op=InvalidBooking&notification=invalid');
		}

		// Deserialize temporary booking data
		$temp_booking = $this->module_model->unserialize_temp_booking_record($book_id, $temp_book_origin);

		// Delete the temporary booking record after accessing it
		$this->module_model->delete_temp_booking_record($book_id, $temp_book_origin);

		// Load the hotel library based on the booking source
		load_hotel_lib($temp_booking['booking_source']);

		// Calculate total booking price and currency details
		$total_booking_price = $this->hotel_lib->total_price($temp_booking['book_attributes']['token']['markup_price_summary']);
		$currency = $temp_booking['book_attributes']['token']['default_currency'];
		$currency_obj = new Currency([
			'module_type' => 'hotel',
			'from' => admin_base_currency(),
			'to' => admin_base_currency()
		]);

		// Check agent balance
		$agent_payable_amount = $currency_obj->get_agent_paybleamount($total_booking_price);
		$domain_balance_status = $this->domain_management_model->verify_current_balance($agent_payable_amount['amount'], $agent_payable_amount['currency']);

		if ($domain_balance_status) {
			// Lock table and proceed with booking
			if ($temp_booking) {
				$booking = null;

				// Handle booking based on the booking source
				switch ($temp_booking['booking_source']) {
					case PROVAB_HOTEL_BOOKING_SOURCE:
					case CRS_HOTEL_BOOKING_SOURCE:
						$booking = $this->hotel_lib->process_booking($book_id, $temp_booking['book_attributes']);
						break;
				}

				// Check booking status and proceed
				if ($booking['status'] == SUCCESS_STATUS) {
					// Save booking details
					$booking['data']['currency_obj'] = $currency_obj;
					$data = $this->hotel_lib->save_booking($book_id, $booking['data'], $this->current_module);
					// Update transaction details
					$this->domain_management_model->update_transaction_details(
						'hotel',
						$book_id,
						$data['fare'],
						$data['admin_markup'],
						$data['agent_markup'],
						$data['convinence'],
						$data['discount'],
						$data['transaction_currency'],
						$data['currency_conversion_rate']
					);

					// Redirect to voucher page
					redirect(base_url() . 'index.php/voucher/hotel/' . $book_id . '/' . $temp_booking['booking_source'] . '/' . $data['booking_status'] . '/show_voucher');
				} else {
					// Redirect to exception page if booking failed
					redirect(base_url() . 'index.php/hotel/exception?op=booking_exception&notification=' . $booking['msg']);
				}
			}
		} else {
			// Redirect to exception page if insufficient balance
			redirect(base_url() . 'index.php/hotel/exception?op=Remote IO error @ Insufficient&notification=validation');
		}
	}
	function test(): void
	{
		$currency_obj = new Currency([
			'module_type' => 'hotel',
			'from' => get_application_default_currency(),
			'to' => get_application_default_currency()
		]);

		debug($currency_obj);
	}
	/**
	 *  Balu A
	 *Process booking on hold - pay at bank
	 */
	function booking_on_hold($book_id) {}
	/**
	 * Balu A
	 */
	function pre_cancellation(string $app_reference, string $booking_source): void
{
    if (empty($app_reference) || empty($booking_source)) {
        redirect('security/log_event?event=Invalid Details');
        return;
    }

    $page_data = [];
    $booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source);

    if ($booking_details['status'] != SUCCESS_STATUS) {
        redirect('security/log_event?event=Invalid Details');
        return;
    }

    $this->load->library('booking_data_formatter');

    // Assemble Booking Data
    $assembled_booking_details = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2b');

    // Assuming 'data' is a valid key from $assembled_booking_details
    $page_data['data'] = $assembled_booking_details['data'];

    $this->template->view('hotel/pre_cancellation', $page_data);
}

	/**
	 * Balu A
	 * Process the Booking Cancellation
	 * Full Booking Cancellation
	 */
	function cancel_booking(string $app_reference, string $booking_source): void
	{
		if (!empty($app_reference)) {
			// Assuming $master_booking_details is an array with a 'status' key.
			$master_booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source);

			if ($master_booking_details['status'] == SUCCESS_STATUS) {
				$this->load->library('booking_data_formatter');

				// Format the booking data
				$master_booking_details = $this->booking_data_formatter->format_hotel_booking_data($master_booking_details, 'b2b');

				// Get booking details
				$master_booking_details = $master_booking_details['data']['booking_details'][0];

				// Load hotel library
				load_hotel_lib($booking_source);

				// Perform cancellation
				$cancellation_details = $this->hotel_lib->cancel_booking($master_booking_details);

				// Prepare query string based on cancellation status
				$query_string = $cancellation_details['status'] == false
					? '?error_msg=' . urlencode($cancellation_details['msg'])
					: '';

				redirect('hotel/cancellation_details/' . $app_reference . '/' . $booking_source . $query_string);
			} else {
				redirect('security/log_event?event=Invalid Details');
			}
		} else {
			redirect('security/log_event?event=Invalid Details');
		}
	}
	/**
	 * Balu A
	 * Cancellation Details
	 * 
	 * @param string $app_reference
	 * @param string $booking_source
	 * @return void
	 */
function cancellation_details(string $app_reference, string $booking_source): void
{
	if (empty($app_reference) || empty($booking_source)) {
		redirect('security/log_event?event=Invalid Details');
		return;
	}

	// Fetch booking details
	$master_booking_details = $GLOBALS['CI']->hotel_model->get_booking_details($app_reference, $booking_source);

	if ($master_booking_details['status'] != SUCCESS_STATUS) {
		redirect('security/log_event?event=Invalid Details');
		return;
	}

	$page_data = [];
	$this->load->library('booking_data_formatter');

	// Format the booking data
	$master_booking_details = $this->booking_data_formatter->format_hotel_booking_data($master_booking_details, 'b2b');

	// Pass the data to the view
	$page_data['data'] = $master_booking_details['data'];
	$this->template->view('hotel/cancellation_details', $page_data);
}

	/**
	 * Handle and render the location map with the provided details.
	 *
	 * @return void
	 */
	function map(): void
	{
		// Get the query parameters from the input
		$details = $this->input->get();

		// Initialize geo_codes array with details
		$geo_codes = [
			'data' => [
				'latitude' => (float) $details['lat'], // Assuming lat is a number
				'longitude' => (float) $details['lon'], // Assuming lon is a number
				'hotel_name' => urldecode($details['hn']),
				'star_rating' => (int) $details['sr'], // Assuming star rating is an integer
				'city' => urldecode($details['c']),
				'hotel_image' => urldecode($details['img']),
			]
		];

		// Output the isolated view with the geo_codes data
		echo $this->template->isolated_view('hotel/location_map', $geo_codes);
	}
	/**
	 * Balu A
	 * Displays Cancellation Refund Details
	 * 
	 * @param void
	 * @return void
	 */
	public function cancellation_refund_details(): void
{
	$get_data = $this->input->get();

	// Validate required parameters
	if (
		!isset($get_data['app_reference'], $get_data['booking_source'], $get_data['status']) ||
		$get_data['status'] !== 'BOOKING_CANCELLED'
	) {
		redirect(base_url());
		return;
	}

	$app_reference = trim($get_data['app_reference']);
	$booking_source = trim($get_data['booking_source']);
	$status = trim($get_data['status']);

	// Fetch booking details
	$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source, $status);

	if ($booking_details['status'] != SUCCESS_STATUS) {
		redirect(base_url());
		return;
	}

	$page_data = [
		'booking_data' => $booking_details['data']
	];

	// Render the view
	$this->template->view('hotel/cancellation_refund_details', $page_data);
}

	/**
	 * Balu A
	 * Handles exceptions and redirects with appropriate messages
	 *
	 * @return void
	 */
	function exception(): void
	{
		// Define the module constant
		$module = META_ACCOMODATION_COURSE;

		// Get operation and notification parameters with default values
		$op = $_GET['op'] ?? '';
		$notification = $_GET['notification'] ?? '';

		// Process the operation and notification values
		if ($op == 'Some Problem Occured. Please Search Again to continue') {
			$op = 'Some Problem Occured. ';
		}

		// Set the message based on the notification
		switch ($notification) {
			case 'Invalid CommitBooking Request':
				$message = 'Session is Expired';
				break;
			case 'Some Problem Occured. Please Search Again to continue':
				$message = 'Some Problem Occured';
				break;
			default:
				$message = $notification;
				break;
		}

		// Log the exception
		$exception = $this->module_model->flight_log_exception($module, $op, $message);

		// Base64 encode the exception data
		$exception = base64_encode(json_encode($exception));

		// Set IP log session before redirection
		$this->session->set_flashdata(['log_ip_info' => true]);

		// Check if the notification is 'session'
		$is_session = ($notification == 'session');

		// Redirect to event_logger with exception data and session flag
		redirect(base_url() . 'index.php/hotel/event_logger/' . $exception . '/' . $is_session . '/' . $op);
	}
	/**
	 * Logs event details and displays the exception page
	 *
	 * @param string $exception
	 * @param string $is_session
	 * @param string $op
	 * @return void
	 */
	function event_logger(string $exception = '', string $is_session = '', string $op = ''): void
	{
		// Retrieve the log IP info from the session flash data
		$log_ip_info = $this->session->flashdata('log_ip_info');

		// Clean the operation string if it is 'not available'
		if (strtolower(urldecode($op)) == 'not available') {
			$op = '';
		}

		// Render the exception view with the provided data
		$this->template->view('hotel/exception', [
			'log_ip_info' => $log_ip_info,
			'exception' => $exception,
			'is_session' => $is_session,
			'message' => $op
		]);
	}
	/**
	 * Updates country name in the api_city_master table based on the country code
	 *
	 * @return void
	 */
	function update_country_name(): void
{
	// Set unlimited memory and time for large operations
	ini_set('memory_limit', '-1');
	ini_set('max_execution_time', '0');

	// Retrieve all country records from the api_country_master table
	$select_country = $this->custom_db->single_table_records('api_country_master', '*', []);

	// If country data retrieval failed or is empty, output failure and exit
	if ($select_country['status'] != 1 || empty($select_country['data'])) {
		echo "Failed to retrieve country data";
		exit;
	}

	foreach ($select_country['data'] as $country) {
		// Retrieve cities associated with the current country code
		$select_city_country = $this->custom_db->single_table_records('api_city_master', '*', ['country_code' => $country['iso_country_code']]);

		// Skip if no matching city data found
		if ($select_city_country['status'] != 1 || empty($select_city_country['data'])) {
			continue;
		}

		// Prepare the update data with the country name
		$update_record = [
			'country_name' => $country['country_name']
		];

		// Update the api_city_master table with the country name
		$this->custom_db->update_record('api_city_master', $update_record, ['country_code' => $country['iso_country_code']]);
	}

	// Output success message
	echo "Success";
	exit;
}

	/**
	 * Get Hotel HOLD Booking status (GRN)
	 *
	 * @param string $app_reference
	 * @param string $booking_source
	 * @param string $status
	 * @return void
	 */
	function get_pending_booking_status(string $app_reference, string $booking_source, string $status): void
	{
		// Initialize status to 0 (default)
		$status = 0;

		// Check if the booking status is 'BOOKING_HOLD'
		if ($status == 'BOOKING_HOLD') {
			// Fetch booking details based on the provided app_reference, booking_source, and status
			$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source, $status);

			// Check if booking details are retrieved successfully
			if ($booking_details['status'] == 1 && !empty($booking_details['data']['booking_details'])) {
				$booking_reference = $booking_details['data']['booking_details'][0]['booking_reference'];

				// Load the hotel library for the given booking source
				load_hotel_lib($booking_source);

				// Get the current hotel booking status using the app_reference
				$hold_booking_status = $this->hotel_lib->get_hotel_booking_status($app_reference);

				// Update status to 1 if hold booking status is successful
				if ($hold_booking_status['status'] == true) {
					$status = 1;
				}
			}
		}

		// Output the final status
		echo $status;
	}
	/**
	 * Fetches and outputs an image from the CDN based on provided parameters.
	 *
	 * @param int $index The index of the image result.
	 * @param string $search_id The search ID associated with the image.
	 * @param string $HotelCode The hotel code (base64 encoded).
	 * @return void
	 */
	function image_cdn(int $index, string $search_id, string $HotelCode): void
{
	// Decode the base64 encoded hotel code
	$HotelCode = base64_decode($HotelCode);

	// Fetch the image URL from the database
	$image_url_result = $this->custom_db->single_table_records('hotel_image_url', 'image_url', [
		'search_id' => $search_id,
		'ResultIndex' => $index,
		'hotel_code' => $HotelCode
	]);

	// If image URL is not found, return 404
	if (!isset($image_url_result['data'][0]['image_url'])) {
		header("HTTP/1.1 404 Not Found");
		echo "Image not found.";
		return;
	}

	$image_url = $image_url_result['data'][0]['image_url'];

	// If image URL is invalid, return 400
	if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
		header("HTTP/1.1 400 Bad Request");
		echo "Invalid image URL.";
		return;
	}

	// Output the image
	header("Content-type: image/gif");
	echo file_get_contents($image_url);
}

	/**
	 * Fetches and outputs an image from the CDN based on provided hotel code and image index.
	 *
	 * @param string $HotelCode The hotel code (base64 encoded).
	 * @param int $images_index The index of the image in the database.
	 * @return void
	 */
	function image_details_cdn(string $HotelCode, int $images_index): void
{
	// Decode the base64 encoded hotel code
	$HotelCode = base64_decode($HotelCode);

	// Fetch the image URL from the database based on the hotel code and image index
	$image_url_result = $this->custom_db->single_table_records('hotel_image_url', 'image_url', [
		'hotel_code' => $HotelCode,
		'ResultIndex' => $images_index
	]);

	// If image URL is not found, return 404
	if (!isset($image_url_result['data'][0]['image_url'])) {
		header("HTTP/1.1 404 Not Found");
		echo "Image not found.";
		return;
	}

	$image_url = $image_url_result['data'][0]['image_url'];

	// If image URL is not valid, return 400
	if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
		header("HTTP/1.1 400 Bad Request");
		echo "Invalid image URL.";
		return;
	}

	// Get the image file type (MIME type) dynamically
	$image_headers = get_headers($image_url, 1);
	$content_type = isset($image_headers['Content-Type']) ? $image_headers['Content-Type'] : 'application/octet-stream';

	// Set the correct header for the image type
	header("Content-type: $content_type");

	// Output the image content
	echo file_get_contents($image_url);
}

}
