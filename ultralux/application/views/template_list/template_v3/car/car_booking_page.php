<style>
.formlabel {color:black;}
#cncelPolicy_id {font-weight:bold;}
.detlnavi ul li {font-size: 14px;}
#cncelPolicy_id {color:red;}
.features {
    display: block;
    margin-top: 0px; float: left; width:34%;
    overflow: hidden;
}
.features li {
    float: left;
    padding: 0px 5px;
    width: auto;
    border-right: 1px solid #cbcbcc;
}

.features li:last-child { border-right:none; }

.features li strong {
    color: #525252;
    display: block;
    float: left;
    font-size: 15px;
    font-weight: normal;
    line-height: 25px;
}

.middleCol {
    display: block; margin-top: 15px;
    overflow: hidden;
}

.car_image img {
    max-width: 100%;
    min-height: 80px !important;
    max-height: 90px;
}

.c_tool .tooltip { width: auto !important; float: left; background: none !important; border-radius: 3px;} 
.c_tool .tooltip.left { padding: 0px !important; }
.c_tool .tooltip-inner { padding: 2px 7px !important; background: #333 !important; max-width: 100% !important; }
.c_tool .tooltip-inner .table { margin-bottom: 0px !important; background: #333 !important; }
.c_tool .tooltip.left .tooltip-arrow { right: -5px !important; border-left-color: #333; }
.c_tool .tooltip.in { opacity: 1 !important; }
  

.suplier_logo {
    display: block;text-align: center; width:30%; float: left;
}

.suplier_logo img { width: 120px; margin-top: 10px; }

.flitruo_hotel {display: block;margin: 6px 0;overflow: hidden;padding: 0 10px 0 0;
}
.ifroundway .flitruo {border-bottom: 1px dashed #ddd;
}
.ifroundway .flitruo:last-child{ border-bottom:none;}
.oneplus{ display:none;background: #e0e0e0 none repeat scroll 0 0;}
.oneonly{opacity:0;}
.plusone .oneplus{ display: inline-block;}
.morestop .oneonly{ opacity:1;}
.ifroundway .fligthsmll{margin: 90px 10px 10px;}
.hoteldist {display: block;overflow: hidden;}
.travlrs {
    color: #999;
    display: block;
    font-size: 16px;
    margin: 0 0 15px;
    overflow: hidden;
}

.portnmeter {
    color: #0a9ed0;
    display: block;
    font-size: 13px;
    overflow: hidden;
}

.fare_loc {
    font-size: 14px;
    color: #5b5b5b;
    font-weight: 500;
}
.sectionbuk .lbllbl { color: #666 !important; }
.pick { width:30%; margin-right:1.5%; float:left; margin-top:0.5%; font-size: 14px;}
.pick .fa { font-size: 16px; }   
.pick span { font-weight: 500; color: #848383; }            
      

.pick h3 { font-size:13px; font-weight:normal; margin:0px; margin-top:2px;  line-height:15px; color:#333;  /*overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;*/     padding-left: 16px;}

.car_name{color: #f88c3e;display: block;font-size: 18px;font-weight: 500;overflow: hidden;}
.car_name span{color: #545454;
    font-size: 13px;}
.madgrid {
    background: #ffffff none repeat scroll 0 0;
    border: 1px solid #e0e0e0;
    box-shadow: 0 0 3px #d2d2d2;
    display: block;
    float: left;
    margin: 8px 0;
    overflow: inherit;
    width: 100%;
    transition: all 400ms ease-in-out 0s;
}

.width80 {
    width: 80%;
}

.waymensn {display: block;overflow: hidden;
}

.features li.person span {
    background-position: 0 0;
}
.features li.transmission span {
    background-position: -24px 0;
}
.features li.baggage span {
    background-position: 0 -24px;
}
.features li.ac span {
    background-position: -48px 0;
}
.features li.doors span {
    background-position: -24px -24px;
}
.features li.fuel span {
    background-position: 0 -48px;
}
.car_image {
    display: block;
    line-height: 120px;
    margin: 5px;
    overflow: hidden;
    text-align: center;
}
.payinput1 {
    border: 1px solid #d6d6d6;
    border-radius: 3px;
    color: #333333;
    display: block;
    font-size: 14px;
    height: 45px;
    overflow: hidden;
    padding: 10px 3px;
    width: 100%;
}
.lokter {
    margin-top: 22px;
}
.add_extras img {
    width: 15px;
    margin-right: 5px;
    vertical-align: top;
}
#ui-datepicker-div.ui-datepicker .ui-datepicker-title {
    float: left;
    width: 60%;
}
.ui-datepicker select.ui-datepicker-month, .ui-datepicker select.ui-datepicker-year {
    float: right;
}
.fl_list { padding-left: 30px; }
.fl_list li { list-style-type: square;width: 50%;float: left;}
.list.fl_list li { width: 100%; margin-bottom: 6px;}
.cust_mdl1 iframe { width: 100%; height: 350px;}
/*responsive*/
@media ( max-width :767px) {
.celhtl.midlbord { width: 20% !important;}
.pick h3 {padding-left: 0;font-size: 11px; }
.pick span { font-size: 13px;}
.features { margin-top: 5px;}
.suplier_logo img { margin-top: 0;}
.sidenamedesc { width: 100%;}
}

@media ( max-width :480px) {
.car_name { font-size: 15px;line-height: 16px;}
.features li .mn-icon { margin: 0 0px 0 2px; }
.features { width: 70% !important;}
.suplier_logo img {width: 100%;}
.repeatprows .set_margin { margin: 0;}
}
@media ( min-width :481px) and (max-width:767px) {

}
@media ( min-width :768px) and (max-width:991px) {
.pick span { font-size: 13px;}
}
@media ( min-width :992px) and (max-width:1199px) {
}

@media ( min-width :992px) {
  .celhtl.width20.midlbord {
    display: table-cell;
    vertical-align: middle;
    float: none;
    width: 20%;
}
.width80 {
    width: 80%;
    display: table-cell;
    vertical-align: middle;
    float: none;
}
.sidenamedesc {
    display: table;
    width: 100%;
}

.c_tool .tooltip.top .tooltip-arrow {
    bottom: -7px;
    left: 50%;
    margin-left: -5px;
    border-width: 5px 5px 0;
    border-top-color: #20364f;
}
.c_tool .tooltip.top {
    padding: 0;
    margin-top: -3px;
    background: #fff !important;
    border: 2px solid #20364f;
    border-radius: 3px;
    max-width: 200px !important;
}
.c_tool .tooltip-inner {
    padding: 10px !important;
    background: #fff !important;
    max-width: 100% !important;
    color: #333;
    /* max-width: 200px !important; */
    text-align: left;
    font-family: 'Aller', sans-serif;
    font-size: 13px;
}
  }
</style>
<?php  
$book_login_auth_loading_image   = '<div class="text-center loader-image"><img src="'.$GLOBALS['CI']->template->template_images('loader_v3.gif').'" alt="please wait"/></div>';

// debug($car_search_params);exit('booking view');
// echo $no_of_day;die;
Js_Loader::$css[] = array('href' => $GLOBALS['CI']->template->template_css_dir('page_resource/car_new_result.css'), 'media' => 'screen');
Js_Loader::$css[] = array('href' => $GLOBALS['CI']->template->template_css_dir('page_resource/pre_booking.css'), 'media' => 'screen');
Js_Loader::$css[] = array('href' => $GLOBALS['CI']->template->template_css_dir('page_resource/car_pre_booking.css'), 'media' => 'screen');
Js_Loader::$css[] = array('href' => $GLOBALS['CI']->template->template_css_dir('page_resource/animation.css'), 'media' => 'screen');
Js_Loader::$css[] = array('href' => $GLOBALS['CI']->template->template_css_dir('owl.carousel.min.css'), 'media' => 'screen');
Js_Loader::$css[] = array('href' => $GLOBALS['CI']->template->template_css_dir('page_resource/car_result.css'), 'media' => 'screen');
// Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/flight_session_expiry_script.js'), 'defer' => 'defer');
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/booking_script.js'), 'defer' => 'defer');
$template_images = $GLOBALS['CI']->template->template_images(); 
// debug( $car_rules);exit;
$car_details = $car_rules['RateRule']['CarRuleResult'][0];

if(isset($car_details) && valid_array($car_details)){ 
$module_value = md5('car');

// debug($car_details);die();
//echo $cancel_description;exit;
$pickup_date_val = '';
$return_date_val = '';
$PickUpDateTime = $car_details['PickUpDateTime'];
$pickup_date = explode( 'T', $PickUpDateTime );
$pickup_date_val = date('d M Y', strtotime($pickup_date[0]));
$pickup_time_val = date('H:i', strtotime($pickup_date[1]));
$ReturnDateTime = $car_details['ReturnDateTime'];
$return_date = explode( 'T', $ReturnDateTime);
$return_date_val = date('d M Y', strtotime($return_date[0]));
$return_time_val = date('H:i', strtotime($return_date[1]));
$Vehicle_name = $car_details['Name'];
$PictureURL = $car_details['PictureURL'];
$CurrencyCode = $currency_obj->get_currency_symbol($currency_obj->to_currency);
$default_currency = $currency_obj->to_currency;
$exclusion = $car_details['PricedCoverage'];
// debug($page_data);exit;
$total_estimate_amount = $car_details['TotalCharge']['EstimatedTotalAmount'];
$car_details['convenience_fees'] = $convenience_fees =$page_data['convenience_fees'];
$total_amount = roundoff_number($car_details['convenience_fees']+$car_details['TotalCharge']['EstimatedTotalAmount']);
$total_amount = ceil($total_amount);
$TotalEstimateAmount = $total_amount;
$onewayFee = $car_details['TotalCharge']['OneWayFee'];
$TotalEstimateAmount += $onewayFee;
// debug($total_amount);exit;
// echo $TotalEstimateAmount;exit;
$fuel_policy_code = '';
$fuel_policy_desc ='';
if(isset($car_details['PricedCoverage']) && !empty($car_details['PricedCoverage']))
{
  foreach($car_details['PricedCoverage'] as $key => $pricedCoverage)
  { 
    if($pricedCoverage['Code'] == 'F2F'){      
      $fuel_policy_code .= $pricedCoverage['Code'];
      $fuel_policy_desc .= @$pricedCoverage['Description'];
    }
  }
}
}
if (is_logged_in_user ()) {
  $travellers_tab_details_class = ' gohel ';
  $review_tab_details_class = '';
}
else{
    $travellers_tab_details_class = ' ';
   $review_tab_details_class = ' gohel ';
}
$phone_code = $page_data['phone_code'];
$active_data = $page_data['active_data'];
$PricedCoverage = $car_details['PricedCoverage'];
$pax_title_enum = get_enum_list ('title');
unset($pax_title_enum[MASTER_TITLE]); // Master is for child so not required
unset($pax_title_enum[MISS_TITLE]); // Master is for child so not required
unset($pax_title_enum[A_MASTER]); // Master is for child so not required

$country = array($page_data['active_data']['api_country_list_fk']);
$country = '';
$country_list = generate_options($page_data['country_list'], $country);
$datepicker_list = array(array('date-picker-dobs', CARADULT_DATE_PICKER));
$this->current_page->set_datepicker($datepicker_list);
$pax_details = $page_data['pax_details'];
$user_country_code = $page_data['user_country_code'];
if(isset($pax_details[0]['title'])){
  $pax_title[0] = $pax_details[0]['title'];
}
else{
  $pax_title = false;
}

$pax_title_options = generate_options($pax_title_enum, $pax_title, true);
// debug($pax_title_options);exit;
// debug($page_data['active_data']);
// debug($pax_details);
// exit;
if (is_logged_in_user()) {
    $review_active_class = ' success ';
    $review_tab_details_class = '  ';
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

?>
<div class="fldealsec">
    <div class="container-fluid">
        <div class="tabcontnue">
            <div class="col-xs-4 nopadding">
                <div class="rondsts <?= $review_active_class ?>">
                    <a class="taba core_review_tab <?= $review_tab_class ?>" id="stepbk1">
                        <div class="iconstatus fa fa-eye"></div>
                        <div class="stausline">Review</div>
                    </a>
                </div>
            </div>
            <div class="col-xs-4 nopadding">
                <div class="rondsts <?= $travellers_active_class ?>">
                    <a class="taba core_travellers_tab <?= $travellers_tab_class ?>" id="stepbk2">
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

<div class="full onlycontent top80 car_bk">
  <div class="container-fluid martopbtm">
    <div class="paymentpage1">
    <div class="bktab1 xlbox <?=$review_tab_details_class?>">
      <button id="back_btn" class="btn waves-effect waves-light"  name="action"><i class="far fa-chevron-left"></i> Back</button>
      <h3 class="htl_rvw">Review your Booking</h3>
      <div class="col-md-4 col-sm-4 nopadding_right frmbl sidebuki nopad" id="sidebar">
         <div class="col-xs-12 nopadding rit_summery test">
         <div class="insiefare">
            <div class="farehd arimobold">Fare Summary</div>
            <div class="fredivs">
               <div class="kindrest">
                  <div class="freshd">Pick Up</div>
                   <div class="reptallt">
                    
                     <div class="col-xs-12 nopadding">
                        <div class="amnter"><?=@$car_details['LocationDetails']['PickUpLocation']['Address']['StreetNmbr'].', '.$car_details['LocationDetails']['PickUpLocation']['Address']['CityName']; ?></div>
                     </div>
                  </div>
               </div>
               <div class="kindrest">
                  <div class="freshd">Drop Off</div>
                  <div class="reptallt">
                    
                     <div class="col-xs-12 nopadding">
                        <div class="amnter"><?=@$car_details['LocationDetails']['DropLocation']['Address']['StreetNmbr'].', '.$car_details['LocationDetails']['PickUpLocation']['Address']['CityName']; ?></div>
                     </div>
                  </div>
               </div>
               <?php 
            // debug($car_details);exit;
             if(isset($car_details['LocationDetails']) && valid_array($car_details['LocationDetails']))
             { 
              ?>
              <div class="kindrest">
               <div class="bokkpricesml nopad">
                <div class="freshd">Business Hours</div>
                <?php foreach($car_details['LocationDetails'] as $location){ 
                  // debug($location);exit;
                  ?>
                  <div class="reptallt">
                     <div class="col-xs-7 nopadding">
                        <div class="faresty"><?php echo $location['value']['Name']; ?></div>
                     </div>
                     <div class="col-xs-5 nopadding">
                        <div class="amnter"><?php echo $location['OperationSchedules']['Start']; ?> to <?php echo $location['OperationSchedules']['End']; ?></div>
                     </div>
                  </div>
                <?php } ?>
               </div>
              </div>
              <?php } ?>
             
             <div class="fare_show">
              <!-- <h5 class="base_f">Fare Breakup <i class="fa fa-chevron-down"></i></h5> -->
              <div class="show_fares_table reptallt">
               <table class="table">
                <tbody>
                 <tr>
                  <td class="faresty" style="width:55%; padding: 7.5px 0 15px !important; border:none">Car Rental Price</td>
                  <td class="text-center amnter" style="text-align: right; padding: 7.5px 0 15px !important; border:none"><?=$currency_obj->get_currency_symbol($currency_obj->to_currency)?> <span id="CarRentalPrice"><?php echo $TotalEstimateAmount; ?></span></td>
                 </tr>
                 <?php if(isset($page_data['convenience_fees']) && $page_data['convenience_fees'] !='0'){?>
                 <tr>
                  <td style="width:55%">Convience Fee</td>
                  <td class="text-center" style="text-align: right"><?=$currency_obj->get_currency_symbol($currency_obj->to_currency)?> <span id="ConvienceFee"><?php echo $page_data['convenience_fees']; ?></span></td>
                 </tr>
                 <?php } ?>
                  <?php
                  // debug($car_details);exit;
                    $Coverage_amount = array();
                    if(isset($PricedCoverage) && valid_array($PricedCoverage)){
                        $in_key = 0;
                       foreach ($PricedCoverage as $p_key => $Coverage) {
                        
                       if($Coverage['Amount'] != 0){ 
                        $Coverage_amount[] = $Coverage['Amount']; 
                        if($in_key == 0){
                        ?>
                        <tr>
                          <td style="width:56%; border:none;color: #0a9ed0;;">Includes the following fees</td>
                          <td class="text-center" style="border:none;">&nbsp;</td>
                        </tr>
                        <?php } ?>
                        <tr>
                          <td style="width:65%; border:none;color: #0a9ed0;;"><?=$Coverage['CoverageType']?></td>
                          <td style="width:35%; border:none;color: #0a9ed0;text-align: right;" ><?=$currency_obj->get_currency_symbol($currency_obj->to_currency).' '.$Coverage['Amount']?></td>
                        </tr>
                       <?php $in_key++; 
                        }
                       }

                    }
                  ?>
                 <tr>
                  <!-- <td class="to_bo">Total Amount Due</td> -->
                  <td class="to_bo nopad" style="border-top: 2px solid #E3E3E3; padding-top: 7.5px !important"><div class="farestybig">Grand Total</div></td>
                  <td class="text-center nopad" style="text-align: right;border-top: 2px solid #E3E3E3; padding-top: 7.5px !important">
                   <div class="amnterbig arimobold grandtotal"><span class="style_currency"><?=$currency_obj->get_currency_symbol($currency_obj->to_currency)?></span> <span class="discount_total"><?php echo $total_amount; ?></span></div>
                  </td>
                 </tr>
                </tbody>
               </table>
              </div>
             </div>
            </div>
         </div>
      <div class="clearfix"></div>
        <div class="cartbukdis hide">
          <ul class="liscartbuk">
            <li class="lostcart ">            
            <div class="faresum">
             <h3>Purchase Summary</h3>
             <div class="booking_div">
             <div class="col-xs-12 nopad colrcelo">
              <div class="bokkpricesml">
               <div class="travlrs col-md-6 nopad">
                <span class="portnmeter">Pick Up</span>
                <div class="fare_loc"><?=@$car_details['LocationDetails'][0]['@attributes']['Name'] ?></div>
                <div class="date_loc"><?php echo $pickup_date_val.' '.$pickup_time_val; ?></div>
               </div>
               <div class="travlrs col-md-6 nopad">
                <span class="portnmeter">Drop Off</span>
                <div class="fare_loc"><?=@$car_details['LocationDetails'][1]['@attributes']['Name'] ?></div>
                <div class="date_loc"><?php echo $return_date_val.' '.$return_time_val; ?></div>
               </div>
              
              </div>
             </div>
             <?php 
            // debug($car_details);exit;
             if(isset($car_details['LocationDetails']) && valid_array($car_details['LocationDetails']))
             { 
              ?>
              <div class="col-xs-12 nopad colrcelo">
               <div class="bokkpricesml">
                <span class="portnmeter">Business Hours</span>
                <?php foreach($car_details['LocationDetails'] as $location){ 
                  // debug($location);exit;
                  ?>
                 <span class="business_hour col-md-6 nopad">
                  <div class="loc_name"><?php echo $location['value']['Name']; ?> </div> 
               
                    <!-- <input type="text" name="buss_time_<?php echo $t_key?>" value="<?php echo $t_value; ?>">  -->
                   <div class="loc_time"><?php echo $location['OperationSchedules']['Start']; ?> to <?php echo $location['OperationSchedules']['End']; ?></div>
                 
                 </span>
                <?php } ?>
               </div>
              </div>
              <?php } ?>
             
             <div class="fare_show">
              <!-- <h5 class="base_f">Fare Breakup <i class="fa fa-chevron-down"></i></h5> -->
              <div class="show_fares_table">
               <table class="table table-striped">
                <tbody>
                 <tr>
                  <td style="width:55%">Car Rental Price</td>
                  <td class="text-center" style="text-align: right"><?=$currency_obj->get_currency_symbol($currency_obj->to_currency)?> <span id="CarRentalPrice"><?php echo $TotalEstimateAmount; ?></span></td>
                 </tr>
                 <?php if(isset($page_data['convenience_fees']) && $page_data['convenience_fees'] !='0'){?>
                 <tr>
                  <td style="width:55%">Convience Fee</td>
                  <td class="text-center" style="text-align: right"><?=$currency_obj->get_currency_symbol($currency_obj->to_currency)?> <span id="ConvienceFee"><?php echo $page_data['convenience_fees']; ?></span></td>
                 </tr>
                 <?php } ?>
                  <?php
                  // debug($car_details);exit;
                    $Coverage_amount = array();
                    if(isset($PricedCoverage) && valid_array($PricedCoverage)){
                        $in_key = 0;
                       foreach ($PricedCoverage as $p_key => $Coverage) {
                        
                       if($Coverage['Amount'] != 0){ 
                        $Coverage_amount[] = $Coverage['Amount']; 
                        if($in_key == 0){
                        ?>
                        <tr>
                          <td style="width:56%; border:none;color: #0a9ed0;;">Includes the following fees</td>
                          <td class="text-center" style="border:none;">&nbsp;</td>
                        </tr>
                        <?php } ?>
                        <tr>
                          <td style="width:65%; border:none;color: #0a9ed0;;"><?=$Coverage['CoverageType']?></td>
                          <td style="width:35%; border:none;color: #0a9ed0;text-align: right;" ><?=$currency_obj->get_currency_symbol($currency_obj->to_currency).' '.$Coverage['Amount']?></td>
                        </tr>
                       <?php $in_key++; 
                        }
                       }

                    }
                  ?>
                
                 <tr>
                  <!-- <td class="to_bo">Total Amount Due</td> -->
                  <td class="to_bo">Pay Now</td>
                  <td class="text-center" style="text-align: right;">
                   <span class="style_currency"><?=$currency_obj->get_currency_symbol($currency_obj->to_currency)?></span> <span class="discount_total"><?php echo $total_amount; ?></span>
                  </td>
                 </tr>
                </tbody>
               </table>
              </div>
             </div>
            </div>
           </div>
           </li>
            <?php 
         
            if(isset($PricedCoverage) && valid_array($PricedCoverage) && !empty($Coverage_amount)){ 
            ?>
            <li class="lostcart" id="extra_box">
            <div class="faresum">
             <h3>Pay On Pick Up </h3>
              <div class="booking_div">
             <div class="fare_show">
              <div class="show_fares_table">
                <table class="table table-striped">
                  <tbody id="extras_holders">
                   <?php foreach ($PricedCoverage as $p_key => $Coverage) {
                    if($Coverage['Amount'] != 0){
                    ?>
                    <tr>
                    <td style="width:65%"><?=$Coverage['CoverageType']?></td>
                    <td style="width:35%;text-align:right" class="text-center" ><?=$currency_obj->get_currency_symbol($currency_obj->to_currency).' '.$Coverage['Amount']?>
                    </td>
                    </tr>
                    <?php } } ?>
                  </tbody>
                  <tbody id="extrass_holder_0"></tbody>
                  <tbody id="extrass_holder_1"></tbody>
                  <tbody id="extrass_holder_2"></tbody>
                </table>
              </div>
             </div>
            </div>
            </div>
           </li>
           <?php } ?>
          </ul>
        </div>
        <div class="clearfix"></div>


          <div class="col-xs-12 ad_info">
                    <div class="cartlistingbuk">
                <!-- <div class="cartitembuk prompform"> -->
                 
                            <div class="col-md-12 col-xs-12 nopadding_right pr_code nopad">
                              <div class="cartprc">
                                <div class="payblnhm singecartpricebuk ritaln">
                               <input type="text" placeholder="Enter Promo Code" name="code" id="code" class="promocode" aria-required="true" />
                                  <input type="hidden" name="module_type" id="module_type" class="promocode" value="<?=@$module_value;?>" />
                                  <input type="hidden" name="total_amount_val" id="total_amount_val" class="promocode" value="<?=@$total_estimate_amount;?>" />
                                  <input type="hidden" name="convenience_fee" id="convenience_fee" class="promocode" value="<?=@$convenience_fees;?>" />
                                  <input type="hidden" name="currency_symbol" id="currency_symbol" value="<?=@$CurrencyCode;?>" />
                                  <input type="hidden" name="currency" id="currency" value="<?=@$default_currency;?>" />
                                 
                                  <p class="error_promocode text-danger"></p>                     
                                </div>
                              </div>
                              <input type="button" value="Apply" name="apply" id="apply" class="promosubmit">
                            </div>
                        
                </div>
                <div class="clearfix"></div>
                <div class="savemessage"></div>
              </div>
      </div>
      </div>
    
      <div class="col-md-8 col-sm-8 nopad fulbuki">
        <div class="col-xs-12 nopad full_room_buk">
         <div class="bookcol">
            <div class="hotelistrowhtl">
               <div class="col-md-12 col-xs-12 nopad">
                  <div class="imagehotel">    
                     <img alt="Svenska Bangalore" src="<?=$PictureURL?>">              
                  </div>
               </div>
               <!-- <span class="usr_rat">5</span> -->
               <div class="col-md-12 col-xs-12 padall10">
                  <div class="htl_loct">
                     <div class="hotelhed"><?= $Vehicle_name ?><span> or Similar</span></div>
                     <div class="clearfix"></div>
                     <div class="mensionspl">Starts in Hanoi. Ends in Siem Reap</div>
                  </div>
                  <!-- <div class="clearfix"></div>  <div class="bokratinghotl rating-no">   
                     <span class="star 1 active"></span><span class="star 2 "></span><span class="star 3 "></span><span class="star 4 "></span><span class="star 5 "></span>  </div> -->  
                  <div class="sckint">
                     <div class="ffty">
                        <div class="borddo brdrit">
                           <span class="lblbk_book"><span class="fa fa-calendar"></span>Pick Up</span>   
                           <div class="fuldate_book">
                              <?php echo $pickup_date_val.' '.$pickup_time_val; ?>
                           </div>
                           <!--<div class="clearfix"></div>  <span>12:00 PM</span>-->
                        </div>
                     </div>
                     <i class="far fa-long-arrow-right"></i>  
                     <div class="ffty">
                        <div class="borddo">
                           <span class="lblbk_book"> <span class="fa fa-calendar"></span>Drop Off</span>   
                           <div class="fuldate_book"><?php echo $return_date_val.' '.$return_time_val; ?>
                           </div>
                           <!--<div class="clearfix"></div>  <span>12:00 PM</span>-->
                        </div>
                     </div>
                     <div class="nigthcunt hide">Night(s) 4, Room(s) 1</div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!--  -->   
        <div class="madgrid hide">
          <div class="col-xs-12 nopad">
            <div class="sidenamedesc">
              <div class="celhtl width20 midlbord">
                <div class="car_image"> <img class="lazy lazy_loader h-img" src="<?=$PictureURL?>" onError="this.onerror=null;this.src='<?php echo $GLOBALS['CI']->template->template_images('no-img.jpg'); ?>';" style="display: inline-block;"></div>
              </div>
              <div class="celhtl width80">
                <div class="waymensn">
                  <div class="flitruo_hotel">
                    <div class="hoteldist">
                      <span class="car_name"><?= $Vehicle_name ?><span> or Similar</span></span>
                      <div class="clearfix"></div>
                      <div class="clearfix"></div>
                      <!-- <div class="pick cr_wdt">
                        <i class="fa fa-map-marker"></i> <span>Pickup Location:</span>
                        <?php //debug($car_details['LocationDetails']);exit;?>
                        <h3><?=ucwords(strtolower($car_details['LocationDetails']['PickUpLocation']['value']['Name'])) ?></h3>                        
                      </div> -->

                      <div class="pick cr_wdt">
                        <i class="fal fa-car"></i> <span>Vehicle Category:</span>
                        <?php //debug($car_details['LocationDetails']);exit;?>
                        <h3> <?=@$car_details['VehicleCategoryName'] ?></h3>                        
                      </div>

                     

                      <div class="pick fuel-plcy" data-toggle="modal" data-target="#myModal">
                        <span class="fuel_icon">Fuel Information:<?=$fuel_policy_code?></span>
                        <h3 style="padding-left: 22px !important;">
                          <a href="#" data-toggle="tooltip" title="<?=$fuel_policy_desc?>">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> 
                            <?=@$car_details['FuelType'] ?>
                          </a>
                        </h3>
                      </div>

                       <div class="pick">
                        <i class="fal fa-car"></i> <span>Vehicle Class:</span>
                        <h3 style="padding-left: 20px !important;"><?=@$car_details['VehClassSizeName'] ?></h3>
                       </div>
                     
                      <div class="clearfix"></div>
                      <div class="middleCol">
                        <ul class="features">
                          <li class="person tooltipv"><a data-original-title="Passengers" data-toggle="tooltip"><strong><?=@$car_details['PassengerQuantity'] ?></strong> <span class="mn-icon"></span> </a></li>
                          <li class="baggage tooltipv"><a data-original-title="Bags" data-toggle="tooltip"><strong><?=@$car_details['BaggageQuantity'] ?></strong> <span class="mn-icon"></span> </a></li>
                          <li class="doors tooltipv dor"><a data-original-title="Doors" data-toggle="tooltip"><strong><?=@$car_details['DoorCount'] ?></strong> <span class="mn-icon"></span> </a></li>
                          <li class="hdng" style="color: #f88c3e !important;width: auto !important;font-size: 13px; clear:both; font-weight: 600 !important;">Age Limit: <?php echo $car_details['RateRestrictions']['MinimumAge']; ?> years - <?php echo $car_details['RateRestrictions']['MaximumAge']; ?> years</li>
                        </ul>

                         <div class="pick">
                        <i class="fal fa-tachometer-alt"></i> <span>Mileage Allowance:</span>
                        <h3 style="padding-left: 20px !important;"><?=(@$car_details['PricedCoverages'][0]['Coverage']['@attributes']['CoverageType'] != '' )? 'Unlimited' : 'Limited' ; ?></h3>
                      </div>
                          <!-- <div class="age_yr">
                            <h5 class="hdng">Age:25years-80years</h5>
                          </div> -->
                        <div class="suplier_logo"><img src="<?= $car_details['TPA_Extensions']['SupplierLogo'];?>"> </div>
                       
                      </div>
                      <div class="clearfix"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
         
          </div>
        </div>
          <!-- LOGIN SECTION STARTS -->
        <?php if(is_logged_in_user() == false) { ?>
        <div class="loginspld hide">
                <div class="logininwrap">
                  <div class="signinhde">Sign in now to Book Online</div>
                  <div class="newloginsectn">
                    <div class="col-xs-5 celoty nopad">
                      <div class="insidechs">
                        <div class="mailenter">
                          <input type="text" name="booking_user_name"
                            id="booking_user_name" placeholder="Your mail id"
                            class="newslterinput nputbrd _guest_validate" maxlength="80" required="required">
                        </div>
                        <div class="noteinote">Your booking details will be sent to
                          this email address.</div>
                        <div class="clearfix"></div>
                        <div class="havealrdy">
                          <div class="squaredThree">
                            <input id="alreadyacnt" type="checkbox" name="check"
                              value="None"> <label for="alreadyacnt"></label>
                          </div>
                          <label for="alreadyacnt" class="haveacntd">I have an Account</label>
                        </div>
                        <div class="clearfix"></div>
                        <div class="twotogle">
                          <div class="cntgust">
                            <div class="phoneumber">
                              <div class="col-xs-3 nopadding">
                                <!--<input type="text" placeholder="+91" class="newslterinput nputbrd" readonly>-->
                                <!-- //FIXME: insert the country code to DB -->
                                <select class="newslterinput nputbrd _numeric_only " >
                                <?php echo diaplay_phonecode($phone_code,$active_data, $user_country_code); ?>
                              </select> 
                              </div>
                              <div class="col-xs-1 nopadding">
                                <div class="sidepo">-</div>
                              </div>
                              <div class="col-xs-8 nopadding">
                                <input type="text" id="booking_user_mobile"
                                  placeholder="Mobile Number"
                                  class="newslterinput nputbrd _numeric_only numeric _guest_validate" maxlength="12">
                              </div>
                              <div class="clearfix"></div>
                              <div class="noteinote">We'll use this number to send
                                possible update alerts.</div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="continye col-xs-8 nopad">
                              <button class="bookcont" id="continue_as_guest">Book as
                                Guest</button>
                            </div>
                          </div>
                          <div class="alrdyacnt">
                            <div class="col-xs-12 nopad">
                              <div class="relativemask">
                                <input type="password" name="booking_user_password"
                                  id="booking_user_password" class="clainput"
                                  placeholder="Password" required="required"/>
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
                              <button class="bookcont" id="continue_as_user">Proceed to
                                Book</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
            
            <div class="col-xs-2 celoty nopad linetopbtm">
                <div class="orround">OR</div>
            </div>
         
            <div class="col-xs-5 celoty nopad">
              <div class="insidechs booklogin">
                <div class="leftpul">
<?php
$social_login1 = 'facebook';
$social1 = is_active_social_login($social_login1);
if($social1){
  $GLOBALS['CI']->load->library('social_network/facebook');
  echo $GLOBALS['CI']->facebook->login_button ();?>
      <?php } 
        $social_login2 = 'twitter';
        $social2 = is_active_social_login($social_login2);
        if($social2){
        ?>
  <a class="logspecify tweetcolor"><span class="fal fa-twitter"></span><div class="mensionsoc">Login with Twitter</div></a>
      <?php } 
        $social_login3 = 'googleplus';
        $social3= is_active_social_login($social_login3);
        if($social3){
        $GLOBALS['CI']->load->library('social_network/google');
        echo $GLOBALS['CI']->google->login_button ();
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
</div>
      <!-- After Authentication Content Starts -->
    <div class="bktab2 xlbox <?=$travellers_tab_details_class?>">
     <div class="col-md-4 col-sm-4 nopadding rit_summery mt-11" id="sidebar">
        <div class="cartbukdis">
          <ul class="liscartbuk">
            <li class="lostcart hide ">            
            <div class="faresum">
             <h3>Fare Summary</h3>
             <div class="booking_div">
             <div class="col-xs-12 nopad colrcelo">
              <div class="bokkpricesml">
               <div class="travlrs col-md-6 nopad">
                <span class="portnmeter">Pick Up</span>
                <div class="fare_loc"><?=@$car_details['LocationDetails'][0]['@attributes']['Name'] ?></div>
                <div class="date_loc"><?php echo $pickup_date_val.' '.$pickup_time_val; ?></div>
               </div>
               <div class="travlrs col-md-6 nopad">
                <span class="portnmeter">Drop Off</span>
                <div class="fare_loc"><?=@$car_details['LocationDetails'][1]['@attributes']['Name'] ?></div>
                <div class="date_loc"><?php echo $return_date_val.' '.$return_time_val; ?></div>
               </div>
              
              </div>
             </div>
             <?php 
          
             if(isset($car_details['LocationDetails']) && valid_array($car_details['LocationDetails']))
             { 
              ?>
              <div class="col-xs-12 nopad colrcelo">
               <div class="bokkpricesml">
                <span class="portnmeter">Business Hours</span>
                <?php foreach($car_details['LocationDetails'] as $location){ 
                  // debug($location);exit;
                  ?>
                 <span class="business_hour col-md-6 nopad">
                  <div class="loc_name"><?php echo $location['value']['Name']; ?> </div> 
               
                    <!-- <input type="text" name="buss_time_<?php echo $t_key?>" value="<?php echo $t_value; ?>">  -->
                   <div class="loc_time"><?php echo $location['OperationSchedules']['Start']; ?> to <?php echo $location['OperationSchedules']['End']; ?></div>
                 
                 </span>
                <?php } ?>
               </div>
              </div>
              <?php } ?>
             
             <div class="fare_show">
              <!-- <h5 class="base_f">Fare Breakup <i class="fa fa-chevron-down"></i></h5> -->
              <div class="show_fares_table">
               <table class="table table-striped">
                <tbody>
                 <tr>
                  <td style="width:55%">Car Rental Price</td>
                  <td class="text-center" style="text-align: right"><?=$currency_obj->get_currency_symbol($currency_obj->to_currency)?> <span id="CarRentalPrice"><?php echo $TotalEstimateAmount; ?></span></td>
                 </tr>
                  <?php
                  // debug($car_details);exit;
                    $Coverage_amount = array();
                    if(isset($PricedCoverage) && valid_array($PricedCoverage)){
                        $in_key = 0;
                       foreach ($PricedCoverage as $p_key => $Coverage) {
                        
                       if($Coverage['Amount'] != 0){ 
                        $Coverage_amount[] = $Coverage['Amount']; 
                        if($in_key == 0){
                        ?>
                        <tr>
                          <td style="width:56%; border:none;color: #0a9ed0;;">Includes the following fees</td>
                          <td class="text-center" style="border:none;">&nbsp;</td>
                        </tr>
                        <?php } ?>
                        <tr>
                          <td style="width:65%; border:none;color: #0a9ed0;;"><?=$Coverage['CoverageType']?></td>
                          <td style="width:35%; border:none;color: #0a9ed0;text-align: right;" ><?=$currency_obj->get_currency_symbol($currency_obj->to_currency).' '.$Coverage['Amount']?></td>
                        </tr>
                       <?php $in_key++; 
                        }
                       }

                    }
                  ?>
                  <tr class="promo_code_discount hide">
                    <th>Promo Code Discount</th>
                    <td class="promo_discount_val text-center" style="text-align: right" ></td>
                  
                  </tr>
                  
                 <tr>
                  <!-- <td class="to_bo">Total Amount Due</td> -->
                  <td class="to_bo">Pay Now</td>
                  <td class="text-center" style="text-align: right;">
                   <span class="style_currency"><?=$currency_obj->get_currency_symbol($currency_obj->to_currency)?></span> <span class="grandtotal"><?php echo $total_amount; ?></span>
                  
                  </td>
                 </tr>
                </tbody>
               </table>
              </div>
             </div>
             </div>
            </div>           
           </li>
           <?php 
         
            if(isset($PricedCoverage) && valid_array($PricedCoverage) && !empty($Coverage_amount)){ 
              $class = '';
            }
            else{
               $class = 'hide';
            }
            ?>
            <li class="lostcart <?php echo $class; ?> payonpickup">
            <div class="faresum">
             <h3>Pay On Pick Up </h3>
              <div class="booking_div">
             <div class="fare_show">
              <div class="show_fares_table">
                <table class="table">
                 <tbody id="extras_holder">
                <?php 
                  if(isset($PricedCoverage) && valid_array($PricedCoverage) && !empty($Coverage_amount)){ 
                ?>
                 
                   <?php foreach ($PricedCoverage as $p_key => $Coverage) {
                    if($Coverage['Amount'] != 0){
                    ?>
                    <tr>
                    <td style="width:65%"><?=$Coverage['CoverageType']?></td>
                    <td style="width:35%;text-align:right" class="text-center" ><?=$currency_obj->get_currency_symbol($currency_obj->to_currency).' '.$Coverage['Amount']?>
                    </td>
                    </tr>
                    <?php } } ?>
                 
                  <?php } ?>
                   </tbody>
                  <tbody id="extras_holder_0"></tbody>
                  <tbody id="extras_holder_1"></tbody>
                  <tbody id="extras_holder_2"></tbody>
                </table>
                </div>
             </div>
            </div>
            </div>
           </li>        
           <input type="hidden" id="pax_allowed" value="<?=$car_details['PassengerQuantity'] -2;?>">
          </ul>

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
      </div>
    
    <div class="col-md-8 col-sm-8 col-xs-12 nopad ">
      <div class="col-xs-12 padpaspotr">
        <div class="col-xs-12 nopadding">
          <div class="fligthsdets">
          <form action="<?=base_url().'index.php/car/pre_booking/'.$page_data['search_id']?>" method="POST" autocomplete="off" id="pre-booking-form">
          <input type="hidden" required="required" name="search_id"   value="<?=$page_data['search_id'];?>" />
          <?php $dynamic_params_url = serialized_data($page_data['raw_car_rate_result']);?>
          <input type="hidden" required="required" name="token"   value="<?=$dynamic_params_url;?>" />
          <input type="hidden" required="required" name="token_key" value="<?=md5($dynamic_params_url);?>" />
          <input type="hidden" required="required" name="op"      value="book_room">
          <input type="hidden" required="required" name="booking_source"    value="<?=$active_booking_source;?>" readonly>
          <input type="hidden" required="required" name="promo_code_discount_val" id="promo_code_discount_val" value="0.00" readonly>
          <input type="hidden" required="required" name="promo_code" id="promocode_val" value="" readonly>


          <div class="flitab1">
          <div class="moreflt boksectn">
          <div class="ontyp">
          <div class="labltowr arimobold">Please enter names as on passport. </div>
          <div class="adult_in">
            <div class="ad_lbl">
              <strong><img src="<?=$GLOBALS['CI']->template->template_images('adult1.svg')?>" alt="img/svg" /> Adult</strong><!--<span>0/3  added</span>-->
            </div>
            <div class="pasngr_input pasngrinput_secnrews pasngrinput _passenger_hiiden_inputs">
                    <div class="col-xs-12 nopadding">   
                        <div class="adltnom"><i class="fas fa-check-square"></i> Adult 1</div> 
                    </div>
                    <div class="col-xs-12 nopadding full_dets_aps">
                    <div class="inptalbox">
                    <div class="btn-group<?php echo $pax_index;?>" data-toggle="buttons">
                        <label class="btn btn-primary active">
                            <input type="radio" name="gender<?php echo $pax_index;?>" checked="checked" value="1"> MALE
                        </label>
                        <label class="btn btn-primary">
                            <input type="radio" name="gender<?php echo $pax_index;?>" value="2"> FEMALE
                        </label>
                        <label class="btn btn-primary">
                            <input type="radio" name="gender<?php echo $pax_index;?>" value="3"> Non-binary
                        </label>
                        <label class="btn btn-primary">
                            <input type="radio" name="gender<?php echo $pax_index;?>" value="4"> Opt Out 
                        </label>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-xs-4 pad_psger">
                     <span class="formlabel">Title</span>
                      <div class="selectedwrap">
                        <select class="name_title flyinputsnor " required name="pax_title">
                        <?=$pax_title_options?>
                        </select>
                      </div>
                    </div>
                    <div class="col-xs-4 pad_psger">
                    <span class="formlabel">First Name <sup class="text-danger">*</sup></span>
                        <input value="<?=@$pax_details[0]['first_name']; ?>" type="text" maxlength="45" id="first_name"  name="first_name" class="clainput  alpha_space" required placeholder="First Name">
                    </div>
                    <div class="col-xs-4 pad_psger hide">
                        <span class="formlabel">Middle Name(Optional)</span>
                        <input type="text" name="middle_name[]" id="middle_name" class="clainput alpha_space" minlength="2" maxlength="45" placeholder="Middle Name" value="" >
                    </div>
                    <div class="col-xs-4 pad_psger">
                     <span class="formlabel">Last Name <sup class="text-danger">*</sup></span>
                        <input value="<?=@$pax_details[0]['last_name']; ?>" type="text" maxlength="45" id="last_name"  name="last_name" class="clainput  alpha_space" required placeholder="Last Name">
                    </div>
                    <div class="col-xs-4 pad_psger">
                        <span class="formlabel">Country <sup class="text-danger">*</sup></span>
                        <div class="selectedwrap">
                          <select name="country" id="country" class="mySelectBoxClass flyinputsnor">
                            <option value="INVALIDIP">Please Select</option>
                            <?=$country_list?>
                          </select>
                        </div>             
                    </div>
                    <div class="col-xs-4 pad_psger">
                      <span class="formlabel">City <sup class="text-danger">*</sup></span>
                        <input value="" type="text" maxlength="45" id="city_name"  name="city_name" class="clainput  alpha_space" required placeholder="City Name">
                    </div>
                     <div class="col-xs-4 pad_psger">
                     <span class="formlabel">State <sup class="text-danger">*</sup></span>
                        <input value="" type="text" maxlength="45" id="state_name"  name="state_name" class="clainput  alpha_space" required placeholder="State Name">
                    </div>
                    <div class="col-xs-4 pad_psger">
                     <span class="formlabel">Postal Code <sup class="text-danger">*</sup></span>
                        <input value="" type="text" maxlength="45" id="postal_code"  name="postal_code" class="newslterinput nputbrd _numeric_only" required placeholder="Postal Code">
                    </div>
                     <div class="col-xs-4 pad_psger">
                     <span class="formlabel">Address <sup class="text-danger">*</sup></span>
                        <input value="<?=@$pax_details[0]['address']?>" type="text" maxlength="45" id="address"  name="address" class="clainput  alpha_space" required placeholder="Address">
                    </div>
                    <div class="col-xs-4 pad_psger">
                        <span class="formlabel">Date of Birth <sup class="text-danger">*</sup></span>
                       
                          <input placeholder="DOB" type="text" class="clainput"  name="date_of_birth" id="date-picker-dobs" value ="<?=@$pax_details[0]['date_of_birth']?>" readonly required="" />
                    </div>
                </div>
          </div>
          </div>
          </div>
          </div>
          </div>
          <div class="clearfix"></div>
          <div class="contbk m-mb-15">
          <div class="contcthdngs">Contact Information</div>
          <div class="hide">
          <input type="hidden" name="billing_country" value="92">
          <input type="hidden" name="billing_city" value="test">
          <input type="hidden" name="billing_zipcode" value="test">
          <input type="hidden" name="billing_address_1" value="test">
          </div>
          <div class="clearfix"></div>
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
          <?php if (false) { ?>
          <div class="contbk">
          <div class="contcthdngs">Contact Information</div>
                    <div class="col-xs-12 nopad full_smal_forty">
                    <div class="col-xs-6 nopad full_smal_forty">
                      <div class="col-xs-3 nopadding">
                        <div class="hide">
                          <input type="hidden" name="billing_country" value="92">
                          <input type="hidden" name="billing_city" value="test">
                          <input type="hidden" name="billing_zipcode" value="test">
                          <input type="hidden" name="billing_address_1" value="test">

                        </div>
                        <select name="country_code" class="newslterinput nputbrd _numeric_only" id="after_country_code" required>
                      <?php echo diaplay_phonecode($phone_code,$active_data, $user_country_code); ?>
                    </select> 
                      </div>
                      <div class="col-xs-2">
                        <div class="sidepo">-</div>
                      </div>
                      <div class="col-xs-7 nopadding">
                        <input value="<?=@$pax_details[0]['phone'] == 0 ? '' : @$pax_details[0]['phone'];?>" type="text" name="passenger_contact" id="passenger-contact" placeholder="Mobile Number" class="newslterinput nputbrd _numeric_only" maxlength="12" required="required">
                      </div>
                                             </div>

                      
                      <div class="emailperson col-xs-6 nopad full_smal_forty">
                        <input value="<?=@$pax_details[0]['email']?>" type="text" maxlength="80" required="required" id="billing-email" class="newslterinput nputbrd" placeholder="Email" name="billing_email">
                      </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="notese">Your mobile number will be used only for sending flight related communication.</div>
                  </div>
                <?php } ?>
        
          <div class="clearfix"></div>
          <div class="contbk">          
            <div class="contcthdngs">Extras</div> 
          <?php //debug($car_details['PricedEquip']);exit;
          if(isset($car_details['PricedEquip']) and valid_array($car_details['PricedEquip'])){
          ?>
          
          <?php foreach($car_details['PricedEquip'] as $key => $priced_equip){
            // debug($car_details['PricedEquip']);exit;
            $equip_array = array('7','8','9');
            if(!in_array($priced_equip['EquipType'], $equip_array)){
              if($priced_equip['EquipType'] == 13){
                $image = 'gps_icon.png';
                $name = 'gps';
              }
              if($priced_equip['EquipType'] == 222){
                $image = 'driver_icon.png';
                $name = 'add_driver';
              }
              if($priced_equip['EquipType'] == 413){
                $image = 'driver_icon.png';
                $name = 'full_prot';
              }
              $image = $template_images.'/'.$image;
            ?>
           <div class="pre_summery1">            
             <div class="toppade">
              <div class="wrp_pre">
                <div class="squaredThree">
                  <input type="checkbox" value="<?=$priced_equip['EquipType']?>" name="<?=$name?>" class="filter_airline_<?=$key?>" id="<?=$key?>" aria-required="true" onchange="set_extras(this,'<?=$priced_equip['Description']?>','<?=$priced_equip['Amount']?>','tr_<?=rand(999,9999)?>','checkbox');">
                  <label for="<?=$key?>" class="add_extras"></label>
                 </div>
                 <label for ="<?=$key?>" class="add_extras">
                <img src="<?=$image?>" alt=""> 
                <?=$priced_equip['Description']?> <?=$currency_obj->get_currency_symbol($currency_obj->to_currency) ?> <?=$priced_equip['Amount'] ?></label>
              </div>
             </div>
             </div>
          <?php }
          if(in_array($priced_equip['EquipType'], $equip_array)){
             $quantity = $car_details['PassengerQuantity'] -2;
             if($priced_equip['EquipType'] == 7){
                $extras_holder = 'Infant_equip_type_0';
                $id = 'Infant';
             }
             else if($priced_equip['EquipType'] == 8){
                $extras_holder = 'Child_equip_type_1';
                $id = 'Child';
             }
             else{
                $extras_holder = 'Booster_equip_type_2';
                $id = 'Booster';
             }
            ?>
           <div class="pre_summery1">            
             <div class="toppade">
              <div class="wrp_pre">
                <span>  
                 <select name="<?=$id?>" id="<?=$id?>s" class="flpayinput" onchange="set_extras(this.value,'<?=$priced_equip['Description']?>','<?=$priced_equip['Amount']?>','tr_<?=rand(999,9999)?>','select','<?=$extras_holder?>')">
                    <?php for($i=0; $i<=$quantity; $i++){ ?>
                    <option value="<?=$i?>"><?=$i?></option>
                    <?php }?>
                   </select>
                  <label for="<?=$key?>" class="add_extras"></label>
                 </span>
                <label for ="<?=$key?>" class="add_extras">
                <?=$priced_equip['Description']?> <?=$currency_obj->get_currency_symbol($currency_obj->to_currency) ?> <?=$priced_equip['Amount'] ?> Per One <?=$id ?></label>
                <input type="hidden" id="<?=$id?>" value="0">
              </div>
             </div>
             </div>
          <?php }
          } ?>
          
          <?php } else{?>
           <div class="pre_summery1">
             <div class="toppade">                      
              <div class="wrp_pre">
               <label for="additional_driver" class="add_extras">There is No Extras</label>
              </div>
             </div>
             <div class="clearfix"></div>
            </div>
            <?php } ?>
            <div class="pre_summery1">            
              <span class="noteclick"> Charges for extras are payable at pickup </span>
           </div>
          </div>
          <div class="clikdiv">
          <div class="squaredThree">
          <input id="terms_cond1" type="checkbox" name="tc" checked="checked" required="required">
          <label for="terms_cond1"></label>
          </div>
          <span class="clikagre" id="clikagre">
            <a href="<?php echo base_url();?>index.php/terms-conditions" target="_blank">Terms and Conditions</a>
           
          </span>
        </div>
        <?php
            //If single payment option then hide selection and select by default
            if (count($page_data['active_payment_options']) == 1) {
              $payment_option_visibility = 'hide';
              $default_payment_option = 'checked="checked"';
            } else {
              $payment_option_visibility = 'show';
              $default_payment_option = '';
            }
            
            ?>
        <div class="row <?=$payment_option_visibility?>">
              <?php if (in_array(PAY_NOW, $page_data['active_payment_options'])) {?>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="payment-mode-<?=PAY_NOW?>">
                      <input <?=$default_payment_option?> name="payment_method" type="radio" required="required" value="<?=PAY_NOW?>" id="payment-mode-<?=PAY_NOW?>" class="form-control b-r-0" placeholder="Payment Mode">
                      Pay Now
                    </label>
                  </div>
                </div>
              <?php } ?>
              <?php if (in_array(PAY_AT_BANK, $page_data['active_payment_options'])) {?>
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
         <div class="payrowsubmt">
         <div class="continye col-sm-4 col-xs-6 nopad">
                        <button type="submit" id="flip" class="bookcont" name="car" class="">Continue</button>
                      </div>
          <div class="col-md-8 col-xs-4 fulat500 nopad"> </div>
          <div class="clear"></div>
          <div class="lastnote"> </div>
         </div>
        
       </form>
      </div> 
      </div>     
     </div>
    </div>
   </div>
 </div>
</div>
</div>
 <?php echo $GLOBALS['CI']->template->isolated_view('share/passenger_confirm_popup');?>  

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
<script type="text/javascript">
    $("#back_btn").click(function (){
      window.history.back();
      return false;
    });
function set_extras(status,title,price,row_id,tag_type,extra_select_box_id="not") {
  $('.payonpickup').removeClass('hide');
  
  if(tag_type == 'select')
  {
    status = status;
  }else if(tag_type == 'checkbox')
  {
    if(status.checked)
    {
      status = 1;
    }else
    {
      status = 0;
    }
  }
  if(status != 0){

      if(tag_type == 'select'){
        var extra_id = extra_select_box_id.split("_");
        price = price*status;
        var pax_allowed = $('#pax_allowed').val(); 
        $('#'+extra_id[0]).val(status);
        var total_pax = +$('#Infant').val() + +$('#Child').val() + +$('#Booster').val();
        
        var html = '<tr class="'+row_id+'"><td style="width:70%">'+title+'</td><td class="text-center " style="text-align: right"><?=$CurrencyCode?> '+price+'</td></tr>';
          if(total_pax > pax_allowed){
            $('#'+extra_id[0]).val(0);
            alert('Exceeds the Passenger Count');
          }
          else{
              $('#extras_holder_'+extra_id[3]).html(html);
              $('#extrass_holder_'+extra_id[3]).html(html);
          }
      }
      else{
        var html = '<tr class="'+row_id+'"><td style="width:70%">'+title+'</td><td class="text-center " style="text-align: right"><?=$CurrencyCode?> '+price+'</td></tr>';
        $('#extras_holder').append(html);
        $('#extras_holders').append(html);
      }
  }
  else{
    if(tag_type == 'select'){
      var extra_id = extra_select_box_id.split("_");
      $('#'+extra_id[0]).val(status);
    }
    $('.'+row_id).remove();
    $('.'+extra_select_box_id).remove();
  }
  var tbody = $("#extras_holder");
// alert(tbody.children().length);
  if (tbody.children().length == 0) {
     $('.payonpickup').addClass('hide');
  }
}

</script>