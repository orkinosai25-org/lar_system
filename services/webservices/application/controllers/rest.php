<?php

declare(strict_types=1);

defined('BASEPATH') || exit('No direct script access allowed');

class Rest extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('flight_model');
        $this->load->library('currency');
    }
    public function domain_balance(): void
    {
        $post_data = $this->input->post();
        $response = ['status' => INACTIVE];

        $domain_filter = [
            'domain_key' => $post_data['domain_key'],
            'status'     => ACTIVE
        ];

        if ($post_data['system'] === 'test') {
            $domain_filter['test_username'] = $post_data['username'];
            $domain_filter['test_password'] = $post_data['password'];
        } elseif ($post_data['system'] === 'live') {
            $domain_filter['live_username'] = $post_data['username'];
            $domain_filter['live_password'] = $post_data['password'];
        }

        $domain_details = $this->custom_db->multiple_table_cross_records(
            ['domain_list', 'currency_converter'],
            'domain_list.*, currency_converter.country AS currency_name',
            ['currency_converter.id' => 'domain_list.currency_converter_fk'],
            $domain_filter
        );

        if ($domain_details['status'] === SUCCESS_STATUS) {
            $data = $domain_details['data'][0];
            $response['status'] = ACTIVE;
            $response['balance'] = $post_data['system'] === 'test' ? $data['test_balance'] : $data['balance'];
            $response['currency'] = $data['currency_name'];

            if ($post_data['system'] === 'live') {
                $response['credit_limit'] = $data['credit_limit'];
                $response['due_amount'] = $data['due_amount'];
            }
        }

        echo_json($response);
    }

    public function domain_currency(): void
    {
        $post_data = $this->input->post();
        $response = ['status' => INACTIVE];

        $domain_filter = [
            'domain_key' => $post_data['domain_key'],
            'status'     => ACTIVE
        ];

        if ($post_data['system'] === 'test') {
            $domain_filter['test_username'] = $post_data['username'];
            $domain_filter['test_password'] = $post_data['password'];
        } elseif ($post_data['system'] === 'live') {
            $domain_filter['live_username'] = $post_data['username'];
            $domain_filter['live_password'] = $post_data['password'];
        }

        $domain_details = $this->custom_db->multiple_table_cross_records(
            ['domain_list', 'currency_converter'],
            'currency_converter.country AS currency',
            ['currency_converter.id' => 'domain_list.currency_converter_fk'],
            $domain_filter
        );

        if ($domain_details['status'] === SUCCESS_STATUS) {
            $response['status'] = ACTIVE;
            $response['currency'] = $domain_details['data'][0]['currency'];
        }

        echo_json($response);
    }

    public function currecny_value_details(): void
    {
        $post_data = $this->input->get();
        $from = $post_data['from'] ?? '';
        $to_val = $post_data['to'] ?? '';
        $amount = $post_data['amount'] ?? 1;

        $data = ['currency_value' => 1];

        if ($from === $to_val) {
            echo json_encode($data);
            return;
        }

        $from_Currency = urlencode($from);
        $to_Currency = urlencode($to_val);
        $encode_amount = urlencode($amount > 1 ? $amount : 1);

        $currency_data = $this->custom_db->single_table_records('currency_detail', 'value', [
            'f_currency' => $from_Currency,
            't_currency' => $to_Currency
        ]);

        if ($currency_data['status'] === 1) {
            $data['currency_value'] = $currency_data['data'][0]['value'];
            echo json_encode($data);
            return;
        }

        $url = "https://www.xe.com/currencyconverter/convert/?Amount=$encode_amount&From=$from_Currency&To=$to_Currency";
        $output = $this->curl_get_contents($url);

        if ($output !== null) {
            $currency_val = $this->extract_currency_value_from_xe($output, $to_val);
            if ($currency_val !== '') {
                $this->custom_db->insert_record('currency_detail', [
                    'f_currency' => $from_Currency,
                    't_currency' => $to_Currency,
                    'status' => 1,
                    'value' => $currency_val,
                    'date_time' => date('Y-m-d H:i:s')
                ]);
                $data['currency_value'] = $currency_val;
                echo json_encode($data);
                return;
            }
        }

        $fallback_url = "https://free.currencyconverterapi.com/api/v6/convert?q={$from_Currency}_{$to_Currency}&compact=ultra&apiKey=5abb514ae6f02754e3bf";
        $fallback_output = $this->curl_get_contents($fallback_url);

        if ($fallback_output) {
            $json = json_decode($fallback_output);
            $key = "{$from_Currency}_{$to_Currency}";
            $data['currency_value'] = (string)($json->$key ?? 1);
        }

        echo json_encode($data);
    }

    private function curl_get_contents(string $url): ?string
    {
        $ch_value = curl_init();
        curl_setopt_array($ch_value, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_ENCODING => "gzip,deflate"
        ]);
        $output = curl_exec($ch_value);
        $error = curl_error($ch_value);
        curl_close($ch_value);

        return $error === '' ? $output : null;
    }

    private function extract_currency_value_from_xe(string $html, string $to_val): string
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query("//table[contains(@class, 'kwmBWW')]//tbody//tr[1]//td[2]");

        if ($nodes->length > 0) {
            $valueText = trim($nodes->item(0)->nodeValue);
            $valueParts = explode($to_val, $valueText);
            return trim($valueParts[0] ?? '');
        }

        return '';
    }
}
