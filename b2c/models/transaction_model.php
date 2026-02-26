<?php
require_once 'abstract_management_model.php';

/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu
 * @version    V2
 */
class Transaction_Model extends CI_Model
{
	// Assuming these are set somewhere before usage
	//public int $entity_user_id;

	/**
	 * Fetch transaction logs
	 *
	 * @param array $condition
	 * @param bool $count
	 * @param int $offset
	 * @param int $limit
	 * @return int|array
	 */
	public function logs(int $count = 0, int $offset = 0, int $limit = 1000000000): int|array
	{
		$domain_origin = get_domain_auth_id();

		if ($count > 0) {
			$sql = 'SELECT COUNT(*) AS total_records 
					FROM transaction_log 
					WHERE domain_origin = ? AND created_by_id = ?';

			$data = $this->db->query($sql, [$domain_origin, $this->entity_user_id])->row_array();
			return (int) ($data['total_records'] ?? 0);
		} 

		if($count == 0) {
			$sql = 'SELECT 
						TL.currency AS currency,
						TL.system_transaction_id,
						TL.transaction_type,
						TL.domain_origin,
						TL.app_reference,
						TL.fare,
						TL.domain_markup AS admin_markup,
						TL.level_one_markup AS agent_markup,
						TL.convinence_fees AS convinence_amount,
						TL.promocode_discount AS discount,
						TL.remarks,
						TL.created_datetime,
						CONCAT(U.first_name, " ", U.last_name) AS username
					FROM transaction_log TL
					LEFT JOIN user U ON TL.created_by_id = U.user_id
					WHERE TL.domain_origin = ? AND TL.created_by_id = ?
					ORDER BY TL.origin DESC
					LIMIT ?, ?';

			$query = $this->db->query($sql, [
				$domain_origin,
				$this->entity_user_id,
				$offset,
				$limit
			]);

			return $query->result_array();
		}
	}
}
