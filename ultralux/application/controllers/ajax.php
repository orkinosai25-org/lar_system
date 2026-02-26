<?php
declare(strict_types=1);
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Controller for all ajax activities
 *
 * @package    Provab
 * @subpackage ajax loaders
 * @author      Balu A
 * @version     V1
 */

class Ajax extends CI_Controller
{
    private $current_module;

    public function __construct()
    {
        parent::__construct();
        if (!function_exists('is_ajax') || !is_ajax()) {
            // If needed, call $this->index();
        }

        ob_start();

        $this->load->model('flight_model');
        $this->load->model('car_model');

        $this->current_module = $this->config->item('current_module');
    }

    public function index()
    {
        // Default action if needed
    }

    public function get_city_list(int $country_id = 0, int $default_select = 0):void
    {
        if (intval($country_id) != 0) {
            $condition = array('country' => $country_id);
            $order_by = array('destination' => 'asc');

            $option_list = $this->custom_db->single_table_records(
                'api_city_list',
                'origin as k, destination as v',
                $condition,
                0,
                1000000,
                $order_by
            );

            if (isset($option_list['data']) && is_array($option_list['data']) && count($option_list['data']) > 0) {
                echo get_compressed_output(generate_options($option_list['data'], array($default_select)));
                exit;
            }
        }
    }
	public function get_country_list(int $continent_id = 0, int $default_select = 0, int $zone_id = 0): void
	{
		 $this->load->model('general_model');
	    // No need to urldecode an int parameter; removed that line

	    if ($continent_id != 0) {
	        $optionList = $this->general_model->get_country_list($continent_id, $zone_id);

	        if (!empty($optionList['data']) && is_array($optionList['data'])) {
	            echo get_compressed_output(generate_options($optionList['data'], [$default_select]));
	        }
	    }
	}

	public function location_list(int $limit = 0): void
	{
	    $chars = $this->input->get('term') ?? '';
	    $list = $this->general_model->get_location_list($chars, $limit);
	    $result = [];

	    if (is_array($list) && count($list) > 0) {
	        foreach ($list as $k => $v) {
	            $result[] = [
	                'id' => $k,
	                'label' => $v['name'] ?? '',
	                'value' => $v['origin'] ?? ''
	            ];
	        }
	    }

	    $this->output_compressed_data($result);
	}

	public function city_list(int $limit = 0): void
	{
	    $chars = $this->input->get('term') ?? '';
	    $list = $this->general_model->get_city_list($chars, $limit);
	    $result = [];

	    if (is_array($list) && count($list) > 0) {
	        foreach ($list as $k => $v) {
	            $result[] = [
	                'id' => $k,
	                'label' => $v['name'] ?? '',
	                'value' => $v['origin'] ?? ''
	            ];
	        }
	    }

	    $this->output_compressed_data($result);
	}

	public function get_currency_value(int $currency_origin = 0): never
	{
	    $data = $this->custom_db->single_table_records(
	        'currency_converter',
	        'value',
	        ['id' => $currency_origin]
	    );

	    $value = $data['data'][0]['value'] ?? 1;

	    header('Content-Type: application/json; charset=utf-8');
	    echo json_encode(['value' => $value], JSON_THROW_ON_ERROR);
	    exit;
	}

	public function get_airport_code_list(): void
	{
	    $term = trim(strip_tags($this->input->get('term') ?? ''));

	    $result = [];
	    $flagPath = DOMAIN_LAG_IMAGE_DIR . '/';

	    $__airports = $this->flight_model->get_airport_list($term)->result();

	    if (!is_array($__airports) || count($__airports) == 0) {
	        $__airports = $this->flight_model->get_airport_list('')->result();
	    }

	    foreach ($__airports as $airport) {
	        $result[] = [
	            'label' => "{$airport->airport_city}, {$airport->country} ({$airport->airport_code})",
	            'value' => "{$airport->airport_city}, {$airport->airport_name} ({$airport->airport_code})",
	            'id' => $airport->origin,
	            'airport_name' => $airport->airport_name,
	            'country_code' => $flagPath . strtolower($airport->CountryCode) . '.png',
	            'category' => !empty($airport->top_destination) ? 'Top cities' : 'Search Results',
	            'type' => !empty($airport->top_destination) ? 'Top cities' : 'Search Results',
	        ];
	    }

	    $this->output_compressed_data($result);
	}
	public function get_airport_city_list(): void
	{
	    $term = trim(strip_tags($this->input->get('term') ?? ''));
	    $result = [];

	    $__airports = $this->car_model->get_airport_list($term)->result();
	    if (!is_array($__airports) || count($__airports) == 0) {
	        $__airports = $this->car_model->get_airport_list('')->result();
	    }

	    foreach ($__airports as $airport) {
	        $result[] = [
	            'label' => "{$airport->Airport_Name_EN},{$airport->Country_Name_EN}",
	            'id' => $airport->origin,
	            'airport_code' => $airport->Airport_IATA,
	            'country_id' => $airport->Country_ISO,
	            'category' => 'Search Results',
	            'type' => 'Search Results',
	        ];
	    }

	    $city_list = $this->car_model->get_city_list($term)->result();

	    if (!is_array($city_list) || count($city_list) == 0) {
	        $city_list = $this->car_model->get_city_list('')->result();
	    }

	    foreach ($city_list as $city) {
	        if (!empty($city->City_ID)) {
	            $categoryType = !empty($city->top_destination) ? 'Top cities' : 'Search Results';
	            $result[] = [
	                'label' => "{$city->City_Name_EN} City/Downtown,{$city->Country_Name_EN}",
	                'id' => $city->origin,
	                'airport_code' => $city->Airport_IATA,
	                'country_id' => $city->Country_ISO,
	                'category' => $categoryType,
	                'type' => $categoryType,
	            ];
	        }
	    }

	    $this->output_compressed_data($result);
	}

     /**
     * Load cars from different sources
     */
    public function car_list(int $offset = 0): void
    {
        $response = [
            'data' => '',
            'msg' => '',
            'status' => FAILURE_STATUS,
        ];

        $searchParams = $this->input->get();
        $limit = (int) $this->config->item('car_per_page_limit');

        if (
            ($searchParams['op'] ?? '') == 'load'
            && isset($searchParams['search_id'], $searchParams['booking_source'])
            && (int)$searchParams['search_id'] > 0
        ) {
            load_car_lib($searchParams['booking_source']);

            switch ($searchParams['booking_source']) {
                case PROVAB_CAR_BOOKING_SOURCE:
                    $searchId = abs((int)$searchParams['search_id']);
                    $safeSearchData = $this->car_model->get_safe_search_data($searchId);
                    $rawCarList = $this->car_lib->get_car_list($searchId);

                    if (!empty($rawCarList['status'])) {
                        // Convert API currency to app-preferred currency
                        $currencyObj = new Currency([
                            'module_type' => 'car',
                            'from' => get_api_data_currency(),
                            'to' => get_application_currency_preference(),
                        ]);

                        $rawCarList = $this->car_lib->search_data_in_preferred_currency($rawCarList, $currencyObj, $searchId);

                        // Prepare another currency object for display
                        $currencyObj = new Currency([
                            'module_type' => 'car',
                            'from' => get_application_currency_preference(),
                            'to' => get_application_currency_preference(),
                        ]);

                        $filters = valid_array($searchParams['filters'] ?? null) ? $searchParams['filters'] : [];

                        // Format search results
                        $rawCarList['data'] = $this->car_lib->format_search_response(
                            $rawCarList['data'],
                            $currencyObj,
                            $searchId,
                            'b2b',
                            $filters
                        );

                        $sourceResultCount = $rawCarList['data']['source_result_count'] ?? 0;
                        $filterResultCount = $rawCarList['data']['filter_result_count'] ?? 0;

                        if ($offset == 0) {
                            $filterSummary = $this->car_lib->filter_summary($rawCarList['data']);
                            $response['filters'] = $filterSummary['data'] ?? [];
                        }

                        $rawCarList['data'] = $this->car_lib->get_page_data($rawCarList['data'], $offset, $limit);

                        $response['data'] = get_compressed_output(
                            $this->template->isolated_view('car/car_search_result_page', [
                                'currency_obj'    => $currencyObj,
                                'raw_car_list'    => $rawCarList['data'],
                                'search_id'       => $searchId,
                                'booking_source'  => $searchParams['booking_source'],
                                'attr'            => ['search_id' => $searchId],
                                'search_params'   => $safeSearchData,
                            ])
                        );

                        $response['status'] = SUCCESS_STATUS;
                        $response['total_result_count'] = $sourceResultCount;
                        $response['filter_result_count'] = $filterResultCount;
                        $response['offset'] = $offset + $limit;
                    }

                    break;
            }
        }

        $this->output_compressed_data($response);
    }

    /**
     * Compress and output JSON response
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
     * Compress and output JSON response for flight data
     */
    private function output_compressed_data_flight(array $data): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        ob_start('ob_gzhandler');
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
        ob_end_flush();
        exit;
    }
     /**
     * Load hotels from different sources - room facilities
     */
    public function get_room_facilities(): void
    {
        $response = [
            'data' => [],
            'msg' => [],
            'status' => FAILURE_STATUS
        ];

        $params = $this->input->get();
        if (empty($params['booking_source'])) {
            $this->output_compressed_data($response);
            return;
        }

        load_hotel_lib($params['booking_source']);

        match ($params['booking_source']) {
            PROVAB_HOTEL_BOOKING_SOURCE => $this->handle_provab_source($params, $response),
            CRS_HOTEL_BOOKING_SOURCE    => $this->handle_crs_source($params, $response),
            default                     => null
        };

        $this->output_compressed_data($response);
    }

    /**
     * Handle PROVAB hotel source logic
     */
    private function handle_provab_source(array $params, array &$response): void
    {
        $raw_room_list = [];
        $raw_room_list = $this->hotel_lib->get_room_facilities($params);

        $cancellation_string = $this->get_cancellation_policy($params);

        $room_inclusions = json_decode(base64_decode($params['room_amenties'] ?? ''), true) ?? [];
        $other_inclusions = trim(json_decode(base64_decode($params['otherroomamenties'] ?? ''), true)) ?? [];
        if(empty($other_inclusions) == true){
            $other_inclusions = [];
        }

        $all_inclusions = array_merge($room_inclusions, $other_inclusions);
        $raw_room_list['data']['room_name'] = $params['room_name'] ?? '';
        $raw_room_list['data']['inclusions'] = $all_inclusions;
        $raw_room_list['data']['cancellation_policy'] = $cancellation_string;

        $response['data'] = get_compressed_output(
            $this->template->isolated_view('hotel/tbo/tbo_room_facilities', $raw_room_list['data'])
        );
    }

    /**
     * Handle CRS hotel source logic
     */
    private function handle_crs_source(array $params, array &$response): void
    {
        $room_facilities = [];
        $raw_room_list = [];
        $room_faci = $this->custom_db->single_table_records(
            'eco_stays_rooms',
            '*',
            ['stays_origin' => $params['hotel_code'] ?? '']
        );

        $amenities = json_decode($room_faci['data'][0]['amenities'] ?? '[]', true);
        foreach ($amenities as $value) {
            $test_val = $this->custom_db->single_table_records(
                'eco_stays_room_amenities',
                '*',
                ['origin' => $value]
            );
            if (!empty($test_val['data'][0]['name'])) {
                $room_facilities[] = $test_val['data'][0]['name'];
            }
        }

        $cancellation_string = $this->get_cancellation_policy($params);

        $raw_room_list['data']['room_name'] = $params['room_name'] ?? '';
        $raw_room_list['data']['cancellation_policy'] = $cancellation_string;
        $raw_room_list['data']['room_facilities'] = $room_facilities;

        $response['data'] = get_compressed_output(
            $this->template->isolated_view('hotel/tbo/tbo_room_facilities', $raw_room_list['data'])
        );
    }
    public function get_room_details(): void
    {
        $response = [
            'data' => '',
            'msg' => '',
            'status' => FAILURE_STATUS
        ];

        $params = $this->input->post();

        $searchId = (int) ($params['search_id'] ?? 0);
        $op_data = $params['op'] ?? '';
        $bookingSource = $params['booking_source'] ?? null;
        $resultIndex = urldecode($params['ResultIndex'] ?? '');

        if ($op_data != 'get_room_details' || $searchId <= 0 || empty($bookingSource)) {
            $this->output_compressed_data($response);
            return;
        }

        $appPreferredCurrency = get_application_currency_preference();
        $appDefaultCurrency = get_application_currency_preference();

        load_hotel_lib($bookingSource);
        $this->hotel_lib->search_data($searchId);
        $attr = ['search_id' => $searchId];

        $rawRoomList = $this->hotel_lib->get_room_list($resultIndex);
        $safeSearchData = $this->hotel_model->get_safe_search_data($searchId);

        if (!($rawRoomList['status'] ?? false)) {
            $this->output_compressed_data($response);
            return;
        }

        $currencyObj = new Currency([
            'module_type' => 'hotel',
            'from' => get_api_data_currency(),
            'to' => $appPreferredCurrency
        ]);

        $rawRoomList = $this->hotel_lib->roomlist_in_preferred_currency(
            $rawRoomList,
            $currencyObj,
            $searchId,
            'b2b'
        );

        $displayCurrencyObj = new Currency([
            'module_type' => 'hotel',
            'from' => $appPreferredCurrency,
            'to' => $appDefaultCurrency
        ]);

        $viewData = [
            'currency_obj' => $displayCurrencyObj,
            'params' => $params,
            'raw_room_list' => $rawRoomList['data'] ?? [],
            'hotel_search_params' => $safeSearchData['data'] ?? [],
            'application_preferred_currency' => $appPreferredCurrency,
            'application_default_currency' => $appDefaultCurrency,
            'attr' => $attr
        ];

        $response['data'] = get_compressed_output(
            $this->template->isolated_view('hotel/tbo/tbo_room_list', $viewData)
        );
        $response['status'] = SUCCESS_STATUS;

        $this->output_compressed_data($response);
    }
     public function flight_list(int $search_id = 0): void
    {
        $response = [
            'data' => '',
            'msg' => '',
            'status' => FAILURE_STATUS
        ];

        $searchParams = $this->input->get();
        $searchId = (int)($searchParams['search_id'] ?? 0);
        $bookingSource = $searchParams['booking_source'] ?? '';
        $op_data = $searchParams['op'] ?? '';

        if ($op_data != 'load' || $searchId <= 0 || empty($bookingSource)) {
            $this->sendNoCacheHeaders();
            $this->output_compressed_data_flight($response);
            return;
        }

        load_flight_lib($bookingSource);

        if ($bookingSource == PROVAB_FLIGHT_BOOKING_SOURCE) {
            $rawFlightList = $this->flight_lib->get_flight_list($searchId);

            if (!($rawFlightList['status'] ?? false)) {
                $this->sendNoCacheHeaders();
                $this->output_compressed_data_flight($response);
                return;
            }

            $apiCurrency = get_api_data_currency();
            $appCurrency = get_application_currency_preference();

            // Convert API currency to preferred currency
            $currencyObj = new Currency([
                'module_type' => 'flight',
                'from' => $apiCurrency,
                'to' => $appCurrency
            ]);

            $rawSearchResult = $rawFlightList['data']['Search']['FlightDataList'] ?? [];
            $rawSearchResult = $this->flight_lib->search_data_in_preferred_currency($rawSearchResult, $currencyObj);

            // Format the result for display
            $currencyObjDisplay = new Currency([
                'module_type' => 'flight',
                'from' => $appCurrency,
                'to' => $appCurrency
            ]);

            $formattedSearchData = $this->flight_lib->format_search_response(
                $rawSearchResult,
                $currencyObjDisplay,
                $searchId,
                $this->current_module,
                $rawFlightList['from_cache'] ?? false,
                $rawFlightList['search_hash'] ?? ''
            );

            $rawFlightList['data'] = $formattedSearchData['data'];
            $routeCount = count($formattedSearchData['data']['Flights'] ?? []);
            $isDomesticRoundway = $formattedSearchData['data']['JourneySummary']['IsDomesticRoundway'] ?? false;

            $isValidRoundtrip =
                ($routeCount > 0 && !$isDomesticRoundway) ||
                ($routeCount == 2 && $isDomesticRoundway);

            if ($isValidRoundtrip) {
                $attr = ['search_id' => $searchId];

                $pageParams = [
                    'raw_flight_list' => $formattedSearchData['data'],
                    'search_id' => $searchId,
                    'booking_url' => $formattedSearchData['booking_url'] ?? '',
                    'booking_source' => $bookingSource,
                    'cabin_class' => $rawFlightList['cabin_class'] ?? '',
                    'trip_type' => $this->flight_lib->master_search_data['trip_type'] ?? '',
                    'attr' => $attr,
                    'route_count' => $routeCount,
                    'IsDomestic' => $formattedSearchData['data']['JourneySummary']['IsDomestic'] ?? false,
                    'domestic_round_way_flight' => $isDomesticRoundway,
                ];

                $viewData = $this->template->isolated_view('flight/tbo/tbo_col2x_search_result', $pageParams);
                $response['data'] = get_compressed_output($viewData);
                $response['status'] = SUCCESS_STATUS;
                $response['session_expiry_details'] = $formattedSearchData['session_expiry_details'] ?? [];
            }
        }

        $this->sendNoCacheHeaders();
        $this->output_compressed_data_flight($response);
    }

    private function sendNoCacheHeaders(): void
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }
     /**
     * Get Data For Fare Calendar
     */
    public function puls_minus_days_fare_list(string $booking_source): void
    {
        $response = [
            'data' => [],
            'status' => FAILURE_STATUS,
        ];

        $params = $this->input->get();
        $searchId = (int)($params['search_id'] ?? 0);

        if ($searchId <= 0) {
            $this->output_compressed_data($response);
            return;
        }

        load_flight_lib($booking_source);
        $searchData = $this->flight_lib->search_data($searchId);

        if (($searchData['status'] ?? '') != SUCCESS_STATUS) {
            $this->output_compressed_data($response);
            return;
        }

        $departure = $searchData['data']['depature'] ?? '';
        if (empty($departure)) {
            $this->output_compressed_data($response);
            return;
        }

        $dateArray = [];
        $departureTime = strtotime(subtract_days_from_date(3, $departure));
        $now = time();

        $dateArray[] = $now >= $departureTime
            ? date('Y-m-d', strtotime(add_days_to_date(1)))
            : date('Y-m-d', $departureTime);

        // Get first day of next month
        $dateArray[] = date('Y-m-01', strtotime($departure . ' +1 month'));

        $dayFareList = [];

        foreach ($dateArray as $date) {
            $searchData['data']['depature'] = $date;
            $safeSearch = $this->flight_lib->calendar_safe_search_data($searchData['data']);

            if (!valid_array($safeSearch)) {
                continue;
            }

            if ($booking_source == PROVAB_FLIGHT_BOOKING_SOURCE) {
                $rawFareList = $this->flight_lib->get_fare_list($safeSearch);

                if (!($rawFareList['status'] ?? false)) {
                    continue;
                }

                $formattedFare = $this->flight_lib->format_cheap_fare_list($rawFareList['data'] ?? []);
                if (($formattedFare['status'] ?? '') != SUCCESS_STATUS) {
                    $response['msg'] = 'Not Available! Please Try Later!';
                    continue;
                }

                $response['data']['departure'] = $safeSearch['depature'];
                $traceId = $rawFareList['data']['TraceId'] ?? '';
                $calendarEvents = $this->get_fare_calendar_events($formattedFare['data'], $traceId);

                $dayFareList = array_merge($dayFareList, $calendarEvents);
                $response['status'] = SUCCESS_STATUS;
            }
        }

        $response['data']['day_fare_list'] = $dayFareList;

        $this->output_compressed_data($response);
    }
    public function fare_list(string $booking_source): void
	{
	    $response = [
	        'data'   => '',
	        'msg'    => '',
	        'status' => FAILURE_STATUS,
	    ];

	    $search_params = $this->input->get();
	    load_flight_lib($booking_source);

	    $search_params = $this->flight_lib->calendar_safe_search_data($search_params);

	    if (!valid_array($search_params)) {
	        $this->output_compressed_data($response);
	        return;
	    }

	    if ($booking_source !== PROVAB_FLIGHT_BOOKING_SOURCE) {
	        $this->output_compressed_data($response);
	        return;
	    }

	    $raw_fare_list = $this->flight_lib->get_fare_list($search_params);

	    if (empty($raw_fare_list['status'])) {
	        $this->output_compressed_data($response);
	        return;
	    }

	    $fare_calendar_list = $this->flight_lib->format_cheap_fare_list($raw_fare_list['data'] ?? []);

	    if (($fare_calendar_list['status'] ?? '') !== SUCCESS_STATUS) {
	        $response['msg'] = 'Not Available! Please Try Later!';
	        $this->output_compressed_data($response);
	        return;
	    }

	    $response['data'] = [
	        'departure' => $search_params['depature'], // note typo? keep or fix to 'departure'
	        'day_fare_list' => $this->get_fare_calendar_events(
	            $fare_calendar_list['data'],
	            $raw_fare_list['data']['GetCalendarFareResult']['SessionId'] ?? ''
	        ),
	    ];
	    $response['status'] = SUCCESS_STATUS;

	    $this->output_compressed_data($response);
	}


    /**
     * Calendar Event Object
     */
    private function get_calendar_event_obj(
        string $title = '',
        string $start = '',
        string $tip = '',
        string $add_class = '',
        string $href = '',
        string $event_date = '',
        string $session_id = '',
        string $data_id = '',
        string $class = ''
    ): array {
        $event_obj = [];

        $event_obj['data_id'] = $data_id;
        $event_obj['title'] = $title;

        $date_parts = explode(' ', $start);
        $date_only = $date_parts[0] ?? '';
        $event_obj['start'] = '';
        if (!empty($date_only)) {
            $event_obj['start'] = $date_only;
            $event_obj['start_label'] = date('D, d M', strtotime($date_only));
        } 
        $event_obj['tip'] = $tip;
        $event_obj['href'] = $href;

        if (!empty($event_date)) {
            $event_obj['event_date'] = $event_date;
        }

        $event_obj['class'] = $class;

        if (!empty($session_id)) {
            $event_obj['session_id'] = $session_id;
        }

        $event_obj['add_class'] = $add_class;

        return $event_obj;
    }
   	public function day_fare_list(string $booking_source): void
	{
	    $response = [
	        'data'   => '',
	        'msg'    => '',
	        'status' => FAILURE_STATUS,
	    ];

	    $search_params = $this->input->get();
	    load_flight_lib($booking_source);

	    $safe_search_params = $this->flight_lib->calendar_day_fare_safe_search_data($search_params);

	    if (($safe_search_params['status'] ?? '') !== SUCCESS_STATUS) {
	        $this->output_compressed_data($response);
	        return;
	    }

	    if ($booking_source !== PROVAB_FLIGHT_BOOKING_SOURCE) {
	        $this->output_compressed_data($response);
	        return;
	    }

	    $raw_day_fare_list = $this->flight_lib->get_day_fare($search_params);

	    if (empty($raw_day_fare_list['status'])) {
	        $this->output_compressed_data($response);
	        return;
	    }

	    $fare_calendar_list = $this->flight_lib->format_day_fare_list($raw_day_fare_list['data'] ?? []);

	    if (($fare_calendar_list['status'] ?? '') !== SUCCESS_STATUS) {
	        $response['msg'] = 'Not Available!!! Please Try Later!!!!';
	        $this->output_compressed_data($response);
	        return;
	    }

	    $calendar_events = $this->get_fare_calendar_events($fare_calendar_list['data'], '');

	    $response['data'] = [
	        'day_fare_list' => $calendar_events,
	        'departure' => $search_params['depature'] ?? '', // keep original key typo or fix to 'departure'
	    ];

	    $response['status'] = SUCCESS_STATUS;

	    $this->output_compressed_data($response);
	}

	/**
	 * Convert events data to calendar event objects
	 *
	 * @param array $events
	 * @param string $session_id
	 * @param string|null $departure_date Optional departure date for active class comparison
	 * @return array
	 */
	private function get_fare_calendar_events(array $events, string $session_id = '', ?string $departure_date = null): array
	{
	    $currency_obj = new Currency([
	        'module_type' => 'flight',
	        'from' => get_api_data_currency(),
	        'to' => get_application_currency_preference(),
	    ]);

	    $calendar_events = [];

	    foreach ($events as $k => $day_fare) {
	        if (valid_array($day_fare)) {
	            $fare_object = ['BaseFare' => $day_fare['BaseFare'] ?? 0];
	            $baseFareData = $this->flight_lib->update_markup_currency($fare_object, $currency_obj);

	            $baseFareValue = $baseFareData['BaseFare'] ?? 0;
	            $tax = $day_fare['tax'] ?? 0;

	            // Calculate total price and floor it
	            $price = floor($baseFareValue + $tax);

	            $class = '';
	            if ($departure_date !== null && strtotime($departure_date) === strtotime($day_fare['date'] ?? '')) {
	                $class = 'active';
	            }

	            $event_obj = $this->get_calendar_event_obj(
	                $currency_obj->get_currency_symbol(get_application_currency_preference()) . ' ' . $price,
	                $k,
	                trim(($day_fare['airline_name'] ?? '') . '-' . ($day_fare['airline_code'] ?? '')),
	                'search-day-fare',
	                '',
	                $day_fare['departure'] ?? '',
	                '',
	                $day_fare['airline_code'] ?? '',
	                $class
	            );

	            $calendar_events[] = $event_obj;
	            continue;
	        }

	        // fallback event when day fare data is invalid
	        $calendar_events[] = $this->get_calendar_event_obj(
	            'Update',
	            $k,
	            'Current Cheapest Fare Not Available. Click To Get Latest Fare.',
	            'update-day-fare',
	            '',
	            $k,
	            $session_id,
	            ''
	        );
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
            'data' => '',
            'msg' => '<i class="fa fa-warning text-danger"></i> Fare Details Not Available',
        ];

        $params = $this->input->post();

        load_flight_lib($params['booking_source'] ?? '');
        $data_access_key = $params['data_access_key'] ?? '';
        $params['data_access_key'] = unserialized_data($data_access_key);

        if (!empty($params['data_access_key'])) {
            if (($params['booking_source'] ?? '') == PROVAB_FLIGHT_BOOKING_SOURCE) {
                $params['data_access_key'] = $this->flight_lib->read_token($data_access_key);
                $data = $this->flight_lib->get_fare_details($params['data_access_key'], $params['search_access_key'] ?? '');
                if (($data['status'] ?? '') == SUCCESS_STATUS) {
                    $response['status'] = SUCCESS_STATUS;
                    $response['data'] = $this->template->isolated_view('flight/tbo/fare_details', ['fare_rules' => $data['data']]);
                    $response['msg'] = 'Fare Details Available';
                }
            }
        }

        $this->output_compressed_data($response);
    }
    /**
     * Get combined booking form data from two trip ways
     */
    public function get_combined_booking_from(): void
    {
        $response = [
            'status' => FAILURE_STATUS,
            'data' => [],
        ];

        $params = $this->input->post();

        if (!empty($params['search_id']) && !empty($params['trip_way_1']) && !empty($params['trip_way_2'])) {
            $tmp_trip_way_1 = json_decode($params['trip_way_1'], true, 512, JSON_THROW_ON_ERROR);
            $tmp_trip_way_2 = json_decode($params['trip_way_2'], true, 512, JSON_THROW_ON_ERROR);

            $trip_way_1 = [];
            foreach ($tmp_trip_way_1 as $item) {
                $trip_way_1[$item['name']] = $item['value'];
            }

            $trip_way_2 = [];
            foreach ($tmp_trip_way_2 as $item) {
                $trip_way_2[$item['name']] = $item['value'];
            }

            $booking_source = $trip_way_1['booking_source'] ?? '';

            switch ($booking_source) {
                case PROVAB_FLIGHT_BOOKING_SOURCE:
                    load_flight_lib(PROVAB_FLIGHT_BOOKING_SOURCE);
                    $response['data']['booking_url'] = $this->flight_lib->booking_url((int)$params['search_id']);
                    $response['data']['form_content'] = $this->flight_lib->get_form_content($trip_way_1, $trip_way_2);
                    $response['status'] = SUCCESS_STATUS;
                    break;
            }
        }

        $this->output_compressed_data($response);
    }

    /**
     * Log event IP info by exception ID
     */
    public function log_event_ip_info(string|int $eid): void
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

    //---------------------------------------------------------------- Booking Events Starts

    /**
     * Load Booking Events of all the modules
     */
    public function booking_events(): void
    {
        $status = true;
        $calendar_events = [];

        $condition = [
            [
                'BD.created_datetime', '>=',
                $this->db->escape(date('Y-m-d', strtotime(subtract_days_from_date(90))))
            ]
        ]; // last 90 days only

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
        if (is_active_transferv1_module()) {
            $calendar_events = array_merge($calendar_events, $this->transfers_booking_events($condition));
        }

        // Output JSON response and exit
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => $status, 'data' => $calendar_events], JSON_THROW_ON_ERROR));
    }

    /**
     * Hotel Booking Events Summary
     * @param array $condition
     * @return array<int, array<string, string>>
     */
    private function hotel_booking_events(array $condition): array
    {
        $this->load->model('hotel_model');
        $data_list = $this->hotel_model->booking($condition);

        $this->load->library('booking_data_formatter');
        $table_data = $this->booking_data_formatter->format_hotel_booking_data($data_list, 'b2b');

        $booking_details = $table_data['data']['booking_details'] ?? [];
        $calendar_events = [];

        if (valid_array($booking_details)) {
            foreach ($booking_details as $key => $booking) {
                $calendar_events[$key] = [
                    'title'     => $booking['app_reference'] . '-' . $booking['status'],
                    'start'     => $booking['created_datetime'],
                    'tip'       => $booking['app_reference'] . '-PNR:' . $booking['confirmation_reference'] .
                        '-From:' . $booking['hotel_check_in'] . ', To:' . $booking['hotel_check_out'] .
                        '-' . $booking['status'] . '- Click To View More Details',
                    'href'      => hotel_voucher_url($booking['app_reference'], $booking['booking_source'], $booking['status']),
                    'add_class' => 'hand-cursor event-hand hotel-booking',
                ];
            }
        }

        return $calendar_events;
    }
    /**
     * Flight Booking Events Summary
     * @param array<int, mixed> $condition
     * @return array<int, array<string, string>>
     */
    private function flight_booking_events(array $condition): array
    {
        $this->load->model('flight_model');
        $data_list = $this->flight_model->booking($condition);

        $this->load->library('booking_data_formatter');
        $table_data = $this->booking_data_formatter->format_flight_booking_data($data_list, 'b2b');

        $booking_details = $table_data['data']['booking_details'] ?? [];
        $calendar_events = [];

        if (valid_array($booking_details)) {
            foreach ($booking_details as $v) {
                $calendar_events[] = [
                    'title' => "{$v['app_reference']}-{$v['status']}",
                    'start' => $v['created_datetime'],
                    'tip' => "{$v['app_reference']},From:{$v['journey_from']}, To:{$v['journey_to']}-{$v['status']}- Click To View More Details",
                    'href' => flight_voucher_url($v['app_reference'], $v['booking_source'], $v['status']),
                    'add_class' => 'hand-cursor event-hand flight-booking',
                ];
            }
        }

        return $calendar_events;
    }

    //---------------------------------------------------------------- Booking Events End

    /**
     * Auto suggest booking ID
     */
    public function auto_suggest_booking_id(): void
    {
        $get_data = $this->input->get();

        if (valid_array($get_data) && !empty($get_data['term']) && !empty($get_data['module'])) {
            $this->load->model('report_model');
            $module = trim($get_data['module']);
            $chars = $get_data['term'];

            $list = match ($module) {
                PROVAB_FLIGHT_BOOKING_SOURCE => $this->report_model->auto_suggest_flight_booking_id($chars),
                PROVAB_HOTEL_BOOKING_SOURCE => $this->report_model->auto_suggest_hotel_booking_id($chars),
                default => [],
            };

            $temp_list = [];
            if (valid_array($list)) {
                foreach ($list as $k => $v) {
                    $temp_list[] = [
                        'id' => $k,
                        'label' => $v['app_reference'],
                        'value' => $v['app_reference'],
                    ];
                }
            }

            $this->output_compressed_data($temp_list);
        }
    }

    /**
     * Get Bank Branches
     */
    public function get_bank_branches(int $bank_origin): void
    {
        $data = ['status' => false, 'branches' => false];

        if ($bank_origin > 0) {
            $branch_details = $this->custom_db->single_table_records(
                'bank_account_details',
                'origin, en_branch_name, account_number',
                ['origin' => $bank_origin, 'status' => ACTIVE]
            );

            if ($branch_details['status'] == true && !empty($branch_details['data'][0])) {
                $data['status'] = true;
                $data['branch'] = $branch_details['data'][0]['en_branch_name'];
                $data['account_number'] = $branch_details['data'][0]['account_number'];
            }
        }

        $this->output_compressed_data($data);
    }

    /**
     * Get Hotel Images by HotelCode
     */
    public function get_hotel_images(): void
    {
        $post_params = $this->input->post();

        if (!empty($post_params['hotel_code']) && !empty($post_params['booking_source'])) {
            $response = [];

            switch ($post_params['booking_source']) {
                case PROVAB_HOTEL_BOOKING_SOURCE:
                    load_hotel_lib($post_params['booking_source']);
                    $raw_hotel_images = $this->hotel_lib->get_hotel_images($post_params['hotel_code']);

                    if (!empty($raw_hotel_images['status']) && $raw_hotel_images['status'] == true) {
                        $this->hotel_model->add_hotel_images(
                            (int)($post_params['search_id'] ?? 0),
                            $raw_hotel_images['data'],
                            $post_params['hotel_code']
                        );

                        $response['data'] = get_compressed_output(
                            $this->template->isolated_view('hotel/tbo/tbo_hotel_images', [
                                'hotel_images' => $raw_hotel_images,
                                'HotelCode' => $post_params['hotel_code'],
                                'HotelName' => $post_params['Hotel_name'] ?? '',
                            ])
                        );
                    }
                    break;
                default:
                    $response['error'] = 'Unsupported booking source';
            }

            $this->output_compressed_data($response);
        }
        exit;
    }
    public function get_cancellation_policy(): string
	{
	    $getParams = $this->input->get();
	    $roomPrice = (float)$getParams['room_price'] ?? 0.0;

	    $currencyObj = new Currency([
	        'module_type' => 'hotel',
	        'from' => get_api_data_currency(),
	        'to' => get_application_currency_preference()
	    ]);

	    if (empty($getParams['booking_source'])) {
	        return 'This rate is non-refundable. If you cancel this booking you will not be refunded any of the payment.';
	    }

	    load_hotel_lib($getParams['booking_source']);

	    // If 'today_cancel_date' is false or not set, proceed
	    if (!empty($getParams['today_cancel_date'])) {
	        return 'This rate is non-refundable. If you cancel this booking you will not be refunded any of the payment.';
	    }

	    $cancelString = '';

	    if (!empty($getParams['policy_code'])) {
	        $safeSearchData = $this->hotel_model->get_safe_search_data($getParams['tb_search_id']);
	        $getParams['no_of_nights'] = $safeSearchData['data']['no_of_nights'] ?? 0;
	        $getParams['room_count'] = $safeSearchData['data']['room_count'] ?? 0;
	        $getParams['check_in'] = $safeSearchData['data']['from_date'] ?? null;

	        $cancellationDetails = $this->hotel_lib->get_cancellation_details($getParams);
	        $policies = $cancellationDetails['GetCancellationPolicy']['policy'][0]['policy'] ?? [];

	        // Unique policies based on 'Charge' field
	        $uniquePolicies = $this->hotel_lib->php_arrayUnique($policies, 'Charge');

	        foreach ($uniquePolicies as $key => $policy) {
	            $policy['Charge'] = $this->hotel_lib->update_cancellation_markup_currency(
	                $policy['Charge'], 
	                $currencyObj, 
	                $getParams['search_id'] ?? null
	            );

	            $amountStr = '';
	            $policyText = '';

	            if ((float)$policy['Charge'] == 0.0) {
	                $policyText = 'No cancellation charges, if cancelled before ' . 
	                    (new DateTimeImmutable($policy['ToDate']))->format('d M Y');
	            } else {
	                $amountStr = match ($policy['ChargeType']) {
	                    1 => $currencyObj->get_currency_symbol($currencyObj->to_currency) . ' ' . round($policy['Charge']),
	                    2 => $currencyObj->get_currency_symbol($currencyObj->to_currency) . ' ' . $roomPrice,
	                    default => ''
	                };

	                $currentDate = new DateTimeImmutable('now');
	                $fromDate = new DateTimeImmutable($policy['FromDate']);

	                $nextPolicyExists = isset($uniquePolicies[$key + 1]);

	                if ($nextPolicyExists && $fromDate > $currentDate) {
	                    $policyText = sprintf(
	                        'Cancellations made after %s to %s, would be charged %s',
	                        $fromDate->format('d M Y'),
	                        (new DateTimeImmutable($policy['ToDate']))->format('d M Y'),
	                        $amountStr
	                    );
	                } else {
	                    // Adjust fromDate if in past
	                    $effectiveFromDate = $fromDate > $currentDate ? $fromDate : $currentDate;
	                    $policyText = sprintf(
	                        'Cancellations made after %s, or no-show, would be charged %s',
	                        $effectiveFromDate->format('d M Y'),
	                        $amountStr
	                    );
	                }
	            }
	            $cancelString .= $policyText . '<br/>';
	        }
	    } else {
	        // Policy code not set, decode policy details
	        $cancelString = $this->process_policy_details($getParams['policy_details'] ?? '', $currencyObj, $roomPrice);
	    }

	    return $cancelString ?: 'This rate is non-refundable. If you cancel this booking you will not be refunded any of the payment.';
	}

	private function process_policy_details(string $encodedPolicyDetails, Currency $currencyObj, float $roomPrice): string
	{
	    if (empty($encodedPolicyDetails)) {
	        return '';
	    }

	    $decoded = json_decode(base64_decode($encodedPolicyDetails));
	    if (!$decoded) {
	        return '';
	    }

	    $policyDetails = json_decode(json_encode($decoded), true);
	    $reversedDetails = array_reverse($policyDetails);
	    $uniquePolicies = $this->hotel_lib->php_arrayUnique($reversedDetails, 'Charge');
	    $cancelReverse = $uniquePolicies;

	    $cancelString = '';

	    foreach ($uniquePolicies as $key => $policy) {
	        $policyText = '';

	        if ((float)$policy['Charge'] == 0.0) {
	            $policyText = 'No cancellation charges, if cancelled before ' . 
	                (new DateTimeImmutable($policy['ToDate']))->format('d M Y');
	        } else {
	            $amountStr = match ($policy['ChargeType']) {
	                1 => $currencyObj->get_currency_symbol($currencyObj->to_currency) . ' ' . $policy['Charge'],
	                2 => $currencyObj->get_currency_symbol($currencyObj->to_currency) . ' ' . $roomPrice,
	                default => ''
	            };

	            $currentDate = new DateTimeImmutable('now');
	            $fromDate = new DateTimeImmutable($policy['FromDate']);
	            $nextPolicyExists = isset($cancelReverse[$key + 1]);

	            if ($nextPolicyExists && $fromDate > $currentDate) {
	                $policyText = sprintf(
	                    'Cancellations made after %s to %s, would be charged %s',
	                    $fromDate->format('d M Y'),
	                    (new DateTimeImmutable($policy['ToDate']))->format('d M Y'),
	                    $amountStr
	                );
	            } else {
	                $effectiveFromDate = $fromDate > $currentDate ? $fromDate : $currentDate;
	                $policyText = sprintf(
	                    'Cancellations made after %s, or no-show, would be charged %s',
	                    $effectiveFromDate->format('d M Y'),
	                    $amountStr
	                );
	            }
	        }
	        $cancelString .= $policyText . '<br/>';
	    }

	    if (empty($cancelString)) {
	        $cancelString = 'This rate is non-refundable. If you cancel this booking you will not be refunded any of the payment.';
	    }

	    return $cancelString;
	}
	public function get_all_hotel_list(): void
    {
        $response = [
            'data' => '',
            'msg' => '',
            'status' => FAILURE_STATUS,
        ];

        $search_params = $this->input->get();
       //$limit = (int) $this->config->item('hotel_per_page_limit');

        if (
            ($search_params['op'] ?? '') == 'load' &&
            isset($search_params['search_id']) &&
            intval($search_params['search_id']) > 0 &&
            isset($search_params['booking_source'])
        ) {
            load_hotel_lib($search_params['booking_source']);

            switch ($search_params['booking_source']) {
                case PROVAB_HOTEL_BOOKING_SOURCE:
                    //$safe_search_data = $this->hotel_model->get_safe_search_data((int)$search_params['search_id']);
                    $raw_hotel_list = $this->hotel_lib->get_hotel_list(abs((int)$search_params['search_id']));

                    if (!empty($raw_hotel_list['status'])) {
                        $currency_obj = new Currency([
                            'module_type' => 'hotel',
                            'from' => get_api_data_currency(),
                            'to' => get_application_currency_preference(),
                        ]);
                        $raw_hotel_list = $this->hotel_lib->search_data_in_preferred_currency($raw_hotel_list, $currency_obj);

                        // Reset currency_obj for display currency (probably same as preferred)
                        $currency_obj = new Currency([
                            'module_type' => 'hotel',
                            'from' => get_application_currency_preference(),
                            'to' => get_application_currency_preference(),
                        ]);

                        /*$filters = (isset($search_params['filters']) && valid_array($search_params['filters']))
                            ? $search_params['filters']
                            : [];*/
                        $attr = [];
                        $attr['search_id'] = abs((int)$search_params['search_id']);
                        $hotel_search_result = [];
                        $i = 0;
                        $counter = 0;

                        // Initialize latitude and longitude boundaries
                        $max_lat = $min_lat = 0.0;
                        $max_lon = $min_lon = 0.0;

                        if (!empty($raw_hotel_list['data']['HotelSearchResult']['HotelResults'])) {
                            foreach ($raw_hotel_list['data']['HotelSearchResult']['HotelResults'] as $value) {
                                $hotel_search_result[$i] = $value;
                                $hotel_search_result[$i]['MResultToken'] = urlencode($value['ResultToken'] ?? '');

                                $lat = floatval($value['Latitude'] ?? 0);
                                $lon = floatval($value['Longitude'] ?? 0);

                                if ($lat != 0.0 && $counter < 1) {
                                    $max_lat = $min_lat = $lat;
                                }

                                if ($lon != 0.0) {
                                    $counter++;
                                    $max_lon = $min_lon = $lon;
                                }

                                $i++;
                            }

                            $raw_hotel_list['data']['HotelSearchResult']['max_lat'] = $max_lat;
                            $raw_hotel_list['data']['HotelSearchResult']['max_lon'] = $max_lon;
                        }

                        $raw_hotel_list['data']['HotelSearchResult']['HotelResults'] = $hotel_search_result;

                        $response['data'] = $raw_hotel_list['data'];
                        $response['status'] = SUCCESS_STATUS;
                    }
                    break;
            }
        }

        $this->output_compressed_data($response);
    }

    public function get_city_lists(): void
    {
        $country_id = $this->input->post('country_id', true);
        $get_resulted_data = $this->custom_db->single_table_records(
            'api_city_list',
            '*',
            ['country' => $country_id],
            0,
            100000000,
            ['destination' => 'asc']
        );

        $html = '';
        if (!empty($get_resulted_data['data'])) {
            $html .= "<option value=''>Select City</option>";
            foreach ($get_resulted_data['data'] as $city) {
                $value = htmlspecialchars($city['origin'], ENT_QUOTES | ENT_HTML5);
                $label = htmlspecialchars($city['destination'], ENT_QUOTES | ENT_HTML5);
                $html .= "<option value=\"{$value}\">{$label}</option>";
            }
        } else {
            $html = "<option value=''>No City Found</option>";
        }

        echo $html;
        exit;
    }

    public function user_traveller_details(): void
    {
        $term = trim($this->input->get('term', true));
        $result = [];

        $this->load->model('user_model');
        $traveller_details = $this->user_model->user_traveller_details($term)->result();

        foreach ($traveller_details as $traveller) {
            $result[] = [
                'category' => 'Travellers',
                'id' => $traveller->origin ?? null,
                'label' => trim(($traveller->first_name ?? '') . ' ' . ($traveller->last_name ?? '')),
                'value' => trim($traveller->first_name ?? ''),
                'first_name' => trim($traveller->first_name ?? ''),
                'last_name' => trim($traveller->last_name ?? ''),
                'date_of_birth' => isset($traveller->date_of_birth)
                    ? date('Y-m-d', strtotime(trim($traveller->date_of_birth)))
                    : null,
                'email' => trim($traveller->email ?? ''),
                'passport_user_name' => trim($traveller->passport_user_name ?? ''),
                'passport_nationality' => trim($traveller->passport_nationality ?? ''),
                'passport_expiry_day' => trim($traveller->passport_expiry_day ?? ''),
                'passport_expiry_month' => trim($traveller->passport_expiry_month ?? ''),
                'passport_expiry_year' => trim($traveller->passport_expiry_year ?? ''),
                'passport_number' => trim($traveller->passport_number ?? ''),
                'passport_issuing_country' => trim($traveller->passport_issuing_country ?? ''),
            ];
        }

        $this->output_compressed_data($result);
    }
    public function send_flight_details_mail(): void
	{
	    $params = $this->input->post();

	    // Defensive checks
	    if (empty($params['flightdetails']) || empty($params['booking_source']) || empty($params['email'])) {
	        echo json_encode(false);
	        return;
	    }

	    // Decode flight details as associative array
	    $flightDetails = json_decode($params['flightdetails'], true);
	    if (json_last_error() != JSON_ERROR_NONE) {
	        echo json_encode(false);
	        return;
	    }

	    load_flight_lib($params['booking_source']);

	    $dataAccessKeyRaw = $flightDetails['Token'] ?? null;
	    if ($dataAccessKeyRaw == null) {
	        echo json_encode(false);
	        return;
	    }

	    // unserialized_data() likely returns some structured data from token
	    $params['data_access_key'] = unserialized_data($dataAccessKeyRaw);
	    $email = $params['email'];

	    if ($params['data_access_key'] == false || $params['data_access_key'] == null) {
	        echo json_encode(false);
	        return;
	    }

	    switch ($params['booking_source']) {
	        case PROVAB_FLIGHT_BOOKING_SOURCE:
	            // Read token securely from library
	            $params['data_access_key'] = $this->flight_lib->read_token($dataAccessKeyRaw);
	            $fareDetails = $this->flight_lib->get_fare_details($params['data_access_key'], $params['search_access_key'] ?? null);

	            $fareDetails['msg'] = '<i class="fa fa-warning text-danger"></i> Fare Details Not Available';
	            if (!empty($fareDetails['status']) && $fareDetails['status'] == SUCCESS_STATUS) {
	                $fareDetails['msg'] = 'Fare Details Available';
	            }

	            $pageData = [
	                'flight_details' => $flightDetails,
	                'fare_rules' => $fareDetails,
	                'data' => []
	            ];

	            // Retrieve domain info safely with nullsafe operator
	            $domainAddress = $this->custom_db->single_table_records(
	                'domain_list',
	                'address,domain_logo,phone,domain_name,phone_code',
	                ['origin' => get_domain_auth_id()]
	            );

	            $domainData = $domainAddress['data'][0] ?? [];

	            $pageData['data']['address'] = $domainData['address'] ?? '';
	            $pageData['data']['phone'] = $domainData['phone'] ?? '';
	            $pageData['data']['phone_code'] = $domainData['phone_code'] ?? '';
	            $pageData['data']['domainname'] = $domainData['domain_name'] ?? '';
	            $pageData['data']['logo'] = $domainData['domain_logo'] ?? '';

	            $mailTemplate = $this->template->isolated_view('flight/flight_details_template', $pageData);


	            $this->load->library('provab_mailer');

	            $subject = 'Flight Details - LAR';

	            $mailStatus = $this->provab_mailer->send_mail($email, $subject, $mailTemplate);

	            echo TRUE;
	           

	        default:
	             echo FALSE;
    		}
		}
        /**
 * Hotels City Auto Suggest (AJAX)
 *
 * @return void
 */
public function get_hotel_city_list(): void
{
    $this->load->model('hotel_model');

    $term = trim(strip_tags($this->input->get('term') ?? ''));

    $data_list = $this->hotel_model->get_hotel_city_list($term);
    if (!is_array($data_list) || empty($data_list)) {
        $data_list = $this->hotel_model->get_hotel_city_list('');
    }

    $result = [];

    foreach ($data_list as $city) {
        $label = "{$city['city_name']}, {$city['country_name']}";
        $value = hotel_suggestion_value($city['city_name'], $city['country_name']);
        $category = !empty($city['top_destination']) ? 'Top cities' : 'Search Results';
        $count = (int)($city['cache_hotels_count'] ?? 0);

        $result[] = [
            'label'    => $label,
            'value'    => $value,
            'id'       => $city['origin'],
            'category' => $category,
            'type'     => $category,
            'count'    => $count
        ];
    }

    $this->output_compressed_data($result);
}
/**
 * Load hotels from different source.
 *
 * @param int $offset
 * @return void
 */
public function hotel_list(int $offset = 0): void
{
    $response = [
        'data' => '',
        'msg' => '',
        'status' => FAILURE_STATUS
    ];

    $search_params = $this->input->get() ?? [];
    $limit = (int) $this->config->item('hotel_per_page_limit');

    $op = $search_params['op'] ?? '';
    $search_id = (int)($search_params['search_id'] ?? 0);
    $booking_source = $search_params['booking_source'] ?? '';

    if ($op !== 'load' || $search_id <= 0 || empty($booking_source)) {
        $this->output_compressed_data($response);
        return;
    }

    load_hotel_lib($booking_source);

    switch ($booking_source) {
        case PROVAB_HOTEL_BOOKING_SOURCE:
            $raw_hotel_list = $this->hotel_lib->get_hotel_list($search_id);

            if (!($raw_hotel_list['status'] ?? false)) {
                break;
            }

            // Convert API currency to preferred
            $currency_obj = new Currency([
                'module_type' => 'hotel',
                'from' => get_api_data_currency(),
                'to' => get_application_currency_preference()
            ]);

            $raw_hotel_list = $this->hotel_lib->search_data_in_preferred_currency(
                $raw_hotel_list,
                $currency_obj,
                $search_id
            );

            // Use preferred currency for display
            $currency_obj = new Currency([
                'module_type' => 'hotel',
                'from' => get_application_currency_preference(),
                'to' => get_application_currency_preference()
            ]);

            $filters = valid_array($search_params['filters'] ?? []) ? $search_params['filters'] : [];

            $raw_hotel_list['data'] = $this->hotel_lib->format_search_response(
                $raw_hotel_list['data'],
                $currency_obj,
                $search_id,
                'b2b',
                $filters
            );

            $source_result_count = $raw_hotel_list['data']['source_result_count'] ?? 0;
            $filter_result_count = $raw_hotel_list['data']['filter_result_count'] ?? 0;

            if ($offset === 0) {
                $filters_summary = $this->hotel_lib->filter_summary($raw_hotel_list['data']);
                $response['filters'] = $filters_summary['data'] ?? [];
            }

            $raw_hotel_list['data'] = $this->hotel_lib->get_page_data(
                $raw_hotel_list['data'],
                $offset,
                $limit
            );

            $attr = ['search_id' => $search_id];

            $response['data'] = get_compressed_output(
                $this->template->isolated_view(
                    'hotel/tbo/tbo_search_result',
                    [
                        'currency_obj' => $currency_obj,
                        'raw_hotel_list' => $raw_hotel_list['data'],
                        'search_id' => $search_id,
                        'booking_source' => $booking_source,
                        'attr' => $attr,
                        'search_params' => $search_params // Assuming you meant $safe_search_data; if sanitization needed, apply it
                    ]
                )
            );

            $response['status'] = SUCCESS_STATUS;
            $response['total_result_count'] = $source_result_count;
            $response['filter_result_count'] = $filter_result_count;
            $response['offset'] = $offset + $limit;

            break;
    }

    $this->output_compressed_data($response);
}


}
