<?php
error_reporting(E_ALL);
      ini_set('display_errors', 1);
//ini_set('display_errors', 1);
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 *
 * @package    Provab
 * @subpackage General
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */
class General extends CI_Controller {
   
    
    public function __construct() {
        parent::__construct();
        //$this->output->enable_profiler(TRUE);
        $this->load->model('user_model');
        $this->load->model('custom_db');
    }

    function test1() {
        $post = $this->input->post();
        $post['lang'] = 'hi';
        $this->session->set_userdata('lang', $post['lang']);
        //echo $this->session->userdata('some_name');
    }

    /**
     * index page of application will be loaded here
     */
  public function index(string $default_view = ''): void
  {
    $data = [];
    $page_data = [];
    $domain_origin = get_domain_auth_id();
    $page_data['banner_images'] = $this->custom_db->single_table_records('banner_images','*',['added_by' => $domain_origin, 'status' => '1'],0,100000000,['banner_order' => 'ASC']
    );
    $page_data['default_view'] = $_GET['default_view'] ?? $default_view;
    $page_data['holiday_data'] = $data;

    if (is_active_airline_module()) {
      $this->load->model('flight_model');
    }
    if (is_active_hotel_module()) {
      $this->load->model('hotel_model');
      $checkin_date = date('d-m-Y', strtotime('+7 days'));
      $checkout_date = date('d-m-Y', strtotime('+11 days'));

      $page_data['top_destination_hotel'] = $this->hotel_model->hotel_top_destinations();

      // Hotel search parameters
      $hotel_search_params = ['city' => 'Bangalore (India)','hotel_destination' => '6743','location' => '','radius' => 1,'latitude' => '','longitude' => '','countrycode' => '','search_type' => 'city_search','hotel_checkin' => $checkin_date,'hotel_checkout' => $checkout_date,'rooms' => 1,'adult' => [2],'child' => [0],'childAge_1' => [0],
      ];

      $page_data['hotel_search_url'] = base_url('index.php/general/pre_hotel_search?' . http_build_query($hotel_search_params));

      // Flight search parameters
      $departure_date = date('d-m-Y', strtotime('+7 days'));
      $flight_params = ['trip_type' => 'oneway','from' => 'Bangalore, Bengaluru International Airport (BLR)','from_loc_id' => 801,'to' => 'Dubai, Dubai International Airport (DXB)','to_loc_id' => 1921,'depature' => $departure_date,'v_class' => 'PremiumEconomy','carrier' => [''],'adult' => 1,'child' => 0,'infant' => 0,'from_loc_airport_name' => 'Bengaluru International Airport','to_loc_airport_name' => 'Dubai International Airport'
      ];

      $page_data['flight_search_url'] = base_url('index.php/general/pre_flight_search?' . http_build_query($flight_params));

      // Car search parameters
      $return_date = date('d-m-Y', strtotime('+9 days'));
      $car_params = ['car_from' => 'Dubai City/Downtown,United Arab Emirates','from_loc_id' => 'DXB','car_from_loc_code' => 'DXB','car_to' => 'Dubai City/Downtown,United Arab Emirates','to_loc_id' => 'DXB','car_to_loc_code' => 'DXB','depature' => $departure_date,'depature_time' => '13:00','return' => $return_date,'return_time' => '11:30','driver_age' => 35,'country' => 'IN','search_flight' => 'search'
      ];

      $page_data['car_search_url'] = base_url('index.php/general/pre_car_search?' . http_build_query($car_params));
      $page_data['holidays_search_url'] = base_url('tours/search?country=&package_type=&duration=&budget=');
    }

    if (is_active_car_module()) {
      $this->load->model('car_model');
    }
    $currency_obj = new Currency([
      'module_type' => 'hotel',
      'from' => get_api_data_currency(),
      'to' => get_application_currency_preference()
    ]);
    $page_data['currency_obj'] = $currency_obj;

    $getSlideImages = $page_data['banner_images']['data'] ?? [];
    $slideImageArray = array_map(function ($item) {
      return [
        'image' => $this->template->template_images() . $item['image'],
        'title' => $item['title'],
        'description' => $item['subtitle']
      ];
    }, $getSlideImages);

    $page_data['slideImageJson'] = $slideImageArray;
    $page_data['promo_code_list'] = $this->get_promocode_list();

    //$headings = $this->custom_db->single_table_records('home_page_headings', '*', ['status' => '1']);
    //$top_airlines = $this->custom_db->single_table_records('top_airlines', '*', ['status' => '1']);
    //$tour_styles = $this->custom_db->single_table_records('tour_styles', '*', ['status' => '1']);
    $domain_data = $this->custom_db->single_table_records('domain_list', '*', ['status' => '1']);
    //$features = $this->custom_db->single_table_records('why_choose_us', '*', ['status' => '1']);

    $headings_array = [];
    // if (!empty($headings['status']) && is_array($headings['data'])) {
    //   foreach ($headings['data'] as $heading) {
    //     $headings_array[] = $heading['title'];
    //   }
    // }
    $page_data['headings'] = $headings_array;
    $page_data['top_airlines'] = [];
    $page_data['features'] = [];
    //$page_data['tour_styles'] = $tour_styles;
    $page_data['domain_data'] = $domain_data;
    $page_data['hotel_search_params'] = [];

    $this->template->view('general/index', $page_data);
  }


  /**
   * Set Search id in cookie
   */
  private function save_search_cookie(string $module, $search_id): void
  {
    $sparam = [];

    // Fetch and safely unserialize cookie
    $cookie_data = $this->input->cookie('sparam', true);
    if (!empty($cookie_data)) {
      $sparam = unserialize($cookie_data);

      // Make sure it's an array
      if (!is_array($sparam)) {
        $sparam = [];
      }
    }

    $sparam[$module] = $search_id;

    $cookie = [
      'name'   => 'sparam',
      'value'  => serialize($sparam),
      'expire' => 86500, // numeric, not string
      'path'   => PROJECT_COOKIE_PATH
    ];

    $this->input->set_cookie($cookie);
  }

    /**
     * Pre Search For Flight
     */
  public function pre_flight_search(string $search_id = ''): void
  {
    $search_params = $this->input->get();
    // Save global search data
    $search_id = $this->save_pre_search(META_AIRLINE_COURSE);
    $this->save_search_cookie(META_AIRLINE_COURSE, $search_id);

    // Analytics tracking
    $this->load->model('flight_model');
    $this->flight_model->save_search_data($search_params, META_AIRLINE_COURSE);

    // Redirect to actual search
    $query_string = $_SERVER['QUERY_STRING'] ?? '';
    redirect('flight/search/' . $search_id . ($query_string ? '?' . $query_string : ''));
  }
    /**
     * Pre Search For Car
     */
  public function pre_car_search(string $search_id = ''): void
  {
    // Get all GET parameters
    $search_params = $this->input->get() ?? [];

    // Save global search and set cookie
    $search_id = $this->save_pre_search(META_CAR_COURSE);
    $this->save_search_cookie(META_CAR_COURSE, $search_id);

    // Load car model and save analytics/search data
    $this->load->model('car_model');
    $this->car_model->save_search_data($search_params, META_CAR_COURSE);

    // Redirect to search results with original query string
    $query_string = $_SERVER['QUERY_STRING'] ?? '';
    redirect('car/search/' . $search_id . ($query_string ? '?' . $query_string : ''));
  }
    /**
     * Pre Search For Hotel
     */
  public function pre_hotel_search(string $search_id = ''): void
  {
    // Save global hotel search data and set cookie
    $search_id = $this->save_pre_search(META_ACCOMODATION_COURSE);
    $this->save_search_cookie(META_ACCOMODATION_COURSE, $search_id);

    // Load model and save search analytics
    $this->load->model('hotel_model');
    $search_params = $this->input->get() ?? [];
    $this->hotel_model->save_search_data($search_params, META_ACCOMODATION_COURSE);

    // Redirect to hotel search results with query string
    $query_string = $_SERVER['QUERY_STRING'] ?? '';

    redirect('hotel/search/' . $search_id . ($query_string ? '?' . $query_string : ''));
  }
	 /**
     * Pre Search For Hotel
     */
  public function pre_hotel_details_search(string $search_id = ''): void
  {
    // Save global hotel search data and set search cookie
    $search_id = $this->save_pre_search(META_ACCOMODATION_COURSE);
    $this->save_search_cookie(META_ACCOMODATION_COURSE, $search_id);

    // Load hotel model and capture search parameters
    $this->load->model('hotel_model');
    $search_params = $this->input->get() ?? [];

    // Save analytics/search data
    $this->hotel_model->save_search_data($search_params, META_ACCOMODATION_COURSE);

    // Build redirect URL with search ID and original query string
    $query_string = $_SERVER['QUERY_STRING'] ?? '';
    redirect('hotel/hotel_details_search/' . $search_id . ($query_string ? '?' . $query_string : ''));
  }
  /*
    
    /**
     * Pre Search used to save the data
     *
     */
  private function save_pre_search(string $search_type): int
  {
    // Get search parameters from GET request
    $search_params = $this->input->get() ?? [];

    // Encode search parameters as JSON
    $search_data = json_encode($search_params, JSON_UNESCAPED_UNICODE);

    // Prepare data for insertion
    $insert_data = [
      'search_type'       => $search_type,
      'search_data'       => $search_data,
      'created_datetime'  => date('Y-m-d H:i:s'),
    ];

    // Insert into search_history and return inserted ID
    $insert_result = $this->custom_db->insert_record('search_history', $insert_data);
    return (int) ($insert_result['insert_id'] ?? 0);
  }

    /**
     * oops page of application will be loaded here
     */
    function ooops() {
        $this->template->view('utilities/404.php');
    }

  /*
     * Activating User Account.
     * Account get activated only when the url is clicked from the account_activation_mail
     */

  function activate_account_status(): void
  {
    $origin = $this->input->get('origin');
    // Extract and decode the secure ID
    $unsecure = substr($origin, 3);
    $secure_id = base64_decode($unsecure);

    // Activate the account
    $status = ACTIVE;
    $this->load->model('user_model');
    $this->user_model->activate_account_status($status, $secure_id);

    // Redirect to home
    redirect(base_url());
  }
 

    /**
     * Email Subscribtion
     *
     */
  function email_subscription(): void
  {
    $data = $this->input->post() ?? [];

    $email = $data['subEmail'] ?? '';
    if (empty($email)) {
      echo json_encode(['status' => 2]); // Missing email
      return;
    }

    $domain_key = get_domain_auth_id();

    // Call model method to handle subscription
    $this->load->model('user_model');
    $inserted_id = $this->user_model->email_subscribtion($email, $domain_key);

    $response = ['status' => 2]; // Default: error

    if ($inserted_id == 'already') {
      $response['status'] = 0; // Already subscribed
    } elseif (!empty($inserted_id)) {
      // Successful subscription
      $this->application_logger->email_subscription($email);
      $response['status'] = 1;
    }

    echo json_encode($response);
  }

  function cms(string $page_label): void
	{

		$page_position = 'Bottom';

		if (empty($page_label)) {
			redirect('general/index');
			return;
		}

		// Fetch CMS page data
		$data = $this->custom_db->single_table_records(
			'cms_pages',
			'page_title,page_description,page_seo_title,page_seo_keyword,page_seo_description',
			[
				'page_label'    => $page_label,
				'page_position' => $page_position,
				'page_status'   => 1
			]
		);

		$this->template->view('cms/cms', $data);
	}

  function offline_payment(): void
	{
		$params = $this->input->post() ?? [];

		// Insert offline payment request
		$this->load->model('user_model');
		$result = $this->user_model->offline_payment_insert($params);

		$referenceCode = $result['refernce_code'] ?? '';

		if (empty($referenceCode)) {
			echo json_encode(['error' => 'Failed to generate reference code.']);
			return;
		}

		// You can optionally email this URL: $url = base_url('index.php/general/offline_approve/' . $referenceCode);
		echo json_encode(['reference_code' => $referenceCode]);
	}

  function offline_approve(string $code): void
  {
    $this->load->model('user_model');
    $approvalData = $this->user_model->offline_approval($code);

    if (!empty($approvalData)) {
      $this->template->view('general/pay', ['data' => $approvalData]);
    }
  }

  /**
   * Booking Not Allowed Popup
   */
  function booking_not_allowed(): void
  {
    $this->template->view('general/booking_not_allowed');
  }


  function update_citylist(): void
  {
    $insert_list = [];
    $total = 80;
    for ($num = 0; $num <= $total; $num++) {
      $city_response = file_get_contents(FCPATH . "test-export-2017-2-27/destinations-" . $num . ".json");

      $city_list = json_decode($city_response, true);
      // debug($city_list);exit;
      foreach ($city_list as $value) {
        $insert_list['country_code'] = $value['country'];
        $insert_list['city_name'] = html_entity_decode($value['name']);
        $insert_list['city_code'] = $value['code'];
        $insert_list['parent_code'] = $value['parent'];
        $insert_list['latitude']  = $value['latitude'];
        $insert_list['longitude'] = $value['longitude'];
        $this->custom_db->insert_record('hotelspro_citylist', $insert_list);
      }
    }
  }
  //get promocode
  private function get_promocode_list(): array
  {
    // Initialize an empty array for promo codes
    $promocodeArr = [];

    // Get today's date for comparison
    $date = date('Y-m-d');

    // Fetch promo codes from the database
    $list = $this->custom_db->single_table_records(
      'promo_code_list',
      '*',
      [
        'status' => ACTIVE,
        'display_home_page' => 'Yes',
        'expiry_date >=' => $date,
      ]
    );

    // Check if the fetch was successful and data is available
    if (!empty($list['status']) && $list['status'] == true && !empty($list['data'])) {
      $promocodeArr = $list['data'];
    }

    // Return the list of promo codes (empty array if none found)
    return $promocodeArr;
  }
  function insert_api_data(): void
  {
    $api_config_data = [];
    $encrypt_method = "AES-256-CBC";
    $api_data = $this->custom_db->single_table_records('email_configuration', '*');
    $secret_iv = PROVAB_SECRET_IV;
    if ($api_data['status'] == true) {
      foreach ($api_data['data'] as $data) {
        if (!empty($data['username'])) {
          $md5_key = PROVAB_MD5_SECRET;
          $encrypt_key = PROVAB_ENC_KEY;
          $decrypt_password = $this->db->query("SELECT AES_DECRYPT($encrypt_key,SHA2('" . $md5_key . "',512)) AS decrypt_data");

          $db_data = $decrypt_password->row();

          $secret_key = trim($db_data->decrypt_data);
          $key = hash('sha256', $secret_key);
          $iv_val = substr(hash('sha256', $secret_iv), 0, 16);
          $username = openssl_encrypt($data['username'], $encrypt_method, $key, 0, $iv_val);
          $username = base64_encode($username);

          $password = openssl_encrypt($data['password'], $encrypt_method, $key, 0, $iv_val);
          $password = base64_encode($password);

          $host = openssl_encrypt($data['host'], $encrypt_method, $key, 0, $iv_val);
          $host = base64_encode($host);

          $cc_val = openssl_encrypt($data['cc'], $encrypt_method, $key, 0, $iv_val);
          $cc_val = base64_encode($cc_val);

          $port = openssl_encrypt($data['port'], $encrypt_method, $key, 0, $iv_val);
          $port = base64_encode($port);

          $bcc = openssl_encrypt($data['bcc'], $encrypt_method, $key, 0, $iv_val);
          $bcc = base64_encode($bcc);

          $api_config_data['from'] = $data['from'];
          $api_config_data['domain_origin'] = $data['domain_origin'];
          $api_config_data['username'] = $username;
          $api_config_data['password'] = $password;
          $api_config_data['host'] = $host;
          $api_config_data['cc'] = $cc_val;
          $api_config_data['port'] = $port;
          $api_config_data['bcc'] = $bcc;
          $api_config_data['status'] = $data['status'];
          $this->custom_db->insert_record('email_configuration_new', $api_config_data);
        }
      }
    }
    exit;
  }
  function insert_api_urls(): void
  {
    $api_data = [];
    $encrypt_method = "AES-256-CBC";
    $api_urls = $this->custom_db->single_table_records('api_urls', '*');

    $secret_iv = PROVAB_SECRET_IV;

    if ($api_urls['status'] == true) {
      foreach ($api_urls['data'] as $data) {

        if (!empty($data)) {
          $md5_key = PROVAB_MD5_SECRET;
          $encrypt_key = PROVAB_ENC_KEY;
          $decrypt_password = $this->db->query("SELECT AES_DECRYPT($encrypt_key,SHA2('" . $md5_key . "',512)) AS decrypt_data");

          $db_data = $decrypt_password->row();

          $secret_key = trim($db_data->decrypt_data);
          $key = hash('sha256', $secret_key);
          $iv_val = substr(hash('sha256', $secret_iv), 0, 16);
          $api_urls_data = openssl_encrypt($data['urls'], $encrypt_method, $key, 0, $iv_val);
          $urls_data = base64_encode($api_urls_data);
          $api_data['system'] = $data['system'];
          $api_data['urls'] = $urls_data;
          $this->custom_db->insert_record('api_urls_new', $api_data);
        }
      }
    }
  }
  function decrypt_api_urls(): void
  {
    $encrypt_method = "AES-256-CBC";
    $api_urls = $this->custom_db->single_table_records('api_urls_new', '*');
   
    $secret_iv = PROVAB_SECRET_IV;
    
    if($api_urls['status'] == true){
      foreach($api_urls['data'] as $data){
        
        if(!empty($data)){
          $md5_key = PROVAB_MD5_SECRET;
          $encrypt_key = PROVAB_ENC_KEY;
          $decrypt_password = $this->db->query("SELECT AES_DECRYPT($encrypt_key,SHA2('".$md5_key."',512)) AS decrypt_data");
          
          $db_data = $decrypt_password->row();
         
          $secret_key = trim($db_data->decrypt_data); 
          $key = hash('sha256', $secret_key);
          $iv_val = substr(hash('sha256', $secret_iv), 0, 16);
          $urls = openssl_decrypt(base64_decode($data['urls']), $encrypt_method, $key, 0, $iv_val);
          debug($urls);exit;
        }
      }
    }
  }
  
}