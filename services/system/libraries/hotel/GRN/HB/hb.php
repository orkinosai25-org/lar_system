<?php
ini_set('memory_limit', '250M');
ini_set("auto_detect_line_endings", true);
require_once BASEPATH . 'libraries/hotel/GRN/Common_api_hotel_v3.php';
ob_start();

class HB extends Common_Api_Hotel
{
    var $search_hash;
    protected $token;
    private $end_user_ip = '127.0.0.1';
    var $api_session_id;
    var $api_cancellation_policy_day;

    function __construct()
    {

        parent::__construct(META_ACCOMODATION_COURSE, HB_HOTEL_BOOKING_SOURCE);
        $this->CI = &get_instance();
        $this->CI->load->library('Converter');
        $this->set_api_session_id();
        $this->get_cancellation_policy_day();
    }
    public function set_api_session_id(string $authentication_response = ''): void
    {

        if (empty($this->api_session_id) == true) {
            if (empty($authentication_response) == false) {
                //store in database
                $authentication_response = json_decode($authentication_response, true);

                if (valid_array($authentication_response) == true && $authentication_response['status'] == 'OK') {
                    $session_id = true;
                    $this->CI->api_model->update_api_session_id($this->booking_source, $session_id);
                }
            } else {

                $session_expiry_time = 10; //In minutes
                $session_id = $this->CI->api_model->get_api_session_id($this->booking_source, $session_expiry_time);
                if (empty($session_id) == true) {
                    $authentication_request = $this->get_authentication_request(true);

                    if ($authentication_request['status'] == SUCCESS_STATUS) {
                        $authentication_request = $authentication_request['data'];
                        $authentication_response = $this->auth_process_request($authentication_request['request'], $authentication_request['header'], $authentication_request['url'], $authentication_request['remarks']);
                        $this->set_api_session_id($authentication_response);
                    }
                }
            }
            if (empty($session_id) == false) {
                $this->api_session_id = $session_id;
            }
        }
    }

    public function get_cancellation_policy_day(): void
    {
        $cancellation_day = $this->CI->custom_db->single_table_records('set_hotel_cancellation', '*', array('api' => $this->booking_source));
        if ($cancellation_day['status'] == 1) {
            $this->api_cancellation_policy_day = trim($cancellation_day['data'][0]['day']);
        } else {
            $this->api_cancellation_policy_day = 1;
        }
    }

    /**
     * (non-PHPdoc)
     * @see Common_Api_Grind::search_data()
     */

    public function search_data($search_id): array
    {
        $response = [];
        $response['status'] = true;
        $response['data'] = array();
        $CI = &get_instance();
        if (empty($this->master_search_data) == true and valid_array($this->master_search_data) == false) {
            //$clean_search_details = $this->get_test_params('search');

            $clean_search_details = $CI->hotel_model_v3->get_safe_search_data($search_id);


            if ($clean_search_details['status'] == true) {
                $response['status'] = true;
                $response['data'] = $clean_search_details['data'];

                $this->master_search_data = $response['data'];
            } else {
                $response['status'] = false;
            }
        } else {
            $response['data'] = $this->master_search_data;
        }
        $this->search_hash = md5(serialized_data($response['data']));
        return $response;
    }

    /**
     * Authentcation RQ for api
     */

    private function authenticate_request(): array
    {
        $request = array();
        $AuthenticationRequest = array();

        $signature = hash("sha256", $this->config['api_key'] . $this->config['secrete'] . time());
        $request['request'] = json_encode(array());
        $request['url'] = $this->config['api_url'] . 'status';
        $request['signature'] = $signature;
        $request['api_key'] = $this->config['api_key'];
        $request['status'] = SUCCESS_STATUS;
        return $request;
    }

    /**
     * Formates Search Request
     */

    private function search_request(array $search_data): array
    {
        $request = array();
        $search_request = array();
        $search_request['stay']['checkIn'] = date('Y-m-d', strtotime($search_data['from_date']));
        $search_request['stay']['checkOut'] = date('Y-m-d', strtotime($search_data['to_date']));

        $search_request['reviews'][0]['type'] = "TRIPADVISOR";
        $search_request['reviews'][0]['maxRate'] = "5";
        $search_request['reviews'][0]['minReviewCount'] = "1";

        if (isset($search_data['StarRatings'])) {
            $search_request['filter']['minCategory'] = $search_data['StarRatings'];
        }



        //get hotel_code based on destination_code

        $get_hotel_code = $this->CI->hotel_model_v3->get_HB_hotel_code($search_data['hb_city_id']);

        $hotel_code_arr = [];
        $total_count = count($get_hotel_code);
        if (isset($search_data['hotelcode']) && empty($search_data['hotelcode']) == false) {
            $hotel_code_arr[0] = $search_data['hotelcode'];
        } else {
            foreach ($get_hotel_code as $key => $value) {
                if ($key <= 2000) {
                    $hotel_code_arr[] = $value['hotel_code'];
                }
            }
        }

        $room_count = $search_data['room_count'];
        $child_count = 0;
        $adult_count = 0;
        $room_no = 1;
        for ($i = 0; $i < $room_count; $i++) {
            $adultTxt = '';
            $response_array[$i] = $room_no . '~' . $search_data['room_config'][$i]['NoOfAdults'] . '~' . $search_data['room_config'][$i]['NoOfChild'];

            if (isset($search_data['room_config'][$i]['NoOfAdults']) && !empty($search_data['room_config'][$i]['NoOfAdults']) && $search_data['room_config'][$i]['NoOfAdults'] != 0) {
                $no_of_adult = $search_data['room_config'][$i]['NoOfAdults'];
                $adultTxt = substr('~' . str_repeat('AD-30;', $no_of_adult), 0, -1);
            }
            $response_array[$i] .= $adultTxt;

            if (isset($search_data['room_config'][$i]['NoOfChild']) && !empty($search_data['room_config'][$i]['NoOfChild']) && $search_data['room_config'][$i]['NoOfChild'] != 0) {
                $no_of_child = $search_data['room_config'][$i]['NoOfChild'];
                $childStr = '';
                for ($j = 0; $j < $no_of_child; $j++) {
                    $child_age = array_shift($search_data['room_config'][$i]['ChildAge']);
                    $childStr .= ';' . 'CH' . '-' . $child_age;
                }
                $response_array[$i] .= $childStr;
            }
        }

        $count_no = array_count_values($response_array);

        $response_array = array();
        foreach ($count_no as $cnt_key => $count_arr_val) {
            $count_multiplier = $count_arr_val;
            if ($count_arr_val > 1) {
                $explode = explode('~', $cnt_key);
                //end -
                $explode_adult_child = explode(';', end($explode));
                $child_Adult = '';
                foreach ($explode_adult_child as $ac_k => $ac_v) {
                    //$explode_adult_child[$ac_k] = substr(str_repeat($ac_v.';', $count_multiplier), 0, -1);//AD-10;AD-30
                    $explode_adult_child[$ac_k] = $ac_v; //AD-10;AD-30
                }

                $child_Adult = implode(';', $explode_adult_child);
                //total length - 1 ; exclude last value
                //rest to be multiplied with $countArrVal
                $pax_list = array_slice($explode, 0, -1);

                foreach ($pax_list as $tk => $tv) {
                    if ($tk == 0) {
                        $pax_list[$tk] = $tv * $count_multiplier;
                    } else {
                        $pax_list[$tk] = $tv;
                    }
                }
                $response_array[] = implode('~', $pax_list) . '~' . $child_Adult;
            } else {
                $response_array[] = $cnt_key;
            }
        }

        /* form array */
        foreach ($response_array as $r_key => $query_string) {

            $query_string_explode = explode('~', $query_string);
            $search_request['occupancies'][$r_key]['rooms'] = (int)array_shift($query_string_explode);
            $search_request['occupancies'][$r_key]['adults'] = (int)array_shift($query_string_explode);
            $search_request['occupancies'][$r_key]['children'] = (int)array_shift($query_string_explode);

            $n = 0;
            $paxes_details = explode(';', end($query_string_explode));
            foreach ($paxes_details as $de_key => $pax) {
                $pax_type = explode('-', $pax);
                $search_request['occupancies'][$r_key]['paxes'][$n]['type'] = $pax_type[0];
                $search_request['occupancies'][$r_key]['paxes'][$n]['age'] = end($pax_type);

                $n++;
            }
        }
        $search_request['hotels']['hotel'] = $hotel_code_arr;
        //debug($this->config['secrete']);exit;
        $signature = hash("sha256", trim($this->config['api_key']) . trim($this->config['secrete']) . time());

        $request['signature'] = $signature;
        $request['request'] = json_encode($search_request);
        $request['url'] = $this->config['api_url'] . 'hotels';
        $request['api_key'] = $this->config['api_key'];
        $request['remarks'] = "Hotellist(hotelbeds)";
        $request['status'] = SUCCESS_STATUS;

        return $request;
    }

    /**
     * Authentication Request
     */
    public function get_authentication_request($internal_request = false)
    {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned
        $active_booking_source = $this->is_active_booking_source();

        if ($active_booking_source['status'] == SUCCESS_STATUS) {
            $authenticate_request = $this->authenticate_request();
            if ($authenticate_request['status'] = SUCCESS_STATUS) {
                $response['status'] = SUCCESS_STATUS;
                $curl_request = $this->form_curl_params($authenticate_request['request'], $authenticate_request['api_key'], $authenticate_request['signature'], $authenticate_request['url']);
                $response['data'] = $curl_request['data'];
            }
            if ($internal_request == true) {
                $response['data']['remarks'] = 'H-Authentication(HB)';
            }
        }

        return $response;
    }

    /**
     * Search Request
     * @param unknown_type $search_id
     */
    public function get_search_request($search_id)
    {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned
        if (empty($this->api_session_id) == true) {
            // return failure object as the login signature is not set
            return $response;
        }

        /* get search criteria based on search id */
        $search_data = $this->search_data($search_id);
        if ($search_data['status'] == SUCCESS_STATUS) {
            // hotel search RQ
            $search_request = $this->search_request($search_data['data']);

            if ($search_request['status'] = SUCCESS_STATUS) {
                $response['status'] = SUCCESS_STATUS;

                $curl_request = $this->form_curl_params($search_request['request'], $search_request['api_key'], $search_request['signature'], $search_request['url']);
                $response['data'] = $curl_request['data'];
            }
        }
        return $response;
    }

    /**
     * Forms the hotel details request
     * @param unknown_type $request
     */
    private function hotel_details_request($search_data, $params)
    {
        $request = array();
        $room_list_request = array();
        $hotel_code = $params['HotelCode'];
        $check_in_date = date('Y-m-d', strtotime($search_data['from_date']));
        $check_out_date = date('Y-m-d', strtotime($search_data['to_date']));
        $request['occupancies'] = $search_data['room_count'];
        $room_count = $search_data['room_count'];
        $child_count = 0;
        $adult_count = 0;
        $room_no = 1;
        for ($i = 0; $i < $room_count; $i++) {
            $adultTxt = '';
            $response_array[$i] = $room_no . '~' . $search_data['room_config'][$i]['NoOfAdults'] . '~' . $search_data['room_config'][$i]['NoOfChild'];

            if (isset($search_data['room_config'][$i]['NoOfAdults']) && !empty($search_data['room_config'][$i]['NoOfAdults']) && $search_data['room_config'][$i]['NoOfAdults'] != 0) {
                $no_of_adult = $search_data['room_config'][$i]['NoOfAdults'];
                $adultTxt = substr('~' . str_repeat('AD-30;', $no_of_adult), 0, -1);
            }
            $response_array[$i] .= $adultTxt;

            if (isset($search_data['room_config'][$i]['NoOfChild']) && !empty($search_data['room_config'][$i]['NoOfChild']) && $search_data['room_config'][$i]['NoOfChild'] != 0) {
                $no_of_child = $search_data['room_config'][$i]['NoOfChild'];
                $childStr = '';
                for ($j = 0; $j < $no_of_child; $j++) {
                    $child_age = array_shift($search_data['room_config'][$i]['ChildAge']);
                    $childStr .= ';' . 'CH' . '-' . $child_age;
                }
                $response_array[$i] .= $childStr;
            }
        }

        $count_no = array_count_values($response_array);

        $response_array = array();
        foreach ($count_no as $cnt_key => $count_arr_val) {
            $count_multiplier = $count_arr_val;
            if ($count_arr_val > 1) {
                $explode = explode('~', $cnt_key);
                //end -
                $explode_adult_child = explode(';', end($explode));
                $child_Adult = '';
                foreach ($explode_adult_child as $ac_k => $ac_v) {
                    //$explode_adult_child[$ac_k] = substr(str_repeat($ac_v.';', $count_multiplier), 0, -1);//AD-10;AD-30
                    $explode_adult_child[$ac_k] = $ac_v; //AD-10;AD-30
                }

                $child_Adult = implode(';', $explode_adult_child);
                //total length - 1 ; exclude last value
                //rest to be multiplied with $countArrVal
                $pax_list = array_slice($explode, 0, -1);

                foreach ($pax_list as $tk => $tv) {
                    if ($tk == 0) {
                        $pax_list[$tk] = $tv * $count_multiplier;
                    } else {
                        $pax_list[$tk] = $tv;
                    }
                }
                $response_array[] = implode('~', $pax_list) . '~' . $child_Adult;
            } else {
                $response_array[] = $cnt_key;
            }
        }
        $occu_str = implode(',', $response_array);
        $request['request'] = json_encode(array());
        $signature = hash("sha256", $this->config['api_key'] . $this->config['secrete'] . time());
        $request['api_header'] = array(
            'Api-key:' . trim($this->config['api_key']),
            'X-Signature:' . trim($signature),
            'X-Originating-Ip: 14.141.47.106',
            'Content-Type:application/json',
            'Accept: application/json',
            'Accept-Encoding: gzip'
        );
        $request['url'] = $this->config['api_url'] . 'hotels/' . $hotel_code . '?checkIn=' . $check_in_date . '&checkOut=' . $check_out_date . '&occupancies=' . $occu_str;
        $request['api_key'] = $this->config['api_key'];
        $request['remarks'] = 'GetHotelInfo(HB)';
        $request['status'] = SUCCESS_STATUS;
        return $request;
    }

    /**
     * Forms the room list request
     * @param unknown_type $request
     */
    private function room_list_request($params, $search_data)
    {
        $request = array();
        $room_list_request = array();
        $hotel_code = $params['HotelCode'];
        $check_in_date = date('Y-m-d', strtotime($search_data['from_date']));
        $check_out_date = date('Y-m-d', strtotime($search_data['to_date']));
        $request['occupancies'] = $search_data['room_count'];
        $room_count = $search_data['room_count'];
        $child_count = 0;
        $adult_count = 0;
        $room_no = 1;
        for ($i = 0; $i < $room_count; $i++) {
            $adultTxt = '';
            $response_array[$i] = $room_no . '~' . $search_data['room_config'][$i]['NoOfAdults'] . '~' . $search_data['room_config'][$i]['NoOfChild'];

            if (isset($search_data['room_config'][$i]['NoOfAdults']) && !empty($search_data['room_config'][$i]['NoOfAdults']) && $search_data['room_config'][$i]['NoOfAdults'] != 0) {
                $no_of_adult = $search_data['room_config'][$i]['NoOfAdults'];
                $adultTxt = substr('~' . str_repeat('AD-30;', $no_of_adult), 0, -1);
            }
            $response_array[$i] .= $adultTxt;

            if (isset($search_data['room_config'][$i]['NoOfChild']) && !empty($search_data['room_config'][$i]['NoOfChild']) && $search_data['room_config'][$i]['NoOfChild'] != 0) {
                $no_of_child = $search_data['room_config'][$i]['NoOfChild'];
                $childStr = '';
                for ($j = 0; $j < $no_of_child; $j++) {
                    $child_age = array_shift($search_data['room_config'][$i]['ChildAge']);
                    $childStr .= ';' . 'CH' . '-' . $child_age;
                }
                $response_array[$i] .= $childStr;
            }
        }

        $count_no = array_count_values($response_array);

        $response_array = array();
        foreach ($count_no as $cnt_key => $count_arr_val) {
            $count_multiplier = $count_arr_val;
            if ($count_arr_val > 1) {
                $explode = explode('~', $cnt_key);
                //end -
                $explode_adult_child = explode(';', end($explode));
                $child_Adult = '';
                foreach ($explode_adult_child as $ac_k => $ac_v) {
                    //$explode_adult_child[$ac_k] = substr(str_repeat($ac_v.';', $count_multiplier), 0, -1);//AD-10;AD-30
                    $explode_adult_child[$ac_k] = $ac_v; //AD-10;AD-30
                }

                $child_Adult = implode(';', $explode_adult_child);
                //total length - 1 ; exclude last value
                //rest to be multiplied with $countArrVal
                $pax_list = array_slice($explode, 0, -1);

                foreach ($pax_list as $tk => $tv) {
                    if ($tk == 0) {
                        $pax_list[$tk] = $tv * $count_multiplier;
                    } else {
                        $pax_list[$tk] = $tv;
                    }
                }
                $response_array[] = implode('~', $pax_list) . '~' . $child_Adult;
            } else {
                $response_array[] = $cnt_key;
            }
        }
        $occu_str = implode(',', $response_array);
        $request['request'] = json_encode(array());
        $signature = hash("sha256", $this->config['api_key'] . $this->config['secrete'] . time());
        $request['api_header'] = array(
            'Api-key:' . trim($this->config['api_key']),
            'X-Signature:' . trim($signature),
            'X-Originating-Ip: 14.141.47.106',
            'Content-Type:application/json',
            'Accept: application/json',
            'Accept-Encoding: gzip'
        );
        $request['url'] = $this->config['api_url'] . 'hotels/' . $hotel_code . '?checkIn=' . $check_in_date . '&checkOut=' . $check_out_date . '&occupancies=' . $occu_str;
        $request['api_key'] = $this->config['api_key'];
        $request['remarks'] = 'GetHotelRoom(HB)';
        $request['status'] = SUCCESS_STATUS;
        return $request;
    }

    /**
     * Forms the room list request
     * @param unknown_type $request
     */

    private function block_room_request(array $params, $search_id): array
    {
        $search_data = $this->search_data($search_id);
        $search_data = $search_data['data'];
        $block_room_request = array();
        $rate_recheck = array();
        $rate_type = '';
        $room_arr = [];
        $rate_recheck['language'] = 'ENG';
        $rate_recheck['upselling'] = 'False';

        foreach ($params['RoomUniqueId'] as $__room_key => $__rv) {
            $__room_value = Common_Hotel_v3::read_record($__rv);
            $__room_value = json_decode($__room_value[0], true);
            $rate_key_arr = json_decode($__room_value['rate_key'], true);
            //debug($rate_key_arr);
            $n = 0;
            foreach ($rate_key_arr as $key => $value) {
                $rate_recheck['rooms'][$n]['rateKey'] = $value;
                $n++;
            }
        }
        //debug($rate_recheck);exit;
        $block_room_request['request'] = json_encode($rate_recheck);
        $block_room_request['url'] = $this->config['api_url'] . 'checkrates';
        $block_room_request['remarks'] = 'Check Rates(HB)';
        $block_room_request['api_key_header'] = $this->config['api_key'];
        $block_room_request['request_method'] = 'post';
        $signature = hash("sha256", $this->config['api_key'] . $this->config['secrete'] . time());
        $block_room_request['api_header'] = array(
            'Api-key:' . trim($this->config['api_key']),
            'X-Signature:' . trim($signature),
            'X-Originating-Ip: 14.141.47.106',
            'Content-Type:application/json',
            'Accept: application/json',
            'Accept-Encoding: gzip'
        );

        $block_room_request['status'] = SUCCESS_STATUS;

        return $block_room_request;
    }

    /**
     * GetBooking Details Request
     * @param unknown_type $booking_params
     */

    public function getbooking_details_service_request(array $params): array
    {
        $request = array();
        $getbooking_details_request = array();
        $getbooking_details_request['EndUserIp'] = $this->end_user_ip;
        $getbooking_details_request['TokenId'] = $this->api_session_id;
        $getbooking_details_request['BookingId'] = $params['BookingId'];

        $request['request'] = json_encode($getbooking_details_request);
        $request['url'] = $this->config['EndPointUrl'] . 'GetBookingDetail';
        $request['remarks'] = 'Hotel GetBookingDetail(TBO)';
        $request['status'] = SUCCESS_STATUS;

        return $request;
    }

    /**
     * Forms the SendChangeRequest
     * @param unknown_type $request
     */

    private function format_send_change_request(array $params): array
    {
        $request = array();
        $send_change_request = [];
        $send_change_request['EndUserIp'] = $this->end_user_ip;
        $send_change_request['TokenId'] = $this->api_session_id;
        $send_change_request['BookingId'] = $params['booking_id'];
        $send_change_request['RequestType'] = 4;
        $send_change_request['Remarks'] = 'Process the Cancellation';

        $request['request'] = json_encode($send_change_request);
        $request['url'] = $this->config['EndPointUrl'] . 'SendChangeRequest';
        $request['remarks'] = 'Hotel SendChangeRequest(TBO)';
        $request['status'] = SUCCESS_STATUS;

        return $request;
    }

    /**
     * Forms the GetChangeRequestStatus
     * @param unknown_type $request
     */

    private function format_get_change_request_status_request($ChangeRequestId): array
    {
        $request = array();
        $get_change_request = [];
        $get_change_request['EndUserIp'] = $this->end_user_ip;
        $get_change_request['TokenId'] = $this->api_session_id;
        $get_change_request['ChangeRequestId'] = $ChangeRequestId;

        $request['request'] = json_encode($get_change_request);
        $request['url'] = $this->config['EndPointUrl'] . 'GetChangeRequestStatus';
        $request['remarks'] = 'Hotel GetChangeRequestStatus(TBO)';
        $request['status'] = SUCCESS_STATUS;
        return $request;
    }

    /**
     * Returns hotel List
     * @param unknown_type $search_id
     */

    public function get_hotel_list(string $hotel_raw_data, $search_id): array
    {
        ini_set('memory_limit', '250M');
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned
        //debug($hotel_raw_data);exit;
        $search_data = $this->search_data($search_id);
        if ($search_data['status'] == SUCCESS_STATUS) {
            $api_response = json_decode($hotel_raw_data, true);
            if ($this->valid_search_result($api_response) == TRUE) {

                $clean_format_data = $this->format_search_data_response($api_response, $search_data['data']);
                if ($clean_format_data) {
                    $response['status'] = SUCCESS_STATUS;
                } else {
                    $response['status'] = FAILURE_STATUS;
                }
            } else {
                $response['status'] = FAILURE_STATUS;
            }
            if ($response['status'] == SUCCESS_STATUS) {
                $response['data'] = $clean_format_data;
            }
        } else {
            $response['status'] = FAILURE_STATUS;
        }

        return $response;
    }

    /**
     * Hotel Details
     * @param unknown_type $request
     * @param unknown_type $search_id
     */

    public function get_hotel_details(array $request, $search_id): array
    {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned
        $search_data = $this->search_data($search_id);

        $hotel_details_request = $this->hotel_details_request($search_data['data'], $request);

        if ($hotel_details_request['status'] == SUCCESS_STATUS) {

            $hotel_details_response = $this->process_request($hotel_details_request['request'], $hotel_details_request['api_header'], $hotel_details_request['url'], $hotel_details_request['remarks']);
            //$hotel_details_response = $this->CI->custom_db->get_static_response (4979);
            $hotel_details_response = json_decode($hotel_details_response, true);

            if (valid_array($hotel_details_response['hotels']) == true && isset($hotel_details_response['hotels']['hotels']) == true && $hotel_details_response['hotels']['total'] > 0) {
                $response['status'] = SUCCESS_STATUS;
                //unset($hotel_details_response['HotelInfoResult']['TraceId']);

                $response['data']['HotelInfoResult'] = $this->format_hotel_details($hotel_details_response);
            } else {
                $response['message'] = 'Not Available';
            }
        } else {
            $response['status'] = FAILURE_STATUS;
        }

        return $response;
    }

    /**
     *  Room List
     * @param unknown_type $request
     * @param unknown_type $search_id
     */

    public function get_room_list(array $request, $search_id): array
    {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned
        $search_data = $this->search_data($search_id);
        //echo "roomlist";exit;
        $room_list_request = $this->room_list_request($request, $search_data['data']);
        if ($room_list_request['status'] == SUCCESS_STATUS) {

            $room_list_response = $this->process_request($room_list_request['request'], $room_list_request['api_header'], $room_list_request['url'], $room_list_request['remarks']);
            //$room_list_response = $this->CI->custom_db->get_static_response (4978);

            $room_list_response = json_decode($room_list_response, true);

            if (valid_array($room_list_response['hotels']) == true && isset($room_list_response['hotels']) == true && $room_list_response['hotels']['total'] >= 1) {
                $response['status'] = SUCCESS_STATUS;
                $response['data']['GetHotelRoomResult'] = $this->format_room_list_response($room_list_response, $request, $search_id);
            } else {
                $response['message'] = 'Not Available';
            }
        } else {
            $response['status'] = FAILURE_STATUS;
        }
        return $response;
    }

    /**
     *  Block Room
     * @param unknown_type $request
     * @param unknown_type $search_id
     */

    public function block_room(array $request, $search_id): array
    {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned

        $block_room_request = $this->block_room_request($request, $search_id);
        if ($block_room_request['status'] == SUCCESS_STATUS) {

            $block_room_response = $this->process_request($block_room_request['request'], $block_room_request['api_header'], $block_room_request['url'], $block_room_request['remarks'], $block_room_request['request_method']);
            //$block_room_response = $this->CI->custom_db->get_static_response (998);//Static Data//426

            $block_room_response = json_decode($block_room_response, true);

            if (valid_array($block_room_response) == true && isset($block_room_response['hotel']) == true) {
                $response['status'] = SUCCESS_STATUS;
                $response['data']['BlockRoomResult'] = $this->format_block_room_response($block_room_response, $request, $search_id);
            } else {
                $response['message'] = 'Not Available';
            }
        } else {
            $response['status'] = FAILURE_STATUS;
        }
        return $response;
    }

    /**
     * Process booking
     * @param array $booking_params
     */

    public function process_booking(
        array $booking_params,
        string $app_reference,
        int $sequence_number,
        int $search_id
    ): array {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned        
        $book_response = array();
        $update_hotel_data = [];
        $book_service_response = $this->run_book_service($booking_params, $app_reference, $search_id);

        if ($book_service_response['status'] == SUCCESS_STATUS) {
            $response['status'] = SUCCESS_STATUS;
            //update booking details
            $hotel_attributes = array();
            $booking_details = $book_service_response['data']['book_response']['data']['booking_details'];
            //debug($book_service_response);
            $hotel_attributes['HotelPolicy'] = '';
            //get hotel address
            $get_hotel_bed_static_data = $this->CI->custom_db->single_table_records('master_hotel_details_beta_hb', 'hotel_desc,address as add1, phone_number, accomodation_type_code', array('hotel_code' => $booking_details['hotel']['code']), 0, 1);
            $hotel_address = '';
            $hotel_contact_number = '';
            $accomodation_type_code  = '';
            if ($get_hotel_bed_static_data['data']) {
                $hotel_address = $get_hotel_bed_static_data['data'][0]['add1'];
                $hotel_contact_number = $get_hotel_bed_static_data['data'][0]['phone_number'];
                $accomodation_type_code = $get_hotel_bed_static_data['data'][0]['accomodation_type_code'];
            }

            $hotel_attributes['Address'] = $hotel_address;
            $hotel_attributes['ContactNumber'] = $hotel_contact_number;
            $hotel_attributes['AccomodationType'] = $accomodation_type_code;
            $booking_room = $booking_details['hotel']['rooms'];
            $booking_cancel_arr = array();
            foreach ($booking_room as $key => $value) {
                $cancellation_policy_arr = array();
                foreach ($value['rates']['cancellationPolicies']['cancellationPolicy'] as $c_key => $c_value) {
                    $cancellation_policy_arr[] = $c_value;
                }
                $booking_cancel_arr[] = $cancellation_policy_arr;
            }
            $booking_cancellation_policy = $this->format_room_cancellation_policy($booking_cancel_arr, $booking_details['hotel']['checkIn'], $booking_details['currency'], $booking_details['hotel']['totalNet']);

            $hotel_attributes['TM_Cancellation_Charge'] = $booking_cancellation_policy;
            //$hotel_attributes['TM_Room_Name']  =  
            $hotel_attributes['TM_Room_Name'] = $booking_params['RoomPriceBreakup'][0]['RoomTypeName'];
            $last_cancel_date = '';
            foreach ($booking_cancellation_policy as $key => $value) {
                if ($value['Charge'] == 0) {
                    $last_cancel_date = $value['FromDate'];
                }
            }
            $hotel_attributes['LastCancellationDate'] = $last_cancel_date;
            $hotel_attributes['HBVATNO'] = $booking_details['hotel']['supplier']['vatNumber'];
            $hotel_attributes['HBInvoice'] = $booking_details['hotel']['invoiceCompany'];
            $hotel_attributes['HBSupplier'] = $booking_details['hotel']['supplier'];
            $hotel_attributes['Booking_request'] = $book_service_response['data']['book_request'];
            $hotel_attributes['Booking_params'] = $booking_params;
            $hotel_attributes['Booking_Items'] = $book_service_response['data']['book_response'];
            $hotel_attributes = json_encode($hotel_attributes);

            $update_hotel_data['booking_id'] = 'TM-' . $booking_details['reference'];
            $update_hotel_data['booking_reference'] = $booking_details['reference'];
            $update_hotel_data['confirmation_reference'] = $booking_details['reference'];
            if ($booking_details['hotel']['supplier']['name']) {
                $update_hotel_data['supplier_code'] = $booking_details['hotel']['supplier']['name'];
            } elseif ($booking_details['invoiceCompany']) {
                if ($booking_details['invoiceCompany']['name']) {
                    $update_hotel_data['supplier_code'] = $booking_details['invoiceCompany']['name'];
                }
            }


            $update_hotel_data['hb_supplier_vat_id'] = $booking_details['hotel']['supplier']['vatNumber'];
            $update_hotel_data['attributes'] = $hotel_attributes;

            $this->CI->custom_db->update_record('hotel_booking_details', $update_hotel_data, array('app_reference' => trim($app_reference)));
            $booking_status = 'BOOKING_CONFIRMED';
        } else {
            $response['message'] = $book_service_response['message'];
            $booking_status = 'BOOKING_FAILED';
            $hotel_attributes['Booking_request'] = $book_service_response['data']['book_request'];
            $hotel_attributes['Booking_params'] = $booking_params;
            $hotel_attributes = json_encode($hotel_attributes);
            $update_hotel_data['attributes'] = $hotel_attributes;
            $this->CI->custom_db->update_record('hotel_booking_details', $update_hotel_data, array('app_reference' => trim($app_reference)));
            //Log Exception
            $exception_log_message = '';
            $this->CI->exception_logger->log_exception($app_reference, $this->booking_source . '- (<strong>Book</strong>)', $exception_log_message, $book_service_response['error']);
        }
        //Update the Booking Status
        $this->update_booking_status($app_reference, $booking_status);
        //exit;
        return $response;
    }

    /**
     * Book Service API call
     * @param unknown_type $booking_params
     */

    private function run_book_service(
        array $booking_params,
        string $app_reference,
        string $search_id
    ): array {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned
        $book_service_request = $this->book_service_request($booking_params, $search_id);
        if ($book_service_request['status'] == SUCCESS_STATUS) {

            $book_service_response = $this->xml_process_request($book_service_request['url'], $book_service_request['request'], $book_service_request['api_header'], $book_service_request['remarks']);
            //echo $book_service_response;exit;
            //$book_service_response = $this->CI->custom_db->get_static_response (4583);


            $book_service_response = utf8_encode($book_service_response);
            $book_service_response = Converter::createArray($book_service_response);

            //$book_service_response = json_decode($book_service_response, true);

            if (isset($book_service_response['bookingRS']['booking']) == true && valid_array($book_service_response['bookingRS']['booking']) == true && $book_service_response['bookingRS']['booking']['@attributes']['status'] == 'CONFIRMED') {
                $response['status'] = SUCCESS_STATUS;
                $book_response = $this->format_booking_response($book_service_response);

                $response['data']['book_response'] = $book_response;
            } else {
                $error_message = '';
                if (isset($book_service_response['error'])) {
                    $error_message = $book_service_response['error']['message'];
                }
                if (empty($error_message) == true) {
                    $error_message = 'Booking Failed';
                }

                $response['message'] = $error_message;
                //Log Exception
                $exception_log_message = '';
                $this->CI->exception_logger->log_exception($app_reference, $this->booking_source . '- (<strong>Book</strong>)', $exception_log_message, $book_service_response);
            }
        } else {

            $response['status'] = FAILURE_STATUS;
        }
        $response['data']['book_request'] = $book_service_request;
        //debug($response);exit;
        return $response;
    }

    /**
     * Get Booking Details
     * @param unknown_type $booking_details
     */

    public function run_getbooking_details_service(array $booking_details): array
    {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned
        $getbooking_details_service_request = $this->getbooking_details_service_request($booking_details);
        if ($getbooking_details_service_request['status'] == SUCCESS_STATUS) {

            $getbooking_details_service_response = $this->process_request($getbooking_details_service_request['request'], $getbooking_details_service_request['url'], $getbooking_details_service_request['remarks']);

            //$getbooking_details_service_response = $this->CI->custom_db->get_static_response (659);//Static book data//659

            $getbooking_details_service_response = json_decode($getbooking_details_service_response, true);

            if (isset($getbooking_details_service_response['GetBookingDetailResult']) == true && valid_array($getbooking_details_service_response['GetBookingDetailResult']) == true && $getbooking_details_service_response['GetBookingDetailResult']['ResponseStatus'] == SUCCESS_STATUS && $getbooking_details_service_response['GetBookingDetailResult']['VoucherStatus'] == SUCCESS_STATUS) {
                $response['status'] = SUCCESS_STATUS;
                $response['data']['booking_details_response'] = $getbooking_details_service_response['GetBookingDetailResult'];
            } else {
                $error_message = '';
                if (isset($getbooking_details_service_response['GetBookingDetailResult']['Error']['ErrorMessage'])) {
                    $error_message = $getbooking_details_service_response['GetBookingDetailResult']['Error']['ErrorMessage'];
                }
                if (empty($error_message) == true) {
                    $error_message = 'GetBookingDetails Failed';
                }
                $response['message'] = $error_message;
            }
        } else {
            $response['status'] = FAILURE_STATUS;
        }
        return $response;
    }

    /**
     * Process Cancel Booking
     * Online Cancellation
     */

    public function admin_cancel_booking(array $request): array
    {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned
        $app_reference = trim($request['AppReference']);
        $get_change_request_status_response = [];

        $booking_details = $this->CI->custom_db->single_table_records('hotel_booking_details', '*', array('app_reference' => $app_reference));
        //$booking_details['data'][0]['status'] = 'BOOKING_CONFIRMED';
        if ($booking_details['status'] == SUCCESS_STATUS && $booking_details['data'][0]['status'] == 'BOOKING_CONFIRMED') {
            $booking_details = $booking_details['data'][0];
            $request_params = array();

            $request_params['booking_reference'] = $booking_details['booking_reference'];
            $booking_ite_details = $this->CI->custom_db->single_table_records('hotel_booking_itinerary_details', '*', array('app_reference' => $app_reference));
            $booking_ite_details = $booking_ite_details['data'][0];
            //$total_booking_amount = round($booking_ite_details['total_fare']);//booking amount
            $total_booking_amount = round($booking_ite_details['total_fare'] + $booking_details['domain_markup']);
            $send_change_request_response = $this->send_change_request($request_params);

            if ($send_change_request_response['status'] == SUCCESS_STATUS) {
                $cancel_response = $send_change_request_response['data']['send_change_response'];
                //NotSet = 0,Pending = 1,InProgress = 2,Processed = 3,Rejected = 4
                $ChangeRequestId = 0;

                switch (strtolower($cancel_response['booking']['status'])) {
                    case 'cancelled':
                        $ChangeRequestId = 3;
                        break;
                    case 'confirmed':
                        $ChangeRequestId = 1;
                        break;
                    default:
                        $ChangeRequestId = 0;
                        break;
                }

                $response['status'] = SUCCESS_STATUS;
                $api_cancel_charge = round($cancel_response['cancellation_charges']['amount']);
                /** cancellation Charge Start* */
                $get_cancellation_details_db = json_decode($booking_details['attributes'], true);
                $tm_cancel_charge = $get_cancellation_details_db['Booking_params']['RoomPriceBreakup'][0]['TM_Cancellation_Charge'];

                //$tm_last_cancel_date = date('Y-m-d',strtotime($get_cancellation_details_db['TM_LastCancellation_date']));

                $tm_last_cancel_date = date('Y-m-d');
                foreach ($tm_cancel_charge as $l_key => $l_value) {
                    if ($l_value['Charge'] == 0) {
                        $tm_last_cancel_date = date('Y-m-d', strtotime($l_value['FromDate']));
                    }
                }

                $current_date = date('Y-m-d');
                $cancel_charge = 0;

                $tm_cancel_charge = array_reverse($tm_cancel_charge);

                if ($tm_last_cancel_date > $current_date) {
                    $cancel_charge = 0;
                } else {
                    foreach ($tm_cancel_charge as $c_key => $c_value) {
                        //if($c_value['Charge']!=0){
                        $db_from_date = date('Y-m-d', strtotime($c_value['FromDate']));
                        $db_to_date = date('Y-m-d', strtotime($c_value['ToDate']));

                        if ($current_date >= $db_from_date && $current_date <= $db_to_date) {
                            if ($c_value['ChargeType'] == 1) {
                                $cancel_charge = round($c_value['Charge']);
                            } elseif ($c_value['ChargeType'] == 2) {
                                $cancel_charge = round($total_booking_amount);
                            }
                        }

                        //}
                    }
                }
                /*                 * End* */

                if ($cancel_charge > 0) {
                    $ChangeRequestId = 2;
                } else {
                    $ChangeRequestId = $ChangeRequestId;
                }
                $get_change_request_status_response['StatusDescription'] = $this->get_cancellation_status_description($ChangeRequestId);
                $cancellation_details = array();
                $cancellation_details['HotelChangeRequestStatusResult'] = $get_change_request_status_response;

                //calculating cancellation charge;
                //$price =abs($total_booking_amount - $api_cancel_charge);
                $price = abs($total_booking_amount - $cancel_charge);
                $cancellation_details['HotelChangeRequestStatusResult']['RefundedAmount'] = $price;
                $cancellation_details['HotelChangeRequestStatusResult']['CancellationCharge'] = $cancel_charge;
                $cancellation_details['HotelChangeRequestStatusResult']['ChangeRequestId'] = $ChangeRequestId;
                $cancellation_details['HotelChangeRequestStatusResult']['ChangeRequestStatus'] = $ChangeRequestId;
                $get_change_request_status_response['ChangeRequestId'] = $ChangeRequestId;
                //Update Cancellation Details
                $get_change_request_status_response['ChangeRequestStatus'] = $get_change_request_status_response['StatusDescription'];

                $response['data']['CancellationDetails'] = $get_change_request_status_response;
                $response['data']['update_cancel_details'] = $cancellation_details;
            } else {
                $response['message'] = $send_change_request_response['message'];
            }
        } else {
            $response['message'] = 'Invalid Request';
        }
        //  debug($response);exit;
        return $response;
    }

    /**
     * Process Cancel Booking
     * Online Cancellation
     */

    public function cancel_booking(array $request): array
    {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned
        $app_reference = trim($request['AppReference']);
        $cancel_details_data = [];
        $get_change_request_status_response = [];


        $booking_details = $this->CI->custom_db->single_table_records('hotel_booking_details', '*', array('app_reference' => $app_reference));
        //$booking_details['data'][0]['status'] = 'BOOKING_CONFIRMED';

        if ($booking_details['status'] == SUCCESS_STATUS && $booking_details['data'][0]['status'] == 'BOOKING_CONFIRMED') {
            $booking_details = $booking_details['data'][0];
            $request_params = array();
            $request_params['booking_reference'] = $booking_details['booking_reference'];
            $booking_ite_details = $this->CI->custom_db->single_table_records('hotel_booking_itinerary_details', '*', array('app_reference' => $app_reference));
            $booking_ite_details = $booking_ite_details['data'][0];
            //$total_booking_amount = round($booking_ite_details['total_fare']);//booking amount
            $total_booking_amount = round($booking_ite_details['total_fare'] + $booking_details['domain_markup']);

            $send_change_request_response = $this->send_change_request($request_params);

            if ($send_change_request_response['status'] == SUCCESS_STATUS) {
                $cancel_response = $send_change_request_response['data']['send_change_response'];
                //NotSet = 0,Pending = 1,InProgress = 2,Processed = 3,Rejected = 4
                $ChangeRequestId = 0;

                switch (strtolower($cancel_response['booking']['status'])) {
                    case 'cancelled':
                        $ChangeRequestId = 3;
                        break;
                    case 'confirmed':
                        $ChangeRequestId = 1;
                        break;
                    default:
                        $ChangeRequestId = 0;
                        break;
                }

                $response['status'] = SUCCESS_STATUS;
                //calculating cancellation charge;
                /** cancellation Charge Start* */
                $get_cancellation_details_db = json_decode($booking_details['attributes'], true);

                $tm_cancel_charge = $get_cancellation_details_db['Booking_params']['RoomPriceBreakup'][0]['TM_Cancellation_Charge'];
                $tm_last_cancel_date = date('Y-m-d');
                foreach ($tm_cancel_charge as $l_key => $l_value) {
                    if ($l_value['Charge'] == 0) {
                        $tm_last_cancel_date = date('Y-m-d', strtotime($l_value['FromDate']));
                    }
                }
                //echo "tm_last_cancel_date".$tm_last_cancel_date;
                //$tm_last_cancel_date = date('Y-m-d',strtotime($get_cancellation_details_db['TM_LastCancellation_date']));
                $current_date = date('Y-m-d');
                $cancel_charge = 0;

                $tm_cancel_charge = array_reverse($tm_cancel_charge);
                //debug($tm_cancel_charge);

                if ($tm_last_cancel_date > $current_date) {
                    $cancel_charge = 0;
                } else {
                    foreach ($tm_cancel_charge as $c_key => $c_value) {
                        //if($c_value['Charge']!=0){
                        $db_from_date = date('Y-m-d', strtotime($c_value['FromDate']));
                        $db_to_date = date('Y-m-d', strtotime($c_value['ToDate']));

                        if ($current_date >= $db_from_date && $current_date <= $db_to_date) {
                            if ($c_value['ChargeType'] == 1) {
                                $cancel_charge = round($c_value['Charge']);
                            } elseif ($c_value['ChargeType'] == 2) {
                                $cancel_charge = round($total_booking_amount);
                            }
                        }

                        //}
                    }
                }
                /*                 * End* */
                // echo "cancel_charge  ".$cancel_charge;
                // echo "ChangeRequestId ".$ChangeRequestId;

                if ($cancel_charge > 0) {
                    $ChangeRequestId = 2;
                } else {
                    $ChangeRequestId = $ChangeRequestId;
                }
                $get_change_request_status_response['StatusDescription'] = $this->get_cancellation_status_description($ChangeRequestId);

                $cancellation_details = array();
                $cancellation_details['HotelChangeRequestStatusResult'] = $get_change_request_status_response;

                //calculate cancellation charge according to Travelomatix, because we chaning the cancellation date by -1 , -2 days

                $api_cancel_charge = round($cancel_response['cancellation_charges']['amount']);

                //$price =abs($total_booking_amount - $api_cancel_charge);
                //$price =abs($total_booking_amount - $cancel_charge);
                $price = abs($total_booking_amount - $cancel_charge);
                $cancellation_details['HotelChangeRequestStatusResult']['RefundedAmount'] = $price;
                $cancellation_details['HotelChangeRequestStatusResult']['CancellationCharge'] = $cancel_charge;
                $cancellation_details['HotelChangeRequestStatusResult']['ChangeRequestId'] = $ChangeRequestId;
                $cancellation_details['HotelChangeRequestStatusResult']['ChangeRequestStatus'] = $ChangeRequestId;
                $get_change_request_status_response['ChangeRequestId'] = $ChangeRequestId;
                $get_change_request_status_response['RefundedAmount'] = $price;
                $get_change_request_status_response['CancellationCharge'] = $cancel_charge;

                $this->CI->hotel_model_v3->update_cancellation_details($app_reference, $cancellation_details);

                //Process the refund to client
                if ($ChangeRequestId == 3) { //if refund processed from supplier
                    $get_change_request_status_response['ChangeRequestStatus'] = $get_change_request_status_response['StatusDescription'];
                    $response['data']['CancellationDetails'] = $this->CI->common_hotel_v3->update_domain_cancellation_refund_details($get_change_request_status_response, $app_reference);
                } else {
                    $get_change_request_status_response['ChangeRequestStatus'] = $get_change_request_status_response['StatusDescription'];

                    $response['data']['CancellationDetails'] = $get_change_request_status_response;
                }
            } else {
                $response['message'] = $send_change_request_response['message'];
            }
        } elseif ($booking_details['status'] == SUCCESS_STATUS && $booking_details['data'][0]['status'] == 'BOOKING_CANCELLED') {
            $booking_details = $booking_details['data'][0];
            $app_reference = $booking_details['app_reference'];
            $get_cancellation_details = $this->CI->custom_db->single_table_records('hotel_cancellation_details', '*', array('app_reference' => $app_reference));
            if ($get_cancellation_details['status'] == true) {
                $cancel_details = $get_cancellation_details['data'][0];
                $response['status'] = SUCCESS_STATUS;
                $cancel_details_data['ChangeRequestId'] = $cancel_details['ChangeRequestId'];
                $cancel_details_data['ChangeRequestStatus'] = $cancel_details['ChangeRequestStatus'];
                $cancel_details_data['RefundedAmount'] = $cancel_details['refund_amount'];
                $cancel_details_data['CancellationCharge'] = $cancel_details['cancellation_charge'];
                $cancel_details_data['StatusDescription'] = $cancel_details['refund_status'];
                $response['data']['CancellationDetails'] = $cancel_details_data;
                $response['message'] = 'Booking Already Cancelled';
            } else {
                $response['message'] = 'Invalid Request';
            }
        } else {
            $response['message'] = 'Invalid Request';
        }

        return $response;
    }

    /**
     * Send ChangeRequest
     * @param unknown_type $booking_details
     * //ChangeRequestStatus: NotSet = 0,Unassigned = 1,Assigned = 2,Acknowledged = 3,Completed = 4,Rejected = 5,Closed = 6,Pending = 7,Other = 8
     */

    private function send_change_request(array $request_params): array
    {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned
        //$send_change_request = $this->format_send_change_request($request_params);
        $send_change_request = $this->cancel_request($request_params);
        if ($send_change_request['status'] == SUCCESS_STATUS) {
            $send_change_response = $this->delete_process_request($send_change_request['url'], $send_change_request['api_header'], $send_change_request['remarks']);
            //$send_change_response = $this->CI->custom_db->get_static_response (31284);

            $send_change_response = json_decode($send_change_response, true);
            if (!isset($send_change_response['error'])) {
                $response['status'] = SUCCESS_STATUS;
                $response['data']['send_change_response'] = $send_change_response;
            } else {
                $error_message = '';
                if (isset($send_change_response['error']['message'])) {
                    $error_message = $send_change_response['error']['message'];
                } else if (isset($send_change_response['error'])) {
                    $error_message = $send_change_response['error'];
                }
                if (empty($error_message) == true) {
                    $error_message = 'Cancellation Failed';
                }
                $response['message'] = $error_message;
            }
        } else {
            $response['status'] = FAILURE_STATUS;
        }

        return $response;
    }

    /**
     * Form the cancellation request
     */

    private function cancel_request(array $booking_reference): array
    {
        $reference = $booking_reference['booking_reference'];
        $cancel_request = array();
        $cancel_request['url'] = $this->config['api_url'] . 'bookings/' . $reference . '?cancellationFlag=CANCELLATION';
        $cancel_request['remarks'] = 'Cancel Booking(HB)';
        $signature = hash("sha256", $this->config['api_key'] . $this->config['secrete'] . time());
        $cancel_request['api_header'] = array(
            'Api-key:' . trim($this->config['api_key']),
            'X-Signature:' . trim($signature),
            'X-Originating-Ip: 14.141.47.106',
            'Content-Type:application/json',
            'Accept: application/json',
            'Accept-Encoding: gzip'
        );
        $cancel_request['request_method'] = 'delete';
        $cancel_request['status'] = SUCCESS_STATUS;
        //debug($cancel_request);
        return $cancel_request;
    }

    public function get_room_facilities(string $hotel_code, string $room_code): array
    {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned
        $hb_hotel_facilities_description = $this->CI->custom_db->single_table_records('hb_hotel_facilities_description', '*');
        $hotel_facilities_description = array();
        if ($hb_hotel_facilities_description['status'] == SUCCESS_STATUS) {
            foreach ($hb_hotel_facilities_description['data'] as $h_facility) {
                $hotel_facilities_description[$h_facility['facility_code']] = $h_facility;
            }
        }
        $hb_facilities_group = $this->CI->custom_db->single_table_records('hb_facilities_group', '*');
        $hotel_facilities_group = array();
        if ($hb_facilities_group['status'] == SUCCESS_STATUS) {
            foreach ($hb_facilities_group['data'] as $h_facility_group) {
                $hotel_facilities_group[$h_facility_group['code']] = $h_facility_group;
            }
        }
        //$room_code = 'JSU.KG';
        if (empty($hotel_code) == false && empty($room_code) == false) {
            $static_hotel_facility_info =  $this->CI->custom_db->single_table_records('master_hotel_details_beta_hb', 'room_faci', array('hotel_code' => $hotel_code));
            $room_faciliies = array();
            $room_stay_faciliies = array();
            if ($static_hotel_facility_info['status'] == SUCCESS_STATUS) {
                if (empty($static_hotel_facility_info['data'][0]['room_faci']) == false) {
                    $room_facility = json_decode($static_hotel_facility_info['data'][0]['room_faci'], true);
                    foreach ($room_facility as $facility) {
                        //debug($facility);
                        if ($facility['roomCode'] == $room_code) {
                            if (isset($facility['roomFacilities'])) {
                                foreach ($facility['roomFacilities'] as $r_facility) {
                                    $room_faciliies[] = $hotel_facilities_description[$r_facility['facilityCode']]['description'];
                                }
                            }
                            if (isset($facility['roomStayFacilities'])) {
                                foreach ($facility['roomStayFacilities'] as $r_facility) {
                                    $room_stay_faciliies[] = $hotel_facilities_description[$r_facility['facilityCode']]['description'];
                                }
                            }
                        }
                    }
                }
            }
        }
        if (valid_array($room_faciliies) || (valid_array($room_stay_faciliies))) {
            $response['status'] = SUCCESS_STATUS; // Status Of Operation
            $roo_all_facilities = array_merge($room_faciliies, $room_stay_faciliies);
            $response['data']['room_facilities'] = $roo_all_facilities;
        }
        return $response;
    }

    /**
     * GetChangeRequestStatus
     * @param unknown_type $booking_details
     * //ChangeRequestStatus: NotSet = 0,Unassigned = 1,Assigned = 2,Acknowledged = 3,Completed = 4,Rejected = 5,Closed = 6,Pending = 7,Other = 8
     */

    public function get_change_request_status(int $ChangeRequestId): array
    {
        $response = [];
        $response['status'] = FAILURE_STATUS; // Status Of Operation
        $response['message'] = ''; // Message to be returned
        $response['data'] = array(); // Data to be returned
        $get_change_request_status_request = $this->format_get_change_request_status_request($ChangeRequestId);
        if ($get_change_request_status_request['status'] == SUCCESS_STATUS) {
            $get_change_request_status_response = $this->process_request($get_change_request_status_request['request'], $get_change_request_status_request['url'], $get_change_request_status_request['remarks']);

            //$get_change_request_status_response = $this->CI->custom_db->get_static_response (35);

            $get_change_request_status_response = json_decode($get_change_request_status_response, true);

            if (valid_array($get_change_request_status_response) == true && isset($get_change_request_status_response['HotelChangeRequestStatusResult']) == true && $get_change_request_status_response['HotelChangeRequestStatusResult']['ResponseStatus'] == SUCCESS_STATUS) {
                $response['status'] = SUCCESS_STATUS;
                $response['data']['get_change_response'] = $get_change_request_status_response['HotelChangeRequestStatusResult'];
            }
        } else {
            $response['status'] = FAILURE_STATUS;
        }
        return $response;
    }

    /**
     * Formates Search Response
     * Enter description here ...
     * @param unknown_type $search_result
     * @param unknown_type $search_data
     */

    public function format_search_data_response(array $search_result, array $search_data): array
    {
        //debug($search_result);exit;
        //exit;
        $response = [];
        $hotel_code = [];
        ini_set('memory_limit', '250M');
        $Results = $search_result['hotels']['hotels'];
        $hotel_list = array();
        $TraceId = $search_result['auditData']['token'];
        //getting hotel bed static master data      
        $string_hotel_code = array();
        foreach ($Results as $re_key => $re_val) {
            //$hotel_code []=$re_val['code'];
            $hotel_code[] = "'" . $re_val['code'] . "'";
        }
        $hotel_string = implode(",", $hotel_code);

        $hotel_bed_master_data = array();
        //exit;
        $hotel_bed_static_data = array();
        $hb_hotel_facilities_description = $this->CI->custom_db->single_table_records('hb_hotel_facilities_description', '*');
        $hotel_facilities_description = array();
        if ($hb_hotel_facilities_description['status'] == SUCCESS_STATUS) {
            foreach ($hb_hotel_facilities_description['data'] as $h_facility) {
                $hotel_facilities_description[$h_facility['facility_code']] = $h_facility;
            }
        }
        $hb_facilities_group = $this->CI->custom_db->single_table_records('hb_facilities_group', '*');
        $hotel_facilities_group = array();
        if ($hb_facilities_group['status'] == SUCCESS_STATUS) {
            foreach ($hb_facilities_group['data'] as $h_facility_group) {
                $hotel_facilities_group[$h_facility_group['code']] = $h_facility_group;
            }
        }
        $hotel_bed_static_data = $this->get_hotel_info($hotel_code, $hotel_facilities_description, $hotel_facilities_group);
        $trip_adv_hotel_data = array();
        //$get_hotel_bed_trip_adv = $this->get_hotel_bed_trip_adv($hotel_string);
        $get_hotel_bed_trip_adv = array();
        if ($get_hotel_bed_trip_adv) {
            foreach ($get_hotel_bed_trip_adv as $h_key => $trip) {
                $trip_json = json_decode($trip['tri_adv_hotel']);
                $trip_adv_hotel_data[$search_data['CountryCode'] . '_' . $trip['h_code']]['image_url'] = $trip_json[0]->rating_image_url;
                $trip_adv_hotel_data[$search_data['CountryCode'] . '_' . $trip['h_code']]['trip_rating'] = $trip_json[0]->rating;
            }
        }
        //exit;
        //$min_room_price_arr = $this->get_min_room_price($search_result);
        $sort_image_list = array();
        // debug($Results);exit;
        foreach ($Results as $result_k => $result_v) {
            $key = array();
            $key['key'][$result_k]['booking_source'] = $this->booking_source;
            $key['key'][$result_k]['TraceId'] = $TraceId;
            $key['key'][$result_k]['ResultIndex'] = $result_k + 1;
            $key['key'][$result_k]['HotelCode'] = $result_v['code'];

            $key['key'][$result_k]['HotelName'] = $result_v['name'];
            //$hotel_list[$result_k] = $result_v;
            $hotel_list[$result_k]['HotelCode'] =  (string)$result_v['code'];
            $hotel_list[$result_k]['OrginalHotelCode'] =  (string)$result_v['code'];
            $hotel_list[$result_k]['HotelName'] = $result_v['name'];
            if (isset($result_v['categoryName']) && !empty(intval($result_v['categoryName']))) {
                $hotel_list[$result_k]['StarRating'] = $star_rating = intval($result_v['categoryName']);
            } else if (!empty(intval(preg_replace('/[^0-9]+/', '', $result_v['categoryName']), 10))) {
                $hotel_list[$result_k]['StarRating'] = $star_rating = intval(preg_replace('/[^0-9]+/', '', $result_v['categoryName']), 10);
            } else {
                $hotel_list[$result_k]['StarRating'] = $star_rating = 0;
            }
            $key['key'][$result_k]['StarRating'] = $star_rating;

            //calculating price
            $room_type_combination_arr = array();
            $single_room_total_price = array();
            $hkey = $result_v['code'];
            $actual_rooms = $result_v['rooms'][0]['rates'][0]['rooms'];
            if ($actual_rooms > 1) {
                $total_min_price = $result_v['minRate'] / $actual_rooms;
            } else {
                $total_min_price = $result_v['minRate'];
            }
            $API_Currency = $result_v['currency'];
            $hotel_picture = '';
            $hotel_address = $result_v['destinationName'];
            $images = array();
            if (isset($hotel_bed_static_data['info'][$hkey])) {
                if ($hotel_bed_static_data['info'][$hkey]['img']) {
                    $hotel_picture = $hotel_bed_static_data['info'][$hkey]['img'];
                    //$hotel_list[$result_k]['ImageOrder'] = 1;
                } else {
                    //$hotel_list[$result_k]['ImageOrder'] = 0;
                }
                if ($hotel_bed_static_data['info'][$hkey]['address']) {
                    $hotel_address = $hotel_bed_static_data['info'][$hkey]['address'];
                }
                if (empty($hotel_bed_static_data['info'][$hkey]['images']) == false) {
                    $images = json_decode($hotel_bed_static_data['info'][$hkey]['images'], true);
                }
            }
            $image_list = array();
            $i = 0;
            foreach ($images as $image) {
                if ($i < 10) {
                    $url = "https://photos.hotelbeds.com/giata/" . $image['path'];
                    //if (file_get_contents($url) != false) {
                    $image_list[] = $url;
                    $i++;
                    //}
                }
            }
            //debug($image_list);exit;
            $sort_image_list[$result_k] = $hotel_list[$result_k]['ImageOrder'];
            $hotel_list[$result_k]['HotelPicture'] = $hotel_picture;
            $hotel_list[$result_k]['Images'] = $image_list;
            $hotel_list[$result_k]['HotelAddress'] = $hotel_address;
            $hotel_list[$result_k]['HotelContactNo'] = array();
            if ($result_v['latitude'] != '') {
                $hotel_list[$result_k]['Latitude'] = $result_v['latitude'];
            } else {
                $hotel_list[$result_k]['Latitude'] = '';
                //$hotel_bed_master_data[$search_data['CountryCode']].'_'.$result_v['code']['Latitude'];    
            }
            if ($result_v['longitude'] != '') {
                $hotel_list[$result_k]['Longitude'] = $result_v['longitude'];
            } else {
                $hotel_list[$result_k]['Longitude'] = '';
                //$hotel_bed_master_data[$search_data['CountryCode']].'_'.$result_v['code']['Longitude'];
            }
            $trip_adv_url = '';
            $trip_adv_rating = '';
            if (isset($trip_adv_hotel_data[$search_data['CountryCode'] . '_' . $result_v['code']])) {
                $trip_adv_url = $trip_adv_hotel_data[$search_data['CountryCode'] . '_' . $result_v['code']]['image_url'];
                $trip_adv_rating = $trip_adv_hotel_data[$search_data['CountryCode'] . '_' . $result_v['code']]['trip_rating'];
            }

            $hotel_list[$result_k]['HotelCategory'] = '';
            $hotel_list[$result_k]['trip_adv_url'] = $trip_adv_url;
            $hotel_list[$result_k]['trip_rating'] = $trip_adv_rating;
            $amenitie_arr = array();
            $cache_facility = array();

            if (isset($hotel_bed_static_data['facility'][$hkey])) {
                $cache_facility = $hotel_bed_static_data['facility'][$hkey];
                if ($cache_facility) {
                    foreach ($cache_facility['Facilities'] as $f_key => $f_value) {

                        $amenitie_arr[] = $f_value['name'];
                    }
                }
            }
            // Promotions start
            $promotions = array();
            $offers = array();
            $free_cancel_date = '';
            $free_cancel_date1 = '';
            $free_cancellation = false;
            //debug($hotel_detail);
            $check_cancel_date = array();

            foreach ($result_v['rooms'] as $rp_key => $room_promotion) {

                if (isset($room_promotion['rates'][0]['promotions'])) {
                    if (is_array($room_promotion['rates'][0]['promotions']) && !empty($room_promotion['rates'][0]['promotions'])) {

                        $promotions_arr = array();
                        $promotions_arr = $room_promotion['rates'][0]['promotions'][0];
                        $promotions['status'] = 1;
                        $promotions['name'] = $promotions_arr['name'];
                        if (isset($promotions_arr['remark'])) {
                            $promotions['remark'] = $promotions_arr['remark'];
                        }
                        break;
                    } else {
                        $promotions['status'] = 0;
                    }
                } else {
                    $promotions['status'] = 0;
                }
                if (isset($room_promotion['rates'][0]['offers'])) {

                    if (is_array($room_promotion['rates'][0]['offers']) && !empty($room_promotion['rates'][0]['offers'])) {
                        //debug($room_promotion); exit;
                        $offers_arr = array();
                        $offers_arr = $room_promotion['rates'][0]['offers'][0];
                        $offers['status'] = 1;
                        $offers['name'] = $offers_arr['name'];
                        $offers['amount'] = $offers_arr['amount'];
                        break;
                    } else {
                        $offers['status'] = 0;
                    }
                } else {
                    $offers['status'] = 0;
                }
                if (isset($room_promotion['rates'][0]['cancellationPolicies']) && valid_array($room_promotion['rates'][0]['cancellationPolicies'])) {
                    foreach ($room_promotion['rates'][0]['cancellationPolicies'] as $c_key => $c_value) {
                        $check_cancel_date[] = date('Y-m-d', strtotime($c_value['from']));
                    }
                }
            }


            //finding disocunt percentage           



            //$hotel_list[$result_k]['PromoOffer'] = $offers;
            if ($check_cancel_date) {
                $max_cancel_date = max($check_cancel_date);
                $free_cancel_date = date('Y-m-d', strtotime("-" . $this->api_cancellation_policy_day . " day", strtotime($max_cancel_date)));
                $free_cancel_date1 = $max_cancel_date;
            }
            if ($free_cancel_date) {
                $current_date = date('Y-m-d');
                $check_date = date('Y-m-d', strtotime($free_cancel_date));
                if ($check_date > $current_date) {
                    $free_cancel_date = $free_cancel_date;
                    $free_cancellation = true;
                }
            }
            $hotel_list[$result_k]['free_cancellation'] = $free_cancellation;
            $hotel_list[$result_k]['HotelAmenities'] = array_values(array_unique($amenitie_arr));
            $hotel_list[$result_k]['Price'] = $this->format_HB_price($total_min_price, '', $API_Currency);
            $hotel_list[$result_k]['HotelLocation'] = $result_v['zoneName']; // after price
            //$hotel_list[$result_k]['HotelPromotion'] = ceil($disocunt_percentage);
            $hotel_list[$result_k]['Free_cancel_date'] = $free_cancel_date;
            if ($promotions['status'] == 1) {
                $disocunt_percentage = 0;
                if (isset($promotions['amount']) && empty($promotions['amount']) == false) {
                    $disocunt_percentage = ceil($total_min_price / $promotions['amount']);
                }
                $hotel_list[$result_k]['HotelPromotion'] = $disocunt_percentage;
                $hotel_list[$result_k]['HotelPromotionContent'] = $promotions['name'];
            } else {
                $disocunt_percentage = 0;
                if (isset($offers['amount']) && empty($offers['amount']) == false) {
                    $disocunt_percentage = ceil($total_min_price / $offers['amount']);
                }
                $hotel_list[$result_k]['HotelPromotion'] = $disocunt_percentage;
                $hotel_list[$result_k]['HotelPromotionContent'] = $offers['name'];
            }
            $hotel_list[$result_k]['ResultToken'] = serialized_data($key['key']);
        }
        array_multisort($sort_image_list, SORT_DESC, $hotel_list);
        //debug($hotel_list);exit;
        $response['HotelSearchResult']['HotelResults'] = $hotel_list;
        $response['HotelSearchResult']['CityId'] = $search_data['hotel_origin'];
        //debug($response);exit;
        return $response;
    }
    private function format_CANCELLATION_Price(float $price, string $API_Currency): float
    {
        $conversion_amount = $GLOBALS['CI']->domain_management_model->get_currency_conversion_rate($API_Currency);
        $conversion_rate = (float) $conversion_amount['conversion_rate'];
        $converted_price = $price * $conversion_rate;
        return $converted_price;
    }

    /**
     * Format Hotel Bed price
     */
    private function format_HB_price(float $room_price=0.0, int|string $room_count = '', string $API_Currency = ''): array
    {
        $conversion_amount = $GLOBALS['CI']->domain_management_model->get_currency_conversion_rate($API_Currency);
        $conversion_rate = (float) $conversion_amount['conversion_rate'];

        if ($room_count != '') {
            $room_price = $room_price / (int) $room_count;
        }

        $room_price *= $conversion_rate;

        return [
            'PublishedPrice'          => $room_price,
            'PublishedPriceRoundedOff' => $room_price,
            'OfferedPrice'            => $room_price,
            'OfferedPriceRoundedOff'  => $room_price,
            'RoomPrice'               => $room_price,
            'Tax'                     => 0,
            'ExtraGuestCharge'        => 0,
            'ChildCharge'             => 0,
            'OtherCharges'            => 0,
            'Discount'                => 0,
            'AgentCommission'         => 0,
            'AgentMarkUp'             => 0,
            'ServiceTax'              => 0,
            'TDS'                     => 0,
            'RoomPriceWoGST'          => $room_price,
            'GSTPrice'                => 0,
        ];
    }
    public function get_min_room_price(array $hotel_details_response): array
    {
        $hotel_array = [];

        $hotel_detail_responce_arr = $hotel_details_response;

        foreach ($hotel_detail_responce_arr['hotels']['hotels'] as $value) {
            if (isset($value) && is_array($value) && !empty($value)) {

                $new_arr = [];
                $hotel_details = $value;
                $code = $hotel_details['code'];

                $hotel_array[$code] = [
                    'hotel_code'       => $code,
                    'hotel_name'       => $hotel_details['name'],
                    'star_rating'      => $hotel_details['categoryName'],
                    'destination'      => $hotel_details['destinationName'],
                    'destination_code' => $hotel_details['destinationCode'],
                    'zone_code'        => $hotel_details['zoneCode'],
                    'zone'             => $hotel_details['zoneName'],
                    'latitude'         => $hotel_details['latitude'] ?? '',
                    'longitude'        => $hotel_details['longitude'] ?? '',
                    'minRate'          => $hotel_details['minRate'],
                    'maxRate'          => $hotel_details['maxRate'],
                    'currency'         => $hotel_details['currency'],
                    'checkIn'          => $hotel_detail_responce_arr['hotels']['checkIn'],
                    'checkOut'         => $hotel_detail_responce_arr['hotels']['checkOut'],
                ];

                if (!empty($hotel_details['rooms'])) {
                    foreach ($hotel_details['rooms'] as $rooms) {
                        if (!empty($rooms['rates'])) {
                            foreach ($rooms['rates'] as $rates) {
                                $rates['code'] = $rooms['code'];
                                $rates['name'] = $rooms['name'];
                                $rates['net'] = round((float)$rates['net'], 2);

                                // Handle cancellation policies
                                if (!empty($rates['cancellationPolicies'])) {
                                    foreach ($rates['cancellationPolicies'] as $cel_key => $cancel_policy) {
                                        $amount = ceil((float)$cancel_policy['amount']);
                                        $rates['cancellationPolicies'][$cel_key]['amount'] = sprintf("%.2f", $amount);
                                    }
                                }

                                $key = $rates['rooms'] . '_' . $rates['adults'] . '_' . $rates['children'] . '_' . ($rates['childrenAges'] ?? '');
                                $new_arr[$key][] = $rates;
                            }
                        }
                    }
                }

                // Sort room details
                $sorted_room_details = [];
                foreach ($new_arr as $k_key => $room_Detail_k) {
                    $sorted_room_details[$k_key] = $this->array_sort($room_Detail_k, 'boardCode', SORT_DESC);
                }

                $hotel_array[$code]['rooms'] = $this->combintaion_min_price($sorted_room_details);

                // Credit card payment options
                if (!empty($hotel_details['creditCards'])) {
                    foreach ($hotel_details['creditCards'] as $crKey => $credit_card) {
                        $hotel_array[$code]['credit_card'][$crKey] = [
                            'code'        => $credit_card['code'],
                            'name'        => $credit_card['name'],
                            'paymentType' => $credit_card['paymentType'],
                        ];
                    }
                }
            }
        }

        return $hotel_array;
    }
    public function get_hotel_rooms_combinations(array $hotel_details_response): array
    {
        $hotel_array = [];
        $new_arr = [];
        $sorted_room_details = [];

        $hotel_detail_response_arr = $hotel_details_response;

        if (
            isset($hotel_details_response['hotels']['hotels'][0]) &&
            is_array($hotel_details_response['hotels']['hotels'][0]) &&
            !empty($hotel_details_response['hotels']['hotels'][0])
        ) {
            $hotel_details = $hotel_details_response['hotels']['hotels'][0];

            $hotel_array = [
                'hotel_code'       => $hotel_details['code'],
                'hotel_name'       => $hotel_details['name'],
                'categoryName'     => $hotel_details['categoryName'],
                'destination'      => $hotel_details['destinationName'],
                'destination_code' => $hotel_details['destinationCode'],
                'zone_code'        => $hotel_details['zoneCode'],
                'zone'             => $hotel_details['zoneName'],
                'latitude'         => $hotel_details['latitude'] ?? '',
                'longitude'        => $hotel_details['longitude'] ?? '',
                'minRate'          => round((float) $hotel_details['minRate'], 2),
                'maxRate'          => $hotel_details['maxRate'],
                'checkIn'          => $hotel_detail_response_arr['hotels']['checkIn'] ?? '',
                'checkOut'         => $hotel_detail_response_arr['hotels']['checkOut'] ?? '',
            ];

            if (!empty($hotel_details['rooms']) && is_array($hotel_details['rooms'])) {
                foreach ($hotel_details['rooms'] as $rooms) {
                    if (!empty($rooms['rates']) && is_array($rooms['rates'])) {
                        foreach ($rooms['rates'] as $rates) {
                            $rates['code'] = $rooms['code'];
                            $rates['name'] = $rooms['name'];
                            $rates['net'] = round((float) $rates['net'], 2);
                            $rates['Currency'] = $hotel_details['currency'] ?? '';

                            // Format cancellation policies
                            if (!empty($rates['cancellationPolicies']) && is_array($rates['cancellationPolicies'])) {
                                foreach ($rates['cancellationPolicies'] as $cel_key => $cancel_policy) {
                                    $amount = (float) ($cancel_policy['amount'] ?? 0);
                                    $rates['cancellationPolicies'][$cel_key]['amount'] = sprintf("%.2f", $amount);
                                }
                            }

                            $key = $rates['rooms'] . '_' . $rates['adults'] . '_' . $rates['children'] . '_' . ($rates['childrenAges'] ?? '');
                            $new_arr[$key][] = $rates;
                        }
                    }
                }
            }

            // Sort room combinations
            foreach ($new_arr as $k_key => $room_Detail_k) {
                $sorted_room_details[$k_key] = $this->array_sort($room_Detail_k, 'boardCode', SORT_DESC);
            }

            $hotel_array['rooms'] = $this->combintaion($sorted_room_details);
        }

        return $hotel_array;
    }
    /**
     * Format and sort room combinations
     *
     * @param array $room_list
     * @return array
     */
    public function format_room_combination_list(array $room_list): array
    {
        $formatted_room_list = [];
        $sorted_room_list = [];
        $min_price = 0.0;
        $check_rooms = [];

        foreach ($room_list as $rate_k => $rate_v) {
            $roomNo = 0;
            $no_of_adults = 0;
            $no_of_children = 0;
            $net_price = 0.0;
            $Currency = '';
            $room_count = count($rate_v);

            foreach ($rate_v as $key => $details) {
                $room_key = $details['code'] . '-' . $details['rateClass'] . '-' . $details['boardCode'] . '-' . $details['net'];

                if (!in_array($room_key, $check_rooms, true)) {
                    $formatted_room_list[$rate_k][$key] = [
                        'rateKey'             => $details['rateKey'],
                        'rateClass'           => $details['rateClass'],
                        'rateType'            => $details['rateType'],
                        'allotment'           => $details['allotment'] ?? '',
                        'rateCommentsId'      => $details['rateCommentsId'] ?? '',
                        'boardCode'           => $details['boardCode'],
                        'boardName'           => $details['boardName'],
                        'cancellationPolicies' => $details['cancellationPolicies'] ?? '',
                        'promotions'          => $details['promotions'] ?? '',
                        'offers'              => $details['offers'] ?? '',
                        'rooms'               => $details['rooms'],
                        'adults'              => $details['adults'],
                        'children'            => $details['children'],
                        'childrenAges'        => $details['childrenAges'] ?? 0,
                        'code'                => $details['code'],
                        'name'                => $details['name'],
                        'net'                 => sprintf("%.2f", (float)$details['net']),
                        'room_count'          => $room_count,
                    ];

                    $roomNo += (int)$details['rooms'];
                    $no_of_adults += (int)$details['adults'];
                    $no_of_children += (int)$details['children'];
                    $net_price += (float)$details['net'];
                    $Currency = $details['Currency'];

                    $check_rooms[] = $room_key;
                }
            }

            if ($no_of_adults > 0) {
                $formatted_room_list[$rate_k]['adults'] = $no_of_adults;
                $formatted_room_list[$rate_k]['children'] = $no_of_children;
                $formatted_room_list[$rate_k]['room'] = $roomNo;
                $formatted_room_list[$rate_k]['net'] = sprintf("%.2f", $net_price);
                $formatted_room_list[$rate_k]['Currency'] = $Currency;

                $min_price = ($min_price == 0.0 || $net_price < $min_price) ? $net_price : $min_price;
            }
        }

        // Sort formatted list by net price
        $sorted_room_list['list'] = $this->order_array_num($formatted_room_list, 'net');
        $sorted_room_list['min_price'] = sprintf("%.2f", $min_price);

        return $sorted_room_list;
    }
    /**
     * Generate all possible room combinations from a list of grouped room rates.
     *
     * @param array $room_list
     * @return array
     */
    private function combintaion(array $room_list): array
    {
        $result = [];
        $total_room_count = count($room_list);

        $expand = function (array $sofar, array $rest) use (&$expand, &$result, $total_room_count): void {
            if (empty($rest)) {
                return;
            }

            $tag = array_key_first($rest);
            $values = array_shift($rest);

            foreach ($values as $value) {
                $subresult = $sofar;
                $subresult[$tag] = $value;

                if (count($subresult) == $total_room_count) {
                    $result[] = $subresult;
                }

                $expand($subresult, $rest);
            }
        };

        $expand([], $room_list);

        return $result;
    }
    /**
     * Generate all valid room combinations and return the one with the lowest total net price.
     *
     * @param array $room_list
     * @return array
     */
    private function combintaion_min_price(array $room_list): array
    {
        $total_room_count = count($room_list);
        $min_combination = [];
        $min_price = null;

        $expand = function (
            array $sofar,
            array $rest
        ) use (&$expand, &$min_combination, &$min_price, $total_room_count): void {
            if (empty($rest)) {
                return;
            }

            $tag = array_key_first($rest);
            $values = array_shift($rest);

            foreach ($values as $value) {
                $subresult = $sofar;
                $subresult[$tag] = $value;

                if (count($subresult) == $total_room_count) {
                    $total_price = 0;
                    foreach ($subresult as $room) {
                        $total_price += (float)($room['net'] ?? 0);
                    }

                    if ($min_price == null || $total_price < $min_price) {
                        $min_price = $total_price;
                        $min_combination = $subresult;
                    }
                }

                $expand($subresult, $rest);
            }
        };

        $expand([], $room_list);

        return $min_combination;
    }
    /**
     * Sort an array of arrays by a numeric key value.
     *
     * @param array $array The array to sort.
     * @param string $key The key inside sub-arrays to sort by.
     * @param string $order 'ASC' or 'DESC'
     * @return array The sorted array.
     */
    private function order_array_num(array $array, string $key, string $order = 'ASC'): array
    {
        $sortable = [];

        foreach ($array as $k => $v) {
            $sortable[$k] = isset($v[$key]) ? (float)$v[$key] : 0.0;
        }

        $order = strtoupper($order);
        if ($order == 'DESC') {
            arsort($sortable, SORT_NUMERIC);
        } else {
            asort($sortable, SORT_NUMERIC);
        }

        $sorted = [];
        foreach ($sortable as $k => $v) {
            $sorted[$k] = $array[$k];
        }

        return $sorted;
    }
    /**
     * Format Room List Data response from HotelBeds
     *
     * @param array $room_list_data
     * @param array $carry_data
     * @param string $search_id
     * @return array
     */
    private function format_room_list_response(array $room_list_data, array $carry_data, string $search_id): array
    {
        ini_set('memory_limit', '250M');

        $room_list = $this->get_hotel_rooms_combinations($room_list_data);
        $response = [];
        $room_images = [];

        $hotel_code = $room_list['hotel_code'] ?? '';
        $static_data = $this->CI->custom_db->single_table_records(
            'master_hotel_details_beta_hb',
            'images',
            ['hotel_code' => $hotel_code],
            0,
            1
        );

        if ($static_data['status'] == SUCCESS_STATUS && !empty($static_data['data'][0]['images'])) {
            $images = json_decode($static_data['data'][0]['images'], true);
            if (valid_array($images)) {
                foreach ($images as $image) {
                    if (!empty($image['roomCode'])) {
                        $room_images[$image['roomCode']] = $image;
                    }
                }
            }
        }

        $TraceId = $room_list_data['auditData']['token'];
        $Hotel_code = $room_list_data['hotels']['hotels'][0]['code'] ?? '';
        $formated_room_list = $this->format_room_combination_list($room_list['rooms']);
        $hotel_room_list = [];
        $Room_Combination_arr = [];

        foreach ($formated_room_list['list'] as $result_k => $result_v) {
            $rateKey_arr = [];
            $rateClass_arr = [];
            $rateType_arr = [];
            $room_hb_name_arr = [];
            $no_of_rooms_arr = [];
            $amenitie_arr = [];
            $room_cancellation_policy = [];
            $partial_rate_key = [];

            $room_image = 'https://www.travelsoho.com/LAR/services/extras/system/template_list/template_v1/images/no-image-available.png';
            $resutls_data = array_values($result_v);
            $first_room_code = $resutls_data[0]['code'] ?? '';

            if (!empty($room_images[$first_room_code]['path'])) {
                $room_image = 'https://photos.hotelbeds.com/giata/' . $room_images[$first_room_code]['path'];
            }

            $total_room_price = $result_v['net'] ?? 0.0;

            foreach ($result_v as $details) {
                if (!valid_array($details)) {
                    continue;
                }

                $TraceId = $details['rateCommentsId'] ?? $TraceId;

                for ($i = 0; $i < ($details['rooms'] ?? 0); $i++) {
                    $room_hb_name_arr[] = $details['name'];
                }

                $rate_key = explode("||", $details['rateKey']);
                $partial_rate_key[] = $rate_key[0] . '|' . $rate_key[1];
                $rateKey_arr[] = $details['rateKey'];
                $rateClass_arr[] = $details['rateClass'];
                $rateType_arr[] = $details['rateType'];
                $no_of_rooms_arr[] = $details['rooms'];

                if (!empty($details['boardCode']) && $details['boardCode'] != 'RO') {
                    $amenitie_arr[] = $details['boardName'];
                }

                if (!empty($details['cancellationPolicies'])) {
                    $room_cancellation_policy[] = $details['cancellationPolicies'];
                }
            }

            $room_names = $this->format_HB_room_names($room_hb_name_arr, $no_of_rooms_arr);

            $hotel_room_list[$result_k] = [
                'RoomIndex' => $result_k,
                'RoomImage' => $room_image,
                'ChildCount' => $result_v['children'],
                'RoomTypeName' => $room_names,
                'Price' => $this->format_HB_price($result_v['net'], '', $result_v['Currency']),
                'SmokingPreference' => 'NoPreference',
                'RatePlanCode' => json_encode($rateKey_arr),
                'RoomTypeCode' => $first_room_code,
                'RoomTypeCodeD' => json_encode($partial_rate_key),
                'CategoryId' => '',
                'Amenities' => array_unique($amenitie_arr),
                'room_only' => 'room only',
                'cancellation_policy_code' => '',
                'CancellationPolicies' => [],
                'CancellationPolicy' => '',
                'rate_key' => json_encode($rateKey_arr),
                'group_code' => '',
                'room_code' => '',
                'HOTEL_CODE' => $Hotel_code,
                'SEARCH_ID' => $TraceId,
                'RoomUniqueId' => serialized_data([
                    'key' => [
                        $result_k => [
                            'booking_source' => $this->booking_source,
                            'TraceId' => $TraceId,
                            'rateKey' => json_encode($rateKey_arr),
                            'rateClass' => json_encode($rateClass_arr),
                            'rateType' => json_encode($rateType_arr)
                        ]
                    ]
                ]),
                'InfoSource' => 'FixedCombination',
            ];

            // Format cancellation policy
            $cancellation_policy = $this->format_room_cancellation_policy(
                $room_cancellation_policy,
                $room_list_data['hotels']['checkIn'],
                $result_v['Currency'],
                $total_room_price
            );

            $api_last_cancel_date = '';
            $cancel_policy_text = '';
            $current_date = date('Y-m-d');

            foreach ($cancellation_policy as $cp_value) {
                $type = '';
                if ($cp_value['ChargeType'] == 1 && $cp_value['Charge'] == 0) {
                    $api_last_cancel_date = date('Y-m-d', strtotime("-{$this->api_cancellation_policy_day} days", strtotime($cp_value['ToDate'])));
                }

                if ($cp_value['ChargeType'] == 1) {
                    $type = $cp_value['Currency'];
                } elseif ($cp_value['ChargeType'] == 2) {
                    $type = '%';
                }

                if ($type != '') {
                    $cancel_policy_text .= "{$cp_value['Charge']} {$type} will be charged between " .
                        date('d-M-Y', strtotime($cp_value['FromDate'])) . ' and ' .
                        date('d-M-Y', strtotime($cp_value['ToDate'])) . ' | ';
                }
            }

            $hotel_room_list[$result_k]['LastCancellationDate'] = ($current_date < $api_last_cancel_date) ? $api_last_cancel_date : '';
            $hotel_room_list[$result_k]['CancellationPolicies'] = array_reverse($cancellation_policy);
            $hotel_room_list[$result_k]['CancellationPolicy'] = $cancel_policy_text . '|';

            $Room_Combination_arr[] = ['RoomIndex' => [$result_k]];
        }

        $response['HotelRoomsDetails'] = array_values($hotel_room_list);
        $response['RoomCombinations'] = [
            'InfoSource' => 'FixedCombination',
            'IsPolicyPerStay' => true,
            'RoomCombination' => $Room_Combination_arr
        ];

        return $response;
    }
    /**
     * Format cancellation policy into a structured array
     *
     * @param array $cancellation_policy
     * @param string $checkin_date (Y-m-d format)
     * @return array
     */
    // private function format_cancellation_policy(array $cancellation_policy, string $checkin_date): array
    // {
    //     $cancel_policy_details_array = [];

    //     if (!empty($cancellation_policy)) {
    //         $check_cancel_dates = array_map(
    //             fn($p) => date('Y-m-d', strtotime($p['from'] ?? '')),
    //             $cancellation_policy
    //         );

    //         $cancel_min_date = min($check_cancel_dates);
    //         $current_date = date('Y-m-d');
    //         $api_date = date('Y-m-d', strtotime("-{$this->api_cancellation_policy_day} days", strtotime($cancel_min_date)));

    //         // Add zero charge free cancellation period if applicable
    //         if ($current_date <= $api_date) {
    //             $cancel_policy_details_array[] = [
    //                 'Charge' => 0,
    //                 'ChargeType' => 1,
    //                 'Currency' => 'INR',
    //                 'FromDate' => date('Y-m-d H:i:s'),
    //                 'ToDate' => $api_date
    //             ];
    //         }

    //         $cancel_policy_count = count($cancellation_policy);

    //         foreach ($cancellation_policy as $index => $policy) {
    //             $from_date = $policy['from'] ?? '';
    //             $amount = ceil($policy['amount'] ?? 0);

    //             $cancel_arr = [
    //                 'Charge' => $amount,
    //                 'ChargeType' => 1,
    //                 'Currency' => 'INR',
    //                 'FromDate' => $from_date
    //             ];

    //             // Determine the ToDate based on next policy or checkin date
    //             if ($cancel_policy_count == 1) {
    //                 $cancel_arr['ToDate'] = $checkin_date;
    //             } elseif ($index + 1 < $cancel_policy_count) {
    //                 $next_from = $cancellation_policy[$index + 1]['from'];
    //                 $cancel_arr['ToDate'] = date('Y-m-d', strtotime("-{$this->api_cancellation_policy_day} days", strtotime($next_from)));
    //             } else {
    //                 $cancel_arr['ToDate'] = $checkin_date;
    //             }

    //             $cancel_policy_details_array[] = $cancel_arr;
    //         }
    //     } else {
    //         // Default 100% penalty if no policy defined
    //         $cancel_policy_details_array[] = [
    //             'Charge' => 100,
    //             'ChargeType' => 2, // percent
    //             'Currency' => 'INR',
    //             'FromDate' => date('Y-m-d H:i:s'),
    //             'ToDate' => $checkin_date
    //         ];
    //     }

    //     return $cancel_policy_details_array;
    // }
    /**
     * Format cancellation policy for room, applying room-wide cancellation charges.
     *
     * @param array $cancellation_policy
     * @param string $checkin_date (Y-m-d format)
     * @param string $API_Currency (optional, default is '')
     * @param float $total_room_price (total room price for cancellation calculation)
     * @return array
     */
    private function format_room_cancellation_policy(array $cancellation_policy, string $checkin_date, string $API_Currency = '', float $total_room_price): array
    {
        $cancel_arr = [];
        $total_cancel_charge = 0;
        $cancel_policy_details_array = [];
        $get_min_cancel_date = [];

        // Check if cancellation policy is provided
        if (empty($cancellation_policy)) {
            return $cancel_policy_details_array;
        }

        // Calculate total cancellation charge and get the earliest cancellation date
        foreach ($cancellation_policy as $value) {
            foreach ($value as $c_value) {
                $total_cancel_charge += $c_value['amount'];
                $get_min_cancel_date[] = date('Y-m-d', strtotime("-{$this->api_cancellation_policy_day} day", strtotime($c_value['from'])));
            }
        }

        // Determine the earliest cancellation date
        $cancel_min_date = min($get_min_cancel_date);
        $current_date = date('Y-m-d');
        $api_date = date('Y-m-d', strtotime("-{$this->api_cancellation_policy_day} day", strtotime($cancel_min_date)));

        // If the current date is before the API cancellation deadline, add free cancellation
        if ($current_date <= $api_date) {
            $cancel_arr = [
                'Charge' => 0,
                'ChargeType' => 1,  // ChargeType 1 is for fixed amount
                'Currency' => 'INR', // Default currency INR, can be updated based on API_Currency
                'FromDate' => date('Y-m-d H:i:s'),
                'ToDate' => $api_date
            ];
            $cancel_policy_details_array[] = $cancel_arr;
        }

        // Ensure total cancellation charge doesn't exceed the total room price
        if ($total_cancel_charge >= $total_room_price) {
            $total_cancel_charge = $total_room_price;
        }

        // Convert the cancellation price based on the provided API currency
        $converted_price = $this->format_CANCELLATION_Price($total_cancel_charge, $API_Currency);

        // Add cancellation policy for the time frame between API cancellation date and check-in date
        $cancel_arr = [
            'Charge' => $converted_price,
            'ChargeType' => 1,
            'Currency' => $API_Currency ?: 'INR',  // Use provided API currency or default to INR
            'FromDate' => $api_date,
            'ToDate' => $checkin_date
        ];

        $cancel_policy_details_array[] = $cancel_arr;

        return $cancel_policy_details_array;
    }
    /**
     * Format room block response with necessary room and cancellation policy details.
     *
     * @param array $room_block_data
     * @param array $carry_data
     * @param string $search_id
     * @return array
     */
    private function format_block_room_response($room_block_data, $carry_data, $search_id)
    {
        // Increase memory limit for large data sets
        ini_set('memory_limit', '250M');

        $response = [];
        $room_cancellation_policy = [];
        $hotel_room_list = [];
        $cancel_policy_text = '';

        // Check if hotel data is available in room block data
        if (!isset($room_block_data['hotel'])) {
            return $response;
        }

        $room_list = $room_block_data['hotel'];
        $room_currency = $room_list['currency'];
        $TraceId = $room_list['destinationCode'];

        // Fetch hotel static details (e.g., country code, postal code)
        $get_hotel_bed_static_data = $this->CI->custom_db->single_table_records('master_hotel_details_beta_hb', '*', ['hotel_code' => $room_list['code']], 0, 1);

        // Default values if static data is not found
        $CountryCode = $PostalCode = '';
        if ($get_hotel_bed_static_data['status'] == 1) {
            $CountryCode = $get_hotel_bed_static_data['data'][0]['country_code'];
        }

        // Get search data (room count, number of nights)
        $search_data = $this->search_data($search_id);
        $no_of_nights = $search_data['data']['no_of_nights'];
        $room_count = $search_data['data']['room_count'];

        // Initialize the hotel room list data
        $hotel_room_list['HotelCode'] = (string)$room_list['code'];
        $child_count = 0;
        $room_names_arr = [];
        $room_hb_name_arr = [];
        $boarding_details_arr = [];
        $rateKey_arr = [];
        $room_details_list = [];
        $rate_comments_arr = [];

        // Process each room in the room list
        foreach ($room_list['rooms'] as $key => $value) {
            $room_names_arr[] = $value['name'];
            $room_details_list[$key]['room_name'] = $value['name'];

            foreach ($value['rates'] as $r_key => $r_value) {
                for ($i = 0; $i < $r_value['rooms']; $i++) {
                    $room_hb_name_arr[] = $value['name'];
                }

                // Collect room details
                $no_of_rooms[] = $r_value['rooms'];
                $child_count += $r_value['children'];
                $rateKey_arr[] = $r_value['rateKey'];
                $rate_comments_arr[] = $r_value['rateComments'];

                if ($r_value['boardCode'] != 'RO') {
                    $boarding_details_arr[] = $r_value['boardName'];
                }

                // Collect cancellation policies for each room
                if (isset($r_value['cancellationPolicies'])) {
                    $room_cancellation_policy[] = $r_value['cancellationPolicies'];
                }

                // Attach rate details to the room
                $room_details_list[$key]['rates'][$r_key] = [
                    'rateKey' => $r_value['rateKey'],
                    'rooms' => $r_value['rooms'],
                    'adult' => $r_value['adults'],
                    'children' => $r_value['children']
                ];
                if (isset($r_value['childrenAges'])) {
                    $room_details_list[$key]['rates'][$r_key]['childrenAges'] = $r_value['childrenAges'];
                }
            }
        }

        // Format room names based on room data
        $room_names = $this->format_HB_room_names($room_hb_name_arr, $no_of_rooms);
        $hotel_room_list['ChildCount'] = $child_count;
        $hotel_room_list['RoomTypeName'] = $room_names;
        $hotel_room_list['TBO_RoomTypeName'] = $room_names;
        $hotel_room_list['Price'] = $this->format_HB_price($room_list['totalNet'], $room_count, $room_currency);
        $hotel_room_list['SmokingPreference'] = 'NoPreference';
        $hotel_room_list['RatePlanCode'] = '';
        $hotel_room_list['RoomTypeCode'] = '';
        $hotel_room_list['CategoryId'] = '';

        // Handle boarding details (e.g., breakfast, other meal plans)
        if (!empty($boarding_details_arr)) {
            $boarding_details_arr = array_unique($boarding_details_arr);
            $hotel_room_list['Boarding_details'] = $boarding_details_arr;
        }

        // Set payment type and cancellation details
        $hotel_room_list['Payment_type'] = isset($r_value['paymentType']) ? $r_value['paymentType'] : '';
        $hotel_room_list['room_only'] = 'room only';
        $hotel_room_list['cancellation_policy_code'] = '';

        // Calculate cancellation policy text
        $cancellation_policy = $this->format_room_cancellation_policy($room_cancellation_policy, $room_list['checkIn'], $room_currency, $room_list['totalNet']);
        $api_last_cancel_date = '';
        foreach ($cancellation_policy as $cp_value) {
            if ($cp_value['ChargeType'] == 1 && $cp_value['Charge'] == 0) {
                $api_last_cancel_date = date('Y-m-d', strtotime("-" . $this->api_cancellation_policy_day . " day", strtotime($cp_value['ToDate'])));
            }
            $type = ($cp_value['ChargeType'] == 1) ? $cp_value['Currency'] : '%';
            $amount = $cp_value['Charge'];
            if ($type != '') {
                $cancel_policy_text .= "{$amount} {$type} will be charged between " . date('d-M-Y', strtotime($cp_value['FromDate'])) . " and " . date('d-M-Y', strtotime($cp_value['ToDate'])) . " | ";
            }
        }

        // Set the last cancellation date if applicable
        $hotel_room_list['LastCancellationDate'] = ($current_date < $api_last_cancel_date) ? $api_last_cancel_date : '';

        // Assign other room and cancellation details
        $hotel_room_list['rateKey'] = json_encode($rateKey_arr);
        $hotel_room_list['RoomNames'] = $room_hb_name_arr;
        $hotel_room_list['rooms'] = $room_details_list;
        $hotel_room_list['CancellationPolicy'] = $cancel_policy_text . '|';
        $hotel_room_list['CancellationPolicies'] = array_reverse($cancellation_policy);

        // Update cancellation policy based on fare markup
        $hotel_room_list['TM_Cancellation_Policy'] = $this->CI->common_hotel_v3->update_fare_markup_commission_cancel_policy(
            $hotel_room_list['CancellationPolicies'],
            $no_of_nights,
            0,
            true,
            $this->booking_source
        );

        // Set other static details
        $hotel_room_list['IsPANMandatory'] = false;
        $hotel_room_list['IsPassportMandatory'] = false;
        $hotel_room_list['NoOfPANRequired'] = 0;
        $hotel_room_list['HotelImage'] = $get_hotel_bed_static_data['data'][0]['image'];
        $hotel_room_list['rate_key'] = '';
        $hotel_room_list['group_code'] = '';
        $hotel_room_list['room_code'] = '';
        $hotel_room_list['HOTEL_CODE'] = (string)$room_list['code'];
        $hotel_room_list['Address'] = $get_hotel_bed_static_data['data'][0]['address'];

        // Prepare response with the room index details
        $key = [];
        $key['key'][0]['TraceId'] = $TraceId;
        $key['key'][0]['booking_source'] = $this->booking_source;
        $key['key'][0]['RateComments'] = $rate_comments_arr;
        $key['key'][0]['RoomNames'] = $room_hb_name_arr;

        // Add the formatted room details for each room count
        for ($i = 0; $i < $room_count; $i++) {
            $hotel_room_list['RoomIndex'] = $i;
            $hotel_room_list['TBO_RoomIndex'] = $i;
            $response['HotelRoomsDetails'][] = $hotel_room_list;
            $key['key'][0]['HotelRoomsDetails'][] = $hotel_room_list;
        }

        // Finalize BlockRoomId and response structure
        $BlockRoomId = serialized_data($key['key']);
        $response['BlockRoomId'] = $BlockRoomId;
        $response['IsPriceChanged'] = false;
        $response['IsCancellationPolicyChanged'] = false;

        return $response;
    }
    function book_service_request(array $booking_params, int $search_id): array
    {
        $response = [];
        $response['status'] = true;

        $safe_search_data = $GLOBALS['CI']->hotel_model_v3->get_search_data($search_id);
        $search_data = json_decode($safe_search_data['search_data'], true);

        // Child ages
        $child_age_arr = [];
        if (valid_array($search_data['room_config'])) {
            foreach ($search_data['room_config'] as $c_key => $child) {
                if ($child['NoOfChild'] >= 1) {
                    if (isset($child['ChildAge'])) {
                        foreach ($child['ChildAge'] as $key => $value) {
                            $child_age_arr[] = $value;
                        }
                    }
                }
            }
        }

        $room_paxes_detials = [];
        $room_paxes_cnt = 0;
        if (isset($booking_params['RoomPriceBreakup'][0]['rooms']) && valid_array($booking_params['RoomPriceBreakup'][0]['rooms'])) {
            foreach ($booking_params['RoomPriceBreakup'][0]['rooms'] as $room_key_d => $room_detials) {
                if (isset($room_detials['rates']) && valid_array($room_detials['rates'])) {
                    foreach ($room_detials['rates'] as $rate_key => $rates_details) {
                        $room_paxes_detials[$room_paxes_cnt]['room_name'] = $room_detials['room_name'];
                        $room_paxes_detials[$room_paxes_cnt]['rateKey'] = $rates_details['rateKey'];

                        $no_of_adults = $rates_details['adult'] / $rates_details['rooms'];
                        $no_of_childs = $rates_details['children'] / $rates_details['rooms'];

                        $total_pax_cnt = $no_of_adults + $no_of_childs;

                        $room_paxes_detials[$room_paxes_cnt]['no_of_rooms'] = $rates_details['rooms'];
                        $room_paxes_detials[$room_paxes_cnt]['no_of_adults'] = $rates_details['adult'] * $rates_details['rooms'];
                        $room_paxes_detials[$room_paxes_cnt]['no_of_children'] = $rates_details['children'] * $rates_details['rooms'];

                        // Convert child ages to array
                        if ($rates_details['children'] != 0) {
                            if (isset($rates_details['childrenAges'])) {
                                $room_paxes_detials[$room_paxes_cnt]['childrenAges'] = explode(",", $rates_details['childrenAges']);
                            }
                        }
                        $room_paxes_cnt++;
                    }
                }
            }
        }

        $child_age_cnt_index = 0;
        $total_pax = 0;
        $rooms_tag_request = [];
        $room_rate_key_arr = json_decode($booking_params['RoomPriceBreakup'][0]['rateKey'], true);

        // Build the passenger type
        $passenger_type = [];
        $first_name_arr = [];
        $last_name_arr = [];
        foreach ($booking_params['Passengers'] as $p_key => $p_value) {
            foreach ($p_value['PassengerDetails'] as $ro_key => $ro_value) {
                $first_name_arr[] = $ro_value['FirstName'];
                $last_name_arr[] = $ro_value['LastName'];
                $pax_type = ($ro_value['PaxType'] == 1) ? 'AD' : 'CH';
                $passenger_type[] = $pax_type;
            }
        }

        // Hotel Bed Booking
        $format_room_rate_key = [];
        foreach ($room_paxes_detials as $key => $value) {
            for ($i = 0; $i < $value['no_of_adults']; $i++) {
                $format_room_rate_key[] = $value['rateKey'];
            }
            if ($value['no_of_children'] >= 1) {
                for ($i = 0; $i < $value['no_of_children']; $i++) {
                    $format_room_rate_key[] = $value['rateKey'];
                }
            }
        }

        $request = '<bookingRQ xmlns="http://www.hotelbeds.com/schemas/messages" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
        $request .= '<holder name="' . $booking_params['Passengers'][0]['PassengerDetails'][0]['FirstName'] . '" surname="' . $booking_params['Passengers'][0]['PassengerDetails'][0]['LastName'] . '"/>';
        $rooms_tag_request = '<rooms>';

        $child_age_cnt_index = 0;
        $total_pax = 0;

        foreach ($room_paxes_detials as $rpd_key => $rooms_pax) {
            $rate_key = $rooms_pax['rateKey'];
            $rooms_tag_request .= '<room rateKey="' . $rate_key . '">';
            $rooms_tag_request .= '<paxes>';

            $roomId = 1;
            for ($j = 0; $j < $rooms_pax['no_of_rooms']; $j++) {
                $no_of_adults = $rooms_pax['no_of_adults'] / $rooms_pax['no_of_rooms'];
                $no_of_childs = $rooms_pax['no_of_children'] / $rooms_pax['no_of_rooms'];
                $total_pax_cnt = $no_of_adults + $no_of_childs;

                for ($a = 0; $a < $total_pax_cnt; $a++) {
                    $pass_rate_key = $format_room_rate_key[$total_pax];
                    if ($rate_key == $pass_rate_key) {
                        $type = $passenger_type[$total_pax];
                        $name = $first_name_arr[$total_pax];
                        $surname = $last_name_arr[$total_pax];
                        if ($type == 'CH') {
                            $age = $child_age_arr[$child_age_cnt_index]; // Age for children
                            $child_age_cnt_index++;
                        } else {
                            $age = 30; // Age for adults (placeholder, can be modified)
                        }
                        $rooms_tag_request .= '<pax roomId="' . $roomId . '" type="' . $type . '" age="' . $age . '" name="' . $name . '" surname="' . $surname . '"></pax>';
                    }
                    $total_pax++;
                }
                $roomId++;
            }
            $rooms_tag_request .= '</paxes>';
            $rooms_tag_request .= '</room>';
        }

        $rooms_tag_request .= '</rooms>';
        $request .= $rooms_tag_request;
        $request .= '<clientReference>Travelomatix</clientReference>';
        $request .= '</bookingRQ>';

        $signature = hash("sha256", $this->config['api_key'] . $this->config['secrete'] . time());

        $response['api_header'] = [
            'Api-key:' . trim($this->config['api_key']),
            'X-Signature:' . trim($signature),
            'X-Originating-Ip: 14.141.47.106',
            'Content-Type:application/xml',
            'Accept:application/xhtml+xml,application/xml,text/xml,application/xml',
            'Accept-Encoding: gzip'
        ];

        $response['request'] = $request;
        $response['url'] = $this->config['api_url'] . 'bookings';
        $response['remarks'] = 'HotelBook(HB)';

        return $response;
    }
    /**
     * Header to be used for hotebeds - XML API Version
     *
     * @return array
     */
    private function xml_header(): array
    {
        $this->xml_api_header = [
            'Api-Key: ' . $this->api_online_key,
            'X-Signature: ' . $this->signature,
            'X-Originating-Ip: 14.141.47.106',
            'Content-Type: application/xml',
            'Accept: application/xhtml+xml, application/xml, text/xml, application/xml',
            'Accept-Encoding: gzip'
        ];
        return $this->xml_api_header;
    }
    /**
     * Find a key-value pair in a nested array and return 'no_of_rooms'.
     * 
     * @param array $array The array to search.
     * @param string $key The key to search for.
     * @param mixed $val The value to search for.
     * 
     * @return int|false The number of rooms if found, otherwise false.
     */
    private function find_key_value(array $array, string $key, $val): int|false
    {
        foreach ($array as $item) {
            // Check if the item is an array and recurse if needed
            if (is_array($item)) {
                // Recursive call if item is an array
                $result = $this->find_key_value($item, $key, $val);
                if ($result != false) {
                    return $result; // Found, return the 'no_of_rooms'
                }
            }

            // Check if the key exists and the value matches
            if (isset($item[$key]) && $item[$key] == $val) {
                return $item['no_of_rooms']; // Return 'no_of_rooms' if found
            }

            // Handle case where key is an array and check if the value exists in that array
            if (isset($item[$key]) && is_array($item[$key]) && in_array($val, $item[$key])) {
                return $item['no_of_rooms'];
            }
        }

        return false; // Return false if not found
    }
    private function format_hotel_details(array $hotel_details_response): array
    {
        ini_set('memory_limit', '250M');

        $format_hotel_details_response = [];
        $facilities_arr = [];
        $facility_mandatory = [];
        $amentiesbycategory = [];
        $amentiesbycategorynew = [];

        $hotel_code = $hotel_details_response['hotels']['hotels'][0]['code'] ?? '';
        $hotel_destination_name = $hotel_details_response['hotels']['hotels'][0]['destinationName'] ?? '';

        // Load facility descriptions
        $hb_hotel_facilities_description = $this->CI->custom_db->single_table_records('hb_hotel_facilities_description', '*');
        $hotel_facilities_description = [];

        if ($hb_hotel_facilities_description['status'] == SUCCESS_STATUS) {
            foreach ($hb_hotel_facilities_description['data'] as $h_facility) {
                $hotel_facilities_description[$h_facility['facility_code']] = $h_facility;
            }
        }

        // Load facilities group
        $hb_facilities_group = $this->CI->custom_db->single_table_records('hb_facilities_group', '*');
        $hotel_facilities_group = [];

        if ($hb_facilities_group['status'] == SUCCESS_STATUS) {
            foreach ($hb_facilities_group['data'] as $h_facility_group) {
                $hotel_facilities_group[$h_facility_group['code']] = $h_facility_group;
            }
        }

        // Get static hotel info
        $hotel_bed_static_data = $this->get_hotel_info($hotel_code, $hotel_facilities_description, $hotel_facilities_group);

        // Group and format amenities
        if (!empty($hotel_bed_static_data['facility'][$hotel_code])) {
            $facility_key = 0;
            foreach ($hotel_bed_static_data['facility'][$hotel_code] as $f_value) {
                foreach ($f_value as $sfvalue) {
                    $amentiesbycategory[$sfvalue['fac_group_desc']][] = $sfvalue['name'];
                    $facilities_arr[$facility_key] = $sfvalue['name'];
                    $facility_key++;
                }
            }

            if (!empty($amentiesbycategory)) {
                $i = 0;
                foreach ($amentiesbycategory as $am_key => $amenity) {
                    $amentiesbycategorynew[$i]['name'] = implode(', ', $amenity);
                    $amentiesbycategorynew[$i]['category'] = $am_key;
                    $i++;
                }
            }
        }

        // Get room combinations
        $hotel_room_list = $this->get_hotel_rooms_combinations($hotel_details_response);
        $formated_room_list = $this->format_room_combination_list($hotel_room_list['rooms'] ?? []);

        if (!empty($hotel_details_response['hotels']['hotels'])) {
            $hotel_info = $hotel_details_response['hotels']['hotels'][0];
            $hotel_code_data = ['hotel_code' => $hotel_info['code']];
            $hotel_images = $this->Get_Hotel_Property_Images($hotel_code_data);

            $format_hotel_details_response['HotelDetails']['Images'] = $hotel_images['images'] ?? [];
            $format_hotel_details_response['HotelDetails']['checkin'] = $hotel_details_response['hotels']['checkIn'] ?? '';
            $format_hotel_details_response['HotelDetails']['checkout'] = $hotel_details_response['hotels']['checkOut'] ?? '';
            $format_hotel_details_response['HotelDetails']['HotelName'] = $hotel_info['name'] ?? '';
            $format_hotel_details_response['HotelDetails']['HotelCode'] = (string)($hotel_info['code'] ?? '');

            // Star rating
            $categoryName = $hotel_info['categoryName'] ?? '';
            if (!empty(intval($categoryName))) {
                $format_hotel_details_response['HotelDetails']['StarRating'] = intval($categoryName);
            } elseif (!empty(intval(preg_replace('/[^0-9]+/', '', $categoryName), 10))) {
                $format_hotel_details_response['HotelDetails']['StarRating'] = intval(preg_replace('/[^0-9]+/', '', $categoryName), 10);
            } else {
                $format_hotel_details_response['HotelDetails']['StarRating'] = 0;
            }

            // Hotel description, address, country
            $get_hotel_bed_static_data = $this->CI->custom_db->single_table_records(
                'master_hotel_details_beta_hb',
                'hotel_desc as hotel_desc, address as add1, city_code as destination_code, phone_number, country_code',
                ['hotel_code' => $format_hotel_details_response['HotelDetails']['HotelCode']],
                0,
                1
            );

            $hotel_desc = '';
            $hotel_address = '';
            $hotel_destination = $hotel_destination_name;
            $hotel_contact_numbers = [];
            $hotel_country_code = '';

            if ($get_hotel_bed_static_data['status'] == 1 && isset($get_hotel_bed_static_data['data'][0])) {
                $data = $get_hotel_bed_static_data['data'][0];
                $hotel_desc = $data['hotel_desc'] ?? '';
                $hotel_address = $data['add1'] ?? '';
                $hotel_destination = $hotel_destination ?: $data['destination_code'] ?? '';
                $hotel_country_code = $data['country_code'] ?? '';

                if (!empty($data['phone_number'])) {
                    $hotel_contact_numbers = explode(",", $data['phone_number']);
                }
            }

            $format_hotel_details_response['HotelDetails']['Address'] = $hotel_address;
            $format_hotel_details_response['HotelDetails']['HotelContactNo'] = $hotel_contact_numbers;
            $format_hotel_details_response['HotelDetails']['HotelFacilities'] = array_values(array_unique($facilities_arr));
            $format_hotel_details_response['HotelDetails']['HotelFacilitiesByCategory'] = $amentiesbycategorynew;
            $format_hotel_details_response['HotelDetails']['Description'] = $hotel_desc;
            $format_hotel_details_response['HotelDetails']['Latitude'] = $hotel_info['latitude'] ?? '';
            $format_hotel_details_response['HotelDetails']['Longitude'] = $hotel_info['longitude'] ?? '';
            $format_hotel_details_response['HotelDetails']['Attractions'] = [];
            $format_hotel_details_response['HotelDetails']['Landmarks'] = [];
            $format_hotel_details_response['HotelDetails']['checkInTime'] = '';
            $format_hotel_details_response['HotelDetails']['checkOutTime'] = '';
            $format_hotel_details_response['HotelDetails']['totalRooms'] = '';
            $format_hotel_details_response['HotelDetails']['totalFloors'] = '';

            // Room processing
            $rateKey_arr = [];
            $rateClass_arr = [];
            $rateType_arr = [];
            $room_name_arr = [];
            $room_hb_name_arr = [];
            $room_cancellation_policy = [];
            $no_of_rooms_arr = [];
            $first_room_price = 0;
            $room_currecny = '';
            $room_names = '';
            $free_cancel_date = '';
            $list = $formated_room_list['list'] ?? [];
            $format_room_list_first_arr = reset($list);

            if (!empty($format_room_list_first_arr)) {
                $room_currecny = $format_room_list_first_arr['Currency'] ?? '';
                foreach ($format_room_list_first_arr as $r_value) {
                    if (is_array($r_value)) {
                        for ($i = 0; $i < ($r_value['rooms'] ?? 0); $i++) {
                            $room_hb_name_arr[] = $r_value['name'] ?? '';
                        }

                        $rateKey_arr[] = $r_value['rateKey'] ?? '';
                        $rateClass_arr[] = $r_value['rateClass'] ?? '';
                        $rateType_arr[] = $r_value['rateType'] ?? '';
                        $room_name_arr[] = $r_value['name'] ?? '';
                        $no_of_rooms_arr[] = $r_value['rooms'] ?? 0;

                        if (!empty($r_value['cancellationPolicies'])) {
                            $room_cancellation_policy[] = $r_value['cancellationPolicies'];
                        }

                        $first_room_price += $r_value['net'] ?? 0;
                    }
                }
            }

            $room_names = $this->format_HB_room_names($room_hb_name_arr, $no_of_rooms_arr);
            $cancellation_policy = $this->format_room_cancellation_policy($room_cancellation_policy, $hotel_details_response['hotels']['checkIn'] ?? '', $room_currecny, $first_room_price);
            $api_last_cancel_date = date('Y-m-d');

            foreach ($cancellation_policy as $cp_value) {
                if ($cp_value['ChargeType'] == 1 && $cp_value['Charge'] == 0) {
                    $api_last_cancel_date = date('Y-m-d', strtotime("-" . $this->api_cancellation_policy_day . " day", strtotime($cp_value['ToDate'])));
                }
            }

            if (date('Y-m-d') < $api_last_cancel_date) {
                $free_cancel_date = $api_last_cancel_date;
            }

            $trip_adv_url = '';
            $trip_rating = '';

            $key = [
                'key' => [[
                    'booking_source' => $this->booking_source,
                    'TraceId' => $hotel_info['rooms'][0]['rates'][0]['rateKey'] ?? '',
                    'rateClass' => json_encode($rateClass_arr),
                    'rateType' => json_encode($rateType_arr),
                    'RateKey' => json_encode($rateKey_arr),
                    'RoomCode' => $hotel_info['rooms'][0]['code'] ?? ''
                ]]
            ];

            $format_hotel_details_response['HotelDetails']['first_room_details']['Price'] = $this->format_HB_price($first_room_price, '', $room_currecny);
            $format_hotel_details_response['HotelDetails']['first_room_details']['room_name'] = $room_names;
            $format_hotel_details_response['HotelDetails']['first_room_details']['Room_data']['RoomUniqueId'] = serialized_data($key['key']);
            $format_hotel_details_response['HotelDetails']['first_room_details']['Room_data']['rate_key'] = json_encode($rateKey_arr);
            $format_hotel_details_response['HotelDetails']['first_rm_cancel_date'] = $free_cancel_date;
            $format_hotel_details_response['HotelDetails']['Amenities'] = array_values(array_unique($facilities_arr));
            $format_hotel_details_response['HotelDetails']['trip_adv_url'] = $trip_adv_url;
            $format_hotel_details_response['HotelDetails']['trip_rating'] = $trip_rating;
            $format_hotel_details_response['HotelPolicy'] = 'No Hotel Policy Found';
            $format_hotel_details_response['HotelPolicyByCategory'] = '';
        }

        return $format_hotel_details_response;
    }
    /**
     * Update markup currency for price object of hotel
     *
     * @param object $price_summary
     * @param object $currency_obj
     * @return void
     */
    public function update_markup_currency(array &$price_summary, object &$currency_obj): void {}
    /**
     * Get total price from summary object
     *
     * @param object $price_summary
     * @return float
     */
    public function total_price(array $price_summary): array {}
    /**
     * Check if the search RS is valid or not
     * 
     * @param array $search_result Search result RS to be validated
     * 
     * @return bool Returns true if the search result is valid, false otherwise
     */
    private function valid_search_result(array $search_result): bool
    {
        return isset($search_result['hotels']['hotels']) && valid_array($search_result['hotels']);
    }
    /**
     * Update the hotel booking status
     * 
     * @param string $app_reference The application reference for the booking
     * @param string $booking_status The new booking status to be set
     * 
     * @return void This method doesn't return anything
     */
    private function update_booking_status(string $app_reference, string $booking_status): void
    {
        $app_reference = trim($app_reference);
        $booking_status = trim($booking_status);

        if (!empty($app_reference) && !empty($booking_status)) {
            $update_condition = array();
            $update_condition['app_reference'] = $app_reference;

            $update_data = array();
            $update_data['status'] = $booking_status;

            // Update master table status
            $this->CI->custom_db->update_record('hotel_booking_details', $update_data, $update_condition);
            // Update itinerary status
            $this->CI->custom_db->update_record('hotel_booking_itinerary_details', $update_data, $update_condition);
            // Update passenger status
            $this->CI->custom_db->update_record('hotel_booking_pax_details', $update_data, $update_condition);
        }
    }
    /**
     * Format room names
     * 
     * @param array $room_name_arr Array of room names
     * @param array $no_of_rooms_arr Array of the number of rooms
     * 
     * @return string Formatted room names as a string
     */
    private function format_HB_room_names(array $room_name_arr, array $no_of_rooms_arr): string
    {
        // Count the occurrences of each room name
        $room_names_arr = array_count_values($room_name_arr);

        $room_names = '';
        $i = 0;
        $room_count = count($room_names_arr);

        // Iterate through the counted room names
        foreach ($room_names_arr as $r_key => $r_value) {
            if ($room_count == 1) {
                // Handle case where there's only one room type
                switch ($r_value) {
                    case 1:
                        $room_names .= $no_of_rooms_arr[$i] . ' X ' . $r_key;
                        break;
                    case 2:
                        $room_names .= ' 2 X ' . $r_key;
                        break;
                    case 3:
                        $room_names .= ' 3 X ' . $r_key;
                        break;
                    case 4:
                        $room_names .= ' 4 X ' . $r_key;
                        break;
                    default:
                        $room_names .= $r_value . ' X ' . $r_key;
                        break;
                }
            } else {
                // Handle case where there are multiple room types
                $add_plus = isset($no_of_rooms_arr[$i + 1]) ? ' + ' : '';
                $room_names .= $no_of_rooms_arr[$i] . ' X ' . $r_key . $add_plus;
            }
            $i++;
        }

        return $room_names;
    }
    /**
     * Process SOAP API request
     * 
     * @param string $request The request data
     * @param string $api_key The API key for authentication
     * @param string $signature The signature for authentication
     * @param string $url The endpoint URL for the API request
     * 
     * @return array The processed request data with headers
     */
    function form_curl_params(string $request, string $api_key, string $signature, string $url): array
    {
        // Initialize the response data
        $data = [
            'status' => SUCCESS_STATUS,
            'message' => '',
            'data' => []
        ];

        // Prepare the cURL data
        $curl_data = [
            'booking_source' => $this->booking_source,
            'request' => $request,
            'url' => $url,
            'header' => [
                'Api-key: ' . trim($api_key),
                'X-Signature: ' . trim($signature),
                'X-Originating-Ip: 14.141.47.106',
                'Content-Type: application/json',
                'Accept: application/json',
                'Accept-Encoding: gzip'
            ]
        ];

        // Add cURL data to the response
        $data['data'] = $curl_data;

        return $data;
    }
    /**
     * Process API Request
     * 
     * @param string $request The request data
     * @param array $header The HTTP headers for the request
     * @param string $url The API endpoint URL
     * @param string $remarks Optional remarks related to the request
     * @param string $method HTTP method for the request, either 'post' or 'get'
     * 
     * @return string The API response
     */
    function process_request(string $request, array $header, string $url, string $remarks = '', string $method = ''): string
    {
        // Store the API request details
        $insert_id = $this->CI->api_model->store_api_request($url, $request, $remarks);
        $insert_id = intval(@$insert_id['insert_id']);

        // Initialize cURL session
        $cs = curl_init();

        // Set cURL options
        curl_setopt($cs, CURLOPT_URL, $url);
        curl_setopt($cs, CURLOPT_TIMEOUT, 180);
        curl_setopt($cs, CURLOPT_HEADER, 0);
        curl_setopt($cs, CURLOPT_RETURNTRANSFER, 1);

        if ($method == 'post') {
            curl_setopt($cs, CURLOPT_POST, 1);
            curl_setopt($cs, CURLOPT_POSTFIELDS, $request);
        } else {
            curl_setopt($cs, CURLOPT_HTTPGET, TRUE);
        }

        // Disable SSL verification for testing (should be improved for production)
        curl_setopt($cs, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($cs, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($cs, CURLOPT_HTTPHEADER, $header);
        curl_setopt($cs, CURLOPT_ENCODING, "");

        // Execute cURL request and capture the response
        $response = curl_exec($cs);

        // Check if there was an error in the cURL execution
        if (curl_errno($cs)) {
            $response = 'cURL Error: ' . curl_error($cs);
        }

        // Get HTTP response code
        $http_code = curl_getinfo($cs, CURLINFO_HTTP_CODE);

        // Update the API response in the database
        $this->CI->api_model->update_api_response($response, $insert_id);

        // Close the cURL session
        curl_close($cs);

        // Return the API response
        return $response;
    }
    /**
     * Process XML API Request
     * 
     * @param string $post_url The URL to which the request is sent
     * @param string $request The XML request data
     * @param array $header The HTTP headers for the request
     * 
     * @return string The API response
     */
    function xml_process_request(string $post_url, string $request, array $header): string
    {
        // Store the API request details
        $insert_id = $this->CI->api_model->store_api_request($post_url, $request, 'HotelBook(HB)');
        $insert_id = intval($insert_id['insert_id']);

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

        // Disable SSL verification for testing (this should be fixed for production)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        // Set the HTTP headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // Execute cURL request and capture the response
        $response = curl_exec($ch);

        // Check for any errors during the cURL request
        if (curl_errno($ch)) {
            $response = 'cURL Error: ' . curl_error($ch);
        }

        // Get the HTTP response code
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Update the API response in the database
        $this->CI->api_model->update_api_response($response, $insert_id);

        // Close the cURL session
        curl_close($ch);

        // Return the API response
        return $response;
    }
    /**
     * Process DELETE API Request
     * 
     * @param string $post_url The URL for the DELETE request
     * @param array $api_header The headers for the DELETE request
     * @param string $remarks Optional remarks related to the request
     * 
     * @return string The response from the API request
     */
    function delete_process_request(string $post_url, array $api_header, string $remarks): string
    {
        // Store the API request details
        $insert_id = $this->CI->api_model->store_api_request($post_url, '', $remarks);
        $insert_id = intval($insert_id['insert_id']);

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options for the DELETE request
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        // Disable SSL verification for testing (should be fixed for production)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        // Set the HTTP headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $api_header);

        // Execute the DELETE request and capture the response
        $response = curl_exec($ch);

        // Check if there was an error during the cURL request
        if (curl_errno($ch)) {
            $response = 'cURL Error: ' . curl_error($ch);
        }

        // Get the HTTP response code
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Update the API response in the database
        $this->CI->api_model->update_api_response($response, $insert_id);

        // Close the cURL session
        curl_close($ch);

        // Return the API response
        return $response;
    }
    /**
     * Process API Request with Authentication
     * 
     * @param string $request The request data to be sent to the API
     * @param array $header The HTTP headers for the request
     * @param string $url The API endpoint URL
     * @param string $remarks Optional remarks related to the request
     * 
     * @return string The response from the API request
     */
    function auth_process_request(string $request, array $header, string $url, string $remarks = ''): string
    {
        // Store the API request details in the database
        $insert_id = $this->CI->api_model->store_api_request($url, $request, $remarks);
        $insert_id = intval($insert_id['insert_id']);

        // Initialize cURL session
        $cs = curl_init();
        $response = '';

        // Set cURL options for the API request
        curl_setopt($cs, CURLOPT_URL, $url);
        curl_setopt($cs, CURLOPT_TIMEOUT, 180);
        curl_setopt($cs, CURLOPT_HEADER, 0);
        curl_setopt($cs, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cs, CURLOPT_SSL_VERIFYHOST, 0);  // Consider enabling this in production
        curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, 0);  // Consider enabling this in production
        curl_setopt($cs, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($cs, CURLOPT_HTTPHEADER, $header);
        curl_setopt($cs, CURLOPT_ENCODING, "gzip");

        try {
            // Execute the cURL request
            $response = curl_exec($cs);

            // Check for cURL errors
            if (curl_errno($cs)) {
                $response = 'cURL Error: ' . curl_error($cs);
            }
        } catch (Exception $e) {
            // If an exception occurs, set the response to an error message
            $response = 'Exception: ' . $e->getMessage();
        }

        // Update the API response in the database
        $this->CI->api_model->update_api_response($response, $insert_id);

        // Get the HTTP status code
        $http_code = curl_getinfo($cs, CURLINFO_HTTP_CODE);

        // Close the cURL session
        curl_close($cs);

        // Return the API response
        return $response;
    }
    /**
     * Get the smoking preference code
     * 
     * @param string $preference The smoking preference as a string
     * 
     * @return int The corresponding code for the preference
     */
    function get_smoking_preference(string $preference): int
    {
        return match ($preference) {
            'NoPreference' => 0,
            'Smoking' => 1,
            'NonSmoking' => 2,
            'Either' => 3,
            default => -1, // Return -1 for unknown preference
        };
    }
    /**
     * Returns Cancellation status description
     * 
     * @param int $ChangeRequestStatus The status code for the change request
     * 
     * @return string The description corresponding to the status code
     */
    private function get_cancellation_status_description(int $ChangeRequestStatus): string
    {
        return match ($ChangeRequestStatus) {
            1 => 'Pending',
            2 => 'InProgress',
            3 => 'Processed',
            4 => 'Rejected',
            default => 'NotSet', // Return 'NotSet' for unknown status codes
        };
    }
    /**
     * Sorts an array alphabetically by a specific key and order.
     * 
     * @param array $array The array to sort
     * @param string $on The key to sort by
     * @param int $order The sorting order (SORT_ASC or SORT_DESC)
     * 
     * @return array The sorted array
     */
    private function array_sort(array $array, string $on, int $order = SORT_ASC): array
    {
        $sortable_array = [];

        // Extract the values based on the key specified by $on
        foreach ($array as $k => $v) {
            $sortable_array[$k] = is_array($v) && isset($v[$on]) ? $v[$on] : $v;
        }

        // Sort based on the specified order
        match ($order) {
            SORT_ASC => asort($sortable_array),
            SORT_DESC => arsort($sortable_array),
        };

        // Rebuild the sorted array
        return array_map(fn($k) => $array[$k], array_keys($sortable_array));
    }
    /**
     * Get hotel property images
     * 
     * @param array $request The request array containing hotel code
     * 
     * @return array The response containing status and image paths
     */
    public function Get_Hotel_Property_Images(array $request): array
    {
        $hotel_images = $this->CI->custom_db->single_table_records(
            'master_hotel_details_beta_hb',
            'images',
            ['hotel_code' => $request['hotel_code']],
            0,
            20
        );

        $response = [
            'status' => FAILURE_STATUS,
            'images' => []
        ];

        if ($hotel_images['status'] == 1) {
            $response['status'] = SUCCESS_STATUS;
            $decoded_images = json_decode($hotel_images['data'][0]['images'], true);

            // Check if json_decode was successful
            if (json_last_error() == JSON_ERROR_NONE) {
                $image_arr = array_map(function ($value) {
                    return $this->config['medium_image_base_url'] . $value['path'];
                }, $decoded_images);

                $response['images'] = $image_arr;
            } else {
                // Handle error if JSON decoding fails
                $response['status'] = FAILURE_STATUS;
                $response['error'] = 'Failed to decode image data.';
            }
        }

        return $response;
    }
    /**
     * Format the booking response into a structured array
     * 
     * @param array $book_response The booking response array to format
     * 
     * @return array The formatted booking response
     */
    public function format_booking_response(array $book_response): array
    {
        // Extracting the booking data
        $booking_data = $book_response['bookingRS']['booking'];
        $holder = $booking_data['holder']['@attributes'];

        // Formatting room and hotel details
        $room_details = $this->format_booked_room_details($booking_data['hotel']['rooms']['room']);
        $hotel_details = $booking_data['hotel']['@attributes'];
        $hotel_details['supplier'] = $booking_data['hotel']['supplier']['@attributes'];
        $hotel_details['rooms'] = $room_details;

        // Formatting booking details
        $booking_details = $booking_data['@attributes'];
        $booking_details['lead_pax'] = $holder;
        $booking_details['hotel'] = $hotel_details;

        // Preparing the final response structure
        return [
            'status' => SUCCESS_STATUS,
            'data' => [
                'booking_details' => $booking_details,
            ],
            'message' => [],
        ];
    }
    /**
     * Format the booked room details.
     * 
     * @param array $room_data The room data to format
     * 
     * @return array The formatted room details
     */
    function format_booked_room_details(array $room_data): array
    {
        $core_room_details = force_multple_data_format($room_data); // Assumed to ensure array structure

        $room_details = [];

        foreach ($core_room_details as $r_k => $r_v) {
            // Passenger details
            $paxes = force_multple_data_format($r_v['paxes']['pax']);
            foreach ($paxes as $p_k => $p_v) {
                $paxes[$p_k] = $p_v['@attributes'];  // Extract attributes
            }

            // Rate details
            $temp_rate = $r_v['rates']['rate'];
            $temp_rate['cancellationPolicies'] = force_multple_data_format($temp_rate['cancellationPolicies']['cancellationPolicy']);
            $temp_rate['rateBreakDown'] = force_multple_data_format($temp_rate['rateBreakDown']['rateSupplements']['rateSupplement'] ?? []);

            $rate = $temp_rate['@attributes'] ?? [];

            // Handle cancellation policies
            foreach ($temp_rate['cancellationPolicies'] as $c_k => $c_v) {
                $rate['cancellationPolicies']['cancellationPolicy'][$c_k] = $c_v['@attributes'] ?? [];
            }

            // Handle rate breakdowns
            $rateBreakDown = [];
            if (isset($temp_rate['rateBreakDown']) && valid_array($temp_rate['rateBreakDown'])) {
                foreach ($temp_rate['rateBreakDown'] as $fb_k => $fb_v) {
                    $rateBreakDown[$fb_k] = $fb_v['@attributes'] ?? [];
                }
            }

            // Assigning rate and breakdown details
            $rates = $rate;
            $rates['rateBreakDown'] = $rateBreakDown;

            // Assign room details
            $room_details[$r_k] = $r_v['@attributes'] ?? [];
            $room_details[$r_k]['rates'] = $rates;
            $room_details[$r_k]['paxes'] = $paxes;
        }

        return $room_details;
    }
    /**
     * Get hotel information including static info and facility details.
     *
     * @param string $hotel_code The unique identifier for the hotel.
     * @param array $hotel_facilities_description (optional) The hotel facilities description.
     * @param array $hotel_facilities_group (optional) The hotel facilities group.
     * 
     * @return array The merged hotel information.
     */
    public function get_hotel_info(string|array $hotel_code, array $hotel_facilities_description = [], array $hotel_facilities_group = []): array
    {
        // Check if hotel code is provided
        if (empty($hotel_code)) {
            return []; // Return an empty array if no hotel code is provided
        }

        // Fetch static hotel info and facility details
        $static_hotel_info = $this->get_static_hotel_info($hotel_code);
        $static_hotel_facility_info = $this->get_static_hotel_facility_info(
            $hotel_code,
            '',
            'icon',
            $hotel_facilities_description,
            $hotel_facilities_group
        );

        // Return merged data
        return array_merge($static_hotel_info, $static_hotel_facility_info);
    }
    /**
     * Fetch static hotel information based on hotel code.
     *
     * @param string|array $hotel_code The hotel code(s) for which the information is to be fetched.
     * @param string $cols The columns to select in the query (optional).
     * 
     * @return array The hotel information, indexed by hotel code.
     */
    public function get_static_hotel_info(array|string $hotel_code, string $cols = ''): array
    {
        // Determine the condition for the hotel code(s)
        if (is_array($hotel_code)) {
            $h_cond = 'h.hotel_code IN (' . implode(',', array_map([$this->CI->db, 'escape'], $hotel_code)) . ')';
        } else {
            $h_cond = 'h.hotel_code = ' . $this->CI->db->escape($hotel_code);
        }

        // Set default columns if none are provided
        if (empty($cols)) {
            $cols = 'h.image AS img, h.images AS images, h.hotel_code AS hc, h.address AS address,
                 h.country_code AS country_code, h.email, h.website, h.hotel_desc AS description';
        }

        // Construct the query
        $query = 'SELECT ' . $cols . ' FROM master_hotel_details_beta_hb AS h WHERE ' . $h_cond;

        // Execute the query and get the result
        $data = $this->CI->db->query($query)->result_array();

        // Initialize response array
        $resp = [];

        // If data is valid, process it and return the hotel info
        if (!empty($data)) {
            foreach ($data as $v) {
                $resp['info'][$v['hc']] = [
                    'country_code' => $v['country_code'],
                    'address' => $v['address'],
                    'img' => $v['img'],
                    'images' => $v['images']
                ];
            }
        }

        // Return the processed hotel information
        return $resp;
    }
    /**
     * Get static hotel facility information.
     *
     * @param string|array $hotel_code The hotel code(s) for which the facility info is fetched.
     * @param string $cols The columns to select in the query (optional).
     * @param string $filter A filter to apply to the query (optional).
     *
     * @return array The facility information, indexed by hotel code.
     */
    function get_static_hotel_facility_info(string|array $hotel_code, string $cols = '', string $filter = ''): array
    {
        // Construct the hotel code condition based on the input type (string or array)
        if (is_array($hotel_code)) {
            $h_cond = 'hf.hotel_code IN (' . implode(',', array_map([$this->CI->db, 'escape'], $hotel_code)) . ')';
        } else {
            $h_cond = 'hf.hotel_code = ' . $this->CI->db->escape($hotel_code);
        }

        // Add filter condition if applicable
        if (!empty($filter)) {
            $h_cond .= ' AND hfd.icon_class != ""';
        }

        // Default columns to select if not provided
        if (empty($cols)) {
            $cols = 'hf.hotel_code AS hc, hf.indFee AS additional_cost, hf.facility_code AS fc, 
                 hfd.description AS name, hfd.icon_class, hfd.origin AS facility_number';
        }

        // Construct the SQL query
        $query = 'SELECT ' . $cols . '
              FROM hb_hotel_facilities AS hf
              JOIN hb_hotel_facilities_description AS hfd ON hf.facility_description_id = hfd.origin
              JOIN hb_facilities_group AS hfg ON hfg.code = hfd.facility_group_code 
              WHERE ' . $h_cond;

        // Execute the query and retrieve results
        $data = $this->CI->db->query($query)->result_array();

        // Initialize response array
        $resp = [];

        // If valid data exists, process it
        if (!empty($data)) {
            $arr_facilities = [];

            foreach ($data as $facility) {
                // Ensure unique facilities per hotel code
                $facility_key = $facility['icon_class'] . $facility['hc'];

                if (!in_array($facility_key, $arr_facilities)) {
                    $arr_facilities[] = $facility_key;
                    $resp['facility'][$facility['hc']][] = $facility;
                }
            }
        }

        // Return the facility information
        return $resp;
    }
    /**
     * Get static hotel facility information.
     *
     * @param string|array $hotel_code The hotel code(s) for which the facility info is fetched.
     * @param string $cols The columns to select in the query (optional).
     * @param string $filter A filter to apply to the query (optional).
     * @param array $hb_hotel_facilities_description Facility descriptions.
     * @param array $hb_hotel_facilities_group Facility group descriptions.
     *
     * @return array The facility information, indexed by hotel code.
     */
    /**
     * Get static hotel facility information with an optional filter.
     *
     * @param string|array $hotel_code The hotel code(s) to fetch facility info for.
     * @param string $cols The columns to select in the query (optional).
     * @param string $filter A filter to apply to the query (optional).
     *
     * @return array Grouped facilities information by hotel code and facility group description.
     */
    function get_static_hotel_facility_info_old1($hotel_code, $cols = '', $filter = '')
    {
        // Initialize response array
        $resp = [];

        // Check if hotel_code is an array, escape each value in array
        if (is_array($hotel_code)) {
            $h_cond = 'hf.hotel_code IN (' . implode(',', array_map([$this->CI->db, 'escape'], $hotel_code)) . ')';
        } else {
            $h_cond = 'hf.hotel_code = ' . $this->CI->db->escape($hotel_code);
        }

        // Apply filter if specified (icon_class != "")
        if (!empty($filter)) {
            $h_cond .= ' AND hfd.icon_class != ""';
        }

        // Default columns if none are provided
        if (empty($cols)) {
            $cols = 'hf.hotel_code AS hc, hf.indFee AS additional_cost, hf.facility_code AS fc,
                 hfd.description AS name, hfd.origin AS facility_number, hfg.description AS fac_group_desc';
        }

        // Construct query to fetch hotel facilities data
        $query = 'SELECT ' . $cols . '
              FROM hb_hotel_facilities AS hf
              JOIN hb_hotel_facilities_description AS hfd ON hf.facility_description_id = hfd.origin
              JOIN hb_facilities_group AS hfg ON hfg.code = hfd.facility_group_code
              WHERE ' . $h_cond;

        // Execute the query and fetch the result
        $data = $this->CI->db->query($query)->result_array();

        // Initialize array to hold grouped facility data
        $facil_array = [];

        // Check if data exists and process it
        if (!empty($data)) {
            foreach ($data as $facility) {
                // Group by hotel code
                $resp['facility'][$facility['hc']][] = $facility;

                // Group by facility group description
                $facil_array['facility'][$facility['hc']][$facility['fac_group_desc']][] = $facility;
            }
        }

        return $facil_array;
    }
    /**
     * Get hotel information from the bed_trip_advisor_new table.
     *
     * @param string|array $hotel_code Hotel code or an array of hotel codes.
     * @return array Result set from the query.
     */
    private function get_hotel_bed_trip_adv($hotel_code)
    {
        // If $hotel_code is an array, escape each value
        if (is_array($hotel_code)) {
            $hotel_code = implode(',', array_map([$this->CI->db, 'escape'], $hotel_code));
        } else {
            // If it's a single value, escape it directly
            $hotel_code = $this->CI->db->escape($hotel_code);
        }

        // Construct the query with proper escaping
        $query = 'SELECT h_code, tri_adv_hotel FROM bed_trip_advisor_new WHERE h_code IN (' . $hotel_code . ')';

        // Execute the query and fetch the result
        $result_data = $this->CI->db->query($query)->result_array();

        return $result_data;
    }
    public function get_hold_booking_status($request)
    {
        $data = [];
        $app_reference = $request['AppReference'];

        $data['status'] = false;
        $data['data'] = [];

        // Ensure the app_reference is properly escaped to prevent SQL injection
        $escaped_app_reference = $app_reference;
        // Get booking details for the confirmed booking
        $get_booking_details = $this->CI->custom_db->single_table_records(
            'hotel_booking_details',
            '*',
            array(
                'status' => 'BOOKING_CONFIRMED',
                'app_reference' => $escaped_app_reference
            )
        );
        // Check if the query was successful and booking details were found
        if ($get_booking_details['status'] == 1 && !empty($get_booking_details['data'])) {
            // Extract the relevant booking data
            $booking_details = $get_booking_details['data'][0];
            $data['data'] = [
                'booking_id' => $booking_details['booking_id'],
                'ConfirmationNo' => $booking_details['confirmation_reference'],
                'BookingRefNo' => $booking_details['booking_reference'],
                'booking_status' => $booking_details['status']
            ];

            $data['status'] = true;
        } else {
            // If no matching records are found, return an empty status
            $data['status'] = false;
            $data['data'] = [];
        }

        return $data;
    }
}
