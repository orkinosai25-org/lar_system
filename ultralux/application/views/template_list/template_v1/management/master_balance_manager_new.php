<script src="<?php echo SYSTEM_RESOURCE_LIBRARY?>/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo SYSTEM_RESOURCE_LIBRARY?>/datatables/dataTables.bootstrap.min.js"></script>
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
    li.nested-dropdown {
	    position: relative;
	}
</style>
<!-- HTML BEGIN -->
<?php
/**if (form_visible_operation()) {
 $tab1 = " active ";
 $tab2 = "";

 } else {
 $tab2 = " active ";
 $tab1 = "";
 } **/
$url =$this->uri->segment(3);
if($url != null ){
	$tab1 = " active ";
	$tab2 = "";
}else{

	$tab2 = " active ";
	$tab1 = "";
}
if (is_array($search_params)) {
	extract($search_params);
}
$_datepicker = array(array('created_datetime_from', PAST_DATE), array('created_datetime_to', PAST_DATE));
$this->current_page->set_datepicker($_datepicker);
$this->current_page->auto_adjust_datepicker(array(array('created_datetime_from', 'created_datetime_to')));
?>
<!-- HTML BEGIN -->
<div class="bodyContent">
   <div class="table_outer_wrper">
      <!-- PANEL WRAP START -->
      <div class="panel_custom_heading">
         <!-- PANEL HEAD START -->
         <div class="org_row">
            <div class="col-md-3 rprt_lft">
               <h5>My Business</h5>
               <div class="dropdown">
                  <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown">
               Update Balance <i class="far fa-angle-down"></i>
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenu">
                     <!-- Nested Pills Inside Dropdown -->
                     <li class="nested-dropdown">
                        <a href="#">My Commission <i class="far fa-angle-right"></i></a>
                        <ul class="dropdown-menu">
                           <li><a href="<?php echo base_url().'management/b2b_airline_markup/flight_commission'; ?>" data-target="com_flight">Flight</a></li>
                           <!-- <li><a href="#" data-target="hotel">Hotel</a></li>
                           <li><a href="#" data-target="car">Car</a></li> -->
                        </ul>
                     </li>
                     <li class="nested-dropdown">
                        <a href="#">My Markup <i class="far fa-angle-right"></i></a>
                        <ul class="dropdown-menu">
                           <li><a href="<?php echo base_url().'management/b2b_airline_markup/flight_markup'?>" data-target="mrkup_flight">Flight</a></li>
                           <li><a href="<?php echo base_url().'management/b2b_airline_markup/hotel_markup'?>" data-target="mrkup_hotel">Hotel</a></li>
                           <li><a href="<?php echo base_url().'management/b2b_airline_markup/car_markup'?>" data-target="mrkup_car">Car</a></li>
                        </ul>
                     </li>
                     <li class="nested-dropdown">
                        <a href="#">Payment <i class="far fa-angle-right"></i></a>
                        <ul class="dropdown-menu">
                           <li><a href="<?php echo base_url().'management/b2b_balance_manager'?>" data-target="pay_update">Update Balance</a></li>
                           <li><a href="<?php echo base_url().'management/b2b_credit_limit'?>">Update Credit Limit</a></li>
                           <li><a href="<?php echo base_url().'management/b2b_airline_markup/bank_account'?>" data-target="pay_acc">Bank Account Details</a></li>
							<li><a href="<?php echo base_url().'management/b2b_airline_markup/balance'?>" data-target="pay_acc">Balance</a></li>
                        </ul>
                     </li>
                  </ul>
               </div>
            </div>
            <!--         <div class="panel_title">
               <?php include 'b2b_markup_header_tab.php';?>
               </div> -->
            <div id="selectedContent">
               <div class="tab-content" id="mrkup_flight">
                  <div class="col-md-12 rprt_rgt">
                     <div class="set_wraper">
                        <div class="panel_title_bak">
                           <div class="pull-left">
                              <i class="fa fa-edit"></i> Manage Flight Markup
                           </div>
                           <span class="pull-right">Note : Application Default Currency - 
                           <strong><?=get_application_default_currency()?></strong>			
                           </span>
                        </div>
                     </div>
                  </div>
                  <!-- PANEL HEAD START -->
                  <div class="panel_bdy">
                     <!-- Add Airline Starts-->
                     <fieldset>
                        <legend><i class="fa fa-plane"></i> Add Airline  <i class=" fa fa-plus"></i></legend>
                        <form action="" class="form-horizontal" method="POST" autocomplete="off">
						   <input type="hidden" name="markup_type" value="b2b_flight" />
                           <input type="hidden" name="form_values_origin" value="add_airline" />
                           <div class="row">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label for="new_airline_value" class="col-sm-3 control-label">Airlines<span class="text-danger">*</span></label>
                                    <div class="col-md-9">
                                       <select class="form-control" name="airline_code" required="required">
                                          <option value="">Please Select</option>
                                          <?php echo generate_options($airline_list);?>
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="radio">
                                    <label for="value_type" class="col-sm-4 control-label">Markup Type<span class="text-danger">*</span></label>
                                    <label for="airline_value_type_plus" class="radio-inline">
                                    <input checked="checked" type="radio" value="plus" id="airline_value_type_plus" name="value_type" class=" value_type_plus radioIp" checked="checked" required=""> Plus(+ <?=get_application_default_currency()?>)
                                    </label>
                                    <label for="airline_value_type_percent" class="radio-inline">
                                    <input type="radio" value="percentage" id="airline_value_type_percent" name="value_type" class=" value_type_percent radioIp" required=""> Percentage(%)
                                    </label>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label for="new_airline_value" class="col-sm-4 control-label">Markup Value</label>
                                    <input type="text" id="new_airline_value" name="specific_value" class=" generic_value numeric" placeholder="Markup Value" value="" />
                                 </div>
                              </div>
                           </div>
                           <div class="well well-sm">
                              <div class="clearfix col-md-offset-1">
                                 <button class=" btn btn-sm btn-success " id="add-airline-submit-btn" type="submit">Add</button>
                                 <button class=" btn btn-sm btn-warning " id="add-airline-reset-btn" type="reset">Reset</button>
                              </div>
                           </div>
                        </form>
                     </fieldset>
                  </div>
                  <!-- Add Airline Ends-->
                  <div class="panel_bdy">
                     <!-- PANEL BODY START -->
                     <fieldset>
                        <legend><i class="fa fa-plane"></i> Flight - General Markup</legend>
                        <form action="" class="form-horizontal" method="POST" autocomplete="off">
                           <div class="hide">
							  <input type="hidden" name="markup_type" value="b2b_flight" />
                              <input type="hidden" name="form_values_origin" value="generic" />
                              <input type="hidden" name="markup_origin" value="<?=@$generic_markup_list[0]['markup_origin']?>" />
                           </div>
                           <?php
                              $default_percentage_status = $default_plus_status = '';
                              if (isset($generic_markup_list[0]) == false || $generic_markup_list[0]['value_type'] == 'percentage') {
                              	$default_percentage_status = 'checked="checked"';
                              } else {
                              	$default_plus_status = 'checked="checked"';
                              }
                              ?>
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="radio">
                                    <label for="value_type" class="col-sm-4 control-label">Markup Type<span class="text-danger">*</span></label>
                                    <label for="value_type_plus" class="radio-inline">
                                    <input <?=$default_plus_status?> type="radio" value="plus" id="value_type_plus" name="value_type" class=" value_type_plus radioIp" checked="checked" required=""> Plus(+ <?=get_application_default_currency()?>)
                                    </label>
                                    <label for="value_type_percent" class="radio-inline">
                                    <input <?=$default_percentage_status?> type="radio" value="percentage" id="value_type_percent" name="value_type" class=" value_type_percent radioIp" required=""> Percentage(%)
                                    </label>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label for="generic_value" class="col-sm-4 control-label">Markup Value<span class="text-danger">*</span></label>
                                    <input type="text" id="generic_value" name="generic_value" class=" generic_value numeric" placeholder="Markup Value" required="" value="<?=@$generic_markup_list[0]['value']?>" />
                                 </div>
                              </div>
                           </div>
                           <div class="well well-sm">
                              <div class="clearfix col-md-offset-1">
                                 <button class=" btn btn-sm btn-success " id="general-markup-submit-btn" type="submit">Save</button>
                                 <button class=" btn btn-sm btn-warning " id="general-markup-reset-btn" type="reset">Reset</button>
                              </div>
                           </div>
                        </form>
                     </fieldset>
                  </div>
                  <!-- PANEL BODY END -->
                  <?php if (valid_array($specific_markup_list)) {//Check if airline list is present -Start IF ?>
                  <div class="panel_bdy">
                     <!-- PANEL BODY START -->
                     <fieldset>
                        <legend><i class="fa fa-plane"></i> Flight - Specific Airline Markup</legend>
                        <form action="<?=$_SERVER['PHP_SELF']?>" class="form-horizontal" method="POST" autocomplete="off">
						   <input type="hidden" name="markup_type" value="b2b_flight" />
                           <input type="hidden" name="form_values_origin" value="specific" />
                           <?php foreach ($specific_markup_list as $__airline_index => $__airline_record) {
                              $default_percentage_status = $default_plus_status = '';
                              if (empty($__airline_record['value_type']) == true || $__airline_record['value_type'] == 'percentage') {
                              	$default_percentage_status = 'checked="checked"';
                              } else {
                              	$default_plus_status = 'checked="checked"';
                              }
                              ?>
                           <div class="hide">
                              <input type="hidden" name="airline_origin[]" value="<?=$__airline_record['airline_origin']?>" />
                              <input type="hidden" name="markup_origin[]" value="<?=$__airline_record['markup_origin']?>" />
                           </div>
                           <div class="row">
                              <div class="col-md-2">
                                 <?=($__airline_index+1);?>
                                 <img src="<?=SYSTEM_IMAGE_DIR?>airline_logo/<?=$__airline_record['airline_code']?>.gif" alt="<?=$__airline_record['airline_name']?>">
                              </div>
                              <div class="col-md-2">
                                 <?=$__airline_record['airline_name']?>
                              </div>
                              <div class="col-md-4">
                                 <div class="radio">
                                    <label class="hide col-sm-4 control-label">Markup Type<span class="text-danger">*</span></label>
                                    <label for="value-type-plus-<?=$__airline_index?>" class="radio-inline">
                                    <input <?=$default_plus_status?> type="radio" value="plus" id="value-type-plus-<?=$__airline_index?>" name="value_type_<?=$__airline_record['airline_origin']?>" class=" value-type-plus radioIp" checked="checked" required=""> Plus(+ <?=get_application_default_currency()?>)
                                    </label>
                                    <label for="value-type-percent-<?=$__airline_index?>" class="radio-inline">
                                    <input <?=$default_percentage_status?> type="radio" value="percentage" id="value-type-percent-<?=$__airline_index?>" name="value_type_<?=$__airline_record['airline_origin']?>" class=" value-type-percent radioIp" required=""> Percentage(%)
                                    </label>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label for="specific-value-<?=$__airline_index?>" class="col-sm-4 control-label">Value</label>
                                    <input type="text" id="specific-value-<?=$__airline_index?>" name="specific_value[]" class=" specific-value numeric" placeholder="Markup Value" value="<?=$__airline_record['value']?>" />
                                 </div>
                              </div>
                           </div>
                           <hr>
                           <?php } ?>
                           <div class="well well-sm">
                              <div class="clearfix col-md-offset-1">
                                 <button class=" btn btn-sm btn-success " type="submit">Save</button>
                                 <button class=" btn btn-sm btn-warning " type="reset">Reset</button>
                              </div>
                           </div>
                        </form>
                     </fieldset>
                  </div>
                  <!-- PANEL BODY END -->
                  <?php } //check if airline list is present - End IF?>
               </div>
               <!-- Tab content -->
               <div class="tab-content" id="com_flight">
               </div>
               <div class="tab-content" id="mrkup_hotel">
				   <!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="table_outer_wrper"><!-- PANEL WRAP START -->
		<div class="panel_custom_heading"><!-- PANEL HEAD START -->
			
            
            <div class="set_wraper">
            <div class="panel_title_bak">
            <div class="pull-left">
				<i class="fa fa-edit"></i> Manage Hotel Markup
            </div>
				
				<span class="pull-right">Note : Application Default Currency - <strong><?=get_application_default_currency()?></strong></span>
		
			</div>
            </div>
            
            
		</div><!-- PANEL HEAD START -->
		<div class="panel_bdy"><!-- PANEL BODY START -->
			<fieldset><legend><i class="fa fa-hotel"></i> Hotel - Markup</legend>
				<form action="" class="form-horizontal" method="POST" autocomplete="off">
					<div class="hide">
						<input type="hidden" name="markup_type" value="b2b_hotel" />
						<input type="hidden" name="domain_origin" value="<?=get_domain_auth_id()?>" />
						<input type="hidden" name="form_values_origin" value="generic" />
						<input type="hidden" name="markup_origin" value="<?=@$hotel_markup_list['generic_markup_list'][0]['markup_origin']?>" />
					</div>
					<?php
					$default_percentage_status = $default_plus_status = '';
					if (isset($hotel_markup_list['generic_markup_list'][0]) == false || $hotel_markup_list['generic_markup_list'][0]['value_type'] == 'percentage') {
						$default_percentage_status = 'checked="checked"';
					} else {
						$default_plus_status = 'checked="checked"';
					}
					?>
					<div class="row">
						<div class="col-md-6">
							<div class="radio">
								<label for="value_type" class="col-sm-4 control-label">Markup Type<span class="text-danger">*</span></label>
								<label for="hotel_value_type_plus" class="radio-inline">
									<input <?=$default_plus_status?> type="radio" value="plus" id="hotel_value_type_plus" name="value_type" class=" value_type_plus radioIp" checked="checked" required=""> Plus(+ <?=get_application_default_currency()?>)
								</label>
								<label for="hotel_value_type_percent" class="radio-inline">
									<input <?=$default_percentage_status?> type="radio" value="percentage" id="hotel_value_type_percent" name="value_type" class=" value_type_percent radioIp" required=""> Percentage(%)
								</label>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="hotel_generic_value" class="col-sm-4 control-label">Markup Value<span class="text-danger">*</span></label>
								<input type="text" id="hotel_generic_value" name="generic_value" class=" generic_value numeric" placeholder="Markup Value" required="" value="<?=@$hotel_markup_list['generic_markup_list'][0]['value']?>" />
							</div>
						</div>
					</div>
					<div class="well well-sm">
						<div class="clearfix col-md-offset-1">
							<button class=" btn btn-sm btn-success " id="hotel_general-markup-submit-btn" type="submit">Save</button>
							<button class=" btn btn-sm btn-warning " id="hotel_general-markup-reset-btn" type="reset">Reset</button>
						</div>
					</div>
				</form>
			</fieldset>
		</div><!-- PANEL BODY END -->
		<?php
		//NOTE : NOT NEEDED AS OF NOW - Balu A
		if (valid_array($hotel_markup_list['specific_markup_list'])) {//Check if domain list is present -Start IF ?>
		<div class="panel-body"><!-- PANEL BODY START -->
			<fieldset><legend><i class="fa fa-hotel"></i> Hotel - Specific Domain Markup</legend>
				<form action="<?=$_SERVER['PHP_SELF']?>" class="form-horizontal" method="POST" autocomplete="off">
					 <input type="hidden" name="markup_type" value="b2b_hotel" />
					<input type="hidden" name="form_values_origin" value="specific" />
				<?php foreach ($hotel_markup_list['specific_markup_list'] as $__doamin_index => $__doamin_record) {
						$default_percentage_status = $default_plus_status = '';
						if (empty($__doamin_record['value_type']) == true || $__doamin_record['value_type'] == 'percentage') {
							$default_percentage_status = 'checked="checked"';
						} else {
							$default_plus_status = 'checked="checked"';
						}
				?>
						<div class="hide">
							<input type="hidden" name="domain_origin[]" value="<?=$__doamin_record['domain_origin']?>" />
							<input type="hidden" name="markup_origin[]" value="<?=$__doamin_record['markup_origin']?>" />
						</div>
						<div class="row">
							<div class="col-md-2">
								<?=($__doamin_index+1);?>
							</div>
							<div class="col-md-2">
								<?=$__doamin_record['domain_name']?>
							</div>
							<div class="col-md-4">
								<div class="radio">
									<label class="hide col-sm-4 control-label">Markup Type<span class="text-danger">*</span></label>
									<label for="hotel_value-type-plus-<?=$__doamin_index?>" class="radio-inline">
										<input <?=$default_plus_status?> type="radio" value="plus" id="hotel_value-type-plus-<?=$__doamin_index?>" name="value_type_<?=$__doamin_record['domain_origin']?>" class=" value-type-plus radioIp" checked="checked" required=""> Plus(+ <?=get_application_default_currency()?>)
									</label>
									<label for="hotel_value-type-percent-<?=$__doamin_index?>" class="radio-inline">
										<input <?=$default_percentage_status?> type="radio" value="percentage" id="hotel_value-type-percent-<?=$__doamin_index?>" name="value_type_<?=$__doamin_record['domain_origin']?>" class=" value-type-percent radioIp" required=""> Percentage(%)
									</label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label for="hotel_specific-value-<?=$__doamin_index?>" class="col-sm-4 control-label">Value</label>
									<input type="text" id="hotel_specific-value-<?=$__doamin_index?>" name="specific_value[]" class=" specific-value numeric" placeholder="Markup Value" value="<?=$__doamin_record['value']?>" />
								</div>
							</div>
						</div>
						<hr>
				<?php } ?>
				<div class="well well-sm">
					<div class="clearfix col-md-offset-1">
						<button class=" btn btn-sm btn-success " type="submit">Save</button>
						<button class=" btn btn-sm btn-warning " type="reset">Reset</button>
					</div>
				</div>
				</form>
			</fieldset>
		</div><!-- PANEL BODY END -->
		<?php } //check if domain list is present - End IF?>
	</div><!-- PANEL WRAP END -->
</div>
               </div>
               <div class="tab-content" id="mrkup_car">
				   <!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="table_outer_wrper"><!-- PANEL WRAP START -->
		<div class="panel_custom_heading"><!-- PANEL HEAD START -->
			
            
            <div class="set_wraper">
            <div class="panel_title_bak">
            <div class="pull-left">
				<i class="fa fa-edit"></i> Manage Car Markup
            </div>
				
				<span class="pull-right">Note : Application Default Currency - <strong><?=get_application_default_currency()?></strong></span>
		
			</div>
            </div>
            
            
		</div><!-- PANEL HEAD START -->
		<div class="panel_bdy"><!-- PANEL BODY START -->
			<fieldset><legend><i class="fa fa-car"></i> Car - Markup</legend>
				<form action="" class="form-horizontal" method="POST" autocomplete="off">
					<div class="hide">
						<input type="hidden" name="markup_type" value="b2b_car" />
						<input type="hidden" name="domain_origin" value="<?=get_domain_auth_id()?>" />
						<input type="hidden" name="form_values_origin" value="generic" />
						<input type="hidden" name="markup_origin" value="<?=@$car_markup_list['generic_markup_list'][0]['markup_origin']?>" />
					</div>
					<?php
					$default_percentage_status = $default_plus_status = '';
					if (isset($car_markup_list['generic_markup_list'][0]) == false || $car_markup_list['generic_markup_list'][0]['value_type'] == 'percentage') {
						$default_percentage_status = 'checked="checked"';
					} else {
						$default_plus_status = 'checked="checked"';
					}
					?>
					<div class="row">
						<div class="col-md-6">
							<div class="radio">
								<label for="value_type" class="col-sm-4 control-label">Markup Type<span class="text-danger">*</span></label>
								<label for="car_value_type_plus" class="radio-inline">
									<input <?=$default_plus_status?> type="radio" value="plus" id="car_value_type_plus" name="value_type" class=" value_type_plus radioIp" checked="checked" required=""> Plus(+ <?=get_application_default_currency()?>)
								</label>
								<label for="car_value_type_percent" class="radio-inline">
									<input <?=$default_percentage_status?> type="radio" value="percentage" id="car_value_type_percent" name="value_type" class=" value_type_percent radioIp" required=""> Percentage(%)
								</label>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="generic_value" class="col-sm-4 control-label">Markup Value<span class="text-danger">*</span></label>
								<input type="text" id="generic_value" name="generic_value" class=" generic_value numeric" placeholder="Markup Value" required="" value="<?=@$car_markup_list['generic_markup_list'][0]['value']?>" />
							</div>
						</div>
					</div>
					<div class="well well-sm">
						<div class="clearfix col-md-offset-1">
							<button class=" btn btn-sm btn-success " id="car_general-markup-submit-btn" type="submit">Save</button>
							<button class=" btn btn-sm btn-warning " id="car_general-markup-reset-btn" type="reset">Reset</button>
						</div>
					</div>
				</form>
			</fieldset>
		</div><!-- PANEL BODY END -->
		<?php
		//NOTE : NOT NEEDED AS OF NOW - Balu A
		if (valid_array($car_markup_list['specific_markup_list'])) {//Check if domain list is present -Start IF ?>
		<div class="panel-body"><!-- PANEL BODY START -->
			<fieldset><legend><i class="fa fa-car"></i> Car - Specific Domain Markup</legend>
				<form action="<?=$_SERVER['PHP_SELF']?>" class="form-horizontal" method="POST" autocomplete="off">
					<input type="hidden" name="form_values_origin" value="specific" />
				<?php foreach ($car_markup_list['specific_markup_list'] as $__doamin_index => $__doamin_record) {
						$default_percentage_status = $default_plus_status = '';
						if (empty($__doamin_record['value_type']) == true || $__doamin_record['value_type'] == 'percentage') {
							$default_percentage_status = 'checked="checked"';
						} else {
							$default_plus_status = 'checked="checked"';
						}
				?>
						<div class="hide">
							<input type="hidden" name="domain_origin[]" value="<?=$__doamin_record['domain_origin']?>" />
							<input type="hidden" name="markup_origin[]" value="<?=$__doamin_record['markup_origin']?>" />
						</div>
						<div class="row">
							<div class="col-md-2">
								<?=($__doamin_index+1);?>
							</div>
							<div class="col-md-2">
								<?=$__doamin_record['domain_name']?>
							</div>
							<div class="col-md-4">
								<div class="radio">
									<label class="hide col-sm-4 control-label">Markup Type<span class="text-danger">*</span></label>
									<label for="car_value-type-plus-<?=$__doamin_index?>" class="radio-inline">
										<input <?=$default_plus_status?> type="radio" value="plus" id="car_value-type-plus-<?=$__doamin_index?>" name="value_type_<?=$__doamin_record['domain_origin']?>" class=" value-type-plus radioIp" checked="checked" required=""> Plus(+ <?=get_application_default_currency()?>)
									</label>
									<label for="car_value-type-percent-<?=$__doamin_index?>" class="radio-inline">
										<input <?=$default_percentage_status?> type="radio" value="percentage" id="car_value-type-percent-<?=$__doamin_index?>" name="value_type_<?=$__doamin_record['domain_origin']?>" class=" value-type-percent radioIp" required=""> Percentage(%)
									</label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label for="car_specific-value-<?=$__doamin_index?>" class="col-sm-4 control-label">Value</label>
									<input type="text" id="car_specific-value-<?=$__doamin_index?>" name="specific_value[]" class=" specific-value numeric" placeholder="Markup Value" value="<?=$__doamin_record['value']?>" />
								</div>
							</div>
						</div>
						<hr>
				<?php } ?>
				<div class="well well-sm">
					<div class="clearfix col-md-offset-1">
						<button class=" btn btn-sm btn-success " type="submit">Save</button>
						<button class=" btn btn-sm btn-warning " type="reset">Reset</button>
					</div>
				</div>
				</form>
			</fieldset>
		</div><!-- PANEL BODY END -->
		<?php } //check if domain list is present - End IF?>
	</div><!-- PANEL WRAP END -->
</div>
               </div>
               <div class="tab-content active" id="pay_update">
				       <div id="general_user" class="bodyContent">
                	<div class="table_outer_wrper"><!-- PANEL WRAP START -->
                			<div class="tab-content active">
                      <div class="col-md-9 rprt_rgt nopadL up_tab"><!-- PANEL HEAD START -->
                      <div class="panel_custom_heading"><!-- PANEL HEAD START -->
                        <div class="panel_title">
                          <ul class="nav nav-tabs b2b_navul" role="tablist" id="myTab">
                            <!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE START-->
                            <li role="presentation" class="<?php echo $tab1; ?>">
                              <a id="fromListHead" href="#fromList" aria-controls="home" role="tab" data-toggle="tab">
                                <i class="far fa-money-bill-alt"></i>
                                New Balance Request
                              </a>
                            </li>
                            <li role="presentation" class="<?php echo $tab2; ?>">
                              <a href="#tableList" aria-controls="profile" role="tab" data-toggle="tab">
                              <i class="far fa-money-bill"></i>
                              Sent Balance Request
                              </a>
                            </li>
                            <!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE END -->
                          </ul>
                        </div>
                      </div>
                      </div><!-- PANEL HEAD START -->
                				<div role="tabpanel" class="tab-pane <?php echo $tab1; ?> clearfix" id="fromList">  
                				<div class="panel_inside updt_blnc fltr">
                                 <!--   <div class="col-xs-4">
                                        <label class="bordinglbl">Request Type </label>
                                        <div class="panel_selcts selctmark_dash">
                                            <select id="balance_request_type" class="normalsel_dash" autocomplete="off">
                                                <?php echo generate_options($provab_balance_requests, array($balance_request_type));?>
                                            </select>
                                        </div>
                                     </div>  -->
                                  <div class="col-md-9 rprt_rgt nopadL">
                                  <h5>Filters:</h5>
                                  <div class="form-group">
                                   <div class="col-sm-5 nopad"> 
                                      <label class="control-label">Request Type </label>
                                      <div class="panel_selcts">
                                          <select id="balance_request_type" class="normalsel_dash form-control" autocomplete="off">
                                              <?php echo generate_options($provab_balance_requests, array($balance_request_type));?>
                                          </select>
                                      </div>
                                   </div>
                                  </div>
                                  </div>
                                  <div class="clearfix"></div>
                                 
                					<div class="section_deposite col-xs-12 mt-2">
                					<?php
                						/************************ GENERATE CURRENT PAGE FORM ************************/
                						echo $balance_page_obj->generate_form('request_form', $form_data);
                						/************************ GENERATE UPDATE PAGE FORM ************************/
                					?>
                					</div>
                               </div>
                            </div>
                            <div role="tabpanel" class="tab-pane nopad <?php echo $tab2; ?> clearfix" id="tableList">
                            <div class="col-md-9 rprt_rgt nopadL">
                			<form method="GET" autocomplete="off">
                        <h5>Filters:</h5>
                        <div class="fltr">
                          <div class="org_row updt_blnc">                          
                          <div class="col-md-10 col-xs-12">
                          <div class="sml_row">
                            <div class="col-md-3 col-xs-6">
                  						<label>
                  						Transaction Number
                  						</label>
                  						<input type="text" class="form-control" name="system_transaction_id" value="<?=@$system_transaction_id?>" placeholder="Transaction Number">
                  					</div>
                  					<div class="col-md-3 col-xs-6">
                  						<label>
                  						Status
                  						</label>
                  						<select class="form-control" name="status">
                  							<option>All</option>
                  							<?=generate_options($status_options, array(@$status))?>
                  						</select>
                  					</div>
                  					<div class="col-md-3 col-xs-6">
                  						<label>
                  						Request From
                  						</label>
                  						<input type="text" readonly id="created_datetime_from" class="form-control" name="created_datetime_from" value="<?=@$created_datetime_from?>" placeholder="Request Date">
                  					</div>
                  					<div class="col-md-3 col-xs-6">
                  						<label>
                  						Request To
                  						</label>
                  						<input type="text" readonly id="created_datetime_to" class="form-control disable-date-auto-update" name="created_datetime_to" value="<?=@$created_datetime_to?>" placeholder="Request Date">
                  					</div>
                				</div>
                      </div>
                				<div class="col-md-2 col-xs-12 nopadL">
                        <label>&nbsp;</label>
                        <div class="aply_btn">
                  				<button type="submit" class="btn btn-primary aply">Search</button> 
                  				<button type="reset" class="btn btn-warning rest">Reset</button> 
                        </div>
                				<!-- <a href="<?php echo base_url(); ?>index.php/management/b2b_balance_manager" id="clear-filter" class="btn btn-primary">ClearFilter</a> -->
                				</div>
                      </div>
                    </div>
                  </form>
                		</div>
                    <div class="clearfix"></div>
                    <div class="col-xs-12 my-2">
                    	<strong>Current Balance</strong> : <strong><?php
        // debug($balance);exit;
                    	 $balance = agent_current_application_balance(); 
                    	echo agent_base_currency().' '.@$balance['value']?></strong>
                    </div>
                    <div class="org_row up_tbl">
                				
                				<?php
                					/************************ GENERATE CURRENT PAGE TABLE ************************/
                					echo get_table_balance($table_data);
                					/************************ GENERATE CURRENT PAGE TABLE ************************/
                				?>
                        </div>
                    </div>
                    </div>
                			</div>
                	</div><!-- PANEL WRAP END -->
                </div>
               </div>
               <div class="tab-content" id="pay_limit">
               </div>
               <div class="tab-content" id="pay_acc">
				   <div class="bodyContent col-md-12">
<div class="table_outer_wrper"><!-- PANEL WRAP START -->

<!-- PANEL HEAD START -->
<div class="panel_bdy"><!-- PANEL BODY START -->


<!-- Table List -->
<div role="tabpanel" class="tab-pane active clearfix" id="tabList">
<div class="col-md-12">
<?php
echo get_table($table_data);
?>
</div>
</div>


</div>
<!-- PANEL BODY END --></div>
<!-- PANEL WRAP END --></div>

<?php
function get_table($table_data='')
{
	//debug($table_data);exit;
	$table = '
<div class="table-responsive col-md-12 nopad"><table class="table table-hover table-striped table-bordered table-condensed">';
	$table .= '<tr>
<th><i class="fa fa-sort-numeric-asc"></i> '.get_app_message('AL006').'</th>
<th>Bank Logo</th>
<th>Account Name</th>
<th>Account Number</th>
<th>Bank Name</th>
<th>Branch Name</th>
<th>IFSC Code</th>
</tr>';

	if (valid_array($table_data) == true) {
		$segment_3 = $GLOBALS['CI']->uri->segment(3);
		$current_record =0;
		foreach ($table_data as $k => $v) {			
			$table .= '<tr>
			<td>'.(++$current_record).'</td>
			<td><img height="75px" width="75px" src="'.$GLOBALS ['CI']->template->domain_images('bank_logo/'.$v['bank_icon']).'" alt="Bank Logo"></td>
			<td>'.$v['en_account_name'].'</td>
			<td>'.$v['account_number'].'</td>
			<td>'.$v['en_bank_name'].'</td>
			<td>'.$v['en_branch_name'].'</td>
			<td>'.$v['ifsc_code'].'</td>
</tr>';
		}
	} else {
		$table .= '<tr><td colspan="7">No Data Found</td></tr>';
	}
	$table .= '</table></div>';
	return $table;
}
?>

               </div>
               <!-- PANEL WRAP END -->
            </div>
         </div>
      </div>
   </div>
</div>
<script type="text/javascript">
$(document).ready(function () {
    // Handle main dropdown toggle
    $('#dropdownMenu').on('click', function (e) {
        e.preventDefault();
        $(this).next('.dropdown-menu').toggle();
    });

    // Handle nested dropdown toggle
    $('.nested-dropdown > a').on('click', function (e) {
        e.preventDefault();
        var submenu = $(this).next('.dropdown-menu');

        // Hide other open submenus
        $('.nested-dropdown .dropdown-menu').not(submenu).hide();
        submenu.toggle();
    });

    // Handle item selection
    $('.dropdown-menu a[data-target]').on('click', function (e) {
		//alert($(this).attr('href'));
		if($(this).attr('href') == "#"){
			e.preventDefault();
			var selectedText = $(this).text();
			var target = $(this).data('target');

			// Update the main dropdown button text
			$('#dropdownMenu').html(selectedText + ' <i class="fa fa-angle-down"></i>');

			// Remove active class from all, then add to the selected item
			$('.dropdown-menu a').removeClass('active');
			$(this).addClass('active');

			// Show selected content
			$('.tab-content').removeClass('active');
			$('#' + target).addClass('active');

			// Close the main dropdown (but not nested ones)
			if (!$(this).closest('.nested-dropdown').length) {
				$('.dropdown-menu').hide();
			}
		}
		else{
			 window.location.href = $(this).attr('href');
		}
    });

    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').hide();
        }
    });

    // Prevent dropdown closing when clicking inside a nested menu
    $('.nested-dropdown .dropdown-menu').on('click', function (e) {
        e.stopPropagation();
        $('.dropdown-menu').hide();
    });

    // Initialize Bootstrap Datepicker
    $('.datepick').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true
    });
	$('#balance_request_type').on('change', function() {
		//reload window with new parameter
		var _request_type = $(this).val();
		if (_request_type != '') {
			window.location.href = app_base_url+'index.php/management/b2b_balance_manager/'+_request_type;
		} else {
			location.reload();
		}
	});
	//Get Branch based on Bank -- Balu A
	$('#bank_id').change(function(){
		var bank_id = $(this).val().trim();
		if(bank_id!="" && isNaN(bank_id) == false && parseInt(bank_id) >= 1) {
			$('#bank').val($('#bank_id option:selected').text());
			$.get(app_base_url+'index.php/ajax/get_bank_branches/'+bank_id, function(response){
				var bank_branch = '';
				if(response.status == true) {
					bank_branch = response.branch;
					account_number = response.account_number;
				}
				$('#branch').val(bank_branch);
				$('#account_number').val(account_number);
				
			});
		} else {
			$('#branch').val('');
			$('#account_number').val('');
			$('#bank').val('');
		}
	});
});

</script>
<?php 
function get_table_balance($table_data='')
{
	$table = '
   <div class="table-responsive col-md-12">
   <table class="table table-hover table-striped table-bordered table-condensed" id="balance_request_table">';
      $table .= '<thead><tr>
   <th>Sno</th>
   <th>System Transaction</th>
   <th>Mode Of Payment</th>
   <th>Amount</th>
   <th>Bank</th>
   <th>Branch</th>
   <th>Status</th>
   <th>Bank Deposit Slip</th>
   <th>Request Sent On</th>
   <th>Update Remarks</th>
   </tr></thead><tbody>';
	if (valid_array($table_data) == true) {
		foreach ($table_data as $k => $v) {
			$current_request_status = strtoupper($v['status']);
			$table .= '<tr>
			<td>'.($k+1).'</td>
			<td>'.$v['system_transaction_id'].'</td>
			<td>'.get_enum_list('provab_balance_requests', strtoupper($v['transaction_type'])).'</td>
			<td>'.$v['amount'].'</td>
			<td>'.$v['bank'].'</td>
			<td>'.$v['branch'].'</td>
			<td><span class="label '.balance_status_label($current_request_status).'">'.$current_request_status.'</span></td>';
			if($v['image'] != ''){
				$table .='<td><img src="'.$GLOBALS ['CI']->template->domain_images('deposit_slips/'.$v['image']).'" height="100px" width="120px" class="img-thumbnail"></td>';
				//<td><a href="'.$GLOBALS['CI']->template->file_full_path ('deposit_slips/'.$v['image']).'" target="_blank">Click to view</a></td>';
			}else{
				$table .='<td>Not Available</td>';
			}
			$table.='<td>'.app_friendly_absolute_date($v['created_datetime']).'</td>
			<td>'.$v['update_remarks'].'<br>'.app_friendly_absolute_date($v['updated_datetime']).'</td>
	</tr>';
		}
	} else {
		$table .= '<tr><td>---</td><td>---</td><td>---</td><td>---</td><td>---</td><td>---</td><td>---</td><td>---</td><td>---</td><td>---</td></tr>';
	}
	$table .= '</tbody></table></div>';
	return $table;
}
?>
<script>
$(document).ready(function() {
    $('#balance_request_table').DataTable({
        // Disable initial sort 
        "aaSorting": []
    });
});
</script>