<?php
$active_domain_modules = $GLOBALS ['CI']->active_domain_modules;
$master_module_list = $GLOBALS ['CI']->config->item ( 'master_module_list' );
if (empty ( $default_view )) {
	$default_view = $GLOBALS ['CI']->uri->segment ( 2 );
}
//echo $dropdown;exit;
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
               Flight <i class="far fa-angle-down"></i>
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
                  <li><a href="<?php echo base_url().'index.php/report/flight?filter_booking_status=BOOKING_PENDING';?>" data-target="pending_ticket">Pending Ticket</a></li>
                  <li><a href="<?php echo base_url().'index.php/report/flight?daily_sales_report='.ACTIVE?>" data-target="daily_sales">Daily Sales Report</a></li>
                  <li><a href="<?php echo base_url().'index.php/management/account_ledger'?>" data-target="account_ledger">Account Ledger</a></li>
                  <li><a href="<?php echo base_url().'index.php/transaction/logs'?>" data-target="transaction_logs">Transaction Logs</a></li>
               </ul>
            </div>
         </div>
         <!-- Content Area -->
         <div id="selectedContent">
            <div class="tab-content active" id="flight">
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
                                      <input class="form-check-input today filter" data-filtername="Today" type="radio" name="filter" id="exampleRadios1" <?php if($dropdown=='Today'){ echo 'checked'; } ?> value="today_booking_data">
                                      <label class="form-check-label" for="exampleRadios1">Today</label>
                                    </div>  
                                </div>                              
                                <div class="form-group">
                                    <div class="form-check">
                                      <input type="radio" id="exampleRadios2" data-filtername="Last 7 Days" class="form-check-input sevendays filter" name="filter" <?php if($dropdown=='Last 7 Days'){ echo 'checked'; } ?> value="prev_booking_data_7days">
                                        <label class="form-check-label" for="exampleRadios2">Last 7 Days</label>
                                    </div> 
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input thismonth filter" data-filtername="This Month" type="radio" name="filter" id="exampleRadios3" <?php if($dropdown=='This Month'){ echo 'checked'; } ?> value="prev_booking_data_month">
                    
                                        <label class="form-check-label" for="exampleRadios3">This Month</label>
                                    </div> 
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input this month filter" data-filtername="Last 3 Months" type="radio" name="filter" id="exampleRadios4"  <?php if($dropdown=='Last 3 Months'){ echo 'checked'; } ?> value="prev_booking_data_3months">
                                        <label class="form-check-label" for="exampleRadios4">Last 3 Months</label>
                                    </div> 
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input customdaterange filter" data-filtername="Custom Date Range" type="radio" name="filter" id="exampleRadios5" value="custom_date_range" <?php if($dropdown=='Custom Date Range'){ echo 'checked'; } ?>>
                                        <label class="form-check-label" for="exampleRadios5">Custom Date Range</label>
                                    </div>
                                    <div class="form-check cstm_dat">
                                      <div class="col-xs-6">
                                        <label>From</label>
                                        <input type="text" id="from_date_airline" name="from_date" class="form-control" placeholder="From Date" value="<?php echo $from_date; ?>">
                                      </div>
                                      <div class="col-xs-6">
                                        <label>To</label>
                                        <input type="text" id="to_date_airline" name ="to_date" class="form-control" placeholder="To Date" value="<?php echo $to_date; ?>">
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
                            <label for="email">By App. Reference/PNR</label>
							  <input type="text" autocomplete="off" data-search_category="search_query" placeholder="App Reference/PNR" class="form-control auto_suggest_booking_id ui-autocomplete-input" id="auto_suggest_booking_id" name="filter_report_data" value="<?=@$_GET['filter_report_data']?>">
                           
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
                    <h3>Booking Details - Flight</h3>
                    <div id="tableList" class="table-responsive nopad">
                       <div class="pull-right tot_bk">
                          <?php echo $this->pagination->create_links();?> <span class="">Total <?php echo $total_rows ?> Bookings</span>
                       </div>
                       <table class="table table-condensed table-bordered">
                          <tr>
                             <th>Sno</th>
                             <th>Application Reference</th>
                             <th>PNR</th>
                             <th>Customer<br/>Name</th>
                             <th>From</th>
                             <th>To</th>
                             <th>Trip Type</th>

                             <th> Net Fare</th>
                             <th> Commission</th>
                             <th>Markup</th>

                             <th>Agent Net Fare</th>
                             <th>Agent Commission</th>
                             <th>Agent <br/>Markup</th>

                             <th>TDS</th>
                             <th>GST</th>
                             <th>TotalFare</th>
                             <th>TravelDate</th>
                             <th>BookedOn</th>
                             <th>Status</th>
                             <th>Action</th>
                          </tr>
                          <?php
                             if(valid_array($table_data['booking_details']) == true) {
                                  		$booking_details = $table_data['booking_details'];
								
                             	$segment_3 = $GLOBALS['CI']->uri->segment(3);
                             	$current_record = (empty($segment_3) ? 1 : $segment_3);
                                   	foreach($booking_details as $parent_k => $parent_v) {
                                   		extract($parent_v);
                             		$action = '';
                             		$cancellation_btn = '';
                             		$voucher_btn = '';
                             		$status_update_btn = '';
                             		$booked_by = '';
                             		
                             		//Status Update Button
                             		if (in_array($status, array('BOOKING_CONFIRMED')) == false) {
                             			switch ($booking_source) {
                             				case PROVAB_FLIGHT_BOOKING_SOURCE :
                             					$status_update_btn = '<button class="btn btn-success btn-sm update-source-status" data-app-reference="'.$app_reference.'"><i class="far fa-database"></i> Update Status</button>';
                             					break;
                             			}
                             		}
                             		$voucher_btn = flight_voucher($app_reference, $booking_source, $status);
                             		$pdf_btn = flight_pdf($app_reference, $booking_source, $status);
                             		$invoice = flight_invoice($app_reference, $booking_source, $status);
                             		$cancel_btn = flight_cancel($app_reference, $booking_source, $status);
                             		$email_btn = flight_voucher_email($app_reference, $booking_source,$status,$parent_v['email']);
                             		$jrny_date = date('Y-m-d', strtotime($journey_start));
                             		$tdy_date = date ( 'Y-m-d' );
                             		$diff = get_date_difference($tdy_date,$jrny_date);
                             		$action .= $voucher_btn;
                             		$action .= '<br />'.$pdf_btn;
                             		$action .=  '<br />'.$email_btn;
                             		if($diff > 0){
                             		$action .= $cancel_btn;
                             		}
                             		$action .= get_cancellation_details_button($parent_v['app_reference'], $parent_v['booking_source'], $parent_v['status'], $parent_v['booking_transaction_details']);
                             	?>
                          <tr>
                             <td><?=($current_record++)?></td>
                             <td><?php echo $app_reference;?></td>
                             <td><?=@$pnr?></td>
                             <td><?=$booking_transaction_details[0]['booking_customer_details'][0]['title'].' '.$booking_transaction_details[0]['booking_customer_details'][0]['first_name'].' '.$booking_transaction_details[0]['booking_customer_details'][0]['last_name']?></td>
                             <td><?=@$from_loc?></td>
                             <td><?=@$to_loc?></td>
                             <td><?=@$trip_type?></td>
                             <td><?php echo $agent_buying_price?></td>
                             <td><?php echo $agent_commission?></td>
                             <td><?php echo $agent_markup?></td>
                             <td><?php echo ($agent_tds)?></td>
                             <td><?php echo ($gst)?></td>
                             <td><?php echo $grand_total?></td>
                             <td><?php echo app_friendly_absolute_date($journey_start)?></td>
                             <td><?php echo $booked_date?></td>
                             <td><span class="<?php echo booking_status_label($status) ?>"><?php echo $status?></span></td>
                             <td>
                                <div class="" role="group"><?php echo $action; ?></div>
                             </td>
                          </tr>
                          <?php
                             }
                             } else {
                             echo '<tr><td colspan="17" style="text-align:center;">No Data Found</td></tr>';
                             }
                             ?>
                       </table>
                    </div>
                  </div>
               </div>
            </div>
			
            <div class="tab-content" id="pnr_search"><h2>PNR Search are Coming Soon</h2></div>
            <div class="tab-content" id="pending_ticket"><h2>Pending Ticket is Coming Soon</h2></div>
            <div class="tab-content" id="daily_sales"><h2>Daily Sales Report is Coming Soon</h2></div>
            <div class="tab-content" id="account_ledger"><h2>Account Ledger is Coming Soon</h2></div>
            <div class="tab-content" id="transaction_logs"><h2>Transaction Logs are Coming Soon</h2></div>
         </div>
      </div>
  	  </div>
   </div>
</div>
<?php

function get_accomodation_cancellation($courseType, $refId)
{
	return '<a href="'.base_url().'index.php/booking/accomodation_cancellation?courseType='.$courseType.'&refId='.$refId.'" class="btn btn-sm btn-danger "><i class="far fa-exclamation-triangle"></i> Cancel</a>';
}
function flight_voucher_email(string $app_reference, string $booking_source, string $status, string $recipient_email): string
{
    return '<a class="btn btn-sm btn-primary send_email_voucher" data-app-status="' . htmlspecialchars($status) . '" data-app-reference="' . htmlspecialchars($app_reference) . '" data-booking-source="' . htmlspecialchars($booking_source) . '" data-recipient_email="' . htmlspecialchars($recipient_email) . '"><i class="far fa-envelope"></i> Email Voucher</a>';
}

function get_cancellation_details_button(string $app_reference, string $booking_source, string $booking_status, array $customer_details): ?string
{
    $status = 'BOOKING_CONFIRMED';

    if ($booking_status === 'BOOKING_CANCELLED') {
        $status = 'BOOKING_CANCELLED';
    }

    if ($status === 'BOOKING_CONFIRMED') {
        foreach ($customer_details as $tv) {
            foreach ($tv['booking_customer_details'] as $pv) {
                if (($pv['status'] ?? '') === 'BOOKING_CANCELLED') {
                    $status = 'BOOKING_CANCELLED';
                    break 2; // exits both foreach loops
                }
            }
        }
    }

    if ($status === 'BOOKING_CANCELLED') {
        $url = base_url() . 'index.php/flight/ticket_cancellation_details?app_reference=' . rawurlencode($app_reference)
             . '&booking_source=' . rawurlencode($booking_source)
             . '&status=' . rawurlencode($status);

        return '<a target="_blank" href="' . $url . '" class="btn btn-sm btn-info"><i class="far fa-info"></i> Cancellation Details</a>';
    }

    return null;
}


	
$datepicker = array(array('from_date_airline', PAST_DATE), array('to_date_airline', PAST_DATE));
$GLOBALS['CI']->current_page->set_datepicker($datepicker);
$this->current_page->auto_adjust_datepicker(array(array('from_date_airline', 'to_date_airline')));
	
?>
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
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
	//update-source-status update status of the booking from api
	$(document).on('click', '.update-source-status', function(e) {
		e.preventDefault();
		$(this).attr('disabled', 'disabled');//disable button
		var app_ref = $(this).data('app-reference');
		$.get(app_base_url+'index.php/flight/get_booking_details/'+app_ref, function(response) {
			
		});
	});

    /*
    *Sagar Wakchaure
    *send email voucher
    */
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
			      
						var _opp_url = app_base_url+'index.php/voucher/flight/';
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
	
	
});
</script>
