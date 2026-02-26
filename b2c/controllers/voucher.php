<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use App\Libraries\Provab_Pdf;
/**
 *
 * @package    Provab
 * @subpackage Bus
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */

class Voucher extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//$this->load->library("provab_pdf");
		$this->load->library('provab_mailer');
		$this->load->library('booking_data_formatter');
		$this->load->model('flight_model');
		$this->load->model('hotel_model');
		$this->load->model('car_model');
		//we need to activate bus api which are active for current domain and load those libraries
		//$this->output->enable_profiler(TRUE);
	}

	
	function hotel($app_reference, $booking_source='', $booking_status='', $operation='show_voucher'): void
	{
		$this->load->model('hotel_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source, $booking_status);
			// debug($booking_details);die;
			$page_data = [];
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions','description', array('module' =>'hotel'));
			if ($booking_details['status'] == SUCCESS_STATUS) {
				//Assemble Booking Data
				$assembled_details = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2c');
				 // /debug($assembled_booking_details);die;
				$page_data['data'] = $assembled_details['data'];
                if(isset($assembled_details['data']['booking_details'][0])){
					//get agent address & logo for b2b voucher
				
					$domain_address = $this->custom_db->single_table_records ('domain_list','address,domain_logo,phone,domain_name,phone_code,email',array('origin'=>get_domain_auth_id()));
					//debug($domain_address);exit;
					$hotel_partners = $this->custom_db->single_table_records ('hotel_partners','*',array('status'=> 1));
					$h_partners = array();
					//debug($hotel_partners);exit;
					if($hotel_partners['status'] == SUCCESS_STATUS){
						foreach($hotel_partners['data'] as $partner){
							$h_partners[] = $this->template->domain_hotel_partner_images ($partner['partner_image']);
						}
					}
				
					$page_data['data']['address'] =$domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['phone_code'] = $domain_address['data'][0]['phone_code'];
					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];
					$page_data['data']['email'] = $domain_address['data'][0]['email'];
					$page_data['data']['hotel_partners'] = $h_partners;
					$page_data['data']['terms_conditions'] = '';
					if($terms_conditions['status'] == SUCCESS_STATUS){
						$page_data['data']['terms_conditions'] = $terms_conditions['data'][0]['description'];
					}

					
				}

				switch ($operation) {
					case 'show_voucher' : $this->template->view('voucher/hotel_voucher', $page_data);
					break;
					case 'show_pdf' :
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view=$this->template->isolated_view('voucher/hotel_pdf', $page_data);
						$create_pdf->create_pdf($get_view,'show');
						break;
				}
			}
		}
	}

	/**
	 *
	 */
	function flight($app_reference, $booking_source='', $booking_status='', $operation='show_voucher',$email=''):void
	{
		
		$this->load->model('flight_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source, $booking_status);
                        
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions','description', array('module' =>'flight'));
			 //debug($booking_details);exit;
			$page_data = [];
			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_flight_lib(PROVAB_FLIGHT_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled__details = $this->booking_data_formatter->format_flight_booking_data($booking_details, 'b2c');	
				$page_data['data'] = $assembled__details['data'];
				 if(isset($assembled__details['data']['booking_details'][0])){
					//get agent address & logo for b2b voucher
					
					$domain_address = $this->custom_db->single_table_records ( 'domain_list','address,domain_logo,phone,domain_name,phone_code',array('origin'=>get_domain_auth_id()));
					// debug($domain_address);exit;
					$page_data['data']['address'] =$domain_address['data'][0]['address'];
					$page_data['data']['phone'] =$domain_address['data'][0]['phone'];
					$page_data['data']['phone_code'] =$domain_address['data'][0]['phone_code'];
					$page_data['data']['domainname'] =$domain_address['data'][0]['domain_name'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['terms_conditions'] = '';
					if($terms_conditions['status'] == SUCCESS_STATUS){
						$page_data['data']['terms_conditions'] = $terms_conditions['data'][0]['description'];
					}	
			
				}
				// $operation = 'show_pdf';
                                # Get Passenger Email ID
                $email=$booking_details['data']['booking_details'][0]['email'];
                                
				
				switch ($operation) {
					case 'show_voucher' : $this->template->view('voucher/flight_voucher', $page_data);
                                        if(empty($email)==false) {
                                                 $mail_template = $this->template->isolated_view('voucher/flight_voucher', $page_data);
                                                 $this->provab_mailer->send_mail($email, domain_name().' - Flight Ticket',$mail_template);
                                               }
                                        
                                                break;
					case 'email_voucher':
                                            
                                                $this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/flight_pdf', $page_data);
                         
						$pdf = $create_pdf->create_pdf($mail_template,'');
                                                                   
                                                $this->provab_mailer->send_mail($email, domain_name().' - Flight Ticket',$mail_template ,$pdf);
						break;
					case 'show_pdf' :
                                                $this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view=$this->template->isolated_view('voucher/flight_pdf', $page_data);
						$create_pdf->create_pdf($get_view,'show');
					
				}
			}
		}
	}
	/**
	 * Car Voucher
	 */
	function car($app_reference, $booking_source = '', $booking_status = '', $operation = 'show_voucher', $email = ''): void
	{
	    $this->load->model('car_model');
	    if (!empty($app_reference)) {
	        $booking_details = $this->car_model->get_booking_details($app_reference, $booking_source, $booking_status);
	        $page_data = [];

	        if ($booking_details['status'] == SUCCESS_STATUS) {
	            // Assemble Booking Data
	            $assembled_details = $this->booking_data_formatter->format_car_booking_datas($booking_details, 'b2c');
	            $page_data['data'] = $assembled_details['data'];

	            if (isset($assembled_details['data']['booking_details'][0])) {
	                $domain_address = $this->custom_db->single_table_records(
	                    'domain_list',
	                    'address,domain_logo',
	                    ['origin' => get_domain_auth_id()]
	                );
	                $page_data['data']['address'] = $domain_address['data'][0]['address'] ?? '';
	                $page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'] ?? '';
	            }

	            switch ($operation) {
	                case 'show_voucher':
	                    $this->template->view('voucher/car_voucher', $page_data);
	                    break;

	                case 'show_pdf':
	                    $this->load->library('provab_pdf');
	                    $create_pdf = new Provab_Pdf();
	                    $get_view = $this->template->isolated_view('voucher/car_pdf', $page_data);
	                    $create_pdf->create_pdf($get_view, 'show');
	                    break;

	                case 'email_voucher':
	                    $this->load->library('provab_pdf');
	                    $email = $booking_details['data']['booking_details'][0]['email'] ?? '';
	                    $create_pdf = new Provab_Pdf();
	                    $mail_template = $this->template->isolated_view('voucher/car_pdf', $page_data);
	                    $pdf = $create_pdf->create_pdf($mail_template, '');
	                    $this->provab_mailer->send_mail($email, domain_name() . ' - Car Ticket', $mail_template, $pdf);
	                    break;
	            }
	        }
	    }
	}

	/*
		send email ticket 
	*/
	function email_ticket(): void
	{
	    $post_params = $this->input->post();

	    $app_reference = $post_params['app_reference'] ?? '';
	    $booking_source = $post_params['booking_source'] ?? '';
	    $booking_status = $post_params['status'] ?? '';
	    $module = $post_params['module'] ?? '';
	    $page_data = [];

	    if (!empty($app_reference)) {
	        $this->load->library('provab_mailer');
	        $this->load->library('booking_data_formatter');

	        switch ($module) {
	            case 'flight':
	                $booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source, $booking_status);
	                break;
	            case 'hotel':
	                $booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source, $booking_status);
	                break;
	            case 'car':
	                $booking_details = $this->car_model->get_booking_details($app_reference, $booking_source, $booking_status);
	                break;
	            default:
	                echo json_encode(["STATUS" => "false"]);
	                return;
	        }

	        if (($booking_details['status'] ?? '') === SUCCESS_STATUS) {
	            $email = $booking_details['data']['booking_details'][0]['email'] ?? '';

	            if (!empty($email)) {
	                switch ($module) {
	                    case 'flight':
	                        $assembled_details = $this->booking_data_formatter->format_flight_booking_data($booking_details, 'b2c');
	                        $page_data['data'] = $assembled_details['data'];

	                        if (!empty($assembled_details['data']['booking_details'][0])) {
	                            $domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,phone,domain_name', ['origin' => get_domain_auth_id()]);
	                            $domain_info = $domain_address['data'][0] ?? [];

	                            $page_data['data']['address'] = $domain_info['address'] ?? '';
	                            $page_data['data']['phone'] = $domain_info['phone'] ?? '';
	                            $page_data['data']['domainname'] = $domain_info['domain_name'] ?? '';
	                            $page_data['data']['logo'] = $domain_info['domain_logo'] ?? '';
	                        }

	                        $mail_template = $this->template->isolated_view('voucher/flight_voucher', $page_data);
	                        $subject = 'Flight Details';
	                        break;

	                    case 'hotel':
	                        $assembled_details = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2c');
	                        $page_data['data'] = $assembled_details['data'];
	                        $mail_template = $this->template->isolated_view('voucher/hotel_voucher', $page_data);
	                        $subject = 'Hotel Details';
	                        break;

	                    case 'car':
	                        $assembled_details = $this->booking_data_formatter->format_car_booking_datas($booking_details, 'b2c');
	                        $page_data['data'] = $assembled_details['data'];
	                        $mail_template = $this->template->isolated_view('voucher/car_voucher', $page_data);
	                        $subject = 'Car Details';
	                        break;
	                }

	                $this->provab_mailer->send_mail($email, $subject, $mail_template, '');
	                echo json_encode(["STATUS" => "true"]);
	                return;
	            }
	        }
	    }

	    echo json_encode(["STATUS" => "false"]);
	}

	function feed_back_hotel(): void
	{
	    $condition = [['BD.status', '=', $this->db->escape('BOOKING_CONFIRMED')]];
	    $booking_details = $this->hotel_model->get_booking_data($condition, false, 0, 10000000);
	    $table_data = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2c');

	    foreach ($table_data['data']['booking_details'] as $details) {
	        $checkout = strtotime($details['hotel_check_out']);
	        $today_date = strtotime(date("Y-m-d"));

	        if ($today_date > $checkout) {
	            $email = $details['email'] ?? null;

	            if (!empty($email)) {
	                $page_data = [
	                    'first_name' => $details['customer_details'][0]['first_name'] ?? '',
	                    'last_name' => $details['customer_details'][0]['last_name'] ?? '',
	                    'activation_link' => base_url() . 'index.php/hotel/hotel_feedback/' . $details['app_reference']
	                ];

	                $mail_template = $this->template->view('voucher/hotel_feedback', $page_data);
	                $this->provab_mailer->send_mail($email, 'Hotel Feedback ' . $details['hotel_name'], $mail_template);
	            }
	     	}
	 	}
	}

}
