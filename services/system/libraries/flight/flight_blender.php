<?php

//TODO: validate Client request
/**
 * Combines the Data from multiple API's
 * @author Jaganath
 *
 */
Class Flight_Blender {
var $search_hash; //search
    function __construct() {
        $this->CI = &get_instance();
        $this->CI->load->library('multi_curl');
        $this->CI->load->library('flight/common_flight');
    }

    /**
     * Assigns the Curl Parameters(URL,Header info.,Request)
     * @param unknown_type $request_params
     * @param unknown_type $curl_request
     * @param unknown_type $curl_url
     * @param unknown_type $curl_header
     * @param unknown_type $curl_booking_source
     */
   public function assign_curl_params(
        array $requestParams,
        array &$curlRequest,
        array &$curlUrl,
        array &$curlHeader,
        array &$curlBookingSource,
        array &$curlRemarks
    ): void {
        $request = [$requestParams['request']];
        $url = [$requestParams['url']];
        $header = [$requestParams['header']];
        $bookingSource = [$requestParams['booking_source']];
        $remarks = [trim($requestParams['remarks'] ?? '')];

        $curlRequest = array_merge($curlRequest, $request);
        $curlUrl = array_merge($curlUrl, $url);
        $curlHeader = array_merge($curlHeader, $header);
        $curlBookingSource = array_merge($curlBookingSource, $bookingSource);
        $curlRemarks = array_merge($curlRemarks, $remarks);
    }

    /**
     * Get active flight booking sources
     */
    private function flight_active_booking_sources(array $condition = []): array
    {
        $activeBookingSourceCondition = [
            ['BS.meta_course_list_id', '=', '"' . META_AIRLINE_COURSE . '"'],
            ['DL.origin', '=', get_domain_auth_id()]
        ];

        $activeBookingSourceCondition = array_merge($activeBookingSourceCondition, $condition);

        return $this->CI->db_cache_api->get_active_api_booking_source($activeBookingSourceCondition);
    }

    /**
     * Authenticate API session for flight booking sources
     */
     private function api_authentication(array $activeBookingSources = []): void
    {
        $curlParams = [
            'booking_source' => [],
            'request' => [],
            'url' => [],
            'header' => [],
            'remarks' => [],
        ];

        $flightActiveBookingSources = !empty($activeBookingSources)
            ? $activeBookingSources
            : $this->flightActiveBookingSources();

        $flightObjects = [];

        foreach ($flightActiveBookingSources as $bsKey => $bsVal) {
            if (($bsVal['check_auth'] ?? 0) == 1) {
                $flightObject = load_flight_lib($bsVal['booking_source'], '', true);
                $flightObjects[$bsKey] = $flightObject;

                $authRequest = $this->CI->{$flightObject}->get_authentication_request();

                if (empty($this->CI->{$flightObject}->api_session_id) && ($authRequest['status'] ?? '') === SUCCESS_STATUS) {
                    $authRequest['data']['remarks'] = $this->CI->{$flightObject}->booking_source_name;
                    $this->assignCurlParams(
                        $authRequest['data'],
                        $curlParams['request'],
                        $curlParams['url'],
                        $curlParams['header'],
                        $curlParams['booking_source'],
                        $curlParams['remarks']
                    );
                }
            }
        }

        $authResults = $this->CI->multi_curl->execute_multi_curl($curlParams);

        foreach ($flightObjects as $objKey => $objVal) {
            $bookingSource = $this->CI->{$objVal}->booking_source;
            if (!empty($authResults[$bookingSource])) {
                $this->CI->{$objVal}->set_api_session_id($authResults[$bookingSource]);
            }
        }
    }
    /**
     * Returns flight list
     * @param int $search_id
     */
     public function flight_list(int $search_id, string $cache_key): array
    {
        $curl_params = $curl_request = $curl_url = $curl_header = $curl_booking_source = $curl_remarks = [];

        $seach_result = $formatted_seach_result = [];
        $final_flight_list = ['status' => FAILURE_STATUS];

        $this->CI->load->driver('cache');
        $search_data = $this->search_data($search_id);
        $cache_search = false;
        $search_hash = $this->search_hash;

        if ($cache_search) {
            $cache_contents = $this->CI->cache->file->get($search_hash);
        }

        if (!$cache_search || ($cache_search && empty($cache_contents))) {
            $flight_active_booking_sources = $this->flight_active_booking_sources();
            $flight_obj = [];

            foreach ($flight_active_booking_sources as $bs_v) {
                $flight_obj_ref = load_flight_lib($bs_v['booking_source'], '', true);
                $flight_obj[$bs_v['booking_source']] = $flight_obj_ref;
                $search_request = $this->CI->{$flight_obj_ref}->get_search_request($search_id);

                if (($search_request['status'] ?? '') === SUCCESS_STATUS) {
                    $search_request['data']['remarks'] = $flight_obj_ref;
                    $this->assign_curl_params(
                        $search_request['data'],
                        $curl_request,
                        $curl_url,
                        $curl_header,
                        $curl_booking_source,
                        $curl_remarks
                    );
                }
            }

            $curl_params = [
                'booking_source' => $curl_booking_source,
                'request'        => $curl_request,
                'url'            => $curl_url,
                'header'         => $curl_header,
                'remarks'        => $curl_remarks,
                'search_id'      => $search_id,
            ];

            $curl_params1 = $curl_params;
            $seq_key = 0;

            foreach ($curl_params1['booking_source'] as $key => $details) {
                if ($details === AMADEUS_FLIGHT_BOOKING_SOURCE) {
                    foreach ($curl_params1['request'][$key] as $request) {
                        $curl_params['booking_source'][$seq_key] = $details;
                        $curl_params['request'][$seq_key] = $request;
                        $curl_params['url'][$seq_key] = $curl_params1['url'][$key];
                        $curl_params['header'][$seq_key] = $curl_params1['header'][$key];
                        $curl_params['remarks'][$seq_key] = $curl_params1['remarks'][$key];
                        $seq_key++;
                    }
                } else {
                    $curl_params['booking_source'][$seq_key] = $details;
                    $curl_params['request'][$seq_key] = $curl_params1['request'][$key];
                    $curl_params['url'][$seq_key] = $curl_params1['url'][$key];
                    $curl_params['header'][$seq_key] = $curl_params1['header'][$key];
                    $curl_params['remarks'][$seq_key] = $curl_params1['remarks'][$key];
                    $seq_key++;
                }
            }

            $seach_result_array = $this->CI->multi_curl->execute_multi_curl($curl_params);

            $seach_result = [];
            foreach ($seach_result_array as $key => $data) {
                foreach ($data as $key1 => $value1) {
                    if ($key1 === AMADEUS_FLIGHT_BOOKING_SOURCE) {
                        $seach_result[$key1][] = $value1;
                    } else {
                        $seach_result[$key1] = $value1;
                    }
                }
            }

            foreach ($flight_obj as $fo_k => $fo_v) {
                if (!empty($seach_result[$fo_k])) {
                    $flight_data = $this->CI->{$fo_v}->get_flight_list($seach_result[$fo_k], $search_id);
                    if (($flight_data['status'] ?? '') === SUCCESS_STATUS &&
                        !empty($flight_data['data']['FlightDataList']['JourneyList'])) {
                        if (empty($formatted_seach_result)) {
                            $formatted_seach_result = $flight_data;
                        } else {
                            $this->merge_flight_list(
                                $flight_data['data']['FlightDataList']['JourneyList'],
                                $formatted_seach_result
                            );
                        }
                    }
                }
            }

            if (($formatted_seach_result['status'] ?? '') === SUCCESS_STATUS &&
                count($flight_active_booking_sources) >= 1) {
                $JourneyList = $formatted_seach_result['data']['FlightDataList']['JourneyList'];

                /*if (in_array(get_domain_auth_id(), [275, 210, 176, 267, 331, 326, 421, 404, 470, 483, 499, 502, 507, 521], true)) {
                    $JourneyList = $this->eliminate_duplicate_flights($JourneyList);
                }*/

                $JourneyList = $this->sort_flight_list($JourneyList);

                $this->CI->load->library('flight/common_flight');
                $formatted_seach_result['data']['FlightDataList']['JourneyList'] =
                    $this->CI->common_flight->update_markup_and_insert_cache_key_to_token(
                        $JourneyList,
                        $cache_key,
                        $search_id
                    );

                $final_flight_list = $formatted_seach_result;
            } else {
                $final_flight_list['message'] = 'No Flights Found';
            }
        } else {
            $final_flight_list = $cache_contents;
            $final_flight_list['data']['cache'] = true;
        }

        return $final_flight_list;
    }
    /**
     * Fare Rules
     * @param unknown_type $request
     */
    public function fare_rules(array $request, int $search_id): array
    {
        $fare_rule_result = [
            'status'  => FAILURE_STATUS,
            'data'    => [],
            'message' => '',
        ];

        $result_token = trim($request['ResultToken'] ?? '');

        if ($result_token === '') {
            $fare_rule_result['message'] = 'Missing ResultToken';
            return $fare_rule_result;
        }

        $flight_search_data = Common_Flight::read_record($result_token);

        if (!valid_array($flight_search_data)) {
            $fare_rule_result['message'] = 'Invalid Fare Rule Request';
            return $fare_rule_result;
        }

        $decoded_data = json_decode($flight_search_data[0], true);
        $fare_rule_request = array_values(unserialized_data($decoded_data['ResultToken']))[0] ?? null;

        if (!is_array($fare_rule_request) || empty($fare_rule_request['booking_source'])) {
            $fare_rule_result['message'] = 'Invalid Booking Source';
            return $fare_rule_result;
        }

        $booking_source = $fare_rule_request['booking_source'];

        $active_booking_source_condition = [['BS.source_id', '=', '"' . $booking_source . '"']];
        $flight_active_booking_sources = $this->flight_active_booking_sources($active_booking_source_condition);

        $this->api_authentication($flight_active_booking_sources);

        $flight_obj_ref = load_flight_lib($booking_source);
        $fare_rule_result = $this->CI->{$flight_obj_ref}->get_fare_rules($fare_rule_request, $search_id);

        return $fare_rule_result;
    }

    public function mini_fare_rules(array $request, int $search_id): array
    {
        $fare_rule_result = [
            'status'  => FAILURE_STATUS,
            'data'    => [],
            'message' => '',
        ];

        $result_token = trim($request['ResultToken'] ?? '');

        if ($result_token === '') {
            $fare_rule_result['message'] = 'Missing ResultToken';
            return $fare_rule_result;
        }

        $flight_search_data = Common_Flight::read_record($result_token);

        if (!valid_array($flight_search_data)) {
            $fare_rule_result['message'] = 'Invalid Fare Rule Request';
            return $fare_rule_result;
        }

        $decoded_data = json_decode($flight_search_data[0], true);
        $fare_rule_request = array_values(unserialized_data($decoded_data['ResultToken']))[0] ?? null;

        if (!is_array($fare_rule_request) || empty($fare_rule_request['booking_source'])) {
            $fare_rule_result['message'] = 'Invalid Booking Source';
            return $fare_rule_result;
        }

        $booking_source = $fare_rule_request['booking_source'];
        $flight_obj_ref = load_flight_lib($booking_source);

        // Modern dynamic property access
        $fare_rule_result = $this->CI->{$flight_obj_ref}->get_mini_fare_rules($decoded_data, $search_id);

        return $fare_rule_result;
    }

    /**
     * Returns Updated Fare
     * @param unknown_type $request
     */
    public function update_fare_quote(array $request, int $search_id, string $cache_key): array
    {
        $result = [
            'status'  => FAILURE_STATUS,
            'data'    => [],
            'message' => '',
        ];

        $result_token = trim($request['ResultToken'][0] ?? '');

        if ($result_token === '') {
            $result['message'] = 'Missing ResultToken';
            return $result;
        }

        $flight_search_data = Common_Flight::read_record($result_token);

        if (!valid_array($flight_search_data)) {
            $result['message'] = 'Invalid updateFareQuote Request';
            return $result;
        }

        $decoded_data = json_decode($flight_search_data[0], true);
        $request_data = array_values(unserialized_data($decoded_data['ResultToken']))[0] ?? null;

        if (!is_array($request_data) || empty($request_data['booking_source'])) {
            $result['message'] = 'Invalid Booking Source';
            return $result;
        }

        $booking_source = $request_data['booking_source'];
        $active_source_condition = [['BS.source_id', '=', '"' . $booking_source . '"']];
        $active_sources = $this->flight_active_booking_sources($active_source_condition);

        $this->api_authentication($active_sources);

        $flight_lib = load_flight_lib($booking_source);
        $fare_quote_response = $this->CI->{$flight_lib}->get_update_fare_quote($request_data, $search_id);

        if ($fare_quote_response['status'] !== SUCCESS_STATUS) {
            $result['message'] = $fare_quote_response['message'] ?? 'Update Fare Quote Failed';
            return $result;
        }

        $this->CI->load->library('flight/common_flight');

        $journey_list = $fare_quote_response['data']['FareQuoteDetails']['JourneyList'] ?? [];
        $markup_data = $this->CI->common_flight->update_markup_and_insert_cache_key_to_token(
            $journey_list,
            $cache_key,
            $search_id
        );

        unset($markup_data[0][0]['ExtraServices']);

        $result['status'] = SUCCESS_STATUS;
        $result['data']['FareQuoteDetails']['JourneyList'] = $markup_data[0][0];

        return $result;
    }

    /**
     * Returns Extra Services
     */
    public function get_extra_services(array $request, int $search_id, string $cache_key): array
    {
        $extra_services = [
            'data' => [],
            'status' => FAILURE_STATUS,
            'message' => '',
        ];

        $ResultToken = trim($request['ResultToken'] ?? '');
        $flight_search_data = Common_Flight::read_record($ResultToken);

        if (valid_array($flight_search_data)) {
            $flight_search_data = json_decode($flight_search_data[0], true, 512, JSON_THROW_ON_ERROR);
            $extra_services_request = array_values(unserialized_data($flight_search_data['ResultToken'] ?? ''))[0] ?? [];

            $booking_source = $extra_services_request['booking_source'] ?? '';
            $active_booking_source_condition = [['BS.source_id', '=', '"' . $booking_source . '"']];
            $flight_active_booking_sources = $this->flight_active_booking_sources($active_booking_source_condition);

            $this->api_authentication($flight_active_booking_sources);

            $flight_obj_ref = load_flight_lib($booking_source);
            $extra_services_data = $this->CI->$flight_obj_ref->get_extra_services($extra_services_request, $search_id);

            if (($extra_services_data['status'] ?? '') === SUCCESS_STATUS) {
                $this->CI->load->library('flight/common_flight');
                $extra_services_result = $this->CI->common_flight->cache_extra_services($extra_services_data['data']['ExtraServiceDetails'] ?? [], $cache_key);

                $extra_services['status'] = SUCCESS_STATUS;
                $extra_services['data']['ExtraServiceDetails'] = $extra_services_result;
            } else {
                $extra_services['message'] = $extra_services_data['message'] ?? 'Failed to retrieve extra services';
            }
        } else {
            $extra_services['message'] = 'Invalid ExtraServices Request';
        }

        return $extra_services;
    }

    /**
     * Process the booking
     */
    public function process_booking(array $request, int $search_id, string $cache_key): array
    {
        $booking_response = [
            'data' => [],
            'status' => FAILURE_STATUS,
            'message' => '',
        ];

        $search_data = $this->search_data($search_id);
        $validate_booking_params = $this->validate_booking_params($request, $search_data);

        if ($validate_booking_params['status'] === FAILURE_STATUS) {
            $booking_response['message'] = $validate_booking_params['message'];
            return $booking_response;
        }

        $ResultToken = trim($request['ResultToken'] ?? '');
        $flight_data = Common_Flight::read_record($ResultToken);

        if (!valid_array($flight_data)) {
            $booking_response['message'] = 'Invalid CommitBooking Request or Session Expired';
            return $booking_response;
        }

        $this->CI->load->library('flight/common_flight');
        $flight_data = json_decode($flight_data[0], true, 512, JSON_THROW_ON_ERROR);
        $raw_api_fare = $flight_data['Price'] ?? [];

        $OperatorCode = $flight_data['FlightDetails']['Details'][0][0]['OperatorCode'] ?? '';
        $ResultToken = array_values(unserialized_data($flight_data['ResultToken'] ?? ''))[0] ?? [];
        $booking_source = $ResultToken['booking_source'] ?? '';

        $flight_obj_ref = load_flight_lib($booking_source);
        $flight_data['mini_fare_rules'] = [];

        $price_details = $this->CI->common_flight->final_booking_transaction_fare_details(
            $flight_data['Price'] ?? [],
            $search_id,
            $booking_source,
            $OperatorCode
        );

        $flight_data['Price'] = $price_details['Price'] ?? [];
        $flight_data['PriceBreakup'] = $price_details['PriceBreakup'] ?? [];
        $booking_transaction_amount = $flight_data['Price']['client_buying_price'] ?? 0;

        $deduct_balance = SUCCESS_STATUS;
        $check_balance_status = SUCCESS_STATUS; // replace with real balance check if needed

        $passenger_details = $this->check_booking_passenger_params($request['Passengers'] ?? []);
        $app_reference = $request['AppReference'] ?? '';
        $sequence_number = $request['SequenceNumber'] ?? '';

        if ($check_balance_status === SUCCESS_STATUS) {
            $save_flight_booking = $this->CI->common_flight->save_flight_booking(
                $flight_data,
                $passenger_details,
                $app_reference,
                $sequence_number,
                $booking_source,
                $search_id
            );

            if ($save_flight_booking['status'] === SUCCESS_STATUS) {
                $book_req_params = [
                    'ResultToken' => $ResultToken,
                    'Passengers' => $passenger_details,
                    'flight_data' => $flight_data,
                ];

                if (!empty($request['GST'])) {
                    $book_req_params['GST'] = $request['GST'];
                }

                $active_booking_source_condition = [['BS.source_id', '=', '"' . $booking_source . '"']];
                $flight_active_booking_sources = $this->flight_active_booking_sources($active_booking_source_condition);
                $this->api_authentication($flight_active_booking_sources);

                $process_booking_response = $this->CI->$flight_obj_ref->process_booking(
                    $book_req_params,
                    $app_reference,
                    $sequence_number,
                    $search_id
                );

                if ($process_booking_response['status'] === SUCCESS_STATUS) {
                    if ($deduct_balance === SUCCESS_STATUS) {
                        $this->CI->common_flight->deduct_flight_booking_amount($app_reference, $sequence_number);
                    }

                    $flight_booking_details = $this->CI->common_flight->get_flight_booking_transaction_details($app_reference, $sequence_number);
                    return $flight_booking_details;
                }

                $booking_response['message'] = $process_booking_response['message'] ?? 'Booking failed';
            } else {
                $booking_response['message'] = $save_flight_booking['message'] ?? 'Booking save failed';
            }
        } else {
            $booking_response['message'] = 'Agency does not have enough balance.';
            $exception_log_message = '';
            $this->CI->exception_logger->log_exception(
                $app_reference,
                $booking_source . ' - (<strong>Agency does not have enough balance.</strong>)',
                $exception_log_message,
                $booking_response
            );

            $this->CI->common_flight->save_flight_booking(
                $flight_data,
                $passenger_details,
                $app_reference,
                $sequence_number,
                $booking_source,
                $search_id
            );
        }

        return $booking_response;
    }

    /**
     * Hold Ticket
     */
    public function hold_ticket(array $request, int|string $search_id, string $cache_key): array
    {
        $booking_response = [
            'data' => [],
            'status' => FAILURE_STATUS,
            'message' => '',
        ];

        $validate_booking_params = $this->validate_booking_params($request);
        if ($validate_booking_params['status'] === FAILURE_STATUS) {
            $booking_response['message'] = $validate_booking_params['message'];
            return $booking_response;
        }

        $ResultToken = trim($request['ResultToken']);
        $flight_data = Common_Flight::read_record($ResultToken);

        if (valid_array($flight_data)) {
            $this->CI->load->library('flight/common_flight');
            $flight_data = json_decode($flight_data[0], true);
            $raw_api_fare = $flight_data['Price'];

            $OperatorCode = $flight_data['FlightDetails']['Details'][0][0]['OperatorCode'] ?? '';

            $ResultTokenData = array_values(unserialized_data($flight_data['ResultToken']))[0];
            $booking_source = $ResultTokenData['booking_source'];

            $flight_price_details = $this->CI->common_flight->final_booking_transaction_fare_details(
                $flight_data['Price'],
                $search_id,
                $booking_source,
                $OperatorCode
            );

            $flight_data['Price'] = $flight_price_details['Price'];
            $flight_data['PriceBreakup'] = $flight_price_details['PriceBreakup'];
            $booking_transaction_amount = $flight_data['Price']['client_buying_price'];

            $passenger_details = $this->check_booking_passenger_params($request['Passengers']);
            $app_reference = $request['AppReference'];
            $sequence_number = $request['SequenceNumber'];

            $save_flight_booking = $this->CI->common_flight->save_flight_booking(
                $flight_data,
                $passenger_details,
                $app_reference,
                $sequence_number,
                $booking_source,
                $search_id
            );

            $this->CI->custom_db->update_record(
                'flight_booking_transaction_details',
                ['is_hold' => ACTIVE],
                ['app_reference' => $app_reference, 'sequence_number' => $sequence_number]
            );

            if ($save_flight_booking['status'] === SUCCESS_STATUS) {
                $book_req_params = [
                    'ResultToken' => $ResultTokenData,
                    'Passengers' => $passenger_details,
                    'flight_data' => $flight_data
                ];

                $active_booking_source_condition = [['BS.source_id', '=', '"' . $booking_source . '"']];
                $flight_active_booking_sources = $this->flight_active_booking_sources($active_booking_source_condition);
                $this->api_authentication($flight_active_booking_sources);

                $flight_obj_ref = load_flight_lib($booking_source);
                $process_booking_response = $this->CI->$flight_obj_ref->hold_ticket(
                    $book_req_params,
                    $app_reference,
                    $sequence_number,
                    $search_id
                );

                if ($process_booking_response['status'] === SUCCESS_STATUS) {
                    $booking_response = $this->CI->common_flight->get_flight_booking_transaction_details(
                        $app_reference,
                        $sequence_number
                    );
                } else {
                    $booking_response['message'] = $process_booking_response['message'];
                }
            } else {
                $booking_response['message'] = $save_flight_booking['message'];
            }
        } else {
            $booking_response['message'] = 'Invalid HoldTicket Request';
        }

        return $booking_response;
    }

    /**
     * Confirm Hold Ticket
     */
    public function confirm_hold_ticket(array $request): array
    {
        $app_reference = $request['AppReference'];
        $sequence_number = $request['SequenceNumber'];
        $booking_id = $request['BookingId'];
        $pnr = $request['Pnr'];

        $booking_response = [
            'data' => [],
            'status' => FAILURE_STATUS,
            'message' => '',
        ];

        $booking_details = $this->CI->custom_db->single_table_records(
            'flight_booking_transaction_details',
            '*',
            ['app_reference' => $app_reference, 'pnr' => $pnr, 'sequence_number' => $sequence_number, 'status' => 'BOOKING_HOLD']
        );

        if (
            valid_array($booking_details)
            && $booking_details['status'] === SUCCESS_STATUS
            && ($booking_details['data'][0]['hold_ticket_req_status'] ?? null) == INACTIVE
        ) {
            $get_booking_details = $this->CI->flight_model->get_booking_details($app_reference);

            $master_booking_details = $get_booking_details['data']['booking_details'][0];
            $passenger_details = $get_booking_details['data']['booking_customer_details'][0];
            $booking_transaction_details = $get_booking_details['data']['booking_transaction_details'][0];

            $fare_details = $booking_details['data'][0];
            $amount = $this->CI->domain_management->agent_buying_price($fare_details)[0];

            if ($this->CI->domain_management->verify_domain_balance($amount, Flight::get_credential_type()) === SUCCESS_STATUS) {
                $transaction_attributes = json_decode($booking_transaction_details['attributes'], true);

                $book_req_params = [
                    'PNR' => $request['Pnr'],
                    'BookingId' => $request['BookingId']
                ];

                if (isset($transaction_attributes['TraceId'])) {
                    $book_req_params['TraceId'] = $transaction_attributes['TraceId'];
                }

                if (isset($transaction_attributes['TotalAmount'])) {
                    $book_req_params['TotalAmount'] = $transaction_attributes['TotalAmount'];
                }

                $booking_source = $fare_details['booking_source'];
                $flight_obj_ref = load_flight_lib($booking_source);

                $process_booking_response = $this->CI->$flight_obj_ref->confirm_hold_ticket(
                    $book_req_params,
                    $app_reference,
                    $sequence_number
                );

                if ($process_booking_response['status'] === SUCCESS_STATUS) {
                    $this->CI->common_flight->deduct_flight_booking_amount($app_reference, $sequence_number);
                    $booking_response = $this->CI->common_flight->get_flight_booking_transaction_details($app_reference, $sequence_number);
                } else {
                    $booking_response['message'] = $process_booking_response['message'];
                }
            } else {
                $booking_response['message'] = 'Agency do not have enough balance.';
                $this->CI->exception_logger->log_exception(
                    $app_reference,
                    $this->booking_source_name . '- (<strong>Agency do not have enough balance.</strong>)',
                    '',
                    $booking_response
                );
            }
        }

        return $booking_response;
    }

 /**
     * Issue Hold Ticket
     */
    public function issue_hold_ticket(array $request): array
    {
        $app_reference = $request['AppReference'];
        $sequence_number = $request['SequenceNumber'];
        $ticket_id = '';
        $booking_id = $request['BookingId'];
        $pnr = $request['Pnr'];

        $response = [
            'data' => [],
            'status' => FAILURE_STATUS,
            'message' => '',
        ];

        $booking_details = $this->CI->custom_db->single_table_records(
            'flight_booking_transaction_details',
            '*',
            [
                'app_reference' => $app_reference,
                'pnr' => $pnr,
                'sequence_number' => $sequence_number,
                'status' => 'BOOKING_HOLD'
            ]
        );

        if (
            valid_array($booking_details) &&
            $booking_details['status'] === SUCCESS_STATUS &&
            $booking_details['data'][0]['hold_ticket_req_status'] === INACTIVE
        ) {
            $get_booking_details = $this->CI->flight_model->get_booking_details($app_reference);
            $master_booking_details = $get_booking_details['data']['booking_details'][0];
            $passenger_details = $get_booking_details['data']['booking_customer_details'][0];
            $booking_transaction_details = $get_booking_details['data']['booking_transaction_details'][0];

            $fare_details = $booking_details['data'][0];
            $amount = $this->CI->domain_management->agent_buying_price($fare_details);
            $total_amount = $amount[0];

            $domain_booking_attr = [
                'app_reference' => $app_reference,
                'transaction_type' => "Flight"
            ];

            if ($this->CI->domain_management->verify_domain_balance($total_amount, Flight::get_credential_type()) === SUCCESS_STATUS) {
                $agent_transaction_amount = $total_amount - $fare_details['domain_markup'];
                $currency = $master_booking_details['currency'];
                $currency_conversion_rate = $master_booking_details['currency_conversion_rate'];

                $this->CI->domain_management_model->save_transaction_details(
                    'flight',
                    $app_reference,
                    $agent_transaction_amount,
                    $fare_details['domain_markup'],
                    0,
                    'flight Transaction was Successfully done',
                    $currency,
                    $currency_conversion_rate
                );

                $this->CI->custom_db->update_record(
                    'flight_booking_transaction_details',
                    ['hold_ticket_req_status' => ACTIVE],
                    ['app_reference' => $app_reference, 'pnr' => $pnr]
                );

                $domain_data = $this->CI->custom_db->single_table_records('domain_list', 'domain_name', ['origin' => get_domain_auth_id()]);
                $domain_name = $domain_data['data'][0]['domain_name'];

                $voucher_data = [
                    'AppReference' => $app_reference,
                    'PNR' => $pnr,
                    'BookingID' => $booking_id,
                    'status' => $booking_details['status'],
                    'travel_date' => date("d M Y, H:i", strtotime($master_booking_details['journey_start'])),
                    'leade_pax_name' => $passenger_details,
                    'domain_name' => $domain_name,
                    'booking_api_name' => $booking_transaction_details['booking_api_name']
                ];

                // Notify Support Team
                $sms_template = $this->CI->load->view('voucher/ticket_hold_sms', $voucher_data, true);
                send_alert_sms($sms_template);

                $mail_template = $this->CI->load->view('voucher/ticket_hold', $voucher_data, true);
                $email = $this->CI->config->item('alert_email_id');

                $this->CI->load->library('provab_mailer');
                $this->CI->provab_mailer->send_mail($email, "$domain_name - Confirm Hold Ticket", $mail_template);

                $response['status'] = SUCCESS_STATUS;
                $response['message'] = 'Request Received, Will Update the Ticket Details Shortly';
            } else {
                $response['status'] = FAILURE_STATUS;
                $response['message'] = 'Insufficient Balance to Confirm this HOLD Booking';
            }
        }

        return $response;
    }

    /**
     * Cancel Booking Request
     */
    public function cancel_booking(array $request): array
    {
        $cancel_booking_response = [
            'data' => [],
            'status' => FAILURE_STATUS,
            'message' => '',
        ];

        if (!valid_array($request)) {
            $cancel_booking_response['message'] = 'Invalid CancelBooking Request';
            return $cancel_booking_response;
        }

        $app_reference = $request['AppReference'];
        $sequence_number = $request['SequenceNumber'];
        $flight_booking_details = $this->CI->flight_model->get_flight_booking_transaction_details($app_reference, $sequence_number);

        foreach ($request['TicketId'] as $TicketData) {
            $temp_pax_data = $this->CI->custom_db->single_table_records(
                'flight_booking_passenger_details',
                'origin, status',
                ['origin' => $TicketData, 'status' => 'BOOKING_CONFIRMED']
            );

            if ($temp_pax_data['status'] === SUCCESS_STATUS) {
                $this->CI->custom_db->update_record(
                    'flight_booking_passenger_details',
                    ['status' => "CANCELLATION_INITIALIZED"],
                    ['origin' => $TicketData]
                );

                $this->CI->custom_db->insert_record('flight_cancellation_details', [
                    'passenger_fk' => $temp_pax_data['data'][0]['origin'],
                    'created_by_id' => intval($this->entity_user_id ?? 0),
                    'created_datetime' => date('Y-m-d H:i:s'),
                    'cancellation_requested_on' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if ($flight_booking_details['status'] === SUCCESS_STATUS) {
            $booking_transaction_details = $flight_booking_details['data']['booking_transaction_details'][0];
            $booking_source = $booking_transaction_details['booking_source'];

            $active_booking_source_condition = [['BS.source_id', '=', '"' . $booking_source . '"']];
            $flight_active_booking_sources = $this->flight_active_booking_sources($active_booking_source_condition);

            $this->api_authentication($flight_active_booking_sources);
            $flight_obj_ref = load_flight_lib($booking_source);
            $cancel_booking_details = $this->CI->$flight_obj_ref->cancel_booking($request);

            if ($cancel_booking_details['status'] === SUCCESS_STATUS) {
                $booking_details = $this->CI->flight_model->get_flight_booking_transaction_details($app_reference, $sequence_number);
                $master_booking_details = $booking_details['data']['booking_details'][0];
                $domain_name = $master_booking_details['domain_name'];
                $booking_transaction_details = $booking_details['data']['booking_transaction_details'][0];
                $booking_customer_details = $booking_details['data']['booking_customer_details'];

                $passenger_ticket_details = $this->CI->common_flight->get_cancellation_reequested_pax_details($booking_customer_details, $request['TicketId']);

                $ticket_cancel_request = [
                    'domain_name' => $domain_name,
                    'booking_transaction_details' => $booking_transaction_details,
                    'passenger_ticket_details' => $passenger_ticket_details,
                ];

                // Notify Team
                $sms_template = $this->CI->load->view('flight/ticket_cancel_request_sms_template', $ticket_cancel_request, true);
                send_alert_sms($sms_template);

                $mail_template = $this->CI->load->view('flight/ticket_cancel_request_template', $ticket_cancel_request, true);
                $email = $this->CI->config->item('alert_email_id');
                $subject = ucfirst($domain_name) . ' - Flight Ticket Cancellation Request';

                $this->CI->load->library('provab_mailer');
                $this->CI->provab_mailer->send_mail($email, $subject, $mail_template);
            }

            $cancel_booking_response = $cancel_booking_details;
        } else {
            $cancel_booking_response['message'] = 'Invalid AppReference';
        }

        return $cancel_booking_response;
    }

   /**
     * Validate Booking Parameters
     */
    private function validate_booking_params(array $request, array $search_data): array
    {
        $data = [
            'status' => FAILURE_STATUS,
            'message' => '',
        ];

        // Validate AppReference
        $appReference = trim($request['AppReference'] ?? '');
        if (strlen($appReference) < 10 || strlen($appReference) > 40) {
            $data['message'] = 'AppReference should be between 10 to 40 in length';
            return $data;
        }

        // Validate SequenceNumber
        $sequenceNumber = trim($request['SequenceNumber'] ?? '');
        if (!is_numeric($sequenceNumber)) {
            $data['message'] = 'SequenceNumber is Required';
            return $data;
        }

        // Validate Passengers
        if (!isset($request['Passengers']) || !valid_array($request['Passengers'])) {
            $data['message'] = 'Passengers information is Required';
            return $data;
        }

        // Validate Passport data for international flights
        if (empty($search_data['data']['is_domestic'])) {
            foreach ($request['Passengers'] as $index => $passenger) {
                if (empty($passenger['PassportNumber'])) {
                    return [
                        'status' => FAILURE_STATUS,
                        'message' => "PassportNumber is Required for passenger index {$index}"
                    ];
                }
                if (empty($passenger['PassportExpiry'])) {
                    return [
                        'status' => FAILURE_STATUS,
                        'message' => "PassportExpiry is Required for passenger index {$index}"
                    ];
                }
                if (empty($passenger['PassportIssueDate'])) {
                    return [
                        'status' => FAILURE_STATUS,
                        'message' => "PassportIssueDate is Required for passenger index {$index}"
                    ];
                }
            }
        }

        $data['status'] = SUCCESS_STATUS;
        return $data;
    }

    /**
     * Check and Normalize Booking Passenger Parameters
     */
    public function check_booking_passenger_params(array $passenger_details): array
    {
        foreach ($passenger_details as $index => $passenger) {
            $passportNumber = !empty($passenger['PassportNumber'])
                ? preg_replace('/\s+/', '', $passenger['PassportNumber'])
                : (string) rand(1111111111, 9999999999);

            $passportExpiry = !empty($passenger['PassportExpiry'])
                ? $passenger['PassportExpiry']
                : date('Y-m-d', strtotime('+5 years'));

            $contactNo = $this->validate_mobile_number($passenger['ContactNo'] ?? '');

            $passenger_details[$index]['PassportNumber'] = $passportNumber;
            $passenger_details[$index]['PassportExpiry'] = $passportExpiry;
            $passenger_details[$index]['ContactNo'] = $contactNo;
        }

        return $passenger_details;
    }
/**
     * Validates the mobile number
     */
    private function validate_mobile_number(string $mobileNumber): string
    {
        $mobileNumber = trim($mobileNumber);
        $mobileNumber = ltrim($mobileNumber, '0');

        if (strlen($mobileNumber) < 10) {
            $missingLength = 10 - strlen($mobileNumber);
            $mobileNumber = str_repeat('0', $missingLength) . $mobileNumber;
        }

        return $mobileNumber;
    }

    /**
     * Returns calendar fare
     */
    public function calendar_fare(array $request): array
    {
        $curlParams = [
            'booking_source' => [],
            'request' => [],
            'url' => [],
            'header' => [],
            'remarks' => [],
        ];

        $formattedCalendarFare = [
            'data' => [],
            'status' => FAILURE_STATUS,
        ];

        $activeBookingSources = $this->flight_active_booking_sources([]);

        // Authenticate APIs
        $this->api_authentication($activeBookingSources);
        $flightObjects = [];

        foreach ($activeBookingSources as $source) {
            $ref = load_flight_lib($source['booking_source'], '', true);
            $flightObjects[$source['booking_source']] = $ref;

            $searchRequest = $this->CI?->$ref?->get_calendar_fare_request($request);
            if ($searchRequest['status'] === SUCCESS_STATUS) {
                $searchRequest['data']['remarks'] = $this->CI?->$ref?->booking_source_name;
                $this->assign_curl_params(
                    $searchRequest['data'],
                    $curlParams['request'],
                    $curlParams['url'],
                    $curlParams['header'],
                    $curlParams['booking_source'],
                    $curlParams['remarks']
                );
            }
        }

        // Replace this with real API call
        $calendarFareResult[0][AMADEUS_FLIGHT_BOOKING_SOURCE] = $this->CI?->custom_db?->get_static_response(5060);

        foreach ($flightObjects as $key => $ref) {
            $result = $this->CI?->$ref?->get_calendar_fare($calendarFareResult[0][$key]);

            if ($result['status'] === SUCCESS_STATUS && valid_array($result['data']['CalendarFareDetails'])) {
                $formattedCalendarFare = $result;
            }
        }

        if ($formattedCalendarFare['status'] === SUCCESS_STATUS && valid_array($formattedCalendarFare['data'])) {
            $this->CI?->load?->library('flight/common_flight');
            $formattedCalendarFare['data']['CalendarFareDetails'] = $this->CI?->common_flight?->update_calendarfare_currency(
                $formattedCalendarFare['data']['CalendarFareDetails']
            );
        } else {
            $formattedCalendarFare['message'] = 'No Data Found';
        }

        return $formattedCalendarFare;
    }

    /**
     * Updates calendar fare
     */
    public function update_calendar_fare(array $request): array
    {
        $curlParams = [
            'booking_source' => [],
            'request' => [],
            'url' => [],
            'header' => [],
            'remarks' => [],
        ];

        $formattedCalendarFare = [
            'data' => [],
            'status' => FAILURE_STATUS,
        ];
        $calendarFareResult = [];

        $activeBookingSources = $this->flight_active_booking_sources([]);

        $this->api_authentication($activeBookingSources);
        $flightObjects = [];

        foreach ($activeBookingSources as $source) {
            $ref = load_flight_lib($source['booking_source'], '', true);
            $flightObjects[$source['booking_source']] = $ref;

            $searchRequest = $this->CI?->$ref?->get_update_calendar_fare_request($request);
            if ($searchRequest['status'] === SUCCESS_STATUS) {
                $searchRequest['data']['remarks'] = $this->CI?->$ref?->booking_source_name;
                $this->assign_curl_params(
                    $searchRequest['data'],
                    $curlParams['request'],
                    $curlParams['url'],
                    $curlParams['header'],
                    $curlParams['booking_source'],
                    $curlParams['remarks']
                );
            }
        }

        $calendarFareResult = $this->CI?->multi_curl?->execute_multi_curl($curlParams);

        foreach ($flightObjects as $key => $ref) {
            $result = $this->CI?->$ref?->get_calendar_fare($calendarFareResult[$key] ?? []);
            if ($result['status'] === SUCCESS_STATUS && valid_array($result['data']['CalendarFareDetails'])) {
                $formattedCalendarFare = $result;
            }
        }

        if ($formattedCalendarFare['status'] === SUCCESS_STATUS && valid_array($formattedCalendarFare['data'])) {
            $this->CI?->load?->library('flight/common_flight');
            $formattedCalendarFare['data']['CalendarFareDetails'] = $this->CI?->common_flight?->update_calendarfare_currency(
                $formattedCalendarFare['data']['CalendarFareDetails']
            );
        } else {
            $formattedCalendarFare['message'] = 'No Data Found';
        }

        return $formattedCalendarFare;
    }
  /**
 * Merges the Flight Data
 */
private function merge_flight_list(array $flight_data, array &$formatted_search_result): void
{
    foreach ($flight_data as $fd_k => $fd_v) {
        if (!empty($formatted_search_result['data']['FlightDataList']['JourneyList'][$fd_k])
            && is_array($formatted_search_result['data']['FlightDataList']['JourneyList'][$fd_k])) {
            $formatted_search_result['data']['FlightDataList']['JourneyList'][$fd_k] = array_merge(
                $formatted_search_result['data']['FlightDataList']['JourneyList'][$fd_k],
                $fd_v
            );
        }
    }
}

/**
 * Eliminates Duplicate Flights Based on Flight Number, Airports, and Timings
 */
/*private function eliminate_duplicate_flights(array $JourneyList): array
{
    $new_journey_list = [];

    foreach ($JourneyList as $jl_k => $jl_v) {
        $flight_data = [];

        foreach ($jl_v as $row_v) {
            $FlightDetails = $row_v['FlightDetails']['Details'];
            $TotalNetFare = (float) $row_v['Price']['TotalDisplayFare'];
            $array_key = '';

            foreach ($FlightDetails as $fd_v) {
                foreach ($fd_v as $flight_v) {
                    $array_key .= $flight_v['FlightNumber']
                        . $flight_v['Origin']['AirportCode']
                        . $flight_v['Destination']['AirportCode']
                        . $flight_v['Origin']['FDTV']
                        . $flight_v['Destination']['FATV'];
                }
            }

            if (!empty($flight_data[$array_key])) {
                $Old_TotalNetFare = (float) $flight_data[$array_key]['Price']['TotalDisplayFare'];
                if ($TotalNetFare < $Old_TotalNetFare) {
                    $flight_data[$array_key] = $row_v;
                }
            } else {
                $flight_data[$array_key] = $row_v;
            }
        }

        $new_journey_list[$jl_k] = $flight_data;
    }

    // Normalize keys
    $final_journey_list = [];
    foreach ($new_journey_list as $jl_k => $flights) {
        $final_journey_list[$jl_k] = array_values($flights);
    }

    return $final_journey_list;
}*/

/**
 * Sorts Flights by Net Fare (after commission adjustments)
 */
private function sort_flight_list(array $JourneyList): array
{
    $sorted_journey_list = [];

    foreach ($JourneyList as $jl_k => $jl_v) {
        $sort_item = [];

        foreach ($jl_v as $row_k => $row_v) {
            $breakup = $row_v['Price']['PriceBreakup'];
            $sort_item[$row_k] = (float) (
                $row_v['Price']['TotalDisplayFare']
                - $breakup['AgentCommission']
                + $breakup['AgentTdsOnCommision']
            );
        }

        array_multisort($sort_item, SORT_ASC, $jl_v);
        $sorted_journey_list[$jl_k] = $jl_v;
    }

    return $sorted_journey_list;
}


    public function booking_details(array $request): array
    {
        $bookingDetails = [
            'data' => [],
            'status' => FAILURE_STATUS,
            'message' => ''
        ];

        if (valid_array($request) && !empty($request['AppReference'])) {
            $appReference = trim($request['AppReference']);

            $bookingData = $this->CI->flight_model->get_booking_details($appReference);
            if ($bookingData['status'] === SUCCESS_STATUS) {
                $this->CI->load->library('booking_data_formatter');
                $formattedData = $this->CI->booking_data_formatter->format_flight_booking_data($bookingData, 'admin');

                $transactionDetails = [];
                foreach ($formattedData['data']['booking_details'][0]['booking_transaction_details'] as $key => $value) {
                    $transactionDetails[$key] = [
                        'PNR' => $value['pnr'],
                        'BookingID' => $value['book_id'],
                        'SequenceNumber' => $value['sequence_number'],
                        'Status' => $value['status'],
                    ];

                    $custArray = [];
                    foreach ($value['booking_customer_details'] as $k => $b_d) {
                        $custArray['cust'][$k] = [
                            'title' => $b_d['title'],
                            'first_name' => $b_d['first_name'],
                            'middle_name' => $b_d['middle_name'],
                            'last_name' => $b_d['last_name'],
                            'TicketId' => $b_d['TicketId'],
                            'Status' => $b_d['status'],
                            'TicketNumber' => $b_d['TicketNumber'],
                        ];

                        $origin = $b_d['origin'];
                        $extra = $value['extra_service_details'];

                        if (!empty($extra['seat_details']['details'][$origin][0])) {
                            $seat = $extra['seat_details']['details'][$origin][0];
                            $custArray['cust'][$k]['extra_service_details']['seat'] = [
                                'from_airport_code' => $seat['from_airport_code'],
                                'to_airport_code' => $seat['to_airport_code'],
                                'airline_code' => $seat['airline_code'],
                                'flight_number' => $seat['flight_number'],
                                'price' => $seat['price'],
                                'seat_id' => $seat['seat_id'],
                            ];
                        }

                        if (!empty($extra['meal_details']['details'][$origin][0])) {
                            $meal = $extra['meal_details']['details'][$origin][0];
                            $custArray['cust'][$k]['extra_service_details']['meal'] = [
                                'from_airport_code' => $meal['from_airport_code'],
                                'to_airport_code' => $meal['to_airport_code'],
                                'description' => $meal['description'],
                                'price' => $meal['price'],
                                'meal_id' => $meal['meal_id'],
                            ];
                        }

                        if (!empty($extra['baggage_details']['details'][$origin][0])) {
                            $baggage = $extra['baggage_details']['details'][$origin][0];
                            $custArray['cust'][$k]['extra_service_details']['baggage'] = [
                                'from_airport_code' => $baggage['from_airport_code'],
                                'to_airport_code' => $baggage['to_airport_code'],
                                'description' => $baggage['description'],
                                'price' => $baggage['price'],
                                'baggage_id' => $baggage['baggage_id'],
                            ];
                        }
                    }
                    $transactionDetails[$key]['BookingCustomer'] = $custArray['cust'] ?? [];
                }

                $bookingItinerary = [];
                foreach ($formattedData['data']['booking_details'][0]['booking_itinerary_details'] as $key => $value) {
                    $bookingItinerary[$key] = [
                        'AirlinePNR' => $value['airline_pnr'],
                        'FromAirlineCode' => $value['from_airport_code'],
                        'ToAirlineCode' => $value['to_airport_code'],
                        'DepartureDatetime' => $value['departure_datetime'],
                    ];
                }

                $data = [
                    'AppReference' => $formattedData['data']['booking_details'][0]['app_reference'],
                    'MasterBookingStatus' => $formattedData['data']['booking_details'][0]['status'],
                    'BoookingTransaction' => $transactionDetails,
                    'BookingItineraryDetails' => $bookingItinerary,
                ];

                $statusCode = $formattedData['data']['booking_details'][0]['booking_transaction_details'][0]['status'];
                $status = match (true) {
                    in_array($statusCode, [BOOKING_CONFIRMED, BOOKING_HOLD, BOOKING_PENDING, BOOKING_ABORTED, BOOKING_INPROGRESS]) => $statusCode,
                    $statusCode === BOOKING_FAILED => $statusCode,
                    $statusCode === BOOKING_CANCELLED => $statusCode,
                    default => FAILURE_STATUS
                };

                $bookingDetails['status'] = $status;
                $bookingDetails['data'] = $data;
            } else {
                $bookingDetails['message'] = 'Invalid AppReference ID';
            }
        } else {
            $bookingDetails['message'] = 'Invalid BookingDetails Request';
        }

        return $bookingDetails;
    }

    public function search_data(int $searchId): array
    {
        $response = [
            'status' => true,
            'data' => []
        ];

        $cleanSearchDetails = $this->CI->flight_model->get_safe_search_data($searchId);
        if ($cleanSearchDetails['status']) {
            $data = $cleanSearchDetails['data'];
            $response['data'] = $data;

            if ($data['trip_type'] === 'multicity') {
                $response['data']['from_city'] = $data['from'];
                $response['data']['to_city'] = $data['to'];
                $response['data']['depature'] = $data['depature'];
                $response['data']['return'] = $data['depature'];
            } else {
                $response['data']['from'] = substr(chop(substr($data['from'], -5), ')'), -3);
                $response['data']['to'] = substr(chop(substr($data['to'], -5), ')'), -3);
                $response['data']['depature'] = date("Y-m-d", strtotime($data['depature'])) . 'T00:00:00';
                if (!empty($data['return'])) {
                    $response['data']['return'] = date("Y-m-d", strtotime($data['return'])) . 'T00:00:00';
                }
            }

            $response['data']['type'] = match ($data['trip_type']) {
                'oneway' => 'OneWay',
                'circle' => 'Return',
                default => 'OneWay'
            };

            if ($data['is_domestic'] && $data['trip_type'] === 'return') {
                $response['data']['domestic_round_trip'] = true;
            } else {
                $response['data']['domestic_round_trip'] = false;
            }

            $response['data']['adult'] = $data['adult_config'];
            $response['data']['child'] = $data['child_config'];
            $response['data']['infant'] = $data['infant_config'];
            $response['data']['v_class'] = $data['v_class'] ?? '';
            $response['data']['carrier'] = implode('', $data['carrier'] ?? []);
        } else {
            $response['status'] = false;
        }

        $responseHash = $response;
        $responseHash['data']['cache_key'] = '';
        $this->search_hash = md5(serialized_data($responseHash['data']));

        return $response;
    }
}
