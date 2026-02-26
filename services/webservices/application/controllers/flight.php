<?php if (! defined ( 'BASEPATH' ))	exit ( 'No direct script access allowed' );
error_reporting(E_ALL);
ini_set('display_errors',1);
class Flight extends CI_Controller {
	//TODO: validate Client request
	
	/**
	 * 1.Validate the Request Based On Method
	 */
	private static $credential_type;//live/test
	private static $track_log_id;//Track Log ID
	private $comm_percentage = 0;//Domain Commission on Commision of Provab
	private $domain_id = 0;//domain origin
	private $course_version = FLIGHT_VERSION_2;//course version
	function __construct()
	{
              
		parent::__construct();
		$this->load->library('domain_management');
		$this->load->library('Exception_Logger', array('meta_course' => META_AIRLINE_COURSE));
		$this->load->library('flight/flight_blender');
		$this->load->model('flight_model');
		//$this->output->enable_profiler(TRUE);
	}
	public function is_valid_user(array $auth_details): array
	{
	    $UserName   = $auth_details['HTTP_X_USERNAME'] ?? null;
	    $Password   = $auth_details['HTTP_X_PASSWORD'] ?? null;
	    $DomainKey  = $auth_details['HTTP_X_DOMAINKEY'] ?? null;
	    $System     = $auth_details['HTTP_X_SYSTEM'] ?? null;
	    $SERVER_IP  = $auth_details['REMOTE_ADDR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

	    $domain_user_details = [
	        'System'      => $System,
	        'DomainKey'   => $DomainKey,
	        'UserName'    => $UserName,
	        'Password'    => $Password,
	        'SERVER_ADDR' => $SERVER_IP,
	        'Header'      => json_encode($auth_details),
	    ];

	    $this->custom_db->insert_record('customer_ip_address', $domain_user_details);

	    $course = META_AIRLINE_COURSE;
	    $domin_details = $this->domain_management->validate_domain($domain_user_details);

	    if (($domin_details['status'] ?? null) != SUCCESS_STATUS) {
	        return $domin_details;
	    }

	    $course_ver = $this->domain_management->validate_domain_course_version(
	        $course,
	        $this->course_version,
	        $domin_details['data']
	    );

	    if (($course_ver['status'] ?? null) != SUCCESS_STATUS) {
	        return $course_ver;
	    }

	    $this->comm_percentage = $this->domain_management->get_flight_commission(
	        $domin_details['data']['domain_origin']
	    );
	    $this->domain_id = (int) $domin_details['data']['domain_origin'];
	    self::$credential_type = $System;
	    self::$track_log_id = PROJECT_PREFIX . '-DOMAIN-' . $this->domain_id . '-' . time();

	    return $domin_details;
	}

	/**
	 * Return Environment Live/Test
	 */
	public static function get_credential_type()
	{
		return self::$credential_type;
	}
	/**
	 * Handles Flight Requests
	 * @param unknown_type $request_type
	 * @param unknown_type $request
	 * @param unknown_type $_header
	 */
	public function service(string $request_type): void
	{
	    $request = file_get_contents('php://input');
	    $headers_info = $_SERVER;

	    $is_valid_domain = $this->is_valid_user($headers_info);
	    $domain_origin = $is_valid_domain['data']['domain_origin'] ?? null;

	    if ($domain_origin != null) {
	        $this->total_request_count($domain_origin);
	    }


	    if ($is_valid_domain['status'] != true) {
	        $response = [
	            'status' => FAILURE_STATUS,
	            'message' => $is_valid_domain['message'],
	        ];
	        $this->send_response($request_type, $request, $response);
	        return;
	    }

	    $this->load->model('api_model');
	    $request = json_decode($request, true);
	    $module_type = 'FLIGHT';

	    $this->api_model->store_client_request($request_type, $request, $module_type);
	    //$cache_track_log_flag = $this->api_model->inactive_client_cache_services($request_type);

	    $response = match ($request_type) {
	        'Search' => $this->Search($request),
	        'FareRule' => $this->FareRule($request),
	        'MiniFareRule' => $this->MiniFareRule($request),
	        'UpdateFareQuote' => $this->UpdateFareQuote($request),
	        'UpsellFare' => $this->UpsellFare($request),
	        'ExtraServices' => $this->ExtraServices($request),
	        'HoldTicket' => $this->HoldTicket($request),
	        'CommitBooking' => $this->CommitBooking($request),
	        'IssueHoldTicket' => $this->IssueHoldTicket($request),
	        'ConfirmHoldTicket' => $this->ConfirmHoldTicket($request),
	        'CancelBooking' => $this->CancelBooking($request),
	        'GetCalendarFare' => $this->GetCalendarFare($request),
	        'UpdateCalendarFareOfDay' => $this->UpdateCalendarFareOfDay($request),
	        'BookingDetails' => $this->BookingDetails($request),
	        'TicketRefundDetails' => $this->TicketRefundDetails($request),
	        'UpdateFlightApi' => $this->UpdateFlightApi($request),
	        'ProcessTicket' => $this->ProcessTicket($request),
	        default => ['status' => FAILURE_STATUS, 'message' => 'Invalid Service'],
	    };

	    /*if ($cache_track_log_flag == false) {
	        $track_log_comments = json_encode([$request_type . ' - Completed - Flight', $response]);
	        // $this->domain_management_model->create_track_log(self::$track_log_id, $track_log_comments);
	    }*/

	    $this->send_response($request_type, $request, $response);
	}
	private function send_response(string $request_type, mixed $request, array $response): void
	{
	    $data = [
	        'Status' => $response['status'] ?? FAILURE_STATUS,
	        'Message' => $response['message'] ?? 'Invalid Service',
	    ];

	    if (!empty($response['data'])) {
	        $data[$request_type] = $response['data'];
	    }

	    if (in_array($request_type, [
	        'HoldTicket', 'CommitBooking', 'IssueHoldTicket', 'CancelBooking', 'BookingDetails'
	    ])) {
	        $log_data = [
	            'request_type' => $request_type,
	            'request' => json_encode($request),
	            'response' => json_encode($data),
	            'created_datetime' => date('Y-m-d H:i:s'),
	            'domain_origin' => get_domain_auth_id(),
	        ];
	        $this->custom_db->insert_record('provab_api_return_response_history', $log_data);
	    }

	    output_service_json_data($data);
	}

	/**
	 * Returns Flight List
	 * 
	 */
	public function Search(array $request): array
	{
	    $data = [
	        'status' => FAILURE_STATUS,
	        'message' => '',
	        'data' => []
	    ];

	    $save_search_data = $this->flight_model->save_search_data($request);

	    $search_type = META_AIRLINE_COURSE;
	    $total_pax = ($request['AdultCount'] ?? 0) + ($request['ChildCount'] ?? 0) + ($request['InfantCount'] ?? 0);

	    $trip_type = match ($request['JourneyType'] ?? '') {
	        'OneWay' => 'oneway',
	        'Return' => 'roundway',
	        'Multicity' => 'multistop',
	        default => '',
	    };

	    $segments = $request['Segments'] ?? [];
	    if (empty($segments)) {
	        $data['message'] = 'Missing segment data';
	        return $data;
	    }

	    $seg_count = count($segments);
	    $from_code = $segments[0]['Origin'] ?? '';
	    $journey_date = $segments[0]['DepartureDate'] ?? '';

	    $to_code = match ($trip_type) {
	        'oneway', 'roundway' => $segments[0]['Destination'] ?? '',
	        default => $segments[$seg_count - 1]['Destination'] ?? '',
	    };

	    /*$return_date = match ($trip_type) {
	        'oneway', 'roundway' => $segments[0]['ReturnDate'] ?? null,
	        default => $segments[$seg_count - 1]['DepartureDate'] ?? null,
	    };*/

	    $from_location = $this->flight_model->get_airport_city_name($from_code)?->airport_city ?? '';
	    $to_location = $this->flight_model->get_airport_city_name($to_code)?->airport_city ?? '';

	    $this->custom_db->insert_record('search_flight_history', [
	        'domain_origin' => get_domain_auth_id(),
	        'search_type' => $search_type,
	        'from_location' => $from_location,
	        'to_location' => $to_location,
	        'from_code' => $from_code,
	        'to_code' => $to_code,
	        'trip_type' => $trip_type,
	        'pnr' => $request['PNR'] ?? '',
	        'booking_id' => $request['BookingId'] ?? '',
	        'journey_date' => $journey_date,
	        'total_pax' => $total_pax,
	        'created_by_id' => '0',
	        'created_datetime' => date('Y-m-d H:i:s'),
	    ]);

	    if (empty($save_search_data['status'])) {
	        $data['message'] = 'Invalid Search Request';
	        return $data;
	    }

	    $search_id = $save_search_data['search_id'] ?? '';
	    $cache_key = $save_search_data['cache_key'] ?? '';
	    $flight_list = $this->flight_blender->flight_list($search_id, $cache_key);

	    if (!empty($flight_list['status']) && $flight_list['status'] == SUCCESS_STATUS) {
	        $data['status'] = SUCCESS_STATUS;
	        $data['data'] = $flight_list['data'] ?? [];
	        return $data;
	    }

	    $data['message'] = $flight_list['message'] ?? 'Flight search failed';
	    return $data;
	}

	/**
	 * Returns Fare Rules
	 * 
	 */
	public function FareRule(array $request): array
	{
	    $data = [
	        'status' => FAILURE_STATUS,
	        'message' => '',
	        'data' => [],
	    ];

	    if (empty($request) || !is_array($request)) {
	        $data['message'] = 'Invalid FareRule Request';
	        return $data;
	    }

	    $result_token = trim($request['ResultToken'] ?? '');
	    if ($result_token == '') {
	        $data['message'] = 'Missing ResultToken';
	        return $data;
	    }

	    $search_id_cache_key = $this->get_search_id_cache_key($result_token);
	    if (empty($search_id_cache_key['status'])) {
	        $data['message'] = 'Search ID not found';
	        return $data;
	    }

	    $search_id = $search_id_cache_key['search_id'] ?? '';
	    $fare_rule_list = $this->flight_blender->fare_rules($request, $search_id);

	    if (!empty($fare_rule_list['status']) && $fare_rule_list['status'] == SUCCESS_STATUS) {
	        $data['status'] = SUCCESS_STATUS;
	        $data['data'] = $fare_rule_list['data'] ?? [];
	        return $data;
	    }

	    $data['message'] = $fare_rule_list['message'] ?? 'Unable to fetch fare rules';
	    return $data;
	}

	/**
	 * Returns Fare Rules
	 * 
	 */
	public function MiniFareRule(array $request): array
	{
	    $data = [
	        'status' => FAILURE_STATUS,
	        'message' => '',
	        'data' => [],
	    ];

	    if (empty($request)) {
	        $data['message'] = 'Invalid FareRule Request';
	        return $data;
	    }

	    $result_token = trim($request['ResultToken'] ?? '');
	    if ($result_token == '') {
	        $data['message'] = 'Missing ResultToken';
	        return $data;
	    }

	    $search_id_cache_key = $this->get_search_id_cache_key($result_token);
	    if (empty($search_id_cache_key['status'])) {
	        $data['message'] = 'Search ID not found';
	        return $data;
	    }

	    $search_id = $search_id_cache_key['search_id'] ?? '';
	    $fare_rule_list = $this->flight_blender->mini_fare_rules($request, $search_id);

	    if (!empty($fare_rule_list['status']) && $fare_rule_list['status'] == SUCCESS_STATUS) {
	        return [
	            'status' => SUCCESS_STATUS,
	            'message' => '',
	            'data' => $fare_rule_list['data'] ?? [],
	        ];
	    }

	    $data['message'] = $fare_rule_list['message'] ?? 'Unable to fetch mini fare rules';
	    return $data;
	}
	/**
	 * Returns Updated Fare
	 * 
	 */
	public function UpdateFareQuote(array $request): array
	{
	    $data = [
	        'status' => FAILURE_STATUS,
	        'message' => '',
	        'data' => [],
	    ];

	    $result_token = $request['ResultToken'] ?? null;
	    $token = '';

	    if (is_array($result_token) && count($result_token) > 1) {
	        $token = trim($result_token[0] ?? '');
	    }

	    if (empty($token)) {
	        $token = trim(is_array($result_token) ? ($result_token[0] ?? '') : $result_token);
	        unset($request['ResultToken']);
	        $request['ResultToken'][0] = $token;
	    }

	    if (empty($token)) {
	        $data['message'] = 'Missing or invalid ResultToken';
	        return $data;
	    }

	    $search_id_cache_key = $this->get_search_id_cache_key($token);

	    if (empty($search_id_cache_key['status'])) {
	        $data['message'] = 'Invalid UpdateFareQuote Request';
	        return $data;
	    }

	    $search_id = $search_id_cache_key['search_id'] ?? '';
	    $cache_key = $search_id_cache_key['cache_key'] ?? '';

	    $updated_fare_quote = $this->flight_blender->update_fare_quote($request, $search_id, $cache_key);

	    if (!empty($updated_fare_quote['status']) && $updated_fare_quote['status'] == SUCCESS_STATUS) {
	        return [
	            'status' => SUCCESS_STATUS,
	            'message' => '',
	            'data' => $updated_fare_quote['data'] ?? [],
	        ];
	    }

	    $data['message'] = $updated_fare_quote['message'] ?? 'Update failed';
	    return $data;
	}


	/**
	 * Returns ExtraServices
	 * 
	 */
	public function ExtraServices(array $request): array
	{
	    $data = [
	        'status' => FAILURE_STATUS,
	        'message' => '',
	        'data' => [],
	    ];

	    $result_token = trim($request['ResultToken'] ?? '');
	    if ($result_token == '') {
	        $data['message'] = 'Missing ResultToken';
	        return $data;
	    }

	    $search_id_cache_key = $this->get_search_id_cache_key($result_token);
	    if (empty($search_id_cache_key['status'])) {
	        $data['message'] = 'Invalid ExtraServices Request';
	        return $data;
	    }

	    $search_id = $search_id_cache_key['search_id'] ?? '';
	    $cache_key = $search_id_cache_key['cache_key'] ?? '';

	    $extra_services = $this->flight_blender->get_extra_services($request, $search_id, $cache_key);

	    if (!empty($extra_services['status']) && $extra_services['status'] == SUCCESS_STATUS) {
	        return [
	            'status' => SUCCESS_STATUS,
	            'message' => '',
	            'data' => $extra_services['data'] ?? [],
	        ];
	    }

	    $data['message'] = $extra_services['message'] ?? 'Failed to retrieve extra services';
	    return $data;
	}
	/**
	 * Process the Booking
	 * 
	 */
	public function CommitBooking(array $request): array
	{
	    $data = [
	        'status' => FAILURE_STATUS,
	        'message' => '',
	        'data' => [],
	    ];

	    $result_token = trim($request['ResultToken'] ?? '');
	    if ($result_token == '') {
	        $data['message'] = 'Missing ResultToken';
	        return $data;
	    }

	    $search_id_cache_key = $this->get_search_id_cache_key($result_token);
	    if (empty($search_id_cache_key['status'])) {
	        $data['message'] = 'Invalid CommitBooking Request or Session Expired';
	        return $data;
	    }

	    $search_id = $search_id_cache_key['search_id'] ?? '';
	    $cache_key = $search_id_cache_key['cache_key'] ?? '';

	    $commit_book_response = $this->flight_blender->process_booking($request, $search_id, $cache_key);

	    return $commit_book_response ?? $data;
	}

	/**
	 * Hold the Ticket
	 * 
	 */
	public function HoldTicket(array $request): array
	{
	    $data = [
	        'status' => FAILURE_STATUS,
	        'message' => '',
	        'data' => [],
	    ];

	    $result_token = trim($request['ResultToken'] ?? '');
	    if ($result_token == '') {
	        $data['message'] = 'Missing ResultToken';
	        return $data;
	    }

	    $search_id_cache_key = $this->get_search_id_cache_key($result_token);
	    if (empty($search_id_cache_key['status'])) {
	        $data['message'] = 'Invalid HoldTicket Request';
	        return $data;
	    }

	    $search_id = $search_id_cache_key['search_id'] ?? '';
	    $cache_key = $search_id_cache_key['cache_key'] ?? '';

	    $hold_ticket_response = $this->flight_blender->hold_ticket($request, $search_id, $cache_key);

	    return $hold_ticket_response ?? $data;
	}

	/**
	 * IssueHoldTicket
	 * 
	 */
	public function IssueHoldTicket(array $request): array
	{
	    $data = [
	        'status' => FAILURE_STATUS,
	        'message' => '',
	        'data' => [],
	    ];

	    if (!valid_array($request)) {
	        $data['message'] = 'Invalid IssueHoldTicket Request';
	        return $data;
	    }

	    $hold_ticket = $this->flight_blender->issue_hold_ticket($request);

	    $data['status'] = $hold_ticket['status'] ?? FAILURE_STATUS;
	    $data['message'] = $hold_ticket['message'] ?? 'Error processing request';

	    return $data;
	}

	/**
	 * IssueHoldTicket
	 * 
	 */
	public function ConfirmHoldTicket(array $request): array
	{
	    if (!valid_array($request)) {
	        return [
	            'status' => FAILURE_STATUS,
	            'message' => 'Invalid ConfirmHoldTicket Request',
	            'data' => [],
	        ];
	    }

	    return $this->flight_blender->confirm_hold_ticket($request);
	}

	
	/**
	 * Get the Booking Details
	 * 
	 */
	public function BookingDetails(array $request): array
	{
	    if (empty($request['AppReference'])) {
	        return [
	            'status' => FAILURE_STATUS,
	            'message' => 'Invalid BookingDetails Request',
	            'data' => [],
	        ];
	    }

	    $response = $this->flight_blender->booking_details($request);

	    return [
	        'status' => $response['status'] ?? FAILURE_STATUS,
	        'message' => $response['message'] ?? '',
	        'data' => $response['status'] == SUCCESS_STATUS ? ($response['data'] ?? []) : [],
	    ];
	}

	/**
	 * Process Cancel Booking
	 * 
	 */
	public function CancelBooking(array $request): array
    {
        if (!valid_array($request)) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid CancelBooking Request',
                'data' => [],
            ];
        }

        return $this->flight_blender->cancel_booking($request);
    }

    private function get_search_id_cache_key(string $ResultToken): array
    {
        $cache_key = $this->redis_server->extract_cache_key($ResultToken);
        $search_history = $this->custom_db->single_table_records('search_history', '*', ['cache_key' => trim($cache_key)]);

        if (
            $search_history['status'] == SUCCESS_STATUS &&
            !empty($search_history['data'][0])
        ) {
            return [
                'status' => SUCCESS_STATUS,
                'search_id' => $search_history['data'][0]['origin'],
                'cache_key' => trim($search_history['data'][0]['cache_key']),
            ];
        }

        return ['status' => FAILURE_STATUS];
    }

    public function GetCalendarFare(array $request): array
    {
        if (!valid_array($request)) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid GetCalendarFare Request',
                'data' => [],
            ];
        }

        $calendar_fare = $this->flight_blender->calendar_fare($request);

        return [
            'status' => $calendar_fare['status'],
            'message' => $calendar_fare['message'] ?? '',
            'data' => $calendar_fare['status'] == SUCCESS_STATUS ? $calendar_fare['data'] : [],
        ];
    }

    public function UpdateCalendarFareOfDay(array $request): array
    {
        if (!valid_array($request)) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid UpdateCalendarFareOfDay Request',
                'data' => [],
            ];
        }

        $calendar_fare = $this->flight_blender->update_calendar_fare($request);

        return [
            'status' => $calendar_fare['status'],
            'message' => $calendar_fare['message'] ?? '',
            'data' => $calendar_fare['status'] == SUCCESS_STATUS ? $calendar_fare['data'] : [],
        ];
    }

    private function TicketRefundDetails(array $request): array
    {
        $data = ['status' => FAILURE_STATUS, 'message' => ''];

        $booking_details = $this->flight_model->get_passenger_ticket_info(
            $request['AppReference'],
            $request['SequenceNumber'],
            $request['BookingId'],
            $request['PNR'],
            $request['TicketId']
        );

        if (!empty($booking_details['status'])) {
            $details = $booking_details['data'];
            $booking = $details['booking_details'][0];
            $customer = $details['booking_customer_details'][0];
            $cancel = $details['cancellation_details'][0];

            $rate = $cancel['currency_conversion_rate'];

            $data['status'] = SUCCESS_STATUS;
            $data['data']['RefundDetails'] = [
                'AppReference' => $booking['app_reference'],
                'TicketId' => $customer['TicketId'],
                'ChangeRequestId' => $cancel['RequestId'],
                'ChangeRequestStatus' => $cancel['ChangeRequestStatus'],
                'StatusDescription' => $cancel['statusDescription'],
                'RefundStatus' => $cancel['refund_status'],
                'RefundedAmount' => $cancel['refund_amount'] * $rate,
                'CancellationCharge' => $cancel['cancellation_charge'] * $rate,
                'ServiceTaxOnRefundAmount' => $cancel['service_tax_on_refund_amount'] * $rate,
                'SwachhBharatCess' => $cancel['swachh_bharat_cess'] * $rate,
            ];
        }

        return $data;
    }

    public function search_data(int $search_id): array
    {
        $clean = $this->flight_model->get_safe_search_data($search_id);
        if (!$clean['status']) {
            return ['status' => false, 'data' => []];
        }

        $data = $clean['data'];
        $response = [
            'status' => true,
            'data' => array_merge($data, [
                'type' => match ($data['trip_type']) {
                    'oneway' => 'OneWay',
                    'circle' => 'Return',
                    default => 'OneWay',
                },
                'from' => substr(chop(substr($data['from'], -5), ')'), -3),
                'to' => substr(chop(substr($data['to'], -5), ')'), -3),
                'depature' => date('Y-m-d', strtotime($data['depature'])) . 'T00:00:00',
                'return' => isset($data['return']) ? date('Y-m-d', strtotime($data['return'])) . 'T00:00:00' : null,
                'domestic_round_trip' => ($data['is_domestic'] && $data['trip_type'] == 'return'),
                'adult' => $data['adult_config'],
                'child' => $data['child_config'],
                'infant' => $data['infant_config'],
                'v_class' => $data['v_class'] ?? null,
                'carrier' => implode($data['carrier']),
            ])
        ];

        return $response;
    }

    public function total_request_count(int $domain_origin): void
    {
        $user_count = $this->flight_model->get_user_count_per_day_per_user($domain_origin);
        $limit_domain = $this->flight_model->get_allowed_limit_domain($domain_origin);
        $limit_global = $this->flight_model->get_allowed_limit();

        $limit = $limit_domain['limit_request'] ?: $limit_global['limit_request'];
        $count = $user_count[0]['totalcount'] ?? 0;

        if ($count > $limit) {
            $this->custom_db->update_record('domain_list', ['status' => 0], ['origin' => $domain_origin]);
        }
    }
  
}
