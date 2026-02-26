<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @package Provab
 * @subpackage Flight
 * @author Balu A<balu.provab@gmail.com>
 * @version V1
 */
class Flight extends CI_Controller
{

    private $current_module;
    public function __construct()
    {
        parent::__construct();
        // $this->output->enable_profiler(TRUE);
        $this->load->model('flight_model');
        $this->load->model('user_model'); // we need to load user model to access provab sms library
        $this->load->library('provab_sms'); // we need this provab_sms library to send sms.
        $this->load->library('social_network/facebook'); //Facebook Library to enable share button
        $this->current_module = $this->config->item('current_module');
        $this->load->library('provab_mailer');
    }
    /**
     * App Validation and reset of data
     */
    /**
     * Handles pre-calendar fare search logic and redirects accordingly.
     *
     * @return void
     */
    public function pre_calendar_fare_search(): void
    {
        // Get query parameters from input
        $params = $this->input->get();
        // Get sanitized flight search data
        $safe_search_data = $this->flight_model->calendar_safe_search_data($params);
        // Extract and validate locations
        $from_loc = $safe_search_data['from_loc'] ?? '';
        $to_loc = $safe_search_data['to_loc'] ?? '';
        // Determine if the flight is domestic
        $is_domestic = false;
        if (!empty($from_loc) && !empty($to_loc)) {
            $is_domestic = $this->flight_model->is_domestic_flight($from_loc, $to_loc);
        }
        $safe_search_data['is_domestic_one_way_flight'] = $is_domestic;
        // Prepare redirect parameters
        $page_params = ['from'     => $is_domestic ? ($safe_search_data['from'] ?? '') : '','to'       => $is_domestic ? ($safe_search_data['to'] ?? '') : '','depature' => $safe_search_data['depature'] ?? '','carrier'  => $safe_search_data['carrier'] ?? '','adult'    => (int) ($safe_search_data['adult'] ?? 1),
        ];
        // Redirect to the calendar fare page
        redirect(base_url('index.php/flight/calendar_fare?' . http_build_query($page_params)));
    }
    /**
     * Displays the calendar fare result page if a booking source is available.
     *
     * @return void
     */
    public function calendar_fare(): void
    {
        // Get query parameters from input
        $params = $this->input->get();
        // Fetch available booking sources
        $active_booking_source = $this->flight_model->active_booking_source();
        if (is_array($active_booking_source) && !empty($active_booking_source)) {
            // Get sanitized and validated search data
            $safe_search_data = $this->flight_model->calendar_safe_search_data($params);
            // Prepare page parameters
            $page_params = ['flight_search_params'   => $safe_search_data,'active_booking_source'  => $active_booking_source,'from_currency'          => get_application_default_currency(),'to_currency'            => get_application_currency_preference()
            ];
            // Load the calendar fare result view
            $this->template->view('flight/calendar_fare_result', $page_params);
        }
    }
    /**
     * Updates the departure date in search data and redirects to new flight search.
     *
     * @return void
     */
    public function add_days_todate(): void
    {
        $get_data = $this->input->get();
        $search_id = isset($get_data['search_id']) ? (int) $get_data['search_id'] : 0;
        $new_date = trim($get_data['new_date'] ?? '');
        if ($search_id > 0 && !empty($new_date)) {
            $safe_data_response = $this->flight_model->get_safe_search_data($search_id);
            if (is_array($safe_data_response) && ($safe_data_response['status'] ?? false) == true) {
                $safe_search_data = $safe_data_response['data'] ?? [];
                // Calculate day difference between old and new date
                $day_diff = get_date_difference($safe_search_data['depature'], $new_date);
                $search_params = ['trip_type'     => trim($safe_search_data['trip_type'] ?? ''),'from'          => trim($safe_search_data['from'] ?? ''),'to'            => trim($safe_search_data['to'] ?? ''),'depature'      => date('d-m-Y', strtotime($new_date)),'adult'         => (int) ($safe_search_data['adult_config'] ?? 1),'child'         => (int) ($safe_search_data['child_config'] ?? 0),'infant'        => (int) ($safe_search_data['infant_config'] ?? 0),'search_flight' => 'search','v_class'       => trim($safe_search_data['v_class'] ?? ''),'carrier'       => $safe_search_data['carrier'] ?? ''
                ];

                if (!empty($safe_search_data['return'] ?? '')) {
                    $search_params['return'] = add_days_to_date($day_diff, $safe_search_data['return']);
                }

                redirect(base_url('index.php/general/pre_flight_search/?' . http_build_query($search_params)));
                return;
            }
        }
        // If any validation fails, show fallback view
        $this->template->view('general/popup_redirect');
    }
    /**
     * Balu A
     * Search Request from Fare Calendar
     */
    /**
     * Builds search parameters from user input and redirects to pre_flight_search.
     * @return void
     */
    public function pre_fare_search_result(): void
    {
        $get_data = $this->input->get();
        $from     = trim($get_data['from'] ?? '');
        $to       = trim($get_data['to'] ?? '');
        $depature = trim($get_data['depature'] ?? '');
        if (!empty($from) && !empty($to) && !empty($depature)) {
            // Fetch airport details
            $from_loc_details = $this->custom_db->single_table_records('flight_airport_list', '*', ['airport_code' => $from]);
            $to_loc_details   = $this->custom_db->single_table_records('flight_airport_list', '*', ['airport_code' => $to]);
            if (
                ($from_loc_details['status'] ?? false) == true &&
                ($to_loc_details['status'] ?? false) == true &&
                isset($from_loc_details['data'][0], $to_loc_details['data'][0])
            ) {
                // Format departure date
                $depature = date('Y-m-d', strtotime($depature));

                // Build formatted "from" string
                $from_airport_code = trim($from_loc_details['data'][0]['airport_code'] ?? '');
                $from_city         = trim($from_loc_details['data'][0]['airport_city'] ?? '');
                $from              = "$from_city ($from_airport_code)";
                // Build formatted "to" string
                $to_airport_code = trim($to_loc_details['data'][0]['airport_code'] ?? '');
                $to_city         = trim($to_loc_details['data'][0]['airport_city'] ?? '');
                $to              = "$to_city ($to_airport_code)";
                // Build search parameters
                $search_params = ['trip_type'      => 'oneway','from'           => $from,'to'             => $to,'depature'       => $depature,'adult'          => 1,'child'          => 0,'infant'         => 0,'search_flight'  => 'search','v_class'        => 'Economy','carrier'        => ['']
                ];

                redirect(base_url('index.php/general/pre_flight_search/?' . http_build_query($search_params)));
                return;
            }
        }
        // If validation fails, show fallback popup
        $this->template->view('general/popup_redirect');
    }
    /**
     * Search Result
     * @param number $search_id
     */
    /**
     * Handles flight search results display based on search ID.
     * @param int $search_id
     * @return void
     */
    public function search(int $search_id): void
    {
        // Retrieve sanitized search data
        $safe_search_data = $this->flight_model->get_safe_search_data($search_id);
        // Retrieve active booking sources
        $active_booking_source = $this->flight_model->active_booking_source();
        // If data is invalid or booking sources are empty, handle redirection or exception
        if (!is_array($active_booking_source) || empty($active_booking_source)) {
            $this->template->view('flight/exception');
            return;
        }
        if (($safe_search_data['status'] ?? false) !== true) {
            $this->template->view('general/popup_redirect');
            return;
        }
        // Safe to proceed
        $search_data = $safe_search_data['data'] ?? [];
        $search_data['search_id'] = abs($search_id);
        // Determine if it's a domestic one-way flight
        $is_domestic = false;
        if (($search_data['trip_type'] ?? '') == 'oneway') {
            $from_loc = $search_data['from_loc'] ?? '';
            $to_loc = $search_data['to_loc'] ?? '';
            $is_domestic = $this->flight_model->is_domestic_flight($from_loc, $to_loc);
        }
        // Prepare parameters for the view
        $page_params = ['flight_search_params'       => $search_data,'active_booking_source'      => $active_booking_source,'from_currency'              => get_application_default_currency(),'to_currency'                => get_application_currency_preference(),'is_domestic_one_way_flight' => $is_domestic,'airline_list'               => $this->db_cache_api->get_airline_code_list(),
        ];
        // Load the search result view
        $this->template->view('flight/search_result_page', $page_params);
    }

    /**
     * Balu A
     * Passenger Details page for final bookings
     * Here we need to run farequote/booking based on api
     * View Page for booking
     */
    public function booking(int $search_id): void
    {
        $pre_booking_params = $this->input->post();
        $booking_setup = $this->validate_and_initialize_booking($pre_booking_params, $search_id);
        if (!$booking_setup) {
            redirect(base_url());
            return;
        }
        [$safe_search_data, $search_hash, $flight_search_url] = $booking_setup;
        $page_data = $this->prepare_page_data($safe_search_data, $search_hash, $flight_search_url);
        $this->handle_booking_source($pre_booking_params, $search_id, $page_data);
    }
    private function validate_and_initialize_booking(array &$pre_booking_params, int $search_id): array|false
    {
        $search_hash = $pre_booking_params['search_hash'] ?? '';
        $booking_source = $pre_booking_params['booking_source'] ?? '';
        $token_key = $pre_booking_params['token_key'] ?? '';
        $token_string = $pre_booking_params['token'] ?? '';
        if (empty($booking_source)) {
            return false;
        }
        load_flight_lib($booking_source);
        $safe_search_data = $this->flight_lib->search_data($search_id);

        if (!$safe_search_data['status']) {
            return false;
        }
        $safe_search_data['data']['search_id'] = $search_id;
        $token = $this->flight_lib->unserialized_token($token_string, $token_key);
        if ($token['status'] == SUCCESS_STATUS) {
            $pre_booking_params['token'] = $token['data']['token'];
        }
        // Generate search URL
        $flight_search_url = base_url() . 'index.php/flight/search/' . $search_id . '?' . http_build_query(['trip_type' => $safe_search_data['data']['trip_type'],'from' => $safe_search_data['data']['from_city'],'from_loc_id' => $safe_search_data['data']['from_loc_id'],'to' => $safe_search_data['data']['to_city'],'to_loc_id' => $safe_search_data['data']['to_loc_id'],'depature' => $safe_search_data['data']['depature'],'v_class' => $safe_search_data['data']['v_class'],'carrier' => [$safe_search_data['data']['carrier']],'adult' => $safe_search_data['data']['adult_config'],'child' => $safe_search_data['data']['child_config'],'infant' => $safe_search_data['data']['infant_config'],'from_loc_airport_name' => $safe_search_data['data']['from_loc_airport_name'],'to_loc_airport_name' => $safe_search_data['data']['to_loc_airport_name'],
        ]);
        return [$safe_search_data, $search_hash, $flight_search_url];
    }

    private function prepare_page_data(array $safe_search_data, string $search_hash, string $flight_search_url): array
    {
        $from_loc = $safe_search_data['data']['from_loc'];
        $to_loc = $safe_search_data['data']['to_loc'];
        $safe_search_data['data']['is_domestic_flight'] = $this->flight_model->is_domestic_flight($from_loc, $to_loc);

        $this->load->model('user_model');

        return ['active_payment_options' => $this->module_model->get_active_payment_module_list(),'search_data' => $safe_search_data['data'],'pax_details' => $this->user_model->get_current_user_details(),'flight_search_url' => $flight_search_url,'session_expiry_details' => $this->flight_lib->set_flight_search_session_expiry(true, $search_hash),
        ];
    }

    private function handle_booking_source(array $pre_booking_params,int $search_id, array $page_data): void
    {
        switch ($pre_booking_params['booking_source']) {
            case PROVAB_FLIGHT_BOOKING_SOURCE:
                $quote_update = $this->fare_quote_booking($pre_booking_params);
                if ($quote_update['status'] == FAILURE_STATUS) {
                    redirect(base_url() . 'index.php/flight/exception?op=Remote IO error @ Session Expiry&notification=session');
                    return;
                }
                $pre_booking_params = $quote_update['data'];
                $extra_services = $this->get_extra_services($pre_booking_params);
                $page_data['extra_services'] = $extra_services['status'] == SUCCESS_STATUS ? $extra_services['data'] : [];
                $currency_obj = new Currency([
                    'module_type' => 'flight',
                    'from' => get_application_currency_preference(),
                    'to' => get_application_currency_preference(),
                ]);
                $is_price_Changed = false;
                $page_data['is_price_Changed'] = $is_price_Changed;
                $page_data['booking_source'] = $pre_booking_params['booking_source'];
                $page_data['currency_obj'] = $currency_obj;
                $page_data['pre_booking_params']['default_currency'] = get_application_default_currency();
                $page_data['iso_country_list'] = $this->db_cache_api->get_iso_country_code();
                $page_data['country_list'] = $this->db_cache_api->get_iso_country_code();
                $page_data['traveller_details'] = $this->user_model->get_user_traveller_details();
                // Flight fare processing
                $updated_flight_details = $pre_booking_params['token'];
                $flight_details = [];

                foreach ($updated_flight_details as $k => $v) {
                    $temp = $this->flight_lib->extract_flight_segment_fare_details($v, $currency_obj, $search_id, $this->current_module);
                    unset($temp[0]['BookingType']);
                    $flight_details[$k] = $temp[0];
                }

                $flight_pre_booking_summary = $this->flight_lib->merge_flight_segment_fare_details($flight_details);
                $pre_booking_params['token'] = $flight_details;
                $page_data['pre_booking_params'] = $pre_booking_params;
                $page_data['pre_booking_summery'] = $flight_pre_booking_summary;
                $total_price = $flight_pre_booking_summary['FareDetails'][$this->current_module . '_PriceDetails']['TotalFare'];
                $page_data['convenience_fees'] = $currency_obj->convenience_fees($total_price, $search_id);

                // Domain / Country Code
                $domain_record = $this->custom_db->single_table_records('domain_list', '*');
                $page_data['active_data'] = $domain_record['data'][0] ?? [];
                $page_data['phone_code'] = $this->custom_db->get_phone_code_list();
                $page_data['user_country_code'] = !empty($this->entity_country_code)
                    ? $this->db_cache_api->get_mobile_code($this->entity_country_code)
                    : ($domain_record['data'][0]['phone_code'] ?? '');
                // Conv Fee
                $convenience_fees_row = $this->private_management_model->get_convinence_fees('flight', $search_id);
                $page_data['org_convience_fee'] = ($convenience_fees_row['type'] ?? '') == 'percentage'
                    ? $convenience_fees_row['value'] ?? 0
                    : 0;
                $page_data['convenience_fees_orginal'] = $convenience_fees_row;
                // State list
                $state_list = $this->custom_db->single_table_records('state_list', '*');
                $page_data['state_list'] = array_column($state_list['data'] ?? [], 'en_name', 'en_name');
                // Fee text
                $fee_text = $this->custom_db->single_table_records('convenience_fees_text', '*');
                $page_data['convenience_fees_text'] = $fee_text['data'][0]['description'] ?? '';
                // Insurance
                $insurance = $this->custom_db->single_table_records('insurance');
                $page_data['insurance'] = $insurance['data'][0] ?? [];
                // Final view
                $this->template->view('flight/tbo/tbo_booking_page', $page_data);
                break;
        }
    }
    /**
     * Fare Quote Booking
     */
    private function fare_quote_booking(array $flight_booking_details): array
    {
        $fare_quote_details = $this->flight_lib->fare_quote_details($flight_booking_details);

        if (!$this->is_successful_fare_quote($fare_quote_details)) {
            return $fare_quote_details;
        }

        $currency_obj = $this->create_flight_currency_object();
        return $this->flight_lib->farequote_data_in_preferred_currency($fare_quote_details, $currency_obj);
    }

    private function is_successful_fare_quote(array $details): bool
    {
        return isset($details['status']) && $details['status'] == SUCCESS_STATUS && valid_array($details);
    }

    private function create_flight_currency_object(): Currency
    {
        return new Currency([
            'module_type' => 'flight',
            'from' => get_api_data_currency(),
            'to' => get_application_currency_preference(),
        ]);
    }
    /**
     * Get Extra Services
     */
    private function get_extra_services(array $flight_booking_details): array
    {
        $extra_service_details = $this->flight_lib->get_extra_services($flight_booking_details);

        return $this->handle_extra_service_response($extra_service_details);
    }
    private function handle_extra_service_response(array $response): array
    {
        // You could add error handling or logging here if needed
        return $response;
    }
    /**
     * Balu A
     * Secure Booking of FLIGHT
     * Process booking no view page
     */
    function pre_booking($search_id): void
    {
        $post_params = $this->input->post();

        if (valid_array($post_params) == false) {
            redirect(base_url());
        }

        // Setting Static Data
        $this->set_static_data($post_params);

        // Token Validation
        $valid_temp_token = $this->validate_token($post_params);
        if ($valid_temp_token !== false) {
            load_flight_lib($post_params['booking_source']);
            // Convert Currency and Process Booking
            $post_params = $this->process_currency_conversion($post_params);
            // Reindex Passport Expiry Month
            $post_params['passenger_passport_expiry_month'] = $this->reindex_passport_month($post_params, $search_id);

            // Serialize Booking Data
            $temp_booking = $this->serialize_booking_data($post_params);
            // Handle Promo Code
            $promocode_discount = $this->handle_promocode($post_params);
            // Handle Payment Method
            $this->handle_payment($post_params, $temp_booking, $promocode_discount, $search_id);
        }

        redirect(base_url() . 'index.php/flight/exception?op=Remote IO error @ FLIGHT Booking&notification=validation');
    }

    private function set_static_data(&$post_params): void
    {
        $post_params['billing_city'] = 'Bangalore';
        $post_params['billing_zipcode'] = '560100';
        $post_params['billing_address_1'] = '2nd Floor, Venkatadri IT Park, HP Avenue, Konnappana Agrahara, Electronic city';
    }


    private function validate_token(array $post_params): mixed
    {
        return unserialized_data($post_params['token'], $post_params['token_key']);
    }


    private function process_currency_conversion(array $post_params): array
    {
        $currency_obj = new Currency(array(
            'module_type' => 'flight',
            'from' => get_application_currency_preference(),
            'to' => get_application_default_currency()
        ));

        $post_params['token'] = unserialized_data($post_params['token']);
        $post_params['token']['token'] = $this->flight_lib->convert_token_to_application_currency($post_params['token']['token'], $currency_obj, $this->current_module);

        // Convert extra services to application currency
        if (isset($post_params['token']['extra_services']) == true) {
            $post_params['token']['extra_services'] = $this->flight_lib->convert_extra_services_to_application_currency($post_params['token']['extra_services'], $currency_obj);
        }

        $post_params['token'] = serialized_data($post_params['token']);
        $post_params['currency_obj'] = $currency_obj;

        return $post_params;
    }


    private function reindex_passport_month(array $post_params, $search_id): array
    {
        return $this->flight_lib->reindex_passport_expiry_month($post_params['passenger_passport_expiry_month'], $search_id);
    }

    private function serialize_booking_data(array $post_params): array
    {
        return $this->module_model->serialize_temp_booking_record($post_params, FLIGHT_BOOKING);
    }


    private function handle_promocode(array $post_params): float
    {
        $promocode_discount = 0;
        if (isset($post_params['promo_code_discount_val']) && $post_params['promo_code_discount_val'] != "0.00") {
            $key = provab_encrypt($post_params['key']);
            $data = $this->custom_db->single_table_records('promo_code_doscount_applied', '*', array('search_key' => $key));
            if ($data['status'] == true) {
                $promocode_discount = $data['data'][0]['discount_value'];
            }
        }
        return $promocode_discount;
    }


    private function handle_payment(array $post_params, array $temp_booking, float $promocode_discount, int $search_id): void
    {
        $book_id = $temp_booking['book_id'];
        $book_origin = $temp_booking['temp_booking_origin'];

        // Fetch the booking data
        $booking_data = $this->module_model->unserialize_temp_booking_record($book_id, $book_origin);
        $book_params = $booking_data['book_attributes'];
        $currency_obj = $post_params['currency_obj'];

        $data = $this->flight_lib->save_booking($book_id, $book_params, $currency_obj, $this->current_module);
        //debug($data);exit;

        // Add extra service price to booking amount
        $extra_services_total_price = $this->flight_model->get_extra_services_total_price($book_id);
        $amount = $data['fare'] + $extra_services_total_price;

        // Convenience Fees
        $currency_obj = new Currency(array(
            'module_type' => 'flight',
            'from' => get_application_currency_preference(),
            'to' => get_application_default_currency()
        ));
        $convenience_fees = ceil($currency_obj->convenience_fees($amount, $search_id));

        $this->process_payment($post_params, $book_id, $book_origin, $amount, $convenience_fees, $promocode_discount, $currency_obj);
    }


    private function process_payment(array $post_params, $book_id, $book_origin, float $amount, float $convenience_fees, float $promocode_discount, Currency $currency_obj): void
    {
        switch ($post_params['payment_method']) {
            case PAY_NOW:
                $this->load->model('transaction');
                $pg_currency_conversion_rate = $currency_obj->payment_gateway_currency_conversion_rate();
                $this->transaction->create_payment_record($book_id, $amount, $post_params['first_name'][0], $post_params['billing_email'], $post_params['passenger_contact'], META_AIRLINE_COURSE, $convenience_fees, $promocode_discount, $pg_currency_conversion_rate);
                redirect(base_url() . 'index.php/payment_gateway/payment/' . $book_id . '/' . $book_origin);
                //redirect(base_url() . 'index.php/flight/process_booking/' . $book_id . '/' . $book_origin);
                break;
            case PAY_AT_BANK:
                echo 'Under Construction - Remote IO Error';
                exit();
                break;
        }
    }
    /* review page */

    function review_passengers(string $app_reference = '', string $book_origin = ''): void
    {
        $page_data = [];
        $page_data['app_reference'] = $app_reference;
        $page_data['book_origin'] = $book_origin;

        $this->load->model('flight_model');
        $this->load->library('booking_data_formatter');

        if (!empty($app_reference)) {
            // These vars should probably be defined or passed in — adding defaults if not.
            $booking_source = PROVAB_FLIGHT_BOOKING_SOURCE;
            $booking_status = ''; // define this appropriately

            $booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source, $booking_status);

            if ($booking_details['status'] == SUCCESS_STATUS) {
                load_flight_lib(PROVAB_FLIGHT_BOOKING_SOURCE);

                $assembled_booking_details = $this->booking_data_formatter->format_flight_booking_data($booking_details, 'b2c');

                $page_data['data'] = $assembled_booking_details['data'];

                $attributes = json_decode($booking_details['data']['booking_details'][0]['attributes'], true);
                $page_data['data']['address'] = $attributes['address'] ?? '';
                $page_data['data']['logo'] = $assembled_booking_details['data']['booking_details'][0]['domain_logo'];
                $page_data['data']['email'] = $booking_details['data']['booking_details'][0]['email'];

                $page_data['country_list'] = $this->db_cache_api->get_iso_country_code();
                $page_data['user_country_code'] = $this->entity_country_code ?: '92';
                $page_data['phone_code'] = $this->custom_db->get_phone_code_list();

                $this->template->view('flight/review_passangers_details', $page_data);
            }
        }
    }
    public function edit_pax(): void
    {
        $params = $this->input->post();

        if (!empty($params)) {
            $id = $params["origin"];
            $app_reference = $params["app_reference"];
            $update_data = [];

            // Handle international passengers
            if (empty($params['is_domestic'])) {
                $passport_issuing_country = $GLOBALS['CI']->db_cache_api->get_country_list(
                    ['k' => 'origin', 'v' => 'name'],
                    ['origin' => $params['passenger_passport_issuing_country']]
                );

                $params['passport_issuing_country'] = $passport_issuing_country[$params['passenger_passport_issuing_country']] ?? '';

                // Format passport expiry date as YYYY-MM-DD
                $expiry_date = sprintf('%04d-%02d-%02d', $params["date"][0], $params["date"][1], $params["date"][2]);
                $update_data['passport_expiry_date'] = $expiry_date;
                $update_data['passport_number'] = $params['passport_number'];
            }

            // Common passenger details
            $update_data['origin'] = $id;
            $update_data['app_reference'] = $app_reference;
            $update_data['first_name'] = $params['first_name'];
            $update_data['last_name'] = $params['last_name'];
            $update_data['date_of_birth'] = $params['date_of_birth'];

            $this->flight_model->update_pax_details($update_data, $id);

            redirect("flight/review_passengers/" . $app_reference);
        }
    }
    public function edit_booking_details(): void
    {
        $params = $this->input->post();

        if (!empty($params)) {
            $id = $params["origin"];
            $app_reference = $params["app_reference"];

            $update_data = [
                "email" => $params["email"],
                "phone" => $params["phone"]
            ];

            $this->flight_model->update_booking_details($update_data, $id);

            redirect("flight/review_passengers/" . $app_reference);
        }
    }
    /*
      process booking in backend until show loader
     */

    function process_booking(string $book_id, string $temp_book_origin): void
    {
        if (empty($book_id) || empty($temp_book_origin) || intval($temp_book_origin) <= 0) {
            redirect(base_url() . 'index.php/flight/exception?op=Invalid request&notification=validation');
            return;
        }

        $page_data = [
            'form_url' => base_url() . 'index.php/flight/secure_booking',
            'form_method' => 'POST',
            'form_params' => [
                'book_id' => $book_id,
                'temp_book_origin' => $temp_book_origin
            ]
        ];

        $this->template->view('share/loader/booking_process_loader', $page_data);
    }
     
    /**
     * Balu A
     * Do booking once payment is successfull - Payment Gateway
     * and issue voucher
     */
    function secure_booking(): void
    {
        $post_data = $this->input->post();

        if (!$this->is_valid_secure_booking_request($post_data)) {
            redirect(base_url() . 'index.php/flight/exception?op=InvalidBooking&notification=invalid');
            return;
        }

        $book_id = trim($post_data['book_id']);
        $temp_book_origin = (int) $post_data['temp_book_origin'];

        $this->load->model('transaction');
        $booking_status = $this->transaction->get_payment_status($book_id);
        //$booking_status['status'] = 'accepted';
        if (($booking_status['status'] ?? '') !== 'accepted') {
            redirect(base_url() . 'index.php/flight/exception?op=Payment Failed&notification=Payment Failed');
            return;
        }

        $temp_booking = $this->module_model->unserialize_temp_booking_record($book_id, $temp_book_origin);
        if (!$temp_booking) {
            redirect(base_url() . 'index.php/flight/exception?op=Booking Retrieval Failed&notification=temp booking not found');
            return;
        }

        $this->process_and_finalize_booking($book_id, $temp_booking);
    }
    private function is_valid_secure_booking_request($post_data): bool
    {
        return true;
        if(!valid_array($post_data) && empty($post_data['book_id']) && empty($post_data['temp_book_origin'])){
            return false;
        }
        
    }

    private function process_and_finalize_booking(string $book_id, array $temp_booking): void
    {
        load_flight_lib($temp_booking['booking_source']);

        if ($temp_booking['booking_source'] == PROVAB_FLIGHT_BOOKING_SOURCE) {
            $currency_obj = new Currency([
                'module_type' => 'flight',
                'from' => admin_base_currency(),
                'to' => admin_base_currency()
            ]);
            //$flight_details = $temp_booking['book_attributes']['token']['token'] ?? [];
        }

        try {
            $booking = $this->flight_lib->process_booking($book_id, $temp_booking['book_attributes']);
        } catch (Exception $e) {
            $booking = ['status' => BOOKING_ERROR, 'message' => $e->getMessage()];
        }

        if (!in_array($booking['status'], [
            SUCCESS_STATUS,
            BOOKING_CONFIRMED,
            BOOKING_PENDING,
            BOOKING_FAILED,
            BOOKING_ERROR,
            BOOKING_HOLD,
            FAILURE_STATUS
        ])) {
            redirect(base_url() . 'index.php/flight/exception?op=booking_exception&notification=' . $booking['message']);
            return;
        }

        $currency_obj = new Currency([
            'module_type' => 'flight',
            'from' => admin_base_currency(),
            'to' => admin_base_currency()
        ]);
        $booking['data']['booking_params']['currency_obj'] = $currency_obj;

        $ticket_details = $booking['data']['ticket'] ?? [];
        $ticket_details['master_booking_status'] = $booking['status'];

        $data = $this->flight_lib->update_booking_details(
            $book_id,
            $booking['data']['booking_params'],
            $ticket_details,
            $this->current_module
        );
        $this->domain_management_model->update_transaction_details(
            'flight',
            $book_id,
            $data['fare'],
            $data['admin_markup'],
            $data['agent_markup'],
            $data['convinence'],
            $data['discount'],
            $data['transaction_currency'],
            $data['currency_conversion_rate']
        );

        if (in_array($data['status'], ['BOOKING_CONFIRMED', 'BOOKING_PENDING', 'BOOKING_HOLD'])) {
            redirect(base_url() . 'index.php/voucher/flight/' .
                $book_id . '/' . $temp_booking['booking_source'] . '/' . $data['status'] . '/show_voucher');
            return;
        }

        // Final fallback redirect
        redirect(base_url() . 'index.php/flight/exception?op=booking_exception&notification=' . $booking['message']);
    }


    /**
     * Balu A
     * Process booking on hold - pay at bank
     * Issue Ticket Later
     */
    function booking_on_hold(string $book_id): void
    {
        load_trawelltag_lib(PROVAB_INSURANCE_BOOKING_SOURCE);

        $response = $this->trawelltag->create_policy([]);

        // Debug response for development (remove or wrap in condition for production)
        debug($response);
        exit;

        // Example static data (can be removed or made dynamic if necessary)
        $response['app_reference'] = $book_id;
        $response['travel_date'] = date('Y-m-d'); // Replaces hardcoded date

        $this->flight_model->save_insurance_details($response);
    }
    /**
     * Balu A
     */
    function pre_cancellation(string $app_reference, string $booking_source): void
    {
        $page_data = [];
        if (empty($app_reference) || empty($booking_source)) {
            redirect('security/log_event?event=Invalid Details');
            return;
        }
        $booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source);

        if ($booking_details['status'] !== SUCCESS_STATUS) {
            redirect('security/log_event?event=Invalid Details');
            return;
        }
        $this->load->library('booking_data_formatter');
        // Assemble Booking Data
        $assembled_booking_details = $this->booking_data_formatter->format_flight_booking_data($booking_details, $this->current_module);
        $page_data['data'] = $assembled_booking_details['data'];
        $this->template->view('flight/pre_cancellation', $page_data);
    }
    /**
     * Balu A
     * @param $app_reference
     */
    function cancel_booking(): void
    {
        $post_data = $this->input->post();
        $has_required_fields = isset($post_data['app_reference'], $post_data['booking_source'], $post_data['transaction_origin'], $post_data['passenger_origin']);
        $has_valid_arrays = is_array($post_data['transaction_origin']) && !empty($post_data['transaction_origin']) &&
            is_array($post_data['passenger_origin']) && !empty($post_data['passenger_origin']);

        if (!$has_required_fields || !$has_valid_arrays) {
            redirect('security/log_event?event=Invalid Details');
            return;
        }
        $app_reference = trim($post_data['app_reference']);
        $booking_source = trim($post_data['booking_source']);
        $passenger_origin = $post_data['passenger_origin'];

        $booking_details = $GLOBALS['CI']->flight_model->get_booking_details($app_reference, $booking_source);

        if ($booking_details['status'] !== SUCCESS_STATUS) {
            redirect('security/log_event?event=Invalid Details');
            return;        }

        load_flight_lib($booking_source);
        // Format booking data
        $this->load->library('booking_data_formatter');
        $formatted = $this->booking_data_formatter->format_flight_booking_data($booking_details, $this->current_module);
        $booking_details = $formatted['data'];
        // Group passenger ticket details
        $grouped_passenger_ticket_details = $this->flight_lib->group_cancellation_passenger_ticket_id($booking_details, $passenger_origin);
        $passenger_origin = $grouped_passenger_ticket_details['passenger_origin'];
        $passenger_ticket_id = $grouped_passenger_ticket_details['passenger_ticket_id'];
        // Process cancellation
        $cancellation_details = $this->flight_lib->cancel_booking($booking_details, $passenger_origin, $passenger_ticket_id);

        redirect('flight/cancellation_details/' . $app_reference . '/' . $booking_source . '/' . $cancellation_details['status']);
    }

    function cancellation_details(string $app_reference, string $booking_source, string $cancellation_status): void
    {
        if (empty($app_reference) || empty($booking_source)) {
            redirect('security/log_event?event=Invalid Details');
            return;
        }
        $master_booking_details = $GLOBALS['CI']->flight_model->get_booking_details($app_reference, $booking_source);

        if ($master_booking_details['status'] !== SUCCESS_STATUS) {
            redirect('security/log_event?event=Invalid Details');
            return;
        }
        $this->load->library('booking_data_formatter');
        $formatted_details = $this->booking_data_formatter->format_flight_booking_data($master_booking_details, $this->current_module);
        $page_data = [
            'data' => $formatted_details['data'],
            'cancellation_status' => $cancellation_status
        ];
        $this->template->view('flight/cancellation_details', $page_data);
    }
    /**
     * Balu A
     */
    function exception(): void
    {
        $module = META_AIRLINE_COURSE;
        $op = $_GET['op'] ?? '';
        $notification = $_GET['notification'] ?? '';

        switch ($notification) {
            case 'Booking is already done for the same criteria for PNR':
                $message = 'Please add another criteria and try again';
                break;
            case 'SEAT NOT AVAILABLE':
            case 'seat no available':
                $message = 'Please book another flight and try again';
                break;
            case 'Sell Failure':
                $message = 'Please try again for the same criteria';
                break;
            case 'The requested class of service is sold out.':
                $message = 'Please try another booking';
                break;
            case 'Supplier Interaction Failed while adding Pax Details. Reason: 18|Presentation|Fusion DSC found an exception !
	The data does not match the maximum length: 
	For data element: freetext
	Data length should be at least 1 and at most 70
	Current position in buffer':
                $message = 'Please add more than 2 characters in the name field and try again';
                break;
            case 'Agency do not have enough balance.':
                $message = 'Please add balance and try again';
                break;
            case 'Invalid CommitBooking Request':
            case 'session':
                $message = 'Session is Expired. Please try again';
                break;
            default:
                $message = $notification . ' Please try again';
                break;
        }

        $exception = $this->module_model->flight_log_exception($module, $op, $message);
        $encoded_exception = base64_encode(json_encode($exception));

        $this->session->set_flashdata([
            'log_ip_info' => true
        ]);

        redirect(base_url() . 'index.php/flight/event_logger/' . $encoded_exception);
    }


    function event_logger(string $exception = ''): void
    {
        $log_ip_info = $this->session->flashdata('log_ip_info');

        $this->template->view('flight/exception', [
            'log_ip_info' => $log_ip_info,
            'exception'   => $exception
        ]);
    }

    function test_server(): void
    {
        $data = $this->custom_db->single_table_records('test', '*', ['origin' => 851]);

        if (!empty($data['data'][0]['test'])) {
        }
    }

    function mail_send_voucher(string $app_reference, string $booking_source, string $booking_status, string $module): void
    {
        send_email($app_reference, $booking_source, $booking_status, $module);
    }
}