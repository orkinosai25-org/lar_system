<?php

/**
 *
 * @author Balu A<balu.provab@gmail.com>
 *
 */
class application
{
	var $CI; //code igniter object
	var $userId; // user id to identify user
	var $page_configuration;
	var $skip_validation;

	/**
	 * constructor to initialize data
	 */
	function __construct()
	{
		$this->CI = &get_instance();
		$this->CI->load->library('provab_page_loader.php');
		$this->CI->load->helper('url');
		if (!isset($this->CI->session)) {
			$this->CI->load->library('session');
		}
		$this->footer_needle = $this->header_needle = $this->CI->uri->segment(2);
		$this->skip_validation = false;
		$this->CI->language_preference = 'english';
		$this->CI->lang->load('form', $this->CI->language_preference);
		$this->CI->lang->load('application', $this->CI->language_preference);
		$this->CI->lang->load('utility', $this->CI->language_preference);
	}

	/**
	 * We need to initialize all the domain key details here
	 * Written only for provab
	 */
	function initialize_domain_key()
	{
		$domain_auth_id = $GLOBALS['CI']->session->userdata(DOMAIN_AUTH_ID);
		$domain_key = $GLOBALS['CI']->session->userdata(DOMAIN_KEY);
		$domain_details = $GLOBALS['CI']->custom_db->single_table_records('domain_list', '*', array(
			'domain_key' => CURRENT_DOMAIN_KEY
		));
		$module = $this->CI->uri->segment(1);
		if (empty($module) == false) {
			if ($module == 'flight') {
				$module = 'flight';
			} else if ($module == 'hotel') {
				$module = 'hotel';
			} else if ($module == 'bus') {
				$module = 'bus';
			} else if ($module == 'sightseeing') {
				$module = 'activity';
			} else if ($module == 'commute') {
				$module = 'transfer';
			} else {
				$module = 'general';
			}
		} else {
			$module = 'general';
		}


		$seo_details = $GLOBALS['CI']->custom_db->single_table_records('seo', '*', array('module' => $module));
		if (valid_array($domain_details) == true) {
			// IF DOMAIN KEY IS NOT SET, THEN SET THE DOMANIN DETAILS
			$domain_details = $domain_details['data'][0];
			// debug($domain_details);exit;
			//$this->CI->application_default_template = 'template_v3';
			$theme = '';
			$theme = isset($_GET['theme']) ? $_GET['theme'] : isset($domain_details['b2b_theme_id']);
			$this->CI->application_default_template = isset($theme) ? $domain_details['b2b_theme_id'] : $domain_details['theme_id'];
			$this->CI->entity_domain_name = $domain_details['domain_name'];
			$this->CI->entity_domain_phone = $domain_details['phone'];
			$this->CI->entity_domain_mail = $domain_details['email'];
			$this->CI->application_domain_logo = $domain_details['domain_logo'];
			$this->CI->entity_domain_website = $domain_details['domain_webiste'];
			if (intval($domain_auth_id) == 0 && empty($domain_key) == true and strlen(trim($domain_details['domain_name'])) > 0) {
				$domain_session_data = array();
				// SETTING DOMAIN KEY
				$domain_session_data[DOMAIN_AUTH_ID] = intval($domain_details['origin']);
				// SETTING DOMAIN CONFIGURATION
				$domain_session_data[DOMAIN_KEY] = base64_encode(trim($domain_details['domain_key']));
				$this->CI->session->set_userdata($domain_session_data);
			}
		}
		define('HEADER_DOMAIN_WEBSITE', $this->CI->entity_domain_website);
		define('HEADER_DOMAIN_NAME', $this->CI->entity_domain_name);
		if ($seo_details['status'] == SUCCESS_STATUS) {

			define('HEADER_TITLE_SUFFIX', $seo_details['data'][0]['title']); // Common Suffix For All Pages
			define('META_KEYWORDS', $seo_details['data'][0]['keyword']); // Common Suffix For All Pages
			define('META_DESCRIPTION', $seo_details['data'][0]['description']); // Common Suffix For All Pages
		} else {
			if (empty($this->CI->entity_domain_name) == false) {
				define('HEADER_TITLE_SUFFIX', ' - Welcome' . $this->CI->entity_domain_name); // Common Suffix For All Pages
			} else {
				define('HEADER_TITLE_SUFFIX', ' - Welcome Travels'); // Common Suffix For All Pages
			}
			define('META_KEYWORDS', HEADER_TITLE_SUFFIX . "Flights, Hotels, Busses, Packages, Low Cost Flights");
			define('META_DESCRIPTION', 'Flight Bookings, Hotel Bookings, Bus Bookings, Package bookings system.');
		}

	}

	/**
	 * Set all the active modules for doamin
	 */
	function initialize_domain_modules()
	{
		//set domain active modules based on auth key
		$domain_key = base64_decode($this->CI->session->userdata(DOMAIN_KEY));
		$domain_auth_id = $this->CI->session->userdata(DOMAIN_AUTH_ID);
		//set global modules data
		$active_domain_modules = $this->CI->module_model->get_active_module_list($domain_auth_id, $domain_key);
		// debug($active_domain_modules);exit;
		$this->CI->active_domain_modules = $active_domain_modules;
	}

	/**
	 * Following pages will not have any validations
	 */
	function bouncer_page_validation()
	{
		$skip_validation_list = array('forgot_password', 'agentRegister', 'get_state_list', 'get_city_lists', 'check_user_name_available', 'check_otp'); //SKIP LIST
		if (in_array($this->header_needle, $skip_validation_list)) {
			$this->skip_validation = true;
		}
	}
	/**
	 * Handle hook for multiple page login system
	 */

	function initilize_multiple_login()
	{
		$this->bouncer_page_validation();
		if ($this->skip_validation == false) {
			$auth_login_id = $this->CI->session->userdata(AUTH_USER_POINTER);
			if (empty($auth_login_id) == false) {
				$condition['uuid'] = $auth_login_id;
				$condition['status'] = ACTIVE;
			}
			if (isset($condition) == true and is_array($condition) == true and count($condition) > 0) {
				$condition['status'] = ACTIVE;
				$user_details = $this->CI->db->get_where('user', $condition)->row_array();
				if (valid_array($user_details) == true) {

					$this->set_global_entity_data($user_details);
				}
			}
		}
	}

	/**
	 * Handle hook for dedicated page login system
	 */
	function initilize_dedicated_login()
	{

		$this->bouncer_page_validation();
		if ($this->skip_validation == false) {

			if (isset($_POST['email'])) {
				$email = isset($_POST['email']) ? $_POST['email'] : '';

			}
			if (isset($_POST['username'])) {
				$email = isset($_POST['username']) ? $_POST['username'] : '';

			}
			$password = isset($_POST['password']) ? $_POST['password'] : '';

			//check session when the user is not in the login page
			$user_id = $this->CI->session->userdata(AUTH_USER_POINTER);
			//segments
			$segment1 = $this->CI->uri->segment(1);
			$segment2 = $this->CI->uri->segment(2);
			if (empty($user_id) == false) {
				$user_details = $this->CI->db->get_where('user', array('uuid' => $user_id, 'status' => ACTIVE))->row_array();
				if (valid_array($user_details) == false) {
					$this->CI->session->unset_userdata(array(AUTH_USER_POINTER => '', LOGIN_POINTER => ''));
				}
			} elseif (
				($segment1 == 'general' and $segment2 == 'index') ||
				(empty($segment1) == true || ($segment2) == true) and
				empty($email) == false and empty($password) == false
			) {
				//USER Logging in with credentials
				$this->CI->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|min_length[4]|max_length[45]|xss_clean');
				$this->CI->form_validation->set_rules('password', 'Password', 'required|min_length[5]|max_length[45]|xss_clean');
				if ($this->CI->form_validation->run()) {

					$condition['password'] = provab_encrypt(md5(trim($this->CI->db->escape_str($password))));
					$condition['user_name'] = provab_encrypt($email);
					$condition['status'] = ACTIVE;
					$domain_id = get_domain_auth_id();
					$domain_key = get_domain_key();
					if (intval($domain_id) > 0 && empty($domain_key) == false) { // IF DOMAIN KEY EXISTS
						//$condition['domain_list_fk'] = intval(get_domain_auth_id());
						$this->CI->db->where_in('domain_list_fk', array(intval(get_domain_auth_id())));
					}
					/** USER TYPES **/
					$user_types = array(SUPPLIER);
					// debug($condition);
					// exit;
					//Merge condition with super admin also
					$user_details = $this->CI->db->where_in('user_type', $user_types)->get_where('user', $condition);
					if ($user_details) {
						$user_details = $user_details->row_array();
					}
				}
			} else {
				$this->CI->session->unset_userdata(array(AUTH_USER_POINTER => '', LOGIN_POINTER => ''));
				if (($this->CI->uri->segment(1) != 'general' || $this->CI->uri->segment(2) != 'index')) {
					redirect('general/index');
				}
			}
			//set the details when the user details is present
			if (isset($user_details) == true and valid_array($user_details) == true and count($user_details) > 0) {
				$this->set_global_entity_data($user_details);
				if (empty($email) == false and empty($password) == false) {
					//SETTING SESSION DATA
					$user_session_data = array();
					$user_session_data[AUTH_USER_POINTER] = $user_details['uuid'];
					$user_session_data[LOGIN_POINTER] = intval($this->update_login_manager());
					$this->CI->session->set_userdata($user_session_data);
				}
			}
		}
	}

	function set_global_entity_data($user_details)
	{

		$this->CI->entity_user_id = $user_details['user_id'];
		$this->CI->entity_domain_id = $user_details['domain_list_fk'];
		$this->CI->entity_uuid = provab_decrypt($user_details['uuid']);
		$this->CI->entity_user_type = $user_details['user_type'];
		$this->CI->entity_email = provab_decrypt($user_details['email']);
		$this->CI->entity_name = get_enum_list('title', $user_details['title']) . ' ' . ucfirst($user_details['first_name']) . ' ' . ucfirst($user_details['last_name']);
		$this->CI->agency_name = $user_details['agency_name']; //Balu A
		$this->CI->entity_address = $user_details['address'];
		$this->CI->entity_phone = $user_details['phone'];
		$this->CI->entity_country_code = $user_details['country_code'];
		$this->CI->entity_status = $user_details['status'];
		$this->CI->entity_date_of_birth = $user_details['date_of_birth'];
		$this->CI->entity_image = $user_details['image'];
		$this->CI->entity_creation = $user_details['created_datetime'];
	}

	/**
	 *function to update login time and logout time details of user when user
	 *login or logout.
	 */
	function update_login_manager()
	{
		//check login
		return $this->CI->user_model->create_login_auth_record($this->CI->entity_uuid, $this->CI->entity_user_type);
	}

	/*
	 * load current page configuration
	 */
	function load_current_page_configuration()
	{
		$this->page_configuration['current_page'] = $this->CI->current_page = new Provab_Page_Loader();
	}
}
