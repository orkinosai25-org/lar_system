<?php
if (! defined('BASEPATH'))
	exit('No direct script access allowed');
/**
 *
 * @package Provab - Provab Application
 * @subpackage Travel Portal
 * @author Balu A<balu.provab@gmail.com>
 * @version V2
 */
class Utilities extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('Api_Interface');
	}
	/**
	 */
	public function deal_sheets(): void
	{
		$page_data=[];
		$bus_deals_result = json_decode($this->api_interface->rest_service('bus_deal_sheet'), true);
		$flight_deals_result = json_decode($this->api_interface->rest_service('airline_deal_sheet'), true);

		$page_data['bus_deals'] = $bus_deals_result['data'];
		$page_data['flight_deals'] = $flight_deals_result['data'];
		$this->template->view('utilities/deal_sheets', $page_data);
	}

	/**
	 * Update Convenience Fees in application
	 */
	public function convenience_fees(): void
	{
		$page_data=[];
		$page_data['post_data'] = $this->input->post();
		$this->load->model('transaction_model');
		if (valid_array($page_data['post_data']) == true) {
			$this->transaction_model->update_convenience_fees($page_data['post_data']);
			set_update_message();
			redirect(base_url() . 'index.php/utilities/convenience_fees');
		}
		$convenience_fees = $this->transaction_model->get_convenience_fees();
		$page_data['convenience_fees'] = $this->format_convenience_fees($convenience_fees);
		$this->template->view('utilities/convenience_fees', $page_data);
	}

	/**
	 * Format Convenience Fees As Per View
	 */

	private function format_convenience_fees(array $convenience_fees): array
	{
		$data = array();
		foreach ($convenience_fees as $k => $v) {
			$data[$k]['origin'] = $v['origin'];
			$data[$k]['module'] = strtoupper($v['module']);
			$fees = '';
			if ($v['value_type'] == 'plus') {
				$fees = '+' . floatval($v['value']);
			} else {
				$fees = floatval($v['value']) . '%';
			}
			$data[$k]['fees'] = $fees;
			$data[$k]['value'] = $v['value'];
			$data[$k]['value_type'] = $v['value_type'];
			$data[$k]['per_pax'] = $v['per_pax'];
		}
		return $data;
	}

	/**
	 * Manage booking source in the application
	 */
	public function manage_source(): void
	{
		$page_data=[];
		$page_data['list_data'] = $this->module_model->get_course_list_new();
		//debug($page_data ['list_data'] );exit;
		$this->template->view('utilities/manage_source', $page_data);
	}
	/**
	 * Manage sms status in sms_checkpoint table
	 */
	public function sms_checkpoint(): void
	{
		$data=[];
		$sms_checkpoint_data = $this->module_model->get_sms_checkpoint();
		$data['sms_data'] = $sms_checkpoint_data;
		$this->template->view('utilities/sms_checkpoint', $data);
	}
	/**
	 * Activate sms_checkpoint
	 */
	public function activate_sms_checkpoint(array $condition): void
	{
		$status = ACTIVE;
		$this->module_model->update_sms_checkpoint_status($status, $condition);
		redirect(base_url() . 'index.php/utilities/sms_checkpoint');
	}

	/**
	 * Deactiavte sms_checkpoint
	 */
	// public function deactivate_sms_checkpoint(array $condition): void
	// {
	// 	$status = INACTIVE;
	// 	// $info = $this->module_model->update_sms_checkpoint_status($status, $condition);
	// 	redirect(base_url() . 'index.php/utilities/sms_checkpoint');
	// }
	/**
	 * Module Activation
	 */
	public function module(): void
	{
		$data=[];
		$domain_list = $this->module_model->get_module_list();
		$data['domain_list'] = $domain_list;
		$this->template->view('utilities/module_list', $data);
	}
	/**
	 * Activate sms_checkpoint
	 */
	public function activate_module(array $condition): void
	{
		$status = ACTIVE;
		$this->module_model->update_module_status($status, $condition);
		redirect(base_url() . 'index.php/utilities/module');
	}

	/**
	 * Deactiavte sms_checkpoint
	 */
	// public function deactivate_module(array $condition): void
	// {
	// 	$status = INACTIVE;
	// 	// $info = $this->module_model->update_module_status($status, $condition);
	// 	redirect(base_url() . 'index.php/utilities/module');
	// }
	/**
	 * Activate social_link
	 */
	public function activate_social_link(array $condition): void
	{
		$status = ACTIVE;
		$this->module_model->update_social_link_status($status, $condition);
		redirect(base_url() . 'index.php/utilities/social_network');
	}

	/**
	 * Deactiavte social_link
	 */
	// public function deactivate_social_link(array $condition): void
	// {
	// 	$status = INACTIVE;
	// 	// $info = $this->module_model->update_social_link_status($status, $condition);
	// 	redirect(base_url() . 'index.php/utilities/social_network');
	// }
	/*
	 * SOcial Network Url Management
	 */
	public function social_network(): void
	{
		$data=[];
		$temp = $this->custom_db->single_table_records('social_links');
		$data['social_links'] = $temp['data'];
		$this->template->view('utilities/social_network', $data);
	}
	public function social_network_status_toggle(int $id = 0, string $status = ACTIVE): void
	{
		$data=[];
		if (intval($id) > 0) {
			$data['status'] = $status;
			$this->custom_db->update_record('social_login', $data, array(
				'origin' => $id
			));
		}
	}
	// public function edit_social_login1(mixed $value, mixed $id): void
	// {
	// 	//echo $value;exit;
	// 	// $info = $this->module_model->update_social_config($value, $id);
	// 	//redirect ( base_url () . 'index.php/utilities/social_network' );
	// }
	/**
	 * Update_Social URL
	 */
	public function edit_social_url(): void
	{
		//$post_data = $this->input->post();
		// $id = $post_data['origin'];
		// $url = $post_data['social_url'];
		// $info = $this->module_model->update_social_url($url, $id);
		redirect(base_url() . 'index.php/utilities/social_network');
	}

	/**
	 * Activate social_login
	 */

	public function activate_social_login(array $condition): void
	{
		$status = ACTIVE;
		$this->module_model->update_social_login_status($status, $condition);
		redirect(base_url() . 'index.php/utilities/social_login');
	}

	/**
	 * Deactiavte social_login
	 */
	// public function deactivate_social_login(array $condition): void
	// {
	// 	$status = INACTIVE;
	// 	// $info = $this->module_model->update_social_login_status($status, $condition);
	// 	redirect(base_url() . 'index.php/utilities/social_login');
	// }
	/*
	 * SOcial Network Url Management
	 */
	public function social_login(): void
	{
		$data=[];
		$temp = $this->custom_db->single_table_records('social_login');
		$data['social_login'] = $temp['data'];
		$this->template->view('utilities/social_login', $data);
	}
	/**
	 * Update social_login
	 */
	// public function edit_social_login(int $id): void
	// {
	// 	$post_data = $this->input->post();
	// 	$url = $post_data['social_login'];
	// 	// $info = $this->module_model->update_social_login_name($url, $id);
	// 	redirect(base_url() . 'index.php/utilities/social_login');
	// }

	/**
	 * Currency Converter Settings!!!
	 * 
	 * @param float $value        	
	 * @param int $id        	
	 */
	public function currency_converter(float $value = 0, int $id = 0): void
	{
		$data=[];
		if (intval($id) > 0 && intval($value) > -1) {
			$data['value'] = $value;
			$this->custom_db->update_record('currency_converter', $data, array(
				'id' => $id
			));
		} else {
			$currency_data = $this->custom_db->single_table_records('currency_converter');
			$data['converter'] = $currency_data['data'];
			$this->template->view('utilities/currency_converter', $data);
		}
	}

	/**
	 * Currency Converter Status Update!!!
	 * 
	 * @param float $value        	
	 * @param int $id        	
	 */
	public function currency_status_toggle(int $id = 0, int $status = ACTIVE): void
	{
		$data=[];
		if (intval($id) > 0) {
			$data['status'] = $status;
			$this->custom_db->update_record('currency_converter', $data, array(
				'id' => $id
			));
		}
	}

	/**
	 * Update Currency Converter Values Automatically Using Live Rates
	 * Keeping COURSE_LIST_DEFAULT_CURRENCY_VALUE AS Base Currency
	 */
	public function auto_currency_converter(): void
	{
		$data=[];
		$data_set = $this->custom_db->single_table_records('currency_converter');

		if ($data_set['status'] == true) {

			$to = urlencode(COURSE_LIST_DEFAULT_CURRENCY_VALUE);
			$data['date_time'] = date('Y-m-d H:i:s');
			$encode_amount = 1;

			foreach ($data_set['data'] as  $v) {

				$from = urlencode($v['country']);
				$encode_amount = ($from != $to) ? $encode_amount : 1;
				$encode_amount = urlencode($encode_amount);


				$get = file_get_contents("http://prod.services.travelomatix.com/webservices/index.php/rest/currecny_value_details?amount=$encode_amount&from=$from&to=$to");
				$get_currency = json_decode($get, true);

				$converted_currency = (isset($get_currency['currency_value']) ? $get_currency['currency_value'] : 1);

				$data['value'] = $converted_currency;

				$this->custom_db->update_record('currency_converter', $data, array('id' => $v['id']));
			}
		}
		redirect('utilities/currency_converter');
	}
	public function auto_currency_converter_old(): void
	{
		$data=[];
		$data_set = $this->custom_db->single_table_records('currency_converter');
		if ($data_set['status'] == true) {
			$from = COURSE_LIST_DEFAULT_CURRENCY_VALUE;
			$data['date_time'] = date('Y-m-d H:i:s');
			foreach ($data_set['data'] as $v) {
				$url = 'http://download.finance.yahoo.com/d/quotes.csv?s=' . $v['country'] . $from . '=X&f=nl1';
				$handle = fopen($url, 'r');
				if ($handle) {
					$currency_data = fgetcsv($handle);
					fclose($handle);
				}
				if ($currency_data != '') {
					if (isset($currency_data[0]) == true and empty($currency_data[0]) == false and isset($currency_data[1]) == true and empty($currency_data[1]) == false) {
						$data['value'] = $currency_data[1];
						$this->custom_db->update_record('currency_converter', $data, array(
							'id' => $v['id']
						));
					}
				}
			}
		}
		redirect('utilities/currency_converter');
	}

	/**
	 * Load All Events Of Trip Calendar
	 */
	public function trip_calendar(): void
	{
		$this->template->view('utilities/trip_calendar');
	}
	public function app_settings(): void
	{
		$this->template->view('utilities/app_settings');
	}

	/**
	 * Show time line to user previous one month - Load Last one month by default
	 */

	public function timeline(): void
	{
		$this->template->view('utilities/timeline');
	}

	/**
	 * Get All The Events Between Two Dates
	 */
	public function timeline_rack(): void
	{$response=[];
		$response['status'] = FAILURE_STATUS;
		$response['data'] = array();
		$response['msg'] = '';
		$params = $this->input->get();
		$oe_start = intval($params['oe_start']);
		$event_limit = intval($params['oe_limit']);
		if ($oe_start > -1 and $event_limit > -1) {
			// Older Events
			$oe_list = $this->application_logger->get_events($oe_start, $event_limit);
			if (valid_array($oe_list) == true) {
				$response['oe_list'] = get_compressed_output($this->template->isolated_view('utilities/core_timeline', array(
					'list' => $oe_list
				)));
				$response['status'] = SUCCESS_STATUS;
			}
		}
		header('Content-type:application/json');
		echo json_encode($response);
		exit();
	}

	/**
	 * Get All The Events Between Two Dates
	 */
	public function latest_timeline_events(): void
	{
		$response=[];
		session_write_close(); // This is needed as it helps remove session locks
		$response['status'] = FAILURE_STATUS;
		$response['data'] = array();
		$response['msg'] = '';
		// $waiting_for_new_event = true;
		$params = $this->input->get();
		$last_event_id = intval($params['last_event_id']);
		if ($last_event_id > -1) {
			$cond = array(
				array(
					'TL.origin',
					'>',
					$last_event_id
				)
			);
			// Older Events
			while ($response['status'] == false) {
				$os_list = $this->application_logger->get_events(0, 10000000000, $cond);
				if (valid_array($os_list) == true) {
					$response['oa_list'] = get_compressed_output($this->template->isolated_view('utilities/core_timeline', array(
						'list' => $os_list
					)));
					$response['status'] = SUCCESS_STATUS;
				} else {
					sleep(3);
				}
			}
		}
		header('Content-type:application/json');
		echo json_encode($response);
		exit();
	}
	/**
	 * Active Notification Count
	 */
	public function active_notifications_count(): void
	{
		$response=[];
		$get_data = $this->input->get();
		$response['status'] = SUCCESS_STATUS;
		$response['data'] = array();
		$response['msg'] = '';
		//DeActive the Notification
		if (isset($get_data['deactive_notification']) == true && $get_data['deactive_notification'] == 1) {
			$this->application_logger->disable_active_event_notification();
		}
		$condition = array();
		$active_notifications_count = $this->application_logger->active_notifications_count($condition);
		$response['data']['active_notifications_count'] = intval($active_notifications_count);
		header('Content-type:application/json');
		echo json_encode($response);
		exit();
	}
	/**
	 * Balu A
	 * Notification Alerts
	 */
	public function events_notification(): void
	{$response=[];
		$response['status'] = FAILURE_STATUS;
		$response['data'] = array();
		$response['msg'] = '';
		$oe_start = 0;
		$event_limit = 10;
		$notification_list = $this->application_logger->get_events_notification($oe_start, $event_limit);
		if (valid_array($notification_list) == true) {
			$page_data = array();
			$page_data['list'] = $notification_list;
			$response['data']['notification_list'] = get_compressed_output($this->template->isolated_view('utilities/events_notification', $page_data));
			$response['status'] = SUCCESS_STATUS;
		}
		header('Content-type:application/json');
		echo json_encode($response);
		exit();
	}
	/**
	 * All Notification List
	 */
	public function notification_list(int $offset = 0): void
	{
		$config=[];
		$page_data = array();
		$condition = array();
		$total_records = $this->application_logger->get_events_notification($offset, RECORDS_RANGE_3, $condition, true);
		$page_data['list'] = $this->application_logger->get_events_notification($offset, RECORDS_RANGE_3, $condition, false);
		//--------PAGINATION-------------//
		$this->load->library('pagination');
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['base_url'] = base_url() . 'index.php/utilities/notification_list/';
		$config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
		$config['total_rows'] = $total_records->total;
		$config['per_page'] = RECORDS_RANGE_3;
		$this->pagination->initialize($config);
		$page_data['total_rows'] = $total_records->total;
		$this->template->view('utilities/notification_list', $page_data);
	}
	/**
	 * Balu A
	 * Manage Promo Codes
	 */
	public function manage_promo_code(int $offset = 0): void
	{
		$config=[];
		$post_data = $this->input->post();
		// debug($post_data);exit;
		$get_data = $this->input->get();
		$page_data = array();
		$page_data['from_data']['origin'] = 0;
		$condition = array();
		$page_data['promo_code_page_obj'] = new Provab_Page_Loader('manage_promo_code');
		// debug($post_data);exit;
		if (isset($get_data['eid']) == true && intval($get_data['eid']) > 0 && valid_array($post_data) == false) {

			$edit_data = $this->custom_db->single_table_records('promo_code_list', '*', array('origin' => intval($get_data['eid'])));

			if ($edit_data['status'] == true) {
				if (strtotime($edit_data['data'][0]['expiry_date']) <= 0) {
					$edit_data['data'][0]['expiry_date'] = ''; //If its Unlimited, setting the Expiry Date to empty

				}
				$edit_data['data'][0]['promo_code_image1'] = $edit_data['data'][0]['promo_code_image'];

				$page_data['from_data'] = $edit_data['data'][0];
			} else {
				redirect('security/log_event?event=InvalidID');
			}
		} else if (valid_array($post_data) == true) { //ADD
			// debug($post_data);exit;
			$page_data['promo_code_page_obj']->set_auto_validator();
			if ($this->form_validation->run()) {

				$origin = intval($post_data['origin']);
				unset($post_data['FID']);
				unset($post_data['origin']);
				$promo_code_list = array();
				$promo_code_list['module'] = trim($post_data['module']);
				$promo_code_list['promo_code'] = trim($post_data['promo_code']);
				$promo_code_list['description'] = trim($post_data['description']);
				$promo_code_list['value_type'] = trim($post_data['value_type']);
				$promo_code_list['value'] = trim($post_data['value']);
				$promo_code_list['display_home_page'] = trim($post_data['display_home_page']);
				$promo_code_list['minimum_amount'] = trim($post_data['minimum_amount']);
				$expiry_date = trim($post_data['expiry_date']);
				if (empty($expiry_date) == false && valid_date_value($expiry_date)) {
					$promo_code_list['expiry_date'] = date('Y-m-d', strtotime($expiry_date));
				} else {
					$promo_code_list['expiry_date'] = date('0000-00-00');
				}
				if (valid_array($_FILES) == true and $_FILES['promo_code_image']['error'] == 0 and $_FILES['promo_code_image']['size'] > 0) {
					if (function_exists("check_mime_image_type")) {
						if (!check_mime_image_type($_FILES['promo_code_image']['tmp_name'])) {
							echo "Please select the image files only (gif|jpg|png|jpeg)";
							exit;
						}
					}
					$config['upload_path'] = $this->template->domain_promo_image_upload_path();
					$temp_file_name = $_FILES['promo_code_image']['name'];
					$config['allowed_types'] = 'gif|jpg|png|jpeg';
					$config['file_name'] = get_domain_key() . $temp_file_name;
					$config['max_size'] = '1000000';
					$config['max_width']  = '';
					$config['max_height']  = '';
					$config['remove_spaces']  = false;
					// echo $config['upload_path'];exit;
					//UPLOAD IMAGE
					$this->load->library('upload', $config);
					$this->upload->initialize($config);
					if (! $this->upload->do_upload('promo_code_image')) {
						echo $this->upload->display_errors();
					} else {
						$image_data =  $this->upload->data();
					}
					/*UPDATING IMAGE */
					$promo_code_list['promo_code_image'] = $image_data['file_name'];
				}
				$promo_code_list['status'] = trim($post_data['status']);
				// debug($promo_code_list);exit;
				set_update_message();
				if ($origin > 0) { //Update
					$this->custom_db->update_record('promo_code_list', $promo_code_list, array('origin' => $origin));
				} else if ($origin == 0) { //Add
					$promo_code_list['created_by_id'] = $this->entity_user_id;
					$promo_code_list['created_datetime'] = db_current_datetime();
					$this->custom_db->insert_record('promo_code_list', $promo_code_list);
					set_insert_message();
				}
				redirect('utilities/manage_promo_code');
			}
		}
		//***********FILTERS***********//
		if (isset($get_data['promo_code']) == true) {
			$filter_promo_code = trim($get_data['promo_code']);
			if (empty($filter_promo_code) == false) {
				$condition[] = array('promo_code', '=', '"' . $filter_promo_code . '"');
			}
		}
		if (isset($get_data['module']) == true) {
			$filter_module = trim($get_data['module']);
			if (empty($filter_module) == false) {
				$condition[] = array('module', '=', '"' . $filter_module . '"');
			}
		}
		//***********FILTERS***********//
		$total_records = $this->module_model->promo_code_list($condition, true);
		$promo_code_list = $this->module_model->promo_code_list($condition, false, $offset, RECORDS_RANGE_2);
		/** TABLE PAGINATION */
		$this->load->library('pagination');
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['base_url'] = base_url() . 'index.php/utilities/manage_promo_code/';
		$config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
		$config['total_rows'] = $total_records;
		$config['per_page'] = RECORDS_RANGE_2;
		$this->pagination->initialize($config);
		/** TABLE PAGINATION */
		$page_data['promocode_module_options'] = $this->module_model->promocode_module_options();

		$page_data['promo_code_list'] = $promo_code_list;

		// debug($page_data['from_data']); die;
		$this->template->view('utilities/manage_promo_code', $page_data);
	}
	public function delete_promo_code(): void
	{
		$get_data = $this->input->get();

		$this->module_model->delete_promo_code($get_data['eid']);
		redirect('utilities/manage_promo_code');
	}

	/**
	 * Update Convenience Fees in application
	 */
	public function insurance_fees(): void
	{
		$page_data=[];
		$insurance=[];
		$post_data = $this->input->post();
		$temp = $this->custom_db->single_table_records('insurance');
		if (valid_array($post_data) == true) {
			if ($temp['status'] == true) {
				$insurance['amount'] = $post_data['insurance'];
				$insurance['status'] = $post_data['status'];
				$insurance['created_time'] =  date('Y-m-d H:i:s');
				$origin = $temp['data'][0]['origin'];
				$this->custom_db->update_record('insurance', $insurance, array('origin' => $origin));
			} else {
				$insurance['status'] = $post_data['status'];
				$insurance['amount'] = $post_data['insurance'];
				$insurance['created_time'] =  date('Y-m-d H:i:s');
				$this->custom_db->insert_record('insurance', $insurance);
			}
		}
		$temp = $this->custom_db->single_table_records('insurance');
		$page_data['insurance'] = $temp['data'];

		$this->template->view('utilities/insurance_fees', $page_data);
	}
	public function convenience_fees_text(): void
	{
		$page_data=[];
		$post_data = $this->input->post();
		if (valid_array($post_data) == true) {
			//debug($post_data);exit;
			$origin = $post_data['origin'];
			//echo $origin;exit;
			$this->custom_db->update_record('convenience_fees_text', array('description' => $post_data['conv_text']), array('origin' => $origin));
		}
		$convenience_fees_text = $this->custom_db->single_table_records('convenience_fees_text', '*');
		$page_data['text'] = $convenience_fees_text['data'][0]['description'];
		$page_data['origin'] = $convenience_fees_text['data'][0]['origin'];
		$this->template->view('utilities/convenience_fees_text', $page_data);
	}

	public function not_access(): void
	{
		$this->template->view('utilities/404');
	}

	public function toggle_asm_status(int $bs_id, int $mc_id, bool $status = false): void
	{

		$CI = &get_instance();
		$this->db1 = $CI->load->database('seconddb', TRUE);

		$list_data = $this->module_model->get_course_list_new(array(
			array(
				'BS.origin',
				'=',
				$bs_id
			),
			array(
				'MCL.origin',
				'=',
				$mc_id
			)
		));
		if (valid_array($list_data) == true) {
			$api_code = $list_data[0]['booking_source_id'];
			$api_name = $list_data[0]['booking_source'];
			$module_name = $list_data[0]['name'];
			if ($status == 'false') {
				$status = 'inactive';
				$status_new = 0;
				$logger_msg = $this->entity_name . ' Deactivated ' . $module_name . ' (' . $api_code . '-' . $api_name . ') API';
			} else {
				$status = 'active';
				$status_new = 1;
				$logger_msg = $this->entity_name . ' Activated ' . $module_name . ' (' . $api_code . '-' . $api_name . ') API';
			}

			$sql = "UPDATE booking_source SET booking_engine_status=$status_new WHERE origin=$bs_id";
			// debug($sql);exit;
			$this->db1->query($sql);

			$sql2 = "UPDATE activity_source_map SET status='$status' WHERE booking_source_fk = $bs_id and meta_course_list_fk = $mc_id";
			// debug($sql2);exit;
			$this->db1->query($sql2);
			set_update_message();
			$this->application_logger->api_status($logger_msg);
		}
	}
}
