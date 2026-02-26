<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab
 * @subpackage General
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */

class General extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		//$this->output->enable_profiler(TRUE);
		$this->load->model('user_model');
		$this->load->model('custom_db');
	}
function index(string $default_view = ''): void
{
    if (is_logged_in_user()) {
        redirect('menu/index');
        return;
    }
    // Show login view
    echo $this->template->view('general/login', []);
}

	/**
	 * Set Search ID in cookie
	 */
	private function save_search_cookie(string $module, string $search_id): void
{
    $sparam = $this->input->cookie('sparam', true);

    if (!empty($sparam)) {
        $sparam = unserialize($sparam);
    }
    
    if (empty($sparam)) {
        $sparam = [];
    }

    $sparam[$module] = $search_id;

    $cookie = [
        'name'   => 'sparam',
        'value'  => serialize($sparam),
        'expire' => '86500',
        'path'   => PROJECT_COOKIE_PATH
    ];

    $this->input->set_cookie($cookie);
}

	/**
	 * Pre Search For Flight
	 */
	function pre_flight_search(string $search_id = ''): void
	{
		// Global search data
		$search_id = $this->save_pre_search(META_AIRLINE_COURSE);
		$this->save_search_cookie(META_AIRLINE_COURSE, $search_id);

		// Analytics
		$this->load->model('flight_model');
		$search_params = $this->input->get();
		$this->flight_model->save_search_data($search_params, META_AIRLINE_COURSE);

		redirect('flight/search/' . $search_id . '?' . $_SERVER['QUERY_STRING']);
	}
	/**
	 * Pre Search For Hotel
	 */
	function pre_hotel_search(string $search_id = ''): void
	{
		// Global search data
		$search_id = $this->save_pre_search(META_ACCOMODATION_COURSE);
		$this->save_search_cookie(META_ACCOMODATION_COURSE, $search_id);

		// Analytics
		$this->load->model('hotel_model');
		$search_params = $this->input->get();
		$this->hotel_model->save_search_data($search_params, META_ACCOMODATION_COURSE);

		redirect('hotel/search/' . $search_id . '?' . $_SERVER['QUERY_STRING']);
	}
	/**
	 * Pre Search For Hotel Details
	 */
	function pre_hotel_details_search(string $search_id = ''): void
	{
		// Global search data
		$search_id = $this->save_pre_search(META_ACCOMODATION_COURSE);
		$this->save_search_cookie(META_ACCOMODATION_COURSE, $search_id);

		// Analytics
		$this->load->model('hotel_model');
		$search_params = $this->input->get();
		$this->hotel_model->save_search_data($search_params, META_ACCOMODATION_COURSE);

		redirect('hotel/hotel_details_search/' . $search_id . '?' . $_SERVER['QUERY_STRING']);
	}
	/**
	 * Pre Search for Sightseeing
	 */
	function pre_sight_seen_search(string $search_id = ''): void
	{
		$search_id = $this->save_pre_search(META_SIGHTSEEING_COURSE);
		$this->save_search_cookie(META_SIGHTSEEING_COURSE, $search_id);

		// Analytics
		$this->load->model('sightseeing_model');
		$search_params = $this->input->get();
		$this->sightseeing_model->save_search_data($search_params, META_SIGHTSEEING_COURSE);

		redirect('sightseeing/search/' . $search_id . '?' . $_SERVER['QUERY_STRING']);
	}
	/**
	 * Pre TransferV1 Search
	 */
	function pre_transferv1_search(string $search_id = ''): void
	{
		$search_id = $this->save_pre_search(META_TRANSFERV1_COURSE);
		$this->save_search_cookie(META_TRANSFERV1_COURSE, $search_id);

		// Analytics
		$this->load->model('transferv1_model');
		$search_params = $this->input->get();
		$this->transferv1_model->save_search_data($search_params, META_TRANSFERV1_COURSE);

		redirect('transferv1/search/' . $search_id . '?' . $_SERVER['QUERY_STRING']);
	}
	/**
	 * Pre Search For Bus
	 */
	function pre_bus_search(string $search_id = ''): void
	{
		// Global search data
		$search_id = $this->save_pre_search(META_BUS_COURSE);
		$this->save_search_cookie(META_BUS_COURSE, $search_id);

		// Analytics
		$this->load->model('bus_model');
		$search_params = $this->input->get();
		$this->bus_model->save_search_data($search_params, META_BUS_COURSE);

		redirect('bus/search/' . $search_id . '?' . $_SERVER['QUERY_STRING']);
	}
	/**
	 * Pre Search For Car
	 */
	function pre_car_search(string $search_id = ''): void
	{
		$search_params = $this->input->get();

		// Global search data
		$search_id = $this->save_pre_search(META_CAR_COURSE);
		$this->save_search_cookie(META_CAR_COURSE, $search_id);

		// Analytics
		$this->load->model('car_model');
		$this->car_model->save_search_data($search_params, META_CAR_COURSE);

		redirect('car/search/' . $search_id . '?' . $_SERVER['QUERY_STRING']);
	}
	/**
	 * Pre Search For Packages
	 */
	function pre_package_search(string $search_id = ''): void
	{
		// Global search data
		$search_id = $this->save_pre_search(META_PACKAGE_COURSE);

		redirect('tours/search/' . $search_id . '?' . $_SERVER['QUERY_STRING']);
	}
	/**
	 * Pre Search used to save the data
	 */
	private function save_pre_search(string $search_type): int
	{
		// Save data
		$search_params = $this->input->get();

		// Encode search parameters to JSON
		$search_data = json_encode($search_params);
		// Insert the record into the database
		$insert_id = $this->custom_db->insert_record('search_history', [
			'search_type'    => $search_type,
			'search_data'    => $search_data,
			'created_datetime' => date('Y-m-d H:i:s')
		]);

		// Return the inserted record's ID
		return (int) $insert_id['insert_id']; // Ensure return is an integer
	}
	/**
	 * Logout function for logout from account and unset all the session variables
	 */
	function initilize_logout(): void
	{
		if (is_logged_in_user()) {
			$this->user_model->update_login_manager($this->session->userdata(LOGIN_POINTER));

			// Unset session data
			$this->session->unset_userdata([
				AUTH_USER_POINTER => '',
				LOGIN_POINTER     => '',
				DOMAIN_AUTH_ID    => '',
				DOMAIN_KEY        => ''
			]);

			// Redirect to the login page
			redirect('general/index');
		}
	}
	/**
	 * Oops page of application will be loaded here
	 */
	public function ooops(): void
	{
		$this->template->view('utilities/404.php');
	}

	/**
	 * Email Subscription
	 */
	public function email_subscription(): void
{
    $data = $this->input->get();

    $mail = $data['email'] ?? '';
    $domain_key = get_domain_auth_id();

    $inserted_id = $this->user_model->email_subscribtion($mail, $domain_key);

    if (isset($inserted_id) && $inserted_id != "already") {
        echo "success";
        return;
    }
    
    if ($inserted_id == "already") {
        echo "already";
        return;
    }

    echo "failed";
}

	/**
	 * Booking Not Allowed Popup
	 */
	public function booking_not_allowed(): void
	{
		$this->template->view('general/booking_not_allowed');
	}

	/**
	 * Test function to retrieve and format flight booking data
	 */
	public function test(string $app_reference): void
	{
		$this->load->model('flight_model');
		$this->load->library('booking_data_formatter');

		// Fetch booking details
		$booking_data = $this->flight_model->get_booking_details($app_reference, '');

		// Format the booking data
		$formatted_booking_data = $this->booking_data_formatter->format_flight_booking_data($booking_data, 'b2b');

		// Extract agent buying price from the formatted data
		$amount = $formatted_booking_data['data']['booking_details'][0]['agent_buying_price'];

		// You can return $amount or perform other actions if needed
	}
}
