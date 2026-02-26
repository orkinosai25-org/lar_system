<?php
$loading_image		 = '<div class="text-center loader-image"><img src="'.$GLOBALS['CI']->template->template_images('loader_v1.gif').'" alt="Loading........"/></div>';
$module_value = md5('hotel');
$currency_symbol = $this->currency->get_currency_symbol($pre_booking_params['default_currency']);



$room_facilities=array();
                   $room_faci =$this->custom_db->single_table_records('eco_stays_rooms', '*', array('stays_origin' => $pre_booking_params['HotelCode']));
                   if($room_faci['status'] == SUCCESS_STATUS){
	                   $testing=json_decode($room_faci['data'][0]['amenities'],true);
	                    foreach ($testing as $key => $value) {
	                       $test_val =$this->custom_db->single_table_records('eco_stays_room_amenities', '*', array('origin' => $value));
	                       $room_facilities[]=$test_val['data'][0]['name'];
	                  }
	                }



$CI=&get_instance();
$template_images = $GLOBALS['CI']->template->template_images();
$mandatory_filed_marker = '<sup class="text-danger">*</sup>';
$hotel_checkin_date = hotel_check_in_out_dates($search_data['from_date']);
$hotel_checkin_date = explode('|', $hotel_checkin_date);
$hotel_checkout_date = hotel_check_in_out_dates($search_data['to_date']);
$hotel_checkout_date = explode('|', $hotel_checkout_date);
/*$room_facilities[0] = "Wifi";
$room_facilities[1] = "Breakfast";*/
$room_facilities=$room_facilities;
if(is_logged_in_user()) {
	$review_active_class = ' success ';
	$review_tab_details_class = '';
	$review_tab_class = ' inactive_review_tab_marker ';
	$travellers_active_class = ' active ';
	$travellers_tab_details_class = ' gohel ';
	$travellers_tab_class = ' travellers_tab_marker ';
} else {
	$review_active_class = ' active ';
	$review_tab_details_class = ' gohel ';
	$review_tab_class = ' review_tab_marker ';
	$travellers_active_class = '';
	$travellers_tab_details_class = '';
	$travellers_tab_class = ' inactive_travellers_tab_marker ';
}
$passport_issuing_country = INDIA_CODE;
$temp_passport_expiry_date = date('Y-m-d', strtotime('+5 years'));
$static_passport_details = array();
$static_passport_details['passenger_passport_expiry_day'] = date('d', strtotime($temp_passport_expiry_date));
$static_passport_details['passenger_passport_expiry_month'] = date('m', strtotime($temp_passport_expiry_date));
$static_passport_details['passenger_passport_expiry_year'] = date('Y', strtotime($temp_passport_expiry_date));
$hotel_code = $pre_booking_params['hotel_code'];
$search_index = $pre_booking_params['ResultIndex'];
$hotel_details_url = base_url().'index.php/hotel/hotel_details/'.($search_data['search_id']).'?ResultIndex='.urlencode($search_index).'&booking_source='.urlencode($booking_source).'&op=get_details';

$hotel_total_price = roundoff_number($this->hotel_lib->total_price($pre_booking_params['markup_price_summary']));

/********************************* Convenience Fees *********************************/

$subtotal = $hotel_total_price;
$pre_booking_params['convenience_fees'] = $convenience_fees;
$hotel_total_price = roundoff_number($hotel_total_price-$pre_booking_params['markup_price_summary']['_GST']);
/********************************* Convenience Fees *********************************/
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('provablib.js'), 'defer' => 'defer');

$book_login_auth_loading_image	 = '<div class="text-center loader-image"><img src="'.$GLOBALS['CI']->template->template_images('loader_v3.gif').'" alt="please wait"/></div>';
//debug($pre_booking_params);
//$LastCancellationDate = $pre_booking_params['LastCancellationDate'];
$LastCancellationDate = '';
$RoomTypeName = $pre_booking_params['RoomTypeName'];
$Boardingdetails = $pre_booking_params['Boarding_details'];

$RateComments = @$pre_booking_params['RateComments'];
//calculating price
$token = $pre_booking_params['price_token'];
$tax_total = 0;
$grand_total = 0;
foreach($token as $token_k => $token_v) {
	// debug($token_v);
	$temp_price_details = $GLOBALS['CI']->hotel_lib->update_room_markup_currency($token_v, $currency_obj, $search_data['search_id'], false, true, 'b2c');
	//debug($temp_price_details);exit;
	$RoomPrice = $temp_price_details['RoomPrice'];
	$room_tax = $GLOBALS['CI']->hotel_lib->tax_service_sum($temp_price_details, $token_v);
	//debug($room_tax);exit;
	//$grand_total += $RoomPrice+$room_tax;
	//echo $RoomPrice.'<br/>';
	$grand_total += $RoomPrice;
	//$tax_total += $room_tax;
}
 //debug($pre_booking_params);exit;
$tax_total += $pre_booking_params['markup_price_summary']['_GST'];
$tax_total  = roundoff_number($tax_total);
// $grand_total += $pre_booking_params['convenience_fees'];
$grand_total = $hotel_total_price+$tax_total+$pre_booking_params['convenience_fees'];
// echo $tax_total;exit;

$grand_total = ceil($grand_total);
#echo $grand_total.'<br/>';
$hotel_total_price = ceil($hotel_total_price);
//calculate total room price without tax

#echo $hotel_total_price;exit;
$total_room_price  = ceil($hotel_total_price);
//echo $total_room_price;exit;
$total_pax = array_sum($search_data['adult_config'])+array_sum($search_data['child_config']);
$base_url=base_url().'index.php/hotel/image_details_cdn';
//check image exists or not in url
/*$file_header = @get_headers($pre_booking_params['HotelImage']);
$image_found=1;
if(!$file_header  || $file_header [0] =='HTTP/1.1 404 Not Found'){
	$image_found=0;

}*/

$total_adult_count	= array_sum($search_data['adult_config']);
$total_child_count	= array_sum($search_data['child_config']);
$no_adult='No of Adults';
	$no_child = 'No of Childs';
	if($total_adult_count==1){
		$no_adult='No of Adult';			      			
	}
	if($total_child_count==1 || $total_child_count ==0){
		$no_child = 'No of Child';
	}
 $total_pax = array_sum($search_data['adult_config'])+array_sum($search_data['child_config']);


?>


<style>
   /* .fixed {
	position: fixed;
	top:60px;
	width: 100%;
	bottom: 0;
}*/
	.topssec::after{display:none;}
</style>
<input type="hidden" id="total_pax" value="<?=$total_pax?>">
<div class="fldealsec">
  <div class="container">
	<div class="tabcontnue">
	<div class="col-xs-4 nopadding">
			<div class="rondsts <?=$review_active_class?>">
			<a class="taba core_review_tab <?=$review_tab_class?>" id="stepbk1">
				<div class="iconstatus fa fa-eye"></div>
				<div class="stausline">Review</div>
			</a>
			</div>
		</div>
		<div class="col-xs-4 nopadding">
			<div class="rondsts <?=$travellers_active_class?>">
			<a class="taba core_travellers_tab <?=$travellers_tab_class?>" id="stepbk2">
				<div class="iconstatus fas fa-users"></div>
				<div class="stausline">Travellers</div>
			</a>
			</div>
		</div>
		<div class="col-xs-4 nopadding">
			<div class="rondsts">
			<a class="taba" id="stepbk3">
				<div class="iconstatus far fa-money-bill-alt"></div>
				<div class="stausline">Payments</div>
			</a>
			</div>
		</div>
	 </div>
	
  </div>
</div>
<div class="clearfix"></div>
<div class="alldownsectn htl_bk">
	<div class="container">
  <div class="ovrgo">
	<div class="bktab1 xlbox <?=$review_tab_details_class?> hide">
		<div class="col-xs-12 col-md-8 toprom nopad">		  
		  	<div class="col-xs-12 nopadding full_log_tab">
	  <div class="fligthsdets">
		<div class="flitab1">
				<div class="clearfix"></div>
				<!-- LOGIN SECTION STARTS -->
				<?php if(is_logged_in_user() == true) { ?>
				<div class="loginspld">
					<div class="logininwrap">
					<div class="signinhde">
						Sign in now to Book Online
					</div>
					<div class="newloginsectn">
						<div class="col-xs-7 celoty nopad">
							<div class="insidechs">
								<div class="mailenter">
									<input type="text" name="booking_user_name" id="booking_user_name"  placeholder="Your mail id" class="newslterinput nputbrd _guest_validate" maxlength="80">
									 <!-- <span id="name_error"><div class="formerror">Please enter your mail id</div></span> -->
								</div>  
								<div class="noteinote">Your booking details will be sent to this email address.</div>
								<div class="clearfix"></div>
								<div class="havealrdy">
									<div class="squaredThree">
									  <input id="alreadyacnt" type="checkbox" name="check" value="None">
									  <label for="alreadyacnt"></label>
									</div>
									<label for="alreadyacnt" class="haveacntd">I have an Account</label>
								</div>
								<div class="clearfix"></div>
								<div class="twotogle">
								<div class="cntgust">
									<div class="phoneumber">
										<div class="col-xs-5 nopadding">
												<!-- <input type="text" placeholder="+91" class="newslterinput nputbrd"> -->
											<select name="" class="newslterinput nputbrd _numeric_only" id="before_country_code"  required>
												<?php 
												//debug($phone_code);exit;
												echo diaplay_phonecode($phone_code,$active_data, $user_country_code); ?>
											</select> 
										</div>
										<div class="col-xs-1 nopadding"><div class="sidepo">-</div></div>
										<div class="col-xs-6 nopadding">
											<input type="text" id="booking_user_mobile" placeholder="Mobile Number" class="newslterinput nputbrd _numeric_only _guest_validate" maxlength="10">
										</div>
										<div class="clearfix"></div>
										<div class="noteinote">We'll use this number to send possible update alerts.</div>
									</div>
									<div class="clearfix"></div>
									<div class="continye col-xs-8 nopad">
										<button class="bookcont" id="continue_as_guest">Book as Guest</button>
									</div>
								</div>
								<div class="alrdyacnt">
									<div class="col-xs-12 nopad">
										 <div class="relativemask"> 
											<input type="password" name="booking_user_password" id="booking_user_password" class="clainput" placeholder="Password" />
										 </div>
										 <div class="clearfix"></div>
										 <a class="frgotpaswrd">Forgot Password?</a>
										 <div style="" class="hide alert alert-danger"></div>
									</div>
									
									<div id="book_login_auth_loading_image" style="display: none">
										<?=$book_login_auth_loading_image?>
									</div>
														
									<div class="clearfix"></div>
									<div class="continye col-xs-8 nopad">
										<button class="bookcont" id="continue_as_user">Proceed to Book</button>
									</div>
								</div>
								</div>
							</div>
						</div>
						 <?php $no_social=no_social(); if($no_social != 0) {?>
						<div class="col-xs-2 celoty nopad linetopbtm">
								<div class="orround">OR</div>
						</div>
						<?php } ?>
						<div class="col-xs-5 celoty nopad">
							<div class="insidechs booklogin">
								<div class="leftpul">
			<?php 
				$social_login1 = 'facebook';
				$social1 = is_active_social_login($social_login1);
				if($social1){
					$GLOBALS['CI']->load->library('social_network/facebook');
					echo $GLOBALS['CI']->facebook->login_button ();
				} 
				$social_login2 = 'twitter';
				$social2 = is_active_social_login($social_login2);
				if($social2){
				?>
				<a class="logspecify tweetcolor"><span class="fa fa-twitter"></span>
				<div class="mensionsoc">Login with Twitter</div>
				</a>
			<?php } 
				$social_login3 = 'googleplus';
				$social3= is_active_social_login($social_login3);
				if($social3){
					$GLOBALS['CI']->load->library('social_network/google');
					echo $GLOBALS['CI']->google->login_button ();
				} ?>
							</div>
						</div>
					</div>
				</div>
				</div>
			</div>
			<?php } ?>
				<!-- LOGIN SECTION ENDS -->
				</div>
				</div>
		 </div>
		  <div class="col-xs-4 nopad full_room_buk hide">
			<div class="sckint">
			  <div class="ffty">
				<div class="borddo brdrit"> <span class="lblbk_book">
				<span class="fa fa-calendar"></span>
				Check-in</span>
				  <div class="fuldate_book"><span class="htl_day"><?php echo date("D", strtotime($search_data['from_date'])).', ';?></span> <span class="bigdate_book"><?=$hotel_checkin_date[0].', ';?></span>
					<div class="biginre_book"> <?=$hotel_checkin_date[1].', ';?><br>
					  <?=$hotel_checkin_date[2]?> </div>
				  </div>
				  
				</div>
			  </div>
			  <div class="ffty">
				
				<div class="borddo"> <span class="lblbk_book"> <span class="fa fa-calendar"></span> Check-out</span>
				  <div class="fuldate_book"><span class="htl_day"><?php echo date("D", strtotime($search_data['to_date'])).', ';?></span><span class="bigdate_book"><?=$hotel_checkout_date[0].', ';?></span>
					<div class="biginre_book"> <?=$hotel_checkout_date[1].', ';?><br>
					  <?=$hotel_checkout_date[2]?> </div>
				  </div>
				 
				</div>
			  </div>
			  <div class="clearfix"></div>
			  <div class="nigthcunt">Night(s) <?=$search_data['no_of_nights']?>, Room(s) <?=$search_data['room_count']?></div>
			</div>
		  </div>
		</div>
		<div class="col-md-4 col-xs-12 full_room_buk rhttbepa">
		   <div id="slidebarscr">
		 	<table class="table table-condensed tblemd">
			 	<tbody>
			 	  <tr class="rmdtls">
			        <td colspan="2">Fare Summary</td>
			      </tr>
			      <tr>
			        <td>Room Type</td>
			        <td><?=$RoomTypeName?></td>
			      </tr>
			      <tr class="aminitdv">
			        <td>Board Type</td>
			        <td style="font-size:12px;"><?php if($Boardingdetails):?>			         		
		         		<?php  $am_arr = array();
		         			foreach ($Boardingdetails as $b_key => $b_value) {
		         				$am_arr[]=$b_value;
		         			}
                                              foreach($am_arr as $key_v=>$_val)
                                              {
                                                  echo 'Room'.($key_v+1).': '.$_val.'<br />';
                                              }
                                               //echo implode("<br />",$am_arr);
			         	?>
			        <?php else:?>
		         	<span>Room Only</span>
			        	<?php endif;?>
			        </td>
			      </tr>
			      <tr>
			       <?php
				         $total_pax = array_sum($search_data['adult_config'])+array_sum($search_data['child_config']);
				        ?>

			        <td>No of Guest</td>
			        <td><?=$total_pax?></td>
			      </tr>
			      <tr>

			        <td><?=$no_adult?></td>
			        <td><?=array_sum($search_data['adult_config'])?></td>
			      </tr>
			      <tr>
			        <td><?=$no_child?></td>
			        <td><?=array_sum($search_data['child_config'])?></td>
			      </tr>
			      <?php if($LastCancellationDate):?>
				      <tr class="frecanpy">
				        <td>Free Cancellation till:<br/><a  href="#" data-target="#roomCancelModal"  data-toggle="modal" >View Cancellation Policy</a></td>
				        <td><?=local_montd_date($LastCancellationDate)?></td>
				        
				      </tr>
				  <?php else:?>
				  		<tr class="frecanpy">
				        <td>Cancellation Policy:<br/><a  href="#" data-target="#roomCancelModal"  data-toggle="modal" >View Cancellation Policy</a></td>
				        <td>Non-Refundable</td>
				      </tr>
			 	 <?php endif;?>
			 
			      <tr>
			        <td>Total Price</td>
			        <td><?=$this->currency->get_currency_symbol($pre_booking_params['default_currency'])?> <?=($total_room_price)?></td>
			      </tr>
			      <tr class="texdiv">
			        <td>Taxes & Service fee</td>
			        <td><?=$this->currency->get_currency_symbol($pre_booking_params['default_currency'])?> <?=$tax_total?></td>
			      </tr>
			      <?php if($pre_booking_params['markup_price_summary']['_GST'] > 0){?>
			      <tr class="texdiv">
			        <td>GST</td>
			        <td><?=$this->currency->get_currency_symbol($pre_booking_params['default_currency'])?> <?=$pre_booking_params['markup_price_summary']['_GST'];?></td>
			      </tr>
			      <?php } ?>
			      <tr class="grd_tol">
			        <td>Grand Total</td>
			        <td><?=$this->currency->get_currency_symbol($pre_booking_params['default_currency'])?> <?=($grand_total)?></td>
			      </tr>
			    </tbody>
			  </table>
			  <?php if(isset($pre_booking_params['sur_Charge_exclude'])){ ?>
			      
			       <div>Not included in price (collected by the property): <?php echo $pre_booking_params['surCharge_exclude_name']; ?> Rs. <?php echo $pre_booking_params['sur_Charge_exclude']; ?>
			      </div>
			      	<?php }?>
		    </div>
		  </div>		
		<div class="clearfix"></div>	
	</div>
	<div class="bktab2 xlbox <?=$travellers_tab_details_class?> d-block">
	  	<button id="back_btn" class="btn waves-effect waves-light"  name="action"><i class="far fa-chevron-left"></i> Back</button>
	  	<h3 class="htl_rvw">Review your Booking</h3>
		<div class="col-xs-4 rhttbepa">
			   <div id="nxtbarslider">
			 	<table class="table table-condensed tblemd">
				 	<tbody>
				 	  <tr class="rmdtls">
				        <td colspan="2">Fare Summary</td>
				      </tr>
				      <tr class="hr_line"><td colspan="2"><span>&nbsp;</span></td></tr>
				      <tr>
				        <td><strong>Base Price</strong></td>
				        <td><?=$this->currency->get_currency_symbol($pre_booking_params['default_currency'])?> <?=$total_room_price?></td>
				      </tr>
						
				      <tr class="aminitdv hide">
			        <td>Board Type</td>
			        <td>
			        	
			        	<?php if($Boardingdetails):?>				         		
		         		<?php  
		         			$am_arr = array();
		         			foreach ($Boardingdetails as $b1_key => $b1_value) {
		         				$am_arr[]=$b1_value;
		         			}
		         			echo implode(",",$am_arr);			         	
		         		?>
			        	
		         	<?php else:?>
		         			<span>Room Only</span>
			        	<?php endif;?>
			        	

			        </td>
			      </tr>
				      <tr class="hide">
				        <td>No of Guest</td>
				        <?php
				         $total_pax = array_sum($search_data['adult_config'])+array_sum($search_data['child_config']);
				        ?>
				        <td><?=$total_pax?></td>
				      </tr>
				      <tr class="hide">
				        <td><?=$no_adult?></td>
				        <td><?=array_sum($search_data['adult_config'])?></td>
				      </tr>
				      <tr class="hide">
				        <td><?=$no_child?></td>
				        <td><?=array_sum($search_data['child_config'])?></td>
				      </tr>
				       <?php if($LastCancellationDate):?>
					      <tr class="frecanpy">
					        <td>Free Cancellation till:<br/><a  href="#" data-target="#roomCancelModal"  data-toggle="modal" >View Cancellation Policy</a></td>
					        <td><?=local_month_date($LastCancellationDate)?></td>
					        
					      </tr>
					  <?php else:?>
					  		<tr class="frecanpy hide">
					        <td>Cancellation Policy:<br/><a  href="#" data-target="#roomCancelModal" data-toggle="modal">View Cancellation Policy</a></td>
					        <td>Non-Refundable</td>
					      </tr>

				 	 <?php endif;?>
				 	<!--   <tr class="frecanpy">
				        <th><a  href="#" data-target="#roomCancelModal" data-toggle="modal" >View Cancellation Policy:</a></th>
			      		</tr>
				 	   -->
				      
				      <tr class="hr_line"><td colspan="2"><span>&nbsp;</span></td></tr>
				        <td><strong>Total Price</strong></td>
				        <td><?=$this->currency->get_currency_symbol($pre_booking_params['default_currency'])?> <?=$total_room_price?></td>
				     
				      <tr class="texdiv">
				        <td>Taxes & Service fee</td>
				        <td><?=$this->currency->get_currency_symbol($pre_booking_params['default_currency'])?> <?=$tax_total?></td>
				      </tr>
				      <?php 

			      	//if($pre_booking_params['markup_price_summary']['_GST'] > 0){

			      	?>
			      <tr class="texdiv">
			        <td><?php echo $convenience_fees_text;?></td>
			        <td><?=$this->currency->get_currency_symbol($pre_booking_params['default_currency'])?> <?=$pre_booking_params['convenience_fees'];?></td>
			      </tr>
			      <?php //} ?>
				      <tr class="promo_code_discount hide">
				        <td>Promo Code Discount</td>
				        <td class="promo_discount_val"></td>
				      </tr>
				      <tr class="hr_line"><td colspan="2"><span>&nbsp;</span></td></tr>
					<tr class="grd_tol">
				        <td>Grand Total</td>
				        <td class="grandtotal"><?=$this->currency->get_currency_symbol($pre_booking_params['default_currency'])?> <?=$grand_total?>/-</td>
				      </tr>
				   
				      				    </tbody>
				  </table>
			   </div>
			<div class="clearfix"></div>
			<div class="col-xs-12 ad_info">
			<form name="promocode" id="promocode" novalidate>
									                    <div class="col-md-12 col-xs-12 nopadding_right pr_code nopad">
									                      <div class="cartprc">
									                        <div class="payblnhm singecartpricebuk ritaln">
									                    	 <input type="text" placeholder="Enter Promo Code" name="code" id="code" class="promocode" aria-required="true" />
									                          <input type="hidden" name="module_type" id="module_type" class="promocode" value="<?=@$module_value;?>" />
									                          <input type="hidden" name="total_amount_val" id="total_amount_val" class="promocode" value="<?=@$subtotal;?>" />
									                          <input type="hidden" name="convenience_fee" id="convenience_fee" class="promocode" value="<?=@$convenience_fees;?>" />
									                          <input type="hidden" name="currency_symbol" id="currency_symbol" value="<?=@$currency_symbol;?>" />
									                          <input type="hidden" name="currency" id="currency" value="<?=@$pre_booking_params['default_currency'];?>" />
									                         
									                         <p class="error_promocode text-danger" style="font-weight:bold"></p>                                          
									                        </div>
									                    </div>
									                    
															 <input type="hidden" value="<?php echo $grand_total;?>" id="actual_grand_total" />	<input type="button" value="Apply" name="apply" id="apply" class="promosubmit">
															
															    </div>
															
									                    
									                  </form>
		</div>
			<div class="clearfix"></div>
			<div class="col-xs-12 ad_info">
				<h2>We also provide :</h2>
				<ul class="ad_sec">
					<?php if (is_active_airline_module()) { ?>
					<li><a href="<?php echo $flight_search_url;?>" target="_blank"><img src="<?=$GLOBALS['CI']->template->template_images('flight.svg')?>" alt="img/svg" /></a></li>
					<?php } ?>
					<?php if (is_active_car_module()) { ?>
						<li><a href="#" target="_blank"><img src="<?=$GLOBALS['CI']->template->template_images('car.svg')?>" alt="img/svg" /></a></li>
					<?php } ?>
					<?php if (is_active_airline_module()) { ?>
					<li class="hide"><a href="#" target="_blank"><img src="<?=$GLOBALS['CI']->template->template_images('cruise.svg')?>" alt="img/svg" /></a></li>
					<?php } ?>
					
				</ul>
			</div>
		</div>
	  <div class="col-xs-12 col-md-8 nopad">
		  <div class="col-xs-12 nopad full_room_buk">
			<div class="bookcol">
			  <div class="hotelistrowhtl">
				<div class="col-md-12 col-xs-12 nopad">
				  <div class="imagehotel">
				  	
				  		<?php if($pre_booking_params['HotelImage']!='/'):?>
				  		<?php
				  			$image = $base_url.'/'.base64_encode($pre_booking_params['HotelCode']).'/0';
				  			//$image = $pre_booking_params['HotelImage'];
				  		?>
				  		<?php 
				  		if($pre_booking_params['booking_source']=='PTBSID0000000011')
				  		{

				  			$data1 = $this->custom_db->single_table_records('eco_stays_gallery_images', '*', array('stays_origin' => $pre_booking_params['hotel_code']));
			$pre_booking_params['HotelImage']='https://www.travelsoho.com/LAR/extras/custom/TMX1512291534825461/uploads/eco_stays/stays/'.$data1['data'][0]['image'];
				  		}


				  		 ?>
				  		<img alt="<?=$pre_booking_params['HotelName']?>" src="<?=$pre_booking_params['HotelImage'];?>">
				  	<?php else:?>
				  		<img alt="Hotel_img" src="<?=$GLOBALS['CI']->template->template_images('default_hotel_img.jpg')?>" class="lazy h-img">
				  	<?php endif;?>
				  </div>
				</div>
				<span class="usr_rat"><?php echo $pre_booking_params['StarRating'];?></span>
				<div class="col-md-12 col-xs-12 padall10">
					<div class="htl_loct">
					  <div class="hotelhed"><?=$pre_booking_params['HotelName']?></div>
					  <div class="clearfix"></div>
					  <div class="mensionspl"> <?=$pre_booking_params['HotelAddress']?> </div>
					</div>
				  <!-- <div class="clearfix"></div>
				  <div class="bokratinghotl rating-no">				   
					  <?=print_star_rating($pre_booking_params['StarRating'])?>
				  </div> -->
				  <div class="sckint">
			  <div class="ffty">
				<div class="borddo brdrit"><span class="lblbk_book">
				<span class="fa fa-calendar"></span>
				Check-in</span>
				   <div class="fuldate_book"><span class="htl_day"><?php echo date("D", strtotime($search_data['from_date'])).', ';?></span> <span class="bigdate_book"><?=$hotel_checkin_date[0];?></span>
					<div class="biginre_book"> <?=$hotel_checkin_date[1].', ';?>
					  <?=$hotel_checkin_date[2]?> </div>
				  </div>
				  <!--<div class="clearfix"></div>
				  <span>12:00 PM</span>-->
				</div>
			  </div>
			  <i class="far fa-long-arrow-right"></i>
			  <div class="ffty">
				<div class="borddo"><span class="lblbk_book"> <span class="fa fa-calendar"></span> Check-out</span>
				   <div class="fuldate_book"><span class="htl_day"><?php echo date("D", strtotime($search_data['to_date'])).', ';?></span><span class="bigdate_book"><?=$hotel_checkout_date[0];?></span>
					<div class="biginre_book"> <?=$hotel_checkout_date[1].', ';?>
					  <?=$hotel_checkout_date[2]?> </div>
				  </div>
				  <!--<div class="clearfix"></div>
				  <span>12:00 PM</span>-->
				</div>
			  </div>
			  <div class="nigthcunt hide">Night(s) <?=$search_data['no_of_nights']?>, Room(s) <?=$search_data['room_count']?></div>
			</div>
				</div>
			  </div>
			</div>
		  </div>
		  <div class="clearfix"></div>
		  <div class="room_cont">
		  	<div class="rm_titl">
		  		<h3><?php echo $RoomTypeName;?></h3>
				<button id="change_room" class="chng_rm view_rooms">Change room</button>
		  		
		  	</div>
		  	<div class="rm_detls">
		  		<div class="rm_gust">
		  			<h5><?php echo $total_pax;?> Guests | <?php echo $search_data['no_of_nights'];?> Nights </h5>
		  			<ul>
		  				<li><?php echo $total_adult_count;?> Adults<?php if( $total_child_count > 0){?> <?php echo $total_child_count;?> Child<?php } ?></li>
		  				<li><?php echo $search_data['room_count'];?> Room</li>
		  				<li><?php echo $search_data['no_of_nights'];?> Nights</li>
		  			</ul>
		  		</div>
		  		<a class="vw_amnty" href="#" data-target="#roomCancelModalnew"  data-toggle="modal" href="#">View Amenities and Policies</a>
		  	</div>
		  </div>
		<!-- <div class="col-xs-12 topalldesc">
			<div class="col-xs-12 nopad">
				<div class="bookcol">
			  <div class="hotelistrowhtl">
				<div class="col-md-4 nopad xcel">
				  <div class="imagehotel">
				  		<?php if($pre_booking_params['HotelImage']!='/'):?>

				  		<?php
				  			$image = $base_url.'/'.base64_encode($pre_booking_params['HotelCode']).'/0';
				  			//$image= $pre_booking_params['HotelImage'];
				  		?>

				  		<img alt="<?=$pre_booking_params['HotelName']?>" src="<?=$image?>">
				  	<?php else:?>
				  		<img alt="Hotel_img" src="<?=$GLOBALS['CI']->template->template_images('default_hotel_img.jpg')?>" class="lazy h-img">
				  	<?php endif;?>
				  </div>
				</div>
				<div class="col-md-8 padall10 xcel">
				  <div class="hotelhed"><?=$pre_booking_params['HotelName']?></div>
				  <div class="clearfix"></div>
				  <div class="bokratinghotl rating-no">				   
					  <?=print_star_rating($pre_booking_params['StarRating'])?>
				  </div>
				  <div class="clearfix"></div>
				  <div class="mensionspl"> <?=$pre_booking_params['HotelAddress']?> </div>
				  <div class="bokkpricesml">
					<div class="travlrs"><span class="travlrsnms">Travelers:</span><span class="fa fa-male"></span> <?=array_sum($search_data['adult_config'])?><span class="fa fa-child"></span> <?=array_sum($search_data['child_config'])?></div>
					<div class="totlbkamnt grandtotal"> <span class="ttlamtdvot">Total Amount</span><?=$this->currency->get_currency_symbol($pre_booking_params['default_currency'])?> <?=($hotel_total_price)?>/-</div>
					</div>
				</div>
			  </div>
			</div>
			</div>		
		</div> -->
		<div class="clearfix"></div>
		<div class="col-xs-12 padpaspotr">
		<div class="col-xs-12 nopadding">
		<div class="fligthsdets">
		<?php
	
/**
 * Collection field name 
 */
//Title, Firstname, Middlename, Lastname, Phoneno, Email, PaxType, LeadPassenger, Age, PassportNo, PassportIssueDate, PassportExpDate
$total_adult_count	= array_sum($search_data['adult_config']);
$total_child_count	= array_sum($search_data['child_config']);

//------------------------------ DATEPICKER START
$i = 1;
$datepicker_list = array();
if ($total_adult_count > 0) {
	for ($i=1; $i<=$total_adult_count; $i++) {
		$datepicker_list[] = array('adult-date-picker-'.$i, ADULT_DATE_PICKER);
	}
}

if ($total_child_count > 0) {
	for ($i=$i; $i<=($total_child_count+$total_adult_count); $i++) {
		$datepicker_list[] = array('child-date-picker-'.$i, CHILD_DATE_PICKER);
	}
}
$GLOBALS['CI']->current_page->set_datepicker($datepicker_list);
//------------------------------ DATEPICKER END
$total_pax_count	= $total_adult_count+$total_child_count;
//First Adult is Primary and and Lead Pax
$adult_enum = $child_enum = get_enum_list('title');
$gender_enum = get_enum_list('gender');
unset($adult_enum[MASTER_TITLE]); // Master is for child so not required
unset($child_enum[MASTER_TITLE]); // Master is not supported in TBO list
unset($adult_enum[MISS_TITLE]); // Miss is not supported in GRN list
unset($child_enum[MISS_TITLE]);
unset($child_enum[C_MRS_TITLE]);
unset($adult_enum[A_MASTER]);
$adult_title_options = generate_options($adult_enum, false, true);
$child_title_options = generate_options($child_enum, false, true);
$gender_options	= generate_options($gender_enum);
$nationality_options = generate_options($iso_country_list, array(INDIA_CODE));//FIXME get ISO CODE --- ISO_INDIA
$passport_issuing_country_options = generate_options($country_list);
//lowest year wanted
$cutoff = date('Y', strtotime('+20 years'));
//current year
$now = date('Y');
$day_options	= generate_options(get_day_numbers());
$month_options	= generate_options(get_month_names());
$year_options	= generate_options(get_years($now, $cutoff));
/**
 * check if current print index is of adult or child by taking adult and total pax count
 * @param number $total_pax		total pax count
 * @param number $total_adult	total adult count
 */
function is_adult($total_pax, $total_adult)
{
	return ($total_pax>$total_adult ?	false : true);
}

/**
 * check if current print index is of adult or child by taking adult and total pax count
 * @param number $total_pax		total pax count
 * @param number $total_adult	total adult count
 */
function is_lead_pax($pax_count)
{
	return ($pax_count == 1 ? true : false);
}
$lead_pax_details = @$pax_details[0];
 ?>
		<form action="<?=base_url().'index.php/hotel/pre_booking/'.$search_data['search_id']?>" method="POST" autocomplete="off">
		<div class="hide">
			<?php $dynamic_params_url = serialized_data($pre_booking_params);?>
			<input type="hidden" required="required" name="token"		value="<?=$dynamic_params_url;?>" />
			<input type="hidden" required="required" name="token_key"	value="<?=md5($dynamic_params_url);?>" />
			<input type="hidden" required="required" name="op"			value="book_flight">
			<input type="hidden" required="required" name="booking_source"		value="<?=$booking_source?>" readonly>
			<input type="hidden" required="required" name="promo_code_discount_val" id="promo_code_discount_val" value="0.00" readonly>
			<input type="hidden" required="required" name="promo_code" id="promocode_val" value="" readonly>
			<input type="hidden" required="required" name="promo_actual_value" id="promo_actual_value" value="" readonly>
		</div>
			 <div class="flitab1">
			<div class="moreflt boksectn">
					<div class="ontyp">
						<div class="labltowr arimobold">Please enter the customer names.</div>
	<div class="adult_in">
		<div class="ad_lbl">
			<strong><img src="<?=$GLOBALS['CI']->template->template_images('adult1.svg')?>" alt="img/svg" /> Adult</strong>
			<!--<span>0/3  added</span>-->
		</div>

<?php
	$child_age = @$search_data['child_age'];
	$search_child_age = @$search_data['child_age'];
	if(is_logged_in_user()) {
		$traveller_class = ' user_traveller_details ';
	} else {
		$traveller_class = '';
	}
	$child_age_index=0;
	for($pax_index=1; $pax_index <= $total_pax_count; $pax_index++) {//START FOR LOOP FOR PAX DETAILS
	$cur_pax_info = is_array($pax_details) ? array_shift($pax_details) : array();
?>
	<div class="pasngrinput _passenger_hiiden_inputs">
		<div class="hide hidden_pax_details">
		<?php
		if(is_adult($pax_index, $total_adult_count) == true) {
			 $static_date_of_birth = date('Y-m-d', strtotime('-30 years'));;
			 } else {//child
			 	$static_date_of_birth = date('Y-m-d', strtotime('-'.intval(array_shift($child_age)).' years'));;
			 	$child_age_index++;
			 }
			 $passport_number = rand(1111111111,9999999999);
		  ?>
			<input type="hidden" name="passenger_type[]" value="<?=(is_adult($pax_index, $total_adult_count) ? 1 : 2)?>">
			<input type="hidden" name="lead_passenger[]" value="<?=(is_lead_pax($pax_index) ? true : false)?>">
			<input type="hidden" name="date_of_birth[]" value="<?=$static_date_of_birth?>">
			
			<input type="hidden" required="required" name="passenger_nationality[]" id="passenger-nationality-<?=$pax_index?>" value="92">
			<!-- Static Passport Details -->
			<input type="hidden" name="passenger_passport_number[]" value="<?=$passport_number?>" id="passenger_passport_number_<?=$pax_index?>">
			<input type="hidden" name="passenger_passport_issuing_country[]" value="<?=$passport_issuing_country?>" id="passenger_passport_issuing_country_<?=$pax_index?>">
			<input type="hidden" name="passenger_passport_expiry_day[]" value="<?=$static_passport_details['passenger_passport_expiry_day']?>" id="passenger_passport_expiry_day_<?=$pax_index?>">
			<input type="hidden" name="passenger_passport_expiry_month[]" value="<?=$static_passport_details['passenger_passport_expiry_month']?>" id="passenger_passport_expiry_month_<?=$pax_index?>">
			<input type="hidden" name="passenger_passport_expiry_year[]" value="<?=$static_passport_details['passenger_passport_expiry_year']?>" id="passenger_passport_expiry_year_<?=$pax_index?>">
		</div>
		<div class="col-xs-12 nopadding">
			<?php
				if($search_child_age){
					if($child_age_index>0){
						$child_yr = '';
						if(isset($search_child_age[$child_age_index-1])){
							if($search_child_age[$child_age_index-1]>1){
								$child_yr = '<br/>'.' '.$search_child_age[$child_age_index-1].' years old';
							}else{
								$child_yr = '<br/>'.$search_child_age[$child_age_index-1].' year old';
							}
						}
					}
					
					
				}
			?>
		   <div class="adltnom"><i class="fas fa-check-square"></i> <?=(is_adult($pax_index, $total_adult_count) ? 'Adult' : 'Child' .$child_yr )?><?=(is_lead_pax($pax_index) ? '- Lead Pax' : '')?></div>
		
		 </div>
		 <div class="col-xs-12 nopadding">
		 <div class="inptalbox">
		 	
				<div class="btn-group<?php echo $pax_index;?>" data-toggle="buttons">
					<label class="btn btn-primary active">
						<input type="radio" name="gender<?php echo $pax_index?>" checked="checked" value="1"> MALE
					</label>
					<label class="btn btn-primary">
						<input type="radio" name="gender<?php echo $pax_index?>" value="2"> FEMALE
					</label>
					<label class="btn btn-primary">
						<input type="radio" name="gender<?php echo $pax_index?>" value="3"> Non-binary
					</label>
					<label class="btn btn-primary">
						<input type="radio" name="gender<?php echo $pax_index?>" value="4"> Opt Out 
					</label>
				</div>
			
		 	<div class="clearfix"></div>
			<div class="col-xs-3 spllty">
			
				<label class="cst_lbl">Title</label>
			<select class="mySelectBoxClass flyinputsnor name_title" name="name_title[]" required>
			<?php echo (is_adult($pax_index, $total_adult_count) ? $adult_title_options : $child_title_options)?>
			</select>
		
			</div>
			<div class="col-xs-4 spllty">
				<label class="cst_lbl">First Name <sup class="text-danger">*</sup></label>
			  	<input value="<?=@$cur_pax_info['first_name']?>" required="required" type="text" name="first_name[]" id="passenger-first-name-<?=$pax_index?>" class="clainput alpha_space <?=$traveller_class?>"  minlength="2" maxlength="45" placeholder="Enter First Name" data-row-id="<?=($pax_index);?>"/>
			  
			</div>
		
			<div class="col-xs-4 spllty">
				<label class="cst_lbl">Last Name <sup class="text-danger">*</sup></label>
			 	<input value="<?=@$cur_pax_info['last_name']?>" required="required" type="text" name="last_name[]" id="passenger-last-name-<?=$pax_index?>" class="clainput alpha_space last_name" minlength="2" maxlength="45" placeholder="Enter Last Name" />
			 </div>
		</div>
		</div>
	</div>
<?php
}//END FOR LOOP FOR PAX DETAILS
?>
	</div>
					</div>
				</div>
				<!---Added by ela-->
				<div>
					<?php 
					   if($RateComments){
					     echo "<div class='labltowr'>RateComments </div>";
					   	 foreach ($RateComments as $r_key => $r_value) {
					   	 	echo "<p>".$r_value."</p>";
					   	 }
					   }
					?>
				</div>
				<!--End -->
				<div class="ontyp hide">
										<div class="kindrest">
										<div class="cartlistingbuk">
													<div class="cartitembuk">
														<div class="col-md-12">
															<div class="payblnhmxm">Have an e-coupon or a deal-code ? (Optional)</div>
														</div>
													</div>
													<div class="clearfix"></div>
													<div class="cartitembuk prompform">
													
													</div>
													<div class="loading hide" id="loading"><img src="<?php echo $GLOBALS['CI']->template->template_images('loader_v3.gif')?>"></div>
													<div class="clearfix"></div>
													<div class="savemessage"></div>
												</div>
											</div>
											</div>
				<div class="clearfix"></div>
				<div class="contbk">
					<div class="contcthdngs">Contact Information</div>
					<div class="hide">
					<input type="hidden" name="billing_country" value="92">
					<input type="hidden" name="billing_city" value="test">
					<input type="hidden" name="billing_zipcode" value="test">
					<input type="hidden" name="billing_address_1" value="test">
					</div>
					<div class="cont_info">
					<div class="col-xs-12 col-md-12 nopad">
					<div class="emailperson col-xs-12 col-md-4 nopad">
					<label class="cst_lbl">Mail ID</label>
					<input value="<?=@$lead_pax_details['email']?>" type="text" maxlength="80" required="required" id="billing-email" class="newslterinput nputbrd" placeholder="Your Mail ID" name="billing_email">
					</div>
					<div class="col-xs-6 col-md-4 nopadding">
					<label class="cst_lbl">Country Code</label>
					<select name="phone_country_code" class="newslterinput nputbrd _numeric_only " id="after_country_code" required>
											<?php echo diaplay_phonecode($phone_code,$active_data, $user_country_code); ?>
										</select> 
					</div>
					<!-- <div class="col-xs-1"><div class="sidepo">-</div></div> -->
					<div class="col-xs-6 col-md-4 nopadding">
					<label class="cst_lbl">Mobile No</label>
					<input value="<?=@$lead_pax_details['phone'] == 0 ? '' : @$lead_pax_details['phone'];?>" type="text" name="passenger_contact" id="passenger-contact" placeholder="Mobile Number" class="newslterinput nputbrd _numeric_only" maxlength="12" required="required">
					</div>
					<div class="clearfix"></div>
					</div>
					<div class="clearfix"></div>
					<div class="notese">Your booking details & update alerts will be send to this contacts.</div>
				</div>
				</div>
				<div class="clikdiv">
					 <div class="squaredThree">
					 <input id="terms_cond1" type="checkbox" name="tc" checked="checked" required="required">
					 <label for="terms_cond1"></label>
					 </div>
					 <span class="clikagre">
					  By Clicking Continue Booking, I confirm that I have read and accept the <a href="<?php echo base_url();?>index.php/terms-conditions" target="_blank">Terms and Conditions</a> & <a href="<?php echo base_url();?>index.php/privacy-policy" target="_blank">Privacy Policies</a>
					 </span>
				</div>
				<div class="clearfix"></div>
				<div class="loginspld">
					<div class="collogg">
						<?php
						//If single payment option then hide selection and select by default
						if (count($active_payment_options) == 1) {
							$payment_option_visibility = 'hide';
							$default_payment_option = 'checked="checked"';
						} else {
							$payment_option_visibility = 'show';
							$default_payment_option = '';
						}
						?>
						<div class="row <?=$payment_option_visibility?>">
							<?php if (in_array(PAY_NOW, $active_payment_options)) {?>
								<div class="col-md-3">
									<div class="form-group">
										<label for="payment-mode-<?=PAY_NOW?>">
											<input <?=$default_payment_option?> name="payment_method" type="radio" required="required" value="<?=PAY_NOW?>" id="payment-mode-<?=PAY_NOW?>" class="form-control b-r-0" placeholder="Payment Mode">
											Pay Now
										</label>
									</div>
								</div>
							<?php } ?>
							<?php if (in_array(PAY_AT_BANK, $active_payment_options)) {?>
								<div class="col-md-3">
									<div class="form-group">
										<label for="payment-mode-<?=PAY_AT_BANK?>">
											<input <?=$default_payment_option?> name="payment_method" type="radio" required="required" value="<?=PAY_AT_BANK?>" id="payment-mode-<?=PAY_AT_BANK?>" class="form-control b-r-0" placeholder="Payment Mode">
											Pay At Bank
										</label>
									</div>
								</div>
							<?php } ?>
							</div>
						<div class="continye col-xs-3">
							<button id="flip" class="bookcont" type="submit">Continue</button>
						</div>
							
						<div class="clearfix"></div>
						<div class="sepertr"></div>
						
						<div class="temsandcndtn hide">
						Most countries require travelers to have a passport valid for more than 3 to 6 months from the date of entry into or exit from the country. Please check the exact rules for your destination country before completing the booking.
						</div>
					</div>
				</div>
			</div>
			</form>
				
			</div>
		</div>
		<!-- ROOM COMBINATION END -->
	 	<?php if(is_logged_in_user() == true) { ?>
			<div class="col-xs-4 nopadding hide">
				<div class="insiefare">
					<div class="farehd arimobold">Passenger List</div>
					<div class="fredivs">
						<div class="psngrnote">
							<?php
								if(valid_array($traveller_details)) {
									$traveller_tab_content = 'You have saved passenger details in your list,on typing, passenger details will auto populate.';
								} else {
									$traveller_tab_content = 'You do not have any passenger saved in your list, start adding passenger so that you do not have to type every time. <a href="'.base_url().'index.php/user/profile?active=traveller" target="_blank">Add Now</a>';
								}
							?>
							<?=$traveller_tab_content;?>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
		</div>
		</div>
		<div class="clearfix"></div>		
			<!-- ROOM COMBINATION START -->
		<div id="room-list" class="room-list romlistnh hide">
			<?php echo $loading_image;?>
		</div>
	</div>
 </div>

	</div>
</div>
<?php //debug($pre_booking_params);exit;?>
<span class="hide">
	<input type="hidden" id="pri_journey_date" value='<?=date('Y-m-d',strtotime($search_data['from_date']))?>'>
</span>
<div class="modal fade bs-example-modal-lg" id="roomCancelModalnew" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h5 class="modal-title" id="myModalLabel">Cancellation Policy</h5>
				
				<div class="imghtltrpadv hide">
				  <img src="" id="trip_adv_img">
				</div>
			</div>
			<div class="modal-body">
				<?php //debug($pre_booking_params);exit;?>
				<?php

?>
<p><?php echo ucfirst(strtolower($RoomTypeName));?></p>
 
<?php if(valid_array($room_facilities)){?>
<div class="room_amnt">
	<h5>Room Facilities</h5>
	<ul>
		<?php foreach($room_facilities as $facility){?>
		<li><?php echo $facility;?></li>
		<?php } ?>
	</ul>               
</div>
<?php } ?>

<?php if(valid_array($Boardingdetails)){?>
<div class="room_amnt">
	<h5>Room Inclusions</h5>
	<ul>
		<?php foreach($Boardingdetails as $inclusion){?>
		<li><?php echo $inclusion;?></li>
		<?php } ?>
	</ul>               
</div>
<?php }else{?>
<h5>Room Inclusions</h5>
	<ul>
		<li>Room Only</li>
	</ul> 
<?php } ?>
<div class="clearfix"></div> 
<div class="room_amnt">
	<h5>Cancellation Policy</h5>
	<p><?php echo $pre_booking_params['CancellationPolicy'][0]; ?></p>                
</div>

				

				
			</div>
			<div class="modal-footer">
	          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	        </div>
		</div>
	</div>
</div>
<?php
//debug($pre_booking_params);exit;
/**
 * This is used only for sending hotel room request - AJAX
 */
$hotel_room_params['HotelCode']		= $pre_booking_params['HotelCode'];
$hotel_room_params['ResultIndex']	= $pre_booking_params['ResultIndex'];
$hotel_room_params['booking_source']		= $pre_booking_params['booking_source'];
$hotel_room_params['TraceId']		= "";
$hotel_room_params['RoomTypeCode']		=  $pre_booking_params['roomTypeCode'];
$hotel_room_params['search_id']		= $pre_booking_params['search_id'];
$hotel_room_params['op']			= 'get_room_details';
//debug($hotel_room_params);exit;
?>
<script type="text/javascript">
	$(document).ready(function(){
		$(window).scroll(function() {
			
  if ($(this).scrollTop() < 500) {
	  $("#room-list").addClass('hide');
  }
		});
		function goToByScroll(id){
       		$('html,body').animate({
            scrollTop: $("#"+id).offset().top},
            'slow');
    	}

    $(".view_rooms").click(function(e) { 
          // Prevent a page reload when a link is pressed
        e.preventDefault(); 
          // Call the scroll function
		$("#room-list").removeClass('hide');
        goToByScroll('room-list');           
    });
		var ResultIndex = '';
	var HotelCode = '';
	var TraceId = '';
	var booking_source = '';
	var RoomTypeCode = '';
	var op = 'get_room_details';
	function load_hotel_room_details()
	{
		var _q_params = <?php echo json_encode($hotel_room_params)?>;
		if (booking_source) { _q_params.booking_source = booking_source; }
		if (ResultIndex) { _q_params.ResultIndex = ResultIndex; }
		if (HotelCode) { _q_params.HotelCode = HotelCode; }
		if (TraceId) { _q_params.TraceId = TraceId; }
		if (RoomTypeCode) { _q_params.RoomTypeCode = RoomTypeCode; }
		$.post(app_base_url+"index.php/ajax/get_room_details", _q_params, function(response) {
		  if (response.hasOwnProperty('status') == true && response.status == true) {
              $('#room-list').html(response.data);
              var _hotel_name = "<?php echo preg_replace('/^\s+|\n|\r|\s+$/m', '', $pre_booking_params['HotelName']); //Hotel Name comes from hotel info response  ?>";
              var _hotel_star_rating = <?php echo abs($pre_booking_params['StarRating']) ?>;
              var _hotel_image = "<?php echo $pre_booking_params['HotelImage']; ?>";
              var _hotel_address = "<?php echo preg_replace('/^\s+|\n|\r|\s+$/m', '', $pre_booking_params['HotelAddress']); ?>";
              $('[name="HotelName"]').val(_hotel_name);
              $('[name="StarRating"]').val(_hotel_star_rating);
              $('[name="HotelImage"]').val(_hotel_image);//Balu A
              $('[name="HotelAddress"]').val(_hotel_address);//Balu A
        }
		});
	}
	load_hotel_room_details(); 
	});  
</script>
<!-- 
<script type="text/javascript">
	$(document).ready(function(){
    $(document).on('scroll', function(){
        if ($('#slidebarscr')[0].offsetTop < $(document).scrollTop()){
        	var top = $(document).scrollTop();
        	var height = $(window).height();
        	/*bottom height*/
        	var scrollHeight = $(document).height();
			var scrollPosition = $(window).height() + $(window).scrollTop();
			var bottom = (scrollHeight - scrollPosition) / scrollHeight;
			
			//console.log("bottom"+bottom);
			// if ((scrollHeight - scrollPosition) / scrollHeight === 0) {
			//     // when scroll to bottom of the page
			// }
        	//alert(height);
        	//alert("bottomsssss"+bottom);
        	if((top >= 150) && (bottom >=0.25))  // || (height > 300))
        	{
        		//alert(bottom+top);
        		$("#slidebarscr").css({position: "fixed", top:0});  	
        	}else 
        	{
        		//alert('bottom '+top);
        		$("#slidebarscr").css({position: "",top:0});  	
        	} 
        	/*else if((top >= 285) && (top < 300))
        	{
        		$("#slidebarscr").css({position: "fixed", top:0});  	
        	}*/
        	                     
        }
    });  
});
</script>
<script type="text/javascript">
	$(document).ready(function(){
    $(document).on('scroll', function(){
        if ($('#nxtbarslider')[0].offsetTop < $(document).scrollTop()){
        	var top = $(document).scrollTop();
        	var height = $(window).height();
        	var scrollHeight = $(document).height();
			var scrollPosition = $(window).height() + $(window).scrollTop();
			var bottom = (scrollHeight - scrollPosition) / scrollHeight;
        	//alert(top);
        	if(((top >= 243) || (height < 300)) && (bottom >=0.2))
        	{
        		//alert(top);
        		$("#nxtbarslider").css({position: "fixed", top:0});  	
        	}else  
        	{
        		$("#nxtbarslider").css({position: "", top:0});  	
        	}         
            
        }
    });  
});
</script> -->
<script type="text/javascript">
	$("#back_btn").click(function (){
	 window.location.href = '<?php echo $hotel_details_url;?>';
	});
	
</script>

<?php 

function diaplay_phonecode(array $phone_code, array $active_data, string $user_country_code = ''): string
    {
    $list = '<option value="">Select Country Code</option>';

    foreach ($phone_code as $code) {
        $isSelected = false;

        if (!empty($user_country_code) && $user_country_code === $code['country_code']) {
            $isSelected = true;
        }

        if (empty($user_country_code) && ($active_data['api_country_list_fk'] === $code['origin'])) {
            $isSelected = true;
        }

        $selected = $isSelected ? 'selected' : '';
        $list .= "<option value=\"{$code['country_code']}\" {$selected}>{$code['name']} {$code['country_code']}</option>";
    }

    return $list;
}
?>
<?php
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/hotel_booking.js'), 'defer' => 'defer');
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/booking_script.js'), 'defer' => 'defer');?>