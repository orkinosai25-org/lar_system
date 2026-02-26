<?php
require_once 'abstract_management_model.php';
/**
 * @package    current domain Application
 * @subpackage Travel Portal
 * @author     Arjun J<arjunjgowda260389@gmail.com>
 * @version    V2
 */
class Domain_Management_Model extends Abstract_Management_Model
{

    private $airline_markup;
    private $hotel_markup;
    private $bus_markup;
    var $verify_domain_balance;

    function __construct()
    {
        parent::__construct('level_2');
        $this->verify_domain_balance = $this->config->item('verify_domain_balance');
    }
    /**
     * Get markup based on different modules
     *
     * 
     * @return array
     */
    function get_markup(string $module_name): array
    {
        $markup_data = '';
        switch ($module_name) {
            case 'b2c_flight':
                $markup_data = $this->airline_markup();
                break;
            case 'b2c_hotel':
                $markup_data = $this->hotel_markup();
                break;
            case 'b2c_bus':
                $markup_data = $this->bus_markup();
                break;
        }
        return $markup_data;
    }
    /**
     * Get airline markup (specific + generic)
     *
     * @return array
     */
    function airline_markup(): array
    {
        if (empty($this->airline_markup)) {
            $response = [
                'specific_markup_list' => $this->specific_airline_markup('b2c_flight'),
                'generic_markup_list'  => $this->generic_domain_markup('b2c_flight')
            ];
            $this->airline_markup = $response;
        } else {
            $response = $this->airline_markup;
        }
        return $response;
    }
    /**
     * Arjun J Gowda
     * Manage domain markup for current domain - Domain wise and module wise
     */
    function hotel_markup(): array
    {
        if (empty($this->hotel_markup) == true) {
            $response['specific_markup_list'] = '';
            $response['generic_markup_list'] = $this->generic_domain_markup('b2c_hotel');
            $this->hotel_markup = $response;
        } else {
            $response = $this->hotel_markup;
        }
        return $response;
    }
    /**
     * Arjun J Gowda
     * Manage domain markup for current domain - Domain wise and module wise
     */
    function bus_markup(): array
    {
        if (empty($this->bus_markup) == true) {
            $response['specific_markup_list'] = '';
            $response['generic_markup_list'] = $this->generic_domain_markup('b2c_bus');
            $this->bus_markup = $response;
        } else {
            $response = $this->bus_markup;
        }
        return $response;
    }
    /**
     * Arjun J Gowda
     * Get generic markup based on the module type
     * @param $module_type
     * @param $markup_level
     */
    function generic_domain_markup($module_type): array
    {
        $query = 'SELECT ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type, ML.markup_currency AS markup_currency
        FROM markup_list AS ML where ML.value != "" and ML.module_type = "' . $module_type . '" and
        ML.markup_level = "' . $this->markup_level . '" and ML.type="generic" and ML.domain_list_fk=' . get_domain_auth_id();
        $generic_data_list = $this->db->query($query)->result_array();
        return $generic_data_list;
    }
    /**
     * Arjun J Gowda
     * Get specific markup based on module type
     * @param string $module_type Name of the module for which the markup has to be returned
     * @param string $markup_level Level of markup
     */
    function specific_airline_markup($module_type): array
    {
        $markup_list = [];
        $query = 'SELECT AL.origin AS airline_origin, AL.name AS airline_name, AL.code AS airline_code,
        ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type, ML.markup_currency AS markup_currency
        FROM airline_list AS AL JOIN markup_list AS ML where ML.value != "" and
        ML.module_type = "' . $module_type . '" and ML.markup_level = "' . $this->markup_level . '" and AL.origin=ML.reference_id and ML.type="specific"
        and ML.domain_list_fk != 0  and ML.domain_list_fk=' . get_domain_auth_id() . ' order by AL.name ASC';
        $specific_data_list = $this->db->query($query)->result_array();
        if (valid_array($specific_data_list)) {
            foreach ($specific_data_list as $__k => $__v) {
                $markup_list[$__v['airline_code']] = $__v;
            }
        }
        return $markup_list;
    }
    /**
     * Check if the Booking Amount is allowed on Client Domain
     */
    function verify_current_balance(float $amount, string $currency): string
    {
        $status = FAILURE_STATUS;
        if ($this->verify_domain_balance == true) {
            if ($amount > 0) {
                $query = 'SELECT DL.balance, CC.country as currency, CC.value as conversion_value from domain_list as DL, currency_converter AS CC where CC.id=DL.currency_converter_fk
            AND DL.status=' . ACTIVE . ' and DL.origin=' . $this->db->escape(get_domain_auth_id()) . ' and DL.domain_key = ' . $this->db->escape(get_domain_key());
                $balance_record = $this->db->query($query)->row_array();
                if ($currency == $balance_record['currency']) {
                    $balance = $balance_record['balance'];
                    if ($balance >= $amount) {
                        $status = SUCCESS_STATUS;
                    } else {
                        //Notify User about current balance problem
                        //FIXME - send email, notification for less balance to domain admin and current domain admin
                        $this->application_logger->balance_status('Your Balance Is Very Low To Make Booking Of ' . $amount . ' ' . $currency);
                    }
                } else {
                    echo 'Under Construction';
                    exit;
                }
            }
        } else {
            $status = SUCCESS_STATUS;
        }
        return $status;
    }
    /**
     * Update transaction details
     *
     * @param string $transaction_type Type of the transaction (e.g., 'booking', 'refund')
     * @param string $app_reference Application reference ID
     * @param float $fare Fare amount
     * @param float $domain_markup Domain-specific markup
     * @param float $level_one_markup Level one markup (default is 0)
     * @param string $currency Currency used in the transaction
     * @param float $currency_conversion_rate Conversion rate for currency (default is 1)
     * @return string The status of the transaction (e.g., SUCCESS_STATUS or FAILURE_STATUS)
     */
    function update_transaction_details(
        string $transaction_type,
        string $app_reference,
        float $fare,
        float $domain_markup,
        float $level_one_markup = 0,
        string $currency = '',
        float $currency_conversion_rate = 1
    ): string {
        // Set default currency if empty
        $currency = empty($currency) ? get_application_default_currency() : $currency;

        // Initialize status as FAILURE
        $status = FAILURE_STATUS;

        if ($this->verify_domain_balance) {
            $amount = floatval($fare + $level_one_markup);

            if ($amount > 0) {
                // Deduct balance
                $this->private_management_model->update_domain_balance(get_domain_auth_id(), -$amount);

                // Log the transaction details
                $remarks = $transaction_type . ' Transaction was Successfully done';
                $this->save_transaction_details($transaction_type, $app_reference, $fare, $domain_markup, $level_one_markup, $remarks, $currency, $currency_conversion_rate);

                // Log the transaction status
                $this->application_logger->transaction_status($remarks . '(' . $amount . ')', [
                    'app_reference' => $app_reference,
                    'type' => $transaction_type
                ]);
            }
        } else {
            // If domain balance verification is off, mark the transaction as successful
            $status = SUCCESS_STATUS;
        }

        return $status;
    }
    /**
     * Save transaction logging for security purposes
     *
     * @param string $transaction_type Type of the transaction (e.g., 'booking', 'refund')
     * @param string $app_reference Application reference ID
     * @param float $fare Fare amount
     * @param float $domain_markup Domain-specific markup
     * @param float $level_one_markup Level one markup
     * @param string $remarks Remarks for the transaction
     * @param string $currency Currency used for the transaction (default is 'INR')
     * @param float $currency_conversion_rate Conversion rate for currency (default is 1)
     * @return void
     */
    /**
     * Save transaction logging for security purposes
     * 
     * @param string $transaction_type Type of the transaction (e.g., 'booking', 'refund')
     * @param string $app_reference Application reference ID
     * @param float $fare Fare amount
     * @param float $domain_markup Domain-specific markup
     * @param float $level_one_markup Level one markup
     * @param string $remarks Remarks for the transaction
     * @param string $currency Currency used for the transaction (default is 'INR')
     * @param float $currency_conversion_rate Conversion rate for currency (default is 1)
     * @return void
     */
    function save_transaction_details(
        string $transaction_type,
        string $app_reference,
        float $fare,
        float $domain_markup,
        float $level_one_markup,
        string $remarks,
        string $currency = 'INR',
        float $currency_conversion_rate = 1.0
    ): void {
        $domain_origin=get_domain_auth_id();
        if(intval($domain_origin) > 0){
              $domain_details = $this->get_domain_details($domain_origin);
              $domain_base_currency = $domain_details['domain_base_currency'];
          } else {
                $domain_base_currency = domain_base_currency();
          }
        // Initialize the currency conversion object
        $currency_obj = new Currency([
            'from' => get_application_default_currency(),
            'to' => $domain_base_currency
        ]);
        // Converting Fare
        $fare_converted_value = $currency_obj->force_currency_conversion($fare);
        $fare = $fare_converted_value['default_value'];

        // Converting Domain Markup
        $domain_markup_converted_value = $currency_obj->force_currency_conversion($domain_markup);
        $domain_markup = $domain_markup_converted_value['default_value'];

        // Converting Level One Markup
        $level_one_markup_converted_value = $currency_obj->force_currency_conversion($level_one_markup);
        $level_one_markup = $level_one_markup_converted_value['default_value'];
        

        // Log the transaction details
        $transaction_log = [
            'system_transaction_id' => date('Ymd-His') . '-S-' . rand(1, 10000),
            'transaction_type' => $transaction_type,
            'domain_origin' => get_domain_auth_id(),
            'app_reference' => $app_reference,
            'fare' => $fare,
            'level_one_markup' => $level_one_markup,
            'domain_markup' => $domain_markup,
            'remarks' => $remarks,
            'created_by_id' => intval(@$this->entity_user_ids),
            'created_datetime' => date('Y-m-d H:i:s', time()),
            'currency' => $currency,
            'currency_conversion_rate' => $currency_conversion_rate
        ];

        // Calculate the total transaction amount (fare + markups)
        $total_transaction_amount = ($fare + $level_one_markup + $domain_markup);

        // Get opening and closing balance details
        $opening_closing_balance_details = $this->get_opening_closing_balance(get_domain_auth_id(), $total_transaction_amount);

        // Prepare data for logging or testing purposes
        $temp_test_data = $opening_closing_balance_details;
        $temp_test_data['total_transaction_amount'] = $total_transaction_amount;
        $temp_test_data['fare_break'] = 'DomainID-' . get_domain_auth_id() . ' F: ' . $fare . ' L: ' . $level_one_markup . ' D: ' . $domain_markup;

        // Insert test data for debugging purposes (if needed)
        $this->custom_db->insert_record('test', ['test' => json_encode($temp_test_data)]);

        // Adding balance details to the transaction log
        $transaction_log['opening_balance'] = $opening_closing_balance_details['opening_balance'];
        $transaction_log['closing_balance'] = $opening_closing_balance_details['closing_balance'];
        $transaction_log['domain_balance'] = $opening_closing_balance_details['domain_balance'];

        // Insert the transaction log into the database
        $this->custom_db->insert_record('transaction_log', $transaction_log);
    }

    /**
     * Get Opening and Closing Balance Details for a given domain and total transaction amount.
     *
     * @param int $domain_origin The domain's origin ID
     * @param float $total_transaction_amount The total transaction amount to calculate the closing balance
     * @return array An associative array containing the opening balance, closing balance, and domain balance
     */
    function get_opening_closing_balance(int $domain_origin, float $total_transaction_amount): array
    {
        // Ensure the total transaction amount is positive
        $total_transaction_amount = abs($total_transaction_amount);

        // Query to fetch the latest balance and transaction details
        $query = "SELECT DL.domain_name, DL.balance, DL.due_amount, TL.opening_balance, TL.closing_balance
              FROM transaction_log TL
              JOIN domain_list DL ON DL.origin = TL.domain_origin
              WHERE DL.origin =".$domain_origin. " ORDER BY TL.origin DESC LIMIT 1";

        // Prepare the query with parameter binding to prevent SQL injection
        $current_balance_details = $this->db->query($query)->row_array();

        // Default values if no result is found (handles possible null values from query)
        if (!$current_balance_details) {
            return [
                'opening_balance' => 0,
                'closing_balance' => 0,
                'domain_balance' => 0
            ];
        }

        // Get the opening balance from the previous transaction
        $opening_balance = $current_balance_details['closing_balance'];

        // Adjust the total transaction amount
        $total_transaction_amount = $total_transaction_amount < 0 ? $total_transaction_amount : -$total_transaction_amount;

        // Calculate the closing balance
        $closing_balance = $opening_balance + $total_transaction_amount;

        // Calculate the domain balance (balance + due_amount)
        $domain_balance = $current_balance_details['balance'] + $current_balance_details['due_amount'];

        // Return the calculated balances in an associative array
        return [
            'opening_balance' => round(floatval($opening_balance), 2),
            'closing_balance' => round(floatval($closing_balance), 2),
            'domain_balance' => round(floatval($domain_balance), 2)
        ];
    }
    /**
     * Log access details to the track log table.
     * 
     * @param string $app_reference The application reference
     * @param string $comment An optional comment (default is an empty string)
     * @return void
     */
    function create_track_log(string $app_reference, string $comment = ''): void
    {
        // Prepare the track log data
        $track_log = [
            'app_reference'   => $app_reference,
            'domain_origin'    => get_domain_auth_id(),
            'http_host'        => $_SERVER['HTTP_HOST'] ?? 'unknown',
            'remote_address'   => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'browser'          => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_url'      => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'request_method'   => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'server_ip'        => $_SERVER['SERVER_ADDR'] ?? 'unknown',
            'server_name'      => $_SERVER['SERVER_NAME'] ?? 'unknown',
            'comments'         => $this->db->escape($comment),
            'created_datetime' => date('Y-m-d H:i:s'),
            'attr'             => serialize($_SERVER)
        ];

        // Insert the log into the 'track_log' table
        $this->custom_db->insert_record('track_log', $track_log);
    }
    /**
     * Logs booking amount details into the database.
     *
     * @param string $transaction_type The type of the transaction.
     * @param string $app_reference The reference ID of the application.
     * @param float $transaction_amount The amount of the transaction.
     * @param string $currency The currency of the transaction (default is 'INR').
     * @param float $currency_conversion_rate The conversion rate for the currency (default is 1).
     * @return void
     */
    public function booking_amount_logger(
        string $transaction_type,
        string $app_reference,
        float $transaction_amount,
        string $currency = 'INR',
        float $currency_conversion_rate = 1
    ): void {
        // Prepare the log data for the booking amount
        $booking_amount_logger = [
            'transaction_type'        => $transaction_type,
            'domain_origin'           => get_domain_auth_id(),
            'app_reference'           => $app_reference,
            'transaction_amount'      => $transaction_amount,
            'remarks'                 => '', // Can be populated if necessary
            'created_by_id'           => intval($this->entity_user_id ?? 0), // Handling possible undefined user ID
            'created_datetime'        => date('Y-m-d H:i:s'),
            'currency'                => $currency,
            'currency_conversion_rate' => $currency_conversion_rate
        ];

        // Insert the log into the database
        $this->custom_db->insert_record('booking_amount_logger', $booking_amount_logger);
    }
    /**
     * Returns the Domain Currency Conversion Rate.
     *
     * @param string $currency The currency to get the conversion rate for (e.g., USD, INR).
     * @return array The conversion rate details.
     */
    public function get_currency_conversion_rate(string $currency): array
    {
        // Sanitize currency input
        $currency = trim($currency);

        // Query for domain-specific currency conversion rate
        $query = 'SELECT currency_value AS conversion_rate FROM domain_currency_value WHERE currency = ? AND domain_origin = ?';
        $details = $this->db->query($query, [$currency, get_domain_auth_id()])->row_array();

        // If details found and conversion_rate is valid, return it
        if (!empty($details) && isset($details['conversion_rate']) && $details['conversion_rate'] > 0) {
            return $details;
        } else {
            // Fallback query if no domain-specific conversion rate is found
            $query = 'SELECT value AS conversion_rate FROM domain_currency_converter WHERE country = ?';
            return $this->db->query($query, [$currency])->row_array();
        }
    }
    /**
     * Elavarasi
     * Return USD to INR updated currency
     */
    public function get_viator_currency_conversion_rate(): array
    {
        $query = "SELECT * FROM viator_api_currency_converter WHERE origin = 1";
        return $this->db->query($query)->row_array() ?? [];
    }

    /**
     * Returns Domain Currency Conversion Rate for Sabre
     * @param string $currency The currency (e.g., USD, INR).
     * @return array The conversion rate details.
     */
    public function get_currency_conversion_rate_sabre(string $currency): array
    {
        // Sanitize currency input
        $currency = trim($currency);

        // Query for domain-specific currency conversion rate
        $query = 'SELECT currency_value AS conversion_rate FROM domain_currency_value WHERE currency = ? AND domain_origin = 31';
        $details = $this->db->query($query, [$currency])->row_array();

        // If details found and conversion_rate is valid, return it
        if (!empty($details) && isset($details['conversion_rate']) && $details['conversion_rate'] > 0) {
            return $details;
        } else {
            // Fallback query if no domain-specific conversion rate is found
            $query = 'SELECT value AS conversion_rate FROM domain_currency_converter WHERE country = ?';
            return $this->db->query($query, [$currency])->row_array() ?? [];
        }
    }
    /**
     * Returns Domain details based on Domain origin
     * @param int $domain_origin The origin of the domain (ID).
     * @return array The details of the domain.
     */
    public function get_domain_details(int $domain_origin): array
    {
        // Use parameterized query to avoid SQL injection
        $query = 'SELECT DL.*, CC.country AS domain_base_currency 
              FROM domain_list DL
              JOIN currency_converter CC ON CC.id = DL.currency_converter_fk
              WHERE DL.origin = ?';

        // Execute the query with parameter binding
        return $this->db->query($query, [$domain_origin])->row_array() ?? [];
    }
}
