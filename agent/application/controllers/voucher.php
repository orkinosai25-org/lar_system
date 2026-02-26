<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Voucher Controller
 *
 * @package    Provab
 * @subpackage Bus
 * @author     Balu A <balu.provab@gmail.com>
 * @version    V1
 */

class Voucher extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->library('provab_mailer');
		$this->load->library('booking_data_formatter');
	}

	/**
	 * Function to handle bus voucher operations.
	 */
	

	/**
	 * Function to handle sightseeing voucher operations.
	 */
	

	
	function hotel(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$page_data = [];
		$this->load->model('hotel_model');

		if (!empty($app_reference)) {
			$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source, $booking_status);
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions', 'description', ['module' => 'hotel']);

			if ($booking_details['status'] === SUCCESS_STATUS) {
				// Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2b');
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					// Get agent address & logo for b2b voucher
					if ($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0) {
						$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);

						if (!empty($get_agent_info)) {
							$page_data['data']['address'] = $get_agent_info[0]['address'];
							$page_data['data']['logo'] = empty($get_agent_info[0]['image']) ? $page_data['data']['booking_details_app'][$app_reference]['domain_logo'] : $get_agent_info[0]['image'];
							$page_data['data']['country_code'] = $get_agent_info[0]['country_code'];
							$page_data['data']['phone'] = $get_agent_info[0]['phone'];
							$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
						}
					}
					$page_data['data']['terms_conditions'] = $terms_conditions['status'] === SUCCESS_STATUS ? $terms_conditions['data'][0]['description'] : '';

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
	}

	function flight(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$this->load->model('flight_model');
		$page_data = [];

		if (!empty($app_reference)) {
			$booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source, $booking_status);
			$terms_conditions = $this->custom_db->single_table_records('terms_conditions', 'description', ['module' => 'flight']);

			if ($booking_details['status'] === SUCCESS_STATUS) {
				load_flight_lib(PROVAB_FLIGHT_BOOKING_SOURCE);

				// Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_flight_booking_data($booking_details, 'b2b');
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					// Get agent address & logo for b2b voucher
					if ($assembled_booking_details['data']['booking_details'][0]['created_by_id'] > 0) {
						$get_agent_info = $this->user_model->get_agent_info($assembled_booking_details['data']['booking_details'][0]['created_by_id']);

						if (!empty($get_agent_info)) {
							$page_data['data']['address'] = $get_agent_info[0]['address'];
							$page_data['data']['logo'] = empty($get_agent_info[0]['image']) ? $page_data['data']['booking_details_app'][$app_reference]['domain_logo'] : $get_agent_info[0]['image'];
							$page_data['data']['phone'] = $get_agent_info[0]['phone'];
							$page_data['data']['country_code'] = $get_agent_info[0]['country_code'];
							$page_data['data']['domainname'] = $get_agent_info[0]['agency_name'];
						}
					}
					$page_data['data']['terms_conditions'] = $terms_conditions['status'] === SUCCESS_STATUS ? $terms_conditions['data'][0]['description'] : '';

					// Get the address
					if (isset($assembled_booking_details['data']['booking_details'][0]['created_by_id'])) {
						$get_address = $this->custom_db->single_table_records('user', 'address', ['user_id' => $assembled_booking_details['data']['booking_details'][0]['created_by_id']]);
						$page_data['data']['address'] = $get_address['data'][0]['address'];
					}

					$email = $booking_details['data']['booking_details'][0]['email'];

					switch ($operation) {
						case 'show_voucher':
							$this->template->view('voucher/flight_voucher', $page_data);
							if (!empty($email)) {
								$mail_template = $this->template->isolated_view('voucher/flight_voucher', $page_data);
								$this->provab_mailer->send_mail($email, domain_name() . ' - Flight Ticket', $mail_template);
							}
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
	}

	function car(
		string $app_reference,
		string $booking_source = '',
		string $booking_status = '',
		string $operation = 'show_voucher',
		string $email = ''
	): void {
		$this->load->model('car_model');
		$page_data = [];

		if (!empty($app_reference)) {
			$booking_details = $this->car_model->get_booking_details($app_reference, $booking_source, $booking_status);

			if ($booking_details['status'] === SUCCESS_STATUS) {
				// Assemble Booking Data
				$assembled_booking_details = $this->booking_data_formatter->format_car_booking_datas($booking_details, 'b2b');
				$page_data['data'] = $assembled_booking_details['data'];

				if (isset($assembled_booking_details['data']['booking_details'][0])) {
					// Get agent address & logo for b2b voucher
					$domain_address = $this->custom_db->single_table_records('domain_list', 'address,domain_logo', ['origin' => get_domain_auth_id()]);
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
						$create_pdf->create_pdf($get_view, 'show');
						break;
					case 'email_voucher':
						$email = $booking_details['data']['booking_details'][0]['email'];
						$this->load->library('provab_pdf');
						$create_pdf = new Provab_Pdf();
						$mail_template = $this->template->isolated_view('voucher/car_pdf', $page_data);
						$pdf = $create_pdf->create_pdf($mail_template, '');
						$this->provab_mailer->send_mail($email, domain_name() . ' - Car Ticket', $mail_template, $pdf);
						break;
				}
			}
		}
	}
}
