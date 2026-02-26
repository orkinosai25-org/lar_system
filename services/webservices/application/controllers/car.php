<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Car extends CI_Controller {

    private static $credential_type;
    private static $track_log_id;
    private $domain_id = 0;
    private $course_version = CAR_VERSION_1;

    function __construct() {
        parent::__construct();
        $this->load->library('domain_management');
        $this->load->library('Exception_Logger', ['meta_course' => META_CAR_COURSE]);
        $this->load->library('car/car_blender');
        $this->load->model('car_model');
    }

    public function is_valid_user(array $auth_details = []): array
    {
        $UserName = $auth_details['HTTP_X_USERNAME'] ?? '';
        $Password = $auth_details['HTTP_X_PASSWORD'] ?? '';
        $DomainKey = $auth_details['HTTP_X_DOMAINKEY'] ?? '';
        $System = $auth_details['HTTP_X_SYSTEM'] ?? '';
        $SERVER_IP = $auth_details['REMOTE_ADDR'] ?? '';

        $domain_user_details = [
            'System' => $System,
            'DomainKey' => $DomainKey,
            'UserName' => $UserName,
            'Password' => $Password,
            'SERVER_ADDR' => $SERVER_IP
        ];

        $course = META_CAR_COURSE;
        $domin_details = $this->domain_management->validate_domain($domain_user_details);
        if ($domin_details['status'] != SUCCESS_STATUS) {
            return $domin_details;
        }

        $course_version = $this->domain_management->validate_domain_course_version(
            $course,
            $this->course_version,
            $domin_details['data']
        );

        if ($course_version['status'] != SUCCESS_STATUS) {
            return $course_version;
        }

        $this->domain_id = (int) $domin_details['data']['domain_origin'];
        self::$credential_type = $domain_user_details['System'];
        self::$track_log_id = PROJECT_PREFIX . '-DOMAIN-' . $this->domain_id . '-' . time();

        return $domin_details;
    }


    public static function get_credential_type() {
        return self::$credential_type;
    }

    public function service($request_type)
    {
        $response = [];

        if (!$this->is_Valid_Method($request_type)) {
            output_service_json_data([
                'status' => FAILURE_STATUS,
                'message' => 'Invalid Service URL.'
            ]);
        }

        $request = file_get_contents("php://input");

        if (!isJson($request)) {
            output_service_json_data([
                'status' => FAILURE_STATUS,
                'message' => 'Invalid Request Format.'
            ]);
        }

        $headers_info = $_SERVER;
        $is_valid_domain = $this->is_valid_user($headers_info);

        if ($is_valid_domain['status'] != true) {
            output_service_json_data([
                'status' => FAILURE_STATUS,
                'message' => $is_valid_domain['message']
            ]);
        }

        $domain_origin = $is_valid_domain['data']['domain_origin'] ?? 0;
        $this->total_request_count($domain_origin);

        $this->load->model('api_model');
        $request_data = json_decode($request, true);
        $request_client_key = 'Car_' . $is_valid_domain['data']['domain_key'] . '_' . $request_type;
        $this->api_model->store_client_request($request_client_key, $request_data, 'CAR');

        $cache_track_log_flag = $this->api_model->inactive_client_cache_services($request_type);

        if ($cache_track_log_flag == false) {
            $this->domain_management_model->create_track_log(
                self::$track_log_id,
                $request_type . ' - Started - Car'
            );
        }

        $method_map = [
            'Search' => 'Search',
            'RateRule' => 'RateRule',
            'Booking' => 'Booking',
            'CancelBooking' => 'CancelBooking'
        ];

        $response = method_exists($this, $method_map[$request_type] ?? '')
            ? $this->{$method_map[$request_type]}($request_data)
            : ['status' => FAILURE_STATUS, 'message' => 'Invalid Service'];

        if ($cache_track_log_flag == false) {
            $this->domain_management_model->create_track_log(
                self::$track_log_id,
                json_encode([$request_type . ' - Completed - Car', $response])
            );
        }

        $data = [
            'Status' => $response['status'] ?? FAILURE_STATUS,
            'Message' => $response['message'] ?? 'Unknown error'
        ];

        if (!empty($response['data'])) {
            $data[$request_type] = $response['data'];
        }

        output_service_json_data($data);
    }


    private function is_Valid_Method($method_name): bool {
        return in_array($method_name, ['Search', 'RateRule', 'Booking', 'CancelBooking'], true);
    }

    public function Search($request): array
    {
        $save_search_data = $this->car_model->save_search_history_data($request);
        
        if ($save_search_data['status'] != true) {
            return [
                'status' => FAILURE_STATUS,
                'message' => 'Invalid Search Request',
                'data' => []
            ];
        }

        $search_id = $save_search_data['search_id'];
        $cache_key = $save_search_data['cache_key'];
        $car_list = $this->car_blender->car_list($search_id, $cache_key);

        return [
            'status' => $car_list['status'],
            'message' => $car_list['message'] ?? '',
            'data' => $car_list['data'] ?? []
        ];
    }


    public function RateRule($request): array {
        $data = ['status' => FAILURE_STATUS, 'message' => '', 'data' => []];
        $ResultToken = trim($request['ResultToken']);
        $search_id_cache_key = $this->get_search_id_cache_key($ResultToken);

        return $search_id_cache_key['status'] == true
            ? $this->car_blender->car_rate_rules($request, $search_id_cache_key['search_id'], $search_id_cache_key['cache_key'])
            : array_merge($data, ['message' => 'Invalid CarRateRule Request']);
    }

    public function Booking($request): array {
        error_reporting(E_ALL);
        $data = ['status' => FAILURE_STATUS, 'message' => '', 'data' => []];
        $ResultToken = trim($request['ResultToken']);
        $search_id_cache_key = $this->get_search_id_cache_key($ResultToken);

        return $search_id_cache_key['status'] == true
            ? $this->car_blender->process_booking($request, $search_id_cache_key['search_id'], $search_id_cache_key['cache_key'])
            : array_merge($data, ['message' => 'Invalid Booking Request']);
    }

    private function get_search_id_cache_key($ResultToken): array {
        $data = ['status' => FAILURE_STATUS];
        $cache_key = $this->redis_server->extract_cache_key($ResultToken);
        $search_history = $this->custom_db->single_table_records('search_history', '*', ['cache_key' => trim($cache_key)]);

        if ($search_history['status'] == SUCCESS_STATUS && !empty($search_history['data'][0])) {
            $data = [
                'status' => SUCCESS_STATUS,
                'search_id' => $search_history['data'][0]['origin'],
                'cache_key' => trim($search_history['data'][0]['cache_key'])
            ];
        }

        return $data;
    }

    public function CancelBooking($request): array {
        $data = ['status' => FAILURE_STATUS, 'message' => '', 'data' => []];
        return !empty($request)
            ? $this->car_blender->cancel_booking($request)
            : array_merge($data, ['message' => 'Invalid CancelBooking Request']);
    }

    function total_request_count($domain_origin) {
        $this->load->model('api_model');
        $get_details = $this->api_model->get_user_count_per_day_per_user($domain_origin);
        $daily_allowed_limit = $this->api_model->get_allowed_lmit();

        $total_count_running = $get_details[0]['totalcount'] ?? 0;
        $daily_limit = $daily_allowed_limit['limit_request'] ?? PHP_INT_MAX;

        if ($total_count_running > $daily_limit) {
            $this->custom_db->update_record('domain_list', ['status' => 0], ['origin' => $domain_origin]);
        }
    }
}
