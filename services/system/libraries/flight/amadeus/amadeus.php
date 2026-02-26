<?php
require_once BASEPATH . 'libraries/flight/Common_api_flight.php';

class Amadeus extends Common_Api_Flight {

    public string $search_hash;
    protected string $token;
    public string $api_session_id;

    function __construct() {
        parent::__construct(META_AIRLINE_COURSE, AMADEUS_FLIGHT_BOOKING_SOURCE);
        // TODO: Define property $CI explicitly for PHP 8.2
        $this->CI = &get_instance();
        $this->CI->load->library('Converter');
        $this->CI->load->library('ArrayToXML');
        $this->CI->load->model('custom_db');

        //$this->set_api_session_id();
        $this->set_api_credentials();
    }
    /**
     * Setting Api Credentials
    */
    private function set_api_credentials(): void
    {
        //debug($this->config);exit;
        // TODO: Define property $wsap explicitly for PHP 8.2
$this->wsap = trim($this->config['WSAP']);
        // TODO: Define property $api_url explicitly for PHP 8.2
$this->api_url = trim($this->config['Api_URL']);        
        // TODO: Define property $username explicitly for PHP 8.2
$this->username = trim($this->config['Username']);
        // TODO: Define property $password explicitly for PHP 8.2
$this->password = trim($this->config['Password']);
        // TODO: Define property $pos_type explicitly for PHP 8.2
$this->pos_type = trim($this->config['POS_Type']);
       // // TODO: Define property $pseudo_city_code explicitly for PHP 8.2
$this->pseudo_city_code = trim($this->config['PseudoCityCode']);
        // TODO: Define property $pseudo_city_code explicitly for PHP 8.2
$this->pseudo_city_code = trim("MELGU3101");        
        // TODO: Define property $agent_duty_code explicitly for PHP 8.2
$this->agent_duty_code = trim($this->config['AgentDutyCode']);
        // TODO: Define property $requestor_type explicitly for PHP 8.2
$this->requestor_type = trim($this->config['RequestorType']);
        // TODO: Define property $created explicitly for PHP 8.2
$this->created = $this->getCreateDate();
        // TODO: Define property $nonce explicitly for PHP 8.2
$this->nonce = $this->getNoncevalue();
        // TODO: Define property $hashPwd explicitly for PHP 8.2
$this->hashPwd = $this->DigestAlgo($this->password,$this->created,$this->nonce);
        // TODO: Define property $soap_url explicitly for PHP 8.2
$this->soap_url = 'http://webservices.amadeus.com/';
        // TODO: Define property $booking_source explicitly for PHP 8.2
$this->booking_source = AMADEUS_FLIGHT_BOOKING_SOURCE;
    }
     /**
     * Setting Session ID
     */
    public function set_api_session_id($auth_response = ''): void {

        if (empty($this->api_session_id) == true) {

            if (empty($auth_response) == false) {
               
                $auth_response = Converter::createArray($auth_response);
                //store in database
                if ($this->valid_create_session_response($auth_response)) {
                    $authenticate_token =$auth_response['soap-env:Envelope']['soap-env:Header']['wsse:Security']['wsse:BinarySecurityToken']['@value'];                    
                    $session_id = trim($authenticate_token);
                    $this->CI->api_model->update_api_session_id($this->booking_source, $session_id);
                }
            } else {

                $session_expiry_time = 2; //In minutes

                $session_id = $this->CI->api_model->get_api_session_id($this->booking_source, $session_expiry_time);

                if (empty($session_id) == true) {

                    $auth_request = $this->get_authentication_request(true);
                    if ($auth_request['status'] == SUCCESS_STATUS) {
                        $auth_request = $auth_request['data'];
                        $auth_response = $this->process_request($auth_request ['request'], $auth_request ['url'], $auth_request ['remarks']);
                        // debug($authentication_response);exit;
                        $this->set_api_session_id($auth_response);
                    }
                }
            }
            if (empty($session_id) == false) {
                $this->api_session_id = $session_id;
            }
        }
    }
    public function valid_create_session_response(array $response): array
    {   
        $result = false;
        if(isset($response['soap-env:Envelope']['soap-env:Header']['wsse:Security']['wsse:BinarySecurityToken']['@value']) ==true){
            $result =  true;
        }
        return $result;
    }

   public function search_data(int $search_id):array
    {
        $response = [
            'status' => true,
            'data' => [],
        ];

        if (!empty($this->master_search_data) || valid_array($this->master_search_data)) {
            $response['data'] = $this->master_search_data;
            $this->search_hash = md5(serialized_data($response['data']));
            return $response;
        }

        $clean_search_details = $this->CI->flight_model->get_safe_search_data($search_id);
        if ($clean_search_details['status'] != true) {
            $response['status'] = false;
            return $response;
        }

        $data = $clean_search_details['data'];

        if ($data['trip_type'] == 'multicity') {
            $data['from_city'] = $data['from'];
            $data['to_city'] = $data['to'];
            $data['depature'] = $data['depature'];
            $data['return'] = $data['depature'];
        } else {
            $data['from'] = substr(chop(substr($data['from'], -5), ')'), -3);
            $data['to'] = substr(chop(substr($data['to'], -5), ')'), -3);
            $data['depature'] = date("Y-m-d", strtotime($data['depature'])) . 'T00:00:00';
            if (isset($data['return'])) {
                $data['return'] = date("Y-m-d", strtotime($data['return'])) . 'T00:00:00';
            }
        }

        $tripType = $data['trip_type'];
        $data['type'] = match ($tripType) {
            'oneway' => 'OneWay',
            'circle' => 'Return',
            default => 'OneWay',
        };

        if ($tripType == 'circle') {
            $data['return'] = date("Y-m-d", strtotime($data['return'])) . 'T00:00:00';
        }

        $data['domestic_round_trip'] = ($data['is_domestic'] && $tripType == 'return');
        $data['adult'] = $data['adult_config'];
        $data['child'] = $data['child_config'];
        $data['infant'] = $data['infant_config'];
        $data['v_class'] = $data['v_class'] ?? '';
        $data['carrier'] = implode($data['carrier']);

        $this->master_search_data = $data;
        $response['data'] = $data;
        $this->search_hash = md5(serialized_data($data));

        return $response;
    }

    /**
     * Search Request
     */
    public function get_search_request(int $search_id): array
    {
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];

        $search_data = $this->search_data($search_id);

        if ($search_data['status'] == SUCCESS_STATUS) {
            $search_request = $this->search_request($search_data['data']);

            // Fix: Comparison should be '==', not '='
            if ($search_request['status'] == SUCCESS_STATUS) {
                $response['status'] = SUCCESS_STATUS;
                $curl_request = $this->form_curl_params(
                    $search_request['request'],
                    $search_request['url'],
                    $search_request['soap_url'] ?? ''
                );
                $response['data'] = $curl_request['data'] ?? [];
            }
        }

        return $response;
    }

     public function get_calendar_fare_request(array $search_data): array {
       
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];   
        $search_request = $this->search_flexible_request($search_data);
        if ($search_request ['status'] = SUCCESS_STATUS) {
            $response ['status'] = SUCCESS_STATUS;
            $curl_request = $this->form_curl_params($search_request ['request'], $search_request ['url'],$search_request['soap_url']);
            $response ['data'] = $curl_request['data'];
        }

      
        return $response;
    }

     private function search_flexible_request(array $search_data): array {
        $request = [] ;
        $search_params = $search_data;
        $search_params['adult_config'] = 1;
        $search_params['child_config'] = 0;
        $search_params['infant_config'] = 0;
        $search_params['flexible_dates'] = 'flexible_dates';

        $trip_type = $search_params['JourneyType'];
        $segref = '';
        $from =array();
        $to_loc =array();
        $depature = array();
      
        $corporate_code_text = '';
        $price_type_corporate = '';
        if(isset($search_params['corporate_codes'])){
            if($search_params['corporate_codes']){
                $price_type_corporate .='<priceType>RW</priceType>';
                $corporate_arr_code = $search_params['corporate_codes'];
                $corporate_code_text ='<corporate>
                    <corporateId>
                        <corporateQualifier>RW</corporateQualifier>';
                        foreach ($corporate_arr_code as $cv) {
                            $corporate_code_text .='<identity>'.$cv.'</identity>';
                        }
                $corporate_code_text .='</corporateId>
                </corporate>';

            }
        }

        if($trip_type !='multicity'){
            $depature[] = $search_params['Segments']['DepartureDate'];
            $from[] = $search_params['Segments']['Origin'];
            $to_loc[] = $search_params['Segments']['Destination'];  
            if($trip_type =='return'){
                $from[] =$search_params['Segments']['Destination']; 
                $to_loc [] =$search_params['Segments']['Origin'];
                $depature[] = $search_params['Segments']['ReturnDate'];
            }          
        }else{
            $depature= $search_params['Segments']['DepartureDate'];
            $from = $search_params['Segments']['Origin'];
            $to_loc = $search_params['Segments']['Destination'];
        }
        $seg_count = 1;
        foreach ($from as $key => $value) {
            $segref .='<itinerary>
                    <requestedSegmentRef>
                        <segRef>'.$seg_count.'</segRef>
                    </requestedSegmentRef>
                    <departureLocalization>
                        <departurePoint>                            
                            <locationId>'.$value.'</locationId>
                        </departurePoint>
                    </departureLocalization>
                    <arrivalLocalization>
                        <arrivalPointDetails>                           
                            <locationId>'.$to[$key].'</locationId>
                        </arrivalPointDetails>
                    </arrivalLocalization>
                    <timeDetails>
                        <firstDateTimeDetail>
                            <date>'.date('dmy',strtotime($depature[$key])).'</date>
                        </firstDateTimeDetail>';
            if(@$search_params['flexible_dates'] == 'flexible_dates'){
                $segref .='<rangeOfDate>
                                <rangeQualifier>C</rangeQualifier>
                                <dayInterval>3</dayInterval>
                            </rangeOfDate>';
            }
            $segref .='</timeDetails>
                </itinerary>';
            $seg_count++;
        }
        //only adult + child not infant
        $total_pax =$search_params['adult_config'] + $search_params['child_config'];
        $paxTag_reference = '';
        $paxTagCHD ='';
        $paxTagINF ='';
        $paxTag = $this->get_paxref_search_req($search_params['adult_config'],'ADT',1);
        $paxTagADT =$paxTag['paxtag'];      
        $paxTag_reference .='<paxReference>'.$paxTagADT.'</paxReference>';
        if($search_params['child_config']>0){
            $paxTagc = $this->get_paxref_search_req($search_params['child_config'],'CH',$paxTag['paxRef']);
            $paxTagCHD = $paxTagc['paxtag'];
            $paxTag_reference .='<paxReference>'.$paxTagCHD.'</paxReference>';
        }
        if($search_params['infant_config']>0){
            $paxTagi = $this->get_paxref_search_req($search_params['infant_config'],'INF',1);
            $paxTagINF = $paxTagi['paxtag'];
            $paxTag_reference .='<paxReference>'.$paxTagINF.'</paxReference>';
        }
        
        $class_code  = $this->get_search_class_code(strtolower($search_params['Segments']['CabinClass']));
        $cabin_text_value = '<cabinId><cabinQualifier>RC</cabinQualifier><cabin>' . $class_code . '</cabin></cabinId>';
        //$soapAction = "FMPTBQ_17_4_1A";// //FMPTBQ_17_4_1A
        $soapAction = "FMPCAQ_14_3_1A";
    $xml_query='
            <?xml version="1.0" encoding="UTF-8"?>           
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:typ="http://xml.amadeus.com/2010/06/Type">
            <soapenv:Header>
            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
            <link:TransactionFlowLink xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1"/>
            <oas:Security xmlns:oas="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <oas:UsernameToken oas1:Id="UsernameToken-1" xmlns:oas1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
            <oas:Username>'.$this->username.'</oas:Username>
            <oas:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'.$this->nonce.'</oas:Nonce>
            <oas:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">'.$this->hashPwd.'</oas:Password>
            <oas1:Created>'.$this->created.'</oas1:Created>
        </oas:UsernameToken>
        </oas:Security>
        <AMA_SecurityHostedUser xmlns="http://xml.amadeus.com/2010/06/Security_v1">
        <UserID AgentDutyCode="'.$this->agent_duty_code.'" RequestorType="'.$this->requestor_type.'" PseudoCityCode="'.$this->pseudo_city_code.'" POS_Type="'.$this->pos_type.'"/>
        </AMA_SecurityHostedUser>
        </soapenv:Header>
        <soapenv:Body>

        <Fare_MasterPricerCalendar xmlns="http://xml.amadeus.com/'.$soapAction.'"> ';
        $xml_query .=' <numberOfUnit>
                    <unitNumberDetail>
                        <numberOfUnits>'.$total_pax.'</numberOfUnits>
                        <typeOfUnit>PX</typeOfUnit>
                    </unitNumberDetail>
                   
                </numberOfUnit>';
        $xml_query .=$paxTag_reference;
        $xml_query.='<fareOptions>   
                        <pricingTickInfo>
                            <pricingTicketing>
                                  <priceType>RP</priceType>
                                  <priceType>RU</priceType>
                                  <priceType>TAC</priceType>
                                '.$price_type_corporate.'
                            </pricingTicketing>
                        </pricingTickInfo>
                        '.$corporate_code_text.'
                        
                    </fareOptions>';
        $xml_query.='<travelFlightInfo>
                        '.$cabin_text_value.'
                    </travelFlightInfo>';
        $xml_query .=$segref;
        $xml_query .='</Fare_MasterPricerCalendar>
        </soapenv:Body>
    </soapenv:Envelope>';
    //echo $xml_query;
    //exit; 
        $request ['request'] = $xml_query;
        $request ['url'] = $this->api_url;
        $request ['soap_url'] = $this->soap_url.$soapAction;
        $request ['status'] = SUCCESS_STATUS;
        return $request;
    }
     /**
     * Formates Search Request
     */
    private function search_request(array $search_data): array {
        $request = [];
        $search_params = $search_data;

        $trip_type = $search_params['trip_type'];
        $segref = '';
        $from =array();
        $to =array();
        $depature = array();
        $return = array();
      
        $corporate_code_text = '';
        $price_type_corporate = '';
        if(isset($search_params['corporate_codes'])){
            if($search_params['corporate_codes']){
                $price_type_corporate .='<priceType>RW</priceType>';
                $corporate_arr_code = $search_params['corporate_codes'];
                $corporate_code_text ='<corporate>
                    <corporateId>
                        <corporateQualifier>RW</corporateQualifier>';
                        foreach ($corporate_arr_code as $cv) {
                            $corporate_code_text .='<identity>'.$cv.'</identity>';
                        }
                $corporate_code_text .='</corporateId>
                </corporate>';

            }
        }

        if($trip_type !='multicity'){
            $depature[] = $search_params['depature'];
            $from[] = $search_params['from'];
            $to[] = $search_params['to'];  
            if($trip_type =='return'){
                $from[] =$search_params['to']; 
                $to [] =$search_params['from'];
                $depature[] = $search_params['return'];
            }          
        }else{
            $depature= $search_params['depature'];
            $from = $search_params['from'];
            $to = $search_params['to'];
        }
        $seg_count = 1;
        foreach ($from as $key => $value) {
            $segref .='<itinerary>
                    <requestedSegmentRef>
                        <segRef>'.$seg_count.'</segRef>
                    </requestedSegmentRef>
                    <departureLocalization>
                        <departurePoint>                            
                            <locationId>'.$value.'</locationId>
                        </departurePoint>
                    </departureLocalization>
                    <arrivalLocalization>
                        <arrivalPointDetails>                           
                            <locationId>'.$to[$key].'</locationId>
                        </arrivalPointDetails>
                    </arrivalLocalization>
                    <timeDetails>
                        <firstDateTimeDetail>
                            <date>'.date('dmy',strtotime($depature[$key])).'</date>
                        </firstDateTimeDetail>';
            if(@$search_params['flexible_dates'] == 'flexible_dates'){
                $segref .='<rangeOfDate>
                                <rangeQualifier>C</rangeQualifier>
                                <dayInterval>1</dayInterval>
                            </rangeOfDate>';
            }
            $segref .='</timeDetails>
                </itinerary>';
            $seg_count++;
        }
        //only adult + child not infant
        $total_pax =$search_params['adult_config'] + $search_params['child_config'];
        $paxTag_reference = '';
        $paxTagCHD ='';
        $paxTagINF ='';
        $paxTag = $this->get_paxref_search_req($search_params['adult_config'],'ADT',1);
        $paxTagADT =$paxTag['paxtag'];      
        $paxTag_reference .='<paxReference>'.$paxTagADT.'</paxReference>';
        if($search_params['child_config']>0){
            $paxTagc = $this->get_paxref_search_req($search_params['child_config'],'CH',$paxTag['paxRef']);
            $paxTagCHD = $paxTagc['paxtag'];
            $paxTag_reference .='<paxReference>'.$paxTagCHD.'</paxReference>';
        }
        if($search_params['infant_config']>0){
            $paxTagi = $this->get_paxref_search_req($search_params['infant_config'],'INF',1);
            $paxTagINF = $paxTagi['paxtag'];
            $paxTag_reference .='<paxReference>'.$paxTagINF.'</paxReference>';
        }
        
        $class_code  = $this->get_search_class_code(strtolower($search_params['cabin_class']));
        $cabin_text_value = '<cabinId><cabinQualifier>RC</cabinQualifier><cabin>' . $class_code . '</cabin></cabinId>';

        $soapAction = $this->config['Fare_MasterPricerTravelBoardSearch'];
       
    $xml_query='
            <?xml version="1.0" encoding="UTF-8"?>           
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:typ="http://xml.amadeus.com/2010/06/Type">
              <soapenv:Header>
            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
            <link:TransactionFlowLink xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1"/>
            <oas:Security xmlns:oas="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <oas:UsernameToken oas1:Id="UsernameToken-1" xmlns:oas1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
            <oas:Username>'.$this->username.'</oas:Username>
            <oas:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'.$this->nonce.'</oas:Nonce>
            <oas:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">'.$this->hashPwd.'</oas:Password>
            <oas1:Created>'.$this->created.'</oas1:Created>
        </oas:UsernameToken>
        </oas:Security>
        <AMA_SecurityHostedUser xmlns="http://xml.amadeus.com/2010/06/Security_v1">
        <UserID AgentDutyCode="'.$this->agent_duty_code.'" RequestorType="'.$this->requestor_type.'" PseudoCityCode="'.$this->pseudo_city_code.'" POS_Type="'.$this->pos_type.'"/>
        </AMA_SecurityHostedUser>
        </soapenv:Header>
        <soapenv:Body>
        <Fare_MasterPricerTravelBoardSearch xmlns="http://xml.amadeus.com/'.$soapAction.'"> ';
        $xml_query .=' <numberOfUnit>
                    <unitNumberDetail>
                        <numberOfUnits>'.$total_pax.'</numberOfUnits>
                        <typeOfUnit>PX</typeOfUnit>
                    </unitNumberDetail>
                    <unitNumberDetail>
                        <numberOfUnits>100</numberOfUnits>
                        <typeOfUnit>RC</typeOfUnit>
                    </unitNumberDetail>
                </numberOfUnit>';
        $xml_query .=$paxTag_reference;
        $xml_query.='<fareOptions>   
                        <pricingTickInfo>
                            <pricingTicketing>
                                <priceType>CUC</priceType>
                                  <priceType>RU</priceType>
                                  <priceType>RP</priceType>
                                  <priceType>TAC</priceType>
                                  <priceType>ET</priceType>
                                  <priceType>IFS</priceType>
                                '.$price_type_corporate.'
                            </pricingTicketing>
                        </pricingTickInfo>
                        '.$corporate_code_text.'
                        <conversionRate>
                            <conversionRateDetail>
                                <currency>AUD</currency>
                            </conversionRateDetail>
                        </conversionRate>
                    </fareOptions>';
        $xml_query.='<travelFlightInfo>
                        '.$cabin_text_value.'
                    </travelFlightInfo>';
        $xml_query .=$segref;
        $xml_query .='</Fare_MasterPricerTravelBoardSearch>
        </soapenv:Body>
    </soapenv:Envelope>';
    // echo $xml_query;
    // exit; 
        $request ['request'][0] = $xml_query;
        $request ['url'] = $this->api_url;
        $request ['soap_url'] = $this->soap_url.$soapAction;
        $request ['status'] = SUCCESS_STATUS;
        //debug($request);exit;

        return $request;
    }
    /**
     * Returns Flight List
     * @param unknown_type $search_id
     */
    public function get_flight_list(array $flight_raw_data, int $search_id): array {
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        
        $search_data = $this->search_data($search_id);
        $clean_format_data = array();
        $formatted_seach_result = array();
        if ($search_data ['status'] == SUCCESS_STATUS) {
             foreach($flight_raw_data as $k_trip => $v_trip){
                
                //foreach ($raw_data as $k_trip => $v_trip) {
                    $search_response_array = array();
                    $search_response = utf8_encode($v_trip);
                    $api_response = $this->xml2array($search_response);
                    if ($this->valid_search_result($api_response) == TRUE) {
                        $clean_format_data = $this->format_search_data_response($api_response, $search_data ['data']);
                        if ($clean_format_data) {
                            $response ['status'] = SUCCESS_STATUS;
                        } else {
                            $response ['status'] = FAILURE_STATUS;
                        }
                    } else {
                        $response ['status'] = FAILURE_STATUS;
                    }
                //}
            }
           
            if ($response ['status'] == SUCCESS_STATUS) {
                $response ['data'] = $clean_format_data;
            }
        } else {
            $response ['status'] = FAILURE_STATUS;
        }
        // debug($response);exit;
        return $response;
    }
    public function get_calendar_fare(array $flight_raw_data): array {
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        //debug($flight_raw_data);exit;
        if ($flight_raw_data) {
            //$api_response = Converter::createArray($flight_raw_data);
            $api_response = $this->xml2array($flight_raw_data);
            if ($this->valid_search_result($api_response) == TRUE) {
                $clean_format_data = $this->format__flexible_search_data_response($api_response);
                if ($clean_format_data) {
                    $response ['status'] = SUCCESS_STATUS;
                } else {
                    $response ['status'] = FAILURE_STATUS;
                }
            } else {
                $response ['status'] = FAILURE_STATUS;
            }

            if ($response ['status'] == SUCCESS_STATUS) {
                $response ['data'] = $clean_format_data;
            }
        } else {
            $response ['status'] = FAILURE_STATUS;
        }
       
        return $response;
    }
     /**
     * Formates Search Response
     * Enter description here ...
     * @param unknown_type $search_result
     * @param unknown_type $search_data
     */
    function format__flexible_search_data_response(array $search_result): array{
       
        $currency = $search_result['soapenv:Envelope']['soapenv:Body']['Fare_MasterPricerCalendarReply']['conversionRate']['conversionRateDetail']['currency'];
        $flight_list = $search_result['soapenv:Envelope']['soapenv:Body']['Fare_MasterPricerCalendarReply']['flightIndex'];
        if(!isset($flight_list[0])){
            $flight_list = array($flight_list);
        }
        $currency_conversion =  $this->CI->custom_db->single_table_records('domain_currency_converter','*', array('country' => $currency));
        $currency_conversion_rate = $currency_conversion['data'][0]['value'];
       
        $recommendation = $search_result['soapenv:Envelope']['soapenv:Body']['Fare_MasterPricerCalendarReply']['recommendation'];
        $fgrop = array();
        foreach($flight_list as $fl_key => $fl_val){
            $group_of_flights = $fl_val['groupOfFlights'];
            if(!isset($group_of_flights[0])){
                $group_of_flights =array($group_of_flights);
            }
            foreach($group_of_flights as $group_key => $group_val){
                $fgrop[$fl_key][$group_val['propFlightGrDetail']['flightProposal'][0]['ref']] = $group_val;
            }
        }
        if(!isset($recommendation[0])){
            $recommendation = array($recommendation);
        }
        $flight_list = array();
        $calendar_fare_details = array();

        $i=0;
        $date_key = array();
        foreach($recommendation as $recom_key => $recom_val){
            $price = 0;
            foreach($recom_val['recPriceInfo']['monetaryDetail'] as $pri_key => $pricing){
                $price += $pricing['amount'];
            }
            $pricing = array('Currency' => $currency, 'Price' => $price);
            if(!isset($recom_val['segmentFlightRef'][0])){
                $recom_val['segmentFlightRef'] = array($recom_val['segmentFlightRef']);
            }
            $fl_list = array();
            foreach($recom_val['segmentFlightRef'] as $seg_ref_key => $seg_ref_val){
                if(!isset($seg_ref_val['referencingDetail'][0])){
                    $seg_ref_val['referencingDetail'] = array($seg_ref_val['referencingDetail']);
                }
                $flist = array();
                foreach($seg_ref_val['referencingDetail'] as $key => $flight_val){
                    $flist[$key] = $fgrop[$key][$flight_val['refNumber']];
                }
                $flight_details = force_multple_data_format($flist[0]['flightDetails']);
                
                $departureDate = $flight_details[0]['flightInformation']['productDateTime']['dateOfDeparture'];
                $departureTime = date("H:i:s", strtotime($flight_details[0]['flightInformation']['productDateTime']['timeOfDeparture']));
                $departure_date =  ("20" .(substr("$departureDate", -2)) . "-" . (substr("$departureDate", -4, 2)) . "-".(substr("$departureDate", 0, -4)));
                $departure_time = ((substr("$departureTime", 0, -2)) . ":" . (substr("$departureTime", -2)));                 
                $DapartureDate = $departure_date.' '.$departure_time;
               $date_key[$i] = $departure_date;
               
                $flight_list[$i]['AirlineCode'] = $flight_details[0]['flightInformation']['companyId']['marketingCarrier'];
                $flight_list[$i]['AirlineName'] = $this->get_airline_name($flight_details[0]['flightInformation']['companyId']['marketingCarrier']);
                $flight_list[$i]['DepartureDate'] = $DapartureDate;
                
                $flight_list[$i]['Fare'] = $currency_conversion_rate*$pricing['Price'];
                $flight_list[$i]['Date'] = $departure_date;
                $flight_list[$i]['BaseFare'] = $currency_conversion_rate*$pricing['Price'];
                $flight_list[$i]['Tax'] = 0;
                $flight_list[$i]['OtherCharges'] = 0;
                $flight_list[$i]['FuelSurcharge'] = 0;
                $calendar_fare_details['Origin'] =$flight_details[0]['flightInformation']['location'][0]['locationId'];
                $calendar_fare_details['Destination'] = $flight_details[0]['flightInformation']['location'][1]['locationId'];
                $calendar_fare_details['TraceId'] = "";
                $calendar_fare_details['CalendarFareDetails'] = $flight_list;
              
                //$fl_list[$i]['flight'] = $flist;
                //$fl_list[$i]['price'] = $pricing;
                $i++;
            }
            //$flight_list = array_merge($flight_list, $calendar_fare_details);
        }
        $calenar_sort = $calendar_fare_details['CalendarFareDetails'];
        array_multisort($date_key, SORT_ASC, $calenar_sort);
        $calendar_fare_details['CalendarFareDetails'] = $calenar_sort;
        return $calendar_fare_details;
    }
    function format_search_data_response(array $search_result, array $search_data): array {
       
        if($search_data['trip_type']=='multicity'){
            $SearchOrigin = $search_data['from'][0];
            $SearchDestination = end($search_data['to']);
        }else{
            $SearchOrigin = $search_data['from'];
            $SearchDestination = $search_data['to'];
        }
        
        $trip_type = isset($search_data ['is_domestic']) && !empty($search_data ['is_domestic']) ? 'domestic' : 'international';
        $currency = $search_result['soapenv:Envelope']['soapenv:Body']['Fare_MasterPricerTravelBoardSearchReply']['conversionRate']['conversionRateDetail'][0]['currency'];
        $currency_conversion =  $this->CI->custom_db->single_table_records('domain_currency_converter','*', array('country' => $currency));
        $currency_conversion_rate = $currency_conversion['data'][0]['value'];
        //$currency_conversion_rate = 1;
        $Results = $search_result['soapenv:Envelope']['soapenv:Body']['Fare_MasterPricerTravelBoardSearchReply']['flightIndex'];
        $Session_id = $search_result['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];
        $SecurityToken = $search_result['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
        $SequenceNumber = $search_result['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
        $Recommedation = $search_result['soapenv:Envelope']['soapenv:Body']['Fare_MasterPricerTravelBoardSearchReply']['recommendation'];

        $ServiceFeesGrp = $search_result['soapenv:Envelope']['soapenv:Body']['Fare_MasterPricerTravelBoardSearchReply']['serviceFeesGrp'];
        $ServiceFeesGrp = force_multple_data_format($ServiceFeesGrp);

        $flightDetails = array();
        $Results = force_multple_data_format($Results);
        $Recommedation = force_multple_data_format($Recommedation);
        /*debug($Recommedation);
        exit;*/
        foreach ($Recommedation as $p => $rs_value) {
            if(isset($rs_value['itemNumber']['itemNumberId']['numberType'])){
                $price = $p;$flag = "MTK";
            }else{
                $price = 0;$flag = "Normal";
            }
            $segmentFlightRef = force_multple_data_format($rs_value['segmentFlightRef']);
            foreach ($segmentFlightRef as $sfr => $sfr_value) {
                $referencingDetail = force_multple_data_format($sfr_value['referencingDetail']);
                foreach($referencingDetail as $rd=>$rd_value ){
                    $refNumber              = $rd_value['refNumber']."-".$flag."-".$p;
                    $refNumberFlight        = $rd_value['refNumber'];
                    $refQualifier           = $rd_value['refQualifier'];
                    if(isset($rs_value['itemNumber']['itemNumberId']['numberType'])){
                        $flightDetails[$refNumber][$price]['PriceInfo']['MultiTicket']          = "Yes";
                        $flightDetails[$refNumber][$price]['PriceInfo']['MultiTicket_number']   = $rs_value['itemNumber']['itemNumberId']['number'];
                    }else{
                        $flightDetails[$refNumber][$price]['PriceInfo']['MultiTicket']          = "No";
                        $flightDetails[$refNumber][$price]['PriceInfo']['MultiTicket_number']   = $rs_value['itemNumber']['itemNumberId']['number'];
                    }
                    $flightDetails[$refNumber][$price]['PriceInfo']['refQualifier']             = $refQualifier;
                    $flightDetails[$refNumber][$price]['PriceInfo']['totalFareAmount']          = $rs_value['recPriceInfo']['monetaryDetail'][0]['amount'];
                    $flightDetails[$refNumber][$price]['PriceInfo']['totalTaxAmount']           = $rs_value['recPriceInfo']['monetaryDetail'][1]['amount'];
                    $paxFareProduct = force_multple_data_format($rs_value['paxFareProduct']);
                    //paxwise price                  
                    foreach ($paxFareProduct as $pfp => $pfp_value) {
                            $paxReference = array();

                            $paxReference = force_multple_data_format($paxFareProduct[$pfp]['paxReference']['traveller']);
                            $passengerType = $paxFareProduct[$pfp]['paxReference']['ptc'];
                            if($paxFareProduct[$pfp]['paxReference']['ptc']=='CNN' || $paxFareProduct[$pfp]['paxReference']['ptc']=='CH'){
                                $passengerType = 'CHD';
                            }
                         
                            for($pr = 0; $pr < (count($paxReference)); $pr++) {
                                $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare'][$passengerType]['PassengerCount']       = ($pr+1);
                            }
                            
                            $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare'][$passengerType]['BasePrice']         =$currency_conversion_rate*( $paxFareProduct[$pfp]['paxFareDetail']['totalFareAmount'] - $paxFareProduct[$pfp]['paxFareDetail']['totalTaxAmount']);

                            $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare'][$passengerType]['Tax'] = $currency_conversion_rate*$paxFareProduct[$pfp]['paxFareDetail']['totalTaxAmount'];
                            $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare'][$passengerType]['TotalPrice']  = $currency_conversion_rate*$paxFareProduct[$pfp]['paxFareDetail']['totalFareAmount'];

                            if(isset($paxFareProduct[$pfp]['paxFareDetail']['codeShareDetails']['transportStageQualifier'])){
                                $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare'][$passengerType]['transportStageQualifier'] = $paxFareProduct[$pfp]['paxFareDetail']['codeShareDetails']['transportStageQualifier'];
                            }else{
                                $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare'][$passengerType]['transportStageQualifier'] = '';
                            }
                            if(isset($paxFareProduct[$pfp]['paxFareDetail']['codeShareDetails']['company'])){
                                $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare'][$passengerType]['company']                 = $paxFareProduct[$pfp]['paxFareDetail']['codeShareDetails']['company'];
                            }else{
                                $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare'][$passengerType]['company']                 = '';
                            }
                              
                            $fare = array();
                            $fare = force_multple_data_format($paxFareProduct[$pfp]['fare']);

                            $last_ticketing_date = date('Y-m-d',strtotime("+10 days"));

                            for($fa = 0; $fa < (count($fare)); $fa++) {
                                $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare'][$passengerType]['fare'][$fa]['description'] = '';  
                                $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare'][$passengerType]['fare'][$fa]['textSubjectQualifier']   = $fare[$fa]['pricingMessage']['freeTextQualification']['textSubjectQualifier'];
                                $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare'][$passengerType]['fare'][$fa]['informationType']        = $fare[$fa]['pricingMessage']['freeTextQualification']['informationType'];
                                $description = array();

                                $description = force_multple_data_format($fare[$fa]['pricingMessage']['description']);
                                $flightDetails[$refNumber][$price]['PriceInfo']['fare'][$fa]['description'] = ''    ;
                                
                                for ($d = 0; $d < count($description); $d++) {
                                    if(isset($description[$d])){
                                        $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare'][$passengerType]['fare'][$fa]['description'] .= $description[$d] . " - ";
                                        if(strtoupper(trim($description[$d]))=='LAST TKT DTE'){
                                            $last_ticketing_date = $description[$d+1];
                                        }
                                    }
                                }
                            }
                           // $flightDetails[$refNumber][$price]['PriceInfo']['LAST_TICKET_DATE'] = $last_ticketing_date;
                            $flightDetails[$refNumber][$price]['PriceInfo']['PassengerFare']['ADT']['LAST_TICKET_DATE'] = $last_ticketing_date; 

                            $fareDetails = array();
                            $fareDetails = force_multple_data_format($paxFareProduct[$pfp]['fareDetails']);

                            for($fd = 0; $fd < (count($fareDetails)); $fd++) 
                            {
                                $flightDetails[$refNumber][$price]['PriceInfo']['fareDetails'][$fd]['flightMtkSegRef']              = $fareDetails[$fd]['segmentRef']['segRef'];
                                $flightDetails[$refNumber][$price]['PriceInfo']['fareDetails'][$fd]['designator']           = $fareDetails[$fd]['majCabin']['bookingClassDetails']['designator'];                                   
                                $groupOfFares = array();
                                $groupOfFares = force_multple_data_format($fareDetails[$fd]['groupOfFares']);
                            
                                for($gf = 0; $gf < (count($groupOfFares)); $gf++) 
                                {
                                    $flightDetails[$refNumber][$price]['PriceInfo']['fareDetails'][$fd]['rbd'][$gf]         = $groupOfFares[$gf]['productInformation']['cabinProduct']['rbd'];
                                    $flightDetails[$refNumber][$price]['PriceInfo']['fareDetails'][$fd]['cabin'][$gf]       = $groupOfFares[$gf]['productInformation']['cabinProduct']['cabin'];
                                    $flightDetails[$refNumber][$price]['PriceInfo']['fareDetails'][$fd]['avlStatus'][$gf]   = $groupOfFares[$gf]['productInformation']['cabinProduct']['avlStatus'];
                                    $flightDetails[$refNumber][$price]['PriceInfo']['fareDetails'][$fd]['breakPoint'][$gf]  = $groupOfFares[$gf]['productInformation']['breakPoint'];
                                    $flightDetails[$refNumber][$price]['PriceInfo']['fareDetails'][$fd]['fareType'][$gf]    = $groupOfFares[$gf]['productInformation']['fareProductDetail']['fareType'];

                                     $flightDetails[$refNumber][$price]['PriceInfo']['fareDetails'][$fd]['fareBasis'][$gf] = $groupOfFares[$gf]['productInformation']['fareProductDetail']['fareBasis'];

                                    if(isset($groupOfFares[$gf]['productInformation']['fareProductDetail']['fareBasis']['corporateId'])){
                                        if($groupOfFares[$gf]['productInformation']['fareProductDetail']['fareBasis']['corporateId']){

                                             $flightDetails[$refNumber][$price]['PriceInfo']['fareDetails'][$fd]['corporateId'][$gf] = $groupOfFares[$gf]['productInformation']['fareProductDetail']['corporateId'];

                                        }
                                    }


                                }
                            }
                        }
                }
            }
        }
        #debug($flightDetails);exit;
        $flight_list = array();
        $flightDetails1  =array();
        foreach ($Results as $result_k => $result_v) { 

            $flight_details  = $result_v['groupOfFlights'];
            
            $Flight_SegDetails_arr = array();
            foreach ($flight_details as $f_key => $f_value) {
            
                $FlightSegment = force_multple_data_format($f_value['flightDetails']);
                
                $Flight_SegDetails = array();
                $flight_id = $f_value['propFlightGrDetail']['flightProposal'][0]['ref'];
                foreach ($FlightSegment as $s_key => $s_value) {
                    
                    $seg_value = $s_value['flightInformation'];
                    //debug($seg_value);exit;
                    $Flight_SegDetails[$s_key]['Origin']['AirportCode'] = $seg_value['location'][0]['locationId'];
                    $Flight_SegDetails[$s_key]['Origin']['CityName'] =  $this->get_airport_city ($seg_value['location'][0]['locationId'] );
                    $Flight_SegDetails[$s_key]['Origin']['AirportName'] =  $this->get_airport_city ($seg_value['location'][0]['locationId'] ); 
                     $Flight_SegDetails[$s_key]['Origin']['AirportCity'] =  $this->get_airport_name ($seg_value['location'][0]['locationId'] ); 
                     $Flight_SegDetails[$s_key]['Origin']['Country'] =  $this->get_airport_country ($seg_value['location'][0]['locationId'] );  
                    $Flight_SegDetails[$s_key]['Origin']['Terminal'] = @$seg_value['location'][0]['terminal'];                   

                    $departureDate = $seg_value['productDateTime']['dateOfDeparture'];
                    $departureTime = $seg_value['productDateTime']['timeOfDeparture'];                    
                    $departure_date =  ((substr("$departureDate", 0, -4)) . "-" . (substr("$departureDate", -4, 2)) . "-20" . (substr("$departureDate", -2)));
                    $departure_time = ((substr("$departureTime", 0, -2)) . ":" . (substr("$departureTime", -2)));                 
                    $DapartureDate = $departure_date.' '.$departure_time;
                    
                    $d_time = date('H:i',strtotime($departureDate));
                    $Flight_SegDetails[$s_key]['Origin']['DateTime'] = date('Y-m-d H:i:s',strtotime($DapartureDate));
                    $Flight_SegDetails[$s_key]['Origin']['FDTV'] = strtotime($d_time);
                    $arrivalDate = $seg_value['productDateTime']['dateOfArrival'];
                    $arrivalTime = $seg_value['productDateTime']['timeOfArrival'];
                    $arrival_date =  ((substr("$arrivalDate", 0, -4)) . "-" . (substr("$arrivalDate", -4, 2)) . "-20" . (substr("$arrivalDate", -2)));
                    $arrival_time = ((substr("$arrivalTime", 0, -2)) . ":" . (substr("$arrivalTime", -2)));
                    $Flight_SegDetails[$s_key]['Destination']['AirportCode'] = $seg_value['location'][1]['locationId'];

                    $Flight_SegDetails[$s_key]['Destination']['CityName'] =  $this->get_airport_city ($seg_value['location'][1]['locationId'] );
                    $Flight_SegDetails[$s_key]['Destination']['AirportName'] =  $this->get_airport_city ($seg_value['location'][1]['locationId'] );
                     $Flight_SegDetails[$s_key]['Destination']['AirportCity'] =  $this->get_airport_name ($seg_value['location'][1]['locationId'] ); 
                    $Flight_SegDetails[$s_key]['Destination']['Country'] =  $this->get_airport_country ($seg_value['location'][1]['locationId'] );
                     $Flight_SegDetails[$s_key]['Destination']['Terminal'] = @$seg_value['location'][1]['terminal']; 
                    
                    $ArrivalDate = $arrival_date.' '.$arrival_time;
                    $a_time = date('H:i',strtotime($ArrivalDate));
                    $Flight_SegDetails[$s_key]['Destination']['DateTime'] = date('Y-m-d H:i:s',strtotime($ArrivalDate));
                    $Flight_SegDetails[$s_key]['Destination']['FATV'] = strtotime($a_time);                 
                    $Flight_SegDetails[$s_key]['OperatorCode'] = $seg_value['companyId']['marketingCarrier'];
                    $Flight_SegDetails[$s_key]['CabinClass'] = '';
                    $Flight_SegDetails[$s_key]['DisplayOperatorCode'] =  $seg_value['companyId']['marketingCarrier'];
                    $Flight_SegDetails[$s_key]['Duration'] = '';
                    $Flight_SegDetails[$s_key]['FlightNumber'] = $seg_value['flightOrtrainNumber'];
                    $Flight_SegDetails[$s_key]['OperatorName'] = $this->get_airline_name($seg_value['companyId']['marketingCarrier']);                   
                    $Flight_SegDetails[$s_key]['Attr']['AvailableSeats']= '';
                    $Flight_SegDetails[$s_key]['Attr']['Baggage']= '';
                    $Flight_SegDetails[$s_key]['Attr']['CabinBaggage']= '7 kg';
                    
                    $flightDetails1[$flight_id]['Details'][$result_k] = $Flight_SegDetails;
                }
            }
        }
        
        $x=0;
        foreach ($Recommedation as $p => $s) {
                if(isset($s['itemNumber']['itemNumberId']['numberType'])) { $price = $p;$flag = "MTK"; }else{ $price = 0;$flag = "Normal"; }
                $segmentFlightRef = array();
                $segmentFlightRef = force_multple_data_format($s['segmentFlightRef']);

                for ($sfr = 0; $sfr <  (count($segmentFlightRef)); $sfr++) {
                    $referencingDetail = array();
                    $referencingDetail = force_multple_data_format($segmentFlightRef[$sfr]['referencingDetail']);

                    for ($rd = 0; $rd < (count($referencingDetail)); $rd++) {
                        $refNumber              = $referencingDetail[$rd]['refNumber']."-".$flag."-".$p;
                        $refNumberFlight        = $referencingDetail[$rd]['refNumber'];
                        $refQualifier           = $referencingDetail[$rd]['refQualifier'];
                       
                       if(isset($flightDetails1[$refNumberFlight]['Details'][$rd])){
                            $FinalResult[$x]['FlightDetails']['Details'][$rd]  = $flightDetails1[$refNumberFlight]['Details'][$rd];
                        }                       
                        
                        if($refQualifier=='B'){                       
                            if($ServiceFeesGrp){
                                foreach ($ServiceFeesGrp as $s_key => $s_value) {
                                    
                                        if(isset($s_value['serviceTypeInfo']['carrierFeeDetails'])){
                                            if($s_value['serviceTypeInfo']['carrierFeeDetails']['type']=='FBA'){
                                                 $FreeBaggageAllowance = force_multple_data_format($s_value['freeBagAllowanceGrp']);
                                                $baggage_allowance_str = $this->format_baggage_info($FreeBaggageAllowance,$refNumberFlight);
                                                if($baggage_allowance_str){
                                                   
                                                   foreach ($FinalResult[$x]['FlightDetails']['Details'] as $s_key => $s_value) {

                                                       foreach ($s_value as $ss_key => $ss_value) {

                                                       $FinalResult[$x]['FlightDetails']['Details'][$s_key][$ss_key]['Attr']['Baggage'] = $baggage_allowance_str;

                                                       
                                                       }
                                                   }
                                                   
                                                }
                                            }
                                        }
                                }
                            }
                        } 
                    }   

                    
                    $priceDetailsfinal = array();
                    foreach($flightDetails[$refNumber] as $price)
                        $priceDetailsfinal[] = $price;
                              
                   foreach ($priceDetailsfinal[0]['PriceInfo']['fareDetails'] as $c_key => $c_value) {
                        foreach ($c_value['cabin'] as $cc_key => $cc_value) {
                            $FinalResult[$x]['FlightDetails']['Details'][$c_key][$cc_key]['CabinClass'] = $cc_value;
                             $FinalResult[$x]['FlightDetails']['Details'][$c_key][$cc_key]['Attr']['AvailableSeats']=$c_value['avlStatus'][$cc_key];
                         }
                   } 
                               
                    $FinalResult[$x]['Price']['PassengerBreakup']      = $priceDetailsfinal[0]['PriceInfo']['PassengerFare'];

                    $FinalResult[$x]['Price']['Currency'] = "INR";
                    $FinalResult[$x]['Price']['TotalDisplayFare'] =  $currency_conversion_rate*$priceDetailsfinal[0]['PriceInfo']['totalFareAmount'];
                    $FinalResult[$x]['Price']['PriceBreakup']['BasicFare'] =  $currency_conversion_rate*($priceDetailsfinal[0]['PriceInfo']['totalFareAmount']- $priceDetailsfinal[0]['PriceInfo']['totalTaxAmount']);
                    $FinalResult[$x]['Price']['PriceBreakup']['Tax'] = $currency_conversion_rate*$priceDetailsfinal[0]['PriceInfo']['totalTaxAmount'];
                    $FinalResult[$x]['Price']['PriceBreakup']['AgentCommission'] = 0;
                    $FinalResult[$x]['Price']['PriceBreakup']['AgentTdsOnCommision'] = 0;
                       
                    if (!isset($s['paxFareProduct'][0])) {
                        $paxFareProduct[0] = $s['paxFareProduct'];
                    } else {
                        $paxFareProduct = $s['paxFareProduct'];
                    }
                    $is_Refund_text = 'Non Refundable';

                    if(isset($paxFareProduct[0]['fare'][0]['pricingMessage']['description'])){
                        if(is_array($paxFareProduct[0]['fare'][0]['pricingMessage']['description'])){
                            $is_Refund_text = implode("",$paxFareProduct[0]['fare'][0]['pricingMessage']['description']);
                        }else{
                            $is_Refund_text = $paxFareProduct[0]['fare'][0]['pricingMessage']['description'];
                        }
                    }
                    if(strpos(strtolower($is_Refund_text),'non')==false){
                         $FinalResult[$x]['Attr']['IsRefundable'] = 1;
                    }else{
                         $FinalResult[$x]['Attr']['IsRefundable'] = 0;
                    }

                    $FinalResult[$x]['Attr']['AirlineRemark'] = $is_Refund_text;
                    $FinalResult[$x]['Attr']['FareDetails'] = $priceDetailsfinal[0]['PriceInfo']['fareDetails'];

                    $key = array();
                    $key['key'][$x]['booking_source'] = $this->booking_source;

                    $key['key'][$x]['ResultIndex'] = '';
                    $key['key'][$x]['IsLCC'] = '';                   
                    $key['key'][$x]['FareBreakdown'] = $priceDetailsfinal;
                    $key['key'][$x]['Session_id'] = $Session_id;
                    $key['key'][$x]['SecurityToken'] = $SecurityToken;
                    $key['key'][$x]['SequenceNumber'] = $SequenceNumber;
                    $key['key'][$x]['SearchOrigin'] = $SearchOrigin;
                    $key['key'][$x]['SearchDestination'] = $SearchDestination;

                  //  $FinalResult[$x]['paxFareProduct'] = $paxFareProduct;                    
                    $specificRecDetails ='';   
                   # debug($s['specificRecDetails']);
                     if (!empty($s['specificRecDetails'])){
                        if (!empty($s['specificRecDetails'][0]['specificProductDetails'][0])){
                            $specificRecDetails = @$s['specificRecDetails']['0']['specificProductDetails'][0]['fareContextDetails']['cnxContextDetails'][0]['fareCnxInfo']['contextDetails']['availabilityCnxType'];
                        } else {
                            $specificRecDetails = @$s['specificRecDetails']['0']['specificProductDetails']['fareContextDetails']['cnxContextDetails'][0]['fareCnxInfo']['contextDetails']['availabilityCnxType'];
                        }                       
                    }
                    $fareContextDetails = array();
                    if(isset($s['specificRecDetails'])){
                        $s['specificRecDetails'] = force_multple_data_format($s['specificRecDetails']);
                        if(isset($s['specificRecDetails'][0]['specificProductDetails'])){
                            $s['specificRecDetails'][0]['specificProductDetails'] = force_multple_data_format($s['specificRecDetails'][0]['specificProductDetails']);
                            if(isset($s['specificRecDetails'][0]['specificProductDetails'][0]['fareContextDetails'])){
                                $fareContextDetails =force_multple_data_format($s['specificRecDetails'][0]['specificProductDetails'][0]['fareContextDetails']);
                            }

                        }
                        
                    }
                    //debug($FinalResult);exit;
                    
                    $FinalResult[$x]['specificProductDetails'] = $fareContextDetails;
                    $FinalResult[$x]['specificRecDetails'] = $specificRecDetails;
                    $key['key'][$x]['FlightInfo'] = $FinalResult[$x];
                    $ResultToken = serialized_data($key['key']);
                    $FinalResult[$x]['ResultToken'] = $ResultToken;
                    $FinalResult[$x]['booking_source'] =$this->booking_source;
                    $x++;
                }
            }
            $response['FlightDataList']['JourneyList'][0] = $FinalResult;

        return $response;       

    }
     /*foramt baggage information*/
    private function format_baggage_info(array $FreeBaggageAllowance, int $flight_ref_num): string{
        $pck='';
        if($FreeBaggageAllowance){
            foreach ($FreeBaggageAllowance as $fb_key => $fb_value) {
                                                    
                if($fb_value['itemNumberInfo']['itemNumberDetails']['number']==$flight_ref_num){
                    $type_str= 'Pieces';
                    if(isset($fb_value['freeBagAllownceInfo']['baggageDetails']['unitQualifier'])){

                        if($fb_value['freeBagAllownceInfo']['baggageDetails']['unitQualifier']=='K'){
                            $type_str = 'Kg';

                        }elseif ($fb_value['freeBagAllownceInfo']['baggageDetails']['unitQualifier']=='L') {
                            $type_str = 'Pounds';
                        }
                    }
                    $q_code = $type_str;
                    if(isset($fb_value['freeBagAllownceInfo']['baggageDetails']['freeAllowance']['quantityCode'])){

                        if($fb_value['freeBagAllownceInfo']['baggageDetails']['freeAllowance']['quantityCode']=='W'){
                            $q_code=$type_str;
                        }elseif ($fb_value['freeBagAllownceInfo']['baggageDetails']['freeAllowance']['quantityCode']=='N') {
                           $q_code= 'Pieces';
                        }
                    }
                    $allowed_pck=1;
                    if(isset($fb_value['freeBagAllownceInfo']['baggageDetails']['freeAllowance'])){                       
                        $allowed_pck= $fb_value['freeBagAllownceInfo']['baggageDetails']['freeAllowance'];
                        $pck  = $allowed_pck.' '.$q_code;
                       
                    }

                }
            }   
        }
        return $pck;
    }  
     /**
     * Fare Rule
     * @param unknown_type $request
     */
    public function get_fare_rules(array $request, int $search_id): array {
       $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $fareResp1 = array();
        $fare_info_request = $this->fare_info_request($request, $search_id);
        $fare_info_response = $this->process_request($fare_info_request ['request'], $fare_info_request ['url'], $fare_info_request ['remarks'],$fare_info_request['soap_url']);
        //debug($fare_info_response);exit;
        //$fare_info_response = $this->CI->custom_db->get_static_response (15305);
        $fare_info_xml_data = array();
        if($fare_info_response){
            $fare_info_xml_data = $this->xml2array($fare_info_response);                    
        }
        if (isset($fare_info_xml_data['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId']) && isset($fare_info_xml_data['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken']) && isset($fare_info_xml_data['soapenv:Envelope']['soapenv:Body']['Fare_InformativeBestPricingWithoutPNRReply'])) {
            $SessionId = $fare_info_xml_data['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];
            $SecurityToken = $fare_info_xml_data['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
            $sequence_number        = $fare_informative_pricing_without_pnr_res['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'] + 1;
            $fare_rule_request = $this->fare_rule_request($SessionId,$SecurityToken, $sequence_number);
            $mini_fare_rule_request = $this->mini_fare_rule_request($SessionId,$SecurityToken, $sequence_number);
            $fare_rule_response = $this->process_request($fare_rule_request ['request'], $fare_rule_request ['url'], $fare_rule_request ['remarks'],$fare_rule_request['soap_url']);
            //$mini_fare_rule_response = $this->process_request($mini_fare_rule_request ['request'], $mini_fare_rule_request ['url'], $mini_fare_rule_request ['remarks'],$mini_fare_rule_request['soap_url']);
                    //$fare_rule_response = $this->CI->custom_db->get_static_response (15017);
            //debug($mini_fare_rule_response);exit;
                    $fare_rule_xml_data = array();
                    if($fare_rule_response){
                        $fare_rule_xml_data = $this->xml2array($fare_rule_response); 
                        $Rules = '';
                        if ($fare_rule_xml_data && isset($fare_rule_xml_data['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId']) && isset($fare_rule_xml_data['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken']) && isset($fare_rule_xml_data['soapenv:Envelope']['soapenv:Body']['Fare_CheckRulesReply'])) {
                                $SecuritySession = $fare_rule_xml_data['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];
                                $SecurityToken = $fare_rule_xml_data['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                                $fareRuleText1 = $fare_rule_xml_data['soapenv:Envelope']['soapenv:Body']['Fare_CheckRulesReply']['tariffInfo']['fareRuleText'];
                                $fareRule = array();
                                $r_c=count($fareRuleText1);
                                for($i=0;$i<$r_c;$i++){
                                    if(empty($fareRuleText1[$i]['freeText']) == false){
                                        $fareRule[] = $fareRuleText1[$i]['freeText'];
                                    }
                                }
                                $seg_key = 0;
                                if(valid_array($fareRule)){
                                    $fareResp1[$seg_key]['FareRules'] = implode(" ",$fareRule);
                                    $fareResp1[$seg_key]['Origin'] = $request['FlightInfo']['FlightDetails']['Details'][0][0]['Origin']['AirportCode'];
                                    $fareResp1[$seg_key]['Destination'] = $request['FlightInfo']['FlightDetails']['Details'][0][0]['Destination']['AirportCode'];
                                    $fareResp1[$seg_key]['Airline'] =  $request['FlightInfo']['FlightDetails']['Details'][0][0]['OperatorCode'];
                                }

                        }                  
                    }
                }
        if(valid_array($fareResp1)){
            $response ['data']['FareRuleDetail'] = $fareResp1;
            $response['status'] = SUCCESS_STATUS;
        }
        else{
            $response['status'] = FAILURE_STATUS;
        }
        return $response;
    }
    function security_signout_request(string $SecuritySession, string $seq, string $SecurityToken): array{
        $soapAction = "VLSSOQ_04_1_1A";
        $SequenceNumber = $seq +1;
        $xml_query ='<?xml version="1.0" encoding="UTF-8"?>           
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus
                .com/2010/06/Security_v1" xmlns:typ="http://xml.amadeus.com/2010/06/Type">
                <soapenv:Header>
                <awsse:Session TransactionStatusCode="End" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">     
                <awsse:SessionId>'.$SecuritySession.'</awsse:SessionId>
                <awsse:SequenceNumber>'.$SequenceNumber.'</awsse:SequenceNumber>
                <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
            </awsse:Session>
            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
            </soapenv:Header>
            <soapenv:Body>
            <Security_SignOut xmlns="http://xml.amadeus.com/'.$soapAction.'"></Security_SignOut>
            </soapenv:Body>
            </soapenv:Envelope>';
            $request ['request'] = $xml_query;
            $request ['url'] = $this->api_url;
            $request ['soap_url'] = $this->soap_url.$soapAction;
            $request ['remarks'] = 'SecuritySignout(Amadeus)';
            $request ['status'] = SUCCESS_STATUS;
            return $request;
    }
     /**
     * Forms the fare rule request
     * @param unknown_type $request
     */
    private function fare_info_request(array $flight_request, int $search_id): array {
        $search_data = $this->search_data($search_id);
        $adult_count = $search_data['data']['adult'];
        $child_count = $search_data['data']['child'];
        $infant_count = $search_data['data']['infant'];
            
        $request = array();
        $soapAction = "TIBNRQ_19_3_1A";
       
            $adult = '';
            $child = '';
            $infant = '';
            $adult .= '<passengersGroup>
                            <segmentRepetitionControl>
                                <segmentControlDetails>
                                    <quantity>1</quantity>
                                    <numberOfUnits>'.$adult_count.'</numberOfUnits>
                                </segmentControlDetails>
                            </segmentRepetitionControl>
                            <travellersID>';
                    for($a=1; $a<=$adult_count; $a++){
                    $adult .= '<travellerDetails>
                                    <measurementValue>'.$a.'</measurementValue>
                                </travellerDetails>';
                    }
                $adult .= '</travellersID>
                        </passengersGroup>';
            if($infant_count > 0){
                $infant .= '<passengersGroup>
                                <segmentRepetitionControl>
                                    <segmentControlDetails>
                                        <quantity>2</quantity>
                                        <numberOfUnits>'.$infant_count.'</numberOfUnits>
                                    </segmentControlDetails>
                                </segmentRepetitionControl>
                                <travellersID>';
                        for($b=1; $b<=$infant_count; $b++){
                    $infant .= '<travellerDetails>
                                    <measurementValue>'.$b.'</measurementValue>
                                </travellerDetails>';
                        }
                            
                    $infant .= '</travellersID>
                                <discountPtc>
                                    <valueQualifier>INF</valueQualifier>
                                    <fareDetails>
                                        <qualifier>766</qualifier>
                                    </fareDetails>
                                </discountPtc>
                            </passengersGroup>';
            }
            if($child_count > 0){
                $child .= '<passengersGroup>
                            <segmentRepetitionControl>
                                <segmentControlDetails>
                                    <quantity>3</quantity>
                                    <numberOfUnits>'.$child_count.'</numberOfUnits>
                                </segmentControlDetails>
                            </segmentRepetitionControl>
                            <travellersID>';
                for($c=1; $c<=$child_count; $c++){
                    $child .= '<travellerDetails>
                                    <measurementValue>3</measurementValue>
                                </travellerDetails>';
                }
                $child .= '</travellersID>
                            <discountPtc>
                                <valueQualifier>CH</valueQualifier>
                            </discountPtc>
                        </passengersGroup>';
            }

         $passengers_group = $adult.$infant.$child;
         $segment_group = '';
         $seg_key = 1;
        //debug($flight_details['FlightInfo']['Attr']);exit;
        foreach($flight_request['FlightInfo']['FlightDetails']['Details'] as $flight_details){
            foreach($flight_details as $params){
            $departdate = explode(" ",$params['Origin']['DateTime']);
            $departure_date = date("dmy", strtotime($departdate[0]));
            $departure_time = date("Hi", strtotime($departdate[1]));
            $arrivaldate = explode(" ",$params['Destination']['DateTime']);
            $arrival_date = date("dmy", strtotime($arrivaldate[0]));
            $arrival_time = date("Hi", strtotime($arrivaldate[1]));
            $attributes = $flight_request['FlightInfo']['Attr'];
            if(isset($params['Origin']['AirportCode'])){
                $borad_point_true_locationid = $params['Origin']['AirportCode'];
            }else{
                $borad_point_true_locationid = '';
            }
            
            if(isset($params['Destination']['AirportCode'])){
                $offpoint_true_locationid = $params['Destination']['AirportCode'];
            }else{
                $offpoint_true_locationid = '';
            }
            if(isset($params['OperatorCode'])){
                $marketing_company = $params['OperatorCode'];
            }else{
                $marketing_company = $params['DisplayOperatorCode'];
            }
            if(isset($params['FlightNumber'])){
                $filght_number = $params['FlightNumber'];
            }else{
                $filght_number ='';
            }
            
            if(isset($attributes['FareDetails'][0]['designator'])){
                $booking_class = $attributes['FareDetails'][0]['designator']; //M
            }else{
                $booking_class = 'M';    
            }
            
            $flight_indicator = 1;    
           
            if(isset($flight_details[0]['PriceInfo']['MultiTicket_number'])){
                $item_number = $flight_details[0]['PriceInfo']['MultiTicket_number'];
            }else{
                $item_number = '1';
            }
            $segment_group.= '<segmentGroup>
            <segmentInformation>
               <flightDate>
                  <departureDate>'.$departure_date.'</departureDate>
                  <departureTime>'.$departure_time.'</departureTime>
                  <arrivalDate>'.$arrival_date.'</arrivalDate>
                  <arrivalTime>'.$arrival_time.'</arrivalTime>
               </flightDate>
               <boardPointDetails>
                  <trueLocationId>'.$borad_point_true_locationid.'</trueLocationId>
               </boardPointDetails>
               <offpointDetails>
                  <trueLocationId>'.$offpoint_true_locationid.'</trueLocationId>
               </offpointDetails>
               <companyDetails>
                  <marketingCompany>'.$marketing_company.'</marketingCompany>
               </companyDetails>
               <flightIdentification>
                  <flightNumber>'.$filght_number.'</flightNumber>
                  <bookingClass>'.$booking_class.'</bookingClass>
               </flightIdentification>
               <flightTypeDetails>
                  <flightIndicator>'.$seg_key.'</flightIndicator>
               </flightTypeDetails>
               <itemNumber>'.$seg_key.'</itemNumber>
            </segmentInformation>
         </segmentGroup>';
         $seg_key++;
        }
    }
        
         $pricing_option_group= '<pricingOptionGroup>
                                        <pricingOptionKey>
                                            <pricingOptionKey>RP</pricingOptionKey>
                                        </pricingOptionKey>
                                    </pricingOptionGroup>
                                    <pricingOptionGroup>
                                        <pricingOptionKey>
                                            <pricingOptionKey>RU</pricingOptionKey>
                                        </pricingOptionKey>
                                    </pricingOptionGroup>';
       $xml_query='<?xml version="1.0" encoding="UTF-8"?>           
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:typ="http://xml.amadeus.com/2010/06/Type">
                <soapenv:Header>
                <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
                <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                <link:TransactionFlowLink xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1"/>
                <oas:Security xmlns:oas="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                <oas:UsernameToken oas1:Id="UsernameToken-1" xmlns:oas1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <oas:Username>'.$this->username.'</oas:Username>
                <oas:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'.$this->nonce.'</oas:Nonce>
                <oas:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">'.$this->hashPwd.'</oas:Password>
                <oas1:Created>'.$this->created.'</oas1:Created>
            </oas:UsernameToken>
            </oas:Security>
            <AMA_SecurityHostedUser xmlns="http://xml.amadeus.com/2010/06/Security_v1">
            <UserID AgentDutyCode="'.$this->agent_duty_code.'" RequestorType="'.$this->requestor_type.'" PseudoCityCode="'.$this->pseudo_city_code.'" POS_Type="'.$this->pos_type.'"/>
            </AMA_SecurityHostedUser>
            <awsse:Session TransactionStatusCode="Start" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3"/>
            </soapenv:Header>
            <soapenv:Body>
            <Fare_InformativeBestPricingWithoutPNR xmlns="http://xml.amadeus.com/TIBNRQ_19_3_1A" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            '.$passengers_group.'
            '.$segment_group.'
            '.$pricing_option_group.'
            </Fare_InformativeBestPricingWithoutPNR>
            </soapenv:Body>
        </soapenv:Envelope>';
    
        
        $request ['request'] = $xml_query;
        $request ['url'] = $this->api_url;
        $request ['soap_url'] = $this->soap_url.$soapAction;
        $request ['remarks'] = 'FareInfo(Amadeus)';
        $request ['status'] = SUCCESS_STATUS;
        return $request;
    }
     /**
     * Forms the fare rule request
     * @param unknown_type $request
     */
    private function mini_fare_rule_request(string $SessionId, string $SecurityToken, string $sequence_no): array {
        $soapAction ="TMRCRQ_11_1_1A";
        $securitysession = $SessionId;//'00CPZ6TDVC';
        $security_token = $SecurityToken;//'1A1S6ZYXWHDLI36RQOWIF8T160';
        
        $xml_query ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:typ="http://xml.amadeus.com/2010/06/Type">
                        <soapenv:Header>
                            <awsse:Session TransactionStatusCode="InSeries"
                                xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
                                <awsse:SessionId>'.$securitysession.'</awsse:SessionId>
                                <awsse:SequenceNumber>'.$sequence_no.'</awsse:SequenceNumber>
                                <awsse:SecurityToken>'.$security_token.'</awsse:SecurityToken> 
                            </awsse:Session>
                            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
                            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                        </soapenv:Header>
                        <soapenv:Body>
                           <MiniRule_GetFromPricing xmlns="http://xml.amadeus.com/'.$soapAction.'">
                              <fareRecommendationId>
                                <referenceType>FRN</referenceType>
                                <uniqueReference>ALL</uniqueReference>
                              </fareRecommendationId>
                            </MiniRule_GetFromPricing>
                        </soapenv:Body>
                    </soapenv:Envelope>';

       
        $request ['request'] = $xml_query;
        $request ['url'] = $this->api_url;
        $request ['soap_url'] = $this->soap_url.$soapAction;
        $request ['remarks'] = 'MiniFareRule(Amadeus)';
        $request ['status'] = SUCCESS_STATUS;
        // debug($request);exit;
        return $request;
    }
   
    /**
     * Forms the fare rule request
     * @param unknown_type $request
     */
    private function fare_rule_request(string $SessionId, string $SecurityToken, string $sequence_no): array {
        $soapAction ="FARQNQ_07_1_1A";
        $securitysession = $SessionId;//'00CPZ6TDVC';
        $security_token = $SecurityToken;//'1A1S6ZYXWHDLI36RQOWIF8T160';
        
        $xml_query ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                        <soapenv:Header>
                            <awsse:Session TransactionStatusCode="InSeries"
                                xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
                                <awsse:SessionId>'.$securitysession.'</awsse:SessionId>
                                <awsse:SequenceNumber>'.$sequence_no.'</awsse:SequenceNumber>
                                <awsse:SecurityToken>'.$security_token.'</awsse:SecurityToken> 
                            </awsse:Session>
                            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
                            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                        </soapenv:Header>
                        <soapenv:Body>
                            <Fare_CheckRules>
                                 <msgType>
                                    <messageFunctionDetails>
                                       <messageFunction>712</messageFunction>
                                    </messageFunctionDetails>
                                 </msgType>
                                 <itemNumber>
                                    <itemNumberDetails>
                                       <number>1</number>
                                    </itemNumberDetails>
                                    <itemNumberDetails>
                                       <number>1</number>
                                       <type>FC</type>
                                    </itemNumberDetails>
                                 </itemNumber>
                                 <fareRule>
                                    <tarifFareRule>
                                       <ruleSectionId>16</ruleSectionId>
                                    </tarifFareRule>
                                 </fareRule>
                            </Fare_CheckRules>
                        </soapenv:Body>
                    </soapenv:Envelope>';

       
        $request ['request'] = $xml_query;
        $request ['url'] = $this->api_url;
        $request ['soap_url'] = $this->soap_url.$soapAction;
        $request ['remarks'] = 'FareRule(Amadeus)';
        $request ['status'] = SUCCESS_STATUS;
        // debug($request);exit;
        return $request;
    }
   
     /**
     * Update Fare Quote
     * @param unknown_type $request
     */

    public function get_update_fare_quote(array $fare_search_data, int $search_id): array {
       # debug($fare_search_data);exit;
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        #debug($fare_search_data);exit;
        if (valid_array($fare_search_data)) {
            
            if (valid_array($fare_search_data) == true && isset($fare_search_data['FlightInfo']['FlightDetails']) == true) {
                $response ['status'] = SUCCESS_STATUS;
                $response ['data']['FareQuoteDetails']['JourneyList'][0][0] = $fare_search_data['FlightInfo'];
                $response ['data']['FareQuoteDetails']['JourneyList'][0][0]['HoldTicket'] =1; 
                $result_token[0]['booking_source'] = $fare_search_data['booking_source'];                
                 $response ['data']['FareQuoteDetails']['JourneyList'][0][0]['ResultToken'] =serialized_data($result_token);

                //$response ['data']['FareQuoteDetails']['JourneyList'][0][0]['HoldTicket'] =; 

                

            } else {
                $response ['message'] = 'Not Available';
            }
        } else {
            $response ['status'] = FAILURE_STATUS;
        }
        #debug($response);exit;
        return $response;
    }
  
     /*
     *
     * get airport city based on airport code
     */
    private function get_airport_city(string $airport_code): string {
        $CI = & get_instance ();
        
        $airport_name = $CI->db_cache_api->get_airport_city_name ( array (
                'airport_code' => $airport_code 
        ) );
        $airport_name = @$airport_name ['airport_city'];
        return ($airport_name);
    }
     /*
     * get airport city based on airport code
     */
    private function get_airport_name(string $airport_code): string {
        $CI = & get_instance ();
        
        $airport_name = $CI->db_cache_api->get_airport_city_name ( array (
                'airport_code' => $airport_code 
        ) );
        $airport_name = @$airport_name ['airport_name'];
        return ($airport_name);
    }
     /*
     *
     * get airport city based on airport code
     */
    private function get_airport_country(string $airport_code): string {
        $CI = & get_instance ();
        
        $airport_name = $CI->db_cache_api->get_airport_city_name ( array (
                'airport_code' => $airport_code 
        ) );
        $airport_name = @$airport_name ['country'];
        return ($airport_name);
    }
     /*
     *
     * get airline name based on airport code
     */
    private function get_airline_name(string $airline_code): string {
        $CI = & get_instance ();
      # echo "hiee";exit;    
        $airline_data = $CI->db_cache_api->get_airline_name ( array (
                'code' => $airline_code 
        ) );
        $airline_name = ucfirst(strtolower(@$airline_data ['name']));
        return ($airline_name);
    }
     /**
     * check if the search RS is valid or not
     * @param array $search_result
     * search result RS to be validated
     */
    private function valid_search_result(array $search_result): bool {
       if(!isset($search_result['soapenv:Envelope']['soapenv:Body']['   Fare_MasterPricerTravelBoardSearchReply']['errorMessage'])){
            return true;
       }else{
            return false;
       }
    }
    public function get_search_class_code(string $class): string
    {
        
        if ($class == 'economy') {
            $economyCode = 'Y';
        }
        elseif ($class == 'premiumeconomy') {
            $economyCode = 'S';
        }
        elseif ($class == 'business') {
            $economyCode = 'C';
        }
        elseif ($class == 'premiumbusiness') {
            $economyCode = 'J';
        }
        elseif ($class == 'first') {
            $economyCode = 'F';
        }
        elseif ($class == 'premiumfirst') {
            $economyCode = 'P';
        }
        else{
            $economyCode = 'Y';
        }
        return $economyCode;
    }
     /**
     * Update markup currency for price object of flight
     * 
     * @param object $price_summary         
     * @param object $currency_obj          
     */
    function update_markup_currency(array &$price_summary, object &$currency_obj):void {
        
    }
    /**
     * get total price from summary object
     * 
     * @param object $price_summary         
     */
    function total_price(array $price_summary):array {
        
    }
     /**
     * Process booking
     * @param array $booking_params
     */
    function process_booking(array $booking_params, string $app_reference, int $sequence_number, int $search_id): array{
        
        // exit;
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $search_data = $this->search_data($search_id);
        
        $AirSellRecommdation = $this->AirSellRecommdation($booking_params,$search_data['data']);  
        $AirSellRecommdation['status']=SUCCESS_STATUS;
        $JourneyList = $booking_params['flight_data'];
        $Price = $booking_params['flight_data']['PriceBreakup'];
        if($AirSellRecommdation['status']==SUCCESS_STATUS){//segment confirm need to save passenger info in PNR AddElement option 0    
      // debug($AirSellRecommdation);exit;        
            $PNR_AddMultiElements_Option0 = $this->PNR_AddMultiElements_0($AirSellRecommdation['data'],$booking_params,array(),false,false);

            if($PNR_AddMultiElements_Option0['status']==SUCCESS_STATUS){
                //checking flight class availablity
                $flight_marketing_carrier = $booking_params['flight_data']['FlightDetails']['Details'][0][0]['OperatorCode'];
                $PNR_AddMultiElements_BookingClass = $this->PNR_AddMultiElements_BookingClass($PNR_AddMultiElements_Option0['data'],$flight_marketing_carrier);

                if($PNR_AddMultiElements_BookingClass['status']==SUCCESS_STATUS){
                    $Ticket_CreateTSTFromPricing = $this->Ticket_CreateTSTFromPricing($search_data['data'],$PNR_AddMultiElements_BookingClass['data']);
                    if($Ticket_CreateTSTFromPricing['status']==SUCCESS_STATUS){
                        //sending fop 
                        //$FOP = $this->FOP($booking_params,$flight_marketing_carrier,$Ticket_CreateTSTFromPricing['data']);
                        $FOP['status']=SUCCESS_STATUS;
                        if($FOP['status']==SUCCESS_STATUS){
                            $PNR_AddMultiElements_Option10 = $this->PNR_AddMultiElements_10($Ticket_CreateTSTFromPricing['data'],$booking_params,$search_data['data']);
                           //booking confirmed
                           if($PNR_AddMultiElements_Option10['status']==SUCCESS_STATUS){

                                $response ['status'] = SUCCESS_STATUS;
                                //if required need to run pnr retrieve
                                //placing in queue
                                //$Place_Queue = $this->Place_Queue($PNR_AddMultiElements_Option10['data']);
                                
                                //storing in database
                                $this->save_book_response_details($PNR_AddMultiElements_Option10['data']['Pnr_No'], $app_reference, $sequence_number);

                                $PNRRetrieve = $this->PNRRetrieve($PNR_AddMultiElements_Option10['data']);
                                sleep(10);// if we need direct ticketing need to wait 10 secondds
                                if($PNRRetrieve['status']==SUCCESS_STATUS){
                                    $service_pricing = $this->Service_IntegratedPricing($PNRRetrieve['data']);
                                    $ticket_create_tsm = $this->Ticket_CreateTSMFromPricing($service_pricing['data']);
                                    //this one will call if baggage added
                                    $bagagge_request = $this->baggagebookservice_request($ticket_create_tsm['data']);
                                    //SSR Meal and Seat adding
                                    $pnr_add_elements = $this->PNR_AddMultiElements_option11($bagagge_request['data']);
                                    
                                    $DocIssurance_Issue_Ticket = $this->DocIssuance_IssueTicket($pnr_add_elements['data']); 
                                    $this->PNR_AddMultiElements_final_option11($DocIssurance_Issue_Ticket['data'], $PNRRetrieve['data']['Pnr_No']);
                                      // Save Ticket Details
                                     $this->save_flight_ticket_details($booking_params,$PNRRetrieve['data']['Pnr_No'], $app_reference, $sequence_number, $search_id);
                                    $response['message'] = 'Ticket method called';
                                }

                           }
                        }
                    }
                }
            }

        }     

        return $response;

    }
    function baggagebookservice_request(array $header_data): array{
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $SessionId = $header_data['SessionId'];
        $SequenceNumber = $header_data['SequenceNumber'];
        $SecurityToken = $header_data['SecurityToken'];
        $pnr_no = $header_data['Pnr_No'];
        $soapAction = 'Service_BookPriceService_1.1';
        $soapHeaderFor_IssueTicket  = '<soapenv:Header>
            <awsse:Session TransactionStatusCode="InSeries" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
            <awsse:SessionId>'.$SessionId.'</awsse:SessionId>
            <awsse:SequenceNumber>'.$SequenceNumber.'</awsse:SequenceNumber>
            <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
            </awsse:Session>
            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
              <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/Service_BookPriceService_1.1</add:Action>
            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
            </soapenv:Header>';
            $baggagebookservice_request = '<?xml version="1.0" encoding="utf-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            '.$soapHeaderFor_IssueTicket.'
                <soapenv:Body>
                    <AMA_ServiceBookPriceServiceRQ Version="1.1">
                    <Product></Product>
                     </AMA_ServiceBookPriceServiceRQ>
            </soapenv:Body>
            </soapenv:Envelope>';
            $soap_url = $this->soap_url.$soapAction;
            $this->CI->custom_db->generate_static_response ($baggagebookservice_request,'Baggagebookservice Request' );
            $ticket_response = $this->process_request($baggagebookservice_request,$this->api_url,'Baggagebookservice(amadeus)',$soap_url);
            $this->CI->custom_db->generate_static_response ($ticket_response,'Amadeus Baggagebookservice Request' );
           if($ticket_response){
             $ticket_response = $this->xml2array($ticket_response);
               if(isset($ticket_response['soapenv:Envelope']['soapenv:Header'])){
                     $SessionId = $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                    $SecurityToken = $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                    $SequenceNumber =  $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                    $SequenceNumber = ($SequenceNumber+1);
                    $response['status'] = SUCCESS_STATUS;
               }

                $data['SessionId'] = $SessionId;
                $data['SecurityToken'] = $SecurityToken;
                $data['SequenceNumber'] = $SequenceNumber;
                $data['Pnr_No'] = $pnr_no;
                $response['data'] = $data;

                // $PNRRetrieve = $this->PNRRetrieve($data);

                //$this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
           }

        return $response;
    }
    /*Booking Procedure starts*/
    private function AirSellRecommdation(array $booking_params, array $search_data): array{
         $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];

        if(valid_array($booking_params['flight_data']['FlightDetails']['Details'])){
            $quantity = $search_data['adult_config']+$search_data['child_config'];
            //debug($booking_params['flight_data']['specificProductDetails']);
           # debug($booking_params['flight_data']);
            $specificRecDetails = $booking_params['flight_data']['specificProductDetails'];
            $slice_dice_seg = array();
           //debug($specificRecDetails);
            foreach ($specificRecDetails as $sr_key => $sr_value) {
                   $slice_dice_seg[$sr_value['requestedSegmentInfo']['segRef']] = $sr_value['cnxContextDetails'];

            }
            
            
            $ite_query ='';
            
            foreach ($booking_params['flight_data']['FlightDetails']['Details'] as $s_key => $s_value) { 
               // echo "s_key...".$s_key;
                $Origin = $s_value[0]['Origin']['AirportCode'];
                $Destination_details = end($s_value);
                $Destination = $Destination_details['Destination']['AirportCode'];
                    $ite_query .='<itineraryDetails><originDestinationDetails>
                                    <origin>'.$Origin.'</origin>
                                    <destination>'.$Destination.'</destination>
                                </originDestinationDetails>
                                <message>
                                    <messageFunctionDetails>
                                        <messageFunction>183</messageFunction>
                                    </messageFunctionDetails>
                                </message>';
                    //subsegment
                   $subsegment ='';
                   $slice_dice_seg_val = array();
                   if(isset($slice_dice_seg[$s_key+1])){
                     $slice_dice_seg_val = $slice_dice_seg[$s_key+1];  
                   }
                  
                   // debug($slice_dice_seg_val);
                   // echo "===";
                   foreach ($s_value as $ss_key => $ss_value) {
                        $flight_identification ='';
                        if($slice_dice_seg_val){
                            if(isset($slice_dice_seg_val[$ss_key]['fareCnxInfo']['contextDetails']['availabilityCnxType'])){

                                 $flight_identification  = ' <flightTypeDetails><flightIndicator>'.$slice_dice_seg_val[$ss_key]['fareCnxInfo']['contextDetails']['availabilityCnxType'].'</flightIndicator></flightTypeDetails>';
                            }
                        }

                       $subsegment .='<segmentInformation>
                                        <travelProductInformation>
                                            <flightDate>
                                                <departureDate>'.date('dmy',strtotime($ss_value['Origin']['DateTime'])).'</departureDate>
                                            </flightDate>
                                            <boardPointDetails>
                                                <trueLocationId>'.$ss_value['Origin']['AirportCode'].'</trueLocationId>
                                            </boardPointDetails>
                                            <offpointDetails>
                                                <trueLocationId>'.$ss_value['Destination']['AirportCode'].'</trueLocationId>
                                            </offpointDetails>
                                            <companyDetails>
                                                <marketingCompany>'.$ss_value['OperatorCode'].'</marketingCompany>
                                            </companyDetails>
                                            <flightIdentification>
                                                <flightNumber>'.$ss_value['FlightNumber'].'</flightNumber>
                                                <bookingClass>'.$ss_value['CabinClass'].'</bookingClass>
                                            </flightIdentification>
                                           '.$flight_identification.'
                                        </travelProductInformation>
                                        <relatedproductInformation>
                                            <quantity>'.$quantity.'</quantity>
                                            <statusCode>NN</statusCode>
                                        </relatedproductInformation>
                                    </segmentInformation>';
                   }

                 $ite_query .=$subsegment.'</itineraryDetails>';
                  
            }
            $soapAction = "ITAREQ_05_2_IA";
            //$soapAction = $this->config['ITAREQ_05_2_IA'];
            $SellRequest = '<?xml version="1.0" encoding="UTF-8"?>           
                    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:typ="http://xml.amadeus.com/2010/06/Type">
                    <soapenv:Header>
                    <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
                    <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/ITAREQ_05_2_IA</add:Action>
                    <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                    <link:TransactionFlowLink xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1"/>
                    <oas:Security xmlns:oas="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                    <oas:UsernameToken oas1:Id="UsernameToken-1" xmlns:oas1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                    <oas:Username>'.$this->username.'</oas:Username>
                    <oas:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'.$this->nonce.'</oas:Nonce>
                    <oas:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">'.$this->hashPwd.'</oas:Password>
                    <oas1:Created>'.$this->created.'</oas1:Created>
                    </oas:UsernameToken>
                    </oas:Security>
                    <AMA_SecurityHostedUser xmlns="http://xml.amadeus.com/2010/06/Security_v1">
                    <UserID AgentDutyCode="'.$this->agent_duty_code.'" RequestorType="'.$this->requestor_type.'" PseudoCityCode="'.$this->pseudo_city_code.'" POS_Type="'.$this->pos_type.'"/>
                    </AMA_SecurityHostedUser>
                   <awsse:Session xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3" TransactionStatusCode="Start" />
                    </soapenv:Header>
                    <soapenv:Body>
                    <itar:Air_SellFromRecommendation xmlns:itar="http://xml.amadeus.com/'.$soapAction.'">
                    <itar:messageActionDetails>
                    <itar:messageFunctionDetails>
                    <itar:messageFunction>183</itar:messageFunction>
                    <itar:additionalMessageFunction>M1</itar:additionalMessageFunction>
                    </itar:messageFunctionDetails>
                    </itar:messageActionDetails>
                    '.$ite_query.'
                    </itar:Air_SellFromRecommendation>
                    </soapenv:Body>
                    </soapenv:Envelope>';
            $api_url = $this->api_url;
            $soap_url = $this->soap_url.$soapAction;
            $remarks = 'AirSellRecommdation(Amadeus)'; 
            // echo $SellRequest;
            // echo "==="        ;
            $this->CI->custom_db->generate_static_response ($SellRequest,'Amadeus AirSellRecommdation Request' );
            $airsell_response = $this->process_request($SellRequest,$api_url,$remarks,$soap_url);
            $this->CI->custom_db->generate_static_response ($airsell_response,'Amadeus AirSellRecommdation Response' );
            // debug($airsell_response);
            // exit;
           // $airsell_response  = file_get_contents(FCPATH.'air_sel_res.xml');
            $Airsell_Response = array();
            if($airsell_response){
                $Airsell_Response = $this->xml2array($airsell_response);
                
                $SecuritySession=$Airsell_Response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];
               $SequenceNumber=$Airsell_Response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
               $SequenceNumber = ($SequenceNumber+1);
                $SecurityToken=$Airsell_Response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];                        
                if(isset($Airsell_Response['soapenv:Envelope']['soapenv:Body']['soap:Fault']) || isset($Airsell_Response['soapenv:Envelope']['soapenv:Body']['Fare_PricePNRWithBookingClassReply']['applicationError']) || isset($Airsell_Response['soap:Envelope']['soap:Body']['soap:Fault'])){

                   $this->Security_SignOut($SecuritySession,$SequenceNumber,$SecurityToken);
                }else{
                    //if not error checking the segment confirmation 
                    $segmentResult1 = array(); $segmentResult = array(); $status_msg = ""; $status_flag="";                  
                    $status = array();
                    if (isset($Airsell_Response['soapenv:Envelope']['soapenv:Body']['Air_SellFromRecommendationReply']['itineraryDetails'])) {
                        $segmentResult1 = $Airsell_Response['soapenv:Envelope']['soapenv:Body']['Air_SellFromRecommendationReply']['itineraryDetails'];
                        $segmentResult = force_multple_data_format($segmentResult1);
                        if(valid_array($segmentResult))
                        {
                            $flag = "TRUE";
                            for ($si = 0; $si < (count($segmentResult)); $si++) {
                                    $segmentInformation = array();
                                    if(!isset($segmentResult[$si]['segmentInformation'][0]))
                                        $segmentInformation[0] = $segmentResult[$si]['segmentInformation'];
                                    else
                                        $segmentInformation = $segmentResult[$si]['segmentInformation'];    
                                            
                                    for ($s = 0; $s < (count($segmentInformation)); $s++) {
                                    $statusCode = $segmentInformation[$s]['actionDetails']['statusCode'];

                                    if($statusCode == "OK" )
                                    {
                                        $status[$si][$s] = "Sold";
                                        if($flag == "TRUE")
                                            $status_flag = "true";
                                        else
                                            $status_flag = "false";
                                    }
                                    else if($statusCode== "UNS")
                                    {
                                        $status[$si][$s] = "Unable to sell";
                                        $status_flag = "false"; $flag = "FALSE";
                                    }
                                    else if($statusCode== "WL")
                                    {
                                        $status[$si][$s] = "Wait listed";
                                        $status_flag = "false"; $flag = "FALSE";
                                    }
                                    else if($statusCode== "X")
                                    {
                                        $status[$si][$s] = "Cancelled after a successful sell";
                                        $status_flag = "false"; $flag = "FALSE";
                                    }
                                    else if($statusCode== "RQ")
                                    {
                                        $status[$si][$s] = "Sell was not even attempted";
                                        $status_flag = "false"; $flag = "FALSE";
                                    } 
                                }
                            }//close the segment checking
                        }
                        if($status_flag=="false"){
                            //if any one of segment not confirm need to close the session
                            $this->Security_SignOut($SecuritySession,$SequenceNumber,$SecurityToken);
                        }elseif($status_flag=="true"){
                            //segment confirm proceed further
                            $response['status'] = SUCCESS_STATUS;
                            $response['data']['SessionId'] = $SecuritySession;
                            $response['data']['SecurityToken'] = $SecurityToken;
                            $response['data']['SequenceNumber'] = $SequenceNumber;
                        }
                    }

                }

            }
        }
       return $response;

    }
    private function format_passenger_data(array $booking_params): array
    {

        $passenger_details = $booking_params['Passengers']; 
        $booking_passenger_arr = array(); 
        $contact_email = '';
        $contact_no = '';
        $country_code  = '';
         //PaxType 1 -adult,2-child,3 -infant                    
        if(valid_array($passenger_details)){
            foreach ($passenger_details as $p_key => $p_value) {
                if($p_key==0){
                    $contact_email = $p_value['Email'];
                    $contact_no= $p_value['ContactNo'];
                    $country_code = $p_value['CountryCode'];
                }                
                if($p_value['PaxType']==1){
                    $booking_passenger_arr['ADT'][] = $p_value;
                }elseif($p_value['PaxType']==2){
                    $booking_passenger_arr['CHD'][] = $p_value;
                }elseif($p_value['PaxType']==3){
                    $booking_passenger_arr['INF'][] = $p_value;
                }
            }
        }
        return array('pax'=>$booking_passenger_arr,'contact_email'=>$contact_email,'contact_no'=>$contact_no,'country_code'=>$country_code);
        
    }
     /**
    *Saving the info in PNR
    *@param $counter - 0  saving the passenger data in PNR,10 status of PNR,11 adding the meal to the PNR 
    *@param $parent_pnr - PNR Number from counter 1    
    */

    private function PNR_AddMultiElements_0(array $header_data, array $booking_params, array $seat_info = array(), bool $reconfirm_meal=false, bool $is_seat_req=false):array{
        
         $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        //PaxType 1 -adult,2-child,3 -infant
        $booking_passenger_arr = array();
        $country_code_num = '+1';
        $last_ticketing_date = $booking_params['flight_data']['Price']['passenger_breakup']['ADT']['LAST_TICKET_DATE'];
        $format_pax_data= $this->format_passenger_data($booking_params);
        $booking_passenger_arr = $format_pax_data['pax'];
        $contact_email = $format_pax_data['contact_email'];
        $contact_no= $format_pax_data['contact_no'];
        $country_code = $format_pax_data['country_code'];
        $country_code_data  = $this->CI->custom_db->single_table_records('api_country_list','*',array('iso_country_code'=>$country_code));
        if($country_code_data['status']==1){
            $country_code_num = $country_code_data['data'][0]['country_code'];    
        }  

        $traveller_info = '';
        $pax_count = 1;
        $seat_pt_count =2;
        $seat_format_xml='';
        //appending adult + infant
        $inf_count_start =2;
        $commission_tag_count = 2;
        $commission_info='';
        foreach ($booking_passenger_arr['ADT'] as $key => $value) {
            //debug($value);exit;
            $traveller_info .='<travellerInfo>
                                <elementManagementPassenger>
                                    <reference>
                                        <qualifier>PR</qualifier>
                                        <number>'.$pax_count.'</number>
                                    </reference>
                                    <segmentName>NM</segmentName>
                                </elementManagementPassenger>';
            $commission_info .='<dataElementsIndiv>
                              <elementManagementData>
                                <reference>
                                  <qualifier>OT</qualifier>
                                  <number>'.$pax_count.'</number>
                                </reference>
                                <segmentName>FM</segmentName>
                              </elementManagementData>
                              <commission>
                                <passengerType>ADT</passengerType>
                                <commissionInfo>
                                  <percentage>5</percentage>
                                </commissionInfo>
                              </commission>
                              <referenceForDataElement>
                                <reference>
                                  <qualifier>PT</qualifier>
                                  <number>'.$commission_tag_count.'</number>
                                </reference>
                              </referenceForDataElement>
                            </dataElementsIndiv>';

            if(isset($booking_passenger_arr['INF'][$key])){
                 $commission_info .='<dataElementsIndiv>
                              <elementManagementData>
                                <reference>
                                  <qualifier>OT</qualifier>
                                  <number>'.$pax_count.'</number>
                                </reference>
                                <segmentName>FM</segmentName>
                              </elementManagementData>
                              <commission>
                                <passengerType>INF</passengerType>
                                <commissionInfo>
                                  <percentage>5</percentage>
                                </commissionInfo>
                              </commission>
                              <referenceForDataElement>
                                <reference>
                                  <qualifier>PT</qualifier>
                                  <number>'.$commission_tag_count.'</number>
                                </reference>
                              </referenceForDataElement>
                            </dataElementsIndiv>';

                $traveller_info .='<passengerData>
                                        <travellerInformation>
                                            <traveller>
                                                <surname>'.$value['LastName'].'</surname>
                                                <quantity>2</quantity>
                                            </traveller>
                                            <passenger>
                                                <firstName>'.$value['FirstName'].' '.$value['Title'].'</firstName>
                                                <type>ADT</type>
                                                <infantIndicator>3</infantIndicator>
                                            </passenger>
                                        </travellerInformation>
                                    </passengerData>';           
                $traveller_info .='<passengerData>
                                <travellerInformation>
                                    <traveller>
                                        <surname>'.$booking_passenger_arr['INF'][$key]['LastName'].'</surname>
                                    </traveller>
                                <passenger>
                                    <firstName>'.$booking_passenger_arr['INF'][$key]['FirstName'].' '.$booking_passenger_arr['INF'][$key]['Title'].'</firstName>
                                    <type>INF</type>
                                </passenger>
                                </travellerInformation>
                                <dateOfBirth>
                                    <dateAndTimeDetails> 
                                        <date>'.date('dMy',strtotime($booking_passenger_arr['INF'][$key]['DateOfBirth'])).'</date>
                                    </dateAndTimeDetails>
                                </dateOfBirth>
                            </passengerData>';

                $commission_tag_count = $commission_tag_count +2;
            }else{
                $traveller_info .='<passengerData>
                                    <travellerInformation>
                                        <traveller>
                                            <surname>'.$value['LastName'].'</surname>
                                            <quantity>1</quantity>
                                        </traveller>
                                        <passenger>
                                            <firstName>'.$value['FirstName'].' '.$value['Title'].'</firstName>
                                            <type>ADT</type>
                                        </passenger>
                                    </travellerInformation>
                                </passengerData>';
                $commission_tag_count++;
            }
                                
            $traveller_info .='</travellerInfo>';           
            $pax_count++;

            //checking seat request
            if(isset($value['SeatId'])){
                if(valid_array($value['SeatId'])){
                    foreach ($value['SeatId'] as $s_key => $s_value) {
                        $seat_data = Common_Flight::read_record($s_value);
                        $seat_data = json_decode($seat_data[0], true);
                        $SeatIddata = array_values(unserialized_data($seat_data['SeatId']));
                        $Segment_Type = $SeatIddata[0]['Segment_Type'];
                        $seat_format_xml.='<dataElementsIndiv>
                    <elementManagementData>
                        <segmentName>STR</segmentName>
                    </elementManagementData>
                    <seatGroup>
                        <seatRequest>
                            <seat>
                                <type>RQST</type>
                            </seat>
                            <special>
                                <data>'.$seat_data['SeatNumber'].'</data>
                            </special>
                        </seatRequest>
                    </seatGroup>
                    <referenceForDataElement>
                        <reference>
                            <qualifier>PT</qualifier>
                            <number>' .($seat_pt_count). '</number>
                        </reference>
                        <reference>
                            <qualifier>ST</qualifier>
                            <number>'.$Segment_Type.'</number>
                        </reference>
                    </referenceForDataElement>
                </dataElementsIndiv>';
                    }
                }                
            }

            if(isset($booking_passenger_arr['INF'][$key])){
                $seat_pt_count = $seat_pt_count+2;
            }else{
                $seat_pt_count =$seat_pt_count+1;
            }
            $inf_count_start++;
        }
       
        //creating child tag
        if(isset($booking_passenger_arr['CHD']) && valid_array($booking_passenger_arr['CHD'])){
            foreach ($booking_passenger_arr['CHD'] as $c_key => $c_value) {
                 $commission_info .='<dataElementsIndiv>
                              <elementManagementData>
                                <reference>
                                  <qualifier>OT</qualifier>
                                  <number>'.$pax_count.'</number>
                                </reference>
                                <segmentName>FM</segmentName>
                              </elementManagementData>
                              <commission>
                                <passengerType>CHD</passengerType>
                                <commissionInfo>
                                  <percentage>5</percentage>
                                </commissionInfo>
                              </commission>
                              <referenceForDataElement>
                                <reference>
                                  <qualifier>PT</qualifier>
                                  <number>'.$commission_tag_count.'</number>
                                </reference>
                              </referenceForDataElement>
                            </dataElementsIndiv>';

               $traveller_info .='<travellerInfo>
                                    <elementManagementPassenger>
                                        <reference>
                                            <qualifier>PR</qualifier>
                                            <number>'.$pax_count.'</number>
                                        </reference>
                                    <segmentName>NM</segmentName>
                                    </elementManagementPassenger>
                                    <passengerData>
                                        <travellerInformation>
                                            <traveller>
                                                <surname>'.$c_value['LastName'].'</surname>
                                            </traveller>
                                            <passenger>
                                                <firstName>'.$c_value['FirstName'].' '.$value['Title'].'</firstName>
                                                <type>CHD</type>
                                            </passenger>
                                        </travellerInformation>
                                        <dateOfBirth>
                                            <dateAndTimeDetails>  
                                                <date>'.date('dMy',strtotime($c_value['DateOfBirth'])).'</date>
                                            </dateAndTimeDetails>
                                        </dateOfBirth>
                                    </passengerData>
                                </travellerInfo>';


                  if(isset($c_value['SeatId'])){
                    if(valid_array($c_value['SeatId'])){
                        foreach ($c_value['SeatId'] as $sc_key => $sc_value) {
                                $seat_data = Common_Flight::read_record($sc_value);
                                $seat_data = json_decode($seat_data[0], true);
                                $SeatIddata = array_values(unserialized_data($seat_data['SeatId']));
                                $Segment_Type = $SeatIddata[0]['Segment_Type'];

                                $seat_format_xml.='<dataElementsIndiv>
                                <elementManagementData>
                                    <segmentName>STR</segmentName>
                                </elementManagementData>
                                <seatGroup>
                                    <seatRequest>
                                        <seat>
                                            <type>RQST</type>
                                        </seat>
                                        <special>
                                            <data>'.$seat_data['SeatNumber'].'</data>
                                        </special>
                                    </seatRequest>
                                </seatGroup>
                                <referenceForDataElement>
                                    <reference>
                                        <qualifier>PT</qualifier>
                                        <number>' .($seat_pt_count). '</number>
                                    </reference>
                                    <reference>
                                        <qualifier>ST</qualifier>
                                        <number>'.($Segment_Type).'</number>
                                    </reference>
                                </referenceForDataElement>
                            </dataElementsIndiv>';
                              $seat_pt_count++;
                        }
                    }
                        
                }               
                 $pax_count++;
                 $commission_tag_count++;
            }
            
            //$inf_count_start++;
        }
       

        if($traveller_info){
            //debug($header_data);exit;
            $SecuritySession = $header_data['SessionId'];
            $SequenceNumber = $header_data['SequenceNumber'];
            $SecurityToken = $header_data['SecurityToken'];
            $soapAction = 'PNRADD_14_1_1A';
            $PNR_AddMultiElements = '';
            $PNR_AddMultiElements.= '<?xml version="1.0" encoding="utf-8"?>
                        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                        <soapenv:Header>
                            <awsse:Session TransactionStatusCode="InSeries" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
                                <awsse:SessionId>' . $SecuritySession . '</awsse:SessionId>
                                <awsse:SequenceNumber>' . $SequenceNumber . '</awsse:SequenceNumber>
                                <awsse:SecurityToken>' . $SecurityToken . '</awsse:SecurityToken>
                            </awsse:Session>
                            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">' .$this-> getuuid() . '</add:MessageID>
                            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                        </soapenv:Header>
                        <soapenv:Body>
                            <PNR_AddMultiElements xmlns="http://xml.amadeus.com/'.$soapAction.'" >
                            ';
            $PNR_AddMultiElements.= '
                   <pnrActions>
                        <optionCode>0</optionCode>
                    </pnrActions>
                     '.$traveller_info;
            $PNR_AddMultiElements.= '
                    <dataElementsMaster>
                    <marker1/>
                    '.$seat_format_xml.'                   
                    <dataElementsIndiv>
                        <elementManagementData>
                            <reference>
                                <qualifier>OT</qualifier>
                                <number>1</number>
                            </reference>
                            <segmentName>RF</segmentName>
                        </elementManagementData>
                        <freetextData>
                            <freetextDetail>
                                <subjectQualifier>3</subjectQualifier>
                                <type>P23</type>
                            </freetextDetail>
                            <longFreetext>Example</longFreetext>
                        </freetextData>
                    </dataElementsIndiv>
                    <dataElementsIndiv>
                         <elementManagementData>
                            <segmentName>TK</segmentName>
                         </elementManagementData>
                         <ticketElement>
                            <ticket>
                               <indicator>TL</indicator>
                               <date>'.date('dmy',strtotime($last_ticketing_date)).'</date>
                            </ticket>
                         </ticketElement>
                    </dataElementsIndiv>
                    
                   <dataElementsIndiv>
                        <elementManagementData>
                            <segmentName>AP</segmentName>
                        </elementManagementData>
                        <freetextData>
                            <freetextDetail>
                                <subjectQualifier>3</subjectQualifier>
                                <type>P02</type>
                            </freetextDetail>
                            <longFreetext>' .$contact_email. '</longFreetext>
                        </freetextData>
                    </dataElementsIndiv>
                    '.$commission_info.'               
                    <dataElementsIndiv>
                        <elementManagementData>
                            <reference>
                                <qualifier>OT</qualifier>
                                <number>4</number>
                            </reference>
                            <segmentName>AP</segmentName>
                        </elementManagementData>
                        <freetextData>
                            <freetextDetail>
                                <subjectQualifier>3</subjectQualifier>
                                <type>S</type>
                            </freetextDetail>
                            <longFreetext>'.$country_code_num.$contact_no. '</longFreetext>
                        </freetextData>
                    </dataElementsIndiv>
                </dataElementsMaster>
            </PNR_AddMultiElements>
            </soapenv:Body>
            </soapenv:Envelope>';

            $api_url = $this->api_url;
            $soap_url = $this->soap_url.$soapAction;
            $remarks = 'PNR_AddMultiElements_Option0(amadeus)';
            $this->CI->custom_db->generate_static_response ($PNR_AddMultiElements,'Amadeus PNR_AddMultiElements_Option0 Request');           
            $PNR_AddElements_Response = $this->process_request($PNR_AddMultiElements,$api_url,$remarks,$soap_url);
           // debug($PNR_AddElements_Response);exit;
            $this->CI->custom_db->generate_static_response ($PNR_AddElements_Response,'Amadeus PNR_AddMultiElements_Option0 Response');

           // $PNR_AddElements_Response  = file_get_contents(FCPATH.'pnr_0.xml');
            $PNR_AddElements_Response_arr = array();
            if($PNR_AddElements_Response){
                $PNR_AddElements_Response_arr = $this->xml2array($PNR_AddElements_Response);

                $SessionId = $PNR_AddElements_Response_arr['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];
                $SecurityToken = $PNR_AddElements_Response_arr['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                $SequenceNumber = $PNR_AddElements_Response_arr['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                $SequenceNumber  = ($SequenceNumber +1);
               if(isset($PNR_AddElements_Response_arr['soapenv:Envelope']['soapenv:Body']['soap:Fault']) || isset($PNR_AddElements_Response_arr['soapenv:Envelope']['soapenv:Body']['PNR_Reply']['applicationError']) || isset($PNR_AddElements_Response_arr['soap:Envelope']['soap:Body']['soap:Fault'])){
                    $this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
               }elseif(isset($PNR_AddElements_Response_arr['soapenv:Envelope']['soapenv:Body']['PNR_Reply']['pnrHeader'])){

                //if success
                    $response['status'] = SUCCESS_STATUS;
                    $response['message'] = 'success';
                    $response['data']['SessionId'] =$SessionId;
                    $response['data']['SecurityToken'] =$SecurityToken;
                    $response['data']['SequenceNumber'] = $SequenceNumber;
               }
                
            }
        }
        return $response;

    }
    /*PNR_AddMultiElements_BookingClass checking flight marketing carrier 9w*/
    private function PNR_AddMultiElements_BookingClass(array $header_data, string $carrier): array{
         $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
     
        $soapAction = 'TPCBRQ_14_1_1A';
        $SessionId = $header_data['SessionId'];
        $SecurityToken = $header_data['SecurityToken'];
        $SequenceNumber = $header_data['SequenceNumber'];
        $xml_query='
            <?xml version="1.0" encoding="utf-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                <soapenv:Header>
                <awsse:Session TransactionStatusCode="InSeries" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
                <awsse:SessionId>'.trim($SessionId).'</awsse:SessionId>
                <awsse:SequenceNumber>'.trim($header_data['SequenceNumber']).'</awsse:SequenceNumber>
                <awsse:SecurityToken>'.trim($header_data['SecurityToken']).'</awsse:SecurityToken>
            </awsse:Session>
            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
            </soapenv:Header>
            <soapenv:Body>
            <tpc:Fare_PricePNRWithBookingClass xmlns:tpc="http://xml.amadeus.com/'.$soapAction.'" >
                <tpc:pricingOptionGroup>
                    <tpc:pricingOptionKey>
                        <tpc:pricingOptionKey>RP</tpc:pricingOptionKey>
                    </tpc:pricingOptionKey>
                </tpc:pricingOptionGroup>
                <tpc:pricingOptionGroup>
                    <tpc:pricingOptionKey>
                        <tpc:pricingOptionKey>RU</tpc:pricingOptionKey>
                    </tpc:pricingOptionKey>
                </tpc:pricingOptionGroup>
                <tpc:pricingOptionGroup>
                <tpc:pricingOptionKey>
                <tpc:pricingOptionKey>VC</tpc:pricingOptionKey>
                </tpc:pricingOptionKey>
                <tpc:carrierInformation>
                <tpc:companyIdentification>
                <tpc:otherCompany>'.$carrier.'</tpc:otherCompany>
                </tpc:companyIdentification>
                </tpc:carrierInformation>
                </tpc:pricingOptionGroup>
             <tpc:pricingOptionGroup>
            <tpc:pricingOptionKey>
            <tpc:pricingOptionKey>FCO</tpc:pricingOptionKey>
            </tpc:pricingOptionKey>
            <tpc:currency>
            <tpc:firstCurrencyDetails>
            <tpc:currencyQualifier>FCO</tpc:currencyQualifier>
            <tpc:currencyIsoCode>USD</tpc:currencyIsoCode>
            </tpc:firstCurrencyDetails>
            </tpc:currency>
            </tpc:pricingOptionGroup>
            </tpc:Fare_PricePNRWithBookingClass>
            </soapenv:Body>
            </soapenv:Envelope>';

        if($xml_query){
            $soap_url = $this->soap_url.$soapAction;
            $remarks = 'PNR_AddMultiElements_BookingClass(amadeus)';
            $this->CI->custom_db->generate_static_response ($xml_query,'Amadeus Flight PNR_AddMultiElements_BookingClass Request' );
            $PNR_Booking_Class = $this->process_request(trim($xml_query),$this->api_url,$remarks,$soap_url);
            $this->CI->custom_db->generate_static_response ($PNR_Booking_Class,'Amadeus Flight PNR_AddMultiElements_BookingClass Response' );
            //$PNR_Booking_Class  = file_get_contents(FCPATH.'fare_class.xml');
            $PNR_Booking_Class_arr = array();
            if($PNR_Booking_Class){
                $PNR_Booking_Class_arr = $this->xml2array($PNR_Booking_Class);
                if(isset($PNR_Booking_Class_arr['soapenv:Envelope']['soapenv:Header'])){

                    $SessionId = $PNR_Booking_Class_arr['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                    $SecurityToken = $PNR_Booking_Class_arr['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                    $SequenceNumber =  $PNR_Booking_Class_arr['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                    $SequenceNumber = ($SequenceNumber+1);
                }
                if(isset($PNR_Booking_Class_arr['soapenv:Envelope']['soapenv:Body']['soap:Fault']) || isset($PNR_Booking_Class_arr['soapenv:Envelope']['soapenv:Body']['Fare_PricePNRWithBookingClassReply']['applicationError']) || isset($PNR_Booking_Class_arr['soap:Envelope']['soap:Body']['soap:Fault'])){

                    $this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
                }else{
                    $response['status'] = SUCCESS_STATUS;
                    $response['message'] = 'success';
                    $response['data']['SessionId'] = $SessionId;
                    $response['data']['SecurityToken'] = $SecurityToken;
                    $response['data']['SequenceNumber'] = $SequenceNumber;
                }                
            }
            
            
        }
        return $response;

    }
    //Checking the price again
    private function Ticket_CreateTSTFromPricing(array $search_data, array $header_data): array{
         $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $SessionId = $header_data['SessionId'];
        $SequenceNumber = $header_data['SequenceNumber'];
        $SecurityToken = $header_data['SecurityToken'];
        $paList='';
        if($search_data['adult_config']>0){
            $paList .= '<psaList>
                        <itemReference>
                            <referenceType>TST</referenceType>
                            <uniqueReference>1</uniqueReference>
                        </itemReference>
                    </psaList>';
        }
        if($search_data['child_config']>0){
             $paList .= '<psaList>
                        <itemReference>
                            <referenceType>TST</referenceType>
                            <uniqueReference>2</uniqueReference>
                        </itemReference>
                    </psaList>';
        }
        if(($search_data['infant_config']>0 && $search_data['child_config'] > 0)){
            $paList .= '<psaList>
                        <itemReference>
                            <referenceType>TST</referenceType>
                            <uniqueReference>3</uniqueReference>
                        </itemReference>
                    </psaList>';
        }elseif (($search_data['infant_config']>0 && $search_data['child_config'] == 0)) {
            $paList .= '<psaList>
                        <itemReference>
                            <referenceType>TST</referenceType>
                            <uniqueReference>2</uniqueReference>
                        </itemReference>
                    </psaList>';
        }
        $soapAction = 'TAUTCQ_04_1_1A';
        $xml_query = '<?xml version="1.0" encoding="utf-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                <soapenv:Header>
                <awsse:Session TransactionStatusCode="InSeries" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
                <awsse:SessionId>'.$SessionId.'</awsse:SessionId>
                <awsse:SequenceNumber>'.$SequenceNumber.'</awsse:SequenceNumber>
                <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
                </awsse:Session>
                <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
                <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                </soapenv:Header>
                <soapenv:Body>
                <taut:Ticket_CreateTSTFromPricing xmlns:taus="http://xml.amadeus.com/'.$soapAction.'">
                '.$paList.'
                </taut:Ticket_CreateTSTFromPricing>
                </soapenv:Body>
                </soapenv:Envelope>';

        $soap_url = $this->soap_url.$soapAction;
        $this->CI->custom_db->generate_static_response ($xml_query,'Amadeus Ticket_CreateTSTFromPricing Request' );
        $api_response = $this->process_request($xml_query,$this->api_url,'TicketPricingReq(amadeus)',$soap_url);
        $this->CI->custom_db->generate_static_response ($api_response,'Amadeus Ticket_CreateTSTFromPricing Response' );
        //$api_response =  file_get_contents(FCPATH.'ticket.xml');
        if($api_response){
            $api_response = $this->xml2array($api_response);
            if(isset($api_response['soapenv:Envelope']['soapenv:Header'])){
                $SessionId = $api_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                $SecurityToken = $api_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                $SequenceNumber =  $api_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                $SequenceNumber = ($SequenceNumber+1);
            }
            if(isset($api_response['soapenv:Envelope']['soapenv:Body']['soap:Fault']) || isset($api_response['soapenv:Envelope']['soapenv:Body']['Ticket_CreateTSTFromPricingReply']['applicationError']) || isset($api_response['soap:Envelope']['soap:Body']['soap:Fault'])){
                 $this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
            }else{
                $response['status'] = SUCCESS_STATUS;
                $response['message'] = 'success';
                $response['data']['SessionId'] = $SessionId;
                $response['data']['SecurityToken'] = $SecurityToken;
                $response['data']['SequenceNumber'] = $SequenceNumber;
            }   
                   
        }
        return $response;
    }
    //Sending the form of payment
    private function FOP($booking_params, $carrier, array $header_data): array{
         $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];     
        $CardInfo = $booking_params['CardInfo'];
        $SessionId = $header_data['SessionId'];
        $SequenceNumber = $header_data['SequenceNumber'];
        $SecurityToken = $header_data['SecurityToken'];  
        $cardNumber=$CardInfo['card_number'];
        $cvv_code=$CardInfo['card_cvv'];
        $exp_date =implode("",explode("-",$CardInfo['expire_date']));
        $expire_date=$exp_date;
        $cardholdername=$CardInfo['holder_name'];

        $soapAction ='TFOPCQ_15_4_1A';
        $fop_tag = '';
        if($booking_params['Module']==B2B_USER){
            $fop_tag ='<fopInformation>
                            <formOfPayment>
                                <type>CASH</type>
                            </formOfPayment>
                        </fopInformation>
                        <dummy/>';
        }else{
            $fop_tag =' <fopInformation>
                            <formOfPayment>
                                <type>CC</type>
                            </formOfPayment>
                        </fopInformation>
                        <dummy/>
                        <creditCardData>
                            <creditCardDetails>
                                <ccInfo>
                                    <vendorCode>VI</vendorCode>
                                    <cardNumber>'.$cardNumber.'</cardNumber>
                                    <securityId>'.$cvv_code.'</securityId>
                                    <expiryDate>'.$expire_date.'</expiryDate>
                                    <ccHolderName>'.$cardholdername.'</ccHolderName>
                                </ccInfo>
                            </creditCardDetails>
                        </creditCardData>';
        }
        $xml_query='<?xml version="1.0" encoding="utf-8"?>
                <soapenv:Envelope
                    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                    <soapenv:Header>
                        <awsse:Session TransactionStatusCode="InSeries"
                            xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
                            <awsse:SessionId>'.$SessionId.'</awsse:SessionId>
                            <awsse:SequenceNumber>'.$SequenceNumber.'</awsse:SequenceNumber>
                            <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
                        </awsse:Session>
                        <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
                        <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                        <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                    </soapenv:Header>
                    <soapenv:Body>
                        <FOP_CreateFormOfPayment
                            xmlns="http://xml.amadeus.com/'.$soapAction.'">
                            <fopGroup>
                                <fopReference/>
                                <mopDescription>
                                    <fopSequenceNumber>
                                        <sequenceDetails>
                                            <number>1</number>
                                        </sequenceDetails>
                                    </fopSequenceNumber>
                                    <paymentModule>
                                        <groupUsage>
                                            <attributeDetails>
                                                <attributeType>DEFP</attributeType>
                                            </attributeDetails>
                                        </groupUsage>
                                        <paymentData>
                                            <merchantInformation>
                                                <companyCode>'.$carrier.'</companyCode>
                                            </merchantInformation>
                                        </paymentData>
                                        <mopInformation>
                                           '.$fop_tag.'
                                        </mopInformation>
                                        <dummy/>
                                    </paymentModule>
                                </mopDescription>
                            </fopGroup>
                        </FOP_CreateFormOfPayment>
                    </soapenv:Body>
                </soapenv:Envelope>';

            $soap_url = $this->soap_url.$soapAction;
            $fop_xml_response = $this->process_request($xml_query,$this->api_url,'formOfPayment(amadeus)',$soap_url);
           // $fop_xml_response =  file_get_contents(FCPATH.'fop.xml');
            if($fop_xml_response){
                $fop_xml_response = $this->xml2array($fop_xml_response);                
                if(isset($fop_xml_response['soapenv:Envelope']['soapenv:Header'])){
                    $SessionId = $fop_xml_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                    $SecurityToken = $fop_xml_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                    $SequenceNumber =  $fop_xml_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                    $SequenceNumber = ($SequenceNumber+1);
                }
                if(isset($fop_xml_response['soapenv:Envelope']['soapenv:Body']['FOP_CreateFormOfPaymentReply']['fopDescription'])){

                    $response['status'] = SUCCESS_STATUS;
                    $response['message'] = 'success';
                    $response['data']['SessionId'] = $SessionId;
                    $response['data']['SecurityToken'] = $SecurityToken;
                    $response['data']['SequenceNumber'] = $SequenceNumber;

                }else{
                     $this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
                }
            }
        return $response;
    }
    //checking the pnr status 
    private function PNR_AddMultiElements_10(array $header_data, array $booking_params, array $search_data): array{
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];

        $passenger_xml ='';
        $passenger_data = $this->format_passenger_data($booking_params);
        $booking_passenger_arr = $passenger_data['pax'];        
        $pt_count =2;       
        $SessionId = $header_data['SessionId'];
        $SequenceNumber = $header_data['SequenceNumber'];
        $SecurityToken = $header_data['SecurityToken'];
        if($search_data['is_domestic']==false){
            if($booking_passenger_arr['ADT']){
                foreach ($booking_passenger_arr['ADT'] as $key => $value) {
                    if($value['Gender']==1||$value['Gender']==6){
                        $title = 'M';
                    }else{
                        $title = 'F';
                    }  
                    //$value['PassporIssuingCountry'] = "IN";
                    $dob= strtoupper(strtolower(date('dMy',strtotime($value['DateOfBirth']))));
                    $PassportIssuingC= strtoupper($value['PassporIssuingCountry']);
                    $passport_no = strtoupper(strtolower($value['PassportNumber']));
                    $expire = strtoupper(strtolower(date('dMy',strtotime($value['PassportExpiry']))));
                    $CountryCode= $value['CountryCode'];
                    $full_name = $value['LastName'].'/'.$value['FirstName'];
                   // $passport_info = 'P/IN/IND906/IN/19JAN78/M/24APR25/PANDEY/RAHUL';
                    $passport_info='P/'.$CountryCode.'/'.$passport_no.'/'.$PassportIssuingC.'/'.$dob.'/'.$title.'/'.$expire.'/'.$full_name;

                    $passenger_xml .='<dataElementsIndiv>
                                        <elementManagementData>
                                             <segmentName>SSR</segmentName>
                                        </elementManagementData>
                                        <serviceRequest>
                                            <ssr>
                                                <type>DOCS</type>
                                                <status>HK</status>
                                                <quantity>1</quantity>
                                                <companyId>YY</companyId>
                                                <freetext>'.$passport_info.'</freetext>
                                            </ssr>
                                        </serviceRequest>
                                        <referenceForDataElement>
                                            <reference>
                                                <qualifier>PT</qualifier>
                                            <number>'.$pt_count.'</number>
                                            </reference>
                                        </referenceForDataElement>
                                    </dataElementsIndiv>';
                    if(isset($booking_passenger_arr['INF'][$key])){
                        $f_value = $booking_passenger_arr['INF'][$key];

                        if($f_value['Gender']==1||$f_value['Gender']==6){
                            $title = 'MI';
                        }else{
                            $title = 'FI';
                        }
                        $dob= strtoupper(strtolower(date('dMy',strtotime($f_value['DateOfBirth']))));
                        $PassportIssuingC= strtoupper($f_value['PassporIssuingCountry']);
                        $passport_no = strtoupper(strtolower($f_value['PassportNumber']));
                        $expire = strtoupper(strtolower(date('dMy',strtotime($f_value['PassportExpiry']))));
                        $CountryCode= $f_value['CountryCode'];
                        $full_name = $f_value['LastName'].'/'.$f_value['FirstName'];

                        $passport_info='P/'.$CountryCode.'/'.$passport_no.'/'.$PassportIssuingC.'/'.$dob.'/'.$title.'/'.$expire.'/'.$full_name;
                        $passenger_xml .='<dataElementsIndiv>
                                        <elementManagementData>
                                             <segmentName>SSR</segmentName>
                                        </elementManagementData>
                                        <serviceRequest>
                                            <ssr>
                                                <type>DOCS</type>
                                                <status>HK</status>
                                                <quantity>1</quantity>
                                                <companyId>YY</companyId>
                                                <freetext>'.$passport_info.'</freetext>
                                            </ssr>
                                        </serviceRequest>
                                        <referenceForDataElement>
                                            <reference>
                                                <qualifier>PT</qualifier>
                                            <number>'.$pt_count.'</number>
                                            </reference>
                                        </referenceForDataElement>
                                    </dataElementsIndiv>';
                        $pt_count=$pt_count+2;
                    }else{
                       $pt_count=$pt_count+1;
                    }
                }
            }
            if(isset($booking_passenger_arr['CHD'])){
                if(valid_array($booking_passenger_arr['CHD'])){
                    foreach ($booking_passenger_arr['CHD'] as $c_key => $c_value) {                    
                        if($c_value['Gender']==1||$c_value['Gender']==6){
                            $title = 'M';
                        }else{
                            $title = 'F';
                        }   
                        $dob= strtoupper(strtolower(date('dMy',strtotime($c_value['DateOfBirth']))));
                        $PassportIssuingC= strtoupper($c_value['PassporIssuingCountry']);
                        $passport_no = strtoupper(strtolower($c_value['PassportNumber']));
                        $expire = strtoupper(strtolower(date('dMy',strtotime($c_value['PassportExpiry']))));
                        $CountryCode= $c_value['CountryCode'];
                        $full_name = $c_value['LastName'].'/'.$c_value['FirstName'];
                       // $passport_info = 'P/IN/IND906/IN/19JAN78/M/24APR25/PANDEY/RAHUL';
                        $passport_info='P/'.$CountryCode.'/'.$passport_no.'/'.$PassportIssuingC.'/'.$dob.'/'.$title.'/'.$expire.'/'.$full_name;

                        $passenger_xml .='<dataElementsIndiv>
                                            <elementManagementData>
                                                 <segmentName>SSR</segmentName>
                                            </elementManagementData>
                                        <serviceRequest>
                                            <ssr>
                                                <type>DOCS</type>
                                                <status>HK</status>
                                                <quantity>1</quantity>
                                                <companyId>YY</companyId>
                                                <freetext>'.$passport_info.'</freetext>
                                            </ssr>
                                        </serviceRequest>
                                        <referenceForDataElement>
                                            <reference>
                                                <qualifier>PT</qualifier>
                                            <number>'.$pt_count.'</number>
                                            </reference>
                                        </referenceForDataElement>
                                    </dataElementsIndiv>';
                        $pt_count++;

                    }
                }
            }
            $pax_xml_query = $passenger_xml;
        }else{
            $pax_xml_query ='';
        }
        $soapAction = 'PNRADD_14_1_1A';
        $xml_query ='<?xml version="1.0" encoding="utf-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                <soapenv:Header>
                    <awsse:Session TransactionStatusCode="InSeries" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
                        <awsse:SessionId>'.$SessionId.'</awsse:SessionId>
                        <awsse:SequenceNumber>'.$SequenceNumber.'</awsse:SequenceNumber>
                        <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
                    </awsse:Session>
                    <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
                    <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                    <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                </soapenv:Header>
                <soapenv:Body>
                    <PNR_AddMultiElements xmlns="http://xml.amadeus.com/'.$soapAction.'" >
                                 <pnrActions>
                                    <optionCode>10</optionCode> 
                                </pnrActions>
                                <dataElementsMaster>
                                    <marker1/>
                                    '.$pax_xml_query.'  
                                </dataElementsMaster>                   
                    </PNR_AddMultiElements>
            </soapenv:Body>
        </soapenv:Envelope>';
        $soap_url = $this->soap_url.$soapAction;
        $this->CI->custom_db->generate_static_response ($xml_query,'Amadeus PNR_AddMultiElements Request' );
        $pnr_option10_response = $this->process_request($xml_query,$this->api_url,'PNRAddElementOption10',$soap_url);
        $this->CI->custom_db->generate_static_response ($pnr_option10_response,'Amadeus PNR_AddMultiElements Response' );
        //$pnr_option10_response  = file_get_contents(FCPATH.'pnr10.xml');
        if($pnr_option10_response){
            $pnr_option10_response = $this->xml2array($pnr_option10_response);
           if(isset($pnr_option10_response['soapenv:Envelope']['soapenv:Header'])){
                $SessionId = $pnr_option10_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                $SecurityToken = $pnr_option10_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                $SequenceNumber =  $pnr_option10_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                $SequenceNumber = ($SequenceNumber+1);
            }
      
            if((isset($pnr_option10_response['soapenv:Envelope']['soapenv:Body']['PNR_Reply'])) && (isset($pnr_option10_response['soapenv:Envelope']['soapenv:Body']['PNR_Reply']['pnrHeader']['reservationInfo']['reservation']['controlNumber'])) ){
                
                $controlNumber = $pnr_option10_response['soapenv:Envelope']['soapenv:Body']['PNR_Reply']['pnrHeader']['reservationInfo']['reservation']['controlNumber'];
                $response['status'] = SUCCESS_STATUS;
                $response['message'] = 'success';
                $response['data']['SessionId'] = $SessionId;
                $response['data']['SecurityToken'] = $SecurityToken;
                $response['data']['SequenceNumber'] = $SequenceNumber;
                $response['data']['Pnr_No'] = $controlNumber;
            }else{
            
                $this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
            }
          
        }
        return $response;
        
    }
    //placing in queue
    private function Place_Queue(array $booking_data): array{
         $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $SessionId = $booking_data['SessionId'];
        $SequenceNumber = $booking_data['SequenceNumber'];
        $SecurityToken = $booking_data['SecurityToken'];
        $pnr_no = $booking_data['Pnr_No'];
        if($pnr_no){
            $soapAction = 'QUQPCQ_03_1_1A';
            $xml_query ='<?xml version="1.0" encoding="UTF-8"?>           
                        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus
                        .com/2010/06/Security_v1" xmlns:typ="http://xml.amadeus.com/2010/06/Type">
                        <soapenv:Header>
                        <awsse:Session TransactionStatusCode="InSeries" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
                        <awsse:SessionId>'.$SessionId.'</awsse:SessionId>
                        <awsse:SequenceNumber>'.$SequenceNumber.'</awsse:SequenceNumber>
                        <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
                        </awsse:Session>
                        <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
                        <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                        <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                        </soapenv:Header>
                        <soapenv:Body>
                            <Queue_PlacePNR>
                                <placementOption>
                                    <selectionDetails>
                                        <option>QEQ</option>
                                    </selectionDetails>
                                </placementOption>
                                <targetDetails>
                                <targetOffice>
                                    <sourceType>
                                        <sourceQualifier1>3</sourceQualifier1>
                                    </sourceType>
                                    <originatorDetails>
                                        <inHouseIdentification1>'.$this->pseudo_city_code.'</inHouseIdentification1>
                                    </originatorDetails>
                                </targetOffice>
                                <queueNumber>
                                    <queueDetails>
                                        <number>1</number>
                                    </queueDetails>
                                </queueNumber>
                                <categoryDetails>
                                    <subQueueInfoDetails>
                                        <identificationType>C</identificationType>
                                        <itemNumber>0</itemNumber>
                                    </subQueueInfoDetails>
                                </categoryDetails>
                                </targetDetails>
                                <recordLocator>
                                    <reservation>
                                        <controlNumber>'.$pnr_no.'</controlNumber>
                                    </reservation>
                                </recordLocator>
                            </Queue_PlacePNR>
                        </soapenv:Body>
                    </soapenv:Envelope>';
            $soap_url  = $this->soap_url.$soapAction;
            $this->CI->custom_db->generate_static_response ($xml_query,'Amadeus Queue_PlacePNR Request');
            $place_queue_response = $this->process_request($xml_query,$this->api_url,'Queue_PlacePNR(amadeus)',$soap_url);
            $this->CI->custom_db->generate_static_response ($place_queue_response,'Amadeus Queue_PlacePNR Resonse');
            if($place_queue_response){
                $place_queue_response = $this->xml2array($place_queue_response);
               
                if(isset($place_queue_response['soapenv:Envelope']['soapenv:Header'])){
                    $SessionId = $place_queue_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                    $SecurityToken = $place_queue_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                    $SequenceNumber =  $place_queue_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                    $SequenceNumber = ($SequenceNumber+1);
                }
                if(isset($place_queue_response['soapenv:Envelope']['soapenv:Body']['Queue_PlacePNRReply']['recordLocator'])){
                    if(isset($place_queue_response['soapenv:Envelope']['soapenv:Body']['Queue_PlacePNRReply']['recordLocator']['reservation']['controlNumber'])){

                        $controlNumber = $place_queue_response['soapenv:Envelope']['soapenv:Body']['Queue_PlacePNRReply']['recordLocator']['reservation']['controlNumber'];

                        $response['status'] = SUCCESS_STATUS;   
                        $response['message'] = 'success';
                        $response['data']['SessionId'] = $SessionId;
                        $response['data']['SecurityToken'] = $SecurityToken;
                        $response['data']['SequenceNumber'] = $SequenceNumber;
                        $response['data']['Pnr_No'] = $controlNumber;


                    }
                }else{
                     // $this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
                }
              
            }
        }
        return $response;
    }
    /*Retrieve booked  PNR details*/
    private function PNRRetrieve(array $header_data): array{
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $SessionId = $header_data['SessionId'];
        $SequenceNumber = $header_data['SequenceNumber'];
        $SecurityToken = $header_data['SecurityToken'];
        $pnr_no = $header_data['Pnr_No'];
        $soapAction = 'PNRRET_17_1_1A';
        //$am_url = 'https://noded2.test.webservices.amadeus.com/1ASIWCTIZ77';
        //$this->api_url
        if(empty($header_data['SessionId'])==false){
            $header ='<soapenv:Header>
                        <awsse:Session TransactionStatusCode="InSeries" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
                            <awsse:SessionId>'.$SessionId.'</awsse:SessionId>
                            <awsse:SequenceNumber>'.$SequenceNumber.'</awsse:SequenceNumber>
                            <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
                        </awsse:Session>
                        <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
                        <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                        <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                    </soapenv:Header>';
        }else{
            $header =' <soapenv:Header>
                    <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
                    <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                    <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                    <link:TransactionFlowLink xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1"/>
                    <oas:Security xmlns:oas="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                    <oas:UsernameToken oas1:Id="UsernameToken-1" xmlns:oas1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                    <oas:Username>'.$this->username.'</oas:Username>
                    <oas:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'.$this->nonce.'</oas:Nonce>
                    <oas:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">'.$this->hashPwd.'</oas:Password>
                    <oas1:Created>'.$this->created.'</oas1:Created>
                    </oas:UsernameToken>
                    </oas:Security>
                    <AMA_SecurityHostedUser xmlns="http://xml.amadeus.com/2010/06/Security_v1">
                    <UserID AgentDutyCode="'.$this->agent_duty_code.'" RequestorType="'.$this->requestor_type.'" PseudoCityCode="'.$this->pseudo_city_code.'" POS_Type="'.$this->pos_type.'"/>
                    </AMA_SecurityHostedUser>
                    <awsse:Session TransactionStatusCode="Start" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3"/>
                    </soapenv:Header>';                    
        }
        $xml_query ='<?xml version="1.0" encoding="UTF-8"?>
                    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:typ="http://xml.amadeus.com/2010/06/Type">
                        '.$header.'
                    <soapenv:Body>
                <PNR_Retrieve xmlns="http://xml.amadeus.com/'.$soapAction.'">
                    <retrievalFacts>
                        <retrieve>
                            <type>2</type>
                        </retrieve>
                        <reservationOrProfileIdentifier>
                            <reservation>
                                <controlNumber>'.$pnr_no.'</controlNumber>
                            </reservation>
                        </reservationOrProfileIdentifier>
                    </retrievalFacts>
                </PNR_Retrieve>
                </soapenv:Body>
                </soapenv:Envelope>';
            $soap_url = $this->soap_url.$soapAction;
            $this->CI->custom_db->generate_static_response ($xml_query,'Amadeus Flight PNR_Retrieve Request' );
            $pnr_rerieve_response = $this->process_request($xml_query,$this->api_url,'PNR_Retrieve(amadeus)',$soap_url);
            $this->CI->custom_db->generate_static_response ($pnr_rerieve_response,'Amadeus Flight PNR_Retrieve Response' );
            if($pnr_rerieve_response){
                $pnr_rerieve_response = $this->xml2array($pnr_rerieve_response);

                if(isset($pnr_rerieve_response['soapenv:Envelope']['soapenv:Header'])){
                    $SessionId = $pnr_rerieve_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                    $SecurityToken = $pnr_rerieve_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                    $SequenceNumber =  $pnr_rerieve_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                    $SequenceNumber = ($SequenceNumber+1);
                    if(isset($pnr_rerieve_response['soapenv:Envelope']['soapenv:Body']['PNR_Reply']['pnrHeader']['reservationInfo']['reservation'])){
                        $controlNumber = $pnr_rerieve_response['soapenv:Envelope']['soapenv:Body']['PNR_Reply']['pnrHeader']['reservationInfo']['reservation']['controlNumber'];

                        $response['status'] = SUCCESS_STATUS;   
                        $response['message'] = 'success';
                        $response['data']['SessionId'] = $SessionId;
                        $response['data']['SecurityToken'] = $SecurityToken;
                        $response['data']['SequenceNumber'] = $SequenceNumber;
                        $response['data']['Pnr_No'] = $controlNumber;
                    }
                }else{
                     //$this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
                }

            }
        return $response;
            
    }
     private function Service_IntegratedPricing(array $header_data): array{
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $SessionId = $header_data['SessionId'];
        $SequenceNumber = $header_data['SequenceNumber'];
        $SecurityToken = $header_data['SecurityToken'];
        $pnr_no = $header_data['Pnr_No'];
        $soapAction = 'TPISGQ_15_1_1A';
        $soapHeaderFor_IssueTicket  = '<soapenv:Header>
            <awsse:Session TransactionStatusCode="InSeries" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
            <awsse:SessionId>'.$SessionId.'</awsse:SessionId>
            <awsse:SequenceNumber>'.$SequenceNumber.'</awsse:SequenceNumber>
            <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
            </awsse:Session>
            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
            </soapenv:Header>';
            $Service_IntegratedPricing = '<?xml version="1.0" encoding="utf-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            '.$soapHeaderFor_IssueTicket.'
            <soapenv:Body>
                <Service_IntegratedPricing xmlns="http://xml.amadeus.com/TPISGQ_15_1_1A">
                    <pricingOption>
                        <pricingOptionKey>
                            <pricingOptionKey>NOP</pricingOptionKey>
                        </pricingOptionKey>
                    </pricingOption>
                </Service_IntegratedPricing>
            </soapenv:Body>
            </soapenv:Envelope>';
            $soap_url = $this->soap_url.$soapAction;
            $this->CI->custom_db->generate_static_response ($Service_IntegratedPricing,'Service_IntegratedPricing Request' );
            $ticket_response = $this->process_request($Service_IntegratedPricing,$this->api_url,'Service_IntegratedPricing(amadeus)',$soap_url);
            $this->CI->custom_db->generate_static_response ($ticket_response,'Amadeus Service_IntegratedPricing Response' );
           if($ticket_response){
             $ticket_response = $this->xml2array($ticket_response);
               if(isset($ticket_response['soapenv:Envelope']['soapenv:Header'])){
                     $SessionId = $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                    $SecurityToken = $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                    $SequenceNumber =  $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                    $SequenceNumber = ($SequenceNumber+1);
                    $response['status'] = SUCCESS_STATUS;
               }

                $data['SessionId'] = $SessionId;
                $data['SecurityToken'] = $SecurityToken;
                $data['SequenceNumber'] = $SequenceNumber;
                $data['Pnr_No'] = $pnr_no;
                $response['data'] = $data;
                // $PNRRetrieve = $this->PNRRetrieve($data);

                //$this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
           }

        return $response;
    }
     private function PNR_AddMultiElements_final_option11(array $header_data, string $pnr): array{
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $SessionId = $header_data['SessionId'];
        $SequenceNumber = $header_data['SequenceNumber'];
        $SecurityToken = $header_data['SecurityToken'];
        $pnr_no = $header_data['Pnr_No'];
        $soapAction = 'PNRADD_14_1_1A';
        
            $soapreq='<PNR_AddMultiElements xmlns:pnr="http://xml.amadeus.com/PNRADD_14_1_1A">
             <reservationInfo>
                        <reservation>
                            <controlNumber>'.$pnr.'</controlNumber>
                        </reservation>
                    </reservationInfo>
                   <pnrActions>
                        <optionCode>11</optionCode>
                        </pnrActions>
                         <dataElementsMaster>
                         <marker1/>                     
                       <dataElementsIndiv>
                            <elementManagementData>
                                <segmentName>RF</segmentName>
                            </elementManagementData>
                            <freetextData>
                                <freetextDetail>
                                    <subjectQualifier>3</subjectQualifier>
                                    <type>P22</type>
                                </freetextDetail>
                                <longFreetext>Flyjet Booking</longFreetext>
                            </freetextData>
                        </dataElementsIndiv>
                     </dataElementsMaster>
                  </PNR_AddMultiElements>';
            
       
        $soapHeaderFor_IssueTicket  = '<soapenv:Header>
            <awsse:Session TransactionStatusCode="InSeries" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
            <awsse:SessionId>'.$SessionId.'</awsse:SessionId>
            <awsse:SequenceNumber>'.$SequenceNumber.'</awsse:SequenceNumber>
            <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
            </awsse:Session>
            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
            </soapenv:Header>';
            $PNR_AddMultiElements_final_option11 = '<?xml version="1.0" encoding="utf-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            '.$soapHeaderFor_IssueTicket.'
            <soapenv:Body>
             '.$soapreq.'
            </soapenv:Body>
            </soapenv:Envelope>';
            $soap_url = $this->soap_url.$soapAction;
            $this->CI->custom_db->generate_static_response ($PNR_AddMultiElements_final_option11,'PNR_AddMultiElements_optionFinal Request' );
            $ticket_response = $this->process_request($PNR_AddMultiElements_final_option11,$this->api_url,'PNR_AddMultiElementsFinal(amadeus)',$soap_url);
            $this->CI->custom_db->generate_static_response ($ticket_response,'Amadeus PNR_AddMultiElements_optionFinal Response' );
           if($ticket_response){
             $ticket_response = $this->xml2array($ticket_response);
               if(isset($ticket_response['soapenv:Envelope']['soapenv:Header'])){
                     $SessionId = $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                    $SecurityToken = $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                    $SequenceNumber =  $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                    $SequenceNumber = ($SequenceNumber+1);
                    $response['status'] = SUCCESS_STATUS;
               }

                $data['SessionId'] = $SessionId;
                $data['SecurityToken'] = $SecurityToken;
                $data['SequenceNumber'] = $SequenceNumber;
                $data['Pnr_No'] = $pnr_no;
                $response['data'] = $data;
                // $PNRRetrieve = $this->PNRRetrieve($data);

                $this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
           }

        return $response;
    }
     private function PNR_AddMultiElements_option11(array $header_data): array{
         $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $SessionId = $header_data['SessionId'];
        $SequenceNumber = $header_data['SequenceNumber'];
        $SecurityToken = $header_data['SecurityToken'];
        $pnr_no = $header_data['Pnr_No'];
        $soapAction = 'PNRADD_14_1_1A';
        $aa = "";
        if($aa==""){
            $SequenceNumber=6;  
            $opt='<optionCode>10</optionCode>';
            $soapreq='<PNR_AddMultiElements xmlns:pnr="http://xml.amadeus.com/'.$soapAction.'">
                    <pnrActions>
                    '.$opt.'
                    </pnrActions>
                    <dataElementsMaster>
                    <marker1/>
                    <dataElementsIndiv>
                    <elementManagementData>
                    <segmentName>RF</segmentName>
                    </elementManagementData>
                    <freetextData>
                    <freetextDetail>
                    <subjectQualifier>3</subjectQualifier>
                    <type>P22</type>
                    </freetextDetail>
                    <longFreetext>Flyjet Booking</longFreetext>
                    </freetextData>
                    </dataElementsIndiv>
                    </dataElementsMaster>
                    </PNR_AddMultiElements>';
            
        }else{

        }
        $soapHeaderFor_IssueTicket  = '<soapenv:Header>
            <awsse:Session TransactionStatusCode="InSeries" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
            <awsse:SessionId>'.$SessionId.'</awsse:SessionId>
            <awsse:SequenceNumber>'.$SequenceNumber.'</awsse:SequenceNumber>
            <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
            </awsse:Session>
            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
            </soapenv:Header>';
            $PNR_AddMultiElements_option11 = '<?xml version="1.0" encoding="utf-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            '.$soapHeaderFor_IssueTicket.'
            <soapenv:Body>
             '.$soapreq.'
            </soapenv:Body>
            </soapenv:Envelope>';
            $soap_url = $this->soap_url.$soapAction;
            $this->CI->custom_db->generate_static_response ($PNR_AddMultiElements_option11,'PNR_AddMultiElements_optionSSR Request' );
            $ticket_response = $this->process_request($PNR_AddMultiElements_option11,$this->api_url,'PNR_AddMultiElementsSSR(amadeus)',$soap_url);
            $this->CI->custom_db->generate_static_response ($ticket_response,'Amadeus PNR_AddMultiElements_optionSSR Response' );
           if($ticket_response){
             $ticket_response = $this->xml2array($ticket_response);
               if(isset($ticket_response['soapenv:Envelope']['soapenv:Header'])){
                     $SessionId = $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                    $SecurityToken = $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                    $SequenceNumber =  $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                    $SequenceNumber = ($SequenceNumber+1);
                    $response['status'] = SUCCESS_STATUS;
               }

                $data['SessionId'] = $SessionId;
                $data['SecurityToken'] = $SecurityToken;
                $data['SequenceNumber'] = $SequenceNumber;
                $data['Pnr_No'] = $pnr_no;
                $response['data'] = $data;
                // $PNRRetrieve = $this->PNRRetrieve($data);

                //$this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
           }

        return $response;
    }
    private function Ticket_CreateTSMFromPricing(array $header_data): array{
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $SessionId = $header_data['SessionId'];
        $SequenceNumber = $header_data['SequenceNumber'];
        $SecurityToken = $header_data['SecurityToken'];
        $pnr_no = $header_data['Pnr_No'];
        $soapAction = 'TPISGQ_15_1_1A';
        $soapHeaderFor_IssueTicket  = '<soapenv:Header>
            <awsse:Session TransactionStatusCode="InSeries" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
            <awsse:SessionId>'.$SessionId.'</awsse:SessionId>
            <awsse:SequenceNumber>'.$SequenceNumber.'</awsse:SequenceNumber>
            <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
            </awsse:Session>
            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
            </soapenv:Header>';
            $Ticket_CreateTSMFromPricing = '<?xml version="1.0" encoding="utf-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            '.$soapHeaderFor_IssueTicket.'
            <soapenv:Body>
            <Ticket_CreateTSMFromPricing>
              <psaList>
                <itemReference>
                  <referenceType>TSM</referenceType>
                </itemReference>
              </psaList>
            </Ticket_CreateTSMFromPricing>
            </soapenv:Body>
            </soapenv:Envelope>';
            $soap_url = $this->soap_url.$soapAction;
            $this->CI->custom_db->generate_static_response ($Ticket_CreateTSMFromPricing,'Ticket_CreateTSMFromPricing Request' );
            $ticket_response = $this->process_request($Ticket_CreateTSMFromPricing,$this->api_url,'Ticket_CreateTSMFromPricing(amadeus)',$soap_url);
            $this->CI->custom_db->generate_static_response ($ticket_response,'Amadeus Ticket_CreateTSMFromPricing Response' );
           if($ticket_response){
             $ticket_response = $this->xml2array($ticket_response);
               if(isset($ticket_response['soapenv:Envelope']['soapenv:Header'])){
                     $SessionId = $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                    $SecurityToken = $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                    $SequenceNumber =  $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                    $SequenceNumber = ($SequenceNumber+1);
                    $response['status'] = SUCCESS_STATUS;
               }

                $data['SessionId'] = $SessionId;
                $data['SecurityToken'] = $SecurityToken;
                $data['SequenceNumber'] = $SequenceNumber;
                $data['Pnr_No'] = $pnr_no;
                $response['data'] = $data;
                // $PNRRetrieve = $this->PNRRetrieve($data);

                //$this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
           }

        return $response;
    }
    private function DocIssuance_IssueTicket(array $header_data): array{
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $SessionId = $header_data['SessionId'];
        $SequenceNumber = $header_data['SequenceNumber'];
        $SecurityToken = $header_data['SecurityToken'];
        $pnr_no = $header_data['Pnr_No'];
        $soapAction = 'TTKTIQ_15_1_1A';
        $soapHeaderFor_IssueTicket  = '<soapenv:Header>
            <awsse:Session TransactionStatusCode="InSeries" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">
            <awsse:SessionId>'.$SessionId.'</awsse:SessionId>
            <awsse:SequenceNumber>'.$SequenceNumber.'</awsse:SequenceNumber>
            <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
            </awsse:Session>
            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
            </soapenv:Header>';
            $DocIssuance_IssueTicket = '<?xml version="1.0" encoding="utf-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            '.$soapHeaderFor_IssueTicket.'
            <soapenv:Body>
            <DocIssuance_IssueTicket>
            <optionGroup>
                <switches>
                    <statusDetails>
                        <indicator>ET</indicator>
                    </statusDetails>
                </switches>
            </optionGroup>
            <otherCompoundOptions>
            <attributeDetails>
                <attributeType>ETC</attributeType>
            </attributeDetails>
            </otherCompoundOptions>
            </DocIssuance_IssueTicket>
            </soapenv:Body>
            </soapenv:Envelope>';
            $soap_url = $this->soap_url.$soapAction;
            $this->CI->custom_db->generate_static_response ($DocIssuance_IssueTicket,'DocIssuance_IssueTicket Request' );
            $ticket_response = $this->process_request($DocIssuance_IssueTicket,$this->api_url,'IssueTicket(amadeus)',$soap_url);
            $this->CI->custom_db->generate_static_response ($ticket_response,'Amadeus DocIssuance_IssueTicket Response' );
           if($ticket_response){
             $ticket_response = $this->xml2array($ticket_response);
               if(isset($ticket_response['soapenv:Envelope']['soapenv:Header'])){
                     $SessionId = $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                    $SecurityToken = $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                    $SequenceNumber =  $ticket_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                    $SequenceNumber = ($SequenceNumber+1);
                    $response['status'] = SUCCESS_STATUS;
               }

                $data['SessionId'] = $SessionId;
                $data['SecurityToken'] = $SecurityToken;
                $data['SequenceNumber'] = $SequenceNumber;
                $data['Pnr_No'] = $pnr_no;
                $response['data'] = $data;
                $PNRRetrieve = $this->PNRRetrieve($data);

                //$this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
           }

        return $response;
    }

    /*Booking Procedure Ends*/    
    

    /**
     * Formats the Fare Request For Passenger-wise
     *
     * @param unknown_type $passenger_token
     */
    private function assign_booking_passenger_fare_breakdown(array $passenger_token): array {
        $passenger_token = force_multple_data_format($passenger_token);
        $Fare = array();
        foreach ($passenger_token as $k => $v) {
            $Fare [$v ['PassengerType']] ['BaseFare'] = ($v ['BasePrice'] / $v ['PassengerCount']);
            $Fare [$v ['PassengerType']] ['Tax'] = ($v ['Tax'] / $v ['PassengerCount']);
            //$Fare [$v ['PassengerType']] ['TransactionFee'] = ($v ['TransactionFee'] / $v ['PassengerCount']);
            $Fare [$v ['PassengerType']] ['TransactionFee'] = 0;
            // $Fare [$v ['PassengerType']] ['YQTax'] = ($v ['YQTax'] / $v ['PassengerCount']);
            // $Fare [$v ['PassengerType']] ['AdditionalTxnFeeOfrd'] = ($v ['AdditionalTxnFeeOfrd'] / $v ['PassengerCount']);
            // $Fare [$v ['PassengerType']] ['AdditionalTxnFeePub'] = ($v ['AdditionalTxnFeePub'] / $v ['PassengerCount']);
            //$Fare [$v ['PassengerType']] ['AirTransFee'] = ($v ['AirTransFee'] / $v ['PassengerCount']);
            $Fare [$v ['PassengerType']] ['AirTransFee'] = 0;
        }
        return $Fare;
    }
    /**
     * Validates the mobile number
     * @param unknown_type $mobile_number
     */
    private function validate_mobile_number(string $mobile_number): array {
        $mobile_number = trim($mobile_number);
        $mobile_number = ltrim($mobile_number, '0');
        if (strlen($mobile_number) < 10) {
            $mobile_number_length = strlen($mobile_number);
            $required_extra_number_lengths = (10) - $mobile_number_length;
            $extra_numbers = str_repeat('0', $required_extra_number_lengths);
            $mobile_number = $mobile_number . '' . $extra_numbers;
        }
        return $mobile_number;
    }            
    
    /**
     * Extra Services
     * @param unknown_type $request
     */
    public function get_extra_services(array $request, int $search_id): array {
       
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $baggage_response = $this->baggage_request($request, $search_id);
        $seat_response = $this->seat_request($request, $search_id);
        $meal_response = $this->meal_request($search_id);
        //$seat_response = array();
        // debug($seat_response);exit;
        $response ['data']['ExtraServiceDetails']['Baggage'] =  $baggage_response;
        $response ['data']['ExtraServiceDetails']['Seat'] = $seat_response;
        $response ['data']['ExtraServiceDetails']['Meals'] = $meal_response;
        $response ['status'] = SUCCESS_STATUS;
        //$response ['data']['ExtraServiceDetails']['MealPreference'] = $this->getMeals($request);
        $response ['data']['ExtraServiceDetails']['MealPreference']  ='';
        //debug($response);exit;
        return $response;
        // debug($response);exit;
    } 
    public function meal_request(int $search_id): array{
        $search_data = $this->search_data($search_id);
        //debug($search_data);exit;
        $meal_data = $this->CI->custom_db->single_table_records('meal_info','*');
        $meals_data = array();
        if($search_data['data']['trip_type'] != "multicity"){
            foreach($meal_data['data'] as $meal_key => $meal_info){
                $key1 ['key'][0]['Type'] = 'dynamic';
                $key1 ['key'][0]['Code'] = $meal_info['meal_code'];
                $key1 ['key'][0]['Description'] = $meal_info['meal_description'];
                $meal_id = serialized_data($key1['key']);
                $meals_data[0][$meal_key]['Code'] = $meal_info['meal_code'];
                $meals_data[0][$meal_key]['Price'] = 0;
                $meals_data[0][$meal_key]['Description'] = $meal_info['meal_description'];;
                $meals_data[0][$meal_key]['Type'] = 'Dynamic';
                $meals_data[0][$meal_key]['Origin'] = $search_data['data']['from'];
                $meals_data[0][$meal_key]['Destination'] = $search_data['data']['to'];
                $meals_data[0][$meal_key]['MealId'] = $meal_id;
            }
        }
        if($search_data['data']['trip_type'] == "circle"){
             foreach($meal_data['data'] as $meal_key => $meal_info){
                $key1 ['key'][0]['Type'] = 'dynamic';
                $key1 ['key'][0]['Code'] = $meal_info['meal_code'];
                $key1 ['key'][0]['Description'] = $meal_info['meal_description'];
                $meal_id = serialized_data($key1['key']);
                $meals_data[1][$meal_key]['Code'] = $meal_info['meal_code'];
                $meals_data[1][$meal_key]['Price'] = 0;
                $meals_data[1][$meal_key]['Description'] = $meal_info['meal_description'];;
                $meals_data[1][$meal_key]['Type'] = 'Dynamic';
                $meals_data[1][$meal_key]['Origin'] = $search_data['data']['from'];
                $meals_data[1][$meal_key]['Destination'] = $search_data['data']['to'];
                $meals_data[1][$meal_key]['MealId'] = $meal_id;
            }
        }
        if($search_data['data']['trip_type'] == "multicity"){
            for($i=0; $i<count($search_data['data']['from']); $i++){
                 foreach($meal_data['data'] as $meal_key => $meal_info){
                    $key1 ['key'][0]['Type'] = 'dynamic';
                    $key1 ['key'][0]['Code'] = $meal_info['meal_code'];
                    $key1 ['key'][0]['Description'] = $meal_info['meal_description'];
                    $meal_id = serialized_data($key1['key']);
                    $meals_data[$i][$meal_key]['Code'] = $meal_info['meal_code'];
                    $meals_data[$i][$meal_key]['Price'] = 0;
                    $meals_data[$i][$meal_key]['Description'] = $meal_info['meal_description'];;
                    $meals_data[$i][$meal_key]['Type'] = 'Dynamic';
                    $meals_data[$i][$meal_key]['Origin'] = $search_data['data']['from'][$i];
                    $meals_data[$i][$meal_key]['Destination'] = $search_data['data']['to'][$i];
                    $meals_data[$i][$meal_key]['MealId'] = $meal_id;
                }
            }
        }
       return $meals_data;
    } 
    public function baggage_request(array $request_data, int $search_id): array {
        //debug($request_data);exit;
        $search_data = $this->search_data($search_id);
        $adult_count = $search_data['data']['adult_config'];
        $child_count = $search_data['data']['child_config'];
        $soapAction="TPSCGQ_17_1_1A";
        $formatted_baggage_details = array();  

        $passengerinfo='';
        $paxsegref='';
       // $option_code = '';
        for($i=0;$i<$adult_count;$i++){
            $refno=$i+1;
            $passengerinfo .='<passengerInfoGroup>
                    <specificTravellerDetails>
                        <travellerDetails>
                            <referenceNumber>'.$refno.'</referenceNumber>
                        </travellerDetails>
                    </specificTravellerDetails>
                    <fareInfo>
                        <valueQualifier>ADT</valueQualifier>
                    </fareInfo>
                </passengerInfoGroup>';
        }
        if($passengerinfo != '' & $child_count==0){
            $refnumb=$refno;
        }
         if($child_count > 0){
            $refnumb = $refno + 1;
            for($i=0;$i<$child_count;$i++)
            {
                $passengerinfo .='<passengerInfoGroup>
                                <specificTravellerDetails>
                                    <travellerDetails>
                                        <referenceNumber>'.$refnumb.'</referenceNumber>
                                    </travellerDetails>
                                </specificTravellerDetails>
                                <fareInfo>
                                    <valueQualifier>CHD</valueQualifier>
                                </fareInfo>
                            </passengerInfoGroup>';
             
                $refnumb++; 
            }
            
        }
        $refnumber=$refnumb;
     
        if($refnumber > 1){
            $total_pax = $adult_count+$child_count;
            $refnumb=$total_pax;
            for($i=1;$i<=$refnumb;$i++)
            {
            $paxsegref.='<referenceDetails>
                    <type>P</type>
                    <value>'.$i.'</value>
                </referenceDetails>';        
            }
            
        }else{
            $paxsegref.='<referenceDetails>
                    <type>P</type>
                    <value>1</value>
                </referenceDetails>';
        }

         $segment_group = '';
         $seg_key = 1;
         $d = 0;
        //debug($request_data['FlightInfo']['Attr']);exit;
        $fare_basis = $request_data['FlightInfo']['Attr']['FareDetails'][0]['fareBasis'];
        foreach($request_data['FlightInfo']['FlightDetails']['Details'] as $flight_det_key => $flight_details){
            $segref = '';
            foreach($flight_details as $flight_key1 => $params){
            $departdate = explode(" ",$params['Origin']['DateTime']);
            $departure_date = date("dmy", strtotime($departdate[0]));
            $departure_time = date("Hi", strtotime($departdate[1]));
            $arrivaldate = explode(" ",$params['Destination']['DateTime']);
            $arrival_date = date("dmy", strtotime($arrivaldate[0]));
            $arrival_time = date("Hi", strtotime($arrivaldate[1]));
            $attributes = $request_data['FlightInfo']['Attr'];
            if(isset($params['Origin']['AirportCode'])){
                $borad_point_true_locationid = $params['Origin']['AirportCode'];
            }else{
                $borad_point_true_locationid = '';
            }
            
            if(isset($params['Destination']['AirportCode'])){
                $offpoint_true_locationid = $params['Destination']['AirportCode'];
            }else{
                $offpoint_true_locationid = '';
            }
            if(isset($params['OperatorCode'])){
                $marketing_company = $params['OperatorCode'];
            }else{
                $marketing_company = $params['DisplayOperatorCode'];
            }
            if(isset($params['FlightNumber'])){
                $filght_number = $params['FlightNumber'];
            }else{
                $filght_number ='';
            }
            
            if(isset($attributes['FareDetails'][0]['designator'])){
                $booking_class = $attributes['FareDetails'][0]['designator']; //M
            }else{
                $booking_class = 'M';    
            }
            
            $flight_indicator = 1;    
           
            if(isset($flight_details[0]['PriceInfo']['MultiTicket_number'])){
                $item_number = $flight_details[0]['PriceInfo']['MultiTicket_number'];
            }else{
                $item_number = '1';
            }
            $segment_group.= '<flightInfo>
            <flightDetails>
               <flightDate>
                  <departureDate>'.$departure_date.'</departureDate>
                  <departureTime>'.$departure_time.'</departureTime>
               </flightDate>
               <boardPointDetails>
                  <trueLocationId>'.$borad_point_true_locationid.'</trueLocationId>
               </boardPointDetails>
               <offpointDetails>
                  <trueLocationId>'.$offpoint_true_locationid.'</trueLocationId>
               </offpointDetails>
               <companyDetails>
                  <marketingCompany>'.$marketing_company.'</marketingCompany>
                  <operatingCompany>'.$marketing_company.'</operatingCompany>
               </companyDetails>
               <flightIdentification>
                  <flightNumber>'.$filght_number.'</flightNumber>
                  <bookingClass>'.$booking_class.'</bookingClass>
               </flightIdentification>
               <flightTypeDetails>
                  <flightIndicator>'.$seg_key.'</flightIndicator>
               </flightTypeDetails>
               <itemNumber>'.$seg_key.'</itemNumber>
            </flightDetails>
         </flightInfo>';
            if($flight_key1 == 0){
                foreach($flight_details as $flight_key => $params){
                    //debug($flight_details);exit;
                  $d=$d+1;
                  $segref.='<referenceDetails>
                                <type>S</type>
                                <value>'.$d.'</value>
                              </referenceDetails>';
                               
                }
                 $option_code.='
                             <pricingOption>
                                <pricingOptionKey>
                                  <pricingOptionKey>FAR</pricingOptionKey>
                                </pricingOptionKey>
                            <optionDetail>
                              <criteriaDetails>
                                <attributeType>B</attributeType>
                                <attributeDescription>'.$fare_basis[$flight_key].'</attributeDescription>
                              </criteriaDetails>
                            </optionDetail>
                            <paxSegTstReference>
                               '.$paxsegref.'
                               '.$segref.'
                            </paxSegTstReference>
                            </pricingOption>
                            ';
            }
            $seg_key++;
        }
    }
      //echo $option_code;exit;
        //new catalog request working
        $service_catalogue_request='<?xml version="1.0" encoding="UTF-8"?>           
                    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:typ="http://xml.amadeus.com/2010/06/Type">
                    <soapenv:Header>
                        <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
                        <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                        <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                        <link:TransactionFlowLink xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1"/>
                        <oas:Security xmlns:oas="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                        <oas:UsernameToken oas1:Id="UsernameToken-1" xmlns:oas1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                        <oas:Username>'.$this->username.'</oas:Username>
                        <oas:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'.$this->nonce.'</oas:Nonce>
                        <oas:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">'.$this->hashPwd.'</oas:Password>
                        <oas1:Created>'.$this->created.'</oas1:Created>
                    </oas:UsernameToken>
                    </oas:Security>
                    <AMA_SecurityHostedUser xmlns="http://xml.amadeus.com/2010/06/Security_v1">
                    <UserID AgentDutyCode="'.$this->agent_duty_code.'" RequestorType="'.$this->requestor_type.'" PseudoCityCode="'.$this->pseudo_city_code.'" POS_Type="'.$this->pos_type.'"/>
                    </AMA_SecurityHostedUser>
                </soapenv:Header>
           <soapenv:Body>
              <Service_StandaloneCatalogue>
            '.$passengerinfo.'
            
                '.$segment_group.'
           
             '.$option_code.'
            <pricingOption>
                <pricingOptionKey>
                    <pricingOptionKey>GRP</pricingOptionKey>
                </pricingOptionKey>
                <optionDetail>
                    <criteriaDetails>
                        <attributeType>BG</attributeType>
                    </criteriaDetails>
                </optionDetail>
            </pricingOption>
            <pricingOption>
                <pricingOptionKey>
                    <pricingOptionKey>SCD</pricingOptionKey>
                </pricingOptionKey>
            </pricingOption>
            <pricingOption>
                <pricingOptionKey>
                    <pricingOptionKey>MIF</pricingOptionKey>
                </pricingOptionKey>
            </pricingOption>
        </Service_StandaloneCatalogue>
           </soapenv:Body>
        </soapenv:Envelope>';
        $soap_url = $this->soap_url.$soapAction;
//echo $service_catalogue_request;exit;
        $baggage_response = $this->process_request($service_catalogue_request,$this->api_url,'Baggage Request(amadeus)',$soap_url); 
        //debug($baggage_response);exit; 
       // $baggage_response = $this->CI->custom_db->get_static_response (4682);  
        $Segment_data = $request_data['FlightInfo']['FlightDetails']['Details'];
        if($baggage_response){
            $baggage_response = $this->xml2array($baggage_response);
            if(isset($baggage_response['soapenv:Envelope']['soapenv:Body']['Service_StandaloneCatalogueReply']['flightInfo'])) {
                $formatted_baggage_details = $this->format_baggage_response($baggage_response,$Segment_data);

            }
            
        } 
        return $formatted_baggage_details;
        //debug($formatted_baggage_details);exit;
           
    }  
    public function format_baggage_response(array $baggageresponse, array $segment_data): array{
       // debug($baggageresponse);exit;
        $segment_count = count($segment_data);
        $baggageInformation = $baggageresponse['soapenv:Envelope']['soapenv:Body']['Service_StandaloneCatalogueReply'];

        $final_baggage_array = array();
        $baggage_raw_data = $baggageresponse['soapenv:Envelope']['soapenv:Body']['Service_StandaloneCatalogueReply'];
        $currency = "AUD";
        $currency_conversion =  $this->CI->custom_db->single_table_records('domain_currency_converter','*', array('country' => $currency));
        $currency_conversion_rate = $currency_conversion['data'][0]['value'];
        //debug($baggage_raw_data);exit;
        $Service_group = $baggage_raw_data['serviceGroup'];
        $flight_info = force_multple_data_format($baggage_raw_data['flightInfo']);
        for($i=0;$i<count($segment_data);$i++){
            $dep11 = $segment_data[$i][0]['Origin']['AirportCode'];
            $rr=count($segment_data[$i])-1;
            $baggage_data = array();
            $arr11=$segment_data[$i][$rr]['Destination']['AirportCode'];
            $flightDetails=$flight_info[$i]['flightDetails'];
            $flight_end_details = $flight_info[$rr]['flightDetails'];

            $segmentdetail[$i]=$dep11."_".$arr11;
            //for start
            $key = 0;
            foreach($Service_group as $key1=>$val){  
                if(isset($val['serviceDetailsGroup']['serviceDetails']['specialRequirementsInfo']['ssrCode'])){
                    if($val['serviceDetailsGroup']['serviceDetails']['specialRequirementsInfo']['ssrCode'] == "XBAG"){

                        $service_fee_text =  $val['serviceDetailsGroup']['serviceDetails']['specialRequirementsInfo']['serviceFreeText'];
                        $code = $service_fee_text;
                        if(isset($service_fee_text[0])){
                            $code = $service_fee_text[0];
                        }
                        $baggage_data[$key]['Code'] = $code ;

                        
                        //pricing
                        if(isset($val['pricingGroup'][0]['computedTaxSubDetails'])){

                            $amountp=$val['pricingGroup'][0]['computedTaxSubDetails']['monetaryDetails']['amount'];

                           $baggage_data[$key]['Price'] = $currency_conversion_rate*$amountp;
                             

                         }else{
                            if(isset($val['pricingGroup']['computedTaxSubDetails'])){
                                $pricing=$val['pricingGroup']['computedTaxSubDetails'];
                                if(isset($pricing['monetaryDetails']['amount'])){
                                    $amountm=$val['pricingGroup']['couponInfoGroup']['monetaryInfo']['monetaryDetails']['amount'];
                                    if($amountm==''){
                                        $amountm=$pricing['monetaryDetails']['amount'];
                                    }
                                    $baggage_data[$key]['Price']= $currency_conversion_rate*($amountm/$segment_count);//get_converted_cur

                                }else{
                                    $baggage_data[$key]['Price']= 0;    
                                }     
                            }
                            else{
                                $baggage_data[$key]['Price']= 0; 
                            }
                        }
                        $description = $val['serviceAttributes'][0]['criteriaDetails']['attributeDescription'];
                        $weight = '';
                        if(isset($val['baggageDescriptionGroup']['baggageData'])  && ($val['baggageDescriptionGroup']['baggageData'] != '')){
                            if(isset($val['baggageDescriptionGroup']['baggageData']['baggageDetails'])){
                                  $baggage_details=$val['baggageDescriptionGroup']['baggageData']['baggageDetails'];

                                  $weight = $baggage_details['quantityCode'].' '.$baggage_details['measurement'];
                                }
                        }
                        else{
                            if(isset($baggage_description['range']['rangeDetails'])){
                                $un=$baggage_description['range']['rangeDetails'][0]['dataType'];
                                $weightmin=$baggage_description['range']['rangeDetails'][0]['min'];
                                $weightmax=$baggage_description['range']['rangeDetails'][0]['max'];
                                if($un=='K'){
                                    $unit='Kg';
                                }
                                $weight = $weightmax .' '.$unit;
                            }
                        }
                        if(empty($weight) == true){
                            preg_match_all('!\d+!', $description, $matches);
                            $weight = $matches[0][0];
                            $description = strtolower($description);
                            $needle = 'kg';
                            if (strpos($description, $needle) != false) {
                                $weight.=' KG';
                            }
                            else{
                                $weight.=' Piece';
                            }
                        }
                        $baggage_data[$key]['Weight']= $description; 
                        $baggage_data[$key]['Type']= 'Dynamic';
                        $baggage_data[$key]['Origin']= $flightDetails['boardPointDetails']['trueLocationId'];
                        $baggage_data[$key]['Destination']= $flight_end_details['offpointDetails']['trueLocationId'];;
                        $key2 ['key'][0]['Type'] = 'dynamic';
                        $key2 ['key'][0]['Code'] = $code;
                       
                        $bag_id = serialized_data($key2['key']);

                        $baggage_data[$key]['BaggageId']= $bag_id;
                        $key++;
                       
                    }
                }
            }
            if(valid_array($baggage_data)){
                $final_baggage_array[$i] = $baggage_data;
            }
        //for end loop
        }
        //debug($final_baggage_array);exit;
        return $final_baggage_array;
    }
      public function seat_request(array $request_data, int $search_id): array {
        // debug($request_data);exit;
        $search_data = $this->search_data($search_id);
        $segment_data = $request_data['FlightInfo']['FlightDetails']['Details'];
        //debug($segment_data);exit;
        // debug($request_data);exit;
        $seatmapresponse = array();
        $segment_type = 1;
        $seg_count = 0;
        $SessionId=$SecurityToken=$SequenceNumber='';
        for($j=0;$j< count($segment_data); $j++){
            for($ss=0;$ss< count($segment_data[$j]); $ss++){
            //if($ss>0){
                 $this->created = $this->getCreateDate();
                $this->nonce = $this->getNoncevalue();
                $this->hashPwd = $this->DigestAlgo($this->password,$this->created,$this->nonce);

                $depature_date = date('dmy',strtotime($segment_data[$j][$ss]['Origin']['DateTime']));
                $arrival_date = date('dmy',strtotime($segment_data[$j][$ss]['Destination']['DateTime']));
                $cabin_class  =$segment_data[$j][$ss]['CabinClass'];
                $OperatorCode = $segment_data[$j][$ss]['OperatorCode'];
                $Origin = $segment_data[$j][$ss]['Origin']['AirportCode'];
                $Destination = $segment_data[$j][$ss]['Destination']['AirportCode'];
                $FlightNumber  = $segment_data[$j][$ss]['FlightNumber'];
                $soapAction = "SMPREQ_17_1_1A";               
                $Header ='<soapenv:Header>
                        <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
                        <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                        <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
                        <link:TransactionFlowLink xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1"/>
                        <oas:Security xmlns:oas="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                        <oas:UsernameToken oas1:Id="UsernameToken-1" xmlns:oas1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                        <oas:Username>'.$this->username.'</oas:Username>
                        <oas:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'.$this->nonce.'</oas:Nonce>
                        <oas:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">'.$this->hashPwd.'</oas:Password>
                        <oas1:Created>'.$this->created.'</oas1:Created>
                    </oas:UsernameToken>
                    </oas:Security>
                    <AMA_SecurityHostedUser xmlns="http://xml.amadeus.com/2010/06/Security_v1">
                    <UserID AgentDutyCode="'.$this->agent_duty_code.'" RequestorType="'.$this->requestor_type.'" PseudoCityCode="'.$this->pseudo_city_code.'" POS_Type="'.$this->pos_type.'"/>
                    </AMA_SecurityHostedUser>
                </soapenv:Header>';
                
            $seat_request =' <?xml version="1.0" encoding="UTF-8"?>           
                    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:typ="http://xml.amadeus.com/2010/06/Type">
                    '.$Header.'
                <soapenv:Body>
                <Air_RetrieveSeatMap xmlns="http://xml.amadeus.com/'.$soapAction.'">            
                    <travelProductIdent>
                                    <flightDate>
                                        <departureDate>'.$depature_date.'</departureDate>
                                    </flightDate>
                                    <boardPointDetails>
                                        <trueLocationId>'.$Origin.'</trueLocationId>
                                    </boardPointDetails>
                                    <offpointDetails>
                                        <trueLocationId>'.$Destination.'</trueLocationId>
                                    </offpointDetails>
                                    <companyDetails>
                                        <marketingCompany>'.$OperatorCode.'</marketingCompany>
                                    </companyDetails>
                                    <flightIdentification>
                                        <flightNumber>'.$FlightNumber.'</flightNumber>
                                        <bookingClass>'.$cabin_class.'</bookingClass>
                                    </flightIdentification>
                                </travelProductIdent>
                            <seatRequestParameters>
                                <processingIndicator>FT</processingIndicator>
                            </seatRequestParameters>
                        </Air_RetrieveSeatMap>
                </soapenv:Body>
                </soapenv:Envelope>';           
            

                    // echo $request_params;exit;
            
            $soap_url = $this->soap_url.$soapAction;
            $seat_response = $this->process_request($seat_request,$this->api_url,'SeatMapReq(amadeus)',$soap_url);      
            //$seat_response = $this->CI->custom_db->get_static_response (15019); 

                if($seat_response){
                    $seat_response = $this->xml2array($seat_response);
//debug($seat_response);exit; 
                    if(isset($seat_response['soapenv:Envelope']['soapenv:Header'])){
                        $SessionId = $seat_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];    
                        $SecurityToken = $seat_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                        $SequenceNumber =  $seat_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'];
                        $SequenceNumber = ($SequenceNumber+1);
                        // $this->Security_SignOut($SessionId,$SequenceNumber,$SecurityToken);
                    }
                    #echo 'SequenceNumber..'.$SequenceNumber.'<br/>';

                    if(isset($seat_response['soapenv:Envelope']['soapenv:Body']['Air_RetrieveSeatMapReply']['seatmapInformation'])){
                        
                        $seat_format_response  = $this->format_seat_mapping_response($seat_response,$segment_data[$j][$ss],$segment_type);
                      
                        if(valid_array( $seat_format_response['seat_data'])){
                            $seatmapresponse[$seg_count]['SeatDetails'] = $seat_format_response['seat_data'];
                            $seatmapresponse[$seg_count]['SeatColumn'] = $seat_format_response['available_columns'];
                            $seatmapresponse[$seg_count]['Description'] = $seat_format_response['description'];
                            $seg_count++;      
                        }
                       
                    }else{
                      // $seatmapresponse[$seg_count]['SeatDetails'] =array();
                       //$seatmapresponse[$seg_count]['SeatColumn'] = array();
                    }
                 }

            //}
                 $segment_type++;
            }
        }
        
        return $seatmapresponse;
    }
    /*Amadeus Seat Format*/
    private function format_seat_mapping_response(array $seat_response, array $segment_data, string $segment_type): array{
       //debug($seat_response);exit;
        $seatmapInformation = $seat_response['soapenv:Envelope']['soapenv:Body']['Air_RetrieveSeatMapReply']['seatmapInformation'];
     
        $final_seat_array = array('available_columns'=>'','seat_data'=>array()); 

        if(!isset($seat_response['soapenv:Envelope']['soapenv:Body']['Air_RetrieveSeatMapReply']['errorInformation'])){
        
           
            if(isset($seatmapInformation['cabin'])){

                $CabinClass_arr = force_multple_data_format($seatmapInformation['cabin']);            
                $rowDetails = force_multple_data_format($seat_response['soapenv:Envelope']['soapenv:Body']['Air_RetrieveSeatMapReply']['seatmapInformation']['row']);
                //debug($CabinClass_arr);  exit;
                // echo "====";          
                //$alpha_char = array('A'=>'A','B'=>'B','C'=>'C','D'=>'D','E'=>'E','F'=>'F','G'=>'G','H'=>'H','I'=>'I','J'=>'J','K'=>'K','L'=>'L','M'=>'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');            
                $airRowDetails = array();
                $compartmentDetails_Row = array();
                if($CabinClass_arr){
                    $available_columns = array();
                    foreach ($CabinClass_arr as $c_key => $c_value) {
                        if(isset($c_value['compartmentDetails']['seatRowRange']['number'])){
                            $start_row=$c_value['compartmentDetails']['seatRowRange']['number'][0];
                            $end_row = $c_value['compartmentDetails']['seatRowRange']['number'][1];
                            if(isset($c_value['compartmentDetails']['defaultSeatOccupation'])){
                                 $row_key = $start_row.'_'.$end_row;
                                 $compartmentDetails_Row[$row_key]['defaultSeatOccupation'] =$c_value['compartmentDetails']['defaultSeatOccupation'];
                            }
                        }
                        if(isset($c_value['compartmentDetails']['columnDetails'])){
                            foreach ($c_value['compartmentDetails']['columnDetails'] as $clkey => $clvalue) { 
                                if(!in_array($clvalue['seatColumn'],$available_columns)){
                                    $available_columns[] = $clvalue['seatColumn'];
                                    if($clvalue['description'] == "A"){
                                        $desc_text = "Aisle";
                                    }
                                    else{
                                        $desc_text = "Window";
                                    }
                                    $description[$clvalue['seatColumn']] = $desc_text;
                                }   
                            }
                            $compartmentDetails_Row[$row_key]['columnDetails'] = $c_value['compartmentDetails']['columnDetails'];
                            
                        }
                    }
                    
                } 
                asort($available_columns);        
                if($rowDetails){
                    foreach ($rowDetails as $r_key => $r_value) {
                          $Row_Number = $r_value['rowDetails']['seatRowNumber'];

                        if(isset($r_value['rowDetails'])){                    
                            $airSeatMapDetails  =array();
                            if(isset($r_value['rowDetails']['seatOccupationDetails'])){
                                  $airSeatMapDetails=$this->seat_format_row($r_key,$r_value,$segment_data,$compartmentDetails_Row,$available_columns,$segment_type);  
                            }else{
                                $airSeatMapDetails = array();
                                //need to take from compartment details
                                $Default_compartment_details = $this->checking_compartment_data($r_value,$compartmentDetails_Row);
                               
                                $r_value['rowDetails']['seatOccupationDetails'] =$Default_compartment_details['seatOccupationDetails'];  
                                $airSeatMapDetails=$this->seat_format_row($r_key,$r_value,$segment_data,$compartmentDetails_Row,$available_columns,$segment_type);
                            }                
                            $airSeatMapDetails_arr = array();
                            //to insert missing columns
                           //debug($available_columns);exit;
                            foreach ($available_columns as $av_key => $av_value) {
                               if(isset($airSeatMapDetails[$av_value])){
                                  $airSeatMapDetails_arr[] =$airSeatMapDetails[$av_value];
                               }else{
                                    $missin_cl_update['AvailablityType'] =0;
                                    $missin_cl_update['Destination'] =  $segment_data['Destination']['AirportCode'];
                                    $missin_cl_update['FlightNumber'] =$segment_data['FlightNumber']; 
                                    $missin_cl_update['Origin'] = $segment_data['Origin']['AirportCode'];
                                    $missin_cl_update['Price'] = 0;
                                    $missin_cl_update['RowNumber'] = $Row_Number;
                                    $missin_cl_update['SeatColumn'] = $av_value;
                                    $missin_cl_update['SeatNumber'] =$Row_Number.$av_value;
                                    $key['key'][0]['Code'] = $missin_cl_update['SeatNumber'];
                                    $key['key'][0]['Type'] ='dynamic';
                                    $key['key'][0]['Description'] ='no row';
                                    $key['key'][0]['Segement_Type'] =$segment_type;

                                    $ResultToken = serialized_data($key['key']);
                                    $missin_cl_update['SeatId'] = $ResultToken;
                                    $airSeatMapDetails_arr[] = $missin_cl_update;
                               }
                            }
                            
                           
                            $airRowDetails[$r_key] =$airSeatMapDetails_arr;
                        }
                    }
                }
               
                $final_seat_array = array('available_columns'=>$available_columns,'seat_data'=>$airRowDetails, 'description' => $description);  
            }
        }
        else{
            $final_seat_array = array('available_columns'=>'','seat_data'=>array(), 'description'=>''); 
        }
             
       //debug($description);exit;
        return $final_seat_array;
        
    }
    
   
    /*Taking the compartment data*/
    private function checking_compartment_data(array $r_value, array $compartmentDetails_Row): array{
       

        $Default_compartment_details = array();
        $Row_Number = $r_value['rowDetails']['seatRowNumber'];
        $seat_char = '';
        if(isset($r_value['rowDetails']['rowCharacteristicDetails'])){
           if(isset($r_value['rowDetails']['rowCharacteristicDetails']['rowCharacteristic'])){
                 $seat_char = $r_value['rowDetails']['rowCharacteristicDetails']['rowCharacteristic'];
            }
        }
        foreach ($compartmentDetails_Row as $ch_key => $ch_value) {
           $explode_arr  = explode("_",$ch_key);
           #debug($ch_value);

            for ($i=$explode_arr[0]; $i <=$explode_arr[1] ; $i++) { 
                
                if($i==$Row_Number){
                    
                    foreach ($ch_value['columnDetails'] as $cc_key => $cc_value) {
                       
                    
                        $ch_value['columnDetails'][$cc_key]['seatOccupation'] = $ch_value['defaultSeatOccupation'];
                        if($seat_char){
                            $ch_value['columnDetails'][$cc_key]['seatCharacteristic'] = $seat_char;    
                        }else{
                            $ch_value['columnDetails'][$cc_key]['seatCharacteristic'] = @$cc_value['description'];
                        }                        
                    }
                    $Default_compartment_details['defaultSeatOccupation'] = $ch_value['defaultSeatOccupation'];
                    $Default_compartment_details['seatOccupationDetails'] = $ch_value['columnDetails'];
                }
            }

        } 
        return $Default_compartment_details;
    }
    /*formating seat rows*/
    private function seat_format_row(string $r_key, array $r_value, array $segment_data, array $compartmentDetails_Row, array $available_columns, string $segment_no): array{
        $airSeatMapDetails  =array();        
        $r_value['rowDetails']['seatOccupationDetails'] = force_multple_data_format($r_value['rowDetails']['seatOccupationDetails']);
        foreach ($r_value['rowDetails']['seatOccupationDetails'] as $rcl_key => $rcl_value) {          
            $Row_Number = $r_value['rowDetails']['seatRowNumber'];
            $airSeatMapDetails[$rcl_value['seatColumn']]['AirlineCode'] = $segment_data['OperatorCode'];
            //Availability type 
            //0-blocked,1 - available 2- space
            $available_seat = 0;
            $is_available =0;
             $check_character = array();     
            # debug($rcl_value);
             #echo 'Row....'.$Row_Number.'<br/>';        
            if(!isset($rcl_value['seatOccupation']) || !isset($rcl_value['seatCharacteristic'])){                
                $Default_compartment_details = array();
                $Default_compartment_details = $this->checking_compartment_data($r_value,$compartmentDetails_Row);              
                if(!isset($rcl_value['seatOccupation'])){
                    $rcl_value['seatOccupation'] = $Default_compartment_details['defaultSeatOccupation'];
                }
            }
            $is_char='';
            if(isset($rcl_value['seatOccupation'])){
                $is_available = $this->seat_occupation($rcl_value['seatOccupation']);

                if($is_available){

                    if(isset($rcl_value['seatCharacteristic'])){
                         if(is_array($rcl_value['seatCharacteristic'])){
                           
                            foreach ($rcl_value['seatCharacteristic'] as $sc_key => $sc_value) {
                              $is_char = $this->seat_characteristics($sc_value);

                                if($is_char=='seat_availble'||$is_char=='infant_adult_seat' || $is_char =='center_seat'                        
                                    ){
                                    //$available_seat = 1;
                                    $check_character[] = 1;
                                }else{
                                    $check_character[]=0;
                                }
                                
                            }
                        }else{
                            $is_char = $this->seat_characteristics($rcl_value['seatCharacteristic']);

                           
                            if($is_char=='seat_availble'||$is_char=='infant_adult_seat' || $is_char =='center_seat'){
                                    $check_character[] = 1;
                                }else{
                                    $check_character[]=0;
                                }

                        }
                    }else{
                       
                        foreach ($Default_compartment_details['seatOccupationDetails'] as $char_key => $char_value) {
                            
                            if($char_value['seatColumn']==$rcl_value['seatColumn']){
                                if(isset($char_value['description'])){
                                    $is_char = $this->seat_characteristics($char_value['description']);
                                }else{
                                    $is_char ='blocked';
                                }
                                
                                if($is_char=='seat_availble'||$is_char=='infant_adult_seat' || $is_char =='center_seat'){
                                        $check_character[] = 1;
                                    }else{
                                        $check_character[] = 0;
                                    }
                                }
                        }
                        
                    }
                   
                }else{
                    $check_character[]=0;
                }
            }
           
            $check_character = array_unique($check_character);
            if(in_array(0,$check_character)){
                $available_seat =0;
            }else{
                $available_seat =1;
            }
            
            $airSeatMapDetails[$rcl_value['seatColumn']]['AvailablityType'] =$available_seat;
            $airSeatMapDetails[$rcl_value['seatColumn']]['Destination'] =  $segment_data['Destination']['AirportCode'];
            $airSeatMapDetails[$rcl_value['seatColumn']]['FlightNumber'] =$segment_data['FlightNumber']; 
            $airSeatMapDetails[$rcl_value['seatColumn']]['Origin'] = $segment_data['Origin']['AirportCode'];
            $airSeatMapDetails[$rcl_value['seatColumn']]['Price'] = 0;
            $airSeatMapDetails[$rcl_value['seatColumn']]['RowNumber'] = $Row_Number;
            $airSeatMapDetails[$rcl_value['seatColumn']]['SeatColumn'] = $rcl_value['seatColumn'];
            $airSeatMapDetails[$rcl_value['seatColumn']]['SeatNumber'] =$Row_Number.$rcl_value['seatColumn'];
             $key['key'][0]['Code'] = $airSeatMapDetails[$rcl_value['seatColumn']]['SeatNumber'];
             $key['key'][0]['Type'] ='dynamic';
             $key['key'][0]['Description'] =$is_char;    
             $key['key'][0]['Segment_Type'] = $segment_no;         
            $ResultToken = serialized_data($key['key']);
            $airSeatMapDetails[$rcl_value['seatColumn']]['SeatId'] = $ResultToken;
        }
        return $airSeatMapDetails;
    }
    /*Amadeus Seat Occupation Details*/
    private function seat_occupation(string $code): int{
        $avaiable_status = false;
        switch ($code) {
            case 'F':
                $avaiable_status = 1;
                break;
            case 'G':
                $avaiable_status = 0;
                break;
            case 'O':
                $avaiable_status = 0;
                break;
            case 'Z':
                $avaiable_status = 0;
                break;                
            
            default:
                $avaiable_status =0;
                break;
        }
        return $avaiable_status;
    }
    /*Seat Characteristics*/
    private function seat_characteristics(mixed $value): string{
        $seat_characteristics = "";
        switch ($value) {
            case '1':
                $seat_characteristics = "restricted_seat";
                break;
            case '2':
                $seat_characteristics = "leg_rest_avaiable";
                break;
            case '3':
                $seat_characteristics = "video_screen";
                break;
            case '8':
                $seat_characteristics = "blocked";//no seat at this location
            break;
             case '9':
            $seat_characteristics = "center_seat";//available
            break;
             case '1A':
            $seat_characteristics = "seat_availble";//seat_available for adult
            break;
             case '1B':
            $seat_characteristics = "blocked";//Seat not available for medical.
            break;
            case '1C':
            $seat_characteristics = "blocked";// Seat not available for unaccompanied minor.
            break;
             case '1D':
            $seat_characteristics = "blocked";//    Restricted reclined seat.
            break;
             case '1M':
            $seat_characteristics = "seat_availble";//Seat with movie view
            break;
             case '1W':
            $seat_characteristics = "seat_availble";//Window seat without window.
            break;
             case '3A':
            $seat_characteristics = "seat_availble";// Individual video screen - No choice of movie.
            break;
             case '6A':
            $seat_characteristics = "seat_availble";//In front of galley seat.
            break;
             case '6B':
            $seat_characteristics = "seat_availble";//Behind galley seat.
            break;
             case '7A':
            $seat_characteristics = "blocked";//In front of toilet seat.
            break;
             case '7B':
            $seat_characteristics = "blocked";//Behind toilet seat.
            break;
             case 'A':
            $seat_characteristics = "seat_availble";//Asile
            break;
             case 'AB':
            $seat_characteristics = "seat_availble";//  Seat adjacent to bar.
            break;
             case 'AC':
            $seat_characteristics = "seat_availble";//Seat adjacent to closet.
            break;
            case 'AG':
            $seat_characteristics = "seat_availble";//Seat adjacent to galley.
            break; 
            case 'AJ':
            $seat_characteristics = "seat_availble";//Adjacent aisle seat.
            break;
            case 'AL':
            $seat_characteristics = "seat_availble";//Seat adjacent to lavatory.
            break;
            case 'AM':
            $seat_characteristics = "seat_availble";//Individual movie screen - No choice of movie selection.
            break;
            case 'AS':
            $seat_characteristics = "seat_availble";//Individual airphone.
            break;
            case 'AT':
            $seat_characteristics = "seat_availble";//Seat adjacent to table.
            break;
            case 'AU':
            $seat_characteristics = "seat_availble";//Seat adjacent to stairs to upper deck.seat.
            break;
            case 'B':
            $seat_characteristics = "seat_availble";//Seat with bassinet facility.seat.
            break;
            case 'C':
            $seat_characteristics = "seat_availble";//Crew seat. facility.seat.
            break;
            case 'CH':
            $seat_characteristics = "blocked";//Chargeable seat.facility.seat.
            break;
            case 'DE':
            $seat_characteristics = "blocked";//Seat suitable for deportee.
            break;
            case 'EC':
            $seat_characteristics = "blocked";//    Electronic connection for laptop or Fax machine.
            break;
            case 'EK':
            $seat_characteristics = "seat_availble";//Economy comfort seat.
            break;
            case 'H':
            $seat_characteristics = "blocked";//Seat with facility for handicapped/incapacitated passenger.
            break;
            case 'I':
            $seat_characteristics = "seat_availble";//Seat suitable for adult with infant.
            break;
              case 'IE':
            $seat_characteristics = "blocked";// Seat not suitable for child.
            break;
              case 'J':
            $seat_characteristics = "seat_availble";//Rear facing seat.
            break;
            case 'K':
            $seat_characteristics = "blocked";//Bulkhead seat.
            break;
            case 'KA':
            $seat_characteristics = "blocked";//Bulkhead seat with movie screen.
            break;
            case 'L':
            $seat_characteristics ="seat_availble";//  Leg space seat.
            break;
            case 'LS':
            $seat_characteristics ="seat_availble";// Left side of aircraft.
            break;
            case 'M':
            $seat_characteristics ="seat_availble";//  Seat without a movie view.
            break;
            case 'N':
            $seat_characteristics ="blocked";//  NonSmokeing seat
            break;
            case 'O':
            $seat_characteristics ="blocked";// Preferential seat.
            break;
            case 'OW':
            $seat_characteristics ="blocked";// Overwing seat.
            break;
            case 'PC':
            $seat_characteristics ="blocked";//Pet cabin.
            break;
            case 'Q':
            $seat_characteristics ="blocked";//Seat in a quiet zone.
            break;
            case 'RS':
            $seat_characteristics ="seat_availble";//Right side of aircraft.
            break;
            case 'S':
            $seat_characteristics ="blocked";// Smoking seat.
            break;
            case 'U':
            $seat_characteristics ="blocked";//Seat suitable for unaccompanied minor.
            break;
            case 'UP':
            $seat_characteristics ="seat_availble";// Upper deck seat.
            break;
            case 'V':
            $seat_characteristics ="seat_availble";// Seat to be left vacant or last offered.
            break;
            case 'W':
            $seat_characteristics ="seat_availble";// Window seat.
            break;
            case 'WA':
            $seat_characteristics ="seat_availble";// Window and Aisle together.
            break;
            case 'X':
            $seat_characteristics ="blocked";// No facility seat (indifferent seat).
            break;
            default:
            $seat_characteristics = "blocked";
            break;
        }
        return $seat_characteristics;
    } 
      /**
     * Process Cancel Booking
     * Online Cancellation
     */
    public function cancel_booking(array $request): array {
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $app_reference = $request['AppReference'];
        $sequence_number = $request['SequenceNumber'];
        $IsFullBookingCancel = $request['IsFullBookingCancel'];
        $ticket_ids = $request['TicketId'];
        $pnr_no = $request['PNR'];        
        // debug($request);exit;
        $elgible_for_ticket_cancellation = $this->CI->common_flight->elgible_for_ticket_cancellation($app_reference, $sequence_number, $ticket_ids, $IsFullBookingCancel, $this->booking_source);
        //debug($elgible_for_ticket_cancellation);exit;
        if ($elgible_for_ticket_cancellation['status'] == SUCCESS_STATUS) {
            $booking_details = $this->CI->flight_model->get_flight_booking_transaction_details($app_reference, $sequence_number, $this->booking_source);
            $booking_details = $booking_details['data'];
            $booking_transaction_details = $booking_details['booking_transaction_details'][0];
            $flight_booking_transaction_details_origin = $booking_transaction_details['origin'];

            $request_params = $booking_details;
            $request_params['passenger_origins'] = $ticket_ids;
            $request_params['IsFullBookingCancel'] = $IsFullBookingCancel;
            $request_params['PNR_NO'] = $pnr_no;
            //debug($request_params);exit;
            //SendChange Request
            $send_change_request = $this->pnr_cancel_request($request_params);

            if ($send_change_request['status'] == SUCCESS_STATUS) {
                $response ['status'] = SUCCESS_STATUS;
                $response ['message'] = 'Cancellation Done';
               // $send_change_response = $send_change_request['data']['send_change_response'];
                $passenger_origin = $request_params['passenger_origins'];
                foreach ($passenger_origin as $origin) {
                    $this->CI->common_flight->update_ticket_cancel_status($app_reference, $sequence_number, $origin);
                }
            }
            else {
                $response ['message'] = $send_change_request['message'];
            }
        }
        else {
            $response ['message'] = $elgible_for_ticket_cancellation['message'];
        }
        return $response;
    }
     /**
     * Send ChangeRequest
     * @param unknown_type $booking_details
     * //ChangeRequestStatus: NotSet = 0,Unassigned = 1,Assigned = 2,Acknowledged = 3,Completed = 4,Rejected = 5,Closed = 6,Pending = 7,Other = 8
     */
    private function pnr_cancel_request(array $request_params): array {
        $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];
        $pnr_req = [];
        if($request_params['PNR_NO']){
            $pnr_req['SessionId'] = '';
            $pnr_req['SequenceNumber'] ='';
            $pnr_req['SecurityToken'] ='';
            $pnr_req['Pnr_No']=$request_params['PNR_NO'];
            $pnr_rerieve_response = $this->PNRRetrieve($pnr_req);
            
            if($pnr_rerieve_response['status']==SUCCESS_STATUS){
                
                $cancel_second_response = $this->pnr_canel_second_request($pnr_rerieve_response);
                $soapAction = "PNRXCL_17_1_1A";
                $soap_url = $this->soap_url.$soapAction;
                $this->api_url = $this->api_url;
                $this->api_url = 'https://noded2.test.webservices.amadeus.com/1ASIWCTIZ77';
                $pnr_cancel_response = $this->process_request($cancel_second_response,$this->api_url,'PNR_Cancel(amadeus)',$soap_url);
                $this->CI->custom_db->generate_static_response ($pnr_cancel_response,'Amadeus Flight PNR_Cancel Request' );
                $this->CI->custom_db->generate_static_response ($pnr_cancel_response,'Amadeus Flight PNR_Cancel Response' );
              
                if($pnr_cancel_response){
                    $pnr_cancel_response = $this->xml2array($pnr_cancel_response);
                    $session_id = $pnr_cancel_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SessionId'];
                    $sequence_number = $pnr_cancel_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SequenceNumber'] + 1;
                    $security_token = $pnr_cancel_response['soapenv:Envelope']['soapenv:Header']['awsse:Session']['awsse:SecurityToken'];
                    $control_number = $pnr_cancel_response['soapenv:Envelope']['soapenv:Body']['PNR_Reply']['pnrHeader']['reservationInfo']['reservation']['controlNumber'];
                    if(!empty($pnr_cancel_response)){
                        $pnr_add_elements = $this->pnr_add_multi_elements($session_id,$sequence_number,$security_token, $control_number);

                        $pnr_add_elements = $this->xml2array($pnr_add_elements);
                        if($pnr_add_elements['soapenv:Envelope']['soapenv:Body']['PNR_Reply']['pnrHeader']['reservationInfo']['reservation']['controlNumber'] == $request_params['PNR_NO']){
                           $response ['status'] = SUCCESS_STATUS;
                           $response ['message'] = 'Cancelled successfully';
                        }                          
                    }
                }   
            }
        }
        return $response;
    }
    public function pnr_canel_second_request(string $pnr_rerieve_response): array{
        $soapAction = "PNRXCL_17_1_1A";
        $xml_pnrCancle = '<?xml version="1.0" encoding="UTF-8"?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3" xmlns:iat="http://www.iata.org/IATA/2007/00/IATA2010.1" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1">
                    <soapenv:Header xmlns:add="http://www.w3.org/2005/08/addressing">
                        <awsse:Session TransactionStatusCode="InSeries">
                            <awsse:SessionId>'.$pnr_rerieve_response['data']['SessionId'].'</awsse:SessionId>
                            <awsse:SequenceNumber>'.$pnr_rerieve_response['data']['SequenceNumber'].'</awsse:SequenceNumber>
                            <awsse:SecurityToken>'.$pnr_rerieve_response['data']['SecurityToken'].'</awsse:SecurityToken>
                        </awsse:Session>
                        <add:MessageID>'.$this->getuuid().'</add:MessageID>
                        <add:Action>http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                        <add:To>'.trim($this->api_url).'</add:To>
                    </soapenv:Header>
                   <soapenv:Body>
                   <PNR_Cancel>
                        <reservationInfo>
                            <reservation>
                                <controlNumber>'.$pnr_rerieve_response['data']['Pnr_No'].'</controlNumber>
                            </reservation>
                        </reservationInfo>
                        <pnrActions>
                            <optionCode>0</optionCode>
                        </pnrActions>
                        <cancelElements>
                            <entryType>I</entryType>
                        </cancelElements>
                    </PNR_Cancel>
                   </soapenv:Body>
                </soapenv:Envelope>';
            return $xml_pnrCancle;
    }
    public function pnr_add_multi_elements(int $session_id, int $sequence_number, string $security_token, int $control_number): array{

        $soapAction = $this->config['PNR_AddMultiElements'];
                $xml_pnrCancle = '<?xml version="1.0" encoding="UTF-8"?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3" xmlns:iat="http://www.iata.org/IATA/2007/00/IATA2010.1" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1">
                    <soapenv:Header xmlns:add="http://www.w3.org/2005/08/addressing">
                        <awsse:Session TransactionStatusCode="InSeries">
                            <awsse:SessionId>'.$session_id.'</awsse:SessionId>
                            <awsse:SequenceNumber>'.$sequence_number.'</awsse:SequenceNumber>
                            <awsse:SecurityToken>'.$security_token.'</awsse:SecurityToken>
                        </awsse:Session>
                        <add:MessageID>'.$this->getuuid().'</add:MessageID>
                        <add:Action>http://webservices.amadeus.com/'.$soapAction.'</add:Action>
                        <add:To>'.trim($this->api_url).'</add:To>
                    </soapenv:Header>
                   <soapenv:Body>
                   <PNR_AddMultiElements>
                        <pnrActions>
                            <optionCode>10</optionCode>
                        </pnrActions>
                        <dataElementsMaster>
                            <marker1/>
                            <dataElementsIndiv>
                                <elementManagementData>
                                    <segmentName>RF</segmentName>
                                </elementManagementData>
                                <freetextData>
                                    <freetextDetail>
                                        <subjectQualifier>3</subjectQualifier>
                                        <type>P22</type>
                                    </freetextDetail>
                                    <longFreetext>FREE TEXT</longFreetext>
                                </freetextData>
                            </dataElementsIndiv>
                        </dataElementsMaster>
                    </PNR_AddMultiElements>
                   </soapenv:Body>
                </soapenv:Envelope>';
                $soap_url = $this->soap_url.$soapAction;
                $this->api_url = $this->api_url;
                //$this->api_url = 'https://noded2.test.webservices.amadeus.com/1ASIWCTIZ77';
                $this->CI->custom_db->generate_static_response ($xml_pnrCancle,'Amadeus Flight PNR_Multi10_Cancel Request' );
                $pnr_cancel_response = $this->process_request($xml_pnrCancle,$this->api_url,'PNR_CancelADD(amadeus)',$soap_url);
                $this->CI->custom_db->generate_static_response ($pnr_cancel_response,'Amadeus Flight PNR_Multi10_Cancel Response' );
                return $pnr_cancel_response;
    }
    /**
     * Forms the SendChangeRequest
     * @param unknown_type $request
     */
    private function format_send_change_request(array $params): array {
        // debug($params);exit;
        // echo 'herrer I am';exit;
        $booking_transaction_details = $params['booking_transaction_details'][0];
        $pnr = trim($booking_transaction_details['pnr']);
        $travel_read_itinerary_request = $this->TravelItineraryReadInfo_Request($pnr);
        $this->process_request($travel_read_itinerary_request ['request'], $travel_read_itinerary_request ['url'], $travel_read_itinerary_request ['remarks']);    
        $cancel_request = $this->OTA_CancelRQ();
        // debug($cancel_request);exit;
        $cancel_response = $this->process_request($cancel_request ['request'], $cancel_request ['url'], $cancel_request ['remarks']);    
        $end_transaction_request = $this->EndTransaction_Request($pnr);
        $this->process_request($end_transaction_request ['request'], $end_transaction_request ['url'], $end_transaction_request ['remarks']);    
        $request ['status'] = SUCCESS_STATUS;
        $request ['cancel_response'] = $cancel_response;
        return $request;
    }
    function OTA_CancelRQ(): array{
        $request = [];
        $request_params = "<?xml version='1.0' encoding='utf-8'?>
                  <soap-env:Envelope xmlns:soap-env='http://schemas.xmlsoap.org/soap/envelope/'>
                      <soap-env:Header>
                          <eb:MessageHeader xmlns:eb='http://www.ebxml.org/namespaces/messageHeader'>
                              <eb:From>
                                  <eb:PartyId eb:type='urn:x12.org.IO5:01'>".$this->config['sabre_email']."</eb:PartyId>
                              </eb:From>
                              <eb:To>
                                  <eb:PartyId eb:type='urn:x12.org.IO5:01'>webservices.sabre.com</eb:PartyId>
                              </eb:To>
                              <eb:ConversationId>".$this->conversation_id."</eb:ConversationId>
                                <eb:Service>OTA_CancelLLSRQ</eb:Service>
                                <eb:Action>OTA_CancelLLSRQ</eb:Action>
                              <eb:CPAID>".$this->config['ipcc']."</eb:CPAID>
                              <eb:MessageData>
                                  <eb:MessageId>".$this->message_id."</eb:MessageId>
                                  <eb:Timestamp>".$this->timestamp."</eb:Timestamp>
                                  <eb:TimeToLive>".$this->timetolive."</eb:TimeToLive>
                              </eb:MessageData>
                          </eb:MessageHeader>
                          <wsse:Security xmlns:wsse='http://schemas.xmlsoap.org/ws/2002/12/secext'>
                              <wsse:UsernameToken>
                                  <wsse:Username>".$this->config['username']."</wsse:Username>
                                  <wsse:Password>".$this->config['password']."</wsse:Password>
                                  <Organization>".$this->config['ipcc']."</Organization>
                                  <Domain>Default</Domain>
                              </wsse:UsernameToken>
                              <wsse:BinarySecurityToken>".$this->api_session_id."</wsse:BinarySecurityToken>
                          </wsse:Security>
                      </soap-env:Header>
                      <soap-env:Body>
                        <OTA_CancelRQ Version='2.0.0' xmlns='http://webservices.sabre.com/sabreXML/2011/10' xmlns:xs='http://www.w3.org/2001/XMLSchema' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>
                            <Segment Type='air'/>
                        </OTA_CancelRQ>                   
                      </soap-env:Body>
                  </soap-env:Envelope>";
        $request ['request'] = $request_params;
        $request ['url'] = $this->config['api_url'];
        $request ['remarks'] = 'Cancellation(Sabare)';
        $request ['status'] = SUCCESS_STATUS;
        // debug($request);exit;
        return $request;
    }
     private function save_flight_ticket_details(array $booking_params, string $airline_pnr, string $app_reference, string $sequence_number, int $search_id): void{
        // debug($booking_response);exit;
        $flight_booking_transaction_details_fk = $this->CI->custom_db->single_table_records('flight_booking_transaction_details', 'origin', array('app_reference' => $app_reference, 'sequence_number' => $sequence_number));
        $flight_booking_itinerary_details_fk = $this->CI->custom_db->single_table_records('flight_booking_itinerary_details', 'airline_code,origin', array('app_reference' => $app_reference));

        $flight_booking_transaction_details_fk = $flight_booking_transaction_details_fk['data'][0]['origin'];
        $update_data['airline_pnr'] = $airline_pnr;       
        $update_condition['app_reference'] = $app_reference;
        $update_condition['sequence_number'] = $sequence_number;
        $this->CI->custom_db->update_record('flight_booking_transaction_details', $update_data, $update_condition);
        $flight_booking_status = 'BOOKING_CONFIRMED';
        $this->CI->common_flight->update_flight_booking_status($flight_booking_status, $app_reference, $sequence_number, $this->booking_source);
        $passenger_details = $this->CI->custom_db->single_table_records('flight_booking_passenger_details', '', array('app_reference' => $app_reference));
        $passenger_details =$passenger_details['data'];
        // debug($flight_booking_itinerary_details_fk);exit;
        foreach($flight_booking_itinerary_details_fk['data'] as $itinerary){
            $update_itinerary_condition = array();
            $update_itinerary_condition['flight_booking_transaction_details_fk'] = $itinerary['origin'];
            $update_itinerary_condition['app_reference'] = $app_reference;
            //itinerary updated data
            $update_itinerary_data = array();
           
            $update_itinerary_data['airline_pnr'] = $airline_pnr;
            $GLOBALS['CI']->custom_db->update_record('flight_booking_itinerary_details', $update_itinerary_data, $update_itinerary_condition);
        }
        $itineray_price_details = $booking_params['flight_data']['PriceBreakup'];
        // debug($itineray_price_details);exit;
        $airline_code = '';
        if (isset($flight_booking_itinerary_details_fk['data'][0]['airline_code'])) {
            $airline_code = $flight_booking_itinerary_details_fk['data'][0]['airline_code'];
        }
        $flight_price_details = $this->CI->common_flight->final_booking_transaction_fare_details($itineray_price_details, $search_id, $this->booking_source, $airline_code);
        // debug($flight_price_details);exit;
        $fare_details = $flight_price_details['Price'];
        $fare_breakup = $flight_price_details['PriceBreakup'];
        $passenger_breakup = $fare_breakup['PassengerBreakup'];
        $single_pax_fare_breakup = $this->CI->common_flight->get_single_pax_fare_breakup($passenger_breakup);
       // debug($single_pax_fare_breakup);exit;

        // $passenger_details = force_multple_data_format($passenger_details);
        $get_passenger_details_condition = array();
        $get_passenger_details_condition['flight_booking_transaction_details_fk'] = $flight_booking_transaction_details_fk;
        $passenger_details_data = $GLOBALS['CI']->custom_db->single_table_records('flight_booking_passenger_details', 'origin, passenger_type', $get_passenger_details_condition);

        $passenger_details_data = $passenger_details_data['data'];

        $passenger_origins = group_array_column($passenger_details_data, 'origin');
        $passenger_types = group_array_column($passenger_details_data, 'passenger_type');
        // echo 'mnngng';

        foreach ($passenger_details as $pax_k => $pax_v) {
            $passenger_fk = intval(array_shift($passenger_origins));
            $pax_type = array_shift($passenger_types);
            
            switch ($pax_type) {
                case 'Adult':
                 $pax_type = 'ADT';
                    break;
                case 'Child':
                    $pax_type = 'CHD';
                    break;
                case 'Infant':
                    $pax_type = 'INF';
                    break;
            }
            $ticket_id = '';
            $ticket_number = '';
            //Update Passenger Ticket Details
            $this->CI->common_flight->update_passenger_ticket_info($passenger_fk, $ticket_id, $ticket_number, $single_pax_fare_breakup[$pax_type]);
        }
    }
     /**
     * Save Book Service Response
     * @param unknown_type $book_response
     * @param unknown_type $app_reference
     * @param unknown_type $sequence_number
     */
    private function save_book_response_details(string $pnr, string $app_reference, string $sequence_number): void {
        $update_data = array();
        $update_condition = array();

        $update_data['pnr'] = $pnr;
        $update_data['book_id'] = $pnr;

        // debug($update_data);exit;
        $update_condition['app_reference'] = $app_reference;
        $update_condition['sequence_number'] = $sequence_number;

        $this->CI->custom_db->update_record('flight_booking_transaction_details', $update_data, $update_condition);

        $flight_booking_status = 'BOOKING_HOLD';
        $this->CI->common_flight->update_flight_booking_status($flight_booking_status, $app_reference, $sequence_number, $this->booking_source);
    }
    private function valid_travelintinerary_response(array $api_response): array
    {
        $response = false;
        if(isset($api_response['soap-env:Envelope']['soap-env:Body']['TravelItineraryAddInfoRS']['stl:ApplicationResults']['stl:Error']) ==false){
            $response = true;
        }
        return $response;
    }
    private function valid_airbook_response(array $api_response): array
    {
        $response = false;
        if(isset($api_response['soap-env:Envelope']['soap-env:Body']['EnhancedAirBookRS']['stl:ApplicationResults']['stl:Error']) ==false){
            $response = true;
        }
        return $response;
    }

    private function valid_specialservice_response(array $api_response): array
    {
        $response = false;
        if(isset($api_response['soap-env:Envelope']['soap-env:Body']['SpecialServiceRS']['stl:ApplicationResults']['stl:Error']) ==false){
            $response = true;
        }
        return $response;
    }
     /**
     * Authentication Request
     */
    public function get_authentication_request(bool $internal_request = false): array {
         $response = [
            'status' => FAILURE_STATUS,
            'message' => '',
            'data' => [],
        ];

        $active_booking_source = $this->is_active_booking_source();
        
        if ($active_booking_source['status'] == SUCCESS_STATUS) {
            $authenticate_request = $this->authenticate_request();

            if ($authenticate_request['status'] = SUCCESS_STATUS) {
                $response ['status'] = SUCCESS_STATUS;
                $curl_request = $this->form_curl_params($authenticate_request['request'], $authenticate_request['url']);

                $response ['data'] = $curl_request['data'];
            }
            if ($internal_request == true) {
                $response ['data']['remarks'] = 'Authentication(Sabare)';
            }
        }

        return $response;
    }
     /**
     * Authentcation RQ for api
     */
    private function authenticate_request(): array {
        $request = array();
        $authentication_request = 
        '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <eb:MessageHeader xmlns:eb="http://www.ebxml.org/namespaces/messageHeader">
                        <eb:From>
                            <eb:PartyId eb:type="urn:x12.org.IO5:01">'.$this->config['sabre_email'].'</eb:PartyId>
                        </eb:From>
                        <eb:To>
                            <eb:PartyId eb:type="urn:x12.org.IO5:01">webservices3.sabre.com</eb:PartyId>
                        </eb:To>
                        <eb:ConversationId>'.$this->conversation_id.'</eb:ConversationId>
                        <eb:Service eb:type="SabreXML">Session</eb:Service>
                        <eb:Action>SessionCreateRQ</eb:Action>
                        <eb:CPAID>'.$this->config['ipcc'].'</eb:CPAID>
                        <eb:MessageData>
                            <eb:MessageId>'.$this->message_id.'</eb:MessageId>
                            <eb:Timestamp>'.$this->timestamp.'</eb:Timestamp>
                            <eb:TimeToLive>'.$this->timetolive.'</eb:TimeToLive>
                        </eb:MessageData>
                    </eb:MessageHeader>
                    <wsse:Security xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/12/secext">
                        <wsse:UsernameToken>
                            <wsse:Username>'.$this->config['username'].'</wsse:Username>
                            <wsse:Password>'.$this->config['password'].'</wsse:Password>
                            <Organization>'.$this->config['ipcc'].'</Organization>
                            <Domain>Default</Domain>
                        </wsse:UsernameToken>
                    </wsse:Security>
                </soap-env:Header>
                <soap-env:Body>
                    <SessionCreateRQ>
                        <POS>
                            <Source PseudoCityCode="'.$this->config['ipcc'].'" />
                        </POS>
                    </SessionCreateRQ>
                </soap-env:Body>
            </soap-env:Envelope>';
        $request ['request'] = $authentication_request;
        $request ['url'] = $this->config['api_url'];
        $request ['status'] = SUCCESS_STATUS;
        // debug($request);exit;
        return $request;
    }
    /**
     * process soap API request
     *
     * @param string $request
    */
    function form_curl_params(array $request, string $url, string $soap_url = ''): array {
         $data = [
            'status' => SUCCESS_STATUS,
            'message' => '',
            'data' => [],
        ];
        if($soap_url == 'http://webservices.amadeus.com/FMPTBQ_14_3_1A'){
           $strlenth = strlen($request[0]);
        }
        else{
            $strlenth = strlen($request);
        }
        $curl_data = array();
        $curl_data['booking_source'] = $this->booking_source;
        $curl_data['request'] = $request;
        $curl_data['url'] = $url;
        $curl_data['header'] = array('Content-Type: text/xml; charset="utf-8"', 
            'Content-Length: '.$strlenth, 
            //'Accept-Encoding: gzip,deflate',
            'Accept: text/xml', 
            'Cache-Control: no-cache', 
            'Pragma: no-cache',
            'SOAPAction: "'.$soap_url.'"');

        $data['data'] = $curl_data;
        // debug($data);exit;
        return $data;
    }
    /**
     * Process API Request
     * @param unknown_type $request
     * @param unknown_type $url
     */
    function process_request(string $request, string $url, string $remarks = '', string $soap_url): string {
        //echo $request;exit;
        $insert_id = $this->CI->api_model->store_api_request($url, $request, $remarks);
        $insert_id = intval(@$insert_id['insert_id']);
        try {
            $headers =  array('Content-Type: text/xml; charset="utf-8"', 
            'Content-Length: '.strlen($request), 
            //'Accept-Encoding: gzip,deflate',
            'Accept: text/xml', 
            'Cache-Control: no-cache', 
            'Pragma: no-cache',
            'SOAPAction: "'.$soap_url.'"');
            //debug($headers);exit;
            
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, trim($url)); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 300); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request); 
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $response = curl_exec($ch);
            //if($remarks == 'PNR_Cancel(amadeus)'){
               /* echo 'REMARKS****** : ';
                debug($response);*/
            //}
           
            #echo $response;exit;
            $error = curl_getinfo($ch);
             //debug($error);exit;
        } catch (Exception $e) {
            $response = 'No Response Recieved From API';
        }
        //Update the API Response
        $this->CI->api_model->update_api_response($response, $insert_id);
        /*$final_de = date('Ymd_His')."_".rand(1,10000);
        $XmlReqFileName = $remarks.'_Req'.$final_de; 
        $XmlResFileName = $remarks.'_Res'.$final_de;
        $fp = fopen(FCPATH.'amadeuslogs/'.$XmlReqFileName.'.xml', 'a+');
        fwrite($fp, $request);
        fclose($fp);
        $fp = fopen(FCPATH.'amadeuslogs/'.$XmlResFileName.'.xml', 'a+');
        fwrite($fp, $response);
        fclose($fp);*/
        $error = curl_error($ch);
        curl_close($ch);
        return $response;
    }
    /**
     * Process API Request
     * @param unknown_type $request
     * @param unknown_type $url
     */
    function process_request_book(array $request, string $url, string $remarks = ''): array {
        $insert_id = $this->CI->api_model->store_api_request($url, $request, $remarks);
        $insert_id = intval(@$insert_id['insert_id']);
        try {
            $httpHeader = array( 'Content-Type: text/xml; charset="utf-8"',);
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
            curl_setopt($ch, CURLOPT_POST, TRUE); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
            $response = curl_exec($ch);

            $error = curl_getinfo($ch);
        } catch (Exception $e) {
            $response = 'No Response Recieved From API';
        }
        // debug($response);
        // debug($error);
        // exit;
        //Update the API Response
        $this->CI->api_model->update_api_response($response, $insert_id);
        $error = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $response;
    }
    function getNoncevalue(): string {
        $Nonce = base64_encode(time());
        return $Nonce;
    }

    function getCreateDate(): string {
        $gmdate = gmdate('Y-m-d\TH:i:s\Z');
        return $gmdate;

    }

    function hextobin($hexstr): string
    {
        $n = strlen($hexstr);
        $sbin=""; 
        $i=0;
        while($i<$n)
        {     
            $a =substr($hexstr,$i,2);         
            $c = pack("H*",$a);
            if ($i==0){$sbin=$c;}
            else {$sbin.=$c;}
            $i+=2;
        } 
        return $sbin;
    }


    function DigestAlgo(string $pwd, string $created, string $nonce): string
    {
        $passha = $this->hextobin(strtoupper(sha1($pwd)));
        $Nonces = base64_decode($nonce);
        $DigHex = $this->hextobin(strtoupper(sha1($Nonces.$created.$passha)));
        return $passwordDigest = base64_encode($DigHex);
    }  
        function uuid(int $serverID = 1): string
    { 
        $t=(int)explode(" ",microtime());
        return sprintf( '%04x-%08s-%08s-%04s-%04x%04x',$serverID,$this->clientIPToHex(),substr("00000000".dechex($t[1]),-8),   
       substr("0000".dechex(round($t[0]*65536)),-4), // get 4HEX of microtime
       mt_rand(0,0xffff), mt_rand(0,0xffff));
    }

    function clientIPToHex(string $ip = ""): string 
    { 
        $hex="";
        if($ip=="") $ip=getEnv("REMOTE_ADDR");
        $part=explode('.', $ip);
        for ($i=0; $i<=count($part)-1; $i++) {
            $vvvv = (int)$part[$i];
            $hex.=substr("0".dechex($vvvv),-2);
        }
        return $hex;
    }


    function getuuid(): string
    {
        return $this->uuid();
    }


    function xml2array(string $xmlStr, int $get_attributes = 1, string $priority = 'tag'): array 
    {
        
        $contents = "";
        $tag = "";
        $level = "";
        $type = "";
        $parent = "";
        if (!function_exists('xml_parser_create')) {
            return array();
        }
        $parser = xml_parser_create('');

        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($xmlStr), $xml_values);
        xml_parser_free($parser);
        if (!$xml_values)
            return array();
        $xml_array = array();
        $parent = array();
        $current = & $xml_array;
         $result = array();
         $value = array();
        $repeated_tag_index = array();
        foreach ($xml_values as $data) {
            unset($attributes, $value);
           
            extract($data);
           
            $attributes_data = array();
            if (isset($value)) {
                if ($priority == 'tag')
                    $result = $value;
                else
                    $result['value'] = $value;
            }
            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) { 
                    if ($priority == 'tag')
                        $attributes_data[$attr] = $val;
                    else
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }
            if ($type == "open") {
                $parent[$level - 1] = & $current; 
                if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
                    $current = is_array($current) ? $current : [];
                    $current[$tag] = $result;
                    if ($attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current = & $current[$tag];
                }
                else {
                    if (isset($current[$tag][0])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                            );
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            } elseif ($type == "complete") {
                if (!isset($current[$tag])) {
                    $current = is_array($current) ? $current : [];
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                }
                else {
                    if (isset($current[$tag][0]) and is_array($current[$tag])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                            );
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }
                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                    }
                }
            } elseif ($type == 'close') {
                $current = & $parent[$level - 1];
            }
        }
        //echo "<pre>"; print_r($xml_array); echo "</pre>";  die();
        return ($xml_array);
    }
    //get the pax reference
    private function get_paxref_search_req(string $pax_count, string $pax_type, int $paxRef): array{
        if($pax_count>0){
            $pax_reference ='';
           
            for ($p=0; $p < $pax_count; $p++) { 
                
                if($p==0){
                    $pax_reference .='<ptc>'.$pax_type.'</ptc>';
                }                    
                if($pax_type=='INF'){
                     $pax_reference .='<traveller>
                            <ref>'.($p+1).'</ref>
                            <infantIndicator>'.($p+1).'</infantIndicator>
                        </traveller>';                  
                   
                }else{
                     $pax_reference .='<traveller>
                            <ref>'.$paxRef.'</ref>
                        </traveller>';    
                }
               $paxRef++;
               
            }
        }
        return array('paxtag'=>$pax_reference,'paxRef'=>$paxRef);
         
    }
    //If any error occured need to close the current session
    private function Security_SignOut(string $SecuritySession, string $seq, string $SecurityToken): void{ 
            $xml='<?xml version="1.0" encoding="UTF-8"?>           
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus
            .com/2010/06/Security_v1" xmlns:typ="http://xml.amadeus.com/2010/06/Type">
            <soapenv:Header>
            <awsse:Session TransactionStatusCode="End" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3">     
            <awsse:SessionId>'.$SecuritySession.'</awsse:SessionId>
            <awsse:SequenceNumber>'.$seq.'</awsse:SequenceNumber>
            <awsse:SecurityToken>'.$SecurityToken.'</awsse:SecurityToken>
            </awsse:Session>
            <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->getuuid().'</add:MessageID>
            <add:Action xmlns:add="http://www.w3.org/2005/08/addressing">http://webservices.amadeus.com/FMPTBQ_23_1_1A</add:Action>
            <add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$this->api_url.'</add:To>
            </soapenv:Header>
            <soapenv:Body>
            <Security_SignOut xmlns="http://xml.amadeus.com/VLSSOQ_04_1_1A"></Security_SignOut>
            </soapenv:Body>
            </soapenv:Envelope>';
            $soapAction = "VLSSOQ_04_1_1A";
            $api_url = $this->api_url;
            $soap_url = $this->soap_url.$soapAction;
            $remarks = 'Security_SignOut(amadeus)';
            $this->process_request($xml,$api_url,$remarks,$soap_url);
        }
}
?>