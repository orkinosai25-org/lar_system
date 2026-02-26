<?php
if (!defined('BASEPATH'))
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

class Ajax extends CI_Controller {

    private $current_module;
   
    public function __construct() {
        parent::__construct();
        if (is_ajax() == false) {
            //$this->index();
        }
        ob_start();
        $this->load->model('flight_model');
        $this->load->model('car_model');        
       
        $this->load->library('Converter');
        $this->current_module = $this->config->item('current_module');
    }

    /**
     * index page of application will be loaded here
     */
    function index() {
        
    }

    /**
     * get city list based on country
     * @param $country_id
     * @param $default_select
     */
    public function get_city_list(int $country_id = 0, int $default_select = 0): void
    {
        if ($country_id > 0) {
            $condition = ['country' => $country_id];
            $order_by = ['destination' => 'asc'];

            // Fetch cities based on the provided country ID
            $option_list = $this->custom_db->single_table_records('api_city_list', 'origin as k, destination as v', $condition, 0, 1000000, $order_by);

            // Check if the data is valid and not empty
            if (!empty($option_list['data'])) {
                // Return options as compressed output
                echo get_compressed_output(generate_options($option_list['data'], [$default_select]));
                exit;
            } 
        }
    }


    /**
     *
     * @param $continent_id
     * @param $default_select
     * @param $zone_id
     */
    public function get_country_list(array $continent_id = [], int $default_select = 0, int $zone_id = 0): void
    {
        $this->load->model('general_model');

        // Decode the continent_id if it is passed as a URL encoded string
        $continent_id = urldecode($continent_id);

        // Validate the continent_id (ensure it's an integer and non-zero)
        if (intval($continent_id) <= 0) {
            return; // Early return if continent_id is invalid
        }

        // Fetch the country list based on continent and zone
        $option_list = $this->general_model->get_country_list($continent_id, $zone_id);

        // Return the options as compressed output if valid data is found
        if (!empty($option_list['data'])) {
            echo get_compressed_output(generate_options($option_list['data'], [$default_select]));
            exit;
        }

        // If no countries found, simply return
        return;
    }


    /**
     * Get Location List
     */
    function location_list(int $limit = AUTO_SUGGESTION_LIMIT): void
    {
        $chars = $_GET['term'] ?? '';
        if (empty($chars)) {
            $this->output_compressed_data([]);
            return;
        }    
        $list = $this->general_model->get_location_list($chars, $limit);
        if (!is_array($list) || empty($list)) {
            $this->output_compressed_data([]);
            return;
        }    
        $temp_list = array_map(
            fn($v, $k) => ['id' => $k, 'label' => $v['name'], 'value' => $v['origin']],
            $list,
            array_keys($list)
        );    
        $this->output_compressed_data($temp_list);
    }  

    /**
     * Get Location List
     */
    function city_list(int $limit = AUTO_SUGGESTION_LIMIT): void
    {
        $chars = $_GET['term'];
        $list = $this->general_model->get_city_list($chars, $limit);

        $temp_list = [];

        // Return early if the list is not valid
        if (!valid_array($list)) {
            $this->output_compressed_data($temp_list);  // Return empty if no valid cities are found
            return;
        }

        // Populate temp_list if valid data is available
        foreach ($list as $k => $v) {
            $temp_list[] = ['id' => $k, 'label' => $v['name'], 'value' => $v['origin']];
        }

        // Output the compressed data
        $this->output_compressed_data($temp_list);
    }


    /**
     * Balu A
     * @param unknown_type $currency_origin origin of currency - default to USD
     */
    function get_currency_value(int $currency_origin = 0): void
    {
        $data = $this->custom_db->single_table_records('currency_converter', 'value', ['id' => $currency_origin]);

        // Default response if no valid data
        $response = valid_array($data['data']) ? $data['data'][0]['value'] : 1;

        header('Content-type:application/json');
        echo json_encode(['value' => $response]);
        exit;
    }

    function get_currency_details(): void
    {
        // Create the Currency objects for conversion
        $currency_obj = new Currency([
            'module_type' => 'flight',
            'from' => get_api_data_currency(),
            'to' => get_application_currency_preference()
        ]);

        $currency_value = $currency_obj->conversion_cache[get_api_data_currency() . get_application_currency_preference()];

        $currency_obj1 = new Currency([
            'module_type' => 'flight',
            'from' => get_application_default_currency(),
            'to' => get_application_currency_preference()
        ]);

        $currency_value1 = $currency_obj1->conversion_cache[get_application_default_currency() . get_application_currency_preference()];

        header('Content-type:application/json');
        echo json_encode([
            'value' => $currency_value,
            'currency' => $currency_obj->to_currency,
            'default_cur_conv_rate' => $currency_value1
        ]);
        exit;
    }


    /*
     *
     * Flight(Airport) auto suggest
     *
     */

    function get_airport_code_list(): void
    {
        $term = $this->input->get('term'); // Retrieve the search term that autocomplete sends
        $term = trim(strip_tags($term));
        $result = [];
        $flagpath = base_url() . 'extras/custom/' . CURRENT_DOMAIN_KEY . '/images/flags/';
        $__airports = $this->flight_model->get_airport_list($term)->result();

        // Fallback if no airports are found
        $__airports = valid_array($__airports) ? $__airports : $this->flight_model->get_airport_list('')->result();

        foreach ($__airports as $airport) {
            $airports = [
                'label' => $airport->airport_city . ', ' . $airport->country . ' (' . $airport->airport_code . ')',
                'value' => $airport->airport_city . ', ' . $airport->airport_name . ' (' . $airport->airport_code . ')',
                'id' => $airport->origin,
                'airport_name' => $airport->airport_name,
                'country_code' => $flagpath . strtolower($airport->CountryCode) . '.png',
                'category' => empty($airport->top_destination) ? 'Search Results' : 'Top cities',
                'type' => empty($airport->top_destination) ? 'Search Results' : 'Top cities',
            ];

            $result[] = $airports;
        }

        $this->output_compressed_data($result);
    }

    /*
     *
     * Car(Airport) auto suggest
     *
     */
    function get_airport_city_list(): void
    {
        $term = $this->input->get('term'); // Retrieve the search term that autocomplete sends
        $term = trim(strip_tags($term));
        $result = [];

        // Fetch airports matching the search term
        $__airports = $this->car_model->get_airport_list($term)->result();
        $__airports = valid_array($__airports) ? $__airports : $this->car_model->get_airport_list('')->result();

        // Process each airport
        foreach ($__airports as $airport) {
            $airports = [
                'label' => $airport->Airport_Name_EN . ',' . $airport->Country_Name_EN,
                'id' => $airport->origin,
                'airport_code' => $airport->Airport_IATA,
                'category' => 'Search Results',
                'type' => 'Search Results',
            ];
            $result[] = $airports;
        }

        // Fetch city list matching the search term
        $city_list = $this->car_model->get_city_list($term)->result();
        $city_list = valid_array($city_list) ? $city_list : $this->car_model->get_city_list('')->result();

        // Process each city
        foreach ($city_list as $city) {
            if ($city->City_ID != "") {
                $city_result = [
                    'label' => $city->City_Name_EN . ' City/Downtown,' . $city->Country_Name_EN,
                    'id' => $city->origin,
                    'airport_code' => $city->Airport_IATA,
                    'country_id' => $city->Country_ISO,
                    'category' => empty($city->top_destination) ? 'Search Results' : 'Top cities',
                    'type' => empty($city->top_destination) ? 'Search Results' : 'Top cities',
                ];
                $result[] = $city_result;
            }
        }

        // Output the result in compressed format
        $this->output_compressed_data($result);
    }

    /*
     *
     * Hotels City auto suggest
     *
     */

    function get_hotel_city_list(): void
    {
        $this->load->model('hotel_model');
        $term = $this->input->get('term'); // Retrieve the search term that autocomplete sends
        $term = trim(strip_tags($term));

        // Fetch hotel city list matching the search term
        $data_list = $this->hotel_model->get_hotel_city_list($term);
        $data_list = valid_array($data_list) ? $data_list : $this->hotel_model->get_hotel_city_list('');

        $result = [];

        // Process each city list
        foreach ($data_list as $city_list) {
            $suggestion_list = [
                'label' => $city_list['city_name'] . ', ' . $city_list['country_name'],
                'value' => hotel_suggestion_value($city_list['city_name'], $city_list['country_name']),
                'id' => $city_list['origin'],
                'category' => empty($city_list['top_destination']) ? 'Search Results' : 'Top cities',
                'type' => empty($city_list['top_destination']) ? 'Search Results' : 'Top cities',
                'count' => intval($city_list['cache_hotels_count']) > 0 ? $city_list['cache_hotels_count'] : 0,
            ];

            $result[] = $suggestion_list;
        }

        // Output the result in compressed format
        $this->output_compressed_data($result);
    }


    /**
    * Get Sightsseeing Category List
    */
    function get_ss_category_list(): void
    {
        $get_params = $this->input->get();

        if (empty($get_params) || empty($get_params['city_id'])) {
            echo "0";
            exit;
        }
        load_sightseen_lib(PROVAB_SIGHTSEEN_BOOKING_SOURCE);
        $select_cate_id = $get_params['Select_cate_id'] ?? 0;
        $get_params['Select_cate_id'] = $select_cate_id;

        $category_list = $this->sightseeing_lib->get_category_list($get_params);
        if ($category_list['status'] != SUCCESS_STATUS) {
            echo "0";
            exit;
        }
        $cate_response = $this->sightseeing_lib->format_category_response(
            $category_list['data']['CategoryList'],
            $select_cate_id
        );
        if ($cate_response['status'] != SUCCESS_STATUS) {
            echo "0";
            exit;
        }

        echo json_encode($cate_response['data']);
        exit;
    }

    function get_all_hotel_list(): void {
        $response = array();
        $response['data'] =array();
        $response['msg'] = array();
        $response['status'] = FAILURE_STATUS;
        $search_params = $this->input->get();
        if ($search_params['op'] == 'load' && intval($search_params['search_id']) > 0 && isset($search_params['booking_source']) == true) {
            load_hotel_lib($search_params['booking_source']);
            switch ($search_params['booking_source']) {
                case PROVAB_HOTEL_BOOKING_SOURCE :
                    //Meaning hotels are loaded first time
                    $raw_hotel_list = $this->hotel_lib->get_hotel_list(abs($search_params['search_id']));
                    //debug($raw_hotel_list);exit;
                    if ($raw_hotel_list['status']) {
                        $attr = [];
                        //Converting API currency data to preferred currency
                        $currency_obj = new Currency(array('module_type' => 'hotel', 'from' => get_api_data_currency(), 'to' => get_application_currency_preference()));
                        $raw_hotel_list = $this->hotel_lib->search_data_in_preferred_currency($raw_hotel_list, $currency_obj);
                        //Display 
                        $currency_obj = new Currency(array('module_type' => 'hotel', 'from' => get_application_currency_preference(), 'to' => get_application_currency_preference()));
                        $attr['search_id'] = abs($search_params['search_id']);
                        $raw_hotel_search_result = array();
                        $i = 0;
                        $counter = 0;
                        if ($max_lat == 0) {
                            $max_lat = 0;
                        }

                        if ($max_lon == 0) {
                            $max_lon = 0;
                        }
                        if ($raw_hotel_list['data']['HotelSearchResult']) {
                            foreach ($raw_hotel_list['data']['HotelSearchResult']['HotelResults'] as $value) {
                                $raw_hotel_search_result[$i] = $value;
                                $raw_hotel_search_result[$i]['MResultToken'] = urlencode($value['ResultToken']);
                                $lat = $value['Latitude'];
                                $lon = $value['Longitude'];
                                if (($lat != '') && ($counter < 1)) {
                                    $max_lat = $lat;
                                }
                                if (($lon != '')) {
                                    $counter++;
                                    $max_lon = $lon;
                                }

                                $i++;
                            }
                            $raw_hotel_list['data']['HotelSearchResult']['max_lat'] = $max_lat;
                            $raw_hotel_list['data']['HotelSearchResult']['max_lon'] = $max_lon;
                        }
                        $raw_hotel_list['data']['HotelSearchResult']['HotelResults'] = $raw_hotel_search_result;
                        //debug($raw_hotel_list);exit;
                        $response['data'] = $raw_hotel_list['data'];
                        $response['status'] = SUCCESS_STATUS;
                    }
                    break;
            }
        }
        $this->output_compressed_data($response);
    }

    /**
     * Get Cancellation Policy based on Cancellation policy code
     *
     */

    function get_cancellation_policy(array $get_params): string
    {
        $application_currency = get_application_currency_preference();
        $currency_obj = new Currency([
            'module_type' => 'hotel',
            'from' => get_api_data_currency(),
            'to' => $application_currency
        ]);

        $room_price = $get_params['room_price'] ?? 0;
        $cancel_string = 'This rate is non-refundable. If you cancel this booking you will not be refunded any of the payment.';

        // Return early if 'booking_source' is empty or 'today_cancel_date' is not empty
        if (empty($get_params['booking_source']) || !empty($get_params['today_cancel_date'])) {
            return $cancel_string;
        }

        load_hotel_lib($get_params['booking_source']);
       
        $cancellation_details = [];
        if (!empty($get_params['policy_code'])) {
            $safe_search_data = $this->hotel_model->get_safe_search_data($get_params['tb_search_id']);
            $get_params['no_of_nights'] = $safe_search_data['data']['no_of_nights'];
            $get_params['room_count'] = $safe_search_data['data']['room_count'];
            $get_params['check_in'] = $safe_search_data['data']['from_date'];

            $cancellation_details = $this->hotel_lib->get_cancellation_details($get_params);
            
            $cancellation_policies = $cancellation_details['GetCancellationPolicy']['policy'][0]['policy'] ?? [];
        }
        
        // If there are no policies, try decoding the policy details
        if (empty($cancellation_policies)) {
            $decoded = base64_decode($get_params['policy_details']);
            $cancellation_policies = json_decode($decoded, true);
        }

        // If still no cancellation policies, return the default string
        if (empty($cancellation_policies)) {
            return $cancel_string;
        }
        debug($cancellation_policies);exit;
        // Normalize and reverse if necessary
        $cancellation_policies = $this->hotel_lib->php_arrayUnique($cancellation_policies, 'Charge');
        $cancel_reverse = $this->hotel_lib->php_arrayUnique(array_reverse($cancellation_policies), 'Charge');

        $cancel_string = '';
        $current_date = date('Y-m-d');
debug($cancellation_policies);exit;
        foreach ($cancellation_policies as $key => $policy) {
            $charge = floatval($policy['Charge']);
            $charge_type = $policy['ChargeType'] ?? 1;
            $from_date = $policy['FromDate'] ?? '';
            $to_date = $policy['ToDate'] ?? '';
            $from_date_formatted = date('d M Y', strtotime($from_date));
            $to_date_formatted = date('d M Y', strtotime($to_date));
            $cancell_date = date('Y-m-d', strtotime($from_date));

            $amount = '';
            if ($charge_type == 1) {
                $amount = $currency_obj->get_currency_symbol($currency_obj->to_currency) . ' ' . round($charge);
            } elseif ($charge_type == 2) {
                $amount = $currency_obj->get_currency_symbol($currency_obj->to_currency) . ' ' . $room_price;
            }

            $policy_string = '';

            if ($charge == 0) {
                $policy_string = "No cancellation charges, if cancelled before $to_date_formatted";
            }

            if (isset($cancel_reverse[$key + 1]) && $cancell_date > $current_date) {
                $policy_string = "Cancellations made after $from_date_formatted to $to_date_formatted, would be charged $amount";
            }

            if (empty($policy_string)) {
                $effective_from = ($cancell_date > $current_date) ? $from_date_formatted : date('d M Y');
                $policy_string = "Cancellations made after $effective_from, or no-show, would be charged $amount";
            }

            $cancel_string .= $policy_string . '<br/>';
        }

        return $cancel_string;
    }
     
    /**
     * Load hotels from different source
     */
    public function hotel_list(int $offset = 0): void
    {
        $response = [
            'data' => [],
            'msg' => [],
            'status' => FAILURE_STATUS
        ];

        $search_params = $this->input->get();
        $limit = $this->config->item('hotel_per_page_limit');

        if (
            isset($search_params['op'], $search_params['search_id'], $search_params['booking_source']) &&
            $search_params['op'] == 'load' &&
            intval($search_params['search_id']) > 0
        ) {
            load_hotel_lib($search_params['booking_source']);
            $booking_source = $search_params['booking_source'];

            switch ($booking_source) {
                case PROVAB_HOTEL_BOOKING_SOURCE:
                    $safe_search_data = $this->hotel_model->get_safe_search_data($search_params['search_id']);
                    $raw_hotel_list = $this->hotel_lib->get_hotel_list(abs($search_params['search_id']), $search_params);

                    if ($raw_hotel_list['status']) {
                        $hotel_reviews = $this->hotel_model->get_hotel_reviews($safe_search_data['data']['hotel_destination']);
                        $review_feedback = [];

                        if (valid_array($hotel_reviews)) {
                            foreach ($hotel_reviews as $review) {
                                $review_feedback[$review['hotel_code']] = $review['count'];
                            }
                        }

                        $currency_obj = new Currency([
                            'module_type' => 'hotel',
                            'from' => get_api_data_currency(),
                            'to' => get_application_currency_preference()
                        ]);

                        $raw_hotel_list = $this->hotel_lib->search_data_in_preferred_currency($raw_hotel_list, $currency_obj, $search_params['search_id']);
                        $currency_obj = new Currency([
                            'module_type' => 'hotel',
                            'from' => get_application_currency_preference(),
                            'to' => get_application_currency_preference()
                        ]);

                        $filters = valid_array($search_params['filters'] ?? []) ? $search_params['filters'] : [];

                        $raw_hotel_list['data'] = $this->hotel_lib->format_search_response(
                            $raw_hotel_list['data'],
                            $currency_obj,
                            $search_params['search_id'],
                            'b2c',
                            $filters
                        );
        
                        $source_result_count = $raw_hotel_list['data']['source_result_count'];
                        $filter_result_count = $raw_hotel_list['data']['filter_result_count'];

                        if ($offset == 0) {
                            $filters = $this->hotel_lib->filter_summary($raw_hotel_list['data']);
                            $response['filters'] = $filters['data'];
                        }

                        $raw_hotel_list['data'] = $this->hotel_lib->get_page_data($raw_hotel_list['data'], $offset, $limit);
                        $attr = ['search_id' => abs($search_params['search_id'])];
                        //debug($raw_hotel_list['data']); die;
                        $response['data'] = get_compressed_output(
                            $this->template->isolated_view('hotel/tbo/tbo_search_result', array('currency_obj' => $currency_obj, 'raw_hotel_list' => $raw_hotel_list['data'],
                                'search_id' => $search_params['search_id'], 'booking_source' => $search_params['booking_source'],
                                'attr' => $attr,
                                'search_params' => $safe_search_data,
                                'hotel_reviews' => $review_feedback
                                                                                                        
                        )));

                        $response['page_reload'] = $raw_hotel_list['page_reload'];
                        $response['total_result_count'] = $source_result_count;
                        $response['filter_result_count'] = $filter_result_count;
                        $response['offset'] = $offset + $limit;
                        $response['status'] = SUCCESS_STATUS;
                    }
                    break;

                case CRS_HOTEL_BOOKING_SOURCE:
                    $safe_search_data = $this->hotel_model->get_safe_search_data($search_params['search_id']);
                    $raw_hotel_list = $this->hotel_lib->get_hotel_list(abs($search_params['search_id']), $search_params);

                    $currency_obj = new Currency([
                        'module_type' => 'hotel',
                        'from' => get_api_data_currency(),
                        'to' => get_application_currency_preference()
                    ]);

                    $raw_hotel_list = $this->hotel_lib->search_data_in_preferred_currency($raw_hotel_list, $currency_obj, $search_params['search_id']);

                    foreach ($raw_hotel_list['data']['HotelSearchResult']['HotelResults'] as &$hotel) {
                        $hotel['booking_source'] = CRS_HOTEL_BOOKING_SOURCE;
                    }

                    unset($hotel); // break reference

                    $currency_obj = new Currency([
                        'module_type' => 'hotel',
                        'from' => get_application_currency_preference(),
                        'to' => get_application_currency_preference()
                    ]);

                    $filters = valid_array($search_params['filters'] ?? []) ? $search_params['filters'] : ['sort_item' => 'price', 'sort_type' => 'asc'];
                    $gst = 0;
                    $gst_details = $this->hotel_model->get_gst_details();

                    if ($gst_details['status'] && $gst_details['data'][0]['gst'] > 0) {
                        $gst = $gst_details['data'][0]['gst'];
                    }

                    $raw_hotel_list['data'] = $this->hotel_lib->format_search_response(
                        $raw_hotel_list['data'],
                        $currency_obj,
                        $search_params['search_id'],
                        'b2c',
                        $filters,
                        $gst
                    );

                    $source_result_count = $raw_hotel_list['data']['source_result_count'];
                    $filter_result_count = $raw_hotel_list['data']['filter_result_count'];

                    if ($offset == 0) {
                        $filters = $this->hotel_lib->filter_summary($raw_hotel_list['data']);
                        $response['filters'] = $filters['data'];
                    }

                    $attr = ['search_id' => abs($search_params['search_id'])];

                    $HotelAmenitiesList = [];
                    $AllHotelAmenities = $this->db->get_where('eco_stays_amenities', ['status' => 1])->result_array();

                    if (!empty($AllHotelAmenities)) {
                        $AllHotelAmenitiesList = array_combine(
                            array_column($AllHotelAmenities, 'name'),
                            array_map(fn($img) => base_url() . $this->template->domain_eco_stays_images_upload_dir('eco_stays_amenities/' . $img), array_column($AllHotelAmenities, 'image'))
                        );

                        $HotelAmenitiesList = $AllHotelAmenitiesList;
                    }

                    $response['data'] = get_compressed_output($this->template->isolated_view(
                        'hotel/tbo/tbo_search_result',
                        compact('currency_obj', 'raw_hotel_list', 'search_params', 'attr', 'safe_search_data')
                    ));

                    $response['page_reload'] = $raw_hotel_list['page_reload'];
                    $response['total_result_count'] = $source_result_count;
                    $response['filter_result_count'] = $filter_result_count;
                    $response['offset'] = $offset + $limit;

                    $response['status'] = $raw_hotel_list['status'] && !$raw_hotel_list['data']['refresh_flag'] ? SUCCESS_STATUS : FAILURE_STATUS;
                    $response['request_count'] = $raw_hotel_list['data']['refresh_flag'];
                    $response['amenities'] = $HotelAmenitiesList;
                    break;
            }
        }

        $this->output_compressed_data($response);
    }    
    /**
     * Load hotels from different source
     */
    public function hotel_image_list(): void
    {

        if (PROVAB_HOTEL_BOOKING_SOURCE) {
            load_hotel_lib(PROVAB_HOTEL_BOOKING_SOURCE);

            switch (PROVAB_HOTEL_BOOKING_SOURCE) {
                case PROVAB_HOTEL_BOOKING_SOURCE:
                    $api_city_master = $this->custom_db->single_table_records(
                        'all_api_city_master',
                        '*',
                        ['status' => 0, 'priority' => 1],
                        0,
                        1
                    );

                    if ($api_city_master['status'] == 1) {
                        $api_city = $api_city_master['data'][0];

                        $city_id         = $api_city['grn_city_id'];
                        $country_code    = $api_city['country_code'];
                        $destination_code = $api_city['grn_destination_id'];

                        $raw_hotel_list = $this->hotel_lib->get_hotel_image_list($city_id, $country_code);
                        $update_condition = [];

                        if ($raw_hotel_list['status']) {
                            foreach ($raw_hotel_list['data'] as $hotel) {
                                $main_image_path = $hotel['images']['main_image'] ?? null;

                                if ($main_image_path) {
                                    $full_image_url = 'https://cdn.grnconnect.com/' . $main_image_path;
                                    $image_data     = file_get_contents($full_image_url);
                                    $image_filename = preg_replace("/[^a-zA-Z0-9]/", "", $hotel['hotel_code']) . time() . basename($main_image_path);
                                    $save_path      = './cdn/images/' . $image_filename;

                                    file_put_contents($save_path, $image_data);

                                    $insert_data = [
                                        'hotel_code'      => $hotel['hotel_code'],
                                        'city_code'       => $hotel['city_code'],
                                        'destination_code' => $destination_code,
                                        'country_code'    => $hotel['country'],
                                        'path_name'       => $image_filename
                                    ];

                                    $existing_image = $this->custom_db->single_table_records(
                                        'api_grn_image_main_image_path',
                                        '*',
                                        [
                                            'hotel_code'   => $insert_data['hotel_code'],
                                            'city_code'    => $insert_data['city_code'],
                                            'country_code' => $insert_data['country_code']
                                        ]
                                    );

                                    if ($existing_image['status'] == 1) {
                                        $this->custom_db->update_record(
                                            'api_grn_image_main_image_path',
                                            ['path_name' => $image_filename],
                                            [
                                                'hotel_code' => $insert_data['hotel_code'],
                                                'city_code'  => $insert_data['city_code']
                                            ]
                                        );
                                    }

                                    // Insert if the image does not exist
                                    if ($existing_image['status'] != 1) {
                                        $this->custom_db->insert_record('api_grn_image_main_image_path', $insert_data);
                                    }
                                }
                            }

                            $update_condition['status'] = 1;
                        }

                        // Set the status and error information if hotel list is not valid
                        $update_condition['status'] = 1;
                        $update_condition['error']  = json_encode($raw_hotel_list);

                        // Update processing status
                        $this->custom_db->update_record(
                            'all_api_city_master',
                            $update_condition,
                            [
                                'grn_city_id'       => $city_id,
                                'country_code'      => $country_code,
                                'grn_destination_id' => $destination_code
                            ]
                        );
                    }

                    echo 'success';
                    break;
            }
        }
    }
    
        /**
     * Compress and output data
     * @param array $data
     */
    private function output_compressed_data(array $data): void
    {
        // Clean any existing output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Start output buffering with gzip compression
        ob_start('ob_gzhandler');

        // Set the correct content type
        header('Content-Type: application/json');

        // Output the JSON-encoded response
        echo json_encode($data);

        // Flush and turn off output buffering
        ob_end_flush();

        // Terminate execution
        exit;
    }
    /**
     * Compress and output data
     * @param array $data
     */
    private function output_compressed_data_flight(array $data): void
    {
        // Clean all existing output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Enable gzip compression
        ob_start('ob_gzhandler');

        // Remove memory and execution time limits
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        // Set content type header
        header('Content-Type: application/json');

        // Output JSON-encoded data without escaped slashes
        echo json_encode($data, JSON_UNESCAPED_SLASHES);

        // Flush and close output buffer
        ob_end_flush();

        // Terminate script
        exit;
    }


    /**
     * Load hotels from different source
     */


    /**
     * Get Bus Booking List
     */

    /**
     * Load hotels from different source
     */
    public function get_room_facilities(): void
    {
        $response = [
            'data' => [],
            'msg' => [],
            'status' => FAILURE_STATUS
        ];

        $params = $this->input->get();
        load_hotel_lib($params['booking_source']);

        switch ($params['booking_source']) {
            case PROVAB_HOTEL_BOOKING_SOURCE:
                $raw_room_list = $this->hotel_lib->get_room_facilities($params);
                $cancellation_string = $this->get_cancellation_policy($params);

                $room_inclusions = json_decode(base64_decode($params['room_amenties'] ?? ''), true) ?? [];
                $room_other_inclusions = json_decode(base64_decode($params['otherroomamenties'] ?? ''), true) ?? [];

                if (!valid_array($room_inclusions)) {
                    $room_inclusions = [];
                }
                if (!valid_array($room_other_inclusions)) {
                    $room_other_inclusions = [];
                }

                $all_inclusions = array_merge($room_inclusions, $room_other_inclusions);

                $raw_room_list['data']['room_name'] = $params['room_name'] ?? '';
                $raw_room_list['data']['inclusions'] = $all_inclusions;
                $raw_room_list['data']['cancellation_policy'] = $cancellation_string;

                $response['data'] = get_compressed_output(
                    $this->template->isolated_view('hotel/tbo/tbo_room_facilities', $raw_room_list['data'])
                );
                break;

            case CRS_HOTEL_BOOKING_SOURCE:
                $room_facilities = [];

                $room_faci = $this->custom_db->single_table_records(
                    'eco_stays_rooms',
                    '*',
                    ['stays_origin' => $params['hotel_code'] ?? '']
                );

                $testing = json_decode($room_faci['data'][0]['amenities'] ?? '[]', true);
                if (valid_array($testing)) {
                    foreach ($testing as $amenity_id) {
                        $amenity = $this->custom_db->single_table_records(
                            'eco_stays_room_amenities',
                            '*',
                            ['origin' => $amenity_id]
                        );
                        if (!empty($amenity['data'][0]['name'])) {
                            $room_facilities[] = $amenity['data'][0]['name'];
                        }
                    }
                }

                $cancellation_string = $this->get_cancellation_policy($params);

                $raw_room_list['data']['room_name'] = $params['room_name'] ?? '';
                $raw_room_list['data']['cancellation_policy'] = $cancellation_string;
                $raw_room_list['data']['room_facilities'] = $room_facilities;

                $response['data'] = get_compressed_output(
                    $this->template->isolated_view('hotel/tbo/tbo_room_facilities', $raw_room_list['data'])
                );
                break;
        }

        $this->output_compressed_data($response);
    }

    public function get_room_details(): void
    {
        $response = [
            'data' => [],
            'msg' => [],
            'status' => FAILURE_STATUS
        ];

        $params = $this->input->post();

        ini_set('memory_limit', '250M');

        if (
            ($params['op'] ?? '') == 'get_room_details' &&
            isset($params['search_id'], $params['booking_source']) &&
            intval($params['search_id']) > 0
        ) {
            $application_preferred_currency = get_application_currency_preference();
            $application_default_currency = get_application_currency_preference();

            load_hotel_lib($params['booking_source']);
            $this->hotel_lib->search_data((int)$params['search_id']);

            $attr = [
                'search_id' => (int)$params['search_id']
            ];

            $resultIndex = urldecode($params['ResultIndex'] ?? '');

            switch ($params['booking_source']) {
                case PROVAB_HOTEL_BOOKING_SOURCE:
                    $raw_room_list = $this->hotel_lib->get_room_list($resultIndex);
                    $safe_search_data = $this->hotel_model->get_safe_search_data((int)$params['search_id']);

                    if (!empty($raw_room_list['status'])) {
                        $currency_obj = new Currency([
                            'module_type' => 'hotel',
                            'from' => get_api_data_currency(),
                            'to' => $application_preferred_currency
                        ]);

                        $raw_room_list = $this->hotel_lib->roomlist_in_preferred_currency($raw_room_list, $currency_obj, $params['search_id']);

                        $currency_obj = new Currency([
                            'module_type' => 'hotel',
                            'from' => $application_default_currency,
                            'to' => $application_preferred_currency
                        ]);

                        $response['data'] = get_compressed_output(
                            $this->template->isolated_view('hotel/tbo/tbo_room_list', [
                                'currency_obj' => $currency_obj,
                                'params' => $params,
                                'raw_room_list' => $raw_room_list['data'],
                                'hotel_search_params' => $safe_search_data['data'],
                                'application_preferred_currency' => $application_preferred_currency,
                                'application_default_currency' => $application_default_currency,
                                'attr' => $attr
                            ])
                        );
                        $response['status'] = SUCCESS_STATUS;
                    }
                    break;

                case CRS_HOTEL_BOOKING_SOURCE:
                    $this->hotel_lib->search_data((int)$params['search_id']);

                    $raw_room_list = $this->hotel_lib->get_room_list($resultIndex);
                    $safe_search_data = $this->hotel_model->get_safe_search_data((int)$params['search_id']);

                    if (!empty($raw_room_list['status'])) {
                        $currency_obj = new Currency([
                            'module_type' => 'hotel',
                            'from' => get_api_data_currency(),
                            'to' => $application_preferred_currency
                        ]);

                        $gst = $params['gst'] ?? null; // Assuming GST may be passed optionally
                        $raw_room_list = $this->hotel_lib->roomlist_in_preferred_currency(
                            $raw_room_list,
                            $currency_obj,
                            $params['search_id'],
                            'b2c',
                            $gst,
                            $params
                        );

                        $currency_obj = new Currency([
                            'module_type' => 'hotel',
                            'from' => $application_default_currency,
                            'to' => $application_preferred_currency
                        ]);

                        $response['data'] = get_compressed_output(
                            $this->template->isolated_view('hotel/tbo/tbo_room_list', [
                                'currency_obj' => $currency_obj,
                                'params' => $params,
                                'raw_room_list' => $raw_room_list['data'],
                                'hotel_search_params' => $safe_search_data['data'],
                                'application_preferred_currency' => $application_preferred_currency,
                                'application_default_currency' => $application_default_currency,
                                'attr' => $attr
                            ])
                        );
                        $response['status'] = SUCCESS_STATUS;
                    }
                    break;
            }
        }

        $this->output_compressed_data($response);
    }
    /**
     * Get Hotel Images by HotelCode
     */
    /**
 * Get Hotel Images by HotelCode
 */
    public function get_hotel_images(): void
    {
        $post_params = $this->input->post();
        $response = [
            'data' => [],
            'msg' => [],
            'status' => FAILURE_STATUS
        ];

        $hotelCode = $post_params['hotel_code'] ?? null;
        $bookingSource = $post_params['booking_source'] ?? null;
        $searchId = $post_params['search_id'] ?? null;
        $hotelName = $post_params['Hotel_name'] ?? '';

        if (empty($hotelCode) || empty($bookingSource)) {
            $response['msg'] = ['Missing required parameters: hotel_code or booking_source.'];
            $this->output_compressed_data($response);
            return;
        }

        if ($bookingSource != PROVAB_HOTEL_BOOKING_SOURCE) {
            $response['msg'] = ['Unsupported booking source.'];
            $this->output_compressed_data($response);
            return;
        }

        load_hotel_lib($bookingSource);
        $raw_hotel_images = $this->hotel_lib->get_hotel_images($hotelCode);

        if (empty($raw_hotel_images['status']) || empty($raw_hotel_images['data'])) {
            $response['msg'] = ['No hotel images found.'];
            $this->output_compressed_data($response);
            return;
        }

        $this->hotel_model->add_hotel_images($searchId, $raw_hotel_images['data'], $hotelCode);

        $response['data'] = get_compressed_output(
            $this->template->isolated_view('hotel/tbo/tbo_hotel_images', [
                'hotel_images' => $raw_hotel_images,
                'HotelCode' => $hotelCode,
                'HotelName' => $hotelName
            ])
        );
        $response['status'] = SUCCESS_STATUS;

        $this->output_compressed_data($response);
    } 
    /**
     * Load Flight from different source
     */
    public function flight_list(string $search_id = ''): void
    {
        $response = [
            'data' => [],
            'msg' => [],
            'status' => FAILURE_STATUS
        ];
        $search_params = $this->input->get();
        if (
            !isset($search_params['op'], $search_params['search_id'], $search_params['booking_source']) ||
            $search_params['op'] != 'load' ||
            intval($search_params['search_id']) <= 0
        ) {
            $response['msg'] = ['Invalid or missing search parameters.'];
            $this->send_flight_list_response($response);
            return;
        }
        $booking_source = $search_params['booking_source'];
        $search_id = abs(intval($search_params['search_id']));
        $attr = ['search_id' => $search_id];

        load_flight_lib($booking_source);
        if ($booking_source == PROVAB_FLIGHT_BOOKING_SOURCE) {
            $raw_flight_list = $this->flight_lib->get_flight_list($search_id);

            if (empty($raw_flight_list['status'])) {
                $this->send_flight_list_response($response);
                return;
            }

            $raw_search_result = $raw_flight_list['data']['Search']['FlightDataList'] ?? [];

            $currency_obj = new Currency([
                'module_type' => 'flight',
                'from' => get_api_data_currency(),
                'to' => get_application_currency_preference()
            ]);

            $raw_search_result = $this->flight_lib->search_data_in_preferred_currency($raw_search_result, $currency_obj);
            $currency_obj = new Currency([
                'module_type' => 'flight',
                'from' => get_application_currency_preference(),
                'to' => get_application_currency_preference()
            ]);

            $formatted_search_data = $this->flight_lib->format_search_response(
                $raw_search_result,
                $currency_obj,
                $search_id,
                $this->current_module,
                $raw_flight_list['from_cache'] ?? false,
                $raw_flight_list['search_hash'] ?? ''
            );

            $raw_flight_list['data'] = $formatted_search_data['data'];
            $route_count = count($raw_flight_list['data']['Flights'] ?? []);
            $domestic_round_way_flight = $raw_flight_list['data']['JourneySummary']['IsDomesticRoundway'] ?? false;

            if ($route_count <= 0) {
                $this->send_flight_list_response($response);
                return;
            }

            $page_params = ['raw_flight_list' => $raw_flight_list['data'],'search_id' => $search_id,'booking_url' => $formatted_search_data['booking_url'] ?? '','booking_source' => $booking_source,'cabin_class' => $raw_flight_list['cabin_class'] ?? '','trip_type' => $this->flight_lib->master_search_data['trip_type'] ?? '','attr' => $attr,'route_count' => $route_count,'IsDomestic' => $raw_flight_list['data']['JourneySummary']['IsDomestic'] ?? false,'domestic_round_way_flight' => $domestic_round_way_flight
            ];
            
            $page_view_data = $this->template->isolated_view('flight/tbo/tbo_col2x_search_result', $page_params);

            $response['data'] = get_compressed_output($page_view_data);
            $response['status'] = SUCCESS_STATUS;
            $response['session_expiry_details'] = $formatted_search_data['session_expiry_details'] ?? [];
            $this->send_flight_list_response($response);
            return;
        }
        if ($booking_source == PROVAB_FLIGHT_CRS_BOOKING_SOURCE) {
            $currency_obj = new Currency([
                'module_type' => 'flight',
                'from' => get_api_data_currency(),
                'to' => get_application_currency_preference()
            ]);
            $formatted_search_data = $this->flight_lib->get_flight_list($search_id, 'b2c', $currency_obj);
            debug($formatted_search_data);die;
            $raw_flight_list['data'] = $formatted_search_data['data'] ?? [];
            $route_count = count($raw_flight_list['data']['FlightResults'] ?? []);

            // CRS-specific rendering could be handled here if needed
            
            $this->send_flight_list_response($response);
            return;        }

        $response['msg'] = ['Unsupported booking source.'];
        $this->send_flight_list_response($response);
    }

    private function send_flight_list_response(array $response): void
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $this->output_compressed_data_flight($response);
    }
    /**
     * Load hotels from different source
     */
    public function car_list(int $offset = 0): void
    {
        $response = [
            'data' => [],
            'msg' => [],
            'status' => FAILURE_STATUS
        ];

        $search_params = $this->input->get();
        $limit = $this->config->item('car_per_page_limit');

        if (
            !isset($search_params['op'], $search_params['search_id'], $search_params['booking_source']) ||
            $search_params['op'] != 'load' ||
            intval($search_params['search_id']) <= 0
        ) {
            $response['msg'] = ['Invalid or missing search parameters.'];
            $this->output_compressed_data($response);
            return;
        }

        $booking_source = $search_params['booking_source'];
        $search_id = abs(intval($search_params['search_id']));

        if ($booking_source != PROVAB_CAR_BOOKING_SOURCE) {
            $response['msg'] = ['Unsupported booking source.'];
            $this->output_compressed_data($response);
            return;
        }

        load_car_lib($booking_source);

        $safe_search_data = $this->car_model->get_safe_search_data($search_id);
        $raw_car_list = $this->car_lib->get_car_list($search_id);
        
        if (empty($raw_car_list['status'])) {
            $this->output_compressed_data($response);
            return;
        }

        $currency_obj = new Currency([
            'module_type' => 'car',
            'from' => get_api_data_currency(),
            'to' => get_application_currency_preference()
        ]);

        $raw_car_list = $this->car_lib->search_data_in_preferred_currency($raw_car_list, $currency_obj, $search_id);

        $currency_obj = new Currency([
            'module_type' => 'car',
            'from' => get_application_currency_preference(),
            'to' => get_application_currency_preference()
        ]);

        $filters = (isset($search_params['filters']) && valid_array($search_params['filters'])) ? $search_params['filters'] : [];

        $raw_car_list['data'] = $this->car_lib->format_search_response(
            $raw_car_list['data'],
            $currency_obj,
            $search_id,
            'b2c',
            $filters
        );

        $source_result_count = $raw_car_list['data']['source_result_count'] ?? 0;
        $filter_result_count = $raw_car_list['data']['filter_result_count'] ?? 0;

        if ($offset == 0) {
            $filters_summary = $this->car_lib->filter_summary($raw_car_list['data']);
            $response['filters'] = $filters_summary['data'] ?? [];
        }

        $raw_car_list['data'] = $this->car_lib->get_page_data($raw_car_list['data'], $offset, $limit);
        $attr = ['search_id' => $search_id];

        $response['data'] = get_compressed_output(
            $this->template->isolated_view('car/car_search_result_page', [
                'currency_obj' => $currency_obj,
                'raw_car_list' => $raw_car_list['data'],
                'search_id' => $search_id,
                'booking_source' => $booking_source,
                'attr' => $attr,
                'search_params' => $safe_search_data
            ])
        );

        $response['status'] = SUCCESS_STATUS;
        $response['total_result_count'] = $source_result_count;
        $response['filter_result_count'] = $filter_result_count;
        $response['offset'] = $offset + $limit;

        $this->output_compressed_data($response);
    }


    /**
     * Get Data For Fare Calendar
     * @param string $booking_source
     */
    public function puls_minus_days_fare_list(string $booking_source): void
    {
        $response = [
            'data' => [],
            'status' => FAILURE_STATUS
        ];

        $params = $this->input->get();

        if (empty($params['search_id']) || !is_numeric($params['search_id'])) {
            $response['msg'] = 'Invalid search ID.';
            $this->output_compressed_data($response);
            return;
        }

        load_flight_lib($booking_source);
        $search_data = $this->flight_lib->search_data((int) $params['search_id']);

        if ($search_data['status'] != SUCCESS_STATUS) {
            $this->output_compressed_data($response);
            return;
        }

        $date_array = [];
        $departure_date = strtotime($search_data['data']['depature']);
        $departure_date = strtotime(subtract_days_from_date(3, date('Y-m-d', $departure_date)));

        if (time() >= $departure_date) {
            $date_array[] = date('Y-m-d', strtotime(add_days_to_date(1)));
        }

        $date_array[] = date('Y-m', $departure_date) . '-01';
        $search = $this->flight_lib->calendar_safe_search_data($search_data['data']);
        if (!valid_array($search)) {
            $this->output_compressed_data($response);
            return;
        }

        if ($booking_source != PROVAB_FLIGHT_BOOKING_SOURCE) {
            $this->output_compressed_data($response);
            return;
        }

        $raw_fare_list = $this->flight_lib->get_fare_list($search);

        if (empty($raw_fare_list['status'])) {
            $this->output_compressed_data($response);
            return;
        }

        $fare_calendar_list = $this->flight_lib->format_cheap_fare_list($raw_fare_list['data']);

        if ($fare_calendar_list['status'] != SUCCESS_STATUS) {
            $response['msg'] = 'Not Available!!! Please Try Later!!!!';
            $this->output_compressed_data($response);
            return;
        }

        $response['data']['departure'] = $search['depature'];
        $calendar_events = $this->get_fare_calendar_events(
            $fare_calendar_list['data'],
            $raw_fare_list['data']['TraceId'],
            $search['depature']
        );

        $response['data']['day_fare_list'] = $calendar_events;
        $response['status'] = SUCCESS_STATUS;

        $this->output_compressed_data($response);
    }

    

    public function get_airport_code_listnew(): void
    {
        $term = trim(strip_tags($this->input->get('term')));
        $input_src = $this->input->get('input_src');

        // Fetch the airport list based on search term
        $__airports = $this->flight_model->get_airport_listnew($term)->result();
        $main_array = json_decode(json_encode($__airports), true);

        $main_priority = '';
        $final_array = [];
        $included_check_array = [];

        foreach ($main_array as $airport_data) {
            $air_code = $airport_data['airport_code'];
            $exist_subarray = $this->check_if_sub_array_exists($main_array, $airport_data['priority']);

            // Check if the airport code is already included in the final array
            if (!in_array($air_code, array_column($included_check_array, 'airport_code'))) {
                $final_array[$air_code] = $airport_data;

                if (count($exist_subarray) > 1) {
                    $final_array[$air_code]['sub_array'] = $exist_subarray;
                    $included_check_array = array_merge($included_check_array, $exist_subarray);
                }
            }
        }

        // Generate the HTML output for the airport list
        $classm = "airportc" . $input_src;

        foreach ($final_array as $val) {
            $airport_name = $val["airport_name"];
            $airport_city = $val["airport_city"];
            $airport_code = $val["airport_code"];
            $country = $val["country"];

            $atextm = $airport_city . ' (' . $airport_code . ')';
            $main_priority .= '<li>
                <a class="' . $classm . '" data-airportname="' . $airport_name . '" data-airportcode="' . $val['origin'] . '" data-aiporttext="' . $atextm . '">
                    <span>
                        <img class="flag_img" src="https://bestfares365.com/extras/system/template_list/template_v3/images/flag_img_drpdwn.png">
                    </span>
                    <span class="left_up_txt flg_txt_ellip">' . $airport_city . '</span>
                    <span class="rgt_txt_flg">' . $airport_code . '</span><br>
                    <p class="left_dwn_txt">' . $country . ', ' . $airport_name . '</p> 
                </a>';

            if (isset($val['sub_array'])) {
                foreach ($val['sub_array'] as $sub_val) {
                    $sub_airport_name = $sub_val['airport_name'];
                    $sub_airport_code = $sub_val['airport_code'];
                    $sub_airport_city = $sub_val['airport_city'];
                    $sub_country = $sub_val['country'];

                    $sub_atext = $sub_airport_city . ' (' . $sub_airport_code . ')';
                    $main_priority .= '<ul class="top_brdr ' . $classm . '" data-airportcode="' . $sub_val['origin'] . '" data-aiporttext="' . $sub_atext . '">
                        <li class="left_up_txt">
                            <a>
                                <span class="pad_lft8 pln_txt_ellip">' . $sub_airport_name . '</span>
                                <span class="rgt_txt">' . $sub_airport_code . '</span><br>
                                <p class="pad_lft30">' . $sub_country . '</p>
                            </a>
                        </li>
                    </ul>';
                }
            }
            $main_priority .= '</li>';
        }

        // Return the final result in the required format
        $result = ["results" => $main_priority];
        $this->output_compressed_data($result);
    }
    
    /**
     * get fare list for calendar search - FLIGHT
     */
    function fare_list(string $booking_source): void
    {
        $response = [
            'data' => [],
            'msg' => [],
            'status' => FAILURE_STATUS
        ];

        $search_params = $this->input->get();
        load_flight_lib($booking_source);
        $search_params = $this->flight_lib->calendar_safe_search_data($search_params);

        if (empty($search_params)) {
            $this->output_compressed_data($response);
            return;
        }
        if ($booking_source != PROVAB_FLIGHT_BOOKING_SOURCE) {
            $this->output_compressed_data($response);
            return;
        }
        $raw_fare_list = $this->flight_lib->get_fare_list($search_params);

        if (empty($raw_fare_list['status'])) {
            $this->output_compressed_data($response);
            return;
        }
        $fare_calendar_list = $this->flight_lib->format_cheap_fare_list($raw_fare_list['data']);

        if ($fare_calendar_list['status'] != SUCCESS_STATUS) {
            $response['msg'] = 'Not Available! Please try later.';
            $this->output_compressed_data($response);
            return;
        }
        $response['data']['departure'] = $search_params['depature'];
        $calendar_events = $this->get_fare_calendar_events($fare_calendar_list['data'], $raw_fare_list['data']['TraceId']);
        $response['data']['day_fare_list'] = $calendar_events;
        $response['status'] = SUCCESS_STATUS;
        $this->output_compressed_data($response);
    }
    /**
     * Calendar Event Object
     * @param $title
     * @param $start
     * @param $tip
     * @param $href
     * @param $event_date
     * @param $session_id
     * @param $add_class
     */
    private function get_calendar_event_obj(string $title = '',string $start = '',string $tip = '',string $add_class = '',string $href = '',string $event_date = '',string $session_id = '',string $data_id = '',string $class = ''
    ): array {
        $event_obj = [];

        // Set values in event_obj with ternary operator for concise checks
        $event_obj['data_id'] = !empty($data_id) ? $data_id : '';
        $event_obj['title'] = !empty($title) ? $title : '';

        $date = explode(" ", $start);
        $event_obj['start'] = !empty($date[0]) ? $date[0] : '';
        $event_obj['start_label'] = !empty($date[0]) ? date('D, d M', strtotime($date[0])) : '';

        $event_obj['tip'] = !empty($tip) ? $tip : '';
        $event_obj['href'] = !empty($href) ? $href : '';

        $event_obj['class'] = $class;

        $event_obj['event_date'] = !empty($event_date) ? $event_date : '';
        $event_obj['session_id'] = !empty($session_id) ? $session_id : '';
        $event_obj['add_class'] = !empty($add_class) ? $add_class : '';

        return $event_obj;
    }


    function day_fare_list(string $booking_source): void
    {
        $response = [
            'data' => [],
            'msg' => [],
            'status' => FAILURE_STATUS
        ];

        $search_params = $this->input->get();
        load_flight_lib($booking_source);

        $safe_search_params = $this->flight_lib->calendar_day_fare_safe_search_data($search_params);

        if ($safe_search_params['status'] != SUCCESS_STATUS) {
            $this->output_compressed_data($response);
            return;
        }

        if ($booking_source != PROVAB_FLIGHT_BOOKING_SOURCE) {
            $this->output_compressed_data($response);
            return;
        }

        $raw_day_fare_list = $this->flight_lib->get_day_fare($search_params);

        if (empty($raw_day_fare_list['status'])) {
            $this->output_compressed_data($response);
            return;
        }

        $fare_calendar_list = $this->flight_lib->format_day_fare_list($raw_day_fare_list['data']);

        if ($fare_calendar_list['status'] != SUCCESS_STATUS) {
            $response['msg'] = 'Not Available!!! Please Try Later!!!!';
            $this->output_compressed_data($response);
            return;
        }

        $calendar_events = $this->get_fare_calendar_events($fare_calendar_list['data'], '');
        $response['data']['day_fare_list'] = $calendar_events;
        $response['data']['departure'] = $search_params['depature'];
        $response['status'] = SUCCESS_STATUS;

        $this->output_compressed_data($response);
    }

    private function get_fare_calendar_events(array $events, string $session_id = '', string $departure_date = ''): array
    {
        $currency_obj = new Currency([
            'module_type' => 'flight',
            'from' => get_api_data_currency(),
            'to' => get_application_currency_preference()
        ]);

        $calendar_events = [];
        $index = 0;

        foreach ($events as $k => $day_fare) {
            // If $day_fare is valid, process it
            if (valid_array($day_fare)) {
                // Safeguard against undefined or null values in $day_fare
                $fare_object = ['BaseFare' => $day_fare['BaseFare'] ?? 0];
                $BaseFare = $this->flight_lib->update_markup_currency($fare_object, $currency_obj);
                $tax = $currency_obj->get_currency($day_fare['tax'] ?? 0, false);

                // Ensuring that tax and fare data are numeric and adding them
                $price = floor(($BaseFare['BaseFare'] ?? 0) + ($tax['default_value'] ?? 0));
                $class = (strtotime($departure_date) == strtotime($day_fare['date'])) ? 'active' : '';

                $event_obj = $this->get_calendar_event_obj(
                    sprintf('%s %s', $currency_obj->get_currency_symbol(get_application_currency_preference()), $price),
                    $k,
                    sprintf('%s-%s', $day_fare['airline_name'] ?? 'Unknown', $day_fare['airline_code'] ?? 'Unknown'),
                    'search-day-fare',
                    '',
                    $day_fare['departure'] ?? '',
                    '',
                    $day_fare['airline_code'] ?? '',
                    $class
                );
                $calendar_events[$index] = $event_obj;
            }
            // If $day_fare is not valid, create the "update" event
            if (!valid_array($day_fare)) {
                $event_obj = $this->get_calendar_event_obj(
                    'Update',
                    $k,
                    'Current Cheapest Fare Not Available. Click To Get Latest Fare.',
                    'update-day-fare',
                    '',
                    $k,
                    $session_id,
                    ''
                );
                $calendar_events[$index] = $event_obj;
            }
            $index++;
        }
        return $calendar_events;
    }

    /**
     * Get Fare Details
     */
    public function get_fare_details(): void
    {
        $response = [
            'status' => false,
            'data' => [],
            'msg' => '<i class="fa fa-warning text-danger"></i> Fare Details Not Available'
        ];

        $params = $this->input->post();

        $booking_source = $params['booking_source'] ?? '';
        $data_access_key_raw = $params['data_access_key'] ?? '';
        $search_access_key = $params['search_access_key'] ?? '';

        if ($booking_source == '' || $data_access_key_raw == '') {
            $this->output_compressed_data($response);
            return;
        }

        load_flight_lib($booking_source);

        $data_access_key = unserialized_data($data_access_key_raw);

        if (!empty($data_access_key)) {
            if ($booking_source == PROVAB_FLIGHT_BOOKING_SOURCE) {
                $token_data = $this->flight_lib->read_token($data_access_key_raw);
                $fare_data = $this->flight_lib->get_fare_details($token_data, $search_access_key);

                if ($fare_data['status'] == SUCCESS_STATUS) {
                    $response['status'] = SUCCESS_STATUS;
                    $response['data'] = $this->template->isolated_view('flight/tbo/fare_details', [
                        'fare_rules' => $fare_data['data']
                    ]);
                    $response['msg'] = 'Fare Details Available';
                }
            }
        }

        $this->output_compressed_data($response);
    }

    public function get_combined_booking_from(): void
    {
        $response = [
            'status' => FAILURE_STATUS,
            'data' => []
        ];

        $params = $this->input->post();
        $search_id = $params['search_id'] ?? null;
        $trip_way_1_raw = $params['trip_way_1'] ?? null;
        $trip_way_2_raw = $params['trip_way_2'] ?? null;

        if ($search_id && $trip_way_1_raw && $trip_way_2_raw) {
            $tmp_trip_way_1 = json_decode($trip_way_1_raw, true);
            $tmp_trip_way_2 = json_decode($trip_way_2_raw, true);

            $trip_way_1 = [];
            foreach ($tmp_trip_way_1 as $item) {
                $trip_way_1[$item['name']] = $item['value'];
            }

            $trip_way_2 = [];
            foreach ($tmp_trip_way_2 as $item) {
                $trip_way_2[$item['name']] = $item['value'];
            }

            $booking_source = $trip_way_1['booking_source'] ?? '';

            if ($booking_source == PROVAB_FLIGHT_BOOKING_SOURCE) {
                load_flight_lib($booking_source);
                $response['data']['booking_url'] = $this->flight_lib->booking_url((int) $search_id);
                $response['data']['form_content'] = $this->flight_lib->get_form_content($trip_way_1, $trip_way_2);
                $response['status'] = SUCCESS_STATUS;
            }
        }

        $this->output_compressed_data($response);
    }   

    /**
     * Balu A
     * Get Traveller Details in Booking Page
     */
    public function user_traveller_details(): void
    {
        $term = trim((string) $this->input->get('term')); // Ensure string type
        $result = [];
    
        $this->load->model('user_model');
        $traveller_details = $this->user_model->user_traveller_details($term)->result();
    
        foreach ($traveller_details as $traveller) {
            $travellers_data = ['category' => 'Travellers','id' => $traveller->origin,'label' => trim($traveller->first_name . ' ' . $traveller->last_name),'value' => trim($traveller->first_name),'first_name' => trim($traveller->first_name),'last_name' => trim($traveller->last_name),'date_of_birth' => date('Y-m-d', strtotime(trim((string) $traveller->date_of_birth))),'email' => trim($traveller->email),'passport_user_name' => trim($traveller->passport_user_name),'passport_nationality' => trim($traveller->passport_nationality),'passport_expiry_day' => trim($traveller->passport_expiry_day),'passport_expiry_month' => trim($traveller->passport_expiry_month),'passport_expiry_year' => trim($traveller->passport_expiry_year),'passport_number' => trim($traveller->passport_number),'passport_issuing_country' => trim($traveller->passport_issuing_country),
            ];
    
            $result[] = $travellers_data;
        }
    
        $this->output_compressed_data($result);  
    }  

    /**
     *
     */
    public function log_event_ip_info(int|string $eid): void
    {
        $params = $this->input->post();

        if (!empty($eid)) {
            $this->custom_db->update_record(
                'exception_logger',
                ['client_info' => serialize($params)],
                ['exception_id' => $eid]
            );
        }
    }
    /**
     * Load hotels from different source
     */
    public function hotel_image_list_desc(): void
    {
    
        if (!defined('PROVAB_HOTEL_BOOKING_SOURCE') || !PROVAB_HOTEL_BOOKING_SOURCE) {
            return;
        }
    
        load_hotel_lib(PROVAB_HOTEL_BOOKING_SOURCE);
    
        if (PROVAB_HOTEL_BOOKING_SOURCE != PROVAB_HOTEL_BOOKING_SOURCE) {
            return;
        }
    
        $api_city_master = $this->custom_db->single_table_records(
            'all_api_city_master',
            '*',
            ['status' => 0, 'priority' => 1],
            0,
            1,
            ['origin' => 'desc']
        );
    
        if (($api_city_master['status'] ?? 0) != 1 || empty($api_city_master['data'][0])) {
            return;
        }
    
        $city_data = $api_city_master['data'][0];
        $city_id = $city_data['grn_city_id'];
        $country_code = $city_data['country_code'];
        $destination_code = $city_data['grn_destination_id'];
    
        $raw_hotel_list = $this->hotel_lib->get_hotel_image_list($city_id, $country_code);
        $update_condition = [];
    
        if (!($raw_hotel_list['status'] ?? false)) {
            $update_condition = [
                'status' => 1,
                'error' => json_encode($raw_hotel_list)
            ];
            $this->custom_db->update_record(
                'all_api_city_master',
                $update_condition,
                [
                    'grn_city_id' => $city_id,
                    'country_code' => $country_code,
                    'grn_destination_id' => $destination_code
                ]
            );
            return;
        }
    
        foreach ($raw_hotel_list['data'] as $hotel) {
            $main_image = $hotel['images']['main_image'] ?? '';
            if (empty($main_image)) {
                continue;
            }
    
            $image_url = 'https://cdn.grnconnect.com/' . $main_image;
            $path_info = pathinfo($main_image);
            $basename = $path_info['basename'] ?? '';
    
            $hotel_code = preg_replace("/[^a-zA-Z0-9]/", '', $hotel['hotel_code']);
            $image_file_name = $hotel_code . time() . $basename;
            $image_path = './cdn/images/' . $image_file_name;
    
            $image_data = file_get_contents($image_url);
            file_put_contents($image_path, $image_data);
    
            $insert_data = [
                'hotel_code' => $hotel['hotel_code'],
                'city_code' => $hotel['city_code'],
                'destination_code' => $destination_code,
                'country_code' => $hotel['country'],
                'path_name' => $image_file_name
            ];
    
            $exists = $this->custom_db->single_table_records(
                'api_grn_image_main_image_path',
                '*',
                [
                    'hotel_code' => $insert_data['hotel_code'],
                    'city_code' => $insert_data['city_code'],
                    'country_code' => $insert_data['country_code']
                ]
            );
    
            if (($exists['status'] ?? 0) == 1) {
                $this->custom_db->update_record(
                    'api_grn_image_main_image_path',
                    ['path_name' => $image_file_name],
                    [
                        'hotel_code' => $insert_data['hotel_code'],
                        'city_code' => $insert_data['city_code']
                    ]
                );
                continue;
            }
    
            $this->custom_db->insert_record('api_grn_image_main_image_path', $insert_data);
        }
    
        $update_condition['status'] = 1;
    
        $this->custom_db->update_record(
            'all_api_city_master',
            $update_condition,
            [
                'grn_city_id' => $city_id,
                'country_code' => $country_code,
                'grn_destination_id' => $destination_code
            ]
        );
    
        echo "success";
    }


    function download_api_grn_exterior_image_part1() {
        error_reporting(0);
        $str = "select * from api_grn_static_images where status=0 and origin <=200000 limit 0,10";
        $execute_query = $this->db->query($str);
        $static_image = [];
        if ($execute_query->num_rows()) {
            $static_image = $execute_query->result_array();
        }
        $this->process_grn_images($static_image);
        echo "successs";
    }
    

    function download_api_grn_exterior_image_part2() {
        error_reporting(0);
        $str = "select * from api_grn_static_images where status=0 and origin >200000 limit 0,10";
        $execute_query = $this->db->query($str);
        $static_image = [];
        if ($execute_query->num_rows()) {
            $static_image = $execute_query->result_array();
        }
        $this->process_grn_images($static_image);
        echo "successs";
    }


    function process_grn_images($static_image)
    {
        if (!$static_image) {
            return;
        }
        $image_update_data = array();
        $update_condition = array();
        foreach ($static_image as $value) {
            $image = $value['image_url'];
            $path_info = pathinfo($image);
            $path_info_base_name = $path_info['basename'];

            // If image cannot be fetched
            if (!file_get_contents($image)) {
                $image_update_data['image_found'] = 'N';
                $image_update_data['status'] = 1;
                $update_condition['origin'] = $value['origin'];
                // Update in all_api_city_master
                $this->custom_db->update_record('api_grn_static_images', $image_update_data, $update_condition);
                continue; // Skip the rest of this iteration and move to the next one
            }

            // Process the image if it is fetched successfully
            $image = file_get_contents($image);
            $hotel_code = preg_replace("/[^a-zA-Z0-9]/", "", $value['hotel_code']);
            $image_file_name = $hotel_code . time() . $path_info_base_name;
            $image_folder_path = './cdn/grn/images/' . $image_file_name;
            file_put_contents($image_folder_path, $image); // Where to save the image on your server

            $insert_data = [];
            $update_data = [];
            $condition = [];
            $insert_data['path_name'] = $image_file_name;
            $insert_data['hotel_code'] = $value['hotel_code'];

            $check_if_exists = $this->custom_db->single_table_records('api_grn_master_image', '*', ['hotel_code' => $insert_data['hotel_code']]);

            if ($check_if_exists['status'] == 1) {
                $update_data['path_name'] = $image_file_name;
                $condition['hotel_code'] = $insert_data['hotel_code'];
                $this->custom_db->update_record('api_grn_master_image', $update_data, $condition);
                continue;
            }

            $this->custom_db->insert_record('api_grn_master_image', $insert_data);

            // Final update to the city master
            $image_update_data['status'] = 1;
            $update_condition['origin'] = $value['origin'];
            // Update in all_api_city_master
            $this->custom_db->update_record('api_grn_static_images', $image_update_data, $update_condition);
        }
    }

    

    // Wrappers for different parts:
    function download_api_grn_exterior_image_part3()
    {
        $this->process_grn_images('origin > 400000');
    }

    function download_api_grn_exterior_image_part4()
    {
        $this->process_grn_images('origin >= 600000');
    }

    function download_api_grn_exterior_image_part5()
    {
        $this->process_grn_images('origin >= 800000');
    }

    function download_api_grn_exterior_image_part6()
    {
        $this->process_grn_images('origin >= 950000');
    }


    function get_country_master(): void
    {
        $country_master = $this->custom_db->single_table_records('api_country_master', '*', array('status' => 0), 0, 30);

        if ($country_master['status'] == 1) {
            foreach ($country_master['data'] as $i_value) {
                $city_master_data = $this->process_request($i_value['iso_country_code']);

                if ($city_master_data && isset($city_master_data['cities'])) {
                    foreach ($city_master_data['cities'] as $c_value) {
                        $insert_data = [
                            'grn_city_id' => $c_value['code'],
                            'city_name' => explode(",", $c_value['name'])[0],
                            'full_city_name' => $c_value['name'],
                            'country_name' => $i_value['country_name'],
                            'country_code' => $i_value['iso_country_code']
                        ];

                        $check_if_data_exists = $this->custom_db->single_table_records('all_api_city_master_update', 'grn_city_id', array('grn_city_id' => $insert_data['grn_city_id'], 'country_code' => $i_value['iso_country_code']), 0, 1);

                        if ($check_if_data_exists['status'] == 1) {
                            $city_code = $check_if_data_exists['data'][0]['grn_city_id'];

                            if ($insert_data['grn_city_id'] != $city_code) {
                                $update_city_data = ['grn_city_id' => $c_value['code']];
                                $this->custom_db->update_record('all_api_city_master_update', $update_city_data, array('grn_city_id' => $insert_data['grn_city_id'], 'country_code' => $insert_data['country_code']));
                            }

                            $this->custom_db->insert_record('grn_api_new_city_list', $insert_data);
                        }

                        $this->custom_db->insert_record('all_api_city_master_update', $insert_data);
                    }

                    $update_date = [
                        'status' => 1,
                        'city_count' => $city_master_data['total'],
                        'error' => json_encode($city_master_data['cities'])
                    ];
                    $this->custom_db->update_record('api_country_master', $update_date, array('iso_country_code' => $i_value['iso_country_code']));
                }

                // Directly handling the case when cities are not found
                if (!isset($city_master_data['cities'])) {
                    $update_date = [
                        'status' => 1,
                        'city_count' => 0,
                        'error' => json_encode($city_master_data)
                    ];
                    $this->custom_db->update_record('api_country_master', $update_date, array('iso_country_code' => $i_value['iso_country_code']));
                }
            }
            echo "successs";
        }
    }

    function get_country_master_desc(): void
    {
        $country_master = $this->custom_db->single_table_records('api_country_master', '*', array('status' => 0), 0, 30, array('origin' => 'desc'));

        if ($country_master['status'] == 1) {
            foreach ($country_master['data'] as $i_value) {
                $city_master_data = $this->process_request($i_value['iso_country_code']);

                if ($city_master_data && isset($city_master_data['cities'])) {
                    foreach ($city_master_data['cities'] as $c_value) {
                        $insert_data = [
                            'grn_city_id' => $c_value['code'],
                            'city_name' => explode(",", $c_value['name'])[0],
                            'full_city_name' => $c_value['name'],
                            'country_name' => $i_value['country_name'],
                            'country_code' => $i_value['iso_country_code']
                        ];

                        $check_if_data_exists = $this->custom_db->single_table_records('all_api_city_master_update', 'grn_city_id', array('grn_city_id' => $insert_data['grn_city_id'], 'country_code' => $i_value['iso_country_code']), 0, 1);

                        if ($check_if_data_exists['status'] == 1) {
                            $city_code = $check_if_data_exists['data'][0]['grn_city_id'];

                            if ($insert_data['grn_city_id'] != $city_code) {
                                $update_city_data = ['grn_city_id' => $c_value['code']];
                                $this->custom_db->update_record('all_api_city_master_update', $update_city_data, array('grn_city_id' => $insert_data['grn_city_id'], 'country_code' => $insert_data['country_code']));
                            }
                            $this->custom_db->insert_record('grn_api_new_city_list', $insert_data);
                        }

                        $this->custom_db->insert_record('all_api_city_master_update', $insert_data);
                    }

                    $update_date = [
                        'status' => 1,
                        'city_count' => $city_master_data['total'],
                        'error' => json_encode($city_master_data['cities'])
                    ];
                    $this->custom_db->update_record('api_country_master', $update_date, array('iso_country_code' => $i_value['iso_country_code']));
                }

                // Directly handling the case when cities are not found
                if (!isset($city_master_data['cities'])) {
                    $update_date = [
                        'status' => 1,
                        'city_count' => 0,
                        'error' => json_encode($city_master_data)
                    ];
                    $this->custom_db->update_record('api_country_master', $update_date, array('iso_country_code' => $i_value['iso_country_code']));
                }
            }
            echo "successs";
        }
    }

    function check_grn_image_found(): void
    {
        error_reporting(0);

        $str = "select * from api_grn_static_images where status=0 and origin <=200000 limit 0,50";
        $execute_query = $this->db->query($str);
        $static_image = [];

        // No need for an 'else' block here, just initialize static_image if rows are found
        if ($execute_query->num_rows()) {
            $static_image = $execute_query->result_array();
        }
        $image_update_data = array();
        $update_condition = array();
        // Proceed only if $static_image has data
        if ($static_image) {
            foreach ($static_image as $value) {
                $image = $value['image_url'];
                // Check if the image exists
                $image_update_data['image_found'] = (file_get_contents($image)) ? 'Y' : 'N';
                $image_update_data['status'] = 1;
                $update_condition['origin'] = $value['origin'];

                // Update the image status in the database
                $this->custom_db->update_record('api_grn_static_images', $image_update_data, $update_condition);
            }
        }

        echo "successs";
    }


    function update_json_data(): void {
        $path = FCPATH . '/hb_static/groupcategories/groupcategories_1.json';
    
        // Proceed only if the file exists
        if (file_exists($path)) {
            $details = json_decode(file_get_contents($path), true);
    
            $hotelArray = $details['groupCategories'];
            foreach ($hotelArray as $value) {
                $insert_data = [
                    'code' => $value['code'],
                    'group_order' => $value['order'],
                    'name' => $value['name']['content'],
                    'description' => $value['description']['content']
                ];
                $this->custom_db->insert_record('hb_group_categories', $insert_data);
            }
        }    
        exit;
    }

    function process_request(int $feed_id, string $city_id = '', string $country_id = '', string $hotel_id = '', string $brand_id = ''): array|false
    {
        $url = match ($feed_id) {
            1 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=1&apikey=10b13bb6-67a2-4311-9d6b-62420157e394',
            2 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=2&apikey=10b13bb6-67a2-4311-9d6b-62420157e394',
            3 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=3&apikey=10b13bb6-67a2-4311-9d6b-62420157e394&ocountry_id=' . $country_id,
            4 => 'xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=4&apikey=10b13bb6-67a2-4311-9d6b-62420157e394',
            5 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=5&apikey=10b13bb6-67a2-4311-9d6b-62420157e394&mCity_id=' . $city_id . '&olanguage_id=1&ocurrency=INR',
            6 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=6&apikey=10b13bb6-67a2-4311-9d6b-62420157e394&mHotel_id=' . $hotel_id,
            7 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=7&apikey=10b13bb6-67a2-4311-9d6b-62420157e394&mhotel_id=' . $hotel_id,
            9 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=9&apikey=10b13bb6-67a2-4311-9d6b-62420157e394&mhotel_id=' . $hotel_id,
            10 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=10&apikey=10b13bb6-67a2-4311-9d6b-62420157e394&mhotel_id=' . $hotel_id,
            11 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=11&apikey=10b13bb6-67a2-4311-9d6b-62420157e394&mSinceDate=20111101',
            13 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=13&apikey=10b13bb6-67a2-4311-9d6b-62420157e394',
            14 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=14&apikey=10b13bb6-67a2-4311-9d6b-62420157e394&mhotel_id=' . $hotel_id,
            15 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=15&apikey=10b13bb6-67a2-4311-9d6b-62420157e394',
            16 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=16&apikey=10b13bb6-67a2-4311-9d6b-62420157e394&mbrand_id=' . $brand_id,
            17 => 'xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=17&apikey=10b13bb6-67a2-4311-9d6b-62420157e394&mcity_id=' . $city_id . '&ohotel_id=' . $hotel_id,
            18 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=18&apikey=10b13bb6-67a2-4311-9d6b-62420157e394&mcity_id=' . $city_id . '&ohotel_id=' . $hotel_id,
            19 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=19&apikey=10b13bb6-67a2-4311-9d6b-62420157e394&mhotel_id=' . $hotel_id,
            20 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=20&apikey=10b13bb6-67a2-4311-9d6b-62420157e394',
            21 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=21&apikey=10b13bb6-67a2-4311-9d6b-62420157e394',
            22 => 'http://xml.agoda.com/datafeeds/Feed.asmx/GetFeed?feed_id=22&apikey=10b13bb6-67a2-4311-9d6b-62420157e394',
            default => '',
        };

        if ($url == '') {
            return false;
        }

        echo $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $headers = curl_getinfo($ch);

        curl_close($ch);

        if ($headers['http_code'] != 200) {
            return false;
        }

        // If the response is an instance of DOMDocument, convert it to an array.
        if ($response instanceof DOMDocument) {
            $response = simplexml_load_string($response->saveXML());
            $response = json_decode(json_encode($response), true); // Convert SimpleXMLElement to array
        }

        return utf8_encode($response);
    }



    function download_agoda_region(): void
    {
        $api_data = $this->process_request(1);

        if (empty($api_data['Continent_feed']['continents'])) {
            return;
        }

        foreach ($api_data['Continent_feed']['continents']['continent'] as $value) {
            $data = [
                'continent_id' => $value['continent_id'],
                'continent_name' => $value['continent_name'],
                'continent_translated' => $value['continent_translated'],
                'active_hotels' => $value['active_hotels'],
            ];

            $check_if_exists = $this->custom_db->single_table_records(
                'api_agoda_continents',
                '*',
                ['continent_id' => $data['continent_id']],
                0,
                1
            );

            if ($check_if_exists['status'] == 0) {
                $this->custom_db->insert_record('api_agoda_continents', $data);
                continue;
            }

            $this->custom_db->update_record('api_agoda_continents', $data, ['continent_id' => $data['continent_id']]);
        }

        echo 'sucess';
        exit;
    }


    function download_agoda_country(): void
    {
        $api_data = $this->process_request(2);

        if (empty($api_data['Country_feed']['countries'])) {
            return;
        }

        foreach ($api_data['Country_feed']['countries']['country'] as $value) {
            $data = [
                'country_id' => $value['country_id'],
                'continent_id' => $value['continent_id'],
                'country_name' => $value['country_name'],
                'country_translated' => $value['country_translated'],
                'active_hotels' => $value['active_hotels'],
                'country_iso' => $value['country_iso'],
                'country_iso2' => $value['country_iso2'],
                'longitude' => $value['longitude'],
                'latitude' => $value['latitude'],
            ];

            $check_if_exists = $this->custom_db->single_table_records(
                'api_agoda_country_list',
                '*',
                ['country_id' => $value['country_id']],
                0,
                1
            );

            if ($check_if_exists['status'] == 0) {
                $this->custom_db->insert_record('api_agoda_country_list', $data);
                continue;
            }

            // update only fields other than ID
            unset($data['country_id']);
            $this->custom_db->update_record(
                'api_agoda_country_list',
                $data,
                ['country_id' => $value['country_id']]
            );
        }

        echo "success";
        exit;
    }

    function download_agoda_city(): void
    {
        $get_country_list = $this->custom_db->single_table_records(
            'api_agoda_country_list',
            '*',
            ['status' => 0],
            0,
            1
        );
        if (empty($get_country_list['data'][0])) {
            return;
        }
        $country_id = $get_country_list['data'][0]['country_id'];
        $api_data = $this->process_request(3, '', $country_id);

        $city_count = 0;
        $cities = $api_data['City_feed']['cities']['city'] ?? [];

        if (!empty($cities)) {
            $city_count = count($cities);
            foreach ($cities as $value) {
                $data = [
                    'city_id' => $value['city_id'],
                    'country_id' => $value['country_id'],
                    'city_name' => $value['city_name'],
                    'city_translated' => $value['city_translated'],
                    'active_hotels' => $value['active_hotels'],
                    'longitude' => $value['longitude'],
                    'latitude' => $value['latitude'],
                    'no_area' => $value['no_area'],
                ];

                $check_if_exists = $this->custom_db->single_table_records(
                    'api_agoda_city_list',
                    '*',
                    ['city_id' => $data['city_id']],
                    0,
                    1
                );
                if ($check_if_exists['status'] == 0) {
                    $this->custom_db->insert_record('api_agoda_city_list', $data);
                    continue;
                }

                $this->custom_db->update_record(
                    'api_agoda_city_list',
                    $data,
                    ['city_id' => $data['city_id']]
                );
            }
        }
        $city_data = [
            'status' => 1,
            'city_count' => $city_count,
        ];
        $this->custom_db->update_record(
            'api_agoda_country_list',
            $city_data,
            ['country_id' => $country_id]
        );
        echo "success";
        exit;
    }

    function update_agoda_hotel_by_origin_range($min_origin = 0, $limit = 5) {
        $str = "SELECT * FROM `api_agoda_city_list` WHERE origin >= {$min_origin} AND `status` = 0 LIMIT {$limit}";
        $execute_query = $this->db->query($str);
        $get_city_list = $execute_query->result_array();
    
        if ($get_city_list) {
            foreach ($get_city_list as $c_value) {
                $api_data = $this->process_request(5, $c_value['city_id']);
                $hotel_count = 0;
    
                $hotels = $api_data['Hotel_feed']['hotels']['hotel'] ?? null;
    
                if (isset($hotels[0])) {
                    // Multiple hotels
                    $hotel_count = count($hotels);
                }
    
                if (!empty($hotels)) {
                    // Single hotel but not array
                    $hotel_count = 1;
                    $hotels = [$hotels]; // Wrap in array for loop
                }
    
                if ($hotels) {
                    foreach ($hotels as $value) {
                        $data = [
                            'hotel_id' => $value['hotel_id'],
                            'hotel_name' => $value['hotel_name'],
                            'hotel_formerly_name' => $value['hotel_formerly_name'],
                            'star_rating' => $value['star_rating'],
                            'continent_id' => $value['continent_id'],
                            'country_id' => $value['country_id'],
                            'city_id' => $value['city_id'],
                            'area_id' => $value['area_id'],
                            'longitude' => $value['longitude'],
                            'latitude' => $value['latitude'],
                            'hotel_url' => $value['hotel_url'],
                            'rates_from' => $value['rates_from'],
                            'rates_currency' => $value['rates_currency'],
                            'popularity_score' => $value['popularity_score'],
                            'remark' => $value['remark'],
                            'number_of_reviews' => $value['number_of_reviews'],
                            'rating_average' => $value['rating_average'],
                            'rates_from_exclusive' => $value['rates_from_exclusive'],
                            'child_and_extra_bed_policy' => json_encode($value['child_and_extra_bed_policy']),
                            'accommodation_type' => $value['accommodation_type'],
                            'nationality_restrictions' => $value['nationality_restrictions']
                        ];
    
                        $check_if_exists = $this->custom_db->single_table_records('agoda_hotel_master', '*', ['hotel_id' => $data['hotel_id']], 0, 1);
    
                        if ($check_if_exists['status'] == 0) {
                            $this->custom_db->insert_record('agoda_hotel_master', $data);
                        }
    
                        $this->custom_db->update_record('agoda_hotel_master', $data, ['hotel_id' => $data['hotel_id']]);
                    }
                }
    
                $this->custom_db->update_record('api_agoda_city_list', [
                    'status' => 1,
                    'hotel_count' => $hotel_count
                ], ['origin' => $c_value['origin']]);
            }
        }
        exit;
    }
    


    function update_agoda_hotel_1(): void
    {
        $query = "SELECT * FROM (`api_agoda_city_list`) WHERE origin <= 2000 AND `status` = 0 LIMIT 5";
        $city_list = $this->db->query($query)->result_array();
    
        if (!$city_list) {
            exit;
        }
    
        foreach ($city_list as $city) {
            $api_data = $this->process_request(5, $city['city_id']);
            $hotels = $api_data['Hotel_feed']['hotels']['hotel'] ?? [];
    
            // Normalize single hotel response into array
            if (isset($hotels['hotel_id'])) {
                $hotels = [$hotels];
            }
    
            $hotel_count = count($hotels);
    
            foreach ($hotels as $hotel) {
                $data = [
                    'hotel_id' => $hotel['hotel_id'],
                    'hotel_name' => $hotel['hotel_name'],
                    'hotel_formerly_name' => $hotel['hotel_formerly_name'],
                    'star_rating' => $hotel['star_rating'],
                    'continent_id' => $hotel['continent_id'],
                    'country_id' => $hotel['country_id'],
                    'city_id' => $hotel['city_id'],
                    'area_id' => $hotel['area_id'],
                    'longitude' => $hotel['longitude'],
                    'latitude' => $hotel['latitude'],
                    'hotel_url' => $hotel['hotel_url'],
                    'rates_from' => $hotel['rates_from'],
                    'rates_currency' => $hotel['rates_currency'],
                    'popularity_score' => $hotel['popularity_score'],
                    'remark' => $hotel['remark'],
                    'number_of_reviews' => $hotel['number_of_reviews'],
                    'rating_average' => $hotel['rating_average'],
                    'rates_from_exclusive' => $hotel['rates_from_exclusive'],
                    'child_and_extra_bed_policy' => json_encode($hotel['child_and_extra_bed_policy']),
                    'accommodation_type' => $hotel['accommodation_type'],
                    'nationality_restrictions' => $hotel['nationality_restrictions'],
                ];
    
                $exists = $this->custom_db->single_table_records(
                    'agoda_hotel_master',
                    '*',
                    ['hotel_id' => $data['hotel_id']],
                    0,
                    1
                );
    
                $condition = ['hotel_id' => $data['hotel_id']];
    
                // Removed the else completely
                if (($exists['status'] ?? 0) == 0) {
                    $this->custom_db->insert_record('agoda_hotel_master', $data);
                }
                $this->custom_db->update_record('agoda_hotel_master', $data, $condition);
            }
    
            $update_data = [
                'status' => 1,
                'hotel_count' => $hotel_count
            ];
            $this->custom_db->update_record('api_agoda_city_list', $update_data, ['origin' => $city['origin']]);
        }
    
        exit;
    }
    


    function update_agoda_hotel_2(): void {
        $this->update_agoda_hotel_by_origin_range(2000);
    }

    function update_agoda_hotel_3(): void {
       $this->update_agoda_hotel_by_origin_range(4000);
    }

    function update_agoda_hotel_4(): void {
        $this->update_agoda_hotel_by_origin_range(6000);
    }

    function update_agoda_hotel_5(): void {
        $this->update_agoda_hotel_by_origin_range(8000);
    }

    function update_agoda_hotel_6(): void {
        $this->update_agoda_hotel_by_origin_range(10000);
    }
    //function agoda downloading the hotel images
    function donload_agoda_image(): void
    {
        $query = "SELECT * FROM (`agoda_hotel_master`) WHERE `image_status` = 0 AND image_not_found = 0 LIMIT 10";
        $hotels = $this->db->query($query)->result_array();

        if (!$hotels) {
            return;
        }

        foreach ($hotels as $hotel) {
            $api_data = $this->process_request(7, '', '', $hotel['hotel_id']);
            $pictures = $api_data['Picture_feed']['pictures']['picture'] ?? [];

            // Normalize to array if single image
            if (isset($pictures['hotel_id'])) {
                $pictures = [$pictures];
            }

            if (empty($pictures)) {
                $this->custom_db->update_record('agoda_hotel_master', [
                    'image_not_found' => 1
                ], ['hotel_id' => $hotel['hotel_id']]);
                continue;
            }

            $image_data = [];
            foreach ($pictures as $key => $pic) {
                $image_data[$key]['caption'] = $pic['caption'];
                $image_data[$key]['image_urls'] = $pic['URL'];
            }

            $this->custom_db->update_record('agoda_hotel_master', [
                'Hotel_ImageLinks' => json_encode($image_data),
                'Image' => $pictures[0]['URL'],
                'image_status' => 1
            ], ['hotel_id' => $pictures[0]['hotel_id']]);
        }
    }

    //function agoda downloading the hotel facilities
    function donload_agoda_facility(): void
    {
        $query = "SELECT * FROM (`agoda_hotel_master`) WHERE origin > 28589 AND `fac_status` = 0 AND city_id = 4923 LIMIT 10";
        $hotels = $this->db->query($query)->result_array();

        if (!$hotels) {
            return;
        }

        foreach ($hotels as $hotel) {
            $api_data = $this->process_request(14, '', '', $hotel['hotel_id']);
            $facilities = $api_data['Roomtype_facility_feed']['roomtype_facilities']['roomtype_facility'] ?? [];

            // Normalize to array if single facility
            if (isset($facilities['hotel_id'])) {
                $facilities = [$facilities];
            }

            if (empty($facilities)) {
                continue;
            }

            $prop_id = [];
            $facility_data = [];

            foreach ($facilities as $fac) {
                if (!in_array($fac['property_id'], $prop_id, true)) {
                    $facility_data[] = [
                        'property_id' => $fac['property_id'],
                        'property_name' => $fac['property_name'],
                        'translated_name' => $fac['translated_name'],
                    ];
                    $prop_id[] = $fac['property_id'];
                }
            }

            $this->custom_db->update_record('agoda_hotel_master', [
                'Hotel_facilities' => json_encode(array_values($facility_data)),
                'fac_status' => 1
            ], ['hotel_id' => $facilities[0]['hotel_id']]);
        }
    }

    //function agoda downloading the hotel data
    function donload_agoda_hotel_info(): void
    {
        $query = "SELECT * FROM (`agoda_hotel_master`) WHERE `hot_add_status` = 0 LIMIT 1";
        $hotels = $this->db->query($query)->result_array();

        if (empty($hotels)) {
            return;
        }

        foreach ($hotels as $_) {
            $api_data = $this->process_request(18, '', '', '12134');
            $addresses = $api_data['Hotel_address_feed']['hotel_addresses']['hotel_address'] ?? [];

            // Normalize to array if single address
            if (isset($addresses['hotel_id'])) {
                $addresses = [$addresses];
            }

            if (empty($addresses)) {
                continue;
            }

            $address = '';
            $condition = [];

            foreach ($addresses as $addr) {
                if (($addr['address_type'] ?? '') == 'English address') {
                    $condition['hotel_id'] = $addr['hotel_id'];
                    $address = implode(',', [
                        $addr['address_line_1'],
                        $addr['state'],
                        $addr['city'],
                        $addr['country'],
                        $addr['postal_code']
                    ]);
                    break;
                }
            }

            if (empty($address)) {
                continue;
            }

            $this->custom_db->update_record('agoda_hotel_master', [
                'address' => $address,
                'hot_add_status' => 1
            ], $condition);
        }
    }

    // downlaod hotel address
    function donload_agoda_hotel_desc(): void
    {
        $query = "SELECT * FROM (`agoda_hotel_master`) WHERE `hot_desc_status` = 0 LIMIT 1";
        $hotels = $this->db->query($query)->result_array();

        if (empty($hotels)) {
            return;
        }

        foreach ($hotels as $hotel) {
            $api_data = $this->process_request(17, '', '', $hotel['hotel_id']);
            $desc_data = $api_data['Hotel_description_feed']['hotel_descriptions']['hotel_description'] ?? null;

            if (!$desc_data) {
                continue;
            }

            $this->custom_db->update_record('agoda_hotel_master', [
                'description' => $desc_data['overview'],
                'hot_desc_status' => 1
            ], ['hotel_id' => $desc_data['hotel_id']]);
        }
    }

    /*
     * Transfer Airport, Hotel auto suggest
     */

    function get_airport_transfer_code_list(): void
    {
        $this->load->model('hotel_model');
        $this->load->model('transfer_model');

        $term = trim(strip_tags($this->input->get('term')));
        $result = [];

        $airport_data_list = $this->transfer_model->get_airport_list($term)->result();
        if (!valid_array($airport_data_list)) {
            $airport_data_list = $this->transfer_model->get_airport_list('')->result();
        }

        foreach ($airport_data_list as $airport) {
            $result[] = [
                'label' => $airport->airport_name . ' (' . $airport->airport_city . ')',
                'id' => $airport->airport_code,
                'transfer_type' => 'ProductTransferTerminal',
                'category' => [],
                'type' => []
            ];
        }

        $this->output_compressed_data($result);
    }

    function download_agoda_images(): void
    {
        $query = "SELECT hotel_id as hotel_code FROM `agoda_hotel_master` WHERE Image != '' LIMIT 10";
        $static_hotels = $this->db->query($query)->result_array();

        if (empty($static_hotels)) {
            return;
        }

        $contextOptions = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ];

        foreach ($static_hotels as $hotel) {
            $hotelCode = $hotel['hotel_code'] ?? null;
            if (!$hotelCode) {
                continue;
            }

            $record = $this->custom_db->single_table_records('agoda_hotel_master', 'origin,Image,image_d_status', ['hotel_id' => $hotelCode], 0, 1);
            if (($record['status'] ?? 0) != 1 || empty($record['data'][0])) {
                continue;
            }

            $hotelData = $record['data'][0];
            $imageUrl = $hotelData['Image'];
            $hotelOrigin = $hotelData['origin'];
            $imageStatus = $hotelData['image_d_status'];

            $pathInfo = pathinfo($imageUrl);
            $baseName = explode('?', $pathInfo['basename'])[0];

            $imageExists = file_get_contents($imageUrl, false, stream_context_create($contextOptions));

            $imageUpdateData = ['image_found' => 1];

            if ($imageExists) {
                if ($imageStatus != 1) {
                    $sanitizedCode = preg_replace("/[^a-zA-Z0-9]/", "", $hotelCode);
                    $imageFileName = $sanitizedCode . time() . $baseName;
                    $imagePath = './cdn/agoda/images/' . $imageFileName;

                    file_put_contents($imagePath, $imageExists);
                    $this->custom_db->update_record('agoda_hotel_master', ['travelomatix_path' => $imageFileName], ['origin' => $hotelOrigin]);
                }

                $imageUpdateData['image_found'] = 2;
            }

            $this->custom_db->update_record('agoda_hotel_master', $imageUpdateData, ['origin' => $hotelOrigin]);
            $this->custom_db->update_record('agoda_hotel_master', ['image_d_status' => 1], ['hotel_id' => $hotelCode]);
        }
    }

    /*
     *
     * Flight Search For Voice Search
     * Balu A
     *
     */


    function get_airport_code_list_for_voice_speach(string $term): void
    {
        $term = trim(strip_tags($term));
        $result = [];

        $__airports = $this->flight_model->get_airport_list($term)->result();

        foreach ($__airports as $airport) {
            $airports = [
                'label' => $airport->airport_city . ', ' . $airport->country . ' (' . $airport->airport_code . ')',
                'value' => $airport->airport_city . ' (' . $airport->airport_code . ')',
                'id' => $airport->origin,
                'category' => 'Search Results',
                'type' => 'Search Results',
            ];

            if (!empty($airport->top_destination)) {
                $airports['category'] = 'Top cities';
                $airports['type'] = 'Top cities';
            }

            $result[] = $airports;
        }

        if (!empty($result[0])) {
            echo $result[0]['value'] . '|' . $result[0]['id'];
        }
    }




    function vocie_colleting(string $data): void
    {
        $ignoredWords = ['flight', 'flights', 'from', 'to'];
        $city = [];

        $data = strtolower(urldecode($data));
        $words = explode(' ', $data);

        foreach ($words as $word) {
            if (!in_array($word, $ignoredWords, true)) {
                $city[] = $word;
            }
        }

        echo json_encode($city);
    }

    function get_booking_currency_details(): void
    {
        $api_price_details = json_decode(base64_decode((string) $this->input->post('api_price_data')), true);
        $api_markup_details = json_decode(base64_decode((string) $this->input->post('api_markup_data')), true);
        $convenience_fees_original = json_decode(base64_decode((string) $this->input->post('convenience_fees_original')), true);
    
        $offered_fare = $api_price_details['TotalDisplayFare']
            - $api_price_details['PriceBreakup']['AgentCommission']
            + $api_price_details['PriceBreakup']['AgentTdsOnCommision'];
    
        $currency_obj = new Currency([
            'module_type' => 'flight',
            'from' => get_api_data_currency(),
            'to' => get_application_currency_preference()
        ]);
    
        $converted_tax = get_converted_currency_value($currency_obj->force_currency_conversion($api_price_details['PriceBreakup']['Tax']));
        $converted_offered_fare = get_converted_currency_value($currency_obj->force_currency_conversion($offered_fare));
        $converted_agent_commission = get_converted_currency_value($currency_obj->force_currency_conversion($api_price_details['PriceBreakup']['AgentCommission']));
        $converted_agent_tds = get_converted_currency_value($currency_obj->force_currency_conversion($api_price_details['PriceBreakup']['AgentTdsOnCommision']));
    
        $markup_price = 0.0;
        if (!empty($api_markup_details['markup_type']) && $api_markup_details['markup_type'] == 'plus') {
            $markup_value = $api_markup_details['original_markup'];
            $markup_price = get_converted_currency_value($currency_obj->force_currency_conversion($markup_value));
        }
        if (!empty($api_markup_details['markup_type']) && $api_markup_details['markup_type'] != 'plus') {
            $markup_value = $api_markup_details['original_markup'];
            $markup_price = ($converted_offered_fare / 100) * $markup_value;
            $markup_price = number_format($markup_price, 2, '.', '');
        }
    
        $total_fare_before_commission = (
            floatval($markup_price)
            + floatval($converted_offered_fare)
            + $converted_agent_commission
            + $converted_agent_tds
        );
    
        $converted_convience_fee = 0.0;
        if (valid_array($convenience_fees_original)) {
            if ($convenience_fees_original['type'] == 'plus') {
                $converted_convience_fee = get_converted_currency_value(
                    $currency_obj->force_currency_conversion($convenience_fees_original['value'])
                );
            }
            if ($convenience_fees_original['type'] != 'plus') {
                $converted_convience_fee = ($total_fare_before_commission / 100) * $convenience_fees_original['value'];
            }
            $converted_convience_fee = roundoff_number($converted_convience_fee);
        }
    
        $total_tax = (
            floatval($markup_price)
            + floatval($converted_tax)
            - $converted_agent_commission
            + $converted_agent_tds
        );
    
        $total_fare = (
            floatval($converted_convience_fee)
            + floatval($markup_price)
            + floatval($converted_offered_fare)
            + $converted_agent_commission
            + $converted_agent_tds
        );
    
        $base_price = [];
        foreach ($api_price_details['PassengerBreakup'] as $pass_key => $passenger_price) {
            $base_price[$pass_key] = get_converted_currency_value(
                $currency_obj->force_currency_conversion($passenger_price['BasePrice'])
            );
        }
    
        $response = [
            'TotalTax' => $total_tax,
            'TotalFare' => $total_fare,
            'PassngerBasePrice' => $base_price,
            'convience_fee' => $converted_convience_fee,
        ];
    
        header('Content-type: application/json');
        echo json_encode($response);
    }
    

    function check_if_sub_array_exists(array $main_array, string|int $sub_priority): array
    {
        return array_values(array_filter(
            $main_array,
            fn($v) => $v['sub_priority'] == $sub_priority
        ));
    }

   
}
