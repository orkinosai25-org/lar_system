<?php
require_once 'transaction.php';
/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Flight Model
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */
Class Flight_Model extends Transaction
{
	/*
	 *
	 * Get Airport List
	 *
	 */

	 function get_airport_list(string $search_chars): CI_DB_result
	 {
		 $raw_search_chars = $this->db->escape($search_chars);
		 
		 $r_search_chars = $this->db->escape($search_chars !== '' ? $search_chars . '%' : $search_chars);
		 $search_chars_escaped = $this->db->escape($search_chars !== '' ? '%' . $search_chars . '%' : $search_chars);
	 
		 $query = "
			 SELECT * 
			 FROM flight_airport_list 
			 WHERE airport_city LIKE {$search_chars_escaped}
				OR airport_code LIKE {$search_chars_escaped}
				OR country LIKE {$search_chars_escaped}
			 ORDER BY top_destination DESC,
				 CASE
					 WHEN airport_code LIKE {$raw_search_chars} THEN 1
					 WHEN airport_city LIKE {$raw_search_chars} THEN 2
					 WHEN country LIKE {$raw_search_chars} THEN 3
	 
					 WHEN airport_code LIKE {$r_search_chars} THEN 4
					 WHEN airport_city LIKE {$r_search_chars} THEN 5
					 WHEN country LIKE {$r_search_chars} THEN 6
	 
					 WHEN airport_code LIKE {$search_chars_escaped} THEN 7
					 WHEN airport_city LIKE {$search_chars_escaped} THEN 8
					 WHEN country LIKE {$search_chars_escaped} THEN 9
	 
					 ELSE 10
				 END
			 LIMIT 0, 20
		 ";
	 
		 return $this->db->query($query);
	 }
	 

	 function top_flight_destination(): array
	 {
		 $query = 'SELECT CT.*, CN.name AS country 
				   FROM flight_airport_list CT
				   JOIN api_country_list CN ON CT.country_id = CN.id
				   WHERE top_destination = ' . ACTIVE . ' 
				   GROUP BY CT.origin';
	 
		 $data = $this->db->query($query)->result_array();
		 
		 return $data;
	 }
	 
	
	/**
	 * Flight booking report
	 *
	 */
	function booking(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array|int
	{
		$condition = $this->custom_db->get_custom_condition($condition);

		// Count query
		if ($count) {
			$query = 'SELECT COUNT(DISTINCT(BD.app_reference)) AS total_records 
				FROM flight_booking_details BD
				WHERE domain_origin = ' . get_domain_auth_id() . ' 
				AND BD.created_by_id = ' . $GLOBALS['CI']->entity_user_id . ' ' . $condition;
			$data = $this->db->query($query)->row_array();
			return (int) $data['total_records'];
		}

		$this->load->library('booking_data_formatter');
		$response = [
			'status' => SUCCESS_STATUS,
			'data' => []
		];

		// Initialize empty arrays for details
		$booking_itinerary_details = [];
		$booking_customer_details = [];
		$booking_transaction_details = [];
		$cancellation_details = [];

		// Booking Details query
		$bd_query = 'SELECT * 
			FROM flight_booking_details AS BD
			WHERE BD.domain_origin = ' . get_domain_auth_id() . ' 
			AND BD.created_by_id = ' . $GLOBALS['CI']->entity_user_id . ' ' . $condition . ' 
			ORDER BY BD.created_datetime DESC, BD.origin DESC 
			LIMIT ' . $offset . ', ' . $limit;

		$booking_details = $this->db->query($bd_query)->result_array();
		$app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);

		if (!empty($app_reference_ids)) {
			// Itinerary Details query
			$id_query = 'SELECT * 
				FROM flight_booking_itinerary_details AS ID
				WHERE ID.app_reference IN (' . $app_reference_ids . ')';

			// Transaction Details query
			$td_query = 'SELECT * 
				FROM flight_booking_transaction_details AS TD
				WHERE TD.app_reference IN (' . $app_reference_ids . ')';

			// Customer and Ticket Details query
			$cd_query = 'SELECT CD.*, FPTI.TicketId, FPTI.TicketNumber, FPTI.IssueDate, FPTI.Fare, FPTI.SegmentAdditionalInfo 
				FROM flight_booking_passenger_details AS CD
				LEFT JOIN flight_passenger_ticket_info FPTI ON CD.origin = FPTI.passenger_fk
				WHERE CD.flight_booking_transaction_details_fk IN 
					(SELECT TD.origin 
					FROM flight_booking_transaction_details AS TD 
					WHERE TD.app_reference IN (' . $app_reference_ids . '))';

			// Cancellation Details query
			$cancellation_details_query = 'SELECT FCD.* 
				FROM flight_booking_passenger_details AS CD
				LEFT JOIN flight_cancellation_details AS FCD ON FCD.passenger_fk = CD.origin
				WHERE CD.flight_booking_transaction_details_fk IN 
					(SELECT TD.origin 
					FROM flight_booking_transaction_details AS TD 
					WHERE TD.app_reference IN (' . $app_reference_ids . '))';

			// Fetch results
			$booking_itinerary_details = $this->db->query($id_query)->result_array();
			$booking_customer_details = $this->db->query($cd_query)->result_array();
			$booking_transaction_details = $this->db->query($td_query)->result_array();
			$cancellation_details = $this->db->query($cancellation_details_query)->result_array();
		}

		// Populate response data
		$response['data'] = [
			'booking_details' => $booking_details,
			'booking_itinerary_details' => $booking_itinerary_details,
			'booking_transaction_details' => $booking_transaction_details,
			'booking_customer_details' => $booking_customer_details,
			'cancellation_details' => $cancellation_details
		];

		return $response;
	}
	

	/**
	 * Read Individual booking details - dont use it to generate table
	 * @param $app_reference
	 * @param $booking_source
	 * @param $booking_status
	 */
	function get_booking_details(
		string $app_reference, 
		string $booking_source = '', 
		string $booking_status = ''
	): array // Added return type here
	{
		$response = [
			'status' => FAILURE_STATUS,
			'data' => []
		];
	
		// Booking Details query
		$bd_query = 'SELECT * 
					 FROM flight_booking_details AS BD 
					 WHERE BD.app_reference LIKE ' . $this->db->escape($app_reference);
		if (!empty($booking_source)) {
			$bd_query .= ' AND BD.booking_source = ' . $this->db->escape($booking_source);
		}
		if (!empty($booking_status)) {
			$bd_query .= ' AND BD.status = ' . $this->db->escape($booking_status);
		}
	
		// Itinerary Details query
		$id_query = 'SELECT * 
					 FROM flight_booking_itinerary_details AS ID 
					 WHERE ID.app_reference = ' . $this->db->escape($app_reference) . ' 
					 ORDER BY origin ASC';
	
		// Transaction Details query
		$td_query = 'SELECT * 
					 FROM flight_booking_transaction_details AS CD 
					 WHERE CD.app_reference = ' . $this->db->escape($app_reference) . ' 
					 ORDER BY origin ASC';
	
		// Customer and Ticket Details query
		$cd_query = 'SELECT DISTINCT CD.*, FPTI.api_passenger_origin, FPTI.TicketId, FPTI.TicketNumber, FPTI.IssueDate, FPTI.Fare, FPTI.SegmentAdditionalInfo
					 FROM flight_booking_passenger_details AS CD
					 LEFT JOIN flight_passenger_ticket_info FPTI ON CD.origin = FPTI.passenger_fk
					 WHERE CD.flight_booking_transaction_details_fk IN 
						 (SELECT DISTINCT TD.origin 
						  FROM flight_booking_transaction_details AS TD 
						  WHERE TD.app_reference = ' . $this->db->escape($app_reference) . ') 
					 ORDER BY origin ASC';
	
		// Cancellation Details query
		$cancellation_details_query = 'SELECT FCD.* 
									  FROM flight_booking_passenger_details AS CD
									  LEFT JOIN flight_cancellation_details AS FCD ON FCD.passenger_fk = CD.origin
									  WHERE CD.flight_booking_transaction_details_fk IN 
										  (SELECT TD.origin 
										   FROM flight_booking_transaction_details AS TD 
										   WHERE TD.app_reference = ' . $this->db->escape($app_reference) . ') 
									  ORDER BY origin ASC';
	
		// Baggage Details query
		$baggage_query = 'SELECT CD.flight_booking_transaction_details_fk, CD.passenger_type,
								 CONCAT(CD.first_name, " ", CD.last_name) AS pax_name, FBG.*
						  FROM flight_booking_passenger_details AS CD
						  JOIN flight_booking_baggage_details FBG ON CD.origin = FBG.passenger_fk
						  WHERE CD.flight_booking_transaction_details_fk IN 
							  (SELECT TD.origin 
							   FROM flight_booking_transaction_details AS TD 
							   WHERE TD.app_reference = ' . $this->db->escape($app_reference) . ')';
	
		// Meal Details query
		$meal_query = 'SELECT CD.flight_booking_transaction_details_fk,
							  CONCAT(CD.first_name, " ", CD.last_name) AS pax_name, FML.*
					   FROM flight_booking_passenger_details AS CD
					   JOIN flight_booking_meal_details FML ON CD.origin = FML.passenger_fk
					   WHERE CD.flight_booking_transaction_details_fk IN 
						   (SELECT TD.origin 
							FROM flight_booking_transaction_details AS TD 
							WHERE TD.app_reference = ' . $this->db->escape($app_reference) . ')';
	
		// Seat Details query
		$seat_query = 'SELECT CD.flight_booking_transaction_details_fk,
							  CONCAT(CD.first_name, " ", CD.last_name) AS pax_name, FST.*
					   FROM flight_booking_passenger_details AS CD
					   JOIN flight_booking_seat_details FST ON CD.origin = FST.passenger_fk
					   WHERE CD.flight_booking_transaction_details_fk IN 
						   (SELECT TD.origin 
							FROM flight_booking_transaction_details AS TD 
							WHERE TD.app_reference = ' . $this->db->escape($app_reference) . ')';
	
		// Execute queries and assign results to response data
		$response['data']['booking_details'] = $this->db->query($bd_query)->result_array();
		$response['data']['booking_itinerary_details'] = $this->db->query($id_query)->result_array();
		$response['data']['booking_transaction_details'] = $this->db->query($td_query)->result_array();
		$response['data']['booking_customer_details'] = $this->db->query($cd_query)->result_array();
		$response['data']['cancellation_details'] = $this->db->query($cancellation_details_query)->result_array();
		$response['data']['baggage_details'] = $this->db->query($baggage_query)->result_array();
		$response['data']['meal_details'] = $this->db->query($meal_query)->result_array();
		$response['data']['seat_details'] = $this->db->query($seat_query)->result_array();
	
		// Update response status if necessary data is available
		if (
			valid_array($response['data']['booking_details']) && 
			valid_array($response['data']['booking_itinerary_details']) && 
			valid_array($response['data']['booking_customer_details'])
		) {
			$response['status'] = SUCCESS_STATUS;
		}
	
		return $response; // Returning the array
	}
	
	/**
	 * Read Individual booking details - dont use it to generate table
	 * @param $app_reference
	 * @param $booking_source
	 * @param $booking_status
	 */
	function booking_guest_user(
		string $app_reference, 
		string $booking_source = '', 
		string $booking_status = ''
	): array // Added return type here
	{
		$response = [
			'status' => FAILURE_STATUS,
			'data' => []
		];
	
		// Booking Details query
		$bd_query1 = 'SELECT * 
					  FROM flight_booking_transaction_details AS FTD 
					  WHERE (FTD.app_reference LIKE ' . $this->db->escape($app_reference) . ' 
					  OR FTD.pnr LIKE ' . $this->db->escape($app_reference) . ')';
		$booking_details1 = $this->db->query($bd_query1)->result_array();
	
		if (valid_array($booking_details1)) {
			$bd_query = 'SELECT * 
						 FROM flight_booking_details AS BD 
						 WHERE BD.app_reference LIKE ' . $this->db->escape($app_reference);
	
			if (!empty($booking_source)) {
				$bd_query .= ' AND BD.booking_source = ' . $this->db->escape($booking_source);
			}
			if (!empty($booking_status)) {
				$bd_query .= ' AND BD.status = ' . $this->db->escape($booking_status);
			}
	
			$booking_details = $this->db->query($bd_query)->result_array();
			$app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);
	
			if (!empty($app_reference_ids)) {
				// Itinerary Details query
				$id_query = 'SELECT * 
							 FROM flight_booking_itinerary_details AS ID 
							 WHERE ID.app_reference IN (' . $app_reference_ids . ')';
	
				// Transaction Details query
				$td_query = 'SELECT * 
							 FROM flight_booking_transaction_details AS TD 
							 WHERE TD.app_reference IN (' . $app_reference_ids . ')';
	
				// Customer and Ticket Details query
				$cd_query = 'SELECT CD.*, FPTI.TicketId, FPTI.TicketNumber, FPTI.IssueDate, FPTI.Fare, FPTI.SegmentAdditionalInfo
							 FROM flight_booking_passenger_details AS CD
							 LEFT JOIN flight_passenger_ticket_info FPTI ON CD.origin = FPTI.passenger_fk
							 WHERE CD.flight_booking_transaction_details_fk IN 
								 (SELECT TD.origin 
								  FROM flight_booking_transaction_details AS TD 
								  WHERE TD.app_reference IN (' . $app_reference_ids . '))';
	
				// Cancellation Details query
				$cancellation_details_query = 'SELECT FCD.* 
											  FROM flight_booking_passenger_details AS CD
											  LEFT JOIN flight_cancellation_details AS FCD ON FCD.passenger_fk = CD.origin
											  WHERE CD.flight_booking_transaction_details_fk IN 
												  (SELECT TD.origin 
												   FROM flight_booking_transaction_details AS TD 
												   WHERE TD.app_reference IN (' . $app_reference_ids . '))';
	
				// Execute the queries
				$booking_itinerary_details = $this->db->query($id_query)->result_array();
				$booking_customer_details = $this->db->query($cd_query)->result_array();
				$booking_transaction_details = $this->db->query($td_query)->result_array();
				$cancellation_details = $this->db->query($cancellation_details_query)->result_array();
	
				// Assign results to response
				$response['data']['booking_details'] = $booking_details;
				$response['data']['booking_itinerary_details'] = $booking_itinerary_details;
				$response['data']['booking_transaction_details'] = $booking_transaction_details;
				$response['data']['booking_customer_details'] = $booking_customer_details;
				$response['data']['cancellation_details'] = $cancellation_details;
	
				// Set status if data is valid
				if (valid_array($response['data']['booking_details']) 
					&& valid_array($response['data']['booking_itinerary_details']) 
					&& valid_array($response['data']['booking_customer_details'])) {
					$response['status'] = SUCCESS_STATUS;
				}
			}
		}
	
		return $response; // Return response array
	}

	// added for flight_cancellation_details
	function add_flight_cancellation_details(int $pax_origin): void
	{
		// 1. Adding Cancellation Details
		$data = [
			'RequestId' => 1,
			'ChangeRequestStatus' => 1,
			'statusDescription' => 'Unassigned',
			'passenger_fk' => $pax_origin,
			'created_by_id' => intval($this->entity_user_id ?? 0),
			'created_datetime' => date('Y-m-d H:i:s'),
			'cancellation_requested_on' => date('Y-m-d H:i:s')
		];

		// Insert Data
		$this->custom_db->insert_record('flight_cancellation_details', $data);
	}

	/**
	 * Update Flight Booking Transaction Status based on Passenger Ticket status
	 * @param unknown_type $transaction_origin
	 */
	public function update_flight_booking_transaction_cancel_status(int $transaction_origin): void
{
    $confirmed_passenger_exists = $this->custom_db->single_table_records(
        'flight_booking_passenger_details',
        '*',
        ['flight_booking_transaction_details_fk' => $transaction_origin, 'status' => 'BOOKING_CONFIRMED']
    );

    if ($confirmed_passenger_exists['status'] == false) {
        $transaction_update_data = ['status' => 'BOOKING_CANCELLED'];
        $transaction_update_condition = ['origin' => $transaction_origin];

        $this->custom_db->update_record('flight_booking_transaction_details', $transaction_update_data, $transaction_update_condition);
    }
}

/**
 * Update Flight Booking Status based on Passenger Ticket status
 */
public function update_flight_booking_cancel_status(string $app_reference): void
{
    $confirmed_passenger_exists = $this->custom_db->single_table_records(
        'flight_booking_passenger_details',
        '*',
        ['app_reference' => $app_reference, 'status' => 'BOOKING_CONFIRMED']
    );

    if ($confirmed_passenger_exists['status'] == false) {
        $booking_update_data = ['status' => 'BOOKING_CANCELLED'];
        $booking_update_condition = ['app_reference' => $app_reference];

        $this->custom_db->update_record('flight_booking_details', $booking_update_data, $booking_update_condition);
    }
}

/**
 * Check if destinations are domestic
 * @param string|array $from_loc
 * @param string|array $to_loc
 */
	public function is_domestic_flight(array|string $from_loc, array|string $to_loc): bool
	{
		// Multicity or One-way/Round-trip
		$airport_cities = valid_array($from_loc) || valid_array($to_loc)
			? array_merge((array) $from_loc, (array) $to_loc)
			: [$from_loc, $to_loc];

		$airport_city_codes = '"' . implode('","', array_unique($airport_cities)) . '"';

		// Query to check country of airports
		$query = "SELECT count(*) AS total FROM flight_airport_list WHERE airport_code IN ($airport_city_codes) AND country != 'India'";

		$data = $this->db->query($query)->row_array();
		return intval($data['total']) == 0;
	}


/**
 * Safe Search data for calendar
 * @param array $search_data
 * @return array
 */
public function calendar_safe_search_data(array $search_data): array
{
    $safe_data = [];

    // Origin
    $safe_data['from_location'] = $search_data['from'] ?? 'DEL';
    $safe_data['from_loc'] = $safe_data['from'] = !empty($search_data['from'])
        ? substr(chop(substr($search_data['from'], -5), ')'), -3)
        : 'DEL';

    // Destination
    $safe_data['to_location'] = $search_data['to'] ?? 'BLR';
    $safe_data['to_loc'] = $safe_data['to'] = !empty($search_data['to'])
        ? substr(chop(substr($search_data['to'], -5), ')'), -3)
        : 'BLR';

    // Preferred Carrier
    $safe_data['carrier'] = (isset($search_data['carrier']) && valid_array($search_data['carrier']))
        ? $search_data['carrier']
        : '';

    // Adult Count
    $safe_data['adult'] = (!empty($search_data['adult']) && intval($search_data['adult']) > 0)
        ? intval($search_data['adult'])
        : 1;

    // Departure Date
    $safe_data['depature'] = !empty($search_data['depature'])
        ? date('Y-m', strtotime($search_data['depature'])) . '-01'
        : date('Y-m-d');

    // Static fields
    $safe_data['trip_type'] = 'OneWay';
    $safe_data['cabin'] = 'Economy';
    $safe_data['return'] = '';
    $safe_data['PromotionalPlanType'] = 'Normal';

    return $safe_data;
}


	/**
 * Clean search data and validate it
 * @param array $temp_search_data
 * @return array{data: array, status: bool}
 */
public function clean_search_data(array $temp_search_data): array
{
    $clean_search = [];
    $success = true;
    if (!isset($temp_search_data['trip_type'])) {
        return ['data' => [], 'status' => false];
    }
 
    $trip_type = $temp_search_data['trip_type'];
    $clean_search['trip_type'] = $trip_type;
 
    $is_multicity = $trip_type == 'multicity';
    $clean_search['trip_type_label'] = match ($trip_type) {
        'circle' => 'Round Way',
        'oneway' => 'One Way',
        default => 'Multi City',
    };
 
    if ($is_multicity) {
        for ($i = 0; $i < count($temp_search_data['depature']); $i++) {
            $prev_dep = $temp_search_data['depature'][$i - 1] ?? date('Y-m-d');
            $cur_dep = $temp_search_data['depature'][$i] ?? '';
 
            if (empty($cur_dep)) {
                $success = false;
                break;
            }
 
            $cur_time = strtotime($cur_dep);
            if (!($cur_time > time() || date('Y-m-d', $cur_time) == date('Y-m-d')) || $cur_time < strtotime($prev_dep)) {
                $success = false;
                break;
            }
 
            $clean_search['depature'][$i] = $cur_dep;
 
            $from = $temp_search_data['from'][$i] ?? '';
            if (empty($from)) {
                $success = false;
                break;
            }
            $clean_search['from'][$i] = $from;
            $clean_search['from_loc'][$i] = substr(chop(substr($from, -5), ')'), -3);
            $clean_search['from_loc_id'][$i] = $temp_search_data['from_loc_id'][$i] ?? null;
 
            $to = $temp_search_data['to'][$i] ?? '';
            if (empty($to)) {
                $success = false;
                break;
            }
            $clean_search['to'][$i] = $to;
            $clean_search['to_loc'][$i] = substr(chop(substr($to, -5), ')'), -3);
            $clean_search['to_loc_id'][$i] = $temp_search_data['to_loc_id'][$i] ?? null;
        }
 
        if (!empty($clean_search['from_loc']) && !empty($clean_search['to_loc'])) {
            $clean_search['is_domestic'] = $this->is_domestic_flight($clean_search['from_loc'], $clean_search['to_loc']);
        }
        $clean_search['adult_config'] = $temp_search_data['adult'];
	    $clean_search['child_config'] = $temp_search_data['child'] ?? 0;
	    $clean_search['infant_config'] = $temp_search_data['infant'] ?? 0;
	    $clean_search['v_class'] = $temp_search_data['v_class'] ?? '';
	    $clean_search['carrier'] = $temp_search_data['carrier'] ?? '';
 
        return ['data' => $clean_search, 'status' => $success];
    }
 
    // Non-multicity (oneway/circle) logic
    $depature = $temp_search_data['depature'] ?? '';
    if (empty($depature)) {
        return ['data' => [], 'status' => false];
    }
 
    $dep_time = strtotime($depature);
    if ($dep_time < time() || date('Y-m-d', $dep_time) < date('Y-m-d')) {
        return ['data' => [], 'status' => false];
    }
    $clean_search['depature'] = $depature;
 
    if ($trip_type == 'circle') {
        $return = $temp_search_data['return'] ?? '';
        $ret_time = strtotime($return);
 
        if (empty($return) || $ret_time <= time() || $ret_time < $dep_time) {
            return ['data' => [], 'status' => false];
        }
 
        $clean_search['return'] = $return;
    }
 
    $from = $temp_search_data['from'] ?? '';
    if (empty($from)) {
        return ['data' => [], 'status' => false];
    }
 
    $to = $temp_search_data['to'] ?? '';
    if (empty($to)) {
        return ['data' => [], 'status' => false];
    }
 
    $clean_search['from'] = $from;
    $clean_search['from_loc'] = substr(chop(substr($from, -5), ')'), -3);
    $clean_search['from_loc_airport_name'] = $temp_search_data['from_loc_airport_name'] ?? '';
    $clean_search['from_loc_id'] = $temp_search_data['from_loc_id'] ?? null;
 
    $clean_search['to'] = $to;
    $clean_search['to_loc'] = substr(chop(substr($to, -5), ')'), -3);
    $clean_search['to_loc_airport_name'] = $temp_search_data['to_loc_airport_name'] ?? '';
    $clean_search['to_loc_id'] = $temp_search_data['to_loc_id'] ?? null;
 
    $clean_search['is_domestic'] = $this->is_domestic_flight($clean_search['from_loc'], $clean_search['to_loc']);
 
    // Passenger & misc fields
    if (!isset($temp_search_data['adult'])) {
        return ['data' => [], 'status' => false];
    }
 
    $clean_search['adult_config'] = $temp_search_data['adult'];
    $clean_search['child_config'] = $temp_search_data['child'] ?? 0;
    $clean_search['infant_config'] = $temp_search_data['infant'] ?? 0;
    $clean_search['v_class'] = $temp_search_data['v_class'] ?? '';
    $clean_search['carrier'] = $temp_search_data['carrier'] ?? '';
    return ['data' => $clean_search, 'status' => true];
}

/**
 * Get and validate search data
 * @param int $search_id
 * @return array{status: bool, data: array}|null
 */
public function get_safe_search_data(int $search_id): ?array
{
	
    $search_data = $this->get_search_data($search_id);	
    if ($search_data !== false) {
        $temp_search_data = json_decode($search_data['search_data'], true);
        $clean_search = $this->clean_search_data($temp_search_data);
        return ['status' => $clean_search['status'], 'data' => $clean_search['data']];
    }
    return null;
}

/**
 * Get raw search data
 * @param int $search_id
 * @return array|false
 */
public function get_search_data(int $search_id): array|false
{
    $search_data = $this->custom_db->single_table_records(
        'search_history',
        '*',
        ['search_type' => META_AIRLINE_COURSE, 'origin' => $search_id]
    );

    return $search_data['status'] == true ? $search_data['data'][0] : false;
}

/**
 * Get active booking sources for current domain
 * @return array
 */
public function active_booking_source(): array
{
    $query = 'SELECT BS.source_id, BS.origin FROM meta_course_list AS MCL
              JOIN activity_source_map AS ASM ON MCL.origin = ASM.meta_course_list_fk
              JOIN booking_source AS BS ON ASM.booking_source_fk = BS.origin
              WHERE MCL.course_id = ' . $this->db->escape(META_AIRLINE_COURSE) . '
              AND BS.booking_engine_status = ' . ACTIVE . '
              AND MCL.status = ' . ACTIVE . '
              AND ASM.status = "active"';

    return $this->db->query($query)->result_array();
}

/**
 * TEMP FUNCTION: Get static response (for testing)
 * @param int $token_id
 * @return array|null
 */
public function get_static_response(int $token_id): ?array
{
    $static_response = $this->custom_db->single_table_records('test', '*', ['origin' => $token_id]);
    return isset($static_response['data'][0]['test']) ? json_decode($static_response['data'][0]['test'], true) : null;
}


	/**
	 * Lock All the tables necessary for flight transaction to be processed
	 */
	static function lock_tables():void
	{
		$CI = & get_instance();
		$CI->db->query(' LOCK TABLES domain_list AS DL WRITE, currency_converter AS CC WRITE ;');
	}

	/**
	 * Save complete master transaction details of flight
	 * @param $domain_origin
	 * @param $status
	 * @param $app_reference
	 * @param $booking_source
	 * @param $is_lcc
	 * @param $total_fare
	 * @param $domain_markup
	 * @param $level_one_markup
	 * @param $currency
	 * @param $phone
	 * @param $alternate_number
	 * @param $email
	 * @param $journey_start
	 * @param $journey_end
	 * @param $journey_from
	 * @param $journey_to
	 * @param $payment_mode
	 * @param $attributes
	 * @param $created_by_id
	 */
	public function save_flight_booking_details(
		array $flight_booking_data,
		int $created_by_id,
		string $from_loc,
		string $to_loc,
		string $from_to_trip_type,
		string $transaction_currency,
		float $currency_conversion_rate,
		string $gst_details,
		string $phone_country_code
	): void {
		$data = [
			'domain_origin' => $flight_booking_data['domain_origin'],
			'status' => $flight_booking_data['status'],
			'app_reference' => $flight_booking_data['app_reference'],
			'booking_source' => $flight_booking_data['booking_source'],
			'phone' => $flight_booking_data['phone'],
			'alternate_number' => $flight_booking_data['alternate_number'],
			'email' => $flight_booking_data['email'],
			'journey_start' => $flight_booking_data['journey_start'],
			'journey_end' => $flight_booking_data['journey_end'],
			'journey_from' => $flight_booking_data['journey_from'],
			'journey_to' => $flight_booking_data['journey_to'],
			'payment_mode' => $flight_booking_data['payment_mode'],
			'attributes' => $flight_booking_data['attributes'],
			'created_by_id' => $created_by_id,
			'created_datetime' => date('Y-m-d H:i:s'),
			'from_loc' => $from_loc,
			'to_loc' => $to_loc,
			'trip_type' => $from_to_trip_type,
			'cabin_class' => $flight_booking_data['cabin_class'],
			'currency' => $transaction_currency,
			'currency_conversion_rate' => $currency_conversion_rate,
			'gst_details' => $gst_details,
			'phone_code' => $phone_country_code,
		];
	
		$this->custom_db->insert_record('flight_booking_details', $data);
	}
	

	/**
	 * Save Passenger details of Flight Booking
	 * @param $app_reference
	 * @param $passenger_type
	 * @param $is_lead
	 * @param $title
	 * @param $first_name
	 * @param $middle_name
	 * @param $last_name
	 * @param $date_of_birth
	 * @param $gender
	 * @param $passenger_nationality
	 * @param $passport_number
	 * @param $passport_issuing_country
	 * @param $passport_expiry_date
	 * @param $status
	 * @param $attributes
	 */
	public function save_flight_booking_passenger_details(
		array $passenger_det,
		string $passenger_nationality,
		?string $passport_number,
		?string $passport_issuing_country,
		?string $passport_expiry_date,
		string $status,
		string $attributes,
		int $flight_booking_transaction_details_fk
	): array {
		$data = [
			'app_reference' => $passenger_det['app_reference'],
			'passenger_type' => $passenger_det['passenger_type'],
			'is_lead' => $passenger_det['is_lead'],
			'title' => $passenger_det['title'],
			'first_name' => $passenger_det['first_name'],
			'middle_name' => $passenger_det['middle_name'],
			'last_name' => $passenger_det['last_name'],
			'date_of_birth' => $passenger_det['date_of_birth'] ?? 'NULL',
			'gender' => $passenger_det['gender'],
			'passenger_nationality' => $passenger_nationality,
			'passport_number' => $passport_number,
			'passport_issuing_country' => $passport_issuing_country,
			'passport_expiry_date' => $passport_expiry_date,
			'status' => $status,
			'attributes' => $attributes,
			'flight_booking_transaction_details_fk' => $flight_booking_transaction_details_fk,
		];
	
		return $this->custom_db->insert_record('flight_booking_passenger_details', $data);
	}
	
	/**
	 * Balu A
	 * Save Passenger Ticket Information
	 * @param $passenger_fk	
	 */
	public function save_passenger_ticket_info(int $passenger_fk): void
	{
		$data = ['passenger_fk' => $passenger_fk];
		$this->custom_db->insert_record('flight_passenger_ticket_info', $data);
	}


	/**	 
	 * Update Passenger Ticket Information
	 * @param $passenger_fk
	 * @param $TicketId
	 * @param $TicketNumber
	 * @param $IssueDate
	 * @param $Fare
	 * @param $SegmentAdditionalInfo
	 * @param $ValidatingAirline
	 * @param $CorporateCode
	 * @param $TourCode
	 * @param $Endorsement
	 * @param $Remarks
	 * @param $ServiceFeeDisplayType
	 */
	public function update_passenger_ticket_info(
		array $passenger_det,
		?string $CorporateCode,
		?string $TourCode,
		?string $Endorsement,
		?string $Remarks,
		?string $ServiceFeeDisplayType,
	): void {
		$data = [
			'TicketId' => $passenger_det['TicketId'],
			'api_passenger_origin' => $passenger_det['api_passenger_origin'],
			'TicketNumber' => $passenger_det['TicketNumber'],
			'IssueDate' => $passenger_det['IssueDate'],
			'Fare' => $passenger_det['Fare'],
			'SegmentAdditionalInfo' => $passenger_det['SegmentAdditionalInfo'],
			'ValidatingAirline' => $passenger_det['ValidatingAirline'],
			'CorporateCode' => $CorporateCode,
			'TourCode' => $TourCode,
			'Endorsement' => $Endorsement,
			'Remarks' => $Remarks,
			'ServiceFeeDisplayType' => $ServiceFeeDisplayType,
		];
	
		$update_condition = ['passenger_fk' => $passenger_det['passenger_fk']];
	
		$this->custom_db->update_record('flight_passenger_ticket_info', $data, $update_condition);
	}
	
	/**
	 * Save Individual booking details of a transaction
	 * @param $app_reference
	 * @param $transaction_status
	 * @param $status_description
	 * @param $pnr
	 * @param $book_id
	 * @param $source
	 * @param $ref_id
	 * @param $attributes
	 * @param $sequence_number
	 */
	public function save_flight_booking_transaction_details(
		array $transaction_det,
		array $transaction_price
	): array {
		$data = [
			'app_reference' => $transaction_det['app_reference'],
			'status' => $transaction_det['transaction_status'],
			'status_description' => $transaction_det['status_description'],
			'pnr' => $transaction_det['pnr'],
			'book_id' => $transaction_det['book_id'],
			'source' => $transaction_det['source'],
			'ref_id' =>$transaction_det['$ref_id'],
			'attributes' => $transaction_det['attributes'],
			'sequence_number' => $transaction_det['sequence_number'],
	
			'total_fare' => $transaction_price['total_fare'],
			'admin_commission' => $transaction_price['admin_commission'],
			'agent_commission' => $transaction_price['agent_commission'],
			'admin_markup' => $transaction_price['admin_markup'],
			'agent_markup' => $transaction_price['agent_markup'],
			'currency' => $transaction_price['currency'],
	
			'getbooking_StatusCode' => $transaction_price['getbooking_StatusCode'],
			'getbooking_Description' => $transaction_price['getbooking_Description'],
			'getbooking_Category' => $transaction_price['getbooking_Category'],
	
			'admin_tds' => $transaction_price['admin_tds'],
			'agent_tds' => $transaction_price['agent_tds'],
			'gst' => $transaction_price['gst'],
		];
	
		return $this->custom_db->insert_record('flight_booking_transaction_details', $data);
	}
	

	/**
	 * Save Individual booking flight details
	 * @param $app_reference
	 * @param $segment_indicator
	 * @param $airline_code
	 * @param $airline_name
	 * @param $flight_number
	 * @param $fare_class
	 * @param $from_airport_code
	 * @param $from_airport_name
	 * @param $to_airport_code
	 * @param $to_airport_name
	 * @param $departure_datetime
	 * @param $arrival_datetime
	 * @param $status
	 * @param $operating_carrier
	 * @param $attributes
	 */
	public function save_flight_booking_itinerary_details(
		array $flight_itinerary_det,
		string $attributes,
		string $FareRestriction,
		string $FareBasisCode,
		string $FareRuleDetail,
		string $airline_pnr,
		string $cabin_baggage,
		string $checkin_baggage,
		bool $is_refundable
	): array {
		$data = [
			'app_reference' => $flight_itinerary_det['app_reference'],
			'segment_indicator' => $flight_itinerary_det['segment_indicator'],
			'airline_code' => $flight_itinerary_det['airline_code'],
			'airline_name' => $flight_itinerary_det['airline_name'],
			'flight_number' => $flight_itinerary_det['flight_number'],
			'fare_class' => $flight_itinerary_det['fare_class'],
			'from_airport_code' => $flight_itinerary_det['from_airport_code'],
			'from_airport_name' => $flight_itinerary_det['from_airport_name'],
			'to_airport_code' => $flight_itinerary_det['to_airport_code'],
			'to_airport_name' => $flight_itinerary_det['to_airport_name'],
			'departure_datetime' => $flight_itinerary_det['departure_datetime'],
			'arrival_datetime' => $flight_itinerary_det['arrival_datetime'],
			'status' => $flight_itinerary_det['status'],
			'operating_carrier' => $flight_itinerary_det['operating_carrier'],
			'attributes' => $attributes,
			'FareRestriction' => $FareRestriction,
			'FareBasisCode' => $FareBasisCode,
			'FareRuleDetail' => $FareRuleDetail,
			'cabin_baggage' => $cabin_baggage,
			'checkin_baggage' => $checkin_baggage,
			'is_refundable' => $is_refundable,
			'airline_pnr' => $airline_pnr,
		];
	
		return $this->custom_db->insert_record('flight_booking_itinerary_details', $data);
	}
	
	/**
	 * Save Baggage Information
	 * @param unknown_type $passenger_fk
	 * @param unknown_type $from_airport_code
	 * @param unknown_type $to_airport_code
	 * @param unknown_type $description
	 * @param unknown_type $price
	 */
	public function save_passenger_baggage_info(
		int $passenger_fk,
		string $from_airport_code,
		string $to_airport_code,
		string $description,
		float $price,
		string $code
	): array {
		$data = [
			'passenger_fk' => $passenger_fk,
			'from_airport_code' => $from_airport_code,
			'to_airport_code' => $to_airport_code,
			'description' => $description,
			'price' => $price,
			'code' => $code,
		];
	
		return $this->custom_db->insert_record('flight_booking_baggage_details', $data);
	}
	
	/**
	 * Save Baggage Information
	 * @param unknown_type $passenger_fk
	 * @param unknown_type $from_airport_code
	 * @param unknown_type $to_airport_code
	 * @param unknown_type $description
	 * @param unknown_type $price
	 */
	public function save_passenger_meals_info(
		int $passenger_fk,
		string $from_airport_code,
		string $to_airport_code,
		string $description,
		float $price,
		string $code,
		string $type = 'dynamic'
	): array {
		$data = [
			'passenger_fk' => $passenger_fk,
			'from_airport_code' => $from_airport_code,
			'to_airport_code' => $to_airport_code,
			'description' => $description,
			'price' => $price,
			'code' => $code,
			'type' => $type,
		];
	
		return $this->custom_db->insert_record('flight_booking_meal_details', $data);
	}
	
	/**
	 * Save Seat Information
	 * @param unknown_type $passenger_fk
	 * @param unknown_type $from_airport_code
	 * @param unknown_type $to_airport_code
	 * @param unknown_type $description
	 * @param unknown_type $price
	 */
	public function save_passenger_seat_info(
		int $passenger_fk,
		string $from_airport_code,
		string $to_airport_code,
		string $description,
		float $price,
		string $code,
		string $type = 'dynamic',
		string $airline_code = '',
		string $flight_number = ''
	): void {
		$data = [
			'passenger_fk' => $passenger_fk,
			'from_airport_code' => $from_airport_code,
			'to_airport_code' => $to_airport_code,
			'description' => $description,
			'price' => $price,
			'code' => $code,
			'type' => $type,
			'airline_code' => $airline_code,
			'flight_number' => $flight_number
		];
		$this->custom_db->insert_record('flight_booking_seat_details', $data);
	}
	
	/**
	 * SAve search data for future use - Analytics
	 * @param array $params
	 */
	public function save_search_data(array $search_data, string $type): void
	{
		$data = [
			'domain_origin' => get_domain_auth_id(),
			'search_type' => $type,
			'created_by_id' => intval($this->entity_user_id ?? 0),
			'created_datetime' => date('Y-m-d H:i:s'),
			'total_pax' => $search_data['adult'] + $search_data['child'] + $search_data['infant'],
		];

		$data['trip_type'] = in_array($search_data['trip_type'], ['oneway', 'circle']) ? $search_data['trip_type'] : 'multistop';

		$from_location = $data['trip_type'] == 'multistop' ? $search_data['from'] : [$search_data['from']];
		$to_location = $data['trip_type'] == 'multistop' ? $search_data['to'] : [$search_data['to']];
		$departure = $data['trip_type'] == 'multistop' ? $search_data['depature'] : [$search_data['depature']];

		foreach ($from_location as $i => $from_loc) {
			$temp_location = explode('(', $from_loc);
			$data['from_location'] = trim($temp_location[0]);
			$data['from_code'] = isset($temp_location[1]) ? trim($temp_location[1], '() ') : '';

			$temp_location = explode('(', $to_location[$i]);
			$data['to_location'] = trim($temp_location[0]);
			$data['to_code'] = isset($temp_location[1]) ? trim($temp_location[1], '() ') : '';

			$data['journey_date'] = date('Y-m-d', strtotime($departure[$i]));

			$this->custom_db->insert_record('search_flight_history', $data);
		}
	}


	/**
	 * Balu A
	 * Returns Airport timezone offset
	 * @param $airport_code
	 */
	public function get_airport_timezone_offset(string $airport_code, string $journey_date): string
	{
		$query = 'SELECT timezonename, CountryCode FROM flight_airport_list WHERE airport_code = ?';
		$result = $this->db->query($query, [$airport_code])->result_array();

		if (!empty($result[0]['timezonename'])) {
			$timezone = new DateTimeZone($result[0]['timezonename']);
			$date_time = new DateTime($journey_date, $timezone);
			return substr($date_time->format(DateTime::ATOM), -6);
		}

		if ($result[0]['CountryCode'] == 'IN') {
			return '+5.30';
		}

		$journey_month = date('m', strtotime($journey_date));
		$query = '
        SELECT FAT.timezone_offset
        FROM flight_airport_list FAL
        JOIN flight_airport_timezone_offset FAT ON FAT.flight_airport_list_fk = FAL.origin
        WHERE FAL.airport_code = ? AND (FAT.start_month <= ? AND FAT.end_month >= ?)
        ORDER BY
            CASE
                WHEN FAT.start_month = ? THEN 1
                WHEN FAT.end_month = ? THEN 2
                ELSE 3
            END
        LIMIT 1
    ';
		$offset_result = $this->db->query($query, [$airport_code, $journey_month, $journey_month, $journey_month, $journey_month])->result_array();

		return $offset_result[0]['timezone_offset'] ?? '+0.00';
	}


	/**Promo code checking ***/
	public function get_promo(string $promo): CI_DB_result
	{
		$query = "SELECT * FROM promo_code_list WHERE promo_code = ?";
		return $this->db->query($query, [$promo]);
	}

	/**
	 Balu A
	 * Returns Passenger Ticket Details based on the following parameteres
	 * @param $app_reference
	 * @param $passenger_origin
	 * @param $passenger_booking_status
	 */
	public function get_passenger_ticket_info(string $app_reference, int $passenger_origin, string $passenger_booking_status = ''): array
	{
		$response = [
			'status' => FAILURE_STATUS,
			'data' => [],
		];

		$bd_query = 'SELECT BD.*, DL.domain_name, DL.origin as domain_id, CC.country as domain_base_currency
        FROM flight_booking_details AS BD
        JOIN domain_list AS DL ON DL.origin = BD.domain_origin
        JOIN currency_converter CC ON CC.id = DL.currency_converter_fk
        WHERE BD.app_reference LIKE ?';

		$cd_query = 'SELECT FBTD.book_id, FBTD.pnr, FBTD.sequence_number, CD.*, FPTI.TicketId, FPTI.TicketNumber, FPTI.IssueDate, FPTI.Fare, FPTI.SegmentAdditionalInfo
        FROM flight_booking_passenger_details AS CD
        JOIN flight_booking_transaction_details FBTD ON CD.flight_booking_transaction_details_fk = FBTD.origin
        LEFT JOIN flight_passenger_ticket_info FPTI ON CD.origin = FPTI.passenger_fk
        WHERE CD.app_reference = ? AND CD.origin = ? AND CD.status = ?';

		$cancellation_details_query = 'SELECT FCD.*
        FROM flight_booking_passenger_details AS CD
        LEFT JOIN flight_passenger_ticket_info FPTI ON CD.origin = FPTI.passenger_fk
        LEFT JOIN flight_cancellation_details AS FCD ON FCD.passenger_fk = CD.origin
        WHERE CD.app_reference = ? AND CD.origin = ? AND CD.status = ?';

		$response['data']['booking_details'] = $this->db->query($bd_query, [$app_reference])->result_array();
		$response['data']['booking_customer_details'] = $this->db->query($cd_query, [$app_reference, $passenger_origin, $passenger_booking_status])->result_array();
		$response['data']['cancellation_details'] = $this->db->query($cancellation_details_query, [$app_reference, $passenger_origin, $passenger_booking_status])->result_array();

		if (!empty($response['data']['booking_details']) && !empty($response['data']['booking_customer_details'])) {
			$response['status'] = SUCCESS_STATUS;
		}

		return $response;
	}

	/**
	 * Updates the flight booking transaction and passenger status 
	 * @param unknown_type $app_reference
	 * @param unknown_type $flight_booking_transaction_details_origin
	 */
	public function update_flight_booking_transaction_failure_status(int $flight_booking_transaction_details_origin): void
	{
		$failed_status = 'BOOKING_FAILED';

		$transaction_failure_data = ['status' => $failed_status];
		$transaction_failure_condition = ['origin' => $flight_booking_transaction_details_origin];
		$GLOBALS['CI']->custom_db->update_record('flight_booking_transaction_details', $transaction_failure_data, $transaction_failure_condition);

		$passenger_failure_data = ['status' => $failed_status];
		$passenger_failure_condition = ['flight_booking_transaction_details_fk' => $flight_booking_transaction_details_origin];
		$GLOBALS['CI']->custom_db->update_record('flight_booking_passenger_details', $passenger_failure_data, $passenger_failure_condition);
	}

	/**
	 * Extraservices(Baggage,Meal and Seats) Price
	 * @param unknown_type $app_reference
	 */
	public function get_extra_services_total_price(string $app_reference): float
	{
		$baggage_total_price = $this->get_baggage_total_price($app_reference);
		$meal_total_price = $this->get_meal_total_price($app_reference);
		$seat_total_price = $this->get_seat_total_price($app_reference);

		return round($baggage_total_price + $meal_total_price + $seat_total_price, 2);
	}

	/**
	 * 
	 * Returns Baggage Total Price
	 * @param unknown_type $app_reference
	 */
	public function get_baggage_total_price(string $app_reference): float
	{
		$query = '
        SELECT SUM(FBG.price) AS baggage_total_price
        FROM flight_booking_passenger_details FP
        LEFT JOIN flight_booking_baggage_details FBG ON FP.origin = FBG.passenger_fk
        WHERE FP.app_reference = ?
        GROUP BY FP.app_reference
    ';
		$data = $this->db->query($query, [$app_reference])->row_array();
		return floatval($data['baggage_total_price'] ?? 0);
	}

	/**
	 * 
	 * Returns Meal Total Price
	 * @param unknown_type $app_reference
	 */
	public function get_meal_total_price(string $app_reference): float
	{
		$query = '
        SELECT SUM(FML.price) AS meal_total_price
        FROM flight_booking_passenger_details FP
        LEFT JOIN flight_booking_meal_details FML ON FP.origin = FML.passenger_fk
        WHERE FP.app_reference = ?
        GROUP BY FP.app_reference
    ';
		$data = $this->db->query($query, [$app_reference])->row_array();
		return floatval($data['meal_total_price'] ?? 0);
	}

	/**
	 * 
	 * Returns Seat Total Price
	 * @param unknown_type $app_reference
	 */
	public function get_seat_total_price(string $app_reference): float
	{
		$query = '
        SELECT SUM(FST.price) AS seat_total_price
        FROM flight_booking_passenger_details FP
        LEFT JOIN flight_booking_seat_details FST ON FP.origin = FST.passenger_fk
        WHERE FP.app_reference = ?
        GROUP BY FP.app_reference
    ';
		$data = $this->db->query($query, [$app_reference])->row_array();
		return floatval($data['seat_total_price'] ?? 0);
	}

	/**
	 * Extraservices(Baggage,Meal and Seats) Price
	 * @param unknown_type $app_reference
	 */
	public function add_extra_service_price_to_published_fare(string $app_reference): void
	{
		$transaction_data = $this->db->query("SELECT * FROM flight_booking_transaction_details WHERE app_reference = " . $this->db->escape($app_reference) . " ORDER BY origin ASC")->result_array();

		foreach ($transaction_data as $transaction) {
			$extra_price = $this->transaction_wise_extra_service_total_price((int)$transaction['origin']);
			$update_data = ['total_fare' => $transaction['total_fare'] + $extra_price];
			$this->custom_db->update_record('flight_booking_transaction_details', $update_data, ['origin' => $transaction['origin']]);
		}
	}

	/**
	 * Transaction-wise extra service total price
	 * @param unknown_type $transaction_origin
	 */
	public function transaction_wise_extra_service_total_price(int $transaction_origin): float
	{
		$baggage_price = $this->db->query("SELECT SUM(FBG.price) AS baggage_total_price FROM flight_booking_passenger_details FP LEFT JOIN flight_booking_baggage_details FBG ON FP.origin = FBG.passenger_fk WHERE FP.flight_booking_transaction_details_fk = $transaction_origin GROUP BY FP.flight_booking_transaction_details_fk")->row_array();
		$meal_price = $this->db->query("SELECT SUM(FML.price) AS meal_total_price FROM flight_booking_passenger_details FP LEFT JOIN flight_booking_meal_details FML ON FP.origin = FML.passenger_fk WHERE FP.flight_booking_transaction_details_fk = $transaction_origin GROUP BY FP.flight_booking_transaction_details_fk")->row_array();
		$seat_price = $this->db->query("SELECT SUM(FST.price) AS seat_total_price FROM flight_booking_passenger_details FP LEFT JOIN flight_booking_seat_details FST ON FP.origin = FST.passenger_fk WHERE FP.flight_booking_transaction_details_fk = $transaction_origin GROUP BY FP.flight_booking_transaction_details_fk")->row_array();

		return floatval(($baggage_price['baggage_total_price'] ?? 0) + ($meal_price['meal_total_price'] ?? 0) + ($seat_price['seat_total_price'] ?? 0));
	}

	public function flight_top_destinations(): array
	{
		$query = "SELECT * FROM bus_stations WHERE top_destination = " . ACTIVE;
		return $this->db->query($query)->result_array();
	}

	public function save_insurance_details(array $response): void
	{
		$data = [
			'app_reference'     => $response['app_reference'],
			'status'            => $response['status'],
			'response'          => json_encode($response['response']),
			'travel_date'       => $response['travel_date'],
			'created_datetime'  => date('Y-m-d H:i:s'),
		];
		$this->custom_db->insert_record('insurance_details', $data);
	}

	public function get_county_code(string $country): ?string
	{
		$row = $this->db->query("SELECT iso_country_code FROM country_list WHERE en_name = " . $this->db->escape($country))->row_array();
		return $row['iso_country_code'] ?? null;
	}


	public function update_pax_details(array $data, int $id): bool
	{
		return $this->custom_db->update_record("flight_booking_passenger_details", $data, ["origin" => $id]);
	}


	public function update_booking_details(array $data, int $id): bool
	{
		return $this->custom_db->update_record("flight_booking_details", $data, ["origin" => $id]);
	}

	public function get_airport_listnew(string $search_chars): mixed
	{
		$raw = $this->db->escape($search_chars);
		$r_search = $this->db->escape($search_chars . '%');
		$query = <<<SQL
		SELECT origin, airport_code, airport_name, airport_city, country, priority, sub_priority FROM (
    	SELECT a.*, 2 AS RANK FROM flight_airport_list a WHERE airport_code IN (
        SELECT CASE WHEN priority = '1' THEN airport_city ELSE airport_code END
        FROM flight_airport_list WHERE airport_code = $raw
    )
    UNION
    SELECT b.*, 1 AS RANK FROM flight_airport_list b WHERE airport_city IN (
        SELECT CASE WHEN priority = '1' THEN airport_city ELSE airport_code END
        FROM flight_airport_list WHERE airport_code = $raw
    )
    UNION
    SELECT c.*, 3 AS RANK FROM flight_airport_list c WHERE airport_city IN (
        SELECT airport_city FROM flight_airport_list WHERE UPPER(airport_city) LIKE $r_search AND origin NOT IN (
            SELECT origin FROM flight_airport_list WHERE airport_city IN (
                SELECT CASE WHEN priority = '1' THEN airport_city ELSE airport_code END
                FROM flight_airport_list WHERE airport_code = $raw
            )
            GROUP BY 1
        )
        GROUP BY 1
    )
) AS flightOrder
ORDER BY RANK, airport_city, priority
LIMIT 10
SQL;

		return $query ? $this->db->query($query) : false;
	}

	public function get_gst_details(): array
	{
		$query = 'SELECT gst.*, MCL.name AS name FROM gst_master gst
              LEFT JOIN meta_course_list MCL ON MCL.origin = gst.meta_course_list_fk
              WHERE MCL.course_id = ' . $this->db->escape(META_AIRLINE_COURSE);

		$result = $this->db->query($query);

		if ($result->num_rows() > 0) {
			return ['status' => QUERY_SUCCESS, 'data' => $result->result_array()];
		}

		return ['status' => QUERY_FAILURE];
	}

}