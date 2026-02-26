<?php if (! defined ( 'BASEPATH' ))	exit ( 'No direct script access allowed' );
class Multi_Curl
{
	function __construct()
	{
		$this->CI = &get_instance();
	}
	/**
	 * 
	 * Enter description here ...
	 */
        
        public function execute_multi_curl_goflysmart($curl_params)
    {
        $curl_response = array();
        $request_insert_id = array();
        $booking_sources = $curl_params['booking_source'];
        $requests = $curl_params['request'];
        $urls = $curl_params['url'];
        $headers = $curl_params['header'];
        $remarks = $curl_params['remarks'];
        if(valid_array($booking_sources) == true && valid_array($requests) == true && 
            valid_array($urls) == true && valid_array($headers) == true){
            foreach ($booking_sources as $k => $v){
              
                    $api_url = $urls[$k];
                    $api_request = $api_url;
                    $api_header = $headers[$k];
                    // create both cURL resources
                    ${"ch" .$k} = curl_init();
                    // set URL and other appropriate options
                        curl_setopt(${"ch" . $k}, CURLOPT_URL, $api_request);
                        curl_setopt(${"ch" . $k}, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt(${"ch" . $k}, CURLOPT_ENCODING, "");
                        curl_setopt(${"ch" . $k}, CURLOPT_MAXREDIRS, 10);
                        curl_setopt(${"ch" . $k}, CURLOPT_TIMEOUT, 30);
                        curl_setopt(${"ch" . $k}, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                        curl_setopt(${"ch" . $k}, CURLOPT_CUSTOMREQUEST, 'GET');
                      
                       
                        curl_setopt(${"ch" . $k}, CURLOPT_HTTPHEADER, $api_header);
                        
                    
                    //Store API Request
                    $backtrace = debug_backtrace();
                    $method_name = $backtrace[1]['function'];
                    $api_remarks = $method_name.'('.$remarks[$k].')';
                    $temp_request_insert_id = $this->CI->api_model->store_api_request($api_url, $api_request, $api_remarks);
                    $request_insert_id[$k] = intval(@$temp_request_insert_id['insert_id']);
               
            }
            //create the multiple cURL handle
            $mh = curl_multi_init();
            //add the handles
            foreach ($booking_sources as $k => $v){
                
                    curl_multi_add_handle($mh,${"ch" .$k});
                
            }
            // execute all queries simultaneously, and continue when all are complete
            $running = null;
            do {
                curl_multi_exec($mh, $running);
            } while ($running);
            //close the handles
            foreach ($booking_sources as $k => $v){
                
                    curl_multi_remove_handle($mh, ${"ch" . $k});
                
            }
            curl_multi_close($mh);
            //Storing the Response
            foreach ($booking_sources as $k => $v){
              
                    $curl_response[$booking_sources[$k]] = curl_multi_getcontent(${"ch" . $k});
                    
                 // $this->CI->api_model->update_api_response($curl_response[$booking_sources[$k]], $request_insert_id[$k]);
                     $this->CI->api_model->update_api_response("NULL", $request_insert_id[$k],$curl_details['total_time']);
                    //$error = curl_getinfo (${"ch" . $k});
                
            }
        }
        return $curl_response;
    }

        
        public function execute_multi_curl($curl_params)
	{
    //debug($curl_params);exit;
		$curl_response = array();
		$request_insert_id = array();
                $curl_response1=array();
		$booking_sources = $curl_params['booking_source'];
		$requests = $curl_params['request'];
		$urls = $curl_params['url'];
		$headers = $curl_params['header'];
		$remarks = $curl_params['remarks'];
                $search_id= @$curl_params['search_id'];
                
		if(valid_array($booking_sources) == true && valid_array($requests) == true && 
			valid_array($urls) == true && valid_array($headers) == true){
                    
			foreach ($booking_sources as $k => $v){
                            
             
					$api_url = $urls[$k];
					$api_request = $requests[$k];
					$api_header = $headers[$k];
					// create both cURL resources
					${"ch" .$k} = curl_init();
                    if($v != AIRBLUE_FLIGHT_BOOKING_SOURCE && $v != GOFLYSMART_FLIGHT_BOOKING_SOURCE){
					// set URL and other appropriate options
                        if($v != PK_FARE_FLIGHT_BOOKING_SOURCE && $v!=YATRA_FLIGHT_BOOKING_SOURCE) {
                            curl_setopt(${"ch" .$k}, CURLOPT_URL, $api_url);
                            curl_setopt(${"ch" .$k}, CURLOPT_TIMEOUT, 60);
                            curl_setopt( ${"ch" .$k}, CURLOPT_HEADER, 0);
                            curl_setopt(${"ch" .$k}, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt(${"ch" .$k}, CURLOPT_POST, 1);
                            curl_setopt(${"ch" .$k}, CURLOPT_POSTFIELDS, $api_request);
                            curl_setopt(${"ch" .$k}, CURLOPT_SSL_VERIFYHOST, 2);
                            curl_setopt(${"ch" .$k}, CURLOPT_SSL_VERIFYPEER, FALSE);
							curl_setopt(${"ch" .$k}, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                        }
                        else{
                        	if($v == YATRA_FLIGHT_BOOKING_SOURCE){
                        		 $api_url = $api_url . '?' . $api_request;
                        	}
                        	else{
                        		 $api_url = $api_url . '?param=' . $api_request;
                        	}
                           //echo $api_url;exit;
                            curl_setopt(${"ch" . $k}, CURLOPT_URL, $api_url);
                            curl_setopt(${"ch" . $k}, CURLOPT_TIMEOUT, 60);
                            curl_setopt(${"ch" . $k}, CURLOPT_HEADER, 0);
                            curl_setopt(${"ch" . $k}, CURLOPT_RETURNTRANSFER, 1);
 
                            curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYHOST, 3);
                            curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYPEER, FALSE);
                            
                        }
					
	                                
	                if($v == TRAVELPORT_FLIGHT_BOOKING_SOURCE || $v ==TRAVELPORT_MEELO_FLIGHT_BOOKING_SOURCE){
                                                
						curl_setopt(${"ch" .$k}, CURLOPT_FOLLOWLOCATION, FALSE);
                                                
					}
                                       else if($v != SABARE_FLIGHT_BOOKING_SOURCE){
						// curl_setopt(${"ch" .$k}, CURLOPT_SSLVERSION, 3);
						curl_setopt(${"ch" .$k}, CURLOPT_FOLLOWLOCATION, TRUE);
					}
                                        
                                      
					
					curl_setopt(${"ch" .$k}, CURLOPT_HTTPHEADER, $api_header);
                                         
					curl_setopt(${"ch" .$k}, CURLOPT_ENCODING, "gzip,deflate");
					if($v == TRAVELPORT_FLIGHT_BOOKING_SOURCE || $v ==TRAVELPORT_MEELO_FLIGHT_BOOKING_SOURCE){
                                              curl_setopt(${"ch" .$k}, CURLOPT_SSLVERSION, 6);
                                        }
                    }
                    
                    else if($v== GOFLYSMART_FLIGHT_BOOKING_SOURCE)
                    {
                        curl_setopt(${"ch" . $k}, CURLOPT_URL, $api_url);
                        curl_setopt(${"ch" . $k}, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt(${"ch" . $k}, CURLOPT_ENCODING, "");
                        curl_setopt(${"ch" . $k}, CURLOPT_MAXREDIRS, 10);
                        curl_setopt(${"ch" . $k}, CURLOPT_TIMEOUT, 30);
                        curl_setopt(${"ch" . $k}, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                        curl_setopt(${"ch" . $k}, CURLOPT_CUSTOMREQUEST, 'GET');
                        curl_setopt(${"ch" . $k}, CURLOPT_HTTPHEADER, $api_header);
                    }
                    
                    else{
                        
                        
                        
                        curl_setopt(${"ch" . $k}, CURLOPT_URL, $api_request);
                        curl_setopt(${"ch" . $k}, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt(${"ch" . $k}, CURLOPT_ENCODING, "");
                        curl_setopt(${"ch" . $k}, CURLOPT_MAXREDIRS, 10);
                        curl_setopt(${"ch" . $k}, CURLOPT_TIMEOUT, 30);
                        curl_setopt(${"ch" . $k}, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                        curl_setopt(${"ch" . $k}, CURLOPT_CUSTOMREQUEST, 'GET');
                        curl_setopt(${"ch" . $k}, CURLOPT_FOLLOWLOCATION, 0);
                        curl_setopt(${"ch" . $k}, CURLOPT_POSTFIELDS, '');
                        curl_setopt(${"ch" . $k}, CURLOPT_HTTPHEADER, $api_header);
                        curl_setopt(${"ch" . $k}, CURLOPT_HEADER, true);
                        
                        
                      
                    }
					//Store API Request
					$backtrace = debug_backtrace();
					$method_name = $backtrace[1]['function'];
					$api_remarks = $method_name.'('.$remarks[$k].')';
                                        
                                        
                                        # make false when need to store those logs Added by Balu
                                        $save_data=true;
                                        if($api_remarks!="flight_list(TBO Flight -Live)" && $api_remarks!="flight_list(PK -Fare Live)" && $api_remarks!="flight_list(Mystifly Flight -Live)" && $api_remarks!="flight_list(Travelport Flight -Live)" && $api_remarks!="flight_list(GoAir-Live)" && $api_remarks!="Authentication(GoAir)" && $api_remarks!="Authentication(Mystifly)" && $api_remarks!="Authentication(TBO)")
                                        {
                                            //$save_data=true;
                                        }
                                        
                                        if($save_data==true) {
                                        	//file_put_contents(FCPATH.'SabreLogs/sabre_rest_shopping_request.json', $api_request); 
					$temp_request_insert_id = $this->CI->api_model->store_api_request($api_url, $api_request, $api_remarks,'',$search_id);
					$request_insert_id[$k] = intval(@$temp_request_insert_id['insert_id']);
                                        } 
			}
			//create the multiple cURL handle
			$mh = curl_multi_init();
			//add the handles
                        
			foreach ($booking_sources as $k => $v){
				
					curl_multi_add_handle($mh,${"ch" .$k});
				
			}
			// execute all queries simultaneously, and continue when all are complete
	  		$running = null;
	  		do {
	    		curl_multi_exec($mh, $running);
	  		} while ($running);
			//close the handles
			foreach ($booking_sources as $k => $v){
				
					curl_multi_remove_handle($mh, ${"ch" . $k});
				
			}
			curl_multi_close($mh);
                       
                        
			//Storing the Response
                      
			foreach ($booking_sources as $k => $v){
                            
				//debug(curl_multi_getcontent(${"ch" . $k}));
                 if ($v == PK_FARE_FLIGHT_BOOKING_SOURCE) {
                    $curl_response[][$booking_sources[$k]] = gzdecode(curl_multi_getcontent(${"ch" . $k}));
                } else {
                    $curl_response[][$booking_sources[$k]] = curl_multi_getcontent(${"ch" . $k});
                }
                
                
					//debug($curl_response);exit;
                    // $curl_response[][$booking_sources[$k]] = curl_multi_getcontent(${"ch" . $k});
                                       $curl_details=curl_getinfo(${"ch" .$k});
                                      // if($save_data==true) { 
		$this->CI->api_model->update_api_response($curl_response[$k][$booking_sources[$k]], $request_insert_id[$k],$curl_details['total_time']);
		//file_put_contents(FCPATH.'SabreLogs/sabre_rest_shopping_response.json', $curl_response[$k][$booking_sources[$k]]); 
             //$this->CI->api_model->update_api_response("NULL", $request_insert_id[$k],$curl_details['total_time']);
                                      // }   
			
			}
		}
               
		return $curl_response;
	}
        
        
        
	public function execute_multi_curl_BALU($curl_params)
	{
		$curl_response = array();
		$request_insert_id = array();
		$booking_sources = $curl_params['booking_source'];
		$requests = $curl_params['request'];
		$urls = $curl_params['url'];
		$headers = $curl_params['header'];
		$remarks = $curl_params['remarks'];
		if(valid_array($booking_sources) == true && valid_array($requests) == true && 
			valid_array($urls) == true && valid_array($headers) == true){
			foreach ($booking_sources as $k => $v){
                if($v != TRAVELPORT_FLIGHT_BOOKING_SOURCE){
					$api_url = $urls[$k];
					$api_request = $requests[$k];
					$api_header = $headers[$k];
					// create both cURL resources
					${"ch" .$k} = curl_init();
					// set URL and other appropriate options
					curl_setopt(${"ch" .$k}, CURLOPT_URL, $api_url);
					curl_setopt(${"ch" .$k}, CURLOPT_TIMEOUT, 180);
					curl_setopt( ${"ch" .$k}, CURLOPT_HEADER, 0);
					curl_setopt(${"ch" .$k}, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt(${"ch" .$k}, CURLOPT_POST, 1);
					curl_setopt(${"ch" .$k}, CURLOPT_POSTFIELDS, $api_request);
					curl_setopt(${"ch" .$k}, CURLOPT_SSL_VERIFYHOST, 2);
					curl_setopt(${"ch" .$k}, CURLOPT_SSL_VERIFYPEER, FALSE);
	                                
	                if($v == TRAVELPORT_FLIGHT_BOOKING_SOURCE){
						curl_setopt(${"ch" .$k}, CURLOPT_FOLLOWLOCATION, FALSE);
					}
					else if($v != SABARE_FLIGHT_BOOKING_SOURCE){
						curl_setopt(${"ch" .$k}, CURLOPT_SSLVERSION, 3);
						curl_setopt(${"ch" .$k}, CURLOPT_FOLLOWLOCATION, TRUE);
					}
					
					curl_setopt(${"ch" .$k}, CURLOPT_HTTPHEADER, $api_header);
					curl_setopt(${"ch" .$k}, CURLOPT_ENCODING, "gzip,deflate");
					
					//Store API Request
					$backtrace = debug_backtrace();
					$method_name = $backtrace[1]['function'];
					$api_remarks = $method_name.'('.$remarks[$k].')';
					$temp_request_insert_id = $this->CI->api_model->store_api_request($api_url, $api_request, $api_remarks);
					$request_insert_id[$k] = intval(@$temp_request_insert_id['insert_id']);
				}
			}
			//create the multiple cURL handle
			$mh = curl_multi_init();
			//add the handles
			foreach ($booking_sources as $k => $v){
				if($v != TRAVELPORT_FLIGHT_BOOKING_SOURCE){
					curl_multi_add_handle($mh,${"ch" .$k});
				}
			}
			// execute all queries simultaneously, and continue when all are complete
	  		$running = null;
	  		do {
	    		curl_multi_exec($mh, $running);
	  		} while ($running);
			//close the handles
			foreach ($booking_sources as $k => $v){
				if($v != TRAVELPORT_FLIGHT_BOOKING_SOURCE){
					curl_multi_remove_handle($mh, ${"ch" . $k});
				}
			}
			curl_multi_close($mh);
			//Storing the Response
			foreach ($booking_sources as $k => $v){
				if($v != TRAVELPORT_FLIGHT_BOOKING_SOURCE){
					$curl_response[$booking_sources[$k]] = curl_multi_getcontent(${"ch" . $k});
					$this->CI->api_model->update_api_response($curl_response[$booking_sources[$k]], $request_insert_id[$k]);
					//$error = curl_getinfo (${"ch" . $k});
				}
			}
		}
		return $curl_response;
	}
        
     /**
     * 
     * Enter description here ...
     */
    public function execute_multi_curl_sightseeing($curl_params) {
        
        // debug($curl_params);exit;
        
        $curl_response = array();
        $request_insert_id = array();
        $booking_sources = $curl_params['booking_source'];
        $requests = $curl_params['request'];
        $urls = $curl_params['url'];
        $headers = $curl_params['header'];
        $remarks = $curl_params['remarks'];
        $cookie_file = @$curl_params['cookie'];

        if (valid_array($booking_sources) == true && valid_array($requests) == true &&
                valid_array($urls) == true && valid_array($headers) == true) {
            foreach ($booking_sources as $k => $v) {
                if($v != AGODA_HOTEL_BOOKING_SOURCE){
                    $api_url = $urls[$k];
                    $api_request = $requests[$k];
                    $api_header = $headers[$k];
                    $cookie_file = $cookie_file[$k];


                    // create both cURL resources
                    ${"ch" . $k} = curl_init();
                    // set URL and other appropriate options
                   
                     curl_setopt(${"ch" . $k}, CURLOPT_URL, $api_url); 
                    curl_setopt(${"ch" . $k}, CURLOPT_TIMEOUT, 180);
                    curl_setopt(${"ch" . $k}, CURLOPT_HEADER, 0);
                    curl_setopt(${"ch" . $k}, CURLOPT_RETURNTRANSFER, 1);
                     curl_setopt(${"ch" . $k}, CURLOPT_POST, 1);
                        curl_setopt(${"ch" . $k}, CURLOPT_POSTFIELDS, $api_request);
                    if ($v!= SIGHTSEEING_BOOKING_SOURCE && $v!=VIATOR_TRANSFER_BOOKING_SOURCE) {

                        curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYHOST, 2);
                        curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYPEER, FALSE);
                    }

                    if ($v == TRAVELPORT_FLIGHT_BOOKING_SOURCE) {
                        curl_setopt(${"ch" . $k}, CURLOPT_FOLLOWLOCATION, FALSE);
                    }

                    
                    curl_setopt(${"ch" . $k}, CURLOPT_HTTPHEADER, $api_header);
                  
                    if ($v != GRN_CONNECT_HOTEL_BOOKING_SOURCE) {
                        curl_setopt(${"ch" . $k}, CURLOPT_ENCODING, "gzip,deflate");
                    }

                    //Store API Request
                    $backtrace = debug_backtrace();
                    $method_name = $backtrace[1]['function'];
                    $api_remarks = $method_name . '(' . $remarks[$k] . ')';
                    $temp_request_insert_id = $this->CI->api_model->store_api_request($api_url, $api_request, $api_remarks);
                    $request_insert_id[$k] = intval(@$temp_request_insert_id['insert_id']);  
                }
               
            }
            //create the multiple cURL handle
            $mh = curl_multi_init();
            //add the handles
            foreach ($booking_sources as $k => $v) {
                if($v != AGODA_HOTEL_BOOKING_SOURCE){
                    curl_multi_add_handle($mh, ${"ch" . $k});
                }
            }
            // execute all queries simultaneously, and continue when all are complete
            $running = null;
            do {
                curl_multi_exec($mh, $running);
            } while ($running);
            //close the handles
           
            curl_multi_close($mh);
            //Storing the Response
            //debug(curl_getinfo(${"ch" . $k}));
           // debug(curl_error(${"ch" . $k}));exit;
            foreach ($booking_sources as $k => $v) {
                if($v != AGODA_HOTEL_BOOKING_SOURCE){
                    $curl_response[$booking_sources[$k]] = curl_multi_getcontent(${"ch" . $k});
                   $this->CI->api_model->update_api_response($curl_response[$booking_sources[$k]], $request_insert_id[$k]);
                  //  $this->CI->api_model->update_api_response("NULL", $request_insert_id[$k]);
                    // $error = curl_getinfo (${"ch" . $k});
                }
            }
        }
        // debug($curl_response);exit;
        return $curl_response;
    }
  	  public function execute_multi_curl1($curl_params) {
        
        // debug($curl_params);exit;
        
        $curl_response = array();
        $request_insert_id = array();
        $booking_sources = $curl_params['booking_source'];
        $requests = $curl_params['request'];
        $urls = $curl_params['url'];
        $headers = $curl_params['header'];
        $remarks = $curl_params['remarks'];
        $cookie_file = @$curl_params['cookie'];
        //debug($requests);exit;
        if (valid_array($booking_sources) == true && valid_array($requests) == true &&
                valid_array($urls) == true && valid_array($headers) == true) {
            foreach ($booking_sources as $k => $v) {
                if($v != GRN_CONNECT_HOTEL_BOOKING_SOURCE ){
                    $api_url = $urls[$k];
                    $api_request = $requests[$k];
                    // debug($api_request);exit;
                    $api_header = $headers[$k];
                    $cookie_file = $cookie_file[$k];

                    foreach($api_request as $key1 => $request){
                    ${"ch" . $key1} = curl_init();
                    // set URL and other appropriate options
                    curl_setopt(${"ch" . $key1}, CURLOPT_URL, $api_url);
                    curl_setopt(${"ch" . $key1}, CURLOPT_TIMEOUT, 180);
                    curl_setopt(${"ch" . $key1}, CURLOPT_HEADER, 0);
                    curl_setopt(${"ch" . $key1}, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt(${"ch" . $key1}, CURLOPT_POST, 1);
                    curl_setopt(${"ch" . $key1}, CURLOPT_POSTFIELDS, $request);
                    if ($v != DIDA_HOTEL_BOOKING_SOURCE) {
	                  	curl_setopt(${"ch" . $key1}, CURLOPT_SSL_VERIFYHOST, 2);
	                    curl_setopt(${"ch" . $key1}, CURLOPT_SSL_VERIFYPEER, FALSE);
                	}
                	else{
                		curl_setopt(${"ch" . $key1}, CURLOPT_SSL_VERIFYHOST, FALSE);
            			curl_setopt(${"ch" . $key1}, CURLOPT_SSL_VERIFYPEER, FALSE);
                	}
                   	if ($v != GRN_CONNECT_HOTEL_BOOKING_SOURCE && $v != DIDA_HOTEL_BOOKING_SOURCE) {
                        curl_setopt(${"ch" . $key1}, CURLOPT_SSLVERSION, 3);
                        curl_setopt(${"ch" . $key1}, CURLOPT_FOLLOWLOCATION, TRUE);
                    }
                    curl_setopt(${"ch" . $key1}, CURLOPT_HTTPHEADER, $api_header);
                   
                    if ($v != GRN_CONNECT_HOTEL_BOOKING_SOURCE) {
                        curl_setopt(${"ch" . $key1}, CURLOPT_ENCODING, "gzip,deflate");
                    }

                    //Store API Request
                    $backtrace = debug_backtrace();
                    $method_name = $backtrace[1]['function'];
                    $api_remarks = $method_name . '(' . $remarks[$k] . ')';
                    $temp_request_insert_id = $this->CI->api_model->store_api_request($api_url, $request, $api_remarks);
                    $request_insert_id[$key1] = intval(@$temp_request_insert_id['insert_id']);  
                    }
                    // create both cURL resources
                 
                }
               
            }
            //create the multiple cURL handle
            $mh = curl_multi_init();
            //add the handles
             foreach($api_request as $key1 => $request){
                //if($v != HB_HOTEL_BOOKING_SOURCE &&$v != GRN_CONNECT_HOTEL_BOOKING_SOURCE ){
                    curl_multi_add_handle($mh, ${"ch" . $key1});
               // }
            }
            // execute all queries simultaneously, and continue when all are complete
            $running = null;
            do {
                curl_multi_exec($mh, $running);
            } while ($running);
            //close the handles
           foreach($api_request as $key1 => $request){
                // if($v != HB_HOTEL_BOOKING_SOURCE &&$v != GRN_CONNECT_HOTEL_BOOKING_SOURCE ){
                    curl_multi_remove_handle($mh, ${"ch" . $key1});
                // }
            }
            curl_multi_close($mh);
            //Storing the Response
            // debug(curl_getinfo(${"ch" . $k}));
            // debug(curl_error(${"ch" . $k}));exit;
             foreach($api_request as $key1 => $request){
                // if($v != HB_HOTEL_BOOKING_SOURCE &&$v != GRN_CONNECT_HOTEL_BOOKING_SOURCE ){
                    $curl_response[DIDA_HOTEL_BOOKING_SOURCE][$key1] = curl_multi_getcontent(${"ch" . $key1});
                    $this->CI->api_model->update_api_response($curl_response[DIDA_HOTEL_BOOKING_SOURCE][$key1], $request_insert_id[$key1]);
                // }
                $error = curl_error (${"ch" . $key1});
            }
        }
       // debug($curl_response);exit;

        return $curl_response;
    }    
        
    
    
    
      public function execute_multi_curl_hotel_ratehawk($curl_params) {
         
        $curl_response = array();
        $request_insert_id = array();
        $booking_sources = $curl_params['booking_source'];
        $requests = $curl_params['request'];
        $urls = $curl_params['url'];
        $headers = $curl_params['header'];

        $remarks = $curl_params['remarks'];
        $KeyId_data = @$curl_params['KeyId'];
        $APIKey_data = @$curl_params['APIKey'];
        $cookie_file = @$curl_params['cookie'];
        
        if (valid_array($booking_sources) == true && valid_array($requests) == true &&
                valid_array($urls) == true && valid_array($headers) == true) 
        {
            foreach ($booking_sources as $k => $v) 
            {

                if ($v != AGODA_HOTEL_BOOKING_SOURCE) 
                {
                    $api_url = $urls[$k];
                    $api_request = $requests[$k];
                    $api_header = $headers[$k];
                    $KeyId = $KeyId_data[$k];
                    $APIKey = $APIKey_data[$k];
                    $cookie_file = $cookie_file[$k];
                    // create both cURL resources
                   
                    ${"ch" . $k} = curl_init();
                    // set URL and other appropriate options
                    curl_setopt(${"ch" . $k}, CURLOPT_URL, $api_url);
                    curl_setopt(${"ch" . $k}, CURLOPT_TIMEOUT, 180);
                    curl_setopt(${"ch" . $k}, CURLOPT_HEADER, 0);
                    curl_setopt(${"ch" . $k}, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt(${"ch" . $k}, CURLOPT_POST, 1);
                    curl_setopt(${"ch" . $k}, CURLOPT_POSTFIELDS, $api_request);
                    if ($v == RATEHAWK_HOTEL_BOOKING_SOURCE) 
                    {
                        $ratehawk_credentials=$KeyId . ':' . $APIKey;

                        curl_setopt(${"ch" . $k}, CURLOPT_CUSTOMREQUEST, 'POST');
                        curl_setopt(${"ch" . $k}, CURLOPT_USERPWD, $ratehawk_credentials);
                       
                    }
                    if ($v != HB_HOTEL_BOOKING_SOURCE && $v != RATEHAWK_HOTEL_BOOKING_SOURCE) 
                    {
                        curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYHOST, 2);
                        curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYPEER, FALSE);
                    }
                    // curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYHOST, 2);
                    // curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYPEER, FALSE);
                    if ($v == TRAVELPORT_FLIGHT_BOOKING_SOURCE) 
                    {
                        curl_setopt(${"ch" . $k}, CURLOPT_FOLLOWLOCATION, FALSE);
                    }


                    if ($v != FAB_HOTEL_BOOKING_SOURCE && $v != GRN_CONNECT_HOTEL_BOOKING_SOURCE && $v != HB_HOTEL_BOOKING_SOURCE && $v != RATEHAWK_HOTEL_BOOKING_SOURCE) 
                    {
                        curl_setopt(${"ch" . $k}, CURLOPT_SSLVERSION, 3);
                        curl_setopt(${"ch" . $k}, CURLOPT_FOLLOWLOCATION, TRUE);
                    }
                    curl_setopt(${"ch" . $k}, CURLOPT_HTTPHEADER, $api_header);

                    if ($v != GRN_CONNECT_HOTEL_BOOKING_SOURCE && $v != RATEHAWK_HOTEL_BOOKING_SOURCE) 
                    {
                        curl_setopt(${"ch" . $k}, CURLOPT_ENCODING, "gzip,deflate");
                    }

                    //Store API Request
                    $backtrace = debug_backtrace();
                    $method_name = $backtrace[1]['function'];
                    $api_remarks = $method_name . '(' . $remarks[$k] . ')';
                    $temp_request_insert_id = $this->CI->api_model->store_api_request($api_url, $api_request, $api_remarks);
                    $request_insert_id[$k] = intval(@$temp_request_insert_id['insert_id']);
                }
            }
            //create the multiple cURL handle
            $mh = curl_multi_init();
            //add the handles
            foreach ($booking_sources as $k => $v) {
                if ($v != AGODA_HOTEL_BOOKING_SOURCE) {
                    curl_multi_add_handle($mh, ${"ch" . $k});
                }
            }
            // execute all queries simultaneously, and continue when all are complete
            $running = null;
            do {
                curl_multi_exec($mh, $running);
            } while ($running);
            //close the handles
            foreach ($booking_sources as $k => $v) {
                if ($v != AGODA_HOTEL_BOOKING_SOURCE) {
                    curl_multi_remove_handle($mh, ${"ch" . $k});
                }
            }
            curl_multi_close($mh);
            //Storing the Response
            //debug(curl_getinfo(${"ch" . $k}));
            // debug(curl_error(${"ch" . $k}));
            //                     exit;
            foreach ($booking_sources as $k => $v) {
                if ($v != AGODA_HOTEL_BOOKING_SOURCE) {
                    $curl_response[][$booking_sources[$k]] = curl_multi_getcontent(${"ch" . $k});
                    
                    $error = curl_error(${"ch" . $k});
                   
                 // $this->CI->api_model->update_api_response($curl_response[$k][$booking_sources[$k]], $request_insert_id[$k]);
                }
            }
        }
        return $curl_response;
    }

    public function execute_multi_curl_hoteld(){
    	$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://apiint.didatravel.com/api/staticdata/GetStaticInformation?%24format=json',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "IsGetUrlOnly": true,
    "Header": {
        "LicenseKey": "TestKey",
        "ClientID": "DidaApiTestID"
    },
    "StaticType": "Policy"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));
$error = curl_error($curl);
$response = curl_exec($curl);
debug($response);exit;
curl_close($curl);
echo $response;exit;
    }
        public function execute_multi_curl_hotel($curl_params)
    
	{
		
//debug($curl_params);exit;
		$curl_response = array();
		$request_insert_id = array();
		$booking_sources = $curl_params['booking_source'];
		$requests = $curl_params['request'];
		$urls = $curl_params['url'];
		$headers = $curl_params['header'];
		$remarks = $curl_params['remarks'];
                $cookie_file= @$curl_params['cookie'];
                
                  $KeyId_data = @$curl_params['KeyId'];
                  $APIKey_data = @$curl_params['APIKey'];
                
		if(valid_array($booking_sources) == true && valid_array($requests) == true && 
			valid_array($urls) == true && valid_array($headers) == true){
			foreach ($booking_sources as $k => $v){
				if($v != AGODA_HOTEL_BOOKING_SOURCE){
				$api_url = $urls[$k];
				$api_request = $requests[$k];
				$api_header = $headers[$k];
                                $cookie_file=$cookie_file[$k];
                                
                                  $KeyId = $KeyId_data[$k];
                    $APIKey = $APIKey_data[$k];
				// create both cURL resources
				${"ch" .$k} = curl_init();
				// set URL and other appropriate options
				curl_setopt(${"ch" .$k}, CURLOPT_URL, $api_url);
				curl_setopt(${"ch" .$k}, CURLOPT_TIMEOUT, 180);
				curl_setopt( ${"ch" .$k}, CURLOPT_HEADER, 0);
				curl_setopt(${"ch" .$k}, CURLOPT_RETURNTRANSFER, 1);
				if($v != YATRA_HOTEL_BOOKING_SOURCE){
					curl_setopt(${"ch" .$k}, CURLOPT_POST, 1);
					curl_setopt(${"ch" .$k}, CURLOPT_POSTFIELDS, $api_request);
				}
				if($v != DIDA_HOTEL_BOOKING_SOURCE || $v != HB_HOTEL_BOOKING_SOURCE){
				curl_setopt(${"ch" .$k}, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt(${"ch" .$k}, CURLOPT_SSL_VERIFYPEER, FALSE);
			}
			else{
				curl_setopt(${"ch" .$k}, CURLOPT_FOLLOWLOCATION, TRUE);
				if( $v == HB_HOTEL_BOOKING_SOURCE){
					curl_setopt(${"ch" .$k}, CURLOPT_SSL_VERIFYHOST, FALSE);
				}	
				
				//curl_setopt(${"ch" .$k}, CURLOPT_SSL_VERIFYHOST, 3);
				curl_setopt(${"ch" .$k}, CURLOPT_SSL_VERIFYPEER, FALSE);
			}
                                
                                 if ($v == RATEHAWK_HOTEL_BOOKING_SOURCE) 
                    {
                        $ratehawk_credentials=$KeyId . ':' . $APIKey;
                        curl_setopt(${"ch" . $k}, CURLOPT_CUSTOMREQUEST, 'POST');
                        curl_setopt(${"ch" . $k}, CURLOPT_USERPWD, $ratehawk_credentials);
                    }
                                
				if($v == TRAVELPORT_FLIGHT_BOOKING_SOURCE){
					curl_setopt(${"ch" .$k}, CURLOPT_FOLLOWLOCATION, FALSE);
				}


                if($v !=GRN_CONNECT_HOTEL_BOOKING_SOURCE && $v !=OYO_HOTEL_BOOKING_SOURCE && $v != FAB_HOTEL_BOOKING_SOURCE && $v != RATEHAWK_HOTEL_BOOKING_SOURCE && $v != DIDA_HOTEL_BOOKING_SOURCE && $v != HB_HOTEL_BOOKING_SOURCE){
					curl_setopt(${"ch" .$k}, CURLOPT_SSLVERSION, 3);
				        curl_setopt(${"ch" .$k}, CURLOPT_FOLLOWLOCATION, TRUE);
				}
                                curl_setopt(${"ch" .$k}, CURLOPT_HTTPHEADER, $api_header);
                               
				 if($v !=GRN_CONNECT_HOTEL_BOOKING_SOURCE && $v != RATEHAWK_HOTEL_BOOKING_SOURCE){
					curl_setopt(${"ch" .$k}, CURLOPT_ENCODING, "gzip,deflate");
				  }				
				
				//Store API Request
				$backtrace = debug_backtrace();
				$method_name = $backtrace[1]['function'];
				$api_remarks = $method_name.'('.$remarks[$k].')';
				$temp_request_insert_id = $this->CI->api_model->store_api_request($api_url, $api_request, $api_remarks);
				$request_insert_id[$k] = intval(@$temp_request_insert_id['insert_id']);
			}
			}
			//create the multiple cURL handle
			$mh = curl_multi_init();
			//add the handles
			foreach ($booking_sources as $k => $v){
				if($v != AGODA_HOTEL_BOOKING_SOURCE){
					curl_multi_add_handle($mh,${"ch" .$k});
				}
			}
			// execute all queries simultaneously, and continue when all are complete
	  		$running = null;
	  		do {
	    		curl_multi_exec($mh, $running);
	  		} while ($running);
			//close the handles
			foreach ($booking_sources as $k => $v){
				if($v != AGODA_HOTEL_BOOKING_SOURCE){
					curl_multi_remove_handle($mh, ${"ch" . $k});
				}
			}
			curl_multi_close($mh);
			//Storing the Response
			//debug(curl_getinfo(${"ch" . $k}));
			// debug(curl_error(${"ch" . $k}));
   //                     exit;
			foreach ($booking_sources as $k => $v){
				if($v != AGODA_HOTEL_BOOKING_SOURCE){
					$curl_response[$booking_sources[$k]][] = curl_multi_getcontent(${"ch" . $k});
                         $curl_details=curl_getinfo(${"ch" .$k});               
					 $api_response = curl_multi_getcontent(${"ch" . $k});
                          file_put_contents(FCPATH.'HBlogs/search_response.json', $api_response);                
                   // $this->CI->api_model->update_api_response($api_response, $request_insert_id[$k]);
                            	$this->CI->api_model->update_api_response("", $request_insert_id[$k],$curl_details['total_time']);
					//$error = curl_getinfo (${"ch" . $k});
				}
			}
		}

		//debug($curl_response);exit;
		return $curl_response;
	}
    public function execute_multi_curl_bus($curl_params) {
        $curl_response = array();
        $request_insert_id = array();
        $booking_sources = $curl_params['booking_source'];
        $requests = $curl_params['request'];
        $urls = $curl_params['url'];
        $headers = $curl_params['header'];
        $remarks = $curl_params['remarks'];
        $cookie_file = @$curl_params['cookie'];

        if (valid_array($booking_sources) == true && valid_array($requests) == true &&
                valid_array($urls) == true && valid_array($headers) == true) {
            foreach ($booking_sources as $k => $v) {
              
                $api_url = $urls[$k];
                $api_request = $requests[$k];
                $api_header = $headers[$k];
                $cookie_file = $cookie_file[$k];


                // create both cURL resources
                ${"ch" . $k} = curl_init();
                // set URL and other appropriate options
                if($v != ETRAVELSMART_BUS_BOOKING_SOURCE){
                    curl_setopt(${"ch" . $k}, CURLOPT_URL, $api_url.''.$api_request);
                    curl_setopt(${"ch" . $k}, CURLOPT_ENCODING, "gzip");
                    curl_setopt(${"ch" . $k}, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt(${"ch" . $k}, CURLOPT_HTTPGET, TRUE);
                    curl_setopt(${"ch" . $k}, CURLOPT_HTTPHEADER, $api_header);
                }
                else{
                    $username = $api_header[0];
                    $password = $api_header[1];
                   
                    curl_setopt(${"ch" . $k}, CURLOPT_URL, $api_url.''.$api_request);
                    curl_setopt(${"ch" . $k}, CURLOPT_ENCODING, "gzip");
                    curl_setopt(${"ch" . $k}, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt(${"ch" . $k}, CURLOPT_USERPWD, "$username:$password");
                    curl_setopt(${"ch" . $k}, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); 
                }  
              
                //Store API Request
                $backtrace = debug_backtrace();
                $method_name = $backtrace[1]['function'];
                $api_remarks = $method_name . '(' . $remarks[$k] . ')';
                $temp_request_insert_id = $this->CI->api_model->store_api_request($api_url, $api_request, $api_remarks);
                $request_insert_id[$k] = intval(@$temp_request_insert_id['insert_id']);
            }
            //create the multiple cURL handle
            $mh = curl_multi_init();
            //add the handles
            foreach ($booking_sources as $k => $v) {
                curl_multi_add_handle($mh, ${"ch" . $k});
            }
            // execute all queries simultaneously, and continue when all are complete
            $running = null;
            do {
                curl_multi_exec($mh, $running);
            } while ($running);
            //close the handles
            foreach ($booking_sources as $k => $v) {
                curl_multi_remove_handle($mh, ${"ch" . $k});
            }
            curl_multi_close($mh);
            //Storing the Response
            //debug(curl_getinfo(${"ch" . $k}));
            //debug(curl_error(${"ch" . $k}));
            foreach ($booking_sources as $k => $v) {
                $curl_response[$booking_sources[$k]] = curl_multi_getcontent(${"ch" . $k});
              //  $this->CI->api_model->update_api_response($curl_response[$booking_sources[$k]], $request_insert_id[$k]);
                 $this->CI->api_model->update_api_response("NULL", $request_insert_id[$k]);
                //$error = curl_getinfo (${"ch" . $k});
            }
        }

        return $curl_response;
    }
    
    
    
    
     public function execute_multi_curl_bus_new($curl_params) {
        $curl_response = array();
        $request_insert_id = array();
        $booking_sources = $curl_params['booking_source'];
        $requests = $curl_params['request'];
        $urls = $curl_params['url'];
        $headers = $curl_params['header'];
        $remarks = $curl_params['remarks'];
        $cookie_file = @$curl_params['cookie'];

        if (valid_array($booking_sources) == true && valid_array($requests) == true &&
                valid_array($urls) == true && valid_array($headers) == true) {
            foreach ($booking_sources as $k => $v) {
              
                $api_url = $urls[$k];
                $api_request = $requests[$k];
                $api_header = $headers[$k];
                $cookie_file = $cookie_file[$k];


                // create both cURL resources
                ${"ch" . $k} = curl_init();
               
                            curl_setopt(${"ch" .$k}, CURLOPT_URL, $api_url);
                            curl_setopt(${"ch" .$k}, CURLOPT_TIMEOUT, 180);
                            curl_setopt( ${"ch" .$k}, CURLOPT_HEADER, 0);
                            curl_setopt(${"ch" .$k}, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt(${"ch" .$k}, CURLOPT_POST, 1);
                            curl_setopt(${"ch" .$k}, CURLOPT_POSTFIELDS, $api_request);
                            curl_setopt(${"ch" .$k}, CURLOPT_SSL_VERIFYHOST, 2);
                            curl_setopt(${"ch" .$k}, CURLOPT_SSL_VERIFYPEER, FALSE);
                            curl_setopt(${"ch" .$k}, CURLOPT_FOLLOWLOCATION, TRUE);
                            curl_setopt(${"ch" .$k}, CURLOPT_HTTPHEADER, $api_header);
			    curl_setopt(${"ch" .$k}, CURLOPT_ENCODING, "gzip,deflate");
              
                //Store API Request
                $backtrace = debug_backtrace();
                $method_name = $backtrace[1]['function'];
                $api_remarks = $method_name . '(' . $remarks[$k] . ')';
                $temp_request_insert_id = $this->CI->api_model->store_api_request($api_url, $api_request, $api_remarks);
                $request_insert_id[$k] = intval(@$temp_request_insert_id['insert_id']);
            }
            //create the multiple cURL handle
            $mh = curl_multi_init();
            //add the handles
            foreach ($booking_sources as $k => $v) {
                curl_multi_add_handle($mh, ${"ch" . $k});
            }
            // execute all queries simultaneously, and continue when all are complete
            $running = null;
            do {
                curl_multi_exec($mh, $running);
            } while ($running);
            //close the handles
            foreach ($booking_sources as $k => $v) {
                curl_multi_remove_handle($mh, ${"ch" . $k});
            }
            curl_multi_close($mh);
            //Storing the Response
            //debug(curl_getinfo(${"ch" . $k}));
            //debug(curl_error(${"ch" . $k}));
            foreach ($booking_sources as $k => $v) {
                $curl_response[$booking_sources[$k]] = curl_multi_getcontent(${"ch" . $k});
                 $this->CI->api_model->update_api_response($curl_response[$booking_sources[$k]], $request_insert_id[$k]);
             //    $this->CI->api_model->update_api_response("NULL", $request_insert_id[$k]);
                //$error = curl_getinfo (${"ch" . $k});
            }
        }

        return $curl_response;
    }
    
    
      public function execute_multi_curl_car($curl_params) {
        $curl_response = array();
        $request_insert_id = array();
        $booking_sources = $curl_params['booking_source'];
        $requests = $curl_params['request'];
        $urls = $curl_params['url'];
        $headers = $curl_params['header'];
        $remarks = $curl_params['remarks'];
        $cookie_file = @$curl_params['cookie'];
        $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
        //debug($curl_params);exit;
        if (valid_array($booking_sources) == true && valid_array($requests) == true &&
                valid_array($urls) == true && valid_array($headers) == true) {
            foreach ($booking_sources as $k => $v) {
              
                $api_url = $urls[$k];
                $api_request = $requests[$k];
                $api_header = $headers[$k];
                // $cookie_file = $cookie_file[$k];
                // debug($api_url);
                // debug($api_header);
                // debug($api_request);exit;
                // create both cURL resources
                ${"ch" . $k} = curl_init();
                // set URL and other appropriate options
                curl_setopt(${"ch" . $k}, CURLOPT_URL, $api_url);
                curl_setopt(${"ch" . $k}, CURLOPT_ENCODING, "gzip");
                curl_setopt(${"ch" . $k}, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt(${"ch" . $k}, CURLOPT_USERAGENT, $ua);
                curl_setopt(${"ch" . $k}, CURLOPT_POST, 1);
                curl_setopt(${"ch" . $k}, CURLOPT_POSTFIELDS, $api_request);
                curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt(${"ch" . $k}, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt(${"ch" . $k}, CURLOPT_HTTPHEADER, $api_header);
                //Store API Request
                $backtrace = debug_backtrace();
                $method_name = $backtrace[1]['function'];
                $api_remarks = $method_name . '(' . $remarks[$k] . ')';

                $temp_request_insert_id = $this->CI->api_model->store_api_request($api_url, $api_request, $api_remarks);
                $request_insert_id[$k] = intval(@$temp_request_insert_id['insert_id']);
            }
            //create the multiple cURL handle
            $mh = curl_multi_init();
            //add the handles
            foreach ($booking_sources as $k => $v) {
                curl_multi_add_handle($mh, ${"ch" . $k});
            }
            // execute all queries simultaneously, and continue when all are complete
            $running = null;
            do {
                curl_multi_exec($mh, $running);
            } while ($running);
            //close the handles
            foreach ($booking_sources as $k => $v) {
                curl_multi_remove_handle($mh, ${"ch" . $k});
            }
            curl_multi_close($mh);
            //Storing the Response
            //debug(curl_getinfo(${"ch" . $k}));
            // debug(curl_error(${"ch" . $k}));
            foreach ($booking_sources as $k => $v) {
                $curl_response[$booking_sources[$k]] = curl_multi_getcontent(${"ch" . $k});
                
               $this->CI->api_model->update_api_response($curl_response[$booking_sources[$k]], $request_insert_id[$k]);
               //  $this->CI->api_model->update_api_response("NULL", $request_insert_id[$k]);
                $error = curl_getinfo (${"ch" . $k});
                // debug($error);exit;
            }
        }
         // debug($curl_response);exit;
         // exit;
        return $curl_response;
    }
    
   
    
    
     public function execute_multi_curl_travelport($curl_params) {
        
        $curl_response = array();
        $request_insert_id = array();
        $booking_sources = $curl_params['booking_source'];
        $requests = $curl_params['request'];
        $urls = $curl_params['url'];
        $headers = $curl_params['header'];
        $remarks = $curl_params['remarks'];
        if (valid_array($booking_sources) == true && valid_array($requests) == true && valid_array($urls) == true && valid_array($headers) == true) {
            foreach ($booking_sources as $k => $v) {
                
                if($v == TRAVELPORT_FLIGHT_BOOKING_SOURCE ){
                    $api_url = $urls[$k];

                    $api_request = $requests[$k];
                    // debug($api_request);exit;
                    $api_header = $headers[$k];
                   // debug($api_header);
                    foreach($api_request as $key1 => $request){
                        if($key1 == 1){
                            $api_header[4] = "Content-length: " . strlen($request);
                             // debug($api_header);exit;
                        }
                        // debug($api_header);
                        // create both cURL resources
                        ${"ch" .$key1} = curl_init();
                        // set URL and other appropriate options
                        curl_setopt(${"ch" .$key1}, CURLOPT_URL, $api_url);
                        curl_setopt(${"ch" .$key1}, CURLOPT_TIMEOUT, 180);
                        // curl_setopt(${"ch" .$key1}, CURLOPT_HEADER, 0);
                        curl_setopt(${"ch" .$key1}, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt(${"ch" .$key1}, CURLOPT_POST, 1);
                        curl_setopt(${"ch" .$key1}, CURLOPT_POSTFIELDS, $request);
                        curl_setopt(${"ch" .$key1}, CURLOPT_SSL_VERIFYHOST, 2);
                        curl_setopt(${"ch" .$key1}, CURLOPT_SSL_VERIFYPEER, FALSE);
                                        
                        curl_setopt(${"ch" .$key1}, CURLOPT_FOLLOWLOCATION, FALSE);
                       
                        
                        curl_setopt(${"ch" .$key1}, CURLOPT_HTTPHEADER, $api_header);
                        curl_setopt(${"ch" .$key1}, CURLOPT_ENCODING, "gzip,deflate");
                        curl_setopt(${"ch" .$key1}, CURLOPT_SSLVERSION, 6);
                        

                        //Store API Request
                        $backtrace = debug_backtrace();
                        $method_name = $backtrace[1]['function'];
                        $api_remarks = $method_name . '(' . $remarks[$k] . ')';
                        $server_info = $_SERVER;
                        $temp_request_insert_id = $this->CI->api_model->store_api_request($api_url, $request, $api_remarks,$server_info);
                        $request_insert_id[$key1] = intval(@$temp_request_insert_id['insert_id']);  
                   }
                }
                   // exit;
                    // create both cURL resources
            }
            //create the multiple cURL handle
            $mh = curl_multi_init();
            //add the handles
             foreach($api_request as $key1 => $request){
                //if($v != HB_HOTEL_BOOKING_SOURCE &&$v != GRN_CONNECT_HOTEL_BOOKING_SOURCE ){
                    curl_multi_add_handle($mh, ${"ch" . $key1});
               // }
            }
            // execute all queries simultaneously, and continue when all are complete
            $running = null;
            do {
                curl_multi_exec($mh, $running);
            } while ($running);
            //close the handles
           foreach($api_request as $key1 => $request){
                // if($v != HB_HOTEL_BOOKING_SOURCE &&$v != GRN_CONNECT_HOTEL_BOOKING_SOURCE ){
                    curl_multi_remove_handle($mh, ${"ch" . $key1});
                // }
            }
            curl_multi_close($mh);
           
            foreach($api_request as $key1 => $request){
                $curl_response[TRAVELPORT_FLIGHT_BOOKING_SOURCE][$key1] = curl_multi_getcontent(${"ch" . $key1});
                //$this->CI->api_model->update_api_response($curl_response[TRAVELPORT_FLIGHT_BOOKING_SOURCE][$key1], $request_insert_id[$key1]);
                  $this->CI->api_model->update_api_response('NULL', $request_insert_id[$key1]);
                // debug($curl_response);exit;
                
              $error = curl_getinfo (${"ch" . $key1});
            }
        }
      

        return $curl_response;
    } 
	/**
	 * Assigns the Curl Parameters(URL,Header info.,Request)
	 * @param unknown_type $request_params
	 * @param unknown_type $curl_request
	 * @param unknown_type $curl_url
	 * @param unknown_type $curl_header
	 * @param unknown_type $curl_booking_source
	 */
	/*
	public function assign_curl_params($request_params, & $curl_request, & $curl_url, & $curl_header, & $curl_booking_source)
	{
		$request = array($request_params['request']);
		$url = array($request_params['url']);
		$header = array($request_params['header']);
		$booking_source = array($request_params['booking_source']);
		$curl_remarks = (isset($request_params['remarks']) == true ? array(trim($request_params['remarks'])) : array(''));
		
		$curl_request = array_merge($curl_request, $request);
		$curl_url = array_merge($curl_url, $url);
		$curl_header = array_merge($curl_header, $header);
		$curl_booking_source = array_merge($curl_booking_source, $booking_source);
	}*/
    function execute_multi_curl_cebu($curl_params) {
        // debug($curl_params);exit;
        $remarks = 'Flight list (Cebu)';
        $insert_id = $this->CI->api_model->store_api_request($curl_params['url'][0], $curl_params['request'][0], $remarks);
        $insert_id = intval(@$insert_id['insert_id']);
        // $url = 'https://www.airblue.com/agents/bookings/flight_selection.aspx';
        // echo $curl_params['request'][0];exit;
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $curl_params['request'][0],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_COOKIEJAR => "cebu.txt",
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_FOLLOWLOCATION => 0,
        CURLOPT_POSTFIELDS => '',
        CURLOPT_HTTPHEADER => $curl_params['header'][0],
        CURLOPT_HEADER => 1,
        ));

        $response = curl_exec($curl); 
        
        $err = curl_error($curl);
        $curl_info = curl_getinfo($curl);
        // debug($curl_info);exit; 
        curl_close($curl);
       
        //Update the API Response
        $this->CI->api_model->update_api_response($response, $insert_id);
        $curl_response[0][AIRBLUE_FLIGHT_BOOKING_SOURCE] = $response;
        
        return $curl_response;
    }
}