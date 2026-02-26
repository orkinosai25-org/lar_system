<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab
 * @subpackage General
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */

class User extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('module_model');
		$this->load->library('provab_sms');
		//$this->output->enable_profiler(TRUE);
	}

	function create_default_domain(): void
	{
		include_once DOMAIN_CONFIG . 'default_domain_configuration.php';
	}

	/**
	 * index page of application will be loaded here
	 */
	function index(): void
	{
		if (is_logged_in_user()) {
			redirect('menu/index');
		}
	}

	function b2c_user(int $offset = 0): void
	{
		$user_type_form=[];
		$page_data = [];
		$config = [];
		$this->form_validation->set_message('is_unique', 'Email account already used');
		$page_data['form_data'] = $this->input->post();
		$get_data = $this->input->get();
		$condition = array();
		//CHECKING DOMAIN ORIGIN SET OR NOT
		if (isset($get_data['domain_origin']) == true && intval($get_data['domain_origin']) > 0) {
			$domain_origin = intval($get_data['domain_origin']);
		} else {
			$domain_origin = 0;
		}

		$page_data['eid'] = intval($get_data['eid']);
		if (valid_array($page_data['form_data']) == false && intval($page_data['eid']) > 0) {
			/**
			 * EDIT DATA
			 */
			$edit_data = $this->custom_db->single_table_records('user', '*', array('user_id' => $page_data['eid']));
			if (valid_array($edit_data['data']) == true) {
				$page_data['form_data'] = $edit_data['data'][0];
				$page_data['form_data']['email'] = provab_decrypt($page_data['form_data']['email']);
				$page_data['form_data']['uuid'] = provab_decrypt($page_data['form_data']['uuid']);
			} else {
				redirect('security/log_event?event=Invalid user edit');
			}
		} elseif (valid_array($page_data['form_data']) == true) {
			/** AUTOMATE VALIDATOR **/
			$page_data['form_data']['language_preference'] = 'english';
			$this->current_page->set_auto_validator();
			$this->load->library('form_validation');

			if ($this->form_validation->run()) {
				//LETS UNSET DATA WHICH ARE NOT NEEDED FOR DB


				/*$image_data = array();
				// FILE UPLOAD
				if (valid_array ( $_FILES ) == true and $_FILES ['image'] ['error'] == 0 and $_FILES ['image'] ['size'] > 0) {
					$img_name = 'Agent_logo-'.time();
					$config ['upload_path'] = $this->template->domain_image_upload_path ();
					$temp_file_name = $_FILES ['image'] ['name'];
					$config ['allowed_types'] = '*';
					$config ['file_name'] ='IMG-'.$img_name;
					$config ['max_size'] = '1000000';
					$config ['max_width'] = '';
					$config ['max_height'] = '';
					$config ['remove_spaces'] = false;
					// UPLOAD IMAGE
					$this->load->library ( 'upload', $config );
					$this->upload->initialize ( $config );
					if (! $this->upload->do_upload ( 'image' )) {
						echo $this->upload->display_errors ();
					} else {
						$image_data = $this->upload->data ();
					}
				}
				$page_data['form_data']['image'] = (empty($image_data ['file_name']) == false ? $image_data ['file_name'] : '');*/


				unset($page_data['form_data']['FID']);
				if (intval($page_data['form_data']['user_id']) > 0) {
					//Update Data
					$email = provab_encrypt($page_data['form_data']['email']);
					$this->custom_db->update_record('user', $page_data['form_data'], array('user_id' => $page_data['form_data']['user_id'], 'email' => $email));
					$this->application_logger->profile_update($this->entity_name, $this->entity_name . ' Updated ' . $page_data['form_data']['first_name'] . ' Profile Details', array('user_id' => $page_data['form_data']['user_id'], 'uuid' => $page_data['form_data']['uuid']));
					set_update_message();
				} elseif (intval($page_data['form_data']['user_id']) == 0) {
					if (isset($user_type_form)) {
						if ($user_type_form == B2C_USER) {
							$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_b2c_check');
						} elseif ($user_type_form == B2B_USER) {

							$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_b2b_check');
						} elseif ($user_type_form == SUB_ADMIN) {
							$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_subadmin_check');
						}
					}


					$this->form_validation->set_rules('password', 'New Password', 'matches[confirm_password]|min_length[5]|max_length[45]|required|callback_valid_password');
					$this->form_validation->set_rules('confirm_password', 'Confirm');
					//Insert Data
					//LETS UNSET DATA WHICH ARE NOT NEEDED FOR DB
					unset($page_data['form_data']['confirm_password']);
					if (intval($domain_origin) > 0) {
						$domain_list_fk = $domain_origin; //DOMAIN ADMIN CREATION BY PROVAB ADMIN
					} else if (get_domain_auth_id() > 0) {
						$domain_list_fk = get_domain_auth_id(); //DOMAIN USERS CREATION BY DOMAIN ADMIN
					} else {
						$domain_list_fk = 0;
					}
					$page_data['form_data']['domain_list_fk'] = $domain_list_fk; //DOMAIN ORIGIN
					$page_data['form_data']['email'] = provab_encrypt($page_data['form_data']['email']);

					$page_data['form_data']['user_name'] = $page_data['form_data']['email'];
					$page_data['form_data']['created_datetime'] = date('Y-m-d H:i:s');
					$page_data['form_data']['created_by_id'] = $this->entity_user_id;
					$page_data['form_data']['uuid'] = provab_encrypt(PROJECT_PREFIX . time());
					$page_data['form_data']['password'] = provab_encrypt(md5(trim($page_data['form_data']['password'])));

					$insert_id = $this->custom_db->insert_record('user', $page_data['form_data']);
					/*  B2B User Details Records */
					if ($page_data['form_data']['user_type'] == B2B_USER) {
						$page_data['b2b_data']['user_oid'] = $insert_id['insert_id'];
						$page_data['b2b_data']['balance'] = 0;
						$page_data['b2b_data']['created_datetime'] = date('Y-m-d H:i:s');
						$page_data['b2b_data']['created_by_id'] = $this->entity_user_id;
						$this->custom_db->insert_record('b2b_user_details', $page_data['b2b_data']);
					}
					$page_data['form_data']['email'] = provab_decrypt($page_data['form_data']['email']);
					$page_data['form_data']['uuid'] = provab_decrypt($page_data['form_data']['uuid']);
					/* B2B User Details Ends */
					$this->application_logger->registration($this->entity_name, $this->entity_name . ' Registered ' . $page_data['form_data']['email'] . ' From Admin Portal', $this->entity_user_id, array('user_id' => $insert_id['insert_id'], 'uuid' => $page_data['form_data']['uuid']));
					set_insert_message();
				} else {
					redirect('security/log_event?event=User Invalid CRUD');
				}
				if (intval($get_data['eid']) > 0) {
					$temp_query_string = str_replace('&eid=' . intval($get_data['eid']), '', $_SERVER['QUERY_STRING']);
				} else {
					$temp_query_string = $_SERVER['QUERY_STRING'];
				}
				redirect('user/' . __FUNCTION__ . '?' . $temp_query_string);
			}
		}


		//IF DOMAIN ORIGIN IS SET, THEN GET ONLY THAT DOMAIN ADMIN DETAILS
		if (intval($domain_origin) > 0) {
			$condition = array(
				array('U.domain_list_fk', '=', $domain_origin),
				array('U.user_type', '=', ADMIN)
			);
		} else if (valid_array($get_data) == true) {
			$condition = array();
			if (isset($get_data['user_status']) == true) {
				$condition[] = array('U.status', '=', $this->db->escape(intval($get_data['user_status'])));
				$condition[] = array('U.user_type', ' IN (', intval(4), ')');
			}
			if (isset($get_data['uuid']) == true && empty($get_data['uuid']) == false) {
				$condition[] = array('U.uuid', ' like ', $this->db->escape('%' . provab_encrypt($get_data['uuid']) . '%'));
			}
			if (isset($get_data['email']) == true && empty($get_data['email']) == false) {
				$condition[] = array('U.email', ' like ', $this->db->escape('%' . provab_encrypt($get_data['email']) . '%'));
			}
			if (isset($get_data['phone']) == true && empty($get_data['phone']) == false) {
				$condition[] = array('U.phone', ' like ', $this->db->escape('%' . $get_data['phone'] . '%'));
			}
			if (isset($get_data['created_datetime_from']) == true && empty($get_data['created_datetime_from']) == false) {
				$condition[] = array('U.created_datetime', '>=', $this->db->escape(db_current_datetime($get_data['created_datetime_from'])));
			}
			if (isset($get_data['filter']) == true && isset($get_data['q']) == true) {
				switch ($get_data['filter']) {
					case 'user_type':
						//Get Users Based on User Types(Active/Inactive Users)
						if (intval($get_data['q']) > 0) {
							$condition[] = array('U.user_type', ' IN (', intval($get_data['q']), ')');
						}
						break;
				}
			}
		}
		/** TABLE PAGINATION */
		$total_records = $this->user_model->get_domain_user_list($condition, true);
		$page_data['table_data'] = $this->user_model->get_domain_user_list($condition, false, $offset, RECORDS_RANGE_1);
		//echo $this->db->last_query();exit;
		//CHECKING DOMAIN ADMIN EXISTS, IF EXISTS DISABLE ADD FORM IN THE VIEW
		if (intval($domain_origin) > 0 && valid_array($page_data['table_data'])) {
			$page_data['domain_admin_exists'] = true;
		} else {
			$page_data['domain_admin_exists'] = false;
		}
		//debug($get_data);exit;
		if (!empty($get_data['user_status'])) {
			$page_data['user_status'] = $get_data['user_status'];
		}

		$this->load->library('pagination');
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['base_url'] = base_url() . 'index.php/user/b2c_user/';
		$config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
		$config['total_rows'] = $total_records->total;
		$config['per_page'] = RECORDS_RANGE_1;
		$this->pagination->initialize($config);
		$page_data['search_params'] = $get_data;
		/** TABLE PAGINATION */
		//Get Online User Count
		$this->template->view('user/user_management', $page_data);
	}
	/**
	 * Separate Form Generations are used for  b2b alone
	 * add/edit are done in b2b_user/b2b_user_edit forms --__
	 * 	____________________________________________________/
	 *  \__-->in b2b_user_management in ->page_configuration
	 * @param number $offset
	 */
	function ultralux_user(int $offset = 0): void
	{
		$user_type_form=[];
		$page_data = [];
		$config = [];
		$e_condition = [];
		$page_data['form_data'] = $this->input->post();
		$this->current_page = new Provab_Page_Loader('ultralux_user_management');
		$get_data = $this->input->get();
		$condition = array();
		$page_data['eid'] = intval($get_data['eid']);
		if (valid_array($page_data['form_data']) == false && intval($page_data['eid']) > 0) {
			/**
			 * EDIT DATA
			 */
			$e_condition[] = array('U.user_id', '=', $page_data['eid']);
			$edit_data = $this->user_model->get_domain_user_list($e_condition, false, 0, 1);
			if (valid_array($edit_data) == true) {
				$page_data['form_data'] = $edit_data[0];
				$page_data['form_data']['email'] = provab_decrypt($page_data['form_data']['email']);
				$page_data['form_data']['uuid'] = provab_decrypt($page_data['form_data']['uuid']);
			} else {
				redirect('security/log_event?event=Invalid user edit');
			}
		} elseif (valid_array($page_data['form_data']) == true) {
			/** AUTOMATE VALIDATOR **/
			$page_data['form_data']['language_preference'] = 'english';
			$this->current_page->set_auto_validator();
			$this->load->library('form_validation');

			if ($this->form_validation->run()) {
				$image_data = array();
				// FILE UPLOAD
				if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
					$img_name = 'Agent_logo-' . time();
					if (function_exists("check_mime_image_type")) {
						if (!check_mime_image_type($_FILES['image']['tmp_name'])) {
							echo "Please select the image files only (gif|jpg|png|jpeg)";
							exit;
						}
					}
					$config['upload_path'] = $this->template->domain_image_upload_path();
					$config['allowed_types'] = 'gif|jpg|png|jpeg';
					$config['file_name'] = 'IMG-' . $img_name;
					$config['max_size'] = MAX_DOMAIN_LOGO_SIZE;
					$config['max_width']  = MAX_DOMAIN_LOGO_WIDTH;
					$config['max_height']  = MAX_DOMAIN_LOGO_HEIGHT;
					$config['remove_spaces'] = false;
					// UPLOAD IMAGE
					$this->load->library('upload', $config);
					$this->upload->initialize($config);
					if (! $this->upload->do_upload('image')) {
						echo $this->upload->display_errors();
					} else {
						$image_data = $this->upload->data();
					}
				}
				$page_data['form_data']['image'] = (empty($image_data['file_name']) == false ? $image_data['file_name'] : '');
				//LETS UNSET DATA WHICH ARE NOT NEEDED FOR DB
				unset($page_data['form_data']['FID']);
				if (intval($page_data['form_data']['user_id']) > 0) {

					$email = provab_encrypt($page_data['form_data']['email']);
					$page_data['form_data']['email'] = $email;
					unset($page_data['form_data']['email']);

					// debug($page_data);exit;
					//Update Data
					$this->custom_db->update_record('user', $page_data['form_data'], array('user_id' => $page_data['form_data']['user_id'], 'email' => $email));
					$this->application_logger->profile_update($this->entity_name, $this->entity_name . ' Updated ' . $page_data['form_data']['first_name'] . ' Profile Details', array('user_id' => $page_data['form_data']['user_id'], 'uuid' => $page_data['form_data']['uuid']));
					set_update_message();
				} elseif (intval($page_data['form_data']['user_id']) == 0) {
					if (isset($user_type_form)) {
						if ($user_type_form == B2C_USER) {
							$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_b2c_check');
						} elseif ($user_type_form == B2B_USER) {

							$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_b2b_check');
						} elseif ($user_type_form == SUB_ADMIN) {
							$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_subadmin_check');
						}
					}


					$this->form_validation->set_rules('password', 'New Password', 'matches[confirm_password]|min_length[5]|max_length[45]|required|callback_valid_password');
					$this->form_validation->set_rules('confirm_password', 'Confirm');

					//Insert Data
					//LETS UNSET DATA WHICH ARE NOT NEEDED FOR DB
					unset($page_data['form_data']['confirm_password']);
					$domain_list_fk = get_domain_auth_id(); //DOMAIN USERS CREATION BY DOMAIN ADMIN
					$page_data['form_data']['domain_list_fk'] = $domain_list_fk; //DOMAIN ORIGIN
					$page_data['form_data']['email'] = provab_encrypt($page_data['form_data']['email']);

					$page_data['form_data']['user_name'] = $page_data['form_data']['email'];
					$page_data['form_data']['created_datetime'] = date('Y-m-d H:i:s');
					$page_data['form_data']['created_by_id'] = $this->entity_user_id;
					$page_data['form_data']['uuid'] = provab_encrypt(PROJECT_PREFIX . time());
					$page_data['form_data']['password'] = provab_encrypt(md5(trim($page_data['form_data']['password'])));
					$insert_id = $this->custom_db->insert_record('user', $page_data['form_data']);
					/*  B2B User Details Records */
					/*get the admin currency*/
					$get_admin_currency = $this->custom_db->single_table_records('domain_list', 'currency_converter_fk', array('domain_key' => CURRENT_DOMAIN_KEY));
					$page_data['b2b_data']['currency_converter_fk'] = $get_admin_currency['data'][0]['currency_converter_fk'];

					$page_data['b2b_data']['user_oid'] = $insert_id['insert_id'];
					$page_data['b2b_data']['balance'] = 0;
					$page_data['b2b_data']['created_datetime'] = date('Y-m-d H:i:s');
					$page_data['b2b_data']['created_by_id'] = $this->entity_user_id;
					$this->custom_db->insert_record('b2b_user_details', $page_data['b2b_data']);
					/* B2B User Details Ends */
					$page_data['form_data']['email'] = provab_decrypt($page_data['form_data']['email']);
					$page_data['form_data']['uuid'] = provab_decrypt($page_data['form_data']['uuid']);

					$this->application_logger->registration($this->entity_name, $this->entity_name . ' Registered ' . $page_data['form_data']['email'] . ' From Admin Portal', $this->entity_user_id, array('user_id' => $insert_id['insert_id'], 'uuid' => $page_data['form_data']['uuid']));
					set_insert_message();
				} else {
					redirect('security/log_event?event=User Invalid CRUD');
				}
				if (intval($get_data['eid']) > 0) {
					$temp_query_string = str_replace('&eid=' . intval($get_data['eid']), '', $_SERVER['QUERY_STRING']);
				} else {
					$temp_query_string = $_SERVER['QUERY_STRING'];
				}
				redirect('user/' . __FUNCTION__ . '?' . $temp_query_string);
			}
		}


		//IF DOMAIN ORIGIN IS SET, THEN GET ONLY THAT DOMAIN ADMIN DETAILS
		if (isset($get_data['user_status']) == true) {
			$condition[] = array('U.status', '=', $this->db->escape(intval($get_data['user_status'])));
			$condition[] = array('U.user_type', ' IN (', intval(8), ')');
		}
		if (isset($get_data['agency_name']) == true && empty($get_data['agency_name']) == false) {
			$condition[] = array('U.agency_name', ' like ', $this->db->escape('%' . $get_data['agency_name'] . '%'));
		}
		if (isset($get_data['uuid']) == true && empty($get_data['uuid']) == false) {
			$condition[] = array('U.uuid', ' like ', $this->db->escape('%' . provab_encrypt($get_data['uuid']) . '%'));
		}
		if (isset($get_data['pan_number']) == true && empty($get_data['pan_number']) == false) {
			$condition[] = array('U.pan_number', ' like ', $this->db->escape('%' . $get_data['pan_number'] . '%'));
		}
		if (isset($get_data['email']) == true && empty($get_data['email']) == false) {
			$condition[] = array('U.email', ' like ', $this->db->escape('%' . provab_encrypt($get_data['email']) . '%'));
		}
		if (isset($get_data['phone']) == true && empty($get_data['phone']) == false) {
			$condition[] = array('U.phone', ' like ', $this->db->escape('%' . $get_data['phone'] . '%'));
		}
		if (isset($get_data['created_datetime_from']) == true && empty($get_data['created_datetime_from']) == false) {
			$condition[] = array('U.created_datetime', '>=', $this->db->escape(db_current_datetime($get_data['created_datetime_from'])));
		}
		if (
			isset($get_data['filter']) == true && $get_data['filter'] == 'search_agent' &&
			isset($get_data['filter_agency']) == true && empty($get_data['filter_agency']) == false
		) {
			$filter_agency = trim($get_data['filter_agency']);
			//Search Filter
			$condition[] = array('U.agency_name', ' like ', $this->db->escape('%' . $filter_agency . '%'));
		}

		//get domain country and city
		$temp_details = $this->custom_db->single_table_records('domain_list', '*', array('origin' => get_domain_auth_id()));
		$page_data['form_data']['api_country_list'] = $temp_details['data'][0];
		$condition[] = array('U.user_type', ' IN (', ULTRALUX_USER, ')');

		/** TABLE PAGINATION */
//debug($condition);
		$page_data['table_data'] = $this->user_model->b2b_user_list($condition, false, $offset, RECORDS_RANGE_3);
		//echo $this->db->last_query();
		//debug($page_data['table_data']);exit;
		$total_records = $this->user_model->b2b_user_list($condition, true);

		$this->load->library('pagination');
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['base_url'] = base_url() . 'index.php/user/ultralux_user/';
		$config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
		$config['total_rows'] = $total_records->total;
		$config['per_page'] = RECORDS_RANGE_3;
		$this->pagination->initialize($config);
		$page_data['search_params'] = $get_data;
		$page_data['total_rows'] = $total_records->total;

		/** TABLE PAGINATION */
		//Get Online User Count

		$this->template->view('user/ultralux_user_management', $page_data);
	}
	/**
	 * Separate Form Generations are used for  b2b alone
	 * add/edit are done in b2b_user/b2b_user_edit forms --__
	 * 	____________________________________________________/
	 *  \__-->in b2b_user_management in ->page_configuration
	 * @param number $offset
	 */
	function b2b_user(int $offset = 0): void
	{
		$user_type_form=[];
		$page_data = [];
		$config = [];
		$e_condition = [];
		$page_data['form_data'] = $this->input->post();
		$this->current_page = new Provab_Page_Loader('b2b_user_management');
		$get_data = $this->input->get();
		$condition = array();
		$page_data['eid'] = intval($get_data['eid']);
		if (valid_array($page_data['form_data']) == false && intval($page_data['eid']) > 0) {
			/**
			 * EDIT DATA
			 */
			$e_condition[] = array('U.user_id', '=', $page_data['eid']);
			$edit_data = $this->user_model->get_domain_user_list($e_condition, false, 0, 1);
			if (valid_array($edit_data) == true) {
				$page_data['form_data'] = $edit_data[0];
				$page_data['form_data']['email'] = provab_decrypt($page_data['form_data']['email']);
				$page_data['form_data']['uuid'] = provab_decrypt($page_data['form_data']['uuid']);
			} else {
				redirect('security/log_event?event=Invalid user edit');
			}
		} elseif (valid_array($page_data['form_data']) == true) {
			/** AUTOMATE VALIDATOR **/
			$page_data['form_data']['language_preference'] = 'english';
			$this->current_page->set_auto_validator();
			$this->load->library('form_validation');

			if ($this->form_validation->run()) {
				$image_data = array();
				// FILE UPLOAD
				if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
					$img_name = 'Agent_logo-' . time();
					if (function_exists("check_mime_image_type")) {
						if (!check_mime_image_type($_FILES['image']['tmp_name'])) {
							echo "Please select the image files only (gif|jpg|png|jpeg)";
							exit;
						}
					}
					$config['upload_path'] = $this->template->domain_image_upload_path();
					$config['allowed_types'] = 'gif|jpg|png|jpeg';
					$config['file_name'] = 'IMG-' . $img_name;
					$config['max_size'] = MAX_DOMAIN_LOGO_SIZE;
					$config['max_width']  = MAX_DOMAIN_LOGO_WIDTH;
					$config['max_height']  = MAX_DOMAIN_LOGO_HEIGHT;
					$config['remove_spaces'] = false;
					// UPLOAD IMAGE
					$this->load->library('upload', $config);
					$this->upload->initialize($config);
					if (! $this->upload->do_upload('image')) {
						echo $this->upload->display_errors();
					} else {
						$image_data = $this->upload->data();
					}
				}
				$page_data['form_data']['image'] = (empty($image_data['file_name']) == false ? $image_data['file_name'] : '');
				//LETS UNSET DATA WHICH ARE NOT NEEDED FOR DB
				unset($page_data['form_data']['FID']);
				if (intval($page_data['form_data']['user_id']) > 0) {

					$email = provab_encrypt($page_data['form_data']['email']);
					$page_data['form_data']['email'] = $email;
					unset($page_data['form_data']['email']);

					// debug($page_data);exit;
					//Update Data
					$this->custom_db->update_record('user', $page_data['form_data'], array('user_id' => $page_data['form_data']['user_id'], 'email' => $email));
					$this->application_logger->profile_update($this->entity_name, $this->entity_name . ' Updated ' . $page_data['form_data']['first_name'] . ' Profile Details', array('user_id' => $page_data['form_data']['user_id'], 'uuid' => $page_data['form_data']['uuid']));
					set_update_message();
				} elseif (intval($page_data['form_data']['user_id']) == 0) {
					if (isset($user_type_form)) {
						if ($user_type_form == B2C_USER) {
							$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_b2c_check');
						} elseif ($user_type_form == B2B_USER) {

							$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_b2b_check');
						} elseif ($user_type_form == SUB_ADMIN) {
							$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_subadmin_check');
						}
					}


					$this->form_validation->set_rules('password', 'New Password', 'matches[confirm_password]|min_length[5]|max_length[45]|required|callback_valid_password');
					$this->form_validation->set_rules('confirm_password', 'Confirm');

					//Insert Data
					//LETS UNSET DATA WHICH ARE NOT NEEDED FOR DB
					unset($page_data['form_data']['confirm_password']);
					$domain_list_fk = get_domain_auth_id(); //DOMAIN USERS CREATION BY DOMAIN ADMIN
					$page_data['form_data']['domain_list_fk'] = $domain_list_fk; //DOMAIN ORIGIN
					$page_data['form_data']['email'] = provab_encrypt($page_data['form_data']['email']);

					$page_data['form_data']['user_name'] = $page_data['form_data']['email'];
					$page_data['form_data']['created_datetime'] = date('Y-m-d H:i:s');
					$page_data['form_data']['created_by_id'] = $this->entity_user_id;
					$page_data['form_data']['uuid'] = provab_encrypt(PROJECT_PREFIX . time());
					$page_data['form_data']['password'] = provab_encrypt(md5(trim($page_data['form_data']['password'])));
					$insert_id = $this->custom_db->insert_record('user', $page_data['form_data']);
					/*  B2B User Details Records */
					/*get the admin currency*/
					$get_admin_currency = $this->custom_db->single_table_records('domain_list', 'currency_converter_fk', array('domain_key' => CURRENT_DOMAIN_KEY));
					$page_data['b2b_data']['currency_converter_fk'] = $get_admin_currency['data'][0]['currency_converter_fk'];

					$page_data['b2b_data']['user_oid'] = $insert_id['insert_id'];
					$page_data['b2b_data']['balance'] = 0;
					$page_data['b2b_data']['created_datetime'] = date('Y-m-d H:i:s');
					$page_data['b2b_data']['created_by_id'] = $this->entity_user_id;
					$this->custom_db->insert_record('b2b_user_details', $page_data['b2b_data']);
					/* B2B User Details Ends */
					$page_data['form_data']['email'] = provab_decrypt($page_data['form_data']['email']);
					$page_data['form_data']['uuid'] = provab_decrypt($page_data['form_data']['uuid']);

					$this->application_logger->registration($this->entity_name, $this->entity_name . ' Registered ' . $page_data['form_data']['email'] . ' From Admin Portal', $this->entity_user_id, array('user_id' => $insert_id['insert_id'], 'uuid' => $page_data['form_data']['uuid']));
					set_insert_message();
				} else {
					redirect('security/log_event?event=User Invalid CRUD');
				}
				if (intval($get_data['eid']) > 0) {
					$temp_query_string = str_replace('&eid=' . intval($get_data['eid']), '', $_SERVER['QUERY_STRING']);
				} else {
					$temp_query_string = $_SERVER['QUERY_STRING'];
				}
				redirect('user/' . __FUNCTION__ . '?' . $temp_query_string);
			}
		}


		//IF DOMAIN ORIGIN IS SET, THEN GET ONLY THAT DOMAIN ADMIN DETAILS
		if (isset($get_data['user_status']) == true) {
			$condition[] = array('U.status', '=', $this->db->escape(intval($get_data['user_status'])));
			$condition[] = array('U.user_type', ' IN (', intval(3), ')');
		}
		if (isset($get_data['agency_name']) == true && empty($get_data['agency_name']) == false) {
			$condition[] = array('U.agency_name', ' like ', $this->db->escape('%' . $get_data['agency_name'] . '%'));
		}
		if (isset($get_data['uuid']) == true && empty($get_data['uuid']) == false) {
			$condition[] = array('U.uuid', ' like ', $this->db->escape('%' . provab_encrypt($get_data['uuid']) . '%'));
		}
		if (isset($get_data['pan_number']) == true && empty($get_data['pan_number']) == false) {
			$condition[] = array('U.pan_number', ' like ', $this->db->escape('%' . $get_data['pan_number'] . '%'));
		}
		if (isset($get_data['email']) == true && empty($get_data['email']) == false) {
			$condition[] = array('U.email', ' like ', $this->db->escape('%' . provab_encrypt($get_data['email']) . '%'));
		}
		if (isset($get_data['phone']) == true && empty($get_data['phone']) == false) {
			$condition[] = array('U.phone', ' like ', $this->db->escape('%' . $get_data['phone'] . '%'));
		}
		if (isset($get_data['created_datetime_from']) == true && empty($get_data['created_datetime_from']) == false) {
			$condition[] = array('U.created_datetime', '>=', $this->db->escape(db_current_datetime($get_data['created_datetime_from'])));
		}
		if (
			isset($get_data['filter']) == true && $get_data['filter'] == 'search_agent' &&
			isset($get_data['filter_agency']) == true && empty($get_data['filter_agency']) == false
		) {
			$filter_agency = trim($get_data['filter_agency']);
			//Search Filter
			$condition[] = array('U.agency_name', ' like ', $this->db->escape('%' . $filter_agency . '%'));
		}

		//get domain country and city
		$temp_details = $this->custom_db->single_table_records('domain_list', '*', array('origin' => get_domain_auth_id()));
		$page_data['form_data']['api_country_list'] = $temp_details['data'][0];
		$condition[] = array('U.user_type', ' IN (', B2B_USER, ')');

		/** TABLE PAGINATION */

		$page_data['table_data'] = $this->user_model->b2b_user_list($condition, false, $offset, RECORDS_RANGE_3);
		// debug($page_data['table_data']);exit;
		$total_records = $this->user_model->b2b_user_list($condition, true);

		$this->load->library('pagination');
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['base_url'] = base_url() . 'index.php/user/b2b_user/';
		$config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
		$config['total_rows'] = $total_records->total;
		$config['per_page'] = RECORDS_RANGE_3;
		$this->pagination->initialize($config);
		$page_data['search_params'] = $get_data;
		$page_data['total_rows'] = $total_records->total;

		/** TABLE PAGINATION */
		//Get Online User Count

		$this->template->view('user/b2b_user_management', $page_data);
	}

	/**
	 * manage user account in the system :p
	 */
	function user_management(int $offset = 0): void
	{
		$user_type_form=[];
		$page_data = [];
		$config = [];
		$page_data['form_data'] = $this->input->post();
		$get_data = $this->input->get();
		// debug($get_data);exit;
		$condition = array();
		//CHECKING DOMAIN ORIGIN SET OR NOT
		if (isset($get_data['domain_origin']) == true && intval($get_data['domain_origin']) > 0) {
			$domain_origin = intval($get_data['domain_origin']);
		} else {
			$domain_origin = 0;
		}

		$page_data['eid'] = intval($get_data['eid']);
		if (valid_array($page_data['form_data']) == false && intval($page_data['eid']) > 0) {
			/**
			 * EDIT DATA
			 */
			$edit_data = $this->custom_db->single_table_records('user', '*', array('user_id' => $page_data['eid']));
			if (valid_array($edit_data['data']) == true) {
				$page_data['form_data'] = $edit_data['data'][0];
				$page_data['form_data']['email'] = provab_decrypt($page_data['form_data']['email']);
			} else {
				redirect('security/log_event?event=Invalid user edit');
			}
		} elseif (valid_array($page_data['form_data']) == true) {
			/** AUTOMATE VALIDATOR **/
			$page_data['form_data']['language_preference'] = 'english';
			$this->current_page->set_auto_validator();
			$this->load->library('form_validation');


			if ($this->form_validation->run()) {
				//LETS UNSET DATA WHICH ARE NOT NEEDED FOR DB
				unset($page_data['form_data']['FID']);
				if (intval($page_data['form_data']['user_id']) > 0) {
					// debug($page_data['form_data']);exit;
					//Update Data
					$email = provab_encrypt($page_data['form_data']['email']);
					unset($page_data['form_data']['email']);
					// debug($page_data);exit;
					$this->custom_db->update_record('user', $page_data['form_data'], array('user_id' => $page_data['form_data']['user_id'], 'email' => $email));
					$this->application_logger->profile_update($this->entity_name, $this->entity_name . ' Updated ' . $page_data['form_data']['first_name'] . ' Profile Details', array('user_id' => $page_data['form_data']['user_id'], 'uuid' => $page_data['form_data']['uuid']));
					set_update_message();
				} elseif (intval($page_data['form_data']['user_id']) == 0) {
					if (isset($user_type_form)) {
						if ($user_type_form == B2C_USER) {
							$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_b2c_check');
						} elseif ($user_type_form == B2B_USER) {

							$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_b2b_check');
						} elseif ($user_type_form == SUB_ADMIN) 
					{
							$this->form_validation->set_rules('email', 'Email', 'valid_email|required|max_length[80]|callback_useremail_subadmin_check');
						}
					}


					$this->form_validation->set_rules('password', 'New Password', 'matches[confirm_password]|min_length[5]|max_length[45]|required|callback_valid_password');
					$this->form_validation->set_rules('confirm_password', 'Confirm');
					//Insert Data
					//LETS UNSET DATA WHICH ARE NOT NEEDED FOR DB
					unset($page_data['form_data']['confirm_password']);
					if (intval($domain_origin) > 0) {
						$domain_list_fk = $domain_origin; //DOMAIN ADMIN CREATION BY PROVAB ADMIN
					} else if (get_domain_auth_id() > 0) {
						$domain_list_fk = get_domain_auth_id(); //DOMAIN USERS CREATION BY DOMAIN ADMIN
					} else {
						$domain_list_fk = 0;
					}

					$page_data['form_data']['domain_list_fk'] = $domain_list_fk; //DOMAIN ORIGIN
					$page_data['form_data']['created_datetime'] = date('Y-m-d H:i:s');
					$page_data['form_data']['created_by_id'] = $this->entity_user_id;
					$page_data['form_data']['uuid'] = provab_encrypt(PROJECT_PREFIX . time());
					$page_data['form_data']['email'] = provab_encrypt($page_data['form_data']['email']);
					$page_data['form_data']['user_name'] = $page_data['form_data']['email'];

					$page_data['form_data']['password'] = provab_encrypt(md5(trim($page_data['form_data']['password'])));

					$insert_id = $this->custom_db->insert_record('user', $page_data['form_data']);
					/*  B2B User Details Records */
					if ($page_data['form_data']['user_type'] == B2B_USER) {
						$page_data['b2b_data']['user_oid'] = $insert_id['insert_id'];
						$page_data['b2b_data']['balance'] = 0;
						$page_data['b2b_data']['created_datetime'] = date('Y-m-d H:i:s');
						$page_data['b2b_data']['created_by_id'] = $this->entity_user_id;
						$this->custom_db->insert_record('b2b_user_details', $page_data['b2b_data']);
					}
					/* B2B User Details Ends */
					$page_data['form_data']['uuid'] = provab_decrypt($page_data['form_data']['uuid']);
					$page_data['form_data']['email'] = provab_decrypt($page_data['form_data']['email']);

					$this->application_logger->registration($this->entity_name, $this->entity_name . ' Registered ' . $page_data['form_data']['email'] . ' From Admin Portal', $this->entity_user_id, array('user_id' => $insert_id['insert_id'], 'uuid' => $page_data['form_data']['uuid']));
					set_insert_message();
				} else {
					redirect('security/log_event?event=User Invalid CRUD');
				}
				if (intval($get_data['eid']) > 0) {
					$temp_query_string = str_replace('&eid=' . intval($get_data['eid']), '', $_SERVER['QUERY_STRING']);
				} else {
					$temp_query_string = $_SERVER['QUERY_STRING'];
				}
				redirect('user/' . __FUNCTION__ . '?' . $temp_query_string);
			}
		}

		// debug($get_data);exit;
		//IF DOMAIN ORIGIN IS SET, THEN GET ONLY THAT DOMAIN ADMIN DETAILS
		if (intval($domain_origin) > 0) {
			$condition = array(
				array('U.domain_list_fk', '=', $domain_origin),
				array('U.user_type', '=', ADMIN)
			);
		} else if (valid_array($get_data) == true) {
			$condition = array();
			if (isset($get_data['user_status']) == true) {
				$condition[] = array('U.status', '=', $this->db->escape(intval($get_data['user_status'])));
				$condition[] = array('U.user_type', ' IN (', intval(2), ')');
			}
			if (isset($get_data['uuid']) == true && empty($get_data['uuid']) == false) {
				$condition[] = array('U.uuid', ' like ', $this->db->escape('%' . provab_encrypt($get_data['uuid']) . '%'));
			}
			if (isset($get_data['email']) == true && empty($get_data['email']) == false) {
				$condition[] = array('U.email', ' like ', $this->db->escape('%' . provab_encrypt($get_data['email']) . '%'));
			}
			if (isset($get_data['phone']) == true && empty($get_data['phone']) == false) {
				$condition[] = array('U.phone', ' like ', $this->db->escape('%' . $get_data['phone'] . '%'));
			}
			if (isset($get_data['created_datetime_from']) == true && empty($get_data['created_datetime_from']) == false) {
				$condition[] = array('U.created_datetime', '>=', $this->db->escape(db_current_datetime($get_data['created_datetime_from'])));
			}
			if (isset($get_data['filter']) == true && isset($get_data['q']) == true) {

				$condition = array();
				if (isset($get_data['user_status']) == true) {

					$condition[] = array('U.status', '=', intval($get_data['user_status']));
					$page_data['user_status'] = intval($get_data['user_status']);
				}
				switch ($get_data['filter']) {
					case 'user_type':
						//Get Users Based on User Types(Active/Inactive Users)
						if (intval($get_data['q']) > 0) {
							$condition[] = array('U.user_type', ' IN (', intval($get_data['q']), ')');
						}
						break;
				}
			}
		}
		// debug($condition);exit;
		/** TABLE PAGINATION */
		$total_records = $this->user_model->get_domain_user_list($condition, true);
		$page_data['table_data'] = $this->user_model->get_domain_user_list($condition, false, $offset, RECORDS_RANGE_1);

		//CHECKING DOMAIN ADMIN EXISTS, IF EXISTS DISABLE ADD FORM IN THE VIEW
		if (intval($domain_origin) > 0 && valid_array($page_data['table_data'])) {
			$page_data['domain_admin_exists'] = true;
		} else {
			$page_data['domain_admin_exists'] = false;
		}
		$this->load->library('pagination');
		$config['base_url'] = base_url() . 'index.php/user/user_management';
		$config['total_rows'] = $total_records->total;
		$config['per_page'] = RECORDS_RANGE_1;
		$this->pagination->initialize($config);
		$page_data['search_params'] = $get_data;
		/** TABLE PAGINATION */
		//Get Online User Count
		$this->template->view('user/user_management', $page_data);
	}

	/**
	 * Activate User Account
	 */
	function activate_account(int $user_id, string $uuid): void
	{
		$cond = [];
		$data = [];
		$cond['user_id'] = intval($user_id);
		$cond['uuid'] = $uuid;
		$data['status'] = ACTIVE;
		$info = $this->user_model->update_user_data($data, $cond);
		if ($info['status'] == SUCCESS_STATUS) {
			$task = 'activate';
			$this->account_status($info, $task);
		}
		exit;
		/*redirect(base_url().'user/user_management?filter=user_type&q='.$info['data']['user_type']);*/
	}

	/**
	 * Deactiavte User Account
	 */
	function deactivate_account(int $user_id, string $uuid): void
	{
		$cond = [];
		$data = [];
		$cond['user_id'] = intval($user_id);
		$cond['uuid'] = $uuid;
		$data['status'] = INACTIVE;
		$info = $this->user_model->update_user_data($data, $cond);
		if ($info['status'] == SUCCESS_STATUS) {
			$task = 'deactivate';
			$this->account_status($info, $task);
		}
		exit;
		/*redirect(base_url().'user/user_management?filter=user_type&q='.$info['data']['user_type']);*/
	}

	/**
	 * Send Account Status Email To User
	 * @param $data
	 */
	function account_status(array $data, string $task): void
	{
		//echo APP_ROOT_DIR;
		//exit;
		if ($data['data']['user_type'] == B2C_USER) {
			$module_name = 'B2C';
		} else if ($data['data']['user_type'] == B2B_USER) {
			$module_name = 'B2B';
		}
		if ($task == 'deactivate') {
			//Sms config & Checkpoint
			if (active_sms_checkpoint('account_deactivate')) {
				$msg = "Dear " . $data['data']['first_name'] . " Your '.$module_name.' Account Has Been Deactivated. Details are sent to your email id";
				$msg = urlencode($msg);
				$this->provab_sms->send_msg($data['data']['phone'], $msg);
			} //sms will be sent

			//Email Configuration
			$mail_template = $this->template->isolated_view('user/account_deactivation_template', $data['data']);
			$email = provab_decrypt($data['data']['email']);
			$this->load->library('provab_mailer');

			if ($data['data']['user_type'] == '3') {
				$this->provab_mailer->send_mail($email, 'B2B Account Deactivation', $mail_template);
			} else {
				$this->provab_mailer->send_mail($email, 'B2C Account Deactivation', $mail_template);
			}
			//$this->provab_mailer->send_mail($email, $module_name.' Account Deactivation', $mail_template);
			//Email Will be sent

		} else {
			//Sms config & Checkpoint
			if (active_sms_checkpoint('account_activate')) {
				$msg = "Dear " . $data['data']['first_name'] . " Your '.$module_name.' Account Has Been Activated. Details are sent to your email id";
				$msg = urlencode($msg);
				$this->provab_sms->send_msg($data['data']['phone'], $msg);
			} //sms will be sent

			//Email Configuration
			//$mail_template = $this->template->isolated_view('user/account_activation_template', $data['data']);
			$email = provab_decrypt($data['data']['email']);
			$this->load->library('provab_mailer');
			//$this->provab_mailer->send_mail($email, $module_name.' Account Activation', $mail_template);
			$data['data']['user_name'] = provab_decrypt($data['data']['user_name']);
			if ($data['data']['user_type'] == '3') {
				$mail_template = $this->template->isolated_view('user/account_activation_template', $data['data']);
				//$email = $data['data']['email'];

				$this->provab_mailer->send_mail($email, ' B2B Travel Portal Activation ', $mail_template);
			} else {
				$mail_template = $this->template->isolated_view('user/account_activation_template_b2c', $data['data']);
				//$email = $data['data']['email'];
				$this->provab_mailer->send_mail($email, ' B2C Travel Portal Activation', $mail_template);
			}
			//Email Will be sent
		}
	}

	/**
	 * Generate my account view to user
	 */
	function account(): void
	{
		$page_data = [];
		$config = [];
		$cond = [];
		$page_data['form_data'] = $this->input->post();
		$get_data = $this->input->get();
		/**
		 * USE USER PAGE FOR MY ACCOUNT
		 * @var unknown_type
		 */
		$this->user_page = new Provab_Page_Loader('user_management');
		if (isset($get_data['uid']) == true) {
			$get_data['uid'] = intval($get_data['uid']);
			$user_id = intval($get_data['uid']);
			if (valid_array($page_data['form_data']) == false) {
				/*** EDIT DATA ***/

				$cond['user_id'] = intval($user_id);
				$edit_data = $this->user_model->get_user_details($cond);
				if (valid_array($edit_data) == true) {
					$page_data['form_data'] = $edit_data[0];
					$page_data['form_data']['uuid'] = provab_decrypt($page_data['form_data']['uuid']);
					$page_data['form_data']['email'] = provab_decrypt($page_data['form_data']['email']);
				} else {
					redirect('security/log_event');
				}
			} elseif (valid_array($page_data['form_data']) == true && (check_default_edit_privilege($get_data['uid']) || super_privilege())) {
				/** AUTOMATE VALIDATOR **/
				$page_data['form_data']['language_preference'] = 'english';
				$this->user_page->set_auto_validator();
				if ($this->form_validation->run()) {
					if (intval($get_data['uid']) === intval($page_data['form_data']['user_id']) && intval($page_data['form_data']['user_id']) > 0) {
						//Update Data -- LETS UNSET POSTED DATA
						unset($page_data['form_data']['FID']);
						unset($page_data['form_data']['email']);
						$this->custom_db->update_record('user', $page_data['form_data'], array('user_id' => $page_data['form_data']['user_id']));
						$this->application_logger->profile_update($page_data['form_data']['first_name'], $page_data['form_data']['first_name'] . ' Updated Profile Details', array('user_id' => $this->entity_user_id, 'uuid' => $this->entity_uuid));
						set_update_message();
						//FILE UPLOAD
						if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
							if (function_exists("check_mime_image_type")) {
								if (!check_mime_image_type($_FILES['image']['tmp_name'])) {
									echo "Please select the image files only (gif|jpg|png|jpeg)";
									exit;
								}
							}
							$config['upload_path'] = $this->template->domain_image_upload_path();
							$config['allowed_types'] = 'gif|jpg|png|jpeg';
							$config['file_name'] = time();
							$config['max_size'] = '1000000';
							$config['max_width']  = '';
							$config['max_height']  = '';
							$config['remove_spaces']  = false;
							$user_id = $page_data['form_data']['user_id'];
							//UPDATE
							$temp_record = $this->custom_db->single_table_records('user', 'image', array('user_id' => $user_id));
							$icon = $temp_record['data'][0]['image'];
							//DELETE OLD FILES
							if (empty($icon) == false) {
								$temp_profile_image = $this->template->domain_image_full_path($icon); //GETTING FILE PATH
								if (file_exists($temp_profile_image)) {
									unlink($temp_profile_image);
								}
							}
							//UPLOAD IMAGE
							$this->load->library('upload', $config);
							if (! $this->upload->do_upload('image')) {
								echo $this->upload->display_errors();
							} else {
								$image_data =  $this->upload->data();
							}
							$this->custom_db->update_record('user', array('image' => $image_data['file_name']), array('user_id' => $user_id));
						}
						refresh();
					} else {
						redirect('security/log_event');
					}
				} else {
				}
			}
			/** ADD DISABLED STATE **/
			$this->template->view('user/account', $page_data);
		} else {
			redirect('security/log_event');
		}
	}

	/**
	 * Logout function for logout from account and unset all the session variables
	 */
	function initilize_logout(): void
	{
		if (is_logged_in_user()) {
			$this->general_model->update_login_manager($this->session->userdata(LOGIN_POINTER));
			$this->session->unset_userdata(array(AUTH_USER_POINTER => '', LOGIN_POINTER => ''));
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
		$page_data = [];
		$condition = [];
		$data = array();
		$get_data = $this->input->get();
		if (isset($get_data['uid'])) {
			$user_id = $get_data['uid']; //intval($this->encrypt->decode($get_data['uid']));
		} else {
			redirect("general/initilize_logout");
		}
		$page_data['form_data'] = $this->input->post();
		if (valid_array($page_data['form_data']) == TRUE) {
			// $this->current_page->set_auto_validator();
			$this->load->library('form_validation');
			$this->form_validation->set_rules('current_password', 'Current Password', 'required|min_length[5]|max_length[45]|callback_password_check');
			$this->form_validation->set_rules('new_password', 'New Password', 'matches[confirm_password]|min_length[5]|max_length[45]|required|callback_valid_password');
			$this->form_validation->set_rules('confirm_password', 'Confirm', 'callback_check_new_password');
			if ($this->form_validation->run()) {
				$table_name = "user";
				/** Checking New Password and Old Password Are Same OR Not **/
				$condition['password'] = provab_encrypt(md5(trim($this->input->post('new_password'))));
				$condition['user_id'] = $user_id;
				$check_pwd = $this->custom_db->single_table_records($table_name, 'password', $condition);
				if (!$check_pwd['status']) {
					$condition['password'] = provab_encrypt(md5(trim($this->input->post('current_password'))));

					$condition['user_id'] = $user_id;
					$data['password'] = provab_encrypt(md5(trim($this->input->post('new_password'))));
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
	 * Manage user domain in the system
	 * Jaganath (21-05-2015) - 21-05-2015
	 */
	function domain_management(): void
	{
		$page_data = [];
		$domain_update_data = [];
		$domain_list = [];
		$get_data = $this->input->get();
		$post_data = $this->input->post();
		if (valid_array($post_data) == false && isset($get_data['eid']) == true && intval($get_data['eid']) > 0) {
			//EDIT
			$edit_domain_data = $this->module_model->domain_details(intval($get_data['eid']));
			if (valid_array($edit_domain_data) == true) {
				$page_data['form_data'] = $edit_domain_data[0];
				$page_data['form_data']['domain_modules'] = explode(DB_SAFE_SEPARATOR, $page_data['form_data']['domain_modules']);
			} else {
				redirect('security/log_event?event=Domain Invalid CRUD');
			}
		} else if (valid_array($post_data) == true) {
			$this->current_page->set_auto_validator();
			if ($this->form_validation->run()) {
				unset($post_data['FID']);
				$origin = intval($post_data['origin']);
				unset($post_data['origin']);
				$active_modules = $post_data['domain_modules'];
				if ($origin > 0) {
					//UPDATE
					$domain_update_data['domain_name'] = $post_data['domain_name'];
					$domain_update_data['domain_ip'] = $post_data['domain_ip'];
					$domain_update_data['comment'] = $post_data['comment'];
					$domain_update_data['status'] = $post_data['status'];
					$domain_update_data['theme_id'] = $post_data['theme_id'];
					$this->custom_db->update_record('domain_list', $domain_update_data, array('origin' => $origin, 'domain_key' => $post_data['domain_key']));
					//delete domain modules
					$this->custom_db->delete_record('domain_module_map', array('domain_list_fk' => $origin));
					set_update_message();
				} else if ($origin == 0) {
					//INSERT
					$domain_list['domain_name'] = $post_data['domain_name'];
					$domain_list['domain_ip'] = $post_data['domain_ip'];
					$domain_list['comment'] = $post_data['comment'];
					$domain_list['status'] = $post_data['status'];
					$domain_list['theme_id'] = $post_data['theme_id'];
					$domain_list['domain_key'] = $post_data['domain_ip'];
					$domain_list['created_by_id'] = $this->entity_user_id;
					$domain_list['created_datetime'] = date('Y-m-d H:i:s');
					$origin = $this->custom_db->insert_record('domain_list', $domain_list);
					$origin = intval($origin['insert_id']);
					/**
					 * we need to create domain folder only when we are adding it for the first time :)
					 */
					$this->create_default_domain($domain_list['domain_key']);
					set_insert_message();
				}
				//Update domain modules and then redirect
				$this->module_model->create_domain_module_map(intval($origin), $active_modules);
				redirect('user/domain_management');
			}
		}
		$temp_domain_list = $this->user_model->get_domain_details();
		if (valid_array($temp_domain_list)) {
			$page_data['table_data'] = $temp_domain_list;
		} else {
			$page_data['table_data'] = '';
		}
		$this->template->view('user/domain_management', $page_data);
	}
	/**
	 * Get Logged in Users
	 * Jaganath (25-05-2015) - 25-05-2015
	 */
	function get_logged_in_users(int $offset = 0): void
	{
		$page_data = [];
		$config = [];
		$get_data = $this->input->get();
		if (
			isset($get_data['filter']) == true && empty($get_data['filter']) == false &&
			isset($get_data['q']) == true && intval($get_data['q']) > 0
		) {
			$online_users = array();
			$logged_users = array();
			$condition = array(array('U.user_type', ' IN (', intval($get_data['q']), ')'));
			$total_records = $this->user_model->get_logged_in_users($condition, true);
			$temp_user_list = $this->user_model->get_logged_in_users($condition, false, $offset, RECORDS_RANGE_1);

			if (valid_array($temp_user_list)) {
				foreach ($temp_user_list as $v) {
					if (intval(strtotime($v['logout_time'])) > 0) {
						//LOGGED USERS
						$logged_users[] = $v;
					} else {
						//ONLINE USERS
						$online_users[] = $v;
					}
				}
			}
			#exit;
			$page_data['online_users'] = $online_users;
			$page_data['online_total_users'] = count($online_users);
			$page_data['logged_users'] = $logged_users;
			$page_data['logged_total_users'] = count($logged_users);
			$this->load->library('pagination');
			$config['base_url'] = base_url() . 'index.php/user/get_logged_in_users/';
			$config['total_rows'] = intval($total_records->total);
			$config['per_page'] = RECORDS_RANGE_1;
			$this->pagination->initialize($config);
			/** TABLE PAGINATION */
			$this->template->view('user/get_logged_in_users', $page_data);
		} else {
			redirect('security/log_event?event=Invalid Details');
		}
	}
	/**
	 * Manage Domain Logo
	 * Jaganath (25-05-2015) - 26-05-2015
	 */
	function manage_domain(): void
	{

		$post_data = $this->input->post();
		$config = [];
		$page_data = [];
		if (valid_array($post_data) == true && isset($post_data['origin']) == true) {
			$GLOBALS['CI']->template->domain_images();
			if (intval($post_data['origin']) == get_domain_auth_id() && get_domain_auth_id() > 0) {
				$domain_origin = get_domain_auth_id();
				//FILE UPLOAD
				if (valid_array($_FILES) == true and $_FILES['domain_logo']['error'] == 0 and $_FILES['domain_logo']['size'] > 0) {
					$config['upload_path'] = $this->template->domain_image_upload_path();
					/*if( function_exists( "check_mime_image_type" ) ) {
					    if ( !check_mime_image_type( $_FILES['image']['tmp_name'] ) ) {
					    	echo "Please select the image files only (gif|jpg|png|jpeg)"; exit;
					    }
					}*/
					$temp_file_name = $_FILES['domain_logo']['name'];
					//debug($temp_file_name);exit;
					$config['allowed_types'] = 'gif|jpg|png|jpeg';
					$config['file_name'] = get_domain_key() . $temp_file_name;
					$config['max_size'] = MAX_DOMAIN_LOGO_SIZE;
					$config['max_width']  = MAX_DOMAIN_LOGO_WIDTH;
					$config['max_height']  = MAX_DOMAIN_LOGO_HEIGHT;
					$config['remove_spaces']  = false;
					//UPDATE
					$temp_record = $this->custom_db->single_table_records('domain_list', 'domain_logo', array('origin' => $domain_origin));
					//debug($temp_record);exit;
					$domain_logo = $temp_record['data'][0]['domain_logo'];
					//debug($domain_logo);exit;
					//DELETE OLD FILES
					if (empty($domain_logo) == false) {
						$temp_domain_logo = $this->template->domain_image_full_path($domain_logo); //GETTING FILE PATH
						if (file_exists($temp_domain_logo)) {
							unlink($temp_domain_logo);
						}
					}
					//UPLOAD IMAGE
					$this->load->library('upload', $config);
					$this->upload->initialize($config);
					if (! $this->upload->do_upload('domain_logo')) {
						echo $this->upload->display_errors();
					} else {
						$image_data =  $this->upload->data();
					}
					$this->custom_db->update_record('domain_list', array('domain_logo' => $image_data['file_name'], 'domain_name' => $post_data['domain_name'], 'email' => $post_data['email'], 'address' => $post_data['address'], 'phone' => $post_data['phone'], 'api_country_list_fk' => $post_data['country'], 'api_city_list_fk' => $post_data['city']), array('origin' => $domain_origin));
				} else {
					$this->custom_db->update_record('domain_list', array('domain_name' => $post_data['domain_name'], 'domain_webiste' => $post_data['domain_website'], 'email' => $post_data['email'], 'address' => $post_data['address'], 'phone' => $post_data['phone'], 'api_country_list_fk' => $post_data['country'], 'api_city_list_fk' => $post_data['city']), array('origin' => $domain_origin));
				}
				refresh();
			}
		}
		$temp_details = $this->custom_db->single_table_records('domain_list', '*', array('origin' => get_domain_auth_id()));
		// debug($temp_details);exit;
		$country_list = $this->custom_db->single_table_records('api_country_list', '*');
		$city_list = $this->custom_db->single_table_records('api_city_list', '*', array('country' => $temp_details['data'][0]['api_country_list_fk']));

		if ($temp_details['status'] == true) {
			$page_data['data']         = $temp_details['data'][0];
			$page_data['country_list'] = $country_list['data'];
			$page_data['city_list']    = $city_list['data'];
		}
		$this->template->view('user/manage_domain', $page_data);
	}
	function add_banner(): void
	{
		$this->template->view('user/banner_add');
	}
	function  add_banner_action(): void
	{
		$insert_data = [];
		$config = [];
		$post_data = $this->input->post();

		if (valid_array($post_data) == true) {

			//POST DATA formating to update
			$insert_data = array('subtitle' => $post_data['banner_description'], 'title' => $post_data['banner_title'], 'status' => $post_data['status'], 'banner_order' => $post_data['banner_order'], 'added_by' => 1);

			//FILE UPLOAD
			if (valid_array($_FILES) == true and $_FILES['banner_image']['error'] == 0 and $_FILES['banner_image']['size'] > 0) {
				if (function_exists("check_mime_image_type")) {
					if (!check_mime_image_type($_FILES['image']['tmp_name'])) {
						echo "Please select the image files only (gif|jpg|png|jpeg)";
						exit;
					}
				}
				//$domain_origin = 1;
				$config['upload_path'] = $this->template->domain_ban_image_upload_path();
				$temp_file_name = $_FILES['banner_image']['name'];
				$config['allowed_types'] = 'gif|jpg|png|jpeg';
				$config['file_name'] = get_domain_key() . $temp_file_name;
				$config['max_size'] = '1000000';
				$config['max_width']  = '';
				$config['max_height']  = '';
				$config['remove_spaces']  = false;

				//UPLOAD IMAGE
				$this->load->library('upload', $config);
				$this->upload->initialize($config);
				if (! $this->upload->do_upload('banner_image')) {
					echo $this->upload->display_errors();
				} else {
					$image_data =  $this->upload->data();
				}

				/*UPDATING IMAGE */
				$insert_data['image'] = $image_data['file_name'];
				//debug($insert_data);exit;
				$this->custom_db->insert_record('banner_images', $insert_data);
			}
			//refresh();
		}
		redirect('user/banner_images');
	}
	function banner_images(): void
	{
		// Search Params(Country And City)
		// CMS - Image(On Home Page)
		$page_data = array();
		$filter = ['added_by' => 1];
		$data_list = $this->custom_db->single_table_records('banner_images', '*', $filter, 0, 100000);
		//debug($data_list);exit;
		$page_data['data_list'] = $data_list['data'];
		$this->template->view('user/banner_images_new', $page_data);
	}
	function edit_banner(int $id): void
	{
		$page_data = array();
		$filter = ['origin' => $id];
		$data_list = $this->custom_db->single_table_records('banner_images', '*', $filter, 0, 100000);
		$page_data['data_list'] = $data_list['data'];
		$this->template->view('user/banner_edit', $page_data);
	}
	function update_banner_action(): void
	{
		$insert_data = [];
		$config = [];
		$post_data = $this->input->post();
		//debug($_FILES);exit;
		$BID = $post_data['BID'];
		if (valid_array($post_data) == true) {

			//POST DATA formating to update
			$insert_data = array('subtitle' => $post_data['banner_description'], 'title' => $post_data['banner_title'], 'status' => $post_data['status'], 'banner_order' => $post_data['banner_order']);

			//FILE UPLOAD
			if (valid_array($_FILES) == true and $_FILES['banner_image']['error'] == 0 and $_FILES['banner_image']['size'] > 0) {
				if (function_exists("check_mime_image_type")) {
					if (!check_mime_image_type($_FILES['banner_image']['tmp_name'])) {
						echo "Please select the image files only (gif|jpg|png|jpeg)";
						exit;
					}
				}
				$domain_origin = 1;
				$config['upload_path'] = $this->template->domain_ban_image_upload_path();
				$temp_file_name = $_FILES['banner_image']['name'];
				$config['allowed_types'] = 'gif|jpg|png|jpeg';
				$config['file_name'] = get_domain_key() . $temp_file_name;
				$config['max_size'] = '1000000';
				$config['max_width']  = '';
				$config['max_height']  = '';
				$config['remove_spaces']  = false;
				//UPDATE
				$temp_record = $this->custom_db->single_table_records('banner_images', 'image', array('added_by' => $domain_origin, 'origin' => $BID));
				//debug($temp_record);exit;
				$banner_image = $temp_record['data'][0]['image'];
				//DELETE OLD FILES
				if (empty($banner_image) == false) {
					$temp_banner_image = $this->template->domain_ban_image_full_path($banner_image); //GETTING FILE PATH
					if (file_exists($temp_banner_image)) {
						unlink($temp_banner_image);
					}
				}
				//echo $temp_banner_image;exit;
				//debug($config);exit;
				//echo $temp_banner_image;exit;
				//UPLOAD IMAGE
				$this->load->library('upload', $config);
				$this->upload->initialize($config);
				if (! $this->upload->do_upload('banner_image')) {
					echo $this->upload->display_errors();
				} else {
					$image_data =  $this->upload->data();
				}
				//debug($image_data);exit;
				/*UPDATING IMAGE */
				$this->custom_db->update_record('banner_images', array('image' => $image_data['file_name']), array('origin' => $BID));
			}
			//refresh();
		}
		/*UPDATING OTHER FIELDS*/
		$this->custom_db->update_record('banner_images', $insert_data, array('origin' => $BID));
		$this->banner_images();
	}
	function banner_delete(int $BID): void
	{
		if ($BID) {
			$temp_record = $this->custom_db->single_table_records('banner_images', 'image', array('origin' => $BID));
			//debug($temp_record);exit;
			$banner_image = $temp_record['data'][0]['image'];
			//DELETE OLD FILES
			if (empty($banner_image) == false) {
				$temp_banner_image = $this->template->domain_ban_image_full_path($banner_image); //GETTING FILE PATH
				if (file_exists($temp_banner_image)) {
					unlink($temp_banner_image);
				}
			}
			$this->custom_db->delete_record('banner_images', array('origin' => $BID));
		}
		redirect('user/banner_images');
	}
	function banner_images_old(): void
	{
		$config = [];
		$page_data = [];
		$post_data = $this->input->post();
		if (valid_array($post_data) == true && isset($post_data['added_by']) == true) {
			$GLOBALS['CI']->template->domain_images();
			if (intval($post_data['added_by']) == get_domain_auth_id() && get_domain_auth_id() > 0) {
				$domain_origin = get_domain_auth_id();
				//FILE UPLOAD
				if (valid_array($_FILES) == true and $_FILES['banner_image']['error'] == 0 and $_FILES['banner_image']['size'] > 0) {
					if (function_exists("check_mime_image_type")) {
						if (!check_mime_image_type($_FILES['image']['tmp_name'])) {
							echo "Please select the image files only (gif|jpg|png|jpeg)";
							exit;
						}
					}
					$config['upload_path'] = $this->template->domain_image_upload_path();
					$temp_file_name = $_FILES['banner_image']['name'];
					$config['allowed_types'] = 'gif|jpg|png|jpeg';
					$config['file_name'] = get_domain_key() . $temp_file_name;
					$config['max_size'] = '1000000';
					$config['max_width']  = '';
					$config['max_height']  = '';
					$config['remove_spaces']  = false;
					//UPDATE
					$temp_record = $this->custom_db->single_table_records('banner_images', 'image', array('added_by' => $domain_origin));
					//debug($temp_record);exit;
					$banner_image = $temp_record['data'][0]['image'];
					//DELETE OLD FILES
					if (empty($banner_image) == false) {
						$temp_banner_image = $this->template->domain_image_full_path($banner_image); //GETTING FILE PATH
						if (file_exists($temp_banner_image)) {
							unlink($temp_banner_image);
						}
					}
					//UPLOAD IMAGE
					$this->load->library('upload', $config);
					$this->upload->initialize($config);
					if (! $this->upload->do_upload('banner_image')) {
						echo $this->upload->display_errors();
					} else {
						$image_data =  $this->upload->data();
					}
					$this->custom_db->delete_record('banner_images', array('added_by' => $domain_origin));
					$this->custom_db->insert_record('banner_images', array('image' => $image_data['file_name'], 'added_by' => $domain_origin));
				}
				refresh();
			}
		}
		$temp_details = $this->custom_db->single_table_records('banner_images', 'image', array('added_by' => get_domain_auth_id()));
		if ($temp_details['status'] == true) {
			$page_data['banner_image'] = $temp_details['data'][0]['image'];
		} else {
			$page_data['banner_image'] = '';
		}
		$this->template->view('user/banner_images', $page_data);
	}
	/**
	 * Jaganath
	 * Reset the Password and send the new Password to Agent Email
	 * @param $user_id
	 * @param $uuid
	 */
	function send_agent_new_password(int $user_id, string $uuid): void
	{
		$cond = [];
		$data = [];
		$update_user_record = [];
		$cond['user_id'] = intval($user_id);
		$cond['uuid'] = $uuid;
		$data['status'] = ACTIVE;
		$user_record = $this->user_model->update_user_data($data, $cond);
		if ($user_record['status'] == SUCCESS_STATUS) {
			//Sms config & Checkpoint
			/* if(active_sms_checkpoint('forget_password'))
			{
			$msg = "Dear ".$user_record['data'][0]['first_name']." Your Password details has been sent to your email id";
			//print($msg); exit;
			$msg = urlencode($msg);
			$this->provab_sms->send_msg($phone,$msg);
			} */
			//sms will be sent

			$new_password = time();
			$user_record['data']['password'] = $new_password;
			//send email
			$user_record['data']['email'] = provab_decrypt($user_record['data']['email']);
			$email = $user_record['data']['email'];
			$mail_template = $this->template->isolated_view('user/forgot_password_template', $user_record['data']);
			$update_user_record['password'] = provab_encrypt(md5(trim($new_password)));

			$this->custom_db->update_record('user', $update_user_record, array('user_id' => intval($user_record['data']['user_id'])));
			$this->load->library('provab_mailer');
			$this->provab_mailer->send_mail($email, 'Password Reset', $mail_template);
		}
	}
	/**
	 * Jaganath
	 * Delete Agent: Make it invisible
	 * @param $user_id
	 * @param $uuid
	 */
	function delete_agent(int $user_id, string $uuid): void
	{
		$cond = [];
		$data = [];
		$update_user_record = [];
		$cond['user_id'] = intval($user_id);
		$cond['uuid'] = $uuid;
		$data['status'] = ACTIVE;
		$user_record = $this->user_model->update_user_data($data, $cond);
		if ($user_record['status'] == SUCCESS_STATUS) {
			//Sms config & Checkpoint
			/* if(active_sms_checkpoint('forget_password'))
			{
			$msg = "Dear ".$user_record['data'][0]['first_name']." Your Password details has been sent to your email id";
			//print($msg); exit;
			$msg = urlencode($msg);
			$this->provab_sms->send_msg($phone,$msg);
			} */
			//sms will be sent
			$update_user_record = array();
			$update_user_record['status'] = (-1); //Delete Agent
			$this->custom_db->update_record('user', $update_user_record, array('user_id' => intval($user_record['data']['user_id'])));
			$email = provab_decrypt($user_record['data']['email']);
			//send email
			$mail_template = $this->template->isolated_view('user/account_deactivation_template', $user_record['data']);
			$this->load->library('provab_mailer');
			$this->provab_mailer->send_mail($email, 'Account Deactivated', $mail_template);
		}
	}

	function get_city_list(): void
	{
		$country_id = $this->input->post('country_id');
		$city_list = $this->custom_db->single_table_records('api_city_list', '*', array('country' => $country_id), 0, 100000000, array('destination' => 'asc'));
		$options = '';
		$city_list = $city_list['data'];
		foreach ($city_list as $city) {
			$options .= "<option value=" . $city['origin'] . ">" . $city['destination'] . "</option>";
		}
		print_r($options);
	}
	/**
	 * Call back function to check useremail availability
	 * @param string $name
	 */
	public function useremail_b2c_check(string $email): bool
	{
		$condition = [];
		$condition['email'] = provab_encrypt($email);
		$condition['user_type'] = B2C_USER;
		$condition['domain_list_fk'] = intval(get_domain_auth_id());
		$data = $this->custom_db->single_table_records('user', 'user_id', $condition);
		if ($data['status'] == SUCCESS_STATUS and valid_array($data['data']) == true) {
			$this->form_validation->set_message('username_check', $email . ' Already Registered!!!');
			return FALSE;
		} else {
			return TRUE;
		}
	}
	/**
	 * Call back function to check useremail availability
	 * @param string $name
	 */
	public function useremail_b2b_check(string $email): bool
	{
		$condition = [];
		$condition['email'] = provab_encrypt($email);
		$condition['user_type'] = B2B_USER;
		$condition['domain_list_fk'] = intval(get_domain_auth_id());
		$data = $this->custom_db->single_table_records('user', 'user_id', $condition);
		if ($data['status'] == SUCCESS_STATUS and valid_array($data['data']) == true) {
			$this->form_validation->set_message('username_check', $email . ' Already Registered!!!');
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	 * Call back function to check useremail availability
	 * @param string $name
	 */
	public function useremail_subadmin_check(string $email): bool
	{
		$condition = [];
		$condition['email'] = provab_encrypt($email);
		$condition['user_type'] = SUB_ADMIN;
		$condition['domain_list_fk'] = intval(get_domain_auth_id());
		$data = $this->custom_db->single_table_records('user', 'user_id', $condition);
		if ($data['status'] == SUCCESS_STATUS and valid_array($data['data']) == true) {
			$this->form_validation->set_message('username_check', $email . ' Already Registered!!!');
			return FALSE;
		} else {
			return TRUE;
		}
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
		$regex_lowercase = '/[a-z]/';
		$regex_uppercase = '/[A-Z]/';
		$regex_number = '/[0-9]/';
		$regex_special = '/[!@#$%^&*()\-_=+{};:,<.>§~]/';
		if (empty($password)) {
			$this->form_validation->set_message('valid_password', 'The Password field is required.');
			return FALSE;
		}
		if (preg_match_all($regex_lowercase, $password) < 1) {
			$this->form_validation->set_message('valid_password', 'The Password field must be at least one lowercase letter.');
			return FALSE;
		}
		if (preg_match_all($regex_uppercase, $password) < 1) {
			$this->form_validation->set_message('valid_password', 'The Password field must be at least one uppercase letter.');
			return FALSE;
		}
		if (preg_match_all($regex_number, $password) < 1) {
			$this->form_validation->set_message('valid_password', 'The Password field must have at least one number.');
			return FALSE;
		}
		if (preg_match_all($regex_special, $password) < 1) {
			$this->form_validation->set_message('valid_password', 'The Password field must have at least one special character.' . ' ' . htmlentities('!@#$%^&*()\-_=+{};:,<.>§~'));
			return FALSE;
		}
		if (strlen($password) < 5) {
			$this->form_validation->set_message('valid_password', 'The Password field must be at least 5 characters in length.');
			return FALSE;
		}
		if (strlen($password) > 32) {
			$this->form_validation->set_message('valid_password', 'The Password field cannot exceed 32 characters in length.');
			return FALSE;
		}
		return TRUE;
	}

	public function change_admin_password(): void
	{
		$user_details = $this->custom_db->single_table_records('user', '*', array('user_type' => 1));
		foreach ($user_details['data'] as $value) {
			$update = array();
			$condition = array();
			$email = 'balu.provab@gmail.com';
			$password = 'Provab@123';
			$update['email'] = provab_encrypt($email);
			$update['user_name'] = $update['email'];
			$update['password'] = provab_encrypt(md5($password));
			$condition['user_id'] = $value['user_id'];
			if ($this->custom_db->update_record('user', $update, $condition)) {
				echo 'Email ID Updated';
			} else {
				echo 'Failed';
			}
		}
	}
	/* User Privileges */
	function user_privilege(): void
	{
		$page_data = [];
		if (! check_user_previlege('p60'))
			redirect(base_url());
		$page_data['form_data'] = $this->input->post();
		$get_data = $this->input->get();
		// $domain_origin = 0;
		// CHECKING DOMAIN ORIGIN SET OR NOT
		// exit;
		if (isset($get_data['domain_origin']) == true && intval($get_data['domain_origin']) > 0) {
			$domain_origin = intval($get_data['domain_origin']);
		} else {
			$domain_origin = 0;
		}

		$page_data['eid'] = intval($get_data['eid']);
		if (valid_array($page_data['form_data']['user_previlages']) == true && isset($page_data['form_data']['user_id'])) {
			/**
			 * AUTOMATE VALIDATOR *
			 */
			$page_data['eid'] = $page_data['form_data']['user_id'];
			$active_previlages = $page_data['form_data']['user_previlages'];
			$this->user_model->edit_user_privileges(intval($page_data['form_data']['user_id']), $active_previlages);
			// edit previlages------------------------------------------------
			if (intval($get_data['eid']) > 0) {
				$temp_query_string = str_replace('&eid=' . intval($get_data['eid']), '', $_SERVER['QUERY_STRING']);
			} else {
				$temp_query_string = $_SERVER['QUERY_STRING'];
			}

			redirect('user/' . __FUNCTION__ . '?' . $temp_query_string);
		}
		$search_text = ' WHERE PL.p_no!=0 ';
		$user_info = $this->custom_db->single_table_records('user', '*', array('user_id' => $page_data['eid']));
		$page_data['info'] = $this->template->isolated_view('user/info', array('info' => $user_info['data'][0]));
		$page_data['table_data'] = $this->user_model->get_privilage_list($page_data['eid'], $search_text);
		/**
		 * TABLE PAGINATION
		 */
		// Get Online User Count
		$this->template->view('user/user_privilege', $page_data);
	}
	function random_str(int $length, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
	{
		$str = '';
		$max = mb_strlen($keyspace, '8bit') - 1;
		if ($max < 1) {
			throw new Exception('$keyspace must be at least two characters long');
		}
		for ($i = 0; $i < $length; ++$i) {
			$str .= $keyspace[random_int(0, $max)];
		}
		return $str;
	}
	function supplier_management(string $origin = ''): void
	{
		$page_data = array();
		$config = [];

		$post_params = $this->input->post();
		//debug($post_params);die;
		if (empty($post_params) == false) {
			$image_data = array();
			if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
				echo "dfdf";
				die;
				$config['upload_path'] = $this->template->domain_image_upload_path();
				$temp_file_name = $_FILES['image']['name'];
				$config['allowed_types'] = '*';
				$config['file_name'] = time() . '_supplier_image_' . $temp_file_name;
				$config['max_size'] = '100000000';
				$config['max_width'] = '';
				$config['max_height'] = '';
				$config['remove_spaces'] = false;

				// UPLOAD IMAGE
				$this->load->library('upload', $config);
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('image')) {
					set_error_message('UL00102');
					redirect('user/supplier_management');
					exit();
				} else {
					$image_data = $this->upload->data();
				}
			}
			$insert_data = $post_params;
			unset($insert_data['FID']);

			$insert_data['user_type'] = SUPPLIER;

			$insert_data['email'] = provab_encrypt($insert_data['email']);
			$insert_data['user_name'] = $insert_data['email'];

			if (empty($image_data) == false) {
				$insert_data['image'] = $image_data['file_name'];
			}

			if (!empty($origin)) {

				$origin = ($origin);
				$origin = intval($origin);
				$data = $this->custom_db->single_table_records('user', '*', array('user_id' => $origin));
				if ($data['status'] == true) {
					//now update
					$update_res = $this->custom_db->update_record('user', $insert_data, array('user_id' => $origin));
					if ($update_res == QUERY_SUCCESS) {


						set_update_message();
						redirect('user/supplier_management');
						exit();
					} else {
						//show error
						set_error_message('UL002');
						//delete uploaded images
						if (empty($image_data) == false) {
							if (empty($image_data['file_name']) == false) {
								$_image = $this->template->domain_image_full_path() . $image_data['file_name']; // GETTING FILE PATH
								if (file_exists($_image)) {
									unlink($_image);
								}
							}
						}
						redirect('user/supplier_management');
						exit();
					}
				} else {
					//invalid request
					set_error_message('UL00100');
					redirect('user/supplier_management');
					exit();
				}
			} else {


				//insert
				$insert_data['uuid'] = provab_encrypt(PROJECT_PREFIX . time());
				$insert_data['created_by_id'] = $this->entity_user_id;
				$rand_password_generator = $this->random_str(8);
				$insert_data['password'] = provab_encrypt(md5(trim($rand_password_generator)));

				$insert_res = $this->custom_db->insert_record('user', $insert_data);
				if ($insert_res['status'] == QUERY_SUCCESS) {
					set_insert_message();

					//debug($email);die;
					$data['first_name'] = $insert_data['first_name'];
					$data['last_name'] = $insert_data['last_name'];
					$data['password'] = $rand_password_generator;
					$data['currency'] = $this->custom_db->single_table_records('currency_converter', 'country', array("id" => $insert_data['currency']))['data'][0]['country'];
					$data['email']   =  provab_decrypt($insert_data['email']);
					//$mail_template = $this->template->isolated_view('general/supplier_template', $data);

					$this->load->library('provab_mailer');
					// $email = provab_decrypt($insert_data['email']);
					// $subject = 'Supplier Registration Acknowledgment-www.' . $_SERVER['HTTP_HOST'];
					//$this->provab_mailer->send_mail($email, $subject, $mail_template);
					redirect('user/supplier_management');
					exit();
				} else {
					//show error
					set_error_message('UL002');
					//delete uploaded images
					if (empty($image_data) == false) {
						if (empty($image_data['file_name']) == false) {
							$_image = $this->template->domain_image_full_path() . 'packages/' . $image_data['file_name']; // GETTING FILE PATH
							if (file_exists($_image)) {
								unlink($_image);
							}
						}
					}
					redirect('user/supplier_management');
					exit();
				}
			}
		} else {
			//echo "ddd";die;
			$origin = ($origin);
			$origin = intval($origin);
			if ($origin > 0) {
				$page_data['origin'] = $origin;
				$data = $this->custom_db->single_table_records('user', '*', array('user_id' => $origin));
				if ($data['status'] == true) {
					$page_data['form_data'] = $data['data'][0];
					$page_data['form_data']['email'] = provab_decrypt($page_data['form_data']['email']);
				} else {
					set_error_message('UL00100');
					redirect('user/supplier_management');
					exit();
				}
			}
		}

		$query = "select * from user where user_type=" . SUPPLIER . " AND status !=-1 ORDER by user_id DESC";

		$data_list = $GLOBALS['CI']->db->query($query)->result_array();
		if (!empty($data_list) == true) {
			$page_data['data_list'] = $data_list;
		}

		$this->template->view('user/supplier_management', $page_data);
	}
}
