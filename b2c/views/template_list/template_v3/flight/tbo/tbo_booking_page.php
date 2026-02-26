<?php
declare(strict_types=1);
//debug($search_data);exit;
include_once 'process_tbo_response.php';
$template_images = $GLOBALS['CI']->template->template_images();
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('provablib.js'), 'defer' => 'defer');
//Flight Booking Summary

$FareDetails = $pre_booking_summery['FareDetails']['b2c_PriceDetails'];
$flight_total_price = round(($FareDetails['TotalFare'] + $convenience_fees), 2);
$ApiPriceDetails = $pre_booking_summery['api_original_price_details'];
$api_markup_details = $pre_booking_summery['FareDetails']['api_PriceDetails'];
$FareBreakdown = $pre_booking_summery['PassengerFareBreakdown'];
$SegmentDetails = $pre_booking_summery['SegmentDetails'];
$SegmentSummary = $pre_booking_summery['SegmentSummary'];
/* * ******************************* Convenience Fees ******************************** */
$flight_total_amount = $FareDetails['TotalFare'] + $convenience_fees;
$flight_amount = $FareDetails['TotalFare'];
/* * ******************************* Convenience Fees ******************************** */
$currency_symbol = $FareDetails['CurrencySymbol'];
$currency = $FareDetails['Currency'];

$is_domestic = $pre_booking_params['is_domestic'];
//Segment Summary and Details
$flight_segment_details = flight_segment_details($SegmentDetails, $SegmentSummary, $search_data);
if ($is_domestic != true) {
    $pass_mand = '<sup class="text-danger">*</sup>';
    $pass_req = 'required="required"';
} else {
    $pass_mand = '';
    $pass_req = '';
}
$mandatory_filed_marker = '<sup class="text-danger">*</sup>';
//Balu A
$is_domestic_flight = $search_data['is_domestic_flight'];
if ($is_domestic_flight) {
    $temp_passport_expiry_date = date('Y-m-d', strtotime('+5 years'));
    $static_passport_details = array();
    $static_passport_details['passenger_passport_expiry_day'] = date('d', strtotime($temp_passport_expiry_date));
    $static_passport_details['passenger_passport_expiry_month'] = date('m', strtotime($temp_passport_expiry_date));
    $static_passport_details['passenger_passport_expiry_year'] = date('Y', strtotime($temp_passport_expiry_date));
}

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

$book_login_auth_loading_image = '<div class="text-center loader-image"><img src="' . $GLOBALS['CI']->template->template_images('loader_v3.gif') . '" alt="please wait"/></div>';

?>
<style>
    .topssec::after{display:none;}
</style>
<div class="fldealsec">
    <div class="container">
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
<div class="alldownsectn air_bk">
    <div class="container">
<?php if ($is_price_Changed == true) { ?>
            <div class="farehd arimobold">
                <span class="text-danger">* Price has been changed from supplier end</span>
            </div>
        <?php } ?>
        <div class="ovrgo">
        <div class="srch_ldr hide">
                    <img src="<?=$GLOBALS['CI']->template->template_images('image_loader.gif')?>" />
                    </div>
            <div class="bktab1 xlbox <?= $review_tab_details_class ?> hide">
                <!-- Balu A - Fare Summery -->

                <div class="col-xs-8 nopadding full_summery_tab">
                    <div class="fligthsdets">
                        <div class="flitab1">
                            <!-- Segment Details Starts-->
                            <div class="moreflt boksectn">
<?= $flight_segment_details['segment_full_details']; ?>
                            </div>
                            <!-- Segment Details Ends-->
                            <div class="clearfix"></div>
                            <div class="sepertr"></div>
                           
                           
                            <!-- LOGIN SECTION STARTS -->
<?php if (is_logged_in_user() == false) { ?>
                                <div class="loginspld">
                                    <div class="logininwrap">
                                        <div class="signinhde">
                                            Sign in now to Book Online
                                        </div>
                                        <div class="newloginsectn">
                                            <div class="col-xs-5 celoty nopad">
                                                <div class="insidechs">
                                                    <div class="mailenter">
                                                        <input type="text" name="booking_user_name" id="booking_user_name" maxlength="80"  placeholder="Your mail id" class="newslterinput nputbrd _guest_validate">
                                                        <!-- <span class="err_msg">this field is required</span> -->
                                                    </div>
                                                    <div class="noteinote">Your booking details will be sent to this email address.</div>
                                                    <div class="clearfix"></div>
                                                    <div class="havealrdy">
                                                        <div class="squaredThree">
                                                            <input id="alreadyacnt" type="checkbox" name="check" value="None">
                                                            <label for="alreadyacnt"></label>
                                                        </div>
                                                        <label for="alreadyacnt" class="haveacntd">I have a Travelomatix password</label>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                    <div class="twotogle">
                                                        <div class="cntgust">
                                                            <div class="phoneumber">
                                                                <div class="col-xs-5 nopadding">
                                                                    <select class="newslterinput nputbrd _numeric_only" id="before_country_code" >
    <?php echo diaplay_phonecode($phone_code, $active_data, $user_country_code); ?>
                                                                    </select> 
                                                                </div>
                                                                <div class="col-xs-1 nopadding">
                                                                    <div class="sidepo">-</div>
                                                                </div>
                                                                <div class="col-xs-6 nopadding">
                                                                    <input type="text" id="booking_user_mobile" placeholder="Mobile Number" class="newslterinput _numeric_only _guest_validate" maxlength="10">
                                                                </div>
                                                                <div class="clearfix"></div>
                                                                <div class="noteinote">We'll use this number to send possible update alerts.</div>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                            <div class="continye col-xs-8 nopad">
                                                                <button class="bookcont" id="continue_as_guest">Book as guest</button>
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
    <?= $book_login_auth_loading_image ?>
                                                            </div>

                                                            <div class="clearfix"></div>
                                                            <div class="continye col-xs-8 nopad">
                                                                <button class="bookcont" id="continue_as_user">Proceed to Book</button>
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
    if ($social1) {
        $GLOBALS['CI']->load->library('social_network/facebook');
        echo $GLOBALS['CI']->facebook->login_button();
    }
    $social_login2 = 'twitter';
    $social2 = is_active_social_login($social_login2);
    if ($social2) {
        ?>
                                                            <a class="logspecify tweetcolor">
                                                                <span class="fa fa-twitter"></span>
                                                                <div class="mensionsoc">Login with Twitter</div>
                                                            </a>
        <?php
    }
    $social_login3 = 'googleplus';
    $social3 = is_active_social_login($social_login3);
    if ($social3) {
        $GLOBALS['CI']->load->library('social_network/google');
        echo $GLOBALS['CI']->google->login_button();
    }
    ?>
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

                <div class="col-xs-4 nopadding rit_summery test1">
                    <div class="copy_fare_summery">
                         <?php if (is_logged_in_user() == false) { ?>
<?php echo get_fare_summary($FareDetails, $FareBreakdown, $convenience_fees, $org_convience_fee, $ApiPriceDetails, $api_markup_details, $convenience_fees_orginal, $convenience_fees_text); ?>
                        <?php } ?>
						
                        </div>
                </div>
				

            </div>
            <div class="bktab2 xlbox <?= $travellers_tab_details_class ?> flight_booking_desc">
                      <button id="back_btn" class="btn waves-effect waves-light"  name="action"><i class="far fa-chevron-left"></i> Back</button>
                      <h3>Review your Booking</h3>
                      <div class="clearfix"></div>
                <div class="topalldesc hide">
                    <div class="col-xs-8 nopadding celtbcel segment_seg">
<?= $flight_segment_details['segment_abstract_details']; ?>
                    </div>

                    <!-- Outer Summary -->
                    <div class="col-xs-4 nopadding celtbcel colrcelo">
                        <div class="bokkpricesml">
                            <div class="travlrs">Travellers: <span class="fa fa-male"></span> <?php echo $search_data['adult']; ?> |  <span class="fa fa-child"></span> <?php echo $search_data['child']; ?> |  <span class="infantbay"><img src="<?= $template_images ?>infant.png" alt="" /></span> <?php echo $search_data['infant']; ?></div>
                            
                            <div class="totlbkamnt"> Total Amount <span class="total_amount_span"><?php echo $currency_symbol; ?> </span> <span id="total_booking_amount"><?php echo round($flight_total_amount); ?></span></div>
                             <input type="hidden" value="<?=$flight_total_amount?>" id="new_total_booking_amount" >
                             <input type="hidden" value="<?=$flight_amount?>" id="flight_amount" >
                            <a class="fligthdets" data-toggle="collapse" data-target="#fligtdetails">Flight Details</a>
                        </div>
                    </div>
                </div>              
                
                <div class="clearfix"></div>
                <div class="padpaspotr">
                    
                    <div class="col-xs-4 nopadding rit_summery test">
<?php echo get_fare_summary($FareDetails, $FareBreakdown, $convenience_fees, $org_convience_fee, $ApiPriceDetails, $api_markup_details, $convenience_fees_orginal, $convenience_fees_text); ?>
                                    <div class="col-xs-12 ad_info">

                                                    <form name="promocode" id="promocode" novalidate>
                                                       <div class="col-md-12 col-xs-12 nopadding_right pr_code nopad">
                                                            <div class="cartprc">
                                                                <div class="payblnhm singecartpricebuk ritaln">
                                                                    <input type="text" placeholder="Enter Promo Code" name="code" id="code" class="promocode" aria-required="true" />
                                                                    <input type="hidden" name="module_type" id="module_type" class="promocode" value="<?= @$module_value; ?>" />
                                                                    <input type="hidden" name="total_amount_val" id="total_amount_val" class="promocode" value="<?= @$FareDetails['TotalFare']; ?>" />
                                                                    <input type="hidden" name="convenience_fee" id="convenience_fee" class="promocode" value="<?= @$convenience_fees; ?>" />
                                                                    <input type="hidden" name="currency_symbol" id="currency_symbol" value="<?= @$currency_symbol; ?>" />
                                                                    <input type="hidden" name="currency" id="currency" value="<?= @$currency; ?>" />

                                                                    <p class="error_promocode text-danger" style="font-weight:bold"></p>                                        
                                                                </div>
                                                            </div>
                                                         <input type="hidden" value="<?php echo $flight_total_price;?>" id="actual_grand_total" />  
                                                       
                                                            <input type="button" value="Apply" name="apply" id="apply" class="promosubmit">
                                                        </div>
                                                      
                                                    </form>
                                                </div>
                    </div>
                    <div class="col-md-8 col-xs-12 nopadding tab_pasnger">
                                        <!-- Segment Details Starts-->
                <div class="collapse splbukdets in" id="fligtdetails">
                    <div class="moreflt insideagain">
<?= $flight_segment_details['segment_full_details']; ?>
                    </div>
                </div>
                <div class="clearfix"></div>
                <!-- Segment Details Ends-->
                      
                        <div class="fligthsdets">
<?php
$module_value = md5('flight');
/**
 * Collection field name 
 */
//Title, Firstname, Middlename, Lastname, Phoneno, Email, PaxType, LeadPassenger, Age, PassportNo, PassportIssueDate, PassportExpDate
$total_adult_count = is_array($search_data['adult_config']) ? array_sum($search_data['adult_config']) : intval($search_data['adult_config']);
$total_child_count = is_array($search_data['child_config']) ? array_sum($search_data['child_config']) : intval($search_data['child_config']);
$total_infant_count = is_array($search_data['infant_config']) ? array_sum($search_data['infant_config']) : intval($search_data['infant_config']);
//------------------------------ DATEPICKER START
$i = 1;
$datepicker_list = array();
if ($total_adult_count > 0) {
    for ($i = 1; $i <= $total_adult_count; $i++) {
        $datepicker_list[] = array('adult-date-picker-' . $i, ADULT_DATE_PICKER);
    }
}

if ($total_child_count > 0) {
    //id should be auto picked so initialize $i to previous value of $i
    for ($i = $i; $i <= ($total_child_count + $total_adult_count); $i++) {
        $datepicker_list[] = array('child-date-picker-' . $i, CHILD_DATE_PICKER);
    }
}
if ($total_infant_count > 0) {
    //id should be auto picked so initialize $i to previous value of $i
    for ($i = $i; $i <= ($total_child_count + $total_adult_count + $total_infant_count); $i++) {
        $datepicker_list[] = array('infant-date-picker-' . $i, INFANT_DATE_PICKER);
    }
}
$GLOBALS['CI']->current_page->set_datepicker($datepicker_list);
//------------------------------ DATEPICKER END
$total_pax_count = $total_adult_count + $total_child_count + $total_infant_count;
//First Adult is Primary and and Lead Pax
$adult_enum = $child_enum = get_enum_list('title');
$gender_enum = get_enum_list('gender');
unset($adult_enum[MASTER_TITLE]); // Master is for child so not required
unset($child_enum[MASTER_TITLE]); // Master is not supported in TBO list
$adult_title_options = generate_options($adult_enum, false, true);
$child_title_options = generate_options($child_enum, false, true);
$gender_options = generate_options($gender_enum);
$nationality_options = generate_options($iso_country_list, array(INDIA_CODE)); //FIXME get ISO CODE --- ISO_INDIA
$passport_issuing_country_options = generate_options($country_list);
if ($search_data['trip_type'] == 'oneway') {
    $passport_minimum_expiry_date = date('Y-m-d', strtotime($search_data['depature']));
} else if ($search_data['trip_type'] == 'circle') {
    $passport_minimum_expiry_date = date('Y-m-d', strtotime($search_data['return']));
} else {
    $passport_minimum_expiry_date = date('Y-m-d', strtotime(end($search_data['depature'])));
}

//lowest year wanted
$cutoff = date('Y', strtotime('+20 years', strtotime($passport_minimum_expiry_date)));
// echo $cutoff;exit;
//current year
$now = date('Y', strtotime($passport_minimum_expiry_date));
$now_day = date('d', strtotime($passport_minimum_expiry_date));
$now_month = date('m', strtotime($passport_minimum_expiry_date));
$day_options = generate_options(get_day_numbers());
$month_options = generate_options(get_month_names());
$year_options = generate_options(get_years($now, $cutoff));

/**
 * check if current print index is of adult or child by taking adult and total pax count
 * @param number $total_pax		total pax count
 * @param number $total_adult	total adult count
 */
function pax_type(int $pax_index, int $total_adult, int $total_child): string
{
    if ($pax_index <= $total_adult) {
        return 'adult';
    }

    if ($pax_index <= ($total_adult + $total_child)) {
        return 'child';
    }

    return 'infant';
}


/**
 * check if current print index is of adult or child by taking adult and total pax count
 * @param number $total_pax		total pax count
 * @param number $total_adult	total adult count
 */
function is_adult(int $pax_index, int $total_adult): bool
{
    return $pax_index <= $total_adult;
}

function pax_type_count(int $pax_index, int $total_adult, int $total_child): int
{
    if ($pax_index <= $total_adult) {
        return $pax_index;
    }

    if ($pax_index <= ($total_adult + $total_child)) {
        return $pax_index - $total_adult;
    }

    return $pax_index - ($total_adult + $total_child);
}


/**
 * check if current print index is of adult or child by taking adult and total pax count
 * @param number $total_pax		total pax count
 * @param number $total_adult	total adult count
 */
function is_lead_pax(int $pax_count) {
    return ($pax_count == 1 ? true : false);
}

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
                            <form action="<?= base_url() . 'index.php/flight/pre_booking/' . $search_data['search_id'] ?>" method="POST" autocomplete="off" id="pre-booking-form">
                                <div class="hide">
                                    <input type="hidden" required="required" name="search_id"		value="<?= $search_data['search_id']; ?>" />
                            <?php $dynamic_params_url = serialized_data($pre_booking_params); ?>
                                    <input type="hidden" required="required" name="token"		value="<?= $dynamic_params_url; ?>" />
                                    <input type="hidden" required="required" name="token_key"	value="<?= md5($dynamic_params_url); ?>" />
                                    <input type="hidden" required="required" name="op"			value="book_room">
                                    <input type="hidden" required="required" name="booking_source"		value="<?= $booking_source ?>" readonly>
                                    <input type="hidden" required="required" name="promo_code_discount_val" id="promo_code_discount_val" value="0.00" readonly>
                                    <input type="hidden" required="required" name="promo_code" id="promocode_val" value="" readonly>
                                    <!--<input type="hidden" required="required" name="provab_auth_key" value="?=$ProvabAuthKey ?>" readonly>
                                    --></div>
                                <div class="clearfix"></div>
                               
                                <div class="flitab1">
                                    <div class="moreflt boksectn">
                                        <div class="ontyp nopad">
                                            <div class="labltowr arimobold">Please enter names as on passport. </div>
                                                <div class="adult_in">
                                                    <div class="ad_lbl">
                                                        <strong><img src="<?=$GLOBALS['CI']->template->template_images('adult1.svg')?>" alt="img/svg" /> Adult</strong>
                                                        <!--<span>0/3  added</span>-->
                                                    </div>
<?php
$pax_index = 1;
$lead_pax_details = @$pax_details[0];
if (is_logged_in_user()) {
    $traveller_class = ' user_traveller_details ';
} else {
    $traveller_class = '';
}
for ($pax_index = 1; $pax_index <= $total_pax_count; $pax_index++) {//START FOR LOOP FOR PAX DETAILS
    $cur_pax_info = is_array($pax_details) ? array_shift($pax_details) : array();
    $pax_type = pax_type($pax_index, $total_adult_count, $total_child_count);
    $pax_type_count = pax_type_count($pax_index, $total_adult_count, $total_child_count);

    if ($pax_type != 'infant') {
        $extract_pax_name_cls = ' extract_pax_name_cls ';
    } else {
        $extract_pax_name_cls = '';
    }
    ?>
                                                <div class="pasngr_input pasngrinput _passenger_hiiden_inputs">
                                                    <div class="hide hidden_pax_details">
                                                        <input type="hidden" name="passenger_type[]" value="<?= ucfirst($pax_type) ?>">
                                                        <input type="hidden" name="lead_passenger[]" value="<?= (is_lead_pax($pax_index) ? true : false) ?>">
                                                        
                                                        <input type="hidden" required="required" name="passenger_nationality[]" id="passenger-nationality-<?= $pax_index ?>" value="92">
                                                    </div>
                                                    <div class="col-xs-12 nopadding">   
                                                        <div class="adltnom"><i class="fas fa-check-square"></i> <?= ucfirst($pax_type) ?> <?= $pax_type_count ?> <?= $mandatory_filed_marker ?></div> 
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

                                                            <div class="col-xs-3 spllty">
                                                                <label class="cst_lbl">Title</label>
                                                                <div class="selectedwrap">
                                                                    <select class="mySelectBoxClass flyinputsnor name_title" name="name_title[]" required>
    <?php echo (is_adult($pax_index, $total_adult_count) ? $adult_title_options : $child_title_options) ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-xs-3 spllty">
                                                                <label class="cst_lbl">First Name <sup class="text-danger">*</sup></label>
                                                                <input value="<?= @$cur_pax_info['first_name'] ?>" required="required" type="text" name="first_name[]" id="passenger-first-name-<?= $pax_index ?>" class="<?= $extract_pax_name_cls ?> clainput alpha_space <?= $traveller_class ?>" maxlength="45" placeholder="Enter First Name" data-row-id="<?= ($pax_index); ?>"/>
                                                            </div>
                                                            <div class="col-xs-3 spllty">
                                                                <label class="cst_lbl">Last Name <sup class="text-danger">*</sup></label>
                                                                <input value="<?= @$cur_pax_info['last_name'] ?>" required="required" type="text" name="last_name[]" id="passenger-last-name-<?= $pax_index ?>" class="<?= $extract_pax_name_cls ?> clainput alpha_space" maxlength="45" placeholder="Enter Last Name" />
                                                            </div>
    <?php if ($pax_type == 'infant') {//Only For Infant  ?>
                                                                <div class="col-xs-3 spllty infant_dob_div">
                                                                    <!-- <div class="col-xs-4 nopadding"><span class="fmlbl">Date of Birth <?= $mandatory_filed_marker ?></span></div>
                                                                    <div class="col-xs-8 nopadding"> -->
                                                                        <label class="cst_lbl">DOB <sup class="text-danger">*</sup></label>
                                                                        <input placeholder="Enter DOB" type="text" class="clainput"  name="date_of_birth[]" readonly <?= (is_adult($pax_index, $total_adult_count) ? 'required="required"' : 'required="required"') ?> id="<?= strtolower(pax_type($pax_index, $total_adult_count, $total_child_count)) ?>-date-picker-<?= $pax_index ?>">
                                                                    <!-- </div> -->
                                                                </div>
        <?php
    } else { //Adult/Child
        if (($pax_type == 'adult' && $is_domestic_flight == false)) {
            ?> 
                                                                    <div class="col-xs-3 spllty infant_dob_div">
                                                                        <!-- <div class="col-xs-4 nopadding"><span class="fmlbl">Date of Birth <?= $mandatory_filed_marker ?></span></div>
                                                                        <div class="col-xs-8 nopadding"> -->
                                                                            <label class="cst_lbl">DOB <sup class="text-danger">*</sup></label>
                                                                            <input placeholder="Enter DOB" type="text" class="clainput"  name="date_of_birth[]" readonly <?= (is_adult($pax_index, $total_adult_count) ? 'required="required"' : 'required="required"') ?> id="<?= strtolower(pax_type($pax_index, $total_adult_count, $total_child_count)) ?>-date-picker-<?= $pax_index ?>">
                                                                        <!-- </div> -->
                                                                    </div>
        <?php } else if (($pax_type == 'child')) { ?>
                                                                    <div class="col-xs-3 spllty infant_dob_div">
                                                                        <!-- <div class="col-xs-4 nopadding"><span class="fmlbl">Date of Birth <?= $mandatory_filed_marker ?></span></div>
                                                                        <div class="col-xs-8 nopadding"> -->
                                                                            <label class="cst_lbl">DOB <sup class="text-danger">*</sup></label>
                                                                            <input placeholder="Enter DOB" type="text" class="clainput"  name="date_of_birth[]" readonly <?= (is_adult($pax_index, $total_adult_count) ? 'required="required"' : 'required="required"') ?> id="<?= strtolower(pax_type($pax_index, $total_adult_count, $total_child_count, $total_infant_count)) ?>-date-picker-<?= $pax_index ?>">
                                                                        <!-- </div> -->
                                                                    </div>
        <?php }
    } ?>
                                                            <div class="clearfix"></div>
                                                            <!-- Passport Section Starts -->
                                                            <div class="passport_content_div">
    <?php if ($is_domestic_flight == false) {$addob = date('Y-m-d', strtotime('-30 years')); //For Internatinal Travel  ?>
    	 															<input placeholder="DOB" type="hidden" class="clainput" name="date_of_birth[]" value="<?php echo $addob?>">
                                                                    <div class="international_passport_content_div">
                                                                        <div class="col-xs-4 spllty">
                                                                            <span class="formlabel">Passport Number <?= $pass_mand ?></span>
                                                                            <div class="relativemask"> 
                                                                                <input type="text" name="passenger_passport_number[]" <?= $pass_req ?> id="passenger_passport_number_<?= $pax_index ?>" class="clainput" maxlength="10" placeholder="Passport Number" />
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xs-4 spllty">
                                                                            <span class="formlabel">Issuing Country <?= $pass_mand ?></span>
                                                                            <div class="selectedwrap">
                                                                                <select name="passenger_passport_issuing_country[]" <?= $pass_req ?> id="passenger_passport_issuing_country_<?= $pax_index ?>" class="mySelectBoxClass flyinputsnor">
                                                                                    <option value="INVALIDIP">Please Select</option>
        <?= $passport_issuing_country_options ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xs-4 spllty doe">
                                                                            <span class="formlabel">Date of Expire <?= $pass_mand ?></span>
                                                                            <div class="relativemask">
                                                                                <div class="col-xs-4 splinmar">
                                                                                    <div class="selectedwrap">
                                                                                        <select name="passenger_passport_expiry_day[]" <?= $pass_req ?> class="mySelectBoxClass flyinputsnor passport_expiry_day" data-expiry-type="day" id="passenger_passport_expiry_day_<?= $pax_index ?>" data-row-id="<?= ($pax_index); ?>">
                                                                                            <option value="INVALIDIP">DD</option>
        <?= $day_options; ?>
                                                                                        </select>
                                                                                        <input type="hidden" value="<?php echo $now; ?>" id="travel_year">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-xs-4 splinmar">
                                                                                    <div class="selectedwrap">
                                                                                        <select name="passenger_passport_expiry_month[]" <?= $pass_req ?> class="mySelectBoxClass flyinputsnor passport_expiry_month" data-expiry-type="month" id="passenger_passport_expiry_month_<?= $pax_index ?>" data-row-id="<?= ($pax_index); ?>">
                                                                                            <option value="INVALIDIP">MM</option>
        <?= $month_options; ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-xs-4 splinmar">
                                                                                    <div class="selectedwrap">
                                                                                        <select name="passenger_passport_expiry_year[]" <?= $pass_req ?> class="mySelectBoxClass flyinputsnor passport_expiry_year" data-expiry-type="year" id="passenger_passport_expiry_year_<?= $pax_index ?>" data-row-id="<?= ($pax_index); ?>">
                                                                                            <option value="INVALIDIP">YYYY</option>
        <?= $year_options; ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="pull-right text-danger hide" id="passport_error_msg_<?= $pax_index ?>"></div>
                                                                    </div>
        <?php
    } else { //For Domestic Travel, Set Static Passport Data
        $passport_number = rand(1111111111, 9999999999);
        $passport_issuing_country = 92;
        ?>
                                                                    <div class="domestic_passport_content_div hide">
                                                                        <input type="hidden" name="passenger_passport_number[]" value="<?= $passport_number ?>" id="passenger_passport_number_<?= $pax_index ?>">
                                                                        <input type="hidden" name="passenger_passport_issuing_country[]" value="<?= $passport_issuing_country ?>" id="passenger_passport_issuing_country_<?= $pax_index ?>">
                                                                        <input type="hidden" name="passenger_passport_expiry_day[]" value="<?= $static_passport_details['passenger_passport_expiry_day'] ?>" id="passenger_passport_expiry_day_<?= $pax_index ?>">
                                                                        <input type="hidden" name="passenger_passport_expiry_month[]" value="<?= $static_passport_details['passenger_passport_expiry_month'] ?>" id="passenger_passport_expiry_month_<?= $pax_index ?>">
                                                                        <input type="hidden" name="passenger_passport_expiry_year[]" value="<?= $static_passport_details['passenger_passport_expiry_year'] ?>" id="passenger_passport_expiry_year_<?= $pax_index ?>">
                                                                    </div>
    <?php } ?>
                                                            </div>
                                                            <!-- Passport Section Ends-->

                                                        </div><!-- inptalbox class ends -->
                                                    </div>
                                                </div>
    <?php
}//END FOR LOOP FOR PAX DETAILS
?>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="ontyp">
                                        <div class="kindrest">
                                            <div class="cartlistingbuk">
                                                <div class="cartitembuk">
                                                    <div class="col-md-12">
                                                        <div class="payblnhmxm">Have an e-coupon or a deal-code ? (Optional)</div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                               
                                                <div class="clearfix"></div>
                                                <div class="savemessage"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="sepertr"></div>
                                    <div class="clearfix"></div>
                                    <div class="contbk">
                                        <div class="contcthdngs">CONTACT DETAILS</div>
                                        <div class="cont_info">
                                        <div class="col-xs-12 nopad full_smal_forty">
                                            <div class="emailperson col-xs-12 col-md-5 nopad">
                                                <label class="cst_lbl">Mail ID</label>
                                                <input value="<?= @$lead_pax_details['email'] ?>" type="text" maxlength="80" required="required" id="billing-email" class="newslterinput nputbrd" placeholder="Email" name="billing_email">
                                            </div>
                                            <div class="col-xs-6 col-md-4 nopadding">
                                                <div class="hide">
                                                    <input type="hidden" name="billing_country" value="92">
                                                    <input type="hidden" name="billing_city" value="test">
                                                    <input type="hidden" name="billing_zipcode" value="test">
                                                    <input type="hidden" name="billing_address_1" value="test">

                                                </div>
                                                <label class="cst_lbl">Country Code</label>
                                                <select name="phone_country_code" class="newslterinput nputbrd _numeric_only" id="after_country_code" required>
    <?php echo diaplay_phonecode($phone_code, $active_data, $user_country_code); ?>
                                                </select> 
                                            </div>
                                            <div class="col-xs-6 col-md-3 nopadding">
                                                <label class="cst_lbl">Mobile No</label>
                                                <input value="<?= @$lead_pax_details['phone'] == 0 ? '' : @$lead_pax_details['phone']; ?>" type="text" name="passenger_contact" id="passenger-contact" placeholder="Mobile Number" class="newslterinput nputbrd _numeric_only" maxlength="12" minlength="8" required="required">
                                            </div>
                                            </div>
                                        <div class="clearfix"></div>

                                        <div class="notese">Your booking details & update alerts will be send to this contacts.</div>
                                        </div>
                                    </div>
                                    <div class="panel-group mb0 hide" role="tablist" aria-multiselectable="true">
                                        <div class="panel panel-default for_gst flight_special_req">
                                            <div class="panel-heading" role="tab" id="gst_opt">
                                                <h4 class="panel-title">
                                                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#gst_optnl" aria-expanded="true" aria-controls="gst_optnl">

                                                        <div class="labltowr arimobold">GST Information(Optional) <i class="more-less glyphicon glyphicon-plus"></i></div>
                                                    </a>
                                                </h4>
                                            </div>
                                            <div id="gst_optnl" class="panel-collapse collapse" role="tabpanel" aria-labelledby="gst_opt">
                                                <!-- <div class="contcthdngs">GST Information(Optional)</div> -->
                                                <div class="col-xs-12 gst_det" id="gst_form_div">
                                                    <div class="row">
                                                        <div class="col-xs-3"> GST Number </div>
                                                        <div class="col-xs-7"> 
                                                            <input type="text" class="newslterinput clainput nputbrd" id="gst_number" name="gst_number" value="">    
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-3"> GST company Name </div>
                                                        <div class="col-xs-7"> 
                                                            <input type="text" class="newslterinput nputbrd" id="gst_company_name" name="gst_company_name" vaule="">    
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-3"> Email </div>
                                                        <div class="col-xs-7"> 
                                                            <input type="email" class="newslterinput nputbrd" id="gst_email" name="gst_email" value="">    
                                                        </div>
                                                    </div>										   
                                                    <div class="row">
                                                        <div class="col-xs-3"> Phone Number </div>
                                                        <div class="col-xs-7"> 
                                                            <input type="text" class="newslterinput nputbrd _numeric_only" id="gst_phone" name="gst_phone" maxlength="10" value="">    
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-3"> Address </div>
                                                        <div class="col-xs-7"> 
                                                            <input type="text" class="newslterinput nputbrd" name="gst_address" id="gst_address" value="">    
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-3"> State </div>
                                                        <div class="col-xs-7">
<?php $state_list = generate_options($state_list); ?>
                                                            <select name="gst_state" class="clainput" id="gststate">
                                                                <option value="INVALIDIP">Please Select</option>
                                                            <?= $state_list ?>
                                                            </select>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <!-- Dyanamic Baggage&Meals Section Starts -->
<?php
if (valid_array($extra_services) == true) {
    if (isset($extra_services['ExtraServiceDetails']['Baggage'])) {
        $baggage_meal_seat_details['baggage_meal_details']['Baggage'] = $extra_services['ExtraServiceDetails']['Baggage'];
    }
    if (isset($extra_services['ExtraServiceDetails']['Meals'])) {
        $baggage_meal_seat_details['baggage_meal_details']['Meals'] = $extra_services['ExtraServiceDetails']['Meals'];
    }
    if (isset($extra_services['ExtraServiceDetails']['Seat'])) {
        $baggage_meal_seat_details['baggage_meal_details']['Seat'] = $extra_services['ExtraServiceDetails']['Seat'];
    }
    $baggage_meal_seat_details['total_adult_count'] = $total_adult_count;
    $baggage_meal_seat_details['total_child_count'] = $total_child_count;
    $baggage_meal_seat_details['total_infant_count'] = $total_infant_count;
    $baggage_meal_seat_details['total_pax_count'] = $total_pax_count;
    echo $GLOBALS['CI']->template->isolated_view('flight/dynamic_baggage_meal_seat_details', $baggage_meal_seat_details);
}
?>
                                    <!-- Dyanamic Baggage&Meals Section Ends -->
                                    <!-- Seats&Meals Preference Section Starts -->
                                    <?php
                                    if (valid_array($extra_services) == true) {
                                        if (isset($extra_services['ExtraServiceDetails']['MealPreference'])) {
                                            $seat_meal_preference_details['seat_meal_preference_details']['MealPreference'] = $extra_services['ExtraServiceDetails']['MealPreference'];
                                        }
                                        if (isset($extra_services['ExtraServiceDetails']['SeatPreference'])) {
                                            $seat_meal_preference_details['seat_meal_preference_details']['SeatPreference'] = $extra_services['ExtraServiceDetails']['SeatPreference'];
                                        }
                                        $seat_meal_preference_details['total_adult_count'] = $total_adult_count;
                                        $seat_meal_preference_details['total_child_count'] = $total_child_count;
                                        $seat_meal_preference_details['total_infant_count'] = $total_infant_count;
                                        $seat_meal_preference_details['total_pax_count'] = $total_pax_count;
                                        echo $GLOBALS['CI']->template->isolated_view('flight/seat_meal_preference_details', $seat_meal_preference_details);
                                    }
                                    ?>
                                    <!-- Seats&Meals Preference Section Ends -->
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
                                            <div class="row <?= $payment_option_visibility ?>">
                                            <?php if (in_array(PAY_NOW, $active_payment_options)) { ?>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="payment-mode-<?= PAY_NOW ?>">
                                                                <input <?= $default_payment_option ?> name="payment_method" type="radio" required="required" value="<?= PAY_NOW ?>" id="payment-mode-<?= PAY_NOW ?>" class="form-control b-r-0" placeholder="Payment Mode">
                                                                Pay Now
                                                            </label>
                                                        </div>
                                                    </div>
<?php } ?>
<?php if (in_array(PAY_AT_BANK, $active_payment_options)) { ?>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="payment-mode-<?= PAY_AT_BANK ?>">
                                                                <input <?= $default_payment_option ?> name="payment_method" type="radio" required="required" value="<?= PAY_AT_BANK ?>" id="payment-mode-<?= PAY_AT_BANK ?>" class="form-control b-r-0" placeholder="Payment Mode">
                                                                Pay At Bank
                                                            </label>
                                                        </div>
                                                    </div>
<?php } ?>
                                            </div>
                                        <div class="clikdiv">
                                        <div class="squaredThree">
                                            <input id="terms_cond1" type="checkbox" name="tc" checked="checked" required="required">
                                            <label for="terms_cond1"></label>
                                        </div>
                                        <span class="clikagre" id="clikagre">
                                            By Clicking Continue Booking, I confirm that I have read and accept the <a href="<?php echo base_url();?>index.php/terms-conditions" target="_blank">Terms and Conditions</a> & <a href="<?php echo base_url();?>index.php/privacy-policy" target="_blank">Privacy Policies</a>
                                        </span>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="sepertr"></div>
                                            <div class="continye col-sm-4 col-xs-6 nopad">
                                                <button type="submit" id="flip" name="flight" class="bookcont continue_booking_button">Continue</button>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="sepertr"></div>
                                            <div class="temsandcndtn hide">
                                                Most countries require travellers to have a passport valid for more than 3 to 6 months from the date of entry into or exit from the country. Please check the exact rules for your destination country before completing the booking.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
					
                        <?php if (is_logged_in_user() == true) { ?>
                        <div class="col-xs-4 nopadding">
                            <div class="insiefare">
                                <div class="farehd arimobold">Passenger List</div>
                                <div class="fredivs">
                                    <div class="psngrnote">
    <?php
    if (valid_array($traveller_details)) {
        $traveller_tab_content = 'You have saved passenger details in your list,on typing, passenger details will auto populate.';
    } else {
        $traveller_tab_content = 'You do not have any passenger saved in your list, start adding passenger so that you do not have to type every time. <a href="' . base_url() . 'index.php/user/profile?active=traveller" target="_blank">Add Now</a>';
    }
    ?>
                                        <?= $traveller_tab_content; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
<?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<span class="hide">
    <input type="hidden" id="pri_passport_min_exp" value="<?= $passport_minimum_expiry_date ?>">
</span>

<?php echo $GLOBALS['CI']->template->isolated_view('share/flight_session_expiry_popup'); ?>
<?php echo $GLOBALS['CI']->template->isolated_view('share/passenger_confirm_popup'); ?>

<?php
/*
 * Balu A
 * Flight segment details
 * Outer summary and Inner Summary
 */

function flight_segment_details(array $SegmentDetails, array $SegmentSummary, array $search_data): array
{
    $inner_summary = array_map(
        fn($seg, $idx) => render_segment_group($seg, $SegmentSummary[$idx], $search_data),
        $SegmentDetails,
        array_keys($SegmentDetails)
    );

    $outer_summary = array_map(
        fn($summary) => render_summary_card($summary),
        $SegmentSummary
    );

    return [
        'segment_abstract_details' => '<div class="moreflt spltopbk">' . implode("\n", $outer_summary) . '</div>',
        'segment_full_details' => implode("\n", $inner_summary)
    ];
}

function render_segment_group(array $segments, array $summary, array $search_data): string
{
    if($search_data['v_class'] == "PremiumEconomy"){
        $search_data['v_class'] = "Premium Economy";
    }
    $output = '<div class="ontyp">';
    $output .= '<div class="labltowr arimobold">' . $summary['OriginDetails']['CityName'] . ' to ' . $summary['DestinationDetails']['CityName'] . '</div>';

    foreach ($segments as $idx => $flight) {
        $output .= render_segment_flight_info($flight, $idx, $search_data['v_class']);

        if (isset($flight['WaitingTime'], $segments[$idx + 1])) {
            $next_seg = $segments[$idx + 1];
            $output .= render_connecting_info($next_seg['OriginDetails']['AirportName'], $flight['WaitingTime']);
        }
    }

    $output .= '</div>';
    return $output;
}

function render_segment_flight_info(array $flight, int $stop, string $travel_class): string
{
    $origin = $flight['OriginDetails'];
    $destination = $flight['DestinationDetails'];
    $airline = $flight['AirlineDetails'];

    $origin_terminal = empty($origin['Terminal']) ? '' : 'Terminal ' . $origin['Terminal'];
    $destination_terminal = empty($destination['Terminal']) ? '' : 'Terminal ' . $destination['Terminal'];
    $available_seats = $flight['AvailableSeats'] ?? '';
    $cabin_baggage = !empty($flight['CabinBaggage']) ? '<span class="bg_cbn">Cabin : 7Kgs</span>' : '';

    $html = '
<div class="allboxflt">
  <div class="allboxfltin">
    <div class="col-xs-4 nopadding width_adjst">
      <div class="jetimg">
        <img alt="' . htmlspecialchars($airline['AirlineCode']) . '" src="' . SYSTEM_IMAGE_DIR . 'airline_logo/' . $airline['AirlineCode'] . '.gif">
      </div>
      <div class="alldiscrpo">
        ' . htmlspecialchars($airline['AirlineName']) . '<span class="sgsmal"> ' . $airline['AirlineCode'] . ' ' . $airline['FlightNumber'] . '</span>
      </div>
      <div class="clearfix"></div>
      <span class="airlblxl"><strong>Starts on - </strong>' . month_date_year_new($origin['DateTime']) . '</span>
      <span class="flt_tim">' . local_time($origin['DateTime']) . '</span>
      <span class="air_code d-block">' . $origin['AirportCode'] . '</span>
      <span class="portnme">' . $origin['AirportName'] . '</span>
      <p class="air_port">' . $origin['AirportCity'] . ', ' . $origin['Country'] . '</p>
      <span class="air_trm">' . $origin_terminal . '</span>
    </div>
    <div class="col-xs-4 nopadding width_adjst">
      <span class="portnme textcntr">' . $flight['SegmentDuration'] . '</span>
      <span class="portnme textcntr">Stop : ' . $stop . '</span>
    </div>
    <div class="col-xs-4 nopadding width_adjst">
      <h5 class="text-uppercase">' . $travel_class . '</h5>
      <span class="airlblxl"><strong>Arrive on - </strong>' . month_date_year_new($destination['DateTime']) . '</span>
      <span class="flt_tim">' . local_time($destination['DateTime']) . '</span>
      <span class="air_code d-block">' . $destination['AirportCode'] . '</span>
      <span class="portnme">' . $destination['AirportName'] . '</span>
      <p class="air_port">' . $destination['AirportCity'] . ', ' . $destination['Country'] . '</p>
      <span class="air_trm">' . $destination_terminal . '</span>
    </div>
  </div>
  <div class="clearfix"></div>
  <div class="bag_detl">
    ' . $cabin_baggage . '<span class="bg_chkin">Check-In : ' . $flight['Baggage'] . '</span>
    <span class="bg_chkin avail_seats">Seats : ' . $available_seats . '</span>
  </div>
</div>';

    return $html;
}


function render_connecting_info(string $airport_name, string $waiting_time): string
{
    $safe_airport = htmlspecialchars($airport_name, ENT_QUOTES, 'UTF-8');
    $safe_waiting = htmlspecialchars($waiting_time, ENT_QUOTES, 'UTF-8');

    return '<div class="clearfix"></div>'
        . '<div class="connectnflt">'
        . '<div class="conctncentr">'
        . '<span class="fa fa-plane"></span>Change of planes at ' . $safe_airport
        . ' | <span class="fa fa-clock-o"></span> Waiting : ' . $safe_waiting
        . '</div>'
        . '</div>'
        . '<div class="clearfix"></div>';
}
function render_summary_card(array $summary): string
{
    $duration = htmlspecialchars($summary['TotalDuaration'] ?? '', ENT_QUOTES, 'UTF-8');
    $stops = (int)($summary['TotalStops'] ?? 0);
    $airline = $summary['AirlineDetails'] ?? [];
    $origin = $summary['OriginDetails'] ?? [];
    $destination = $summary['DestinationDetails'] ?? [];

    $airlineCode = htmlspecialchars($airline['AirlineCode'] ?? '', ENT_QUOTES, 'UTF-8');
    $airlineName = htmlspecialchars($airline['AirlineName'] ?? '', ENT_QUOTES, 'UTF-8');
    $flightNumber = htmlspecialchars($airline['FlightNumber'] ?? '', ENT_QUOTES, 'UTF-8');

    $originName = htmlspecialchars($origin['AirportName'] ?? '', ENT_QUOTES, 'UTF-8');
    $originCode = htmlspecialchars($origin['AirportCode'] ?? '', ENT_QUOTES, 'UTF-8');
    $originDate = isset($origin['DateTime']) ? month_date_year_new($origin['DateTime']) : '';

    $destinationName = htmlspecialchars($destination['AirportName'] ?? '', ENT_QUOTES, 'UTF-8');
    $destinationCode = htmlspecialchars($destination['AirportCode'] ?? '', ENT_QUOTES, 'UTF-8');
    $destinationDate = isset($destination['DateTime']) ? month_date_year_new($destination['DateTime']) : '';

    $html  = '<div class="ontypsec">';
    $html .= '<div class="allboxflt">';

    $html .= '<div class="col-xs-3 nopadding width_adjst">';
    $html .= '<div class="jetimg">';
    $html .= '<img class="airline-logo" alt="' . $airlineCode . '" src="' . SYSTEM_IMAGE_DIR . 'airline_logo/' . $airlineCode . '.gif">';
    $html .= '</div>';
    $html .= '<div class="alldiscrpo">';
    $html .= '<span class="air_name">' . $airlineName . '</span>';
    $html .= '<span class="sgsmal"> ' . $airlineCode . $flightNumber . '</span>';
    $html .= '</div>';
    $html .= '</div>';

    $html .= '<div class="col-xs-7 nopadding width_adjst">';
    $html .= '<div class="col-xs-6">';
    $html .= '<span class="airlblxl">' . $originName . ' (' . $originCode . ')</span>';
    $html .= '<span class="portnme">' . $originDate . '</span>';
    $html .= '</div>';
    $html .= '<div class="col-xs-6">';
    $html .= '<span class="airlblxl">' . $destinationName . ' (' . $destinationCode . ')</span>';
    $html .= '<span class="portnme">' . $destinationDate . '</span>';
    $html .= '</div>';
    $html .= '</div>';

    $html .= '<div class="col-xs-2 nopadding width_adjst">';
    $html .= '<span class="portnme textcntr">' . $duration . '</span>';
    $html .= '<span class="portnme textcntr">Stop:' . $stops . '</span>';
    $html .= '</div>';

    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

function get_fare_summary($FareDetails, $FareBreakdown, $convenience_fees, $org_convience_fee, $ApiPriceDetails, $api_markup_details, $convenience_orginal, $convenience_fees_text) {
    // debug($api_markup_details);exit;
    $api_price_details = base64_encode(json_encode($ApiPriceDetails));
    $api_markup_details = base64_encode(json_encode($api_markup_details));
    $convenience_orginal = base64_encode(json_encode($convenience_orginal));
    $total_tax = $FareDetails['TotalTax'] - $FareDetails['_GST'];
    $flight_total_price = round(($FareDetails['TotalFare'] + $convenience_fees), 2);
    $currency_symbol = $FareDetails['CurrencySymbol'];
    $gst_data = '';
    $i_val = 0;
    if ($FareDetails['_GST'] > 0) {
        $gst_data = '<div class="col-xs-8 nopadding">
						<div class="faresty">GST/VAT</div>
						</div>
					<div class="col-xs-4 nopadding">
						<div class="amnter arimobold">' . $currency_symbol . ' ' . $FareDetails['_GST'] . ' </div>
					</div>
					';
    }
    $fare_summary = '<div class="insiefare">
				<div class="farehd arimobold">Fare Summary</div>
				<div class="fredivs">';
    $pax_base_details = '<div class="kindrest">
							<div class="freshd">Base Fare</div>';
    $pax_tax_details = '<div class="kindrest">
								<div class="freshd">Taxes</div>';
    foreach ($FareBreakdown as $v) {
        $pax_type = $v['PassengerType'];
        $pax_base_fare = $v['BaseFare'];
        $pax_count = $v['Count'];
        $signle_pax_fare = number_format($pax_base_fare / $pax_count, 2);
        $pax_base_details .= '<div class="reptallt">
						<div class="col-xs-8 nopadding">
							<div class="faresty">' . $pax_count . ' ' . $pax_type . '(s) ‎(1 X ' . $signle_pax_fare . ')</div>
						</div>
						<div class="col-xs-4 nopadding">
							<div class="amnter"><span class="base_fare_span">' . $currency_symbol . '</span> <span class="base_fare_value'.$i_val.'">' . $pax_base_fare . ' </span></div>
						</div>
					</div>';
        $i_val++;
    }
    $pax_tax_details .= '<div class="reptallt">
						<div class="col-xs-8 nopadding">
							<div class="faresty">Taxes & Fees</div>
						</div>
						<div class="col-xs-4 nopadding">
							<div class="amnter arimobold"><span class="tax_fees_span">' . $currency_symbol . '</span> <span class="tax_fees_value">' . $total_tax . ' </span></div>
						</div>
						<div class="col-xs-8 nopadding">
							<div class="faresty">'.$convenience_fees_text.'</div>
						</div>
						<span class="org_convience_fee hide">'.$org_convience_fee.'</span>
						<div class="col-xs-4 nopadding">
							<div class="amnter arimobold"><span class="convenience_fees_span">'.$currency_symbol.'</span> <span class="convenience_fees_value"> '.$convenience_fees.' </span></div>
						</div>
                       
						' . $gst_data . '
						<div class="col-xs-8 nopadding promo_code_discount hide">
							<div class="faresty">Promo Code Discount</div>
						</div>
						<div class="col-xs-4 nopadding promo_code_discount hide">
							<div class="amnter arimobold promo_discount_val"> </div>
						</div>
					</div>';
    $pax_base_details .= '</div>';
    $pax_tax_details .= '</div>';
    $fare_summary .= $pax_base_details;
    $fare_summary .= $pax_tax_details;
    $fare_summary .= '
					<div class="clearfix"></div>
                    <div class="extra_services_cls">
                    <div class="baggagecharge" id="extra_baggage_charge_label" style="display:none">
                                Extra Baggage Charge '.$currency_symbol.'
                                <span id="extra_baggage_charge"></span>
                                <span class="btn btn-sm btn-default" id="remove_extra_baggage"><i class="fa fa-times" aria-hidden="true"></i></span>
                            </div>
                            <div class="baggagecharge" id="extra_meal_charge_label" style="display:none">
                                Meal Charge '.$currency_symbol.'
                                <span id="extra_meal_charge"></span>
                                <span class="btn btn-sm btn-default" id="remove_extra_meal"><i class="fa fa-times" aria-hidden="true"></i></span>
                            </div>
                            <div class="baggagecharge" id="extra_seat_charge_label" style="display:none">
                                Seat Charge '. $currency_symbol .' 
                                <span id="extra_seat_charge"></span>
                                <span class="btn btn-sm btn-default" id="remove_extra_seat"><i class="fa fa-times" aria-hidden="true"></i></span>
                            </div>
                    </div>
						<div class="reptalltftr">
							<div class="col-xs-6 nopadding">
								<div class="farestybig">Grand Total</div>
							</div>
							<div class="col-xs-6 nopadding ">
                                <div id="grandtotal_valid" class="grandtotal_valid hide">' .$flight_total_price. '</div>
								<div class="amnterbig arimobold grandtotal"><span class="grandtotal_span"> ' .$currency_symbol . ' </span> <span class="total_booking_amount grandtotal_value"> ' . ($flight_total_price) . '</span>  </div>
							    <div class="hide" id="api_price_details" data-api_price ="'.$api_price_details.'"></div>
                                <div class="hide" id="api_markup_details" data-markup_price ="'.$api_markup_details.'"></div>
                                <div class="hide" id="convenience_fees_original" data-convience_fee ="'.$convenience_orginal.'"></div>
                            </div>
						</div>
					</div>
					</div>';
    return $fare_summary;
}

//Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/flight_session_expiry_script.js'), 'defer' => 'defer');
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/booking_script.js'), 'defer' => 'defer');
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/flight_booking.js'), 'defer' => 'defer');
Js_Loader::$js[] = array('src' => $GLOBALS['CI']->template->template_js_dir('page_resource/flight_extra_services.js'), 'defer' => 'defer');
Js_Loader::$css[] = array('href' => $GLOBALS['CI']->template->template_css_dir('flight_extra_services.css'), 'media' => 'screen');
?>
<script type="text/javascript">
    $("#back_btn").click(function (){
     window.location.href = '<?php echo $flight_search_url;?>';
    });
    /*
     session time out variables defined
     */
    var search_session_expiry = "<?php echo $GLOBALS ['CI']->config->item('flight_search_session_expiry_period'); ?>";
    var search_session_alert_expiry = "<?php echo $GLOBALS ['CI']->config->item('flight_search_session_expiry_alert_period'); ?>";
    var search_hash = "<?php echo $session_expiry_details['search_hash']; ?>";
    var start_time = "<?php echo $session_expiry_details['session_start_time']; ?>";
    var session_time_out_function_call = 1;

    $('.panel-collapse').on('show.bs.collapse', function () {
        $(this).siblings('.panel-heading').addClass('active');
    });

    $('.panel-collapse').on('hide.bs.collapse', function () {
        $(this).siblings('.panel-heading').removeClass('active');
    });
    $(".cont_plus").click(function () {
        $(".cont_show").show(500).css('display', 'inline-block');
        $(".cont_minus").show(500);
        $(".cont_plus").hide(500);
    });
    $(".cont_minus").click(function () {
        $(".cont_show").hide(500);
        $('.cont_plus').show();
        $(".cont_minus").hide(500);
    });
   //  $(".passport_expiry_year").click(function () {
   //  	var travel_year = $('#travel_year').val();
   //  	var select_year = $(this).val()
   //  	if(select_year > travel_year){
   //  		var day_options ='';
   //  		for(i = 1; i<=31; i++){
   //  			day_options += '<option value='+i+'>'+i+'</option>';
   //  		}
    		
   //  		var month_names = ["Jan" ,"Feb", "Mar", "Apr","May","Jun","Jul", "Aug","Sep","Oct","Nov","Dec"];
   //  		var month_options ='';
   //  		for(i = 0; i<month_names.length; i++){
   //  			month_options += '<option value='+i+'>'+month_names[i]+'</option>';
   //  		}
   //  		$('.passport_expiry_day').html(day_options);
   //  		$('.passport_expiry_month').html(month_options);
   //  	}
   //  	else{
   //  		alert('same');
   //  	}
    	
  	// });
</script>