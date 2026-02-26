<?php 
error_reporting(E_ALL);
ini_set('display_errors',1);
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab
 * @subpackage General
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */

class User extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('module_model');
		//$this->output->enable_profiler(TRUE);
	}
	/**
	 * index page of application will be loaded here
	 */
	function index():void
	{
		if (is_logged_in_user()) {
			redirect('menu/index');
		}
	}

	/**
	 * User Profile Management
	 */
	public function profile(): void
    {
        validate_user_login();

        $op_data = $this->input->post();
        $this->load->model('transaction_model');
        if($op_data){
	        if ($this->is_valid_profile_submission($op_data)) {
	            $this->handle_profile_update($op_data);
	            $this->handle_image_upload();
	            $this->session->set_flashdata(['message' => 'AL004', 'type' => SUCCESS_MESSAGE]);
	            $query_string = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
	            redirect('user/profile' . $query_string);
	            return;
	        }
	    }

        $page_data = $this->build_profile_page_data();
        $this->template->view('user/profile', $page_data);
    }

    private function is_valid_profile_submission(array $data): bool
    {
        return !empty($data['title']) &&
               !empty($data['first_name']) &&
               !empty($data['last_name']) &&
               !empty($data['country_code']) &&
               !empty($data['phone']) &&
               !empty($data['address']);
    }

    private function handle_profile_update(array $op_data): void
    {
        $notification_users = $this->user_model->get_admin_user_id();
        $remarks = $op_data['first_name'] . ' Updated Profile Details';
        $action_query_string = [
            'user_id' => $this->entity_user_id,
            'uuid' => $this->entity_uuid,
            'user_type' => B2C_USER,
        ];

        $this->application_logger->profile_update(
            $op_data['first_name'],
            $remarks,
            $action_query_string,
            [],
            $this->entity_user_id,
            $notification_users
        );

        $this->custom_db->update_record(
            'user',
            $op_data,
            ['user_id' => $this->entity_user_id, 'uuid' => provab_encrypt($this->entity_uuid)]
        );
    }

   private function handle_image_upload(): void
	{
	    if (
	        !valid_array($_FILES) ||
	        $_FILES['image']['error'] !== 0 ||
	        $_FILES['image']['size'] <= 0
	    ) {
	        return;
	    }

	    if (
	        function_exists("check_mime_image_type") &&
	        !check_mime_image_type($_FILES['image']['tmp_name'])
	    ) {
	        echo "Please select the image files only (gif|jpg|png|jpeg)";
	        exit;
	    }

	    $config = [
	        'upload_path' => $this->template->domain_image_upload_path(),
	        'allowed_types' => 'gif|jpg|png|jpeg',
	        'file_name' => $_FILES['image']['name'],
	        'max_size' => '1000000',
	        'remove_spaces' => false
	    ];

	    $temp_record = $this->custom_db->single_table_records(
	        'user',
	        'image',
	        ['user_id' => $this->entity_user_id]
	    );

	    $icon = $temp_record['data'][0]['image'] ?? '';
	    if (!empty($icon) && file_exists($config['upload_path'] . $icon)) {
	        unlink($config['upload_path'] . $icon);
	    }

	    $this->load->library('upload', $config);
	    $this->upload->initialize($config);

	    if (!$this->upload->do_upload('image')) {
	        echo $this->upload->display_errors();
	        return;
	    }

	    $image_data = $this->upload->data();
	    $this->custom_db->update_record('user', ['image' => $image_data['file_name']], ['user_id' => $this->entity_user_id]);
	}


    private function build_profile_page_data(): array
    {
        $currency_obj = new Currency();
        $page_data = [
            'currency_obj' => $currency_obj,
            'title' => $this->entity_title,
            'first_name' => $this->entity_first_name,
            'last_name' => $this->entity_last_name,
            'full_name' => $this->entity_name,
            'mobile_code' => $this->entity_country_code,
            'user_country_code' => $this->entity_country_code,
            'date_of_birth' => !empty($this->entity_date_of_birth) && strtotime($this->entity_date_of_birth)
                ? date('d-m-Y', strtotime($this->entity_date_of_birth))
                : '',
            'address' => $this->entity_address,
            'phone' => $this->entity_phone,
            'email' => $this->entity_email,
            'profile_image' => $this->entity_image,
            'signature' => $this->entity_signature,
        ];

        $this->load->library('booking_data_formatter');
        $booking_counts = $this->booking_data_formatter->get_booking_counts();
        $page_data['booking_counts'] = $booking_counts['data'];

        $country_code = $this->db_cache_api->get_country_code_list_profile();
       /*debug($country_code);exit;
        $phone_code_array = [];
        foreach ($country_code as $c_value) {
        	debug();exit;
            $phone_code_array[$c_value['country_code']] = $c_value['name'] . ' ' . $c_value['country_code'];
        }*/
        $page_data['phone_code_array'] = $country_code;

        $latest_transaction = $this->transaction_model->logs(0, 0, 5);
        $latest_transaction = $this->booking_data_formatter->format_recent_transactions($latest_transaction, 'b2c');
        $page_data['latest_transaction'] = $latest_transaction['data']['transaction_details'];

        $traveller_details = $this->traveller_details();
        $page_data['user_passport_visa_details'] = $traveller_details['user_passport_visa_details'];
        $page_data['traveller_details'] = $traveller_details['traveller_details'];
        $page_data['iso_country_list'] = $this->db_cache_api->get_iso_country_code();
        $page_data['country_list'] = $this->db_cache_api->get_iso_country_code();

        return $page_data;
    }

	/**
	 * Logout function for logout from account and unset all the session variables
	 */
	public function initilize_logout(): void
	{
	    if (is_logged_in_user()) {
	        $this->general_model->update_login_manager($this->session->userdata(LOGIN_POINTER));
	        $this->session->unset_userdata([AUTH_USER_POINTER => '', LOGIN_POINTER     => '', 'mail_user'
	        ]);
	    }

	   	 redirect('auth/initilize_logout');
	}

	/**
	 * oops page of application will be loaded here
	 */
	public function ooops():void
	{
		$this->template->view('utilities/404.php');
	}
	/**
	 * Function to Change the Password of a User
	 */
	public function change_password(): void
	{
	    validate_user_login();

	    $data = [];
	    $get_data = $this->input->get();

	    if (!isset($get_data['uid'])) {
	        redirect("general/initilize_logout");
	    }

	    $user_id = intval($this->encrypt->decode($get_data['uid']));
	    $data['form_data'] = $this->input->post();

	    if (valid_array($data['form_data']) === true) {
	        $this->current_page->set_auto_validator();
			if ($this->form_validation->run()) {
	            $table_name = "user";
				// Check if new password is the same as existing password
	            $new_password_hash = md5($this->input->post('new_password'));
	            $condition = [
	                'user_id' => $user_id,
	                'password' => $new_password_hash
	            ];

	            $check_pwd = $this->custom_db->single_table_records($table_name, 'password', $condition);
				if (!$check_pwd['status']) {
	                // Now verify current password
	                $condition['password'] = md5($this->input->post('current_password'));

	                $data_to_update = [
	                    'password' => $new_password_hash
	                ];

	                $update_res = $this->custom_db->update_record($table_name, $data_to_update, $condition);
					if ($update_res) {
	                    $this->session->set_flashdata(['message' => 'UL0010', 'type' => SUCCESS_MESSAGE]);
	                    refresh();
	                }
					$this->session->set_flashdata(['message' => 'UL0011', 'type' => ERROR_MESSAGE]);
	                refresh();
	            }
	            $this->session->set_flashdata(['message' => 'UL0012', 'type' => WARNING_MESSAGE]);
	            refresh();
	        }
	    }

	    $this->template->view('user/change_password', $data);
	}
	/**
	 * Balu A
	 * Add Traveller
	 */
	public function add_traveller()
	{
	    validate_user_login();

	    $post_data = $this->input->post();

	    if (!$this->is_valid_traveller_post_data($post_data)) {
	        return $this->redirect_with_query('user/profile');
	    }

	    $first_name = trim($post_data['traveller_first_name']);
	    $last_name = trim($post_data['traveller_last_name']);
	    $date_of_birth = date('Y-m-d', strtotime(trim($post_data['traveller_date_of_birth'])));
	    $email = trim($post_data['traveller_email']);

	    $user_traveller_det = [
	        'first_name' => $first_name,
	        'last_name' => $last_name,
	        'date_of_birth' => $date_of_birth,
	        'email' => $email,
	        'created_by_id' => $this->entity_user_id,
	        'created_datetime' => date('Y-m-d H:i:s')
	    ];

	    $check = $this->custom_db->single_table_records(
	        'user_traveller_details',
	        '*',
	        [
	            'created_by_id' => $this->entity_user_id,
	            'first_name' => $first_name,
	            'date_of_birth' => $date_of_birth
	        ]
	    );

	    if ($check['status'] == FAILURE_STATUS) {
	        $this->custom_db->insert_record('user_traveller_details', $user_traveller_det);
	    }

	    $this->redirect_with_query('user/profile');
	}

	private function is_valid_traveller_post_data(array $data): bool
	{
	    return valid_array($data)
	        && !empty($data['traveller_first_name'])
	        && !empty($data['traveller_date_of_birth'])
	        && isset($data['traveller_email'], $data['traveller_last_name']);
	}

	private function redirect_with_query(string $base_url): void
	{
	    $query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
	    redirect($base_url . $query);
	}

	/**
	 * Balu A
	 */
	public function update_traveller_details(): void
	{
	    $post_data = $this->input->post();

	    if (
	        valid_array($post_data)
	        && isset($post_data['origin'], $post_data['traveller_first_name'], $post_data['traveller_date_of_birth'], $post_data['traveller_email'], $post_data['traveller_last_name'])
	        && intval($post_data['origin']) > 0
	        && !empty($post_data['traveller_first_name'])
	        && !empty($post_data['traveller_date_of_birth'])
	    ) {
	        $user_traveller_det = [
	            'first_name' => trim($post_data['traveller_first_name']),
	            'last_name' => trim($post_data['traveller_last_name']),
	            'date_of_birth' => date('Y-m-d', strtotime(trim($post_data['traveller_date_of_birth']))),
	            'email' => trim($post_data['traveller_email']),
	            'passport_user_name' => trim($post_data['passport_user_name'] ?? ''),
	            'passport_nationality' => trim($post_data['passport_nationality'] ?? ''),
	            'passport_expiry_day' => trim($post_data['passport_expiry_day'] ?? ''),
	            'passport_expiry_month' => trim($post_data['passport_expiry_month'] ?? ''),
	            'passport_expiry_year' => trim($post_data['passport_expiry_year'] ?? ''),
	            'passport_number' => trim($post_data['passport_number'] ?? ''),
	            'passport_issuing_country' => trim($post_data['passport_issuing_country'] ?? ''),
	            'updated_by_id' => $this->entity_user_id,
	            'updated_datetime' => date('Y-m-d H:i:s')
	        ];

	        $this->custom_db->update_record(
	            'user_traveller_details',
	            $user_traveller_det,
	            ['origin' => intval($post_data['origin'])]
	        );
	    }
  $this->session->set_flashdata(['message' => 'AL004', 'type' => SUCCESS_MESSAGE]);
	    $query_string = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
	    redirect('user/profile' . $query_string);
	}
	/**
	 * Balu A
	 */
	function traveller_details():array
	{
		$data = array();
		$data['user_passport_visa_details'] = array();
		$data['traveller_details'] = array();
		//traveller details
		$traveller_details = $this->custom_db->single_table_records('user_traveller_details', '*', array('created_by_id' => $this->entity_user_id, 'user_id' => 0));
		if($traveller_details['status'] == true) {
			$data['traveller_details'] = $traveller_details['data'];
		}
		//User PassportVisa details
		$user_pass_details = $this->custom_db->single_table_records('user_traveller_details', '*', array('created_by_id' => $this->entity_user_id, 'user_id' => $this->entity_user_id));
		if($user_pass_details['status'] == true) {
			$data['traveller_details'] = $user_pass_details['data'][0];
		}
		return $data;
	}
}
