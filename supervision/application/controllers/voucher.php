<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab
 * @subpackage Bus
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */
error_reporting(0);
class Voucher extends CI_Controller
{
	private $current_module;
	public function __construct()
	{
		parent::__construct();
		$this->load->library('booking_data_formatter');
		$this->load->library('provab_mailer');
		$this->current_module = $this->config->item('current_module');
		//$this->load->library('provab_pdf');

		//we need to activate bus api which are active for current domain and load those libraries
		//$this->output->enable_profiler(TRUE);
	}
	public function bus(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		//echo 'under working';exit;
		$this->load->model('bus_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->bus_model->get_booking_details($app_reference, $booking_source, $booking_status);
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions', 'description', array('module' => 'bus'));
			if ($booking_details['status'] == SUCCESS_STATUS) {
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_bus_booking_data($booking_details, $this->current_module);
				$page_data['data'] = $assembled_booking_details['data'];
				// debug($assembled_booking_details);exit;
				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher
					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,phone,domain_name,phone_code', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['phone_code'] = $domain_address['data'][0]['phone_code'];
					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];
					$page_data['data']['terms_conditions'] = '';
					if ($terms_conditions['status'] == SUCCESS_STATUS) {
						$page_data['data']['terms_conditions'] = $terms_conditions['data'][0]['description'];
					}
				}
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/bus_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/bus_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');
						break;

					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/bus_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Bus Ticket', $mail_template, $pdf);
						break;
				}
			} else {
				redirect('security/log_event?event=Invalid AppReference');
			}
		} else {
			redirect('security/log_event?event=Invalid AppReference');
		}
	}
	public function hotel(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		$this->load->model('sightseeing_model');

		if (empty($app_reference) == false) {
			$booking_details = $this->sightseeing_model->get_booking_details($app_reference, $booking_source, $booking_status);
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions', 'description', array('module' => 'activity'));

			if ($booking_details['status'] == SUCCESS_STATUS) {
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_sightseeing_booking_data($booking_details, $this->current_module);

				$page_data['data'] = $assembled_booking_details['data'];
				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher

					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,phone,domain_name', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];
					if ($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0) {
						$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
						if (!empty($get_agent_info)) {
							$page_data['data']['address'] = $get_agent_info[0]['address'];
							$page_data['data']['logo'] = $get_agent_info[0]['logo'];
							$page_data['data']['phone'] = $get_agent_info[0]['phone'];
							$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
						}
					}
					$page_data['data']['terms_conditions'] = '';
					if ($terms_conditions['status'] == SUCCESS_STATUS) {
						$page_data['data']['terms_conditions'] = $terms_conditions['data'][0]['description'];
					}
				}
				//debug($page_data);exit;
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/sightseeing_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/sightseeing_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');

						break;
					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/sightseeing_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Sightseeing Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
	public function transfers(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		$this->load->model('transferv1_model');

		if (empty($app_reference) == false) {
			$booking_details = $this->transferv1_model->get_booking_details($app_reference, $booking_source, $booking_status);
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions', 'description', array('module' => 'transfer'));

			if ($booking_details['status'] == SUCCESS_STATUS) {
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_transferv1_booking_data($booking_details, $this->current_module);

				$page_data['data'] = $assembled_booking_details['data'];
				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher

					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,phone,domain_name,phone_code', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['phone_code'] = $domain_address['data'][0]['phone_code'];
					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];

					if ($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0) {
						$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
						if (!empty($get_agent_info)) {
							$page_data['data']['address'] = $get_agent_info[0]['address'];
							$page_data['data']['logo'] = $get_agent_info[0]['logo'];
							$page_data['data']['phone'] = $get_agent_info[0]['phone'];
							$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
						}
					}
					$page_data['data']['terms_conditions'] = '';
					if ($terms_conditions['status'] == SUCCESS_STATUS) {
						$page_data['data']['terms_conditions'] = $terms_conditions['data'][0]['description'];
					}
				}
				//debug($page_data);exit;
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/transfer_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/transfer_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');

						break;
					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/transfer_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Transfers Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
	public function flight(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		$this->load->model('flight_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source, $booking_status);

			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_flight_lib(PROVAB_FLIGHT_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_flight_booking_data($booking_details, 'b2c', false);
				//$assembled_booking_details = $this->booking_data_formatter->format_flight_booking_data($booking_details, $this->current_module);				
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher

					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,phone,domain_name', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];
					if ($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0) {
						$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
						if (!empty($get_agent_info)) {
							$page_data['data']['address'] = $get_agent_info[0]['address'];
							$page_data['data']['logo'] = $get_agent_info[0]['logo'];
							$page_data['data']['phone'] = $get_agent_info[0]['phone'];
							$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
						}
					}
				}

				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/flight_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/flight_pdf', $page_data);
						//debug($get_view);exit;
						$create_pdf->create_pdf($get_view, 'show');
						break;
					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/flight_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Flight Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
	public function b2c_flight_voucher(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		$this->load->model('flight_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source, $booking_status);
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions', 'description', array('module' => 'flight'));
			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_flight_lib(PROVAB_FLIGHT_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_flight_booking_data($booking_details, 'b2c', false);
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher

					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,domain_name,phone,phone_code', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone_code'] = $domain_address['data'][0]['phone_code'];
					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];
					// debug($assembled_booking_details);exit;
					// if($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0){
					// 	$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
					// 	// debug($get_agent_info);exit;
					// 	if(!empty($get_agent_info)){
					// 	$page_data['data']['address'] = $get_agent_info[0]['address'];
					// 	$page_data['data']['logo'] = $get_agent_info[0]['logo'];
					// 	}

					// }
					$page_data['data']['terms_conditions'] = '';
					if ($terms_conditions['status'] == SUCCESS_STATUS) {
						$page_data['data']['terms_conditions'] = $terms_conditions['data'][0]['description'];
					}
				}
				// debug($page_data);exit;
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/flight_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/flight_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');
						break;
					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/flight_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Flight Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
	public function b2b_flight_voucher(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		$this->load->model('flight_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source, $booking_status);
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions', 'description', array('module' => 'flight'));
			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_flight_lib(PROVAB_FLIGHT_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_flight_booking_data($booking_details, $this->current_module);
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher

					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,domain_name,phone,phone_code', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['phone_code'] = $domain_address['data'][0]['phone_code'];
					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];
					// if($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0){
					// 	$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
					// 	// debug($get_agent_info);exit;
					// 	if(!empty($get_agent_info)){
					// 	$page_data['data']['address'] = $get_agent_info[0]['address'];
					// 	$page_data['data']['logo'] = $get_agent_info[0]['logo'];
					// 	$page_data['data']['phone'] = $get_agent_info[0]['phone'];
					// 	$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
					// 	}
					// }
					$page_data['data']['terms_conditions'] = '';
					if ($terms_conditions['status'] == SUCCESS_STATUS) {
						$page_data['data']['terms_conditions'] = $terms_conditions['data'][0]['description'];
					}
				}
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/flight_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/flight_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');
						break;
					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/flight_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Flight Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
	public function b2c_hotel_voucher(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		$this->load->model('hotel_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source, $booking_status);
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions', 'description', array('module' => 'hotel'));
			//echo $this->db->last_query();exit;
			if ($booking_details['status'] == SUCCESS_STATUS) {
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2c');
				$page_data['data'] = $assembled_booking_details['data'];
				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher

					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,domain_name,phone,phone_code', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];

					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['phone_code'] = $domain_address['data'][0]['phone_code'];

					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];


					// if($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0){
					// 	$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
					// 	if(!empty($get_agent_info)){
					// 		$page_data['data']['address'] = $get_agent_info[0]['address'];
					// 		$page_data['data']['logo'] = $get_agent_info[0]['logo'];
					// 		$page_data['data']['phone'] = $get_agent_info[0]['phone'];
					// 		$page_data['data']['domainname'] = $get_agent_info[0]['domain_name'];

					// 	}
					$page_data['data']['terms_conditions'] = '';
					if ($terms_conditions['status'] == SUCCESS_STATUS) {
						$page_data['data']['terms_conditions'] = $terms_conditions['data'][0]['description'];
					}
				}
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/hotel_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/hotel_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');

						break;
					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/hotel_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Hotel Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
	public function b2b_hotel_voucher(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		$this->load->model('hotel_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source, $booking_status);
			if ($booking_details['status'] == SUCCESS_STATUS) {
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_hotel_booking_data($booking_details, $this->current_module);
				$page_data['data'] = $assembled_booking_details['data'];
				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher

					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,phone,domain_name', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];

					// if($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0){
					// 	$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
					// 	if(!empty($get_agent_info)){
					// 		$page_data['data']['address'] = $get_agent_info[0]['address'];
					// 		$page_data['data']['logo'] = $get_agent_info[0]['logo'];
					// 		$page_data['data']['phone'] = $get_agent_info[0]['phone'];
					// 		$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];

					// 	}
					// }

				}
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/hotel_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/hotel_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');

						break;
					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/hotel_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Hotel Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
	public function b2c_bus_voucher(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		//echo 'under working';exit;
		$this->load->model('bus_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->bus_model->get_booking_details($app_reference, $booking_source, $booking_status);
			if ($booking_details['status'] == SUCCESS_STATUS) {
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_bus_booking_data($booking_details, 'b2c');
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher
					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,phone_code', array('origin' => get_domain_auth_id()));
					//print_r($domain_address);exit;//
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone_code'] = $domain_address['data'][0]['phone_code'];
					// if($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0){
					// 	$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
					// 	if(!empty($get_agent_info)){
					// 		$page_data['data']['address'] = $get_agent_info[0]['address'];
					// 		$page_data['data']['logo'] = $get_agent_info[0]['logo'];
					// 	}
					// }

				}
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/bus_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/bus_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');
						break;

					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/bus_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Bus Ticket', $mail_template, $pdf);
						break;
				}
			} else {
				redirect('security/log_event?event=Invalid AppReference');
			}
		} else {
			redirect('security/log_event?event=Invalid AppReference');
		}
	}
	public function b2b_bus_voucher(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		//echo 'under working';exit;
		$this->load->model('bus_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->bus_model->get_booking_details($app_reference, $booking_source, $booking_status);
			if ($booking_details['status'] == SUCCESS_STATUS) {
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_bus_booking_data($booking_details, $this->current_module);
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher
					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					// if($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0){
					// 	$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
					// 	if(!empty($get_agent_info)){
					// 		$page_data['data']['address'] = $get_agent_info[0]['address'];
					// 		$page_data['data']['logo'] = $get_agent_info[0]['logo'];
					// 	}
					// }

				}
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/bus_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/bus_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');
						break;

					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/bus_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Bus Ticket', $mail_template, $pdf);
						break;
				}
			} else {
				redirect('security/log_event?event=Invalid AppReference');
			}
		} else {
			redirect('security/log_event?event=Invalid AppReference');
		}
	}
	public function car(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		$this->load->model('car_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->car_model->get_booking_details($app_reference, $booking_source, $booking_status);
			// debug($booking_details);exit;
			if ($booking_details['status'] == SUCCESS_STATUS) {
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_car_booking_datas($booking_details, 'b2c');
				// debug($assembled_booking_details);exit;
				$page_data['data'] = $assembled_booking_details['data'];
				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher

					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
				}
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/car_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/car_pdf', $page_data);
						// debug($get_view);exit;
						$create_pdf->create_pdf($get_view, 'show');

						break;
					case 'email_voucher':
						$email = $this->load->library('provab_pdf');
						$email = $booking_details['data']['booking_details'][0]['email'];
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/car_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Car Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
	public function flight_invoice(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher'
	): void {
		$page_data=[];
		$this->load->model('flight_model');
		if (empty($app_reference) == false) {
			$data = $this->flight_model->get_booking_details($app_reference, $booking_source, $booking_status);
			// debug($data);exit;
			if ($data['status'] == SUCCESS_STATUS) {
				//depending on booking source we need to convert to view array
				load_flight_lib($data['data']['booking_details']['booking_source']);
				$page_data = $this->flight_lib->parse_voucher_data($data['data']);
				$domain_details = $this->custom_db->single_table_records('domain_list', '*', array('origin' => $page_data['booking_details']['domain_origin']));
				$page_data['domain_details'] = $domain_details['data'][0];
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/flight_invoice', $page_data);
						break;
				}
			}
		}
	}
	public function flight_invoice_GST(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $module = ''
	): void {
		$page_data=[];
		error_reporting(0);
		$this->load->model('flight_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source, $booking_status);
			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_flight_lib(PROVAB_FLIGHT_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_flight_booking_data($booking_details, $this->current_module);
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher
					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,domain_name,phone', array('origin' => get_domain_auth_id()));

					$page_data['admin_details']['address'] = $domain_address['data'][0]['address'];
					$page_data['admin_details']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['admin_details']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['admin_details']['domainname'] = $domain_address['data'][0]['domain_name'];

					if ($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0) {
						$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);

						if (!empty($get_agent_info)) {
							$page_data['data']['address'] = $get_agent_info[0]['address'];
							$page_data['data']['logo'] = $get_agent_info[0]['logo'];
							$page_data['data']['phone'] = $get_agent_info[0]['phone'];
							$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
						}
					} else {
						$page_data['data']['address'] = $assembled_booking_details['data']['booking_details'][0]['cutomer_address'];

						$page_data['data']['phone'] = $assembled_booking_details['data']['booking_details'][0]['lead_pax_phone_number'];
						$page_data['data']['domainname'] = $assembled_booking_details['data']['booking_details'][0]['lead_pax_name'];

						$page_data['data']['domaincountry'] = $assembled_booking_details['data']['booking_details'][0]['cutomer_country'];
					}
				}

				//debug($page_data);
				// exit;
				$page_data['module'] = $module;
				$this->template->view('voucher/flight_invoice_new', $page_data);
			}
		}
	}
	public function hotel_invoice_GST(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $module = ''
	): void {
		$page_data=[];
		error_reporting(0);
		$this->load->model('hotel_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source, $booking_status);
			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_flight_lib(PROVAB_FLIGHT_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_hotel_booking_data($booking_details, $this->current_module);
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher
					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,domain_name,phone', array('origin' => get_domain_auth_id()));

					$page_data['admin_details']['address'] = $domain_address['data'][0]['address'];
					$page_data['admin_details']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['admin_details']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['admin_details']['domainname'] = $domain_address['data'][0]['domain_name'];

					if ($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0) {
						$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);

						if (!empty($get_agent_info)) {
							$page_data['data']['address'] = $get_agent_info[0]['address'];
							$page_data['data']['logo'] = $get_agent_info[0]['logo'];
							$page_data['data']['phone'] = $get_agent_info[0]['phone'];
							$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
						}
					} else {
						$page_data['data']['address'] = $assembled_booking_details['data']['booking_details'][0]['cutomer_address'];

						$page_data['data']['phone'] = $assembled_booking_details['data']['booking_details'][0]['lead_pax_phone_number'];
						$page_data['data']['domainname'] = $assembled_booking_details['data']['booking_details'][0]['lead_pax_name'];

						$page_data['data']['domaincountry'] = $assembled_booking_details['data']['booking_details'][0]['cutomer_country'];
					}
				}

				// debug($page_data);
				// exit;
				$page_data['module'] = $module;
				$this->template->view('voucher/hotel_invoice', $page_data);
			}
		}
	}
	public function bus_invoice_GST(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $module = ''
	): void {
		$page_data=[];
		error_reporting(0);
		$this->load->model('bus_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->bus_model->get_booking_details($app_reference, $booking_source, $booking_status);
			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_flight_lib(PROVAB_FLIGHT_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_bus_booking_data($booking_details, $this->current_module);
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher
					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,domain_name,phone', array('origin' => get_domain_auth_id()));

					$page_data['admin_details']['address'] = $domain_address['data'][0]['address'];
					$page_data['admin_details']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['admin_details']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['admin_details']['domainname'] = $domain_address['data'][0]['domain_name'];

					if ($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0) {
						$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);

						if (!empty($get_agent_info)) {
							$page_data['data']['address'] = $get_agent_info[0]['address'];
							$page_data['data']['logo'] = $get_agent_info[0]['logo'];
							$page_data['data']['phone'] = $get_agent_info[0]['phone'];
							$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
						}
					} else {
						$page_data['data']['address'] = $assembled_booking_details['data']['booking_details'][0]['cutomer_address'];

						$page_data['data']['phone'] = $assembled_booking_details['data']['booking_details'][0]['lead_pax_phone_number'];
						$page_data['data']['domainname'] = $assembled_booking_details['data']['booking_details'][0]['lead_pax_name'];

						$page_data['data']['domaincountry'] = $assembled_booking_details['data']['booking_details'][0]['cutomer_country'];
					}
				}

				// debug($page_data);
				// exit;
				$page_data['module'] = $module;
				$this->template->view('voucher/bus_invoice', $page_data);
			}
		}
	}
	public function activity_invoice_GST(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $module = ''
	): void {
		$page_data=[];
		error_reporting(0);
		$this->load->model('sightseeing_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->sightseeing_model->get_booking_details($app_reference, $booking_source, $booking_status);
			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_flight_lib(PROVAB_FLIGHT_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_sightseeing_booking_data($booking_details, $this->current_module);
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher
					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,domain_name,phone', array('origin' => get_domain_auth_id()));

					$page_data['admin_details']['address'] = $domain_address['data'][0]['address'];
					$page_data['admin_details']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['admin_details']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['admin_details']['domainname'] = $domain_address['data'][0]['domain_name'];

					if ($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0) {
						$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);

						if (!empty($get_agent_info)) {
							$page_data['data']['address'] = $get_agent_info[0]['address'];
							$page_data['data']['logo'] = $get_agent_info[0]['logo'];
							$page_data['data']['phone'] = $get_agent_info[0]['phone'];
							$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
						}
					} else {
						$page_data['data']['address'] = $assembled_booking_details['data']['booking_details'][0]['cutomer_address'];

						$page_data['data']['phone'] = $assembled_booking_details['data']['booking_details'][0]['lead_pax_phone_number'];
						$page_data['data']['domainname'] = $assembled_booking_details['data']['booking_details'][0]['lead_pax_name'];

						$page_data['data']['domaincountry'] = $assembled_booking_details['data']['booking_details'][0]['cutomer_country'];
					}
				}

				// debug($page_data);
				// exit;
				$page_data['module'] = $module;
				$this->template->view('voucher/activity_invoice', $page_data);
			}
		}
	}
	public function transfer_invoice_GST(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $module = ''
	): void {
		$page_data=[];
		error_reporting(0);
		$this->load->model('transferv1_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->transferv1_model->get_booking_details($app_reference, $booking_source, $booking_status);
			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_flight_lib(PROVAB_FLIGHT_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_transferv1_booking_data($booking_details, $this->current_module);
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher
					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,domain_name,phone', array('origin' => get_domain_auth_id()));

					$page_data['admin_details']['address'] = $domain_address['data'][0]['address'];
					$page_data['admin_details']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['admin_details']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['admin_details']['domainname'] = $domain_address['data'][0]['domain_name'];

					if ($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0) {
						$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);

						if (!empty($get_agent_info)) {
							$page_data['data']['address'] = $get_agent_info[0]['address'];
							$page_data['data']['logo'] = $get_agent_info[0]['logo'];
							$page_data['data']['phone'] = $get_agent_info[0]['phone'];
							$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
						}
					} else {
						$page_data['data']['address'] = $assembled_booking_details['data']['booking_details'][0]['cutomer_address'];

						$page_data['data']['phone'] = $assembled_booking_details['data']['booking_details'][0]['lead_pax_phone_number'];
						$page_data['data']['domainname'] = $assembled_booking_details['data']['booking_details'][0]['lead_pax_name'];

						$page_data['data']['domaincountry'] = $assembled_booking_details['data']['booking_details'][0]['cutomer_country'];
					}
				}

				// debug($page_data);
				// exit;
				$page_data['module'] = $module;
				$this->template->view('voucher/transfer_invoice', $page_data);
			}
		}
	}
	public function b2c_sightseeing_voucher(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		$this->load->model('sightseeing_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->sightseeing_model->get_booking_details($app_reference, $booking_source, $booking_status);
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions', 'description', array('module' => 'activity'));

			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_sightseen_lib(PROVAB_SIGHTSEEN_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_sightseeing_booking_data($booking_details, 'b2c', false);
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher

					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,domain_name,phone,phone_code', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['phone_code'] = $domain_address['data'][0]['phone_code'];

					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];

					// if($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0){
					// 	$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
					// 	if(!empty($get_agent_info)){
					// 		$page_data['data']['address'] = $get_agent_info[0]['address'];
					// 		$page_data['data']['logo'] = $get_agent_info[0]['logo'];
					// 		$page_data['data']['phone'] = $get_agent_info[0]['phone'];
					// 		$page_data['data']['domainname'] = $get_agent_info[0]['domain_name'];
					// 	}
					// }
					$page_data['data']['terms_conditions'] = '';
					if ($terms_conditions['status'] == SUCCESS_STATUS) {
						$page_data['data']['terms_conditions'] = $terms_conditions['data'][0]['description'];
					}
				}
				//debug($page_data);exit;
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/sightseeing_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/sightseeing_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');
						break;
					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/sightseeing_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Sightseeing Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
	public function b2b_sightseeing_voucher(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		$this->load->model('sightseeing_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->sightseeing_model->get_booking_details($app_reference, $booking_source, $booking_status);
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions', 'description', array('module' => 'activity'));
			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_sightseen_lib(PROVAB_SIGHTSEEN_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_sightseeing_booking_data($booking_details, 'b2b');
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher

					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,phone,domain_name,phone_code', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['phone_code'] = $domain_address['data'][0]['phone_code'];
					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];
					// if($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0){
					// 	$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
					// 	if(!empty($get_agent_info)){
					// 	$page_data['data']['address'] = $get_agent_info[0]['address'];
					// 	$page_data['data']['logo'] = $get_agent_info[0]['logo'];
					// 	$page_data['data']['phone'] = $get_agent_info[0]['phone'];
					// 	$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
					// 	}
					// }
					$page_data['data']['terms_conditions'] = '';
					if ($terms_conditions['status'] == SUCCESS_STATUS) {
						$page_data['data']['terms_conditions'] = $terms_conditions['data'][0]['description'];
					}
				}
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/sightseeing_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/sightseeing_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');
						break;
					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/sightseeing_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Sightseeing Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
	public function b2c_transfers_voucher(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		$this->load->model('transferv1_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->transferv1_model->get_booking_details($app_reference, $booking_source, $booking_status);
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions', 'description', array('module' => 'transfers'));
			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_transferv1_lib(PROVAB_TRANSFERV1_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_transferv1_booking_data($booking_details, 'b2c', false);
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher

					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,phone,domain_name,phone_code', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['phone_code'] = $domain_address['data'][0]['phone_code'];

					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];

					// if($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0){
					// 	$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
					// 	if(!empty($get_agent_info)){
					// 		$page_data['data']['address'] = $get_agent_info[0]['address'];
					// 		$page_data['data']['logo'] = $get_agent_info[0]['logo'];
					// 		$page_data['data']['phone'] = $get_agent_info[0]['phone'];
					// 		$page_data['data']['domainname'] = $get_agent_info[0]['domain_name'];

					// 	}
					// }
					$page_data['data']['terms_conditions'] = '';
					if ($terms_conditions['status'] == SUCCESS_STATUS) {
						$page_data['data']['terms_conditions'] = $terms_conditions['data'][0]['description'];
					}
				}
				//debug($page_data);exit;
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/transfer_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/transfer_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');
						break;
					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/transfer_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Transfers Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
	public function b2b_transfers_voucher(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data=[];
		$this->load->model('transferv1_model');
		if (empty($app_reference) == false) {
			$booking_details = $this->transferv1_model->get_booking_details($app_reference, $booking_source, $booking_status);
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions', 'description', array('module' => 'transfers'));
			if ($booking_details['status'] == SUCCESS_STATUS) {
				load_transferv1_lib(PROVAB_TRANSFERV1_BOOKING_SOURCE);
				//Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_transferv1_booking_data($booking_details, $this->current_module);
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					//get agent address & logo for b2b voucher

					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo,phone,domain_name,phone_code', array('origin' => get_domain_auth_id()));
					$page_data['data']['address'] = $domain_address['data'][0]['address'];
					$page_data['data']['logo'] = $domain_address['data'][0]['domain_logo'];
					$page_data['data']['phone'] = $domain_address['data'][0]['phone'];
					$page_data['data']['phone_code'] = $domain_address['data'][0]['phone_code'];
					$page_data['data']['domainname'] = $domain_address['data'][0]['domain_name'];

					// if($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0){
					// 	$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);
					// 	if(!empty($get_agent_info)){
					// 		$page_data['data']['address'] = $get_agent_info[0]['address'];
					// 		$page_data['data']['logo'] = $get_agent_info[0]['logo'];
					// 		$page_data['data']['phone'] = $get_agent_info[0]['phone'];
					// 		$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
					// 	}
					// }
					$page_data['data']['terms_conditions'] = '';
					if ($terms_conditions['status'] == SUCCESS_STATUS) {
						$page_data['data']['terms_conditions'] = $terms_conditions['data'][0]['description'];
					}
				}
				switch ($operation) {
					case 'show_voucher':
						$this->template->view('voucher/transfer_voucher', $page_data);
						break;
					case 'show_pdf':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$get_view = $this->template->isolated_view('voucher/transfer_pdf', $page_data);
						$create_pdf->create_pdf($get_view, 'show');
						break;
					case 'email_voucher':
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/transfer_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Transfers Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
}
