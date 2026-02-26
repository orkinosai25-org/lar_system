<?php

declare(strict_types=1);

/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A <balu.provab@gmail.com>
 * @version    V2
 */

abstract class Abstract_Management_Model extends CI_Model
{
    protected string $markup_level;

    public function __construct(string $markup_level)
    {
        parent::__construct();
        $this->markup_level = $markup_level;
    }

    /**
     * Save markup data (generic or specific).
     *
     * @param int $markup_origin
     * @param string $type         'generic' or 'specific'
     * @param string $module_type  e.g., 'b2c_hotel', 'b2c_flight'
     * @param int $reference_id
     * @param float $value
     * @param string $value_type   'percentage' or 'plus'
     * @param int $domain_origin
     * @return void
     */
    public function save_markup_data(
        int $markup_origin,
        string $type,
        string $module_type,
        int $reference_id,
        float $value,
        string $value_type,
        int $domain_origin
    ): void {
        $markup_data = [
            'origin'           => $markup_origin,
            'markup_level'     => $this->markup_level,
            'type'             => strtolower($type),
            'module_type'      => strtolower($module_type),
            'reference_id'     => $reference_id,
            'value'            => $value,
            'value_type'       => strtolower($value_type),
            'domain_list_fk'   => $domain_origin,
            'user_oid'         => $this->entity_user_id ?? 0,
            'markup_currency'  => get_application_currency_preference(),
        ];

        if (!empty($markup_data['type']) && !empty($markup_data['value_type'])) {
            if ($markup_origin > 0) {
                // Update existing record
                $this->custom_db->update_record('markup_list', $markup_data, ['origin' => $markup_origin]);
                return;
            }

            // Insert new record
            $this->custom_db->insert_record('markup_list', $markup_data);
        }
    }
}
