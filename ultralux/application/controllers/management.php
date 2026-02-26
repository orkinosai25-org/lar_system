<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package    Provab - Provab Application
 * @subpackage Travel Portal
 * @author     Balu A <balu.provab@gmail.com>
 * @version    V2
 */
class Management extends CI_Controller
{
	private string $current_module;

	public function __construct()
	{
		parent::__construct();

		$this->load->model('domain_management_model');
		$this->load->model('hotel_model');
		$this->load->model('flight_model');

		$this->load->library('booking_data_formatter');
		$this->load->helper('custom/transaction_log');

		$this->current_module = (string) $this->config->item('current_module');
	}

	public function index(): void
	{
		redirect(base_url());
	}

	/**
	 * Manage domain markup for B2B - Domain wise and module wise
	 */
	public function b2b_airline_markup(string $page_type = ''): void
	{
		ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
		$page_data = [];
		$menu = 'My Markup';
		$sub_menu = '';

		// Set default page type
		if (empty($page_type)) {
			$page_type = 'flight_markup';
		}

		// Set submenu based on page type
		switch ($page_type) {
			case 'flight_markup':
				$sub_menu = 'Flight';
				break;
			case 'hotel_markup':
				$sub_menu = 'Hotel';
				break;
			case 'car_markup':
				$sub_menu = 'Car';
				break;
			case 'flight_commission':
				$sub_menu = 'Flight';
				break;
			case 'balance':
				$sub_menu = 'Balance';
				break;
		}

		$page_data['form_data'] = $this->input->post();

		if (!empty($page_data['form_data']) && is_array($page_data['form_data'])) {
			$form_data = $page_data['form_data'];
			$markup_module_type = $form_data['markup_type'] ?? '';

			switch ($markup_module_type) {
				case 'b2b_flight':
					$page_type = 'flight_markup';
					$sub_menu = 'Flight';

					switch ($form_data['form_values_origin']) {
						case 'generic':
							$markup_origin = 0;
							if($form_data['markup_origin'] !=''){
$markup_origin = $form_data['markup_origin'];

							}
							$this->domain_management_model->save_markup_data(
								$markup_origin,
								'generic',
								$markup_module_type,
								0,
								$form_data['generic_value'],
								$form_data['value_type'],
								get_domain_auth_id()
							);
							break;

						case 'specific':
							if (!empty($form_data['airline_origin']) && is_array($form_data['airline_origin'])) {
								foreach ($form_data['airline_origin'] as $k => $domain_origin) {
									$value = $form_data['specific_value'][$k] ?? '';
									$value_type = $form_data['value_type_' . $domain_origin] ?? '';

									if ($value != '' && intval($value) > -1 && !empty($value_type)) {
											$markup_origin = 0;
							if($form_data['markup_origin'][$k] !=''){
$markup_origin = $form_data['markup_origin'][$k];

							}
										$this->domain_management_model->save_markup_data(
											$markup_origin,
											'specific',
											$markup_module_type,
											$domain_origin,
											$value,
											$value_type,
											get_domain_auth_id()
										);
									}
								}
							}
							break;

						case 'add_airline':
							if (!empty($form_data['airline_code'])) {
								$airline_code = trim($form_data['airline_code']);
								$markup_details = $this->domain_management_model->individual_airline_markup_details($markup_module_type, $airline_code);
								$airline_list_origin = intval($markup_details['airline_list_origin']);
								$markup_list_origin = intval($markup_details['markup_list_origin'] ?? 0);

								$this->domain_management_model->save_markup_data(
									$markup_list_origin,
									'specific',
									$markup_module_type,
									$airline_list_origin,
									$form_data['specific_value'],
									$form_data['value_type'],
									get_domain_auth_id()
								);
							}
							break;
					}

					redirect(base_url('index.php/management/' . __FUNCTION__));
					break;

				case 'b2b_hotel':
					$page_type = 'hotel_markup';
					$sub_menu = 'Hotel';

					if ($form_data['form_values_origin'] == 'generic') {
						$this->domain_management_model->save_markup_data(
							(int) $form_data['markup_origin'],
							'generic',
							$markup_module_type,
							0,
							$form_data['generic_value'],
							$form_data['value_type'],
							get_domain_auth_id()
						);
					}
					break;

				case 'b2b_car':
					$page_type = 'car_markup';
					$sub_menu = 'Car';

					if ($form_data['form_values_origin'] == 'generic') {
						$this->domain_management_model->save_markup_data(
							(int)$form_data['markup_origin'],
							'generic',
							$markup_module_type,
							0,
							$form_data['generic_value'],
							$form_data['value_type'],
							get_domain_auth_id()
						);
					}

					redirect(base_url('index.php/management/' . __FUNCTION__));
					break;
			}
		}

		// Load data for the view
		$data_list = $this->domain_management_model->get_agent_airline_markup_details();
		$data_list['hotel_markup_list'] = $this->domain_management_model->hotel_markup();
		$data_list['car_markup_list'] = $this->domain_management_model->car_markup();
		$data_list['airline_list'] = $this->db_cache_api->get_airline_list();

		$bank_data = $this->domain_management_model->bank_account_details();
		$data_list['table_data'] = $bank_data['status'] ? $bank_data['data'] : [];

		$flight_commission_details = $this->domain_management_model->flight_commission_details();
		$data_list['commission_details'] = $flight_commission_details['data'];

		$data_list['page_type'] = $page_type;
		$data_list['menu'] = $menu;
		$data_list['sub_menu'] = $sub_menu;

		$this->template->view('management/b2b_airline_markup', $data_list);
	}
	/**
	 * Manage domain markup for B2B Hotel - Generic and domain-wise
	 */
	public function b2b_hotel_markup(): void
	{
		$markup_module_type = 'b2b_hotel';

		$form_data = $this->input->post();

		if (!empty($form_data) && is_array($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';

			if ($form_values_origin == 'generic') {
				$this->domain_management_model->save_markup_data(
					$form_data['markup_origin'],
					'generic',
					$markup_module_type,
					0,
					$form_data['generic_value'],
					$form_data['value_type'],
					get_domain_auth_id()
				);
			}

			// Redirect after save
			redirect(base_url('index.php/management/' . __FUNCTION__));
		}

		// Load all hotel markup data
		$data_list = $this->domain_management_model->hotel_markup();

		// Render view
		$this->template->view('management/b2b_hotel_markup', $data_list);
	}
	/**
	 * Manage domain markup for B2B Sightseeing - Generic and domain-wise
	 */
	public function b2b_sightseeing_markup(): void
	{
		$markup_module_type = 'b2b_sightseeing';
		$form_data = $this->input->post();

		if (!empty($form_data) && is_array($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';

			if ($form_values_origin == 'generic') {
				$this->domain_management_model->save_markup_data(
					$form_data['markup_origin'],
					'generic',
					$markup_module_type,
					0,
					$form_data['generic_value'],
					$form_data['value_type'],
					get_domain_auth_id()
				);
			}

			// Redirect after processing form submission
			redirect(base_url('index.php/management/' . __FUNCTION__));
		}

		// Load markup data for Sightseeing
		$data_list = $this->domain_management_model->sightseeing_markup();

		// Load the view with data
		$this->template->view('management/b2b_sightseeing_markup', $data_list);
	}
	/**
	 * Manage domain markup for B2B Transfers - Generic and domain wise
	 */
	public function b2b_transfer_markup(): void
	{
		$markup_module_type = 'b2b_transferv1';
		$form_data = $this->input->post();

		if (!empty($form_data) && is_array($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';

			if ($form_values_origin == 'generic') {
				$this->domain_management_model->save_markup_data(
					$form_data['markup_origin'],
					'generic',
					$markup_module_type,
					0,
					$form_data['generic_value'],
					$form_data['value_type'],
					get_domain_auth_id()
				);
			}

			redirect(base_url('index.php/management/' . __FUNCTION__));
		}

		// Fetch markup data and load the view
		$data_list = $this->domain_management_model->transfer_markup();
		$this->template->view('management/b2b_transfer_markup', $data_list);
	}
	/**
	 * Manage domain markup for B2B Car - Generic and domain wise
	 * Author: Anitha G
	 */
	public function b2b_car_markup(): void
	{
		$markup_module_type = 'b2b_car';
		$form_data = $this->input->post();

		if (!empty($form_data) && is_array($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';

			if ($form_values_origin == 'generic') {
				$this->domain_management_model->save_markup_data(
					$form_data['markup_origin'],
					'generic',
					$markup_module_type,
					0,
					$form_data['generic_value'],
					$form_data['value_type'],
					get_domain_auth_id()
				);
			}

			redirect(base_url('index.php/management/' . __FUNCTION__));
		}

		$data_list = $this->domain_management_model->car_markup();
		$this->template->view('management/b2b_car_markup', $data_list);
	}
	/**
	 * Balu A
	 * Manage Balance history and other details of domain with provab
	 */
	public function b2b_balance_manager(string $balance_request_type = 'Cash'): void
	{
		$page_data = [];
		$params = $this->input->get();
		$page_data['form_data'] = $this->input->post();

		switch (strtoupper($balance_request_type)) {
			case 'CHECK___DD':
				$page_data['balance_page_obj'] = new Provab_Page_Loader('balance_request_check');
				break;
			case 'ETRANSFER':
				$page_data['balance_page_obj'] = new Provab_Page_Loader('balance_request_e_transfer');
				break;
			case 'CASH':
				$page_data['balance_page_obj'] = new Provab_Page_Loader('balance_request_cash');
				break;
			default:
				redirect(base_url());
				return;
		}

		if (valid_array($page_data['form_data'])) {
			$page_data['balance_page_obj']->set_auto_validator();

			if ($this->form_validation->run()) {
				$page_data['form_data']['transaction_type'] = $page_data['form_data']['transaction_type'];

				if ((int) $page_data['form_data']['origin'] == 0) {
					// Get the conversion rate with respect to admin currency
					$agent_deposit_currency_details = $this->convert_agent_deposit_currency($page_data['form_data']['amount']);
					$page_data['form_data']['currency'] = $agent_deposit_currency_details['currency'];
					$page_data['form_data']['currency_conversion_rate'] = $agent_deposit_currency_details['currency_conversion_rate'];
					$page_data['form_data']['amount'] = $agent_deposit_currency_details['amount'];

					// Insert
					$insert_id = $this->domain_management_model->save_master_transaction_details($page_data['form_data']);
				} elseif ((int) $page_data['form_data']['origin'] > 0) {
					// FIXME :: Update Not Needed As Of Now
					$insert_id = null;
				}

				// Slip Upload
				$this->deposit_slip_upload($insert_id);
				redirect(base_url() . 'index.php/management/' . __FUNCTION__ . '/' . $balance_request_type);
				return;
			}
		}

		// Filter params
		$data_list_filt = [];

		if (!empty($params['system_transaction_id'])) {
			$data_list_filt[] = ['MTD.system_transaction_id', 'like', $this->db->escape('%' . $params['system_transaction_id'] . '%')];
		}

		if (!empty($params['status']) && strtolower($params['status']) != 'all') {
			$data_list_filt[] = ['MTD.status', '=', $this->db->escape($params['status'])];
		}

		if (!empty($params['created_datetime_from'])) {
			$data_list_filt[] = ['MTD.created_datetime', '>=', $this->db->escape(db_current_datetime($params['created_datetime_from']))];
		}

		if (!empty($params['created_datetime_to'])) {
			$data_list_filt[] = ['MTD.created_datetime', '<=', $this->db->escape(db_current_datetime($params['created_datetime_to']))];
		}

		// Get and format table data
		$page_data['table_data'] = $this->domain_management_model->master_transaction_request_list($data_list_filt);
		$page_data['table_data'] = $this->booking_data_formatter->format_master_transaction_balance($page_data['table_data'], $this->current_module);

		// Additional data for view
		$page_data['balance_request_type'] = strtoupper($balance_request_type);
		$page_data['provab_balance_requests'] = get_enum_list('provab_balance_requests');
		$page_data['status_options'] = get_enum_list('provab_balance_status');
		$page_data['search_params'] = $params;

		if (empty($page_data['form_data']['currency_converter_origin'])) {
			$page_data['form_data']['currency_converter_origin'] = COURSE_LIST_DEFAULT_CURRENCY;
			$page_data['form_data']['conversion_value'] = 1;
		}

		$page_data['form_data']['transaction_type'] = $balance_request_type;

		// Render view
		$this->template->view('management/master_balance_manager_new', $page_data);
	}
	/**
	 * Sagar Wakchaure
	 * Get conversion rate with respect to admin currency
	 *
	 * @param float $deposit_amount
	 * @return array{currency_conversion_rate: float, currency: string, amount: float}
	 */
	public function convert_agent_deposit_currency(float $deposit_amount): array
	{
		$currency_obj = new Currency();

		$currency_conversion_rate = $currency_obj->transaction_currency_conversion_rate();
		$converted_amount = $deposit_amount * $currency_obj->currency_conversion_value(
			false,
			agent_base_currency(),
			admin_base_currency()
		);

		return [
			'currency_conversion_rate' => $currency_conversion_rate,
			'currency' => agent_base_currency(),
			'amount' => $converted_amount
		];
	}
	/**
	 * Uploads the deposit slip image and updates the corresponding record
	 *
	 * @param int $origin
	 * @return void
	 */
	public function deposit_slip_upload(int $origin): void
	{
		if (
			!empty($_FILES) &&
			isset($_FILES['image']) &&
			$_FILES['image']['error'] == UPLOAD_ERR_OK &&
			$_FILES['image']['size'] > 0
		) {
			// Validate MIME type
			if (function_exists("check_mime_image_type")) {
				if (!check_mime_image_type($_FILES['image']['tmp_name'])) {
					echo "Please select only image files (gif|jpg|png|jpeg)";
					exit;
				}
			}

			$upload_path = $this->template->domain_image_upload_path() . 'deposit_slips/';
			$config = [
				'upload_path'     => $upload_path,
				'allowed_types'   => 'gif|jpg|png|jpeg',
				'file_name'       => (string) time(),
				'max_size'        => '1000000',
				'remove_spaces'   => false,
				'max_width'       => '',
				'max_height'      => '',
			];

			// Remove old image
			$temp_record = $this->custom_db->single_table_records(
				'master_transaction_details',
				'image',
				['origin' => $origin]
			);

			if (!empty($temp_record['data'][0]['image'])) {
				$existing_file = $this->template->domain_image_full_path($temp_record['data'][0]['image']);
				if (file_exists($existing_file)) {
					unlink($existing_file);
				}
			}

			// Upload new image
			$this->load->library('upload', $config);
			if (!$this->upload->do_upload('image')) {
				echo $this->upload->display_errors();
				return;
			}

			$image_data = $this->upload->data();
			$this->custom_db->update_record(
				'master_transaction_details',
				['image' => $image_data['file_name']],
				['origin' => $origin]
			);
		}
	}
	/**
	 * Balu A
	 */
	public function set_balance_alert(): void
	{
		
            $get_data = $this->input->get();
       
        // Retrieve POST parameters
        $post_data = $get_data;
		//$post_data = $this->input->post();
        // Debug: log or print the received data
      
        

		$page_data = [];
		$data = [];

		$page_data['balance_alert_page_obj'] = new Provab_Page_Loader('set_balance_alert');

		if (valid_array($post_data)) {
			
			$page_type = $post_data['page_type'] ?? '';

			if (isset($post_data['threshold_amount'])) {
				//$page_data['balance_alert_page_obj']->set_auto_validator();

			//if ($this->form_validation->run()) {
					$origin = (int)($post_data['origin'] ?? 0);
					$agent_balance_alert_details = [
						'threshold_amount' => trim($post_data['threshold_amount']),
						'mobile_number' => trim($post_data['mobile_number']),
						'email_id' => trim($post_data['email_id']),
						'enable_sms_notification' => trim($post_data['enable_sms_notification'][0] ?? ''),
						'enable_email_notification' => trim($post_data['enable_email_notification'][0] ?? ''),
						'created_by_id' => $this->entity_user_id,
						'created_datetime' => date('Y-m-d H:i:s')
					];

					if ($origin > 0) {
						// UPDATE
						$update_data = $this->custom_db->update_record('agent_balance_alert_details', $agent_balance_alert_details, [
							'agent_fk' => $this->entity_user_id
						]);
						//echo $this->db->last_query();exit;
					} else {
						// ADD
						$agent_balance_alert_details['agent_fk'] = $this->entity_user_id;
						
						$this->custom_db->insert_record('agent_balance_alert_details', $agent_balance_alert_details);
					}

					redirect('management/set_balance_alert?uid=1196');
				//}
			}

			if ($page_type == "logo_upload") {
				$GLOBALS['CI']->template->domain_images();

				if ((int)$post_data['origin'] == get_domain_auth_id() && get_domain_auth_id() > 0) {
					$domain_origin = get_domain_auth_id();

					if (valid_array($_FILES) && $_FILES['domain_logo']['error'] == 0 && $_FILES['domain_logo']['size'] > 0) {
						if (function_exists("check_mime_image_type") && !check_mime_image_type($_FILES['domain_logo']['tmp_name'])) {
							echo "Please select image files only (gif|jpg|png|jpeg)";
							exit;
						}

						$config = [
							'upload_path' => $this->template->domain_image_upload_path(),
							'allowed_types' => 'gif|jpg|png|jpeg',
							'file_name' => get_domain_key() . $_FILES['domain_logo']['name'],
							'max_size' => MAX_DOMAIN_LOGO_SIZE,
							'max_width' => MAX_DOMAIN_LOGO_WIDTH,
							'max_height' => MAX_DOMAIN_LOGO_HEIGHT,
							'remove_spaces' => false
						];

						// Delete old logo
						$temp_record = $this->custom_db->single_table_records('ultralux_user_details', 'logo', ['user_oid' => (int)$this->entity_user_id]);
						$domain_logo = $temp_record['data'][0]['logo'] ?? '';
						if (!empty($domain_logo)) {
							$temp_domain_logo = $this->template->domain_image_full_path($domain_logo);
							if (file_exists($temp_domain_logo)) {
								unlink($temp_domain_logo);
							}
						}

						// Upload image
						$this->load->library('upload', $config);
						$this->upload->initialize($config);

						if (!$this->upload->do_upload('domain_logo')) {
							echo $this->upload->display_errors();
						} else {
							$image_data = $this->upload->data();
							$this->custom_db->update_record('ultralux_user_details', ['logo' => $image_data['file_name']], ['user_oid' => (int)$this->entity_user_id]);
						}

						refresh();
					}
				}
			}
		}

		// UID-based processing
		if (isset($get_data['uid'])) {
			$page_data['form_data'] = $this->input->post() ?? [];

			if (empty($page_data['form_data']['first_name'] ?? '')) {
				$page_data['form_data'] = [];
			}

			$get_data['uid'] = (int)$get_data['uid'];

			if (!valid_array($page_data['form_data'])) {
				$cond = [['U.user_id', '=', $get_data['uid']]];
				$edit_data = $this->user_model->get_user_details($cond);

				if (valid_array($edit_data)) {
					$page_data['form_data'] = $edit_data[0];
					$page_data['form_data']['uuid'] = provab_decrypt($page_data['form_data']['uuid']);
					$page_data['form_data']['email'] = provab_decrypt($page_data['form_data']['email']);
				} else {
					redirect('security/log_event');
				}
			} elseif (check_default_edit_privilege($get_data['uid']) || super_privilege()) {
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
					if ($get_data['uid'] == (int)($page_data['form_data']['user_id'])) {
						// Application logger
						$notification_users = $this->user_model->get_admin_user_id();
						$remarks = $page_data['form_data']['first_name'] . ' Updated Profile Details';
						$action_query_string = [
							'user_id' => $this->entity_user_id,
							'uuid' => $this->entity_uuid,
							'user_type' => ULTRALUX_USER
						];
						$this->application_logger->profile_update(
							$page_data['form_data']['first_name'],
							$remarks,
							$action_query_string,
							[],
							$this->entity_user_id,
							$notification_users
						);

						$user_id = (int)$page_data['form_data']['user_id'];
						unset($page_data['form_data']['FID'], $page_data['form_data']['email'], $page_data['form_data']['uuid'], $page_data['form_data']['user_id']);

						$page_data['form_data']['date_of_birth'] = date('Y-m-d', strtotime($page_data['form_data']['date_of_birth']));

						$this->custom_db->update_record('user', $page_data['form_data'], ['user_id' => $user_id]);

						$this->session->set_flashdata(['message' => 'AL004', 'type' => SUCCESS_MESSAGE]);

						if (valid_array($_FILES) && $_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
							if (function_exists("check_mime_image_type") && !check_mime_image_type($_FILES['image']['tmp_name'])) {
								echo "Please select image files only (gif|jpg|png|jpeg)";
								exit;
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
							$icon = $temp_record['data'][0]['image'] ?? '';

							if (!empty($icon)) {
								$temp_profile_image = $this->template->domain_image_full_path($icon);
								if (file_exists($temp_profile_image)) {
									unlink($temp_profile_image);
								}
							}

							$this->load->library('upload', $config);

							if (!$this->upload->do_upload('image')) {
								$message = $this->upload->display_errors();
								if ($message == '<p>The filetype you are attempting to upload is not allowed.</p>') {
									$this->session->set_flashdata(['message' => 'AL005', 'type' => FAILURE_MESSAGE]);
								}
							} else {
								$image_data = $this->upload->data();
								$this->custom_db->update_record('user', ['image' => $image_data['file_name']], ['user_id' => $user_id]);
							}

							refresh();
						}
					} else {
						redirect('security/log_event');
					}
				}
			}

			$page_data['change_page_obj'] = new Provab_Page_Loader('change_password');
			$page_data['country_code_list'] = $this->db_cache_api->get_country_code_list();
			$country_code = $this->db_cache_api->get_country_code_list_profile();
			$mobile_code = $this->db_cache_api->get_mobile_code($page_data['form_data']['country_code']);
			$page_data['mobile_code'] = $mobile_code;

			$phone_code_array = [];
			foreach ($country_code['data'] as $c_value) {
				$phone_code_array[$c_value['origin']] = $c_value['name'] . ' ' . $c_value['country_code'];
			}

			$page_data['phone_code_array'] = $phone_code_array;

			$form_data = $this->input->post();
			if (valid_array($form_data) && isset($form_data['new_password'])) {
				$this->load->library('form_validation');
				$this->form_validation->set_rules('current_password', 'Current Password', 'required|min_length[5]|max_length[45]|callback_password_check');
				$this->form_validation->set_rules('new_password', 'New Password', 'matches[confirm_password]|min_length[5]|max_length[45]|required|callback_valid_password');
				$this->form_validation->set_rules('confirm_password', 'Confirm', 'callback_check_new_password');

				if ($this->form_validation->run()) {
					$table_name = "user";
					$condition = [
						'password' => provab_encrypt(md5(trim($this->input->post('new_password')))),
						'user_id' => $user_id
					];

					$check_pwd = $this->custom_db->single_table_records($table_name, 'password', $condition);
					if ($check_pwd['status'] == false) {
						$condition['password'] = provab_encrypt(md5(trim($this->input->post('current_password'))));
						$data['password'] = provab_encrypt(md5(trim($this->input->post('new_password'))));
						$update_res = $this->custom_db->update_record($table_name, $data, $condition);

						if ($update_res) {
							$this->session->set_flashdata(['message' => 'Password Changed Successfully', 'type' => SUCCESS_MESSAGE, 'override_app_msg' => true]);
							refresh();
						} else {
							$this->session->set_flashdata(['message' => 'Invalid Current Password', 'type' => ERROR_MESSAGE, 'override_app_msg' => true]);
							refresh();
						}
					} else {
						$this->session->set_flashdata(['message' => 'Current Password and New Password Are Same', 'type' => WARNING_MESSAGE, 'override_app_msg' => true]);
						refresh();
					}
				}
			}
		}

		// Load balance alert details
		$temp_alert_details = $this->custom_db->single_table_records('agent_balance_alert_details', '*', ['agent_fk' => $this->entity_user_id]);
		if ($temp_alert_details['status']) {
			$page_data['balance_alert_details'] = $temp_alert_details['data'][0];
			$form_data = $temp_alert_details['data'][0];
		} else {
			$page_data['balance_alert_details'] = '';
			$form_data['origin'] = 0;
		}

		$page_data['form_data_balance'] = $form_data;

		// Load domain logo
		$temp_details = $this->custom_db->single_table_records('ultralux_user_details', 'logo', ['user_oid' => (int)$this->entity_user_id]);
		$page_data['domain_logo'] = $temp_details['data'][0]['logo'] ?? '';

		$this->template->view('management/set_balance_alert', $page_data);
	}
	/**
	 * Sachin
	 * Account Ledger (transactions) search by date
	 */
	public function account_ledger(int $offset = 0): void
	{
		ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
		$get_data = $this->input->get();
		$condition = [];
		$page_data = [];
		$config = [];
		$user_id = $this->entity_user_id;

		// From-Date and To-Date
		$from_date = trim($get_data['created_datetime_from'] ?? '');
		$to_date = trim($get_data['created_datetime_to'] ?? '');

		// Auto swipe date
		if (!empty($from_date) && !empty($to_date)) {
			$valid_dates = auto_swipe_dates($from_date, $to_date);
			$from_date = $valid_dates['from_date'];
			$to_date = $valid_dates['to_date'];
		}

		if (!empty($from_date)) {
			$ymd_from_date = date('Y-m-d', strtotime($from_date));
			$condition[] = ['date(TL.created_datetime)', '>=', $this->db->escape($ymd_from_date)];
		}

		if (!empty($to_date)) {
			$ymd_to_date = date('Y-m-d', strtotime($to_date));
			$condition[] = ['date(TL.created_datetime)', '<=', $this->db->escape($ymd_to_date)];
		}

		if (!empty($get_data['app_reference'])) {
			$condition[] = ['TL.app_reference', 'like', $this->db->escape('%' . $get_data['app_reference'] . '%')];
		}

		if (!empty($get_data['transaction_type'])) {
			$condition[] = ['TL.transaction_type', 'like', $this->db->escape('%' . $get_data['transaction_type'] . '%')];
		}



		// Transaction Data
		$total_data = $this->domain_management_model->agent_account_ledger($condition, true);
		$total_records = $total_data['total_records'] ?? 0;

		$transactions = $this->domain_management_model->agent_account_ledger($condition, false, $offset, RECORDS_RANGE_3);
		$formatted_transactions = format_account_ledger($transactions['data'] ?? []);

		$page_data['table_data'] = $formatted_transactions['data'] ?? [];

		// Table Pagination
		$this->load->library('pagination');

		if (!empty($_GET)) {
			$config['suffix'] = '?' . http_build_query($_GET, '', "&");
		}

		$config['base_url'] = base_url() . 'management/account_ledger/';
		$config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
		$page_data['total_records'] = $config['total_rows'] = $total_records;
		$config['per_page'] = RECORDS_RANGE_2;

		$this->pagination->initialize($config);

		$page_data['search_params'] = $get_data;

		$this->template->view('management/account_legder_new', $page_data);
	}
	/**
	 * Sachin
	 * Export Account Ledger details to Excel or PDF Format
	 */
	public function export_account_ledger(string $op = ''): void
{
	$get_data = $this->input->get();
	$condition = [];

	// From-Date and To-Date
	$from_date = trim($get_data['created_datetime_from'] ?? '');
	$to_date = trim($get_data['created_datetime_to'] ?? '');

	// Auto swipe date
	if (!empty($from_date) && !empty($to_date)) {
		$valid_dates = auto_swipe_dates($from_date, $to_date);
		$from_date = $valid_dates['from_date'];
		$to_date = $valid_dates['to_date'];
	}

	if (!empty($from_date)) {
		$ymd_from_date = date('Y-m-d', strtotime($from_date));
		$condition[] = ['date(TL.created_datetime)', '>=', $this->db->escape($ymd_from_date)];
	}

	if (!empty($to_date)) {
		$ymd_to_date = date('Y-m-d', strtotime($to_date));
		$condition[] = ['date(TL.created_datetime)', '<=', $this->db->escape($ymd_to_date)];
	}

	if (!empty($get_data['app_reference'])) {
		$condition[] = ['TL.app_reference', 'like', $this->db->escape('%' . $get_data['app_reference'] . '%')];
	}

	if (!empty($get_data['transaction_type'])) {
		$condition[] = ['TL.transaction_type', 'like', $this->db->escape('%' . $get_data['transaction_type'] . '%')];
	}

	// Fetch and format data
	$transaction_logs = $this->domain_management_model->agent_account_ledger($condition, false);
	$formatted_logs = format_account_ledger($transaction_logs['data'] ?? []);
	$export_data = $formatted_logs['data'] ?? [];

	if ($op == 'excel') {
		// Excel Export
		$headings = [
			'a1' => 'Sl. No.',
			'b1' => 'Date',
			'c1' => 'Reference Number',
			'd1' => 'Description',
			'e1' => 'Debit',
			'f1' => 'Credit',
			'g1' => 'Opening Balance',
			'h1' => 'Closing Balance'
		];

		$fields = [
			'a' => '', // Sl. No. placeholder
			'b' => 'transaction_date',
			'c' => 'reference_number',
			'd' => 'full_description',
			'e' => 'debit_amount',
			'f' => 'credit_amount',
			'g' => 'opening_balance',
			'h' => 'closing_balance'
		];

		$excel_props = [
			'title' => 'Account_Ledger_' . date('d-M-Y'),
			'creator' => 'Provab',
			'description' => 'Account Ledger',
			'sheet_title' => 'Account Ledger'
		];

		$this->load->library('provab_excel');
		$this->provab_excel->excel_export($headings, $fields, $export_data, $excel_props);
		exit();
	}

	// PDF Export
	$columns = [
		'transaction_date' => 'Date',
		'reference_number' => 'Reference Number',
		'full_description' => 'Description',
		'debit_amount' => 'Debit',
		'credit_amount' => 'Credit',
		'opening_balance' => 'Opening Balance',
		'closing_balance' => 'Closing Balance'
	];

	$pdf_data = format_pdf_data($export_data, $columns);
	$this->load->library('provab_pdf');
	$view_html = $this->template->isolated_view('report/table', $pdf_data);
	$this->provab_pdf->create_pdf($view_html, 'D', 'Account_Ledger');
	exit();
}

	/**
	 * Pravinkumar
	 * PNR/Transaction Search
	 */
	public function pnr_search(): void
	{
		$get_data = $this->input->get();

		$filter_report_data = $get_data['filter_report_data'] ?? '';
		$module = $get_data['module'] ?? '';

		if ($filter_report_data != '' && $module != '') {
			$query_string = http_build_query([
				'module' => $module,
				'filter_report_data' => $filter_report_data
			]);

			// Redirect based on module
			switch ($module) {
				case PROVAB_FLIGHT_BOOKING_SOURCE:
					redirect("report/flight?$query_string");
					break;
				case PROVAB_HOTEL_BOOKING_SOURCE:
					redirect("report/hotel?$query_string");
					break;
				case PROVAB_BUS_BOOKING_SOURCE:
					redirect("report/bus?$query_string");
					break;
				case PROVAB_TRANSFERV1_BOOKING_SOURCE:
					redirect("report/transfers?$query_string");
					break;
				case PROVAB_SIGHTSEEN_BOOKING_SOURCE:
					redirect("report/activities?$query_string");
					break;
				default:
					refresh(); // fallback in case of unexpected module
			}
		}

		// Load default view if module or data not set
		$this->template->view('management/pnr_search_new');
	}
	/**
	 * Balu A
	 * Flight Commission for Agent
	 */
	public function flight_commission(): void
	{
		$page_data = [];
		$flight_commission_details = $this->domain_management_model->flight_commission_details();
		$page_data['commission_details'] = $flight_commission_details['data'] ?? [];
		$this->template->view('management/flight_commission', $page_data);
	}

	/**
	 * Balu A
	 * Bus Commission for Agent
	 */

	/**
	 * Elavarasi
	 * Sightseeing Commission for Agent
	 */
	/**
	 * Elavarasi
	 * Transfers Commission for Agent
	 */
	

	/**
	 * Balu A
	 * Bank Account Details
	 */
	public function bank_account_details(): void
	{
		$page_data = [];
		$temp_data = $this->domain_management_model->bank_account_details();
		$page_data['table_data'] = ($temp_data['status'] ?? false) ? $temp_data['data'] : '';
		$this->template->view('management/bank_account_details', $page_data);
	}
	/**
	 * Anitha G
	 * Credit Limit
	 */
	public function b2b_credit_limit(): void
	{
		$page_data = [];
		$page_data['form_data'] = $this->input->post();
		$page_data['balance_page_obj'] = new Provab_Page_Loader('credit_manager');

		if (valid_array($page_data['form_data'])) {
			$page_data['balance_page_obj']->set_auto_validator();

			if ($this->form_validation->run()) {
				$page_data['form_data']['transaction_type'] = 'Credit';
				$page_data['form_data']['bank'] = 'Credit';
				$page_data['form_data']['branch'] = 'Credit';
				$page_data['form_data']['date_of_transaction'] = date('Y-m-d');
				$page_data['form_data']['deposited_branch'] = 'Credit';

				// Get the conversion rate with respect to admin currency
				$agent_deposit_currency_details = $this->convert_agent_deposit_currency($page_data['form_data']['amount']);
				$page_data['form_data']['currency'] = $agent_deposit_currency_details['currency'];
				$page_data['form_data']['currency_conversion_rate'] = $agent_deposit_currency_details['currency_conversion_rate'];
				$page_data['form_data']['amount'] = $agent_deposit_currency_details['amount'];

				// Insert
				$insert_id = $this->domain_management_model->save_master_transaction_details($page_data['form_data'], 'Credit');
				$balance_request_type = isset($balance_request_type)? $balance_request_type :'';
				// Note: $balance_request_type is not defined in the snippet, ensure it is declared properly
				redirect(base_url() . 'index.php/management/' . __FUNCTION__ . '/' . ($balance_request_type ?? ''));
				return; // good practice to return after redirect
			}
		}

		$params = $this->input->get();

		$data_list_filt = [];

		if (!empty($params['system_transaction_id'] ?? null)) {
			$data_list_filt[] = ['MTD.system_transaction_id', 'like', $this->db->escape('%' . $params['system_transaction_id'] . '%')];
		}
		if (!empty($params['status'] ?? null) && strtolower($params['status']) != 'all') {
			$data_list_filt[] = ['MTD.status', '=', $this->db->escape($params['status'])];
		}
		if (!empty($params['created_datetime_from'] ?? null)) {
			$data_list_filt[] = ['MTD.created_datetime', '>=', $this->db->escape(db_current_datetime($params['created_datetime_from']))];
		}
		if (!empty($params['created_datetime_to'] ?? null)) {
			$data_list_filt[] = ['MTD.created_datetime', '<=', $this->db->escape(db_current_datetime($params['created_datetime_to']))];
		}

		if (empty($page_data['form_data']['currency_converter_origin'] ?? null)) {
			$page_data['form_data']['currency_converter_origin'] = COURSE_LIST_DEFAULT_CURRENCY;
			$page_data['form_data']['conversion_value'] = 1;
		}

		$page_data['table_data'] = $this->domain_management_model->master_transaction_request_list($data_list_filt, 'Credit');
		$page_data['table_data'] = $this->booking_data_formatter->format_master_transaction_balance($page_data['table_data'], $this->current_module);

		$page_data['search_params'] = $params;
		$page_data['status_options'] = get_enum_list('provab_balance_status');

		$this->template->view('management/b2b_credit_limit_new', $page_data);
	}
}
