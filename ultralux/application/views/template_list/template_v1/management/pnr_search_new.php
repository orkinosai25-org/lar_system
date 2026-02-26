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
               PNR Search <i class="far fa-angle-down"></i>
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
            <div class="tab-content active" id="pnr_search">
				
               <div class="col-md-9 rprt_rgt mt-15">
                  <div class="panel_bdy nopad">
                    <h5>Transaction / PNR Search</h5>
		<!-- PANEL WRAP START -->
<!-- 		<div class="panel-heading">
			<div class="panel-title">
				<ul id="myTab" role="tablist" class="nav nav-tabs  ">
				</ul>
			</div>
		</div> -->
		<!-- PANEL HEAD START -->
		<div class="panel-body nopad">
			<!-- PANEL BODY START -->
			<div class="tab-content active">
				<div role="" class="" id="fromList">
					<div class="col-md-12 nopad">
						<div class="panel nobrdr fltr">
							<form autocomplete="off" name="pnr_search" id="pnr_search"
								action="" method="GET" class="activeForm oneway_frm" style="">
								<div class="sml_row">
									<div class="col-sm-3 date-wrapper">
										<div class="form-group">
											<label for="bus-date-1">PNR Number</label>
											<div class="input-group">
												<input type="text"
													class="auto-focus hand-cursor form-control b-r-0" id=""
													placeholder="PNR Number" value="" name="filter_report_data" required>
											</div>
										</div>
									</div>
									<div class="col-sm-3 date-wrapper">
										<div class="form-group">
											<label for="bus-date-1">Module</label>
											<div class="input-group pnr_mdl">
											<?php if(is_active_airline_module()){?>
												<input type="radio" name="module"
													value="<?php echo PROVAB_FLIGHT_BOOKING_SOURCE ?>" checked> <span>Flight</span>
												<?php } if (is_active_bus_module()){?>
												<input type="radio" name="module"
													value="<?php echo PROVAB_BUS_BOOKING_SOURCE ?>"> <span>Bus</span>
												<?php } ?> 
												<?php if(is_active_transferv1_module()){ ?>
												<input type="radio" name="module"
													value="<?php echo PROVAB_TRANSFERV1_BOOKING_SOURCE ?>"><span>Transfers</span>
												<?php }?>
												<?php if(is_active_sightseeing_module()){ ?>
												<input type="radio" name="module"
													value="<?php echo PROVAB_SIGHTSEEN_BOOKING_SOURCE ?>"><span>Activities</span>
												<?php }?>


											</div>
										</div>
									</div>
									<div class="col-sm-3">
			                          <div class="form-group">
			                            <label for="sel1">&nbsp;</label>
										<button type="submit" name="" id="form-submit"
											class="btn btn-lg btn-i b-r-0 bus_search_btn">Search</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<?php
			//Only Visible if module is Flight 
			if(@$module == 'Flight'){?>
			<div class="tab-content">
				<div id="tableList" class="">
					<table class="table table-condensed table-bordered">
					<tr><th colspan="10"><center>Flight Details</center></th></tr>
						<tr>
							<th>Sno</th>
							<th>Application <br>Reference</th>
							<th>Status</th>
							<th>Base Fare</th>
							<th>Markup</th>
							<th>Customer</th>
							<th>Booking</th>
							<th>Total Ticket(s)</th>
							<th>Booked On</th>
							<th>Action</th>
						</tr>
						<?php
				if (isset ( $table_data ) == true and valid_array ( $table_data )) {
					//debug($table_data);exit;
					foreach ($table_data as $k => $v){
						$current_record = 0;
						extract ( $v );
						$action = '';
						$cancellation_btn = '';
						$voucher_btn = '';
						$status_update_btn = '';
						if (strtotime ( $journey_start ) > time () and (status == BOOKING_PENDING || $status == BOOKING_VOUCHERED || $status == BOOKING_CONFIRMED || $status == BOOKING_HOLD)) {
							// $cancellation_btn = get_accomodation_cancellation($api_code, $v['reference']);
						}
						// Status Update Button
						if (in_array ( $status, array (
								'BOOKING_CONFIRMED' 
						) ) == false) {
							switch ($booking_source) {
								case PROVAB_FLIGHT_BOOKING_SOURCE :
									$status_update_btn = '<button class="btn btn-success btn-sm update-source-status" data-app-reference="' . $app_reference . '"><i class="fa fa-database"></i> Update Status</button>';
									break;
							}
						}
						$voucher_btn = flight_voucher ( $app_reference, $booking_source, $status );
						$action = $voucher_btn . $status_update_btn . $cancellation_btn;
						?>
						<tr>
							<td><?=($current_record+$k+1)?></td>
							<td><?=$app_reference;?></td>
							<td><span class="<?=booking_status_label($status) ?>"><?=$status?></span></td>
							<td><?php echo $currency.':'.($total_fare+$domain_markup)?></td>
							<td><?php echo $level_one_markup?></td>
							<td><?php echo $name.'<br>Email:<br>'.$email?><br><?php echo 'P:'.$phone_number?></td>
							<td><strong><?php echo $journey_from.'</strong><br> @'.app_friendly_datetime($journey_start).' <br><strong><i> to </i><br> '.$journey_to?></strong><br><?php echo ' @'.app_friendly_datetime($journey_end)?></td>
							<td><?php echo $total_passengers?></td>
							<td><?php echo app_friendly_absolute_date($created_datetime)?></td>
							<td><div class="" role="group"><?php echo $action; ?></div></td>
						</tr>
								<?php
					}
				} else {
					echo '<tr><td colspan="12">No Data Found</td></tr>';
				}
				?>
					</table>
				</div>
			</div>

<?php
function get_accomodation_cancellation($courseType, $refId) {
	return '<a href="' . base_url () . 'booking/accomodation_cancellation?courseType=' . $courseType . '&refId=' . $refId . '" class="btn btn-sm btn-danger "><i class="fa fa-exclamation-triangle"></i> Cancel</a>';
}
?>
			<?php } 
			// Only Visible if module is Bus			 
			if(@$module == 'Bus'){?>
			<div class="tab-content">
				<div id="tableList" class="">
					<table class="table table-condensed table-bordered ">
					<tr><th colspan="11"><center>Bus Details</center></th></tr>
						<tr>
							<th>Application <br> Reference</th>
							<th>Status</th>
							<th>PNR/Ticket</th>
							<th>Total Fare</th>
							<th>Customer</th>
							<th>Booking</th>
							<th>journey</th>
							<th>Operator</th>
							<th>Booked On</th>
							<th>Action</th>
						</tr>
						<?php
					if (isset ( $table_data ) == true and valid_array ( $table_data )) {
					foreach ( $table_data as $k => $v ) {
						// get cancel button only if check in date has not passed and api cancellation is active
						// ONLY AOT IS ACTIVE FOR CANCELLATION
						$api_code = '';
						$action = '';
						if ($v ['booking_source'] == PROVAB_BUS_BOOKING_SOURCE) {
							$api_code = PROVAB_BUS_BOOKING_SOURCE;
							if (strtotime ( $v ['departure_datetime'] ) > time () and ($v ['status'] == BOOKING_CONFIRMED || $v ['status'] == BOOKING_HOLD)) {
								// $action .= get_accomodation_cancellation($api_code, $v['reference']);
							}
						}
						if (empty ( $api_code ) == false) {
							$action .= bus_voucher ( $v ['app_reference'], $api_code, $v ['status'] );
						}
						$customer = explode ( DB_SAFE_SEPARATOR, $v ['name'] );
						?>
						<tr>
							<td><?php echo $v['app_reference'];?></td>
							<td><span
								class="<?php echo booking_status_label($v['status']) ?>"><?php echo $v['status']?></span></td>
							<td class=""><span><?php echo 'PNR:<br>'.$v['pnr']?></span><br>
							<span><?php echo 'Tick:<br>'.$v['ticket']?></span></td>
							<td><?php echo $v['currency'].':'.($v['total_fare']+$v['level_one_markup']+$v['domain_markup'])?></td>
							<td><?php echo $customer[0].'<br>Email:<br>'.$v['email']?><br><?php echo 'P:'.$v['phone_number']?><br><?php echo 'O:'.$v['alternate_number']?></td>
							<td><?php echo $v['departure_from']?><br>to<br><?php echo $v['arrival_to']?><br>(<?php echo $v['total_passengers']?> <?=(intval($v['total_passengers']) > 1 ? 'tickets' : 'ticket' )?>)</td>
							<td><?php echo app_friendly_datetime($v['departure_datetime'])?></td>
							<td><?php echo $v['operator']?></td>
							<td><?php echo app_friendly_absolute_date($v['created_datetime'])?></td>
							<td><div class="" role="group"><?php echo $action; ?></div></td>
						</tr>
								<?php
					}
				} else {
					echo '<tr><td colspan="12">No Data Found</td></tr>';
				}
				?>
					</table>
				</div>
			</div>
			<?php } 
			// Only Visible if module is Hotel
			if(@$module == 'Hotel') {?>
			<div class="tab-content">
				<div id="tableList" class="">
					<table class="table table-condensed table-bordered">
					`	<tr><th colspan="12"><center>Hotel Details</center></th></tr>
						<tr>
							<th>Application <br>Reference</th>
							<th>Status</th>
							<th>Confirmation/<br>Reference</th>
							<th>Total Fare</th>
							<th>Payment Mode</th>
							<th>Customer</th>
							<th>Booking</th>
							<th>Check-In</th>
							<th>Hotel</th>
							<th>Booked <br>On</th>
							<th>Action</th>
						</tr>
						<?php
				if (isset ( $table_data ) == true and valid_array ( $table_data )) {
					foreach ( $table_data as $k => $v ) {
						extract ( $v );
						// get cancel button only if check in date has not passed and api cancellation is active
						// ONLY AOT IS ACTIVE FOR CANCELLATION
						$action = '';
						if ($v ['booking_source'] == PROVAB_HOTEL_BOOKING_SOURCE) {
							if (strtotime ( $v ['hotel_check_in'] ) > time () and ($v ['status'] == BOOKING_PENDING || $v ['status'] == BOOKING_VOUCHERED || $v ['status'] == BOOKING_CONFIRMED || $v ['status'] == BOOKING_HOLD)) {
								// $action .= get_accomodation_cancellation($api_code, $v['reference']);
							}
						}
						$action .= hotel_voucher ( $v ['app_reference'], $booking_source, $v ['status'] );
						?>
						<tr>
							<td><?=$app_reference;?></td>
							<td><span class="<?=booking_status_label($status) ?>"><?=$status?></span></td>
							<td class=""><span><?php echo 'Conf:'.$confirmation_reference?></span><br>
							<span><?php echo 'Ref:'.$booking_reference?></span></td>
							<td><?php echo $currency.':'.($total_fare+$level_one_markup+$domain_markup)?></td>
							<td><?php echo $payment_name?></td>
							<td><?php echo $name.'<br>Email:<br>'.$email?><br><?php echo 'P:'.$phone_number?></td>
							<td><?php echo app_friendly_absolute_date($hotel_check_in)?> <br> to <br> <?php echo app_friendly_date($hotel_check_out)?></td>
							<td><?php echo $total_passengers?> Pax, <br><?php echo $v['total_rooms']?> <?=(intval($total_rooms) > 1 ? 'Rooms' : 'Room' )?></td>
							<td><?php echo $hotel_name?></td>
							<td><?php echo app_friendly_absolute_date($created_datetime)?></td>
							<td><div class="" role="group"><?php echo $action; ?></div></td>
						</tr>
								<?php
					}
				} else {
					echo '<tr><td colspan="12">No Data Found</td></tr>';
				}
				?>	
					</table>
				</div>
			</div>
			<?php
				function get_accomodation_cancellation($courseType, $refId) {
					return '<a href="' . base_url () . 'booking/accomodation_cancellation?courseType=' . $courseType . '&refId=' . $refId . '" class="col-md-12 btn btn-sm btn-danger "><i class="fa fa-exclamation-triangle"></i> Cancel</a>';
				}
				?>
			<?php } ?>
		</div>
	</div>
</div>

            </div>
			
           
         </div>
      </div>
  	  </div>
   </div>
</div>

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
		$.get(app_base_url+'flight/get_booking_details/'+app_ref, function(response) {
			
		});
	});
	});
</script>
