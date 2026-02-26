<?php

//Read cookie when user has not given any search
if ((isset($flight_search_params) == false) || (isset($flight_search_params) == true && valid_array($flight_search_params) == false)) {
	//parse_str(get_cookie('flight_search'), $flight_search_params);
	$sparam = $this->input->cookie('sparam', TRUE);
	$sparam = unserialize($sparam);
	$sid = intval(@$sparam[META_AIRLINE_COURSE]);
 
	$flight_search_params = array();
	if ($sid > 0) {
		$this->load->model('flight_model');
		$flight_search_params = $this->flight_model->get_safe_search_data($sid, true);
		
		$flight_search_params = @$flight_search_params['data'];
		if ($flight_search_params['trip_type'] != 'multicity' && strtotime(@$flight_search_params['depature']) < time() ) {
			$flight_search_params['depature'] = date('d-m-Y');
			if (isset($flight_search_params['return']) == true) {
				$flight_search_params['return'] = date('d-m-Y', strtotime(add_days_to_date(1)));
			}
		}
	}
}
$onw_rndw_segment_search_params = array();
$multicity_segment_search_params = array();
if(@$flight_search_params['trip_type'] != 'multicity') {
	$onw_rndw_segment_search_params = $flight_search_params;
} else {//MultiCity
	$multicity_segment_search_params = $flight_search_params;
}
$flight_datepicker = array(array('flight_datepicker1', FUTURE_DATE_DISABLED_MONTH), array('flight_datepicker2', FUTURE_DATE_DISABLED_MONTH));
$this->current_page->set_datepicker($flight_datepicker);
$airline_list = $GLOBALS['CI']->db_cache_api->get_airline_code_list();
if(isset($flight_search_params['adult_config']) == false || intval($flight_search_params['adult_config']) < 1) {
	$flight_search_params['adult_config'] = 1;
}
$tripType = $flight_search_params['trip_type'] ?? null;
$checked = (!$tripType || $tripType == 'oneway') ? 'checked' : '';
?>
<form autocomplete="off" name="flight" id="flight_form" action="<?php echo base_url();?>index.php/general/pre_flight_search" method="get" class="activeForm oneway_frm" style="">
	<div class="tabspl">
		<div class="tabrow">
			<div class="waywy">
				<div class="smalway">
				<label class="wament hand-cursor">
					<input class="hide" type="radio" name="trip_type"
    <?= (!isset($flight_search_params['trip_type']) || $flight_search_params['trip_type'] === 'oneway') ? 'checked="checked"' : '' ?>
    id="onew-trp" value="oneway" /> One way					</label>
					<label class="wament hand-cursor">
						<input class="hide" type="radio" name="trip_type" <?=(@$flight_search_params['trip_type'] == 'circle' ? 'checked="checked"' : '')?> id="rnd-trp" value="circle" /> Roundtrip<span> / Return</span>
					</label>
					<label class="wament hand-cursor">
						<input class="hide" type="radio" name="trip_type" <?=(@$flight_search_params['trip_type'] == 'multicity' ? 'checked="checked"' : '')?> id="multi-trp" value="multicity" /> Multi-city
					</label>
				</div>
                                  <div class="col-xs-5 col-md-1 nopad pull-right hide">
                <div class="searchsbmt_speak">

                    <a id="rec" class="btn btn-raised btn-default mobbtn dropdown-toggle" data-toggle="dropdown" style="border-radius: 50%; background: #e6e6e6; height: 40px; width: 40px;"><img style=" width: 14px; padding-top: 2px;" src="<?php echo $GLOBALS['CI']->template->template_images('mike.png'); ?>">
                    <span class="beta">Beta</span></a>

                    <div class="dropdown-menu speak_bar_box">
                       <div class="speak_static_text">Please say something like this</div>
                       <div class="bot-text-speak">
		                       <span id="chat-dialog-queue-span" style="opacity: 1;"><i>“</i> Flight for 2 adults from Delhi to Mumbai <i>”</i></span>
                       </div>

                   <div class="mike-noanimate" id="mikewave">
                    <div class="sound-icon">
                      <div class="mike-wave">
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                        <i class="barr"></i>
                      </div>
                    </div>
                  </div>

                    </div>

                </div>

            </div>

				<div class="col-xs-5 col-md-2 nopad pull-right hide">
				<button class="farhomecal" id="flight_fare_calendar"><span class="fal fa-calendar-alt"></span> Fare Calendar</button>
			</div>
			</div>
            
            <div class="flt_brdr col-xs-12 nopad">
			<div id="onw_rndw_fieldset" class="col-md-9 nopad"><!-- Oneway/Roundway Fileds Starts-->
				<div class="col-md-8 nopad placerows br_rt">
					<div class="col-xs-6 padfive">
						<div class="lablform">From</div>
						<div class="plcetogo deprtures sidebord">
							<input type="text" autocomplete="off" name="from" class="fromflight normalinput auto-focus valid_class form-control b-r-0" id="from" placeholder="Type Departure City" value="<?php echo @$onw_rndw_segment_search_params['from'] ?>" required />
								<span class="truncate airportname airport_name_from"><?php echo @$onw_rndw_segment_search_params['from_loc_airport_name'] ?></span>
							<input class="hide loc_id_holder" id="from_loc_id" name="from_loc_id" type="hidden" value="<?=@$onw_rndw_segment_search_params['from_loc_id']?>" >
							<input class="hide from_loc_airport_name" id="from_loc_airport_name" name="from_loc_airport_name" type="hidden" value="<?= @$onw_rndw_segment_search_params['from_loc_airport_name'] ?>">

					<div class="flight_chnge"><i class="far fa-long-arrow-right rot_arrow"></i>
						<i class="far fa-exchange rot_arrow hide"></i></div>
						</div>
						
					</div>
					<div class="col-xs-6 padfive ft_dest">
						<div class="lablform">To</div>
						<div class="plcetogo destinatios sidebord">
							<input type="text" autocomplete="off" name="to"  class="departflight normalinput auto-focus valid_class form-control b-r-0" id="to" placeholder="Type Destination City" value="<?php echo @$onw_rndw_segment_search_params['to'] ?>" required/>
							<span class="truncate airportname airport_name_to"><?php echo @$onw_rndw_segment_search_params['to_loc_airport_name'] ?></span>
							<input class="hide loc_id_holder" id="to_loc_id" name="to_loc_id" type="hidden" value="<?=@$onw_rndw_segment_search_params['to_loc_id']?>" >
								<input class="hide to_loc_airport_name" id="to_loc_airport_name" name="to_loc_airport_name" type="hidden" value="<?= @$onw_rndw_segment_search_params['to_loc_airport_name'] ?>">
						</div>
					</div>
				</div>
				<div class="col-md-4 nopad secndates">
					<div class="col-xs-6 padfive">
						<div class="lablform">Departure</div>
						<div class="plcetogo datemark sidebord datepicker_new1" iditem="flight_datepicker1">
							<input type="text" readonly class=" normalinput auto-focus hand-cursor form-control b-r-0" id="flight_datepicker1" placeholder="Select Date" value="<?php echo @$onw_rndw_segment_search_params['depature'] ?>" name="depature" required/>
							<span class="flt_day checkInDayDiv"><?php if(empty($onw_rndw_segment_search_params['depature']) == false){echo date("l", strtotime(@$onw_rndw_segment_search_params['depature']));}?></span>
						</div>
					</div>
					<div class="col-xs-6 padfive date-wrapper">
						<div class="lablform">Return</div>
						<div class="plcetogo datemark sidebord datepicker_new2" iditem="flight_datepicker2">
							<input type="text" readonly class="normalinput auto-focus hand-cursor form-control b-r-0" id="flight_datepicker2" name="return" placeholder="Select Date" value="<?php echo @$onw_rndw_segment_search_params['return'] ?>" <?=(@$onw_rndw_segment_search_params['trip_type'] != 'circle' ? 'disabled="disabled"' : '')?> />
							<span class="flt_day checkOutDayDiv"><?php if(empty($onw_rndw_segment_search_params['return']) == false){echo date("l", strtotime(@$onw_rndw_segment_search_params['return']));}?></span>
						</div>
					</div>
				</div>
			</div><!-- Oneway/Roundway Fileds Ends-->
            
            
			<?=$GLOBALS['CI']->template->isolated_view('share/flight_multi_way_search', array('multicity_segment_search_params' => $multicity_segment_search_params))?><!-- Multiway-->
            
			<div class="col-md-3 col-xs-12 nopad thrdtraveller">
				<div class="col-xs-9 padfive mobile_width">
					<div class="lablform">Travellers</div>
					<div class="totlall">
						<span class="remngwd"><span class="total_pax_count"></span> <span id="travel_text">Traveller</span></span>
						<div class="roomcount pax_count_div">

						<?php
				//debug($flight_search_params);exit;
						//Airline Class
						// $v_class = array('Economy' => 'Economy', 'PremiumEconomy' => 'Premium Economy', 'Business' => 'Business', 'PremiumBusiness' => 'Premium Business', 'First' => 'First');
						$v_class = array('Economy' => 'Economy', 'PremiumEconomy' => 'Premium Economy','Business' => 'Business', 'First' => 'First');
						$airline_classes = '';
						if(isset($flight_search_params['v_class']) == true && empty($flight_search_params['v_class']) == false) {
							$choosen_airline_class = $v_class[$flight_search_params['v_class']];
							$irline_class_value = $flight_search_params['v_class'];
							//$air_class ='';
						} else {
							$choosen_airline_class = 'Economy';
							$irline_class_value = 'Economy';
							//$air_class = 'active';
						}
						foreach($v_class as $v_class_k => $v_class_v) {
							if($v_class_v == $choosen_airline_class){
								$air_class = 'active';
							}
							else{
								$air_class ='';
							}
							$airline_classes .= '<a class="adscrla choose_airline_class '.$air_class.'" data-airline_class="'.$v_class_k.'">'.$v_class_v.'</a>';
						}
						//Preferred Airlines
						if(isset($flight_search_params['carrier'][0]) == true && empty($flight_search_params['carrier'][0]) == false &&  $flight_search_params['carrier'][0] != 'all') {
							$choosen_airline_name = $airline_list[$flight_search_params['carrier'][0]];
						} else {
							$choosen_airline_name = 'Preferred Airline';
						}
						$preferred_airlines = '<a class="adscrla choose_preferred_airline" data-airline_code="">All</a>';
						foreach($airline_list as $airline_list_k => $airline_list_v) {
							$preferred_airlines .= '<a class="adscrla choose_preferred_airline" data-airline_code="'.$airline_list_k.'">'.$airline_list_v.'</a>';
						}
					?>
                   <div class="advance_opt">
					<div class="col-xs-12 nopad">
					<div class="lablform2">Cabin Class</div>
						<div class="alladvnce">
							<span class="remngwd" id="choosen_airline_class"><?php echo $choosen_airline_class;?></span>
							<input type="hidden" autocomplete="off" name="v_class" id="class" value="<?php echo $irline_class_value;?>" >
							<div class="advncedown spladvnce class_advance_div">
								<div class="inallsnnw">
									<div class="scroladvc">
										<?php echo $airline_classes;?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xs-12 nopad">
					<div class="lablform2">Preferred Airline</div>
						<div class="alladvnce">
						<select class="js-example-basic-single" name="carrier[]">
						<option value="">All</option>
										<?php 

										foreach($airline_list as $airline_list_k => $airline_list_v) {?>
										
										  <option value="<?php echo $airline_list_k; ?>"><?php echo $airline_list_v; ?></option>
										  <?php } ?>
										 </select>
                                                    <?php  //debug($flight_search_params);exit; ?>
<!-- 						<select id="airline_select" style="width:300px;">
									
									</select> -->
						<!-- 	<span class="remngwd" id="choosen_preferred_airline"><?php //echo $choosen_airline_name;?></span> -->
							<!-- <input type="hidden" autocomplete="off" name="carrier[]" id="carrier" value="<?php echo @$flight_search_params['carrier'][0];?>" > -->
							<!-- <div class="advncedown spladvnce preferred_airlines_advance_div">

								<div class="inallsnnw">
									<div class="scroladvc">
										
										<?php //echo $preferred_airlines;?>
									</div>
								</div>
							</div> -->
						</div>
					</div>
					</div>

						<div class="mobile_adult_icon">Travellers<i class="fa fa-male"></i></div>
						
							<div class="inallsn">
								<div class="oneroom fltravlr">
								<div class="lablform2">Travellers</div>
									<div class="clearfix"></div>
									<div class="roomrow">

										<div class="celroe col-xs-7"><i class="fal fa-male"></i> Adults
											<span class="agemns">(12+)</span>
										</div>
										<div class="celroe col-xs-5">
											<div class="input-group countmore pax-count-wrapper adult_count_div"> <span class="input-group-btn">
												<button type="button" class="btn btn-default btn-number" data-type="minus" data-field="adult"> <span class="glyphicon glyphicon-minus"></span> </button>
												</span>
												<input type="text" id="OWT_adult" name="adult" class="form-control input-number centertext valid_class pax_count_value" value="<?=(int)@$flight_search_params['adult_config']?>" min="1" max="9" readonly>
												<span class="input-group-btn">
												<button type="button" class="btn btn-default btn-number" data-type="plus" data-field="adult"> <span class="glyphicon glyphicon-plus"></span> </button>
												</span> 
											</div>
										</div>
									</div>
									<div class="roomrow">
										<div class="celroe col-xs-7"><i class="fal fa-child"></i> Children
											<span class="agemns">(2-11)</span>
										</div>
										<div class="celroe col-xs-5">
											<div class="input-group countmore pax-count-wrapper child_count_div"> <span class="input-group-btn">
												<button type="button" class="btn btn-default btn-number" data-type="minus" data-field="child"> <span class="glyphicon glyphicon-minus"></span> </button>
												</span>
												<input type="text" id="OWT_child" name="child" class="form-control input-number centertext pax_count_value" value="<?=(int)@$flight_search_params['child_config']?>" min="0" max="9" readonly>
												<span class="input-group-btn">
												<button type="button" class="btn btn-default btn-number" data-type="plus" data-field="child"> <span class="glyphicon glyphicon-plus"></span> </button>
												</span> 
											</div>
										</div>
									</div>
									<div class="roomrow last">
										<div class="celroe col-xs-7"><i class="fal fa-child"></i> Infants
											<span class="agemns">(0-2)</span>
										</div>
										<div class="celroe col-xs-5">
											<div class="input-group countmore pax-count-wrapper infant_count_div"> <span class="input-group-btn">
												<button type="button" class="btn btn-default btn-number" data-type="minus" data-field="infant"> <span class="glyphicon glyphicon-minus"></span> </button>
												</span>
												<input type="text" id="OWT_infant" name="infant" class="form-control input-number centertext pax_count_value" value="<?=(int)@$flight_search_params['infant_config']?>" min="0" max="9" readonly>
												<span class="input-group-btn">
												<button type="button" class="btn btn-default btn-number" data-type="plus" data-field="infant"> <span class="glyphicon glyphicon-plus"></span> </button>
												</span> 
											</div>
										</div>
									</div>
									<!-- Infant Error Message-->
									<div class="roomrow last">
										<div class="celroe col-xs-12">
										<div class="alert-wrapper hide">
										<div role="alert" class="alert alert-error">
											<span class="alert-content"></span>
										</div>
										</div>
										</div>
									</div>
									<a class="done1 comnbtn_room1"><span class="fa fa-check"></span> Done</a>
									<!-- Infant Error Message-->
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-3 padfive mobile_width">
					<!-- <div class="lablform">&nbsp;</div> -->
					<div class="searchsbmtfot">
						<input type="submit" name="search_flight" id="flight-form-submit" class="searchsbmt flight_search_btn" value="" />
						<i class="far fa-search"></i>
					</div>
				</div>
                <div class="clearfix"></div>
				
			</div>
			</div>
			<div class="clearfix"></div>
			<div class="alert-box" id="flight-alert-box"></div>			
		</div>
	</div>
</form>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
<script>
$(document).ready(function() {
	var select_val ='<?php if(isset($flight_search_params['carrier'][0])){ echo $flight_search_params['carrier'][0]; } ?>';
	//alert(select_val);
    $('.js-example-basic-single').select2();
    //$('.js-example-basic-single').select2().select2('val',select_val)
      $(".flight_chnge").click(function(){
   var from = $('#from').val();
   var from_loc_id = $('#from_loc_id').val();
	var from_airport_name = $('.airport_name_from').text();
   var from_loc_airport_name = $('#from_loc_airport_name').val();

   var to = $('#to').val();
   var to_loc_id = $('#to_loc_id').val();
   var to_airport_name = $('.airport_name_to').text();
   var to_loc_airport_name = $('#to_loc_airport_name').val();


   $('#from').val(to);
   $('#to').val(from);
   $('.airport_name_from').text(to_airport_name);
   $('.airport_name_to').text(from_airport_name);
   $('#from_loc_airport_name').val(to_loc_airport_name);
   $('#to_loc_airport_name').val(from_loc_airport_name);
  

   $('#from').val(to);
   $('#to').val(from);

   $('#from_loc_id').val(to_loc_id);
   $('#to_loc_id').val(from_loc_id);

   $(".flight_chnge .fa-exchange").toggleClass('rot_arrow');

  });

  //  $('.close-modify-section').click(function(){
  //  		$('.splmodify').slideToggle(400);
  //  		$(this).stop( true, true ).toggleClass('up');
		// $('.search-result').stop( true, true ).toggleClass('flightresltpage');
		// $('.modfictions').stop( true, true ).toggleClass('fixd');
  //  })




});
$( document ).on( 'focus', ':input', function(){
    $( this ).attr( 'autocomplete', 'new-username' );
});
</script>
<?php
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/flight_suggest.js'), 'defer' => 'defer');
?>
