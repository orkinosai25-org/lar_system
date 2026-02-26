<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
		//$this->load->library('provab_sms');
		$this->load->model('db_cache_api');
		//$this->output->enable_profiler(TRUE);
	}
    public function index(): void
	{
		if (is_logged_in_user()) {
			redirect('menu/index');
		}
	}

	/**
	 * Generate my account view to user
	 */
	public function account(): void
	{
        $page_data=[];
        $config=[];
        $cond=[];
		// /echo "dfdf";die;
		$page_data['form_data'] = $this->input->post();
		$get_data = $this->input->get();
		$this->user_page = new Provab_Page_Loader('user_management');
		if (isset($get_data['uid']) == true) {
			$get_data['uid'] = intval($get_data['uid']);
			$user_id = intval($get_data['uid']);

			if (valid_array($page_data['form_data']) == false) {
				/*** EDIT DATA ***/
				//$cond = array(array('U.user_id', '=', intval($get_data['uid'])));

				$cond['user_id'] = intval($user_id);
				
				$edit_data = $this->user_model->get_agent_user($get_data['uid']);
            
				if (valid_array($edit_data) == true) {
					$page_data['form_data'] = $edit_data[0];
					
					// exit;
					$page_data['form_data']['uuid']= provab_decrypt($page_data['form_data']['uuid']);
					$page_data['form_data']['email'] =provab_decrypt($page_data['form_data']['email']);
				} else {
					redirect('security/log_event');
				}
			} elseif (valid_array($page_data['form_data']) == true && (check_default_edit_privilege($get_data['uid']) || super_privilege())) {

				/** AUTOMATE VALIDATOR **/
			
				$page_data['form_data']['language_preference'] = 'english';
				
				// $this->user_page->set_auto_validator();
				if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
							if( function_exists( "check_mime_image_type" ) ) {


							    if ( !check_mime_image_type( $_FILES['image']['tmp_name'] ) ) {
							    	echo "Please select the image files only (gif|jpg|png|jpeg)"; exit;
							    }
							}
									$config['upload_path'] = $this->template->domain_image_upload_path();
							$config['allowed_types'] = 'gif|jpg|png|jpeg';
							$config['file_name'] =  $_FILES['domain_logo']['name'];
							$config['max_size'] = '1000000';
							$config['max_width']  = '';
							$config['max_height']  = '';
							$config['remove_spaces']  = false;
							$user_id = $page_data['form_data']['user_id'];
							//UPDATE
							$temp_record = $this->custom_db->single_table_records('user', 'image', array('user_id' => $user_id));
							// $icon = $temp_record['data'][0]['image'];

							//DELETE OLD FILES
							// if (empty($icon) == false) {
							// 	$temp_profile_image = $this->template->domain_image_full_path($icon);//GETTING FILE PATH
							// 	if (file_exists($temp_profile_image)) {
							// 		unlink($temp_profile_image);
							// 	}
							// }

							//UPLOAD IMAGE
							$this->load->library('upload', $config);
							if ( ! $this->upload->do_upload('image')) {
								echo $this->upload->display_errors();
							} else {
								$image_data =  $this->upload->data();
							}
							$this->custom_db->update_record('user', array('image' => $image_data['file_name']), array('user_id' => $user_id));
							refresh();
						}
						// $page = $_SERVER['PHP_SELF'];
						// $sec = "10";
						// header("Refresh: $sec; url=$page");
				if ($this->form_validation->run()) {
					
					if (intval($get_data['uid']) === intval($page_data['form_data']['user_id']) && intval($page_data['form_data']['user_id']) > 0) {
						//Update Data -- LETS UNSET POSTED DATA
						unset($page_data['form_data']['FID']);
						unset($page_data['form_data']['email']);
						$this->custom_db->update_record('user', $page_data['form_data'], array('user_id' => $page_data['form_data']['user_id']));
						$this->application_logger->profile_update($page_data['form_data']['first_name'], $page_data['form_data']['first_name'].' Updated Profile Details', array('user_id' => $this->entity_user_id, 'uuid' => $this->entity_uuid));
						// set_update_message();
						//FILE UPLOAD

						
						
					} else {
						redirect('security/log_event');
					}
				} else {

				}
			}
			/** ADD DISABLED STATE **/
		
           $page_data['country_code_list'] = $this->db_cache_api->get_country_code_list();
			$this->template->view('user/account', $page_data);
		} else {
			redirect('security/log_event');
		}
	}


	/**
	 * Logout function for logout from account and unset all the session variables
	 */
	public function initilize_logout(): void{
		if (is_logged_in_user()) {
			$this->general_model->update_login_manager($this->session->userdata(LOGIN_POINTER));
			$this->session->unset_userdata(array(AUTH_USER_POINTER => '',LOGIN_POINTER => '') );
			// added by nithin for unseting the email username
			$this->session->unset_userdata('mail_user');
			redirect('general/index');
		}
	}
	/**
	 * oops page of application will be loaded here
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
        $condition=[];
        $page_data=[];
		$data=array();
		$get_data = $this->input->get();
		if(isset($get_data['uid'])) {
			$user_id = $get_data['uid'];//intval($this->encrypt->decode($get_data['uid']));
		} else {
			redirect("general/initilize_logout");
		}
		$page_data['form_data'] = $this->input->post();
		if(valid_array($page_data['form_data'])==TRUE) {
			// $this->current_page->set_auto_validator();
			$this->load->library('form_validation');
			$this->form_validation->set_rules('current_password', 'Current Password', 'required|min_length[5]|max_length[45]|callback_password_check');
			$this->form_validation->set_rules('new_password', 'New Password', 'matches[confirm_password]|min_length[5]|max_length[45]|required|callback_valid_password');
			$this->form_validation->set_rules('confirm_password', 'Confirm', 'callback_check_new_password');			
			if ($this->form_validation->run()) {
				$table_name="user";
				/** Checking New Password and Old Password Are Same OR Not **/
				$condition['password'] = provab_encrypt(md5(trim($this->input->post('new_password'))));
				$condition['user_id'] = $user_id;
				$check_pwd = $this->custom_db->single_table_records($table_name,'password',$condition);
				if(!$check_pwd['status']) {
					$condition['password'] = provab_encrypt(md5(trim($this->input->post('current_password'))));

					$condition['user_id'] = $user_id;
					$data['password'] = provab_encrypt(md5(trim($this->input->post('new_password'))));
					$update_res=$this->custom_db->update_record($table_name, $data, $condition);
					if($update_res)	{
						$this->session->set_flashdata(array('message' => ' Congratulations!! You are successfully changed paswword', 'type' => SUCCESS_MESSAGE, 'override_app_msg' => true));
						refresh();
					} else {
					$this->session->set_flashdata(array('message' => 'You are not changed paswword', 'type' => ERROR_MESSAGE, 'override_app_msg' => true));
						refresh();
						/*$data['msg'] = 'UL0011';
						 $data['type'] = ERROR_MESSAGE;*/
					}
				} else {
					$this->session->set_flashdata(array('message' => 'UL0012', 'type'=>WARNING_MESSAGE));
					refresh();
					//redirect('general/change_password?uid='.urlencode($get_data['uid']));
				}
			}
		}
		$this->template->view('user/change_password', $data);
	}

	
}
