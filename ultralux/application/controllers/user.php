<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab
 * @subpackage General
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */
error_reporting(0);
class User extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('module_model');
		//$this->output->enable_profiler(TRUE);
	}
	/**
	 * Create default domain configuration
	 */
	function create_default_domain(string $domain_key_name = '192.168.0.26'): void
	{
		include_once DOMAIN_CONFIG . 'default_domain_configuration.php';
	}

	/**
	 * Index page of the application will be loaded here
	 */
	function index(): void
	{
		if (is_logged_in_user()) {
			redirect('menu/index');
		}
	}
	/**
	 * Generate my account view to user
	 */
	function balance(): void
	{
		$page_data = [];
		$this->template->view('user/balance', $page_data);
	}
	/**
	 * Generate my account view to user
	 */
	function account(): void
	{
		$page_data = [];
		$page_data['form_data'] = $this->input->post();
		$get_data = $this->input->get();

		/**
		 * USE USER PAGE FOR MY ACCOUNT
		 */
		$this->user_page = new Provab_Page_Loader('user_management');

		if (isset($get_data['uid'])) {
			$get_data['uid'] = (int) $get_data['uid'];

			if (empty($page_data['form_data'])) {
				/*** EDIT DATA ***/
				$cond = [['U.user_id', '=', (int) $get_data['uid']]];
				$edit_data = $this->user_model->get_user_details($cond);

				if (!empty($edit_data)) {
					$page_data['form_data'] = $edit_data[0];
					$page_data['form_data']['uuid'] = provab_decrypt($page_data['form_data']['uuid']);
					$page_data['form_data']['email'] = provab_decrypt($page_data['form_data']['email']);
				} else {
					redirect('security/log_event');
				}
			} elseif (!empty($page_data['form_data']) && (check_default_edit_privilege($get_data['uid']) || super_privilege())) {
				/** AUTOMATE VALIDATOR **/
				$page_data['form_data']['language_preference'] = 'english';
				$this->form_validation->set_rules('title', 'Title', 'trim|required|min_length[1]|max_length[4]');
				$this->form_validation->set_rules('first_name', 'First Name', 'trim|required|min_length[2]|max_length[45]|xss_clean');
				$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required|min_length[1]|max_length[45]|xss_clean');
				$this->form_validation->set_rules('country_code', 'Country Code', 'trim|required|min_length[1]|max_length[6]');
				$this->form_validation->set_rules('phone', 'Mobile Number', 'trim|required|min_length[7]|max_length[10]|numeric');
				$this->form_validation->set_rules('address', 'Address', 'trim|required|min_length[5]|max_length[500]|xss_clean');
				$this->form_validation->set_rules('date_of_birth', 'Date of Birth', 'trim|min_length[5]|xss_clean');
				$this->form_validation->set_rules('user_id', 'UserId', 'trim|min_length[1]|max_length[10]|numeric');

				if ($this->form_validation->run()) {
					if ((int) $get_data['uid'] == (int) $page_data['form_data']['user_id'] && (int) $page_data['form_data']['user_id'] > 0) {
						// Application Logger
						$notification_users = $this->user_model->get_admin_user_id();
						$remarks = $page_data['form_data']['first_name'] . ' Updated Profile Details';
						$action_query_string = [
							'user_id' => $this->entity_user_id,
							'uuid' => $this->entity_uuid,
							'user_type' => ULTRALUX_USER
						];
						$this->application_logger->profile_update($page_data['form_data']['first_name'], $remarks, $action_query_string, [], $this->entity_user_id, $notification_users);

						// Update Data
						unset($page_data['form_data']['FID']);
						unset($page_data['form_data']['email']);
						unset($page_data['form_data']['uuid']);
						$user_id = (int) $page_data['form_data']['user_id'];
						unset($page_data['form_data']['user_id']);
						$page_data['form_data']['date_of_birth'] = date('Y-m-d', strtotime($page_data['form_data']['date_of_birth']));
						$this->custom_db->update_record('user', $page_data['form_data'], ['user_id' => $user_id]);

						$this->session->set_flashdata(['message' => 'AL004', 'type' => SUCCESS_MESSAGE]);

						// File Upload
						if (!empty($_FILES) && $_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
							if (function_exists("check_mime_image_type")) {
								if (!check_mime_image_type($_FILES['image']['tmp_name'])) {
									echo "Please select the image files only (gif|jpg|png|jpeg)";
									exit;
								}
							}

							$config = [
								'upload_path' => $this->template->domain_image_upload_path(),
								'allowed_types' => 'gif|jpg|png|jpeg',
								'file_name' => time(),
								'max_size' => MAX_DOMAIN_LOGO_SIZE,
								'max_width' => MAX_DOMAIN_LOGO_WIDTH,
								'max_height' => MAX_DOMAIN_LOGO_HEIGHT,
								'remove_spaces' => false
							];

							$temp_record = $this->custom_db->single_table_records('user', 'image', ['user_id' => $user_id]);
							$icon = $temp_record['data'][0]['image'];

							// Delete old files
							if (!empty($icon)) {
								$temp_profile_image = $this->template->domain_image_full_path($icon);
								if (file_exists($temp_profile_image)) {
									unlink($temp_profile_image);
								}
							}

							// Upload image
							$this->load->library('upload', $config);
							if (!$this->upload->do_upload('image')) {
								$message = $this->upload->display_errors();
								if ($message == '<p>The filetype you are attempting to upload is not allowed.</p>') {
									$this->session->set_flashdata(['message' => 'AL005', 'type' => FAILURE_MESSAGE]);
								}
							} else {
								$image_data = $this->upload->data();
							}

							$this->custom_db->update_record('user', ['image' => $image_data['file_name']], ['user_id' => $user_id]);
						}

						refresh();
					} else {
						redirect('security/log_event');
					}
				}
			}

			// Fetch country code list and phone code array
			$page_data['country_code_list'] = $this->db_cache_api->get_country_code_list();
			$country_code = $this->db_cache_api->get_country_code_list_profile();
			$mobile_code = $this->db_cache_api->get_mobile_code($page_data['form_data']['country_code']);
			$page_data['mobile_code'] = $mobile_code;

			$phone_code_array = [];
			foreach ($country_code['data'] as $c_key => $c_value) {
				$phone_code_array[$c_value['origin']] = $c_value['name'] . ' ' . $c_value['country_code'];
			}

			$page_data['phone_code_array'] = $phone_code_array;
			$this->template->view('user/account', $page_data);
		} else {
			redirect('security/log_event');
		}
	}
	/**
	 * Agent Registration
	 */
	public function agentRegister(): void
	{
		$page_data = [];
		$page_data_arr = [];
		$data = [];
		$page_data['form_data'] = $this->input->post();

		if (valid_array($page_data['form_data']) == true) {

			$page_data['form_data']['language_preference'] = 'english';
			$this->form_validation->set_rules('company_name', 'Company', 'trim|required|min_length[2]|max_length[45]|xss_clean');
			$this->form_validation->set_rules('title', 'Title', 'trim|required|min_length[1]|max_length[4]');
			$this->form_validation->set_rules('first_name', 'FirstName', 'trim|required|min_length[2]|max_length[45]|xss_clean');
			$this->form_validation->set_rules('last_name', 'LastName', 'trim|required|min_length[1]|max_length[45]|xss_clean');
			$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_check');
			$this->form_validation->set_rules('user_name', 'Username', 'valid_email|required|max_length[80]|callback_username_check');
			$this->form_validation->set_rules('password', 'Password', 'matches[password_c]|min_length[5]|max_length[45]|required|callback_valid_password');
			$this->form_validation->set_rules('password_c', 'Confirm');
			$this->form_validation->set_rules('country_code', 'CountryCode', 'trim|required|min_length[1]|max_length[6]');
			$this->form_validation->set_rules('phone', 'Mobile', 'trim|required|min_length[7]|max_length[10]|numeric');
			$this->form_validation->set_rules('office_phone', 'Phone', 'trim|required|min_length[7]|max_length[15]|numeric');
			$this->form_validation->set_rules('address', 'Address', 'trim|required|max_length[500]|xss_clean');
			$this->form_validation->set_rules('city', 'City Name', 'trim|required');
			$this->form_validation->set_rules('country', 'Country Name', 'trim|required');
			$this->form_validation->set_rules('term_condition', 'Term And condition', 'trim|required');
			$this->form_validation->set_rules('pin_code', 'Pincode', 'trim|required');

			if ($this->form_validation->run()) {
				//unset($page_data['form_data']['password_c']);
				$page_data_arr['form_data']['uuid'] = provab_encrypt(PROJECT_PREFIX . time());
				$page_data_arr['form_data']['password'] = provab_encrypt(md5(trim($page_data['form_data']['password'])));
				$page_data_arr['form_data']['title'] = $page_data['form_data']['title'];
				$page_data_arr['form_data']['user_type'] = ULTRALUX_USER;
				$page_data_arr['form_data']['created_datetime'] = date("Y-m-d h:i:sa");
				$page_data_arr['form_data']['domain_list_fk'] = intval(get_domain_auth_id());
				$page_data_arr['form_data']['status'] = FAILURE_STATUS;
				$page_data_arr['form_data']['first_name'] = $page_data['form_data']['first_name'];
				$page_data_arr['form_data']['last_name'] = $page_data['form_data']['last_name'];
				$page_data_arr['form_data']['country_code'] = $page_data['form_data']['country_code'];
				$page_data_arr['form_data']['phone'] = $page_data['form_data']['phone'];
				$page_data_arr['form_data']['email'] = provab_encrypt(trim($page_data['form_data']['email']));
				$page_data_arr['form_data']['agency_name'] = $page_data['form_data']['company_name'];
				$page_data_arr['form_data']['pan_number'] = $page_data['form_data']['pan_number'];
				$page_data_arr['form_data']['pan_holdername'] = $page_data['form_data']['pan_holdername'];
				$page_data_arr['form_data']['address'] = $page_data['form_data']['address'];
				$page_data_arr['form_data']['country_name'] = $page_data['form_data']['country'];
				$page_data_arr['form_data']['city'] = $page_data['form_data']['city'];
				$page_data_arr['form_data']['pin_code'] = $page_data['form_data']['pin_code'];
				$page_data_arr['form_data']['office_phone'] = $page_data['form_data']['office_phone'];
				$page_data_arr['form_data']['user_name'] = provab_encrypt($page_data['form_data']['user_name']);
				$page_data_arr['form_data']['creation_source'] = 'portal';
				$page_data_arr['form_data']['terms_conditions'] = 1;
				$page_data_arr['form_data']['created_by_id'] = 0;
				$insert_id = $this->custom_db->insert_record('user', $page_data_arr['form_data']);
				$insert_id = $insert_id['insert_id'];

				// B2B User Details
				$b2b_user_details = array();
				$get_admin_currency = $this->custom_db->single_table_records('domain_list', 'currency_converter_fk', array('domain_key' => CURRENT_DOMAIN_KEY));
				$b2b_user_details['currency_converter_fk'] = $get_admin_currency['data'][0]['currency_converter_fk'];

				$image = '';
				$b2b_user_details['user_oid'] = $insert_id;
				$b2b_user_details['logo'] = $image;
				$b2b_user_details['balance'] = 0;
				$b2b_user_details['created_datetime'] = $page_data_arr['form_data']['created_datetime'];
				$this->custom_db->insert_record('ultralux_user_details', $b2b_user_details);

				$page_data_arr['form_data']['password'] = $page_data['form_data']['password']; // Dont remove
				$data['agent'] = $page_data_arr['form_data'];
				$mail_template = $this->template->isolated_view('agent/agent_template', $data);

				$email = provab_decrypt($page_data_arr['form_data']['email']);
				$this->load->library('provab_mailer');
				$subject = 'Agent Registration Acknowledgment-www.' . $_SERVER['HTTP_HOST'];
				$mail_status = $this->provab_mailer->send_mail($email, $subject, $mail_template);

				// Application Logger
				$remarks = $email . ' Has Registered From Agent Portal';
				$notification_users = $this->user_model->get_admin_user_id();
				$action_query_string = array();
				$action_query_string['user_id'] = $insert_id;
				$action_query_string['uuid'] = provab_decrypt($page_data_arr['form_data']['uuid']);
				$action_query_string['user_type'] = ULTRALUX_USER;
				$this->application_logger->registration($email, $remarks, $insert_id, $action_query_string, array(), $notification_users);

				$this->session->set_flashdata(array('message' => ' Congratulations!! You are successfully registered as an Agent', 'type' => SUCCESS_MESSAGE, 'override_app_msg' => true));
				redirect('user/agentRegister/show');
			}
		}

		$data['message'] = isset($banner)? $banner :'';
		$temp_record = $this->custom_db->single_table_records('domain_list', '*');
		$data['active_data'] = $temp_record['data'][0];

		$temp_record = $this->custom_db->single_table_records('api_country_list', '*');
		$data['phone_code'] = $temp_record['data'];

		$city_record = $this->custom_db->single_table_records('api_city_list', 'destination', array('country' => $data['active_data']['api_country_list_fk']));
		$data['city_list'] = $city_record['data'][0];
		$data['country_code_list'] = $this->db_cache_api->get_country_code_list();

		$country_code = $this->db_cache_api->get_country_code_list_profile();
		$phone_code_array = array();
		foreach ($country_code['data'] as $c_key => $c_value) {
			$phone_code_array[$c_value['origin']] = $c_value['name'] . ' ' . $c_value['country_code'];
		}

		$data['phone_code_array'] = $phone_code_array;
		$data['country_list'] = $this->db_cache_api->get_country_list();

		$this->template->view('agent/agent_register', $data);
	}
	public function username_check(string $name): bool
	{
		$condition = [];
		$condition['user_name'] = provab_encrypt($name);
		$condition['user_type'] = ULTRALUX_USER;
		$condition['domain_list_fk'] = intval(get_domain_auth_id());

		$data = $this->custom_db->single_table_records('user', 'user_id', $condition);

		if ($data['status'] == SUCCESS_STATUS && valid_array($data['data']) == true) {
			$this->form_validation->set_message(__FUNCTION__, $name . ' is Not Available!!!');
			return false;
		}

		return true;
	}

	public function useremail_check(string $name): bool
	{
		$condition = [];
		$condition['email'] = provab_encrypt($name);
		$condition['user_type'] = ULTRALUX_USER;
		$condition['domain_list_fk'] = intval(get_domain_auth_id());

		$data = $this->custom_db->single_table_records('user', 'user_id', $condition);

		if ($data['status'] == SUCCESS_STATUS && valid_array($data['data']) == true) {
			$this->form_validation->set_message(__FUNCTION__, $name . ' is Not Available!!!');
			return false;
		}

		return true;
	}
	/**
	 * Logout function for logout from account and unset all the session variables
	 */
	public function initilize_logout(): void
	{
		if (is_logged_in_user()) {
			$this->general_model->update_login_manager($this->session->userdata(LOGIN_POINTER));
			$this->session->unset_userdata(array(AUTH_USER_POINTER => '', LOGIN_POINTER => ''));
			// added by nithin for unsetting the email username
			$this->session->unset_userdata('mail_user');
			redirect('general/index');
		}
	}

	/**
	 * Oops page of the application will be loaded here
	 */
	public function ooops(): void
	{
		$this->template->view('utilities/404.php');
	}
	/**
	 * Function to Change the Password of a User
	 */
	public function change_password(): void
{
	$page_data = [];
	$condition = [];
	$data = [];
	$get_data = $this->input->get();

	// Check if user ID exists in the URL
	if (!isset($get_data['uid'])) {
		redirect("general/initilize_logout");
	}
	$user_id = $get_data['uid'];

	$page_data['form_data'] = $this->input->post();

	if (valid_array($page_data['form_data']) === true) {
		// Load form validation library
		$this->load->library('form_validation');

		// Set validation rules
		$this->form_validation->set_rules('current_password', 'Current Password', 'required|min_length[5]|max_length[45]|callback_password_check');
		$this->form_validation->set_rules('new_password', 'New Password', 'matches[confirm_password]|min_length[5]|max_length[45]|required|callback_valid_password');
		$this->form_validation->set_rules('confirm_password', 'Confirm', 'callback_check_new_password');

		if ($this->form_validation->run()) {
			$table_name = "user";

			// Check if the new password is the same as the current one
			$condition['password'] = provab_encrypt(md5(trim($this->input->post('new_password'))));
			$condition['user_id'] = $user_id;

			$check_pwd = $this->custom_db->single_table_records($table_name, 'password', $condition);

			if ($check_pwd['status'] === false) {
				// If new password is different from current password, update it
				$condition['password'] = provab_encrypt(md5(trim($this->input->post('current_password'))));
				$condition['user_id'] = $user_id;
				$data['password'] = provab_encrypt(md5(trim($this->input->post('new_password'))));
				$update_res = $this->custom_db->update_record($table_name, $data, $condition);

				if ($update_res) {
					$this->session->set_flashdata([
						'message' => 'Password Changed Successfully',
						'type' => SUCCESS_MESSAGE,
						'override_app_msg' => true,
					]);
					refresh();
				}

				$this->session->set_flashdata([
					'message' => 'Invalid Current Password',
					'type' => ERROR_MESSAGE,
					'override_app_msg' => true,
				]);
				refresh();
			}

			$this->session->set_flashdata([
				'message' => 'Current Password and New Password Are Same',
				'type' => WARNING_MESSAGE,
				'override_app_msg' => true,
			]);
			refresh();
		}
	}

	// Render the change password page
	$this->template->view('user/change_password', $data);
}

	/**
	 * Manage Domain Logo
	 * Balu A (25-05-2015) - 26-05-2015
	 */
	public function domain_logo(): void
	{
		// Get the POST data
		$config = [];
		$page_data  = [];
		$post_data = $this->input->post();

		// Check if the POST data is valid and contains 'origin'
		if (valid_array($post_data) && isset($post_data['origin'])) {
			$GLOBALS['CI']->template->domain_images();

			// Check if the domain is authorized and has a valid domain ID
			if (intval($post_data['origin']) == get_domain_auth_id() && get_domain_auth_id() > 0) {
				$domain_origin = get_domain_auth_id();

				// FILE UPLOAD
				if (valid_array($_FILES) && $_FILES['domain_logo']['error'] == 0 && $_FILES['domain_logo']['size'] > 0) {
					// Check if the uploaded file is an image
					if (function_exists("check_mime_image_type")) {
						if (!check_mime_image_type($_FILES['domain_logo']['tmp_name'])) {
							echo "Please select the image files only (gif|jpg|png|jpeg)";
							exit;
						}
					}

					// Configure upload settings
					$config['upload_path'] = $this->template->domain_image_upload_path();
					$temp_file_name = $_FILES['domain_logo']['name'];
					$config['allowed_types'] = 'gif|jpg|png|jpeg';
					$config['file_name'] = get_domain_key() . $temp_file_name;
					$config['max_size'] = MAX_DOMAIN_LOGO_SIZE;
					$config['max_width'] = MAX_DOMAIN_LOGO_WIDTH;
					$config['max_height'] = MAX_DOMAIN_LOGO_HEIGHT;
					$config['remove_spaces'] = false;

					// Fetch current domain logo
					$temp_record = $this->custom_db->single_table_records('ultralux_user_details', 'logo', array('user_oid' => intval($this->entity_user_id)));
					$domain_logo = $temp_record['data'][0]['logo'];

					// DELETE OLD FILES
					if (!empty($domain_logo)) {
						$temp_domain_logo = $this->template->domain_image_full_path($domain_logo); // Get the full file path
						if (file_exists($temp_domain_logo)) {
							unlink($temp_domain_logo); // Remove the old logo file
						}
					}

					// Initialize the upload library and upload the image
					$this->load->library('upload', $config);
					$this->upload->initialize($config);

					if (!$this->upload->do_upload('domain_logo')) {
						echo $this->upload->display_errors(); // Display any upload errors
					} else {
						// Get uploaded image data
						$image_data = $this->upload->data();
						// Update the logo in the database
						$this->custom_db->update_record('ultralux_user_details', array('logo' => $image_data['file_name']), array('user_oid' => intval($this->entity_user_id)));
					}
				}

				// Refresh the page to reflect the changes
				refresh();
			}
		}

		// Fetch the current logo to display
		$temp_details = $this->custom_db->single_table_records('ultralux_user_details', 'logo', array('user_oid' => intval($this->entity_user_id)));

		if ($temp_details['status'] == true) {
			$page_data['domain_logo'] = $temp_details['data'][0]['logo'];
		} else {
			$page_data['domain_logo'] = '';
		}

		// Load the view to show the domain logo
		$this->template->view('user/domain_logo', $page_data);
	}
	/**
	 * Get City Data based on Country ID.
	 */
	public function get_city_data(): void
	{
		// Get the country ID from the POST request
		$country_id = $this->input->post('country_id');

		// Fetch the city list based on country ID
		$city_list_response = $this->custom_db->single_table_records(
			'api_city_list',
			'*',
			['country' => $country_id],
			0,
			100000000,
			['destination' => 'asc']
		);

		// Initialize the options string
		$options = '';

		// Check if the city list is valid
		if ($city_list_response['status'] == true && !empty($city_list_response['data'])) {
			// Extract the city data
			$city_list = $city_list_response['data'];

			// Loop through each city and create an option for the dropdown
			foreach ($city_list as $city) {
				$options .= "<option value=\"" . htmlspecialchars($city['origin'], ENT_QUOTES) . "\">" . htmlspecialchars($city['destination'], ENT_QUOTES) . "</option>";
			}
		}

		// Output the options string
		echo $options;
	}
	/**
	 * Validate the password
	 *
	 * @param string $password
	 *
	 * @return bool
	 */
	public function valid_password(string $password): bool
	{
		// Trim any unnecessary spaces from the password
		$password = trim($password);

		// Define regex patterns for validation
		$regex_lowercase = '/[a-z]/';
		$regex_uppercase = '/[A-Z]/';
		$regex_number = '/[0-9]/';
		$regex_special = '/[!@#$%^&*()\-_=+{};:,<.>§~]/';

		// Check if the password is empty
		if (empty($password)) {
			$this->form_validation->set_message('valid_password', 'The Password field is required.');
			return false;
		}

		// Check if the password contains at least one lowercase letter
		if (preg_match_all($regex_lowercase, $password) < 1) {
			$this->form_validation->set_message('valid_password', 'The Password field must be at least one lowercase letter.');
			return false;
		}

		// Check if the password contains at least one uppercase letter
		if (preg_match_all($regex_uppercase, $password) < 1) {
			$this->form_validation->set_message('valid_password', 'The Password field must be at least one uppercase letter.');
			return false;
		}

		// Check if the password contains at least one number
		if (preg_match_all($regex_number, $password) < 1) {
			$this->form_validation->set_message('valid_password', 'The Password field must have at least one number.');
			return false;
		}

		// Check if the password contains at least one special character
		if (preg_match_all($regex_special, $password) < 1) {
			$this->form_validation->set_message('valid_password', 'The Password field must have at least one special character.' . ' ' . htmlentities('!@#$%^&*()\-_=+{};:,<.>§~'));
			return false;
		}

		// Check if the password length is less than 5 characters
		if (strlen($password) < 5) {
			$this->form_validation->set_message('valid_password', 'The Password field must be at least 5 characters in length.');
			return false;
		}

		// Check if the password length exceeds 32 characters
		if (strlen($password) > 32) {
			$this->form_validation->set_message('valid_password', 'The Password field cannot exceed 32 characters in length.');
			return false;
		}

		// If all checks pass, return true
		return true;
	}
}
