<?php

declare(strict_types=1);

require_once 'abstract_management_model.php';

/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A
 * @version    V2
 */
class Transaction_Model extends CI_Model
{
    /**
     * Fetch or count transaction logs based on condition
     *
     * @param array $condition
     * @param bool $count
     * @param int $offset
     * @param int $limit
     * @return int|array
     */
    public function logs(array $condition = [], bool $count = false, int $offset = 0, int $limit = PHP_INT_MAX): int|array
    {
        $customCondition = $this->custom_db->get_custom_condition($condition);
        $userId = $GLOBALS['CI']->entity_user_id;
        $domainId = get_domain_auth_id();

        if (is_domain_user()) {
            if ($count) {
                $query = "SELECT COUNT(*) AS total_records 
                          FROM transaction_log TL 
                          WHERE TL.domain_origin = {$domainId} 
                          AND TL.transaction_owner_id = {$userId} 
                          {$customCondition}";
                $data = $this->db->query($query)->row_array();
                return (int) ($data['total_records'] ?? 0);
            }

            $query = "SELECT 'INR' AS currency,
                             TL.system_transaction_id,
                             TL.transaction_type,
                             TL.domain_origin,
                             TL.app_reference,
                             TL.fare,
                             TL.domain_markup AS admin_markup,
                             TL.domain_markup AS profit,
                             TL.level_one_markup AS agent_markup,
                             TL.convinence_fees AS convinence_amount,
                             TL.promocode_discount AS discount,
                             TL.remarks,
                             TL.created_datetime,
                             TL.transaction_owner_id,
                             CONCAT(U.first_name, ' ', U.last_name) AS username,
                             agency_name AS agent_name
                      FROM transaction_log TL
                      LEFT JOIN user U ON TL.transaction_owner_id = U.user_id
                      WHERE TL.domain_origin = {$domainId}
                      AND TL.transaction_owner_id = {$userId}
                      {$customCondition}
                      ORDER BY TL.origin DESC
                      LIMIT {$offset}, {$limit}";

            return $this->db->query($query)->result_array();
        }

        // Non-domain user logic
        if ($count) {
            $query = "SELECT COUNT(*) AS total_records 
                      FROM transaction_log TL 
                      WHERE TL.transaction_owner_id = {$userId} 
                      {$customCondition}";
            $data = $this->db->query($query)->row_array();
            return (int) ($data['total_records'] ?? 0);
        }

        $query = "SELECT TL.system_transaction_id,
                         TL.transaction_type,
                         TL.domain_origin,
                         TL.app_reference,
                         TL.fare,
                         TL.domain_markup AS admin_markup,
                         TL.domain_markup AS profit,
                         TL.level_one_markup AS agent_markup,
                         TL.convinence_fees AS convinence_amount,
                         TL.promocode_discount AS discount,
                         TL.remarks,
                         TL.created_datetime,
                         CONCAT(U.first_name, ' ', U.last_name) AS username,
                         agency_name AS agent_name
                  FROM transaction_log TL
                  LEFT JOIN user U ON TL.transaction_owner_id = U.user_id
                  WHERE TL.transaction_owner_id = {$userId}
                  {$customCondition}
                  ORDER BY TL.origin DESC
                  LIMIT {$offset}, {$limit}";

        return $this->db->query($query)->result_array();
    }
}
