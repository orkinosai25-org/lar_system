<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// ------------------------------------------------------------------------
/**
 * Controller for all ajax activities
 *
 * @package    Provab
 * @subpackage ajax loaders
 * @author     Balu A J<balu.provab@gmail.com>
 * @version    V1
 */
// ------------------------------------------------------------------------

class Auth extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
		$this->load->library('provab_sms');
		
		$this->load->library('social_network/facebook');
	}

	/**
	 * index page of application will be loaded here
	 */
	function index()
	{

	}

	function register_on_light_box(): void
	{
	    if (is_logged_in_user()) {
	        redirect(base_url());
	        exit;
	    }

	    $status = false;
	    $message = '';
	    $op_data = $this->input->post();

	    if (is_array($op_data) && !empty($op_data)) {
	        $this->load->library('form_validation');

	        $this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_username_check');//Username to be unique
				$this->form_validation->set_rules('password', 'Password', 'matches[confirm_password]|min_length[5]|max_length[45]|required|callback_valid_password');
				$this->form_validation->set_rules('confirm_password', 'Confirm');
				$this->form_validation->set_rules('first_name', 'Name', 'xss_clean|required|min_length[2]|max_length[45]');
				$user_check = $this->username_check($op_data['email']);
				 
				if($user_check == false){
					$message = "Username Already Registered !!!";
				}
	       	if ($this->form_validation->run() == FALSE) {
	       		$message = validation_errors();
			}
	       
	        if ($this->form_validation->run() ) {
	        	$email        = $op_data['email'] ?? '';$password     = $op_data['password'] ?? '';$first_name   = $op_data['first_name'] ?? ''; $country_code = $op_data['country_code'] ?? '';$phone        = $op_data['phone'] ?? '';$creation = $this->user_model->create_user($email,$password,$first_name,$country_code,$phone
	            );

				$user = $creation['data'][0] ?? null;
	            $message = get_app_message('AL003');
	            if (!empty($creation['status']) && $user) {
	                $original = $user['user_id'] ?? 0;
	                $encoded_data = rand(100, 999) . base64_encode((string)$original);
	                $activation_link = base_url('index.php/general/activate_account_status?origin=' . $encoded_data);

	                $user['activation_link'] = $activation_link;
	                $user['email'] = provab_decrypt($user['email'] ?? '');

	                $mail_template = $this->template->isolated_view('user/user_registration_template', $user);

	                $this->load->library('provab_mailer');
	                $this->provab_mailer->send_mail($user['email'], 'New-User Account Activation', $mail_template);

	                $status = true;
	                $message = get_app_message('AL002');
	            } 
	        } 

	    }
		header('Content-Type: application/json');
	    echo json_encode(['status' => $status, 'data' => $message], JSON_THROW_ON_ERROR);
	    exit;
	}

	/**
	 * Balu A
	 */
	public function register(): void
	{
	    if (is_logged_in_user()) {
	        redirect(base_url());
	        exit;
	    }

	    $op_data = $this->input->post();
	    $view_data = ['form' => $op_data ?? []];

	    if (is_array($op_data) && !empty($op_data)) {
	        $this->load->library('form_validation');

	        $this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_username_check');
	        $this->form_validation->set_rules('password', 'Password', 'matches[confirm_password]|min_length[5]|max_length[45]|required');
	        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required');
	        $this->form_validation->set_rules('first_name', 'Name', 'required|min_length[2]|max_length[45]');

	        if ($this->form_validation->run()) {
	            $email      = $op_data['email'] ?? '';
	            $password   = $op_data['password'] ?? '';
	            $first_name = $op_data['first_name'] ?? '';
	            $phone      = $op_data['phone'] ?? '';

	            $creation = $this->user_model->create_user($email, $password, $first_name, $phone);

	            if (!empty($creation['status']) && !empty($creation['data'][0])) {
	                $user = $creation['data'][0];
	                $original_id = $user['user_id'] ?? 0;
	                $encoded_data = rand(100, 999) . base64_encode((string)$original_id);
	                $activation_link = base_url('index.php/general/activate_account_status?origin=' . $encoded_data);

	                $user['activation_link'] = $activation_link;
	                $mail_template = $this->template->isolated_view('user/user_registration_template', $user);

	                // Optional debug view of email template
	                // echo $mail_template; exit;

	                $email_to = $user['email'] ?? '';
	                $this->load->library('provab_mailer');
	                $this->provab_mailer->send_mail($email_to, 'New-User Account Activation', $mail_template);

	                $this->session->set_flashdata(['message' => 'AL002', 'type' => SUCCESS_MESSAGE]);
	                redirect(base_url('index.php/auth/register'));
	                exit;
	            }

	            $this->session->set_flashdata(['message' => 'AL003', 'type' => ERROR_MESSAGE]);
	        }
	    }

	    $this->template->view('user/register', $view_data);
	}

	/*
	 * Jaganath
	 * Add guest User details
	 */
	public function register_guest_user(): void
	{
	    $post_data = $this->input->post();
	    $response = ['status' => false, 'data' => ''];

	    if (is_logged_in_user()) {
	        $this->output_json($response);
	        return;
	    }

	    $username = trim($post_data['username'] ?? '');
	    $mobile_number = trim($post_data['mobile_number'] ?? '');

	    if ($username === '' || $mobile_number === '') {
	        $response['data'] = 'Missing required fields';
	        $this->output_json($response);
	        return;
	    }

	    if (!$this->username_check($username)) {
	        $response['status'] = true;
	        $response['data'] = 'User Exists';
	        $this->output_json($response);
	        return;
	    }

	    $password = 'test';
	    $first_name = 'user';
	    $creation_source = 'guest';
	    $user_type = 0;

	    $this->user_model->create_user($username, $password, $first_name, $mobile_number, $creation_source, $user_type);

	    $response['status'] = true;
	    $response['data'] = 'Added guest User';
	    $this->output_json($response);
	}

	private function output_json(array $response): void
	{
	    header('Content-Type: application/json');
	    echo json_encode($response);
	    exit;
	}

	/**
	 * Call back function to check username availability
	 * @param string $name
	 */
	public function username_check(string $name): bool
	{
	    $condition = [
	        'email' => provab_encrypt($name),
	        'user_type' => B2C_USER,
	        'domain_list_fk' => (int) get_domain_auth_id()
	    ];

	    $data = $this->custom_db->single_table_records('user', 'user_id', $condition);

	    if (!empty($data['status']) && $data['status'] === SUCCESS_STATUS && valid_array($data['data'])) {
	        $this->form_validation->set_message('username_check', "$name Already Registered!!!");
	        return false;
	    }

	    return true;
	}


	/**
	 * Balu A
	 */
	public function forgot_password(): void
	{
	    $post_data = $this->input->post();
	    $email = $post_data['email'] ?? '';

	    $response = [
	        'status' => false,
	        'data' => 'Please Provide Correct Data To Identify Your Account'
	    ];

	    if (empty($email)) {
	        $this->send_json_response($response);
	    }

	    $condition = [
	        'email' => provab_encrypt($email),
	        'status' => ACTIVE,
	        'user_type' => B2C_USER
	    ];

	    $user_record = $this->custom_db->single_table_records('user', 'email, password, user_id, first_name, last_name', $condition);

	    if (!empty($user_record['status']) && valid_array($user_record['data'])) {
	        $user = $user_record['data'][0];

	        // Reset password to current timestamp (or generate a real one)
	        $plain_password = (string) time();
	        $encrypted_password = provab_encrypt(md5(trim($plain_password)));

	        // Prepare user data for update
	        $this->custom_db->update_record('user', ['password' => $encrypted_password], ['user_id' => (int) $user['user_id']]);

	        // Prepare and send email
	        $user['email'] = provab_decrypt($user['email']);
	        $user['password'] = $plain_password; // Display plain password in the email
	        $mail_template = $this->template->isolated_view('user/forgot_password_template', $user);

	        $this->load->library('provab_mailer');
	        $this->provab_mailer->send_mail($user['email'], 'Password Reset', $mail_template);

	        $response['status'] = true;
	        $response['data'] = 'Password has been reset and sent to your email ID.';
	    }

	    $this->send_json_response($response);
	}

	private function send_json_response(array $response): void
	{
	    header('Content-Type: application/json');
	    echo json_encode($response);
	    exit;
	}


	/**
	 * Balu A
	 */
	public function login(): void
	{
	    $post_data = $this->input->post();

	    $username = $post_data['username'] ?? '';
	    $password = $post_data['password'] ?? '';

	    $status = false;
	    $data = '';
	     
	    if (!is_logged_in_user()) {
	    	$status = false;
	    	$data = 'Username and password are required.';
	        if (!empty($username) && !empty($password)) {
	            $user_record = $this->user_model->active_b2c_user($username, $password);
	            // Invalid credentials
                $status = false;
                $data = 'Username and password do not match.';
	            if (!empty($user_record) && valid_array($user_record)) {
	                $user = $user_record[0];
	                // Account inactive
                    $status = false;
                    $data = 'Username is Inactive. Please contact admin.';
	                if ($user['status'] != 0) {
	                    // Successful login
	                    $status = true;
	                    $data = 'Login Successful';

	                    // Create login session
	                    $user_type = $user['user_type'];
	                    $auth_user_pointer = $user['uuid'];
	                    $user_id = $user['user_id'];
	                    $first_name = $user['first_name'];

	                    $this->create_login_session($auth_user_pointer, $user_type, $user_id, $first_name);
	                } 
	            } 
	        } 
	    }

	    // Send JSON response
	    $this->send_json_response(['status' => $status, 'data' => $data]);
	}

	/**
	 *
	 * @param string $auth_user_pointer	Unique user id
	 * @param string $user_type			User type
	 * @param number $user_id			Unique id of user - origin
	 * @param string $first_name		First name of the user
	 */
	private function create_login_session(string $auth_user_pointer, int $user_type, int $user_id, string $first_name): void
	{
	    // Create login authentication record
	    $login_pointer = $this->user_model->create_login_auth_record($auth_user_pointer, $user_type, $user_id, $first_name);

	    // Set session data
	    $this->session->set_userdata([
	        AUTH_USER_POINTER => $auth_user_pointer,
	        LOGIN_POINTER => $login_pointer
	    ]);
	}


	/**
	 * Network Source
	 */
	function social_network_login_auth(string $domain_name): void
	{
	    $response = [
	        'status' => FAILURE_STATUS,
	        'message' => 'Remote IO Error!!!'
	    ];

	    if (!is_logged_in_user()) {
	        $params = $this->input->post();
	        $email = $params['email'] ?? '';
	        $first_name = $params['name'] ?? '';

	        if (empty($email) || empty($first_name)) {
	            $response['message'] = 'Missing user data!';
	            echo json_encode($response);
	            exit;
	        }

	        $email1 = provab_encrypt($email);
	        $cond = [
	            ['U.email', '=', $this->db->escape($email1)],
	            ['U.user_type', '=', B2C_USER]
	        ];

	        switch (strtolower($domain_name)) {
	            case 'google':
	            case 'facebook':
	                $existing_user = $this->user_model->get_user_details($cond);

	                // Create new user if not existing
	                if (empty($existing_user)) {
	                    $this->user_model->create_user($email, 'password', $first_name, '', $domain_name);
	                    $existing_user = $this->user_model->get_user_details($cond);
	                }
	                break;

	            default:
	                $response['message'] = 'Unsupported social network!';
	                echo json_encode($response);
	                exit;
	        }

	        if (!empty($existing_user)) {
	            // Create session
	            $response['status'] = SUCCESS_STATUS;
	            $response['message'] = 'Login Successful!!!';

	            $user_type = 4;
	            $auth_user_pointer = $existing_user[0]['uuid'];
	            $user_id = $existing_user[0]['user_id'];
	            $first_name = $existing_user[0]['first_name'];
	            $this->create_login_session($auth_user_pointer, $user_type, $user_id, $first_name);
	        }
	    }

	    header('Content-Type: application/json');
	    echo json_encode($response);
	    exit;
	}

	function change_password(): void
	{
	    // Ensure the user is logged in
	    validate_user_login();
	    
	    $data = [];
	    $page_data = [];
	    $page_data['form_data'] = $this->input->post();
	    //debug($page_data);exit;
	    // Proceed if form data is valid
	    if (valid_array($page_data['form_data']) === true) {
	        $this->load->library('form_validation');
	        
	        // Set validation rules
	        $this->form_validation->set_rules('current_password', 'Current Password', 'required|min_length[5]|max_length[45]|callback_password_check');
	        $this->form_validation->set_rules('new_password', 'New Password', 'matches[confirm_password]|min_length[5]|max_length[45]|required|callback_valid_password');
	        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'callback_check_new_password');

	        // Run validation
	        if ($this->form_validation->run()) {
	            $table_name = 'user';
	            $new_password = trim($this->input->post('new_password'));
	            $current_password = trim($this->input->post('current_password'));
	            
	            // Encrypt the new password
	            $encrypted_password = provab_encrypt(md5($new_password));

	            // Check if the new password is the same as the old one
	            $condition = [
	                'password' => $encrypted_password,
	                'user_id' => $this->entity_user_id
	            ];
	            $check_pwd = $this->custom_db->single_table_records($table_name, 'password', $condition);

	            // Proceed if new password is different from current password
	           	// If new password is the same as current password
	           	if ($check_pwd['status'] === true) {
	           		$this->session->set_flashdata(['message' => 'UL0012', 'type' => WARNING_MESSAGE]);
					refresh();
				}
				$condition['password'] = provab_encrypt(md5($current_password));
	            $data['password'] = $encrypted_password;
				// Update the password in the database
                $update_res = $this->custom_db->update_record($table_name, $data, $condition);
                
                if (!$update_res) {
                   	$this->session->set_flashdata(['message' => 'UL0011', 'type' => ERROR_MESSAGE]);
                   	refresh();
                } 
                $this->application_logger->change_password($this->entity_name);
                $this->session->set_flashdata(['message' => 'UL0010', 'type' => SUCCESS_MESSAGE]);
                refresh();
	        }
	    }

	    // Get the current user's details
	    $user_details = $this->user_model->get_current_user_details();
	    $data['form_data'] = $user_details[0];

	    // Render the change password view
	    $this->template->view('user/change_password', $data);
	}
	/**
	 * Logout function for logout from account and unset all the session variables
	 */
	function initialize_logout(): void
	{
	    // Check if the user is logged in
	    // If user isn't logged in, still unset session data (for cleanup)
	    $this->session->unset_userdata([AUTH_USER_POINTER, LOGIN_POINTER]);
	    if (is_logged_in_user()) {
	        $user_id = $this->session->userdata(AUTH_USER_POINTER);
	        $login_id = $this->session->userdata(LOGIN_POINTER);

	        // Update login manager status
	        $this->user_model->update_login_manager($user_id, $login_id);

	        // Unset session data related to the user's login
	        $this->session->unset_userdata([AUTH_USER_POINTER, LOGIN_POINTER]);
	    } 
	    // Redirect the user to the home page
	    redirect(base_url());
	}

	/**
	 * Ajax Logout
	 * Logout function for logout from account and unset all the session variables
	 */
	function ajax_logout(): void
	{
	   $data = '';
		$status = false;
		if (is_logged_in_user()) {
			$user_id = $this->session->userdata(AUTH_USER_POINTER);
			$login_id = $this->session->userdata(LOGIN_POINTER);
			$this->user_model->update_login_manager($user_id, $login_id);
			$this->session->unset_userdata(array(AUTH_USER_POINTER => '',LOGIN_POINTER => ''));
			$status = true;
			$data = 'Logout Successfull';
		} else {
			$user_id = $this->session->userdata(AUTH_USER_POINTER);
			$login_id = $this->session->userdata(LOGIN_POINTER);
			$this->session->unset_userdata(array(AUTH_USER_POINTER => '',LOGIN_POINTER => ''));
			$status = false;
			$data = 'User Not Logged In!!!';
		}
		header('content-type:application/json');
		echo json_encode(array('status' => $status, 'data' => $data));
		exit;
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
	    $password = trim($password);

	    // Regular expressions for password validation
	    $regex_lowercase = '/[a-z]/';
	    $regex_uppercase = '/[A-Z]/';
	    $regex_number = '/[0-9]/';
	    $regex_special = '/[!@#$%^&*()\-_=+{};:,<.>§~]/';

	    // Check if password is empty
	    if (empty($password)) {
	        $this->form_validation->set_message('valid_password', 'The Password field is required.');
	        return false;
	    }

	    // Check if password matches required character types
	    if (!preg_match($regex_lowercase, $password) || 
	        !preg_match($regex_uppercase, $password) || 
	        !preg_match($regex_number, $password) || 
	        !preg_match($regex_special, $password)) {
	        
	        $this->form_validation->set_message('valid_password', 'The Password field must include at least one lowercase letter, one uppercase letter, one number, and one special character.');
	        return false;
	    }

	    // Check minimum password length
	    if (strlen($password) < 5) {
	        $this->form_validation->set_message('valid_password', 'The Password field must be at least 5 characters in length.');
	        return false;
	    }

	    // Check maximum password length
	    if (strlen($password) > 32) {
	        $this->form_validation->set_message('valid_password', 'The Password field cannot exceed 32 characters in length.');
	        return false;
	    }

	    return true;
	}

}
