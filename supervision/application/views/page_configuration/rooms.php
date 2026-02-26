<?php

/**
 * FORM START
 */
$form_configuration['inputs'] = array(
    'origin' => array('type' => 'hidden', 'label_line_code' => -1, 'mandatory' => false),
    'type' => array('type' => 'select', 'source' => 'db', 'source_location' => 'db_cache_api::get_eco_stays_room_types', 'label_line_code' => 272, 'mandatory' => true),
    'name' => array('type' => 'text', 'label_line_code' => 183, 'mandatory' => true),
    'board_type' => array('type' => 'select', 'source' => 'db', 'source_location' => 'db_cache_api::get_eco_stays_board_types', 'label_line_code' => 284, 'mandatory' => true),
    'description' => array('type' => 'textarea', 'label_line_code' => 46, 'mandatory' => true),
    'max_adults' => array('type' => 'number', 'label_line_code' => 279, 'mandatory' => true),
    'max_childs' => array('type' => 'number', 'label_line_code' => 280, 'mandatory' => true),
    'policy' => array('type' => 'textarea', 'label_line_code' => 278, 'mandatory' => true),
    'meal_type' => array('type' => 'select', 'source' => 'db', 'source_location' => 'db_cache_api::get_eco_stays_room_meal_types', 'label_line_code' => 281, 'mandatory' => true),
    'extra_bed' => array('type' => 'radio', 'source' => 'enum', 'source_id' => 'status_choice', 'label_line_code' => 282, 'mandatory' => true),
    'amenities' => array('type' => 'select', 'multiple' => true, 'source' => 'db', 'source_location' => 'db_cache_api::get_eco_stays_room_amenities', 'label_line_code' => 275, 'mandatory' => true),
    'quantity' => array('type' => 'number', 'label_line_code' => 283, 'mandatory' => true),
    'status' => array('type' => 'radio', 'label_line_code' => 222, 'source' => 'enum', 'source_id' => 'status'),
);

$form_attributes = array('method' => 'POST', 'action' => '');
$form_configuration['form']['rooms'] = array(
    'form_header' => $form_attributes,
    'sections' => array(
        array(
            'elements' => array('type', 'name', 'board_type', 'max_adults', 'max_childs', 'policy',  'extra_bed', 'amenities', 'quantity', 'status')
        ),
    ),
    'form_footer' => array('submit', 'reset')
);
$form_configuration['form']['rooms_edit'] = array(
    'form_header' => $form_attributes,
    'sections' => array(
        array(
            'elements' => array('origin', 'type', 'name', 'board_type', 'max_adults', 'max_childs', 'extra_bed', 'amenities', 'quantity', 'status')
        ),
    ),
    'form_footer' => array('submit', 'cancel')
);
/*** Form End ***/
/**
 * FORM VALIDATION SETTINGS
 */
