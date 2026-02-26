
<?php
//debug($search_params);exit;
$template_images = $GLOBALS['CI']->template->template_images();
$mini_loading_image = '<div class="text-center loader-image">Please Wait</div>';
$no_of_nights = $search_params['data']['no_of_nights'];
$night_text = 'Night';
if($no_of_nights > 1){
	$night_text = 'Nights';
}
foreach ($raw_hotel_list['HotelSearchResult']['HotelResults'] as $hd_key=> $hd) {
	if($hd['booking_source']=='PTBSID0000000011'){

		$hd['Images'][]=$hd['HotelPicture'];
	//	debug($hd['Images']);die("inside");
	}
	
	$images = array();
	$i = 0;
	if(valid_array($hd['Images'])){
// echo "dddsd";die;
		foreach($hd['Images'] as $image_key => $image_list){
			if($i < 5){
				if (file_get_contents($image_list) !== false) {
					$images[] = $image_list;
					$i++;
				}
				
			}
		}
			
	}
	//debug($hd);exit;
	$current_hotel_rate = $hd['StarRating'];
	$hotel_code = preg_replace("/[^a-zA-Z0-9]/", "",$hd['HotelCode']);
	//check image exists in that url or not
	//$file_headers = @get_headers($hd['HotelPicture']);
	$image_found=1;
	// if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	// 	$image_found = 0;
	// }
?>
<div class="rowresult r-r-i item">
	<div class="madgrid forhtlpopover shapcs" id="result_<?=$hd_key?>" data-key="<?=$hd_key?>" data-hotel-code="<?=$hotel_code?>" data-access-key="<?=@$hd['Latitude'].'_'.@$hd['Longitude']?>">
		<div class="col-xs-4 nopad listimage full_mobile">
			<div class="imagehtldis">
				<!-- static slider start -->
				<span class="lux_exc">Lux Exclusive</span>
				<span class="sld_cnt">1/<?php echo count($images)?></span>
				<div id="myCarousel<?php echo $hd_key;?>" class="carousel slide" data-ride="carousel">
					<div class="carousel-inner">
					  <?php if(valid_array($images)){
						//debug($images);exit;
					  	foreach($images as $img_key => $image){
							//if (file_exists($image)) {
								if($img_key == 0){
									$cls_active = 'active';
								}
								else{
									$cls_active = '';
								}
					  ?>
				    <div class="item <?php echo $cls_active;?>">
				      <img src="<?=$image?>" alt="img/jpg" />
				    </div>
					  <?php } }  ?>
						<?php //} ?>

				   
				  </div>
				  <!--<div class="carousel-inner">
					  
				    <div class="item active">
				      <img src="<?=$GLOBALS['CI']->template->domain_images('htl_srch1.jpg')?>" alt="img/jpg" />
				    </div>

				    <div class="item">
				      <img src="<?=$GLOBALS['CI']->template->domain_images('htl_srch1.jpg')?>" alt="img/jpg" />
				    </div>

				    <div class="item">
				      <img src="<?=$GLOBALS['CI']->template->domain_images('htl_srch1.jpg')?>" alt="img/jpg" />
				    </div>
				  </div>-->

				  <!-- Left and right controls -->
				  <a class="left carousel-control" href="#myCarousel<?php echo $hd_key;?>" data-slide="prev">
				    <span class="glyphicon glyphicon-chevron-left"></span>
				    <span class="sr-only">Previous</span>
				  </a>
				  <a class="right carousel-control" href="#myCarousel<?php echo $hd_key;?>" data-slide="next">
				    <span class="glyphicon glyphicon-chevron-right"></span>
				    <span class="sr-only">Next</span>
				  </a>
					<span class="hide h-loc"><?php echo $hd['HotelLocation'];?></span>
				</div>
				<?php 
                                $search_id = intval($attr['search_id']);	
                                if($hd['HotelPicture']&&$image_found==true):?>
<?php  ?>
<!-- <img src="" alt="Hotel img" data-src="<?php //echo base_url().'index.php/hotel/image_cdn/'.$hd['ResultIndex'].'/'.$search_id.'/'.base64_encode($hd['HotelCode'])?>" class="lazy h-img"> -->
					<img src="<?=$hd['HotelPicture']?>" alt="Hotel Image" data-src="<?=$hd['HotelPicture']?>" class="lazy h-img load-image hide">

					<img src="<?=$GLOBALS['CI']->template->template_images('image_loader.gif')?>" class='loader-image'>

					<!-- <img src="" alt="Hotel img" data-src="<?php echo base_url().'index.php/hotel/image_hide/'.$hd['ResultIndex'].'/'.$search_id?>" class="lazy h-img"> -->

				<?php else:?>
					<img src="<?=$hd['HotelPicture']?>" alt="Hotel Image" data-src="<?=$GLOBALS['CI']->template->template_images('default_hotel_img.jpg') ?>" class="lazy h-img">
				<?php endif;?>
				<?php
					/**
					 * HOTEL PRICE SECTION With Markup price will be returned
					 * 
					 */
					 //Getting RoomPrice from API per night wise					
									
					//$RoomPrice					= round($hd['Price']['RoomPrice']/$no_of_nights);
					$RoomPrice					= $hd['Price']['RoomPrice'];
					//debug(getimagesize($hd['HotelPicture']));
					?>
				<!-- <img src="" alt="Hotel img" data-src="<?//=$hd['HotelPicture'] ?>" class="lazy h-img"> -->
				<?php if($hd['HotelPicture']&&$image_found==true):?>
				<a data-target="map-box-modal" data-result-token="<?=urlencode($hd['ResultToken'])?>" data-booking-source="<?=urlencode($booking_source)?>" data-price="<?=$RoomPrice?>" data-star-rating="<?=$current_hotel_rate?>"  data-hotel-name="<?php echo $hd['HotelName']?>" id="map_id_<?=str_replace("!","H",$hd['HotelCode'])?>" data-trip-url="<?=$hd['trip_adv_url']?>" data-trip-rating="<?=$hd['trip_rating']?>" data-id="<?=str_replace("!","H",$hd['HotelCode'])?>" class="hotel-image-gal mapviewhtlhotl fal fa-image view-photo-btn" data-hotel-code="<?=$hd['HotelCode']?>"></a> 
				<?php endif;?>
				<a class="hotel_location" data-lat="<?=@$hd['Latitude']?>" data-lon="<?=@$hd['Longitude']?>"></a>
				
			</div>
		</div>
		<div class="col-xs-8 nopad listfull full_mobile">
			<div class="sidenamedesc">
				<div class="celhtl width70">
					<div class="innd">
					   <div class="imptpldz">
						<div class="property-type" data-property-type="hotel"></div>
						<div class="shtlnamehotl">
							 <span class="h-name"><?php echo $hd['HotelName']?></span> 
						</div>
						<span class="result_token hide"><?=urlencode($hd['ResultToken'])?></span>
						<div class="starrtinghotl rating-no">
								<span class="h-sr hide"><?php echo $current_hotel_rate?></span>
								<?php echo print_star_rating($current_hotel_rate);?>
						</div>
						<div class="adreshotle h-adr"><i class="fas fa-map-marker-alt"></i><?php echo $hd['HotelAddress']?>
						</div>
						<div class="clearfix"></div>
						<div class="rtng_rvw rating-no">
							<span class="str_in str_numbr"><span class="star 1 active"><label><?php echo $current_hotel_rate;?></label></span><!--<strong>Excellent</strong>--></span>
							<!-- <span class="str_in"><?php echo print_star_rating($current_hotel_rate);?>
							</span> -->
							<?php if(isset($hotel_reviews[$hotel_code])){ 
								if($hotel_reviews[$hotel_code] > 0){?>
							<span class="rvw"><a href="<?php echo base_url().'index.php/hotel/hotel_reviews/'.$hotel_code;?>" target="_blank"><?php echo $hotel_reviews[$hotel_code];?> Reviews</a></span>
							<?php }} ?>
						</div>
						<div class="bothicntri">
						<div class="mwifdiv">
                           <ul class="htl_spr">                         
				         	<?php if(isset($hd['HotelAmenities'])):?>
				         		<?php if($hd['HotelAmenities']):?>
				         			<?php
				         				//debug($hd['HotelAmenities']);
				         			   	$in_search_params = "".strtolower('wireless')."";
										$in_input = preg_quote(@$in_search_params, '~'); // don't forget to quote input string!
										$internet_result = preg_grep('~' . $in_input . '~', $hd['HotelAmenities']);
										$inn_search_params = "Wi-Fi";
										$inn_input = preg_quote(@$inn_search_params, '~'); 
										$innternet_result = preg_grep('~' . $inn_input . '~', $hd['HotelAmenities']);

										//checking free wifi
										
										$wf_search_params = "Wi";
										$wf_input = preg_quote(@$wf_search_params, '~'); 
										$wf_result = preg_grep('~' . $wf_input . '~', $hd['HotelAmenities']);

										$b_search_params = "".strtolower('breakfast')."";
										$b_input = preg_quote(@$b_search_params, '~'); 
										$b_result = preg_grep('~' . $b_input . '~', $hd['HotelAmenities']);
										//checking breakfast 
										$bf_search_params = "Breakfast";
										$bf_input = preg_quote(@$bf_search_params, '~'); 
										$bf_result = preg_grep('~' . $bf_input . '~', $hd['HotelAmenities']);

										$p_search_params = "".strtolower('parking')."";
										$p_input = preg_quote(@$p_search_params, '~'); 
										$p_result = preg_grep('~' . $p_input . '~', $hd['HotelAmenities']);
										//car parking
										$cp_search_params = "".strtolower('park')."";
										$cp_input = preg_quote(@$cp_search_params, '~'); 
										$cp_result = preg_grep('~' . $cp_input . '~', $hd['HotelAmenities']);

										$s_search_params = "pool";
										$s_input = preg_quote(@$s_search_params, '~'); 
										$s_result = preg_grep('~' . $s_input . '~', $hd['HotelAmenities']);
										$swim = "Swim";
								
										$sw_input = preg_quote(@$swim, '~'); 
										$sw_result = preg_grep('~' . $sw_input . '~', $hd['HotelAmenities']);
				         			?>
				         				<?php if($internet_result||$innternet_result|| $wf_result):?>
				         					<li class="wf" data-toggle="tooltip" data-placement="top" title="Wifi"><span>Wifi</span></li>
				         					<input type="hidden" value="filter" id="wifi" class="wifi" data-wifi="1">
				         				<?php else:?>
				         					<input type="hidden" value="filter" id="wifi" class="wifi" data-wifi="0">
				         				<?php endif;?>
				         				<?php if($b_result||$bf_result):?>
				         					<li class="bf" data-toggle="tooltip" data-placement="top" title="Breakfast"><span>Breakfast</span></li>
				         					<input type="hidden" value="filter" id="breakfast" class="breakfast" data-breakfast="1">
				         				<?php else:?>
				         					<input type="hidden" value="filter" id="breakfast" class="breakfast" data-breakfast="0">
				         				<?php endif;?>
				         				<?php if($p_result || $cp_result):?>
				         						 <li class="pr" data-toggle="tooltip" data-placement="top" title="Parking"><span>Parking</span></li>
		         						 		<input type="hidden" value="filter" id="parking" data-parking ="1" class="parking">
		         						<?php else:?>
		         								<input type="hidden" value="filter" id="parking" class="parking" data-parking="0">
				         				<?php endif;?>
				         				<?php if($s_result||$sw_result):?>
				         						 <li class="sf" data-toggle="tooltip" data-placement="top" title="Swimming pool"><span>Swimming pool</span></li>
				         						 <input type="hidden" value="filter" id="pool" class="pool" data-pool="1">
				         				<?php else:?>
				         					 <input type="hidden" value="filter" id="pool" class="pool" data-pool="0">
				         				<?php endif;?>			         			 
				         			<?php else:?>
				         				<input type="hidden" value="filter" id="wifi" class="wifi" data-wifi="0">
						         		<input type="hidden" value="filter" id="breakfast" class="breakfast" data-breakfast="0">
						         		<input type="hidden" value="filter" id="parking" class="parking" data-parking="0">
						         		<input type="hidden" value="filter" id="pool" class="pool" data-pool="0">
				         		<?php endif;?>
				         	<?php else:?>
				         		<input type="hidden" value="filter" id="wifi" class="wifi" data-wifi="0">
				         		<input type="hidden" value="filter" id="breakfast" class="breakfast" data-breakfast="0">
				         		<input type="hidden" value="filter" id="parking" class="parking" data-parking="0">
				         		<input type="hidden" value="filter" id="pool" class="pool" data-pool="0">
				         	<?php endif;?>
                           
                           </ul>
						</div>

						<?php if(isset($hd['trip_adv_url'])&&empty($hd['trip_adv_url'])==false):?>
						  <div class="tripad">
						    <a href="#"><img src="<?=$hd['trip_adv_url']?>"></a>
						    <span>Rating <?=$hd['trip_rating']?></span>
						  </div>
						<?php endif;?>
						 </div>
						</div>
						<div class="maprew">
						  <div class="hoteloctnf">
						  <a href="<?php echo base_url().'index.php/hotel/map?lat='.$hd['Latitude'].'&lon='.$hd['Longitude'].'&hn='.urlencode($hd['HotelName']).'&sr='.intval($hd['StarRating']).'&c='.urlencode($hd['HotelLocation']).'&price='.$RoomPrice.'&img='.urlencode($hd['HotelPicture'])?>" class="location-map  fa fa-map-marker" target="map_box_frame" data-key="<?=$hd_key?>" data-hotel-code="<?=$hotel_code?>" data-star-rating="<?=$hd['StarRating']?>" data-hotel-name="<?=$hd['HotelName']?>" id="location_<?=$hotel_code?>_<?=$hd_key?>" data-toggle="tooltip" data-placement="top" data-original-title="View Map"></a>
						   </div>
						  
						</div>
					</div>
				</div>
				
				<div class="celhtl width30">
					<div class="sidepricewrp">
				
						<div class="priceflights">
							<div class="prc_in">
							<strong> <?php echo $currency_obj->get_currency_symbol($currency_obj->to_currency); ?> </strong>
							<span class="h-p"><?php echo roundoff_number($RoomPrice); ?></span>
							<div class="prcstrtingt">/suite</div>
						</div>
						<div class="nghts"><?php echo $no_of_nights;?> <?php echo $night_text;?></div>
						</div>
						<form action="<?php echo base_url().'index.php/hotel/hotel_details/'.($search_id)?>">
						
							<input type="hidden" id="mangrid_id_<?=$hd_key?>_<?=$hotel_code?>" value="<?=urlencode($hd['ResultToken'])?>" name="ResultIndex"  data-key="<?=$hd_key?>" data-hotel-code="<?=$hotel_code?>" class="result-index">


							<input type="hidden" id="booking_source_<?=$hd_key?>_<?=$hotel_code?>" value="<?=urlencode($booking_source)?>" name="booking_source"  data-key="<?=$hd_key?>" data-hotel-code="<?=$hotel_code?>" class="booking_source">
							<input type="hidden" value="get_details" name="op" class="operation">
							<button class="confirmBTN b-btn bookallbtn splhotltoy" type="submit">View Offer</button>
							<div class="preclsdv">
								<?php if(isset($hd['Free_cancel_date'])):?>
									<?php if($hd['Free_cancel_date']):?>
							 		 <span class="canplyto"><i class="fa fa-check" aria-hidden="true"></i> Free Cancellation till:<b><?=local_month_date($hd['Free_cancel_date']);?></b></span>
							 		 <input type="hidden" class="free_cancel" type="text" value="1" data-free-cancel="1">
							 		<?php else:?>
							 			<input type="hidden" class="free_cancel" type="text" value="0" data-free-cancel="0">
							 		<?php endif;?>
							 	<?php else:?>
							 		<input type="hidden" data-free-cancel="0" class="free_cancel" type="text" value="0">
								<?php endif;?>

							</div>
						</form>
						<div class="viewhotlrmtgle hide">
							<button class="vwrums room-btn" type="button">View Rooms</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<!-- <form class="room-form hide">
			<input type="hidden" value="<?=urlencode($hd['ResultToken'])?>" name="ResultIndex" class="result-index">
			<input type="hidden" value="<?=urlencode($booking_source)?>" name="booking_source" class="booking_source">
			<input type="hidden" name="op" value="get_room_details">
			<input type="hidden" name="search_id" value="<?=$search_id?>">
		</form> -->
		
		<div class="clearfix"></div>
		<div class="room-list" style="display:none">
			<div class="room-summ romlistnh">
				<?=$mini_loading_image?>
			</div>
		</div>
		<?php
			//echo $hd['HotelPromotion'];
			if (isset($hd['HotelPromotion']) == true and empty($hd['HotelPromotion']) == false) {?>	
				<div class="gift-tag">
		          <span class="offdiv deal-status" data-deal="<?php echo ACTIVE?>"><?=$hd['HotelPromotion']?>% Off</span>
		        </div>
			<?php } else {?>
				<span class="deal-status hide" data-deal="<?php echo INACTIVE?>"></span>
				<?php
		}?>
	</div>


</div>

<?php

}
?>
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();  
});
</script>
<strong class="currency_symbol hide" > <?php echo $currency_obj->get_currency_symbol($currency_obj->to_currency); ?> </strong>
<script type="text/javascript">
	$(document).ready(function(){
		var default_loader = "<?=$GLOBALS['CI']->template->template_images('image_loader.gif')?>";
		//console.log("default_loader"+default_loader);
		//$(".load-image").attr('src',default_loader);

		setTimeout(function(){
			$(".load-image").removeClass('hide');
			$(".loader-image").addClass('hide');
			//loader-image
		},3000);

	});
</script>

