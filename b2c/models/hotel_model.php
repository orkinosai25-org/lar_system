<?php
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
	public function hotel_top_destinations(): array
    {
        $query = "
            SELECT CT.*, CN.country_name AS country
            FROM all_api_city_master CT
            JOIN api_country_master CN ON CT.country_code = CN.iso_country_code
            WHERE top_destination = " . ACTIVE;

        return $this->db->query($query)->result_array();
    }

    public function get_hotel_city_list(string $search_chars): array
    {
        $raw_search_chars = $this->db->escape($search_chars);
        $r_search_chars = $this->db->escape($search_chars . '%');
        $search_chars = empty($search_chars) ? $this->db->escape($search_chars) : $this->db->escape($search_chars . '%');

        $query = "
            SELECT cm.country_name, cm.city_name, cm.origin, cm.country_code
            FROM all_api_city_master AS cm
            WHERE cm.city_name LIKE {$search_chars}
            ORDER BY cm.cache_hotels_count DESC,
                CASE
                    WHEN cm.city_name LIKE {$raw_search_chars} THEN 1
                    WHEN cm.city_name LIKE {$r_search_chars} THEN 2
                    WHEN cm.city_name LIKE {$search_chars} THEN 3
                    ELSE 4
                END,
                cm.cache_hotels_count DESC
            LIMIT 0, 30
        ";

        return $this->db->query($query)->result_array();
    }

    public function get_hotel_city_list_base(string $search_chars): array
    {
        $raw_search_chars = $this->db->escape($search_chars);
        $r_search_chars = $this->db->escape($search_chars . '%');
        $search_chars = $this->db->escape('%' . $search_chars . '%');

        $query = "
            SELECT *
            FROM hotels_city
            WHERE city_name LIKE {$search_chars}
                OR country_name LIKE {$search_chars}
                OR country_code LIKE {$search_chars}
            ORDER BY top_destination DESC,
                CASE
                    WHEN city_name LIKE {$raw_search_chars} THEN 1
                    WHEN country_name LIKE {$raw_search_chars} THEN 2
                    WHEN country_code LIKE {$raw_search_chars} THEN 3
                    WHEN city_name LIKE {$r_search_chars} THEN 4
                    WHEN country_name LIKE {$r_search_chars} THEN 5
                    WHEN country_code LIKE {$r_search_chars} THEN 6
                    WHEN city_name LIKE {$search_chars} THEN 7
                    WHEN country_name LIKE {$search_chars} THEN 8
                    WHEN country_code LIKE {$search_chars} THEN 9
                    ELSE 10
                END,
                cache_hotels_count DESC
            LIMIT 0, 20
        ";

        return $this->db->query($query)->result_array();
    }

    public function active_booking_source(): array
    {
        $query = "
            SELECT BS.source_id, BS.origin
            FROM meta_course_list AS MCL
            JOIN activity_source_map AS ASM ON MCL.origin = ASM.meta_course_list_fk
            JOIN booking_source AS BS ON ASM.booking_source_fk = BS.origin
            WHERE MCL.course_id = " . $this->db->escape(META_ACCOMODATION_COURSE) . "
                AND BS.booking_engine_status = " . ACTIVE . "
                AND MCL.status = " . ACTIVE . "
                AND ASM.status = 'active'
        ";

        return $this->db->query($query)->result_array();
    }

    public function booking(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array|int
    {
        $condition_sql = $this->custom_db->get_custom_condition($condition);
        $domain_origin = get_domain_auth_id();
        $created_by = $GLOBALS['CI']->entity_user_id;

        if ($count) {
            $query = "
                SELECT COUNT(DISTINCT BD.app_reference) AS total_records
                FROM hotel_booking_details BD
                JOIN hotel_booking_itinerary_details HBID ON BD.app_reference = HBID.app_reference
                JOIN payment_option_list POL ON BD.payment_mode = POL.payment_category_code
                WHERE BD.domain_origin = {$domain_origin}
                    AND BD.created_by_id = {$created_by}
                    {$condition_sql}
            ";

            $data = $this->db->query($query)->row_array();
            return (int) $data['total_records'];
        }

        $this->load->library('booking_data_formatter');

        $response = [
            'status' => SUCCESS_STATUS,
            'data' => [
                'booking_details' => [],
                'booking_itinerary_details' => [],
                'booking_customer_details' => [],
                'cancellation_details' => [],
            ],
        ];

        $query = "
            SELECT *
            FROM hotel_booking_details BD
            WHERE BD.domain_origin = {$domain_origin}
                AND BD.created_by_id = {$created_by}
                {$condition_sql}
            ORDER BY BD.origin DESC
            LIMIT {$offset}, {$limit}
        ";

        $booking_details = $this->db->query($query)->result_array();
        $app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);

        if (!empty($app_reference_ids)) {
            $id_query = "SELECT * FROM hotel_booking_itinerary_details WHERE app_reference IN ({$app_reference_ids})";
            $cd_query = "SELECT * FROM hotel_booking_pax_details WHERE app_reference IN ({$app_reference_ids})";
            $cancellation_query = "SELECT * FROM hotel_cancellation_details WHERE app_reference IN ({$app_reference_ids})";

            $response['data']['booking_itinerary_details'] = $this->db->query($id_query)->result_array();
            $response['data']['booking_customer_details'] = $this->db->query($cd_query)->result_array();
            $response['data']['cancellation_details'] = $this->db->query($cancellation_query)->result_array();
        }

        $response['data']['booking_details'] = $booking_details;
        return $response;
    }
	public function booking_guest_user(string $app_reference, string $booking_source = '', string $booking_status = ''): array
	{
		$response = ['status' => FAILURE_STATUS, 'data' => []];
		$booking_itinerary_details = $booking_customer_details = $cancellation_details = [];

		$bd_query = 'SELECT * FROM hotel_booking_details AS BD 
        WHERE (BD.app_reference LIKE ' . $this->db->escape($app_reference) . ' 
        OR BD.booking_id LIKE ' . $this->db->escape($app_reference) . ')';

		if (!empty($booking_source)) {
			$bd_query .= ' AND BD.booking_source = ' . $this->db->escape($booking_source);
		}
		if (!empty($booking_status)) {
			$bd_query .= ' AND BD.status = ' . $this->db->escape($booking_status);
		}

		$booking_details = $this->db->query($bd_query)->result_array();
		$app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);

		if (!empty($app_reference_ids)) {
			$id_query = "SELECT * FROM hotel_booking_itinerary_details WHERE app_reference IN ($app_reference_ids)";
			$cd_query = "SELECT * FROM hotel_booking_pax_details WHERE app_reference IN ($app_reference_ids)";
			$cancellation_details_query = "SELECT * FROM hotel_cancellation_details WHERE app_reference IN ($app_reference_ids)";

			$booking_itinerary_details = $this->db->query($id_query)->result_array();
			$booking_customer_details = $this->db->query($cd_query)->result_array();
			$cancellation_details = $this->db->query($cancellation_details_query)->result_array();
		}

		$response['data'] = [
			'booking_details' => $booking_details,
			'booking_itinerary_details' => $booking_itinerary_details,
			'booking_customer_details' => $booking_customer_details,
			'cancellation_details' => $cancellation_details,
		];

		if (valid_array($booking_details) && valid_array($booking_itinerary_details) && valid_array($booking_customer_details)) {
			$response['status'] = SUCCESS_STATUS;
		}

		return $response;
	}

	/**
	 * Return Booking Details based on the app_reference passed
	 * @param $app_reference
	 * @param $booking_source
	 * @param $booking_status
	 */
	public function get_booking_details(string $app_reference, string $booking_source = '', string $booking_status = ''): array
	{
		$response = ['status' => FAILURE_STATUS, 'data' => []];

		$bd_query = 'SELECT * FROM hotel_booking_details AS BD WHERE BD.app_reference LIKE ' . $this->db->escape($app_reference);
		if (!empty($booking_source)) {
			$bd_query .= ' AND BD.booking_source = ' . $this->db->escape($booking_source);
		}
		if (!empty($booking_status)) {
			$bd_query .= ' AND BD.status = ' . $this->db->escape($booking_status);
		}

		$response['data'] = [
			'booking_details' => $this->db->query($bd_query)->result_array(),
			'booking_itinerary_details' => $this->db->query("SELECT * FROM hotel_booking_itinerary_details WHERE app_reference = " . $this->db->escape($app_reference))->result_array(),
			'booking_customer_details' => $this->db->query("SELECT * FROM hotel_booking_pax_details WHERE app_reference = " . $this->db->escape($app_reference))->result_array(),
			'cancellation_details' => $this->db->query("SELECT * FROM hotel_cancellation_details WHERE app_reference = " . $this->db->escape($app_reference))->result_array(),
		];

		if (valid_array($response['data']['booking_details']) && valid_array($response['data']['booking_itinerary_details']) && valid_array($response['data']['booking_customer_details'])) {
			$response['status'] = SUCCESS_STATUS;
		}

		return $response;
	}


	/**
	 * get search data and validate it
	 */
	public function get_safe_search_data(int $search_id): array
	{
		$search_data = $this->get_search_data($search_id);
		$success = true;
		$clean_search = [];

		if ($search_data != false) {
			$temp_search_data = json_decode($search_data['search_data'], true);
			$clean_result = $this->clean_search_data($temp_search_data);
			$success = $clean_result['status'];
			$clean_search = $clean_result['data'];
		}

		return ['status' => $success, 'data' => $clean_search];
	}



	/**
	 * Clean up search data
	 */
	public function clean_search_data(array $temp_search_data): array
	{
		$success = true;
		$clean_search = [];

		if (
			(strtotime($temp_search_data['hotel_checkin']) > time() && strtotime($temp_search_data['hotel_checkout']) > time())
			|| date('Y-m-d', strtotime($temp_search_data['hotel_checkin'])) == date('Y-m-d')
		) {
			$clean_search['from_date'] = $temp_search_data['hotel_checkin'];
			$clean_search['to_date'] = $temp_search_data['hotel_checkout'];
			$clean_search['no_of_nights'] = abs(get_date_difference($clean_search['from_date'], $clean_search['to_date']));
		}

		$success = $success && isset($clean_search['from_date']); // date validation failed

		if (isset($temp_search_data['hotel_destination'])) {
			$clean_search['hotel_destination'] = $temp_search_data['hotel_destination'];
		}

		if (isset($temp_search_data['city'])) {
			$clean_search['location'] = $temp_search_data['city'];
			$temp_location = explode('(', $temp_search_data['city']);
			$clean_search['city_name'] = trim($temp_location[0]);
			$clean_search['country_name'] = isset($temp_location[1]) ? trim(array_pop($temp_location), '() ') : '';
		}

		$success = $success && isset($clean_search['location']); // city not set

		if (isset($temp_search_data['rooms'])) {
			$clean_search['room_count'] = abs($temp_search_data['rooms']);
		}

		$success = $success && isset($clean_search['room_count']); // room count missing

		if (isset($temp_search_data['adult'])) {
			$clean_search['adult_config'] = $temp_search_data['adult'];
		}

		$success = $success && isset($clean_search['adult_config']); // adult config missing

		if (isset($temp_search_data['child'])) {
			$clean_search['child_config'] = $temp_search_data['child'];
		}

		if (isset($temp_search_data['hotelcode'])) {
			$clean_search['hotelcode'] = $temp_search_data['hotelcode'];
		}

		if (valid_array($temp_search_data['child'] ?? [])) {
			foreach ($temp_search_data['child'] as $tc_k => $tc_v) {
				if (intval($tc_v) > 0) {
					$child_key = 'childAge_' . ($tc_k + 1);
					$clean_search['child_age'] = $clean_search['child_age'] ?? [];
					$clean_search['child_age'][] = is_array($temp_search_data[$child_key]) && count($temp_search_data[$child_key]) > 1
						? array_merge($clean_search['child_age'], $temp_search_data[$child_key])
						: $temp_search_data[$child_key][0];
				}
			}
		}

		$clean_search['is_domestic'] = strtolower($clean_search['country_name'] ?? '') == 'india';

		if (($temp_search_data['search_type'] ?? '') == 'location_search') {
			$clean_search = array_merge($clean_search, array_intersect_key($temp_search_data, array_flip([
				'location',
				'latitude',
				'longitude',
				'radius',
				'countrycode'
			])));
		}

		$clean_search['search_type'] = $temp_search_data['search_type'] ?? '';

		return ['data' => $clean_search, 'status' => $success];
	}



	/**
	 * get search data without doing any validation
	 * @param $search_id
	 */
	public function get_search_data(int $search_id): array|false
	{
		if (empty($this->master_search_data)) {
			$search_data = $this->custom_db->single_table_records(
				'search_history',
				'*',
				['search_type' => META_ACCOMODATION_COURSE, 'origin' => $search_id]
			);

			if ($search_data['status']) {
				$this->master_search_data = $search_data['data'][0];
			}

			return $search_data['status'] ? $this->master_search_data : false;
		}

		return $this->master_search_data;
	}



	/**
	 * get hotel city id of tbo from tbo hotel city list
	 * @param string $city	  city name for which id has to be searched
	 * @param string $country country name in which the city is present
	 */
	public function tbo_hotel_city_id(string $city, string $country): array
	{
		$response = ['status' => true, 'data' => []];

		$location_details = $this->custom_db->single_table_records(
			'hotels_city',
			'country_code, origin',
			['city_name like' => $city, 'country_name like' => $country]
		);

		if ($location_details['status']) {
			$response['data'] = $location_details['data'][0];
		}

		$response['status'] = $location_details['status'] ? true : false;

		return $response;
	}



	/**
	 *
	 * @param number $domain_origin
	 * @param string $status
	 * @param string $app_reference
	 * @param string $booking_source
	 * @param string $booking_id
	 * @param string $booking_reference
	 * @param string $confirmation_reference
	 * @param number $total_fare
	 * @param number $domain_markup
	 * @param number $level_one_markup
	 * @param string $currency
	 * @param string $hotel_name
	 * @param number $star_rating
	 * @param string $hotel_code
	 * @param number $phone_number
	 * @param string $alternate_number
	 * @param string $email
	 * @param string $payment_mode
	 * @param string $attributes
	 * @param number $created_by_id
	 */
	/**
 * Save hotel booking details to the database.
 *
 * @return array Status of the insert operation
 */
	public function save_booking_details(
		int $domain_origin,
		string $status,
		string $app_reference,
		string $booking_source,
		string $booking_id,
		string $booking_reference,
		string $confirmation_reference,
		string $hotel_name,
		string $star_rating,
		string $hotel_code,
		string $phone_number,
		string $alternate_number,
		string $email,
		string $hotel_check_in,
		string $hotel_check_out,
		string $payment_mode,
		string $attributes,
		int $created_by_id,
		string $transaction_currency,
		float $currency_conversion_rate,
		string $phone_code,
		string $supplier = '',
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
			'phone_code' => $phone_code,
			'alternate_number' => $alternate_number,
			'email' => $email,
			'hotel_check_in' => $hotel_check_in,
			'hotel_check_out' => $hotel_check_out,
			'payment_mode' => $payment_mode,
			'attributes' => $attributes,
			'created_by_id' => $created_by_id,
			'city_id' => $city_id,
			'created_datetime' => date('Y-m-d H:i:s'),
			'currency' => $transaction_currency,
			'currency_conversion_rate' => $currency_conversion_rate,
		];

		// Optional fields that might be used later
		if (!empty($supplier)) {
			$data['supplier'] = $supplier;
		}

		return $this->custom_db->insert_record('hotel_booking_details', $data);
	}


	/**
	 *
	 * @param string $app_reference
	 * @param string $location
	 * @param date	 $check_in
	 * @param date	 $check_out
	 * @param string $room_type_name
	 * @param string $bed_type_code
	 * @param string $status
	 * @param string $smoking_preference
	 * @param string $attributes
	 */
	/**
 * Save itinerary details for a hotel booking.
 *
 * @return array Status of the insert operation
 */
	public function save_booking_itinerary_details(
		array $itinerary_data,
		array $itinerary_price,
		float $gst
	): array {
		$data = [
			'app_reference'       => $itinerary_data['app_reference'],
			'location'            => $itinerary_data['location'],
			'check_in'            => $itinerary_data['check_in'],
			'check_out'           => $itinerary_data['check_out'],
			'room_type_name'      => $itinerary_data['room_type_name'],
			'bed_type_code'       => $itinerary_data['bed_type_code'],
			'status'              => $itinerary_data['status'],
			'smoking_preference'  => $itinerary_data['smoking_preference'],
			'total_fare'          => $itinerary_data['total_fare'],
			'admin_markup'        => $itinerary_data['admin_markup'],
			'agent_markup'        => $itinerary_data['agent_markup'],
			'currency'            => $itinerary_data['currency'],
			'attributes'          => $itinerary_data['attributes'],
			'RoomPrice'           => $itinerary_price['RoomPrice'],
			'Tax'                 => $itinerary_price['Tax'],
			'ExtraGuestCharge'    => $itinerary_price['ExtraGuestCharge'],
			'ChildCharge'         => $itinerary_price['ChildCharge'],
			'OtherCharges'        => $itinerary_price['OtherCharges'],
			'Discount'            => $itinerary_price['Discount'],
			'ServiceTax'          => $itinerary_price['ServiceTax'],
			'AgentCommission'     => $itinerary_price['AgentCommission'],
			'AgentMarkUp'         => $itinerary_price['AgentMarkUp'],
			'TDS'                 => $itinerary_price['TDS'],
			'gst'                 => $gst,
		];

		return $this->custom_db->insert_record('hotel_booking_itinerary_details', $data);
	}


	/**
	 *
	 * @param $app_reference
	 * @param $title
	 * @param $first_name
	 * @param $middle_name
	 * @param $last_name
	 * @param $phone
	 * @param $email
	 * @param $pax_type
	 * @param $date_of_birth
	 * @param $passenger_nationality
	 * @param $passport_number
	 * @param $passport_issuing_country
	 * @param $passport_expiry_date
	 * @param $status
	 * @param $attributes
	 */
	/**
 * Save passenger (pax) details for a hotel booking.
 *
 * @return array Status of the insert operation
 */
	public function save_booking_pax_details(
		string $app_reference,
		array $personal_details,
		string $passenger_nationality,
		string $passport_number,
		string $passport_issuing_country,
		string $passport_expiry_date,
		string $status,
		string $attributes,
		string $gender
	): array {
		$data = [
			'app_reference'            => $app_reference,
			'title'                    => $personal_details['title'],
			'first_name'               => $personal_details['first_name'],
			'middle_name'              => empty($personal_details['middle_name']) ? $personal_details['last_name'] : $personal_details['middle_name'],
			'last_name'                => $personal_details['last_name'],
			'phone'                    => $personal_details['phone'],
			'email'                    => $personal_details['email'],
			'pax_type'                 => $personal_details['pax_type'],
			'date_of_birth'            => $personal_details['date_of_birth'],
			'passenger_nationality'    => $passenger_nationality,
			'passport_number'          => $passport_number,
			'passport_issuing_country' => $passport_issuing_country,
			'passport_expiry_date'     => $passport_expiry_date,
			'status'                   => $status,
			'gender'                   => $gender,
			'attributes'               => $attributes,
		];

		return $this->custom_db->insert_record('hotel_booking_pax_details', $data);
	}

	/**
	 *
	 */
	public function get_static_response(int $token_id): array
	{
		$static_response = $this->custom_db->single_table_records('test', '*', ['origin' => $token_id]);

		if (!empty($static_response['data'][0]['test'])) {
			return json_decode($static_response['data'][0]['test'], true);
		}

		return [];
	}


	/**
	 * SAve search data for future use - Analytics
	 * @param array $params
	 */
	/**
	 * Save search data for analytics and reporting
	 */
	public function save_search_data(array $search_data, string $type): void
	{
		$data = [
			'domain_origin'     => get_domain_auth_id(),
			'search_type'       => $type,
			'created_by_id'     => (int) ($this->entity_user_id ?? 0),
			'created_datetime'  => date('Y-m-d H:i:s'),
			'check_in'          => date('Y-m-d', strtotime($search_data['hotel_checkin'])),
			'nights'            => abs(get_date_difference($search_data['hotel_checkin'], $search_data['hotel_checkout'])),
			'rooms'             => $search_data['rooms'],
			'total_pax'         => array_sum($search_data['adult']) + array_sum($search_data['child']),
		];

		$temp_location = explode('(', $search_data['city']);
		$data['city'] = trim($temp_location[0]);
		$data['country'] = isset($temp_location[1]) ? trim($temp_location[1], '() ') : '';

		$this->custom_db->insert_record('search_hotel_history', $data);
	}

	/**
	 * Balu A
	 * Update Cancellation details and Status
	 * @param $AppReference
	 * @param $cancellation_details
	 */
	/**
 * Update cancellation status and refund info
 */
	public function update_cancellation_details(string $AppReference, array $cancellation_details): void
	{
		$AppReference = trim($AppReference);
		$booking_status = 'BOOKING_CANCELLED';

		$this->update_cancellation_refund_details($AppReference, $cancellation_details);

		$update_data = ['status' => $booking_status];
		$where = ['app_reference' => $AppReference];

		$this->custom_db->update_record('hotel_booking_details', $update_data, $where);
		$this->custom_db->update_record('hotel_booking_itinerary_details', $update_data, $where);
	}

	/**
	 * Add Cancellation details
	 * @param unknown_type $AppReference
	 * @param unknown_type $cancellation_details
	 */
	/**
 * Internal: Save or update cancellation refund data
 */
	private function update_cancellation_refund_details(string $AppReference, array $cancellation_details): void
	{
		$data = [
			'app_reference'           => $AppReference,
			'ChangeRequestId'         => $cancellation_details['ChangeRequestId'],
			'ChangeRequestStatus'     => $cancellation_details['ChangeRequestStatus'],
			'status_description'      => $cancellation_details['StatusDescription'],
			'API_RefundedAmount'      => $cancellation_details['RefundedAmount'] ?? null,
			'API_CancellationCharge'  => $cancellation_details['CancellationCharge'] ?? null,
		];

		if ((int)$cancellation_details['ChangeRequestStatus'] == 3) {
			$data['cancellation_processed_on'] = date('Y-m-d H:i:s');
		}

		$existing = $this->custom_db->single_table_records('hotel_cancellation_details', '*', ['app_reference' => $AppReference]);

		$is_update = !empty($existing['status']);

		if ($is_update) {
			unset($data['app_reference']);
		}

		$data += [
			'created_by_id' => (int)($this->entity_user_id ?? 0),
			'created_datetime' => date('Y-m-d H:i:s'),
			'cancellation_requested_on' => date('Y-m-d H:i:s')
		];

		$is_update
			? $this->custom_db->update_record('hotel_cancellation_details', $data, ['app_reference' => $AppReference])
			: $this->custom_db->insert_record('hotel_cancellation_details', $data);
	}


	/**
	*Image masking
	*/
	/**
 * Outputs a JPEG image directly to the browser.
 *
 * @param string $imagePath Path to the JPEG image file
 * @return void
 */
	public function setImgDownload(string $imagePath): void
	{
		if (!file_exists($imagePath) || !is_readable($imagePath)) {
			http_response_code(404);
			echo 'Image not found or inaccessible.';
			return;
		}

		$image = imagecreatefromjpeg($imagePath);

		if (!$image) {
			http_response_code(500);
			echo 'Failed to load image.';
			return;
		}

		header('Content-Type: image/jpeg');
		imagejpeg($image);
		imagedestroy($image);
	}

	public function add_hotel_images(array $HotelPicture, string $HotelCode): void
	{
		$image_url = $this->custom_db->single_table_records('hotel_image_url', 'image_url', ['hotel_code' => $HotelCode]);
	
		if ($image_url['status'] == 0) {
			foreach ($HotelPicture as $key => $value) {
				$data = [
					'image_url'  => $value,
					'ResultIndex'=> $key,
					'hotel_code' => $HotelCode,
				];
				$this->custom_db->insert_record('hotel_image_url', $data);
			}
		}
	}
	
	/**
	 * return booking list
	 */
	/**
 * Returns a list of hotel bookings with related itinerary, customer, and cancellation details.
 */
	public function get_booking_data(array $condition = [], int $offset = 0, int $limit = PHP_INT_MAX): array
	{
		$condition_sql = $this->custom_db->get_custom_condition($condition);
		$this->load->library('booking_data_formatter');

		$response = ['status' => SUCCESS_STATUS, 'data' => []];

		$booking_details_query = "
        SELECT * FROM hotel_booking_details AS BD 
        WHERE BD.domain_origin = " . get_domain_auth_id() . " {$condition_sql}
        ORDER BY BD.origin DESC 
        LIMIT {$offset}, {$limit}
    ";

		$booking_details = $this->db->query($booking_details_query)->result_array();
		$app_reference_ids = $this->booking_data_formatter->implode_app_reference_ids($booking_details);

		$booking_itinerary_details = [];
		$booking_customer_details = [];
		$cancellation_details = [];

		if (!empty($app_reference_ids)) {
			$booking_itinerary_details = $this->db
				->query("SELECT * FROM hotel_booking_itinerary_details WHERE app_reference IN ({$app_reference_ids})")
				->result_array();

			$booking_customer_details = $this->db
				->query("SELECT * FROM hotel_booking_pax_details WHERE app_reference IN ({$app_reference_ids})")
				->result_array();

			$cancellation_details = $this->db
				->query("SELECT * FROM hotel_cancellation_details WHERE app_reference IN ({$app_reference_ids})")
				->result_array();
		}

		$response['data'] = [
			'booking_details'            => $booking_details,
			'booking_itinerary_details' => $booking_itinerary_details,
			'booking_customer_details'  => $booking_customer_details,
			'cancellation_details'      => $cancellation_details,
		];

		return $response;
	}

	public function get_hotel_reviews(int|string $city_id): array
	{
		$query = "SELECT COUNT(*) AS count, hotel_code 
              FROM hotel_feedback 
              WHERE city_id = " . $this->db->escape($city_id) . " 
              GROUP BY hotel_code";

		return $this->db->query($query)->result_array();
	}

	public function get_hotel_reviews_by_hotel(string $hotel_code): array
	{
		$query = "SELECT COUNT(*) AS count, hotel_code 
              FROM hotel_feedback 
              WHERE hotel_code = " . $this->db->escape($hotel_code);

		return $this->db->query($query)->result_array();
	}

	public function get_gst_details(): array
	{
		$query = "
        SELECT gst.*, MCL.name AS name 
        FROM gst_master gst
        LEFT JOIN meta_course_list MCL ON MCL.origin = gst.meta_course_list_fk 
        WHERE MCL.course_id = " . $this->db->escape(META_ACCOMODATION_COURSE);

		$result = $this->db->query($query);

		if ($result->num_rows() > 0) {
			return [
				'status' => QUERY_SUCCESS,
				'data'   => $result->result_array(),
			];
		}

		return ['status' => QUERY_FAILURE];
	}

	
}
