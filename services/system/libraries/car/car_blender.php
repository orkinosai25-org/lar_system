<?php
declare(strict_types=1);
/**
 * Combines the Data from multiple API's
 * @package    Provab Application
 * @subpackage Car Model
 * @author     Badri Nath Nayak 
 * @version    V1
 *
 */
Class car_Blender {

    function __construct() {
        $this->CI = &get_instance();
        $this->CI->load->library('multi_curl');
        $this->CI->load->library('car/Common_car');
    }

    /**
     * Assigns the Curl Parameters (URL, Header info, Request)
     */
    public function assign_curl_params(
        array $requestParams,
        array &$curlRequest,
        array &$curlUrl,
        array &$curlHeader,
        array &$curlBookingSource,
        array &$curlRemarks
    ): void {
        $curlRequest[]       = $requestParams['request'];
        $curlUrl[]           = $requestParams['url'];
        $curlHeader[]        = $requestParams['header'];
        $curlBookingSource[] = $requestParams['booking_source'];
        $curlRemarks[]       = trim($requestParams['remarks'] ?? '');
    }

    /**
     * Car Active Booking Sources
     */
    private function car_active_booking_sources(array $condition = []): array {
        $activeConditions = [
            ['BS.meta_course_list_id', '=', '"' . META_CAR_COURSE . '"'],
            ['DL.origin', '=', get_domain_auth_id()],
            ...$condition,
        ];

        return $this->CI->db_cache_api->get_active_api_booking_source($activeConditions);
    }

    /**
     * Authenticate the API
     */
    private function api_authentication(array $activeBookingSources = []): void {
        $curlRequest        = [];
        $curlUrl            = [];
        $curlHeader         = [];
        $curlBookingSource  = [];
        $curlRemarks        = [];

        $carBookingSources = !empty($activeBookingSources)
            ? $activeBookingSources
            : $this->carActiveBookingSources();

        $carObjects = [];

        // debug($carBookingSources); exit; // Comment out or replace with logging

        foreach ($carBookingSources as $key => $source) {
            $carObject = load_car_lib($source['booking_source'], '', true);
            $carObjects[$key] = $carObject;

            $authRequest = $this->CI->$carObject->get_authentication_request();

            if (empty($this->CI->$carObject->api_session_id) && ($authRequest['status'] ?? '') == SUCCESS_STATUS) {
                $authRequest['data']['remarks'] = $this->CI->$carObject->booking_source_name;
                $this->assign_curl_params(
                    $authRequest['data'],
                    $curlRequest,
                    $curlUrl,
                    $curlHeader,
                    $curlBookingSource,
                    $curlRemarks
                );
            }
        }

        $curlParams = [
            'booking_source' => $curlBookingSource,
            'request'        => $curlRequest,
            'url'            => $curlUrl,
            'header'         => $curlHeader,
            'remarks'        => $curlRemarks,
        ];

        $authResults = $this->CI->multi_curl->execute_multi_curl($curlParams);

        foreach ($carObjects as $key => $objName) {
            $bookingSource = $this->CI->$objName->booking_source;
            if (isset($authResults[$bookingSource])) {
                $this->CI->$objName->set_api_session_id($authResults[$bookingSource]);
            }
        }
    }
   /**
 * Returns car list.
 */
    public function car_list(int $searchId, string $cacheKey): array
    {
        $curlRequest = [];
        $curlUrl = [];
        $curlHeader = [];
        $curlBookingSource = [];
        $curlRemarks = [];

        $formattedSearchResult = [];
        $finalCarList = ['status' => FAILURE_STATUS];

        $carActiveBookingSources = $this->car_active_booking_sources();
        $carObjects = [];

        foreach ($carActiveBookingSources as $source) {
            $carObjRef = load_car_lib($source['booking_source'], '', true);
            $carObjects[$source['booking_source']] = $carObjRef;
            $searchRequest = $this->CI->$carObjRef->get_search_request($searchId);

            if (($searchRequest['status'] ?? '') == SUCCESS_STATUS) {
                $searchRequest['data']['remarks'] = $carObjRef;
                $this->assign_curl_params(
                    $searchRequest['data'],
                    $curlRequest,
                    $curlUrl,
                    $curlHeader,
                    $curlBookingSource,
                    $curlRemarks
                );
            }
        }

        $curlParams = [
            'booking_source' => $curlBookingSource,
            'request'        => $curlRequest,
            'url'            => $curlUrl,
            'header'         => $curlHeader,
            'remarks'        => $curlRemarks,
        ];

        //$searchResults = $this->CI->multi_curl->execute_multi_curl_car($curlParams);
        $searchResults['PTBSID0000000017'] = file_get_contents(FCPATH."car_nect/search_response.xml");
       
        foreach ($carObjects as $bsKey => $objRef) {

            if (!isset($searchResults[$bsKey])) {
                continue;
            }
            $carData = $this->CI->$objRef->get_car_list($searchResults[$bsKey], $searchId);
            if (
                ($carData['status'] ?? '') == SUCCESS_STATUS &&
                !empty($carData['data']['CarSearchResult']['CarResults'])
            ) {
                if (empty($formattedSearchResult)) {
                    $formattedSearchResult = $carData;

                } else {
                    $formattedSearchResult['data']['CarSearchResult']['CarResults'] = array_merge(
                        $formattedSearchResult['data']['CarSearchResult']['CarResults'],
                        $carData['data']['CarSearchResult']['CarResults']
                    );
                    $formattedSearchResult['status'] = SUCCESS_STATUS;
                }
            }
        }

        if (($formattedSearchResult['status'] ?? '') == SUCCESS_STATUS) {
            $this->CI->load->library('car/common_car');

            $carResults = $formattedSearchResult['data']['CarSearchResult']['CarResults'];
            $updatedResults = $this->CI->common_car->update_markup_and_insert_cache_key_to_token(
                $carResults,
                $cacheKey,
                $searchId
            );

            $formattedSearchResult['data']['CarSearchResult']['CarResults'] = $updatedResults;

            return $formattedSearchResult;
        }

        $finalCarList['message'] = 'No Cars Found';
        return $finalCarList;
    }

    /**
     * Car Rate Rules
     */
    public function car_rate_rules(array $request, int $searchId, string $cacheKey): array
    {
        $carRateRules = [
            'data' => [],
            'status' => FAILURE_STATUS,
            'message' => ''
        ];

        $resultToken = trim($request['ResultToken'] ?? '');

        if (empty($resultToken)) {
            $carRateRules['message'] = 'Missing ResultToken';
            return $carRateRules;
        }

        $carSearchData = Common_Car::read_record($resultToken);

        if (!valid_array($carSearchData)) {
            $carRateRules['message'] = 'Invalid CarRules Request';
            return $carRateRules;
        }

        $carSearchDataDecoded = json_decode($carSearchData[0], true);
        $carRuleRequest = unserialized_data($carSearchDataDecoded['ResultToken']);

        if (!isset($carRuleRequest['booking_source'], $carRuleRequest['ID_Context'], $carRuleRequest['Type'])) {
            $carRateRules['message'] = 'Invalid CarRules Request';
            return $carRateRules;
        }

        $bookingSource = $carRuleRequest['booking_source'];

        $carObjRef = load_car_lib($bookingSource);
        $carRulesData = $this->CI->$carObjRef->get_car_rules($carRuleRequest, $searchId);

        if (($carRulesData['status'] ?? '') != SUCCESS_STATUS) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Car Rules Data not found',
                'data' => []
            ];
        }

        $this->CI->load->library('car/common_car');
        $carRulesData['data']['CarRuleResult'] = $this->CI->common_car->update_markup_and_insert_cache_key_to_token(
            $carRulesData['data']['CarRuleResult'],
            $cacheKey,
            $searchId
        );

        return $carRulesData;
    }
     public function process_booking(array $request, int $search_id, string $cache_key): array
    {
        $booking_response = [
            'data' => [],
            'status' => FAILURE_STATUS,
            'message' => ''
        ];

        // 1. Validate booking parameters
        $validate_booking_params = $this->validate_booking_params($request);
        if ($validate_booking_params['status'] == FAILURE_STATUS) {
            $booking_response['message'] = $validate_booking_params['message'];
            return $booking_response;
        }

        $ResultToken = trim($request['ResultToken'] ?? '');

        $car_data = Common_Car::read_record($ResultToken);
        if (!valid_array($car_data)) {
            $booking_response['message'] = 'Invalid CommitBooking Request';
            return $booking_response;
        }

        $car_data = json_decode($car_data[0], true);
        $token_array = array_values(unserialized_data($car_data['ResultToken'] ?? ''));
        $ResultTokenData = $token_array[0] ?? [];
        $booking_source = $ResultTokenData['booking_source'] ?? '';

        $car_price_details = $this->CI->common_car->final_booking_transaction_fare_details(
            $car_data['TotalCharge'],
            $search_id,
            $booking_source
        );

        $booking_transaction_amount = $car_price_details['Price']['client_buying_price'] ?? 0;

        // Check Domain Balance
        $domain_balance = $this->CI->domain_management->verify_domain_balance(
            $booking_transaction_amount,
            Car::get_credential_type()
        );

        if ($domain_balance != SUCCESS_STATUS) {
            $booking_response['message'] = 'Insufficient Balance';
            return $booking_response;
        }

        // Save car details
        $passenger_details = $request['Passengers'] ?? [];
        $app_reference = $request['AppReference'] ?? '';

        $save_car_booking = $this->CI->common_car->save_car_booking(
            $car_data,
            $passenger_details,
            $car_price_details,
            $app_reference,
            $booking_source,
            $search_id
        );

        if ($save_car_booking['status'] != SUCCESS_STATUS) {
            $booking_response['message'] = $save_car_booking['message'];
            return $booking_response;
        }

        $book_req_params = [
            'ResultToken' => $ResultTokenData,
            'Passengers' => $passenger_details,
            'car_data' => $car_data
        ];

        $car_obj_ref = load_car_lib($booking_source);

        $process_booking_response = $this->CI->$car_obj_ref->process_booking(
            $book_req_params,
            $app_reference,
            0,
            $search_id
        );

        if ($process_booking_response['status'] == SUCCESS_STATUS) {
            $car_booking_details = $this->CI->common_car->get_car_booking_transaction_details($app_reference);
            return $car_booking_details;
        }

        $booking_response['message'] = $process_booking_response['message'] ?? 'Booking failed';
        return $booking_response;
    }

    private function validate_booking_params(array $request): array
    {
        $AppReference = trim($request['AppReference'] ?? '');

        if (empty($AppReference) || strlen($AppReference) > 40 || strlen($AppReference) < 10) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'AppReference should be between 10 to 40 in length'
            ];
        }

        if (!isset($request['Passengers']) || !valid_array($request['Passengers'])) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Passengers information is Required'
            ];
        }

        // You can add further passenger validation here

        return [
            'status' => SUCCESS_STATUS,
            'message' => ''
        ];
    }
     public function cancel_booking(array $request): array
    {
        $response = [
            'data' => [],
            'status' => FAILURE_STATUS,
            'message' => ''
        ];

        if (!valid_array($request)) {
            $response['message'] = 'Invalid CancelBooking Request';
            return $response;
        }

        $app_reference = trim($request['AppReference'] ?? '');

        $car_booking_details = $this->CI->custom_db->single_table_records(
            'car_booking_details',
            '*',
            ['app_reference' => $app_reference]
        );

        if (
            $car_booking_details['status'] != SUCCESS_STATUS ||
            empty($car_booking_details['data'][0])
        ) {
            $response['message'] = 'Invalid AppReference';
            return $response;
        }

        $booking_transaction = $car_booking_details['data'][0];
        $booking_status = $booking_transaction['status'] ?? '';
        $booking_source = $booking_transaction['booking_source'] ?? '';

        $car_obj_ref = load_car_lib($booking_source);
        $cancel_booking_details = $this->CI->$car_obj_ref->cancel_booking($request);

        if ($cancel_booking_details['status'] == SUCCESS_STATUS) {
            $response = $cancel_booking_details;
        } else {
            $response['message'] = $cancel_booking_details['message'] ?? 'Cancellation failed';
        }

        if ($booking_status == 'BOOKING_CANCELLED') {
            $response['message'] = 'Booking Already Cancelled';
        }

        return $response;
    }

}
