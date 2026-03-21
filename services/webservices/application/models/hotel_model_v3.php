<?php

/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Hotel Model
 * @author     Arjun J<arjunjgowda260389@gmail.com>
 * @version    V2
 */
Class Hotel_Model_V3 extends CI_Model
{
    private $master_search_data;

	/*
	 *
	 * Get Airport List
	 *
	 */

    function get_hotel_city_list(string $query): array
    {
        $this->db->like('country_name', $query)
                ->or_like('city_name', $query)
                ->or_like('country_code', $query)
                ->limit(10);

        return $this->db->get('hotels_city')->result_array();
    }
    
    /*
	*Get Hotel city List
	*/

    	function get_hotel_city_list_v3(){
		$city_data = $this->custom_db->single_table_records('all_api_city_master','origin as city_code,city_name,country_name,country_code');

		return $city_data['data'];
	}

    /**
	 * get all the booking source which are active for current domain
	 */

     function active_booking_source(): array
    {
        $query = 'SELECT BS.source_id, BS.origin 
                FROM meta_course_list AS MCL
                JOIN activity_source_map AS ASM ON MCL.origin = ASM.meta_course_list_fk
                JOIN booking_source AS BS ON ASM.booking_source_fk = BS.origin
                WHERE MCL.course_id = ' . $this->db->escape(META_ACCOMODATION_COURSE) . '
                AND BS.booking_engine_status = ' . ACTIVE . '
                AND MCL.status = ' . ACTIVE . '
                AND ASM.status = "active"';
        
        return $this->db->query($query)->result_array();
    }

    /**
	 * return booking list
	 */

     function booking(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array|int
    {
    if ($count) {
        // Counting total records
        $query = 'SELECT COUNT(*) AS total_records 
                  FROM hotel_booking_details BD 
                  WHERE domain_origin = ' . get_domain_auth_id() . ' 
                  AND BD.created_by_id = ' . $GLOBALS['CI']->entity_user_id;
        
        $data = $this->db->query($query)->row_array();
        return (int)$data['total_records']; // Return total count as an integer
    } else {
        // Columns to be selected in case of booking details
        $cols = '
            BD.status, BD.app_reference, BD.booking_source, BD.booking_id, BD.booking_reference, BD.confirmation_reference, BD.total_fare,
            BD.domain_markup, BD.level_one_markup, BD.currency, BD.hotel_name, BD.star_rating, BD.phone_number, BD.hotel_check_in,
            BD.hotel_check_out, BD.payment_mode, BD.created_by_id, BD.created_datetime,
            COUNT(DISTINCT(CD.origin)) AS total_passengers, COUNT(DISTINCT(ID.origin)) AS total_rooms,
            CONCAT(CD.title, " ", CD.first_name, " ", CD.middle_name, " ", CD.last_name) AS name, CD.email,
            POL.name AS payment_name';
        
        $query = 'SELECT ' . $cols . ' 
                  FROM hotel_booking_details AS BD
                  JOIN hotel_booking_pax_details AS CD ON BD.app_reference = CD.app_reference
                  JOIN hotel_booking_itinerary_details AS ID ON BD.app_reference = ID.app_reference
                  JOIN payment_option_list AS POL ON POL.payment_category_code = BD.payment_mode
                  WHERE BD.domain_origin = ' . get_domain_auth_id() . ' 
                  AND BD.created_by_id = ' . $GLOBALS['CI']->entity_user_id . ' 
                  GROUP BY BD.app_reference, CD.app_reference, ID.app_reference 
                  ORDER BY BD.origin DESC, ID.origin, CD.origin 
                  LIMIT ' . $offset . ', ' . $limit;
        
        return $this->db->query($query)->result_array(); // Return booking details as array
      }
    }
    /**
	 * get search data and validate it
	 */

    function get_safe_search_data($search_id): array
    {

        $search_data = $this->get_search_data($search_id);
        $success = true;
        $clean_search = [];

        if ($search_data != false) {
            $temp_search_data = json_decode($search_data['search_data'], true);

            $clean_search['from_date'] = $temp_search_data['hotel_checkin'];
            $clean_search['to_date'] = $temp_search_data['hotel_checkout'];
            $clean_search['no_of_nights'] = abs(get_date_difference($clean_search['from_date'], $clean_search['to_date']));

            if (isset($temp_search_data['city'])) {
                $clean_search['location'] = $temp_search_data['city'];
                $temp_location = explode('(', $temp_search_data['city']);
                $clean_search['city_name'] = trim($temp_location[0]);
                $clean_search['country_name'] = isset($temp_location[1]) ? trim($temp_location[1], '() ') : $temp_search_data['country_name'];
            } elseif (!(isset($temp_search_data['latitude']) && isset($temp_search_data['longitude']))) {
                $success = false;
            }

            if (!empty($temp_search_data['hotelcode'])) {
                $clean_search['hotelcode'] = $temp_search_data['hotelcode'];
            }

            if (isset($temp_search_data['search_type'])) {
                if ($temp_search_data['search_type'] == 'location_search') {
                    $clean_search['CountryCode'] = $temp_search_data['CountryCode'];
                } else {
                    $clean_search = array_merge($clean_search, [
                        'CountryCode' => $temp_search_data['CountryCode'],
                        'city_code' => $temp_search_data['city_code'],
                        'destination_code' => $temp_search_data['destination_code'],
                        'agoda_city_id' => $temp_search_data['agoda_city_id'],
                        'fab_city_id' => $temp_search_data['fab_city_id'],
                        'hb_city_id' => $temp_search_data['hb_city_id'],
                        'dida_city_id' => $temp_search_data['dida_city_id'],
                        'fab_state' => $temp_search_data['fab_state'],
                        'hotel_origin' => $temp_search_data['hotel_origin'],
                        'location_id' => $temp_search_data['hotel_destination'],
                        'ratehawk_id' => $temp_search_data['ratehawk_id']
                    ]);
                }
            } else {
                $clean_search = array_merge($clean_search, [
                    'CountryCode' => $temp_search_data['CountryCode'],
                    'city_code' => $temp_search_data['city_code'],
                    'destination_code' => $temp_search_data['destination_code'],
                    'hotel_origin' => $temp_search_data['hotel_origin'],
                    'agoda_city_id' => $temp_search_data['agoda_city_id'],
                    'location_id' => $temp_search_data['hotel_destination'],
                    'dida_city_id' => $temp_search_data['dida_city_id'],
                    'hb_city_id' => $temp_search_data['hb_city_id'],
                    'ratehawk_id' => $temp_search_data['ratehawk_id']
                ]);
            }

            if (isset($temp_search_data['rooms'])) {
                $clean_search['room_count'] = abs($temp_search_data['rooms']);
            } else {
                $success = false;
            }

            if (isset($temp_search_data['adult'])) {
                $clean_search['adult_config'] = $temp_search_data['adult'];
            } else {
                $success = false;
            }

            if (isset($temp_search_data['child'])) {
                $clean_search['child_config'] = $temp_search_data['child'];
            }

            if (isset($temp_search_data['guestnationality'])) {
                $clean_search['guestnationality'] = $temp_search_data['guestnationality'];
            }

            if (valid_array($temp_search_data['child'])) {
                foreach ($temp_search_data['child'] as $tc_k => $tc_v) {
                    if ((int)$tc_v > 0) {
                        foreach ($temp_search_data['childAge_' . ($tc_k + 1)] as $ic_v) {
                            $clean_search['child_age'][] = $ic_v;
                        }
                    }
                }
            }

            if (isset($temp_search_data['hotel_code']) && $temp_search_data['hotel_code']) {
                $clean_search['hotel_code'] = $temp_search_data['hotel_code'];
                $clean_search['hotel_name'] = $temp_search_data['hotel_name'];
            }

            $clean_search['room_config'] = $temp_search_data['room_config'];

            if (isset($temp_search_data['search_type']) && $temp_search_data['search_type'] == 'location_search') {
                $clean_search['latitude'] = $temp_search_data['latitude'];
                $clean_search['longitude'] = $temp_search_data['longitude'];
                $clean_search['radius'] = $temp_search_data['radius'];
            }

            $clean_search['search_type'] = $temp_search_data['search_type'] ?? null;

        } else {
            $success = false;
        }

        return ['status' => $success, 'data' => $clean_search];
    }

    function get_safe_search_data_grn($search_id): array
    {

        $search_data = $this->get_search_data($search_id);
        $success = true;
        $clean_search = [];

        if ($search_data != false) {
            $temp_search_data = json_decode($search_data['search_data'], true);

            $clean_search['from_date'] = $temp_search_data['hotel_checkin'];
            $clean_search['to_date'] = $temp_search_data['hotel_checkout'];
            $clean_search['no_of_nights'] = abs(get_date_difference($temp_search_data['hotel_checkin'], $temp_search_data['hotel_checkout']));

            if (isset($temp_search_data['city'])) {
                $clean_search['location'] = $temp_search_data['city'];
                $temp_location = explode('(', $temp_search_data['city']);
                $clean_search['city_name'] = trim($temp_location[0]);
                $clean_search['country_name'] = isset($temp_location[1]) ? trim($temp_location[1], '() ') : $temp_search_data['country_name'];
            } elseif (!(isset($temp_search_data['latitude']) && isset($temp_search_data['longitude']))) {
                $success = false;
            }

            $clean_search = array_merge($clean_search, [
                'CountryCode' => $temp_search_data['CountryCode'],
                'city_code' => $temp_search_data['city_code'],
                'destination_code' => $temp_search_data['destination_code'],
                'agoda_city_id' => $temp_search_data['agoda_city_id'],
                'fab_city_id' => $temp_search_data['fab_city_id'],
                'dida_city_id' => $temp_search_data['dida_city_id'],
                'hb_city_id' => $temp_search_data['hb_city_id'],
                'fab_state' => $temp_search_data['fab_state'],
                'hotel_origin' => $temp_search_data['hotel_origin'],
                'location_id' => $temp_search_data['hotel_destination'],
                'yatra_id' => $temp_search_data['yatra_id']
            ]);

            if (isset($temp_search_data['api_occurance'])) {
                $clean_search['api_occurance'] = $temp_search_data['api_occurance'];
            }

            if (isset($temp_search_data['hotel_code']) && !empty($temp_search_data['hotel_code'])) {
                $clean_search['hotel_code'] = $temp_search_data['hotel_code'];
                $clean_search['hotel_name'] = $temp_search_data['hotel_name'];
            }

            if (isset($temp_search_data['rooms'])) {
                $clean_search['room_count'] = abs($temp_search_data['rooms']);
            } else {
                $success = false;
            }

            if (isset($temp_search_data['adult'])) {
                $clean_search['adult_config'] = $temp_search_data['adult'];
            } else {
                $success = false;
            }

            if (isset($temp_search_data['child'])) {
                $clean_search['child_config'] = $temp_search_data['child'];
            }

            if (valid_array($temp_search_data['child'])) {
                foreach ($temp_search_data['child'] as $tc_k => $tc_v) {
                    if ((int)$tc_v > 0) {
                        foreach ($temp_search_data['childAge_' . ($tc_k + 1)] as $ic_v) {
                            $clean_search['child_age'][] = $ic_v;
                        }
                    }
                }
            }

            $clean_search['room_config'] = $temp_search_data['room_config'];

            if (isset($temp_search_data['search_type']) && $temp_search_data['search_type'] == 'location_search') {
                $clean_search['latitude'] = $temp_search_data['latitude'];
                $clean_search['longitude'] = $temp_search_data['longitude'];
                $clean_search['radius'] = $temp_search_data['radius'];
            }

            $clean_search['search_type'] = $temp_search_data['search_type'] ?? null;

        } else {
            $success = false;
        }

        return ['status' => $success, 'data' => $clean_search];
    }

    /**
	 * get search data without doing any validation
	 * @param $search_id
	 */

     function get_search_data($search_id): ?array
    {
        if (empty($this->master_search_data)) {
            $search_data = $this->custom_db->single_table_records('search_history', '*', ['search_type' => META_ACCOMODATION_COURSE, 'origin' => $search_id]);
            
            if ($search_data['status'] == true) {
                $this->master_search_data = $search_data['data'][0];
            } else {
                return null; // Return null instead of false when the search data isn't found
            }
        }

        return $this->master_search_data;
    }

    /**
	 * get hotel city id of tbo from tbo hotel city list
	 * @param string $city	  city name for which id has to be searched
	 * @param string $country country name in which the city is present
	 */

     function tbo_hotel_city_id(string $city, string $country): array
    {
        $response = [
            'status' => true,
            'data' => []
        ];

        $location_details = $this->custom_db->single_table_records(
            'hotels_city',
            'country_code, origin',
            ['city_name like' => $city, 'country_name like' => $country]
        );

        if ($location_details['status']) {
            $response['data'] = $location_details['data'][0];
            return $response;
        }

        $response['status'] = false;
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

        function save_booking_details(
            string $domain_origin,
            string $status,
            string $app_reference,
            string $booking_source,
            string $booking_id,
            string $booking_reference,
            string $confirmation_reference,
            float $total_fare,
            float $domain_markup,
            float $level_one_markup,
            string $currency,
            string $hotel_name,
            int $star_rating,
            string $hotel_code,
            string $phone_number,
            string $alternate_number,
            string $email,
            string $hotel_check_in,
            string $hotel_check_out,
            string $payment_mode,
            string $attributes,
            int $created_by_id,
            float $currency_conversion_rate = 1.0,
            string $hotel_version = HOTEL_VERSION_1,
            float $gst = 0.0,
            float $hotel_markup_price = 0.0,
            float $admin_markup_gst = 0.0
        ): array {
            // Default value for star_rating if empty
            if ($star_rating == 0) {
                $star_rating = 0;
            }

            $data = [
                'domain_origin' => $domain_origin,
                'status' => $status,
                'app_reference' => $app_reference,
                'booking_source' => $booking_source,
                'booking_id' => $booking_id,
                'booking_reference' => $booking_reference,
                'confirmation_reference' => $confirmation_reference,
                'total_fare' => $total_fare,
                'domain_markup' => $domain_markup,
                'level_one_markup' => $level_one_markup,
                'currency' => $currency,
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
                'created_datetime' => date('Y-m-d H:i:s'),
                'currency_conversion_rate' => $currency_conversion_rate,
                'version' => $hotel_version,
                'domain_gst' => $gst,
                'hotel_markup_price' => $hotel_markup_price,
                'admin_markup_gst' => $admin_markup_gst
            ];

            // Inserting the data into the database and returning the result
            $status = $this->custom_db->insert_record('hotel_booking_details', $data);
            return $status;
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



    function save_booking_itinerary_details(
        string $app_reference,
        string $location,
        string $check_in,
        string $check_out,
        string $room_type_name,
        string $bed_type_code,
        string $status,
        string $smoking_preference,
        float $total_fare,
        float $domain_markup,
        float $level_one_markup,
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
        float $commissionable_fare,
        float $commission_percentage
    ): array {
        // Prepare the data array for insertion
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
            'total_commissionable_fare' => $commissionable_fare,
            'domain_markup' => $domain_markup,
            'level_one_markup' => $level_one_markup,
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
            'CommissionPercenatge' => $commission_percentage,  // Fixed typo: CommissionPercenatge -> CommissionPercentage
            'AgentMarkUp' => $AgentMarkUp,
            'TDS' => $TDS
        ];

        // Perform the insert operation
        $status = $this->custom_db->insert_record('hotel_booking_itinerary_details', $data);
        
        // Return the result of the insert operation (true or false)
        return $status;
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

    function save_booking_pax_details(
        string $app_reference,
        string $title,
        string $first_name,
        string $middle_name,
        string $last_name,
        string $phone,
        string $email,
        string $pax_type,
        string $date_of_birth,
        string $passenger_nationality,
        string $passport_number,
        string $passport_issuing_country,
        string $passport_expiry_date,
        string $status,
        string $attributes='',
        string $pan_card_number=''
    ): array {
        // Prepare the data array for insertion
        $data = [
            'app_reference' => $app_reference,
            'title' => $title,
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'email' => $email,
            'pax_type' => $pax_type,
            'date_of_birth' => $date_of_birth,
            'passenger_nationality' => $passenger_nationality,
            'passport_number' => $passport_number,
            'passport_issuing_country' => $passport_issuing_country,
            'passport_expiry_date' => $passport_expiry_date,
            'pan_card_number' => $pan_card_number,
            'status' => $status,
            'attributes' => $attributes
        ];

        // Perform the insert operation into the database
        $status = $this->custom_db->insert_record('hotel_booking_pax_details', $data);
        
        // Return the result of the insert operation (true or false)
        return $status;
    }

    /**
	 * Return Booking Details based on the app_reference passed
	 * @param $app_reference
	 * @param $booking_source
	 * @param $booking_status
	 */

     function get_booking_details(
        string $app_reference, 
        string $booking_source, 
        string $booking_status = ''
    ): array {
        $response = [
            'status' => FAILURE_STATUS,
            'data' => []
        ];

        $bd_query = 'SELECT * FROM hotel_booking_details AS BD WHERE BD.app_reference LIKE ' . $this->db->escape($app_reference);
        if ($booking_source != '') {
            $bd_query .= ' AND BD.booking_source = ' . $this->db->escape($booking_source);
        }
        if ($booking_status != '') {
            $bd_query .= ' AND BD.status = ' . $this->db->escape($booking_status);
        }
        
        $id_query = 'SELECT * FROM hotel_booking_itinerary_details AS ID WHERE ID.app_reference = ' . $this->db->escape($app_reference);
        $cd_query = 'SELECT * FROM hotel_booking_pax_details AS CD WHERE CD.app_reference = ' . $this->db->escape($app_reference);

        $response['data']['booking_details'] = $this->db->query($bd_query)->row_array();
        $response['data']['booking_itinerary_details'] = $this->db->query($id_query)->result_array();
        $response['data']['booking_pax_details'] = $this->db->query($cd_query)->result_array();

        if (
            valid_array($response['data']['booking_details']) == true &&
            valid_array($response['data']['booking_itinerary_details']) == true &&
            valid_array($response['data']['booking_pax_details']) == true
        ) {
            $response['status'] = SUCCESS_STATUS;
        }

        return $response;
    }

    /**
	 * Returns Hotel Cancellation details
	 * @param unknown_type $app_reference
	 * @param unknown_type $ChangeRequestId
	 */

     function get_hotel_cancellation_details(
            string $app_reference, 
            string $ChangeRequestId, 
            int $domain_id
        ): array {
            $query = 'SELECT HCD.* FROM hotel_booking_details HB
                    JOIN hotel_cancellation_details HCD ON HCD.app_reference = HB.app_reference
                    WHERE HB.app_reference = ' . $this->db->escape($app_reference) . ' 
                    AND HB.domain_origin = ' . intval($domain_id) . ' 
                    AND HCD.ChangeRequestId = ' . $this->db->escape($ChangeRequestId);
            $details = $this->db->query($query)->result_array();
            return $details;
    }

    /**
	 *
	 */

    function get_static_response(int $token_id): array
    {
        $static_response = $this->custom_db->single_table_records('test', '*', ['origin' => $token_id]);
        return json_decode($static_response['data'][0]['test'], true);
    }

    /**
	 * SAve search data for future use - Analytics
	 * @param array $params
	 */

     function save_search_data(array $search_data, string $type): void
    {
		$data = [];
        $data['domain_origin'] = get_domain_auth_id();
        $data['search_type'] = $type;
        $data['created_by_id'] = intval($this->entity_user_id ?? 0);
        $data['created_datetime'] = date('Y-m-d H:i:s');

        $temp_location = explode('(', $search_data['city']);
        $data['city'] = trim($temp_location[0]);

        $data['country'] = isset($temp_location[1]) ? trim($temp_location[1], '() ') : '';

        $data['check_in'] = date('Y-m-d', strtotime($search_data['hotel_checkin']));
        $data['nights'] = abs(get_date_difference($search_data['hotel_checkin'], $search_data['hotel_checkout']));
        $data['rooms'] = $search_data['rooms'];
        $data['total_pax'] = array_sum($search_data['adult']) + array_sum($search_data['child']);

        $this->custom_db->insert_record('search_hotel_history', $data);
    }

    /**
	 * SAve search data for future use - Analytics
	 * @param array $params
	 */

     function save_search_history_data(array $search_request): array
    {
		$data = [];
        $data['status'] = SUCCESS_STATUS;
        $cache_key = $this->redis_server->generate_cache_key();
        $hotel_city_id = isset($search_request['CityId']) ? intval($search_request['CityId']) : 0;
        $number_of_nights = isset($search_request['NoOfNights']) ? intval($search_request['NoOfNights']) : 0;
        $number_of_adults = 0;
        $number_of_childs = 0;

        if (isset($search_request['RoomGuests'])) {
            foreach ($search_request['RoomGuests'] as $k => $v) {
                $number_of_adults += isset($v['NoOfAdults']) ? $v['NoOfAdults'] : 0;
                $number_of_childs += isset($v['NoOfChild']) ? $v['NoOfChild'] : 0;
            }
        }

        $hotel_city_details = $this->db->query('SELECT * FROM all_api_city_master WHERE origin=' . $hotel_city_id)->row_array();
        $hotel_check_in_date = isset($search_request['CheckInDate']) ? date('d-m-Y', strtotime($search_request['CheckInDate'])) : '';
        $hotel_check_out_date = date('d-m-Y', strtotime("+" . $number_of_nights . " days", strtotime($hotel_check_in_date)));

        $request = [];

        if (isset($search_request['hotelcode']) && !empty($search_request['hotelcode'])) {
            $request['hotelcode'] = $search_request['hotelcode'];
        }

        if (isset($search_request['search_type'])) {
            if ($search_request['search_type'] == 'location_search') {
                $request['latitude'] = $search_request['latitude'] ?? '';
                $request['longitude'] = $search_request['longitude'] ?? '';
                $request['radius'] = $search_request['radius'] ?? '';
                $request['CountryCode'] = $search_request['CountryCode'] ?? '';
            } else {
                $request['city'] = $hotel_city_details['city_name'] . '(' . $hotel_city_details['country_name'] . ')';
                $request['CountryCode'] = $hotel_city_details['country_code'];
                $request['country_name'] = $hotel_city_details['country_name'];
                $request['city_code'] = $hotel_city_details['grn_city_id'];
                $request['destination_code'] = $hotel_city_details['grn_destination_id'];
                $request['agoda_city_id'] = $hotel_city_details['agoda_city_id'];
                $request['fab_city_id'] = $hotel_city_details['fab_city_id'];
                $request['fab_state'] = $hotel_city_details['fab_state'];
                $request['hotel_destination'] = $hotel_city_details['tbo_city_id'];
                $request['dida_city_id'] = $hotel_city_details['dida_city_id'];
                $request['hb_city_id'] = $hotel_city_details['hb_city_id'];
                $request['hotel_origin'] = $hotel_city_details['id'];
                $request['ratehawk_id'] = $hotel_city_details['ratehawk_id'];
                $request['yatra_id'] = $hotel_city_details['yatra_id'];
            }
            $request['search_type'] = $search_request['search_type'];
        } else {
            $request['city'] = $hotel_city_details['city_name'] . '(' . $hotel_city_details['country_name'] . ')';
            $request['CountryCode'] = $hotel_city_details['country_code'];
            $request['country_name'] = $hotel_city_details['country_name'];
            $request['city_code'] = $hotel_city_details['grn_city_id'];
            $request['destination_code'] = $hotel_city_details['grn_destination_id'];
            $request['agoda_city_id'] = $hotel_city_details['agoda_city_id'];
            $request['fab_city_id'] = $hotel_city_details['fab_city_id'];
            $request['fab_state'] = $hotel_city_details['fab_state'];
            $request['hotel_destination'] = $hotel_city_details['tbo_city_id'];
            $request['dida_city_id'] = $hotel_city_details['dida_city_id'];
            $request['hb_city_id'] = $hotel_city_details['hb_city_id'];
            $request['ratehawk_id'] = $hotel_city_details['ratehawk_id'];
            $request['hotel_origin'] = $hotel_city_details['origin'];
            $request['yatra_id'] = $hotel_city_details['yatra_id'];
        }

        $request['hotel_checkin'] = $hotel_check_in_date;
        $request['hotel_checkout'] = $hotel_check_out_date;
        $request['rooms'] = $search_request['NoOfRooms'] ?? 0;
        $request['adult'] = $number_of_adults;
        $request['child'] = $number_of_childs;
        $request['room_config'] = $search_request['RoomGuests'] ?? [];
        $request['guestnationality'] = $search_request['GuestNationality'] ?? '';

        if (array_key_exists('api_occurance', $search_request)) {
            $request['api_occurance'] = $search_request['api_occurance'];
        }

        if (isset($search_request['HotelCode']) && !empty($search_request['HotelCode'])) {
            $request['hotel_code'] = $search_request['HotelCode'];
            $request['hotel_name'] = $search_request['HotelName'] ?? '';
        }

        $search_history_data = [];
        $search_history_data['domain_origin'] = get_domain_auth_id();
        $search_history_data['cache_key'] = $cache_key;
        $search_history_data['search_type'] = META_ACCOMODATION_COURSE;
        $search_history_data['search_data'] = json_encode($request);
        $search_history_data['created_datetime'] = db_current_datetime();

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
	 * Jaganath
	 * Update Cancellation details and Status
	 * @param $AppReference
	 * @param $cancellation_details
	 */

    public function update_cancellation_details(string $AppReference, array $cancellation_details): bool
    {
        $AppReference = trim($AppReference);
        $booking_status = 'BOOKING_CANCELLED';

        $this->update_cancellation_refund_details($AppReference, $cancellation_details);
        
        $updateBookingStatus = $this->custom_db->update_record(
            'hotel_booking_details',
            ['status' => $booking_status],
            ['app_reference' => $AppReference]
        );
        
        $updateItineraryStatus = $this->custom_db->update_record(
            'hotel_booking_itinerary_details',
            ['status' => $booking_status],
            ['app_reference' => $AppReference]
        );

        return $updateBookingStatus && $updateItineraryStatus;
    }

    /**
	 * Add Cancellation details
	 * @param unknown_type $AppReference
	 * @param unknown_type $cancellation_details
	 */

     private function update_cancellation_refund_details(string $AppReference, array $cancellation_details): bool
    {
        $cancellation_details = $cancellation_details['HotelChangeRequestStatusResult'];
        $hotel_cancellation_details = [
            'app_reference' => $AppReference,
            'ChangeRequestId' => $cancellation_details['ChangeRequestId'],
            'ChangeRequestStatus' => $cancellation_details['ChangeRequestStatus'],
            'status_description' => $cancellation_details['StatusDescription'],
            'API_RefundedAmount' => $cancellation_details['RefundedAmount'],
            'API_CancellationCharge' => $cancellation_details['CancellationCharge']
        ];

        if ($cancellation_details['ChangeRequestStatus'] == 3) {
            $hotel_cancellation_details['cancellation_processed_on'] = date('Y-m-d H:i:s');
            $attributes = [
                'CreditNoteNo' => $cancellation_details['CreditNoteNo'] ?? null,
                'CreditNoteCreatedOn' => $cancellation_details['CreditNoteCreatedOn'] ?? null
            ];
            $hotel_cancellation_details['attributes'] = json_encode($attributes);
        }

        $cancel_details_exists = $this->custom_db->single_table_records('hotel_cancellation_details', '*', ['app_reference' => $AppReference]);

        if ($cancel_details_exists['status']) {
            unset($hotel_cancellation_details['app_reference']);
            return $this->custom_db->update_record('hotel_cancellation_details', $hotel_cancellation_details, ['app_reference' => $AppReference]);
        }

        $hotel_cancellation_details['created_by_id'] = (int)($this->entity_user_id ?? 0);
        $hotel_cancellation_details['created_datetime'] = date('Y-m-d H:i:s');
        $hotel_cancellation_details['cancellation_requested_on'] = date('Y-m-d H:i:s');

        return $this->custom_db->insert_record('hotel_cancellation_details', $hotel_cancellation_details);
    }

    /**
	 * Update the Refund details
	 * @param unknown_type $app_reference
	 * @param unknown_type $refund_status
	 * @param unknown_type $refund_amount
	 * @param unknown_type $currency
	 * @param unknown_type $currency_conversion_rate
	 */

     function update_refund_details(string $app_reference, string $refund_status, float $refund_amount, float $cancellation_charge, string $currency, float $currency_conversion_rate): bool
    {
        $refund_details = array();
        $refund_details['refund_amount'] = $refund_amount;
        $refund_details['cancellation_charge'] = $cancellation_charge;
        $refund_details['refund_status'] = $refund_status;
        $refund_details['refund_payment_mode'] = 'online';
        $refund_details['currency'] = $currency;
        $refund_details['currency_conversion_rate'] = $currency_conversion_rate;
        $refund_details['refund_date'] = date('Y-m-d H:i:s');

        return $this->custom_db->update_record('hotel_cancellation_details', $refund_details, array('app_reference' => $app_reference));
    }

    /**
	 * Update the Old Booking App Reference
	 */

    public function update_old_booking_app_reference(string $new_app_reference, int $booking_id, int $domain_origin): bool
    {
        $new_booking_data = $this->db->query('SELECT * FROM hotel_booking_details WHERE app_reference = ' . $this->db->escape($new_app_reference))->row_array();

        if (valid_array($new_booking_data)) {
            return false;
        }

        $master_booking_details = $this->db->query('SELECT HBD.app_reference, HBD.booking_source FROM hotel_booking_details HBD WHERE HBD.domain_origin = ' . intval($domain_origin) . ' AND HBD.booking_id = ' . $this->db->escape($booking_id))->row_array();
        
        if (empty($master_booking_details)) {
            return false;
        }

        $old_app_reference = trim($master_booking_details['app_reference']);
        $booking_source = trim($master_booking_details['booking_source']);

        $update_data = ['app_reference' => $new_app_reference];
        $update_condition = ['app_reference' => $old_app_reference];

        $this->custom_db->update_record('hotel_booking_details', $update_data, $update_condition);
        $this->custom_db->update_record('hotel_booking_itinerary_details', $update_data, $update_condition);
        $this->custom_db->update_record('hotel_booking_pax_details', $update_data, $update_condition);
        $this->custom_db->update_record('transaction_log', $update_data, $update_condition);

        return true;
    }

    public function get_GRN_country_code(string $country_name): ?string
    {
        $get_grn_city_list = $this->db->query('SELECT * FROM api_country_master WHERE country_name = ' . $this->db->escape($country_name))->row_array();

        return $get_grn_city_list['iso_country_code'] ?? null;
    }

    public function get_hotel_list_code(string $country_code): array
    {
        $get_grn_hotel_code = $this->db->query('SELECT * FROM api_hotel_master WHERE country_code = ' . $this->db->escape($country_code))->result_array();

        $hotel_codes = [];
        if ($get_grn_hotel_code) {
            foreach ($get_grn_hotel_code as $key => $value) {
                if ($key < 3000) {
                    $hotel_codes[] = $value['hotel_code'];
                }
            }
        }
        
        return $hotel_codes;
    }

    /**
	*Getting city_code from grn connect
	*/

    public function get_grn_city_code(string $city_name, string $country_name): array
    {
        if ($city_name != '') {
            $get_iso_code = $this->get_GRN_country_code($country_name);
            
            $get_grn_city_list = $this->db->query('SELECT * FROM api_city_master WHERE country_code = ' . $this->db->escape($get_iso_code) . ' AND city_name LIKE ' . $this->db->escape('%' . $city_name . '%'))->row_array();

            if ($get_grn_city_list) {
                return [
                    'status' => 1,
                    'city_code' => $get_grn_city_list['city_code'],
                    'destination_code' => $get_grn_city_list['destination_code'],
                    'country_code' => $get_grn_city_list['country_code']
                ];
            }
        }
        
        return ['status' => 0];
    }

    public function get_grn_hotel_area_code(string $city_name, string $country_name): array
    {
        $get_iso_code = $this->get_GRN_country_code($country_name);

        $get_grn_area_list = $this->db->query('SELECT * FROM api_area_master WHERE country = ' . $this->db->escape($get_iso_code) . ' AND country_name LIKE ' . $this->db->escape('%' . $country_name . '%') . ' AND area_name LIKE ' . $this->db->escape('%' . $city_name . '%'))->row_array();

        if ($get_grn_area_list) {
            return [
                'status' => 1,
                'area_code' => $get_grn_area_list['area_code']
            ];
        }

        return ['status' => 0];
    }
    public function get_grn_destination_code(string $city_name, string $country_name): array
    {
        if ($city_name != '') {
            $get_iso_code = $this->get_GRN_country_code($country_name);
            
            $get_grn_city_list = $this->db->query('SELECT * FROM api_city_master WHERE country_code = ' . $this->db->escape($get_iso_code) . ' AND city_name LIKE ' . $this->db->escape('%' . $city_name . '%'))->row_array();

            if ($get_grn_city_list) {
                $iso_country_code = $get_grn_city_list['country_code'];

                $get_destination_list = $this->custom_db->single_table_records('api_city_master', '*', ['country_code' => $iso_country_code]);

                if ($get_destination_list['status'] == 1) {
                    if ($get_destination_list['data']) {
                        $destination_count = count($get_destination_list['data']);
                        $destination = $get_destination_list['data'][0];

                        if ($destination_count == 1) {
                            return [
                                'status' => 1,
                                'city_code' => $destination['city_code'],
                                'destination_code' => $destination['destination_code']
                            ];
                        } else {
                            return [
                                'status' => 0,
                                'city_code' => $get_grn_city_list['city_code']
                            ];
                        }
                    }
                } else {
                    return [
                        'status' => 0,
                        'city_code' => $get_grn_city_list['city_code']
                    ];
                }
            }
        }
    
        return ['status' => 0]; // Default return if no city found or other condition fails
    }
    //getting static trip advisior rating from table
    public function get_trip_advisor_data(string $hotel_code, string $city_code, string $country_code): array
    {
        $result_data = $this->db->query('SELECT hotel_code, city_code, tri_adv_hotel FROM grn_trip_advisor WHERE hotel_code = ' . $this->db->escape($hotel_code) . ' AND country_code = ' . $this->db->escape($country_code) . ' AND city_code = ' . $this->db->escape($city_code))->result_array();
        
        return $result_data;
    }

    public function get_trip_advisor_data_country(string $country_code): array
    {
        $result_data = $this->db->query('SELECT * FROM grn_trip_advisor WHERE country_code = ' . $this->db->escape($country_code))->result_array();
        
        return $result_data;
    }

    public function set_grn_room_boarding_details(string $hotel_code, string $boarding_details, string $city_code, string $country_code): void
    {
        $check_if_exists = $this->custom_db->single_table_records('grn_room_boarding_details', '*', ['hotel_code' => $hotel_code, 'city_code' => $city_code, 'country_code' => $country_code]);

        if ($check_if_exists['status'] == true) {
            $this->custom_db->update_record('grn_room_boarding_details', ['boarding_details' => $boarding_details], ['hotel_code' => $hotel_code, 'city_code' => $city_code, 'country_code' => $country_code]);
        } else {
            $insert_data = [
                'hotel_code' => $hotel_code,
                'boarding_details' => $boarding_details,
                'city_code' => $city_code,
                'country_code' => $country_code
            ];
            $this->custom_db->insert_record('grn_room_boarding_details', $insert_data);
        }
    }

    //get grn static image from database
    public function get_grn_master_image(string $hotel_code): array
    {
        $image_data = $this->db->query('SELECT path_name FROM api_grn_master_image USE INDEX (hotel_code) WHERE hotel_code = ' . $this->db->escape($hotel_code))->result_array();
        return $image_data;
    }

    // Get Agoda hotel code based on destination
    public function get_agoda_hotel_code(string $destination_code): array
    {
        $result_data = $this->db->query('SELECT DISTINCT(hotel_id) FROM agoda_hotel_master WHERE city_id = ' . $this->db->escape($destination_code))->result_array();
        return $result_data;
    }

    // Get payment details
    public function get_payment_details(): array
    {
        $payment_data = $this->db->query('SELECT * FROM payment_details')->row_array();
        return $payment_data;
    }

    // Get offline hotel APIs based on domain
    public function get_offline_hotel_api(string $domain_origin): array
    {
        $result_data = $this->db->query('SELECT booking_source_origin FROM offline_hotelapi_list WHERE domain_origin = ' . $this->db->escape($domain_origin) . ' AND status = 1')->result_array();
        return $result_data;
    }

    public function check_hotel_name(string $hotel_name): bool
    {
        $result_data = $this->db->query('SELECT * FROM oyo_hotel_details_live WHERE hotel_name LIKE "%' . $this->db->escape_like_str($hotel_name) . '%"')->num_rows();
        
        return $result_data > 0;
    }

    public function validate_city_id(string $CityId, string $CountryCode): array
    {
        $response = [
            'status' => true,
            'data' => []
        ];

        $location_details = $this->custom_db->single_table_records('all_api_city_master', 'country_code, origin', [
            'origin LIKE' => $CityId, 
            'country_code LIKE' => $CountryCode
        ]);

        if ($location_details['status']) {
            $response['data'] = $location_details['data'][0];
        } else {
            $response['status'] = false;
        }

        return $response;
    }

    // Get hotel bed hotel code based on destination
    public function get_HB_hotel_code(string $destination_code): array
    {
        $result_data = $this->db->query('SELECT hotel_code FROM master_hotel_details_beta_hb WHERE city_code = ' . $this->db->escape($destination_code))->result_array();
        return $result_data;
    }

}