<?php
/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A
 * @version    V2
 */
class Report_Model extends CI_Model
{
    private int $domain_origin;
    private int $entity_user_id;

    public function __construct()
    {
        parent::__construct();

        // Assign these once, since they don't change during a request
        $this->domain_origin = get_domain_auth_id();
        $this->entity_user_id = (int) $GLOBALS['CI']->entity_user_id; // Still using $GLOBALS due to CI2 context
    }

    /**
     * Suggest matching flight booking IDs or PNRs
     */
    public function auto_suggest_flight_booking_id(string $chars, int $limit = 15): array
    {
        $sql = '
            SELECT DISTINCT BD.app_reference
            FROM flight_booking_details AS BD
            JOIN flight_booking_transaction_details AS TD ON BD.app_reference = TD.app_reference
            WHERE BD.domain_origin = ?
              AND BD.created_by_id = ?
              AND BD.app_reference != ""
              AND (
                  BD.app_reference LIKE ? OR
                  TD.pnr LIKE ?
              )
            ORDER BY BD.origin DESC
            LIMIT ?
        ';

        $like = '%' . $chars . '%';
        return $this->db->query($sql, [
            $this->domain_origin,
            $this->entity_user_id,
            $like,
            $like,
            $limit
        ])->result_array();
    }

    /**
     * Suggest matching hotel booking IDs or reference numbers
     */
    public function auto_suggest_hotel_booking_id(string $chars, int $limit = 15): array
    {
        $sql = '
            SELECT DISTINCT BD.app_reference
            FROM hotel_booking_details AS BD
            WHERE BD.domain_origin = ?
              AND BD.created_by_id = ?
              AND BD.app_reference != ""
              AND (
                  BD.app_reference LIKE ? OR
                  BD.confirmation_reference LIKE ? OR
                  BD.booking_reference LIKE ?
              )
            ORDER BY BD.origin DESC
            LIMIT ?
        ';

        $like = '%' . $chars . '%';
        return $this->db->query($sql, [
            $this->domain_origin,
            $this->entity_user_id,
            $like,
            $like,
            $like,
            $limit
        ])->result_array();
    }
}
