<?php
declare(strict_types=1);
/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Hotel Model
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */
Class Hotel_Model extends CI_Model
{
	private $master_search_data;
	/**
	 * return top destinations in hotel
	 */
	function hotel_top_destinations()
	{
		$query = 'Select CT.*, CN.name AS country from api_city_list CT, api_country_list CN where CT.country=CN.origin AND top_destination = '.ACTIVE;
		$data = $this->db->query($query)->result_array();
		return $data;
	}
		/*
	 *
	 * Get Hotel City List
	 *
	 */
	function get_hotel_city_list($search_chars)
{
    $raw_search_chars = $this->db->escape($search_chars);

    $r_search_chars = $this->db->escape($search_chars . '%');
    $search_chars = $this->db->escape($search_chars . '%');

    if (empty($search_chars)) {
        $r_search_chars = $this->db->escape($search_chars);
        $search_chars = $this->db->escape($search_chars);
    }

    $query = 'SELECT cm.country_name, cm.city_name, cm.origin, cm.country_code 
        FROM all_api_city_master AS cm 
        WHERE cm.city_name LIKE ' . $search_chars . ' 
        ORDER BY cm.cache_hotels_count DESC, CASE
            WHEN cm.city_name LIKE ' . $raw_search_chars . ' THEN 1
            WHEN cm.city_name LIKE ' . $r_search_chars . ' THEN 2
            WHEN cm.city_name LIKE ' . $search_chars . ' THEN 3
            ELSE 4 
        END, cm.cache_hotels_count DESC 
        LIMIT 0, 30';

    return $this->db->query($query)->result_array();
}

	function get_monthly_booking_summary($condition=array())
	{
		//Balu A
		$condition = $this->custom_db->get_custom_condition($condition);
		$query = 'select count(distinct(BD.app_reference)) AS total_booking,
				sum(HBID.total_fare+HBID.admin_markup+HBID.agent_markup) as monthly_payment, sum(HBID.admin_markup) as monthly_earning, 
				MONTH(BD.created_datetime) as month_number 
				from hotel_booking_details AS BD
				join hotel_booking_itinerary_details AS HBID on BD.app_reference=HBID.app_reference
				where (YEAR(BD.created_datetime) BETWEEN '.date('Y').' AND '.date('Y', strtotime('+1 year')).')  and
				BD.domain_origin='.get_domain_auth_id().' AND BD.created_by_id='.$GLOBALS['CI']->entity_user_id.' '.$condition.'
				GROUP BY YEAR(BD.created_datetime), 
				MONTH(BD.created_datetime)';
		return $this->db->query($query)->result_array();
	}

	/**
	 * get all the booking source which are active for current domain
	 */
	function active_booking_source()
	{
		$query = 'select BS.source_id, BS.origin from meta_course_list AS MCL, booking_source AS BS, activity_source_map AS ASM WHERE
		MCL.origin=ASM.meta_course_list_fk and ASM.booking_source_fk=BS.origin and MCL.course_id='.$this->db->escape(META_ACCOMODATION_COURSE).'
		and BS.booking_engine_status='.ACTIVE.' AND MCL.status='.ACTIVE.' AND ASM.status="active"';
		return $this->db->query($query)->result_array();
	}


	/**
	 * Get Hotel Booking By App_refernece
	 */
	function hotel_pnr_data($pnr){
		$cols = '
				BD.status, BD.app_reference, BD.booking_source, BD.booking_id, BD.booking_reference, BD.confirmation_reference, BD.total_fare,
				BD.domain_markup, BD.level_one_markup, BD.currency, BD.hotel_name, BD.star_rating, BD.phone_number, BD.hotel_check_in,
				BD.hotel_check_out, BD.payment_mode, BD.created_by_id, BD.created_datetime,
				count(distinct(CD.origin)) as total_passengers, count(distinct(ID.origin)) as total_rooms,
				concat(CD.title, " ", CD.first_name, " ", CD.middle_name, " ",CD.last_name) name, CD.email,
				POL.name as payment_name';
		$query = 'select '.$cols.' from hotel_booking_details AS BD, hotel_booking_pax_details AS CD, hotel_booking_itinerary_details AS ID
				,payment_option_list as POL where POL.payment_category_code=BD.payment_mode AND BD.app_reference=CD.app_reference AND BD.app_reference=ID.app_reference AND BD.domain_origin='.get_domain_auth_id().'
				AND BD.created_by_id ='.$GLOBALS['CI']->entity_user_id.' AND BD.app_reference ="'.$pnr.'"';
		return $this->db->query($query)->result_array();

	}

	/**
	 * get search data and validate it
	 */
	function get_safe_search_data($search_id)
{
    $search_data = $this->get_search_data($search_id);
    $success = true;
    $clean_search = array();

    if ($search_data != false) {
        $temp_search_data = json_decode($search_data['search_data'], true);

        $checkin = strtotime($temp_search_data['hotel_checkin']);
        $checkout = strtotime($temp_search_data['hotel_checkout']);
        $today = strtotime(date('Y-m-d'));

        if (($checkin > time() && $checkout > time()) || date('Y-m-d', $checkin) == date('Y-m-d')) {
            $clean_search['from_date'] = $temp_search_data['hotel_checkin'];
            $clean_search['to_date'] = $temp_search_data['hotel_checkout'];
            $clean_search['no_of_nights'] = abs(get_date_difference($clean_search['from_date'], $clean_search['to_date']));
        }

        if (!($checkin > time() && $checkout > time()) && date('Y-m-d', $checkin) != date('Y-m-d')) {
            $success = false;
        }

        if (isset($temp_search_data['hotel_destination'])) {
            $clean_search['hotel_destination'] = $temp_search_data['hotel_destination'];
        }

        if (isset($temp_search_data['city'])) {
            $clean_search['location'] = $temp_search_data['city'];
            $temp_location = explode('(', $temp_search_data['city']);
            $clean_search['city_name'] = trim($temp_location[0]);
            $clean_search['country_name'] = isset($temp_location[1]) ? trim($temp_location[1], '() ') : '';
        }

        if (!isset($temp_search_data['city'])) {
            $success = false;
        }

        if (isset($temp_search_data['rooms'])) {
            $clean_search['room_count'] = abs((int)$temp_search_data['rooms']);
        }

        if (!isset($temp_search_data['rooms'])) {
            $success = false;
        }

        if (isset($temp_search_data['adult'])) {
            $clean_search['adult_config'] = $temp_search_data['adult'];
        }

        if (!isset($temp_search_data['adult'])) {
            $success = false;
        }

        if (isset($temp_search_data['child'])) {
            $clean_search['child_config'] = $temp_search_data['child'];
        }

        if (valid_array($temp_search_data['child'])) {
            foreach ($temp_search_data['child'] as $tc_k => $tc_v) {
                if (intval($tc_v) > 0) {
                    foreach ($temp_search_data['childAge_' . ($tc_k + 1)] as $ic_v) {
                        $clean_search['child_age'][] = $ic_v;
                    }
                }
            }
        }
    }

    if ($search_data == false) {
        $success = false;
    }

    return array('status' => $success, 'data' => $clean_search);
}

	/**
	 * get search data without doing any validation
	 * @param $search_id
	 */
	function get_search_data($search_id)
{
    if (empty($this->master_search_data)) {
        $search_data = $this->custom_db->single_table_records(
            'search_history',
            '*',
            ['search_type' => META_ACCOMODATION_COURSE, 'origin' => $search_id]
        );
        if ($search_data['status'] == true) {
            $this->master_search_data = $search_data['data'][0];
        }
        if ($search_data['status'] != true) {
            return false;
        }
    }
    return $this->master_search_data;
}

	/**
	 * get hotel city id of tbo from tbo hotel city list
	 * @param string $city	  city name for which id has to be searched
	 * @param string $country country name in which the city is present
	 */
	function tbo_hotel_city_id($city, $country)
{
    $response['status'] = true;
    $response['data'] = array();

    $location_details = $this->custom_db->single_table_records(
        'hotels_city',
        'country_code, origin',
        ['city_name like' => $city, 'country_name like' => $country]
    );

    if ($location_details['status']) {
        $response['data'] = $location_details['data'][0];
    }

    if (!$location_details['status']) {
        $response['status'] = false;
    }

    return $response;
}

	/**
     * Save booking details.
     *
     * @param int|string $domain_origin
     * @param string $status
     * @param string $app_reference
     * @param string $booking_source
     * @param string $booking_id
     * @param string $booking_reference
     * @param string $confirmation_reference
     * @param string $hotel_name
     * @param float|int $star_rating
     * @param string $hotel_code
     * @param string|int $phone_number
     * @param string $alternate_number
     * @param string $email
     * @param DateTimeImmutable|string $hotel_check_in
     * @param DateTimeImmutable|string $hotel_check_out
     * @param string|null $check_in_time
     * @param string|null $check_out_time
     * @param string $payment_mode
     * @param string $attributes
     * @param int $created_by_id
     * @param string $transaction_currency
     * @param float $currency_conversion_rate
     * @param string $phone_code
     * @param string $supplier
     * @param string $part_pay
     * @param string $city_id
     * @return bool
     */
    public function save_booking_details(
        int|string $domain_origin,
        string $status,
        string $app_reference,
        string $booking_source,
        string $booking_id,
        string $booking_reference,
        string $confirmation_reference,
        string $hotel_name,
        float|int $star_rating,
        string $hotel_code,
        int|string $phone_number,
        string $alternate_number,
        string $email,
        string $hotel_check_in,
      	string $hotel_check_out,
        string $check_in_time,
        string $check_out_time,
        string $payment_mode,
        string $attributes,
        int $created_by_id,
        string $transaction_currency,
        float $currency_conversion_rate,
        string $phone_code,
        string $supplier = '',
        string $part_pay = '',
        string $city_id = ''
    ): array {
        

        $data = [
            'domain_origin' => $domain_origin,
            'status' => $status,
            'app_reference' => $app_reference,
            'booking_source' => $booking_source,
            'booking_id' => $booking_id,
            'booking_reference' => $booking_reference,
            'confirmation_reference' => $confirmation_reference,
            'hotel_name' => $hotel_name,
            'star_rating' => $star_rating,
            'hotel_code' => $hotel_code,
            'phone_number' => $phone_number,
            'alternate_number' => $alternate_number,
            'email' => $email,
            'hotel_check_in' => $hotel_check_in,
            'hotel_check_out' => $hotel_check_out,
            'payment_mode' => $payment_mode,
            'attributes' => $attributes,
            'created_by_id' => $created_by_id,
            'created_datetime' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'currency' => $transaction_currency,
            'currency_conversion_rate' => $currency_conversion_rate,
            'phone_code' => $phone_code,
            'city_id' => $city_id,
        ];
        // Optional fields that might be used later
        if (!empty($supplier)) {
            $data['supplier'] = $supplier;
        }
        if (!empty($supplier)) {
            $data['part_pay'] = $part_pay;
        }

        return $this->custom_db->insert_record('hotel_booking_details', $data);
    }

    /**
     * Save booking itinerary details.
     *
     * @param string $app_reference
     * @param string $location
     * @param DateTimeImmutable|string $check_in
     * @param DateTimeImmutable|string $check_out
     * @param string $room_type_name
     * @param string $bed_type_code
     * @param string $status
     * @param string $smoking_preference
     * @param float $total_fare
     * @param float $admin_markup
     * @param float $agent_markup
     * @param string $currency
     * @param string $attributes
     * @param float $RoomPrice
     * @param float $Tax
     * @param float $ExtraGuestCharge
     * @param float $ChildCharge
     * @param float $OtherCharges
     * @param float $Discount
     * @param float $ServiceTax
     * @param float $AgentCommission
     * @param float $AgentMarkUp
     * @param float $TDS
     * @param float $gst
     * @return bool
     */
    public function save_booking_itinerary_details(
        string $app_reference,
        string $location,
        string $check_in,
       	string $check_out,
        string $room_type_name,
        string $bed_type_code,
        string $status,
        string $smoking_preference,
        float $total_fare,
        float $admin_markup,
        float $agent_markup,
        string $currency,
        string $attributes,
        float $RoomPrice,
        float $Tax,
        float $ExtraGuestCharge,
        float $ChildCharge,
        float $OtherCharges,
        float $Discount,
        float $ServiceTax,
        float $AgentCommission,
        float $AgentMarkUp,
        float $TDS,
        float $gst
    ): array {

        $data = [
            'app_reference' => $app_reference,
            'location' => $location,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'room_type_name' => $room_type_name,
            'bed_type_code' => $bed_type_code,
            'status' => $status,
            'smoking_preference' => $smoking_preference,
            'total_fare' => $total_fare,
            'admin_markup' => $admin_markup,
            'agent_markup' => $agent_markup,
            'currency' => $currency,
            'attributes' => $attributes,
            'RoomPrice' => $RoomPrice,
            'Tax' => $Tax,
            'ExtraGuestCharge' => $ExtraGuestCharge,
            'ChildCharge' => $ChildCharge,
            'OtherCharges' => $OtherCharges,
            'Discount' => $Discount,
            'ServiceTax' => $ServiceTax,
            'AgentCommission' => $AgentCommission,
            'AgentMarkUp' => $AgentMarkUp,
            'TDS' => $TDS,
            'gst' => $gst,
        ];

        return $this->custom_db->insert_record('hotel_booking_itinerary_details', $data);
    }

    /**
     * Save booking passenger details.
     *
     * @param string $app_reference
     * @param string $title
     * @param string $first_name
     * @param string|null $middle_name
     * @param string $last_name
     * @param string|int $phone
     * @param string $email
     * @param string $pax_type
     * @param DateTimeImmutable|string $date_of_birth
     * @param string $passenger_nationality
     * @param string $passport_number
     * @param string $passport_issuing_country
     * @param DateTimeImmutable|string $passport_expiry_date
     * @param string $status
     * @param string $attributes
     * @param string $gender
     * @return bool
     */
    public function save_booking_pax_details(
        string $app_reference,
        string $title,
        string $first_name,
        ?string $middle_name,
        string $last_name,
        int|string $phone,
        string $email,
        string $pax_type,
       	string $date_of_birth,
        string $passenger_nationality,
        string $passport_number,
        string $passport_issuing_country,
        string $passport_expiry_date,
        string $status,
        string $attributes,
        string $gender
    ): array {
       
        $data = [
            'app_reference' => $app_reference,
            'title' => $title,
            'first_name' => $first_name,
            'middle_name' => $middle_name ?? $last_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'email' => $email,
            'pax_type' => $pax_type,
            'date_of_birth' => $date_of_birth,
            'passenger_nationality' => $passenger_nationality,
            'passport_number' => $passport_number,
            'passport_issuing_country' => $passport_issuing_country,
            'passport_expiry_date' => $passport_expiry_date,
            'status' => $status,
            'attributes' => $attributes,
            'gender' => $gender,
        ];

        return $this->custom_db->insert_record('hotel_booking_pax_details', $data);
    }
     /**
     * Return booking list
     * 
     * @param array<string, mixed> $condition
     * @param bool $count
     * @param int $offset
     * @param int $limit
     * @return array<string, mixed>|int
     */
    public function booking(array $condition = [], bool $count = false, int $offset = 0, int $limit = 1_000_000_000_000): array|int
    {
        $this->load->library('booking_data_formatter');
        $conditionSql = $this->custom_db->get_custom_condition($condition);

        if ($count) {
            $sql = 'SELECT COUNT(DISTINCT BD.app_reference) AS total_records
                    FROM hotel_booking_details BD
                    JOIN hotel_booking_itinerary_details HBID ON BD.app_reference = HBID.app_reference
                    JOIN payment_option_list POL ON BD.payment_mode = POL.payment_category_code
                    WHERE BD.domain_origin = ? AND BD.created_by_id = ? ' . $conditionSql;

            $data = $this->db->query($sql, [$this->domainOrigin, $this->createdById])->row_array();
            return (int) ($data['total_records'] ?? 0);
        }

        $response = [
            'status' => SUCCESS_STATUS,
            'data' => []
        ];

        $bd_query = 'select * from hotel_booking_details AS BD 
                        WHERE BD.domain_origin='.get_domain_auth_id().' and BD.created_by_id ='.$GLOBALS['CI']->entity_user_id.' '.$conditionSql.'
                        order by BD.origin desc limit '.$offset.', '.$limit;
        $bookingDetails = $this->db->query($bd_query)->result_array();
        $appReferenceIds = '';
        if(valid_array($bookingDetails)){
            $appReferenceIds = $this->booking_data_formatter->implode_app_reference_ids($bookingDetails);
        }

        $bookingItineraryDetails = [];
        $bookingCustomerDetails = [];
        $cancellationDetails = [];

        if ($appReferenceIds != '') {
            $idSql = "SELECT * FROM hotel_booking_itinerary_details ID WHERE ID.app_reference IN ($appReferenceIds)";
            $cdSql = "SELECT * FROM hotel_booking_pax_details CD WHERE CD.app_reference IN ($appReferenceIds)";
            $cancelSql = "SELECT * FROM hotel_cancellation_details HCD WHERE HCD.app_reference IN ($appReferenceIds)";

            $bookingItineraryDetails = $this->db->query($idSql)->result_array();
            $bookingCustomerDetails = $this->db->query($cdSql)->result_array();
            $cancellationDetails = $this->db->query($cancelSql)->result_array();
        }

        $response['data'] = [
            'booking_details' => $bookingDetails,
            'booking_itinerary_details' => $bookingItineraryDetails,
            'booking_customer_details' => $bookingCustomerDetails,
            'cancellation_details' => $cancellationDetails,
        ];

        return $response;
    }

    /**
     * Return Booking Details based on the app_reference passed
     * 
     * @param string $app_reference
     * @param string $booking_source
     * @param string $booking_status
     * @return array<string, mixed>
     */
    public function get_booking_details(string $app_reference, string $booking_source, string $booking_status = ''): array
    {
        $response = [
            'status' => FAILURE_STATUS,
            'data' => []
        ];

        $params = [$app_reference];
        $sql = 'SELECT * FROM hotel_booking_details BD WHERE BD.app_reference = ?';

        if ($booking_source != '') {
            $sql .= ' AND BD.booking_source = ?';
            $params[] = $booking_source;
        }

        if ($booking_status != '') {
            $sql .= ' AND BD.status = ?';
            $params[] = $booking_status;
        }

        $response['data']['booking_details'] = $this->db->query($sql, $params)->result_array();

        $response['data']['booking_itinerary_details'] = $this->db
            ->query('SELECT * FROM hotel_booking_itinerary_details ID WHERE ID.app_reference = ?', [$app_reference])
            ->result_array();

        $response['data']['booking_customer_details'] = $this->db
            ->query('SELECT * FROM hotel_booking_pax_details CD WHERE CD.app_reference = ?', [$app_reference])
            ->result_array();

        $response['data']['cancellation_details'] = $this->db
            ->query('SELECT * FROM hotel_cancellation_details HCD WHERE HCD.app_reference = ?', [$app_reference])
            ->result_array();

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
     * Return booking list with filtering
     * 
     * @param string $search_filter_condition
     * @param bool $count
     * @param int $offset
     * @param int $limit
     * @return array<string, mixed>|int
     */
    public function filter_booking_report(string $search_filter_condition = '', bool $count = false, int $offset = 0, int $limit = 1_000_000_000_000): array|int
    {
        $searchFilter = $search_filter_condition != '' ? ' AND ' . $search_filter_condition : '';

        if ($count) {
            $sql = 'SELECT COUNT(DISTINCT BD.app_reference) AS total_records
                    FROM hotel_booking_details BD
                    JOIN hotel_booking_itinerary_details HBID ON BD.app_reference = HBID.app_reference
                    JOIN payment_option_list POL ON BD.payment_mode = POL.payment_category_code
                    WHERE BD.domain_origin = ? AND BD.created_by_id = ? ' . $searchFilter;

            $data = $this->db->query($sql, [$this->domainOrigin, $this->createdById])->row_array();

            return (int) ($data['total_records'] ?? 0);
        }

        $response = [
            'status' => SUCCESS_STATUS,
            'data' => []
        ];

        $sql = 'SELECT * FROM hotel_booking_details BD
                WHERE BD.domain_origin = ? AND BD.created_by_id = ? ' . $searchFilter . '
                ORDER BY BD.origin DESC LIMIT ?, ?';

        $bookingDetails = $this->db->query($sql, [$this->domainOrigin, $this->createdById, $offset, $limit])->result_array();

        $appReferenceIds = $this->booking_data_formatter->implode_app_reference_ids($bookingDetails);

        $bookingItineraryDetails = [];
        $bookingCustomerDetails = [];

        if ($appReferenceIds != '') {
            $idSql = "SELECT * FROM hotel_booking_itinerary_details ID WHERE ID.app_reference IN ($appReferenceIds)";
            $cdSql = "SELECT * FROM hotel_booking_pax_details CD WHERE CD.app_reference IN ($appReferenceIds)";

            $bookingItineraryDetails = $this->db->query($idSql)->result_array();
            $bookingCustomerDetails = $this->db->query($cdSql)->result_array();
        }

        $response['data'] = [
            'booking_details' => $bookingDetails,
            'booking_itinerary_details' => $bookingItineraryDetails,
            'booking_customer_details' => $bookingCustomerDetails,
        ];

        return $response;
    }

    /**
     * Get static response JSON decoded
     * 
     * @param int $token_id
     * @return array<string, mixed>|null
     */
    public function get_static_response(int $token_id): ?array
    {
        $staticResponse = $this->custom_db->single_table_records('test', '*', ['origin' => $token_id]);

        return $staticResponse['data'][0]['test'] ?? null
            ? json_decode($staticResponse['data'][0]['test'], true, 512, JSON_THROW_ON_ERROR)
            : null;
    }
    /**
     * Save search data for future use - Analytics
     */
    public function saveSearchData(array $search_data, string $type): void
    {
        $data['domain_origin'] = get_domain_auth_id();
        $data['search_type'] = $type;
        $data['created_by_id'] = $this->entity_user_id ?? 0;
        $data['created_datetime'] = date('Y-m-d H:i:s');

        $temp_location = explode('(', $search_data['city'] ?? '');
        $data['city'] = trim($temp_location[0]);
        $data['country'] = isset($temp_location[1]) ? trim($temp_location[1], '() ') : '';

        $data['check_in'] = date('Y-m-d', strtotime($search_data['hotel_checkin'] ?? ''));
        $data['nights'] = abs(get_date_difference(
            $search_data['hotel_checkin'] ?? '',
            $search_data['hotel_checkout'] ?? ''
        ));
        $data['rooms'] = (int) ($search_data['rooms'] ?? 0);

        $adultCount = is_array($search_data['adult'] ?? null) ? array_sum($search_data['adult']) : 0;
        $childCount = is_array($search_data['child'] ?? null) ? array_sum($search_data['child']) : 0;
        $data['total_pax'] = $adultCount + $childCount;

        $this->custom_db->insert_record('search_hotel_history', $data);
    }

    /**
     * Clean up search data
     * @return array{data: array, status: bool}
     */
   public function cleanSearchData(array $temp_search_data): array
{
    $success = true;
    $clean_search = [];

    $checkinTimestamp = strtotime($temp_search_data['hotel_checkin'] ?? '');
    $checkoutTimestamp = strtotime($temp_search_data['hotel_checkout'] ?? '');

    if ($checkinTimestamp > time() && $checkoutTimestamp > time()) {
        $clean_search['from_date'] = $temp_search_data['hotel_checkin'];
        $clean_search['to_date'] = $temp_search_data['hotel_checkout'];
        $clean_search['no_of_nights'] = abs(get_date_difference(
            $clean_search['from_date'],
            $clean_search['to_date']
        ));
    }

    if (!($checkinTimestamp > time() && $checkoutTimestamp > time())) {
        $success = false;
    }

    // City and country
    if (!empty($temp_search_data['city'])) {
        $clean_search['location'] = $temp_search_data['city'];
        $temp_location = explode('(', $temp_search_data['city']);
        $clean_search['city_name'] = trim($temp_location[0]);
        $clean_search['country_name'] = isset($temp_location[1])
            ? trim(array_pop($temp_location), '() ')
            : '';
    }

    if (empty($temp_search_data['city'])) {
        $success = false;
    }

    // Occupancy
    if (isset($temp_search_data['rooms'])) {
        $clean_search['room_count'] = abs((int)$temp_search_data['rooms']);
    }

    if (!isset($temp_search_data['rooms'])) {
        $success = false;
    }

    if (isset($temp_search_data['adult'])) {
        $clean_search['adult_config'] = $temp_search_data['adult'];
    }

    if (!isset($temp_search_data['adult'])) {
        $success = false;
    }

    if (isset($temp_search_data['child'])) {
        $clean_search['child_config'] = $temp_search_data['child'];
    }

    if (valid_array($temp_search_data['child'] ?? [])) {
        foreach ($temp_search_data['child'] as $tc_k => $tc_v) {
            if ((int)$tc_v > 0) {
                $childAgeKey = 'childAge_' . ($tc_k + 1);
                if (!empty($temp_search_data[$childAgeKey]) && is_array($temp_search_data[$childAgeKey])) {
                    foreach ($temp_search_data[$childAgeKey] as $age) {
                        $clean_search['child_age'][] = $age;
                    }
                }
            }
        }
    }

    return ['data' => $clean_search, 'status' => $success];
}


    /**
     * Update Cancellation details and Status
     */
    public function updateCancellationDetails(string $AppReference, array $cancellation_details): void
    {
        $AppReference = trim($AppReference);
        $booking_status = 'BOOKING_CANCELLED';

        // 1. Add Cancellation details
        $this->updateCancellationRefundDetails($AppReference, $cancellation_details);

        // 2. Update Master Booking Status
        $this->custom_db->update_record('hotel_booking_details', ['status' => $booking_status], ['app_reference' => $AppReference]);

        // 3. Update Itinerary Status
        $this->custom_db->update_record('hotel_booking_itinerary_details', ['status' => $booking_status], ['app_reference' => $AppReference]);
    }

    /**
     * Add Cancellation details
     */
  private function updateCancellationRefundDetails(string $AppReference, array $cancellation_details): void
{
    $hotel_cancellation_details = [
        'app_reference' => $AppReference,
        'ChangeRequestId' => $cancellation_details['ChangeRequestId'] ?? null,
        'ChangeRequestStatus' => $cancellation_details['ChangeRequestStatus'] ?? null,
        'status_description' => $cancellation_details['StatusDescription'] ?? null,
        'API_RefundedAmount' => $cancellation_details['RefundedAmount'] ?? null,
        'API_CancellationCharge' => $cancellation_details['CancellationCharge'] ?? null,
    ];

    if (($cancellation_details['ChangeRequestStatus'] ?? 0) == 3) {
        $hotel_cancellation_details['cancellation_processed_on'] = date('Y-m-d H:i:s');
    }

    $cancel_details_exists = $this->custom_db->single_table_records('hotel_cancellation_details', '*', ['app_reference' => $AppReference]);

    if (($cancel_details_exists['status'] ?? false) == true) {
        // Update the data
        unset($hotel_cancellation_details['app_reference']);
        $this->custom_db->update_record('hotel_cancellation_details', $hotel_cancellation_details, ['app_reference' => $AppReference]);
        return;
    }

    // Insert data (when no existing record)
    $hotel_cancellation_details['created_by_id'] = $this->entity_user_id ?? 0;
    $hotel_cancellation_details['created_datetime'] = date('Y-m-d H:i:s');
    $hotel_cancellation_details['cancellation_requested_on'] = date('Y-m-d H:i:s');
    $this->custom_db->insert_record('hotel_cancellation_details', $hotel_cancellation_details);
}


    /**
     * Add hotel images if not exists
     */
    public function addHotelImages(int $sid, array $HotelPicture, string $HotelCode): void
    {
        $image_url = $this->custom_db->single_table_records('hotel_image_url', 'image_url', ['hotel_code' => $HotelCode]);

        if (($image_url['status'] ?? 0) == 0) {
            foreach ($HotelPicture as $key => $value) {
                $data = [
                    'image_url' => $value,
                    'ResultIndex' => $key,
                    'hotel_code' => $HotelCode,
                ];
                $this->custom_db->insert_record('hotel_image_url', $data);
            }
        }
    }

    /**
     * Get GST Details
     */
    public function getGstDetails(): array
    {
        $query = $this->db->query('
            SELECT gst.*, MCL.name AS name
            FROM gst_master gst
            LEFT JOIN meta_course_list MCL ON MCL.origin = gst.meta_course_list_fk
            WHERE MCL.course_id = ' . $this->db->escape(META_ACCOMODATION_COURSE)
        );

        if ($query->num_rows() > 0) {
            return [
                'status' => QUERY_SUCCESS,
                'data' => $query->result_array(),
            ];
        }

        return ['status' => QUERY_FAILURE];
    }
    /**
 * Save hotel search data for analytics.
 *
 * @param array $search_data
 * @param string $type
 * @return void
 */
public function save_search_data(array $search_data, string $type): void
{
    $data = [
        'domain_origin'     => get_domain_auth_id(),
        'search_type'       => $type,
        'created_by_id'     => (int)($this->entity_user_id ?? 0),
        'created_datetime'  => date('Y-m-d H:i:s'),
    ];

    // Parse city and country
    $temp_location = explode('(', $search_data['city'] ?? '');
    $data['city'] = trim($temp_location[0] ?? '');
    $data['country'] = isset($temp_location[1]) ? trim($temp_location[1], '() ') : '';

    // Parse dates
    $check_in = $search_data['hotel_checkin'] ?? '';
    $check_out = $search_data['hotel_checkout'] ?? '';

    $data['check_in'] = date('Y-m-d', strtotime($check_in));
    $data['nights'] = abs(get_date_difference($check_in, $check_out));

    // Pax and rooms
    $data['rooms'] = $search_data['rooms'] ?? 0;
    $data['total_pax'] = array_sum($search_data['adult'] ?? []) + array_sum($search_data['child'] ?? []);

    // Save to DB
    $this->custom_db->insert_record('search_hotel_history', $data);
}
public function get_gst_details(): array
    {
        $result = $this->db->query('SELECT gst.*,MCL.name as name FROM gst_master gst
            LEFT JOIN meta_course_list MCL ON MCL.origin=gst.meta_course_list_fk where MCL.course_id='.$this->db->escape(META_ACCOMODATION_COURSE));
        if ($result->num_rows() > 0) {
            return [
                'status' => QUERY_SUCCESS,
                'data' => $result->result_array()
            ];
        }

        return ['status' => QUERY_FAILURE];
    }
    /**
 * Add hotel images if they do not already exist.
 *
 * @param int|string $sid
 * @param array $hotelPictures
 * @param string $hotelCode
 * @return void
 */
public function add_hotel_images(mixed $sid, array $hotelPictures, string $hotelCode): void
{
    $existingImages = $this->custom_db->single_table_records('hotel_image_url','image_url',array('hotel_code'=>$hotelCode));   

    if (($existingImages['status'] ?? 0) === 0) {
        foreach ($hotelPictures as $key => $imageUrl) {
            $data['image_url'] = $value;
            $data['ResultIndex'] = $key;
                    $data['hotel_code'] = $HotelCode;
            $this->custom_db->insert_record('hotel_image_url', $data);
        }
    }
}

}