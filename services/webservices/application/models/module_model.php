<?php

/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Jaganath J<jaganath.provab@gmail.com>
 * @version    V2
 */
Class Module_Model extends CI_Model
{
    /**
	 * get complete domain details
	 */

     public function domain_details(int $domain_origin): array
        {
            $query = 'SELECT DL.*, GROUP_CONCAT(DMM.meta_course_list_fk SEPARATOR "' . DB_SAFE_SEPARATOR . '") AS domain_modules
                    FROM domain_list AS DL
                    LEFT JOIN domain_module_map DMM ON DMM.domain_list_fk = DL.origin
                    WHERE DL.origin = ' . $this->db->escape((int) $domain_origin) . '
                    GROUP BY DL.origin';

            return $this->db->query($query)->result_array();
        }

    /**
	 * Domain Module Map Creation
	 * @param $domain_origin
	 * @param $module_origin
	 */

     public function create_domain_module_map(int $domain_origin, $module_origin): void
     {
            if (!is_array($module_origin)) {
                $module_origin = (array) $module_origin;
            }

            $domain_module_map = [
                'domain_list_fk' => $domain_origin,
                'status' => ACTIVE,
                'created_by_id' => (int) $this->entity_user_id,
                'created_datetime' => date('Y-m-d H:i:s')
            ];

            foreach ($module_origin as $v) {
                $domain_module_map['meta_course_list_fk'] = (int) $v;
                $this->custom_db->insert_record('domain_module_map', $domain_module_map);
            }
    }
    /**
	 * Get Module List
	 * Jaganath
	 */

     public function module_management(int $pk, string $course_id): array
     {
            $query = "SELECT MCL.*, BS.origin AS booking_source 
                    FROM meta_course_list MCL
                    LEFT JOIN activity_source_map ASM ON ASM.meta_course_list_fk = MCL.origin
                    LEFT JOIN booking_source BS ON BS.origin = ASM.booking_source_fk
                    WHERE MCL.origin = $pk AND MCL.course_id = '$course_id'
                    GROUP BY BS.origin";

            $tmp_data = $this->db->query($query);

            if ($tmp_data->num_rows() > 0) {
                $tmp_data = $tmp_data->result_array();
                return [
                    'status' => QUERY_SUCCESS,
                    'data' => $tmp_data
                ];
            }

            return [
                'status' => QUERY_FAILURE
            ];
     }

    /**
	 * Jaganath
	 * Booking source Details
	 */

     public function get_course_list(array $condition = []): array
    {
        $filter_condition = ' ';
        
        if (!empty($condition)) {
            $filter_condition = ' WHERE ';
            foreach ($condition as $k => $v) {
                $filter_condition .= implode($v) . ' AND ';
            }
        }

        $filter_condition = rtrim($filter_condition, ' AND ');
        
        $query = 'SELECT MCL.*, CONCAT(U.first_name, " ", U.last_name, "-", U.uuid) AS username, 
                        U.image AS user_image, GROUP_CONCAT(BS.name SEPARATOR ", ") AS booking_source
                FROM meta_course_list AS MCL
                JOIN user AS U ON MCL.created_by_id = U.user_id
                LEFT JOIN activity_source_map ASM ON ASM.meta_course_list_fk = MCL.origin
                LEFT JOIN booking_source BS ON BS.origin = ASM.booking_source_fk
                ' . $filter_condition . ' 
                GROUP BY MCL.course_id';

        return $this->db->query($query)->result_array();
    }

    /**
	 * Get active module list for domain
	 * @param $domain_key		unique origin key of domain
	 * @param $domain_auth_id	unique auth provab key for domain
	 */

     public function get_active_module_list(int $domain_origin, string $domain_key): array
    {
        $active_module_list = [];

        $query = 'SELECT GROUP_CONCAT(MCL.course_id SEPARATOR "' . DB_SAFE_SEPARATOR . '") AS domain_module 
                FROM domain_module_map AS DMM
                JOIN domain_list AS D ON DMM.domain_list_fk = D.origin
                JOIN meta_course_list AS MCL ON DMM.meta_course_list_fk = MCL.origin
                WHERE D.origin = ? 
                AND D.domain_key = ? 
                AND DMM.status = ? 
                AND D.status = ? 
                AND MCL.status = ? 
                GROUP BY D.origin';

        $active_module_list = $this->db->query($query, [
            $domain_origin, 
            $domain_key, 
            ACTIVE, 
            ACTIVE, 
            ACTIVE
        ])->row_array();

        if (isset($active_module_list['domain_module'])) {
            $active_module_list = explode(DB_SAFE_SEPARATOR, $active_module_list['domain_module']);
        }

        return $active_module_list;
    }

    /**
	 * Get active module list for domain
	 * @param $domain_key		unique origin key of domain
	 * @param $domain_auth_id	unique auth provab key for domain
	 */

    public function get_active_payment_module_list(): array
    {
        $active_payment_module = [];

        // Prepare the query using parameterized queries for security
        $query = 'SELECT GROUP_CONCAT(payment_category_code SEPARATOR "' . DB_SAFE_SEPARATOR . '") AS payment_category 
                FROM payment_option_list AS POL 
                WHERE POL.status = ?';

        // Execute the query with the active status parameter
        $active_payment_module = $this->db->query($query, [ACTIVE])->row_array();

        if (isset($active_payment_module['payment_category'])) {
            $active_payment_module = explode(DB_SAFE_SEPARATOR, $active_payment_module['payment_category']);
        }

        return $active_payment_module;
    }

    /**
	 * serialize temp booking details
	 * @param unknown_type $booking_params
	 */

     public function serialize_temp_booking_record(array $booking_params, string $module): array
     {
            // Generate the booking ID using the provided module and date-time
            $book_id = $module . date('d-His') . '-' . rand(1, 1000000);

            // Prepare the booking data
            $temp_booking = [
                'domain_list_fk'     => get_domain_auth_id(),
                'book_id'            => $book_id,
                'booking_source'     => $booking_params['booking_source'],
                'book_attributes'    => serialized_data($booking_params),
                'booking_ip'         => $_SERVER['REMOTE_ADDR'],
                'created_datetime'   => date('Y-m-d H:i:s')
            ];

            // Insert the record into the database and get the insert ID
            $temp_booking_origin = $this->custom_db->insert_record('temp_booking', $temp_booking);

            // Return the book ID and the insert ID
            return [
                'book_id'            => $book_id,
                'temp_booking_origin' => $temp_booking_origin['insert_id']
            ];
    }

    /**
	 * get back unserialized data
	 */

     public function unserialize_temp_booking_record(string $book_id, int $temp_book_origin): array|false
    {
        $temp_booking_details = $this->custom_db->single_table_records(
            'temp_booking',
            'domain_list_fk, booking_source, book_id, book_attributes',
            array('book_id' => $book_id, 'id' => $temp_book_origin)
        );

        if ($temp_booking_details['status'] === SUCCESS_STATUS) {
            $temp_booking_details['data'][0]['book_attributes'] = unserialized_data($temp_booking_details['data'][0]['book_attributes']);
            
            if (isset($temp_booking_details['data'][0]['book_attributes']['token'])) {
                $temp_booking_details['data'][0]['book_attributes']['token'] = unserialized_data($temp_booking_details['data'][0]['book_attributes']['token']);
            }
            
            return $temp_booking_details['data'][0];
        }

        return false;
    }

    /**
	 * 
	 * @param string $module
	 * @param string $op
	 * @param string $notification
	 */

     public function log_exception(string $module, string $op, string $notification, string $app_reference = '', string $log_file = ''): string
    {
        $data = [];
        $data['exception_id'] = 'EID-' . time() . '-' . rand(1, 100);
        $data['module'] = $module;
        $data['op'] = $op;
        $data['notification'] = $notification;
        $data['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $data['user_ip'] = $_SERVER['HTTP_HOST'];
        $data['domain_origin'] = get_domain_auth_id();
        $data['app_reference'] = $app_reference;
        $data['log_file'] = $log_file;
        $data['created_datetime'] = date('Y-m-d H:i:s');

        $this->custom_db->insert_record('exception_logger', $data);

        return $data['exception_id'];
    }

    public function check_app_reference(string $app_reference, string $module): bool
    {
        $query = '';

        if ($module === 'flight') {
            $query = 'SELECT count(*) AS total FROM flight_booking_details WHERE app_reference=' . $this->db->escape($app_reference);
        } elseif ($module === 'hotel') {
            $query = 'SELECT count(*) AS total FROM hotel_booking_details WHERE app_reference=' . $this->db->escape($app_reference);
        } elseif ($module === 'bus') {
            $query = 'SELECT count(*) AS total FROM bus_booking_details WHERE app_reference=' . $this->db->escape($app_reference);
        } elseif ($module === 'transfer') {
            $query = 'SELECT count(*) AS total FROM viatortransfer_booking_details WHERE app_reference=' . $this->db->escape($app_reference);
        } elseif ($module === 'sightseen') {
            $query = 'SELECT count(*) AS total FROM sightseeing_booking_details WHERE app_reference=' . $this->db->escape($app_reference);
        }

        $data = $this->db->query($query)->row_array();

        return (intval($data['total']) > 0) ? false : true;
    }

}