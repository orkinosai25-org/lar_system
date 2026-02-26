<?php
error_reporting(0);
ini_set('display_errors', 0);
 if (! defined('BASEPATH')) exit('No direct script access allowed');


/**
 *
 * @package    Provab
 * @subpackage Hotel
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */

class Hotel extends CI_Controller
{
	private $current_module;
	public function __construct()
	{
		parent::__construct();
		//we need to activate hotel api which are active for current domain and load those libraries
		$this->load->model('hotel_model');
		$this->load->library('social_network/facebook'); //Facebook Library to enable login button		
		//$this->output->enable_profiler(TRUE);
		$this->current_module = $this->config->item('current_module');
	}
	/**
	 * index page of application will be loaded here
	 */
	function index() {}
	/**
	 *  Balu A
	 * Load Hotel Search Result
	 * @param number $search_id unique number which identifies search criteria given by user at the time of searching
	 */
	public function search(int $search_id): void
	{
		// Fetch safe search data based on the search ID
		$safeSearchData = $this->hotel_model->get_safe_search_data($search_id);
		// Get all active booking sources for hotels
		$activeBookingSource = $this->hotel_model->active_booking_source();

		// Early return if data is invalid or no booking sources
		if ($safeSearchData['status'] != true || empty($activeBookingSource)) {
			$this->template->view('general/popup_redirect');
			return;
		}
		$safeSearchData['data']['search_id'] = abs($search_id);

		// Render the hotel search results view
		$this->template->view(
			'hotel/search_result_page',
			[
				'hotel_search_params' => $safeSearchData['data'],
				'active_booking_source' => $activeBookingSource
			]
		);
	}
	/**
	 *  Balu A
	 * Load Hotel Search Result
	 * @param number $search_id unique number which identifies search criteria given by user at the time of searching
	 */
	public function hotel_details_search(int $search_id): void
	{
		// Fetch safe search data based on the search ID
		$safeSearchData = $this->hotel_model->get_safe_search_data($search_id);
		// Get active booking sources for hotels
		$activeBookingSource = $this->hotel_model->active_booking_source();

		// Early return if data is invalid or no booking sources
		if ($safeSearchData['status'] != true || empty($activeBookingSource)) {
			$this->template->view('hotel/no_hotel_found');
			return;
		}
		$safeSearchData['data']['search_id'] = abs($search_id);
		// Loop through active booking sources
		foreach ($activeBookingSource as $bookingSource) {
			if ($bookingSource['source_id'] == PROVAB_HOTEL_BOOKING_SOURCE) {
				// Load the hotel library based on the source
				load_hotel_lib($bookingSource['source_id']);
				// Fetch safe search data again (assuming this is needed per booking source)
				$safeSearchData = $this->hotel_model->get_safe_search_data($search_id);
				// Get hotel list from the booking source
				$rawHotelList = $this->hotel_lib->get_hoteldetails_list(abs($search_id));

				// Redirect to hotel details if results are found
				if ($rawHotelList['status'] == SUCCESS_STATUS) {
					$searchIndex = $rawHotelList['data']['HotelSearchResult']['HotelResults'][0]['ResultToken'];
					$hotelDetailsUrl = base_url() . 'index.php/hotel/hotel_details/' . $search_id .'?ResultIndex=' . urlencode($searchIndex) .'&booking_source=' . urlencode($bookingSource['source_id']) .'&op=get_details';

					redirect($hotelDetailsUrl);
					return;
				}
				// Early return if no hotel list found
				$this->template->view('hotel/no_hotel_found');
				return;
			}
		}
		// Fallback view if no matching booking source is found
		$this->template->view('hotel/no_hotel_found');
	}

	/**
	 *  Elavarasi
	 * Load hotel details based on booking source
	 */
	public function hotel_details(int $search_id): void
	{
		$params = $this->input->get();
		$safeSearchData = $this->hotel_model->get_safe_search_data($search_id);
		$safeSearchData['data']['search_id'] = abs($search_id);

		$currencyObj = new Currency(['module_type' => 'hotel','from' => get_api_data_currency(),'to' => get_application_currency_preference()
		]);

		if (!isset($params['booking_source'])) {
			redirect(base_url());
			return;
		}

		$bookingSource = $params['booking_source'];
		load_hotel_lib($bookingSource);

		$hasRequiredParams = isset($params['ResultIndex'], $params['op']) &&
			$params['op'] == 'get_details' &&
			in_array($bookingSource, [PROVAB_HOTEL_BOOKING_SOURCE, CRS_HOTEL_BOOKING_SOURCE], true) &&
			$safeSearchData['status'] == true;

		if (!$hasRequiredParams) {
			redirect(base_url());
			return;
		}

		$params['ResultIndex'] = urldecode($params['ResultIndex']);
		$rawHotelDetails = $this->hotel_lib->get_hotel_details($params['ResultIndex']);

		if (!$rawHotelDetails['status']) {
			$message = $rawHotelDetails['data']['Message'] ?? 'Hotel details not found';
			redirect(base_url() . 'index.php/hotel/exception?op=' . $message . '&notification=session');
			return;
		}

		$hotelCode = $rawHotelDetails['data']['HotelInfoResult']['HotelDetails']['HotelCode'];
		$hotelReviews = $this->hotel_model->get_hotel_reviews_by_hotel($hotelCode);
		$params['hotel_reviews'] = $hotelReviews;

		if ($bookingSource == CRS_HOTEL_BOOKING_SOURCE) {
			$rawHotelDetails['data']['HotelInfoResult']['HotelDetails']['Images'] =
				$rawHotelDetails['data']['HotelInfoResult']['HotelDetails']['Images']['data'];
		}

		if (!empty($rawHotelDetails['data']['HotelInfoResult']['HotelDetails']['first_room_details']['Price'])) {
			$rawHotelDetails['data']['HotelInfoResult']['HotelDetails']['first_room_details']['Price'] =
				$this->hotel_lib->update_booking_markup_currency(
					$rawHotelDetails['data']['HotelInfoResult']['HotelDetails']['first_room_details']['Price'],
					$currencyObj,
					$search_id
				);
		}

		$this->template->view('hotel/tbo/tbo_hotel_details_page', ['currency_obj' => $currencyObj,'hotel_details' => $rawHotelDetails['data'],'hotel_search_params' => $safeSearchData['data'],'active_booking_source' => $bookingSource,'params' => $params
		]);
	}
	/**
	 *  Balu A
	 * Passenger Details page for final bookings
	 * Here we need to run booking based on api
	 */
	function booking(int $search_id): void
	{
		$pageData = [];
		$safe_search_data = $this->hotel_model->get_safe_search_data($search_id);
		$pageData['search_data'] = $safe_search_data['data'];
		$pageData['search_data']['search_id'] = $search_id;
		// Get the pre-booking parameters from POST request
		$preBookingParams = $this->input->post();

		// Check if booking source exists in POST parameters
		if (!isset($preBookingParams['booking_source'])) {
			redirect(base_url());
			return;
		}
		$bookingSource = $preBookingParams['booking_source'];
		load_hotel_lib($bookingSource);
		// Fetch safe search data for the given search ID
		$safeSearchData = $this->hotel_model->get_safe_search_data($search_id);
		$safeSearchData['data']['search_id'] = abs($search_id);
		// Initialize the active payment options
		$pageData['active_payment_options'] = $this->module_model->get_active_payment_module_list();
		// Fill in user details if logged in
		$this->load->model('user_model');
		$pageData['pax_details'] = $this->user_model->get_current_user_details();

		// Set headers to prevent caching
		$this->set_no_cache_headers();
		// Check if the booking source and other necessary parameters are set
		if (
			!in_array($bookingSource, [PROVAB_HOTEL_BOOKING_SOURCE, CRS_HOTEL_BOOKING_SOURCE], true) ||
			!isset($preBookingParams['token'], $preBookingParams['op']) ||
			$preBookingParams['op'] != 'block_room' ||
			$safeSearchData['status'] != true
		) {
			redirect(base_url());
			return;
		}

		// Unserialize token and check if it's valid
		$preBookingParams['token'] = unserialized_data($preBookingParams['token'], $preBookingParams['token_key']);

		if ($preBookingParams['token'] == false) {
			redirect(base_url() . 'index.php/hotel/exception?op=Data Modification&notification=Data modified while transfer(Invalid Data received while validating tokens)');
			return;
		}
		// Block the room with the provided details
		$roomBlockDetails = $this->hotel_lib->block_room($preBookingParams);
		
		if ($roomBlockDetails['status'] == false) {
			redirect(base_url() . 'index.php/hotel/exception?op=' . $roomBlockDetails['data']['msg']);
			return;
		}
		// Convert API currency data to preferred currency
		$currencyObj = new Currency([
			'module_type' => 'hotel',
			'from' => get_api_data_currency(),
			'to' => get_application_currency_preference()
		]);
		$roomBlockDetails = $this->hotel_lib->roomblock_data_in_preferred_currency($roomBlockDetails, $currencyObj, $search_id);
		$cancel_currency_obj = new Currency(array('module_type' => 'hotel','from' => get_api_data_currency(), 'to' => get_application_currency_preference()));

		$preBookingParams = $this->hotel_lib->update_block_details($roomBlockDetails['data']['response']['BlockRoomResult'], $preBookingParams,$cancel_currency_obj);
		// Update markup for price summary
		// Convert API currency data to preferred currency
		$currencyObj = new Currency([
			'module_type' => 'hotel',
			'from' => get_application_currency_preference(),
			'to' => get_application_currency_preference()
		]);
		$pageData['markup_price_summary'] = $preBookingParams['markup_price_summary'] = $this->hotel_lib->update_booking_markup_currency(
			$preBookingParams['price_summary'],
			$currencyObj,
			$safeSearchData['data']['search_id']
		);
		
		// Fetch domain list for phone code and user country code
		$DomainRecord = $this->custom_db->single_table_records('domain_list', '*');
		$pageData['user_country_code'] = $this->get_user_country_code($DomainRecord);

		// Prepare data for the booking page
		$this->prepare_booking_page_data($pageData, $preBookingParams, $currencyObj, $DomainRecord);

		// Render the booking page
		$this->template->view('hotel/tbo/tbo_booking_page', $pageData);
	}
	/**
	 * Set no-cache headers to prevent caching of the page.
	 *
	 * @return void
	 */
	private function set_no_cache_headers(): void
	{
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}

	/**
	 * Get user country code from domain or fallback record.
	 *
	 * @param array $domainRecord The domain record fetched from the database.
	 * @return string The user country code.
	 */
	private function get_user_country_code(array $domainRecord): string
	{
		if (!empty($this->entity_country_code)) {
			return $this->db_cache_api->get_mobile_code($this->entity_country_code);
		}

		return $domainRecord['data'][0]['phone_code'];
	}

	/**
	 * Prepares the booking page data.
	 *
	 * @param array $pageData The page data array to be populated.
	 * @param array $preBookingParams The pre-booking parameters.
	 * @param Currency $currencyObj The currency object for the booking.
	 * @param array $roomBlockDetails The room block details after conversion.
	 * @param array $safeSearchData The safe search data.
	 * @param array $DomainRecord The domain record fetched from the database.
	 * @return void
	 */
	private function prepare_booking_page_data(array &$pageData, array $preBookingParams, Currency $currencyObj, array $DomainRecord): void
	{
		// Fill the pageData with necessary details
		$pageData['booking_source'] = $preBookingParams['booking_source'];
		$pageData['pre_booking_params'] = $preBookingParams;
		$pageData['pre_booking_params']['default_currency'] = get_application_currency_preference();
		$pageData['iso_country_list'] = $this->db_cache_api->get_iso_country_list();
		$pageData['country_list'] = $this->db_cache_api->get_country_list();
		$pageData['currency_obj'] = $currencyObj;
		$pageData['total_price'] = $this->hotel_lib->total_price($preBookingParams['markup_price_summary']);
		$pageData['convenience_fees'] = $currencyObj->convenience_fees($pageData['total_price'], $pageData['search_data']['search_id']);
		$pageData['tax_service_sum'] = $this->hotel_lib->tax_service_sum($preBookingParams['markup_price_summary'], $preBookingParams['price_summary']);

		// Get convenience fees text
		$convenienceFeesText = $this->custom_db->single_table_records('convenience_fees_text', '*');
		$pageData['convenience_fees_text'] = $convenienceFeesText['data'][0]['description'];

		// Traveller details
		$pageData['traveller_details'] = $this->user_model->get_user_traveller_details();

		// Get phone code
		$pageData['active_data'] = $DomainRecord['data'][0];
		$tempRecord = $this->custom_db->single_table_records('api_country_list', '*');
		$pageData['phone_code'] = $tempRecord['data'];

		// Flight search data (example)
		$pageData['flight_search_url'] = $this->get_flight_search_url();
	}
	/**
	 * Generates the flight search URL.
	 *
	 * @return string The flight search URL.
	 */
	private function get_flight_search_url(): string
	{
		$departureDate = date('d-m-Y', strtotime(date("d-m-Y") . ' + 7 days'));

		$searchParams = ['trip_type' => 'oneway','from' => 'Bangalore, Bengaluru International Airport (BLR)','from_loc_id' => 801,'to' => 'Dubai, Dubai International Airport (DXB)','to_loc_id' => 1921,'depature' => $departureDate,'v_class' => 'Economy','carrier' => [''],'adult' => 1,'child' => 0,'infant' => 0,'from_loc_airport_name' => 'Bengaluru International Airport','to_loc_airport_name' => 'Dubai International Airport'
		];

		return base_url() . 'index.php/general/pre_flight_search?' . http_build_query($searchParams);
	}
	/**
	 *  Balu A
	 * Secure Booking of hotel
	 * 255 single adult static booking request 2310
	 * 261 double room static booking request 2308
	 */
	function pre_booking(int $search_id): void
	{
		// Get the POST parameters
		$postParams = $this->input->post();

		// Set static billing information
		$this->set_billing_details($postParams);

		// Validate the token and proceed if valid
		$validTempToken = $this->validate_token($postParams);
		if ($validTempToken == false) {
			redirect(base_url() . 'index.php/hotel/exception?op=Remote IO error @ Hotel Booking&notification=validation');
			return;
		}

		load_hotel_lib($postParams['booking_source']);

		// Convert the display currency to the application default currency
		$this->convert_currency($postParams);

		// Serialize temp booking data
		$tempBooking = $this->module_model->serialize_temp_booking_record($postParams, HOTEL_BOOKING);
		$bookId = $tempBooking['book_id'];
		$bookOrigin = $tempBooking['temp_booking_origin'];

		// Calculate the total price and convenience fees
		$amount = $this->hotel_lib->total_price($validTempToken['markup_price_summary']);
		$currency = $validTempToken['default_currency'];
		$convenienceFees = $this->calculate_convenience_fees($amount, $search_id);

		// Apply any promo code discount
		$promoCodeDiscount = $this->apply_promo_code($postParams);

		// Prepare details for the payment gateway
		$paymentDetails = $this->prepare_payment_details($postParams, $amount, $convenienceFees, $promoCodeDiscount);

		// Check domain balance before proceeding
		$domainBalanceStatus = $this->domain_management_model->verify_current_balance($paymentDetails['verification_amount'], $currency);
		if (!$domainBalanceStatus) {
			redirect(base_url() . 'index.php/hotel/exception?op=Amount Hotel Booking&notification=insufficient_balance');
			return;
		}

		$this->process_payment($postParams, $bookId, $paymentDetails, $convenienceFees, $promoCodeDiscount, $bookOrigin);
	}

	/**
	 * Sets the static billing details in the POST parameters.
	 *
	 * @param array $postParams The POST parameters to update.
	 * @return void
	 */
	private function set_billing_details(array &$postParams): void
	{
		$postParams['billing_city'] = 'Bangalore';
		$postParams['billing_zipcode'] = '560100';
		$postParams['billing_address_1'] = '2nd Floor, Venkatadri IT Park, HP Avenue,, Konnappana Agrahara, Electronic city';
	}

	/**
	 * Validates the token provided in the POST parameters.
	 *
	 * @param array $postParams The POST parameters containing the token.
	 * @return mixed The unserialized token if valid, false otherwise.
	 */
	private function validate_token(array $postParams)
	{
		return unserialized_data($postParams['token'], $postParams['token_key']);
	}
	/**
	 * Converts the token to the application default currency.
	 *
	 * @param array $postParams The POST parameters containing the token.
	 * @return void
	 */
	private function convert_currency(array &$postParams): void
	{
		$postParams['token'] = unserialized_data($postParams['token']);
		$currencyObj = new Currency([
			'module_type' => 'hotel',
			'from' => get_application_currency_preference(),
			'to' => admin_base_currency(),
		]);

		$postParams['token'] = $this->hotel_lib->convert_token_to_application_currency($postParams['token'], $currencyObj, $this->current_module);
		$postParams['token'] = serialized_data($postParams['token']);
	}
	/**
	 * Calculates the convenience fees based on the amount and search ID.
	 *
	 * @param float $amount The total amount of the booking.
	 * @param int $searchId The search ID for the booking.
	 * @return float The calculated convenience fees.
	 */
	private function calculate_convenience_fees(float $amount, int $searchId): float
	{
		$currencyObj = new Currency([
			'module_type' => 'hotel',
			'from' => admin_base_currency(),
			'to' => admin_base_currency(),
		]);

		return $currencyObj->convenience_fees($amount, $searchId);
	}
	/**
	 * Applies the promo code discount if available.
	 *
	 * @param array $postParams The POST parameters containing the promo code.
	 * @return float The discount value from the promo code, if any.
	 */
	private function apply_promo_code(array $postParams): float
	{
		if (isset($postParams['key'])) {
			if (isset($postParams['promo_code_discount_val'])) {
				$key = provab_encrypt($postParams['key']);
				$data = $this->custom_db->single_table_records('promo_code_doscount_applied', '*', ['search_key' => $key]);

				if ($data['status'] == true) {
					return (float)$data['data'][0]['discount_value'];
				}
			}
		}

		return 0.0;
	}
	/**
	 * Prepares the payment details for the payment gateway.
	 *
	 * @param array $postParams The POST parameters containing user and booking details.
	 * @param float $amount The total amount to be paid.
	 * @param float $convenienceFees The convenience fees to be added to the total amount.
	 * @param float $promoCodeDiscount The discount applied via the promo code.
	 * @return array The prepared payment details.
	 */
	private function prepare_payment_details(array $postParams, float $amount, float $convenienceFees, float $promoCodeDiscount): array
	{
		return ['email' => $postParams['billing_email'],'phone' => $postParams['passenger_contact'],'verification_amount' => roundoff_number($amount + $convenienceFees - $promoCodeDiscount),'firstname' => $postParams['first_name'][0],'productinfo' => META_ACCOMODATION_COURSE,
		];
	}
	/**
	 * Processes the payment based on the selected payment method.
	 *
	 * @param array $postParams The POST parameters containing payment details.
	 * @param int $bookId The booking ID.
	 * @param array $paymentDetails The payment details array.
	 * @param string $bookOrigin The origin of the booking.
	 * @return void
	 */
	private function process_payment(array $postParams, string $bookId, array $paymentDetails, float $convenienceFees, float $promoCodeDiscount, string $bookOrigin): void
	{
		switch ($postParams['payment_method']) {
			case PAY_NOW:
				$this->load->model('transaction');
				$currencyObj = new Currency(['module_type' => 'hotel','from' => admin_base_currency(),'to' => admin_base_currency(),
				]);
				$pgCurConvRate = $currencyObj->payment_gateway_currency_conversion_rate();
				$this->transaction->create_payment_record($bookId, $paymentDetails['verification_amount'], $paymentDetails['firstname'], $paymentDetails['email'], $paymentDetails['phone'], $paymentDetails['productinfo'], $convenienceFees, $promoCodeDiscount, $pgCurConvRate);

				// Redirect to booking process page
				//redirect(base_url() . 'index.php/payment_gateway/payment/' . $bookId . '/' . $bookOrigin);
				redirect(base_url() . 'index.php/hotel/process_booking/' . $bookId . '/' . $bookOrigin);
				break;

			case PAY_AT_BANK:
				echo 'Under Construction - Remote IO Error';
				exit;

			default:
				redirect(base_url() . 'index.php/hotel/exception?op=Invalid Payment Method');
		}
	}
	/*
		process booking in backend until show loader 
	*/
	function process_booking($book_id, $temp_book_origin): void
	{
		// Basic validation
		if (empty($book_id) || empty($temp_book_origin) || !is_numeric($temp_book_origin) || (int)$temp_book_origin <= 0) {
			redirect(base_url('index.php/hotel/exception?op=Invalid request&notification=validation'));
			return;
		}

		$page_data = ['form_url' => base_url('index.php/hotel/secure_booking'),'form_method' => 'POST','form_params' => [
				'book_id' => $book_id,
				'temp_book_origin' => $temp_book_origin,
			],
		];

		$this->template->view('share/loader/booking_process_loader', $page_data);
	}
	/**
	 *  Balu A
	 *Do booking once payment is successfull - Payment Gateway
	 *and issue voucher
	 *HB11-152109-443266/1
	 *HB11-154107-854480/2
	 */
	function secure_booking(): void
	{
		$post_data = $this->input->post();

		if (
			!is_array($post_data) ||
			empty($post_data['book_id']) ||
			!isset($post_data['temp_book_origin']) ||
			(int)$post_data['temp_book_origin'] <= 0
		) {
			redirect(base_url('index.php/hotel/exception?op=InvalidBooking&notification=invalid'));
		}

		$book_id = trim((string)$post_data['book_id']);
		$temp_book_origin = (int)$post_data['temp_book_origin'];
		$this->load->model('transaction');
		$booking_status = $this->transaction->get_payment_status($book_id);
		$booking_status['status'] = 'accepted';
		if (($booking_status['status'] ?? '') != 'accepted') {
			redirect(base_url('index.php/hotel/exception?op=Payment Not Done&notification=validation'));
		}
		$temp_booking = $this->module_model->unserialize_temp_booking_record($book_id, $temp_book_origin);
		if (!$temp_booking) {
			redirect(base_url('index.php/hotel/exception?op=InvalidBooking&notification=invalid'));
		}
		load_hotel_lib($temp_booking['booking_source']);

		$total_price = $this->hotel_lib->total_price(
			$temp_booking['book_attributes']['token']['markup_price_summary'] ?? []
		);
		$currency = $temp_booking['book_attributes']['token']['default_currency'] ?? '';

		if (!$this->domain_management_model->verify_current_balance($total_price, $currency)) {
			redirect(base_url('index.php/hotel/exception?op=Insufficient Balance&notification=validation'));
		}
		$booking = match ($temp_booking['booking_source']) {
			PROVAB_HOTEL_BOOKING_SOURCE => $this->hotel_lib->process_booking($book_id, $temp_booking['book_attributes']),
			CRS_HOTEL_BOOKING_SOURCE => $this->hotel_lib->process_booking($book_id, $temp_booking['book_attributes']),
			default => redirect(base_url('index.php/hotel/exception?op=Unsupported Booking Source&notification=error')),
		};

		if (($booking['status'] ?? '') != SUCCESS_STATUS) {
			$error_message = urlencode($booking['data']['message'] ?? 'Booking Failed');
			redirect(base_url("index.php/hotel/exception?op=booking_exception&notification={$error_message}"));
		}
		$currency_obj = new Currency(['module_type' => 'hotel','from' => admin_base_currency(),'to' => admin_base_currency()
		]);
		$promo_currency_obj = new Currency([
			'module_type' => 'sightseeing',
			'from' => get_application_currency_preference(),
			'to' => admin_base_currency()
		]);
		$booking['data']['currency_obj'] = $currency_obj;
		$booking['data']['promo_currency_obj'] = $promo_currency_obj;

		$data = $this->hotel_lib->save_booking($book_id, $booking['data']);

		$this->domain_management_model->update_transaction_details(
			'hotel',$book_id,$data['fare'],$data['admin_markup'],$data['agent_markup'],$data['convinence'],$data['discount'],$data['transaction_currency'],$data['currency_conversion_rate']
		);
		$booking_status = $data['booking_status'] ?? 'unknown';
		$source = $temp_booking['booking_source'];
		redirect(base_url("index.php/voucher/hotel/{$book_id}/{$source}/{$booking_status}/show_voucher"));
	}
	// function test(): void
	// {
	// 	$currency_obj = new Currency([
	// 		'module_type' => 'hotel',
	// 		'from' => admin_base_currency(),
	// 		'to' => admin_base_currency()
	// 	]);

	// 	// Use var_dump instead of debug() if debug is a custom function and unavailable
	// 	var_dump($currency_obj);
	// }
	/**
	 *  Balu A
	 *Process booking on hold - pay at bank
	 */
	/*Anitha.G
		Review passenger page for hotel
	*/
	function review(string $app_reference, int $temp_book_origin, int $search_id): void
	{
		$page_data = [];
		$temp_booking = $this->module_model->unserialize_temp_booking_record($app_reference, $temp_book_origin);
		$safe_search_data = $this->hotel_model->get_safe_search_data($search_id);

		if (empty($temp_booking) || empty($safe_search_data['status'])) {
			redirect(base_url() . 'index.php/hotel/exception?op=review_error&notification=data_missing');
			return;
		}

		// Booking Hotel Data
		$token = $temp_booking['book_attributes']['token'];
		$page_data['hotel_data'] = ['HotelName'     => $token['HotelName'] ?? '','HotelAddress'  => $token['HotelAddress'] ?? '','RoomTypeName'  => $token['RoomTypeName'] ?? '','adult_config'  => array_sum($safe_search_data['data']['adult_config'] ?? []),'child_config'  => array_sum($safe_search_data['data']['child_config'] ?? []),'room_count'    => $safe_search_data['data']['room_count'] ?? 0,'checkin_date'  => $safe_search_data['data']['from_date'] ?? '','checkout_date' => $safe_search_data['data']['to_date'] ?? ''
		];
		// Guest Details
		$page_data['guest_data'] = [
			'first_name' => $temp_booking['book_attributes']['first_name'] ?? '',
			'last_name'  => $temp_booking['book_attributes']['last_name'] ?? ''
		];
		// Price Details
		$total_amount_val     = $temp_booking['book_attributes']['total_amount_val'] ?? 0;
		$convenience_amount   = $temp_booking['book_attributes']['convenience_amount'] ?? 0;
		$discount             = $temp_booking['book_attributes']['promo_code_discount_val'] ?? 0;

		$page_data['price_details'] = ['total_amount_val'   => $total_amount_val,'convenience_amount' => $convenience_amount,'discount'           => $discount,'grand_total'        => $total_amount_val + $convenience_amount - $discount
		];
		// Lead Customer Details
		$page_data['lead_pax'] = ['email'              => $temp_booking['book_attributes']['billing_email'] ?? '','contact'            => $temp_booking['book_attributes']['passenger_contact'] ?? '','phone_country_code' => $temp_booking['book_attributes']['phone_country_code'] ?? ''
		];
		// General Info
		$page_data['currency_symbol'] = $temp_booking['book_attributes']['currency_symbol'] ?? '';
		$page_data['book_id']         = $app_reference;
		$page_data['book_origin']     = $temp_book_origin;

		// Load View
		$this->template->view('hotel/review_page', $page_data);
	}
	/**
	 * Balu A
	 */
	function guest_pre_cancellation(string $app_reference, string $booking_source): void
	{
		$page_data = [];
		if (!empty($app_reference) && !empty($booking_source)) {
			$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source);

			if (!empty($booking_details) && $booking_details['status'] == SUCCESS_STATUS) {
				$this->load->library('booking_data_formatter');

				// Assemble Booking Data
				$assem_booking_det = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2c');

				$page_data['data'] = $assem_booking_det['data'] ?? [];

				$this->template->view('hotel/guest_pre_cancellation', $page_data);
				return;
			}
		}
		// Redirect in all failure scenarios
		redirect('security/log_event?event=Invalid Details');
	}
	/**
	 * Balu A
	 */
	function pre_cancellation(string $app_reference, string $booking_source): void
	{
		$page_data = [];
		if (!empty($app_reference) && !empty($booking_source)) {
			$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source);

			if (!empty($booking_details) && $booking_details['status'] == SUCCESS_STATUS) {
				$this->load->library('booking_data_formatter');

				// Format the booking data for B2C
				$assem_booking_det = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2c');

				$page_data['data'] = $assem_booking_det['data'] ?? [];

				$this->template->view('hotel/pre_cancellation', $page_data);
				return;
			}
		}

		// Redirect on any failure
		redirect('security/log_event?event=Invalid Details');
	}
	/*
	 * Balu A
	 * Process the Booking Cancellation
	 * Full Booking Cancellation
	 *
	 */
	function cancel_booking(string $app_reference, string $booking_source, string $cancel_type = ''): void
	{
		if (!empty($app_reference)) {
			$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source);

			if ($booking_details['status'] == SUCCESS_STATUS) {
				$this->load->library('booking_data_formatter');

				$formatted_details = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2c');
				$booking_data = $formatted_details['data']['booking_details'][0] ?? null;

				if (!$booking_data) {
					redirect('security/log_event?event=Missing Booking Data');
				}

				load_hotel_lib($booking_source);

				$cancellation_result = $this->hotel_lib->cancel_booking($booking_data);

				$query_string = $cancellation_result['status'] ? '' : '?error_msg=' . urlencode($cancellation_result['msg'] ?? 'Cancellation Failed');

				redirect("hotel/cancellation_details/{$app_reference}/{$booking_source}/{$cancel_type}{$query_string}");
				return;
			}

			redirect('security/log_event?event=Invalid Details');
		}
	}
	/**
	 * Balu A
	 * Cancellation Details
	 * @param $app_reference
	 * @param $booking_source
	 */
	function cancellation_details(string $app_reference, string $booking_source, string $cancel_type = ''): void
	{
		if (!empty($app_reference) && !empty($booking_source)) {
			$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source);

			if ($booking_details['status'] == SUCCESS_STATUS) {
				$this->load->library('booking_data_formatter');

				$formatted_details = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2c');

				$page_data = [
					'data' => $formatted_details['data'],
					'cancel_type' => $cancel_type,
				];

				$this->template->view('hotel/cancellation_details', $page_data);
				return;
			}
		}

		redirect('security/log_event?event=Invalid Details');
	}
	function map(): void
	{
		$details = $this->input->get(null, true); // Enable XSS filtering

		$geo_codes = [
			'data' => ['latitude'     => $details['lat'] ?? '','longtitude'   => $details['lon'] ?? '','hotel_name'   => urldecode($details['hn'] ?? ''),'star_rating'  => $details['sr'] ?? '','city'         => urldecode($details['c'] ?? ''),'hotel_image'  => urldecode($details['img'] ?? ''),'price'        => $details['price'] ?? '',
			]
		];

		echo $this->template->isolated_view('hotel/location_map', $geo_codes);
	}
	/**
	 * Balu A
	 */
	function exception(): void
	{
		$module = META_ACCOMODATION_COURSE;
		$op_val = $_GET['op'] ?? '';
		$notification = $_GET['notification'] ?? '';

		// Normalize operation message for specific cases
		if ($op_val == 'Some Problem Occured. Please Search Again to continue') {
			$op_val = 'Some Problem Occured.';
		}

		// Handle notification-specific messages
		$message = match ($notification) {
			'Invalid CommitBooking Request' => 'Session is Expired',
			'Some Problem Occured. Please Search Again to continue' => 'Some Problem Occured',
			default => $notification,
		};

		// Log exception in flight log
		$exception = $this->module_model->flight_log_exception($module, $op_val, $message);
		$exception = base64_encode(json_encode($exception));

		// Set session log info
		$this->session->set_flashdata(['log_ip_info' => true]);

		// Handle session-related notification
		$is_session = ($notification == 'session');

		// Redirect to event logger
		redirect(base_url() . 'index.php/hotel/event_logger/' . $exception . '/' . ($is_session ? 'true' : 'false') . '/' . $op_val);
	}

	function event_logger(string $exception = '', string $is_session = '', string $op_val = ''): void
	{
		// Retrieve IP logging info from session flash data
		$log_ip_info = $this->session->flashdata('log_ip_info');

		// Handle 'not available' operation type by clearing $op_val
		$op_val = strtolower(urldecode($op_val)) == 'not available' ? '' : $op_val;

		// Prepare data for the view
		$view_data = ['log_ip_info' => $log_ip_info,'exception' => $exception,'is_session' => $is_session,'message' => $op_val,
		];

		// Load the exception view with the prepared data
		$this->template->view('hotel/exception', $view_data);
	}

	function get_hotel_images(): void
	{
		// Define the hotel code
		$hotel_code = 'H!0634455';

		// Check if hotel code is set
		if (!empty($hotel_code)) {

			// Handle the specific hotel booking source
			switch (PROVAB_HOTEL_BOOKING_SOURCE) {

				case PROVAB_HOTEL_BOOKING_SOURCE:
					// Load the hotel library
					load_hotel_lib(PROVAB_HOTEL_BOOKING_SOURCE);

					// Fetch raw hotel images using the hotel code
					$raw_hotel_images = $this->hotel_lib->get_hotel_images($hotel_code);

					// Debug the response and exit (for testing purposes)
					debug($raw_hotel_images);
					exit;
			}
		}
		exit;
	}
	function image_cdn(int $index, int $search_id, string $HotelCode): void
	{
		// Decode the HotelCode
		$decoded_hotel_code = base64_decode($HotelCode, true);

		// Fetch the image URL from the database
		$image_url_result = $this->custom_db->single_table_records('hotel_image_url', 'image_url', ['search_id' => $search_id,'ResultIndex' => $index,'hotel_code' => $decoded_hotel_code
		]);

		// Extract the image URL
		$image_url = $image_url_result['data'][0]['image_url'];

		// Set the appropriate header for the image type
		header("Content-Type: image/gif");

		// Output the image content
		echo file_get_contents($image_url);
		exit;
	}
	function image_details_cdn(string $HotelCode, int $images_index): void
	{
		// Decode the HotelCode
		$decoded_hotel_code = base64_decode($HotelCode, true);

		// Fetch the image URL from the database
		$image_url_result = $this->custom_db->single_table_records('hotel_image_url', 'image_url', [
			'hotel_code' => $decoded_hotel_code,
			'ResultIndex' => $images_index
		]);

		// Extract the image URL
		$image_url = $image_url_result['data'][0]['image_url'];

		// Set the appropriate header for the image type (assuming gif, but can be extended)
		header("Content-Type: image/gif");

		// Output the image content
		echo file_get_contents($image_url);
		exit;
	}
	//Agoda BookingList
	function get_agoda_hotel_bookings(): void
	{
		// Attempt to load the hotel library
		try {
			load_hotel_lib(PROVAB_HOTEL_BOOKING_SOURCE);

			// Fetch the bookings list from Agoda
			$bookings = $this->hotel_lib->get_agoda_bookings_list();

			// Handle if no bookings are found
			if (empty($bookings)) {
				log_message('info', 'No Agoda bookings found.');
				echo 'No bookings found.';
				return;
			}

			// Return or process the bookings as needed (e.g., pass it to a view or output it)
			echo json_encode($bookings); // or pass the bookings to a view if needed

		} catch (Exception $e) {
			// Log error and show an appropriate message
			log_message('error', 'Error loading Agoda hotel bookings: ' . $e->getMessage());
			echo 'An error occurred while fetching Agoda bookings.';
		}
	}
	function hotel_feedback(string $app_reference): void
	{
		$page_data = [];
		// Fetch booking details
		$hotel_details = $this->hotel_model->get_booking_details($app_reference);

		// Check if the booking details were successfully fetched
		if ($hotel_details['status'] != SUCCESS_STATUS) {
			log_message('error', 'Failed to fetch hotel details for app_reference: ' . $app_reference);
			return;
		}

		// Extract necessary hotel details
		$hotel_name = $hotel_details['data']['booking_details'][0]['hotel_name'];
		$booking_origin = $hotel_details['data']['booking_details'][0]['origin'];
		// $hotel_code = $hotel_details['data']['booking_details'][0]['hotel_code'];
		// $city_id = $hotel_details['data']['booking_details'][0]['city_id'];
		// $customer_name = $hotel_details['data']['booking_customer_details'][0]['first_name'] . ' ' . $hotel_details['data']['booking_customer_details'][0]['last_name'];

		// Prepare page data
		$page_data['hotel_name'] = $hotel_name;
		$page_data['app_reference'] = $app_reference;
		$this->template->view('hotel/hotel_feedback_page', $page_data);

		// Check if the feedback form was submitted
		$post_params = $this->input->post();

		// If feedback data is not valid, return
		if (empty($post_params) || !isset($post_params['star_rating'], $post_params['page_title'], $post_params['page_description'])) {
			log_message('error', 'Incomplete feedback data for app_reference: ' . $app_reference);
			return;
		}

		// Prepare the feedback data
		$insert_data = ['star_rating' => (int)$post_params['star_rating'], // Cast to int to ensure it's a valid rating'title' => $post_params['page_title'],'description' => $post_params['page_description'],'hotel_code' => $hotel_code,'city_id' => $city_id,'hotel_name' => $hotel_name,'reviewer_name' => $customer_name,'created_datetime' => date('Y-m-d H:i:s')
		];

		try {
			// Insert the feedback into the database
			$this->custom_db->insert_record('hotel_feedback', $insert_data);

			// Update the booking details to reflect that feedback was given
			$this->custom_db->update_record('hotel_booking_details', ['feedback' => 1], ['origin' => $booking_origin]);

			// Set a success message to be displayed to the user
			$this->session->set_flashdata(['message' => 'UL0098', 'type' => SUCCESS_MESSAGE]);
		} catch (Exception $e) {
			// Log any errors during the feedback process
			log_message('error', 'Error inserting hotel feedback: ' . $e->getMessage());

			// Optionally, set an error flash message
			$this->session->set_flashdata(['message' => 'Error occurred while submitting your feedback.', 'type' => ERROR_MESSAGE]);
		}
	}
	function hotel_reviews(string $hotel_code = ''): void
	{
		$page_data = [];
		if (empty($hotel_code) == false) {
			// Fetch hotel reviews from the database
			$hotel_reviews = $this->custom_db->single_table_records('hotel_feedback', '*', ['hotel_code' => $hotel_code]);
		}
		if ($hotel_reviews['status'] != SUCCESS_STATUS) {
			// Pass the reviews data to the view
			$page_data['reviews'] = $hotel_reviews['data'];
			$this->template->view('hotel/hotel_reviews', $page_data);
		}
	}
}
