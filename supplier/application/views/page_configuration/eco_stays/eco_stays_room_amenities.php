<?php

/**
 * FORM START
 */
$form_configuration['inputs'] = array(
    'origin' => array('type' => 'hidden', 'label_line_code' => -1, 'mandatory' => false),
    'name' => array('type' => 'text', 'label_line_code' => 183, 'mandatory' => true),
    'image' => array('type' => 'file', 'label_line_code' => 265, 'DT' => 'PROVAB_SOLID_IMAGE_TYPE', 'mandatory' => false),
    'status' => array('type' => 'radio', 'label_line_code' => 222, 'source' => 'enum', 'source_id' => 'status'),
);

$form_attributes = array('method' => 'POST', 'action' => '');
$form_configuration['form']['types'] = array(
    'form_header' => $form_attributes,
    'sections' => array(
        array(
            'elements' => array('name','image')
        ),
    ),
    'form_footer' => array('submit', 'reset')
);
$form_configuration['form']['types_edit'] = array(
    'form_header' => $form_attributes,
    'sections' => array(
        array(
            'elements' => array('origin','name','image')
        ),
    ),
    'form_footer' => array('submit', 'cancel')
);
/*** Form End ***/
/**
 * FORM VALIDATION SETTINGS
 */
