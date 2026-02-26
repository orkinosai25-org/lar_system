<?php
$active_domain_modules = $GLOBALS ['CI']->active_domain_modules;
$master_module_list = $GLOBALS ['CI']->config->item ( 'master_module_list' );
if (empty ( $default_view )) {
	$default_view = $GLOBALS ['CI']->uri->segment ( 2 );
}
$today_search = date('Y-m-d');
$last_today_searchs = date('Y-m-d', strtotime('-7 day'));
$last_month = date('Y-m-d', strtotime('-30 day'));
$last_three_month = date('Y-m-d', strtotime('-90 day'));
?>
<link href="<?php echo $GLOBALS['CI']->template->template_css_dir('bootstrap-toastr/toastr.min.css');?>" rel="stylesheet" defer>
<script src="<?php echo $GLOBALS['CI']->template->template_js_dir('bootstrap-toastr/toastr.min.js'); ?>"></script>
<?=$GLOBALS['CI']->template->isolated_view('report/email_popup')?>
    <style>
        /* Vertical Pills Styling */
        .nav-pills > li > a {
            padding: 10px 20px;
            background-color: #f5f5f5;
            border-radius: 4px;
            margin-bottom: 5px;
            display: block;
        }
        .nav-pills > li.active > a {
            background-color: #337ab7;
            color: white !important;
        }
        .dropdown-menu {
            padding: 10px;
        }
        .nested-dropdown .dropdown-menu {
            left: 100%;
            top: 0;
            margin-left: 0;
        }
        .dropdown-toggle::after {
            display: none; /* Remove default caret */
        }

        /* Content Styling */
        .tab-content {
            display: none; /* Hide all by default */
        }
        .tab-content.active {
            display: block; /* Show only active content */
        }
        .content-wrapper {
            background: #fff !important;
        }
    </style>
<div class="bodyContent">
   <div class="table_outer_wrper">
      <!-- PANEL WRAP START -->
      <div class="panel_custom_heading">
         <!-- PANEL HEAD START -->
         <div class="org_row">
         <div class="col-md-3 rprt_lft">
            <h5>My Reports</h5>
            <div class="dropdown">
               <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown">
               Car <i class="far fa-angle-down"></i>
               </button>
               <ul class="dropdown-menu" aria-labelledby="dropdownMenu">
                  <!-- Nested Pills Inside Dropdown -->
                  <li class="nested-dropdown">
                     <a href="#">Booking Details <i class="far fa-angle-right"></i></a>
                     <ul class="dropdown-menu">
                        <li><a href="<?php echo base_url().'report/flight/';?>">Flight</a></li>
                        <li><a href="<?php echo base_url().'report/hotel/';?>">Hotel</a></li>
                        <li><a href="<?php echo base_url().'report/car/';?>">Car</a></li>
                     </ul>
                  </li>
                  <li><a href="<?php echo base_url().'management/pnr_search';?>">PNR Search</a></li>
                  <li><a href="<?php echo base_url().'report/flight?filter_booking_status=BOOKING_PENDING';?>" data-target="pending_ticket">Pending Ticket</a></li>
                  <li><a href="<?php echo base_url().'report/flight?daily_sales_report='.ACTIVE?>" data-target="daily_sales">Daily Sales Report</a></li>
                  <li><a href="<?php echo base_url().'management/account_ledger'?>" data-target="account_ledger">Account Ledger</a></li>
                  <li><a href="<?php echo base_url().'index.php/transaction/logs'?>" data-target="transaction_logs">Transaction Logs</a></li>
               </ul>
            </div>
         </div>
         <!-- Content Area -->
         <div id="selectedContent">
            <div class="tab-content active" id="car">
				 <form action="<?=base_url().'report/'.$default_view?>" method="get" autocomplete="off" role="form">
               <div class="col-md-9 rprt_rgt nopadL">
                  <h5>Filters:</h5>
                  <div class="fltr">
                    <div class="sml_row">
                      <div class="col-md-3 col-xs-6">
                          <div class="form-group">
                            <label for="sel1">Time period</label>
                          <div class="dropdown recommended">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" id="sort_text">
                              <?php echo $dropdown;?> <i class="far fa-angle-down"></i>
                            </button>
                            <div class="dropdown-menu">
                                <div class="form-group">
                                    <div class="form-check">
                                      <input class="form-check-input today filter" data-filtername="Today" type="radio" name="filter" id="exampleRadios1" checked value="today_booking_data">
                                      <label class="form-check-label" for="exampleRadios1">Today</label>
                                    </div>  
                                </div>                              
                                <div class="form-group">
                                    <div class="form-check">
                                      <input type="radio" id="exampleRadios2" data-filtername="Last 7 days" class="form-check-input sevendays filter" name="filter" value="prev_booking_data_7days">
                                        <label class="form-check-label" for="exampleRadios2">Last 7 days</label>
                                    </div> 
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input thismonth filter" data-filtername="This Month" type="radio" name="filter" id="exampleRadios3" value="prev_booking_data_month">
                    
                                        <label class="form-check-label" for="exampleRadios3">This Month</label>
                                    </div> 
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input this month filter" data-filtername="Last 3 months" type="radio" name="filter" id="exampleRadios4" value="prev_booking_data_3months">
                                        <label class="form-check-label" for="exampleRadios4">Last 3 months</label>
                                    </div> 
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input customdaterange filter" data-filtername="Custom Date Range" type="radio" name="filter" id="exampleRadios5" value="">
                                        <label class="form-check-label" for="exampleRadios5">Custom Date Range</label>
                                    </div>
                                    <div class="form-check cstm_dat">
                                      <div class="col-xs-6">
                                        <label>From</label>
                                        <input type="text" name="from_date" class="datepick form-control" placeholder="<?php echo $from_date; ?>">
                                      </div>
                                      <div class="col-xs-6">
                                        <label>To</label>
                                        <input type="text" name ="to_date" class="datepick form-control" placeholder="<?php echo $to_date; ?>">
                                      </div>
                                    </div>
                                </div>
                              </div>
                            </div>
                        </div>
                      </div>
                      <div class="col-md-3 col-xs-6">
                          <div class="form-group">
                            <label for="sel1">Status</label>
                            <select class="form-control" id="sel1" name="filter_booking_status">
                               <option value="">All</option>
                       	<?=generate_options(get_enum_list('report_filter_status'),(array)@$_GET['filter_booking_status']);?>
                            </select>
                          </div>
                      </div>
                      <div class="col-md-4 col-xs-6">
                          <div class="form-group">
                            <label for="email">By AppReference/PNR</label>
							  <input type="text" autocomplete="off" data-search_category="search_query" placeholder="AppReference/PNR" class="form-control auto_suggest_booking_id ui-autocomplete-input" id="auto_suggest_booking_id" name="filter_report_data" value="<?=@$_GET['filter_report_data']?>">
                           
                          </div>
                      </div>
                      <div class="col-md-2 col-xs-6">
                          <div class="form-group">
                            <label for="sel1">&nbsp;</label>
                            <div class="d-flex aply_btn">
                            <button type="submit" class="btn btn-default aply">Apply</button>
							              <a class="btn btn-warning rest" href="<?=base_url().'report/'.$default_view?>">Reset All</a>
                            </div>
							
                          </div>
                      </div>
                    </div>
                  </div>
                 
               </div>
				    </form>
               <div class="col-md-12 rprt_rgt mt-15">
                  <div class="panel_bdy nopad">
                    <h3>Booking Details - Car</h3>
                    <?php echo get_table($table_data, $total_rows);?>
                  </div>
               </div>
            </div>
			
           
         </div>
      </div>
  	  </div>
   </div>
</div>
<?php
function get_table(array $table_data, int $total_rows): string
{
    $pagination = '<div class="pull-right">' . $GLOBALS['CI']->pagination->create_links() . '<span class="">Total ' . $total_rows . ' Bookings</span></div>';
    $report_data = '<div id="tableList" class="nopad">';
    $report_data .= $pagination;

    $report_data .= '<table class="table table-condensed table-bordered">
        <tr>
            <th>Sno</th>
            <th>Application<br/>Reference</th>
            <th>Confirmation<br/>Number</th>
            <th>Customer<br/>Name</th>
            <th>PickupLocation</th>
            <th>DropLocation</th>
            <th>Fare</th>
            <th>Markup</th>
            <th>TotalFare</th>
            <th>Pickup DateTime/<br/>Drop DateTime</th>
            <th>BookedOn</th>
            <th>Status</th>
            <th>Action</th>
        </tr>';

    if (!isset($table_data['booking_details']) || !valid_array($table_data['booking_details'])) {
        $report_data .= '<tr><td colspan="13">No Data Found</td></tr>';
        $report_data .= '</table></div>';
        return $report_data;
    }

    $segment_3 = $GLOBALS['CI']->uri->segment(3);
    $current_record = empty($segment_3) ? 1 : (int)$segment_3;
    $booking_details = $table_data['booking_details'];
    $tdy_date = date('Y-m-d');

    foreach ($booking_details as $parent_v) {
        extract($parent_v);

        $pickup_datetime = $car_from_date . ' ' . $pickup_time;
        $drop_datetime = $car_to_date . ' ' . $drop_time;
        $diff = get_date_difference($tdy_date, $car_from_date);

        $action = '';
        $action .= car_voucher($app_reference, $booking_source, $status) . '<br/>';
        $action .= car_pdf($app_reference, $booking_source, $status) . '<br/>';
        $action .= car_voucher_email($app_reference, $booking_source, $status, $parent_v['email']) . '<br/>';

        if ($status === 'BOOKING_CONFIRMED' && $diff > 0) {
            $action .= cancel_car_booking($app_reference, $booking_source, $status);
        }

        $action .= get_cancellation_details_button($app_reference, $booking_source, $status);
        $action .= get_booking_pending_status($app_reference, $booking_source, $status);

        $report_data .= '<tr>
            <td>' . ($current_record++) . '</td>
            <td>' . $app_reference . '</td>
            <td>' . $booking_reference . '</td>
            <td>' . $customer_details[0]['title'] . ' ' . $customer_details[0]['first_name'] . ' ' . $customer_details[0]['last_name'] . '</td>
            <td>' . $car_pickup_lcation . '</td>
            <td>' . $car_drop_location . '</td>
            <td>' . ($fare + $admin_markup) . '</td>
            <td>' . $agent_markup . '</td>
            <td>' . $grand_total . '</td>
            <td>' . month_date_year_time($pickup_datetime) . '/<br/>' . month_date_year_time($drop_datetime) . '</td>
            <td>' . $voucher_date . '</td>
            <td><span class="' . booking_status_label($status) . '">' . $status . '</span></td>
            <td><div class="" role="group">' . $action . '</div></td>
        </tr>';
    }

    $report_data .= '</table></div>';
    return $report_data;
}

function car_voucher_email(string $app_reference, string $booking_source, string $status, string $recipient_email): string
{

	return '<a class="btn btn-sm btn-primary send_email_voucher" data-app-status="'.$status.'"   data-app-reference="'.$app_reference.'" data-booking-source="'.$booking_source.'"data-recipient_email="'.$recipient_email.'"><i class="far fa-envelope"></i> Email Voucher</a>';
}
function get_cancellation_details_button(string $app_reference, string $booking_source, string $status): string
{
	if($status == 'BOOKING_CANCELLED'){
		return '<a target="_blank" href="'.base_url().'car/cancellation_refund_details?app_reference='.$app_reference.'&booking_source='.$booking_source.'&status='.$status.'" class="col-md-12 btn btn-sm btn-info "><i class="far fa-info"></i> Cancellation Details</a>';
	}
}
function get_booking_pending_status(string $app_reference, string $booking_source, string $status): string
{
	if($status == 'BOOKING_HOLD'){
		return '<a class="get_car_hb_status col-md-12 btn btn-sm btn-info flight_u" id="pending_status_'.$app_reference.'" data-booking-source="'.$booking_source.'"
			data-app-reference="'.$app_reference.'" data-status="'.$status.'"><i class="far fa-info"></i>Update Supplier Info</a>';
	}
}
?>
<script>
$(document).ready(function () {
	 //$('#dropdownMenu').html(selectedText + ' <i class="far fa-angle-down"></i>');
    // Handle main dropdown toggle
    $('#dropdownMenu').on('click', function (e) {
        e.preventDefault();
        $(this).next('.dropdown-menu').toggle();
    });

    // Handle item selection
    /*$('.dropdown-menu a').on('click', function (e) {
        e.preventDefault();
        var selectedText = $(this).text();
        var target = $(this).data('target');

        // Update the button text
        $('#dropdownMenu').html(selectedText + ' <i class="far fa-angle-down"></i>');

        // Add active class to selected tab, remove from others
        $('.dropdown-menu a').removeClass('active');
        $(this).addClass('active');

        // Show content for selected tab
        $('.tab-content').removeClass('active');
        $(`#${target}`).addClass('active');

        // Close dropdowns only if it's not a nested link
        if (!$(this).closest('.nested-dropdown').length) {
            $('.dropdown-menu').hide();
        }
    });*/

    // Handle nested dropdown toggle
    $('.nested-dropdown > a').on('click', function (e) {
        e.preventDefault();
        $(this).next('.dropdown-menu').toggle();
    });

    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').hide();
        }
    });

    // Prevent dropdown closing when clicking inside the nested menu
    $('.nested-dropdown .dropdown-menu').on('click', function (e) {
        e.stopPropagation(); // Prevent click from closing the parent dropdown
        $('.dropdown-menu').hide();
    });
    $( ".datepick" ).datepicker();
	$(document).on("click",".filter",function() {
		var selectedText = $(this).data('filtername');
		if(selectedText!="Today"){
			$("input[name=today_booking_data]").prop('checked',false);
		}
		$('#sort_text').html(selectedText +'<i class="far fa-angle-down"></i>');
	});
});

</script>
<script>

$(document).ready(function() {

	 //send the email voucher
		$('.send_email_voucher').on('click', function(e) {
			$("#mail_voucher_modal").modal('show');
			$('#mail_voucher_error_message').empty();
	        email = $(this).data('recipient_email');
			$("#voucher_recipient_email").val(email);
	        app_reference = $(this).data('app-reference');
	        book_reference = $(this).data('booking-source');
	        app_status = $(this).data('app-status');
		  $("#send_mail_btn").off('click').on('click',function(e){
			  email = $("#voucher_recipient_email").val();
			  var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
			 if(email != ''){ 
				  if(!emailReg.test(email)){
					  $('#mail_voucher_error_message').empty().text('Please Enter Correct Email Id');
	                     return false;    
					      }
				 
						var _opp_url = app_base_url+'index.php/voucher/car/';
						_opp_url = _opp_url+app_reference+'/'+book_reference+'/'+app_status+'/email_voucher/'+email;
						toastr.info('Please Wait!!!');
						$.get(_opp_url, function() {
							
							toastr.info('Email sent  Successfully!!!');
							$("#mail_voucher_modal").modal('hide');
						});
			    }else{
			 	  	 $('#mail_voucher_error_message').empty().text('Please Enter Email ID');
				 }
			 
		  });

	});
	$(".get_car_hb_status").on("click",function(e){
  		
		 	app_reference = $(this).data('app-reference');
        book_reference = $(this).data('booking-source');
        app_status = $(this).data('status');
        var _opp_url = app_base_url+'index.php/car/get_pending_booking_status/';
		_opp_url = _opp_url+app_reference+'/'+book_reference+'/'+app_status;
		toastr.info('Please Wait!!!');
		$.get(_opp_url, function(res) {
			if(res==1){
				toastr.info('Status Updated Successfully!!!');	
				location.reload(); 
			}else{
				toastr.info('Status not updated');
			}
			
			$("#mail_voucher_modal").modal('hide');
		});
  });
	});
</script>
