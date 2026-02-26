<?php

/**
 * Provab Currency Class
 *
 * Handle all the currency conversion in the application
 *
 * @package	Provab
 * @subpackage	provab
 * @category	Libraries
 * @author		Balu A<balu.provab@gmail.com>
 * @link		http://www.provab.com
 */
class Currency extends Master_currency
{


	public function __construct($params = array())
	{
		//call parent
		parent::__construct($params);
	}
	/**
	 * Set Commission
	 */
	public function set_commission(): void
	{
		$this->commission_fees_row['admin_commission_list'] = [
			'commission_currency' => get_application_currency_preference(),
			'value' => 0,
		];
	}

	public function get_commission(): array
	{
		$this->set_commission();
		return $this->commission_fees_row;
	}
}
