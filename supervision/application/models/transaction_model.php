<?php
require_once 'abstract_management_model.php';
/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */
class Transaction_Model extends CI_Model
{
	/**
	 * Return Convenience Fees
	 */
	public function get_convenience_fees(): array
	{
		$data = $this->custom_db->single_table_records('convenience_fees');
		return $data['data'];
	}
	/**
	 * Update Convinence Fees
	 * @param $data array having list of data to be updated
	 */
	public function update_convenience_fees(array $data): void
	{
        $cond=[];
        $row=[];
		foreach ($data['origin'] as $k => $v) {
			if (intval($v) > 0) {
				$cond['origin'] = intval($v);
			} else {
				continue;
			}
			$row['value'] = $data['value'][$k];
			$row['value_type'] = $data['value_type_' . $v];
			$row['per_pax'] = $data['per_pax_' . $v];
			$row['convenience_fee_currency'] = get_application_default_currency();
			$this->custom_db->update_record('convenience_fees', $row, $cond);
		}
	}
	/**
	 *
	 */
	public function logs(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array|string
	{

		$condition = $this->custom_db->get_custom_condition($condition);
		//BT, CD, ID
		if (is_domain_user()) {
			if ($count) {
				$query = 'select count(*) as total_records from transaction_log TL LEFT JOIN user U ON TL.transaction_owner_id = U.user_id where domain_origin = ' . get_domain_auth_id() . ' ' . $condition;
				$data = $this->db->query($query)->row_array();
				return $data['total_records'];
			} else {
				$query = 'select "USD" as currency, TL.system_transaction_id,TL.transaction_type,TL.domain_origin,TL.app_reference,
				TL.fare,TL.domain_markup as admin_markup,TL.domain_markup as profit,TL.level_one_markup as agent_markup,TL.convinence_fees as convinence_amount,TL.promocode_discount as discount,
				TL.remarks,TL.created_datetime,TL.transaction_owner_id, concat(U.first_name, " ", U.last_name) as username, agency_name as agent_name  
				from transaction_log TL LEFT JOIN user U ON TL.transaction_owner_id=U.user_id where TL.domain_origin=' . get_domain_auth_id() . ' ' . $condition . ' 
				order by TL.origin desc limit ' . $offset . ', ' . $limit;
				return $this->db->query($query)->result_array();
			}
		} else {
			if ($count) {
				$query = 'select count(*) as total_records from transaction_log TL LEFT JOIN user U ON TL.transaction_owner_id=U.user_id where 1 = 1 ' . $condition;
				$data = $this->db->query($query)->row_array();
				return $data['total_records'];
			} else {
				$query = 'select TL.system_transaction_id,TL.transaction_type,TL.domain_origin,TL.app_reference,
				TL.fare,TL.domain_markup as admin_markup,TL.domain_markup as profit,TL.level_one_markup as agent_markup,TL.convinence_fees as convinence_amount,TL.promocode_discount as discount,
				TL.remarks,TL.created_datetime, concat(U.first_name, " ", U.last_name) as username, agency_name as agent_name 
				from transaction_log TL LEFT JOIN user U ON TL.transaction_owner_id=U.user_id where 1=1 ' . $condition . ' order by TL.origin desc limit ' . $offset . ', ' . $limit;
				return $this->db->query($query)->result_array();
			}
		}
	}
}
