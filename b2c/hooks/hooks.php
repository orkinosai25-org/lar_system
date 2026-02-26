<?php
/**
 *
 * @author Balu A<balu.provab@gmail.com>
 *
 */
class application {
	var $CI; // code igniter object
	var $userId; // user id to identify user
	var $page_configuration;
	var $skip_validation;
	var $header_needle;
	var $footer_needle;
	public $language_preference;
	/**
	 * constructor to initialize data
	 */
	function __construct() {
		$this->CI = &get_instance ();
		$this->CI->load->library ( 'provab_page_loader.php' );
		$this->CI->load->helper ( 'url' );
		if (! isset ( $this->CI->session )) {
			$this->CI->load->library ( 'session' );
		}
		$this->footer_needle = $this->header_needle = $this->CI->uri->segment ( 2 );
		$this->skip_validation = false;
		$this->CI->language_preference  = 'english';
		$this->CI->lang->load ( 'form', $this->CI->language_preference );
		$this->CI->lang->load ( 'application', $this->CI->language_preference );
		$this->CI->lang->load ( 'utility', $this->CI->language_preference );
		// $this->CI->session->set_userdata(array(AUTH_USER_POINTER => 10, LOGIN_POINTER => intval(100)));
	}

	
	/**
	 * We need to initialize all the domain key details here
	 * Written only for provab
	 */
	function initialize_domain_key(): void
	{
		//$domain_auth_id = 1;
		$domain_key = CURRENT_DOMAIN_KEY;

		$domain_details = $GLOBALS['CI']->custom_db->single_table_records('domain_list', '*', [
			'domain_key' => $domain_key
		]);
		$module_segment = $this->CI->uri->segment(1);
		$module_map = [
			'flight' => 'flight',
			'hotel' => 'hotel',
			'bus' => 'bus',
			'sightseeing' => 'activity',
			'transferv1' => 'transfer',
		];
		$module = $module_map[$module_segment] ?? 'general';

		$seo_details = $GLOBALS['CI']->custom_db->single_table_records('seo', '*', ['module' => $module]);

		if (valid_array($domain_details)) {
			$domain_details = $domain_details['data'][0];

			$theme_id = $_GET['theme'] ?? $domain_details['theme_id'];
			$this->CI->application_default_template = $theme_id;
			$this->CI->entity_domain_name = $domain_details['domain_name'];
			$this->CI->entity_domain_website = $domain_details['domain_webiste'];
			$this->CI->entity_domain_phone = $domain_details['phone'];
			$this->CI->entity_domain_mail = $domain_details['email'];
			$this->CI->application_domain_logo = $domain_details['domain_logo'];

			if (trim($domain_details['domain_name']) !== '') {
				$domain_session_data = [
					DOMAIN_AUTH_ID => intval($domain_details['origin']),
					DOMAIN_KEY => base64_encode(trim($domain_details['domain_key'])),
				];
				$this->CI->session->set_userdata($domain_session_data);
			}
		}

		define('HEADER_DOMAIN_WEBSITE', $this->CI->entity_domain_website);
		define('HEADER_DOMAIN_NAME', $this->CI->entity_domain_name);

		if ($seo_details['status'] === SUCCESS_STATUS) {
			define('HEADER_TITLE_SUFFIX', $seo_details['data'][0]['title']);
			define('META_KEYWORDS', $seo_details['data'][0]['keyword']);
			define('META_DESCRIPTION', $seo_details['data'][0]['description']);
			return;
		}

		$domain_name = $this->CI->entity_domain_name;
		$header_title_suffix = ' - Welcome' . ($domain_name ?: ' Travels');

		define('HEADER_TITLE_SUFFIX', $header_title_suffix);
		define('META_KEYWORDS', $header_title_suffix . ' Flights, Hotels, Busses, Packages, Low Cost Flights');
		define('META_DESCRIPTION', 'Flight Bookings, Hotel Bookings, Bus Bookings, Package bookings system.');
	}


	/**
	 * Set all the active modules for doamin
	 */
	function initialize_domain_modules(): void
{
    $domain_key = CURRENT_DOMAIN_KEY;
    $domain_auth_id = 1;
    $active_domain_modules = $this->CI->module_model->get_active_module_list($domain_auth_id, $domain_key);
    $this->CI->active_domain_modules = $active_domain_modules;
}

function bouncer_page_validation(): void
{
    $skip_validation_list = ['forgot_password'];
    $this->skip_validation = in_array($this->header_needle, $skip_validation_list);
}

	function initilize_multiple_login(): void
	{
		$this->bouncer_page_validation();

		if ($this->skip_validation) {
			return;
		}

		$auth_login_id = $this->CI->session->userdata(AUTH_USER_POINTER);

		$condition = [];
		if (!empty($auth_login_id)) {
			$condition = [
				'uuid' => $auth_login_id,
				'status' => ACTIVE,
				'user_type' => B2C_USER
			];
		}

		if (!empty($condition)) {
			$user_details = $this->CI->db->get_where('user', $condition)->row_array();
			if (valid_array($user_details)) {
				$this->set_global_entity_data($user_details);
			}
		}
	}

	function set_global_entity_data(array $user_details): void
	{
		$this->CI->entity_user_id = $user_details['user_id'];
		$this->CI->entity_domain_id = $user_details['domain_list_fk'];
		$this->CI->entity_uuid = provab_decrypt($user_details['uuid']);
		$this->CI->entity_user_type = $user_details['user_type'];
		$this->CI->entity_email = provab_decrypt($user_details['email']);
		$this->CI->entity_title = $user_details['title'];
		$this->CI->entity_first_name = $user_details['first_name'];
		$this->CI->entity_signature = $user_details['signature'];
		$this->CI->entity_last_name = $user_details['last_name'];
		$this->CI->entity_name = get_enum_list('title', $user_details['title']) . ' ' . ucfirst($user_details['first_name']) . ' ' . ucfirst($user_details['last_name']);
		$this->CI->entity_address = $user_details['address'];
		$this->CI->entity_phone = $user_details['phone'];
		$this->CI->entity_country_code = $user_details['country_code'];
		$this->CI->entity_status = $user_details['status'];
		$this->CI->entity_date_of_birth = $user_details['date_of_birth'];
		$this->CI->entity_image = $user_details['image'];
		$this->CI->entity_created_datetime = $user_details['created_datetime'];
		$this->CI->entity_language_preference = $user_details['language_preference'];
	}

	function update_login_manager(): int
	{
		$loginDetails = [];
		$loginDetails['browser'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
		$remote_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
		$loginDetails['info'] = file_get_contents("http://ipinfo.io/{$remote_ip}/json");

		$checkLogin = $this->CI->custom_db->single_table_records(
			'login_manager',
			'*',
			[
				'user_id' => $this->CI->entity_user_id,
				'login_ip !=' => $remote_ip
			],
			'0',
			'10',
			''
		);

		if (empty($checkLogin['data'])) {
			$checkLoginSameIP = $this->CI->custom_db->single_table_records(
				'login_manager',
				'*',
				[
					'user_id' => $this->CI->entity_uuid,
					'login_ip' => $remote_ip
				],
				'0',
				'10',
				''
			);

			if (!empty($checkLoginSameIP['data'])) {
				return $this->CI->session->userdata[LOGIN_POINTER] ?? $this->CI->entity_user_id;
			}

			$loginID = $this->CI->custom_db->insert_record('login_manager', [
				'user_type' => $this->CI->entity_user_type,
				'user_id' => $this->CI->entity_uuid,
				'login_date_time' => date('Y-m-d H:i:s'),
				'login_ip' => $remote_ip,
				'attributes' => mysql_real_escape_string(json_encode($loginDetails))
			]);

			return $loginID['insert_id'];
		}

		$this->CI->custom_db->update_record('login_manager', [
			'logout_date_time' => date('Y-m-d H:i:s')
		], [
			'user_id' => $this->CI->entity_uuid
		]);

		$loginID = $this->CI->custom_db->insert_record('login_manager', [
			'user_type' => $this->CI->entity_user_type,
			'user_id' => $this->CI->entity_uuid,
			'login_date_time' => date('Y-m-d H:i:s'),
			'login_ip' => $remote_ip,
			'attributes' => mysql_real_escape_string(json_encode($loginDetails))
		]);

		return $loginID['insert_id'];
	}

	/*
	 * load current page configuration
	 */
	public function load_current_page_configuration(): void
	{
		// Removed commented-out line
		$this->page_configuration['current_page'] = $this->CI->current_page = new Provab_Page_Loader();
	}

	public function set_page_configuration(): void
	{
		// Kept empty as in the original
	}

	public function set_project_configuration(): void
	{
		$api_data_result = $this->CI->custom_db->single_table_records('api_urls_new', '*', ['status' => 1]);
		$domain_list_result = $this->CI->custom_db->single_table_records('domain_list', '*', ['status' => 1]);
		if ($api_data_result['status'] != true) {
			return;
		}
		$api_data = $api_data_result['data'][0];
		$domain_data = $domain_list_result['data'][0];
		$system = strtolower($api_data['system']) === 'test' ? 'test' : 'live';

		$this->CI->{$system . '_username'} = $domain_data[$system . '_username'];
		$this->CI->{$system . '_password'} = $domain_data[$system . '_password'];

		// Set system modes
		$this->CI->flight_engine_system = $system;
		$this->CI->hotel_engine_system = $system;
		$this->CI->external_service_system = $system;
		$this->CI->car_engine_system = $system;

		// Decrypt API URLs
		$secret_iv = PROVAB_SECRET_IV;
		$md5_key = PROVAB_MD5_SECRET;
		$encrypt_key = PROVAB_ENC_KEY;
		$encrypt_method = 'AES-256-CBC';

		$decrypt_result = $this->CI->db->query(
			"SELECT AES_DECRYPT($encrypt_key, SHA2('" . $md5_key . "', 512)) AS decrypt_data"
		);

		$db_data = $decrypt_result->row();
		$secret_key = trim($db_data->decrypt_data);
		$key = hash('sha256', $secret_key);
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		$urls = openssl_decrypt(base64_decode($api_data['urls']), $encrypt_method, $key, 0, $iv);
		$urls = json_decode($urls, true);

		// Manually override URLs
		$urls['flight_url'] = 'https://www.travelsoho.com/LAR/services/webservices/index.php/flight/service/';
		$urls['hotel_url'] = 'https://www.travelsoho.com/LAR/services/webservices/hotel_v3/service/';
		$urls['external_service'] = 'https://www.travelsoho.com/LAR/services/webservices/index.php/rest/';

		// Set final configurations
		$this->CI->flight_url = $urls['flight_url'];
		$this->CI->hotel_url = $urls['hotel_url'];
		$this->CI->car_url = $urls['car_url'] ?? '';
		$this->CI->external_service = $urls['external_service'];
		$this->CI->domain_key = $domain_data['domain_key'];
	}
}
