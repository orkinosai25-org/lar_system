<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['master_module_list']	= array(
META_AIRLINE_COURSE => 'flights',
META_TRANSFERS_COURSE => 'transferHotel',
META_ACCOMODATION_COURSE => 'hotels',
META_BUS_COURSE => 'bus',
META_TRANSFERV1_COURSE=>'transfers',
META_SIGHTSEEING_COURSE=>'activities',
META_CAR_COURSE=>'cars',
META_PACKAGE_COURSE => 'holidays',
META_CRUISE_COURSE => 'curise',
META_AIRCHARTER_COURSE => 'air charter'	
);
/******** Current Module ********/
$config['current_module'] = 'b2b';

$config['verify_domain_balance'] = true;

/******** PAYMENT GATEWAY START ********/
//To enable/disable PG
$config['enable_payment_gateway'] = false;
$config['active_payment_gateway'] = 'Paypal';
$config['active_payment_system'] = 'test';//test/live
$config['payment_gateway_currency'] = 'USD';//INR
/******** PAYMENT GATEWAY END ********/

/**
 * 
 * Enable/Disable caching for search result
 */
$config['cache_hotel_search'] = true;
$config['cache_sightseeing_search'] = true;
$config['cache_flight_search'] = false;
$config['cache_bus_search'] = true;
$config['cache_car_search'] = false;

/**
 * Number of seconds results should be cached in the system
 */
$config['cache_hotel_search_ttl'] = 300;
$config['cache_flight_search_ttl'] = 1900;
$config['cache_bus_search_ttl'] = 300;
$config['cache_car_search_ttl'] = 300;
$config['cache_sightseeing_search_ttl'] = 300;



/*$config['lazy_load_hotel_search'] = true;*/
$config['hotel_per_page_limit'] = 20;
$config['car_per_page_limit'] = 200;

/*
	search session expiry period in seconds
*/
$config['flight_search_session_expiry_period'] = 600;
$config['flight_search_session_expiry_alert_period'] = 300;
