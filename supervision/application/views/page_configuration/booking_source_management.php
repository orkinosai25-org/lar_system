<?php

/**
 * author: Balu A
 * FORM START
 * booking_source
 */
$form_configuration['inputs'] = array(
	'origin' 		  => array('type' => 'hidden',   'label_line_code' => -1),
	'course_id'   => array('type' => 'text', 'readonly' => true, 'label_line_code' => 86),
	'name'    	  => array('type' => 'text', 'label_line_code' => 97),
	'description' => array('type' => 'textarea', 'mandatory'=>false, 'label_line_code' => 257),
	'meta_course_list_id' => array('type' => 'select', 'label_line_code' => 103, 'mandatory'=>false, 'source' => 'db', 'source_location' => 'db_cache_api::get_module_list'),
	'booking_engine_status' 	  => array('type' => 'radio', 'label_line_code' => 258, 'source' => 'enum', 'source_id' => 'status'),
);

/**
 * Add FORM
 */
$form_configuration['form']['booking_source_management'] = array(
'form_header' => '',
		'sections' => array(
			array('elements' => array('origin', 'name', 'description','meta_course_list_id','booking_engine_status'),
				'fieldset' => 'FFL0054'
			)
		),
		'form_footer' => array('submit', 'reset')
	);
/**
 * Update FORM
 */
$form_configuration['form']['booking_source_management_edit'] = array(
		'form_header' => '',
		'sections' => array(
			array('elements' => array('origin', 'name', 'description', 'meta_course_list_id','booking_engine_status'),
				'fieldset' => 'FFL0054'
			)
		),
		'form_footer' => array('update', 'reset')
	);

/*** Form End ***/
// LETS CLEAN DISABLED LABEL DATA
/**
* adding to disabled to make sure that no validation is done for update
*/
$disabled['core_course_list_edit'] = array('course_id');

/*** Form End ***/
/**
 * FORM VALIDATION SETTINGS
 */
$auto_validator['origin'] = 'trim|min_length[1]|max_length[4]|numeric';
$auto_validator['name'] = 'trim|required|max_length[30]|xss_clean';
$auto_validator['description'] = 'trim|max_length[250]|xss_clean';
