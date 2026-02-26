<?php

/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */
abstract class Abstract_Management_Model extends CI_Model
{
	var $markup_level;
	function __construct($markup_level)
	{
		$this->markup_level = $markup_level;
	}
	/**
	 * Balu A
	 * @param number $markup_origin - Markup origin
	 * @param string $type			- generic/specific
	 * @param string $module_type	- module name - b2c_hotel/b2c_flight/b2c_bus
	 * @param number $reference_id	- reference id in case of specific markup
	 * @param number $value			- value of markup
	 * @param string $value_type	- precentage/plus
	 * @param number $domain_origin	- domain to which markup is applicable
	 */
	function save_markup_data(
		int $markup_origin,
		string $type,
		string $module_type,
		int $reference_id,
		float $value,
		string $value_type,
		int $domain_origin,
		int $user_oid = 0
	): void {
        $markup_data=[];
		$markup_data['origin']			= intval($markup_origin);
		$markup_data['markup_level']	= $this->markup_level;
		$markup_data['type']			= strtolower($type);
		$markup_data['module_type']		= strtolower($module_type);
		$markup_data['reference_id']	= intval($reference_id);
		$markup_data['value']			= floatval($value);
		$markup_data['value_type']		= strtolower($value_type);
		$markup_data['domain_list_fk']	= intval($domain_origin);
		$markup_data['user_oid']		= $user_oid;
		$markup_data['markup_currency']	= get_application_default_currency();
		// debug($markup_data);exit;
		if (empty($markup_data['type']) == false && empty($markup_data['value_type']) == false) {
			if (intval($markup_origin) > 0) {
				//update
				$this->custom_db->update_record('markup_list', $markup_data, array('origin' => intval($markup_origin)));
			} else {
				//insert
				$this->custom_db->insert_record('markup_list', $markup_data);
			}
		}
	}
	/**
	 * Categorize Airline Markup List - generic and specific

private function categorize_markup_list(array $markup_list): array
	 {
		$data_list['generic'] = $data_list['specific'] = '';
		foreach($markup_list as $__k => $__v) {
		if ($__v['markup_type'] == 'generic') {
		$data_list['generic'][] = $__v;
		} elseif ($__v['markup_type'] == 'specific') {
		$data_list['specific'][$__v['domain_key']] = $__v;
		}
		}
		return $data_list;
		}*/

	/**
	 * @param array $data_list

	private function extract_markup_domain_list(array $data_list): array
	 {
		$data = '';
		if (valid_array($data_list)) {
		foreach ($data_list as $__k => $__v) {
		if (isset($data[$__v['domain_key']]) == false) {
		$data[$__v['domain_key']]['domain_origin']	= $__v['domain_origin'];
		$data[$__v['domain_key']]['domain_name']	= $__v['domain_name'];
		$data[$__v['domain_key']]['domain_key']		= $__v['domain_key'];
		$data[$__v['domain_key']]['domain_status']	= $__v['domain_status'];
		}
		}
		}
		return $data;
		} */
}
