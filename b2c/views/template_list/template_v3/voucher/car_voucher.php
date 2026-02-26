<style type="text/css">
   td, th { padding: 8px }
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
.car_detl strong { font-size: 14px; }
strong { font-weight: 600 }
   @media print { 
   header, footer, #show_log, #printOption { display: none; }
   .pag_brk { page-break-before: always; }
   .our_partner img { height: auto; max-height: 30px !important; }
   .our_partner span {background: none !important;}
   }
</style>
<?php
   $booking_details = $data['booking_details'][0];
   // debug($booking_details);exit;
   $itineray_details = $booking_details ['itinerary_details'];
   $customer_details = $booking_details ['customer_details'];
   $attributes = json_decode($itineray_details[0]['attributes'], true);
   $priced_coverage = json_decode($itineray_details[0]['priced_coverage'], true);
   $cancellation_poicy = json_decode($itineray_details[0]['cancellation_poicy'], true);
   $extra_service_details = $booking_details ['extra_service_details'];
   // debug($booking_details);exit;
   ?>
<div class="table-responsive" style="width:100%; position:relative; padding: 10px 0" id="tickect_car">
   <table style="font-size:13px; font-family: 'Poppins', sans-serif; width:1000px; margin:10px auto;background-color:#fff; padding:50px 45px;" width="100%" cellpadding="0" cellspacing="0" border="0">
      <tbody>
         <tr>
            <td style="border-collapse: collapse; padding: 25px 35px;box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15)" >
               <table width="100%" style="border-collapse: collapse;" cellpadding="0" cellspacing="0" border="0">
                  <tr>
                     <td colspan="4" style="padding:0;padding-bottom:15px">
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
				         <tbody>
				            <tr>
				               <td style="font-size:20px; line-height:32px; font-weight:500; text-align:center;vertical-align: bottom; padding: 0">Car Voucher</td>
				            </tr>
				         </tbody>
				      </table>
				   </td>
				</tr>
				<tr><td style="line-height:20px;padding: 0">&nbsp;</td></tr>
                  <!-- <tr><td style="padding: 10px;width:65%;"><span style="max-height: 56px" ><img src="<?php echo $booking_details['car_image']?>" width ="100" height="100"/></span></td></tr> -->
                           <tr>
                              <td colspan="4" style="border:2px solid color(srgb 0.25 0.25 0.25 / 0.25); padding:0;">
			                        <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px;">
			                           <tbody>
			                            <tr>
                                       <td width="100%"
                                          style="background: #F9F5F1;padding: 12px;font-size:15px; line-height: 24px; color: #3f3f3f; font-weight: 500"><strong style="font-weight: 600">Reservation Ticket</strong> (<?php echo ucfirst($booking_details['car_pickup_lcation']).' To '.ucfirst($booking_details['car_drop_location']);?>)</td>
                                    </tr>
                                    <tr>
                                       <td style="padding: 12px">
                                          <table class="car_detl" width="100%" cellpadding="5"
                                             style="font-size:15px; line-height: 24px; color: #3f3f3f; padding: 10px 10px;">
                                             <tr>
                                                <td width="20%"><strong>Car name</strong></td>
                                                <td width="30%"><?php echo $booking_details['car_name'];?></td>
                                                <td width="20%"><strong>Reference Number</strong></td>
                                                <td width="30%"><?php echo $booking_details['booking_reference'];?></td>
                                             </tr>
                                             <tr>
                                                <td width="20%"><strong>Supplier Name</strong></td>
                                                <td width="30%"><?php echo $booking_details['car_supplier_name'];?></td>
                                                <td width="20%"><strong>Supplier Idenfier</strong></td>
                                                <td width="30%"><?php echo $booking_details['supplier_identifier'];?></td>
                                             </tr>
                                             <tr>
                                                <td width="20%"><strong>Passengers</strong></td>
                                                <td width="30%"><?php echo $attributes['pass_quantity'];?></td>
                                                <td width="20%"><strong>Doors</strong></td>
                                                <td width="30%"><?php echo $attributes['door_count'];?></td>
                                             </tr>
                                             <tr>
                                                <td width="20%"><strong>Bags</strong></td>
                                                <td width="30%"><?php echo $attributes['bagg_quantity'];?></td>
                                                <td width="20%"><strong>AirConditioning</strong></td>
                                                <td width="30%"><?php echo $attributes['air_condition'];?></td>
                                             </tr>
                                             <tr>
                                                <td><strong>Transmission</strong></td>
                                                <td><?php echo $attributes['transmission_type'];?></td>
                                                <td><strong>Booking Status</strong></td>
                                                <td class="st_lbl"><strong
                                                   class="<?php echo booking_status_label( $booking_details['status']);?>">
                                                   <?php
                                                      switch ($booking_details ['status']) {
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
                                                      
                                                      ?>
                                                   </strong>
                                                </td>
                                             </tr>
                                             <tr>
                                                <td><strong>PickUp Date Time</strong></td>
                                                <td><?php echo $booking_details['car_from_date'];?> <?php echo $booking_details['pickup_time'];?></td>
                                                <td><strong>Drop Date Time</strong></td>
                                                <td><?php echo $booking_details['car_to_date'];?> <?php echo $booking_details['drop_time'];?></td>
                                             </tr>
                                             <tr>
                                                <td><strong>Pickup Location</strong></td>
                                                <td><?php echo $booking_details['car_pickup_address'];?></td>
                                                <td><strong>Drop Location</strong></td>
                                                <td><?php echo $booking_details['car_drop_address'];?></td>
                                             </tr>
                                             <tr>
                                                <td><strong>Pickup Opening Hours</strong></td>
                                                <td><?php echo $attributes['pickup_opening_hours'];?></td>
                                                <td><strong>Drop Opening Hours</strong></td>
                                                <td><?php echo $attributes['drop_opening_hours'];?></td>
                                             </tr>
                                             <tr>
                                                <td><strong>Booking Date</strong></td>
                                                <td><?php echo $booking_details['created_datetime'];?></td>
                                                <td><strong>Travel Date Time</strong></td>
                                                <td><?=@date("d M Y",strtotime($booking_details['car_from_date']))?> <?=get_time($booking_details['pickup_time']);?></td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                                    <tr>
                                       <td style="padding: 0; line-height: 15px;">&nbsp;</td>
                                    </tr>
                    <tr>                     
                    	<td colspan="4" style="padding:0;">                        
                    		<table cellspacing="0" cellpadding="10" width="100%" style="font-size:15px; line-height: 24px; color: #3f3f3f;">                           
                    			<tbody>                              
                    				<tr>
                                       <td style="font-size:16px; line-height: 24px; color: #3f3f3f; padding: 15px; background: #FAF7F480;border-bottom: 1px dashed #93908A;">Travellers Information
                                       </td>
                                    </tr>
                                    <tr>
                                       <td style="padding: 0">
                                          <table width="100%" cellpadding="5"
                                             style="padding: 10px; font-size: 14px;">
                                             <tr>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background: #FAF7F480">First Name</td>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background: #FAF7F480">Last Name</td>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background: #FAF7F480">Phone</td>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background: #FAF7F480">Email</td>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background: #FAF7F480">City</td>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background: #FAF7F480">Country</td>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background: #FAF7F480">PinCode</td>
                                             </tr>
                                             <tr>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background-color: #F7F3EDCC;font-weight: 500"><?php echo $customer_details[0]['first_name'];?></td>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background-color: #F7F3EDCC;font-weight: 500"><?php echo $customer_details[0]['last_name'];?></td>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background-color: #F7F3EDCC;font-weight: 500"><?php echo $customer_details[0]['phone'];?></td>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background-color: #F7F3EDCC;font-weight: 500"><?php echo $booking_details['email'];?></td>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background-color: #F7F3EDCC;font-weight: 500"><?php echo $customer_details[0]['city'];?></td>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background-color: #F7F3EDCC;font-weight: 500"><?php echo $customer_details[0]['country_name'];?></td>
                                                <td style="font-size:14px; line-height: 24px; color: #3f3f3f; padding: 12px;background-color: #F7F3EDCC;font-weight: 500"><?php echo $customer_details[0]['pincode'];?></td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td style="padding: 0">
                                          <table width="100%" cellpadding="5"
                                             style="padding: 12px; font-size: 14px;background: #FAF7F480; border-top: 1px solid #E5E5E5">
                                             <tr style="font-size: 15px;">
                                                <td style="padding: 12px;font-size:16px; line-height: 24px; color: #3f3f3f;" colspan="5"><strong>Total Fare </strong></td>
                                              <td style="padding: 12px;" colspan="5" align="right"><strong style="font-weight: 500;font-size: 20px; background: #BF9766; color: #fff;padding: 4px 12px"><?=@$booking_details['currency']?>  <?=@$booking_details['grand_total']?></strong></td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
                                    <tr><td style="padding: 0; line-height: 20px">&nbsp;</td></tr>
                                    <tr>
                                       <td colspan="4" style="background: #F9F9F9CC; padding: 15px;">
                                          <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px; padding:0;">
                                             <tbody>
                                                <tr>
                                                   <td colspan="4"><span style="line-height:26px;font-size: 15px;font-weight: 500; color: #3f3f3f; padding-bottom: 5px;">Rental Price Includes</span></td>
                                                </tr>
                                                <tr>
                                                   <td colspan="4" style="line-height:22px; font-size:13px; color:#3f3f3f;padding-bottom: 5px;padding-top:5px">
                                                      <?php 
                                             if($priced_coverage){
                                             foreach($priced_coverage  as $key => $coverage){
                                             $key = $key+1;
                                             if(!empty($coverage['Desscription'])){
                                             	echo $key.". ".$coverage['CoverageType'].' - '.$coverage['Desscription'];
                                             
                                             }
                                             else{
                                             	echo $key.". ".$coverage['CoverageType'];
                                             
                                             }
                                             echo "<br/>";
                                             ?>
                                          <?php } 
                                             }?>
                                                   </td>
                                                </tr>
                                             </tbody>
                                          </table>
                                       </td>
                                    </tr>
                                    <tr><td style="padding: 0; line-height: 20px">&nbsp;</td></tr>
                                    <tr>
                                       <td colspan="4" style="background: #F9F9F9CC; padding: 15px;">
                                          <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px; padding:0;">
                                             <tbody>
                                                <tr>
                                                   <td colspan="4"><span style="line-height:26px;font-size: 15px;font-weight: 500; color: #3f3f3f; padding-bottom: 5px;">Payment Details</span></td>
                                                </tr>
                                                <tr>
                                                   <td colspan="4" style="line-height:22px; font-size:13px; color:#3f3f3f;padding-bottom: 5px;padding-top:5px">
                                                      <?php echo $attributes['payment_rule'];?>
                                                   </td>
                                                </tr>
                                             </tbody>
                                          </table>
                                       </td>
                                    </tr>
                                    <tr><td style="padding: 0; line-height: 20px">&nbsp;</td></tr>
                                    <tr>
                                       <td colspan="4" style="background: #F9F9F9CC; padding: 15px;">
                                          <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px; padding:0;">
                                             <tbody>
                                                <tr>
                                                   <td colspan="4"><span style="line-height:26px;font-size: 15px;font-weight: 500; color: #3f3f3f; padding-bottom: 5px;">Cancellatio Policy</span></td>
                                                </tr>
                                                <tr>
                                                   <td colspan="4" style="line-height:22px; font-size:13px; color:#3f3f3f;padding-bottom: 5px;padding-top:5px">
                                                      <?php if(isset($cancellation_poicy) && !empty($cancellation_poicy)){
                                             foreach($cancellation_poicy as $policy){
                                             	if($policy['Amount'] == 0){
                                             		echo 'No Cancellation Fee between ' .$policy['FromDate'].' To '.$policy['ToDate'];
                                             	}
                                             	else{
                                             		echo  $booking_details['currency'].' '.$policy['Amount'].' Cancellation Fee between ' .$policy['FromDate'].' To '.$policy['ToDate'];
                                             	}
                                             	echo "<br/>";
                                             } }?>
                                                   </td>
                                                </tr>
                                             </tbody>
                                          </table>
                                       </td>
                                    </tr>
                                    <tr><td style="padding: 0; line-height: 20px">&nbsp;</td></tr>
                                    <?php 
                                       // debug($booking_details);exit;
                                       if((isset($extra_service_details) && valid_array($extra_service_details)) || ($booking_details['oneway_fee']!= 0)){ ?>

                                    <tr>
                                       <td colspan="4" style="background: #F9F9F9CC; padding: 15px;">
                                          <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px; padding:0;">
                                             <tbody>
                                                <tr>
                                                   <td colspan="4"><span style="line-height:26px;font-size: 15px;font-weight: 500; color: #3f3f3f; padding-bottom: 5px;">Pay at Pickup</span></td>
                                                </tr>
                                                <tr>
                                                   <td colspan="4" style="line-height:22px; font-size:13px; color:#3f3f3f;padding-bottom: 5px;padding-top:5px">
                                             <?php
                                             foreach($priced_coverage as $coverage){
                                             	if($coverage['IncludedInRate'] != true && $coverage['Amount'] != 0) {
                                             		// debug($coverage);exit;
                                             		echo $coverage['CoverageType'] .' - '.$coverage['Currency'].' '.$coverage['Amount'].' '.$coverage['Desscription'];
                                             		echo "<br/>";
                                             	}
                                             	
                                             }
                                             
                                             ?>
                                          <?php
                                             foreach($extra_service_details as $details){
                                             	echo $details['description'] .' - '.$booking_details['currency'].' '.$details['amount'];
                                             	echo "<br/>";
                                             }
                                             
                                             ?>
                                                   </td>
                                                </tr>
                                             </tbody>
                                          </table>
                                       </td>
                                    </tr>
                                    <?php } ?>
                                    <tr><td style="padding: 0; line-height: 20px">&nbsp;</td></tr>
                                    <tr>
                                       <td colspan="4" style="background: #F9F9F9CC; padding: 15px;">
                                          <table cellspacing="0" cellpadding="5" width="100%" style="font-size:12px; padding:0;">
                                             <tbody>
                                                <tr>
                                                   <td colspan="4"><span style="line-height:26px;font-size: 15px;font-weight: 500; color: #3f3f3f; padding-bottom: 5px;">Important Information</span></td>
                                                </tr>
                                                <tr>
                                                   <td colspan="4" style="line-height:22px; font-size:13px; color:#3f3f3f;padding-bottom: 5px;padding-top:5px">
                                                      <ul style="padding-bottom:5px">
                                                         <li>Please ensure that operator PNR is filled, otherwise the ticket is not valid.</li>
                                                      </ul>
                                                   </td>
                                                </tr>
                                             </tbody>
                                          </table>
                                       </td>
                                    </tr>
                           <tr>
                              <td style="line-height:40px; padding: 0">&nbsp;</td>
                           </tr>
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
                           </tbody>
                       </table>
                   </td>
               </tr>
           </table>
       </td>
   </tr>
</tbody>
</table>
</div>
   <table id="printOption"
      style="border-collapse: collapse; font-size: 14px; margin: 20px auto 10px; font-family: arial;"
      width="70%" cellpadding="0" cellspacing="0" border="0">
      <tbody>
         <tr>
            <td align="center"><input
               style="background: #D6B97B; height: 34px; padding: 4px 15px; border-radius: 4px; border: none; color: #fff; margin: 0 2px;"
               type="button" id="printBtn" value="Print" />
         </tr>
      </tbody>
   </table>
<script>
   document.getElementById('printBtn').onclick = function () {
       const content = document.getElementById('tickect_car');
       if (!content) {
           alert("Content to print not found!");
           return;
       }
   
       const win = window.open('', '_blank');
       win.document.write('<html><head><title>Print</title>');
       win.document.write('<style>body{font-family:Arial;} table{border-collapse:collapse; width:100%;} td, th{padding:8px;}</style>');
       win.document.write('</head><body>');
       win.document.write(content.innerHTML);
       win.document.write('</body></html>');
       win.document.close();
   
       // Use a short delay to allow rendering
       setTimeout(() => {
           win.focus();
           win.print();
           win.close();
       }, 500); // 0.5 second is usually enough
   };
   
</script>