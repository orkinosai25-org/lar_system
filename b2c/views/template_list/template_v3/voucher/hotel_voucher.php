<?php


   $booking_details = $data['booking_details'][0];

  // debug($booking_details);exit;
   $itinerary_details = $booking_details['itinerary_details'][0];
   $attributes = json_decode($booking_details['attributes'],true);
  // debug($booking_details['booking_source'];die;
   if($booking_details['booking_source']=='PTBSID0000000011'){
     $currency_objd = new Currency ( array (
                  'module_type' => 'flight',
                  'from' => 'INR',
                  'to' => 'USD' 
               ) );
     
         $currency_conversion_rate1 = $currency_objd->getConversionRate();
         //debug($currency_conversion_rate);die;
         $display_amount = roundoff_number($payment_data['amount']*$currency_conversion_rate);

   }else{
      $currency_conversion_rate1=1;
   }
    
   $customer_details = $booking_details['customer_details'];
   
   $domain_details = $booking_details;
   $lead_pax_details = $booking_details['customer_details'];
  
//debug($attributes);exit;
   if(valid_array($attributes['Boarding_details'])){
		foreach($attributes['Boarding_details'] as $b_value){
             $inclusions[] = $b_value;                                      
		}
   }
	else{
	 $inclusions[] = 'Room Only';
	}

?>
<style type="text/css">
.st_lbl .label {
    padding: 0;
    font-size: 20px; line-height: 19px;
    font-weight: 600;
    background: none !important;
    color: #BF9766 !important
}
.star_in {
    position: absolute;
    right: 0;
    top: 18px;
}
.star_in strong {
    position: absolute;
    left: 0;
    right: 0;
    color: #fff;
    text-align: center;
    font-weight: 400;
    font-size: 11px;
    line-height: 24px;
}
.star_in i {
    font-size: 22px;
    color: #BF9766;
    line-height: 22px;
}
table li {
    list-style: disc;
    /* padding-left: 30px; */
}

table ul {
    padding-left: 30px;
}
</style>
<div class="table-responsive" style="width:100%; position:relative; padding: 10px 0" id="tickect_hotel">
   <table cellspacing="0" width="100%" style="font-size:13px; font-family: 'Poppins', sans-serif; width:900px; margin:20px auto;background-color:#fff; padding:50px 45px;">
      <tbody>
         <tr>
            <td style="border-collapse: collapse; padding: 25px 35px;box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15)" >
               <table width="100%" style="border-collapse: collapse;" cellpadding="0" cellspacing="0" border="0">
                  <tr>
                     <td style="padding: 0px;">
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse: collapse;">
                           <tr>
                              <td style="padding-bottom: 15px;">
                                 <table width="100%" style="border-collapse: collapse; background: #FAFAFA; vertical-align: middle;" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
										
                                       <td style=" vertical-align: middle;font-size:24px; line-height:38px;font-weight:600; text-align:left; padding-left: 12px">Luxury Africa Resorts</td>
                                       <td style=" vertical-align: middle;padding: 0 12px 7.5px; text-align: right;"><img style="height: 87px;" src="<?=$GLOBALS['CI']->template->domain_images($data['logo'])?>"></td>
                                    </tr>
                                 </table>
                              </tr>
                           <tr>
                              <td>
                                 <table width="100%" style="border-collapse: collapse;" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                       <td style="font-size:20px; line-height:32px; font-weight:500; text-align:left;vertical-align: bottom;">Hotel Voucher</td>
                                       <td style="padding: 0px;">
                                          <table width="100%" style="font-size:13px; font-family: 'Poppins', sans-serif;border-collapse: collapse;text-align: right; line-height:18px;" cellpadding="0" cellspacing="0" border="0">
                                             <!-- <tr>
                                                <td style="font-size:14px;"><span style="width:100%; float:left"><?php echo $data['address'];?></span></td>
                                                </tr> -->
                                             <tr>
                                                <td style="font-size:17px;line-height:24px;padding-bottom:10px;" align="right"><span>Booking ID: <?php echo $booking_details['booking_id']; ?></span></td>
                                             </tr>
                                             <tr>
                                                <td style="font-size:17px;line-height:24px;padding-bottom:10px;" align="right"><span>Booked Date : <?php echo date("d M Y",strtotime($booking_details['created_datetime'])); ?></span></td>
                                             </tr>
                                             <tr>
                                                <td class="st_lbl" align="right" style="line-height:24px;;padding-bottom:10px;font-size:17px;">Status: <strong class="<?php echo booking_status_label( $booking_details['status']);?>" style="font-size:17px;">
                                                   <?php 
                                                      switch($booking_details['status']){
                                                         case 'BOOKING_CONFIRMED': echo 'CONFIRMED';break;
                                                         case 'BOOKING_CANCELLED': echo 'CANCELLED';break;
                                                         case 'BOOKING_FAILED': echo 'FAILED';break;
                                                         case 'BOOKING_INPROGRESS': echo 'INPROGRESS';break;
                                                         case 'BOOKING_INCOMPLETE': echo 'INCOMPLETE';break;
                                                         case 'BOOKING_HOLD': echo 'HOLD';break;
                                                         case 'BOOKING_PENDING': echo 'PENDING';break;
                                                         case 'BOOKING_ERROR': echo 'ERROR';break;
                                                      }                         
                                                      ?>
                                                   </strong>
                                                </td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                           <tr><td style="line-height:20px;">&nbsp;</td></tr>                           
                           <tr>
                              <td style="border: 2px solid color(srgb 0.25 0.25 0.25 / 0.25); padding: 15px;">
                                 <table width="100%" cellpadding="5" style="padding: 10px;font-size: 13px;padding:5px;">
                                    <tbody>
                                    <tr>
                                       <?php if($attributes['HotelImage'] != '/'):?>
                                          <td style="padding:0 0 15px"><img style="width:251px;height: 164px;" src="<?=$attributes['HotelImage'];?>" /></td>
                                       <?php else:?>
                                          <td style="padding:0 0 15px"><img style="width:251px;height: 164px;" src="<?=$GLOBALS['CI']->template->template_images("default_hotel_img.jpg");?>" /></td>
                                       <?php endif;?>
                                       <td valign="top" style="padding:10px 20px 10px;width: 69%; position: relative;"><span style="line-height:30px;font-size:24px;line-height: 30px;color:#3F3F3F;vertical-align:middle;font-weight: 400;padding-bottom: 0;"><?php echo $booking_details['hotel_name']; ?></span><br>
                                          <span class="star_in"><i class="fas fa-star"></i><strong><?php echo $booking_details['star_rating']; ?></strong></span><br>
                                          <span style="display: block;font-size: 17px;line-height: 25px;"><i style="margin-right: 3px; color: #BF9766" class="fas fa-map-marker-alt"></i> <?php echo $booking_details['hotel_address']; ?> </span><br><span style="display: none;line-height:22px;font-size: 13px;"><img style="width:70px;" src="<?php echo $GLOBALS['CI']->template->template_images('star_rating-'.$attributes["StarRating"].'.png'); ?>" /></span></td>

                                       <!-- <td width="32%" style="padding:10px 0;text-align: center;"><span style="font-size:14px; border:2px solid #ccc; display:block"><span style="color:#BF9766;padding:5px; display:block;text-transform:uppercase">Booking ID</span><span style="font-size:14px;line-height:35px;padding-bottom: 5px;display:block;font-weight: 600;"><?php echo $booking_details['booking_id']; ?></span></span></td> -->
                                    </tr>
                                    <tr>
                                       <td style="border-top: 1.5px dashed color(srgb 0.87 0.69 0.2 / 0.5);padding-top:15px;font-size: 16px;line-height: 30px;">
                                          <span style="font-weight: 500;">Check-In</span><br>
                                          <span>Thu, <?=@date("d M Y",strtotime($itinerary_details['check_in']))?></span>
                                       </td>
                                       <td style="border-top: 1.5px dashed color(srgb 0.87 0.69 0.2 / 0.5);padding-top:15px;text-align: right;font-size: 16px;line-height: 30px;">
                                          <span style="font-weight: 500;">Check-Out</span><br>
                                          <span>Thu, <?=@date("d M Y",strtotime($itinerary_details['check_out']))?></span>
                                       </td>
                                    </tr>
                                    </tbody>
                                 </table>
                              </td>
                           </tr>
                           <tr><td style="line-height:20px;">&nbsp;</td></tr>      
                           <tr>
                              <td style="background-color:#FAF7F4B2;color:#3F3F3F; font-size:14px; padding:15px; border-bottom: 1px dashed #93908A"><img style="vertical-align:middle;margin-right: 5px" src="<?=SYSTEM_IMAGE_DIR.'hotel_v.svg'?>" /> <span style="font-size:16px; line-height: 24px; color:#3F3F3F;vertical-align:middle;font-weight: 500;"> &nbsp;Room Summary:</span></td>
                           </tr>
                           <tr>
                              <td  width="100%" style="padding:0px;">
                                 <table width="100%" cellpadding="5" style="padding: 10px;font-size: 16px; line-height: 24px; padding:5px;">
                                    <tr>
                                       <!-- <td>Phone</td> -->
                                       <td style="background-color:#FAF7F4B2;padding:15px;color: #3F3F3F;">Room Type</td>
										 <td style="background-color:#FAF7F4B2;padding:15px;color: #3F3F3F;text-align:center">Inclusions</td>
                                       <td style="background-color:#FAF7F4B2;padding:15px;color: #3F3F3F;text-align:center">No of Room's</td>
                                       <td style="background-color:#FAF7F4B2;padding:15px;color: #3F3F3F;text-align:center">Adult's</td>
                                       <td style="background-color:#FAF7F4B2;padding:15px;color: #3F3F3F;text-align:center">Children</td>
                                    </tr>
                                    <tr>
                                       <td style="padding:15px;background-color:#F7F3EDCC; color: #3F3F3F"><?php echo $itinerary_details['room_type_name']; ?></td>
										 <td style="padding:15px;background-color:#F7F3EDCC; color: #3F3F3F" align="center"><?php echo implode(", ", $inclusions); ?></td>
                                       <td style="padding:15px;background-color:#F7F3EDCC; color: #3F3F3F" align="center"><?php echo $booking_details['total_rooms']; ?></td>
                                       <td style="padding:15px;background-color:#F7F3EDCC; color: #3F3F3F" align="center"><?php echo $booking_details['adult_count']; ?></td>
                                       <td  style="padding:15px;background-color:#F7F3EDCC; color: #3F3F3F" align="center"><?php echo $booking_details['child_count']; ?></td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                           <tr><td style="line-height:20px;">&nbsp;</td></tr>
                           <tr>
                             <td style="background-color:#FAF7F4B2;color:#3F3F3F; font-size:14px; padding:15px; border-bottom: 1px dashed #93908A"><img style="vertical-align:middle;margin-right: 5px" src="<?=SYSTEM_IMAGE_DIR.'people_group.svg'?>" /> <span style="font-size:16px; line-height: 24px; color:#3F3F3F;vertical-align:middle;font-weight: 500;"> &nbsp;Guest Summary:</span></td>
                           </tr>
                           <tr>
                              <td  width="100%" style="padding:0px;">
                                 <table width="100%" cellpadding="5" style="padding: 10px;font-size: 16px; line-height: 24px; padding:5px;">
                                    <tr>
                                       <td style="background-color:#FAF7F4B2;padding:15px;color: #3F3F3F;">Guest Name</td>
                                       <td style="background-color:#FAF7F4B2;padding:15px;color: #3F3F3F;">Guest Type</td>
                                       <td style="background-color:#FAF7F4B2;padding:15px;color: #3F3F3F;">Age</td>
                                    </tr>
                                    <!-- <tr>
                                       <td><?php echo $customer_details['title'].' '.$customer_details['first_name'].' '.$customer_details['middle_name'].' '.$customer_details['last_name'];?></td>
                                                                        <td><?php echo $customer_details['phone'];?></td>
                                                                        <td><?php echo $customer_details['email'];?></td>
                                                                        <td><?php echo $booking_details['cutomer_city'];?></td>
                                                                         
                                                                     </tr>   -->
                                     <?php
                                          $i=1;
                                       ?> 
                                    <?php foreach($customer_details as $details):?>
                                    <tr>
                                       <td style="padding:15px;background-color:#F7F3EDCC; color: #3F3F3F"><?php echo $details['title'].' '.$details['first_name'].' '.$details['last_name']?></td>
                                       <td style="padding:15px;background-color:#F7F3EDCC; color: #3F3F3F;"><?=$details['pax_type']?></td>
                                       <?php
                                          
                                          $age = '-';

                                         $current_date = date('Y-m-d');
                                          $date1 = date_create($current_date);
                                           $date2 = date_create($details['date_of_birth']);
                                          $date_obj = date_diff($date1,$date2);
                                          

                                          if($details['pax_type']=='Child'){
                                             $age = $date_obj->y;
                                          }
                                          $i++;
                                       ?>
                                       <td style="padding:15px;background-color:#F7F3EDCC; color: #3F3F3F;"><?=$age?></td>                                       
                                       <!-- <td style="padding:5px"><?php echo $details['phone'] ?></td>
                                       <td style="padding:5px"><?php echo $details['email']?></td> -->
                                    </tr>
                                    <?php endforeach;?>
                                 </table>
                              </td>
                              <td></td>
                           </tr>
                           <tr><td style="line-height:20px;">&nbsp;</td></tr>
                     <tr>
                        <td colspan="4" style="">
                           <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px; padding:0;">
                              <tbody>
                                 <tr>
                                    <td width="100%" style="padding:10px;border: 2px solid color(srgb 0.71 0.56 0.17 / 0.25);">
                                       <table cellspacing="0" cellpadding="5" width="100%" style="font-size:16px; line-height: 24px; padding:0;">
                                          <tbody>
                                             <tr>
                                                <td style="border-bottom:1px dashed #93908A;padding:12px 15px;font-weight: 500;"><span style=""><img style="vertical-align:middle;margin-right: 5px" src="<?=SYSTEM_IMAGE_DIR.'pymnt.svg'?>" /> Payment Summary</span></td>
                                                <td style="border-bottom:1px dashed #93908A;padding:12px 15px;font-weight: 500;text-align: right;"><span style="">Currency : <strong style="color: #BF9766; font-weight: 500"><?=$booking_details['currency']?></strong></span></td>
                                             </tr>
                                             <tr>
                                                <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;"><span>Base Fare</span></td>
                                                <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;text-align: right;font-weight: 500;"><span><?php echo round($booking_details['fare']*$currency_conversion_rate1+$booking_details['admin_markup']); ?></span></td>
                                             </tr>
                                             <tr>
                                                <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;"><span>Taxes</span></td>
                                                <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;text-align: right;font-weight: 500;"><span><?php echo round($booking_details['convinence_amount']); ?></span></td>
                                             </tr>
                                             <?php if($itinerary_details['gst'] > 0){?>
                                             <tr>
                                                <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;"><span>GST</span></td>
                                                <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;text-align: right;font-weight: 500;"><span><?php echo round($itinerary_details['gst']); ?></span></td>
                                             </tr>
                                            <?php } ?>
                                             <tr>
                                                <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;"><span>Discount</span></td>
                                                <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;text-align: right;font-weight: 500;"><span><?php echo $booking_details['discount']; ?></span></td>
                                             </tr>
                                             
                                             <tr>
                                                <td style="border-top:1px solid #ccc;padding:12px 15px;"><span style="font-weight: 500; color: #525252">Total Amount Charged</span></td>
                                                <td style="border-top:1px solid #ccc;padding:12px 0px;text-align: right;"><span style="font-weight: 500;font-size: 20px; background: #BF9766; color: #fff;padding: 4px 12px"><?=$booking_details['currency']?> <?php echo round($booking_details['grand_total']*$currency_conversion_rate1); ?></span></td>
                                             </tr>
                                          </tbody>
                                       </table>
                                    </td>
                                 </tr>
                                 <tr><td style="line-height:20px;">&nbsp;</td></tr>
                                 <!-- <tr>
                                    <td width="100%" style="padding:10px;border: 1px solid color(srgb 0.71 0.56 0.17 / 0.5);">
                                       <table cellspacing="0" cellpadding="5" width="100%" style="border:1px solid #ccc;font-size:12px; padding:0;">

                                          <tbody>
                                             <tr>
                                                <td style="background-color:#d9d9d9;border-bottom:1px solid #ccc;padding:5px; color:#333"><span style="font-size:13px">Room Inclusions</span></td>
                                             </tr>
                                             <?php if($attributes['Boarding_details']): ?>
                                                <?php foreach($attributes['Boarding_details'] as $b_value):?>
                                                   <tr>
                                                      <td style="padding:5px"><span><?=$b_value?></span></td>  
                                                   </tr>
                                                
                                                <?php endforeach;?>
                                             <?php else:?>
                                                   <tr>
                                                      <td style="padding:5px"><span>Room Only</span></td>  
                                                   </tr>
                                             <?php endif;?>
                                             <tr>
                                                <td style="padding:5px"><span style="font-size:10px; color:#666; line-height:20px;">* Room inclusions are subject to change with Hotels.</span></td>
                                             </tr>
                                          </tbody>
                                       </table>
                                    </td>
                                 </tr> -->
                              </tbody>
                           </table>
                        </td>
                     </tr>
                     <!-- <tr><td style="line-height:20px;">&nbsp;</td></tr>
                     <tr><td align="center" colspan="4" style="border-bottom:1px solid #ccc;padding-bottom:15px"><span style="font-size:13px; color:#555;">Customer Contact Details | E-mail : <?=$customer_details[0]['email']?> | Contact No : <?=$booking_details['phone_code']." ".$customer_details[0]['phone']?></span></td></tr>
                     <tr><td style="line-height:20px;">&nbsp;</td></tr> -->
                     <tr>
                        <td colspan="4" style="background: #F9F9F9CC; padding: 15px;">
                           <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px; padding:0;">
                              <tbody>
                                 <tr>
                                 <td colspan="4"><span style="line-height:26px;font-size: 15px;font-weight: 500; color: #3f3f3f; padding-bottom: 5px;">Cancellation Policy</span></td></tr>
                                 <tr>
                                    <td colspan="4" style="line-height:20px; font-size:13px; color:#3f3f3f;padding-bottom: 5px;padding-top:5px"><ul><li><?=$booking_details['cancellation_policy'][0]?></li></ul></td>
                                 </tr>
                                 <tr><td style="line-height:10px;">&nbsp;</td></tr>
                                 <tr>
                                 <td colspan="4"><span style="line-height:26px;font-size: 15px;font-weight: 500; color: #3f3f3f; padding-bottom: 5px;">Terms and Conditions</span></td></tr>
                                 <tr>
                                    <td colspan="4" style="line-height:20px; font-size:13px; color:#3f3f3f; padding-bottom: 5px;padding-top:5px"><?php echo $data['terms_conditions']; ?>
                              </td>
                                 </tr>
                              </tbody>
                           </table>
                        </td>
                     </tr>
                     <!-- <tr><td style="line-height:20px;">&nbsp;</td></tr>
                     
                     <tr>
                     <td colspan="4"><span style="line-height:26px;font-size: 14px;font-weight: 500;">Important Information</span></td></tr>
                     <tr>
                        <td colspan="4" style="border-bottom:1px solid #ccc; line-height:20px; font-size:12px; color:#555">
                           <ul>
                              <li>All Guests, including children and infants, must present valid identification at check-in.</li>
                              <li>Check-in begins 2 hours prior to the flight for seat assignment and closes 45 minutes prior to the scheduled departure.</li>
                              <li>Carriage and other services provided by the carrier are subject to conditions of carriage, which are hereby incorporated by reference. These conditions may be obtained from the issuing carrier.</li>
                              <li>In case of cancellations less than 6 hours before departure please cancel with the airlines directly. We are not responsible for any losses if the request is received less than 6 hours before departure.</li>
                              <li>Please contact airlines for Terminal Queries.</li>
                              <li>Free Baggage Allowance: Checked-in Baggage = 15kgs in Economy class.</li>
                              <li>Partial cancellations are not allowed for Round-trip Fares</li>
                              <li>Changes to the reservation will result in the above fee plus any difference in the fare between the original fare paid and the fare for the revised booking.</li>
                              <li>In case of cancellation of a booking, made by a Go channel partner, refund has to be collected from that respective Go Channel.</li>
                              <li>The No Show refund should be collected within 15 days from departure date.</li>
                              <li>If the basic fare is less than cancellation charges then only statutory taxes would be refunded.</li>
                              <li>We are not be responsible for any Flight delay/Cancellation from airline's end.</li>
                              <li>Kindly contact the airline at least 24 hrs before to reconfirm your flight detail giving reference of Airline PNR Number.</li>
                              <li>We are a travel agent and all reservations made through our website are as per the terms and conditions of the concerned airlines. All modifications,cancellations and refunds of the airline tickets shall be strictly in accordance with the policy of the concerned airlines and we disclaim all liability in connection thereof.</li>
                           </ul>
                        </td>
                     </tr> -->
                     <tr><td style="line-height:50px;">&nbsp;</td></tr>
                     <tr>
                        <td colspan="4" align="center" style="padding:15px 0;font-size:14px;line-height:24px;">For booking issues, email <strong style="font-weight: 500; color: #BF9766"><?php echo $data['email'];?></strong> or  call our 24x7 team on <strong style="font-weight: 500; color: #BF9766"><?=$data['phone_code']?><?=$data['phone']?></strong>.(Booking Reference : <?php echo $booking_details['app_reference']; ?>)</td>
                     </tr>
							<tr>
							  <td style=" vertical-align: middle;font-size:24px; line-height:38px;font-weight:600; text-align:left; padding-left: 12px"><a href="<?php echo base_url().'hotel/hotel_feedback/'.$booking_details['app_reference'];?>">Hotel Feedback</a></td></tr>
                     <tr>
                        <td colspan="4" style="">
                           <table width="100%" style="background: #F9F5F1">
                              <tr>
                                 <td style="text-align: left;padding: 5px 15px;"><span style="line-height: 18px;font-size: 13px;font-weight: 600;color: #3f3f3f;margin-right: 5px">Our Partners</span>
									 <?php if(valid_array($data['hotel_partners'])){
									 	foreach($data['hotel_partners'] as $partner){
									 ?>
                                    <span style="background: #fff; border-radius: 3px; padding: 9px 15px; margin-right: 5px"><img style="height: 50px;" src="<?=$partner;?>"></span>
                                   <?php } } ?>
                                 </td>
								 
                                 <td style="padding: 0 15px;font-weight: 500;font-size: 15px;" align="right"><?php echo $data['domainname'];?></td>
                              </tr>
                           </table>
                        </td>
                     </tr>
                           
                        </table>
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
      </tbody>
   </table>
   <table
      style="border-collapse: collapse;font-size: 14px; margin: 10px auto; font-family: arial;" width="70%" cellpadding="0" cellspacing="0" border="0">
      <tbody>
         <tr>
            <td align="center"><input id="printBtn" style="background:#BF9766; height:34px; padding:5px 15px; border-radius:4px; border:none; color:#fff; margin:0 2px;" type="button" value="Print" />
         </tr>
      </tbody>
   </table>
</div>
<script>
document.getElementById('printBtn').onclick = function () {
    const content = document.getElementById('tickect_hotel');
    if (!content) {
        alert("Content to print not found!");
        return;
    }

    const win = window.open('', '_blank');
    win.document.write('<html><head><title>Print</title>');
    win.document.write('<style>table{border-collapse:collapse; width:100%;}</style>');
    win.document.write('</head><body>');
    win.document.write(content.innerHTML);
    win.document.write('</body></html>');
    win.document.close();

    // Wait for window to finish loading
    win.onload = function () {
        const imgs = win.document.images;
        let loaded = 0;

        if (imgs.length === 0) {
            win.focus();
            win.print();
            win.close();
            return;
        }

        for (let img of imgs) {
            img.onload = img.onerror = function () {
                loaded++;
                if (loaded === imgs.length) {
                    win.focus();
                    win.print();
                    win.close();
                }
            };
        }
    };
};
</script>
