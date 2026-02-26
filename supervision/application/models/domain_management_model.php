<?php
require_once 'abstract_management_model.php';
/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */
class Domain_Management_Model extends Abstract_Management_Model
{
	function __construct()
	{
		parent::__construct('level_2');
	}

	public function get_markup(string $module_name): array
	{
		$markup_data = '';
		switch ($module_name) {
			case 'flight':
				$markup_data = $this->b2b_airline_markup();
				break;
			case 'hotel':
				$markup_data = $this->b2b_hotel_markup();
				break;
			case 'bus':
				$markup_data = $this->b2b_bus_markup();
				break;
			case 'sightseeing':
				$markup_data = $this->b2b_sightseeing_markup();
				break;
			case 'transferv1':
				$markup_data = $this->b2b_transferv1_markup();
				break;
		}
		return $markup_data;
	}

	/**
	 * Balu A
	 * Manage domain markup for b2b domain
	 */
	public function b2b_airline_markup(): array
	{
		if (empty($this->airline_markup) == true) {
			$response['specific_markup_list'] = $this->specific_airline_markup('b2b_flight');
			$response['generic_markup_list'] = $this->generic_domain_markup('b2b_flight');
			$this->airline_markup = $response;
		} else {
			$response = $this->airline_markup;
		}
		return $response;
	}
	public function ultra_airline_markup(): array
	{
		if (empty($this->airline_markup) == true) {
			$response['specific_markup_list'] = $this->specific_airline_markup('ultra_flight');
			$response['generic_markup_list'] = $this->generic_domain_markup('ultra_flight');
			$this->airline_markup = $response;
		} else {
			$response = $this->airline_markup;
		}
		return $response;
	}

	/**
	 * Balu A
	 * Manage domain markup for b2b domain
	 */
	public function b2b_hotel_markup(): array
	{
		if (empty($this->hotel_markup) == true) {
			$response['specific_markup_list'] = '';
			$response['generic_markup_list'] = $this->generic_domain_markup('b2b_hotel');
			$this->hotel_markup = $response;
		} else {
			$response = $this->hotel_markup;
		}
		return $response;
	}
	public function ultra_hotel_markup(): array
	{
		if (empty($this->hotel_markup) == true) {
			$response['specific_markup_list'] = '';
			$response['generic_markup_list'] = $this->generic_domain_markup('ultra_hotel');
			$this->hotel_markup = $response;
		} else {
			$response = $this->hotel_markup;
		}
		return $response;
	}

	/**
	 * Elavarasi
	 * Manage domain markup for b2b domain
	 */

	/**
	 * Anitha G
	 * Manage domain markup for b2b domain
	 */
	public function b2b_car_markup(): array
	{
		if (empty($this->hotel_markup) == true) {
			$response['specific_markup_list'] = '';
			$response['generic_markup_list'] = $this->generic_domain_markup('b2b_car');
			$this->hotel_markup = $response;
		} else {
			$response = $this->hotel_markup;
		}
		return $response;
	}
	public function ultra_car_markup(): array
	{
		if (empty($this->hotel_markup) == true) {
			$response['specific_markup_list'] = '';
			$response['generic_markup_list'] = $this->generic_domain_markup('ultra_car');
			$this->hotel_markup = $response;
		} else {
			$response = $this->hotel_markup;
		}
		return $response;
	}
	/**
	 * Balu A
	 * Manage domain markup for b2b domain
	 */


	/**
	 * Balu A
	 * Manage domain markup for provab - Domain wise and module wise
	 */
	public function b2c_airline_markup(): array
	{
		$response=[];
		$response['data'] = array();
		$response['data']['specific_markup_list'] = $this->specific_airline_markup('b2c_flight');
		$response['data']['generic_markup_list'] = $this->generic_domain_markup('b2c_flight');
		return $response;
	}

	/**
	 * Balu A
	 * Manage domain markup for provab - Domain wise and module wise
	 */
	public function b2c_hotel_markup(): array
	{
		$response=[];
		$response['data'] = array();
		$response['data']['specific_markup_list'] = array();
		$response['data']['generic_markup_list'] = $this->generic_domain_markup('b2c_hotel');
		return $response;
	}

	/**
	 * Elavarasi
	 * Manage domain markup for provab - Domain wise and module wise
	 */
	public function b2c_sightseeing_markup(): array
	{
		$response=[];
		$response['data'] = '';
		$response['data']['specific_markup_list'] = '';
		$response['data']['generic_markup_list'] = $this->generic_domain_markup('b2c_sightseeing');
		return $response;
	}
	/**
	 * Elavarasi
	 * Manage domain markup for provab - Domain wise and module wise
	 */
	public function b2c_transferv1_markup(): array
	{
		$response=[];
		$response['data'] = '';
		$response['data']['specific_markup_list'] = '';
		$response['data']['generic_markup_list'] = $this->generic_domain_markup('b2c_transferv1');
		return $response;
	}
	/**
	 * Balu A
	 * Manage domain markup for provab - Domain wise and module wise
	 */

	/**
	 * Anitha G
	 * Manage domain markup for provab - Domain wise and module wise
	 */
	public function b2c_car_markup(): array
	{
		$response=[];
		$response['data'] = array();;
		$response['data']['specific_markup_list'] = array();;
		$response['data']['generic_markup_list'] = $this->generic_domain_markup('b2c_car');
		return $response;
	}
	/**
	 * Balu A
	 * Get generic markup based on the module type
	 * @param $module_type
	 * @param $markup_level
	 */
	public function generic_domain_markup(string $module_type): array
	{
		$query = 'SELECT ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type
		FROM markup_list AS ML where ML.module_type = "' . $module_type . '" and
		ML.markup_level = "' . $this->markup_level . '" and ML.type="generic" and ML.domain_list_fk=' . get_domain_auth_id();
		$generic_data_list = $this->db->query($query)->result_array();
		return $generic_data_list;
	}

	/**
	 * Get specific markup based on module type
	 * @param string $module_type	Name of the module for which the markup has to be returned
	 * @param string $markup_level	Level of markup
	 */
	public function specific_airline_markup(string $module_type): array
	{
		$sub_query = 'SELECT AL.origin 
		FROM airline_list AS AL 
		JOIN markup_list AS ML ON
		ML.module_type = "' . $module_type . '" and ML.markup_level = "' . $this->markup_level . '" and AL.origin=ML.reference_id and ML.type="specific"
		and ML.domain_list_fk != 0  and ML.domain_list_fk=' . get_domain_auth_id();

		$query = 'SELECT AL.origin AS airline_origin, AL.name AS airline_name, AL.code AS airline_code,
		ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type
		FROM airline_list AS AL LEFT JOIN markup_list AS ML ON
		ML.module_type = "' . $module_type . '" and ML.markup_level = "' . $this->markup_level . '" and AL.origin=ML.reference_id and ML.type="specific"
		and ML.domain_list_fk != 0  and ML.domain_list_fk=' . get_domain_auth_id() . ' 
		where (AL.has_specific_markup=' . ACTIVE . ' OR AL.origin in (' . $sub_query . ')) order by AL.name ASC';
		$specific_data_list = $this->db->query($query)->result_array();
		return $specific_data_list;
	}
	/**
	 * Get Details based on Airline Code
	 * @param string $module_type	Name of the module for which the markup has to be returned
	 * @param string $markup_level	Level of markup
	 */
	public function individual_airline_markup_details(string $module_type, string $airline_code): array
	{
		$query = 'SELECT ML.origin as markup_list_origin,AL.origin as airline_list_origin 
		FROM airline_list AS AL 
		left JOIN markup_list AS ML ON
		ML.module_type = "' . $module_type . '" and ML.markup_level = "' . $this->markup_level . '" and AL.origin=ML.reference_id and ML.type="specific"
		and ML.domain_list_fk != 0  and ML.domain_list_fk=' . get_domain_auth_id() . ' where AL.code="' . $airline_code . '"';
		$specific_data_list = $this->db->query($query)->row_array();
		return $specific_data_list;
	}

	/**
	 * Get specific markup based on module type
	 * @param string $module_type	Name of the module for which the markup has to be returned
	 * @param string $markup_level	Level of markup
	 */
	public function specific_agent_markup(string $module_type): array
	{
		//FIXME
		$query = 'SELECT AL.origin AS airline_origin, AL.name AS airline_name, AL.code AS airline_code,
		ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type
		FROM airline_list AS AL LEFT JOIN markup_list AS ML ON
		ML.module_type = "' . $module_type . '" and ML.markup_level = "' . $this->markup_level . '" and AL.origin=ML.reference_id and ML.type="specific"
		and ML.domain_list_fk != 0  and ML.domain_list_fk=' . get_domain_auth_id() . ' order by AL.name ASC';
		$specific_data_list = $this->db->query($query)->result_array();
		return $specific_data_list;
	}
	/**
	 * save master transaction details request
	 * @param array $details
	 */
	public function save_master_transaction_details(array $details): int
	{
		$master_transaction_details=[];
		$master_transaction_details['system_transaction_id'] = 'DEP-' . $this->entity_user_id . time();
		$master_transaction_details['domain_list_fk'] = get_domain_auth_id();
		$master_transaction_details['transaction_type'] = $details['transaction_type'];
		$master_transaction_details['amount'] = $details['amount'];
		$master_transaction_details['currency_converter_origin'] = $details['currency_converter_origin'];
		$master_transaction_details['conversion_value'] = $details['conversion_value'];
		$master_transaction_details['date_of_transaction'] = valid_date_value($details['date_of_transaction']);
		$master_transaction_details['bank'] = $details['bank'];
		$master_transaction_details['branch'] = $details['branch'];
		$master_transaction_details['transaction_number'] = isset($details['transaction_number']) ? $details['transaction_number'] : 'N/A';
		$master_transaction_details['status'] = 'pending';
		$master_transaction_details['remarks'] = $details['remarks'];
		$master_transaction_details['created_datetime'] = db_current_datetime();
		$master_transaction_details['created_by_id'] = $this->entity_user_id;
		$master_transaction_details['user_oid'] = $this->entity_user_id;
		$insert_id = $this->custom_db->insert_record('master_transaction_details', $master_transaction_details);
		return $insert_id['insert_id'];
	}

	/**
	 * Master Transaction Request List
	 */
	public function master_transaction_request_list(string $type = 'provab', array $data_list_filt = [], string $credit = ''): array
	{
		$data_list_cond = '';
		if (valid_array($data_list_filt) == true) {
			$data_list_cond = $this->custom_db->get_custom_condition($data_list_filt);
		}
		if (!empty($credit)) {
			$query = 'select MTD.*, CONCAT(U.first_name, " ", U.last_name) request_user, U.email, U.agency_name AS requested_from from master_transaction_details MTD, user U
			where MTD.created_by_id=U.user_id AND MTD.type="' . $type . '" and MTD.domain_list_fk = ' . get_domain_auth_id() . ' ' . $data_list_cond . '
			and MTD.transaction_type ="Credit" order by MTD.updated_datetime DESC, MTD.created_datetime DESC';
		} else {
			$query = 'select MTD.*, CONCAT(U.first_name, " ", U.last_name) request_user, U.email, U.agency_name AS requested_from from master_transaction_details MTD, user U
			where MTD.created_by_id=U.user_id AND MTD.type="' . $type . '" and MTD.domain_list_fk = ' . get_domain_auth_id() . ' ' . $data_list_cond . '
			and MTD.transaction_type !="Credit" order by MTD.updated_datetime DESC, MTD.created_datetime DESC';
		}

		return $this->db->query($query)->result_array();
	}

	/**
	 *
	 */
	public function event_logs(bool $count = false, int $offset = 0, int $limit = 100000000000): int
	{
		//BT, CD, ID
		if ($count) {
			$query = 'select count(*) as total_records from exception_logger where domain_origin=' . get_domain_auth_id();
			$data = $this->db->query($query)->row_array();
			return $data['total_records'];
		} else {
			$query = 'select * from exception_logger where domain_origin=' . get_domain_auth_id() . ' order by origin desc limit ' . $offset . ', ' . $limit;
			return $this->db->query($query)->result_array();
		}
	}

	/**
	 * Process Update Request
	 * @param number $origin
	 * @param string $system_request_id
	 * @param string $status_id
	 * @param string $update_remarks
	 *
	 * @return $response status of the update operation
	 */
	public function process_balance_request(
		int $origin,
		string $system_request_id,
		string $status_id,
		string $update_remarks,
		string $type = ''
	): array {
		$response=[];
		$response['status']	= SUCCESS_STATUS;
		$response['data']	= array();
		$user_condition = [];
		//get amount details to process - safety
		$transaction_details_cond = array('origin' => intval($origin), 'system_transaction_id' => $system_request_id, 'type' => 'b2b');
		//Depending on status update
		$transaction_details = $this->custom_db->single_table_records('master_transaction_details', '*', $transaction_details_cond);
		if (valid_array($transaction_details['data']) == true && strtoupper($transaction_details['data'][0]['status']) == 'PENDING') {
			$response['data'] = $transaction_details['data'][0];
			//data to be updated
			$transaction_data = array(
				'update_remarks' => $update_remarks,
				'status' => strtolower($status_id),
				'updated_datetime' => db_current_datetime(),
				'updated_by_id' => intval($this->entity_user_id)
			);
			$amount = ($transaction_details['data'][0]['amount'] * $transaction_details['data'][0]['currency_conversion_rate']); //FORCE TO INR
			if (strtoupper($status_id) == 'ACCEPTED') {
				//Add to current balance and continue
				//$domain_origin = $transaction_details['data'][0]['domain_list_fk'];
				//update balance details and notification
				$this->load->model('private_management_model');
				//passing negative so balance gets deducted before processing
				$transaction_owner_id = $transaction_details['data'][0]['user_oid'];

				//Saving to Transaction Log
				$currency = $transaction_details['data'][0]['currency'];
				$currency_conversion_rate = $transaction_details['data'][0]['currency_conversion_rate'];
				$tr_remarks = (empty($update_remarks) == false ? trim($update_remarks) : 'Amount Deposited');
				$agent_transaction_amount = - ($amount); //Dont Change
				$this->save_transaction_details('transaction', $system_request_id, $agent_transaction_amount, 0, 0, $tr_remarks, 0, 0, $currency, $currency_conversion_rate, $transaction_owner_id);

				//Application Logger
				$user_id = $transaction_owner_id;
				$user_condition[] = array('user_id', '=', $user_id);
				$user_details = $this->user_model->get_user_details($user_condition);
				$agency_name = $user_details[0]['agency_name'];


				//Updating Agent Balnce with Debit Note
				if ((!empty($type) == true) && ($type == 'Debit')) {
					$response['data']['agent_balance'] = $this->private_management_model->update_b2b_debit_balance($transaction_owner_id, $amount);
					$remarks = 'Debit Request <span class="label label-success">' . strtoupper($status_id) . '</span>:' . $amount . ' ' . get_application_default_currency() . '(' . $agency_name . ')';
					$admin_user_id = $this->user_model->get_admin_user_id();
					$notification_users = array_merge($admin_user_id, array($user_id));
					$this->application_logger->balance_debit_request($remarks, array('system_transaction_id' => $system_request_id), $notification_users);
				} else {
					//Updating Agent Balance
					$response['data']['agent_balance'] = $this->private_management_model->update_b2b_balance($transaction_owner_id, $amount);
					$remarks = 'Deposit Request <span class="label label-success">' . strtoupper($status_id) . '</span>:' . $amount . ' ' . get_application_default_currency() . '(' . $agency_name . ')';
					$admin_user_id = $this->user_model->get_admin_user_id();
					$notification_users = array_merge($admin_user_id, array($user_id));
					$this->application_logger->balance_deposit_request($remarks, array('system_transaction_id' => $system_request_id), $notification_users);
				}
				$this->custom_db->update_record('master_transaction_details', $transaction_data, $transaction_details_cond);
			} elseif (strtoupper($status_id) != 'ACCEPTED') {
				$this->custom_db->update_record('master_transaction_details', $transaction_data, $transaction_details_cond);
			}
		} else {
			$response['status']	= FAILURE_STATUS;
		}
		return  $response;
	}
	public function ultra_process_balance_request(
		int $origin,
		string $system_request_id,
		string $status_id,
		string $update_remarks,
		string $type = ''
	): array {
		$response=[];
		$response['status']	= SUCCESS_STATUS;
		$response['data']	= array();
		$user_condition = [];
		//get amount details to process - safety
		$transaction_details_cond = array('origin' => intval($origin), 'system_transaction_id' => $system_request_id, 'type' => 'b2b');
		//Depending on status update
		$transaction_details = $this->custom_db->single_table_records('master_transaction_details', '*', $transaction_details_cond);
		if (valid_array($transaction_details['data']) == true && strtoupper($transaction_details['data'][0]['status']) == 'PENDING') {
			$response['data'] = $transaction_details['data'][0];
			//data to be updated
			$transaction_data = array(
				'update_remarks' => $update_remarks,
				'status' => strtolower($status_id),
				'updated_datetime' => db_current_datetime(),
				'updated_by_id' => intval($this->entity_user_id)
			);
			$amount = ($transaction_details['data'][0]['amount'] * $transaction_details['data'][0]['currency_conversion_rate']); //FORCE TO INR
			if (strtoupper($status_id) == 'ACCEPTED') {
				//Add to current balance and continue
				//$domain_origin = $transaction_details['data'][0]['domain_list_fk'];
				//update balance details and notification
				$this->load->model('private_management_model');
				//passing negative so balance gets deducted before processing
				$transaction_owner_id = $transaction_details['data'][0]['user_oid'];

				//Saving to Transaction Log
				$currency = $transaction_details['data'][0]['currency'];
				$currency_conversion_rate = $transaction_details['data'][0]['currency_conversion_rate'];
				$tr_remarks = (empty($update_remarks) == false ? trim($update_remarks) : 'Amount Deposited');
				$agent_transaction_amount = - ($amount); //Dont Change
				$this->save_transaction_details('transaction', $system_request_id, $agent_transaction_amount, 0, 0, $tr_remarks, 0, 0, $currency, $currency_conversion_rate, $transaction_owner_id);

				//Application Logger
				$user_id = $transaction_owner_id;
				$user_condition[] = array('user_id', '=', $user_id);
				$user_details = $this->user_model->get_user_details($user_condition);
				$agency_name = $user_details[0]['agency_name'];


				//Updating Agent Balnce with Debit Note
				if ((!empty($type) == true) && ($type == 'Debit')) {
					$response['data']['agent_balance'] = $this->private_management_model->update_b2b_debit_balance($transaction_owner_id, $amount);
					$remarks = 'Debit Request <span class="label label-success">' . strtoupper($status_id) . '</span>:' . $amount . ' ' . get_application_default_currency() . '(' . $agency_name . ')';
					$admin_user_id = $this->user_model->get_admin_user_id();
					$notification_users = array_merge($admin_user_id, array($user_id));
					$this->application_logger->balance_debit_request($remarks, array('system_transaction_id' => $system_request_id), $notification_users);
				} else {
					//Updating Agent Balance
					$response['data']['agent_balance'] = $this->private_management_model->update_b2b_balance($transaction_owner_id, $amount);
					$remarks = 'Deposit Request <span class="label label-success">' . strtoupper($status_id) . '</span>:' . $amount . ' ' . get_application_default_currency() . '(' . $agency_name . ')';
					$admin_user_id = $this->user_model->get_admin_user_id();
					$notification_users = array_merge($admin_user_id, array($user_id));
					$this->application_logger->balance_deposit_request($remarks, array('system_transaction_id' => $system_request_id), $notification_users);
				}
				$this->custom_db->update_record('master_transaction_details', $transaction_data, $transaction_details_cond);
			} elseif (strtoupper($status_id) != 'ACCEPTED') {
				$this->custom_db->update_record('master_transaction_details', $transaction_data, $transaction_details_cond);
			}
		} else {
			$response['status']	= FAILURE_STATUS;
		}
		return  $response;
	}
	/**
	 * Process Update Request
	 * @param number $origin
	 * @param string $system_request_id
	 * @param string $status_id
	 * @param string $update_remarks
	 *
	 * @return $response status of the update operation
	 */
	public function process_credit_limit_request(
		int $origin,
		string $system_request_id,
		string $status_id,
		string $update_remarks
	): array {
		$response=[];
		$user_condition=[];
		$response['status']	= SUCCESS_STATUS;
		$response['data']	= array();
		//get amount details to process - safety
		$transaction_details_cond = array('origin' => intval($origin), 'system_transaction_id' => $system_request_id, 'type' => 'b2b');
		//Depending on status update
		$transaction_details = $this->custom_db->single_table_records('master_transaction_details', '*', $transaction_details_cond);
		if (valid_array($transaction_details['data']) == true && strtoupper($transaction_details['data'][0]['status']) == 'PENDING') {
			$response['data'] = $transaction_details['data'][0];
			//data to be updated
			$transaction_data = array(
				'update_remarks' => $update_remarks,
				'status' => strtolower($status_id),
				'updated_datetime' => db_current_datetime(),
				'updated_by_id' => intval($this->entity_user_id)
			);
			$amount = ($transaction_details['data'][0]['amount'] * $transaction_details['data'][0]['currency_conversion_rate']); //FORCE TO INR
			if (strtoupper($status_id) == 'ACCEPTED') {
				//Add to current balance and continue
				// $domain_origin = $transaction_details['data'][0]['domain_list_fk'];
				//update balance details and notification
				$this->load->model('private_management_model');
				//passing negative so balance gets deducted before processing
				$transaction_owner_id = $transaction_details['data'][0]['user_oid'];

				//Saving to Transaction Log
				$currency = $transaction_details['data'][0]['currency'];
				$currency_conversion_rate = $transaction_details['data'][0]['currency_conversion_rate'];
				$tr_remarks = (empty($update_remarks) == false ? trim($update_remarks) : 'Amount Deposited');
				$agent_transaction_amount = ($amount); //Dont Change
				$this->save_transaction_details('transaction', $system_request_id, $agent_transaction_amount, 0, 0, $tr_remarks, 0, 0, $currency, $currency_conversion_rate, $transaction_owner_id);

				//Updating Agent Balance
				$response['data']['agent_balance'] = $this->private_management_model->update_b2b_credit_limit($transaction_owner_id, $amount);
				$this->custom_db->update_record('master_transaction_details', $transaction_data, $transaction_details_cond);

				//Application Logger
				$user_id = $transaction_owner_id;
				$user_condition[] = array('user_id', '=', $user_id);
				$user_details = $this->user_model->get_user_details($user_condition);
				$agency_name = $user_details[0]['agency_name'];
				$remarks = 'Credit Limit <span class="label label-success">' . strtoupper($status_id) . '</span>:' . $amount . ' ' . get_application_default_currency() . '(' . $agency_name . ')';
				$admin_user_id = $this->user_model->get_admin_user_id();
				$notification_users = array_merge($admin_user_id, array($user_id));
				$this->application_logger->credit_limit_request($remarks, array('system_transaction_id' => $system_request_id), $notification_users);
			} elseif (strtoupper($status_id) != 'ACCEPTED') {
				$this->custom_db->update_record('master_transaction_details', $transaction_data, $transaction_details_cond);
			}
		} else {
			$response['status']	= FAILURE_STATUS;
		}
		return  $response;
	}
	public function process_credit_limit_request1(
		int $origin,
		string $system_request_id,
		string $status_id,
		string $update_remarks
	): array {
		$response=[];
		$user_condition=[];
		$response['status']	= SUCCESS_STATUS;
		$response['data']	= array();
		//get amount details to process - safety
		$transaction_details_cond = array('origin' => intval($origin), 'system_transaction_id' => $system_request_id, 'type' => 'ultralux');
		//Depending on status update
		$transaction_details = $this->custom_db->single_table_records('master_transaction_details', '*', $transaction_details_cond);
		if (valid_array($transaction_details['data']) == true && strtoupper($transaction_details['data'][0]['status']) == 'PENDING') {
			$response['data'] = $transaction_details['data'][0];
			//data to be updated
			$transaction_data = array(
				'update_remarks' => $update_remarks,
				'status' => strtolower($status_id),
				'updated_datetime' => db_current_datetime(),
				'updated_by_id' => intval($this->entity_user_id)
			);
			$amount = ($transaction_details['data'][0]['amount'] * $transaction_details['data'][0]['currency_conversion_rate']); //FORCE TO INR
			if (strtoupper($status_id) == 'ACCEPTED') {
				//Add to current balance and continue
				// $domain_origin = $transaction_details['data'][0]['domain_list_fk'];
				//update balance details and notification
				$this->load->model('private_management_model');
				//passing negative so balance gets deducted before processing
				$transaction_owner_id = $transaction_details['data'][0]['user_oid'];

				//Saving to Transaction Log
				$currency = $transaction_details['data'][0]['currency'];
				$currency_conversion_rate = $transaction_details['data'][0]['currency_conversion_rate'];
				$tr_remarks = (empty($update_remarks) == false ? trim($update_remarks) : 'Amount Deposited');
				$agent_transaction_amount = ($amount); //Dont Change
				$this->save_transaction_details('transaction', $system_request_id, $agent_transaction_amount, 0, 0, $tr_remarks, 0, 0, (string)$currency, $currency_conversion_rate, $transaction_owner_id);

				//Updating Agent Balance
				$response['data']['agent_balance'] = $this->private_management_model->update_b2b_credit_limit($transaction_owner_id, $amount);
				$this->custom_db->update_record('master_transaction_details', $transaction_data, $transaction_details_cond);

				//Application Logger
				$user_id = $transaction_owner_id;
				$user_condition[] = array('user_id', '=', $user_id);
				$user_details = $this->user_model->get_user_details($user_condition);
				$agency_name = $user_details[0]['agency_name'];
				$remarks = 'Credit Limit <span class="label label-success">' . strtoupper($status_id) . '</span>:' . $amount . ' ' . get_application_default_currency() . '(' . $agency_name . ')';
				$admin_user_id = $this->user_model->get_admin_user_id();
				$notification_users = array_merge($admin_user_id, array($user_id));
				$this->application_logger->credit_limit_request($remarks, array('system_transaction_id' => $system_request_id), $notification_users);
			} elseif (strtoupper($status_id) != 'ACCEPTED') {
				$this->custom_db->update_record('master_transaction_details', $transaction_data, $transaction_details_cond);
			}
		} else {
			$response['status']	= FAILURE_STATUS;
		}
		return  $response;
	}
	/**
	 * update domain balance details
	 * @param number $domain_origin	doamin unique key
	 * @param number $amount		amount to be added or deducted(-100 or +100)
	 */
	public function update_domain_balance(int $origin, float $amount): float
	{
		$current_balance = 0;
		$cond = array('origin' => intval($origin));

		$details = $this->custom_db->single_table_records('b2b_user_details', 'balance', $cond);

		if ($details['status'] == true) {
			$details['data'][0]['balance'] = $current_balance = ($details['data'][0]['balance'] + $amount);

			$this->custom_db->update_record('b2b_user_details', $details['data'][0], $cond);
		}
		return $current_balance;
	}
	/**
	 * Balu A
	 * B2B Agent Commission Details: Flight, Hotel, Bus
	 * @param $agent_fk
	 */
	function get_commission_details(int $agent_fk): array
	{
		$response=[];
		$response['status'] = FAILURE_STATUS;
		$response['data'] = array();

		$agent_details_query = 'select U.*,BUD.logo as agent_logo, UT.user_type
								FROM user AS U 
								INNER JOIN user_type AS UT ON U.user_type=UT.origin
								left JOIN b2b_user_details AS BUD ON U.user_id=BUD.user_oid 
								WHERE U.domain_list_fk =' . get_domain_auth_id() . ' and U.user_id = ' . intval($agent_fk);
		$flight_commission_query = 'select BFCD.* from b2b_flight_commission_details as BFCD 
								inner join user as U on BFCD.agent_fk = U.user_id
								where BFCD.domain_list_fk =' . get_domain_auth_id() . ' and BFCD.agent_fk=' . intval($agent_fk) . ' and BFCD.type="specific"';
		/*$bus_commission_query = 'select BBCD.* from b2b_bus_commission_details as BBCD 
								inner join user as U on BBCD.agent_fk = U.user_id
								where BBCD.domain_list_fk =' . get_domain_auth_id() . ' and BBCD.agent_fk=' . intval($agent_fk) . ' and BBCD.type="specific"';*/

		/*$sightseeing_commission_query = 'select BSCD.* from b2b_sightseeing_commission_details as BSCD 
								inner join user as U on BSCD.agent_fk = U.user_id
								where BSCD.domain_list_fk =' . get_domain_auth_id() . ' and BSCD.agent_fk=' . intval($agent_fk) . ' and BSCD.type="specific"';

		$transfer_commission_query = 'select BTCD.* from b2b_transfer_commission_details as BTCD 
								inner join user as U on BTCD.agent_fk = U.user_id
								where BTCD.domain_list_fk =' . get_domain_auth_id() . ' and BTCD.agent_fk=' . intval($agent_fk) . ' and BTCD.type="specific"';*/


		$response['data']['agent_details']				= $this->db->query($agent_details_query)->row_array();
		$response['data']['flight_commission_details']	= $this->db->query($flight_commission_query)->row_array();;
		$response['data']['hotel_commission_details']	= '';
		//$response['data']['bus_commission_details']		= $this->db->query($bus_commission_query)->row_array();

		//$response['data']['sightseeing_commission_details'] = $this->db->query($sightseeing_commission_query)->row_array();

		//$response['data']['transfer_commission_details'] = $this->db->query($transfer_commission_query)->row_array();


		if (valid_array($response['data']['agent_details']) == true) {
			$response['status'] = SUCCESS_STATUS;
		}
		return $response;
	}
	function ultra_get_commission_details(int $agent_fk): array
	{
		$response=[];
		$response['status'] = FAILURE_STATUS;
		$response['data'] = array();

		$agent_details_query = 'select U.*,BUD.logo as agent_logo, UT.user_type
								FROM user AS U 
								INNER JOIN user_type AS UT ON U.user_type=UT.origin
								left JOIN b2b_user_details AS BUD ON U.user_id=BUD.user_oid 
								WHERE U.domain_list_fk =' . get_domain_auth_id() . ' and U.user_id = ' . intval($agent_fk);
		$flight_commission_query = 'select BFCD.* from b2b_flight_commission_details as BFCD 
								inner join user as U on BFCD.agent_fk = U.user_id
								where BFCD.domain_list_fk =' . get_domain_auth_id() . ' and BFCD.agent_fk=' . intval($agent_fk) . ' and BFCD.type="specific"';
		/*$bus_commission_query = 'select BBCD.* from b2b_bus_commission_details as BBCD 
								inner join user as U on BBCD.agent_fk = U.user_id
								where BBCD.domain_list_fk =' . get_domain_auth_id() . ' and BBCD.agent_fk=' . intval($agent_fk) . ' and BBCD.type="specific"';*/

		/*$sightseeing_commission_query = 'select BSCD.* from b2b_sightseeing_commission_details as BSCD 
								inner join user as U on BSCD.agent_fk = U.user_id
								where BSCD.domain_list_fk =' . get_domain_auth_id() . ' and BSCD.agent_fk=' . intval($agent_fk) . ' and BSCD.type="specific"';

		$transfer_commission_query = 'select BTCD.* from b2b_transfer_commission_details as BTCD 
								inner join user as U on BTCD.agent_fk = U.user_id
								where BTCD.domain_list_fk =' . get_domain_auth_id() . ' and BTCD.agent_fk=' . intval($agent_fk) . ' and BTCD.type="specific"';*/


		$response['data']['agent_details']				= $this->db->query($agent_details_query)->row_array();
		$response['data']['flight_commission_details']	= $this->db->query($flight_commission_query)->row_array();;
		$response['data']['hotel_commission_details']	= '';
		//$response['data']['bus_commission_details']		= $this->db->query($bus_commission_query)->row_array();

		//$response['data']['sightseeing_commission_details'] = $this->db->query($sightseeing_commission_query)->row_array();

		//$response['data']['transfer_commission_details'] = $this->db->query($transfer_commission_query)->row_array();


		if (valid_array($response['data']['agent_details']) == true) {
			$response['status'] = SUCCESS_STATUS;
		}
		return $response;
	}
	/**
	 * Balu A
	 * B2B Agent Commission Details: Flight, Bus
	 */
	function agent_commission_details(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array|object
	{
		$response=[];
		$condition = $this->custom_db->get_custom_condition($condition);
		$response['status'] = FAILURE_STATUS;
		$response['data'] = array();
		if (!$count) {
			$agent_details_query = 'select U.*,BFCD.value as flight_commission_value,BFCD.api_value as flight_api_value,BFCD.value_type as flight_commission_type
									
									FROM user AS U 
									INNER JOIN user_type AS UT ON U.user_type=UT.origin
									left join b2b_flight_commission_details as BFCD on U.user_id=BFCD.agent_fk and BFCD.type="specific"
									


									WHERE U.user_type = ' . B2B_USER . ' and U.domain_list_fk =' . get_domain_auth_id() . $condition . '
									ORDER BY U.user_id DESC limit ' . $offset . ', ' . $limit;
			$response['data']['agent_commission_details']				= $this->db->query($agent_details_query)->result_array();
			return $response;
		} else {
			return $this->db->query('SELECT count(*) as total FROM user AS U, user_type AS UT, api_country_list AS ACL
				 WHERE U.user_type=UT.origin 
				 AND U.country_code=ACL.origin and U.domain_list_fk =' . get_domain_auth_id() . $condition . ' limit ' . $limit . ' offset ' . $offset)->row();
		}
	}
	/**
	 * Balu A
	 * B2B Agent Commission Details: Flight, Bus,Sightseeing
	 * @param $search_filter_condition => (condition)
	 */
	function filter_agent_commission_details(string $search_filter_condition = '', bool $count = false, int $offset = 0, int $limit = 100000000000): array
	{
		$response=[];
		$response['status'] = FAILURE_STATUS;
		$response['data'] = array();
		if (empty($search_filter_condition) == false) {
			$search_filter_condition = ' and' . $search_filter_condition;
		}
		if (!$count) {
			$agent_details_query = 'select U.*,BFCD.value as flight_commission_value,BFCD.api_value as flight_api_value,BFCD.value_type as flight_commission_type,
									BBCD.value as bus_commission_value,BBCD.api_value as bus_api_value,BBCD.value_type as bus_commission_type,

									BSCD.value as sightseeing_commission_value,BSCD.api_value as sightseeing_api_value,BSCD.value_type as sightseeing_commission_type,

									BTCD.value as transfer_commission_value,BTCD.api_value as transfer_api_value,BTCD.value_type as transfer_commission_type


									FROM user AS U 
									INNER JOIN user_type AS UT ON U.user_type=UT.origin
									left join b2b_flight_commission_details as BFCD on U.user_id=BFCD.agent_fk and BFCD.type="specific"
									left join b2b_bus_commission_details as BBCD on U.user_id=BBCD.agent_fk and BBCD.type="specific"

									left join b2b_sightseeing_commission_details as BSCD on U.user_id=BSCD.agent_fk and BSCD.type="specific"

									left join b2b_transfer_commission_details as BTCD on U.user_id=BTCD.agent_fk and BTCD.type="specific"



									WHERE U.user_type = ' . B2B_USER . ' and U.domain_list_fk =' . get_domain_auth_id() . $search_filter_condition . '
									ORDER BY U.user_id DESC limit ' . $offset . ', ' . $limit;
			$response['data']['agent_commission_details']				= $this->db->query($agent_details_query)->result_array();
			return $response;
		} else {
			return $this->db->query('SELECT count(*) as total FROM user AS U, user_type AS UT, api_country_list AS ACL
				 WHERE U.user_type=UT.origin AND U.user_type = ' . B2B_USER . ' and U.domain_list_fk =' . get_domain_auth_id() . ' and  U.country_code=ACL.origin' . $search_filter_condition . ' limit ' . $limit . ' offset ' . $offset)->row();
		}
	}
	function ultra_filter_agent_commission_details(string $search_filter_condition = '', bool $count = false, int $offset = 0, int $limit = 100000000000): array
	{
		$response=[];
		$response['status'] = FAILURE_STATUS;
		$response['data'] = array();
		if (empty($search_filter_condition) == false) {
			$search_filter_condition = ' and' . $search_filter_condition;
		}
		if (!$count) {
			$agent_details_query = 'select U.*,BFCD.value as flight_commission_value,BFCD.api_value as flight_api_value,BFCD.value_type as flight_commission_type
									FROM user AS U 
									INNER JOIN user_type AS UT ON U.user_type=UT.origin
									left join b2b_flight_commission_details as BFCD on U.user_id=BFCD.agent_fk and BFCD.type="specific"
									



									WHERE U.user_type = ' . ULTRALUX_USER . ' and U.domain_list_fk =' . get_domain_auth_id() . $search_filter_condition . '
									ORDER BY U.user_id DESC limit ' . $offset . ', ' . $limit;
			$response['data']['agent_commission_details']				= $this->db->query($agent_details_query)->result_array();
			return $response;
		} else {
			return $this->db->query('SELECT count(*) as total FROM user AS U, user_type AS UT, api_country_list AS ACL
				 WHERE U.user_type=UT.origin AND U.user_type = ' . ULTRALUX_USER . ' and U.domain_list_fk =' . get_domain_auth_id() . ' and  U.country_code=ACL.origin' . $search_filter_condition . ' limit ' . $limit . ' offset ' . $offset)->result_array();
		}
	}
	/**
	 * Balu A
	 * B2B Default Commission Details: Flight, Hotel, Bus,Sightseeing
	 * @param $agent_fk
	 */
	function default_commission_details(): array
	{
		$response=[];
		$response['status'] = SUCCESS_STATUS;
		$response['data'] = array();

		$flight_commission_query = 'select BFCD.* from b2b_flight_commission_details as BFCD
								where BFCD.domain_list_fk =' . get_domain_auth_id() . ' and BFCD.type="generic"';
		/*$bus_commission_query = 'select BBCD.* from b2b_bus_commission_details as BBCD
								where BBCD.domain_list_fk =' . get_domain_auth_id() . ' and BBCD.type="generic"';*/

	/*	$sightseeing_commission_query = 'select BSCD.* from b2b_sightseeing_commission_details as BSCD
								where BSCD.domain_list_fk =' . get_domain_auth_id() . ' and BSCD.type="generic"';

		$transfer_commission_query = 'select BTCD.* from b2b_transfer_commission_details as BTCD
								where BTCD.domain_list_fk =' . get_domain_auth_id() . ' and BTCD.type="generic"';
*/

		$response['data']['flight_commission_details']	= $this->db->query($flight_commission_query)->row_array();;
		$response['data']['hotel_commission_details']	= '';
		//$response['data']['bus_commission_details']		= $this->db->query($bus_commission_query)->row_array();
	//	$response['data']['sightseeing_commission_details'] = $this->db->query($sightseeing_commission_query)->row_array();
		//$response['data']['transfer_commission_details'] = $this->db->query($transfer_commission_query)->row_array();

		return $response;
	}
	/**
	 * Balu A 
	 */
	function auto_suggest_agency_name(string $chars, int $limit = 15): array
	{
		$query = 'select U.*
					FROM user AS U 
					INNER JOIN user_type AS UT ON U.user_type=UT.origin 
					WHERE U.agency_name!="" and U.domain_list_fk =' . get_domain_auth_id() . ' and 
					(U.uuid like "%' . $chars . '%" OR U.agency_name like "%' . $chars . '%" OR U.first_name like "%' . $chars . '%" OR U.last_name like "%' . $chars . '%" OR U.email like "%' . $chars . '%" OR U.phone like "%' . $chars . '%" )
					order by U.agency_name asc limit 0, ' . $limit;
		return $this->db->query($query)->result_array();
	}
	/**
	 * Balu A
	 * Bank Account Details
	 */
	function bank_account_details(): array
	{
		$query = 'SELECT BAD.*,CONCAT(U.first_name," ",U.last_name) as created_by_name 
		        FROM bank_account_details BAD
		        JOIN user U on U.user_id=BAD.created_by_id
		        where BAD.domain_list_fk=' . get_domain_auth_id();
		$tmp_data = $this->db->query($query);
		if ($tmp_data->num_rows() > 0) {
			$tmp_data = $tmp_data->result_array();
			$data = array('status' => QUERY_SUCCESS, 'data' => $tmp_data);
		} else {
			$data = array('status' => QUERY_FAILURE);
		}
		return $data;
	}
	/**
	 * Update Balance of Agent
	 * @param number $amount Amount to be added or deducted
	 */
	function update_agent_balance(float $amount, int $agent_user_id): float
	{
		$current_balance = 0;
		$cond = array('user_oid' => intval($agent_user_id));
		$details = $this->custom_db->single_table_records('b2b_user_details', 'balance', $cond);
		if ($details['status'] == true) {
			$details['data'][0]['balance'] = $current_balance = ($details['data'][0]['balance'] + $amount);
			$this->custom_db->update_record('b2b_user_details', $details['data'][0], $cond);
			$this->balance_notification($current_balance);
		}
		return $current_balance;
	}
	/**
	 * Balu A
	 * if less than limit then send notification
	 */
	function balance_notification(float $current_balance): void
	{
		$condition = array('agent_fk' => intval($this->entity_user_id));
		$details = $this->custom_db->single_table_records('agent_balance_alert_details', '*', $condition);
		if ($details['status'] == true) {
			$threshold_amount = $details['data'][0]['threshold_amount'];
			$mobile_number = trim($details['data'][0]['mobile_number']);
			$email_id = trim($details['data'][0]['email_id']);
			$enable_sms_notification = $details['data'][0]['enable_sms_notification'];
			$enable_email_notification = $details['data'][0]['enable_email_notification'];
			if ($current_balance <= $threshold_amount) {
				//FIXME:Send Notification
				//SMS ALERT
				if ($enable_sms_notification == ACTIVE && empty($mobile_number) == false) {
					//Send SMS Alert for Low Balance
				}
				//EMAIL NOTIFICATION
				if ($enable_email_notification == ACTIVE && empty($email_id) == false) {
					//Send Email Notification for Low Balance
					//$subject = $this->agency_name . '- Low Balance Alert';
					//$message = 'Dear ' . $this->entity_name . '<br/> <h1>Your Agent Balance is Low.</h1><br/><h2>Agent Balance as on ' . date("Y-m-d h:i:sa") . 'is : ' . COURSE_LIST_DEFAULT_CURRENCY_VALUE . ' ' . $threshold_amount . '/-</h2><h3>Please Recharge Your Account to enjoy UnInterrupted Bookings. :)</h3>';
					//$this->load->library('provab_mailer');
					// $mail_status = $this->provab_mailer->send_mail($email_id, $subject, $message);
				}
			}
		}
	}
	/**
	 * Save transaction logging for security purpose
	 * @param string $transaction_type
	 * @param string $app_reference
	 * @param number $fare
	 * @param number $domain_markup
	 * @param number $level_one_markup
	 * @param string $remarks
	 */
	function save_transaction_details(
		string $transaction_type,
		string $app_reference,
		float $fare,
		float $domain_markup,
		float $level_one_markup,
		string $remarks,
		float $convinence = 0.0,
		float $discount = 0.0,
		string $currency = 'USD',
		float $currency_conversion_rate = 1.0,
		int $transaction_owner_id = 0
	): void {
		$transaction_log=[];
		$transaction_owner_id = intval(intval($transaction_owner_id) > 0 ? $transaction_owner_id : $this->entity_user_id);

		$transaction_log['system_transaction_id']	= date('Ymd-His') . '-S-' . rand(1, 10000);
		$transaction_log['transaction_type']		= $transaction_type;
		$transaction_log['domain_origin']			= get_domain_auth_id();
		$transaction_log['app_reference']			= $app_reference;
		$transaction_log['fare']					= $fare;
		$transaction_log['level_one_markup']		= $level_one_markup;
		$transaction_log['domain_markup']			= $domain_markup;
		$transaction_log['remarks']					= $remarks;
		$transaction_log['transaction_owner_id'] 	= $transaction_owner_id;
		$transaction_log['created_by_id']			= intval($this->entity_user_id);
		$transaction_log['created_datetime']		= date('Y-m-d H:i:s', time());

		$transaction_log['convinence_fees']			= $convinence;
		$transaction_log['promocode_discount']		= $discount;
		$transaction_log['currency']				= $currency;
		$transaction_log['currency_conversion_rate'] = $currency_conversion_rate;

		//Opening and Closing Balance
		$total_transaction_amount = ($fare + $level_one_markup + $domain_markup);
		$opening_closing_balance_details = $this->get_opening_closing_balance($transaction_owner_id, $total_transaction_amount);
		$transaction_log['opening_balance'] = $opening_closing_balance_details['opening_balance'];
		$transaction_log['closing_balance'] = $opening_closing_balance_details['closing_balance'];

		$this->custom_db->insert_record('transaction_log', $transaction_log);
	}
	/**
	 * Get Opening and Closing Balance Details
	 */
	function get_opening_closing_balance(int $agent_id, float $total_transaction_amount): array
	{
		$data=[];
		$total_transaction_amount = floatval($total_transaction_amount);
		//Get current agent balance
		$query = 'SELECT balance AS closing_balance FROM b2b_user_details WHERE user_oid = ' . intval($agent_id);
		$current_balance_details = $this->db->query($query)->row_array();
		$opening_balance = 			$current_balance_details['closing_balance'];
		$total_transaction_amount =	($total_transaction_amount) < 0 ? abs($total_transaction_amount) : - ($total_transaction_amount); //if -Ve, convert to +Ve and ViceVersa
		$closing_balance = ($opening_balance + $total_transaction_amount); //Closing Balance
		$data['opening_balance'] = round(floatval($opening_balance), 4);
		$data['closing_balance'] = round(floatval($closing_balance), 4);
		return $data;
	}

	/*
	 Get Agent Details 
	*/
	function get_agent_details(int $agent_id): array
	{

		$query = 'SELECT 
					B2B.balance AS balance,B2B.credit_limit, B2B.due_amount,
					U.agency_name AS agency_name, 
					CC.country AS agent_base_currency, 
					CC.id AS agent_base_currency_fk 
				FROM 
					b2b_user_details AS B2B 
				JOIN currency_converter CC ON CC.id = B2B.currency_converter_fk 
				JOIN user AS U ON U.user_id = B2B.user_oid 
				WHERE B2B.user_oid = ' . intval($agent_id);

		$agent_details = $this->db->query($query)->row_array();
		return $agent_details;
	}

	/**
	 * Agent Transaction Log
	 * @param unknown_type $condition
	 * @param unknown_type $count
	 * @param unknown_type $offset
	 * @param unknown_type $limit
	 */
	public function agent_account_ledger(array $condition = [], bool $count = false, int $offset = 0, int $limit = 1000000000): array
	{
		$data = array();
		$condition = $this->custom_db->get_custom_condition($condition);
		$agent_filter = '';
		$transaction_activated_from_date = '2017-01-10'; //DONT REMOVE THIS CONDITION
		$agent_filter = ' AND U.user_type =' . B2B_USER;

		if ($count) {
			$query = 'select count(*) as total_records from transaction_log TL 
						join user U on U.user_id=TL.transaction_owner_id
						where TL.origin>0 and date(TL.created_datetime)>= "' . $transaction_activated_from_date . '" ' . $agent_filter . ' ' . $condition;
			$total_records = $this->db->query($query)->row_array();
			$data['total_records'] = $total_records['total_records'];
		} else {
			$query = 'SELECT U.agency_name,TL.*,
			 CASE TL.transaction_type
			   WHEN "flight" THEN 
			   					(select concat("LeadPax:", PD.first_name," ",PD.last_name, " PNR: ",group_concat(distinct(FTD. pnr))) as REF from flight_booking_transaction_details FTD,flight_booking_passenger_details PD
			   						WHERE FTD.app_reference = TL.app_reference and PD.app_reference = TL.app_reference 
			   						group by FTD.app_reference)
			   WHEN "hotel" THEN 
			   					(select concat("LeadPax:", PD.first_name," ",PD.last_name, " Booking ID: ",HTD.booking_id," Booking Ref.: ",HTD.booking_reference) as REF from hotel_booking_details HTD,hotel_booking_pax_details PD 
			   						WHERE HTD.app_reference = TL.app_reference and PD.app_reference = TL.app_reference 
			   						group by HTD.app_reference)
			 
			   WHEN "transaction" THEN 
			   					(SELECT concat("Amount ",MTD.amount) as REF FROM `master_transaction_details` MTD WHERE MTD.`system_transaction_id` = TL.app_reference group by MTD.system_transaction_id)
			  END as "REF"
			FROM
			transaction_log TL 
			join user U on U.user_id = TL.transaction_owner_id 
			where 1=1 and date(TL.created_datetime)>= "' . $transaction_activated_from_date . '" ' . $agent_filter . ' ' . $condition . ' order by TL.created_datetime desc limit ' . $offset . ', ' . $limit;
			//echo $query;
			//exit;
			$data['data'] = $this->db->query($query)->result_array();
		}
		return $data;
	}
	public function ultra_agent_account_ledger(array $condition = [], bool $count = false, int $offset = 0, int $limit = 1000000000): array
	{

		ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

		$data = array();
		$condition = $this->custom_db->get_custom_condition($condition);
		$agent_filter = '';
		$transaction_activated_from_date = '2017-01-10'; //DONT REMOVE THIS CONDITION
		$agent_filter = ' AND U.user_type =' . ULTRALUX_USER;

		if ($count) {
			$query = 'select count(*) as total_records from transaction_log TL 
						join user U on U.user_id=TL.transaction_owner_id
						where TL.origin>0 and date(TL.created_datetime)>= "' . $transaction_activated_from_date . '" ' . $agent_filter . ' ' . $condition;
			$total_records = $this->db->query($query)->row_array();
			$data['total_records'] = $total_records['total_records'];
		} else {
			$query = 'SELECT U.agency_name,TL.*,
			 CASE TL.transaction_type
			   WHEN "flight" THEN 
			   					(select concat("LeadPax:", PD.first_name," ",PD.last_name, " PNR: ",group_concat(distinct(FTD. pnr))) as REF from flight_booking_transaction_details FTD,flight_booking_passenger_details PD
			   						WHERE FTD.app_reference = TL.app_reference and PD.app_reference = TL.app_reference 
			   						group by FTD.app_reference)
			   WHEN "hotel" THEN 
			   					(select concat("LeadPax:", PD.first_name," ",PD.last_name, " Booking ID: ",HTD.booking_id," Booking Ref.: ",HTD.booking_reference) as REF from hotel_booking_details HTD,hotel_booking_pax_details PD 
			   						WHERE HTD.app_reference = TL.app_reference and PD.app_reference = TL.app_reference 
			   						group by HTD.app_reference)
			  
			   WHEN "transaction" THEN 
			   					(SELECT concat("Amount ",MTD.amount) as REF FROM `master_transaction_details` MTD WHERE MTD.`system_transaction_id` = TL.app_reference group by MTD.system_transaction_id)
			  END as "REF"
			FROM
			transaction_log TL 
			join user U on U.user_id = TL.transaction_owner_id 
			where 1=1 and date(TL.created_datetime)>= "' . $transaction_activated_from_date . '" ' . $agent_filter . ' ' . $condition . ' order by TL.created_datetime desc limit ' . $offset . ', ' . $limit;
			/*echo $query;
			exit;*/
			$data['data'] = $this->db->query($query)->result_array();
		}
		return $data;
	}


	/*
		Get All Active Agent List
	*/
	function agent_list(): array
	{

		$query = 'select user_id, agency_name FROM user WHERE status = ' . ACTIVE . ' AND user_type = ' . B2B_USER . '
					order by user_id asc';
		$data = $this->db->query($query)->result_array();
		return $data;
	}

	/**
	 * Returns Agent Currency Conversion Rate
	 * @param string $agent_currency;EX: USD, INR
	 */
	public function get_currency_conversion_rate(string $currency): array
	{
		$query = 'select value as conversion_rate from currency_converter where country="' . trim($currency) . '"';
		return $this->db->query($query)->row_array();
	}
	/**
	 * save master transaction details request
	 * @param array $details
	 */
	function process_direct_credit_debit_transaction(array $details, string $type = ''): void
	{
		$master_transaction_details=[];
		//SAVE TRANSACTION DETAILS
		$app_reference = trim($details['app_reference']);
		if (strlen($app_reference) >= 5 && strlen($app_reference) <= 20) {
			$system_transaction_id = $app_reference;
		} else {
			$system_transaction_id = 'DEP-' . $this->entity_user_id . time();
		}
		$remarks = trim($details['remarks']);
		$master_transaction_details['system_transaction_id'] = $system_transaction_id;
		$master_transaction_details['domain_list_fk'] = get_domain_auth_id();
		$master_transaction_details['transaction_type'] = 'Wallet';
		$master_transaction_details['amount'] = $details['amount'];
		$master_transaction_details['currency'] = $details['currency'];
		$master_transaction_details['currency_conversion_rate'] = $details['currency_conversion_rate'];
		$master_transaction_details['date_of_transaction'] = db_current_datetime();
		$master_transaction_details['bank'] = 'N/A';
		$master_transaction_details['branch'] = 'N/A';
		$master_transaction_details['transaction_number'] = isset($details['transaction_number']) ? $details['transaction_number'] : 'N/A';
		$master_transaction_details['status'] = 'pending';
		$master_transaction_details['type'] = 'b2b';
		$master_transaction_details['user_oid'] = $details['agent_list_fk'];
		$master_transaction_details['remarks'] = $remarks;
		$master_transaction_details['created_datetime'] = db_current_datetime();
		$master_transaction_details['created_by_id'] = $this->entity_user_id;
		$master_transaction_details['image'] = '';
		$insert_id = $this->custom_db->insert_record('master_transaction_details', $master_transaction_details);

		//UPDATE AGENT BALANCE AND SAVE INTO TRANSACTION LOG
		$insert_id = $insert_id['insert_id'];
		$status_id = 'accepted';
		$update_remarks = '';
		$update_remarks .= $details['issued_for'] . '<br/>';
		$update_remarks .= 'Reference: ' . trim($details['app_reference']) . '<br/>';
		$update_remarks .= $remarks;
		$this->process_balance_request($insert_id, $system_transaction_id, $status_id, $update_remarks, $type);
	}
}
