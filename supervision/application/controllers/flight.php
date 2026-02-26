<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
ini_set('max_execution_time', 300);
/**
 *
 * @package    Provab
 * @subpackage Flight
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */

class Flight extends CI_Controller {
	private $current_module;
	public function __construct()
	{
		parent::__construct();
		//$this->output->enable_profiler(TRUE);
		$this->load->model('flight_model');
		$this->load->model('domain_management_model');
		$this->current_module = $this->config->item('current_module');
	}

	function get_booking_details( string $app_reference): void
	{
		$condition = [];
		$condition[] = array('BD.app_reference', '=', $this->db->escape($app_reference));
		$details = $this->flight_model->get_booking_details($app_reference);
		if ($details['status'] == SUCCESS_STATUS) {
			$booking_source = $details['data']['booking_details']['booking_source'];
			load_flight_lib($booking_source);
			$this->flight_lib->get_booking_details($details['data']['booking_details'], $details['data']['booking_transaction_details']);
		}
	}
	/**
	 * Cancellation
	 * Balu A
	 */
	// add return type in bottom function 
	function pre_cancellation(string $app_reference, string $booking_source): void
	{
		if (empty($app_reference) == false && empty($booking_source) == false) {
			$page_data = array();
			$booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source);
			if ($booking_details['status'] == SUCCESS_STATUS) {
				$this->load->library('booking_data_formatter');
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_flight_booking_data($booking_details,$this->current_module);
				$page_data['data'] = $assembled_booking_details['data'];
				$this->template->view('flight/pre_cancellation', $page_data);
			} else {
				redirect('security/log_event?event=Invalid Details');
			}
		} else {
			redirect('security/log_event?event=Invalid Details');
		}
	}
	/**
	 * Balu A
	 * @param $app_reference
	 */
	function cancel_booking(): void
	{
		//error_reporting(E_ALL);
		$post_data = $this->input->post();
		if (isset($post_data['app_reference']) == true && isset($post_data['booking_source']) == true && isset($post_data['transaction_origin']) == true &&
			valid_array($post_data['transaction_origin']) == true && isset($post_data['passenger_origin']) == true && valid_array($post_data['passenger_origin']) == true) {
			$app_reference = trim($post_data['app_reference']);
			$booking_source = trim($post_data['booking_source']);
			//$transaction_origin = $post_data['transaction_origin'];
			$passenger_origin = $post_data['passenger_origin'];
			$booking_details = $GLOBALS['CI']->flight_model->get_booking_details($app_reference, $booking_source);
                        
			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_flight_lib($booking_source);
				//Formatting the Data
				$this->load->library('booking_data_formatter');
				$booking_details = $this->booking_data_formatter->format_flight_booking_data($booking_details, $this->current_module);
				$booking_details = $booking_details['data'];
                                
				//Grouping the Passenger Ticket Ids
				$grouped_passenger_ticket_details = $this->flight_lib->group_cancellation_passenger_ticket_id($booking_details, $passenger_origin);
                                
				$passenger_origin = $grouped_passenger_ticket_details['passenger_origin'];
				$passenger_ticket_id = $grouped_passenger_ticket_details['passenger_ticket_id'];

				$cancellation_details = $this->flight_lib->cancel_booking($booking_details, $passenger_origin, $passenger_ticket_id);

				$cancellation_details = base64_encode(json_encode($cancellation_details));

				redirect('flight/cancellation_details/'.$app_reference.'/'.$booking_source.'/'.$cancellation_details);
			} else {
				redirect('security/log_event?event=Invalid Details');
			}
		} else {
			redirect('security/log_event?event=Invalid Details');
		}
	}
	/**
	 *
	 * @param $app_reference
	 * @param $booking_source
	 */
	function cancellation_details(string $app_reference, string $booking_source, string $cancellation_details): void
	{
		$cancellation_details = json_decode(base64_decode($cancellation_details), true);
		if (empty($app_reference) == false && empty($booking_source) == false) {
			$master_booking_details = $GLOBALS['CI']->flight_model->get_booking_details($app_reference, $booking_source);
			if ($master_booking_details['status'] == SUCCESS_STATUS) {
				$page_data = array();
				$this->load->library('booking_data_formatter');
				$master_booking_details = $this->booking_data_formatter->format_flight_booking_data($master_booking_details, 'b2c');

                                
				$page_data['data'] = $master_booking_details['data'];
				$page_data['cancellation_status'] = $cancellation_details['status'];
				$page_data['cancellation_message'] = $cancellation_details['message'];
				$this->template->view('flight/cancellation_details', $page_data);
			} else {
				redirect('security/log_event?event=Invalid Details');
			}
		} else {
			redirect('security/log_event?event=Invalid Details');
		}

	}
	/**
	 * Balu A
	 * Get supplier cancellation status
	 */
	public function update_supplier_cancellation_status_details(): void
	{
		$get_data = $this->input->get();

		if(isset($get_data['app_reference']) == true && isset($get_data['booking_source']) == true && isset($get_data['passenger_status']) == true && $get_data['passenger_status'] == 'BOOKING_CANCELLED' && isset($get_data['passenger_origin']) == true && intval($get_data['passenger_origin']) > 0){
			$app_reference = trim($get_data['app_reference']);
			$booking_source = trim($get_data['booking_source']);
			$passenger_origin = trim($get_data['passenger_origin']);
			$passenger_status = trim($get_data['passenger_status']);
			$booking_details = $this->flight_model->get_passenger_ticket_info($app_reference, $passenger_origin, $passenger_status);
			if($booking_details['status'] == SUCCESS_STATUS){
				$master_booking_details = $booking_details['data']['booking_details'][0];
				$booking_customer_details = $booking_details['data']['booking_customer_details'][0];
				$cancellation_details = $booking_details['data']['cancellation_details'][0];
				$booking_source = $master_booking_details['booking_source'];
				$request_data = array();
				$request_data['AppReference'] = 		$booking_customer_details['app_reference'];
				$request_data['SequenceNumber'] =		$booking_customer_details['sequence_number'];
				$request_data['BookingId'] = 			$booking_customer_details['book_id'];
				$request_data['PNR'] = 					$booking_customer_details['pnr'];
				$request_data['TicketId'] = 			$booking_customer_details['TicketId'];
				$request_data['ChangeRequestId'] =	$cancellation_details['RequestId'];
				load_flight_lib($booking_source);
				$supplier_ticket_refund_details = $this->flight_lib->get_supplier_ticket_refund_details($request_data);
				if($supplier_ticket_refund_details['status'] == SUCCESS_STATUS){
					$this->flight_model->update_supplier_ticket_refund_details($passenger_origin, $supplier_ticket_refund_details['data']);
				}
			}
		}
	}
	/**
	 * Balu A
	 * Displays Cancellation Ticket Details
	 */
	public function ticket_cancellation_details(): void
	{
		$user_condition = [];
		$get_data = $this->input->get();
		if(isset($get_data['app_reference']) == true && isset($get_data['booking_source']) == true && isset($get_data['status']) == true){
			$app_reference = trim($get_data['app_reference']);
			$booking_source = trim($get_data['booking_source']);
			$status = trim($get_data['status']);
			$booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source, $status);
			 // debug($booking_details);exit;
			if($booking_details['status'] == SUCCESS_STATUS){
				$this->load->library('booking_data_formatter');
				$booking_details = $this->booking_data_formatter->format_flight_booking_data($booking_details, $this->config->item('current_module'));
				$page_data = array();
				$booked_user_id = intval($booking_details['data']['booking_details'][0]['created_by_id']);
				$booked_user_details = array();
				$is_agent = false;
				$user_condition[] = array('U.user_id' ,'=', $booked_user_id);
				$booked_user_details = $this->user_model->get_user_details($user_condition);
				if(valid_array($booked_user_details) == true){
					$booked_user_details = $booked_user_details[0];
					if($booked_user_details['user_type'] == B2B_USER){
						$is_agent = true;
					}
				}
				$page_data['booking_data'] = $booking_details['data'];
		//debug($page_data['booking_data']); die;
				$page_data['booked_user_details'] =	$booked_user_details;
				$page_data['is_agent'] = 			$is_agent;
				$this->template->view('flight/ticket_cancellation_details', $page_data);
			} else {
				redirect(base_url());
			}
		} else {
			redirect(base_url());
		}
	}
	/**
	 * Balu A
	 * Displays Ticket cancellation Refund details
	 */
	public function cancellation_refund_details(): void
		
	{
		$user_condition = [];
		$get_data = $this->input->get();
		if(isset($get_data['app_reference']) == true && isset($get_data['booking_source']) == true && isset($get_data['passenger_status']) == true && $get_data['passenger_status'] == 'BOOKING_CANCELLED' && isset($get_data['passenger_origin']) == true && intval($get_data['passenger_origin']) > 0){
			$app_reference = trim($get_data['app_reference']);
			//$booking_source = trim($get_data['booking_source']);
			$passenger_origin = trim($get_data['passenger_origin']);
			$passenger_status = trim($get_data['passenger_status']);
			$booking_details = $this->flight_model->get_passenger_ticket_info($app_reference, $passenger_origin, $passenger_status);
			if($booking_details['status'] == SUCCESS_STATUS){
				$booked_user_id = intval($booking_details['data']['booking_details'][0]['created_by_id']);
				$booked_user_details = array();
				$is_agent = false;
				$user_condition[] = array('U.user_id' ,'=', $booked_user_id);
				$booked_user_details = $this->user_model->get_user_details($user_condition);
				if(valid_array($booked_user_details) == true){
					$booked_user_details = $booked_user_details[0];
					if($booked_user_details['user_type'] == B2B_USER){
						$is_agent = true;
					}
				}
				$page_data = array();
				$page_data['booking_data'] = $booking_details['data'];
				$page_data['booked_user_details'] =	$booked_user_details;
				$page_data['is_agent'] = 			$is_agent;
				$this->template->view('flight/cancellation_refund_details', $page_data);
			} else {
				redirect(base_url());
			}
		} else {
			redirect(base_url());
		}
	}
	/**
	 * Balu A
	 * Update Ticket Refund Details
	 */
	public function update_ticket_refund_details(): void
	
	{
		$user_condition = [];
		$post_data = $this->input->post();
		$redirect_url_params = array();
		$this->form_validation->set_rules('app_reference', 'app_reference', 'trim|required|xss_clean');
		$this->form_validation->set_rules('passenger_origin', 'passenger_origin', 'trim|required|min_length[1]|numeric');
		$this->form_validation->set_rules('passenger_status', 'passenger_status', 'trim|required|xss_clean');
		$this->form_validation->set_rules('refund_payment_mode', 'refund_payment_mode', 'trim|required|xss_clean');
		$this->form_validation->set_rules('refund_amount', 'refund_amount', 'trim|numeric');
		$this->form_validation->set_rules('cancellation_charge', 'cancellation_charge', 'trim|numeric');
		$this->form_validation->set_rules('service_tax_on_refund_amount', 'service_tax_on_refund_amount', 'trim|numeric');
		$this->form_validation->set_rules('swachh_bharat_cess', 'swachh_bharat_cess', 'trim|numeric');
		$this->form_validation->set_rules('refund_status', 'refund_status', 'trim|required|xss_clean');
		$this->form_validation->set_rules('refund_comments', 'UserId', 'trim|required');
		if ($this->form_validation->run()) {
			$app_reference = 				trim($post_data['app_reference']);
			$passenger_origin = 			intval($post_data['passenger_origin']);
			$passenger_status = 			trim($post_data['passenger_status']);
			$refund_payment_mode = 			trim($post_data['refund_payment_mode']);
			$refund_amount = 				floatval($post_data['refund_amount']);
			$cancellation_charge = 			floatval($post_data['cancellation_charge']);
			$service_tax_on_refund_amount =	floatval($post_data['service_tax_on_refund_amount']);
			$swachh_bharat_cess = 			floatval($post_data['swachh_bharat_cess']);
			$refund_status = 				trim($post_data['refund_status']);
			$refund_comments = 				trim($post_data['refund_comments']);
			//Get Ticket Details
			$booking_details = $this->flight_model->get_passenger_ticket_info($app_reference, $passenger_origin, $passenger_status);
			if($booking_details['status'] == SUCCESS_STATUS){
				$master_booking_details = $booking_details['data']['booking_details'][0];
				//$booking_customer_details = $booking_details['data']['booking_customer_details'][0];
				//$cancellation_details = $booking_details['data']['cancellation_details'][0];
				$booking_currency = $master_booking_details['currency'];//booking currency
				$booked_user_id = intval($master_booking_details['created_by_id']);
				$user_condition[] = array('U.user_id' ,'=', $booked_user_id);
				$booked_user_details = $this->user_model->get_user_details($user_condition);
				$is_agent = false;
				if(valid_array($booked_user_details) == true && $booked_user_details[0]['user_type'] == B2B_USER){
					$is_agent = true;
				}
				$currency_obj = new Currency(array('from' => get_application_default_currency() , 'to' => $booking_currency));
				$currency_conversion_rate = $currency_obj->currency_conversion_value(true, get_application_default_currency(), $booking_currency);
				if($refund_status == 'PROCESSED' && floatval($refund_amount) > 0 && $is_agent == true){
					//1.Crdeit the Refund Amount to Respective Agent
					$agent_refund_amount = ($currency_conversion_rate*$refund_amount);//converting to agent currency
					
					//2.Add Transaction Log for the Refund
					$fare = -($refund_amount);//dont remove: converting to negative
					$domain_markup=0;
					$level_one_markup=0;
					$convinence = 0;
					$discount = 0;
					$remarks = 'flight Refund was Successfully done';
					$this->domain_management_model->save_transaction_details('flight', $app_reference, $fare, $domain_markup, $level_one_markup, $remarks, $convinence, $discount, $booking_currency, $currency_conversion_rate, $booked_user_id);

					//update agent balance
					$this->domain_management_model->update_agent_balance($agent_refund_amount, $booked_user_id);
				}
				//UPDATE THE REFUND DETAILS
				//Update Condition
				$update_refund_condition = array();
				$update_refund_condition['passenger_fk'] =	$passenger_origin;
				//Update Data
				$update_refund_details = array();
				$update_refund_details['refund_payment_mode'] = 			$refund_payment_mode;
				$update_refund_details['refund_amount'] =					$refund_amount;
				$update_refund_details['cancellation_charge'] = 			$cancellation_charge;
				$update_refund_details['service_tax_on_refund_amount'] =	$service_tax_on_refund_amount;
				$update_refund_details['swachh_bharat_cess'] = 				$swachh_bharat_cess;
				$update_refund_details['refund_status'] = 					$refund_status;
				$update_refund_details['refund_comments'] = 				$refund_comments;
				$update_refund_details['currency'] = 						$booking_currency;
				$update_refund_details['currency_conversion_rate'] = 		$currency_conversion_rate;
				if($refund_status == 'PROCESSED'){
					$update_refund_details['refund_date'] = 				date('Y-m-d H:i:s');
				}
				$this->custom_db->update_record('flight_cancellation_details', $update_refund_details, $update_refund_condition);
				
				$redirect_url_params['app_reference'] = $app_reference;
				$redirect_url_params['booking_source'] = $master_booking_details['booking_source'];
				$redirect_url_params['passenger_status'] = $passenger_status;
				$redirect_url_params['passenger_origin'] = $passenger_origin;
			}
		}
		redirect('flight/cancellation_refund_details?'.http_build_query($redirect_url_params));
	}
	/** 
	 ** Issue hold ticket 
	 **	Jeevanandam K
	**/
	function run_ticketing_method(string $app_reference, string $booking_source): void
	{	
		$response = [];
		$page_data = [];
		$post_data = [];
		$response ['data'] = array ();
		$response ['Status'] = FAILURE_STATUS;
		$response ['Message'] = '';	

		load_flight_lib($booking_source);
		$this->load->library('booking_data_formatter');
		$token_detail = $GLOBALS['CI']->custom_db->single_table_records('flight_booking_transaction_details','*',array('app_reference'=>$app_reference,'status'=>"BOOKING_HOLD"));

		if(valid_array($token_detail) && $token_detail['status'] == SUCCESS_STATUS)
		{
			$token_details = $token_detail['data']['0'];
			if($token_details['hold_ticket_req_status'] == INACTIVE )
			{
				$sequence_number = $token_details['sequence_number'];
				$pnr = $token_details['pnr'];
				$booking_id = $token_details['book_id'];

				$booked_user_details = $this->flight_model->get_booked_user_details($app_reference);
				if($booked_user_details[0]['user_type'] == B2B_USER){
					$agent_id = $booked_user_details[0]['created_by_id'];
					$agent_details = $this->domain_management_model->get_agent_details($agent_id);
					
					$page_data['agent_details'] = $agent_details;
					$agent_base_currency = $agent_details['agent_base_currency'];
					
					$currency_obj = new Currency();
					$currency_conversion_rate = $currency_obj->getConversionRate(false, get_application_default_currency(), $agent_base_currency);//Currency conversion rate of the domain currency
									
					
					if(valid_array($agent_details) == false){//Invalid Agent ID
						redirect(base_url());
					}
					
					$page_data['agent_id'] = $agent_id;
					$amount = $this->booking_data_formatter->agent_buying_price($token_detail['data']);
					$post_data['amount'] = -abs($amount[0]);
					
					$debit_amount = ($post_data['amount']*$currency_conversion_rate);					
					
					
					$post_data['app_reference'] = $app_reference;
					$post_data['agent_list_fk'] = $agent_id;
					$post_data['remarks'] = "Flight transaction successfully done";
					$post_data['amount'] = $debit_amount;
					$post_data['currency'] = $agent_details['agent_base_currency'];
					$post_data['currency_conversion_rate'] = $currency_conversion_rate;
					$post_data['issued_for'] = 'Debited Towards: Flight ';
					$this->domain_management_model->process_direct_credit_debit_transaction($post_data);
					
					//Update Issue Hold Ticket Status In Booking Transaction Details
					$this->custom_db->update_record('flight_booking_transaction_details',array('hold_ticket_req_status'=>ACTIVE),array('app_reference'=>$app_reference,'pnr' => $pnr));

					$ticket_response = $this->flight_lib->issue_hold_ticket($app_reference,$sequence_number,$pnr,$booking_id);

					if($ticket_response['status'] == SUCCESS_STATUS)
					{

						$response['Status'] = SUCCESS_STATUS;
						$response['Message'] = "Request Sent Successfully !!";
					}else{
						$response['Status'] = FAILURE_STATUS;
						$response['Message'] = "Failed to send request !!";	
					}
				}else{
					$response['Status'] = FAILURE_STATUS;
					$response['Message'] = "Booking Details Not Found !!";
				}
			}else{
				$response['Status'] = FAILURE_STATUS;
				$response['Message'] = "Request Already Sent !!";
			}
			
		}else{
			$response['Status'] = FAILURE_STATUS;
			$response['Message'] = "Booking Details Not Found !!";
		}

		
		echo json_encode($response);
	}
	/**
	 * Balu A
	 */
	function exception(): void
	{
		$module = META_AIRLINE_COURSE;
		$op = $_GET['op'];
		$notification = $_GET['notification'];
		$eid = $this->module_model->log_exception($module, $op, $notification);
		//set ip log session before redirection
		$this->session->set_flashdata(array('log_ip_info' => true));
		redirect(base_url().'index.php/flight/event_logger/'.$eid);
	}

	function event_logger(string $eid = ''): void
	{
		$log_ip_info = $this->session->flashdata('log_ip_info');
		$this->template->view('flight/exception', array('log_ip_info' => $log_ip_info, 'eid' => $eid));
	}
    function exception_log_details(): void {
		$res = [];
        $get_data = $this->input->get();
        
        $result=$this->flight_model->exception_log_details($get_data);
        if($result=="null")
        {   $res['Status']=0;
            $res['Message']='Booking may confirmed, Please contact API support team';
            echo json_encode($res);
        }else {
        echo $result;exit;
        }
    }
     /*
     *
     * Flight(Airport) auto suggest
     *
     */

    function get_airport_code_list(): void{

        $term = $this->input->get('term'); //retrieve the search term that autocomplete sends
        $term = trim(strip_tags($term));
        $result = array();
        
        $__airports = $this->flight_model->get_airport_list($term)->result();
        if (valid_array($__airports) == false) {
            $__airports = $this->flight_model->get_airport_list('')->result();
        }
       
        $airports = array();
        foreach ($__airports as $airport) {
         	$airports['label'] = $airport->airport_city . ', ' . $airport->country . ' (' . $airport->airport_code . ')';
            $airports['value'] = $airport->airport_city . ' (' . $airport->airport_code . ')';
            $airports['id'] = $airport->origin;
            
            // if (empty($airport->top_destination) == false) {
            //     $airports['category'] = 'Top cities';
            //     $airports['type'] = 'Top cities';
            // } else {
            //     $airports['category'] = 'Search Results';
            //     $airports['type'] = 'Search Results';
            // }
            $airports['category'] = 'Search Results';
            $airports['type'] = 'Search Results';
            array_push($result, $airports);
        }
        $this->output_compressed_data($result);
    }
     /**
     * Compress and output data
     * @param array $data
     */
    private function output_compressed_data(array $data): never {


        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start("ob_gzhandler");
        header('Content-type:application/json');
        echo json_encode($data);
        ob_end_flush();
        exit;
    }
    function flight_crs_airline_list(): void
	{
		$page_data = [];
		$data =  $this->custom_db->single_table_records('flight_crs_airline_list');

		if ($data['status']) {
			$page_data['airline_list'] = $data['data'];
		} else {
			$page_data['airline_list'] = array();
		}
		//debug($page_data);die;
		$this->template->view('flight/flight_crs_airline_list', $page_data);
	}

    function add_flight(): void
	{
				ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(0);
		$page_data = array();
		$condition = array();

		//$condition['status'] = 1;

		$option_list = $this->custom_db->single_table_records('flight_crs_tax_list', 'tax_name', $condition, 0, 1000000);
		//debug($option_list);die;
		$tax_list = array();
		if (valid_array($option_list['data'])) {
			foreach ($option_list['data'] as $v) {
				$tax_list = $option_list['data'];
			}
		}
		$page_data['tax_list'] = $tax_list;
		$aircraft_data =  $this->custom_db->single_table_records('aircrafts');
		if ($aircraft_data['status']) {
			$page_data['aircraft_data'] = $aircraft_data['data'];
		}
		//debug($page_data);exit;
		$this->template->view('flight/add_flight', $page_data);
	}
	function save_crs_flight_details(): void
	{
		$block_seat_array = [];
		$data = $this->input->post();
		
		$images = $_FILES;



		if ($data['seat_temp_id'] != 0) {

			$con_for_seat_tmp = array('id' => $data['seat_temp_id']);
			//Retriving Seat temp data
			$seat_temp_data = $this->custom_db->single_table_records('seat_temp', 'data', $con_for_seat_tmp)['data'][0];

			$seat_data = json_decode($seat_temp_data['data'], 1);

			//Setting seat data for inserting
			$data['from_seat_range'] = $seat_data['from_seat_range'];
			$data['to_seat_range'] = $seat_data['to_seat_range'];
			$data['range_price'] = $seat_data['range_price'];

			$aircraft = $this->custom_db->single_table_records('aircrafts', 'seat_row_count,seat_coulumns', array('origin' => $data['aircraft']));

			$flight_starting_column = substr($aircraft['data'][0]['seat_coulumns'], 0, 1);
			$flight_last_column = substr($aircraft['data'][0]['seat_coulumns'], -1);
		}

		$depr = date('Y-m-d', strtotime($data['dep_date'][0])) . ' ' . $data['departure_time'][0] . ':00';
		$newdate = strtotime('+330 minute', strtotime($depr));
		$GMT_dep_time = date('Y-m-d H:i:s', $newdate);

		$arrr = date('Y-m-d', strtotime($data['arr_date'][0])) . ' ' . $data['arrival_time'][0] . ':00';
		$newdater = strtotime('+330 minute', strtotime($arrr));
		$GMT_arr_time = date('Y-m-d H:i:s', $newdater);

		//$strType = $data['is_triptype'];
		//$strCnt  = (($strType == 1) ? 2 : 1);
		$strFSId = 0;
		// for ($i = 0; $i < $strCnt; $i++) {
		$arrInsData = array();
		$strIsDomestic = $data['is_domestic'];
		$strIsTripType = $data['is_triptype'];
		$arrInsData['is_domestic'] 	= $strIsDomestic;
		$arrInsData['is_triptype'] 	= $strIsTripType;
		// if ($i > 0) {
		// 	if (isset($data['no_of_stop_1']) && $data['no_of_stop_1'] > -1) {
		// 		$arrInsData['no_of_stop']  		= $data['no_of_stop_1'];
		// 		$arrInsData['origin']  			= $data['origin_1'];
		// 		$arrInsData['destination']  	= $data['destination_1'];
		// 		$arrInsData['arr_date']  		= $data['arr_date_1'];
		// 		$arrInsData['dep_date']  		= $data['dep_date_1'];
		// 		$arrInsData['departure_time']   = $data['departure_time_1'];
		// 		$arrInsData['arrival_time']     = $data['arrival_time_1'];

		// 		$arrInsData['GMT_dep_time']     = $GMT_dep_time;
		// 		$arrInsData['GMT_arr_time']     = $GMT_arr_time;


		// 		$arrInsData['flight_num']     	= $data['flight_num_1'];
		// 		$arrInsData['carrier_code']     = $data['carrier_code_1'];
		// 		$arrInsData['class_type']     	= $data['class_type_1'];
		// 		$arrInsData['fare_rule']     	= $data['fare_rule_1'];
		// 		$arrInsData['trip_type']     	= $i;
		// 	}
		// } else {
		$arrInsData['no_of_stop']  		= $data['no_of_stop'];
		$arrInsData['origin']  			= $data['origin'];
		$arrInsData['days']  			= implode(',', $data['days']);

		$arrInsData['destination']  	= $data['destination'];
		$arrInsData['arr_date']  		= $data['arr_date'];
		$arrInsData['dep_date']  		= $data['dep_date'];

		$arrInsData['GMT_dep_time']     = $GMT_dep_time;
		$arrInsData['GMT_arr_time']     = $GMT_arr_time;

		$arrInsData['departure_time']   = $data['departure_time'];
		$arrInsData['arrival_time']     = $data['arrival_time'];
		$arrInsData['flight_num']     	= $data['flight_num'];
		$arrInsData['carrier_code']     = $data['carrier_code'];
		$arrInsData['class_type']     	= $data['class_type'];
		$arrInsData['fare_rule']     	= $data['fare_rule'];

		$arrInsData['aircraft']     	= $data['aircraft'];
		$arrInsData['seats']     		= $data['seats'];
		$arrInsData['cancellation_percentage']     		= $data['cancellation_percentage'];
		$arrInsData['cancellation_type']     		= $data['cancellation'];
		//$arrInsData['pilots_type']     		= $data['pilots_type'];


		if (isset($data['pnr'])) {
			$arrInsData['pnr']     		= $data['pnr'];
		} else {
			$arrInsData['pnr']     		= '';
		}

		$arrInsData['tax_breakup']     	= json_encode($data['tax']);
		$arrInsData['baggage_info']     = json_encode($data['baggage']);

		$arrInsData['fare_type']     = $data['fare_type'];
		$arrInsData['charter_basefare']   = $data['charter_basefare'];
		$arrInsData['charter_tax']   = $data['charter_tax'];
		$arrInsData['charter_vat']   = $data['charter_vat'];


		$arrInsData['adult_basefare']   = $data['adult_basefare'];
		$arrInsData['adult_tax']     	= $data['adult_tax'];
		$arrInsData['child_basefare']   = $data['child_basefare'];
		$arrInsData['child_tax']     	= $data['child_tax'];
		$arrInsData['infant_basefare']  = $data['infant_basefare'];
		$arrInsData['infant_tax']     	= $data['infant_tax'];

		$arrInsData['adult_local_basefare']   = $data['adult_local_basefare'];
		$arrInsData['adult_local_tax']     	= $data['adult_local_tax'];
		$arrInsData['child_local_basefare']   = $data['child_local_basefare'];
		$arrInsData['child_local_tax']     	= $data['child_local_tax'];
		$arrInsData['infant_local_basefare']  = $data['infant_local_basefare'];
		$arrInsData['infant_local_tax']     	= $data['infant_local_tax'];

		$arrInsData['trip_type']     	= 0;
		// }
		if ($strIsDomestic == 1 && $strIsTripType == 1) {
			$arrInsData['arrival_date']     	= $data['arr_date_1'];
		}
		// debug($arrInsData);exit;
		$arrInsData['show_meal'] = 0;
		$arrInsData['show_baggage'] = 0;
		$arrInsData['show_seat'] = 0;

		if (isset($data['show_meals'])) {
			$arrInsData['show_meal'] = 1;
		}
		if (isset($data['show_baggage'])) {
			$arrInsData['show_baggage'] = 1;
		}
		if (isset($data['show_seat'])) {
			$arrInsData['show_seat'] = 1;
		}
		// echo "<br>--------<br/>";exit;

		$strFSId = $this->save_crs_flight_details_ins($arrInsData, $strFSId, $images);

		// }	


		$seat_range = array();
		if (valid_array($data["from_seat_range"]) && valid_array($data["to_seat_range"]) && valid_array($data["range_price"])) {
			$from_seat_range = $data["from_seat_range"];
			$to_seat_range = $data["to_seat_range"];
			$range_price = $data["range_price"];
			$count = isset($from_seat_range) ? count($from_seat_range) : 0;
			for ($b = 0; $b < $count; $b++) {
				$from_seat_range_data = array(
					'seat_from_range' => $from_seat_range[$b],
					'seat_to_range' => $to_seat_range[$b],
					'price' => $range_price[$b],
					'created_date' => date('Y-m-d h:i:s'),
					'created_by_id' => $this->entity_user_id,
					'flight_id' => $strFSId
				);
				array_push($seat_range, $from_seat_range_data);
			}

			$this->db->insert_batch('flight_crs_master_seat_price_range', $seat_range);
		}

		$block_seat_from_range = $data["block_seat_from_range"];
		$block_seat_to_range = $data["block_seat_to_range"];




		if (valid_array($block_seat_to_range) && valid_array($block_seat_from_range)) {

			foreach ($block_seat_from_range as $block_key => $block_seats) {

				$from_seat_row = intval($block_seats);
				//$from_seat_column = str_replace($from_seat_row, '', $block_seats);

				$to_seat_row = intval($block_seat_to_range[$block_key]);
				//$to_seat_column = str_replace($to_seat_row, '', $block_seat_to_range[$block_key]);

				if ($block_seats != $block_seat_to_range[$block_key]) {
					//$last_count = strlen($to_seat_column) - 1;
					// debug($last_count);
					//$i+=strlen($from_seat_column)

					// for ($i=$from_seat_column; $i <=$to_seat_column ;$i++ ) { 
					$flag = false;
					for ($l = $from_seat_row; $l <= $to_seat_row; $l++) {
						for ($i = $flight_starting_column; $i <= $flight_last_column; $i++) {

							if ($block_seats == $l . $i) {
								$flag = true;
							}

							if ($flag == true) {


								$column_key = $l . $i;

								$block_seat_array[] = array(
									'flight_id' => $strFSId,
									'seat_number' => $column_key,
									'seat_flag' => 'BLK',
									'status' => 1,
									'created_date' => date('Y-m-d h:i:s'),
									'created_by_id' => $this->entity_user_id,
								);
								if ($block_seat_to_range[$block_key] == $column_key) {

									$flag = false;
								}
							}
						}
					}
				} else {
					$block_seat_array[] = array(
						'flight_id' => $strFSId,
						'seat_number' => $block_seats,
						'seat_flag' => 'BLK',
						'status' => 1,
						'created_date' => date('Y-m-d h:i:s'),
						'created_by_id' => $this->entity_user_id,
					);
				}
			}

			$this->db->insert_batch('flight_seat', $block_seat_array);
		}


		//Deleting seat temp data
		if (isset($con_for_seat_tmp)) {
			$this->custom_db->delete_record('seat_temp', $con_for_seat_tmp);
		}

		// die("end");
		if ($data['fare_type'] == 0) {
			redirect(base_url() . 'index.php/flight/flight_list/');
		} else {
			redirect(base_url() . 'index.php/flight/empty_list/');
		}
	}

	function save_crs_flight_details_ins(array $data_ins, int $strFSId, array $images): int
	{
		$image_insert = [];
		$flight_segment_details = array();
		$data = $data_ins;


		if ($_SERVER['REMOTE_ADDR'] == "223.186.27.185") {
			// debug($data); 
		}
		$temp_carrier_code = explode('(', $data['carrier_code'][0]);
		$carrier_code = trim($temp_carrier_code[1]);
		if (isset($carrier_code) == true) {
			$carrier_code = trim($carrier_code, '() ');
		} else {
			$carrier_code = '';
		}

		$alrline_name = trim($temp_carrier_code[0]);
		if (isset($alrline_name) == true) {
			$alrline_name = trim($alrline_name, '() ');
		} else {
			$alrline_name = '';
		}
		//debug($data['origin']);
		$count = isset($data['origin']) ? count($data['origin']) : 0;
		$total_flight = $count;
		$flight_segment_details['is_domestic'] 	= 1; // $data['is_domestic'];

		$flight_segment_details['origin'] 		= $this->getAirportCode($data['origin'][0]);
		$flight_segment_details['days'] 	= $data['days'];
		$strDestination = !empty($data['destination'][$total_flight - 1]) ? $data['destination'][$total_flight - 1] : $data['destination'][0];
		$flight_segment_details['destination'] 	= $this->getAirportCode($strDestination);
		//debug($data['destination']);
		//exit;
		$dep_count = isset($data['departure_time']) ? count($data['departure_time']) : 0;
		for ($r = 0; $r < $dep_count; $r++) {
			$data['departure_time'][$r] = implode(':', explode(' : ', $data['departure_time'][$r]));
			$data['arrival_time'][$r] 	= implode(':', explode(' : ', $data['arrival_time'][$r]));
		}
		/*$data['departure_time'][0] = implode(':',explode(' : ',$data['departure_time'][0]));
		$data['arrival_time'][$total_flight-1] = implode(':',explode(' : ',$data['arrival_time'][$total_flight-1]));
		*/
		$departure_dt = $data['dep_date'][0] . ' ' . $data['departure_time'][0];
		//$arrival_dt   = $data['arr_date'][$total_flight - 1] . ' ' . $data['arrival_time'][$total_flight - 1];
		$flight_segment_details['dep_from_date'] = date('Y-m-d', strtotime($data['dep_date'][0]));
		if (isset($data['arr_date'])) {
			$flight_segment_details['dep_to_date'] = date('Y-m-d', strtotime($data['arr_date'][0]));
		} else {
			$flight_segment_details['dep_to_date'] = date('Y-m-d', strtotime($data['arr_date'][$total_flight - 1]));
		}
		/*debug($data['arrival_time']); 
		debug($total_flight);
		exit;*/
		$flight_segment_details['departure_time'] 	= date('H:i', strtotime($data['departure_time'][0]));
		$flight_segment_details['arrival_time'] 	= !empty($data['arrival_time'][$total_flight - 1]) ? date('H:i', strtotime($data['arrival_time'][$total_flight - 1])) : date('H:i', strtotime($data['arrival_time'][0]));



		$flight_segment_details['GMT_dep_time'] = $data['GMT_dep_time'];
		$flight_segment_details['GMT_arr_time'] = $data['GMT_arr_time'];

		// date('H:i',strtotime($data['arrival_time'][$total_flight-1]))

		$flight_segment_details['flight_num'] 		= $data['flight_num'][0];
		$flight_segment_details['carrier_code'] 	= $carrier_code;
		$flight_segment_details['airline_name'] 	= $alrline_name;

		$flight_segment_details['class_type'] 		= $data['class_type'][0];
		//$flight_segment_details['actual_basefare'] = $data['actual_basefare'];
		$flight_segment_details['adult_basefare'] 	= $data['adult_basefare'];
		$flight_segment_details['child_basefare'] 	= $data['child_basefare'];
		$flight_segment_details['infant_basefare'] 	= $data['infant_basefare'];
		$flight_segment_details['charter_basefare'] 	= $data['charter_basefare'];
		$flight_segment_details['charter_tax'] 	= $data['charter_tax'];

		$flight_segment_details['charter_vat'] 	= $data['charter_vat'];

		$flight_segment_details['fare_type'] 	= $data['fare_type'];



		for ($i = 0; $i < count($images['pro-image']['name']); $i++) {
			$directory = DOMAIN_AIRCRAFT_UPLOAD_DIR;


			//$randId = rand(0, 99999999);

			$fileName = basename($_FILES["pro-image"]['name'][$i]);
			$targetFilePath = $directory . $fileName;

			$fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
			$allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf');
			$newFileName = 'image' . rand(1111, 9999) . '.' . $fileType;
			$targetFilePath = $directory  . $newFileName;
			$url = strstr(base_url(), 'supervision', true);


			$saveFile = $url . DOMAIN_AIRCRAFT_IMAGE  . $newFileName; // Need to change into https



			if (in_array($fileType, $allowTypes)) {
				debug($targetFilePath);

				move_uploaded_file($_FILES["pro-image"]["tmp_name"][$i], $targetFilePath);
			}
			// $image_insert['hotel_id'] = $btn_type['hotel_id'];
			$image_insert[$i] = $saveFile;
			// $image_insert['status'] = 1;

		}


		//$flight_segment_details['adult_local_basefare'] 	= $data['adult_local_basefare'];
		//$flight_segment_details['child_local_basefare'] 	= $data['child_local_basefare'];
		//$flight_segment_details['infant_local_basefare'] 	= $data['infant_local_basefare'];

		$flight_segment_details['child_tax'] 		= $data['child_tax'];

		$flight_segment_details['tax_breakup'] 		= $data['tax_breakup'];
		$flight_segment_details['trip_type']  		= $data['is_triptype'];
		$flight_segment_details['crs_currency'] 	= get_api_data_currency();
		$flight_segment_details['no_of_stops'] 		= $data['no_of_stop'];
		$flight_segment_details['aircraft'] 		= $data['aircraft'];
		$flight_segment_details['seats'] 			= $data['seats'];
		//$flight_segment_details['pilots_type'] 		= $data['pilots_type'];
		$flight_segment_details['pnr'] 				= $data['pnr'];
		$flight_segment_details['active'] 			= '0';
		$flight_segment_details['update_time'] 		= date('Y-m-d H:i:s');

		$tax_breakup = json_decode($data['tax_breakup']);
		//debug($tax_breakup);exit;
		$adult_tax = 0;
		$child_tax = 0;
		$infant_tax = 0;

		// $adult_local_tax = 0;
		// $child_local_tax = 0;
		// $infant_local_tax = 0;
		foreach ($tax_breakup->adt as $v) {
			$adult_tax += floatval($v->value);
		}
		foreach ($tax_breakup->child as $v) {
			$child_tax += floatval($v->value);
		}

		foreach ($tax_breakup->inf as $val) {
			$infant_tax += floatval($val->value);
		}

		$flight_segment_details['adult_tax'] = $adult_tax;
		$flight_segment_details['infant_tax'] = $infant_tax;
		$flight_segment_details['child_tax'] = $child_tax;

		//$flight_segment_details['adult_local_tax'] = $adult_local_tax;
		//$flight_segment_details['infant_local_tax'] = $infant_local_tax;
		//$flight_segment_details['child_local_tax'] = $child_local_tax;
		$flight_segment_details['cancellation_percentage'] = $data['cancellation_percentage'];
		$flight_segment_details['cancellation_type'] = $data['cancellation_type'];

		$flight_segment_details['show_meal'] = $data['show_meal'];
		$flight_segment_details['show_baggage'] = $data['show_baggage'];
		$flight_segment_details['show_seat'] = $data['show_seat'];



		// debug($flight_segment_details);exit;
		if ($_SERVER['REMOTE_ADDR'] == "223.186.27.185") {
			//debug($flight_segment_details);exit;
		}
		if ($strFSId == 0) {
			$flight_segment = $this->custom_db->insert_record('flight_crs_segment_details', $flight_segment_details);
			$fsid = $flight_segment['insert_id'];
		} else {
			$fsid = $strFSId;
		}
		// echo $this->db->last_query();exit;

		for ($i = 0; $i < $total_flight; $i++) {
			if (!empty($data['origin'][$i])) {
				$temp_carrier_code_1 = explode('(', $data['carrier_code'][$i]);
				$carrier_code_1      = trim($temp_carrier_code_1[1]);
				if (isset($carrier_code_1) == true) {
					$carrier_code_1 = trim($carrier_code_1, '() ');
				} else {
					$carrier_code_1 = '';
				}

				$alrline_name_1 = trim($temp_carrier_code_1[0]);
				if (isset($alrline_name_1) == true) {
					$alrline_name_1	= trim($alrline_name_1, '() ');
				} else {
					$alrline_name_1	= '';
				}


				$departure_from_dt_d 	= $data['dep_date'][$i];
				$departure_to_dt_d 		= $data['arr_date'][$i];

				$departure_dt_t 		= $data['departure_time'][$i];
				$arrival_dt_t 			= $data['arrival_time'][$i];

				$flight_details 		= array();
				//$flight_data 			= array();

				$flight_details['fsid'] = $fsid;
				if (isset($data['origin'][$i])) {
					$flight_details['origin'] 				= $this->getAirportCode($data['origin'][$i]);
					$flight_details['destination'] 			= $this->getAirportCode($data['destination'][$i]);

					$flight_details['departure_from_date'] 	= date('Y-m-d', strtotime($departure_from_dt_d));
					$flight_details['departure_to_date'] 	= date('Y-m-d', strtotime($departure_to_dt_d));
					$flight_details['departure_time'] 		= date('H:i', strtotime($departure_dt_t));
					$flight_details['arrival_time'] 		= date('H:i', strtotime($arrival_dt_t));


					$flight_details['GMT_dep_time'] = $data['GMT_dep_time'];
					$flight_details['GMT_arr_time'] = $data['GMT_arr_time'];


					$flight_details['flight_num'] 			= $data['flight_num'][$i];
					$flight_details['carrier_code'] 		= $carrier_code_1;
					$flight_details['airline_name'] 		= $alrline_name_1;
					$flight_details['class_type'] 			= $data['class_type'][$i];
					$flight_details['fare_rule'] 			= $data['fare_rule'][$i];
					$flight_details['trip_type'] 			= $data['trip_type'];
					$flight_details['fare_type'] 			= $data['fare_type'];
					$flight_details['images'] 			    = json_encode($image_insert);



					$flight_details['baggage_info'] 			= $data['baggage_info'];

					$flight_details['update_time'] 			= date('Y-m-d H:i:s');


					// debug($flight_details);exit;
					 $this->custom_db->insert_record('flight_crs_details', $flight_details);



					//echo $this->db->last_query();
					//$num_of_days = get_date_difference(date('Y-m-d', strtotime($departure_dt)), date('Y-m-d', strtotime($departure_to_dt_d)));

					//$start_date = $departure_from_dt_d;
				}
				//die("end loop");
				/*for($j=0;$j<=$num_of_days;$j++){
					$fdid = $flight_data['insert_id'];
					$dep_dat = date('Y-m-d',strtotime("+".$j." day",strtotime($start_date)));
					$flight_available_date = array();
					
					$flight_available_date['fdid'] = $fdid;
					$flight_available_date['dep_datetime'] = date('Y-m-d H:i',strtotime($dep_dat.' '.$departure_dt_t));
					$flight_available_date['arr_datetime'] = get_nextDateTime_by_time($departure_dt_t,$arrival_dt_t,$dep_dat);
					$flight_avl = $this->custom_db->insert_record( 'flight_crs_available_dates', $flight_available_date);
				}*/
			}
		}
		return $fsid;
		//redirect ( base_url () . 'flight/flight_list/');
	}


	function getAirportCode(string $strPlace): string
	{
		preg_match('#\((.*?)\)#', $strPlace, $match);
		$strRetData = $match[1];
		return $strRetData;
	}

	function flight_list(): void
	{
		$search_data = [];
		$flight_data = [];
		$get_data = $this->input->get();

		if (!empty($get_data['dep_origin']) && isset($get_data['dep_origin'])) {
			$search_data['dep_origin'] = $this->getAirportCode($get_data['dep_origin']);
		}
		if (!empty($get_data['arival_origin']) && isset($get_data['arival_origin'])) {
			$search_data['arrival_origin'] = $this->getAirportCode($get_data['arival_origin']);
		}

		if (!empty($get_data['month']) && isset($get_data['month'])) {
			$search_data['month'] = $get_data['month'];
		}
		if (!empty($get_data['year']) && isset($get_data['year'])) {
			$search_data['year'] = $get_data['year'];
		}
		if (!empty($get_data['flight_no']) && isset($get_data['flight_no'])) {
			$search_data['flight_no'] = $get_data['flight_no'];
		}

		if (!empty($get_data['arr_date']) && isset($get_data['arr_date'])) {
			$search_data['arr_date'] = $get_data['arr_date'];
		}



		$flight_data['data'] = $this->flight_model->all_flight_list($search_data);
		//echo $this->db->last_query();exit;
		$fsid = $this->flight_model->get_booking_count();
		if (!empty($fsid)) {
			$fsids = '';
			foreach ($fsid as $f__val) {
				$fsids .= $f__val['fsid'] . ',';
			}
			$fsids = rtrim($fsids, ',');
		}
		$flight_data['fsid'] = explode(',', $fsids);
		$this->template->view('flight/flight_list', $flight_data);
	}
	function flight_general_terms(): void
	{
		$page_data = [];
		$data =  $this->custom_db->single_table_records('flight_general_terms');
		if ($data['status']) {
			$page_data['general_terms'] = $data['data'];
		} else {
			$page_data['general_terms'] = array();
		}
		$this->template->view('flight/flight_general_terms', $page_data);
	}

	function flight_fare_rules(): void
	{
		$page_data = [];
		$data =  $this->custom_db->single_table_records('flight_crs_fare_rules');
		if ($data['status']) {
			$page_data['fare_rule_list'] = $data['data'];
		} else {
			$page_data['fare_rule_list'] = array();
		}
		$this->template->view('flight/flight_fare_rules', $page_data);
	}
	function add_fare_rule(): void
	{
		$data = $this->input->post();
		if (!empty($data)) {
			if ($data['origin'] != 0) {
				$carrier = explode('(', $data['carrier_code']);
				$data['carrier_name'] = $carrier[0];
				$data['carrier_code'] =  explode(")", $carrier[1])[0];
				$this->custom_db->update_record('flight_crs_fare_rules', $data, array('origin' => $data['origin']));
			} else {
				$carrier = explode('(', $data['carrier_code']);
				$data['carrier_name'] = $carrier[0];
				$data['carrier_code'] =  explode(")", $carrier[1])[0];
				$this->custom_db->insert_record('flight_crs_fare_rules', $data);
			}
		}
		redirect(base_url() . 'index.php/flight/flight_fare_rules');
	}
	function add_genetal_terms(): void
	{
		$data = $this->input->post();
		//debug($data);exit;
		if (!empty($data)) {
			if ($data['origin'] != 0) {
				$this->custom_db->update_record('flight_general_terms', $data, array('origin' => $data['origin']));
			} else {

				$this->custom_db->insert_record('flight_general_terms', $data);
			}
		}
		redirect(base_url() . 'index.php/flight/flight_general_terms');
	}

	function flight_meal_details(): void
	{
		$page_data = [];
		$data =  $this->custom_db->single_table_records('flight_crs_meal_details');
		if ($data['status']) {
			$page_data['meal_detail_list'] = $data['data'];
		} else {
			$page_data['meal_detail_list'] = array();
		}
		$this->template->view('flight/flight_meal_details', $page_data);
	}

	function document_name_list(): void
	{
		$page_data = [];
		$data =  $this->custom_db->single_table_records('flight_crs_document_name_list');
		if ($data['status']) {
			$page_data['document_list'] = $data['data'];
		} else {
			$page_data['document_list'] = array();
		}
		$this->template->view('flight/flight_crs_document_name_list', $page_data);
	}
	


	function baggage_fare_rules(): void
	{

		$data = $this->input->post();
		$config = [];
		$page_data = [];
		if (!empty($data)) {

			//debug($data);

			if (valid_array($_FILES) == true and $_FILES['banner_image']['error'] == 0 and $_FILES['banner_image']['size'] > 0) {
				//$domain_origin = 1;
				$config['upload_path'] = $this->template->domain_ban_image_upload_path();
				$temp_file_name = $_FILES['banner_image']['name'];
				$config['allowed_types'] = '*';
				$config['file_name'] = get_domain_key() . $temp_file_name;
				$config['max_size'] = '1000000';
				$config['max_width']  = '';
				$config['max_height']  = '';
				$config['remove_spaces']  = false;

				$this->load->library('upload', $config);
				$this->upload->initialize($config);
				if (! $this->upload->do_upload('banner_image')) {
					echo $this->upload->display_errors();
				} else {
					$image_data =  $this->upload->data();
				}
				$data['image'] = $image_data['file_name'];
			}
			//debug($data);exit;


			if ($data['origin'] != 0) {


				$this->custom_db->update_record('flight_crs_baggage_rule', $data, array('origin' => $data['origin']));
			} else {
				$this->custom_db->insert_record('flight_crs_baggage_rule', $data);
			}
		}

		$data1 =  $this->custom_db->single_table_records('flight_crs_baggage_rule');
		if ($data1['status']) {
			$page_data['baggage_rule_list'] = $data1['data'];
		} else {
			$page_data['baggage_rule_list'] = array();
		}
		//debug($data);exit;
		$this->template->view('flight/baggage_details', $page_data);
	}

	function update_flight_details(int $id, array $filter_data = array()): void
	{
		$result = [];
		$con = [];
		$conn = [];
		$condi = [];
		//error_reporting(E_ALL);ini_set('display_errors', 1); 
		$filter_data =  json_decode(base64_decode($filter_data), true);

		// debug($filter_data);die;
		$result['flight_details'] = $this->flight_model->update_flight_details($id);
		if ($_SERVER['REMOTE_ADDR'] == "14.97.94.42") {
			//debug($result['flight_details']); die;
		}
		$blocked_seat_details = $this->flight_model->flight_blocked_seat_details($id);
		// debug($blocked_seat_details); die;
		//debug($result['flight_details'][0]['aircraft']); die;
		$flight_aircraft_seat = $this->custom_db->single_table_records('flight_aircraft_seat', '*', array('flight_aircraft_id' => $result['flight_details'][0]['aircraft']));


		$blocked_seat_array = array();
		//debug($blocked_seat_details); die;
		foreach ($blocked_seat_details['blocked_seats'] as  $b_value) {
			$blocked_seat_array[$b_value['seat_number']] = $b_value['seat_number'];
		}
		//debug($blocked_seat_array); die;
		foreach ($flight_aircraft_seat['data'] as $f_key => $f_value) {
			if (isset($blocked_seat_array[$f_value['seat_number']])) {
				$flight_aircraft_seat['data'][$f_key]['seat_flag'] = 'BLK';
			}
		}
		//debug($flight_aircraft_seat); die;
		$result['aircraft'] = $this->custom_db->single_table_records('aircrafts', '*', array('origin' => $result['flight_details'][0]['aircraft']))['data'][0];


		$result['flight_aircraft_seat'] = $flight_aircraft_seat;
		// debug($flight_aircraft_seat);exit;
		if ($blocked_seat_details['status'] == SUCCESS_STATUS) {
			$result['blocked_seat_details'] = $blocked_seat_details['blocked_seats'];
			$result['blocked_seat_count'] = $blocked_seat_details['blocked_seat_count'];
		} else {
			$result['blocked_seat_details'] = array();
			$result['blocked_seat_count'] = 0;
		}
		//debug($result); die;
		$result['fsid'] = $id;
		$result['ID'] = $result['flight_details'][0]['aircraft'];
		$conn['origin'] =  $result['flight_details'][0]['aircraft'];
		$aircraft_data = $this->custom_db->single_table_records('aircrafts', '*', $conn)['data'][0];
		$aircraft = [];
		$condi['type'] =  $aircraft_data['type'];
		$condi['model'] =  $aircraft_data['model'];
		$selected_aircraft_data = $this->custom_db->single_table_records('aircrafts', '*', $condi);
		if ($selected_aircraft_data['status']) {
			foreach ($selected_aircraft_data['data'] as $valu) {
				$aircraft[$valu['origin']] = $valu['reg'];
			}
		}

		$result['flight_details'][0]['aircraft'] = $aircraft;

		// debug($id); die;
		$this->flight_model->initial_update_flight_details($id, $result);

		//debug($res);exit; 
		// $fsid_list = implode(',',$res['fsid_list']);
		// debug($fsid_list);exit;

		$result['update_flight_details'] = $this->flight_model->crs_update_flight_details($id, $filter_data);
		//debug($result['update_flight_details']); die;
		// $result['update_flight_details'] = $this->flight_model->crs_update_flight_details ($fsid_list,$filter_data);

		foreach ($result['update_flight_details'] as $key => $pilot) {
			$fare_details = $this->adding_fare_management($pilot, $result['flight_details']);
			// $result['update_flight_details'][$key]['total_adult_book_price'] = $total_adult_book_price;

			$fdtl = 0;
			$avail_date = date('Y-m-d', strtotime($pilot['avail_date']));
			$temp = "SELECT * FROM `jlb_details` WHERE `jlb_date` = '" . $avail_date . "' and flight_number=" . $pilot['fsid'];
			//debug($temp);
			$f_temp =  $this->custom_db->get_custom_query($temp);
			//debug($f_temp);
			if ($f_temp['status']) {
				$fdtl = 1;
			}

			$result['update_flight_details'][$key]['fdtl'] =  $fdtl;
			$result['update_flight_details'][$key]['total_adult_book_price'] = $fare_details['total_adult_book_price'];
			$result['update_flight_details'][$key]['total_child_book_price'] = $fare_details['total_child_book_price'];
			$result['update_flight_details'][$key]['total_infant_book_price'] = $fare_details['total_infant_book_price'];

			$con['origin'] =  $pilot['pilot_id'];
			$select_date_data = $this->custom_db->single_table_records('pilot', '*', $con);
			//debug($select_date_data);exit;
			if (isset($select_date_data['data'][0])) {
				$result['update_flight_details'][$key]['pilot_name'] = $select_date_data['data'][0]['first_name'];
			}

			$con['origin'] =  $pilot['co_pilot'];
			$select_date_data = $this->custom_db->single_table_records('pilot', '*', $con);
			if (isset($select_date_data['data'][0])) {
				$result['update_flight_details'][$key]['co_pilot_name'] = $select_date_data['data'][0]['first_name'];
			}
		}
		//exit;
		if ($_SERVER['REMOTE_ADDR'] == "14.97.94.42") {
			//debug($result); die;
		}

		$this->template->view('flight/update_flight_details', $result);
	}
	function get_flight_details(int $fsid): void
	{
		$result = $this->flight_model->flight_details($fsid);
		echo json_encode($result);
	}

	function add_pilot(): void
	{
		$page_data = array();
		$pilot = [];
		$cond = [];
		$condi = [];
		$condition = [];
		$data = $this->input->post();

		if (!empty($data)) {

			//debug($data);exit;
			if ($data['origin'] != 0) {
				$info = $data;

				$pilot['pilot_id'] = $info['pilot_id'][0];
				foreach ($info['training_type'] as $key => $tt) {
					if (!empty($tt)) {

						$pilot['training_type'] = $info['training_type'][$key];
						$pilot['training_issue_date'] = date("Y-m-d", strtotime($info['training_issue_date'][$key]));
						$pilot['training_expiry_date'] = date("Y-m-d", strtotime($info['training_expiry_date'][$key]));


						$pilot_origin = $info['pilot_origin'][$key];
						if (empty($pilot_origin)) {

							$pilot['pilot_id']  = $data['origin'];
							$this->custom_db->insert_record('pilot_training', $pilot);
							// echo $this->db->last_query();echo'<br><br>';
						} else {

							$this->custom_db->update_record('pilot_training', $pilot, array('origin' =>  $pilot_origin));
							// echo $this->db->last_query();echo'<br><br>';
						}
					}
				}

				foreach ($info['licence_number'] as $key => $tt) {
					if (!empty($tt)) {
						$origin = $info['licence_origin'][$key];
						$licence['licence_type'] = $info['licence_type'][$key];
						$licence['licence_issue_date'] = date("Y-m-d", strtotime($info['licence_issue_date'][$key]));
						$licence['licence_expiry_date'] = date("Y-m-d", strtotime($info['licence_expiry_date'][$key]));
						$licence['licence_number'] = $tt;

						//debug($licence);exit;
						if (empty($origin)) {
							$licence['pilot_id']  = $data['origin'];
							$this->custom_db->insert_record('pilot_licence', $licence);
						} else {
							$this->custom_db->update_record('pilot_licence', $licence, array('origin' =>  $info['licence_origin'][$key]));
							// echo $this->db->last_query();echo'<br><br>';
						}
					}
				}
				unset($data['pilot_origin']);
				unset($data['pilot_id']);
				unset($data['training_type']);
				unset($data['training_issue_date']);
				unset($data['training_expiry_date']);
				unset($data['licence_type']);
				unset($data['licence_number']);
				unset($data['licence_issue_date']);
				unset($data['licence_expiry_date']);
				unset($data['licence_origin']);

				$data['passport_issue_date'] = date("Y-m-d", strtotime($data['passport_issue_date']));
				$data['passport_expiry_date'] = date("Y-m-d", strtotime($data['passport_expiry_date']));
				$data['doj'] = date("Y-m-d", strtotime($data['doj']));

				$data['aircraft']  = implode(',', $data['aircraft']);
				//     debug($data);exit;
				$this->custom_db->update_record('pilot', $data, array('origin' => $data['origin']));


				//	echo $this->db->last_query();exit;
				redirect(base_url() . 'index.php/flight/pilot_list');
			} else {
				
				$info = $data;
				unset($data['origin']);
				unset($data['training_type']);
				unset($data['training_issue_date']);
				unset($data['training_expiry_date']);

				unset($data['licence_type']);
				unset($data['licence_number']);
				unset($data['licence_issue_date']);
				unset($data['licence_expiry_date']);

				$data['passport_issue_date'] = date("Y-m-d", strtotime($data['passport_issue_date']));
				$data['passport_expiry_date'] = date("Y-m-d", strtotime($data['passport_expiry_date']));
				$data['doj'] = date("Y-m-d", strtotime($data['doj']));
				$data['aircraft']  = implode(',', $data['aircraft']);


				$this->custom_db->insert_record('pilot', $data);
				$pilot['pilot_id']  = $this->db->insert_id();
				foreach ($info['training_type'] as $key => $tt) {
					$pilot['training_type'] = $info['training_type'][$key];
					$pilot['training_issue_date'] = date("Y-m-d", strtotime($info['training_issue_date'][$key]));
					$pilot['training_expiry_date'] = date("Y-m-d", strtotime($info['training_expiry_date'][$key]));
					$this->custom_db->insert_record('pilot_training', $pilot);
				}

				$licence['pilot_id']  = $pilot['pilot_id'];

				//debug($info['licence_number']);exit;


				foreach ($info['licence_number'] as $key => $tt) {
					$licence['licence_type'] = $info['licence_type'][$key];
					$licence['licence_number'] = $tt;
					$licence['licence_issue_date'] = date("Y-m-d", strtotime($info['licence_issue_date'][$key]));
					$licence['licence_expiry_date'] = date("Y-m-d", strtotime($info['licence_expiry_date'][$key]));
					$this->custom_db->insert_record('pilot_licence', $licence);
				}







				redirect(base_url() . 'index.php/flight/pilot_list');
			}
		}

		$get_data = $this->uri->segment(3);
		if ($get_data) {
			$cond['origin'] = $get_data;
			$data =  $this->custom_db->single_table_records('pilot', '*', $cond);

			//debug($data);die;
			$condi['pilot_id'] = $data['data'][0]['origin'];
			$training =  $this->custom_db->single_table_records('pilot_training', '*', $condi);
			$page_data['pilot_training'] =  $training['data'];

			if ($data['status']) {
				$page_data['pilot_list'] = $data['data'][0];
			}


			$condi['pilot_id'] = $data['data'][0]['origin'];
			$licence =  $this->custom_db->single_table_records('pilot_licence', '*', $condi);
			$page_data['pilot_licence'] =  $licence['data'];



			//	echo $this->db->last_query();
			//	debug($page_data['pilot_licence']);exit;

			$condition['origin'] = $data['data'][0]['airport_name'];
			$condition['origin'] =801;

			$airport =  $this->custom_db->single_table_records('flight_airport_list', '*', $condition);
			$page_data['pilot_list']['airport_name'] =  $airport['data'][0]['airport_name'];
			$page_data['pilot_list']['airport_code'] =  $airport['data'][0]['origin'];
		}

		$data =  $this->custom_db->single_table_records('country_list');
		if ($data['status']) {
			$page_data['country_list'] = $data['data'];
		}
		/*$licence_data =  $this->custom_db->single_table_records('flight_crs_licence_name_list');
		if ($licence_data['status']) {
			$page_data['licence_list'] = $licence_data['data'];
		}*/

		$aircraft_data =  $this->custom_db->single_table_records('aircrafts');
		if ($aircraft_data['status']) {
			$page_data['aircraft_data'] = $aircraft_data['data'];
		}

		$training_data =  $this->custom_db->single_table_records('flight_crs_training_name_list');
		//debug($training_data);die;
		if ($training_data['status']) {
			$page_data['training_data'] = $training_data['data'];
		}
		$this->template->view('flight/add_pilot', $page_data);
	}
	function pilot_list(): void
	{
		$page_data = [];
		$data =  $this->custom_db->single_table_records('flight_crs_leave_type_list', '*');
		if ($data['status']) {
			$page_data['leaves_type'] = $data['data'];
		}
		$data =  $this->custom_db->single_table_records('pilot_leaves', '*', array('status' => 1));
		if ($data['status']) {
			$page_data['pilot_leaves'] = $data['data'];
		}

		$data =  $this->custom_db->single_table_records('pilot', '*');
		if ($data['status']) {
			$page_data['pilots'] = $data['data'];
		}



		$data =  $this->custom_db->single_table_records('pilot');
		if ($data['status']) {
			$page_data['pilot_list'] = $data['data'];
		} else {
			$page_data['pilot_list'] = array();
		}
		//debug($page_data);exit;
		$this->template->view('flight/pilot_list', $page_data);
	}

	function pilot_licence(int $origin): void
	{
		$data = $this->input->post();
		$page_data = [];

		if (!empty($data)) {
			$data['licence_issue_date'] = date("Y-m-d", strtotime($data['licence_issue_date']));
			$data['licence_expiry_date'] = date("Y-m-d", strtotime($data['licence_expiry_date']));
			$this->custom_db->update_record('pilot_licence', $data, array('origin' => $data['origin']));
		}
		$query = "SELECT pl.origin as pilot_origin ,pl.pilot_id as pilot_id ,  lnl.origin as licence_name_origin , lnl.licence_name as licence_name ,pl.origin as licence_number_origin,pl.licence_number as licence_number ,pl.licence_issue_date as licence_issue_date ,pl.licence_expiry_date as licence_expiry_date, p.first_name as first_name, p.last_name as last_name  FROM pilot_licence pl join pilot p on p.origin = pl.pilot_id join flight_crs_licence_name_list lnl on lnl.origin = pl.licence_type  where p.origin = '$origin'";
		$page_data['pilot_licence'] = $this->db->query($query)->result_array();
		$data =  $this->custom_db->single_table_records('flight_crs_licence_name_list', '*');
		if ($data['status']) {
			$page_data['licence_type'] = $data['data'];
		}
		$this->template->view('flight/flight_crs_pilot_licence', $page_data);
	}

	function pilot_training(int $origin): void
	{
		$page_data = [];

		$data = $this->input->post();

		if (!empty($data)) {
			$data['training_issue_date'] = date("Y-m-d", strtotime($data['training_issue_date']));
			$data['training_expiry_date'] = date("Y-m-d", strtotime($data['training_expiry_date']));
			$this->custom_db->update_record('pilot_training', $data, array('origin' => $data['origin']));
		}
		$query = "SELECT pt.origin as pilot_origin ,pt.pilot_id as pilot_id , tnl.origin as training_origin , tnl.training_name as training_name ,pt.training_issue_date as training_issue_date ,pt.training_expiry_date as training_expiry_date, p.first_name as first_name, p.last_name as last_name FROM pilot_training pt join pilot p on p.origin = pt.pilot_id join flight_crs_training_name_list tnl on tnl.origin = pt.training_type where p.origin = '$origin'";
		$page_data['pilot_training'] = $this->db->query($query)->result_array();
		$data =  $this->custom_db->single_table_records('flight_crs_training_name_list', '*');
		if ($data['status']) {
			$page_data['training_type'] = $data['data'];
		}

		//	debug($page_data);exit;
		$this->template->view('flight/flight_crs_pilot_training', $page_data);
	}
	function pilot_leave(int $origin): void
	{
		$page_data = [];

		$data =  $this->custom_db->single_table_records('flight_crs_leave_type_list', '*');
		if ($data['status']) {
			$page_data['leaves_type'] = $data['data'];
		}
		$data =  $this->custom_db->single_table_records('pilot_leaves', '*', array('status' => 1, 'pilot_id' => $origin));
		if ($data['status']) {
			$page_data['pilot_leaves'] = $data['data'];
		}

		$data =  $this->custom_db->single_table_records('pilot', '*', array('origin' => $origin));
		//	echo $this->db->last_query();
		if ($data['status']) {
			$page_data['pilots'] = $data['data'];
		}

		//debug($page_data);exit;


		$this->template->view('flight/flight_crs_pilot_leave', $page_data);
	}

	function fdtl_temp_details(): void
	{

		$page_data = array();
		$pilot_fdtl_details = [];
		//$flight_list = array();
		$neworigin = array();
		$data = $this->input->post();
		$pilot_id = $this->uri->segment(3);
		if (!empty($data)) {
			// debug($data);
			asort($data);
			$i = 0;
			foreach ($data as $key => $value) {
				if ($key == 'common') {
					continue;
				}
				if ($key == 'block_time') {
					continue;
				}
				if ($key == 'no_of_landindgs') {
					continue;
				}
				if ($key == 'night_time') {
					continue;
				}
				$temp = array();

				$k = explode('_', $key);
				$org = $k[1];
				$neworigin[$i] = $org;
				$i++;

				$fdtl_details = 'select * from fdtl_details_temp where origin = ' . $org;
				$x =  $this->custom_db->get_custom_query($fdtl_details);
				if ($x[status]) {
					$fdtl = $x['data'][0];
					if ($pilot_id == $fdtl['pilot_in_command']) {
						$temp['pilot_update_status'] = 1;
						if ($value == 1) {
							$temp['pilot_first_flight'] = 1;
						}
						if ($value == 2) {
							$temp['pilot_middle_flight'] = 2;
						}
						if ($value == 3) {
							$temp['pilot_last_flight'] = 3;
						}
						if ($value == 4) {
							$temp['pilot_first_flight'] = 1;
							$temp['pilot_last_flight'] = 3;
						}
					}
					if ($pilot_id == $fdtl['co_pilot']) {
						$temp['co_pilot_update_status'] = 1;
						if ($value == 1) {
							$temp['co_pilot_first_flight'] = 1;
						}
						if ($value == 2) {
							$temp['co_pilot_middle_flight'] = 2;
						}
						if ($value == 3) {
							$temp['co_pilot_last_flight'] = 3;
						}
						if ($value == 4) {
							$temp['co_pilot_first_flight'] = 1;
							$temp['co_pilot_last_flight'] = 3;
						}
					}
				}

				//debug($temp);exit;
				$this->custom_db->update_record('fdtl_details_temp', $temp, array('origin' => $org));
			}

			//debug($x);

			$fl1 = 'select * from fdtl_details_temp where pilot_in_command = ' . $pilot_id . ' and pilot_first_flight = 1 and jlb_date="' . $x[data][0]['jlb_date'] . '"';
			$f_l1 =  $this->custom_db->get_custom_query($fl1);
			if ($f_l1['status']) {
				$first_flight = $f_l1['data'];
			} else {
				$fl1 = 'select * from fdtl_details_temp where co_pilot = ' . $pilot_id . ' and co_pilot_first_flight = 1 and jlb_date="' . $x[data][0]['jlb_date'] . '"';
				$f_l1 =  $this->custom_db->get_custom_query($fl1);
				if ($f_l1['status']) {
					$first_flight = $f_l1['data'];
				}
			}

			$fl3 = 'select * from fdtl_details_temp where pilot_in_command = ' . $pilot_id . ' and pilot_last_flight = 3 and jlb_date="' . $x[data][0]['jlb_date'] . '"';
			$f_l3 =  $this->custom_db->get_custom_query($fl3);
			if ($f_l3['status']) {
				$last_flight = $f_l3['data'];
			} else {
				$fl3 = 'select * from fdtl_details_temp where co_pilot = ' . $pilot_id . ' and co_pilot_last_flight = 3 and jlb_date="' . $x[data][0]['jlb_date'] . '"';
				$f_l3 =  $this->custom_db->get_custom_query($fl3);
				if ($f_l3['status']) {
					$last_flight = $f_l3['data'];
				}
			}
			$fl2 = 'select * from fdtl_details_temp where pilot_in_command = ' . $pilot_id . ' and pilot_middle_flight = 2 and jlb_date="' . $x[data][0]['jlb_date'] . '"';
			$f_l2 =  $this->custom_db->get_custom_query($fl2);
			if ($f_l2['status']) {
				$middle_flight1 = $f_l2['data'];
			}
			$fl22 = 'select * from fdtl_details_temp where co_pilot = ' . $pilot_id . ' and co_pilot_middle_flight = 2 and jlb_date="' . $x[data][0]['jlb_date'] . '"';
			$f_l22 =  $this->custom_db->get_custom_query($fl22);
			if ($f_l22['status']) {
				$middle_flight2 = $f_l22['data'];
			}
			$middle_flight = array_merge($middle_flight1, $middle_flight2);
			uasort($middle_flight, function ($a, $b) {
				return strcmp($a['chocks_off_time'], $b['chocks_off_time']);
			});
			//$flight_list = array_merge($first_flight, $middle_flight, $last_flight);

			//debug($flight_list);
			$duty_start_time = date("H:i:s", strtotime("-45 minutes", strtotime($first_flight[0]['chocks_off_time'])));
			$duty_end_time = date("H:i:s", strtotime("+15 minutes", strtotime($last_flight[0]['chocks_on_time'])));
			$date1 = new DateTime($duty_end_time);
			$date2 = $date1->diff(new DateTime($duty_start_time));
			$hours =  $date2->h * 60;
			$mins  =  $hours + $date2->i;
			$total_duty_time = floor($mins / 60) . ':' . ($mins % 60);
			$c_duty_time = $total_duty_time;

			$x = 'select jlb_date from fdtl_details_temp where origin= ' . $org;
			$y =  $this->custom_db->get_custom_query($x);
			$date1 =  $y['data'][0]['jlb_date'];

			$date = date('Y-m-d', strtotime('+1 day', strtotime($date1)));
			$total_flight_time = 0;
			$total_duty_period = 0;

			for ($i = 1; $i < 8; $i++) {
				$l_date = date('Y-m-d', strtotime('-' . $i . ' day', strtotime($date)));
				//debug($date);
				$temp = "SELECT * FROM `fdtl_details` WHERE `pilot_id` = " . $pilot_id . " AND `current_date` = '" . $l_date . "'";
				$f_temp =  $this->custom_db->get_custom_query($temp);
				//debug($f_temp);
				if ($f_temp['status']) {
					$flight[$i] = $f_temp['data'][0];
					$t_f_t  = $f_temp['data'][0]['total_flight_time'];
					if (strpos($t_f_t, ':') !== false) {
						list($hours, $minutes) = explode(':', $t_f_t);
					}
					$flight_time = $hours * 60 + $minutes;
					$total_flight_time = $total_flight_time + $flight_time;
					$t_d_p  = $f_temp['data'][0]['total_duty_time'];
					if (strpos($t_d_p, ':') !== false) {
						list($hours, $minutes) = explode(':', $t_d_p);
					}
					$duty_peirod = $hours * 60 + $minutes;
					$total_duty_period = $total_duty_period + $duty_peirod;
				}
				//debug($total_duty_period);
			}


			//debug($total_flight_time);
			//debug($data['block_time']);
			if (strpos($data['block_time'], ':') !== false) {
				list($hours, $minutes) = explode(':', $data['block_time']);
			}
			$b_time = $hours * 60 + $minutes;
			$total_flight_time = $total_flight_time + $b_time;
			//debug($total_flight_time);

			if (strpos($c_duty_time, ':') !== false) {
				list($hours, $minutes) = explode(':', $c_duty_time);
			}
			$b_duty = $hours * 60 + $minutes;
			$total_duty_period = $total_duty_period + $b_duty;



			$last_7_flight_time = floor($total_flight_time / 60) . ':' . ($total_flight_time % 60);
			$last_7_duty_period = floor($total_duty_period / 60) . ':' . ($total_duty_period % 60);
			//debug($last_7_flight_time);
			//debug($last_7_duty_period);

			//exit;
			$total_flight_time = 0;
			for ($i = 1; $i < 31; $i++) {
				$l_date = date('Y-m-d', strtotime('-' . $i . ' day', strtotime($date)));
				$temp = "SELECT * FROM `fdtl_details` WHERE `pilot_id` = " . $pilot_id . " AND `current_date` = '" . $l_date . "'";
				$f_temp =  $this->custom_db->get_custom_query($temp);
				if ($f_temp['status']) {
					$flight[$i] = $f_temp['data'][0];

					$t_f_t  = $f_temp['data'][0]['total_flight_time'];
					if (strpos($t_f_t, ':') !== false) {
						list($hours, $minutes) = explode(':', $t_f_t);
					}
					$flight_time = $hours * 60 + $minutes;
					$total_flight_time = $total_flight_time + $flight_time;
				}
			}
			if (strpos($data['block_time'], ':') !== false) {
				list($hours, $minutes) = explode(':', $data['block_time']);
			}
			$b_time = $hours * 60 + $minutes;
			$total_flight_time = $total_flight_time + $b_time;

			$last_30_flight_time = floor($total_flight_time / 60) . ':' . ($total_flight_time % 60);
			$total_flight_time = 0;

			for ($i = 1; $i < 366; $i++) {
				$l_date = date('Y-m-d', strtotime('-' . $i . ' day', strtotime($date)));
				$temp = "SELECT * FROM `fdtl_details` WHERE `pilot_id` = " . $pilot_id . " AND `current_date` = '" . $l_date . "'";
				$f_temp =  $this->custom_db->get_custom_query($temp);

				if ($f_temp['status']) {
					$flight[$i] = $f_temp['data'][0];

					$t_f_t  = $f_temp['data'][0]['total_flight_time'];
					if (strpos($t_f_t, ':') !== false) {
						list($hours, $minutes) = explode(':', $t_f_t);
					}
					$flight_time = $hours * 60 + $minutes;
					$total_flight_time = $total_flight_time + $flight_time;
				}
			}
			if (strpos($data['block_time'], ':') !== false) {
				list($hours, $minutes) = explode(':', $data['block_time']);
			}
			$b_time = $hours * 60 + $minutes;
			$total_flight_time = $total_flight_time + $b_time;

			$last_365_flight_time = floor($total_flight_time / 60) . ':' . ($total_flight_time % 60);
			$total_duty_period = 0;

			for ($i = 1; $i < 15; $i++) {
				$l_date = date('Y-m-d', strtotime('-' . $i . ' day', strtotime($date)));
				$temp = "SELECT * FROM `fdtl_details` WHERE `pilot_id` = " . $pilot_id . " AND `current_date` = '" . $l_date . "'";
				$f_temp =  $this->custom_db->get_custom_query($temp);

				if ($f_temp['status']) {
					$flight[$i] = $f_temp['data'][0];

					$t_d_p  = $f_temp['data'][0]['total_duty_time'];
					if (strpos($t_d_p, ':') !== false) {
						list($hours, $minutes) = explode(':', $t_d_p);
					}
					$duty_peirod = $hours * 60 + $minutes;
					$total_duty_period = $total_duty_period + $duty_peirod;
				}
			}

			if (strpos($c_duty_time, ':') !== false) {
				list($hours, $minutes) = explode(':', $c_duty_time);
			}
			$b_duty = $hours * 60 + $minutes;
			$total_duty_period = $total_duty_period + $b_duty;


			$last_14_duty_period = floor($total_duty_period / 60) . ':' . ($total_duty_period % 60);
			$total_duty_period = 0;

			for ($i = 1; $i < 29; $i++) {
				$l_date = date('Y-m-d', strtotime('-' . $i . ' day', strtotime($date)));
				$temp = "SELECT * FROM `fdtl_details` WHERE `pilot_id` = " . $pilot_id . " AND `current_date` = '" . $l_date . "'";
				$f_temp =  $this->custom_db->get_custom_query($temp);

				if ($f_temp['status']) {
					$flight[$i] = $f_temp['data'][0];

					$t_d_p  = $f_temp['data'][0]['total_duty_time'];
					if (strpos($t_d_p, ':') !== false) {
						list($hours, $minutes) = explode(':', $t_d_p);
					}
					$duty_peirod = $hours * 60 + $minutes;
					$total_duty_period = $total_duty_period + $duty_peirod;
				}
			}

			if (strpos($c_duty_time, ':') !== false) {
				list($hours, $minutes) = explode(':', $c_duty_time);
			}
			$b_duty = $hours * 60 + $minutes;
			$total_duty_period = $total_duty_period + $b_duty;


			$last_28_duty_period = floor($total_duty_period / 60) . ':' . ($total_duty_period % 60);


			/*uasort($flight, function($a,$b){
						return strcmp($a['chocks_off_time'], $b['chocks_off_time']);
					});*/

			/*debug($flight); exit;*/

			$pilot_fdtl_details['duty_start_time'] 	=   $duty_start_time;
			$pilot_fdtl_details['duty_end_time'] 	=  $duty_end_time;
			$pilot_fdtl_details['total_duty_time'] 	=  $total_duty_time;
			$pilot_fdtl_details['total_flight_time'] =  $data['block_time'];
			$pilot_fdtl_details['total_flight_night_time'] =  $data['night_time'];
			$pilot_fdtl_details['no_of_landindgs'] 	=  $data['no_of_landindgs'];

			$pilot_fdtl_details['flight_time_7_days'] =  $last_7_flight_time;
			$pilot_fdtl_details['duty_period_7_days'] =  $last_7_duty_period;


			$pilot_fdtl_details['flight_time_30_days'] =  $last_30_flight_time;
			$pilot_fdtl_details['duty_period_14_days'] =  $last_14_duty_period;

			$pilot_fdtl_details['flight_time_365_days'] =  $last_365_flight_time;
			$pilot_fdtl_details['duty_period_28_days'] =  $last_28_duty_period;


			$pilot_fdtl_details['pilot_id'] =  $pilot_id;
			$pilot_fdtl_details['current_date'] = $date1;


			/*	debug($pilot_fdtl_details);
				exit;*/

			$this->custom_db->insert_record('fdtl_details', $pilot_fdtl_details);
		}
		//echo $pilot_id;die;
		$pilot = 'select * from fdtl_details_temp where pilot_in_command = ' . $pilot_id . ' and pilot_update_status = 0';
		$all_pilot =  $this->custom_db->get_custom_query($pilot);
		//echo "aoas";die;
		if ($all_pilot['status']) {
			$pilot_in_command = $all_pilot['data'];
		}

		//	debug($pilot_in_command);die;
		$co_pilot = 'select * from fdtl_details_temp where co_pilot = ' . $pilot_id . ' and co_pilot_update_status = 0';
		$all_co_pilot =  $this->custom_db->get_custom_query($co_pilot);

		if ($all_co_pilot['status']) {
			$co_pilot_in_command = $all_co_pilot['data'];
		}
		if (!empty($pilot_in_command)) {
			if (!empty($co_pilot_in_command)) {
				$fdtl_temp_details = array_merge($pilot_in_command, $co_pilot_in_command);
			} else {
				$fdtl_temp_details = $pilot_in_command;
			}
		} else {
			if (!empty($co_pilot_in_command)) {
				$fdtl_temp_details = $co_pilot_in_command;
			}
		}

		uasort($fdtl_temp_details, function ($a, $b) {
			return strcmp($a['chocks_off_time'], $b['chocks_off_time']);
		});



		// /debug($fdtl_temp_details);die;
		if (is_array($fdtl_temp_details)) {
			$jlb_date = array();
			$i = 0;
			foreach ($fdtl_temp_details as $key => $fdtl) {

				$flight =  $this->custom_db->single_table_records('flight_crs_segment_details', 'flight_num', array('fsid' => $fdtl['flight_number']));
				if ($flight['status']) {
					$flight_number = $flight['data'][0]['flight_num'];
				}
				$fdtl['flight_number'] = $flight_number;

				$jlb_date[$fdtl['jlb_date']][$i] = $fdtl;
				$i++;
			}
			//debug($jlb_date);
			ksort($jlb_date);

			$page_data['fdtl_details_temp'] = $jlb_date;
		}

		//exit;


		$pilot =  $this->custom_db->single_table_records('pilot', 'first_name,last_name', array('origin' => $pilot_id));
		if ($pilot['status']) {
			$page_data['pilot_name'] = $pilot['data'];
		}
		//debug($page_data);exit;
		$this->template->view('flight/flight_crs_fdtl_temp_details', $page_data);
	}


	function fdtl_details(int $origin): void
	{

		$page_data = [];
		$fdtl_details = 'select * from fdtl_details where pilot_id = ' . $origin . ' ORDER BY origin';
		$fdtl =  $this->custom_db->get_custom_query($fdtl_details);
		//debug($fdtl);exit; 

		///$fdtl =  $this->custom_db->single_table_records('fdtl_details','*',array('pilot_id'=> $origin));
		if ($fdtl['status']) {
			$page_data['fdtl_details_temp'] = $fdtl['data'];
		}

		$pilot =  $this->custom_db->single_table_records('pilot', 'first_name,last_name', array('origin' => $origin));
		if ($pilot['status']) {
			$page_data['pilot_name'] = $pilot['data'];
			$page_data['pilot_name'][0]['pilot_id'] = $origin;
		}

		$flight =  $this->custom_db->single_table_records('flight_crs_segment_details', 'flight_num', array('fsid' => $fdtl['data'][0]['flight_number']));

		if ($flight['status']) {
			$page_data['flight_num'] = $flight['data'];
		}


		$this->template->view('flight/flight_crs_fdtl_details', $page_data);
	}

	function add_aircraft(): void
	{
	/*	ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		*/
		$page_data = array();
		$config = [];
		$data1 = [];
		$condi = [];
		$cond = [];
		$file_data_array = [];
		$file_data_ar = [];
		$data = $this->input->post();


		if (!empty($data)) {
			//debug($data); 
			// exit;
			if ($data['origin'] != 0) {

				$data['fuel_type'] = implode(",", $data['fuel_type']);
				$data['component'] = json_encode($data['component']);

				// debug($_FILES['files']);
				if (isset($_FILES['files'])) {
					$count =  isset($_FILES['files']['name']) ? count($_FILES['files']['name']) : 0;

					for ($i = 0; $i < $count; $i++) {

						if (!empty($_FILES['files']['name'][$i])) {



							$_FILES['file']['name'] = $_FILES['files']['name'][$i];
							$_FILES['file']['type'] = $_FILES['files']['type'][$i];
							$_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$i];
							$_FILES['file']['error'] = $_FILES['files']['error'][$i];
							$_FILES['file']['size'] = $_FILES['files']['size'][$i];

							$config['upload_path'] = DOMAIN_IMAGE_UPLOAD_DIR;
							$config['allowed_types'] = '*';
							$config['max_size'] = '100000';
							$config['file_name'] = $_FILES['files']['name'][$i];

							$this->load->library('upload', $config);

							if ($this->upload->do_upload('file')) {
								$uploadData = $this->upload->data();
								$filename = $uploadData['file_name'];

								$data1['totalFiles'][] = $filename;
							}
						}
					}
				}
				//debug($data['files']);
				//debug($data1);
				if (isset($data['files'])) {

					$file = $data['files'];
					if (isset($data1['totalFiles'])) {
						$all_file = array_merge($data['files'], $data1['totalFiles']);
					} else {
						$all_file = $file;
					}
				} else {

					$all_file = $data1['totalFiles'];
				}
				//debug($all_file);  					exit;

				foreach ($data['documentation_type'] as $key => $sc) {

					if ($sc == '') {
						continue;
					}

					$condi['origin'] = $sc;
					$crs_comp1 =  $this->custom_db->single_table_records('flight_crs_document_name_list', 'document_name', $condi);

					if ($crs_comp1['status']) {
						$document_name = $crs_comp1['data'][0]['document_name'];
					}

					$file_data_ar[] = array(

						"document_name" => $document_name,
						"documentation_type" => $sc,
						"issue_date" => $data['issue_date'][$key],
						"date_of_expiry" => $data['date_of_expiry'][$key],
						"remarks" => $data['remarks'][$key],
						"file" => $all_file[$key],

					);
					$document = json_encode($file_data_ar);
				}
				unset($data['issue_date']);
				unset($data['date_of_expiry']);
				unset($data['remarks']);
				unset($data['files']);
				unset($data['documentation_type']);
				$data['documentation'] = $document;

				foreach ($data['serialcomponent'] as $key => $sc) {

					if ($sc != '') {
						$condi['origin'] = $sc;
						$crs_comp =  $this->custom_db->single_table_records('flight_crs_components', 'serial_number', $condi);
						if ($crs_comp['status']) {
							$serialnumber = $crs_comp['data'][0]['serial_number'];
						}


						$file_data_array[] = array(
							"component_name" => $data['component_name'][$key],
							"partcomponent" => $data['partcomponent'][$key],
							"serialnumber" => $serialnumber,
							"serialcomponent" => $sc,
							"fitted_date" => $data['fitted_date'][$key],
							"fitted_time" => $data['fitted_time'][$key],

						);
						$component = json_encode($file_data_array);
					}
				}
				unset($data['component_name']);
				unset($data['serialcomponent']);
				unset($data['partcomponent']);
				unset($data['fitted_date']);
				unset($data['fitted_time']);
				$data['component'] = $component;


				//debug($data);exit;
				$this->custom_db->update_record('aircrafts', $data, array('origin' => $data['origin']));


				redirect(base_url() . 'index.php/flight/aircraft_list');
			} else {



				unset($data['origin']);
				unset($data['seat_numbers']);

				$data['induction_at_base'] = date("Y-m-d", strtotime($data['induction_at_base']));
				$data['component'] = json_encode($data['component']);
				$data['fuel_type'] = implode(",", $data['fuel_type']);
				$data['created_by'] = $GLOBALS['CI']->entity_user_id;

				//Seat Layout
				$columns = $data['seat_coulumns'];
				$columns_count = strlen($columns);
				$rows = $data['seat_row_count'];
				$seat_gap = $data['seat_coulmn_space'];



				$this->form_validation->set_rules("seat_row_count", "Seat Rows Count", "required");
				$this->form_validation->set_rules("seat_coulumns", "Seat Columns Count", "required");
				$this->form_validation->set_rules("seating_capacity", "Total Seat Count", "required");
				$this->form_validation->set_rules("seat_coulmn_space", "Seat Column Space", "required");



				if (isset($_FILES['files'])) {
					$count =  isset($_FILES['files']['name']) ? count($_FILES['files']['name']) : 0;
					for ($i = 0; $i <= $count; $i++) {
						if (!empty($_FILES['files']['name'][$i])) {
							$_FILES['file']['name'] = $_FILES['files']['name'][$i];
							$_FILES['file']['type'] = $_FILES['files']['type'][$i];
							$_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$i];
							$_FILES['file']['error'] = $_FILES['files']['error'][$i];
							$_FILES['file']['size'] = $_FILES['files']['size'][$i];

							$config['upload_path'] = DOMAIN_IMAGE_UPLOAD_DIR;
							$config['allowed_types'] = '*';
							$config['max_size'] = '100000';
							$config['file_name'] = $_FILES['files']['name'][$i];
							// debug($config);exit;
							$this->load->library('upload', $config);
							if ($this->upload->do_upload('file')) {
								$uploadData = $this->upload->data();
								$filename = $uploadData['file_name'];

								$all_file[] = $filename;
							}
						}
					}
				}

				// if (isset($_FILES['aicraft_image'])) {

				// 	if (!empty($_FILES['aicraft_image']['name'])) {
				// 		$_FILES['file']['name'] = $_FILES['aicraft_images']['name'];
				// 		$_FILES['file']['type'] = $_FILES['aicraft_images']['type'];
				// 		$_FILES['file']['tmp_name'] = $_FILES['aicraft_image']['tmp_name'];
				// 		$_FILES['file']['error'] = $_FILES['aicraft_image']['error'];
				// 		$_FILES['file']['size'] = $_FILES['aicraft_image']['size'];

				// 		$config['upload_path'] = DOMAIN_IMAGE_UPLOAD_DIR;
				// 		$config['allowed_types'] = '*';
				// 		$config['max_size'] = '100000';
				// 		$config['file_name'] = $_FILES['aicraft_image']['name'];
				// 		// debug($config);exit;
				// 		$this->load->library('upload', $config);
				// 		if ($this->upload->do_upload('file')) {
				// 			$uploadData = $this->upload->data();
				// 			$aircraft_image = $uploadData['file_name'];
				// 		}
				// 	}
				// }

				foreach ($data['documentation_type'] as $rr => $sc) {
					$condi['origin'] = $sc;
					$crs_comp1 =  $this->custom_db->single_table_records('flight_crs_document_name_list', 'document_name', $condi);
					if ($crs_comp1['status']) {
						$document_name = $crs_comp1['data'][0]['document_name'];
					}

					$file_data_ar[] = array(
						"document_name" => 		$document_name,
						"documentation_type" => $sc,
						"issue_date" =>			date("Y-m-d", strtotime($data['issue_date'][$rr])),
						"date_of_expiry" => date("Y-m-d", strtotime($data['date_of_expiry'][$rr])),
						"remarks" => 			$data['remarks'][$rr],
						"file" => 				$all_file[$rr],
					);
					//debug($file_data_ar);
					$document = json_encode($file_data_ar);
				}

				unset($data['issue_date']);
				unset($data['date_of_expiry']);
				unset($data['remarks']);
				unset($data['file']);
				unset($data['documentation_type']);
				$data['documentation'] = $document;


				foreach ($data['serialcomponent'] as $key => $sc) {
					if ($sc != '') {
						$file_data_array[] = array(
							"serialcomponent" => $sc,
							"component_name" => $data['component_name'][$key],
							"partcomponent" => $data['partcomponent'][$key],
							"fitted_date" => $data['fitted_date'][$key],
							"fitted_time" => $data['fitted_time'][$key],
						);
						$component = json_encode($file_data_array);
					}
				}
				unset($data['component_name']);
				unset($data['serialcomponent']);
				unset($data['partcomponent']);
				unset($data['fitted_date']);
				unset($data['fitted_time']);
				$data['component'] = $component;
				// $data['aircraft_image'] = $aircraft_image;

				$data["seat_row_count"] = $this->input->post("seat_row_count");
				$data["seat_coulumns"] = $this->input->post("seat_coulumns");
				$data["seating_capacity"] = $this->input->post("seating_capacity");
				$data["passenger"] = $this->input->post("seating_capacity");
				$data["seat_coulmn_space"] = $this->input->post("seat_coulmn_space");
				$data["speed"] = $this->input->post("speed");
				$data["pilots"] = $this->input->post("pilots");

				$data["from_exit"] = $this->input->post("speed")['from_exit'];
				$data["to_exit"] = $this->input->post("to_exit")[0];




				$insert_data = $this->custom_db->insert_record('aircrafts', $data);

				if ($insert_data['status'] == SUCCESS_STATUS) {
					// if(1){

					$flight_aircraft_id = $insert_data['insert_id'];
					// $flight_aircraft_id =42;
					// debug($columns);exit;
					$last_count = strlen($columns) - 1;
					for ($i = 0; $i < strlen($columns); $i += strlen($columns)) {
						for ($l = 1; $l <= $rows; $l++) {

							if ($seat_gap == 3 && $columns_count == 6) {
								$seats[] = array(
									$l . $columns[$i] => 'W',
									$l . $columns[$i + 1] => 'C',
									$l . $columns[$i + 2] => 'A',
									$l . $columns[$i + 3] => 'A',
									$l . $columns[$i + 4] => 'C',
									$l . $columns[$last_count] => 'W'
								);
							} elseif ($seat_gap == 2 && $columns_count == 6) {
								$seats[] = array(
									$l . $columns[$i] => 'W',
									$l . $columns[$i + 1] => 'A',
									$l . $columns[$i + 2] => 'A',
									$l . $columns[$i + 3] => 'A',
									$l . $columns[$i + 4] => 'A',
									$l . $columns[$last_count] => 'W'
								);
							} elseif ($seat_gap == 0 && $columns_count == 6) {
								$seats[] = array(
									$l . $columns[$i] => 'W',
									$l . $columns[$i + 1] => 'C',
									$l . $columns[$i + 2] => 'C',
									$l . $columns[$i + 3] => 'C',
									$l . $columns[$i + 4] => 'C',
									$l . $columns[$last_count] => 'W'
								);
							} elseif ($seat_gap == 2 && $columns_count == 4) {
								$seats[] = array(
									$l . $columns[$i] => 'W',
									$l . $columns[$i + 1] => 'A',
									$l . $columns[$i + 2] => 'A',
									$l . $columns[$last_count] => 'W'
								);
							} elseif ($seat_gap == 4 && $columns_count == 8) {
								$seats[] = array(
									$l . $columns[$i] => 'W',
									$l . $columns[$i + 1] => 'C',
									$l . $columns[$i + 2] => 'C',
									$l . $columns[$i + 3] => 'A',
									$l . $columns[$i + 4] => 'A',
									$l . $columns[$i + 5] => 'C',
									$l . $columns[$i + 6] => 'C',
									$l . $columns[$last_count] => 'W'
								);
							}
						}
					}


					//Seat data inserting in seat table
					foreach ($seats as $key => $value) {
						foreach ($value as $column_key => $column_value) {
							$this->custom_db->insert_record('flight_aircraft_seat', array(
								'flight_aircraft_id' => $flight_aircraft_id,
								'seat_number' => $column_key,
								'seat_type' => $column_value,
								'seat_flag' => 'AVA',
								'status' => 1,
								'created_date' => date('Y-m-d h:i:s'),
								'created_by_id' => $this->entity_user_id,
							));
							//inserting aircraft seat details 
						}
					}
				}

				redirect(base_url() . 'index.php/flight/aircraft_list');
			}
		}
		$get_data = $this->uri->segment(3);

		if ($get_data) {
			$cond['origin'] = $get_data;
			$data =  $this->custom_db->single_table_records('aircrafts', '*', $cond);

			$flight_aircraft_seat = $this->custom_db->single_table_records('flight_aircraft_seat', '*', array('flight_aircraft_id' => $cond['origin'])); //fetching aircraft details based on id

			$page_data['flight_aircraft_seat'] = $flight_aircraft_seat;
			// debug($flight_aircraft_seat);exit;

			$condi['id'] = $data['data'][0]['icao_code_of_airport'];
			$airport =  $this->custom_db->single_table_records('flight_airport_list', '*', $condi);
			if ($data['status']) {
				$page_data['aircrafts'] = $data['data'][0];
				$page_data['aircrafts']['component'] = json_decode($data['data'][0]['component']);
				$page_data['aircrafts']['documentation'] = json_decode($data['data'][0]['documentation']);
			}
			$page_data['aircrafts']['icao_code_of_airport'] =  $airport['data'][0]['airport_name'] . ' - (' . $airport['data'][0]['airport_code'] . ')';
			$page_data['aircrafts']['icao_code'] = $data['data'][0]['icao_code_of_airport'];
		}


		$query = "SELECT  *,c.origin as org ,fc.component_type as type FROM flight_crs_components c LEFT join flight_crs_component_type_list fc on c.component_type = fc.origin";
		$page_data['allcomp_type'] = $this->db->query($query)->result_array();

		$document =  $this->custom_db->single_table_records('flight_crs_document_name_list');
		if ($document['status']) {
			$page_data['doc_type'] = $document['data'];
		}
		//$query = "SELECT * FROM flight_crs_components where component_name = '$origin' or serial_number ='$origin' ";
		//$component_list = $this->db->query($query)->result_array();



		// debug($page_data);exit;
		$this->template->view('flight/add_aircraft', $page_data);
	}



	function aircraft_list(): void
	{
		$page_data = array();
		$data =  $this->custom_db->single_table_records('aircrafts');
		//debug($data);die;
		if ($data['status']) {
			$page_data['all_aircrafts_list'] = $data['data'];
		}



		$data =  $this->custom_db->single_table_records('aircrafts');

		if ($data['status']) {

			foreach ($data['data'] as $k => $aircraft_data) {

				$f_type = explode(',', $aircraft_data['fuel_type']);
				$fuel_type = [];
				foreach ($f_type as $key =>  $value) {
					if ($value == 0) {
						$fuel_type[$key] = 'AVGAS 100LL';
					}
					if ($value == 1) {
						$fuel_type[$key] = 'MOGAS 95';
					}
					if ($value == 2) {
						$fuel_type[$key] = 'JET A1';
					}
				}
				$data['data'][$k]['fuel_type'] = $fuel_type;


				$data1 =  $this->custom_db->single_table_records('flight_crs_document_name_list', '*', array('origin' =>  $aircraft_data['documentation_type']));
				$data['data'][$k]['documentation_type'] = $data1['data'][0]['document_name'];


			//	$data2 =  $this->custom_db->single_table_records('flight_airport_list', '*', array('id' =>  $aircraft_data['icao_code_of_airport']));
				//$data['data'][$k]['icao_code_of_airport'] = $data2['data'][0]['airport_name'] . '(' . $data2['data'][0]['airport_code'] . ')';
			}
		}
		$page_data['aircrafts_list'] = $data['data'];

		//debug($page_data);exit;

		$this->template->view('flight/aircraft_list2', $page_data);
	}
	function flight_crs_tax_list(): void
	{
		$page_data = [];
		$data =  $this->custom_db->single_table_records('flight_crs_tax_list');
		if ($data['status']) {
			$page_data['tax_list'] = $data['data'];
		} else {
			$page_data['tax_list'] = array();
		}
		$this->template->view('flight/flight_crs_tax_list', $page_data);
	}
	function add_tax(): void
	{
		$data = $this->input->post();
		if (!empty($data)) {
			if ($data['origin'] != 0) {
				$this->custom_db->update_record('flight_crs_tax_list', $data, array('origin' => $data['origin']));
			} else {
				unset($data['origin']);
				$this->custom_db->insert_record('flight_crs_tax_list', $data);
			}
		}
		redirect(base_url() . 'index.php/flight/flight_crs_tax_list');
	}
	function delete_tax(int $origin): void
	{
		$cond = [];
		$cond['origin'] = $origin;
		$result = $this->custom_db->delete_record('flight_crs_tax_list', $cond);
		echo json_encode(array('status' => true));
		exit;
	}
	function journey_log(): void
	{
		$page_data = [];
		$data = $this->input->post();
		if (!empty($data)) {

			if ($data['origin'] != 0) {
				$data['date'] = date("Y-m-d", strtotime($data['date']));
				$this->custom_db->update_record('journey_log', $data, array('origin' => $data['origin']));
				$this->db->last_query();
			} else {
				unset($data['origin']);
				$data['date'] = date("Y-m-d", strtotime($data['date']));
				$data['created_by'] = $GLOBALS['CI']->entity_user_id;
				$this->custom_db->insert_record('journey_log', $data);
			}

			redirect(base_url() . 'index.php/flight/journey_log');
		}
		$data =  $this->custom_db->single_table_records('journey_log');
		if ($data['status']) {
			$page_data['journey_list'] = $data['data'];
		} else {
			$page_data['journey_list'] = array();
		}
		$sdata =  $this->custom_db->single_table_records('aircrafts', 'origin,reg');
		if ($sdata['status']) {
			$page_data['regno'] = $sdata['data'];
		}
		$this->template->view('flight/journey_log', $page_data);
	}
	function add_jlb_details(): void
	{
		$data = $this->input->post();
		$temp = [];
		$cond = [];
		$condi = [];
		$condd = [];
		$page_data = [];
		$fdtlcond = [];
		$condition2 = [];
		if (!empty($data)) {
			//debug($data);
			//exit;
			if ($data['origin'] != 0) {
				$info = $data;
				unset($data['fdtl_temp']);
				$this->custom_db->update_record('jlb_details', $data, array('origin' => $data['origin']));
				$temp['journey_log'] =  $data['journey_log'];
				$temp['chocks_on_time'] =  $data['chocks_on_time'];
				$temp['chocks_off_time'] =  $data['chocks_off_time'];
				$temp['block_time'] =  $data['block_time'];
				$temp['total_no_of_landings'] =  $data['total_no_of_landings'];

				$this->custom_db->update_record('fdtl_details_temp', $temp, array('origin' => $info['fdtl_temp']));
				//echo $this->db->last_query();
				//exit;


			} else {
				$info = $data;
				unset($info['origin']);
				unset($info['fdtl_temp']);
				$info['jlb_date'] = date("Y-m-d", strtotime($info['jlb_date']));
				$info['chocks_on_date'] = date("Y-m-d", strtotime($info['chocks_on_date']));
				$info['chocks_off_date'] = date("Y-m-d", strtotime($info['chocks_off_date']));
				$info['created_by'] = $GLOBALS['CI']->entity_user_id;
				$info['common'] = $info['common'];
				$this->custom_db->insert_record('jlb_details', $info);

				$temp['journey_log'] =  $data['journey_log'];
				$temp['jlb_number'] =   $this->db->insert_id();
				$temp['jlb_date'] =   $info['jlb_date'];
				$temp['flight_number'] =  $data['flight_number'];
				$temp['flight_from'] =   $info['from_city'];
				$temp['flight_to'] =   $info['to_city'];
				$temp['pilot_in_command'] =  $data['pilot_in_command'];
				$temp['co_pilot'] =  $data['co_pilot'];
				$temp['chocks_on_date'] =  $info['chocks_on_date'];
				$temp['chocks_on_time'] =  $data['chocks_on_time'];
				$temp['chocks_off_date'] =  $info['chocks_off_date'];
				$temp['chocks_off_time'] =  $data['chocks_off_time'];
				$temp['block_time'] =  $data['block_time'];
				$temp['total_no_of_landings'] =  $data['total_no_of_landings'];
				$temp['common'] =   $data['common'];
				$this->custom_db->insert_record('fdtl_details_temp', $temp);
			}


			$query1 = "select component from aircrafts where origin =(SELECT aircraft from flight_crs_segment_details where fsid = " . $data['flight_number'] . ")";
			$data1 =  $this->custom_db->get_custom_query($query1);
			if ($data1['status']) {
				$component_list = json_decode($data1['data'][0]['component']);
			}
			foreach ($component_list as $k => $c_l) {
				$time = 0;
				$t = 0;
				$query2 = "select * from flight_crs_components where origin =" . $c_l->serialcomponent;
				$data2 =  $this->custom_db->get_custom_query($query2);

				//time since new
				$t = $this->h2m($data2['data'][0]['induction_time_temp']);
				$ftime = $c_l->fitted_time . ":00";
				$query3 = "SELECT * FROM `jlb_details` WHERE flight_number =" . $data['flight_number'] . " and jlb_date >= '" . date('Y-m-d', strtotime($c_l->fitted_date)) . "' and chocks_off_time >= '" . $ftime . "'";
				$data3 =  $this->custom_db->get_custom_query($query3);
				if ($data3['status']) {
					foreach ($data3['data'] as $d) {
						$time += $this->h2m($d['block_time']);
					}
				}
				$totaltime_induction_time = $t + $time;
				$totaltime_induction_time = $this->m2h($totaltime_induction_time);
				$query3 = "update flight_crs_components set  total_time = '" . $totaltime_induction_time . "'  where origin =" . $c_l->serialcomponent;
				$this->db->query($query3);
				//debug('--------------------------------------------<br>');

				$b_time = 0;
				//Time Since Overhaul && Overhaul Due (in Hrs)
				$query4 = "SELECT * FROM `jlb_details` WHERE flight_number =" . $data['flight_number'] . " and jlb_date >= '" . date('Y-m-d', strtotime($data2['data'][0]['last_date_overhaul'])) . "'";
				$data4 =  $this->custom_db->get_custom_query($query4);
				if ($data4['status']) {
					foreach ($data4['data'] as $d) {
						$b_time += $this->h2m($d['block_time']);
					}
				}

				$overhaul_cycle = $this->h2m($data2['data'][0]['overhaul_cycle']);
				$overhaul_due =  $this->m2h($overhaul_cycle - $b_time);
				$b_time = $this->m2h($b_time);

				$query5 = "update flight_crs_components set time_since_overhaul_hours = '" . $b_time . "' , overhaul_due_hours='" . $overhaul_due . "' where origin =" . $c_l->serialcomponent;
				$this->db->query($query5);
				//debug('--------------------------------------------<br>');
				//Next Maintenance Due (in Hrs)
				$b_time = 0;
				$query6 = "SELECT * FROM `jlb_details` WHERE flight_number =" . $data['flight_number'] . " and jlb_date >= '" . date('Y-m-d', strtotime($data2['data'][0]['last_maintenance_date'])) . "'";
				$data6 =  $this->custom_db->get_custom_query($query6);
				//debug($data6);
				if ($data6['status']) {
					foreach ($data6['data'] as $d) {
						$b_time += $this->h2m($d['block_time']);
					}
				}
				//debug($b_time);
				$maintenance_cycle = $this->h2m($data2['data'][0]['maintenance_cycle']);
				//debug($maintenance_cycle);
				//echo '<br>';
				$y = $maintenance_cycle - $b_time;
				$next_maintenance_due_hours =  $this->m2h($y);


				$query7 = "update flight_crs_components set next_maintenance_due_hours = '" . $next_maintenance_due_hours . "' where origin =" . $c_l->serialcomponent;
				$this->db->query($query7);
				//debug('--------------------------------------------<br>');

			}
			//exit;
			$org = $this->input->get('origin');
			$reg = $this->input->get('reg');
			$d = $this->input->get('date');
			redirect(base_url() . 'index.php/flight/add_jlb_details?origin=' . $org . '&reg=' . $reg . '&date=' . $d);
		}


		$j_l = $this->input->get('origin');
		$query = "select * from jlb_details where journey_log = '$j_l' ORDER BY origin DESC";
		$data =  $this->custom_db->get_custom_query($query);
		if ($data['status']) {
			$page_data['jlb_details'] = $data['data'];
			foreach ($data['data'] as $key => $tt) {
				$cond['origin'] = $tt['pilot_in_command'];
				$pildata =  $this->custom_db->single_table_records('pilot', '*', $cond);
				$page_data['jlb_details'][$key]['pilot_name'] = $pildata['data'][0]['first_name'];
				$cond['origin'] = $tt['co_pilot'];
				$pildata =  $this->custom_db->single_table_records('pilot', '*', $cond);
				$page_data['jlb_details'][$key]['co_pilot_name'] = $pildata['data'][0]['first_name'];
				$condi['fsid'] = $tt['flight_number'];
				$flight =  $this->custom_db->single_table_records('flight_crs_segment_details', '*', $condi);
				$page_data['jlb_details'][$key]['flight_number'] = $flight['data'][0]['flight_num'];
				$page_data['jlb_details'][$key]['flight_no'] = $flight['data'][0]['fsid'];


				$fdtlcond['jlb_number'] = $tt['origin'];
				$fdtldata =  $this->custom_db->single_table_records('fdtl_details_temp', '*', $fdtlcond);
				$page_data['jlb_details'][$key]['fdtl_temp'] = $fdtldata['data'][0]['origin'];
				$page_data['jlb_details'][$key]['pilot_update_status'] = $fdtldata['data'][0]['pilot_update_status'];
				$page_data['jlb_details'][$key]['co_pilot_update_status'] = $fdtldata['data'][0]['co_pilot_update_status'];
			}
		}


		/********************************performance**********************/
		$j_l = $this->input->get('origin');

		$aircrafts_performance_log =  $this->custom_db->single_table_records('aircrafts_performance_log', '*', array('journey_log' => $j_l));

		//echo $this->db->last_query();exit;
		if ($aircrafts_performance_log['status']) {

			foreach ($aircrafts_performance_log['data'] as $key => $tt) {
				$cond['origin'] = $tt['jlb_date_flightno'];
				$jlb_date =  $this->custom_db->single_table_records('jlb_details', '*', $cond);
				$aircrafts_performance_log['data'][$key]['jlb_date'] = $jlb_date['data'][0]['jlb_date'];
				$condd['fsid'] = $jlb_date['data'][0]['flight_number'];
				$flight =  $this->custom_db->single_table_records('flight_crs_details', '*', $condd);
				$aircrafts_performance_log['data'][$key]['flight_number'] = $flight['data'][0]['flight_num'];
			}

			$page_data['aircrafts_performance_log'] = $aircrafts_performance_log['data'];
		}
		/********************************performance**********************/
		/********************************airperformance*******************/
		$j_l = $this->input->get('origin');

		$mel_details =  $this->custom_db->single_table_records('mel_details', '*', array('journey_log' => $j_l));
		if ($mel_details['status']) {
			$page_data['mel_details'] = $mel_details['data'];

			foreach ($mel_details['data'] as $key => $tt) {
				$cond['origin'] = $tt['mel_raised'];
				$pildata =  $this->custom_db->single_table_records('pilot', '*', $cond);
				$page_data['mel_details'][$key]['pilot_name'] = $pildata['data'][0]['first_name'];

				$condition['origin'] = $tt['jlb_date_flightno'];
				$jlb_date =  $this->custom_db->single_table_records('jlb_details', '*', $condition);
				$page_data['mel_details'][$key]['jlb_date'] = $jlb_date['data'][0]['jlb_date'];

				$condition2['fsid'] = $jlb_date['data'][0]['flight_number'];
				$flight =  $this->custom_db->single_table_records('flight_crs_details', '*', $condition2);

				$page_data['mel_details'][$key]['flight_number'] = $flight['data'][0]['flight_num'];
			}
		}
		/*******************************airperformance**************************/

		//debug($page_data);exit;
		$this->template->view('flight/add_jlb_details', $page_data);
	}

	function flight_time_duty(): void
	{
		$page_data = [];
		$data = $this->input->post();
		if (!empty($data)) {
			//debug($data);
			if (($data['time_origin'] != 0) || ($data['duty_origin'] != 0)) {
				if (isset($data['time_origin'])) {
					$data['origin'] = $data['time_origin'];
					unset($data['time_origin']);
				}
				if (isset($data['duty_origin'])) {
					$data['origin'] = $data['duty_origin'];
					unset($data['duty_origin']);
				}
				$this->custom_db->update_record('flight_timelimit_dutyperiod', $data, array('origin' => $data['origin']));
				//echo $this->db->last_query();
				//exit;
			} else {
				if (isset($data['time_origin'])) {
					unset($data['time_origin']);
				}
				if (isset($data['duty_origin'])) {
					unset($data['duty_origin']);
				}
				$this->custom_db->insert_record('flight_timelimit_dutyperiod', $data);
			}
		}
		/*$query = "SELECT 
                        t.common as common ,t.origin as time_origin,t.flight as time ,
                        t.limit_limit as flight_time_limit,t.limit_week as flight_time_limit_week,
                        t.limit_month as flight_time_limit_month,t.limit_year as flight_time_limit_year,
                        t.caution_limit as flight_time_caution,t.caution_week as flight_time_caution_week,
                        t.caution_month as flight_time_caution_month,t.caution_month as flight_time_caution_month,
                        t.caution_year as flight_time_caution_year,
                        d.origin as duty_origin,d.flight as duty ,
                        d.limit_limit as flight_duty_limit,d.limit_week as flight_duty_limit_week,
                        d.limit_month as flight_duty_limit_month,d.limit_year as flight_duty_limit_year, 
                        d.caution_limit as flight_duty_caution,d.caution_week as flight_duty_caution_week,
                        d.caution_month as flight_duty_caution_month,d.caution_year as flight_duty_caution_year 
                        FROM `flight_timelimit_dutyperiod` t left join `flight_timelimit_dutyperiod` d on t.common = d.common where t.origin != d. origin group by t.common";
	                	$result= $this->db->query($query)->result_array();
        				$page_data['flight_timeduty'] = $result;*/



		$flight_time_limit = $this->custom_db->single_table_records('flight_timelimit_dutyperiod', '*', array('flight' => 1));
		if ($flight_time_limit['status']) {
			$page_data['flight_time_limit'] = $flight_time_limit['data'];
		}

		$flight_duty_period = $this->custom_db->single_table_records('flight_timelimit_dutyperiod', '*', array('flight' => 2));
		if ($flight_duty_period['status']) {
			$page_data['flight_duty_period'] = $flight_duty_period['data'];
		}
		$flight_single_time_limit = $this->custom_db->single_table_records('flight_timelimit_dutyperiod', '*', array('flight' => 3));
		if ($flight_single_time_limit['status']) {
			$page_data['single_flight_time_limit'] = $flight_single_time_limit['data'];
		}

		$flight_single_duty_period = $this->custom_db->single_table_records('flight_timelimit_dutyperiod', '*', array('flight' => 4));
		if ($flight_single_duty_period['status']) {
			$page_data['single_flight_duty_period'] = $flight_single_duty_period['data'];
		}

		$single_pilot_landings = $this->custom_db->single_table_records('flight_timelimit_dutyperiod', '*', array('flight' => 6));
		if ($single_pilot_landings['status']) {
			$page_data['single_pilot_landings'] = $single_pilot_landings['data'];
		}
		$dual_pilot_landings = $this->custom_db->single_table_records('flight_timelimit_dutyperiod', '*', array('flight' => 5));
		if ($dual_pilot_landings['status']) {
			$page_data['dual_pilot_landings'] = $dual_pilot_landings['data'];
		}

		//debug($page_data);exit;

		$this->template->view('flight/flight_time_duty', $page_data);
	}

	function add_sid(): void
	{
		$data = $this->input->post();
		$page_data = [];
		if (!empty($data)) {
			if ($data['origin'] != 0) {

				$data['doneat'] = date("Y-m-d", strtotime($data['doneat']));
				$data['dueat'] = date("Y-m-d", strtotime($data['dueat']));
				$this->custom_db->update_record('structural_inspection_programme', $data, array('origin' => $data['origin']));
			} else {
				unset($data['origin']);
				$data['dueat'] = date("Y-m-d", strtotime($data['dueat']));
				$data['doneat'] = date("Y-m-d", strtotime($data['doneat']));
				$data['created_by'] = $GLOBALS['CI']->entity_user_id;
				$this->custom_db->insert_record('structural_inspection_programme', $data);
			}

			redirect('flight/add_sid');
		}
		$data =  $this->custom_db->single_table_records('structural_inspection_programme', '*');
		if ($data['status']) {
			$page_data['sid'] = $data['data'];
		}
		$this->template->view('flight/add_sid', $page_data);
	}
	function home_base(): void
	{
		$page_data = [];
		$data = $this->input->post();
		$base = [];
		//debug($data);exit;
		if (!empty($data)) {

			if ($data['origin'] != 0) {
				$base['airport_name'] = $data['airport_name'];
				$base['base'] = $data['base'];
				$this->custom_db->update_record('flight_airport_list', $base, array('id' => $data['origin']));
				//echo $this->db->last_query();exit;
			}
		}
		$data =  $this->custom_db->single_table_records('flight_airport_list', '*', array());
		if ($data['status']) {
			$page_data['sid'] = $data['data'];
		}
		$this->template->view('flight/home_base', $page_data);
	}
	function api_city_list(): void
	{
		$data = $this->input->post();
		$page_data = [];
		//	debug($data);
		if (!empty($data)) {

			if ($data['origin'] != 0) {
				$base = $data;
				//debug($base);exit;


				$this->custom_db->update_record('flight_airport_list', $base, array('id' => $data['origin']));
				// echo $this->db->last_query();exit;
			} else {
				unset($data['origin']);
				$data['country'] = 'India';
				$this->custom_db->insert_record('flight_airport_list', $data);
			}
		}
		$data =  $this->custom_db->single_table_records('flight_airport_list', '*', array('country' => 'India'));
		if ($data['status']) {
			$page_data['sid'] = $data['data'];
		}

		//debug($page_data);
		$this->template->view('flight/flight_city_list', $page_data);
	}

	function view_enquiry(): void
	{
		$data = [];
		$data['enquiries'] = $this->flight_model->enquiries();
		//debug($data);exit;
		$this->template->view('flight/flight_enquiry', $data);
	}
	function add_component(): void
	{
		$page_data = array();
		$cond = [];
		$data = $this->input->post();
		if (!empty($data)) {
			if ($data['origin'] != 0) {
				$data['induction_date'] = date("Y-m-d", strtotime($data['induction_date']));
				$data['induction_time_temp'] = $data['induction_time'];
				$data['last_date_overhaul'] = date("Y-m-d", strtotime($data['last_date_overhaul']));
				$data['last_maintenance_date'] = date("Y-m-d", strtotime($data['last_maintenance_date']));
				$data['next_maintenance_date'] = date("Y-m-d", strtotime($data['next_maintenance_date']));
				$data['manufacturing_date'] = date("Y-m-d", strtotime($data['manufacturing_date']));
				$data['warranty_start'] = date("Y-m-d", strtotime($data['warranty_start']));
				$data['warranty_expire'] = date("Y-m-d", strtotime($data['warranty_expire']));
				$data['tbo_date'] = date("Y-m-d", strtotime($data['tbo_date']));
				$data['shelf_life_date'] = date("Y-m-d", strtotime($data['shelf_life_date']));
				$this->custom_db->update_record('flight_crs_components', $data, array('origin' => $data['origin']));
			} else {
				unset($data['origin']);
				$data['induction_date'] = date("Y-m-d", strtotime($data['induction_date']));
				$data['induction_time_temp'] = $data['induction_time'];
				$data['manufacturing_date'] = date("Y-m-d", strtotime($data['manufacturing_date']));
				$data['last_date_overhaul'] = date("Y-m-d", strtotime($data['last_date_overhaul']));
				$data['last_maintenance_date'] = date("Y-m-d", strtotime($data['last_maintenance_date']));
				$data['next_maintenance_date'] = date("Y-m-d", strtotime($data['next_maintenance_date']));
				$data['warranty_start'] = date("Y-m-d", strtotime($data['warranty_start']));
				$data['warranty_expire'] = date("Y-m-d", strtotime($data['warranty_expire']));
				$data['tbo_date'] = date("Y-m-d", strtotime($data['tbo_date']));
				$data['shelf_life_date'] = date("Y-m-d", strtotime($data['shelf_life_date']));
				$this->custom_db->insert_record('flight_crs_components', $data);
			}
			redirect(base_url() . 'index.php/flight/component_list');
		}
		$get_data = $this->uri->segment(3);
		if ($get_data) {
			$cond['origin'] = $get_data;
			$data =  $this->custom_db->single_table_records('flight_crs_components', '*', $cond);
			if ($data['status']) {
				$page_data['component'] = $data['data'][0];
				//$manufacturing_date = $this->diff_date($data['data'][0]['manufacturing_date'], '00:00');
				$shelf_life_due = $this->diff_datetime($data['data'][0]['shelf_life_date'], '00:00');
				$next_maintenance_datetime = $this->diff_datetime($data['data'][0]['next_maintenance_date'], '00:00');
				//$time_since_overhaul = $this->diff_datetime($data['data'][0]['last_date_overhaul'], '00:00');
				$overhaul_due_days = $this->diff_datetime($data['data'][0]['tbo_date'], '00:00');
				$page_data['component']['last_date_overhaul'] = $this->dd($data['data'][0]['last_date_overhaul']);
				$page_data['component']['last_maintenance_date'] = $this->dd($data['data'][0]['last_maintenance_date']);
				$page_data['component']['next_maintenance_date'] = $this->dd($data['data'][0]['next_maintenance_date']);
				$page_data['component']['manufacturing_date'] = $this->dd($data['data'][0]['manufacturing_date']);
				$page_data['component']['tbo_date'] = $this->dd($data['data'][0]['tbo_date']);
				$page_data['component']['shelf_life_date'] = $this->dd($data['data'][0]['shelf_life_date']);
				$page_data['component']['warranty_start'] = $this->dd($data['data'][0]['warranty_start']);
				$page_data['component']['warranty_expire'] = $this->dd($data['data'][0]['warranty_expire']);
				$page_data['component']['induction_time'] = $data['data'][0]['induction_time'];
				$page_data['component']['tbo_time'] = $data['data'][0]['tbo_time'];
				$page_data['component']['shelf_datetime_time'] = $data['data'][0]['shelf_datetime_time'];
				$page_data['component']['shelf_life_due'] = $shelf_life_due;
				$page_data['component']['maintenance_due_fulldatetime'] = $next_maintenance_datetime;
				$page_data['component']['overhaul_due_days'] = $overhaul_due_days;
			} else {
				$page_data['component'] = array();
			}
			//debug($page_data['component']);exit;
		}
		$data =  $this->custom_db->single_table_records('flight_crs_component_type_list');
		if ($data['status']) {
			$page_data['comp_type'] = $data['data'];
		}
		$sdata =  $this->custom_db->single_table_records('aircrafts', 'origin,reg');
		if ($sdata['status']) {
			$page_data['serialno'] = $sdata['data'];
		}
		$this->template->view('flight/add_component', $page_data);
	}
	function component_list(): void
	{
		$page_data = [];
		$query = "SELECT *,fc.component_type as type ,c.origin as org  FROM flight_crs_components c LEFT join flight_crs_component_type_list fc on c.component_type = fc.origin";
		$page_data['component_list'] = $this->db->query($query)->result_array();
		$cl = $page_data['component_list'];

		foreach ($cl as $key => $maintain) {

			$manufacturing_date = $this->diff_date($maintain['manufacturing_date'], '00:00');
			$shelf_life_due = $this->diff_datetime($maintain['shelf_life_date'], '00:00');
			$next_maintenance_datetime = $this->diff_datetime($maintain['next_maintenance_date'], '00:00');
			//$time_since_overhaul = $this->diff_datetime($maintain['last_date_overhaul'], '00:00');
			$overhaul_due_days = $this->diff_datetime($maintain['tbo_date'], '00:00');



			$page_data['component_list'][$key]['manufacturing_date'] = $manufacturing_date;
			$page_data['component_list'][$key]['shelf_life_due'] = $shelf_life_due;
			$page_data['component_list'][$key]['maintenance_due_fulldatetime'] = $next_maintenance_datetime;
			$page_data['component_list'][$key]['overhaul_due_days'] = $overhaul_due_days;


			$query = "SELECT * FROM flight_crs_component_maintenance_log where component =  " . $maintain['org'] . "  ORDER BY origin DESC  LIMIT 1 ";
			$maintenance = $this->db->query($query)->result_array();

			$page_data['component_list'][$key]['next_maintenance_date'] = '';

			$nmd = ($maintenance[0]['next_maintenance_date']) ? $maintenance[0]['next_maintenance_date'] : '';
			if ($nmd) {
				$next_maintenance_date = date('M j, Y', strtotime($maintenance[0]['next_maintenance_date']));
				$diff1 = date_diff(date_create($next_maintenance_date), date_create(date("M j, Y")));
				$next_maintenance = $diff1->days;
				$page_data['component_list'][$key]['next_maintenance_day'] = $next_maintenance;
				$page_data['component_list'][$key]['next_maintenance_date'] = $maintenance[0]['next_maintenance_date'];
			}
		}

		//debug($page_data['component_list']);exit;

		$this->template->view('flight/component_list', $page_data);
	}
	function dd(string $date): string
	{
		$dt = explode('-', $date);
		$y = $dt[0];
		$m = $dt[1];
		$d = $dt[2];
		$date = $d . '-' . $m . '-' . $y;
		return $date;
	}
	function diff_datetime(string $date1, string $time1): string
	{
		$d1 = $date1 . ' ' . $time1 . ':00';
		$datetime1 = new DateTime($d1);
		$datetime2 = new DateTime();
		$interval = $datetime1->diff($datetime2);
		//$elapsed1 = $interval->format('%y years %m months %d days %h : %i ');
		$elapsed1 = $interval->format('%y years %m months %d days');
		return $elapsed1;
	}

	function diff_datetime1(string $date1, string $time1): string
	{
		$d1 = $date1 . ' ' . $time1 . ':00';
		$datetime1 = new DateTime($d1);
		$datetime2 = new DateTime();

		if ($datetime2 < $datetime1) {
			$interval = $datetime1->diff($datetime2);
			//$elapsed1 = $interval->format('%y years %m months %d days %h : %i ');
			$elapsed1 = $interval->format('%y years %m months %d days');
			return $elapsed1;
		} else {
			return '0';
		}
	}


	function diff_date(string $date1, string $time1): string
	{
		$d1 = $date1 . ' ' . $time1 . ':00';
		$datetime1 = new DateTime($d1);
		$datetime2 = new DateTime();
		$interval = $datetime1->diff($datetime2);
		$elapsed1 = $interval->format('%y years %m months %d days');
		//$elapsed = $interval->format('%a Days %h Hours %i Minutes');
		return $elapsed1;
	}
	function export_component_details(string $op = ''): void
	{
		$page_data = [];
		$query = "SELECT *,fc.component_type as type ,c.origin as org  FROM flight_crs_components c LEFT join flight_crs_component_type_list fc on c.component_type = fc.origin";
		$page_data['component_list'] = $this->db->query($query)->result_array();
		$cl = $page_data['component_list'];

		foreach ($cl as $key => $maintain) {

			$manufacturing_date = $this->diff_date($maintain['manufacturing_date'], '00:00');
			$shelf_life_due = $this->diff_datetime1($maintain['shelf_life_date'], '00:00');
			$next_maintenance_datetime = $this->diff_datetime1($maintain['next_maintenance_date'], '00:00');
			//$time_since_overhaul = $this->diff_datetime1($maintain['last_date_overhaul'], '00:00');
			$overhaul_due_days = $this->diff_datetime1($maintain['tbo_date'], '00:00');

			$page_data['component_list'][$key]['manufacturing_date'] = $manufacturing_date;



			if ($shelf_life_due) {
				$page_data['component_list'][$key]['shelf_life_due'] = $shelf_life_due;
			} else {
				$page_data['component_list'][$key]['shelf_life_due'] = 'Due day has passed';
			}

			if ($next_maintenance_datetime) {
				$page_data['component_list'][$key]['maintenance_due_fulldatetime'] = $next_maintenance_datetime;
			} else {
				$page_data['component_list'][$key]['maintenance_due_fulldatetime'] = 'Due day has passed';
			}

			if ($overhaul_due_days) {
				$page_data['component_list'][$key]['overhaul_due_days'] = $overhaul_due_days;
			} else {
				$page_data['component_list'][$key]['overhaul_due_days'] = 'Due day has passed';
			}

			//debug($maintain['next_maintenance_due_hours']);

			if ($maintain['next_maintenance_due_hours'] < 0) {
				$page_data['component_list'][$key]['next_maintenance_due_hours'] = 'Due hours has passed';
			}

			if ($maintain['overhaul_due_hours'] < 0) {
				$page_data['component_list'][$key]['overhaul_due_hours'] = 'Due hours has passed';
			}

			/*	
				 $query = "SELECT * FROM flight_crs_component_maintenance_log where component =  ".$maintain['org']."  ORDER BY origin DESC  LIMIT 1 ";
				$maintenance = $this->db->query($query)->result_array();
				
				$page_data['component_list'][$key]['next_maintenance_date'] = '';
				
				$nmd = ($maintenance[0]['next_maintenance_date']) ? $maintenance[0]['next_maintenance_date'] : '';
				if($nmd){
					$next_maintenance_date = date('M j, Y', strtotime( $maintenance[0]['next_maintenance_date']));
					$diff1 = date_diff(date_create($next_maintenance_date),date_create(date("M j, Y")));
					$next_maintenance = $diff1->days;
					$page_data['component_list'][$key]['next_maintenance_day'] = $next_maintenance;
					
					
					$page_data['component_list'][$key]['next_maintenance_date1'] = $maintenance[0]['next_maintenance_date'];
				}	*/
		}
		//	debug($page_data['component_list']);exit;
		$export_data = $page_data['component_list'];
		if ($op == 'excel') {

			$headings = array(
				'a1' => 'Sl. No.',
				'b1' => 'Component name',
				'c1' => 'Component type',
				'd1' => 'Part number',
				'e1' => 'Serial number',
				'f1' => 'Induction date',
				'g1' => 'Induction time',
				'h1' => 'Manufacturing date',
				'i1' => 'Shelf life date',
				'j1' => 'Warranty start',
				'k1' => 'Warranty expire',
				'l1' => 'Last date overhaul',
				'm1' => 'Time of Overhaul',
				'n1' => 'Overhaul due date',
				'o1' => 'Overhaul Cycle (Hours Cycle)',
				'p1' => 'Last Maintenance date',
				'q1' => 'Maintenance Cycle (Hours Cycle)',
				'r1' => 'Next Maintenance date',
				's1' => 'Set Caution Limit (In Days)',
				't1' => 'Set Caution Limit Hours (In Hours)',


				'u1' => 'Time Since New (In Days)',
				'v1' => 'Time Since New (In Hours)',

				'w1' => 'Shelf Life Due (In Days)',

				'x1' => 'Time Since Overhaul (In Days)',
				'y1' => 'Time Since Overhaul (In Hours)',


				'z1' => 'Overhaul due (In Days)',
				'AA1' => 'Overhaul due (In Hours)',
				'AB1' => 'Next Maintenance due (In Days)',
				'AC1' => 'Next Maintenance due (In Hours)'

			);
			$fields = array(
				'a' => '',
				'b' => 'component_name',
				'c' => 'component_type',
				'd' => 'part_number',
				'e' => 'serial_number',
				'f' => 'induction_date',
				'g' => 'induction_time',
				'h' => 'manufacturing_date',
				'i' => 'shelf_life_date',
				'j' => 'warranty_start',
				'k' => 'warranty_expire',
				'l' => 'last_date_overhaul',
				'm' => 'time_of_overhaul',
				'n' => 'tbo_date',
				'o' => 'overhaul_cycle',
				'p' => 'last_maintenance_date',
				'q' => 'maintenance_cycle',
				'r' => 'next_maintenance_date',
				's' => 'set_caution_limit_date',
				't' => 'set_caution_limit_time',

				'u' => 'time_since_new',
				'v' => 'total_time',
				'w' => 'shelf_life_due',
				'x' => 'time_since_overhaul',
				'y' => 'time_since_overhaul_hours',
				'z' => 'overhaul_due_days',
				'AA' => 'overhaul_due_hours',
				'AB' => 'next_maintenance_due_days',
				'AC' => 'next_maintenance_due_hours',



			);
			$excel_sheet_properties = array(
				'title' => 'Component List ' . date('d-M-Y'),
				'creator' => 'Provab',
				'description' => 'Component List',
				'sheet_title' => 'Component List'
			);
			$this->load->library('provab_excel');
			$this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
		}
	}

	function component_type_list(): void
	{
		$page_data = [];
		//$result = [];
		$data =  $this->custom_db->single_table_records('flight_crs_component_type_list');
		if ($data['status']) {
			$page_data['component_type_list'] = $data['data'];
		} else {
			$page_data['component_type_list'] = array();
		}
		$this->template->view('flight/flight_crs_component_type_list', $page_data);
	}
	function adding_fare_management(array $pilot, array $flight_details): array
	{
		$result = [];
		//Adding Fare Management
		$season_fare = $this->flight_model->check_date_in_season($pilot['avail_date']);
		$week_fare = $this->flight_model->check_date_in_week($pilot['avail_date']);
		$time_booking_before_fare = $this->flight_model->check_time_before_booking($pilot['avail_date']);
		$holiday_fare = $this->flight_model->check_date_in_holiday($pilot['avail_date']);

		$total_adult_book_price = 0;
		$total_child_book_price = 0;
		$total_infant_book_price = 0;
		//Adult
		if (isset($pilot['adult_base'])) {
			$total_adult_book_price += $pilot['adult_base'];
		} else {
			$total_adult_book_price += $flight_details[0]['adult_basefare'];
		}
		if (isset($pilot['adult_tax'])) {
			$total_adult_book_price += $pilot['adult_tax'];
		} else {
			$total_adult_book_price += $flight_details[0]['adult_tax'];
		}
		//Child
		if (isset($pilot['child_base'])) {
			$total_child_book_price += $pilot['child_base'];
		} else {
			$total_child_book_price += $flight_details[0]['child_basefare'];
		}
		if (isset($pilot['child_tax'])) {
			$total_child_book_price += $pilot['child_tax'];
		} else {
			$total_child_book_price += $flight_details[0]['child_tax'];
		}
		//Infant
		if (isset($pilot['infant_base'])) {
			$total_infant_book_price += $pilot['infant_base'];
		} else {
			$total_infant_book_price += $flight_details[0]['infant_basefare'];
		}
		if (isset($pilot['infant_tax'])) {
			$total_infant_book_price += $pilot['infant_tax'];
		} else {
			$total_infant_book_price += $flight_details[0]['infant_tax'];
		}
		if (valid_array($season_fare)) {
			if ($season_fare[0]['percentage'] != "100%") {
				$percentage = str_replace("%", "", $season_fare[0]['percentage']);
				$percentage = floatval($percentage) - 100;

				$total_percenatge = $total_adult_book_price * $percentage / 100;
				$total_adult_book_price += $total_percenatge;
				$total_child_percenatge = $total_child_book_price * $percentage / 100;
				$total_child_book_price += $total_child_percenatge;
				$total_infant_percenatge = $total_infant_book_price * $percentage / 100;
				$total_infant_book_price += $total_infant_percenatge;
			}
		}

		if (valid_array($week_fare)) {
			if ($week_fare[0]['percentage'] != "100%") {
				$percentage = str_replace("%", "", $week_fare[0]['percentage']);
				$percentage = $percentage - 100;

				$total_percenatge = $total_adult_book_price * $percentage / 100;
				$total_adult_book_price += $total_percenatge;
				$total_child_percenatge = $total_child_book_price * $percentage / 100;
				$total_child_book_price += $total_child_percenatge;
				$total_infant_percenatge = $total_infant_book_price * $percentage / 100;
				$total_infant_book_price += $total_infant_percenatge;
			}
		}

		if (valid_array($holiday_fare)) {
			if ($holiday_fare[0]['percentage'] != "100%") {
				$percentage = str_replace("%", "", $holiday_fare[0]['percentage']);
				$percentage = $percentage - 100;

				$total_percenatge = $total_adult_book_price * $percentage / 100;
				$total_adult_book_price += $total_percenatge;
				$total_child_percenatge = $total_child_book_price * $percentage / 100;
				$total_child_book_price += $total_child_percenatge;
				$total_infant_percenatge = $total_infant_book_price * $percentage / 100;
				$total_infant_book_price += $total_infant_percenatge;
			}
		}
		if (valid_array($time_booking_before_fare)) {
			if ($time_booking_before_fare[0]['percentage'] != "100%") {
				$percentage = str_replace("%", "", $time_booking_before_fare[0]['percentage']);
				$percentage = $percentage - 100;
				//echo $percentage."ccc";
				$total_percenatge = $total_adult_book_price * $percentage / 100;
				$total_adult_book_price += $total_percenatge;
				$total_child_percenatge = $total_child_book_price * $percentage / 100;
				$total_child_book_price += $total_child_percenatge;
				$total_infant_percenatge = $total_infant_book_price * $percentage / 100;
				$total_infant_book_price += $total_infant_percenatge;
			}
		}
		$result['total_adult_book_price'] = $total_adult_book_price;
		$result['total_child_book_price'] = $total_child_book_price;
		$result['total_infant_book_price'] = $total_infant_book_price;
		return $result;
	}
	function get_flight_suggestions(): void
	{
		ini_set('memory_limit', '-1');
		$apts = [];
		$term = $this->input->get('term'); //retrieve the search term that autocomplete sends
		$term = trim(strip_tags($term));
		$flights = $this->flight_model->get_airport_list($term)->result_array();
		// debug($flights);exit;
		$result = array();
		foreach ($flights as $val) {
			$apts['label'] = $val['airport_name'] . ' - ' . $val['airport_city'] . ' - ' . $val['country'] . ' (' . $val['airport_code'] . ')';
			$apts['value'] = $val['airport_name'] . ' (' . $val['airport_code'] . ')';
			$apts['id'] = $val['airport_code'];
			$result[] = $apts;
		}
		echo json_encode($result);
	}
	//create a dummy function here




}
