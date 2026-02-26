<?php

/**
 * FORM START
 */
$form_configuration['inputs'] = array(
    'origin' => array('type' => 'hidden', 'label_line_code' => -1, 'mandatory' => false),
    'type' => array('type' => 'select', 'source' => 'db', 'source_location' => 'db_cache_api::get_eco_stays_types', 'label_line_code' => 272, 'mandatory' => true),
    'theme' => array('type' => 'multipleselect', 'multiple' => true, 'source' => 'db', 'source_location' => 'db_cache_api::get_d_packages_themes', 'label_line_code' => 270, 'mandatory' => true),
   // 'host' => array('type' => 'select', 'source' => 'db', 'source_location' => 'db_cache_api::get_suppliers', 'label_line_code' => 277, 'mandatory' => true),
    'name' => array('type' => 'text', 'label_line_code' => 183, 'mandatory' => true),
    'description' => array('type' => 'textarea', 'label_line_code' => 46, 'mandatory' => true),
    'amenities' => array('type' => 'multipleselect', 'multiple' => true, 'source' => 'db', 'source_location' => 'db_cache_api::get_eco_stays_amenities', 'label_line_code' => 275, 'mandatory' => true),
    'country' => array('type' => 'select', 'source' => 'db', 'source_location' => 'db_cache_api::get_all_api_country_list', 'label_line_code' => 25, 'mandatory' => true),
    'city' => array('type' => 'select', 'label_line_code' => 18, 'mandatory' => true),
    'address' => array('type' => 'textarea', 'label_line_code' => 17, 'DT' => 'PROVAB_SOLID_V255'),
    'display_address' => array('type' => 'textarea', 'label_line_code' => 341, 'DT' => 'PROVAB_SOLID_V255'),
    'latitude' => array('type' => 'text', 'label_line_code' => 273, 'mandatory' => true),
    'longitude' => array('type' => 'text', 'label_line_code' => 274, 'mandatory' => true),
    'country_code' => array('type' => 'select', 'label_line_code' => 19, 'source' => 'db', 'source_location' => 'db_cache_api::get_postal_code_list', 'DT' => 'PROVAB_SOLID_SB03'),
    'phone' => array('type' => 'text', 'label_line_code' => 20, 'DT' => 'PROVAB_SOLID_I10', 'maxlength' => 10),
    'email' => array('type' => 'email', 'label_line_code' => 6, 'DT' => 'PROVAB_SOLID_V80'),
    'gst_number' => array('type' => 'text', 'label_line_code' => 276, 'DT' => 'PROVAB_SOLID_V80', 'mandatory' => false),
    'expiry' => array('type' => 'date', 'label_line_code' => 231, 'mandatory' => false),
    'ratings' => array('type' => 'select', 'source' => 'enum', 'source_id' => 'ratings', 'label_line_code' => 269, 'mandatory' => true),
    //'video_link' => array('type' => 'text', 'label_line_code' => 342, 'DT' => 'PROVAB_SOLID_V80', 'mandatory' => false),
    //'display_on_home_page' => array('type' => 'radio', 'source' => 'enum', 'source_id' => 'status_choice', 'label_line_code' => 254, 'mandatory' => true),
    'image' => array('type' => 'file', 'label_line_code' => 265, 'DT' => 'PROVAB_SOLID_IMAGE_TYPE', 'mandatory' => false),
    'status' => array('type' => 'radio', 'label_line_code' => 222, 'source' => 'enum', 'source_id' => 'status'),
);

$form_attributes = array('method' => 'POST', 'action' => '');
$form_configuration['form']['stays'] = array(
    'form_header' => $form_attributes,
    'sections' => array(
        array(
            'elements' => array('type', 'theme','name', 'description', 'amenities', 'country', 'city', 'address', 'latitude', 'longitude', 'country_code', 'phone', 'email', 'gst_number', 'expiry', 'ratings', 'image', 'display_address',  'status')
        ),
    ),
    'form_footer' => array('submit', 'reset')
);
$form_configuration['form']['stays_edit'] = array(
    'form_header' => $form_attributes,
    'sections' => array(
        array(
            'elements' => array('origin', 'type', 'theme', 'name', 'description', 'amenities', 'country', 'city', 'address', 'latitude', 'longitude', 'country_code', 'phone', 'email', 'gst_number', 'expiry', 'ratings', 'image', 'display_address', 'status')
        ),
    ),
    'form_footer' => array('submit', 'cancel')
);
/*** Form End ***/
/**
 * FORM VALIDATION SETTINGS
 */
