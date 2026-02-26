<?php
/**
 * Provab Common Functionality For Carnect
 *
 *
 * @package Provab
 * @subpackage provab
 * @category Libraries
 * @author Badri Nath Nayak
 * @link http://www.provab.com
 */
require_once BASEPATH . 'libraries/car/Common_api_car.php';
ob_start();
class Carnect extends Common_Api_Car {

    var $master_search_data;
    var $search_hash;
    protected $token;
    private $end_user_ip = '127.0.0.1';
    var $api_session_id;
    var $api_cancellation_policy_day;

    function __construct() {

        parent::__construct(META_CAR_COURSE, CARNECT_CAR_BOOKING_SOURCE);
        $this->CI = &get_instance();
        $this->CI->load->library('Converter');
        $this->set_api_credentials();
        $this->get_cancellation_policy_day();
        //$this->set_api_session_id();
    }

      private function set_api_credentials() {
        // $this->config['login_id'] = 'Test_CarnectTech';
        // $this->config['password']  = '4f$ES462Ks';
    
        $this->service_url = $this->config['api_url'];
        $this->username = $this->config['login_id'];
        $this->password = $this->config['password'];
    }
    function get_cancellation_policy_day() {
        $cancellation_day = $this->CI->custom_db->single_table_records('set_car_cancellation', '*', array('api' => $this->booking_source));
        if ($cancellation_day['status'] == 1) {
            $this->api_cancellation_policy_day = trim($cancellation_day['data'][0]['day']);
        } else {
            $this->api_cancellation_policy_day = 1;
        }
    }
  
    /**
     * (non-PHPdoc)
     * @see Common_Api_Grind::search_data()
     */
    public function search_data($search_id) {
        $response ['status'] = true;
        $response ['data'] = array();
        $CI = & get_instance();

        if (empty($this->master_search_data) == true and valid_array($this->master_search_data) == false) {

            $clean_search_details = $CI->car_model->get_safe_search_data($search_id);

            if ($clean_search_details ['status'] == true) {
                $response ['status'] = true;
                $response ['data'] = $clean_search_details ['data'];

                $this->master_search_data = $response ['data'];
            } else {
                $response ['status'] = false;
            }
        } else {
            $response ['data'] = $this->master_search_data;
        }
        $this->search_hash = md5(serialized_data($response ['data']));
        return $response;
    }

    /**
     * Formates Search Request
     */
    private function search_request($search_data) {
        // debug($search_data);exit;
        $search_request = array();
        
        $request = '';
        $pickup_location = '';
        $pickup_loc_id = '';
        $return_location = '';
        $return_loc_id = '';
        $pickup_datetime = '';
        $return_datetime = '';
        $driver_age = '';
        $country_code = '';

        if(isset($search_data) && !empty($search_data)){
            $pickup_loc_id = @$search_data['pickup_loc_id'];
            $return_loc_id = @$search_data['return_loc_id'];
            $pickup_datetime = @$search_data['pickup_datetime'];
            $pickup_datetime = date('Y-m-d\TH:i:s', strtotime($pickup_datetime));
            $return_datetime = @$search_data['return_datetime'];
            $return_datetime = date('Y-m-d\TH:i:s', strtotime($return_datetime));
            
            $pickup_location = @$search_data['pickup_location'];
            $return_location = @$search_data['return_location'];

            $driver_age = @$search_data['driver_age'];
            
            if (strpos($pickup_location, 'City/Downtown') !== false) {
                $CodeContext_FROM = 1;
            }else{
                $CodeContext_FROM = 2;
            }   
            if (strpos($return_location, 'City/Downtown') !== false) {
                $CodeContext_TO = 1;
            }else{
                $CodeContext_TO = 2;
            }
            
            $country_code = @$search_data['country_code'];
            $RateQueryParameterType = (int)4;
        }
        // $return_loc_id = 'DXB';
        $return = '';
        if(empty($return_loc_id) == false){
            $return = '<ReturnLocation LocationCode="'.$return_loc_id.'" CodeContext="'.$CodeContext_TO.'" />';
        }
        $country_code = $search_data['country'];
        $request = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"> 
        <soap:Body>
        <VehAvailRateRQ xmlns="http://www.opentravel.org/OTA/2003/05"> <POS>
            <Source ISOCountry="'.$country_code.'">
                <RequestorID ID_Context="'.$this->password.'" Type="'.$this->username.'" /> </Source>
                </POS>
                <VehAvailRQCore RateQueryType="Live">
                    <RateQueryParameterType>'.$RateQueryParameterType.'</RateQueryParameterType>
                    <VehRentalCore PickUpDateTime="'.$pickup_datetime.'" ReturnDateTime="'.$return_datetime.'">
                        <PickUpLocation LocationCode="'.$pickup_loc_id.'" CodeContext="'.$CodeContext_FROM.'" />
                        '.$return.'
                    </VehRentalCore>
                    <DriverType Age="'.$driver_age.'"/>
                </VehAvailRQCore>
            </VehAvailRateRQ> 
        </soap:Body>
    </soap:Envelope>';
          
        // debug( $request);exit;
        $search_request ['request'] = $request;
        $search_request ['url'] = $this->config['api_url'];
        $search_request ['status'] = SUCCESS_STATUS;

        return $search_request;
    }

    /**
     * Search Request
     * @param unknown_type $search_id
     */
    public function get_search_request($search_id) {

        $response ['status'] = FAILURE_STATUS; // Status Of Operation
        $response ['message'] = ''; // Message to be returned
        $response ['data'] = array(); // Data to be returned       
        /* get search criteria based on search id */
        $search_data = $this->search_data($search_id);
    
        if ($search_data ['status'] == SUCCESS_STATUS) {
            // Car search RQ
            $search_request = $this->search_request($search_data ['data']);
          // debug( $search_request); exit;
            if ($search_request ['status'] = SUCCESS_STATUS) {
                $response ['status'] = SUCCESS_STATUS;

                $curl_request = $this->form_curl_params($search_request ['request'],$search_request ['url']);
                //debug($curl_request); exit('carnect lib get_search_request() set header and proceed');
                $response ['data'] = $curl_request['data'];
            }
        }
        
        return $response;
    }

    /**
     * format car response
     *
     * @param string $response
     */
    function get_car_list($car_raw_data, $search_id){
        $response ['status'] = FAILURE_STATUS; // Status Of Operation
        $response ['message'] = ''; // Message to be returned
        $response ['data'] = array(); // Data to be returned
        $search_data = $this->search_data($search_id);
        if ($search_data ['status'] == SUCCESS_STATUS) {
            $api_response = $car_raw_data;
            $api_response = Converter::createArray($api_response);
            // debug($api_response);exit;
            if ($this->valid_search_result($api_response) == TRUE) {
                $clean_format_data = $this->format_search_data_response($api_response['soap:Envelope']['soap:Body']['VehAvailRateRS'], $search_data ['data']);
                // debug($clean_format_data);exit;
                if ($clean_format_data) {
                    $response ['status'] = SUCCESS_STATUS;
                } else {
                    $response ['status'] = FAILURE_STATUS;
                }
            }
            else {
                $response ['status'] = FAILURE_STATUS;
            }
            if ($response ['status'] == SUCCESS_STATUS) {
                $response ['data'] = $clean_format_data;
            }
        }
        else {
            $response ['status'] = FAILURE_STATUS;
        }
        // debug($response);exit;
        return $response;
    }
     /**
     * Formates Search Response
     * Enter description here ...
     * @param unknown_type $search_result
     * @param unknown_type $search_data
     */
    function format_search_data_response($search_result, $search_data) {
       // debug($search_result);exit;
        $response = array();
        if(isset($search_result['VehAvailRSCore']) && valid_array($search_result['VehAvailRSCore'])){
            $car_response = $search_result['VehAvailRSCore'];
           
            $car_response_avail = @$car_response['VehVendorAvails']['VehVendorAvail']['VehAvails']['VehAvail'];     
            if(isset($car_response_avail) && valid_array($car_response_avail)){
                foreach($car_response_avail as $key => $car_response_avail_v){
                    if(isset($car_response['VehRentalCore']) && !empty($car_response['VehRentalCore'])){
                        $response[$key]['PickUpDateTime'] = @$car_response['VehRentalCore']['@attributes']['PickUpDateTime'];
                        $response[$key]['ReturnDateTime'] = @$car_response['VehRentalCore']['@attributes']['ReturnDateTime']; 
                        $response[$key]['Quantity'] = @$car_response['VehRentalCore']['@attributes']['Quantity']; // count of result
                        $response[$key]['PickUpLocationCode'] = @$car_response['VehRentalCore']['PickUpLocation']['@attributes']['LocationCode'];
                        $response[$key]['ReturnLocationCode'] = @$car_response['VehRentalCore']['ReturnLocation']['@attributes']['LocationCode'];
                    }
                    if(isset($car_response_avail_v['VehAvailCore']) && !empty($car_response_avail_v['VehAvailCore'])){
                        $response[$key]['Status'] = @$car_response_avail_v['VehAvailCore']['@attributes']['Status'];
                        $Vehicle = @$car_response_avail_v['VehAvailCore']['Vehicle'];
                        if(isset($Vehicle) && valid_array($Vehicle)){
                            $result_token[$key]['booking_source'] = $this->booking_source;
                           
                            $response[$key]['AirConditionInd'] = @$Vehicle['@attributes']['AirConditionInd'];
                            $response[$key]['TransmissionType'] = @$Vehicle['@attributes']['TransmissionType'];
                            $response[$key]['FuelType'] = @$Vehicle['@attributes']['FuelType'];
                            $response[$key]['DriveType'] = @$Vehicle['@attributes']['DriveType'];
                            $response[$key]['PassengerQuantity'] = @$Vehicle['@attributes']['PassengerQuantity'];
                            $response[$key]['BaggageQuantity'] = @$Vehicle['@attributes']['BaggageQuantity'];
                            $response[$key]['VendorCarType'] = @$Vehicle['@attributes']['VendorCarType'];
                            $response[$key]['Code'] = @$Vehicle['@attributes']['Code'];
                            $response[$key]['CodeContext'] = @$Vehicle['@attributes']['CodeContext'];
                            if(isset($Vehicle['VehType']) && !empty($Vehicle['VehType'])){
                                $response[$key]['VehicleCategory'] = @$Vehicle['VehType']['@attributes']['VehicleCategory'];
                                $car_category = $this->CI->car_model->get_vehicle_category(@$Vehicle['VehType']['@attributes']['VehicleCategory']);
                                // debug($car_category);exit;
                                $response[$key]['VehicleCategoryName'] = $car_category;
                                $response[$key]['DoorCount'] = @$Vehicle['VehType']['@attributes']['DoorCount'];  
                            }
                            if(isset($Vehicle['VehClass']) && !empty($Vehicle['VehClass'])){
                                $response[$key]['VehClassSize'] = @$Vehicle['VehClass']['@attributes']['Size'];
                                $car_size= $this->CI->car_model->get_vehicle_size(@$Vehicle['VehClass']['@attributes']['Size']);
                                $response[$key]['VehClassSizeName'] = $car_size;
                              
                            }
                            if(isset($Vehicle['VehMakeModel']) && !empty($Vehicle['VehMakeModel'])){
                                $response[$key]['Name'] = @$Vehicle['VehMakeModel']['@attributes']['Name'];
                                $response[$key]['Code'] = @$Vehicle['VehMakeModel']['@attributes']['Code'];  
                                $result_token[$key]['Name'] = @$Vehicle['VehMakeModel']['@attributes']['Name'];
                            }
                            if(isset($Vehicle['VehClass']) && !empty($Vehicle['VehClass'])){
                                $response[$key]['PictureURL'] = @$Vehicle['PictureURL'];
                            }
                            $RentalRate = @$car_response_avail_v['VehAvailCore']['RentalRate'];

                            if(isset($RentalRate) && !empty($RentalRate)){
                                if(isset($RentalRate['RateDistance']) && !empty($RentalRate['RateDistance'])){
                                    $response[$key]['Unlimited'] = @$RentalRate['RateDistance']['@attributes']['Unlimited'];  
                                    $response[$key]['DistUnitName'] = @$RentalRate['RateDistance']['@attributes']['DistUnitName'];
                                }
                               
                                if(isset($RentalRate['VehicleCharges']['VehicleCharge']) && !empty($RentalRate['VehicleCharges']['VehicleCharge'])){
                                    $vehicle_charges = force_multple_data_format($RentalRate['VehicleCharges']['VehicleCharge']);
                                     // debug($vehicle_charges);exit;
                                     foreach($vehicle_charges as $veh_key => $vehicle_ch){
                                        if($vehicle_ch['@attributes']['Purpose'] == 'Estimated deposit amount'){
                                            $response[$key]['Estimated_Deposit_Amount'] = $vehicle_ch['@attributes']['Description'];
                                        }
                                        
                                     }
                                    //$response['VehicleCharge'] = @$RentalRate['VehicleCharges']['VehicleCharge'];
                                }
                                if(isset($RentalRate['RateQualifier']) && !empty($RentalRate['RateQualifier'])){
                                    $response[$key]['VendorRateID'] = @$RentalRate['RateQualifier']['@attributes']['VendorRateID'];
                                    $response[$key]['RateComments'] = @$RentalRate['RateQualifier']['RateComments']['RateComment']['@attributes']['Name'];    
                                    $response[$key]['RateComments'] = ($response[$key]['RateComments'] == 'h')? 'SilverPackage': $response[$key]['RateComments'];
                                }
                                if(isset($RentalRate['RateRestrictions']) && !empty($RentalRate['RateRestrictions'])){
                                    $response[$key]['RateRestrictions'] = $RentalRate['RateRestrictions'];
                                }
                            }
                           
                            if(isset($car_response_avail_v['VehAvailCore']['Reference']) && !empty($car_response_avail_v['VehAvailCore']['Reference'])){
                                $response[$key]['Reference'] = @$car_response_avail_v['VehAvailCore']['Reference']['@attributes'];
                                $result_token[$key]['ID_Context'] = @$car_response_avail_v['VehAvailCore']['Reference']['@attributes']['ID_Context'];
                                $result_token[$key]['Type'] = @$car_response_avail_v['VehAvailCore']['Reference']['@attributes']['Type'];

                            }
                            if(isset($car_response_avail_v['VehAvailCore']['Vendor']) && !empty($car_response_avail_v['VehAvailCore']['Vendor'])){
                                $response[$key]['Vendor'] = @$car_response_avail_v['VehAvailCore']['Vendor']['@value'];

                            }
                            if(isset($car_response_avail_v['VehAvailCore']['VendorLocation']) && !empty($car_response_avail_v['VehAvailCore']['VendorLocation'])){
                                $response[$key]['VendorLocation'] = @$car_response_avail_v['VehAvailCore']['VendorLocation']['@value'];
                            }
                            if(isset($car_response_avail_v['VehAvailCore']['DropOffLocation']) && !empty($car_response_avail_v['VehAvailCore']['DropOffLocation'])){
                                $response[$key]['DropOffLocation'] = @$car_response_avail_v['VehAvailCore']['DropOffLocation']['@attributes']['Name'];
                            }
                            // debug($car_response_avail_v['VehAvailInfo']['PaymentRules']);exit;
                            if(isset($car_response_avail_v['VehAvailInfo']['PaymentRules']) && !empty($car_response_avail_v['VehAvailInfo']['PaymentRules'])){
                                // $response[$key]['PaymentRules']['PaymentRule'] = $car_response_avail_v['VehAvailInfo']['PaymentRules']['PaymentRule']['@value'];
                                // $response[$key]['PaymentRules']['PaymentType'] = $car_response_avail_v['VehAvailInfo']['PaymentRules']['PaymentRule']['@attributes']['PaymentType'];

                            }

                            if(isset($car_response_avail_v['VehAvailInfo']['TPA_Extensions']) && !empty($car_response_avail_v['VehAvailInfo']['TPA_Extensions'])){
                                // $response[$key]['TPA_Extensions']['TermsConditions'] = $car_response_avail_v['VehAvailInfo']['TPA_Extensions']['TermsConditions']['@attributes']['url'];
                                // $response[$key]['TPA_Extensions']['SupplierLogo'] = $car_response_avail_v['VehAvailInfo']['TPA_Extensions']['SupplierLogo']['@attributes']['url'];

                            }
                            if(isset($car_response_avail_v['AdvanceBooking']) && !empty($car_response_avail_v['AdvanceBooking'])){
                                $response[$key]['AdvanceBooking_RulesApplyInd'] = $car_response_avail_v['AdvanceBooking']['@attributes']['RulesApplyInd'];                        
                            }
                            $PricedCoverages = $car_response_avail_v['VehAvailInfo']['PricedCoverages'];
                            // debug($PricedCoverages);exit;
                            if(isset($PricedCoverages['PricedCoverage']) && !empty($PricedCoverages['PricedCoverage'])){
                                $PricedCoverage = $PricedCoverages['PricedCoverage'];
                                $prce_coverage_array = array();
                                $price_cov_code = array();
                                $other_tax_amount = 0;
                                foreach($PricedCoverage as $key1 => $coverage){
                                    // debug($coverage);exit;
                                    if($coverage['Coverage']['@attributes']['Code'] != 'CF'){
                                        if(isset($coverage['Coverage']['Details']['Charge']['@attributes']['Amount']) && ($coverage['Coverage']['Details']['Charge']['@attributes']['Amount'] != '0.00')){
                                            $amount =  @$coverage['Coverage']['Details']['Charge']['@attributes']['Amount'];
                                        }
                                        else{
                                            $amount = 0;
                                        }
                                        
                                        if(isset($coverage['Charge']['@attributes']['Description'])){
                                            $desc = $coverage['Charge']['@attributes']['Description'];
                                            //$description = explode('with excess up to', @$coverage['Charge']['@attributes']['Description']);
                                        }
                                        else if(isset($coverage['Coverage']['Details']['Charge']['@attributes']['Description'])){
                                            $desc = $coverage['Coverage']['Details']['Charge']['@attributes']['Description'];
                                            // $desc_data = explode(': ', $desc_data);
                                            // if(isset($desc_data[0])){
                                            //     $desc_data = $desc_data[0]; 
                                            // }
                                            // else{
                                            //     $desc_data = $desc_data;
                                            // }
                                           
                                        }
                                        // if(isset($description[1])){
                                        //     $desc_amount = explode(' ', $description[1]);
                                           
                                        //     $desc_amount1 =  $this->convert_INR_price($desc_amount[1], $desc_amount[2]);
                                        //     $desc = 'with excess up to '.$desc_amount1.' INR'; 
                                        // }
                                        // else{
                                        //    $desc = $desc_data;
                                        // }
                                        $code = $coverage['Coverage']['@attributes']['Code'];
                                        if($code ==  416){
                                            $desc = htmlspecialchars($coverage['Coverage']['@attributes']['CoverageType']);
                                            $coverage['Coverage']['@attributes']['CoverageType'] = 'Limited Mileage';

                                        }
                                        if(($code == 418) ){
                                            if($coverage['Coverage']['Details']['Charge']['@attributes']['IncludedInEstTotalInd'] == "false"){
                                                $other_tax_amount += $amount;
                                                $prce_coverage_array[$key1]['Code'] = $code;
                                                $prce_coverage_array[$key1]['CoverageType'] = htmlspecialchars($coverage['Coverage']['@attributes']['CoverageType']);
                                                $prce_coverage_array[$key1]['Currency'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                                $prce_coverage_array[$key1]['Amount'] = $amount;
                                                $prce_coverage_array[$key1]['Desscription'] = @$desc;
                                                $prce_coverage_array[$key1]['IncludedInRate'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['IncludedInRate'];
                                                $prce_coverage_array[$key1]['IncludedInEstTotalInd'] = @$coverage['Charge']['@attributes']['IncludedInEstTotalInd'];
                                                $prce_coverage_array[$key1]['TaxInclusive'] = @$coverage['Charge']['@attributes']['TaxInclusive'];
                                            }
                                        }
                                        else{
                                            $prce_coverage_array[$key1]['Code'] = $code;
                                            $prce_coverage_array[$key1]['CoverageType'] = htmlspecialchars($coverage['Coverage']['@attributes']['CoverageType']);
                                            $prce_coverage_array[$key1]['Currency'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                            $prce_coverage_array[$key1]['Amount'] = $amount;
                                            $prce_coverage_array[$key1]['Desscription'] = @$desc;
                                            $prce_coverage_array[$key1]['IncludedInRate'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['IncludedInRate'];
                                            $prce_coverage_array[$key1]['IncludedInEstTotalInd'] = @$coverage['Charge']['@attributes']['IncludedInEstTotalInd'];
                                            $prce_coverage_array[$key1]['TaxInclusive'] = @$coverage['Charge']['@attributes']['TaxInclusive'];
                                        }
                                        // if(!in_array($code, $price_cov_code)){
                                            
                                        //     array_push($price_cov_code, $code);
                                        // }
                                        // else{
                                        //     foreach($prce_coverage_array as $p_key => $p_value){
                                               
                                        //         if($p_value['Code'] == $code && $p_value['IncludedInEstTotalInd'] == 'false'){
                                        //             $currency = @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                        //             $amount = $p_value['Amount'] + $amount;
                                        //             $prce_coverage_array[$p_key]['Amount'] = $amount;
                                        //             $currency_symobl = $this->CI->custom_db->single_table_records('currency_converter','currency_symbol',array('country' => $currency));
                                        //             if($currency_symobl['status'] == SUCCESS_STATUS){
                                        //                 $currency_symobl = $currency_symobl['data'][0]['currency_symbol'];
                                        //             }
                                        //             else{
                                        //                $currency_symobl = $currency;
                                        //             }

                                        //             // debug($currency_symobl);exit;
                                        //             $desc_data = explode(': ', $desc);
                                        //             if(isset($desc_data[0])){
                                        //                 $desc_data = $desc_data[0]; 
                                        //             }
                                        //             else{
                                        //                 $desc_data = $desc_data;
                                        //             }
                                        //             if($desc_data == 'per rental'){
                                        //                 $desc_data = $desc_data.": ".$currency_symobl.$amount;
                                        //             }
                                                   
                                        //             $prce_coverage_array[$p_key]['Desscription'] = @$desc_data;
                                        //         }
                                        //     }
                                        // }
                                    }

                                    $response[$key]['PricedCoverage'] = $prce_coverage_array;
                                    if($code == 412){
                                        $response[$key]['OneWayFee']['Amount'] = $OneWayFee = @$coverage['Coverage']['Details']['Charge']['@attributes']['Amount'];
                                     
                                        $response[$key]['OneWayFee']['Amount'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['Amount'];
                                        $response[$key]['OneWayFee']['CurrencyCode'] =  @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                        $response[$key]['TotalCharge']['OneWayFee'] = @$OneWayFee;
                                    }
                                    
                                    if($coverage['Coverage']['@attributes']['Code'] == 'CF'){
                                       // debug($coverage['Coverage']['Details']);
                                        $CancellationPolicy = array();
                                       foreach($coverage['Coverage']['Details'] as $key2 => $Details){
                                            $cancel_date = explode('_', $Details['Coverage']['@attributes']['CoverageType']);
                                            $cance_from_date = date('Y-m-d H:i', strtotime($cancel_date[0] .'-'.$this->api_cancellation_policy_day.' days' ));
                                            $cance_to_date = date('Y-m-d H:i', strtotime($cancel_date[1] .'-'.$this->api_cancellation_policy_day.' days' ));
                                            $current_date = date('Y-m-d H:i');
                                            // echo $cance_to_date;exit;
                                            if(strtotime($cance_to_date) > strtotime($current_date)){
                                               if(strtotime($cance_from_date) > strtotime($current_date)){
                                                    $cance_from_date = $cance_from_date;
                                               }
                                               else{
                                                    $cance_from_date = $current_date;
                                               }
                                                $CancellationPolicy[$key2]['FromDate'] = $cance_from_date;
                                                $CancellationPolicy[$key2]['ToDate'] = $cance_to_date;
                                                // $CancellationPolicy[$key2]['FromDate'] = $cancel_date[0];
                                                // $CancellationPolicy[$key2]['ToDate'] = $cancel_date[1];
                                                $CancellationPolicy[$key2]['CurrencyCode'] =  'INR';
                                                if(isset($Details['Charge']['@attributes']['Amount']) && ($Details['Charge']['@attributes']['Amount'] != 0)){
                                                    $CancellationPolicy[$key2]['Amount'] =  $this->convert_INR_price($Details['Charge']['@attributes']['Amount'],$Details['Charge']['@attributes']['CurrencyCode']);
                                                }
                                                else{
                                                    $CancellationPolicy[$key2]['Amount'] = 0;
                                                }
                                               
                                            }
                                        }
                                        // $response[$key]['CancellationPolicy'] = $CancellationPolicy;
                                        $response[$key]['CancellationPolicy'] = $this->CI->common_car->update_fare_markup_commission_cancel_policy($CancellationPolicy, 1,0,true,$this->booking_source);
                                    }
                                    else{
                                        $response[$key]['CancellationPolicy'] = array();
                                    }
                                    $response[$key]['ResultToken'] = serialized_data($result_token[$key]);
                                }
                              
                               
                            }
                            $TotalCharge = @$car_response_avail_v['VehAvailCore']['TotalCharge'];
                            if(isset($TotalCharge) && !empty($TotalCharge)){
                               
                                $response[$key]['TotalCharge'] = $TotalCharge['@attributes'];
                                $total_amount = $TotalCharge['@attributes']['EstimatedTotalAmount'] + $OneWayFee + $other_tax_amount;
                              
                                $Total_Price = $this->convert_INR_price($total_amount, $TotalCharge['@attributes']['CurrencyCode']);
                                $response[$key]['TotalCharge']['EstimatedTotalAmount'] = $Total_Price;
                                $response[$key]['TotalCharge']['CurrencyCode'] = 'INR';
                                $response[$key]['TotalCharge']['OneWayFee'] = 0;
                               
                            }
                        }
                    }
                }
            }
        }
        
        $response_data['CarSearchResult']['CarResults'] = $response;
        
        return $response_data;
    } 

    /**
     * Car Rules
     * @param unknown_type $request
     * @param unknown_type $search_id
     */
    function get_car_rules($request, $search_id){
        $response ['status'] = FAILURE_STATUS; // Status Of Operation
        $response ['message'] = ''; // Message to be returned
        $response ['data'] = array(); // Data to be returned
        $search_data = $this->search_data($search_id);
        $car_rules_request = $this->car_rules_request($search_data['data'], $request);
        //debug($car_rules_request);exit;
        if ($car_rules_request ['status'] == SUCCESS_STATUS) {
            $car_rules_response = $this->process_request($car_rules_request ['data']['request'], $this->xml_header(), $car_rules_request['data'] ['service_url'], $car_rules_request['data']['remarks']);
            // $car_rules_response = file_get_contents(FCPATH."car_net_car_rules.xml");
            // $car_rules_response = utf8_encode($car_rules_response);
            $car_rules_response = Converter::createArray($car_rules_response);
           
            if (valid_array($car_rules_response['soap:Envelope']['soap:Body']['VehRateRuleRS']) == true && ($car_rules_response['soap:Envelope']['soap:Header']['informationHeader']['Successfully'] == true)) {
                $response ['status'] = SUCCESS_STATUS;
                $response ['data']['CarRuleResult'][0] = $this->format_car_rules($car_rules_response['soap:Envelope']['soap:Body']['VehRateRuleRS'], $request, $search_id);
            } else {
                $response ['message'] = 'Not Available';
            }
        }
        else{
            $response ['status'] = FAILURE_STATUS;
        }

       return $response;
    }
    /**
     * Format Car Rules
     * @param unknown_type $car_rules_response
     */
    private function format_car_rules($car_rules_response, $request, $search_id) {
        // debug($car_rules_response);exit;
        $car_rule_array = array();
        if(valid_array($car_rules_response)){
            if (isset($car_rules_response['VehRentalCore']) && !empty($car_rules_response['VehRentalCore'])) {
                $car_rule_array['PickUpDateTime'] = $pickup_time = @$car_rules_response['VehRentalCore']['@attributes']['PickUpDateTime'];
                $car_rule_array['ReturnDateTime'] = $drop_time = @$car_rules_response['VehRentalCore']['@attributes']['ReturnDateTime']; 
                $car_rule_array['CompanyShortName'] = @$car_rules_response['VehRentalCore']['@attributes']['CompanyShortName']; // count of result
                $car_rule_array['TravelSector'] = @$car_rules_response['VehRentalCore']['@attributes']['TravelSector'];
                $car_rule_array['Code'] = @$car_rules_response['VehRentalCore']['@attributes']['Code'];
                $car_rule_array['CodeContext'] = @$car_rules_response['VehRentalCore']['@attributes']['CodeContext'];
                $Vehicle = @$car_rules_response['Vehicle'];
                $pickup_day = date("D", strtotime($pickup_time));
                $drop_day = date("D", strtotime($drop_time));
                if($pickup_day == 'Wed'){
                    $pickup_day = 'Weds';
                }
                else if($pickup_day == 'Thu'){
                    $pickup_day = 'Thur';
                }
                else{
                   $pickup_day = $pickup_day; 
                }
                if($drop_day == 'Wed'){
                    $drop_day = 'Weds';
                }
                else if($drop_day == 'Thu'){
                    $drop_day = 'Thur';
                }
                else{
                   $drop_day = $drop_day; 
                }
                
                // debug($request);exit;
                if(isset($Vehicle) && valid_array($Vehicle)){
                    $result_token[0] = $request;
                    $car_rule_array['AirConditionInd'] = @$Vehicle['@attributes']['AirConditionInd'];
                    $car_rule_array['TransmissionType'] = @$Vehicle['@attributes']['TransmissionType'];
                    $car_rule_array['FuelType'] = @$Vehicle['@attributes']['FuelType'];
                    $car_rule_array['DriveType'] = @$Vehicle['@attributes']['DriveType'];
                    $car_rule_array['PassengerQuantity'] = @$Vehicle['@attributes']['PassengerQuantity'];
                    $car_rule_array['BaggageQuantity'] = @$Vehicle['@attributes']['BaggageQuantity'];
                    $car_rule_array['VendorCarType'] = @$Vehicle['@attributes']['VendorCarType'];
                    $car_rule_array['Code'] = @$Vehicle['@attributes']['Code'];
                    $car_rule_array['CodeContext'] = @$Vehicle['@attributes']['CodeContext'];
                    if(isset($Vehicle['VehType']) && !empty($Vehicle['VehType'])){
                        $car_rule_array['VehicleCategory'] = @$Vehicle['VehType']['@attributes']['VehicleCategory'];
                        $car_category = $this->CI->car_model->get_vehicle_category(@$Vehicle['VehType']['@attributes']['VehicleCategory']);
                        $car_rule_array['VehicleCategoryName'] = $car_category;
                        $car_rule_array['DoorCount'] = @$Vehicle['VehType']['@attributes']['DoorCount'];  
                    }
                    if(isset($Vehicle['VehClass']) && !empty($Vehicle['VehClass'])){
                        $car_rule_array['VehClassSize'] = @$Vehicle['VehClass']['@attributes']['Size'];
                        $car_size= $this->CI->car_model->get_vehicle_size(@$Vehicle['VehClass']['@attributes']['Size']);
                        $car_rule_array['VehClassSizeName'] = $car_size;
                    }
                    if(isset($Vehicle['VehMakeModel']) && !empty($Vehicle['VehMakeModel'])){
                        $car_rule_array['Name'] = @$Vehicle['VehMakeModel']['@attributes']['Name'];
                        $car_rule_array['Code'] = @$Vehicle['VehMakeModel']['@attributes']['Code'];  
                    }
                    if(isset($Vehicle['VehClass']) && !empty($Vehicle['VehClass'])){
                        $car_rule_array['PictureURL'] = @$Vehicle['PictureURL'];
                    }
                    $RentalRate = @$car_rules_response['RentalRate'];
                    
                    if(isset($RentalRate) && !empty($RentalRate)){
                        if(isset($RentalRate['RateDistance']) && !empty($RentalRate['RateDistance'])){
                            $car_rule_array['Unlimited'] = @$RentalRate['RateDistance']['@attributes']['Unlimited'];  
                            $car_rule_array['DistUnitName'] = @$RentalRate['RateDistance']['@attributes']['DistUnitName'];
                        }
                        if(isset($RentalRate['VehicleCharges']['VehicleCharge']) && !empty($RentalRate['VehicleCharges']['VehicleCharge'])){
                            //$response['VehicleCharge'] = @$RentalRate['VehicleCharges']['VehicleCharge'];
                        }
                        if(isset($RentalRate['RateQualifier']) && !empty($RentalRate['RateQualifier'])){
                            $car_rule_array['VendorRateID'] = @$RentalRate['RateQualifier']['@attributes']['VendorRateID'];
                            $car_rule_array['RateComments'] = @$RentalRate['RateQualifier']['RateComments']['RateComment']['@attributes']['Name'];    
                            $car_rule_array['RateComments'] = ($response[$key]['RateComments'] == 'h')? 'SilverPackage': $response[$key]['RateComments'];
                        }
                        if(isset($RentalRate['RateRestrictions']) && !empty($RentalRate['RateRestrictions'])){
                            $car_rule_array['RateRestrictions'] = $RentalRate['RateRestrictions'];
                        }
                    }
                    $TotalCharge = @$car_rules_response['TotalCharge'];
                    // debug($TotalCharge);exit;
                    // if(isset($TotalCharge) && !empty($TotalCharge)){
                        
                    //     // $car_rule_array['TotalCharge'] = $TotalCharge['@attributes'];
                    //     $Total_Price = $this->convert_INR_price($TotalCharge['@attributes']['EstimatedTotalAmount'], $TotalCharge['@attributes']['CurrencyCode']);
                    //     $car_rule_array['TotalCharge']['EstimatedTotalAmount'] = $Total_Price;
                    //     $car_rule_array['TotalCharge']['CurrencyCode'] = 'INR';
                    //     $car_rule_array['TotalCharge']['OneWayFee'] = 0;
                        
                       
                    // }
                   
                    if(isset($car_rules_response['LocationDetails']) && !empty($car_rules_response['LocationDetails']) && valid_array($car_rules_response['LocationDetails'])){
                        $LocationDetails = force_multple_data_format($car_rules_response['LocationDetails']);
                       
                        foreach($LocationDetails as $loc_key => $location){
                            if($loc_key == 0){
                                $loc_type = 'PickUpLocation';
                            }
                            else{
                                $loc_type = 'DropLocation';
                            }
                            $tel_phone ='';
                            $telphone_numbers = force_multple_data_format($location['Telephone']);
                            $car_rule_array['LocationDetails'][$loc_type]['Address']['StreetNmbr'] = $location['Address']['StreetNmbr'];
                            $car_rule_array['LocationDetails'][$loc_type]['Address']['CityName'] = $location['Address']['CityName'];
                            $car_rule_array['LocationDetails'][$loc_type]['Address']['PostalCode'] = $location['Address']['PostalCode'];
                            $car_rule_array['LocationDetails'][$loc_type]['Address']['CountryName'] = $location['Address']['CountryName']['@value'];
                            foreach($telphone_numbers as $t_key => $t_value){
                                if(empty($t_value['@attributes']['PhoneNumber']) == false){
                                    $tel_phone .= $t_value['@attributes']['PhoneNumber'].", ";
                                }
                               
                            }
                          
                            $car_rule_array['LocationDetails'][$loc_type]['Telephone']= substr($tel_phone, 0,-2);
                            $car_rule_array['LocationDetails'][$loc_type]['value']['AtAirport'] = $location['@attributes']['AtAirport'];
                            $car_rule_array['LocationDetails'][$loc_type]['value']['Code'] = $location['@attributes']['Code'];
                            $car_rule_array['LocationDetails'][$loc_type]['value']['Name'] = $location['@attributes']['Name'];
                            $car_rule_array['LocationDetails'][$loc_type]['value']['CodeContext'] = $location['@attributes']['CodeContext'];
                            $car_rule_array['LocationDetails'][$loc_type]['value']['ExtendedLocationCode'] = $location['@attributes']['ExtendedLocationCode'];
                            $car_rule_array['LocationDetails'][$loc_type]['AdditionalInfo']['ParkLocation'] = $location['AdditionalInfo']['ParkLocation']['@attributes']['Location'];
                            $operation_times = $location['AdditionalInfo']['OperationSchedules']['OperationSchedule']['OperationTimes']['OperationTime'];
                            $operation_times = force_multple_data_format($operation_times);
                            $operation = array();
                            // debug($operation_times);
                            foreach($operation_times as $op_key => $op_time){
                                $i=0;
                                if(array_key_exists($pickup_day, $op_time['@attributes'])){
                                    $car_rule_array['LocationDetails'][$loc_type]['OperationSchedules']['Start'] = $op_time['@attributes']['Start'];
                                    $car_rule_array['LocationDetails'][$loc_type]['OperationSchedules']['End'] = $op_time['@attributes']['End'];
                                }
                                foreach($op_time['@attributes'] as $t_key => $time){
                                    if($i == 0){
                                        $time = $t_key;
                                        $t_key = 'Day';
                                        $ttt_key = $time;
                                    }
                                    $operation[$ttt_key][$t_key] = $time;
                                    $i++;
                                }
                            }
                            $car_rule_array['LocationDetails'][$loc_type]['AdditionalInfo']['OpeningHours'] = array_values($operation);
                            
                        }
                    }
                   
                    if(isset($car_rules_response['RateRules']['PaymentRules']) && !empty($car_rules_response['RateRules']['PaymentRules'])){
                        $car_rule_array['PaymentRules']['PaymentRule'] = $car_rules_response['RateRules']['PaymentRules']['PaymentRule']['@value'];
                        $car_rule_array['PaymentRules']['PaymentType'] = $car_rules_response['RateRules']['PaymentRules']['PaymentRule']['@attributes']['PaymentType'];
                    }
                  
                    if(isset($car_rules_response['TPA_Extensions']) && !empty($car_rules_response['TPA_Extensions'])){
                        $car_rule_array['TPA_Extensions']['TermsConditions'] = $car_rules_response['TPA_Extensions']['TermsConditions']['@attributes']['url'];
                        $car_rule_array['TPA_Extensions']['ProductInfo'] = $car_rules_response['TPA_Extensions']['ProductInformation']['@attributes']['url'];
                        $car_rule_array['TPA_Extensions']['SupplierLogo'] = $car_rules_response['TPA_Extensions']['SupplierLogo']['@attributes']['url'];
                    }
                    $PricedEquips = @$car_rules_response['PricedEquips'];
                    if(isset($PricedEquips['PricedEquip']) && !empty($PricedEquips['PricedEquip'])){
                        $PricedEquips = $PricedEquips['PricedEquip'];
                        
                        $price_equips_array = array();
                        foreach($PricedEquips as $key1 => $equips){
                            // debug($equips);exit;
                            $price_equips_array[$key1]['Description'] = $price_equips_array1[0]['Description'] = $equips['Equipment']['Description'];
                            $price_equips_array[$key1]['EquipType'] = $price_equips_array1[0]['EquipType'] = $equips['Equipment']['@attributes']['EquipType'];
                            $price_equips_array[$key1]['CurrencyCode'] = $equips['Charge']['@attributes']['CurrencyCode'];
                            // $price_equips_array[$key1]['Amount'] = $price_equips_array1[0]['Amount'] = $this->convert_INR_price($equips['Charge']['@attributes']['Amount'],$equips['Charge']['@attributes']['CurrencyCode']);
                            $price_equips_array[$key1]['Amount'] = $price_equips_array1[0]['Amount'] = $equips['Charge']['@attributes']['Amount'];
                            $price_equips_array[$key1]['ExtraServiceId'] = base64_encode(serialize($price_equips_array1));
                            if($equips['Equipment']['@attributes']['EquipType'] == 413){
                                $insurance_data = file_get_contents($car_rules_response['TPA_Extensions']['InsuranceContent']['@attributes']['url']);
                                $insurance_data = json_decode($insurance_data, true);
                                $price_equips_array[$key1]['policy_description'] = $insurance_data;
                                // $inclusion_list = $insurance_data['InclusionsList'];
                                // debug($inclusion_list);exit;
                            }
                        }
                        $car_rule_array['PricedEquip'] = $price_equips_array;
                    }
                   
                    $PricedCoverages = $car_rules_response['PricedCoverages'];
                    if(isset($PricedCoverages['PricedCoverage']) && !empty($PricedCoverages['PricedCoverage'])){
                        $PricedCoverage = $PricedCoverages['PricedCoverage'];
                        $prce_coverage_array = array();
                        $price_cov_code = array();
                        $other_tax_amount = 0;
                            foreach($PricedCoverage as $key1 => $coverage){
                                if($coverage['Coverage']['@attributes']['Code'] != 'CF'){
                                    if(isset($coverage['Coverage']['Details']['Charge']['@attributes']['Amount']) && ($coverage['Coverage']['Details']['Charge']['@attributes']['Amount'] != '0.00')){
                                        $amount =  @$coverage['Coverage']['Details']['Charge']['@attributes']['Amount'];
                                    }
                                    else{
                                        $amount = 0;
                                    }
                                    if($code ==  416){
                                        $desc = htmlspecialchars($coverage['Coverage']['@attributes']['CoverageType']);
                                        $coverage['Coverage']['@attributes']['CoverageType'] = 'Limited Mileage';
                                    }
                                    if(isset($coverage['Charge']['@attributes']['Description'])){
                                        $desc = $coverage['Charge']['@attributes']['Description'];
                                        //$description = explode('with excess up to', @$coverage['Charge']['@attributes']['Description']);
                                    }
                                    else if(isset($coverage['Coverage']['Details']['Charge']['@attributes']['Description'])){
                                        $desc = $coverage['Coverage']['Details']['Charge']['@attributes']['Description'];
                                    }
                                   
                                    $code = $coverage['Coverage']['@attributes']['Code'];
                                    if($code == 418){
                                       if($coverage['Coverage']['Details']['Charge']['@attributes']['IncludedInEstTotalInd'] == "false"){
                                            // debug($coverage);exit;
                                            $other_tax_amount += $amount;
                                                
                                        }
                                    }
                                    // if(!in_array($code, $price_cov_code)){
                                        if($code == 418){
                                            if($coverage['Coverage']['Details']['Charge']['@attributes']['IncludedInRate'] == 'false'){
                                                $prce_coverage_array[$key1]['Code'] = $code;
                                                $prce_coverage_array[$key1]['CoverageType'] = htmlspecialchars($coverage['Coverage']['@attributes']['CoverageType']);
                                                $prce_coverage_array[$key1]['Currency'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                                $prce_coverage_array[$key1]['Amount'] = $amount;
                                                $prce_coverage_array[$key1]['Desscription'] = @$desc;
                                                $prce_coverage_array[$key1]['IncludedInRate'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['IncludedInRate'];
                                                $prce_coverage_array[$key1]['IncludedInEstTotalInd'] = @$coverage['Charge']['@attributes']['IncludedInEstTotalInd'];
                                                $prce_coverage_array[$key1]['TaxInclusive'] = @$coverage['Charge']['@attributes']['TaxInclusive'];
                                            }
                                           
                                        }
                                        else{
                                            $prce_coverage_array[$key1]['Code'] = $code;
                                            $prce_coverage_array[$key1]['CoverageType'] = utf8_decode($coverage['Coverage']['@attributes']['CoverageType']);
                                            $prce_coverage_array[$key1]['Currency'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                            $prce_coverage_array[$key1]['Amount'] = $amount;
                                            $prce_coverage_array[$key1]['Desscription'] = @$desc;
                                            $prce_coverage_array[$key1]['IncludedInRate'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['IncludedInRate'];
                                            $prce_coverage_array[$key1]['IncludedInEstTotalInd'] = @$coverage['Charge']['@attributes']['IncludedInEstTotalInd'];
                                            $prce_coverage_array[$key1]['TaxInclusive'] = @$coverage['Charge']['@attributes']['TaxInclusive'];
                                        }
                                        
                                        // array_push($price_cov_code, $code);
                                    // }
                                    // else{
                                    //     foreach($prce_coverage_array as $p_key => $p_value){
                                    //        if($p_value['Code'] == $code && $p_value['IncludedInEstTotalInd'] == 'false'){
                                    //             $currency = @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                    //             $amount = $p_value['Amount'] + $amount;
                                    //             $prce_coverage_array[$p_key]['Amount'] = $amount;
                                    //             $currency_symobl = $this->CI->custom_db->single_table_records('currency_converter','currency_symbol',array('country' => $currency));
                                    //             if($currency_symobl['status'] == SUCCESS_STATUS){
                                    //                 $currency_symobl = $currency_symobl['data'][0]['currency_symbol'];
                                    //             }
                                    //             else{
                                    //                $currency_symobl = $currency;
                                    //             }

                                    //             // debug($currency_symobl);exit;
                                    //             $desc_data = explode(': ', $desc);
                                    //             if(isset($desc_data[0])){
                                    //                 $desc_data = $desc_data[0]; 
                                    //             }
                                    //             else{
                                    //                 $desc_data = $desc_data;
                                    //             }
                                    //             if($desc_data == 'per rental'){
                                    //                 $desc_data = $desc_data.": ".$currency_symobl.$amount;
                                    //             }
                                               
                                    //             $prce_coverage_array[$p_key]['Desscription'] = @$desc_data;
                                    //         }
                                    //     }
                                    // }
                                }
                                $car_rule_array['PricedCoverage'] = $prce_coverage_array;
                                if($code == 412){

                                    $car_rule_array['OneWayFee']['Amount'] = $OneWayFee = @$coverage['Coverage']['Details']['Charge']['@attributes']['Amount'];
                                    $car_rule_array['OneWayFee']['Amount'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['Amount'];
                                    $car_rule_array['OneWayFee']['CurrencyCode'] =  $local_currency = @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                    $car_rule_array['TotalCharge']['OneWayFee'] = @$OneWayFee;
                                   
                                }
                                if($coverage['Coverage']['@attributes']['Code'] == 'CF'){
                                    $CancellationPolicy = array();
                                    foreach($coverage['Coverage']['Details'] as $key2 => $Details){
                                        $cancel_date = explode('_', $Details['Coverage']['@attributes']['CoverageType']);
                                        $cance_from_date = date('Y-m-d H:i', strtotime($cancel_date[0] .'-'.$this->api_cancellation_policy_day.' days' ));
                                        $cance_to_date = date('Y-m-d H:i', strtotime($cancel_date[1] .'-'.$this->api_cancellation_policy_day.' days' ));
                                        $current_date = date('Y-m-d H:i');
                                        // echo $cance_to_date;exit;
                                        if(strtotime($cance_to_date) > strtotime($current_date)){
                                           if(strtotime($cance_from_date) > strtotime($current_date)){
                                                $cance_from_date = $cance_from_date;
                                           }
                                           else{
                                                $cance_from_date = $current_date;
                                           }
                                            $CancellationPolicy[$key2]['FromDate'] = $cance_from_date;
                                            $CancellationPolicy[$key2]['ToDate'] = $cance_to_date;
                                            // $CancellationPolicy[$key2]['FromDate'] = $cancel_date[0];
                                            // $CancellationPolicy[$key2]['ToDate'] = $cancel_date[1];
                                            $CancellationPolicy[$key2]['CurrencyCode'] =  'INR';
                                            if(isset($Details['Charge']['@attributes']['Amount']) && ($Details['Charge']['@attributes']['Amount'] != 0)){
                                                $CancellationPolicy[$key2]['Amount'] =  $this->convert_INR_price($Details['Charge']['@attributes']['Amount'],$Details['Charge']['@attributes']['CurrencyCode']);
                                            }
                                            else{
                                                $CancellationPolicy[$key2]['Amount'] = 0;
                                            }
                                        }
                                    }
                                    // $response[$key]['CancellationPolicy'] = $CancellationPolicy;
                                   $car_rule_array['CancellationPolicy'] = $this->CI->common_car->update_fare_markup_commission_cancel_policy($CancellationPolicy, 1,0,true,$this->booking_source);
                                }
                                else{
                                    $car_rule_array['CancellationPolicy'] = array();
                                }
                                    $car_rule_array['ResultToken'] = serialized_data($result_token);
                                }
                            }
                            
                            $TotalCharge = @$car_rules_response['TotalCharge'];
                            if(isset($TotalCharge) && !empty($TotalCharge)){
                                $local_currency = $TotalCharge['@attributes']['CurrencyCode'];
                                $car_rule_array['TotalCharge'] = $TotalCharge['@attributes'];
                                $car_rule_array['TotalCharge']['local_OneWayFee'] = $OneWayFee;
                                $car_rule_array['TotalCharge']['local_Other_Tax_Amount'] = $other_tax_amount;
                                $car_rule_array['TotalCharge']['local_Currency'] = $local_currency;
                                $total_amount = $TotalCharge['@attributes']['EstimatedTotalAmount'];
                                $Total_Price = $this->convert_INR_price($total_amount, $TotalCharge['@attributes']['CurrencyCode']);
                                $car_rule_array['TotalCharge']['Pay_now'] = $Total_Price;
                                $total_amount = $TotalCharge['@attributes']['EstimatedTotalAmount'] + $OneWayFee + $other_tax_amount;
                                $Total_Price = $this->convert_INR_price($total_amount, $TotalCharge['@attributes']['CurrencyCode']);
                                
                                $OneWayFee = $this->convert_INR_price($OneWayFee, $TotalCharge['@attributes']['CurrencyCode']);
                                
                                $other_tax_amount = $this->convert_INR_price($other_tax_amount, $TotalCharge['@attributes']['CurrencyCode']);
                                $car_rule_array['TotalCharge']['EstimatedTotalAmount'] = $Total_Price;
                                $car_rule_array['TotalCharge']['OneWayFee'] = $OneWayFee;
                                $car_rule_array['TotalCharge']['Other_Tax_Amount'] = $other_tax_amount;
                                $car_rule_array['TotalCharge']['CurrencyCode'] = 'INR';
                            }
                        }
                    }
                }
       // debug($car_rule_array);exit;
       return $car_rule_array;
    }
     /* create request for Car Rules API */

    function car_rules_request($search_params, $request) {

        // debug($search_params);exit;
        $response ['status'] = FAILURE_STATUS;
        $response ['data'] = array();
        if (isset($request) && !empty($request)) {
            $request = '<?xml version="1.0" encoding="utf-8"?>
                        <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"> 
                        <soap:Body>
                        <VehRateRuleRQ Version="0" xmlns="http://www.opentravel.org/OTA/2003/05">
                            <POS>
                                <Source ISOCountry="IN">
                                    <RequestorID ID_Context="'.$this->password.'" Type="'.$this->username.'" />
                                    </Source>
                                </POS>
                                <Reference Type="'.$request['Type'].'" ID_Context="'.$request['ID_Context'].'"/>
                            </VehRateRuleRQ>
                        </soap:Body>
                    </soap:Envelope>';
            $response ['data'] ['request'] = $request;
            $response ['data'] ['service_url'] = $this->config['api_url'];
            $response ['data']['remarks'] = 'CarRules(Carnet)';
            $response ['status'] = SUCCESS_STATUS;

        }
        return $response;
       
    }
     /**
     * Process booking
     * @param array $booking_params
     */
    function process_booking($booking_params, $app_reference, $sequence_number, $search_id) {
        $response ['status'] = FAILURE_STATUS; // Status Of Operation
        $response ['message'] = ''; // Message to be returned
        $response ['data'] = array(); // Data to be returned

        $ResultToken = $booking_params['ResultToken'];
        // debug($booking_params);
        $car_booking_request = $this->car_booking_request($booking_params, $app_reference);
        // debug($car_booking_request);exit;
        if ($car_booking_request ['status'] == SUCCESS_STATUS) {
            $api_response = $this->process_request($car_booking_request ['data']['request'], $this->xml_header(), $car_booking_request['data'] ['service_url'], $car_booking_request['data']['remarks']);
             // debug($car_booking_response);exit;
            // $car_booking_response = $this->CI->custom_db->single_table_records('provab_api_response_history', '*', array('origin' => '2143'));
            // $api_response = $car_booking_response['data'][0]['response'];
            // debug($car_booking_response);exit;
           // $car_booking_response = file_get_contents(FCPATH."travelport_xmls/car_net_car_booking.xml");
            // $api_response = utf8_encode($car_booking_response);
            $api_response = Converter::createArray($api_response);
           
            if (valid_array($api_response['soap:Envelope']['soap:Body']['VehResRS']) == true && ($api_response['soap:Envelope']['soap:Header']['informationHeader']['Successfully'] == true) && 
                isset($api_response['soap:Envelope']['soap:Body']['VehResRS']['VehResRSCore']['@attributes']['ReservationStatus']) && $api_response['soap:Envelope']['soap:Body']['VehResRS']['VehResRSCore']['@attributes']['ReservationStatus'] == 'Confirmed') {
                
                $car_book_res = $api_response['soap:Envelope']['soap:Body']['VehResRS'];
                // debug($car_book_res);exit;
                $response ['status'] = SUCCESS_STATUS; // Status Of Operation
                $booking_status = 'BOOKING_CONFIRMED';
                $car_attributes['Booking_request'] = $car_booking_request['data']['request'];
                $car_attributes['Booking_params'] = $booking_params;
                $car_attributes['Booking_response'] = $car_booking_response;
                $car_attributes = json_encode($car_attributes);
                $company_name = $car_book_res['VehResRSCore']['VehReservation']['VehSegmentCore']['Vendor']['@attributes']['CompanyShortName'];
                if($company_name == 'Avis'){
                    $account_info['IATA-No'] = $car_book_res['VehResRSCore']['TPA_Extensions']['AccountingInformation']['IATA-No.'];
                    $account_info['AV.No'] = $car_book_res['VehResRSCore']['TPA_Extensions']['AccountingInformation']['AV.No.'];
                    $update_car_data['account_info'] = json_encode($account_info);
                }
                
                $update_car_data['attributes'] = $car_attributes;
                $update_car_data['supplier_identifier'] = $car_book_res['VehResRSCore']['VehReservation']['Customer']['Primary']['PaymentForm']['Voucher']['@attributes']['SupplierIdentifier'];
                $update_car_data['value_type'] = $car_book_res['VehResRSCore']['VehReservation']['Customer']['Primary']['PaymentForm']['Voucher']['@attributes']['ValueType'];
                $update_car_data['booking_reference'] = $car_book_res['VehResRSCore']['VehReservation']['Customer']['Primary']['PaymentForm']['Voucher']['@attributes']['Identifier'];
                $update_car_data['booking_id'] = $car_book_res['VehResRSCore']['VehReservation']['Customer']['Primary']['PaymentForm']['Voucher']['@attributes']['Identifier'];
                $this->CI->custom_db->update_record('car_booking_details', $update_car_data, array('app_reference' => trim($app_reference)));


            }
            else{
                // debug($api_response);exit;
                if(isset($api_response['soap:Envelope']['soap:Body']['VehResRS']['Errors']['Error'])){
                    $error = $api_response['soap:Envelope']['soap:Body']['VehResRS']['Errors']['Error']['@value'];
                    $response ['message'] = $error;
                }
                else{
                    $response ['message'] = 'Booking Failed';
                }
                // echo $error;exit;
                $booking_status = 'BOOKING_FAILED';
                $car_attributes['Booking_request'] = $car_booking_request['data']['book_request'];
                $car_attributes['Booking_params'] = $booking_params;
                $car_attributes = json_encode($car_attributes);
                $update_car_data['attributes'] = $car_attributes;
                $this->CI->custom_db->update_record('car_booking_details', $update_car_data, array('app_reference' => trim($app_reference)));
                //Log Exception
                $exception_log_message = '';
                $this->CI->exception_logger->log_exception($app_reference, $this->booking_source . '- (<strong>Book</strong>)', $exception_log_message, $error);
            }
            $this->update_booking_status($app_reference, $booking_status);
            return $response;
        }
    }
    /* create request for Car Booking API */

    function car_booking_request($booking_params) {
       
        $response ['status'] = FAILURE_STATUS;
        $response ['data'] = array();
        if(isset($booking_params['Passengers']['ExtraServices'])){
            $extra_services = $booking_params['Passengers']['ExtraServices'];
        }
        // debug($extra_services);exit;
        if (isset($booking_params) && !empty($booking_params)) {
            $passenger = $booking_params['Passengers'];
            if ($passenger['Title'] == '1') {
                $gender = 'Male';
            } else {
                $gender = 'Female';
            }
            $request_data = $booking_params['ResultToken'];
            $request = '<?xml version="1.0" encoding="utf-8"?> 
                        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                        <soap:Body>
                        <VehResRQ Version="0" xmlns="http://www.opentravel.org/OTA/2003/05">
                            <POS>
                                <Source ISOCountry="IN">
                                    <RequestorID ID_Context="'.$this->password.'" Type="'.$this->username.'" />
                                </Source>
                            </POS>
                        <VehResRQCore>
                        <Customer>
                        <Primary Gender="'.$gender.'" BirthDate="'.$passenger['DateOfBirth'].'" Language="EN">
                            <PersonName>
                                <NameTitle>'.$passenger['Title'].'</NameTitle>
                                <GivenName>'.$passenger['FirstName'].'</GivenName>
                                <Surname>'.$passenger['LastName'].'</Surname>
                            </PersonName>';                           
                            if(isset($passenger['ContactNo']) && !empty($passenger['ContactNo'])){
                                $request .= '<Telephone PhoneTechType="1" PhoneNumber="'.$passenger['ContactNo'].'" />';
                                $request .= '<Telephone PhoneTechType="2" PhoneNumber="'.$passenger['ContactNo'].'" />';
                            }

                            $request .= '<Email>'.$passenger['Email'].'</Email>
                            <Address>
                                <StreetNmbr>'.$passenger['AddressLine1'].'</StreetNmbr>
                                <AddressLine>'.$passenger['AddressLine1'].'</AddressLine>
                                <CityName>'.$passenger['City'].'</CityName> 
                                <PostalCode>'.$passenger['PinCode'].'</PostalCode>
                                 <StateProv StateCode="KA" />
                            </Address>
                            <CitizenCountryName Code="'.$passenger['CountryCode'].'"/>
                        </Primary>';
                          
                    $request .= '</Customer>
                    <VehPref Code="'.$request_data['ID_Context'].'" />';
                    if(isset($extra_services)){
                        $request .= '<SpecialEquipPrefs>';
                        foreach($extra_services as $ext_key => $extras){
                            if($ext_key == 'Child_equip_count'){
                                $quantity = $extras;
                                $extras = 8;
                            }
                            else if($ext_key == 'Booster_equip_count'){
                                $quantity = $extras;
                                $extras = 9;
                            }
                            else if($ext_key == 'Infant_equip_count'){
                                $quantity = $extras;
                                $extras = 7;
                            }
                            else{
                                $quantity = 1;
                            }
                            $request .= '<SpecialEquipPref EquipType="'.$extras.'" Quantity="'.$quantity.'"/>';
                        }
                        $request .= '</SpecialEquipPrefs>';
                        /*$request .= '<SpecialEquipPrefs>';
                        if(isset($extra_services['Addtionaldriver']) && !empty($extra_services['Addtionaldriver'])){
                            $request .= '<SpecialEquipPref EquipType="'.$extra_services['Addtionaldriver'].'" Quantity="1"/>';
                        }
                         if(isset($extra_services['FullProtection']) && !empty($extra_services['FullProtection'])){
                            $request .= '<SpecialEquipPref EquipType="'.$extra_services['FullProtection'].'" Quantity="1"/>';
                        }
                        if(isset($extra_services['Child_equip_count']) && !empty($extra_services['Child_equip_count'])){
                            $request .= '<SpecialEquipPref EquipType="8" Quantity="'.$extra_services['Child_equip_count'].'"/>';
                        }
                        if(isset($extra_services['Booster_equip_count']) && !empty($extra_services['Booster_equip_count'])){
                            $request .= '<SpecialEquipPref EquipType="9" Quantity="'.$extra_services['Booster_equip_count'].'"/>';
                        }
                        if(isset($extra_services['Infant_equip_count']) && !empty($extra_services['Infant_equip_count'])){
                            $request .= '<SpecialEquipPref EquipType="7" Quantity="'.$extra_services['Infant_equip_count'].'"/>';
                        }
                        if(isset($extra_services['Gps']) && !empty($extra_services['Gps'])){
                            $request .= '<SpecialEquipPref EquipType="'.$extra_services['Gps'].'" Quantity="1"/>';
                        }
                        if(isset($extra_services['Wifi']) && !empty($extra_services['Wifi'])){
                            $request .= '<SpecialEquipPref EquipType="'.$extra_services['Wifi'].'" Quantity="1"/>';    
                        }
                        if(isset($extra_services['Diesel']) && !empty($extra_services['Diesel'])){
                            $request .= '<SpecialEquipPref EquipType="'.$extra_services['Diesel'].'" Quantity="1"/>';
                        }
                        if(isset($extra_services['Other']) && !empty($extra_services['Other'])){
                            $request .= '<SpecialEquipPref EquipType="'.$extra_services['Other'].'" Quantity="1"/>';
                        }
                    $request .= '</SpecialEquipPrefs>';*/
                    }
                               $request .= '</VehResRQCore>
                                </VehResRQ>
                            </soap:Body>
                        </soap:Envelope>';
            // debug($request);exit;
            $response ['data'] ['request'] = $request;
            $response ['data'] ['service_url'] = $this->config['api_url'];
            $response ['data']['remarks'] = 'CarBooking(Carnet)';
            $response ['status'] = SUCCESS_STATUS;

        }
        return $response;
       
    }
       /**
     * Process Cancel Booking
     * Online Cancellation
     */
    public function cancel_booking($request) {
        $response ['status'] = FAILURE_STATUS; // Status Of Operation
        $response ['message'] = ''; // Message to be returned
        $response ['data'] = array(); // Data to be returned
        $app_reference = trim($request['AppReference']);

        $booking_details = $this->CI->custom_db->single_table_records('car_booking_details', '*', array('app_reference' => $app_reference));
        $customer_details = $this->CI->custom_db->single_table_records('car_booking_pax_details', '*', array('app_reference' => $app_reference));
        // $itinerary_details = $this->CI->custom_db->single_table_records('car_booking_itinerary_details', '*', array('app_reference' => $app_reference));
        // debug($customer_details);exit;
        //$booking_details['data'][0]['status'] = 'BOOKING_CONFIRMED';
        if ($booking_details['status'] == SUCCESS_STATUS && $booking_details['data'][0]['status'] == 'BOOKING_CONFIRMED') {
            $booking_details = $booking_details['data'][0];
            $request_params = array();

            $request_params['booking_reference'] = $booking_details['booking_reference'];
            $request_params['last_name'] = $customer_details['data'][0]['last_name'];
            $booking_ite_details = $this->CI->custom_db->single_table_records('car_booking_itinerary_details', '*', array('app_reference' => $app_reference));
            // debug($booking_ite_details);exit;
            $send_change_request_response = $this->send_change_request($request_params);
            // debug($send_change_request_response);exit;
            if ($send_change_request_response['status'] == SUCCESS_STATUS) {
                $cancel_response = $send_change_request_response['data']['cancel_status'];
                switch (strtolower($cancel_response)) {
                    case 'cancelled':
                        $ChangeRequestId = 3;
                        break;
                    case 'failed':
                        $ChangeRequestId = 2;
                        break;
                    case 'confirmed':
                        $ChangeRequestId = 3;
                        break;
                    case 'pending':
                        $ChangeRequestId = 1;
                        break;
                    default:
                        $ChangeRequestId = 0;
                        break;
                }
                // debug($booking_details);exit;
                $total_booking_amount = $booking_details['total_fare']+$booking_details['domain_markup']+$booking_details['domain_gst'];
                $response ['status'] = SUCCESS_STATUS;
                $get_cancellation_details_db = json_decode($booking_ite_details['data'][0]['cancellation_poicy'], true);
                // debug($get_cancellation_details_db);exit;
                if(!empty($get_cancellation_details_db)){
                    $tm_last_cancel_date = date('Y-m-d');
                    foreach ($get_cancellation_details_db as $l_key => $l_value) {
                        if ($l_value['Amount'] == 0) {
                            $tm_last_cancel_date = date('Y-m-d', strtotime($l_value['FromDate']));
                        }
                    }
                    $current_date = date('Y-m-d');
                    $cancel_charge = 0;
                    if ($tm_last_cancel_date > $current_date) {
                        $cancel_charge = 0;
                    } else {
                        foreach ($get_cancellation_details_db as $c_key => $c_value) {
                            $db_from_date = date('Y-m-d', strtotime($c_value['FromDate']));
                            $db_to_date = date('Y-m-d', strtotime($c_value['ToDate']));

                            if ($current_date >= $db_from_date && $current_date <= $db_to_date) {
                                $cancel_charge = round($c_value['Amount']);
                            }
                        }
                    }
               
            }
            else{
                $cancel_charge = round($total_booking_amount);
            }
            if ($cancel_charge > 0) {
                $ChangeRequestId = 2;
            } else {
                $ChangeRequestId = $ChangeRequestId;
            }
            $get_change_request_status_response['StatusDescription'] = $this->get_cancellation_status_description($ChangeRequestId);
            $price = abs($total_booking_amount - $cancel_charge);
            $cancellation_details['CarChangeRequestStatusResult']['RefundedAmount'] = $price;
            $cancellation_details['CarChangeRequestStatusResult']['CancellationCharge'] = $cancel_charge;
            $cancellation_details['CarChangeRequestStatusResult']['ChangeRequestId'] = $ChangeRequestId;
            $cancellation_details['CarChangeRequestStatusResult']['ChangeRequestStatus'] = $ChangeRequestId;
            $cancellation_details['CarChangeRequestStatusResult']['StatusDescription'] = $get_change_request_status_response['StatusDescription'];
            $get_change_request_status_response['ChangeRequestId'] = $ChangeRequestId;
            $get_change_request_status_response['RefundedAmount'] = $price;
            $get_change_request_status_response['CancellationCharge'] = $cancel_charge;

            $this->CI->car_model->update_cancellation_details($app_reference, $cancellation_details);

            //Process the refund to client
            if ($ChangeRequestId == 3) {//if refund processed from supplier
                $get_change_request_status_response['ChangeRequestStatus'] = $get_change_request_status_response['StatusDescription'];
                $response['data']['CancellationDetails'] = $this->CI->common_car->update_domain_cancellation_refund_details($get_change_request_status_response, $app_reference);
            } else {
                $get_change_request_status_response['ChangeRequestStatus'] = $get_change_request_status_response['StatusDescription'];

                $response['data']['CancellationDetails'] = $get_change_request_status_response;
            }
        }
    }
    elseif ($booking_details['status'] == SUCCESS_STATUS && $booking_details['data'][0]['status'] == 'BOOKING_CANCELLED') {
        $booking_details = $booking_details['data'][0];
        $app_reference = $booking_details['app_reference'];
        $get_cancellation_details = $this->CI->custom_db->single_table_records('car_cancellation_details', '*', array('app_reference' => $app_reference));
        if ($get_cancellation_details['status'] == true) {
            $cancel_details = $get_cancellation_details['data'][0];
            $response ['status'] = SUCCESS_STATUS;
            $cancel_details_data['ChangeRequestId'] = $cancel_details['ChangeRequestId'];
            $cancel_details_data['ChangeRequestStatus'] = $cancel_details['ChangeRequestStatus'];
            $cancel_details_data['RefundedAmount'] = $cancel_details['refund_amount'];
            $cancel_details_data['CancellationCharge'] = $cancel_details['cancellation_charge'];
            $cancel_details_data['StatusDescription'] = $cancel_details['refund_status'];
            $response['data']['CancellationDetails'] = $cancel_details_data;
            $response ['message'] = 'Booking Already Cancelled';
        } else {
            $response['message'] = 'Invalid Request';
        }
    }
    else {
        $response ['message'] = 'Invalid Request';
    }
    // debug($response);exit;
    return $response;
}
     /**
     * Process Cancel Booking
     * Online Cancellation
     */
    public function admin_cancel_booking($request) {
        $response ['status'] = FAILURE_STATUS; // Status Of Operation
        $response ['message'] = ''; // Message to be returned
        $response ['data'] = array(); // Data to be returned
        $app_reference = trim($request['AppReference']);

        $booking_details = $this->CI->custom_db->single_table_records('car_booking_details', '*', array('app_reference' => $app_reference));
        $customer_details = $this->CI->custom_db->single_table_records('car_booking_pax_details', '*', array('app_reference' => $app_reference));
        // $itinerary_details = $this->CI->custom_db->single_table_records('car_booking_itinerary_details', '*', array('app_reference' => $app_reference));
        // debug($customer_details);exit;
        //$booking_details['data'][0]['status'] = 'BOOKING_CONFIRMED';
        if ($booking_details['status'] == SUCCESS_STATUS && $booking_details['data'][0]['status'] == 'BOOKING_CONFIRMED') {
            $booking_details = $booking_details['data'][0];
            $request_params = array();

            $request_params['booking_reference'] = $booking_details['booking_reference'];
            $request_params['last_name'] = $customer_details['data'][0]['last_name'];
            $booking_ite_details = $this->CI->custom_db->single_table_records('car_booking_itinerary_details', '*', array('app_reference' => $app_reference));
            // debug($booking_ite_details);exit;
            $send_change_request_response = $this->send_change_request($request_params);
            // debug($send_change_request_response);exit;
            if ($send_change_request_response['status'] == SUCCESS_STATUS) {
                $cancel_response = $send_change_request_response['data']['cancel_status'];
                switch (strtolower($cancel_response)) {
                    case 'cancelled':
                        $ChangeRequestId = 3;
                        break;
                    case 'failed':
                        $ChangeRequestId = 2;
                        break;
                    case 'confirmed':
                        $ChangeRequestId = 3;
                        break;
                    case 'pending':
                        $ChangeRequestId = 1;
                        break;
                    default:
                        $ChangeRequestId = 0;
                        break;
                }
                // debug($booking_details);exit;
                $total_booking_amount = $booking_details['total_fare']+$booking_details['domain_markup']+$booking_details['domain_gst'];
                $response ['status'] = SUCCESS_STATUS;
                $get_cancellation_details_db = json_decode($booking_ite_details['data'][0]['cancellation_poicy'], true);
                // debug($get_cancellation_details_db);exit;
                if(!empty($get_cancellation_details_db)){
                    $tm_last_cancel_date = date('Y-m-d');
                    foreach ($get_cancellation_details_db as $l_key => $l_value) {
                        if ($l_value['Amount'] == 0) {
                            $tm_last_cancel_date = date('Y-m-d', strtotime($l_value['FromDate']));
                        }
                    }
                    $current_date = date('Y-m-d');
                    $cancel_charge = 0;
                    if ($tm_last_cancel_date > $current_date) {
                        $cancel_charge = 0;
                    } else {
                        foreach ($get_cancellation_details_db as $c_key => $c_value) {
                            $db_from_date = date('Y-m-d', strtotime($c_value['FromDate']));
                            $db_to_date = date('Y-m-d', strtotime($c_value['ToDate']));

                            if ($current_date >= $db_from_date && $current_date <= $db_to_date) {
                                $cancel_charge = round($c_value['Amount']);
                            }
                        }
                    }
               
            }
            else{
                $cancel_charge = round($total_booking_amount);
            }
            if ($cancel_charge > 0) {
                $ChangeRequestId = 2;
            } else {
                $ChangeRequestId = $ChangeRequestId;
            }
            $get_change_request_status_response['StatusDescription'] = $this->get_cancellation_status_description($ChangeRequestId);
            $price = abs($total_booking_amount - $cancel_charge);
            $cancellation_details['CarChangeRequestStatusResult']['RefundedAmount'] = $price;
            $cancellation_details['CarChangeRequestStatusResult']['CancellationCharge'] = $cancel_charge;
            $cancellation_details['CarChangeRequestStatusResult']['ChangeRequestId'] = $ChangeRequestId;
            $cancellation_details['CarChangeRequestStatusResult']['ChangeRequestStatus'] = $ChangeRequestId;
            $cancellation_details['CarChangeRequestStatusResult']['StatusDescription'] = $get_change_request_status_response['StatusDescription'];
            $get_change_request_status_response['ChangeRequestId'] = $ChangeRequestId;
            //Update Cancellation Details
            $get_change_request_status_response['ChangeRequestStatus'] = $get_change_request_status_response['StatusDescription'];

            $response['data']['CancellationDetails'] = $get_change_request_status_response;
            $response['data']['update_cancel_details'] = $cancellation_details;
        }
    }
    elseif ($booking_details['status'] == SUCCESS_STATUS && $booking_details['data'][0]['status'] == 'BOOKING_CANCELLED') {
        $booking_details = $booking_details['data'][0];
        $app_reference = $booking_details['app_reference'];
        $get_cancellation_details = $this->CI->custom_db->single_table_records('car_cancellation_details', '*', array('app_reference' => $app_reference));
        if ($get_cancellation_details['status'] == true) {
            $cancel_details = $get_cancellation_details['data'][0];
            $response ['status'] = SUCCESS_STATUS;
            $cancel_details_data['ChangeRequestId'] = $cancel_details['ChangeRequestId'];
            $cancel_details_data['ChangeRequestStatus'] = $cancel_details['ChangeRequestStatus'];
            $cancel_details_data['RefundedAmount'] = $cancel_details['refund_amount'];
            $cancel_details_data['CancellationCharge'] = $cancel_details['cancellation_charge'];
            $cancel_details_data['StatusDescription'] = $cancel_details['refund_status'];
            $response['data']['CancellationDetails'] = $cancel_details_data;
            $response ['message'] = 'Booking Already Cancelled';
        } else {
            $response['message'] = 'Invalid Request';
        }
    }
    else {
        $response ['message'] = 'Invalid Request';
    }
    // debug($response);exit;
    return $response;
}
     /**
     * Send ChangeRequest
     * @param unknown_type $booking_details
     * //ChangeRequestStatus: NotSet = 0,Unassigned = 1,Assigned = 2,Acknowledged = 3,Completed = 4,Rejected = 5,Closed = 6,Pending = 7,Other = 8
     */
    private function send_change_request($request_params) {
       
        $response ['status'] = FAILURE_STATUS; // Status Of Operation
        $response ['message'] = ''; // Message to be returned
        $response ['data'] = array(); // Data to be returned
        $car_cancel_request = $this->cancel_request($request_params);
        if ($car_cancel_request['status'] == SUCCESS_STATUS) {
            // debug($send_change_request);exit;
            $car_cancel_response = $this->process_request($car_cancel_request ['data']['request'], $this->xml_header(), $car_cancel_request['data'] ['service_url'], $car_cancel_request['data']['remarks']);
            // $car_cancel_response = file_get_contents(FCPATH."carnet_cancel_res.xml");
            
            // $api_response = utf8_encode($car_cancel_response);
            $api_response = Converter::createArray($car_cancel_response);
            if(valid_array($api_response['soap:Envelope']['soap:Body']['VehCancelResRS']) == true && isset($api_response['soap:Envelope']['soap:Body']['VehCancelResRS']['VehCancelRSCore']['@attributes']['CancelStatus'])
                && ($api_response['soap:Envelope']['soap:Body']['VehCancelResRS']['VehCancelRSCore']['@attributes']['CancelStatus'] == 'Cancelled')
                && !isset($api_response['soap:Envelope']['soap:Body']['VehCancelResRS']['Errors'])){
                $response ['status'] = SUCCESS_STATUS;
                $response['data']['cancel_status'] = 'confirmed';
                
            }else{
                if($api_response['soap:Envelope']['soap:Body']['VehCancelResRS']['Errors']){
                    $error_message = $api_response['soap:Envelope']['soap:Body']['VehCancelResRS']['Errors']['Error']['@value'];
                }

                if (empty($error_message) == true) {
                    $error_message = 'Cancellation Failed';
                    $response['message'] = $error_message;
                }
                $response['data']['cancel_status'] = 'failed';
                $response['message'] = $error_message;
               
            }
        
            return $response;
        }
    }
     /* create request for Car Cancel API */

    function cancel_request($request) {

     
        $response ['status'] = FAILURE_STATUS;
        $response ['data'] = array();
        if (isset($request) && !empty($request)) {
            $request = '<?xml version="1.0" encoding="utf-8"?>
                        <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"> 
                        <soap:Body>
                        <VehCancelResRQ Version="0" xmlns="http://www.opentravel.org/OTA/2003/05">
                            <POS>
                                <Source ISOCountry="IN">
                                    <RequestorID ID_Context="'.$this->password.'" Type="'.$this->username.'" />
                                </Source>
                            </POS>
                            <VehCancelRQCore CancelType="Book">
                                <UniqueID ID_Context="'.$request['booking_reference'].'" />
                                <PersonName>
                                    <Surname>'.$request['last_name'].'</Surname>
                                </PersonName>
                            </VehCancelRQCore>
                            </VehCancelResRQ>
                        </soap:Body>
                    </soap:Envelope>';
            $response ['data'] ['request'] = $request;
            $response ['data'] ['service_url'] = $this->config['api_url'];
            $response ['data']['remarks'] = 'CarCancel(Carnet)';
            $response ['status'] = SUCCESS_STATUS;

        }
        return $response;
       
    }
     /**
     * Update the Car booking status
     * @param unknown_type $app_reference
     * @param unknown_type $booking_status
     */
    private function update_booking_status($app_reference, $booking_status) {
        $app_reference = trim($app_reference);
        $booking_status = trim($booking_status);
        if (empty($app_reference) == false && empty($booking_status) == false) {
            $update_condition = array();
            $update_condition['app_reference'] = $app_reference;

            $update_data = array();
            $update_data['status'] = $booking_status;

            //update master table status
            $this->CI->custom_db->update_record('car_booking_details', $update_data, $update_condition);
            //update itinerary status
            $this->CI->custom_db->update_record('car_booking_itinerary_details', $update_data, $update_condition);
            //update passenger status
            $this->CI->custom_db->update_record('car_booking_pax_details', $update_data, $update_condition);
        }
    }
    /**
     * Returns Cancellation status description
     */
    private function get_cancellation_status_description($ChangeRequestStatus) {
        $description = '';
        //NotSet = 0,Pending = 1,InProgress = 2,Processed = 3,Rejected = 4
        switch ($ChangeRequestStatus) {
            case 1: $description = 'Pending';
                break;
            case 2: $description = 'InProgress';
                break;
            case 3: $description = 'Processed';
                break;
            case 4: $description = 'Rejected';
                break;
            default:$description = 'NotSet';
        }
        return $description;
    }
    /**
     * Covert the Price 
     */
    private function convert_INR_price($total_price, $API_Currency) {
       
        $conversion_amount = $GLOBALS ['CI']->custom_db->single_table_records('currency_detail','value',array('f_currency' => $API_Currency,'t_currency'=>'USD'));
       
        $total_price = $total_price * $conversion_amount['data'][0]['value'];
        return $total_price;
    }
    /**
     * check if the search RS is valid or not
     * @param array $search_result
     * search result RS to be validated
     */
    private function valid_search_result($search_result) {
        if (isset($search_result['soap:Envelope']['soap:Body']['VehAvailRateRS']['VehAvailRSCore']) == true && valid_array($search_result['soap:Envelope']['soap:Body']['VehAvailRateRS']['VehAvailRSCore']) == true && isset($search_result['soap:Envelope']['soap:Header']['informationHeader']['Successfully']) == true) {
            return true;
        } else {
            return false;
        }
    }
     /**
     * process soap API request
     *
     * @param string $request
     */
    function form_curl_params($request, $url) {
        $data['status'] = SUCCESS_STATUS;
        $data['message'] = '';
        $data['data'] = array();

        $curl_data = array();
        $curl_data['booking_source'] = $this->booking_source;
        $curl_data['request'] = $request;
        $curl_data['url'] = $url;
        $curl_data['header'] = array(
            'X-Originating-Ip: 14.141.47.106',
            "Content-type: text/xml;charset=utf-8",
            'Accept: application/xml',
            'Accept-Encoding: gzip'
        );

        $data['data'] = $curl_data;
        return $data;
    }
    function process_request($request,$header, $post_url, $remarks) {
        $insert_id = $this->CI->api_model->store_api_request($post_url, $request, $remarks);
        $insert_id = intval(@$insert_id['insert_id']);
        //echo $post_url;exit;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // Execute request, store response and HTTP response code
        $response = curl_exec($ch);
        $this->CI->api_model->update_api_response($response, $insert_id);
        $error = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $response;
    }
    /**
     * Header to be used for carnet
     */
    private function xml_header()
    {
        $this->xml_api_header = array(
            "Content-type: text/xml;charset=utf-8",
            'Accept: application/xml',
            'Accept-Encoding: gzip'
            );
        
        return $this->xml_api_header;
    }


    /**
     * Update markup currency for price object of hotel
     *
     * @param object $price_summary
     * @param object $currency_obj
     */
    function update_markup_currency(& $price_summary, & $currency_obj) {
        
    }
    /**
     * get total price from summary object
     *
     * @param object $price_summary
     */
    function total_price($price_summary) {
        
    }
      /**
     * Process booking
     * @param array $booking_params
     */
    // function process_booking($booking_params, $app_reference, $sequence_number, $search_id) {

    // }

   

}
