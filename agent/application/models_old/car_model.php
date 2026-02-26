<?php
declare(strict_types=1);

require_once 'transaction.php';

/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Car Model
 * @author     Anitha.G J <anitha.g.provab@gmail.com>
 * @version    V1
 */
class Car_Model extends Transaction
{
    public function get_airport_list(string $search_chars): CI_DB_result
    {
        $raw_search_chars = $this->db->escape($search_chars);
		$r_search_chars = $this->db->escape($search_chars.'%');
		$search_chars = $this->db->escape('%'.$search_chars.'%');
		$query = 'Select * from Car_Airport where Airport_Name_EN like '.$search_chars.'
		OR Airport_IATA like '.$search_chars.' OR Country_ISO like '.$search_chars.'
		ORDER BY top_destination DESC,
		CASE
			WHEN	Airport_IATA	LIKE	'.$raw_search_chars.'	THEN 1
			WHEN	Airport_Name_EN	LIKE	'.$raw_search_chars.'	THEN 2
			WHEN	Country_ISO		LIKE	'.$raw_search_chars.'	THEN 3

			WHEN	Airport_IATA	LIKE	'.$r_search_chars.'	THEN 4
			WHEN	Airport_Name_EN	LIKE	'.$r_search_chars.'	THEN 5
			WHEN	Country_ISO		LIKE	'.$r_search_chars.'	THEN 6

			WHEN	Airport_IATA	LIKE	'.$search_chars.'	THEN 7
			WHEN	Airport_Name_EN	LIKE	'.$search_chars.'	THEN 8
			WHEN	Country_ISO		LIKE	'.$search_chars.'	THEN 9
			ELSE 10 END
		LIMIT 0, 20';
		// echo $query;exit;
		return $this->db->query($query);
    }

    public function get_city_list(string $search_chars): CI_DB_result
    {
        $search_chars = $this->db->escape(''.$search_chars.'%');
		$query = 'Select Country_ISO, origin, Country_Name_EN, City_ID, City_Name_EN, City_IATA as Airport_IATA from Car_City where City_Name_EN like '.$search_chars.'
		OR Country_Name_EN like '.$search_chars.' AND City_IATA !="" LIMIT 0, 10';

        return $this->db->query($query);
    }

    public function save_search_data(array $search_data, string $type): void
    {
        $data = [
            'domain_origin'     => get_domain_auth_id(),
            'search_type'       => $type,
            'created_by_id'     => intval($this->entity_user_id ?? 0),
            'created_datetime'  => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'from_location'     => $search_data['car_from'] ?? '',
            'from_loc_id'       => $search_data['from_loc_id'] ?? '',
            'from_loc_code'     => $search_data['car_from_loc_code'] ?? '',
            'to_location'       => $search_data['car_to'] ?? '',
            'to_loc_id'         => $search_data['to_loc_id'] ?? '',
            'to_loc_code'       => $search_data['car_to_loc_code'] ?? '',
            'driver_age'        => $search_data['driver_age'] ?? null,
        ];

        $departureStr = ($search_data['depature'] ?? '') . ' ' . ($search_data['depature_time'] ?? '');
        $returnStr = ($search_data['return'] ?? '') . ' ' . ($search_data['return_time'] ?? '');

        $departure = DateTimeImmutable::createFromFormat('d-m-Y H:i', date('d-m-Y', strtotime($search_data['depature'] ?? '')) . ' ' . ($search_data['depature_time'] ?? '00:00'));
        $return = DateTimeImmutable::createFromFormat('d-m-Y H:i', date('d-m-Y', strtotime($search_data['return'] ?? '')) . ' ' . ($search_data['return_time'] ?? '00:00'));

        $data['depature'] = $departure?->format('Y-m-d H:i') ?? null;
        $data['return'] = $return?->format('Y-m-d H:i') ?? null;

        $this->custom_db->insert_record('search_car_history', $data);
    }

    public function get_safe_search_data(int|string $search_id): array
    {
        $search_data = $this->get_search_data($search_id);
        if ($search_data == false) {
            return ['status' => false, 'data' => []];
        }

        $temp_search_data = json_decode($search_data['search_data'] ?? '{}', true);
        $clean_search = $this->clean_search_data($temp_search_data);
        return [
            'status' => $clean_search['status'],
            'data' => $clean_search['data'],
        ];
    }

    public function get_search_data(int|string $search_id): array|false
    {
        $search_data = $this->custom_db->single_table_records(
            'search_history',
            '*',
            ['search_type' => META_CAR_COURSE, 'origin' => $search_id]
        );

        return $search_data['status'] == true ? $search_data['data'][0] : false;
    }

    public function clean_search_data(array $temp_search_data): array
    {
        // Future validation can be done here
        return ['data' => $temp_search_data, 'status' => true];
    }

    public function car_booking_source(): array
    {
       $query = 'select BS.source_id, BS.origin from meta_course_list AS MCL, booking_source AS BS, activity_source_map AS ASM WHERE
		MCL.origin=ASM.meta_course_list_fk and ASM.booking_source_fk=BS.origin and MCL.course_id='.$this->db->escape(META_CAR_COURSE).'
		and BS.booking_engine_status='.ACTIVE.' AND MCL.status='.ACTIVE.' AND ASM.status="active"';
		return $this->db->query($query)->result_array();
    }

    public function vehiclecategory(): array
    {
        $sql = 'SELECT vehiclecategory_id, vehiclecategory_name FROM car_vehiclecategory';
        return $this->db->query($sql)->result_array();
    }

    public function vehiclesize(): array
    {
        $sql = 'SELECT vehiclesize_id, vehiclesize_name FROM car_vehiclesize';
        return $this->db->query($sql)->result_array();
    }

    public function save_booking_details(
        int|string $domain_origin,
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
        string $car_pickup_location,
        string $car_drop_location,
        string $car_drop_address,
        string $car_pickup_address,
        string $final_cancel_date,
        string $transfer_type,
        float $oneway_fee
    ): array {
        $data = [
            'domain_origin' => $domain_origin,
            'status' => $status,
            'app_reference' => $app_reference,
            'booking_source' => $booking_source,
            'booking_id' => $booking_id,
            'booking_reference' => $booking_reference,
            'total_fare' => $total_fare,
            'currency' => $currency,
            'car_name' => $car_name,
            'car_supplier_name' => $car_supplier_name,
            'car_model' => $car_model,
            'phone_number' => $phone_number,
            'email' => $email,
            'car_to_date' => $car_to_date,
            'car_from_date' => $car_from_date,
            'payment_mode' => $payment_mode,
            'supplier_identifier' => $supplier_identifier,
            'pickup_time' => $pickup_time,
            'drop_time' => $drop_time,
            'car_pickup_lcation' => $car_pickup_location,
            'car_drop_location' => $car_drop_location,
            'car_drop_address' => $car_drop_address,
            'car_pickup_address' => $car_pickup_address,
            'final_cancel_date' => $final_cancel_date,
            'transfer_type' => $transfer_type,
            'oneway_fee' => $oneway_fee,
            'created_by_id' => $created_by_id,
            'created_datetime' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'currency_conversion_rate' => $currency_conversion_rate,
        ];

        return $this->custom_db->insert_record('car_booking_details', $data);
    }
    public function save_booking_itinerary_details(
	    string $appReference,
	    string $carFromDate,
	    string $carToDate,
	    string $pickupTime,
	    string $dropTime,
	    string $carPickupLocation,
	    string $carDropLocation,
	    string $carPickupAddress,
	    string $carDropAddress,
	    string $carName,
	    string $pictureUrl,
	    string $pricedEquip,
	    string $pricedCoverage,
	    string $cancellationPolicy,
	    string $attributes,
	    float $totalFare,
	    float $adminMarkup,
	    float $agentMarkup,
	    string $status
		): array {
	    $data = [
	        'status' => $status,
	        'app_reference' => $appReference,
	        'car_from_date' => $carFromDate,
	        'car_to_date' => $carToDate,
	        'pickup_time' => $pickupTime,
	        'drop_time' => $dropTime,
	        'car_pickup_loc' => $carPickupLocation,
	        'car_drop_loc' => $carDropLocation,
	        'car_pickup_add' => $carPickupAddress,
	        'car_drop_add' => $carDropAddress,
	        'car_name' => $carName,
	        'pricture_url' => $pictureUrl,
	        'priced_equip' => $pricedEquip,
	        'priced_coverage' => $pricedCoverage,
	        'cancellation_poicy' => $cancellationPolicy,
	        'admin_markup' => $adminMarkup,
	        'agent_markup' => $agentMarkup,
	        'total_fare' => $totalFare,
	        'attributes' => $attributes,
	    ];

	    return $this->custom_db->insert_record('car_booking_itinerary_details', $data);
	}

	public function save_booking_pax_details(
    string $appReference,
    string $title,
    string $firstName,
    string $lastName,
    string $phone,
    string $email,
    string $dob,
    string $countryCode,
    string $countryName,
    string $city,
    string $pincode,
    string $address1,
    string $address2,
    string $status
	): array {
	    $data = [
	        'status' => $status,
	        'app_reference' => $appReference,
	        'title' => $title,
	        'first_name' => $firstName,
	        'last_name' => $lastName,
	        'phone' => $phone,
	        'email' => $email,
	        'date_of_birth' => $dob,
	        'country_code' => $countryCode,
	        'country_name' => $countryName,
	        'city' => $city,
	        'pincode' => $pincode,
	        'adress1' => $address1,
	        'adress2' => $address2,
	    ];

	    return $this->custom_db->insert_record('car_booking_pax_details', $data);
	}
	public function get_booking_details(string $app_reference, string $booking_source, string $booking_status = ''): array
	{
	    $this->load->library('booking_data_formatter');

	    $response = [
	        'status' => SUCCESS_STATUS,
	        'data' => []
	    ];

	    $booking_itinerary_details = [];
	    $booking_customer_details = [];
	    $booking_extra_details = [];
	    $cancellation_details = [];

	    $app_reference = trim($app_reference);

	    $sql = "SELECT * FROM car_booking_details AS BD WHERE BD.app_reference = ?";
	    $params = [$app_reference];

	    if ($booking_source != '') {
	        $sql .= " AND BD.booking_source = ?";
	        $params[] = $booking_source;
	    }

	    if ($booking_status != '') {
	        $sql .= " AND BD.status = ?";
	        $params[] = $booking_status;
	    }

	    $booking_details = $this->db->query($sql, $params)->result_array();

	    $app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);

	    if ($app_reference_ids != '') {
	        // Using prepared statements for safety is preferred, but
	        // assuming implode_app_reference_ids returns a safe list for IN(...)
	        $booking_itinerary_details = $this->db->query(
	            "SELECT * FROM car_booking_itinerary_details WHERE app_reference IN ($app_reference_ids)"
	        )->result_array();

	        $booking_customer_details = $this->db->query(
	            "SELECT * FROM car_booking_pax_details WHERE app_reference IN ($app_reference_ids)"
	        )->result_array();

	        $booking_extra_details = $this->db->query(
	            "SELECT * FROM car_booking_extra_details WHERE app_reference IN ($app_reference_ids)"
	        )->result_array();

	        $cancellation_details = $this->db->query(
	            "SELECT * FROM car_cancellation_details WHERE app_reference IN ($app_reference_ids)"
	        )->result_array();
	    }

	    $response['data'] = [
	        'booking_details' => $booking_details,
	        'booking_itinerary_details' => $booking_itinerary_details,
	        'booking_pax_details' => $booking_customer_details,
	        'booking_extra_details' => $booking_extra_details,
	        'cancellation_details' => $cancellation_details,
	    ];

	    return $response;
	}

	public function save_booking_extra_details(array $extra_service_details): bool|array
	{
	    $status = false;
	    foreach ($extra_service_details as $service) {
	        $data = [
	            'app_reference' => $service['app_reference'] ?? '',
	            'amount' => $service['amount'] ?? 0,
	            'description' => $service['description'] ?? '',
	            'equiptype' => $service['equiptype'] ?? '',
	            'quantity' => $service['qunatity'] ?? 0, // consider fixing this typo in your source data to 'quantity'
	        ];

	        $status = $this->custom_db->insert_record('car_booking_extra_details', $data);
	        if (!$status) {
	            return false; // early return on failure
	        }
	    }
    	return $status;
	}

	/**
	 * Return booking list
	 *
	 * @param array $condition
	 * @param bool $count
	 * @param int $offset
	 * @param int $limit
	 * @return array|int
	 */
	public function booking(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array|int
	{
	    $condition_sql = $this->custom_db->get_custom_condition($condition);

	    if ($condition_sql != '') {
	        $offset = 0;
	    }

	    if ($count) {
	        $query = "
	            SELECT COUNT(DISTINCT BD.app_reference) AS total_records
	            FROM car_booking_details BD
	            JOIN car_booking_itinerary_details HBID ON BD.app_reference = HBID.app_reference
	            JOIN payment_option_list POL ON BD.payment_mode = POL.payment_category_code
	            WHERE BD.domain_origin = ? AND BD.created_by_id = ? $condition_sql
	        ";

	        $params = [get_domain_auth_id(), $GLOBALS['CI']->entity_user_id];
	        $data = $this->db->query($query, $params)->row_array();
	        return (int)($data['total_records'] ?? 0);
	    } else {
	        $this->load->library('booking_data_formatter');

	        $response = [
	            'status' => SUCCESS_STATUS,
	            'data' => []
	        ];

	        $booking_itinerary_details = [];
	        $booking_customer_details = [];
	        $booking_extra_details = [];
	        $cancellation_details = [];

	        $sql = "
	            SELECT * FROM car_booking_details AS BD
	            WHERE BD.domain_origin = ? AND BD.created_by_id = ? $condition_sql
	            ORDER BY BD.origin DESC
	            LIMIT ?, ?
	        ";
	        $params = [get_domain_auth_id(), $GLOBALS['CI']->entity_user_id, $offset, $limit];
	        $booking_details = $this->db->query($sql, $params)->result_array();

	        $app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);

	        if ($app_reference_ids != '') {
	            $booking_itinerary_details = $this->db->query(
	                "SELECT * FROM car_booking_itinerary_details WHERE app_reference IN ($app_reference_ids)"
	            )->result_array();

	            $booking_customer_details = $this->db->query(
	                "SELECT * FROM car_booking_pax_details WHERE app_reference IN ($app_reference_ids)"
	            )->result_array();

	            $booking_extra_details = $this->db->query(
	                "SELECT * FROM car_booking_extra_details WHERE app_reference IN ($app_reference_ids)"
	            )->result_array();

	            $cancellation_details = $this->db->query(
	                "SELECT * FROM car_cancellation_details WHERE app_reference IN ($app_reference_ids)"
	            )->result_array();
	        }

	        $response['data'] = [
	            'booking_details' => $booking_details,
	            'booking_itinerary_details' => $booking_itinerary_details,
	            'booking_pax_details' => $booking_customer_details,
	            'booking_extra_details' => $booking_extra_details,
	            'cancellation_details' => $cancellation_details,
	        ];

	        return $response;
	    }
	}

	/**
	 * Update Cancellation details and Status
	 */
	public function update_cancellation_details(string $app_reference, array $cancellation_details): void
	{
	    $app_reference = trim($app_reference);
	    $booking_status = 'BOOKING_CANCELLED';

	    // 1. Add Cancellation details
	    $this->update_cancellation_refund_details($app_reference, $cancellation_details);

	    // 2. Update Master Booking Status
	    $this->custom_db->update_record('car_booking_details', ['status' => $booking_status], ['app_reference' => $app_reference]);

	    // 3. Update Itinerary Status
	    $this->custom_db->update_record('car_booking_itinerary_details', ['status' => $booking_status], ['app_reference' => $app_reference]);
	}

	/**
	 * Add Cancellation details
	 */
	public function update_cancellation_refund_details(string $app_reference, array $cancellation_details): void
	{
	    $car_cancellation_details = [
	        'app_reference' => $app_reference,
	        'ChangeRequestId' => $cancellation_details['ChangeRequestId'] ?? null,
	        'ChangeRequestStatus' => $cancellation_details['ChangeRequestStatus'] ?? null,
	        'status_description' => $cancellation_details['StatusDescription'] ?? null,
	        'API_RefundedAmount' => $cancellation_details['RefundedAmount'] ?? null,
	        'API_CancellationCharge' => $cancellation_details['CancellationCharge'] ?? null,
	    ];

	    if (($cancellation_details['ChangeRequestStatus'] ?? 0) == 3) {
	        $car_cancellation_details['cancellation_processed_on'] = date('Y-m-d H:i:s');
	    }

	    $cancel_details_exists = $this->custom_db->single_table_records('car_cancellation_details', '*', ['app_reference' => $app_reference]);

	    if ($cancel_details_exists['status'] == true) {
	        unset($car_cancellation_details['app_reference']);
	        $this->custom_db->update_record('car_cancellation_details', $car_cancellation_details, ['app_reference' => $app_reference]);
	    } else {
	        $car_cancellation_details['created_by_id'] = (int)($this->entity_user_id ?? 0);
	        $car_cancellation_details['created_datetime'] = date('Y-m-d H:i:s');
	        $car_cancellation_details['cancellation_requested_on'] = date('Y-m-d H:i:s');
	        $this->custom_db->insert_record('car_cancellation_details', $car_cancellation_details);
	    }
	}

}
