<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 * @package    Provab - Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com> on 01-06-2015
 * @version    V2
 */

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

class Management extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('domain_management_model');
		$this->load->helper('custom/transaction_log');
		$this->load->helper('url');
		//$this->load->helper('download');
		//$this->load->library('excel');
		//$this->output->enable_profiler(TRUE);

	}

	/**
	 * Balu A
	 * Manage domain markup for provab - Domain wise and module wise
	 */

	public function b2c_airline_markup(): void
	{
		$page_data = [];
		$markup_module_type = 'b2c_flight';
		$page_data['form_data'] = $this->input->post();

		if (is_array($page_data['form_data']) && !empty($page_data['form_data'])) {
			$form_data = $page_data['form_data'];

			switch ($form_data['form_values_origin']) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						(int)$form_data['markup_origin'],
						$form_data['form_values_origin'],
						$markup_module_type,
						0,
						$form_data['generic_value'],
						$form_data['value_type'],
						get_domain_auth_id()
					);
					break;

				case 'specific':
					if (isset($form_data['airline_origin']) && is_array($form_data['airline_origin'])) {
						foreach ($form_data['airline_origin'] as $k => $domain_origin) {
							$specific_value = $form_data['specific_value'][$k] ?? '';
							$value_type_key = 'value_type_' . $domain_origin;
							$value_type = $form_data[$value_type_key] ?? '';

							if ($specific_value != '' && intval($specific_value) > -1 && $value_type != '') {
								$this->domain_management_model->save_markup_data(
									(int)$form_data['markup_origin'][$k],
									$form_data['form_values_origin'],
									$markup_module_type,
									$domain_origin,
									$specific_value,
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

						$airline_list_origin = isset($markup_details['airline_list_origin']) ? intval($markup_details['airline_list_origin']) : 0;
						$markup_list_origin = isset($markup_details['markup_list_origin']) && intval($markup_details['markup_list_origin']) > 0
							? intval($markup_details['markup_list_origin'])
							: 0;

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

			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Load view data
		$data_list = $this->domain_management_model->b2c_airline_markup();
		$airline_list = $this->db_cache_api->get_airline_list();
		$data_list['data']['airline_list'] = $airline_list;

		$this->template->view('management/b2c_airline_markup', $data_list['data']);
	}

	/**
	 * Balu A
	 * Manage domain markup for provab - Domain wise and module wise
	 */

	public function b2c_hotel_markup(): void
	{
		$markup_module_type = 'b2c_hotel';
		$form_data = $this->input->post();

		if (is_array($form_data) && !empty($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0,
						$generic_value,
						$value_type,
						get_domain_auth_id()
					);
					break;
			}

			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Hotel would have All - general and domain wise markup
		$data_list = $this->domain_management_model->b2c_hotel_markup();
		$this->template->view('management/b2c_hotel_markup', $data_list['data']);
	}


	/**
	 * Anitha G
	 * Manage domain markup for provab - Domain wise and module wise
	 */

	public function b2c_car_markup(): void
	{
		$markup_module_type = 'b2c_car';
		$form_data = $this->input->post();

		if (is_array($form_data) && !empty($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0,
						$generic_value,
						$value_type,
						get_domain_auth_id()
					);
					break;
			}

			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Car would have All - general and domain wise markup
		$data_list = $this->domain_management_model->b2c_car_markup();
		$this->template->view('management/b2c_car_markup', $data_list['data']);
	}

	/**
	 * Elavarasi
	 * Manage domain markup for provab - Domain wise and module wise
	 */

	public function b2c_sightseeing_markup(): void
	{
		$markup_module_type = 'b2c_sightseeing';
		$form_data = $this->input->post();

		if (is_array($form_data) && !empty($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0,
						$generic_value,
						$value_type,
						get_domain_auth_id()
					);
					break;
			}

			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Sightseeing would have All - general and domain wise markup
		$data_list = $this->domain_management_model->b2c_sightseeing_markup();
		$this->template->view('management/b2c_sightseeing_markup', $data_list['data']);
	}

	/**
	 * Elavarasi
	 * Manage domain markup for provab - Domain wise and module wise
	 */

	public function b2c_transfer_markup(): void
	{
		$markup_module_type = 'b2c_transferv1';
		$form_data = $this->input->post();

		if (is_array($form_data) && !empty($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0,
						$generic_value,
						$value_type,
						get_domain_auth_id()
					);
					break;
			}

			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Transfer would have All - general and domain wise markup
		$data_list = $this->domain_management_model->b2c_transferv1_markup();
		$this->template->view('management/b2c_transferv1_markup', $data_list['data']);
	}

	/**
	 * Balu A
	 * Manage domain markup for provab - Domain wise and module wise
	 */

	public function b2c_bus_markup(): void
	{
		$markup_module_type = 'b2c_bus';
		$form_data = $this->input->post();

		if (is_array($form_data) && !empty($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0,
						$generic_value,
						$value_type,
						get_domain_auth_id()
					);
					break;
			}

			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Bus would have All - general and domain wise markup
		$data_list = $this->domain_management_model->b2c_bus_markup();
		$this->template->view('management/b2c_bus_markup', $data_list['data']);
	}


	/**
	 * Balu A
	 * Manage domain markup for B2B - Domain wise and module wise
	 */

	public function b2b_airline_markup(): void
	{
		$user_oid = 0; // Defining general only as of now
		$this->domain_management_model->markup_level = 'level_3';
		$markup_module_type = 'b2b_flight';

		$form_data = $this->input->post();

		if (is_array($form_data) && !empty($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						(int)$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0,
						$generic_value,
						$value_type,
						get_domain_auth_id(),
						$user_oid
					);
					break;

				case 'specific':
					if (isset($form_data['airline_origin']) && is_array($form_data['airline_origin'])) {
						foreach ($form_data['airline_origin'] as $k => $domain_origin) {
							$specific_value = $form_data['specific_value'][$k] ?? '';
							$value_type_key = 'value_type_' . $domain_origin;
							$specific_value_type = $form_data[$value_type_key] ?? '';

							if ($specific_value != '' && intval($specific_value) > -1 && $specific_value_type != '') {
								$this->domain_management_model->save_markup_data(
									(int)$form_data['markup_origin'][$k],
									$form_values_origin,
									$markup_module_type,
									$domain_origin,
									$specific_value,
									$specific_value_type,
									get_domain_auth_id(),
									$user_oid
								);
							}
						}
					}
					break;

				case 'add_airline':
					if (!empty($form_data['airline_code'])) {
						$airline_code = trim($form_data['airline_code']);
						$markup_details = $this->domain_management_model->individual_airline_markup_details($markup_module_type, $airline_code);

						$airline_list_origin = isset($markup_details['airline_list_origin']) ? intval($markup_details['airline_list_origin']) : 0;
						$markup_list_origin = isset($markup_details['markup_list_origin']) && intval($markup_details['markup_list_origin']) > 0
							? intval($markup_details['markup_list_origin'])
							: 0;

						$specific_value = $form_data['specific_value'] ?? 0;
						$value_type = $form_data['value_type'] ?? '';

						$this->domain_management_model->save_markup_data(
							$markup_list_origin,
							'specific',
							$markup_module_type,
							$airline_list_origin,
							$specific_value,
							$value_type,
							get_domain_auth_id(),
							$user_oid
						);
					}
					break;
			}

			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Airline would have All - general and Agent-wise markup
		$data_list = $this->domain_management_model->b2b_airline_markup();
		$airline_list = $this->db_cache_api->get_airline_list();
		$data_list['airline_list'] = $airline_list;

		$this->template->view('management/b2b_airline_markup', $data_list);
	}
	public function ultra_airline_markup(): void
	{
		ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);
		$user_oid = 0; // Defining general only as of now
		$this->domain_management_model->markup_level = 'level_3';
		$markup_module_type = 'ultra_flight';

		$form_data = $this->input->post();
		//debug($form_data);die;
		if (is_array($form_data) && !empty($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						(int)$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0,
						$generic_value,
						$value_type,
						get_domain_auth_id(),
						$user_oid
					);
					break;

				case 'specific':
					if (isset($form_data['airline_origin']) && is_array($form_data['airline_origin'])) {
						foreach ($form_data['airline_origin'] as $k => $domain_origin) {
							$specific_value = $form_data['specific_value'][$k] ?? '';
							$value_type_key = 'value_type_' . $domain_origin;
							$specific_value_type = $form_data[$value_type_key] ?? '';

							if ($specific_value != '' && intval($specific_value) > -1 && $specific_value_type != '') {
								$this->domain_management_model->save_markup_data(
									(int)$form_data['markup_origin'][$k],
									$form_values_origin,
									$markup_module_type,
									$domain_origin,
									$specific_value,
									$specific_value_type,
									get_domain_auth_id(),
									$user_oid
								);
							}
						}
					}
					break;

				case 'add_airline':
					if (!empty($form_data['airline_code'])) {
						$airline_code = trim($form_data['airline_code']);
						$markup_details = $this->domain_management_model->individual_airline_markup_details($markup_module_type, $airline_code);

						$airline_list_origin = isset($markup_details['airline_list_origin']) ? intval($markup_details['airline_list_origin']) : 0;
						$markup_list_origin = isset($markup_details['markup_list_origin']) && intval($markup_details['markup_list_origin']) > 0
							? intval($markup_details['markup_list_origin'])
							: 0;

						$specific_value = $form_data['specific_value'] ?? 0;
						$value_type = $form_data['value_type'] ?? '';

						$this->domain_management_model->save_markup_data(
							$markup_list_origin,
							'specific',
							$markup_module_type,
							$airline_list_origin,
							$specific_value,
							$value_type,
							get_domain_auth_id(),
							$user_oid
						);
					}
					break;
			}

			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Airline would have All - general and Agent-wise markup
		$data_list = $this->domain_management_model->ultra_airline_markup();
	//	debug($data_list);die;
		$airline_list = $this->db_cache_api->get_airline_list();
		$data_list['airline_list'] = $airline_list;

		$this->template->view('management/ultra_airline_markup', $data_list);
	}


	/**
	 * Balu A
	 * Manage domain markup for B2B - Domain wise and module wise
	 */

	public function b2b_hotel_markup(): void
	{
		$user_oid = 0; // Defining general only as of now
		$this->domain_management_model->markup_level = 'level_3';
		$markup_module_type = 'b2b_hotel';

		$form_data = $this->input->post();

		if (is_array($form_data) && !empty($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						(int)$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0,
						$generic_value,
						$value_type,
						get_domain_auth_id(),
						$user_oid
					);
					break;
			}

			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Hotel would have All - general and domain wise markup
		$data_list = $this->domain_management_model->b2b_hotel_markup();
		$this->template->view('management/b2b_hotel_markup', $data_list);
	}
	public function ultra_hotel_markup(): void
	{
		ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);
		$user_oid = 0; // Defining general only as of now
		$this->domain_management_model->markup_level = 'level_3';
		$markup_module_type = 'ultra_hotel';

		$form_data = $this->input->post();

		if (is_array($form_data) && !empty($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						(int)$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0,
						$generic_value,
						$value_type,
						get_domain_auth_id(),
						$user_oid
					);
					break;
			}

			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Hotel would have All - general and domain wise markup
		$data_list = $this->domain_management_model->ultra_hotel_markup();
		$this->template->view('management/ultra_hotel_markup', $data_list);
	}

	/**
	 * Elavarasi
	 * Manage domain markup for B2B - Domain wise and module wise
	 */

	public function b2b_sightseeing_markup(): void
	{
		$user_oid = 0; // Defining general only as of now
		$this->domain_management_model->markup_level = 'level_3';
		$markup_module_type = 'b2b_sightseeing';

		$form_data = $this->input->post();

		if (is_array($form_data) && !empty($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0,
						$generic_value,
						$value_type,
						get_domain_auth_id(),
						$user_oid
					);
					break;
			}

			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Sightseeing would have All - general and domain wise markup
		$data_list = $this->domain_management_model->b2b_sightseeing_markup();
		$this->template->view('management/b2b_sightseeing_markup', $data_list);
	}

	/**
	 * Elavarasi
	 * Manage domain markup for B2B - Domain wise and module wise
	 */

	public function b2b_transfer_markup(): void
	{
		$user_oid = 0; // Defining general only as of now
		$this->domain_management_model->markup_level = 'level_3';

		// Transfer markup for B2B - general and domain-wise markup
		$markup_module_type = 'b2b_transferv1';
		$form_data = $this->input->post();

		// Check if the form data is valid and not empty
		if (is_array($form_data) && !empty($form_data)) {
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			// Process the form based on the origin type
			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0,
						$generic_value,
						$value_type,
						get_domain_auth_id(),
						$user_oid
					);
					break;
			}

			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Get transfer markup data
		$data_list = $this->domain_management_model->b2b_transferv1_markup();
		$this->template->view('management/b2b_transfers_markup', $data_list);
	}

	/**
	 * Anitha G
	 * Manage domain markup for B2B - Domain wise and module wise
	 */

	public function b2b_car_markup(): void
	{
		// Define general user OID
		$user_oid = 0;

		// Set markup level for domain management model
		$this->domain_management_model->markup_level = 'level_3';

		// Set markup module type for cars
		$markup_module_type = 'b2b_car';

		// Get form data from the POST request
		$form_data = $this->input->post();

		// Check if form data is valid and not empty
		if (is_array($form_data) && !empty($form_data)) {
			// Check form values origin
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			// Process the form based on the origin type
			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0, // Assuming 0 for domain (if this is dynamic, adjust as necessary)
						$generic_value,
						$value_type,
						get_domain_auth_id(),
						$user_oid
					);
					break;
			}

			// Set update message and redirect
			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Fetch car markup data and pass it to the view
		$data_list = $this->domain_management_model->b2b_car_markup();
		$this->template->view('management/b2b_car_markup', $data_list);
	}
	public function ultra_car_markup(): void
	{
		// Define general user OID
		$user_oid = 0;

		// Set markup level for domain management model
		$this->domain_management_model->markup_level = 'level_3';

		// Set markup module type for cars
		$markup_module_type = 'ultra_car';

		// Get form data from the POST request
		$form_data = $this->input->post();

		// Check if form data is valid and not empty
		if (is_array($form_data) && !empty($form_data)) {
			// Check form values origin
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			// Process the form based on the origin type
			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						(int)$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0, // Assuming 0 for domain (if this is dynamic, adjust as necessary)
						$generic_value,
						$value_type,
						get_domain_auth_id(),
						$user_oid
					);
					break;
			}

			// Set update message and redirect
			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Fetch car markup data and pass it to the view
		$data_list = $this->domain_management_model->ultra_car_markup();
		$this->template->view('management/ultra_car_markup', $data_list);
	}


	/**
	 * Balu A
	 * Manage domain markup for B2B - Domain wise and module wise
	 */

	public function b2b_bus_markup(): void
	{
		// Define general user OID
		$user_oid = 0;

		// Set markup level for domain management model
		$this->domain_management_model->markup_level = 'level_3';

		// Set markup module type for buses
		$markup_module_type = 'b2b_bus';

		// Get form data from the POST request
		$form_data = $this->input->post();

		// Check if form data is valid and not empty
		if (is_array($form_data) && !empty($form_data)) {
			// Retrieve necessary values from the form data
			$form_values_origin = $form_data['form_values_origin'] ?? '';
			$markup_origin = $form_data['markup_origin'] ?? 0;
			$generic_value = $form_data['generic_value'] ?? 0;
			$value_type = $form_data['value_type'] ?? '';

			// Process the form based on the origin type
			switch ($form_values_origin) {
				case 'generic':
					$this->domain_management_model->save_markup_data(
						$markup_origin,
						$form_values_origin,
						$markup_module_type,
						0, // Assuming 0 for domain (if this is dynamic, adjust as necessary)
						$generic_value,
						$value_type,
						get_domain_auth_id(),
						$user_oid
					);
					break;
			}

			// Set update message and redirect
			set_update_message();
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Fetch bus markup data and pass it to the view
		$data_list = $this->domain_management_model->b2b_bus_markup();
		$this->template->view('management/b2b_bus_markup', $data_list);
	}


	/**
	 * Balu A
	 * Manage Balance history and other details of domain with provab
	 */

	public function master_balance_manager(string $balance_request_type = "Cash"): void
	{
		// Placeholder for the construction message
		echo 'Under Construction';
		exit;

		// Fetch form data from POST request
		$form_data = $this->input->post();

		// Initialize page_data array
		$page_data = [
			'form_data' => $form_data
		];

		// Switch to handle different balance request types
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
		}

		// Validate form data
		if (is_array($form_data) && !empty($form_data)) {
			// Auto validate the form
			$page_data['balance_page_obj']->set_auto_validator();

			// Run the form validation
			if ($this->form_validation->run()) {
				// Unserialize transaction type
				$form_data['transaction_type'] = unserialized_data($form_data['transaction_type']);

				// If origin is 0, insert new data
				if ($form_data['origin'] == 0) {
					// Insert new transaction
					$status = $this->domain_management_model->save_master_transaction_details($form_data);
				} elseif (intval($form_data['origin']) > 0) {
					// Update not needed as of now, can be expanded later
				}

				// Check status and set appropriate message
				if ($status['status'] == SUCCESS_STATUS) {
					set_update_message();
				} else {
					set_error_message();
				}

				// Redirect after processing
				redirect(base_url() . 'index.php/management/' . __FUNCTION__ . '/' . $balance_request_type);
			}
		}

		// Fetch transaction request list
		$page_data['table_data'] = $this->domain_management_model->master_transaction_request_list();
		$page_data['balance_request_type'] = strtoupper($balance_request_type);

		// Fetch balance requests and set defaults for empty form data
		$page_data['provab_balance_requests'] = get_enum_list('provab_balance_requests');

		if (empty($page_data['form_data']['currency_converter_origin'])) {
			$page_data['form_data']['currency_converter_origin'] = COURSE_LIST_DEFAULT_CURRENCY;
			$page_data['form_data']['conversion_value'] = 1;
		}

		// Serialize transaction type before passing to view
		$page_data['form_data']['transaction_type'] = serialized_data($balance_request_type);

		// Load the template view
		$this->template->view('management/master_balance_manager', $page_data);
	}

	// Managing Balance of B2B users.

	public function b2b_balance_manager(): void
	{
		// Fetch form data from POST request
		$form_data = $this->input->post();
		$page_data = ['form_data' => $form_data];

		if (!empty($page_data['form_data']) && is_array($page_data['form_data'])) {
			$process_details = [];
			if (intval($page_data['form_data']['request_origin']) > 0) {
				// Process balance request if request_origin is valid
				$process_details = $this->domain_management_model->process_balance_request(
					$page_data['form_data']['request_origin'],
					$page_data['form_data']['system_request_id'],
					$page_data['form_data']['status_id'],
					$page_data['form_data']['update_remarks']
				);
			} else {
				// If request_origin is 0, perform validations and potentially insert new data
				$page_data['balance_page_obj']->set_auto_validator();
				if ($this->form_validation->run()) {
					$page_data['form_data']['transaction_type'] = unserialized_data($page_data['form_data']['transaction_type']);
					if ($page_data['form_data']['request_origin'] == 0) {
						// Insert transaction logic (not implemented here)
						// $this->domain_management_model->save_master_transaction_details($page_data['form_data']);
					}
				}
			}

			// If process details are successfully handled, send email notification
			if ($process_details['status'] == SUCCESS_STATUS) {
				$data_list_filt = [
					['MTD.origin', '=', trim($page_data['form_data']['request_origin'])]
				];
				$data = $this->domain_management_model->master_transaction_request_list('b2b', $data_list_filt);

				if (!empty($data) && isset($data[0])) {
					// $master_transaction['master_transaction'] = $data[0];
					//	$email = $page_data['form_data']['request_user_email'];
					//	$mail_template = $this->template->isolated_view('user/deposit_confirmation_template');

					// Load and send email
					$this->load->library('provab_mailer');
					// $status = $this->provab_mailer->send_mail($email, 'Account Deposit', $mail_template);
				}
				set_update_message();
			} else {
				set_error_message();
			}

			// Redirect to the same page with query string
			redirect(base_url() . 'index.php/management/' . __FUNCTION__ . '?' . $_SERVER['QUERY_STRING']);
		}

		// Fetch query parameters for filtering
		$params = $this->input->get();
		$data_list_filt = [];

		if (!empty($params['agency_name'])) {
			$data_list_filt[] = ['U.agency_name', 'like', $this->db->escape('%' . $params['agency_name'] . '%')];
		}
		if (!empty($params['uuid'])) {
			$data_list_filt[] = ['U.uuid', 'like', $this->db->escape('%' . $params['uuid'] . '%')];
		}
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

		// Fetch master transaction request list
		$page_data['table_data'] = $this->domain_management_model->master_transaction_request_list('b2b', $data_list_filt);

		// Get enum lists for balance requests and statuses
		$page_data['provab_balance_requests'] = get_enum_list('provab_balance_requests');
		$page_data['provab_balance_status'] = get_enum_list('provab_balance_status');

		// Set default values for form data if missing
		if (empty($page_data['form_data']['currency_converter_origin'])) {
			$page_data['form_data']['currency_converter_origin'] = COURSE_LIST_DEFAULT_CURRENCY;
			$page_data['form_data']['conversion_value'] = 1;
		}

		$page_data['status_options'] = get_enum_list('provab_balance_status');
		$page_data['heading'] = 'B2B Balance Request';
		$page_data['search_params'] = $params;

		// Render the view with the updated page data
		$this->template->view('management/b2b_balance_manager', $page_data);
	}
	public function ultra_balance_manager(): void
	{
		// Fetch form data from POST request
		$form_data = $this->input->post();
		$page_data = ['form_data' => $form_data];

		if (!empty($page_data['form_data']) && is_array($page_data['form_data'])) {
			$process_details = [];
			if (intval($page_data['form_data']['request_origin']) > 0) {
				// Process balance request if request_origin is valid
				$process_details = $this->domain_management_model->ultra_process_balance_request(
					$page_data['form_data']['request_origin'],
					$page_data['form_data']['system_request_id'],
					$page_data['form_data']['status_id'],
					$page_data['form_data']['update_remarks']
				);
			} else {
				// If request_origin is 0, perform validations and potentially insert new data
				$page_data['balance_page_obj']->set_auto_validator();
				if ($this->form_validation->run()) {
					$page_data['form_data']['transaction_type'] = unserialized_data($page_data['form_data']['transaction_type']);
					if ($page_data['form_data']['request_origin'] == 0) {
						// Insert transaction logic (not implemented here)
						// $this->domain_management_model->save_master_transaction_details($page_data['form_data']);
					}
				}
			}

			// If process details are successfully handled, send email notification
			if ($process_details['status'] == SUCCESS_STATUS) {
				$data_list_filt = [
					['MTD.origin', '=', trim($page_data['form_data']['request_origin'])]
				];
				$data = $this->domain_management_model->master_transaction_request_list('ultralux', $data_list_filt);

				if (!empty($data) && isset($data[0])) {
					// $master_transaction['master_transaction'] = $data[0];
					//	$email = $page_data['form_data']['request_user_email'];
					//	$mail_template = $this->template->isolated_view('user/deposit_confirmation_template');

					// Load and send email
					$this->load->library('provab_mailer');
					// $status = $this->provab_mailer->send_mail($email, 'Account Deposit', $mail_template);
				}
				set_update_message();
			} else {
				set_error_message();
			}

			// Redirect to the same page with query string
			redirect(base_url() . 'index.php/management/' . __FUNCTION__ . '?' . $_SERVER['QUERY_STRING']);
		}

		// Fetch query parameters for filtering
		$params = $this->input->get();
		$data_list_filt = [];

		if (!empty($params['agency_name'])) {
			$data_list_filt[] = ['U.agency_name', 'like', $this->db->escape('%' . $params['agency_name'] . '%')];
		}
		if (!empty($params['uuid'])) {
			$data_list_filt[] = ['U.uuid', 'like', $this->db->escape('%' . $params['uuid'] . '%')];
		}
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

		// Fetch master transaction request list
		$page_data['table_data'] = $this->domain_management_model->master_transaction_request_list('ultralux', $data_list_filt);

		// Get enum lists for balance requests and statuses
		$page_data['provab_balance_requests'] = get_enum_list('provab_balance_requests');
		$page_data['provab_balance_status'] = get_enum_list('provab_balance_status');

		// Set default values for form data if missing
		if (empty($page_data['form_data']['currency_converter_origin'])) {
			$page_data['form_data']['currency_converter_origin'] = COURSE_LIST_DEFAULT_CURRENCY;
			$page_data['form_data']['conversion_value'] = 1;
		}

		$page_data['status_options'] = get_enum_list('provab_balance_status');
		$page_data['heading'] = 'Ultralux Balance Request';
		$page_data['search_params'] = $params;

		// Render the view with the updated page data
		$this->template->view('management/ultra_balance_manager', $page_data);
	}


	public function b2b_credit_request(): void
	{
		// Fetch form data from POST request
		$form_data = $this->input->post();
		$page_data = ['form_data' => $form_data];

		// If form data is valid
		if (!empty($page_data['form_data']) && is_array($page_data['form_data'])) {
			$process_details = [];

			if (intval($page_data['form_data']['request_origin']) > 0) {
				// Process credit limit request if request_origin is valid
				$process_details = $this->domain_management_model->process_credit_limit_request(
					$page_data['form_data']['request_origin'],
					$page_data['form_data']['system_request_id'],
					$page_data['form_data']['status_id'],
					$page_data['form_data']['update_remarks']
				);
			} else {
				// If request_origin is 0, perform validations and potentially insert new data
				$page_data['balance_page_obj']->set_auto_validator();
				if ($this->form_validation->run()) {
					$page_data['form_data']['transaction_type'] = unserialized_data($page_data['form_data']['transaction_type']);
					if ($page_data['form_data']['request_origin'] == 0) {
						// Insert transaction logic (not implemented here)
						// $this->domain_management_model->save_master_transaction_details($page_data['form_data']);
					}
				}
			}

			// If process details are successfully handled, send email notification
			if ($process_details['status'] == SUCCESS_STATUS) {
				$data_list_filt = [
					['MTD.origin', '=', trim($page_data['form_data']['request_origin'])]
				];
				$data = $this->domain_management_model->master_transaction_request_list('b2b', $data_list_filt);

				if (!empty($data) && isset($data[0])) {
					// $master_transaction['master_transaction'] = $data[0];
					//$email = $page_data['form_data']['request_user_email'];
					//$mail_template = $this->template->isolated_view('user/deposit_confirmation_template');

					// Load and send email
					$this->load->library('provab_mailer');
					// $status = $this->provab_mailer->send_mail($email, 'Account Deposit', $mail_template);
				}

				set_update_message();
			} else {
				set_error_message();
			}

			// Redirect to the same page with query string
			redirect(base_url() . 'index.php/management/' . __FUNCTION__ . '?' . $_SERVER['QUERY_STRING']);
		}

		// Fetch query parameters for filtering
		$params = $this->input->get();
		$data_list_filt = [];

		if (!empty($params['agency_name'])) {
			$data_list_filt[] = ['U.agency_name', 'like', $this->db->escape('%' . $params['agency_name'] . '%')];
		}
		if (!empty($params['uuid'])) {
			$data_list_filt[] = ['U.uuid', 'like', $this->db->escape('%' . $params['uuid'] . '%')];
		}
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

		// Fetch master transaction request list
		$page_data['table_data'] = $this->domain_management_model->master_transaction_request_list('b2b', $data_list_filt, 'Credit');

		// Get enum lists for balance requests and statuses
		$page_data['provab_balance_requests'] = get_enum_list('provab_balance_requests');
		$page_data['provab_balance_status'] = get_enum_list('provab_balance_status');

		// Set default values for form data if missing
		if (empty($page_data['form_data']['currency_converter_origin'])) {
			$page_data['form_data']['currency_converter_origin'] = COURSE_LIST_DEFAULT_CURRENCY;
			$page_data['form_data']['conversion_value'] = 1;
		}

		$page_data['status_options'] = get_enum_list('provab_balance_status');
		$page_data['heading'] = 'B2B Credit Limit Request';
		$page_data['search_params'] = $params;

		// Render the view with the updated page data
		$this->template->view('management/b2b_balance_manager', $page_data);
	}

	public function ultra_credit_request(): void
	{
		ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
		// Fetch form data from POST request
		$form_data = $this->input->post();
		$page_data = ['form_data' => $form_data];

		// If form data is valid
		if (!empty($page_data['form_data']) && is_array($page_data['form_data'])) {
			$process_details = [];

			if (intval($page_data['form_data']['request_origin']) > 0) {
				// Process credit limit request if request_origin is valid
				$process_details = $this->domain_management_model->process_credit_limit_request1(
					$page_data['form_data']['request_origin'],
					$page_data['form_data']['system_request_id'],
					$page_data['form_data']['status_id'],
					$page_data['form_data']['update_remarks']
				);
				//debug($process_details);die;
			} else {
				// If request_origin is 0, perform validations and potentially insert new data
				$page_data['balance_page_obj']->set_auto_validator();
				if ($this->form_validation->run()) {
					$page_data['form_data']['transaction_type'] = unserialized_data($page_data['form_data']['transaction_type']);
					if ($page_data['form_data']['request_origin'] == 0) {
						// Insert transaction logic (not implemented here)
						// $this->domain_management_model->save_master_transaction_details($page_data['form_data']);
					}
				}
			}

			// If process details are successfully handled, send email notification
			if ($process_details['status'] == SUCCESS_STATUS) {
				$data_list_filt = [
					['MTD.origin', '=', trim($page_data['form_data']['request_origin'])]
				];
				$data = $this->domain_management_model->master_transaction_request_list('ultralux', $data_list_filt);

				if (!empty($data) && isset($data[0])) {
					// $master_transaction['master_transaction'] = $data[0];
					//$email = $page_data['form_data']['request_user_email'];
					//$mail_template = $this->template->isolated_view('user/deposit_confirmation_template');

					// Load and send email
					$this->load->library('provab_mailer');
					// $status = $this->provab_mailer->send_mail($email, 'Account Deposit', $mail_template);
				}

				set_update_message();
			} else {
				set_error_message();
			}

			// Redirect to the same page with query string
			redirect(base_url() . 'index.php/management/' . __FUNCTION__ . '?' . $_SERVER['QUERY_STRING']);
		}

		// Fetch query parameters for filtering
		$params = $this->input->get();
		$data_list_filt = [];

		if (!empty($params['agency_name'])) {
			$data_list_filt[] = ['U.agency_name', 'like', $this->db->escape('%' . $params['agency_name'] . '%')];
		}
		if (!empty($params['uuid'])) {
			$data_list_filt[] = ['U.uuid', 'like', $this->db->escape('%' . $params['uuid'] . '%')];
		}
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

		// Fetch master transaction request list
		$page_data['table_data'] = $this->domain_management_model->master_transaction_request_list('ultralux', $data_list_filt, 'Credit');

		// Get enum lists for balance requests and statuses
		$page_data['provab_balance_requests'] = get_enum_list('provab_balance_requests');
		$page_data['provab_balance_status'] = get_enum_list('provab_balance_status');

		// Set default values for form data if missing
		if (empty($page_data['form_data']['currency_converter_origin'])) {
			$page_data['form_data']['currency_converter_origin'] = COURSE_LIST_DEFAULT_CURRENCY;
			$page_data['form_data']['conversion_value'] = 1;
		}

		$page_data['status_options'] = get_enum_list('provab_balance_status');
		$page_data['heading'] = 'Ultralux Credit Limit Request';
		$page_data['search_params'] = $params;

		// Render the view with the updated page data
		$this->template->view('management/ultra_balance_manager', $page_data);
	}


	/**
	 * Event logging
	 * @param number $offset
	 */
	public function event_logs(int $offset = 0): void
	{
		$page_data = [];
		$config = [];
		// Define condition array
		//$condition = [];

		// Fetch event logs with pagination
		$page_data['table_data'] = $this->domain_management_model->event_logs(false, $offset, RECORDS_RANGE_3);

		// Get total number of records
		$total_records = $this->domain_management_model->event_logs(true);

		// Initialize pagination
		$this->load->library('pagination');
		$config['base_url'] = base_url() . 'index.php/management/event_logs/';
		$config['total_rows'] = $total_records;
		$config['per_page'] = RECORDS_RANGE_3;

		// Initialize pagination library
		$this->pagination->initialize($config);

		// Set the total number of records for the view
		$page_data['total_rows'] = $config['total_rows'];
		$page_data['total_records'] = $config['total_rows'];

		// Render the view with the pagination data
		$this->template->view('management/event_logs', $page_data);
	}

	/**
	 * Balu A
	 * Update B2B Agent Commission
	 */
	public function agent_commission(int $offset = 0): void
	{
		ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

		// Get input data (GET and POST)
		$get_data = $this->input->get();
		$post_data = $this->input->post();
		$page_data = [];
		$config = [];
		$condition = [];
		// Load external library
		$this->load->library('Api_Interface');

		// Handling commission details based on agent_ref_id
		if (isset($get_data['agent_ref_id']) && !empty($get_data['agent_ref_id']) && empty($post_data)) {
			$agent_ref_id = base64_decode(trim($get_data['agent_ref_id']));
			$page_data['agent_ref_id'] = $agent_ref_id;

			$agent_commission_details = $this->domain_management_model->get_commission_details($agent_ref_id);
			if ($agent_commission_details['status']) {
				$page_data['commission_details'] = $agent_commission_details['data'];
			} else {
				// Invalid CRUD - redirect if the agent commission details are not valid
				redirect('security/log_event?event=InvalidAgent');
			}
		} elseif (!empty($post_data) && isset($post_data['module']) && !empty($post_data['module'])) {
			// Handling commission updates for different modules
			foreach ($post_data['module'] as $module_k => $module_v) {
				$module = trim($module_v);
				$update_data = [
					'module' => $module,
					'agent_ref_id' => $post_data['agent_ref_id'][$module_k],
					'commission_origin' => $post_data['commission_origin'][$module_k],
					'commission' => $post_data['commission'][$module_k],
					'api_value' => $post_data['api_value'][$module_k]
				];

				switch ($module) {
					case META_AIRLINE_COURSE:  // Airline Commission
						$this->update_b2b_flight_commission($update_data);
						break;
					case META_BUS_COURSE:  // Bus Commission
					//	$this->update_b2b_bus_commission($update_data);
						break;
					case META_SIGHTSEEING_COURSE:  // Sightseeing Commission
						//$this->update_b2b_sightseeing_commission($update_data);
						break;
					case META_TRANSFERV1_COURSE:  // Transfer Commission
						//$this->update_b2b_transfer_commission($update_data);
						break;
				}
			}

			set_update_message();
			$query_string = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
			redirect('management/agent_commission' . $query_string);
		}

		// Handling default commission
		if (isset($get_data['default_commission']) && $get_data['default_commission'] == ACTIVE) {
			$page_data['default_commission'] = ACTIVE;
			$commission_details = $this->domain_management_model->default_commission_details();
			$page_data['commission_details'] = $commission_details['data'];
		} else {
			// Agent's List
			if (isset($get_data['filter']) && $get_data['filter'] == 'search_agent' && isset($get_data['filter_agency']) && !empty($get_data['filter_agency'])) {
				$filter_agency = trim($get_data['filter_agency']);
				$search_filter_condition = '(U.uuid LIKE "%' . $filter_agency . '%" OR U.agency_name LIKE "%' . $filter_agency . '%" OR U.first_name LIKE "%' . $filter_agency . '%" OR U.last_name LIKE "%' . $filter_agency . '%" OR U.email LIKE "%' . $filter_agency . '%" OR U.phone LIKE "%' . $filter_agency . '%")';
				$total_records = $this->domain_management_model->filter_agent_commission_details($search_filter_condition, true);
				$agent_list = $this->domain_management_model->filter_agent_commission_details($search_filter_condition, false, $offset, RECORDS_RANGE_1);
			} else {
				$condition[] = ['U.user_type', 'IN', '(' . B2B_USER . ')'];
				$condition[] = ['U.status', 'IN', '(1)']; // Active agents
				$total_records = $this->domain_management_model->agent_commission_details($condition, true);
				$agent_list = $this->domain_management_model->agent_commission_details($condition, false, $offset, RECORDS_RANGE_1);
			}

			$page_data['agent_list'] = $agent_list['data']['agent_commission_details'];
			$this->load->library('pagination');

			// Configure pagination
			$config['base_url'] = base_url() . 'index.php/management/agent_commission/';
			$config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
			$config['total_rows'] = $total_records->total;
			$config['per_page'] = RECORDS_RANGE_1;
			$this->pagination->initialize($config);
		}

		// Super Admin Commission Details
		//$super_admin_bus_commission = json_decode($this->api_interface->rest_service('bus_commission_details'), true);
		$super_admin_flight_commission = json_decode($this->api_interface->rest_service('airline_commission_details'), true);
		//$super_admin_sightseeing_commission = json_decode($this->api_interface->rest_service('sightseeing_commission_details'), true);
		//$super_admin_transfer_commission = json_decode($this->api_interface->rest_service('transfer_commission_details'), true);

		/*$page_data['super_admin_bus_commission'] = $super_admin_bus_commission['status']
			? ['value' => $super_admin_bus_commission['data'][0]['value'], 'api_value' => $super_admin_bus_commission['data'][0]['api_value']]
			: ['value' => 0, 'api_value' => 0];
*/
		$page_data['super_admin_flight_commission'] = $super_admin_flight_commission['status']
			? ['value' => $super_admin_flight_commission['data'][0]['value'], 'api_value' => $super_admin_flight_commission['data'][0]['api_value']]
			: ['value' => 0, 'api_value' => 0];

		/*$page_data['super_admin_sightseeing_commission'] = $super_admin_sightseeing_commission['status']
			? ['value' => $super_admin_sightseeing_commission['data'][0]['value'], 'api_value' => $super_admin_sightseeing_commission['data'][0]['api_value']]
			: ['value' => 0, 'api_value' => 0];*/

		/*$page_data['super_admin_transfer_commission'] = $super_admin_transfer_commission['status']
			? ['value' => $super_admin_transfer_commission['data'][0]['value'], 'api_value' => $super_admin_transfer_commission['data'][0]['api_value']]
			: ['value' => 0, 'api_value' => 0];
*/
		// Render the view with the commission data
		$this->template->view('management/agent_commission', $page_data);
	}


	public function ultra_agent_commission(int $offset = 0): void
	{
		ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

		// Get input data (GET and POST)
		$get_data = $this->input->get();
		$post_data = $this->input->post();
		$page_data = [];
		$config = [];
		$condition = [];
		// Load external library
		$this->load->library('Api_Interface');

		// Handling commission details based on agent_ref_id
		if (isset($get_data['agent_ref_id']) && !empty($get_data['agent_ref_id']) && empty($post_data)) {
			$agent_ref_id = base64_decode(trim($get_data['agent_ref_id']));
			$page_data['agent_ref_id'] = $agent_ref_id;

			$agent_commission_details = $this->domain_management_model->ultra_get_commission_details($agent_ref_id);
			if ($agent_commission_details['status']) {
				$page_data['commission_details'] = $agent_commission_details['data'];
			} else {
				// Invalid CRUD - redirect if the agent commission details are not valid
				redirect('security/log_event?event=InvalidAgent');
			}
		} elseif (!empty($post_data) && isset($post_data['module']) && !empty($post_data['module'])) {
			// Handling commission updates for different modules
			foreach ($post_data['module'] as $module_k => $module_v) {
				$module = trim($module_v);
				$update_data = [
					'module' => $module,
					'agent_ref_id' => $post_data['agent_ref_id'][$module_k],
					'commission_origin' => $post_data['commission_origin'][$module_k],
					'commission' => $post_data['commission'][$module_k],
					'api_value' => $post_data['api_value'][$module_k]
				];

				switch ($module) {
					case META_AIRLINE_COURSE:  // Airline Commission
						$this->update_b2b_flight_commission($update_data);
						break;
					case META_BUS_COURSE:  // Bus Commission
					//	$this->update_b2b_bus_commission($update_data);
						break;
					case META_SIGHTSEEING_COURSE:  // Sightseeing Commission
						//$this->update_b2b_sightseeing_commission($update_data);
						break;
					case META_TRANSFERV1_COURSE:  // Transfer Commission
						//$this->update_b2b_transfer_commission($update_data);
						break;
				}
			}

			set_update_message();
			$query_string = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
			redirect('management/agent_commission' . $query_string);
		}

		// Handling default commission
		if (isset($get_data['default_commission']) && $get_data['default_commission'] == ACTIVE) {
			$page_data['default_commission'] = ACTIVE;
			$commission_details = $this->domain_management_model->default_commission_details();
			$page_data['commission_details'] = $commission_details['data'];
		} else {
			// Agent's List
			if (isset($get_data['filter']) && $get_data['filter'] == 'search_agent' && isset($get_data['filter_agency']) && !empty($get_data['filter_agency'])) {
				$filter_agency = trim($get_data['filter_agency']);
				$search_filter_condition = '(U.uuid LIKE "%' . $filter_agency . '%" OR U.agency_name LIKE "%' . $filter_agency . '%" OR U.first_name LIKE "%' . $filter_agency . '%" OR U.last_name LIKE "%' . $filter_agency . '%" OR U.email LIKE "%' . $filter_agency . '%" OR U.phone LIKE "%' . $filter_agency . '%")';
				$total_records = $this->domain_management_model->ultra_filter_agent_commission_details($search_filter_condition, true);
				$agent_list = $this->domain_management_model->ultra_filter_agent_commission_details($search_filter_condition, false, $offset, RECORDS_RANGE_1);
			} else {
				$condition[] = ['U.user_type', 'IN', '(' . ULTRALUX_USER . ')'];
				$condition[] = ['U.status', 'IN', '(1)']; // Active agents
				$total_records = $this->domain_management_model->agent_commission_details($condition, true);
				$agent_list = $this->domain_management_model->agent_commission_details($condition, false, $offset, RECORDS_RANGE_1);
			}

			$page_data['agent_list'] = $agent_list['data']['agent_commission_details'];
			$this->load->library('pagination');

			// Configure pagination
			$config['base_url'] = base_url() . 'index.php/management/agent_commission/';
			$config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
			$config['total_rows'] = $total_records->total;
			$config['per_page'] = RECORDS_RANGE_1;
			$this->pagination->initialize($config);
		}

		// Super Admin Commission Details
		$super_admin_bus_commission = json_decode($this->api_interface->rest_service('bus_commission_details'), true);
		$super_admin_flight_commission = json_decode($this->api_interface->rest_service('airline_commission_details'), true);
		$super_admin_sightseeing_commission = json_decode($this->api_interface->rest_service('sightseeing_commission_details'), true);
		$super_admin_transfer_commission = json_decode($this->api_interface->rest_service('transfer_commission_details'), true);

		$page_data['super_admin_bus_commission'] = $super_admin_bus_commission['status']
			? ['value' => $super_admin_bus_commission['data'][0]['value'], 'api_value' => $super_admin_bus_commission['data'][0]['api_value']]
			: ['value' => 0, 'api_value' => 0];

		$page_data['super_admin_flight_commission'] = $super_admin_flight_commission['status']
			? ['value' => $super_admin_flight_commission['data'][0]['value'], 'api_value' => $super_admin_flight_commission['data'][0]['api_value']]
			: ['value' => 0, 'api_value' => 0];

		$page_data['super_admin_sightseeing_commission'] = $super_admin_sightseeing_commission['status']
			? ['value' => $super_admin_sightseeing_commission['data'][0]['value'], 'api_value' => $super_admin_sightseeing_commission['data'][0]['api_value']]
			: ['value' => 0, 'api_value' => 0];

		$page_data['super_admin_transfer_commission'] = $super_admin_transfer_commission['status']
			? ['value' => $super_admin_transfer_commission['data'][0]['value'], 'api_value' => $super_admin_transfer_commission['data'][0]['api_value']]
			: ['value' => 0, 'api_value' => 0];

		// Render the view with the commission data
		$this->template->view('management/agent_commission', $page_data);
	}

	/**
	 * Balu A
	 * Update Flight Commission Details
	 * @param $commission_details
	 */

	public function update_b2b_flight_commission(array $commission_details): void
	{
		// Check if the required keys exist and have valid values
		if (
			isset($commission_details['module']) && !empty($commission_details['module']) &&
			isset($commission_details['agent_ref_id']) && !empty($commission_details['agent_ref_id']) &&
			isset($commission_details['flight_commission_origin']) && isset($commission_details['flight_commission'])
		) {

			// Sanitize and prepare the commission details
			$origin = trim($commission_details['flight_commission_origin']);
			$agent_ref_id = base64_decode(trim($commission_details['agent_ref_id']));
			$commission_value = floatval(trim($commission_details['flight_commission']));
			$api_value = floatval(trim($commission_details['api_value']));

			// Initialize the commission details array
			$b2b_flight_commission_details = [
				'value' => $commission_value,
				'api_value' => $api_value,
				'value_type' => MARKUP_VALUE_PERCENTAGE,
				'commission_currency' => MARKUP_CURRENCY,
				'created_by_id' => $this->entity_user_id,
				'created_datetime' => date('Y-m-d H:i:s')
			];

			// Determine if it's a specific agent commission or a generic one
			$b2b_flight_commission_details['type'] = (intval($agent_ref_id) > 0) ? SPECIFIC : GENERIC;

			// If the origin exists (non-zero), update the commission details
			if ($origin > 0) {
				$update_condition = (intval($agent_ref_id) > 0)
					? ['agent_fk' => $agent_ref_id]
					: ['type' => GENERIC];

				// Perform update
				$this->custom_db->update_record('b2b_flight_commission_details', $b2b_flight_commission_details, $update_condition);
			} else {
				// If no origin, add new commission details
				$b2b_flight_commission_details['agent_fk'] = $agent_ref_id;
				$b2b_flight_commission_details['domain_list_fk'] = get_domain_auth_id();

				// Prepare delete condition
				$delete_condition = (intval($agent_ref_id) > 0)
					? ['agent_fk' => $agent_ref_id]
					: ['type' => GENERIC];

				// Delete existing commission if applicable
				$this->custom_db->delete_record('b2b_flight_commission_details', $delete_condition);

				// Insert new commission details
				$this->custom_db->insert_record('b2b_flight_commission_details', $b2b_flight_commission_details);
			}
		} else {
			// Invalid commission details, redirect to log event
			redirect('security/log_event?event=InvalidFlightCommissionDetails');
		}
	}

	public function update_b2b_bus_commission(array $commission_details): void
	{
		// Check if required keys exist and are valid
		if (
			isset($commission_details['module']) && !empty($commission_details['module']) &&
			isset($commission_details['agent_ref_id']) && !empty($commission_details['agent_ref_id']) &&
			isset($commission_details['bus_commission_origin']) && isset($commission_details['bus_commission'])
		) {

			// Sanitize and prepare the commission details
			$origin = trim($commission_details['bus_commission_origin']);
			$agent_ref_id = base64_decode(trim($commission_details['agent_ref_id']));
			$commission_value = floatval(trim($commission_details['bus_commission']));
			$api_value = floatval(trim($commission_details['api_value']));

			// Initialize the commission details array
			$b2b_bus_commission_details = [
				'value' => $commission_value,
				'api_value' => $api_value,
				'value_type' => MARKUP_VALUE_PERCENTAGE,
				'commission_currency' => MARKUP_CURRENCY,
				'created_by_id' => $this->entity_user_id,
				'created_datetime' => date('Y-m-d H:i:s')
			];

			// Determine if it's a specific agent commission or a generic one
			$b2b_bus_commission_details['type'] = (intval($agent_ref_id) > 0) ? SPECIFIC : GENERIC;

			// If the origin exists (non-zero), update the commission details
			if ($origin > 0) {
				$update_condition = (intval($agent_ref_id) > 0)
					? ['agent_fk' => $agent_ref_id]
					: ['type' => GENERIC];

				// Perform the update
				$this->custom_db->update_record('b2b_bus_commission_details', $b2b_bus_commission_details, $update_condition);
			} else {
				// If no origin, add new commission details
				$b2b_bus_commission_details['agent_fk'] = $agent_ref_id;
				$b2b_bus_commission_details['domain_list_fk'] = get_domain_auth_id();

				// Prepare the delete condition
				$delete_condition = (intval($agent_ref_id) > 0)
					? ['agent_fk' => $agent_ref_id]
					: ['type' => GENERIC];

				// Delete existing commission if applicable
				$this->custom_db->delete_record('b2b_bus_commission_details', $delete_condition);

				// Insert the new commission details
				$this->custom_db->insert_record('b2b_bus_commission_details', $b2b_bus_commission_details);
			}
		} else {
			// Invalid commission details, redirect to log event
			redirect('security/log_event?event=InvalidBusCommissionDetails');
		}
	}

	/**
	 * Elavarasi
	 * Update Sightseeing Commission Details
	 * @param $commission_details
	 */

	public function update_b2b_sightseeing_commission(array $commission_details): void
	{
		// Check if required keys exist and are valid
		if (
			isset($commission_details['module']) && !empty($commission_details['module']) &&
			isset($commission_details['agent_ref_id']) && !empty($commission_details['agent_ref_id']) &&
			isset($commission_details['sightseeing_commission_origin']) && isset($commission_details['sightseeing_commission'])
		) {

			// Sanitize and prepare the commission details
			$origin = trim($commission_details['sightseeing_commission_origin']);
			$agent_ref_id = base64_decode(trim($commission_details['agent_ref_id']));
			$commission_value = floatval(trim($commission_details['sightseeing_commission']));
			$api_value = floatval(trim($commission_details['api_value']));

			// Initialize the commission details array
			$b2b_sightseeing_commission_details = [
				'value' => $commission_value,
				'api_value' => $api_value,
				'value_type' => MARKUP_VALUE_PERCENTAGE,
				'commission_currency' => MARKUP_CURRENCY,
				'created_by_id' => $this->entity_user_id,
				'created_datetime' => date('Y-m-d H:i:s')
			];

			// Determine if it's a specific agent commission or a generic one
			$b2b_sightseeing_commission_details['type'] = (intval($agent_ref_id) > 0) ? SPECIFIC : GENERIC;

			// If the origin exists (non-zero), update the commission details
			if ($origin > 0) {
				$update_condition = (intval($agent_ref_id) > 0)
					? ['agent_fk' => $agent_ref_id]
					: ['type' => GENERIC];

				// Perform the update
				$this->custom_db->update_record('b2b_sightseeing_commission_details', $b2b_sightseeing_commission_details, $update_condition);
			} else {
				// If no origin, add new commission details
				$b2b_sightseeing_commission_details['agent_fk'] = $agent_ref_id;
				$b2b_sightseeing_commission_details['domain_list_fk'] = get_domain_auth_id();

				// Prepare the delete condition
				$delete_condition = (intval($agent_ref_id) > 0)
					? ['agent_fk' => $agent_ref_id]
					: ['type' => GENERIC];

				// Delete existing commission if applicable
				$this->custom_db->delete_record('b2b_sightseeing_commission_details', $delete_condition);

				// Insert the new commission details
				$this->custom_db->insert_record('b2b_sightseeing_commission_details', $b2b_sightseeing_commission_details);
			}
		} else {
			// Invalid commission details, redirect to log event
			redirect('security/log_event?event=InvalidSightseeingCommissionDetails');
		}
	}

	/**
	 * Elavarasi
	 * Update Transfer Commission Details
	 * @param $commission_details
	 */
	public function update_b2b_transfer_commission(array $commission_details): void
	{
		// Check if required keys exist and are valid
		if (
			isset($commission_details['module']) && !empty($commission_details['module']) &&
			isset($commission_details['agent_ref_id']) && !empty($commission_details['agent_ref_id']) &&
			isset($commission_details['transfer_commission_origin']) && isset($commission_details['transfer_commission'])
		) {

			// Sanitize and prepare the commission details
			$origin = trim($commission_details['transfer_commission_origin']);
			$agent_ref_id = base64_decode(trim($commission_details['agent_ref_id']));
			$commission_value = floatval(trim($commission_details['transfer_commission']));
			$api_value = floatval(trim($commission_details['api_value']));

			// Initialize the commission details array
			$b2b_transfer_commission_details = [
				'value' => $commission_value,
				'api_value' => $api_value,
				'value_type' => MARKUP_VALUE_PERCENTAGE,
				'commission_currency' => MARKUP_CURRENCY,
				'created_by_id' => $this->entity_user_id,
				'created_datetime' => date('Y-m-d H:i:s')
			];

			// Determine if it's a specific agent commission or a generic one
			$b2b_transfer_commission_details['type'] = (intval($agent_ref_id) > 0) ? SPECIFIC : GENERIC;

			// If the origin exists (non-zero), update the commission details
			if ($origin > 0) {
				$update_condition = (intval($agent_ref_id) > 0)
					? ['agent_fk' => $agent_ref_id]
					: ['type' => GENERIC];

				// Perform the update
				$this->custom_db->update_record('b2b_transfer_commission_details', $b2b_transfer_commission_details, $update_condition);
			} else {
				// If no origin, add new commission details
				$b2b_transfer_commission_details['agent_fk'] = $agent_ref_id;
				$b2b_transfer_commission_details['domain_list_fk'] = get_domain_auth_id();

				// Prepare the delete condition
				$delete_condition = (intval($agent_ref_id) > 0)
					? ['agent_fk' => $agent_ref_id]
					: ['type' => GENERIC];

				// Delete existing commission if applicable
				$this->custom_db->delete_record('b2b_transfer_commission_details', $delete_condition);

				// Insert the new commission details
				$this->custom_db->insert_record('b2b_transfer_commission_details', $b2b_transfer_commission_details);
			}
		} else {
			// Invalid commission details, redirect to log event
			redirect('security/log_event?event=InvalidTransferCommissionDetails');
		}
	}

	/**
	 * Balu A
	 * Manages Bank Account Details
	 */

	public function bank_account_details(): void
	{
		$page_data = [];
		$config = [];
		$post_data = [];
		$post_data['form_data'] = $this->input->post();
		$get_data = $this->input->get();
		$page_data['form_data'] = [];

		// Check if POST data is empty and GET data contains 'eid'
		if (valid_array($post_data['form_data']) == false && isset($get_data['eid']) && intval($get_data['eid']) > 0) {
			$temp_data = $this->custom_db->single_table_records('bank_account_details', '*', ['origin' => $get_data['eid']]);
			if (!empty($temp_data['data'])) {
				$page_data['form_data'] = $temp_data['data'][0];
			}
		} elseif (valid_array($post_data['form_data'])) {
			// Set up auto validation
			$this->current_page->set_auto_validator();

			if ($this->form_validation->run()) {
				$origin = intval($post_data['form_data']['origin']);
				unset($post_data['form_data']['FID']);
				unset($post_data['form_data']['origin']);

				if ($origin > 0) {
					// UPDATE operation
					$post_data['form_data']['updated_by_id'] = $this->entity_user_id;
					$post_data['form_data']['updated_datetime'] = date('Y-m-d H:i:s');
					$this->custom_db->update_record('bank_account_details', $post_data['form_data'], ['origin' => $origin]);
					set_update_message();
				} elseif ($origin == 0) {
					// INSERT operation
					$post_data['form_data']['domain_list_fk'] = get_domain_auth_id();
					$post_data['form_data']['created_by_id'] = $this->entity_user_id;
					$post_data['form_data']['created_datetime'] = date('Y-m-d H:i:s');
					$insert_id = $this->custom_db->insert_record('bank_account_details', $post_data['form_data']);
					set_insert_message();
				}

				// FILE UPLOAD
				if (!empty($_FILES) && $_FILES['bank_icon']['error'] == 0 && $_FILES['bank_icon']['size'] > 0) {
					if (function_exists('check_mime_image_type') && !check_mime_image_type($_FILES['bank_icon']['tmp_name'])) {
						echo "Please select the image files only (gif|jpg|png|jpeg)";
						exit;
					}

					$config['upload_path'] = $this->template->domain_image_full_path() . 'bank_logo/';
					$config['allowed_types'] = 'gif|jpg|png|jpeg';
					$config['file_name'] = time();
					$config['max_size'] = MAX_DOMAIN_LOGO_SIZE;
					$config['max_width'] = MAX_DOMAIN_LOGO_WIDTH;
					$config['max_height'] = MAX_DOMAIN_LOGO_HEIGHT;
					$config['remove_spaces'] = false;

					if (empty($insert_id) == true) {
						// UPDATE existing icon
						$temp_record = $this->custom_db->single_table_records('bank_account_details', 'bank_icon', ['origin' => $origin]);
						$icon = $temp_record['data'][0]['bank_icon'];
						// DELETE old file if exists
						if (!empty($icon) && file_exists($config['upload_path'] . $icon)) {
							unlink($config['upload_path'] . $icon);
						}
					} else {
						$origin = $insert_id['insert_id'];
					}

					// UPLOAD new image
					$this->load->library('upload', $config);
					if (!$this->upload->do_upload('bank_icon')) {
						// Handle upload errors if any
						echo $this->upload->display_errors();
					} else {
						$image_data = $this->upload->data();
						$this->custom_db->update_record('bank_account_details', ['bank_icon' => $image_data['file_name']], ['origin' => $origin]);
					}
				}

				redirect('management/bank_account_details');
			}
		} else {
			$page_data['form_data']['origin'] = 0;
		}

		// Fetch table data
		$temp_data = $this->domain_management_model->bank_account_details();
		if ($temp_data['status']) {
			$page_data['table_data'] = $temp_data['data'];
		} else {
			$page_data['table_data'] = [];
		}

		$this->template->view('management/bank_account_details', $page_data);
	}

	/*
	 *Admin Account Ledger
	 *
	*/

	public function account_ledger(int $offset = 0): void
	{
		ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
		$config = [];
		$get_data = $this->input->get();
		$condition = [];
		$page_data = [];

		$agent_details = [];

		// Check if agent_id is present in the GET data
		$agent_id = isset($get_data['agent_id']) && intval($get_data['agent_id']) > 0 ? intval($get_data['agent_id']) : 0;

		// Add condition for agent_id
		$condition[] = ['U.user_id', '=', $agent_id];

		// Fetch complete agent details if agent_id is valid
		$complete_agent_details = $this->domain_management_model->get_agent_details($agent_id);
		if (valid_array($complete_agent_details)) {
			$agent_details['agency_name'] = $complete_agent_details['agency_name'];
			$agent_details['agent_balance'] = $complete_agent_details['balance'];
			$agent_details['agent_currency'] = $complete_agent_details['agent_base_currency'];
		}
		$page_data['agent_details'] = $agent_details;

		// Get 'from_date' and 'to_date' from GET data
		$from_date = trim($get_data['created_datetime_from'] ?? '');
		$to_date = trim($get_data['created_datetime_to'] ?? '');

		// Auto swipe date if both dates are provided
		if (!empty($from_date) && !empty($to_date)) {
			$valid_dates = auto_swipe_dates($from_date, $to_date);
			$from_date = $valid_dates['from_date'];
			$to_date = $valid_dates['to_date'];
		}

		// Add conditions for 'from_date' and 'to_date' if provided
		if (!empty($from_date)) {
			$ymd_from_date = date('Y-m-d', strtotime($from_date));
			$condition[] = ['date(TL.created_datetime)', '>=', $this->db->escape($ymd_from_date)];
		}
		if (!empty($to_date)) {
			$ymd_to_date = date('Y-m-d', strtotime($to_date));
			$condition[] = ['date(TL.created_datetime)', '<=', $this->db->escape($ymd_to_date)];
		}

		// Add condition for app_reference if provided
		if (!empty($get_data['app_reference'])) {
			$condition[] = ['TL.app_reference', 'LIKE', $this->db->escape('%' . $get_data['app_reference'] . '%')];
		}

		// Add condition for transaction_type if provided
		if (!empty($get_data['transaction_type'])) {
			$condition[] = ['TL.transaction_type', 'LIKE', $this->db->escape('%' . $get_data['transaction_type'] . '%')];
		}

		// Get total records and transaction logs
		$total_records_data = $this->domain_management_model->agent_account_ledger($condition, true);
		$total_records = $total_records_data['total_records'] ?? 0;
		$transaction_logs = $this->domain_management_model->agent_account_ledger($condition, false, $offset, RECORDS_RANGE_3);

		// Format the transaction logs
		$transaction_logs = format_account_ledger($transaction_logs['data']);
		$page_data['table_data'] = $transaction_logs['data'];

		// Pagination
		$this->load->library('pagination');
		if (!empty($get_data)) {
			if (count($get_data) > 0) {
				$config['suffix'] = '?' . http_build_query($get_data, '', "&");
			}
		}

		$config['base_url'] = base_url() . 'index.php/management/account_ledger/';
		if (!empty($get_data)) {
			$config['first_url'] = $config['base_url'] . '?' . http_build_query($get_data);
		}

		$page_data['total_records'] = $config['total_rows'] = $total_records;
		$config['per_page'] = RECORDS_RANGE_2;
		$this->pagination->initialize($config);

		// Set search params in the page data
		$page_data['search_params'] = $get_data;

		// Get active agent list
		$agent_list = $this->domain_management_model->agent_list();
		$page_data['agent_list'] = magical_converter(['k' => 'user_id', 'v' => 'agency_name'], $agent_list);

		// Load the view
		$this->template->view('management/account_ledger', $page_data);
	}
	public function ultra_account_ledger(int $offset = 0): void
	{
		$config = [];
		$get_data = $this->input->get();
		$condition = [];
		$page_data = [];

		$agent_details = [];

		// Check if agent_id is present in the GET data
		$agent_id = isset($get_data['agent_id']) && intval($get_data['agent_id']) > 0 ? intval($get_data['agent_id']) : 0;

		// Add condition for agent_id
		$condition[] = ['U.user_id', '=', $agent_id];

		// Fetch complete agent details if agent_id is valid
		$complete_agent_details = $this->domain_management_model->get_agent_details($agent_id);
		if (valid_array($complete_agent_details)) {
			$agent_details['agency_name'] = $complete_agent_details['agency_name'];
			$agent_details['agent_balance'] = $complete_agent_details['balance'];
			$agent_details['agent_currency'] = $complete_agent_details['agent_base_currency'];
		}
		$page_data['agent_details'] = $agent_details;

		// Get 'from_date' and 'to_date' from GET data
		$from_date = trim($get_data['created_datetime_from'] ?? '');
		$to_date = trim($get_data['created_datetime_to'] ?? '');

		// Auto swipe date if both dates are provided
		if (!empty($from_date) && !empty($to_date)) {
			$valid_dates = auto_swipe_dates($from_date, $to_date);
			$from_date = $valid_dates['from_date'];
			$to_date = $valid_dates['to_date'];
		}

		// Add conditions for 'from_date' and 'to_date' if provided
		if (!empty($from_date)) {
			$ymd_from_date = date('Y-m-d', strtotime($from_date));
			$condition[] = ['date(TL.created_datetime)', '>=', $this->db->escape($ymd_from_date)];
		}
		if (!empty($to_date)) {
			$ymd_to_date = date('Y-m-d', strtotime($to_date));
			$condition[] = ['date(TL.created_datetime)', '<=', $this->db->escape($ymd_to_date)];
		}

		// Add condition for app_reference if provided
		if (!empty($get_data['app_reference'])) {
			$condition[] = ['TL.app_reference', 'LIKE', $this->db->escape('%' . $get_data['app_reference'] . '%')];
		}

		// Add condition for transaction_type if provided
		if (!empty($get_data['transaction_type'])) {
			$condition[] = ['TL.transaction_type', 'LIKE', $this->db->escape('%' . $get_data['transaction_type'] . '%')];
		}

		// Get total records and transaction logs
		$total_records_data = $this->domain_management_model->ultra_agent_account_ledger($condition, true);
		//debug(total_records_data);die;
		$total_records = $total_records_data['total_records'] ?? 0;
		$transaction_logs = $this->domain_management_model->ultra_agent_account_ledger($condition, false, $offset, RECORDS_RANGE_3);
// debug($transaction_logs);die;
		// Format the transaction logs
		$transaction_logs = format_account_ledger($transaction_logs['data']);
		$page_data['table_data'] = $transaction_logs['data'];

		// Pagination
		$this->load->library('pagination');
		if (!empty($get_data)) {
			if (count($get_data) > 0) {
				$config['suffix'] = '?' . http_build_query($get_data, '', "&");
			}
		}

		$config['base_url'] = base_url() . 'management/account_ledger/';
		if (!empty($get_data)) {
			$config['first_url'] = $config['base_url'] . '?' . http_build_query($get_data);
		}

		$page_data['total_records'] = $config['total_rows'] = $total_records;
		$config['per_page'] = RECORDS_RANGE_2;
		$this->pagination->initialize($config);

		// Set search params in the page data
		$page_data['search_params'] = $get_data;

		// Get active agent list
		$agent_list = $this->domain_management_model->agent_list();
		$page_data['agent_list'] = magical_converter(['k' => 'user_id', 'v' => 'agency_name'], $agent_list);

		// Load the view
		$this->template->view('management/account_ledger', $page_data);
	}

	/*
	*Export Account Ledger details to Excel Format
	*/

	public function export_account_ledger(string $op = ''): void
	{
		$get_data = $this->input->get();
		$condition = [];

		// Check if agent_id is present and valid
		$agent_id = isset($get_data['agent_id']) && intval($get_data['agent_id']) > 0 ? intval($get_data['agent_id']) : 0;
		$condition[] = ['U.user_id', '=', $agent_id];

		// Get 'from_date' and 'to_date' from GET data
		$from_date = trim($get_data['created_datetime_from'] ?? '');
		$to_date = trim($get_data['created_datetime_to'] ?? '');

		// Auto swipe dates if both are provided
		if (!empty($from_date) && !empty($to_date)) {
			$valid_dates = auto_swipe_dates($from_date, $to_date);
			$from_date = $valid_dates['from_date'];
			$to_date = $valid_dates['to_date'];
		}

		// Add conditions for from_date and to_date
		if (!empty($from_date)) {
			$ymd_from_date = date('Y-m-d', strtotime($from_date));
			$condition[] = ['date(TL.created_datetime)', '>=', $this->db->escape($ymd_from_date)];
		}
		if (!empty($to_date)) {
			$ymd_to_date = date('Y-m-d', strtotime($to_date));
			$condition[] = ['date(TL.created_datetime)', '<=', $this->db->escape($ymd_to_date)];
		}

		// Add condition for app_reference if present
		if (!empty($get_data['app_reference'])) {
			$condition[] = ['TL.app_reference', 'LIKE', $this->db->escape('%' . $get_data['app_reference'] . '%')];
		}

		// Add condition for transaction_type if present
		if (!empty($get_data['transaction_type'])) {
			$condition[] = ['TL.transaction_type', 'LIKE', $this->db->escape('%' . $get_data['transaction_type'] . '%')];
		}

		// Fetch transaction logs based on conditions
		$transaction_logs = $this->domain_management_model->agent_account_ledger($condition, false);
		$transaction_logs = format_account_ledger($transaction_logs['data']);
		$export_data = $transaction_logs['data'];

		// Excel export logic
		if ($op == 'excel') {

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

			// Field names in data set
			$fields = [
				'a' => '', // empty for sl. no.
				'b' => 'transaction_date',
				'c' => 'reference_number',
				'd' => 'full_description',
				'e' => 'debit_amount',
				'f' => 'credit_amount',
				'g' => 'opening_balance',
				'h' => 'closing_balance'
			];

			$excel_sheet_properties = [
				'title' => 'Account_Ledger_' . date('d-M-Y'),
				'creator' => 'Provab',
				'description' => 'Account Ledger of All Clients',
				'sheet_title' => 'Account Ledger'
			];

			$this->load->library('provab_excel'); // Provab Excel library required for export
			$this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
		} else { // PDF export logic

			// Column mapping for PDF export
			$col = [
				'transaction_date' => 'Date',
				'reference_number' => 'Reference Number',
				'full_description' => 'Description',
				'debit_amount' => 'Debit',
				'credit_amount' => 'Credit',
				'opening_balance' => 'Opening Balance',
				'closing_balance' => 'Closing Balance'
			];

			// Format the export data for PDF
			$pdf_data = format_pdf_data($export_data, $col);
			$this->load->library('provab_pdf');
			$get_view = $this->template->isolated_view('report/table', $pdf_data);
			$this->provab_pdf->create_pdf($get_view, 'D', 'Account_Ledger');
			exit();
		}
	}

	/*
	*Getting Admin Balance
	*/
	public function get_travelomatix_balance(): void
	{
		$balance = current_application_balance();

		$json_arr = array(
			'face_value' => $balance['face_value'],
			'credit_limit' => $balance['credit_limit'],
			'due_amount' => $balance['due_amount']
		);

		echo json_encode($json_arr);
		exit;
	}

	// GST Details
	public function gst_master(): void
	{
		$page_data = [];
		$post_data = $this->input->post();
		$condition = array();

		if (!empty($post_data) && valid_array($post_data)) {
			// Iterate over gst_origin
			foreach ($post_data['gst_origin'] as $i => $gst_origin) {
				// Set default value for 'tds' if it's null
				if ($post_data['tds'][$i] == null) {
					$post_data['tds'][$i] = 0;
				}

				$data = array(
					'tds' => $post_data['tds'][$i],
					'gst' => $post_data['gst'][$i],
					'modified_date' => date('Y-m-d H:i:s')
				);

				$update_origin = $gst_origin;
				$condition['origin'] = $update_origin;

				// Update gst_master table with the new data
				$this->db->update('gst_master', $data, $condition);
			}

			// Redirect to the same page after update
			redirect(base_url() . 'index.php/management/' . __FUNCTION__);
		}

		// Fetch GST details
		$details = $this->module_model->get_gst_details();

		// Check if status is SUCCESS_STATUS
		if ($details['status'] == SUCCESS_STATUS) {
			$page_data['details'] = $details['data'];
		} else {
			$page_data['details'] = array();
		}

		// Render the template with GST details
		$this->template->view('management/gst_master', $page_data);
	}

	/** Anitha G Update Credit Limit ** /
	 * 
	 */

	public function credit_balance_show(): void
	{
		//echo "dfdf";die;
		$page_data = [];
		$get_data = $this->input->get();

		// Check if 'agent_id' is set and valid
		if (!empty($get_data) && !empty($get_data['agent_id'])) {
			$user_details = $this->user_model->get_agent_info($get_data['agent_id']);

			// Check if user details are valid
			if (empty($user_details)) {
				redirect(base_url()); // Redirect to the home page if invalid
			}

			$user_details = $user_details[0];
			$page_data['user_details'] = $user_details;
		}

		// Render the credit limit view
		$this->template->view('management/credit_limit', $page_data);
	}
	public function ultra_credit_balance_show(): void
	{
		//echo "dfdf";die;
		$page_data = [];
		$get_data = $this->input->get();

		// Check if 'agent_id' is set and valid
		if (!empty($get_data) && !empty($get_data['agent_id'])) {
			$user_details = $this->user_model->get_agent_info_ultra($get_data['agent_id']);

			// Check if user details are valid
			if (empty($user_details)) {
				redirect(base_url()); // Redirect to the home page if invalid
			}

			$user_details = $user_details[0];
			$page_data['user_details'] = $user_details;
		}

		// Render the credit limit view
		$this->template->view('management/ultra_credit_limit', $page_data);
	}

	public function credit_balance_update(): void
	{
		$get_data = $this->input->post();
		$page_data = [];

		// Check if valid data is received and 'origin' is not empty
		if (!empty($get_data) && !empty($get_data['origin'])) {
			// Prepare data for updating the credit limit
			$page_data['credit_limit'] = $get_data['amount'];
			$page_data['user_id'] = $get_data['user_id'];
			$page_data['origin'] = $get_data['origin'];

			// Update the credit limit
			$this->user_model->update_credit_limit($page_data);

			// Retrieve updated user details
			$user_details = $this->user_model->get_agent_info($get_data['user_id']);
			$user_details = $user_details[0];
			$page_data['user_details'] = $user_details;
		}

		// Render the credit limit view
		$this->template->view('management/credit_limit', $page_data);
	}
	public function credit_balance_update_ultra(): void
	{
		$get_data = $this->input->post();
		$page_data = [];

		// Check if valid data is received and 'origin' is not empty
		if (!empty($get_data) && !empty($get_data['origin'])) {
			// Prepare data for updating the credit limit
			$page_data['credit_limit'] = $get_data['amount'];
			$page_data['user_id'] = $get_data['user_id'];
			$page_data['origin'] = $get_data['origin'];

			// Update the credit limit
			$this->user_model->update_credit_limit($page_data);

			// Retrieve updated user details
			$user_details = $this->user_model->get_agent_info_ultra($get_data['user_id']);
			$user_details = $user_details[0];
			$page_data['user_details'] = $user_details;
		}

		// Render the credit limit view
		$this->template->view('management/credit_limit', $page_data);
	}
}
