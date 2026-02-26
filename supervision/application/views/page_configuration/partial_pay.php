<?php

/**
 * FORM START
 */
$form_configuration['inputs'] = array(
    'origin' => array('type' => 'hidden', 'label_line_code' => -1, 'mandatory' => false),
    'partial_value' => array('type' => 'text', 'label_line_code' => 345, 'mandatory' => true),

);

$form_attributes = array('method' => 'POST', 'action' => '');

$form_configuration['form']['partial_edit'] = array(
    'form_header' => $form_attributes,
    'sections' => array(
        array(
            'elements' => array('origin', 'partial_value')
        ),
    ),
    'form_footer' => array('update', 'cancel')
);
/*** Form End ***/
/**
 * FORM VALIDATION SETTINGS
 */
