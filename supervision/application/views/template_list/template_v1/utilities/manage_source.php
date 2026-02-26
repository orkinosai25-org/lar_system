<?php
$active_domain_modules = $GLOBALS['CI']->active_domain_modules;
$master_module_list = $GLOBALS['CI']->config->item('master_module_list');
if (empty($default_view)) {
	$default_view = $GLOBALS['CI']->uri->segment(1);
}
function set_default_active_tab($module_name, &$default_active_tab)
{
	if (empty($default_active_tab) == true || $module_name == $default_active_tab) {
		if (empty($default_active_tab) == true) {
			$default_active_tab = $module_name; // Set default module as current active module
		}
		return 'active';
	}
}
?>
<div class="box box-danger">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa fa-database"></i> Manage API Details</h3>
	</div>
	<div class="box-body">
		<div class="row">
			<div class="col-md-5">
				<div class="box box-danger"  style="background:#EFEFEF">
					<div class="box-header with-border">
						<h3 class="box-title">API List</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
			<?php
			if (valid_array($list_data) == true) {
				foreach ($list_data as $k => $v) {
					$booking_source = implode('<br>', explode(DB_SAFE_SEPARATOR, $v['asm_status_label']));
					echo '<strong><i class="fa fa-book margin-r-5"></i>  '.$v['name'].'</strong>
						<p class="text-muted">
							'.$booking_source.'.
						</p>
						<hr>';
				}
			}
			?>
					</div>
					<!-- /.box-body -->
				</div>
			</div>
			<div class="col-md-7">
				<div class="nav-tabs-custom">
					<!-- Nav pills -->
					<ul class="nav nav-tabs" role="tablist">
						<?php if (in_array(META_AIRLINE_COURSE, $active_domain_modules)) { ?>
						<li role="presentation" class="flight-l-bg <?php echo set_default_active_tab(META_AIRLINE_COURSE, $default_active_tab)?>"><a href="#flight" aria-controls="flight" role="pill" data-toggle="pill"><i class="fa fa-plane"></i> <span class="hidden-xs">Flight</span></a></li>
						<?php } ?>
						<?php if (in_array(META_ACCOMODATION_COURSE, $active_domain_modules)) { ?>
						<li role="presentation" class="hotel-l-bg <?php echo set_default_active_tab(META_ACCOMODATION_COURSE, $default_active_tab)?>"><a href="#hotel" aria-controls="hotel" role="pill" data-toggle="pill"><i class="fa fa-bed"></i> <span class="hidden-xs">Hotel</span></a></li>
						<?php } ?>
						<?php if (in_array(META_BUS_COURSE, $active_domain_modules)) { ?>
						<li role="presentation" class="bus-l-bg <?php echo set_default_active_tab(META_BUS_COURSE, $default_active_tab)?>"><a href="#bus" aria-controls="bus" role="pill" data-toggle="pill"><i class="fa fa-bus"></i> <span class="hidden-xs">Bus</span></a></li>
						<?php } ?>
					</ul>
				</div>
				<?php
				//Form Booking Source List Details
				foreach ($list_data as $k => $v) {
					switch ($v['course_id']) {
						case META_AIRLINE_COURSE : $airline_api_list = $v;
							break;
						case META_ACCOMODATION_COURSE : $accomodation_api_list = $v;
							break;
						case META_BUS_COURSE : $bus_api_list = $v;
							break;
					}
					
				}
				/**
				 * create array with booking source details
				 */
				function extract_booking_source_details($data_list)
				{
					$data = '';
					$booking_source_name = explode(DB_SAFE_SEPARATOR, $data_list['booking_source']);
					$booking_source_origin = explode(DB_SAFE_SEPARATOR, $data_list['bs_origin']);
					$booking_source_id = explode(DB_SAFE_SEPARATOR, $data_list['booking_source_id']);
					$asm_status = explode(DB_SAFE_SEPARATOR, $data_list['asm_status']);
					$course_origin = $data_list['origin'];
					foreach ($booking_source_id as $k => $v) {
						if ($asm_status[$k] == 'active') {
							$asm_status_needle = 'checked="checked"';
						} else {
							$asm_status_needle = '';
						}
						$data .= '<p class="text-navy"><label><input autocomplete="off" type="checkbox" data-mc-id="'.$course_origin.'" data-bs-id="'.$booking_source_origin[$k].'" class="asm-status-toggle" '.$asm_status_needle.'> '.$booking_source_name[$k].' - '.$booking_source_id[$k].'</label></p><hr>';
					}
					return $data;
				}
				?>
				<!-- Tab panes -->
				<div class="tab-content highlight">
					<p>Click On select box to activate/deactivate API</p>
					<?php if (in_array(META_AIRLINE_COURSE, $active_domain_modules)) { ?>
					<div role="tabpanel" class="clearfix tab-pane fade in <?php echo set_default_active_tab(META_AIRLINE_COURSE, $default_active_tab)?>" id="flight">
						<?php
						//Show All Booking Source
						if (empty($airline_api_list) == false) {
							echo extract_booking_source_details($airline_api_list);
						}
						?>
					</div>
					<?php } ?>
					<?php if (in_array(META_ACCOMODATION_COURSE, $active_domain_modules)) { ?>
					<div role="tabpanel" class="clearfix tab-pane fade in <?php echo set_default_active_tab(META_ACCOMODATION_COURSE, $default_active_tab)?>" id="hotel">
						<?php
						if (empty($accomodation_api_list) == false) {
							echo extract_booking_source_details($accomodation_api_list);
						}
						?>
					</div>
					<?php } ?>
					<?php if (in_array(META_BUS_COURSE, $active_domain_modules)) { ?>
					<div role="tabpanel" class="clearfix tab-pane fade in <?php echo set_default_active_tab(META_BUS_COURSE, $default_active_tab)?>" id="bus">
						<?php
						if (empty($bus_api_list) == false) {
							echo extract_booking_source_details($bus_api_list);
						}
						?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<!-- /.box-body -->
</div>
<script>
$(document).ready(function() {
	$('.asm-status-toggle').on('change', function() {
		var _bs_id = parseInt($(this).data('bs-id'));
		var _mc_id = parseInt($(this).data('mc-id'));
		if (_bs_id > 0 && _mc_id > 0) {
			$.get(app_base_url+"index.php/utilities/toggle_asm_status/"+_bs_id+"/"+_mc_id+"/"+this.checked, function(resp) {
			    location.reload();
			});
		}
	});
});
</script>