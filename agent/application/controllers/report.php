<?php
//error_reporting(E_ALL);
//ini_set('display_errors',1);

if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab - Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */

class Report extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('hotel_model');
		$this->load->model('flight_model');
		$this->load->model('car_model');
		$this->load->library('booking_data_formatter');
	}

	/**
	 * Display monthly booking report
	 * @return void
	 */
	public function monthly_booking_report(): void
	{
		$this->template->view('report/monthly_booking_report');
	}

	/**
	 * Index function, redirecting to the flight report
	 * @param int $offset
	 * @return void
	 */
	public function index(int $offset = 0): void
	{
		$this->flight($offset);
	}
	/**
	 * Display hotel booking report
	 * @param int $offset
	 * @return void
	 */
	public function hotel(int $offset = 0): void
	{
		$config = [];
		$get_data = $this->input->get();
		// Initialize dropdown as "Today"
		$dropdown = "Today";

		// Determine the date range based on the filter
		if ((isset($get_data['from']) && !empty($get_data['from'])) || (isset($get_data['to']) && !empty($get_data['to']))) {
			$dropdown = "Custom Date Range";
		}

		// Filter by "Today" booking data
		if (isset($get_data['filter']) && $get_data['filter'] == "today_booking_data") {
			$today_search = date('Y-m-d');
			$get_data['today_booking_data'] = $today_search;
			$dropdown = "Today";
		}

		// Filter by "Last 7 Days"
		if (isset($get_data['filter']) && $get_data['filter'] == "prev_booking_data_7days") {
			$last_today_search = date('Y-m-d', strtotime('-7 days'));
			$get_data['prev_booking_data'] = $last_today_search;
			$dropdown = "Last 7 Days";
		}

		// Filter by "This Month"
		if (isset($get_data['filter']) && $get_data['filter'] == "prev_booking_data_month") {
			$last_month = date('Y-m-d', strtotime('-30 days'));
			$get_data['prev_booking_data'] = $last_month;
			$dropdown = "This Month";
		}

		// Filter by "Last 3 Months"
		if (isset($get_data['filter']) && $get_data['filter'] == "prev_booking_data_3months") {
			$last_3months = date('Y-m-d', strtotime('-90 days'));
			$get_data['prev_booking_data'] = $last_3months;
			$dropdown = "Last 3 Months";
		}

		// Prepare the conditions for the report
		$page_data = [];
		$condition = [];
		$filter_data = $this->format_basic_search_filters();
		$page_data['from_date'] = $filter_data['from_date'];
		$page_data['to_date'] = $filter_data['to_date'];
		$condition = $filter_data['filter_condition'];

		// Check for filter report data
		if (isset($get_data['filter_report_data']) && !empty($get_data['filter_report_data'])) {
			$filter_report_data = trim($get_data['filter_report_data']);
			$search_filter_condition = '(BD.app_reference LIKE "%' . $filter_report_data . '%" OR BD.confirmation_reference LIKE "%' . $filter_report_data . '%")';
			$total_records = $this->hotel_model->filter_booking_report($search_filter_condition, true);
			$table_data = $this->hotel_model->filter_booking_report($search_filter_condition, false, $offset, RECORDS_RANGE_2);
		} else {
			$total_records = $this->hotel_model->booking($condition, true);
			$table_data = $this->hotel_model->booking($condition, false, $offset, RECORDS_RANGE_2);
		}

		// Format the booking data
		$table_data = $this->booking_data_formatter->format_hotel_booking_data($table_data, 'b2b');
		$page_data['table_data'] = $table_data['data'];
		$page_data['dropdown'] = $dropdown;

		// Set up pagination
		$this->load->library('pagination');
		if (count($_GET) > 0) {
			$config['suffix'] = '?' . http_build_query($_GET, '', "&");
		}
		$config['base_url'] = base_url() . 'index.php/report/hotel/';
		$config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
		$page_data['total_rows'] = $config['total_rows'] = $total_records;
		$config['per_page'] = RECORDS_RANGE_2;
		$this->pagination->initialize($config);

		// Set pagination data
		$page_data['total_records'] = $config['total_rows'];

		// Load the view
		$this->template->view('report/hotel_new_report', $page_data);
	}



	/**
	 * Flight Report
	 *
	 * @param int $offset
	 * @return void
	 */
	public function flight(int $offset = 0): void
	{
		$config = [];
		$get_data = $this->input->get();
		$page_data = [];
		$condition = [];
		$filter_data = $this->format_basic_search_filters();

		// Set filter dates
		$page_data['from_date'] = $filter_data['from_date'];
		$page_data['to_date'] = $filter_data['to_date'];
		$condition = $filter_data['filter_condition'];

		// Initialize dropdown
		$dropdown = "Today";

		// Custom date range filter check
		if ((isset($get_data['from']) && !empty($get_data['from'])) || (isset($get_data['to']) && !empty($get_data['to']))) {
			$dropdown = "Custom Date Range";
		}

		// Today filter
		if (isset($get_data['filter']) && $get_data['filter'] == "today_booking_data") {
			$today_search = date('Y-m-d');
			$get_data['today_booking_data'] = $today_search;
			$dropdown = "Today";
		}

		// Last 7 days filter
		if (isset($get_data['filter']) && $get_data['filter'] == "prev_booking_data_7days") {
			$last_today_search = date('Y-m-d', strtotime('-7 days'));
			$get_data['prev_booking_data'] = $last_today_search;
			$dropdown = "Last 7 Days";
		}

		// This month filter
		if (isset($get_data['filter']) && $get_data['filter'] == "prev_booking_data_month") {
			$last_month = date('Y-m-d', strtotime('-30 days'));
			$get_data['prev_booking_data'] = $last_month;
			$dropdown = "This Month";
		}

		// Last 3 months filter
		if (isset($get_data['filter']) && $get_data['filter'] == "prev_booking_data_3months") {
			$last_3months = date('Y-m-d', strtotime('-90 days'));
			$get_data['prev_booking_data'] = $last_3months;
			$dropdown = "Last 3 Months";
		}

		// Apply search filter condition if any
		if (isset($get_data['filter_report_data']) && !empty($get_data['filter_report_data'])) {
			$filter_report_data = trim($get_data['filter_report_data']);
			$search_filter_condition = '(TD.app_reference LIKE "%' . $filter_report_data . '%" OR TD.pnr LIKE "%' . $filter_report_data . '%")';
			$total_records = $this->flight_model->filter_booking_report($search_filter_condition, true);
			$table_data = $this->flight_model->filter_booking_report($search_filter_condition);
		} else {
			// Get the booking report without the filter
			$total_records = $this->flight_model->booking($condition, true);
			$table_data = $this->flight_model->booking($condition, false, $offset, RECORDS_RANGE_2);
		}

		// Format the flight booking data
		$table_data = $this->booking_data_formatter->format_flight_booking_data($table_data, 'b2b');

		// Prepare page data
		$page_data['table_data'] = $table_data['data'];
		$page_data['dropdown'] = $dropdown;

		// Setup pagination
		$this->load->library('pagination');
		if (count($_GET) > 0) {
			$config['suffix'] = '?' . http_build_query($_GET, '', "&");
		}
		$config['base_url'] = base_url() . 'index.php/report/flight/';
		$config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
		$page_data['total_rows'] = $config['total_rows'] = $total_records;
		$config['per_page'] = RECORDS_RANGE_2;
		$this->pagination->initialize($config);

		// Set pagination data
		$page_data['total_records'] = $config['total_rows'];

		// Render the view with page data
		$this->template->view('report/airline', $page_data);
	}



	/**
	 * Car Report
	 *
	 * @param int $offset
	 * @return void
	 */
	public function car(int $offset = 0): void
	{
		$config = [];
		// Get input data
		$get_data = $this->input->get();
		$page_data = [];
		$condition = [];

		// Format basic search filters
		$filter_data = $this->format_basic_search_filters();
		$page_data['from_date'] = $filter_data['from_date'];
		$page_data['to_date'] = $filter_data['to_date'];
		$condition = $filter_data['filter_condition'];

		// Set dropdown label
		$dropdown = "Today";

		// Check for custom date range
		if ((isset($get_data['from']) && !empty($get_data['from'])) || (isset($get_data['to']) && !empty($get_data['to']))) {
			$dropdown = "Custom Date Range";
		}

		// Check for specific filter values
		if (isset($get_data['filter']) && $get_data['filter'] == "today_booking_data") {
			$today_search = date('Y-m-d');
			$get_data['today_booking_data'] = $today_search;
			$dropdown = "Today";
		}

		if (isset($get_data['filter']) && $get_data['filter'] == "prev_booking_data_7days") {
			$last_today_search = date('Y-m-d', strtotime('-7 days'));
			$get_data['prev_booking_data'] = $last_today_search;
			$dropdown = "Last 7 Days";
		}

		if (isset($get_data['filter']) && $get_data['filter'] == "prev_booking_data_month") {
			$last_month = date('Y-m-d', strtotime('-30 days'));
			$get_data['prev_booking_data'] = $last_month;
			$dropdown = "This Month";
		}

		if (isset($get_data['filter']) && $get_data['filter'] == "prev_booking_data_3months") {
			$last_3months = date('Y-m-d', strtotime('-90 days'));
			$get_data['prev_booking_data'] = $last_3months;
			$dropdown = "Last 3 Months";
		}

		// Apply search filter condition if any
		if (isset($get_data['filter_report_data']) && !empty($get_data['filter_report_data'])) {
			$filter_report_data = trim($get_data['filter_report_data']);
			$search_filter_condition = '(TD.app_reference LIKE "%' . $filter_report_data . '%" OR TD.pnr LIKE "%' . $filter_report_data . '%")';
			$total_records = $this->car_model->filter_booking_report($search_filter_condition, true);
			$table_data = $this->car_model->filter_booking_report($search_filter_condition);
		} else {
			// Get the booking report without the filter
			$total_records = $this->car_model->booking($condition, true);
			$table_data = $this->car_model->booking($condition, false, $offset, RECORDS_RANGE_2);
		}

		// Format the car booking data
		$table_data = $this->booking_data_formatter->format_car_booking_datas($table_data, 'b2c');

		// Prepare page data
		$page_data['table_data'] = $table_data['data'];
		$page_data['dropdown'] = $dropdown;

		// Setup pagination
		$this->load->library('pagination');
		if (count($_GET) > 0) {
			$config['suffix'] = '?' . http_build_query($_GET, '', "&");
		}
		$config['base_url'] = base_url() . 'index.php/report/car/';
		$config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
		$page_data['total_rows'] = $config['total_rows'] = $total_records;
		$config['per_page'] = RECORDS_RANGE_2;
		$this->pagination->initialize($config);

		// Set pagination data
		$page_data['total_records'] = $config['total_rows'];
		$page_data['customer_email'] = $this->entity_email;

		// Render the view with page data
		$this->template->view('report/car_new_report', $page_data);
	}


	/**
	 * Sightseeing Report
	 *
	 * @param int $offset
	 * @return void
	 */


	/**
	 * Transfers Report
	 *
	 * @param int $offset
	 * @return void
	 */
	
	/**
	 * Package redirect function
	 *
	 * @param int $offset
	 * @return void*/
	 


	/**
	 * Format Basic Search Filters
	 *
	 * @return array
	 */
	private function format_basic_search_filters(): array
	{
		$get_data = $this->input->get();
		if (isset($get_data['filter']) && $get_data['filter'] == "today_booking_data") {
			$today_search = date('Y-m-d');
			$get_data['today_booking_data'] = $today_search;
		}
		if (isset($get_data['filter']) && $get_data['filter'] == "prev_booking_data_7days") {
			$last_today_search = date('Y-m-d', strtotime('-7 day'));
			$get_data['prev_booking_data'] = $last_today_search;
		}
		if (isset($get_data['filter']) && $get_data['filter'] == "prev_booking_data_month") {
			$last_month = date('Y-m-d', strtotime('-30 day'));
			$get_data['prev_booking_data'] = $last_month;
		}
		if (isset($get_data['filter']) && $get_data['filter'] == "prev_booking_data_3months") {
			$last_3months = date('Y-m-d', strtotime('-90 day'));
			$get_data['prev_booking_data'] = $last_3months;
		}

		if (valid_array($get_data)) {
			$filter_condition = [];

			// From-Date and To-Date
			$from_date = trim($get_data['from_date']);
			$to_date = trim($get_data['to_date']);

			// Auto swipe date
			if (!empty($from_date) && !empty($to_date)) {
				$valid_dates = auto_swipe_dates($from_date, $to_date);
				$from_date = $valid_dates['from_date'];
				$to_date = $valid_dates['to_date'];
			}

			if (!empty($from_date)) {
				$filter_condition[] = ['DATE(BD.created_datetime)', '>=', '"' . date('Y-m-d', strtotime($from_date)) . '"'];
			}
			if (!empty($to_date)) {
				$filter_condition[] = ['DATE(BD.created_datetime)', '<=', '"' . date('Y-m-d', strtotime($to_date)) . '"'];
			}

			// App reference
			if (isset($get_data['app_reference']) && !empty($get_data['app_reference'])) {
				$filter_condition[] = ['BD.app_reference', '=', '"' . trim($get_data['app_reference']) . '"'];
			}

			// Booking Status
			if (isset($get_data['filter_booking_status'])) {
				$status = $get_data['filter_booking_status'];
				if ($status == 'BOOKING_CONFIRMED') {
					$filter_condition[] = ['BD.status', '=', '"BOOKING_CONFIRMED"'];
				} elseif ($status == 'BOOKING_PENDING') {
					$filter_condition[] = ['BD.status', '=', '"BOOKING_PENDING"'];
				} elseif ($status == 'BOOKING_CANCELLED') {
					$filter_condition[] = ['BD.status', '=', '"BOOKING_CANCELLED"'];
				}
			}

			// Today's Booking Data
			if (isset($get_data['today_booking_data']) && !empty($get_data['today_booking_data'])) {
				$filter_condition[] = ['DATE(BD.created_datetime)', '=', '"' . date('Y-m-d') . '"'];
			}

			// Last day Booking Data
			if (isset($get_data['last_day_booking_data']) && !empty($get_data['last_day_booking_data'])) {
				$filter_condition[] = ['DATE(BD.created_datetime)', '=', '"' . trim($get_data['last_day_booking_data']) . '"'];
			}

			// Previous Booking Data
			if (isset($get_data['prev_booking_data']) && !empty($get_data['prev_booking_data'])) {
				$filter_condition[] = ['DATE(BD.created_datetime)', '>=', '"' . trim($get_data['prev_booking_data']) . '"'];
			}

			// Daily Sales Report
			if (isset($get_data['daily_sales_report']) && $get_data['daily_sales_report'] == ACTIVE) {
				$from_date = date('d-m-Y', strtotime('-1 day'));
				$to_date = date('d-m-Y');
				$filter_condition[] = ['DATE(BD.created_datetime)', '>=', '"' . date('Y-m-d', strtotime($from_date)) . '"'];
				$filter_condition[] = ['DATE(BD.created_datetime)', '<=', '"' . date('Y-m-d', strtotime($to_date)) . '"'];
			}

			return ['filter_condition' => $filter_condition, 'from_date' => $from_date, 'to_date' => $to_date];
		} else {
			$filter_condition = [['DATE(BD.created_datetime)', '=', '"' . date('Y-m-d') . '"']];
			return ['filter_condition' => $filter_condition, 'from_date' => '', 'to_date' => ''];
		}
	}

	/**
	 * Package Enquiries
	 *
	 * @return void
	 */
}
