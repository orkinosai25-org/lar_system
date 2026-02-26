<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab - Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com> on 01-06-2015
 * @version    V2
 */

class Private_Management extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('private_management_model');
		$this->load->model('domain_management_model');
	}

	/**
	 * Manage domain markup for provab - Domain wise and module wise
	 */
	function airline_domain_markup(): void
	{
		$page_data = [];
		$page_data['form_data'] = $this->input->post();
		if (valid_array($page_data['form_data']) == true) {
			switch ($page_data['form_data']['form_values_origin']) {
				case 'generic':
					$this->private_management_model->save_markup_data(
						$page_data['form_data']['markup_origin'],
						$page_data['form_data']['form_values_origin'],
						'b2c_flight',
						0,
						$page_data['form_data']['generic_value'],
						$page_data['form_data']['value_type'],
						$page_data['form_data']['domain_origin']
					);
					break;
				case 'specific':
					if (valid_array($page_data['form_data']['domain_origin'])) {
						foreach ($page_data['form_data']['domain_origin'] as $__k => $__domain_origin) {
							if (
								$page_data['form_data']['specific_value'][$__k] != '' && intval($page_data['form_data']['specific_value'][$__k]) > -1
								&& empty($page_data['form_data']['value_type_' . $__domain_origin]) == false
							) {
								$this->private_management_model->save_markup_data(
									$page_data['form_data']['markup_origin'][$__k],
									$page_data['form_data']['form_values_origin'],
									'b2c_flight',
									$page_data['form_data']['domain_origin'][$__k],
									$page_data['form_data']['specific_value'][$__k],
									$page_data['form_data']['value_type_' . $__domain_origin],
									$page_data['form_data']['domain_origin'][$__k]
								);
							}
						}
					}
					break;
			}
			redirect(base_url() . 'index.php/private_management/' . __FUNCTION__);
		}
		//Airline would have All - general and domain wise markup
		$data_list = $this->private_management_model->airline_domain_markup();
		$this->template->view('private_management/airline_domain_markup', $data_list['data']);
	}

	/**
	 * Manage domain markup for provab - Domain wise and module wise
	 */
	function hotel_domain_markup(): void
	{
		$page_data = [];
		// $map_data=[];
		// $insert_map_data=[];
		// $insert_data=[];
		//Hotel would have All - general and domain wise markup
		$page_data['form_data'] = $this->input->post();
		if (valid_array($page_data['form_data']) == true) {
			switch ($page_data['form_data']['form_values_origin']) {
				case 'generic':
					$this->private_management_model->save_markup_data(
						$page_data['form_data']['markup_origin'],
						$page_data['form_data']['form_values_origin'],
						'b2c_hotel',
						0,
						$page_data['form_data']['generic_value'],
						$page_data['form_data']['value_type'],
						$page_data['form_data']['domain_origin']
					);
					break;
				case 'specific':
					if (valid_array($page_data['form_data']['domain_origin'])) {
						foreach ($page_data['form_data']['domain_origin'] as $__k => $__domain_origin) {
							if (
								$page_data['form_data']['specific_value'][$__k] != '' && intval($page_data['form_data']['specific_value'][$__k]) > -1
								&& empty($page_data['form_data']['value_type_' . $__domain_origin]) == false
							) {
								$this->private_management_model->save_markup_data(
									$page_data['form_data']['markup_origin'][$__k],
									$page_data['form_data']['form_values_origin'],
									'b2c_hotel',
									$page_data['form_data']['domain_origin'][$__k],
									$page_data['form_data']['specific_value'][$__k],
									$page_data['form_data']['value_type_' . $__domain_origin],
									$page_data['form_data']['domain_origin'][$__k]
								);
							}
						}
					}
					break;
			}
			redirect(base_url() . 'index.php/private_management/' . __FUNCTION__);
		}
		//Airline would have All - general and domain wise markup
		$data_list = $this->private_management_model->hotel_domain_markup();
		$this->template->view('private_management/hotel_domain_markup', $data_list['data']);
	}
	/**
	 * Manage domain markup for provab - Domain wise and module wise
	 */
	/**
	 * Manage domain markup for provab - Domain wise and module wise
	 */

	/**
	 * Manage domain markup for provab - Domain wise and module wise
	 */

	/**
	 * Balu A
	 * Process Balance Request with provab
	 */
	function process_balance_manager(): void
	{
		$page_data = [];
		if (!is_domain_user()) {
			$page_data['form_data'] = $this->input->post();

			if (valid_array($page_data['form_data']) == true) {

				 $this->private_management_model->process_balance_request($page_data['form_data']['request_origin'], $page_data['form_data']['system_request_id'], $page_data['form_data']['status_id'], $page_data['form_data']['update_remarks']);
				redirect(base_url() . 'index.php/private_management/' . __FUNCTION__);
			}
			$page_data['provab_balance_requests'] = get_enum_list('provab_balance_status');
			$page_data['table_data'] = $this->private_management_model->master_transaction_request_list();

			$this->template->view('private_management/process_balance_manager', $page_data);
		}
	}

	/**
	 * Event logging
	 * @param number $offset
	 */
	function event_logs(int $offset = 0): void
	{
		$page_data = [];
		$config = [];
		//$insert_map_data = [];
		$condition = array();
		$page_data['table_data'] = $this->private_management_model->event_logs($condition, false, $offset, RECORDS_RANGE_2);
		$total_records = $this->private_management_model->event_logs($condition, true);
		$this->load->library('pagination');
		$config['base_url'] = base_url() . 'index.php/private_management/event_logs/';
		$page_data['total_rows'] = $config['total_rows'] = $total_records;
		$config['per_page'] = RECORDS_RANGE_2;
		$this->pagination->initialize($config);
		/** TABLE PAGINATION */
		$page_data['total_records'] = $config['total_rows'];
		$this->template->view('private_management/event_logs', $page_data);
	}

	/**
	 * Admin Credits Balance to Agent Directly
	 * 
	 */
	public function credit_balance(): void
	{
		//$insert_map_data = [];
		$page_data = [];
		$get_data = $this->input->get();
		$post_data = $this->input->post();
		$page_data = array();
		$form_data = array();
		$page_data['form_data'] = $form_data;
		if (valid_array($get_data) == true && isset($get_data['agent_id']) == true && empty($get_data['agent_id']) == false) {

			$agent_id = $get_data['agent_id'];
			$agent_details = $this->domain_management_model->get_agent_details($agent_id);
			if (valid_array($agent_details) == false) { //Invalid Agent ID
				redirect(base_url());
			}
			$page_data['agent_details'] = $agent_details;
			$page_data['agent_id'] = $agent_id;
		}
		//echo 'inprocess'; die();
		if (valid_array($post_data) == true) {

			$this->current_page->set_auto_validator();

			if ($this->form_validation->run()) {
				unset($post_data['FID']);
				$post_data['amount'] = abs($post_data['amount']); //CREDTING AMOUNT
				$agent_base_currency = $agent_details['agent_base_currency'];
				$agent_currency_convsesrion_rate = $this->domain_management_model->get_currency_conversion_rate($agent_base_currency); //TODO: find better solution, place it in currency library
				$deposit_amount = ($post_data['amount'] * $agent_currency_convsesrion_rate['conversion_rate']); //Converting to INR
				$currency_obj = new Currency();
				$currency_conversion_rate = $currency_obj->getConversionRate(false, get_application_default_currency(), $agent_base_currency); //Currency conversion rate of the domain currency
				$post_data['agent_list_fk'] = $agent_id;
				$post_data['remarks'] = $post_data['remarks'];
				$post_data['amount'] = $deposit_amount;
				$post_data['currency'] = $agent_details['agent_base_currency'];
				$post_data['currency_conversion_rate'] = $currency_conversion_rate;
				$post_data['issued_for'] = 'Credited Towards: ' . $post_data['issued_for'];
				$this->domain_management_model->process_direct_credit_debit_transaction($post_data);
				set_update_message('Amount Credited Successfully', SUCCESS_MESSAGE, array('override_app_msg' => true));
				refresh();
			}
		}
		$this->template->view('private_management/credit_balance', $page_data);
	}
	public function ultra_credit_balance(): void
	{
		//$insert_map_data = [];
		$page_data = [];
		$get_data = $this->input->get();
		$post_data = $this->input->post();
		$page_data = array();
		$form_data = array();
		$page_data['form_data'] = $form_data;
		if (valid_array($get_data) == true && isset($get_data['agent_id']) == true && empty($get_data['agent_id']) == false) {

			$agent_id = $get_data['agent_id'];
			$agent_details = $this->domain_management_model->get_agent_details($agent_id);
			//debug($agent_details);die;
			if (valid_array($agent_details) == false) { //Invalid Agent ID
				redirect(base_url());
			}
			$page_data['agent_details'] = $agent_details;
			$page_data['agent_id'] = $agent_id;
		}
		//echo 'inprocess'; die();
		if (valid_array($post_data) == true) {

			$this->current_page->set_auto_validator();

			if ($this->form_validation->run()) {
				unset($post_data['FID']);
				$post_data['amount'] = abs($post_data['amount']); //CREDTING AMOUNT
				$agent_base_currency = $agent_details['agent_base_currency'];
				$agent_currency_convsesrion_rate = $this->domain_management_model->get_currency_conversion_rate($agent_base_currency); //TODO: find better solution, place it in currency library
				$deposit_amount = ($post_data['amount'] * $agent_currency_convsesrion_rate['conversion_rate']); //Converting to INR
				$currency_obj = new Currency();
				$currency_conversion_rate = $currency_obj->getConversionRate(false, get_application_default_currency(), $agent_base_currency); //Currency conversion rate of the domain currency
				$post_data['agent_list_fk'] = $agent_id;
				$post_data['remarks'] = $post_data['remarks'];
				$post_data['amount'] = $deposit_amount;
				$post_data['currency'] = $agent_details['agent_base_currency'];
				$post_data['currency_conversion_rate'] = $currency_conversion_rate;
				$post_data['issued_for'] = 'Credited Towards: ' . $post_data['issued_for'];
				$this->domain_management_model->process_direct_credit_debit_transaction($post_data);
				set_update_message('Amount Credited Successfully', SUCCESS_MESSAGE, array('override_app_msg' => true));
				refresh();
			}
		}
		$this->template->view('private_management/ultra_credit_balance', $page_data);
	}

	/**
	 * Admin Debits Balance from Agent Directly
	 * 
	 */
	public function debit_balance(): void
	{
		error_reporting(E_ALL);
		$get_data = $this->input->get();
		$post_data = $this->input->post();
		$page_data = array();
		$form_data = array();
		$page_data['form_data'] = $form_data;
		if (valid_array($get_data) == true && isset($get_data['agent_id']) == true && empty($get_data['agent_id']) == false) {
			$agent_id = $get_data['agent_id'];
			$agent_details = $this->domain_management_model->get_agent_details($agent_id);
			if (valid_array($agent_details) == false) { //Invalid Agent ID
				redirect(base_url());
			}
			$page_data['agent_details'] = $agent_details;
			$page_data['agent_id'] = $agent_id;
		}
		if (valid_array($post_data) == true) {
			$this->current_page->set_auto_validator();
			if ($this->form_validation->run()) {
				unset($post_data['FID']);
				$post_data['amount'] = -abs($post_data['amount']); //DEBITING AMOUNT
				$agent_base_currency = $agent_details['agent_base_currency'];
				$agent_currency_convsesrion_rate = $this->domain_management_model->get_currency_conversion_rate($agent_base_currency); //TODO: find better solution, place it in currency library
				$debit_amount = ($post_data['amount'] * $agent_currency_convsesrion_rate['conversion_rate']); //Converting to INR
				$currency_obj = new Currency();
				$currency_conversion_rate = $currency_obj->getConversionRate(false, get_application_default_currency(), $agent_base_currency); //Currency conversion rate of the domain currency
				$post_data['agent_list_fk'] = $agent_id;
				$post_data['remarks'] = $post_data['remarks'];
				$post_data['amount'] = $debit_amount;
				$post_data['currency'] = $agent_details['agent_base_currency'];
				$post_data['currency_conversion_rate'] = $currency_conversion_rate;
				$post_data['issued_for'] = 'Debited Towards: ' . $post_data['issued_for'];
				$this->domain_management_model->process_direct_credit_debit_transaction($post_data, 'Debit');
				set_update_message('Amount Debited Successfully', SUCCESS_MESSAGE, array('override_app_msg' => true));
				refresh();
			}
		}
		$this->template->view('private_management/debit_balance', $page_data);
	}
	public function ultra_debit_balance(): void
	{
		error_reporting(E_ALL);
		$get_data = $this->input->get();
		$post_data = $this->input->post();
		$page_data = array();
		$form_data = array();
		$page_data['form_data'] = $form_data;
		if (valid_array($get_data) == true && isset($get_data['agent_id']) == true && empty($get_data['agent_id']) == false) {
			$agent_id = $get_data['agent_id'];
			$agent_details = $this->domain_management_model->get_agent_details($agent_id);
			if (valid_array($agent_details) == false) { //Invalid Agent ID
				redirect(base_url());
			}
			$page_data['agent_details'] = $agent_details;
			$page_data['agent_id'] = $agent_id;
		}
		if (valid_array($post_data) == true) {
			$this->current_page->set_auto_validator();
			if ($this->form_validation->run()) {
				unset($post_data['FID']);
				$post_data['amount'] = -abs($post_data['amount']); //DEBITING AMOUNT
				$agent_base_currency = $agent_details['agent_base_currency'];
				$agent_currency_convsesrion_rate = $this->domain_management_model->get_currency_conversion_rate($agent_base_currency); //TODO: find better solution, place it in currency library
				$debit_amount = ($post_data['amount'] * $agent_currency_convsesrion_rate['conversion_rate']); //Converting to INR
				$currency_obj = new Currency();
				$currency_conversion_rate = $currency_obj->getConversionRate(false, get_application_default_currency(), $agent_base_currency); //Currency conversion rate of the domain currency
				$post_data['agent_list_fk'] = $agent_id;
				$post_data['remarks'] = $post_data['remarks'];
				$post_data['amount'] = $debit_amount;
				$post_data['currency'] = $agent_details['agent_base_currency'];
				$post_data['currency_conversion_rate'] = $currency_conversion_rate;
				$post_data['issued_for'] = 'Debited Towards: ' . $post_data['issued_for'];
				$this->domain_management_model->process_direct_credit_debit_transaction($post_data, 'Debit');
				set_update_message('Amount Debited Successfully', SUCCESS_MESSAGE, array('override_app_msg' => true));
				refresh();
			}
		}
		$this->template->view('private_management/ultra_debit_balance', $page_data);
	}
}
