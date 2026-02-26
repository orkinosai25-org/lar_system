<?php
   // echo $this->entity_user_id;exit;
   //debug($data);exit;
   $booking_details = $data ['booking_details'] [0];
   //debug($booking_details);exit;
   $itinerary_details = $booking_details ['booking_itinerary_details'];
   // debug($itinerary_details);exit;
   $attributes = $booking_details ['attributes'];
   $customer_details = $booking_details ['booking_transaction_details'] [0] ['booking_customer_details'];
   $domain_details = $booking_details;
   $lead_pax_details = $customer_details;
   $booking_transaction_details = $booking_details ['booking_transaction_details'];
   
   $adult_count = 0;
   $infant_count = 0;
   
   foreach ($customer_details as $k => $v) {
       if (strtolower($v ['passenger_type']) == 'infant') {
           $infant_count ++;
       } else {
           $adult_count ++;
       }
   }
   
   $Onward = '';
   $return = '';
   if (count($booking_transaction_details) == 2) {
       $Onward = 'Onward ';
       $Return = 'Return ';
   }
   
   // generate onword and return
   if ($booking_details ['is_domestic'] == true && count($booking_transaction_details) == 2) {
       $onward_segment_details = array();
       $return_segment_details = array();
       $segment_indicator_arr = array();
       $segment_indicator_sort = array();
   
       foreach ($itinerary_details as $key => $key_sort_data) {
           $segment_indicator_sort [$key] = $key_sort_data ['origin'];
       }
       array_multisort($segment_indicator_sort, SORT_ASC, $itinerary_details);
   
       foreach ($itinerary_details as $k => $sub_details) {
           $segment_indicator_arr [] = $sub_details ['segment_indicator'];
           $count_value = array_count_values($segment_indicator_arr);
   
           if ($count_value [1] == 1) {
               $onward_segment_details [] = $sub_details;
           } else {
               $return_segment_details [] = $sub_details;
           }
       }
   }
   // debug($onward_segment_details);exit;
   if (isset($onward_segment_details[0]['airline_pnr']) && !empty($itinerary_details[0]['airline_pnr'])) {
       $airline_pnr = $itinerary_details[0]['airline_pnr'];
       $gds_pnr = $booking_transaction_details[0]['pnr'];
   } else if (!empty($booking_transaction_details[0]['pnr'])) {
       $airline_pnr = $booking_transaction_details[0]['pnr'];
       $gds_pnr = $booking_transaction_details[0]['pnr'];
   } else {
       $airline_pnr = $booking_transaction_details[0]['book_id'];
       $gds_pnr = $booking_transaction_details[0]['book_id'];
   }
   if (isset($return_segment_details)) {
       // debug($booking_transaction_details);exit;
       if (isset($return_segment_details[0]['airline_pnr']) && !empty($return_segment_details[0]['airline_pnr'])) {
           $return_airline_pnr = $return_segment_details[0]['airline_pnr'];
           $return_gds_pnr = $booking_transaction_details[1]['pnr'];
       } elseif (!empty($booking_transaction_details[1]['pnr'])) {
           $return_airline_pnr = $booking_transaction_details[1]['pnr'];
           $return_gds_pnr = $booking_transaction_details[1]['pnr'];
       } else {
           $return_airline_pnr = $booking_transaction_details[1]['book_id'];
           $return_gds_pnr = $booking_transaction_details[1]['book_id'];
       }
       $retur_fare_details = json_decode($booking_transaction_details[1]['attributes'], True);
   }
   
   $fare_details = json_decode($booking_transaction_details[0]['attributes'], True);
   
   $BaseFare = $fare_details['Fare']['BaseFare'] + @$retur_fare_details['Fare']['BaseFare'];
   $Tax = $fare_details['Fare']['Tax'] + @$retur_fare_details['Fare']['Tax'];
   $GST = $booking_transaction_details[0]['gst'] + @$booking_transaction_details[1]['gst'];
   $booking_transaction_details_value = $booking_transaction_details [0];
   $baggage_price = 0;
   $meal_price = 0;
   $seat_price = 0;
   // debug($booking_transaction_details_value);exit;
   if (isset($booking_transaction_details_value['extra_service_details']['baggage_details']) == true && valid_array($booking_transaction_details_value['extra_service_details']['baggage_details']) == true) {
       $baggage_details = $booking_transaction_details_value['extra_service_details']['baggage_details'];
       foreach ($baggage_details['details'] as $bag_k => $bag_v) {
           foreach ($bag_v as $bd_k => $bd_v) {
               $baggage_price += $bd_v['price'];
           }
       }
   }
   if (isset($booking_transaction_details_value['extra_service_details']['meal_details']) == true && valid_array($booking_transaction_details_value['extra_service_details']['meal_details']) == true) {
       // debug($booking_transaction_details_value);exit;
       $meal_details = $booking_transaction_details_value['extra_service_details']['meal_details'];
       foreach ($meal_details['details'] as $meal_k => $meal_v) {
           foreach ($meal_v as $md_k => $md_v) {
               $meal_price += $md_v['price'];
           }
       }
   }
   if (isset($booking_transaction_details_value['extra_service_details']['seat_details']) == true && valid_array($booking_transaction_details_value['extra_service_details']['seat_details']) == true) {
       $seat_details = $booking_transaction_details_value['extra_service_details']['seat_details'];
       foreach ($seat_details['details'] as $seat_k => $seat_v) {
           foreach ($seat_v as $sd_k => $sd_v) {
               // debug($seat_v);exit;
               $seat_price += $sd_v['price'];
           }
       }
   }
   if ($booking_details ['is_domestic'] == true && count($booking_transaction_details) == 2) {
       $booking_transaction_details_value = $booking_transaction_details [1];
       if (isset($booking_transaction_details_value['extra_service_details']['baggage_details']) == true && valid_array($booking_transaction_details_value['extra_service_details']['baggage_details']) == true) {
           $baggage_details = $booking_transaction_details_value['extra_service_details']['baggage_details'];
           foreach ($baggage_details['details'] as $bag_k => $bag_v) {
               foreach ($bag_v as $bd_k => $bd_v) {
                   $baggage_price += $bd_v['price'];
               }
           }
       }
       if (isset($booking_transaction_details_value['extra_service_details']['meal_details']) == true && valid_array($booking_transaction_details_value['extra_service_details']['meal_details']) == true) {
           // debug($booking_transaction_details_value);exit;
           $meal_details = $booking_transaction_details_value['extra_service_details']['meal_details'];
           foreach ($meal_details['details'] as $meal_k => $meal_v) {
               foreach ($meal_v as $md_k => $md_v) {
                   $meal_price += $md_v['price'];
               }
           }
       }
       if (isset($booking_transaction_details_value['extra_service_details']['seat_details']) == true && valid_array($booking_transaction_details_value['extra_service_details']['seat_details']) == true) {
           $seat_details = $booking_transaction_details_value['extra_service_details']['seat_details'];
           foreach ($seat_details['details'] as $seat_k => $seat_v) {
               foreach ($seat_v as $sd_k => $sd_v) {
                   // debug($seat_v);exit;
                   $seat_price += $sd_v['price'];
               }
           }
       }
   }
   
   $airline_contact_no = $this->custom_db->single_table_records('airline_contact_numbers', '*', array('airline_code' => $itinerary_details[0]['airline_code']));
   // debug($airline_contact_no);exit;
   if (isset($airline_contact_no['data'][0])) {
       $airline_number = '<span><img style="vertical-align:middle" src=' . SYSTEM_IMAGE_DIR . 'phone.png /><span style="font-size:16px;color:#00a9d6;vertical-align:middle;font-weight: 600;"> &nbsp;' . $airline_contact_no['data'][0]['phone_number'] . '</span></span> ';
   } else {
       $airline_number = '';
   }
   if ($booking_transaction_details[0] ['status'] == 'BOOKING_CONFIRMED') {
       $border = 'border-top:2px solid #808080;';
   } else if ($booking_transaction_details[0] ['status'] == 'BOOKING_HOLD') {
       $border = '';
   }
   $trip_type = $booking_details['trip_type'];
   // echo $meal_price;exit;
   // debug($fare_details);exit;
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
   @media print { 
   header, footer, #show_log { display: none; }
   .pag_brk { page-break-before: always; }
   }
</style>
<div class="table-responsive" style="width:100%; position:relative; padding: 20px 0">
   <table cellpadding="0" border-collapse cellspacing="0" width="100%" style="font-size:13px; font-family: 'Poppins', sans-serif; width:1000px; margin:10px auto;background-color:#fff; padding:50px 45px;">
      <tbody>
         <tr>
            <td style="border-collapse: collapse; padding: 25px 35px;box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15)" >
               <table width="100%" style="border-collapse: collapse;" cellpadding="0" cellspacing="0" border="0">
                  <tr>
                     <td colspan="4" style="padding-bottom:15px">
                        <table width="100%" style="border-collapse: collapse; background: #FAFAFA; vertical-align: middle;" cellpadding="0" cellspacing="0" border="0">
                           <tr>
                              <td style=" vertical-align: middle;font-size:24px; line-height:38px;font-weight:600; text-align:left; padding-left: 12px">Luxury Africa Resorts</td>
                              <td style=" vertical-align: middle;padding: 0 12px 7.5px; text-align: right;"><img style="height: 87px;" src="<?=$GLOBALS['CI']->template->domain_images($data['logo'])?>"></td>
                           </tr>
                        </table>
                     </td>
                  </tr>
                  <tr>
                     <td colspan="4" style="padding: 0">
                        <table width="100%" style="border-collapse: collapse;" cellpadding="0" cellspacing="0" border="0">
                           <tr>
                              <td style="font-size:20px; line-height:32px; font-weight:500; text-align:left;vertical-align: bottom; padding: 0">Flight Voucher</td>
                              <td style="padding: 0">
                                 <table width="100%" style="border-collapse: collapse;" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                       <td style="font-size:17px;line-height:24px;padding: 0;padding-bottom:10px;" align="right">
                                          <span>Booking ID : <?= @$booking_details['app_reference'] ?></span>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td style="font-size:17px;line-height:24px;padding: 0;padding-bottom:10px;" align="right">
                                          <span>Booked on : <?= app_friendly_absolute_date(@$booking_details['booked_date']) ?></span>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="st_lbl" style="font-size:17px;line-height:24px;padding: 0;padding-bottom:10px;" align="right"><span>Status: </span><strong class="<?php echo booking_status_label($booking_transaction_details[0]['status']) ?>">
                                          <?php
                                             switch (@$booking_transaction_details[0] ['status']) {
                                                 case 'BOOKING_CONFIRMED' :
                                                     echo 'CONFIRMED';
                                                     break;
                                                 case 'BOOKING_CANCELLED' :
                                                     echo 'CANCELLED';
                                                     break;
                                                 case 'BOOKING_FAILED' :
                                                     echo 'FAILED';
                                                     break;
                                                 case 'BOOKING_INPROGRESS' :
                                                     echo 'INPROGRESS';
                                                     break;
                                                 case 'BOOKING_INCOMPLETE' :
                                                     echo 'INCOMPLETE';
                                                     break;
                                                 case 'BOOKING_HOLD' :
                                                     echo 'HOLD';
                                                     break;
                                                 case 'BOOKING_PENDING' :
                                                     echo 'PENDING';
                                                     break;
                                                 case 'BOOKING_ERROR' :
                                                     echo 'ERROR';
                                                     break;
                                             }
                                             ?></strong>
                                       </td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                        </table>
                     </td>
                  </tr>
                  <tr><td style="line-height:20px;">&nbsp;</td></tr>
                  <?php if ($booking_details ['is_domestic'] == true && count($booking_transaction_details) == 2) { ?>
                  <tr>
                     <td colspan="4" style="font-size: 18px;font-weight: 600;text-align: center;padding: 10px 0 0;">E-Ticket <?php echo $Onward; ?></td>
                  </tr>
                  <?php } ?>
                  <?php if (false) { ?>
                  <tr>
                     <td style="padding:10px 0"><img style="width:60px;" src="<?= SYSTEM_IMAGE_DIR . 'airline_logo/' . $itinerary_details[0]['airline_code'] . '.gif' ?>" /></td>
                     <td style="padding:10px 0; line-height:25px;"><span style="display: block;border-right: 1px solid #999;"><span style="font-size:14px;"><?= @$itinerary_details[0]['airline_name'] ?></span><br><?php echo $airline_number ?></span></td>
                     <td style="padding:10px 0; padding-left: 10%; line-height:25px;"><span style="font-size:14px;">Agency</span><br><span><img style="vertical-align:middle" src="<?= SYSTEM_IMAGE_DIR . 'phone.png' ?>" /> <span style="font-size:16px;color:#00a9d6;vertical-align:middle;font-weight: 600;"> &nbsp;<?php echo $data['phone'] ?></span></span></td>
                  </tr>                  
                  <?php } ?>
                  <tr>
                     <td colspan="4" style="border:2px solid color(srgb 0.25 0.25 0.25 / 0.25); padding:0;">
                        <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px;">
                           <tbody>
                              <tr>
                                 <td style="background: #F9F5F1;padding: 15px 15px;" colspan="2"><img style="vertical-align:middle" src="<?= SYSTEM_IMAGE_DIR . 'flight1.svg' ?>"> &nbsp;<span style="vertical-align:middle;font-size:16px; line-height: 24px; color: #3f3f3f; font-weight: 500">Onward Flight Details</span></td>
                                 <?php if ((!empty($gds_pnr)) || (!empty($airline_pnr))) { ?>
                     <td style="background: #F9F5F1;padding: 15px 15px;" colspan="2" align="right">
                        <span style="font-size:16px; line-height: 24px; color: #414141">
                        <?php if ($booking_transaction_details[0] ['status'] == 'BOOKING_CONFIRMED') { ?>
                        <span>AIRLINE PNR: </span>
                        <strong style="font-weight: 500"><?= @$airline_pnr ?></strong>,
                        <?php } ?>
                        <?php if (($booking_transaction_details[0] ['status'] == 'BOOKING_CONFIRMED' || $booking_transaction_details[1] ['status'] == 'BOOKING_HOLD') && !empty($gds_pnr)) { ?>
                        <span>GDS PNR:  </span><strong style="font-weight: 500"><?= @$gds_pnr ?></strong>
                        <?php } ?>
                        </span>
                     </td>
                     <?php } ?>
                              </tr>
                              <?php
                                 if (isset($booking_transaction_details) && $booking_transaction_details != "") {
                                     if ($booking_details ['is_domestic'] == true && count($booking_transaction_details) == 2) {
                                         $itinerary_details = array();
                                         $itinerary_details = $onward_segment_details;
                                     }
                                     // debug($itinerary_details);exit;
                                     $checkin_baggage = 0;
                                     $cabin_baggage = 0;
                                     $seg_count = count($itinerary_details);
                                     if ($seg_count == 1) {
                                         $non_stop = 'Non Stop';
                                     } else {
                                         $non_stop = '';
                                     }
                                     $seg_in_array = array();
                                     $seg_array = array();
                                     $seg_counts = 0;
                                     // debug($itinerary_details);exit;
                                     foreach ($itinerary_details as $segment_details_k => $segment_details_v) {
                                         // echo $trip_type;
                                         $seg_array [] = $segment_details_v['segment_indicator'];
                                         // debug($seg_array);
                                         if (in_array($segment_details_v['segment_indicator'], $seg_in_array) && !empty($seg_in_array) && ($seg_counts == 0) && ($trip_type != 'multicity')) {
                                             // echo $seg_count;
                                             $seg_counts = $seg_counts + 1;
                                 
                                             // debug($seg_in_array);
                                             ?>
                              <tr>
                                 <td colspan="2" style="background-color:#00a9d6; color:#fff"><img style="vertical-align:middle" src="<?= SYSTEM_IMAGE_DIR . 'flight.png' ?>"> &nbsp;<span style="vertical-align:middle;font-size:13px">Return Flight Details</span></td>
                                 <td align="right" colspan="2" style="background-color:#00a9d6; color:#fff"><span style="font-size:10px">*Please verify flight times with the airlines prior to departure</span></td>
                              </tr>
                              <?php
                                 }
                                 
                                 $itinerary_details_attributes = json_decode($segment_details_v ['attributes'], true);
                                 $airline_terminal_origin = @$itinerary_details_attributes['departure_terminal'];
                                 $airline_terminal_destination = @$itinerary_details_attributes['arrival_terminal'];
                                 $origin_terminal = '';
                                 $destination_terminal = '';
                                 // debug($itinerary_details_attributes);exit;
                                 if ($airline_terminal_origin != '') {
                                     $origin_terminal = 'Terminal ' . $airline_terminal_origin;
                                 }
                                 if ($airline_terminal_destination != '') {
                                     $destination_terminal = 'Terminal ' . $airline_terminal_destination;
                                 }
                                 
                                 
                                 # Checking CheckIN Baggage
                                 $BaggageSegment_checkin = '';
                                 $BaggageSegment_cabin = '';
                                 if (is_int($segment_details_v['checkin_baggage'])) {
                                     $BaggageSegment_checkin .= 'Checkin Baggage : ' . @$segment_details_v['from_airport_code'] . ' To ' . @$segment_details_v['to_airport_code'] . ' (Adult :' . (int) $segment_details_v['checkin_baggage'] . ' Kg & Child :'.(int) $segment_details_v['checkin_baggage'] .' Kg)<br />';
                                     $TotalBaggageCheckIN = (int) $segment_details_v['checkin_baggage'];
                                 } else {
                                     $BaggageSegment_checkin .= 'Checkin Baggage : ' . @$segment_details_v['from_airport_code'] . ' To ' . @$segment_details_v['to_airport_code'] . ' (Adult :' . $segment_details_v['checkin_baggage'] . ' & Child :'.$segment_details_v['checkin_baggage'] .')<br />';
                                     $TotalBaggageCheckIN = $segment_details_v['checkin_baggage'];
                                 }
                                 
                                 # Checking Cabin Baggage
                                 
                                 if (is_int($segment_details_v['cabin_baggage'])) {
                                     $BaggageSegment_cabin .= 'Cabin Baggage : ' . @$segment_details_v['from_airport_code'] . ' To ' . @$segment_details_v['to_airport_code'] . ' (Adult :' . (int) $segment_details_v['cabin_baggage'] . ' Kg & Child :'.(int) $segment_details_v['cabin_baggage'].')<br />';
                                     $TotalBaggageCabin = (int) $segment_details_v['cabin_baggage'];
                                 } else {
                                     $BaggageSegment_cabin .= 'Cabin Baggage : ' . @$segment_details_v['from_airport_code'] . ' To ' . @$segment_details_v['to_airport_code'] . ' (Adult :' . $segment_details_v['cabin_baggage'] . ' & Child : '.$segment_details_v['cabin_baggage'].')<br />';
                                     $TotalBaggageCabin = $segment_details_v['cabin_baggage'];
                                 }
                                 
                                 
                                 
                                 if ($seg_count != 1 && $trip_type != 'multicity') {
                                 
                                     if (count(array_unique($seg_array)) == 1 && end($seg_array) == 1 && $seg_count == 2 && $trip_type != 'oneway') {
                                 
                                         $non_stop = 'Non Stop';
                                     } else {
                                         $non_stop = $segment_details_v['segment_indicator'] . ' Stop';
                                     }
                                 } else if ($seg_count != 1) {
                                     $non_stop = ($segment_details_k + 1) . ' Stop';
                                 }
                                 if ($trip_type == 'multicity') {
                                     $fight_count = ($segment_details_k + 1);
                                 } else {
                                     $fight_count = $segment_details_v['segment_indicator'];
                                 }
                                 $seg_in_array[] = $segment_details_v['segment_indicator'];
                                 if (valid_array($segment_details_v) == true) {
                                     ?>
                              <tr>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 10px 10px; border-bottom: 1.5px dashed color(srgb 0.87 0.69 0.2 / 0.5)"><span>Flight <?php echo $fight_count; ?></span></td>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 10px 10px; border-bottom: 1.5px dashed color(srgb 0.87 0.69 0.2 / 0.5)"><img style="vertical-align:middle" src="<?= SYSTEM_IMAGE_DIR . 'up_flight.svg' ?>">&nbsp;<span style="vertical-align:middle">Departing</span></td>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 10px 10px; border-bottom: 1.5px dashed color(srgb 0.87 0.69 0.2 / 0.5)"><img style="vertical-align:middle" src="<?= SYSTEM_IMAGE_DIR . 'up_flight.svg' ?>">&nbsp;<span style="vertical-align:middle">Arriving</span></td>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 10px 10px; border-bottom: 1.5px dashed color(srgb 0.87 0.69 0.2 / 0.5)">&nbsp;</td>
                              </tr>
                              <tr>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 10px 10px;"><span><?= @$segment_details_v['airline_name'] ?><br><?php echo $segment_details_v['airline_code'] . ' - ' . $segment_details_v['flight_number']; ?><br>Cabin: <?php echo $booking_details['cabin_class']; ?></span></td>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 10px 10px;"><span><strong style="font-weight: 600"><?= @$segment_details_v['from_airport_name'] ?>(<?= @$segment_details_v['from_airport_code'] ?>)</strong><br><?php echo date("D, d M Y", strtotime($segment_details_v['departure_datetime'])) . ", " . date("h:i A", strtotime($segment_details_v['departure_datetime'])); ?><br><?php echo $origin_terminal; ?></span></td>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 10px 10px;"><span><strong style="font-weight: 600"><?= @$segment_details_v['to_airport_name'] ?>(<?= @$segment_details_v['to_airport_code'] ?>)</strong><br><?php echo date("D, d M Y", strtotime($segment_details_v['arrival_datetime'])) . ", " . date("h:i A", strtotime($segment_details_v['arrival_datetime'])); ?><br><?php echo $destination_terminal; ?></span></td>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 10px 10px;"><span style="display:block;border-left: 1.5px dashed color(srgb 0.87 0.69 0.2 / 0.5); padding-left:10%"><?php echo $non_stop; ?><br><?php echo $segment_details_v['total_duration']; ?><br><?php echo $segment_details_v['is_refundable'] ?></span></td>
                              </tr>
                              <?php
                                 }
                                 }
                                 }
                                 ?>
                           </tbody>
                        </table>
                     </td>
                  </tr>
                  <tr>
                     <td style="line-height:15px;padding:0;">&nbsp;</td>
                  </tr>
                  <tr>
                     <td colspan="4" style="padding:0;">
                        <table cellspacing="0" cellpadding="10" width="100%" style="font-size:16px; line-height: 24px; color: #3f3f3f;">
                           <tbody>
                              <tr>
                                 <td colspan="4" style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 15px; background: #FAF7F480;border-bottom: 1px dashed #93908A;"><img style="vertical-align:middle" src="<?= SYSTEM_IMAGE_DIR . 'people_group.svg' ?>"> &nbsp;<span style="vertical-align:middle;font-weight: 500">Passenger(s) Details</span></td>
                              </tr>
                              <tr>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 12px;background: #FAF7F480"><span>Sr No.</span></td>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 12px;background: #FAF7F480"><span>Passenger(s) Name</span></td>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 12px;background: #FAF7F480"><span>Type</span></td>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 12px;background: #FAF7F480"><span>E-ticket No</span></td>
                              </tr>
                              <?php
                                 $booking_transaction_details_value = $booking_transaction_details [0];
                                 // debug($booking_transaction_details_value);exit;
                                 if (isset($booking_transaction_details_value ['booking_customer_details'])) {
                                     foreach ($booking_transaction_details_value ['booking_customer_details'] as $cus_k => $cus_v) {
                                         if (strtolower($cus_v['passenger_type']) == 'infant') {
                                             $pass_name = $cus_v['title'] . '.' .$cus_v['first_name'] . '  ' . $cus_v['last_name'];
                                         } else {
                                             $pass_name = $cus_v['title'] . '.' . $cus_v['first_name'] . '  ' . $cus_v['last_name'];
                                         }
                                         ?>
                              <tr>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 12px;    background-color: #F7F3EDCC;"><span><?php echo ($cus_k + 1); ?></span></td>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 12px;    background-color: #F7F3EDCC;"><span><strong style="font-weight: 500"><?php echo $pass_name; ?></strong></span></td>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 12px;    background-color: #F7F3EDCC;"><span><?php echo ucfirst($cus_v['passenger_type']) ?></span></td>
                                 <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 12px;    background-color: #F7F3EDCC;"><span><?= @$cus_v['TicketNumber']; ?></span></td>
                              </tr>
                              <?php
                                 }
                                 }
                                 ?>
                           </tbody>
                        </table>
                     </td>
                  </tr>
                  <tr>
                     <td style="line-height:15px;padding:0;">&nbsp;</td>
                  </tr>
                 <tr>
                    <td colspan="4">
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
                                            <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;"><span>Air Fare</span></td>
                                            <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;text-align: right;font-weight: 500;"><span><?php echo number_format($BaseFare, 2) ?></span></td>
                                         </tr>
                                          <?php if ($this->uri->segment(2) == 'b2c_flight_voucher'): ?>
                                          <tr>
                                             <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;"><span>Taxes &amp; Fees</span></td>
                                             <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;text-align: right;font-weight: 500;"><span> <?php echo number_format(($Tax + $booking_details['admin_markup'] + $booking_details['agent_markup'] + ($booking_details['convinence_amount'])) - $booking_details['admin_commission'] + $booking_details['admin_tds'], 2) ?></span></td>
                                          </tr>
                                          <?php else: ?>
                                          <tr>
                                             <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;"><span>Taxes &amp; Fees</span></td>
                                             <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;text-align: right;font-weight: 500;"><span> <?php echo number_format(($Tax + $booking_details['admin_markup'] + $booking_details['agent_markup'] + ($booking_details['convinence_amount'])), 2) ?></span></td>
                                          </tr>
                                          <?php endif; ?>
                                          <?php if ($GST > 0) { ?>
                                          <tr>
                                             <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 15px;"><span>GST</span></td>
                                             <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;text-align: right;font-weight: 500;"><span> <?php echo number_format(($GST), 2) ?></span></td>
                                          </tr>
                                          <?php } ?>
                                          <?php if (isset($baggage_price) && $baggage_price != 0) { ?>
                                          <tr>
                                             <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 15px;"><span>Cabin Baggage</span></td>
                                             <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;text-align: right;font-weight: 500;"><span> <?php echo number_format($baggage_price, 2) ?></span></td>
                                          </tr>
                                          <?php } ?>
                                          <?php if (isset($meal_price) && $meal_price != 0) { ?>
                                          <tr>
                                             <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 15px;"><span>Meals</span></td>
                                             <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;text-align: right;font-weight: 500;"><span> <?php echo number_format($meal_price, 2) ?></span></td>
                                          </tr>
                                          <?php } ?>
                                          <?php if (isset($seat_price) && $seat_price != 0) { ?>
                                          <tr>
                                             <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 15px;"><span>Seat</span></td>
                                             <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;text-align: right;font-weight: 500;"><span> <?php echo number_format($seat_price, 2) ?></span></td>
                                          </tr>
                                          <?php } if ($booking_details['discount'] != 0) { ?>
                                          <tr>
                                             <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 15px;"><span>Discount (-)</span></td>
                                             <td style="border-bottom:1px solid #E5E5E5;padding:12px 15px;text-align: right;font-weight: 500;"><span> <?php echo number_format($booking_details['discount'], 2) ?></span></td>
                                          </tr>
                                          <?php } ?>
                                          <tr>
                                             <td style="border-top:1px solid #ccc;padding:12px 15px;"><span style="font-weight: 500; color: #525252">Total Amount Charged</span></td>
                                             <td style="border-top:1px solid #ccc;padding:12px 0px;text-align: right;"><span style="font-weight: 500;font-size: 20px; background: #BF9766; color: #fff;padding: 4px 12px"><?=$booking_details['currency']?> <?= number_format(@$booking_details['grand_total'] + $seat_price + $meal_price + $baggage_price, 2) ?></span></td>
                                          </tr>
                                      </tbody>
                                   </table>
                                </td>
                             </tr>
                          </tbody>
                       </table>
                    </td>
                 </tr>
                  <?php //debug($booking_details);exit;?>
                  <tr>
                     <td style="line-height:15px;padding:0;">&nbsp;</td>
                  </tr>
                  <!-- <tr>
                     <td colspan="4" style="border-bottom:1px solid #999999;padding-bottom:15px"><span style="font-size:13px; color:#555;">Customer Contact Details | E-mail : <?= $booking_details['email'] ?> | Contact No : <?= $booking_details['phone_code']." ".$booking_details['phone'] ?></span></td>
                  </tr>
                  <tr>
                     <td style="line-height:15px;padding:0;">&nbsp;</td>
                  </tr> -->
                  <tr>
                        <td colspan="4" style="background: #F9F9F9CC; padding: 15px;">
                           <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px; padding:0;">
                              <tbody>
                                 <tr>
                                 <td colspan="4"><span style="line-height:26px;font-size: 15px;font-weight: 500; color: #3f3f3f; padding-bottom: 5px;">Important Information</span></td></tr>
                                 <tr>
                                    <td colspan="4" style="line-height:22px; font-size:13px; color:#3f3f3f;padding-bottom: 5px;padding-top:5px"><ul style="padding-bottom:5px">
                           <li>All Guests, including children and infants, must present valid identification at check-in.</li>
                           <li>Check-in begins 2 hours prior to the flight for seat assignment and closes 45 minutes prior to the scheduled departure.</li>
                           <li>Carriage and other services provided by the carrier are subject to conditions of carriage, which are hereby incorporated by reference. These conditions may be obtained from the issuing carrier.</li>
                           <li>In case of cancellations less than 6 hours before departure please cancel with the airlines directly. We are not responsible for any losses if the request is received less than 6 hours before departure.</li>
                           <li>Please contact airlines for Terminal Queries.</li>
                           <li>Free Baggage Allowance: Checked-in Baggage = 15kgs in Economy class.</li>
                           <li>Changes to the reservation will result in the above fee plus any difference in the fare between the original fare paid and the fare for the revised booking.</li>
                           <li>The No Show refund should be collected within 15 days from departure date.</li>
                           <li>If the basic fare is less than cancellation charges then only statutory taxes would be refunded.</li>
                           <li>We are not be responsible for any Flight delay/Cancellation from airline's end.</li>
                           <li>Kindly contact the airline at least 24 hrs before to reconfirm your flight detail giving reference of Airline PNR Number.</li>
                           <li>We are a travel agent and all reservations made through our website are as per the terms and conditions of the concerned airlines. All modifications,cancellations and refunds of the airline tickets shall be strictly in accordance with the policy of the concerned airlines and we disclaim all liability in connection thereof.</li>
                        </ul></td>
                                 </tr>
                              </tbody>
                           </table>
                        </td>
                     </tr>
<!--                   <tr>
                     <td colspan="4"><span style="line-height:20px; font-size:13px;">Important Information</span></td>
                  </tr>
                  <tr>
                     <td colspan="4" style="border-bottom:1px solid #999999; line-height:20px; font-size:12px; color:#555">
                        <ul style="padding-bottom:10px">
                           <li>All Guests, including children and infants, must present valid identification at check-in.</li>
                           <li>Check-in begins 2 hours prior to the flight for seat assignment and closes 45 minutes prior to the scheduled departure.</li>
                           <li>Carriage and other services provided by the carrier are subject to conditions of carriage, which are hereby incorporated by reference. These conditions may be obtained from the issuing carrier.</li>
                           <li>In case of cancellations less than 6 hours before departure please cancel with the airlines directly. We are not responsible for any losses if the request is received less than 6 hours before departure.</li>
                           <li>Please contact airlines for Terminal Queries.</li>
                           <li>Free Baggage Allowance: Checked-in Baggage = 15kgs in Economy class.</li>
                           <li>Changes to the reservation will result in the above fee plus any difference in the fare between the original fare paid and the fare for the revised booking.</li>
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
                        <td colspan="4" align="center" style="padding:15px 0;font-size:14px;line-height:24px;">For booking issues, email <strong style="font-weight: 500; color: #BF9766">support@gmail.com</strong> or  call our 24x7 team on <strong style="font-weight: 500; color: #BF9766"><?=$data['phone_code']?><?=$data['phone']?></strong>.(Booking Reference : <?php echo $booking_details['app_reference']; ?>)</td>
                     </tr>
                     
                           <tr>
                              <td colspan="4" style="padding: 0">
                                 <table width="100%" style="background: #F9F5F1">
                                    <tr>
                                       <td class="our_partner" style="text-align: left;padding: 5px 15px;"><span style="font-size: 13px;font-weight: 600;color: #3f3f3f;margin-right: 5px">Our Partners</span>
                                          <span style="margin-right: 5px"><img style="height: 45px;background-color: #fff;border-radius: 3px;padding: 0 10px;" src="<?=$GLOBALS['CI']->template->template_images("booking_com.png");?>"></span>
                                          <span style="margin-right: 5px"><img style="height: 45px;background-color: #fff;border-radius: 3px;padding: 0 10px;" src="<?=$GLOBALS['CI']->template->template_images("airbnb.png");?>"></span>
                                          <span style="margin-right: 5px"><img style="height: 45px;background-color: #fff;border-radius: 3px;padding: 0 10px;" src="<?=$GLOBALS['CI']->template->template_images("expedia.png");?>"></span>
                                       </td>
                                       <td style="padding: 0 15px;font-weight: 500;font-size: 15px;" align="right">Luxury Africa Resorts</td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                  <!-- <tr>
                     <td colspan="4" align="right" style="padding-top:10px"><?php echo strtoupper($data['domainname']) ?><br>ContactNo : <?php echo $data['phone'] ?><br><?php echo $data['address'] ?></td>
                  </tr> -->
               </table>
            </td>
         </tr>
      </tbody>
   </table>
</div>
<!-- Return Ticket -->
<?php
   if (count($booking_transaction_details) == 2) {
       $airline_contact_no = $this->custom_db->single_table_records('airline_contact_numbers', '*', array('airline_code' => $return_segment_details[0]['airline_code']));
   // debug($airline_contact_no);exit;
       if (isset($airline_contact_no['data'][0])) {
           $airline_number = '<span><img style="vertical-align:middle" src=' . SYSTEM_IMAGE_DIR . 'phone.png /><span style="font-size:16px;color:#00a9d6;vertical-align:middle;font-weight: 600;"> &nbsp;' . $airline_contact_no['data'][0]['phone_number'] . '</span></span> ';
       } else {
           $airline_number = '';
       }
       if ($booking_transaction_details[1] ['status'] == 'BOOKING_CONFIRMED') {
           $border = 'border-top:2px solid #808080;';
       } else if ($booking_transaction_details[1] ['status'] == 'BOOKING_HOLD') {
           $border = '';
       }
       ?>
<div style="background:#ccc; width:100%; position:relative">
   <table class="pag_brk" cellpadding="0" border-collapse cellspacing="0" width="100%" style="font-size:12px; font-family: 'Open Sans', sans-serif; width:850px; margin:0px auto;background-color:#fff; padding:45px;border-collapse:separate;color: #000;">
      <tbody>
         <tr>
            <td colspan="4" style="font-size: 18px;font-weight: 600;text-align: center;padding: 10px 0 12px;">E-Ticket <?php echo $Return; ?></td>
         </tr>
         <tr>
            <td align="right" colspan="4" style="line-height:26px; border-top:1px solid #00a9d6; border-bottom:1px solid #00a9d6;"><span style="font-size:12px;">Status: </span><strong class="<?php echo booking_status_label($booking_transaction_details[1]['status']) ?>" style=" font-size:14px;">
               <?php
                  switch (@$booking_transaction_details[1] ['status']) {
                      case 'BOOKING_CONFIRMED' :
                          echo 'CONFIRMED';
                          break;
                      case 'BOOKING_CANCELLED' :
                          echo 'CANCELLED';
                          break;
                      case 'BOOKING_FAILED' :
                          echo 'FAILED';
                          break;
                      case 'BOOKING_INPROGRESS' :
                          echo 'INPROGRESS';
                          break;
                      case 'BOOKING_INCOMPLETE' :
                          echo 'INCOMPLETE';
                          break;
                      case 'BOOKING_HOLD' :
                          echo 'HOLD';
                          break;
                      case 'BOOKING_PENDING' :
                          echo 'PENDING';
                          break;
                      case 'BOOKING_ERROR' :
                          echo 'ERROR';
                          break;
                  }
                  ?></strong>
            </td>
         </tr>
         <?php if (false) { ?>
         <tr>
            <td style="padding:10px 0"><img style="width:60px;" src="<?= SYSTEM_IMAGE_DIR . 'airline_logo/' . $return_segment_details[0]['airline_code'] . '.gif' ?>" /></td>
            <td style="padding:10px 0; line-height:25px;"><span style="display: block;border-right: 1px solid #999;"><span style="font-size:14px;"><?= @$return_segment_details[0]['airline_name'] ?></span><br><?php echo $airline_number ?></span></td>
            <td style="padding:10px 0; padding-left: 10%; line-height:25px;"><span style="font-size:14px;">Agency</span><br><span><img style="vertical-align:middle" src="<?= SYSTEM_IMAGE_DIR . 'phone.png' ?>" /> <span style="font-size:16px;color:#00a9d6;vertical-align:middle;font-weight: 600;"> &nbsp;<?php echo $data['phone'] ?></span></span></td>
            <?php if ((!empty($return_gds_pnr)) || (!empty($return_airline_pnr))) { ?>
            <td style="padding:10px 0;text-align: center;">
               <span style="font-size:14px; border:2px solid #808080; display:block">
               <?php if ($booking_transaction_details[1] ['status'] == 'BOOKING_CONFIRMED') { ?>
               <span style="color:#00a9d6;padding:5px; display:block">AIRLINE PNR</span>
               <span style="font-size:26px;line-height:35px;padding-bottom: 5px;display:block;font-weight: 600;"><?= @$return_airline_pnr ?></span>
               <?php } ?>
               <?php if (($booking_transaction_details[1] ['status'] == 'BOOKING_CONFIRMED' || $booking_transaction_details[1] ['status'] == 'BOOKING_HOLD') && (!empty($return_gds_pnr))) { ?>
               <span style="<?php echo $border; ?>display:block; padding:5px;">GDS PNR:  <?= @$return_gds_pnr ?></span>
               <?php } ?>
               </span>
            </td>
            <?php } ?>
         </tr>
        <?php } ?>
         <tr>
            <td colspan="4" style="border:1px solid #00a9d6; padding:0;">
               <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px; padding:0;">
                  <tbody>
                     <tr>
                        <td colspan="2" style="background-color:#00a9d6; color:#fff"><img style="vertical-align:middle" src="<?= SYSTEM_IMAGE_DIR . 'flight.png' ?>"> &nbsp;<span style="vertical-align:middle;font-size:13px"><?php echo $Return; ?> Flight Details</span></td>
                        <?php if ((!empty($return_gds_pnr)) || (!empty($return_airline_pnr))) { ?>
                        <td style="padding:10px 0;text-align: center;">
                           <span style="font-size:14px; border:2px solid #808080; display:block">
                           <?php if ($booking_transaction_details[1] ['status'] == 'BOOKING_CONFIRMED') { ?>
                           <span style="color:#00a9d6;padding:5px; display:block">AIRLINE PNR</span>
                           <span style="font-size:26px;line-height:35px;padding-bottom: 5px;display:block;font-weight: 600;"><?= @$return_airline_pnr ?></span>
                           <?php } ?>
                           <?php if (($booking_transaction_details[1] ['status'] == 'BOOKING_CONFIRMED' || $booking_transaction_details[1] ['status'] == 'BOOKING_HOLD') && (!empty($return_gds_pnr))) { ?>
                           <span style="<?php echo $border; ?>display:block; padding:5px;">GDS PNR:  <?= @$return_gds_pnr ?></span>
                           <?php } ?>
                           </span>
                        </td>
                        <?php } ?>
                     </tr>
                     <?php
                        if (isset($booking_transaction_details) && $booking_transaction_details != "") {
                            $checkin_baggage = 0;
                            $cabin_baggage = 0;
                            $seg_count = count($itinerary_details);
                            if ($seg_count == 1) {
                                $non_stop = 'Non Stop';
                            } else {
                                $non_stop = '';
                            }
                            foreach ($return_segment_details as $segment_details_k => $segment_details_v) {
                                $itinerary_details_attributes = json_decode($segment_details_v ['attributes'], true);
                                $airline_terminal_origin = @$itinerary_details_attributes['departure_terminal'];
                                $airline_terminal_destination = @$itinerary_details_attributes['arrival_terminal'];
                                $origin_terminal = '';
                                $destination_terminal = '';
                                if ($airline_terminal_origin != '') {
                                    $origin_terminal = 'Terminal ' . $airline_terminal_origin;
                                }
                                if ($airline_terminal_destination != '') {
                                    $destination_terminal = 'Terminal ' . $airline_terminal_destination;
                                }
                        
                                //$checkin_baggage += (int) $segment_details_v['checkin_baggage'];
                                //$cabin_baggage += (int) $segment_details_v['cabin_baggage'];  
                        # Checking CheckIN Baggage
                        
                        
                                   if (is_int($segment_details_v['checkin_baggage'])) {
                                                            @$BaggageSegment_checkin1 .= 'Checkin Baggage : ' . @$segment_details_v['from_airport_code'] . ' To ' . @$segment_details_v['to_airport_code'] . ' (Adult :' . (int) $segment_details_v['checkin_baggage'] . ' Kg & Child :'.(int) $segment_details_v['checkin_baggage'] .' Kg)<br />';
                                                            $TotalBaggageCheckIN1 = (int) $segment_details_v['checkin_baggage'];
                                                        } else {
                                                            @$BaggageSegment_checkin1 .= 'Checkin Baggage : ' . @$segment_details_v['from_airport_code'] . ' To ' . @$segment_details_v['to_airport_code'] . ' (Adult :' . $segment_details_v['checkin_baggage'] . ' & Child :'.$segment_details_v['checkin_baggage'] .')<br />';
                                                            $TotalBaggageCheckIN1 = $segment_details_v['checkin_baggage'];
                                                        }
                        
                        # Checking Cabin Baggage
                        
                                                        if (is_int($segment_details_v['cabin_baggage'])) {
                                                            @$BaggageSegment_cabin1 .= 'Cabin Baggage : ' . @$segment_details_v['from_airport_code'] . ' To ' . @$segment_details_v['to_airport_code'] . ' (Adult :' . (int) $segment_details_v['cabin_baggage'] . ' Kg & Child :'.(int) $segment_details_v['cabin_baggage'].')<br />';
                                                            $TotalBaggageCabin1 = (int) $segment_details_v['cabin_baggage'];
                                                        } else {
                                                            @$BaggageSegment_cabin1 .= 'Cabin Baggage : ' . @$segment_details_v['from_airport_code'] . ' To ' . @$segment_details_v['to_airport_code'] . ' (Adult :' . $segment_details_v['cabin_baggage'] . ' & Child : '.$segment_details_v['cabin_baggage'].')<br />';
                                                            $TotalBaggageCabin1 = $segment_details_v['cabin_baggage'];
                                                        }
                        
                        
                                if ($seg_count != 1) {
                                    $non_stop = ($segment_details_k + 1) . ' Stop';
                                }
                                if (valid_array($segment_details_v) == true) {
                                    ?>
                     <tr>
                        <td style="background-color:#d9d9d9; color:#555555"><span>Flight <?php echo $segment_details_v['segment_indicator']; ?></span></td>
                        <td style="background-color:#d9d9d9; color:#555555"><img style="vertical-align:middle" src="<?= SYSTEM_IMAGE_DIR . 'flight_up.png' ?>">&nbsp;<span style="vertical-align:middle">Departing</span></td>
                        <td style="background-color:#d9d9d9; color:#555555"><img style="vertical-align:middle" src="<?= SYSTEM_IMAGE_DIR . 'flight_down.png' ?>">&nbsp;<span style="vertical-align:middle">Arriving</span></td>
                        <td style="background-color:#d9d9d9; color:#555555">&nbsp;</td>
                     </tr>
                     <tr>
                        <td><span><?= @$segment_details_v['airline_name'] ?><br><?php echo $segment_details_v['airline_code'] . ' - ' . $segment_details_v['flight_number']; ?><br>Cabin: <?php echo $booking_details['cabin_class']; ?></span></td>
                        <td><span><strong><?= @$segment_details_v['from_airport_name'] ?>(<?= @$segment_details_v['from_airport_code'] ?>)</strong><br><?php echo date("D, d M Y", strtotime($segment_details_v['departure_datetime'])) . ", " . date("h:i A", strtotime($segment_details_v['departure_datetime'])); ?><br><?php echo $origin_terminal; ?></span></td>
                        <td><span><strong><?= @$segment_details_v['to_airport_name'] ?>(<?= @$segment_details_v['to_airport_code'] ?>)</strong><br><?php echo date("D, d M Y", strtotime($segment_details_v['arrival_datetime'])) . ", " . date("h:i A", strtotime($segment_details_v['arrival_datetime'])); ?><br><?php echo $destination_terminal; ?></span></td>
                        <td><span style="display:block;border-left: 1px solid #999; padding-left:10%"><?php echo $non_stop; ?><br><?php echo $segment_details_v['total_duration']; ?><br><?php echo $segment_details_v['is_refundable'] ?></span></td>
                     </tr>
                     <?php
                        }
                        }
                        }
                        ?>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
            <td style="line-height:15px;padding:0;">&nbsp;</td>
         </tr>
         <tr>
            <td colspan="4" style="border:1px solid #666666; padding:0;">
               <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px; padding:0;">
                  <tbody>
                     <tr>
                        <td colspan="4" style="background-color:#666666; color:#fff"><img style="vertical-align:middle" src="<?= SYSTEM_IMAGE_DIR . 'people_group.png' ?>"> &nbsp;<span style="vertical-align:middle;font-size:13px;">Passenger(s) Details</span></td>
                     </tr>
                     <tr>
                        <td style="background-color:#d9d9d9; color:#555555"><span>Sr No.</span></td>
                        <td style="background-color:#d9d9d9; color:#555555"><span>Passenger(s) Name</span></td>
                        <td style="background-color:#d9d9d9; color:#555555"><span>Type</span></td>
                        <td style="background-color:#d9d9d9; color:#555555"><span>E-ticket No</span></td>
                     </tr>
                     <?php
                        $booking_transaction_details_value = $booking_transaction_details [1];
                        // debug($booking_transaction_details_value);exit;
                        if (isset($booking_transaction_details_value ['booking_customer_details'])) {
                            foreach ($booking_transaction_details_value ['booking_customer_details'] as $cus_k => $cus_v) {
                                if (strtolower($cus_v['passenger_type']) == 'infant') {
                                    $pass_name = $cus_v['title'] . '.' . $cus_v['first_name'] . '  ' . $cus_v['last_name'];
                                } else {
                                    $pass_name = $cus_v['title'] . '.' . $cus_v['first_name'] . '  ' . $cus_v['last_name'];
                                }
                                ?>
                     <tr>
                        <td><span><?php echo ($cus_k + 1); ?></span></td>
                        <td><span><strong style="font-weight: 500"><?php echo $pass_name; ?></strong></span></td>
                        <td><span><?php echo ucfirst($cus_v['passenger_type']) ?></span></td>
                        <td><span><?= @$cus_v['TicketNumber']; ?></span></td>
                     </tr>
                     <?php
                        }
                        }
                        ?>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
            <td style="line-height:15px;padding:0;">&nbsp;</td>
         </tr>
         <tr>
            <td colspan="4" style="padding:0;">
               <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px; padding:0;">
                  <tbody>
                     <tr>
                        <td style="padding:0; vertical-align:top">
                           <table cellspacing="0" cellpadding="5" width="100%" style="border:1px solid #9a9a9a;font-size:12px; padding:0;">
                              <tbody>
                                 <tr>
                                    <td colspan="4" style="border-bottom:1px solid #ccc"><span style="font-size:13px">Flight Inclusions</span></td>
                                 </tr>
                                 <tr>
                                    <?php if ($TotalBaggageCheckIN1 != '' && $TotalBaggageCheckIN1 != 0) { ?>
                                    <td colspan="4"><span><?php echo $BaggageSegment_checkin1; ?></span></td>
                                    <?php } else {
                                       ?>
                                    <td colspan="4"><span>Checkin Baggage : Allowed as per the Airline Policy</span></td>
                                    <?php } ?>
                                 </tr>
                                 <tr>
                                    <?php if ($TotalBaggageCabin1 != '' && $TotalBaggageCabin1 != 0) { ?>
                                    <td colspan="4"><span><?php echo $BaggageSegment_cabin1; ?></span></td>
                                    <?php } else {
                                       ?>
                                    <td colspan="4"><span>Cabin Baggage :  Allowed as per the Airline Policy</span></td>
                                    <?php } ?>
                                 </tr>
                                 <tr>
                                    <td colspan="4" style="border-top:1px solid #ccc"><span style="font-size:10px; color:#666; line-height:20px;">* Flight inclusions are subject to change with Airlines.</span></td>
                                 </tr>
                              </tbody>
                           </table>
                        </td>
                     </tr>
                     <?php if (isset($booking_transaction_details_value['extra_service_details']) == true && valid_array($booking_transaction_details_value['extra_service_details']) == true) { ?>
                     <tr>
                        <td style="padding:10px 0 0 0;vertical-align:top">
                           <table cellspacing="0" cellpadding="5" width="100%" style="border:1px solid #9a9a9a;font-size:12px; padding:0;">
                              <tbody>
                                 <tr>
                                    <td colspan="4" style="border-bottom:1px solid #ccc"><span style="font-size:13px">Flight Extra Information</span></td>
                                 </tr>
                                 <?php
                                    if (isset($booking_transaction_details_value['extra_service_details']['baggage_details']) == true && valid_array($booking_transaction_details_value['extra_service_details']['baggage_details']) == true) {
                                        $baggage_details = $booking_transaction_details_value['extra_service_details']['baggage_details'];
                                        foreach ($baggage_details['baggage_source_destination_label'] as $bag_lk => $bag_lv) {
                                            ?>
                                 <tr>
                                    <td colspan="4" style="background-color:#d9d9d9; color:#555555"><span><?php echo 'Extra Baggage Information'; ?> ( <?= $bag_lv ?> )</span></td>
                                 </tr>
                                 <?php
                                    foreach ($baggage_details['details'] as $bag_k => $bag_v) {
                                        foreach ($bag_v as $bd_k => $bd_v) {
                                            // debug($bag_lv);exit;
                                            if ($bd_v['from_airport_code'] . '-' . $bd_v['to_airport_code'] == $bag_lv) {
                                                ?>
                                 <tr>
                                    <td colspan="4"><?= $bd_v['description'] ?> ( <?= $bag_v[0]['pax_name'] ?> )</td>
                                 </tr>
                                 <?php
                                    }
                                    }
                                    }
                                    }
                                    }
                                    // debug($booking_transaction_details_value);exit;
                                    if (isset($booking_transaction_details_value['extra_service_details']['meal_details']) == true && valid_array($booking_transaction_details_value['extra_service_details']['meal_details']) == true) {
                                    $meal_details = $booking_transaction_details_value['extra_service_details']['meal_details'];
                                    $meal_type = end($meal_details['details']);
                                    
                                    $meal_type = $meal_type[0]['type'];
                                    if ($meal_type == 'static') {
                                    $meal_type_label = 'Meal Preference';
                                    } else {
                                    $meal_type_label = 'Meal Information';
                                    }
                                    foreach ($meal_details['meal_source_destination_label'] as $meal_lk => $meal_lv) {
                                    ?>
                                 <tr>
                                    <td colspan="4" style="background-color:#d9d9d9; color:#555555"><span><?php echo $meal_type_label; ?> ( <?= $bag_lv ?> )</span></td>
                                 </tr>
                                 <?php
                                    foreach ($meal_details['details'] as $meal_k => $meal_v) {
                                        foreach ($meal_v as $md_k => $md_v) {
                                    
                                            if ($md_v['from_airport_code'] . '-' . $md_v['to_airport_code'] == $bag_lv) {
                                                ?>
                                 <tr>
                                    <td colspan="4"><?= $md_v['description'] ?> ( <?= $meal_v[0]['pax_name'] ?> )</td>
                                 </tr>
                                 <?php
                                    }
                                    }
                                    }
                                    }
                                    }
                                    if (isset($booking_transaction_details_value['extra_service_details']['seat_details']) == true && valid_array($booking_transaction_details_value['extra_service_details']['seat_details']) == true) {
                                    $seat_details = $booking_transaction_details_value['extra_service_details']['seat_details'];
                                    $seat_type = end($seat_details['details']);
                                    $seat_type = $seat_type[0]['type'];
                                    if ($seat_type == 'static') {
                                    $seat_type_label = 'Seat Preference';
                                    } else {
                                    $seat_type_label = 'Seat Information';
                                    }
                                    foreach ($seat_details['seat_source_destination_label'] as $seat_lk => $seat_lv) {
                                    ?>
                                 <tr>
                                    <td colspan="4" style="background-color:#d9d9d9; color:#555555"><span><?php echo $seat_type_label; ?> ( <?= $seat_lv ?> )</span></td>
                                 </tr>
                                 <?php
                                    foreach ($seat_details['details'] as $seat_k => $seat_v) {
                                        // debug($seat_lv);exit;
                                        foreach ($seat_v as $sd_k => $sd_v) {
                                            if ($sd_v['from_airport_code'] . '-' . $sd_v['to_airport_code'] == $seat_lv) {
                                                $seat_description = trim($sd_v['description']);
                                                if (empty($seat_description) == true) {
                                                    $seat_description = trim($sd_v['code']);
                                                }
                                                ?>
                                 <tr>
                                    <td colspan="4"><?= $seat_description ?> ( <?= $seat_v[0]['pax_name'] ?> )</td>
                                 </tr>
                                 <?php }
                                    }
                                    }
                                    }
                                    } ?>
                              </tbody>
                           </table>
                        </td>
                     </tr>
                     <?php } ?>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
            <td style="line-height:15px;padding:0;">&nbsp;</td>
         </tr>
         <!-- <tr>
            <td colspan="4" style="border-bottom:1px solid #999999;padding-bottom:15px"><span style="font-size:13px; color:#555;">Customer Contact Details | E-mail : <?= $booking_details['email'] ?> | Contact No : <?= $booking_details['phone'] ?></span></td>
         </tr>
         <tr>
            <td style="line-height:15px;padding:0;">&nbsp;</td>
         </tr> -->
         <tr>
            <td colspan="4"><span style="line-height:20px; font-size:13px;">Important Information</span></td>
         </tr>
         <tr>
            <td colspan="4" style="border-bottom:1px solid #999999; line-height:20px; font-size:12px; color:#555">
               <?php echo $data['terms_conditions']; ?>
               <!-- <ul style="padding-bottom:10px">
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
                  </ul> -->
            </td>
         </tr>
         <tr>
            <td colspan="4" align="right" style="padding-top:10px"><?php echo strtoupper($data['domainname']) ?><br>ContactNo :  <?php echo $data['phone_code'] ?><?php echo $data['phone'] ?><br><?php echo $data['address'] ?></td>
         </tr>
      </tbody>
   </table>
</div>
<?php } ?>