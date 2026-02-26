<?php

declare(strict_types=1);

/**
 * Provab Common Functionality For API Class
 * @package     Provab
 * @subpackage  provab
 * @category    Libraries
 * @author      Arjun J
 * @link        http://www.provab.com
 */
abstract class Common_Api_Grind
{
    protected string $DomainKey;
    public array $master_search_data = [];
    protected array $config = [];
    protected int|string|null $search_id = null;

    protected string $booking_source = '';
    protected string $booking_source_name = '';

    public function __construct(string $module, string $api)
    {
        $CI = &get_instance();
        $CI->load->model('api_model');

        $configData = $CI->api_model->active_api_config($module, $api);

        if ($configData !== false && !empty($configData['config'])) {
            $this->config = json_decode($configData['config'], true);
            $this->booking_source = $api;
            $this->booking_source_name = $configData['remarks'];
        }
    }

    /**
     * Return master search details.
     */
    public function get_master_search_data(string|false $key = false): mixed
    {
        return $key === false ? $this->master_search_data : ($this->master_search_data[$key] ?? null);
    }

    /**
     * Convert search params to format required by booking source.
     */
    abstract public function search_data(int $search_id): array;

    /**
     * Update markup currency and return summary.
     */
    abstract public function update_markup_currency(array &$price_summary, object &$currency_obj): void;

    /**
     * Calculate and return total price details.
     */
    abstract public function total_price(array $price_summary): array;

    /**
     * Process Booking.
     */
    abstract public function process_booking(array $booking_params, string $app_reference, int $sequence_number, int $search_id): array;

    /**
     * Generate time filter category.
     */
    protected function time_filter_category(string $time_value): int
    {
        $hour = (int) date('H', strtotime($time_value));

        return match (true) {
            $hour < 6 => 1,
            $hour < 12 => 2,
            $hour < 18 => 3,
            default => 4,
        };
    }

    /**
     * Generate stop filter category.
     */
    protected function stop_filter_category(int $stop_count): int
    {
        return match ($stop_count) {
            0 => 1,
            1 => 2,
            default => 3,
        };
    }

    /**
     * Returns default price object.
     */
    public function get_price_object(): array
    {
        return [
            'Currency' => false,
            'TotalDisplayFare' => 0,
            'PriceBreakup' => [
                'BasicFare' => 0,
                'Tax' => 0,
                'AgentCommission' => 0,
                'AgentTdsOnCommision' => 0
            ]
        ];
    }
}
