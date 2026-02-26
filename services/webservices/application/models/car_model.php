<?php
error_reporting(E_ALL);
/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Car Model
 * @author     Anitha.G J<anitha.g.provab@gmail.com>
 * @version    V1
 */
Class Car_Model extends CI_Model
{
	/*
	 *
	 * Get Airport List
	 *
	 */

	/**
 * Get airport list by search characters.
 *
 * @param string $search_chars
 * @return CI_DB_result
 */
public function get_airport_list(string $search_chars): CI_DB_result 
{
    $raw_search_chars = $this->db->escape($search_chars);
    $r_search_chars = $this->db->escape($search_chars . '%');
    $search_chars_like = $this->db->escape('%' . $search_chars . '%');

    $query = "
        SELECT * FROM Car_Airport
        WHERE Airport_Name_EN LIKE {$search_chars_like}
            OR Airport_IATA LIKE {$search_chars_like}
            OR Country_ISO LIKE {$search_chars_like}
        ORDER BY top_destination DESC,
            CASE
                WHEN Airport_IATA LIKE {$raw_search_chars} THEN 1
                WHEN Airport_Name_EN LIKE {$raw_search_chars} THEN 2
                WHEN Country_ISO LIKE {$raw_search_chars} THEN 3
                WHEN Airport_IATA LIKE {$r_search_chars} THEN 4
                WHEN Airport_Name_EN LIKE {$r_search_chars} THEN 5
                WHEN Country_ISO LIKE {$r_search_chars} THEN 6
                WHEN Airport_IATA LIKE {$search_chars_like} THEN 7
                WHEN Airport_Name_EN LIKE {$search_chars_like} THEN 8
                WHEN Country_ISO LIKE {$search_chars_like} THEN 9
                ELSE 10
            END
        LIMIT 0, 20
    ";

    return $this->db->query($query);
}
/**
 * Get city list by search characters.
 *
 * @param string $search_chars
 * @return CI_DB_result
 */
public function get_city_list(string $search_chars): CI_DB_result
{
    $search_chars_escaped = $this->db->escape($search_chars . '%');

    $query = '
        SELECT Country_ISO, origin, Country_Name_EN, City_ID, City_Name_EN, City_IATA AS Airport_IATA
        FROM Car_City
        WHERE (City_Name_EN LIKE ' . $search_chars_escaped . '
               OR Country_Name_EN LIKE ' . $search_chars_escaped . ')
              AND City_IATA != ""
        LIMIT 0, 10
    ';

    return $this->db->query($query);
}

/**
 * Save search data for future use - Analytics
 *
 * @param array $search_data
 * @return array
 */
public function save_search_history_data(array $search_data): array
{
	$data = array();
    $data['status'] = SUCCESS_STATUS;
    $cache_key = $this->redis_server->generate_cache_key();

    $search_history_data = [
        'domain_origin'     => get_domain_auth_id(),
        'created_datetime'  => db_current_datetime(),
        'search_type'       => META_CAR_COURSE,
        'cache_key'         => $cache_key,
        'search_data'       => json_encode($search_data),
    ];

    $insert_data = $this->custom_db->insert_record('search_history', $search_history_data);

    if ($insert_data['status'] == QUERY_SUCCESS) {
        $data['cache_key'] = $cache_key;
        $data['search_id'] = $insert_data['insert_id'];
    } else {
        $data['status'] = FAILURE_STATUS;
    }

    return $data;
}
/**
 * Get search data and validate it
 *
 * @param int|string $search_id
 * @return array
 */
public function get_safe_search_data(int $search_id): array
{
    $search_data = $this->get_search_data($search_id);

    $success = false;
    $clean_search = [];

    if ($search_data != false) {
        $temp_search_data = json_decode($search_data['search_data'], true);
        $validated_data = $this->clean_search_data($temp_search_data);
        $success = $validated_data['status'];
        $clean_search = $validated_data['data'];
    }

    return [
        'status' => $success,
        'data' => $clean_search
    ];
}
/**
 * Get search data without doing any validation
 *
 * @param int|string $search_id
 * @return array|false
 */
public function get_search_data(int $search_id): array
{
    $search_data = $this->custom_db->single_table_records(
        'search_history',
        '*',
        [
            'search_type' => META_CAR_COURSE,
            'origin' => $search_id
        ]
    );

    if ($search_data['status'] == true && !empty($search_data['data'][0])) {
        return $search_data['data'][0];
    }

    return false;
}
/**
 * Clean up search data
 *
 * @param array $temp_search_data
 * @return array{data: array, status: bool}
 */
public function clean_search_data(array $temp_search_data): array
{
    $success = true;
    return [
        'data' => $temp_search_data,
        'status' => $success
    ];
}
/**
 * Get all the booking sources which are active for the current domain
 *
 * @return array
 */
public function car_booking_source(): array
{
    $query = '
        SELECT BS.source_id, BS.origin 
        FROM meta_course_list AS MCL
        JOIN activity_source_map AS ASM ON MCL.origin = ASM.meta_course_list_fk
        JOIN booking_source AS BS ON ASM.booking_source_fk = BS.origin
        WHERE MCL.course_id = ' . $this->db->escape(META_CAR_COURSE) . '
          AND BS.booking_engine_status = ' . ACTIVE . '
          AND MCL.status = ' . ACTIVE . '
          AND ASM.status = "active"
    ';

    return $this->db->query($query)->result_array();
}
/**
 * Get Vehicle Category name based on ID
 *
 * @param int $id
 * @return string|null
 */
public function get_vehicle_category(int $id): ?string
{
    $query = 'SELECT vehiclecategory_name FROM car_vehiclecategory WHERE vehiclecategory_id = ' . $this->db->escape($id);
    $result = $this->db->query($query)->row();

    return $result->vehiclecategory_name ?? null;
}
/**
 * Get Vehicle Size name based on ID
 *
 * @param int $id
 * @return string|null
 */
public function get_vehicle_size(int $id): ?string
{
    $query = 'SELECT vehiclesize_name FROM car_vehiclesize WHERE vehiclesize_id = ' . $this->db->escape($id);
    $result = $this->db->query($query)->row();

    return $result->vehiclesize_name ?? null;
}
/**
 * Get Vehicle Category list
 *
 * @return array
 */
public function vehiclecategory(): array
{
    $query = 'SELECT vehiclecategory_id, vehiclecategory_name FROM car_vehiclecategory';
    return $this->db->query($query)->result_array();
}
/**
 * Get Vehicle Size list
 *
 * @return array
 */
public function vehiclesize(): array
{
    $query = 'SELECT vehiclesize_id, vehiclesize_name FROM car_vehiclesize';
    return $this->db->query($query)->result_array();
}
/**
 * Save car booking data
 *
 * @return array
 */
public function save_car_booking_details(
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
    float $domain_markup,
    float $domain_gst,
    string $booking_id,
    string $booking_reference,
    string $supplier_identifier,
    string $car_name,
    string $car_supplier_name,
    string $car_model,
    string $api_supplier_name,
    string $car_from_date,
    string $car_to_date,
    string $pickup_time,
    string $drop_time,
    string $car_pickup_lcation,
    string $car_drop_location,
    string $car_drop_address,
    string $car_pickup_address,
    string $value_type,
    string $final_cancel_date,
    string $transfer_type,
    string $pay_on_pickup,
    int $car_version
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
        'car_from_date' => $car_from_date,
        'car_to_date' => $car_to_date,
        'payment_mode' => $payment_mode,
        'supplier_identifier' => $supplier_identifier,
        'pickup_time' => $pickup_time,
        'drop_time' => $drop_time,
        'car_pickup_lcation' => $car_pickup_lcation,
        'car_drop_location' => $car_drop_location,
        'car_drop_address' => $car_drop_address,
        'car_pickup_address' => $car_pickup_address,
        'final_cancel_date' => $final_cancel_date,
        'transfer_type' => $transfer_type,
        'created_by_id' => $created_by_id,
        'created_datetime' => date('Y-m-d H:i:s'),
        'currency_conversion_rate' => $currency_conversion_rate,
        'version' => $car_version,
        'domain_markup' => $domain_markup,
        'domain_gst' => $domain_gst,
        'value_type' => $value_type,
        'pay_on_pickup' => $pay_on_pickup,
        'api_supplier_name' => $api_supplier_name
    ];

    return $this->custom_db->insert_record('car_booking_details', $data);
}
/**
 * Save car booking itinerary details
 *
 * @return array
 */
public function save_booking_itinerary_details(
    string $app_reference,
    string $car_from_date,
    string $car_to_date,
    string $pickup_time,
    string $drop_time,
    string $car_pickup_location,
    string $car_drop_location,
    string $car_pickup_address,
    string $car_drop_address,
    string $car_name,
    string $pricture_url,
    string $priced_equip,
    string $priced_coverage,
    string $cancellation_poicy,
    string $attributes1,
    string $status
): array {
    $data = [
        'status' => $status,
        'app_reference' => $app_reference,
        'car_from_date' => $car_from_date,
        'car_to_date' => $car_to_date,
        'pickup_time' => $pickup_time,
        'drop_time' => $drop_time,
        'car_pickup_loc' => $car_pickup_location,
        'car_drop_loc' => $car_drop_location,
        'car_pickup_add' => $car_pickup_address,
        'car_drop_add' => $car_drop_address,
        'car_name' => $car_name,
        'pricture_url' => $pricture_url,
        'priced_equip' => $priced_equip,
        'priced_coverage' => $priced_coverage,
        'cancellation_poicy' => $cancellation_poicy,
        'attributes' => $attributes1,
    ];

    return $this->custom_db->insert_record('car_booking_itinerary_details', $data);
}
/**
 * Save car booking passenger (pax) details
 *
 * @return array
 */
public function save_booking_pax_details(
    string $app_reference,
    string $title,
    string $first_name,
    string $last_name,
    string $phone,
    string $email,
    string $dob,
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
        'app_reference' => $app_reference,
        'title' => $title,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'phone' => $phone,
        'email' => $email,
        'date_of_birth' => $dob,
        'country_code' => $country_code,
        'country_name' => $country_name,
        'city' => $city,
        'pincode' => $pincode,
        'adress1' => $adress1,
        'adress2' => $adress2,
    ];

    return $this->custom_db->insert_record('car_booking_pax_details', $data);
}
/**
 * Get complete car booking details
 *
 * @param string $app_reference
 * @param string $booking_source
 * @param string $booking_status
 * @return array
 */
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

    $bd_query = 'SELECT * FROM car_booking_details AS BD WHERE BD.app_reference = ' . $this->db->escape(trim($app_reference));

    if (!empty($booking_source)) {
        $bd_query .= ' AND BD.booking_source = ' . $this->db->escape($booking_source);
    }

    if (!empty($booking_status)) {
        $bd_query .= ' AND BD.status = ' . $this->db->escape($booking_status);
    }

    $booking_details = $this->db->query($bd_query)->result_array();
    $app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);

    if (!empty($app_reference_ids)) {
        $booking_itinerary_details = $this->db->query(
            'SELECT * FROM car_booking_itinerary_details AS ID WHERE ID.app_reference IN (' . $app_reference_ids . ')'
        )->result_array();

        $booking_customer_details = $this->db->query(
            'SELECT * FROM car_booking_pax_details AS CD WHERE CD.app_reference IN (' . $app_reference_ids . ')'
        )->result_array();

        $booking_extra_details = $this->db->query(
            'SELECT * FROM car_booking_extra_details AS CD WHERE CD.app_reference IN (' . $app_reference_ids . ')'
        )->result_array();

        $cancellation_details = $this->db->query(
            'SELECT * FROM car_cancellation_details AS HCD WHERE HCD.app_reference IN (' . $app_reference_ids . ')'
        )->result_array();
    }

    $response['data'] = [
        'booking_details' => $booking_details,
        'booking_itinerary_details' => $booking_itinerary_details,
        'booking_pax_details' => $booking_customer_details,
        'booking_extra_details' => $booking_extra_details,
        'cancellation_details' => $cancellation_details
    ];

    return $response;
}
/**
 * Save extra service details for a booking
 *
 * @param string $app_reference
 * @param array $extra_service_details
 * @return bool
 */
public function save_booking_extra_details(string $app_reference, array $extra_service_details): bool
{
    $status = false;

    foreach ($extra_service_details as $service) {
        $data = [
            'app_reference' => $app_reference,
            'amount'        => $service['Amount'] ?? 0,
            'currency'      => $service['Currency'] ?? '',
            'description'   => $service['Description'] ?? '',
            'equiptype'     => $service['EquipType'] ?? '',
            'qunatity'      => $service['qunatity'] ?? 1  // Keeping original key spelling if DB expects it
        ];

        $insert_result = $this->custom_db->insert_record('car_booking_extra_details', $data);

        // Return false if any insert fails
        if ($insert_result['status'] != true) {
            return false;
        }

        $status = true;
    }

    return $status;
}
/**
 * Update cancellation details and status
 *
 * @param string $app_reference
 * @param array $cancellation_details
 * @return void
 */
public function update_cancellation_details(string $app_reference, array $cancellation_details): void
{
    $app_reference = trim($app_reference);
    $booking_status = 'BOOKING_CANCELLED';

    // 1. Add Cancellation details
    $this->update_cancellation_refund_details($app_reference, $cancellation_details);

    // 2. Update Master Booking Status
    $this->custom_db->update_record(
        'car_booking_details',
        ['status' => $booking_status],
        ['app_reference' => $app_reference]
    );

    // 3. Update Itinerary Status
    $this->custom_db->update_record(
        'car_booking_itinerary_details',
        ['status' => $booking_status],
        ['app_reference' => $app_reference]
    );
}
/**
 * Add cancellation refund details to the database.
 *
 * @param string $app_reference
 * @param array $cancellation_details
 * @return void
 */
private function update_cancellation_refund_details(string $app_reference, array $cancellation_details): void
{
    $cancellation_data = $cancellation_details['CarChangeRequestStatusResult'];

    $record = [
        'app_reference'              => $app_reference,
        'ChangeRequestId'            => $cancellation_data['ChangeRequestId'],
        'ChangeRequestStatus'        => $cancellation_data['ChangeRequestStatus'],
        'status_description'         => $cancellation_data['StatusDescription'],
        'API_RefundedAmount'         => $cancellation_data['RefundedAmount'],
        'API_CancellationCharge'     => $cancellation_data['CancellationCharge']
    ];

    if ($cancellation_data['ChangeRequestStatus'] == 3) {
        $record['cancellation_processed_on'] = date('Y-m-d H:i:s');

        $attributes = [
            'CreditNoteNo'        => $cancellation_data['CreditNoteNo'] ?? '',
            'CreditNoteCreatedOn' => $cancellation_data['CreditNoteCreatedOn'] ?? ''
        ];

        $record['attributes'] = json_encode($attributes);
    }

    $existing = $this->custom_db->single_table_records('car_cancellation_details', '*', [
        'app_reference' => $app_reference
    ]);

    if ($existing['status'] == true) {
        unset($record['app_reference']);
        $this->custom_db->update_record('car_cancellation_details', $record, [
            'app_reference' => $app_reference
        ]);
    } else {
        $record['created_by_id'] = $this->entity_user_id ?? 0;
        $record['created_datetime'] = date('Y-m-d H:i:s');
        $record['cancellation_requested_on'] = date('Y-m-d H:i:s');

        $this->custom_db->insert_record('car_cancellation_details', $record);
    }
}
/**
 * Update the refund details for a cancelled booking.
 *
 * @param string $app_reference
 * @param string $refund_status
 * @param float $refund_amount
 * @param float $cancellation_charge
 * @param string $currency
 * @param float $currency_conversion_rate
 * @return void
 */
public function update_refund_details(
    string $app_reference,
    string $refund_status,
    float $refund_amount,
    float $cancellation_charge,
    string $currency,
    float $currency_conversion_rate
): void {
    $refund_details = [
        'refund_amount'             => $refund_amount,
        'cancellation_charge'       => $cancellation_charge,
        'refund_status'             => $refund_status,
        'refund_payment_mode'       => 'online',
        'currency'                  => $currency,
        'currency_conversion_rate'  => $currency_conversion_rate,
        'refund_date'               => date('Y-m-d H:i:s')
    ];

    $this->custom_db->update_record(
        'car_cancellation_details',
        $refund_details,
        ['app_reference' => $app_reference]
    );
}



}
