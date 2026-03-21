<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function create_pay_record(string $app_reference, float $booking_fare, string $firstname, string $email, string $phone, string $productinfo, float $conversion_rate, float $convenience_fees = 0, float $promocode_discount = 0): bool {
        $duplicate_pg = $this->read_pay_record($app_reference);
        if ($duplicate_pg) {
            return false;
        }

        $payment_currency = $this->config->item('payment_gateway_currency');

        $request_params = [
            'txnid'               => $app_reference,
            'booking_fare'        => $booking_fare,
            'convenience_amount'  => $convenience_fees,
            'promocode_discount'  => $promocode_discount,
            'firstname'           => $firstname,
            'email'               => $email,
            'phone'               => $phone,
            'productinfo'         => $productinfo,
        ];

        $data = [
            'amount'                  => roundoff_number($booking_fare + $convenience_fees - $promocode_discount),
            'domain_origin'           => get_domain_auth_id(),
            'app_reference'           => $app_reference,
            'request_params'          => json_encode($request_params),
            'currency'                => $payment_currency,
            'currency_conversion_rate'=> $conversion_rate
        ];

        $this->custom_db->insert_record('payment_gateway_details', $data);
        return true;
    }

    public function read_pay_record(string $app_reference): mixed
    {
        $cond = ['app_reference' => $app_reference];
        $data = $this->custom_db->single_table_records('payment_gateway_details', '*', $cond);

        return ($data['status'] === SUCCESS_STATUS && !empty($data['data'][0]))
            ? $data['data'][0]
            : false;
    }

    public function update_pay_record(string $app_reference, string $status, array $response_params = []): void
    {
        $cond = ['app_reference' => $app_reference];
        $data = ['status' => $status];

        if (!empty($response_params)) {
            $data['response_params'] = json_encode($response_params);
        }

        $this->custom_db->update_record('payment_gateway_details', $data, $cond);
    }
}
