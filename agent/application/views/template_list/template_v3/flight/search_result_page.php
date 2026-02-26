<?php
foreach ($active_booking_source as $t_k => $t_v) {
	$active_source[] = $t_v['source_id'];
}
$active_source = json_encode($active_source);
if($flight_search_params['trip_type'] == 'multicity') {
	$flight_search_params['depature'] = $flight_search_params['depature'][0];
}
if($flight_search_params['trip_type'] == "multicity"){
	$seg_count =count( $flight_search_params['to_loc']);
	$from1 =  explode(", ",$flight_search_params['from'][0]);
	$from = $from1[0].' ('.$flight_search_params['from_loc'][0].')';
	$to1 = explode(", ",$flight_search_params['to'][$seg_count-1]);
	$to = $to1[0].' ('.$flight_search_params['to_loc'][$seg_count-1].')';;
}
else{
	$from1 =  explode(", ",$flight_search_params['from']);
	$from = $from1[0].' ('.$flight_search_params['from_loc'].')';;
	$to1 = explode(", ",$flight_search_params['to']);
	$to = $to1[0].' ('.$flight_search_params['to_loc'].')';;
}
?>
<script>
var search_session_alert_expiry = "<?php echo $GLOBALS ['CI']->config->item ( 'flight_search_session_expiry_alert_period' ); ?>";
var search_session_expiry = "<?php echo $GLOBALS ['CI']->config->item ( 'flight_search_session_expiry_period' ); ?>";
var search_hash = '';
var session_time_out_function_call = 0;

var load_flights = function(){
	$.ajax({
		type: 'GET',
		url: app_base_url+'index.php/ajax/flight_list?booking_source=<?=$active_booking_source[0]['source_id']?>&search_id=<?=$flight_search_params['search_id']?>&op=load',
		async: true,
		cache: true,
		dataType: 'json',
		success: function(res) {
			var dui;
			var r = res;
			dui = setInterval(function(){
			if (typeof(process_result_update) != "undefined" && $.isFunction(process_result_update) == true) {
				clearInterval(dui);
				process_result_update(r);
				search_hash = r.session_expiry_details.search_hash;

				//check_session_time_out(search_hash, r.session_expiry_details.session_start_time); // check session expired or not 
			}
			}, 1);
			$('#onwFltContainer').hide();
		}
	});
}
load_flights();

/*
	append hidden element search_hash for booking form when submiting
*/
$('body').on('submit', '.book-form-wrapper, #multi-flight-form', function(){
	$('<input />').attr('type', 'hidden')
          .attr('name', "search_hash")
          .attr('value', search_hash)
          .appendTo(this);
      return true;
});
</script>
<span class="hide">
	<input type="hidden" id="pri_preferred_currency" value='<?=$this->currency->get_currency_symbol(get_application_currency_preference())?>'>
	<input type="hidden" id="pri_trip_type" value='<?=$is_domestic_one_way_flight?>'>
	<input type="hidden" id="pri_active_source" value='<?=$active_source?>'>
	<input type="hidden" id="pri_search_id" value='<?=$flight_search_params['search_id']?>'>
	<input type="hidden" id="pri_airline_lg_path" value='<?=SYSTEM_IMAGE_DIR.'airline_logo/'?>'>
	<input type="hidden" id="pri_search_params" value='<?=json_encode($flight_search_params)?>'>
	<input type="hidden" name="" id="pri_template_image_path" value="<?=$GLOBALS['CI']->template->template_images()?>">
	<input type="hidden" id="pri_def_curr" value='<?=$this->currency->get_currency_symbol($to_currency)?>'>
</span>
<?php
Js_Loader::$css[] = array('href' => $GLOBALS['CI']->template->template_css_dir('owl.carousel.min.css'), 'media' => 'screen');
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('owl.carousel.min.js'), 'defer' => 'defer');
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/flight_search.js'), 'defer' => 'defer');
//Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('provablib.js'), 'defer' => 'defer');
Js_Loader::$js[] = array('src' => JAVASCRIPT_LIBRARY_DIR.'jquery.jsort.0.4.min.js', 'defer' => 'defer');
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/pax_count.js'), 'defer' => 'defer');
//Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/flight_session_expiry_script.js'), 'defer' => 'defer');
$data['trip_details'] = $flight_search_params;
$data['airline_list'] = $airline_list;//Balu A
$template_images = $GLOBALS['CI']->template->template_images();
$mini_loading_image = '<div class="text-center loader-image"><img src="'.$template_images.'loader_v3.gif" alt="Loading........"/></div>';
$loading_image = '<div class="text-center loader-image"><img src="'.$template_images.'loader_v1.gif" alt="Loading........"/></div>';
$flight_o_direction_icon = '<img src="'.$template_images.'icons/flight-search-result-up-icon.png" alt="Flight Search Result Up Icon">';
echo $GLOBALS['CI']->template->isolated_view('flight/search_panel_summary');
?>
<!-- Page Scripts -->
<section class="search-result onlyfrflty">
	<div class="container-fluid"  id="page-parent">
		<?php //echo $GLOBALS['CI']->template->isolated_view('share/loader/flight_result_pre_loader',$data);?>
		<div class="resultalls">
			<div class="coleft">
				<div class="flteboxwrp">
					<div class="filtersho">
						<div class="avlhtls"><strong id="total_records"> </strong> flight found
						</div>
						<span class="close_fil_box"><i class="fas fa-times"></i></span>
					</div>
					<!-- Refine Search Filters Start -->
					<div class="fltrboxin">
						<div class="celsrch">
								<div class="row_top_fltr">
									<a class="snf_btn pull-left active hide" title="Show Net Fare">
									<span class="fas fa-tag hide"></span>
									<span class="tag_snf hide">SNF</span></a>
									<a class="pull-right reset_filter" id="reset_filters">RESET ALL</a>
								</div>
							<div class="rangebox">
								<button data-target="#collapse501" data-toggle="collapse" class="collapsebtn" type="button">
								Price
								</button>
								<div id="collapse501" class="in">
									<div class="price_slider1">
									<div id="core_min_max_slider_values" class="hide">
										<input type="hiden" id="core_minimum_range_value" value="">
										<input type="hiden" id="core_maximum_range_value" value="">
									</div>
										<p id="amount" class="level"></p>
										<div id="slider-range" class="" aria-disabled="false"></div>
									</div>
								</div>
							</div>
							<div class="septor"></div>
							<div class="rangebox">
								<button data-target="#collapse502" data-toggle="collapse" class="collapsebtn" type="button">
								No. of Stops
								</button>
								<div id="collapse502" class="collapse in">
									<div class="boxins marret" id="stopCountWrapper">
										<a class="stopone toglefil stop-wrapper">
											<input type="checkbox" class="hidecheck stopcount" value="1"></input>
											<div class="starin">
												<div class="stopbig"> 0 <span class="stopsml">stop</span></div>
												<span class="htlcount min-price">-</span>
											</div>
										</a>
										<a class="stopone toglefil stop-wrapper">
											<input type="checkbox" class="hidecheck stopcount" value="2"></input>
											<div class="starin">
												<div class="stopbig"> 1 <span class="stopsml">stop</span></div>
												<span class="htlcount min-price">-</span>
											</div>
										</a>
										<a class="stopone toglefil stop-wrapper">
											<input type="checkbox" class="stopcount hidecheck" value="3"></input>
											<div class="starin">
												<div class="stopbig"> 1+ <span class="stopsml">stop</span></div>
												<span class="htlcount min-price">-</span>
											</div>
										</a>
									</div>
								</div>
							</div>
							<div class="septor"></div>
							<div class="rangebox">
								<button data-target="#collapse503" data-toggle="collapse" class="collapsebtn" type="button">
								Departure Time
								</button>
								<div id="collapse503" class="collapse in">
									<div class="boxins marret" id="departureTimeWrapper">
										<a class="timone toglefil time-wrapper">
											<div class="starin">
											<div class="tmxdv">
											<input type="checkbox" class="time-category hidecheck" value="1"></input>
											<label for="check1" class="ckboxdv">Early Morning</label>
											</div>
											
												<div class="flitsprt mng1"></div>
												<span class="htlcount">12-6AM</span>
											</div>

										</a>
										<a class="timone toglefil time-wrapper">
											<div class="starin">
											<div class="tmxdv">
											<input type="checkbox" class="time-category hidecheck" value="2"></input>
											<label for="check1" class="ckboxdv">Morning</label>
											</div>
												<div class="flitsprt mng2"></div>
												<span class="htlcount">6-12PM</span>
											</div>

										</a>
										<a class="timone toglefil time-wrapper">
											<div class="starin">
											<div class="tmxdv">
											<input type="checkbox" class="time-category hidecheck" value="3"></input>
											<label for="check1" class="ckboxdv">Mid-Day</label>
											</div>
											
												<div class="flitsprt mng3"></div>
												<span class="htlcount">12-6PM</span>
											</div>

										</a>
										<a class="timone toglefil time-wrapper">
											<div class="starin">
											<div class="tmxdv">
											<input type="checkbox" class="hidecheck time-category" value="4"></input>
											<label for="check1" class="ckboxdv">Evening</label>
											</div>
											
												<div class="flitsprt mng4"></div>
												<span class="htlcount">6-12AM</span>
											</div>

										</a>
									</div>
								</div>
							</div>
							<div class="rangebox">
								<button data-target="#collapse504" data-toggle="collapse" class="collapsebtn" type="button">
								Arrival Time
								</button>
								<div id="collapse504" class="collapse in">
									<div class="boxins marret" id="arrivalTimeWrapper">

										<a class="timone toglefil time-wrapper">
											<div class="starin">
											<div class="tmxdv">
											<input type="checkbox" class="time-category hidecheck" value="1"></input>
											<label for="check1" class="ckboxdv">Early Morning</label>
											</div>
											
												<div class="flitsprt mng1"></div>
												<span class="htlcount">12-6AM</span>
											</div>

										</a>

										<a class="timone toglefil time-wrapper">
											<div class="starin">
											<div class="tmxdv">
											<input type="checkbox" class="time-category hidecheck" value="2"></input>
											<label for="check1" class="ckboxdv">Morning</label>
											</div>
											
												<div class="flitsprt mng2"></div>
												<span class="htlcount">6-12PM</span>
											</div>

										</a>

										<a class="timone toglefil time-wrapper">
											<div class="starin">
											<div class="tmxdv">
											<input type="checkbox" class="time-category hidecheck" value="3"></input>
											<label for="check1" class="ckboxdv">Mid-Day</label>
											</div>
											
												<div class="flitsprt mng3"></div>
												<span class="htlcount">12-6PM</span>
											</div>

										</a>

										<a class="timone toglefil time-wrapper">
											<div class="starin">
											<div class="tmxdv">
											<input type="checkbox" class="time-category hidecheck" value="4"></input>
											<label for="check1" class="ckboxdv">Evening</label>
											</div>
											
												<div class="flitsprt mng4"></div>
												<span class="htlcount">6-12AM</span>
											</div>

										</a>

									</div>
								</div>
							</div>
							<div class="septor"></div>
							<div class="rangebox">
								<button data-target="#collapse505" data-toggle="collapse" class="collapsebtn" type="button">
								Airlines
								</button>
								<div id="collapse505" class="collapse in">
									<div class="boxins" id="allairlines">
										
									</div>
								</div>
							</div>
							<div class="septor"></div>
						</div>
					</div>
				</div>
				<!-- Refine Search Filters End -->
			</div>
			<div class="colrit">
				<?php if($flight_search_params['trip_type'] != 'multicity'){ ?>
				<!-- Prev|Next Searcrh Button Starts -->
				<?php
				$core_travel_date = $flight_search_params['depature'];
				$current_date = date('Y-m-d');
				if(strtotime($core_travel_date) > strtotime($current_date)) {
					$prev_day_search_disabled = '';
					$prev_day_search_date = date('d-m-Y', strtotime('-1 day', strtotime($core_travel_date)));
					$prev_day_search_url = base_url().'index.php/flight/add_days_todate?search_id='.$flight_search_params['search_id'].'&new_date='.$prev_day_search_date;
				} else {
					$prev_day_search_url = '';
					$prev_day_search_disabled = ' disabled="disabled" ';
				}
				$next_day_search_date = date('d-m-Y', strtotime('+1 day', strtotime($core_travel_date)));
				$next_day_search_url = base_url().'index.php/flight/add_days_todate?search_id='.$flight_search_params['search_id'].'&new_date='.$next_day_search_date;
				?>
                <div class="prev_next_date hide">
	                <a <?=$prev_day_search_disabled?> href="<?=$prev_day_search_url?>" title="<?=(empty($prev_day_search_date) == false ? app_friendly_absolute_date($prev_day_search_date): 'depart is less thant current date')?>" class="btn_dates"><i class="fal fa-angle-left"></i> Prev Day</a>
                    <span class="datevery">
	                <?=app_friendly_absolute_date($core_travel_date)?>
                    </span>
	                <a href="<?=$next_day_search_url?>" title="<?=(empty($next_day_search_date) == false ? app_friendly_absolute_date($next_day_search_date): 'depart is less than current date')?>" class="btn_dates">Next Day <i class="fal fa-angle-right"></i></a>
                 </div>
               <!-- Prev|Next Searcrh Button Ends -->
               <?php } ?>
				<div class="insidebosc">
				<!-- Fare Calander -->
				<?php //if ($is_domestic_one_way_flight == true) : ?>
				<div class="calandcal" id="fare_calendar_wrapper">
					<div class="col-xs-12 nopad">
						<div class="farenewcal">
							<div class="matrx">
								<div id="farecal" class="owl-carousel matrixcarsl">
								</div>
								</div>
								</div>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-xs-12 flt_seg">
					<span><?php echo $from;?> <strong><i class="fas fa-plane"></i></strong> <?php echo $to;?></span>
				</div>
				<div class="clearfix"></div>
				<?php //endif; // Checking if one way domestic?>
				<!-- Fare Calander End -->
					<!-- Airline Slider Start -->
					<div class="airlinrmatrix hide" id="clone-list-container">
				<div class="inside_shadow_airline">
						<div class="linefstr">
							<div class="airlineall">All Airline</div>
						</div>
						<div class="linescndr">
							<div class="matrx">
								<div id="arlinemtrx" class="owl-carousel matrixcarsl">
								</div>
							</div>
						</div>
					</div>
                    </div>
					<!-- Airline Slider End -->
                    <div class="clearfix"></div>
                    <!--  Current Selection  -->
                    <div class="fixincrmnt hide" id="multi-flight-summary-container">
                        <div class="insidecurent">
                        <div class="col-xs-9 nopad">
                            <div class="col-xs-6 nopad">
                                <div class="selctarln colorretn">
                                    <div class="col-xs-2 nopad flightimage">
                                        <div class="fligthsmll">
                                            <img class="departure-flight-icon" src="<?=$template_images?>airline.png" alt="" />
                                        </div>
                                        <div class="airlinename departure-flight-name">Please Select</div>
                                    </div>
                                    
                                    <div class="col-xs-10 nopad listfull">
                                        <div class="sidenamedesc">
                                        
                                        
                                            <div class="celhtl width80">
                                                <div class="waymensn">
                                                    <div class="flitruo">
                                                        <div class="outbound-details">
                                                        </div>
                                                        <div class="detlnavi outbound-timing-details">
                                                            <div class="col-xs-4 padflt widfty">
                                                                <span class="timlbl departure">---</span>
                                                            </div>
                                                            <div class="col-xs-4 padflt nonefity">
                                                                <div class="lyovrtime">
                                                                    <span class="flect duration">---</span>
                                                                    <span class="flect stop-count">---</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-xs-4 padflt widfty">
                                                                <span class="timlbl arrival text_algn_rit">---</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>  
                                            </div>
                                        </div>
                                    </div>                  
                                </div>
                            </div>
                            <div class="col-xs-6 nopad">
                                <div class="selctarln cloroutbnd">
                                    <div class="col-xs-2 nopad flightimage">
                                        <div class="fligthsmll">
                                            <img class="arrival-flight-icon" src="<?=$template_images?>airline.png" alt="" />
                                        </div>
                                        <div class="airlinename arrival-flight-name">---</div>
                                    </div>
                                    
                                    <div class="col-xs-10 nopad listfull">
                                        <div class="sidenamedesc">
                                        
                                        
                                            <div class="celhtl width80">
                                                <div class="waymensn">
                                                    <div class="flitruo">
                                                        <div class="inbound-details">
                                                        </div>
                                                        <div class="detlnavi inbound-timing-details">
                                                            <div class="col-xs-4 padflt widfty">
                                                                <span class="timlbl departure">---</span>
                                                            </div>
                                                            <div class="col-xs-4 padflt nonefity">
                                                                <div class="lyovrtime">
                                                                    <span class="flect duration">---</span>
                                                                    <span class="flect stop-count">---</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-xs-4 padflt widfty">
                                                                <span class="timlbl arrival text_algn_rit">---</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>  
                                            </div>
                                        </div>
                                    </div>                  
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xs-3 nopad">
                                <div class="sidepricewrp"> 
                                    <div class="col-xs-12 nopad">             		
                                        <div class="sidepricebig">
                                        	<strong class="currency"></strong> <span class="f-p"></span>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 nopad pull-right">
                                        <div class="bookbtn">
											<input type="hidden" id="flight-from-price" value="0">
											<input type="hidden" id="flight-to-price" value="0">
											<form id="multi-flight-form" action="" method="POST">
												<div class="hide" id="trip-way-wrapper"></div>
												<button class="btn-flat booknow" type="submit" id="multi-flight-booking-btn">Book</button>
											</form>
                                        </div>
                                    </div>
                                </div>
                                
                        </div>
                        </div>
                    </div>
        			<!--  Current Selection  End  -->              	
					<div class="clearfix"></div>
					<div class="flt_srt">  
                    <div class="filter_tab"><i class="fas fa-filter"></i> Filters</div>
					 <div class="dropdown recommended flt_rocm" id="top-sort-list-1">
						 <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" id="sort_text_data" value="Price (Highest)">
							Price (Lowest)
                           <!--<span class="caret"></span>-->
                        </button>
                        <button type="button" class="btn btn-primary sorta asc price-h-2-l"  id="sort_text" value="Price (Highest)">
							
                        </button>
                        <div class="dropdown-menu">
                            <div class="form-group">
                                <div class="form-check">
                                  <input class="form-check-input price-l-2-h" data-order="asc" type="radio" name="filter" id="exampleRadios1" value="Price (Lowest)" checked="checked">
									
                                  <label class="form-check-label" for="exampleRadios1">Price</label>
                                </div>  
                            </div>                              
                         
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input departure-l-2-h" data-order="asc" type="radio" name="filter" id="exampleRadios3" value="Early Departure">
									
                                    <label class="form-check-label" for="exampleRadios3">Departure</label>
                                </div> 
                            </div>
                           
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input arrival-l-2-h" data-order="asc" type="radio" name="filter" id="exampleRadios5" value="Early Arrival">
                                    <label class="form-check-label" for="exampleRadios5">Arrival</label>
                                </div> 
                            </div>
                           
                        </div>
                    </div>
                    <?php $sorting_list = '';?>
                    <div class="filterforall addtwofilter hide" id="top-sort-list-wrapper">
                        <div class="topmisty" id="top-sort-list-1">
                            <div class="col-xs-12 nopad">
                            <div class="divinsidefltr">
                                <div class="insidemyt col-xs-12 nopad">
                                <?php 
                                    $sorting_list .= '<ul class="sortul">';
                                        $sorting_list .= '<li class="sortli hide_lines">';
                                         $sorting_list .= '<a class="sorta name-l-2-h loader asc"><i class="fas fa-plane"></i> <strong>Airline</strong></a>';
                                         $sorting_list .= '<a class="sorta name-h-2-l hide loader des"><i class="fas fa-plane"></i> <strong>Airline</strong></a>';
                                         $sorting_list .= '</li>';
                                         $sorting_list .= '<li class="sortli">';
                                         $sorting_list .= '<a class="sorta departure-l-2-h loader asc"><i class="far fa-calendar-alt"></i> <strong>Depart</strong></a>';
                                         $sorting_list .= '<a class="sorta departure-h-2-l hide loader des"><i class="far fa-calendar-alt"></i> <strong>Depart</strong></a>';
                                        $sorting_list .= '</li>';
                                          $sorting_list .= '<li class="sortli hide_lines">';
                                        	$sorting_list .= '<a class="sorta duration-l-2-h loader asc"><i class="far fa-clock"></i> <strong>Duration</strong></a>';
                                            $sorting_list .= '<a class="sorta duration-h-2-l hide loader des"><i class="far fa-clock"></i> <strong>Duration</strong></a>';
                                        $sorting_list .= '</li>';
                                        $sorting_list .= '<li class="sortli">';
                                        	$sorting_list .= '<a class="sorta arrival-l-2-h loader asc"><i class="far fa-calendar-alt"></i> <strong>Arrive</strong></a>';
                                            $sorting_list .= '<a class="sorta arrival-h-2-l hide loader des"><i class="far fa-calendar-alt"></i> <strong>Arrive</strong></a>';
                                        $sorting_list .= '</li>';
                                      
                                        $sorting_list .= '<li class="sortli">';
                                        	$sorting_list .= '<a class="sorta price-l-2-h loader asc"><i class="fas fa-tag"></i> <strong>Price</strong></a>';
                                            $sorting_list .= '<a class="sorta price-h-2-l hide loader des"><i class="fas fa-tag"></i> <strong>Price</strong></a>';
                                        $sorting_list .= '</li>';
                                    $sorting_list .= '</ul>';
                                    echo $sorting_list; ?>
                                </div>
                            </div>
                            </div>
                        </div>
                        <div class="topmisty" id="top-sort-list-2">
                            <div class="col-xs-10 nopad divinsidefltr">
                                <div class="insidemyt">
                                    <?=$sorting_list?>
                                </div>
                            </div>
                        </div>
                    </div>
                	</div>
                    <div class="clearfix"></div>
                    <!-- FLIGHT SEARCH RESULT START -->
					<div class="srch_ldr hide">
					<img src="<?=$GLOBALS['CI']->template->template_images('image_loader.gif')?>" />
					</div>
					<div class="clearfix"></div>
					<div  class="allresult" id="flight_search_result">

					<div class="fl width100 fltRndTripWrap" id="onwFltContainer">
                            <!-- <div class="fl padTB5 width100">
                            </div> -->
                            <div class="fl width100">
                                <div class="card fl width100 marginTB10">

                                    <div class="card-block fl width100 padT20 marginT10 padB10 padLR20">
                                        <div class="col-md-2 col-sm-2 col-xs-2 padT10">
                                            <span class="db padB10 marginR20 marginB10 col-md-8 animated-background"></span>
                                            <span class="db padT10 animated-background col-md-8 marginR20"></span>
                                        </div>
                                        <div class="col-md-7 col-sm-7 col-xs-7 padT10 padLR0 brdRight">
                                            <div class="fl width100">
                                                <div class="col-md-3 col-sm-3 col-xs-3">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-5 col-sm-5 col-xs-5">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-3 padLR0">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-xs-3 col-sm-3 fltPrice">
                                            <div class="col-md-5 col-sm-8 col-xs-8 fr padT10">
                                                <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                <span class="animated-background db padB10 marginR20"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fl width100 padTB10 animated-background"></div>
                                </div>
                            </div>

                            <div class="fl width100">
                                <div class="card fl width100 marginTB10">

                                    <div class="card-block fl width100 padT20 marginT10 padB10 padLR20">
                                        <div class="col-md-2 col-sm-2 col-xs-2 padT10">
                                            <span class="db padB10 marginR20 marginB10 col-md-8 animated-background"></span>
                                            <span class="db padT10 animated-background col-md-8 marginR20"></span>
                                        </div>
                                        <div class="col-md-7 col-sm-7 col-xs-7 padT10 padLR0 brdRight">
                                            <div class="fl width100">
                                                <div class="col-md-3 col-sm-3 col-xs-3">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-5 col-sm-5 col-xs-5">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-3 padLR0">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-xs-3 col-sm-3 fltPrice">
                                            <div class="col-md-5 col-sm-8 col-xs-8 fr padT10">
                                                <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                <span class="animated-background db padB10 marginR20"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fl width100 padTB10 animated-background"></div>
                                </div>

                            </div>

                            <div class="fl width100">
                                <div class="card fl width100 marginTB10">

                                    <div class="card-block fl width100 padT20 marginT10 padB10 padLR20">
                                        <div class="col-md-2 col-sm-2 col-xs-2 padT10">
                                            <span class="db padB10 marginR20 marginB10 col-md-8 animated-background"></span>
                                            <span class="db padT10 animated-background col-md-8 marginR20"></span>
                                        </div>
                                        <div class="col-md-7 col-sm-7 col-xs-7 padT10 padLR0 brdRight">
                                            <div class="fl width100">
                                                <div class="col-md-3 col-sm-3 col-xs-3">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-5 col-sm-5 col-xs-5">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-3 padLR0">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-xs-3 col-sm-3 fltPrice">
                                            <div class="col-md-5 col-sm-8 col-xs-8 fr padT10">
                                                <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                <span class="animated-background db padB10 marginR20"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fl width100 padTB10 animated-background"></div>
                                </div>

                            </div>

                            <div class="fl width100">
                                <div class="card fl width100 marginTB10">

                                    <div class="card-block fl width100 padT20 marginT10 padB10 padLR20">
                                        <div class="col-md-2 col-sm-2 col-xs-2 padT10">
                                            <span class="db padB10 marginR20 marginB10 col-md-8 animated-background"></span>
                                            <span class="db padT10 animated-background col-md-8 marginR20"></span>
                                        </div>
                                        <div class="col-md-7 col-sm-7 col-xs-7 padT10 padLR0 brdRight">
                                            <div class="fl width100">
                                                <div class="col-md-3 col-sm-3 col-xs-3">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-5 col-sm-5 col-xs-5">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-3 padLR0">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-xs-3 col-sm-3 fltPrice">
                                            <div class="col-md-5 col-sm-8 col-xs-8 fr padT10">
                                                <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                <span class="animated-background db padB10 marginR20"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fl width100 padTB10 animated-background"></div>
                                </div>

                            </div>

                            <div class="fl width100">
                                <div class="card fl width100 marginTB10">

                                    <div class="card-block fl width100 padT20 marginT10 padB10 padLR20">
                                        <div class="col-md-2 col-sm-2 col-xs-2 padT10">
                                            <span class="db padB10 marginR20 marginB10 col-md-8 animated-background"></span>
                                            <span class="db padT10 animated-background col-md-8 marginR20"></span>
                                        </div>
                                        <div class="col-md-7 col-sm-7 col-xs-7 padT10 padLR0 brdRight">
                                            <div class="fl width100">
                                                <div class="col-md-3 col-sm-3 col-xs-3">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-5 col-sm-5 col-xs-5">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-3 padLR0">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-xs-3 col-sm-3 fltPrice">
                                            <div class="col-md-5 col-sm-8 col-xs-8 fr padT10">
                                                <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                <span class="animated-background db padB10 marginR20"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fl width100 padTB10 animated-background"></div>
                                </div>

                            </div>

                            <div class="fl width100">
                                <div class="card fl width100 marginTB10">

                                    <div class="card-block fl width100 padT20 marginT10 padB10 padLR20">
                                        <div class="col-md-2 col-sm-2 col-xs-2 padT10">
                                            <span class="db padB10 marginR20 marginB10 col-md-8 animated-background"></span>
                                            <span class="db padT10 animated-background col-md-8 marginR20"></span>
                                        </div>
                                        <div class="col-md-7 col-sm-7 col-xs-7 padT10 padLR0 brdRight">
                                            <div class="fl width100">
                                                <div class="col-md-3 col-sm-3 col-xs-3">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-5 col-sm-5 col-xs-5">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-3 padLR0">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-xs-3 col-sm-3 fltPrice">
                                            <div class="col-md-5 col-sm-8 col-xs-8 fr padT10">
                                                <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                <span class="animated-background db padB10 marginR20"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fl width100 padTB10 animated-background"></div>
                                </div>

                            </div>

                            <div class="fl width100">
                                <div class="card fl width100 marginTB10">

                                    <div class="card-block fl width100 padT20 marginT10 padB10 padLR20">
                                        <div class="col-md-2 col-sm-2 col-xs-2 padT10">
                                            <span class="db padB10 marginR20 marginB10 col-md-8 animated-background"></span>
                                            <span class="db padT10 animated-background col-md-8 marginR20"></span>
                                        </div>
                                        <div class="col-md-7 col-sm-7 col-xs-7 padT10 padLR0 brdRight">
                                            <div class="fl width100">
                                                <div class="col-md-3 col-sm-3 col-xs-3">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-5 col-sm-5 col-xs-5">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-3 padLR0">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-xs-3 col-sm-3 fltPrice">
                                            <div class="col-md-5 col-sm-8 col-xs-8 fr padT10">
                                                <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                <span class="animated-background db padB10 marginR20"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fl width100 padTB10 animated-background"></div>
                                </div>

                            </div>

                            <div class="fl width100">
                                <div class="card fl width100 marginTB10">

                                    <div class="card-block fl width100 padT20 marginT10 padB10 padLR20">
                                        <div class="col-md-2 col-sm-2 col-xs-2 padT10">
                                            <span class="db padB10 marginR20 marginB10 col-md-8 animated-background"></span>
                                            <span class="db padT10 animated-background col-md-8 marginR20"></span>
                                        </div>
                                        <div class="col-md-7 col-sm-7 col-xs-7 padT10 padLR0 brdRight">
                                            <div class="fl width100">
                                                <div class="col-md-3 col-sm-3 col-xs-3">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-5 col-sm-5 col-xs-5">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-3 padLR0">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-xs-3 col-sm-3 fltPrice">
                                            <div class="col-md-5 col-sm-8 col-xs-8 fr padT10">
                                                <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                <span class="animated-background db padB10 marginR20"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fl width100 padTB10 animated-background"></div>
                                </div>

                            </div>

                            <div class="fl width100">
                                <div class="card fl width100 marginTB10">

                                    <div class="card-block fl width100 padT20 marginT10 padB10 padLR20">
                                        <div class="col-md-2 col-sm-2 col-xs-2 padT10">
                                            <span class="db padB10 marginR20 marginB10 col-md-8 animated-background"></span>
                                            <span class="db padT10 animated-background col-md-8 marginR20"></span>
                                        </div>
                                        <div class="col-md-7 col-sm-7 col-xs-7 padT10 padLR0 brdRight">
                                            <div class="fl width100">
                                                <div class="col-md-3 col-sm-3 col-xs-3">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-5 col-sm-5 col-xs-5">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-3 padLR0">
                                                    <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                    <span class="animated-background db padB10 marginR20"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-xs-3 col-sm-3 fltPrice">
                                            <div class="col-md-5 col-sm-8 col-xs-8 fr padT10">
                                                <span class="animated-background db padT10 marginB5 marginR20"></span>
                                                <span class="animated-background db padB10 marginR20"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fl width100 padTB10 animated-background"></div>
                                </div>

                            </div>
                        </div>

					</div>		
                    <div id="npl_img" class="text-center loader-image" loaded="true">
                        <?= '<img src="' . $GLOBALS['CI']->template->template_images('loader_v1.gif') . '" alt="Please Wait" />' ?>
                        <!-- <div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div><div class="bounce4"></div></div> -->
                    </div>
					<div  class="" id="empty_flight_search_result" style="display:none">
                    
                    	<div class="noresultfnd">
                        	<div class="imagenofnd"><img src="<?=$template_images?>empty.jpg" alt="Empty" /></div>
                            <div class="lablfnd">No Result Found!!!</div>
                        </div>
                    
					</div>
					<!-- FLIGHT SEARCH RESULT END -->
				</div>
			</div>
		</div>
	</div>
	<div id="empty-search-result" class="jumbotron container" style="display:none">
		<h1><i class="fas fa-plane"></i> Oops!</h1>
		<p>No flights were found for this route today.</p>
		<p>
		Search results change daily based on availability.If you have an urgent requirement, please get in touch with our call center using the contact details mentioned on the home page. They will assist you to the best of their ability.
		</p>
	</div>

	<?php echo $GLOBALS['CI']->template->isolated_view('share/flight_session_expiry_popup');?>
</section>
<script>
$(document).ready(function() {

	$(".close_fil_box").click(function(){
			$(".coleft").hide();
			$(".resultalls").removeClass("open");
      });

	//************************** **********/
	//airline price sort
	<?php
	$i=1;
	for ($i=1; $i<=2; $i++) ://Multiple Filters Need to be loaded - Balu A
	?>
	$(document).on("click","#top-sort-list-<?=$i?> .price-l-2-h",function() {
		//$("#top-sort-list-<?=$i?> .price-l-2-h").click(function() {
			
			//$(this).addClass('hide');
			var sort_text = $(this).val();
			$("#sort_text_data").text(sort_text);
			$("#sort_text").removeClass('departure-h-2-l');
			$("#sort_text").removeClass('departure-l-2-h');
			$("#sort_text").removeClass('arrival-h-2-l');
			$("#sort_text").removeClass('arrival-l-2-h');
			$("#sort_text").removeClass('price-l-2-h');
			$("#sort_text").addClass('price-h-2-l');
			$("#sort_text").removeClass('des');
			$("#sort_text").addClass('asc');
			$("#sort_text").val('Price (Highest)');
			
			$('#top-sort-list-<?=$i?> .price-h-2-l').removeClass('hide');
			$("#flight_search_result #t-w-i-<?=$i?>").jSort({
				sort_by: '.f-p:first',
				item: '.r-r-i',
				order: 'asc',
				is_num: true
			});
		});
		$(document).on("click","#top-sort-list-<?=$i?> .price-h-2-l",function() {
		//$("#top-sort-list-<?=$i?> .price-h-2-l").click(function() {
			//$(this).addClass('hide');
			var sort_text = $(this).val();
			
			$("#sort_text_data").text(sort_text);
			$("#sort_text").removeClass('departure-h-2-l');
			$("#sort_text").removeClass('departure-l-2-h');
			$("#sort_text").removeClass('arrival-h-2-l');
			$("#sort_text").removeClass('arrival-l-2-h');
			$("#sort_text").removeClass('price-h-2-l');
			$("#sort_text").addClass('price-l-2-h');
			$("#sort_text").removeClass('asc');
			$("#sort_text").addClass('des');
			$("#sort_text").val('Price (Lowest)');
			 
			$('#top-sort-list-<?=$i?> .price-l-2-h').removeClass('hide');
			$("#flight_search_result #t-w-i-<?=$i?>").jSort({
				sort_by: '.f-p:first',
				item: '.r-r-i',
				order: 'desc',
				is_num: true
			});
		});
		//airline name sort
		$("#top-sort-list-<?=$i?> .name-l-2-h").click(function() {
			$(this).addClass('hide');
			$('#top-sort-list-<?=$i?> .name-h-2-l').removeClass('hide');
			$("#flight_search_result #t-w-i-<?=$i?>").jSort({
				sort_by: '.a-n:first',
				item: '.r-r-i',
				order: 'asc',
				is_num: false
			});
		});
		$("#top-sort-list-<?=$i?> .name-h-2-l").click(function() {
			$(this).addClass('hide');
			$('#top-sort-list-<?=$i?> .name-l-2-h').removeClass('hide');
			$("#flight_search_result #t-w-i-<?=$i?>").jSort({
				sort_by: '.a-n:first',
				item: '.r-r-i',
				order: 'desc',
				is_num: false
			});
		});
		//duration sort
		$("#top-sort-list-<?=$i?> .duration-l-2-h").click(function() {
			
			$(this).addClass('hide');
			$('#top-sort-list-<?=$i?> .duration-h-2-l').removeClass('hide');
			$("#flight_search_result #t-w-i-<?=$i?>").jSort({
				sort_by: '.f-d:first',
				item: '.r-r-i',
				order: 'asc',
				is_num: true
			});
		});
		$("#top-sort-list-<?=$i?> .duration-h-2-l").click(function() {
			$(this).addClass('hide');
			$('#top-sort-list-<?=$i?> .duration-l-2-h').removeClass('hide');
			$("#flight_search_result #t-w-i-<?=$i?>").jSort({
				sort_by: '.f-d:first',
				item: '.r-r-i',
				order: 'desc',
				is_num: true
			});
		});
		$(document).on("click","#top-sort-list-<?=$i?> .departure-l-2-h",function() {
		//departure name sort
		//$("#top-sort-list-<?=$i?> .departure-l-2-h").click(function() {
			var sort_text = $(this).val();
			
			$("#sort_text_data").text(sort_text);
			$("#sort_text").removeClass('price-h-2-l');
			$("#sort_text").removeClass('price-l-2-h');
			$("#sort_text").removeClass('arrival-h-2-l');
			$("#sort_text").removeClass('arrival-l-2-h');
			$("#sort_text").removeClass('departure-l-2-h');
			$("#sort_text").addClass('departure-h-2-l');
			$("#sort_text").removeClass('des');
			$("#sort_text").addClass('asc');
			$("#sort_text").val('Late Departure');
			//$(this).addClass('hide');
			$('#top-sort-list-<?=$i?> .departure-h-2-l').removeClass('hide');
			$("#flight_search_result #t-w-i-<?=$i?>").jSort({
				sort_by: '.fdtv:first',
				item: '.r-r-i',
				order: 'asc',
				is_num: true
			});
		});
		$(document).on("click","#top-sort-list-<?=$i?> .departure-h-2-l",function() {
		//$("#top-sort-list-<?=$i?> .departure-h-2-l").click(function() {
			var sort_text = $(this).val();
			$("#sort_text").removeClass('price-h-2-l');
			$("#sort_text").removeClass('price-l-2-h');
			$("#sort_text").removeClass('arrival-h-2-l');
			$("#sort_text").removeClass('arrival-l-2-h');
			$("#sort_text_data").text(sort_text);
			$("#sort_text").removeClass('departure-h-2-l');
			$("#sort_text").addClass('departure-l-2-h');
			$("#sort_text").removeClass('asc');
			$("#sort_text").addClass('des');
			$("#sort_text").val('Early Departure');
			//alert('ett');
			//$(this).addClass('hide');
			$('#top-sort-list-<?=$i?> .departure-l-2-h').removeClass('hide');
			$("#flight_search_result #t-w-i-<?=$i?>").jSort({
				sort_by: '.fdtv:first',
				item: '.r-r-i',
				order: 'desc',
				is_num: true
			});
		});
		$(document).on("click","#top-sort-list-<?=$i?> .arrival-l-2-h",function() {
		//arrival sort
		//$("#top-sort-list-<?=$i?> .arrival-l-2-h").click(function() {
			var sort_text = $(this).val();
			
			$("#sort_text_data").text(sort_text);
			$("#sort_text").removeClass('price-h-2-l');
			$("#sort_text").removeClass('price-l-2-h');
			$("#sort_text").removeClass('departure-h-2-l');
			$("#sort_text").removeClass('departure-l-2-h');
			$("#sort_text").removeClass('arrival-l-2-h');
			$("#sort_text").addClass('arrival-h-2-l');
			$("#sort_text").removeClass('des');
			$("#sort_text").addClass('asc');
			$("#sort_text").val('Late Arrival');
			//$(this).addClass('hide');
			$('#top-sort-list-<?=$i?> .arrival-h-2-l').removeClass('hide');
			$("#flight_search_result #t-w-i-<?=$i?>").jSort({
				sort_by: '.fatv:first',
				item: '.r-r-i',
				order: 'asc',
				is_num: true
			});
		});
		$(document).on("click","#top-sort-list-<?=$i?> .arrival-h-2-l",function() {
		//$("#top-sort-list-<?=$i?> .arrival-h-2-l").click(function() {
			var sort_text = $(this).val();
			
			$("#sort_text_data").text(sort_text);
			$("#sort_text").removeClass('price-h-2-l');
			$("#sort_text").removeClass('price-l-2-h');
			$("#sort_text").removeClass('departure-h-2-l');
			$("#sort_text").removeClass('departure-l-2-h');
			$("#sort_text").removeClass('arrival-h-2-l');
			$("#sort_text").addClass('arrival-l-2-h');
			$("#sort_text").removeClass('asc');
			$("#sort_text").addClass('des');
			$("#sort_text").val('Early Arrival');
			//$(this).addClass('hide');
			$('#top-sort-list-<?=$i?> .arrival-l-2-h').removeClass('hide');
			$("#flight_search_result #t-w-i-<?=$i?>").jSort({
				sort_by: '.fatv:first',
				item: '.r-r-i',
				order: 'desc',
				is_num: true
			});
		});
	<?php
	endfor;
	//End Multiple Filter Looping - Balu A
	?>

	//if ($(window).width() < 550) {
//			$(document).on("click",".madgrid",function(){
//				var search_id = $(this).data('key');
//				$( "#form-id-"+ search_id).submit();
//			});
//			
//		}	

	//show/hide net fare	
	$('.snf_btn').click(function(){
		$(this).toggleClass('active');
		$('.net-fare-tag').toggle();
		var title = 'Show Net Fare' ;
		if( $(this).hasClass('active')){
		   title = 'Show Net Fare';
		}
		else{
			title = 'Hide Net Fare';
		}
		$(this).attr('title', title);
		$('.tag_snf', this).text(function(i, text){
			return text === "SNF" ? "HNF" : "SNF";
		});
	});
});
</script>
<?php
echo $this->template->isolated_view('share/media/flight_search');
?>
