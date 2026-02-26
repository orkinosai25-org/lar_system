<?php
/**
 * APPLICATION THEME
 * Themes available in the application
 */
$enums['theme_list'] = array('template_v1' => 'Default Theme');

$enums['room_type_list'] = array('SG' => 'Single', 'TW' => 'Twin', 'DB' => 'Double', 'TR' => 'Triple', 'QD' => 'Quad');

/**
 * Type of balance request methods integrated in the system
 */
$enums['provab_balance_requests'] = array(
	'CHECK___DD' => 'CHEQUE/DD',
	'ETRANSFER' => 'E-TRANSFER',
	'CASH' => 'Cash'
);
$enums['value_type'] = array(
	'plus' => 'Plus(+)',
	'percentage' => 'Percentage(%)',
);
$enums['priority_list'] = array(
	'high' => 'High',
	'medium' => 'Medium',
	'low' => 'Low'
);
// $enums['title'] = array(
// 1 => 'Mr',
// 2 => 'Ms',
// 3 => 'Miss',
// 4 => 'Master',
// 5 => 'Mrs',
// 6=>  'Mstr'
// );
$enums['title'] = array(
1 => 'Mr',
2 => 'Ms',
3 => 'Miss',
5 => 'Mrs',
6=>  'Mstr'
);

$enums['adult_title'] = array(
1 => 'Mr',
2 => 'Ms',
5 => 'Mrs',
);
$enums['child_title'] = array(
	3=>'Miss',
	6=>'Mstr',
	
);
$enums['viator_title'] = array(
	1=>'Mr',
	2=>'Ms',	
	5=>'Mrs',
	6=>'Mstr',
	
);
$enums['status'] = array(
0 => 'Inactive',
1 => 'Active'
);

$enums['status_choice'] = array(
0 => 'No',
1 => 'Yes'
);

$enums['user_group'] = array('marketing_team', 'sales_team', 'zone_team', 'production_team', 'accounts_team', 'merchantize_team');

$enums['language_preference'] = array(
	'english' => 'English',
	'hindi' => 'Hindi'
);

$enums['location_type'] = array(
	'1' => 'continent',
	'2' => 'country',
	'3' => 'city'
);

$enums['payment_mode'] = array(
1 => 'Cash',
2 => 'Check',
3 => 'Deposit'
);

$enums['duration_type'] = array(
1 => 'day',
2 => 'week',
3 => 'month',
4 => 'year'
);

$enums['gender'] = array(
1 => 'Male',
2 => 'Female',
3 => 'Others'
);
//Agent Balance Alert - Balu A
$enums['agree_sms_alert'] = array(
	1 => 'Send SMS'
);
$enums['agree_email_alert'] = array(
	1 => 'Notify to E-mail'
);
$enums['commission_value_type'] = array(
	'plus' => '+ INR',
	'percentage' => '%'
);
//Flight Fare Classes - Balu A
$enums ['first_class'] = array (
		0 => 'A',
		1 => 'F',
		2 => 'O',
		3 => 'P',
		4 => 'R' 
);
$enums ['buisness_class'] = array (
		0 => 'C',
		1 => 'D',
		2 => 'I',
		3 => 'J', 
		4 => 'Z'
);
$enums ['economy_class'] = array (
		0 => 'N',
		1 => 'X',
);
$enums ['coach_class'] = array (
		0 => 'B',
		1 => 'E',
		2 => 'G',
		3 => 'H',
		4 => 'K',
		5 => 'L',
		6 => 'M',
		7 => 'Q',
		8 => 'S',
		9 => 'T',
		10 => 'W',
		11 => 'U',
		12 => 'V',
		13 => 'Y'
);
$enums ['report_filter_status'] = array (
		'BOOKING_CONFIRMED' => 'Confirmed',
		'BOOKING_PENDING' => 'Pending',
		'BOOKING_CANCELLED' => 'Cancelled'
);
$enums['provab_balance_status'] = array('ACCEPTED' => 'accepted', 'REJECTED' => 'rejected', 'PENDING' => 'pending');

$enums['transaction_type'] = array(
'flight' => 'Flight',
'hotel' => 'Hotel',
'bus' => 'Bus',
// 'commute'=>'Transfers',
// 'sightseeing'=>'Activites',
// 'transaction' => 'Transaction-(Deposits/Others)'
);
$enums ['month_names'] = array (
		0 => 'Jan',
		1 => 'Feb',
		2 => 'Mar',
		3 => 'Apr',
		4 => 'May',
		5 => 'Jun',
		6 => 'Jul',
		7 => 'Aug',
		8 => 'Sep',
		9 => 'Oct',
		10 => 'Nov',
		11 => 'Dec'
);


$enums['ratings'] = array(
	1 => '1 star',
	2 => '2 star',
	3 => '3 star',
	4 => '4 star',
	5 => '5 star'
);