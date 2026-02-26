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
	/**
	 *verify is the user credentials are valid
	 *
	 *@param string $email    email of the user
	 *@param string @password password of the user
	 *
	 *return boolean status of the user credentials
	 */
	public function get_agent_user(int $cond): array{

		$query = 'select * from user where user_id = '.$cond;
		
		$data = $this->db->query($query)->result_array();
		return $data;
	}
	public function get_user_details(array $condition = [], bool $count = false, int $offset = 0, int $limit = PHP_INT_MAX, array $order_by = []): array
	{
		$filter_condition = ' and ';
		if (valid_array($condition) == true) {
			foreach ($condition as $k => $v) {
				$filter_condition .= implode($v).' and ';
			}
		}

		if (valid_array($order_by) == true) {
			$filter_order_by = 'ORDER BY';
			foreach ($order_by as $k => $v) {
				$filter_order_by .= implode($v).',';
			}
		} else {
			$filter_order_by = '';
		}
		$filter_condition = rtrim($filter_condition, 'and ');
		$filter_order_by = rtrim($filter_order_by, ',');
		if (!$count) {
		return $this->db->query('SELECT U.*, UT.user_type as user_profile_name
			FROM user AS U, user_type AS UT
		 	WHERE U.user_type=UT.origin '.$filter_condition.' limit '.$limit.' offset '.$offset.' '.$filter_order_by)->result_array();
		} else {
		return $this->db->query('SELECT count(*) as total FROM user AS U, user_type AS UT
		 WHERE U.user_type=UT.origin '.$filter_condition.' limit '.$limit.' offset '.$offset)->row();
		}
		// echo $this->db->last_query();exit;
	}

	/**
	 * get Domain user list in the system
	 */
		public function get_domain_user_list(array $condition = [], bool $count = false, int $offset = 0, int $limit = PHP_INT_MAX, array $order_by = []): array
	{
		$filter_condition = ' and ';
		if (valid_array($condition) == true) {
			foreach ($condition as $k => $v) {
				$filter_condition .= implode($v).' and ';
			}
		}
		if(is_domain_user() == false) {
			//PROVAB ADMIN
			//GET ALL DOMAIN ADMINS DETAILS
			$filter_condition .= ' U.domain_list_fk > 0 and U.user_type = '.ADMIN.' and U.user_id != '.intval($this->entity_user_id).' and ';
		} else if(is_domain_user() == true) {
			//DOMAIN ADMIN
			//GET ALL DOMAIN USERS DETAILS
			$filter_condition .= ' U.domain_list_fk ='.get_domain_auth_id().' and U.user_type != '.ADMIN.' and U.user_id != '.intval($this->entity_user_id).' and ';
		}
		if (valid_array($order_by) == true) {
			$filter_order_by = 'ORDER BY';
			foreach ($order_by as $k => $v) {
				$filter_order_by .= implode($v).',';
			}
		} else {
			$filter_order_by = '';
		}
		$filter_condition = rtrim($filter_condition, 'and ');
		$filter_order_by = rtrim($filter_order_by, ',');
		if (!$count) {
			return $this->db->query('SELECT U.*, UT.user_type, ACL.country_code as country_code_value FROM user AS U, user_type AS UT, api_country_list AS ACL
		 WHERE U.user_type=UT.origin 
		 AND U.country_code=ACL.origin'.$filter_condition.' limit '.$limit.' offset '.$offset.' '.$filter_order_by)->result_array();
		} else {
			return $this->db->query('SELECT count(*) as total FROM user AS U, user_type AS UT, api_country_list AS ACL
		 WHERE U.user_type=UT.origin 
		 AND U.country_code=ACL.origin'.$filter_condition.' limit '.$limit.' offset '.$offset)->row();
		}
	}

	/**
	 * get Logged in Users
	 Balu A (25-05-2015) - 25-05-2015
	 */
		public function get_logged_in_users(array $condition = [], bool $count = false, int $offset = 0, int $limit = PHP_INT_MAX): array
	{
		$filter_condition = ' and ';
		if (valid_array($condition) == true) {
			foreach ($condition as  $v) {
				$filter_condition .= implode($v).' and ';
			}
		}
		if(is_domain_user() == false) {
			//PROVAB ADMIN
			//GET ALL DOMAIN ADMINS DETAILS
			$filter_condition .= ' U.domain_list_fk > 0 and U.user_type = '.ADMIN.' and U.user_id != '.intval($this->entity_user_id).' and ';
		} else if(is_domain_user() == true) {
			//DOMAIN ADMIN
			//GET ALL DOMAIN USERS DETAILS
			$filter_condition .= ' U.domain_list_fk ='.get_domain_auth_id().' and U.user_type != '.ADMIN.' and U.user_id != '.intval($this->entity_user_id).' and ';
		}
		$filter_condition = rtrim($filter_condition, 'and ');
		$current_date = date('Y-m-d', time());
		if (!$count) {
			return $this->db->query('SELECT U.*, UT.user_type, LM.login_date_time as login_time,LM.logout_date_time as logout_time,LM.login_ip
			FROM user AS U
			JOIN user_type AS UT ON U.user_type=UT.origin
			JOIN api_country_list AS ACL ON U.country_code=ACL.origin
			JOIN login_manager AS LM ON U.user_type=LM.user_type and U.user_id=LM.user_id
		    WHERE LM.login_date_time >="'.$current_date.' 00:00:00"
			and (LM.logout_date_time = "0000-00-00 00:00:00" or LM.logout_date_time >= "'.$current_date.' 00:00:00")
			 '.$filter_condition.' order by LM.logout_date_time asc limit '.$limit.' offset '.$offset)->result_array();
		} else {
			return $this->db->query('SELECT count(*) as total FROM user AS U
			JOIN user_type AS UT ON U.user_type=UT.origin
			JOIN api_country_list AS ACL ON U.country_code=ACL.origin
			JOIN login_manager AS LM ON U.user_type=LM.user_type and U.user_id=LM.user_id
		    WHERE LM.login_date_time >="'.$current_date.' 00:00:00"
			and (LM.logout_date_time = "0000-00-00 00:00:00" or LM.logout_date_time >= "'.$current_date.' 00:00:00")'.$filter_condition.' limit '.$limit.' offset '.$offset)->row();
		}
	}

	/**
	 * get Domain List present in the system
	 */
	public function get_domain_details(): array
	{
		$query = 'select DL.*,CONCAT(U.first_name, " ", U.last_name) as created_user_name from domain_list DL join user U on DL.created_by_id=U.user_id';
		return $this->db->query($query)->result_array();
	}

	/**
	 *update logout time
	 *
	 *@param number $LID unique login id which has to be updated
	 *
	 *@return status;
	 */
	public function update_login_manager(int $user_id, int $login_id): void
	{
		$condition = array(
				'user_id' => $user_id,
				'origin' => $login_id
		);
		//update all the logout session in login manager
		$this->custom_db->update_record('login_manager', array('logout_date_time' => date('Y-m-d H:i:s', time())), $condition);
	}


	/**
	 * Create Login Manager
	 */
	public function create_login_auth_record(int $user_id, int $user_type): int
	{$data=[];$login_details=[];
		$login_details['browser'] = $_SERVER['HTTP_USER_AGENT'];
		$remote_ip = $_SERVER['REMOTE_ADDR'];
		$this->update_auth_record_expiry($user_id, $user_type, $remote_ip);
		//logout of same user from same ip
		$login_details['info'] = file_get_contents('https://tools.keycdn.com/geo.json');
		$data['user_id'] = $user_id;
		$data['user_type'] = $user_type;
		$data['login_date_time'] = date('Y-m-d H:i:s');
		$data['login_ip'] = $remote_ip;
		$data['attributes'] = json_encode($login_details);
		$login_id = $this->custom_db->insert_record('login_manager', $data);
		return $login_id['insert_id'];
	}

	/**
	 * Update logout
	 * @param $user_id
	 * @param $user_type
	 * @param $remote_ip
	 * @param $browser
	 */
	public function update_auth_record_expiry(int $user_id, int $user_type, string $remote_ip): void
	
	{$cond=[];
		$cond['user_id'] = $user_id;
		$cond['user_type'] = $user_type;
		$cond['login_ip'] = $remote_ip;
		$this->custom_db->update_record('login_manager', array('logout_date_time' => date('Y-m-d H:i:s')), $cond);
	}
	
		public function get_current_user_details(): array
	{
		if (intval($this->entity_user_id) > 0) {
			$cond = array(array('U.user_id', '=', intval($this->entity_user_id)));
				
			$user = $this->get_user_details($cond);
			$user[0]['uuid']=provab_decrypt($user[0]['uuid']);
			$user[0]['email']=provab_decrypt($user[0]['email']);
			$user[0]['user_name']=provab_decrypt($user[0]['user_name']);
			$user[0]['password']=provab_decrypt($user[0]['password']);
			return $user;
		} else {
			return false;
		}
	}
	/**
	 * Balu A
	 */
	public function get_admin_user_id(): array
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

	public function active_user(string $username, string $password): array
	{$condition=[];
		$condition[] = array('U.status', '=', ACTIVE);
		$condition[] = array('U.user_type', '=', SUPPLIER);
		$condition[] = array('U.user_name', '=', $this->db->escape(provab_encrypt(trim($username))));
		$condition[] = array('U.password', '=', $this->db->escape(provab_encrypt(md5(trim($password)))));
			//debug($condition);die;
		return $this->get_user_details($condition);

	}
	
	
}
