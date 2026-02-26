<?php
error_reporting(E_ALL);
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/hotel_details_slider.js'), 'defer' => 'defer');
$booking_url = $GLOBALS['CI']->hotel_lib->booking_url($hotel_search_params['search_id']);


$mini_loading_image = '<div class="text-center loader-image"><img src="' . $GLOBALS['CI']->template->template_images('loader_v3.gif') . '" alt="Loading........"/></div>';
$loading_image = '<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div><div class="bounce4"></div></div>';
$_HotelDetails = $hotel_details['HotelInfoResult']['HotelDetails'];
$page_data['HotelCode'] = $_HotelDetails['HotelCode'];
//debug($_HotelDetails);exit;
$sanitized_data['HotelCode'] = $_HotelDetails['HotelCode'];
$sanitized_data['HotelName'] = $_HotelDetails['HotelName'];
$sanitized_data['StarRating'] = $_HotelDetails['StarRating'];
$sanitized_data['Description'] = $_HotelDetails['Description'];
$sanitized_data['Attractions'] = (isset($_HotelDetails['Attractions']) ? $_HotelDetails['Attractions'] : false);
$sanitized_data['HotelFacilities'] = (isset($_HotelDetails['HotelFacilities']) ? $_HotelDetails['HotelFacilities'] : false);
$sanitized_data['HotelPolicy'] = (isset($_HotelDetails['HotelPolicy']) ? $_HotelDetails['HotelPolicy'] : false);
$sanitized_data['SpecialInstructions'] = (isset($_HotelDetails['SpecialInstructions']) ? $_HotelDetails['SpecialInstructions'] : false);
$sanitized_data['Address'] = (isset($_HotelDetails['Address']) ? $_HotelDetails['Address'] : false);
$sanitized_data['PinCode'] = (isset($_HotelDetails['PinCode']) ? $_HotelDetails['PinCode'] : false);
$sanitized_data['HotelContactNo'] = (isset($_HotelDetails['HotelContactNo']) ? $_HotelDetails['HotelContactNo'] : false);
$sanitized_data['Latitude'] = (isset($_HotelDetails['Latitude']) ? $_HotelDetails['Latitude'] : 0);
$sanitized_data['Longitude'] = (isset($_HotelDetails['Longitude']) ? $_HotelDetails['Longitude'] : 0);
$sanitized_data['RoomFacilities'] = (isset($_HotelDetails['RoomFacilities']) ? $_HotelDetails['RoomFacilities'] : false);
$sanitized_data['Images'] = $_HotelDetails['Images'];
$hotel_contact_numbers  = "Not Available";
if($sanitized_data['HotelContactNo']){
	$hotel_contact_numbers = implode(",",$sanitized_data['HotelContactNo']);
}
if ($sanitized_data['Images']) {
    $sanitized_data['Images'] = $sanitized_data['Images'];
} else {
    $sanitized_data['Images'] = $GLOBALS['CI']->template->template_images('default_hotel_img.jpg');
}
$description = array();
if(empty($sanitized_data['Description']) == false){
	$description = explode(". ", $sanitized_data['Description']);
}
//debug($sanitized_data['HotelFacilities']);

$popular_facillities = array();
$facilities = array();
if(valid_array($sanitized_data['HotelFacilities'])){
	 foreach ($sanitized_data['HotelFacilities'] as $ak => $av) {
		 $facilities[] = strtolower($av);
	 }
}
if(valid_array($facilities)){
$b_search_params = "breakfast";
$b_input = preg_quote(@$b_search_params, '~');
$b_result = preg_grep('~' . $b_input . '~', $facilities);
$b_result1 = preg_grep('~' . $b_input, $facilities);
$b_result2 = preg_grep($b_inputt . '~', $facilities);
if ($b_result || $b_result1 || $b_result2) {
	$popular_facillities [] = 'breakfast';
}
$b_search_params = "lunch";
$b_input = preg_quote(@$b_search_params, '~');
$b_result = preg_grep('~' . $b_input . '~', $facilities);
$b_result1 = preg_grep('~' . $b_input, $facilities);
$b_result2 = preg_grep($b_inputt . '~', $facilities);
if ($b_result || $b_result1 || $b_result2) {
	$popular_facillities [] = 'lunch';
}

//debug($sanitized_data['HotelFacilities']);
$b_search_params = "bar";
$b_input = preg_quote(@$b_search_params, '~');
$b_result = preg_grep('~' . $b_input . '~', $facilities);
$b_result1 = preg_grep('~' . $b_input, $facilities);
$b_result2 = preg_grep($b_inputt . '~', $facilities);
if ($b_result || $b_result1 || $b_result2) {
	$popular_facillities [] = 'bar';
}
$b_search_params = "spa";
$b_input = preg_quote(@$b_search_params, '~');
$b_result = preg_grep('~' . $b_input . '~', $facilities);
$b_result1 = preg_grep('~' . $b_input, $facilities);
$b_result2 = preg_grep($b_inputt . '~', $facilities);
if ($b_result || $b_result1 || $b_result2) {
	$popular_facillities [] = 'spa';
}
$b_search_params = "tea";
$b_input = preg_quote(@$b_search_params, '~');
$b_result = preg_grep('~' . $b_input . '~', $facilities);
$b_result1 = preg_grep('~' . $b_input, $facilities);
$b_result2 = preg_grep($b_inputt . '~', $facilities);
if ($b_result || $b_result1 || $b_result2) {
	$popular_facillities [] = 'tea';
}
$b_search_params = "pool";
$b_input = preg_quote(@$b_search_params, '~');
$b_result = preg_grep('~' . $b_input . '~', $facilities);
$b_result1 = preg_grep('~' . $b_input, $facilities);
$b_result2 = preg_grep($b_inputt . '~', $facilities);
if ($b_result || $b_result1 || $b_result2) {
	$popular_facillities [] = 'swimming pool';
}
$b_search_params = "gym";
$b_input = preg_quote(@$b_search_params, '~');
$b_result = preg_grep('~' . $b_input . '~', $facilities);
$b_result1 = preg_grep('~' . $b_input, $facilities);
$b_result2 = preg_grep($b_inputt . '~', $facilities);
if ($b_result || $b_result1 || $b_result2) {
	$popular_facillities [] = 'gym';
}
$b_search_params = "wifi";
$b_input = preg_quote(@$b_search_params, '~');
$b_result = preg_grep('~' . $b_input . '~', $facilities);
$b_result1 = preg_grep('~' . $b_input, $facilities);
$b_result2 = preg_grep($b_inputt . '~', $facilities);
if ($b_result || $b_result1 || $b_result2) {
	$popular_facillities [] = 'complimentary Wi-Fi';
}
}

//debug($popular_facillities);exit;
$star_rating = $sanitized_data['StarRating'];
$trip_text = '';
if ($star_rating == 3 || $star_rating == 3.5) {
	$trip_text = 'Average';
} elseif ($star_rating == 4) {
	$trip_text = 'Good';
} elseif ($star_rating == 4.5 || $star_rating == 5) {
	$trip_text = 'Excellent';
} elseif ($star_rating == 2 || $star_rating == 2.5) {
	$trip_text = 'Bad';
} elseif ($star_rating < 2) {
	$trip_text = 'Very Bad';
}
                        
//debug($_HotelDetails);exit;
$free_cancellation = 'Non-Refundable';
if ($_HotelDetails['first_rm_cancel_date']){
	$now = strtotime(date('Y-m-d'));// or your date as well
    $your_date = strtotime($_HotelDetails['first_rm_cancel_date']);
    $datediff = $your_date - $now;
    $free_cancellation = abs(round($datediff / (60 * 60 * 24))).' days Free Concellation';
}
Js_Loader::$css[] = array('href' => $GLOBALS['CI']->template->template_css_dir('owl.carousel.min.css'), 'media' => 'screen');
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('owl.carousel.min.js'), 'defer' => 'defer');

$base_url_image=base_url().'index.php/hotel/image_details_cdn';

//debug($_HotelDetails);exit;
?>

<?php
/**
 * Application VIEW
 */
// echo $GLOBALS['CI']->template->isolated_view('hotel/search_panel_summary');
?>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCJfvWH36KY3rrRfopWstNfduF5-OzoywY"></script>
<script type="text/javascript">
    /** Google Maps **/
    var myCenter = new google.maps.LatLng(<?= floatval($sanitized_data['Latitude']) ?>,<?= floatval($sanitized_data['Longitude']); ?>);
    var image_url = "<?php echo $GLOBALS['CI']->template->template_images() ?>";
    var hotel_name = "<?php echo $sanitized_data['HotelName'] ?>";
    /*function initialize()
     {
     var mapProp = {
     center:myCenter,
     zoom:10,
     mapTypeId:google.maps.MapTypeId.ROADMAP
     
     };
     
     var map = new google.maps.Map(document.getElementById("Map"), mapProp);
     
     var marker = new google.maps.Marker({
     position:myCenter,
     
     
     });
     
     marker.setMap(map);
     
     var infowindow = new google.maps.InfoWindow({
     content:hotel_name
     });
     
     google.maps.event.addListener(marker, "click", function() {
     infowindow.open(map, marker);
     });
     }
     google.maps.event.addDomListener(window, "load", initialize);*/
</script>
<div class="clearfix"></div>
<input type="hidden" id="latitude" value="<?= $sanitized_data['Latitude'] ?>">
<input type="hidden" id="longitude" value="<?= $sanitized_data['Longitude'] ?>">
<input type="hidden" id="api_base_url" value="<?= $GLOBALS['CI']->template->template_images('marker/green_hotel_map_marker.png') ?>">
<input type="hidden" id="hotel_name" value="<?php echo $sanitized_data['HotelName'] ?>">
<div class="htl_detl">
    <div id="myCarousel" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
			<?php if (valid_array($sanitized_data['Images']) == true) { 
				  foreach ($sanitized_data['Images'] as $i_k => $i_v) { 
                    if(is_array($i_v)){
                       foreach($i_v as $k=>$test){
                        $i_v=$test;
                       }
                    }else{

                    }

					$class = '';
				  	if($i_k == 0){
						$class = 'active';
					}
					?>                   
						<div class="item <?php echo $class;?>">
							<?php echo '<img src="' . $i_v.'" alt="img/jpg" />' ?>
						</div>

			<?php } ?>
			
           
			<?php } else{
			  echo '<img src="' . $GLOBALS['CI']->template->template_images('default_hotel_img.jpg') . '" alt="' . $sanitized_data['HotelName'] . '"/>'; ?>
			<?php } ?>
           
        </div>

        <!-- Left and right controls -->
        <a class="left carousel-control" href="#myCarousel" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left"></span>
        <span class="sr-only">Previous</span>
        </a>
        <a class="right carousel-control" href="#myCarousel" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right"></span>
        <span class="sr-only">Next</span>
        </a>
    </div>
    <div class="container"><div class="num">1/10</div></div>
    <!-- static -->
    <div class="container detl_new">
    <div class="col-md-12 col-lg-12 col-xs-12 padall10">
   <div class="htl_loct">
	  <div class="hotelhed"><?php echo $sanitized_data['HotelName']; ?></div>
	    <div class="clearfix"></div>
      
    <div class="mensionspl"> <?php echo $sanitized_data['Address']; ?> </div>
     
      <div class="clearfix"></div>
          <?php if(valid_array($popular_facillities)){?>
    <div class="fclty col-md-12 col-sm-12 col-xs-12 resmagfix">
        <div class="containers">
            <div class="org_row">
            <div class="col-xs-12">
                <h3 class="fc_titl">Facilities</h3>
                <ul>
                    <?php foreach($popular_facillities as $facility){
                        $facility_image = $facility;
                        if($facility == "complimentary Wi-Fi"){
                            $facility_image = "wi_fi";
                        }
                        if($facility == "swimming pool"){
                            $facility_image = "swimming";
                        }
                        
                    ?>
                    <li><img src="<?=$GLOBALS['CI']->template->template_images('facility/'.$facility_image.'.svg')?>" alt="img/svg" /><span><?php echo ucfirst($facility);?></span></li>
                    <?php } ?>
                    <!--<li><img src="<?=$GLOBALS['CI']->template->template_images('facility/breakfast.svg')?>" alt="img/svg" /><span>Breakfast</span></li>
                    <li><img src="<?=$GLOBALS['CI']->template->template_images('facility/swimming.svg')?>" alt="img/svg" /><span>Swimming Pool</span></li>
                    <li><img src="<?=$GLOBALS['CI']->template->template_images('facility/bar.svg')?>" alt="img/svg" /><span>Bar</span></li>
                    <li><img src="<?=$GLOBALS['CI']->template->template_images('facility/spa.svg')?>" alt="img/svg" /><span>Spa</span></li>
                    <li><img src="<?=$GLOBALS['CI']->template->template_images('facility/tea.svg')?>" alt="img/svg" /><span>Tea/Coffee</span></li>
                    <li><img src="<?=$GLOBALS['CI']->template->template_images('facility/lunch.svg')?>" alt="img/svg" /><span>Lunch</span></li>
                    <li><img src="<?=$GLOBALS['CI']->template->template_images('facility/gym.svg')?>"><span>Fitness centre</span></li>
                    <li><img src="<?=$GLOBALS['CI']->template->template_images('facility/wi_fi.;svg')?>" alt="img/svg" /><span>Complimentary Wi-Fi</span></li>-->
                    
                </ul>
            </div>
            </div>
        </div>
    </div>
    <?php } ?>
   </div>
   <!-- <div class="clearfix"></div>  <div class="bokratinghotl rating-no">   
      <span class="star 1 active"></span><span class="star 2 "></span><span class="star 3 "></span><span class="star 4 "></span><span class="star 5 "></span>  </div> -->  
   <div class="sckint">
      <div class="ffty">
         <div class="borddo brdrit">
            <span class="lblbk_book"><span class="fa fa-calendar"></span>Check-in</span>   
            <div class="fuldate_book">
               <span class="htl_day"><?php echo date('D', strtotime($hotel_search_params['from_date']))?>, </span><span class="bigdate_book"><?php echo date('d', strtotime($hotel_search_params['from_date']))?></span>
               <div class="biginre_book"> <?php echo date('M', strtotime($hotel_search_params['from_date']))?>, <?php echo date('Y', strtotime($hotel_search_params['from_date']))?> </div>
            </div>
            <!--<div class="clearfix"></div>
            <span>12:00 PM</span>-->
         </div>
      </div>
      <i class="far fa-long-arrow-right"></i>  
      <div class="ffty">
         <div class="borddo">
            <span class="lblbk_book"> <span class="fa fa-calendar"></span> Check-out</span>   
            <div class="fuldate_book">
               <span class="htl_day"><?php echo date('D', strtotime($hotel_search_params['to_date']))?>, </span><span class="bigdate_book"><?php echo date('d', strtotime($hotel_search_params['to_date']))?></span>
               <div class="biginre_book"> <?php echo date('M', strtotime($hotel_search_params['to_date']))?>, <?php echo date('Y', strtotime($hotel_search_params['to_date']))?> </div>
            </div>
            <!--<div class="clearfix"></div>
            <span>12:00 PM</span>-->
         </div>
      </div>
      <div class="nigthcunt hide">Night(s) 4, Room(s) 1</div>
   </div>
</div>
</div>
    <!-- static -->
</div>
<div class="clearfix"></div>
<div class="search-result htl_detls">
    <div class="col-xs-12 mobilepad">
        <div class="top_detls">
            <ul class="dtls_lft">
                <li><i class="far fa-calendar-alt"></i> <?php echo $free_cancellation;?></li>
                <li><i class="far fa-usd-circle"></i> Best Price Guarantee</li>
                <li><i class="far fa-user"></i> 24/7 Support</li>
            </ul>
            <!--<ul>
                <li><a href="#"><i class="far fa-shopping-bag"></i></a></li>
                <li><a href="#"><i class="far fa-share-alt"></i></a></li>
            </ul>-->
        </div>
        <div class="htl_dtls_cont htldetailspage">
            <div class="rowfstep">
                <!-- slider -->
                <div class="col-md-8 col-sm-12 col-xs-12 nopad">
                    <div class="col-md-12 nopad">
                        <div class="htladdet">
                            <div class="marhtldet">
                               <!-- <span class="locadres"><i class="fa fa-map-marker-alt" aria-hidden="true"></i>&nbsp;<?php echo $sanitized_data['Address'] ?></span>-->
                                <ul class="htlratpz hide">
                                    <div class="stardetshtl"><span class="rating-no"><span class="hide" id="h-sr"><?= $sanitized_data['StarRating'] ?></span><?php echo print_star_rating($sanitized_data['StarRating']); ?></span>
                                    </div>
                        <!-- <li><i class="fa fa-star" aria-hidden="true"></i></li> -->

                                </ul>
                                <?php if ($_HotelDetails['trip_adv_url']): ?>
                                    <div class="triexcimg mobile_advisor">
                                        <a href="#"><img src="<?= $_HotelDetails['trip_adv_url'] ?>"></a>
                                    </div>
                                <?php endif; ?>

                            </div>
                            <span><?php //echo strtoupper($sanitized_data['HotelName']) ?></span>
                           
                        </div>
                        <div class="clearfix"></div>
                        <div class="rtng_rvw rating-no">
                            <span class="str_in str_numbr"><span class="star 1 active"><label><?php echo $sanitized_data['StarRating'];?></label></span>
                            <!-- <span class="rt_count"><?php echo $star_rating;?></span> -->
                            <strong><?php echo $trip_text;?></strong></span>
							<?php if(valid_array($params['hotel_reviews'])){
							if($params['hotel_reviews'][0]['count'] > 0){?>
                            <span class="rvw"><a href="<?php echo base_url().'index.php/hotel/hotel_reviews/'.$sanitized_data['HotelCode'] ;?>" target="_blank"><?php echo $params['hotel_reviews'][0]['count'];?> Reviews</a></span>
							<?php } } ?>
                        </div>
                        <div class="clearfix"></div>
						<?php if(valid_array($description)){ ?>
                            <div class="innertabs">
                                <!-- <h3 class="mobile_view_header">Description</h3> -->
                                <!-- <div class="htldesdv">Hotel Description</div> -->
                                <div id="hotel-additional-info" class="padinnerntb">
                                    <div class="lettrfty short-text">
										<ul>
										<?php foreach($description as $desc_key => $desc){
											if(empty($desc) == false){
											if($desc_key < count($description)-1){
											?>
											<li><?php echo $desc.'.';?>	
										<?php }else{?>
											<li><?php echo $desc;?>
											<?php }}} ?>
										</ul>
										<?php //echo $sanitized_data['Description'] ?></div>
                                    <div class="show-more" style="display:none;">
                                        <a href="#">Read more</a>
                                    </div>
                                </div>
                            </div>
						<?php } ?>
                    </div>
                    <?php if (false) { ?>
                    <div class="col-md-12 nopad">
                        <div class="htldtdv">

                            <div id="hotel_top" class="owl-carousel owl-theme">
                                <?php if (valid_array($sanitized_data['Images']) == true) { ?>
                                    <?php foreach ($sanitized_data['Images'] as $i_k => $i_v) { ?>                   
                                      
                                            <div class="item">
                                            <?= '<img src="' . $i_v.'" alt="' . $sanitized_data['HotelName'] . '"/>' ?>
                                            </div>
                                           
                                    <?php } ?>
                                <?php }else { ?>
                                    <?= '<img src="' . $GLOBALS['CI']->template->template_images('default_hotel_img.jpg') . '" alt="' . $sanitized_data['HotelName'] . '"/>' ?>
                                <?php } ?>
                            </div>
                            <div class="item hide" id="map_viewsld">
                                <div class="map_mobile_dets">
                                    <div id="Map" class="col-md-12" style="height:363px; width:100%;max-height: 363px;">Map</div>
                                </div> 
                            </div>
                            <div id="hotel_bottom" class="owl-carousel owl-theme">
<?php //debug($sanitized_data['Images']); exit;

if (valid_array($sanitized_data['Images']) == true) { ?>
                                    <?php foreach ($sanitized_data['Images'] as $i_k => $i_v) { ?>
                                        <?php
                                        //check if image exists in that url not
                                        //$file_header = @get_headers($i_v);
                                        $image_found = 1;
                                        // if(!$file_header  || $file_header [0] =='HTTP/1.1 404 Not Found'){
                                        // 	$image_found=0;
                                        // } 
                                        ?>
                                        <?php if ($image_found): ?>
                                            <div class="item">
                                                 <?= '<img src="' . $i_v.'" alt="' . $sanitized_data['HotelName'] . '"/>' ?>
                                            
                                            </div>
                                            <?php endif; ?>
                                        <?php } ?>
                                <?php }else { ?>
                                    <?= '<img src="' . $GLOBALS['CI']->template->template_images('default_hotel_img.jpg') . '" alt="' . $sanitized_data['HotelName'] . '"/>' ?>
                                <?php } ?>
                                <!-- <div class="item hide">
                                  <div class="map_mobile_dets">
                                             <div id="Map" class="col-md-12" style="height:200px; width:100%">Map</div>
                                      </div> 
                               </div> -->
                            </div>
                            <div class="htlmapdtls" id="maphtlmapdtls">
                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                            </div>
                            <div class="htlmapdtls hide" id="maphtlmapimages">
                                <i class="fas fa-images"></i>
                            </div>

                        </div>
                    </div>
                <?php } ?>

                </div>
                <!-- slider end -->
                <div class="col-md-4 col-sm-12 col-xs-12 resmagfix">
                <div class="dtl_prc">
                 <?php if ($_HotelDetails['trip_rating']): ?>
                    <div class="tridtls">                       
                            <div class="trirat">
                                <span class="trpratclr"><?= $_HotelDetails['trip_rating'] ?></span>
                            <?php
                            $star_rating = $_HotelDetails['trip_rating'];
                            $trip_text = '';
                            if ($star_rating == 3 || $star_rating == 3.5) {
                                $trip_text = 'Average';
                            } elseif ($star_rating == 4) {
                                $trip_text = 'Good';
                            } elseif ($star_rating == 4.5 || $star_rating == 5) {
                                $trip_text = 'Excellent';
                            } elseif ($star_rating == 2 || $star_rating == 2.5) {
                                $trip_text = 'Bad';
                            } elseif ($star_rating < 2) {
                                $trip_text = 'Very Bad';
                            }
                            ?>
                                <span class="triexcer"><?= $trip_text ?></span>
                            </div>
                           
                            <?php if ($_HotelDetails['trip_adv_url']): ?>
                            <div class="triexcimg">
                                <span class="trptrvrat">TripAdvisor Traveler Rating</span>
                                <a href="#"><img src="<?= $_HotelDetails['trip_adv_url'] ?>"></a>
                            </div>
                             <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                        <?php if (isset($_HotelDetails['first_room_details']) && empty($_HotelDetails['first_room_details']) == false): ?>
                        <div class="htlfull_dtls">
                            <div class="htlamtnyt hide">
                        <?php
                        //debug($_HotelDetails);exit;
                        //calculating room price per nights
                        $RoomPrice = $_HotelDetails['first_room_details']['Price']['RoomPrice'];
                        //echo $RoomPrice;
                        $no_of_nights = $hotel_search_params['no_of_nights'];
                        $per_night_price = ceil($RoomPrice / $no_of_nights);
                        //echo "per_night_price".$per_night_price;
                        $night_str = 'Night';
                        if ($no_of_nights > 1) {
                            $night_str = 'Nights';
                        }
                        //echo get_converted_currency_value($currency_obj->force_currency_conversion($RoomPrice));
                        ?>
                                <h2 class="amthtlrs"><strong> <?php echo $currency_obj->get_currency_symbol($currency_obj->to_currency); ?> </strong>&nbsp;<?= roundoff_number($per_night_price) ?><span class="pernyt">/ Per Night</span></h2> 

                                <div class="stdrmac">
                                    <span class="stdnonaclt"><?= $_HotelDetails['first_room_details']['room_name'] ?></span>
                                    <!-- <span class="bedndimg"><i class="fa fa-bed" aria-hidden="true"></i>&nbsp;Double Bed</span> -->
                                </div>
                            </div>

                            <div class="clearfix"></div>
                            <div class="htlamtnytstd">
                                <h4 class="amthtlrsstd"><small><?= $no_of_nights ?> <?= $night_str ?> from</small><i class="" aria-hidden="true"></i><?php echo $currency_obj->get_currency_symbol($currency_obj->to_currency); ?>&nbsp;<?= $RoomPrice ?> <span class="pernytdet">/suite</span></h4> 

    <?php if ($_HotelDetails['first_rm_cancel_date']): ?>
                                    <div class="stdrmac">
                                        <span class="stdnonacltfre">Free Cancellation</span>
                                        <span class="untdate">till <?php echo date('d M Y', strtotime($_HotelDetails['first_rm_cancel_date'])); ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="stdrmac">
                                        <span class="stdnonacltfre">Cancellation Policy</span>
                                        <span class="untdate">Non-Refundable</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
<?php endif; ?>
                    <div class="clearfix"></div>
                    <div class="cheoutdv hide">
                        <div class="chkdatetacell">
                            <span class="chkin">Check-in</span>
                            <span class="chkdate"><?= date('d M Y', strtotime($_HotelDetails['checkin'])); ?></span>
                        </div>
                        <div class="chkdatetacell">
                            <span class="chkin">Check-out</span>
                            <span class="chkdate"><?= date('d M Y', strtotime($_HotelDetails['checkout'])); ?></span>
                        </div>
                        <div class="chkdatetacell">
                            <span class="chkin">Room Guests</span>
<?php
$adult_count = 0;
$child_count = 0;
foreach ($hotel_search_params['adult_config'] as $a_value) {
    $adult_count += $a_value;
}
foreach ($hotel_search_params['child_config'] as $c_value) {
    $child_count += $c_value;
}
$adult_str = 'adult';
if ($adult_count > 1) {
    $adult_str = 'adults';
}
$child_str = 'child';
if ($child_count > 1) {
    $child_str = 'childrens';
}
?>
                            <span class="chkdate"><?= $hotel_search_params['room_count'] ?> room & <?= $adult_count ?> <?= $adult_str ?> <?php if ($child_count): ?> & <?= $child_count ?> <?= $child_str ?> <?php endif; ?></span>
                        </div>

                    </div>
                    <div class="clearfix"></div>
                    <div class="htlbkbtn">
<?php
$common_params_url = '';
$common_params_url .= '<input type="hidden" name="CancellationPolicy[]"	value="Cancellation">'; //Balu A
$common_params_url .= '<input type="hidden" name="booking_source"	value="' . $params['booking_source'] . '">';
$common_params_url .= '<input type="hidden" name="search_id"		value="' . $hotel_search_params['search_id'] . '">';
$common_params_url .= '<input type="hidden" name="ResultIndex"		value="' . $params['ResultIndex'] . '">';
$common_params_url .= '<input type="hidden" name="op"				value="block_room">';
$common_params_url .= '<input type="hidden" name="GuestNationality"	value="' . ISO_INDIA . '" >';
$common_params_url .= '<input type="hidden" name="HotelName"		value="" >';
$common_params_url .= '<input type="hidden" name="StarRating"		value="">';
$common_params_url .= '<input type="hidden" name="HotelImage"		value="">'; //Balu A
$common_params_url .= '<input type="hidden" name="HotelAddress"		value="">'; //

$dynamic_params_url[] = $_HotelDetails['first_room_details']['Room_data']['RoomUniqueId'];

$dynamic_params_url = serialized_data($dynamic_params_url);

$temp_dynamic_params_url = '';
$temp_dynamic_params_url .= '<input type="hidden" name="token" value="' . $dynamic_params_url . '">';
$temp_dynamic_params_url .= '<input type="hidden" name="token_key" value="' . md5($dynamic_params_url) . '">';
?>
                        <!--<form method="POST" action="<?= $booking_url ?>">
                        <?php
                        echo $common_params_url . $temp_dynamic_params_url;
                        ?>
							
                            <button class="bookallbtn htlbkftsz" type="submit" id="selectroom">View Rooms</button>
                        </form>-->
						<a href="#" class="view_rooms bookallbtn htlbkftsz" id="room-listlink">View Rooms</a>
                    </div>
                </div>
            </div>


                <!-- <div class="col-sm-4 col-xs-6 nopad ">
                                        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                                                <div class="carousel-inner" role="listbox">
<?php
//loop images
if (valid_array($sanitized_data['Images']) == true) {
    $visible = 'active';
    foreach ($sanitized_data['Images'] as $i_k => $i_v) {
        ?>
                                                                        <div class="item <?php echo $visible;
        $visible = ''; ?> ">
                                                                                <img src=<?php echo $i_v ?> alt="<?php echo $i_k ?>" class="img-responsive" style="width:100%; height:200px">
                                                                                <div class="carousel-caption">
                                                                                        <p><?php echo $sanitized_data['HotelName'] ?></p>
                                                                                </div>
                                                                        </div>
    <?php
    }
}
?>
                                                </div>
                                                
                                                <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
                                                        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                                                        <span class="sr-only">Previous</span>
                                                </a>
                                                <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
                                                        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                                                        <span class="sr-only">Next</span>
                                                </a>
                                        </div>
                                </div>
                                <div class="col-sm-4 col-xs-6 nopad">
                                        <div class="innerdetspad">
                                                <div class="hoteldetsname"><?php echo strtoupper($sanitized_data['HotelName']); ?></div>
                                                <div class="stardetshtl"><span class="rating-no"><span class="hide" id="h-sr"><?= $sanitized_data['StarRating'] ?></span><?php echo print_star_rating($sanitized_data['StarRating']); ?></span></div>
                                                <div class="adrshtlo"><?php echo $sanitized_data['Address'] ?></div>
                                                <div class="butnbigs">
                                                        <a class="tonavtorum movetop">Select Rooms</a>
                                                </div>
                                        </div>
                                </div> -->
                <!-- <div class="col-sm-4 nopad map_mobile_dets">
                        <div id="Map" class="col-md-12" style="height:200px; width:100%">Map</div>
                </div>  -->
            </div> 
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="prm_srvc">
        <div class="container-fluid">
            <div class="org_row">
            <div class="col-xs-12">
                <div class="usr_icn">
                    <img src="<?=$GLOBALS['CI']->template->template_images('srvc_icn.svg')?>">
                </div>
				<?php if($hotel_contact_numbers == "Not Available"){?>
				<div class="srvc_cont"><?php echo $hotel_contact_numbers;?></div>
				<?php } else{ ?>
                <div class="srvc_cont">
                    <h3>24/7 Premier concierge service</h3>
                    <p>Our team are available anytime, 24 hours, 7 days a week</p>
                    <h3><i class="fas fa-phone"></i> Call <?php echo $hotel_contact_numbers;?></h3>
                </div>
				<?php } ?>
            </div>
            </div>
        </div>
    </div>
	 <div class="tab-pane" id="htldets">
        <div class="container-fluid">
          <?php echo $GLOBALS['CI']->template->isolated_view('share/hotel_details_search', $page_data) ?>
        </div>
     </div>
    <div class="clearfix"></div>
    <div class="room_list" id="room-list">
       <?php echo $loading_image; ?>
    </div>
    <div class="clearfix"></div>
    <div class="rm_loc">
        <div class="container-fluid">
            <div class="org_row">
            <div class="col-xs-12">
                <h3 class="fc_titl">Location</h3>
                <div class="loc_in">
                	<div id="Map" class="col-md-12" style="height:363px; width:100%;max-height: 363px;">Map</div>
                </div>
            </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="top_things">
        <div class="container-fluid">
            <div class="org_row">
            <div class="col-xs-12">
                <h3>Top Things To Do</h3>
                <div class="to_do">
                  <ul class="nav nav-pills">
                    <li class="active"><a data-toggle="pill" href="#culture">Culture</a></li>
                    <li><a data-toggle="pill" href="#adventure">Adventure</a></li>
                    <li><a data-toggle="pill" href="#nature">Nature</a></li>
                    <li><a data-toggle="pill" href="#relaxation">Relaxation</a></li>
                    <li><a data-toggle="pill" href="#family">Family</a></li>
                  </ul>
                  <div class="tab-content">
                    <div id="culture" class="tab-pane fade in active">
                        <ul class="lst_img">
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_do1.jpg')?>" alt="img/jpg" /><span>Explore Unique Dining Experiences</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_do2.jpg')?>" alt="img/jpg" /><span>Explore Natural Landmarks</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_do3.jpg')?>" alt="img/jpg" /><span>Explore Luxurious Resorts</span></li>
                        </ul>
                        <div class="clearfix"></div>
                        <div class="col-xs-12 text-center load_more">
                            <button type="button" class="btn btn-default">Load more</button>
                        </div>
                    </div>
                    <!-- <div id="adventure" class="tab-pane fade">
                    </div>
                    <div id="nature" class="tab-pane fade">
                    </div>
                    <div id="relaxation" class="tab-pane fade">
                    </div>
                    <div id="family" class="tab-pane fade">
                    </div> -->
                  </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-xs-12">
                <h3>Top Places To Go</h3>
                <div class="to_do">
                  <ul class="nav nav-pills">
                    <li class="active"><a data-toggle="pill" href="#culture">Popular</a></li>
                    <li><a data-toggle="pill" href="#adventure">Cities</a></li>
                    <li><a data-toggle="pill" href="#nature">States</a></li>
                    <li><a data-toggle="pill" href="#relaxation">Beaches</a></li>
                    <li><a data-toggle="pill" href="#family">Islands</a></li>
                    <li><a data-toggle="pill" href="#family">Resorts</a></li>
                    <li><a data-toggle="pill" href="#family">Mountains</a></li>
                  </ul>
                  <div class="tab-content">
                    <div id="culture" class="tab-pane fade in active">
                        <ul class="lst_img">
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_go1.jpg')?>" alt="img/jpg" /><span>Explore Australia's Landmarks</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_go2.jpg')?>" alt="img/jpg" /><span>Explore Beauty Of Europe</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_go3.jpg')?>" alt="img/jpg" /><span>Explore Cruise Destinations</span></li>
                        </ul>
                        <div class="clearfix"></div>
                        <div class="col-xs-12 text-center load_more">
                            <button type="button" class="btn btn-default">Load more</button>
                        </div>
                    </div>
                    <!-- <div id="adventure" class="tab-pane fade">
                    </div>
                    <div id="nature" class="tab-pane fade">
                    </div>
                    <div id="relaxation" class="tab-pane fade">
                    </div>
                    <div id="family" class="tab-pane fade">
                    </div> -->
                  </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-xs-12">
                <h3>Must Visit Restaurants</h3>
                <div class="to_do">
                  <ul class="nav nav-pills">
                    <li class="active"><a data-toggle="pill" href="#culture">Australian</a></li>
                    <li><a data-toggle="pill" href="#adventure">Italian</a></li>
                    <li><a data-toggle="pill" href="#nature">Thai</a></li>
                    <li><a data-toggle="pill" href="#relaxation">Chinese</a></li>
                    <li><a data-toggle="pill" href="#family">Indian</a></li>
                  </ul>
                  <div class="tab-content">
                    <div id="culture" class="tab-pane fade in active">
                        <ul class="lst_img">
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('rest1.jpg')?>" alt="img/jpg" /><span>Barbecue Snag Home</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('rest2.jpg')?>" alt="img/jpg" /><span>Lamington Corner</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('rest3.jpg')?>" alt="img/jpg" /><span>Sausage House</span></li>
                        </ul>
                        <div class="clearfix"></div>
                        <div class="col-xs-12 text-center load_more">
                            <button type="button" class="btn btn-default">Load more</button>
                        </div>
                    </div>
                   <!--  <div id="adventure" class="tab-pane fade">
                    </div>
                    <div id="nature" class="tab-pane fade">
                    </div>
                    <div id="relaxation" class="tab-pane fade">
                    </div>
                    <div id="family" class="tab-pane fade">
                    </div> -->
                  </div>
                </div>
            </div>
        </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <?php if (false) { ?>
    <div class="fulldowny"> 
        <div class="col-xs-12 mobilepad">	
            <div class="fuldownsct">
                <div class="col-xs-12 nopad tab_htl_detail">
                    <div class="detailtab fulldetab shdoww">
                        <ul class="nav nav-tabs responsive-tabs">
                            <li class=""><a href="#htldets" data-toggle="tab">Hotel Details</a></li>
                            <li class="active roomstab"><a href="#rooms" data-toggle="tab">Rooms</a></li>
                            <li><a href="#facility" data-toggle="tab">Facilities</a></li>
                            <!-- <li><a href="#htlpolicy" data-toggle="tab">Hotel Policy</a></li> -->
                        </ul>
                        <div class="tab-content">
                            <!-- Hotel Detail-->
                           
                            <!-- Hotel Detail End-->
							 <div class="tab-pane" id="htldets">
                                <?php echo $GLOBALS['CI']->template->isolated_view('share/hotel_details_search', $page_data) ?>
                            </div>
                            <!-- Rooms-->
                            <div class="tab-pane active" id="rooms">

                                <div class="innertabs">
                                    <h3 class="mobile_view_header">Rooms</h3>

                                    <div class="">
                                        <div id="room-listdd" class="room-list romlistnh short-text1">
<?php echo $loading_image; ?>
                                        </div>

                                        <div class="show-rooms">
                                            <a href="#" id="show-more-link" class="hide">Show More Rooms +</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Rooms End-->
                            <!-- Facilities--> 
                            <div class="tab-pane" id="facility">

                                <div class="innertabs">
                                    <h3 class="mobile_view_header">Facilities</h3>
                                    <div class="padinnerntb htlfac_lity">
<?php
if (valid_array($sanitized_data['HotelFacilities']) == true) {
    //:p Did this for random color generation
    //$color_code = string_color_code('Balu A');
    $color_code = '#00a0e0';
    ?>
                                            <!-- <div class="hedinerflty">
                                                    Hotel Facilities
                                            </div> -->
                                            <?php
                                            //-- List group -->
                                            if ($sanitized_data['HotelFacilities']) {


                                                foreach ($sanitized_data['HotelFacilities'] as $ak => $av) {
                                                    ?>
                                                    <div class="col-xs-4 col-md-3 nopad">
                                                        <div class="facltyid">
                                                            <span class="glyphicon glyphicon-check" style="color:<?php echo $color_code ?>"></span> <?php echo $av; ?></div></div>
        <?php }
    }
    ?>


    <?php
}
?>
                                        <?php
                                        if (valid_array($sanitized_data['Attractions']) == true) {
                                            //:p Did this for random color generation
                                            //$color_code = string_color_code('Balu A');
                                            $color_code = '#00a0e0';
                                            ?>
                                            <div class="subfty">
                                                <div class="hedinerflty">
                                                    Attractions
                                                </div>
    <?php
    //-- List group -->
    foreach ($sanitized_data['Attractions'] as $ak => $av) {
        ?>
                                                    <div class="col-xs-4 nopad"><div class="facltyid"><span class="glyphicon glyphicon-check" style="color:<?php echo $color_code ?>"></span> <?php echo $av['Value']; ?></div></div>
                                                    <?php }
                                                ?>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <!-- Facilities End-->
                            <!-- Policy--> 
                            <!-- <div class="tab-pane" id="htlpolicy">
                                    <div class="innertabs hote_plcys">
                                            <div class="padinnerntb">
                                            <p><?php echo (empty($sanitized_data['HotelPolicy']) == false ? $sanitized_data['HotelPolicy'] : '---'); ?></p>
                                            </div>
                                            
                                    </div>
                            </div> -->
                            <!-- Policy End-->

                        </div>
                    </div>
                </div>
                <div class="col-xs-4 hide">
                    <div class="innertabs">
                        <?php if (valid_array($sanitized_data['HotelFacilities']) == true) { ?>
                            <div class="hedinerflty">
                                Hotel Facilities
                            </div>
                        <?php } ?>

                        <div class="padinnerntb htlfac_lity">
                            <?php
                            if (valid_array($sanitized_data['HotelFacilities']) == true) {
                                //:p Did this for random color generation
                                //$color_code = string_color_code('Balu A');
                                $color_code = '#00a0e0';
                                ?>

                                <?php
                                //-- List group -->
                                foreach ($sanitized_data['HotelFacilities'] as $ak => $av) {
                                    ?>
                                    <div class="col-xs-12 nopad">
                                        <div class="facltyid">
                                            <span class="glyphicon glyphicon-check" style="color:<?php echo $color_code ?>"></span> <?php echo $av; ?></div></div>
                                    <?php }
                                ?>


                                <?php
                            }
                            ?>
                            <?php
                            if (valid_array($sanitized_data['Attractions']) == true) {
                                //:p Did this for random color generation
                                //$color_code = string_color_code('Balu A');
                                $color_code = '#00a0e0';
                                ?>
                                <div class="subfty">

                                    <?php
                                    //-- List group -->
                                    foreach ($sanitized_data['Attractions'] as $ak => $av) {
                                        ?>
                                        <div class="col-xs-4 nopad"><div class="facltyid"><span class="glyphicon glyphicon-check" style="color:<?php echo $color_code ?>"></span> <?php echo $av['Value']; ?></div></div>
                                        <?php }
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<?php
/**
 * This is used only for sending hotel room request - AJAX
 */
$hotel_room_params['ResultIndex'] = $params['ResultIndex'];
$hotel_room_params['booking_source'] = $params['booking_source'];
$hotel_room_params['search_id'] = $hotel_search_params['search_id'];
$hotel_room_params['op'] = 'get_room_details';

?>

<script>
    $(document).ready(function () {
		 function goToByScroll(id){
          // Reove "link" from the ID
        id = id.replace("link", "");
          // Scroll
        $('html,body').animate({
            scrollTop: $("#"+id).offset().top},
            'slow');
    }

    $(".view_rooms").click(function(e) { 
          // Prevent a page reload when a link is pressed
        e.preventDefault(); 
          // Call the scroll function
        goToByScroll($(this).attr("id"));           
    });
        //Load hotel Room Details
        var ResultIndex = '';
        var HotelCode = '';
        var TraceId = '';
        var booking_source = '';
        var op = 'get_room_details';
        function load_hotel_room_details()
        {
            var _q_params = <?php echo json_encode($hotel_room_params) ?>;
            if (booking_source) {
                _q_params.booking_source = booking_source;
            }
            if (ResultIndex) {
                _q_params.ResultIndex = ResultIndex;
            }
            $.post(app_base_url + "index.php/ajax/get_room_details", _q_params, function (response) {
                if (response.hasOwnProperty('status') == true && response.status == true) {
                    $('#room-list').html(response.data);
                    var _hotel_name = "<?php echo preg_replace('/^\s+|\n|\r|\s+$/m', '', $sanitized_data['HotelName']); //Hotel Name comes from hotel info response  ?>";
                    var _hotel_star_rating = <?php echo abs($sanitized_data['StarRating']) ?>;
                    var _hotel_image = "<?php echo $sanitized_data['Images'][0]; ?>";
                    var _hotel_address = "<?php echo preg_replace('/^\s+|\n|\r|\s+$/m', '', $sanitized_data['Address']); ?>";
                    $('[name="HotelName"]').val(_hotel_name);
                    $('[name="StarRating"]').val(_hotel_star_rating);
                    $('[name="HotelImage"]').val(_hotel_image);//Balu A
                    $('[name="HotelAddress"]').val(_hotel_address);//Balu A
                }
            });
        }
        load_hotel_room_details();
        $('.hotel_search_form').on('click', function (e) {
            e.preventDefault();
            $('#hotel_search_form').slideToggle(500);
        });


        $('.movetop').click(function () {
            $('html, body').animate({scrollTop: $('.fulldowny').offset().top - 60}, 'slow');
        });
        $(".close_fil_box").click(function () {
            $(".coleft").hide();
            $(".resultalls").removeClass("open");
        });

        /*slider*/
        var totalItems = $('#myCarousel .item').length;
		
        var currentIndex = $('#myCarousel .item.active').index() + 1;
		console.log(currentIndex);
        $('.num').html('' + currentIndex + '/' + totalItems + '');

        $('#myCarousel').carousel({
          interval: 2000
        });

       $('#myCarousel').on('slid', function () {
          currentIndex = $('div.item.active').index() + 1;
			console.log(currentIndex);
          $('.num').html('' + currentIndex + '/' + totalItems + '');
        });
    });

</script>

<?php
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/pax_count.js'), 'defer' => 'defer');
?>