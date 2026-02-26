<?php
$max_multicity_segments = 5;
if (!empty($multicity_segment_search_params['from_loc']) && is_array($multicity_segment_search_params['from_loc'])) {
    $multicity_segment_count = count($multicity_segment_search_params['from_loc']);
} else {
    $multicity_segment_count = 2;
}
$multi_flight_datepicker = array();
for($i=1;$i<=$max_multicity_segments; $i++){
	$multi_flight_datepicker[$i] = array('m_flight_datepicker'.$i, FUTURE_DATE_DISABLED_MONTH);
}
$this->current_page->set_datepicker($multi_flight_datepicker);
?>
<span class="hide">
	<input type="hidden" id="max_multicity_segments" value="<?=$max_multicity_segments?>">
	<input type="hidden" id="multicity_segment_count" value="<?=$multicity_segment_count?>">
</span>

<div id="multi_way_fieldset" class="col-md-9 nopad" style="display: none">

	<?php for($multi_k=1;$multi_k<=$max_multicity_segments; $multi_k++){ 
			if (intval($multi_k) > $multicity_segment_count) {
				$segment_visibility = 'display:none';
			} else {
				$segment_visibility = '';
			}
	?>
			<div class="multi_city_container" id="multi_city_container_<?=$multi_k?>" style="<?=$segment_visibility?>">
				<div class="col-md-8 col-xs-9 nopad placerows mfulwdth">
				<div class="col-xs-6 padfive br_rt"> 
				 <?php //if($multi_k == 1) { ?>
					<div class="lablform">From</div>
                    <?php //} ?>
					<div class="plcetogo deprtures sidebord">
						<input type="text" autocomplete="off" name="from[]" class="m_depcity m_arr_c  normalinput auto-focus valid_class fromflight form-control b-r-0" id="m_from<?=$multi_k?>" placeholder="Type Departure City" value="<?php echo @$multicity_segment_search_params['from'][$multi_k-1]?>" required />
						<input class="hide loc_id_holder" name="from_loc_id[]" type="hidden" value="<?=@$multicity_segment_search_params['from_loc_id'][$multi_k-1]?>" >
						<span class="airport_value"></span>
					</div>
				</div>
				<div class="col-xs-6 padfive br_rt">
                <?php //if($multi_k == 1) { ?>
					<div class="lablform">To</div>
                    <?php //} ?>
					<div class="plcetogo destinatios sidebord">
						<input type="text" autocomplete="off" name="to[]"  class="m_arrcity m_arr_c normalinput auto-focus valid_class departflight form-control b-r-0" id="m_to<?=$multi_k?>" placeholder="Type Destination City" value="<?php echo @$multicity_segment_search_params['to'][$multi_k-1] ?>" required/>
						<input class="hide loc_id_holder" name="to_loc_id[]" type="hidden" value="<?=@$multicity_segment_search_params['to_loc_id'][$multi_k-1]?>" >
						<span class="airport_value"></span>
					</div>
				</div>
				</div>
				<div class="col-md-4 col-xs-3 nopad secndates mfulwdth">
					<div class="col-xs-12 padfive br_rt">
	                    <?php //if($multi_k == 1) { ?>
							<div class="lablform">Departure</div>
                        <?php //} ?>
						<div class="plcetogo datemark sidebord">
							<input type="text" name="depature[]" class="m_depature_date normalinput auto-focus hand-cursor form-control b-r-0 disable-date-auto-update" id="m_flight_datepicker<?=$multi_k?>" placeholder="Select Date" value="<?php echo @$multicity_segment_search_params['depature'][$multi_k-1] ?>" readonly required/>
							<span class="flt_day checkInDayDivMulti<?=$multi_k?>"><?php if(empty($multicity_segment_search_params['depature'][$multi_k-1]) == false){echo date("l", strtotime(@$multicity_segment_search_params['depature'][$multi_k-1]));}?></span>
						</div>
					</div>
                    <?php if($multi_k > 2) { ?>
	                    <button class="city_close_btn remove_city" value="<?php echo $multi_k-1;?>"> <span class="fa fa-times"></span></button>
					<?php } if($multi_k > 1 && $multi_k < 5) { ?>
					  
					<button class="add_city_btn add_city<?php echo $multi_k;?>" value="<?php echo $multi_k;?>"> <span class="fa fa-plus"></span> Add City</button>

				
                    <?php } ?>
                    
				</div>
                
			</div>
	<?php }?>
</div>
