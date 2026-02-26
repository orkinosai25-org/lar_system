<?php

/**
 * Provab Common Functionality For API Class
 *
 *
 * @package	Provab
 * @subpackage	provab
 * @category	Libraries
 * @author		Balu A<balu.provab@gmail.com>
 * @link		http://www.provab.com
 */
abstract class Common_Api_Grind {
	protected $DomainKey;
	function __construct()
	{
		$this->DomainKey = get_domain_key();
	}
	
	/**
	 *  Balu A
	 * convert search params to format required by booking source
	 * @param number $search_id unique id which identifies search details
	 */
	abstract function search_data(int $search_id);

	/**
	 * Balu A
	 * update markup currency and return summary
	 *
	 * @param array	 $price_summary
	 * @param object $currency_obj
	 */
	abstract function update_markup_currency( array & $price_summary, object & $currency_obj);

	/**
	 * Balu A
	 * calculate and return total price details
	 * @param array $price_summary - price which has to be added
	 */
	abstract function total_price(array $price_summary);

	/**
	 * Balu A
	 *Process Booking
	 * @param array $booking_params
	 */
	abstract function process_booking(int|string $book_id, array $booking_params);
	
	/**
	 * return booking url to be used in the application for all the modules
	 */
	abstract function booking_url(int $search_id);
}