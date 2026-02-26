<?php
declare(strict_types=1);
require_once 'transaction.php';

/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Flight Model
 */
class Flight_Model extends Transaction
{
    /**
     * Get Airport List
     */
   public function get_airport_list(string $search_chars): CI_DB_result
{
    $raw_search_chars = $this->db->escape($search_chars);

    $r_search_chars = $this->db->escape($search_chars . '%');
    $search_chars = !empty($search_chars)
        ? $this->db->escape('%' . $search_chars . '%')
        : $this->db->escape($search_chars);

    $query = "
        SELECT * FROM flight_airport_list
        WHERE airport_city LIKE {$search_chars}
           OR airport_code LIKE {$search_chars}
           OR country LIKE {$search_chars}
        ORDER BY top_destination DESC,
            CASE
                WHEN airport_code LIKE {$raw_search_chars} THEN 1
                WHEN airport_city LIKE {$raw_search_chars} THEN 2
                WHEN country LIKE {$raw_search_chars} THEN 3
                WHEN airport_code LIKE {$r_search_chars} THEN 4
                WHEN airport_city LIKE {$r_search_chars} THEN 5
                WHEN country LIKE {$r_search_chars} THEN 6
                WHEN airport_code LIKE {$search_chars} THEN 7
                WHEN airport_city LIKE {$search_chars} THEN 8
                WHEN country LIKE {$search_chars} THEN 9
                ELSE 10
            END
        LIMIT 0, 20
    ";

    return $this->db->query($query);
}

    public function get_monthly_booking_summary(): array
    {
        $current_year = (int)date('Y');
        $next_year = $current_year + 1;
        $domain_origin = (int)get_domain_auth_id();
        $user_id = (int)$GLOBALS['CI']->entity_user_id;

        $query = "
            SELECT COUNT(DISTINCT BD.app_reference) AS total_booking,
                   SUM(TD.total_fare + TD.admin_markup + BD.convinence_amount) AS monthly_payment,
                   SUM(TD.admin_markup + BD.convinence_amount) AS monthly_earning,
                   MONTH(BD.created_datetime) AS month_number
            FROM flight_booking_details AS BD
            JOIN flight_booking_transaction_details AS TD ON BD.app_reference = TD.app_reference
            WHERE YEAR(BD.created_datetime) BETWEEN {$current_year} AND {$next_year}
              AND BD.domain_origin = {$domain_origin}
              AND BD.created_by_id = {$user_id}
            GROUP BY YEAR(BD.created_datetime), MONTH(BD.created_datetime)
        ";

        return $this->db->query($query)->result_array();
    }

    public function booking(array $condition = [], bool $count = false, int $offset = 0, int $limit = PHP_INT_MAX): int|array
    {
        $domain_origin = (int)get_domain_auth_id();
        $user_id = (int)$GLOBALS['CI']->entity_user_id;
        $condition_string = $this->custom_db->get_custom_condition($condition);

        if ($count) {
            $query = "
                SELECT COUNT(DISTINCT BD.app_reference) AS total_records
                FROM flight_booking_details BD
                WHERE domain_origin = {$domain_origin}
                  AND BD.created_by_id = {$user_id}
                  {$condition_string}
            ";
            return (int)$this->db->query($query)->row_array()['total_records'];
        }

        $this->load->library('booking_data_formatter');

        $bd_query = "
            SELECT * FROM flight_booking_details AS BD
            WHERE BD.domain_origin = {$domain_origin}
              {$condition_string}
              AND BD.created_by_id = {$user_id}
            ORDER BY BD.created_datetime DESC, BD.origin DESC
            LIMIT {$offset}, {$limit}
        ";

        $booking_details = $this->db->query($bd_query)->result_array();
        $app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);

        $response = [
            'status' => SUCCESS_STATUS,
            'data' => [
                'booking_details' => $booking_details,
                'booking_itinerary_details' => [],
                'booking_transaction_details' => [],
                'booking_customer_details' => [],
                'cancellation_details' => [],
            ]
        ];

        if (!empty($app_reference_ids)) {
            $id_query = "SELECT * FROM flight_booking_itinerary_details WHERE app_reference IN ({$app_reference_ids})";
            $td_query = "SELECT * FROM flight_booking_transaction_details WHERE app_reference IN ({$app_reference_ids})";
            $cd_query = "
                SELECT CD.*, FPTI.TicketId, FPTI.TicketNumber, FPTI.IssueDate, FPTI.Fare, FPTI.SegmentAdditionalInfo
                FROM flight_booking_passenger_details AS CD
                LEFT JOIN flight_passenger_ticket_info FPTI ON CD.origin = FPTI.passenger_fk
                WHERE CD.flight_booking_transaction_details_fk IN (
                    SELECT TD.origin FROM flight_booking_transaction_details AS TD
                    WHERE TD.app_reference IN ({$app_reference_ids})
                )
            ";
            $cancellation_query = "
                SELECT FCD.*
                FROM flight_booking_passenger_details AS CD
                LEFT JOIN flight_cancellation_details AS FCD ON FCD.passenger_fk = CD.origin
                WHERE CD.flight_booking_transaction_details_fk IN (
                    SELECT TD.origin FROM flight_booking_transaction_details AS TD
                    WHERE TD.app_reference IN ({$app_reference_ids})
                )
            ";

            $response['data']['booking_itinerary_details'] = $this->db->query($id_query)->result_array();
            $response['data']['booking_transaction_details'] = $this->db->query($td_query)->result_array();
            $response['data']['booking_customer_details'] = $this->db->query($cd_query)->result_array();
            $response['data']['cancellation_details'] = $this->db->query($cancellation_query)->result_array();
        }

        return $response;
    }

    public function get_booking_details(string $app_reference, string $booking_source = '', string $booking_status = ''): array
    {
        $escaped_ref = $this->db->escape($app_reference);
        $response = ['status' => FAILURE_STATUS, 'data' => []];

        $bd_query = "SELECT * FROM flight_booking_details WHERE app_reference LIKE {$escaped_ref}";
        if (!empty($booking_source)) {
            $bd_query .= " AND booking_source = " . $this->db->escape($booking_source);
        }
        if (!empty($booking_status)) {
            $bd_query .= " AND status = " . $this->db->escape($booking_status);
        }

        $id_query = "SELECT * FROM flight_booking_itinerary_details WHERE app_reference = {$escaped_ref} ORDER BY origin ASC";
        $td_query = "SELECT * FROM flight_booking_transaction_details WHERE app_reference = {$escaped_ref} ORDER BY origin ASC";

        $cd_query = "
            SELECT DISTINCT CD.*, FPTI.api_passenger_origin, FPTI.TicketId, FPTI.TicketNumber, FPTI.IssueDate, FPTI.Fare, FPTI.SegmentAdditionalInfo
            FROM flight_booking_passenger_details CD
            LEFT JOIN flight_passenger_ticket_info FPTI ON CD.origin = FPTI.passenger_fk
            WHERE CD.flight_booking_transaction_details_fk IN (
                SELECT TD.origin FROM flight_booking_transaction_details TD
                WHERE TD.app_reference = {$escaped_ref}
            )
            ORDER BY origin ASC
        ";

        $cancellation_query = "
            SELECT FCD.*
            FROM flight_booking_passenger_details CD
            LEFT JOIN flight_cancellation_details FCD ON FCD.passenger_fk = CD.origin
            WHERE CD.flight_booking_transaction_details_fk IN (
                SELECT TD.origin FROM flight_booking_transaction_details TD
                WHERE TD.app_reference = {$escaped_ref}
            )
            ORDER BY origin ASC
        ";

        $baggage_query = "
            SELECT CD.flight_booking_transaction_details_fk, CONCAT(CD.first_name, ' ', CD.last_name) AS pax_name, FBG.*
            FROM flight_booking_passenger_details CD
            JOIN flight_booking_baggage_details FBG ON CD.origin = FBG.passenger_fk
            WHERE CD.flight_booking_transaction_details_fk IN (
                SELECT TD.origin FROM flight_booking_transaction_details TD
                WHERE TD.app_reference = {$escaped_ref}
            )
        ";

        $meal_query = "
            SELECT CD.flight_booking_transaction_details_fk, CONCAT(CD.first_name, ' ', CD.last_name) AS pax_name, FML.*
            FROM flight_booking_passenger_details CD
            JOIN flight_booking_meal_details FML ON CD.origin = FML.passenger_fk
            WHERE CD.flight_booking_transaction_details_fk IN (
                SELECT TD.origin FROM flight_booking_transaction_details TD
                WHERE TD.app_reference = {$escaped_ref}
            )
        ";

        $seat_query = "
            SELECT CD.flight_booking_transaction_details_fk, CONCAT(CD.first_name, ' ', CD.last_name) AS pax_name, FST.*
            FROM flight_booking_passenger_details CD
            JOIN flight_booking_seat_details FST ON CD.origin = FST.passenger_fk
            WHERE CD.flight_booking_transaction_details_fk IN (
                SELECT TD.origin FROM flight_booking_transaction_details TD
                WHERE TD.app_reference = {$escaped_ref}
            )
        ";

        $response['data']['booking_details'] = $this->db->query($bd_query)->result_array();
        $response['data']['booking_itinerary_details'] = $this->db->query($id_query)->result_array();
        $response['data']['booking_transaction_details'] = $this->db->query($td_query)->result_array();
        $response['data']['booking_customer_details'] = $this->db->query($cd_query)->result_array();
        $response['data']['cancellation_details'] = $this->db->query($cancellation_query)->result_array();
        $response['data']['baggage_details'] = $this->db->query($baggage_query)->result_array();
        $response['data']['meal_details'] = $this->db->query($meal_query)->result_array();
        $response['data']['seat_details'] = $this->db->query($seat_query)->result_array();
        if (valid_array($response['data']['booking_details']) == true and valid_array($response['data']['booking_itinerary_details']) == true and valid_array($response['data']['booking_customer_details']) == true) {
            $response['status'] = SUCCESS_STATUS;
        }
        return $response;
    }
    /**
     * Filter Flight Booking Report
     */
    public function filter_booking_report(string $search_filter_condition = '', bool $count = false, int $offset = 0, int $limit = PHP_INT_MAX): int|array
    {
        $CI = get_instance();

        $search_condition_sql = '';
        if (!empty($search_filter_condition)) {
            $search_condition_sql = ' AND ' . $search_filter_condition;
        }

        if ($count) {
            $query = '
                SELECT COUNT(DISTINCT(BD.app_reference)) AS total_records 
                FROM flight_booking_details AS BD
                WHERE BD.domain_origin = ' . (int)get_domain_auth_id() . ' 
                  AND BD.created_by_id = ' . (int)$CI->entity_user_id . '
                  AND BD.app_reference IN (
                      SELECT TD.app_reference 
                      FROM flight_booking_transaction_details AS TD 
                      WHERE 1=1 ' . $search_condition_sql . ')';

            $data = $this->db->query($query)->row_array();
            return (int)($data['total_records'] ?? 0);
        }

        $this->load->library('booking_data_formatter');

        $response = [
            'status' => SUCCESS_STATUS,
            'data' => [
                'booking_details' => [],
                'booking_itinerary_details' => [],
                'booking_transaction_details' => [],
                'booking_customer_details' => [],
                'cancellation_details' => [],
            ]
        ];

        $bd_query = '
            SELECT * 
            FROM flight_booking_details AS BD
            WHERE BD.domain_origin = ' . (int)get_domain_auth_id() . ' 
              AND BD.created_by_id = ' . (int)$CI->entity_user_id . '
              AND BD.app_reference IN (
                  SELECT TD.app_reference 
                  FROM flight_booking_transaction_details AS TD 
                  WHERE 1=1 ' . $search_condition_sql . ')';

        $booking_details = $this->db->query($bd_query)->result_array();
        $app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);

        if (!empty($app_reference_ids)) {
            // Itinerary Details
            $id_query = '
                SELECT * 
                FROM flight_booking_itinerary_details AS ID
                WHERE ID.app_reference IN (' . $app_reference_ids . ')';

            // Transaction Details
            $td_query = '
                SELECT * 
                FROM flight_booking_transaction_details AS TD
                WHERE TD.app_reference IN (' . $app_reference_ids . ')';

            // Customer & Ticket Info
            $cd_query = '
                SELECT CD.*, FPTI.TicketId, FPTI.TicketNumber, FPTI.IssueDate, FPTI.Fare, FPTI.SegmentAdditionalInfo
                FROM flight_booking_passenger_details AS CD
                LEFT JOIN flight_passenger_ticket_info FPTI ON CD.origin = FPTI.passenger_fk
                WHERE CD.flight_booking_transaction_details_fk IN (
                    SELECT TD.origin 
                    FROM flight_booking_transaction_details AS TD 
                    WHERE TD.app_reference IN (' . $app_reference_ids . ')
                )';

            // Cancellation Info
            $cancellation_query = '
                SELECT FCD.* 
                FROM flight_booking_passenger_details AS CD
                LEFT JOIN flight_cancellation_details AS FCD ON FCD.passenger_fk = CD.origin
                WHERE CD.flight_booking_transaction_details_fk IN (
                    SELECT TD.origin 
                    FROM flight_booking_transaction_details AS TD 
                    WHERE TD.app_reference IN (' . $app_reference_ids . ')
                )';

            $response['data']['booking_itinerary_details'] = $this->db->query($id_query)->result_array();
            $response['data']['booking_transaction_details'] = $this->db->query($td_query)->result_array();
            $response['data']['booking_customer_details'] = $this->db->query($cd_query)->result_array();
            $response['data']['cancellation_details'] = $this->db->query($cancellation_query)->result_array();
        }

        $response['data']['booking_details'] = $booking_details;

        return $response;
    }

    /**
     * Update Flight Booking Transaction Status if all passengers are cancelled
     */
    public function update_flight_booking_transaction_cancel_status(int $transaction_origin): void
    {
        $confirmed_passengers = $this->custom_db->single_table_records(
            'flight_booking_passenger_details',
            '*',
            [
                'flight_booking_transaction_details_fk' => $transaction_origin,
                'status' => 'BOOKING_CONFIRMED'
            ]
        );

        if ($confirmed_passengers['status'] == false) {
            $this->custom_db->update_record(
                'flight_booking_transaction_details',
                ['status' => 'BOOKING_CANCELLED'],
                ['origin' => $transaction_origin]
            );
        }
    }

    /**
     * Update Flight Booking Status if all passengers are cancelled
     */
    public function update_flight_booking_cancel_status(string $app_reference): void
    {
        $confirmed_passengers = $this->custom_db->single_table_records(
            'flight_booking_passenger_details',
            '*',
            [
                'app_reference' => $app_reference,
                'status' => 'BOOKING_CONFIRMED'
            ]
        );

        if ($confirmed_passengers['status'] == false) {
            $this->custom_db->update_record(
                'flight_booking_details',
                ['status' => 'BOOKING_CANCELLED'],
                ['app_reference' => $app_reference]
            );
        }
    }
    /**
	 * Check if destinations are domestic
	 */
	function is_domestic_flight(string $from_loc, array|string $to_loc): bool
{
    $ci = get_instance(); // Assuming CodeIgniter context

    $query = '';
    if (is_array($from_loc) || is_array($to_loc)) {
        // Multicity
        $airport_cities = array_unique(array_merge((array) $from_loc, (array) $to_loc));
        $airport_city_codes = implode(',', array_map(fn($code) => '"' . addslashes($code) . '"', $airport_cities));
        $query = "SELECT COUNT(*) AS total FROM flight_airport_list WHERE airport_code IN ($airport_city_codes) AND country != 'India'";
    }

    if (empty($query)) {
        // Oneway/RoundWay
        $from = $ci->db->escape($from_loc);
        $to = $ci->db->escape($to_loc);
        $query = "SELECT COUNT(*) AS total FROM flight_airport_list WHERE airport_code IN ($from, $to) AND country != 'India'";
    }

    $data = $ci->db->query($query)->row_array();
    return intval($data['total']) == 0;
}


	/**
	 * Safe Search data for calendar
	 */
function calendar_safe_search_data(array $search_data): array
{
    $safe_data = [];

    // Origin
    $from = $search_data['from'] ?? '';
    if (!empty($from)) {
        $safe_data['from'] = $safe_data['from_loc'] = substr(chop(substr($from, -5), ')'), -3);
        $safe_data['from_location'] = $from;
    }

    if (empty($from)) {
        $safe_data['from'] = $safe_data['from_loc'] = $safe_data['from_location'] = 'DEL';
    }

    // Destination
    $to = $search_data['to'] ?? '';
    if (!empty($to)) {
        $safe_data['to'] = $safe_data['to_loc'] = substr(chop(substr($to, -5), ')'), -3);
        $safe_data['to_location'] = $to;
    }

    if (empty($to)) {
        $safe_data['to'] = $safe_data['to_loc'] = $safe_data['to_location'] = 'BLR';
    }

    // Carrier
    $safe_data['carrier'] = (isset($search_data['carrier']) && is_array($search_data['carrier']))
        ? $search_data['carrier']
        : '';

    // Adult count
    $adult = isset($search_data['adult']) ? intval($search_data['adult']) : 0;
    $safe_data['adult'] = $adult > 0 ? $adult : 1;

    // Departure date
    $dep = $search_data['depature'] ?? '';
    $safe_data['depature'] = !empty($dep)
        ? date('Y-m', strtotime($dep)) . '-01'
        : date('Y-m-d');

    // Static defaults
    $safe_data['trip_type'] = 'OneWay';
    $safe_data['cabin'] = 'Economy';
    $safe_data['return'] = '';
    $safe_data['PromotionalPlanType'] = 'Normal';

    return $safe_data;
}

	public function clean_search_data(array $temp_search_data): array
	{
	    $clean_search = [];
	    $success = true;

	    if (!isset($temp_search_data['trip_type'])) {
	        return ['data' => [], 'status' => false];
	    }

	    $tripType = $temp_search_data['trip_type'];
	    $clean_search['trip_type'] = $tripType;

	    if ($tripType != 'multicity') {
	        $departure = $temp_search_data['depature'] ?? '';
	        $return = $temp_search_data['return'] ?? '';

	        if (
	            strtotime($departure) > time() ||
	            date('Y-m-d', strtotime($departure)) == date('Y-m-d')
	        ) {
	            $clean_search['depature'] = $departure;
	        } else {
	            $success = false;
	        }

	        if ($tripType == 'circle') {
	            $clean_search['trip_type_label'] = 'Round Way';

	            if (
	                strtotime($return) > time() &&
	                strtotime($return) >= strtotime($departure)
	            ) {
	                $clean_search['return'] = $return;
	            } else {
	                $success = false;
	            }
	        } else {
	            $clean_search['trip_type_label'] = 'One Way';
	        }

	        if (!empty($temp_search_data['from'])) {
	            $from = $temp_search_data['from'];
	            $clean_search['from'] = $from;
	            $clean_search['from_loc'] = substr(chop(substr($from, -5), ')'), -3);
	            $clean_search['from_loc_airport_name'] = $temp_search_data['from_loc_airport_name'] ?? '';
	            $clean_search['from_loc_id'] = $temp_search_data['from_loc_id'] ?? null;
	        } else {
	            $success = false;
	        }

	        if (!empty($temp_search_data['to'])) {
	            $to = $temp_search_data['to'];
	            $clean_search['to'] = $to;
	            $clean_search['to_loc'] = substr(chop(substr($to, -5), ')'), -3);
	            $clean_search['to_loc_airport_name'] = $temp_search_data['to_loc_airport_name'] ?? '';
	            $clean_search['to_loc_id'] = $temp_search_data['to_loc_id'] ?? null;
	        } else {
	            $success = false;
	        }

	        $clean_search['is_domestic'] = $this->is_domestic_flight(
	            $clean_search['from_loc'],
	            $clean_search['to_loc']
	        );

	    } else {
	        // Multicity
	        $clean_search['trip_type_label'] = 'Multi City';
	        $clean_search['depature'] = [];
	        $clean_search['from'] = [];
	        $clean_search['from_loc'] = [];
	        $clean_search['from_loc_id'] = [];
	        $clean_search['to'] = [];
	        $clean_search['to_loc'] = [];
	        $clean_search['to_loc_id'] = [];

	        $depatureDates = $temp_search_data['depature'] ?? [];
	        $fromList = $temp_search_data['from'] ?? [];
	        $toList = $temp_search_data['to'] ?? [];

	        for ($i = 0, $count = count($depatureDates); $i < $count; $i++) {
	            if (!$success) {
	                break;
	            }

	            $currentDeparture = $depatureDates[$i];
	            $prevDeparture = $depatureDates[$i - 1] ?? $currentDeparture;

	            if (
	                strtotime($currentDeparture) > time() ||
	                date('Y-m-d', strtotime($currentDeparture)) == date('Y-m-d') &&
	                strtotime($currentDeparture) >= strtotime(date('Y-m-d', strtotime($prevDeparture)))
	            ) {
	                $clean_search['depature'][$i] = $currentDeparture;
	            } else {
	                $success = false;
	            }

	            if (!empty($fromList[$i])) {
	                $from = $fromList[$i];
	                $clean_search['from'][$i] = $from;
	                $clean_search['from_loc'][$i] = substr(chop(substr($from, -5), ')'), -3);
	                $clean_search['from_loc_id'][$i] = $temp_search_data['from_loc_id'][$i] ?? null;
	            } else {
	                $success = false;
	            }

	            if (!empty($toList[$i])) {
	                $to = $toList[$i];
	                $clean_search['to'][$i] = $to;
	                $clean_search['to_loc'][$i] = substr(chop(substr($to, -5), ')'), -3);
	                $clean_search['to_loc_id'][$i] = $temp_search_data['to_loc_id'][$i] ?? null;
	            } else {
	                $success = false;
	            }
	        }

	        $clean_search['is_domestic'] = $this->is_domestic_flight(
	            $clean_search['from_loc'],
	            $clean_search['to_loc']
	        );
	    }

	    // Passenger info
	    $clean_search['adult_config'] = $temp_search_data['adult'] ?? null;
	    if ($clean_search['adult_config'] == null) {
	        $success = false;
	    }

	    $clean_search['child_config'] = $temp_search_data['child'] ?? 0;
	    $clean_search['infant_config'] = $temp_search_data['infant'] ?? 0;
	    $clean_search['v_class'] = $temp_search_data['v_class'] ?? '';
	    $clean_search['carrier'] = $temp_search_data['carrier'] ?? '';

	    return ['data' => $clean_search, 'status' => $success];
	}
	/**
     * Get search data and validate it.
     */
    public function get_safe_search_data(int|string $search_id): ?array
    {
        $search_data = $this->get_search_data($search_id);
        if ($search_data != false) {
            $temp_search_data = json_decode($search_data['search_data'], true);
            $clean_search = $this->clean_search_data($temp_search_data);
            return [
                'status' => $clean_search['status'],
                'data' => $clean_search['data']
            ];
        }
        return null;
    }

    /**
     * Get search data without doing any validation.
     */
    public function get_search_data(int|string $search_id): array|false
    {
        $search_data = $this->custom_db->single_table_records(
            'search_history',
            '*',
            ['search_type' => META_AIRLINE_COURSE, 'origin' => $search_id]
        );

        return $search_data['status'] == true ? $search_data['data'][0] : false;
    }

    /**
     * Get all the booking sources which are active for current domain.
     */
    public function active_booking_source(): array
    {
        $query = '
            SELECT BS.source_id, BS.origin
            FROM meta_course_list AS MCL
            JOIN activity_source_map AS ASM ON MCL.origin = ASM.meta_course_list_fk
            JOIN booking_source AS BS ON ASM.booking_source_fk = BS.origin
            WHERE MCL.course_id = ' . $this->db->escape(META_AIRLINE_COURSE) . '
            AND BS.booking_engine_status = ' . ACTIVE . '
            AND MCL.status = ' . ACTIVE . '
            AND ASM.status = "active"
        ';

        return $this->db->query($query)->result_array();
    }

    /**
     * TEMPORARY FUNCTION - Returns static response for test.
     */
    public function get_static_response(int|string $token_id): array
    {
        $static_response = $this->custom_db->single_table_records(
            'test',
            '*',
            ['origin' => (int) $token_id]
        );

        return json_decode($static_response['data'][0]['test'], true);
    }

    /**
     * Lock all the tables necessary for flight transaction to be processed.
     */
    public static function lock_tables(): void
    {
        $CI = &get_instance();
        $CI->db->query('LOCK TABLES domain_list AS DL WRITE, currency_converter AS CC WRITE');
    }

    /**
     * Save complete master transaction details of flight.
     */
    public function save_flight_booking_details(
        int $domain_origin,
        string $status,
        string $app_reference,
        string $cabin_class,
        string $booking_source,
        string $phone,
        ?string $alternate_number,
        string $email,
        string $journey_start,
        string $journey_end,
        string $journey_from,
        string $journey_to,
        string $payment_mode,
        string $attributes,
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
            'domain_origin' => $domain_origin,
            'status' => $status,
            'app_reference' => $app_reference,
            'booking_source' => $booking_source,
            'phone' => $phone,
            'alternate_number' => $alternate_number,
            'email' => $email,
            'journey_start' => $journey_start,
            'journey_end' => $journey_end,
            'journey_from' => $journey_from,
            'journey_to' => $journey_to,
            'payment_mode' => $payment_mode,
            'attributes' => $attributes,
            'created_by_id' => $created_by_id,
            'created_datetime' => date('Y-m-d H:i:s'),
            'from_loc' => $from_loc,
            'to_loc' => $to_loc,
            'trip_type' => $from_to_trip_type,
            'cabin_class' => $cabin_class,
            'currency' => $transaction_currency,
            'currency_conversion_rate' => $currency_conversion_rate,
            'gst_details' => $gst_details,
            'phone_code' => $phone_country_code
        ];

        $this->custom_db->insert_record('flight_booking_details', $data);
    }
    public function save_flight_booking_passenger_details(
        string $app_reference,
        string $passenger_type,
        bool $is_lead,
        string $title,
        string $first_name,
        ?string $middle_name,
        string $last_name,
        ?string $date_of_birth,
        string $gender,
        string $passenger_nationality,
        ?string $passport_number,
        ?string $passport_issuing_country,
        ?string $passport_expiry_date,
        string $status,
        string $attributes,
        int $flight_booking_transaction_details_fk
    ): array {
        $data = [
            'app_reference' => $app_reference,
            'passenger_type' => $passenger_type,
            'is_lead' => $is_lead,
            'title' => $title,
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'date_of_birth' => $date_of_birth ?? 'NULL',
            'gender' => $gender,
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

    public function save_passenger_ticket_info(int $passenger_fk): array
    {
        return $this->custom_db->insert_record('flight_passenger_ticket_info', [
            'passenger_fk' => $passenger_fk
        ]);
    }

    public function update_passenger_ticket_info(
        int $passenger_fk,
        string $TicketId,
        string $TicketNumber,
        string $IssueDate,
        float $Fare,
        string $SegmentAdditionalInfo,
        string $ValidatingAirline,
        string $CorporateCode,
        string $TourCode,
        string $Endorsement,
        string $Remarks,
        string $ServiceFeeDisplayType,
        int $api_passenger_origin
    ): void {
        $data = [
            'TicketId' => $TicketId,
            'api_passenger_origin' => $api_passenger_origin,
            'TicketNumber' => $TicketNumber,
            'IssueDate' => $IssueDate,
            'Fare' => $Fare,
            'SegmentAdditionalInfo' => $SegmentAdditionalInfo,
            'ValidatingAirline' => $ValidatingAirline,
            'CorporateCode' => $CorporateCode,
            'TourCode' => $TourCode,
            'Endorsement' => $Endorsement,
            'Remarks' => $Remarks,
            'ServiceFeeDisplayType' => $ServiceFeeDisplayType
        ];

        $condition = ['passenger_fk' => $passenger_fk];

        $this->custom_db->update_record('flight_passenger_ticket_info', $data, $condition);
    }

    public function save_flight_booking_transaction_details(
        string $app_reference,
        string $transaction_status,
        string $status_description,
        string $pnr,
        string $book_id,
        string $source,
        string $ref_id,
        string $attributes,
        int $sequence_number,
        string $currency,
        float $total_fare,
        float $admin_markup,
        float $agent_markup,
        float $admin_commission,
        float $agent_commission,
        string $getbooking_StatusCode,
        string $getbooking_Description,
        string $getbooking_Category,
        float $admin_tds,
        float $agent_tds,
        float $gst
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
            'currency' => $currency,
            'total_fare' => $total_fare,
            'admin_commission' => $admin_commission,
            'agent_commission' => $agent_commission,
            'admin_markup' => $admin_markup,
            'agent_markup' => $agent_markup,
            'getbooking_StatusCode' => $getbooking_StatusCode,
            'getbooking_Description' => $getbooking_Description,
            'getbooking_Category' => $getbooking_Category,
            'admin_tds' => $admin_tds,
            'agent_tds' => $agent_tds,
            'gst' => $gst
        ];

        return $this->custom_db->insert_record('flight_booking_transaction_details', $data);
    }

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
        string $FareRestriction,
        string $FareBasisCode,
        string $FareRuleDetail,
        string $airline_pnr,
        ?string $cabin_baggage,
        ?string $checkin_baggage,
        bool $is_refundable
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
     */
    public function savePassengerBaggageInfo(
        int|string $passengerFk,
        string $fromAirportCode,
        string $toAirportCode,
        string $description,
        float $price,
        string $code
    ): void {
        $data = [
            'passenger_fk' => $passengerFk,
            'from_airport_code' => $fromAirportCode,
            'to_airport_code' => $toAirportCode,
            'description' => $description,
            'price' => $price,
            'code' => $code
        ];

        $this->custom_db->insert_record('flight_booking_baggage_details', $data);
    }

    /**
     * Save Meal Information
     */
    public function savePassengerMealsInfo(
        int|string $passengerFk,
        string $fromAirportCode,
        string $toAirportCode,
        string $description,
        float $price,
        string $code,
        string $type = 'dynamic'
    ): void {
        $data = [
            'passenger_fk' => $passengerFk,
            'from_airport_code' => $fromAirportCode,
            'to_airport_code' => $toAirportCode,
            'description' => $description,
            'price' => $price,
            'code' => $code,
            'type' => $type
        ];

        $this->custom_db->insert_record('flight_booking_meal_details', $data);
    }

    /**
     * Save Seat Information
     */
    public function savePassengerSeatInfo(
        int|string $passengerFk,
        string $fromAirportCode,
        string $toAirportCode,
        string $description,
        float $price,
        string $code,
        string $type = 'dynamic',
        string $airlineCode = '',
        string $flightNumber = ''
    ): void {
        $data = [
            'passenger_fk' => $passengerFk,
            'from_airport_code' => $fromAirportCode,
            'to_airport_code' => $toAirportCode,
            'description' => $description,
            'price' => $price,
            'code' => $code,
            'type' => $type,
            'airline_code' => $airlineCode,
            'flight_number' => $flightNumber
        ];

        $this->custom_db->insert_record('flight_booking_seat_details', $data);
    }

    /**
     * Save search data for analytics
     */
    public function saveSearchData(array $searchData, string $type): void
    {
        $data = [
            'domain_origin' => get_domain_auth_id(),
            'search_type' => $type,
            'created_by_id' => $this->entity_user_id ?? 0,
            'created_datetime' => date('Y-m-d H:i:s')
        ];

        $from = is_array($searchData['from']) ? ($searchData['from'][0] ?? '') : $searchData['from'];
        $to = is_array($searchData['to']) ? (end($searchData['to']) ?: '') : $searchData['to'];

        [$data['from_location'], $data['from_code']] = $this->splitLocation($from);
        [$data['to_location'], $data['to_code']] = $this->splitLocation($to);

        $data['trip_type'] = $searchData['trip_type'] ?? '';
        $depature = $searchData['depature'] ?? '';
        $jDate = is_array($depature) ? ($depature[0] ?? '') : $depature;
        $data['journey_date'] = date('Y-m-d', strtotime($jDate));

        $data['total_pax'] = ($searchData['adult'] ?? 0) + ($searchData['child'] ?? 0) + ($searchData['infant'] ?? 0);

        $this->custom_db->insert_record('search_flight_history', $data);
    }

    private function splitLocation(string $location): array
    {
        $temp = explode('(', $location);
        $name = trim($temp[0]);
        $code = isset($temp[1]) ? trim($temp[1], '() ') : '';

        return [$name, $code];
    }
    public function get_baggage_total_price(string $app_reference): float
    {
       $query = 'select sum(FBG.price) as baggage_total_price
			from flight_booking_passenger_details FP
			left join flight_booking_baggage_details FBG on FP.origin=FBG.passenger_fk
			where FP.app_reference="'.$app_reference.'" group by FP.app_reference';
		$data = $this->db->query($query)->row_array();
		return floatval(@$data['baggage_total_price']);
    }

    public function get_meal_total_price(string $app_reference): float
    {
        $query = 'select sum(FML.price) as meal_total_price
			from flight_booking_passenger_details FP
			left join flight_booking_meal_details FML on FP.origin=FML.passenger_fk
			where FP.app_reference="'.$app_reference.'" group by FP.app_reference';
		$data = $this->db->query($query)->row_array();
		return floatval(@$data['meal_total_price']);
    }

    public function get_seat_total_price(string $app_reference): float
    {
       $query = 'select sum(FST.price) as seat_total_price
			from flight_booking_passenger_details FP
			left join flight_booking_seat_details FST on FP.origin=FST.passenger_fk
			where FP.app_reference="'.$app_reference.'" group by FP.app_reference';

        $data = $this->db->query($query)->row_array();
        return floatval($data['seat_total_price'] ?? 0);
    }

    public function add_extra_service_price_to_published_fare(string $app_reference): void
    {
        $query = "SELECT * FROM flight_booking_transaction_details WHERE app_reference = ? ORDER BY origin ASC";
        $transaction_data = $this->db->query($query, [$app_reference])->result_array();

        if (!empty($transaction_data)) {
            foreach ($transaction_data as $tr_v) {
                $transaction_origin = (int)$tr_v['origin'];
                $extra_service_total_price = $this->transaction_wise_extra_service_total_price($transaction_origin);

                $update_data = ['total_fare' => $tr_v['total_fare'] + $extra_service_total_price];
                $update_condition = ['origin' => $transaction_origin];

                $this->custom_db->update_record('flight_booking_transaction_details', $update_data, $update_condition);
            }
        }
    }

    public function transaction_wise_extra_service_total_price(int $transaction_origin): float
    {
        $baggage_price = $this->db->query(
            'SELECT SUM(FBG.price) AS baggage_total_price
             FROM flight_booking_passenger_details FP
             LEFT JOIN flight_booking_baggage_details FBG ON FP.origin = FBG.passenger_fk
             WHERE FP.flight_booking_transaction_details_fk = ?
             GROUP BY FP.flight_booking_transaction_details_fk',
            [$transaction_origin]
        )->row_array();

        $meal_price = $this->db->query(
            'SELECT SUM(FML.price) AS meal_total_price
             FROM flight_booking_passenger_details FP
             LEFT JOIN flight_booking_meal_details FML ON FP.origin = FML.passenger_fk
             WHERE FP.flight_booking_transaction_details_fk = ?
             GROUP BY FP.flight_booking_transaction_details_fk',
            [$transaction_origin]
        )->row_array();

        $seat_price = $this->db->query(
            'SELECT SUM(FST.price) AS seat_total_price
             FROM flight_booking_passenger_details FP
             LEFT JOIN flight_booking_seat_details FST ON FP.origin = FST.passenger_fk
             WHERE FP.flight_booking_transaction_details_fk = ?
             GROUP BY FP.flight_booking_transaction_details_fk',
            [$transaction_origin]
        )->row_array();

        return floatval(
            ($baggage_price['baggage_total_price'] ?? 0) +
            ($meal_price['meal_total_price'] ?? 0) +
            ($seat_price['seat_total_price'] ?? 0)
        );
    }

    public function add_flight_cancellation_details(int $pax_origin): void
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            'RequestId' => 1,
            'ChangeRequestStatus' => 1,
            'statusDescription' => 'Unassigned',
            'passenger_fk' => $pax_origin,
            'created_by_id' => $this->entity_user_id ?? 0,
            'created_datetime' => $now,
            'cancellation_requested_on' => $now,
        ];

        $this->custom_db->insert_record('flight_cancellation_details', $data);
    }

    public function get_gst_details(): array
    {
        $result = $this->db->query('SELECT gst.*,MCL.name as name FROM gst_master gst
			LEFT JOIN meta_course_list MCL ON MCL.origin=gst.meta_course_list_fk where MCL.course_id='.$this->db->escape(META_AIRLINE_COURSE));
		if ($result->num_rows() > 0) {
            return [
                'status' => QUERY_SUCCESS,
                'data' => $result->result_array()
            ];
        }

        return ['status' => QUERY_FAILURE];
    }
    /**
     * Save search data for future use - Analytics
     *
     * @param array $search_data
     * @param string $type
     * @return void
     */
    function save_search_data(array $search_data, string $type): void
    {
        $data = [
            'domain_origin'      => get_domain_auth_id(),
            'search_type'        => $type,
            'created_by_id'      => (int)($this->entity_user_id ?? 0),
            'created_datetime'   => date('Y-m-d H:i:s'),
        ];

        $from = is_array($search_data['from'] ?? null) ? ($search_data['from'][0] ?? '') : ($search_data['from'] ?? '');
        $to   = is_array($search_data['to'] ?? null)   ? end($search_data['to']) : ($search_data['to'] ?? '');

        $data['from_location'] = trim(explode('(', $from)[0] ?? '');
        $data['from_code']     = isset(explode('(', $from)[1])
            ? trim(trim(explode('(', $from)[1], ') '))
            : '';

        $data['to_location'] = trim(explode('(', $to)[0] ?? '');
        $data['to_code']     = isset(explode('(', $to)[1])
            ? trim(trim(explode('(', $to)[1], ') '))
            : '';

        $data['trip_type'] = $search_data['trip_type'] ?? '';

        $departure = is_array($search_data['depature'] ?? null)
            ? ($search_data['depature'][0] ?? '')
            : ($search_data['depature'] ?? '');

        $data['journey_date'] = !empty($departure) ? date('Y-m-d', strtotime($departure)) : null;

        $adult  = (int)($search_data['adult'] ?? 0);
        $child  = (int)($search_data['child'] ?? 0);
        $infant = (int)($search_data['infant'] ?? 0);

        $data['total_pax'] = $adult + $child + $infant;

        $this->custom_db->insert_record('search_flight_history', $data);
    }
    /**
     * Returns Airport timezone offset
     *
     * @param string $airport_code
     * @param string $journey_date
     * @return string|null
     */
    public function get_airport_timezone_offset(string $airport_code, string $journey_date): ?string
    {
        // Extract month from journey date
        $journey_month = (int)date('m', strtotime($journey_date));

        // Use query bindings to prevent SQL injection
        $query = "
            SELECT FAL.airport_code, FAT.start_month, FAT.end_month, FAT.timezone_offset 
            FROM flight_airport_list FAL
            JOIN flight_airport_timezone_offset FAT ON FAT.flight_airport_list_fk = FAL.origin
            WHERE FAL.airport_code = ? 
                AND (FAT.start_month <= ? AND FAT.end_month >= ?)
            ORDER BY 
                CASE
                    WHEN FAT.start_month = ? THEN 1
                    WHEN FAT.end_month = ? THEN 2
                    ELSE 3
                END
            LIMIT 1
        ";

        $bindings = [$airport_code, $journey_month, $journey_month, $journey_month, $journey_month];
        $result = $this->db->query($query, $bindings)->result_array();

        return $result[0]['timezone_offset'] ?? null;
    }
    /**
     * Get total price of extra services (Baggage, Meal, and Seats)
     *
     * @param string $app_reference
     * @return float
     */
    public function get_extra_services_total_price(string $app_reference): float
    {
        $baggage_total_price = $this->get_baggage_total_price($app_reference);
        $meal_total_price = $this->get_meal_total_price($app_reference);
        $seat_total_price = $this->get_seat_total_price($app_reference);

        $total_price = round($baggage_total_price + $meal_total_price + $seat_total_price, 2);

        return $total_price;
    }


}
