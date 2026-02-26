<?php

/**
 * FORM START
 */

$form_configuration['inputs'] = array(
    'origin' => array('type' => 'hidden', 'label_line_code' => -1, 'mandatory' => false),
    'from_before_days' => array('type' => 'number', 'label_line_code' => 285, 'mandatory' => true),
    'to_before_days' => array('type' => 'number', 'label_line_code' => 286, 'mandatory' => true),
    'penality_type' => array('type' => 'select', 'label_line_code' => 287, 'source' => 'enum', 'source_id' => 'value_type'),
    'penality_value' => array('type' => 'number', 'label_line_code' => 288, 'mandatory' => true),
    'status' => array('type' => 'radio', 'label_line_code' => 222, 'source' => 'enum', 'source_id' => 'status'),
);

$form_attributes = array('method' => 'POST', 'action' => '');
$form_configuration['form']['room_cancellation_policy'] = array(
    'form_header' => $form_attributes,
    'sections' => array(
        array(
            'elements' => array('to_before_days', 'penality_type', 'penality_value', 'status')
        ),
    ),
    'form_footer' => array('submit', 'reset')
);
$form_configuration['form']['room_cancellation_policy_edit'] = array(
    'form_header' => $form_attributes,
    'sections' => array(
        array(
            'elements' => array('origin', 'to_before_days', 'penality_type', 'penality_value', 'status')
        ),
    ),
    'form_footer' => array('submit', 'cancel')
);
/*** Form End ***/
/**
 * FORM VALIDATION SETTINGS
 */
