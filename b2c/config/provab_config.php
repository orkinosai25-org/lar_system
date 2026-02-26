<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['master_module_list']	= array(
META_AIRLINE_COURSE => 'flights',
META_TRANSFER_COURSE => 'transfershotelbed',
META_ACCOMODATION_COURSE => 'hotels',
META_BUS_COURSE => 'buses',
META_TRANSFERV1_COURSE=>'transfers',
META_CAR_COURSE=>'car',
META_SIGHTSEEING_COURSE=>'activities',
META_PACKAGE_COURSE => 'holidays'

);
/******** Current Module ********/
$config['current_module'] = 'b2c';

$config['load_minified'] = false;

$config['verify_domain_balance'] = false;

/******** PAYMENT GATEWAY START ********/
//To enable/disable PG
$config['enable_payment_gateway'] = true;
$config['active_payment_gateway'] = 'Paypal';
$config['active_payment_system'] = 'test';//test/live
$config['payment_gateway_currency'] = 'USD';//INR


/******** PAYMENT GATEWAY END ********/

/**
 * 
 * Enable/Disable caching for search result
 */
$config['cache_hotel_search'] = true;//right now not needed
$config['cache_flight_search'] = false;
$config['cache_bus_search'] = true;
$config['cache_car_search'] = false;
$config['cache_sightseeing_search'] = true;
$config['cache_transferv1_search'] = true;

/**
 * Number of seconds results should be cached in the system
 */
$config['cache_hotel_search_ttl'] = 600;
$config['cache_flight_search_ttl'] = 300;
$config['cache_bus_search_ttl'] = 300;
$config['cache_car_search_ttl'] = 300;
$config['cache_sightseeing_search_ttl'] = 300;
$config['cache_transferv1_search_ttl'] = 300;

/*$config['lazy_load_hotel_search'] = true;*/
$config['hotel_per_page_limit'] = 20;
$config['car_per_page_limit'] = 200;
$config['sightseeing_page_limit'] = 50;
$config['transferv1_page_limit'] = 50;

/*
	search session expiry period in seconds
*/
$config['flight_search_session_expiry_period'] = 600;//600
$config['flight_search_session_expiry_alert_period'] = 300;//300
