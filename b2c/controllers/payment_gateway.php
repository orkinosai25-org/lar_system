<?php
if (! defined ( 'BASEPATH' ))
exit ( 'No direct script access allowed' );
/**
 *
 * @package Provab
 * @subpackage Transaction
 * @author Balu A <balu.provab@gmail.com>
 * @version V1
 */
class Payment_Gateway extends CI_Controller {
	/**
	 *
	 * 
	 */
	public function __construct() {
		parent::__construct ();
		// $this->output->enable_profiler(TRUE);
		$this->load->model ( 'module_model' );
		$this->load->model('transaction');
	}
	/**
	 * Temporarily blocks the booking process for demo sites.
	 */
	function demo_booking_blocked()
	{
		echo '<h1>Booking Not Allowed, This Is Demo Site. Go To <a href="'.base_url().'">Travelomatix</a></h1>';
	}
	public function payment(string $book_id, int $book_origin): void{
		$this->load->model('transaction');
	    $page_data = [];
	    $Payment_Gateway = $this->config->item('active_payment_gateway');
	    load_pg_lib($Payment_Gateway);

	    $pg_record = $this->transaction->read_payment_record($book_id);
	    if (empty($pg_record) || !valid_array($pg_record)) {
	        show_error('Under Construction :p', 503);
	        return;
	    }

	    $pg_record['amount'] = roundoff_number($pg_record['amount'] * $pg_record['currency_conversion_rate']);
	    $params = json_decode($pg_record['request_params'], true);
	    $pg_initialize_data = [
	        'txnid'       => $params['txnid'] ?? '',
	        'pgi_amount'  => $pg_record['amount'],
	        'firstname'   => $params['firstname'] ?? '',
	        'email'       => $params['email'] ?? '',
	        'phone'       => $params['phone'] ?? '',
	        'productinfo' => $params['productinfo'] ?? '',
	    ];

	    if ($this->config->item('enable_payment_gateway') === true) {

	        $this->template->view('payment/payment_page', $pg_initialize_data);

	      
	    }
	}
	/**
	 * Redirection to payment gateway
	 * @param string $book_id		Unique string to identify every booking - app_reference
	 * @param number $book_origin	Unique origin of booking
	 */
	public function payment_old(string $book_id, int $book_origin): void
	{
	    $this->load->model('transaction');
	    $page_data = [];
	    $Payment_Gateway = $this->config->item('active_payment_gateway');
	    load_pg_lib($Payment_Gateway);

	    $pg_record = $this->transaction->read_payment_record($book_id);
	    if (empty($pg_record) || !valid_array($pg_record)) {
	        show_error('Under Construction :p', 503);
	        return;
	    }

	    $pg_record['amount'] = roundoff_number($pg_record['amount'] * $pg_record['currency_conversion_rate']);
	    $params = json_decode($pg_record['request_params'], true);

	    $pg_initialize_data = [
	        'txnid'       => $params['txnid'] ?? '',
	        'pgi_amount'  => $pg_record['amount'],
	        'firstname'   => $params['firstname'] ?? '',
	        'email'       => $params['email'] ?? '',
	        'phone'       => $params['phone'] ?? '',
	        'productinfo' => $params['productinfo'] ?? '',
	    ];

	    if ($this->config->item('enable_payment_gateway') === true) {
	        $this->pg->initialize($pg_initialize_data);
	        $page_data['pay_data'] = $this->pg->process_payment();

	        $resp = $this->pg->process_payment_test($page_data['pay_data']);
	        $testres = json_decode($resp, true);

	        $this->custom_db->insert_record('test', ['test' => $resp]);

	        if (isset($testres['links'][1]['href'])) {
	            redirect($testres['links'][1]['href']);
	        }

	        show_error('Payment gateway failed to return a valid response.', 500);
	    }

	    redirect("flight/secure_booking/{$book_id}/{$book_origin}");
	}

	/**
	 *
	 */
	public function response(): void
	{
	    $token = $this->input->get_post('token', true);

	    if (empty($token)) {
	        show_error('Invalid request: token missing.', 400);
	        return;
	    }

	    $access_token_result = $this->custom_db->single_table_records(
	        'paypal_access_token',
	        '',
	        ['payment_id' => $token]
	    );

	    if (empty($access_token_result['status']) || empty($access_token_result['data'][0]['access_toekn'])) {
	        show_error('Access token not found.', 404);
	        return;
	    }

	    $access_token = $access_token_result['data'][0]['access_toekn'];

	    // 1. Capture the payment
	    /*$capture_response = $this->make_curl_request(
	        "https://api.sandbox.paypal.com/v2/checkout/orders/{$token}/capture",
	        'POST',
	        [
	            'Content-Type: application/json',
	            "Authorization: Bearer {$access_token}"
	        ]
	    );*/

	    // 2. Get order details
	    $order_response = $this->make_curl_request(
	        "https://api.sandbox.paypal.com/v2/checkout/orders/{$token}",
	        'GET',
	        ["Authorization: Bearer {$access_token}"]
	    );

	    $order_data = json_decode($order_response, true);

	    if (!is_array($order_data) || empty($order_data['purchase_units'][0])) {
	        show_error('Failed to retrieve PayPal order details.', 500);
	        return;
	    }

	    $app_ref = $order_data['purchase_units'][0]['reference_id'] ?? '';
	    $response_amount = $order_data['purchase_units'][0]['amount']['value'] ?? '0';

	    $pg_record = $this->transaction->read_payment_record($app_ref);

	    $db_amount = number_format($pg_record['amount'] ?? 0, 2, '.', '');

	    $amount_matches = ((float)$db_amount === (float)$response_amount);

	    if (($order_data['status'] ?? '') === 'COMPLETED' && $amount_matches) {
	        $this->success($app_ref, $order_response, 1);
	        return; 
	    }
	    $this->cancel($app_ref, $order_response);
	}
	private function make_curl_request(string $url, string $method, array $headers = [], ?array $data = null): string
	{
			$response = [];
	    $curl = curl_init();

	    $options = [
	        CURLOPT_URL            => $url,
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_ENCODING       => '',
	        CURLOPT_MAXREDIRS      => 10,
	        CURLOPT_TIMEOUT        => 0,
	        CURLOPT_FOLLOWLOCATION => true,
	        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
	        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
	        CURLOPT_HTTPHEADER     => $headers,
	    ];

	    if (!empty($data)) {
	        $options[CURLOPT_POSTFIELDS] = json_encode($data);
	    }

	    curl_setopt_array($curl, $options);
	    $response = curl_exec($curl);
	    curl_close($curl);

	    return $response;
	}	
	public function success():void{
		$response = $this->input->get();
		$this->custom_db->insert_record('test', ['test' => $response]);

	    $this->load->model('transaction');

	    $temp_booking = $this->custom_db->single_table_records('temp_booking', '', [
	        'book_id' => $app_ref
	    ]);

	    $pg_record = $this->transaction->read_payment_record($app_ref);
	    $request_params = json_decode($pg_record['request_params'] ?? '{}', true);

	    if (
	        !empty($pg_record) &&
	        $amount_test === 1 &&
	        valid_array($pg_record) &&
	        valid_array($temp_booking['data'] ?? [])
	    ) {
	        // Sanitize and safely collect response
        $response_params = $this->input->post(null, true) ?: $this->input->get(null, true);

        $this->transaction->update_payment_record_status(
            $app_ref,
            ACCEPTED,
            $response_params
        );

        $book_origin = $temp_booking['data'][0]['id'];
        $product = $request_params['productinfo'] ?? '';

        $redirect_url = match ($product) {
            META_AIRLINE_COURSE       => 'flight',
            META_ACCOMODATION_COURSE  => 'hotel',
            META_CAR_COURSE           => 'car',
            default                   => 'transaction/cancel',
        };

        redirect(base_url("index.php/{$redirect_url}/process_booking/{$app_ref}/{$book_origin}"));
        return; // Optional: ensures no execution continues
	}
}
	public function success_old(string $app_ref, string $response, int $amount_test): void
	{
    $this->custom_db->insert_record('test', ['test' => $response]);

    $this->load->model('transaction');

    $temp_booking = $this->custom_db->single_table_records('temp_booking', '', [
        'book_id' => $app_ref
    ]);

    $pg_record = $this->transaction->read_payment_record($app_ref);
    $request_params = json_decode($pg_record['request_params'] ?? '{}', true);

    if (
        !empty($pg_record) &&
        $amount_test === 1 &&
        valid_array($pg_record) &&
        valid_array($temp_booking['data'] ?? [])
    ) {
        // Sanitize and safely collect response
        $response_params = $this->input->post(null, true) ?: $this->input->get(null, true);

        $this->transaction->update_payment_record_status(
            $app_ref,
            ACCEPTED,
            $response_params
        );

        $book_origin = $temp_booking['data'][0]['id'];
        $product = $request_params['productinfo'] ?? '';

        $redirect_url = match ($product) {
            META_AIRLINE_COURSE       => 'flight',
            META_ACCOMODATION_COURSE  => 'hotel',
            META_CAR_COURSE           => 'car',
            default                   => 'transaction/cancel',
        };

        redirect(base_url("index.php/{$redirect_url}/process_booking/{$app_ref}/{$book_origin}"));
        return; // Optional: ensures no execution continues
    }

    show_error('Booking validation failed or payment record missing.', 400);
	}

	public function cancel(string $book_id, string $response_f): void
	{
		$this->custom_db->insert_record('test', ['test' => $response_f]);
    $this->load->model('transaction');

    $temp_booking = $this->custom_db->single_table_records('temp_booking', '', [
        'book_id' => $book_id
    ]);

    $pg_record = $this->transaction->read_payment_record($book_id);
    $request_params = json_decode($pg_record['request_params'] ?? '{}', true);
    $product = $request_params['productinfo'] ?? '';

    if (empty($pg_record) || !valid_array($pg_record) || !valid_array($temp_booking['data'] ?? [])) {
        show_error("Booking cancellation failed: invalid or missing data.", 400);
        return;
    }

    $response_params = $this->input->post(null, true) ?: $this->input->get(null, true);
    $this->transaction->update_payment_record_status($book_id, DECLINED, $response_params);

    $msg = urlencode("Payment Unsuccessful, Please try again.");

    $redirect_url = match ($product) {
        META_AIRLINE_COURSE      => 'flight',
        META_CAR_COURSE          => 'car',
        META_ACCOMODATION_COURSE => 'hotel',
        default                  => null,
    };

    if ($redirect_url) {
        redirect(base_url("index.php/{$redirect_url}/exception?op=booking_exception&notification={$msg}"));
        return;
    }

    show_error("Invalid booking product type.", 400);
	}
	function test_trust(){
		$page_data = array();
		$this->template->view('flight/payment_page', $page_data);

	}
}
