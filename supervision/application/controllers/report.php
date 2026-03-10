<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab - Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */

class Report extends CI_Controller
{
    private $current_module;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('hotel_model');
        $this->load->model('flight_model');
        $this->load->model('car_model');
        $this->load->model('user_model');
        $this->load->model('supplierpackage_model');
        $this->load->library('booking_data_formatter');
        $this->current_module = $this->config->item('current_module');
        //		$this->load->library('export');

    }
    public function index(): void
    {
        redirect('general');
    }

    public function monthly_booking_report(): void
    {
        $this->template->view('report/monthly_booking_report');
    }

    public function bus(int $offset = 0): void
    {$config=[];
        $get_data = $this->input->get();
        $condition = array();
        $page_data = array();
        if (valid_array($get_data) == true) {
            //From-Date and To-Date
            $from_date = trim($get_data['created_datetime_from']);
            $to_date = trim($get_data['created_datetime_to']);
            //Auto swipe date
            if (empty($from_date) == false && empty($to_date) == false) {
                $valid_dates = auto_swipe_dates($from_date, $to_date);
                $from_date = $valid_dates['from_date'];
                $to_date = $valid_dates['to_date'];
            }
            if (empty($from_date) == false) {
                $condition[] = array('BD.created_datetime', '>=', $this->db->escape(db_current_datetime($from_date)));
            }
            if (empty($to_date) == false) {
                $condition[] = array('BD.created_datetime', '<=', $this->db->escape(db_current_datetime($to_date)));
            }

            if (empty($get_data['created_by_id']) == false) {
                $condition[] = array('BD.created_by_id', '=', $this->db->escape($get_data['created_by_id']));
            }

            if (empty($get_data['status']) == false && strtolower($get_data['status']) != 'all') {
                $condition[] = array('BD.status', '=', $this->db->escape($get_data['status']));
            }

            if (empty($get_data['phone']) == false) {
                $condition[] = array('BD.phone_number', ' like ', $this->db->escape('%' . $get_data['phone'] . '%'));
            }

            if (empty($get_data['email']) == false) {
                $condition[] = array('BD.email', ' like ', $this->db->escape('%' . $get_data['email'] . '%'));
            }

            if (empty($get_data['app_reference']) == false) {
                $condition[] = array('BD.app_reference', ' like ', $this->db->escape('%' . $get_data['app_reference'] . '%'));
            }
            $page_data['from_date'] = $from_date;
            $page_data['to_date'] = $to_date;
        }
        $total_records = $this->bus_model->booking($condition, true);
        $table_data = $this->bus_model->booking($condition, false, $offset, RECORDS_RANGE_2);
        $table_data = $this->booking_data_formatter->format_bus_booking_data($table_data, $this->current_module);
        $page_data['table_data'] = $table_data['data'];
        /** TABLE PAGINATION */
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/bus/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['customer_email'] = $this->entity_email;
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');
        $this->template->view('report/bus', $page_data);
    }
    public function hotel(int $offset = 0): void
    {
        $page_data=[];
        $config=[];
        $condition = array();
        $get_data = $this->input->get();
        if (valid_array($get_data) == true) {
            //From-Date and To-Date
            $from_date = trim($get_data['created_datetime_from']);
            $to_date = trim($get_data['created_datetime_to']);
            //Auto swipe date
            if (empty($from_date) == false && empty($to_date) == false) {
                $valid_dates = auto_swipe_dates($from_date, $to_date);
                $from_date = $valid_dates['from_date'];
                $to_date = $valid_dates['to_date'];
            }
            if (empty($from_date) == false) {
                $condition[] = array('BD.created_datetime', '>=', $this->db->escape(db_current_datetime($from_date)));
            }
            if (empty($to_date) == false) {
                $condition[] = array('BD.created_datetime', '<=', $this->db->escape(db_current_datetime($to_date)));
            }

            if (empty($get_data['created_by_id']) == false) {
                $condition[] = array('BD.created_by_id', '=', $this->db->escape($get_data['created_by_id']));
            }

            if (empty($get_data['status']) == false && strtolower($get_data['status']) != 'all') {
                $condition[] = array('BD.status', '=', $this->db->escape($get_data['status']));
            }

            if (empty($get_data['phone']) == false) {
                $condition[] = array('BD.phone_number', ' like ', $this->db->escape('%' . $get_data['phone'] . '%'));
            }

            if (empty($get_data['email']) == false) {
                $condition[] = array('BD.email', ' like ', $this->db->escape('%' . $get_data['email'] . '%'));
            }

            if (empty($get_data['app_reference']) == false) {
                $condition[] = array('BD.app_reference', ' like ', $this->db->escape('%' . $get_data['app_reference'] . '%'));
            }
            $page_data['from_date'] = $from_date;
            $page_data['to_date'] = $to_date;
        }
        $total_records = $this->hotel_model->booking($condition, true);
        $table_data = $this->hotel_model->booking($condition, false, $offset, RECORDS_RANGE_2);
        $table_data = $this->booking_data_formatter->format_hotel_booking_data($table_data, $this->current_module);
        $page_data['table_data'] = $table_data['data'];
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/hotel/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        //debug($page_data);exit;
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');
        $this->template->view('report/hotel', $page_data);
    }
    public function b2c_bus_report(int $offset = 0): void
    {
        $config=[];
        $get_data = $this->input->get();
        $condition = array();
        $page_data = array();

        $filter_data = $this->format_basic_search_filters('bus');
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        $condition = $filter_data['filter_condition'];

        //debug($get_data); die;
        /*if(valid_array($get_data) == true) {
			//From-Date and To-Date
			$from_date = trim(@$get_data['created_datetime_from']);
			$to_date = trim(@$get_data['created_datetime_to']);
			//Auto swipe date
			if(empty($from_date) == false && empty($to_date) == false)
			{
				$valid_dates = auto_swipe_dates($from_date, $to_date);
				$from_date = $valid_dates['from_date'];
				$to_date = $valid_dates['to_date'];
			}
			if(empty($from_date) == false) {
				$condition[] = array('BD.created_datetime', '>=', $this->db->escape(db_current_datetime($from_date)));
			}
			if(empty($to_date) == false) {
				$condition[] = array('BD.created_datetime', '<=', $this->db->escape(db_current_datetime($to_date)));
			}
	
			if (empty($get_data['created_by_id']) == false) {
				$condition[] = array('BD.created_by_id', '=', $this->db->escape($get_data['created_by_id']));
			}
	
			if (empty($get_data['status']) == false && strtolower($get_data['status']) != 'all') {
				$condition[] = array('BD.status', '=', $this->db->escape($get_data['status']));
			}
	
			// if (empty($get_data['phone']) == false) {
			// 	$condition[] = array('BD.phone_number', ' like ', $this->db->escape('%'.$get_data['phone'].'%'));
			// }
	
			// if (empty($get_data['email']) == false) {
			// 	$condition[] = array('BD.email', ' like ', $this->db->escape('%'.$get_data['email'].'%'));
			// }
	
			if (empty($get_data['app_reference']) == false) {
				$condition[] = array('BD.app_reference', ' like ', $this->db->escape('%'.$get_data['app_reference'].'%'));
			}
			if (empty($get_data['pnr']) == false) {
				$condition[] = array('BD.pnr', ' like ', $this->db->escape('%'.$get_data['pnr'].'%'));
			}
			$page_data['from_date'] = $from_date;
			$page_data['to_date'] = $to_date;
		}*/

        $total_records = $this->bus_model->b2c_bus_report($condition, true);
        $table_data = $this->bus_model->b2c_bus_report($condition, false, $offset, RECORDS_RANGE_2);
        // debug($table_data); exit;
        $table_data = $this->booking_data_formatter->format_bus_booking_data($table_data, $this->current_module);

        $page_data['table_data'] = $table_data['data'];

        // debug($table_data); exit;

        /** TABLE PAGINATION */
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/b2c_bus_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['customer_email'] = $this->entity_email;
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');
        //debug($page_data); die;
        $this->template->view('report/b2c_report_bus', $page_data);
    }
    public function b2b_bus_report(int $offset = 0): void
    {
        $config=[];
        $get_data = $this->input->get();
        $condition = array();
        $page_data = array();

        $filter_data = $this->format_basic_search_filters('bus');
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        $condition = $filter_data['filter_condition'];

        //debug($condition); die;
        $total_records = $this->bus_model->b2b_bus_report($condition, true);
        $table_data = $this->bus_model->b2b_bus_report($condition, false, $offset, RECORDS_RANGE_2);
        $table_data = $this->booking_data_formatter->format_bus_booking_data($table_data, 'b2b');
        $page_data['table_data'] = $table_data['data'];
        /** TABLE PAGINATION */
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/b2b_bus_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['customer_email'] = $this->entity_email;
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');

        $agent_info = $this->custom_db->single_table_records('user', '*', array('user_type' => B2B_USER, 'domain_list_fk' => get_domain_auth_id()));

        $page_data['agent_details'] = magical_converter(array('k' => 'user_id', 'v' => 'agency_name'), $agent_info);

        $this->template->view('report/b2b_report_bus', $page_data);
    }
    public function b2b_hotel_report(int $offset = 0): void
    {
        $page_data=[];
        $config=[];
        $condition = array();
        $get_data = $this->input->get();

        $filter_data = $this->format_basic_search_filters();
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        if(!empty($filter_data)){
            $condition = $filter_data['filter_condition'];
        }
        

        $total_records = $this->hotel_model->b2b_hotel_report($condition, true);
        $table_data = $this->hotel_model->b2b_hotel_report($condition, false, $offset, RECORDS_RANGE_2);
        $table_data = $this->booking_data_formatter->format_hotel_booking_data($table_data, $this->current_module);
        $page_data['table_data'] = $table_data['data'];
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/b2b_hotel_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        //debug($page_data);exit;
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');

        $agent_info = $this->custom_db->single_table_records('user', '*', array('user_type' => B2B_USER, 'domain_list_fk' => get_domain_auth_id()));

        $page_data['agent_details'] = magical_converter(array('k' => 'user_id', 'v' => 'agency_name'), $agent_info);
        $this->template->view('report/b2b_report_hotel', $page_data);
    }
    public function ultra_hotel_report(int $offset = 0): void
    {
        $page_data=[];
        $config=[];
        $condition = array();
        $get_data = $this->input->get();

        $filter_data = $this->format_basic_search_filters();
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        if(!empty($filter_data)){
            $condition = $filter_data['filter_condition'];
        }
        

        $total_records = $this->hotel_model->b2b_hotel_report($condition, true);
        $table_data = $this->hotel_model->b2b_hotel_report($condition, false, $offset, RECORDS_RANGE_2);
        $table_data = $this->booking_data_formatter->format_hotel_booking_data($table_data, $this->current_module);
        $page_data['table_data'] = $table_data['data'];
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/b2b_hotel_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        //debug($page_data);exit;
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');

        $agent_info = $this->custom_db->single_table_records('user', '*', array('user_type' => ULTRALUX_USER, 'domain_list_fk' => get_domain_auth_id()));

        $page_data['agent_details'] = magical_converter(array('k' => 'user_id', 'v' => 'agency_name'), $agent_info);
        $this->template->view('report/ultr_b2b_report_hotel', $page_data);
    }
    public function b2c_hotel_report(int $offset = 0): void
    {
        $page_data=[];
        $config=[];
        $condition = array();
        $get_data = $this->input->get();

        $filter_data = $this->format_basic_search_filters();
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        if(!empty($filter_data)){
            $condition = $filter_data['filter_condition'];
        }
        

        /*if(valid_array($get_data) == true) {
			//From-Date and To-Date
			$from_date = trim(@$get_data['created_datetime_from']);
			$to_date = trim(@$get_data['created_datetime_to']);
			//Auto swipe date
			if(empty($from_date) == false && empty($to_date) == false)
			{
				$valid_dates = auto_swipe_dates($from_date, $to_date);
				$from_date = $valid_dates['from_date'];
				$to_date = $valid_dates['to_date'];
			}
			if(empty($from_date) == false) {
				$condition[] = array('BD.created_datetime', '>=', $this->db->escape(db_current_datetime($from_date)));
			}
			if(empty($to_date) == false) {
				$condition[] = array('BD.created_datetime', '<=', $this->db->escape(db_current_datetime($to_date)));
			}
	
			if (empty($get_data['created_by_id']) == false) {
				$condition[] = array('BD.created_by_id', '=', $this->db->escape($get_data['created_by_id']));
			}
	
			if (empty($get_data['status']) == false && strtolower($get_data['status']) != 'all') {
				$condition[] = array('BD.status', '=', $this->db->escape($get_data['status']));
			}
	
			// if (empty($get_data['phone']) == false) {
			// 	$condition[] = array('BD.phone_number', ' like ', $this->db->escape('%'.$get_data['phone'].'%'));
			// }
	
			// if (empty($get_data['email']) == false) {
			// 	$condition[] = array('BD.email', ' like ', $this->db->escape('%'.$get_data['email'].'%'));
			// }
	
			if (empty($get_data['app_reference']) == false) {
				$condition[] = array('BD.app_reference', 'like',$this->db->escape('%'.$get_data['app_reference'].'%'));
			}
			$page_data['from_date'] = $from_date;
			$page_data['to_date'] = $to_date;
		}*/
        //debug($this->session->userdata('id'));die;
        $total_records = $this->hotel_model->b2c_hotel_report($condition, true);
        //	debug($total_records); die;
        $table_data = $this->hotel_model->b2c_hotel_report($condition, false, $offset, RECORDS_RANGE_2);
        //debug($table_data['data']); exit;
        $table_data = $this->booking_data_formatter->format_hotel_booking_data($table_data, $this->current_module);
        $page_data['table_data'] = $table_data['data'];
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/b2c_hotel_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        //debug($page_data);exit;
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');
        $this->template->view('report/b2c_report_hotel', $page_data);
    }
    /*B2c sightseeing Report*/
    public function b2c_activities_report(int $offset = 0): void
    {
        $page_data=[];
        $config=[];
        $condition = array();
        $get_data = $this->input->get();

        $filter_data = $this->format_basic_search_filters();
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        $condition = $filter_data['filter_condition'];

        $total_records = $this->sightseeing_model->b2c_sightseeing_report($condition, true);

        //	debug($total_records); die;
        $table_data = $this->sightseeing_model->b2c_sightseeing_report($condition, false, $offset, RECORDS_RANGE_2);
        //debug($table_data['data']); exit;
        $table_data = $this->booking_data_formatter->format_sightseeing_booking_data($table_data, $this->current_module);
        $page_data['table_data'] = $table_data['data'];
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/b2c_sightseeing_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        //debug($page_data);exit;
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');
        $this->template->view('report/b2c_report_sightseeing', $page_data);
    }
    /**
     * Sightseeing Report for b2b flight
     * @param $offset
     */
    public function b2b_activities_report(int $offset = 0): void
    {
        $page_data=[];
        $config=[];
        // $current_user_id = $GLOBALS['CI']->entity_user_id;
        $get_data = $this->input->get();
        $condition = array();
        $filter_data = $this->format_basic_search_filters();
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        $condition = $filter_data['filter_condition'];

        $total_records = $this->sightseeing_model->b2b_sightseeing_report($condition, true);
        //echo '<pre>'; print_r($page_data); die;
        $table_data = $this->sightseeing_model->b2b_sightseeing_report($condition, false, $offset, RECORDS_RANGE_2);
        $table_data = $this->booking_data_formatter->format_sightseeing_booking_data($table_data, $this->current_module);
        // debug($table_data);
        // exit;
        $page_data['table_data'] = $table_data['data'];

        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/b2b_sightseeing_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');

        $user_cond = [];
        $user_cond[] = array('U.user_type', '=', ' (', B2B_USER, ')');
        $user_cond[] = array('U.domain_list_fk', '=', get_domain_auth_id());

        //$agent_info['data'] = $this->user_model->b2b_user_list($user_cond,false);

        $agent_info = $this->custom_db->single_table_records('user', '*', array('user_type' => B2B_USER, 'domain_list_fk' => get_domain_auth_id()));

        $page_data['agent_details'] = magical_converter(array('k' => 'user_id', 'v' => 'agency_name'), $agent_info);

        $this->template->view('report/b2b_sightseeing', $page_data);
    }
    /*B2B Transfer Report*/
    public function b2b_transfers_report(int $offset = 0): void
    {
        $page_data=[];
        $config=[];
        // $current_user_id = $GLOBALS['CI']->entity_user_id;
        $get_data = $this->input->get();
        $condition = array();
        $filter_data = $this->format_basic_search_filters();
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        $condition = $filter_data['filter_condition'];

        $total_records = $this->transferv1_model->b2b_transferv1_report($condition, true);
        //echo '<pre>'; print_r($page_data); die;
        $table_data = $this->transferv1_model->b2b_transferv1_report($condition, false, $offset, RECORDS_RANGE_2);
        $table_data = $this->booking_data_formatter->format_transferv1_booking_data($table_data, $this->current_module);
        // debug($table_data);
        // exit;
        $page_data['table_data'] = $table_data['data'];

        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/b2b_transfers_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');

        $user_cond = [];
        $user_cond[] = array('U.user_type', '=', ' (', B2B_USER, ')');
        $user_cond[] = array('U.domain_list_fk', '=', get_domain_auth_id());

        //$agent_info['data'] = $this->user_model->b2b_user_list($user_cond,false);

        $agent_info = $this->custom_db->single_table_records('user', '*', array('user_type' => B2B_USER, 'domain_list_fk' => get_domain_auth_id()));

        $page_data['agent_details'] = magical_converter(array('k' => 'user_id', 'v' => 'agency_name'), $agent_info);

        $this->template->view('report/b2b_transfer', $page_data);
    }
    /*B2c Transfer Report*/
    public function b2c_transfers_report(int $offset = 0): void
    {
        $page_data=[];
        $config=[];
        $condition = array();
        $get_data = $this->input->get();

        $filter_data = $this->format_basic_search_filters();
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        $condition = $filter_data['filter_condition'];

        $total_records = $this->transferv1_model->b2c_transferv1_report($condition, true);

        //	debug($total_records); die;
        $table_data = $this->transferv1_model->b2c_transferv1_report($condition, false, $offset, RECORDS_RANGE_2);
        //debug($table_data['data']); exit;
        $table_data = $this->booking_data_formatter->format_transferv1_booking_data($table_data, $this->current_module);
        $page_data['table_data'] = $table_data['data'];
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/b2c_transfers_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        //debug($page_data);exit;
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');
        $this->template->view('report/b2c_transferv1_report', $page_data);
    }
    public function b2c_car_report(int $offset = 0): void
    {
        $config=[];
        $get_data = $this->input->get();
        $condition = array();
        $page_data = array();
        $filter_data = $this->format_basic_search_filters('bus');
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        if(!empty($filter_data)){
            $condition = $filter_data['filter_condition'];
        }
        

        $total_records = $this->car_model->b2c_car_report($condition, true);

        $table_data = $this->car_model->b2c_car_report($condition, false, $offset, RECORDS_RANGE_2);

        $table_data = $this->booking_data_formatter->format_car_booking_datas($table_data, $this->current_module);
        // debug($table_data);exit;
        $page_data['table_data'] = $table_data['data'];

        /** TABLE PAGINATION */
        $this->load->library('pagination');
        if (count($_GET) > 0)
            $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/car/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['customer_email'] = $this->entity_email;
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');
        $this->template->view('report/b2c_car_report', $page_data);
    }

    public function b2b_car_report(int $offset = 0): void
    {
        $config=[];
        $get_data = $this->input->get();
        $condition = array();
        $page_data = array();
        $filter_data = $this->format_basic_search_filters('bus');
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        if(!empty($filter_data)){
            $condition = $filter_data['filter_condition'];
        }
       

        $total_records = $this->car_model->b2b_car_report($condition, true);

        $table_data = $this->car_model->b2b_car_report($condition, false, $offset, RECORDS_RANGE_2);
        // echo $this->current_module;exit;
        $table_data = $this->booking_data_formatter->format_car_booking_datas($table_data, $this->current_module);
        // debug($table_data);exit;
        $page_data['table_data'] = $table_data['data'];

        /** TABLE PAGINATION */
        $this->load->library('pagination');
        if (count($_GET) > 0)
            $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/car/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['customer_email'] = $this->entity_email;
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');
        $this->template->view('report/b2c_car_report', $page_data);
    }
    public function ultra_car_report(int $offset = 0): void
    {
        //echo "dfdff";die;
        $config=[];
        $get_data = $this->input->get();
        $condition = array();
        $page_data = array();
        $filter_data = $this->format_basic_search_filters('bus');
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        if(!empty($filter_data)){
            $condition = $filter_data['filter_condition'];
        }
       

        $total_records = $this->car_model->ultra_car_report($condition, true);

        $table_data = $this->car_model->b2b_car_report($condition, false, $offset, RECORDS_RANGE_2);
        // echo $this->current_module;exit;
        $table_data = $this->booking_data_formatter->format_car_booking_datas($table_data, $this->current_module);
        // debug($table_data);exit;
        $page_data['table_data'] = $table_data['data'];

        /** TABLE PAGINATION */
        $this->load->library('pagination');
        if (count($_GET) > 0)
            $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/car/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['customer_email'] = $this->entity_email;
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');
        $this->template->view('report/b2b_car_report', $page_data);
    }

    /**
     * Flight Report
     * @param $offset
     */
    public function flight(int $offset = 0): void
    {
        $page_data=[];
        $config=[];
        //$current_user_id=[];
        //$current_user_id = $GLOBALS['CI']->entity_user_id;
        $get_data = $this->input->get();
        $condition = array();
        if (valid_array($get_data) == true) {
            //From-Date and To-Date
            $from_date = trim($get_data['created_datetime_from']);
            $to_date = trim($get_data['created_datetime_to']);
            //Auto swipe date
            if (empty($from_date) == false && empty($to_date) == false) {
                $valid_dates = auto_swipe_dates($from_date, $to_date);
                $from_date = $valid_dates['from_date'];
                $to_date = $valid_dates['to_date'];
            }
            if (empty($from_date) == false) {
                $condition[] = array('BD.created_datetime', '>=', $this->db->escape(db_current_datetime($from_date)));
            }
            if (empty($to_date) == false) {
                $condition[] = array('BD.created_datetime', '<=', $this->db->escape(db_current_datetime($to_date)));
            }

            if (empty($get_data['created_by_id']) == false) {
                $condition[] = array('BD.created_by_id', '=', $this->db->escape($get_data['created_by_id']));
            }

            if (empty($get_data['status']) == false && strtolower($get_data['status']) != 'all') {
                $condition[] = array('BD.status', '=', $this->db->escape($get_data['status']));
            }

            if (empty($get_data['phone']) == false) {
                $condition[] = array('BD.phone', ' like ', $this->db->escape('%' . $get_data['phone'] . '%'));
            }

            if (empty($get_data['email']) == false) {
                $condition[] = array('BD.email', ' like ', $this->db->escape('%' . $get_data['email'] . '%'));
            }

            if (empty($get_data['app_reference']) == false) {
                $condition[] = array('BD.app_reference', ' like ', $this->db->escape('%' . $get_data['app_reference'] . '%'));
            }
            $page_data['from_date'] = $from_date;
            $page_data['to_date'] = $to_date;
        }
        $total_records = $this->flight_model->booking($condition, true);
        $table_data = $this->flight_model->booking($condition, false, $offset, RECORDS_RANGE_2);
        $table_data = $this->booking_data_formatter->format_flight_booking_data($table_data, $this->current_module);
        $page_data['table_data'] = $table_data['data'];
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/flight/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');
        $this->template->view('report/airline', $page_data);
    }
    public function b2c_flight_report(int $offset = 0): void
    {
        $page_data=[];
        $config=[];
        // $current_user_id=[];
        // $current_user_id = $GLOBALS['CI']->entity_user_id;
        $get_data = $this->input->get();
        //debug($get_data); die;
        $condition = array();

        $filter_data = $this->format_basic_search_filters();
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        if(!empty($filter_data)){
            $condition = $filter_data['filter_condition'];
        }
        

        //$condition[] = array('U.user_type', '=', B2C_USER, ' OR ', 'BD.created_by_id');
        $total_records = $this->flight_model->b2c_flight_report($condition, true);

        $table_data = $this->flight_model->b2c_flight_report($condition, false, $offset, RECORDS_RANGE_2);
        $table_data = $this->booking_data_formatter->format_flight_booking_data($table_data, 'b2c', false);

        //Export report


        $page_data['table_data'] = $table_data['data'];
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/b2c_flight_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);




        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');
        $this->template->view('report/b2c_report_airline', $page_data);
    }
    /**
     * Flight Report for b2b flight
     * @param $offset
     */
    public function b2b_flight_report(int $offset = 0): void
    {
        $page_data=[];
        $config=[];
        //$current_user_id = $GLOBALS['CI']->entity_user_id;
        $get_data = $this->input->get();
        $condition = array();
        $filter_data = $this->format_basic_search_filters();
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        if(!empty($filter_data)){
            $condition = $filter_data['filter_condition'];
        }
        

        $total_records = $this->flight_model->b2b_flight_report($condition, true);
        //echo '<pre>'; print_r($page_data); die;
        $table_data = $this->flight_model->b2b_flight_report($condition, false, $offset, RECORDS_RANGE_2);
        $table_data = $this->booking_data_formatter->format_flight_booking_data($table_data, $this->current_module);
        $page_data['table_data'] = $table_data['data'];

        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/b2b_flight_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');

        $user_cond = [];
        $user_cond[] = array('U.user_type', '=', ' (', B2B_USER, ')');
        $user_cond[] = array('U.domain_list_fk', '=', get_domain_auth_id());

        //$agent_info['data'] = $this->user_model->b2b_user_list($user_cond,false);

        $agent_info = $this->custom_db->single_table_records('user', '*', array('user_type' => B2B_USER, 'domain_list_fk' => get_domain_auth_id()));

        $page_data['agent_details'] = magical_converter(array('k' => 'user_id', 'v' => 'agency_name'), $agent_info);

        $this->template->view('report/b2b_report_airline', $page_data);
    }
    public function ultra_flight_report(int $offset = 0): void
    {
        $page_data=[];
        $config=[];
        //$current_user_id = $GLOBALS['CI']->entity_user_id;
        $get_data = $this->input->get();
        $condition = array();
        $filter_data = $this->format_basic_search_filters();
        $page_data['from_date'] = $filter_data['from_date'];
        $page_data['to_date'] = $filter_data['to_date'];
        if(!empty($filter_data)){
            $condition = $filter_data['filter_condition'];
        }
        

        $total_records = $this->flight_model->ultra_flight_report($condition, true);
        //echo '<pre>'; print_r($page_data); die;
        $table_data = $this->flight_model->ultra_flight_report($condition, false, $offset, RECORDS_RANGE_2);
        $table_data = $this->booking_data_formatter->format_flight_booking_data($table_data, $this->current_module);
        $page_data['table_data'] = $table_data['data'];

        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url() . 'index.php/report/b2b_flight_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['search_params'] = $get_data;
        $page_data['status_options'] = get_enum_list('booking_status_options');

        $user_cond = [];
        $user_cond[] = array('U.user_type', '=', ' (', ULTRALUX_USER, ')');
        $user_cond[] = array('U.domain_list_fk', '=', get_domain_auth_id());

        //$agent_info['data'] = $this->user_model->b2b_user_list($user_cond,false);

        $agent_info = $this->custom_db->single_table_records('user', '*', array('user_type' => ULTRALUX_USER, 'domain_list_fk' => get_domain_auth_id()));

        $page_data['agent_details'] = magical_converter(array('k' => 'user_id', 'v' => 'agency_name'), $agent_info);

        $this->template->view('report/ultra_b2b_report_airline', $page_data);
    }


    public function update_flight_booking_details(string $app_reference, string $booking_source): void
    {
        load_flight_lib($booking_source);
        $this->flight_lib->update_flight_booking_details($app_reference);
        header('Content-Type:application/json');
        echo json_encode(['status' => SUCCESS_STATUS]);
        exit;
    }
    /**
     * Sagar Wakchaure
     *Update pnr Details 
     * @param unknown $app_reference
     * @param unknown $booking_source
     * @param unknown $booking_status
     */
    public function update_pnr_details(string $app_reference, string $booking_source, string $booking_status): void
    {

        load_flight_lib($booking_source);
        $response = $this->flight_lib->update_pnr_details($app_reference);

        $get_pnr_updated_status = $this->flight_model->update_pnr_details($response, $app_reference, $booking_source, $booking_status);
        echo $get_pnr_updated_status;
    }

    public function package(): void
    {
        redirect('report/b2c_package_report');
    }

    public function b2b_package_report(int $offset = 0): void
    {
        $page_data = [];
        $config = [];
        $condition = [];
        $get_data = $this->input->get();

        if (valid_array($get_data)) {
            $from_date = trim(@$get_data['from_date']);
            $to_date   = trim(@$get_data['to_date']);
            if (!empty($from_date) && !empty($to_date)) {
                $valid_dates = auto_swipe_dates($from_date, $to_date);
                $from_date   = $valid_dates['from_date'];
                $to_date     = $valid_dates['to_date'];
            }
            if (!empty($from_date)) {
                $condition[] = ['PE.date', '>=', $this->db->escape($from_date)];
            }
            if (!empty($to_date)) {
                $condition[] = ['PE.date', '<=', $this->db->escape($to_date)];
            }
            if (!empty($get_data['package_name'])) {
                $condition[] = ['P.title', ' like ', $this->db->escape('%' . $get_data['package_name'] . '%')];
            }
            $page_data['from_date'] = $from_date ?? '';
            $page_data['to_date']   = $to_date ?? '';
        }

        $total_records = $this->supplierpackage_model->b2b_package_report($condition, true);
        $table_data    = $this->supplierpackage_model->b2b_package_report($condition, false, $offset, RECORDS_RANGE_2);
        $page_data['table_data'] = $table_data;
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', '&');
        $config['base_url']  = base_url() . 'index.php/report/b2b_package_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        $page_data['total_records'] = $config['total_rows'];
        $page_data['search_params'] = $get_data;
        $this->template->view('report/b2b_report_package', $page_data);
    }

    public function b2c_package_report(int $offset = 0): void
    {
        $page_data = [];
        $config = [];
        $condition = [];
        $get_data = $this->input->get();

        if (valid_array($get_data)) {
            $from_date = trim(@$get_data['from_date']);
            $to_date   = trim(@$get_data['to_date']);
            if (!empty($from_date) && !empty($to_date)) {
                $valid_dates = auto_swipe_dates($from_date, $to_date);
                $from_date   = $valid_dates['from_date'];
                $to_date     = $valid_dates['to_date'];
            }
            if (!empty($from_date)) {
                $condition[] = ['PE.date', '>=', $this->db->escape($from_date)];
            }
            if (!empty($to_date)) {
                $condition[] = ['PE.date', '<=', $this->db->escape($to_date)];
            }
            if (!empty($get_data['package_name'])) {
                $condition[] = ['P.title', ' like ', $this->db->escape('%' . $get_data['package_name'] . '%')];
            }
            $page_data['from_date'] = $from_date ?? '';
            $page_data['to_date']   = $to_date ?? '';
        }

        $total_records = $this->supplierpackage_model->b2c_package_report($condition, true);
        $table_data    = $this->supplierpackage_model->b2c_package_report($condition, false, $offset, RECORDS_RANGE_2);
        $page_data['table_data'] = $table_data;
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', '&');
        $config['base_url']  = base_url() . 'index.php/report/b2c_package_report/';
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = RECORDS_RANGE_2;
        $this->pagination->initialize($config);
        $page_data['total_records'] = $config['total_rows'];
        $page_data['search_params'] = $get_data;
        $this->template->view('report/b2c_report_package', $page_data);
    }
    private function format_basic_search_filters(string $module = ''): array
    {
        $page_data=[];
        $get_data = $this->input->get();

        if(empty($get_data)){
            return [];
        }
        if (valid_array($get_data) == true) {
            $filter_condition = array();
            //From-Date and To-Date
            $from_date = trim($get_data['created_datetime_from']);
            $to_date = trim($get_data['created_datetime_to']);
            //Auto swipe date
            if (empty($from_date) == false && empty($to_date) == false) {
                $valid_dates = auto_swipe_dates($from_date, $to_date);
                $from_date = $valid_dates['from_date'];
                $to_date = $valid_dates['to_date'];
            }
            if (empty($from_date) == false) {
                $filter_condition[] = array('BD.created_datetime', '>=', $this->db->escape(db_current_datetime($from_date)));
            }
            if (empty($to_date) == false) {
                $filter_condition[] = array('BD.created_datetime', '<=', $this->db->escape(db_current_datetime($to_date)));
            }

            /*if (empty($get_data['created_by_id']) == false) {
				$filter_condition[] = array('BD.created_by_id', '=', $this->db->escape($get_data['created_by_id']));
			}*/

            if (empty($get_data['created_by_id']) == false && strtolower($get_data['created_by_id']) != 'all') {
                $filter_condition[] = array('BD.created_by_id', '=', $this->db->escape($get_data['created_by_id']));
            }

            if (empty($get_data['status']) == false && strtolower($get_data['status']) != 'all') {
                $filter_condition[] = array('BD.status', '=', $this->db->escape($get_data['status']));
            }

            /*if (empty($get_data['phone']) == false) {
				$filter_condition[] = array('BD.phone', ' like ', $this->db->escape('%'.$get_data['phone'].'%'));
			}
	
			if (empty($get_data['email']) == false) {
				$filter_condition[] = array('BD.email', ' like ', $this->db->escape('%'.$get_data['email'].'%'));
			}*/

            if ($module == 'bus') {
                if (empty($get_data['pnr']) == false) {
                    $filter_condition[] = array('BD.pnr', ' like ', $this->db->escape('%' . $get_data['pnr'] . '%'));
                }
            } else {
                if (empty($get_data['pnr']) == false) {
                    $filter_condition[] = array('BT.pnr', ' like ', $this->db->escape('%' . $get_data['pnr'] . '%'));
                }
            }


            if (empty($get_data['app_reference']) == false) {
                $filter_condition[] = array('BD.app_reference', ' like ', $this->db->escape('%' . $get_data['app_reference'] . '%'));
            }

            $page_data['from_date'] = $from_date;
            $page_data['to_date'] = $to_date;

            //Today's Booking Data
            if (isset($get_data['today_booking_data']) == true && empty($get_data['today_booking_data']) == false) {
                $filter_condition[] = array('DATE(BD.created_datetime)', '=', '"' . date('Y-m-d') . '"');
            }
            //Last day Booking Data
            if (isset($get_data['last_day_booking_data']) == true && empty($get_data['last_day_booking_data']) == false) {
                $filter_condition[] = array('DATE(BD.created_datetime)', '=', '"' . trim($get_data['last_day_booking_data']) . '"');
            }
            //Previous Booking Data: last 3 days, 7 days, 15 days, 1 month and 3 month
            if (isset($get_data['prev_booking_data']) == true && empty($get_data['prev_booking_data']) == false) {
                $filter_condition[] = array('DATE(BD.created_datetime)', '>=', '"' . trim($get_data['prev_booking_data']) . '"');
            }

            return array('filter_condition' => $filter_condition, 'from_date' => $from_date, 'to_date' => $to_date);
        }
        
    }
    public function cancellation_queue(int $offset = 0): void
    {
        $page_data=[];
        $filter_condition=[];
        error_reporting(0);
        $this->load->model('flight_model');
        //$get_data = $this->input->get();
        $condition = array();
        $cancel_data = array();
        $CancelQueue = array();
        //$status = "BOOKING_CANCELLED";
        $from_date = "2017-12-01";
        $to_date = date("Y-m-d");
        if (empty($from_date) == false) {
            $filter_condition[] = array('DATE(BD.created_datetime)', '>=', $this->db->escape(db_current_datetime($from_date)));
        }
        if (empty($to_date) == false) {
            $filter_condition[] = array('DATE(BD.created_datetime)', '<=', $this->db->escape(db_current_datetime($to_date)));
        }
        $filter_data =  array('filter_condition' => $filter_condition);
        $condition = $filter_data['filter_condition'];

        $page_data['table_data'] = $this->flight_model->booking_cancel($condition, false, $offset, 5000);
        // debug($page_data['table_data']);exit;
        $cancellation_details = $this->booking_data_formatter->format_flight_booking_data($page_data['table_data'], $this->current_module);
        // debug($cancellation_details);exit;
        // $transaction_Details = array();
        $Appreference = array();
        foreach ($cancellation_details['data']['booking_details_app'] as $value) {
            foreach ($value['booking_transaction_details'] as $val) {

                foreach ($val['booking_customer_details'] as  $data) {


                    if (isset($data['cancellation_details'])) {

                        if ($data['cancellation_details']['refund_status'] == "INPROGRESS") {
                            $Appreference[] = $data['app_reference'];
                        }
                    }
                }
            }
        }
        $result = array_unique($Appreference);

        foreach ($result as  $final_data) {
            $CancelQueue[] = $cancellation_details['data']['booking_details_app'][$final_data];
        }
        // debug($CancelQueue);exit;
        $cancel_data['CancelQueue'] = $CancelQueue;
        $this->template->view('report/cancellation_queue', $cancel_data);
    }
    public function get_customer_details(string $app_reference, string $booking_source, string $booking_status, string $module): void
    {
$response=[];
        if ($module == 'flight') {
            $booking_details = $this->flight_model->get_booking_details($app_reference, $booking_source, $booking_status);
        } else if ($module == 'hotel') {
            $booking_details = $this->hotel_model->get_booking_details($app_reference, $booking_source, $booking_status);
        } else if ($module == 'bus') {
            $booking_details = $this->bus_model->get_booking_details($app_reference, $booking_source, $booking_status);
        }
        // debug($booking_details);exit;
        $booking_details['module'] = $module;

        if ($booking_details['status'] == SUCCESS_STATUS && valid_array($booking_details['data']) == true) {
            $response['data'] = get_compressed_output(
                $this->template->isolated_view(
                    'report/customer_details',
                    array('customer_details' => $booking_details,)
                )
            );
        }

        $this->output_compressed_data($response);
    }
    private function output_compressed_data(array $data): void
    {

        ini_set('always_populate_raw_post_data', '-1');

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start("ob_gzhandler");
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        header('Content-type:application/json');

        echo json_encode($data);
        ob_end_flush();
        exit;
    }
    /*
     * For Confirmed Booking
     * Export AirlineReport details to Excel Format or PDF
     */

    public function export_confirmed_booking_airline_report(string $op = ''): void
    {
        $this->load->model('flight_model');
       // $get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('flight');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CONFIRMED'));

        $flight_booking_data = $this->flight_model->b2c_flight_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $flight_booking_data = $this->booking_data_formatter->format_flight_booking_data($flight_booking_data, $this->current_module);
        $flight_booking_data = $flight_booking_data['data']['booking_details'];



        $export_data = array();
        // debug($flight_booking_data);exit;
        foreach ($flight_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['gds_pnr'] = $v['pnr'];
            $export_data[$k]['airline_pnr'] = $v['booking_itinerary_details'][0]['airline_pnr'];
            $export_data[$k]['airline_code'] = $v['booking_itinerary_details'][0]['airline_code'];
            $export_data[$k]['journey_from'] = $v['journey_from'];
            $export_data[$k]['journey_to'] = $v['journey_to'];
            $export_data[$k]['journey_start'] = $v['journey_start'];
            $export_data[$k]['journey_end'] = $v['journey_end'];
            $export_data[$k]['commission_fare'] = $v['fare'];
            $export_data[$k]['commission'] = $v['net_commission'];
            $export_data[$k]['tds'] = $v['net_commission_tds'];
            $export_data[$k]['net_fare'] = $v['net_fare'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['convinence_amount'] = $v['convinence_amount'];
            $export_data[$k]['discount'] = $v['discount'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['booked_date'] = date('d-m-Y', strtotime($v['booked_date']));
        }

        if ($op == 'excel') { // excel export
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'Appreference',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'GDS PNR',
                'g1' => 'Airline PNR',
                'h1' => 'Airline Code',
                'i1' => 'From',
                'j1' => 'To',
                'k1' => 'Form Date',
                'l1' => 'To Date',
                'm1' => 'Commission Fare',
                'n1' => 'Commission',
                'o1' => 'TDS',
                'p1' => 'Net Fare',
                'q1' => 'GST',
                'r1' => 'Convinence Amount',
                's1' => 'Discount',
                't1' => 'Customer Paid',
                'u1' => 'Booked Date',
            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'gds_pnr',
                'g' => 'airline_pnr',
                'h' => 'airline_code',
                'i' => 'journey_from',
                'j' => 'journey_to',
                'k' => 'journey_start',
                'l' => 'journey_end',
                'm' => 'commission_fare',
                'n' => 'commission',
                'o' => 'tds',
                'p' => 'net_fare',
                'q' => 'gst',
                'r' => 'convinence_amount',
                's' => 'discount',
                't' => 'grand_total',
                'u' => 'booked_date',

            );

            $excel_sheet_properties = array(
                'title' => 'Confirmed_Booking_AirlineReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Confirmed_Booking_AirlineReport',
                'sheet_title' => 'Confirmed_Booking_AirlineReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_cancelled_booking_airline_report(string $op = ''): void
    {
        $this->load->model('flight_model');
        //$get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('flight');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CANCELLED'));

        $flight_booking_data = $this->flight_model->b2c_flight_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $flight_booking_data = $this->booking_data_formatter->format_flight_booking_data($flight_booking_data, $this->current_module);
        $flight_booking_data = $flight_booking_data['data']['booking_details'];



        $export_data = array();
        // debug($flight_booking_data);exit;
        foreach ($flight_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['gds_pnr'] = $v['pnr'];
            $export_data[$k]['airline_pnr'] = $v['booking_itinerary_details'][0]['airline_pnr'];
            $export_data[$k]['airline_code'] = $v['booking_itinerary_details'][0]['airline_code'];
            $export_data[$k]['journey_from'] = $v['journey_from'];
            $export_data[$k]['journey_to'] = $v['journey_to'];
            $export_data[$k]['journey_start'] = $v['journey_start'];
            $export_data[$k]['journey_end'] = $v['journey_end'];
            $export_data[$k]['commission_fare'] = $v['fare'];
            $export_data[$k]['commission'] = $v['net_commission'];
            $export_data[$k]['tds'] = $v['net_commission_tds'];
            $export_data[$k]['net_fare'] = $v['net_fare'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['convinence_amount'] = $v['convinence_amount'];
            $export_data[$k]['discount'] = $v['discount'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['booked_date'] = date('d-m-Y', strtotime($v['booked_date']));
        }

        if ($op == 'excel') { // excel export
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'Appreference',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'GDS PNR',
                'g1' => 'Airline PNR',
                'h1' => 'Airline Code',
                'i1' => 'From',
                'j1' => 'To',
                'k1' => 'Form Date',
                'l1' => 'To Date',
                'm1' => 'Commission Fare',
                'n1' => 'Commission',
                'o1' => 'TDS',
                'p1' => 'Net Fare',
                'q1' => 'GST',
                'r1' => 'Convinence Amount',
                's1' => 'Discount',
                't1' => 'Customer Paid',
                'u1' => 'Booked Date',
            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'gds_pnr',
                'g' => 'airline_pnr',
                'h' => 'airline_code',
                'i' => 'journey_from',
                'j' => 'journey_to',
                'k' => 'journey_start',
                'l' => 'journey_end',
                'm' => 'commission_fare',
                'n' => 'commission',
                'o' => 'tds',
                'p' => 'net_fare',
                'q' => 'gst',
                'r' => 'convinence_amount',
                's' => 'discount',
                't' => 'grand_total',
                'u' => 'booked_date',

            );

            $excel_sheet_properties = array(
                'title' => 'Confirmed_Booking_AirlineReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Confirmed_Booking_AirlineReport',
                'sheet_title' => 'Confirmed_Booking_AirlineReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_confirmed_booking_airline_report_b2b(string $op = ''): void
    {
        $this->load->model('flight_model');
        //$get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('flight');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CONFIRMED'));

        $flight_booking_data = $this->flight_model->b2b_flight_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $flight_booking_data = $this->booking_data_formatter->format_flight_booking_data($flight_booking_data, $this->current_module);
        $flight_booking_data = $flight_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($flight_booking_data);exit;
        foreach ($flight_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['status'] = $v['status'];
            $export_data[$k]['agency_name'] = $v['agency_name'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['gds_pnr'] = $v['pnr'];
            $export_data[$k]['airline_pnr'] = $v['booking_itinerary_details'][0]['airline_pnr'];
            $export_data[$k]['airline_code'] = $v['booking_itinerary_details'][0]['airline_code'];
            $export_data[$k]['journey_from'] = $v['journey_from'];
            $export_data[$k]['journey_to'] = $v['journey_to'];
            $export_data[$k]['journey_start'] = $v['journey_start'];
            $export_data[$k]['journey_end'] = $v['journey_end'];
            //$export_data[$k]['trip_type_label'] = $v['trip_type_label'];
            $export_data[$k]['commission_fare'] = $v['fare'];
            $export_data[$k]['commission'] = $v['net_commission'];
            $export_data[$k]['tds'] = $v['net_commission_tds'];
            $export_data[$k]['net_fare'] = $v['net_fare'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['admin_markup'] = $v['admin_markup'];
            $export_data[$k]['agent_markup'] = $v['agent_markup'];
            $export_data[$k]['agent_commission'] = $v['agent_commission'];
            $export_data[$k]['agent_tds'] = $v['agent_tds'];
            $export_data[$k]['agent_buying_price'] = $v['agent_buying_price'];
            $export_data[$k]['admin_buying_price'] = $v['admin_buying_price'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['booked_date'] = date('d-m-Y', strtotime($v['booked_date']));
        }

        if ($op == 'excel') { // excel export
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'Appreference',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'GDS PNR',
                'g1' => 'Airline PNR',
                'h1' => 'Airline Code',
                'i1' => 'From',
                'j1' => 'To',
                'k1' => 'Form Date',
                'l1' => 'To Date',
                'm1' => 'Commission Fare',
                'n1' => 'Commission',
                'o1' => 'TDS',
                'p1' => 'Net Fare',
                'q1' => 'GST',
                'r1' => 'Admin Markup',
                's1' => 'Agent Mark up',
                't1' => 'Agent Commission',
                'u1' => 'Agent Tds',
                'v1' => 'Agent NetFare',
                'x1' => 'Admin Netfare',
                'y1' => 'Customer Paid',
                'z1' => 'Booked Date',
            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'gds_pnr',
                'g' => 'airline_pnr',
                'h' => 'airline_code',
                'i' => 'journey_from',
                'j' => 'journey_to',
                'k' => 'journey_start',
                'l' => 'journey_end',
                'm' => 'commission_fare',
                'n' => 'commission',
                'o' => 'tds',
                'p' => 'net_fare',
                'q' => 'gst',
                'r' => 'admin_markup',
                's' => 'agent_markup',
                't' => 'agent_commission',
                'u' => 'agent_tds',
                'v' => 'agent_buying_price',
                'x' => 'admin_buying_price',
                'y' => 'grand_total',
                'z' => 'booked_date',

            );

            $excel_sheet_properties = array(
                'title' => 'Confirmed_Booking_AirlineReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Confirmed_Booking_AirlineReport',
                'sheet_title' => 'Confirmed_Booking_AirlineReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_cancelled_booking_airline_report_b2b(string $op = ''): void
    {
        $this->load->model('flight_model');
        //$get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('flight');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CANCELLED'));

        $flight_booking_data = $this->flight_model->b2b_flight_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $flight_booking_data = $this->booking_data_formatter->format_flight_booking_data($flight_booking_data, $this->current_module);
        $flight_booking_data = $flight_booking_data['data']['booking_details'];



        $export_data = array();
        // debug($flight_booking_data);exit;
        foreach ($flight_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['gds_pnr'] = $v['pnr'];
            $export_data[$k]['airline_pnr'] = $v['booking_itinerary_details'][0]['airline_pnr'];
            $export_data[$k]['airline_code'] = $v['booking_itinerary_details'][0]['airline_code'];
            $export_data[$k]['journey_from'] = $v['journey_from'];
            $export_data[$k]['journey_to'] = $v['journey_to'];
            $export_data[$k]['journey_start'] = $v['journey_start'];
            $export_data[$k]['journey_end'] = $v['journey_end'];
            $export_data[$k]['commission_fare'] = $v['fare'];
            $export_data[$k]['commission'] = $v['net_commission'];
            $export_data[$k]['tds'] = $v['net_commission_tds'];
            $export_data[$k]['net_fare'] = $v['net_fare'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['admin_markup'] = $v['admin_markup'];
            $export_data[$k]['agent_markup'] = $v['agent_markup'];
            $export_data[$k]['agent_tds'] = $v['agent_tds'];
            $export_data[$k]['agent_buying_price'] = $v['agent_buying_price'];
            $export_data[$k]['admin_buying_price'] = $v['admin_buying_price'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['booked_date'] = date('d-m-Y', strtotime($v['booked_date']));
        }

        if ($op == 'excel') { // excel export
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'Appreference',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'GDS PNR',
                'g1' => 'Airline PNR',
                'h1' => 'Airline Code',
                'i1' => 'From',
                'j1' => 'To',
                'k1' => 'Form Date',
                'l1' => 'To Date',
                'm1' => 'Commission Fare',
                'n1' => 'Commission',
                'o1' => 'TDS',
                'p1' => 'Net Fare',
                'q1' => 'GST',
                'r1' => 'Admin Markup',
                's1' => 'Agent Mark up',
                't1' => 'Agent Commission',
                'u1' => 'Agent Tds',
                'v1' => 'Agent NetFare',
                'x1' => 'Admin Netfare',
                'y1' => 'Customer Paid',
                'z1' => 'Booked Date',
            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'gds_pnr',
                'g' => 'airline_pnr',
                'h' => 'airline_code',
                'i' => 'journey_from',
                'j' => 'journey_to',
                'k' => 'journey_start',
                'l' => 'journey_end',
                'm' => 'commission_fare',
                'n' => 'commission',
                'o' => 'tds',
                'p' => 'net_fare',
                'q' => 'gst',
                'r' => 'admin_markup',
                's' => 'agent_markup',
                't' => 'agent_commission',
                'u' => 'agent_tds',
                'v' => 'agent_buying_price',
                'x' => 'admin_buying_price',
                'y' => 'grand_total',
                'z' => 'booked_date',

            );

            $excel_sheet_properties = array(
                'title' => 'Confirmed_Booking_AirlineReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Confirmed_Booking_AirlineReport',
                'sheet_title' => 'Confirmed_Booking_AirlineReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_confirmed_booking_hotel_report(string $op = ''): void
    {
        $this->load->model('hotel_model');
        //$get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('hotel');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CONFIRMED'));

        $hotel_booking_data = $this->hotel_model->b2c_hotel_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $hotel_booking_data = $this->booking_data_formatter->format_hotel_booking_data($hotel_booking_data, $this->current_module);
        $hotel_booking_data = $hotel_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($hotel_booking_data);exit;
        foreach ($hotel_booking_data as $k => $v) {

            $export_data[$k]['Reference No'] = $v['app_reference'];
            $export_data[$k]['Confirmation_Reference'] = $v['confirmation_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['Hotel Name'] = $v['hotel_name'];
            $export_data[$k]['No.of rooms'] = $v['total_rooms'];
            $export_data[$k]['No.of Adult'] = $v['adult_count'];
            $export_data[$k]['No.of Child'] = $v['child_count'];
            $export_data[$k]['city'] = $v['hotel_location'];
            $export_data[$k]['check_in'] = $v['hotel_check_in'];
            $export_data[$k]['check_out'] = $v['hotel_check_out'];
            $export_data[$k]['commission_fare'] = $v['fare'];
            $export_data[$k]['TDS'] = $v['TDS'];
            $export_data[$k]['Admin_markup'] = $v['admin_markup'];
            $export_data[$k]['convinence_amount'] = $v['convinence_amount'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['Discount'] = $v['discount'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['booked_on'] = date('d-m-Y', strtotime($v['voucher_date']));
        }
        //debug($export_data[$k]['Payment Status']);exit;
        if ($op == 'excel') { // excel export
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'Reference No',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'Confirmation_Reference',
                'g1' => 'Hotel Name',
                'h1' => 'No.of rooms',
                'i1' => 'No.of Adult',
                'j1' => 'No.of Child',
                'k1' => 'city',
                'l1' => 'check_in',
                'm1' => 'check_out',
                'n1' => 'Commission Fare',
                'o1' => 'TDS',
                'p1' => 'Admin Markup',
                'q1' => 'GST',
                'r1' => 'convinence Fee',
                's1' => 'Discount',
                't1' => 'Grand Total',
                'u1' => 'Booked On',

            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'Reference No',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'Confirmation_Reference',
                'g' => 'Hotel Name',
                'h' => 'No.of rooms',
                'i' => 'No.of Adult',
                'j' => 'No.of Child',
                'k' => 'city',
                'l' => 'check_in',
                'm' => 'check_out',
                'n' => 'commission_fare',
                'o' => 'TDS',
                'p' => 'Admin_markup',
                'q' => 'convinence_amount',
                'r' => 'gst',
                's' => 'Discount',
                't' => 'grand_total',
                'u' => 'booked_on',

            );

            $excel_sheet_properties = array(
                'title' => 'Confirmed_Booking_HotelReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Confirmed_Booking_HotelReport',
                'sheet_title' => 'Confirmed_Booking_HotelReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_cancelled_booking_hotel_report(string $op = ''): void
    {
        $this->load->model('hotel_model');
       // $get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('hotel');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CANCELLED'));

        $hotel_booking_data = $this->hotel_model->b2c_hotel_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $hotel_booking_data = $this->booking_data_formatter->format_hotel_booking_data($hotel_booking_data, $this->current_module);
        $hotel_booking_data = $hotel_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($hotel_booking_data);exit;
        foreach ($hotel_booking_data as $k => $v) {

            $export_data[$k]['Reference No'] = $v['app_reference'];
            $export_data[$k]['Confirmation_Reference'] = $v['confirmation_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['Hotel Name'] = $v['hotel_name'];
            $export_data[$k]['No.of rooms'] = $v['total_rooms'];
            $export_data[$k]['No.of Adult'] = $v['adult_count'];
            $export_data[$k]['No.of Child'] = $v['child_count'];
            $export_data[$k]['city'] = $v['hotel_location'];
            $export_data[$k]['check_in'] = $v['hotel_check_in'];
            $export_data[$k]['check_out'] = $v['hotel_check_out'];
            $export_data[$k]['commission_fare'] = $v['fare'];
            $export_data[$k]['TDS'] = $v['TDS'];
            $export_data[$k]['Admin_markup'] = $v['admin_markup'];
            $export_data[$k]['convinence_amount'] = $v['convinence_amount'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['Discount'] = $v['discount'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['booked_on'] = date('d-m-Y', strtotime($v['voucher_date']));
        }
        //debug($export_data[$k]['Payment Status']);exit;
        if ($op == 'excel') { // excel export
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'Reference No',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'Confirmation_Reference',
                'g1' => 'Hotel Name',
                'h1' => 'No.of rooms',
                'i1' => 'No.of Adult',
                'j1' => 'No.of Child',
                'k1' => 'city',
                'l1' => 'check_in',
                'm1' => 'check_out',
                'n1' => 'Commission Fare',
                'o1' => 'TDS',
                'p1' => 'Admin Markup',
                'q1' => 'GST',
                'r1' => 'convinence Fee',
                's1' => 'Discount',
                't1' => 'Grand Total',
                'u1' => 'Booked On',

            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'Reference No',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'Confirmation_Reference',
                'g' => 'Hotel Name',
                'h' => 'No.of rooms',
                'i' => 'No.of Adult',
                'j' => 'No.of Child',
                'k' => 'city',
                'l' => 'check_in',
                'm' => 'check_out',
                'n' => 'commission_fare',
                'o' => 'TDS',
                'p' => 'Admin_markup',
                'q' => 'convinence_amount',
                'r' => 'gst',
                's' => 'Discount',
                't' => 'grand_total',
                'u' => 'booked_on',

            );

            $excel_sheet_properties = array(
                'title' => 'Cancelled_Booking_HotelReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Cancelled_Booking_HotelReport',
                'sheet_title' => 'Cancelled_Booking_HotelReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_confirmed_booking_hotel_report_b2b(string $op = ''): void
    {
        $this->load->model('hotel_model');
       // $get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('hotel');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CONFIRMED'));

        $hotel_booking_data = $this->hotel_model->b2b_hotel_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $hotel_booking_data = $this->booking_data_formatter->format_hotel_booking_data($hotel_booking_data, $this->current_module);
        $hotel_booking_data = $hotel_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($hotel_booking_data);exit;
        foreach ($hotel_booking_data as $k => $v) {

            $export_data[$k]['Reference No'] = $v['app_reference'];
            $export_data[$k]['Confirmation_Reference'] = $v['confirmation_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['Hotel Name'] = $v['hotel_name'];
            $export_data[$k]['No.of rooms'] = $v['total_rooms'];
            $export_data[$k]['No.of Adult'] = $v['adult_count'];
            $export_data[$k]['No.of Child'] = $v['child_count'];
            $export_data[$k]['city'] = $v['hotel_location'];
            $export_data[$k]['check_in'] = $v['hotel_check_in'];
            $export_data[$k]['check_out'] = $v['hotel_check_out'];
            $export_data[$k]['commission_fare'] = $v['fare'];
            $export_data[$k]['TDS'] = $v['TDS'];
            $export_data[$k]['Admin_markup'] = $v['admin_markup'];
            $export_data[$k]['convinence_amount'] = $v['convinence_amount'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['Discount'] = $v['discount'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['booked_on'] = date('d-m-Y', strtotime($v['voucher_date']));
        }
        //debug($export_data[$k]['Payment Status']);exit;
        if ($op == 'excel') { // excel export
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'Reference No',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'Confirmation_Reference',
                'g1' => 'Hotel Name',
                'h1' => 'No.of rooms',
                'i1' => 'No.of Adult',
                'j1' => 'No.of Child',
                'k1' => 'city',
                'l1' => 'check_in',
                'm1' => 'check_out',
                'n1' => 'Commission Fare',
                'o1' => 'TDS',
                'p1' => 'Admin Markup',
                'q1' => 'GST',
                'r1' => 'convinence Fee',
                's1' => 'Discount',
                't1' => 'Grand Total',
                'u1' => 'Booked On',

            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'Reference No',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'Confirmation_Reference',
                'g' => 'Hotel Name',
                'h' => 'No.of rooms',
                'i' => 'No.of Adult',
                'j' => 'No.of Child',
                'k' => 'city',
                'l' => 'check_in',
                'm' => 'check_out',
                'n' => 'commission_fare',
                'o' => 'TDS',
                'p' => 'Admin_markup',
                'q' => 'convinence_amount',
                'r' => 'gst',
                's' => 'Discount',
                't' => 'grand_total',
                'u' => 'booked_on',

            );

            $excel_sheet_properties = array(
                'title' => 'Confirmed_Booking_HotelReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Confirmed_Booking_HotelReport',
                'sheet_title' => 'Confirmed_Booking_HotelReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_cancelled_booking_hotel_report_b2b(string $op = ''): void
    {
        $this->load->model('hotel_model');
       // $get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('hotel');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CANCELLED'));

        $hotel_booking_data = $this->hotel_model->b2b_hotel_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $hotel_booking_data = $this->booking_data_formatter->format_hotel_booking_data($hotel_booking_data, $this->current_module);
        $hotel_booking_data = $hotel_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($hotel_booking_data);exit;
        foreach ($hotel_booking_data as $k => $v) {

            $export_data[$k]['Reference No'] = $v['app_reference'];
            $export_data[$k]['Confirmation_Reference'] = $v['confirmation_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['Hotel Name'] = $v['hotel_name'];
            $export_data[$k]['No.of rooms'] = $v['total_rooms'];
            $export_data[$k]['No.of Adult'] = $v['adult_count'];
            $export_data[$k]['No.of Child'] = $v['child_count'];
            $export_data[$k]['city'] = $v['hotel_location'];
            $export_data[$k]['check_in'] = $v['hotel_check_in'];
            $export_data[$k]['check_out'] = $v['hotel_check_out'];
            $export_data[$k]['commission_fare'] = $v['fare'];
            $export_data[$k]['TDS'] = $v['TDS'];
            $export_data[$k]['Admin_markup'] = $v['admin_markup'];
            $export_data[$k]['convinence_amount'] = $v['convinence_amount'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['Discount'] = $v['discount'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['booked_on'] = date('d-m-Y', strtotime($v['voucher_date']));
        }
        //debug($export_data[$k]['Payment Status']);exit;
        if ($op == 'excel') { // excel export
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'Reference No',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'Confirmation_Reference',
                'g1' => 'Hotel Name',
                'h1' => 'No.of rooms',
                'i1' => 'No.of Adult',
                'j1' => 'No.of Child',
                'k1' => 'city',
                'l1' => 'check_in',
                'm1' => 'check_out',
                'n1' => 'Commission Fare',
                'o1' => 'TDS',
                'p1' => 'Admin Markup',
                'q1' => 'GST',
                'r1' => 'convinence Fee',
                's1' => 'Discount',
                't1' => 'Grand Total',
                'u1' => 'Booked On',

            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'Reference No',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'Confirmation_Reference',
                'g' => 'Hotel Name',
                'h' => 'No.of rooms',
                'i' => 'No.of Adult',
                'j' => 'No.of Child',
                'k' => 'city',
                'l' => 'check_in',
                'm' => 'check_out',
                'n' => 'commission_fare',
                'o' => 'TDS',
                'p' => 'Admin_markup',
                'q' => 'convinence_amount',
                'r' => 'gst',
                's' => 'Discount',
                't' => 'grand_total',
                'u' => 'booked_on',

            );

            $excel_sheet_properties = array(
                'title' => 'Cancelled_Booking_HotelReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Cancelled_Booking_HotelReport',
                'sheet_title' => 'Cancelled_Booking_HotelReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_confirmed_booking_bus_report(string $op = ''): void
    {
        $this->load->model('bus_model');
        //$get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('bus');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CONFIRMED'));

        $bus_booking_data = $this->bus_model->b2c_bus_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $bus_booking_data = $this->booking_data_formatter->format_bus_booking_data($bus_booking_data, $this->current_module);
        $bus_booking_data = $bus_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($bus_booking_data);exit;
        foreach ($bus_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['Pnr'] = $v['pnr'];
            $export_data[$k]['operator'] = $v['operator'];
            $export_data[$k]['from'] = $v['departure_from'];
            $export_data[$k]['to'] = $v['arrival_to'];
            $export_data[$k]['bus_type'] = $v['bus_type'];
            $export_data[$k]['Comm.Fare'] = $v['fare'];
            $export_data[$k]['commission'] = $v['admin_commission'];
            $export_data[$k]['TDS'] = $v['admin_tds'];
            $export_data[$k]['NetFare'] = $v['admin_buying_price'];
            $export_data[$k]['convinence_amount'] = $v['convinence_amount'];
            $export_data[$k]['Markup'] = $v['admin_markup'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['Discount'] = $v['discount'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['Travel date'] = $v['journey_datetime'];
            $export_data[$k]['booked_date'] = date('d-m-Y', strtotime($v['booked_date']));
        }
        //debug($export_data[$k]['Payment Status']);exit;
        if ($op == 'excel') { // excel export
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'app_reference',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'Pnr',
                'g1' => 'operator',
                'h1' => 'From',
                'i1' => 'To',
                'j1' => 'Seat Type',
                'k1' => 'commision Fare',
                'l1' => 'commission',
                'm1' => 'Tds',
                'n1' => 'Net Fare',
                'o1' => 'Conivence Fee',
                'p1' => 'Markup',
                'q1' => 'GST',
                'r1' => 'Discount',
                's1' => 'Total Fare',
                't1' => 'Travel date',
                'u1' => 'Booked On',

            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'Pnr',
                'g' => 'operator',
                'h' => 'from',
                'i' => 'to',
                'j' => 'bus_type',
                'k' => 'Comm.Fare',
                'l' => 'commission',
                'm' => 'TDS',
                'n' => 'NetFare',
                'o' => 'convinence_amount',
                'p' => 'Markup',
                'q' => 'gst',
                'r' => 'Discount',
                's' => 'grand_total',
                't' => 'Travel date',
                'u' => 'booked_date',

            );

            $excel_sheet_properties = array(
                'title' => 'Confirmed_Booking_BusReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Confirmed_Booking_BusReport',
                'sheet_title' => 'Confirmed_Booking_BusReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_cancelled_booking_bus_report(string $op = ''): void
    {
        $this->load->model('bus_model');
        //$get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('bus');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CANCELLED'));

        $bus_booking_data = $this->bus_model->b2c_bus_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $bus_booking_data = $this->booking_data_formatter->format_bus_booking_data($bus_booking_data, $this->current_module);
        $bus_booking_data = $bus_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($bus_booking_data);exit;
        foreach ($bus_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['Pnr'] = $v['pnr'];
            $export_data[$k]['operator'] = $v['operator'];
            $export_data[$k]['from'] = $v['departure_from'];
            $export_data[$k]['to'] = $v['arrival_to'];
            $export_data[$k]['bus_type'] = $v['bus_type'];
            $export_data[$k]['Comm.Fare'] = $v['fare'];
            $export_data[$k]['commission'] = $v['admin_commission'];
            $export_data[$k]['TDS'] = $v['admin_tds'];
            $export_data[$k]['NetFare'] = $v['admin_buying_price'];
            $export_data[$k]['convinence_amount'] = $v['convinence_amount'];
            $export_data[$k]['Markup'] = $v['admin_markup'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['Discount'] = $v['discount'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['Travel date'] = $v['journey_datetime'];
            $export_data[$k]['booked_date'] = date('d-m-Y', strtotime($v['booked_date']));
        }
        //debug($export_data[$k]['Payment Status']);exit;
        if ($op == 'excel') { // excel export
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'app_reference',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'Pnr',
                'g1' => 'operator',
                'h1' => 'From',
                'i1' => 'To',
                'j1' => 'Seat Type',
                'k1' => 'commision Fare',
                'l1' => 'commission',
                'm1' => 'Tds',
                'n1' => 'Net Fare',
                'o1' => 'Conivence Fee',
                'p1' => 'Markup',
                'q1' => 'GST',
                'r1' => 'Discount',
                's1' => 'Total Fare',
                't1' => 'Travel date',
                'u1' => 'Booked On',

            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'Pnr',
                'g' => 'operator',
                'h' => 'from',
                'i' => 'to',
                'j' => 'bus_type',
                'k' => 'Comm.Fare',
                'l' => 'commission',
                'm' => 'TDS',
                'n' => 'NetFare',
                'o' => 'convinence_amount',
                'p' => 'Markup',
                'q' => 'gst',
                'r' => 'Discount',
                's' => 'grand_total',
                't' => 'Travel date',
                'u' => 'booked_date',

            );

            $excel_sheet_properties = array(
                'title' => 'Cancelled_Booking_BusReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Cancelled_Booking_BusReport',
                'sheet_title' => 'Cancelled_Booking_BusReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_confirmed_booking_bus_report_b2b(string $op = ''): void
    {
        $this->load->model('bus_model');
       // $get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('bus');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CONFIRMED'));

        $bus_booking_data = $this->bus_model->b2b_bus_report($condition, false, 0, 2000);
        //Maximum 500 Data Can be exported at time
        $bus_booking_data = $this->booking_data_formatter->format_bus_booking_data($bus_booking_data, $this->current_module);
        $bus_booking_data = $bus_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($bus_booking_data);exit;
        foreach ($bus_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['Pnr'] = $v['pnr'];
            $export_data[$k]['operator'] = $v['operator'];
            $export_data[$k]['from'] = $v['departure_from'];
            $export_data[$k]['to'] = $v['arrival_to'];
            $export_data[$k]['bus_type'] = $v['bus_type'];
            $export_data[$k]['Comm.Fare'] = $v['fare'];
            $export_data[$k]['Netfare'] = $v['admin_buying_price'];
            $export_data[$k]['admin_markup'] = $v['admin_markup'];
            $export_data[$k]['agent_markup'] = $v['agent_markup'];
            $export_data[$k]['admin_commission'] = $v['admin_commission'];
            $export_data[$k]['agent_commission'] = $v['agent_commission'];
            $export_data[$k]['admin_tds'] = $v['admin_tds'];
            $export_data[$k]['agent_tds'] = $v['agent_tds'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['Price Deducted From Agent'] = $v['agent_buying_price'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['booked_date'] = date('d-m-Y', strtotime($v['booked_date']));
        }
        //debug($export_data[$k]['Payment Status']);exit;
        if ($op == 'excel') { // excel export
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'app_reference',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'Pnr',
                'g1' => 'operator',
                'h1' => 'From',
                'i1' => 'To',
                'j1' => 'Seat Type',
                'k1' => 'commision Fare',
                'l1' => 'Netfare',
                'm1' => 'Admin_markup',
                'n1' => 'Agent_markup',
                'o1' => 'Admin_tds',
                'p1' => 'Agent_tds',
                'q1' => 'Admin_commission',
                'r1' => 'Agent_commission',
                's1' => 'Gst',
                't1' => 'Price Deducted From Agent',
                'u1' => 'Total Price',
                'v1' => 'Booked On',
            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'Pnr',
                'g' => 'operator',
                'h' => 'from',
                'i' => 'to',
                'j' => 'bus_type',
                'k' => 'Comm.Fare',
                'l' => 'Netfare',
                'm' => 'admin_markup',
                'n' => 'agent_markup',
                'o' => 'admin_tds',
                'p' => 'agent_tds',
                'q' => 'admin_commission',
                'r' => 'agent_commission',
                's' => 'gst',
                't' => 'Price Deducted From Agent',
                'u' => 'grand_total',
                'v' => 'booked_date',

            );

            $excel_sheet_properties = array(
                'title' => 'Confirmed_Booking_BusReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Confirmed_Booking_BusReport',
                'sheet_title' => 'Confirmed_Booking_BusReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_cancelled_booking_bus_report_b2b(string $op = ''): void
    {
        $this->load->model('bus_model');
       // $get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('bus');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CANCELLED'));

        $bus_booking_data = $this->bus_model->b2b_bus_report($condition, false, 0, 2000);
        //Maximum 500 Data Can be exported at time
        $bus_booking_data = $this->booking_data_formatter->format_bus_booking_data($bus_booking_data, $this->current_module);
        $bus_booking_data = $bus_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($bus_booking_data);exit;
        foreach ($bus_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['Pnr'] = $v['pnr'];
            $export_data[$k]['operator'] = $v['operator'];
            $export_data[$k]['from'] = $v['departure_from'];
            $export_data[$k]['to'] = $v['arrival_to'];
            $export_data[$k]['bus_type'] = $v['bus_type'];
            $export_data[$k]['Comm.Fare'] = $v['fare'];
            $export_data[$k]['Netfare'] = $v['admin_buying_price'];
            $export_data[$k]['admin_markup'] = $v['admin_markup'];
            $export_data[$k]['agent_markup'] = $v['agent_markup'];
            $export_data[$k]['admin_commission'] = $v['admin_commission'];
            $export_data[$k]['agent_commission'] = $v['agent_commission'];
            $export_data[$k]['admin_tds'] = $v['admin_tds'];
            $export_data[$k]['agent_tds'] = $v['agent_tds'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['Price Deducted From Agent'] = $v['agent_buying_price'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['booked_date'] = date('d-m-Y', strtotime($v['booked_date']));
        }
        //debug($export_data[$k]['Payment Status']);exit;
        if ($op == 'excel') { // excel export
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'app_reference',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'Pnr',
                'g1' => 'operator',
                'h1' => 'From',
                'i1' => 'To',
                'j1' => 'Seat Type',
                'k1' => 'commision Fare',
                'l1' => 'Netfare',
                'm1' => 'Admin_markup',
                'n1' => 'Agent_markup',
                'o1' => 'Admin_tds',
                'p1' => 'Agent_tds',
                'q1' => 'Admin_commission',
                'r1' => 'Agent_commission',
                's1' => 'Gst',
                't1' => 'Price Deducted From Agent',
                'u1' => 'Total Price',
                'v1' => 'Booked On',
            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'Pnr',
                'g' => 'operator',
                'h' => 'from',
                'i' => 'to',
                'j' => 'bus_type',
                'k' => 'Comm.Fare',
                'l' => 'Netfare',
                'm' => 'admin_markup',
                'n' => 'agent_markup',
                'o' => 'admin_tds',
                'p' => 'agent_tds',
                'q' => 'admin_commission',
                'r' => 'agent_commission',
                's' => 'gst',
                't' => 'Price Deducted From Agent',
                'u' => 'grand_total',
                'v' => 'booked_date',

            );

            $excel_sheet_properties = array(
                'title' => 'Cancelled_Booking_BusReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Cancelled_Booking_BusReport',
                'sheet_title' => 'Cancelled_Booking_BusReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_confirmed_booking_transfer_report(string $op = ''): void
    {
        $this->load->model('transferv1_model');
        //$get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('transfers');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CONFIRMED'));

        $transfer_booking_data = $this->transferv1_model->b2c_transferv1_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $transfer_booking_data = $this->booking_data_formatter->format_transferv1_booking_data($transfer_booking_data, $this->current_module);
        $transfer_booking_data = $transfer_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($transfer_booking_data);exit;
        foreach ($transfer_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['confirmation_reference'] = $v['confirmation_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['product_name'] = $v['product_name'];
            $export_data[$k]['grade_desc'] = $v['grade_desc'];
            $export_data[$k]['travel_date'] = $v['travel_date'];
            $export_data[$k]['NO of adult_count'] = $v['adult_count'];
            $export_data[$k]['NO of child_count'] = $v['child_count'];
            $export_data[$k]['NO of youth_count'] = $v['youth_count'];
            $export_data[$k]['NO of senior_count'] = $v['senior_count'];
            $export_data[$k]['NO of infant_count'] = $v['infant_count'];
            $export_data[$k]['Comm.Fare'] = $v['fare'];
            $export_data[$k]['commission'] = $v['admin_commission'];
            $export_data[$k]['tds'] = $v['net_commission_tds'];
            $export_data[$k]['admin_net_fare'] = $v['admin_net_fare'];
            $export_data[$k]['admin_markup'] = $v['admin_markup'];
            $export_data[$k]['convinence_amount'] = $v['convinence_amount'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['Discount'] = $v['discount'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['Travel date'] = $v['journey_datetime'];
            $export_data[$k]['booked_date'] = date('d-m-Y', strtotime($v['voucher_date']));
        }
        //debug($export_data[$k]['booked_date']);exit;
        if ($op == 'excel') { // excel export
            //error_reporting(E_ALL);
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'APP reference',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'confirmation reference',
                'g1' => 'product name',
                'h1' => 'No of Adult',
                'i1' => 'No of Child',
                'j1' => 'No of youth',
                'k1' => 'No of senior',
                'l1' => 'No of infant',
                'm1' => 'City',
                'n1' => 'Travel Date',
                'o1' => 'Commission Fare',
                'p1' => 'Commission',
                'q1' => 'TDS',
                'r1' => 'Admin NetFare',
                's1' => 'Admin Markup',
                't1' => 'GST',
                'u1' => 'Discount',
                'v1' => 'Total Fare',
                'w1' => 'Convinence Fee',
                'x1' => 'Booked On',

            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'confirmation_reference',
                'g' => 'product_name',
                'h' => 'NO of adult_count',
                'i' => 'NO of child_count',
                'j' => 'NO of youth_count',
                'k' => 'NO of senior_count',
                'l' => 'NO of infant_count',
                'm' => 'grade_desc',
                'n' => 'travel_date',
                'o' => 'Comm.Fare',
                'p' => 'commission',
                'q' => 'tds',
                'r' => 'admin_net_fare',
                's' => 'admin_markup',
                't' => 'gst',
                'u' => 'Discount',
                'v' => 'grand_total',
                'w' => 'convinence_amount',
                'x' => 'booked_date',

            );

            $excel_sheet_properties = array(
                'title' => 'Confirmed_Booking_transferReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Confirmed_Booking_transferReport',
                'sheet_title' => 'Confirmed_Booking_transferReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_cancelled_booking_transfer_report(string $op = ''): void
    {
        $this->load->model('transferv1_model');
        //$get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('transfers');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CANCELLED'));

        $transfer_booking_data = $this->transferv1_model->b2c_transferv1_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $transfer_booking_data = $this->booking_data_formatter->format_transferv1_booking_data($transfer_booking_data, $this->current_module);
        $transfer_booking_data = $transfer_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($transfer_booking_data);exit;
        foreach ($transfer_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['confirmation_reference'] = $v['confirmation_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['product_name'] = $v['product_name'];
            $export_data[$k]['grade_desc'] = $v['grade_desc'];
            $export_data[$k]['travel_date'] = $v['travel_date'];
            $export_data[$k]['NO of adult_count'] = $v['adult_count'];
            $export_data[$k]['NO of child_count'] = $v['child_count'];
            $export_data[$k]['NO of youth_count'] = $v['youth_count'];
            $export_data[$k]['NO of senior_count'] = $v['senior_count'];
            $export_data[$k]['NO of infant_count'] = $v['infant_count'];
            $export_data[$k]['Comm.Fare'] = $v['fare'];
            $export_data[$k]['commission'] = $v['admin_commission'];
            $export_data[$k]['tds'] = $v['net_commission_tds'];
            $export_data[$k]['admin_net_fare'] = $v['admin_net_fare'];
            $export_data[$k]['admin_markup'] = $v['admin_markup'];
            $export_data[$k]['convinence_amount'] = $v['convinence_amount'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['Discount'] = $v['discount'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
            $export_data[$k]['Travel date'] = $v['journey_datetime'];
            $export_data[$k]['booked_date'] = date('d-m-Y', strtotime($v['voucher_date']));
        }
        //debug($export_data[$k]['booked_date']);exit;
        if ($op == 'excel') { // excel export
            //error_reporting(E_ALL);
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'APP reference',
                'c1' => 'Lead Pax Name',
                'd1' => 'Lead Pax Email',
                'e1' => 'Lead Pax Phone',
                'f1' => 'confirmation reference',
                'g1' => 'product name',
                'h1' => 'No of Adult',
                'i1' => 'No of Child',
                'j1' => 'No of youth',
                'k1' => 'No of senior',
                'l1' => 'No of infant',
                'm1' => 'City',
                'n1' => 'Travel Date',
                'o1' => 'Commission Fare',
                'p1' => 'Commission',
                'q1' => 'TDS',
                'r1' => 'Admin NetFare',
                's1' => 'Admin Markup',
                't1' => 'GST',
                'u1' => 'Discount',
                'v1' => 'Total Fare',
                'w1' => 'Convinence Fee',
                'x1' => 'Booked On',
            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'lead_pax_name',
                'd' => 'lead_pax_email',
                'e' => 'lead_pax_phone_number',
                'f' => 'confirmation_reference',
                'g' => 'product_name',
                'h' => 'NO of adult_count',
                'i' => 'NO of child_count',
                'j' => 'NO of youth_count',
                'k' => 'NO of senior_count',
                'l' => 'NO of infant_count',
                'm' => 'grade_desc',
                'n' => 'travel_date',
                'o' => 'Comm.Fare',
                'p' => 'commission',
                'q' => 'tds',
                'r' => 'admin_net_fare',
                's' => 'admin_markup',
                't' => 'gst',
                'u' => 'Discount',
                'v' => 'grand_total',
                'w' => 'convinence_amount',
                'x' => 'booked_date',

            );


            $excel_sheet_properties = array(
                'title' => 'Confirmed_Booking_transferReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Confirmed_Booking_transferReport',
                'sheet_title' => 'Confirmed_Booking_transferReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_confirmed_booking_transfer_report_b2b(string $op = ''): void
    {
        $this->load->model('transferv1_model');
        //$get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('transfers');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CONFIRMED'));

        $transfer_booking_data = $this->transferv1_model->b2b_transferv1_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $transfer_booking_data = $this->booking_data_formatter->format_transferv1_booking_data($transfer_booking_data, $this->current_module);
        $transfer_booking_data = $transfer_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($transfer_booking_data);exit;
        foreach ($transfer_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['agency_name'] = $v['agency_name'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['product_name'] = $v['product_name'];
            $export_data[$k]['Destination'] = $v['Destination'];
            $export_data[$k]['created_datetime'] = $v['created_datetime'];
            $export_data[$k]['travel_date'] = $v['travel_date'];
            $export_data[$k]['confirmation_reference'] = $v['confirmation_reference'];
            $export_data[$k]['Comm_Fare'] = $v['fare'];
            $export_data[$k]['commission'] = $v['admin_commission'];
            $export_data[$k]['admin_tds'] = $v['admin_tds'];
            $export_data[$k]['net_fare'] = $v['net_fare'];
            $export_data[$k]['admin_profit'] = $v['admin_commission'];
            $export_data[$k]['admin_markup'] = $v['admin_markup'];
            $export_data[$k]['agent_commission'] = $v['agent_commission'];
            $export_data[$k]['agent_tds'] = $v['agent_tds'];
            $export_data[$k]['agent_netfare'] = $v['agent_buying_price'];
            $export_data[$k]['agent_markup'] = $v['agent_markup'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
        }
        //debug($export_data[$k]['booked_date']);exit;
        if ($op == 'excel') { // excel export
            //error_reporting(E_ALL);
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'APP reference',
                'c1' => 'Agency name',
                'd1' => 'Lead Pax Name',
                'e1' => 'Lead Pax Email',
                'f1' => 'Lead Pax Phone Number',
                'g1' => 'Activity Name',
                'h1' => 'Acitvity Location',
                'i1' => 'Booked On',
                'j1' => 'Journey Date',
                'k1' => 'Confirmation Reference',
                'l1' => 'Commission Fare',
                'm1' => 'Commission',
                'n1' => 'TDS',
                'o1' => 'Admin NetFare',
                'p1' => 'Admin Profit',
                'q1' => 'Admin Markup',
                'r1' => 'Agent Commission',
                's1' => 'Agent TDS',
                't1' => 'Agent Net Fare',
                'u1' => 'Agent Markup',
                'v1' => 'GST',
                'z1' => 'TotalFare',

            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'agency_name',
                'd' => 'lead_pax_name',
                'e' => 'lead_pax_email',
                'f' => 'lead_pax_phone_number',
                'g' => 'product_name',
                'h' => 'Destination',
                'i' => 'created_datetime',
                'j' => 'travel_date',
                'k' => 'confirmation_reference',
                'l' => 'Comm_Fare',
                'm' => 'commission',
                'n' => 'admin_tds',
                'o' => 'net_fare',
                'p' => 'admin_profit',
                'q' => 'admin_markup',
                'r' => 'agent_commission',
                's' => 'agent_tds',
                't' => 'agent_netfare',
                'u' => 'agent_markup',
                'v' => 'gst',
                'z' => 'grand_total',

            );

            $excel_sheet_properties = array(
                'title' => 'Confirmed_Booking_transferReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Confirmed_Booking_transferReport',
                'sheet_title' => 'Confirmed_Booking_transferReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_cancelled_booking_transfer_report_b2b(string $op = ''): void
    {
        $this->load->model('transferv1_model');
        //$get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('transfers');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CANCELLED'));

        $transfer_booking_data = $this->transferv1_model->b2b_transferv1_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $transfer_booking_data = $this->booking_data_formatter->format_transferv1_booking_data($transfer_booking_data, $this->current_module);
        $transfer_booking_data = $transfer_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($transfer_booking_data);exit;
        foreach ($transfer_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['agency_name'] = $v['agency_name'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['product_name'] = $v['product_name'];
            $export_data[$k]['Destination'] = $v['Destination'];
            $export_data[$k]['created_datetime'] = $v['created_datetime'];
            $export_data[$k]['travel_date'] = $v['travel_date'];
            $export_data[$k]['confirmation_reference'] = $v['confirmation_reference'];
            $export_data[$k]['Comm_Fare'] = $v['fare'];
            $export_data[$k]['commission'] = $v['admin_commission'];
            $export_data[$k]['admin_tds'] = $v['admin_tds'];
            $export_data[$k]['net_fare'] = $v['net_fare'];
            $export_data[$k]['admin_profit'] = $v['admin_commission'];
            $export_data[$k]['admin_markup'] = $v['admin_markup'];
            $export_data[$k]['agent_commission'] = $v['agent_commission'];
            $export_data[$k]['agent_tds'] = $v['agent_tds'];
            $export_data[$k]['agent_netfare'] = $v['agent_buying_price'];
            $export_data[$k]['agent_markup'] = $v['agent_markup'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
        }
        //debug($export_data[$k]['booked_date']);exit;
        if ($op == 'excel') { // excel export
            //error_reporting(E_ALL);
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'APP reference',
                'c1' => 'Agency name',
                'd1' => 'Lead Pax Name',
                'e1' => 'Lead Pax Email',
                'f1' => 'Lead Pax Phone Number',
                'g1' => 'Activity Name',
                'h1' => 'Acitvity Location',
                'i1' => 'Booked On',
                'j1' => 'Journey Date',
                'k1' => 'Confirmation Reference',
                'l1' => 'Commission Fare',
                'm1' => 'Commission',
                'n1' => 'TDS',
                'o1' => 'Admin NetFare',
                'p1' => 'Admin Profit',
                'q1' => 'Admin Markup',
                'r1' => 'Agent Commission',
                's1' => 'Agent TDS',
                't1' => 'Agent Net Fare',
                'u1' => 'Agent Markup',
                'v1' => 'GST',
                'z1' => 'TotalFare',

            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'agency_name',
                'd' => 'lead_pax_name',
                'e' => 'lead_pax_email',
                'f' => 'lead_pax_phone_number',
                'g' => 'product_name',
                'h' => 'Destination',
                'i' => 'created_datetime',
                'j' => 'travel_date',
                'k' => 'confirmation_reference',
                'l' => 'Comm_Fare',
                'm' => 'commission',
                'n' => 'admin_tds',
                'o' => 'net_fare',
                'p' => 'admin_profit',
                'q' => 'admin_markup',
                'r' => 'agent_commission',
                's' => 'agent_tds',
                't' => 'agent_netfare',
                'u' => 'agent_markup',
                'v' => 'gst',
                'z' => 'grand_total',

            );

            $excel_sheet_properties = array(
                'title' => 'Cancelled_Booking_transferReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Cancelled_Booking_transferReport',
                'sheet_title' => 'Cancelled_Booking_transferReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    public function export_confirmed_booking_activities_report(string $op = ''): void
    {
        $this->load->model('sightseeing_model');
        //$get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('activities');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CONFIRMED'));

        $activites_booking_data = $this->sightseeing_model->b2c_sightseeing_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $activites_booking_data = $this->booking_data_formatter->format_sightseeing_booking_data($activites_booking_data, $this->current_module);
        $activites_booking_data = $activites_booking_data['data']['booking_details'];



        $export_data = array();
        //debug($activites_booking_data);exit;
        foreach ($activites_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['confirmation_reference'] = $v['confirmation_reference'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['product_name'] = $v['product_name'];
            $export_data[$k]['No of Adults'] = $v['adult_count'];
            $export_data[$k]['No of Child'] = $v['child_count'];
            $export_data[$k]['No of youth'] = $v['youth_count'];
            $export_data[$k]['No of Senior'] = $v['senior_count'];
            $export_data[$k]['No of infant'] = $v['infant_count'];
            $export_data[$k]['location'] = $v['cutomer_city'];
            //$export_data[$k]['created_datetime'] = $v['created_datetime'];
            $export_data[$k]['travel_date'] = $v['travel_date'];
            //$export_data[$k]['currency'] = $v['currency'];
            $export_data[$k]['Comm_Fare'] = $v['fare'];
            $export_data[$k]['commission'] = $v['admin_commission'];
            $export_data[$k]['admin_tds'] = $v['admin_tds'];
            $export_data[$k]['net_fare'] = $v['admin_net_fare'];
            //	$export_data[$k]['admin_profit'] = $v['admin_commission'];
            $export_data[$k]['admin_markup'] = $v['admin_markup'];
            $export_data[$k]['convinence_amount'] = $v['convinence_amount'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['Discount'] = $v['discount'];
            $export_data[$k]['amount'] = $v['grand_total'];
            $export_data[$k]['Booked_on'] = $v['voucher_date'];
            //	$export_data[$k]['grand_total'] = $v['grand_total'];


        }
        //debug($export_data[$k]['booked_date']);exit;
        if ($op == 'excel') { // excel export
            //error_reporting(E_ALL);
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'APP reference',
                'c1' => 'Confirmation_Reference',
                'd1' => 'Lead Pax Name',
                'e1' => 'Lead Pax Email',
                'f1' => 'Lead Pax Phone Number',
                'g1' => 'Product Name',
                'h1' => 'No of Adults',
                'i1' => 'No of Child',
                'j1' => 'No of youth',
                'k1' => 'No of Senior',
                'l1' => 'No of infant',
                'm1' => 'City',
                'n1' => 'Travel Date',
                //'o1' => 'Currency',
                'p1' => 'Commission Fare',
                'q1' => 'Commission',
                'r1' => 'Tds',
                's1' => 'Admin NetFare',
                't1' => 'Admin Markup',
                'u1' => 'Convinence amount',
                'v1' => 'GST',
                'w1' => 'Discount',
                'x1' => 'Customer Paid amount',
                'y1' => 'Booked On',

            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'confirmation_reference',
                'd' => 'lead_pax_name',
                'e' => 'lead_pax_email',
                'f' => 'lead_pax_phone_number',
                'g' => 'product_name',
                'h' => 'No of Adults',
                'i' => 'No of Child',
                'j' => 'No of youth',
                'k' => 'No of Senior',
                'l' => 'No of infant',
                'm' => 'location',
                'n' => 'travel_date',
                // 'o' => 'currency',
                'p' => 'Comm_Fare',
                'q' => 'commission',
                'r' => 'admin_tds',
                's' => 'net_fare',
                't' => 'admin_markup',
                'u' => 'convinence_amount',
                'v' => 'gst',
                'w' => 'Discount',
                'x' => 'amount',
                'y' => 'Booked_on',

            );

            $excel_sheet_properties = array(
                'title' => 'Confirmed_Booking_activitesReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Confirmed_Booking_activitesReport',
                'sheet_title' => 'Confirmed_Booking_activitesReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
    // private function export_booking_activities_report( string $op = ''): void
    // {
    //     $this->load->model('sightseeing_model');
    //     $get_data = $this->input->get();
    //     $condition = array();
    //     //From-Date and To-Date
    //     // $from_date = trim(@$get_data['created_datetime_from']);
    //     // $to_date = trim(@$get_data['created_datetime_to']);

    //     $filter_data = $this->format_basic_search_filters('activities');
    //     $condition = $filter_data['filter_condition'];

    //     //Unset the Status Filter
    //     if (valid_array($condition) == true) {
    //         foreach ($condition as $ck => $cv) {

    //             if ($cv[0] == 'BD.status') {
    //                 unset($condition[$ck]);
    //             }
    //         }
    //     }

    //     //Adding Confirmed Status Filter
    //     $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CANCELLED'));

    //     $activites_booking_data = $this->sightseeing_model->b2c_sightseeing_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
    //     $activites_booking_data = $this->booking_data_formatter->format_sightseeing_booking_data($activites_booking_data, $this->current_module);
    //     $activites_booking_data = $activites_booking_data['data']['booking_details'];



    //     $export_data = array();
    //     //debug($activites_booking_data);exit;
    //     foreach ($activites_booking_data as $k => $v) {

    //         $export_data[$k]['app_reference'] = $v['app_reference'];
    //         $export_data[$k]['confirmation_reference'] = $v['confirmation_reference'];
    //         $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
    //         $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
    //         $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
    //         $export_data[$k]['product_name'] = $v['product_name'];
    //         $export_data[$k]['No of Adults'] = $v['adult_count'];
    //         $export_data[$k]['No of Child'] = $v['child_count'];
    //         $export_data[$k]['No of youth'] = $v['youth_count'];
    //         $export_data[$k]['No of Senior'] = $v['senior_count'];
    //         $export_data[$k]['No of infant'] = $v['infant_count'];
    //         $export_data[$k]['location'] = $v['cutomer_city'];
    //         //$export_data[$k]['created_datetime'] = $v['created_datetime'];
    //         $export_data[$k]['travel_date'] = $v['travel_date'];
    //         //	$export_data[$k]['currency'] = $v['currency'];
    //         $export_data[$k]['Comm_Fare'] = $v['fare'];
    //         $export_data[$k]['commission'] = $v['admin_commission'];
    //         $export_data[$k]['admin_tds'] = $v['admin_tds'];
    //         $export_data[$k]['net_fare'] = $v['admin_net_fare'];
    //         //	$export_data[$k]['admin_profit'] = $v['admin_commission'];
    //         $export_data[$k]['admin_markup'] = $v['admin_markup'];
    //         $export_data[$k]['convinence_amount'] = $v['convinence_amount'];
    //         $export_data[$k]['gst'] = $v['gst'];
    //         $export_data[$k]['Discount'] = $v['discount'];
    //         $export_data[$k]['amount'] = $v['grand_total'];
    //         $export_data[$k]['Booked_on'] = $v['voucher_date'];
    //         //	$export_data[$k]['grand_total'] = $v['grand_total'];


    //     }
    //     //debug($export_data[$k]['booked_date']);exit;
    //     if ($op == 'excel') { // excel export
    //         //error_reporting(E_ALL);
    //         $headings = array(
    //             'a1' => 'Sl. No.',
    //             'b1' => 'APP reference',
    //             'c1' => 'Confirmation_Reference',
    //             'd1' => 'Lead Pax Name',
    //             'e1' => 'Lead Pax Email',
    //             'f1' => 'Lead Pax Phone Number',
    //             'g1' => 'Product Name',
    //             'h1' => 'No of Adults',
    //             'i1' => 'No of Child',
    //             'j1' => 'No of youth',
    //             'k1' => 'No of Senior',
    //             'l1' => 'No of infant',
    //             'm1' => 'City',
    //             'n1' => 'Travel Date',
    //             // 'o1' => 'Currency',
    //             'p1' => 'Commission Fare',
    //             'q1' => 'Commission',
    //             'r1' => 'Tds',
    //             's1' => 'Admin NetFare',
    //             't1' => 'Admin Markup',
    //             'u1' => 'Convinence amount',
    //             'v1' => 'GST',
    //             'w1' => 'Discount',
    //             'x1' => 'Customer Paid amount',
    //             'y1' => 'Booked On',

    //         );
    //         // field names in data set 
    //         $fields = array(
    //             'a' => '', // empty for sl. no.
    //             'b' => 'app_reference',
    //             'c' => 'confirmation_reference',
    //             'd' => 'lead_pax_name',
    //             'e' => 'lead_pax_email',
    //             'f' => 'lead_pax_phone_number',
    //             'g' => 'product_name',
    //             'h' => 'No of Adults',
    //             'i' => 'No of Child',
    //             'j' => 'No of youth',
    //             'k' => 'No of Senior',
    //             'l' => 'No of infant',
    //             'm' => 'location',
    //             'n' => 'travel_date',
    //             //'o' => 'currency',
    //             'p' => 'Comm_Fare',
    //             'q' => 'commission',
    //             'r' => 'admin_tds',
    //             's' => 'net_fare',
    //             't' => 'admin_markup',
    //             'u' => 'convinence_amount',
    //             'v' => 'gst',
    //             'w' => 'Discount',
    //             'x' => 'amount',
    //             'y' => 'Booked_on',

    //         );

    //         $excel_sheet_properties = array(
    //             'title' => 'Cancelled_Booking_activitesReport_' . date('d-M-Y'),
    //             'creator' => 'Accentria Solutions',
    //             'description' => 'Cancelled_Booking_activitesReport',
    //             'sheet_title' => 'Cancelled_Booking_activitesReport'
    //         );

    //         $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
    //         $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
    //     }
    // }
    // private function export_booking_activities_report_by_type(string $op = ''): void
    // {
    //     $this->load->model('sightseeing_model');
    //     $get_data = $this->input->get();
    //     $condition = array();
    //     //From-Date and To-Date
    //     // $from_date = trim(@$get_data['created_datetime_from']);
    //     // $to_date = trim(@$get_data['created_datetime_to']);

    //     $filter_data = $this->format_basic_search_filters('activities');
    //     $condition = $filter_data['filter_condition'];

    //     //Unset the Status Filter
    //     if (valid_array($condition) == true) {
    //         foreach ($condition as $ck => $cv) {

    //             if ($cv[0] == 'BD.status') {
    //                 unset($condition[$ck]);
    //             }
    //         }
    //     }

    //     //Adding Confirmed Status Filter
    //     $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CONFIRMED'));

    //     $activites_booking_data = $this->sightseeing_model->b2b_sightseeing_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
    //     $activites_booking_data = $this->booking_data_formatter->format_sightseeing_booking_data($activites_booking_data, $this->current_module);
    //     $activites_booking_data = $activites_booking_data['data']['booking_details'];



    //     $export_data = array();
    //     // debug($activites_booking_data);exit;
    //     foreach ($activites_booking_data as $k => $v) {

    //         $export_data[$k]['app_reference'] = $v['app_reference'];
    //         $export_data[$k]['agency_name'] = $v['agency_name'];
    //         $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
    //         $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
    //         $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
    //         $export_data[$k]['product_name'] = $v['product_name'];
    //         $export_data[$k]['location'] = $v['destination_name'];
    //         $export_data[$k]['Booked_on'] = $v['voucher_date'];
    //         $export_data[$k]['travel_date'] = $v['travel_date'];
    //         $export_data[$k]['confirmation_reference'] = $v['confirmation_reference'];
    //         $export_data[$k]['Comm_Fare'] = $v['fare'];
    //         $export_data[$k]['commission'] = $v['net_commission'];
    //         $export_data[$k]['tds'] = $v['net_commission_tds'];
    //         $export_data[$k]['net_fare'] = $v['net_fare'];
    //         $export_data[$k]['admin_commission'] = $v['admin_commission'];
    //         $export_data[$k]['admin_markup'] = $v['admin_markup'];
    //         $export_data[$k]['agent_commission'] = $v['agent_commission'];
    //         $export_data[$k]['agent_tds'] = $v['agent_tds'];
    //         $export_data[$k]['agent_buying_price'] = $v['agent_buying_price'];
    //         $export_data[$k]['agent_markup'] = $v['agent_markup'];
    //         $export_data[$k]['gst'] = $v['gst'];
    //         $export_data[$k]['grand_total'] = $v['grand_total'];
    //     }
    //     //debug($export_data[$k]['booked_date']);exit;
    //     if ($op == 'excel') { // excel export
    //         //error_reporting(E_ALL);
    //         $headings = array(
    //             'a1' => 'Sl. No.',
    //             'b1' => 'APP reference',
    //             'c1' => 'Agency Name',
    //             'd1' => 'Lead Pax Name',
    //             'e1' => 'Lead Pax Email',
    //             'f1' => 'Lead Pax Phone Number',
    //             'g1' => 'Activity Name',
    //             'h1' => 'Acitvity Location',
    //             'i1' => 'BookedOn',
    //             'j1' => 'JourneyDate',
    //             'k1' => 'Confirmation Reference',
    //             'l1' => 'Commission Fare	',
    //             'm1' => 'Commission',
    //             'n1' => 'TDS',
    //             'o1' => 'Admin NetFare',
    //             'p1' => 'Admin Profit',
    //             'q1' => 'Admin Markup',
    //             'r1' => 'Agent Commission',
    //             's1' => 'Agent TDS',
    //             't1' => 'Agent Net Fare',
    //             'u1' => 'Agent Markup',
    //             'v1' => 'GST',
    //             'w1' => 'TotalFare',


    //         );
    //         // field names in data set 
    //         $fields = array(
    //             'a' => '', // empty for sl. no.
    //             'b' => 'app_reference',
    //             'c' => 'agency_name',
    //             'd' => 'lead_pax_name',
    //             'e' => 'lead_pax_email',
    //             'f' => 'lead_pax_phone_number',
    //             'g' => 'product_name',
    //             'h' => 'location',
    //             'i' => 'Booked_on',
    //             'j' => 'travel_date',
    //             'k' => 'confirmation_reference',
    //             'l' => 'Comm_Fare',
    //             'm' => 'commission',
    //             'n' => 'tds',
    //             'o' => 'net_fare',
    //             'p' => 'admin_commission',
    //             'q' => 'admin_markup',
    //             'r' => 'agent_commission',
    //             's' => 'agent_tds',
    //             't' => 'agent_buying_price',
    //             'u' => 'agent_markup',
    //             'v' => 'gst',
    //             'w' => 'grand_total',


    //         );

    //         $excel_sheet_properties = array(
    //             'title' => 'Confirmed_Booking_activitesReport_' . date('d-M-Y'),
    //             'creator' => 'Accentria Solutions',
    //             'description' => 'Confirmed_Booking_activitesReport',
    //             'sheet_title' => 'Confirmed_Booking_activitesReport'
    //         );

    //         $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
    //         $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
    //     }
    // }
    public function export_cancelled_booking_activities_report_b2b(string $op = ''): void
    {
        $this->load->model('sightseeing_model');
        //$get_data = $this->input->get();
        $condition = array();
        //From-Date and To-Date
        // $from_date = trim(@$get_data['created_datetime_from']);
        // $to_date = trim(@$get_data['created_datetime_to']);

        $filter_data = $this->format_basic_search_filters('activities');
        $condition = $filter_data['filter_condition'];

        //Unset the Status Filter
        if (valid_array($condition) == true) {
            foreach ($condition as $ck => $cv) {

                if ($cv[0] == 'BD.status') {
                    unset($condition[$ck]);
                }
            }
        }

        //Adding Confirmed Status Filter
        $condition[] = array('BD.status', '=', $this->db->escape('BOOKING_CANCELLED'));

        $activites_booking_data = $this->sightseeing_model->b2b_sightseeing_report($condition, false, 0, 2000); //Maximum 500 Data Can be exported at time
        $activites_booking_data = $this->booking_data_formatter->format_sightseeing_booking_data($activites_booking_data, $this->current_module);
        $activites_booking_data = $activites_booking_data['data']['booking_details'];



        $export_data = array();
        // debug($activites_booking_data);exit;
        foreach ($activites_booking_data as $k => $v) {

            $export_data[$k]['app_reference'] = $v['app_reference'];
            $export_data[$k]['agency_name'] = $v['agency_name'];
            $export_data[$k]['lead_pax_name'] = $v['lead_pax_name'];
            $export_data[$k]['lead_pax_email'] = $v['lead_pax_email'];
            $export_data[$k]['lead_pax_phone_number'] = $v['lead_pax_phone_number'];
            $export_data[$k]['product_name'] = $v['product_name'];
            $export_data[$k]['location'] = $v['destination_name'];
            $export_data[$k]['Booked_on'] = $v['voucher_date'];
            $export_data[$k]['travel_date'] = $v['travel_date'];
            $export_data[$k]['confirmation_reference'] = $v['confirmation_reference'];
            $export_data[$k]['Comm_Fare'] = $v['fare'];
            $export_data[$k]['commission'] = $v['net_commission'];
            $export_data[$k]['tds'] = $v['net_commission_tds'];
            $export_data[$k]['net_fare'] = $v['net_fare'];
            $export_data[$k]['admin_commission'] = $v['admin_commission'];
            $export_data[$k]['admin_markup'] = $v['admin_markup'];
            $export_data[$k]['agent_commission'] = $v['agent_commission'];
            $export_data[$k]['agent_tds'] = $v['agent_tds'];
            $export_data[$k]['agent_buying_price'] = $v['agent_buying_price'];
            $export_data[$k]['agent_markup'] = $v['agent_markup'];
            $export_data[$k]['gst'] = $v['gst'];
            $export_data[$k]['grand_total'] = $v['grand_total'];
        }
        //debug($export_data[$k]['booked_date']);exit;
        if ($op == 'excel') { // excel export
            //error_reporting(E_ALL);
            $headings = array(
                'a1' => 'Sl. No.',
                'b1' => 'APP reference',
                'c1' => 'Agency Name',
                'd1' => 'Lead Pax Name',
                'e1' => 'Lead Pax Email',
                'f1' => 'Lead Pax Phone Number',
                'g1' => 'Activity Name',
                'h1' => 'Acitvity Location',
                'i1' => 'BookedOn',
                'j1' => 'JourneyDate',
                'k1' => 'Confirmation Reference',
                'l1' => 'Commission Fare	',
                'm1' => 'Commission',
                'n1' => 'TDS',
                'o1' => 'Admin NetFare',
                'p1' => 'Admin Profit',
                'q1' => 'Admin Markup',
                'r1' => 'Agent Commission',
                's1' => 'Agent TDS',
                't1' => 'Agent Net Fare',
                'u1' => 'Agent Markup',
                'v1' => 'GST',
                'w1' => 'TotalFare',


            );
            // field names in data set 
            $fields = array(
                'a' => '', // empty for sl. no.
                'b' => 'app_reference',
                'c' => 'agency_name',
                'd' => 'lead_pax_name',
                'e' => 'lead_pax_email',
                'f' => 'lead_pax_phone_number',
                'g' => 'product_name',
                'h' => 'location',
                'i' => 'Booked_on',
                'j' => 'travel_date',
                'k' => 'confirmation_reference',
                'l' => 'Comm_Fare',
                'm' => 'commission',
                'n' => 'tds',
                'o' => 'net_fare',
                'p' => 'admin_commission',
                'q' => 'admin_markup',
                'r' => 'agent_commission',
                's' => 'agent_tds',
                't' => 'agent_buying_price',
                'u' => 'agent_markup',
                'v' => 'gst',
                'w' => 'grand_total',


            );

            $excel_sheet_properties = array(
                'title' => 'Cancelled_Booking_activitesReport_' . date('d-M-Y'),
                'creator' => 'Accentria Solutions',
                'description' => 'Cancelled_Booking_activitesReport',
                'sheet_title' => 'Cancelled_Booking_activitesReport'
            );

            $this->load->library('provab_excel'); // we need this provab_excel library to export excel.
            $this->provab_excel->excel_export($headings, $fields, $export_data, $excel_sheet_properties);
        }
    }
}
