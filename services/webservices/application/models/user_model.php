<?php

/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Arjun J<arjunjgowda260389@gmail.com>
 * @version    V2
 */
class User_Model extends CI_Model
{

    var $verify_domain_balance;

    function __construct()
    {
        parent::__construct();
        $this->verify_domain_balance = $this->config->item('verify_domain_balance');
    }
    /**
     * Creates a new user and logs the registration event.
     *
     * @param string $email
     * @param string $password
     * @param string $first_name
     * @param string $phone
     * @param string $creation_source
     * @return array|null  Returns user data or null on failure
     */
    public function create_user(
        string $email,
        string $password,
        string $first_name = 'Customer',
        string $phone = '',
        string $creation_source = 'portal'
    ): array|null {
        $data = [
            'email'               => $email,
            'password'            => md5($password),
            'user_type'           => B2C_USER,
            'first_name'          => $first_name,
            'phone'               => $phone,
            'domain_list_fk'      => get_domain_auth_id(),
            'uuid'                => time() . rand(1, 1000),
            'creation_source'     => $creation_source,
            'status'              => ($creation_source === 'portal') ? INACTIVE : ACTIVE,
            'created_datetime'    => date('Y-m-d H:i:s'),
            'created_by_id'       => intval($GLOBALS['CI']->entity_user_id ?? 0),
            'language_preference' => 'english'
        ];

        $insert_result = $this->custom_db->insert_record('user', $data);
        $insert_id = $insert_result['insert_id'] ?? 0;

        if (!$insert_id) {
            return null;
        }

        $user_data = $this->custom_db->single_table_records('user', '*', [
            'user_id' => $insert_id
        ]);

        $this->application_logger->registration(
            $email,
            "$email Has Registered From B2C Portal",
            $insert_id,
            [
                'user_id' => $insert_id,
                'uuid'    => $data['uuid']
            ]
        );

        return $user_data['data'][0] ?? null;
    }
    /**
     * Get SMS configuration for a specific domain.
     *
     * @param int $sms
     * @return object|null
     */
    public function sms_configuration(int $sms): object
    {
        $tmp_data = $this->db->select('*')
            ->get_where('sms_configuration', ['domain_origin' => $sms]);

        return $tmp_data->row(); // returns null if not found
    }

    /**
     * Check SMS checkpoint status by condition name.
     *
     * @param string $name
     * @return string|null
     */
    public function sms_checkpoint(string $name): string
    {
        $result = $this->db->select('status')
            ->get_where('sms_checkpoint', ['condition' => $name])
            ->row();

        return $result->status ?? null;
    }

    /**
     * Activate or deactivate a user account.
     *
     * @param int $status
     * @param int $user_id
     * @return bool Whether the update was successful
     */
    public function activate_account_status(int $status, int $user_id): bool
    {
        $data = ['status' => $status];

        $this->db->where('user_id', $user_id);
        return $this->db->update('user', $data); // returns true/false
    }
    /**
     * Verify if the user credentials are valid or get user details
     *
     * @param array $condition SQL conditions
     * @param bool $count If true, returns count instead of rows
     * @param int $offset Offset for pagination
     * @param int $limit Limit of records to fetch
     * @param array $order_by SQL order by clause
     * @return array|object[]|object|null Result set or row object or null
     */
    public function get_user_details(
        array $condition = [],
        bool $count = false,
        int $offset = 0,
        int $limit = 10000000000,
        array $order_by = []
    ): array|object|null {
        $filter_condition = ' AND ';
        if (valid_array($condition)) {
            foreach ($condition as $v) {
                $filter_condition .= implode($v) . ' AND ';
            }
        }

        $filter_condition = rtrim($filter_condition, 'AND ');

        $filter_order_by = '';
        if (valid_array($order_by)) {
            $filter_order_by = ' ORDER BY ';
            foreach ($order_by as $v) {
                $filter_order_by .= implode($v) . ',';
            }
            $filter_order_by = rtrim($filter_order_by, ',');
        }

        if (!$count) {
            return $this->db->query(
                'SELECT U.*, UT.user_type AS user_profile_name, ACL.country_code AS country_code_value
             FROM user AS U, user_type AS UT, api_country_list AS ACL
             WHERE U.user_type = UT.origin 
             AND U.country_code = ACL.origin ' . $filter_condition .
                    ' LIMIT ' . $limit . ' OFFSET ' . $offset . $filter_order_by
            )->result_array(); // returns array
        } else {
            return $this->db->query(
                'SELECT COUNT(*) AS total 
             FROM user AS U, user_type AS UT, api_country_list AS ACL
             WHERE U.user_type = UT.origin 
             AND U.country_code = ACL.origin ' . $filter_condition .
                    ' LIMIT ' . $limit . ' OFFSET ' . $offset
            )->row(); // returns object|null
        }
    }
    /**
     * Get Domain user list in the system
     *
     * @param array $condition Query conditions
     * @param bool $count Whether to return count or full list
     * @param int $offset Offset for pagination
     * @param int $limit Maximum number of records
     * @param array $order_by SQL order by clauses
     * @return array|object|null Array of users or count object
     */
    public function get_domain_user_list(
        array $condition = [],
        bool $count = false,
        int $offset = 0,
        int $limit = 10000000000,
        array $order_by = []
    ): array|object|null {
        $filter_condition = ' AND ';

        if (valid_array($condition)) {
            foreach ($condition as $v) {
                $filter_condition .= implode($v) . ' AND ';
            }
        }

        if (!is_domain_user()) {
            // PROVAB ADMIN: Get all domain admins except self
            $filter_condition .= ' U.domain_list_fk > 0 AND U.user_type = ' . ADMIN . ' AND U.user_id != ' . intval($this->entity_user_id) . ' AND ';
        } else {
            // DOMAIN ADMIN: Get all users of same domain except self
            $filter_condition .= ' U.domain_list_fk = ' . get_domain_auth_id() . ' AND U.user_type != ' . ADMIN . ' AND U.user_id != ' . intval($this->entity_user_id) . ' AND ';
        }

        $filter_condition = rtrim($filter_condition, 'AND ');

        $filter_order_by = '';
        if (valid_array($order_by)) {
            $filter_order_by = ' ORDER BY ';
            foreach ($order_by as $v) {
                $filter_order_by .= implode($v) . ',';
            }
            $filter_order_by = rtrim($filter_order_by, ',');
        }

        if (!$count) {
            return $this->db->query(
                'SELECT U.*, UT.user_type, ACL.country_code AS country_code_value
             FROM user AS U, user_type AS UT, api_country_list AS ACL
             WHERE U.user_type = UT.origin 
             AND U.country_code = ACL.origin ' . $filter_condition .
                    ' LIMIT ' . $limit . ' OFFSET ' . $offset . $filter_order_by
            )->result_array(); // Returns array
        } else {
            return $this->db->query(
                'SELECT COUNT(*) AS total
             FROM user AS U, user_type AS UT, api_country_list AS ACL
             WHERE U.user_type = UT.origin 
             AND U.country_code = ACL.origin ' . $filter_condition .
                    ' LIMIT ' . $limit . ' OFFSET ' . $offset
            )->row(); // Returns object|null
        }
    }
    /**
     * Get logged-in users
     *
     * @param array $condition Query conditions
     * @param bool $count Return count or full result
     * @param int $offset Query offset
     * @param int $limit Query limit
     * @return array|object|null
     */
    public function get_logged_in_users(
        array $condition = [],
        bool $count = false,
        int $offset = 0,
        int $limit = 10000000000
    ): array|object|null {
        $filter_condition = ' AND ';

        if (valid_array($condition)) {
            foreach ($condition as $v) {
                $filter_condition .= implode($v) . ' AND ';
            }
        }

        if (!is_domain_user()) {
            // PROVAB ADMIN: Get all domain admins except self
            $filter_condition .= ' U.domain_list_fk > 0 AND U.user_type = ' . ADMIN . ' AND U.user_id != ' . intval($this->entity_user_id) . ' AND ';
        } else {
            // DOMAIN ADMIN: Get all users of the same domain except self
            $filter_condition .= ' U.domain_list_fk = ' . get_domain_auth_id() . ' AND U.user_type != ' . ADMIN . ' AND U.user_id != ' . intval($this->entity_user_id) . ' AND ';
        }

        $filter_condition = rtrim($filter_condition, 'AND ');
        $current_date = date('Y-m-d');

        if (!$count) {
            $query = '
            SELECT U.*, UT.user_type, LM.login_date_time AS login_time, LM.logout_date_time AS logout_time, LM.login_ip
            FROM user AS U
            JOIN user_type AS UT ON U.user_type = UT.origin
            JOIN api_country_list AS ACL ON U.country_code = ACL.origin
            JOIN login_manager AS LM ON U.user_type = LM.user_type AND U.user_id = LM.user_id
            WHERE LM.login_date_time >= "' . $current_date . ' 00:00:00"
              AND (LM.logout_date_time = "0000-00-00 00:00:00" OR LM.logout_date_time >= "' . $current_date . ' 00:00:00")
              ' . $filter_condition . '
            ORDER BY LM.logout_date_time ASC
            LIMIT ' . $limit . ' OFFSET ' . $offset;

            return $this->db->query($query)->result_array(); // array
        } else {
            $query = '
            SELECT COUNT(*) AS total
            FROM user AS U
            JOIN user_type AS UT ON U.user_type = UT.origin
            JOIN api_country_list AS ACL ON U.country_code = ACL.origin
            JOIN login_manager AS LM ON U.user_type = LM.user_type AND U.user_id = LM.user_id
            WHERE LM.login_date_time >= "' . $current_date . ' 00:00:00"
              AND (LM.logout_date_time = "0000-00-00 00:00:00" OR LM.logout_date_time >= "' . $current_date . ' 00:00:00")
              ' . $filter_condition . '
            LIMIT ' . $limit . ' OFFSET ' . $offset;

            return $this->db->query($query)->row(); // object|null
        }
    }
    /**
     * Get domain list present in the system
     *
     * @return array
     */
    public function get_domain_details(): array
    {
        $query = 'SELECT DL.*, CONCAT(U.first_name, " ", U.last_name) AS created_user_name 
              FROM domain_list DL 
              JOIN user U ON DL.created_by_id = U.user_id';

        return $this->db->query($query)->result_array();
    }

    /**
     * Update logout time for login session
     *
     * @param int $user_id Unique user ID
     * @param int $login_id Unique login ID
     * @return void
     */
    public function update_login_manager(int $user_id, int $login_id): void
    {
        $condition = [
            'user_id' => $user_id,
            'origin'  => $login_id
        ];

        // Update the logout time in login_manager table
        $this->custom_db->update_record('login_manager', [
            'logout_date_time' => date('Y-m-d H:i:s')
        ], $condition);

        $this->application_logger->logout(
            $this->entity_name,
            $this->entity_user_id,
            [
                'user_id' => $this->entity_user_id,
                'uuid'    => $this->entity_uuid
            ]
        );
    }
    /**
     * Create Login Manager record for a user login session.
     *
     * @param int $user_id
     * @param int $user_type
     * @param int $user_origin
     * @param string $username
     * @return int The insert ID of the login record.
     */
    public function create_login_auth_record(int $user_id, int $user_type, int $user_origin = 0, string $username = 'customer'): int
    {
        $login_details['browser'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $remote_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $this->update_auth_record_expiry($user_id, $user_type, $remote_ip, $user_origin, $username);

        // Try to fetch IP info (consider adding timeout/error handling in production)
        $login_details['info'] = @file_get_contents("http://ipinfo.io/" . $remote_ip . "/json");

        $data = [
            'user_id'         => $user_id,
            'user_type'       => $user_type,
            'login_date_time' => date('Y-m-d H:i:s'),
            'login_ip'        => $remote_ip,
            'attributes'      => json_encode($login_details)
        ];

        $login_id = $this->custom_db->insert_record('login_manager', $data);

        $this->application_logger->login($username, $user_origin, [
            'user_id' => $user_origin,
            'uuid'    => $user_id
        ]);

        return $login_id['insert_id'];
    }
    /**
     * Update the logout time for an existing login session.
     *
     * @param int $user_id
     * @param int $user_type
     * @param string $remote_ip
     * @param int $user_origin
     * @param string $username
     * @return void
     */
    public function update_auth_record_expiry(
        int $user_id,
        int $user_type,
        string $remote_ip,
        int $user_origin,
        string $username
    ): void {
        $cond = [
            'user_id'   => $user_id,
            'user_type' => $user_type,
            'login_ip'  => $remote_ip
        ];

        $auth_exp = $this->custom_db->update_record('login_manager', [
            'logout_date_time' => date('Y-m-d H:i:s')
        ], $cond);

        if ($auth_exp === true) {
            // Update application logger
            $this->application_logger->logout($username, $user_origin, [
                'user_id' => $user_origin,
                'uuid'    => $user_id
            ]);
        }
    }
    /**
     * Subscribe an email to a domain.
     *
     * @param string $email
     * @param int $domain_key
     * @return string
     */
    public function email_subscribtion(string $email, int $domain_key): string
    {
        $query = $this->db->get_where('email_subscribtion', [
            'email_id' => $email
        ]);

        if ($query->num_rows() > 0) {
            return "already";
        } else {
            $insert_id = $this->custom_db->insert_record('email_subscribtion', [
                'email_id'       => $email,
                'domain_list_fk' => $domain_key
            ]);
            return $insert_id['insert_id'];
        }
    }
    /**
     * Validating Domain Login
     *
     * @param int    $domain_key
     * @param string $username
     * @param string $password
     * @param string $system
     * @param string $serverIp
     * @return array
     */
    public function domain_login(string $domain_key, string $username, string $password, string $system = 'test', string $serverIp): array
    {
        $resp = [
            'status' => false,
            'data' => []
        ];
        $user_filter = '';

        if ($system === "test") {
            $user_filter .= ' and test_username = ' . $this->db->escape($username);
            $user_filter .= ' and test_password = ' . $this->db->escape($password);
            $user_filter .= ' and (domain_ip = ' . $this->db->escape($serverIp) . ' || IP.ip_address = ' . $this->db->escape($serverIp) . ')';
        } else if ($system === "live") {
            $user_filter .= ' and live_username = ' . $this->db->escape($username);
            $user_filter .= ' and live_password = ' . $this->db->escape($password);
            $user_filter .= ' and (domain_ip = ' . $this->db->escape($serverIp) . ' || IP.ip_address = ' . $this->db->escape($serverIp) . ')';
        }

        $query = 'select DL.* from domain_list DL 
              LEFT JOIN domain_ip_list AS IP ON DL.origin = IP.domain_list_fk 
              WHERE DL.domain_key = ' . $this->db->escape($domain_key) . ' 
              and DL.status = ' . ACTIVE . ' ' . $user_filter;

        $domain_details = $this->db->query($query)->row_array();

        if (!empty($domain_details)) {
            $resp['status'] = true;
            $resp['data'] = $domain_details;
        }

        return $resp;
    }
    /**
     * Add test data to the database
     *
     * @param mixed $data
     * @param bool  $enable_json
     * @return void
     */
    public function add_test_data(mixed $data, bool $enable_json = false): void
    {
        if ($enable_json === true) {
            $data = json_encode($data);
        }

        $this->custom_db->insert_record('test', array(
            'test' => $data
        ));
    }
    /**
     * Verify The Current Balance of Domain User
     *
     * @param float  $amount
     * @param string $system
     * @param string $currency
     * @param bool   $consider_credit_balance
     * @return string
     */
    public function get_balance(float $amount, string $system, string $currency = 'INR', bool $consider_credit_balance = true): string
    {
        $amount = floatval($amount);
        $status = FAILURE_STATUS;

        if ($this->verify_domain_balance === true) {
            if ($amount > 0) {
                if ($system === 'test') {
                    $query = 'SELECT DL.test_balance, DL.credit_limit, DL.due_amount, CC.country as currency, CC.value as conversion_value
                          FROM domain_list as DL
                          JOIN currency_converter AS CC ON CC.id = DL.currency_converter_fk
                          WHERE DL.status = ' . ACTIVE . ' 
                          AND DL.origin = ' . $this->db->escape(get_domain_auth_id()) . ' 
                          AND DL.domain_key = ' . $this->db->escape(get_domain_key());

                    $balance_record = $this->db->query($query)->row_array();

                    if ($currency === $balance_record['currency']) {
                        $balance = $balance_record['test_balance'];
                        if (!$consider_credit_balance) {
                            // Due is always stored with -ve symbol
                            $balance += floatval($balance_record['credit_limit']) + floatval($balance_record['due_amount']);
                        }

                        if ($balance >= $amount) {
                            $status = SUCCESS_STATUS;
                        } else {
                            // Notify User about current balance problem
                            // FIXME: send email/notification to domain admin and current domain admin
                            $this->application_logger->balance_status('Your Balance Is Very Low To Make Booking Of ' . $amount . ' ' . $currency);
                        }
                    } else {
                        echo 'Under Construction';
                        exit;
                    }
                } elseif ($system === 'live') {
                    $query = 'SELECT DL.balance, DL.credit_limit, DL.due_amount, CC.country as currency, CC.value as conversion_value
                          FROM domain_list as DL
                          JOIN currency_converter AS CC ON CC.id = DL.currency_converter_fk
                          WHERE DL.status = ' . ACTIVE . ' 
                          AND DL.origin = ' . $this->db->escape(get_domain_auth_id()) . ' 
                          AND DL.domain_key = ' . $this->db->escape(get_domain_key());

                    $balance_record = $this->db->query($query)->row_array();

                    if ($currency === $balance_record['currency']) {
                        $balance = $balance_record['balance'];
                        if (!$consider_credit_balance) {
                            // Due is always stored with -ve symbol
                            $balance += floatval($balance_record['credit_limit']) + floatval($balance_record['due_amount']);
                        }

                        if ($balance >= $amount) {
                            $status = SUCCESS_STATUS;
                        } else {
                            // Notify User about current balance problem
                            // FIXME: send email/notification to domain admin and current domain admin
                            $this->application_logger->balance_status('Your Balance Is Very Low To Make Booking Of ' . $amount . ' ' . $currency);
                        }
                    } else {
                        echo 'Under Construction';
                        exit;
                    }
                }
            }
        } else {
            $status = SUCCESS_STATUS;
        }

        return $status;
    }
    /**
     * Deduct The Amount of Domain User
     *
     * @param float  $amount
     * @param string $system
     * @return float
     */
    public function update_balance(float $amount, string $system): float
    {
        $current_balance = 0.0;
        $cond = array('origin' => intval(get_domain_auth_id()));

        if ($system === 'test') {
            $details = $this->custom_db->single_table_records('domain_list', 'test_balance,credit_limit,due_amount', $cond);
            if ($details['status'] === true) {
                // Code for adding due if booking happened with credit limit
                $BalanceToAdded = 0;
                if ($details['data'][0]['due_amount'] < 0) {
                    $TotalDueAmount = $details['data'][0]['due_amount'] + $amount;
                    if ($TotalDueAmount > 0) {
                        $BalanceToAdded = $TotalDueAmount;
                        $TotalDueAmount = 0;
                    }
                }

                $details['data'][0]['test_balance'] = $current_balance = ($details['data'][0]['test_balance'] + $amount);
                if ($details['data'][0]['test_balance'] < 0) {
                    $details['data'][0]['due_amount'] += $details['data'][0]['test_balance'];
                    $details['data'][0]['test_balance'] = 0;
                }

                $this->custom_db->update_record('domain_list', $details['data'][0], $cond);
            }
        } elseif ($system === 'live') {
            $details = $this->custom_db->single_table_records('domain_list', 'balance,credit_limit,due_amount', $cond);
            if ($details['status'] === true) {
                // Code for adding due if booking happened with credit limit
                $BalanceToAdded = 0;
                if ($details['data'][0]['due_amount'] < 0) {
                    $TotalDueAmount = $details['data'][0]['due_amount'] + $amount;
                    if ($TotalDueAmount > 0) {
                        $BalanceToAdded = $TotalDueAmount;
                        $TotalDueAmount = 0;
                    }
                }

                $details['data'][0]['balance'] = $current_balance = ($details['data'][0]['balance'] + $amount);
                if ($details['data'][0]['balance'] < 0) {
                    $details['data'][0]['due_amount'] += $details['data'][0]['balance'];
                    $details['data'][0]['balance'] = 0;
                }

                $this->custom_db->update_record('domain_list', $details['data'][0], $cond);
            }
        } else {
            echo "Under Construction";
        }

        return $current_balance;
    }
    /**
     * Validate the payment key for domain user.
     *
     * @param string $domain_key
     * @param string $username
     * @param string $password
     * @param string $system
     * @param string $travelomatix_payment_key
     * @return array
     */
    public function valid_payment_key(string $domain_key, string $username, string $password, string $system = 'test', string $travelomatix_payment_key): array
    {
        $resp = [
            'status' => false,
            'data' => []
        ];

        $user_filter = '';
        if ($system === "test") {
            $user_filter .= ' and test_username = ' . $this->db->escape($username);
            $user_filter .= ' and test_password = ' . $this->db->escape($password);
        } elseif ($system === "live") {
            $user_filter .= ' and live_username = ' . $this->db->escape($username);
            $user_filter .= ' and live_password = ' . $this->db->escape($password);
        }

        $query = 'SELECT DL.* 
              FROM domain_list DL 
              LEFT JOIN domain_ip_list AS IP ON DL.origin = IP.domain_list_fk 
              WHERE DL.domain_key = ' . $this->db->escape($domain_key) . ' 
              AND DL.travelomatix_payment_key = ' . $this->db->escape($travelomatix_payment_key) . ' 
              AND DL.status = ' . ACTIVE . ' ' . $user_filter;

        $domain_details = $this->db->query($query)->row_array();

        if (!empty($domain_details)) {
            $domain_session_data = [];

            // SETTING DOMAIN KEY
            $domain_session_data[DOMAIN_AUTH_ID] = intval($domain_details['origin']);

            // SETTING DOMAIN CONFIGURATION
            $domain_key = trim($domain_details['domain_key']);
            $domain_session_data[DOMAIN_KEY] = base64_encode($domain_key);

            $this->session->set_userdata($domain_session_data);

            $resp['status'] = true;
            $resp['data'] = $domain_details;
        }

        return $resp;
    }
}
