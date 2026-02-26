<?php

/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Flight Model
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */
class Flight_Model extends CI_Model
{
	/**
	 *TEMPORARY FUNCTION NEEDS TO BE CLEANED UP IN PRODUCTION ENVIRONMENT
	 */
	public function get_static_response(int $token_id): ?array
	{
		$static_response = $this->custom_db->single_table_records('test', '*', array('origin' => intval($token_id)));
		return json_decode($static_response['data'][0]['test'], true);
	}
	/**
	 * Flight booking report
	 *
	 */
	public function booking(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array|string
	{
		$response=[];
		$condition = $this->custom_db->get_custom_condition($condition);
		//BT, CD, ID
		if ($count) {
			$query = 'select count(distinct(BD.app_reference)) AS total_records from flight_booking_details BD
					where domain_origin=' . get_domain_auth_id() . '' . $condition;
			$data = $this->db->query($query)->row_array();
			return $data['total_records'];
		} else {
			$this->load->library('booking_data_formatter');
			$response['status'] = SUCCESS_STATUS;
			$response['data'] = array();
			$booking_itinerary_details	= array();
			$booking_customer_details	= array();
			$booking_transaction_details = array();
			$cancellation_details = array();
			// $payment_details = array();
			//Booking Details
			$bd_query = 'select * from flight_booking_details AS BD
						WHERE BD.domain_origin=' . get_domain_auth_id() . ' ' . $condition . '
						order by BD.created_datetime desc, BD.origin desc limit ' . $offset . ', ' . $limit;
			$booking_details	= $this->db->query($bd_query)->result_array();
			$app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);
			if (empty($app_reference_ids) == false) {
				//Itinerary Details
				$id_query = 'select * from flight_booking_itinerary_details AS ID
							WHERE ID.app_reference IN (' . $app_reference_ids . ')';
				//Transaction Details
				$td_query = 'select * from flight_booking_transaction_details AS TD
							WHERE TD.app_reference IN (' . $app_reference_ids . ')';
				//Customer and Ticket Details
				$cd_query = 'select CD.*,FPTI.TicketId,FPTI.TicketNumber,FPTI.IssueDate,FPTI.Fare,FPTI.SegmentAdditionalInfo
							from flight_booking_passenger_details AS CD
							left join flight_passenger_ticket_info FPTI on CD.origin=FPTI.passenger_fk
							WHERE CD.flight_booking_transaction_details_fk IN 
							(select TD.origin from flight_booking_transaction_details AS TD 
							WHERE TD.app_reference IN (' . $app_reference_ids . '))';
				//Cancellation Details
				$cancellation_details_query = 'select FCD.*
						from flight_booking_passenger_details AS CD
						left join flight_cancellation_details AS FCD ON FCD.passenger_fk=CD.origin
						WHERE CD.flight_booking_transaction_details_fk IN 
						(select TD.origin from flight_booking_transaction_details AS TD 
						WHERE TD.app_reference IN (' . $app_reference_ids . '))';
				//$payment_details_query = '';
				$booking_itinerary_details	= $this->db->query($id_query)->result_array();
				$booking_customer_details	= $this->db->query($cd_query)->result_array();
				$booking_transaction_details = $this->db->query($td_query)->result_array();
				$cancellation_details = $this->db->query($cancellation_details_query)->result_array();
				//$payment_details = $this->db->query($payment_details_query)->result_array();
			}

			$response['data']['booking_details']			= $booking_details;
			$response['data']['booking_itinerary_details']	= $booking_itinerary_details;
			$response['data']['booking_transaction_details']	= $booking_transaction_details;
			$response['data']['booking_customer_details']	= $booking_customer_details;
			$response['data']['cancellation_details']	= $cancellation_details;
			//$response['data']['payment_details']	= $payment_details;
			return $response;
		}
	}
	/**
	 * Read Individual booking details - dont use it to generate table
	 * @param $app_reference
	 * @param $booking_source
	 * @param $booking_status
	 */
	function get_booking_details(string $app_reference, string $booking_source = '', string $booking_status = ''): array
	{
		$response=[];
		$response['status'] = FAILURE_STATUS;
		$response['data'] = array();
		//Booking Details
		$bd_query = 'select * from flight_booking_details AS BD WHERE BD.app_reference like ' . $this->db->escape($app_reference);
		if (empty($booking_source) == false) {
			$bd_query .= '	AND BD.booking_source = ' . $this->db->escape($booking_source);
		}
		if (empty($booking_status) == false) {
			$bd_query .= ' AND BD.status = ' . $this->db->escape($booking_status);
		}
		//Itinerary Details
		$id_query = 'select * from flight_booking_itinerary_details AS ID WHERE ID.app_reference=' . $this->db->escape($app_reference) . ' order by origin ASC';
		//Transaction Details
		$td_query = 'select * from flight_booking_transaction_details AS CD WHERE CD.app_reference=' . $this->db->escape($app_reference) . ' order by origin ASC';
		//Customer and Ticket Details
		$cd_query = 'select distinct CD.*,FPTI.api_passenger_origin,FPTI.TicketId,FPTI.TicketNumber,FPTI.IssueDate,FPTI.Fare,FPTI.SegmentAdditionalInfo
						from flight_booking_passenger_details AS CD
						left join flight_passenger_ticket_info FPTI on CD.origin=FPTI.passenger_fk
						WHERE CD.flight_booking_transaction_details_fk IN 
						(select TD.origin from flight_booking_transaction_details AS TD 
						WHERE TD.app_reference =' . $this->db->escape($app_reference) . ')';
		//Cancellation Details
		$cancellation_details_query = 'select FCD.*
						from flight_booking_passenger_details AS CD
						left join flight_cancellation_details AS FCD ON FCD.passenger_fk=CD.origin
						WHERE CD.flight_booking_transaction_details_fk IN 
						(select TD.origin from flight_booking_transaction_details AS TD 
						WHERE TD.app_reference =' . $this->db->escape($app_reference) . ')';

		//Baggage Details
		$baggage_query = 'select CD.flight_booking_transaction_details_fk,
						concat(CD.first_name," ", CD.last_name) as pax_name,FBG.*
						from flight_booking_passenger_details AS CD
						join flight_booking_baggage_details FBG on CD.origin=FBG.passenger_fk
						WHERE CD.flight_booking_transaction_details_fk IN 
						(select TD.origin from flight_booking_transaction_details AS TD 
						WHERE TD.app_reference =' . $this->db->escape($app_reference) . ')';
		//Meal Details
		$meal_query = 'select CD.flight_booking_transaction_details_fk,
						concat(CD.first_name," ", CD.last_name) as pax_name,FML.*
						from flight_booking_passenger_details AS CD
						join flight_booking_meal_details FML on CD.origin=FML.passenger_fk
						WHERE CD.flight_booking_transaction_details_fk IN 
						(select TD.origin from flight_booking_transaction_details AS TD 
						WHERE TD.app_reference =' . $this->db->escape($app_reference) . ')';
		//Seat Details
		$seat_query = 'select CD.flight_booking_transaction_details_fk,
						concat(CD.first_name," ", CD.last_name) as pax_name,FST.*
						from flight_booking_passenger_details AS CD
						join flight_booking_seat_details FST on CD.origin=FST.passenger_fk
						WHERE CD.flight_booking_transaction_details_fk IN 
						(select TD.origin from flight_booking_transaction_details AS TD 
						WHERE TD.app_reference =' . $this->db->escape($app_reference) . ')';

		$response['data']['booking_details']			= $this->db->query($bd_query)->result_array();
		$response['data']['booking_itinerary_details']	= $this->db->query($id_query)->result_array();
		$response['data']['booking_transaction_details']	= $this->db->query($td_query)->result_array();
		$response['data']['booking_customer_details']	= $this->db->query($cd_query)->result_array();
		$response['data']['cancellation_details']	= $this->db->query($cancellation_details_query)->result_array();
		$response['data']['baggage_details']	= $this->db->query($baggage_query)->result_array();
		$response['data']['meal_details']	= $this->db->query($meal_query)->result_array();
		$response['data']['seat_details']	= $this->db->query($seat_query)->result_array();

		if (valid_array($response['data']['booking_details']) == true and valid_array($response['data']['booking_itinerary_details']) == true and valid_array($response['data']['booking_customer_details']) == true) {
			$response['status'] = SUCCESS_STATUS;
		}
		return $response;
	}
	/**
	 * Sagar Wakchaure
	 * B2C Flight Report
	 * @param unknown $condition
	 * @param unknown $count
	 * @param unknown $offset
	 * @param unknown $limit
	 * $condition[] = array('U.user_typ', '=', B2C_USER, ' OR ', 'BD.created_by_i', '=', 0);
	 */
	function b2c_flight_report(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array|string
	{
		$response=[];
		$condition = $this->custom_db->get_custom_condition($condition);
		//$b2c_condition_array = array('U.user_type', '=', B2C_USER, ' OR ', 'BD.created_by_id', '=', 0);

		//BT, CD, ID

		// if(isset($condition) == true)
		// {
		// 	$offset = 0;
		// }else{

		// 	$offset = $offset;
		// }


		if ($count) {

			//echo debug($condition);exit;
			$query = 'select count(distinct(BD.app_reference)) AS total_records from flight_booking_details BD
					left join user U on U.user_id = BD.created_by_id
					left join user_type UT on UT.origin = U.user_type
					join flight_booking_transaction_details as BT on BD.app_reference = BT.app_reference	
					where (U.user_type=' . B2C_USER . ' OR BD.created_by_id = 0) AND BD.domain_origin=' . get_domain_auth_id() . '' . $condition;
			//echo debug($query);exit;

			$data = $this->db->query($query)->row_array();

			return $data['total_records'];
		} else {
			$this->load->library('booking_data_formatter');
			$response['status'] = SUCCESS_STATUS;
			$response['data'] = array();
			$booking_itinerary_details	= array();
			$booking_customer_details	= array();
			$booking_transaction_details = array();
			$cancellation_details = array();
			$payment_details = array();
			//Booking Details
			$bd_query = 'select BD.* ,U.user_name,U.first_name,U.last_name from flight_booking_details AS BD
					     left join user U on U.user_id = BD.created_by_id
					     left join user_type UT on UT.origin = U.user_type
					     join flight_booking_transaction_details as BT on BD.app_reference = BT.app_reference		
						 WHERE  (U.user_type=' . B2C_USER . ' OR BD.created_by_id = 0) AND BD.domain_origin=' . get_domain_auth_id() . ' ' . $condition . '
						 order by BD.created_datetime desc, BD.origin desc limit ' . $offset . ', ' . $limit;



			$booking_details	= $this->db->query($bd_query)->result_array();
			//echo debug($bd_query); 			exit;
			$app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);
			if (empty($app_reference_ids) == false) {
				//Itinerary Details
				$id_query = 'select * from flight_booking_itinerary_details AS ID
							WHERE ID.app_reference IN (' . $app_reference_ids . ')';
				//Transaction Details
				$td_query = 'select * from flight_booking_transaction_details AS TD
							WHERE TD.app_reference IN (' . $app_reference_ids . ')';
				//Customer and Ticket Details
				$cd_query = 'select CD.*,FPTI.TicketId,FPTI.TicketNumber,FPTI.IssueDate,FPTI.Fare,FPTI.SegmentAdditionalInfo
							from flight_booking_passenger_details AS CD
							left join flight_passenger_ticket_info FPTI on CD.origin=FPTI.passenger_fk
							WHERE CD.flight_booking_transaction_details_fk IN
							(select TD.origin from flight_booking_transaction_details AS TD
							WHERE TD.app_reference IN (' . $app_reference_ids . '))';
				//Cancellation Details
				$cancellation_details_query = 'select FCD.*
						from flight_booking_passenger_details AS CD
						left join flight_cancellation_details AS FCD ON FCD.passenger_fk=CD.origin
						WHERE CD.flight_booking_transaction_details_fk IN
						(select TD.origin from flight_booking_transaction_details AS TD
						WHERE TD.app_reference IN (' . $app_reference_ids . '))';
				$payment_details_query = 'select * from  payment_gateway_details AS PD
							WHERE PD.app_reference IN (' . $app_reference_ids . ')';
				$booking_itinerary_details	= $this->db->query($id_query)->result_array();
				$booking_customer_details	= $this->db->query($cd_query)->result_array();
				$booking_transaction_details = $this->db->query($td_query)->result_array();
				$cancellation_details = $this->db->query($cancellation_details_query)->result_array();
				$payment_details = $this->db->query($payment_details_query)->result_array();
			}

			$response['data']['booking_details']			= $booking_details;
			$response['data']['booking_itinerary_details']	= $booking_itinerary_details;
			$response['data']['booking_transaction_details']	= $booking_transaction_details;
			$response['data']['booking_customer_details']	= $booking_customer_details;
			$response['data']['cancellation_details']	= $cancellation_details;
			$response['data']['payment_details']	= $payment_details;
			return $response;
		}
	}


	/**
	 * Sagar Wakchaure
	 * B2C Flight Report
	 * @param unknown $condition
	 * @param unknown $count
	 * @param unknown $offset
	 * @param unknown $limit
	 * $condition[] = array('U.user_typ', '=', B2C_USER, ' OR ', 'BD.created_by_i', '=', 0);
	 */
	public function b2b_flight_report(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array|string
	{
		$response=[];
		$condition = $this->custom_db->get_custom_condition($condition);
		//$b2c_condition_array = array('U.user_type', '=', B2C_USER, ' OR ', 'BD.created_by_id', '=', 0);

		//BT, CD, ID

		// if(isset($condition) == true)
		// {
		// 	$offset = 0;
		// }else{
		// 	$offset = $offset;
		// }

		if ($count) {

			//echo debug($condition);exit;
			$query = 'select count(distinct(BD.app_reference)) AS total_records from flight_booking_details BD
					  join user U on U.user_id = BD.created_by_id
					  join flight_booking_transaction_details as BT on BD.app_reference = BT.app_reference						
					  where U.user_type=' . B2B_USER . ' AND BD.domain_origin=' . get_domain_auth_id() . '' . $condition;



			$data = $this->db->query($query)->row_array();
			//echo debug($data);exit;
			return $data['total_records'];
		} else {
			$this->load->library('booking_data_formatter');
			$response['status'] = SUCCESS_STATUS;
			$response['data'] = array();
			$booking_itinerary_details	= array();
			$booking_customer_details	= array();
			$booking_transaction_details = array();
			$cancellation_details = array();
			// $payment_details = array();
			//Booking Details
			$bd_query = 'select BD.*,U.agency_name,U.first_name,U.last_name from flight_booking_details AS BD
					      join user U on U.user_id = BD.created_by_id join flight_booking_transaction_details as BT on BD.app_reference = BT.app_reference					      
						  WHERE  U.user_type=' . B2B_USER . ' AND BD.domain_origin=' . get_domain_auth_id() . ' ' . $condition . '
						  order by BD.created_datetime desc, BD.origin desc limit ' . $offset . ', ' . $limit;

			//echo debug($bd_query);			
			//exit;

			$booking_details	= $this->db->query($bd_query)->result_array();
			//echo debug($booking_details);exit;
			$app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);
			if (empty($app_reference_ids) == false) {
				//Itinerary Details
				$id_query = 'select * from flight_booking_itinerary_details AS ID
							WHERE ID.app_reference IN (' . $app_reference_ids . ')';
				//Transaction Details
				$td_query = 'select * from flight_booking_transaction_details AS TD
							WHERE TD.app_reference IN (' . $app_reference_ids . ')';

				//Customer and Ticket Details
				$cd_query = 'select CD.*,FPTI.TicketId,FPTI.TicketNumber,FPTI.IssueDate,FPTI.Fare,FPTI.SegmentAdditionalInfo
							from flight_booking_passenger_details AS CD
							left join flight_passenger_ticket_info FPTI on CD.origin=FPTI.passenger_fk
							WHERE CD.flight_booking_transaction_details_fk IN
							(select TD.origin from flight_booking_transaction_details AS TD
							WHERE TD.app_reference IN (' . $app_reference_ids . '))';
				//Cancellation Details
				$cancellation_details_query = 'select FCD.*
						from flight_booking_passenger_details AS CD
						left join flight_cancellation_details AS FCD ON FCD.passenger_fk=CD.origin
						WHERE CD.flight_booking_transaction_details_fk IN
						(select TD.origin from flight_booking_transaction_details AS TD
						WHERE TD.app_reference IN (' . $app_reference_ids . '))';
				//$payment_details_query = '';
				$booking_itinerary_details	= $this->db->query($id_query)->result_array();
				$booking_customer_details	= $this->db->query($cd_query)->result_array();
				$booking_transaction_details = $this->db->query($td_query)->result_array();
				$cancellation_details = $this->db->query($cancellation_details_query)->result_array();
				//$payment_details = $this->db->query($payment_details_query)->result_array();
			}

			$response['data']['booking_details']			= $booking_details;
			$response['data']['booking_itinerary_details']	= $booking_itinerary_details;
			$response['data']['booking_transaction_details']	= $booking_transaction_details;
			$response['data']['booking_customer_details']	= $booking_customer_details;
			$response['data']['cancellation_details']	= $cancellation_details;
			//$response['data']['payment_details']	= $payment_details;
			return $response;
		}
	}

	public function ultra_flight_report(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array|string
	{
		$response=[];
		$condition = $this->custom_db->get_custom_condition($condition);
		//$b2c_condition_array = array('U.user_type', '=', B2C_USER, ' OR ', 'BD.created_by_id', '=', 0);

		//BT, CD, ID

		// if(isset($condition) == true)
		// {
		// 	$offset = 0;
		// }else{
		// 	$offset = $offset;
		// }

		if ($count) {

			//echo debug($condition);exit;
			$query = 'select count(distinct(BD.app_reference)) AS total_records from flight_booking_details BD
					  join user U on U.user_id = BD.created_by_id
					  join flight_booking_transaction_details as BT on BD.app_reference = BT.app_reference						
					  where U.user_type=' . ULTRALUX_USER . ' AND BD.domain_origin=' . get_domain_auth_id() . '' . $condition;



			$data = $this->db->query($query)->row_array();
			//echo debug($data);exit;
			return $data['total_records'];
		} else {
			$this->load->library('booking_data_formatter');
			$response['status'] = SUCCESS_STATUS;
			$response['data'] = array();
			$booking_itinerary_details	= array();
			$booking_customer_details	= array();
			$booking_transaction_details = array();
			$cancellation_details = array();
			// $payment_details = array();
			//Booking Details
			$bd_query = 'select BD.*,U.agency_name,U.first_name,U.last_name from flight_booking_details AS BD
					      join user U on U.user_id = BD.created_by_id join flight_booking_transaction_details as BT on BD.app_reference = BT.app_reference					      
						  WHERE  U.user_type=' . ULTRALUX_USER . ' AND BD.domain_origin=' . get_domain_auth_id() . ' ' . $condition . '
						  order by BD.created_datetime desc, BD.origin desc limit ' . $offset . ', ' . $limit;

			//echo debug($bd_query);			
			//exit;

			$booking_details	= $this->db->query($bd_query)->result_array();
			//echo debug($booking_details);exit;
			$app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);
			if (empty($app_reference_ids) == false) {
				//Itinerary Details
				$id_query = 'select * from flight_booking_itinerary_details AS ID
							WHERE ID.app_reference IN (' . $app_reference_ids . ')';
				//Transaction Details
				$td_query = 'select * from flight_booking_transaction_details AS TD
							WHERE TD.app_reference IN (' . $app_reference_ids . ')';

				//Customer and Ticket Details
				$cd_query = 'select CD.*,FPTI.TicketId,FPTI.TicketNumber,FPTI.IssueDate,FPTI.Fare,FPTI.SegmentAdditionalInfo
							from flight_booking_passenger_details AS CD
							left join flight_passenger_ticket_info FPTI on CD.origin=FPTI.passenger_fk
							WHERE CD.flight_booking_transaction_details_fk IN
							(select TD.origin from flight_booking_transaction_details AS TD
							WHERE TD.app_reference IN (' . $app_reference_ids . '))';
				//Cancellation Details
				$cancellation_details_query = 'select FCD.*
						from flight_booking_passenger_details AS CD
						left join flight_cancellation_details AS FCD ON FCD.passenger_fk=CD.origin
						WHERE CD.flight_booking_transaction_details_fk IN
						(select TD.origin from flight_booking_transaction_details AS TD
						WHERE TD.app_reference IN (' . $app_reference_ids . '))';
				//$payment_details_query = '';
				$booking_itinerary_details	= $this->db->query($id_query)->result_array();
				$booking_customer_details	= $this->db->query($cd_query)->result_array();
				$booking_transaction_details = $this->db->query($td_query)->result_array();
				$cancellation_details = $this->db->query($cancellation_details_query)->result_array();
				//$payment_details = $this->db->query($payment_details_query)->result_array();
			}

			$response['data']['booking_details']			= $booking_details;
			$response['data']['booking_itinerary_details']	= $booking_itinerary_details;
			$response['data']['booking_transaction_details']	= $booking_transaction_details;
			$response['data']['booking_customer_details']	= $booking_customer_details;
			$response['data']['cancellation_details']	= $cancellation_details;
			//$response['data']['payment_details']	= $payment_details;
			return $response;
		}
	}

	/**
	 * return all booking events
	 */
	public function booking_events(): array
	{
		//BT, CD, ID
		$query = 'select * from flight_booking_details where domain_origin=' . get_domain_auth_id();
		return $this->db->query($query)->result_array();
	}
	public function get_monthly_booking_summary(): array
	{
		$query = 'select count(distinct(BD.app_reference)) AS total_booking, sum(TD.total_fare+TD.admin_markup+BD.convinence_amount) as monthly_payment, sum(TD.admin_markup+BD.convinence_amount) as monthly_earning, 
		MONTH(BD.created_datetime) as month_number 
		from flight_booking_details AS BD
		join flight_booking_transaction_details as TD on BD.app_reference=TD.app_reference
		where (YEAR(BD.created_datetime) BETWEEN ' . date('Y') . ' AND ' . date('Y', strtotime('+1 year')) . ') AND BD.domain_origin=' . get_domain_auth_id() . '
		GROUP BY YEAR(BD.created_datetime), MONTH(BD.created_datetime)';
		return $this->db->query($query)->result_array();
	}

	public function monthly_search_history(int $year_start, int $year_end): array
	{
		$query = 'select count(*) AS total_search, MONTH(created_datetime) as month_number from search_flight_history where
		(YEAR(created_datetime) BETWEEN ' . $year_start . ' AND ' . $year_end . ') AND domain_origin=' . get_domain_auth_id() . ' 
		AND search_type="' . META_AIRLINE_COURSE . '"
		GROUP BY YEAR(created_datetime), MONTH(created_datetime)';
		return $this->db->query($query)->result_array();
	}

	public function top_search(int $year_start, int $year_end): array
	{
		$query = 'select count(*) AS total_search, concat(from_code, "-",to_code) label from search_flight_history where
		(YEAR(created_datetime) BETWEEN ' . $year_start . ' AND ' . $year_end . ') AND domain_origin=' . get_domain_auth_id() . ' 
		AND search_type="' . META_AIRLINE_COURSE . '"
		GROUP BY CONCAT(from_code, to_code) order by count(*) desc, created_datetime desc limit 0, 15';
		return $this->db->query($query)->result_array();
	}
	/*
	 * Balu A
	 * Update the Cancellation Details of the Passenger
	 */
	public function update_pax_ticket_cancellation_details(array $ticket_cancellation_details, int $pax_origin): void
	{
		//1.Updating Passenger Status
		$booking_status = 'BOOKING_CANCELLED';
		$passenger_update_data = array();
		$passenger_update_data['status'] = $booking_status;
		$passenger_update_condition = array();
		$passenger_update_condition['origin'] = $pax_origin;
		$this->custom_db->update_record('flight_booking_passenger_details', $passenger_update_data, $passenger_update_condition);
		//2.Adding Cancellation Details
		$data = array();
		$cancellation_details = $ticket_cancellation_details['cancellation_details'];
		$data['RequestId'] = $cancellation_details['ChangeRequestId'];
		$data['ChangeRequestStatus'] = $cancellation_details['ChangeRequestStatus'];
		$data['statusDescription'] = $cancellation_details['StatusDescription'];
		$pax_details_exists = $this->custom_db->single_table_records('flight_cancellation_details', '*', array('passenger_fk' => $pax_origin));
		if ($pax_details_exists['status'] == true) {
			//Update the Data
			$this->custom_db->update_record('flight_cancellation_details', $data, array('passenger_fk' => $pax_origin));
		} else {
			//Insert Data
			$data['passenger_fk'] = $pax_origin;
			$data['created_by_id'] = intval($this->entity_user_id);
			$data['created_datetime'] = date('Y-m-d H:i:s');
			$data['cancellation_requested_on'] = date('Y-m-d H:i:s');
			$this->custom_db->insert_record('flight_cancellation_details', $data);
		}
	}
	/**
	 * Update Flight Booking Transaction Status based on Passenger Ticket status
	 * @param unknown_type $transaction_origin
	 */
	public function update_flight_booking_transaction_cancel_status(int $transaction_origin): void
	{
		$confirmed_passenger_exists = $this->custom_db->single_table_records('flight_booking_passenger_details', '*', array('flight_booking_transaction_details_fk' => $transaction_origin, 'status' => 'BOOKING_CONFIRMED'));
		if ($confirmed_passenger_exists['status'] == false) {
			//If all passenger cancelled the ticket for that particular transaction, then set the transaction status to  BOOKING_CANCELLED
			$transaction_update_data = array();
			$booking_status = 'BOOKING_CANCELLED';
			$transaction_update_data['status'] = $booking_status;
			$transaction_update_condition = array();
			$transaction_update_condition['origin'] = $transaction_origin;
			$this->custom_db->update_record('flight_booking_transaction_details', $transaction_update_data, $transaction_update_condition);
		}
	}
	/**
	 * Update Flight Booking Transaction Status based on Passenger Ticket status
	 * @param unknown_type $transaction_origin
	 */
	public function update_flight_booking_cancel_status(string $app_reference): void
	{
		$confirmed_passenger_exists = $this->custom_db->single_table_records('flight_booking_passenger_details', '*', array('app_reference' => $app_reference, 'status' => 'BOOKING_CONFIRMED'));
		if ($confirmed_passenger_exists['status'] == false) {
			//If all passenger cancelled the ticket, then set the booking status to  BOOKING_CANCELLED
			$booking_update_data = array();
			$booking_status = 'BOOKING_CANCELLED';
			$booking_update_data['status'] = $booking_status;
			$booking_update_condition = array();
			$booking_update_condition['app_reference'] = $app_reference;
			$this->custom_db->update_record('flight_booking_details', $booking_update_data, $booking_update_condition);
		}
	}

	/**
	 * Check if destination are domestic
	 * @param string $from_loc Unique location code
	 * @param string $to_loc   Unique location code
	 */
	public function is_domestic_flight(string $from_loc, string $to_loc): bool
	{
		if (valid_array($from_loc) == true || valid_array($to_loc)) { //Multicity
			$airport_cities = array_merge($from_loc, $to_loc);
			$airport_cities = array_unique($airport_cities);
			$airport_city_codes = '';
			foreach ($airport_cities as  $v) {
				$airport_city_codes .= '"' . $v . '",';
			}
			$airport_city_codes = rtrim($airport_city_codes, ',');
			$query = 'SELECT count(*) total FROM flight_airport_list WHERE airport_code IN (' . $airport_city_codes . ') AND country != "India"';
		} else { //Oneway/RoundWay
			$query = 'SELECT count(*) total FROM flight_airport_list WHERE airport_code IN (' . $this->db->escape($from_loc) . ',' . $this->db->escape($to_loc) . ') AND country != "India"';
		}
		$data = $this->db->query($query)->row_array();
		if (intval($data['total']) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Sagar Wakchaure
	 * update the pnr details
	 * @param unknown $response
	 * @param unknown $app_reference
	 * @param unknown $booking_source
	 * @param unknown $booking_status
	 * @return string
	 */
	public function update_pnr_details(array $response, string $app_reference, string $booking_source = '', string $booking_status = ''): string
	{

		$return_response = FAILURE_STATUS;
		$booking_details = $this->get_booking_details($app_reference, $booking_source, $booking_status);
		$table_data = $this->booking_data_formatter->format_flight_booking_data($booking_details, 'admin');
		$booking_transaction_details = $table_data['data']['booking_details'][0]['booking_transaction_details'];
		$update_pnr_array = array();
		$update_itinerary_details = array();
		$update_ticket_info = array();

		//update flight_booking_transaction_details table and flight_passenger_ticket_info

		if ($booking_details['status'] == SUCCESS_STATUS && $response['status'] == SUCCESS_STATUS) {
			$i = 0;
			foreach ($booking_transaction_details as $key => $transaction_detail_sub_data) {
				$update_pnr_array['pnr'] = $response['data']['BoookingTransaction'][$i]['PNR'];
				$update_pnr_array['book_id'] = $response['data']['BoookingTransaction'][$i]['BookingID'];
				$update_pnr_array['status'] = $response['data']['BoookingTransaction'][$i]['Status'];
				$sequence_no = $response['data']['BoookingTransaction'][$i]['SequenceNumber'];

				//update flight_booking_transaction_details
				$this->custom_db->update_record('flight_booking_transaction_details', $update_pnr_array, array('app_reference' => $app_reference, 'sequence_number' => trim($sequence_no)));

				foreach ($transaction_detail_sub_data['booking_customer_details'] as $k => $booking_customer_data) {
					$update_ticket_info['TicketId'] = $response['data']['BoookingTransaction'][$i]['BookingCustomer'][$k]['TicketId'];
					$update_ticket_info['TicketNumber'] = $response['data']['BoookingTransaction'][$i]['BookingCustomer'][$k]['TicketNumber'];

					//update flight_passenger_ticket_info
					$this->custom_db->update_record('flight_passenger_ticket_info', $update_ticket_info, array('passenger_fk' => $booking_customer_data['origin']));
				}
				$i++;

				//update  status in flight_booking_passenger_details
				$this->custom_db->update_record('flight_booking_passenger_details', array('status' => $update_pnr_array['status']), array('app_reference' => trim($app_reference)));
			}

			//update status in flight_booking_details
			if (isset($response['data']['MasterBookingStatus']) && !empty($response['data']['MasterBookingStatus'])) {

				$this->custom_db->update_record('flight_booking_details', array('status' => $response['data']['MasterBookingStatus']), array('app_reference' => $app_reference));
			}

			//update flight_booking_itinerary_details table		
			foreach ($booking_details['data']['booking_itinerary_details'] as $key => $transaction_detail_sub_data) {
				$update_itinerary_details['airline_pnr'] = $response['data']['BookingItineraryDetails'][$key]['AirlinePNR'];
				$from = $response['data']['BookingItineraryDetails'][$key]['FromAirlineCode'];
				$to = $response['data']['BookingItineraryDetails'][$key]['ToAirlineCode'];
				$departure_datetime = $response['data']['BookingItineraryDetails'][$key]['DepartureDatetime'];

				$this->custom_db->update_record(
					'flight_booking_itinerary_details',
					$update_itinerary_details,
					array('app_reference' => $app_reference, 'from_airport_code' => trim($from), 'to_airport_code' => trim($to), 'departure_datetime' => trim($departure_datetime))
				);
			}

			$return_response = SUCCESS_STATUS;
		}
		return $return_response;
	}
	/**
	 
	 * Returns Passenger Ticket Details based on the following parameteres
	 * @param $app_reference
	 * @param $passenger_origin
	 * @param $passenger_booking_status
	 */
	function get_passenger_ticket_info(string $app_reference, int $passenger_origin, string $passenger_booking_status = ''): array
	{
		$response=[];
		$response['status'] = FAILURE_STATUS;
		$response['data'] = array();
		$bd_query = 'select BD.*,DL.domain_name,DL.origin as domain_id,CC.country as domain_base_currency from flight_booking_details AS BD,domain_list AS DL
						join currency_converter CC on CC.id=DL.currency_converter_fk 
						WHERE DL.origin = BD.domain_origin AND BD.app_reference like ' . $this->db->escape($app_reference);
		//Customer and Ticket Details
		$cd_query = 'select FBTD.book_id,FBTD.pnr,FBTD.sequence_number,CD.*,FPTI.TicketId,FPTI.TicketNumber,FPTI.IssueDate,FPTI.Fare,FPTI.SegmentAdditionalInfo
						from flight_booking_passenger_details AS CD
						join flight_booking_transaction_details FBTD on CD.flight_booking_transaction_details_fk=FBTD.origin
						left join flight_passenger_ticket_info FPTI on CD.origin=FPTI.passenger_fk
						WHERE CD.app_reference="' . $app_reference . '" and CD.origin=' . intval($passenger_origin) . ' and CD.status="' . $passenger_booking_status . '"';
		//Cancellation Details
		$cancellation_details_query = 'select FCD.*
						from flight_booking_passenger_details AS CD
						left join flight_passenger_ticket_info FPTI on CD.origin=FPTI.passenger_fk
						left join flight_cancellation_details AS FCD ON FCD.passenger_fk=CD.origin
						WHERE CD.app_reference="' . $app_reference . '" and CD.origin=' . intval($passenger_origin) . ' and CD.status="' . $passenger_booking_status . '"';
		$response['data']['booking_details']			= $this->db->query($bd_query)->result_array();
		$response['data']['booking_customer_details']	= $this->db->query($cd_query)->result_array();
		$response['data']['cancellation_details']	= $this->db->query($cancellation_details_query)->result_array();
		if (valid_array($response['data']['booking_details']) == true && valid_array($response['data']['booking_customer_details']) == true) {
			$response['status'] = SUCCESS_STATUS;
		}
		return $response;
	}
	/**
	 * Balu A
	 * Update Supplier Ticket Refund Details
	 * @param unknown_type $supplier_ticket_refund_details
	 */
	public function update_supplier_ticket_refund_details(int $passenger_origin, array $supplier_ticket_refund_details): void
	{
		$update_refund_details = array();
		$supplier_ticket_refund_details = $supplier_ticket_refund_details['RefundDetails'];
		$update_refund_details['ChangeRequestStatus'] = 			$supplier_ticket_refund_details['ChangeRequestStatus'];
		$update_refund_details['statusDescription'] = 				$supplier_ticket_refund_details['StatusDescription'];
		$update_refund_details['API_refund_status'] = 				$supplier_ticket_refund_details['RefundStatus'];
		$update_refund_details['API_RefundedAmount'] = 				floatval($supplier_ticket_refund_details['RefundedAmount']);
		$update_refund_details['API_CancellationCharge'] = 			floatval($supplier_ticket_refund_details['CancellationCharge']);
		$update_refund_details['API_ServiceTaxOnRefundAmount'] =	floatval($supplier_ticket_refund_details['ServiceTaxOnRefundAmount']);
		$update_refund_details['API_SwachhBharatCess'] = 			floatval($supplier_ticket_refund_details['SwachhBharatCess']);

		if ($supplier_ticket_refund_details['RefundStatus'] == 'PROCESSED') {
			$update_refund_details['cancellation_processed_on'] = date('Y-m-d H:i:s');
		}
		$this->custom_db->update_record('flight_cancellation_details', $update_refund_details, array('passenger_fk' => intval($passenger_origin)));
	}
	function get_booked_user_details(string $app_reference): array
	{
		$query = "select  BD.created_by_id,U.user_type from flight_booking_details as BD join user as U on U.user_id = BD.created_by_id where app_reference = '" . $app_reference . "'";
		return $this->db->query($query)->result_array();
	}
	/**
	 * Extraservices(Baggage,Meal and Seats) Price
	 * @param unknown_type $app_reference
	 */
	public function get_extra_services_total_price(string $app_reference): float
	{
		$extra_service_total_price = 0;

		//get baggage price
		$baggage_total_price = $this->get_baggage_total_price($app_reference);

		//get meal price
		$meal_total_price = $this->get_meal_total_price($app_reference);

		//get seat price
		$seat_total_price = $this->get_seat_total_price($app_reference);

		//Addig all services price
		$extra_service_total_price = round(($baggage_total_price + $meal_total_price + $seat_total_price), 2);

		return $extra_service_total_price;
	}
	/**
	 * 
	 * Returns Baggage Total Price
	 * @param unknown_type $app_reference
	 */
	public function get_baggage_total_price(string $app_reference): float
	{
		$query = 'select sum(FBG.price) as baggage_total_price
			from flight_booking_passenger_details FP
			left join flight_booking_baggage_details FBG on FP.origin=FBG.passenger_fk
			where FP.app_reference="' . $app_reference . '" group by FP.app_reference';
		$data = $this->db->query($query)->row_array();
		return floatval($data['baggage_total_price']);
	}
	/**
	 * 
	 * Returns Meal Total Price
	 * @param unknown_type $app_reference
	 */
	public function get_meal_total_price(string $app_reference): float
	{
		$query = 'select sum(FML.price) as meal_total_price
			from flight_booking_passenger_details FP
			left join flight_booking_meal_details FML on FP.origin=FML.passenger_fk
			where FP.app_reference="' . $app_reference . '" group by FP.app_reference';
		$data = $this->db->query($query)->row_array();

		return floatval($data['meal_total_price']);
	}
	/**
	 * 
	 * Returns Seat Total Price
	 * @param unknown_type $app_reference
	 */
	public function get_seat_total_price(string $app_reference): float
	{
		$query = 'select sum(FST.price) as seat_total_price
			from flight_booking_passenger_details FP
			left join flight_booking_seat_details FST on FP.origin=FST.passenger_fk
			where FP.app_reference="' . $app_reference . '" group by FP.app_reference';
		$data = $this->db->query($query)->row_array();

		return floatval($data['seat_total_price']);
	}
	/**
	 * Extraservices(Baggage,Meal and Seats) Price
	 * @param unknown_type $app_reference
	 */
	public function add_extra_service_price_to_published_fare(string $app_reference): void
	{
		$transaction_data = $this->db->query('select * from flight_booking_transaction_details where app_reference="' . $app_reference . '" order by origin asc')->result_array();
		if (valid_array($transaction_data) == true) {
			foreach ($transaction_data as $tr_v) {
				$transaction_origin = $tr_v['origin'];
				$extra_service_totla_price = $this->transaction_wise_extra_service_total_price($transaction_origin);

				$update_data = array();
				$update_condition = array();
				$update_data['total_fare'] = $tr_v['total_fare'] + $extra_service_totla_price;
				$update_condition['origin'] = $transaction_origin;
				$this->custom_db->update_record('flight_booking_transaction_details', $update_data, $update_condition);
			}
		}
	}
	/**
	 * Transaction-wise extra service total price
	 * @param unknown_type $transaction_origin
	 */
	public function transaction_wise_extra_service_total_price(int $transaction_origin): float
	{
		$extra_service_totla_price = 0;
		//Baggage
		$baggage_price = $this->db->query('select sum(FBG.price) as baggage_total_price
											from flight_booking_passenger_details FP
											left join flight_booking_baggage_details FBG on FP.origin=FBG.passenger_fk
											where FP.flight_booking_transaction_details_fk=' . $transaction_origin . ' group by FP.flight_booking_transaction_details_fk')->row_array();

		//Meal
		$meal_price = $this->db->query('select sum(FML.price) as meal_total_price
											from flight_booking_passenger_details FP
											left join flight_booking_meal_details FML on FP.origin=FML.passenger_fk
											where FP.flight_booking_transaction_details_fk=' . $transaction_origin . ' group by FP.flight_booking_transaction_details_fk')->row_array();
		//Seat
		$seat_price = $this->db->query('select sum(FST.price) as seat_total_price
											from flight_booking_passenger_details FP
											left join flight_booking_seat_details FST on FP.origin=FST.passenger_fk
											where FP.flight_booking_transaction_details_fk=' . $transaction_origin . ' group by FP.flight_booking_transaction_details_fk')->row_array();

		$extra_service_totla_price = floatval($baggage_price['baggage_total_price'] + $meal_price['meal_total_price'] + $seat_price['seat_total_price']);

		return $extra_service_totla_price;
	}
	public function booking_cancel(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): int
	{
		$response=[];
		$condition = $this->custom_db->get_custom_condition($condition);
		//$b2c_condition_array = array('U.user_type', '=', B2C_USER, ' OR ', 'BD.created_by_id', '=', 0);

		//BT, CD, ID

		// if(isset($condition) == true)
		// {
		// 	$offset = 0;
		// }else{

		// 	$offset = $offset;
		// }


		if ($count) {

			//echo debug($condition);exit;
			$query = 'select count(distinct(BD.app_reference)) AS total_records from flight_booking_details BD
					left join user U on U.user_id = BD.created_by_id
					left join user_type UT on UT.origin = U.user_type
					join flight_booking_transaction_details as BT on BD.app_reference = BT.app_reference	
					where (U.user_type=' . B2C_USER . ' OR BD.created_by_id = 0) AND BD.domain_origin=' . get_domain_auth_id() . '' . $condition;
			//echo debug($query);exit;

			$data = $this->db->query($query)->row_array();

			return $data['total_records'];
		} else {
			$this->load->library('booking_data_formatter');
			$response['status'] = SUCCESS_STATUS;
			$response['data'] = array();
			$booking_itinerary_details	= array();
			$booking_customer_details	= array();
			$booking_transaction_details = array();
			$cancellation_details = array();
			// $payment_details = array();
			//Booking Details
			$bd_query = 'select BD.* ,U.user_name,U.first_name,U.last_name from flight_booking_details AS BD
					     left join user U on U.user_id = BD.created_by_id
					     left join user_type UT on UT.origin = U.user_type
					     join flight_booking_transaction_details as BT on BD.app_reference = BT.app_reference		
						 WHERE  (U.user_type=' . B2B_USER . ' OR U.user_type=' . B2C_USER . ' OR BD.created_by_id = 0) AND BD.domain_origin=' . get_domain_auth_id() . ' ' . $condition . '
						 order by BD.created_datetime desc, BD.origin desc limit ' . $offset . ', ' . $limit;



			$booking_details	= $this->db->query($bd_query)->result_array();

			$app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);
			if (empty($app_reference_ids) == false) {
				//Itinerary Details
				$id_query = 'select * from flight_booking_itinerary_details AS ID
							WHERE ID.app_reference IN (' . $app_reference_ids . ')';
				//Transaction Details
				$td_query = 'select * from flight_booking_transaction_details AS TD
							WHERE TD.app_reference IN (' . $app_reference_ids . ')';
				//Customer and Ticket Details
				$cd_query = 'select CD.*,FPTI.TicketId,FPTI.TicketNumber,FPTI.IssueDate,FPTI.Fare,FPTI.SegmentAdditionalInfo
							from flight_booking_passenger_details AS CD
							left join flight_passenger_ticket_info FPTI on CD.origin=FPTI.passenger_fk
							WHERE CD.flight_booking_transaction_details_fk IN
							(select TD.origin from flight_booking_transaction_details AS TD
							WHERE TD.app_reference IN (' . $app_reference_ids . '))';
				//Cancellation Details
				$cancellation_details_query = 'select FCD.*
						from flight_booking_passenger_details AS CD
						left join flight_cancellation_details AS FCD ON FCD.passenger_fk=CD.origin
						WHERE CD.flight_booking_transaction_details_fk IN
						(select TD.origin from flight_booking_transaction_details AS TD
						WHERE TD.app_reference IN (' . $app_reference_ids . '))';
				// echo $cancellation_details_query;exit;
				//$payment_details_query = '';
				$booking_itinerary_details	= $this->db->query($id_query)->result_array();
				$booking_customer_details	= $this->db->query($cd_query)->result_array();
				$booking_transaction_details = $this->db->query($td_query)->result_array();
				$cancellation_details = $this->db->query($cancellation_details_query)->result_array();
				//$payment_details = $this->db->query($payment_details_query)->result_array();
			}

			$response['data']['booking_details']			= $booking_details;
			$response['data']['booking_itinerary_details']	= $booking_itinerary_details;
			$response['data']['booking_transaction_details']	= $booking_transaction_details;
			$response['data']['booking_customer_details']	= $booking_customer_details;
			$response['data']['cancellation_details']	= $cancellation_details;
			//$response['data']['payment_details']	= $payment_details;
			return $response;
		}
	}
	// added for flight_cancellation_details
	public function add_flight_cancellation_details(int $pax_origin): void
	{
		//1.Adding Cancellation Details
		$data = array();
		$data['RequestId'] = 1;
		$data['ChangeRequestStatus'] = 1;
		$data['statusDescription'] = 'Unassigned';
		//Insert Data
		$data['passenger_fk'] = $pax_origin;
		$data['created_by_id'] = intval($this->entity_user_id);
		$data['created_datetime'] = date('Y-m-d H:i:s');
		$data['cancellation_requested_on'] = date('Y-m-d H:i:s');
		$this->custom_db->insert_record('flight_cancellation_details', $data);
	}
	public function exception_log_details(array $details): ?string
	{
		$response = $this->custom_db->single_table_records('provab_xml_logger', '*', array('app_reference' => ($details['app_reference'])));
		return $response['data'][0]['response'];
	}
	/*
	 *
	 * Get Airport List
	 *
	 */

	public function get_airport_list(string $search_chars): CI_DB_result
	{
		$raw_search_chars = $this->db->escape($search_chars);
		if (empty($search_chars) == false) {
			$r_search_chars = $this->db->escape($search_chars . '%');
			$search_chars = $this->db->escape('%' . $search_chars . '%');
		} else {
			$r_search_chars = $this->db->escape($search_chars);
			$search_chars = $this->db->escape($search_chars);
		}

		$query = 'Select * from flight_airport_list where airport_city like ' . $search_chars . '
		OR airport_code like ' . $search_chars . ' OR country like ' . $search_chars . '
		ORDER BY top_destination DESC,
		CASE
			WHEN	airport_code	LIKE	' . $raw_search_chars . '	THEN 1
			WHEN	airport_city	LIKE	' . $raw_search_chars . '	THEN 2
			WHEN	country			LIKE	' . $raw_search_chars . '	THEN 3

			WHEN	airport_code	LIKE	' . $r_search_chars . '	THEN 4
			WHEN	airport_city	LIKE	' . $r_search_chars . '	THEN 5
			WHEN	country			LIKE	' . $r_search_chars . '	THEN 6

			WHEN	airport_code	LIKE	' . $search_chars . '	THEN 7
			WHEN	airport_city	LIKE	' . $search_chars . '	THEN 8
			WHEN	country			LIKE	' . $search_chars . '	THEN 9
			ELSE 10 END
		LIMIT 0, 20';
		// echo $query;
		// exit;
		return $this->db->query($query);
	}
	public function all_flight_list(array $data): array
	{

		$cond = '';
		$flag = 1;


		if (!empty($data['arr_date']) && isset($data['arr_date'])) {
			$cond .= ' AND dep_from_date like "' . $data['arr_date'] . '"';
			$cond .= ' OR dep_to_date like "' . $data['arr_date'] . '"';
			$flag = 0;
		}
		if (!empty($data['flight_no']) && isset($data['flight_no'])) {
			$cond .= ' AND flight_num like ' . $this->db->escape('%' . $data['flight_no'] . '%');
			$flag = 0;
		}

		if (!empty($data['dep_origin']) && isset($data['dep_origin'])) {
			$cond .= ' AND origin like ' . $this->db->escape('%' . $data['dep_origin'] . '%');
			$flag = 0;
		}
		if (!empty($data['arrival_origin']) && isset($data['arrival_origin'])) {
			$cond .= ' AND destination like ' . $this->db->escape('%' . $data['arrival_origin'] . '%');
			$flag = 0;
		}

		/*if(!empty($data['month']) && isset($data['month'])){
			$month_name1 = date("F", mktime(0, 0, 0, $data['month'], 10)); 
			$cond .= ' AND ( "'.$month_name1.'" between MONTHNAME(dep_from_date) and MONTHNAME(dep_to_date))'; 

			$cond .= ' AND ( MONTH(dep_from_date) like '.$data['month']; 
			$cond .= ' OR MONTH(dep_to_date) like '.$data['month'].' ) '; 
			$flag = 0;
		}*/
		if (!empty($data['month']) && isset($data['month'])) {
			$y =  date("Y", strtotime("-5 year"));

			if (!empty($data['year']) && isset($data['year'])) {

				$m = $data['year'] . $data['month'];
				$cond .=  'AND ("' . $m . '" between DATE_FORMAT(dep_from_date,"%Y%m") and DATE_FORMAT(dep_to_date,"%Y%m"))  ';
			} else {

				$ny = date('Y', strtotime(date("Y", mktime()) . " + 1825 day"));
				$cond .=  'AND ';
				while ($y <= $ny) {
					$m = $y . $data['month'];
					$cond .=  '("' . $m . '" between DATE_FORMAT(dep_from_date,"%Y%m") and DATE_FORMAT(dep_to_date,"%Y%m")) OR ';
					$y++;
				}
				$cond =  substr($cond, 0, -3);
			}
			$flag = 0;
		}

		if (!empty($data['year']) && isset($data['year'])) {
			$cond .= ' AND ( YEAR(dep_from_date) like ' . $data['year'];
			$cond .= ' OR YEAR(dep_to_date) like ' . $data['year'] . ' )';
			$flag = 0;
		}

		if ($flag) {

			$m = date('Ym');

			$cond .=  'AND ("' . $m . '" between DATE_FORMAT(dep_from_date,"%Y%m") and DATE_FORMAT(dep_to_date,"%Y%m"))';


			//$d = date('m');
			//$month_name = date("F", mktime(0, 0, 0, $d, 10)); 
			//$cond .= ' AND ( "'.$month_name.'" between MONTHNAME(dep_from_date) and MONTHNAME(dep_to_date))'; 


			//$cond .= ' OR MONTH(dep_from_date) like '.date('m'); 
			//$cond .= ' OR MONTH(dep_to_date) like '.date('m'); 


			//$cond .= ' AND YEAR(dep_from_date) like '.date('Y');
			//$cond .= ' AND YEAR(dep_to_date) like '.date('Y');
		}

		$query = "SELECT * FROM flight_crs_segment_details where (active='1' or active='0') " . $cond . " AND fare_type=0 ORDER BY flight_num,carrier_code,class_type desc";
		//debug($query); exit;
		return $this->db->query($query)->result_array();
	}
	public function get_booking_count(): array
	{
		$query = 'SELECT DISTINCT(fsid) FROM flight_crs_booking_details';
		return $this->db->query($query)->result_array();
	}
	public function update_flight_details(int $id): array
	{
		$query = "SELECT * FROM `flight_crs_segment_details` where fsid = $id";
		//echo $query;
		$result = $this->db->query($query)->result_array();
		return $result;
	}
	public function flight_blocked_seat_details(int $flight_id): array
	{
		$response=[];
		$response['status'] = FAILURE_STATUS;
		//$flight_seats_cond=array('flight_id'=>$flight_id,'seat_flag'=>'BLK');
		$flight_seats_cond = array('flight_id' => $flight_id, 'seat_flag' => 'BLK');
		$flight_blocked_seat_details = $this->custom_db->single_table_records('flight_seat', 'seat_number,seat_flag', $flight_seats_cond)['data'];

		$count = isset($flight_blocked_seat_details) ? count($flight_blocked_seat_details) : 0;
		$block_seat_count = $count;

		// if ($block_seat_count > 1) {
		$response['status'] = SUCCESS_STATUS;
		$response['blocked_seats'] = $flight_blocked_seat_details;
		$response['blocked_seat_count'] = $block_seat_count;
		// }

		return $response;
	}
	public function check_date_in_holiday(string $departure_date): array
	{
		$departure_date = $departure_date1 = date("Y-m-d", strtotime($departure_date));
		$departure_date = $this->db->escape($departure_date);
		$query = 'Select * from flight_crs_holiday_master where holiday_date =' . $departure_date;
		$data = $this->db->query($query)->result_array();
		if (valid_array($data)) {
			$query = "Select * from flight_crs_holiday_fare where holiday_type='Holiday'";
		} else {
			$holiday_dates[] = date('Y-m-d', strtotime($departure_date1 . ' +1 day'));
			$holiday_dates[] = date('Y-m-d', strtotime($departure_date1 . ' -1 day'));
			$holiday_dates[] = date('Y-m-d', strtotime($departure_date1 . ' +2 days'));
			$holiday_dates[] = date('Y-m-d', strtotime($departure_date1 . ' -2 days'));
			$holiday_dates[] = date('Y-m-d', strtotime($departure_date1 . ' +3 days'));
			$holiday_dates[] = date('Y-m-d', strtotime($departure_date1 . ' -3 days'));
			$holiday_date_list = '';
			foreach ($holiday_dates as $date) {
				$holiday_date_list .= '"' . $date . '"' . ", ";
			}
			$holiday_date_list = trim($holiday_date_list, ", ");
			$query = 'Select * from flight_crs_holiday_master where holiday_date in (' . $holiday_date_list . ')';
			$data = $this->db->query($query)->result_array();
			if (valid_array($data)) {
				$query = "Select * from flight_crs_holiday_fare where holiday_type='Near Holiday'";
			} else {
				$query = "Select * from flight_crs_holiday_fare where holiday_type='Normal'";
			}
		}
		return $this->db->query($query)->result_array();
	}
	public function check_time_before_booking(string $departure_date): array
	{
		$today_date = date("Y-m-d");
		$travel_duration = abs(get_date_difference($today_date, $departure_date));
		$query = 'Select * from flight_crs_time_before_booking_master where before_booking_days>=' . $travel_duration;
		return $this->db->query($query)->result_array();;
	}
	public function flight_details(int $fsid): array
	{
		$query = 'SELECT *,DATE_FORMAT(departure_from_date, "%d-%m-%Y") AS `departure_date_from`,DATE_FORMAT(departure_to_date, "%d-%m-%Y") AS `departure_date_to`,DATE_FORMAT(departure_time, "%H:%i") AS `departure_time`,DATE_FORMAT(arrival_time, "%H:%i") AS `arrival_time` FROM `flight_crs_details` WHERE `fsid`=' . $fsid . ' ORDER BY fdid, trip_type';
		return $this->db->query($query)->result_array();
	}
	public function enquiries(): bool
	{

		$this->db->from('flight_enquiry');
		$this->db->order_by('id', "desc");
		$query = $this->db->get();
		if ($query->num_rows > 0) {
			return $query->result();
		}
		return false;
	}
	public function initial_update_flight_details(int $id, array $data): array
	{
		$return_data=[];
		$GMT=[];
		$conb=[];
		$data_b=[];
		$con=[];
		$data = $data['flight_details'][0];
		$con['origin'] = $data['origin'];
		$con['destination'] = $data['destination'];
		$con['flight_num'] = $data['flight_num'];
		$con['carrier_code'] = $data['carrier_code'];
		$con['class_type'] = $data['class_type'];
		$con['fsid'] = $id;
		//debug($con); die;
		$fs_res = $this->custom_db->single_table_records('flight_crs_segment_details', '*', $con);

		// debug($fs_res);exit;
		$blocked_seat_details = $this->flight_blocked_seat_details($id);

		// debug($blocked_seat_details);exit;
		/*	$query ="SELECT * FROM `flight_crs_segment_details` where fsid = $id";
				
		
		$result = $this->db->query($query)->result_array();*/

		$fsid_list = array();
		// debug($fs_res);exit;
		foreach ($fs_res['data'] as $d) {
			$fsid_list[] = $d['fsid'];
			$condition = array();
			$seat_condition = array();

			$condition['fsid'] = $d['fsid'];
			$fs_flight_res = $this->custom_db->single_table_records('crs_update_flight_details', '*', $condition);
			$seat_condition['flight_id'] = $d['fsid'];
			$seat_res = $this->custom_db->single_table_records('flight_crs_master_seat_price_range', '*', $seat_condition);


			//	$query = $this->db->query("SELECT * FROM crs_update_flight_details where fsid = ".$d['fsid']);
			//	$count = $query->num_rows();
			if ($fs_flight_res['status'] > 0) {
				if ($blocked_seat_details['status'] == SUCCESS_STATUS) {
					// debug($fs_res);exit;

					$avail_seat = $fs_res['data'][0]['seats'];
					$data_b['blocked_seat'] = $blocked_seat_details['blocked_seat_count'];
					$data_b['avail_seat'] = $avail_seat;
					$conb['fsid'] = $id;
					//debug($data_b);exit;
					$this->custom_db->update_record('crs_update_flight_details', $data_b, $conb);
				}
			} else {
				$update_details = $d;
				$dep_from_date = $update_details['dep_from_date'];
				$dep_to_date = $update_details['dep_to_date'];
				$begin = new DateTime($dep_from_date);
				$end = new DateTime($dep_to_date);
				$end = $end->modify('+1 day');

				$interval = new DateInterval('P1D');
				$daterange = new DatePeriod($begin, $interval, $end);



				$days = array();
				if (!empty($d['days'])) {
					$days = explode(',', $d['days']);
				}



				$begin1 = new DateTime($d['GMT_dep_time']);
				$end1 = new DateTime($d['GMT_arr_time']);
				$end1 = $end->modify('+1 day');

				//	debug($begin1);
				//	debug($end1); 

				$daterange1 = new DatePeriod($begin1, $interval, $end1);
				//	debug($daterange1);

				foreach ($daterange1 as $date1) {

					$GMT[] = $date1->format('Y-m-d');

					//$d_day = date('D', $timestamp);
				}
				//	debug($GMT);

				// debug($d);die;
				foreach ($daterange as $y => $date) {
					// $date1 = $date->format('d-M-Y');
					//debug($y);
					$timestamp = strtotime($date->format('Y-m-d'));

					$d_day = date('D', $timestamp);

					if (valid_array($days)) {

						if (in_array($d_day, $days)) {
							$query = "INSERT into crs_update_flight_details (fsid, avail_date,GMT_avail_date,adult_base,adult_tax,child_base,child_tax,infant_base,infant_tax,adult_local_base,adult_local_tax,child_local_base,child_local_tax,infant_local_base,infant_local_tax,tax_breakup,dep_time,arr_time,GMT_dep_time,GMT_arr_time,avail_seat,aircraft) 
    					values('" . $update_details['fsid'] . "','" . $date->format('Y-m-d') . "','" . $GMT[$y] . "','" . $d['adult_basefare'] . "','" . $d['adult_tax'] . "','" . $d['child_basefare'] . "','" . $d['child_tax'] . "','" . $d['infant_basefare'] . "','" . $d['infant_tax'] . "','" . $d['adult_local_basefare'] . "','" . $d['adult_local_tax'] . "','" . $d['child_local_basefare'] . "','" . $d['child_local_tax'] . "','" . $d['infant_local_basefare'] . "','" . $d['infant_local_tax'] . "','" . $d['tax_breakup'] . "','" . substr($d['departure_time'], 0, 5) . "','" . substr($d['arrival_time'], 0, 5) . "','" . date('H:i:s', strtotime($d['GMT_dep_time'])) . "','" . date('H:i:s', strtotime($d['GMT_arr_time'])) . "', '" . $d['seats'] . "','" . $d['aircraft'] . "')";
							debug($query);
							exit;
							$this->db->query($query);
							$query1 = "SELECT `origin` FROM `crs_update_flight_details`   ORDER BY `origin` DESC LIMIT 1";
							$res = $this->db->query($query1)->result_array();
							// debug($res);exit;


							$seat_range = array();
							if (valid_array($seat_res['data'])) {
								foreach ($seat_res['data'] as $master_seat_key => $master_seat_value) {
									$seat_range_data = array(
										'seat_from_range' => $master_seat_value['seat_from_range'],
										'seat_to_range' => $master_seat_value['seat_to_range'],
										'price' => $master_seat_value['price'],
										'created_date' => date('Y-m-d h:i:s'),
										'created_by_id' => $this->entity_user_id,
										'flight_id' => $master_seat_value['flight_id'],
										'flight_crs_list_fk' => $res[0]['origin']
									);
									array_push($seat_range, $seat_range_data);
								}
								// debug($seat_range);exit;
								$this->db->insert_batch('flight_crs_seat_price_range', $seat_range);
							}
						}
					} else {
						$query = "INSERT into crs_update_flight_details (fsid, avail_date,GMT_avail_date,adult_base,adult_tax,child_base,child_tax,infant_base,infant_tax,adult_local_base,adult_local_tax,child_local_base,child_local_tax,infant_local_base,infant_local_tax,tax_breakup,dep_time,arr_time,GMT_dep_time,GMT_arr_time,avail_seat ,aircraft) 
					values('" . $update_details['fsid'] . "','" . $date->format('Y-m-d') . "','" . $GMT[$y] . "','" . $d['adult_basefare'] . "','" . $d['adult_tax'] . "','" . $d['child_basefare'] . "','" . $d['child_tax'] . "','" . $d['infant_basefare'] . "','" . $d['infant_tax'] . "','" . $d['adult_local_basefare'] . "','" . $d['adult_local_tax'] . "','" . $d['child_local_basefare'] . "','" . $d['child_local_tax'] . "','" . $d['infant_local_basefare'] . "','" . $d['infant_local_tax'] . "','" . $d['tax_breakup'] . "','" . substr($d['departure_time'], 0, 5) . "','" . substr($d['arrival_time'], 0, 5) . "','" . date('H:i:s', strtotime($d['GMT_dep_time'])) . "','" . date('H:i:s', strtotime($d['GMT_arr_time'])) . "', '" . $d['seats'] . "','" . $d['aircraft'] . "')";
						// debug($query);exit;
						$this->db->query($query);
						$query1 = "SELECT `origin` FROM `crs_update_flight_details`   ORDER BY `origin` DESC LIMIT 1";
						$res = $this->db->query($query1)->result_array();
						// debug($res);exit;


						$seat_range = array();
						foreach ($seat_res['data'] as $master_seat_key => $master_seat_value) {
							$seat_range_data = array(
								'seat_from_range' => $master_seat_value['seat_from_range'],
								'seat_to_range' => $master_seat_value['seat_to_range'],
								'price' => $master_seat_value['price'],
								'created_date' => date('Y-m-d h:i:s'),
								'created_by_id' => $this->entity_user_id,
								'flight_id' => $master_seat_value['flight_id'],
								'flight_crs_list_fk' => $res[0]['origin']
							);
							array_push($seat_range, $seat_range_data);
						}
						// debug($seat_range);exit;
						// $this->db->insert_batch('flight_crs_seat_price_range', $seat_range);
					}
				}
				//exit; 
			}
		}
		// debug($fsid_list);exit;
		$return_data['fsid_list'] = $fsid_list;
		return $return_data;
	}
	public function crs_update_flight_details(string $fsid_list, array $filter_data = []): array
	{

		$condition = '';
		if (isset($filter_data['month']) and !empty($filter_data['month'])) {
			//$condition .= ' AND month(avail_date) = '.$filter_data['month'].'';
		}
		if (isset($filter_data['year']) and !empty($filter_data['year'])) {
			//$condition .= ' AND year(avail_date) = '.$filter_data['year'].'';
		} else {
			//$condition .= ' AND year(avail_date) = year(curdate())';
		}
		//debug($fsid_list); die;
		//$query ="SELECT * FROM `crs_update_flight_details` where fsid in(".$fsid_list.") ".$condition ." order by avail_date";
		$query = "SELECT * FROM `crs_update_flight_details` where fsid in(" . $fsid_list . ") " . $condition . " order by avail_date";
		//debug($query); exit;
		return $this->db->query($query)->result_array();
	}
	public function check_date_in_season(string $departure_date): array
	{
		$departure_date = date("Y-m-d", strtotime($departure_date));
		$departure_date = $this->db->escape($departure_date);
		$query = 'Select * from flight_crs_season_master where ' . $departure_date . ' between start_date and end_date order by origin desc';
		return $this->db->query($query)->result_array();;
	}
	public function check_date_in_week(string $departure_date): array
	{
		$departure_date = date("D", strtotime($departure_date));
		$query = "Select * from flight_crs_week_master where label='" . $departure_date . "'";
		return $this->db->query($query)->result_array();;
	}
}
