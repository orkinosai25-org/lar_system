<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
require_once BASEPATH . 'libraries/Common_Api_Grind.php';

class Provab_hotelcrs extends Common_Api_Grind
{
	private $ClientId;
	private $UserName;
	private $Password;
	private $service_url;
	private $Url;
	public $master_search_data;
	public $search_hash;
	public function __construct()
	{
		$this->CI = &get_instance();
		$GLOBALS['CI']->load->library('Api_Interface');
		$GLOBALS['CI']->load->model('hotel_model');
	}

	public function search_data($search_id)
	{
		$response['status'] = true;
		$response['data'] = array();
		if (empty($this->master_search_data) == true and valid_array($this->master_search_data) == false) {
			$clean_search_details = $GLOBALS['CI']->hotel_model->get_safe_search_data($search_id);


			if ($clean_search_details['status'] == true) {
				$response['status'] = true;
				$response['data'] = $clean_search_details['data'];
				// 28/12/2014 00:00:00 - date format
				$response['data']['from_date'] = date('d/m/Y', strtotime(@$clean_search_details['data']['from_date']));
				$response['data']['to_date'] = date('d/m/Y', strtotime(@$clean_search_details['data']['to_date']));

				$response['data']['raw_from_date'] = @$clean_search_details['data']['from_date'];
				$response['data']['raw_to_date'] = @$clean_search_details['data']['to_date'];
				$response['data']['no_of_nights'] = @$clean_search_details['data']['no_of_nights'];
				$response['data']['location_id'] = $clean_search_details['data']['hotel_destination'];
				$response['data']['CityId'] = @$clean_search_details['data']['hotel_destination'];
				//get countrycode 
				$get_country_code = $GLOBALS['CI']->custom_db->single_table_records('all_api_city_master', '*', array('origin' => $clean_search_details['data']['hotel_destination']));

				if (@$clean_search_details['data']['search_type'] == 'location_search') {
					$response['data']['country_code'] = $clean_search_details['data']['countrycode'];
				} else {
					$response['data']['country_code'] = $get_country_code['data'][0]['country_code'];
				}
				$this->master_search_data = $response['data'];
			} else {
				$response['status'] = false;
			}
		} else {
			$response['data'] = $this->master_search_data;
		}
		$this->search_hash = md5(serialized_data($response['data']));
		return $response;
	}

	function total_price($price_summary)
	{
		return ($price_summary['OfferedPriceRoundedOff']);
	}

	function booking_url($search_id)
	{
		return base_url() . 'index.php/hotel/booking/' . intval($search_id);
	}

	function process_booking($book_id, $booking_params, $type = '')
	{
		$response['status'] = FAILURE_STATUS;
		$response['data'] = array();

		$book_response = array(
			'Status' => false,
			"Message" => ""
		);
		if ($type == 'full') {
			$book_status = 'BOOKING_CONFIRMED';
			$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			$conf_id = '';
			for ($i = 0; $i < 10; $i++) {
				$conf_id .= $chars[mt_rand(0, strlen($chars) - 1)];
			}
			$update_status['status'] = 0;

			$GLOBALS['CI']->db->where('app_reference', $book_id);
			$GLOBALS['CI']->db->update('sys_scheduler', $update_status);
		} else {

			if ($booking_params['payment_method'] == PAY_PART) {
				$book_status = 'PARTIAL_PAID';
				$conf_id = 'N/A';
			} else {
				$book_status = 'BOOKING_CONFIRMED';
				$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
				$conf_id = '';
				for ($i = 0; $i < 10; $i++) {
					$conf_id .= $chars[mt_rand(0, strlen($chars) - 1)];
				}
			}
		}
		$book_request = $this->get_book_request($booking_params, $book_id);

		if ($book_request['status'] == true) {
			if (isset($book_request['data']['request'])) {
				$book_request = $book_request['data']['request'];
				if (empty($book_request) == false) {
					$_book_request = json_decode($book_request, true);

					if (isset($_book_request['BlockRoomId'])) {
						$BlockRoomId = intval($_book_request['BlockRoomId']);
						//unblock rooms and confirm them
						$query = "SELECT * from eco_stays_blocked_rooms WHERE origin = " . $BlockRoomId;
						$holded_details = $this->CI->db->query($query)->result_array();


						if (count($holded_details) > 0) {
							$holded_rooms = $holded_details[0];
							$room_origins = json_decode($holded_rooms["room_origins"], true);
							$dates = json_decode($holded_rooms["dates"], true);
							//unblocking and confirming rooms
							$this->CI->db->set('holded', 'holded - 1', FALSE);
							$this->CI->db->set('booked', 'booked + 1', FALSE);
							$this->CI->db->where_in('room_origin', $room_origins);
							$this->CI->db->where_in('date', $dates);
							$this->CI->db->update('eco_stays_room_availability');

							$book_data = array(
								'room_origins' => json_encode($room_origins),
								'dates' => json_encode($dates),
								'book_id' => $book_id
							);
							$this->CI->db->insert('eco_stays_booked_rooms', $book_data);

							$num_inserts = $this->CI->db->affected_rows();
							$book_origin = -1;
							if (intval($num_inserts) > 0) {
								$book_origin = $this->CI->db->insert_id();

								$book_response['Status'] = true;

								$book_response['CommitBooking'] = array(
									"BookingDetails" => array(
										"booking_status" => $book_status,
										"BookingId" => $book_id,
										"BookingRefNo" => $book_id,
										"ConfirmationNo" => $conf_id,
										"SupplierCode" => null,
										"SupplierVatId" => null
									)
								);
							}
						}

						if ($book_status == 'BOOKING_CONFIRMED') {
							if (intval($BlockRoomId) > 0) {
								$this->CI->db->where('origin', $BlockRoomId);
								$this->CI->db->delete('eco_stays_blocked_rooms');
							}
						}
					}
				}
			}
		}

		$api_book_response_status = $book_response['Status'];
		$book_response['BookResult'] = @$book_response['CommitBooking']['BookingDetails'];
		/**
		 * PROVAB LOGGER *
		 */
		$GLOBALS['CI']->private_management_model->provab_xml_logger('Book_Room', $book_id, 'hotel', $book_request['data']['request'], json_encode($book_response));
		// validate response

		if ($api_book_response_status == true) {
			$response['status'] = SUCCESS_STATUS;
			$response['data']['book_response'] = $book_response;
			$response['data']['booking_params'] = $booking_params;
			// Convert Room Book Data in Application Currency
			$block_data_array = $book_request['data']['request'];
			$room_book_data = json_decode($block_data_array, true);
			$room_book_data['HotelRoomsDetails'] = $this->formate_hotel_room_details($booking_params);

			$response['data']['room_book_data'] = $this->convert_roombook_data_to_application_currency($room_book_data);
		} else {
			$response['data']['message'] = $book_response['Message'];
		}

		return $response;
	}

	function get_book_request($booking_params, $booking_id)
	{

		$search_id = $booking_params['token']['search_id'];
		$safe_search_data = $GLOBALS['CI']->hotel_model->get_search_data($search_id);
		$search_data = json_decode($safe_search_data['search_data'], true);
		$NO_OF_ROOMS = $search_data['rooms'];

		$search_params = $this->search_data($search_id);
		$search_params = $search_params['data'];

		/*************Re-Assign the Pax Room Wise Strats******************************/
		$room_wise_passenger_info = array();
		for ($i = 0; $i < $NO_OF_ROOMS; $i++) {

			$room_adult_count = $search_params['adult_config'][$i];
			$room_child_count = $search_params['child_config'][$i];

			foreach ($booking_params['name_title'] as $bk => $bv) {
				$pax_type = trim($booking_params['passenger_type'][$bk]);

				$assigned_pax_type_count = $this->get_assigned_pax_type_count(@$room_wise_passenger_info[$i]['passenger_type'], $pax_type);

				if (intval($pax_type) == 1 && intval($assigned_pax_type_count) < intval($room_adult_count)) { //Adult
					$room_wise_passenger_info[$i]['name_title'][] = $booking_params['name_title'][$bk];
					$room_wise_passenger_info[$i]['first_name'][] = $booking_params['first_name'][$bk];
					$room_wise_passenger_info[$i]['PAN'][] = $booking_params['PAN'][$bk];
					$room_wise_passenger_info[$i]['middle_name'][] = $booking_params['middle_name'][$bk];
					$room_wise_passenger_info[$i]['last_name'][] = $booking_params['last_name'][$bk];
					$room_wise_passenger_info[$i]['passenger_contact'][] = $booking_params['passenger_contact'];
					$room_wise_passenger_info[$i]['billing_email'][] = $booking_params['billing_email'];
					$room_wise_passenger_info[$i]['passenger_type'][] = $booking_params['passenger_type'][$bk];
					$room_wise_passenger_info[$i]['date_of_birth'][] = $booking_params['date_of_birth'][$bk];

					//Remove the pax data from array
					unset($booking_params['name_title'][$bk]);
				} else if (intval($pax_type) == 2 && intval($assigned_pax_type_count) < intval($room_child_count)) { //Child
					$room_wise_passenger_info[$i]['name_title'][] = $booking_params['name_title'][$bk];
					$room_wise_passenger_info[$i]['first_name'][] = $booking_params['first_name'][$bk];
					$room_wise_passenger_info[$i]['middle_name'][] = $booking_params['middle_name'][$bk];
					$room_wise_passenger_info[$i]['last_name'][] = $booking_params['last_name'][$bk];
					$room_wise_passenger_info[$i]['PAN'][] = $booking_params['PAN'][$bk];
					$room_wise_passenger_info[$i]['passenger_contact'][] = $booking_params['passenger_contact'];
					$room_wise_passenger_info[$i]['billing_email'][] = $booking_params['billing_email'];
					$room_wise_passenger_info[$i]['passenger_type'][] = $booking_params['passenger_type'][$bk];
					$room_wise_passenger_info[$i]['date_of_birth'][] = $booking_params['date_of_birth'][$bk];

					//Remove the pax data from array
					unset($booking_params['name_title'][$bk]);
				}
			}
		}

		/*************Re-Assign the Pax Room Wise Ends******************************/


		/* Counting No of adults and childs per room wise */
		for ($i = 0; $i < $NO_OF_ROOMS; $i++) {
			$booking_params['token']['token'][$i]['no_of_pax'] = $search_data['adult'][$i] + $search_data['child'][$i];
		}

		/* Forming Request */
		$response['status'] = true;
		$response['data'] = array();
		$request['ResultToken'] = urldecode($booking_params['token']['ResultIndex']);
		$request['BlockRoomId'] = $booking_params['token']['BlockRoomId'];
		$request['AppReference'] = trim($booking_id);
		$k = 0;
		for ($i = 0; $i < $NO_OF_ROOMS; $i++) {
			for ($j = 0; $j < $booking_params['token']['token'][$i]['no_of_pax']; $j++) {

				$pax_list = array(); // Reset Pax List Array
				$pax_title = get_enum_list('title', $room_wise_passenger_info[$i]['name_title'][$j]);
				$pax_list['Title'] = $pax_title;
				$pax_list['FirstName'] = $room_wise_passenger_info[$i]['first_name'][$j];
				$pax_list['MiddleName'] = $room_wise_passenger_info[$i]['middle_name'][$j];
				$pax_list['LastName'] = $room_wise_passenger_info[$i]['last_name'][$j];
				$pax_list['PAN'] = $room_wise_passenger_info[$i]['PAN'][$j];
				$pax_list['Phoneno'] = $room_wise_passenger_info[$i]['passenger_contact'][$j];
				$pax_list['Email'] = $room_wise_passenger_info[$i]['billing_email'][$j];
				$pax_list['PaxType'] = $room_wise_passenger_info[$i]['passenger_type'][$j];

				$pax_lead = false;

				if ($j == 0) {
					$pax_lead = true;
				}
				$pax_list['LeadPassenger'] = $pax_lead;
				/* Age Calculation of Pax */
				$from = new DateTime($room_wise_passenger_info[$i]['date_of_birth'][$j]);
				$to = new DateTime('today');
				$pax_age = $from->diff($to)->y;
				$pax_list['Age'] = $pax_age;
				$request['RoomDetails'][$i]['PassengerDetails'][$j] = $pax_list;
				$k++;
			}
		}
		//debug($request);die;
		$response['data']['request'] = json_encode($request);

		return $response;
	}

	function get_assigned_pax_type_count($pax_type_arr, $pax_type)
	{
		$pax_type_count = 0;
		if (valid_array($pax_type_arr) == true) {
			foreach ($pax_type_arr as $k => $v) {
				if ($pax_type == $v) {
					$pax_type_count++;
				}
			}
		}
		return $pax_type_count;
	}

	private function formate_hotel_room_details($booking_params)
	{
		$search_id = $booking_params['token']['search_id'];
		$safe_search_data = $GLOBALS['CI']->hotel_model->get_search_data($search_id);
		$search_data = json_decode($safe_search_data['search_data'], true);
		$NO_OF_ROOMS = $search_data['rooms'];
		$k = 0;


		$HotelRoomsDetails = array();
		/* Counting No of adults and childs per room wise */
		for ($i = 0; $i < $NO_OF_ROOMS; $i++) {
			$booking_params['token']['token'][$i]['no_of_pax'] = $search_data['adult'][$i] + $search_data['child'][$i];
		}
		for ($i = 0; $i < $NO_OF_ROOMS; $i++) {
			$room_detail = array();
			$room_detail['RoomIndex'] = $booking_params['token']['token'][$i]['RoomIndex'];
			$room_detail['RatePlanCode'] = $booking_params['token']['token'][$i]['RatePlanCode'];
			$room_detail['RatePlanName'] = $booking_params['token']['token'][$i]['RatePlanName'];
			$room_detail['RoomTypeCode'] = $booking_params['token']['token'][$i]['RoomTypeCode'];
			$room_detail['RoomTypeName'] = $booking_params['token']['token'][$i]['RoomTypeName'];
			$room_detail['SmokingPreference'] = 0;

			$room_detail['Price']['CurrencyCode'] = $booking_params['token']['token'][$i]['CurrencyCode'];
			$room_detail['Price']['RoomPrice'] = $booking_params['token']['token'][$i]['RoomPrice'];
			$room_detail['Price']['Tax'] = $booking_params['token']['token'][$i]['Tax'];
			$room_detail['Price']['ExtraGuestCharge'] = $booking_params['token']['token'][$i]['ExtraGuestCharge'];
			$room_detail['Price']['ChildCharge'] = $booking_params['token']['token'][$i]['ChildCharge'];
			$room_detail['Price']['OtherCharges'] = $booking_params['token']['token'][$i]['OtherCharges'];
			$room_detail['Price']['Discount'] = $booking_params['token']['token'][$i]['Discount'];
			$room_detail['Price']['PublishedPrice'] = $booking_params['token']['token'][$i]['PublishedPrice'];
			$room_detail['Price']['PublishedPriceRoundedOff'] = $booking_params['token']['token'][$i]['PublishedPriceRoundedOff'];
			$room_detail['Price']['OfferedPrice'] = $booking_params['token']['token'][$i]['OfferedPrice'];
			$room_detail['Price']['OfferedPriceRoundedOff'] = $booking_params['token']['token'][$i]['OfferedPriceRoundedOff'];
			$room_detail['Price']['SmokingPreference'] = $booking_params['token']['token'][$i]['SmokingPreference'];
			$room_detail['Price']['ServiceTax'] = $booking_params['token']['token'][$i]['ServiceTax'];
			$room_detail['Price']['Tax'] = $booking_params['token']['token'][$i]['Tax'];
			$room_detail['Price']['ExtraGuestCharge'] = $booking_params['token']['token'][$i]['ExtraGuestCharge'];
			$room_detail['Price']['ChildCharge'] = $booking_params['token']['token'][$i]['ChildCharge'];
			$room_detail['Price']['OtherCharges'] = $booking_params['token']['token'][$i]['OtherCharges'];
			$room_detail['Price']['Discount'] = $booking_params['token']['token'][$i]['Discount'];
			$room_detail['Price']['AgentCommission'] = $booking_params['token']['token'][$i]['AgentCommission'];
			$room_detail['Price']['AgentMarkUp'] = $booking_params['token']['token'][$i]['AgentMarkUp'];
			$room_detail['Price']['TDS'] = $booking_params['token']['token'][$i]['TDS'];
			$HotelRoomsDetails[$i] = $room_detail;

			for ($j = 0; $j < $booking_params['token']['token'][$i]['no_of_pax']; $j++) {
				$pax_list = array(); // Reset Pax List Array
				$pax_title = get_enum_list('title', $booking_params['name_title'][$k]);
				$pax_list['Title'] = $pax_title;
				$pax_list['FirstName'] = $booking_params['first_name'][$k];
				$pax_list['MiddleName'] = $booking_params['middle_name'][$k];
				$pax_list['LastName'] = $booking_params['last_name'][$k];
				$pax_list['PAN'] = $booking_params['PAN'][$k];
				$pax_list['Phoneno'] = $booking_params['passenger_contact'];
				$pax_list['Email'] = $booking_params['billing_email'];
				$pax_list['PaxType'] = $booking_params['passenger_type'][$k];

				$pax_lead = false;
				// temp
				if ($j == 0) {
					$pax_lead = true;
				}
				$pax_list['LeadPassenger'] = $pax_lead;
				/* Age Calculation of Pax */
				$from = new DateTime($booking_params['date_of_birth'][$k]);
				$to = new DateTime('today');
				$pax_age = $from->diff($to)->y;
				$pax_list['Age'] = $pax_age;
				$HotelRoomsDetails[$i]['HotelPassenger'][$j] = $pax_list;
				$k++;
			}
		}
		return $HotelRoomsDetails;
	}

	private function convert_roombook_data_to_application_currency($room_book_data)
	{
		$application_default_currency = admin_base_currency();
		$currency_obj = new Currency(
			array(
				'module_type' => 'hotel',
				'from' => get_api_data_currency(),
				'to' => admin_base_currency()
			)
		);
		$master_room_book_data = array();
		$HotelRoomsDetails = array();
		foreach ($room_book_data['HotelRoomsDetails'] as $hrk => $hrv) {
			$HotelRoomsDetails[$hrk] = $hrv;
			$HotelRoomsDetails[$hrk]['Price'] = $this->preferred_currency_fare_object($hrv['Price'], $currency_obj, $application_default_currency);
		}
		$master_room_book_data = $room_book_data;
		$master_room_book_data['HotelRoomsDetails'] = $HotelRoomsDetails;
		return $master_room_book_data;
	}

	function get_hotel_list($search_id = '')
	{

		if (strstr(base_url(), 'supervision', true)) {
			$base_url = strstr(base_url(), 'supervision', true);
		} elseif (strstr(base_url(), 'agent', true)) {
			$base_url = strstr(base_url(), 'agent', true);
		} elseif (strstr(base_url(), 'subadmin', true)) {
			$base_url = strstr(base_url(), 'subadmin', true);
		} else {
			$base_url = base_url();
		}
		$this->CI->load->driver('cache');
		$response['data'] = array();
		$response['status'] = true;

		$search_data = $this->search_data($search_id);


		$cache_search = $this->CI->config->item('cache_hotel_search');
		$search_hash = $this->search_hash;
		$cache_contents = '';


		if ($search_data['status'] == true) {
			$search_data = $search_data['data'];
			if ($cache_search === false || ($cache_search === true && empty($cache_contents) == true)) {

				$no_of_nights = $search_data['no_of_nights'];
				if (empty($search_data['hotel_destination'] == true)) {
					$query = 'Select * from all_api_city_master as cm where  cm.city_name ="' . $search_data['city_name'] . '" AND cm.country_name="' . $search_data['country_name'] . '" ';

					$city_list = $GLOBALS['CI']->db->query($query)->result_array()[0];
					$search_data['hotel_destination'] = $city_list['origin'];
				}


				$hotel_results = $this->get_hotels_from_search($search_data);
				
				//debug($hotel_results);exit("case1 ");

				$final_hotel_results = array();

				foreach ($hotel_results as $stay_origin => $hotel_result) {
					$final_hotel_price = 0;
					$sup_final_hotel_price = 0;
					$hotel_data = array();

					//calculaing total price for all rooms
					foreach ($hotel_result as $key => $value) {
						$final_hotel_price += $value['total_price'];
						$sup_final_hotel_price += $value['supplier_total_price'];
						$hotel_data = $value;
					}

					$final_hotel_price = $final_hotel_price / $no_of_nights;
					$sup_final_hotel_price = $sup_final_hotel_price / $no_of_nights;

					$formatted_hotel_data = array();
					$formatted_hotel_data['HotelCode'] = $hotel_data['origin'];
					$formatted_hotel_data['ResultToken'] = serialized_data(array_merge($search_data, $formatted_hotel_data));
					$formatted_hotel_data['OrginalHotelCode'] = $hotel_data['origin'];
					$formatted_hotel_data['HotelName'] = $hotel_data['name'];
					$formatted_hotel_data['HotelDescription'] = $hotel_data['description'];
					$formatted_hotel_data['StarRating'] = $hotel_data['ratings'];
					$formatted_hotel_data['HotelPicture'] = $base_url . $GLOBALS['CI']->template->domain_view_ecoimage() . $hotel_data['image'];
					$formatted_hotel_data['HotelAddress'] = empty($hotel_data['display_address']) ? 'Address not Available' : $hotel_data['display_address'];
					$formatted_hotel_data['HotelContactNo'] = array(
						array(
							'type' => 'Voice',
							'number' => '+' . $hotel_data['country_code'] . ' ' . $hotel_data['phone'],
						)
					);
					$formatted_hotel_data['video_link'] = $hotel_data['video_link'];
					$formatted_hotel_data['Latitude'] = $hotel_data['latitude'];
					$formatted_hotel_data['Longitude'] = $hotel_data['longitude'];
					$formatted_hotel_data['HotelCategory'] = 'Hotel';
					$formatted_hotel_data['trip_adv_url'] = '';
					$formatted_hotel_data['trip_rating'] = '0.0';
					$hotel_amenities_codes = json_decode($hotel_data['amenities'], true);
					$formatted_hotel_data['HotelAmenities'] = $this->get_hotel_amenities($hotel_amenities_codes);
					$formatted_hotel_data['HotelLocation'] = $hotel_data['city_name'];
					$formatted_hotel_data['HotelPromotion'] = '';
					$formatted_hotel_data['HotelPromotionContent'] = '';
					if ($hotel_data['currency'] > 0) {

						$currency = $GLOBALS['CI']->custom_db->single_table_records("currency_converter", "country", array("id" => $hotel_data['currency']))['data'][0]['country'];
					}
					$formatted_hotel_data['Price'] = array(
						'Tax' => 0,
						'ExtraGuestCharge' => 0,
						'ChildCharge' => 0,
						'OtherCharges' => 0,
						'Discount' => 0,
						'PublishedPrice' => $final_hotel_price,
						'SupplierOriginalPrice' => $sup_final_hotel_price,
						'RoomPrice' => $final_hotel_price,
						'PublishedPriceRoundedOff' => round($final_hotel_price, 2),
						'OfferedPrice' => $final_hotel_price,
						'OfferedPriceRoundedOff' => round($final_hotel_price, 2),
						'AgentCommission' => 0,
						'AgentMarkUp' => 0,
						'ServiceTax' => 0,
						'TDS' => 0,
						'ServiceCharge' => 0,
						'TotalGSTAmount' => 0,
						'RoomPriceWoGST' => $final_hotel_price,
						'GSTPrice' => 0,
						'CurrencyCode' => 'INR',
						'SupplierCurrencyCode' => (!empty($currency)) ? $currency : 'INR'
					);
					$final_hotel_results[] = $formatted_hotel_data;
				}

				$search_response = array(
					'Status' => 1,
					'Message' => '',
					'Search' => array(
						'HotelSearchResult' => array(
							'HotelResults' => $final_hotel_results
						)
					)
				);

				if ($this->valid_search_result($search_response)) {
					$response['data'] = $search_response['Search'];
					if ($cache_search) {
						$cache_exp = $this->CI->config->item('cache_hotel_search_ttl');
						$this->CI->cache->file->save($search_hash, $response['data'], $cache_exp);
					}

					$this->cache_result_hotel_count($search_response);
				} else {
					$response['status'] = false;
				}
			} else {
				$response['data'] = $cache_contents;
			}
		} else {
			$response['status'] = false;
		}
		//debug($response);exit;

		return $response;
	}

	function hotel_room_list($hotel_id, $room_id, $s_id, $b_s)
	{
		$response['data'] = array();
		$response['status'] = true;

		$search_data = $this->search_data($s_id);


		$hotels_and_rooms = array();

		if ($search_data['data']['search_type'] == 'myop') {
			$ignore_availability = true;
			$get_first_available_price = true;
		}
		/************** DELETING PREVIOUS DAYS AVAILABILIY **************** */
		$todays_date = DateTime::createFromFormat('d-m-Y', date('d-m-Y'));
		$todays_date = $todays_date->format('Y-m-d');
		$this->CI->db->where('date <', $todays_date);
		$this->CI->db->delete('eco_stays_room_availability');
		/************ DELETING PREVIOUS DAYS AVAILABILIY ENDS ************* */

		/************** Unblocking rooms holded for more than 15 minutes *************** */
		$query = "SELECT * from eco_stays_blocked_rooms WHERE (created_on <= NOW() - INTERVAL 15 MINUTE)";
		$holded_details = $this->CI->db->query($query)->result_array();
		$hold_origins = array();

		foreach ($holded_details as $key => $holded_rooms) {

			$hold_origins[] = $holded_rooms["origin"];

			$room_origins = json_decode($holded_rooms["room_origins"], true);
			$dates = json_decode($holded_rooms["dates"], true);
			//unblocking rooms
			$this->CI->db->set('holded', 'holded - 1', FALSE);
			$this->CI->db->where_in('room_origin', $room_origins);
			$this->CI->db->where_in('date', $dates);
			$this->CI->db->update('eco_stays_room_availability');
		}

		if (count($hold_origins) > 0) {
			$this->CI->db->where_in('origin', $hold_origins);
			$this->CI->db->delete('eco_stays_blocked_rooms');
		}
		/************ Unblocking rooms holded for more than 15 minutes ENDS ************* */

		$room_count = $search_data['data']['room_count'];




		$hotels_and_rooms = array();

		for ($i = 0; $i < $room_count; $i++) {
			$max_adult = $search_data['data']['adult_config'][$i];
			$max_child = $search_data['data']['child_config'][$i];

			$query_selects = "SELECT stay.*,location.city_name, room.origin AS room_origin , room.quantity, room_type.name AS room_type, ";

			$query_froms = " FROM eco_stays AS stay ";

			$query_left_joins = "   LEFT JOIN all_api_city_master AS location ON location.origin = stay.city
									LEFT JOIN eco_stays_rooms AS room ON room.stays_origin = stay.origin 
									LEFT JOIN eco_stays_room_types AS room_type ON room_type.origin = room.type ";

			$query_conditions = " WHERE stay.status = 1
									AND room.max_adults >= $max_adult
									AND room.max_childs >= $max_child
									AND room.status = 1
									AND EXISTS (SELECT supplier.user_id from user AS supplier where supplier.user_id = stay.host)";

			$query_group_by = "";



			$searched_stay_origin = $hotel_id;
			$query_conditions .= "AND stay.origin = $searched_stay_origin ";
			$searched_room_origin = $room_id;
			$query_conditions .= "AND room.origin = $searched_room_origin ";



			if ($ignore_availability == false) {

				$from_date = DateTime::createFromFormat('d-m-Y', $search_data['data']['raw_from_date']);
				$from_date = $from_date->format('Y-m-d');
				$to_date = DateTime::createFromFormat('d-m-Y', $search_data['data']['raw_to_date']);
				$to_date = $to_date->sub(new DateInterval('P1D'));
				$to_date = $to_date->format('Y-m-d');

				$query_selects .= " availability.date, prices.prices,";

				$query_left_joins .= " LEFT JOIN eco_stays_room_availability AS availability ON availability.room_origin = room.origin
										LEFT JOIN eco_stays_room_prices AS prices ON prices.origin = availability.price_origin ";

				$query_conditions .= " AND availability.date BETWEEN '$from_date' AND '$to_date'
										AND prices.status = 1 ";

				$query_conditions .= " AND NOT EXISTS (
						SELECT 1
						FROM (
							SELECT DISTINCT DATE_ADD('$from_date', INTERVAL seq DAY) AS date
							FROM (
								SELECT (t0.i + t1.i + t2.i + t3.i + t4.i) as seq
								FROM
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t0,
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t1,
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t2,
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t3,
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t4
							) numbers
							WHERE DATE_ADD('$from_date', INTERVAL seq DAY) BETWEEN '$from_date' AND '$to_date'
						) AS dates
						WHERE NOT EXISTS (
							SELECT 1
							FROM eco_stays_room_availability AS availability3
							WHERE room.origin = availability3.room_origin
							AND dates.date = availability3.date
							AND ((availability3.booked + availability3.holded) < room.quantity)
						)
					)
					";
			} elseif ($get_first_available_price) {
				$query_selects .= " prices.prices,";
				$query_left_joins .= " LEFT JOIN eco_stays_room_prices AS prices ON prices.room_origin = room.origin ";
				$query_conditions .= " AND prices.status = 1 ";
				$query_group_by .= " GROUP BY room_origin ";
			} else {
				$query_selects .= " '{}' AS prices,";
			}
			$query = rtrim($query_selects, ',') . ' ' . $query_froms . ' ' . $query_left_joins . ' ' . $query_conditions . ' ' . $query_group_by;

			$avl_stays = array();


			$avl_stays = $this->CI->db->query($query)->result_array();



			$formatted_avl_stays = array();
			//combinig room prices for all dates

			foreach ($avl_stays as $row => $stay) {
				$currrency = '';
				if ($stay['currency'] > 0) {
					$currency = $GLOBALS['CI']->custom_db->single_table_records("currency_converter", "country", array("id" => $stay['currency']))['data'][0]['country'];
				} else {
					$currency = 'INR';
				}
				$currency_obj = new Currency(array('module_type' => 'hotel', 'from' => $currency, 'to' => 'INR'));

				if (isset($formatted_avl_stays[$stay['origin']])) {
					$stay_data = $formatted_avl_stays[$stay['origin']];
					$stay_rooms = array();
					if (isset($stay_data['rooms'])) {
						$stay_rooms = $stay_data['rooms'];
					}

					if (isset($stay_rooms[$stay['room_origin']])) {
						$room_data = $stay_rooms[$stay['room_origin']];
						$room_price_data = json_decode($stay['prices'], TRUE);
						$room_calculated_price = $this->calculate_room_price($room_price_data, $max_adult, $max_child, $currency_obj);
						$room_data['room_data'] = $this->get_room_details($stay['room_origin']);
						$room_data['total_price'] += $room_calculated_price['admin_price'];
						$room_data['quantity'] = $stay['quantity'];
						$room_data['room_origin'] = $stay['room_origin'];
						$stay_rooms[$stay['room_origin']] = $room_data;
					} else {
						$room_data = array();
						$room_price_data = json_decode($stay['prices'], TRUE);
						$room_calculated_price = $this->calculate_room_price($room_price_data, $max_adult, $max_child, $currency_obj);
						$room_data['room_data'] = $this->get_room_details($stay['room_origin']);

						$room_data['total_price'] += $room_calculated_price['admin_price'];
						$room_data['quantity'] = $stay['quantity'];
						$room_data['room_origin'] = $stay['room_origin'];
						$stay_rooms[$stay['room_origin']] = $room_data;
					}
					$stay_data['rooms'] = $stay_rooms;
					$formatted_avl_stays[$stay['origin']] = $stay_data;
				} else {
					$stay_data = $stay;
					$stay_rooms = array();
					$room_data = array();
					$room_price_data = json_decode($stay['prices'], TRUE);
					$room_calculated_price = $this->calculate_room_price($room_price_data, $max_adult, $max_child, $currency_obj);
					$room_data['total_price'] += $room_calculated_price['admin_price'];
					$room_data['quantity'] = $stay['quantity'];
					$room_data['room_data'] = $this->get_room_details($stay['room_origin']);

					$room_data['room_origin'] = $stay['room_origin'];
					$stay_rooms[$stay['room_origin']] = $room_data;
					$stay_data['rooms'] = $stay_rooms;
					unset($stay_data['room_origin']);
					unset($stay_data['quantity']);
					unset($stay_data['date']);
					unset($stay_data['prices']);
					$formatted_avl_stays[$stay['origin']] = $stay_data;
				}
			}

			$hotels_and_rooms[$i] = $formatted_avl_stays;
		}

		//checking if stay has all rooms

		for ($i = 0; $i < $room_count; $i++) {
			foreach ($hotels_and_rooms[$i] as $stay_origin => $stay_data) {
				for ($j = 0; $j < $room_count; $j++) {
					if ($j != $i) {
						if (!array_key_exists($stay_origin, $hotels_and_rooms[$j])) {
							unset($hotels_and_rooms[$i][$stay_origin]);
						}
					}
				}
			}
		}


		foreach ($hotels_and_rooms as $stay_origin => $hotel_result) {
			$final_hotel_price = 0;
			$hotel_data = array();

			//calculaing total price for all rooms
			// debug($$hotel_result);
			// die;
			foreach ($hotel_result as $key => $value) {

				$hotel_data = $value;
				foreach ($value['rooms'] as $key => $value) {
					$final_hotel_price += $value['total_price'];
				}
			}


			$final_hotel_price = $final_hotel_price;


			if (strstr(base_url(), 'supervision', true)) {
				$base_url = strstr(base_url(), 'supervision', true);
			} elseif (strstr(base_url(), 'agent', true)) {
				$base_url = strstr(base_url(), 'agent', true);
			} elseif (strstr(base_url(), 'subadmin', true)) {
				$base_url = strstr(base_url(), 'subadmin', true);
			} else {
				$base_url = base_url();
			}

			$formatted_hotel_data = array();
			$formatted_hotel_data['HotelCode'] = $hotel_id;
			$formatted_hotel_data['ResultToken'] = serialized_data(array_merge($search_data, $formatted_hotel_data));
			$formatted_hotel_data['OrginalHotelCode'] = $hotel_id;
			$formatted_hotel_data['HotelName'] = $hotel_data['name'];
			$formatted_hotel_data['HotelDescription'] = $hotel_data['description'];
			$formatted_hotel_data['StarRating'] = $hotel_data['ratings'];
			$formatted_hotel_data['HotelPicture'] = $base_url . $GLOBALS['CI']->template->domain_view_ecoimage() . $hotel_data['image'];
			$formatted_hotel_data['HotelAddress'] = empty($hotel_data['display_address']) ? 'Address not Available' : $hotel_data['display_address'];
			$formatted_hotel_data['HotelContactNo'] = array(
				array(
					'type' => 'Voice',
					'number' => '+' . $hotel_data['country_code'] . ' ' . $hotel_data['phone'],
				)
			);
			$formatted_hotel_data['video_link'] = $hotel_data['video_link'];
			$formatted_hotel_data['Latitude'] = $hotel_data['latitude'];
			$formatted_hotel_data['Longitude'] = $hotel_data['longitude'];
			$formatted_hotel_data['HotelCategory'] = 'Hotel';
			$formatted_hotel_data['trip_adv_url'] = '';
			$formatted_hotel_data['trip_rating'] = '0.0';
			$hotel_amenities_codes = json_decode($hotel_data['amenities'], true);
			$formatted_hotel_data['HotelAmenities'] = $this->get_hotel_amenities($hotel_amenities_codes);
			$formatted_hotel_data['HotelLocation'] = $hotel_data['city_name'];
			$formatted_hotel_data['HotelPromotion'] = '';
			$formatted_hotel_data['HotelPromotionContent'] = '';
			$formatted_hotel_data['room'] = $hotel_data['rooms'];
			$formatted_hotel_data['Price'] = array(
				'Tax' => 0,
				'ExtraGuestCharge' => 0,
				'ChildCharge' => 0,
				'OtherCharges' => 0,
				'Discount' => 0,
				'PublishedPrice' => $final_hotel_price,
				'RoomPrice' => $final_hotel_price,
				'PublishedPriceRoundedOff' => round($final_hotel_price, 2),
				'OfferedPrice' => $final_hotel_price,
				'OfferedPriceRoundedOff' => round($final_hotel_price, 2),
				'AgentCommission' => 0,
				'AgentMarkUp' => 0,
				'ServiceTax' => 0,
				'TDS' => 0,
				'ServiceCharge' => 0,
				'TotalGSTAmount' => 0,
				'RoomPriceWoGST' => $final_hotel_price,
				'GSTPrice' => 0,
				'CurrencyCode' => 'INR'
			);
			$final_hotel_results[] = $formatted_hotel_data;
		}

		return $final_hotel_results;
	}

	private function get_hotels_from_search($search_data)
	{
		$hotel_results = array();

		$avl_hotels_and_rooms = $this->get_available_hotels_and_rooms($search_data);

		//debug($avl_hotels_and_rooms);die("get_hotels_from_search");

		foreach ($avl_hotels_and_rooms as $key => $avl_hotels_and_room) {
			$formatted_avl_stays = $avl_hotels_and_room;
			//getting the stay room with minimum price
			foreach ($formatted_avl_stays as $stay_origin => $formatted_avl_stay) {
				$rooms = $formatted_avl_stay['rooms'];
				$room_origin_min_price = array_keys($rooms)[0];
				$min_price = $rooms[$room_origin_min_price]['total_price'];
				$supplier_min_price = $rooms[$room_origin_min_price]['supplier_total_price'];
				foreach ($rooms as $room_origin => $room) {
					if ($room['total_price'] < $min_price) {
						$min_price = $room['total_price'];
						$room_origin_min_price = $room_origin;
						$supplier_min_price = $room['supplier_total_price'];
					}
				}
				unset($formatted_avl_stays[$stay_origin]['rooms']);
				$formatted_avl_stays[$stay_origin]['room_origin'] = $room_origin_min_price;
				$formatted_avl_stays[$stay_origin]['total_price'] = $min_price;
				$formatted_avl_stays[$stay_origin]['supplier_total_price'] = $supplier_min_price;

				$hotel_results[$stay_origin][] = $formatted_avl_stays[$stay_origin];
			}
		}
		return $hotel_results;
	}

	public function get_available_hotels_and_rooms($search_data, $ignore_availability = false, $night = 0, $flag = '', $get_first_available_price = false)
	{

		if ($search_data['search_type'] == 'myop') {
			$ignore_availability = true;
			$get_first_available_price = true;
		}
		/************** DELETING PREVIOUS DAYS AVAILABILIY **************** */
		$todays_date = DateTime::createFromFormat('d-m-Y', date('d-m-Y'));
		$todays_date = $todays_date->format('Y-m-d');
		$this->CI->db->where('date <', $todays_date);
		$this->CI->db->delete('eco_stays_room_availability');
		/************ DELETING PREVIOUS DAYS AVAILABILIY ENDS ************* */

		/************** Unblocking rooms holded for more than 15 minutes *************** */
		$query = "SELECT * from eco_stays_blocked_rooms WHERE (created_on <= NOW() - INTERVAL 15 MINUTE)";
		$holded_details = $this->CI->db->query($query)->result_array();
		$hold_origins = array();

		foreach ($holded_details as $key => $holded_rooms) {

			$hold_origins[] = $holded_rooms["origin"];

			$room_origins = json_decode($holded_rooms["room_origins"], true);
			$dates = json_decode($holded_rooms["dates"], true);
			//unblocking rooms
			$this->CI->db->set('holded', 'holded - 1', FALSE);
			$this->CI->db->where_in('room_origin', $room_origins);
			$this->CI->db->where_in('date', $dates);
			$this->CI->db->update('eco_stays_room_availability');
		}

		if (count($hold_origins) > 0) {
			$this->CI->db->where_in('origin', $hold_origins);
			$this->CI->db->delete('eco_stays_blocked_rooms');
		}
		/************ Unblocking rooms holded for more than 15 minutes ENDS ************* */

		$room_count = $search_data['room_count'];




		$hotels_and_rooms = array();

		for ($i = 0; $i < $room_count; $i++) {
			$max_adult = $search_data['adult_config'][$i];
			$max_child = $search_data['child_config'][$i];

			// $query = "SELECT stay.*,location.city_name, room.origin AS room_origin , room.quantity, availability.date, prices.prices
			// 		FROM eco_stays AS stay
			// 		LEFT JOIN all_api_city_master AS location ON location.origin = stay.city
			// 		LEFT JOIN eco_stays_rooms AS room ON room.stays_origin = stay.origin
			// 		LEFT JOIN eco_stays_room_availability AS availability ON availability.room_origin = room.origin
			// 		LEFT JOIN eco_stays_room_prices AS prices ON prices.origin = availability.price_origin
			// 		WHERE stay.status = 1
			// 		AND room.max_adults >= $max_adult
			// 		AND room.max_childs >= $max_child
			// 		AND room.status = 1
			// 		AND prices.status = 1 ";
			$query_selects = "SELECT stay.*,location.Destination, room.origin AS room_origin , room.quantity, room_type.name AS room_type, ";

			$query_froms = " FROM eco_stays AS stay ";

			$query_left_joins = "   LEFT JOIN tbo_city_list AS location ON location.origin = stay.city
									LEFT JOIN eco_stays_rooms AS room ON room.stays_origin = stay.origin 
									LEFT JOIN eco_stays_room_types AS room_type ON room_type.origin = room.type ";

			$query_conditions = " WHERE stay.status = 1
									AND room.max_adults >= $max_adult
									AND room.max_childs >= $max_child
									AND room.status = 1
									AND EXISTS (SELECT supplier.user_id from user AS supplier where supplier.user_id = stay.host)";

			$query_group_by = "";

			if (isset($search_data['hotel_code']) && empty($search_data['hotel_code']) == false) {
				$searched_stay_origin = $search_data['hotel_code'];
				$query_conditions .= " AND stay.origin = $searched_stay_origin ";
			}


			if (isset($search_data['HotelCode']) && empty($search_data['HotelCode']) == false) {
				$searched_stay_origin = $search_data['HotelCode'];
				$query_conditions .= "AND stay.origin = $searched_stay_origin ";
			}


			if (isset($search_data['hotel_destination']) && empty($search_data['hotel_destination']) == false) {
				$hotel_destination = $search_data['hotel_destination'];
				$query_conditions .= "AND stay.city = $hotel_destination ";
			}


			if (isset($search_data['hotel_type']) && empty($search_data['hotel_type']) == false) {
				$searched_hotel_type = $search_data['hotel_type'];
				$query_conditions .= "AND stay.type = $searched_hotel_type ";
			}

			if (isset($search_data['theme_cat']) && empty($search_data['theme_cat']) == false) {
				$searched_hotel_theme = $search_data['theme_cat'];
				$query_conditions .= "AND EXISTS (SELECT * from eco_stays_theme_map as theme_map WHERE stay.origin = theme_map.stays_origin AND theme_map.theme_origin = $searched_hotel_theme) ";
			}

			if ($ignore_availability == false) {
				// $query .= "AND NOT EXISTS (
				// 		SELECT 1
				// 		FROM (
				// 			SELECT DISTINCT DATE_ADD('$from_date', INTERVAL seq DAY) AS date
				// 			FROM (
				// 				SELECT (t0.i + t1.i + t2.i + t3.i + t4.i) as seq
				// 				FROM
				// 					(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t0,
				// 					(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t1,
				// 					(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t2,
				// 					(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t3,
				// 					(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t4
				// 			) numbers
				// 			WHERE DATE_ADD('$from_date', INTERVAL seq DAY) BETWEEN '$from_date' AND '$to_date'
				// 		) AS dates
				// 		WHERE NOT EXISTS (
				// 			SELECT 1
				// 			FROM eco_stays_room_availability AS availability3
				// 			WHERE room.origin = availability3.room_origin
				// 			AND dates.date = availability3.date
				// 			AND ((availability3.booked + availability3.holded) < room.quantity)
				// 		)
				// 	)
				// 	";

				$raw_from_date = date("d-m-Y", strtotime($search_data['raw_from_date']));
				$raw_to_date = date("d-m-Y", strtotime($search_data['raw_to_date']));


				$from_date = DateTime::createFromFormat('d-m-Y', $raw_from_date);


				$from_date = $from_date->format('Y-m-d');

				$to_date = DateTime::createFromFormat('d-m-Y', $raw_to_date);
				$to_date = $to_date->sub(new DateInterval('P1D'));
				$to_date = $to_date->format('Y-m-d');

				$query_selects .= " availability.date, prices.prices,";

				$query_left_joins .= " LEFT JOIN eco_stays_room_availability AS availability ON availability.room_origin = room.origin
										LEFT JOIN eco_stays_room_prices AS prices ON prices.origin = availability.price_origin ";

				$query_conditions .= " AND availability.date BETWEEN '$from_date' AND '$to_date'
										AND prices.status = 1 ";

				$query_conditions .= " AND NOT EXISTS (
						SELECT 1
						FROM (
							SELECT DISTINCT DATE_ADD('$from_date', INTERVAL seq DAY) AS date
							FROM (
								SELECT (t0.i + t1.i + t2.i + t3.i + t4.i) as seq
								FROM
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t0,
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t1,
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t2,
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t3,
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t4
							) numbers
							WHERE DATE_ADD('$from_date', INTERVAL seq DAY) BETWEEN '$from_date' AND '$to_date'
						) AS dates
						WHERE NOT EXISTS (
							SELECT 1
							FROM eco_stays_room_availability AS availability3
							WHERE room.origin = availability3.room_origin
							AND dates.date = availability3.date
							AND ((availability3.booked + availability3.holded) < room.quantity)
						)
					)
					";
			} elseif ($get_first_available_price) {
				$query_selects .= " prices.prices,";
				$query_left_joins .= " LEFT JOIN eco_stays_room_prices AS prices ON prices.room_origin = room.origin ";
				$query_conditions .= " AND prices.status = 1 ";
				$query_group_by .= " GROUP BY room_origin ";
			} else {
				$query_selects .= " '{}' AS prices,";
			}
			$query = rtrim($query_selects, ',') . ' ' . $query_froms . ' ' . $query_left_joins . ' ' . $query_conditions . ' ' . $query_group_by;


			$avl_stays = array();

			// debug($query);die;

			$avl_stays = $this->CI->db->query($query)->result_array();



			$formatted_avl_stays = array();
			//combinig room prices for all dates

			foreach ($avl_stays as $row => $stay) {
				$currrency = '';
				if ($stay['currency'] > 0) {
					$currency = $GLOBALS['CI']->custom_db->single_table_records("currency_converter", "country", array("id" => $stay['currency']))['data'][0]['country'];
				} else {
					$currency = 'INR';
				}
				$currency_obj = new Currency(array('module_type' => 'hotel', 'from' => $currency, 'to' => get_api_data_currency()));


				if (isset($formatted_avl_stays[$stay['origin']])) {
					$stay_data = $formatted_avl_stays[$stay['origin']];
					$stay_rooms = array();
					if (isset($stay_data['rooms'])) {
						$stay_rooms = $stay_data['rooms'];
					}

					if (isset($stay_rooms[$stay['room_origin']])) {
						$room_data = $stay_rooms[$stay['room_origin']];
						$room_price_data = json_decode($stay['prices'], TRUE);

						$room_calculated_price = $this->calculate_room_price($room_price_data, $max_adult, $max_child, $currency_obj);

						$room_data['total_price'] += $room_calculated_price['admin_price'];
						$room_data['supplier_total_price'] += $room_calculated_price['supplier_price'];
						$room_data['quantity'] = $stay['quantity'];
						$room_data['room_origin'] = $stay['room_origin'];
						$stay_rooms[$stay['room_origin']] = $room_data;
					} else {
						$room_data = array();
						$room_price_data = json_decode($stay['prices'], TRUE);
						$room_calculated_price = $this->calculate_room_price($room_price_data, $max_adult, $max_child,  $currency_obj);

						$room_data['total_price'] += $room_calculated_price['admin_price'];
						$room_data['supplier_total_price'] += $room_calculated_price['supplier_price'];
						$room_data['quantity'] = $stay['quantity'];
						$room_data['room_origin'] = $stay['room_origin'];
						$stay_rooms[$stay['room_origin']] = $room_data;
					}
					$stay_data['rooms'] = $stay_rooms;
					$formatted_avl_stays[$stay['origin']] = $stay_data;
				} else {
					$stay_data = $stay;
					$stay_rooms = array();
					$room_data = array();
					$room_price_data = json_decode($stay['prices'], TRUE);
					$room_calculated_price = $this->calculate_room_price($room_price_data, $max_adult, $max_child, $currency_obj);
					$room_data['total_price'] += $room_calculated_price['admin_price'];
					$room_data['supplier_total_price'] += $room_calculated_price['supplier_price'];
					$room_data['quantity'] = $stay['quantity'];
					$room_data['room_origin'] = $stay['room_origin'];
					$stay_rooms[$stay['room_origin']] = $room_data;
					$stay_data['rooms'] = $stay_rooms;
					unset($stay_data['room_origin']);
					unset($stay_data['quantity']);
					unset($stay_data['date']);
					unset($stay_data['prices']);
					$formatted_avl_stays[$stay['origin']] = $stay_data;
				}
				if ($night == 1) {
					if ($row == 1) {
						break;
					}
				}
			}

			$hotels_and_rooms[$i] = $formatted_avl_stays;
		}

		//checking if stay has all rooms
		for ($i = 0; $i < $room_count; $i++) {
			foreach ($hotels_and_rooms[$i] as $stay_origin => $stay_data) {
				for ($j = 0; $j < $room_count; $j++) {
					if ($j != $i) {
						if (!array_key_exists($stay_origin, $hotels_and_rooms[$j])) {
							unset($hotels_and_rooms[$i][$stay_origin]);
						}
					}
				}
			}
		}


		return $hotels_and_rooms;
	}

	public function get_available_hotels($search_data, $ignore_availability = false, $night = 0, $flag = '')
	{


		/************** DELETING PREVIOUS DAYS AVAILABILIY **************** */
		$todays_date = DateTime::createFromFormat('d-m-Y', date('d-m-Y'));
		$todays_date = $todays_date->format('Y-m-d');
		$this->CI->db->where('date <', $todays_date);
		$this->CI->db->delete('eco_stays_room_availability');
		/************ DELETING PREVIOUS DAYS AVAILABILIY ENDS ************* */

		/************** Unblocking rooms holded for more than 15 minutes *************** */
		$query = "SELECT * from eco_stays_blocked_rooms WHERE (created_on <= NOW() - INTERVAL 15 MINUTE)";
		$holded_details = $this->CI->db->query($query)->result_array();
		$hold_origins = array();

		foreach ($holded_details as $key => $holded_rooms) {

			$hold_origins[] = $holded_rooms["origin"];

			$room_origins = json_decode($holded_rooms["room_origins"], true);
			$dates = json_decode($holded_rooms["dates"], true);
			//unblocking rooms
			$this->CI->db->set('holded', 'holded - 1', FALSE);
			$this->CI->db->where_in('room_origin', $room_origins);
			$this->CI->db->where_in('date', $dates);
			$this->CI->db->update('eco_stays_room_availability');
		}

		if (count($hold_origins) > 0) {
			$this->CI->db->where_in('origin', $hold_origins);
			$this->CI->db->delete('eco_stays_blocked_rooms');
		}
		/************ Unblocking rooms holded for more than 15 minutes ENDS ************* */

		$room_count = $search_data['room_count'];

		$from_date = isset($search_data['raw_from_date']) ? date('Y-m-d', strtotime($search_data['raw_from_date'])) : '';

		// $from_date = $from_date->format('Y-m-d');
		$to_date = isset($search_data['raw_from_date']) ? date('Y-m-d', strtotime($search_data['raw_to_date'])) : '';
		// $to_date = DateTime::createFromFormat('d-m-Y', $search_data['raw_to_date']);
		// $to_date = $to_date->sub(new DateInterval('P1D'));
		// $to_date = $to_date->format('Y-m-d');


		$hotels_and_rooms = array();

		for ($i = 0; $i < $room_count; $i++) {
			$max_adult = $search_data['adult_config'][$i];
			$max_child = $search_data['child_config'][$i];

			$query = "SELECT stay.*,location.city_name,room.name as room_name
					FROM eco_stays AS stay
					LEFT JOIN all_api_city_master AS location ON location.origin = stay.city
					LEFT JOIN eco_stays_rooms AS room ON room.stays_origin = stay.origin
					WHERE stay.status = 1
					AND room.max_adults >= $max_adult
					AND room.max_childs >= $max_child
					AND room.status = 1
				";
			if ($from_date != '' && $to_date != '') {
				$query .= "AND availability.date BETWEEN '$from_date' AND '$to_date'";
			}
			if (isset($search_data['hotel_code']) && empty($search_data['hotel_code']) == false) {
				$searched_stay_origin = $search_data['hotel_code'];
				$query .= "AND stay.origin = $searched_stay_origin ";
			}

			if (isset($search_data['HotelCode']) && empty($search_data['HotelCode']) == false) {
				$searched_stay_origin = $search_data['HotelCode'];
				$query .= "AND stay.origin = $searched_stay_origin ";
			}


			if (isset($search_data['hotel_destination']) && empty($search_data['hotel_destination']) == false) {
				$hotel_destination = $search_data['hotel_destination'];
				$query .= "AND stay.city = $hotel_destination ";
			}


			if (isset($search_data['hotel_type']) && empty($search_data['hotel_type']) == false) {
				$searched_hotel_type = $search_data['hotel_type'];
				$query .= "AND stay.type = $searched_hotel_type ";
			}

			if (isset($search_data['theme_cat']) && empty($search_data['theme_cat']) == false) {
				$searched_hotel_theme = $search_data['theme_cat'];
				$query .= "AND EXISTS (SELECT * from eco_stays_theme_map as theme_map WHERE stay.origin = theme_map.stays_origin AND theme_map.theme_origin = $searched_hotel_theme) ";
			}

			if ($ignore_availability == false) {
				$query .= "AND NOT EXISTS (
						SELECT 1
						FROM (
							SELECT DISTINCT DATE_ADD('$from_date', INTERVAL seq DAY) AS date
							FROM (
								SELECT (t0.i + t1.i + t2.i + t3.i + t4.i) as seq
								FROM
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t0,
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t1,
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t2,
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t3,
									(SELECT 0 as i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) t4
							) numbers
							WHERE DATE_ADD('$from_date', INTERVAL seq DAY) BETWEEN '$from_date' AND '$to_date'
						) AS dates
						WHERE NOT EXISTS (
							SELECT 1
							FROM eco_stays_room_availability AS availability3
							WHERE room.origin = availability3.room_origin
							AND dates.date = availability3.date
							AND ((availability3.booked + availability3.holded) < room.quantity)
						)
					)
					";
			}
			$avl_stays = array();

			$avl_stays = $this->CI->db->query($query)->result_array();

			$formatted_avl_stays = array();

			//combinig room prices for all dates

			foreach ($avl_stays as $row => $stay) {
				if (isset($formatted_avl_stays[$stay['origin']])) {
					$stay_data = $formatted_avl_stays[$stay['origin']];
					$stay_rooms = array();

					$formatted_avl_stays[$stay['origin']] = $stay_data;
				} else {
					$stay_data = $stay;

					$formatted_avl_stays[$stay['origin']] = $stay_data;
				}
			}
			$hotels_and_rooms[$i] = $formatted_avl_stays;
		}

		// //checking if stay has all rooms
		// for ($i = 0; $i < $room_count; $i++) {
		// 	foreach ($hotels_and_rooms[$i] as $stay_origin => $stay_data) {
		// 		for ($j = 0; $j < $room_count; $j++) {
		// 			if ($j != $i) {
		// 				if (!array_key_exists($stay_origin, $hotels_and_rooms[$j])) {
		// 					unset($hotels_and_rooms[$i][$stay_origin]);
		// 				}
		// 			}
		// 		}
		// 	}
		// }


		return $hotels_and_rooms;
	}
	public function get_hotel_results_from_search($search_data)
	{
		$no_of_nights = $search_data['no_of_nights'];
		$hotel_results = $this->get_hotels_from_search($search_data);

		$final_hotel_results = array();

		foreach ($hotel_results as $stay_origin => $hotel_result) {
			$final_hotel_price = 0;
			$hotel_data = array();

			$rooms = array();
			$room_codes = array();

			//calculaing total price for all rooms and storing room types
			foreach ($hotel_result as $key => $value) {
				$final_hotel_price += $value['total_price'];
				$hotel_data = $value;

				$rooms[] = $value['room_type'];
				$room_codes[] = $value['room_origin'];
			}

			$final_hotel_price = $final_hotel_price / $no_of_nights;

			$formatted_hotel_data = array();
			$formatted_hotel_data['HotelCode'] = $hotel_data['origin'];
			$formatted_hotel_data['ResultToken'] = serialized_data(array_merge($search_data, array('hotel_code' => $hotel_data['origin'])));
			$formatted_hotel_data['OrginalHotelCode'] = $hotel_data['origin'];
			$formatted_hotel_data['HotelName'] = $hotel_data['name'];
			$formatted_hotel_data['StarRating'] = $hotel_data['ratings'];
			$formatted_hotel_data['HotelPicture'] = base_url() . $GLOBALS['CI']->template->domain_eco_stays_images_upload_dir('stays/' . $hotel_data['image']);
			$formatted_hotel_data['HotelAddress'] = empty($hotel_data['display_address']) ? 'Address not Available' : $hotel_data['display_address'];
			$formatted_hotel_data['HotelContactNo'] = array(
				array(
					'type' => 'Voice',
					'number' => '+' . $hotel_data['country_code'] . ' ' . $hotel_data['phone'],
				)
			);
			$formatted_hotel_data['video_link'] = $hotel_data['video_link'];
			$formatted_hotel_data['Latitude'] = $hotel_data['latitude'];
			$formatted_hotel_data['Longitude'] = $hotel_data['longitude'];
			$formatted_hotel_data['HotelCategory'] = 'Hotel';
			$formatted_hotel_data['trip_adv_url'] = '';
			$formatted_hotel_data['trip_rating'] = '0.0';
			$hotel_amenities_codes = json_decode($hotel_data['amenities'], true);
			$formatted_hotel_data['HotelAmenities'] = $this->get_hotel_amenities($hotel_amenities_codes);
			$formatted_hotel_data['HotelLocation'] = $hotel_data['city_name'];
			$formatted_hotel_data['HotelPromotion'] = '';
			$formatted_hotel_data['HotelPromotionContent'] = '';
			$formatted_hotel_data['Price'] = array(
				'Tax' => 0,
				'ExtraGuestCharge' => 0,
				'ChildCharge' => 0,
				'OtherCharges' => 0,
				'Discount' => 0,
				'PublishedPrice' => $final_hotel_price,
				'RoomPrice' => $final_hotel_price,
				'PublishedPriceRoundedOff' => round($final_hotel_price, 2),
				'OfferedPrice' => $final_hotel_price,
				'OfferedPriceRoundedOff' => round($final_hotel_price, 2),
				'AgentCommission' => 0,
				'AgentMarkUp' => 0,
				'ServiceTax' => 0,
				'TDS' => 0,
				'ServiceCharge' => 0,
				'TotalGSTAmount' => 0,
				'RoomPriceWoGST' => $final_hotel_price,
				'GSTPrice' => 0,
				'CurrencyCode' => 'INR'
			);
			$formatted_hotel_data['rooms'] = $rooms;
			$formatted_hotel_data['room_codes'] = $room_codes;
			$final_hotel_results[] = $formatted_hotel_data;
		}

		return $final_hotel_results;
	}
	private function calculate_room_price($price_data, $adults, $childs, $currency_obj)
	{
		$adults_price = 0;
		$childs_price = 0;

		if ($adults > 0) {
			$adults_price = get_converted_currency_value($currency_obj->force_currency_conversion($price_data[$adults . '_adult_price']));
			$sup_adults_price = $price_data[$adults . '_adult_price'];
		}

		if ($childs > 0) {
			$childs_price = get_converted_currency_value($currency_obj->force_currency_conversion($price_data[$childs . '_child_price']));
			$sup_childs_price = $price_data[$childs . '_child_price'];
		}

		$admin_price = $adults_price + $childs_price;
		$supplier_price = $sup_adults_price + $sup_childs_price;
		return array('admin_price' => $admin_price, "supplier_price" => $supplier_price);
	}

	private function valid_search_result($search_result)
	{
		if (valid_array($search_result) == true and isset($search_result['Status']) == true and $search_result['Status'] == SUCCESS_STATUS) {
			return true;
		} else {
			return false;
		}
	}

	function cache_result_hotel_count($response)
	{
		$CI = &get_instance();
		$city_id = @$response['Search']['HotelSearchResult']['CityId'];
		$hotel_count = intval(count(@$response['Search']['HotelSearchResult']['HotelResults']));
		if ($hotel_count > 0 && $city_id != '') {
			$CI->custom_db->update_record(
				'all_api_city_master',
				array(
					'cache_hotels_count' => $hotel_count
				),
				array(
					'origin' => $city_id
				)
			);
		}
	}

	public function search_data_in_preferred_currency($search_result, $currency_obj, $search_id)
	{
		$hotels = $search_result['data']['HotelSearchResult']['HotelResults'];
		$hotel_list = array();

		foreach ($hotels as $hk => $hv) {
			$currency_obj_nw = new Currency(array('module_type' => 'hotel', 'from' => get_api_data_currency(), 'to' => get_application_currency_preference()));

			$hotel_list[$hk] = $hv;
			$hotel_list[$hk]['Price'] = $this->preferred_currency_fare_object($hv['Price'], $currency_obj);
		}
		$search_result['data']['HotelSearchResult']['PreferredCurrency'] = get_application_currency_preference();
		$search_result['data']['HotelSearchResult']['HotelResults'] = $hotel_list;

		return $search_result;
	}

	private function preferred_currency_fare_object($fare_details, $currency_obj, $default_currency = '', $gst = 0)
	{


		$price_details = array();
		$price_details['CurrencyCode'] = empty($default_currency) == false ? $default_currency : get_application_currency_preference();
		$price_details['RoomPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['RoomPrice']));
		$price_details['Tax'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['Tax']));
		$price_details['ExtraGuestCharge'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['ExtraGuestCharge']));
		$price_details['ChildCharge'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['ChildCharge']));
		$price_details['OtherCharges'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['OtherCharges']));
		$price_details['Discount'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['Discount']));
		$price_details['PublishedPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['PublishedPrice']));
		$price_details['PublishedPriceRoundedOff'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['PublishedPriceRoundedOff']));
		$price_details['OfferedPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['OfferedPrice']));
		$price_details['OfferedPriceRoundedOff'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['OfferedPriceRoundedOff']));
		$price_details['AgentCommission'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['AgentCommission']));
		$price_details['AgentMarkUp'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['AgentMarkUp']));
		$price_details['ServiceTax'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['ServiceTax']));
		$price_details['TDS'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['TDS']));

		return $price_details;
	}

	function update_search_markup_currency(&$price_summary, &$currency_obj, $search_id, $level_one_markup = false, $current_domain_markup = true, $gst = 0, $search_params = array(), $module = '')
	{
	    //debug($price_summary);die("bbbb");
		$multiplier = 1;
		$search_data = $this->search_data($search_id);
		$search_data_new = $search_data;
		if ($search_data['status'] == true) {
			$search_data = $search_data['data'];
			$multiplier = $search_data['room_count'];
		}

		$booking_source = $search_params['booking_source'];

		return $this->update_markup_currency($price_summary, $currency_obj, $multiplier, $level_one_markup, $current_domain_markup, $gst, $booking_source, $search_data_new, '', $module);
	}

	function update_markup_currency(&$price_summary, &$currency_obj, $no_of_nights = 1, $level_one_markup = false, $current_domain_markup = true, $gst = 0, $booking_source = '', $search_data = '', $tag = '', $module = '')
	{

		// debug($module);die;
		$tax_service_sum = 0;
		$tax_removal_list = array();
		$markup_list = array(
			'RoomPrice',
			'PublishedPrice',
			'PublishedPriceRoundedOff',
			'OfferedPrice',
			'OfferedPriceRoundedOff'
		);

		$markup_summary = array();
		// debug($price_summary);die;
		foreach ($price_summary as $__k => $__v) {


			$ref_cur = $currency_obj->force_currency_conversion($__v); // Passing Value By Reference so dont remove it!!!

			$price_summary[$__k] = $ref_cur['default_value']; // If you dont understand then go and study "Passing value by reference"
			
			if (in_array($__k, $markup_list)) {
                //debug($__v);
				$temp_price = $currency_obj->get_currency($__v, true, $level_one_markup, $current_domain_markup, $no_of_nights, $search_data, $booking_source, $tag);

                //debug($temp_price);die("test");
				if ($module == 'b2b') {
					//debug($temp_price);die;
				//	echo "b";
					$temp_price['default_value'] = $temp_price['agent_markup']['total_value'];
					$markup_summary['original_markup'] = $temp_price['agent_markup']['markup_value'] + $temp_price['admin_markup']['markup_value'];
					$markup_summary['markup_type'] = $temp_price['agent_markup']['markup_type'];
					$markup_summary['mark_id'] = $temp_price['agent_markup']['mark_id'];

					$markup_summary['AdminMarkUp'] = $temp_price['admin_markup']['markup_value'];
					$markup_summary['admin_markup_type'] = $temp_price['admin_markup']['markup_type'];
					$markup_summary['admin_mark_id'] = $temp_price['admin_markup']['mark_id'];
				} else {
				 //debug($temp_price);die("dfdfdfdfdf");
				 if($temp_price['admin_markup']['total_value']==''){
				      $temp_price['default_value'] =$temp_price['default_value'];
				 }else{
				     $temp_price['default_value'] = $temp_price['admin_markup']['total_value'];
				 }
					
					$markup_summary['original_markup'] = $temp_price['admin_markup']['original_markup'] ;
					$markup_summary['markup_type'] = $temp_price['admin_markup']['markup_type'];
					$markup_summary['mark_id'] = $temp_price['admin_markup']['mark_id'];

					$markup_summary['AdminMarkUp'] = $temp_price['admin_markup']['markup_value'];
					$markup_summary['admin_markup_type'] = $temp_price['admin_markup']['markup_type'];
					$markup_summary['admin_mark_id'] = $temp_price['admin_markup']['mark_id'];
					$mark_id =  $temp_price['admin_markup']['mark_id'];
				}
	//	debug($markup_summary);die;

			} elseif (is_array($__v) == false) {
				//debug($__v);die;
				$temp_price = $currency_obj->force_currency_conversion($__v);
			} else {
				$temp_price['default_value'] = $__v;
			}
			$markup_summary[$__k] = $temp_price['default_value'];


			//if (in_array($__k, $tax_removal_list)) {
			//	$markup_summary[$__k] = round($temp_price['default_value'] + $tax_service_sum);
			//} else {
			//	$markup_summary[$__k] = round($temp_price['default_value']);
			//}
		}
		//debug($module);die;
		if ($module == 'b2b') {
			$agent_markup = $markup_summary['RoomPrice'] - $price_summary['RoomPrice'];
			$markup_summary['AgentMarkUp'] = $agent_markup;
			//debug($markup_summary);die;
		}
		$Markup = 0;
		//if (isset($markup_summary['PublishedPrice'])) {
		//	$Markup = $markup_summary['PublishedPrice'] - $price_summary['PublishedPrice'];
		//}
		if (!empty($markup_summary['AdminMarkUp'])) {
			$Markup = $markup_summary['AdminMarkUp'];
		}

		$gst_value = 0;
		//adding gst
		if ($Markup > 0) {
			$gst_details = $GLOBALS['CI']->hotel_model->get_gst_details();
			//$gst_details = $GLOBALS['CI']->custom_db->single_table_records('gst_master', '*', array('module' => 'hotel'));
			if ($gst_details['status'] == true) {
				if ($gst_details['data'][0]['gst'] > 0) {
					$gst_value = ($Markup / 100) * $gst_details['data'][0]['gst'];
				}
			}
		}
		
		 //var_dump((int)str_replace(",", "", $markup_summary['RoomPrice']));die;
		$markup_summary['_GST'] = $gst_value;
		$markup_summary['PublishedPrice'] = round($markup_summary['PublishedPrice'] + $markup_summary['_GST']);
		$markup_summary['PublishedPriceRoundedOff'] = round($markup_summary['PublishedPriceRoundedOff'] + $markup_summary['_GST']);
		$markup_summary['OfferedPrice'] = round($markup_summary['OfferedPrice'] + $markup_summary['_GST'] + $Markup);
		$markup_summary['OfferedPriceRoundedOff'] = round($markup_summary['OfferedPriceRoundedOff'] + $markup_summary['_GST']);
		$markup_summary['RoomPrice'] = round((int)str_replace(",", "", $markup_summary['RoomPrice']) + $markup_summary['_GST']);
		$markup_summary['_Markup'] = round($Markup + $agent_markup);
		$markup_summary['_MARKID'] = $mark_id;
		//debug($markup_summary);die;
		return $markup_summary;
	}

	function format_search_response($hl, $cobj, $sid, $module = 'b2c', $fltr = array(), $gst = 0, $min_price = -1, $max_price = -1, $exclude_hotel = '', $search_params = array())
	{
        
		$level_one = true;
		$current_domain = true;
		if ($module == 'b2c') {
			$level_one = false;
			$current_domain = true;
		} else if ($module == 'b2b') {
			$level_one = true;
			$current_domain = true;
		}
		$HotelResults = array();
		if (isset($fltr['hl']) == true) {
			foreach ($fltr['hl'] as $tk => $tv) {
				$fltr['hl'][urldecode($tk)] = strtolower(urldecode($tv));
			}
		}

		$hc = 0;
		$frc = 0;
		//debug($hl);die;
		foreach ($hl['HotelSearchResult']['HotelResults'] as $hr => $hd) {
			$hc++;
			// default values
			$hd['StarRating'] = intval($hd['StarRating']);
			if (empty($hd['HotelLocation']) == true) {
				$hd['HotelLocation'] = 'Others';
			}
			if (isset($hd['Latitude']) == false) {
				$hd['Latitude'] = 0;
			}
			if (isset($hd['Longitude']) == false) {
				$hd['Longitude'] = 0;
			}
        //debug($hd['Price']);die;
			$hd['Price'] = $this->update_search_markup_currency($hd['Price'], $cobj, $sid, $level_one, $current_domain, $gst, $search_params, $module);

            //debug(	$hd['Price']);die("aaaa");
			$append = true;

			if ($max_price > -1) {
				if ($hd['Price']['RoomPrice'] > $max_price) {
					$append = false;
				}
			}
			if ($min_price > -1) {
				if ($min_price > $hd['Price']['RoomPrice']) {
					$append = false;
				}
			}
			if ($exclude_hotel != '') {
				if (intval($hd['HotelCode']) == intval($exclude_hotel)) {
					$append = false;
				}
			}

			if ($append) {
				$HotelResults[$hr] = $hd;
				$frc++;
			}
		}
		//echo "sathish";
		//die;

		// SORTING STARTS
		if (isset($fltr['sort_item']) == true && empty($fltr['sort_item']) == false && isset($fltr['sort_type']) == true && empty($fltr['sort_type']) == false) {
			$sort_item = array();
			foreach ($HotelResults as $key => $row) {
			   // debug($row);die;
				if ($fltr['sort_item'] == 'price') {
					$sort_item[$key] = floatval($row['Price']['RoomPrice']);
				} else if ($fltr['sort_item'] == 'star') {
					$sort_item[$key] = floatval($row['StarRating']);
				} else if ($fltr['sort_item'] == 'name') {
					$sort_item[$key] = trim($row['HotelName']);
				}
			}
			if ($fltr['sort_type'] == 'asc') {
				$sort_type = SORT_ASC;
			} else if ($fltr['sort_type'] == 'desc') {
				$sort_type = SORT_DESC;
			}
			if (valid_array($sort_item) == true && empty($sort_type) == false) {
				array_multisort($sort_item, $sort_type, $HotelResults);
			}
		} // SORTING ENDS


		$hl['HotelSearchResult']['HotelResults'] = $HotelResults;
		$hl['source_result_count'] = $hc;
		$hl['filter_result_count'] = $frc;
        //debug()
        //die;
		return $hl;
	}

	function filter_summary($hl)
	{
		$h_count = 0;
		$filt['p']['max'] = false;
		$filt['p']['min'] = false;
		$filt['loc'] = array();
		$filt['star'] = array();
		$filters = array();
		foreach ($hl['HotelSearchResult']['HotelResults'] as $hr => $hd) {
			// filters
			$StarRating = intval(@$hd['StarRating']);
			$HotelLocation = empty($hd['HotelLocation']) == true ? 'Others' : $hd['HotelLocation'];

			if (isset($filt['star'][$StarRating]) == false) {
				$filt['star'][$StarRating]['c'] = 1;
				$filt['star'][$StarRating]['v'] = $StarRating;
			} else {
				$filt['star'][$StarRating]['c']++;
			}

			if (($filt['p']['max'] != false && $filt['p']['max'] < $hd['Price']['RoomPrice']) || $filt['p']['max'] == false) {
				$filt['p']['max'] = roundoff_number($hd['Price']['RoomPrice']);
			}
			if (($filt['p']['min'] != false && $filt['p']['min'] > $hd['Price']['RoomPrice']) || $filt['p']['min'] == false) {
				$filt['p']['min'] = roundoff_number($hd['Price']['RoomPrice']);
			}

			if (($filt['p']['min'] != false && $filt['p']['min'] > $hd['Price']['RoomPrice']) || $filt['p']['min'] == false) {
				$filt['p']['min'] = $hd['Price']['RoomPrice'];
			}
			$hloc = ucfirst(strtolower($HotelLocation));
			if (isset($filt['loc'][$hloc]) == false) {
				$filt['loc'][$hloc]['c'] = 1;
				$filt['loc'][$hloc]['v'] = $hloc;
			} else {
				$filt['loc'][$hloc]['c']++;
			}

			$filters['data'] = $filt;
			$h_count++;
		}
		ksort($filters['data']['loc']);
		$filters['hotel_count'] = $h_count;
		return $filters;
	}

	public function get_hotel_details($ResultIndex)
	{
		$response['data'] = array();
		$response['status'] = false;
		$search_data = unserialized_data($ResultIndex);
		//debug($search_data);exit;
		$hotel_results = $this->get_hotels_from_search($search_data);
		//debug($hotel_results);exit;
		$no_of_nights = $search_data['no_of_nights'];

		if (count($hotel_results) > 0) {

			$first_room_price = 0;

			//calculaing total price for all rooms
			foreach ($hotel_results[$search_data['HotelCode']] as $key => $value) {
				$first_room_price += $value['total_price'];
			}

			$first_room_price = $first_room_price; // $no_of_nights;

			$hotel_results_keys = array_keys($hotel_results);
			$hotel_result = $hotel_results[$hotel_results_keys[0]];
			$hotel_data = $hotel_result[0];

			$hotel_images = $this->get_hotel_images($hotel_data['origin']);
			$hotel_amenities_codes = json_decode($hotel_data['amenities'], true);
			$hotel_amenities = $this->get_hotel_amenities($hotel_amenities_codes);

			$first_room_details['Price']['RoomPrice'] = $first_room_price;
			// $first_room_details['Price']['RoomPrice']

			$hotel_details_response = array(
				'Status' => 1,
				'Message' => '',
				'HotelDetails' => array(
					'HotelInfoResult' => array(
						'HotelDetails' => array(
							'HotelCode' => $hotel_data['origin'],
							'HotelName' => $hotel_data['name'],
							'StarRating' => intval($hotel_data['ratings']),
							'HotelFacilities' => $hotel_amenities,
							'Description' => $hotel_data['description'],
							'Images' => $hotel_images,
							'Attractions' => array(),
							'HotelURL' => '',
							'HotelPolicy' => '',
							'SpecialInstructions' => '',
							'Address' => empty($hotel_data['display_address']) ? 'Address not Available' : $hotel_data['display_address'],
							'HotelContactNo' => array(
								array(
									'type' => 'Voice',
									'number' => '+' . $hotel_data['country_code'] . ' ' . $hotel_data['phone'],
								)
							),
							'video_link' => $hotel_data['video_link'],
							'Latitude' => $hotel_data['latitude'],
							'Longitude' => $hotel_data['longitude'],
							"HotelCategoryName" => "Hotel",
							'PopularFacilities' => $hotel_amenities,
							'trip_adv_url' => '',
							'trip_rating' => '',
							'CheckInTime' => $hotel_data['check_in'],
							'CheckOutTime' => $hotel_data['check_out'],
							'checkin' => $search_data['raw_from_date'],
							'checkout' => $search_data['raw_to_date'],
							'first_rm_cancel_date' => '',
							'first_room_details' => $first_room_details,
							'api_type' => 'CRS'
						)
					)
				)
			);

			if ($this->valid_hotel_details($hotel_details_response)) {
				$response['data'] = $hotel_details_response['HotelDetails'];
				$response['status'] = true;
			} else {
				$response['data'] = $hotel_details_response;
			}
		}

		return $response;
	}

	public function get_hotel_images($hotel_code)
	{
		if (strstr(base_url(), 'supervision', true)) {
			$base_url = strstr(base_url(), 'supervision', true);
		} elseif (strstr(base_url(), 'agent', true)) {
			$base_url = strstr(base_url(), 'agent', true);
		} elseif (strstr(base_url(), 'subadmin', true)) {
			$base_url = strstr(base_url(), 'subadmin', true);
		} else {
			$base_url = base_url();
		}
		// echo $hotel_code;die;
		$hotel_images_url = array();

		$query = "SELECT image FROM eco_stays WHERE origin = " . $hotel_code;
		$hotel_images = $this->CI->db->query($query)->result_array();
		foreach ($hotel_images as $key => $value) {
			$hotel_images_url['data'][] = $base_url . $GLOBALS['CI']->template->domain_view_ecoimage() . $value['image'];
		}

		$query = "SELECT image FROM eco_stays_gallery_images WHERE stays_origin = " . $hotel_code;
		$hotel_images = $this->CI->db->query($query)->result_array();
		foreach ($hotel_images as $key => $value) {
			$hotel_images_url['data'][] = $base_url . $GLOBALS['CI']->template->domain_view_ecoimage() . $value['image'];
		}
		$hotel_images_url['status'] = true;
		// debug($hotel_images_url);die;
		return $hotel_images_url;
	}

	private function get_hotel_amenities($hotel_amenities_codes)
	{
		$hotel_amenities = array();
		if (is_array($hotel_amenities_codes) && count($hotel_amenities_codes) > 0) {
			$this->CI->db->select('name');
			$this->CI->db->where_in('origin', $hotel_amenities_codes);
			$hotel_amenities = $this->CI->db->get('eco_stays_amenities')->result_array();
			if (count($hotel_amenities) > 0) {
				$hotel_amenities = array_column($hotel_amenities, 'name');
			}
		}

		return $hotel_amenities;
	}

	private function get_hotel_amenities_by_id($hotel_code)
	{
		$hotel_amenities = array();
		$query = "SELECT amenities FROM eco_stays WHERE origin = " . $hotel_code;
		$hotel_amenities_codes = $this->CI->db->query($query)->result_array();
		if (count($hotel_amenities_codes) > 0) {
			$hotel_amenities_codes = json_decode($hotel_amenities_codes[0]['amenities'], TRUE);
			$this->CI->db->select('name');
			$this->CI->db->where_in('origin', $hotel_amenities_codes);
			$hotel_amenities = $this->CI->db->get('eco_stays_amenities')->result_array();
			if (count($hotel_amenities) > 0) {
				$hotel_amenities = array_column($hotel_amenities, 'name');
			}
		}
		return $hotel_amenities;
	}

	private function valid_hotel_details($hotel_details)
	{
		$status = false;
		if (valid_array($hotel_details) == true and isset($hotel_details['Status']) == true and $hotel_details['Status'] == SUCCESS_STATUS) {
			$status = true;
		}
		return $status;
	}

	function get_room_list($ResultToken)
	{
		$response['data'] = array();
		$response['status'] = false;

		$search_data = unserialized_data($ResultToken);

		$hotel_room_list_response = $this->get_hotel_rooms_from_search($search_data);


		if ($this->valid_room_details_details($hotel_room_list_response)) {
			$response['data'] = $hotel_room_list_response['RoomList'];
			$response['status'] = true;
		} else {
			$response['data'] = $hotel_room_list_response;
		}

		return $response;
	}

	private function get_hotel_rooms_from_search($search_data)
	{
		$room_combinations = array();
		$rooms_result = array();
		$rooms_temp_result = array();

		$available_stay = $this->get_available_hotels_and_rooms($search_data);

		foreach ($available_stay as $item) {
			foreach ($item as $origin => $data) {
				if (!isset($mergedArray[$origin])) {
					$mergedArray[$origin] = $data;
				} else {
					// Merge rooms
					foreach ($data['rooms'] as $roomId => $roomData) {
						if (isset($mergedArray[$origin]['rooms'][$roomId])) {
							// Room already exists, add total price
							$mergedArray[$origin]['rooms'][$roomId]['total_price'] += $roomData['total_price'];
						} else {
							// Room doesn't exist, add it
							$mergedArray[$origin]['rooms'][$roomId] = $roomData;
						}
					}
				}
			}
		}
		$available_stays[] = $mergedArray;
		//check same room


		$stay_origin = 0;
		if (empty($available_stays) == false) {
			$first_stay = $available_stays[0];
			if (empty($first_stay) == false) {
				$first_stay = array_keys($first_stay);
				$stay_origin = $first_stay[0];
			}
		}
		$from_date = str_replace("/", "-", $search_data['from_date']);
		if (intval($stay_origin) > 0) {

			$room_index = 0;

			foreach ($available_stays as $key => $available_stay) {

				$stay = $available_stay[$stay_origin];
				$rooms = $stay['rooms'];
				foreach ($rooms as $room_origin => $room) {
					if (!isset($rooms_temp_result[$room_origin])) {
						$rooms_temp_result[$room_origin] = $this->get_room_details($room_origin, $from_date);
					}

					$room_id = $room_origin;
					$room_id_i = 0;
					while (isset($rooms_result[$room_id])) {
						$room_id_i++;
						$room_id = $room_id . '_' . $room_id_i;
					}
					$rooms_result[$room_id] = $rooms_temp_result[$room_origin];

					$currency_id =  $available_stays[0][$stay_origin]['currency'];
					$partial_pay = $available_stays[0][$stay_origin]['partial_value'];
					if ($partial_pay > 0) {
						$part_val = $partial_pay;
						$part_flag = TRUE;
					} else {
						$part_val = 0;
						$part_flag = FALSE;
					}
					$supplier['currency'] = $GLOBALS['CI']->custom_db->single_table_records("currency_converter", "country", array("id" => $currency_id))['data'][0]['country'];
					$supplier['supplier_total_price'] = $room['supplier_total_price'];
					//setting room price
					$rooms_result[$room_id]['Price'] = $this->get_room_price_details($room['total_price'], $supplier);

					//setting room Index
					$rooms_result[$room_id]['RoomIndex'] = $room_index;

					//setting room RoomUniqueId
					$rooms_result[$room_id]['RoomUniqueId'] = $room_origin . '_' . $room_index;

					//setting room index for original $available_stays
					$available_stays[$key][$stay_origin]['rooms'][$room_origin]['room_index'] = $room_index++;
				}
			}

			$room_combinations_result = $this->generate_unique_room_combinations($available_stays, $stay_origin);

			foreach ($room_combinations_result as $room_combination_key => $room_combination) {
				$room_combinations[$room_combination_key] = array();
				foreach ($room_combination as $i_k => $room) {
					$room_combinations[$room_combination_key]['RoomIndex'][] = $room['room_index'];
				}
			}
		}

		$rooms_result = array_values($rooms_result);
		//check for partial amount

		$hotel_room_result = array(
			"Status" => true,
			"Message" => '',
			"isPartialRequired" => $part_flag,
			"partialValue" => $part_val,
			"RoomList" => array(
				"GetHotelRoomResult" => array(
					"HotelRoomsDetails" => array_values($rooms_result),
					"RoomCombinations" => array(
						'InfoSource' => 'FixedCombination',
						'IsPolicyPerStay' => false,
						'RoomCombination' => $room_combinations
					),
					"api" => "CRS"
				)
			),

		);

		return $hotel_room_result;
	}

	private function get_room_details($room_origin, $from_date = '')
	{
		$this->CI->db->where("origin", $room_origin);
		$room_data = $this->CI->db->get("eco_stays_rooms")->result_array();
		$cancel_data = $this->get_room_cancellation_policies($room_origin);

		$last_date = ($from_date != "" ? date('Y-m-d', strtotime("- " . $cancel_data[0]['day'] . ' day', strtotime($from_date))) : '');

		$room_details = array();
		$room_amenities_codes = json_decode($room_data[0]["amenities"], TRUE);
		$room_details['Amenities'] = $this->get_room_amenities($room_amenities_codes);
		$room_details['cancellation_policy_code'] = "";
		$room_details["CancellationPolicies"] = $this->get_room_cancellation_policies($room_origin);
		$room_details["CancellationPolicy"] = "";
		$room_details["ChildCount"] = $room_data[0]["max_childs"];
		$room_details["group_code"] = "";
		$room_details["HOTEL_CODE"] = $room_data[0]["stays_origin"];
		$room_details["LastCancellationDate"] = $last_date;
		$room_details["OtherAmennities"] = array();
		$room_details["Price"] = array();
		$room_details["rate_key"] = 2;
		$room_details["RatePlanCode"] = 1;
		$room_details["room_code"] = $room_data[0]["origin"];
		$room_details["board_type"] = $this->get_board_type_name($room_data[0]["board_type"]);;
		$room_details["RoomIndex"] = $room_data[0]["origin"];
		$room_details["RoomTypeCode"] = $room_data[0]["type"];
		$room_details["RoomTypeName"] = $this->get_room_type_name($room_data[0]["type"]);
		$room_details["RoomUniqueId"] = $room_data[0]["origin"];
		$room_details["SEARCH_ID"] = "";
		$room_details["SmokingPreference"] = "NoPreference";

		return $room_details;
	}
	private function get_board_type_name($type_origin)
	{
		$room_type_name = '';

		$this->CI->db->select('name');
		$this->CI->db->where_in('origin', $type_origin);
		$type = $this->CI->db->get('eco_stays_board_types')->result_array();

		if (count($type) > 0) {
			$board_type_name = $type[0]['name'];
		}

		return $board_type_name;
	}
	private function get_room_price_details($final_room_price, $supplier_data = '')
	{

		$room_price_details = array(
			'Tax' => 0,
			'ExtraGuestCharge' => 0,
			'ChildCharge' => 0,
			'OtherCharges' => 0,
			'Discount' => 0,
			'PublishedPrice' => $final_room_price,
			'RoomPrice' => $final_room_price,
			'SupplierOriginalPrice' => $supplier_data['supplier_total_price'],
			'PublishedPriceRoundedOff' => round($final_room_price, 2),
			'OfferedPrice' => $final_room_price,
			'OfferedPriceRoundedOff' => round($final_room_price, 2),
			'AgentCommission' => 0,
			'AgentMarkUp' => 0,
			'ServiceTax' => 0,
			'TDS' => 0,
			'ServiceCharge' => 0,
			'TotalGSTAmount' => 0,
			'RoomPriceWoGST' => $final_room_price,
			'GSTPrice' => 0,
			'CurrencyCode' => 'INR',
			'SupplierCurrencyCode' => $supplier_data['currency']
		);
		return $room_price_details;
	}
	private function get_room_amenities($room_amenities_codes)
	{
		$room_amenities = array();
		if (is_array($room_amenities_codes) && count($room_amenities_codes) > 0) {
			$this->CI->db->select('name');
			$this->CI->db->where_in('origin', $room_amenities_codes);
			$room_amenities = $this->CI->db->get('eco_stays_room_amenities')->result_array();
			if (count($room_amenities) > 0) {
				$room_amenities = array_column($room_amenities, 'name');
			}
		}
		return $room_amenities;
	}

	private function get_room_cancellation_policies($room_origin)
	{
		$room_cancellation_policies = array();

		$this->CI->db->where('room_origin', $room_origin);
		$this->CI->db->order_by('to_before_days', 'DESC'); // or 'DESC' for descending order

		$room_cancellation_policies_data = $this->CI->db->get('eco_stays_room_cancellation_policy')->result_array();

		foreach ($room_cancellation_policies_data as $id => $can_val) {
			if ($can_val['penality_type'] == 'percentage') {
				$statement[$id]['day'] = $can_val['to_before_days'];
				$statement[$id]['type'] = 'percentage';
				$statement[$id]['value'] = $can_val['penality_value'];

				$statement[$id]['statement'] = $can_val['penality_value'] . ' % before ' . $can_val['to_before_days'] . ' days';
			} elseif ($can_val['penality_type'] == 'plus') {
				$statement[$id]['day'] = $can_val['to_before_days'];
				$statement[$id]['type'] = 'plus';
				$statement[$id]['value'] = $can_val['penality_value'];
				$statement[$id]['statement'] = 'INR ' . $can_val['penality_value'] . ' before ' . $can_val['to_before_days'] . ' day';
			}
		}


		return $statement;
	}
	function cancellation_policy($data)
	{


		$this->CI->db->where('room_origin', $data['room_code']);
		$this->CI->db->order_by('to_before_days', 'DESC'); // or 'DESC' for descending order

		$room_cancellation_policies_data = $this->CI->db->get('eco_stays_room_cancellation_policy')->result_array();

		$search_data = $this->search_data($data['tb_search_id']);

		foreach ($room_cancellation_policies_data as $id => $jk) {
			$from_date = str_replace('/', "-", $search_data['data']['from_date']);

			$from_date = date("Y-m-d", strtotime("-" . $jk['to_before_days'] . " day", strtotime($from_date)));
			$i = $id + 1;
			if ($jk['penality_type'] == 'plus') {

				$cancel['from_date'] = $from_date;
				$cancel['type'] = $jk['penality_type'];
				$cancel['amount'] = $jk['penality_value'];
				$add_on = '';
				if ($jk['penality_value'] == 0) {
					$add_on = 'No cancellation charges, If cancelled before ' . date("d M Y", strtotime($from_date));
					$cancel['no_cancel'] = $add_on;

					$ad = 'Till';
				} else {
					$ad = 'After';
				}
				$cancel['description'] = $ad . ' ' . date("d M Y", strtotime($from_date)) . " INR " . $jk['penality_value'] . " charge will be applied.";
			} elseif ($jk['penality_type'] == 'percentage') {
				$cancel['from_date'] = $from_date;
				$cancel['type'] = $jk['penality_type'];
				$cancel['amount'] = $jk['penality_value'];
				$add_on = '';
				if ($jk['penality_value'] == 0) {
					$add_on = 'No cancellation charges, If cancelled before ' . date("d M Y", strtotime($from_date));
					$cancel['no_cancel'] = $add_on;
					$ad = 'Till';
				} else {
					$ad = 'After';
				}
				$cancel['description'] = $ad . ' ' . date("d M Y", strtotime($from_date)) . "  " . $jk['penality_value'] . "% charge will be applied.";
			}
			$cancellation['cancellation'][$id] = $cancel;
		}
		return $cancellation;
	}

	private function get_room_type_name($type_origin)
	{
		$room_type_name = '';

		$this->CI->db->select('name');
		$this->CI->db->where_in('origin', $type_origin);
		$type = $this->CI->db->get('eco_stays_room_types')->result_array();

		if (count($type) > 0) {
			$room_type_name = $type[0]['name'];
		}

		return $room_type_name;
	}

	private function generate_room_combinations($available_stays, $stay_origin, $index = 0, $current_combination = [])
	{
		$combinations = [];

		if ($index == count($available_stays)) {
			$combinations[] = $current_combination;
			return $combinations;
		}

		$stays = $available_stays[$index];
		$stay = $stays[$stay_origin];
		$rooms = $stay['rooms'];




		foreach ($rooms as $room_origin => $room) {
			$new_combination = $current_combination;
			$new_combination[] = $room;

			if ($this->is_valid_combination($new_combination)) {
				$combinations = array_merge(
					$combinations,
					$this->generate_room_combinations($available_stays, $stay_origin, $index + 1, $new_combination)
				);
			}
		}




		return $combinations;
	}

	private function generate_unique_room_combinations($available_stays, $stay_origin)
	{
		$combinations = $this->generate_room_combinations($available_stays, $stay_origin);


		$unique_combinations = array();

		for ($i = 0; $i < count($combinations); $i++) {
			$combination_i_rooms = array_column($combinations[$i], 'room_origin');


			$is_in_unique_combination = false;

			for ($j = 0; $j < count($unique_combinations); $j++) {
				$combination_j_rooms = array_column($unique_combinations[$j], 'room_origin');



				if (count($combination_j_rooms) == count($combination_i_rooms)) {
					sort($combination_i_rooms);
					sort($combination_j_rooms);
					$is_same = true;
					for ($k = 0; $k < count($combination_j_rooms); $k++) {
						if ($combination_j_rooms[$k] != $combination_i_rooms[$k]) {
							$is_same = false;
							break;
						}
					}
					if ($is_same) {
						$is_in_unique_combination = true;
					}
				}
			}

			if (!$is_in_unique_combination) {
				$unique_combinations[] = $combinations[$i];
			}
		}


		return $unique_combinations;
	}

	private function is_valid_combination($combination)
	{
		$room_quantities = [];

		foreach ($combination as $room) {
			$room_origin = $room['room_origin'];
			$quantity = $room['quantity'];

			if (isset($room_quantities[$room_origin])) {
				// Check if the room quantity is available
				if ($room_quantities[$room_origin] < $quantity) {
					return false;
				}
			} else {
				$room_quantities[$room_origin] = $quantity;
			}
		}

		return true;
	}

	private function valid_room_details_details($room_list)
	{
		$status = false;
		if (valid_array($room_list) == true and isset($room_list['Status']) == true and $room_list['Status'] == SUCCESS_STATUS) {
			$status = true;
		}
		return $status;
	}

	public function roomlist_in_preferred_currency($room_list, $currency_obj, $search_id, $module = 'b2c', $gst = 0, $booking_source = '')
	{

		$level_one = true;
		$current_domain = true;
		if ($module == 'b2c') {
			$level_one = false;
			$current_domain = true;
		} else if ($module == 'b2b') {
			$level_one = true;
			$current_domain = true;
		}
		$search_params = $this->search_data($search_id);
		$application_currency_preference = get_application_currency_preference();
		$hotel_room_details = $room_list['data']['GetHotelRoomResult']['HotelRoomsDetails'];
		$hotel_room_result = array();
		foreach ($hotel_room_details as $hr_k => $hr_v) {
			$hotel_room_result[$hr_k] = $hr_v;
			// Price
			$API_raw_price = $hr_v['Price'];

			// $data_price = $this->update_search_markup_currency($hr_v['Price'], $currency_obj, $search_id, $level_one, $current_domain, $gst, $search_params);

			$Price = $this->preferred_currency_fare_object($hr_v['Price'], $currency_obj, $gst);

			// CancellationPolicies
			$CancellationPolicies = array();
			foreach ($hr_v['CancellationPolicies'] as $ck => $cv) {
				//add cancellation charge in markup

				$Charge = $this->update_cancellation_markup_currency($cv['Charge'], $currency_obj, $search_id, $level_one, $current_domain);

				$CancellationPolicies[$ck] = $cv;
				$CancellationPolicies[$ck]['Currency'] = $application_currency_preference;
				//$CancellationPolicies [$ck] ['Charge'] = get_converted_currency_value ( $currency_obj->force_currency_conversion ( $Charge ) );
				$CancellationPolicies[$ck]['Charge'] = $Charge;
			}
			$hotel_room_result[$hr_k]['API_raw_price'] = $API_raw_price;
			$hotel_room_result[$hr_k]['Price'] = $Price;
			$hotel_room_result[$hr_k]['CancellationPolicies'] = $CancellationPolicies;
			// CancellationPolicy:FIXME: convert the INR price to preferred currency
		}
		$room_list['data']['GetHotelRoomResult']['HotelRoomsDetails'] = $hotel_room_result;
		return $room_list;
	}

	function update_room_markup_currency(&$price_summary, &$currency_obj, $search_id, $level_one_markup = false, $current_domain_markup = true, $gst = 0, $tag = '', $module = '')
	{
		$search_data = $this->search_data($search_id);

		$no_of_nights = $search_data['data']['no_of_nights'];
		$multiplier = $no_of_nights;
		return $this->update_markup_currency($price_summary, $currency_obj, $multiplier, $level_one_markup, $current_domain_markup, $gst, 'PTBSID0000000011', $search_data, $tag, $module);
	}

	function block_room($pre_booking_params)
	{
		$response['status'] = false;
		$response['data'] = array();
		$run_block_room_request = true;
		$block_room_request_count = 0;

		$ResultToken = $pre_booking_params['ResultIndex'];
		$search_data = unserialized_data($ResultToken);
		//debug($search_data);die;
		$from_date = date('d-m-Y', strtotime($search_data['raw_from_date']));
		//debug($from_date);die;
		$from_date = date('Y-m-d', strtotime($from_date));
		$to_date = date('d-m-Y', strtotime($search_data['raw_to_date']));
		//debug($to_date);die;
		//$to_date = $to_date->sub(new DateInterval('P1D'));
		$to_date = date('Y-m-d', strtotime($to_date));

		$date_range = $this->generate_date_range($from_date, $to_date);

		$requested_rooms_for_block = $pre_booking_params['token'];

		$block_room_response = array(
			'Status' => false,
			'Message' => '',
		);

		while ($run_block_room_request) {
			//get available room combination
			$_hotel_room_list_response = $this->get_hotel_rooms_from_search($search_data);

			$hotel_room_list_response = $this->replace_room_index_with_unique_id_in_combination($_hotel_room_list_response);
			//check if requested combination exist
			if ($this->room_cobmination_exists($hotel_room_list_response["RoomList"]["GetHotelRoomResult"]["RoomCombinations"]["RoomCombination"], $requested_rooms_for_block)) {

				$requested_room_origins_for_block = $this->get_room_origins_from_room_unique_ids($requested_rooms_for_block, $_hotel_room_list_response);
				//blocking rooms
				$this->CI->db->set('holded', 'holded + 1', FALSE);
				$this->CI->db->where_in('room_origin', $requested_room_origins_for_block);
				$this->CI->db->where_in('date', $date_range);
				$this->CI->db->update('eco_stays_room_availability');
				$block_data = array(
					'room_origins' => json_encode($requested_room_origins_for_block),
					'dates' => json_encode($date_range),
					'search_id' => $pre_booking_params['search_id']
				);
				$this->CI->db->insert('eco_stays_blocked_rooms', $block_data);

				$num_inserts = $this->CI->db->affected_rows();
				$block_id = -1;
				if (intval($num_inserts) > 0) {
					$block_id = $this->CI->db->insert_id();
				}
				$hotel_room_details = array();

				foreach ($requested_rooms_for_block as $room_unique_id) {
					$hotel_room_details[] = $this->get_hotel_room_details($room_unique_id, $_hotel_room_list_response, $from_date);
				}

				$block_room_response['Status'] = true;
				$block_room_response['BlockRoom'] = array(
					'BlockRoomResult' => array(
						'HotelRoomsDetails' => $hotel_room_details,
						'BlockRoomId' => $block_id,
						'IsPriceChanged' => false,
						'isPartialRequired' => $_hotel_room_list_response['isPartialRequired'],
						'partialValue' => $_hotel_room_list_response['partialValue'],
						'IsCancellationPolicyChanged' => false,
						'api_type' => 'CRS'
					)
				);
			}

			$api_block_room_response_status = $block_room_response['Status'];

			if ($api_block_room_response_status == true) {
				$response['status'] = true;
				$run_block_room_request = false;
			} else {
				$run_block_room_request = true;
			}

			$block_room_request_count++; // Increment number of times request is run
			if ($block_room_request_count == 3 && $run_block_room_request == true) {
				// try max 3times to block the room
				$run_block_room_request = false;
			}
		}


		$response['data']['response'] = $block_room_response['BlockRoom'];

		return $response;
	}

	private function get_room_origins_from_room_unique_ids($room_unique_ids, $hotel_room_list)
	{
		$rooms_origins = array();

		foreach ($room_unique_ids as $_key => $room_unique_id) {
			foreach ($hotel_room_list["RoomList"]["GetHotelRoomResult"]["HotelRoomsDetails"] as $key => $room) {
				if ($room['RoomUniqueId'] == $room_unique_id) {
					$rooms_origins[] = $room['room_code'];
					unset($hotel_room_list["RoomList"]["GetHotelRoomResult"]["HotelRoomsDetails"][$key]);
				}
			}
		}

		return $rooms_origins;
	}

	private function replace_room_index_with_unique_id_in_combination($room_list)
	{
		$new_room_list = $room_list;
		foreach ($new_room_list["RoomList"]["GetHotelRoomResult"]["RoomCombinations"]["RoomCombination"] as $key => $combination) {
			foreach ($combination['RoomIndex'] as $index_key => $room_index) {
				$room_unique_id = $this->get_room_unique_id_for_room_index($room_list, $room_index);
				$new_room_list["RoomList"]["GetHotelRoomResult"]["RoomCombinations"]["RoomCombination"][$key]['RoomIndex'][$index_key] = $room_unique_id;
			}
		}
		return $new_room_list;
	}

	private function get_room_unique_id_for_room_index($room_list, $room_index)
	{
		$room_unique_id = -1;
		foreach ($room_list["RoomList"]["GetHotelRoomResult"]['HotelRoomsDetails'] as $key => $room_details) {
			if ($room_details["RoomIndex"] == $room_index) {
				return $room_details['RoomUniqueId'];
			}
		}
		return $room_unique_id;
	}

	private function room_cobmination_exists($room_combinations_with_unique_id, $requested_combination)
	{
		foreach ($room_combinations_with_unique_id as $key => $combination) {
			$cobination_rooms = $combination["RoomIndex"];
			$is_same = true;
			foreach ($cobination_rooms as $j_key => $room_origin) {
				if ($requested_combination[$j_key] != $room_origin) {
					$is_same = false;
					break;
				}
			}
			if ($is_same) {
				return true;
			}
		}
		return false;
	}


	function generate_date_range($start_date, $end_date)
	{
		$start_date_obj = DateTime::createFromFormat('Y-m-d', $start_date);
		$end_date_obj = DateTime::createFromFormat('Y-m-d', $end_date);

		$interval = new DateInterval('P1D'); // 1 day interval
		$date_range = new DatePeriod($start_date_obj, $interval, $end_date_obj->modify('+1 day'));

		$date_array = array_map(function ($date) {
			return $date->format('Y-m-d');
		}, iterator_to_array($date_range));

		return $date_array;
	}

	private function get_hotel_room_details($room_unique_id, $hotel_room_list, $from_date)
	{
		$hotel_room_details = array();
		foreach ($hotel_room_list["RoomList"]["GetHotelRoomResult"]['HotelRoomsDetails'] as $key => $room_details) {
			if ($room_details["RoomUniqueId"] == $room_unique_id) {

				$cancel_data = $this->cancellation_policy($room_details);

				$room_origin = $room_details['room_code'];
				$this->CI->db->where("origin", $room_origin);
				$room_data = $this->CI->db->get("eco_stays_rooms")->result_array();

				$hotel_room_details = $room_details;
				$hotel_room_details['AvailabilityType'] = 'Confirm';
				$hotel_room_details["RequireAllPaxDetails"] = true;
				$hotel_room_details["RoomId"] = $room_origin;
				$hotel_room_details["RoomStatus"] = 0;
				$hotel_room_details["RoomDescription"] = $room_data[0]["description"];
				$hotel_room_details["RatePlan"] = 0;
				$hotel_room_details["RatePlanName"] = "Room Only";
				$hotel_room_details["InfoSource"] = "FixedCombination";
				$hotel_room_details["SequenceNo"] = "";
				$hotel_room_details["IsPerStay"] = false;
				$hotel_room_details["SupplierPrice"] = null;
				$hotel_room_details["RoomPromotion"] = "";
				$hotel_room_details["Amenity"] = array();
				$hotel_room_details["BedTypes"] = array();
				$hotel_room_details["HotelSupplements"] = array();
				$hotel_room_details["LastCancellationDate"] = $cancel_data['cancellation'][0]['from_date'];
				$hotel_room_details["LastVoucherDate"] = $cancel_data['cancellation'][0]['from_date'];
				$hotel_room_details["Inclusion"] = $room_details['Amenities'];
				$hotel_room_details["IsPassportMandatory"] = false;
				$hotel_room_details["IsPANMandatory"] = false;
				$hotel_room_details["HotelCode"] = $room_data[0]["stays_origin"];
				$hotel_room_details["API_raw_price"] = 1;
				$hotel_room_details["AccessKey"] = '';
				$hotel_room_details["Address"] = '';
				$hotel_room_details["Boarding_details"] = '';
				$hotel_room_details["TM_Cancellation_Charge"] = $cancel_data['cancellation'];
				break;
			}
		}
		return $hotel_room_details;
	}

	public function get_hotel_detail_by_room_id($room_id = 0)
	{
		$response = array(
			'status' => false,
			'data' => array()
		);
		$query = "SELECT stay.*, room.origin AS room_origin , room.type AS room_type_id ,room_type.name AS room_type_name
					FROM eco_stays AS stay
					LEFT JOIN eco_stays_rooms AS room ON room.stays_origin = stay.origin
					LEFT JOIN eco_stays_room_types AS room_type ON room_type.origin = room.type
					WHERE room.origin = $room_id";

		$result = $this->CI->db->query($query)->result_array();

		if (empty($result) == false) {
			$response['status'] = true;
			$result[0]['image'] = base_url() . $GLOBALS['CI']->template->domain_eco_stays_images_upload_dir('stays/' . $result[0]['image']);
			$response['data'] = $result[0];
		}

		return $response;
	}

	public function roomblock_data_in_preferred_currency($block_room_data, $currency_obj, $search_id, $module = 'b2c', $booking_source = '')
	{
		$level_one = true;
		$current_domain = true;
		if ($module == 'b2c') {
			$level_one = false;
			$current_domain = true;
		} else if ($module == 'b2b') {
			$level_one = true;
			$current_domain = true;
		}
		$application_currency_preference = get_application_currency_preference();
		$hotel_room_details = $block_room_data['data']['response']['BlockRoomResult']['HotelRoomsDetails'];
		$hotel_room_result = array();
		foreach ($hotel_room_details as $hr_k => $hr_v) {
			$hotel_room_result[$hr_k] = $hr_v;

			// Price
			$API_raw_price = $hr_v['Price'];
			$Price = $this->preferred_currency_fare_object($hr_v['Price'], $currency_obj);
			// CancellationPolicies
			$CancellationPolicies = array();
			foreach ($hr_v['CancellationPolicies'] as $ck => $cv) {
				$Charge = $this->update_cancellation_markup_currency($cv['Charge'], $currency_obj, $search_id, $level_one, $current_domain);
				$CancellationPolicies[$ck] = $cv;
				$CancellationPolicies[$ck]['Currency'] = $application_currency_preference;
				$CancellationPolicies[$ck]['Charge'] = $Charge;
			}
			$hotel_room_result[$hr_k]['API_raw_price'] = $API_raw_price;
			$hotel_room_result[$hr_k]['Price'] = $Price;
			$hotel_room_result[$hr_k]['CancellationPolicies'] = $CancellationPolicies;
			// CancellationPolicy:FIXME: convert the INR price to preferred currency
		}
		$block_room_data['data']['response']['BlockRoomResult']['HotelRoomsDetails'] = $hotel_room_result;
        //debug($block_room_data);die;
		return $block_room_data;
	}

	function update_booking_markup_currency(&$price_summary, &$currency_obj, $search_id, $gst = 0, $level_one_markup = false, $search_params = '', $current_domain_markup = true, $module = '')
	{
		$multiplier = 1;
		$search_data = $this->search_data($search_id);
		$search_data_new = $search_data;
		if ($search_data['status'] == true) {
			$search_data = $search_data['data'];
			$multiplier = $search_data['no_of_nights'] * $search_data['room_count'];
		}
		//debug($search_params);die;
		$booking_source = $search_params;
		return $this->update_markup_currency($price_summary, $currency_obj, $multiplier, $level_one_markup, $current_domain_markup, $gst, $booking_source, $search_data_new, '', $module);
	}

	function update_cancellation_markup_currency(&$cancel_charge, &$currency_obj, $search_id, $level_one_markup = false, $current_domain_markup = true)
	{
		$search_data = $this->search_data($search_id);
		$booking_source = $booking_source['booking_source'];
		$no_of_nights = $search_data['no_of_nights'];
		$temp_price = $currency_obj->get_currency($cancel_charge, true, $level_one_markup, $current_domain_markup, $no_of_nights);

		return round($temp_price['default_value']);
	}

	public function update_block_details($room_details, $booking_parameters, $cancel_currency_obj)
	{

		$Surcharge_total = 0;
		foreach ($room_details['HotelRoomsDetails'] as $key => $value) {
			$Surcharge_total += @$value['Surcharge_total'];
		}




		$booking_parameters['BlockRoomId'] = $room_details['BlockRoomId'];
		$room_details['HotelRoomsDetails'] = get_room_index_list($room_details['HotelRoomsDetails']);
		$booking_parameters['token'] = array(); // Remove all the token details

		foreach ($room_details['HotelRoomsDetails'] as $__rc_key => $__rc_value) {

			$booking_parameters['token'][] = get_dynamic_booking_parameters($__rc_key, $__rc_value, get_application_currency_preference());
			$booking_parameters['price_token'][] = $__rc_value['Price'];
			$booking_parameters['HotelCode'] = $__rc_value['HotelCode'];
		}

		$policy_string = '';
		$cancel_string = '';

		$last_cancellation_date = $room_details['HotelRoomsDetails'][0]['LastCancellationDate'];

		$cancellation_details = $room_details['HotelRoomsDetails'][0]['TM_Cancellation_Charge'];
		$room_price = 0;
		foreach ($room_details['HotelRoomsDetails'] as $p_key => $p_value) {
			$room_price += $p_value['Price']['RoomPrice'];
		}

		$cancel_count = count($cancellation_details);


		if ($cancellation_details && !empty($last_cancellation_date)) {
			foreach ($cancellation_details as $key => $value) {
				$amount = 0;
				$policy_string = '';

				if ($value['amount'] == 0) {
					$policy_string .= 'No cancellation charges, if cancelled before ' . date('d M Y', strtotime($value['from_date']));
				} else {

					if (isset($cancellation_rev_details[$key + 1])) {
						if ($value['type'] == 'plus') {
							$amount = $cancel_currency_obj->get_currency_symbol($cancel_currency_obj->to_currency) . " " . $value['amount'];
						} elseif ($value['type'] == 'percentage') {
							$amount = $cancel_currency_obj->get_currency_symbol($cancel_currency_obj->to_currency) . " " . $room_price;
						}

						$current_date = date('Y-m-d');
						$cancell_date = date('Y-m-d', strtotime($value['from_date']));
						if ($cancell_date > $current_date) {

							$policy_string .= 'Cancellations made after ' . date('d M Y', strtotime($value['from_date'])) .  ', would be charged ' . $amount;
						}
					} else {
						if ($value['type'] == 'plus') {
							$amount = $cancel_currency_obj->get_currency_symbol($cancel_currency_obj->to_currency) . " " . $value['amount'];
						} elseif ($value['type'] == 'percentage') {
							$amount = " " .  $value['amount'] . '%';
						}


						$current_date = date('Y-m-d');
						$cancell_date = date('Y-m-d', strtotime($value['from_date']));
						if ($cancell_date > $current_date) {
							$policy_string .= 'Cancellations made after ' . date('d M Y', strtotime($value['from_date'])) . ',  would be charged ' . $amount;
						} else {
							$value['from_date'] = date('Y-m-d');
							$policy_string .= 'This rate is non-refundable. If you cancel this booking you will not be refunded any of the payment.';
						}
					}
				}

				$cancel_string .= $policy_string . '<br/>';
			}
		} else {
			$cancel_string = 'This rate is non-refundable. If you cancel this booking you will not be refunded any of the payment.';
		}


		if (isset($room_details['HotelRoomsDetails'][0]['RoomTypeName'])) {
			$booking_parameters['RoomTypeName'] = $room_details['HotelRoomsDetails'][0]['RoomTypeName'];
		}

		if (isset($room_details['HotelRoomsDetails'][0]['Boarding_details'])) {
			$booking_parameters['Boarding_details1'][] = $room_details['HotelRoomsDetails'][0]['Boarding_details'];
		}



		$booking_parameters['CancellationPolicy'] = array($cancel_string);

		$booking_parameters['isPartialRequired'] = $room_details['isPartialRequired'];
		$booking_parameters['partialValue'] = $room_details['partialValue'];
		$booking_parameters['LastCancellationDate'] = $last_cancellation_date;
		$booking_parameters['CancellationPolicy_API'] = array($room_details['HotelRoomsDetails'][0]['CancellationPolicy']);
		$booking_parameters['TM_Cancellation_Charge'] = $cancellation_details;

		$booking_parameters['Boarding_details'] = $room_details['HotelRoomsDetails'][0]['Boarding_details'];
		$booking_parameters['Surcharge_total'] = @$Surcharge_total;
		$booking_parameters['sur_Charge_exclude'] = @$room_details['HotelRoomsDetails'][0]['surCharge_exclude'];
		$booking_parameters['surCharge_exclude_name'] = @$room_details['HotelRoomsDetails'][0]['surCharge_exclude_name'];
		$booking_parameters['price_summary'] = tbo_summary_room_combination($room_details['HotelRoomsDetails']);
		$booking_parameters['api_type'] = 'CRS';

		return $booking_parameters;
	}

	function php_arrayUnique($array, $key)
	{
		$temp_array = array();
		$i = 0;
		$key_array = array();

		foreach ($array as $val) {
			if (!in_array($val[$key], $key_array)) {
				$key_array[$i] = $val[$key];
				$temp_array[$i] = $val;
			}
			$i++;
		}
		return $temp_array;
	}

	function tax_service_sum($markup_price_summary, $api_price_summary)
	{
		return (($api_price_summary['Tax'] + $markup_price_summary['PublishedPrice'] - $api_price_summary['PublishedPrice']));
	}

	public function convert_token_to_application_currency($token, $currency_obj, $module)
	{
		$master_token = array();
		$price_token = array();
		$price_summary = array();
		$markup_price_summary = array();
		// Price Token
		foreach ($token['price_token'] as $ptk => $ptv) {
			$price_token[$ptk] = $this->preferred_currency_fare_object($ptv, $currency_obj, admin_base_currency());
		}
		// Price Summary
		$price_summary = $this->preferred_currency_price_summary($token['price_summary'], $currency_obj);
		// Markup Price Summary
		$markup_price_summary = $this->preferred_currency_price_summary($token['markup_price_summary'], $currency_obj);
		// Assigning the Converted Data
		$master_token = $token;
		$master_token['price_token'] = $price_token;
		$master_token['price_summary'] = $price_summary;
		$master_token['markup_price_summary'] = $markup_price_summary;
		$master_token['default_currency'] = admin_base_currency();
		$master_token['convenience_fees'] = get_converted_currency_value($currency_obj->force_currency_conversion($token['convenience_fees'])); // check this
		return $master_token;
	}

	private function preferred_currency_price_summary($fare_details, $currency_obj)
	{
		$price_details = array();
		$price_details['RoomPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['RoomPrice']));
		$price_details['PublishedPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['PublishedPrice']));
		$price_details['PublishedPriceRoundedOff'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['PublishedPriceRoundedOff']));
		$price_details['OfferedPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['OfferedPrice']));
		$price_details['OfferedPriceRoundedOff'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['OfferedPriceRoundedOff']));
		$price_details['ServiceTax'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['ServiceTax']));
		$price_details['Tax'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['Tax']));
		$price_details['ExtraGuestCharge'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['ExtraGuestCharge']));
		$price_details['ChildCharge'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['ChildCharge']));
		$price_details['OtherCharges'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['OtherCharges']));
		$price_details['TDS'] = get_converted_currency_value($currency_obj->force_currency_conversion($fare_details['TDS']));
		return $price_details;
	}

	function save_booking($app_booking_id, $params, $module = 'b2c', $convenience_fees = '')
	{

		// Need to return following data as this is needed to save the booking fare in the transaction details
		$response['fare'] = $response['domain_markup'] = $response['level_one_markup'] = 0;

		$domain_origin = get_domain_auth_id();
		$master_search_id = $params['booking_params']['token']['search_id'];
		$search_data = $this->search_data($master_search_id);
		$app_reference = $app_booking_id;
		$booking_source = $params['booking_params']['token']['booking_source'];

		$currency_obj = $params['currency_obj'];
		$deduction_cur_obj = clone $currency_obj;
		$promo_currency_obj = @$params['promo_currency_obj'];
		// PREFERRED TRANSACTION CURRENCY AND CURRENCY CONVERSION RATE
		$transaction_currency = get_application_currency_preference();
		$application_currency = admin_base_currency();
		// $currency_conversion_rate = $currency_obj->transaction_currency_conversion_rate();
		$currency_conversion_rate = 1;
		if (isset($params['book_response'])) {
			$booking_id = $params['book_response']['BookResult']['BookingId'];
			$booking_reference = $params['book_response']['BookResult']['BookingRefNo'];

			$confirmation_reference = $params['book_response']['BookResult']['ConfirmationNo'];
			$status =  $params['book_response']['BookResult']['booking_status'];
		} else {

			$booking_id = $app_booking_id;
			$booking_reference = '';

			$confirmation_reference = '';
			$status =  'BOOKING_PENDING';
		}
		// $booking_id = $params['book_response']['BookResult']['BookingId'];
		// $booking_reference = $params['book_response']['BookResult']['BookingRefNo'];

		// $confirmation_reference = $params['book_response']['BookResult']['ConfirmationNo'];
		// $status = $params['book_response']['BookResult']['booking_status'];
		$no_of_nights = intval($search_data['data']['no_of_nights']);
		$HotelRoomsDetails = force_multple_data_format($params['room_book_data']['HotelRoomsDetails']);
		$total_room_count = count($HotelRoomsDetails);
		$book_total_fare = $params['booking_params']['token']['price_summary']['OfferedPriceRoundedOff']; // (TAX+ROOM PRICE)
		$room_price = $params['booking_params']['token']['price_summary']['RoomPrice'];

		if ($module == 'b2c') {
			$markup_total_fare = $currency_obj->get_currency($book_total_fare, true, false, true, $no_of_nights * $total_room_count, $search_data, $booking_source); // (ON Total PRICE ONLY)
			//debug($markup_total_fare);die;
			//$ded_total_fare = $deduction_cur_obj->get_currency ( $book_total_fare, true, true, false, $no_of_nights * $total_room_count ); // (ON Total PRICE ONLY)
			$admin_markup = $markup_total_fare;
			//$agent_markup = sprintf ( "%.2f", $ded_total_fare ['default_value'] - $book_total_fare );
			//$markup_total_fare = $currency_obj->get_currency($book_total_fare, true, false, true, $no_of_nights * $total_room_count); // (ON Total PRICE ONLY)
			//$ded_total_fare = $deduction_cur_obj->get_currency($book_total_fare, true, true, false, $no_of_nights * $total_room_count); // (ON Total PRICE ONLY)
			//$admin_markup = sprintf("%.2f", $markup_total_fare['default_value'] - $ded_total_fare['default_value']);
			//$agent_markup = sprintf("%.2f", $ded_total_fare['default_value'] - $book_total_fare);
		} else {
			// B2B Calculation
			$markup_total_fare = $currency_obj->get_currency($book_total_fare, true, true, false, $no_of_nights * $total_room_count); // (ON Total PRICE ONLY)
			$ded_total_fare = $deduction_cur_obj->get_currency($book_total_fare, true, false, true, $no_of_nights * $total_room_count); // (ON Total PRICE ONLY)
			$admin_markup = sprintf("%.2f", $markup_total_fare['default_value'] - $ded_total_fare['default_value']);
			$agent_markup = sprintf("%.2f", $ded_total_fare['default_value'] - $book_total_fare);
		}

//debug($markup_total_fare);die;
		$currency = $params['booking_params']['token']['default_currency'];
		$hotel_name = $params['booking_params']['token']['HotelName'];
		$star_rating = $params['booking_params']['token']['StarRating'];
		$hotel_code = '';
		$phone_number = $params['booking_params']['passenger_contact'];
		$phone_code = @$params['booking_params']['phone_country_code'];
		$alternate_number = 'NA';
		$email = $params['booking_params']['billing_email'];
		$hotel_check_in = db_current_datetime(str_replace('/', '-', $search_data['data']['from_date']));
		$hotel_check_out = db_current_datetime(str_replace('/', '-', $search_data['data']['to_date']));
		$payment_mode = $params['booking_params']['payment_method'];

		$alternate_hotel_room = $params['booking_params']['alternate_hotel_room'];

		$country_name = $GLOBALS['CI']->db_cache_api->get_country_list(
			array(
				'k' => 'origin',
				'v' => 'name'
			),
			array(
				'origin' => $params['booking_params']['billing_country']
			)
		);


		$attributes = array(
			'address' => @$params['booking_params']['billing_address_1'],
			'billing_country' => @$country_name[$params['booking_params']['billing_country']],
			'billing_city' => @$params['booking_params']['billing_city'],
			'billing_zipcode' => @$params['booking_params']['billing_zipcode'],
			'HotelCode' => @$params['booking_params']['token']['HotelCode'],
			'search_id' => @$params['booking_params']['token']['search_id'],
			'TraceId' => @$params['booking_params']['token']['TraceId'],
			'HotelName' => @$params['booking_params']['token']['HotelName'],
			'StarRating' => @$params['booking_params']['token']['StarRating'],
			'HotelImage' => @$params['booking_params']['token']['HotelImage'],
			'HotelAddress' => @$params['booking_params']['token']['HotelAddress'],
			'CancellationPolicy' => @$params['booking_params']['token']['TM_Cancellation_Charge'],
			'Boarding_details' => @$params['booking_params']['token']['Boarding_details']
		);
		$created_by_id = intval(@$GLOBALS['CI']->entity_user_id);
		// SAVE Booking details

		$supplier_id = $GLOBALS['CI']->custom_db->single_table_records("eco_stays", "host,check_in,check_out", array("origin" => $params['booking_params']['token']['HotelCode']));

		$supplier_id = ($supplier_id['data'][0]['host'] > 0) ? $supplier_id : 0;

		$supplier['supplier_price'] = $params['booking_params']['token']['token'][0]['SupplierOriginalPrice'];
		$supplier['supplier_currency'] = $params['booking_params']['token']['token'][0]['SupplierCurrencyCode'];
		$supplier['supplier_id'] = $supplier_id['data'][0]['host'];

		if ($params['booking_params']['payment_method'] == PAY_PART) {
			$part_pay = $params['partial_value'];
		} else {
			$part_pay = 0;
		}
		$check_in_time = $supplier_id['data'][0]['check_in'];
		$check_out_time = $supplier_id['data'][0]['check_out'];
		//debug($currency_conversion_rate);die;
		$GLOBALS['CI']->hotel_model->save_booking_details($domain_origin, $status, $app_reference, $booking_source, $booking_id, $booking_reference, $confirmation_reference, $hotel_name, $star_rating, $hotel_code, $phone_number, $alternate_number, $email, $hotel_check_in, $hotel_check_out, $check_in_time, $check_out_time, $payment_mode, json_encode($attributes), $created_by_id, $transaction_currency, $currency_conversion_rate, $phone_code, $supplier, $part_pay,$cityid='');
		// $alternate_hotel_room

		$check_in = db_current_datetime(str_replace('/', '-', $search_data['data']['from_date']));
		$check_out = db_current_datetime(str_replace('/', '-', $search_data['data']['to_date']));

		$location = $search_data['data']['location'];

		//$gst_details = $GLOBALS['CI']->custom_db->single_table_records('gst_master', '*', array('module' => 'hotel'));
		$gst_details = $GLOBALS['CI']->hotel_model->get_gst_details();
		$gst = 0;
		if ($gst_details['status'] == true) {
			if (floatval($gst_details['data'][0]['gst']) > 0) {
				$gst = floatval($gst_details['data'][0]['gst']);
			}
		}
		// debug($params);die;
		$booking_params = $params['booking_params'];
		$HotelRoomsDetails = $this->formate_hotel_room_details($booking_params);
		//debug($HotelRoomsDetails);
		// loop token of token

		foreach ($HotelRoomsDetails as $k => $v) {
			$room_type_name = $v['RoomTypeName'];
			$bed_type_code = $v['RoomTypeCode'];
			$smoking_preference = get_smoking_preference($v['SmokingPreference']);
			$smoking_preference = $smoking_preference['label'];
			$total_fare = $v['Price']['OfferedPriceRoundedOff'];
			$room_price = $v['Price']['RoomPrice'];
			$gst_value = 0;
			if ($module == 'b2c') {
				$markup_total_fare = $currency_obj->get_currency($total_fare, true, false, true, $no_of_nights, $search_data, $booking_source); // (ON Total PRICE ONLY)
				//debug($markup_total_fare);
				/*$admin_markup = $markup_total_fare['admin_markup']['original_markup'];*/ 
				//changed by avi
				$admin_markup = $markup_total_fare['original_markup'];
				$agent_markup = $markup_total_fare['agent_markup']['original_markup'];
				//adding gst
			//	debug($admin_markup);
				if ($admin_markup > 0) {
					$gst_details = $GLOBALS['CI']->hotel_model->get_gst_details();
					//$gst_details = $GLOBALS['CI']->custom_db->single_table_records('gst_master', '*', array('module' => 'hotel'));
					if ($gst_details['status'] == true) {
						if ($gst_details['data'][0]['gst'] > 0) {
							$gst_value = ($admin_markup / 100) * $gst_details['data'][0]['gst'];
							$gst_value = roundoff_number($gst_value);
						}
					}
				}
			} else {
				// B2B Calculation - Room wise price
				$markup_total_fare = $currency_obj->get_currency($total_fare, true, true, false, $no_of_nights, $search_data, $booking_source); // (ON Total PRICE ONLY)
				//debug($markup_total_fare);
				
				$admin_markup = $markup_total_fare['original_markup'];
				$agent_markup = $markup_total_fare['markup_value'];
				$markup = $admin_markup + $agent_markup;
				//adding gst
				if ($admin_markup > 0) {
					$gst_value = roundoff_number(($admin_markup / 100) * $gst);
				}
			}
			//echo $admin_markup;die;
			$total_fare_markup = round($book_total_fare + $admin_markup + $gst_value);
			$attributes = '';
			//echo $total_fare_markup."test";die;
			// SAVE Booking Itinerary details
			$GLOBALS['CI']->hotel_model->save_booking_itinerary_details($app_reference, $location, $check_in, $check_out, $room_type_name, $bed_type_code, $status, $smoking_preference, $total_fare, $admin_markup, $agent_markup, $currency, $attributes, @$v['RoomPrice'], @$v['Tax'], @$v['ExtraGuestCharge'], @$v['ChildCharge'], @$v['OtherCharges'], @$v['Discount'], @$v['ServiceTax'], @$v['AgentCommission'], @$v['AgentMarkUp'], @$v['TDS'], $gst_value);

			$passengers = force_multple_data_format($v['HotelPassenger']);
			// debug($passengers);die;
			if (valid_array($passengers) == true) {
				foreach ($passengers as $passenger) {
					$title = $passenger['Title'];
					$first_name = $passenger['FirstName'];
					$middle_name = $passenger['MiddleName'];
					$last_name = $passenger['LastName'];
					$pan_no = $passenger['PAN'];
					$phone = $passenger['Phoneno'];
					$email = $passenger['Email'];
					$pax_type = $passenger['PaxType'];
					$date_of_birth = array_shift($params['booking_params']['date_of_birth']); //

					$passenger_nationality_id = array_shift($params['booking_params']['passenger_nationality']); //
					$passport_issuing_country_id = array_shift($params['booking_params']['passenger_passport_issuing_country']); //

					$passenger_nationality = $GLOBALS['CI']->db_cache_api->get_country_list(
						array(
							'k' => 'origin',
							'v' => 'name'
						),
						array(
							'origin' => $passenger_nationality_id
						)
					);
					$passport_issuing_country = $GLOBALS['CI']->db_cache_api->get_country_list(
						array(
							'k' => 'origin',
							'v' => 'name'
						),
						array(
							'origin' => $passport_issuing_country_id
						)
					);

					$passenger_nationality = $passenger_nationality[$passenger_nationality_id];
					$passport_issuing_country = $passport_issuing_country[$passport_issuing_country_id];
					$passport_number = array_shift($params['booking_params']['passenger_passport_number']); //
					$passport_expiry_date = array_shift($params['booking_params']['passenger_passport_expiry_year']) . '-' . array_shift($params['booking_params']['passenger_passport_expiry_month']) . '-' . array_shift($params['booking_params']['passenger_passport_expiry_day']); //
					$attributes = array();

					// SAVE Booking Pax details
					$GLOBALS['CI']->hotel_model->save_booking_pax_details($app_reference, $title, $first_name, $middle_name, $last_name, $phone, $email, $pax_type, $date_of_birth, $passenger_nationality, $passport_number, $passport_issuing_country, $passport_expiry_date, $status, serialize($attributes),$gender='');
				}
			}
		}

		/**
		 * ************ Update Convinence Fees And Other Details Start *****************
		 */
		// Convinence_fees to be stored and discount
		$convinence = 0;
		$discount = 0;
		$convinence_value = 0;
		$convinence_type = 0;
		$convinence_per_pax = 0;
		if ($module == 'b2c') {
			//$convinence = $currency_obj->convenience_fees($total_fare_markup, $master_search_id);
			$convinence = $convenience_fees;
			$convinence_row = $currency_obj->get_convenience_fees();
			//$convinence_value = $convinence_row['value'];
			$convinence_value  = $convenience_fees;
			$convinence_type = $convinence_row['type'];
			$convinence_per_pax = $convinence_row['per_pax'];

			if ($params['booking_params']['promo_actual_value']) {
				$discount = get_converted_currency_value($currency_obj->force_currency_conversion($params['booking_params']['promo_actual_value']));
			}
			$promo_code = @$params['booking_params']['promo_code'];
		} elseif ($module == 'b2b') {
			$discount = 0;
		}

		$GLOBALS['CI']->load->model('transaction');

		// SAVE Booking convinence_discount_details details
		$GLOBALS['CI']->transaction->update_convinence_discount_details('hotel_booking_details', $app_reference, $discount, $promo_code, $convinence, $convinence_value, $convinence_type, $convinence_per_pax);
		/**
		 * ************ Update Convinence Fees And Other Details End *****************
		 */

		$response['fare'] = $book_total_fare;
		$response['admin_markup'] = $admin_markup;
		$response['agent_markup'] = $agent_markup;
		$response['convinence'] = $convinence;
		$response['discount'] = $discount;
		$response['phone'] = $params['booking_params']['passenger_contact'];
		$response['transaction_currency'] = $transaction_currency;
		$response['currency_conversion_rate'] = $currency_conversion_rate;
		//booking_status
		$response['booking_status'] = $status;
		return $response;
	}

	function get_page_data($hl, $offset, $limit)
	{
		$hl['HotelSearchResult']['HotelResults'] = array_slice($hl['HotelSearchResult']['HotelResults'], $offset, $limit);
		return $hl;
	}

	public function update_booking_details($book_id, $booking_source, $book_params, $ticket_details, $currency_obj, $module = 'b2c')
	{

		$booking_status = $book_params['book_response']['CommitBooking']['BookingDetails']['booking_status'];
		$app_reference = $book_id;
		$master_search_id = $book_params['booking_params']['token']['search_id'];
		//Setting Master Booking Status
		//$master_transaction_status = $this->status_code_value($booking_status);
		$master_transaction_status = $booking_status;
		// debug($master_transaction_status);die;
		$saved_booking_data = $GLOBALS['CI']->hotel_model->get_booking_details($book_id, $booking_source);
		//debug($saved_booking_data);exit;
		// if ($saved_booking_data['status'] == false) {
		//     $response['status'] = BOOKING_ERROR;
		//     $response['msg'] = 'No Data Found';
		//     return $response;
		// }
		$s_master_data = $saved_booking_data['data']['booking_details'][0];
		$s_booking_itinerary_details = $saved_booking_data['data']['booking_itinerary_details'];
		$s_booking_customer_details = $saved_booking_data['data']['booking_customer_details'];
		$passenger_origins = group_array_column($s_booking_customer_details, 'origin');
		$itinerary_origins = group_array_column($s_booking_itinerary_details, 'origin');
		$hotel_master_booking_status = $master_transaction_status;
		$transaction_currency = get_application_currency_preference();
		$application_currency = admin_base_currency();
		$currency_conversion_rate = $currency_obj->transaction_currency_conversion_rate();
		$booking_id = $book_params['book_response']['BookResult']['BookingId'];
		$BookingRefNo = $book_params['book_response']['BookResult']['BookingRefNo'];
		$ConfirmationNo = $book_params['book_response']['BookResult']['ConfirmationNo'];
		$GLOBALS['CI']->custom_db->update_record('hotel_booking_details', array('status' => $master_transaction_status, 'booking_id' => $booking_id, 'booking_reference' => $BookingRefNo, 'confirmation_reference' => $ConfirmationNo), array('app_reference' => $app_reference));
		//debug('hiiiiiiii');exit;
		$total_pax_count = count($book_params['booking_params']['passenger_type']);
		$pax_count = $total_pax_count;
		$search_data = $this->search_data($master_search_id);
		$no_of_nights = intval($search_data['data']['no_of_nights']);
		$HotelRoomsDetails = force_multple_data_format($book_params['booking_params']['token']['token']);
		$total_room_count = count($HotelRoomsDetails);
		//debug($total_room_count);exit;
		$book_total_fare = $book_params['booking_params']['token']['price_token'][0]['OfferedPriceRoundedOff']; // (TAX+ROOM PRICE)
		$room_price = $book_params['booking_params']['token']['price_token'][0]['RoomPrice'];
		$deduction_cur_obj = clone $currency_obj;

		if ($module == 'b2c') {
			$markup_total_fare = $currency_obj->get_currency($book_total_fare, true, false, true, $no_of_nights * $total_room_count); // (ON Total PRICE ONLY)
			$ded_total_fare = $deduction_cur_obj->get_currency($book_total_fare, true, true, false, $no_of_nights * $total_room_count); // (ON Total PRICE ONLY)
			$admin_markup = sprintf("%.2f", $markup_total_fare['default_value'] - $ded_total_fare['default_value']);
			$agent_markup = sprintf("%.2f", $ded_total_fare['default_value'] - $book_total_fare);
		} else {
			// B2B Calculation
			$markup_total_fare = $currency_obj->get_currency($book_total_fare, true, true, false, $no_of_nights * $total_room_count); // (ON Total PRICE ONLY)
			$ded_total_fare = $deduction_cur_obj->get_currency($book_total_fare, true, false, true, $no_of_nights * $total_room_count); // (ON Total PRICE ONLY)
			$admin_markup = sprintf("%.2f", $markup_total_fare['default_value'] - $ded_total_fare['default_value']);
			$agent_markup = sprintf("%.2f", $ded_total_fare['default_value'] - $book_total_fare);
		}
		//debug($markup_total_fare);exit;
		foreach ($HotelRoomsDetails as $k => $v) {
			$room_type_name = $v['RoomTypeName'];
			$bed_type_code = $v['RoomTypeCode'];
			$smoking_preference = get_smoking_preference($v['SmokingPreference']);
			$smoking_preference = $smoking_preference['label'];
			$total_fare = $v['OfferedPriceRoundedOff'];
			$room_price = $v['RoomPrice'];
			$gst_value = 0;
			if ($module == 'b2c') {
				$markup_total_fare = $currency_obj->get_currency($total_fare, true, false, true, $no_of_nights); // (ON Total PRICE ONLY)
				$ded_total_fare = $deduction_cur_obj->get_currency($total_fare, true, true, false, $no_of_nights); // (ON Total PRICE ONLY)
				$admin_markup = sprintf("%.2f", $markup_total_fare['default_value'] - $ded_total_fare['default_value']);
				$agent_markup = sprintf("%.2f", $ded_total_fare['default_value'] - $total_fare);
				//adding gst
				if ($admin_markup > 0) {
					$gst_details = $GLOBALS['CI']->hotel_model->get_gst_details();
					//$gst_details = $GLOBALS['CI']->custom_db->single_table_records('gst_master', '*', array('module' => 'hotel'));
					if ($gst_details['status'] == true) {
						if ($gst_details['data'][0]['gst'] > 0) {
							$gst_value = ($admin_markup / 100) * $gst_details['data'][0]['gst'];
							$gst_value  = roundoff_number($gst_value);
						}
					}
				}
			} else {
				// B2B Calculation - Room wise price
				//echo 'total_fare',debug($total_fare);
				$markup_total_fare = $currency_obj->get_currency($total_fare, true, true, false, $no_of_nights); // (ON Total PRICE ONLY)
				$ded_total_fare = $deduction_cur_obj->get_currency(($markup_total_fare['default_value']), true, false, true, $no_of_nights); // (ON Total PRICE ONLY)
				$admin_markup = sprintf("%.2f", $markup_total_fare['default_value'] -  $total_fare);
				$agent_markup = sprintf("%.2f", $ded_total_fare['default_value'] - $markup_total_fare['default_value']);
				$markup = $admin_markup + $agent_markup;
				//adding gst
				if ($markup > 0) {
					$gst_details = $GLOBALS['CI']->hotel_model->get_gst_details();
					//$gst_details = $GLOBALS['CI']->custom_db->single_table_records('gst_master', '*', array('module' => 'hotel'));
					if ($gst_details['status'] == true) {
						if ($gst_details['data'][0]['gst'] > 0) {
							$gst_value = ($markup / 100) * $gst_details['data'][0]['gst'];
							$gst_value  = roundoff_number($gst_value);
						}
					}
				}
			}
			$total_fare_markup = round($book_total_fare + $admin_markup + $gst_value);
			$GLOBALS['CI']->custom_db->update_record('hotel_booking_itinerary_details', array('status' => $master_transaction_status), array('app_reference' => $app_reference));

			$GLOBALS['CI']->custom_db->update_record('hotel_booking_pax_details', array('status' => $master_transaction_status), array('app_reference' => $app_reference));
		}
		$convinence = 0;
		$discount = 0;
		$convinence_value = 0;
		$convinence_type = 0;
		$convinence_per_pax = 0;
		if ($module == 'b2c') {
			$convinence = $currency_obj->convenience_fees($total_fare_markup, $master_search_id);
			$convinence_row = $currency_obj->get_convenience_fees();
			$convinence_value = $convinence_row['value'];
			$convinence_type = $convinence_row['type'];
			$convinence_per_pax = $convinence_row['per_pax'];
			// if($book_params['booking_params']['promo_actual_value']){
			// 	$discount = get_converted_currency_value ( $promo_currency_obj->force_currency_conversion ( $book_params['booking_params']['promo_actual_value']) );
			// }			
			$discount = @$book_params['promo_actual_value'];
			$discount = floatval(preg_replace('/[^\d.]/', '', $discount));
			$promo_code = @$book_params['promo_code'];
		} elseif ($module == 'b2b') {
			$discount = 0;
		}
		$response['fare'] = $book_total_fare;
		$response['admin_markup'] = $admin_markup;
		$response['agent_markup'] = $agent_markup;
		$response['convinence'] = $convinence;
		$response['discount'] = $discount;
		$response['transaction_currency'] = $transaction_currency;
		$response['currency_conversion_rate'] = $currency_conversion_rate;
		//booking_status
		$response['booking_status'] = $master_transaction_status;
		return $response;
	}
	private function status_code_value($status_code)
	{
		//debug($status_code);die;
		switch ($status_code) {
			case BOOKING_CONFIRMED:
			case SUCCESS_STATUS:
				$status_value = 'BOOKING_CONFIRMED';
				break;
			case BOOKING_HOLD:
				$status_value = 'BOOKING_HOLD';
				break;
			default:
				$status_value = 'BOOKING_FAILED';
				break;
		}
		return $status_value;
	}

	function convenience_fees($total_price, $search_id, $pre_booking_params)
	{
		$Markup_id = $pre_booking_params['_MARKID'];
		$query = "select * from markup_setup as MS INNER JOIN markup_hotel as MH ON MS.system_id=MH.system_id where MS.system_id='{$Markup_id}' ORDER BY MS.priority ASC";
		$result = $GLOBALS['CI']->db->query($query)->result_array();

		$convenience_details = array();
		foreach ($result as $key1 => $result_val) {
			if ($result_val['conv_type'] == 1) {
				$convenience_details['value_type'] = 'plus';
				$convenience_details['value'] = $result_val['conv_value'];
			} else {
				$convenience_details['value_type'] = 'percentage';
				$convenience_details['value'] = $total_price * ($result_val['conv_value'] / 100);
			}
			break;
		}
		// debug($convenience_details);die;
		return $convenience_details['value'];
	}
	function cancel_booking($booking_details)
	{
		$response['data'] = array();
		$response['status'] = TRUE;
		$resposne['msg'] = 'Remote IO Error';
		$BookingId = $booking_details['booking_id'];
		$app_reference = $booking_details['app_reference'];

		//create random id
		$random_number = rand(99999, 1000000);
		$cancel_booking_request['status'] = true;
		$cancel_booking_request['AppReference'] = trim($app_reference);
		$cancel_booking_request['ChangeRequestId'] = $random_number;
		$check_cancel = json_decode($booking_details['attributes'], true);

		if ($cancel_booking_request['status'] == true) {


			// 1.SendChangeRequest
			$GLOBALS['CI']->custom_db->generate_static_response(json_encode($cancel_booking_request));

			$cancel_booking_response['ChangeRequestId'] = $random_number;
			if ($booking_details['status'] == 'PARTIAL_PAID') {
				$update_status['status'] = 0;
				$GLOBALS['CI']->db->where('app_reference', $booking_details['app_reference']);
				$GLOBALS['CI']->db->update('sys_scheduler', $update_status);
			}
			$cur_date = date("Y-m-d");

			foreach ($check_cancel['CancellationPolicy'] as $id => $check_policy) {
				if (strtotime($cur_date) > strtotime($check_policy['from_date']) && strtotime($cur_date) < strtotime($check_policy['from_date'])) {
					$type = $check_policy['type'];
					$amount = $check_policy['amount'];
					break;
				} else {
					$type = 'percentage';
					$amount = 100;
					break;
				}
			}

			if ($type == 'plus') {
				$refundable_amount = $booking_details['grand_total'] - $amount;
				$cancel_charge = $amount;
			} elseif ($type == 'percentage') {
				$refundable_amount = $booking_details['grand_total'] -  (($booking_details['grand_total'] / 100) * $amount);
				$cancel_charge = $amount . '%';
			}
			$cancel_booking_response['CancelBooking']['CancellationDetails']['ChangeRequestId'] = $random_number;
			$cancel_booking_response['CancelBooking']['CancellationDetails']['ChangeRequestStatus'] = 3;
			$cancel_booking_response['CancelBooking']['CancellationDetails']['StatusDescription'] = 'CANCELLED';
			$cancel_booking_response['CancelBooking']['CancellationDetails']['RefundedAmount'] = $refundable_amount;
			$cancel_booking_response['CancelBooking']['CancellationDetails']['CancellationCharge'] = $cancel_charge;
			$GLOBALS['CI']->custom_db->generate_static_response(json_encode($cancel_booking_response));
			$cancel_booking_response['Status'] = true;
			// $cancel_booking_response = $GLOBALS['CI']->hotel_model->get_static_response(3317);
			if (valid_array($cancel_booking_response) == true && $cancel_booking_response['Status'] == SUCCESS_STATUS) {

				// Save Cancellation Details
				$hotel_cancellation_details = $cancel_booking_response['CancelBooking']['CancellationDetails'];

				$GLOBALS['CI']->hotel_model->update_cancellation_details($app_reference, $hotel_cancellation_details);
				$response['status'] = SUCCESS_STATUS;
			} else {
				$response['msg'] = $cancel_booking_response['Message'];
			}
		}

		return $response;
	}
}
