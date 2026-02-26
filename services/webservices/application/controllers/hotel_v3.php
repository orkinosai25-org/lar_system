<?php
declare(strict_types=1);
error_reporting(E_ALL);
        ini_set('display_errors',1);
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Hotel_v3 extends CI_Controller {

    /**
     * 1.Validate the Request Based On Method
     */
    private static $credential_type; //live/test
    private static $track_log_id; //Track Log ID
    private $domain_id = 0; //domain origin
    private $course_version = HOTEL_VERSION_3; //course version

    function __construct() {
      
        parent::__construct();
        $this->load->library('domain_management');
        $this->load->library('Exception_Logger', array('meta_course' => META_ACCOMODATION_COURSE));
        $this->load->library('hotel/GRN/hotel_blender_v3');
        $this->load->library('hotel/GRN/common_hotel_v3');
        $this->load->model('hotel_model_v3');
      
    }
    public function is_valid_user(array $auth_details = []): array
    {
        $UserName   = $auth_details['HTTP_X_USERNAME'] ?? '';
        $Password   = $auth_details['HTTP_X_PASSWORD'] ?? '';
        $DomainKey  = $auth_details['HTTP_X_DOMAINKEY'] ?? '';
        $System     = $auth_details['HTTP_X_SYSTEM'] ?? '';
        $SERVER_IP  = $auth_details['REMOTE_ADDR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        $domain_user_details = [
            'System'       => $System,
            'DomainKey'    => $DomainKey,
            'UserName'     => $UserName,
            'Password'     => $Password,
            'SERVER_ADDR'  => $SERVER_IP,
        ];

        $this->custom_db->insert_record('customer_ip_address', $domain_user_details);

        $course = META_ACCOMODATION_COURSE;
        $domin_details = $this->domain_management->validate_domain($domain_user_details);

        if ($domin_details['status'] != SUCCESS_STATUS) {
            return $domin_details;
        }

        // Validate domain course version
        $c_version = $this->domain_management->validate_domain_course_version(
            $course,
            $this->course_version,
            $domin_details['data']
        );

        if ($c_version['status'] != SUCCESS_STATUS) {
            return $c_version;
        }

        $this->domain_id = (int) ($domin_details['data']['domain_origin'] ?? 0);
        self::$credential_type = $System;
        self::$track_log_id = PROJECT_PREFIX . '-DOMAIN-' . $this->domain_id . '-' . time();

        return $domin_details;
    }

    /**
     * Return Environment Live/Test
     */
    public static function get_credential_type() {
        return self::$credential_type;
    }

    /**
     * Handles hotel Requests
     * @param unknown_type $request_type
     * @param unknown_type $request
     * @param unknown_type $_header
     */
    public function service(string $request_type): void
    {
        $requestBody = file_get_contents("php://input");
        $headers = $_SERVER;

        // Validate domain
        $is_valid_domain = $this->is_valid_user($headers);
        if (($is_valid_domain['status'] ?? false) != true) {
            output_service_json_data([
                'Status' => FAILURE_STATUS,
                'Message' => $is_valid_domain['message'] ?? 'Invalid request'
            ]);
            return;
        }

        $domain_origin = (int)$is_valid_domain['data']['domain_origin'] ?? 0;

        $this->total_request_count($domain_origin);

        $this->load->model('api_model');

        $request = json_decode($requestBody, true) ?? [];

        // Log client request
        $module_type = 'HOTELV3';
        $this->api_model->store_client_request($request_type, $request, $module_type);

        // Track log
        $cache_track_log_flag = $this->api_model->inactive_client_cache_services($request_type);
        if ($cache_track_log_flag == false) {
            $this->domain_management_model->create_track_log(self::$track_log_id, "$request_type - Started - hotel");
        }

        // Process request
        switch ($request_type) {
                case 'Search' :
                    $response = $this->Search($request);
                    break;
                case 'HotelDetails' :
                    $response = $this->HotelDetails($request);
                    break;
                case 'RoomList' :
                    $response = $this->RoomList($request);
                    break;
				case 'RoomFacilities' :
                    $response = $this->RoomFacilities($request);
                    break;
				case 'BlockRoom' :
                    $response = $this->BlockRoom($request);
                    break;
                case 'CommitBooking' :
                    $response = $this->CommitBooking($request);
                    break;
                case 'CancelBooking' :
                    $response = $this->CancelBooking($request);
                    break;
                //addedby ela
                case 'GetCancellationPolicy':
                    $response = $this->GetCancellationPolicy($request);
                    break;
                //addedby ela
                case 'GetHotelImages':
                    $response = $this->GetHotelImages($request);
                    break;
                case 'CancellationRefundDetails' :
                    $response = $this->CancellationRefundDetails($request);
                    break;
                case 'UpdateHoldBooking':
                    $response = $this->GetHotelHoldBookingStatus($request);
                break;
				case 'GetBookingDetails':
                    $response = $this->GetHoteBookingDetails($request);
                break;
                case 'HotelCityList':

                    $response = $this->GetHotelCityList();
					$cache_track_log_flag=true;
                break;
                default:
                    $response['status'] = FAILURE_STATUS;
                    $response['message'] = 'Invalid Service';
            }

        // Complete track log
        /*if ($cache_track_log_flag == false) {
            $track_log_comments = json_encode([$request_type . ' - Completed - hotel', $response]);
            // Uncomment if needed:
            // $this->domain_management_model->create_track_log(self::$track_log_id, $track_log_comments);
        }*/

        // Prepare and output response
        $data = [
            'Status' => $response['status'] ?? FAILURE_STATUS,
            'Message' => $response['message'] ?? 'Invalid Service'
        ];

        if (!empty($response['data']) && is_array($response['data'])) {
            $data[$request_type] = $response['data'];
        }

        output_service_json_data($data);
    }

    //Get Cacellation Policy
   

    public function GetCancellationPolicy(array $request): array
    {
        $policyCode = trim($request['policy_code'] ?? '');
        if (!$policyCode) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid Cancellation Request',
            ];
        }

        $searchId = $request['tb_search_id'] ?? '';
        $policy = $this->hotel_blender_v3->get_cancellation_policy($request, $searchId);

        return [
            'status' => $policy['status'],
            'message' => $policy['message'],
            'data' => ['policy' => $policy['data']],
        ];
    }

    public function RoomFacilities(array $request): array
    {
        $hotelCode = trim($request['hotel_code'] ?? '');
        $roomCode = trim($request['room_code'] ?? '');

        if (!$hotelCode || !$roomCode) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid RoomFacility Request',
            ];
        }

        $facilities = $this->hotel_blender_v3->get_room_facilities($hotelCode, $roomCode);

        return [
            'status' => $facilities['status'],
            'message' => $facilities['message'],
            'data' => $facilities['data'],
        ];
    }

    public function GetHotelHoldBookingStatus(array $request): array
    {
        if (empty($request['AppReference'])) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid Hotel Hold Booking Request',
            ];
        }

        $status = $this->hotel_blender_v3->get_hold_booking_status($request);

        return [
            'status' => $status['status'],
            'message' => $status['message'],
            'data' => $status['data'],
        ];
    }

    public function GetHotelImages(array $request): array
    {
        if (empty($request['hotel_code'])) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid Hotel Image Request',
            ];
        }

        $images = $this->hotel_blender_v3->get_hotel_images($request);

        return [
            'status' => $images['status'],
            'message' => $images['message'],
            'data' => $images['data'],
        ];
    }

    public function GetHotelCityList(): array
    {
        return [
            'status' => SUCCESS_STATUS,
            'message' => 'Hotel City List Success',
            'data' => $this->hotel_model_v3->get_hotel_city_list_v3(),
        ];
    }

    public function GetHoteBookingDetails(array $request): array
    {
        $appRef = $request['app_reference'] ?? $request['AppReference'] ?? '';
        if (!$appRef) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid Request',
            ];
        }

        $booking = $this->hotel_blender_v3->get_hold_booking_status($request);
        return [
            'status' => $booking['status'],
            'message' => $booking['message'],
            'data' => $booking['data'],
        ];
    }
    public function Search(array $request): array
    {
        $data = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];

        $checkInDate = $request['CheckInDate'] ?? '';
        if (!$this->validateDate($checkInDate)) {
            $request['CheckInDate'] = date('d-m-Y', strtotime($checkInDate));
        }

        if (strtotime($request['CheckInDate']) < strtotime(date('d-m-Y'))) {
            $data['message'] = 'Invalid Checkin Date';
            return $data;
        }

        if (($request['NoOfNights'] ?? 0) > 31) {
            $data['message'] = 'Maximum number of nights can be allowed 31 days';
            return $data;
        }

        foreach ($request['RoomGuests'] ?? [] as $index => $room) {
            if (count($room['ChildAge'] ?? []) > 2) {
                $data['message'] = 'NoOfChild cannot be more than 2 per room';
                return $data;
            }

            foreach (($room['ChildAge'] ?? []) as $age) {
                if ($age < 1) {
                    $data['message'] = 'ChildAge is mandatory for room ' . ($index + 1);
                    return $data;
                }
            }
        }

        $save = $this->hotel_model_v3->save_search_history_data($request);
        if (empty($save['status'])) {
            $data['message'] = 'Invalid Search Request';
            return $data;
        }

        $searchId = $save['search_id'];
        $cacheKey = $save['cache_key'];
        $hotels = $this->hotel_blender_v3->hotel_list($searchId, $cacheKey);

        if ($hotels['status'] == SUCCESS_STATUS) {
            return [
                'status' => SUCCESS_STATUS,
                'message' => '',
                'data' => $hotels['data'],
            ];
        }

        $data['message'] = $hotels['message'] ?? 'Hotel list retrieval failed';
        return $data;
    }
    


    /**
     * HotelDetails
     * 
     */
     public function HotelDetails(array $request): array
    {
        $ResultToken = trim($request['ResultToken'] ?? '');
        $search_id_cache_key = $this->get_search_id_cache_key($ResultToken);

        if (empty($search_id_cache_key['status'])) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid HotelDetails Request',
                'data' => []
            ];
        }

        $search_id = (int)$search_id_cache_key['search_id'];
        $cache_key = $search_id_cache_key['cache_key'];
        return $this->hotel_blender_v3->hotel_details($request, $search_id, $cache_key);
    }

    public function RoomList(array $request): array
    {
        $ResultToken = trim($request['ResultToken'] ?? '');
        $search_id_cache_key = $this->get_search_id_cache_key($ResultToken);

        if (empty($search_id_cache_key['status'])) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid RoomList Request',
                'data' => []
            ];
        }

        $search_id = (int)$search_id_cache_key['search_id'];
        $cache_key = $search_id_cache_key['cache_key'];

        return $this->hotel_blender_v3->room_list($request, $search_id, $cache_key);
    }

    public function BlockRoom(array $request): array
    {
        $ResultToken = trim($request['ResultToken'] ?? '');
        $search_id_cache_key = $this->get_search_id_cache_key($ResultToken);

        $room_id_status = !empty($request['RoomUniqueId']) && valid_array($request['RoomUniqueId']);
        if ($room_id_status) {
            foreach ($request['RoomUniqueId'] as $r_v) {
                $room_unique_id =$this->common_hotel_v3->read_record($r_v);
                if (!valid_array($room_unique_id) || empty($room_unique_id[0])) {
                    $room_id_status = false;
                    break;
                }
            }
        }

        if (empty($search_id_cache_key['status']) || !$room_id_status) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid BlockRoom Request',
                'data' => []
            ];
        }

        $search_id = (int)$search_id_cache_key['search_id'];
        $cache_key = $search_id_cache_key['cache_key'];

        return $this->hotel_blender_v3->block_room($request, $search_id, $cache_key);
    }


    public function CommitBooking(array $request): array
    {
        $ResultToken = trim($request['ResultToken'] ?? '');
        $search_id_cache_key = $this->get_search_id_cache_key($ResultToken);

        if (empty($search_id_cache_key['status'])) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid CommitBooking Request or Session Expired',
                'data' => []
            ];
        }

        if (!$this->validate_commit_booking_request($request)) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid CommitBooking Request or Session Expired',
                'data' => []
            ];
        }

        $search_id = $search_id_cache_key['search_id'];
        $cache_key = $search_id_cache_key['cache_key'];

        return $this->hotel_blender_v3->process_booking($request, $search_id, $cache_key);
    }


    public function CancellationRefundDetails(array $request): void
    {
        $headers_list = $_SERVER;
        if ($this->is_valid_user($headers_list)) {
            $response = [
                'Status' => FAILURE_STATUS,
                'Message' => '',
                'RefundDetails' => []
            ];

            $AppReference = $request['AppReference'] ?? '';
            $ChangeRequestId = $request['ChangeRequestId'] ?? '';
            $cancellation_details = $this->hotel_model_v3->get_hotel_cancellation_details(
                $AppReference,
                $ChangeRequestId,
                get_domain_auth_id()
            );

            if (valid_array($cancellation_details)) {
                $cancellation_details = $cancellation_details[0];
                $currency_rate = $cancellation_details['currency_conversion_rate'];
                $ChangeRequestStatus = $cancellation_details['ChangeRequestStatus'];
                $domain_refund_status = $cancellation_details['refund_status'];

                $StatusDescription = $cancellation_details['status_description'];
                if ($domain_refund_status == 'PROCESSED') {
                    $ChangeRequestStatus = 3;
                    $StatusDescription = $domain_refund_status;
                }

                $RefundDetails = [
                    'ChangeRequestId' => $cancellation_details['ChangeRequestId'],
                    'ChangeRequestStatus' => $ChangeRequestStatus,
                    'StatusDescription' => $StatusDescription,
                    'RefundedAmount' => $cancellation_details['refund_amount'] * $currency_rate,
                    'CancellationCharge' => $cancellation_details['cancellation_charge'] * $currency_rate
                ];

                $response['Status'] = SUCCESS_STATUS;
                $response['RefundDetails'] = $RefundDetails;
            }

            header('Content-type: application/json');
            echo json_encode($response);
            exit();
        }
    }

   public function CancelBooking(array $request): array
    {
        if (!valid_array($request)) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid CancelBooking Request',
                'data' => []
            ];
        }

        return $this->hotel_blender_v3->cancel_booking($request);
    }

    private function get_search_id_cache_key(string $ResultToken): array
    {
        $data = ['status' => FAILURE_STATUS];
        $cache_key = $this->redis_server->extract_cache_key($ResultToken);
        $search_history = $this->custom_db->single_table_records('search_history', '*', ['cache_key' => trim($cache_key)]);

        if ($search_history['status'] == SUCCESS_STATUS && valid_array($search_history['data'][0])) {
            $record = $search_history['data'][0];
            $data = [
                'status' => SUCCESS_STATUS,
                'search_id' => $record['origin'],
                'cache_key' => trim($record['cache_key'])
            ];
        }

        return $data;
    }

    private function validate_commit_booking_request(array $request): bool
    {
        $valid = true;
        $block_data = $this->common_hotel_v3->read_record(trim($request['BlockRoomId']));
        if (empty($request['BlockRoomId']) || !valid_array($block_data)) {
            $valid = false;
        }

        if ($valid && (empty($request['AppReference']))) {
            $valid = false;
        }

        if ($valid && (!isset($request['RoomDetails']) || !valid_array($request['RoomDetails']))) {
            $valid = false;
        }

        return $valid;
    }

    public function validateDate(string $date, string $format = 'd-m-Y'): bool
    {
        $date_val = DateTime::createFromFormat($format, $date);
        return $date_val && $date_val->format($format) == $date;
    }

    public function total_request_count(int $domain_origin): void
    {
        $this->load->model('api_model');
        $get_details = $this->api_model->get_user_count_per_day_per_user($domain_origin);
        $daily_allowed_limit = $this->api_model->get_allowed_lmit();

        $total_count_running = (int) ($get_details[0]['totalcount'] ?? 0);
        $daily_limit = (int) ($daily_allowed_limit['limit_request'] ?? 0);

        if ($total_count_running > $daily_limit) {
            $this->custom_db->update_record('domain_list', ['status' => 0], ['origin' => $domain_origin]);
        }
    }
 
 
}
