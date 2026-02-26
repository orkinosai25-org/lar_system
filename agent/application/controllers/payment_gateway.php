<?php

if (! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 *
 * @package Provab
 * @subpackage Transaction
 * @author Balu A <balu.provab@gmail.com>
 * @version V1
 */


class Payment_Gateway extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		// $this->output->enable_profiler(TRUE);
		$this->load->model('module_model');
	}

	/**
	 * Redirection to payment gateway
	 * @param string $book_id		Unique string to identify every booking - app_reference
	 * @param number $book_origin	Unique origin of booking
	 */

	public function payment(string $book_id, int $book_origin): void
{
	// Load the transaction model
	$this->load->model('transaction');

	// Load the active payment gateway
	$PG = $this->config->item('active_payment_gateway');
	load_pg_lib($PG);

	// Read the payment record
	$pg_record = $this->transaction->read_payment_record($book_id);

	// Fetch temporary booking info
	$temp_booking = $this->custom_db->single_table_records('temp_booking', '', ['book_id' => $book_id]);

	// Exit if booking not found
	if (!isset($temp_booking['data'][0]['id'])) {
		echo 'Booking not found';
		exit;
	}

	$book_origin = $temp_booking['data'][0]['id'];

	// Exit if payment record is empty or invalid
	if (empty($pg_record) || !is_array($pg_record)) {
		echo 'Under Construction :p';
		exit;
	}

	$params = json_decode($pg_record['request_params'], true);

	// Get the payment gateway status
	$payment_gateway_status = $this->config->item('enable_payment_gateway');

	if ($payment_gateway_status == true) {
		// Payment gateway is enabled - you can uncomment and use the logic below when needed
		/*
		$pg_initialize_data = [
			'txnid' => $params['txnid'],
			'pgi_amount' => ceil($pg_record['amount']),
			'firstname' => $params['firstname'],
			'email' => $params['email'],
			'phone' => $params['phone'],
			'productinfo' => $params['productinfo']
		];

		$this->pg->initialize($pg_initialize_data);
		$page_data['pay_data'] = $this->pg->process_payment();

		// Prevent browser caching
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		echo $this->template->isolated_view('payment/' . $PG . '/pay', $page_data);
		*/
		return;
	}

	// Payment gateway is disabled, redirect booking
	if (isset($params['productinfo'], $params['txnid'])) {
		$this->redirect_booking($params['productinfo'], $params['txnid'], $book_origin);
		return;
	}

	echo 'Invalid payment parameters';
	exit;
}


	public function success(): void
{
	$this->load->model('transaction');

	// Safely extract required request parameters
	$product = $_REQUEST['productinfo'] ?? null;
	$book_id = $_REQUEST['txnid'] ?? null;
	$pg_status = $_REQUEST['status'] ?? null;

	// Validate required input
	if (empty($product) || empty($book_id) || empty($pg_status)) {
		echo 'Missing required payment data';
		exit;
	}

	// Retrieve booking and payment record
	$temp_booking = $this->custom_db->single_table_records('temp_booking', '', ['book_id' => $book_id]);
	$pg_record = $this->transaction->read_payment_record($book_id);

	// Check for valid success conditions
	$is_valid = (
		$pg_status === 'success' &&
		!empty($pg_record) &&
		is_array($pg_record) &&
		isset($temp_booking['data'][0]) &&
		is_array($temp_booking['data'][0])
	);

	if (!$is_valid) {
		echo 'Invalid or incomplete payment information';
		exit;
	}

	// Proceed with payment success logic
	$response_params = $_REQUEST;
	$this->transaction->update_payment_record_status($book_id, ACCEPTED, $response_params);

	$book_origin = $temp_booking['data'][0]['id'];
	$this->redirect_booking($product, $book_id, $book_origin);
}


	private function redirect_booking(string $product, string $book_id, int $book_origin): void
	{
		switch ($product) {
			case META_AIRLINE_COURSE:
				redirect(base_url() . 'index.php/flight/process_booking/' . $book_id . '/' . $book_origin);
				break;

			case META_BUS_COURSE:
				redirect(base_url() . 'index.php/bus/process_booking/' . $book_id . '/' . $book_origin);
				break;

			case META_ACCOMODATION_COURSE:
				redirect(base_url() . 'index.php/hotel/process_booking/' . $book_id . '/' . $book_origin);
				break;

			case META_CAR_COURSE:
				redirect(base_url() . 'index.php/car/process_booking/' . $book_id . '/' . $book_origin);
				break;

			default:
				redirect(base_url() . 'index.php/transaction/cancel');
				break;
		}
	}

public function cancel(): void
{
	$this->load->model('transaction');

	// Safely get request parameters
	$product = $_REQUEST['productinfo'] ?? null;
	$book_id = $_REQUEST['txnid'] ?? null;

	if (empty($product) || empty($book_id)) {
		echo 'Missing required parameters.';
		exit;
	}

	// Get temporary booking and payment record
	$temp_booking = $this->custom_db->single_table_records('temp_booking', '', ['book_id' => $book_id]);
	$pg_record = $this->transaction->read_payment_record($book_id);

	// Validate record
	$is_valid = !empty($pg_record) && is_array($pg_record) && isset($temp_booking['data'][0]);
	if (!$is_valid) {
		echo 'Invalid booking or payment record.';
		exit;
	}

	// Update status
	$response_params = $_REQUEST;
	$this->transaction->update_payment_record_status($book_id, DECLINED, $response_params);

	$msg = "Payment Unsuccessful, Please try again.";
	$notification_url = 'index.php/';

	// Determine redirect URL based on product type
	switch ($product) {
		case META_AIRLINE_COURSE:
			$notification_url .= 'flight/exception?op=booking_exception&notification=' . urlencode($msg);
			break;

		case META_BUS_COURSE:
			$notification_url .= 'bus/exception?op=booking_exception&notification=' . urlencode($msg);
			break;

		case META_ACCOMODATION_COURSE:
			$notification_url .= 'hotel/exception?op=booking_exception&notification=' . urlencode($msg);
			break;

		default:
			$notification_url .= 'transaction/cancel';
			break;
	}

	redirect(base_url() . $notification_url);
}

	public function transaction_log(): void
	{

		load_pg_lib('PAYU');


		$output = $this->template->isolated_view('payment/PAYU/pay');


		echo $output;
	}
}
