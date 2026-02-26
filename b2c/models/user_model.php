<?php
/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */
Class User_Model extends CI_Model
{

	public function create_user(
		string $email,
		string $password,
		string $first_name = 'Customer',
		string $country_code = '',
		string $phone = '',
		string $creation_source = 'portal',
		string|int $user_type = ''
	): array {
		$data = [];
		$action_query_string = [];
	
		$data['email'] = provab_encrypt(trim($email));
		$data['user_name'] = provab_encrypt(trim($email));
	
		if (!empty($password) && strlen($password) > 3) {
			$data['password'] = provab_encrypt(md5(trim($password)));
		}
	
		$is_default_user_type = (empty($user_type) && $user_type !== 0) || $user_type == '';
		$data['user_type'] = $is_default_user_type ? B2C_USER : $user_type;
		$action_query_string['user_type'] = $data['user_type'];
	
		$data['first_name'] = $first_name;
		$data['phone'] = $phone;
		$data['domain_list_fk'] = get_domain_auth_id();
		$data['uuid'] = provab_encrypt((string)(time() . rand(1, 1000)));
		$data['creation_source'] = $creation_source;
		$data['country_code'] = $country_code;
		$data['status'] = ($creation_source == 'portal') ? INACTIVE : ACTIVE;
		$data['created_datetime'] = date('Y-m-d H:i:s');
		if(isset($GLOBALS['CI']->entity_user_id)){
			$data['created_by_id'] = intval($GLOBALS['CI']->entity_user_id);
		}
		else{
			$data['created_by_id'] = 0;
		}
		
		$data['language_preference'] = 'english';
	
		$insert_result = $this->custom_db->insert_record('user', $data);
		$insert_id = $insert_result['insert_id'] ?? 0;
	
		$user_data = $this->custom_db->single_table_records('user', '*', ['user_id' => $insert_id]);
	
		$remarks = $email . ' Has Registered From B2C Portal';
		$notification_users = $this->get_admin_user_id();
	
		$action_query_string['user_id'] = $insert_id;
		$action_query_string['uuid'] = provab_decrypt($data['uuid']);
		$action_query_string['user_type'] = B2C_USER;
	
		$this->application_logger->registration(
			$email,
			$remarks,
			$insert_id,
			$action_query_string,
			[],
			$notification_users
		);
	
		return $user_data;
	}
	


	//sms configuration
	public function sms_configuration(int $sms): ?object
	{
		$tmp_data = $this->db->select('*')->get_where('sms_configuration', ['domain_origin' => $sms]);
		return $tmp_data->row(); // returns null if not found
	}

	public function fb_network_configuration(int|string $id, string $social): string|false
	{
		$social_links = $this->db_cache_api->get_active_social_network_list();
		return isset($social_links[$social]['config']) ? $social_links[$social]['config'] : false;
	}

	public function google_network_configuration(int|string $id, string $social): string|false
	{
		$social_links = $this->db_cache_api->get_active_social_network_list();
		return isset($social_links[$social]['config']) ? $social_links[$social]['config'] : false;
	}

	public function sms_checkpoint(string $name): string|false
	{
		$result = $this->db->select('status')->get_where('sms_checkpoint', ['condition' => $name])->row();
		return $result->status ?? false;
	}

	public function activate_account_status(string $status, int $user_id): void
	{
		$data = ['status' => $status];
		$this->db->where('user_id', $user_id);
		$this->db->update('user', $data);
	}

	public function get_current_user_details(): array|false
	{
		if (intval($this->entity_user_id) > 0) {
			$cond = [['U.user_id', '=', intval($this->entity_user_id)]];
			$user = $this->get_user_details($cond);
			if (!empty($user)) {
				$user[0]['uuid'] = provab_decrypt($user[0]['uuid']);
				$user[0]['email'] = provab_decrypt($user[0]['email']);
				$user[0]['user_name'] = provab_decrypt($user[0]['user_name']);
				$user[0]['password'] = provab_decrypt($user[0]['password']);
			}
			return $user;
		}
		return false;
	}

	public function active_b2c_user(string $username, string $password): array
	{
		$condition = [
			['U.domain_list_fk', '=', get_domain_auth_id()],
			['U.user_type', '=', B2C_USER],
			['U.user_name', '=', $this->db->escape(provab_encrypt($username))],
			['U.password', '=', $this->db->escape(provab_encrypt(md5(trim($password))))]
		];

		return $this->get_user_details($condition);
	}



	/**
	 *verify is the user credentials are valid
	 *
	 *@param string $email    email of the user
	 *@param string @password password of the user
	 *
	 *return boolean status of the user credentials
	 */
	public function get_user_details(array $condition = [], bool $count = false, int $offset = 0, int $limit = 10000000000, array $order_by = []): array|object
	{
		$filter_condition = ' and ';
		foreach ($condition as $v) {
			$filter_condition .= implode($v) . ' and ';
		}

		$filter_order_by = '';
		if (!empty($order_by)) {
			$filter_order_by = 'ORDER BY';
			foreach ($order_by as $v) {
				$filter_order_by .= implode($v) . ',';
			}
		}

		$filter_condition = rtrim($filter_condition, 'and ');
		$filter_order_by = rtrim($filter_order_by, ',');

		if (!$count) {
			return $this->db->query(
				'SELECT U.*, UT.user_type as user_profile_name, ACL.country_code as country_code_value
            FROM user AS U, user_type AS UT, api_country_list AS ACL
            WHERE U.user_type=UT.origin AND U.country_code=ACL.country_code ' . $filter_condition .
					' LIMIT ' . $limit . ' OFFSET ' . $offset . ' ' . $filter_order_by
			)->result_array();
		}

		return $this->db->query(
			'SELECT count(*) as total FROM user AS U, user_type AS UT, api_country_list AS ACL
        WHERE U.user_type=UT.origin AND U.country_code=ACL.country_code ' . $filter_condition .
				' LIMIT ' . $limit . ' OFFSET ' . $offset
		)->row();
	}

	public function get_domain_user_list(array $condition = [], bool $count = false, int $offset = 0, int $limit = 10000000000, array $order_by = []): array|object
	{
		$filter_condition = ' and ';
		foreach ($condition as $v) {
			$filter_condition .= implode($v) . ' and ';
		}

		if (!is_domain_user()) {
			$filter_condition .= ' U.domain_list_fk > 0 and U.user_type = ' . ADMIN . ' and U.user_id != ' . intval($this->entity_user_id) . ' and ';
		}

		$filter_condition .= ' U.domain_list_fk =' . get_domain_auth_id() . ' and U.user_type != ' . ADMIN . ' and U.user_id != ' . intval($this->entity_user_id) . ' and ';

		$filter_order_by = '';
		if (!empty($order_by)) {
			$filter_order_by = 'ORDER BY ';
			foreach ($order_by as $v) {
				$filter_order_by .= implode($v) . ',';
			}
			$filter_order_by = rtrim($filter_order_by, ',');
		}

		$filter_condition = rtrim($filter_condition, 'and ');

		if (!$count) {
			return $this->db->query(
				'SELECT U.*, UT.user_type, ACL.country_code as country_code_value 
            FROM user AS U, user_type AS UT, api_country_list AS ACL
            WHERE U.user_type=UT.origin AND U.country_code=ACL.origin ' . $filter_condition .
					' ' . $filter_order_by .
					' LIMIT ' . $limit . ' OFFSET ' . $offset
			)->result_array();
		}

		return $this->db->query(
			'SELECT count(*) as total 
        FROM user AS U, user_type AS UT, api_country_list AS ACL
        WHERE U.user_type=UT.origin AND U.country_code=ACL.origin ' . $filter_condition .
				' LIMIT ' . $limit . ' OFFSET ' . $offset
		)->row();
	}


	public function get_logged_in_users(array $condition = [], bool $count = false, int $offset = 0, int $limit = 10000000000): array|object
	{
		$filter_condition = ' and ';
		foreach ($condition as $v) {
			$filter_condition .= implode($v) . ' and ';
		}

		if (!is_domain_user()) {
			$filter_condition .= ' U.domain_list_fk > 0 and U.user_type = ' . ADMIN . ' and U.user_id != ' . intval($this->entity_user_id) . ' and ';
		}

		$filter_condition .= ' U.domain_list_fk =' . get_domain_auth_id() . ' and U.user_type != ' . ADMIN . ' and U.user_id != ' . intval($this->entity_user_id) . ' and ';

		$filter_condition = rtrim($filter_condition, 'and ');
		$current_date = date('Y-m-d');

		if (!$count) {
			return $this->db->query(
				'SELECT U.*, UT.user_type, LM.login_date_time as login_time, LM.logout_date_time as logout_time, LM.login_ip
            FROM user AS U
            JOIN user_type AS UT ON U.user_type=UT.origin
            JOIN api_country_list AS ACL ON U.country_code=ACL.origin
            JOIN login_manager AS LM ON U.user_type=LM.user_type AND U.user_id=LM.user_id
            WHERE LM.login_date_time >= "' . $current_date . ' 00:00:00"
            AND (LM.logout_date_time = "0000-00-00 00:00:00" OR LM.logout_date_time >= "' . $current_date . ' 00:00:00")
            ' . $filter_condition . '
            ORDER BY LM.logout_date_time ASC
            LIMIT ' . $limit . ' OFFSET ' . $offset
			)->result_array();
		}

		return $this->db->query(
			'SELECT count(*) as total 
        FROM user AS U
        JOIN user_type AS UT ON U.user_type=UT.origin
        JOIN api_country_list AS ACL ON U.country_code=ACL.origin
        JOIN login_manager AS LM ON U.user_type=LM.user_type AND U.user_id=LM.user_id
        WHERE LM.login_date_time >= "' . $current_date . ' 00:00:00"
        AND (LM.logout_date_time = "0000-00-00 00:00:00" OR LM.logout_date_time >= "' . $current_date . ' 00:00:00")
        ' . $filter_condition . '
        LIMIT ' . $limit . ' OFFSET ' . $offset
		)->row();
	}


	public function get_domain_details(): array
	{
		$query = 'SELECT DL.*, CONCAT(U.first_name, " ", U.last_name) as created_user_name 
              FROM domain_list DL 
              JOIN user U ON DL.created_by_id=U.user_id';
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

		$this->application_logger->logout(
			$this->entity_name,
			$this->entity_user_id,
			['user_id' => $this->entity_user_id, 'uuid' => $this->entity_uuid]
		);
	}

	public function create_login_auth_record(string $user_id, string $user_type, int $user_origin = 0, string $username = 'customer'): int|null
	{
		$ch = curl_init('https://tools.keycdn.com/geo.json');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'MyCustomUserAgent/1.0');
		$response = curl_exec($ch);
		curl_close($ch);
		$login_details = [];

		$data = json_decode($response, true);
		$login_details['browser'] = $_SERVER['HTTP_USER_AGENT'];
		$remote_ip = $_SERVER['REMOTE_ADDR'];
		$this->update_auth_record_expiry($user_id, $user_type, $remote_ip, $user_origin, $username);

		if ($data['status'] !== 'error') {
			$login_details['info'] = file_get_contents('https://tools.keycdn.com/geo.json');
			$data['user_id'] = $user_id;
			$data['user_type'] = $user_type;
			$data['login_date_time'] = date('Y-m-d H:i:s');
			$data['login_ip'] = $remote_ip;
			$data['attributes'] = json_encode($login_details);

			$login_id = $this->custom_db->insert_record('login_manager', $data);
			$this->application_logger->login($username, $user_origin, ['user_id' => $user_origin, 'uuid' => $user_id]);
			return $login_id['insert_id'];
		}

		return null;
	}

	/**
	 * Update logout
	 * @param $user_id
	 * @param $user_type
	 * @param $remote_ip
	 * @param $browser
	 */
	public function update_auth_record_expiry(string $user_id, string $user_type, string $remote_ip, int $user_origin, string $username): void
	{
		$cond = [
			'user_id' => $user_id,
			'user_type' => $user_type,
			'login_ip' => $remote_ip
		];

		$auth_exp = $this->custom_db->update_record('login_manager', [
			'logout_date_time' => date('Y-m-d H:i:s')
		], $cond);

		if ($auth_exp == true) {
			$this->application_logger->logout($username, $user_origin, [
				'user_id' => $user_origin,
				'uuid' => $user_id
			]);
		}
	}

	public function email_subscribtion(string $email, int $domain_key): string|int
	{
		$query = $this->db->get_where('email_subscribtion', ['email_id' => $email]);

		if ($query->num_rows() > 0) {
			return "already";
		}

		$insert_id = $this->custom_db->insert_record('email_subscribtion', [
			'email_id' => $email,
			'domain_list_fk' => $domain_key
		]);

		return $insert_id['insert_id'];
	}

	public function user_traveller_details(string $search_chars): CI_DB_result
	{
		$search_chars = $this->db->escape('%' . $search_chars . '%');

		$query = 'SELECT * FROM user_traveller_details 
              WHERE created_by_id=' . intval($this->entity_user_id) . ' 
              AND (first_name LIKE ' . $search_chars . ' OR last_name LIKE ' . $search_chars . ') 
              ORDER BY first_name ASC 
              LIMIT 0, 20';

		return $this->db->query($query);
	}

	public function get_user_traveller_details(): array
	{
		$query = 'SELECT * FROM user_traveller_details 
              WHERE created_by_id=' . intval($this->entity_user_id) . ' 
              ORDER BY first_name ASC';

		return $this->db->query($query)->result_array();
	}

	public function offline_payment_insert(array $params): array
	{
		$created_date = date('Y-m-d H:i:s');
		$coded = str_shuffle($params['data'][0]['value'] . $params['data'][3]['value']);

		$record = [];
		foreach ($params['data'] as $item) {
			$record[$item['name']] = $item['value'];
		}

		$record['created_date'] = $created_date;
		$record['refernce_code'] = $coded;

		$insert_id = $this->custom_db->insert_record('offline_payment', $record);

		return [
			'db' => $insert_id,
			'refernce_code' => $coded
		];
	}

	public function offline_approval(string $cd): array
	{
		$query = "SELECT * FROM `offline_payment` WHERE `refernce_code` = '$cd'";
		return $this->db->query($query)->result_array();
	}

	public function get_admin_user_id(): array
	{
		$cond = [];
		$admin_user_id = [];
		$cond[] = ['U.user_type', '=', ADMIN];
		$cond[] = ['U.status', '=', ACTIVE];
		$cond[] = ['U.domain_list_fk', '=', get_domain_auth_id()];

		$user_details = $this->get_user_details($cond);

		foreach ($user_details as $k => $v) {
			$admin_user_id[$k] = $v['user_id'];
		}

		return $admin_user_id;
	}
}// main class end*************
