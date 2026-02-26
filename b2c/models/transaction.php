<?php
declare(strict_types=1);

/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Flight Model
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2 - PHP 8.2 Compatible
 */
class Transaction extends CI_Model
{
    /**
     * Lock All the tables necessary for flight transaction to be processed
     */
    public static function lock_tables(): void
    {
        echo 'Under Construction';
    }

    /**
     * Create Payment record for payment gateway used in the application
     */
    public function create_payment_record(string $app_reference, float $booking_fare,string $firstname, string $email, string $phone, string $productinfo, float $convenience_fees = 0, float $promocode_discount = 0, float $currency_rate = 0.00): bool {
        $duplicate_pg = $this->read_payment_record($app_reference);
        if ($duplicate_pg === false) {
            $payment_currency = $this->config->item('payment_gateway_currency');

            $request_params = [
                'txnid' => $app_reference,
                'booking_fare' => $booking_fare,
                'convenience_amount' => $convenience_fees,
                'promocode_discount' => $promocode_discount,
                'firstname' => $firstname,
                'email' => $email,
                'phone' => $phone,
                'productinfo' => $productinfo
            ];

            $data = [
                'amount' => roundoff_number($booking_fare + $convenience_fees - $promocode_discount),
                'domain_origin' => get_domain_auth_id(),
                'app_reference' => $app_reference,
                'request_params' => json_encode($request_params, JSON_THROW_ON_ERROR),
                'currency' => $payment_currency,
                'currency_conversion_rate' => $currency_rate,
                'created_datetime' => date('Y-m-d H:i:s')
            ];

            $this->custom_db->insert_record('payment_gateway_details', $data);
            return true;
        }

        return false;
    }

    /**
     * Read Payment record with payment gateway reference
     */
    public function read_payment_record(string $app_reference): array|false
    {
        $cond = ['app_reference' => $app_reference];
        $data = $this->custom_db->single_table_records('payment_gateway_details', '*', $cond);

        return ($data['status'] === SUCCESS_STATUS && !empty($data['data']))
            ? $data['data'][0]
            : false;
    }

    /**
     * Update Payment record with payment gateway reference
     */
    public function update_payment_record_status(string $app_reference, string $status, array $response_params = []): void
    {
        $cond = ['app_reference' => $app_reference];
        $data = ['status' => $status];

        if (valid_array($response_params)) {
            $data['response_params'] = json_encode($response_params, JSON_THROW_ON_ERROR);
        }

        $this->custom_db->update_record('payment_gateway_details', $data, $cond);
    }

    /**
     * Update additional details of transaction
     */
    public function update_convinence_discount_details(
        string $book_detail_table,
        string $app_reference,
        float $discount = 0,
        string $promo_code = '',
        float $convinence = 0,
        float $convinence_value = 0,
        string $convinence_type = '',
        int $convinence_per_pax = 0,
        float $gst = 0
    ): void {
        $data = [
            'discount' => $discount,
            'promo_code' => $promo_code,
        ];

        if (!empty($convinence)) {
            $data['convinence_amount'] = $convinence;
        }
        if (!empty($convinence_value)) {
            $data['convinence_value'] = $convinence_value;
        }
        if (!empty($convinence_type)) {
            $data['convinence_value_type'] = $convinence_type;
        }
        if (!empty($convinence_per_pax)) {
            $data['convinence_per_pax'] = $convinence_per_pax;
        }
        if (!empty($gst)) {
            $data['gst'] = $gst;
        }

        $cond = ['app_reference' => $app_reference];
        $this->custom_db->update_record($book_detail_table, $data, $cond);
    }

    /**
     * Returns the Payment Status
     */
    public function get_payment_status(string $app_reference): array
    {
        $this->db->select('status');
        $this->db->where('app_reference =', trim($app_reference));
        $this->db->from('payment_gateway_details');
        return $this->db->get()->row_array();
    }

    /**
     * Unlock All The Tables
     */
    public static function release_locked_tables(): void
    {
        $CIInstance = &get_instance();
        $CIInstance->db->query('UNLOCK TABLES');
    }
}
