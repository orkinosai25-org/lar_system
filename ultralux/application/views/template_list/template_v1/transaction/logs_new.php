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
               Transaction Logs <i class="far fa-angle-down"></i>
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
                            <label for="sel1">Time period</label>
                          <div class="dropdown recommended">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" id="sort_text">
                              Today <i class="far fa-angle-down"></i>
                            </button>
                               <div class="dropdown-menu">
                                <div class="form-group">
                                    <div class="form-check">
                                      <input class="form-check-input today filter" data-filtername="Today" type="radio" name="filter" id="exampleRadios1" value="today_booking_data">
                                      <label class="form-check-label" for="exampleRadios1">Today</label>
                                    </div>  
                                </div>                              
                                <div class="form-group">
                                    <div class="form-check">
                                      <input type="radio" id="exampleRadios2" data-filtername="Last 7 Days" class="form-check-input sevendays filter" name="filter" value="prev_booking_data_7days">
                                        <label class="form-check-label" for="exampleRadios2">Last 7 Days</label>
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
                                        <input class="form-check-input this month filter" data-filtername="Last 3 Months" type="radio" name="filter" id="exampleRadios4" value="prev_booking_data_3months">
                                        <label class="form-check-label" for="exampleRadios4">Last 3 Months</label>
                                    </div> 
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input customdaterange filter" data-filtername="Custom Date Range" type="radio" name="filter" id="exampleRadios5" value="custom_date_range">
                                        <label class="form-check-label" for="exampleRadios5">Custom Date Range</label>
                                    </div>
                                    <div class="form-check cstm_dat">
                                      <div class="col-xs-6">
                                        <label>From</label>
                                        <input type="text" id="from_date_airline" name="from_date" class="form-control" placeholder="From Date" value="">
                                      </div>
                                      <div class="col-xs-6">
                                        <label>To</label>
                                        <input type="text" id="to_date_airline" name ="to_date" class="form-control" placeholder="To Date" value="">
                                      </div>
                                    </div>
                                </div>
                              </div>
                            </div>
                        </div>
                      </div>
                      <div class="col-md-3 col-xs-6">
                          <div class="form-group">
                            <label for="sel1">Transaction Type</label>
                            <select class="form-control" name="transaction_type">
                              <option value="">All</option>
                              <?=generate_options(get_enum_list('transaction_type'), array(@$transaction_type))?>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-3 col-xs-6">
                          <div class="form-group">
                            <label for="sel1">Reference Number</label>
                            <input type="text" class="form-control" name="app_reference" value="<?=@$app_reference?>" placeholder="Reference Number">
                          </div>
                      </div>
                      <div class="col-xs-4 hide">
                        <label>
                        From Date
                        </label>
                        <input type="text" readonly id="created_datetime_from" class="form-control" name="created_datetime_from" value="<?=@$created_datetime_from?>" placeholder="Request Date">
                      </div>
                      <div class="col-xs-4 hide">
                        <label>
                        To Date
                        </label>
                        <input type="text" readonly id="created_datetime_to" class="form-control disable-date-auto-update" name="created_datetime_to" value="<?=@$created_datetime_to?>" placeholder="Request Date">
                      </div>
                      <div class="col-md-3 col-xs-6">
                          <div class="form-group">
                            <label for="sel1">&nbsp;</label>
                          <button type="submit" class="btn btn-primary aply">Search</button> 
                          <button type="reset"  id ="tr_log"class="btn btn-warning rest">Reset</button> 
                          <!-- <a href="<?php echo base_url().'index.php/transaction/logs'?>" id="clear-filter" class="btn btn-primary">ClearFilter</a> -->
                        </div>
                      </div>
                    </div>
                </div>
              </form>
				
               <div class="col-md-12 rprt_rgt mt-15">
                  <div class="panel_bdy nopad">
                    <h3>Transaction Logs</h3>
                   <div class="pull-right">
				<?php echo $this->pagination->create_links();?> <span class="">Total <?php echo $total_rows ?> Records</span>
			</div>
      <div class="clearfix"></div>
			<div class="table-responsive">
			<table class="table table-condensed table-bordered">
				<tr>
					<th>Sl. No.</th>
					<th>Agent</th>
					<th>Transaction Date</th>
					<th>Reference Number</th>
					<th>Transaction Type</th>
					<th>Amount</th>
					<th>Description</th>
				</tr>
			<?php
			if (valid_array($table_data)) {
//debug($table_data);exit;
				foreach ($table_data as $k => $v) {
					if ($v['transaction_owner_id'] == 0) {
						$user_info = 'Guest';
					} else {
						$user_info = $v['username'];
					}
				?>
					<tr>
						<td><?=($k+1)?></td>
						<td><?=$v['agent_name']?></td>
						<td><?=app_friendly_date($v['created_datetime'])?></td>
						<td><?=$v['app_reference']?></td>
						<td><?=ucfirst($v['transaction_type'])?></td>
						<th><?=abs($v['fare']+$v['profit']).'-'.$v['currency']?></th>					
						<td><?=$v['remarks']?></td>	
					</tr>
				<?php
				}
			} else {
				echo '<tr><td>No Data Found</td></tr>';
			}
			?>
			</table>
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
    $('#tr_log').click(function() {
      
        var url = "<?php echo base_url().'index.php/report/flight'; ?>"; // PHP dynamically generates the URL
        window.location.href = url; // Redirect to the URL
    });
});
</script>