<?php
require_once 'transaction.php';
/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Car Model
 * @author     Anitha.G J<anitha.g.provab@gmail.com>
 * @version    V1
 */
Class Car_Model extends Transaction
{
	/*
	 *
	 * Get Airport List
	 *
	 */

	 public function get_airport_list(string $search_chars): CI_DB_result
	 {
		 $raw_search_chars = $this->db->escape($search_chars);
		 $r_search_chars = $this->db->escape($search_chars . '%');
		 $search_chars = $this->db->escape('%' . $search_chars . '%');
	 
		 $query = 'SELECT * FROM Car_Airport 
				   WHERE Airport_Name_EN LIKE ' . $search_chars . '
				   OR Airport_IATA LIKE ' . $search_chars . ' 
				   OR Country_ISO LIKE ' . $search_chars . '
				   ORDER BY top_destination DESC,
				   CASE
					 WHEN Airport_IATA LIKE ' . $raw_search_chars . ' THEN 1
					 WHEN Airport_Name_EN LIKE ' . $raw_search_chars . ' THEN 2
					 WHEN Country_ISO LIKE ' . $raw_search_chars . ' THEN 3
					 WHEN Airport_IATA LIKE ' . $r_search_chars . ' THEN 4
					 WHEN Airport_Name_EN LIKE ' . $r_search_chars . ' THEN 5
					 WHEN Country_ISO LIKE ' . $r_search_chars . ' THEN 6
					 WHEN Airport_IATA LIKE ' . $search_chars . ' THEN 7
					 WHEN Airport_Name_EN LIKE ' . $search_chars . ' THEN 8
					 WHEN Country_ISO LIKE ' . $search_chars . ' THEN 9
					 ELSE 10 
				   END 
				   LIMIT 0, 20';
	 
		 return $this->db->query($query);
	 }
	 
	 public function get_city_list(string $search_chars): CI_DB_result
	 {
		 $search_chars = $this->db->escape($search_chars . '%');
	 
		 $query = 'SELECT Country_ISO, origin, Country_Name_EN, City_ID, City_Name_EN, City_IATA as Airport_IATA 
				   FROM Car_City 
				   WHERE (City_Name_EN LIKE ' . $search_chars . ' 
				   OR Country_Name_EN LIKE ' . $search_chars . ') 
				   AND City_IATA != "" 
				   LIMIT 0, 10';
	 
		 return $this->db->query($query);
	 }
	 
	 public function save_search_data(array $search_data, string $type): void
	 {
		 $data = [
			 'domain_origin'     => get_domain_auth_id(),
			 'search_type'       => $type,
			 'created_by_id'     => intval($this->entity_user_id ?? 0),
			 'created_datetime'  => date('Y-m-d H:i:s'),
			 'from_location'     => $search_data['car_from'],
			 'from_loc_id'       => $search_data['from_loc_id'],
			 'from_loc_code'     => $search_data['car_from_loc_code'],
			 'to_location'       => $search_data['car_to'],
			 'to_loc_id'         => $search_data['to_loc_id'],
			 'to_loc_code'       => $search_data['car_to_loc_code'],
			 'driver_age'        => $search_data['driver_age'],
			 'depature'          => date('Y-m-d H:i', strtotime(date('d-m-Y', strtotime($search_data['depature'])) . ' ' . $search_data['depature_time'])),
			 'return'            => date('Y-m-d H:i', strtotime(date('d-m-Y', strtotime($search_data['return'])) . ' ' . $search_data['return_time']))
		 ];
	 
		 $this->custom_db->insert_record('search_car_history', $data);
	 }
	 
	 public function get_safe_search_data(int $search_id): array|null
	 {
		 $search_data = $this->get_search_data($search_id);
	 
		 if ($search_data != false) {
			 $temp_search_data = json_decode($search_data['search_data'], true);
			 $clean_search = $this->clean_search_data($temp_search_data);
	 
			 return [
				 'status' => $clean_search['status'],
				 'data'   => $clean_search['data']
			 ];
		 }
	 
		 return null;
	 }
	 
	 public function get_search_data(int $search_id): array|false
	 {
		 $search_data = $this->custom_db->single_table_records('search_history', '*', [
			 'search_type' => META_CAR_COURSE,
			 'origin' => $search_id
		 ]);
	 
		 return $search_data['status'] == true ? $search_data['data'][0] : false;
	 }
	 
	 public function clean_search_data(array $temp_search_data): array
	 {
		 return [
			 'data'   => $temp_search_data,
			 'status' => true
		 ];
	 }
	 
	 /**
	 * get all the booking source which are active for current domain
	 */
	public function car_booking_source(): array
	{
		$query = 'SELECT BS.source_id, BS.origin 
              FROM meta_course_list AS MCL, booking_source AS BS, activity_source_map AS ASM 
              WHERE MCL.origin = ASM.meta_course_list_fk 
              AND ASM.booking_source_fk = BS.origin 
              AND MCL.course_id = ' . $this->db->escape(META_CAR_COURSE) . ' 
              AND BS.booking_engine_status = ' . ACTIVE . ' 
              AND MCL.status = ' . ACTIVE . ' 
              AND ASM.status = "active"';

		return $this->db->query($query)->result_array();
	}

	public function vehiclecategory(): array
	{
		$query = 'SELECT vehiclecategory_id, vehiclecategory_name FROM car_vehiclecategory';
		return $this->db->query($query)->result_array();
	}

	public function vehiclesize(): array
	{
		$query = 'SELECT vehiclesize_id, vehiclesize_name FROM car_vehiclesize';
		return $this->db->query($query)->result_array();
	}

	public function save_booking_details(
		int $domain_origin,
		string $status,
		string $app_reference,
		string $booking_source,
		string $currency,
		string $phone_number,
		string $email,
		string $payment_mode,
		int $created_by_id,
		float $currency_conversion_rate,
		float $total_fare,
		string $booking_id,
		string $booking_reference,
		string $supplier_identifier,
		string $car_name,
		string $car_supplier_name,
		string $car_model,
		string $car_from_date,
		string $car_to_date,
		string $pickup_time,
		string $drop_time,
		string $car_pickup_lcation,
		string $car_drop_location,
		string $car_drop_address,
		string $car_pickup_address,
		string $final_cancel_date,
		string $transfer_type,
		float $oneway_fee
	): array {
		$data = compact(
			'domain_origin',
			'status',
			'app_reference',
			'booking_source',
			'booking_id',
			'booking_reference',
			'total_fare',
			'currency',
			'car_name',
			'car_supplier_name',
			'car_model',
			'phone_number',
			'email',
			'car_to_date',
			'car_from_date',
			'payment_mode',
			'supplier_identifier',
			'pickup_time',
			'drop_time',
			'car_pickup_lcation',
			'car_drop_location',
			'car_drop_address',
			'car_pickup_address',
			'final_cancel_date',
			'transfer_type',
			'oneway_fee',
			'created_by_id',
			'currency_conversion_rate'
		);
		$data['created_datetime'] = date('Y-m-d H:i:s');

		return $this->custom_db->insert_record('car_booking_details', $data);
	}

	public function save_booking_itinerary_details(
		array $itinerary_data,
		string $priced_equip,
		string $priced_coverage,
		string $cancellation_poicy,
		string $attributes1,
		float $total_fare,
		float $admin_markup,
		float $agent_markup,
		string $status
	): array {
		$data = [
			'status' => $status,
			'app_reference' => $itinerary_data['app_reference'],
			'car_from_date' => $itinerary_data['car_from_date'],
			'car_to_date' => $itinerary_data['car_to_date'],
			'pickup_time' => $itinerary_data['pickup_time'],
			'drop_time' => $itinerary_data['drop_time'],
			'car_pickup_loc' => $itinerary_data['car_pickup_location'],
			'car_drop_loc' => $itinerary_data['car_drop_location'],
			'car_pickup_add' => $itinerary_data['car_pickup_address'],
			'car_drop_add' => $itinerary_data['car_drop_address'],
			'car_name' => $itinerary_data['car_name'],
			'pricture_url' => $itinerary_data['car_picture_url'],
			'priced_equip' => $priced_equip,
			'priced_coverage' => $priced_coverage,
			'cancellation_poicy' => $cancellation_poicy,
			'admin_markup' => $admin_markup,
			'agent_markup' => $agent_markup,
			'total_fare' => $total_fare,
			'attributes' => $attributes1,
		];

		return $this->custom_db->insert_record('car_booking_itinerary_details', $data);
	}

	public function save_booking_pax_details(
		array $pax_details_data,
		string $country_code,
		string $country_name,
		string $city,
		string $pincode,
		string $adress1,
		string $adress2,
		string $status
	): array {
		$data = [
			'status' => $status,
			'app_reference' => $pax_details_data['app_reference'],
			'title' => $pax_details_data['title'],
			'first_name' => $pax_details_data['first_name'],
			'last_name' => $pax_details_data['last_name'],
			'phone' => $pax_details_data['phone_number'],
			'email' => $pax_details_data['email'],
			'date_of_birth' => $pax_details_data['dob'],
			'country_code' => $country_code,
			'country_name' => $country_name,
			'city' => $city,
			'pincode' => $pincode,
			'adress1' => $adress1,
			'adress2' => $adress2,
		];

		return $this->custom_db->insert_record('car_booking_pax_details', $data);
	}

	public function get_booking_details(string $app_reference, string $booking_source, string $booking_status = ''): array
	{
		$this->load->library('booking_data_formatter');
		$response = [
			'status' => SUCCESS_STATUS,
			'data' => [
				'booking_details' => [],
				'booking_itinerary_details' => [],
				'booking_pax_details' => [],
				'booking_extra_details' => [],
				'cancellation_details' => [],
			],
		];

		$bd_query = 'SELECT * FROM car_booking_details AS BD WHERE BD.app_reference = ' . $this->db->escape(trim($app_reference));
		if (!empty($booking_source)) {
			$bd_query .= ' AND BD.booking_source = ' . $this->db->escape($booking_source);
		}
		if (!empty($booking_status)) {
			$bd_query .= ' AND BD.status = ' . $this->db->escape($booking_status);
		}

		$booking_details = $this->db->query($bd_query)->result_array();
		$response['data']['booking_details'] = $booking_details;

		$app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);

		if (!empty($app_reference_ids)) {
			$id_query = 'SELECT * FROM car_booking_itinerary_details WHERE app_reference IN (' . $app_reference_ids . ')';
			$cd_query = 'SELECT * FROM car_booking_pax_details WHERE app_reference IN (' . $app_reference_ids . ')';
			$ex_query = 'SELECT * FROM car_booking_extra_details WHERE app_reference IN (' . $app_reference_ids . ')';
			$cancellation_query = 'SELECT * FROM car_cancellation_details WHERE app_reference IN (' . $app_reference_ids . ')';

			$response['data']['booking_itinerary_details'] = $this->db->query($id_query)->result_array();
			$response['data']['booking_pax_details'] = $this->db->query($cd_query)->result_array();
			$response['data']['booking_extra_details'] = $this->db->query($ex_query)->result_array();
			$response['data']['cancellation_details'] = $this->db->query($cancellation_query)->result_array();
		}

		return $response;
	}
	
	/**
	 * Read Individual booking details - dont use it to generate table
	 * @param $app_reference
	 * @param $booking_source
	 * @param $booking_status
	 */
	// function booking_guest_user($app_reference, $booking_source='', $booking_status='')
	// {
	// 	//need to work on this
	// }
	 function save_booking_extra_details($extra_service_details=array()): array{
		$data = [];
        foreach($extra_service_details as $service){
            $data['app_reference'] = $service['app_reference'];
            $data['amount'] = $service['amount'];
            $data['description'] = $service['description'];
            $data['equiptype'] = $service['equiptype'];
            $data['qunatity'] = $service['qunatity'];
            $status = $this->custom_db->insert_record('car_booking_extra_details', $data);
        }
        return $status;
      }
    /**
	 * return booking list
	 */
	function booking($condition=array(), $count=false, $offset=0, $limit=100000000000)
	{

	$condition = $this->custom_db->get_custom_condition($condition);
		
		if(isset($condition) == true)
		{
			$offset = 0;
		}else{
			$offset = $offset;
		}

		//BT, CD, ID
		if ($count) {
			$query = 'SELECT COUNT(DISTINCT(BD.app_reference)) AS total_records
                  FROM car_booking_details BD
                  JOIN car_booking_itinerary_details AS HBID ON BD.app_reference = HBID.app_reference
                  JOIN payment_option_list AS POL ON BD.payment_mode = POL.payment_category_code
                  WHERE BD.domain_origin = ' . get_domain_auth_id() .
				' AND BD.created_by_id = ' . $GLOBALS['CI']->entity_user_id . $condition;
			$data = $this->db->query($query)->row_array();
			return (int) $data['total_records'];
		}

		$this->load->library('booking_data_formatter');
		$response = [
			'status' => SUCCESS_STATUS,
			'data' => []
		];

		$bd_query = 'SELECT * FROM car_booking_details AS BD
                 WHERE BD.domain_origin = ' . get_domain_auth_id() .
			' AND BD.created_by_id = ' . $GLOBALS['CI']->entity_user_id . $condition .
			' ORDER BY BD.origin DESC LIMIT ' . $offset . ', ' . $limit;

		$booking_details = $this->db->query($bd_query)->result_array();
		$app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);

		$response['data']['booking_details'] = $booking_details;
		$response['data']['booking_itinerary_details'] = [];
		$response['data']['booking_pax_details'] = [];
		$response['data']['booking_extra_details'] = [];
		$response['data']['cancellation_details'] = [];

		if (!empty($app_reference_ids)) {
			$queries = [
				'booking_itinerary_details' => 'SELECT * FROM car_booking_itinerary_details WHERE app_reference IN (' . $app_reference_ids . ')',
				'booking_pax_details'       => 'SELECT * FROM car_booking_pax_details WHERE app_reference IN (' . $app_reference_ids . ')',
				'booking_extra_details'     => 'SELECT * FROM car_booking_extra_details WHERE app_reference IN (' . $app_reference_ids . ')',
				'cancellation_details'      => 'SELECT * FROM car_cancellation_details WHERE app_reference IN (' . $app_reference_ids . ')'
			];

			foreach ($queries as $key => $sql) {
				$response['data'][$key] = $this->db->query($sql)->result_array();
			}
		}

		return $response;
	}

	public function update_cancellation_details(string $AppReference, array $cancellation_details): void
	{
		$AppReference = trim($AppReference);
		$booking_status = 'BOOKING_CANCELLED';

		$this->update_cancellation_refund_details($AppReference, $cancellation_details);

		$this->custom_db->update_record('car_booking_details', [
			'status' => $booking_status
		], ['app_reference' => $AppReference]);

		$this->custom_db->update_record('car_booking_itinerary_details', [
			'status' => $booking_status
		], ['app_reference' => $AppReference]);
	}

	public function update_cancellation_refund_details(string $AppReference, array $cancellation_details): array
	{	
		$car_cancellation_details = [
			'app_reference' => $AppReference,
			'ChangeRequestId' => $cancellation_details['ChangeRequestId'],
			'ChangeRequestStatus' => $cancellation_details['ChangeRequestStatus'],
			'status_description' => $cancellation_details['StatusDescription'],
			'API_RefundedAmount' => $cancellation_details['RefundedAmount'] ?? null,
			'API_CancellationCharge' => $cancellation_details['CancellationCharge'] ?? null,
		];

		if ($cancellation_details['ChangeRequestStatus'] == 3) {
			$car_cancellation_details['cancellation_processed_on'] = date('Y-m-d H:i:s');
		}

		$existing = $this->custom_db->single_table_records(
			'car_cancellation_details',
			'*',
			['app_reference' => $AppReference]
		);

		if (!empty($existing['status'])) {
			unset($car_cancellation_details['app_reference']);
			$this->custom_db->update_record('car_cancellation_details', $car_cancellation_details, [
				'app_reference' => $AppReference
			]);
		}

		$car_cancellation_details['created_by_id'] = (int)($this->entity_user_id ?? 0);
		$car_cancellation_details['created_datetime'] = date('Y-m-d H:i:s');
		$car_cancellation_details['cancellation_requested_on'] = date('Y-m-d H:i:s');
		$this->custom_db->insert_record('car_cancellation_details', $car_cancellation_details);
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

}