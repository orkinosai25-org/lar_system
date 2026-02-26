<?php
/**
 * author: Balu A
 * FORM START
 */
$form_configuration ['inputs'] = array (
		'amount' => array (
				'type' => 'number',
				'label_line_code' => 208,
				'class' => array (
						'numeric' 
				) 
		),
		'currency_converter_origin' => array (
				'type' => 'hidden',
				'label_line_code' => - 1 
		),
		'conversion_value' => array (
				'type' => 'hidden',
				'label_line_code' => - 1 
		),
		'remarks' => array (
				'type' => 'textarea',
				'label_line_code' => 211,
				'mandatory' => false 
		),
		
);

/**
 * Add FORM
 */
$form_configuration ['form'] ['credit_request_form'] = array (
		'form_header' => '',
		'sections' => array (
				array (
						'elements' => array (
								'amount',
								'currency_converter_origin',
								'conversion_value',
								'remarks' 
						),
						'fieldset' => 'FFL0050' 
				) 
		),
		'form_footer' => array (
				'submit',
				'reset' 
		) 
);
/**
 * * Form End **
 */
/**
 * FORM VALIDATION SETTINGS
 */
$auto_validator ['amount'] = 'trim|numeric';
$auto_validator ['remarks'] = 'trim';
