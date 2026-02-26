<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab - vibrant holidays
 * @subpackage Client
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */
//error_reporting(E_ALL);
class Menu extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('hotel_model');
		$this->load->model('flight_model');
		$this->load->model('car_model');
		$this->load->model('user_model');
		$this->load->model('transaction_model');	
		//$this->output->enable_profiler(TRUE);
	}

	/**
	 * index page of application will be loaded here
	 */
	function index()
	{
		$time_line_report=[];
		$module_total_earning=[];
		$group_time_line_report=[];
		$car_earning=[];
		if (web_page_access_privilege('p1')) {
			if (is_active_bus_module()) {
				$active_bus = true;
			}
			if (is_active_hotel_module()) {
				$active_hotel = true;
			}
			if (is_active_airline_module()) {
				$active_airline = true;
			}
			if(is_active_sightseeing_module()){
				$active_sightseeing = true;
			}
			if(is_active_car_module()){
				$active_car = true;
			}
			if(is_active_transferv1_module()){
				$active_transfers = true;
			}
			$this->load->library('booking_data_formatter');
			$days_duration = -1; // ADD day count to filter result
			$condition = array();
			$condition[] = array('BD.status', ' IN ', '("BOOKING_CONFIRMED","BOOKING_PENDING")');
			if ($days_duration > 0) {
				$condition = array(
				array('BD.created_datetime', '>=', $this->db->escape(date('Y-m-d', strtotime(subtract_days_from_date($days_duration)))))
				
				);
			}
			//load for current year only
			$time_line_interval = get_month_names();
			$hotel_earning = $flight_earning = $bus_earning = $sightseeing_earning = $transfers_earning= array();
			if (!empty($active_hotel)) {
				$module_total_earning[0]['name'] = 'Hotel';
				$module_total_earning[0]['y'] = 0;
				$time_line_report[0]['name'] = 'Hotel';
				$time_line_report[0]['data'] = array();
				$time_line_report[0]['color'] = '#00a65a';
				$tmp_hotel_booking = $this->hotel_model->get_monthly_booking_summary();
				$month_index_hotel = index_month_number($tmp_hotel_booking);
			}
			if (!empty($active_airline)) {
				$module_total_earning[1]['name'] = 'Flight';
				$module_total_earning[1]['y'] = 0;
				$time_line_report[1]['name'] = 'Flight';
				$time_line_report[1]['data'] = array();
				$time_line_report[1]['color'] = '#0073b7';
				$tmp_flight_booking = $this->flight_model->get_monthly_booking_summary();
				$month_index_flight = index_month_number($tmp_flight_booking);
			}
			if (!empty($active_bus)) {
				$module_total_earning[2]['bus'] = 'Bus';
				$module_total_earning[2]['y'] = 0;
				$time_line_report[2]['name'] = 'Bus';
				$time_line_report[2]['data'] = array();
				$time_line_report[2]['color'] = '#dd4b39';
				$tmp_bus_booking = $this->bus_model->get_monthly_booking_summary();
				$month_index_bus = index_month_number($tmp_bus_booking);
			}
			if (!empty($active_sightseeing)) {
				$module_total_earning[3]['sightseeing'] = 'Activities';
				$module_total_earning[3]['y'] = 0;
				$time_line_report[3]['name'] = 'Activities';
				$time_line_report[3]['data'] = array();
				$time_line_report[3]['color'] = '#ff9800';
				$tmp_sightseeing_booking = $this->sightseeing_model->get_monthly_booking_summary();

				$month_index_sightseeing = index_month_number($tmp_sightseeing_booking);
			}
			if (!empty($active_car)) {
				$module_total_earning[4]['car'] = 'Car';
				$module_total_earning[4]['y'] = 0;
				$time_line_report[4]['name'] = 'Car';
				$time_line_report[4]['data'] = array();
				$time_line_report[4]['color'] = '#dd4b39';
				// $tmp_car_booking = $this->car_model->get_monthly_booking_summary();
				$month_index_car = index_month_number($tmp_bus_booking);
			}
			if (!empty($active_transfers)) {
				$module_total_earning[5]['transfers'] = 'Transfers';
				$module_total_earning[5]['y'] = 0;
				$time_line_report[5]['name'] = 'Transfers';
				$time_line_report[5]['data'] = array();
				$time_line_report[5]['color'] = '#456F13';
				$tmp_transfers_booking = $this->transferv1_model->get_monthly_booking_summary();
				//debug($tmp_transfers_booking);

				$month_index_transfers = index_month_number($tmp_transfers_booking);
			}

			$time_line_report_average = array();
			$monthly_hotel_booking = array();
			$monthly_flight_booking = array();
			$monthly_bus_booking = array();
			$monthly_sightseeing_booking = array();
			$monthly_car_booking = array();
			$monthly_transfers_booking = array();

			foreach ($time_line_interval as $k => $v) {
				if (!empty($active_hotel)) {
					if (isset($month_index_hotel[$k])) {
						//HOTEL
						$monthly_hotel_booking[$k] = intval($month_index_hotel[$k]['total_booking']);
						$hotel_earning[$k] = round($month_index_hotel[$k]['monthly_earning']);
					} else {
						$monthly_hotel_booking[$k] = 0;
						$hotel_earning[$k] = 0;
					}
					($time_line_report_average[$k] += round($hotel_earning[$k]))/(intval($monthly_hotel_booking[$k]) > 0 ? $monthly_hotel_booking[$k] : 1);
					($module_total_earning[0]['y'] += round($hotel_earning[$k]));
				}
				if (!empty($active_airline)) {
					if (isset($month_index_flight[$k])) {
						//FLIGHT
						$monthly_flight_booking[$k] = intval($month_index_flight[$k]['total_booking']);
						$flight_earning[$k] = round($month_index_flight[$k]['monthly_earning']);
					} else {
						$monthly_flight_booking[$k] = 0;
						$flight_earning[$k] = 0;
					}
					($time_line_report_average[$k] += round($flight_earning[$k]))/(intval($monthly_flight_booking[$k]) > 0 ? $monthly_flight_booking[$k] : 1);
					($module_total_earning[1]['y'] += round($flight_earning[$k]));
				}
				if (!empty($active_bus)) {
					if (isset($month_index_bus[$k])) {
						//BUS
						$monthly_bus_booking[$k] = intval($month_index_bus[$k]['total_booking']);
						$bus_earning[$k] = round($month_index_bus[$k]['monthly_earning']);
					} else {
						$monthly_bus_booking[$k] = 0;
						$bus_earning[$k] = 0;
					}
					($time_line_report_average[$k] += round($bus_earning[$k]))/(intval($monthly_bus_booking[$k]) > 0 ? $monthly_bus_booking[$k] : 1);
					($module_total_earning[2]['y'] += round($bus_earning[$k]));
				}
				if (!empty($active_sightseeing)) {

					if (isset($month_index_sightseeing[$k])) {
						//Sightseeing
						$monthly_sightseeing_booking[$k] = intval($month_index_sightseeing[$k]['total_booking']);
						$sightseeing_earning[$k] = round($month_index_sightseeing[$k]['monthly_earning']);
					} else {
						$monthly_sightseeing_booking[$k] = 0;
						$sightseeing_earning[$k] = 0;
					}
					($time_line_report_average[$k] += round($sightseeing_earning[$k]))/(intval($monthly_sightseeing_booking[$k]) > 0 ? $monthly_sightseeing_booking[$k] : 1);
					($module_total_earning[3]['y'] += round($sightseeing_earning[$k]));
				}

				if (!empty($active_transfers)) {

					if (isset($month_index_transfers[$k])) {
						//Transfers
						$monthly_transfers_booking[$k] = intval($month_index_transfers[$k]['total_booking']);
						$transfers_earning[$k] = round($month_index_transfers[$k]['monthly_earning']);
					} else {
						$monthly_transfers_booking[$k] = 0;
						$transfers_earning[$k] = 0;
					}
					($time_line_report_average[$k] += round($transfers_earning[$k]))/(intval($monthly_transfers_booking[$k]) > 0 ? $monthly_transfers_booking[$k] : 1);
					($module_total_earning[5]['y'] += round($transfers_earning[$k]));
				}


			}
			// debug($module_total_earning);
			// exit;
			if (!empty($active_car)) {
					if (isset($month_index_car[$k])) {
						//BUS
						$monthly_car_booking[$k] = intval($month_index_car[$k]['total_booking']);
						$car_earning[$k] = round($month_index_car[$k]['monthly_earning']);
					} else {
						$monthly_car_booking[$k] = 0;
						$car_earning[$k] = 0;
					}
					($time_line_report_average[$k] += round($car_earning[$k]))/(intval($monthly_car_booking[$k]) > 0 ? $monthly_car_booking[$k] : 1);
					($module_total_earning[4]['y'] += round($car_earning[$k]));
				}
			if (!empty($active_hotel)) {
				$time_line_report[0]['data'] = $monthly_hotel_booking;
				$module_total_earning[0]['color'] = $time_line_report[0]['color'];
				$group_time_line_report[] = array('type' => 'column', 'name' => 'Hotel', 'data' => $hotel_earning, 'color' => $time_line_report[0]['color']);
			}
			if (!empty($active_airline)) {
				$time_line_report[1]['data'] = $monthly_flight_booking;
				$module_total_earning[1]['color'] = $time_line_report[1]['color'];
				$group_time_line_report[] = array('type' => 'column','name' => 'Flight', 'data' => $flight_earning, 'color' => $time_line_report[1]['color']);
			}
			if (!empty($active_bus)) {
				$time_line_report[2]['data'] = $monthly_bus_booking;
				$module_total_earning[2]['color'] = $time_line_report[2]['color'];
				$group_time_line_report[] = array('type' => 'column','name' => 'Bus', 'data' => $bus_earning, 'color' => $time_line_report[2]['color']);
			}
			if (!empty($active_sightseeing)) {
				$time_line_report[3]['data'] = $monthly_sightseeing_booking;
				$module_total_earning[3]['color'] = $time_line_report[3]['color'];
				$group_time_line_report[] = array('type' => 'column','name' => 'Activities', 'data' => $sightseeing_earning, 'color' => $time_line_report[3]['color']);
			}
			if (!empty($active_transfers)) {
				$time_line_report[5]['data'] = $monthly_transfers_booking;
				$module_total_earning[5]['color'] = $time_line_report[5]['color'];
				$group_time_line_report[] = array('type' => 'column','name' => 'Transfers', 'data' => $transfers_earning, 'color' => $time_line_report[5]['color']);
			}

			if (!empty($active_car)) {
				$time_line_report[4]['data'] = $monthly_car_booking;
				$module_total_earning[4]['color'] = $time_line_report[4]['color'];
				$group_time_line_report[] = array('type' => 'column','name' => 'Car', 'data' => $bus_earning, 'color' => $time_line_report[4]['color']);
			}
		
			$max_count = max(array_merge($monthly_hotel_booking, $monthly_flight_booking, $monthly_bus_booking,$monthly_sightseeing_booking, $monthly_car_booking,$monthly_transfers_booking));
			foreach ($time_line_report_average as $k => $v) {
				if ($v > 0) {
					$time_line_report_average[$k] = round($v/3);
				}
			}
			$group_time_line_report[] = array(
				'type' => 'spline', 'name' => 'Average', 'data' => $time_line_report_average,
				'marker' => array('lineColor' => '#e65100', 'color' => '#ff5722', 'lineWidth' => 2, 'fillColor' => '#FFF')
			);


			/*debug($group_time_line_report);exit;*/
			$page_data = array('group_time_line_report' => $group_time_line_report,
			'module_total_earning' => $module_total_earning,
			'time_line_interval' => $time_line_interval,
			'max_count' => $max_count, 'time_line_report' => $time_line_report, 'time_line_report_average' => $time_line_report_average);

			
			if (!empty($active_hotel)) {
				$page_data['hotel_booking_count'] = $this->hotel_model->booking($condition, true);
			}
			if (!empty($active_airline)) {
				$page_data['flight_booking_count'] = $this->flight_model->booking($condition, true);
			}
			if (!empty($active_bus)) {
				$page_data['bus_booking_count'] = $this->bus_model->booking($condition, true);
			}

			if (!empty($active_sightseeing)) {
				$page_data['sightseeing_booking_count'] = $this->sightseeing_model->booking($condition, true);
			}
			if (!empty($active_transfers)) {
				$page_data['transfers_booking_count'] = $this->transferv1_model->booking($condition, true);
			}

			if (!empty($active_car)) {
				$page_data['car_booking_count'] = $this->car_model->booking($condition, true);
			}
			$condition = array();
			$latest_transaction = $this->transaction_model->logs($condition, false, 0, 5);
			$latest_transaction = $this->booking_data_formatter->format_recent_transactions($latest_transaction, 'b2c');
			$page_data['latest_transaction'] = $latest_transaction['data']['transaction_details'];

			$page_data['total_online_user'] = $this->user_model->get_logged_in_users(array(array('U.user_id', '!=', intval($this->entity_user_id))), true);
			$page_data['latest_user'] = $this->user_model->get_domain_user_list(array(), false, 0, 12);
			$this->template->view('menu/dashboard', $page_data);
		}
	}
	
}
