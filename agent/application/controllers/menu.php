<?php 
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @package    Provab - vibrant holidays
 * @subpackage Client
 * @author      Balu A <balu.provab@gmail.com>
 * @version     V1
 */
class Menu extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('custom_db');
		$this->load->model('hotel_model');
		$this->load->model('flight_model');
		$this->load->model('car_model');
		$this->load->model('user_model');
		$this->load->model('transaction_model');
		
	
	}

	/**
	 * Index page of the application will be loaded here.
	 */
	public function index(): void
	{
		redirect(base_url('index.php/menu/dashboard/?' . $_SERVER['QUERY_STRING']));

		// Unused legacy code has been commented out.
		/*
    $page_data['default_view'] = $_GET['default_view'] ?? null;

    // Banner Images
    $domain_origin = get_domain_auth_id();
    $page_data['banner_images'] = $this->custom_db->single_table_records(
        'banner_images',
        'image',
        ['added_by' => $domain_origin]
    );

    if (is_active_hotel_module()) {
        $this->load->model('hotel_model');
        $page_data['top_destination_hotel'] = $this->hotel_model->hotel_top_destinations();
    }

    $this->template->view('general/index', $page_data);
    */
	}
	public function dashboard(): void
	{$time_line_report=[];$module_total_earning=[];$group_time_line_report=[];$monthly_car_booking=[];$monthly_flight_booking=[];$monthly_hotel_booking=[];
        $time_line_report=[];$module_total_earning=[];$group_time_line_report=[];$monthly_car_booking=[];$monthly_flight_booking=[];$monthly_hotel_booking=[];$time_line_report=[];$module_total_earning=[];$month_index_transfers=[];
		// echo $this->entity_user_id;exit;
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
			if (is_active_car_module()) {

				$active_car= true;
			}
			if(is_active_sightseeing_module()){
				$active_sightseeing = true;
			}
			if(is_active_transferv1_module()){
				$active_transfers = true;
			}
			$this->load->library('booking_data_formatter');
			$days_duration = -1; // ADD day count to filter result
			$condition = array();
			if ($days_duration > 0) {
				$condition = array(
				array('BD.created_datetime', '>=', $this->db->escape(date('Y-m-d', strtotime(subtract_days_from_date($days_duration))))),
				array('BD.status', 'IN ', '("BOOKING_CONFIRMED","BOOKING_PENDING")')
				);
			}
			//load for current year only
			$time_line_interval = get_month_names();
			$hotel_earning = $flight_earning =  $car_earning   = array();
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
				// $tmp_bus_booking = $this->bus_model->get_monthly_booking_summary();
				// $month_index_bus = index_month_number($tmp_bus_booking);
			}
			if (!empty($active_car)) {
				$module_total_earning[3]['car'] = 'Car';
				$module_total_earning[3]['y'] = 0;
				$time_line_report[3]['name'] = 'Car';
				$time_line_report[3]['data'] = array();
				$time_line_report[3]['color'] = '#dd4b39';
				// $tmp_car_booking = $this->car_model->get_monthly_booking_summary();
				// $tmp_car_booking ='';
				// $month_index_car = index_month_number($tmp_car_booking);
			}
			if(!empty($active_sightseeing)){
				$module_total_earning[4]['activity'] = 'Activities';
				$module_total_earning[4]['y'] = 0;
				$time_line_report[4]['name'] = 'Activities';
				$time_line_report[4]['data'] = array();
				$time_line_report[4]['color'] = '#ff9800';
				// $tmp_sightseeing_booking = $this->sightseeing_model->get_monthly_booking_summary();
				// $month_index_sightseeing = index_month_number($tmp_sightseeing_booking);
			}
			if(!empty($active_transfers)){
				$module_total_earning[5]['transfers'] = 'Transfers';
				$module_total_earning[5]['y'] = 0;
				$time_line_report[5]['name'] = 'Transfers';
				$time_line_report[5]['data'] = array();
				$time_line_report[5]['color'] = '#456F13';
				// $tmp_transfers_booking = $this->transferv1_model->get_monthly_booking_summary();
				//$month_index_transfers = index_month_number($tmp_transfers_booking);
			}


		$time_line_report_average = [];

		foreach ($time_line_interval as $k => $v) {
			// Hotel
			if ($active_hotel) {
				$booking = $month_index_hotel[$k] ?? ['total_booking' => 0, 'monthly_earning' => 0];
				$monthly_hotel_booking[$k] = (int) $booking['total_booking'];
				$hotel_earning[$k] = round($booking['monthly_earning']);
				$module_total_earning[0]['y'] += $hotel_earning[$k];
				$time_line_report_average[$k] = ($time_line_report_average[$k] ?? 0) + $hotel_earning[$k];
			}

			// Flight
			if ($active_airline) {
				$booking = $month_index_flight[$k] ?? ['total_booking' => 0, 'monthly_earning' => 0];
				$monthly_flight_booking[$k] = (int) $booking['total_booking'];
				$flight_earning[$k] = round($booking['monthly_earning']);
				$module_total_earning[1]['y'] += $flight_earning[$k];
				$time_line_report_average[$k] = ($time_line_report_average[$k] ?? 0) + $flight_earning[$k];
			}

			// Car
			if ($active_car) {
				$monthly_car_booking[$k] = 0;
				$car_earning[$k] = 0;
			}
		}

		foreach ($time_line_report_average as $k => $v) {
			$time_line_report_average[$k] = round($v / 3);
		}

		// Prepare Highcharts-compatible arrays
		if ($active_hotel) {
			$time_line_report[0]['data'] = $monthly_hotel_booking;
			$group_time_line_report[] = ['type' => 'column', 'name' => 'Hotel', 'data' => $hotel_earning, 'color' => $time_line_report[0]['color']];
		}
		if ($active_airline) {
			$time_line_report[1]['data'] = $monthly_flight_booking;
			$group_time_line_report[] = ['type' => 'column', 'name' => 'Flight', 'data' => $flight_earning, 'color' => $time_line_report[1]['color']];
		}
	
		if ($active_car) {
			$time_line_report[3]['data'] = $monthly_car_booking;
			$group_time_line_report[] = ['type' => 'column', 'name' => 'Car', 'data' => $car_earning, 'color' => $time_line_report[3]['color']];
		}

		$max_count = max(array_merge($monthly_hotel_booking, $monthly_flight_booking, $monthly_car_booking));

		$group_time_line_report[] = [
			'type' => 'spline',
			'name' => 'Average',
			'data' => $time_line_report_average,
			'marker' => [
				'lineColor' => '#e65100',
				'color' => '#ff5722',
				'lineWidth' => 2,
				'fillColor' => '#FFF'
			]
		];

		$page_data = [
			'group_time_line_report' => $group_time_line_report,
			'module_total_earning' => $module_total_earning,
			'time_line_interval' => $time_line_interval,
			'max_count' => $max_count,
			'time_line_report' => $time_line_report,
			'time_line_report_average' => $time_line_report_average
		];

		// Booking counts
		if ($active_hotel) {
			$page_data['hotel_booking_count'] = $this->hotel_model->booking($condition, true);
		}
		if ($active_airline) {
			$page_data['flight_booking_count'] = $this->flight_model->booking($condition, true);
		}
	
		if ($active_car) {
			$page_data['car_booking_count'] = $this->car_model->booking($condition, true);
		}

		// Recent transactions
		$latest_transaction = $this->transaction_model->logs([], false, 0, 5);
		$latest_transaction = $this->booking_data_formatter->format_recent_transactions($latest_transaction, 'b2b');
		$page_data['latest_transaction'] = $latest_transaction['data']['transaction_details'];

			/********************************** SEARCH ENGINE START **********************************/
			/*Package Data*/
			if(is_active_package_module()) {
				//$data['caption'] = $this->Package_Model->getPageCaption('tours_packages')->row();
				//$data['packages'] = $this->Package_Model->getAllPackages();
				//$data['countries'] = $this->Package_Model->getPackageCountries_new();
				//$data['package_types'] = $this->Package_Model->getPackageTypes();
				//$page_data['holiday_data'] = $data; //Package Data
				$currency_obj = new Currency(array('module_type' => 'hotel','from' => get_api_data_currency(), 'to' => get_application_currency_preference()));
				$page_data['currency_obj'] = $currency_obj;
			}
			$page_data['default_view'] = $_GET['default_view'];
			/*Banner_Images */
			$domain_origin = get_domain_auth_id();
			$page_data['banner_images'] = $this->custom_db->single_table_records('banner_images', 'image', array('added_by' => $domain_origin));
			if (!empty($active_hotel)) {
				$this->load->model('hotel_model');
				$page_data['top_destination_hotel'] = $this->hotel_model->hotel_top_destinations();
			}
			$page_data['search_engine'] = $this->template->isolated_view('menu/index', $page_data);
			/********************************** SEARCH ENGINE END **********************************/
			$this->template->view('menu/dashboard', $page_data);
		}
	}
}