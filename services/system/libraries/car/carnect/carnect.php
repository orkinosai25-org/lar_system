<?php
/**
 * Provab Common Functionality For Carnect
 *
 * @package Provab
 * @subpackage provab
 * @category Libraries
 */
class Carnect
{
    public string $search_hash;
    public int $api_cancellation_policy_day = 0;

    protected object $CI;
    protected array $config = [];
    protected string $username = '';
    protected string $password = '';
    protected string $service_url = '';
    protected array $master_search_data = [];

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('Converter');
        $this->set_api_credentials();
        $this->get_cancellation_policy_day();
    }

    private function set_api_credentials(): void
    {
        $this->config['api_url'] = "https://ota2007a.carhire-solutions.com/service.asmx?WSDL";
        $this->service_url = $this->config['api_url'];
        $this->username = $this->config['login_id'] ?? '';
        $this->password = $this->config['password'] ?? '';
    }

    public function get_cancellation_policy_day(): void
    {
        $booking_source = CARNECT_CAR_BOOKING_SOURCE;
        $cancellation_day = $this->CI->custom_db->single_table_records(
            'set_car_cancellation',
            '*',
            ['api' => $booking_source]
        );

        $this->api_cancellation_policy_day = ($cancellation_day['status'] == 1)
            ? (int) trim($cancellation_day['data'][0]['day'] ?? 0)
            : 0;
    }

    public function search_data(int $search_id): array
    {
        $response = ['status' => true, 'data' => []];

        if (empty($this->master_search_data) || !valid_array($this->master_search_data)) {
            $clean_search_details = $this->CI->car_model->get_safe_search_data($search_id);

            if ($clean_search_details['status'] == true) {
                $this->master_search_data = $clean_search_details['data'];
                $response['data'] = $this->master_search_data;
            } else {
                $response['status'] = false;
            }
        } else {
            $response['data'] = $this->master_search_data;
        }

        $this->search_hash = md5(serialized_data($response['data']));
        return $response;
    }

    private function search_request(array $search_data): array
    {
        $pickup_loc_id = $search_data['pickup_loc_id'] ?? '';
        $return_loc_id = $search_data['return_loc_id'] ?? '';
        $pickup_datetime = date('Y-m-d\TH:i:s', strtotime($search_data['pickup_datetime'] ?? ''));
        $return_datetime = date('Y-m-d\TH:i:s', strtotime($search_data['return_datetime'] ?? ''));
        $pickup_location = $search_data['pickup_location'] ?? '';
        $return_location = $search_data['return_location'] ?? '';
        $driver_age = $search_data['driver_age'] ?? '';
        $country_code = $search_data['country'] ?? '';
        $RateQueryParameterType = (int)4;
        $CodeContext_FROM = (strpos($pickup_location, 'City/Downtown') != false) ? 1 : 2;
        $CodeContext_TO = (strpos($return_location, 'City/Downtown') != false) ? 1 : 2;

        $return_xml = '';
        if (!empty($return_loc_id)) {
            $return_xml = '<ReturnLocation LocationCode="' . htmlspecialchars($return_loc_id) . '" CodeContext="' . $CodeContext_TO . '" />';
        }

        $request = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"> 
        <soap:Body>
        <VehAvailRateRQ xmlns="http://www.opentravel.org/OTA/2003/05"> <POS>
             <Source ISOCountry="EN">
                <RequestorID ID_Context="'.$this->password.'" Type="'.$this->username.'" /> </Source>
                <Source ISOCountry="'.$country_code.'" />
                </POS>
                <VehAvailRQCore RateQueryType="Live">
                    <RateQueryParameterType>'.$RateQueryParameterType.'</RateQueryParameterType>
                    <VehRentalCore PickUpDateTime="'.$pickup_datetime.'" ReturnDateTime="'.$return_datetime.'">
                        <PickUpLocation LocationCode="'.$pickup_loc_id.'" CodeContext="'.$CodeContext_FROM.'" />
                        '.$return_xml.'
                    </VehRentalCore>
                    <DriverType Age="'.$driver_age.'"/>
                </VehAvailRQCore>
            </VehAvailRateRQ> 
        </soap:Body>
    </soap:Envelope>';

        return [
            'request' => $request,
            'url' => $this->config['api_url'],
            'status' => SUCCESS_STATUS
        ];
    }

    public function get_search_request(int $search_id): array
    {
        $response = ['status' => FAILURE_STATUS, 'message' => '', 'data' => []];
        $search_data = $this->search_data($search_id);

        if ($search_data['status'] == SUCCESS_STATUS) {
            $search_request = $this->search_request($search_data['data']);

            if ($search_request['status'] == SUCCESS_STATUS) {
                $response['status'] = SUCCESS_STATUS;
                $response['data'] = $this->form_curl_params($search_request['request'], $search_request['url'])['data'];
            }
        }

        return $response;
    }
    /**
     * Format car response
     *
     * @param string $carRawData
     * @param int $searchId
     * @return array{
     *     status: string,
     *     message: string,
     *     data: array
     * }
     */
    public function get_car_list(string $carRawData, int $searchId): array
    {
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];

        $searchData = $this->search_data($searchId);
        if ($searchData['status'] == SUCCESS_STATUS) {
            $apiResponse = Converter::createArray($carRawData);

            if ($this->valid_search_result($apiResponse)) {

                $rawVehAvailRateRS = $apiResponse['soap:Envelope']['soap:Body']['VehAvailRateRS'] ?? [];
                $cleanFormatData = $this->format_search_data_response($rawVehAvailRateRS, $searchData['data'], $searchId);
                if (!empty($cleanFormatData)) {
                    $response['status'] = SUCCESS_STATUS;
                    $response['data'] = $cleanFormatData;
                }
            }
        }

        return $response;
    }
    function get_car_rules(array $request, int $search_id): array
    {
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => []
        ];

        $search_data = $this->search_data($search_id);
        $car_rules_request = $this->car_rules_request($search_data['data'], $request);
        if ($car_rules_request['status'] != SUCCESS_STATUS) {
            return $response;
        }

        /*$car_rules_response_raw = $this->process_request(
            $car_rules_request['data']['request'],
            $this->xml_header(),
            $car_rules_request['data']['service_url'],
            $car_rules_request['data']['remarks']
        );*/
        $car_rules_response_raw = file_get_contents(FCPATH."car_nect/car_rules.xml");
        // Convert XML to array
        $car_rules_response = Converter::createArray($car_rules_response_raw);

        $rate_rule_rs = $car_rules_response['soap:Envelope']['soap:Body']['VehRateRuleRS'] ?? null;
        $success = $car_rules_response['soap:Envelope']['soap:Header']['informationHeader']['Successfully'] ?? false;

        if (is_array($rate_rule_rs) && $success == true) {
            $response['status'] = SUCCESS_STATUS;
            $response['data']['CarRuleResult'][0] = $this->format_car_rules($rate_rule_rs, $request, $search_id);
        } else {
            $response['message'] = 'Not Available';
        }

        return $response;
    }
     /**
     * Formates Search Response
     * Enter description here ...
     * @param unknown_type $search_result
     * @param unknown_type $search_data
     */
        
    function format_search_data_response(array $search_result, array $search_data): array
    {
        $response = [];
        $result_token = [];
        $response_data = [];
        if (isset($search_result['VehAvailRSCore']) && is_array($search_result['VehAvailRSCore'])) {
            $car_response = $search_result['VehAvailRSCore'];
            $car_response_avail = $car_response['VehVendorAvails']['VehVendorAvail']['VehAvails']['VehAvail'] ?? [];
            if (!empty($car_response_avail) && is_array($car_response_avail)) {
                foreach ($car_response_avail as $key => $car_response_avail_v) {
                    $rentalCore = $car_response['VehRentalCore']['@attributes'] ?? [];
                    $response[$key]['PickUpDateTime'] = $rentalCore['PickUpDateTime'] ?? '';
                    $response[$key]['ReturnDateTime'] = $rentalCore['ReturnDateTime'] ?? '';

                    $vehAvailCore = $car_response_avail_v['VehAvailCore'] ?? [];
                    if (!empty($vehAvailCore)) {
                        $response[$key]['Status'] = $vehAvailCore['@attributes']['Status'] ?? '';
                        $Vehicle = $vehAvailCore['Vehicle'] ?? [];

                        if (!empty($Vehicle) && is_array($Vehicle)) {
                            global $CI; // Use dependency injection in modern PHP
                            $result_token[$key]['booking_source'] = CARNECT_CAR_BOOKING_SOURCE;

                            $attrs = $Vehicle['@attributes'] ?? [];
                            $response[$key]['AirConditionInd'] = $attrs['AirConditionInd'] ?? '';
                            $response[$key]['TransmissionType'] = $attrs['TransmissionType'] ?? '';
                            $response[$key]['FuelType'] = $attrs['FuelType'] ?? '';
                            $response[$key]['PassengerQuantity'] = $attrs['PassengerQuantity'] ?? '';
                            $response[$key]['BaggageQuantity'] = $attrs['BaggageQuantity'] ?? '';
                            $response[$key]['VendorCarType'] = $attrs['VendorCarType'] ?? '';

                            $vehType = $Vehicle['VehType']['@attributes'] ?? [];
                            $vehClass = $Vehicle['VehClass']['@attributes'] ?? [];
                            $makeModel = $Vehicle['VehMakeModel']['@attributes'] ?? [];

                            $response[$key]['VehicleCategoryName'] = $CI->car_model->get_vehicle_category($vehType['VehicleCategory'] ?? '');
                            $response[$key]['DoorCount'] = $vehType['DoorCount'] ?? '';
                            $response[$key]['VehClassSizeName'] = $CI->car_model->get_vehicle_size($vehClass['Size'] ?? '');
                            $response[$key]['Name'] = $makeModel['Name'] ?? '';
                            $result_token[$key]['Name'] = $makeModel['Name'] ?? '';
                            $response[$key]['PictureURL'] = $Vehicle['PictureURL'] ?? '';
                        }

                        $RentalRate = $vehAvailCore['RentalRate'] ?? [];
                        if (!empty($RentalRate)) {
                            $rateDistance = $RentalRate['RateDistance']['@attributes'] ?? [];
                            $response[$key]['Unlimited'] = $rateDistance['Unlimited'] ?? '';
                            $response[$key]['DistUnitName'] = $rateDistance['DistUnitName'] ?? '';

                            $rateQualifier = $RentalRate['RateQualifier']['RateComments']['RateComment']['@attributes']['Name'] ?? '';
                            $response[$key]['RateComments'] = ($rateQualifier == 'h') ? 'SilverPackage' : $rateQualifier;
                            $response[$key]['RateRestrictions'] = $RentalRate['RateRestrictions'] ?? [];
                        }

                        $reference = $vehAvailCore['Reference']['@attributes'] ?? [];
                        $response[$key]['reference_url'] = $reference['URL'] ?? '';
                        $result_token[$key]['ID_Context'] = $reference['ID_Context'] ?? '';
                        $result_token[$key]['Type'] = $reference['Type'] ?? '';

                        $response[$key]['Vendor'] = $vehAvailCore['Vendor']['@value'] ?? '';
                        $response[$key]['DropOffLocation'] = $vehAvailCore['DropOffLocation']['@attributes']['Name'] ?? '';
                    }

                    $availInfo = $car_response_avail_v['VehAvailInfo'] ?? [];
                    $paymentRules = $availInfo['PaymentRules']['PaymentRule'] ?? [];
                    $paymentAttrs = $paymentRules['@attributes'] ?? [];
                    $response[$key]['PaymentRules'] = [
                        'PaymentRule' => $paymentRules['@value'] ?? '',
                        'PaymentType' => $paymentAttrs['PaymentType'] ?? '',
                    ];

                    $tpaExt = $availInfo['TPA_Extensions'] ?? [];
                    $response[$key]['TPA_Extensions'] = [
                        'TermsConditions' => $tpaExt['TermsConditions']['@attributes']['url'] ?? '',
                        'SupplierLogo' => $tpaExt['SupplierLogo']['@attributes']['url'] ?? '',
                    ];

                    $PricedCoverages = $availInfo['PricedCoverages']['PricedCoverage'] ?? [];
                    if (!empty($PricedCoverages)) {
                        $PricedCoverages = $this->is_assoc($PricedCoverages) ? [$PricedCoverages] : $PricedCoverages;

                        $coverageArray = [];
                        $other_tax_amount = 0;
                        $young_driver_amount = 0;
                        $OneWayFee = 0;
                        $CurrencyCode = '';

                        foreach ($PricedCoverages as $coverage) {
                            $covAttrs = $coverage['Coverage']['@attributes'] ?? [];
                            $chargeAttrs = $coverage['Coverage']['Details']['Charge']['@attributes'] ?? [];

                            $code = $covAttrs['Code'] ?? '';
                            $amount = floatval($chargeAttrs['Amount'] ?? 0);
                            $desc = $coverage['Charge']['@attributes']['Description']
                                ?? $chargeAttrs['Description']
                                ?? '';
                            $response[$key]['CancellationPolicy'] = array();
                            if ($code == 'CF') {
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
                                         $CancellationPolicy[$key2]['CurrencyCode'] =  'INR';
                                        if(isset($Details['Charge']['@attributes']['Amount']) && ($Details['Charge']['@attributes']['Amount'] != 0)){
                                            $CancellationPolicy[$key2]['Amount'] =  $this->convert_INR_price($Details['Charge']['@attributes']['Amount'],$Details['Charge']['@attributes']['CurrencyCode']);
                                        }
                                        else{
                                            $CancellationPolicy[$key2]['Amount'] = 0;
                                        }
                                       
                                    }
                                }
                                        
                                $response[$key]['CancellationPolicy'] = $CancellationPolicy;
                            }

                            if ($code == '416') {
                                $covAttrs['CoverageType'] = 'Limited Mileage';
                                $desc = htmlspecialchars($covAttrs['CoverageType'] ?? '');
                            }

                            if ($code == '410' && $amount > 0) {
                                $young_driver_amount = $amount;
                                $CurrencyCode = $chargeAttrs['CurrencyCode'] ?? '';
                            }

                            if ($code == '418' && ($chargeAttrs['IncludedInEstTotalInd'] ?? 'true') == 'false') {
                                $other_tax_amount += $amount;
                            }

                            if ($code == '412') {
                                $OneWayFee = $amount;
                                $CurrencyCode = $chargeAttrs['CurrencyCode'] ?? '';
                            }

                            $coverageArray[] = [
                                'Code' => $code,
                                'CoverageType' => htmlspecialchars($covAttrs['CoverageType'] ?? ''),
                                'Currency' => $chargeAttrs['CurrencyCode'] ?? '',
                                'Amount' => $amount,
                                'Desscription' => $desc,
                                'IncludedInRate' => $chargeAttrs['IncludedInRate'] ?? 'false',
                            ];
                             $response[$key]['ResultToken'] = serialized_data($result_token[$key]);
                        }

                        $response[$key]['PricedCoverage'] = $coverageArray;
                    }
                }
            }
            $response_data['CarSearchResult']['CarResults'] = $response;
        }

        return $response_data;
    }

    function is_assoc(array $arr): bool
    {
        return array_keys($arr) != range(0, count($arr) - 1);
    }
    /**
     * Format Car Rules
     * @param unknown_type $car_rules_response
     */
    private function format_car_rules(array $car_rules_response, array $request, int $search_id):array {
        $car_rule_array = array();
        if(valid_array($car_rules_response)){
            $search_data = $this->search_data($search_id);
            $search_no_of_days = abs(get_date_difference($search_data['data']['pickup_datetime'], $search_data['data']['return_datetime']));
           
            if (isset($car_rules_response['VehRentalCore']) && !empty($car_rules_response['VehRentalCore'])) {
                $car_rule_array['PickUpDateTime'] = $pickup_time = @$car_rules_response['VehRentalCore']['@attributes']['PickUpDateTime'];
                $car_rule_array['ReturnDateTime'] = $drop_time = @$car_rules_response['VehRentalCore']['@attributes']['ReturnDateTime']; 
                $car_rule_array['CompanyShortName'] = @$car_rules_response['VehRentalCore']['@attributes']['CompanyShortName']; // count of result
                $car_rule_array['TravelSector'] = @$car_rules_response['VehRentalCore']['@attributes']['TravelSector'];
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
                    
                    $car_rule_array['PassengerQuantity'] = @$Vehicle['@attributes']['PassengerQuantity'];
                    $car_rule_array['BaggageQuantity'] = @$Vehicle['@attributes']['BaggageQuantity'];
                    $car_rule_array['VendorCarType'] = @$Vehicle['@attributes']['VendorCarType'];
                    if(isset($Vehicle['VehType']) && !empty($Vehicle['VehType'])){
                        $car_category = $this->CI->car_model->get_vehicle_category(@$Vehicle['VehType']['@attributes']['VehicleCategory']);
                        $car_rule_array['VehicleCategoryName'] = $car_category;
                        $car_rule_array['DoorCount'] = @$Vehicle['VehType']['@attributes']['DoorCount'];  
                    }
                    if(isset($Vehicle['VehClass']) && !empty($Vehicle['VehClass'])){
                        $car_size= $this->CI->car_model->get_vehicle_size(@$Vehicle['VehClass']['@attributes']['Size']);
                        $car_rule_array['VehClassSizeName'] = $car_size;
                    }
                    if(isset($Vehicle['VehMakeModel']) && !empty($Vehicle['VehMakeModel'])){
                        $car_rule_array['Name'] = @$Vehicle['VehMakeModel']['@attributes']['Name'];
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
                        if(isset($RentalRate['RateQualifier']) && !empty($RentalRate['RateQualifier'])){
                          $car_rule_array['RateComments'] = @$RentalRate['RateQualifier']['RateComments']['RateComment']['@attributes']['Name'];    
                        }
                        if(isset($RentalRate['RateRestrictions']) && !empty($RentalRate['RateRestrictions'])){
                            $car_rule_array['RateRestrictions'] = $RentalRate['RateRestrictions'];
                        }
                    }
                    $TotalCharge = @$car_rules_response['TotalCharge'];
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
                        $PricedEquips = force_multple_data_format($PricedEquips);
                        
                        $price_equips_array = array();
                        foreach($PricedEquips as $key1 => $equips){
                            $no_of_days = 1;
                            $calculation = force_multple_data_format($equips['Charge']['Calculation']);
                            $no_of_days = (int)$calculation[0]['@attributes']['UnitName'];
                            if($equips['Equipment']['@attributes']['EquipType'] == 413){
                                $name = 'full_prot';
                            }
                            else if($equips['Equipment']['@attributes']['EquipType'] == 13){
                                $name = 'gps';
                            }
                            else if($equips['Equipment']['@attributes']['EquipType'] == 222){
                                $name = 'add_driver';
                            }
                            else if($equips['Equipment']['@attributes']['EquipType'] == 7){
                                $name = 'Infant';
                            }
                            else if($equips['Equipment']['@attributes']['EquipType'] == 8){
                                $name = 'Child';
                            }
                            else if($equips['Equipment']['@attributes']['EquipType'] == 9){
                                $name = 'Booster';
                            }
                            else{
                                $name = strtolower($equips['Equipment']['Description']);
                            }
                            $price_equips_array[$key1]['Description'] = $price_equips_array1[0]['Description'] = $equips['Equipment']['Description'];
                            $price_equips_array[$key1]['EquipType'] = $price_equips_array1[0]['EquipType'] = $equips['Equipment']['@attributes']['EquipType'];
                            $price_equips_array[$key1]['CurrencyCode'] = $equips['Charge']['@attributes']['CurrencyCode'];
                            $price_equips_array[$key1]['name'] = $name;
                            $price_equips_array[$key1]['Amount'] = $price_equips_array1[0]['Amount'] = $search_no_of_days*($equips['Charge']['@attributes']['Amount']/$no_of_days);
                            if($equips['Equipment']['@attributes']['EquipType'] == 413){
                                $insurance_data = file_get_contents($car_rules_response['TPA_Extensions']['InsuranceContent']['@attributes']['url']);
                                $insurance_data = json_decode($insurance_data, true);
                                $price_equips_array[$key1]['Amount'] = $price_equips_array1[0]['Amount'] = $equips['Charge']['@attributes']['Amount'];
                                $price_equips_array[$key1]['policy_description'] = $insurance_data;
                                
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
                        $young_driver_amount = 0;
                        $OneWayFee = 0;
                        $CurrencyCode = '';
                        foreach($PricedCoverage as $key1 => $coverage){
                            if($coverage['Coverage']['@attributes']['Code'] != 'CF'){
                                if(isset($coverage['Coverage']['Details']['Charge']['@attributes']['Amount']) && ($coverage['Coverage']['Details']['Charge']['@attributes']['Amount'] != '0.00')){
                                    $amount =  @$coverage['Coverage']['Details']['Charge']['@attributes']['Amount'];
                                }
                                else{
                                    $amount = 0;
                                }
                                
                                if(isset($coverage['Charge']['@attributes']['Description'])){
                                    $desc = $coverage['Charge']['@attributes']['Description'];
                                }
                                else if(isset($coverage['Coverage']['Details']['Charge']['@attributes']['Description'])){
                                    $desc = $coverage['Coverage']['Details']['Charge']['@attributes']['Description'];
                                }
                               
                                $code = $coverage['Coverage']['@attributes']['Code'];
                                if($code == 418){
                                    if($coverage['Coverage']['Details']['Charge']['@attributes']['IncludedInEstTotalInd'] == "false"){
                                        $CurrencyCode =  @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                        $other_tax_amount += $amount;
                                    }
                                }
                                if($code ==  416){
                                    $desc = htmlspecialchars($coverage['Coverage']['@attributes']['CoverageType']);
                                    $coverage['Coverage']['@attributes']['CoverageType'] = 'Limited Mileage';
                                }
                                if($code == 410){
                                   if($coverage['Coverage']['Details']['Charge']['@attributes']['Amount'] > 0){
                                        $CurrencyCode =  @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                        // debug($coverage);exit;
                                        $young_driver_amount = $coverage['Coverage']['Details']['Charge']['@attributes']['Amount'];
                                            
                                    }
                                }
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
                                    $prce_coverage_array[$key1]['CoverageType'] = htmlspecialchars($coverage['Coverage']['@attributes']['CoverageType']);
                                    $prce_coverage_array[$key1]['Currency'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                    $prce_coverage_array[$key1]['Amount'] = $amount;
                                    $prce_coverage_array[$key1]['Desscription'] = @$desc;
                                    $prce_coverage_array[$key1]['IncludedInRate'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['IncludedInRate'];
                                    $prce_coverage_array[$key1]['IncludedInEstTotalInd'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['IncludedInEstTotalInd'];
                                    $prce_coverage_array[$key1]['TaxInclusive'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['TaxInclusive'];
                                }
                                  
                            }
                            $car_rule_array['PricedCoverage'] = $prce_coverage_array;
                            if($code == 412){
                                $CurrencyCode =  @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                $OneWayFee = @$coverage['Coverage']['Details']['Charge']['@attributes']['Amount'];
                                // $car_rule_array['OneWayFee']['Amount'] = @$coverage['Coverage']['Details']['Charge']['@attributes']['Amount'];
                                // $car_rule_array['OneWayFee']['CurrencyCode'] =  $local_currency = @$coverage['Coverage']['Details']['Charge']['@attributes']['CurrencyCode'];
                                // $car_rule_array['TotalCharge']['OneWayFee'] = @$OneWayFee;
                               
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
                                        $CancellationPolicy[$key2]['CurrencyCode'] =  'INR';
                                        if(isset($Details['Charge']['@attributes']['Amount']) && ($Details['Charge']['@attributes']['Amount'] != 0)){
                                            $CancellationPolicy[$key2]['Amount'] =  $this->convert_INR_price($Details['Charge']['@attributes']['Amount'],$Details['Charge']['@attributes']['CurrencyCode']);
                                        }
                                        else{
                                            $CancellationPolicy[$key2]['Amount'] = 0;
                                        }
                                    }
                                }
                                $car_rule_array['CancellationPolicy'] = $CancellationPolicy;
                            }
                            else{
                                $car_rule_array['CancellationPolicy'] = array();
                            }
                            $car_rule_array['ResultToken'] = serialized_data($result_token);
                        }
                    }
                        $TotalCharge = @$car_rules_response['TotalCharge'];
                        if(isset($TotalCharge) && !empty($TotalCharge)){
                            $total_amount = $TotalCharge['@attributes']['EstimatedTotalAmount'] + $OneWayFee + $other_tax_amount + $young_driver_amount;
                            $Total_Price = $this->convert_INR_price($total_amount, $TotalCharge['@attributes']['CurrencyCode']);
                            $car_rule_array['TotalCharge']['Pay_now'] = $Total_Price;
                            $car_rule_array['TotalCharge']['EstimatedTotalAmount'] = $Total_Price;
                            $domain_currency_oneway_fee = 0;
                            $domain_currency_other_tax = 0;
                            $domain_currency_young_driver_amount = 0;
                            if($OneWayFee > 0){
                                $domain_currency_oneway_fee = $this->convert_INR_price($OneWayFee, $CurrencyCode);
                            }
                            if($other_tax_amount > 0){
                                $domain_currency_other_tax = $this->convert_INR_price($other_tax_amount, $CurrencyCode);
                            }
                            if($young_driver_amount > 0){
                                $domain_currency_young_driver_amount = $this->convert_INR_price($young_driver_amount, $CurrencyCode);
                            }

                            $car_rule_array['TotalCharge']['Pricebreakup']['RentalPrice'] = $this->convert_INR_price($TotalCharge['@attributes']['EstimatedTotalAmount'], $TotalCharge['@attributes']['CurrencyCode']);
                            $car_rule_array['TotalCharge']['Pricebreakup']['OnewayFee'] = (int)$domain_currency_oneway_fee;
                            $car_rule_array['TotalCharge']['Pricebreakup']['OtherTaxes'] = (int)$domain_currency_other_tax;
                            $car_rule_array['TotalCharge']['Pricebreakup']['YoungDriverAmount'] = (int)$domain_currency_young_driver_amount;
                            if(empty($CurrencyCode) == false){
                                $car_rule_array['TotalCharge']['Payonpickup']['OnewayFee'] = (int)$OneWayFee;
                                $car_rule_array['TotalCharge']['Payonpickup']['OtherTaxes'] = (int)$other_tax_amount;
                                $car_rule_array['TotalCharge']['Payonpickup']['YoungDriverAmount'] = (int)$young_driver_amount;
                                $car_rule_array['TotalCharge']['Payonpickup']['LocalCurrencyCode'] = $CurrencyCode;
                            }
                           
                            $car_rule_array['TotalCharge']['CurrencyCode'] = 'INR';
                        }
                    }
                }
            }
       // debug($car_rule_array);exit;
        return $car_rule_array;
    }
    public function car_rules_request(array $search_params, ?array $request): array
    {
        $response = [
            'status' => FAILURE_STATUS,
            'data' => [],
        ];

        if (!empty($request)) {
            $xmlRequest = '<?xml version="1.0" encoding="utf-8"?>
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

            $response = [
                'status' => SUCCESS_STATUS,
                'data' => [
                    'request' => $xmlRequest,
                    'service_url' => $this->config['api_url'],
                    'remarks' => 'CarRules(Carnet)',
                ],
            ];
        }

        return $response;
    }
    public function process_booking(array $booking_params, string $app_reference, int $sequence_number, int $search_id): array
    {
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];

        $car_booking_request = $this->car_booking_request($booking_params, $app_reference);

        if ($car_booking_request['status'] != SUCCESS_STATUS) {
            return $response;
        }

        /*$api_response_raw = $this->process_request(
            $car_booking_request['data']['request'],
            $this->xml_header(),
            $car_booking_request['data']['service_url'],
            $car_booking_request['data']['remarks']
        );*/
        $api_response_raw = file_get_contents(FCPATH."car_nect/booking_response.xml");
        $api_response = Converter::createArray($api_response_raw);
        $vehResRS = $api_response['soap:Envelope']['soap:Body']['VehResRS'] ?? [];
        $header = $api_response['soap:Envelope']['soap:Header']['informationHeader'] ?? [];

        $booking_status = 'BOOKING_FAILED';
        $error = 'Booking Failed';
        $Identifier="CN".rand(1000000000000,100);
        $SupplierIdentifier="MNV".rand(100000000,100);
        if (
            valid_array($vehResRS) &&
            ($header['Successfully'] ?? false) == true &&
            ($vehResRS['VehResRSCore']['@attributes']['ReservationStatus'] ?? '') == 'Confirmed'
        ) {
            $response['status'] = SUCCESS_STATUS;
            $booking_status = 'BOOKING_CONFIRMED';

            $car_book_res = $vehResRS;
            $car_attributes = json_encode([
                'Booking_request' => $car_booking_request['data']['request'],
                'Booking_params' => $booking_params,
                'Booking_response' => $car_book_res,
            ]);

            $company_name = $car_book_res['VehResRSCore']['VehReservation']['VehSegmentCore']['Vendor']['@attributes']['CompanyShortName'] ?? '';
            $update_car_data = [
                'attributes' => $car_attributes,
                'supplier_identifier' => $car_book_res['VehResRSCore']['VehReservation']['Customer']['Primary']['PaymentForm']['Voucher']['@attributes']['SupplierIdentifier'] ?? '',
                'value_type' => $car_book_res['VehResRSCore']['VehReservation']['Customer']['Primary']['PaymentForm']['Voucher']['@attributes']['ValueType'] ?? '',
                'booking_reference' => $car_book_res['VehResRSCore']['VehReservation']['Customer']['Primary']['PaymentForm']['Voucher']['@attributes']['Identifier'] ?? '',
                'booking_id' => $car_book_res['VehResRSCore']['VehReservation']['Customer']['Primary']['PaymentForm']['Voucher']['@attributes']['Identifier'] ?? '',
            ];

            if ($company_name == 'Avis') {
                $account_info = [
                    'IATA-No' => $car_book_res['VehResRSCore']['TPA_Extensions']['AccountingInformation']['IATA-No.'] ?? '',
                    'AV.No' => $car_book_res['VehResRSCore']['TPA_Extensions']['AccountingInformation']['AV.No.'] ?? '',
                ];
                $update_car_data['account_info'] = json_encode($account_info);
            }

            $this->CI->custom_db->update_record('car_booking_details', $update_car_data, [
                'app_reference' => trim($app_reference)
            ]);
        } else {
            $api_error = $vehResRS['Errors']['Error']['@value'] ?? null;

            if ($api_error != null) {
                $error = $api_error;
            }

            $car_attributes = json_encode([
                'Booking_request' => $car_booking_request['data']['book_request'] ?? '',
                'Booking_params' => $booking_params,
            ]);

            $update_car_data = ['attributes' => $car_attributes];

            $this->CI->custom_db->update_record('car_booking_details', $update_car_data, [
                'app_reference' => trim($app_reference)
            ]);

            $this->CI->exception_logger->log_exception(
                $app_reference,
                $this->booking_source . ' - (<strong>Book</strong>)',
                '',
                $error
            );

            $response['message'] = $error;
        }

        $this->update_booking_status($app_reference, $booking_status);

        return $response;
    }
    public function car_booking_request(array $bookingParams): array
    {
        $response = [
            'status' => FAILURE_STATUS,
            'data' => []
        ];

        if (empty($bookingParams)) {
            return $response;
        }

        $passenger = $bookingParams['Passengers'] ?? [];
        $extraServices = $passenger['ExtraServices'] ?? [];

        $gender = match ($passenger['Title'] ?? '') {
            '1' => 'Male',
            default => 'Female',
        };

        $requestData = $bookingParams['ResultToken'] ?? [];

        $phoneXml = '';
        if (!empty($passenger['ContactNo'])) {
            $phoneNumber = htmlspecialchars($passenger['ContactNo']);
            $phoneXml .='
                <Telephone PhoneTechType="1" PhoneNumber="'.$phoneNumber.'" />
                <Telephone PhoneTechType="2" PhoneNumber="'.$phoneNumber.'" />';
        }

        $email = htmlspecialchars($passenger['Email'] ?? '');
        $address = htmlspecialchars($passenger['AddressLine1'] ?? '');
        $city = htmlspecialchars($passenger['City'] ?? '');
        $pinCode = htmlspecialchars($passenger['PinCode'] ?? '');
        $countryCode = htmlspecialchars($passenger['CountryCode'] ?? '');
        $dob = htmlspecialchars($passenger['DateOfBirth'] ?? '');
        $title = htmlspecialchars($passenger['Title'] ?? '');
        $firstName = htmlspecialchars($passenger['FirstName'] ?? '');
        $lastName = htmlspecialchars($passenger['LastName'] ?? '');
        $vehPrefCode = htmlspecialchars($requestData['ID_Context'] ?? '');

        $specialEquipments = '';
        if (!empty($extraServices)) {
            $specialEquipments .= '<SpecialEquipPrefs>';
            foreach ($extraServices as $key => $value) {
                $quantity = (int)$value;
                $equipType = match ($key) {
                    'Child_equip_count' => 8,
                    'Booster_equip_count' => 9,
                    'Infant_equip_count' => 7,
                    default => $value,
                };
                $specialEquipments .= '<SpecialEquipPref EquipType="'.$equipType.'" Quantity="'.$quantity.'"/>';
            }
            $specialEquipments .= '</SpecialEquipPrefs>';
        }

        $xmlRequest = '
        <?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                       xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                       xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <VehResRQ Version="0" xmlns="http://www.opentravel.org/OTA/2003/05">
              <POS>
                <Source ISOCountry="IN">
                  <RequestorID ID_Context="'.$this->password.'" Type="'.$this->username.'" />
                </Source>
              </POS>
              <VehResRQCore>
                <Customer>
                  <Primary Gender="'.$gender.'" BirthDate="'.$dob.'" Language="EN">
                    <PersonName>
                      <NameTitle>'.$title.'</NameTitle>
                      <GivenName>'.$firstName.'</GivenName>
                      <Surname>'.$lastName.'</Surname>
                    </PersonName>
                    '.$phoneXml.'
                    <Email>'.$email.'</Email>
                    <Address>
                      <StreetNmbr>'.$address.'</StreetNmbr>
                      <AddressLine>'.$address.'</AddressLine>
                      <CityName>'.$city.'</CityName>
                      <PostalCode>'.$pinCode.'</PostalCode>
                      <StateProv StateCode="KA" />
                    </Address>
                    <CitizenCountryName Code="'.$countryCode.'" />
                  </Primary>
                </Customer>
                <VehPref Code="'.$vehPrefCode.'" />
                '.$specialEquipments.'
              </VehResRQCore>
            </VehResRQ>
          </soap:Body>
        </soap:Envelope>';

                $response['data'] = [
                    'request' => $xmlRequest,
                    'service_url' => $this->config['api_url'],
                    'remarks' => 'CarBooking(Carnet)'
                ];
                $response['status'] = SUCCESS_STATUS;

                return $response;
    }

    public function cancel_booking(array $request): array
    {
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];

        $app_reference = trim($request['AppReference']);

        // Fetch booking and customer details
        $booking_details = $this->CI->custom_db->single_table_records('car_booking_details', '*', ['app_reference' => $app_reference]);
        $customer_details = $this->CI->custom_db->single_table_records('car_booking_pax_details', '*', ['app_reference' => $app_reference]);

        // Check if the booking exists and is confirmed
        if ($booking_details['status'] != SUCCESS_STATUS || $booking_details['data'][0]['status'] != 'BOOKING_CONFIRMED') {
            $response['message'] = 'Invalid Request';
            return $response;
        }

        $booking_details = $booking_details['data'][0];
        $request_params = [
            'booking_reference' => $booking_details['booking_reference'],
            'last_name' => $customer_details['data'][0]['last_name'],
        ];

        // Send change request to supplier
        $send_change_request_response = $this->send_change_request($request_params);

        if ($send_change_request_response['status'] != SUCCESS_STATUS) {
            $response['message'] = 'Error in sending change request';
            return $response;
        }

        $cancel_response = $send_change_request_response['data']['cancel_status'];
        $ChangeRequestId = $this->map_cancel_response_to_change_request_id($cancel_response);

        // Calculate cancellation charge based on policy
        $total_booking_amount = $booking_details['total_fare'] + $booking_details['domain_markup'] + $booking_details['domain_gst'];
        $booking_ite_details = $this->CI->custom_db->single_table_records('car_booking_itinerary_details', '*', ['app_reference' => $app_reference]);

        $get_cancellation_details_db = json_decode($booking_ite_details['data'][0]['cancellation_poicy'], true);

        $cancel_charge = $this->calculate_cancellation_charge($get_cancellation_details_db, $total_booking_amount);

        // Set the change request status based on cancellation charge
        $ChangeRequestId = ($cancel_charge > 0) ? 2 : $ChangeRequestId;

        // Prepare the cancellation details to be saved
        $get_change_request_status_response = [
            'StatusDescription' => $this->get_cancellation_status_description($ChangeRequestId),
            'ChangeRequestId' => $ChangeRequestId,
            'RefundedAmount' => abs($total_booking_amount - $cancel_charge),
            'CancellationCharge' => $cancel_charge,
        ];

        // Update cancellation details in the database
        $this->CI->car_model->update_cancellation_details($app_reference, ['CarChangeRequestStatusResult' => $get_change_request_status_response]);

        // Process refund if applicable
        if ($ChangeRequestId == 3) {
            $get_change_request_status_response['ChangeRequestStatus'] = $get_change_request_status_response['StatusDescription'];
            $response['data']['CancellationDetails'] = $this->CI->common_car->update_domain_cancellation_refund_details($get_change_request_status_response, $app_reference);
        } else {
            $get_change_request_status_response['ChangeRequestStatus'] = $get_change_request_status_response['StatusDescription'];
            $response['data']['CancellationDetails'] = $get_change_request_status_response;
        }

        return $response;
    }

    private function map_cancel_response_to_change_request_id(string $cancel_response): int
    {
        return match (strtolower($cancel_response)) {
            'cancelled', 'confirmed' => 3,
            'failed' => 2,
            'pending' => 1,
            default => 0,
        };
    }

    private function calculate_cancellation_charge(?array $cancellation_policy, float $total_booking_amount): float
    {
        if (empty($cancellation_policy)) {
            return round($total_booking_amount);
        }

        $tm_last_cancel_date = date('Y-m-d');
        foreach ($cancellation_policy as $policy) {
            if ($policy['Amount'] == 0) {
                $tm_last_cancel_date = date('Y-m-d', strtotime($policy['FromDate']));
            }
        }

        $current_date = date('Y-m-d');
        $cancel_charge = 0;

        if ($tm_last_cancel_date <= $current_date) {
            foreach ($cancellation_policy as $policy) {
                $db_from_date = date('Y-m-d', strtotime($policy['FromDate']));
                $db_to_date = date('Y-m-d', strtotime($policy['ToDate']));

                if ($current_date >= $db_from_date && $current_date <= $db_to_date) {
                    $cancel_charge = round($policy['Amount']);
                }
            }
        }

        return $cancel_charge;
    }
    public function admin_cancel_booking(array $request): array
    {
        // Initialize the response
        $response = [
            'status' => FAILURE_STATUS, // Status of Operation
            'message' => '', // Message to be returned
            'data' => [], // Data to be returned
        ];

        // Validate AppReference
        $app_reference = trim($request['AppReference'] ?? '');

        if (empty($app_reference)) {
            $response['message'] = 'Invalid Request';
            return $response;
        }

        // Retrieve booking and customer details
        $booking_details = $this->CI->custom_db->single_table_records('car_booking_details', '*', ['app_reference' => $app_reference]);
        $customer_details = $this->CI->custom_db->single_table_records('car_booking_pax_details', '*', ['app_reference' => $app_reference]);

        // Check if booking exists and is confirmed
        if ($booking_details['status'] == SUCCESS_STATUS && $booking_details['data'][0]['status'] == 'BOOKING_CONFIRMED') {
            $booking_details = $booking_details['data'][0];
            $request_params = [
                'booking_reference' => $booking_details['booking_reference'],
                'last_name' => $customer_details['data'][0]['last_name'] ?? '',
            ];

            // Get itinerary details
            $booking_ite_details = $this->CI->custom_db->single_table_records('car_booking_itinerary_details', '*', ['app_reference' => $app_reference]);

            // Send change request to the supplier
            $send_change_request_response = $this->send_change_request($request_params);

            // Check if the change request was successful
            if ($send_change_request_response['status'] == SUCCESS_STATUS) {
                $cancel_response = strtolower($send_change_request_response['data']['cancel_status']);
                $ChangeRequestId = match ($cancel_response) {
                    'cancelled', 'confirmed' => 3,
                    'failed' => 2,
                    'pending' => 1,
                    default => 0,
                };

                $total_booking_amount = $booking_details['total_fare'] + $booking_details['domain_markup'] + $booking_details['domain_gst'];
                $response['status'] = SUCCESS_STATUS;

                // Calculate cancellation charge
                $get_cancellation_details_db = json_decode($booking_ite_details['data'][0]['cancellation_poicy'], true);
                $cancel_charge = 0;
                if (!empty($get_cancellation_details_db)) {
                    $tm_last_cancel_date = date('Y-m-d');
                    foreach ($get_cancellation_details_db as $l_value) {
                        if ($l_value['Amount'] == 0) {
                            $tm_last_cancel_date = date('Y-m-d', strtotime($l_value['FromDate']));
                        }
                    }

                    $current_date = date('Y-m-d');
                    if ($tm_last_cancel_date <= $current_date) {
                        foreach ($get_cancellation_details_db as $c_value) {
                            $db_from_date = date('Y-m-d', strtotime($c_value['FromDate']));
                            $db_to_date = date('Y-m-d', strtotime($c_value['ToDate']));

                            if ($current_date >= $db_from_date && $current_date <= $db_to_date) {
                                $cancel_charge = round($c_value['Amount']);
                            }
                        }
                    }
                } else {
                    $cancel_charge = round($total_booking_amount);
                }

                if ($cancel_charge > 0) {
                    $ChangeRequestId = 2;
                }

                // Set the cancellation details response
                $get_change_request_status_response['StatusDescription'] = $this->get_cancellation_status_description($ChangeRequestId);
                $price = abs($total_booking_amount - $cancel_charge);
                $cancellation_details = [
                    'CarChangeRequestStatusResult' => [
                        'RefundedAmount' => $price,
                        'CancellationCharge' => $cancel_charge,
                        'ChangeRequestId' => $ChangeRequestId,
                        'ChangeRequestStatus' => $ChangeRequestId,
                        'StatusDescription' => $get_change_request_status_response['StatusDescription'],
                    ],
                ];

                // Update cancellation details in the database
                $this->CI->car_model->update_cancellation_details($app_reference, $cancellation_details);

                // Process the refund to client if applicable
                $get_change_request_status_response['ChangeRequestStatus'] = $get_change_request_status_response['StatusDescription'];
                $response['data']['CancellationDetails'] = $ChangeRequestId == 3
                    ? $this->CI->common_car->update_domain_cancellation_refund_details($get_change_request_status_response, $app_reference)
                    : $get_change_request_status_response;
            }
        } elseif ($booking_details['status'] == SUCCESS_STATUS && $booking_details['data'][0]['status'] == 'BOOKING_CANCELLED') {
            // Handle already cancelled booking
            $booking_details = $booking_details['data'][0];
            $app_reference = $booking_details['app_reference'];

            $get_cancellation_details = $this->CI->custom_db->single_table_records('car_cancellation_details', '*', ['app_reference' => $app_reference]);
            if ($get_cancellation_details['status']) {
                $cancel_details = $get_cancellation_details['data'][0];
                $response['status'] = SUCCESS_STATUS;
                $cancel_details_data = [
                    'ChangeRequestId' => $cancel_details['ChangeRequestId'],
                    'ChangeRequestStatus' => $cancel_details['ChangeRequestStatus'],
                    'RefundedAmount' => $cancel_details['refund_amount'],
                    'CancellationCharge' => $cancel_details['cancellation_charge'],
                    'StatusDescription' => $cancel_details['refund_status'],
                ];
                $response['data']['CancellationDetails'] = $cancel_details_data;
                $response['message'] = 'Booking Already Cancelled';
            } else {
                $response['message'] = 'Invalid Request';
            }
        } else {
            $response['message'] = 'Invalid Request';
        }
        return $response;
    }
    private function send_change_request(array $request_params): array
    {
        $response = [
            'status' => FAILURE_STATUS, // Status of Operation
            'message' => '', // Message to be returned
            'data' => [], // Data to be returned
        ];

        // Send the cancel request
        $car_cancel_request = $this->cancel_request($request_params);
        
        if ($car_cancel_request['status'] == SUCCESS_STATUS) {
            // Process the cancel request response
            $car_cancel_response = $this->process_request(
                $car_cancel_request['data']['request'],
                $this->xml_header(),
                $car_cancel_request['data']['service_url'],
                $car_cancel_request['data']['remarks']
            );

            // Convert the XML response to an array
            $api_response = Converter::createArray($car_cancel_response);

            // Validate the response structure
            $cancel_status = $api_response['soap:Envelope']['soap:Body']['VehCancelResRS']['VehCancelRSCore']['@attributes']['CancelStatus'] ?? null;
            $errors = $api_response['soap:Envelope']['soap:Body']['VehCancelResRS']['Errors'] ?? null;

            if (isset($cancel_status) && $cancel_status == 'Cancelled' && empty($errors)) {
                $response['status'] = SUCCESS_STATUS;
                $response['data']['cancel_status'] = 'confirmed';
            } else {
                // Handle errors if present
                $error_message = $errors['Error']['@value'] ?? 'Cancellation Failed';

                $response['data']['cancel_status'] = 'failed';
                $response['message'] = $error_message;
            }
        }

        return $response;
    }

     /* create request for Car Cancel API */

    function cancel_request(array $request) {
        $response = [];
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
     * @param string $app_reference
     * @param string $booking_status
     */
    private function update_booking_status(string $app_reference, string $booking_status): void
    {
        $app_reference = trim($app_reference);
        $booking_status = trim($booking_status);

        if ($app_reference && $booking_status) {
            $update_condition = ['app_reference' => $app_reference];
            $update_data = ['status' => $booking_status];

            // Update master table status
            $this->CI->custom_db->update_record('car_booking_details', $update_data, $update_condition);
            // Update itinerary status
            $this->CI->custom_db->update_record('car_booking_itinerary_details', $update_data, $update_condition);
            // Update passenger status
            $this->CI->custom_db->update_record('car_booking_pax_details', $update_data, $update_condition);
        }
    }

    /**
     * Returns Cancellation status description
     * @param int $ChangeRequestStatus
     * @return string
     */
    private function get_cancellation_status_description(int $ChangeRequestStatus): string
    {
        return match ($ChangeRequestStatus) {
            1 => 'Pending',
            2 => 'InProgress',
            3 => 'Processed',
            4 => 'Rejected',
            default => 'NotSet',
        };
    }

    /**
     * Convert the Price
     * @param float $total_price
     * @param string $API_Currency
     * @return string
     */
    private function convert_INR_price(float $total_price, string $API_Currency): string
    {
        $conversion_amount = $GLOBALS['CI']->custom_db->single_table_records(
            'currency_detail',
            'value',
            ['f_currency' => $API_Currency, 't_currency' => 'INR']
        );

        $rate = $conversion_amount['data'][0]['value'] ?? 1; // Default to 1 if no conversion rate found.
        $total_price = number_format($total_price * $rate, 2, '.', '');
        return $total_price;
    }

    /**
     * Check if the search RS is valid or not
     * @param array $search_result
     * @return bool
     */
    private function valid_search_result(array $search_result): bool
    {
        return isset($search_result['soap:Envelope']['soap:Body']['VehAvailRateRS']['VehAvailRSCore']) &&
            valid_array($search_result['soap:Envelope']['soap:Body']['VehAvailRateRS']['VehAvailRSCore']) &&
            isset($search_result['soap:Envelope']['soap:Header']['informationHeader']['Successfully']);
    }

    /**
     * Process SOAP API request
     * @param string $request
     * @param string $url
     * @return array
     */
    public function form_curl_params(string $request, string $url): array
    {
        return [
            'status' => SUCCESS_STATUS,
            'message' => '',
            'data' => [
                'booking_source' => CARNECT_CAR_BOOKING_SOURCE,
                'request' => $request,
                'url' => $url,
                'header' => [
                    'X-Originating-Ip: 14.141.47.106',
                    "Content-type: text/xml;charset=utf-8",
                    'Accept: application/xml',
                    'Accept-Encoding: gzip'
                ]
            ]
        ];
    }

    /**
     * Process the SOAP request with curl
     * @param string $request
     * @param array $header
     * @param string $post_url
     * @param string $remarks
     * @return string
     */
    public function process_request(string $request, array $header, string $post_url, string $remarks): string
    {
        $insert_id = $this->CI->api_model->store_api_request($post_url, $request, $remarks);
        $insert_id = (int)($insert_id['insert_id'] ?? 0);

        // Initialize cURL
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

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            // Handle error (you could log this or throw an exception)
            $response = "cURL error: {$error_message}";
        }

        curl_close($ch);
        return $response;
    }

    /**
     * Header to be used for carnet
     * @return array
     */
    private function xml_header(): array
    {
        return [
            "Content-type: text/xml;charset=utf-8",
            'Accept: application/xml',
            'Accept-Encoding: gzip'
        ];
    }
}