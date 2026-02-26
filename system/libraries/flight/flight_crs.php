<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');
require_once BASEPATH . 'libraries/Common_Api_Grind.php';
class Flight_crs extends Common_Api_Grind
{

	protected $source_code = PROVAB_FLIGHT_CRS_BOOKING_SOURCE;
	protected $token;
	protected $ClientId;
	protected $UserName;
	protected $Password;
	protected $system;			//test/live   -   System to which we have to connect in web service
	protected $Url;
	private $service_url;
	private $TokenId; //	Token ID that needs to be echoed back in every subsequent request
	protected $ins_token_file;
	private $CI;
	private $commission = array();
	var $master_search_data;
	var $search_hash; //search

	public function __construct()
	{
		parent::__construct();

		$this->CI = &get_instance();
		$this->CI->load->library('Api_Interface');
		$this->CI->load->library('flight/common_flight');
		//$this->CI->load->model('flight_model');
		// /$this->CI->load->model('flights_model');

		$this->service_url = base_url() . 'webconnect/index.php/v1/flight/';
		//error_reporting(0);


	}
	public function get_access_token(string $username,string $password, string $token, string $type): ?string
	{
		$response = [];
		$response['status'] = false;
		$response['data'] = array();
		$search_rq = [];
		$search_rq['apikey'] = $username;
		$search_rq['secretkey'] = $password;
		$search_rq['token'] = $token;

		// if (empty($_SESSION['access_token']) == true) {
		$service_url = base_url() . "webconnect/index.php/auth/v1/" . $type;
		$api_response = array();
		if(self::run_curl($service_url, json_encode($search_rq))){
			$api_response = self::run_curl($service_url, json_encode($search_rq));
			$api_response = $api_response['data']['access_token'];
		}
		return $api_response;
	}

	function getBlockData(string $BiD): ?array
    {
        $service_url = base_url() ."webconnect/index.php/v1/hotel/GetBlockDetails?id=" . $BiD;
        $api_response = self::run_curl($service_url, '');

        return $api_response['data'];
    }

	function getPg( string $bookId,string $pgKey,float $amount,string $currency, array $postData, string $blockId): array{
		$request = [];
		$response = [];
		$request['AppReference'] = $book_id;
		$request['SequenceNumber'] = 1;
		$request['ResultToken'] = $blockId;
		$request['Hold'] =0;

		$pax_count = count($post_data['passenger_type']);

		$passenger = array();
		$passenger = self::paxDetails($pax_count,$post_data);

		$request['Passengers'] = $passenger;

	
		$req = json_encode($request);

		$service_url = base_url() . "webconnect/index.php/pay_secure";

        $api_response = self::run_curl($service_url, $req,'Stripe',$pgKey);
		if($api_response['success']== true){

			$response['status'] = true;
			$response['PGUrl'] = $api_response['data']['link']['pgUrl'];

		}else{
			$response['status'] = false;
		}
		return $response;


	}
	function saveEnquiry(array $post_data, string $blockId): array {
		$request = [];
		$response = [];
		$request['FullName'] = $post_data['fullName'];
		$request['Email'] = $post_data['Email'];
		$request['CountryCode'] = '91';
		$request['Mob'] =$post_data['Mob'];
		$request['ResultToken'] =$blockId;


		
		$req = json_encode($request);

		$service_url = base_url() . "webconnect/index.php/v1/flight/Enquiry";

        $api_response = self::run_curl($service_url, $req,'');

		if($api_response['success']== true){

			$response['status'] = true;
			$response['Message'] = $api_response['data']['Message'];
			$response['EnqId'] = $api_response['data']['EnqId'];


		}else{
			$response['status'] = false;
		}
		return $response;


	}

	function PaxTypeId(string $id): ?int
	{
		$pax_type = array(
			'Adult' => 1,
			'Child' => 2,
			'Infant' => 3
		);
		return $pax_type[$id];
	}
	function paxDetails(int $count, array $passenger): array{
		$tmp_passenger = [];
		for ($i = 0; $i < $count; $i++) {

			$tmp_passenger[$i]['IsLeadPax'] = $passenger['lead_passenger'][$i];
            $tmp_passenger[$i]['Title'] = $passenger['name_title'][$i];
            $tmp_passenger[$i]['FirstName'] = ((strlen($passenger['first_name'][$i]) < 2) ? str_repeat($passenger['first_name'][$i], 2) : $passenger['first_name'][$i]);
            $tmp_passenger[$i]['LastName'] = ((strlen($passenger['last_name'][$i]) < 2) ? str_repeat($passenger['last_name'][$i], 2) : $passenger['last_name'][$i]);
            $tmp_passenger[$i]['PaxType'] = self::PaxTypeId($passenger['passenger_type'][$i]);
            $tmp_passenger[$i]['Gender'] = $passenger['gender'][$i];

            $tmp_passenger[$i]['DateOfBirth'] = date('Y-m-d', strtotime($passenger['date_of_birth'][$i]));

            if (empty($passenger['passport_number'][$i]) == false and empty($passenger['passport_expiry_date'][$i]) == false) {
                $tmp_passenger[$i]['PassportNumber'] = $passenger['passport_number'][$i];
                $tmp_passenger[$i]['PassportExpiry'] = $passenger['passport_expiry_date'][$i];
            } else {
                $tmp_passenger[$i]['PassportNumber'] = '';
                $tmp_passenger[$i]['PassportExpiry'] = '';
            }

            $tmp_passenger[$i]['CountryCode'] = $passenger['passenger_nationality'][$i];
            $tmp_passenger[$i]['CountryName'] = $passenger['billing_country_name'];
            $tmp_passenger[$i]['ContactNo'] = $passenger['passenger_contact'];
            $tmp_passenger[$i]['City'] = $passenger['billing_city'];
            $tmp_passenger[$i]['PinCode'] = $passenger['billing_zipcode'];

            $tmp_passenger[$i]['AddressLine1'] = $passenger['billing_city'];
            $tmp_passenger[$i]['AddressLine2'] = $passenger['billing_city'];
            $tmp_passenger[$i]['Email'] = $passenger['billing_email'];


            //Baggage
            if (isset($passenger['baggage'][$i]) == true && valid_array($passenger['baggage'][$i]) == true) {
                $tmp_passenger[$i]['BaggageId'] = $passenger['baggage'][$i];
            }

            //Meals
            if (isset($passenger['meal'][$i]) == true && valid_array($passenger['meal'][$i]) == true) {
                $tmp_passenger[$i]['MealId'] = $passenger['meal'][$i];
            }

            //Seat
            if (isset($passenger['seat'][$i]) == true && valid_array($passenger['seat'][$i]) == true) {
                $tmp_passenger[$i]['SeatId'] = $passenger['seat'][$i];
            }



		}
		return $tmp_passenger;
		

	}
	/**
	 *
	 * @param int $search_id        	
	 */
	public function search_data(int $search_id): array
	{
		$response = [];
		$response['status'] = true;
		$response['data'] = array();
		if (empty($this->master_search_data) == true and valid_array($this->master_search_data) == false) {
			$clean_search_details = $this->CI->flight_model->get_safe_search_data($search_id);
			$is_roundtrip  = false;
			$is_multicity  = false;
			if ($clean_search_details['status'] == true) {
				$response['status'] = true;
				$response['data'] = $clean_search_details['data'];
				// 28/12/2014 00:00:00 - date format
				if ($clean_search_details['data']['trip_type'] == 'multicity') {
					$response['data']['from'] = $clean_search_details['data']['from'];
					$response['data']['to'] = $clean_search_details['data']['to'];
					$response['data']['from_city'] = $clean_search_details['data']['from'];
					$response['data']['to_city'] = $clean_search_details['data']['to'];
					$response['data']['depature'] = $clean_search_details['data']['depature'];
					$response['data']['return'] = $clean_search_details['data']['depature'];
				} else {
					$response['data']['from'] = substr(chop(substr($clean_search_details['data']['from'], -5), ')'), -3);
					$response['data']['to'] = substr(chop(substr($clean_search_details['data']['to'], -5), ')'), -3);
					$response['data']['from_city'] = $clean_search_details['data']['from'];
					$response['data']['to_city'] = $clean_search_details['data']['to'];
					$response['data']['depature'] = date("Y-m-d", strtotime($clean_search_details['data']['depature'])) . 'T00:00:00';
					$response['data']['return'] = date("Y-m-d", strtotime($clean_search_details['data']['depature'])) . 'T00:00:00';
				}

				switch ($clean_search_details['data']['trip_type']) {

					case 'oneway':
						$response['data']['type'] = 'OneWay';
						break;

					case 'circle':
						$response['data']['type'] = 'Return';
						$response['data']['return'] = date("Y-m-d", strtotime($clean_search_details['data']['return'])) . 'T00:00:00';
						$is_roundtrip = true;
						break;
					case 'multicity':
						$response['data']['type'] = 'MultiCity';
						$is_multicity  = true;
						break;
					case 'gdsspecial':
						$response['data']['type'] = 'GDS Special';
						$response['data']['return'] = date("Y-m-d", strtotime($clean_search_details['data']['return'])) . 'T00:00:00';
						break;

					default:
						$response['data']['type'] = 'OneWay';
				}
				$response['data']['adult'] = $clean_search_details['data']['adult_config'];
				$response['data']['child'] = $clean_search_details['data']['child_config'];
				$response['data']['infant'] = $clean_search_details['data']['infant_config'];
				$response['data']['total_passenger'] = intval($clean_search_details['data']['adult_config'] + $clean_search_details['data']['child_config'] + $clean_search_details['data']['infant_config']);
				$response['data']['v_class'] = $clean_search_details['data']['v_class'];
				if ($clean_search_details['data']['carrier'] != '') {
					$response['data']['carrier'] = implode($clean_search_details['data']['carrier']);
				} else {
					$response['data']['carrier'] = '';
				}

				$response['data']['is_roundtrip'] = $is_roundtrip;
				$response['data']['is_multicity'] = $is_multicity;
				$response['data']['airSearch'] = $clean_search_details['data']['airSearch'];

				$response['data']['search_type'] = $clean_search_details['data']['search_type'];

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
	function get_available_fare_list(string $from_val, string $to_val, string $trip_type, string $segment_type, object $currency_obj) : array
	{
		$currency_symbol = $currency_obj->get_currency_symbol($currency_obj->to_currency);
		$search_data = [];
		$fare_list_obj = [];
		$adult = 1;
		$fare_details = $this->CI->flights_model->get_available_flight_prices($from_val, $to_val, $trip_type, $segment_type);
		$new_array = array();
		if ($fare_details) {
			foreach ($fare_details as $k => $day_fare) {

				$search_data['depature'] = $day_fare['avail_date'] . "T:00:00:00";
				$markup_data = $this->get_markup_details($search_data);
				$dept_date = $day_fare['avail_date'];
				$strAdultFare = $day_fare['adult_basefare'];
				$adt_tax_tot = $day_fare['adult_tax'];
				$total_temp_adult_fare = $strAdultFare + $adt_tax_tot;
				if (valid_array($markup_data['season_details'][0])) {
					if ($markup_data['season_details'][0]['percentage'] != "100%") {
						$percentage = str_replace("%", "", $markup_data['season_details'][0]['percentage']);
						$percentage = $percentage - 100;
						$tapercenatge = $total_temp_adult_fare * $percentage / 100;
						$total_temp_adult_fare += $tapercenatge;
					}
				}
				if (valid_array($markup_data['week_details'][0])) {
					if ($markup_data['week_details'][0]['percentage'] != "100%") {
						$percentage = str_replace("%", "", $markup_data['week_details'][0]['percentage']);
						$percentage = $percentage - 100;
						$tapercenatge = $total_temp_adult_fare * $percentage / 100;
						$total_temp_adult_fare += $tapercenatge;
					}
				}
				if (valid_array($markup_data['holiday_details'][0])) {
					if ($markup_data['holiday_details'][0]['percentage'] != "100%") {
						$percentage = str_replace("%", "", $markup_data['holiday_details'][0]['percentage']);
						$percentage = $percentage - 100;
						$tapercenatge = $total_temp_adult_fare * $percentage / 100;
						$total_temp_adult_fare += $tapercenatge;
					}
				}
				if (valid_array($markup_data['time_before_booking_details'][0])) {
					if ($markup_data['time_before_booking_details'][0]['percentage'] != "100%") {
						$percentage = str_replace("%", "", $markup_data['time_before_booking_details'][0]['percentage']);
						$percentage = $percentage - 100;
						$tapercenatge = $total_temp_adult_fare * $percentage / 100;
						$total_temp_adult_fare += $tapercenatge;
					}
				}
				if ($adult > 0) {
					$total_adult_markup = $total_temp_adult_fare - $strAdultFare - $adt_tax_tot;
				}
				$strAdultFare += $total_adult_markup;
				$strTotalFare  = ($strAdultFare * $adult);
				$strTotalTax   = ($adt_tax_tot * $adult);
				$TotalFare = $strTotalFare + $strTotalTax;
				$fare_list_obj['TotalFare'] = '₹ ' . round($TotalFare);
				$fare_list_obj['Fare'] = round($TotalFare);
				$fare_list_obj['departure'] = $dept_date;
				$TotalNetFare = $TotalFare;
				if (isset($new_array[$dept_date]) == true) {
					$Old_TotalFare = round($new_array[$dept_date]['Fare']);

					if ($TotalNetFare < $Old_TotalFare) { //If fare is low, then assign the new flight
						$new_array[$dept_date] = $fare_list_obj;
					}
				} else {
					$new_array[$dept_date] = $fare_list_obj;
				}
			}
		}
		$new_array = array_values($new_array);
		return $new_array;
	}
	/**
	 * Calendar Fare
	 * @param $search_params
	 */
	function get_fare_list(array $search_data): array
	{
		$response = [];
		// debug($search_data);exit;
		$response['data'] = array();
		$response['status'] = true;
		if (valid_array($search_data)) {
			$departure_date = $search_data['depature'];
			$new_departure_dates = array();

			$trip_type 	 = $search_data['trip_type'];
			$from        = $search_data['from'];
			$from_loc_id = $search_data['from_loc_id'];
			$to          = $search_data['to'];
			$to_loc_id   = $search_data['to_loc_id'];
			$depature    = $search_data['depature'];
			$adult       = $search_data['adult_config'];
			$child       = $search_data['child_config'];
			$infant      = $search_data['infant_config'];
			$fare_type   = $search_data['fare_type'];
			$segment_type 	= (($search_data['is_domestic'] == 0) ? 1 : 0);
			$return      = isset($search_data['return']) ? $search_data['return'] : $search_data['depature'];
			$today = new DateTime(date("Y-m-d"));
			$later = new DateTime(date("Y-m-d", strtotime($depature)));
			$diff = $later->diff($today)->format("%r%a");

			$date_diff = 0;
			if ($diff != 0) {
				$date_diff = ($diff < -3) ? -3 : $diff;

				$date_difference = $date_diff . " day";

				$depature_date = new DateTime(date("Y-m-d", strtotime($depature)));
				$start_date = $depature_date->modify($date_difference)->format('Y-m-d');

				$depature_date = new DateTime(date("Y-m-d", strtotime($depature)));
				$end_date = $depature_date->modify('+3 days')->format('Y-m-d');
			} else {
				$start_date = date("Y-m-d");
				$depature_date = new DateTime(date("Y-m-d", strtotime($depature)));
				$end_date = $depature_date->modify('+3 days')->format('Y-m-d');
			}
			$dates = range(strtotime($start_date), strtotime($end_date), 86400);
			foreach ($dates as $date) {
				$new_departure_dates[] = date('Y-m-d', $date);
			}
			$fare_details = array();
			$fare_details = $this->CI->flights_model->get_calendarfare($from, $to, $trip_type, $start_date, $end_date, $adult, $child, $infant, $segment_type);
			$oneway_final_list = $this->format_calender_fare($fare_details, $search_data, $new_departure_dates);

			$roundtrip_final_list = array();
			if ($trip_type == "circle") {
				$return = $search_data['return'];
				$later = new DateTime(date("Y-m-d", strtotime($return)));
				$diff = $later->diff($today)->format("%r%a");

				$date_diff = 0;
				if ($diff != 0) {
					$date_diff = ($diff < -3) ? -3 : $diff;

					$date_difference = $date_diff . " day";

					$depature_date = new DateTime(date("Y-m-d", strtotime($return)));
					$start_date = $depature_date->modify($date_difference)->format('Y-m-d');

					$depature_date = new DateTime(date("Y-m-d", strtotime($return)));
					$end_date = $depature_date->modify('+3 days')->format('Y-m-d');
				} else {
					$start_date = date("Y-m-d");
					$depature_date = new DateTime(date("Y-m-d", strtotime($return)));
					$end_date = $depature_date->modify('+3 days')->format('Y-m-d');
				}
				$dates = range(strtotime($start_date), strtotime($end_date), 86400);
				$new_retun_dates = array();
				foreach ($dates as $date) {
					$new_retun_dates[] = date('Y-m-d', $date);
				}
				$fare_details = $this->CI->flights_model->get_calendarfare($to, $from, $trip_type, $start_date, $end_date, $adult, $child, $infant, $segment_type);
				$roundtrip_final_list = $this->format_calender_fare($fare_details, $search_data, $new_retun_dates);
				//debug($roundtrip_final_list);exit;
			}

			if (valid_array($oneway_final_list) || valid_array($roundtrip_final_list)) {
				$response['data']['OneWay'] = $oneway_final_list;
				$response['data']['RoundTrip'] = $roundtrip_final_list;
			} else {
				$response['status'] = FAILURE_STATUS;
				$response['message'] = "No flights";
			}
		} else {
			$response['status'] = FAILURE_STATUS;
			$response['message'] = "No flights";
		}
		// debug($response);exit;
		return $response;
	}
	function format_calender_fare(array $fare_details, array $search_data, array $new_departure_dates): array
	{
		$fare_list = [];
		$fare_list_obj =array();
		$new_array = array($fare_details);
		$avaliable_dates = array();
		$final_list = array();
		$adult       = $search_data['adult_config'];
		$child       = $search_data['child_config'];
		$infant      = $search_data['infant_config'];
		//debug($fare_details);exit;
		if ($fare_details) {
			foreach ($fare_details as $k => $day_fare) {
				$dept_date = local_date($day_fare['avail_date']);
				$search_data['depature'] = $day_fare['avail_date'] . 'T00:00:00';
				$markup_data = $this->get_markup_details($search_data);
				$avaliable_dates[] = date('Y-m-d', strtotime($day_fare['avail_date']));
				//debug($day_fare);exit;
				if (valid_array($day_fare) == true) {
					$avail_time = $day_fare['avail_date'] . ' ' . $day_fare['departure_time'];
					$fare_list_obj['airline_code'] = $day_fare['carrier_code'];
					$fare_list_obj['airline_icon'] = SYSTEM_IMAGE_DIR . 'airline_logo/' . $day_fare['carrier_code'] . '.gif';
					$fare_list_obj['airline_name'] = $day_fare['airline_name'];

					$fare_list_obj['departure_date'] = local_date($day_fare['avail_date']);
					$fare_list_obj['departure_time'] = local_time($avail_time);
					$fare_list_obj['departure'] = $day_fare['avail_date'];

					$adt_tax_tot = $day_fare['adult_tax'];
					$inf_tax_tot = $day_fare['infant_tax'];
					$chd_tax_tot = $day_fare['child_tax'];
					$strAdultFare = $day_fare['adult_basefare'];
					$strChildFare = $day_fare['child_basefare'];
					$strInfantFare = $day_fare['infant_basefare'];
					//Adding Season Markup
					$total_temp_adult_fare = $strAdultFare + $adt_tax_tot;
					$total_temp_child_fare = $strChildFare + $chd_tax_tot;
					$total_temp_infant_fare = $strInfantFare + $inf_tax_tot;
					if (valid_array($markup_data['season_details'][0])) {
						if ($markup_data['season_details'][0]['percentage'] != "100%") {
							$percentage = str_replace("%", "", $markup_data['season_details'][0]['percentage']);
							$percentage = $percentage - 100;
							$tapercenatge = $total_temp_adult_fare * $percentage / 100;
							$total_temp_adult_fare += $tapercenatge;
							$tcpercenatge = $total_temp_child_fare * $percentage / 100;
							$total_temp_child_fare += $tcpercenatge;
							$tipercenatge = $total_temp_infant_fare * $percentage / 100;
							$total_temp_infant_fare += $tipercenatge;
						}
					}
					if (valid_array($markup_data['week_details'][0])) {
						if ($markup_data['week_details'][0]['percentage'] != "100%") {
							$percentage = str_replace("%", "", $markup_data['week_details'][0]['percentage']);
							$percentage = $percentage - 100;
							$tapercenatge = $total_temp_adult_fare * $percentage / 100;
							$total_temp_adult_fare += $tapercenatge;
							$tcpercenatge = $total_temp_child_fare * $percentage / 100;
							$total_temp_child_fare += $tcpercenatge;
							$tipercenatge = $total_temp_infant_fare * $percentage / 100;
							$total_temp_infant_fare += $tipercenatge;
						}
					}
					if (valid_array($markup_data['holiday_details'][0])) {
						if ($markup_data['holiday_details'][0]['percentage'] != "100%") {
							$percentage = str_replace("%", "", $markup_data['holiday_details'][0]['percentage']);
							$percentage = $percentage - 100;
							$tapercenatge = $total_temp_adult_fare * $percentage / 100;
							$total_temp_adult_fare += $tapercenatge;
							$tcpercenatge = $total_temp_child_fare * $percentage / 100;
							$total_temp_child_fare += $tcpercenatge;
							$tipercenatge = $total_temp_infant_fare * $percentage / 100;
							$total_temp_infant_fare += $tipercenatge;
						}
					}
					if (valid_array($markup_data['time_before_booking_details'][0])) {
						if ($markup_data['time_before_booking_details'][0]['percentage'] != "100%") {
							$percentage = str_replace("%", "", $markup_data['time_before_booking_details'][0]['percentage']);
							$percentage = $percentage - 100;
							$tapercenatge = $total_temp_adult_fare * $percentage / 100;
							$total_temp_adult_fare += $tapercenatge;
							$tcpercenatge = $total_temp_child_fare * $percentage / 100;
							$total_temp_child_fare += $tcpercenatge;
							$tipercenatge = $total_temp_infant_fare * $percentage / 100;
							$total_temp_infant_fare += $tipercenatge;
						}
					}

					if ($adult > 0) {
						$total_adult_markup = $total_temp_adult_fare - $strAdultFare - $adt_tax_tot;
					}
					if ($child > 0) {
						$total_child_markup = $total_temp_child_fare - $strChildFare - $chd_tax_tot;
					}
					if ($infant > 0) {
						$total_infant_markup = $total_temp_infant_fare - $strInfantFare - $inf_tax_tot;
					}
					$strAdultFare += $total_adult_markup;
					$strChildFare += $total_child_markup;
					$strInfantFare += $total_infant_markup;
					$strTotalFare  = ($strAdultFare * $adult) + ($strChildFare * $child) + ($strInfantFare * $infant);
					$strTotalTax   = ($adt_tax_tot * $adult) + ($inf_tax_tot * $infant) + ($chd_tax_tot * $child);

					$fare_list_obj['BaseFare'] = $strTotalFare; //Base Fare
					$fare_list_obj['tax'] = $strTotalTax;
					$TotalNetFare = floatval($strTotalFare + $strTotalTax);

					if (isset($new_array[$dept_date]) == true) {
						$Old_TotalFare = floatval($new_array[$dept_date]['BaseFare'] + $new_array[$dept_date]['tax']);

						if ($TotalNetFare < $Old_TotalFare) { //If fare is low, then assign the new flight
							$new_array[$dept_date] = $fare_list_obj;
						}
					} else {
						$new_array[$dept_date] = $fare_list_obj;
					}
				} else {
					$fare_list_obj = false;
				}
				if (valid_array($day_fare) == true) {
					$fare_list[db_current_datetime(add_days_to_date(0, $day_fare['avail_date']))] = $new_array[$dept_date];
				}
			}
			$new_list = array_diff($new_departure_dates, $avaliable_dates);
			$not_available_data = array();
			foreach ($new_list as $d_key => $dates) {
				$d_key = db_current_datetime(add_days_to_date(0, $dates));
				$not_available_data[$d_key]['airline_code'] = '';
				$not_available_data[$d_key]['airline_icon'] = '';
				$not_available_data[$d_key]['airline_name'] = '';
				$not_available_data[$d_key]['departure_date'] = local_date($dates);
				$not_available_data[$d_key]['departure_time'] = '';
				$not_available_data[$d_key]['departure'] = $dates;
				$not_available_data[$d_key]['BaseFare'] = 0;
				$not_available_data[$d_key]['tax'] = 0;
			}

			$final_list = array_merge($fare_list, $not_available_data);
			ksort($final_list);
		}
		return $final_list;
	}



	function make_domestic_flight_req( string $cust_date,int $total_seat,string $origin, string $destination,string $strClass,string $module = "b2c"): string
	{
		$query_ho = '';
		$query_ho  = "SELECT fcsd.fsid, 
						fcsd.is_domestic, 
						fcsd.origin, 
						fcsd.destination,
						fcsd.dep_to_date, 
						
						fcsd.dep_from_date, 
						
						fcsd.flight_num, 
						fcsd.carrier_code, 
						fcsd.airline_name, 
						fcsd.class_type, 
						fcsd.actual_basefare, 
						fcsd.tax, 
						fcsd.s_tax, 
						fcsd.s_charge, 
						fcsd.t_discount, 
						fcsd.no_of_stops, 
						fcsd.origin_city, 
						fcsd.destination_city, 
						fcsd.active, 
						fcsd.update_time, 
						fcsd.dep_from_date_1, 
						fcsd.crs_currency, 
						fcsd.trip_type, 
						cufd.arr_time as arrival_time, 
						cufd.dep_time as departure_time, 
						cufd.avail_date ,
						cufd.adult_base AS adult_basefare, 
						cufd.adult_tax, 
						cufd.child_base AS child_basefare, 
						cufd.child_tax AS child_tax,
						cufd.infant_base AS infant_basefare, 
						cufd.infant_tax AS infant_tax, 
						cufd.avail_seat AS seats, 
						cufd.pnr
					FROM flight_crs_segment_details fcsd 
						INNER JOIN flight_crs_details fcd on(fcsd.fsid = fcd.fsid)
						INNER JOIN crs_update_flight_details cufd on(fcsd.fsid = cufd.fsid)
					where '$cust_date' between DATE(fcsd.dep_from_date) and DATE(fcsd.dep_to_date) 
							AND fcsd.active = '1'
							AND fcsd.origin = '$origin' 
							AND fcsd.destination = '$destination' 
							AND cufd." . $module . "_status = '1'
							AND cufd.avail_date ='$cust_date' 

							AND cufd.avail_seat>='$total_seat' 
							AND fcsd.trip_type=0
							"; //AND cufd.pnr != ''

		if ($strClass != 'All') {
			$query_ho .= " AND fcsd.class_type='$strClass'";
		}


		return $query_ho;
	}


	function flight_availability_req(array $search_data, string $module): array
	{

		$module = $module;
		$arrFltDtls = [];
		$response = [];
		$response['status'] = false;
		if (valid_array($search_data)) {
			// echo $segment_type 	= isset($search_data['is_domestic']) && $search_data['is_domestic'] == 1 ? '0' : '1';exit;
			$segment_type 	= (($search_data['is_domestic'] == 0) ? 1 : 0);
			$trip_type 		= isset($search_data['trip_type']) && strcmp($search_data['trip_type'], 'oneway') == 0 ? 'O' : 'R';
			$origin 		= $search_data['from'];
			$destination 	= $search_data['to'];
			// $date 			= date('m/d/Y',strtotime($search_data['depature']));
			$arr_dep_date	= explode("T", $search_data['depature']);
			$dep_date 		= $arr_dep_date[0];

			$arr_ret_date	= explode("T", $search_data['return']);
			$ret_date 		= $arr_ret_date[0];

			$adults 		= $search_data['adult'];
			$childs 		= $search_data['child'];
			$infants 		= $search_data['infant'];
			$strClass 		= $search_data['v_class'];
			$strTripType    = $search_data['trip_type'];
			$strIsDomstic   = $search_data['is_domestic'];
			//exit;
			$fare_type 		= 'N'; // N for normal R for Roundtrip special
			// $class 			= 'Economy'; //Business & Economy
			$host_access 	= 'Y';
			/*$strTripType    = 0;
			if($strTripType == "circle" && $strIsDomstic == true){
				$strTripType    = 1;
			}*/

			$total_seat = $adults + $childs;
			if ($total_seat > 2) {

				$total_seat = 1;
			}

			if (true) {
				$distance = 0;
				if ($search_data['search_type'] == 'ch') {

					$dep_details = $this->CI->custom_db->single_table_records("flight_airport_list", "*", array("airport_code" => $origin));
					$arriv_details = $this->CI->custom_db->single_table_records("flight_airport_list", "*", array("airport_code" => $destination));

					$dep_lat = $dep_details['data'][0]['latitude'];
					$dep_lon = $dep_details['data'][0]['longitude'];

					$arrv_lat = $arriv_details['data'][0]['latitude'];
					$arrv_lon = $arriv_details['data'][0]['longitude'];
					$distance = self::haversineDistance($dep_lat, $dep_lon, $arrv_lat, $arrv_lon);
				}
				if ($strTripType == "oneway" && $strIsDomstic == false) {
					$round_int_query = '';

					if ($search_data['search_type'] == 'ch') {

						$round_int_query = $this->make_search_query_charter($dep_date, $origin, $destination, $segment_type, $strClass, $total_seat, $strTripType, $ret_date, $module);
					} else {
						$round_int_query = $this->make_search_query($dep_date, $origin, $destination, $segment_type, $strClass, $total_seat, $strTripType, $ret_date, $module);
					}



					$flight_result = $this->CI->custom_db->custon_query_run($round_int_query);


					$arrFltDtls['onward_hdrdata'] = $flight_result;
					$arrFltDtls['distance'] = $distance;


					//onward Details- start
					if (!empty($arrFltDtls['onward_hdrdata'])) {
						$round_flifgt_details_int_query = '';
						$round_flifgt_details_int_query = $this->make_flight_details_query($dep_date, $origin, $destination, $segment_type, $strClass, "oneway", $ret_date, $module);
						//debug($round_flifgt_details_int_query);exit;
						$flight_result = $this->CI->custom_db->custon_query_run($round_flifgt_details_int_query);

						$arrFltDtls['onward_dtldata'] = $flight_result;
					} else {
						$arrFltDtls['onward_dtldata'] = array();
					}
				} else {
					//onward - start
					$query_oneway = '';
					if ($search_data['search_type'] == 'ch') {

						$query_oneway = $this->make_search_query_charter($dep_date, $origin, $destination, $segment_type, $strClass, $total_seat, $strTripType, $ret_date, $module);
					} else {
						$query_oneway = $this->make_search_query($dep_date, $origin, $destination, $segment_type, $strClass, $total_seat, $strTripType, '', $module);
					}

					$flight_result = $this->CI->custom_db->custon_query_run($query_oneway);


					$arrFltDtls['onward_hdrdata'] = $flight_result;

					//return - start
					$query_return = '';
					$query_return = $this->make_search_query($ret_date, $destination, $origin, $segment_type, $strClass, $total_seat, $strTripType, '', $module);
					$flight_result = $this->CI->custom_db->custon_query_run($query_return);
					$arrFltDtls['return_hdrdata'] = $flight_result;

					// debug($arrFltDtls);exit;
					//onward Details- start

					if (!empty($arrFltDtls['onward_hdrdata'])) {
						$onward_fcd_query = '';
						if ($search_data['search_type'] == 'ch') {
							$onward_fcd_query = $this->make_flight_details_query_charter($dep_date, $origin, $destination, $segment_type, $strClass, "oneway", '', $module);
						} else {
							$onward_fcd_query = $this->make_flight_details_query($dep_date, $origin, $destination, $segment_type, $strClass, "oneway", '', $module);
						}

						$flight_result = $this->CI->custom_db->custon_query_run($onward_fcd_query);

						$arrFltDtls['onward_dtldata'] = $flight_result;
					} else {
						$arrFltDtls['onward_dtldata'] = array();
					}

					$arrFltDtls['distance'] = $distance;

					//return Details- start
					if (!empty($arrFltDtls['return_hdrdata'])) {
						$return_fcd_query = '';
						$return_fcd_query = $this->make_flight_details_query($ret_date, $destination, $origin, $segment_type, $strClass, "oneway", '', $module);

						$flight_result = $this->CI->custom_db->custon_query_run($return_fcd_query);
						$arrFltDtls['return_dtldata'] = $flight_result;
					} else {
						$arrFltDtls['return_dtldata'] = array();
					}
				}
			}
			$response['status'] = true;
			$response['data'] = $arrFltDtls;
		}




		return $response;
	}

	function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
	{
		$earthRadius = 6371; // Earth radius in kilometers

		// Convert degrees to radians
		$lat1 = deg2rad($lat1);
		$lon1 = deg2rad($lon1);
		$lat2 = deg2rad($lat2);
		$lon2 = deg2rad($lon2);

		// Haversine formula
		$dlat = $lat2 - $lat1;
		$dlon = $lon2 - $lon1;


		$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$distance = $earthRadius * $c;

		return $distance; // Distance in kilometers
	}

	function make_search_query_charter(string $dep_date,string $origin,string $destination,int|string $segment_type,string $strClass,int $total_seat,string $strTripType = "oneway",string $ret_date = '',string $module = "b2c"): string
	{
		$date_con = '';
		if ($strTripType == "circle" && $segment_type == false && !empty($ret_date)) {
			$date_con = " DATE(FCSD.dep_from_date) = '$dep_date' and DATE(FCSD.dep_to_date) = '$ret_date' and FCSD.trip_type=1 ";
		} else {
			$date_con = " '$dep_date' between DATE(FCSD.dep_from_date) and DATE(FCSD.dep_to_date) and FCSD.trip_type=0 ";
		}



		if ($GLOBALS['CI']->entity_user_group == 1) {
			$listing = 'FCSD.adult_local_basefare as actual_basefare, 
								FCSD.adult_local_tax as tax,
								CUFD.adult_local_base AS adult_basefare, 
								CUFD.adult_local_tax, 
								CUFD.infant_local_base AS infant_basefare, 
								CUFD.infant_local_tax AS infant_tax,
								CUFD.child_local_base AS child_basefare, 
								CUFD.child_local_tax AS child_tax,';
		} else {
			$listing = 'FCSD.adult_basefare as actual_basefare, 
								FCSD.adult_tax as tax,
								CUFD.adult_base AS adult_basefare, 
								CUFD.adult_tax, 
								CUFD.infant_base AS infant_basefare, 
								CUFD.infant_tax AS infant_tax,
								CUFD.child_base AS child_basefare, 
								FCSD.charter_basefare AS charter_basefare, 
								FCSD.charter_tax AS charter_tax, 
								FCSD.charter_vat AS charter_vat, 
								CUFD.child_tax AS child_tax,';
		}

		$oneway_query = '';
		$oneway_query = "select FCSD.fsid, 
								FCSD.is_domestic, 
								FCSD.origin, 
								FCSD.destination,
								FCSD.dep_to_date, 
								FCSD.dep_from_date, 
								FCSD.GMT_dep_time, 
								FCSD.GMT_arr_time, 
								FCSD.flight_num, 
								FCSD.carrier_code, 
								FCSD.airline_name, 
								FCSD.class_type, 
								FCSD.aircraft, 

								" . $listing . "
								FCSD.no_of_stops, 
								FCSD.origin_city, 
								FCSD.destination_city,
								FCSD.cancellation_percentage, 
								FCSD.active, 
								FCSD.update_time,
								FCSD.dep_from_date_1, 
								FCSD.crs_currency, 
								FCSD.trip_type,
								FCSD.show_meal,
								FCSD.show_baggage,
								FCSD.show_seat,
								CUFD.origin as flight_details_id, 
								CUFD.avail_date,
								CUFD.avail_seat AS seats, 
								CUFD.pnr,
								CUFD.avail_seat, 
								CUFD.booked_seat, 
								CUFD.blocked_seat, 
								CUFD.dep_time as departure_time,
								CUFD.arr_time as arrival_time, 
								
								
								CUFD.tax_breakup AS tax_breakup,  
								FCD.baggage_info,
								FCD.fare_rule

						 from flight_crs_segment_details FCSD
						 	  join crs_update_flight_details CUFD using(fsid)
						 	  join flight_crs_details FCD using(fsid)
						 where $date_con 
						 		and FCSD.active = '1'
						 		AND CUFD." . $module . "_status = '1'
						 		and (CUFD.avail_seat - CUFD.booked_seat - CUFD.blocked_seat) >= '1' 
						 		and FCSD.origin = '$origin' 
						 		and FCSD.fare_type='0' 
						 		and FCSD.is_domestic='1'
						 		
						 "; // AND CUFD.pnr != '' 
		//and (CUFD.avail_seat - CUFD.booked_seat) >= '$total_seat' 

		// and CUFD.GMT_avail_date = '$dep_date' 

		if ($strClass != 'All') {
			//	$oneway_query .= "and FCSD.class_type='$strClass' ";
		}
		$oneway_query .= ' GROUP by FCSD.fsid order by FCSD.fsid ';

		return $oneway_query;
	}
	function make_search_query(string $dep_date,string $origin,string $destination,int|string $segment_type,string $strClass, int $total_seat, string $strTripType = "oneway", string $ret_date = '',string $module = "b2c"): string
	{
		$date_con = '';
		if ($strTripType == "circle" && $segment_type == false && !empty($ret_date)) {
			$date_con = " DATE(FCSD.dep_from_date) = '$dep_date' and DATE(FCSD.dep_to_date) = '$ret_date' and FCSD.trip_type=1 ";
		} else {
			$date_con = " '$dep_date' between DATE(FCSD.dep_from_date) and DATE(FCSD.dep_to_date) and FCSD.trip_type=0 ";
		}



		if ($GLOBALS['CI']->entity_user_group == 1) {
			$listing = 'FCSD.adult_local_basefare as actual_basefare, 
								FCSD.adult_local_tax as tax,
								CUFD.adult_local_base AS adult_basefare, 
								CUFD.adult_local_tax, 
								CUFD.infant_local_base AS infant_basefare, 
								CUFD.infant_local_tax AS infant_tax,
								CUFD.child_local_base AS child_basefare, 
								CUFD.child_local_tax AS child_tax,';
		} else {
			$listing = 'FCSD.adult_basefare as actual_basefare, 
								FCSD.adult_tax as tax,
								CUFD.adult_base AS adult_basefare, 
								CUFD.adult_tax, 
								CUFD.infant_base AS infant_basefare, 
								CUFD.infant_tax AS infant_tax,
								CUFD.child_base AS child_basefare, 
								CUFD.child_tax AS child_tax,';
		}

		$oneway_query = '';
		$oneway_query = "select FCSD.fsid, 
								FCSD.is_domestic, 
								FCSD.origin, 
								FCSD.destination,
								FCSD.dep_to_date, 
								FCSD.dep_from_date, 
								FCSD.GMT_dep_time, 
								FCSD.GMT_arr_time, 
								FCSD.flight_num, 
								FCSD.carrier_code, 
								FCSD.airline_name, 
								FCSD.class_type, 
								" . $listing . "
								FCSD.no_of_stops, 
								FCSD.origin_city, 
								FCSD.destination_city,
								FCSD.cancellation_percentage, 
								FCSD.active, 
								FCSD.update_time,
								FCSD.dep_from_date_1, 
								FCSD.crs_currency, 
								FCSD.trip_type,
								FCSD.show_meal,
								FCSD.show_baggage,
								FCSD.show_seat,
								CUFD.origin as flight_details_id, 
								CUFD.avail_date,
								CUFD.avail_seat AS seats, 
								CUFD.pnr,
								CUFD.avail_seat, 
								CUFD.booked_seat, 
								CUFD.blocked_seat, 
								CUFD.dep_time as departure_time,
								CUFD.arr_time as arrival_time, 
								
								
								CUFD.tax_breakup AS tax_breakup,  
								FCD.baggage_info,
								FCD.fare_rule

						 from flight_crs_segment_details FCSD
						 	  join crs_update_flight_details CUFD using(fsid)
						 	  join flight_crs_details FCD using(fsid)
						 where $date_con 
						 		and FCSD.active = '1'
						 		AND CUFD." . $module . "_status = '1'
						 		and (CUFD.avail_seat - CUFD.booked_seat - CUFD.blocked_seat) >= '1' 
						 		and FCSD.origin = '$origin' 
						 		and FCSD.destination='$destination' 
						 		and FCSD.is_domestic='$segment_type'
						 		
						 "; // AND CUFD.pnr != '' 
		//and (CUFD.avail_seat - CUFD.booked_seat) >= '$total_seat' 

		// and CUFD.GMT_avail_date = '$dep_date' 

		if ($strClass != 'All') {
			//	$oneway_query .= "and FCSD.class_type='$strClass' ";
		}
		$oneway_query .= ' GROUP by FCSD.fsid order by FCSD.fsid ';


		return $oneway_query;
	}
	function make_search_query_old(string $dep_date,string $origin,string $destination,int|string $segment_type, string $strClass, int $total_seat, string $strTripType = "oneway", string $ret_date = '', string $module = "b2c"): string
	{
		$date_con = '';
		if ($strTripType == "circle" && $segment_type == false && !empty($ret_date)) {
			$date_con = " DATE(FCSD.dep_from_date) = '$dep_date' and DATE(FCSD.dep_to_date) = '$ret_date' and FCSD.trip_type=1 ";
		} else {
			$date_con = " '$dep_date' between DATE(FCSD.dep_from_date) and DATE(FCSD.dep_to_date) and FCSD.trip_type=0 ";
		}
		$oneway_query = '';
		$oneway_query = "select FCSD.fsid, 
								FCSD.is_domestic, 
								FCSD.origin, 
								FCSD.destination,
								FCSD.dep_to_date, 
								FCSD.dep_from_date, 
									FCSD.GMT_dep_time, 
								FCSD.GMT_arr_time, 
								
								
								FCSD.flight_num, 
								FCSD.carrier_code, 
								FCSD.airline_name, 
								FCSD.class_type, 
								FCSD.adult_basefare as actual_basefare, 
								FCSD.adult_tax as tax, 
								FCSD.no_of_stops, 
								FCSD.origin_city, 
								FCSD.destination_city, 
								FCSD.active, 
								FCSD.update_time, 
								FCSD.dep_from_date_1, 
								FCSD.crs_currency, 
								FCSD.trip_type, 

								CUFD.avail_date,
								CUFD.avail_seat AS seats, 
								CUFD.pnr,
								CUFD.avail_seat, 
								CUFD.booked_seat, 
								CUFD.blocked_seat, 
								CUFD.dep_time as departure_time,
								CUFD.arr_time as arrival_time, 
								
								CUFD.adult_base AS adult_basefare, 
								CUFD.adult_tax, 
								CUFD.infant_base AS infant_basefare, 
								CUFD.infant_tax AS infant_tax,
								CUFD.child_base AS child_basefare, 
								CUFD.child_tax AS child_tax,
								CUFD.tax_breakup AS tax_breakup 

						 from flight_crs_segment_details FCSD
						 	  join crs_update_flight_details CUFD using(fsid)
						 where $date_con 
						 		and FCSD.active = '1'
						 		AND CUFD." . $module . "_status = '1'
						 		and CUFD.avail_date = '$dep_date' 
						 		and (CUFD.avail_seat - CUFD.booked_seat - CUFD.blocked_seat) >= '$total_seat' 
						 		and FCSD.origin = '$origin' 
						 		and FCSD.destination='$destination' 
						 		and FCSD.is_domestic='$segment_type'
						 		
						 "; // AND CUFD.pnr != '' 
		if ($strClass != 'All') {
			//$oneway_query .= "and FCSD.class_type='$strClass' ";
		}
		$oneway_query .= ' GROUP by FCSD.fsid order by FCSD.fsid ';
		return $oneway_query;
	}
	function make_flight_details_query_charter(string $dep_date,string $origin,string $destination,int|string $segment_type, string $strClass, string $strTripType = "oneway", string $ret_date = '', string $module = "b2c"): string
	{

		$date_con = '';
		$trip_type = 0;
		if ($strTripType == "circle" && $segment_type == false && !empty($ret_date)) {
			$date_con = " DATE(dep_from_date) = '$dep_date' and DATE(dep_to_date) = '$ret_date' and FCSD.trip_type=1 ";
			$trip_type = 1;
		} else {
			$date_con = " '$dep_date' between DATE(FCSD.dep_from_date) and DATE(FCSD.dep_to_date) and FCSD.trip_type=0 ";
			$trip_type = 0;
		}

		$fcd_query = '';
		$fcd_query = "select FCS.*,CUFD.origin as flight_details_id
					  from flight_crs_details FCS
					  join crs_update_flight_details CUFD using(fsid)
					  where FCS.fsid in (select FCSD.fsid from flight_crs_segment_details FCSD
					  				where $date_con  
					  					AND CUFD." . $module . "_status = '1'
					  					and CUFD.avail_date = '$dep_date' 
					  					and (CUFD.avail_seat - CUFD.booked_seat - CUFD.blocked_seat) >= '1'   
					  					and FCSD.origin = '$origin' 
					  					and FCSD.fare_type='0' 
					  					and FCSD.is_domestic='1'
					  					and FCSD.active='1'
					  "; // AND CUFD.pnr != '' 
		if ($strClass != 'All') {
			//$fcd_query .= "and FCSD.class_type='$strClass' ";
		}
		$fcd_query .= " ) and FCS.trip_type=$trip_type order by fdid ";

		return $fcd_query;
	}
	function make_flight_details_query(string $dep_date,string $origin,string $destination,int|string $segment_type,string $strClass,string $strTripType = "oneway",string $ret_date = '',string $module = "b2c"): string
	{

		$date_con = '';
		$trip_type = 0;
		if ($strTripType == "circle" && $segment_type == false && !empty($ret_date)) {
			$date_con = " DATE(dep_from_date) = '$dep_date' and DATE(dep_to_date) = '$ret_date' and FCSD.trip_type=1 ";
			$trip_type = 1;
		} else {
			$date_con = " '$dep_date' between DATE(FCSD.dep_from_date) and DATE(FCSD.dep_to_date) and FCSD.trip_type=0 ";
			$trip_type = 0;
		}

		$fcd_query = '';
		$fcd_query = "select FCS.*,CUFD.origin as flight_details_id
					  from flight_crs_details FCS
					  join crs_update_flight_details CUFD using(fsid)
					  where FCS.fsid in (select FCSD.fsid from flight_crs_segment_details FCSD
					  				where $date_con  
					  					AND CUFD." . $module . "_status = '1'
					  					and CUFD.avail_date = '$dep_date' 
					  					and (CUFD.avail_seat - CUFD.booked_seat - CUFD.blocked_seat) >= '1'   
					  					and FCSD.origin = '$origin' 
					  					and FCSD.destination='$destination' 
					  					and FCSD.is_domestic='$segment_type'
					  					and FCSD.active='1'
					  "; // AND CUFD.pnr != '' 
		if ($strClass != 'All') {
			//$fcd_query .= "and FCSD.class_type='$strClass' ";
		}
		$fcd_query .= " ) and FCS.trip_type=$trip_type order by fdid ";
		return $fcd_query;
	}

	/**
	 * flight search request
	 *
	 * @param $search_id unique
	 *        	id which identifies search details
	 */
	function get_flight_list(int $search_id, string $module, object $currency_obj): array
	{
		$response = [];
		$module = $module;
		$response['data'] = array();
		$response['status'] = SUCCESS_STATUS;

		/* get search criteria based on search id */
		$search_data = $this->search_data($search_id);



		// generate unique searchid string to enable caching
		$cache_search = $this->CI->config->item('cache_flight_search');
		$search_hash = $this->search_hash;

		if ($cache_search) {
			$cache_contents = $this->CI->cache->file->get($search_hash);
		}

		if ($search_data['status'] == SUCCESS_STATUS) {
			if ($cache_search == FALSE || ($cache_search === true && empty($cache_contents) == true)) {
				
				$flight_search_request = $this->search_request($search_data['data'], $module);
				//debug($flight_search_request);die;
			
				//Call API

				// debug($flight_search_request['data']['request']);die;

				$search_response = self::run_curl($flight_search_request['data']['service_url'], $flight_search_request['data']['request']);
				//debug($search_response);die("asasas");
				if ($search_response['success'] = SUCCESS_STATUS) {
					try {
						// debug($flight_search_request);exit;
						if (is_array($search_response['data']['FlightResults'])) {


							$response['status'] = SUCCESS_STATUS;
							$response['data']   = $search_response['data'];
						} else {
							$response['status'] = FAILURE_STATUS;
							$response['data']   = array();
						}
					} // catch exception
					catch (Exception $e) {
						$response['status'] = FAILURE_STATUS;
						$response['data']   = array();
					}
				}
				if ($search_data['data']['trip_type'] == 'circle') {

					if (empty($clean_format_data['data']['Search']['FlightDataList']['JourneyList'][1])) {
						$response['status'] = FAILURE_STATUS;
						$response['data']   = array();
					}

					if (empty($clean_format_data['data']['Search']['FlightDataList']['JourneyList'][0])) {
						$response['status'] = FAILURE_STATUS;
						$response['data']   = array();
					}
				}

				if ($response['status'] == SUCCESS_STATUS) {
					if ($cache_search) {
						$cache_exp = $this->CI->config->item('cache_flight_search_ttl');
						$this->CI->cache->file->save($search_hash, $response['data'], $cache_exp);
					}
				}
			} else {
				$response['data'] = $cache_contents;
			}
		} else {
			$response['status'] = FAILURE_STATUS;
			$response['data']   = array();
		}
		return $response;
	}
	function run_curl(string $url, string $request, string $type = '', string $key = ''): array
	{

		$header = array(
			'Content-Type:application/json',
		);
		$rt = $GLOBALS['CI']->session->userdata(REFRESH_TOKEN);

		if ($type == 'auth') {
			$header[] = 'Authorization: Bearer ' . $key;
		}elseif ($type == 'Stripe') {
			$header[] = 'Authorization: Stripe ' . $key;
		} else {
			if (empty($rt) == false) {
				$header[] = 'Authorization: Bearer ' . $rt;
			}
		}


		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$res = curl_exec($ch);
		

		$res = json_decode($res, true);
		curl_close($ch);

		return $res;
	}

	function search_request(array $search_data, string $module): array
	{
		$response = [];
		$search_params = $search_data;
		$response['status']	= SUCCESS_STATUS;
		$response['data']	= array();
		/** Request to be formed for search **/

		$request_params = array();
		//Converting to an array
		$search_params['from'] = (is_array($search_params['from']) ? $search_params['from'] : array($search_params['from']));
		$search_params['to'] = (is_array($search_params['to']) ? $search_params['to'] : array($search_params['to']));
		$search_params['depature'] = (is_array($search_params['depature']) ? $search_params['depature'] : array($search_params['depature']));
		$search_params['return'] = (is_array($search_params['return']) ? $search_params['return'] : array($search_params['return']));
		$segments = array();
		for ($i = 0; $i < count($search_params['from']); $i++) {
			$segments[$i]['Origin'] = $search_params['from'][$i];
			$segments[$i]['Destination'] = $search_params['to'][$i];
			if (isset($search_params['chrtime']) && !empty($search_params['chrtime'])) {
			

				//add new time in $search_params['depature'][
				$newDate = explode("T", $search_params['depature'][$i]);

				$search_params['depature'][$i]= $newDate[0] . 'T' . $search_params['chrtime'].':00';
				
				
			}
			$segments[$i]['DepartureDate'] = $search_params['depature'][$i];
			if ($search_params['type'] == 'Return') {
				$segments[$i]['ReturnDate'] = $search_params['return'][$i];
			}
		}
		$request_params['AdultCount'] =			$search_params['adult'];
		$request_params['ChildCount'] = 		$search_params['child'];
		$request_params['InfantCount'] =		$search_params['infant'];
		$request_params['JourneyType'] = 		$search_params['type'];
		$request_params['PreferredAirlines'] = 	array($search_params['carrier']);
		$request_params['CabinClass'] = 		$search_params['v_class'];
		$request_params['Mode'] = 		isset($search_params['airSearch']) ? $search_params['airSearch']: strtoupper($search_params['search_type']);
		$request_params['Mode'] = 	"EL";
		$request_params['Currency'] = get_application_currency_preference();
		$request_params['Segments'] = $segments;


		$response['data']['request'] = json_encode($request_params);
		$response['data']['service_url'] = $this->service_url . 'search';

		// debug($response);die;
	
		return $response;
	}

	function format_search_data_response(array $search_data, array $flight_search_res,object $currency_obj): array
	{
		$arrResp = [];

		$strTripTyp = $search_data['trip_type'];
		$strIsDomst = $search_data['is_domestic'];


		$strStatus  = $flight_search_res['status'];
		$arrResp['status'] = FAILURE_STATUS;
		if ($strStatus == SUCCESS_STATUS) {
			$arrResp['status'] = SUCCESS_STATUS;
			$arrFlightData = $flight_search_res['data'];

			$arrOnwardData = $this->getFlights($search_data, $arrFlightData['onward_dtldata'], $arrFlightData['onward_hdrdata'], 'Onward', $currency_obj, $flight_search_res['data']['distance']);

			$sort_item = array();
			foreach ($arrOnwardData as $row_k => $row_v) {
				$sort_item[$row_k] = floatval($row_v['price']['total_breakup']['api_total_display_fare']);
			}
			// debug($sort_item);exit;
			array_multisort($sort_item, SORT_ASC, $arrOnwardData);
			//debug($arrOnwardData); exit;
			// $arrOnwardData = array();
			$arrResp['data'] = array('Status' => SUCCESS_STATUS, 'Message' => '', 'Search' => array());
			$arrResp['data']['Search']['FlightDataList']['JourneyList'][0] = array();
			// $arrRespdata_Search_flight_data_list_journey_list =& $arrResp['data']['Search']['flight_data_list']['journey_list'];
			$arrResp['status'] = '1';
			$arrResp['search_hash'] = '';
			$arrResp['from_cache'] = '';
			$arrResp['cabin_class'] = 'Economy';
			// debug($arrOnwardData);exit;
			$arrResp['data']['Search']['FlightDataList']['JourneyList'][0] = $arrOnwardData;



			// if($strTripTyp == "circle" && $strIsDomst == 1){
			if ($strTripTyp == "circle") {
				// echo "here";exit;
				$arrReturnData = $this->getFlights($search_data, $arrFlightData['return_dtldata'], $arrFlightData['return_hdrdata'], 'Return', $currency_obj, $flight_search_res['distance']);
				$sort_item_return = array();
				foreach ($arrReturnData as $row_k => $row_v) {
					$sort_item_return[$row_k] = floatval($row_v['price']['total_breakup']['api_total_fare']);
				}
				array_multisort($sort_item_return, SORT_ASC, $arrReturnData);
				$arrResp['data']['Search']['FlightDataList']['JourneyList'][1] = $arrReturnData;
			}
		}

		return $arrResp;
	}
	function calculateTravelTime(float $distance, float $speed, string $departureTime = ''): array
	{
		$rep = [];
		// Calculate the distance

		// Calculate travel time (distance / speed) in hours
		$time = $distance / $speed;

		// Convert to hours and minutes
		$hours = floor($time);
		$minutes = ($time - $hours) * 60;
		$rep['arrivalTime'] = '';
		if ($time !== '') {
			list($departureHour, $departureMinute) = explode(':', $departureTime);

			// Calculate the arrival time
			$arrivalHour = $departureHour + $hours;
			$arrivalMinute = $departureMinute + round($minutes);
			if ($arrivalMinute >= 60) {
				$arrivalMinute -= 60;
				$arrivalHour++;
			}
			$arrivalHour = $arrivalHour % 24;

			// Format arrival time in HH:mm
			$arrivalTime = sprintf('%02d:%02d', $arrivalHour, $arrivalMinute);
			$rep['arrivalTime'] = $arrivalTime;
		}
		$rep['estimated'] = "{$hours} h  " . round($minutes) . " m";
		$rep['time'] = $time;
		return $rep;
	}


	function getFlights(array $search_data,array $arrOnwardData,array $arrHdrData,string $strType,object $currency_obj,string $distance = ''): array
	{
		$arrFlData = [];
		$arrFlt_summary = [];
		$arrFlight_fare = [];
		$depart_date = $search_data['depature'];
		if ($strType == "Return") {
			$search_data['depature'] = $search_data['return'];
		}
		$markup_data = $this->get_markup_details($search_data);
		$search_data['depature'] = $depart_date;
		$strAdult  = $search_data['adult'];
		$strChild  = $search_data['child'];
		$strInfant = $search_data['infant'];



		$arrFlgihtData = array();
		$CI = &get_instance();
		$arrData = array();
		$strTrip_Type   = $search_data['trip_type'];
		$str_isdomestic = $search_data['is_domestic'];


		if (!empty($arrOnwardData)) {
			$arrHdr_Data = $this->getHdrData($arrHdrData);


			// debug($arrHdr_Data);exit;
			// echo count($arrOnwardData); exit;
			// debug($arrOnwardData); exit;
			// debug($arrOnwardData);exit;
			for ($o = 0; $o < count($arrOnwardData); $o++) {
				//$i = 0;


				$images = json_decode($arrOnwardData[$o]['images'], true);


				if ($arrOnwardData[$o]['destination'] == '') {
					$arrOnwardData[$o]['destination'] = $search_data['to'];
				}

				$get_aircraft = $this->CI->custom_db->single_table_records("aircrafts", "*", array("origin" => $arrHdrData[$o]['aircraft']));

				$speed = $get_aircraft['data'][0]['speed'];


				$charttime = '';
				if (isset($search_data['chrtime'])) {
					$charttime = $search_data['chrtime'];
					$arrHdrData[$o]['departure_time'] = $charttime;
				}
				$time = self::calculateTravelTime($distance, $speed, $charttime);

				if ($time['arrivalTime'] != '') {
					$arrHdrData[$o]['arrival_time'] = $time['arrivalTime'];
				}
				$strFsID = $arrOnwardData[$o]['fsid'];
				$strTripType = $arrOnwardData[$o]['trip_type'];
				//debug($arrOnwardData); die;
				// if($strTrip_Type == "circle" && $str_isdomestic == false){
				// 	$TripType    = (($strTripType==0)?"Onward":"Return");
				// }else{
				// 	$TripType    = $strType;
				// }
				//$arrDtlData[] = "";
				//debug($strType); die;
				$TripType = $strType;

				$origin_code 							= $arrOnwardData[$o]['origin'];
				if ($TripType == "Return") {
					$strOrgDate 						= $arrOnwardData[$o]['departure_from_date'];
					$arrOrgDate[0]						= $strOrgDate;

					////////////////
					$return 						= $search_data['return'];
					$return							= explode("T", $return);






					$strDepDateTime 					=  $return[0] . " " . $arrHdrData[$o]['departure_time'];
					//$strDepDateTime 					=  $return[0]." ".$arrOnwardData[$o]['departure_time'];
					//$arrFlData['origin']['datetime'] 	=  $strDepDateTime;
					// debug($strDepDateTime); exit;
					$arrFlData['Origin']['DateTime'] 	=  $strDepDateTime;

					$strArrDateTime 					=  $return[0] . " " . $arrHdrData[$o]['arrival_time'];


					$strGMTDepDateTime 					=  $return[0] . " " . $arrHdrData[$o]['GMT_dep_time'];
					$strGMTArrDateTime 						=  $return[0] . " " . $arrHdrData[$o]['GMT_arr_time'];

					//$strArrDateTime 						=  $return[0]." ".$arrOnwardData[$o]['arrival_time'];
					$arrFlData['Destination']['DateTime'] 	=  $strArrDateTime;
				} else {
					$strOrgDate 						= $search_data['depature'];
					$arrOrgDate							= explode("T", $strOrgDate);
					// Jagannat B
					// $strOrgDate1 					= $search_data['return'];
					// $arrOrgDate1						= explode("T",$strOrgDate1);
					// debug($arrOrgDate1); exit;
				}
				$arrFlt_summary['origin']['loc']   		= $origin_code;
				$strOrgDate_M 							= date("m", strtotime($strOrgDate));

				$org_loc_details = $CI->db_cache_api->get_airport_details($origin_code, $strOrgDate_M);


				$strOrgDtlsTZ    = $org_loc_details['timezone_offset'];

				$dest_code 							 	= $arrOnwardData[$o]['destination'];
				if ($TripType == "Onward") {

					// debug($arrOrgDate); exit;
					$strArrivalDate 					= $this->getArrivalDate($arrOrgDate[0], $arrOnwardData[$o]['arrival_time'], $arrOnwardData[$o]['departure_time']);
					// debug($strArrivalDate); exit;
					// if($o == 0){
					// debug("Coming"); exit;
					$strDepDateTime 					=  $arrOrgDate[0] . " " . $arrHdrData[$o]['departure_time'];
					// }
					// if($o == 1){
					// 	// debug("Coming1"); exit;
					// 	$strDepDateTime 					=  $arrOrgDate1[0]." ".$arrOnwardData[$o]['departure_time'];	
					// }



					$strGMTDepDateTime 					    =  $arrOrgDate[0] . " " . $arrHdrData[$o]['GMT_dep_time'];
					$strGMTArrDateTime 						=  $arrOrgDate[0] . " " . $arrHdrData[$o]['GMT_arr_time'];



					$arrFlData['origin']['datetime'] 	=  $strDepDateTime;
					// debug($strDepDateTime); exit;
					$arrFlData['Origin']['DateTime'] 	=  $strDepDateTime;

					$strArrDateTime 						=  $strArrivalDate . " " . $arrHdrData[$o]['arrival_time'];

					$arrFlData['Destination']['DateTime'] 	=  $strArrDateTime;
				} else {
					$strArrivalDate 					= $arrOnwardData[$o]['departure_to_date'];
				}
				$strArrDate_M 							= date("m", strtotime($strArrivalDate));

				$des_loc_details = $CI->db_cache_api->get_airport_details($dest_code, $strArrDate_M);
				// debug($des_loc_details);exit;
				$strDestDtlsTZ   = @$des_loc_details['timezone_offset'];


				// $arrFlData['journey_number'] 		=  $strType;
				$arrFlData['journey_number'] 		=  $TripType;
				$arrFlData['origin']['loc'] 		=  $origin_code;
				$arrFlData['Origin']['AirportCode'] =  $origin_code;
				$arrFlData['Origin']['fsid'] 		=  $strFsID;
				$arrFlData['Origin']['CityName'] 	=  $org_loc_details['airport_city'];
				$arrFlData['Origin']['AirportName'] =  $org_loc_details['airport_city'];
				$arrFlData['origin']['city'] 		=  $org_loc_details['airport_city'];


				// $strDepDateTime 					=  $arrOrgDate[0]." ".$arrOnwardData[$o]['departure_time'];
				// $arrFlData['origin']['datetime'] 	=  $strDepDateTime;
				// // debug($strDepDateTime); exit;
				// $arrFlData['Origin']['DateTime'] 	=  $strDepDateTime;


				$arrFlData['origin']['date'] 		=  $arrOrgDate[0];
				$arrFlData['origin']['time'] 		=  $arrHdrData[$o]['departure_time'];
				$arrFlData['origin']['GMT_dep_time'] 		=  $arrHdrData[$o]['GMT_dep_time'];

				$arrFlData['origin']['fdtv'] 		=  "";
				$arrFlData['Origin']['FDTV'] 		=  "";


				$arrFlData['Origin']['GMT_dep_time'] 		=  $arrHdrData[$o]['GMT_dep_time'];


				$arrFlData['destination']['loc'] 		=  $arrOnwardData[$o]['destination'];
				$arrFlData['destination']['city'] 		=  $des_loc_details['airport_city'];


				$strArrDateTime 						=  $strArrivalDate . " " . $arrHdrData[$o]['arrival_time'];

				$arrFlData['destination']['datetime'] 	=  $strArrDateTime;
				$arrFlData['destination']['date'] 		=  $strArrivalDate;
				$arrFlData['destination']['time'] 		=  $arrHdrData[$o]['arrival_time'];
				$arrFlData['destination']['GMT_arr_time'] 		=  $arrHdrData[$o]['GMT_arr_time'];
				$arrFlData['destination']['charter_time'] 		=  $time['estimated'];
				$arrFlData['destination']['charter_tot_time'] 		=  $time['time'];





				$arrFlData['destination']['fdtv'] 		=  "";
				$arrFlData['Destination']['AirportCode'] 		=  $arrOnwardData[$o]['destination'];
				$arrFlData['Destination']['CityName'] 		=  $des_loc_details['airport_city'];
				$arrFlData['Destination']['AirportName'] 		=  $des_loc_details['airport_city'];
				$arrFlData['Destination']['GMT_arr_time'] 		=  $arrHdrData[$o]['GMT_arr_time'];

				// $arrFlData['Destination']['DateTime'] 	=  $strArrDateTime;

				$arrFlData['Destination']['FATV'] 		=  "";

				$departure_dt_tz = $strDepDateTime . $strOrgDtlsTZ;
				$arrival_dt_tz   = $strArrDateTime . $strDestDtlsTZ;
				// echo $departure_dt_tz."<br/>";
				// echo $arrival_dt_tz."<br/>";
				$duration = calculate_duration($departure_dt_tz, $arrival_dt_tz); // seconds
				// debug($arrHdr_Data[$strFsID]);exit;

				$arrFlData['duration_seconds'] 		=  $duration;
				$arrFlData['duration'] 				=  get_time_duration_label($duration);
				$arrFlData['operator_code'] 		=  $arrOnwardData[$o]['carrier_code'];
				$arrFlData['operator_name'] 		=  $arrOnwardData[$o]['airline_name'];
				$arrFlData['flight_number'] 		=  $arrOnwardData[$o]['flight_num'];
				$arrFlData['cabin_class'] 			=  $arrOnwardData[$o]['class_type'];
				$arrFlData['is_leg'] 				=  "";
				$arrFlData['operator_class'] 		=  $arrOnwardData[$o]['class_type'];
				$arrFlData['class']['name'] 		=  $arrOnwardData[$o]['class_type'];
				$arrFlData['class']['description'] 	=  $arrOnwardData[$o]['class_type'];

				$arrFlData['OperatorCode'] 		=  $arrOnwardData[$o]['carrier_code'];
				$arrFlData['DisplayOperatorCode'] = '';
				$arrFlData['OperatorName'] 		=  $arrOnwardData[$o]['airline_name'];
				$arrFlData['FlightNumber'] 		=  $arrOnwardData[$o]['flight_num'];
				$arrFlData['FlightDetailsId'] 		=  $arrOnwardData[$o]['flight_details_id'];
				// debug($arrHdr_Data[$o][$strFsID]);exit;
				$arrFlData['show_meal'] 		=  $arrHdr_Data[$strFsID]['show_meal'];
				$arrFlData['show_baggage'] 		=  $arrHdr_Data[$strFsID]['show_baggage'];
				$arrFlData['show_seat'] 		=  $arrHdr_Data[$strFsID]['show_seat'];

				$arrFlData['CabinClass'] 			=  $arrOnwardData[$o]['class_type'];
				// debug($arrFlData);exit;
				$ava = $arrHdrData[$o]['avail_seat'] - $arrHdrData[$o]['booked_seat'] - $arrHdrData[$o]['blocked_seat'];
				$arrFlData['Attr']				= array("AvailableSeats" => $ava);

				//$arrFlData['Attr']				= array("AvailableSeats" => $arrHdrData[$o]['seats']);



				$arrFlData['baggage_info'] = $arrHdrData[$o]['baggage_info'];
				$arrFlData['fare_rule'] = $arrHdrData[$o]['fare_rule'];

				$prim_image = $images[0];
				$images_array = $images;
				$arrFlData['cancellation_percentage'] = $arrHdrData[$o]['cancellation_percentage'];

				$arrFlData['prime_image'] = $prim_image;
				$arrFlData['images_array'] = $images_array;


				//debug($arrFlData); die;
				$arrData[$strFsID][] = $arrFlData;
				// $i++;
			}

			if (!empty($arrData)) {
				$strFlCnt = 0;
				if (valid_array($arrData)) {

					foreach ($arrData as $FKey => $F_Val) {
						// echo count($F_Val);exit;
						$arrFlight_Details_details = array();
						$arrFlight_Details_summary = array();
						for ($fv = 0; $fv < count($F_Val); $fv++) {
							//debug($FKey);
							// debug(count($F_Val)); exit();
							$FVal     = $F_Val[$fv];

							$arrFirst = $FVal;
							$arrEnd   = $FVal;
							// debug($FKey);exit;
							$strFsId  = $FKey;

							$arrDataH = $arrHdr_Data[$FKey];

							// debug($arrDataH);exit;


							if ($search_data['search_type'] == 'ch') {
								$charter_bf = $arrDataH['charter_basefare'];
								$charter_tax = $arrDataH['charter_tax'];
								$charter_vat = $arrDataH['charter_vat'];
								$min  = $arrData[$FKey][0]['destination']['charter_tot_time'];
								$estimated_fare = round((($charter_bf + $charter_tax + $charter_vat) * ($min)), 2);
								$arrFlight_fare['fare']['estimated_fare']  = $estimated_fare;
							} else {
								$tax_breakup = json_decode($arrDataH['tax_breakup'], true);
								$strAdultFare  = $arrDataH['adult_basefare'];
								$strChildFare  = $arrDataH['child_basefare'];
								$strInfantFare = $arrDataH['infant_basefare'];

								$pax = array();

								$adt_tax_tot = 0;
								$inf_tax_tot = 0;
								$chd_tax_tot = 0;
								$tax_breakup_total = array();
								if (valid_array($tax_breakup)) {

									// debug($tax_breakup);exit;
									foreach ($tax_breakup as $t_key => $t_val) {

										foreach ($t_val as $tt_key => $tt_val) {


											$tax_value = 0;
											$per_tax_value = 0;
											if ($GLOBALS['CI']->entity_user_group == 1) {
												$var = '_local';
											} else {
												$var = '';
											}

											if ($t_key == 'adt' . $var) {
												$tax_value =  ($this->cal_tax($tt_val['value_type'], $tt_val['value'], $strAdultFare)) * ($strAdult);

												$adt_tax_tot += $tax_value;

												$per_tax_value =  ($this->cal_tax($tt_val['value_type'], $tt_val['value'], $strAdultFare));


												$pax['adult_tax'] += $per_tax_value;
											} else if ($t_key == 'inf' . $var) {
												$tax_value =  ($this->cal_tax($tt_val['value_type'], $tt_val['value'], $strInfantFare)) * ($strInfant);
												$inf_tax_tot += $tax_value;

												$per_tax_value =  ($this->cal_tax($tt_val['value_type'], $tt_val['value'], $strInfantFare));

												$pax['infant_tax'] += $per_tax_value;
											} else if ($t_key == 'child' . $var) {
												$tax_value =  ($this->cal_tax($tt_val['value_type'], $tt_val['value'], $strChildFare)) * ($strChild);
												$chd_tax_tot += $tax_value;

												$per_tax_value =  ($this->cal_tax($tt_val['value_type'], $tt_val['value'], $strChildFare));
												$pax['child_tax'] += $per_tax_value;
											}
											if (isset($tax_breakup_total[$tt_key])) {
												$tax_breakup_total[$tt_key] += $tax_value;
											} else {
												$tax_breakup_total[$tt_key] = $tax_value;
											}
										}
									}
								}

								$adt_tax_tot = $arrDataH['adult_tax'];
								$inf_tax_tot = $arrDataH['infant_tax'];
								$chd_tax_tot = $arrDataH['child_tax'];

								//Adding Season Markup
								$total_temp_adult_fare = $strAdultFare + $adt_tax_tot;
								$total_temp_child_fare = $strChildFare + $chd_tax_tot;
								$total_temp_infant_fare = $strInfantFare + $inf_tax_tot;
								if (valid_array($markup_data['season_details'][0])) {
									if ($markup_data['season_details'][0]['percentage'] != "100%") {
										$percentage = str_replace("%", "", $markup_data['season_details'][0]['percentage']);
										$percentage = $percentage - 100;
										$tapercenatge = $total_temp_adult_fare * $percentage / 100;
										$total_temp_adult_fare += $tapercenatge;
										$tcpercenatge = $total_temp_child_fare * $percentage / 100;
										$total_temp_child_fare += $tcpercenatge;
										$tipercenatge = $total_temp_infant_fare * $percentage / 100;
										$total_temp_infant_fare += $tipercenatge;
									}
								}
								if (valid_array($markup_data['week_details'][0])) {
									if ($markup_data['week_details'][0]['percentage'] != "100%") {
										$percentage = str_replace("%", "", $markup_data['week_details'][0]['percentage']);
										$percentage = $percentage - 100;
										$tapercenatge = $total_temp_adult_fare * $percentage / 100;
										$total_temp_adult_fare += $tapercenatge;
										$tcpercenatge = $total_temp_child_fare * $percentage / 100;
										$total_temp_child_fare += $tcpercenatge;
										$tipercenatge = $total_temp_infant_fare * $percentage / 100;
										$total_temp_infant_fare += $tipercenatge;
									}
								}
								if (valid_array($markup_data['holiday_details'][0])) {
									if ($markup_data['holiday_details'][0]['percentage'] != "100%") {
										$percentage = str_replace("%", "", $markup_data['holiday_details'][0]['percentage']);
										$percentage = $percentage - 100;
										$tapercenatge = $total_temp_adult_fare * $percentage / 100;
										$total_temp_adult_fare += $tapercenatge;
										$tcpercenatge = $total_temp_child_fare * $percentage / 100;
										$total_temp_child_fare += $tcpercenatge;
										$tipercenatge = $total_temp_infant_fare * $percentage / 100;
										$total_temp_infant_fare += $tipercenatge;
									}
								}
								if (valid_array($markup_data['time_before_booking_details'][0])) {
									if ($markup_data['time_before_booking_details'][0]['percentage'] != "100%") {
										$percentage = str_replace("%", "", $markup_data['time_before_booking_details'][0]['percentage']);
										$percentage = $percentage - 100;
										$tapercenatge = $total_temp_adult_fare * $percentage / 100;
										$total_temp_adult_fare += $tapercenatge;
										$tcpercenatge = $total_temp_child_fare * $percentage / 100;
										$total_temp_child_fare += $tcpercenatge;
										$tipercenatge = $total_temp_infant_fare * $percentage / 100;
										$total_temp_infant_fare += $tipercenatge;
									}
								}

								if ($strAdult > 0) {
									$total_adult_markup = $total_temp_adult_fare - $strAdultFare - $adt_tax_tot;
								}
								if ($strChild > 0) {
									$total_child_markup = $total_temp_child_fare - $strChildFare - $chd_tax_tot;
								}
								if ($strInfant > 0) {
									$total_infant_markup = $total_temp_infant_fare - $strInfantFare - $inf_tax_tot;
								}

								//debug($tax_breakup_total);die;

								//$tax_breakup_total['Taxes_and_Charges'] = $tax_breakup_total['Taxes_and_Charges'];
								$tax_breakup_total['Taxes_and_Charges'] = get_converted_currency_value($currency_obj->force_currency_conversion($tax_breakup_total['Taxes_and_Charges']));
								$tax_breakup_total['VAT'] = get_converted_currency_value($currency_obj->force_currency_conversion($tax_breakup_total['VAT']));
								$tax_breakup_total['GST'] = get_converted_currency_value($currency_obj->force_currency_conversion($tax_breakup_total['GST']));
								$strAdultFare += $total_adult_markup;
								$strChildFare += $total_child_markup;
								$strInfantFare += $total_infant_markup;

								// if($_SERVER['REMOTE_ADDR']=='157.48.122.222'){
								// debug($pax); exit;
								// }
								$pax['adult_tax'] = $adt_tax_tot;
								$pax['adult'] = $strAdultFare;
								$pax['child'] = $strChildFare;
								$pax['infant'] = $strInfantFare;
								$pax['infant_tax'] = $inf_tax_tot;
								$pax['child_tax'] = $chd_tax_tot;

								$strTotalFare  = ($strAdultFare * $strAdult) + ($strChildFare * $strChild) + ($strInfantFare * $strInfant);
								$strTotalTax   = ($adt_tax_tot * $strAdult) + ($inf_tax_tot * $strInfant) + ($chd_tax_tot * $strChild);

								$api_total_display_fare = round($strTotalFare + $strTotalTax);
								//debug($api_total_display_fare);die;
								//debug($currency_obj);die;

								$arrFlight_fare['price']['api_currency']  			= $arrDataH['crs_currency'];

								//$arrFlight_fare['price']['api_total_display_fare']  = $api_total_display_fare;
								//$arrFlight_fare['price']['total_breakup']['api_total_tax']  	= $strTotalTax;
								//$arrFlight_fare['price']['total_breakup']['api_total_fare']  	= $strTotalFare;


								$arrFlight_fare['price']['api_total_display_fare']  = get_converted_currency_value($currency_obj->force_currency_conversion($api_total_display_fare));
								$arrFlight_fare['price']['total_breakup']['api_total_tax']  	= get_converted_currency_value($currency_obj->force_currency_conversion($strTotalTax));
								$arrFlight_fare['price']['total_breakup']['api_total_fare']  	= get_converted_currency_value($currency_obj->force_currency_conversion($strTotalFare));

								$arrFlight_fare['price']['total_breakup']['tax_breakup']  	= $tax_breakup_total;
								//debug($arrFlight_fare);exit;
								$arrFlight_fare['price']['pax_breakup']  = $pax;
								$arrFlight_fare['fare'][0] = $arrFlight_fare['price'];

								$arrFlight_fare['PaxWise']['Adult']  = $strAdult;
								$arrFlight_fare['PaxWise']['Child']  = $strChild;
								$arrFlight_fare['PaxWise']['Infant']  = $strInfant;
								$arrFlight_fare['PaxWise'][0]['Taxes']  = $strTotalTax;
							}





							// debug($arrEnd);exit;
							$strDepDateTime = $arrFirst['origin']['datetime'];
							$strArrDateTime = $arrEnd['destination']['datetime'];
							$strJrnyJNumber   						= ($FVal['journey_number'] == "Onward") ? 0 : 1;
							$arrFlt_summary['journey_number'] 		= $strJrnyJNumber;
							$origin_code 							= $arrFirst['origin']['loc'];
							$strOrgDate 							= $arrFirst['origin']['date'];
							$arrFlt_summary['origin']['loc']   		= $origin_code;
							$strOrgDate_M 							= date("m", strtotime($strOrgDate));
							//	error_reporting(E_ALL);
							$org_loc_details = $CI->db_cache_api->get_airport_details($origin_code, $strOrgDate_M);
							//debug($this->CI->db->last_query());exit('ggg');
							$arrFlt_summary['origin']['city']  		= $org_loc_details['airport_city'];
							$arrFlt_summary['origin']['datetime']  	= $strDepDateTime;
							$arrFlt_summary['origin']['date']  	 	= $strOrgDate;
							$arrFlt_summary['origin']['time']  	 	= $arrFirst['origin']['time'];
							$arrFlt_summary['origin']['fdtv']  	 	= "";

							$dest_code 							 	= $arrEnd['destination']['loc'];
							$strArrivalDate 						= $arrEnd['destination']['datetime'];
							$strArrDate_M 							= date("m", strtotime($strArrivalDate));
							$des_loc_details = $CI->db_cache_api->get_airport_details($dest_code, $strArrDate_M);

							$arrFlt_summary['destination']['loc']   		= $arrEnd['destination']['loc'];
							$arrFlt_summary['destination']['city']  		= $des_loc_details['airport_city'];
							$arrFlt_summary['destination']['datetime']  	= $strArrDateTime;
							$arrFlt_summary['destination']['date']  		= $arrEnd['destination']['date'];
							$arrFlt_summary['destination']['time']  		= $arrEnd['destination']['time'];
							$arrFlt_summary['destination']['fdtv']  		= "";

							$strOrgDtlsTZ    = $org_loc_details['timezone_offset'];
							$strDestDtlsTZ   = $des_loc_details['timezone_offset'];

							$departure_dt_tz = $strDepDateTime . $strOrgDtlsTZ;
							$arrival_dt_tz   = $strArrDateTime . $strDestDtlsTZ;
							$duration = calculate_duration($departure_dt_tz, $arrival_dt_tz); // seconds
							$baggage = json_decode($FVal['baggage_info'], 1);
							if (valid_array($baggage)) {
								$cabin_baggage = $baggage['cabin_baggage'];
								$chekin_baggage = $baggage['chechin_baggage'];
							} else {
								$cabin_baggage = 0;
								$chekin_baggage = 0;
							}
							$arrFlt_summary['operator_code']  			= $arrHdr_Data[$FKey]['carrier_code'];
							$arrFlt_summary['display_operator_code']  	= $arrHdr_Data[$FKey]['carrier_code'];
							$arrFlt_summary['operator_name'] 			= $arrHdr_Data[$FKey]['airline_name'];
							$arrFlt_summary['flight_number'] 			= $arrHdr_Data[$FKey]['flight_num'];
							$arrFlt_summary['cabin_class'] 				= $arrHdr_Data[$FKey]['class_type'];
							$arrFlt_summary['fare_class'] 				= "";
							$arrFlt_summary['no_of_stops'] 				= $arrHdr_Data[$FKey]['no_of_stops'];
							$arrFlt_summary['is_leg'] 					= ($arrHdr_Data[$FKey]['no_of_stops'] == 0) ? 0 : 1;
							$arrFlt_summary['cabin_bag'] 				= $cabin_baggage;
							$arrFlt_summary['hand_bag'] 				= $chekin_baggage;
							$arrFlt_summary['duration_seconds'] 		= $duration;
							$arrFlt_summary['duration'] 				= get_time_duration_label($duration);

							// echo $strJrnyJNumber."<br/>";
							//$arrFlight_Details['details'][$strJrnyJNumber][] = $FVal;
							//$arrFlight_Details['summary'][$strJrnyJNumber] = $arrFlt_summary;
							if ($strTrip_Type == "circle") {
								$str_isdomestic = true;
							}
							$arrFlight_Details_details[$strJrnyJNumber][] = $FVal;
							if ($strTrip_Type == "circle" && $str_isdomestic == false) {
								$arrFlight_Details_summary[] = $arrFlt_summary;
							} else {
								$arrFlight_Details_summary[$strJrnyJNumber] = $arrFlt_summary;
								$arrFlight_Details_summary[$strJrnyJNumber]['fare_rule'] = $FVal['fare_rule'];
								$arrFlight_Details_summary[$strJrnyJNumber]['cancellation_percentage'] = $FVal['cancellation_percentage'];
								$arrFlight_Details_summary[$strJrnyJNumber]['baggage_info'] = $FVal['baggage_info'];
							}


							// 	$arrFlight_Details_details

							// 	$arrFlgihtData[$strFlCnt]['flight_details']['baggage_info']	= json_decode($FVal['baggage_info'],1);
							// $arrFlgihtData[$strFlCnt]['flight_details']['fare_rule']	= $arrFlight_Details_details;
							// debug($arrFlight_Details_details); exit;
							//debug($arrFlgihtData);
							// debug(json_decode($FVal['baggage_info'],1)); exit;
						}

						// exit;
						$arrFlgihtData[$strFlCnt] = $arrFlight_fare;

						$arrFlgihtData[$strFlCnt]['flight_details']['details']	= $arrFlight_Details_details;


						$arrFlgihtData[$strFlCnt]['flight_details']['summary']	= $arrFlight_Details_summary;
						$arrFlgihtData[$strFlCnt]['token'] 			= serialized_data($arrFlgihtData);
						$arrFlgihtData[$strFlCnt]['flight_details_id'] 			= $arrDataH['flight_details_id'];
						$arrFlgihtData[$strFlCnt]['token_key'] 		= md5($arrFlgihtData[$strFlCnt]['token']);

						$arrFlgihtData[$strFlCnt]['show_meal'] 		= $arrDataH['show_meal'];
						$arrFlgihtData[$strFlCnt]['show_baggage'] 		= $arrDataH['show_baggage'];
						$arrFlgihtData[$strFlCnt]['show_seat'] 		= $arrDataH['show_seat'];

						$arrFlgihtData[$strFlCnt]['booking_source'] = PROVAB_FLIGHT_CRS_BOOKING_SOURCE;

						$strFlCnt++;
					}
				}
			}
			// debug($arrFlgihtData);exit;
		}



		return $arrFlgihtData;
	}

	function cal_tax(string $value_type, float $value, float $base_fare): string
	{

		if ($value_type == 'percentage') {
			//%
			$tax_val = ($base_fare / 100) * $value;
		} else {
			//plus
			$tax_val = $value;
		}
		return number_format($tax_val, 2, '.', '');
	}
	function getArrivalDate(string $strDepDate, string $strAriveTime, string $strDepTime): string
	{
		//$strAriveTime = "01:39:00";
		$strTimeTaken = strtotime($strAriveTime) - strtotime($strDepTime);
		//echo $strAriveTime."-".$strDepTime." = ".$strTimeTaken;exit;
		$strArrivalDate = $strDepDate;
		if ($strTimeTaken <= 0) {
			$strArrivalDate = date('Y-m-d', strtotime($strDepDate . ' +1 day'));
		}
		return $strArrivalDate;
	}

	function getHdrData(array $arrHdrData): array
	{
		if (!empty($arrHdrData)) {
			$arrData = array();
			foreach ($arrHdrData as $DKey => $DVal) {
				$strFsID = $DVal['fsid'];
				$arrData[$strFsID] = $DVal;
			}
		}
		return $arrData;
	}

	function format_search_data_response_BAK(array $search_data, array $flight_search_res): array
	{
		$arrResp = [];
		$arrRespData = [];
		$arrFlgihtDetails = [];
		$arrFlt_summary = [];
		$arrFlightSegmentDtls = [];
		$strStatus = $flight_search_res['status'];
		$arrResp['status'] = FAILURE_STATUS;
		if ($strStatus == SUCCESS_STATUS) {
			$arrResp['status'] = SUCCESS_STATUS;
			$arrFltData = $flight_search_res['data'];
			$arrFsid 	= array();
			for ($i = 0; $i < count($arrFltData); $i++) {
				$arrJourneyData  = $arrFltData[$i];
				//debug($arrJourneyData);exit;
				$arrFsId = array();
				$arrRespData[$i] = array();
				$jrnyNo = 0;
				$arrFsID = array();
				for ($seg = 0; $seg < count($arrJourneyData); $seg++) {
					$strFsId = $arrJourneyData[$seg]['fsid'];
					//$arrRespData[$i][$strFsId][] = $arrJourneyData[$seg]['fdid'];
					$arrFsID[$strFsId][] = "";
					if (!in_array($strFsId, $arrFsid)) {
						$arrFsid[] = $strFsId;
					}
					//$arrFlgihtDetails = array();
					$arrFlgihtDetails[$strFsId][]['journey_number'] = ($i == 0) ? "Onward" : "Return";


					$arrFlt_summary[$strFsId] = array();
					$arrFlt_summary[$strFsId]['journey_number'] 		= $jrnyNo;
					$arrFlt_summary[$strFsId]['origin']['loc']   		= $arrJourneyData[$seg]['hdrOrigin'];
					$arrFlt_summary[$strFsId]['origin']['city']  		= $arrJourneyData[$seg]['hdrOrigin'];
					$arrFlt_summary[$strFsId]['origin']['datetime']  	= $search_data['depature'] . "-" . $arrJourneyData[$seg]['departure_time'];


					$arrFlt_summary[$strFsId]['origin']['date']  	 	= $search_data['depature'];
					$arrFlt_summary[$strFsId]['origin']['time']  	 	= $arrJourneyData[$seg]['departure_time'];
					$arrFlt_summary[$strFsId]['origin']['fdtv']  	 	= "";

					$arrFlt_summary[$strFsId]['destination']['loc']   		= $arrJourneyData[$seg]['hdrDest'];
					$arrFlt_summary[$strFsId]['destination']['city']  		= $arrJourneyData[$seg]['hdrDest'];
					$arrFlt_summary[$strFsId]['destination']['datetime']  	= $search_data['depature'] . "-" . $arrJourneyData[$seg]['arrival_time'];
					$arrFlt_summary[$strFsId]['destination']['date']  		= $search_data['depature'];
					$arrFlt_summary[$strFsId]['destination']['time']  		= $arrJourneyData[$seg]['arrival_time'];
					$arrFlt_summary[$strFsId]['destination']['fdtv']  		= "";

					$arrFlt_summary[$strFsId]['operator_code']  			= $arrJourneyData[$seg]['carrier_code'];
					$arrFlt_summary[$strFsId]['display_operator_code']  	= "";
					$arrFlt_summary[$strFsId]['operator_name'] 				= $arrJourneyData[$seg]['airline_name'];
					$arrFlt_summary[$strFsId]['flight_number'] 				= $arrJourneyData[$seg]['flight_num'];
					$arrFlt_summary[$strFsId]['cabin_class'] 				= $arrJourneyData[$seg]['class_type'];
					$arrFlt_summary[$strFsId]['fare_class'] 				= "";
					$arrFlt_summary[$strFsId]['no_of_stops'] 				= (count($arrFsID[$strFsId]) == 1) ? 0 : count($arrFsID[$strFsId]) - 1;
					$arrFlt_summary[$strFsId]['is_leg'] 					= (count($arrFsID[$strFsId]) == 1) ? 0 : 1;
					$arrFlt_summary[$strFsId]['cabin_bag'] 					= "";
					$arrFlt_summary[$strFsId]['hand_bag'] 					= "";
					$arrFlt_summary[$strFsId]['duration_seconds'] 			= $arrJourneyData[$seg]['departure_time'] . "--" . $arrJourneyData[$seg]['arrival_time'];
					$arrFlt_summary[$strFsId]['duration'] 					= $arrJourneyData[$seg]['departure_time'] . "--" . $arrJourneyData[$seg]['arrival_time'];

					$arrFlightSegmentDtls['summary'][0] = $arrFlt_summary[$strFsId];



					$arrFlightSegmentDtls['details'] = $arrFlgihtDetails[$strFsId];
					$arrRespData[$i][$strFsId]['flight_details'] = $arrFlightSegmentDtls;
				}
				$arrData = $arrRespData[$i];
				/*$arrNewData = array();
				foreach ($arrData as $arrKey => $arrVal) {
					$arrNewData[] = $arrVal;
				}*/
				debug($arrData);
				exit;
				$arrResp['data'][$i] = $arrData;
			}
			debug($arrResp);
			exit;
		}
		exit;
		return $arrRespData;
	}

	/**
	 * Update markup currency for price object of flight
	 *
	 * @param object $price_summary
	 * @param object $currency_obj
	 */
	function update_markup_currency(array &$price_summary,object &$currency_obj,bool $level_one_markup = false, bool $current_domain_markup = true,float $multiplier = 1,array $specific_markup_config = []): array
	{

		$markup_list = array('api_total_display_fare', 'OfferedFare');
		$markup_summary = array();
		//debug($price_summary);
		$temp_price = array();
		foreach ($price_summary as $__k => $__v) {
			// debug($__v);exit;
			if (is_numeric($__v) == true) {
				$ref_cur = $currency_obj->force_currency_conversion($__v);	//Passing Value By Reference so dont remove it!!!
				//debug($multiplier); die;
				$price_summary[$__k] = $ref_cur['default_value'];			//If you dont understand then go and study "Passing value by reference"

				if (in_array($__k, $markup_list)) {

					$temp_price = $currency_obj->get_currency($__v, true, $level_one_markup, $current_domain_markup, $multiplier, $specific_markup_config);
				} elseif (is_array($__v) == false) {
					$temp_price = $currency_obj->force_currency_conversion($__v);
				} else {
					$temp_price['default_value'] = $__v;
				}
				//if($_SERVER['REMOTE_ADDR']=="14.97.94.42"){
				//	debug($temp_price); die;
				//}
				//	if($multiplier==2){
				//		$markup_summary[$__k] = $temp_price['default_value']-$temp_price['markup_data']/2;
				//	}
				//	else{
				$markup_summary[$__k] = $temp_price['default_value'];
				//	}

			}
		}
		if ($_SERVER['REMOTE_ADDR'] == "14.97.94.42") {
			//debug($markup_summary); die;
		}
		// 		 debug($markup_summary);
		// 		debug($price_summary['api_total_display_fare']); die;
		//Markup
		//PublishedFare
		$Markup = 0;
		$price_summary['_Markup'] = 0;


		// if (isset($markup_summary['OfferedFare'])) {
		// 	$Markup = $markup_summary['OfferedFare'] - $price_summary['OfferedFare'];
		// 	$markup_summary['PublishedFare'] = $markup_summary['PublishedFare'] + $Markup;
		// }
		//debug($temp_price);
		if (isset($markup_summary['api_total_display_fare'])) {
			//$Markup = $markup_summary['api_total_display_fare'] - $price_summary['api_total_display_fare'];
			$Markup = $temp_price['markup_data'];
			$markup_summary['PublishedFare'] = $markup_summary['api_total_display_fare'];
		}
		$markup_summary['_Markup'] = $Markup;
		// if($multiplier==2){
		// 	$markup_summary['api_total_display_fare']=$markup_summary['api_total_display_fare']-$markup_summary['_Markup']/2;
		// 	$markup_summary['PublishedFare']=$markup_summary['api_total_display_fare']-$markup_summary['_Markup']/2;
		// }

		//debug($markup_summary);exit;
		if ($_SERVER['REMOTE_ADDR'] == "14.97.94.42") {
			//debug($markup_summary); die;
		}
		return $markup_summary;
	}



	/**
	 * get total price from summary object
	 *
	 * @param object $price_summary
	 */
	function total_price(array $price_summary, bool $retain_commission = false, object|null $currency_obj = null): float
	{

		$com = 0;
		$com_tds = 0;
		if ($retain_commission == false) {
			$com = 0;
			$com_tds += floatval($currency_obj->calculate_tds($price_summary['AgentCommission']));
			$com_tds += floatval($currency_obj->calculate_tds($price_summary['PLBEarned']));
			$com_tds += floatval($currency_obj->calculate_tds($price_summary['IncentiveEarned']));
		} else {
			$com += floatval($price_summary['AgentCommission']);
			$com += floatval($price_summary['PLBEarned']);
			$com += floatval($price_summary['IncentiveEarned']);
			$com_tds = 0;
		}
		return (floatval($price_summary['OfferedFare']) + $com + $com_tds);
	}
	/**
	 * Process booking
	 *
	 * @param string $book_id
	 * @param array $booking_params
	 *        	Needed as token is not saved in database
	 */
	// function process_booking($book_id, $temp_booking) {}


	/**
	 * booking_url to be used
	 */
	function booking_url(int $search_id): string
	{
		return base_url() . 'index.php/flight/booking/' . intval($search_id);
	}

	///////////Done by Jagannath
	public function search_data_in_preferred_currency(array $search_result, object $currency_obj): array
	{

		//debug('ramya');exit;
		// echo "Hii";
		// exit;

		$flights = $search_result['JourneyList'];
		//debug($flights);exit;
		$flight_list = array();
		foreach ($flights as $fk => $fv) {
			foreach ($fv as $list_k => $list_v) {
				//debug($currency_obj); exit;
				$flight_list[$fk][$list_k] = $list_v;
				$flight_list[$fk][$list_k]['FareDetails'] = $this->preferred_currency_fare_object($list_v['price'], $currency_obj);
				// debug($flight_list[$fk][$list_k]['FareDetails']); exit;
				$flight_list[$fk][$list_k]['PassengerFareBreakdown'] = $this->preferred_currency_paxwise_breakup_object($list_v, $currency_obj);
				// debug($flight_list[$fk][$list_k]['PassengerFareBreakdown']); exit;
			}
		}
		$search_result['JourneyList'] = $flight_list;
		//debug($search_result); die;
		return $search_result;
	}

	private function preferred_currency_fare_object(array $fare_details, object $currency_obj, string $default_currency = ''): array
	{
		//debug($fare_details);
		if (isset($fare_details['TotalDisplayFare']) == true && isset($fare_details['PriceBreakup']) == true) {
			$base_fare = 				$fare_details['PriceBreakup']['BasicFare'];
			$tax = 						$fare_details['PriceBreakup']['Tax'];
			$published_fare = 			$fare_details['TotalDisplayFare'];
			$agent_commission = 		$fare_details['PriceBreakup']['AgentCommission'];
			$agent_tds_on_commission =	$fare_details['PriceBreakup']['AgentTdsOnCommision'];
		} else {
			$base_fare = 				$fare_details['BaseFare'];
			$tax = 						$fare_details['Tax'];
			$published_fare = 			$fare_details['PublishedFare'];
			$agent_commission = 		$fare_details['AgentCommission'];
			$agent_tds_on_commission =	$fare_details['AgentTdsOnCommision'];
		}


		if (!empty($fare_details['api_total_display_fare'])) {
			$base_fare = round($fare_details['api_total_display_fare']);
		}
		//debug($currency_obj);die;
		//$base_fare=$fare_details['total_breakup']['api_total_fare'];
		//$tax=$fare_details['total_breakup']['api_total_tax'];
		//debug($base_fare);die;
		$FareDetails = array();
		$FareDetails['Currency'] = 				empty($default_currency) == false ? $default_currency : get_api_data_currency();
		$FareDetails['BaseFare'] = 				get_converted_currency_value($currency_obj->force_currency_conversion($base_fare));

		//$FareDetails['Tax'] = 					get_converted_currency_value($currency_obj->force_currency_conversion($tax));
		$FareDetails['Tax'] = 					$tax;
		$FareDetails['PublishedFare'] = 		get_converted_currency_value($currency_obj->force_currency_conversion($published_fare));
		$FareDetails['AgentCommission'] = 		get_converted_currency_value($currency_obj->force_currency_conversion($agent_commission));
		$FareDetails['AgentTdsOnCommision'] = 	get_converted_currency_value($currency_obj->force_currency_conversion($agent_tds_on_commission));
		$OfferedFare = 							($FareDetails['PublishedFare'] - $FareDetails['AgentCommission']);
		$FareDetails['OfferedFare'] = 			$OfferedFare;
		//$FareDetails['TotalTaxBreakup'] = 	    $fare_details['TotalTaxBreakup'];
		$FareDetails['TotalTaxBreakup'] = 	    $fare_details['total_breakup']['tax_breakup'];
		//debug($FareDetails);exit;
		return $FareDetails;
	}
	public function preferred_currency_paxwise_breakup_object(array $fare_details, object $currency_obj): array
	{
		
		$PassengerFareBreakdown = array();
		//pax_breakup
		$pax_breakup = $fare_details['price']['pax_breakup'];

		if (isset($fare_details['PaxWise']['Adult']) &&  $fare_details['PaxWise']['Adult'] >= 1) {
			$PassengerCount = $fare_details['PaxWise']['Adult'];

			if (isset($fare_details['price'])) {
				// $base_fare = $fare_details['price']['total_breakup']['api_total_fare'];
				// $tax = $fare_details['price']['total_breakup']['api_total_tax'];
				// $total_fare = $fare_details['price']['api_total_display_fare'];
				// //unset($PassengerFareBreakdown[$k]['BasePrice']);


				// $adult = $fare_details['fare'][0]['pax_breakup']['adult'];


				$PassengerFareBreakdown['ADULT']['Tax'] = get_converted_currency_value($currency_obj->force_currency_conversion($pax_breakup['adult_tax']));
				$PassengerFareBreakdown['ADULT']['TotalPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion(($pax_breakup['adult_tax'] + $pax_breakup['adult'])));
				$PassengerFareBreakdown['ADULT']['PassengerCount'] = $PassengerCount;
				$PassengerFareBreakdown['ADULT']['BaseFare'] = get_converted_currency_value($currency_obj->force_currency_conversion($pax_breakup['adult']));
			}
		}
		if (isset($fare_details['PaxWise']['Child']) && $fare_details['PaxWise']['Child'] >= 1) {
			$PassengerCount = $fare_details['PaxWise']['Child'];
			if (isset($fare_details['price'])) {
				// $base_fare = $fare_details['total_breakup']['api_total_fare'];
				// $tax = $fare_details['total_breakup']['api_total_tax'];
				// $total_fare = $fare_details['api_total_display_fare'];
				//unset($PassengerFareBreakdown[$k]['BasePrice']);

				// $child = $fare_details['fare'][0]['pax_breakup']['child'];


				$PassengerFareBreakdown['CHILD']['Tax'] = get_converted_currency_value($currency_obj->force_currency_conversion($pax_breakup['child_tax']));
				$PassengerFareBreakdown['CHILD']['TotalPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion(($pax_breakup['child_tax'] + $pax_breakup['child'])));
				$PassengerFareBreakdown['CHILD']['PassengerCount'] = $PassengerCount;
				$PassengerFareBreakdown['CHILD']['BaseFare'] = get_converted_currency_value($currency_obj->force_currency_conversion($pax_breakup['child']));
			}
		}
		if (isset($fare_details['PaxWise']['Infant']) && $fare_details['PaxWise']['Infant'] >= 1) {
			$PassengerCount = $fare_details['PaxWise']['Infant'];
			if (isset($fare_details['price'])) {
				// $base_fare = $fare_details['total_breakup']['api_total_fare'];
				// $tax = $fare_details['total_breakup']['api_total_tax'];
				// $total_fare = $fare_details['api_total_display_fare'];
				// //unset($PassengerFareBreakdown[$k]['BasePrice']);

				// 	$infant = $fare_details['fare'][0]['pax_breakup']['infant'];


				$PassengerFareBreakdown['INFANT']['Tax'] = get_converted_currency_value($currency_obj->force_currency_conversion($pax_breakup['infant_tax']));
				$PassengerFareBreakdown['INFANT']['TotalPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion(($pax_breakup['infant_tax'] + $pax_breakup['infant'])));
				$PassengerFareBreakdown['INFANT']['PassengerCount'] = $PassengerCount;
				$PassengerFareBreakdown['INFANT']['BaseFare'] = get_converted_currency_value($currency_obj->force_currency_conversion($pax_breakup['infant']));
			}
		}


		//}
		//debug($PassengerFareBreakdown); exit;
		return $PassengerFareBreakdown;
	}
	public function format_search_response(array $search_result,object $currency_obj,int $search_id,string $module,bool $from_cache = false,string $search_hash = ''): array
	{

		$formatted_search_data = array();
		$journey_summary = $this->extract_journey_details($search_id);
		//Flight List
		$flights = $search_result['JourneyList'];
		$formatted_flight_list = array();
		$ins_token = true;
		$formatted_flight_list = $this->extract_flight_details($flights, $currency_obj, $search_id, $module, $ins_token);
		//Assigning the Data
		$formatted_search_data['booking_url'] = $this->booking_url(intval($search_id));
		//debug($formatted_search_data['booking_url']); exit; 
		$formatted_search_data['data']['JourneySummary'] = $journey_summary;
		// debug($formatted_flight_list); exit;
		$formatted_search_data['data']['Flights'] = $formatted_flight_list;

		// set session expiry time
		$session_expiry_details = $this->set_flight_search_session_expiry($from_cache, $search_hash);
		// debug($session_expiry_details); exit;
		$formatted_search_data['session_expiry_details'] = $session_expiry_details;

		return $formatted_search_data;
	}
	private function extract_journey_details(int $search_id): array
	{
		$search_data = $this->search_data($search_id);
		$search_data = $search_data['data'];
		//debug($search_data); exit;
		$PassengerConfig = array();
		$PassengerConfig['Adult'] = intval($search_data['adult']);
		$PassengerConfig['Child'] = intval($search_data['child']);
		$PassengerConfig['Infant'] = intval($search_data['infant']);
		$PassengerConfig['TotalPassenger'] = intval($search_data['total_passenger']);
		//Journey Summary
		$journey_summary = array();

		$origin = is_array($search_data['from']) ? $search_data['from'][0] : $search_data['from'];
		$destination = is_array($search_data['to']) ? end($search_data['to']) : $search_data['to'];


		$journey_summary['Origin'] = $origin;
		$journey_summary['Destination'] = $destination;

		$journey_summary['IsDomestic'] = $search_data['is_domestic'];
		$journey_summary['RoundTrip'] = $search_data['is_roundtrip'];
		$journey_summary['MultiCity'] = $search_data['is_multicity'];
		if ($search_data['is_roundtrip'] == true) {
			$journey_summary['IsDomestic'] = true;
		}
		$journey_summary['PassengerConfig'] = $PassengerConfig;
		//debug($search_data); exit;
		// changed by badri
		if ($journey_summary['IsDomestic'] == true && $journey_summary['RoundTrip'] == true) {
			$is_domestic_roundway = true;
		} else {
			$is_domestic_roundway = false;
		}
		$journey_summary['IsDomesticRoundway'] = $is_domestic_roundway;
		return $journey_summary;
	}
	public function extract_flight_details(array $flights, object $currency_obj, int $search_id, string $module, bool $ins_token = false): array
	{
		// echo "Hi";
		$formatted_flight_list = array();
		//Token Details
		$token = array(); //This will be stored in local file so less data gets transmitted
		$this->ins_token_file = time() . rand(100, 10000);
		// echo "Hi";

		foreach ($flights as $fk => $fv) {

			$formatted_flight_list[$fk] = $this->extract_flight_segment_fare_details($fv, $currency_obj, $search_id, $module, $ins_token, $token, $fk);
		}
		// echo "extract_flight_segment_fare_details"; exit;
		$ins_token === true ? $this->save_token($token) : '';

		// debug($formatted_flight_list); exit;
		return $formatted_flight_list;
	}
	function get_markup_details(array $search_data): array
	{

		$response = array();
		$departure_date = explode("T", $search_data['depature']);
		$d_date = $departure_date[0];
		$season_fare = $this->CI->flights_model->check_date_in_season($d_date);
		$week_fare = $this->CI->flights_model->check_date_in_week($d_date);
		$holiday_fare = $this->CI->flights_model->check_date_in_holiday($d_date);
		$time_booking_before_fare = $this->CI->flights_model->check_time_before_booking($d_date);
		$response['season_details'] = $season_fare;
		$response['week_details'] = $week_fare;
		$response['holiday_details'] = $holiday_fare;
		$response['time_before_booking_details'] = $time_booking_before_fare;
		return $response;
	}
	public function extract_flight_segment_fare_details(mixed $flights,object $currency_obj,int $search_id,string $module,bool $ins_token = false,array &$token = [],int $flight_index = 0): array
	{


		$flights = force_multple_data_format($flights);


		// debug($flights);exit;
		// echo "Hii"; exit;
		$flight_list = array();
		foreach ($flights as $list_k => $list_v) {


			//Pushing data into the Token
			if ($ins_token === true) {
				$tkn_key = $flight_index . $list_k;
				$this->push_token($list_v, $token, $tkn_key);
			}


			if ($list_v['flight_details']['summary'][0]['cancellation_percentage'] > 0) {
				$flight_list[$list_k]['AirlineRemark'] = ' Refundable - ' . $list_v['flight_details']['summary'][0]['cancellation_percentage'] . '% ' . 'Cancellation Charge till 8 days Before Travel Date.After that Non Refundable';
				$refundFlag = 1;
			} elseif ($list_v['flight_details']['summary'][1]['cancellation_percentage'] > 0) {
				$flight_list[$list_k]['AirlineRemark'] = ' Refundable - ' . $list_v['flight_details']['summary'][1]['cancellation_percentage'] . '% ' . 'Cancellation Charge till 8 days Before Travel Date.After that Non Refundable';
				$refundFlag = 1;
			} else {

				$flight_list[$list_k]['AirlineRemark'] = 'Non Flexi / Non Refundable';
				$refundFlag = 0;
			}

			// debug($list_v['flight_details']);exit;
			//$flight_list[$list_k]['AirlineRemark'] = $this->filter_airline_remark(@$list_v['Attr']['AirlineRemark'], $module);
			// echo "Hi"; exit;

			$flight_list[$list_k]['FareDetails'] = $this->get_fare_object($list_v, $currency_obj, $search_id, $module);
			if (isset($list_v['flight_details']['summary'][0])) {
				$flight_list[$list_k]['FareRule'] = $list_v['flight_details']['summary'][0]['fare_rule'];
				$flight_list[$list_k]['cancellation_percentage'] = $list_v['flight_details']['summary'][0]['cancellation_percentage'];
				$flight_list[$list_k]['BaggageInfo'] = $list_v['flight_details']['summary'][0]['baggage_info'];
			}

			//ReturnFlight
			if (isset($list_v['flight_details']['summary'][1])) {
				$flight_list[$list_k]['FareRule'] = $list_v['flight_details']['summary'][1]['fare_rule'];
				$flight_list[$list_k]['cancellation_percentage'] = $list_v['flight_details']['summary'][1]['cancellation_percentage'];
				$flight_list[$list_k]['BaggageInfo'] = $list_v['flight_details']['summary'][1]['baggage_info'];
			}
			//Get Meal details

			$meals = $this->CI->custom_db->single_table_records('flight_crs_meal_details', 'origin,meal_name,price,currency', array('currency' => 'USD'))['data'];
			if (valid_array($meals)) {
				$flight_list[$list_k]['Meals'] = $meals;
			}
			// debug($list_v['flight_details']['details']);exit; 
			$flight_list[$list_k]['PassengerFareBreakdown'] = $list_v['PassengerFareBreakdown'];

			$segments = $this->extract_segment_details($list_v['flight_details']['details']);

			$flight_list[$list_k]['SegmentSummary'] = $segments['segment_summary'];
			$flight_list[$list_k]['SegmentDetails'] = $segments['segment_full_details'];

			$flight_list[$list_k]['ProvabAuthKey'] = $list_v['token'];
			$flight_list[$list_k]['flight_details_id'] = $list_v['flight_details_id'];

			$flight_list[$list_k]['show_meal'] = $list_v['show_meal'];
			$flight_list[$list_k]['show_baggage'] = $list_v['show_baggage'];
			$flight_list[$list_k]['show_seat'] = $list_v['show_seat'];

			$flight_list[$list_k]['booking_source'] = PROVAB_FLIGHT_CRS_BOOKING_SOURCE;

			//Hold Ticket
			if (isset($list_v['HoldTicket']) == true) {
				$hold_ticket = $list_v['HoldTicket'];
			} else {
				$hold_ticket = false;
			}
			$flight_list[$list_k]['HoldTicket'] = $hold_ticket;

			if (isset($list_v['Token']) == true) {
				$flight_list[$list_k]['Token'] = $list_v['Token'];
			}
			if (isset($list_v['TokenKey']) == true) {
				$flight_list[$list_k]['TokenKey'] = $list_v['TokenKey'];
			}
			// $flight_list[$list_k]['Attr'] = $list_v['Attr'];
			$flight_list[$list_k]['Attr'] = array("IsRefundable" => $refundFlag, "AirlineRemark" => '');
		}
		//exit;

		// debug($flight_list); exit;
		return $flight_list;
	}
	private function push_token(array &$flight, array &$token, string $key): void
	{
		//push data inside token before adding token and key values
		$token[$key] = $flight;

		//Adding token and token key
		$flight['Token'] = serialized_data($this->ins_token_file . DB_SAFE_SEPARATOR . $key);
		$flight['TokenKey'] = md5($flight['Token']);
	}
	private function filter_airline_remark(string $AirlineRemark, string $module): string
	{
		$filtered_airline_remark = '';
		if ($module == 'b2c') {
			if (preg_match_all('~\b(special|bag|meal|meals)\b~i', $AirlineRemark) == true && preg_match_all('~\b(Series|operated|commissionable)\b~i', $AirlineRemark) == false) {
				$filtered_airline_remark = $AirlineRemark;
			}
		} else if ($module == 'b2b') {
			if (preg_match_all('~\b(special|bag|meal|meals)\b~i', $AirlineRemark) == true && preg_match_all('~\b(Series|operated)\b~i', $AirlineRemark) == false) {
				$filtered_airline_remark = $AirlineRemark;
			}
		}
		return $filtered_airline_remark;
	}
	private function get_fare_object(array $flight_details, $currency_obj, int $search_id, string $module): array
	{

		$FareDetails = array();
		$b2c_price_details = array();
		$b2b_fare_details = array();


		$api_price_details = array(
			"Currency" => $flight_details['price']['api_currency'],
			"BaseFare" => isset($flight_details['price']['total_breakup']['api_total_fare']) ? $flight_details['price']['total_breakup']['api_total_fare'] : $flight_details['fare']['estimated_fare'],
			"Tax" => $flight_details['price']['total_breakup']['api_total_tax'],
			"TotalTaxBreakup" => $flight_details['price']['total_breakup']['tax_breakup'],
			"PublishedFare" => isset($flight_details['price']['total_breakup']['api_total_fare']) ? $flight_details['price']['total_breakup']['api_total_tax'] + $flight_details['price']['total_breakup']['api_total_fare'] : $flight_details['fare']['estimated_fare'],
			"AgentCommission" => 0,
			"AgentTdsOnCommision" => 0,
			"OfferedFare" => isset($flight_details['price']['total_breakup']['api_total_fare']) ? $flight_details['price']['total_breakup']['api_total_tax'] + $flight_details['price']['total_breakup']['api_total_fare'] : $flight_details['fare']['estimated_fare']
		);
		// debug($api_price_details); exit;

		$api_price_details1 = $flight_details['price'];
		$currency_symbol = $currency_obj->get_currency_symbol($currency_obj->to_currency);
		// debug($api_price_details1);exit;
		//SPECIFIC MARKUP CONFIG DETAILS
		$specific_markup_config = array();
		$specific_markup_config = $this->get_airline_specific_markup_config($flight_details['flight_details']['details']); //Get the Airline code for setting airline-wise markup
		//Updating the Commission
		// echo "Hi"; exit; Done
		if ($module == 'b2c') {
			//B2C
			$admin_price_details = $this->update_search_markup_currency($flight_details['price'], $currency_obj, false, true, $search_id, $specific_markup_config); //B2c:DONT CHANGE

			$b2c_price_details['BaseFare'] = isset($flight_details['price']['total_breakup']['api_total_fare']) ? $api_price_details1['total_breakup']['api_total_fare'] : $flight_details['fare']['estimated_fare'];
			// $b2c_price_details['TotalTax'] = $o_Total_Tax;
			$b2c_price_details['TotalTax'] = $api_price_details1['total_breakup']['api_total_tax'];
			$b2c_price_details['TotalFare'] = isset($flight_details['price']['total_breakup']['api_total_fare']) ? $api_price_details1['total_breakup']['api_total_fare'] + $api_price_details1['total_breakup']['api_total_tax'] : $flight_details['fare']['estimated_fare'];
			$b2c_price_details['total_tax_breakup'] = $api_price_details1['total_breakup']['tax_breakup'];
			$b2c_price_details['Currency'] = $api_price_details1['api_currency'];
			$b2c_price_details['CurrencySymbol'] = $currency_symbol;
			$FareDetails['b2c_PriceDetails'] = $b2c_price_details; //B2C PRICE DETAILS
		} else if ($module == 'b2b') {
			//B2B
			//	debug($flight_details['price']);
			$admin_price_details = $this->update_search_markup_currency($flight_details['price'], $currency_obj, false, true, $search_id, $specific_markup_config);
			//debug($admin_price_details);
			//debug($specific_markup_config); die;
			$agent_price_details = $this->update_search_markup_currency($api_price_details1, $currency_obj, true, true, $search_id, $specific_markup_config);
			//	debug($agent_price_details); 
			$b2b_price_details = $this->b2b_price_details($api_price_details, $admin_price_details, $agent_price_details, $currency_obj);
			//	debug($b2b_price_details); exit;
			$b2b_price_details['Currency'] = $api_price_details['Currency'];
			$b2b_price_details['CurrencySymbol'] = $currency_symbol;
			// debug($b2b_price_details);
			// debug($api_price_details);exit;
			$b2b_price_details['_Markup'] = $b2b_price_details['_AgentMarkup'];
			$FareDetails['b2b_PriceDetails'] = $b2b_price_details; //B2B PRICE DETAILS
			//debug($b2b_price_details); exit;
		}
		$FareDetails['api_PriceDetails'] = $api_price_details; //API PRICE DETAILS
		$FareDetails['api_PriceDetails']['total_tax_breakup'] = $api_price_details1['total_breakup']['tax_breakup'];
		if ($_SERVER['REMOTE_ADDR'] == "14.97.94.42") {
			//debug($FareDetails); die;
		}
		// debug($FareDetails); exit;
		return $FareDetails;
	}
	public function get_airline_specific_markup_config(array $segment_details): array
	{
		$specific_markup_config = array();
		if (isset($segment_details[0][0]['OperatorCode'])) {
			$airline_code = $segment_details[0][0]['OperatorCode'];
		} else {
			$airline_code = $segment_details[0][0]['AirlineDetails']['AirlineCode'];
		}
		$category = 'airline_wise';
		$specific_markup_config[] = array('category' => $category, 'ref_id' => $airline_code);
		return $specific_markup_config;
	}
	function update_search_markup_currency(array &$price_summary,object &$currency_obj,bool $level_one_markup = false, bool $current_domain_markup = true, int $search_id = 0, array $specific_markup_config = []): array
	{
		// debug($price_summary); exit;
		if (intval($search_id) > 0) {
			$search_data = $this->search_data($search_id);
		}

		$total_pax = intval($this->master_search_data['adult_config'] + $this->master_search_data['child_config'] + $this->master_search_data['infant_config']);
		$trip_type = $this->master_search_data['trip_type'];

		$way_count = $this->way_multiplier($this->master_search_data['trip_type'], $this->master_search_data['is_domestic'], $search_id);
		// echo $total_pax; exit;
		// $multiplier = ($total_pax*$way_count);
		$multiplier = $total_pax;
		//debug($price_summary); exit;
		//  
		return $this->update_markup_currency($price_summary, $currency_obj, $level_one_markup, $current_domain_markup, $multiplier, $specific_markup_config);
		// debug($resul); exit;
	}
	private function way_multiplier(string $way_type, bool $domestic, int $search_id = 0): int
	{

		$way_count = 0;
		if ($way_type == 'multicity') {
			$search_data = $this->search_data($search_id);
			$way_count = intval(count($search_data['data']['from']));
		} else if ($way_type == 'oneway' || $domestic == true) {
			$way_count = 1;
		} else {
			$way_count = 2;
		}
		return $way_count;
	}
	function tax_service_sum(array $markup_price_summary, array $api_price_summary, bool $retain_commission = false): float
	{
		//AirlineTransFee - Not Available
		//sum of tax and service ;
		if ($retain_commission == true) {
			$commission = 0;
			$commission_tds = 0;
		} else {
			$commission = $markup_price_summary['AgentCommission'];
			$commission_tds = $markup_price_summary['AgentTdsOnCommision'];
		}
		$markup_price = 0;
		$markup_price = $markup_price_summary['OfferedFare'] - $api_price_summary['OfferedFare'];
		return ((floatval($markup_price + $markup_price_summary['AdditionalTxnFee']) + floatval($markup_price_summary['Tax']) + floatval($markup_price_summary['OtherCharges']) + floatval($markup_price_summary['ServiceTax'])) - $commission + $commission_tds);
	}
	public function extract_segment_details(array $segment_details): array
	{
		$segment_summary = array();
		$segment_full_details = array();
		$data = [];

		foreach ($segment_details as $seg_k => $seg_v) {
			$this->update_segment_details($seg_v);


			//Segment Summry

			$OriginDetails = $seg_v[0]['Origin'];
			$AirlineDetails = $seg_v[0]['AirlineDetails'];
			$OriginDetails['_DateTime'] = local_time($OriginDetails['DateTime']);
			$OriginDetails['GMT_dep_DateTime'] = local_time($seg_v[0]['origin']['GMT_dep_time']);


			$airline_image = $this->CI->custom_db->single_table_records('flight_crs_airline_list', 'airline_img', array('airline_code' => $seg_v[0]['operator_code']))['data'];

			$OriginDetails['_Date'] = local_date_new($OriginDetails['DateTime']);
			$last_segment_details = end($seg_v);
			$DestinationDetails = $last_segment_details['Destination'];
			$DestinationDetails['_DateTime'] = local_time($DestinationDetails['DateTime']);


			$DestinationDetails['GMT_arr_DateTime'] = local_time($seg_v[0]['destination']['GMT_arr_time']);
			$DestinationDetails['_Date'] = local_date_new($DestinationDetails['DateTime']);
			$total_stops = (count($seg_v) - 1);
			$total_duaration = $this->segment_total_duration($seg_v);
			$segment_summary[$seg_k]['AirlineDetails'] = $AirlineDetails;
			$segment_summary[$seg_k]['airline_image'] = $airline_image[0]['airline_img'];
			$segment_summary[$seg_k]['OriginDetails'] = $OriginDetails;
			$segment_summary[$seg_k]['DestinationDetails'] = $DestinationDetails;
			$segment_summary[$seg_k]['TotalStops'] = $total_stops;
			$segment_summary[$seg_k]['TotalDuaration'] = $total_duaration;
			$segment_summary[$seg_k]['Charter_d'] = $seg_v[0]['destination']['charter_time'];

			//Segment Details
			foreach ($seg_v as $seg_details_k => $seg_details_v) {
				//Origin Details
				$AirlineDetails = $seg_details_v['AirlineDetails'];
				$OriginDetails = $seg_details_v['Origin'];

				$OriginDetails['_DateTime'] = local_time($OriginDetails['DateTime']);
				$OriginDetails['_Date'] = local_date_new($OriginDetails['DateTime']);
				//Destination Details
				$DestinationDetails = $seg_details_v['Destination'];
				$DestinationDetails['_DateTime'] = local_time($DestinationDetails['DateTime']);
				$DestinationDetails['_Date'] = local_date_new($DestinationDetails['DateTime']);
				$SegmentDuration = get_time_duration_label($seg_details_v['SegmentDuration'] * 60); //Converting into seconds

				if (isset($seg_v[$seg_details_k + 1]) == true) {
					$next_seg_info = $seg_v[$seg_details_k + 1];
					$WaitingTime = (get_time_duration_label(calculate_duration($seg_details_v['Destination']['DateTime'], $next_seg_info['Origin']['DateTime'])));
				}
				$Baggage = '';
				$CabinBaggage = '';
				if (valid_array($seg_details_v['Attr']) == true) {
					
					$bagg = json_decode($seg_details_v['baggage_info'], 1);				
					$Baggage = $bagg['chechin_baggage'];				
					$CabinBaggage = $bagg['cabin_baggage'];
					if (isset($seg_details_v['Attr']['AvailableSeats'])) {
						$segment_full_details[$seg_k][$seg_details_k]['AvailableSeats'] = $seg_details_v['Attr']['AvailableSeats'];
					}
				}
				$segment_full_details[$seg_k][$seg_details_k]['Baggage'] = $Baggage;
				$segment_full_details[$seg_k][$seg_details_k]['CabinBaggage'] = $CabinBaggage;
				$segment_full_details[$seg_k][$seg_details_k]['AirlineDetails'] = $AirlineDetails;
				$segment_full_details[$seg_k][$seg_details_k]['OriginDetails'] = $OriginDetails;
				$segment_full_details[$seg_k][$seg_details_k]['DestinationDetails'] = $DestinationDetails;
				$segment_full_details[$seg_k][$seg_details_k]['SegmentDuration'] = $SegmentDuration;
				$segment_full_details[$seg_k][$seg_details_k]['WaitingTime'] = '';

				$baggage_rule = $GLOBALS['CI']->flight_model->get_baggage_rules($seg_details_v);
				//debug($baggage_rule);exit;
				$segment_full_details[$seg_k][$seg_details_k]['Baggage_rules'] = $baggage_rule['rule'];
				$segment_full_details[$seg_k][$seg_details_k]['Baggage_image'] = $baggage_rule['image'];


				//debug($segment_full_details);exit;

				if (isset($WaitingTime) == true) {
					$segment_full_details[$seg_k][$seg_details_k]['WaitingTime'] = $WaitingTime;
				}
			}
		}
		$data['segment_summary'] = $segment_summary;
		$data['segment_full_details'] = $segment_full_details;


		return $data;
	}
	private function update_segment_details(array &$segments): void
	{
		foreach ($segments as $k => &$v) {
			$v['SegmentDuration'] = $this->flight_segment_duration($v['Origin']['AirportCode'], $v['Destination']['AirportCode'], $v['Origin']['DateTime'], $v['Destination']['DateTime']);
			$AirlineDetails = array();
			$AirlineDetails['AirlineCode'] = $v['OperatorCode'];
			$AirlineDetails['AirlineName'] = $v['OperatorName'];
			$AirlineDetails['FlightNumber'] = $v['FlightNumber'];
			$AirlineDetails['FareClass'] = $v['CabinClass'];
			unset($v['OperatorCode'], $v['OperatorName'], $v['FlightNumber'], $v['CabinClass'], $v['DisplayOperatorCode']);
			$v['AirlineDetails'] = $AirlineDetails;
		}
	}
	private function flight_segment_duration(string $departure_airport_code, string $arrival_airport_code, string $departure_datetime, string $arrival_datetime): int
	{

		$departure_datetime = date('Y-m-d H:i:s', strtotime($departure_datetime));
		$arrival_datetime = date('Y-m-d H:i:s', strtotime($arrival_datetime));
		//debug($departure_datetime." ".$arrival_datetime); die;

		//Get TimeZone of Departure and Arrival Airport
		$departure_timezone_offset = $this->get_airport_timezone_offset($departure_airport_code, $departure_datetime);
		$arrival_timezone_offset = $this->get_airport_timezone_offset($arrival_airport_code, $arrival_datetime);


		//Converting TimeZone to Minutes
		$departure_timezone_offset = $this->convert_timezone_offset_to_minutes($departure_timezone_offset);
		$arrival_timezone_offset = $this->convert_timezone_offset_to_minutes($arrival_timezone_offset);


		//Getting Total time difference between 2 airports
		$timezone_offset = ($departure_timezone_offset - $arrival_timezone_offset);
		//debug($timezone_offset);die;
		//Calculating Total Duration Time
		//debug($departure_datetime." ".$arrival_datetime); die;
		$segment_duration = calculate_duration($departure_datetime, $arrival_datetime);
		//debug($segment_duration); die;
		//Converting into minutes
		$segment_duration = ($segment_duration) / 60; //Converting int minutes
		//debug($segment_duration); die;
		//Updating the total duration with time zone offset difference
		//debug($segment_duration." ".$timezone_offset); die;
		//$segment_duration = ($segment_duration+$timezone_offset);

		return $segment_duration;
	}
	private function get_airport_timezone_offset(string $airport_code, string $journey_date): int
	{
		//FIXME: cache the data
		$journey_month = date('m', strtotime($journey_date));
		$query = 'select FAL.airport_code,FAT.start_month,FAT.end_month,FAT.timezone_offset from flight_airport_list FAL
					join flight_airport_timezone_offset FAT on FAT.flight_airport_list_fk=FAL.id
					where airport_code = "' . $airport_code . '" and (start_month<=' . $journey_month . ' or end_month>=' . $journey_month . ')
					order by 
					CASE
					WHEN start_month	= ' . $journey_month . ' THEN 1
		            WHEN end_month	= ' . $journey_month . ' THEN 2
					ELSE 3 END';
		$timezone_offset = $this->CI->db->query($query)->result_array();
		//debug($timezone_offset); die;
		return $timezone_offset[0]['timezone_offset'];
	}
	private function convert_timezone_offset_to_minutes(string $timezone_offset): string
	{
		$add_mode_sign = $timezone_offset[0];
		$time_zone_details = explode(':', $timezone_offset);
		$hours = abs(intval($time_zone_details[0]));
		$minutes = abs(intval($time_zone_details[1]));
		$minutes = $hours * 60  + $minutes;
		$minutes = ($add_mode_sign . $minutes);
		return $minutes;
	}
	private function segment_total_duration(array $segments): string
	{
		$total_duration = 0;
		//debug($segments); die;
		foreach ($segments as $k => $v) {
			$total_duration += $v['SegmentDuration'];
			//adding waiting time
			if (isset($segments[$k + 1]['Origin']) == true) {
				$total_duration += $this->wating_segment_time(
					$v['Destination']['AirportCode'],
					$segments[$k + 1]['Origin']['AirportCode'],
					$v['Destination']['DateTime'],
					$segments[$k + 1]['Origin']['DateTime']
				);
			}
		}
		$total_duration = ($total_duration * 60); //Converting into seconds
		return get_time_duration_label($total_duration);
	}
	private function wating_segment_time(string $arrival_airport_city, string $departure_airport_city, string $arrival_datetime, string $departure_datetime): float
	{
		$departure_datetime = date('Y-m-d H:i:s', strtotime($departure_datetime));
		$arrival_datetime = date('Y-m-d H:i:s', strtotime($arrival_datetime));
		//Get TimeZone of Departure and Arrival Airport
		$departure_timezone_offset = $GLOBALS['CI']->flight_model->get_airport_timezone_offset($departure_airport_city, $departure_datetime);
		$arrival_timezone_offset = $GLOBALS['CI']->flight_model->get_airport_timezone_offset($arrival_airport_city, $arrival_datetime);
		//Converting TimeZone to Minutes
		$departure_timezone_offset = $this->convert_timezone_offset_to_minutes($departure_timezone_offset);
		$arrival_timezone_offset = $this->convert_timezone_offset_to_minutes($arrival_timezone_offset);
		//Getting Total time difference between 2 airports
		$timezone_offset = ($arrival_timezone_offset - $departure_timezone_offset);
		//Calculating the Waiting time between 2 segments
		$current_segment_arr = strtotime($arrival_datetime);
		$next_segment_dep = strtotime($departure_datetime);
		$segment_waiting_time = ($next_segment_dep - $current_segment_arr);

		//Converting into minutes
		$segment_waiting_time = ($segment_waiting_time) / 60; //Converting into minutes
		//Updating the total duration with time zone offset difference
		$segment_waiting_time = ($segment_waiting_time + $timezone_offset);
		return $segment_waiting_time;
	}

	private function save_token(array $token): void
	{
		$file = DOMAIN_TMP_UPLOAD_DIR . $this->ins_token_file . '.json';
		file_put_contents($file, json_encode($token));
	}

	function set_flight_search_session_expiry(bool $from_cache = true, string $search_hash = ''): array
	{
		$response = array();
		if ($from_cache == false) {
			$GLOBALS['CI']->session->set_userdata(array($search_hash => date("Y-m-d H:i:s")));
			$response['session_start_time'] = $GLOBALS['CI']->config->item('flight_search_session_expiry_period');
		} else {
			$start_time = $GLOBALS['CI']->session->userdata($search_hash);
			$current_time = date("Y-m-d H:i:s");
			$diff = strtotime($current_time) - strtotime($start_time);
			$response['session_start_time'] = $GLOBALS['CI']->config->item('flight_search_session_expiry_period') - $diff;
		}
		$response['search_hash'] = $search_hash;
		return $response;
	}

	function get_commission(array &$__trip_flight, Currency &$currency_obj): void
	{
		//$res = $currency_obj->get_commission();
		// debug($__trip_flight); exit;
		$this->commission = $currency_obj->get_commission();
		if (valid_array($this->commission) == true && intval($this->commission['admin_commission_list']['value']) > 0) {
			//update commission
			//$bus_row = array(); Preserving Row data before calculation
			// $core_agent_commision = ($__trip_flight['FareDetails']['PublishedFare']-$__trip_flight['FareDetails']['OfferedFare']);
			$core_agent_commision = ($__trip_flight['price']['api_total_display_fare'] - $__trip_flight['price']['api_total_display_fare']);
			// debug($core_agent_commision); exit;
			$com = $this->calculate_commission($core_agent_commision);
			// debug($__trip_flight['FareDetails']); exit;
			$this->set_b2b_comm_tag($__trip_flight['FareDetails'], $com, $currency_obj);
		} else {
			//update commission
			$this->set_b2b_comm_tag($__trip_flight['FareDetails'], 0, $currency_obj);
		}
	}

	private function calculate_commission(float $agent_com): string
	{
		$agent_com_row = $this->commission['admin_commission_list'];
		// debug($agent_com_row); exit();
		$b2b_comm = 0;
		if ($agent_com_row['value_type'] == 'percentage') {
			//%
			$b2b_comm = ($agent_com / 100) * $agent_com_row['value'];
		} else {
			//plus
			$b2b_comm = ($agent_com - $agent_com_row['value']);
		}
		return number_format($b2b_comm, 2, '.', '');
	}

	function set_b2b_comm_tag(array &$v, float $b2b_com = 0, object $currency_obj)
	{
		$v['ORG_AgentCommission'] = $v['AgentCommission'];
		$v['ORG_TdsOnCommission'] = $v['AgentTdsOnCommision'];
		$v['ORG_OfferedFare'] = $v['OfferedFare'];

		//$admin_com = $v['AgentCommission'] - $b2b_com;
		$core_agent_commision = ($v['PublishedFare'] - $v['OfferedFare']);
		$admin_com = $core_agent_commision - $b2b_com;

		$v['OfferedFare'] = $v['OfferedFare'] + $admin_com;
		$v['AgentCommission'] = $b2b_com;
		$v['TdsOnCommission'] = $currency_obj->calculate_tds($core_agent_commision);
	}

	function b2b_price_details(array $api_price_details, array $admin_price_details, array $agent_price_details, object $currency_obj): array
	{
		$total_price = [];
		$total_price['BaseFare']	= $api_price_details['BaseFare'];
		$total_price['_CustomerBuying']	= round($agent_price_details['PublishedFare'], 1);;
	
		$total_price['_AdminBuying']	= $api_price_details['BaseFare'];

		$total_price['_AdminMarkup']	=  $agent_price_details['api_total_display_fare'] - $admin_price_details['api_total_display_fare'];

		$total_price['_AgentMarkup']	=  $agent_price_details['_Markup'] - $total_price['_AdminMarkup'];

		$total_price['_AgentBuying']	= $agent_price_details['api_total_display_fare'] - $total_price['_AgentMarkup'];
	
		$total_price['_Commission']		= 0;
		$total_price['_tdsCommission']	= $currency_obj->calculate_tds($total_price['_Commission']); //Includes TDS ON PLB AND COMMISSION COMM
		$total_price['_tdsCommission']	= 0;
		$total_price['_AgentEarning']	= 0;
		
		$total_price['_TaxSum']			= $api_price_details['Tax'] + $agent_price_details['_Markup']; //rik
	
		$total_price['_BaseFare']		= $api_price_details['BaseFare'];
		
		$total_price['_TotalPayable']	= round($agent_price_details['api_total_display_fare'] - $total_price['_AgentMarkup'], 1);
		
		
		return $total_price;
	}

	function booking_form(bool $isDomestic, string $token = '', string $token_key = '', string $search_access_key = '', string $promotional_plan_type = '', string $booking_source = PROVAB_FLIGHT_CRS_BOOKING_SOURCE): string
	{
		$booking_form = '';

		$booking_form .= '<input type="hidden" name="is_domestic" class="" value="' . $isDomestic . '">';
		$booking_form .= '<input type="hidden" name="token[]" class="token data-access-key" value="' . $token . '">';
		$booking_form .= '<input type="hidden" name="token_key[]" class="token_key" value="' . $token_key . '">';
		//$booking_form .= '<input type="hidden" name="search_access_key[]" class="search-access-key" value="'.$search_access_key.'">';
		$booking_form .= '<input type="hidden" name="promotional_plan_type[]" class="promotional-plan-type" value="' . $promotional_plan_type . '">';

		if (empty($booking_source) == false) {
			$booking_form .= '<input type="hidden" name="booking_source" class="booking-source" value="' . $booking_source . '">';
		}
		//debug($booking_form);exit;
		return $booking_form;
	}

	public function unserialized_token(array $token, array $token_key): array
	{
		$response = [];
		$response['data'] = array();
		$response['status'] = true;
		foreach ($token as $___k => $___v) {
			$tmp_tkn = $this->read_token($___v);
			if ($tmp_tkn != false) {
				$response['data']['token'][$___k] = $tmp_tkn;
				$response['data']['token_key'] = $token_key[$___k];
			} else {
				$response['data']['token'][$___k] = false;
			}

			if ($response['status'] == true) {
				if ($response['data']['token'][$___k] == false) {
					$response['status'] = false;
				}
			}
		}

		return $response;
	}

	public function read_token(string $token_key): mixed
	{
		$token_key = explode(DB_SAFE_SEPARATOR, unserialized_data($token_key));
		if (valid_array($token_key) == true) {
			$file = DOMAIN_TMP_UPLOAD_DIR . $token_key[0] . '.json'; //File name
			$index = $token_key[1]; // access key

			if (file_exists($file) == true) {
				$token_content = file_get_contents($file);
				if (empty($token_content) == false) {
					$token = json_decode($token_content, true);
					if (valid_array($token) == true && isset($token[$index]) == true) {


						return $token[$index];
					} else {
						return false;
						echo 'Token data not found';
						exit;
					}
				} else {
					return false;
					echo 'Invalid File access';
					exit;
				}
			} else {
				return false;
				echo 'Invalid Token access';
				exit;
			}
		} else {
			return false;
			echo 'Invalid Token passed';
			exit;
		}
	}
	function checkAvailability(array $data): array{
		$avail = [];
		$response = [];
		$avail['key'] = $data['fkey'];
		$avail['date'] = $data['date'];
		$avail['paxCount'] = $data['adult']+$data['child']+$data['infant'];
		$base_url = strstr(base_url(),'supplier',true);

		$url = $base_url . 'webconnect/index.php/flightAvailability';

		$request_data = json_encode($avail);

		$api_response = self::run_curl($url, $request_data);
		$response['status'] = false;

		if($api_response['data']['is_seat_available'] == true){
			$response['status'] = true;
			$response['flight_available'] = 'yes';
			$response['avail_seat'] = $api_response['data']['avail_seat'];
			$response['airline_name'] = $api_response['data']['airline_name'];

		}else{
			$response['flight_available'] = 'no';
			$response['avail_seat'] = 0;
			$response['airline_name'] = '';

		}
		
		return $response;

	}
	function sendEnquiry(array $data): array{
		$avail = [];
		$response = [];
		$avail['type'] = 'quote';
		$avail['enqNo'] = $data['enqNo'];
		$avail['quote'] = $data['price'];
		$base_url = strstr(base_url(),'supplier',true);

		$url = $base_url . 'webconnect/index.php/sendEmail';

		$request_data = json_encode($avail);

		$api_response = self::run_curl($url, $request_data);
		
			$response['status'] = $api_response['success'] ;
			$response['msg'] = $api_response['message'] ;
			
		
		return $response;

	}
	function fare_quote_details($resultToken)
	{
		$request = [];
		$_response = [];
		$request['Currency'] = get_application_currency_preference();
		$request['ResultToken'][] = $resultToken;

		$url = $this->service_url . 'UpdateFare';
		$request_data = json_encode($request);

		$api_response = self::run_curl($url, $request_data);
		

		$api_response['status'] = false;

		if ($api_response['success'] == 1) {
			$_response['status'] = true;
			$_response['data'] = $api_response['data'];
		}
		return $_response;
	}

	public function farequote_data_in_preferred_currency($fare_quote_details, $currency_obj)
	{
		$flight_quote = $fare_quote_details['data']['token'];
		$flight_quote_data = array();
		foreach ($flight_quote as $fk => $fv) {
			$flight_quote_data[$fk] = $fv;
			$flight_quote_data[$fk]['FareDetails'] = $this->preferred_currency_fare_object($fv['Price'], $currency_obj);
			$flight_quote_data[$fk]['PassengerFareBreakdown'] = $this->preferred_currency_paxwise_breakup_object($fv['Price']['PassengerBreakup'], $currency_obj);
			unset($flight_quote_data[$fk]['Price']);
		}
		$fare_quote_details['data']['token'] = $flight_quote_data;
		return $fare_quote_details;
	}

	public function merge_flight_segment_fare_details($flight_details)
	{
		$flight_pre_booking_summery = array();
		$PassengerFareBreakdown = array();
		$SegmentDetails = array();
		$SegmentSummary = array();
		$FareDetails = $this->merge_fare_details($flight_details);
		$PassengerFareBreakdown = $this->merge_passenger_fare_break_down($flight_details);
		$SegmentDetails = $this->merge_segment_details($flight_details);
		$SegmentSummary = $this->merge_segment_summary($flight_details);

		$flight_pre_booking_summery['FareDetails'] = $FareDetails;
		$flight_pre_booking_summery['PassengerFareBreakdown'] = $PassengerFareBreakdown;
		$flight_pre_booking_summery['SegmentDetails'] = $SegmentDetails;
		$flight_pre_booking_summery['SegmentSummary'] = $SegmentSummary;
		$flight_pre_booking_summery['HoldTicket'] = $flight_details[0]['HoldTicket'];
		// debug($flight_pre_booking_summery);exit;
		return $flight_pre_booking_summery;
	}


	public function merge_fare_details($flight_details)
	{
		$FareDetails = array();

		$temp_fare_details = group_array_column($flight_details, 'FareDetails');

		$APIPriceDetails = array_merge_numeric_values(group_array_column($temp_fare_details, 'api_PriceDetails'));
		if (isset($temp_fare_details[0]['b2c_PriceDetails']) == true) { //B2C
			$B2CPriceDetails = array_merge_numeric_values(group_array_column($temp_fare_details, 'b2c_PriceDetails'));
			$FareDetails['b2c_PriceDetails'] = $B2CPriceDetails;
		} elseif (isset($temp_fare_details[0]['b2b_PriceDetails']) == true) { //B2B
			$B2BPriceDetails = array_merge_numeric_values(group_array_column($temp_fare_details, 'b2b_PriceDetails'));
			// debug($B2BPriceDetails);exit;
			$FareDetails['b2b_PriceDetails'] = $B2BPriceDetails;
		}
		$FareDetails['api_PriceDetails'] = $APIPriceDetails;

		return $FareDetails;
	}

	public function merge_passenger_fare_break_down($flight_details)
	{
		$PassengerFareBreakdown = array();

		$tmp_fare_breakdown = group_array_column($flight_details, 'PassengerFareBreakdown');
		// debug($tmp_fare_breakdown);exit;
		foreach ($tmp_fare_breakdown as $k => $v) {
			foreach ($v as $pax_k => $pax_v) {
				$pax_type = $pax_k;
				if (isset($PassengerFareBreakdown[$pax_type]) == false) {
					$PassengerFareBreakdown[$pax_type]['PassengerType'] = $pax_type;
					$PassengerFareBreakdown[$pax_type]['Count'] = $pax_v['PassengerCount'];
					$PassengerFareBreakdown[$pax_type]['BaseFare'] = $pax_v['BaseFare'];
				} else {
					$PassengerFareBreakdown[$pax_type]['BaseFare'] += $pax_v['BaseFare'];
				}
			}
		}
		return $PassengerFareBreakdown;
	}
	/**
	 * Merges Flight Segment Details
	 * @param unknown_type $flight_details
	 */
	public function merge_segment_details($flight_details)
	{
		$SegmentDetails = array();
		foreach ($flight_details as $k => $v) {
			$SegmentDetails = array_merge($SegmentDetails, $v['SegmentDetails']);
		}
		return $SegmentDetails;
	}
	/**
	 * Merges Flight Segment Summery
	 * @param unknown_type $flight_details
	 */
	public function merge_segment_summary($flight_details)
	{
		$SegmentSummary = array();
		foreach ($flight_details as $k => $v) {
			$SegmentSummary = array_merge($SegmentSummary, $v['SegmentSummary']);
		}
		return $SegmentSummary;
	}

	public function convert_token_to_application_currency($token, $currency_obj, $module)
	{
		$token_details = $token;
		$token = array();
		$application_default_currency = admin_base_currency();
		foreach ($token_details as $tk => $tv) {
			$token[$tk] = $tv;
			$temp_fare_details = $tv['FareDetails'];
			//debug($temp_fare_details);exit;
			//Fare Details
			$FareDetails = array();
			if ($module == 'b2c') {
				$PriceDetails = $temp_fare_details[$module . '_PriceDetails'];

				$FareDetails['b2c_PriceDetails']['BaseFare'] = 			get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['BaseFare']));
				$FareDetails['b2c_PriceDetails']['TotalTax'] = 			get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['TotalTax']));
				$FareDetails['b2c_PriceDetails']['TotalFare'] = 		get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['TotalFare']));
				$FareDetails['b2c_PriceDetails']['Currency'] = 			$application_default_currency;
				$FareDetails['b2c_PriceDetails']['CurrencySymbol'] =	$currency_obj->get_currency_symbol($currency_obj->to_currency);
				$FareDetails['b2c_PriceDetails']['TotalTaxBreakup'] =   $PriceDetails['total_tax_breakup'];
			} else if ($module == 'b2b') {
				$PriceDetails = $temp_fare_details[$module . '_PriceDetails'];

				$FareDetails['b2b_PriceDetails']['BaseFare'] = 			get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['BaseFare']));
				$FareDetails['b2b_PriceDetails']['_CustomerBuying'] =	get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['_CustomerBuying']));
				$FareDetails['b2b_PriceDetails']['_AgentBuying'] = 		get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['_AgentBuying']));
				$FareDetails['b2b_PriceDetails']['_AdminBuying'] = 		get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['_AdminBuying']));
				$FareDetails['b2b_PriceDetails']['_Markup'] = 			get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['_Markup']));
				$FareDetails['b2b_PriceDetails']['_AgentMarkup'] = 		get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['_AgentMarkup']));
				$FareDetails['b2b_PriceDetails']['_AdminMarkup'] = 		get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['_AdminMarkup']));
				$FareDetails['b2b_PriceDetails']['_Commission'] = 		get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['_Commission']));
				$FareDetails['b2b_PriceDetails']['_tdsCommission'] = 	get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['_tdsCommission']));
				$FareDetails['b2b_PriceDetails']['_AgentEarning'] = 	get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['_AgentEarning']));
				$FareDetails['b2b_PriceDetails']['_TaxSum'] = 			get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['_TaxSum']));
				$FareDetails['b2b_PriceDetails']['_BaseFare'] = 		get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['_BaseFare']));
				$FareDetails['b2b_PriceDetails']['_TotalPayable'] = 	get_converted_currency_value($currency_obj->force_currency_conversion($PriceDetails['_TotalPayable']));
				$FareDetails['b2b_PriceDetails']['Currency'] = 			$application_default_currency;
				$FareDetails['b2b_PriceDetails']['CurrencySymbol'] = 	$currency_obj->get_currency_symbol($currency_obj->to_currency);
			}

			$FareDetails['api_PriceDetails'] = $this->preferred_currency_fare_object($temp_fare_details['api_PriceDetails'], $currency_obj, $application_default_currency);
			$FareDetails['api_PriceDetails']['TotalTaxBreakup'] =   $temp_fare_details['api_PriceDetails']['total_tax_breakup'];
			$token[$tk]['FareDetails'] = $FareDetails;
			//Passenger Breakdown
			$token[$tk]['PassengerFareBreakdown'] = $this->preferred_currency_paxwise_breakup_object($tv['PassengerFareBreakdown'], $currency_obj);
		}
		return $token;
	}

	public function reindex_passport_expiry_month($passenger_passport_expiry_month, $search_id)
	{
		$safe_search_data = $this->search_data($search_id);
		$is_domestic = $safe_search_data['data']['is_domestic'];
		if ($is_domestic == false) {
			foreach ($passenger_passport_expiry_month as $k => $v) {
				$passenger_passport_expiry_month[$k] = ($v + 1);
			}
		}
		return $passenger_passport_expiry_month;
	}

	function save_booking($app_booking_id, $book_params, $currency_obj, $module = 'b2c')
	{
		$response = [];
		//Need to return following data as this is needed to save the booking fare in the transaction details
		$response['fare'] = $response['domain_markup'] = $response['level_one_markup'] = 0;
		$book_total_fare = array();
		$book_domain_markup = array();
		$book_level_one_markup = array();
		$master_transaction_status = 'BOOKING_HOLD';
		$master_search_id = $book_params['search_id'];

		$domain_origin = get_domain_auth_id();
		$app_reference = $app_booking_id;
		$booking_source = $book_params['token']['booking_source'];

		//PASSENGER DATA UPDATE
		$total_pax_count = count($book_params['passenger_type']);
		$pax_count = $total_pax_count;
		// debug("1711"); exit;
		//Extract ExtraService Details
		// $extra_service_details = $this->extract_extra_service_details($book_params);
		$extra_service_details = $book_params['token']['extra_services'];

		// debug($extra_service_details); exit();
		//PREFERRED TRANSACTION CURRENCY AND CURRENCY CONVERSION RATE 
		$transaction_currency = get_application_currency_preference();
		$application_currency = admin_base_currency();
		$currency_conversion_rate = $currency_obj->transaction_currency_conversion_rate();

		//********************** only for calculation
		$safe_search_data = $this->search_data($master_search_id);
		$safe_search_data = $safe_search_data['data'];
		// debug($safe_search_data);exit;
		//Extra services calculation

		if (valid_array($extra_service_details)) {

			if ($safe_search_data['trip_type'] == 'circle') {
				$trip = 2;
			} else {
				$trip = 1;
			}

			$baggage_pax_count = $safe_search_data['total_passenger'] - $safe_search_data['infant'];
			//debug($book_params);exit;
			if (isset($extra_service_details['baggage']) && valid_array($extra_service_details['baggage'])) {


				$extra_services_fare = array();
				for ($m = 0; $m < $trip; $m++) {
					// debug($book_params['chck_bag_pax_'.$m.($i+1)]);
					// debug($extra_service_details);exit;
					for ($i = 0; $i < $baggage_pax_count; $i++) {

						if (isset($book_params['chck_bag_pax_' . $m . ($i + 1)]) && $book_params['chck_bag_pax_' . $m . ($i + 1)] != '0') {
							$extra_services_fare[$m][$i]['baggage_fare']['checkin_baggage_price'] = $extra_service_details['baggage'][$m]['checkin_baggage_price'];
						}

						if (isset($book_params['extra_bag_pax_' . $m . ($i + 1)]) && ($book_params['extra_bag_pax_' . $m . ($i + 1)] <= $extra_service_details['baggage'][$m]['extra_baggage_limit'])) {

							$extra_services_fare[$m][$i]['baggage_fare']['extra_baggage_price'] = $extra_service_details['baggage'][$m]['extra_baggage_price'] * $book_params['extra_bag_pax_' . $m . ($i + 1)];
							$extra_services_fare[$m][$i]['baggage_fare']['extra_baggage_weight'] = $book_params['extra_bag_pax_' . $m . ($i + 1)];
						}
					}
				}
			}

			for ($m = 0; $m < $trip; $m++) {
				if (isset($extra_service_details['meals'][$m]) && valid_array($extra_service_details['meals'][$m])) {


					$re_index_meals = array();
					foreach ($extra_service_details['meals'][$m] as $m_key => $m_value) {
						$re_index_meals[$m_value['origin']] = $m_value;
					}

					// debug($re_index_meals);exit;
					for ($j = 0; $j < $baggage_pax_count; $j++) {
						// debug($book_params['meal_pref'.$j]);exit;
						if (valid_array($book_params['meal_pref' . $j])) {
							foreach ($book_params['meal_pref' . $j] as $route_meal_key => $route_meal_value) {
								// code...

								$extra_services_fare[$route_meal_key][$j]['meal_fare']['extra_meal_price'] = $re_index_meals[$route_meal_value]['price'];
								$extra_services_fare[$route_meal_key][$j]['meal_fare']['extra_meal_origin'] = $re_index_meals[$route_meal_value]['origin'];
								$extra_services_fare[$route_meal_key][$j]['meal_fare']['extra_meal_name'] = $re_index_meals[$route_meal_value]['meal_name'];
							}
						}
					}
				}
			}
		}
		//debug($book_params);exit;
		$pax_seat_data = array();
		if (isset($book_params['pax_seat_number']) && valid_array($book_params['pax_seat_number'])) {

			foreach ($book_params['pax_seat_number'] as $pax_seat_key => $pax_seat_value) {
				if (isset($book_params['seat_lopa'][$pax_seat_value])) {
					$pax_seat_data[0][$pax_seat_key] = $book_params['seat_lopa'][$pax_seat_value];
					$extra_services_fare[0][$pax_seat_key]['seat']['seat_number'] = $pax_seat_value;
					$extra_services_fare[0][$pax_seat_key]['seat']['seat_price'] = $book_params['seat_lopa'][$pax_seat_value]['SeatPrice'];
				}
			}
		}

		if (isset($book_params['pax_seat_number_return']) && valid_array($book_params['pax_seat_number_return'])) {

			foreach ($book_params['pax_seat_number_return'] as $pax_seat_key => $pax_seat_value) {
				if (isset($book_params['seat_lopa_return'][$pax_seat_value])) {
					$pax_seat_data[1][$pax_seat_key] = $book_params['seat_lopa_return'][$pax_seat_value];
					$extra_services_fare[1][$pax_seat_key]['seat']['seat_number'] = $pax_seat_value;
					$extra_services_fare[1][$pax_seat_key]['seat']['seat_price'] = $book_params['seat_lopa_return'][$pax_seat_value]['SeatPrice'];
				}
			}
		}

		//debug($extra_services_fare);exit;
		$safe_search_data['is_domestic_one_way_flight'] = false;
		$from_to_trip_type = $safe_search_data['trip_type'];
		if (strtolower($from_to_trip_type) == 'multicity') {
			$from_loc = $safe_search_data['from'][0];
			$to_loc = end($safe_search_data['to']);
			$journey_from = $safe_search_data['from_city'][0];
			$journey_to = end($safe_search_data['to_city']);
		} else {
			$from_loc = $safe_search_data['from'];
			$to_loc = $safe_search_data['to'];
			$journey_from = $safe_search_data['from_city'];
			$journey_to = $safe_search_data['to_city'];
		}
		$safe_search_data['is_domestic_one_way_flight'] = $GLOBALS['CI']->flight_model->is_domestic_flight($from_loc, $to_loc);
		if ($safe_search_data['is_domestic_one_way_flight'] == false && strtolower($from_to_trip_type) == 'circle') {
			$multiplier = $pax_count * 2; //Multiply with 2 for international round way
		} else if (strtolower($from_to_trip_type) == 'multicity') {
			$multiplier = $pax_count * count($safe_search_data['from']);
		} else {
			//$multiplier = 1;
			$multiplier = $pax_count;
		}
		$token = $book_params['token']['token'];
		//debug($token);exit;
		//********************* only for calculation
		$master_booking_source = array();
		$currency = $currency_obj->to_currency;
		$deduction_cur_obj	= clone $currency_obj;
		//Storing Flight Details - Every Segment can repeate also
		// debug($book_params); exit;
		// debug($token); exit;
		$segment_summary = array();
		// debug($token);exit;
		foreach ($token as $token_index => $token_value) {


			$segment_details = $token_value['SegmentDetails'];
			$segment_summary[$token_index] = $token_value['SegmentSummary'];
			$Fare = $token_value['FareDetails']['api_PriceDetails'];
			$FareRule = $token_value['FareRule'];
			$tmp_domain_markup = 0;
			$tmp_level_one_markup = 0;
			$itinerary_price	= $Fare['BaseFare'];

			if (valid_array($extra_services_fare)) {

				$Fare['extra_services'][0] = $extra_services_fare[$token_index];
			}
			//debug($Fare);exit;

			//Calculation is different for b2b and b2c
			//Specific Markup Config
			$specific_markup_config = array();
			// debug("1762"); exit;
			$specific_markup_config = $this->get_airline_specific_markup_config($segment_details);
			// debug($specific_markup_config); exit;
			//Get the Airline code for setting airline-wise markup
			// debug($Fare); exit;
			$final_booking_price_details = $this->get_final_booking_price_details($Fare, $multiplier, $specific_markup_config, $currency_obj, $deduction_cur_obj, $module);
			// debug($final_booking_price_details); exit;
			//$commissionable_fare = $final_booking_price_details['commissionable_fare'];
			$commissionable_fare = $Fare['BaseFare'];
			$trans_total_fare = $final_booking_price_details['trans_total_fare'];
			$admin_markup = $final_booking_price_details['admin_markup'];
			$tax = $Fare['Tax'];

			$agent_markup = $final_booking_price_details['agent_markup'];
			$admin_commission = $final_booking_price_details['admin_commission'];
			$agent_commission = $final_booking_price_details['agent_commission'];
			$admin_tds = $final_booking_price_details['admin_tds'];
			$agent_tds = $final_booking_price_details['agent_tds'];


			$extra_services_price = $final_booking_price_details['extra_services_fare'];

			//**************Ticketing For Each Token START
			//Following Variables are used to save Transaction and Pax Ticket Details

			$book_id = '';
			$source = '';
			$ref_id = '';
			$transaction_status = 0;
			$GetBookingResult = array();
			$transaction_description = '';
			$getbooking_StatusCode = '';
			$getbooking_Description = '';
			$getbooking_Category = '';
			$WSTicket = array();
			$WSFareRule = array();
			$ticket_trans_status_group = [];
			//Saving Flight Transaction Details
			$tranaction_attributes = array();
			$pnr = '';
			$book_id = '';
			//$source = $this->get_tbo_source_name($token_value['Source']);
			$source = '';
			$ref_id = '';
			$transaction_status = $master_transaction_status;
			//debug($transaction_status); exit;
			$transaction_description = '';
			//Get Booking Details
			$getbooking_status_details = '';
			$getbooking_StatusCode = '';
			$getbooking_Description = '';
			$getbooking_Category = '';
			$tranaction_attributes['Fare'] = $Fare;
			$sequence_number = $token_index;
			//Transaction Log Details
			$ticket_trans_status_group[] = $transaction_status;
			$book_total_fare[]	= $trans_total_fare;
			$book_domain_markup[]	= $admin_markup;
			$book_level_one_markup[] = $agent_markup;
			//Need individual transaction price details
			//SAVE Transaction Details
			// exit;
			// debug(${"pnr".$x});
			// $x = 1;
			// debug($x);
			// debug($pnr1);${"pnr".$x}
			// debug($pnr2);
			//debug($tranaction_attributes);exit;
			$transaction_insert_id = $GLOBALS['CI']->flight_model->save_flight_booking_transaction_details(
				$app_reference,
				$transaction_status,
				$transaction_description,
				$pnr,
				$book_id,
				$source,
				$ref_id,
				json_encode($tranaction_attributes),
				$sequence_number,
				$currency,
				$commissionable_fare,
				$admin_markup,
				$agent_markup,
				$admin_commission,
				$agent_commission,
				$getbooking_StatusCode,
				$getbooking_Description,
				$getbooking_Category,
				$admin_tds,
				$agent_tds,
				$tax,
				$extra_services_price
			);

			// $x = $x + 1;
			// debug($x); exit;
			//	debug($transaction_insert_id); 
			$transaction_insert_id = $transaction_insert_id['insert_id'];

			// $PassengerFareBreakdown=$book_params['token']['token'][0]['PassengerFareBreakdown'];
			$PassengerFareBreakdown = $book_params['token']['token'][$token_index]['PassengerFareBreakdown'];

			//debug($PassengerFareBreakdown);exit;
			//Saving Passenger Details
			//debug($book_params);die;
			$i = 0;
			$extra_services_attr = array();
			$x = 1;
			for ($i = 0; $i < $total_pax_count; $i++) {

				$passenger_type = $book_params['passenger_type'][$i];



				$is_lead = $book_params['lead_passenger'][$i];

				$title = get_enum_list('title', $book_params['name_title'][$i]);
				// if($passenger_type=='Infant'){
				//	debug($title);exit;
				// }
				$first_name = $book_params['first_name'][$i];
				$middle_name = $book_params['middle_name'][$i];
				$last_name = $book_params['last_name'][$i];
				$date_of_birth = $book_params['date_of_birth'][$i];
				$scp_status = 0;
				//debug($book_params);
				if (isset($book_params['scp_select']) && in_array($x, $book_params['scp_select'])) {
					$scp_status =  1;
				}
				//debug($scp_status);exit;
				$remarks_text = $book_params['remarks_text'];
				$gender = get_enum_list('gender', $book_params['gender'][$i]);
				$x++;
				//	debug($book_params);
				$passenger_nationality_id = intval($book_params['passenger_nationality'][$i]);
				$passport_issuing_country_id = intval($book_params['passenger_passport_issuing_country'][$i]);
				$passenger_nationality = $GLOBALS['CI']->db_cache_api->get_country_list(array('k' => 'origin', 'v' => 'name'), array('origin' => $passenger_nationality_id));
				$passport_issuing_country = $GLOBALS['CI']->db_cache_api->get_country_list(array('k' => 'origin', 'v' => 'name'), array('origin' => $passport_issuing_country_id));

				if ($safe_search_data['is_domestic_one_way_flight'] == false) {
					$passenger_nationality = isset($passenger_nationality[$passenger_nationality_id]) ? $passenger_nationality[$passenger_nationality_id] : '';
					$passport_issuing_country = isset($passport_issuing_country[$passport_issuing_country_id]) ? $passport_issuing_country[$passport_issuing_country_id] : '';


					$passport_number = $book_params['passenger_passport_number'][$i];
					$passport_expiry_date = $book_params['passenger_passport_expiry_year'][$i] . '-' . $book_params['passenger_passport_expiry_month'][$i] . '-' . $book_params['passenger_passport_expiry_day'][$i];
				} else {
					$passenger_nationality =  '';
					$passport_issuing_country = '';


					$passport_number = '';
					$passport_expiry_date = '';
				}
				//$status = 'BOOKING_CONFIRMED';//Check it
				$status = $master_transaction_status;
				$passenger_attributes = array();


				$flight_booking_transaction_details_fk = $transaction_insert_id; //Adding Transaction Details Origin
				if (isset($book_params['transit_passenger_' . ($i + 1)]) && $book_params['transit_passenger_' . ($i + 1)] == 'on') {
					$transit_passenger = 1;
				} else {
					$transit_passenger = 0;
				}

				$pax_breakup = array();

				// debug($extra_services_fare);exit;
				//Pax wise Price Storing
				$paxType = strtoupper($passenger_type);
				$pax_breakup['base_fare'] = $PassengerFareBreakdown[$paxType]['BaseFare'];
				$pax_breakup['tax'] = $PassengerFareBreakdown[$paxType]['Tax'];
				$pax_breakup['total_price'] = $PassengerFareBreakdown[$paxType]['TotalPrice'];

				$passenger_attributes = $book_params['token']['token'][$token_index]['PassengerFareBreakdown'][$paxType];

				// if(isset($book_params['token']['token'][1]['PassengerFareBreakdown'])){
				// 	$passenger_attributes[1]=$book_params['token']['token'][1]['PassengerFareBreakdown'][$paxType];
				// }

				$extra_services_attr = array();
				// foreach ($extra_services_fare as $route_key => $route_extra_services) {
				foreach ($Fare['extra_services'] as $route_key => $route_extra_services) {
					// foreach ($route_extra_services as $pax_key => $pax_extra_services) {
					// debug($route_extra_services[$i]);
					if (isset($route_extra_services[$i])) {
						//debug($route_extra_services[$i]);
						// if(!isset($pax_extra_services_fare)){
						$pax_extra_services_fare = 0;
						// }
						if (isset($route_extra_services[$i]['baggage_fare']['checkin_baggage_price'])) {
							$pax_extra_services_fare += $route_extra_services[$i]['baggage_fare']['checkin_baggage_price'];
						}

						if (isset($route_extra_services[$i]['baggage_fare']['extra_baggage_price'])) {
							$pax_extra_services_fare += $route_extra_services[$i]['baggage_fare']['extra_baggage_price'];
						}

						if (isset($route_extra_services[$i]['seat']['seat_price'])) {
							$pax_extra_services_fare += $route_extra_services[$i]['seat']['seat_price'];
							$seat_price = $route_extra_services[$i]['seat']['seat_price'];
							$seat_number = $route_extra_services[$i]['seat']['seat_number'];
						} else {
							$seat_price = 0;
							$seat_number = '';
						}

						if (isset($route_extra_services[$i]['meal_fare']['extra_meal_price'])) {
							$pax_extra_services_fare += $route_extra_services[$i]['meal_fare']['extra_meal_price'];
							$meal_price = $route_extra_services[$i]['meal_fare']['extra_meal_price'];
							$meal_number = $route_extra_services[$i]['meal_fare']['meal_number'];
						} else {
							$meal_price = 0;
							$meal_number = '';
						}

						$pax_breakup['extra_services_fare'] = $pax_extra_services_fare;
						$extra_services_attr[$route_key] = $route_extra_services[$i];
					}
					// }
				}

				$pax_breakup['extra_services_attr'] = json_encode($extra_services_attr);

				//debug($transit_passenger); die;
				//SAVE Pax Details
				//	debug($title);
				$pax_insert_id = $GLOBALS['CI']->flight_model->save_flight_booking_passenger_details(
					$app_reference,
					$passenger_type,
					$is_lead,
					$title,
					$first_name,
					$middle_name,
					$last_name,
					$date_of_birth,
					$remarks_text,
					$gender,
					$passenger_nationality,
					$passport_number,
					$passport_issuing_country,
					$passport_expiry_date,
					$status,
					json_encode($passenger_attributes),
					$flight_booking_transaction_details_fk,
					$pax_breakup,
					$transit_passenger,
					$scp_status
				);
				//debug($pax_insert_id); die;
				// debug($segment_summary);exit;
				if ($token_index == 0) {
					$seat_flight_id = $flight_id = $segment_summary[$token_index][$token_index]['OriginDetails']['fsid'];
					$seat_flight_details_id = $flight_details_id = $token_value['flight_details_id'];
					$seat_airline_code = $airline_code = $segment_summary[$token_index][$token_index]['AirlineDetails']['AirlineCode'];
				}

				if ($token_index == 1) {
					if (isset($segment_summary[$token_index][$token_index]['OriginDetails']['fsid'])) {
						$seat_flight_id = $return_flight_id = $segment_summary[$token_index][$token_index]['OriginDetails']['fsid'];
					} elseif (isset($segment_summary[$token_index][0]['OriginDetails']['fsid'])) {
						$seat_flight_id = $return_flight_id = $segment_summary[$token_index][0]['OriginDetails']['fsid'];
					}
					$seat_flight_details_id = $return_flight_details_id = $token_value['flight_details_id'];
					if (isset($segment_summary[$token_index][$token_index]['AirlineDetails']['AirlineCode'])) {
						$seat_airline_code = $return_airline_code = $segment_summary[$token_index][$token_index]['AirlineDetails']['AirlineCode'];
					} elseif (isset($segment_summary[$token_index][0]['AirlineDetails']['AirlineCode'])) {
						$seat_airline_code = $return_airline_code = $segment_summary[$token_index][0]['AirlineDetails']['AirlineCode'];
					}
				} else {
					$return_flight_id = '';
					$return_flight_details_id = '';
				}

				if ($paxType != 'INFANT') {
					$res_seat_data = array(
						'app_reference' => $app_reference,
						'passenger_details_origin' => $pax_insert_id['insert_id'],
						'from_airport_code' => $from_loc,
						'to_airport_code' => $to_loc,
						'airline_code' => $seat_airline_code,
						'flight_number' => $seat_flight_id,
						'flight_details_id' => $seat_flight_details_id,
						'description' => 1,
						'price' => $seat_price,
						'seat_number' => $seat_number
					);




					$seat_booking_id = $GLOBALS['CI']->custom_db->insert_record('api_flight_booking_seat_details', $res_seat_data);
				}

				$this->file_upload($pax_insert_id['insert_id'], $i + 1);

				//Save passenger ticket information
				$passenger_ticket_info = $GLOBALS['CI']->flight_model->save_passenger_ticket_info($pax_insert_id['insert_id']);
			} //Adding Pax Details Ends	
			//Saving Segment Details
			// 			debug($segment_details);exit;
			foreach ($segment_details as $seg_k => $seg_v) {
				// debug($seg_v);exit;
				$curr_segment_indicator = 1;

				foreach ($seg_v as $ws_key => $ws_val) {

					$FareRestriction = '';
					$FareBasisCode = '';
					if ($ws_key == 0) {
						$FareRuleDetail = $FareRule;
					} else {
						$FareRuleDetail = '';
					}
					$airline_pnr = '';
					$AirlineDetails = $ws_val['AirlineDetails'];
					$OriginDetails = $ws_val['OriginDetails'];
					$DestinationDetails = $ws_val['DestinationDetails'];
					//$segment_indicator = $ws_val['SegmentIndicator'];
					$segment_indicator = ($curr_segment_indicator++);

					$airline_code = $AirlineDetails['AirlineCode'];
					$airline_name = $AirlineDetails['AirlineName'];
					$flight_number = $AirlineDetails['FlightNumber'];
					$fare_class = $AirlineDetails['FareClass'];
					$from_airport_code = $OriginDetails['AirportCode'];

					$from_airport_result = $this->CI->custom_db->single_table_records('flight_airport_list', 'airport_name', array('airport_code' => $OriginDetails['AirportCode']));
					// debug($from_airport_result);exit;

					$from_airport_name = $from_airport_result['data'][0]['airport_name'];
					$to_airport_code = $DestinationDetails['AirportCode'];

					$to_airport_result = $this->CI->custom_db->single_table_records('flight_airport_list', 'airport_name', array('airport_code' => $DestinationDetails['AirportCode']));

					$to_airport_name =  $to_airport_result['data'][0]['airport_name'];
					$departure_datetime = date('Y-m-d H:i:s', strtotime($OriginDetails['DateTime']));
					$arrival_datetime = date('Y-m-d H:i:s', strtotime($DestinationDetails['DateTime']));
					$iti_status = '';
					$operating_carrier = $AirlineDetails['AirlineCode'];
					$attributes = array('craft' => $ws_val['Craft'], 'ws_val' => $ws_val);
					//SAVE ITINERARY
					$GLOBALS['CI']->flight_model->save_flight_booking_itinerary_details(
						$app_reference,
						$segment_indicator,
						$airline_code,
						$airline_name,
						$flight_number,
						$fare_class,
						$from_airport_code,
						$from_airport_name,
						$to_airport_code,
						$to_airport_name,
						$departure_datetime,
						$arrival_datetime,
						$iti_status,
						$operating_carrier,
						json_encode($attributes),
						$FareRestriction,
						$FareBasisCode,
						$FareRuleDetail,
						$airline_pnr,
						$transaction_insert_id
					);
					break;
				}
			} //End Of Segments Loop

		} //End Of Token Loop
		//	 exit;
		//Save Master Booking Details
		$book_total_fare = array_sum($book_total_fare);
		$book_domain_markup = array_sum($book_domain_markup);
		$book_level_one_markup = array_sum($book_level_one_markup);

		$phone = $book_params['passenger_contact'];
		$alternate_number = '';
		$email = $book_params['billing_email'];
		$start = $token[0];
		$end = end($token);

		$journey_start = $segment_summary[0][0]['OriginDetails']['DateTime'];
		$journey_start = date('Y-m-d H:i:s', strtotime($journey_start));
		$journey_end = end(end($segment_summary));
		$journey_end = $journey_end['DestinationDetails']['DateTime'];
		$journey_end = date('Y-m-d H:i:s', strtotime($journey_end));
		$payment_mode = $book_params['payment_method'];
		$created_by_id = intval($GLOBALS['CI']->entity_user_id);

		$passenger_country_id = intval($book_params['billing_country']);
		//$passenger_city_id = intval($book_params['billing_city']);
		$passenger_country = $GLOBALS['CI']->db_cache_api->get_country_list(array('k' => 'origin', 'v' => 'name'), array('origin' => $passenger_country_id));
		//$passenger_city = $GLOBALS['CI']->db_cache_api->get_city_list(array('k' => 'origin', 'v' => 'destination'), array('origin' => $passenger_city_id));

		$passenger_country = isset($passenger_country[$passenger_country_id]) ? $passenger_country[$passenger_country_id] : '';
		//$passenger_city = isset($passenger_city[$passenger_city_id]) ? $passenger_city[$passenger_city_id] : '';
		$passenger_city = $book_params['billing_city'];

		$baggage = array();
		if ($book_params['trip_type'] == 'circle') {
			$baggage['first']['extra_baggage'] = $book_params['extra_baggage_first'];
			$baggage['first']['no_extra_baggage'] = $book_params['no_extra_baggage_first'];
			$baggage['first']['trip_type'] = 'circle';
			$baggage['first']['for_trip'] = 'first';

			$baggage['second']['extra_baggage'] = $book_params['extra_baggage_second'];
			$baggage['second']['no_extra_baggage'] = $book_params['no_extra_baggage_second'];
			$baggage['second']['trip_type'] = 'circle';
			$baggage['second']['for_trip'] = 'second';
		} else {

			$baggage['first']['extra_baggage'] = $book_params['extra_baggage_first'];
			$baggage['first']['no_extra_baggage'] = $book_params['no_extra_baggage_first'];
			$baggage['first']['trip_type'] = 'single';
			$baggage['first']['for_trip'] = 'first';
		}
		//debug($master_transaction_status);exit;
		//debug($master_transaction_status);exit;
		$attributes = array('country' => $passenger_country, 'city' => $passenger_city, 'zipcode' => $book_params['billing_zipcode'], 'address' =>  $book_params['billing_address_1'], 'extra_baggage' => $baggage['first']['extra_baggage'], 'no_extra_baggage' => $baggage['first']['no_extra_baggage'], 'trip_type' => $baggage['first']['trip_type'], 'for_trip' =>$baggage['first']['for_trip'] , 'all_baggage' => $extra_service_details['baggage']);


		//debug($master_transaction_status);exit;
		$flight_booking_status = $master_transaction_status;
		//SAVE Booking Details
		//debug($return_flight_id);die;
		$GLOBALS['CI']->flight_model->save_flight_booking_details(
			$domain_origin,
			$flight_booking_status,
			$app_reference,
			$booking_source,
			$phone,
			$alternate_number,
			$email,
			$journey_start,
			$journey_end,
			$journey_from,
			$journey_to,
			$payment_mode,
			json_encode($attributes),
			$created_by_id,
			$from_loc,
			$to_loc,
			$from_to_trip_type,
			$transaction_currency,
			$currency_conversion_rate,
			$flight_id,
			$flight_details_id,
			$return_flight_id,
			$return_flight_details_id
		);

		//Save Passenger Baggage Details
		if (isset($extra_service_details['ExtraServiceDetails']['Baggage']) == true && valid_array($extra_service_details['ExtraServiceDetails']['Baggage']) == true) {
			$this->save_passenger_baggage_info($app_reference, $book_params, $extra_service_details['ExtraServiceDetails']['Baggage']);
		}


		//Save Passenger Meals Details
		if (isset($extra_service_details['ExtraServiceDetails']['Meals']) == true && valid_array($extra_service_details['ExtraServiceDetails']['Meals']) == true) {
			$this->save_passenger_meal_info($app_reference, $book_params, $extra_service_details['ExtraServiceDetails']['Meals']);
		}

		//Save Passenger Meals Details
		if (isset($extra_service_details['ExtraServiceDetails']['Seat']) == true && valid_array($extra_service_details['ExtraServiceDetails']['Seat']) == true) {
			$this->save_passenger_seat_info($app_reference, $book_params, $extra_service_details['ExtraServiceDetails']['Seat']);
		}

		//Meal Preference
		if (isset($extra_service_details['ExtraServiceDetails']['MealPreference']) == true && valid_array($extra_service_details['ExtraServiceDetails']['MealPreference']) == true) {
			$this->save_passenger_meal_preference($app_reference, $book_params, $extra_service_details['ExtraServiceDetails']['MealPreference']);
		}

		//Seat Preference
		if (isset($extra_service_details['ExtraServiceDetails']['SeatPreference']) == true && valid_array($extra_service_details['ExtraServiceDetails']['SeatPreference']) == true) {
			$this->save_passenger_seat_preference($app_reference, $book_params, $extra_service_details['ExtraServiceDetails']['SeatPreference']);
		}

		//Add Extra Service Price to published price
		$GLOBALS['CI']->flight_model->add_extra_service_price_to_published_fare($app_reference);

		//Adding Extra services Total Price
		$extra_services_total_price = $GLOBALS['CI']->flight_model->get_extra_services_total_price($app_reference);
		$book_total_fare += $extra_services_total_price;

		/************** Update Convinence Fees And Other Details Start ******************/
		//Convinence_fees to be stored and discount
		$convinence = 0;
		$discount = 0;
		$convinence_value = 0;
		$convinence_type = 0;
		$convinence_type = 0;
		if ($module == 'b2c') {
			//$total_transaction_amount = $book_total_fare+$book_domain_markup;
			$total_transaction_amount = $book_total_fare;
			$convinence = $currency_obj->convenience_fees($total_transaction_amount, $master_search_id);
			// debug($convinence);exit;
			$convinence_row = $currency_obj->get_convenience_fees();

			$convinence_value = $convinence_row['value'];
			$convinence_type = $convinence_row['type'];
			$convinence_per_pax = $convinence_row['per_pax'];
			$discount = $book_params['promo_code_discount_val'];
			$promo_code = $book_params['promo_code'];

			$gst_tax = $this->CI->custom_db->single_table_records('convenience_fees', '*', array('module' => 'GST'));


			// $amt = $total_transaction_amount+$convinence_value;
			$amt = $total_transaction_amount + $convinence;
			// debug($amt);exit;
			if ($gst_tax['status']) {

				$gst_fees_details  = $gst_tax['data'][0];
				$gst_fees = 0;

				if ($gst_fees_details['value_type'] == 'plus') {
					$gst_fees = $gst_fees_details['value'];
				} elseif ($gst_fees_details['value_type'] == 'percentage') {
					//	$gst_fees = (($amt/100)* $gst_fees_details['value'] );

					$gg = (($amt / 100) * $gst_fees_details['value']);
					$gst_fees = number_format($gg, 2);
				}
			}


			// debug($gst_fees);exit;


			$gst_value = $gst_fees_details['value'];
			$gst_type = $gst_fees_details['value_type'];
			$gst_per_pax = $gst_fees_details['per_pax'];
			$gst_amount = $gst_fees;
		} elseif ($module == 'b2b') {
			$total_transaction_amount = $book_total_fare + $book_domain_markup;
			if ($book_params['payment_mode'] == 1) {
				$convinence = $currency_obj->convenience_fees($total_transaction_amount, $master_search_id);
				$convinence_row = $currency_obj->get_convenience_fees();
				$convinence_value = $convinence_row['value'];
				$convinence_type = $convinence_row['type'];
				$convinence_per_pax = $convinence_row['per_pax'];
			} else {
				$convinence_per_pax = 0;
			}
			$discount = 0;
		}
		$GLOBALS['CI']->load->model('transaction');
		//SAVE Convinience and Discount Details


		$GLOBALS['CI']->transaction->update_convinence_discount_details('flight_booking_details', $app_reference, $discount, $promo_code, $convinence, $convinence_value, $convinence_type, $convinence_per_pax, $gst_amount, $gst_value, $gst_type, $gst_per_pax);

		/************** Update Convinence Fees And Other Details End ******************/

		/**
		 * Data to be returned after transaction is saved completely
		 */

		$response['fare'] = $book_total_fare;
		$response['admin_markup'] = $book_domain_markup;
		$response['agent_markup'] = $book_level_one_markup;
		$response['convinence'] = $convinence;
		$response['discount'] = $discount;

		$response['status'] = $flight_booking_status;
		$response['status_description'] = $transaction_description;
		$response['name'] = $first_name;
		$response['phone'] = $phone;

		// debug($response);exit;
		return $response;
	}

	function file_upload($origin, $pax_id)
	{
		$config = [];
		$update_data = [];
		//FILE UPLOAD

		for ($i = 0; $i < 3; $i++) {

			if ($i == 0) {
				$doc = 'passport_' . $pax_id;
			} elseif ($i == 1) {
				$doc = 'visa_' . $pax_id;
			} else {
				$doc = 'transist_' . $pax_id;
			}

			if (valid_array($_FILES[$doc]) == true and $_FILES[$doc]['error'] == 0 and $_FILES[$doc]['size'] > 0) {

				$config['upload_path'] = DOMAIN_IMAGE_UPLOAD_DIR . 'passport_visa/';

				$config['allowed_types'] = '*';
				$config['file_name'] = time();
				$config['max_size'] = '1000000';
				$config['max_width']  = '';
				$config['max_height']  = '';
				$config['remove_spaces']  = false;
				// //UPDATE
				// $temp_record = $this->custom_db->single_table_records('flight_booking_passenger_details', 'image', array('origin' => $origin));
				// $icon = $temp_record['data'][0]['image'];
				// //DELETE OLD FILES
				// if (empty($icon) == false) {
				// 	$temp_profile_image = $this->template->domain_image_full_path($icon);//GETTING FILE PATH
				// 	if (file_exists($temp_profile_image)) {
				// 		unlink($temp_profile_image);
				// 	}
				// }


				// debug($config);exit;
				//UPLOAD IMAGE
				// $this->load->library('upload', $config);
				$this->CI = &get_instance();
				$this->CI->load->library('upload', $config);

				if (! $this->CI->upload->do_upload($doc)) {
					echo $this->CI->upload->display_errors();
				} else {
					$image_data =  $this->CI->upload->data();
				}

				if ($doc == 'passport_' . $pax_id) {
					$update_data['passport_image'] = $image_data['file_name'];
				} elseif ($doc == 'visa_' . $pax_id) {
					$update_data['visa_image'] = $image_data['file_name'];
				} else {
					$update_data['transist_image'] = $image_data['file_name'];
				}
			}
		}
		// debug($update_data);exit;
		if (valid_array($update_data)) {
			$this->CI->custom_db->update_record('flight_booking_passenger_details', $update_data, array('origin' => $origin));
		}
	}

	public function extract_extra_service_details($book_params)
	{
		$extra_services = array();
		if (
			isset($book_params['token']['extra_services']) && isset($book_params['token']['extra_services']['status']) == true && $book_params['token']['extra_services']['status'] == SUCCESS_STATUS
			&& isset($book_params['token']['extra_services']['data']['ExtraServiceDetails']) == true && valid_array($book_params['token']['extra_services']['data']['ExtraServiceDetails']) == true
		) {

			$ExtraServiceDetails = $book_params['token']['extra_services']['data']['ExtraServiceDetails'];

			//re-index baggage details with BaggageId
			$reindexed_baggage = array();
			if (isset($ExtraServiceDetails['Baggage']) == true && valid_array($ExtraServiceDetails['Baggage']) == true) {
				$Baggage = $ExtraServiceDetails['Baggage'];
				foreach ($Baggage as $ob_k => $ob_v) {
					foreach ($ob_v as $bk => $bv) {
						$reindexed_baggage[$bv['BaggageId']] = $bv;
					}
				}
			}

			//re-index meal details with MealId
			$reindexed_meal = array();
			if (isset($ExtraServiceDetails['Meals']) == true && valid_array($ExtraServiceDetails['Meals']) == true) {
				$Meals = $ExtraServiceDetails['Meals'];
				foreach ($Meals as $om_k => $om_v) {
					foreach ($om_v as $mk => $mv) {
						$reindexed_meal[$mv['MealId']] = $mv;
					}
				}
			}
			//re-index seat details with SeatId
			$reindexed_seat = array();
			if (isset($ExtraServiceDetails['Seat']) == true && valid_array($ExtraServiceDetails['Seat']) == true) {
				$Seat = $ExtraServiceDetails['Seat'];
				foreach ($Seat as $os_k => $os_v) {
					foreach ($os_v as $sk => $sv) {
						foreach ($sv as $seat_index => $seat_value) {
							$reindexed_seat[$seat_value['SeatId']] = $seat_value;
						}
					}
				}
			}
			//Meal Preference - re-index meal details with MealId
			$reindexed_meal_pref = array();
			if (isset($ExtraServiceDetails['MealPreference']) == true && valid_array($ExtraServiceDetails['MealPreference']) == true) {
				$Meals = $ExtraServiceDetails['MealPreference'];
				foreach ($Meals as $om_k => $om_v) {
					foreach ($om_v as $mk => $mv) {
						$reindexed_meal_pref[$mv['MealId']] = $mv;
					}
				}
			}
			//Seat Preference - re-index seat details with SeatId
			$reindexed_seat_pref = array();
			if (isset($ExtraServiceDetails['SeatPreference']) == true && valid_array($ExtraServiceDetails['SeatPreference']) == true) {
				$Seats = $ExtraServiceDetails['SeatPreference'];
				foreach ($Seats as $os_k => $os_v) {
					foreach ($os_v as $sk => $sv) {
						$reindexed_seat_pref[$sv['SeatId']] = $sv;
					}
				}
			}

			//Assigning the values
			if (valid_array($reindexed_baggage) == true) {
				$extra_services['ExtraServiceDetails']['Baggage'] = $reindexed_baggage;
			}
			if (valid_array($reindexed_meal) == true) {
				$extra_services['ExtraServiceDetails']['Meals'] = $reindexed_meal;
			}
			if (valid_array($reindexed_seat) == true) {
				$extra_services['ExtraServiceDetails']['Seat'] = $reindexed_seat;
			}
			if (valid_array($reindexed_meal_pref) == true) {
				$extra_services['ExtraServiceDetails']['MealPreference'] = $reindexed_meal_pref;
			}
			if (valid_array($reindexed_seat_pref) == true) {
				$extra_services['ExtraServiceDetails']['SeatPreference'] = $reindexed_seat_pref;
			}
		}

		return $extra_services;
	}

	private function get_final_booking_price_details($Fare, $multiplier, $specific_markup_config, $currency_obj, $deduction_cur_obj, $module)
	{
		$data = array();
		$core_agent_commision = ($Fare['PublishedFare'] - $Fare['OfferedFare']);
		$commissionable_fare = $Fare['PublishedFare'];
		if ($module == 'b2c') {
			$trans_total_fare = $this->total_price($Fare, false, $currency_obj);
			$markup_total_fare	= $currency_obj->get_currency($trans_total_fare, true, false, true, $multiplier, $specific_markup_config);
			$ded_total_fare		= $deduction_cur_obj->get_currency($trans_total_fare, true, true, false, $multiplier, $specific_markup_config);
			$admin_markup = roundoff_number($markup_total_fare['default_value'] - $ded_total_fare['default_value']);
			$admin_commission = $core_agent_commision;
			$agent_markup = 0;
			$agent_commission = 0;
		} else {
			//B2B Calculation
			//Markup
			$trans_total_fare = $Fare['PublishedFare'];

			$markup_total_fare	= $currency_obj->get_currency($trans_total_fare, true, true, true, $multiplier, $specific_markup_config);
			$ded_total_fare		= $deduction_cur_obj->get_currency($trans_total_fare, true, false, true, $multiplier, $specific_markup_config);

			$admin_markup = abs($markup_total_fare['default_value'] - $ded_total_fare['default_value']);
			$agent_markup = roundoff_number($ded_total_fare['default_value'] - $trans_total_fare);
			//Commission
			$this->commission = $currency_obj->get_commission();
			$AgentCommission = $this->calculate_commission($core_agent_commision);
			$admin_commission = roundoff_number($core_agent_commision - $AgentCommission); //calculate here
			$agent_commission = roundoff_number($AgentCommission);
		}
		//TDS Calculation
		$admin_tds = $currency_obj->calculate_tds($admin_commission);
		$agent_tds = $currency_obj->calculate_tds($agent_commission);

		// debug($Fare['extra_services']);
		//Extra Services Calculation
		$total_of_extra_services = 0;
		if (valid_array($Fare['extra_services'])) {
			foreach ($Fare['extra_services'] as $route_key => $extraServices) {

				foreach ($extraServices as $pax_key => $pax_value) {

					if (isset($pax_value['baggage_fare']['checkin_baggage_price']) && $pax_value['baggage_fare']['checkin_baggage_price'] > 0) {
						$total_of_extra_services += $pax_value['baggage_fare']['checkin_baggage_price'];
					}

					if (isset($pax_value['baggage_fare']['extra_baggage_price']) && $pax_value['baggage_fare']['extra_baggage_price'] > 0) {
						$total_of_extra_services += $pax_value['baggage_fare']['extra_baggage_price'];
					}

					if (isset($pax_value['seat']['seat_price']) && $pax_value['seat']['seat_price'] > 0) {
						$total_of_extra_services += $pax_value['seat']['seat_price'];
					}

					// debug($pax_value['meal_fare']);exit;
					if (isset($pax_value['meal_fare']['extra_meal_price']) && $pax_value['meal_fare']['extra_meal_price'] > 0) {

						$total_of_extra_services += $pax_value['meal_fare']['extra_meal_price'];

						// foreach ($pax_value['meal_fare']['extra_meal_price'] as $r_p_m_key => $r_p_m_value) {

						// 	if($r_p_m_value>0){
						// 	$total_of_extra_services+=$r_p_m_value;
						// }
						// }

					}
				}
			}
		}
		// debug($total_of_extra_services);exit;
		$trans_total_fare = $trans_total_fare + $total_of_extra_services;
		// debug($trans_total_fare);exit;
		// debug($total_of_extra_services);exit;
		$data['commissionable_fare'] = $commissionable_fare;
		$data['extra_services_fare'] = $total_of_extra_services;
		$data['trans_total_fare'] = $trans_total_fare;
		$data['admin_markup'] = $admin_markup;
		$data['agent_markup'] = $agent_markup;
		$data['admin_commission'] = $admin_commission;
		$data['agent_commission'] = $agent_commission;
		$data['admin_tds'] = $admin_tds;
		$data['agent_tds'] = $agent_tds;

		//debug($data);exit;
		return $data;
	}

	private function save_passenger_baggage_info($app_reference, $book_params, $baggage_details)
	{
		$stored_booking_details = $GLOBALS['CI']->flight_model->get_booking_details($app_reference);
		$GLOBALS['CI']->load->library('booking_data_formatter');
		$booking_details = $GLOBALS['CI']->booking_data_formatter->format_flight_booking_data($stored_booking_details, $GLOBALS['CI']->config->item('current_module'));
		$booking_details = $booking_details['data']['booking_details']['0'];
		$booking_transaction_details = $booking_details['booking_transaction_details'];

		$baggage_index = 0;
		while (isset($book_params["baggage_$baggage_index"]) == true) {
			foreach ($booking_transaction_details as $tr_k => $tr_v) {
				if (count($booking_transaction_details) == 2) {
					if ($tr_k == 0) {
						$journy_type = 'onward_journey';
					} else {
						$journy_type = 'return_journey';
					}
				} else {
					$journy_type = 'full_journey';
				}

				//
				foreach ($book_params["baggage_$baggage_index"] as $bag_k => $bag_v) {

					if (empty($bag_v) == false && isset($baggage_details[$bag_v]) == true && $baggage_details[$bag_v]['JourneyType'] == $journy_type) {
						$passenger_fk = 		$tr_v['booking_customer_details'][$bag_k]['origin'];
						$from_airport_code =	$baggage_details[$bag_v]['Origin'];
						$to_airport_code = 		$baggage_details[$bag_v]['Destination'];
						$description = 			$baggage_details[$bag_v]['Weight'];
						$price = 				$baggage_details[$bag_v]['Price'];
						$code = 				$baggage_details[$bag_v]['Code'];

						//Save passenger baggage information
						$GLOBALS['CI']->flight_model->save_passenger_baggage_info($passenger_fk, $from_airport_code, $to_airport_code, $description, $price, $code);
					}
				}
			}
			$baggage_index++;
		}
	}

	private function save_passenger_meal_info($app_reference, $book_params, $meal_details)
	{
		$stored_booking_details = $GLOBALS['CI']->flight_model->get_booking_details($app_reference);
		$GLOBALS['CI']->load->library('booking_data_formatter');
		$booking_details = $GLOBALS['CI']->booking_data_formatter->format_flight_booking_data($stored_booking_details, $GLOBALS['CI']->config->item('current_module'));
		$booking_details = $booking_details['data']['booking_details']['0'];
		$booking_transaction_details = $booking_details['booking_transaction_details'];

		$meal_index = 0;
		while (isset($book_params["meal_$meal_index"]) == true) {
			foreach ($booking_transaction_details as $tr_k => $tr_v) {
				if (count($booking_transaction_details) == 2) {
					if ($tr_k == 0) {
						$journy_type = 'onward_journey';
					} else {
						$journy_type = 'return_journey';
					}
				} else {
					$journy_type = 'full_journey';
				}

				//
				foreach ($book_params["meal_$meal_index"] as $meal_k => $meal_v) {

					if (empty($meal_v) == false && isset($meal_details[$meal_v]) == true && $meal_details[$meal_v]['JourneyType'] == $journy_type) {
						$passenger_fk = 		$tr_v['booking_customer_details'][$meal_k]['origin'];
						$from_airport_code =	$meal_details[$meal_v]['Origin'];
						$to_airport_code = 		$meal_details[$meal_v]['Destination'];
						$description = 			$meal_details[$meal_v]['Description'];
						$price = 				$meal_details[$meal_v]['Price'];
						$code = 				$meal_details[$meal_v]['Code'];
						//Save passenger meal information
						$GLOBALS['CI']->flight_model->save_passenger_meals_info($passenger_fk, $from_airport_code, $to_airport_code, $description, $price, $code);
					}
				}
			}
			$meal_index++;
		}
	}

	private function save_passenger_seat_info($app_reference, $book_params, $seat_details)
	{
		$stored_booking_details = $GLOBALS['CI']->flight_model->get_booking_details($app_reference);
		$GLOBALS['CI']->load->library('booking_data_formatter');
		$booking_details = $GLOBALS['CI']->booking_data_formatter->format_flight_booking_data($stored_booking_details, $GLOBALS['CI']->config->item('current_module'));
		$booking_details = $booking_details['data']['booking_details']['0'];
		$booking_transaction_details = $booking_details['booking_transaction_details'];

		$seat_index = 0;
		while (isset($book_params["seat_$seat_index"]) == true) {
			foreach ($booking_transaction_details as $tr_k => $tr_v) {
				if (count($booking_transaction_details) == 2) {
					if ($tr_k == 0) {
						$journy_type = 'onward_journey';
					} else {
						$journy_type = 'return_journey';
					}
				} else {
					$journy_type = 'full_journey';
				}

				//
				foreach ($book_params["seat_$seat_index"] as $seat_k => $seat_v) {

					if (empty($seat_v) == false && isset($seat_details[$seat_v]) == true && $seat_details[$seat_v]['JourneyType'] == $journy_type) {

						$passenger_fk = 		$tr_v['booking_customer_details'][$seat_k]['origin'];
						$from_airport_code =	$seat_details[$seat_v]['Origin'];
						$to_airport_code = 		$seat_details[$seat_v]['Destination'];
						$description = 			'';
						$price = 				$seat_details[$seat_v]['Price'];
						$code = 				$seat_details[$seat_v]['SeatNumber'];
						$airline_code = 		$seat_details[$seat_v]['AirlineCode'];
						$flight_number = 		$seat_details[$seat_v]['FlightNumber'];

						//Save passenger seat information
						$GLOBALS['CI']->flight_model->save_passenger_seat_info($passenger_fk, $from_airport_code, $to_airport_code, $description, $price, $code, 'dynamic', $airline_code, $flight_number);
					}
				}
			}
			$seat_index++;
		}
	}

	private function save_passenger_meal_preference($app_reference, $book_params, $meal_details)
	{
		$stored_booking_details = $GLOBALS['CI']->flight_model->get_booking_details($app_reference);
		$GLOBALS['CI']->load->library('booking_data_formatter');
		$booking_details = $GLOBALS['CI']->booking_data_formatter->format_flight_booking_data($stored_booking_details, $GLOBALS['CI']->config->item('current_module'));
		$booking_details = $booking_details['data']['booking_details']['0'];
		$booking_transaction_details = $booking_details['booking_transaction_details'];

		$meal_index = 0;
		while (isset($book_params["meal_pref$meal_index"]) == true) {
			foreach ($booking_transaction_details as $tr_k => $tr_v) {
				if (count($booking_transaction_details) == 2) {
					if ($tr_k == 0) {
						$journy_type = 'onward_journey';
					} else {
						$journy_type = 'return_journey';
					}
				} else {
					$journy_type = 'full_journey';
				}

				//
				foreach ($book_params["meal_pref$meal_index"] as $meal_k => $meal_v) {

					if (empty($meal_v) == false && isset($meal_details[$meal_v]) == true && $meal_details[$meal_v]['JourneyType'] == $journy_type) {
						$passenger_fk = 		$tr_v['booking_customer_details'][$meal_k]['origin'];
						$from_airport_code =	$meal_details[$meal_v]['Origin'];
						$to_airport_code = 		$meal_details[$meal_v]['Destination'];
						$description = 			$meal_details[$meal_v]['Description'];
						$price = 				0;
						$code = 				$meal_details[$meal_v]['Code'];
						//Save passenger meal information
						$GLOBALS['CI']->flight_model->save_passenger_meals_info($passenger_fk, $from_airport_code, $to_airport_code, $description, $price, $code, 'static');
					}
				}
			}
			$meal_index++;
		}
	}

	private function save_passenger_seat_preference($app_reference, $book_params, $seat_details)
	{
		$stored_booking_details = $GLOBALS['CI']->flight_model->get_booking_details($app_reference);
		$GLOBALS['CI']->load->library('booking_data_formatter');
		$booking_details = $GLOBALS['CI']->booking_data_formatter->format_flight_booking_data($stored_booking_details, $GLOBALS['CI']->config->item('current_module'));
		$booking_details = $booking_details['data']['booking_details']['0'];
		$booking_transaction_details = $booking_details['booking_transaction_details'];

		$seat_index = 0;
		while (isset($book_params["seat_pref$seat_index"]) == true) {
			foreach ($booking_transaction_details as $tr_k => $tr_v) {
				if (count($booking_transaction_details) == 2) {
					if ($tr_k == 0) {
						$journy_type = 'onward_journey';
					} else {
						$journy_type = 'return_journey';
					}
				} else {
					$journy_type = 'full_journey';
				}

				//
				foreach ($book_params["seat_pref$seat_index"] as $seat_k => $seat_v) {

					if (empty($seat_v) == false && isset($seat_details[$seat_v]) == true && $seat_details[$seat_v]['JourneyType'] == $journy_type) {
						$passenger_fk = 		$tr_v['booking_customer_details'][$seat_k]['origin'];
						$from_airport_code =	$seat_details[$seat_v]['Origin'];
						$to_airport_code = 		$seat_details[$seat_v]['Destination'];
						$description = 			$seat_details[$seat_v]['Description'];
						$price = 				0;
						$code = 				$seat_details[$seat_v]['Code'];
						//Save passenger seat information
						$GLOBALS['CI']->flight_model->save_passenger_seat_info($passenger_fk, $from_airport_code, $to_airport_code, $description, $price, $code, 'static');
					}
				}
			}
			$seat_index++;
		}
	}

	public function process_booking($book_id, $booking_params)
	{
		$response = [];
		// debug($booking_params); exit; pnr not coming
		foreach ($booking_params['token']['token'] as $k => $v) {
			$booking_params['token']['token'][$k]['SequenceNumber'] = $k;
		}
		$book_response = array();
		//$book_response = $this->book_flight($book_id, $booking_params);
		//debug($book_response); exit;

		$agent_details =	$GLOBALS['CI']->user_model->get_current_user_details();
		if ($agent_details[0]['reserve_booking'] == 0) {
			$response['status'] = BOOKING_HOLD;
			$book_response['status'] = BOOKING_HOLD;
		} else {
			$response['status'] = BOOKING_CONFIRMED;
			$book_response['status'] = BOOKING_CONFIRMED;
		}


		$wrapper_token = $booking_params['token'];

		if ($book_response['status'] == FAILURE_STATUS) {
			$response['status'] = FAILURE_STATUS;
			$response['message'] = $book_response['message'];
		} else {
			$ticket_response = $book_response;
			$response['status'] = $ticket_response['status'];
		}
		//Extracting Response
		$response['data']['ticket']['TicketDetails'] = @$ticket_response['data'];
		$response['data']['book_id'] = $book_id;
		$response['data']['booking_params'] = $booking_params;
		//	debug($response);
		return $response;
	}

	function book_flight($book_id, $booking_params)
	{
		$response = [];
		// debug($booking_params); exit;
		$response['status'] = FAILURE_STATUS;
		$booking_response = array();
		$token_wrapper = $booking_params['token'];
		$op = $booking_params['op'];
		//check ONE WAY - Domestic / Intl & ROUND WAY - Intl - Run Once
		$unique_search_access_key = array_unique($token_wrapper['search_access_key']);
		// debug($unique_search_access_key); exit;
		if (count($unique_search_access_key) == 1) { // Single session is one request
			if (count($token_wrapper['search_access_key']) == 1) {
				// debug("2380"); exit; Data coming till here
				//Extract Passenger Information
				$passenger = $this->extract_passenger_info($booking_params, $token_wrapper['token'][0]['SequenceNumber']);
				// debug($passenger); exit; Coming
				if (isset($booking_params['ticket_method']) &&  $booking_params['ticket_method'] === 'hold_ticket') { //HOLD TICKET
					$tmp_res = $this->run_hold_booking($op, $book_id, $token_wrapper['token'][0], $passenger, $token_wrapper['search_access_key'][0]);
				} else { //DIRECT TICKETING
					$tmp_res = $this->run_commit_booking($op, $book_id, $token_wrapper['token'][0], $passenger, $token_wrapper['search_access_key'][0]);
					// debug($tmp_res); exit;
				}


				// if ($this->valid_flight_booking_status($tmp_res['status']) == true) {
				if (($tmp_res['status']) == true) {
					$booking_response[] = $tmp_res['data'];
					$response['status'] = $tmp_res['status'];
				} else {
					$response['message'] = $tmp_res['message'];
					$response['status'] = FAILURE_STATUS;
				}
			}
		} else { // multiple request is two request
			//Domestic Round - Run Twice
			foreach ($token_wrapper['token'] as $___k => $___v) {
				//Extract Passenger Information
				// debug("2402"); exit;
				$passenger = $this->extract_passenger_info($booking_params, $___v['SequenceNumber']);
				// debug($passenger); exit;
				$tmp_resp = $this->run_commit_booking($op, $book_id, $___v, $passenger, $token_wrapper['search_access_key'][$___k]);
				if ($this->valid_flight_booking_status($tmp_resp['status']) == true) {

					$booking_response[$___k] = $tmp_resp['data'];

					if ($response['status'] != BOOKING_CONFIRMED) {
						$response['status'] = $tmp_resp['status'];
					}
				} else {
					$booking_response[$___k]['Status'] = $tmp_resp['status'];
					$booking_response[$___k]['Message'] = $tmp_resp['message'];
					$response['message'] = $booking_response[$___k]['message'];
					if ($this->valid_flight_booking_status($response['status']) == false) { //Even if one booking is Hold/Success, return the status as Hold/Success
						$response['status'] = FAILURE_STATUS;
					}
					break;
				}
			}
		}
		$response['data'] = $booking_response;
		return $response;
	}

	private function extract_passenger_info($booking_params, $SequenceNumber)
	{
		$passenger = [];
		$extra_service_details = $this->extract_extra_service_details($booking_params);
		// debug($extra_service_details); exit; coming
		$country_list = $GLOBALS['CI']->db_cache_api->get_country_list(array('k' => 'origin', 'v' => 'iso_country_code'));
		//$city_list = $GLOBALS['CI']->db_cache_api->get_city_list();
		$passenger['lead_passenger']		= $booking_params['lead_passenger'];
		foreach ($booking_params['name_title'] as $__k => $__v) {
			$passenger['name_title'][$__k]	= @get_enum_list('title', $__v);
		}
		$passenger['first_name']			= $booking_params['first_name'];
		//$passenger['middle_name']			= $booking_params['middle_name'];
		$passenger['last_name']				= $booking_params['last_name'];
		$passenger['date_of_birth']			= $booking_params['date_of_birth'];
		foreach ($booking_params['passenger_type'] as $__k => $__v) {
			$passenger['passenger_type'][$__k]		= $this->pax_type($__v);
		}
		foreach ($booking_params['gender'] as $__k => $__v) {
			$gender		= (isset($__v) ? get_enum_list('gender', $__v) : '');
			$passenger['gender'][$__k] = $this->gender_type($gender);
		}
		foreach ($booking_params['passenger_nationality'] as $__k => $__v) {
			$passenger['passenger_nationality'][$__k]	= (isset($country_list[$__v]) ? $country_list[$__v] : '');
		}

		foreach ($booking_params['passenger_passport_issuing_country'] as $__k => $__v) {
			$passenger['passenger_passport_issuing_country'][$__k]	= (isset($country_list[$__v]) ? $country_list[$__v] : '');
		}
		//$passenger['passport_number'] = $booking_params['passenger_passport_number'];
		$passenger['passport_number'] = preg_replace('/\s+/', '', $booking_params['passenger_passport_number']);


		foreach ($passenger['passport_number'] as $__k => $__v) {
			if (empty($__v) == false) {
				//FIXME
				$pass_date = strtotime($booking_params['passenger_passport_expiry_year'][$__k] . '-' . $booking_params['passenger_passport_expiry_month'][$__k] . '-' . $booking_params['passenger_passport_expiry_day'][$__k]);
				$passenger['passport_expiry_date'][$__k]	= date('Y-m-d', $pass_date);
			} else {
				$passenger['passport_expiry_date'][$__k]	= '';
			}
		}

		if ($SequenceNumber == 0) {
			$journy_type = array('full_journey', 'onward_journey');
		} else {
			$journy_type = array('return_journey');
		}

		//Baggage
		if (isset($extra_service_details['ExtraServiceDetails']['Baggage']) == true && valid_array($extra_service_details['ExtraServiceDetails']['Baggage']) == true) {
			$Baggage = $extra_service_details['ExtraServiceDetails']['Baggage'];

			foreach ($booking_params['first_name'] as $__k => $__v) {
				$baggage_index = 0;
				$passenger_baggage = array();

				while (isset($booking_params["baggage_$baggage_index"]) == true) {
					if (
						isset($booking_params["baggage_$baggage_index"][$__k]) == true && empty($booking_params["baggage_$baggage_index"][$__k]) == false
						&& in_array($Baggage[$booking_params["baggage_$baggage_index"][$__k]]['JourneyType'], $journy_type) == true
					) {

						$passenger_baggage[] = $booking_params["baggage_$baggage_index"][$__k];
					}
					$baggage_index++;
				} //while ends

				if (valid_array($passenger_baggage) == true) {
					$passenger['baggage'][$__k]	= $passenger_baggage;
				}
			}
		} //Baggage ends


		//Meals
		if (isset($extra_service_details['ExtraServiceDetails']['Meals']) == true && valid_array($extra_service_details['ExtraServiceDetails']['Meals']) == true) {
			$Meals = $extra_service_details['ExtraServiceDetails']['Meals'];

			foreach ($booking_params['first_name'] as $__k => $__v) {
				$meal_index = 0;
				$passenger_meal = array();
				while (isset($booking_params["meal_$meal_index"]) == true) {
					if (
						isset($booking_params["meal_$meal_index"][$__k]) == true && empty($booking_params["meal_$meal_index"][$__k]) == false
						&& in_array($Meals[$booking_params["meal_$meal_index"][$__k]]['JourneyType'], $journy_type) == true
					) {
						$passenger_meal[] = $booking_params["meal_$meal_index"][$__k];
					}
					$meal_index++;
				}
				if (valid_array($passenger_meal) == true) {
					$passenger['meal'][$__k]	= $passenger_meal;
				}
			}
		} //Meal ends

		//Meals Preference
		if (isset($extra_service_details['ExtraServiceDetails']['MealPreference']) == true && valid_array($extra_service_details['ExtraServiceDetails']['MealPreference']) == true) {
			$Meals = $extra_service_details['ExtraServiceDetails']['MealPreference'];

			foreach ($booking_params['first_name'] as $__k => $__v) {
				$meal_index = 0;
				$passenger_meal_pref = array();
				while (isset($booking_params["meal_pref$meal_index"]) == true) {
					if (
						isset($booking_params["meal_pref$meal_index"][$__k]) == true && empty($booking_params["meal_pref$meal_index"][$__k]) == false
						&& in_array($Meals[$booking_params["meal_pref$meal_index"][$__k]]['JourneyType'], $journy_type) == true
					) {
						$passenger_meal_pref[] = $booking_params["meal_pref$meal_index"][$__k];
					}
					$meal_index++;
				}
				if (valid_array($passenger_meal_pref) == true) {
					$passenger['meal'][$__k]	= $passenger_meal_pref;
				}
			}
		} //Meal Preference ends

		//Seat
		if (isset($extra_service_details['ExtraServiceDetails']['Seat']) == true && valid_array($extra_service_details['ExtraServiceDetails']['Seat']) == true) {
			$Seat = $extra_service_details['ExtraServiceDetails']['Seat'];

			foreach ($booking_params['first_name'] as $__k => $__v) {
				$seat_index = 0;
				$passenger_seat = array();
				while (isset($booking_params["seat_$seat_index"]) == true) {
					if (
						isset($booking_params["seat_$seat_index"][$__k]) == true && empty($booking_params["seat_$seat_index"][$__k]) == false
						&& in_array($Seat[$booking_params["seat_$seat_index"][$__k]]['JourneyType'], $journy_type) == true
					) {
						$passenger_seat[] = $booking_params["seat_$seat_index"][$__k];
					}
					$seat_index++;
				}
				if (valid_array($passenger_seat) == true) {
					$passenger['seat'][$__k]	= $passenger_seat;
				}
			}
		} //Seat ends

		//Seat Preference
		if (isset($extra_service_details['ExtraServiceDetails']['SeatPreference']) == true && valid_array($extra_service_details['ExtraServiceDetails']['SeatPreference']) == true) {
			$SeatPreference = $extra_service_details['ExtraServiceDetails']['SeatPreference'];

			foreach ($booking_params['first_name'] as $__k => $__v) {
				$seat_index = 0;
				$passenger_seat_pref = array();
				while (isset($booking_params["seat_pref$seat_index"]) == true) {
					if (
						isset($booking_params["seat_pref$seat_index"][$__k]) == true && empty($booking_params["seat_pref$seat_index"][$__k]) == false
						&& in_array($SeatPreference[$booking_params["seat_pref$seat_index"][$__k]]['JourneyType'], $journy_type) == true
					) {
						$passenger_seat_pref[] = $booking_params["seat_pref$seat_index"][$__k];
					}
					$seat_index++;
				}
				if (valid_array($passenger_seat_pref) == true) {
					$passenger['seat'][$__k]	= $passenger_seat_pref;
				}
			}
		} //Seat Preference ends

		$passenger['billing_country'] = $country_list[$booking_params['billing_country']];
		$passenger['billing_country_name'] = 'India'; //FIXME: Make it Dynamic
		//$passenger['billing_city'] = $city_list[$booking_params['billing_city']];
		$passenger['billing_city'] = $booking_params['billing_city'];
		$passenger['billing_zipcode'] = $booking_params['billing_zipcode'];
		$passenger['billing_email'] = $booking_params['billing_email'];
		$passenger['billing_address_1'] = $booking_params['billing_address_1'];
		$passenger['passenger_contact'] = $booking_params['passenger_contact'];
		$passenger['st'] = 'BOOKING_PENDING';
		// debug($passenger); exit;
		return $passenger;
	}

	function run_hold_booking($op, $book_id, $token, $passenger, $search_access_key)
	{
		$response = [];
		$booking_params = [];
		$response['data'] = array();
		$response['status'] = FAILURE_STATUS;
		$response['message'] = '';
		$SequenceNumber = $token['SequenceNumber'];
		$booking_params['Passenger']			= $this->WSPassenger($passenger);

		//Prova Auth key
		$booking_params['ProvabAuthKey']		= $token['ProvabAuthKey'];
		$booking_params['SequenceNumber']		= $SequenceNumber;
		$api_request = $this->hold_booking_request($booking_params, $book_id);
		//get data
		if ($api_request['status']) {
			$header_info = $this->get_header();

			$this->CI->custom_db->generate_static_response(json_encode($api_request['data']['request']));

			$api_response = $this->CI->api_interface->get_json_response($api_request['data']['service_url'], $api_request['data']['request'], $header_info);
			$this->CI->custom_db->generate_static_response(json_encode($api_response));

			/*$static_id = 	1198;
			$api_response = $this->CI->flight_model->get_static_response($static_id);//378*/

			if ($this->valid_commit_booking_response($api_response) == true) {
				$api_response['CommitBooking'] = $api_response['HoldTicket'];
				unset($api_response['HoldTicket']);
				$api_response['CommitBooking']['BookingDetails']['Price'] = $this->convert_bookingdata_to_application_currency($api_response['CommitBooking']['BookingDetails']['Price']);

				$response['data'] = $api_response;
				$response['status'] = $api_response['Status'];
			} else {
				$response['message'] = $api_response['Message'];
				$response['status'] = FAILURE_STATUS;
			}
		}
		/** PROVAB LOGGER **/
		$GLOBALS['CI']->private_management_model->provab_xml_logger('Hold Booking', $book_id, 'flight', json_encode($api_request['data']), json_encode($api_response));
		return $response;
	}

	private function WSPassenger($passenger)
	{
		$tmp_passenger = array();
		$total_pax_count = count($passenger['passenger_type']);
		$i = 0;
		for ($i = 0; $i < $total_pax_count; $i++) {
			$tmp_passenger[$i]['IsLeadPax'] = $passenger['lead_passenger'][$i];
			$tmp_passenger[$i]['Title'] = $passenger['name_title'][$i];
			$tmp_passenger[$i]['FirstName'] = ((strlen($passenger['first_name'][$i]) < 2) ? str_repeat($passenger['first_name'][$i], 2) : $passenger['first_name'][$i]);
			$tmp_passenger[$i]['LastName'] = ((strlen($passenger['last_name'][$i]) < 2)   ? str_repeat($passenger['last_name'][$i], 2)  : $passenger['last_name'][$i]);
			$tmp_passenger[$i]['PaxType'] = $passenger['passenger_type'][$i];
			$tmp_passenger[$i]['Gender'] = $passenger['gender'][$i];
			$tmp_passenger[$i]['DateOfBirth'] = date('Y-m-d', strtotime($passenger['date_of_birth'][$i]));

			if (empty($passenger['passport_number'][$i]) == false and empty($passenger['passport_expiry_date'][$i]) == false) {
				$tmp_passenger[$i]['PassportNumber'] = $passenger['passport_number'][$i];
				$tmp_passenger[$i]['PassportExpiry'] = $passenger['passport_expiry_date'][$i];
			} else {
				$tmp_passenger[$i]['PassportNumber'] = '';
				$tmp_passenger[$i]['PassportExpiry'] = null;
			}

			$tmp_passenger[$i]['CountryCode'] = $passenger['passenger_nationality'][$i];
			$tmp_passenger[$i]['CountryName'] = $passenger['billing_country_name'];
			$tmp_passenger[$i]['ContactNo'] = $passenger['passenger_contact'];
			$tmp_passenger[$i]['City'] = $passenger['billing_city'];
			$tmp_passenger[$i]['PinCode'] = $passenger['billing_zipcode'];

			$tmp_passenger[$i]['AddressLine1'] = $passenger['billing_address_1'];
			$tmp_passenger[$i]['AddressLine2'] = $passenger['billing_address_1'];
			$tmp_passenger[$i]['Email'] = $passenger['billing_email'];


			//Baggage
			if (isset($passenger['baggage'][$i]) == true && valid_array($passenger['baggage'][$i]) == true) {
				$tmp_passenger[$i]['BaggageId'] = $passenger['baggage'][$i];
			}

			//Meals
			if (isset($passenger['meal'][$i]) == true && valid_array($passenger['meal'][$i]) == true) {
				$tmp_passenger[$i]['MealId'] = $passenger['meal'][$i];
			}

			//Seat
			if (isset($passenger['seat'][$i]) == true && valid_array($passenger['seat'][$i]) == true) {
				$tmp_passenger[$i]['SeatId'] = $passenger['seat'][$i];
			}
		}

		return $tmp_passenger;
	}

	private function hold_booking_request($booking_params, $app_reference)
	{
		$response = [];
		$response['status']	= SUCCESS_STATUS;
		$response['data']	= array();
		$request_params = array();
		//$this->credentials('HoldTicket'); Runs on API so commented for custom CRS
		$request_params['AppReference'] = trim($app_reference);
		$request_params['SequenceNumber'] = $booking_params['SequenceNumber'];
		$request_params['ResultToken'] = $booking_params['ProvabAuthKey'];
		$request_params['Passengers'] = $booking_params['Passenger'];
		$response['data']['request']		= json_encode($request_params);
		$response['data']['service_url']		= $this->service_url;
		return $response;
	}

	function run_commit_booking($op, $book_id, $token, $passenger, $search_access_key)
	{
		$booking_params = [];
		$response = [];
		$response['data'] = array();
		$response['status'] = FAILURE_STATUS;
		$response['message'] = '';
		$SequenceNumber = $token['SequenceNumber'];
		$booking_params['Passenger']			= $this->WSPassenger($passenger);
		// debug($booking_params['Passenger']); exit(); coming
		//Prova Auth key
		$booking_params['ProvabAuthKey']		= $token['ProvabAuthKey'];
		$booking_params['SequenceNumber']		= $SequenceNumber;
		$api_request = $this->commit_booking_request($booking_params, $book_id);
		// debug($api_request); exit;
		$api_request['status'] = true;

		//get data
		// if ($api_request['status']) {
		// 	//$header_info = $this->get_header();

		// 	$this->CI->custom_db->generate_static_response(json_encode($api_request['data']['request']));

		// 	$api_response = $this->CI->api_interface->get_json_response($api_request['data']['service_url'], $api_request['data']['request'], $header_info);
		// 	$this->CI->custom_db->generate_static_response(json_encode($api_response));

		// 	//$static_id = 	378;
		// 	//$api_response = $this->CI->flight_model->get_static_response($static_id);//378

		// 	if ($this->valid_commit_booking_response($api_response) == true) {
		// 		$api_response['CommitBooking']['BookingDetails']['Price'] = $this->convert_bookingdata_to_application_currency($api_response['CommitBooking']['BookingDetails']['Price']);
		// 		$response['data'] = $api_response;
		// 		$response['status'] = $api_response['Status'];
		// 	} else {
		// 		$response['message'] = @$api_response['Message'];
		// 		$response['status'] = FAILURE_STATUS;
		// 	}
		// }
		// /** PROVAB LOGGER **/
		// $GLOBALS['CI']->private_management_model->provab_xml_logger('Commit Booking', $book_id, 'flight', json_encode($api_request['data']), json_encode($api_response));
		$response['message'] = '';
		$response['status'] = SUCCESS_STATUS;
		// $response['st'] = 'BOOKING_HOLD';
		return $response;
	}

	private function commit_booking_request($booking_params, $app_reference)
	{
		$response = [];
		$response['status']	= SUCCESS_STATUS;
		$response['data']	= array();
		$request_params = array();
		//$this->credentials('CommitBooking');
		$request_params['AppReference'] = trim($app_reference);
		$request_params['SequenceNumber'] = $booking_params['SequenceNumber'];
		$request_params['ResultToken'] = $booking_params['ProvabAuthKey'];
		$request_params['Passengers'] = $booking_params['Passenger'];
		$response['data']['request']		= json_encode($request_params);

		//$response['data']['service_url']		= $this->service_url;

		return $response;
	}

	private function gender_type($pax_type)
	{
		switch (strtoupper($pax_type)) {
			case 'MALE':
				$pax_type = "1";
				break;
			case 'FEMALE':
				$pax_type = "2";
		}
		return $pax_type;
	}

	private function pax_type($pax_type)
	{
		switch (strtoupper($pax_type)) {
			case 'ADULT':
				$pax_type = "1";
				break;
			case 'CHILD':
				$pax_type = "2";
				break;
			case 'INFANT':
				$pax_type = "3";
				break;
		}
		return $pax_type;
	}
	function random_strings($length_of_string)
	{

		// String of all alphanumeric character 
		$str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

		// Shufle the $str_result and returns substring 
		// of specified length 
		return substr(
			str_shuffle($str_result),
			0,
			$length_of_string
		);
	}

	public function update_booking_details($book_id, $book_params, $ticket_details, $module = 'b2c')
	{
		$response = array();
		$book_total_fare = array();
		$book_domain_markup = array();
		$book_level_one_markup = array();

		$app_reference = $book_id;

		$master_search_id = $book_params['search_id'];

		$master_transaction_status = $this->status_code_value(SUCCESS_STATUS);

		$saved_booking_data = $GLOBALS['CI']->flight_model->get_booking_details($book_id);

		$passenger_details = $saved_booking_data['data']['booking_customer_details'];
		if ($saved_booking_data['status'] == false) {
			$response['status'] = BOOKING_ERROR;
			$response['msg'] = 'No Data Found';
			return $response;
		}

		//Extracting the Saved data
		$s_master_data = $saved_booking_data['data']['booking_details'][0];
		$s_booking_itinerary_details = $saved_booking_data['data']['booking_itinerary_details'];
		$s_booking_transaction_details = $saved_booking_data['data']['booking_transaction_details'];

		$s_booking_customer_details = $saved_booking_data['data']['booking_customer_details'];
		$first_name = $s_booking_customer_details[0]['first_name'];
		$phone = $s_master_data['phone'];
		$current_master_booking_status = $s_master_data['status'];
		//Extracting the Origins
		$transaction_origins = group_array_column($s_booking_transaction_details, 'origin');
		// debug($transaction_origins); exit;
		$passenger_origins = group_array_column($s_booking_customer_details, 'origin');
		$itinerary_origins = group_array_column($s_booking_itinerary_details, 'origin');
		#debug($saved_booking_data);
		#debug($itinerary_origins);
		//Indexing the data with origin
		$indexed_transaction_details = array();
		foreach ($s_booking_transaction_details as $s_tk => $s_tv) {
			$indexed_transaction_details[$s_tv['origin']] = $s_tv;
		}
		/*$itinerary_details = $indexed_transaction_details[$s_tv['origin']];
		$itinary_update_condition = array('origin'=>$itinerary_origins[0]);*/

		#debug($itinary_update_condition);

		//1.Update : flight_booking_details

		$agent_details = $GLOBALS['CI']->user_model->get_current_user_details();
		if ($agent_details[0]['reserve_booking'] == 0) {

			$flight_master_booking_status = BOOKING_HOLD;
		} else {

			$flight_master_booking_status = BOOKING_CONFIRMED;
		}


		if ($agent_details[0]['reserve_booking'] == 0) {

			$trans_status = BOOKING_HOLD;
		} else {

			$trans_status = BOOKING_CONFIRMED;
		}



		// changed status to confirmed
		// $flight_master_booking_status = 1;
		$GLOBALS['CI']->custom_db->update_record('flight_booking_details', array('status' => $flight_master_booking_status), array('app_reference' => $app_reference));

		$total_pax_count = count($book_params['passenger_type']);
		$pax_count = $total_pax_count;
		// debug($pax_count); exit;
		/////////////////////////
		//update pnr start Jagannath B

		$type_for_crs = count($book_params['token']['token']);
		// debug($type_for_crs); exit;
		$fsid_for_oneway = $book_params['token']['token'][0]['SegmentSummary'][0]['OriginDetails']['fsid'];
		if (isset($book_params['token']['token'][1]['SegmentSummary'][1]['OriginDetails']['fsid'])) {
			$fsid_for_roundway = $book_params['token']['token'][1]['SegmentSummary'][1]['OriginDetails']['fsid'];
		} elseif (isset($book_params['token']['token'][1]['SegmentSummary'][0]['OriginDetails']['fsid'])) {
			$fsid_for_roundway = $book_params['token']['token'][1]['SegmentSummary'][0]['OriginDetails']['fsid'];
		}
		// debug($fsid_for_roundway); exit;

		$c_is_domestic = $book_params['token']['is_domestic'];
		//exit;
		$status = $this->status_code_value(SUCCESS_STATUS);

		$fsid_pnr = array();
		if ($c_is_domestic != '' && $type_for_crs == 1) {
			//debug("hii"); exit;
			$c_details = $book_params['token']['token'][0]['SegmentSummary'];
			//$c_origin = current($c_details)['OriginDetails']['AirportCode'];
			//$c_destination = end($c_details)['DestinationDetails']['AirportCode'];
			$c_date = current($c_details)['OriginDetails']['_Date'];
			$c_date1 = date("Y-m-d", strtotime($c_date));
			//$c_acode = current($c_details)['AirlineDetails']['AirlineCode'];
			//$c_aname = current($c_details)['AirlineDetails']['AirlineName'];
			//$c_fnum = current($c_details)['AirlineDetails']['FlightNumber'];
			$c_fsid = $GLOBALS['CI']->flight_model->get_fsid_for_pnr_oneway($c_date1, $fsid_for_oneway);

			//$c_pnr = $c_fsid['pnr'];
			$c_pnr = $this->random_strings(6);
			$fudid = array($c_fsid['fudid']);

			$pnr1 = strtoupper($c_pnr);
			$pnr = array($pnr1);
			$pnr_latest = strtoupper($pnr1);
			$update_transaction_condition = array();
			$update_transaction_data = array();
			$update_transaction_condition['origin'] = $transaction_origins[0];


			if ($agent_details[0]['reserve_booking'] == 0) {
			} else {
				$update_transaction_data['pnr'] = strtoupper($pnr1);
			}

			$update_transaction_data['status'] = $trans_status;

			$fsid_pnr[$fsid_for_oneway] = strtoupper($pnr1);
			$GLOBALS['CI']->custom_db->update_record('flight_booking_transaction_details', $update_transaction_data, $update_transaction_condition);
			//debug($update_transaction_condition); exit;
			$update_itenerary_data = array('airline_pnr' => $pnr_latest);
			//$itinerary_origins
			for ($i = 0; $i < count($itinerary_origins); $i++) {
				$GLOBALS['CI']->custom_db->update_record('flight_booking_itinerary_details', $update_itenerary_data, array('origin' => $itinerary_origins[$i]));
			}
		}
		if ($c_is_domestic != '' && $type_for_crs == 2) {
			//Onward pnr
			$c_details = $book_params['token']['token'][0]['SegmentSummary'];
			$c_date = current($c_details)['OriginDetails']['_Date'];
			$c_date1 = date("Y-m-d", strtotime($c_date));
			$c_fsid = $GLOBALS['CI']->flight_model->get_fsid_for_pnr_twoway_onward($c_date1, $fsid_for_oneway);
			//$c_pnr = $c_fsid['pnr'];
			$c_pnr = $this->random_strings(6);
			$fudid1 = $c_fsid['fudid'];
			$pnr1 = strtoupper($c_pnr);
			$pnr_latest = strtoupper($pnr2);
			// debug($pnr1);
			$update_transaction_condition = array();
			$update_transaction_data = array();
			$update_transaction_condition['origin'] = $transaction_origins[0];
			if ($agent_details[0]['reserve_booking'] == 0) {
			} else {
				$update_transaction_data['pnr'] = strtoupper($pnr1);
			}
			$update_transaction_data['status'] = $trans_status;

			$fsid_pnr[$fsid_for_oneway] = strtoupper($pnr1);

			$GLOBALS['CI']->custom_db->update_record('flight_booking_transaction_details', $update_transaction_data, $update_transaction_condition);

			$GLOBALS['CI']->custom_db->update_record('flight_booking_transaction_details', $update_transaction_data, $update_transaction_condition);
			$update_itenerary_data = array('airline_pnr' => $pnr1);

			//Return pnr
			$c_details1 = $book_params['token']['token'][1]['SegmentSummary'];
			$c_date1 = current($c_details1)['OriginDetails']['_Date'];
			$c_date11 = date("Y-m-d", strtotime($c_date1));
			$c_fsid1 = $GLOBALS['CI']->flight_model->get_fsid_for_pnr_twoway_return($c_date11, $fsid_for_roundway);

			//$c_pnr1 = $c_fsid1['pnr'];
			$c_pnr1 = $this->random_strings(6);

			$fudid2 = $c_fsid1['fudid'];
			$pnr2 = strtoupper($c_pnr1);
			$array_pnr = array($pnr1, $pnr2);
			$fudid = array($fudid1, $fudid2);
			$pnr = array($pnr1, $pnr2);
			$pnr_latest = strtoupper($pnr2);
			// debug($pnr2); 
			$update_transaction_condition = array();
			$update_transaction_data = array();
			$update_transaction_condition['origin'] = $transaction_origins[1];
			if ($agent_details[0]['reserve_booking'] == 0) {
			} else {
				$update_transaction_data['pnr'] = strtoupper($pnr2);
			}
			$update_transaction_data['status'] = $trans_status;

			$fsid_pnr[$fsid_for_roundway] = strtoupper($pnr2);

			$GLOBALS['CI']->custom_db->update_record('flight_booking_transaction_details', $update_transaction_data, $update_transaction_condition);
				/*$update_itenerary_data = array('airline_pnr' => $pnr2);

				$update_itenerary_data = array('airline_pnr' => $pnr_latest)*/;

			//$itinerary_origins
			for ($i = 0; $i < count($itinerary_origins); $i++) {
				$GLOBALS['CI']->custom_db->update_record('flight_booking_itinerary_details', array('airline_pnr' => $array_pnr[$i]), array('origin' => $itinerary_origins[$i]));
			}



			/*$GLOBALS['CI']->custom_db->update_record('flight_booking_itinerary_details',$update_itenerary_data,$itinary_update_condition);*/
		} else {

			$c_details = $book_params['token']['token'][0]['SegmentSummary'];
			$c_date = current($c_details)['OriginDetails']['_Date'];
			$c_date1 = date("Y-m-d", strtotime($c_date));
			$c_fsid = $GLOBALS['CI']->flight_model->get_fsid_for_pnr_int($c_date1, $fsid_for_oneway);
			//$c_pnr = $c_fsid['pnr'];
			$c_pnr = $this->random_strings(6);
			$fudid = array($c_fsid['fudid']);
			$pnr1 = strtoupper($c_pnr);
			$pnr = array($pnr1);
			$pnr_latest = strtoupper($pnr1);
			$update_transaction_condition = array();
			$update_transaction_data = array();
			$update_transaction_condition['origin'] = $transaction_origins[0];
			if ($agent_details[0]['reserve_booking'] == 0) {
			} else {
				$update_transaction_data['pnr'] = strtoupper($pnr1);
			}
			$update_transaction_data['status'] = $trans_status;

			$fsid_pnr[$fsid_for_oneway] = strtoupper($pnr1);

			$GLOBALS['CI']->custom_db->update_record('flight_booking_transaction_details', $update_transaction_data, $update_transaction_condition);

			$update_itenerary_data = array('airline_pnr' => $pnr1);

			for ($i = 0; $i < count($itinerary_origins); $i++) {
				$GLOBALS['CI']->custom_db->update_record('flight_booking_itinerary_details', $update_itenerary_data, array('origin' => $itinerary_origins[$i]));
			}

			//$GLOBALS['CI']->custom_db->update_record('flight_booking_itinerary_details',$update_itenerary_data,$itinary_update_condition);
			// $pnr = '';
		}

		///////////////////////////
		////////////////////////////////////////////////////////////
		//Seat deduct Jagannath B
		// debug($book_params); exit;
		$is_domestic_crs = $book_params['token']['is_domestic'];
		$crs_count_ways = count($book_params['token']['token']);

		if ($is_domestic_crs == '') {
			//$airline_code=$saved_booking_data['data']['booking_itinerary_details'][0]['airline_code'];
			//$airline_name=$saved_booking_data['data']['booking_itinerary_details'][0]['airline_name'];
			//$flight_number=$saved_booking_data['data']['booking_itinerary_details'][0]['flight_number'];
			//$from_airport_code=$saved_booking_data['data']['booking_itinerary_details'][0]['from_airport_code'];
			//$to_airport_code=$saved_booking_data['data']['booking_itinerary_details'][0]['to_airport_code'];
			// debug($saved_booking_data); exit;
			$departure_datetime = $saved_booking_data['data']['booking_details'][0]['journey_start'];
			// debug($departure_datetime); exit;
			//$dat = Date('y-m-d',$departure_datetime);
			$dt = new DateTime($departure_datetime);
			$dat = $dt->format('Y-m-d');

			$departure_datetime1 = $saved_booking_data['data']['booking_details'][0]['journey_end'];
			// debug($departure_datetime); exit;
			//$dat = Date('y-m-d',$departure_datetime);
			$dt1 = new DateTime($departure_datetime1);
			$dat1 = $dt1->format('Y-m-d');

			// debug($book_params); exit;
			$fsid = $book_params['token']['token'][0]['SegmentSummary'][0]['OriginDetails']['fsid'];

			// debug($fsid); exit;

			$seat_status_query = $GLOBALS['CI']->flight_model->update_crs_seat1($dat, $pax_count, $fsid, $dat1);
			$fsid = array($fsid);
		}
		if ($is_domestic_crs == 1 && $crs_count_ways == 2) {

			$onward_datetime = $book_params['token']['token'][0]['SegmentSummary'][0]['OriginDetails']['DateTime'];
			$dt = new DateTime($onward_datetime);
			$dat = $dt->format('Y-m-d');
			$fsid = $book_params['token']['token'][0]['SegmentSummary'][0]['OriginDetails']['fsid'];
			$seat_status_query = $GLOBALS['CI']->flight_model->update_crs_seat_do($dat, $pax_count, $fsid);

			$retu_datetime = $book_params['token']['token'][1]['SegmentSummary'][1]['OriginDetails']['DateTime'];
			$dt1 = new DateTime($retu_datetime);
			$dat1 = $dt1->format('Y-m-d');
			if (isset($book_params['token']['token'][1]['SegmentSummary'][1]['OriginDetails']['fsid'])) {
				$fsid1 = $book_params['token']['token'][1]['SegmentSummary'][1]['OriginDetails']['fsid'];
			} elseif (isset($book_params['token']['token'][1]['SegmentSummary'][0]['OriginDetails']['fsid'])) {
				$fsid1 = $book_params['token']['token'][1]['SegmentSummary'][0]['OriginDetails']['fsid'];
			}

			$seat_status_query = $GLOBALS['CI']->flight_model->update_crs_seat_dr($dat1, $pax_count, $fsid1);
			$fsid = array($fsid, $fsid1);
		}
		if ($is_domestic_crs == 1 && $crs_count_ways == 1) {
			$onward_datetime = $book_params['token']['token'][0]['SegmentSummary'][0]['OriginDetails']['DateTime'];
			$dt = new DateTime($onward_datetime);
			$dat = $dt->format('Y-m-d');
			$fsid = $book_params['token']['token'][0]['SegmentSummary'][0]['OriginDetails']['fsid'];

			$seat_status_query = $GLOBALS['CI']->flight_model->update_crs_seat_done($dat, $pax_count, $fsid);
			$fsid = array($fsid);
		}

		if (isset($GLOBALS['CI']->entity_user_id) == true and intval($GLOBALS['CI']->entity_user_id) > 0) {
			$agent_id = $GLOBALS['CI']->entity_user_id;
		} else {
			$cus_email = $saved_booking_data['data']['booking_details'][0]['email'];
			$user = $GLOBALS['CI']->flight_model->get_user($cus_email);
			$agent_id = $user[0]['user_id'];
		}

		$itineary_details = $saved_booking_data['data']['booking_itinerary_details'];
		$transcation_details = $saved_booking_data['data']['booking_transaction_details'];



		$crs_array = array();
		for ($i = 0; $i < count($fsid); $i++) {
			if ($agent_details[0]['reserve_booking'] == 0) {
			} else {
				$crs_pnr = $fsid_pnr[$fsid[$i]];
			}
			$crs_array = array(
				'fsid' => $fsid[$i],
				'fudid' => $fudid[$i],
				'app_reference' => $saved_booking_data['data']['booking_details'][0]['app_reference'],
				'booking_source' => $saved_booking_data['data']['booking_details'][0]['booking_source'],
				'pnr' => $crs_pnr,
				'status' => $status,
				'agent_id' => $agent_id,
				'created_date_time' => date('Y-m-d H:i:s')
			);
			$GLOBALS['CI']->flight_model->save_flight_crs_booking_details($crs_array);
		}


		for ($k = 0; $k < count($itineary_details); $k++) {

			$price_details = json_decode($transcation_details[$k]['attributes'], true);
			$per_passenger_fare = $price_details['Fare']['BaseFare'] / count($passenger_details);
			$per_passenger_tax = $price_details['Fare']['Tax'] / count($passenger_details);
			$passenger_price = array('price_breakup' => array('pax_per_price' => $per_passenger_fare, 'pax_per_tax' => $per_passenger_tax, 'base_price' => $price_details['Fare']['BaseFare'], 'tax' => $price_details['Fare']['Tax'], 'total_price' => $price_details['Fare']['PublishedFare']));

			$passenger_price = json_encode($passenger_price);
			$update_passenger_data = array();

			$trans_id = $transcation_details[$k]['origin'];

			foreach ($passenger_details as $pax_k => $pax_v) {

				if ($trans_id == $pax_v['flight_booking_transaction_details_fk']) {

					$itinary_update_condition = array('passenger_fk' => $pax_v['origin']);
					$ticket_number = rand(100, 999) . rand(1000, 9999) . rand(100, 999) . rand(100, 999);;
					// $update_passenger_data['TicketId'] = $pnr[$k];
					// $update_passenger_data['TicketNumber'] = $pnr[$k];
					$update_passenger_data['TicketId'] = $ticket_number;
					if ($agent_details[0]['reserve_booking'] == 0) {
					} else {
						$update_passenger_data['TicketNumber'] = $ticket_number;
					}
					$GLOBALS['CI']->custom_db->update_record('flight_passenger_ticket_info', $update_passenger_data, $itinary_update_condition);
					if ($agent_details[0]['reserve_booking'] == 0) {
						$status_pass = 'BOOKING_HOLD';
					} else {
						$status_pass = 'BOOKING_CONFIRMED';
					}
					$GLOBALS['CI']->custom_db->update_record('flight_booking_passenger_details', array('status' => $status_pass, 'attributes' => $passenger_price), array('origin' => $pax_v['origin']));
				}
			}
		}











		//seat update ends
		////////////////////////////////////////////////////////////////////
		//********************** only for calculation
		$safe_search_data = $this->search_data($master_search_id);
		$safe_search_data = $safe_search_data['data'];
		$from_loc = $safe_search_data['from'];
		$to_loc = $safe_search_data['to'];
		$safe_search_data['is_domestic_one_way_flight'] = false;
		$from_to_trip_type = $safe_search_data['trip_type'];

		$safe_search_data['is_domestic_one_way_flight'] = $GLOBALS['CI']->flight_model->is_domestic_flight($from_loc, $to_loc);
		if ($safe_search_data['is_domestic_one_way_flight'] == false && strtolower($from_to_trip_type) == 'circle') {
			$multiplier = $pax_count * 2; //Multiply with 2 for international round way
		} else if (strtolower($from_to_trip_type) == 'multicity') {
			$multiplier = $pax_count * count($safe_search_data['from']);
		} else {
			$multiplier = $pax_count;
		}
		//********************* only for calculation
		$currency_obj		= $book_params['currency_obj'];
		$currency = $currency_obj->to_currency;
		$deduction_cur_obj	= clone $currency_obj;
		//PREFERRED TRANSACTION CURRENCY AND CURRENCY CONVERSION RATE 
		$transaction_currency = get_application_currency_preference();
		$application_currency = admin_base_currency();
		$currency_conversion_rate = $currency_obj->transaction_currency_conversion_rate();

		if (valid_array($ticket_details) == true) {
			//Ticket Loop Starts
			foreach ($ticket_details as $ticket_index => $ticket_value) {
				$transaction_details_origin = intval($transaction_origins[$ticket_index]);

				if (($ticket_value['Status']) == true) { //IF Ticket is HOLD/CONFIRMED
					$status = $this->status_code_value(SUCCESS_STATUS);
					//$status = $this->status_code_value($ticket_value['Status']);
					$ticket_value = $ticket_value['CommitBooking']['BookingDetails'];

					$api_booking_id = $ticket_value['BookingId'];
					$pnr = $ticket_value['PNR'];
					$Fare = $ticket_value['Price']['FareDetails'];
					$PassengerFareBreakdown = $ticket_value['Price']['PassengerFareBreakdown'];
					$segment_details = $ticket_value['JourneyList']['FlightDetails']['Details'];
					$passenger_details = $ticket_value['PassengerDetails'];

					$tmp_domain_markup = 0;
					$tmp_level_one_markup = 0;
					$itinerary_price	= $Fare['BaseFare'];
					//Calculation is different for b2b and b2c
					//Specific Markup Config
					$specific_markup_config = array();
					$specific_markup_config = $this->get_airline_specific_markup_config($segment_details); //Get the Airline code for setting airline-wise markup

					$final_booking_price_details = $this->get_final_booking_price_details($Fare, $multiplier, $specific_markup_config, $currency_obj, $deduction_cur_obj, $module);
					$commissionable_fare = $final_booking_price_details['commissionable_fare'];
					$trans_total_fare = $final_booking_price_details['trans_total_fare'];
					$admin_markup = $final_booking_price_details['admin_markup'];
					$agent_markup = $final_booking_price_details['agent_markup'];
					$admin_commission = $final_booking_price_details['admin_commission'];
					$agent_commission = $final_booking_price_details['agent_commission'];
					$admin_tds = $final_booking_price_details['admin_tds'];
					$agent_tds = $final_booking_price_details['agent_tds'];


					//2.Update : flight_booking_transaction_details
					// debug("2926"); exit;
					$update_transaction_condition = array();
					$update_transaction_data = array();
					$update_transaction_condition['origin'] = $transaction_details_origin;
					if ($agent_details[0]['reserve_booking'] == 0) {
					} else {
						$update_transaction_data['pnr'] = $pnr;
					}
					$update_transaction_data['book_id'] = $api_booking_id;
					$update_transaction_data['status'] = $trans_status;
					$update_transaction_data['total_fare'] = $commissionable_fare;
					$update_transaction_data['admin_commission'] = $admin_commission;
					$update_transaction_data['agent_commission'] = $agent_commission;
					$update_transaction_data['admin_tds'] = $admin_tds;
					$update_transaction_data['agent_tds'] = $agent_tds;
					$update_transaction_data['admin_markup'] = $admin_markup;
					$update_transaction_data['agent_markup'] = $agent_markup;
					//For Transaction Log
					$book_total_fare[]	= $trans_total_fare;
					$book_domain_markup[]	= $admin_markup;
					$book_level_one_markup[] = $agent_markup;

					$GLOBALS['CI']->custom_db->update_record('flight_booking_transaction_details', $update_transaction_data, $update_transaction_condition);

					//3.Update: flight_booking_passenger_details
					$update_passenger_condition = array();
					$update_passenger_data = array();
					$update_passenger_condition['flight_booking_transaction_details_fk'] = $transaction_details_origin;
					if ($agent_details[0]['reserve_booking'] == 0) {
						$update_passenger_data['status'] = 'BOOKING_HOLD';
					} else {
						$update_passenger_data['status'] = $master_transaction_status;
					}


					$GLOBALS['CI']->custom_db->update_record('flight_booking_passenger_details', $update_passenger_data, $update_passenger_condition);

					//4.Update Ticket details to flight_passenger_ticket_info
					$single_pax_fare_breakup = $this->get_single_pax_fare_breakup($PassengerFareBreakdown);

					foreach ($passenger_details as $pax_k => $pax_v) {
						debug($pax_v);
						exit;
						$passenger_fk = intval(array_shift($passenger_origins));
						/*$TicketId = $pax_v['PassengerId'];
						$TicketNumber = $pax_v['TicketNumber'];*/
						$TicketId = $pnr_latest;
						if ($agent_details[0]['reserve_booking'] == 0) {
						} else {
							$TicketNumber = $pnr_latest;
						}
						$IssueDate = '';
						$Fare = json_encode($single_pax_fare_breakup[$pax_v['PassengerType']]);
						$SegmentAdditionalInfo = '';
						$ValidatingAirline = '';
						$CorporateCode = '';
						$TourCode = '';
						$Endorsement = '';
						$Remarks = '';
						$ServiceFeeDisplayType = '';
						//SAVE PAX Ticket Details
						$GLOBALS['CI']->flight_model->update_passenger_ticket_info(
							$passenger_fk,
							$TicketId,
							$TicketNumber,
							$IssueDate,
							$Fare,
							$SegmentAdditionalInfo,
							$ValidatingAirline,
							$CorporateCode,
							$TourCode,
							$Endorsement,
							$Remarks,
							$ServiceFeeDisplayType
						);
					}
					//5. Update :flight_booking_itinerary_details
					foreach ($segment_details as $seg_k => $seg_v) {
						foreach ($seg_v as $ws_key => $ws_val) {
							$update_segment_condition = array();
							$update_segement_data = array();
							$update_segment_condition['origin'] = intval(array_shift($itinerary_origins));
							$update_segement_data['airline_pnr'] = $pnr_latest;
							$attributes = array();
							$attributes['departure_terminal'] = $ws_val['Origin']['Terminal'];
							$attributes['arrival_terminal'] = $ws_val['Destination']['Terminal'];
							$attributes['CabinClass'] = $ws_val['CabinClass'];
							$attributes['Attr'] = $ws_val['Attr'];

							$update_segement_data['attributes'] = json_encode($attributes);
							$update_segement_data['status'] = '';

							$update_segement_data['FareRestriction'] = '';
							$update_segement_data['FareBasisCode'] = '';
							$update_segement_data['FareRuleDetail'] = '';

							$GLOBALS['CI']->custom_db->update_record('flight_booking_itinerary_details', $update_segement_data, $update_segment_condition);
						}
					}
				} else { //IF Ticket is Failed
					$GLOBALS['CI']->flight_model->update_flight_booking_transaction_failure_status($transaction_details_origin);
					//For Transaction Log
					$book_total_fare[]	= $indexed_transaction_details[$transaction_details_origin]['total_fare'];
					$book_domain_markup[]	= $indexed_transaction_details[$transaction_details_origin]['admin_markup'];
					$book_level_one_markup[] = $indexed_transaction_details[$transaction_details_origin]['agent_markup'];
				}
			} //Ticket Loop Ends
		} else {
			foreach ($indexed_transaction_details as $itd_k => $itd_v) {
				$transaction_details_origin = $itd_v['origin'];
				$GLOBALS['CI']->flight_model->update_flight_booking_transaction_failure_status_custom($transaction_details_origin);

				$book_total_fare[]	= $itd_v['total_fare'];
				$book_domain_markup[]	= $itd_v['admin_markup'];
				$book_level_one_markup[] = $itd_v['agent_markup'];
			}
		}


		/**
		 * Data to be returned after transaction is saved completely
		 */
		$transaction_description = '';
		$book_total_fare = array_sum($book_total_fare);
		$book_domain_markup = array_sum($book_domain_markup);
		$book_level_one_markup = array_sum($book_level_one_markup);
		$discount = 0;


		//Adding Extra services Total Price
		// $extra_services_total_price = $GLOBALS['CI']->flight_model->get_extra_services_total_price($app_reference);
		// $book_total_fare += $extra_services_total_price;

		if ($module == 'b2c') {
			$total_transaction_amount = $book_total_fare + $book_domain_markup;
			$convinence = $currency_obj->convenience_fees($total_transaction_amount, $master_search_id);
		} else {
			$convinence = 0;
		}
		$response['fare'] = $book_total_fare;
		$response['admin_markup'] = $book_domain_markup;
		$response['agent_markup'] = $book_level_one_markup;
		$response['convinence'] = $convinence;
		$response['discount'] = $discount;

		$response['status'] = $flight_master_booking_status;
		$response['status_description'] = $transaction_description;
		$response['name'] = $first_name;
		$response['phone'] = $phone;
		$response['transaction_currency'] = $transaction_currency;
		$response['currency_conversion_rate'] = $currency_conversion_rate;
		//	 debug($response);exit();
		return $response;
	}

	private function status_code_value($status_code)
	{
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
		}
		return $status_value;
	}

	private function get_single_pax_fare_breakup($passenger_fare_breakdown)
	{
		$single_pax_fare_breakup = array();
		foreach ($passenger_fare_breakdown as $k => $v) {
			$PassengerCount = $v['PassengerCount'];
			$single_pax_fare_breakup[$k]['BaseFare'] = 	($v['BaseFare'] / $PassengerCount);
			$single_pax_fare_breakup[$k]['Tax'] = 			($v['Tax'] / $PassengerCount);
			$single_pax_fare_breakup[$k]['TotalPrice'] =	($v['TotalPrice'] / $PassengerCount);
		}
		return $single_pax_fare_breakup;
	}

	function get_form_content($form_1, $form_2)
	{
		// error_reporting();
		// debug($form_1); exit;
		$booking_form = '';
		$lcc = (($form_1['is_lcc[]'] == true || $form_2['is_lcc[]'] == true) ? true : false);
		//booking_type - decide it based on f1 is_lcc and f2 is_lcc
		$booking_type = $this->get_booking_type($lcc);
		$booking_form .= $this->booking_form(true, $form_1['token[]'], $form_1['token_key[]'], $form_1['search_access_key[]']);
		$booking_form .= $this->booking_form(true, $form_2['token[]'], $form_2['token_key[]'], $form_2['search_access_key[]']);
		return $booking_form;
	}

	// function get_form_content2($form_1)
	// {
	// 	$booking_form = '';
	// 	$lcc = (($form_1['is_lcc[]'] == true || $form_2['is_lcc[]'] == true) ? true : false);
	// 	//booking_type - decide it based on f1 is_lcc and f2 is_lcc
	// 	$booking_type = $this->get_booking_type($lcc);
	// 	$booking_form .= $this->booking_form(true, $form_1['token[]'], $form_1['token_key[]'], $form_1['search_access_key[]']);
	// 	//$booking_form .= $this->booking_form(true, $form_2['token[]'], $form_2['token_key[]'], $form_2['search_access_key[]']);
	// 	return $booking_form;
	// }

	function get_booking_type($is_lcc)
	{
		if ($is_lcc) {
			return LCC_BOOKING;
		} else {
			return NON_LCC_BOOKING;
		}
	}

	function calendar_safe_search_data()
	{
		$response = [];
		$response['data']['day_fare_list'] = array();
		$response['data']['status'] = 1;

		return $response;
	}

	public function FlightSeatLopa($flight_details_id, $departure_date)
	{
		$data = [];
		$lopa_data = [];
		//Reading Redis Cache to take Flight Data
		$flight_data = $this->CI->custom_db->single_table_records('crs_update_flight_details', 'fsid,avail_date', array('origin' => $flight_details_id));
		if (isset($flight_details_id) && isset($flight_data['data'][0]['fsid'])) {
			$FlightID = $flight_data['data'][0]['fsid'];
			$DepartureDate = $flight_data['data'][0]['avail_date'];

			$flight_seat_prices = $this->CI->flights_model->get_seat_prices($flight_details_id);

			$flight_avail_blocked_seats = $this->CI->flights_model->flight_avail_blocked_seats($FlightID);

			//fetching flight booked seats based on RBD Class

			$booked_seats = $this->CI->flights_model->get_flight_booked_seats($FlightID, $flight_details_id, $DepartureDate);
			//rearranging array from the database result
			$booked_seats_array = array();

			if (count($booked_seats) > 0 && isset($booked_seats[0]['BookedSeats'])) {
				$seats = $booked_seats[0]['BookedSeats'];
				$booked_seats_array = explode(",", $seats);
			}
			$SeatPriceMatrix = array();

			foreach ($flight_seat_prices as $fsp_k => $fsp_v) {
				$seat_start_column = substr($fsp_v['FromRangeSeat'], -1);
				$seat_end_column = substr($fsp_v['ToRangeSeat'], -1);
				$seat_start_row = substr($fsp_v['FromRangeSeat'], 0, strlen($fsp_v['FromRangeSeat']) - 1);
				$seat_end_row = substr($fsp_v['ToRangeSeat'], 0, strlen($fsp_v['ToRangeSeat']) - 1);
				$PriceSeat = $fsp_v['PriceSeat'];
				$ColumnCount = $fsp_v['ColumnCount'];


				$PriceColRange = substr($ColumnCount, strpos($ColumnCount, $seat_start_column), strpos($ColumnCount, $seat_end_column) + 1);

				for ($m = 1; $m <= strlen($PriceColRange); $m++) {
					for ($n = $seat_start_row; $n <= $seat_end_row; $n++) {
						$SeatPriceMatrix[$n . $ColumnCount[$m - 1]] = array('FLAG' => 'AVA', 'PRICE' => $PriceSeat);
					}
				}
			}
			ksort($SeatPriceMatrix);
			if (count($flight_avail_blocked_seats) > 0) {
				$rbd_seat_matrix = array();
				$RBDPrices = array();
				foreach ($flight_avail_blocked_seats as $key => $value) {
					$BlockedSeats = explode(",", $flight_avail_blocked_seats[$key]['BlockedSeats']);
					$AvailableSeats = explode(",", $flight_avail_blocked_seats[$key]['AvailableSeats']);
					$EmergencySeats = explode(",", $flight_avail_blocked_seats[$key]['EmergencySeats']);
					$PermanentBlockedSeats = explode(",", $flight_avail_blocked_seats[$key]['PermanentBlockedSeats']);
					$Space = $flight_avail_blocked_seats[$key]['Space'];

					$RowCount = $flight_avail_blocked_seats[$key]['RowCount'];
					$Coulmns = $flight_avail_blocked_seats[$key]['Coulmns'];

					$count_column = strlen($Coulmns);

					$start_column = substr($Coulmns, 0, 1);
					$end_column = substr($Coulmns, -1);

					$rbd_col_range = substr($Coulmns, strpos($Coulmns, $start_column), strpos($Coulmns, $end_column) + 1);
					$seat_price = 0;
					for ($i = 1; $i <= strlen($rbd_col_range); $i++) {
						for ($k = 1; $k <= $RowCount; $k++) {
							$status = "";
							$extra_status = "";

							if (array_search(($k . $Coulmns[$i - 1]), $BlockedSeats) !== FALSE) {
								$status = "BLK";
							} elseif (array_search(($k . $Coulmns[$i - 1]), $PermanentBlockedSeats) !== FALSE) {
								$status = "PBLK";
							} elseif (array_search(($k . $Coulmns[$i - 1]), $booked_seats_array) !== FALSE) {
								$status = "BKD";
							} else {
								$status = "AVA";
							}
							if (array_search(($k . $Coulmns[$i - 1]), $EmergencySeats) !== FALSE) {
								$extra_status = "EMG";
							}
							$seat_price = 0;
							if (isset($SeatPriceMatrix[$k . $Coulmns[$i - 1]])) {

								$seat_price = $SeatPriceMatrix[$k . $Coulmns[$i - 1]]['PRICE'];
							}
							$rbd_seat_matrix[$k . $Coulmns[$i - 1]] = array('FLAG' => $status, 'SeatPrice' => $seat_price, 'EmgFlag' => $extra_status);
						}
					}
					
				}				
				$flight_lopa = array('FlightID' => $FlightID, 'FlightDetailsId' => $flight_details_id, 'Coulmns' => $Coulmns, 'Rows' => $RowCount, 'Space' => $Space, 'Seats' => $rbd_seat_matrix);
				$data['status']     =   SUCCESS_STATUS;
				$data['message']    =   "Displaying Data";
				$data['data']       =   $flight_lopa;
			} else {
				$data['status']     =   FAILURE_STATUS;
				$data['message']    =   "Seats Not Avaiable";
				$data['data']       =   "";
			}
		} else {
			$data['message'] = "Expired Result Token";
		}

		if ($data['status'] == SUCCESS_STATUS && valid_array($data['data'])) {
			$lopa_data['status'] =  $data['status'];
			$lopa_data['message'] = $data['message'];
			// $lopa_data['data']['LopaToken']=$access_data['access_key'];
			$lopa_data['FlightSeatLopa']['Coulmns']   =   $data['data']['Coulmns'];
			$lopa_data['FlightSeatLopa']['Rows']      =   $data['data']['Rows'];
			$lopa_data['FlightSeatLopa']['Space']     =   $data['data']['Space'];
			$lopa_data['FlightSeatLopa']['Seats']     =   $data['data']['Seats'];
		}
		return $lopa_data;
	}

	public function preferred_currency_seat_price_object($seats, $currency_obj, $default_currency = '')
	{
		$Seats = array();
		foreach ($seats as $key => $value) {
			$FLAG 		= 			$value['FLAG'];
			$SeatPrice 	= 			$value['SeatPrice'];
			$EmgFlag 	= 			$value['EmgFlag'];
			$Seats[$key]['FLAG'] = $FLAG;
			$Seats[$key]['SeatPrice'] = get_converted_currency_value($currency_obj->force_currency_conversion($SeatPrice));
			$Seats[$key]['EmgFlag'] = $EmgFlag;
		}
		return $Seats;
	}
}
