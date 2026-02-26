<?php
declare(strict_types=1);
require_once BASEPATH . 'libraries/flight/Common_api_flight.php';
class Common_Flight {
	 public function __construct()
    {
        $this->ci = &get_instance();
    }
	/**
	 * Url to be used for combined flight booking - only for domestic round way
	 *
	 * @param number $search_id        	
	 */
	public static function combined_booking_url(int $search_id): string {
		return Common_Api_Flight::pre_booking_url ( $search_id );
	}
	
	/**
	 * Data gets saved in list so remember to use correct source value
	 *
	 * @param string $source
	 *        	source of the data - will be used as key while saving
	 * @param string $value
	 *        	value which has to be cached - pass json
	 */
	public static function insert_record(string $key, string $value): array {
		$ci = & get_instance ();
		
		$index = $ci->redis_server->store_list ( $key, $value );
		return array (
				'access_key' => $key . DB_SAFE_SEPARATOR . $index . DB_SAFE_SEPARATOR . random_string () . random_string (),
				'index' => $index 
		);
	}
	
	/**
	 */
	public static function read_record(string $key, int $offset = -1, int $limit = -1): array {
		$ci = & get_instance ();
		return $ci->redis_server->read_list ( $key, $offset, $limit );
	}
	
	/**
	 * Cache the data
	 *
	 * @param string $key        	
	 * @param value $value        	
	 * @return array[]
	 */
	static function insert_string(int $key, string $value):void {
		$ci = & get_instance ();
		$ci->redis_server->store_string ( $key, $value );
	}
	
	/**
	 * read data from cache
	 *
	 * @param string $key        	
	 * @param number $offset        	
	 * @param number $limit        	
	 */
	static function read_string(int $key):int{
		$ci = & get_instance ();
		return $ci->redis_server->read_string ( $key );
	}
	static function domestic_roundway_data(string $onward, string $return):array {
		return Common_Api_Flight::form_flight_combination ( $onward, $return ) [0];
	}
	
	/**
	 *
	 * @param string $temp_booking_id        	
	 */
	public function locate_temp_booking_id(string $temp_booking_id):bool {
		$ci = & get_instance ();
		
		$data = $ci->custom_db->single_table_records ( 'tmp_flight_pre_booking_details', 'origin', array (
				'reference_id' => $ci->db->escape ( $temp_booking_id ) 
		) );
		if ($data ['status'] == FAILURE_STATUS) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 *
	 * @param
	 *        	$app_reference
	 * @param
	 *        	$booking_source
	 */
	public function validate_temp_booking_id(string $app_reference, string $token, string $booking_source, int $search_id):string {
		// check app reference creation and booking source combination
		$token = unserialized_data ( $token );
		$booking_source = unserialized_data ( $booking_source );
		echo $app_reference;
		exit ();
	}
	/**
	 * Read data and create ssr listing
	 *
	 * @param string $app_reference
	 *        	unique application reference
	 */
	function read_ssr_details(string $app_reference):array {
		$response ['status'] = FAILURE_STATUS;
		$response ['msg'] = '';
		$response ['data'] = array ();
		
		$ci = & get_instance ();
		$details = $ci->flight_model->get_pax_itinerary ( $app_reference );
		
		// can be used by all the api
		Common_Api_Flight::$app_reference = $app_reference;
		if (valid_array ( $details ) == true) {
			$response ['status'] = SUCCESS_STATUS;
			$temp_booking = $this->module_model->unserialize_temp_booking_record ( $app_reference );
			
			// get ssr details and baggage details
			$pax_iti_map = array ();
			foreach ( $details as $k => $passenger ) {
				$lib = load_flight_lib ( $passenger ['booking_source'], '', true );
				$ci->$lib->read_ssr ( $passenger, $temp_booking, $pax_iti_map );
			}
			
			$seat_map = $ci->$lib->seat_map_details ($temp_booking,$details);
//			debug($seat_map);exit;
			$response ['data'] = $pax_iti_map;
			$response ['seatmap'] = @$seat_map['data'];
		}
		return $response;
	}
	
	/*
	 * save flight details
	 */
	function save_flight_booking(array $flight_data, array $passenger_details, string $app_reference, int $sequence_number, string $booking_source, int $search_id):array
	{
		//debug($flight_data);exit;
		$ci = & get_instance();
		$data['status'] = SUCCESS_STATUS;
		$data['message'] = '';
		$porceed_to_save = $this->is_duplicate_flight_booking($app_reference, $sequence_number);
		$porceed_to_save['status'] = SUCCESS_STATUS;
		if($porceed_to_save['status'] != SUCCESS_STATUS){
			$data['status'] = $porceed_to_save['status'];
			$data['message'] = $porceed_to_save['message'];
		} else {
			$search_data = $ci->flight_model->get_safe_search_data ( $search_id );
			$search_data = $search_data['data'];
			$segment_details = $flight_data['FlightDetails']['Details'];
			$seg_count = count($segment_details);
			$fare_details = $flight_data['Price'];
			$fare_breakup = $flight_data['PriceBreakup'];
			$last_segment_details1 = $segment_details[$seg_count-1];
			$last_segment_details = end($last_segment_details1);
			
			$master_booking_status = 'BOOKING_INPROGRESS';
			$ResultToken = array_values(unserialized_data($flight_data['ResultToken']));
			$mini_fare_rules = '';
			if(isset($ResultToken[0]['mini_fare_rules'])){
				$mini_fare_rules = json_encode($ResultToken[0]['mini_fare_rules']);
			}
			//Save to Master table
			$domain_origin = get_domain_auth_id();
			$flight_booking_status = $master_booking_status;
			$is_lcc = 0;
			$currency = domain_base_currency();
			$currency_obj = new Currency(array('module_type' => 'b2c_flight'));
			$currency_conversion_rate = $currency_obj->get_domain_currency_conversion_rate();
			$phone = $passenger_details[0]['ContactNo'];
			$alternate_number = 0;
			$email = $passenger_details[0]['Email'];
			$journey_from = is_array($search_data['from']) ? $search_data['from'][0] : $search_data['from'];
			$journey_to = is_array($search_data['to']) ? end($search_data['to']): $search_data['to'];
			$journey_start = $segment_details[0][0]['Origin']['DateTime'];
			$journey_end = $last_segment_details['Destination']['DateTime'];
			$payment_mode = 'PNHB1';
			$booking_details_attributes = array('JourneyAttributes' => $search_data,'attributes' => @$flight_data['Attr']);
			$created_by_id = 0;
			//debug($search_data);exit;
			
			$cabin_class =  $search_data['cabin_class'];
			
			$ci->flight_model->save_flight_booking_details ( $domain_origin, $flight_booking_status, $app_reference, $booking_source, $is_lcc, $currency, $phone, $alternate_number, $email, $journey_start, $journey_end, $journey_from, $journey_to, $payment_mode, json_encode($booking_details_attributes), $created_by_id,$currency_conversion_rate, FLIGHT_VERSION_2, $cabin_class);
			
			
			
			//Save to transaction details
			$transaction_status = $master_booking_status;
			$transaction_description = '';
			$pnr = '';
			$booking_id = '';
			$source = '';
			$ref_id = 0;
			$faretype = $flight_data['Attr']['FareType'];
			$transaction_details_attributes = '';
			$total_fare = 		$fare_details['commissionable_fare'];
			$admin_commission =	$fare_details['admin_commission'];
			$agent_commission =	$fare_details['agent_commission'];
			$admin_tds = 		$fare_details['admin_tds'];
			$agent_tds = 		$fare_details['agent_tds'];
			$domain_markup = 	$fare_details['admin_markup'];
			$transaction_insert_id = $ci->flight_model->save_flight_booking_transaction_details ( $app_reference, $transaction_status, $transaction_description, $pnr, $booking_id, $source, $ref_id, 
									json_encode($transaction_details_attributes), $sequence_number, $total_fare, $domain_markup, $admin_commission, $agent_commission, $admin_tds, $agent_tds, $booking_source, json_encode($fare_breakup), $mini_fare_rules);
			$flight_booking_transaction_details_fk = $transaction_insert_id['insert_id'];
			
			//Save Passenger Details
			foreach($passenger_details as $pax_k => $pax_v) {
				$pax_type = $pax_v['PaxType'];
				$passenger_type = "Adult";
				if($pax_type  == 2){
					$passenger_type = "Child";
				}
				else if($pax_type  == 3){
					$passenger_type = "Infant";
				}
				$is_lead = $pax_v['IsLeadPax'];
				$title = $pax_v['Title'];
				$first_name = $pax_v['FirstName'];
				$middle_name = '';
				$last_name = $pax_v['LastName'];
				$date_of_birth = $pax_v['DateOfBirth'];
				$gender = ($pax_v['Gender'] == 1 ? 'Male': 'Female');
				$passenger_nationality = $pax_v['CountryName'];
				$passport_number = $pax_v['PassportNumber'];
				$passport_issuing_country = '';
				$passport_expiry_date = $pax_v['PassportExpiry'];
				$status = $master_booking_status;
				//Attributes
				$passenger_attributes = array();
				//Attributes
				$passenger_attributes = array();
				$passenger_insert_id = $ci->flight_model->save_flight_booking_passenger_details($app_reference, $passenger_type, $is_lead, $title, $first_name, $middle_name, $last_name, $date_of_birth, $gender, $passenger_nationality, $passport_number, $passport_issuing_country, $passport_expiry_date, 
				$status, json_encode($passenger_attributes), $flight_booking_transaction_details_fk);
				
				//Passenger Ticket Details
				$passenger_fk = $passenger_insert_id['insert_id'];
				$ci->flight_model->save_passenger_ticket_info($passenger_fk);
				
				//Save ExtraService Details
				$this->save_extra_services($pax_v, $passenger_fk);
			}
                        
			//debug($segment_details);exit;
			//Save Flight Segment Details
			foreach ($segment_details as $segment_k => $segment_v) {
				$curr_segment_indicator = 1;
				foreach($segment_v as $ws_key => $ws_val) {
					$OriginDetails = $ws_val['Origin'];
					$DestinationDetails = $ws_val['Destination'];
					$segment_indicator = ($curr_segment_indicator++);
					$airline_code = 		$ws_val['OperatorCode'];
					$airline_name = 		$ws_val['OperatorName'];
					$flight_number = 		$ws_val['FlightNumber'];
					$fare_class = 			$ws_val['CabinClass'];
					$operating_carrier =	$ws_val['OperatorCode'];
					$from_airport_code = 	$OriginDetails['AirportCode'];
					$from_airport_name = 	$OriginDetails['AirportName'];
					$to_airport_code = 		$DestinationDetails['AirportCode'];
					$to_airport_name = 		$DestinationDetails['AirportName'];
					$departure_datetime = 	$OriginDetails['DateTime'];
					$arrival_datetime = 	$DestinationDetails['DateTime'];
					$iti_status = 			'';
					//Attributes
					$ws_val['departure_terminal'] = @$OriginDetails['Terminal'];
					$ws_val['arrival_terminal'] = @$DestinationDetails['Terminal'];
					$itinerary_attributes['AirlinePNR'] = @$ws_val['AirlinePNR'];
					$itinerary_attributes['arrival_terminal'] = @$OriginDetails['Terminal'];
					$itinerary_attributes['departure_terminal'] = @$DestinationDetails['Terminal'];
					$itinerary_attributes['Attr'] = $ws_val['Attr'];
					$itinerary_attributes = $itinerary_attributes;
					$cabin_baggage = $ws_val['Attr']['CabinBaggage'];
                    $checkin_baggage = $ws_val['Attr']['Baggage'];
                    $is_refundable = @$flight_data['Attr']['IsRefundable'];

                  
					$ci->flight_model->save_flight_booking_itinerary_details( $app_reference, $segment_indicator, $airline_code, $airline_name, $flight_number, $fare_class, $from_airport_code, $from_airport_name, $to_airport_code, $to_airport_name, $departure_datetime, $arrival_datetime, $iti_status, $operating_carrier, json_encode($itinerary_attributes), $flight_booking_transaction_details_fk, $cabin_baggage, $checkin_baggage, $is_refundable);
				}
			}
			
			//Add Extra Service Price to published price
			$ci->flight_model->add_extra_service_price_to_published_fare($app_reference, $sequence_number);
		}
		return $data;
	}
	/**
	 * Save Extra Services
	 * @param unknown_type $passenger_details
	 * @param unknown_type $passenger_fk
	 */
	private function save_extra_services(array $passenger_details, int $passenger_fk):void
	{
		//Save Passenger Baggage
		$this->save_passenger_baggage_info($passenger_details, $passenger_fk);
		//Save Passenger Meal
		$this->save_passenger_meals_info($passenger_details, $passenger_fk);
		//Save Passenger Seat
		$this->save_passenger_seat_info($passenger_details, $passenger_fk);
	}
	function remove_extra_services(string $app_reference, int $sequence_number):void{
		$ci = & get_instance();
		//Remove Extra Service Price to published price
		$ci->flight_model->remove_extra_service_price_to_published_fare($app_reference, $sequence_number);
		$passenger_data = $ci->custom_db->single_table_records('flight_booking_passenger_details', '*', array('app_reference' => $app_reference));
		//debug($passenger_data);exit;
		foreach($passenger_data['data'] as $customer_v){
			$ci->custom_db->delete_record('flight_booking_baggage_details', array('passenger_fk' => $customer_v['origin']) );
			$ci->custom_db->delete_record('flight_booking_meal_details', array('passenger_fk' => $customer_v['origin']) );
			$ci->custom_db->delete_record('flight_booking_seat_details', array('passenger_fk' => $customer_v['origin']) );

		}
		 $ci->custom_db->update_record('flight_booking_transaction_details',array('extra_services_status' => 0),array('app_reference' => $app_reference, 'sequence_number' => $sequence_number));
	}
	private function save_passenger_baggage_info(array $passenger_details, int $passenger_fk): void
	{
		$ci = & get_instance ();
	    if (!empty($passenger_details['BaggageId'])) {
	        $ci = &get_instance();
	        foreach ($passenger_details['BaggageId'] as $bag_k => $bag_v) {
	            $bag_v = trim($bag_v);
	            if (!empty($bag_v)) {
	                $baggage_data = Common_Flight::read_record($bag_v);
	                if (!empty($baggage_data)) {
	                    $baggage_data = json_decode($baggage_data[0], true);
	                    if (json_last_error() === JSON_ERROR_NONE && is_array($baggage_data)) {
	                        $BaggageId = array_values(unserialized_data($baggage_data['BaggageId'] ?? []));
	                        $baggage_data['BaggageId'] = $BaggageId[0]['Code'] ?? '';
	                        
	                        // Save passenger baggage information
	                        $ci->flight_model->save_passenger_baggage_info(
	                            $passenger_fk,
	                            $baggage_data['Origin'] ?? '',
	                            $baggage_data['Destination'] ?? '',
	                            $baggage_data['Weight'] ?? 0.0,
	                            $baggage_data['Price'] ?? 0.0,
	                            $baggage_data['BaggageId']
	                        );
	                    }
	                }
	            }
	        }
	    }
	}

	private function save_passenger_meals_info(array $passenger_details, int $passenger_fk): void
	{
	  $ci = & get_instance ();
	    if (!empty($passenger_details['MealId'])) {
	        $ci = &get_instance();
	        foreach ($passenger_details['MealId'] as $meal_k => $meal_v) {
	            $meal_v = trim($meal_v);
	            if (!empty($meal_v)) {
	                $meal_data = Common_Flight::read_record($meal_v);
	                if (!empty($meal_data)) {
	                    $meal_data = json_decode($meal_data[0], true);
	                    if (json_last_error() === JSON_ERROR_NONE && is_array($meal_data)) {
	                        $MealId = array_values(unserialized_data($meal_data['MealId'] ?? []));
	                        $meal_data['MealId'] = $MealId[0]['Code'] ?? '';
	                        $type = $MealId[0]['Type'] ?? '';
	                        
	                        // Save passenger meal information
	                        $ci->flight_model->save_passenger_meals_info(
	                            $passenger_fk,
	                            $meal_data['Origin'] ?? '',
	                            $meal_data['Destination'] ?? '',
	                            $meal_data['Description'] ?? '',
	                            (float)($meal_data['Price'] ?? 0.0),
	                            $meal_data['MealId'],
	                            $type
	                        );
	                    }
	                }
	            }
	        }
	    }
	}

	private function save_passenger_seat_info(array $passenger_details, int $passenger_fk): void
	{
	    if (!empty($passenger_details['SeatId'])) {
	        $ci = &get_instance();
	        foreach ($passenger_details['SeatId'] as $seat_k => $seat_v) {
	            $seat_v = trim($seat_v);
	            if (!empty($seat_v)) {
	                $seat_data = Common_Flight::read_record($seat_v);
	                if (!empty($seat_data)) {
	                    $seat_data = json_decode($seat_data[0], true);
	                    if (json_last_error() === JSON_ERROR_NONE && is_array($seat_data)) {
	                        $SeatId = array_values(unserialized_data($seat_data['SeatId'] ?? []));
	                        $seat_data['SeatId'] = $SeatId[0]['Code'] ?? '';
	                        $type = $SeatId[0]['Type'] ?? '';
	                        $sdescription = $seat_data['Description'] ?? '';
	                        
	                        // Save passenger seat information
	                        $ci->flight_model->save_passenger_seat_info(
	                            $passenger_fk,
	                            $seat_data['Origin'] ?? '',
	                            $seat_data['Destination'] ?? '',
	                            $sdescription,
	                            (float)($seat_data['Price'] ?? 0.0),
	                            $seat_data['SeatId'],
	                            $type,
	                            $seat_data['AirlineCode'] ?? '',
	                            $seat_data['FlightNumber'] ?? ''
	                        );
	                    }
	                }
	            }
	        }
	    }
	}
	public function update_flight_booking_status(string $flight_booking_status, string $app_reference, int $sequence_number, string $booking_source): void
	{
	    $ci = &get_instance();
	    $flight_booking_status = trim($flight_booking_status);
	    $app_reference = trim($app_reference);
	    $booking_source = trim($booking_source);
	    
	    $master_booking_details = $ci->flight_model->get_booking_details($app_reference);
	    if ($master_booking_details['status']) {
	        // Get flight_booking_transaction_details
	        $flight_booking_transaction_details_condition = [
	            'app_reference' => $app_reference,
	            'sequence_number' => $sequence_number,
	            'booking_source' => $booking_source,
	        ];
	        
	        $flight_transaction_details = $ci->custom_db->single_table_records('flight_booking_transaction_details', 'origin', $flight_booking_transaction_details_condition);
	        
	        if ($flight_transaction_details['status']) {
	            $flight_booking_transaction_details_origin = $flight_transaction_details['data'][0]['origin'];
	            
	            // 1. Update flight_booking_transaction_details
	            $ci->custom_db->update_record('flight_booking_transaction_details', ['status' => $flight_booking_status], ['origin' => $flight_booking_transaction_details_origin]);
	            
	            // 2. Update flight_booking_passenger_details
	            $ci->custom_db->update_record('flight_booking_passenger_details', ['status' => $flight_booking_status], ['flight_booking_transaction_details_fk' => $flight_booking_transaction_details_origin]);
	            
	            // 3. Update flight_booking_details (Master Table)
	            $master_booking_status = $flight_booking_status;
	            
	            // Running again to get the latest status
	            $master_booking_details = $ci->flight_model->get_booking_details($app_reference);
	            $booking_transaction_details = $master_booking_details['data']['booking_transaction_details'];
	            
	            if (count($booking_transaction_details) === 1) {
	                $master_booking_status = $booking_transaction_details[0]['status'];
	            } elseif (count($booking_transaction_details) === 2) {
	                $onward_booking_status = $booking_transaction_details[0]['status'];
	                $return_booking_status = $booking_transaction_details[1]['status'];
	                
	                if ($onward_booking_status === 'BOOKING_CONFIRMED' || $return_booking_status === 'BOOKING_CONFIRMED') {
	                    $master_booking_status = 'BOOKING_CONFIRMED';
	                } elseif ($onward_booking_status === 'BOOKING_HOLD' || $return_booking_status === 'BOOKING_HOLD') {
	                    $master_booking_status = 'BOOKING_HOLD';
	                } elseif ($onward_booking_status === 'BOOKING_FAILED' || $return_booking_status === 'BOOKING_FAILED') {
	                    $master_booking_status = 'BOOKING_FAILED';
	                } else {
	                    $master_booking_status = 'BOOKING_INPROGRESS';
	                }
	            }
	            
	            $ci->custom_db->update_record('flight_booking_details', ['status' => $master_booking_status], ['app_reference' => $app_reference]);
	        }
	    }
	}

	public function update_flight_booking_tranaction_price_details(
	    string $app_reference,
	    int $sequence_number,
	    float $commissionable_fare,
	    float $admin_commission,
	    float $agent_commission,
	    float $admin_tds,
	    float $agent_tds,
	    float $admin_markup,
	    array $fare_breakup
	): void {
	    $ci = &get_instance();
	    
	    $update_data = [
	        'total_fare' => $commissionable_fare,
	        'admin_commission' => $admin_commission,
	        'agent_commission' => $agent_commission,
	        'admin_tds' => $admin_tds,
	        'agent_tds' => $agent_tds,
	        'domain_markup' => $admin_markup,
	        'fare_breakup' => json_encode($fare_breakup), // Handle fare_breakup
	    ];
	    
	    $update_condition = [
	        'app_reference' => $app_reference,
	        'sequence_number' => $sequence_number,
	    ];
	    
	    $ci->custom_db->update_record('flight_booking_transaction_details', $update_data, $update_condition);
	}

	public function update_passenger_ticket_info(int $passenger_fk, string $ticket_id, string $ticket_number, array $pax_break_down = []): void
	{
	    $update_ticket_data = [
	        'TicketId' => $ticket_id,
	        'TicketNumber' => $ticket_number,
	    ];
	    
	    if (!empty($pax_break_down)) {
	        $update_ticket_data['Fare'] = json_encode($pax_break_down);
	    }
	    
	    $update_ticket_condition = [
	        'passenger_fk' => $passenger_fk,
	    ];
	    
	    $GLOBALS['CI']->custom_db->update_record('flight_passenger_ticket_info', $update_ticket_data, $update_ticket_condition);
	}

	 /**
     * Deduct flight booking amount for confirmed booking.
     *
     * @param string $app_reference
     * @param int $sequence_number
     */
    public function deduct_flight_booking_amount(string $app_reference, int $sequence_number): void
    {
        $condition = [
            'app_reference' => $app_reference,
            'sequence_number' => $sequence_number
        ];

        $data = $this->ci->db->query('select BD.currency,BD.currency_conversion_rate,FT.* from flight_booking_details BD
						join flight_booking_transaction_details FT on BD.app_reference=FT.app_reference
						where FT.app_reference="'.trim($app_reference).'" and FT.sequence_number='.$sequence_number
						)->row_array();

        if ($this->isValidBookingData($data)) {
            $this->ci->load->library('booking_data_formatter');

            $transaction_details = $data;
            $agent_buying_price = $this->ci->booking_data_formatter->agent_buying_price($transaction_details)[0];

            $domain_booking_attr = [
                'app_reference' => $app_reference,
                'transaction_type' => 'flight'
            ];

            // Deduct domain balance
            $this->ci->domain_management->debit_domain_balance($agent_buying_price, Flight::get_credential_type(), get_domain_auth_id(), $domain_booking_attr);

            // Save to transaction log
            $this->logTransactionDetails($transaction_details, $agent_buying_price, $app_reference);
        }
    }

    /**
     * Deduct flight booking amount for hold booking.
     *
     * @param string $app_reference
     * @param int $sequence_number
     */
    public function deduct_flight_booking_amount_hold(string $app_reference, int $sequence_number): void
    {
        $condition = [
            'app_reference' => $app_reference,
            'sequence_number' => $sequence_number
        ];

        $data = $this->ci->db->query('select BD.currency,BD.domain_origin,BD.currency_conversion_rate,FT.* from flight_booking_details BD
						join flight_booking_transaction_details FT on BD.app_reference=FT.app_reference
						where FT.app_reference="'.trim($app_reference).'" and FT.sequence_number='.$sequence_number
						)->row_array();

        if ($this->isValidHoldBookingData($data)) {
            $this->ci->load->library('booking_data_formatter');

            $transaction_details = $data;
            $agent_buying_price = $this->ci->booking_data_formatter->agent_buying_price($transaction_details)[0];

            $domain_booking_attr = [
                'app_reference' => $app_reference,
                'transaction_type' => 'flight',
                'currency_conversion_rate' => $transaction_details['currency_conversion_rate']
            ];

            // Deduct domain balance for hold booking
            $this->ci->domain_management->debit_domain_balance_hold_booking($agent_buying_price, 'live', $data['domain_origin'], $domain_booking_attr);

            // Save to transaction log
            $this->logTransactionDetails($transaction_details, $agent_buying_price, $app_reference, $data['domain_origin']);
        }
    }
    /**
     * Validate if the flight booking data is valid for deduction.
     *
     * @param array|null $data
     * @return bool
     */
    private function isValidBookingData(?array $data): bool
    {
        return !empty($data) && in_array($data['status'], ['BOOKING_CONFIRMED', 'BOOKING_PENDING']);
    }

    /**
     * Validate if the hold booking data is valid for deduction.
     *
     * @param array|null $data
     * @return bool
     */
    private function isValidHoldBookingData(?array $data): bool
    {
        return !empty($data) && $data['status'] === 'BOOKING_CONFIRMED';
    }

    /**
     * Validate if the aborted booking data is valid for credit.
     *
     * @param array|null $data
     * @return bool
     */
    private function isValidAbortedBookingData(?array $data): bool
    {
        return !empty($data) && $data['status'] === 'BOOKING_ABORTED';
    }

    /**
     * Log the transaction details.
     *
     * @param array $transaction_details
     * @param float $transaction_amount
     * @param string $app_reference
     * @param string|null $domain_origin
     */
    private function logTransactionDetails(array $transaction_details, float $transaction_amount, string $app_reference, ?string $domain_origin = null): void
    {
        $domain_markup = (float)$transaction_details['domain_markup'];
        $level_one_markup = 0;
        $currency = $transaction_details['currency'];
        $currency_conversion_rate = (float)$transaction_details['currency_conversion_rate'];
        $remarks = 'Transaction was successfully processed for flight booking reference ' . $app_reference;

        $this->ci->domain_management_model->save_transaction_details(
            'flight',
            $app_reference,
            $transaction_amount - $domain_markup,
            $domain_markup,
            $level_one_markup,
            $remarks,
            $currency,
            $currency_conversion_rate,
            $domain_origin ?? ''
        );
    }
        
        
	private function is_duplicate_flight_booking(string $app_reference, int $sequence_number): array
{
    $ci = &get_instance();
    $data = [
        'status' => SUCCESS_STATUS,
        'message' => '',
    ];

    $flight_booking_details = $ci->custom_db->single_table_records('flight_booking_transaction_details', '*', [
        'app_reference' => trim($app_reference),
        'sequence_number' => $sequence_number,
    ]);

    if ($flight_booking_details['status'] && valid_array($flight_booking_details['data'][0])) {
        $flight_booking_details = $flight_booking_details['data'][0];
        $pnr = trim($flight_booking_details['pnr']);
        $data['status'] = FAILURE_STATUS;
        $data['message'] = !empty($pnr) 
            ? "Booking Already Done with PNR: $pnr" 
            : 'Duplicate Booking Not Allowed';
    }

    return $data;
}

function get_passenger_type_code(string $type): string
{
    return match (strtolower($type)) {
        'adult' => 'ADT',
        'child' => 'CHD',
        'infant' => 'INF',
        default => '',
    };
}

/**
 * Save SSR details.
 */
function save_ssr_details(string $app_refedrence, array $data): void
{
    $ci = &get_instance();

    // Flight-wise passenger
    $passenger_list = [];
    if (isset($data['passenger']) && valid_array($data['passenger'])) {
        foreach ($data['passenger'] as $pass) {
            $passenger_list[$pass['flight_no']][] = $pass;
        }
    }

    // Save seat details
    if (isset($data['seat']) && valid_array($data['seat'])) {
        foreach ($data['seat'] as $s_key => $seat_arr) {
            foreach ($seat_arr as $st_k => $seat) {
                $pass_seat = $passenger_list[$s_key][$st_k];
                $seat_array = [
                    'p_origin' => $pass_seat['p_origin'],
                    'i_origin' => $pass_seat['i_origin'],
                    'seat' => $seat,
                    'fare' => 0,
                ];
                $ci->custom_db->insert_record('flight_booking_seat_details', $seat_array);
            }
        }
    }

    // Save meal details
    foreach ($data['meal_code'] as $k => $v) {
        if ($v !== 'INVALIDIP') {
            $meal = [
                'i_origin' => $data['mi_origin'][$k],
                'p_origin' => $data['mp_origin'][$k],
                'value' => explode(DB_SAFE_SEPARATOR, str_replace("'", "", $v))[0],
                'description' => explode(DB_SAFE_SEPARATOR, str_replace("'", "", $v))[1],
                'fare' => explode(DB_SAFE_SEPARATOR, str_replace("'", "", $v))[2],
            ];
            $ci->custom_db->insert_record('flight_booking_meals_details', $meal);
        }
    }

    // Save baggage details
    $prev_code = '';
    foreach ($data['baggage_code'] as $k => $v) {
        if ($v !== 'INVALIDIP') {
            $baggage = [
                'i_origin' => $data['bi_origin'][$k],
                'p_origin' => $data['bp_origin'][$k],
                'value' => explode(DB_SAFE_SEPARATOR, str_replace("'", "", $v))[0],
                'description' => explode(DB_SAFE_SEPARATOR, str_replace("'", "", $v))[1],
                'fare' => explode(DB_SAFE_SEPARATOR, str_replace("'", "", $v))[2],
                'is_selected' => 1,
            ];
            $ci->custom_db->insert_record('flight_booking_baggage_details', $baggage);
            $prev_code = $v;
        } elseif ($prev_code !== '') {
            $baggage = [
                'i_origin' => $data['bi_origin'][$k],
                'p_origin' => $data['bp_origin'][$k],
                'value' => explode(DB_SAFE_SEPARATOR, str_replace("'", "", $prev_code))[0],
                'description' => explode(DB_SAFE_SEPARATOR, str_replace("'", "", $prev_code))[1],
                'fare' => explode(DB_SAFE_SEPARATOR, str_replace("'", "", $prev_code))[2],
                'is_selected' => 0,
            ];
            $ci->custom_db->insert_record('flight_booking_baggage_details', $baggage);
        }
    }
}

/**
 * Add fare details to the flight data.
 */
static function add_fare_details(array &$flight_data): void
{
    if (isset($flight_data['fare']) && valid_array($flight_data['fare'])) {
        $api_total_display_fare = 0;
        $api_total_tax = 0;
        $api_total_fare = 0;
        $total_meal_and_baggage = 0;
        $price_breakup = [];

        foreach ($flight_data['fare'] as $__fare) {
            $api_total_display_fare += $__fare['api_total_display_fare'];
            $api_total_tax += $__fare['total_breakup']['api_total_tax'];
            $api_total_fare += $__fare['total_breakup']['api_total_fare'];

            if (isset($__fare['total_breakup']['meal_and_baggage_fare'])) {
                $total_meal_and_baggage += $__fare['total_breakup']['meal_and_baggage_fare'];
            }

            foreach ($__fare['price_breakup'] as $p_key => $breakup) {
                $price_breakup[$p_key] = ($price_breakup[$p_key] ?? 0) + $breakup;
            }
        }

        $flight_data['price'] = [
            'api_currency' => $__fare['api_currency'],
            'api_total_display_fare' => $api_total_display_fare,
            'total_breakup' => [
                'api_total_tax' => $api_total_tax,
                'api_total_fare' => $api_total_fare,
                'meal_and_baggage_fare' => $total_meal_and_baggage,
            ],
            'price_breakup' => $price_breakup,
        ];
    }
}

/**
 * Update markup and commission, and insert cache key.
 */
public function update_markup_and_insert_cache_key_to_token(array $flight_list, string $carry_cache_key, int $search_id): array
{
    $ci = &get_instance();
    $multiplier = $this->get_markup_multiplier($search_id);

    $search_data = $ci->flight_blender->search_data($search_id);
    $is_domestic = $search_data['data']['is_domestic'];
    $domain_id = get_domain_auth_id();
    $commission_percentage = $ci->domain_management->get_flight_commission($domain_id);
    foreach ($flight_list as &$j_flight_list) {
        foreach ($j_flight_list as &$v) {
            $OperatorCode = $v['FlightDetails']['Details'][0][0]['OperatorCode'] ?? '';

            $temp_token = array_values(unserialized_data($v['ResultToken']));
            $booking_source = $temp_token[0]['booking_source'];

            // Cache the Data
            $access_data = Common_Flight::insert_record($carry_cache_key, json_encode($v));

            // Assign the Cache Key
            $v['ResultToken'] = $access_data['access_key'];

            // Update the Markup and Commission
            $this->update_fare_markup_commission($v['Price'], $multiplier, $commission_percentage, true, $booking_source, $OperatorCode, $is_domestic);
        }
    }

    return $flight_list;
}

/**
 * Cache extra services (Baggage, Meals, Seat, etc.).
 */
public function cache_extra_services(array $extra_services, string $carry_cache_key): array
{
    $ci = &get_instance();
    $currency_obj = new Currency(['from' => get_application_default_currency(), 'to' => domain_base_currency()]);

    foreach (['Baggage', 'Meals', 'Seat', 'MealPreference', 'SeatPreference'] as $service_type) {
        if (isset($extra_services[$service_type]) && valid_array($extra_services[$service_type])) {
            foreach ($extra_services[$service_type] as $service_k => &$service_v) {
                foreach ($service_v as $sd_k => &$sd_v) {
                    $access_data = Common_Flight::insert_record($carry_cache_key, json_encode($sd_v));
                    $extra_services[$service_type][$service_k][$sd_k]['ServiceId'] = $access_data['access_key'];

                    // Convert the Price to Domain Currency
                    $extra_services[$service_type][$service_k][$sd_k]['Price'] = get_converted_currency_value($currency_obj->force_currency_conversion($sd_v['Price']));
                }
            }
        }
    }

   	 	return $extra_services;
	}

	 /**
     * Adding the Markup and Commission
     */
    private function update_fare_markup_commission(
        array &$FareDetails,
        float $multiplier,
        int $commission_percentage,
        bool $domain_currency_conversion,
        string $booking_source,
        string $OperatorCode = '',
        bool $is_domestic = false
    ): void {
        $ci = &get_instance();

        // Calculating Markup and commission
        $total_fare = ($FareDetails['TotalDisplayFare'] - $FareDetails['PriceBreakup']['AgentCommission'] + $FareDetails['PriceBreakup']['AgentTdsOnCommision']);
        $currency_obj = new Currency([
            'module_type' => 'b2c_flight',
            'from' => get_application_default_currency(),
            'to' => get_application_default_currency()
        ]);

        // Get Operator Airline Code
        if (isset($OperatorCode['OperatorCode'])) {
            $OperatorCode = $OperatorCode['OperatorCode'];
        }

        $markup_price = $currency_obj->get_currency(
            $total_fare,
            true,
            true,
            false,
            $multiplier,
            $booking_source,
            FLIGHT_VERSION_2,
            $OperatorCode,
            $is_domestic
        );

        $total_markup = ($markup_price['default_value'] - $total_fare);

        // Updating Fare Details with Markup
        $FareDetails['TotalDisplayFare'] += $total_markup;
        if ($total_markup > 0) {
            $FareDetails['PriceBreakup']['Tax'] += $total_markup;
        } else {
            $FareDetails['PriceBreakup']['BasicFare'] += $total_markup;
        }

        $FareDetails['PriceBreakup']['AgentCommission'] = round($this->update_agent_commision($FareDetails['PriceBreakup']['AgentCommission'], $commission_percentage), 3);
        $FareDetails['PriceBreakup']['AgentTdsOnCommision'] = round($currency_obj->calculate_tds($FareDetails['PriceBreakup']['AgentCommission']), 3);

        // Updating Passenger Breakdown details
        if (valid_array($FareDetails['PassengerBreakup'])) {
            $total_pax_count = array_sum(array_column($FareDetails['PassengerBreakup'], 'PassengerCount'));
            $single_pax_markup = ($total_markup / $total_pax_count);
            foreach ($FareDetails['PassengerBreakup'] as $k => $v) {
                if ($single_pax_markup > 0) {
                    $FareDetails['PassengerBreakup'][$k]['Tax'] += ($single_pax_markup * $FareDetails['PassengerBreakup'][$k]['PassengerCount']);
                } else {
                    if ($FareDetails['PassengerBreakup'][$k]['BasePrice'] > 0) {
                        $FareDetails['PassengerBreakup'][$k]['BasePrice'] += ($single_pax_markup * $FareDetails['PassengerBreakup'][$k]['PassengerCount']);
                    } else {
                        $FareDetails['PriceBreakup']['Tax'] -= ($FareDetails['PassengerBreakup'][$k]['Tax']);
                        $FareDetails['PriceBreakup']['BasicFare'] += ($FareDetails['PassengerBreakup'][$k]['Tax']);
                        $FareDetails['PassengerBreakup'][$k]['Tax'] += ($single_pax_markup * $FareDetails['PassengerBreakup'][$k]['PassengerCount']);
                        $FareDetails['PassengerBreakup'][$k]['BasePrice'] = $FareDetails['PassengerBreakup'][$k]['Tax'];
                        $FareDetails['PassengerBreakup'][$k]['Tax'] = 0;
                    }
                }
                $FareDetails['PassengerBreakup'][$k]['TotalPrice'] = ($FareDetails['PassengerBreakup'][$k]['BasePrice'] + $FareDetails['PassengerBreakup'][$k]['Tax']);
            }
        }

        // Converting Fare Object to Domain Currency
        $this->convert_to_domain_currency_object($FareDetails, $domain_currency_conversion);
    }

    /**
     * Convert Fare Object to Domain Currency
     */
    private function convert_to_domain_currency_object(array &$FareDetails, bool $domain_currency_conversion = true): void
    {
        $domain_base_currency = $domain_currency_conversion ? domain_base_currency() : get_application_default_currency();
        $TotalDisplayFare = $FareDetails['TotalDisplayFare'];
        $PriceBreakup = $FareDetails['PriceBreakup'];
        $PassengerBreakup = $FareDetails['PassengerBreakup'];
        $currency_obj = new Currency(['from' => get_application_default_currency(), 'to' => $domain_base_currency]);

        // Converting the API Fare Currency to Domain Currency
        $FareDetails['Currency'] = $domain_base_currency;
        $FareDetails['TotalDisplayFare'] = get_converted_currency_value($currency_obj->force_currency_conversion($TotalDisplayFare));

        $FareDetails['PriceBreakup']['Tax'] = get_converted_currency_value($currency_obj->force_currency_conversion($PriceBreakup['Tax']));
        $FareDetails['PriceBreakup']['BasicFare'] = get_converted_currency_value($currency_obj->force_currency_conversion($PriceBreakup['BasicFare']));
        $FareDetails['PriceBreakup']['AgentCommission'] = get_converted_currency_value($currency_obj->force_currency_conversion($PriceBreakup['AgentCommission']));
        $FareDetails['PriceBreakup']['AgentTdsOnCommision'] = get_converted_currency_value($currency_obj->force_currency_conversion($PriceBreakup['AgentTdsOnCommision']));

        // PASSENGER BREAKDOWN
        foreach ($PassengerBreakup as $pk => $pv) {
            $FareDetails['PassengerBreakup'][$pk] = $pv;
            $FareDetails['PassengerBreakup'][$pk]['BasePrice'] = get_converted_currency_value($currency_obj->force_currency_conversion($pv['BasePrice']));
            $FareDetails['PassengerBreakup'][$pk]['Tax'] = get_converted_currency_value($currency_obj->force_currency_conversion($pv['Tax']));
            $FareDetails['PassengerBreakup'][$pk]['TotalPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion($pv['TotalPrice']));
        }
    }

    /**
     * Returns Booking Transaction Amount Details
     */
    public function final_booking_transaction_fare_details(
        array $core_price_details,
        int $search_id,
        string $booking_source,
        string $OperatorCode = ''
    ): array {
        $ci = &get_instance();
        $multiplier = $this->get_markup_multiplier($search_id);
        $search_data = $ci->flight_blender->search_data($search_id);
        $is_domestic = $search_data['data']['is_domestic'];

        $domain_id = get_domain_auth_id();
        $commission_percentage = $ci->domain_management->get_flight_commission($domain_id);
        $domain_currency_conversion = false;

        // Update the markup and commission
        $core_commissionable_fare = $core_price_details['TotalDisplayFare'];
        $core_commission = $core_price_details['PriceBreakup']['AgentCommission'];
        $core_commission_on_tds = $core_price_details['PriceBreakup']['AgentTdsOnCommision'];

        $this->update_fare_markup_commission($core_price_details, $multiplier, $commission_percentage, $domain_currency_conversion, $booking_source, $OperatorCode, $is_domestic);

        $commissionable_fare = $core_price_details['TotalDisplayFare'];
        $agent_commission = $core_price_details['PriceBreakup']['AgentCommission'];
        $agent_tds = $core_price_details['PriceBreakup']['AgentTdsOnCommision'];
        $admin_commission = $core_commission - $agent_commission;
        $admin_tds = $core_commission_on_tds - $agent_tds;
        $admin_markup = ($commissionable_fare - $core_commissionable_fare);

        // Fare Breakups
        $final_booking_transaction_fare_details['PriceBreakup'] = $core_price_details;
        $final_booking_transaction_fare_details['Price'] = [
            'commissionable_fare' => $core_commissionable_fare,
            'admin_commission' => $admin_commission,
            'agent_commission' => $agent_commission,
            'admin_tds' => $admin_tds,
            'agent_tds' => $agent_tds,
            'admin_markup' => round($admin_markup, 1),
            'passenger_breakup' => $core_price_details['PassengerBreakup']
        ];

        // Client Buying Price
        $final_booking_transaction_fare_details['Price']['client_buying_price'] = floatval($commissionable_fare - $agent_commission + $agent_tds);

        return $final_booking_transaction_fare_details;
    }

    public function get_flight_booking_transaction_details(
        string $app_reference,
        int $sequence_number,
        string $booking_source = '',
        string $booking_status = ''
    ): array {
        $ci = &get_instance();
        $data = [
            'status' => FAILURE_STATUS,
            'data' => '',
            'message' => ''
        ];

        $flight_booking_details = $ci->flight_model->get_flight_booking_transaction_details($app_reference, $sequence_number, $booking_source, $booking_status);
        if ($flight_booking_details['status'] === SUCCESS_STATUS) {
            $flight_booking_details = $flight_booking_details['data'];
            $booking_transaction_details = $flight_booking_details['booking_transaction_details'][0];
            $flight_booking_status_code = $booking_transaction_details['status_code'];

            switch ($flight_booking_status_code) {
                case BOOKING_CONFIRMED:
                case BOOKING_HOLD:
                case BOOKING_PENDING:
                    $data['status'] = $flight_booking_status_code;
                    $data['data'] = $this->format_flight_booking_details($flight_booking_details);
                    break;
                case BOOKING_FAILED:
                    $data['status'] = $flight_booking_status_code;
                    $data['message'] = 'Booking Failed';
                    break;
                case BOOKING_CANCELLED:
                    $data['status'] = $flight_booking_status_code;
                    $data['message'] = 'Booking Cancelled';
                    break;
                default:
                    $data['status'] = FAILURE_STATUS;
            }
        } else {
            $data['message'] = 'Invalid Request';
        }

        return $data;
    }
	 /**
     * Formats Flight Booking Details
     */
    public function format_flight_booking_details(array $flight_booking_details): array
    {
        $formatted = [];

        $booking_itinerary = $flight_booking_details['booking_itinerary_details'];
        $booking_transaction = $flight_booking_details['booking_transaction_details'][0];
        $booking_customers = $flight_booking_details['booking_customer_details'];
        $booking = $flight_booking_details['booking_details'][0];
        $booking_attributes = json_decode($booking['attributes'], true);

        $journey = [];

        foreach ($booking_itinerary as $index => $segment) {
            $attr = json_decode($segment['attributes'], true) ?? [];

            $origin = [
                'AirportCode' => $segment['from_airport_code'],
                'CityName' => $segment['from_airport_name'],
                'AirportName' => $segment['from_airport_name'],
                'DateTime' => $segment['departure_datetime'],
                'FDTV' => strtotime($segment['departure_datetime']),
                'Terminal' => $attr['departure_terminal'] ?? ''
            ];

            $destination = [
                'AirportCode' => $segment['to_airport_code'],
                'CityName' => $segment['to_airport_name'],
                'AirportName' => $segment['to_airport_name'],
                'DateTime' => $segment['arrival_datetime'],
                'FATV' => strtotime($segment['arrival_datetime']),
                'Terminal' => $attr['arrival_terminal'] ?? ''
            ];

            $journey['FlightDetails']['Details'][0][$index] = [
                'Origin' => $origin,
                'Destination' => $destination,
                'AirlinePNR' => $segment['airline_pnr'] ?: $booking_transaction['pnr'],
                'OperatorCode' => $segment['airline_code'],
                'DisplayOperatorCode' => $segment['operating_carrier'],
                'OperatorName' => $segment['airline_name'],
                'FlightNumber' => $segment['flight_number'],
                'CabinClass' => $segment['fare_class'],
                'Attr' => [
                    'Baggage' => $attr['Attr']['Baggage'] ?? '',
                    'CabinBaggage' => $attr['Attr']['CabinBaggage'] ?? '',
                    'AvailableSeats' => $attr['Attr']['AvailableSeats'] ?? ''
                ]
            ];
        }

        // Price and currency conversion
        $price = json_decode($booking_transaction['fare_breakup'], true);
        $this->convert_to_domain_currency_object($price, true);

        $currency_obj = new Currency([
            'from' => get_application_default_currency(),
            'to' => domain_base_currency()
        ]);

        $passengers = [];

        foreach ($booking_customers as $index => $passenger) {
            $passenger_data = [
                'PassengerId' => $passenger['origin'],
                'PassengerType' => $this->get_passenger_type_code($passenger['passenger_type']),
                'Title' => $passenger['title'],
                'FirstName' => $passenger['first_name'],
                'LastName' => $passenger['last_name'],
                'TicketNumber' => $passenger['TicketNumber']
            ];

            if (!empty($passenger['TicketId'])) {
                $passenger_data['TicketId'] = $passenger['TicketId'];
            }
            $origin = (int)$passenger['origin'];
            $passenger_data['Baggage'] = $this->getOptionalServices('flight_booking_baggage_details', $origin, $currency_obj);
            $passenger_data['Meal'] = $this->getOptionalServices('flight_booking_meal_details', $origin, $currency_obj);
            $passenger_data['Seat'] = $this->getOptionalServices('flight_booking_seat_details', $origin, $currency_obj, true);

            $passengers[$index] = $passenger_data;
        }

        $formatted['BookingDetails'] = [
            'BookingId' => $booking_transaction['book_id'],
            'PNR' => $booking_transaction['pnr'],
            'PassengerDetails' => $passengers,
            'JourneyList' => $journey,
            'Price' => $price,
            'Attr' => $booking_attributes['attributes'] ?? []
        ];

        if (
            $booking_transaction['status'] === 'BOOKING_HOLD'
            && $booking_transaction['ticket_time_limit'] !== '0000-00-00 00:00:00'
        ) {
            $formatted['BookingDetails']['TicketingTimeLimit'] = $booking_transaction['ticket_time_limit'];
        }

        return $formatted;
    }

    /**
     * Converts Calendar Fare details to domain currency
     */
    public function update_calendarfare_currency(array $FareDetails): array
    {
        $converted = [];
        $currency_obj = new Currency([
            'from' => get_application_default_currency(),
            'to' => domain_base_currency()
        ]);

        foreach ($FareDetails as $key => $item) {
            $converted[$key] = $item;
            foreach (['Fare', 'BaseFare', 'Tax', 'OtherCharges', 'FuelSurcharge'] as $field) {
                $converted[$key][$field] = get_converted_currency_value(
                    $currency_obj->force_currency_conversion($item[$field] ?? 0)
                );
            }
        }

        return $converted;
    }

    private function getOptionalServices(string $table, int $passenger_fk, Currency $currency_obj, bool $isSeat = false): array
    {
        $services = [];
        $data = $this->ci->custom_db->single_table_records($table, '*', ['passenger_fk' => $passenger_fk]);

        if (($data['status'] ?? false) === SUCCESS_STATUS) {
            foreach ($data['data'] as $row) {
                $entry = [
                    'Origin' => $row['from_airport_code'],
                    'Destination' => $row['from_airport_code'],
                    'Price' => get_converted_currency_value($currency_obj->force_currency_conversion($row['price']))
                ];

                if ($isSeat) {
                    $entry['FlightNumber'] = $row['flight_number'];
                    $entry['SeatNumber'] = $row['seat_id'];
                } else {
                    $entry['Weight'] = $row['description'] ?? '';
                    $entry['Description'] = $row['description'] ?? '';
                }

                $services[] = $entry;
            }
        }

        return $services;
    }

	  private function update_agent_commision(float $amount, float $commissionPercentage): float
    {
        return ($amount * $commissionPercentage) / 100;
    }

    /**
     * Returns Markup Multiplier for Flight
     */
    private function get_markup_multiplier(int $searchId): int
    {	
        $searchData = $this->ci->flight_model->get_safe_search_data($searchId);
        $searchData = $searchData['data'];
        $multiplier = $searchData['total_pax'];

        if ($searchData['trip_type'] === 'return' && $searchData['is_domestic'] === false) {
            $multiplier *= 2; // International Round Way
        } elseif ($searchData['trip_type'] === 'multicity') {
            $wayCount = count($searchData['from']);
            $multiplier *= $wayCount;
        }

        return $multiplier;
    }

    /**
     * Calculate Markup
     */
    private function calculate_markup(string $markupType, float $markupVal, float $totalFare, int $multiplier): float
    {
        if ($markupType === 'percentage') {
            $markupAmount = ($totalFare * $markupVal) / 100;
        } else {
            $markupAmount = $multiplier * $markupVal;
        }

        return round($markupAmount, 3);
    }

    /**
     * Get airline list from DB
     */
    public function get_airline_list(): array
    {
        return $this->ci->db
            ->get_where('airline_list', ['is_duplicate' => 0])
            ->result_array();
    }

    /**
     * Get airline class label from class code
     */
    public function get_airline_class_label(?string $className): string
    {
        return match ($className) {
            'C' => 'Business Class',
            'W' => 'Premium Economy',
            'Y' => 'Economy',
            'P' => 'Premium First Class',
            'F' => 'First Class',
            default => '',
        };
    }

    /**
     * Returns the next highest integer by rounding up
     */
    public static function get_round_price(float $price): int
    {
        return (int)ceil($price);
    }

    /**
     * Filters booking source (currently just returns input with validation)
     */
    public function filter_booking_source(array $activeBookingSourceList, array $safeSearchData): array
    {
        if (!empty($activeBookingSourceList)) {
            return $activeBookingSourceList;
        }

        return [];
    }

    /**
     * Returns Single Pax Breakdown
     */
    public function get_single_pax_fare_breakup(array $passengerFareBreakdown): array
    {
        $singlePaxFareBreakup = [];

        foreach ($passengerFareBreakdown as $k => $v) {
            $passengerCount = $v['PassengerCount'] ?: 1;
            $singlePaxFareBreakup[$k] = [
                'BasePrice' => $v['BasePrice'] / $passengerCount,
                'Tax' => $v['Tax'] / $passengerCount,
                'TotalPrice' => $v['TotalPrice'] / $passengerCount,
            ];
        }

        return $singlePaxFareBreakup;
    }
	 /**
     * Checks if ticket is eligible for cancellation.
     */
    public function elgible_for_ticket_cancellation(
        string $appReference,
        string $sequenceNumber,
        array $ticketIds,
        bool $isFullBookingCancel,
        string $bookingSource
    ): array {
    	$ci = & get_instance ();
        $response = [
            'status'  => FAILURE_STATUS,
            'data'    => [],
            'message' => '',
        ];

        $bookingDetails = $this->ci->flight_model->get_flight_booking_transaction_details(
            $appReference,
            $sequenceNumber,
            $bookingSource
        );

        if (($bookingDetails['status'] ?? '') !== SUCCESS_STATUS) {
            $response['message'] = 'AppReference is Not Valid';
            return $response;
        }

        $details = $bookingDetails['data'];
        $transaction = $details['booking_transaction_details'][0];
        $itinerary = $details['booking_itinerary_details'][0];
        $customers = $details['booking_customer_details'];

        $transactionOrigin = $transaction['origin'];
        $travelTimestamp = strtotime($itinerary['departure_datetime']);

        if ($travelTimestamp < time()) {
            $response['message'] = 'Cancellation Failed !! Journey Date is over';
            return $response;
        }

        if ($isFullBookingCancel) {
            $paxRecords = $this->ci->custom_db->single_table_records(
                'flight_booking_passenger_details',
                'origin, status',
                [
                    'flight_booking_transaction_details_fk' => $transactionOrigin,
                    'status' => 'BOOKING_CANCELLED'
                ]
            );

            if (($paxRecords['status'] ?? '') === FAILURE_STATUS) {
                $response['status'] = SUCCESS_STATUS;
            } else {
                $response['message'] = 'Cancellation Failed';
            }

            return $response;
        }

        // Partial cancellation
        $paxStatusMap = [];
        foreach ($customers as $pax) {
            $paxStatusMap[$pax['origin']] = $pax['status'];
        }

        foreach ($ticketIds as $ticketId) {
            if (
                !isset($paxStatusMap[$ticketId]) ||
                $paxStatusMap[$ticketId] !== 'CANCELLATION_INITIALIZED'
            ) {
                $response['message'] = 'Cancellation Failed';
                return $response;
            }
        }

        $response['status'] = SUCCESS_STATUS;
        return $response;
    }

    /**
     * Updates ticket cancellation status.
     */
    public function update_ticket_cancel_status(
        string $appReference,
        string $sequenceNumber,
        int $passengerOrigin
    ): void {
    	$ci = & get_instance ();
        // 1. Update Passenger Status
        $this->ci->custom_db->update_record(
            'flight_booking_passenger_details',
            ['status' => 'BOOKING_CANCELLED'],
            ['origin' => $passengerOrigin]
        );

        // 2. Get Transaction FK
        $tempData = $this->ci->custom_db->single_table_records(
            'flight_booking_passenger_details',
            'flight_booking_transaction_details_fk',
            ['origin' => $passengerOrigin]
        );

        $transactionOrigin = $tempData['data'][0]['flight_booking_transaction_details_fk'] ?? null;

        if ($transactionOrigin !== null) {
            // 3. Update Transaction Cancel Status
            $this->ci->flight_model->update_flight_booking_transaction_cancel_status($transactionOrigin);
        }

        // 4. Update Booking Cancel Status
        $this->ci->flight_model->update_flight_booking_cancel_status($appReference);
    }

    /**
     * Returns cancellation requested passenger details.
     */
    public function get_cancellation_reequested_pax_details(
        array $bookingCustomerDetails,
        array $passengerOrigins
    ): array {
        $indexedPax = [];

        foreach ($bookingCustomerDetails as $pax) {
            $indexedPax[$pax['origin']] = $pax;
        }

        $requested = [];
        foreach ($passengerOrigins as $index => $origin) {
            if (isset($indexedPax[$origin])) {
                $requested[$index] = $indexedPax[$origin];
            }
        }

        return $requested;
    }
	
}
