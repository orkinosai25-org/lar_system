<?php

/**
 * FORM START
 */
$form_configuration['inputs'] = array(
    'origin' => array('type' => 'hidden', 'label_line_code' => -1, 'mandatory' => false),
    'name' => array('type' => 'text', 'label_line_code' => 183, 'mandatory' => true),
    'start_date' => array('type' => 'text', 'label_line_code' => 343, 'mandatory' => true),
    'end_date' => array('type' => 'text', 'label_line_code' => 344, 'mandatory' => true),
);

$form_attributes = array('method' => 'POST', 'action' => '');
$form_configuration['form']['seasons'] = array(
    'form_header' => $form_attributes,
    'sections' => array(
        array(
            'elements' => array('name', 'start_date', 'end_date')
        ),
    ),
    'form_footer' => array('submit', 'reset')
);
$form_configuration['form']['seasons_edit'] = array(
    'form_header' => $form_attributes,
    'sections' => array(
        array(
            'elements' => array('origin', 'name', 'start_date', 'end_date')
        ),
    ),
    'form_footer' => array('submit', 'cancel')
);
/*** Form End ***/
/**
 * FORM VALIDATION SETTINGS
 */
