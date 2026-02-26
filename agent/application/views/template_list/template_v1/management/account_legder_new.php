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
<?php
if (is_array($search_params)) {
  extract($search_params);
}
$_datepicker = array(array('created_datetime_from', PAST_DATE), array('created_datetime_to', PAST_DATE));
$this->current_page->set_datepicker($_datepicker);
$this->current_page->auto_adjust_datepicker(array(array('created_datetime_from', 'created_datetime_to')));
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
               Account Ledger <i class="far fa-angle-down"></i>
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
            <div class="tab-content active" id="account_ledger">
                <form method="GET" autocomplete="off">
                  <div class="col-md-9 rprt_rgt nopadL">
                  <h5>Filters:</h5>
                  <div class="fltr">
                    <div class="sml_row">
                      <div class="col-md-3 col-xs-6">
                          <div class="form-group">
                            <label for="sel1">From Date</label>
                            <input type="text" readonly id="created_datetime_from" class="form-control" name="created_datetime_from" value="<?=@$created_datetime_from?>" placeholder="From Date">
                          </div>
                      </div>
                      <div class="col-md-3 col-xs-6">
                          <div class="form-group">
                            <label for="sel1">From Date</label>
                            <input type="text" readonly id="created_datetime_to" class="form-control disable-date-auto-update" name="created_datetime_to" value="<?=@$created_datetime_to?>" placeholder="To Date">
                          </div>
                      </div>
                      <div class="col-md-2 col-xs-6">
                          <div class="form-group">
                            <label for="sel1">&nbsp;</label>
                            <button type="submit" class="btn btn-primary aply">Apply</button> 
                            <button type="reset" class="btn btn-warning rest">Reset</button>
                          </div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>				
               <div class="col-md-12 rprt_rgt mt-15">
                  <div class="panel_bdy nopad">
                    <h3>Account Ledger</h3>
                    <div class="pull-right">
					<?php echo $this->pagination->create_links();?>
					<div><?php echo 'Total <strong>'.$total_records.'</strong> transaction found.'?></div>
				</div>
        <div class="clearfix"></div>
			<div class="table-responsive">
			<table id="accounts_led" class="table table-condensed table-bordered">
      <tbody>
				<tr>
					<th>Sl. No.</th>
					<th>Date</th>
					<th>Reference Number</th>
					<th>Description</th>
					<th>Debit</th>
					<th>Credit</th>
					<th>Opening Balance</th>
					<th>Closing Balance</th>
				</tr>	
				<?php
				$segment_3 = $GLOBALS['CI']->uri->segment(3);
				$current_record = (empty($segment_3) ? 1 : $segment_3+1);
				
					if(valid_array($table_data) == true){ 
						
						$i=0;
						foreach($table_data as $k => $v){ ?>
							<tr>
								<td><?=($re = $current_record++)?></td>
								<td><?=app_friendly_datetime($v['transaction_date'])?></td>
								<td><?=$v['reference_number']?></td>
								<td><strong><?=$v['description']?></strong>
									<br>
									<small><?=$v['transaction_details']?></small>
								</td>
								<td><?=(empty($v['debit_amount']) == false ? $v['debit_amount'] : '-')?></td>
								<td><?=(empty($v['credit_amount']) == false ? $v['credit_amount']: '-')?></td>
								<td><?=$v['opening_balance']?></td>
								<td><?=$v['closing_balance']?></td>
							</tr>
					<?php $i++;
						if(empty($segment_3 = $GLOBALS['CI']->uri->segment(3)))
						{if($re>=20){break;}}else{
						if($i>=20){break;}
						} } ?>
				<?php }	else{ ?>
					<tr><td colspan="9">No Transaction Found !!</td></tr>
				<?php }?>
				</tbody>
			</table>
			</div>
			<div class="">
				<?php echo $this->pagination->create_links();?> 
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

