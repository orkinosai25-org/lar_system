<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 *
 * @package Provab
 * @subpackage Flight
 * @author Balu A<balu.provab@gmail.com>
 * @version V1
 */
class Report extends CI_Controller
{

    // private $current_module;

    public function __construct()
    {
        parent::__construct();
         $this->load->model('hotel_model');
         $this->load->library('booking_data_formatter');
        
    }
   public function b2c_hotel_report(int $offset = 0): void
   {
$page_data=[];$config=[];
      $condition = array();
      $get_data = $this->input->get();
        if(valid_array($get_data) == true) {
            //From-Date and To-Date
            $from_date = trim($get_data['created_datetime_from']);
            $to_date = trim($get_data['created_datetime_to']);
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

          

            if (empty($get_data['status']) == false && strtolower($get_data['status']) != 'all') {
                $condition[] = array('BD.status', '=', $this->db->escape($get_data['status']));
            }

            

            if (empty($get_data['app_reference']) == false) {
                $condition[] = array('BD.app_reference', ' like ', $this->db->escape('%'.$get_data['app_reference'].'%'));
            }
            $page_data['from_date'] = $from_date;
            $page_data['to_date'] = $to_date;
        }
        $total_records = $this->hotel_model->booking($condition, true);

        $table_data = $this->hotel_model->booking($condition, false, $offset, 10);

        $table_data = $this->booking_data_formatter->format_hotel_booking_data($table_data, 'b2c');
        $page_data['table_data'] = $table_data['data'];
     
        /** TABLE PAGINATION */
        $this->load->library('pagination');
        if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
        $config['base_url'] = base_url().'index.php/report/b2c_hotel_report/';
        $config['first_url'] = $config['base_url'].'?'.http_build_query($_GET);
        $page_data['total_rows'] = $config['total_rows'] = $total_records;
        $config['per_page'] = 10;
        $this->pagination->initialize($config);
        /** TABLE PAGINATION */
        $page_data['total_records'] = $config['total_rows'];
        $page_data['customer_email'] = $this->entity_email;
       // debug($page_data);die;
        $this->template->view('report/b2c_hotel_report', $page_data);
  
}

}