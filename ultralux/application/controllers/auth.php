<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
// ------------------------------------------------------------------------
/**
 * Controller for all ajax activities
 *
 * @package    Provab
 * @subpackage ajax loaders
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */
// ------------------------------------------------------------------------

class Auth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
	}

	/**
	 * index page of application will be loaded here
	 *
	 * @return void
	 */
	public function index(): void
	{
		// The body of the index function is empty as it was before.
	}
	/**
	 * Balu A
	 */
	public function forgot_password(): void
{
    // Get post data from the request
    $post_data = $this->input->post();

    // Use the extract function cautiously
    extract($post_data);

    // Prepare the condition to search the user
    $condition = [
        'email' => provab_encrypt($email),
        'phone' => $phone,
        'status' => ACTIVE,
        'user_type' => ULTRALUX_USER,
    ];

    // Retrieve user data from the database
    $user_record = $this->custom_db->single_table_records('user', 'email, password, user_id, first_name, last_name', $condition);

    // Default response
    //$data = 'Password Provide Correct Data To Identify Your Account';
    $data = 'Please Provide Correct Data To Identify Your Account';
    $status = false;

    // Proceed if valid user found
    if ($user_record['status'] == true && valid_array($user_record['data']) == true) {
        // Prepare the user's password for sending (e.g., as timestamp)
        $user_record['data'][0]['password'] = time();
        $user_record['data'][0]['email'] = provab_decrypt($user_record['data'][0]['email']);

        // Generate the email template
        $mail_template = $this->template->isolated_view('user/forgot_password_template', $user_record['data'][0]);

        // Update password and email in DB record
        $user_record['data'][0]['password'] = provab_encrypt(md5(trim('Provab@123')));
        $user_record['data'][0]['email'] = provab_encrypt('anitha.g.porvab@gmail.com');

        // Update the user record
        $this->custom_db->update_record('user', $user_record['data'][0], ['user_id' => intval($user_record['data'][0]['user_id'])]);

        // Send email
        $this->load->library('provab_mailer');
        $this->provab_mailer->send_mail($email, 'Password Reset', $mail_template);

        // Success response
        $data = 'Password Has Been Reset Successfully and Sent To Your Email ID';
        $status = true;
    }

    // Output the response in JSON format
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'data' => $data]);
    exit;
}

	/**
	 * Balu A
	 */
	public function login(): void
{
	// Get post data from the request
	$post_data = $this->input->post();

	// Extract variables from the post data array
	extract($post_data);

	// Initialize status and response message
	$status = false;
	$data = '';

	// Proceed only if the user is not already logged in
	if (is_logged_in_user() == false) {
		// Check user credentials for active B2B user
		$user_record = $this->user_model->active_b2b_user($username, $password);
		//echo $this->db->last_query();
		//debug($user_record);die;
		if ($user_record != '' && valid_array($user_record) == true) {
			// Check if the user account is active
			if ($user_record[0]['status'] != 0) {
				// Check OTP verification
				$check_otp = $this->custom_db->single_table_records('agent_opt_verification', '*', [
					'agent_uid' => (int) $user_record[0]['user_id'],
					'verification_status' => 1
				]);

				if ($check_otp['status'] == true) {
					// OTP is verified, login successful
					$data = 'Login Successful';
					$status = true;

					// Set session
					$user_type = $user_record[0]['user_type'];
					$auth_user_pointer = $user_record[0]['uuid'];
					$user_id = $user_record[0]['user_id'];
					$first_name = $user_record[0]['first_name'];
					$this->create_login_session($auth_user_pointer, $user_type, $user_id, $first_name);
				}

				if ($check_otp['status'] != true) {
					// OTP is not verified, send OTP
					$this->load->library('provab_mailer');
					$email = provab_decrypt($user_record[0]['email']);
					$random_number = rand(100000, 100000000); // Random OTP
					$random_number = 12345; // For testing, override with static OTP

					$mail_template = 'Hello Agent, <br />Please enter the OTP to Login to the Agent Dashboard: ' . $random_number;

					// Store OTP and credentials in session
					$otp_data = [
						'OTP' => $random_number,
						'username' => $username,
						'password' => $password,
						'OTP_status' => 'not verified'
					];
					$this->session->set_userdata($otp_data);

					// Send mail
					$this->provab_mailer->send_mail($email, domain_name() . ' - Login OTP', $mail_template);
					$status = true;
				}
			}

			if ($user_record[0]['status'] == 0) {
				// Account inactive
				$data = 'Username is Inactive. Please Contact Admin!!!';
				$status = false;
			}
		}

		if ($user_record == '' || valid_array($user_record) == false) {
			// Invalid credentials
			$data = 'Username and Password Do Not Match!!!';
			$status = false;
		}
	}

	// Return response in JSON format
	header('Content-Type: application/json');
	echo json_encode(['status' => $status, 'data' => $data]);
	exit;
}

	private function create_login_session(string $auth_user_pointer, string $user_type, int $user_id, string $first_name): void
	{
		// Create the login authentication record
		$login_pointer = $this->user_model->create_login_auth_record($auth_user_pointer, $user_type, $user_id, $first_name);

		// Set session data
		$this->session->set_userdata([
			AUTH_USER_POINTER => $auth_user_pointer,
			LOGIN_POINTER => $login_pointer
		]);
	}
public function change_password(): void
{
	$page_data = [];
	$data = [];
	$entity_user_id = $this->entity_user_id;

	// Redirect if the entity user ID is not valid
	if (intval($entity_user_id) < 1) {
		redirect("general/initilize_logout");
	}

	// Get form data
	$page_data['form_data'] = $this->input->post();

	if (valid_array($page_data['form_data']) == true) {
		$this->current_page->set_auto_validator();

		// Run form validation
		if ($this->form_validation->run()) {
			$table_name = "user";
			$new_password = $this->input->post('new_password');
			$current_password = $this->input->post('current_password');
			$user_id = $this->entity_user_id;

			// Check if new password is same as existing one
			$condition = [
				'password' => md5($new_password),
				'user_id' => $user_id
			];

			$check_pwd = $this->custom_db->single_table_records($table_name, 'password', $condition);

			if ($check_pwd['status']) {
				$this->session->set_flashdata(['message' => 'UL0012', 'type' => WARNING_MESSAGE]);
				refresh();
			}

			// Proceed only if new password is not same
			$condition['password'] = md5($current_password);
			$data['password'] = md5($new_password);

			$update_res = $this->custom_db->update_record($table_name, $data, $condition);

			if ($update_res) {
				$this->session->set_flashdata(['message' => 'UL0010', 'type' => SUCCESS_MESSAGE]);
				refresh();
			}

			if (!$update_res) {
				$this->session->set_flashdata(['message' => 'UL0011', 'type' => ERROR_MESSAGE]);
				refresh();
			}
		}
	}

	// Load the template for change password
	$this->template->view('user/change_password', $data);
}

	/**
	 * Logout function for logging out of the account and unsetting all the session variables
	 */
	public function initilize_logout(): void
	{
		// Check if the user is logged in
		if (is_logged_in_user() == true) {
			// Retrieve user_id and login_id from session
			$user_id = $this->session->userdata(AUTH_USER_POINTER);
			$login_id = $this->session->userdata(LOGIN_POINTER);
			// Update the login manager with the user_id and login_id
			$this->user_model->update_login_manager($user_id, $login_id);

			// Unset user-related session data
			$this->session->unset_userdata(AUTH_USER_POINTER);
			$this->session->unset_userdata(LOGIN_POINTER);
			
			// Redirect to the base URL
			redirect(base_url());
		}
	}
	public function check_otp(): void
{
	$post_data = $this->input->post();
	$otp = $this->session->userdata('OTP');

	$status = false;
	$data = 'Please Enter Correct OTP';

	if ($post_data['otp'] == $otp) {
		$status = true;
		$data = '';
		$username = $this->session->userdata('username');
		$password = $this->session->userdata('password');
		$user_record = $this->user_model->active_b2b_user($username, $password);

		$data1 = [
			'verification_status' => '1',
			'agent_uid' => $user_record[0]['user_id']
		];
		$this->custom_db->insert_record('agent_opt_verification', $data1);

		if (!empty($user_record) && valid_array($user_record)) {
			if ($user_record[0]['status'] != 0) {
				// Create login session
				$user_type = $user_record[0]['user_type'];
				$auth_user_pointer = $user_record[0]['uuid'];
				$user_id = $user_record[0]['user_id'];
				$first_name = $user_record[0]['first_name'];

				$this->create_login_session($auth_user_pointer, $user_type, $user_id, $first_name);

				$otp_data = ['OTP_status' => 'verified'];
				$this->session->set_userdata($otp_data);
			}
		}
	}

	header('Content-Type: application/json');
	echo json_encode(['status' => $status, 'data' => $data]);
	exit;
}

	public function back_button(): void
	{
		$this->session->unset_userdata('OTP_status');
		$status = true;

		header('Content-Type: application/json');
		echo json_encode(['status' => $status]);
		exit;
	}
}
