<?php
require_once BASEPATH . 'libraries/hotel/GRN/Common_api_hotel_v3.php';

class Common_Hotel_v3
{

	/**
	 * Url to be used for combined hotel booking - only for domestic round way
	 */
	public static function combined_booking_url(int|string $search_id): string
	{
		return Common_Api_Hotel::pre_booking_url($search_id);
	}

	/**
	 * Data gets saved in list so remember to use correct source value
	 */
	public static function insert_record(string $key, string $value): array
	{
		$ci = &get_instance();
		$index = $ci->redis_server->store_list($key, $value);

		return [
			'access_key' => $key . DB_SAFE_SEPARATOR . $index . DB_SAFE_SEPARATOR . random_string() . random_string(),
			'index' => $index
		];
	}

	/**
	 * Read records from redis list
	 */
	public static function read_record(string $key, int $offset = -1, int $limit = -1): array
	{
		$ci = &get_instance();
		return $ci->redis_server->read_list($key, $offset, $limit);
	}

	/**
	 * Cache the data as string
	 */
	public static function insert_string(string $key, string $value): void
	{
		$ci = &get_instance();
		$ci->redis_server->store_string($key, $value);
	}

	/**
	 * Read string data from cache
	 */
	public static function read_string(string $key): ?string
	{
		$ci = &get_instance();
		return $ci->redis_server->read_string($key);
	}

	/**
	 * Save hotel booking data
	 */
	public function save_hotel_booking(
		array $hotel_data,
		array $passenger_details,
		string $app_reference,
		string $booking_source,
		int|string $search_id
	): array {
		$data = [
			'status' => SUCCESS_STATUS,
			'message' => ''
		];

		$ci = &get_instance();

		$porceed_to_save = $this->is_duplicate_hotel_booking($app_reference);
		$porceed_to_save['status'] = SUCCESS_STATUS; // override for testing?

		if ($porceed_to_save['status'] != SUCCESS_STATUS) {
			$data['status'] = $porceed_to_save['status'];
			$data['message'] = $porceed_to_save['message'];
		} else {
			$search_data = $ci->hotel_model_v3->get_safe_search_data_grn($search_id)['data'];
			$fare_details = $hotel_data['Price'];
			$room_details = $hotel_data['RoomPriceBreakup'];
			$master_booking_status = 'BOOKING_INPROGRESS';

			$domain_origin = get_domain_auth_id();
			$hotel_booking_status = $master_booking_status;
			$currency = domain_base_currency();
			$currency_obj = new Currency(['module_type' => 'b2c_hotel']);
			$currency_conversion_rate = $currency_obj->get_domain_currency_conversion_rate();

			if ($booking_source == YATRA_HOTEL_BOOKING_SOURCE) {
				$book_total_fare = $fare_details['total_net_fare'];
				$commissionable_fare = $fare_details['total_fare'];
			} else {
				$book_total_fare = $fare_details['total_fare'];
				$commissionable_fare = $fare_details['total_fare'];
			}

			$book_domain_markup = $fare_details['admin_markup'];
			$book_level_one_markup = 0;
			$book_gst_price = $fare_details['gst'];
			$book_hotel_markup_price = $fare_details['hotel_markup_price'];
			$book_markup_gst = $fare_details['admin_markup_gst'];

			$hotel_name = $hotel_data['ResultToken']['HotelName'];
			$star_rating = $hotel_data['ResultToken']['StarRating'];
			$hotel_code = $hotel_data['ResultToken']['HotelCode'];
			$phone_number = $passenger_details[0]['PassengerDetails'][0]['Phoneno'];
			$email = $passenger_details[0]['PassengerDetails'][0]['Email'];
			$hotel_check_in = date('Y-m-d', strtotime($search_data['from_date']));
			$hotel_check_out = date('Y-m-d', strtotime($search_data['to_date']));
			$attributes = '';
			$booking_id = $booking_reference = $confirmation_reference = '';
			$payment_mode = 'PNHB1';
			$created_by_id = 0;

			$ci->hotel_model_v3->save_booking_details(
				$domain_origin,
				$hotel_booking_status,
				$app_reference,
				$booking_source,
				$booking_id,
				$booking_reference,
				$confirmation_reference,
				$book_total_fare,
				$book_domain_markup,
				$book_level_one_markup,
				$currency,
				$hotel_name,
				$star_rating,
				$hotel_code,
				$phone_number,
				'',
				$email,
				$hotel_check_in,
				$hotel_check_out,
				$payment_mode,
				$attributes,
				$created_by_id,
				$currency_conversion_rate,
				HOTEL_VERSION_2,
				$book_gst_price,
				$book_hotel_markup_price,
				$book_markup_gst
			);

			foreach ($room_details as $room_v) {
				$location = $search_data['city_name'];
				$room_type_name = $room_v['RoomTypeName'];
				$bed_type_code = $room_v['RoomTypeCode'];
				$status = $master_booking_status;
				$smoking_preference = $room_v['SmokingPreference'];
				$commission_percentage = 0;

				if ($booking_source == YATRA_HOTEL_BOOKING_SOURCE) {
					$total_fare = $room_v['Price']['OfferedPriceRoundedOff'];
					$commissionable_fare = $room_v['Price']['PublishedPriceRoundedOff'];
					$commission_percentage = $room_v['Price']['CommissionPercentage'];
					$RoomPrice = $room_v['Price']['OfferedPriceRoundedOff'];
				} else {
					$total_fare = $room_v['Price']['PublishedPriceRoundedOff'];
					$commissionable_fare = $room_v['Price']['PublishedPriceRoundedOff'] + $room_v['Price']['AgentCommission'] - $room_v['Price']['TDS'];
					$RoomPrice = $room_v['Price']['RoomPrice'];
				}

				$domain_markup = $fare_details['admin_markup'] / $search_data['room_count'];
				$level_one_markup = 0;
				$Tax = $room_v['Price']['Tax'];
				$ExtraGuestCharge = $room_v['Price']['ExtraGuestCharge'];
				$ChildCharge = $room_v['Price']['ChildCharge'];
				$OtherCharges = $room_v['Price']['OtherCharges'];
				$Discount = $room_v['Price']['Discount'];
				$ServiceTax = $room_v['Price']['ServiceTax'];
				$AgentCommission = $room_v['Price']['AgentCommission'];
				$AgentMarkUp = $room_v['Price']['AgentMarkUp'];
				$TDS = $room_v['Price']['TDS'];

				$ci->hotel_model_v3->save_booking_itinerary_details(
					$app_reference,
					$location,
					$hotel_check_in,
					$hotel_check_out,
					$room_type_name,
					$bed_type_code,
					$status,
					$smoking_preference,
					$total_fare,
					$domain_markup,
					$level_one_markup,
					$currency,
					serialize($attributes),
					$RoomPrice,
					$Tax,
					$ExtraGuestCharge,
					$ChildCharge,
					$OtherCharges,
					$Discount,
					$ServiceTax,
					$AgentCommission,
					$AgentMarkUp,
					$TDS,
					$commissionable_fare,
					$commission_percentage
				);
			}

			foreach ($passenger_details as $pax_v) {
				foreach ($pax_v['PassengerDetails'] as $passenger_v) {
					$title = $passenger_v['Title'];
					$first_name = $passenger_v['FirstName'];
					$middle_name = empty($passenger_v['MiddleName']) ? $passenger_v['LastName'] : $passenger_v['MiddleName'];
					$last_name = $passenger_v['LastName'];
					$phone = $passenger_v['Phoneno'];
					$pax_type = $passenger_v['PaxType'] == "1" ? "Adult" : "Child";
					$pan = '';
					if(isset($passenger_v['PAN'])){
						$pan = $passenger_v['PAN'];
					}
					$date_of_birth = $passenger_v['Age'] ? date('Y-m-d', strtotime("-" . $passenger_v['Age'] . ' Year')) : null;
					$passenger_nationality = 'India';
					$passport_number = '959595959';
					$passport_issuing_country = 'India';
					$passport_expiry_date = '2016-01-15';

					$ci->hotel_model_v3->save_booking_pax_details(
						$app_reference,
						$title,
						$first_name,
						$middle_name,
						$last_name,
						$phone,
						$email,
						$pax_type,
						$date_of_birth,
						$passenger_nationality,
						$passport_number,
						$passport_issuing_country,
						$passport_expiry_date,
						$pan,
						$status,
						serialize($attributes)
					);
				}
			}
		}

		return $data;
	}
	/**
	 * Get Booked Hotel Details
	 */
	public function get_hotel_booking_transaction_details(string $app_reference): array
	{
		$data = [];
		$ci = &get_instance();
		$data['status'] = FAILURE_STATUS;
		$data['data'] = [];
		$data['message'] = '';

		$formatted_booking_details = [];
		$app_reference = trim($app_reference);

		$booking_details = $ci->custom_db->single_table_records('hotel_booking_details', '*', ['app_reference' => $app_reference]);

		if (
			$booking_details['status'] == SUCCESS_STATUS &&
			in_array($booking_details['data'][0]['status'], ['BOOKING_CONFIRMED', 'BOOKING_HOLD'], true)
		) {

			$booking_details = $booking_details['data'][0];

			$formatted_booking_details['ConfirmationNo'] = $booking_details['confirmation_reference'];
			$formatted_booking_details['BookingRefNo'] = $booking_details['booking_reference'];
			$formatted_booking_details['BookingId'] = $booking_details['booking_id'];
			$formatted_booking_details['booking_status'] = $booking_details['status'];

			$data['status'] = SUCCESS_STATUS;
			$data['data']['BookingDetails'] = $formatted_booking_details;
		} else {
			$data['message'] = 'Invalid Request';
		}

		return $data;
	}

	/**
	 * Deduct hotel booking amount for confirmed bookings
	 */
	public function deduct_hotel_booking_amount(string $app_reference): void
	{
		$ci = &get_instance();
		$app_reference = trim($app_reference);

		$data = $ci->db->query("
        SELECT BD.* 
        FROM hotel_booking_details BD
        WHERE BD.app_reference = '{$app_reference}'
    ")->row_array();

		if (valid_array($data) && in_array($data['status'], ['BOOKING_CONFIRMED'], true)) {
			$agent_buying_price = $data['total_fare'] + $data['domain_markup'];

			$domain_booking_attr = [
				'app_reference'     => $app_reference,
				'transaction_type'  => 'hotel',
			];

			$ci->domain_management->debit_domain_balance(
				$agent_buying_price,
				hotel_v3::get_credential_type(),
				get_domain_auth_id(),
				$domain_booking_attr
			);

			$agent_transaction_amount = $agent_buying_price - $data['domain_markup'];
			$currency = $data['currency'];
			$currency_conversion_rate = $data['currency_conversion_rate'];
			$remarks = 'hotel Transaction was Successfully done';

			$ci->domain_management_model->save_transaction_details(
				'hotel',
				$app_reference,
				$agent_transaction_amount,
				$data['domain_markup'],
				0, // level_one_markup
				$remarks,
				$currency,
				$currency_conversion_rate
			);
		}
	}

	/**
	 * Check for duplicate hotel booking
	 */
	private function is_duplicate_hotel_booking(string $app_reference): array
	{
		$ci = &get_instance();
		$data = [
			'status' => SUCCESS_STATUS,
			'message' => ''
		];

		$hotel_booking_details = $ci->custom_db->single_table_records(
			'hotel_booking_details',
			'*',
			['app_reference' => trim($app_reference)]
		);

		if ($hotel_booking_details['status'] === true && valid_array($hotel_booking_details['data'][0])) {
			$data['status'] = FAILURE_STATUS;
			$data['message'] = 'Duplicate Booking Not Allowed';
		}

		return $data;
	}

	/**
	 * Update markup and cache key to token
	 */
	public function update_markup_and_insert_cache_key_to_token(
		array $hotel_list,
		string $carry_cache_key,
		int|string $search_id,
		bool $store_full_result = true
	): array {
		$ci = &get_instance();
		$search_data = $ci->hotel_model_v3->get_safe_search_data_grn($search_id);
		$search_data = $search_data['data'];

		$no_of_nights = 1; // fallback
		$multiplier = $no_of_nights;
		$commission_percentage = 0;

		foreach ($hotel_list as $j_hotel => &$j_hotel_list) {
			$temp_token = array_values(unserialized_data($j_hotel_list['ResultToken']));
			$booking_source= '';
			if(isset($temp_token[0]['booking_source'])){
				$booking_source = $temp_token[0]['booking_source'];
			}
			$cache_data = $store_full_result ? $j_hotel_list : ['ResultToken' => $j_hotel_list['ResultToken']];
			$access_data = Common_hotel_v3::insert_record($carry_cache_key, json_encode($cache_data));

			$hotel_list[$j_hotel]['ResultToken'] = $access_data['access_key'];

			$this->update_fare_markup_commission(
				$j_hotel_list['Price'],
				$multiplier,
				$commission_percentage,
				true,
				$booking_source,
				$no_of_nights
			);
		}

		return $hotel_list;
	}

	/**
	 * Cache first hotel room list
	 */
	public function update_hotel_first_room_unique(
		array $first_room_list,
		string $carry_cache_key,
		int|string $search_id
	): string {
		$temp_token = array_values(unserialized_data($first_room_list['Room_data']['RoomUniqueId']));
		$booking_source = $temp_token[0]['booking_source'];

		$access_data = Common_hotel_v3::insert_record(
			$carry_cache_key,
			json_encode($first_room_list['Room_data'])
		);

		return $access_data['access_key'];
	}
	public function cache_room_list(array $room_list, string $carry_cache_key, int|string $search_id): array
	{
		$ci = &get_instance();
		$currency_obj = new Currency(['from' => get_application_default_currency(), 'to' => domain_base_currency()]);

		$search_data = $ci->hotel_model_v3->get_safe_search_data_grn($search_id);
		$search_data = $search_data['data'];
		$no_of_nights = $search_data['no_of_nights'];
		$multiplier = $no_of_nights;

		$commission_percentage = 0;

		if (valid_array($room_list)) {
			foreach ($room_list as $rm_k => &$rm_v) {
				$temp_token = array_values(unserialized_data($rm_v['RoomUniqueId']));
				$temp_token = array_values($temp_token[0]);
				if(isset($temp_token[0]['booking_source'])){
					$booking_source = $temp_token[0]['booking_source'];
				}
				
				$access_data = Common_hotel_v3::insert_record($carry_cache_key, json_encode($rm_v));
				$room_list[$rm_k]['RoomUniqueId'] = $access_data['access_key'];
				$this->update_fare_markup_commission($rm_v['Price'], $multiplier, $commission_percentage, true, $booking_source, $no_of_nights);
				$this->update_fare_markup_commission_cancellation_policy($rm_v['CancellationPolicies'], $multiplier, $commission_percentage, true, $booking_source);
			}
		}

		return $room_list;
	}
	public function cache_block_room_data(array $block_room_data, string $carry_cache_key, int|string $search_id): array
	{
		$ci = &get_instance();
		$currency_obj = new Currency(['from' => get_application_default_currency(), 'to' => domain_base_currency()]);

		$search_data = $ci->hotel_model_v3->get_safe_search_data_grn($search_id);
		$search_data = $search_data['data'];
		$no_of_nights = $search_data['no_of_nights'];
		$multiplier = $no_of_nights;

		$commission_percentage = 0;
		$domain_currency_conversion = true;

		if (valid_array($block_room_data)) {
			$temp_token = array_values(unserialized_data($block_room_data['BlockRoomId']));
			$booking_source = $temp_token[0]['booking_source'];

			$cache_data = ['BlockRoomId' => $block_room_data['BlockRoomId']];
			$access_data = Common_hotel_v3::insert_record($carry_cache_key, json_encode($cache_data));
			$block_room_data['BlockRoomId'] = $access_data['access_key'];

			$HotelRoomsDetails = $block_room_data['HotelRoomsDetails'];
			$markup_currency_obj = new Currency([
				'module_type' => 'b2c_hotel',
				'from' => get_application_default_currency(),
				'to' => get_application_default_currency()
			]);

			$markup_price = $markup_currency_obj->get_currency($HotelRoomsDetails[0]['Price']['PublishedPrice'], true, true, false, $multiplier, '');
			$block_room_markup_plus = isset($markup_price['markup_type']) && $markup_price['markup_type'] === 'plus';

			foreach ($HotelRoomsDetails as $rm_k => &$rm_v) {
				if ($block_room_markup_plus) {
					if ($rm_k === 0) {
						$this->update_fare_markup_commission($rm_v['Price'], $multiplier, $commission_percentage, true, $booking_source);
					} else {
						$this->convert_to_domain_currency_object($rm_v['Price'], $domain_currency_conversion);
					}
				} else {
					$this->update_fare_markup_commission($rm_v['Price'], $multiplier, $commission_percentage, true, $booking_source);
				}
			}

			$block_room_data['HotelRoomsDetails'] = $HotelRoomsDetails;
		}

		return $block_room_data;
	}
	public function update_hotel_details_markup(array $FareHotelDetails, string $booking_source, int|string $search_id): array
	{
		$ci = &get_instance();
		$search_data = $ci->hotel_model_v3->get_safe_search_data_grn($search_id);
		$search_data = $search_data['data'];
		$no_of_nights = $search_data['no_of_nights'];
		$multiplier = $no_of_nights;
		$commission_percentage = 0;

		return $this->update_fare_hotel_details_markup_commission($FareHotelDetails, $multiplier, $commission_percentage, true, $booking_source);
	}
	private function update_fare_hotel_details_markup_commission(array &$FareDetails, int $multiplier, int $commission_percentage, bool $domain_currency_conversion, string $booking_source, int|string $no_of_nights = ''): array
	{
		$ci = &get_instance();
		$booking_source_fk = ''; // Future use for FK lookup

		$total_fare = $FareDetails['PublishedPrice'];

		$currency_obj = new Currency([
			'module_type' => 'b2c_hotel',
			'from' => get_application_default_currency(),
			'to' => get_application_default_currency()
		]);
		$markup_price = $currency_obj->get_currency($total_fare, true, true, false, $multiplier, $booking_source_fk);

		$total_markup = $markup_price['default_value'] - $total_fare;

		$domain_id = get_domain_auth_id();
		$domain_currency_details = $ci->domain_management_model->get_domain_details($domain_id);
		$domain_currency = $domain_currency_details['domain_base_currency'];

		if ($domain_currency === 'INR') {
			$gst_price = 0;
			$FareDetails['RoomPriceWoGST'] += $total_markup;
			if ($total_markup > 0) {
				$gst_price = round((18 / 100) * $total_markup);
				$total_markup += $gst_price;
			}
			$FareDetails['RoomPrice'] += $total_markup;
			$FareDetails['RoomPrice'] = ceil($FareDetails['RoomPrice']);
			$FareDetails['PublishedPrice'] += $total_markup;
			$FareDetails['PublishedPriceRoundedOff'] += $total_markup;
			$FareDetails['PublishedPriceRoundedOff'] = ceil($FareDetails['PublishedPriceRoundedOff']);
			$FareDetails['OfferedPrice'] += $total_markup;
			$FareDetails['OfferedPriceRoundedOff'] += $total_markup;
			$FareDetails['OfferedPriceRoundedOff'] = ceil($FareDetails['OfferedPriceRoundedOff']);
		} else {
			$FareDetails['PublishedPrice'] += $total_markup;
			$FareDetails['PublishedPriceRoundedOff'] += $total_markup;
			$FareDetails['PublishedPriceRoundedOff'] = round($FareDetails['PublishedPriceRoundedOff'], 2);
			$FareDetails['OfferedPrice'] += $total_markup;
			$FareDetails['OfferedPriceRoundedOff'] += $total_markup;
			$FareDetails['OfferedPriceRoundedOff'] = round($FareDetails['OfferedPriceRoundedOff'], 2);
			$FareDetails['RoomPriceWoGST'] += $total_markup;
			$FareDetails['RoomPrice'] += $total_markup;
			$FareDetails['RoomPrice'] = round($FareDetails['RoomPrice'], 2);
		}

		if (isset($FareDetails['GST'])) {
			unset($FareDetails['GST']);
		}

		return $this->convert_to_domain_currency_object_hotel_details($FareDetails, $domain_currency_conversion);
	}
	/**
	 * Convert Fare Object to Domain Currency
	 */
	private function convert_to_domain_currency_object_hotel_details(array &$FareDetails, bool $domain_currency_conversion = true): array
	{
		$domain_base_currency = $domain_currency_conversion
			? domain_base_currency()
			: get_application_default_currency();

		$currency_obj = new Currency([
			'from' => get_application_default_currency(),
			'to' => $domain_base_currency
		]);

		$FareDetails['CurrencyCode'] = $domain_base_currency;

		$keys = [
			'RoomPrice',
			'Tax',
			'ExtraGuestCharge',
			'ChildCharge',
			'OtherCharges',
			'Discount',
			'PublishedPrice',
			'PublishedPriceRoundedOff',
			'OfferedPrice',
			'OfferedPriceRoundedOff',
			'AgentCommission',
			'AgentMarkUp',
			'ServiceTax',
			'TDS',
			'RoomPriceWoGST'
		];

		foreach ($keys as $key) {
			$FareDetails[$key] = get_converted_currency_value(
				$currency_obj->force_currency_conversion($FareDetails[$key])
			);
		}

		return $FareDetails;
	}
	/**
	 * Convert Cancellation Policy for Cancel hotel from TBO
	 */
	public function update_fare_markup_commission_cancel_policy_tbo(
		array $CancellationCharage,
		int $multiplier,
		int $commission_percentage,
		bool $domain_currency_conversion,
		string $booking_source
	): array {
		$ci = &get_instance();
		$booking_source_fk = ''; // Future use

		$domain_id = get_domain_auth_id();
		$domain_currency_details = $ci->domain_management_model->get_domain_details($domain_id);
		$domain_currency = $domain_currency_details['domain_base_currency'];

		$currency_obj = new Currency([
			'module_type' => 'b2c_hotel',
			'from' => get_application_default_currency(),
			'to' => get_application_default_currency()
		]);

		foreach ($CancellationCharage as $key => $value) {
			if ($value['Charge'] != 0) {
				$total_charge = $value['Charge'];
				$markup_price = $currency_obj->get_currency($total_charge, true, true, false, $multiplier, $booking_source_fk);
				$total_markup = $markup_price['default_value'] - $total_charge;

				$gst_price = 0;
				if ($domain_currency === 'INR') {
					if ($total_markup > 0) {
						$gst_price = round((18 / 100) * $total_markup);
					}
					$cancellation_charge = $total_markup + $gst_price;
					$CancellationCharage[$key]['Charge'] += $cancellation_charge;
					$CancellationCharage[$key]['Charge'] = ceil($CancellationCharage[$key]['Charge']);
				} else {
					$CancellationCharage[$key]['Charge'] += $total_markup;
					$CancellationCharage[$key]['Charge'] = round($CancellationCharage[$key]['Charge'], 2);
				}

				$cancel_charge = $this->convert_to_domain_currency_object_cancel(
					$CancellationCharage[$key],
					$domain_currency_conversion
				);
				$CancellationCharage[$key]['Charge'] = $cancel_charge;
			}
		}

		return $CancellationCharage;
	}
	/**
	 * Convert Cancellation Policy for Cancel hotel
	 */
	public function update_fare_markup_commission_cancel_policy(array &$CancellationCharage, float $multiplier, float $commission_percentage, bool $domain_currency_conversion, string $booking_source): array
	{
		$ci = &get_instance();
		$booking_source_fk = ''; // Placeholder for future use

		$domain_id = get_domain_auth_id();
		$domain_currency_details = $ci->domain_management_model->get_domain_details($domain_id);
		$domain_currency = $domain_currency_details['domain_base_currency'];

		$currency_obj = new Currency([
			'module_type' => 'b2c_hotel',
			'from' => get_application_default_currency(),
			'to' => get_application_default_currency()
		]);

		foreach ($CancellationCharage as $key => $value) {
			if ($value['Charge'] != 0) {
				$total_charge = $value['Charge'];
				$markup_price = $currency_obj->get_currency($total_charge, true, true, false, $multiplier, $booking_source_fk);
				$total_markup = ($markup_price['default_value'] - $total_charge);

				$gst_price = 0;
				if ($domain_currency === 'INR' && $total_markup > 0) {
					$gst_price = round((18 / 100) * $total_markup);
				}

				$cancellation_charge = $total_markup + $gst_price;
				$CancellationCharage[$key]['Charge'] += $cancellation_charge;

				$CancellationCharage[$key]['Charge'] = ($domain_currency === 'INR')
					? ceil($CancellationCharage[$key]['Charge'])
					: round($CancellationCharage[$key]['Charge'], 2);

				$cancel_charge = $this->convert_to_domain_currency_object_cancel($CancellationCharage[$key], $domain_currency_conversion);
				$CancellationCharage[$key]['Charge'] = $cancel_charge;
			}
		}
		return $CancellationCharage;
	}
	/**
	 * Adding the Markup and Commission for Cancellation Policy
	 */
	private function update_fare_markup_commission_cancellation_policy(
		array &$CancellationCharage,
		int $multiplier,
		int $commission_percentage,
		bool $domain_currency_conversion,
		string $booking_source,
		int|string $no_of_nights = ''
	): array {
		$ci = &get_instance();
		$booking_source_fk = ''; // Future use

		$domain_id = get_domain_auth_id();
		$domain_currency_details = $ci->domain_management_model->get_domain_details($domain_id);
		$domain_currency = $domain_currency_details['domain_base_currency'];

		$currency_obj = new Currency([
			'module_type' => 'b2c_hotel',
			'from' => get_application_default_currency(),
			'to' => get_application_default_currency()
		]);

		foreach ($CancellationCharage as $key => $value) {
			if ($value['Charge'] != 0) {
				$total_charge = $value['Charge'];
				$markup_price = $currency_obj->get_currency($total_charge, true, true, false, $multiplier, $booking_source_fk);
				$total_markup = $markup_price['default_value'] - $total_charge;

				$gst_price = 0;
				if ($domain_currency === 'INR') {
					if ($total_markup > 0) {
						$gst_price = round((18 / 100) * $total_markup);
					}
					$cancellation_charge = $total_markup + $gst_price;
					$CancellationCharage[$key]['Charge'] += $cancellation_charge;
					$CancellationCharage[$key]['Charge'] = ceil($CancellationCharage[$key]['Charge']);
				} else {
					$CancellationCharage[$key]['Charge'] += $total_markup;
					$CancellationCharage[$key]['Charge'] = round($CancellationCharage[$key]['Charge'], 2);
				}

				$this->convert_to_domain_currency_object_cancellation($CancellationCharage[$key], $domain_currency_conversion);
			}
		}

		return $CancellationCharage;
	}

	private function convert_to_domain_currency_object_cancel(array &$CancellationCharage, bool $domain_currency_conversion = true): float
	{
		$domain_base_currency = $domain_currency_conversion
			? domain_base_currency()
			: get_application_default_currency();

		$currency_obj = new Currency([
			'from' => get_application_default_currency(),
			'to' => $domain_base_currency
		]);

		$CancellationCharage['Currency'] = $domain_base_currency;
		return get_converted_currency_value($currency_obj->force_currency_conversion($CancellationCharage['Charge']));
	}
	private function convert_to_domain_currency_object_cancellation(array &$CancellationCharage, bool $domain_currency_conversion = true): void
	{
		$domain_base_currency = $domain_currency_conversion
			? domain_base_currency()
			: get_application_default_currency();

		$currency_obj = new Currency([
			'from' => get_application_default_currency(),
			'to' => $domain_base_currency
		]);

		$CancellationCharage['Currency'] = $domain_base_currency;
		$CancellationCharage['Charge'] = get_converted_currency_value($currency_obj->force_currency_conversion($CancellationCharage['Charge']));
	}
	/**
	 * Adding the Markup and Commission
	 *
	 * @param array $FareDetails
	 * @param float $multiplier
	 * @param float $commission_percentage
	 * @param bool $domain_currency_conversion
	 * @param string $booking_source
	 * @param int|string $no_of_nights
	 */
	private function update_fare_markup_commission(
		array &$FareDetails,
		float $multiplier,
		float $commission_percentage,
		bool $domain_currency_conversion,
		string $booking_source,
		int|string $no_of_nights = ''
	): void {
		$ci = &get_instance();

		// Get booking_source_fk - currently unused (left as empty string)
		$booking_source_fk_result = $ci->custom_db->single_table_records('booking_source', 'origin', ['source_id' => trim($booking_source)]);
		$booking_source_fk = '';

		$total_fare = $FareDetails['PublishedPrice'];

		$currency_obj = new Currency([
			'module_type' => 'b2c_hotel',
			'from' => get_application_default_currency(),
			'to' => get_application_default_currency()
		]);

		$markup_price = $currency_obj->get_currency($total_fare, true, true, false, $multiplier, $booking_source_fk);
		$total_markup = $markup_price['default_value'] - $total_fare;

		$gst_price = 0;
		$domain_id = get_domain_auth_id();
		$domain_currency_details = $ci->domain_management_model->get_domain_details($domain_id);
		$domain_currency = $domain_currency_details['domain_base_currency'];

		$no_of_nights = ($no_of_nights !== '') ? (int)$no_of_nights : 1;

		if ($domain_currency === 'INR') {
			if ($total_markup > 0) {
				$gst_price = round((18 / 100) * $total_markup);
			}

			$price_with_gst = $total_markup + $gst_price;
			$night_price = $price_with_gst;

			if (isset($FareDetails['GSTPrice'])) {
				$FareDetails['GSTPrice'] = $gst_price;
			}

			$FareDetails['RoomPrice'] += $night_price;
			$FareDetails['RoomPrice'] = round($FareDetails['RoomPrice'] / $no_of_nights, 2);

			$FareDetails['PublishedPrice'] += $night_price;
			$FareDetails['PublishedPrice'] = $FareDetails['PublishedPrice'] / $no_of_nights;

			$FareDetails['PublishedPriceRoundedOff'] += $night_price;
			$FareDetails['PublishedPriceRoundedOff'] = round($FareDetails['PublishedPriceRoundedOff'] / $no_of_nights, 2);

			$FareDetails['OfferedPrice'] += $night_price;
			$FareDetails['OfferedPrice'] = $FareDetails['OfferedPrice'] / $no_of_nights;

			$FareDetails['OfferedPriceRoundedOff'] += $night_price;
			$FareDetails['OfferedPriceRoundedOff'] = ceil($FareDetails['OfferedPriceRoundedOff'] / $no_of_nights);

			if (isset($FareDetails['RoomPriceWoGST'])) {
				$FareDetails['RoomPriceWoGST'] += $total_markup;
				$FareDetails['RoomPriceWoGST'] = round($FareDetails['RoomPriceWoGST'] / $no_of_nights, 2);
			}
		} else {
			$FareDetails['RoomPrice'] += $total_markup;
			$FareDetails['RoomPrice'] = round($FareDetails['RoomPrice'] / $no_of_nights, 2);

			$FareDetails['PublishedPrice'] += $total_markup;
			$FareDetails['PublishedPrice'] = $FareDetails['PublishedPrice'] / $no_of_nights;

			$FareDetails['PublishedPriceRoundedOff'] += $total_markup;
			$FareDetails['PublishedPriceRoundedOff'] = round($FareDetails['PublishedPriceRoundedOff'] / $no_of_nights, 2);

			$FareDetails['OfferedPrice'] += $total_markup;
			$FareDetails['OfferedPrice'] = $FareDetails['OfferedPrice'] / $no_of_nights;

			$FareDetails['OfferedPriceRoundedOff'] += $total_markup;
			$FareDetails['OfferedPriceRoundedOff'] = $FareDetails['OfferedPriceRoundedOff'] / $no_of_nights;

			if (isset($FareDetails['RoomPriceWoGST'])) {
				$FareDetails['RoomPriceWoGST'] += $total_markup;
				$FareDetails['RoomPriceWoGST'] = round($FareDetails['RoomPriceWoGST'] / $no_of_nights, 2);
			}
		}

		if (isset($FareDetails['GST'])) {
			unset($FareDetails['GST']);
		}

		$this->convert_to_domain_currency_object($FareDetails, $domain_currency_conversion);
	}
	/**
	 * Convert Fare Object to Domain Currency
	 */
	private function convert_to_domain_currency_object(array &$FareDetails, bool $domain_currency_conversion = true): void
	{
		$domain_base_currency = $domain_currency_conversion
			? domain_base_currency()
			: get_application_default_currency();

		$currency_obj = new Currency([
			'from' => get_application_default_currency(),
			'to' => $domain_base_currency
		]);

		$FareDetails['CurrencyCode'] = $domain_base_currency;

		$FareDetails['RoomPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['RoomPrice']));
		$FareDetails['Tax'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['Tax']));
		$FareDetails['ExtraGuestCharge'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['ExtraGuestCharge']));
		$FareDetails['ChildCharge'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['ChildCharge']));
		$FareDetails['OtherCharges'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['OtherCharges']));
		$FareDetails['Discount'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['Discount']));
		$FareDetails['PublishedPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['PublishedPrice']));
		$FareDetails['PublishedPriceRoundedOff'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['PublishedPriceRoundedOff']));
		$FareDetails['OfferedPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['OfferedPrice']));
		$FareDetails['OfferedPriceRoundedOff'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['OfferedPriceRoundedOff']));
		$FareDetails['AgentCommission'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['AgentCommission']));
		$FareDetails['AgentMarkUp'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['AgentMarkUp']));
		$FareDetails['ServiceTax'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['ServiceTax']));
		$FareDetails['TDS'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['TDS']));

		if (isset($FareDetails['RoomPriceWoGST'])) {
			$FareDetails['RoomPriceWoGST'] = get_converted_currency_value($currency_obj->force_currency_conversion($FareDetails['RoomPriceWoGST']));
		}
	}
	/**
	 * Returns Booking Transaction Amount Details
	 *
	 * @param array $core_price_details
	 * @param int|string $search_id
	 * @param string $booking_source
	 * @return array
	 */
	public function final_booking_transaction_fare_details(array $core_price_details, int|string $search_id, string $booking_source): array
	{
		$ci = &get_instance();
		$final_booking_transaction_fare_details = [];
		$search_data = $ci->hotel_model_v3->get_safe_search_data_grn($search_id);
		$search_data = $search_data['data'];
		$no_of_nights = $search_data['no_of_nights'];
		$multiplier = $no_of_nights;

		$domain_id = get_domain_auth_id();
		$commission_percentage = 0;
		$domain_currency_conversion = false;

		$core_total_price = 0;
		$updated_total_price = 0;
		$markup_gst_price = 0;
		$updated_markup_gst_price = 0;
		$markup_currency_obj = new Currency([
			'module_type' => 'b2c_hotel',
			'from' => get_application_default_currency(),
			'to' => get_application_default_currency()
		]);

		$markup_price = $markup_currency_obj->get_currency($core_price_details[0]['Price']['PublishedPrice'], true, true, false, $multiplier, '');
		$block_room_markup_plus = false;
		if (isset($markup_price['markup_type']) && $markup_price['markup_type'] === 'plus') {
			$block_room_markup_plus = true;
		}

		foreach ($core_price_details as $k => &$v) {
			$core_total_price += $v['Price']['PublishedPriceRoundedOff'];

			if ($block_room_markup_plus && $k === 0) {
				$this->update_fare_markup_commission($v['Price'], $multiplier, $commission_percentage, $domain_currency_conversion, $booking_source);
				if (isset($v['Price']['GSTPrice'])) {
					$markup_gst_price += $v['Price']['GSTPrice'];
				}
				$updated_markup_gst_price += $v['Price']['RoomPriceWoGST'];
			} else {
				$this->update_fare_markup_commission($v['Price'], $multiplier, $commission_percentage, $domain_currency_conversion, $booking_source);
				if (isset($v['Price']['GSTPrice'])) {
					$markup_gst_price += $v['Price']['GSTPrice'];
				}
				$updated_markup_gst_price += $v['Price']['RoomPriceWoGST'];
			}
			$updated_total_price += $v['Price']['PublishedPriceRoundedOff'];
		}
		$admin_markup = ($updated_total_price - $core_total_price);
		$gst_price = $markup_gst_price;
		$hotel_markup_price = ($updated_total_price - $updated_markup_gst_price);
		$admin_markup_gst = ($admin_markup - $gst_price);

		$final_booking_transaction_fare_details['RoomPriceBreakup'] = $core_price_details;
		$final_booking_transaction_fare_details['Price'] = [];
		$final_booking_transaction_fare_details['Price']['total_fare'] = $core_total_price;
		$final_booking_transaction_fare_details['Price']['admin_markup'] = round($admin_markup, 1);
		$final_booking_transaction_fare_details['Price']['gst'] = round($gst_price, 1);
		$final_booking_transaction_fare_details['Price']['hotel_markup_price'] = round($hotel_markup_price, 1);
		$final_booking_transaction_fare_details['Price']['admin_markup_gst'] = round($admin_markup_gst, 1);
		$final_booking_transaction_fare_details['Price']['client_buying_price'] = floatval($updated_total_price);

		return $final_booking_transaction_fare_details;
	}
	/**
	 * Update Cancellation Refund details
	 *
	 * @param array $cancellation_details
	 * @param string $app_reference
	 * @return array
	 */
	public function update_domain_cancellation_refund_details(array $cancellation_details, string $app_reference): array
	{
		$ci = &get_instance();
		$HotelChangeRequestStatusResult = [];

		$upadted_cancellation_details = $this->add_cancellation_charge($cancellation_details);
		$RefundedAmount = floatval($upadted_cancellation_details['RefundedAmount']);
		$CancellationCharge = floatval($upadted_cancellation_details['CancellationCharge']);

		$cancelltion_domain_attr = [];
		$cancelltion_domain_attr['app_reference'] = $app_reference;
		$cancelltion_domain_attr['transaction_type'] = 'hotel';

		$ci->domain_management->credit_domain_balance(
			$RefundedAmount,
			hotel_v3::get_credential_type(),
			get_domain_auth_id(),
			$cancelltion_domain_attr
		);

		$domain_base_currency = domain_base_currency();
		$currency_obj = new Currency([
			'from' => get_application_default_currency(),
			'to' => $domain_base_currency
		]);

		$currency_conversion_rate = $currency_obj->get_domain_currency_conversion_rate();
		$refund_status = 'PROCESSED';
		$ci->hotel_model_v3->update_refund_details(
			$app_reference,
			$refund_status,
			$RefundedAmount,
			$CancellationCharge,
			$domain_base_currency,
			$currency_conversion_rate
		);

		$fare = - ($RefundedAmount);
		$domain_markup = 0;
		$level_one_markup = 0;
		$remarks = 'hotel Refund was Successfully done';

		$ci->domain_management_model->save_transaction_details(
			'hotel',
			$app_reference,
			$fare,
			$domain_markup,
			$level_one_markup,
			$remarks,
			$domain_base_currency,
			$currency_conversion_rate
		);

		$RefundedAmount = get_converted_currency_value($currency_obj->force_currency_conversion($RefundedAmount));
		$CancellationCharge = get_converted_currency_value($currency_obj->force_currency_conversion($CancellationCharge));

		$HotelChangeRequestStatusResult = $cancellation_details;
		$HotelChangeRequestStatusResult['RefundedAmount'] = $RefundedAmount;
		$HotelChangeRequestStatusResult['CancellationCharge'] = $CancellationCharge;

		return $HotelChangeRequestStatusResult;
	}
	/**
	 * Add cancellation charge
	 * TODO: add Travelomatix cancellation charges
	 *
	 * @param array $cancellation_details
	 * @return array
	 */
	private function add_cancellation_charge(array $cancellation_details): array
	{
		$upadted_cancellation_details = [];
		$upadted_cancellation_details['RefundedAmount'] = floatval($cancellation_details['RefundedAmount']);
		$upadted_cancellation_details['CancellationCharge'] = floatval($cancellation_details['CancellationCharge']);
		return $upadted_cancellation_details;
	}

	/**
	 * Returns Markup Multiplier for hotel
	 *
	 * @param int|string $search_id
	 * @return int
	 */
	private function get_markup_multiplier(int|string $search_id): int
	{
		$ci = &get_instance();
		$search_data = $ci->hotel_model_v3->get_safe_search_data_grn($search_id);
		$search_data = $search_data['data'];

		$no_of_nights = $search_data['no_of_nights'];
		$no_of_rooms = $search_data['room_count'];
		$multiplier = ($no_of_nights * $no_of_rooms);

		return $multiplier;
	}

	/**
	 * Calculate markup
	 *
	 * @param string $markup_type
	 * @param float $markup_val
	 * @param float $total_fare
	 * @param int $multiplier
	 * @return float
	 */
	private function calculate_markup(string $markup_type, float $markup_val, float $total_fare, int $multiplier): float
	{
		if ($markup_type === 'percentage') {
			$markup_amount = ($total_fare * $markup_val) / 100;
		} else {
			$markup_amount = $multiplier * $markup_val;
		}
		return (float) number_format($markup_amount, 3, '.', '');
	}

	/**
	 * Returns the next highest integer value by rounding up
	 *
	 * @param float|int $price
	 * @return int
	 */
	public static function get_round_price(float|int $price): int
	{
		return (int) ceil($price);
	}

	/**
	 * Decrypts a string using MCRYPT_RIJNDAEL_128 (Deprecated in PHP 7.1+)
	 *
	 * @param string $string
	 * @param string $key
	 * @return string|false
	 */
	public function decrypt(string $string, string $key): string|false
	{
		$key = base64_decode($key);
		$algorithm = MCRYPT_RIJNDAEL_128;

		$key = md5($key, true);
		$iv_length = mcrypt_get_iv_size($algorithm, MCRYPT_MODE_CBC);
		$string = base64_decode($string);

		if ($string === false || strlen($string) < $iv_length) {
			return false;
		}

		$iv = substr($string, 0, $iv_length);
		$encrypted = substr($string, $iv_length);
		$result = mcrypt_decrypt($algorithm, $key, $encrypted, MCRYPT_MODE_CBC, $iv);

		return $result;
	}
}
