<?php

/**
 * Combines the Data from multiple API's
 * @author Jaganath
 *
 */
class hotel_Blender_v3
{
	var $master_search_data;
	function __construct()
	{
		$this->CI = &get_instance();
		$this->CI->load->library('multi_curl');
		$this->CI->load->library('hotel/GRN/Common_hotel_v3');
	}
	/**
	 * Assigns the Curl Parameters (URL, Header info, Request)
	 *
	 * @param array $request_params
	 * @param array $curl_request
	 * @param array $curl_url
	 * @param array $curl_header
	 * @param array $curl_booking_source
	 * @param array $curl_remarks
	 * @param array $curl_keyid
	 * @param array $curl_apikey
	 * @return void
	 */
	public function assign_curl_params(
		array $request_params,
		array &$curl_request,
		array &$curl_url,
		array &$curl_header,
		array &$curl_booking_source,
		array &$curl_remarks,
		array &$curl_keyid,
		array &$curl_apikey
	): void {
		$request = [$request_params['request']];
		$url = [$request_params['url']];
		$header = [$request_params['header']];
		$booking_source = [$request_params['booking_source']];
		$remarks = isset($request_params['remarks']) ? [trim($request_params['remarks'])] : [''];

		$KeyId = isset($request_params['KeyId']) ? [trim($request_params['KeyId'])] : [''];
		$APIKey = isset($request_params['APIKey']) ? [trim($request_params['APIKey'])] : [''];

		$curl_request = array_merge($curl_request, $request);
		$curl_url = array_merge($curl_url, $url);
		$curl_header = array_merge($curl_header, $header);
		$curl_booking_source = array_merge($curl_booking_source, $booking_source);
		$curl_remarks = array_merge($curl_remarks, $remarks);
		$curl_keyid = array_merge($curl_keyid, $KeyId);
		$curl_apikey = array_merge($curl_apikey, $APIKey);
	}
	/**
	 * Get active hotel booking sources
	 *
	 * @param array $condition
	 * @return array
	 */
	private function hotel_active_booking_sources(array $condition = []): array
	{
		$active_booking_source_condition = [];
		$active_booking_source_condition[] = ['BS.meta_course_list_id', '=', '"' . META_ACCOMODATION_COURSE . '"'];
		$active_booking_source_condition[] = ['DL.origin', '=', get_domain_auth_id()];
		$active_booking_source_condition = array_merge($active_booking_source_condition, $condition);

		$active_booking_sources = $this->CI->db_cache_api->get_active_api_booking_source($active_booking_source_condition);

		return $active_booking_sources;
	}
	/**
	 * Authenticates the API for active hotel booking sources
	 * 
	 * FIXME: Check for other services (other than search)
	 *
	 * @param array $active_booking_sources
	 * @return void
	 */
	private function api_authentication(array $active_booking_sources = []): void
	{
		$curl_params = [
			'booking_source' => [],
			'request' => [],
			'url' => [],
			'header' => [],
			'remarks' => []
		];

		$curl_request = [];
		$curl_url = [];
		$curl_header = [];
		$curl_booking_source = [];
		$curl_remarks = [];

		$hotel_active_booking_sources = !empty($active_booking_sources)
			? $active_booking_sources
			: $this->hotel_active_booking_sources();

		$hotel_obj = [];

		foreach ($hotel_active_booking_sources as $bs_k => $bs_v) {
			if ((int)($bs_v['check_auth'] ?? 0) == 1) {
				$hotel_obj[$bs_k] = load_hotel_lib_v3($bs_v['booking_source'], '', true);

				// Authentication Request
				$authentication_request = $this->CI->{$hotel_obj[$bs_k]}->get_authentication_request();

				if (
					empty($this->CI->{$hotel_obj[$bs_k]}->api_session_id) &&
					($authentication_request['status'] ?? '') == SUCCESS_STATUS
				) {
					$authentication_request['data']['remarks'] = $this->CI->{$hotel_obj[$bs_k]}->booking_source_name;

					$this->assign_curl_params(
						$authentication_request['data'],
						$curl_request,
						$curl_url,
						$curl_header,
						$curl_booking_source,
						$curl_remarks,
						$unused_keyid = [],
						$unused_apikey = []
					);
				}
			}
		}

		$curl_params['booking_source'] = $curl_booking_source;
		$curl_params['request'] = $curl_request;
		$curl_params['url'] = $curl_url;
		$curl_params['header'] = $curl_header;
		$curl_params['remarks'] = $curl_remarks;

		$authentication_result = [];

		if (!empty($curl_remarks)) {
			$authentication_result = $this->CI->multi_curl->execute_multi_curl_hotel($curl_params);
		}

		foreach ($hotel_obj as $obj_k => $obj_v) {
			if (!empty($authentication_result)) {
				$source = $this->CI->{$obj_v}->booking_source;
				if (isset($authentication_result[$source])) {
					$this->CI->{$obj_v}->set_api_session_id($authentication_result[$source]);
				}
			}
		}
	}
	/**
	 * Returns hotel list
	 *
	 * @param int $search_id
	 * @param string $cache_key
	 * @return array
	 */
	public function hotel_list(int $search_id, string $cache_key): array
	{
		$curl_params = $curl_request = $curl_url = $curl_header = $curl_booking_source = $curl_remarks = $curl_keyid = $curl_apikey = [];

		$seach_result = $formatted_seach_result = [];
		$final_hotel_list = ['status' => FAILURE_STATUS];

		$search_data = $this->search_data($search_id);
		$hotel_active_booking_sources = $this->hotel_active_booking_sources();
		$this->api_authentication();

		$hotel_obj = [];

		foreach ($hotel_active_booking_sources as $bs_v) {
			$booking_source = $bs_v['booking_source'];
			$hotel_obj_ref = load_hotel_lib_v3($booking_source, '', true);
			$hotel_obj[$booking_source] = $hotel_obj_ref;

			$search_request = $this->CI->{$hotel_obj_ref}->get_search_request($search_id);
			if (($search_request['status'] ?? '') == SUCCESS_STATUS) {
				$search_request['data']['remarks'] = $hotel_obj_ref;
				$this->assign_curl_params(
					$search_request['data'],
					$curl_request,
					$curl_url,
					$curl_header,
					$curl_booking_source,
					$curl_remarks,
					$curl_keyid,
					$curl_apikey
				);
			}
		}
		$curl_params['booking_source'] = $curl_booking_source;
		$curl_params['request'] = $curl_request;
		$curl_params['url'] = $curl_url;
		$curl_params['header'] = $curl_header;
		$curl_params['remarks'] = $curl_remarks;
		$curl_params1 = $curl_params;
		$seq_key = 0;
		// debug($curl_params1);exit;
		foreach ($curl_params1['booking_source'] as $key => $details) {
			$curl_params['booking_source'][$seq_key] = $details;
			$curl_params['request'][$seq_key] = $curl_params1['request'][$key];
			$curl_params['url'][$seq_key] = $curl_params1['url'][$key];
			$curl_params['header'][$seq_key] = $curl_params1['header'][$key];
			$curl_params['remarks'][$seq_key] = $curl_params1['remarks'][$key];
			
			$seq_key++;
		}
		$seach_result_array[] = $this->CI->multi_curl->execute_multi_curl_hotel($curl_params, false);
		foreach ($seach_result_array as $key => $seach_result_data) {

			foreach ($seach_result_data as $key1 => $value1) {
				
					$seach_result[$key1] = $value1[0];
				
			}
		}
	
		$hotel_grn_data = array();

		//Format the hotel List

		foreach ($hotel_obj as $fo_k => $fo_v) {

			if (isset($seach_result[$fo_k]) == true || isset($seach_result[0][$fo_k]) == true) {

				if (isset($seach_result[0][$fo_k]) == true) {
					$seach_result[$fo_k] = $seach_result[0][$fo_k];
				}

				//debug($seach_result);exit;
				$hotel_data = $this->CI->$fo_v->get_hotel_list($seach_result[$fo_k], $search_id);


				//debug($hotel_data);exit;
				//debug($hotel_data);exit;
				// echo "--------";
				if ($hotel_data['status'] == SUCCESS_STATUS && valid_array($hotel_data['data']['HotelSearchResult']['HotelResults']) == true) {

					//Merge hotel List
					if (valid_array($formatted_seach_result) == false) { //Assiging the hotel data, if not set(only for first API data, for next API's, it will be merged)

						$formatted_seach_result = $hotel_data;
					} else {
						//$formatted_seach_result['data']['HotelSearchResult']['HotelResults'] =array_merge($hotel_data['data']['HotelSearchResult']['HotelResults'], $formatted_seach_result['data']['HotelSearchResult']['HotelResults']);
						$formatted_seach_result['data']['HotelSearchResult']['HotelResults'] = array_merge($formatted_seach_result['data']['HotelSearchResult']['HotelResults'], $hotel_data['data']['HotelSearchResult']['HotelResults']);

						$formatted_seach_result['status'] = SUCCESS_STATUS;
					}
				}
			}
		}

		if (isset($formatted_seach_result['status']) == true && $formatted_seach_result['status'] == SUCCESS_STATUS) {

			//if(count($hotel_active_booking_sources) > 1){
			if (isset($formatted_seach_result['data']['HotelSearchResult']['HotelResults']['status'])) {
				unset($formatted_seach_result['data']['HotelSearchResult']['HotelResults']['status']);
			}
			$HotelList = array();
			$HotelList[0] = $formatted_seach_result['data']['HotelSearchResult']['HotelResults'];
			// debug($HotelList);
			//Eliminate Duplicate Flights
			$HotelList = $this->eliminate_duplicate_hotels($HotelList);
			// debug($HotelList);exit;
			//Sort based on price
			$HotelList = $this->sort_hotel_list($HotelList);

			$formatted_seach_result['data']['HotelSearchResult']['HotelResults'] = $HotelList[0];
			//}
			$carry_cache_key = $cache_key;
			$this->CI->load->library('hotel/GRN/common_hotel_v3');
			// debug($formatted_seach_result);exit;
			$formatted_seach_result['data']['HotelSearchResult']['HotelResults'] = $this->CI->common_hotel_v3->update_markup_and_insert_cache_key_to_token($formatted_seach_result['data']['HotelSearchResult']['HotelResults'], $carry_cache_key, $search_id, false);
			#exit;

			$final_hotel_list = $formatted_seach_result;
		} else {
			$final_hotel_list['message'] = 'No Hotels Found';
		}


		// echo "Elavarasi";
		//exit;

		return $final_hotel_list;
	}
	/**
	 * Eliminates duplicate hotels based on name and lowest price
	 *
	 * @param array $HotelList
	 * @return array
	 */
	private function eliminate_duplicate_hotels(array $HotelList): array
	{
		$new_hotel_list = [];

		foreach ($HotelList as $hl_k => $hl_v) {
			$hotel_data = [];

			foreach ($hl_v as $row_v) {
				$hotel_name_key = strtolower($row_v['HotelName']);
				$current_price = floatval($row_v['Price']['RoomPrice']);

				if (!empty($hotel_data[$hotel_name_key])) {
					$existing_price = floatval($hotel_data[$hotel_name_key]['Price']['RoomPrice']);

					if ($current_price < $existing_price) {
						$hotel_data[$hotel_name_key] = $row_v;
					}
				} else {
					$hotel_data[$hotel_name_key] = $row_v;
				}
			}

			$new_hotel_list[$hl_k] = $hotel_data;
		}

		// Re-index results
		$final_hotel_list = [];

		foreach ($new_hotel_list as $nhl_k => $nhl_v) {
			$final_hotel_list[$nhl_k] = array_values($nhl_v);
		}

		return $final_hotel_list;
	}
	/**
	 * Sorts hotels by star rating in descending order
	 *
	 * @param array $HotelList
	 * @return array
	 */
	private function sort_hotel_list(array $HotelList): array
	{
		$sorted_hotel_list = [];

		foreach ($HotelList as $list_key => $hotels) {
			$star_ratings = [];

			foreach ($hotels as $index => $hotel) {
				$star_ratings[$index] = (int) ($hotel['StarRating'] ?? 0);
			}

			array_multisort($star_ratings, SORT_DESC, $hotels);

			$sorted_hotel_list[$list_key] = $hotels;
		}

		return $sorted_hotel_list;
	}
	/**
	 * Retrieves room list based on the search and request data.
	 *
	 * @param array $request The room request data.
	 * @param int $search_id The search ID.
	 * @param string $cache_key The cache key for storing room data.
	 * @return array The room list data with status and message.
	 */
	public function room_list(array $request, int $search_id, string $cache_key): array
	{
		$room_list = [
			'data' => [],
			'status' => FAILURE_STATUS,
			'message' => ''
		];

		// Ensure ResultToken is provided in the request
		$result_token = trim($request['ResultToken']);
		$hotel_search_data = Common_Hotel_v3::read_record($result_token);

		if (is_array($hotel_search_data) && !empty($hotel_search_data)) {
			$hotel_search_data = json_decode($hotel_search_data[0], true);
			$room_list_request = array_values(unserialized_data($hotel_search_data['ResultToken']))[0];
			$booking_source = $room_list_request['booking_source'];

			$active_booking_source_condition = [
				['BS.source_id', '=', '"' . $booking_source . '"']
			];
			$hotel_active_booking_sources = $this->hotel_active_booking_sources($active_booking_source_condition);

			// Authenticate the APIs
			$this->api_authentication($hotel_active_booking_sources);

			$hotel_obj_ref = load_hotel_lib_v3($booking_source);
			$room_list_data = $this->CI->$hotel_obj_ref->get_room_list($room_list_request, $search_id);

			if ($room_list_data['status'] == SUCCESS_STATUS) {
				$this->CI->load->library('hotel/GRN/common_hotel_v3');
				$room_list['status'] = SUCCESS_STATUS;
				$room_list['data'] = $room_list_data['data'];
				$room_list['data']['GetHotelRoomResult']['HotelRoomsDetails'] = $this->CI->common_hotel_v3->cache_room_list(
					$room_list_data['data']['GetHotelRoomResult']['HotelRoomsDetails'],
					$cache_key,
					$search_id
				);
			} else {
				$room_list['message'] = $room_list_data['message'];
			}
		} else {
			$room_list['message'] = 'Invalid RoomList Request';
		}

		return $room_list;
	}
	/**
	 * Retrieves hotel details based on the request and search ID.
	 *
	 * @param array $request The hotel request data.
	 * @param int $search_id The search ID.
	 * @param string $cache_key The cache key for storing hotel details.
	 * @return array The hotel details with status and message.
	 */
	public function hotel_details(array $request, int $search_id, string $cache_key): array
	{
		// Initialize the hotel details response structure
		$hotel_details = [
			'data' => [],
			'status' => FAILURE_STATUS,
			'message' => ''
		];

		// Validate and extract the ResultToken from the request
		$result_token = trim($request['ResultToken']);
		$hotel_search_data = Common_Hotel_v3::read_record($result_token);

		if (is_array($hotel_search_data) && !empty($hotel_search_data)) {
			// Decode the search data
			$hotel_search_data = json_decode($hotel_search_data[0], true);
			$hotel_details_request = array_values(unserialized_data($hotel_search_data['ResultToken']))[0];
			$booking_source = $hotel_details_request['booking_source'];

			// Define condition for active booking source
			$active_booking_source_condition = [
				['BS.source_id', '=', '"' . $booking_source . '"']
			];
			$hotel_active_booking_sources = $this->hotel_active_booking_sources($active_booking_source_condition);

			// Authenticate the APIs for the booking source
			$this->api_authentication($hotel_active_booking_sources);

			// Load the hotel library and fetch hotel details
			$hotel_obj_ref = load_hotel_lib_v3($booking_source);
			$hotel_details_data = $this->CI->$hotel_obj_ref->get_hotel_details($hotel_details_request, $search_id);

			// Format first room response if available
			$this->CI->load->library('hotel/GRN/common_hotel_v3');
			if (isset($hotel_details_data['data']['HotelInfoResult']['HotelDetails']['first_room_details']['Price'])) {
				$price = $this->CI->common_hotel_v3->update_hotel_details_markup(
					$hotel_details_data['data']['HotelInfoResult']['HotelDetails']['first_room_details']['Price'],
					GRN_CONNECT_HOTEL_BOOKING_SOURCE,
					$search_id
				);

				// Update room price and unique ID
				$hotel_details_data['data']['HotelInfoResult']['HotelDetails']['first_room_details']['Price'] = $price;
				$unique_key = $this->CI->common_hotel_v3->update_hotel_first_room_unique(
					$hotel_details_data['data']['HotelInfoResult']['HotelDetails']['first_room_details'],
					$cache_key,
					$search_id
				);
				$hotel_details_data['data']['HotelInfoResult']['HotelDetails']['first_room_details']['Room_data']['RoomUniqueId'] = $unique_key;
			}

			// Check if hotel details data was successfully retrieved
			if ($hotel_details_data['status'] == SUCCESS_STATUS) {
				$hotel_details['status'] = SUCCESS_STATUS;
				$hotel_details['data'] = $hotel_details_data['data'];
			} else {
				$hotel_details['message'] = $hotel_details_data['message'];
			}
		} else {
			$hotel_details['message'] = 'Invalid HotelDetails Request';
		}

		return $hotel_details;
	}
	/**
	 * Blocks a room based on the request and search ID.
	 *
	 * @param array $request The room blocking request data.
	 * @param int $search_id The search ID.
	 * @param string $cache_key The cache key for storing room block details.
	 * @return array The room block result with status and message.
	 */
	public function block_room(array $request, int $search_id, string $cache_key): array
	{
		// Initialize the block room response structure
		$block_room = [
			'data' => [],
			'status' => FAILURE_STATUS,
			'message' => ''
		];

		// Extract and validate ResultToken and RoomUniqueId from the request
		$result_token = trim($request['ResultToken']);
		$room_unique_id = $request['RoomUniqueId'];
		$hotel_search_data = Common_Hotel_v3::read_record($result_token);

		// Check if the hotel search data is valid
		if (is_array($hotel_search_data) && !empty($hotel_search_data)) {
			// Decode the search data
			$hotel_search_data = json_decode($hotel_search_data[0], true);
			$block_room_request = array_values(unserialized_data($hotel_search_data['ResultToken']))[0];
			$block_room_request['RoomUniqueId'] = $room_unique_id;
			$booking_source = $block_room_request['booking_source'];

			// Define condition for active booking source
			$active_booking_source_condition = [
				['BS.source_id', '=', '"' . $booking_source . '"']
			];
			$hotel_active_booking_sources = $this->hotel_active_booking_sources($active_booking_source_condition);

			// Authenticate the APIs for the booking source
			$this->api_authentication($hotel_active_booking_sources);

			// Load the hotel library and request room block
			$hotel_obj_ref = load_hotel_lib_v3($booking_source);
			$block_room_data = $this->CI->$hotel_obj_ref->block_room($block_room_request, $search_id);

			// Handle the response for successful room block
			if ($block_room_data['status'] == SUCCESS_STATUS) {
				$carry_cache_key = $cache_key;
				$this->CI->load->library('hotel/GRN/common_hotel_v3');

				$block_room['status'] = SUCCESS_STATUS;
				$block_room['data'] = $block_room_data['data'];

				// Cache the blocked room data
				$block_room['data']['BlockRoomResult'] = $this->CI->common_hotel_v3->cache_block_room_data(
					$block_room_data['data']['BlockRoomResult'],
					$carry_cache_key,
					$search_id
				);
			} else {
				// If block room fails, return the failure message
				$block_room['message'] = $block_room_data['message'];
			}
		} else {
			// If invalid search data, return error message
			$block_room['message'] = 'Invalid BlockRoom Request';
		}

		return $block_room;
	}
	/**
	 * Process the hotel booking.
	 *
	 * @param array $request
	 * @param string $search_id
	 * @param string $cache_key
	 * @return array{status: string, message: string, data: array}
	 */
	public function process_booking(array $request, string $search_id, string $cache_key): array
	{
		$booking_response = [
			'data' => [],
			'status' => FAILURE_STATUS,
			'message' => '',
		];

		$ResultToken = trim($request['ResultToken'] ?? '');
		$BlockRoomId = trim($request['BlockRoomId'] ?? '');

		$search_data = $this->search_data($search_id)['data'] ?? [];

		$hotel_data_raw = Common_Hotel_v3::read_record($ResultToken);
		$room_data_raw = Common_Hotel_v3::read_record($BlockRoomId);

		if (valid_array($hotel_data_raw) && valid_array($room_data_raw)) {
			$this->CI->load->library('hotel/GRN/common_hotel_v3');

			$hotel_data = json_decode($hotel_data_raw[0], true);
			$ResultTokenData = array_values(unserialized_data($hotel_data['ResultToken'] ?? ''))[0] ?? [];
			$booking_source = $ResultTokenData['booking_source'] ?? '';

			$room_data = json_decode($room_data_raw[0], true);
			$room_data_unserialized = array_values(unserialized_data($room_data['BlockRoomId'] ?? ''))[0] ?? [];

			// Fetch pricing details
			$hotel_price_details = $this->CI->common_hotel_v3->final_booking_transaction_fare_details(
				$room_data_unserialized['HotelRoomsDetails'] ?? [],
				$search_id,
				$booking_source
			);

			$hotel_data['ResultToken'] = $ResultTokenData;
			$hotel_data['Price'] = $hotel_price_details['Price'];
			$hotel_data['RoomPriceBreakup'] = $hotel_price_details['RoomPriceBreakup'];

			$booking_transaction_amount = $hotel_data['Price']['client_buying_price'] ?? 0;

			if (
				$this->CI->domain_management->verify_domain_balance(
					$booking_transaction_amount,
					hotel_v3::get_credential_type()
				) == SUCCESS_STATUS
			) {
				$app_reference = $request['AppReference'] ?? '';
				$passenger_details = $request['RoomDetails'] ?? [];

				$save_hotel_booking = $this->CI->common_hotel_v3->save_hotel_booking(
					$hotel_data,
					$passenger_details,
					$app_reference,
					$booking_source,
					$search_id
				);

				if ($save_hotel_booking['status'] == SUCCESS_STATUS) {
					$book_req_params = [
						'ResultToken' => $ResultTokenData,
						'Passengers' => $passenger_details,
						'RoomPriceBreakup' => $hotel_price_details['RoomPriceBreakup'],
						'room_data' => $room_data_unserialized,
					];

					$active_booking_source_condition = [
						['BS.source_id', '=', '"' . $booking_source . '"']
					];
					$hotel_active_booking_sources = $this->hotel_active_booking_sources($active_booking_source_condition);

					$this->api_authentication($hotel_active_booking_sources);

					$hotel_obj_ref = load_hotel_lib_v3($booking_source);
					$process_booking_response = $this->CI->$hotel_obj_ref->process_booking(
						$book_req_params,
						$app_reference,
						0,
						$search_id
					);

					if ($process_booking_response['status'] == SUCCESS_STATUS) {
						$this->CI->load->library('hotel/GRN/common_hotel_v3');
						$this->CI->common_hotel_v3->deduct_hotel_booking_amount($app_reference);

						$hotel_booking_details = $this->CI->common_hotel_v3->get_hotel_booking_transaction_details($app_reference);
						$booking_response = $hotel_booking_details;
					} else {
						$booking_response['message'] = $process_booking_response['message'] ?? 'Booking failed';
					}
				} else {
					$booking_response['message'] = $save_hotel_booking['message'] ?? 'Booking not saved';
				}
			} else {
				$booking_response['message'] = 'Insufficient Balance';
			}
		} else {
			$booking_response['message'] = 'Invalid CommitBooking Request';
		}

		return $booking_response;
	}
	/**
	 * Process cancel Booking Request
	 *
	 * @param array $request
	 * @return array{status: string, message: string, data: array}
	 */
	public function cancel_booking(array $request): array
	{
		$cancel_booking_response = [
			'data' => [],
			'status' => FAILURE_STATUS,
			'message' => '',
		];

		if (valid_array($request)) {
			$app_reference = trim($request['AppReference'] ?? '');
			$hotel_booking_details = $this->CI->custom_db->single_table_records(
				'hotel_booking_details',
				'*',
				['app_reference' => $app_reference]
			);

			if (
				$hotel_booking_details['status'] == SUCCESS_STATUS &&
				isset($hotel_booking_details['data'][0]['status'])
			) {
				$booking_status = $hotel_booking_details['data'][0]['status'];
				$booking_transaction_details = $hotel_booking_details['data'][0];
				$booking_source = $booking_transaction_details['booking_source'] ?? '';

				$active_booking_source_condition = [
					['BS.source_id', '=', '"' . $booking_source . '"']
				];
				$hotel_active_booking_sources = $this->hotel_active_booking_sources($active_booking_source_condition);

				// Authenticate API
				$this->api_authentication($hotel_active_booking_sources);

				$hotel_obj_ref = load_hotel_lib_v3($booking_source);
				$cancel_booking_details = $this->CI->$hotel_obj_ref->cancel_booking($request);

				if ($cancel_booking_details['status'] == SUCCESS_STATUS) {
					$cancel_booking_response = $cancel_booking_details;
				} else {
					$cancel_booking_response['message'] = $cancel_booking_details['message'] ?? 'Cancellation failed';
				}

				if ($booking_status == 'BOOKING_CANCELLED') {
					$cancel_booking_response['message'] = 'Booking Already Cancelled';
				}
			} else {
				$cancel_booking_response['message'] = 'Invalid AppReference';
			}
		} else {
			$cancel_booking_response['message'] = 'Invalid CancelBooking Request';
		}

		return $cancel_booking_response;
	}

	/**
	 * Get Hotel Hold Booking Status
	 *
	 * @param array $request
	 * @return array{status: string, message: string, data: array}
	 */
	public function get_hold_booking_status(array $request): array
	{
		$data = [
			'status' => FAILURE_STATUS,
			'message' => '',
			'data' => [],
		];

		if (!empty($request)) {
			$hotel_active_booking_sources = $this->hotel_active_booking_sources();

			// Authenticate the APIs
			$this->api_authentication($hotel_active_booking_sources);

			$hotel_booking_id = [];

			foreach ($hotel_active_booking_sources as $bs_k => $bs_v) {
				$hotel_obj_ref = load_hotel_lib_v3($bs_v['booking_source'], '', true);
				$hotel_booking_id = $this->CI->$hotel_obj_ref->get_hold_booking_status($request);
			}

			if ($hotel_booking_id['status'] == true) {
				$data['data'] = $hotel_booking_id;
				$data['status'] = SUCCESS_STATUS;
			} else {
				$data['message'] = 'Booking Id Not Found';
			}
		}

		return $data;
	}
	/**
	 * Get Hotel Images (GRN)
	 *
	 * @param array $request
	 * @return array{status: string, message: string, data: array}
	 */
	public function get_hotel_images(array $request): array
	{
		$data = [
			'status' => FAILURE_STATUS,
			'message' => '',
			'data' => [],
		];

		if (!empty($request)) {
			$hotel_active_booking_sources = $this->hotel_active_booking_sources();

			// Authenticate the APIs
			$this->api_authentication($hotel_active_booking_sources);

			$status = FAILURE_STATUS;
			$image_response = [];

			foreach ($hotel_active_booking_sources as $bs_k => $bs_v) {
				$hotel_obj_ref = load_hotel_lib_v3($bs_v['booking_source'], '', true);

				// Fetch hotel images only if status is FAILURE
				if ($status == FAILURE_STATUS) {
					$image_response = $this->CI->$hotel_obj_ref->Get_Hotel_Property_Images($request);

					// If the API response is successful, update the data
					if ($image_response['status'] == SUCCESS_STATUS) {
						$status = SUCCESS_STATUS;
						$data['data'] = $image_response['images'];
						$data['status'] = SUCCESS_STATUS;
						$data['message'] = 'Hotel Images';
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Get Room Facilities
	 *
	 * @param string $hotel_code
	 * @param string $room_code
	 * @return array{status: string, message: string, data: array}
	 */
	public function get_room_facilities(string $hotel_code, string $room_code): array
	{
		$data = [
			'status' => FAILURE_STATUS,
			'message' => '',
			'data' => [],
		];

		if (!empty($hotel_code) && !empty($room_code)) {
			$hotel_obj_ref = load_hotel_lib_v3(HB_HOTEL_BOOKING_SOURCE, '', true);
			$room_facilities = $this->CI->$hotel_obj_ref->get_room_facilities($hotel_code, $room_code);

			if ($room_facilities['status'] == SUCCESS_STATUS) {
				$data['data'] = $room_facilities['data'];
				$data['status'] = SUCCESS_STATUS;
				$data['message'] = ''; // Optional: You can provide a custom message if necessary
			}
		}

		return $data;
	}

	/**
	 * Getting Cancellation Policy by Cancellation Code (GRN)
	 *
	 * @param array $request
	 * @param string $search_id
	 * @return array{status: string, message: string, data: array}
	 */
	public function get_cancellation_policy(array $request, string $search_id): array
	{
		$data = [
			'status' => FAILURE_STATUS,
			'message' => '',
			'data' => [],
		];

		if (valid_array($request)) {
			$hotel_active_booking_sources = $this->hotel_active_booking_sources();
			// Authenticate the API's
			$this->api_authentication($hotel_active_booking_sources);

			$hotel_obj_ref = load_hotel_lib_v3(GRN_CONNECT_HOTEL_BOOKING_SOURCE, '', true);
			$hotel_obj[$request['booking_source']] = $hotel_obj_ref;

			$cancellation_policy = $this->CI->$hotel_obj_ref->get_cancellation_policy($search_id, $request);

			$data['data'] = [$cancellation_policy];
			$data['status'] = SUCCESS_STATUS;
			$data['message'] = 'cancellationpolicy';
		}

		return $data;
	}
	/**
	 * Merges the hotel data.
	 *
	 * @param array $hotel_data
	 * @param array $formatted_seach_result
	 * @return void
	 */
	private function merge_hotel_list(array $hotel_data, array &$formatted_seach_result): void
	{
		// Iterate through each hotel data
		foreach ($hotel_data as $fd_k => $fd_v) {
			// Merge the data into the formatted search result
			$formatted_seach_result['data']['HotelSearchResult']['HotelResults'][$fd_k] = array_merge(
				$formatted_seach_result['data']['HotelSearchResult']['HotelResults'][$fd_k],
				$fd_v
			);
		}
	}

	/**
	 * Sends a notification when the booking is not confirmed.
	 *
	 * @param string $app_reference
	 * @return void
	 */
	private function booking_not_confirmed_notification(string $app_reference): void
	{
		// Retrieve hotel booking details based on the app reference
		$hotel_booking_details = $this->CI->custom_db->single_table_records(
			'hotel_booking_details',
			'*',
			['app_reference' => $app_reference]
		);

		// Retrieve domain name for the origin
		$get_domain_name = $this->CI->custom_db->single_table_records(
			'domain_list',
			'domain_name',
			['origin' => get_domain_auth_id()]
		);

		// Retrieve booking source details
		$booking_source_details = $this->CI->custom_db->single_table_records(
			'booking_source',
			'*',
			['source_id' => $hotel_booking_details['data'][0]['booking_source']]
		);

		// Extract the domain name and prepare the notification data
		$domain_name = $get_domain_name['data'][0]['domain_name'];
		$booking_failed_template = [
			'domain_name' => $domain_name,
			'app_reference' => $app_reference,
			'booking_api_name' => $booking_source_details['data'][0]['name']
		];

		// Send SMS to the TMX support team
		$sms_template = $this->CI->load->view('hotel/booking_failed_sms_template', $booking_failed_template, true);
		send_alert_sms($sms_template);

		// Send an email notification
		$mail_template = $this->CI->load->view('hotel/booking_failed_mail_template', $booking_failed_template, true);
		$Email = $this->CI->config->item('alert_email_id');

		// Send the email
		$this->CI->load->library('provab_mailer');
		$this->CI->provab_mailer->send_mail($Email, $domain_name . ' - Hotel Booking HOLD Status', $mail_template);
	}
	/**
	 * (non-PHPdoc)
	 * @see Common_Api_Grind::search_data()
	 *
	 * @param string|int $search_id
	 * @return array
	 */
	public function search_data(int $search_id): array
	{
		$response = [];
		$response['status'] = true;
		$response['data'] = [];
		$CI = &get_instance();

		// Check if the master search data is empty or invalid
		if (empty($this->master_search_data) && !valid_array($this->master_search_data)) {
			// Fetch the search details from the model
			$clean_search_details = $CI->hotel_model_v3->get_safe_search_data_grn($search_id);

			if ($clean_search_details['status'] == true) {
				$response['status'] = true;
				$response['data'] = $clean_search_details['data'];
				$this->master_search_data = $response['data'];
			} else {
				$response['status'] = false;
			}
		} else {
			// Use cached master search data
			$response['data'] = $this->master_search_data;
		}

		// Generate search hash from the response data
		$this->search_hash = md5(serialize($response['data']));

		return $response;
	}
}
