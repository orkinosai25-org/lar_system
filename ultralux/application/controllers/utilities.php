<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab - Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */

class Utilities extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Active Notification Count
	 */
	function active_notifications_count(): void
	{
		// Retrieve the GET data
		$get_data = $this->input->get();

		// Prepare the response structure
		$response = [
			'status' => SUCCESS_STATUS,
			'data'   => [],
			'msg'    => ''
		];

		// Deactivate the notification if the flag is set
		if (isset($get_data['deactive_notification']) && $get_data['deactive_notification'] == 1) {
			$this->application_logger->disable_active_event_notification();
		}

		// Define the condition (empty in this case)
		$condition = [];

		// Get the active notifications count
		$active_notifications_count = $this->application_logger->active_notifications_count($condition);

		// Add the count to the response data
		$response['data']['active_notifications_count'] = (int) $active_notifications_count;

		// Send the JSON response
		header('Content-type: application/json');
		echo json_encode($response);
		exit();
	}
	/**
	 * Notification Alerts
	 */
	function events_notification(): void
	{
		$page_data = [];
		$response = [
			'status' => FAILURE_STATUS,
			'data'   => [],
			'msg'    => ''
		];

		$oe_start = 0;
		$event_limit = 10;

		// Get the notification list
		$notification_list = $this->application_logger->get_events_notification($oe_start, $event_limit);

		// Check if notification list is valid
		if (valid_array($notification_list)) {
			$page_data['list'] = $notification_list;

			// Compress the notification list output and add to the response
			$response['data']['notification_list'] = get_compressed_output($this->template->isolated_view('utilities/events_notification', $page_data));

			// Set status to success
			$response['status'] = SUCCESS_STATUS;
		}

		debug($response); die;

		// Send JSON response
		header('Content-type: application/json');
		echo json_encode($response);
		exit();
	}
	/**
	 * All Notification List
	 */
	function notification_list(int $offset = 0): void
	{
		$page_data = [];
		$condition = [];
		$config  = [];

		// Get total records and notification list
		$total_records = $this->application_logger->get_events_notification($offset, RECORDS_RANGE_3, $condition, true);
		$page_data['list'] = $this->application_logger->get_events_notification($offset, RECORDS_RANGE_3, $condition, false);

		// Pagination setup
		$this->load->library('pagination');

		// Build the query string for pagination
		if (count($_GET) > 0) {
			$config['suffix'] = '?' . http_build_query($_GET, '', "&");
		}

		$config['base_url'] = base_url() . 'index.php/utilities/notification_list/';
		$config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
		$config['total_rows'] = $total_records->total;
		$config['per_page'] = RECORDS_RANGE_3;

		// Initialize pagination
		$this->pagination->initialize($config);

		// Add total row count to page data
		$page_data['total_rows'] = $total_records->total;

		// Load the view with the page data
		$this->template->view('utilities/notification_list', $page_data);
	}
}
