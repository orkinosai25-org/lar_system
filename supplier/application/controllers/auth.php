<?php if (!defined('BASEPATH'))
	exit('No direct script access allowed');
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
	 */
	public function index(): void
	{
	}

	/**
	 * Balu A
	 */
	public function forgot_password(): void
	{
$email=[];$condition=[];
		$post_data = $this->input->post();
		extract($post_data);
		//email, phone
		$condition['email'] = provab_encrypt($email);

		$condition['status'] = ACTIVE;
		$condition['user_type'] = SUPPLIER;
		$user_record = $this->custom_db->single_table_records('user', 'email, password, user_id, first_name, last_name', $condition);

		if ($user_record['status'] == true and valid_array($user_record['data']) == true) {
			$temp_password = generate_temporary_password();
			$user_record['data'][0]['password'] = $temp_password;
			$user_record['data'][0]['email'] = provab_decrypt($user_record['data'][0]['email']);
			//send email
			$mail_template = $this->template->isolated_view('emails/forgot_password', $user_record['data'][0]);
			$user_record['data'][0]['password'] = provab_encrypt(md5(trim($user_record['data'][0]['password'])));
			$user_record['data'][0]['email'] = provab_encrypt($user_record['data'][0]['email']);
			$this->custom_db->update_record('user', $user_record['data'][0], array('user_id' => intval($user_record['data'][0]['user_id'])));
			$this->load->library('provab_mailer');
			$this->provab_mailer->send_mail($email, 'Password Reset', $mail_template);
			$data = 'Password Has Been Reset Successfully and Sent To Your Email ID: ' . $temp_password;
			$status = true;
		} else {
			$data = 'Password Provide Correct Data To Identify Your Account';
			$status = false;
		}
		header('content-type:application/json');
		echo json_encode(array('status' => $status, 'data' => $data));
		exit;
	}

	/**
	 * Balu A
	 */
	public function login(): void
	{$otp_data=[];$username=[];$password=[];
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
		$post_data = $this->input->post();
		extract($post_data);
		$status = false;
		$data = '';
		if (is_logged_in_user() == false) {
			//email, phone
			$user_record = $this->user_model->active_user($username, $password);

			if ($user_record != '' and valid_array($user_record) == true) {
				if ($user_record[0]['status'] != 0) {
					// $check_otp = $this->custom_db->single_table_records('agent_opt_verification', '*', array('agent_uid' => intval($user_record[0]['user_id']), 'verification_status' => 1));
					$this->load->library('provab_mailer');
					// $email = provab_decrypt($user_record[0]['email']);
					// $random_number = rand(100000,100000000);
					$random_number = 12345;
					// $mail_template = 'Hello Host, <br />Please enter the OTP to Login Dashboard:- ' . $random_number;
					$otp_data['OTP'] = $random_number;
					$otp_data['username'] = $username;
					$otp_data['password'] = $password;
					$otp_data['OTP_status'] = 'not verified';
					$this->session->set_userdata($otp_data);
					// 	 $res = $this->provab_mailer->send_mail($email, domain_name().' - Login OTP',$mail_template );
					$status = true;
				} else {
					$data = 'Username is Inactive Please Contact Admin!!!';
					$status = false;
				}
			} else {
				$data = 'Username And Password Does Not Match!!!';
				$status = false;
			}
		}

		header('content-type:application/json');
		echo json_encode(array('status' => $status, 'data' => $data));
		exit;
    
	}
   private function create_login_session(string $auth_user_pointer, string $user_type, int $user_id, string $first_name): void
	{

		$login_pointer = $this->user_model->create_login_auth_record($auth_user_pointer, $user_type, $user_id, $first_name);
		$this->session->set_userdata(array(AUTH_USER_POINTER => $auth_user_pointer, LOGIN_POINTER => $login_pointer));
	}

	public function change_password(): void
	{$condition=[];$user_id=[];$page_data=[];$username=[];
		$data = array();
		$entity_user_id = $this->entity_user_id;
		if (intval($entity_user_id) < 1) {
			redirect("general/initilize_logout");
		}
		$page_data['form_data'] = $this->input->post();
		if (valid_array($page_data['form_data']) == TRUE) {
			$this->current_page->set_auto_validator();
			if ($this->form_validation->run()) {
				$table_name = "user";
				/** Checking New Password and Old Password Are Same OR Not **/
				$condition['password'] = md5($this->input->post('new_password'));
				$condition['user_id'] = $user_id;
				$check_pwd = $this->custom_db->single_table_records($table_name, 'password', $condition);
				if (!$check_pwd['status']) {
					$condition['password'] = md5($this->input->post('current_password'));
					$condition['user_id'] = $user_id;
					$data['password'] = md5($this->input->post('new_password'));
					$update_res = $this->custom_db->update_record($table_name, $data, $condition);
					if ($update_res) {
						$this->session->set_flashdata(array('message' => 'UL0010', 'type' => SUCCESS_MESSAGE));
						refresh();
					} else {
						$this->session->set_flashdata(array('message' => 'UL0011', 'type' => ERROR_MESSAGE));
						refresh();
						/*$data['msg'] = 'UL0011';
																			   $data['type'] = ERROR_MESSAGE;*/
					}
				} else {
					$this->session->set_flashdata(array('message' => 'UL0012', 'type' => WARNING_MESSAGE));
					refresh();
					//redirect('general/change_password?uid='.urlencode($get_data['uid']));
				}
			}
		}
		$this->template->view('user/change_password', $data);
	}

	/**
	 * Logout function for logout from account and unset all the session variables
	 */
	public function initilize_logout(): void
	{
		if (is_logged_in_user()) {
			$user_id = $this->session->userdata(AUTH_USER_POINTER);
			$login_id = $this->session->userdata(LOGIN_POINTER);

			$this->user_model->update_login_manager($user_id, $login_id);
			$this->session->unset_userdata(array(AUTH_USER_POINTER => '', LOGIN_POINTER => ''));
			redirect(base_url());
		}
	}
    public function check_otp(): void
	{
$otp_data=[];$data1=[];
		$post_data = array();
		$post_data = $this->input->post();
		$otp = $this->session->userdata('OTP');
		$data = '';

		if ($post_data['otp'] == $otp) {
			$status = true;
			$username = $this->session->userdata('username');
			$password = $this->session->userdata('password');
			$user_record = $this->user_model->active_user($username, $password);
			$data1['verification_status'] = '1';
			$data1['agent_uid'] = $user_record[0]['user_id'];
			$this->custom_db->insert_record('agent_opt_verification', $data1);
			if ($user_record != '' and valid_array($user_record) == true) {
				if ($user_record[0]['status'] != 0) {

					$status = true;
					//create login pointer
					$user_type = $user_record[0]['user_type'];
					$auth_user_pointer = $user_record[0]['uuid'];
					$user_id = $user_record[0]['user_id'];
					$first_name = $user_record[0]['first_name'];
					// echo $auth_user_pointer;exit;
					$this->create_login_session($auth_user_pointer, $user_type, $user_id, $first_name);
				}
				$otp_data['OTP_status'] = 'verified';
				$this->session->set_userdata($otp_data);
			}
		} else {
			$status = false;
			$data = 'Please Enter Correct OTP';
		}
		header('content-type:application/json');
		echo json_encode(array('status' => $status, 'data' => $data));
		exit;
	}
	public function back_button(): void
	{

		$this->session->unset_userdata('OTP_status');
		$status = true;
		header('content-type:application/json');
		echo json_encode(array('status' => $status));
		exit;
	}
}
