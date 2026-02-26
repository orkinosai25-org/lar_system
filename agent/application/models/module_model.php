<?php
declare(strict_types=1);
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A
 * @version    V2
 */
class Module_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        // Ensure entity_user_id is defined if used dynamically
        $this->entity_user_id ??= 0;
    }

    public function domain_details(int $domain_origin): array
    {
        $query = '
            SELECT DL.*, 
                   GROUP_CONCAT(DMM.meta_course_list_fk SEPARATOR "' . DB_SAFE_SEPARATOR . '") AS domain_modules 
            FROM domain_list AS DL 
            LEFT JOIN domain_module_map DMM ON DMM.domain_list_fk = DL.origin 
            WHERE DL.origin = ' . $domain_origin . ' 
            GROUP BY DL.origin
        ';
        return $this->db->query($query)->result_array();
    }

    public function create_domain_module_map(int $domain_origin, array $module_origin): void
    {
        $domain_module_map = [
            'domain_list_fk'   => $domain_origin,
            'status'           => ACTIVE,
            'created_by_id'    => (int) ($this->entity_user_id ?? 0),
            'created_datetime' => date('Y-m-d H:i:s'),
        ];

        foreach ($module_origin as $v) {
            $domain_module_map['meta_course_list_fk'] = (int) $v;
            $this->custom_db->insert_record('domain_module_map', $domain_module_map);
        }
    }

    public function module_management(int $pk, string $course_id): array
    {
        $query = "
            SELECT MCL.*, BS.origin AS booking_source 
            FROM meta_course_list MCL
            LEFT JOIN activity_source_map ASM ON ASM.meta_course_list_fk = MCL.origin
            LEFT JOIN booking_source BS ON BS.origin = ASM.booking_source_fk 
            WHERE MCL.origin = {$pk} AND MCL.course_id = " . $this->db->escape($course_id) . " 
            GROUP BY BS.origin
        ";
        $result = $this->db->query($query);
        return $result->num_rows() > 0
            ? ['status' => QUERY_SUCCESS, 'data' => $result->result_array()]
            : ['status' => QUERY_FAILURE];
    }

    public function get_course_list(array $condition = []): array
    {
        $filter_condition = '';
        if (!empty($condition)) {
            $filter_condition = ' WHERE ';
            foreach ($condition as $v) {
                $filter_condition .= implode('', $v) . ' AND ';
            }
            $filter_condition = rtrim($filter_condition, ' AND ');
        }

        $query = "
            SELECT MCL.*, 
                   CONCAT(U.first_name, ' ', U.last_name, '-', U.uuid) AS username, 
                   U.image AS user_image,
                   GROUP_CONCAT(BS.name SEPARATOR ', ') AS booking_source
            FROM meta_course_list AS MCL
            JOIN user AS U ON MCL.created_by_id = U.user_id
            LEFT JOIN activity_source_map ASM ON ASM.meta_course_list_fk = MCL.origin
            LEFT JOIN booking_source BS ON BS.origin = ASM.booking_source_fk
            {$filter_condition} 
            GROUP BY MCL.course_id
        ";

        return $this->db->query($query)->result_array();
    }

    public function get_active_module_list(int $domain_origin, string $domain_key): array
    {
        $query = '
            SELECT GROUP_CONCAT(MCL.course_id SEPARATOR "' . DB_SAFE_SEPARATOR . '") AS domain_module 
            FROM domain_module_map AS DMM 
            JOIN domain_list AS D ON DMM.domain_list_fk = D.origin 
            JOIN meta_course_list AS MCL ON DMM.meta_course_list_fk = MCL.origin 
            WHERE D.origin = ' . $domain_origin . ' 
            AND D.domain_key = ' . $this->db->escape($domain_key) . ' 
            AND DMM.status = ' . ACTIVE . ' 
            AND D.status = ' . ACTIVE . ' 
            AND MCL.status = ' . ACTIVE . ' 
            GROUP BY D.origin
        ';
        $result = $this->db->query($query)->row_array();
        return isset($result['domain_module']) ? explode(DB_SAFE_SEPARATOR, $result['domain_module']) : [];
    }

    public function get_active_payment_module_list(): array
    {
        $query = '
            SELECT GROUP_CONCAT(payment_category_code SEPARATOR "' . DB_SAFE_SEPARATOR . '") AS payment_category 
            FROM payment_option_list 
            WHERE status = ' . ACTIVE;
        $result = $this->db->query($query)->row_array();
        return isset($result['payment_category']) ? explode(DB_SAFE_SEPARATOR, $result['payment_category']) : [];
    }

    public function serialize_temp_booking_record(array $booking_params, string $module): array
    {
        $book_id = $module . date('d-His') . '-' . rand(1, 1_000_000);
        $temp_booking = [
            'domain_list_fk'   => get_domain_auth_id(),
            'book_id'          => $book_id,
            'booking_source'   => $booking_params['booking_source'] ?? '',
            'book_attributes'  => serialized_data($booking_params),
            'booking_ip'       => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'created_datetime' => date('Y-m-d H:i:s'),
        ];
        $insert_result = $this->custom_db->insert_record('temp_booking', $temp_booking);
        return ['book_id' => $book_id, 'temp_booking_origin' => $insert_result['insert_id'] ?? 0];
    }

    public function unserialize_temp_booking_record(string $book_id, int $temp_book_origin): array|false
    {
        $record = $this->custom_db->single_table_records(
            'temp_booking',
            'domain_list_fk, booking_source, book_id, book_attributes',
            ['book_id' => $book_id, 'id' => $temp_book_origin]
        );

        if (($record['status'] ?? false) == SUCCESS_STATUS) {
            $data = $record['data'][0];
            $data['book_attributes'] = unserialized_data($data['book_attributes']);
            $data['book_attributes']['token'] = unserialized_data($data['book_attributes']['token'] ?? '');
            return $data;
        }

        return false;
    }

    public function delete_temp_booking_record(string $book_id, int $temp_book_origin): void
    {
        $this->custom_db->delete_record('temp_booking', ['book_id' => $book_id, 'id' => $temp_book_origin]);
    }

    public function log_exception(string $module, string $op, string $notification): string
    {
        $data = [
            'exception_id'     => 'EID-' . time() . '-' . rand(1, 100),
            'module'           => $module,
            'op'               => $op,
            'notification'     => $notification,
            'user_agent'       => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'user_ip'          => $_SERVER['HTTP_HOST'] ?? '',
            'domain_origin'    => get_domain_auth_id(),
            'created_datetime' => date('Y-m-d H:i:s'),
        ];
        $this->custom_db->insert_record('exception_logger', $data);
        return $data['exception_id'];
    }

    public function flight_log_exception(string $module, string $op, string $notification): array
    {
        $exception_id = $this->log_exception($module, $op, $notification);
        return [
            'message'      => $notification,
            'op'           => $op,
            'exception_id' => $exception_id,
        ];
    }
}
