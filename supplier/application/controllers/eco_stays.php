<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Eco_stays extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('custom_db'));
    }
public function index(): void
    {
        redirect('eco_stays/stays');
    }
public function stays(string $origin = ''): void
    {$config=[];
        // error_reporting(E_ALL);  ini_set('display_errors', '1');
        $page_data = array();

        $post_params = $this->input->post();
        //debug($post_params);die;
        if (empty($post_params) == false) {
            $image_data = array();
            if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
                $config['upload_path'] = $this->template->domain_eco_stays_images_upload_dir();
                $temp_file_name = $_FILES['image']['name'];
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['file_name'] = time() . $temp_file_name;
                $config['max_size'] = '100000000';
                $config['max_width'] = '10000';
                $config['max_height'] = '10000';
                $config['remove_spaces'] = false;
                // debug($config);die;
                 // UPLOAD IMAGE
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('image')) {
                    set_error_message('UL00102');
                } else {
                    $image_data = $this->upload->data();
                   
                }  
                // UPLOAD IMAGE
                // $this->load->library('upload', $config);
                // $this->upload->initialize($config);
                // if (!$this->upload->do_upload('image')) {
                //     set_error_message('UL00102');
                //     redirect('eco_stays/stays');
                //     exit();
                // } else {
                //     $image_data = $this->upload->data();
                // }
            }
            $post_params['host']=$this->entity_user_id;
            $insert_data = $post_params;
            //debug($post_params);die;
            unset($insert_data['FID']);

            if (empty($image_data) == false) {
                $insert_data['image'] = $image_data['file_name'];
            }

            if (isset($insert_data['amenities'])) {
                $insert_data['amenities'] = json_encode($insert_data['amenities']);
            }

            if (isset($insert_data['theme'])) {
                $insert_data['theme'] = json_encode($insert_data['theme']);
            }

            if (isset($post_params['origin'])) {
                //update
                $origin = intval($post_params['origin']);
                $data = $this->custom_db->single_table_records('eco_stays', '*', array('origin' => $origin));
                // debug($data);
                // debug($insert_data);die;
                if ($data['status'] == true) {
                    //now update
                    $update_res = $this->custom_db->update_record('eco_stays', $insert_data, array('origin' => $origin));
                    if ($update_res == QUERY_SUCCESS) {

                        //if new image uploaded delete old FILES
                        if (empty($image_data) == false) {
                            if (empty($data['data'][0]['image']) == false) {
                                $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . $data['data'][0]['image']; // GETTING FILE PATH
                                if (file_exists($_image)) {
                                    unlink($_image);
                                }
                            }
                        }
                        //if $insert_data['theme'] != $data['data'][0]['theme'] then re map 
                        if ($insert_data['theme'] != $data['data'][0]['theme']) {
                            $this->custom_db->delete_record('eco_stays_theme_map', array('stays_origin' => $origin));
                            $batch_theme_map_data = array();
                            foreach ($post_params['theme'] as  $theme) {
                                $batch_theme_map_data[] = array(
                                    'stays_origin' => $origin,
                                    'theme_origin' => $theme
                                );
                            }
                            $this->custom_db->insert_records('eco_stays_theme_map', $batch_theme_map_data);
                        }

                        set_update_message();
                        redirect('eco_stays/stays');
                        exit();
                    } else {
                        //show error
                        set_error_message('UL002');
                        //delete uploaded images
                        if (empty($image_data) == false) {
                            if (empty($image_data['file_name']) == false) {
                                $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . $image_data['file_name']; // GETTING FILE PATH
                                if (file_exists($_image)) {
                                    unlink($_image);
                                }
                            }
                        }
                        redirect('eco_stays/stays');
                        exit();
                    }
                } else {
                    //invalid request
                    set_error_message('UL00100');
                    redirect('eco_stays/stays');
                    exit();
                }
            } else {
                //insert
                $insert_data['created_by_id'] = $this->entity_user_id;
                $insert_res = $this->custom_db->insert_record('eco_stays', $insert_data);
                if ($insert_res['status'] == QUERY_SUCCESS) {
                    //map $insert_data['theme']
                    // $batch_theme_map_data = array();
                    // foreach ($post_params['theme'] as $k => $theme) {
                    //     $batch_theme_map_data[] = array(
                    //         'stays_origin' => $insert_res['insert_id'],
                    //         'theme_origin' => $theme
                    //     );
                    // }
                    // $this->custom_db->insert_records('eco_stays_theme_map', $batch_theme_map_data);
                    set_insert_message();
                    redirect('eco_stays/stays');
                    exit();
                } else {
                    //show error
                    set_error_message('UL002');
                    //delete uploaded images
                    if (empty($image_data) == false) {
                        if (empty($image_data['file_name']) == false) {
                            $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . $image_data['file_name']; // GETTING FILE PATH
                            if (file_exists($_image)) {
                                unlink($_image);
                            }
                        }
                    }
                    redirect('eco_stays/stays');
                    exit();
                }
            }
        } else {
            $origin = intval($origin);
            if ($origin > 0) {
                $page_data['origin'] = $origin;
                $data = $this->custom_db->single_table_records('eco_stays', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    $page_data['form_data'] = $data['data'][0];
                    if (isset($page_data['form_data']['amenities'])) {
                        $page_data['form_data']['amenities'] = json_decode($page_data['form_data']['amenities'], true);
                    }
                    if (isset($page_data['form_data']['theme'])) {
                        $page_data['form_data']['theme'] = json_decode($page_data['form_data']['theme'], true);
                    } else {
                        $page_data['form_data']['theme'] = array();
                    }
                } else {
                    set_error_message('UL00100');
                    redirect('eco_stays/stays');
                    exit();
                }
            }
        }

        /*$data_list = $this->db->query("SELECT eco_stays.*,CONCAT(host.first_name, ' ', host.last_name) AS host_name FROM eco_stays AS eco_stays LEFT JOIN user AS host ON host.user_id = eco_stays.host");*/
         $data_list = $this->db->query("SELECT eco_stays.*,CONCAT(host.first_name, ' ', host.last_name) AS host_name FROM eco_stays AS eco_stays LEFT JOIN user AS host ON host.user_id = eco_stays.host   WHERE 
        host.user_id = $this->entity_user_id");
        $page_data['data_list'] = $data_list->result_array();
        
        // debug($page_data);die;
        $this->template->view('eco_stays/stays', $page_data);
    }
    public function stays1(string $origin = ''): void
    {$config=[];
        $page_data = array();

        $post_params = $this->input->post();

        if (empty($post_params) == false) {
            $image_data = array();
            if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
                $config['upload_path'] = $this->template->domain_eco_stays_images_upload_dir() . 'stays/';
                $temp_file_name = $_FILES['image']['name'];
                $config['allowed_types'] = '*';
                $config['file_name'] = 'eco_stays_' . $temp_file_name;
                $config['max_size'] = '';
                $config['max_width'] = '';
                $config['max_height'] = '';
                $config['remove_spaces'] = false;

                // UPLOAD IMAGE
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('image')) {
                    set_error_message('UL00102');
                    redirect('eco_stays/stays');
                    exit();
                } else {
                    $image_data = $this->upload->data();
                }
            }
            $insert_data = $post_params;
            unset($insert_data['FID']);

            if (empty($image_data) == false) {
                $insert_data['image'] = $image_data['file_name'];
            }

            if (isset($insert_data['amenities'])) {
                $insert_data['amenities'] = json_encode($insert_data['amenities']);
            }

            if (isset($insert_data['theme'])) {
                $insert_data['theme'] = json_encode($insert_data['theme']);
            }

            if (isset($post_params['origin'])) {
                //update
                $origin = intval($post_params['origin']);
                $data = $this->custom_db->single_table_records('eco_stays', '*', array('origin' => $origin, 'host' => $this->entity_user_id));
                if ($data['status'] == true) {
                    //now update
                    $update_res = $this->custom_db->update_record('eco_stays', $insert_data, array('origin' => $origin));
                    if ($update_res == QUERY_SUCCESS) {

                        //if new image uploaded delete old FILES
                        if (empty($image_data) == false) {
                            if (empty($data['data'][0]['image']) == false) {
                                $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'stays/' . $data['data'][0]['image']; // GETTING FILE PATH
                                if (file_exists($_image)) {
                                    unlink($_image);
                                }
                            }
                        }
                        //if $insert_data['theme'] != $data['data'][0]['theme'] then re map 
                        if ($insert_data['theme'] != $data['data'][0]['theme']) {
                            $this->custom_db->delete_record('eco_stays_theme_map', array('stays_origin' => $origin));
                            $batch_theme_map_data = array();
                            foreach ($post_params['theme'] as $k => $theme) {
                                $batch_theme_map_data[] = array(
                                    'stays_origin' => $origin,
                                    'theme_origin' => $theme
                                );
                            }
                            $this->custom_db->insert_records('eco_stays_theme_map', $batch_theme_map_data);
                        }

                        set_update_message();
                        redirect('eco_stays/stays');
                        exit();
                    } else {
                        //show error
                        set_error_message('UL002');
                        //delete uploaded images
                        if (empty($image_data) == false) {
                            if (empty($image_data['file_name']) == false) {
                                $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'stays/' . $image_data['file_name']; // GETTING FILE PATH
                                if (file_exists($_image)) {
                                    unlink($_image);
                                }
                            }
                        }
                        redirect('eco_stays/stays');
                        exit();
                    }
                } else {
                    //invalid request
                    set_error_message('UL00100');
                    redirect('eco_stays/stays');
                    exit();
                }
            } else {
                //insert
                $insert_data['created_by_id'] = $this->entity_user_id;
                $insert_data['host'] = $this->entity_user_id;
                $insert_res = $this->custom_db->insert_record('eco_stays', $insert_data);
                if ($insert_res['status'] == QUERY_SUCCESS) {
                    //map $insert_data['theme']
                    $batch_theme_map_data = array();
                    foreach ($post_params['theme'] as $k => $theme) {
                        $batch_theme_map_data[] = array(
                            'stays_origin' => $insert_res['insert_id'],
                            'theme_origin' => $theme
                        );
                    }
                    $this->custom_db->insert_records('eco_stays_theme_map', $batch_theme_map_data);
                    set_insert_message();
                    redirect('eco_stays/stays');
                    exit();
                } else {
                    //show error
                    set_error_message('UL002');
                    //delete uploaded images
                    if (empty($image_data) == false) {
                        if (empty($image_data['file_name']) == false) {
                            $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'stays/' . $image_data['file_name']; // GETTING FILE PATH
                            if (file_exists($_image)) {
                                unlink($_image);
                            }
                        }
                    }
                    redirect('eco_stays/stays');
                    exit();
                }
            }
        } else {
            $origin = intval($origin);
            if ($origin > 0) {
                $page_data['origin'] = $origin;
                $data = $this->custom_db->single_table_records('eco_stays', '*', array('origin' => $origin, 'host' => $this->entity_user_id));
                if ($data['status'] == true) {
                    $page_data['form_data'] = $data['data'][0];
                    if (isset($page_data['form_data']['amenities'])) {
                        $page_data['form_data']['amenities'] = json_decode($page_data['form_data']['amenities'], true);
                    }
                    if (isset($page_data['form_data']['theme'])) {
                        $page_data['form_data']['theme'] = json_decode($page_data['form_data']['theme'], true);
                    } else {
                        $page_data['form_data']['theme'] = array();
                    }
                } else {
                    set_error_message('UL00100');
                    redirect('eco_stays/stays');
                    exit();
                }
            }
        }

        $data_list = $this->db->query("SELECT eco_stays.*,CONCAT(host.first_name, ' ', host.last_name) AS host_name FROM eco_stays AS eco_stays LEFT JOIN user AS host ON host.user_id = eco_stays.host   WHERE 
        host.user_id = $this->entity_user_id");
        $page_data['data_list'] = $data_list->result_array();

        $this->template->view('eco_stays/stays', $page_data);
    }
public function delete_eco_stays(string $origin = ''): void
    {
        $origin = intval($origin);
        if ($origin > 0) {
            $data = $this->custom_db->single_table_records('eco_stays', '*', array('origin' => $origin, 'host' => $this->entity_user_id));
            if ($data['status'] == true) {
                //delete stays
                $this->custom_db->delete_record('eco_stays', array('origin' => $origin));
                // DELETE Image FILES
                $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'stays/' . $data['data'][0]['image']; // GETTING FILE PATH
                if (file_exists($_image)) {
                    unlink($_image);
                }

                //delete galary images 
                $gallery_images_data = $this->custom_db->single_table_records('eco_stays_gallery_images', '*', array('stays_origin' => $origin));
                if ($gallery_images_data['status'] == true) {
                    $gallery_images_data = $gallery_images_data['data'];
                    foreach ($gallery_images_data as $gallery_image) {
                        // DELETE Image FILES
                        $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'stays/' . $gallery_image['image']; // GETTING FILE PATH
                        if (file_exists($_image)) {
                            unlink($_image);
                        }
                    }
                }
                $this->custom_db->delete_record('eco_stays_gallery_images', array('stays_origin' => $origin, 'host' => $this->entity_user_id));

                //delete seasons 
                $this->custom_db->delete_record('eco_stays_seasons', array('stays_origin' => $origin, 'host' => $this->entity_user_id));

                //delete rooms
                $this->custom_db->delete_record('eco_stays_rooms', array('stays_origin' => $origin, 'host' => $this->entity_user_id));

                //delete reviews
                $this->custom_db->delete_record('eco_stays_admin_reviews', array('stays_origin' => $origin, 'host' => $this->entity_user_id));

                //delete theme map
                $this->custom_db->delete_record('eco_stays_theme_map', array('stays_origin' => $origin, 'host' => $this->entity_user_id));

                //delete room availability
                $this->custom_db->delete_record('eco_stays_room_availability', array('stays_origin' => $origin, 'booked =' => 0, 'host' => $this->entity_user_id));
                set_update_message('UL00103');
            } else {
                set_error_message('UL00100');
            }
        } else {
            set_error_message('UL00100');
        }
        redirect('eco_stays/stays');
        exit();
    }

   public function gallery_images(string $stays_origin = ''): void
    {$insert_data=[];$config=[];
        $stays_origin = intval($stays_origin);
        $page_data = array();
        if ($stays_origin > 0) {
            $stays_data = $this->custom_db->single_table_records('eco_stays', '*', array('origin' => $stays_origin, 'host' => $this->entity_user_id));
            if ($stays_data['status'] == true) {
                $image_data = array();
                if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
                  $config['upload_path'] = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'stays/';
                   // debug($config['upload_path']);die;
                    $temp_file_name = $_FILES['image']['name'];
                    $config['allowed_types'] = '*';
                    $config['file_name'] = 'eco_stays_' . $temp_file_name;
                    $config['max_size'] = '';
                    $config['max_width'] = '';
                    $config['max_height'] = '';
                    $config['remove_spaces'] = false;

                    // UPLOAD IMAGE
                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('image')) {
                        set_error_message('UL00102');
                        redirect('eco_stays/gallery_images/' . $stays_origin);
                        exit();
                    } else {
                        $image_data = $this->upload->data();

                        //insert record
                        $insert_data['image'] = $image_data['file_name'];
                        $insert_data['stays_origin'] = $stays_origin;
                        $insert_data['created_by_id'] = $this->entity_user_id;
                        $insert_res = $this->custom_db->insert_record('eco_stays_gallery_images', $insert_data);
                        if ($insert_res['status'] == QUERY_SUCCESS) {
                            set_insert_message();
                            redirect('eco_stays/gallery_images/' . $stays_origin);
                            exit();
                        } else {
                            //show error
                            set_error_message('UL002');
                            //delete uploaded images
                            if (empty($image_data) == false) {
                                if (empty($image_data['file_name']) == false) {
                                    $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'stays/' . $image_data['file_name']; // GETTING FILE PATH
                                    if (file_exists($_image)) {
                                        unlink($_image);
                                    }
                                }
                            }
                            redirect('eco_stays/gallery_images/' . $stays_origin);
                            exit();
                        }
                    }
                }
                $data_list = $this->custom_db->single_table_records('eco_stays_gallery_images', '*', array('stays_origin' => $stays_origin));
                $page_data['stays_origin'] = $stays_origin;
                if ($data_list['status'] == true) {
                    $page_data['data_list'] = $data_list['data'];
                }
                $this->template->view('eco_stays/gallery_images', $page_data);
            } else {
                // echo"hi";die;
                set_error_message('UL00100');
                // redirect('eco_stays/gallery_images/' . $stays_origin);

                redirect('eco_stays/gallery_images/');

                exit();
            }
        } else {
            set_error_message('UL00100');
            redirect('eco_stays/stays');
        }
    }
 public function delete_gallery_images(string $stays_origin = '', string $origin = ''): void
    {$page_data=[];
        $origin = intval($origin);
        if ($origin > 0) {
            // $page_data['origin'] = $origin;
            $stays_data = $this->custom_db->single_table_records('eco_stays', '*', array('origin' => $stays_origin, 'host' => $this->entity_user_id));
            if ($stays_data['status'] == true) {
                $data = $this->custom_db->single_table_records('eco_stays_gallery_images', '*', array('origin' => $origin, 'stays_origin' => $stays_origin));
                if ($data['status'] == true) {
                    // DELETE Image FILES

                    $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'stays/' . $data['data'][0]['image']; // GETTING FILE PATH
                    if (file_exists($_image)) {
                        unlink($_image);
                    }

                    $this->custom_db->delete_record('eco_stays_gallery_images', array('origin' => $origin));
                    set_update_message('UL00103');
                } else {
                    set_error_message('UL00100');
                }
            } else {
                set_error_message('UL00100');
            }
        } else {
            set_error_message('UL00100');
        }
        redirect('eco_stays/gallery_images/' . $stays_origin);
        exit();
    }
    public function seasons(string $stays_origin = '', string $origin = ''): void
    {$page_data=[];
        $stays_origin = intval($stays_origin);
        $origin = intval($origin);
        if ($stays_origin > 0) {
            $stays_data = $this->custom_db->single_table_records('eco_stays', '*', array('origin' => $stays_origin, 'host' => $this->entity_user_id));
            if ($stays_data['status'] == true) {

                $page_data['stays_data'] = $stays_data['data'][0];

                $post_params = $this->input->post();
                if (empty($post_params) == false) {
                    $insert_data = $post_params;
                    $season_avalilable = $this->check_season_avalability($stays_origin, $origin, $insert_data['start_date'], $insert_data['end_date']);

                    if (!$season_avalilable) {
                        set_error_message('UL00104');
                        redirect('eco_stays/seasons/' . $stays_origin);
                        exit();
                    }

                    $valid_start_and_end_date = $this->valid_start_and_end_date($insert_data['start_date'], $insert_data['end_date']);

                    if (!$valid_start_and_end_date) {
                        set_error_message('UL00105');
                        redirect('eco_stays/seasons/' . $stays_origin);
                        exit();
                    }

                    unset($insert_data['FID']);
                    if (isset($post_params['origin'])) {
                        //update
                        $origin = intval($post_params['origin']);
                        $data = $this->custom_db->single_table_records('eco_stays_seasons', '*', array('origin' => $origin));
                        if ($data['status'] == true) {
                            //now update
                            $update_res = $this->custom_db->update_record('eco_stays_seasons', $insert_data, array('origin' => $origin, 'stays_origin' => $stays_origin));
                            if ($update_res == QUERY_SUCCESS) {

                                //update room availability
                                $season_data = $this->custom_db->single_table_records('eco_stays_seasons', '*', array('origin' => $origin));

                                if ($season_data['status'] == true) {
                                    $room_availability_data = array();

                                    $season_data = $season_data['data'][0];
                                    $season_begin = new DateTime($season_data['start_date']);
                                    $season_end = new DateTime($season_data['end_date']);

                                    $interval_day = DateInterval::createFromDateString('1 day');
                                    $season_period = new DatePeriod($season_begin, $interval_day, $season_end);

                                    //get all room prices for the seaason
                                    $rooms_prices = $this->db->query('SELECT stays_origin,room_origin,price_origin FROM  eco_stays_room_availability WHERE season_origin = ' . $origin . ' GROUP BY price_origin')->result_array();
                                    $this->custom_db->delete_record('eco_stays_room_availability', array('season_origin' => $origin, 'booked =' => 0));

                                    foreach ($rooms_prices as $room_price) {
                                        $existing_room_availability_dates = array();
                                        $existing_room_availability_data = $this->custom_db->single_table_records('eco_stays_room_availability', 'date', array('price_origin' => $room_price['price_origin']));
                                        if ($existing_room_availability_data['status'] == true) {
                                            $existing_room_availability_dates = $existing_room_availability_data['data'];
                                        }

                                        foreach ($season_period as $period_date) {
                                            $formatted_period_date = $period_date->format('Y-m-d');
                                            if (!in_array($formatted_period_date, $existing_room_availability_dates)) {
                                                $room_availability_data[] = array(
                                                    'stays_origin' => $stays_origin,
                                                    'room_origin' => $room_price['room_origin'],
                                                    'season_origin' => $origin,
                                                    'price_origin' => $room_price['price_origin'],
                                                    'date' => $formatted_period_date,
                                                    'booked' => 0,
                                                    'holded' => 0
                                                );

                                            }
                                        }
                                    }

                                    if (empty($room_availability_data) == false) {
                                        $this->db->insert_batch('eco_stays_room_availability', $room_availability_data);
                                    }
                                }

                                set_update_message();
                                redirect('eco_stays/seasons/' . $stays_origin);
                                exit();
                            } else {
                                //show error
                                set_error_message('UL002');
                                redirect('eco_stays/seasons/' . $stays_origin);
                                exit();
                            }
                        } else {
                            //invalid request
                            set_error_message('UL00100');
                            redirect('eco_stays/seasons/' . $stays_origin);
                            exit();
                        }
                    } else {
                        //insert
                        $insert_data['stays_origin'] = $stays_origin;
                        $insert_data['created_by_id'] = $this->entity_user_id;
                        $insert_res = $this->custom_db->insert_record('eco_stays_seasons', $insert_data);
                        if ($insert_res['status'] == QUERY_SUCCESS) {
                            set_insert_message();
                            redirect('eco_stays/seasons/' . $stays_origin);
                            exit();
                        } else {
                            //show error
                            set_error_message('UL002');
                            redirect('eco_stays/seasons/' . $stays_origin);
                            exit();
                        }
                    }


                } else {
                    $origin = intval($origin);
                    if ($origin > 0) {
                        $page_data['origin'] = $origin;
                        $data = $this->custom_db->single_table_records('eco_stays_seasons', '*', array('origin' => $origin));
                        if ($data['status'] == true) {
                            $page_data['form_data'] = $data['data'][0];
                        } else {
                            set_error_message('UL00100');
                            redirect('eco_stays/seasons/' . $stays_origin);
                            exit();
                        }
                    }
                }
                $data_list = $this->custom_db->single_table_records('eco_stays_seasons', '*', array('stays_origin' => $stays_origin));

                
                // $page_data['data_list'] = $data_list['data'];
                $this->template->view('eco_stays/seasons');
               

            } else {

                set_error_message('UL00100');
                // redirect('eco_stays/seasons/' . $stays_origin);
                redirect('eco_stays/seasons/');
                exit();
            }
        } else {
            set_error_message('UL00100');
            redirect('eco_stays/stays');
        }
    }
     public function delete_seasons(string $stays_origin = '', string $origin = ''): void
    {
        $stays_origin = intval($stays_origin);
        if ($stays_origin > 0) {
            $stays_data = $this->custom_db->single_table_records('eco_stays', '*', array('origin' => $stays_origin, 'host' => $this->entity_user_id));
            if ($stays_data['status'] == true) {
                $origin = intval($origin);
                if ($origin > 0) {
                    $data = $this->custom_db->single_table_records('eco_stays_seasons', '*', array('origin' => $origin));
                    if ($data['status'] == true) {
                        //delete season
                        $this->custom_db->delete_record('eco_stays_seasons', array('origin' => $origin));

                        //delete room availability
                        $this->custom_db->delete_record('eco_stays_room_availability', array('season_origin' => $origin, 'booked =' => 0));

                        set_update_message('UL00108');
                    } else {
                        set_error_message('UL00100');
                    }
                } else {
                    set_error_message('UL00100');
                }
                redirect('eco_stays/seasons/' . $stays_origin);
                exit();
            } else {
                set_error_message('UL00100');
                redirect('eco_stays/seasons/' . $stays_origin);
                exit();
            }
        } else {
            set_error_message('UL00100');
            redirect('eco_stays/stays');
        }
    }
   private function check_season_avalability(int $stays_origin, int $season_origin = 0, string $start_date, string $end_date): bool
    {
        $avalability = true;
        $stays_origin = intval($stays_origin);
        $season_origin = intval($season_origin);
        $condt = array('stays_origin' => $stays_origin);
        if ($season_origin > 0) {
            $condt['origin !='] = $season_origin;
        }
        $data_list = $this->custom_db->single_table_records('eco_stays_seasons', '*', $condt);
        if ($data_list['status'] == true) {
            $avalability = true;
            $seasons = $data_list['data'];
            foreach ($seasons as  $season) {
                if ($start_date >= $season['start_date']) {
                    if ($start_date <= $season['end_date']) {
                        $avalability = false;
                    }
                } else {
                    if ($end_date >= $season['start_date']) {
                        $avalability = false;
                    }
                }
            }
        }

        return $avalability;
    }
 private function valid_start_and_end_date(string $start_date, string $end_date): bool
    {
        if ($start_date > $end_date) {
            return false;
        }
        return true;
    }

   public function rooms(string $stays_origin = '', string $origin = ''): void
    {$page_data=[];
        $stays_origin = intval($stays_origin);
        $origin = intval($origin);
        if ($stays_origin > 0) {
            $stays_data = $this->custom_db->single_table_records('eco_stays', '*', array('origin' => $stays_origin, 'host' => $this->entity_user_id));
            if ($stays_data['status'] == true) {
                $page_data['stays_data'] = $stays_data['data'][0];
                $post_params = $this->input->post();
                if (empty($post_params) == false) {
                    $insert_data = $post_params;

                    if (isset($insert_data['amenities'])) {
                        $insert_data['amenities'] = json_encode($insert_data['amenities']);
                    }

                    unset($insert_data['FID']);
                    if (isset($post_params['origin'])) {
                        //update
                        $origin = intval($post_params['origin']);
                        $data = $this->custom_db->single_table_records('eco_stays_rooms', '*', array('origin' => $origin));
                        if ($data['status'] == true) {
                            //now update
                            $update_res = $this->custom_db->update_record('eco_stays_rooms', $insert_data, array('origin' => $origin, 'stays_origin' => $stays_origin));
                            if ($update_res == QUERY_SUCCESS) {
                                set_update_message();
                                redirect('eco_stays/rooms/' . $stays_origin);
                                exit();
                            } else {
                                //show error
                                set_error_message('UL002');
                                redirect('eco_stays/rooms/' . $stays_origin);
                                exit();
                            }
                        } else {
                            //invalid request
                            set_error_message('UL00100');
                            redirect('eco_stays/rooms/' . $stays_origin);
                            exit();
                        }
                    } else {
                        //insert
                        $insert_data['stays_origin'] = $stays_origin;
                        $insert_data['created_by_id'] = $this->entity_user_id;
                        $insert_res = $this->custom_db->insert_record('eco_stays_rooms', $insert_data);
                        if ($insert_res['status'] == QUERY_SUCCESS) {
                            set_insert_message();
                            redirect('eco_stays/rooms/' . $stays_origin);
                            exit();
                        } else {
                            //show error
                            set_error_message('UL002');
                            redirect('eco_stays/rooms/' . $stays_origin);
                            exit();
                        }
                    }
                } else {
                    $origin = intval($origin);
                    if ($origin > 0) {
                        $page_data['origin'] = $origin;
                        $data = $this->custom_db->single_table_records('eco_stays_rooms', '*', array('origin' => $origin));
                        if ($data['status'] == true) {
                            $page_data['form_data'] = $data['data'][0];
                            if (isset($page_data['form_data']['amenities'])) {
                                $page_data['form_data']['amenities'] = json_decode($page_data['form_data']['amenities'], true);
                            }
                        } else {
                            set_error_message('UL00100');
                            redirect('eco_stays/rooms/' . $stays_origin);
                            exit();
                        }
                    }
                }
                $data_list = $this->db->query("SELECT eco_stays_rooms.*,room_type.name AS type,board_type.name AS board_type  FROM eco_stays_rooms AS eco_stays_rooms 
                                                LEFT JOIN eco_stays_room_types AS room_type ON room_type.origin = eco_stays_rooms.type
                                                LEFT JOIN eco_stays_board_types AS board_type ON board_type.origin = eco_stays_rooms.board_type
                                                WHERE eco_stays_rooms.stays_origin = " . $stays_origin);
                $page_data['data_list'] = $data_list->result_array();
                $this->template->view('eco_stays/rooms', $page_data);
            } else {
                set_error_message('UL00100');
                redirect('eco_stays/rooms/');

                exit();
            }
        } else {
            set_error_message('UL00100');
            redirect('eco_stays/stays');
        }
    }

   public function delete_rooms(string $stays_origin = '', string $origin = ''): void
    {
        $stays_origin = intval($stays_origin);
        if ($stays_origin > 0) {
            $stays_data = $this->custom_db->single_table_records('eco_stays', '*', array('origin' => $stays_origin, 'host' => $this->entity_user_id));
            if ($stays_data['status'] == true) {
                $origin = intval($origin);
                if ($origin > 0) {
                    $data = $this->custom_db->single_table_records('eco_stays_rooms', '*', array('origin' => $origin));
                    if ($data['status'] == true) {
                        //delete room
                        $this->custom_db->delete_record('eco_stays_rooms', array('origin' => $origin));

                        //delete price 
                        $this->custom_db->delete_record('eco_stays_room_prices', array('room_origin' => $origin));

                        //delete cancellation policy
                        $this->custom_db->delete_record('eco_stays_room_cancellation_policy', array('room_origin' => $origin));

                        //delete room availability
                        $this->custom_db->delete_record('eco_stays_room_availability', array('room_origin' => $origin, 'booked =' => 0));

                        set_update_message('UL00103');
                    } else {
                        set_error_message('UL00100');
                    }
                } else {
                    set_error_message('UL00100');
                }
                redirect('eco_stays/rooms/' . $stays_origin);
                exit();
            } else {
                set_error_message('UL00100');
                redirect('eco_stays/rooms/' . $stays_origin);
                exit();
            }
        } else {
            set_error_message('UL00100');
            redirect('eco_stays/stays');
        }
    }
  public function room_price_management(int $room_origin = 0, int $origin = 0): void
    {
        $room_origin = intval($room_origin);
        $origin = intval($origin);
        $page_data = array();

        $room_data = $this->custom_db->single_table_records('eco_stays_rooms', '*', array('origin' => $room_origin));
        if ($room_data['status'] == true) {
            $room_data = $room_data['data'][0];
            $post_params = $this->input->post();
            if (empty($post_params) == false) {
                $insert_data = $post_params;
                $insert_data_prices = array();
                foreach ($insert_data as $key => $value) {
                    if (strpos($key, '_price') !== false) {
                        $insert_data_prices[$key] = $value;
                        unset($insert_data[$key]);
                    }
                }
                $insert_data['prices'] = json_encode($insert_data_prices);

                if ($origin > 0) {

                    //check if another price data exist for season
                    $data = $this->custom_db->single_table_records('eco_stays_room_prices', '*', array('origin !=' => $origin, 'room_origin' => $room_origin, 'season_origin' => $insert_data['season_origin']));

                    if ($data['status'] == true) {

                        //show error if price data exist for season
                        set_error_message('UL00107');
                        redirect('eco_stays/room_price_management/' . $room_origin);
                        exit();
                    } else {
                        //update
                        $data = $this->custom_db->single_table_records('eco_stays_room_prices', '*', array('origin' => $origin));
                        if ($data['status'] == true) {
                            //now update
                            $update_res = $this->custom_db->update_record('eco_stays_room_prices', $insert_data, array('origin' => $origin, 'room_origin' => $room_origin));
                            if ($update_res == QUERY_SUCCESS) {
                                //update room availability
                                //delete existing
                                $this->custom_db->delete_record('eco_stays_room_availability', array('price_origin' => $origin, 'booked =' => 0));

                                $existing_room_availability_dates = array();
                                $existing_room_availability_data = $this->custom_db->single_table_records('eco_stays_room_availability', 'date', array('price_origin' => $origin));
                                if ($existing_room_availability_data['status'] == true) {
                                    $existing_room_availability_dates = array_column($existing_room_availability_data['data'], "date");
                                }

                                $room_availability_data = array();
                                $season_data = $this->custom_db->single_table_records('eco_stays_seasons', '*', array('origin' => $insert_data['season_origin']));

                                if ($season_data['status'] == true) {
                                    $season_data = $season_data['data'][0];
                                    $season_begin = new DateTime($season_data['start_date']);
                                    $season_end = new DateTime($season_data['end_date']);

                                    $interval_day = DateInterval::createFromDateString('1 day');
                                    $season_period = new DatePeriod($season_begin, $interval_day, $season_end);

                                    foreach ($season_period as $period_date) {
                                        $date_now = new DateTime();
                                        $date_now = $date_now->format('Y-m-d');
                                        $formatted_period_date = $period_date->format('Y-m-d');
                                        if ($formatted_period_date < $date_now) {
                                            continue;
                                        }
                                        if (!in_array($formatted_period_date, $existing_room_availability_dates)) {
                                            $room_availability_data[] = array(
                                                'stays_origin' => $room_data['stays_origin'],
                                                'room_origin' => $room_origin,
                                                'season_origin' => $insert_data['season_origin'],
                                                'price_origin' => $origin,
                                                'date' => $formatted_period_date,
                                                'booked' => 0,
                                                'holded' => 0
                                            );

                                        }
                                    }
                                }

                                if (empty($room_availability_data) == false) {
                                    $this->db->insert_batch('eco_stays_room_availability', $room_availability_data);
                                }

                                set_update_message();
                                redirect('eco_stays/room_price_management/' . $room_origin);
                                exit();
                            } else {
                                //show error
                                set_error_message('UL002');
                                redirect('eco_stays/room_price_management/' . $room_origin);
                                exit();
                            }
                        } else {
                            //invalid request
                            set_error_message('UL00100');
                            redirect('eco_stays/room_price_management/' . $room_origin);
                            exit();
                        }
                    }

                } else {

                    //check if price data exist for season
                    $data = $this->custom_db->single_table_records('eco_stays_room_prices', '*', array('room_origin' => $room_origin, 'season_origin' => $insert_data['season_origin']));

                    if ($data['status'] == true) {
                        //show error if price data exist for season
                        set_error_message('UL00107');
                        redirect('eco_stays/room_price_management/' . $room_origin);
                        exit();
                    } else {
                        //insert
                        $insert_data['room_origin'] = $room_origin;
                        $insert_data['created_by_id'] = $this->entity_user_id;
                        $insert_res = $this->custom_db->insert_record('eco_stays_room_prices', $insert_data);
                        if ($insert_res['status'] == QUERY_SUCCESS) {

                            //add room availability
                            $room_availability_data = array();
                            $season_data = $this->custom_db->single_table_records('eco_stays_seasons', '*', array('origin' => $insert_data['season_origin']));

                            if ($season_data['status'] == true) {
                                $season_data = $season_data['data'][0];
                                $season_begin = new DateTime($season_data['start_date']);
                                $season_end = new DateTime($season_data['end_date']);

                                $interval_day = DateInterval::createFromDateString('1 day');
                                $season_period = new DatePeriod($season_begin, $interval_day, $season_end);

                                foreach ($season_period as $period_date) {
                                    $room_availability_data[] = array(
                                        'stays_origin' => $room_data['stays_origin'],
                                        'room_origin' => $room_origin,
                                        'season_origin' => $insert_data['season_origin'],
                                        'price_origin' => $insert_res['insert_id'],
                                        'date' => $period_date->format('Y-m-d'),
                                        'booked' => 0,
                                        'holded' => 0
                                    );
                                }
                            }

                            if (empty($room_availability_data) == false) {
                                $this->db->insert_batch('eco_stays_room_availability', $room_availability_data);
                            }

                            set_insert_message();
                            redirect('eco_stays/room_price_management/' . $room_origin);
                            exit();
                        } else {
                            //show error
                            set_error_message('UL002');
                            redirect('eco_stays/room_price_management/' . $room_origin);
                            exit();
                        }
                    }
                }

            } else {
                if ($origin > 0) {
                    $page_data['origin'] = $origin;
                    $data = $this->custom_db->single_table_records('eco_stays_room_prices', '*', array('origin' => $origin));
                    if ($data['status'] == true) {
                        $form_data = $data['data'][0];
                        if (isset($form_data['prices'])) {
                            $form_data_prices = json_decode($form_data['prices'], TRUE);
                            $form_data = array_merge($form_data, $form_data_prices);
                        }
                        $page_data['form_data'] = $form_data;
                    } else {
                        set_error_message('UL00100');
                        redirect('eco_stays/room_price_management/' . $room_origin);
                        exit();
                    }
                }
            }

            $page_data['seasons'] = $this->db->query("SELECT origin,name FROM eco_stays_seasons WHERE stays_origin = " . $room_data['stays_origin'])->result_array();
            $page_data['room_data'] = $room_data;

            $room_prices = $this->db->query(
                "SELECT eco_stays_room_prices.*,  eco_stays_seasons.name AS season_name, eco_stays_seasons.start_date AS start_date, eco_stays_seasons.end_date AS end_date
                FROM eco_stays_room_prices 
                LEFT JOIN eco_stays_seasons ON eco_stays_seasons.origin = eco_stays_room_prices.season_origin
                WHERE eco_stays_room_prices.room_origin = " . $room_origin
            )->result_array();


            $page_data['data_list'] = $room_prices;


            $this->template->view('eco_stays/room_price_management', $page_data);
        } else {
            set_error_message('UL00100');
            redirect('eco_stays/stays');
        }
    }

   public function room_availability_calendar(int $room_origin = 0): void
    {
        $room_origin = intval($room_origin);
        $page_data = array();

        $room_data = $this->custom_db->single_table_records('eco_stays_rooms', '*', array('origin' => $room_origin));
        if ($room_data['status'] == true) {
            $room_data = $room_data['data'][0];

            $room_availability_query = "SELECT room_availability.*,room.quantity AS quantity, (room.quantity - room_availability.booked) AS available, season.name AS season_name
                                        FROM eco_stays_room_availability AS room_availability
                                        LEFT JOIN eco_stays_rooms AS room ON room.origin = room_availability.room_origin
                                        LEFT JOIN eco_stays_seasons AS season ON season.origin = room_availability.season_origin
                                        WHERE room_availability.room_origin = " . $room_origin;
            $room_availability_data = $this->db->query($room_availability_query)->result_array();

            $room_availability_formatted_data = array();

            foreach ($room_availability_data as $room_availability_data_item) {
                $room_availability_formatted_data[] = array(
                    'start' => $room_availability_data_item['date'],
                    'title' => 'Available: ' . $room_availability_data_item['available'],
                );
                $room_availability_formatted_data[] = array(
                    'start' => $room_availability_data_item['date'],
                    'title' => 'Booked: ' . $room_availability_data_item['booked'],
                );
                $room_availability_formatted_data[] = array(
                    'start' => $room_availability_data_item['date'],
                    'title' => 'Total Rooms: ' . $room_availability_data_item['quantity'],
                );
            }

            $page_data['room_data'] = $room_data;
            $page_data['room_availability_data'] = $room_availability_formatted_data;
            $this->template->view('eco_stays/room_availability_calendar', $page_data);
        } else {
            set_error_message('UL00100');
            redirect('eco_stays/stays');
        }
    }
 public function delete_room_price(int $room_origin = 0, int $origin = 0): void
    {
        $room_origin = intval($room_origin);
        $origin = intval($origin);

        $room_data = $this->custom_db->single_table_records('eco_stays_rooms', '*', array('origin' => $room_origin));
        if ($room_data['status'] == true) {
            if ($origin > 0) {
                $data = $this->custom_db->single_table_records('eco_stays_room_prices', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    //delete prices
                    $this->custom_db->delete_record('eco_stays_room_prices', array('origin' => $origin));

                    //delete room availability
                    $this->custom_db->delete_record('eco_stays_room_availability', array('price_origin' => $origin, 'booked =' => 0));

                    set_update_message('UL00108');
                } else {
                    set_error_message('UL00100');
                }
            } else {
                set_error_message('UL00100');
            }

            redirect('eco_stays/room_price_management/' . $room_origin);
            exit();
        } else {
            set_error_message('UL00100');
            redirect('eco_stays/stays');
        }
    }

    public function room_cancellation_policy(int $room_origin = 0, int $origin = 0): void
    {$page_data=[];
        $room_origin = intval($room_origin);
        $origin = intval($origin);

        $room_data = $this->custom_db->single_table_records('eco_stays_rooms', '*', array('origin' => $room_origin));
        if ($room_data['status'] == true) {
            $room_data = $room_data['data'][0];
            $post_params = $this->input->post();
            if (empty($post_params) == false) {
                $insert_data = $post_params;
                $valid_cancellation_days = $this->check_cancellation_days_validity($room_origin, $origin, $insert_data['to_before_days']);

                if (!$valid_cancellation_days) {
                    set_error_message('UL00106');
                    redirect('eco_stays/room_cancellation_policy/' . $room_origin);
                    exit();
                }
                unset($insert_data['FID']);
                if (isset($post_params['origin'])) {
                    //update
                    $origin = intval($post_params['origin']);
                    $data = $this->custom_db->single_table_records('eco_stays_room_cancellation_policy', '*', array('origin' => $origin));
                    if ($data['status'] == true) {
                        //now update
                        $update_res = $this->custom_db->update_record('eco_stays_room_cancellation_policy', $insert_data, array('origin' => $origin, 'room_origin' => $room_origin));
                        if ($update_res == QUERY_SUCCESS) {
                            set_update_message();
                            redirect('eco_stays/room_cancellation_policy/' . $room_origin);
                            exit();
                        } else {
                            //show error
                            set_error_message('UL002');
                            redirect('eco_stays/room_cancellation_policy/' . $room_origin);
                            exit();
                        }
                    } else {
                        //invalid request
                        set_error_message('UL00100');
                        redirect('eco_stays/room_cancellation_policy/' . $room_origin);
                        exit();
                    }
                } else {
                    //insert
                    $insert_data['room_origin'] = $room_origin;
                    $insert_data['created_by_id'] = $this->entity_user_id;
                    $insert_res = $this->custom_db->insert_record('eco_stays_room_cancellation_policy', $insert_data);
                    if ($insert_res['status'] == QUERY_SUCCESS) {
                        set_insert_message();
                        redirect('eco_stays/room_cancellation_policy/' . $room_origin);
                        exit();
                    } else {
                        //show error
                        set_error_message('UL002');
                        redirect('eco_stays/room_cancellation_policy/' . $room_origin);
                        exit();
                    }
                }


            } else {
                $origin = intval($origin);
                if ($origin > 0) {
                    $page_data['origin'] = $origin;
                    $data = $this->custom_db->single_table_records('eco_stays_room_cancellation_policy', '*', array('origin' => $origin));
                    if ($data['status'] == true) {
                        $page_data['form_data'] = $data['data'][0];
                    } else {
                        set_error_message('UL00100');
                        redirect('eco_stays/room_cancellation_policy/' . $room_origin);
                        exit();
                    }
                }
            }

            $data_list = $this->db->query("SELECT *
                                            FROM eco_stays_room_cancellation_policy
                                            WHERE room_origin = " . $room_origin);
            $page_data['data_list'] = $data_list->result_array();
            $page_data['room_data'] = $room_data;
            $this->template->view('eco_stays/room_cancellation_policy', $page_data);
        } else {
            set_error_message('UL00100');
            redirect('eco_stays/stays');
        }
    }
   public function delete_room_cancellation_policy(int $room_origin = 0, int $origin = 0): void
    {
        $room_origin = intval($room_origin);
        if ($room_origin > 0) {
            $room_data = $this->custom_db->single_table_records('eco_stays_rooms', '*', array('origin' => $room_origin));
            if ($room_data['status'] == true) {
                $origin = intval($origin);
                if ($origin > 0) {
                    $data = $this->custom_db->single_table_records('eco_stays_room_cancellation_policy', '*', array('origin' => $origin));
                    if ($data['status'] == true) {
                        $this->custom_db->delete_record('eco_stays_room_cancellation_policy', array('origin' => $origin));
                        set_update_message('UL00108');
                    } else {
                        set_error_message('UL00100');
                    }
                } else {
                    set_error_message('UL00100');
                }
                redirect('eco_stays/room_cancellation_policy/' . $room_origin);
                exit();
            } else {
                set_error_message('UL00100');
                redirect('eco_stays/room_cancellation_policy/' . $room_origin);
                exit();
            }
        } else {
            set_error_message('UL00100');
            redirect('eco_stays/stays');
        }
    }
    private function check_cancellation_days_validity(int $room_origin = 0, int $origin = 0, int $to_before_days): bool
    {$cancellation_policy=[];
        $room_origin = intval($room_origin);
        $origin = intval($origin);


        $cancellation_policies = $this->db->query("SELECT * FROM eco_stays_room_cancellation_policy WHERE room_origin = " . $room_origin)->result_array();

        $existing_upto_days = array();

        if (empty($cancellation_policies) == false) {
            // $existing_upto_days = array_column($cancellation_policy['to_before_days']);
        }

        if (in_array($to_before_days, $existing_upto_days)) {
            return false;
        }





        return true;
    }

   public function reviews(int $stays_origin = 0): void
    {
        $page_data = array();
        $stays_origin = intval($stays_origin);
        if ($stays_origin > 0) {
            $reviews = $this->db->query(
                'SELECT crateria.origin AS origin, crateria.name AS crateria_name, reviews.rating AS rating
                FROM eco_stays_review_criteria AS crateria
                LEFT JOIN eco_stays_admin_reviews AS reviews ON reviews.review_criteria_id = crateria.origin
                WHERE crateria.status = 1 AND stays_origin = ' . $stays_origin
            )->result_array();

            $post_params = $this->input->post();

            if (empty($post_params) == false) {
                $creterias_id = array_column($reviews, 'origin');
                $old_ratings = array_column($reviews, 'rating');
                $old_ratings_map = array_combine($creterias_id, $old_ratings);

                foreach ($post_params as $criteria => $rating) {
                    if (strpos($criteria, 'criteria_') !== false && 0 < $rating && $rating <= 5) {
                        $review_criteria_id = str_replace('criteria_', '', $criteria);

                        $insert_data = array(
                            'stays_origin' => $stays_origin,
                            'review_criteria_id' => $review_criteria_id,
                            'rating' => $rating
                        );
                        if ($old_ratings_map[$review_criteria_id] === null) {
                            //insert
                            $this->custom_db->insert_record('eco_stays_admin_reviews', $insert_data);
                        } else {
                            //update
                            $condt = array(
                                'stays_origin' => $stays_origin,
                                'review_criteria_id' => $review_criteria_id
                            );
                            $this->custom_db->update_record('eco_stays_admin_reviews', $insert_data, $condt);
                        }
                    }
                }
                set_update_message();
                redirect('eco_stays/stays');
            }

            $reviews = $this->db->query(
                'SELECT crateria.origin AS origin, crateria.name AS crateria_name, reviews.rating AS rating
                FROM eco_stays_review_criteria AS crateria
                LEFT JOIN eco_stays_admin_reviews AS reviews ON reviews.review_criteria_id = crateria.origin
                WHERE crateria.status = 1
                GROUP BY crateria.origin'
            )->result_array();

            $page_data['stays_origin'] = $stays_origin;
            $page_data['reviews'] = $reviews;

            $this->template->view('eco_stays/review', $page_data);

        } else {
            set_error_message('UL00100');
            redirect('eco_stays/stays');
        }
    }
    public function types(int $origin = 0): void
    {$config=[];
        $page_data = array();
        $page_data['origin'] = 0;

        $post_params = $this->input->post();

        if (empty($post_params) == false) {
            if (isset($post_params['origin'])) {
                //update
                $origin = intval($post_params['origin']);
                $data = $this->custom_db->single_table_records('eco_stays_types', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    //now update
                    $image_data = array();
                    if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
                        $config['upload_path'] = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'types/';
                        $temp_file_name = $_FILES['image']['name'];
                        $config['allowed_types'] = '*';
                        $config['file_name'] = 'eco_stays_types_' . $temp_file_name;
                        $config['max_size'] = '100000000';
                        $config['max_width'] = '';
                        $config['max_height'] = '';
                        $config['remove_spaces'] = false;

                        // DELETE OLD FILES
                        if (empty($data['data'][0]['image']) == false) {
                            $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'types/' . $data['data'][0]['image']; // GETTING FILE PATH
                            if (file_exists($_image)) {
                                unlink($_image);
                            }
                        }
                        // UPLOAD IMAGE
                        $this->load->library('upload', $config);
                        $this->upload->initialize($config);
                        if (!$this->upload->do_upload('image')) {
                            set_error_message('UL00102');
                        } else {
                            $image_data = $this->upload->data();
                        }
                    }
                    $update_data = array(
                        'name' => $post_params['name'],

                        'status' => $post_params['status']
                    );
                    if (empty($image_data) == false) {
                        $update_data['image'] = $image_data['file_name'];
                    }
                    $this->custom_db->update_record('eco_stays_types', $update_data, array('origin' => $origin));
                    set_update_message();

                } else {
                    //invalid request
                    set_error_message('UL00100');
                }
            } else {

                //print_r($post_params);die;
                //insert
                if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
                    $config['upload_path'] = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'types/';
                    $temp_file_name = $_FILES['image']['name'];
                    $config['allowed_types'] = '*';
                    $config['file_name'] = 'eco_stays_types_' . $temp_file_name;
                    $config['max_size'] = '1000000';
                    $config['max_width'] = '';
                    $config['max_height'] = '';
                    $config['remove_spaces'] = false;

                    // UPLOAD IMAGE
                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('image')) {
                        set_error_message('UL00102');
                    } else {
                        $image_data = $this->upload->data();
                        $insert_data = array(
                            'name' => $post_params['name'],
                            'image' => $image_data['file_name'],
                            'status' => $post_params['status'],
                            'created_by_id' => $this->entity_user_id
                        );


                        $this->custom_db->insert_record('eco_stays_types', $insert_data);
                        set_insert_message();
                    }
                } elseif ($_FILES['image']['name'] == '') {
                    $insert_data = array(
                        'name' => $post_params['name'],
                        'status' => $post_params['status'],
                        'created_by_id' => $this->entity_user_id
                    );


                    $this->custom_db->insert_record('eco_stays_types', $insert_data);
                    set_insert_message();
                    //echo"hi"; die;

                } else {
                    // show msg image is important
                    set_error_message('UL00101');
                }
            }
            redirect('eco_stays/types');
            exit();
        } else {
            $origin = intval($origin);
            if ($origin > 0) {
                $page_data['origin'] = $origin;
                $data = $this->custom_db->single_table_records('eco_stays_types', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    $page_data['form_data'] = $data['data'][0];
                } else {
                    redirect('eco_stays/types');
                    exit();
                }
            }
        }

        $data_list = $this->custom_db->single_table_records('eco_stays_types', '*');

        if ($data_list['status'] == true) {
            $page_data['data_list'] = $data_list['data'];
        }

        $this->template->view('eco_stays/types', $page_data);
    }


   public function delete_types(int $origin = 0): void
    {$page_data=[];
        $origin = intval($origin);
        if ($origin > 0) {
            // $page_data['origin'] = $origin;
            $data = $this->custom_db->single_table_records('eco_stays_types', '*', array('origin' => $origin));
            if ($data['status'] == true) {
                // DELETE Image FILES

                $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'types/' . $data['data'][0]['image']; // GETTING FILE PATH
                if (file_exists($_image)) {
                    unlink($_image);
                }

                $this->custom_db->delete_record('eco_stays_types', array('origin' => $origin));
                set_update_message('UL00103');
            } else {
                set_error_message('UL00100');
            }
        } else {
            set_error_message('UL00100');
        }
        redirect('eco_stays/types');
        exit();
    }

    public function room_types(int $origin = 0): void
    {
        $page_data = array();
        $page_data['origin'] = 0;

        $post_params = $this->input->post();



        if (empty($post_params) == false) {
            if (isset($post_params['origin'])) {
                //update
                $origin = intval($post_params['origin']);
                $data = $this->custom_db->single_table_records('eco_stays_room_types', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    //now update
                    // $image_data = array();

                    $update_data = array(
                        'name' => $post_params['name'],

                        'status' => $post_params['status']
                    );

                    $this->custom_db->update_record('eco_stays_room_types', $update_data, array('origin' => $origin));
                    set_update_message();

                } else {

                    set_error_message('UL00100');
                }
            } else {

                //insert



                $insert_data = array(
                    'name' => $post_params['name'],

                    'status' => $post_params['status'],
                    'created_by_id' => $this->entity_user_id
                );

                //print_r($insert_data)
                $this->custom_db->insert_record('eco_stays_room_types', $insert_data);

                set_insert_message();


            }
            redirect('eco_stays/room_types');
            exit();
        } else {
            $origin = intval($origin);
            if ($origin > 0) {
                $page_data['origin'] = $origin;
                $data = $this->custom_db->single_table_records('eco_stays_room_types', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    $page_data['form_data'] = $data['data'][0];
                } else {
                    redirect('eco_stays/room_types');
                    exit();
                }
            }
        }

        $data_list = $this->custom_db->single_table_records('eco_stays_room_types', '*');

        if ($data_list['status'] == true) {
            $page_data['data_list'] = $data_list['data'];
        }

        $this->template->view('eco_stays/room_types', $page_data);
    }
 public function delete_room_types(int $origin = 0): void
    {$page_data=[];
        $origin = intval($origin);
        if ($origin > 0) {
            // $page_data['origin'] = $origin;
            $data = $this->custom_db->single_table_records('eco_stays_room_types', '*', array('origin' => $origin));
            if ($data['status'] == true) {
                // DELETE Image FILES

                $this->custom_db->delete_record('eco_stays_room_types', array('origin' => $origin));
                set_update_message('UL00103');
            } else {
                set_error_message('UL00100');
            }
        } else {
            set_error_message('UL00100');
        }
        redirect('eco_stays/room_types');
        exit();
    }

  public function board_types(int $origin = 0): void
    {
        $page_data = array();
        $page_data['origin'] = 0;

        $post_params = $this->input->post();

        if (empty($post_params) == false) {
            if (isset($post_params['origin'])) {
                //update
                $origin = intval($post_params['origin']);
                $data = $this->custom_db->single_table_records('eco_stays_board_types', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    //now update
                    $image_data = array();

                    $update_data = array(
                        'name' => $post_params['name'],

                        'status' => $post_params['status']
                    );

                    $this->custom_db->update_record('eco_stays_board_types', $update_data, array('origin' => $origin));
                    set_update_message();

                } else {

                    set_error_message('UL00100');
                }
            } else {

                //insert
                $insert_data = array(
                    'name' => $post_params['name'],

                    'status' => $post_params['status'],
                    'created_by_id' => $this->entity_user_id
                );

                //print_r($insert_data)
                $this->custom_db->insert_record('eco_stays_board_types', $insert_data);

                set_insert_message();


            }
            redirect('eco_stays/board_types');
            exit();
        } else {
            $origin = intval($origin);
            if ($origin > 0) {
                $page_data['origin'] = $origin;
                $data = $this->custom_db->single_table_records('eco_stays_board_types', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    $page_data['form_data'] = $data['data'][0];
                } else {
                    redirect('eco_stays/board_types');
                    exit();
                }
            }
        }

        $data_list = $this->custom_db->single_table_records('eco_stays_board_types', '*');

        if ($data_list['status'] == true) {
            $page_data['data_list'] = $data_list['data'];
        }

        $this->template->view('eco_stays/board_types', $page_data);
    }
   public function delete_board_types(int $origin = 0): void
    {$page_data=[];
        $origin = intval($origin);
        if ($origin > 0) {
            // $page_data['origin'] = $origin;
            $data = $this->custom_db->single_table_records('eco_stays_board_types', '*', array('origin' => $origin));
            if ($data['status'] == true) {
                // DELETE Image FILES

                $this->custom_db->delete_record('eco_stays_board_types', array('origin' => $origin));
                set_update_message('UL00103');
            } else {
                set_error_message('UL00100');
            }
        } else {
            set_error_message('UL00100');
        }
        redirect('eco_stays/board_types');
        exit();
    }
    public function eco_stays_amenities(int $origin = 0): void
    {$config=[];
        $page_data = array();
        $page_data['origin'] = 0;

        $post_params = $this->input->post();

        if (empty($post_params) == false) {
            if (isset($post_params['status']) == false) {
                $post_params['status'] = true;
            }
            if (isset($post_params['origin'])) {
                //update
                $origin = intval($post_params['origin']);
                $data = $this->custom_db->single_table_records('eco_stays_amenities', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    //now update
                    $image_data = array();
                    if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
                        $config['upload_path'] = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'eco_stays_amenities/';
                        $temp_file_name = $_FILES['image']['name'];
                        $config['allowed_types'] = '*';
                        $config['file_name'] = 'eco_stays_amenities_' . $temp_file_name;
                        $config['max_size'] = '100000000';
                        $config['max_width'] = '';
                        $config['max_height'] = '';
                        $config['remove_spaces'] = false;

                        //print_r($config);die;

                        // DELETE OLD FILES
                        if (empty($data['data'][0]['image']) == false) {
                            $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'eco_stays_amenities/' . $data['data'][0]['image']; // GETTING FILE PATH
                            if (file_exists($_image)) {
                                unlink($_image);
                            }
                        }
                        // UPLOAD IMAGE
                        $this->load->library('upload', $config);
                        $this->upload->initialize($config);
                        if (!$this->upload->do_upload('image')) {
                            set_error_message('UL00102');
                        } else {
                            $image_data = $this->upload->data();
                        }
                    }
                    $update_data = array(
                        'name' => $post_params['name'],
                        'status' => $post_params['status']
                    );
                    if (empty($image_data) == false) {
                        $update_data['image'] = $image_data['file_name'];
                    }
                    $this->custom_db->update_record('eco_stays_amenities', $update_data, array('origin' => $origin));
                    set_update_message();

                } else {
                    //invalid request
                    set_error_message('UL00100');
                }
            } else {

                //print_r($post_params);die;
                //insert
                if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
                    $config['upload_path'] = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'eco_stays_amenities/';
                    $temp_file_name = $_FILES['image']['name'];
                    $config['allowed_types'] = '*';
                    $config['file_name'] = 'eco_stays_amenities_' . $temp_file_name;
                    $config['max_size'] = '1000000';
                    $config['max_width'] = '';
                    $config['max_height'] = '';
                    $config['remove_spaces'] = false;

                    // UPLOAD IMAGE
                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('image')) {
                        set_error_message('UL00102');
                    } else {
                        $image_data = $this->upload->data();
                        $insert_data = array(
                            'name' => $post_params['name'],
                            'image' => $image_data['file_name'],
                            'status' => $post_params['status'],
                            'created_by_id' => $this->entity_user_id
                        );


                        $this->custom_db->insert_record('eco_stays_amenities', $insert_data);
                        set_insert_message();
                    }
                } elseif ($_FILES['image']['name'] == '') {
                    $insert_data = array(
                        'name' => $post_params['name'],
                        'status' => $post_params['status'],
                        'created_by_id' => $this->entity_user_id
                    );


                    $this->custom_db->insert_record('eco_stays_amenities', $insert_data);
                    set_insert_message();
                    //echo"hi"; die;

                } else {
                    // show msg image is important
                    set_error_message('UL00101');
                }
            }
            redirect('eco_stays/eco_stays_amenities');
            exit();
        } else {
            $origin = intval($origin);
            if ($origin > 0) {
                $page_data['origin'] = $origin;
                $data = $this->custom_db->single_table_records('eco_stays_amenities', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    $page_data['form_data'] = $data['data'][0];
                } else {
                    redirect('eco_stays/types');
                    exit();
                }
            }
        }

        $data_list = $this->custom_db->single_table_records('eco_stays_amenities', '*');

        if ($data_list['status'] == true) {
            $page_data['data_list'] = $data_list['data'];
        }

        $this->template->view('eco_stays/eco_stays_amenities', $page_data);
    }
   public function delete_eco_stays_amenities(int $origin = 0): void
    {$page_data=[];
        $origin = intval($origin);
        if ($origin > 0) {
            // $page_data['origin'] = $origin;
            $data = $this->custom_db->single_table_records('eco_stays_amenities', '*', array('origin' => $origin));
            if ($data['status'] == true) {
                // DELETE Image FILES
                $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'eco_stays_amenities/' . $data['data'][0]['image']; // GETTING FILE PATH
                if (file_exists($_image)) {
                    unlink($_image);
                }
                $this->custom_db->delete_record('eco_stays_amenities', array('origin' => $origin));
                set_update_message('UL00103');
            } else {
                set_error_message('UL00100');
            }
        } else {
            set_error_message('UL00100');
        }
        redirect('eco_stays/eco_stays_amenities');
        exit();
    }
     public function eco_stays_room_amenities(int $origin = 0): void
    {$config=[];
        $page_data = array();
        $page_data['origin'] = 0;

        $post_params = $this->input->post();

        if (empty($post_params) == false) {
            if (isset($post_params['status']) == false) {
                $post_params['status'] = true;
            }
            if (isset($post_params['origin'])) {
                //update
                $origin = intval($post_params['origin']);
                $data = $this->custom_db->single_table_records('eco_stays_room_amenities', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    //now update
                    $image_data = array();
                    if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
                        $config['upload_path'] = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'eco_stays_room_amenities/';
                        $temp_file_name = $_FILES['image']['name'];
                        $config['allowed_types'] = '*';
                        $config['file_name'] = 'eco_stays_room_amenities_' . $temp_file_name;
                        $config['max_size'] = '100000000';
                        $config['max_width'] = '';
                        $config['max_height'] = '';
                        $config['remove_spaces'] = false;

                        // DELETE OLD FILES
                        if (empty($data['data'][0]['image']) == false) {
                            $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'eco_stays_room_amenities/' . $data['data'][0]['image']; // GETTING FILE PATH
                            if (file_exists($_image)) {
                                unlink($_image);
                            }
                        }
                        // UPLOAD IMAGE
                        $this->load->library('upload', $config);
                        $this->upload->initialize($config);
                        if (!$this->upload->do_upload('image')) {
                            set_error_message('UL00102');
                        } else {
                            $image_data = $this->upload->data();
                        }
                    }
                    $update_data = array(
                        'name' => $post_params['name'],

                        'status' => $post_params['status']
                    );
                    if (empty($image_data) == false) {
                        $update_data['image'] = $image_data['file_name'];
                    }
                    $this->custom_db->update_record('eco_stays_room_amenities', $update_data, array('origin' => $origin));
                    set_update_message();

                } else {
                    //invalid request
                    set_error_message('UL00100');
                }
            } else {

                //print_r($post_params);die;
                //insert
                if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
                    $config['upload_path'] = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'eco_stays_room_amenities/';
                    $temp_file_name = $_FILES['image']['name'];
                    $config['allowed_types'] = '*';
                    $config['file_name'] = 'eco_stays_room_amenities_' . $temp_file_name;
                    $config['max_size'] = '1000000';
                    $config['max_width'] = '';
                    $config['max_height'] = '';
                    $config['remove_spaces'] = false;

                    // UPLOAD IMAGE
                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('image')) {
                        set_error_message('UL00102');
                    } else {
                        $image_data = $this->upload->data();
                        $insert_data = array(
                            'name' => $post_params['name'],
                            'image' => $image_data['file_name'],
                            'status' => $post_params['status'],
                            'created_by_id' => $this->entity_user_id
                        );


                        $this->custom_db->insert_record('eco_stays_room_amenities', $insert_data);
                        set_insert_message();
                    }
                } elseif ($_FILES['image']['name'] == '') {
                    $insert_data = array(
                        'name' => $post_params['name'],
                        'status' => $post_params['status'],
                        'created_by_id' => $this->entity_user_id
                    );


                    $this->custom_db->insert_record('eco_stays_room_amenities', $insert_data);
                    set_insert_message();
                    //echo"hi"; die;

                } else {
                    // show msg image is important
                    set_error_message('UL00101');
                }
            }
            redirect('eco_stays/eco_stays_room_amenities');
            exit();
        } else {
            $origin = intval($origin);
            if ($origin > 0) {
                $page_data['origin'] = $origin;
                $data = $this->custom_db->single_table_records('eco_stays_room_amenities', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    $page_data['form_data'] = $data['data'][0];
                } else {
                    redirect('eco_stays/types');
                    exit();
                }
            }
        }

        $data_list = $this->custom_db->single_table_records('eco_stays_room_amenities', '*');

        if ($data_list['status'] == true) {
            $page_data['data_list'] = $data_list['data'];
        }

        $this->template->view('eco_stays/eco_stays_room_amenities', $page_data);
    }
   public function delete_eco_stays_room_amenities(int $origin = 0): void
    {$page_data=[];
        $origin = intval($origin);
        if ($origin > 0) {
            // $page_data['origin'] = $origin;
            $data = $this->custom_db->single_table_records('eco_stays_room_amenities', '*', array('origin' => $origin));
            if ($data['status'] == true) {
                // DELETE Image FILES
                $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'eco_stays_room_amenities/' . $data['data'][0]['image']; // GETTING FILE PATH
                if (file_exists($_image)) {
                    unlink($_image);
                }

                $this->custom_db->delete_record('eco_stays_room_amenities', array('origin' => $origin));
                set_update_message('UL00103');
            } else {
                set_error_message('UL00100');
            }
        } else {
            set_error_message('UL00100');
        }
        redirect('eco_stays/eco_stays_room_amenities');
        exit();
    }
  public function eco_stays_room_meal_types(int $origin = 0): void
    {
        $page_data = array();
        $page_data['origin'] = 0;

        $post_params = $this->input->post();

        if (empty($post_params) == false) {
            if (isset($post_params['origin'])) {
                //update
                $origin = intval($post_params['origin']);
                $data = $this->custom_db->single_table_records('eco_stays_room_meal_types', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    //now update
                    // $image_data = array();

                    $update_data = array(
                        'name' => $post_params['name'],

                        'status' => $post_params['status']
                    );

                    $this->custom_db->update_record('eco_stays_room_meal_types', $update_data, array('origin' => $origin));
                    set_update_message();

                } else {

                    set_error_message('UL00100');
                }
            } else {

                //insert
                $insert_data = array(
                    'name' => $post_params['name'],

                    'status' => $post_params['status'],
                    'created_by_id' => $this->entity_user_id
                );

                //print_r($insert_data)
                $this->custom_db->insert_record('eco_stays_room_meal_types', $insert_data);

                set_insert_message();


            }
            redirect('eco_stays/eco_stays_room_meal_types');
            exit();
        } else {
            $origin = intval($origin);
            if ($origin > 0) {
                $page_data['origin'] = $origin;
                $data = $this->custom_db->single_table_records('eco_stays_room_meal_types', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    $page_data['form_data'] = $data['data'][0];
                } else {


                    redirect('eco_stays/eco_stays_room_meal_types');
                    exit();
                }
            }
        }

        $data_list = $this->custom_db->single_table_records('eco_stays_room_meal_types', '*');

        if ($data_list['status'] == true) {
            $page_data['data_list'] = $data_list['data'];
        }

        $this->template->view('eco_stays/eco_stays_room_meal_types', $page_data);
    }
   public function delete_eco_stays_room_meal_types(int $origin = 0): void
    {$page_data=[];
        $origin = intval($origin);
        if ($origin > 0) {
            // $page_data['origin'] = $origin;
            $data = $this->custom_db->single_table_records('eco_stays_room_meal_types', '*', array('origin' => $origin));
            if ($data['status'] == true) {
                // DELETE Image FILES

                $this->custom_db->delete_record('eco_stays_room_meal_types', array('origin' => $origin));
                set_update_message('UL00103');
            } else {
                set_error_message('UL00100');
            }
        } else {
            set_error_message('UL00100');
        }
        redirect('eco_stays/eco_stays_room_meal_types');
        exit();
    }

   public function eco_stays_review_criteria(int $origin = 0): void
    {$config=[];
        $page_data = array();
        $page_data['origin'] = 0;

        $post_params = $this->input->post();

        if (empty($post_params) == false) {
            if (isset($post_params['origin'])) {
                //update
                $origin = intval($post_params['origin']);
                $data = $this->custom_db->single_table_records('eco_stays_review_criteria', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    //now update
                    $image_data = array();
                    if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
                        $config['upload_path'] = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'eco_stays_review_criteria/';
                        $temp_file_name = $_FILES['image']['name'];
                        $config['allowed_types'] = '*';
                        $config['file_name'] = 'eco_stays_amenities_' . $temp_file_name;
                        $config['max_size'] = '100000000';
                        $config['max_width'] = '';
                        $config['max_height'] = '';
                        $config['remove_spaces'] = false;

                        // DELETE OLD FILES
                        if (empty($data['data'][0]['image']) == false) {
                            $_image = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'eco_stays_review_criteria/' . $data['data'][0]['image']; // GETTING FILE PATH
                            if (file_exists($_image)) {
                                unlink($_image);
                            }
                        }
                        // UPLOAD IMAGE
                        $this->load->library('upload', $config);
                        $this->upload->initialize($config);
                        if (!$this->upload->do_upload('image')) {
                            //set_error_message('UL00102');
                        } else {
                            $image_data = $this->upload->data();
                        }
                    }

                    $update_data = array(
                        'name' => $post_params['name'],
                        'status' => $post_params['status']
                    );

                    if (empty($image_data) == false) {
                        $update_data['image'] = $image_data['file_name'];
                    }

                    $this->custom_db->update_record('eco_stays_review_criteria', $update_data, array('origin' => $origin));
                    set_update_message();

                } else {

                    set_error_message('UL00100');
                }
            } else {

                $image_data = array();
                if (valid_array($_FILES) == true and $_FILES['image']['error'] == 0 and $_FILES['image']['size'] > 0) {
                    $config['upload_path'] = $this->template->domain_eco_stays_images_upload_dir_full_path() . 'eco_stays_review_criteria/';
                    $temp_file_name = $_FILES['image']['name'];
                    $config['allowed_types'] = '*';
                    $config['file_name'] = 'eco_stays_amenities_' . $temp_file_name;
                    $config['max_size'] = '100000000';
                    $config['max_width'] = '';
                    $config['max_height'] = '';
                    $config['remove_spaces'] = false;

                    // UPLOAD IMAGE
                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('image')) {
                        //set_error_message('UL00102');
                    } else {
                        $image_data = $this->upload->data();
                    }
                }

                $insert_data = array(
                    'name' => $post_params['name'],
                    'status' => $post_params['status'],
                    'created_by_id' => $this->entity_user_id
                );

                if (empty($image_data) == false) {
                    $insert_data['image'] = $image_data['file_name'];
                }

                $this->custom_db->insert_record('eco_stays_review_criteria', $insert_data);

                set_insert_message();

            }
            redirect('eco_stays/eco_stays_review_criteria');
            exit();
        } else {
            $origin = intval($origin);
            if ($origin > 0) {
                $page_data['origin'] = $origin;
                $data = $this->custom_db->single_table_records('eco_stays_review_criteria', '*', array('origin' => $origin));
                if ($data['status'] == true) {
                    $page_data['form_data'] = $data['data'][0];
                } else {


                    redirect('eco_stays/eco_stays_review_criteria');
                    exit();
                }
            }
        }

        $data_list = $this->custom_db->single_table_records('eco_stays_review_criteria', '*');

        if ($data_list['status'] == true) {
            $page_data['data_list'] = $data_list['data'];
        }

        $this->template->view('eco_stays/eco_stays_review_criteria', $page_data);
    }
     public function delete_eco_stays_review_criteria(int $origin = 0): void
    {$page_data=[];
        $origin = intval($origin);
        if ($origin > 0) {
            // $page_data['origin'] = $origin;
            $data = $this->custom_db->single_table_records('eco_stays_review_criteria', '*', array('origin' => $origin));
            if ($data['status'] == true) {
                // DELETE Image FILES

                $this->custom_db->delete_record('eco_stays_review_criteria', array('origin' => $origin));
                set_update_message('UL00103');
            } else {
                set_error_message('UL00100');
            }
        } else {
            set_error_message('UL00100');
        }
        redirect('eco_stays/eco_stays_review_criteria');
        exit();
    }
}