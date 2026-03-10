<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab - Provab Application
 * @subpackage Travel Portal
 * @version    V2
 */
class Report extends CI_Controller
{
	public $session;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('hotel_model');
		$this->load->model('flight_model');
		$this->load->model('car_model');
		$this->load->library('booking_data_formatter');
	}
	function index()
	{
		$this->flight(0);
	}
	function bookings()
	{
		$this->template->view('report/bookings');
	}
	
	/************************************** HOTEL REPORT STARTS ***********************************/
	/**
	 * Hotel Report
	 * @param $offset
	 */
	function hotels($offset=0):void
	{ 
		$config = [];
		$page_data = [];
		validate_user_login();
		$condition = array();
		$total_records = $this->hotel_model->booking($condition, true);
		$table_data = $this->hotel_model->booking($condition, false, $offset, RECORDS_RANGE_2);
		$table_data = $this->booking_data_formatter->format_hotel_booking_data($table_data, 'b2c');
		$page_data['table_data'] = $table_data['data'];
		/** TABLE PAGINATION */
		$this->load->library('pagination');
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['base_url'] = base_url().'index.php/report/hotel/';
		$config['first_url'] = $config['base_url'].'?'.http_build_query($_GET);
		$page_data['total_rows'] = $config['total_rows'] = $total_records;
		$config['per_page'] = RECORDS_RANGE_2;
		$this->pagination->initialize($config);
		/** TABLE PAGINATION */
		$page_data['total_records'] = $config['total_rows'];
		$page_data['customer_email'] = $this->entity_email;
		$this->template->view('report/hotel', $page_data);
	}
	/************************************** CAR REPORT STARTS ***********************************/
	/**
	 * Cae Report
	 * @param $offset
	 */
	function car($offset=0):void
	{
		$config = [];
		$page_data = [];
		validate_user_login();
		$condition = array();
		$total_records = $this->car_model->booking($condition, true);
		$table_data = $this->car_model->booking($condition, false, $offset, RECORDS_RANGE_2);
		$table_data = $this->booking_data_formatter->format_car_booking_datas($table_data, 'b2c');
		$page_data['table_data'] = $table_data['data'];
		/** TABLE PAGINATION */
		$this->load->library('pagination');
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['base_url'] = base_url().'index.php/report/car/';
		$config['first_url'] = $config['base_url'].'?'.http_build_query($_GET);
		$page_data['total_rows'] = $config['total_rows'] = $total_records;
		$config['per_page'] = RECORDS_RANGE_2;
		$this->pagination->initialize($config);
		/** TABLE PAGINATION */
		$page_data['total_records'] = $config['total_rows'];
		$page_data['customer_email'] = $this->entity_email;
		// debug($page_data);exit;
		$this->template->view('report/car', $page_data);
	}
	

	/**
	 * Hotel Booking Dettails
	 */
	function hotel_booking_details(): void
	{
		$page_data = [];
		$get_data = $this->input->get();

		if (!valid_array($get_data) || empty($get_data['status']) || empty($get_data['reference_id']) || empty($get_data['app_reference'])) {
			redirect('general/index/bus?event=Invalid Booking Details');
			return;
		}

		$booking_id = trim($get_data['reference_id']);
		$status = trim($get_data['status']);
		$app_reference = trim($get_data['app_reference']);
		$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_id, $status);

		if (!valid_array($booking_details) || $booking_details['status'] !== SUCCESS_STATUS) {
			redirect('general/index/bus?event=Invalid Booking ID');
			return;
		}

		// Assemble Booking Details
		$assem_book_det = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2c');
		$page_data['data'] = $assem_book_det['data'];
		$this->template->view('hotel/booking_details', $page_data);
	}

	/**
	 * Hotel Voucher
	 */
	function get_hotel_voucher(): void
	{
		$page_data = [];
		$get_data = $this->input->get();

		if (!valid_array($get_data) || empty($get_data['reference_id']) || empty($get_data['app_reference'])) {
			redirect('general/index/bus?event=Invalid Booking Details');
			return;
		}

		$booking_id = trim($get_data['reference_id']);
		$status = trim($get_data['status']);
		$app_reference = trim($get_data['app_reference']);
		$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_id, $status);

		if (!valid_array($booking_details) || $booking_details['status'] !== SUCCESS_STATUS) {
			redirect('general/index/bus?event=Invalid Deatils');
			return;
		}

		// Assemble Booking Details
		$assem_book_det = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2c');
		$page_data['data'] = $assem_book_det['data'];
		$data = $this->template->isolated_view('hotel/get_voucher', $page_data);

		header('Content-Type:application/json');
		echo json_encode(['ticket' => get_compressed_output($data)]);
		exit;
	}

	/**
	 * Hotel Invoice
	 */
	function get_hotel_invoice(): void
	{
		$page_data = [];
		$get_data = $this->input->get();

		if (!valid_array($get_data) || empty($get_data['reference_id']) || empty($get_data['app_reference'])) {
			redirect('general/index/bus?event=Invalid Booking Details');
			return;
		}

		$booking_id = trim($get_data['reference_id']);
		$status = trim($get_data['status']);
		$app_reference = trim($get_data['app_reference']);
		$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_id, $status);

		if (!valid_array($booking_details) || $booking_details['status'] !== SUCCESS_STATUS) {
			redirect('general/index/bus?event=Invalid Deatils');
			return;
		}

		// Assemble Booking Details
		$assem_book_det = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2c');
		$page_data['data'] = $assem_book_det['data'];
		$data = $this->template->isolated_view('hotel/get_invoice', $page_data);

		header('Content-Type:application/json');
		echo json_encode(['invoice' => get_compressed_output($data)]);
		exit;
	}
	/**
	 * Mail Hotel Voucher
	 * @param $app_reference
	 * @param $booking_source
	 * @param $booking_status
	 * @param $user_email_id
	 * @param $operation
	 */

	function email_hotel_voucher(string $app_reference, string $booking_source = '', string $booking_status = '', string $user_email_id = ''): void
	{
		$page_data = [];
		if (empty($app_reference)) {
			redirect('general/index/bus?event=Invalid Deatils');
			return;
		}

		$booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source, $booking_status);

		if ($booking_details['status'] !== SUCCESS_STATUS) {
			header('Content-Type:application/json');
			echo json_encode(['status' => 'failed']);
			exit;
		}

		$this->load->library("provab_pdf");
		$this->load->library('provab_mailer');

		// Assemble Booking Details
		$assem_book_det = $this->booking_data_formatter->format_hotel_booking_data($booking_details, 'b2c');
		$page_data['data'] = $assem_book_det['data'];
		$mail_template = $this->template->isolated_view('hotel/get_voucher', $page_data);
		$pdf = $this->provab_pdf->create_pdf($mail_template);
		$user_email_id = trim($user_email_id);

		$this->provab_mailer->send_mail($user_email_id, 'ProApp - Hotel Ticket', $mail_template, $pdf);

		header('Content-Type:application/json');
		echo json_encode(['status' => SUCCESS_STATUS]);
		exit;
	}



	/************************************** FLIGHT REPORT STARTS ***********************************/
	/**
	 * Flight Report
	 * @param $offset
	 */
	function flights(int $offset=0):void
	{

		$page_data = [];
		$config = [];
		validate_user_login();
		$condition = array();
		$total_records = $this->flight_model->booking($condition, true);
		$table_data = $this->flight_model->booking($condition, false, $offset, RECORDS_RANGE_2);
		
		$table_data = $this->booking_data_formatter->format_flight_booking_data($table_data, 'b2c');
		
		$page_data['table_data'] = $table_data['data'];
		$this->load->library('pagination');
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['base_url'] = base_url().'index.php/report/flight/';
		$config['first_url'] = $config['base_url'].'?'.http_build_query($_GET);
		$page_data['total_rows'] = $config['total_rows'] = $total_records;
		$config['per_page'] = RECORDS_RANGE_2;
		$this->pagination->initialize($config);
		/** TABLE PAGINATION */
		$page_data['total_records'] = $config['total_rows'];
		$this->template->view('report/airline', $page_data);
	}
	/*
	 * Flight Booking Details
	 */
	function flight_booking_details(): void
	{
		$page_data = [];
		$get_data = $this->input->get();

		if (!valid_array($get_data) || empty($get_data['status']) || empty($get_data['reference_id']) || empty($get_data['app_reference'])) {
			redirect('general/index/flights?event=Invalid Booking Details');
			return;
		}

		$booking_id = trim($get_data['reference_id']);
		$status = trim($get_data['status']);
		$app_reference = trim($get_data['app_reference']);
		$booking_details = $this->flight_model->get_booking_details($app_reference, $booking_id, $status);

		if (!valid_array($booking_details) || $booking_details['status'] !== SUCCESS_STATUS) {
			redirect('general/index/flights?event=Invalid Booking ID');
			return;
		}

		$assem_book_det = $this->booking_data_formatter->format_flight_booking_data($booking_details, 'b2c');
		$page_data['data'] = $assem_book_det['data'];
		$this->template->view('flight/booking_details', $page_data);
	}
	/*
	 * Flight Ticket
	 */
	function get_flight_ticket(): void
	{
		$page_data = [];
		$get_data = $this->input->get();

		if (!valid_array($get_data) || empty($get_data['reference_id']) || empty($get_data['app_reference'])) {
			redirect('general/index/bus?event=Invalid Booking Details');
			return;
		}

		$booking_id = trim($get_data['reference_id']);
		$status = trim($get_data['status']);
		$app_reference = trim($get_data['app_reference']);
		$booking_details = $this->flight_model->get_booking_details($app_reference, $booking_id, $status);

		if (!valid_array($booking_details) || $booking_details['status'] !== SUCCESS_STATUS) {
			redirect('general/index/bus?event=Invalid Deatils');
			return;
		}

		$page_data['booking_details'] = $booking_details;
		$data = $this->template->isolated_view('flight/get_eticket', $page_data);

		header('Content-Type:application/json');
		echo json_encode(['ticket' => get_compressed_output($data)]);
		exit;
	}

	/**
	 * Flight Invoice
	 */
	function get_flight_invoice(): void
	{
		$page_data = [];
		$get_data = $this->input->get();

		if (!valid_array($get_data) || empty($get_data['reference_id']) || empty($get_data['app_reference'])) {
			redirect('general/index/bus?event=Invalid Booking Details');
			return;
		}

		$booking_id = trim($get_data['reference_id']);
		$status = trim($get_data['status']);
		$app_reference = trim($get_data['app_reference']);

		$booking_details = $this->flight_model->get_booking_details($app_reference, $booking_id, $status);

		if (!valid_array($booking_details) || $booking_details['status'] !== SUCCESS_STATUS) {
			redirect('general/index/bus?event=Invalid Deatils');
			return;
		}

		$page_data['booking_details'] = $booking_details;
		$data = $this->template->isolated_view('flight/get_invoice', $page_data);

		header('Content-Type:application/json');
		echo json_encode(['invoice' => get_compressed_output($data)]);
		exit;
	}

	/*
	 * Mail Flight Ticket
	 */
	function email_flight_ticket(string $app_reference, string $booking_source = '', string $booking_status = '', string $user_email_id = ''): void
	{
		$page_data = [];
		if (empty($app_reference)) {
			redirect('general/index/flights?event=Invalid Details');
			return;
		}

		$booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source, $booking_status);

		if ($booking_details['status'] !== SUCCESS_STATUS) {
			header('Content-Type:application/json');
			echo json_encode(['status' => 'failed']);
			exit;
		}

		$this->load->library("provab_pdf");
		$this->load->library('provab_mailer');

		$page_data['booking_details'] = $booking_details;
		$mail_template = $this->template->isolated_view('flight/get_eticket', $page_data);
		$pdf = $this->provab_pdf->create_pdf($mail_template);
		$user_email_id = trim($user_email_id);

		$this->provab_mailer->send_mail($user_email_id, 'ProApp - Flight Ticket', $mail_template, $pdf);

		header('Content-Type:application/json');
		echo json_encode(['status' => SUCCESS_STATUS]);
		exit;
	}
	
	function monthly_booking_report():void
	{
		$this->template->view('report/monthly_booking_report');
	}
	/* print the voucher for all modules for B2C*/
	function print_voucher(): void
	{
		$page_data = [];
		$config = [];
		$post_data = $this->input->post();

		if (!isset($post_data) || !valid_array($post_data)) {
			$this->template->view('report/print_voucher');
			return;
		}

		if (empty($post_data['pnr_number'])) {
			$this->template->view('report/print_voucher');
			return;
		}

		$this->load->library('pagination');
		$page_data['total_rows'] = $config['total_rows'] = 1;
		$config['per_page'] = RECORDS_RANGE_2;
		$this->pagination->initialize($config);
		$page_data['print_voucher'] = 'yes';
		$page_data['total_records'] = $config['total_rows'];

		$booking_status = 'BOOKING_CONFIRMED';

		switch ($post_data['module']) {
			case PROVAB_FLIGHT_BOOKING_SOURCE:
				$booking_details = $this->flight_model->booking_guest_user($post_data['pnr_number'], $post_data['module'], $booking_status);
				break;
			case PROVAB_HOTEL_BOOKING_SOURCE:
				$booking_details = $this->hotel_model->booking_guest_user($post_data['pnr_number'], $post_data['module'], $booking_status);
				break;
			case PROVAB_BUS_BOOKING_SOURCE:
				$booking_details = $this->bus_model->booking_guest_user($post_data['pnr_number'], $post_data['module'], $booking_status);
				break;
			case PROVAB_TRANSFERV1_BOOKING_SOURCE:
				$booking_details = $this->transferv1_model->booking_guest_user($post_data['pnr_number'], $post_data['module'], $booking_status);
				break;
			case PROVAB_SIGHTSEEN_BOOKING_SOURCE:
				$booking_details = $this->sightseeing_model->booking_guest_user($post_data['pnr_number'], $post_data['module'], $booking_status);
				break;
			default:
				$booking_details = null;
				break;
		}

		if ($booking_details && $booking_details['status'] === SUCCESS_STATUS) {
			$assem_book_det = $this->booking_data_formatter->format_booking_data($booking_details, $post_data['module'], 'b2c');
			$page_data['table_data'] = $assem_book_det['data'];

			switch ($post_data['module']) {
				case PROVAB_FLIGHT_BOOKING_SOURCE:
					$this->template->view('report/airline', $page_data);
					break;
				case PROVAB_HOTEL_BOOKING_SOURCE:
					$this->template->view('report/hotel', $page_data);
					break;
				case PROVAB_BUS_BOOKING_SOURCE:
					$this->template->view('report/bus', $page_data);
					break;
				case PROVAB_TRANSFERV1_BOOKING_SOURCE:
					$this->template->view('report/transferv1', $page_data);
					break;
				case PROVAB_SIGHTSEEN_BOOKING_SOURCE:
					$this->template->view('report/sightseeing', $page_data);
					break;
			}
		}
	}

	function pnr_status(): void
	{
		$pnr_number = trim((string)($this->input->post('pnr_number') ?? $this->input->get('pnr_number') ?? ''));

		header('Content-Type:application/json');

		if (empty($pnr_number)) {
			echo json_encode(['status' => FAILURE_STATUS, 'message' => 'No PNR provided']);
			exit;
		}

		$booking_details = $this->flight_model->booking_guest_user($pnr_number);

		if (valid_array($booking_details) && $booking_details['status'] === SUCCESS_STATUS) {
			echo json_encode(['status' => SUCCESS_STATUS, 'message' => 'PNR found']);
		} else {
			echo json_encode(['status' => FAILURE_STATUS, 'message' => 'No PNR found']);
		}
		exit;
	}
	/* print the voucher for all modules for B2C*/
	function cancel_booking(): void
	{
		$page_data = [];
		$post_data = $this->input->post();

		if (!isset($post_data) || !valid_array($post_data)) {
			$this->template->view('report/cancel_booking');
			return;
		}

		if (empty($post_data['pnr_number'])) {
			$this->template->view('report/cancel_booking');
			return;
		}

		$booking_status = 'BOOKING_CONFIRMED';

		switch ($post_data['module']) {
			case PROVAB_FLIGHT_BOOKING_SOURCE:
				$booking_details = $this->flight_model->booking_guest_user($post_data['pnr_number'], $post_data['module'], $booking_status);
				break;
			case PROVAB_HOTEL_BOOKING_SOURCE:
				$booking_details = $this->hotel_model->booking_guest_user($post_data['pnr_number'], $post_data['module'], $booking_status);
				break;
			default:
				$booking_details = null;
				break;
		}

		if (!$booking_details || $booking_details['status'] !== SUCCESS_STATUS) {
			$this->session->set_flashdata('msg', 'Booking Not Found!');
			redirect("report/cancel_booking");
			return;
		}

		switch ($post_data['module']) {
			case PROVAB_FLIGHT_BOOKING_SOURCE:
				$assem_book_det = $this->booking_data_formatter->format_flight_booking_data($booking_details, 'b2c');
				$page_data['table_data'] = $assem_book_det['data'];
				$this->template->view('report/airline', $page_data);
				break;
			case PROVAB_HOTEL_BOOKING_SOURCE:
				redirect('hotel/guest_pre_cancellation/' . $post_data['pnr_number'] . '/' . PROVAB_HOTEL_BOOKING_SOURCE);
				break;
		}
	}

} 
?>