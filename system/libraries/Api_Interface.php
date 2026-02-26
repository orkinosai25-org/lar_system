<?php

ini_set('memory_limit', '-1');

/**
 * Provab XML Class
 *
 * Handle XML Details
 *
 * @package	Provab
 * @subpackage	provab
 * @category	Libraries
 * @author		Balu A<balu.provab@gmail.com>
 * @link		http://www.provab.com
 */
class Api_Interface {

    /**
     *
     * @param array $query_details - array having details of query
     */
    public function __construct() {
        
    }

    /**
     * Get Domain Balance for Admin
     */
    function rest_service(string $method, array $params = []): string|false
    {
        $CI = &get_instance();
        $system = $CI->external_service_system;

        $user_name_key = $system . '_username';
        $password_key = $system . '_password';

        $username = $CI->$user_name_key ?? '';
        $password = $CI->$password_key ?? '';

        $params = array('domain_key' => get_domain_key(), 'username' => $username, 'password' => $password, 'system' => $system);
        $params['domain_id'] = @$CI->entity_domain_id;
        $url = $CI->external_service;
        $ch = curl_init($url . $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
    /**
     * get response from server for the request
     *
     * @param $request 	   request which has to be processed
     * @param $url	   	   url to which the request has to be sent
     * @param $soap_action
     *
     * @return xml response
     */
    public function get_json_response(string $url, string $request, array $header_details = []): array|null
    {
        // Ensure all required header fields are present
        $username = $header_details['UserName'] ?? '';
        $domainKey = $header_details['DomainKey'] ?? '';
        $system = $header_details['system'] ?? '';
        $password = $header_details['Password'] ?? '';
    
        $header = [
            'Content-Type: application/json',
            'Accept-Encoding: gzip, deflate',
            'x-Username: ' . $username,
            'x-DomainKey: ' . $domainKey,
            'x-system: ' . $system,
            'x-Password: ' . $password
        ];
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
        $res = curl_exec($ch);
        curl_close($ch);
    
        if ($res == false) {
            return null; // Or consider throwing an exception
        }
    
        return json_decode($res, true);
    }
    public function get_json_image_response(
        string $url,
        array $json_data = [],
        array $header_details = [],
        string $method = ''
    ): array|false
    {
        $header = [
            'api-key: 07b9b13ecc82ace91324aa816496339d',
            'Content-Type: application/json',
            'Accept: application/json'
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    
        if (strtolower($method) == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        } elseif (strtolower($method) == 'delete') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        }
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        $response = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);
    
        if ($headers['http_code'] != 200 || $response == false) {
            return false;
        }
    
        return json_decode($response, true);
    }

    /**
     * get response from server for the request
     *
     * @param $request 	   request which has to be processed
     * @param $url	   	   url to which the request has to be sent
     * @param $soap_action
     *
     * @return xml response
     */
    public function debug_get_json_response(
        string $url,
        array $request = [],
        array $header_details = []
    ): array|null
    {
        // Safe defaults for header values
        $username = $header_details['UserName'] ?? '';
        $domainKey = $header_details['DomainKey'] ?? '';
        $system = $header_details['system'] ?? '';
        $password = $header_details['Password'] ?? '';
    
        $header = [
            'Content-Type: application/json',
            'Accept-Encoding: gzip, deflate',
            'x-Username: ' . $username,
            'x-DomainKey: ' . $domainKey,
            'x-system: ' . $system,
            'x-Password: ' . $password
        ];
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $res = curl_exec($ch);
        curl_close($ch);
    
        return $res != false ? json_decode($res, true) : null;
    }

    function get_json_insurance(string $method, string $url, array|false $data = false): string|false
    {
        $curl = curl_init();
    
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            default:
                if ($data) {
                    $url = sprintf('%s?%s', $url, http_build_query($data));
                }
                break;
        }
    
        // Optional Authentication
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, 'username:password');
    
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
        $response = curl_exec($curl);
        curl_close($curl);
    
        return $response;
    }

    /**
     * Get xml response from URL for the request
     * @param string $url
     * @param xml	 $request
     */
    public function get_xml_response(string $url, string $request, bool $convert_to_array = true): array|string|false
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: text/xml',
            'Accept-Encoding: gzip, deflate'
        ]);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    
        $xml = curl_exec($ch);
        curl_close($ch);
    
        if ($xml == false) {
            return false;
        }
    
        if ($convert_to_array) {
            return Converter::createArray($xml);
        }
    
        return $xml;
    }

    public function objectToArray(mixed $d): mixed
    {
        if (is_object($d)) {
            $d = get_object_vars($d);
        }
    
        if (is_array($d)) {
            return array_map([$this, 'objectToArray'], $d);
        }
    
        return $d;
    }

    public function get_object_response(string $request_type, mixed $request, array $header_details): mixed
    {
        $header = $header_details['header'];
        $credintials = $header_details['credintials'];
    
        $_header[] = new SoapHeader("http://provab.com/soap/", 'AuthenticationData', $header, "");
    
        $client = new SoapClient(null, [
            'location'   => $credintials['URL'],
            'uri'        => 'http://provab.com/soap/',
            'trace'      => 1,
            'exceptions' => 0
        ]);
    
        try {
            $result = $client->provab_api($request_type, $request, $_header);
        } catch (Exception $err) {
            echo "<pre>";
            print_r($err->getMessage());
            return null;
        }
    
        return $result;
    }

}
 
