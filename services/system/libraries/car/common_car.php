<?php
declare(strict_types=1);
/**
 * Provab Common Functionality For API Class
 *
 *
 * @package Provab
 * @subpackage provab
 * @category Libraries
 * @author Badri Nath Nayak
 * @link http://www.provab.com
 */
class Common_Car
{
	
	/**
	 * Data gets saved in list so remember to use correct source value
	 *
	 * @param string $key
	 *        	source of the data - will be used as key while saving
	 * @param string $value
	 *        	value which has to be cached - pass json
	 * @return array{
	 *     access_key: string,
	 *     index: int|string
	 * }
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
	 * Reads records from the Redis list based on key, offset, and limit.
	 *
	 * @param string $key
	 * @param int $offset
	 * @param int $limit
	 * @return array<mixed>  Returns an array of items from the list.
	 */
	public static function read_record(string $key, int $offset = -1, int $limit = -1): array
	{
		$ci = &get_instance();
		return $ci->redis_server->read_list($key, $offset, $limit);
	}
	/**
	 * Cache the data as a string.
	 *
	 * @param string $key   The key under which the value will be stored.
	 * @param string $value The value to store.
	 * @return void
	 */
	public static function insert_string(string $key, string $value): void
	{
		$ci = &get_instance();
		$ci->redis_server->store_string($key, $value);
	}
	/**
	 * Read data from cache.
	 *
	 * @param string $key The key for the cached value.
	 * @return string|null The cached value or null if not found.
	 */
	public static function read_string(string $key): ?string
	{
		$ci = &get_instance();
		return $ci->redis_server->read_string($key);
	}
	/**
	 * Update cache keys by saving data to be accessed on the next page and apply markup/commission updates.
	 *
	 * @param array $car_list          List of car data to update.
	 * @param string $carry_cache_key  Cache key used to store the data.
	 * @param int|string $search_id    Identifier for the search (can be string if UUID used).
	 *
	 * @return array Updated car list with new ResultTokens and updated pricing.
	 */
	public function update_markup_and_insert_cache_key_to_token(array $car_list, string $carry_cache_key, int $search_id): array
	{
		$ci = &get_instance();
		$search_data = $ci->car_model->get_safe_search_data($search_id);
		$search_data = $search_data['data'];

		$commission_percentage = 0;
		foreach ($car_list as $j_car => &$j_car_list) {
			$temp_token = array_values(unserialized_data($j_car_list['ResultToken']));
			$booking_source = $temp_token[0]['booking_source'];
			$cache_data = $j_car_list;
			$multiplier = 1;

			// Cache the data
			$access_data = Common_Car::insert_record($carry_cache_key, json_encode($cache_data));

			// Assign the new cache key to ResultToken
			$car_list[$j_car]['ResultToken'] = $access_data['access_key'];
			// Update markup and commission
			$this->update_fare_markup_commission(
				$j_car_list,
				$j_car_list['CancellationPolicy'],
				$multiplier,
				$commission_percentage,
				true,
				$booking_source
			);
		}

		return $car_list;
	}
	/**
	 * Adds markup and commission to fare details, then converts to domain currency.
	 *
	 * @param array $FareDetails                Fare breakdown to be modified (by reference).
	 * @param array $CancellationPolicy         Cancellation policy (passed by reference, used in currency conversion).
	 * @param float|int $multiplier             Value used in price calculations (usually 1).
	 * @param float|int $commission_percentage  Commission to apply (currently unused).
	 * @param bool $domain_currency_conversion  Whether to convert fare details to domain currency.
	 * @param string $booking_source            Booking source identifier.
	 * @return void
	 */
	private function update_fare_markup_commission(
		array &$FareDetails,
		array &$CancellationPolicy,
		float $multiplier,
		float $commission_percentage,
		bool $domain_currency_conversion,
		string $booking_source
	): void {
		error_reporting(E_ALL);

		$ci = &get_instance();

		$total_fare = $FareDetails['EstimatedTotalAmount'];

		$currency_obj = new Currency([
			'module_type' => 'b2c_car',
			'from' => get_application_default_currency(),
			'to' => get_application_default_currency()
		]);

		$markup_price = $currency_obj->get_currency(
			$total_fare,
			true,  // include_markup
			true,  // round_off
			false, // currency_conversion
			$multiplier,
			'',    // custom_currency
			CAR_VERSION_1
		);

		$total_markup = $markup_price['default_value'] - $total_fare;

		$gst_price = 0;
		$domain_id = get_domain_auth_id();
		$domain_currency_details = $ci->domain_management_model->get_domain_details($domain_id);
		$domain_currency = $domain_currency_details['domain_base_currency'];

		// If GST needs to be applied for INR domain, uncomment below
		/*
    if ($domain_currency == 'INR' && $total_markup > 0) {
        $gst_price = round((18 / 100) * $total_markup);
    }
    */

		$FareDetails['Pricebreakup']['RentalPrice'] += $total_markup + $gst_price;
		$FareDetails['EstimatedTotalAmount'] += $total_markup + $gst_price;

		// Convert to domain currency if needed
		$this->convert_to_domain_currency_object($FareDetails, $CancellationPolicy, $domain_currency_conversion);
	}
	/**
	 * Convert fare details and cancellation policy amounts to domain currency.
	 *
	 * @param array $FareDetails          Reference to fare details array.
	 * @param array $CancellationPolicy   Reference to cancellation policy array.
	 * @param bool $domain_currency_conversion Whether to convert to domain currency or use default app currency.
	 *
	 * @return void
	 */
	private function convert_to_domain_currency_object(
		array &$FareDetails,
		array &$CancellationPolicy,
		bool $domain_currency_conversion = true
	): void {
		$domain_base_currency = $domain_currency_conversion
			? domain_base_currency()
			: get_application_default_currency();

		$currency_obj = new Currency([
			'from' => get_application_default_currency(),
			'to' => $domain_base_currency
		]);

		$FareDetails['CurrencyCode'] = $domain_base_currency;

		if (valid_array($CancellationPolicy)) {
			foreach ($CancellationPolicy as $c_key => $c_value) {
				if (!empty($c_value['Amount']) && $c_value['Amount'] > 0) {
					$converted = $currency_obj->force_currency_conversion($c_value['Amount']);
					$CancellationPolicy[$c_key]['Amount'] = get_converted_currency_value($converted);
				}
			}
		}

		$FareDetails['EstimatedTotalAmount'] = get_converted_currency_value(
			$currency_obj->force_currency_conversion($FareDetails['EstimatedTotalAmount'])
		);

		$FareDetails['Pricebreakup']['RentalPrice'] = get_converted_currency_value(
			$currency_obj->force_currency_conversion($FareDetails['Pricebreakup']['RentalPrice'])
		);

		$FareDetails['Pricebreakup']['OnewayFee'] = get_converted_currency_value(
			$currency_obj->force_currency_conversion($FareDetails['Pricebreakup']['OnewayFee'])
		);

		$FareDetails['Pricebreakup']['OtherTaxes'] = get_converted_currency_value(
			$currency_obj->force_currency_conversion($FareDetails['Pricebreakup']['OtherTaxes'])
		);

		$FareDetails['Pricebreakup']['YoungDriverAmount'] = get_converted_currency_value(
			$currency_obj->force_currency_conversion($FareDetails['Pricebreakup']['YoungDriverAmount'])
		);

		$FareDetails['Pay_now'] = get_converted_currency_value(
			$currency_obj->force_currency_conversion($FareDetails['Pay_now'])
		);
	}
	/**
	 * Returns booking transaction amount details with markup and commission breakdown.
	 *
	 * @param array $core_price_details Base fare details before markup.
	 * @param int|string $search_id     Search identifier (could be int or UUID).
	 * @param string $booking_source    Booking source identifier.
	 *
	 * @return array{
	 *     PriceBreakup: array,
	 *     Price: array{
	 *         commissionable_fare: float,
	 *         admin_markup: float,
	 *         admin_gst: float,
	 *         client_buying_price: float
	 *     }
	 * }
	 */
	public function final_booking_transaction_fare_details(
		array $core_price_details,
		int $search_id,
		string $booking_source
	): array {
		$ci = &get_instance();

		$commission_percentage = 0;
		$multiplier = 1;
		$domain_currency_conversion = true;

		$core_commissionable_fare = $core_price_details['EstimatedTotalAmount'];

		$cancellation_policy = [];

		// Update fare details with markup and conversion
		$this->update_fare_markup_commission(
			$core_price_details,
			$cancellation_policy,
			$multiplier,
			$commission_percentage,
			$domain_currency_conversion,
			$booking_source
		);

		$commissionable_fare = $core_price_details['EstimatedTotalAmount'];
		$admin_markup = $commissionable_fare - $core_commissionable_fare;

		$domain_id = get_domain_auth_id();
		$domain_currency_details = $ci->domain_management_model->get_domain_details($domain_id);
		$domain_currency = $domain_currency_details['domain_base_currency'];

		$gst_price = 0;
		// Optional GST logic for Indian domain
		/*
    if ($domain_currency == 'INR' && $admin_markup > 0) {
        $gst_price = round((18 / 100) * $admin_markup);
    }
    */

		return [
			'PriceBreakup' => $core_price_details,
			'Price' => [
				'commissionable_fare' => $core_commissionable_fare,
				'admin_markup' => $admin_markup,
				'admin_gst' => $gst_price,
				'client_buying_price' => (float) $core_price_details['EstimatedTotalAmount']
			]
		];
	}
	/**
	 * Cache extra services and convert their prices to domain currency.
	 *
	 * @param array $extra_services    List of extra services to cache and convert.
	 * @param string $carry_cache_key  Cache key used for storing each service.
	 *
	 * @return array Updated extra services with cache keys and converted amounts.
	 */
	public function cache_extra_services(array $extra_services, string $carry_cache_key): array
	{
		$ci = &get_instance();

		$currency_obj = new Currency([
			'from' => get_application_default_currency(),
			'to' => domain_base_currency()
		]);

		if (valid_array($extra_services)) {
			foreach ($extra_services as $ex_k => $ex_v) {
				// Cache each extra service item
				$access_data = Common_Car::insert_record($carry_cache_key, json_encode($ex_v));
				$extra_services[$ex_k]['ExtraServiceId'] = $access_data['access_key'];

				// Convert the amount to domain currency
				$extra_services[$ex_k]['Amount'] = get_converted_currency_value(
					$currency_obj->force_currency_conversion($extra_services[$ex_k]['Amount'])
				);
			}
		}

		return $extra_services;
	}
	/**
	 * Save car booking, passenger, pricing, and optional extras into DB.
	 *
	 * @param array $car_data API response with car and location details
	 * @param array $passenger_details Customer's personal and extra service data
	 * @param array $car_price_details Final pricing breakdown (fare, markup, GST)
	 * @param string $app_reference Unique reference for booking
	 * @param string $booking_source Booking source (e.g. API provider name)
	 * @param int|string $search_id Search session ID
	 *
	 * @return array{status: string, message: string}
	 */
	function save_car_booking(
		array $car_data,
		array $passenger_details,
		array $car_price_details,
		string $app_reference,
		string $booking_source,
		int $search_id
	): array {
		$ci = &get_instance();
		$data = ['status' => SUCCESS_STATUS, 'message' => ''];

		$proceed = $this->is_duplicate_car_booking($app_reference);
		if ($proceed['status'] != SUCCESS_STATUS) {
			return ['status' => $proceed['status'], 'message' => $proceed['message']];
		}

		$search_data = $ci->car_model->get_safe_search_data($search_id)['data'];
		$currency = domain_base_currency();
		$currency_obj = new Currency(['module_type' => 'b2c_car']);
		$conversion_rate = $currency_obj->get_domain_currency_conversion_rate();
		$car_booking_status = 'BOOKING_INPROGRESS';
		$domain_origin = get_domain_auth_id();

		$pickup_dt = explode(' ', $search_data['pickup_datetime']);
		$return_dt = explode(' ', $search_data['return_datetime']);

		$pickup_date = implode('-', array_reverse(explode('-', $pickup_dt[0])));
		$return_date = implode('-', array_reverse(explode('-', $return_dt[0])));
		$pickup_time = $pickup_dt[1];
		$drop_time = $return_dt[1];

		$pickup_location = $search_data['pickup_location'];
		$drop_location = $search_data['return_location'];

		$pickup_info = $car_data['LocationDetails']['PickUpLocation'];
		$drop_info = $car_data['LocationDetails']['DropLocation'];

		$format_address = function ($loc, $tel) {
			return "{$loc['Address']['StreetNmbr']}, {$loc['Address']['CityName']}, {$loc['Address']['PostalCode']}, {$loc['Address']['CountryName']}, Telephone: {$tel}";
		};

		$pickup_address = $format_address($pickup_info, $pickup_info['Telephone']);
		$drop_address = $format_address($drop_info, $drop_info['Telephone']);

		$final_cancel_date = '';
		if (!empty($car_data['CancellationPolicy'])) {
			foreach ($car_data['CancellationPolicy'] as $policy) {
				if ($policy['Amount'] == 0) {
					$final_cancel_date = $policy['ToDate'];
					break;
				}
			}
		}

		$attributes = [
			'air_condition'        => $car_data['AirConditionInd'],
			'transmission_type'    => $car_data['TransmissionType'],
			'fuel_type'            => $car_data['FuelType'],
			'pass_quantity'        => $car_data['PassengerQuantity'],
			'bagg_quantity'        => $car_data['BaggageQuantity'],
			'door_count'           => $car_data['DoorCount'],
			'unlimited'            => $car_data['Unlimited'],
			'distanceunit'         => $car_data['DistUnitName'],
			'min_age'              => $car_data['RateRestrictions']['MinimumAge'],
			'max_age'              => $car_data['RateRestrictions']['MaximumAge'],
			'payment_rule'         => $car_data['PaymentRules']['PaymentRule'],
			'payment_type'         => $car_data['PaymentRules']['PaymentType'],
			'term_conditions'      => $car_data['TPA_Extensions']['TermsConditions'],
			'supplier_logo'        => $car_data['TPA_Extensions']['SupplierLogo'],
			'pickup_opening_hours' => $pickup_info['OperationSchedules']['Start'] . '-' . $pickup_info['OperationSchedules']['End'],
			'drop_opening_hours'   => $drop_info['OperationSchedules']['Start'] . '-' . $drop_info['OperationSchedules']['End'],
		];

		$ci->car_model->save_booking_itinerary_details(
			$app_reference,
			$pickup_date,
			$return_date,
			$pickup_time,
			$drop_time,
			$pickup_location,
			$drop_location,
			$pickup_address,
			$drop_address,
			$car_data['Name'],
			$car_data['PictureURL'],
			json_encode($car_data['PricedEquip']),
			json_encode($car_data['PricedCoverage']),
			json_encode($car_data['CancellationPolicy']),
			json_encode($attributes),
			$car_booking_status
		);

		$total_fare = (float)$car_price_details['Price']['commissionable_fare'];
		$domain_markup = (float)$car_price_details['Price']['admin_markup'] ?? '0.00';
		$domain_gst = $car_price_details['Price']['admin_gst'] ?? '0.00';

		$ci->car_model->save_car_booking_details(
			$domain_origin,
			$car_booking_status,
			$app_reference,
			$booking_source,
			$currency,
			$passenger_details['ContactNo'],
			$passenger_details['Email'],
			'PNHB1',
			0,
			$conversion_rate,
			$total_fare,
			$domain_markup,
			$domain_gst,
			'',
			'',
			'', // booking_id, booking_ref, supplier_id
			$car_data['Name'],
			$car_data['CompanyShortName'],
			$car_data['VendorCarType'],
			'CarNect',
			$pickup_date,
			$return_date,
			$pickup_time,
			$drop_time,
			$pickup_location,
			$drop_location,
			$drop_address,
			$pickup_address,
			'',
			$final_cancel_date,
			'Cancellation fee',
			isset($car_data['TotalCharge']['Payonpickup']) ? json_encode($car_data['TotalCharge']['Payonpickup']) : '',
			CAR_VERSION_1
		);

		$ci->car_model->save_booking_pax_details(
			$app_reference,
			$passenger_details['Title'],
			$passenger_details['FirstName'],
			$passenger_details['LastName'],
			$passenger_details['ContactNo'],
			$passenger_details['Email'],
			$passenger_details['DateOfBirth'],
			$passenger_details['CountryCode'],
			$passenger_details['CountryName'],
			$passenger_details['City'],
			$passenger_details['PinCode'],
			$passenger_details['AddressLine1'],
			$passenger_details['AddressLine1'],
			$car_booking_status
		);

		// Optional Extras
		if (!empty($passenger_details['ExtraServices']) && valid_array($passenger_details['ExtraServices'])) {
			$extras = [];
			$requested = force_multple_data_format($passenger_details['ExtraServices']);

			foreach ($requested as $service) {
				foreach ($car_data['PricedEquip'] as $equip) {
					$match = false;
					foreach ($service as $type => $val) {
						if (is_numeric($val) && $equip['EquipType'] == $this->map_equip_type($type)) {
							$extras[] = [
								'Currency'   => $equip['CurrencyCode'] ?? '',
								'Amount'     => $equip['Amount'],
								'Description' => $equip['Description'],
								'EquipType'  => $equip['EquipType'],
								'qunatity'   => $val
							];
							$match = true;
							break;
						}
					}
					if ($match) break;
				}
			}

			if (!empty($extras)) {
				$ci->car_model->save_booking_extra_details($app_reference, $extras);
			}
		}

		return $data;
	}


	/**
	 * Checks if it is a duplicate car booking
	 *
	 * @param string $app_reference
	 * @return array{status: string, message: string}
	 */
	private function is_duplicate_car_booking(string $app_reference): array
	{
		$ci = &get_instance();
		$data = [
			'status' => SUCCESS_STATUS,
			'message' => ''
		];

		$car_booking_details = $ci->custom_db->single_table_records(
			'car_booking_details',
			'*',
			['app_reference' => trim($app_reference)]
		);

		if (
			$car_booking_details['status'] == true &&
			isset($car_booking_details['data'][0]) &&
			is_array($car_booking_details['data'][0])
		) {
			$data['status'] = FAILURE_STATUS;
			$data['message'] = 'Duplicate Booking Not Allowed';
		}

		return $data;
	}
	/**
	 * Deducts car booking amount for a confirmed booking.
	 *
	 * @param string $app_reference
	 * @return void
	 */
	public function deduct_car_booking_amount(string $app_reference): void
	{
		$ci = &get_instance();
		$app_reference = trim($app_reference);

		$data = $ci->db->query(
			'SELECT BD.* FROM car_booking_details BD WHERE BD.app_reference = "' . $app_reference . '"'
		)->row_array();

		// Proceed only if booking is confirmed
		if (!empty($data) && isset($data['status']) && $data['status'] == 'BOOKING_CONFIRMED') {

			$agent_buying_price = $data['total_fare'] + $data['domain_markup'] + $data['domain_gst'];

			// Domain balance attributes
			$domain_booking_attr = [
				'app_reference' => $app_reference,
				'transaction_type' => 'car'
			];

			// Deduct the domain balance
			$ci->domain_management->debit_domain_balance(
				$agent_buying_price,
				car::get_credential_type(),
				get_domain_auth_id(),
				$domain_booking_attr
			);

			// Prepare transaction log
			$domain_markup = $data['domain_markup'];
			$domain_gst = $data['domain_gst'];
			$level_one_markup = 0;
			$agent_transaction_amount = $agent_buying_price - $domain_markup - $domain_gst;

			$currency = $data['currency'];
			$currency_conversion_rate = $data['currency_conversion_rate'];
			$remarks = 'car Transaction was Successfully done';

			// Save transaction details
			$ci->domain_management_model->save_transaction_details(
				'car',
				$app_reference,
				$agent_transaction_amount,
				$domain_markup,
				$level_one_markup,
				$remarks,
				$currency,
				$currency_conversion_rate
			);
		}
	}
	/**
	 * Get Car Booking Transaction Details
	 * 
	 * @param string $app_reference
	 * @return array
	 */
	public function get_car_booking_transaction_details(string $app_reference): array
	{
		$ci = &get_instance();

		// Initialize response data
		$data = [
			'status' => FAILURE_STATUS,
			'data' => [],
			'message' => ''
		];

		$formatted_booking_details = [];
		$app_reference = trim($app_reference);

		// Fetch car booking details and extra details
		$booking_details = $ci->custom_db->single_table_records('car_booking_details', '*', ['app_reference' => $app_reference]);
		$booking_extra_details = $ci->custom_db->single_table_records('car_booking_extra_details', '*', ['app_reference' => $app_reference]);

		// Check if booking details are valid and booking status is confirmed
		if ($booking_details['status'] == SUCCESS_STATUS && $booking_details['data'][0]['status'] == 'BOOKING_CONFIRMED') {
			$booking_details = $booking_details['data'][0];
			// Format the booking details
			$formatted_booking_details['BookingRefNo'] = $booking_details['booking_reference'];
			$formatted_booking_details['SupplerIdentifier'] = $booking_details['supplier_identifier'];

			if (!empty($booking_details['account_info'])) {
				$formatted_booking_details['account_info'] = $booking_details['account_info'];
			}

			$formatted_booking_details['BookingId'] = $booking_details['booking_id'];
			$formatted_booking_details['booking_status'] = $booking_details['status'];

			// If extra booking details are found, format them
			if ($booking_extra_details['status'] == SUCCESS_STATUS) {
				$extra_service = [];

				foreach ($booking_extra_details['data'] as $extra_serv) {
					$extra_service[] = [
						'app_reference' => $extra_serv['app_reference'],
						'description' => $extra_serv['description'],
						'equiptype' => $extra_serv['equiptype'],
						'quantity' => $extra_serv['qunatity'],
						'currency' => $extra_serv['currency'],
						'amount' => $extra_serv['amount']
					];
				}

				$formatted_booking_details['extra_services'] = $extra_service;
			}

			// Prepare successful response
			$data['status'] = SUCCESS_STATUS;
			$data['data']['BookingDetails'] = $formatted_booking_details;
		} else {
			// Invalid booking request
			$data['message'] = 'Invalid Request';
		}

		return $data;
	}
	/**
	 * Convert Cancellation Policy for Cancel hotel
	 *
	 * @param array $CancellationCharage
	 * @param float $multiplier
	 * @param float $commission_percentage
	 * @param float $domain_currency_conversion
	 * @param string $booking_source
	 * @return array
	 */
	public function update_fare_markup_commission_cancel_policy(
		array &$CancellationCharage,
		float $multiplier,
		float $commission_percentage,
		float $domain_currency_conversion,
		string $booking_source
	): array {
		$ci = &get_instance();

		// Get booking source details (you can later extend this logic)
		$booking_source_fk = $ci->custom_db->single_table_records('booking_source', 'origin', ['source_id' => trim($booking_source)]);

		// TODO: Calculate markup based on API (booking_source_fk logic)
		$booking_source_fk = ''; // Placeholder

		// Get domain currency details
		$domain_id = get_domain_auth_id();
		$domain_currency_details = $ci->domain_management_model->get_domain_details($domain_id);
		$domain_currency = $domain_currency_details['domain_base_currency'];

		// Initialize the currency conversion object
		$currency_obj = new Currency([
			'module_type' => 'b2c_car',
			'from' => get_application_default_currency(),
			'to' => get_application_default_currency()
		]);

		// Process each cancellation charge
		foreach ($CancellationCharage as $key => $value) {
			if ($value['Amount'] != 0) {
				$total_charge = $value['Amount'];

				// Calculate markup price using currency conversion
				$markup_price = $currency_obj->get_currency($total_charge, true, true, false, $multiplier, '', CAR_VERSION_1);
				$total_markup = ($markup_price['default_value'] - $total_charge);

				// Calculate 18% GST for markup price if domain currency is INR
				$gst_price = 0;
				if ($domain_currency == 'INR' && $total_markup > 0) {
					$gst_price = round((18 / 100) * $total_markup);
				}

				// Update cancellation charge
				if ($domain_currency == 'INR') {
					$cancellation_charge = $total_markup + $gst_price;
					$CancellationCharage[$key]['Amount'] += $cancellation_charge;
				} else {
					$CancellationCharage[$key]['Amount'] += $total_markup;
					$CancellationCharage[$key]['Amount'] = round($CancellationCharage[$key]['Amount'], 2);
				}

				// Convert to domain currency and update cancellation charge
				$cancel_charge = $this->convert_to_domain_currency_object_cancel($CancellationCharage[$key], $domain_currency_conversion);
				$CancellationCharage[$key]['Amount'] = $cancel_charge;
			}
		}

		return $CancellationCharage;
	}
	/**
	 * Convert Fare Object to Domain Currency
	 *
	 * @param array $CancellationCharage
	 * @param bool $domain_currency_conversion
	 * @return float
	 */
	private function convert_to_domain_currency_object_cancel(array &$CancellationCharage, bool $domain_currency_conversion = true): float
	{
		// Determine the domain base currency based on the conversion flag
		$domain_base_currency = $domain_currency_conversion ? domain_base_currency() : get_application_default_currency();

		// Initialize the currency conversion object
		$currency_obj = new Currency([
			'from' => get_application_default_currency(),
			'to' => $domain_base_currency
		]);

		// Convert the cancellation charge amount to domain currency
		$converted_amount = $currency_obj->force_currency_conversion($CancellationCharage['Amount']);

		// Get the final converted value and update the cancellation charge
		$CancellationCharage['Amount'] = get_converted_currency_value($converted_amount);

		return $CancellationCharage['Amount'];
	}
	/**
	 * Jaganath
	 * Update Cancellation Refund details
	 *
	 * @param array $cancellation_details
	 * @param string $app_reference
	 * @return array
	 */
	public function update_domain_cancellation_refund_details(array $cancellation_details, string $app_reference): array
	{
		$ci = &get_instance();
		$CarChangeRequestStatusResult = [];

		// Adding Travelomatix Cancellation Charges
		$updated_cancellation_details = $this->add_cancellation_charge($cancellation_details);

		// Extract and convert refund and cancellation charge amounts
		$RefundedAmount = floatval($updated_cancellation_details['RefundedAmount'] ?? 0);
		$CancellationCharge = floatval($updated_cancellation_details['CancellationCharge'] ?? 0);

		// Credit Refund Amount to Domain Balance
		$cancellation_domain_attr = [
			'app_reference' => $app_reference,
			'transaction_type' => 'car'
		];
		$ci->domain_management->credit_domain_balance($RefundedAmount, car::get_credential_type(), get_domain_auth_id(), $cancellation_domain_attr);

		// Adding Refund Details
		$domain_base_currency = domain_base_currency();
		$currency_obj = new Currency([
			'from' => get_application_default_currency(),
			'to' => $domain_base_currency
		]);
		$currency_conversion_rate = $currency_obj->get_domain_currency_conversion_rate();
		$refund_status = 'PROCESSED';

		// Update refund details in car_model
		$ci->car_model->update_refund_details(
			$app_reference,
			$refund_status,
			$RefundedAmount,
			$CancellationCharge,
			$domain_base_currency,
			$currency_conversion_rate
		);

		// Saving Refund details in transaction log
		$fare = -$RefundedAmount; // Converting to negative for logging
		$domain_markup = 0;
		$level_one_markup = 0;
		$remarks = 'Car Refund was Successfully done';

		$ci->domain_management_model->save_transaction_details(
			'car',
			$app_reference,
			$fare,
			$domain_markup,
			$level_one_markup,
			$remarks,
			$domain_base_currency,
			$currency_conversion_rate
		);

		// Converting the API Fare Currency to Domain Currency
		$RefundedAmount = get_converted_currency_value($currency_obj->force_currency_conversion($RefundedAmount));
		$CancellationCharge = get_converted_currency_value($currency_obj->force_currency_conversion($CancellationCharge));

		// Assigning the cancellation data
		$CarChangeRequestStatusResult = $updated_cancellation_details;
		$CarChangeRequestStatusResult['RefundedAmount'] = $RefundedAmount;
		$CarChangeRequestStatusResult['CancellationCharge'] = $CancellationCharge;

		return $CarChangeRequestStatusResult;
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
		$updated_cancellation_details = [];

		// Check if 'RefundedAmount' and 'CancellationCharge' exist and are valid
		$updated_cancellation_details['RefundedAmount'] = isset($cancellation_details['RefundedAmount'])
			? floatval($cancellation_details['RefundedAmount'])
			: 0.0;

		$updated_cancellation_details['CancellationCharge'] = isset($cancellation_details['CancellationCharge'])
			? floatval($cancellation_details['CancellationCharge'])
			: 0.0;

		return $updated_cancellation_details;
	}
}