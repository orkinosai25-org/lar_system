<?php
declare(strict_types=1);
/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu
 */

defined('BASEPATH') || exit('No direct script access allowed');

class User_Model extends CI_Model
{
    public function get_user_details(array $condition = [], bool $count = false, int $offset = 0, int $limit = PHP_INT_MAX, array $order_by = []): array|object
    {
        $filter_condition = ' AND ';
        foreach ($condition as $v) {
            $filter_condition .= implode($v) . ' AND ';
        }

        $filter_condition = rtrim($filter_condition, ' AND ');

        $filter_order_by = '';
        if (!empty($order_by)) {
            $filter_order_by = 'ORDER BY ' . rtrim(implode(',', array_map(fn($v) => implode($v), $order_by)), ',');
        }

        $base_query = '
            FROM user AS U
            JOIN user_type AS UT ON U.user_type = UT.origin
            JOIN api_country_list AS ACL ON ACL.origin = U.country_code
        ';

        if ($count) {
            return $this->db->query("SELECT COUNT(*) as total {$base_query} WHERE 1 {$filter_condition} LIMIT {$limit} OFFSET {$offset}")->row();
        }

        return $this->db->query("SELECT U.*, UT.user_type as user_profile_name, ACL.country_code as country_code_value {$base_query} WHERE 1 {$filter_condition} LIMIT {$limit} OFFSET {$offset} {$filter_order_by}")->result_array();
    }

    public function get_domain_user_list(array $condition = [], bool $count = false, int $offset = 0, int $limit = PHP_INT_MAX, array $order_by = []): array|object
    {
        $filter_condition = ' AND ';

        foreach ($condition as $v) {
            $filter_condition .= implode($v) . ' AND ';
        }

        $entity_id = (int)$this->entity_user_id;
        if (!is_domain_user()) {
            $filter_condition .= " U.domain_list_fk > 0 AND U.user_type = " . ADMIN . " AND U.user_id != {$entity_id} AND ";
        } else {
            $domain_id = get_domain_auth_id();
            $filter_condition .= " U.domain_list_fk = {$domain_id} AND U.user_type != " . ADMIN . " AND U.user_id != {$entity_id} AND ";
        }

        $filter_condition = rtrim($filter_condition, ' AND ');

        $filter_order_by = '';
        if (!empty($order_by)) {
            $filter_order_by = 'ORDER BY ' . rtrim(implode(',', array_map(fn($v) => implode($v), $order_by)), ',');
        }

        $base_query = '
            FROM user AS U
            JOIN user_type AS UT ON U.user_type = UT.origin
            JOIN api_country_list AS ACL ON U.country_code = ACL.origin
        ';

        if ($count) {
            return $this->db->query("SELECT COUNT(*) AS total {$base_query} WHERE 1 {$filter_condition} LIMIT {$limit} OFFSET {$offset}")->row();
        }

        return $this->db->query("SELECT U.*, UT.user_type, ACL.country_code as country_code_value {$base_query} WHERE 1 {$filter_condition} LIMIT {$limit} OFFSET {$offset} {$filter_order_by}")->result_array();
    }

   public function get_logged_in_users(array $condition = [], bool $count = false, int $offset = 0, int $limit = PHP_INT_MAX): array|object
{
    $filter_condition = ' AND ';
    foreach ($condition as $v) {
        $filter_condition .= implode($v) . ' AND ';
    }

    $entity_id = (int)$this->entity_user_id;

    if (!is_domain_user()) {
        $filter_condition .= " U.domain_list_fk > 0 AND U.user_type = " . ADMIN . " AND U.user_id != {$entity_id} AND ";
    }

    // This runs regardless of the previous if-condition (the old else part)
    if (is_domain_user()) {
        $domain_id = get_domain_auth_id();
        $filter_condition .= " U.domain_list_fk = {$domain_id} AND U.user_type != " . ADMIN . " AND U.user_id != {$entity_id} AND ";
    }

    $filter_condition = rtrim($filter_condition, ' AND ');
    $current_date = date('Y-m-d');

    $base_query = '
        FROM user AS U
        JOIN user_type AS UT ON U.user_type = UT.origin
        JOIN api_country_list AS ACL ON U.country_code = ACL.origin
        JOIN login_manager AS LM ON U.user_type = LM.user_type AND U.user_id = LM.user_id
        WHERE LM.login_date_time >= "' . $current_date . ' 00:00:00"
        AND (LM.logout_date_time = "0000-00-00 00:00:00" OR LM.logout_date_time >= "' . $current_date . ' 00:00:00")
    ';

    if ($count) {
        return $this->db->query("SELECT COUNT(*) AS total {$base_query} {$filter_condition} LIMIT {$limit} OFFSET {$offset}")->row();
    }

    return $this->db->query("
        SELECT U.*, UT.user_type, LM.login_date_time AS login_time, LM.logout_date_time AS logout_time, LM.login_ip 
        {$base_query} {$filter_condition}
        ORDER BY LM.logout_date_time ASC
        LIMIT {$limit} OFFSET {$offset}
    ")->result_array();
}


    public function get_domain_details(): array
    {
        $query = 'SELECT DL.*, CONCAT(U.first_name, " ", U.last_name) AS created_user_name 
                  FROM domain_list DL 
                  JOIN user U ON DL.created_by_id = U.user_id';
        return $this->db->query($query)->result_array();
    }

    public function update_login_manager(string $user_id, int $login_id): void
    {
        $condition = [
            'user_id' => $user_id,
            'origin' => $login_id
        ];

        $this->custom_db->update_record('login_manager', [
            'logout_date_time' => date('Y-m-d H:i:s')
        ], $condition);
    }

    public function create_login_auth_record(string $user_id, int $user_type): int
    {
        $browser = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $remote_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $this->update_auth_record_expiry($user_id, $user_type, $remote_ip);

        $info = file_get_contents('https://tools.keycdn.com/geo.json');

        $data = [
            'user_id' => $user_id,
            'user_type' => $user_type,
            'login_date_time' => date('Y-m-d H:i:s'),
            'login_ip' => $remote_ip,
            'attributes' => json_encode(['browser' => $browser, 'info' => $info]),
        ];

        $result = $this->custom_db->insert_record('login_manager', $data);
        return (int)$result['insert_id'];
    }

    public function update_auth_record_expiry(string $user_id, int $user_type, string $remote_ip): void
    {
        $cond = [
            'user_id' => $user_id,
            'user_type' => $user_type,
            'login_ip' => $remote_ip
        ];

        $this->custom_db->update_record('login_manager', [
            'logout_date_time' => date('Y-m-d H:i:s')
        ], $cond);
    }

    public function email_subscribtion(string $email, string $domain_key): string|int
    {
        $query = $this->db->get_where('email_subscribtion', ['email_id' => $email]);

        if ($query->num_rows() > 0) {
            return 'already';
        }

        $insert = $this->custom_db->insert_record('email_subscribtion', [
            'email_id' => $email,
            'domain_list_fk' => $domain_key
        ]);

        return (int)$insert['insert_id'];
    }
    /**
     * Get SMS configuration by domain origin.
     */
    public function sms_configuration(string $domainOrigin): ?object
    {
        $query = $this->db->select('*')->get_where('sms_configuration', ['domain_origin' => $domainOrigin]);
        return $query->row() ?: null;
    }

    /**
     * Get the status of SMS checkpoint condition.
     */
    public function sms_checkpoint(string $conditionName): bool
    {
        $result = $this->db->select('status')->get_where('sms_checkpoint', ['condition' => $conditionName])->row();
        return !empty($result?->status);
    }

    /**
     * Return current user details who has logged in.
     *
     * @return array|false
     */
    public function get_current_user_details(): array|false
    {
        $userId = filter_var($this->entity_user_id, FILTER_VALIDATE_INT);
        if ($userId === false || $userId <= 0) {
            return false;
        }

        $cond = [['U.user_id', '=', $userId]];
        $user = $this->get_user_details($cond);

        if (empty($user[0])) {
            return false;
        }

        $user[0]['uuid'] = provab_decrypt($user[0]['uuid']);
        $user[0]['email'] = provab_decrypt($user[0]['email']);
        $user[0]['user_name'] = provab_decrypt($user[0]['user_name']);
        $user[0]['password'] = provab_decrypt($user[0]['password']);

        return $user;
    }

    /**
     * Balu A
     */
    function get_admin_user_id()
    {$cond=[];
        $admin_user_id = array();
        $cond[] = array('U.user_type', '=', ADMIN);
        $cond[] = array('U.status', '=', ACTIVE);
        $cond[] = array('U.domain_list_fk', '=', get_domain_auth_id());
        $user_details = $this->get_user_details($cond);
        foreach($user_details as $k => $v){
            $admin_user_id[$k] = $v['user_id'];
        }
        return $admin_user_id;
    }

    /**
     * get agent information
     * @param unknown $user_id
     */
    function get_agent_info($user_id){
        $query = 'select U.*,BU.logo from user AS U
                          join  b2b_user_details BU on U.user_id = BU.user_oid
                          join  currency_converter CUC on CUC.id = BU.currency_converter_fk
                          WHERE  U.user_type='.B2B_USER.' AND U.user_id='.$user_id;
                          // echo $query;exit;
        return $this->db->query($query)->result_array();
            
    }

    /**
     * Get user traveller details filtered by search characters.
     *
     * @param string $searchChars
     * @return \CI_DB_result
     */
    public function user_traveller_details(string $searchChars): \CI_DB_result
    {
        $userId = filter_var($this->entity_user_id, FILTER_VALIDATE_INT) ?: 0;
        $likeSearch = "%{$searchChars}%";

        $sql = '
            SELECT *
            FROM user_traveller_details
            WHERE created_by_id = ? AND (first_name LIKE ? OR last_name LIKE ?)
            ORDER BY first_name ASC
            LIMIT 20';

        return $this->db->query($sql, [$userId, $likeSearch, $likeSearch]);
    }

    /**
     * Get Facebook network configuration.
     */
    public function fb_network_configuration( string $social): string|false
    {
        $socialLinks = $this->db_cache_api->get_active_social_network_list();
        return $socialLinks[$social]['config'] ?? false;
    }

    /**
     * Get active B2B user by username and password.
     *
     * @param string $username
     * @param string $password
     * @return array<int, array<string, mixed>>
     */
    public function active_b2b_user(string $username, string $password): array
    {$condition=[];
        $condition[] = array('U.domain_list_fk', '=', get_domain_auth_id());
        $condition[] = array('U.user_type', '=', B2B_USER);
        $condition[] = array('U.user_name', '=', $this->db->escape(provab_encrypt(trim($username))));
        //$condition[] = array('U.phone', '=', $this->db->escape($username));
        $condition[] = array('U.password', '=', $this->db->escape(provab_encrypt(md5(trim($password)))));
        
        return $this->get_user_details($condition);
    }

    
}
