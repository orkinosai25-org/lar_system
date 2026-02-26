<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab - Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com> on 01-06-2015
 * @version    V2
 */

class Management extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model ( 'Custom_Db' );
		$this->load->library('Currency');
	}
	public function promocode(): void
	{
	    $all_post = $this->input->post();
	    $currency_code = $all_post['currency'] ?? '';
	    $promo_input = $all_post['promocode'] ?? '';
	    $moduletype_hash = $all_post['moduletype'] ?? '';
	    $currency_obj = new Currency([
	        'module_type' => 'flight',
	        'from' => admin_base_currency(),
	        'to' => $currency_code
	    ]);
	    $condition = ['promo_code' => $promo_input, 'status' => 1];
	    $promo_code_res = $this->Custom_Db->single_table_records('promo_code_list', '*', $condition);

	    $result = ['status' => 0, 'error_msg' => 'Invalid Promo Code'];

	    if ($promo_code_res['status'] === 1) {
	        $promo_code = $promo_code_res['data'][0];
	        $result['error_msg'] = 'Invalid Promo Code';
	        if (md5($promo_code['module']) === $moduletype_hash) {
	        	$module_map = [
	                'car' => 'car_booking_details',
	                'hotel' => 'hotel_booking_details',
	                'flight' => 'flight_booking_details',
	                'activities' => 'sightseeing_booking_details',
	                'transfers' => 'transferv1_booking_details',
	                'bus' => 'bus_booking_details',
	            ];
	            $booking_table = $module_map[$promo_code['module']] ?? '';

	            if ($booking_table) {
	                $query = is_logged_in_user()
	                    ? "SELECT BD.origin FROM payment_gateway_details AS PGD 
	                       RIGHT JOIN {$booking_table} AS BD ON PGD.app_reference = BD.app_reference 
	                       WHERE BD.created_by_id = '{$this->entity_user_id}'"
	                    : "SELECT BD.origin FROM payment_gateway_details AS PGD 
	                       RIGHT JOIN {$booking_table} AS BD ON PGD.app_reference = BD.app_reference 
	                       WHERE BD.email = '{$all_post['email']}' AND PGD.status != 'pending'";

	                $user_promocode_check = $this->Custom_Db->get_result_by_query($query);
	                $used_count = is_array($user_promocode_check) ? count($user_promocode_check) : 0;
	                $result['error_msg'] = 'Already used';
	                if ($used_count <= 0) {
	                	$min_amount = get_converted_currency_value($currency_obj->force_currency_conversion($promo_code['minimum_amount']));
	                    $total_amount_val_org = (float) str_replace(',', '', $all_post['total_amount_val']);

	                    if ($total_amount_val_org > $min_amount) {
	                    	$value = $promo_code['value'];
	                        $value = get_converted_currency_value($currency_obj->force_currency_conversion($value));
	                        if ($promo_code['value_type'] === 'percentage') {
	                            $value = ($total_amount_val_org * round($promo_code['value'])) / 100;
	                        }
	                        $actual_value = number_format($value, 2);

	                        if ($value < $total_amount_val_org) {
	                            $total_amount_val = $total_amount_val_org + ($all_post['convenience_fee'] ?? 0) - $value;
	                            $total_amount_val += (float) ($all_post['extra_baggage'] ?? 0);
	                            $total_amount_val += (float) ($all_post['extra_meal'] ?? 0);
	                            $total_amount_val += (float) ($all_post['extra_seat'] ?? 0);

	                            $total_amount_val = max(0, $total_amount_val);

	                            $result = [
	                                'value' => $value,
	                                'actual_value' => $actual_value,
	                                'total_amount_val' => round($total_amount_val),
	                                'total_amount_data' => $all_post['currency_symbol'] . " " . number_format($total_amount_val, 2),
	                                'convenience_fee' => $all_post['convenience_fee'],
	                                'promocode' => $promo_input,
	                                'discount_value' => $all_post['currency_symbol'] . " " . number_format($value, 2),
	                                'module' => $moduletype_hash,
	                                'status' => 1
	                            ];

	                            $this->custom_db->insert_record('promo_code_doscount_applied', [
	                                'discount_value' => $actual_value,
	                                'promocode' => $promo_input,
	                                'module' => $moduletype_hash,
	                                'search_key' => provab_encrypt($all_post['booking_key']),
	                                'created_datetime' => date('Y-m-d H:i:s')
	                            ]);
	                        }
	                    }
	                   
	                } 
	            }
	            
	        } elseif ($promo_code['expiry_date'] <= date('Y-m-d') && $promo_code['expiry_date'] !== '0000-00-00') {
	            $result['error_msg'] = 'Promo Code Expired';
	        } 
	    }

	    echo json_encode($result);
	}

}