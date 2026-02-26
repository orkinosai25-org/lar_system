<?php
/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Flight Model
 * @author     Arjun J<arjunjgowda260389@gmail.com>
 * @version    V2
 */
Class Flight_Model extends CI_Model
{
    /**
 * Fetch airport list based on a search query.
 *
 * @param string $query
 * @return CI_DB_result
 */
public function get_airport_list(string $query): CI_DB_result
{
    $this->db->like('airport_city', $query)
             ->or_like('airport_code', $query)
             ->or_like('country', $query)
             ->limit(20);

    return $this->db->get('flight_airport_list');
}

/**
 * Get sanitized flight search data based on search ID.
 *
 * @param int $search_id
 * @return array{status: bool, data: array}
 */
public function get_safe_search_data(int $search_id): array
{
    $search_condition = [['origin', '=', $search_id]];
    $search_data = $this->get_search_data($search_condition);

    if (empty($search_data)) {
        return ['status' => false, 'data' => []];
    }

    $decoded = json_decode($search_data['search_data'], true);
    if (!is_array($decoded)) {
        return ['status' => false, 'data' => []];
    }

    $trip_type = $decoded['JourneyType'] ?? '';
    $segments  = $decoded['Segments'] ?? [];

    $clean_search = [
        'cache_key'      => $search_data['cache_key'],
        'trip_type'      => $trip_type,
        'carrier'        => $decoded['PreferredAirlines'] ?? '',
        'cabin_class'    => $decoded['CabinClass'] ?? '',
        'pnr'            => $decoded['PNR'] ?? '',
        'booking_id'     => $decoded['BookingId'] ?? '',
        'adult_config'   => (int)($decoded['AdultCount'] ?? 0),
        'child_config'   => (int)($decoded['ChildCount'] ?? 0),
        'infant_config'  => (int)($decoded['InfantCount'] ?? 0),
        'is_domestic'    => $decoded['IsDomestic'] ?? false,
    ];

    $clean_search['total_pax'] = $clean_search['adult_config']
                               + $clean_search['child_config']
                               + $clean_search['infant_config'];

    if ($trip_type === 'multicity') {
        $clean_search['from']         = array_column($segments, 'Origin');
        $clean_search['to']           = array_column($segments, 'Destination');
        $clean_search['depature']     = array_column($segments, 'DepartureDate');
        $clean_search['from_country'] = array_column($segments, 'Origin_Country');
        $clean_search['to_country']   = array_column($segments, 'Dest_Country');
        $clean_search['from_city']    = array_column($segments, 'Origin_City');
        $clean_search['to_city']      = array_column($segments, 'Dest_City');
    } else {
        $segment = $segments[0] ?? [];
        $clean_search['from']         = $segment['Origin'] ?? '';
        $clean_search['to']           = $segment['Destination'] ?? '';
        $clean_search['depature']     = $segment['DepartureDate'] ?? '';
        $clean_search['from_country'] = $segment['Origin_Country'] ?? '';
        $clean_search['to_country']   = $segment['Dest_Country'] ?? '';
        $clean_search['from_city']    = $segment['Origin_City'] ?? '';
        $clean_search['to_city']      = $segment['Dest_City'] ?? '';
        $clean_search['return']       = ($trip_type === 'return') ? ($segment['ReturnDate'] ?? '') : '';
    }

    return ['status' => true, 'data' => $clean_search];
}
/**
 * Save flight search data and return the cache key and search ID.
 *
 * @param array $request
 * @return array{status: string, cache_key?: string, search_id?: int}
 */
public function save_search_data(array $request): array
{
    $data = ['status' => SUCCESS_STATUS];

    // Generate unique cache key
    $cache_key = $this->redis_server->generate_cache_key();

    // Enrich segments with airport city and country details
    foreach ($request['Segments'] as &$segment) {
        $origin_info = $this->get_airport_city_name($segment['Origin']);
        $dest_info   = $this->get_airport_city_name($segment['Destination']);

        $segment['Origin_Country'] = $origin_info->country      ?? '';
        $segment['Dest_Country']   = $dest_info->country         ?? '';
        $segment['Origin_City']    = $origin_info->airport_city  ?? '';
        $segment['Dest_City']      = $dest_info->airport_city    ?? '';
    }

    // Determine if the flight is domestic
    $from_locations = array_column($request['Segments'], 'Origin');
    $to_locations   = array_column($request['Segments'], 'Destination');
    $request['IsDomestic'] = $this->is_domestic_flight($from_locations, $to_locations);

    // Normalize input
    $request['JourneyType'] = strtolower($request['JourneyType'] ?? '');
    $request['CabinClass']  = strtolower($request['CabinClass'] ?? '');

    // Prepare DB insert data
    $search_history_data = [
        'domain_origin'    => get_domain_auth_id(),
        'cache_key'        => $cache_key,
        'search_type'      => META_AIRLINE_COURSE,
        'search_data'      => json_encode($request),
        'created_datetime' => db_current_datetime(),
    ];

    // Insert record
    $insert_data = $this->custom_db->insert_record('search_history', $search_history_data);

    // Check DB response
    if (($insert_data['status'] ?? '') !== QUERY_SUCCESS) {
        return ['status' => FAILURE_STATUS];
    }

    // Return result
    return [
        'status'    => SUCCESS_STATUS,
        'cache_key' => $cache_key,
        'search_id' => $insert_data['insert_id'],
    ];
}
/**
 * Get full airport details by IATA code.
 *
 * @param string $airport_code
 * @return object|null
 */
public function get_airport_city_name(string $airport_code): ?object
{
    $query = 'SELECT * FROM flight_airport_list WHERE airport_code = ? LIMIT 1';
    return $this->db->query($query, [$airport_code])->row(); // returns null if not found
}

/**
 * Fetch flight search data from search_history table using dynamic condition.
 *
 * @param array $condition
 * @return array
 */
public function get_search_data(array $condition = []): array
{
    $custom_condition = $this->custom_db->get_custom_condition($condition);
    $query = 'SELECT SH.* FROM search_history SH WHERE search_type = ? ' . $custom_condition . ' LIMIT 1';
    $result = $this->db->query($query, [META_AIRLINE_COURSE])->row_array();
    return !empty($result) ? $result : false;
}

/**
 * Determine if a flight is domestic based on from and to airport codes.
 *
 * @param array $from_loc
 * @param array $to_loc
 * @return bool
 */
public function is_domestic_flight(array $from_loc, array $to_loc): bool
{
    $all_codes = array_unique(array_merge($from_loc, $to_loc));

    if (empty($all_codes)) {
        return false; // No airports to evaluate
    }

    // Build placeholders for the IN clause
    $placeholders = rtrim(str_repeat('?,', count($all_codes)), ',');
    $query = "SELECT COUNT(*) AS total FROM flight_airport_list WHERE airport_code IN ($placeholders) AND country != 'India'";

    $result = $this->db->query($query, $all_codes)->row_array();

    // If total is 0, all airports are in India → domestic
    return (int)($result['total'] ?? 0) === 0;
}
/**
 * Flight booking report
 *
 * @param array $condition
 * @param bool $count
 * @param int $offset
 * @param int $limit
 * @return int|array
 */
public function booking(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100):array
{
    $domain_id = (int)get_domain_auth_id();
    $user_id = (int)($GLOBALS['CI']->entity_user_id);

    if ($count) {
        $sql = "SELECT COUNT(DISTINCT BD.app_reference) AS total_records
                FROM flight_booking_details BD
                WHERE BD.domain_origin = ? AND BD.created_by_id = ?";
        $result = $this->db->query($sql, [$domain_id, $user_id])->row_array();
        return (int)($result['total_records'] ?? 0);
    }

    $cols = '
        BD.status, BD.app_reference, BD.booking_source, BD.total_fare,
        BD.domain_markup, BD.level_one_markup, BD.currency, BD.journey_from, BD.journey_to, 
        BD.journey_start, BD.journey_end, BD.phone, BD.payment_mode, 
        BD.created_by_id, BD.created_datetime, BD.email, BD.phone AS phone_number,
        COUNT(DISTINCT CD.origin) AS total_passengers,
        CONCAT(CD.title, " ", CD.first_name, " ", CD.middle_name, " ", CD.last_name) AS name,
        POL.name AS payment_name';

    $sql = "SELECT {$cols}
            FROM flight_booking_details BD
            JOIN flight_booking_passenger_details CD ON BD.app_reference = CD.app_reference
            JOIN flight_booking_itinerary_details ID ON BD.app_reference = ID.app_reference
            JOIN flight_booking_transaction_details TD ON BD.app_reference = TD.app_reference
            JOIN payment_option_list POL ON POL.payment_category_code = BD.payment_mode
            WHERE BD.domain_origin = ? AND BD.created_by_id = ?
            GROUP BY BD.app_reference
            ORDER BY BD.origin DESC
            LIMIT ?, ?";

    return $this->db->query($sql, [$domain_id, $user_id, $offset, $limit])->result_array();
}
/**
 * Get all active booking sources for current domain
 */
public function active_booking_source(): array
{
    $sql = "SELECT BS.source_id, BS.origin 
            FROM meta_course_list AS MCL
            JOIN activity_source_map AS ASM ON MCL.origin = ASM.meta_course_list_fk
            JOIN booking_source AS BS ON ASM.booking_source_fk = BS.origin
            WHERE MCL.course_id = ?
              AND BS.booking_engine_status = ?
              AND MCL.status = ?
              AND ASM.status = ?";

    $params = [
        META_AIRLINE_COURSE,
        ACTIVE,
        ACTIVE,
        'active'
    ];

    return $this->db->query($sql, $params)->result_array();
}
/**
 * TEMPORARY FUNCTION: Get static response from test table
 */
public function get_static_response(int $token_id): array
{
    $record = $this->custom_db->single_table_records('test', 'test', ['origin' => $token_id]);

    if (!empty($record['status']) && $record['status'] === true && !empty($record['data'][0]['test'])) {
        $decoded = json_decode($record['data'][0]['test'], true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Save flight booking details
     */
    public function save_flight_booking_details(int $domain_origin,string $status,string $app_reference, string $booking_source,string $is_lcc, string $currency, string $phone,
        string $alternate_number,
        string $email,
        string $journey_start,
        string $journey_end,
        string $journey_from,
        string $journey_to,
        string $payment_mode,
        string $attributes,
        int $created_by_id,
        float $conversion_rate,
        int $flight_version,
        string $cabin_class
    ): void {
        if ($this->is_app_reference_exists($app_reference)) {
            return;
        }

    // Additional validations (e.g., checking if the email is valid)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Log invalid email
        log_message('error', 'Invalid email address: ' . $email);
        return;
    }

    // Data preparation
    $data = [
        'domain_origin' => $domain_origin,
        'status' => $status,
        'app_reference' => $app_reference,
        'booking_source' => $booking_source,
        'is_lcc' => $is_lcc,
        'currency' => $currency,
        'phone' => $phone,
        'alternate_number' => $alternate_number,
        'email' => $email,
        'journey_start' => $journey_start,
        'journey_end' => $journey_end,
        'journey_from' => $journey_from,
        'journey_to' => $journey_to,
        'payment_mode' => $payment_mode,
        'attributes' => $attributes,
        'cabin_class' => $cabin_class,
        'created_by_id' => $created_by_id,
        'created_datetime' => date('Y-m-d H:i:s'), // or use a time helper function
        'currency_conversion_rate' => $conversion_rate,
        'version' => $flight_version
    ];

    // Attempt to insert data
    $insert_result = $this->custom_db->insert_record('flight_booking_details', $data);

    /**
     * Save flight booking passenger details
     */
    public function save_flight_booking_passenger_details(string $app_reference,string $passenger_type, string $is_lead, string $title, string $first_name,
        string $middle_name,
        string $last_name,
        string $date_of_birth,
        string $gender,
        string $nationality,
        string $passport_number,
        string $passport_country,
        string $passport_expiry_date,
        string $status,
        string $attributes,
        int $transaction_det_fk
    ): array {
        $data = [
            'app_reference' => $app_reference,
            'passenger_type' => $passenger_type,
            'is_lead' => $is_lead,
            'title' => $title,
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'date_of_birth' => $date_of_birth,
            'gender' => $gender,
            'passenger_nationality' => $nationality,
            'passport_number' => $passport_number,
            'passport_issuing_country' => $passport_country,
            'passport_expiry_date' => $passport_expiry_date,
            'status' => $status,
            'attributes' => $attributes,
            'flight_booking_transaction_details_fk' => $transaction_det_fk
        ];

        return $this->custom_db->insert_record('flight_booking_passenger_details', $data);
    }
	 /**
     * Save flight booking transaction details
     */
    public function save_flight_booking_transaction_details(
        string $app_reference,
        string $transaction_status,
        string $status_description,
        string $pnr,
        string $book_id,
        string $source,
        int $ref_id,
        string $attributes,
        int $sequence_number,
        float $total_fare,
        float $domain_markup,
        float $admin_commission,
        float $agent_commission,
        float $admin_tds,
        float $agent_tds,
        string $booking_source = TBO_FLIGHT_BOOKING_SOURCE,
        string $fare_breakup = '',
        string $mini_fare_rules = ''
    ): array {
        $data = [
            'app_reference' => $app_reference,
            'status' => $transaction_status,
            'status_description' => $status_description,
            'pnr' => $pnr,
            'book_id' => $book_id,
            'source' => $source,
            'ref_id' => $ref_id,
            'attributes' => $attributes,
            'sequence_number' => $sequence_number,
            'total_fare' => $total_fare,
            'domain_markup' => $domain_markup,
            'admin_commission' => $admin_commission,
            'agent_commission' => $agent_commission,
            'admin_tds' => $admin_tds,
            'agent_tds' => $agent_tds,
            'booking_source' => $booking_source,
            'fare_breakup' => $fare_breakup,
            'mini_farerules' => $mini_fare_rules
        ];

        return $this->custom_db->insert_record('flight_booking_transaction_details', $data);
    }

    /**
     * Save flight booking itinerary details
     */
    public function save_flight_booking_itinerary_details(
        string $app_reference,
        int $segment_indicator,
        string $airline_code,
        string $airline_name,
        string $flight_number,
        string $fare_class,
        string $from_airport_code,
        string $from_airport_name,
        string $to_airport_code,
        string $to_airport_name,
        string $departure_datetime,
        string $arrival_datetime,
        string $status,
        string $operating_carrier,
        string $attributes,
        int $transaction_det_fk = 0,
        string $is_refundable = 'false'
    ): array {
        $data = [
            'app_reference' => $app_reference,
            'segment_indicator' => $segment_indicator,
            'airline_code' => $airline_code,
            'airline_name' => $airline_name,
            'flight_number' => $flight_number,
            'fare_class' => $fare_class,
            'from_airport_code' => $from_airport_code,
            'from_airport_name' => $from_airport_name,
            'to_airport_code' => $to_airport_code,
            'to_airport_name' => $to_airport_name,
            'departure_datetime' => $departure_datetime,
            'arrival_datetime' => $arrival_datetime,
            'status' => $status,
            'operating_carrier' => $operating_carrier,
            'attributes' => $attributes,
            'flight_booking_transaction_details_fk' => $transaction_det_fk,
            'is_refundable' => $is_refundable
        ];

    // Insert the itinerary details into the database
    $insert_result = $this->custom_db->insert_record('flight_booking_itinerary_details', $data);
    
    // Check if the insert was successful
    if ($insert_result['status'] !== QUERY_SUCCESS) {
        log_message('error', 'Failed to save itinerary details for app reference ' . $app_reference);
        return ['status' => false, 'message' => 'Failed to save itinerary details'];
    }

    return ['status' => true, 'message' => 'Itinerary details saved successfully'];
}
/**
 * Save passenger ticket info
 */
public function save_passenger_ticket_info(int $passenger_fk): array
{
    // Check if the passenger exists in the passenger details table
    $existing_passenger = $this->custom_db->single_table_records('flight_booking_passenger_details', 'id', ['id' => $passenger_fk]);

    if (empty($existing_passenger['data'])) {
        log_message('error', 'Passenger with ID ' . $passenger_fk . ' does not exist.');
        return ['status' => false, 'message' => 'Passenger not found'];
    }

    // Check if the passenger already has a ticket info entry
    $existing_ticket_info = $this->custom_db->single_table_records('flight_passenger_ticket_info', 'id', ['passenger_fk' => $passenger_fk]);

    if (!empty($existing_ticket_info['data'])) {
        log_message('error', 'Ticket info already exists for passenger with ID ' . $passenger_fk);
        return ['status' => false, 'message' => 'Ticket info already exists for this passenger'];
    }

    // Prepare data for insertion
    $data = ['passenger_fk' => $passenger_fk];

    // Insert the ticket info into the database
    $insert_result = $this->custom_db->insert_record('flight_passenger_ticket_info', $data);

    // Check if the insert was successful
    if ($insert_result['status'] !== QUERY_SUCCESS) {
        log_message('error', 'Failed to save ticket info for passenger with ID ' . $passenger_fk);
        return ['status' => false, 'message' => 'Failed to save ticket info'];
    }

    return ['status' => true, 'message' => 'Ticket info saved successfully'];
}
/**
 * Update passenger ticket info
 */
public function update_passenger_ticket_info(
    int $passenger_fk,
    string $TicketId,
    string $TicketNumber,
    string $IssueDate,
    float $Fare,
    string $SegmentInfo,
    string $ValidatingAirline,
    string $CorporateCode,
    string $TourCode,
    string $Endorsement,
    string $Remarks,
    string $FeeDisplayType
): void {
    // Check if the passenger exists
    $existing_passenger = $this->custom_db->single_table_records('flight_booking_passenger_details', 'id', ['id' => $passenger_fk]);

    if (empty($existing_passenger['data'])) {
        log_message('error', 'Passenger with ID ' . $passenger_fk . ' does not exist.');
        throw new Exception('Passenger not found');
    }

    // Prepare data for the update
    $data = [
        'TicketId' => $TicketId,
        'TicketNumber' => $TicketNumber,
        'IssueDate' => $IssueDate,
        'Fare' => $Fare,
        'SegmentAdditionalInfo' => $SegmentInfo,
        'ValidatingAirline' => $ValidatingAirline,
        'CorporateCode' => $CorporateCode,
        'TourCode' => $TourCode,
        'Endorsement' => $Endorsement,
        'Remarks' => $Remarks,
        'ServiceFeeDisplayType' => $FeeDisplayType
    ];

    // Update condition
    $update_condition = ['passenger_fk' => $passenger_fk];

    // Perform the update
    $update_result = $this->custom_db->update_record('flight_passenger_ticket_info', $data, $update_condition);

    // Check if the update was successful
    if ($update_result['status'] !== QUERY_SUCCESS) {
        log_message('error', 'Failed to update ticket info for passenger with ID ' . $passenger_fk);
        throw new Exception('Failed to update ticket info');
    }

    log_message('info', 'Successfully updated ticket info for passenger with ID ' . $passenger_fk);
}
/**
 * Save passenger baggage info
 */
public function save_passenger_baggage_info(
    int $passenger_fk,
    string $from_airport_code,
    string $to_airport_code,
    string $description,
    float $price,
    string $baggage_id
): void {
    // Check if the passenger exists
    $existing_passenger = $this->custom_db->single_table_records('flight_booking_passenger_details', 'id', ['id' => $passenger_fk]);

    if (empty($existing_passenger['data'])) {
        log_message('error', 'Passenger with ID ' . $passenger_fk . ' does not exist.');
        throw new Exception('Passenger not found');
    }

    // Prepare data for the insert
    $data = [
        'passenger_fk' => $passenger_fk,
        'from_airport_code' => $from_airport_code,
        'to_airport_code' => $to_airport_code,
        'description' => $description,
        'price' => $price,
        'baggage_id' => $baggage_id
    ];

    // Perform the insert
    $insert_result = $this->custom_db->insert_record('flight_booking_baggage_details', $data);

    // Check if the insertion was successful
    if ($insert_result['status'] !== QUERY_SUCCESS) {
        log_message('error', 'Failed to insert baggage info for passenger with ID ' . $passenger_fk);
        throw new Exception('Failed to insert baggage info');
    }

    log_message('info', 'Successfully inserted baggage info for passenger with ID ' . $passenger_fk);
}
/**
 * Save passenger meals info
 */
public function save_passenger_meals_info(
    int $passenger_fk,
    string $from_airport_code,
    string $to_airport_code,
    string $description,
    float $price,
    string $meal_id,
    string $type
): void {
    // Check if the passenger exists
    $existing_passenger = $this->custom_db->single_table_records('flight_booking_passenger_details', 'id', ['id' => $passenger_fk]);

    if (empty($existing_passenger['data'])) {
        log_message('error', 'Passenger with ID ' . $passenger_fk . ' does not exist.');
        throw new Exception('Passenger not found');
    }

    // Prepare data for the insert
    $data = [
        'passenger_fk' => $passenger_fk,
        'from_airport_code' => $from_airport_code,
        'to_airport_code' => $to_airport_code,
        'description' => $description,
        'price' => $price,
        'meal_id' => $meal_id,
        'type' => $type
    ];

    // Perform the insert
    $insert_result = $this->custom_db->insert_record('flight_booking_meal_details', $data);

    // Check if the insertion was successful
    if ($insert_result['status'] !== QUERY_SUCCESS) {
        log_message('error', 'Failed to insert meal info for passenger with ID ' . $passenger_fk);
        throw new Exception('Failed to insert meal info');
    }

    log_message('info', 'Successfully inserted meal info for passenger with ID ' . $passenger_fk);
}
/**
 * Save passenger seat info
 */
public function save_passenger_seat_info(
    int $passenger_fk,
    string $from_airport_code,
    string $to_airport_code,
    string $description,
    float $price,
    string $seat_id,
    string $type,
    string $airline_code,
    string $flight_number
): void {
    // Check if the passenger exists
    $existing_passenger = $this->custom_db->single_table_records('flight_booking_passenger_details', 'id', ['id' => $passenger_fk]);

    if (empty($existing_passenger['data'])) {
        log_message('error', 'Passenger with ID ' . $passenger_fk . ' does not exist.');
        throw new Exception('Passenger not found');
    }

    // Prepare data for the insert
    $data = [
        'passenger_fk' => $passenger_fk,
        'from_airport_code' => $from_airport_code,
        'to_airport_code' => $to_airport_code,
        'description' => $description,
        'price' => $price,
        'seat_id' => $seat_id,
        'type' => $type,
        'airline_code' => $airline_code,
        'flight_number' => $flight_number
    ];

    // Perform the insert
    $insert_result = $this->custom_db->insert_record('flight_booking_seat_details', $data);

    // Check if the insertion was successful
    if ($insert_result['status'] !== QUERY_SUCCESS) {
        log_message('error', 'Failed to insert seat info for passenger with ID ' . $passenger_fk);
        throw new Exception('Failed to insert seat info');
    }

    log_message('info', 'Successfully inserted seat info for passenger with ID ' . $passenger_fk);
}
/**
 * Fetch airline deals for the given domain ID.
 *
 * @param int $idval Domain ID for which the airline deals are fetched.
 * @return array List of airline deals, including airline name, code, business fare, economy fare, and import fee.
 */
public function airline_deals(int $idval): array
{
    // Validate that the input is a positive integer (domain_id).
    if ($idval <= 0) {
        log_message('error', "Invalid domain_id: {$idval}. It must be a positive integer.");
        return [];  // Return an empty array or handle the error appropriately
    }

    // Columns to fetch from the database
    $cols = 'AL.name, AL.code, ADS.business, ADS.economy, ADS.import_fee';

    // Prepare the query with sanitized domain_id
    $query = "SELECT $cols FROM airline_deal_sheet AS ADS 
              LEFT JOIN airline_list AS AL ON ADS.airline_origin = AL.origin 
              WHERE ADS.domain_id = {$this->db->escape($idval)} 
              AND ADS.business > 0 AND ADS.economy > 0 AND ADS.import_fee > 0 
              ORDER BY AL.name ASC";

    // Execute the query and return the result
    try {
        $result = $this->db->query($query)->result_array();
        return $result;
    } catch (Exception $e) {
        log_message('error', "Error fetching airline deals: " . $e->getMessage());
        return [];  // Return an empty array or handle the error gracefully
    }
}
/**
 * Fetch airline commission details for the given domain ID.
 *
 * @param int $idval Domain ID to fetch commission details for.
 * @return array List of airline commission details, including domain name, commission value, API value, and value type.
 */
public function airline_commission_details(int $idval): array
{
    // Validate that the input is a positive integer (domain_id).
    if ($idval <= 0) {
        log_message('error', "Invalid domain_id: {$idval}. It must be a positive integer.");
        return [];  // Return an empty array if the domain ID is invalid
    }

    // Columns to fetch from the database
    $cols = 'DL.domain_name, BFCD.value, BFCD.api_value, BFCD.value_type';

    // Prepare the query with sanitized domain ID
    $query = "SELECT $cols FROM domain_list DL
              LEFT JOIN b2b_flight_commission_details AS BFCD ON DL.origin = BFCD.domain_list_fk
              WHERE DL.origin = " . $this->db->escape($idval);

    // Execute the query and return the result
    try {
        $result = $this->db->query($query)->result_array();
        return $result;
    } catch (Exception $e) {
        log_message('error', "Error fetching airline commission details: " . $e->getMessage());
        return [];  // Return an empty array in case of an error
    }
}
public function check_details_update_pnr(string $app_reference): array
{
    // Initialize response with failure status
    $response = [
        'status' => FAILURE_STATUS,
        'data' => []
    ];

    // Escape the app reference to prevent SQL injection
    $escaped_ref = $this->db->escape($app_reference);

    // First query to fetch flight booking details
    $response['data']['booking_details'] = $this->db->query(
        "SELECT BD.*, DL.domain_name, DL.origin AS domain_id
        FROM flight_booking_details AS BD
        JOIN domain_list AS DL ON DL.origin = BD.domain_origin
        WHERE BD.app_reference LIKE ?", [$escaped_ref]
    )->result_array();

    // Fetch itinerary details
    $response['data']['booking_itinerary_details'] = $this->db->query(
        "SELECT * FROM flight_booking_itinerary_details AS ID
        WHERE ID.app_reference = ?", [$escaped_ref]
    )->result_array();

    // Fetch transaction details
    $response['data']['booking_transaction_details'] = $this->db->query(
        "SELECT * FROM flight_booking_transaction_details AS CD
        WHERE CD.app_reference = ?", [$escaped_ref]
    )->result_array();

    // Fetch customer details
    $response['data']['booking_customer_details'] = $this->db->query(
        "SELECT CD.*, FPTI.TicketId, FBTD.sequence_number, FPTI.TicketNumber, FPTI.IssueDate, FPTI.Fare, FPTI.SegmentAdditionalInfo
        FROM flight_booking_passenger_details AS CD
        LEFT JOIN flight_passenger_ticket_info FPTI ON CD.origin = FPTI.passenger_fk
        LEFT JOIN flight_booking_transaction_details FBTD ON FBTD.origin = CD.flight_booking_transaction_details_fk
        WHERE CD.flight_booking_transaction_details_fk IN (
            SELECT TD.origin FROM flight_booking_transaction_details AS TD
            WHERE TD.app_reference = ? ORDER BY TD.sequence_number DESC
        )", [$escaped_ref]
    )->result_array();

    // Fetch cancellation details
    $response['data']['cancellation_details'] = $this->db->query(
        "SELECT FCD.*
        FROM flight_booking_passenger_details AS CD
        LEFT JOIN flight_cancellation_details AS FCD ON FCD.passenger_fk = CD.origin
        WHERE CD.flight_booking_transaction_details_fk IN (
            SELECT TD.origin FROM flight_booking_transaction_details AS TD
            WHERE TD.app_reference = ?
        )", [$escaped_ref]
    )->result_array();

    // Check if all the required data is present and update the status
    if (
        !empty($response['data']['booking_details']) &&
        !empty($response['data']['booking_itinerary_details']) &&
        !empty($response['data']['booking_customer_details'])
    ) {
        $response['status'] = SUCCESS_STATUS;
    }

    return $response;
}
public function is_app_reference_exists(string $app_reference): bool
{
    // Trim the app_reference to remove unnecessary spaces
    $trimmed_ref = trim($app_reference);

    // Return false if the reference is empty after trimming
    if (empty($trimmed_ref)) {
        return false;
    }

    // Query to check if app_reference exists in the flight_booking_details table
    $booking_details = $this->custom_db->single_table_records(
        'flight_booking_details',
        '*',
        ['app_reference' => $trimmed_ref]
    );

    // Return true if the app_reference exists, false otherwise
    return !empty($booking_details['data']);
}
public function get_passenger_ticket_info(
    string $app_reference,
    string $sequence_number,
    string $booking_id,
    string $pnr,
    string $ticket_id
): array {
    $response = [
        'status' => FAILURE_STATUS,
        'data' => [],
    ];

    // Escape variables
    $app_reference_e = $this->db->escape($app_reference);
    $pnr_escaped = $this->db->escape($pnr);
    $booking_id_escaped = $this->db->escape($booking_id);
    $ticket_id_escaped = $this->db->escape(trim($ticket_id));
    $sequence_number_escaped = $this->db->escape(trim($sequence_number));

    // SQL Queries
    $queries = [
        'booking_details' => "
            SELECT BD.*, DL.domain_name, DL.origin AS domain_id 
            FROM flight_booking_details AS BD
            JOIN domain_list AS DL ON DL.origin = BD.domain_origin
            WHERE BD.app_reference = {$app_reference_e}
        ",
        'itinerary_details' => "
            SELECT * 
            FROM flight_booking_itinerary_details 
            WHERE app_reference = {$app_reference_e}
        ",
        'transaction_details' => "
            SELECT TD.* 
            FROM flight_booking_transaction_details AS TD 
            WHERE TD.app_reference = {$app_reference_e}
            AND TD.sequence_number = {$sequence_number_escaped}
            AND TD.pnr = {$pnr_escaped}
            AND TD.book_id = {$booking_id_escaped}
        ",
        'customer_details' => "
            SELECT CD.*, FPTI.TicketId, FPTI.TicketNumber, FPTI.IssueDate, FPTI.Fare, FPTI.SegmentAdditionalInfo
            FROM flight_booking_passenger_details AS CD
            LEFT JOIN flight_passenger_ticket_info AS FPTI ON CD.origin = FPTI.passenger_fk
            WHERE FPTI.TicketId = {$ticket_id_escaped}
            AND CD.flight_booking_transaction_details_fk IN (
                SELECT TD.origin 
                FROM flight_booking_transaction_details AS TD
                WHERE TD.app_reference = {$app_reference_e}
                AND TD.sequence_number = {$sequence_number_escaped}
                AND TD.pnr = {$pnr_escaped}
                AND TD.book_id = {$booking_id_escaped}
            )
        ",
        'cancellation_details' => "
            SELECT FCD.*
            FROM flight_booking_passenger_details AS CD
            LEFT JOIN flight_passenger_ticket_info AS FPTI ON CD.origin = FPTI.passenger_fk
            LEFT JOIN flight_cancellation_details AS FCD ON FCD.passenger_fk = CD.origin
            WHERE FPTI.TicketId = {$ticket_id_escaped}
            AND CD.flight_booking_transaction_details_fk IN (
                SELECT TD.origin 
                FROM flight_booking_transaction_details AS TD
                WHERE TD.app_reference = {$app_reference_e}
                AND TD.sequence_number = {$sequence_number_escaped}
                AND TD.pnr = {$pnr_escaped}
                AND TD.book_id = {$booking_id_escaped}
            )
        "
    ];

    // Execute queries and store results
    foreach ($queries as $key => $query) {
        $response['data'][$key] = $this->db->query($query)->result_array();
    }

    // Check if all necessary data is returned and set status to success
    if (
        valid_array($response['data']['booking_details']) &&
        valid_array($response['data']['itinerary_details']) &&
        valid_array($response['data']['transaction_details']) &&
        valid_array($response['data']['customer_details'])
    ) {
        $response['status'] = SUCCESS_STATUS;
    }

    return $response;
}
/**
 * Updates cancellation details.
 */
public function update_cancellation_details(
    string $AppReference,
    string $SequenceNumber,
    string $BookingId,
    string $PNR,
    string $TicketId,
    string $ChangeRequestId,
    array $cancellation_details
): void {
    // Get passenger ticket info
    $passenger_info = $this->get_passenger_ticket_info(
        $AppReference,
        $SequenceNumber,
        $BookingId,
        $PNR,
        $TicketId
    );

    if ($passenger_info['status'] !== SUCCESS_STATUS) {
        // If passenger info retrieval fails, log the error and exit
        log_message('error', "Failed to retrieve passenger ticket info for AppReference: $AppReference, TicketId: $TicketId");
        return;
    }

    $data = $passenger_info['data'];
    $transaction_details = $data['booking_transaction_details'][0] ?? [];
    $customer_details = $data['booking_customer_details'][0] ?? [];

    // Start a transaction to ensure data integrity
    $this->db->trans_start();

    try {
        // 1. Update Passenger Status and Add Cancellation details
        if (!empty($customer_details)) {
            $this->update_pax_ticket_cancellation_details(
                $cancellation_details,
                (int) $customer_details['origin']
            );
        }

        // 2. Update Transaction details
        if (!empty($transaction_details)) {
            $this->update_flight_booking_transaction_cancel_status((int) $transaction_details['origin']);
        }

        // 3. Update the Master Booking Status
        $this->update_flight_booking_cancel_status($AppReference);

        // Commit the transaction
        $this->db->trans_commit();
    } catch (Exception $e) {
        // Rollback transaction if any part fails
        $this->db->trans_rollback();
        log_message('error', "Error updating cancellation details for AppReference: $AppReference. Error: " . $e->getMessage());
    }
}
/**
 * Update the Cancellation Details of the Passenger
 */
public function update_pax_ticket_cancellation_details(array $cancellation_details, int $passenger_origin): void
{
    $data = [];
    
    // 1. Update Passenger Status to 'BOOKING_CANCELLED'
    $booking_status = 'BOOKING_CANCELLED';
    $passenger_update = ['status' => $booking_status];
    $passenger_condition = ['origin' => $passenger_origin];

    // Error handling for passenger status update
    try {
        $this->custom_db->update_record('flight_booking_passenger_details', $passenger_update, $passenger_condition);
    } catch (Exception $e) {
        log_message('error', "Failed to update passenger status for passenger_origin: $passenger_origin. Error: " . $e->getMessage());
        return;
    }

    // 2. Add Cancellation Details
    $cancellation_info = $cancellation_details['data']['TicketCancellationtDetails'] ?? [];

    if (($cancellation_info['ChangeRequestStatus'] ?? null) == 4) {
        $data['cancellation_processed_on'] = date('Y-m-d H:i:s');
    }

    // Set cancellation-related fields
    $data['RequestId'] = $cancellation_info['ChangeRequestId'] ?? '';
    $data['API_RefundedAmount'] = $cancellation_info['RefundedAmount'] ?? 0.0;
    $data['API_CancellationCharge'] = $cancellation_info['CancellationCharge'] ?? 0.0;
    $data['API_ServiceTaxOnRefundAmount'] = $cancellation_info['ServiceTaxOnRefundAmount'] ?? 0.0;
    $data['API_SwachhBharatCess'] = $cancellation_info['SwachhBharatCess'] ?? 0.0;
    $data['API_KrishiKalyanCess'] = $cancellation_info['KrishiKalyanCess'] ?? 0.0;
    $data['ChangeRequestStatus'] = $cancellation_info['ChangeRequestStatus'] ?? 0;
    $data['statusDescription'] = $cancellation_info['StatusDescription'] ?? '';
    $data['current_status'] = $data['ChangeRequestStatus'];

    // Check if cancellation record exists for the passenger
    $exists = $this->custom_db->single_table_records('flight_cancellation_details', '*', ['passenger_fk' => $passenger_origin]);

    // If record exists, update it
    if ($exists['status'] == true) {
        try {
            $this->custom_db->update_record('flight_cancellation_details', $data, ['passenger_fk' => $passenger_origin]);
        } catch (Exception $e) {
            log_message('error', "Failed to update cancellation details for passenger_origin: $passenger_origin. Error: " . $e->getMessage());
        }
        return;
    }

    // If record doesn't exist, insert new cancellation details
    $data['passenger_fk'] = $passenger_origin;
    $data['created_by_id'] = intval($this->entity_user_id ?? 0);
    $data['created_datetime'] = date('Y-m-d H:i:s');
    $data['cancellation_requested_on'] = date('Y-m-d H:i:s');

    // Error handling for inserting new record
    try {
        $this->custom_db->insert_record('flight_cancellation_details', $data);
    } catch (Exception $e) {
        log_message('error', "Failed to insert cancellation details for passenger_origin: $passenger_origin. Error: " . $e->getMessage());
    }
}
/**
 * Update Flight Booking Transaction Status
 */
public function update_flight_booking_transaction_cancel_status(int $transaction_origin): void
{
    try {
        // Check if there are any confirmed bookings
        $result = $this->custom_db->single_table_records(
            'flight_booking_passenger_details',
            '*',
            ['flight_booking_transaction_details_fk' => $transaction_origin, 'status' => 'BOOKING_CONFIRMED']
        );

        // If no active bookings exist, proceed with the transaction cancellation update
        if ($result['status'] == false) {
            // Log the status update attempt
            log_message('info', "Updating flight booking transaction status to 'BOOKING_CANCELLED' for transaction origin: $transaction_origin");

            // Check if the transaction is already in the cancelled state
            $current_status = $this->custom_db->single_table_records(
                'flight_booking_transaction_details',
                'status',
                ['origin' => $transaction_origin]
            );

            if ($current_status['status'] != 'BOOKING_CANCELLED') {
                // Proceed with the update if the status is not already 'BOOKING_CANCELLED'
                $this->custom_db->update_record('flight_booking_transaction_details', [
                    'status' => 'BOOKING_CANCELLED'
                ], ['origin' => $transaction_origin]);

                // Log the successful update
                log_message('info', "Successfully updated flight booking transaction status to 'BOOKING_CANCELLED' for transaction origin: $transaction_origin");
            } else {
                // Log if the transaction status was already 'BOOKING_CANCELLED'
                log_message('info', "Transaction origin $transaction_origin is already in 'BOOKING_CANCELLED' status.");
            }
        }
    } catch (Exception $e) {
        // Log any exceptions that occur
        log_message('error', "Error updating flight booking transaction status for transaction origin: $transaction_origin. Error: " . $e->getMessage());
    }
}
/**
 * Update Flight Booking Master Status
 */
public function update_flight_booking_cancel_status(string $app_reference): void
{
    try {
        // Check if there are any confirmed bookings
        $result = $this->custom_db->single_table_records(
            'flight_booking_passenger_details',
            '*',
            ['app_reference' => $app_reference, 'status' => 'BOOKING_CONFIRMED']
        );

        // If no active bookings exist, proceed with the booking cancellation update
        if ($result['status'] == false) {
            // Log the status update attempt
            log_message('info', "Updating flight booking status to 'BOOKING_CANCELLED' for app_reference: $app_reference");

            // Check if the booking status is already 'BOOKING_CANCELLED'
            $current_status = $this->custom_db->single_table_records(
                'flight_booking_details',
                'status',
                ['app_reference' => $app_reference]
            );

            // Proceed with the update if the status is not already 'BOOKING_CANCELLED'
            if ($current_status['status'] != 'BOOKING_CANCELLED') {
                // Update the flight booking details
                $this->custom_db->update_record('flight_booking_details', [
                    'status' => 'BOOKING_CANCELLED'
                ], ['app_reference' => $app_reference]);

                // Log the successful update
                log_message('info', "Successfully updated flight booking status to 'BOOKING_CANCELLED' for app_reference: $app_reference");
            } else {
                // Log if the booking status is already 'BOOKING_CANCELLED'
                log_message('info', "Booking with app_reference $app_reference is already in 'BOOKING_CANCELLED' status.");
            }
        }
    } catch (Exception $e) {
        // Log any exceptions that occur
        log_message('error', "Error updating flight booking status for app_reference: $app_reference. Error: " . $e->getMessage());
    }
}
/**
 * Update old booking app reference if not already set in DB
 */
public function update_old_booking_app_reference(string $new_app_reference, string $BookingId, string $PNR, int $domain_origin): bool
{
    // Check if the new_app_reference already exists in DB
    $new_booking_data = $this->db->query('SELECT * FROM flight_booking_details WHERE app_reference = ' . $this->db->escape($new_app_reference))->row_array();
    if (valid_array($new_booking_data)) {
        return false; // No update needed, new app reference already exists
    }

    // Start a database transaction for atomic updates
    $this->db->trans_start();

    // Retrieve the master booking details
    $mbooking_details = $this->db->query('
        SELECT FBD.app_reference, FBD.booking_source
        FROM flight_booking_details FBD
        JOIN flight_booking_transaction_details FBTD ON FBTD.app_reference = FBD.app_reference
        WHERE FBD.domain_origin = ' . intval($domain_origin) . '
        AND FBTD.book_id = ' . $this->db->escape($BookingId) . '
        AND FBTD.pnr = ' . $this->db->escape($PNR)
    )->row_array();

    if (!empty($mbooking_details)) {
        $old_app_reference = trim($mbooking_details['app_reference'] ?? '');
        $update_data = ['app_reference' => $new_app_reference];
        $update_condition = ['app_reference' => $old_app_reference];

        // Update all related tables with the new app reference
        foreach ([
            'flight_booking_details',
            'flight_booking_itinerary_details',
            'flight_booking_transaction_details',
            'flight_booking_passenger_details',
            'transaction_log'
        ] as $table) {
            $this->custom_db->update_record($table, $update_data, $update_condition);
        }
    }

    // Complete the transaction
    $this->db->trans_complete();

    // Check if the transaction was successful
    if ($this->db->trans_status() === false) {
        // If the transaction failed, roll it back and return false
        return false;
    }

    return true;
}
public function get_booking_details(string $app_reference, string $booking_source = '', string $booking_status = ''): array
{
    $response = ['status' => FAILURE_STATUS, 'data' => []];

    // Construct the base query for flight booking details
    $bd_query = $this->build_query('flight_booking_details', $app_reference, $booking_source, $booking_status);

    // Other queries for itinerary, transaction, customer details, cancellation, seat, baggage, and meal
    $queries = [
        'booking_details' => $bd_query,
        'booking_itinerary_details' => 'SELECT * FROM flight_booking_itinerary_details WHERE app_reference = ' . $this->db->escape($app_reference),
        'booking_transaction_details' => 'SELECT TD.*, BS.name AS booking_api_name FROM flight_booking_transaction_details TD LEFT JOIN booking_source BS ON BS.source_id = TD.booking_source WHERE TD.app_reference = ' . $this->db->escape($app_reference),
        'booking_customer_details' => 'SELECT CD.*, FPTI.TicketId, FPTI.TicketNumber, FPTI.IssueDate, FPTI.Fare, FPTI.SegmentAdditionalInfo FROM flight_booking_passenger_details CD LEFT JOIN flight_passenger_ticket_info FPTI ON CD.origin = FPTI.passenger_fk WHERE CD.flight_booking_transaction_details_fk IN (SELECT TD.origin FROM flight_booking_transaction_details TD WHERE TD.app_reference = ' . $this->db->escape($app_reference) . ' ORDER BY TD.sequence_number DESC)',
        'cancellation_details' => 'SELECT FCD.* FROM flight_booking_passenger_details CD LEFT JOIN flight_cancellation_details FCD ON FCD.passenger_fk = CD.origin WHERE CD.flight_booking_transaction_details_fk IN (SELECT TD.origin FROM flight_booking_transaction_details TD WHERE TD.app_reference = ' . $this->db->escape($app_reference) . ')',
        'seat_details' => 'SELECT FST.*, FP.flight_booking_transaction_details_fk FROM flight_booking_seat_details FST LEFT JOIN flight_booking_passenger_details FP ON FP.origin = FST.passenger_fk WHERE FP.app_reference = ' . $this->db->escape($app_reference),
        'baggage_details' => 'SELECT FBG.*, FP.flight_booking_transaction_details_fk FROM flight_booking_baggage_details FBG LEFT JOIN flight_booking_passenger_details FP ON FP.origin = FBG.passenger_fk WHERE FP.app_reference = ' . $this->db->escape($app_reference),
        'meal_details' => 'SELECT FML.*, FP.flight_booking_transaction_details_fk FROM flight_booking_meal_details FML LEFT JOIN flight_booking_passenger_details FP ON FP.origin = FML.passenger_fk WHERE FP.app_reference = ' . $this->db->escape($app_reference)
    ];

    // Execute all queries and populate the response data
    foreach ($queries as $key => $query) {
        $result = $this->db->query($query)->result_array();
        if (valid_array($result)) {
            $response['data'][$key] = $result;
        }
    }

    // Check if required data is valid and set status accordingly
    if (
        valid_array($response['data']['booking_details']) &&
        valid_array($response['data']['booking_itinerary_details']) &&
        valid_array($response['data']['booking_customer_details'])
    ) {
        $response['status'] = SUCCESS_STATUS;
    }

    return $response;
}

/**
 * Build query for the flight booking details table
 */
private function build_query(string $table, string $app_reference, string $booking_source, string $booking_status): string
{
    $query = 'SELECT BD.*, DL.domain_name, DL.origin AS domain_id 
              FROM ' . $table . ' AS BD 
              JOIN domain_list AS DL ON DL.origin = BD.domain_origin 
              WHERE BD.app_reference = ' . $this->db->escape($app_reference);

    if ($booking_source != '') {
        $query .= ' AND BD.booking_source = ' . $this->db->escape($booking_source);
    }
    if ($booking_status != '') {
        $query .= ' AND BD.status = ' . $this->db->escape($booking_status);
    }

    return $query;
}
public function get_flight_booking_transaction_details(string $app_reference, int $sequence_number, string $booking_source = '', string $booking_status = ''): array
{
    $response = ['status' => FAILURE_STATUS, 'data' => []];

    // Booking details query
    $bd_query = 'SELECT BD.*, DL.domain_name, DL.origin AS domain_id 
                 FROM flight_booking_details BD 
                 JOIN domain_list DL ON DL.origin = BD.domain_origin 
                 WHERE BD.app_reference = ' . $this->db->escape($app_reference);

    if ($booking_status != '') {
        $bd_query .= ' AND BD.status = ' . $this->db->escape($booking_status);
    }

    // Transaction details query
    $td_query = 'SELECT TD.*, CAST(TD.status AS UNSIGNED) AS status_code, BS.name AS booking_api_name 
                 FROM flight_booking_transaction_details TD 
                 LEFT JOIN booking_source BS ON BS.source_id = TD.booking_source 
                 WHERE TD.app_reference = ' . $this->db->escape($app_reference) . ' 
                 AND TD.sequence_number = ' . intval($sequence_number);

    if ($booking_source != '') {
        $td_query .= ' AND TD.booking_source = ' . $this->db->escape($booking_source);
    }

    // Execute queries
    $transaction_details = $this->db->query($td_query)->result_array();
    $transaction_origin = isset($transaction_details[0]['origin']) ? (int)$transaction_details[0]['origin'] : 0;

    // If no transaction found, exit early
    if ($transaction_origin === 0) {
        return $response;
    }

    // Additional queries for itinerary, customer details, cancellation, etc.
    $queries = [
        'booking_details' => $bd_query,
        'booking_transaction_details' => $td_query,
        'booking_itinerary_details' => 'SELECT * FROM flight_booking_itinerary_details WHERE app_reference = ' . $this->db->escape($app_reference) . ' AND flight_booking_transaction_details_fk = ' . $transaction_origin,
        'booking_customer_details' => 'SELECT CD.*, FPTI.TicketId, FPTI.TicketNumber, FPTI.IssueDate, FPTI.Fare, FPTI.SegmentAdditionalInfo 
                                      FROM flight_booking_passenger_details CD 
                                      LEFT JOIN flight_passenger_ticket_info FPTI ON CD.origin = FPTI.passenger_fk 
                                      WHERE CD.flight_booking_transaction_details_fk = ' . $transaction_origin,
        'cancellation_details' => 'SELECT FCD.* FROM flight_booking_passenger_details CD 
                                  LEFT JOIN flight_cancellation_details FCD ON FCD.passenger_fk = CD.origin 
                                  WHERE CD.flight_booking_transaction_details_fk = ' . $transaction_origin
    ];

    // Execute and store query results
    foreach ($queries as $key => $query) {
        $result = $this->db->query($query)->result_array();
        if (valid_array($result)) {
            $response['data'][$key] = $result;
        }
    }

    // Check if essential data is valid and update status
    if (
        valid_array($response['data']['booking_details']) &&
        valid_array($response['data']['booking_transaction_details']) &&
        valid_array($response['data']['booking_itinerary_details']) &&
        valid_array($response['data']['booking_customer_details'])
    ) {
        $response['status'] = SUCCESS_STATUS;
    }

    return $response;
}
public function add_extra_service_price_to_published_fare(string $app_reference, int $sequence_number): void
{
    // Start a database transaction for integrity
    $this->db->trans_begin();

    try {
        // Fetch the transaction details
        $transaction_data = $this->db->query(
            'SELECT * FROM flight_booking_transaction_details 
             WHERE app_reference = ' . $this->db->escape($app_reference) . ' 
             AND sequence_number = ' . $sequence_number
        )->row_array();

        // Check if transaction data is valid
        if (valid_array($transaction_data)) {
            // Get the extra service price for the transaction
            $extra_price = $this->transaction_wise_extra_service_total_price((int)$transaction_data['origin']);

            // Update the total fare with the extra service price
            $updated_fare = $transaction_data['total_fare'] + $extra_price;
            $this->custom_db->update_record('flight_booking_transaction_details', [
                'total_fare' => $updated_fare
            ], ['origin' => $transaction_data['origin']]);
        } else {
            throw new Exception('Transaction data not found.');
        }

        // Commit the transaction
        $this->db->trans_commit();
    } catch (Exception $e) {
        // Rollback in case of an error
        $this->db->trans_rollback();
        // Optionally log the error for debugging
        log_message('error', 'Failed to add extra service price: ' . $e->getMessage());
    }
}
public function remove_extra_service_price_to_published_fare(string $app_reference, int $sequence_number): void
{
    // Start a database transaction for integrity
    $this->db->trans_begin();

    try {
        // Fetch the transaction details
        $transaction_data = $this->db->query(
            'SELECT * FROM flight_booking_transaction_details 
             WHERE app_reference = ' . $this->db->escape($app_reference) . ' 
             AND sequence_number = ' . $sequence_number
        )->row_array();

        // Check if transaction data is valid
        if (valid_array($transaction_data)) {
            // Get the extra service price for the transaction
            $extra_price = $this->transaction_wise_extra_service_total_price((int)$transaction_data['origin']);

            // Ensure that the total fare is not less than zero
            $updated_fare = max(0, $transaction_data['total_fare'] - $extra_price);

            // Update the total fare with the reduced price
            $this->custom_db->update_record('flight_booking_transaction_details', [
                'total_fare' => $updated_fare
            ], ['origin' => $transaction_data['origin']]);
        } else {
            throw new Exception('Transaction data not found.');
        }

        // Commit the transaction
        $this->db->trans_commit();
    } catch (Exception $e) {
        // Rollback in case of an error
        $this->db->trans_rollback();
        // Optionally log the error for debugging
        log_message('error', 'Failed to remove extra service price: ' . $e->getMessage());
    }
}
public function transaction_wise_extra_service_total_price(int $transaction_origin): float
{
    // Combine all the queries into a single query to improve performance
    $query = '
        SELECT 
            SUM(FBG.price) AS baggage_total_price,
            SUM(FML.price) AS meal_total_price,
            SUM(FST.price) AS seat_total_price
        FROM flight_booking_passenger_details FP
        LEFT JOIN flight_booking_baggage_details FBG ON FP.origin = FBG.passenger_fk
        LEFT JOIN flight_booking_meal_details FML ON FP.origin = FML.passenger_fk
        LEFT JOIN flight_booking_seat_details FST ON FP.origin = FST.passenger_fk
        WHERE FP.flight_booking_transaction_details_fk = ' . $this->db->escape($transaction_origin);

    // Execute the combined query
    $result = $this->db->query($query)->row_array();

    // Sum up the prices, ensuring null values are treated as 0
    return floatval(($result['baggage_total_price'] ?? 0) + ($result['meal_total_price'] ?? 0) + ($result['seat_total_price'] ?? 0));
}
public function check_commision(string $carrier, int $is_domestic, string $booking_source)
{
    $module_type = $is_domestic == 1 ? 'domestic' : 'international';

    // Attempt to retrieve commission data based on carrier, booking source, and module type
    $query = 'SELECT * FROM b2b_flight_commission_details_new 
              WHERE module_type = ' . $this->db->escape($module_type) . ' 
              AND booking_source = ' . $this->db->escape($booking_source) . ' 
              AND airline_code = ' . $this->db->escape($carrier);

    $data = $this->db->query($query)->row();

    if (!empty($data)) {
        return $data;
    }

    // Fallback to generic commission details if no specific data found
    return $this->db->query('SELECT * FROM b2b_flight_commission_details_new WHERE type = "generic"')->row();
}
public function get_airport_timezone_offset(string $airport_code, string $journey_date): ?string
{
    // Validate the journey date format
    if (!$this->is_valid_date_format($journey_date)) {
        // Log or throw an error as appropriate
        return null;
    }

    $journey_month = (int)date('m', strtotime($journey_date));

    $query = 'SELECT FAT.timezone_offset 
              FROM flight_airport_list FAL 
              JOIN flight_airport_timezone_offset FAT ON FAT.flight_airport_list_fk = FAL.origin 
              WHERE FAL.airport_code = ' . $this->db->escape($airport_code) . ' 
              AND (FAT.start_month <= ' . $journey_month . ' AND FAT.end_month >= ' . $journey_month . ') 
              ORDER BY CASE 
                  WHEN FAT.start_month = ' . $journey_month . ' THEN 1 
                  WHEN FAT.end_month = ' . $journey_month . ' THEN 2 
                  ELSE 3 
              END';

    try {
        $result = $this->db->query($query)->row_array();
        return $result['timezone_offset'] ?? null;
    } catch (Exception $e) {
        // Log the exception or handle it appropriately
        return null;
    }
}

private function is_valid_date_format(string $date): bool
{
    return (bool)strtotime($date); // Returns true if the date format is valid
}
public function get_user_count_per_day_per_user(int $domain_origin): array
{
    // Get current date range for today
    $from = date('Y-m-d') . ' 00:00:00';
    $to = date('Y-m-d') . ' 23:59:59';

    // Use parameterized query to prevent SQL injection
    $query = 'SELECT COUNT(PRH.origin) AS totalcount 
              FROM provab_api_request_history AS PRH
              WHERE PRH.created_datetime BETWEEN ? AND ? 
              AND PRH.domain_origin = ?
              GROUP BY PRH.domain_origin';

    $get_hits_details = $this->db->query($query, [$from, $to, $domain_origin])->result_array();

    return $get_hits_details;
}
public function get_allowed_limit(): array
{
    // Query to fetch the allowed request limit from the database
    $session_id_details = $this->db->query('SELECT limit_request FROM set_request_limit')->row_array();

    // Check if result is empty or not
    if (empty($session_id_details)) {
        return ['status' => 'failure', 'message' => 'No request limit found'];
    }

    return $session_id_details;
}
public function get_allowed_limit_domain(int $domain_origin): array
{
    // Query to fetch the allowed request limit for the specific domain_origin
    $query = 'SELECT limit_request FROM set_request_limit WHERE domain_origin = ?';
    
    // Use parameterized query to prevent SQL injection
    $session_id_details = $this->db->query($query, [$domain_origin])->row_array();
    
    // Check if no result is found for the domain_origin
    if (empty($session_id_details)) {
        return ['status' => 'failure', 'message' => 'No request limit found for the specified domain'];
    }

    return $session_id_details;
}


}
