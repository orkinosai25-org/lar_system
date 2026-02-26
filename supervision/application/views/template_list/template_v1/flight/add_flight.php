<?php

// $d=strtotime("today");
// $seat_temp_id= date("Ymd", $d).date("his").rand(10,1000);


// error_reporting(E_ALL);

if ($_SERVER['REMOTE_ADDR'] == "192.168.0.40") {
}

?>
<style type="text/css">
   .wdt50 {
      width: 50%;
      margin-right: 2%;
   }

   .wdt25 {
      width: 25%;
      margin-right: 2%;
   }

   .wdt10 {
      width: 12%;
      margin-right: 2%;
   }

   .org_row {
      margin: 0 -15px
   }

   .nopad {
      padding: 0 !important
   }

   .far_info.bag_info .radio {
      width: 100%;
      clear: both;
   }

   .far_info {
      line-height: 34px;
   }

   input.radioIp {
      margin-top: 10px;
   }

   label.radio-inline {
      display: inline-block;
      vertical-align: top;
   }

   .padL5 {
      padding-left: 5px;
   }

   .padd {
      padding: 0 2px;
   }

   .red {
      color: red;
   }

   .wdt50 {
      width: 50%
   }

   .wdt8 {
      width: 8%
   }

   .cnl_flx {
      display: flex;
      align-items: center;
      justify-content: space-between;
      line-height: 29px !important;
   }
</style>
<script src="<?php echo SYSTEM_RESOURCE_LIBRARY ?>/ckeditor/ckeditor.js"></script>
<!--link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['CI']->template->template_css_dir('page_resource/wickedpicker.css'); ?>"-->
<!-- <link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['CI']->template->template_css_dir('page_resource/timepicker.css'); ?>"> -->
<section class="content" style="opacity: 1;">
   <!-- UTILITY NAV -->
   <div class="container-fluid utility-nav clearfix">
      <!-- ROW --><!-- /ROW -->
   </div>
   <!-- Info boxes -->
   <div class="row">
      <!-- HTML BEGIN -->
      <div class="bodyContent">
         <div class="panel panel-primary">
            <!-- PANEL WRAP START -->
            <div class="panel-heading">
               <!-- PANEL HEAD START -->
               <div class="panel-title"><i class="fa fa-edit"></i> Add Flight</div>
            </div>
            <!-- PANEL HEAD START -->
            <div class="panel-body ad_flt">
               <!-- PANEL BODY START -->
               <form id='addflight' action="<?php echo base_url(); ?>index.php/flight/save_crs_flight_details" enctype="multipart/form-data" class="form-horizontal" method="POST" autocomplete="off">

                  <!-- PANEL BODY START -->
                  <fieldset>

                     <div class="col-xs-12">
                        <div class="col-xs-12 col-sm-12">
                           <div class="form-group">
                              <label form="user" for="title" class="col-sm-3 control-label">Flight Type</label>
                              <div class="col-sm-9 ad_pad">
                                 <div class="form-group">
                                    <div class="col-sm-4">
                                       <label class="radio-inline">
                                          <input type="radio" class="fare_type" name="fare_type" checked value="0">Charter Flight
                                       </label>
                                     <!--   <label class="radio-inline">
                                          <input type="radio" class="fare_type" name="fare_type" value="1">Empty Leg Flight
                                       </label> -->
                                    </div>
                                    <input type="hidden" class="crs_is_triptype" name="is_triptype" checked value="0">

                                 </div> <!-- id="fare_rule" -->
                              </div>
                           </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                           <div class="form-group">
                              <label form="user" for="title" class="col-sm-6 control-label">Departure Airport</label>
                              <div class="col-sm-6">
                                 <input type="text" class="form-control getAiportlist" id='flight1_departure_airport' placeholder="Departure Airport" name="origin[]" required>
                                 <?php if (false) { ?>
                                    <select class="form-control" name="origin[]" id="origin_0">
                                       <option value="NA">Select Departure Airport</option>
                                       <?php
                                       foreach ($airport_list_l as $k => $v) {

                                          echo  '<option value="' . $v['airport_code'] . '" >' . $v['airport_city'] . '</option>';
                                       }

                                       ?>

                                    </select>
                                 <?php } ?>
                              </div>
                           </div>
                        </div>

                        <div class="col-xs-12 col-sm-6 arrival_div">
                           <div class="form-group">
                              <label form="user" for="title" class="col-sm-6 control-label">Arrival Airport</label>
                              <div class="col-sm-6">
                                 <input type="text" class="form-control getAiportlist" id='flight1_arrival_airport' placeholder="Arrival Airport" name="destination[]" required>
                                 <?php if (false) { ?>
                                    <select class="form-control" name="destination[]" id="destination_0">
                                       <option value="NA">Select Arrival Airport</option>
                                       <?php
                                       foreach ($airport_list_l as $k => $v) {

                                          echo  '<option value="' . $v['airport_code'] . '" >' . $v['airport_city'] . '</option>';
                                       }

                                       ?>
                                    </select>
                                 <?php } ?>
                              </div>
                           </div>
                        </div>





                        <div class="col-xs-12 col-sm-6">
                           <div class="form-group">
                              <label form="user" for="title" class="col-sm-6 control-label">Date Beginning</label>

                              <div class="col-sm-6">
                                 <input type="text" class="form-control" name="dep_date[]" id="dep_date1" name="DepartureDate[]" required />
                              </div>
                           </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                           <div class="form-group">
                              <label form="user" for="title" class="col-sm-6 control-label">Date Ending</label>
                              <div class="col-sm-6">
                                 <input type="text" class="form-control" name="arr_date[]" id="arr_date1" required />
                              </div>
                           </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                           <div class="form-group">
                              <label form="user" for="title" class="col-sm-6 control-label">Departure Time</label>
                              <div class="col-sm-6">
                                 <!-- <input type="time" class="form-control" placeholder="12:50:00 24 hour format" name="departure_time[]" required/> -->
                                 <input type="text" name="departure_time[]" id="departure_time1" class="timepicker-24 form-control" placeholder="12:50 24 hour format" />
                              </div>
                           </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                           <div class="form-group">
                              <label form="user" for="title" class="col-sm-6 control-label">Arrival Time</label>
                              <div class="col-sm-6">
                                 <!-- <input type="time" class="form-control" placeholder="15:50:00 24 hour format" name="arrival_time[]" required/> -->
                                 <input type="text" name="arrival_time[]" id="arrival_time1" class="timepicker-24 form-control" placeholder="12:50 24 hour format" />
                              </div>
                           </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                           <div class="form-group">
                              <label form="user" for="title" class="col-sm-6 control-label">Flight Number</label>
                              <div class="col-sm-6">
                                 <input type="text" class="form-control" placeholder="3123" id='flight_num_1' name="flight_num[]" required />
                              </div>
                           </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                           <div class="form-group">
                              <label form="user" for="title" class="col-sm-6 control-label">Operating Airline Code</label>
                              <div class="col-sm-6">
                                 <input type="text" class="form-control tags" id="operating_air_code" placeholder="SG" name="carrier_code[]" required />
                              </div>
                           </div>
                        </div>


                        <div class="col-xs-12 col-sm-12">
                           <div class="form-group">
                              <label form="user" for="title" class="col-sm-3 control-label">Fare Rule</label>
                              <div class="col-sm-9 ad_pad">
                                 <textarea class="ckeditor" id="editor" rows="2" cols="3" class="form-control" placeholder="Fare Rule" name="fare_rule[]"></textarea>
                                 <!-- id="fare_rule" -->
                              </div>
                           </div>
                        </div>
                        <div class="col-xs-12 col-sm-12">
                           <div class="form-group">
                              <label form="user" for="title" class="col-sm-3 control-label">Aircraft Images</label>

                              <div class="col-xs-4 bottm_prt nopad" style="margin-top: 10px;">
                                 <input type="hidden" name="hotel_id" class="form-control" value="<?= $hotel_id; ?>">
                                 <input type="file" id="pro-image" name="pro-image[]" class="form-control upld_img_mng" multiple>


                              </div>
                              <div class="col-xs-12 bottm_prt nopad">
                                 <div id="preview_image"></div>
                              </div>

                           </div>



                        </div>

                        <div class="col-xs-12 nopad far_info">

                           <legend class="sm_titl">Fare Information (in USD)</legend>
                           <div class="org_row col-md-12">
                              <div class="col-xs-3">
                                 <div class="org_row">
                                    <div class="radio cnl_flx">
                                       <label for="value_type" class="col-sm-4 control-label">Select Aircraft<span class="text-danger">*</span></label>
                                       <select class="col-sm-8 wdt50 form-control" id="aircraft" name="aircraft" required>
                                          <option value=''>select</option>
                                          <?php foreach ($aircraft_data as $ad) {
                                             $selected = '';
                                             if ($ad['origin'] == $pilot_list['aircraft']) {
                                                $selected = 'selected';
                                             } ?>
                                             <option <?= $selected ?> value="<?= $ad['origin'] ?>"><?= $ad['type'] ?> <?= $ad['model'] ?></option>
                                          <?php }  ?>
                                       </select>

                                    </div>
                                 </div>
                              </div>
                              <div class="col-xs-3">
                                 <div class="org_row">
                                    <div class="radio cnl_flx">
                                       <label for="value_type" class="col-sm-4  wdt50 control-label">Seats Available<span class="text-danger">*</span></label>

                                       <input type="text" value='0' class="col-sm-8 wdt25  form-control" name="seats" id="seats" required minlenght="1" maxlength="5">
                                       <input type="hidden" value='0' class="col-sm-8 wdt25  form-control" name="seat_temp_id" id="seat_temp_id">
                                    </div>
                                 </div>
                              </div>

                              <div class="col-xs-6 nopad">
                                 <div class="org_row">
                                    <div class="radio cnl_flx">
                                       <label for="cancellation_percentage" class="col-sm-4 control-label">Cancellation Price<span class="text-danger">*</span></label>

                                       <input type="number" value='0' class="col-sm-8 wdt8  form-control" name="cancellation_percentage" id="cancellation_percentage" required min="0" max="100" minlenght="1" maxlength="5" onkeydown="if(event.key==='.'){event.preventDefault();}" oninput="event.target.value = event.target.value.replace(/[^0-9]*/g,'')" ;>

                                       <label for="cancellation_percentage" class="radio-inline">
                                          <input id="cancellation_plus" checked="checked" type="radio" value="plus" name="cancellation" class=" value_type_plus radioIp" required=""> Plus(+ USD)</label>
                                       <label for="cancellation_percentage" class="radio-inline">
                                          <input id="cancellation_percent" type="radio" value="percentage" name="cancellation" class=" value_type_percent radioIp" required=""> Percentage(%)</label>
                                    </div>
                                 </div>
                              </div>


                           </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 fare_info nopad">


                           <div class="clearfix"></div>
                           <div class="charter org_row ">
                              <div class="col-xs-12">
                                 <h4>Charter Price</h4>
                                 <div class="clearfix"></div>

                                 <div class="org_row">
                                    <div class="radio">
                                       <label for="value_type" class="col-sm-3 control-label">Base Fare<span class="text-danger">*</span></label>
                                       <input type="text" value='0' class="col-sm-3 wdt25 form-control numeric" placeholder="5050" name="charter_basefare" required="" minlenght="1" maxlength="5">
                                    </div>
                                 </div>
                                 <div class="clearfix"></div>

                                 <div class="org_row">
                                    <div class="radio">
                                       <label for="value_type" class="col-sm-3 control-label">TAX<span class="text-danger">*</span></label>
                                       <input type="text" value='0' class="col-sm-3 wdt25 form-control numeric" placeholder="5050" name="charter_tax" required="" minlenght="1" maxlength="5">
                                    </div>
                                 </div>
                                 <div class="clearfix"></div>


                                 <div class="org_row">
                                    <div class="radio">
                                       <label for="value_type" class="col-sm-3 control-label">VAT<span class="text-danger">*</span></label>
                                       <input type="text" value='0' class="col-sm-3 wdt25 form-control numeric" placeholder="5050" name="charter_vat" required="" minlenght="1" maxlength="5">
                                    </div>
                                 </div>
                              </div>
                           </div>


                           <div class="empty_leg org_row">
                              <div class="col-xs-12">
                                 <h4>Empty Leg Price</h4>
                                 <strong class="padL5">Adult</strong>
                                 <div class="org_row">
                                    <div class="radio">
                                       <label for="value_type" class="col-sm-3 control-label">Base Fare<span class="text-danger">*</span></label>
                                       <input type="text" value='0' class="col-sm-3 wdt25 form-control numeric" placeholder="5050" name="adult_basefare" required="" minlenght="1" maxlength="5">
                                    </div>
                                 </div>
                                 <?php

                                 foreach ($tax_list as $tk => $tv) {
                                    $tax_name = preg_replace('/\s+/', '_', $tv['tax_name']);
                                    if ($tax_name !== 'GST') {

                                 ?>
                                       <div class="clearfix"></div>
                                       <div class="org_row">
                                          <div class="radio">
                                             <label for="<?= 'adt_value_type_' . $tax_name ?>" class="col-sm-3 control-label"><?= $tv['tax_name'] ?>
                                                <span class="text-danger">*</span></label>
                                             <input type="text" class="col-sm-3 wdt25 form-control numeric" value='0' placeholder="500" name="tax[adt][<?= $tax_name ?>][value]" value="0" minlength="1" maxlength="6">

                                          </div>
                                       </div>
                                 <?php }
                                 } ?>


                              </div>

                              <div class="col-xs-12">
                                 <strong class="padL5">Child</strong>
                                 <div class="org_row">
                                    <div class="radio">
                                       <label for="value_type" class="col-sm-3 control-label">Base Fare<span class="text-danger">*</span></label>
                                       <input type="text" value='0' class="col-sm-3 wdt25 form-control numeric" placeholder="5050" name="child_basefare" required="" minlenght="1" maxlength="5">
                                    </div>
                                 </div>
                                 <?php
                                 // debug($tax_list);exit;
                                 foreach ($tax_list as $tk => $tv) {
                                    $tax_name = preg_replace('/\s+/', '_', $tv['tax_name']);
                                    if ($tax_name !== 'GST') {
                                 ?>
                                       <div class="clearfix"></div>
                                       <div class="org_row">
                                          <div class="radio">
                                             <label for="<?= 'child_value_type_' . $tax_name ?>" class="col-sm-3 control-label"><?= $tv['tax_name'] ?>
                                                <span class="text-danger">*</span></label>
                                             <input type="text" class="col-sm-3 wdt25 form-control numeric" value='0' placeholder="500" name="tax[child][<?= $tax_name ?>][value]" value="0" minlength="1" maxlength="6">

                                          </div>
                                       </div>
                                 <?php }
                                 } ?>


                              </div>

                              <div class="col-xs-12">
                                 <strong class="padL5">Infant</strong>
                                 <div class="org_row">
                                    <div class="radio">
                                       <label for="value_type" class="col-sm-3 control-label">Base Fare<span class="text-danger">*</span></label>
                                       <input type="text" value='0' class="col-sm-3 wdt25 form-control numeric" placeholder="500" name="infant_basefare" required="" minlength="1" maxlength="6" />
                                    </div>
                                 </div>
                                 <?php
                                 // debug($tax_list);exit;
                                 foreach ($tax_list as $tk => $tv) {
                                    $tax_name = preg_replace('/\s+/', '_', $tv['tax_name']);
                                    if ($tax_name !== 'GST') {
                                 ?>
                                       <div class="clearfix"></div>
                                       <div class="org_row">
                                          <div class="radio">
                                             <label for="<?= 'inf_value_type_' . $tax_name ?>" class="col-sm-3 control-label"><?= $tv['tax_name'] ?>
                                                <span class="text-danger">*</span></label>
                                             <input type="text" class="col-sm-3 wdt25 form-control numeric" value='0' placeholder="500" name="tax[inf][<?= $tax_name ?>][value]" value="0" required="" minlenght="1" maxlenght='6'>

                                          </div>
                                       </div>
                                 <?php }
                                 } ?>

                              </div>
                              <div class="clearfix"></div>

                              <!-- <div class="col-xs-12 nopad">
                                 <div class="col-md-12 form-group nopad">
                                    <div class="panel panel-default">
                                       <div class="panel-heading">Seat Range Prices<span style="color:red" id="aircraft_details"></span></div>
                                       <div class="panel-body">
                                          <br>
                                          <div class="row">
                                             <div class="table-repsonsive">
                                                <span id="error"></span>
                                                <table class="table table-bordered" id="seat_price">
                                                   <tr>
                                                      <th>From Seat Range</th>
                                                      <th>To Seat Range</th>
                                                      <th>Price (USD)</th>
                                                      <th style="text-align: right;"><button type="button" class="btn btn-success btn-sm add_seat_price size1" style="text-align: center;"><span class="glyphicon glyphicon-plus "></span></button></th>
                                                   </tr>
                                                </table>
                                             </div>
                                          </div>

                                       </div>
                                    </div>
                                 </div>
                              </div> -->
                           </div>
                        </div>

                        <div class="clearfix"></div>
                     </div>
            </div>
         </div>


         <div class="col-xs-6">
            <div class="org_row">
               <div class="radio">
                  <label for="value_type" class="col-sm-6 control-label padd">Extra Baggage Price<span class="text-danger">*</span></label>
                  <input type="text" class="col-sm-3 wdt10 form-control numeric" value='0' placeholder="10" name="baggage[extra_baggage_price]" minlength="1" maxlength="5" required />USD./kg
               </div>
            </div>
         </div>
         <div class="col-xs-6">
            <div class="org_row">
               <div class="radio">
                  <label for="value_type" class="col-sm-6 control-label padd">Extra Baggage Limit<span class="text-danger">*</span></label>
                  <input type="text" class="col-sm-3 wdt10 form-control numeric" value='0' placeholder="10" name="baggage[extra_baggage_limit]" minlength="1" maxlength="5" required />kg
               </div>
            </div>
         </div>
         <div class="col-xs-6">
            <div class="org_row">
               <div class="radio">
                  <label for="value_type" class="col-sm-6 control-label padd">Checkin Baggage Price And Limit<span class="text-danger">*</span></label>
                  <input type="text" class="col-sm-3 wdt10 form-control numeric" value='0' placeholder="10" name="baggage[checkin_baggage_price]" minlength="1" maxlength="5" required />USD
                  <input type="text" class="col-sm-3 wdt10 form-control numeric" value='0' placeholder="10" name="baggage[checkin_baggage_kg]" minlength="1" maxlength="5" required />Kg
               </div>
            </div>
         </div>
         <div class="col-xs-6">
            <div class="org_row">
               <div class="radio">
                  <label for="value_type" class="col-sm-6 control-label padd">Free Hand Baggage <span class="text-danger">*</span></label>
                  <input type="text" class="col-sm-3 wdt10 form-control numeric" value='0' placeholder="10" name="baggage[hand_baggage_price]" minlength="1" maxlength="5" required />Kg
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="col-xs-12 nopad extra_service_privillege">
      <legend class="sm_titl">Extra Service Privillege</legend>
      <div class="org_row">

         <div class="col-xs-4">
            <div class="org_row">
               <label class="radio-inline">
                  <input type="checkbox" class="check" name="show_baggage" checked="checked" value="1"> Show Baggage
               </label>
            </div>
         </div>

         <div class="col-xs-4">
            <div class="org_row">
               <label class="radio-inline">
                  <input type="checkbox" class="check" name="show_meals" checked="checked" value="1"> Show Meals
               </label>
            </div>
         </div>

         <div class="col-xs-4">
            <div class="org_row">
               <label class="radio-inline">
                  <input type="checkbox" class="check" name="show_seat" checked="checked" value="1"> Show Seat
               </label>
            </div>
         </div>

      </div>
   </div>

   </div>


   </fieldset>





   <div class="col-xs-12 col-sm-12">
      <p id='error'></p>
      <div class="clearfix col-md-offset-1">
         <button class="btn btn-sm btn-success pull-right save" id='save' type="button">Save</button>
      </div>
   </div>
   </form>
   </fieldset>

   </div>
   </div>
   <!-- PANEL WRAP END -->
   </div>
   </div>
   <!-- /.row -->
</section>

<script type="text/javascript">
   $(document).ready(function() {
      $("#pro-image").on("change", function() {
         let img_sel = document.querySelector("#pro-image");
         let prev_div = '';
         let image_count = img_sel.files;

         for (let i = 0; i < image_count.length; i++) {
            prev_div += `<img src="${ URL.createObjectURL(image_count[i]) }" width="100px" height="120"> `;

         }
         document.getElementById("preview_image").innerHTML = prev_div;
      });
      var nomonths = 1;
      var dformat = 'dd-mm-yy';
      $(".empty_leg").css("display", "none")
      $(".arrival_div").css("display", "none")


      $('.fare_type').on('click', function() {
         var faretype = $(this).val();
         if (faretype == 0) {
            $(".charter").css("display", "block")
            $(".empty_leg").css("display", "none")
            $(".arrival_div").css("display", "none")



         } else if (faretype == 1) {
            $(".charter").css("display", "none")
            $(".arrival_div").css("display", "block")


            $(".empty_leg").css("display", "block")
         }

      })
      $("#dep_date1").datepicker({
         // minDate: 0,
         numberOfMonths: nomonths,
         dateFormat: dformat,
         onSelect: function(selected) {
            $("#arr_date1").datepicker("option", "minDate", selected)



            $("#dep_date1").removeClass('invalid-ip');
         }
      });



      $("#arr_date1").datepicker({
         numberOfMonths: nomonths,
         dateFormat: dformat,
         onSelect: function(selected) {
            $("#dep_date2").datepicker("option", "minDate", selected)
            $("#arr_date1").removeClass('invalid-ip');
            //$("#dep_date1_1").datepicker("option","minDate", selected) Jagannath to modify
         }
      });


      $("#dep_date0").datepicker({
         // minDate: 0,
         numberOfMonths: nomonths,
         dateFormat: dformat,
         onSelect: function(selected) {
            $("#arr_date0").datepicker("option", "minDate", selected)
         }
      });

      $("#arr_date0").datepicker({
         numberOfMonths: nomonths,
         dateFormat: dformat,
         onSelect: function(selected) {
            // $("#dep_date0").datepicker("option","minDate", selected)
            //$("#dep_date1_1").datepicker("option","minDate", selected) Jagannath to modify
         }
      });



      $("#dep_date2").datepicker({
         minDate: 0,
         numberOfMonths: nomonths,
         dateFormat: dformat,
         onSelect: function(selected) {
            $("#arr_date2").datepicker("option", "minDate", selected)
         }
      });

      $("#arr_date2").datepicker({
         minDate: 0,
         numberOfMonths: nomonths,
         dateFormat: dformat,
         onSelect: function(selected) {
            $("#dep_date1_1").datepicker("option", "minDate", selected)
         }
      });

      $("#dep_date1_1").datepicker({
         minDate: 0,
         numberOfMonths: 1,
         dateFormat: 'dd-mm-yy',
         onSelect: function(selected) {
            $("#arr_date1_1").datepicker("option", "minDate", selected)
         }
      });
      $("#arr_date1_1").datepicker({
         numberOfMonths: 1,
         dateFormat: 'dd-mm-yy',
         onSelect: function(selected) {
            $("#dep_date2_1").datepicker("option", "minDate", selected)
         }
      });
      $("#dep_date2_1").datepicker({
         minDate: 0,
         numberOfMonths: 1,
         dateFormat: 'dd-mm-yy',
         onSelect: function(selected) {
            $("#arr_date2_1").datepicker("option", "minDate", selected)
         }
      });
      $("#arr_date2_1").datepicker({
         numberOfMonths: 1,
         dateFormat: 'dd-mm-yy',
         onSelect: function(selected) {
            $("#dep_date2").datepicker("option", "maxDate", selected)
         }
      });

   });


   $(function() {
      $(".crs_is_triptype").change(function() {
         var is_triptyp = $('input[name=is_triptype]:checked').val();
         $("#returnflightinfo").hide(500);
         if (is_triptyp == 1) {
            $("#returnflightinfo").show(500);
         }
      });

      $(".crs_is_domestic").change(function() {
         var is_domestic = $('input[name=is_domestic]:checked').val();
         $("#returnflightinfo").hide(500);
         if (is_domestic == 1) {
            $("#triptype").show(500);
            $('#triptype').each(function() {
               $('input[type=radio]', this).get(0).checked = true;
            });
         } else {
            $("#triptype").hide(500);
         }

         $.ajax({
            url: app_base_url + "index.php/ajax/get_flight_crs_airline_list/" + is_domestic,
            success: function(result) {

               var obj = JSON.parse(result);

               var availableTags = new Array();
               var availableTags_obj = new Array();
               availableTags_obj = obj.airline;
               for (i = 0; i < obj.airline.length; i++) {
                  availableTags.push(availableTags_obj[i]);
               }

               $(".tags").autocomplete({
                  source: availableTags
               });

            }
         });
      });
      $(window).load(function() {
         var is_domestic = $('input[name=is_domestic]:checked').val();
         $.ajax({
            url: app_base_url + "index.php/ajax/get_flight_crs_airline_list/" + is_domestic,
            success: function(result) {

               var obj = JSON.parse(result);

               var availableTags = new Array();
               var availableTags_obj = new Array();
               availableTags_obj = obj.airline;
               for (i = 0; i < obj.airline.length; i++) {

                  availableTags.push(availableTags_obj[i]);
               }

               $(".tags").autocomplete({
                  source: availableTags
               });

            }
         });
      });
      // var availableTags_static = ["Air India(AI)","Jet Airways(9W)","Vistara(UK)","Indigo(6E)","Spicejet(SG)","Go Air(G8)","Air Asia(AK)","Tru Jet(2T)","Aeroflot(SU)","Aerosvit airlines(VV)","Air berlin(AB)","Air canada(AC)","Air china ltd(CA)","Air france(AF)","Air mauritius(MK)","Air newzealand(NZ)","Air sychelles(HM)","Alitalia(AZ)","Al nippon airways(NH)","American airline(AA)","Asiana airlines(OZ)","Austrian airlines(OS)","Bangkok airlines(PG)","Biman bangladesh(BG)","British midland(BD)","British airways(BA)","Bhutan airlines(B3)","Cathay pacific(CX)","China airlines(CI)","China eastern airlines(MU)","China southern airlines(CZ)","Delta air lines(DL)","Egypt air(MS)","El al israel airline(LY)","Emirates(EK)","Ethiopian airlines(ET)","Etihad airways(EY)","Finnair oyj(AJ)","Gulf air(GF)","Heli air(YO)","Japan airlines(JL)","Kenya airways(KQ)","Klm  dutch(KL)","Korean air(KE)","Kuwait airways(KU)","Lufthansa german air(LH)","Mahan air(W5)","Malindo air(OD)","Malaysia airlines(MH)","Mihin lanka(MJ)","Oman air(WY)","Phillipines airlines(PR)","Qantas airways ltd(QF)","Qatar airways(QR)","Regent airways(RX)","Royal bhutan airlines  (druk air)(KB)","Royal brunei airline(BI)","Royal jordanian(RJ)","Royal nepal airlines(RA)","Saudi arabian airline(SV)","Silk air(MI)","Singapore airlines(SQ)","South african airlines(SA)","Srilankan airlines(UL)","Swiss intl airlines(LX)","Thai airways international(TG)","Turkish airlines inc(TK)","Us airways(US)","United airlines(UA)","United airways bd(4H)","Vietnam airlines(VN)","Yemenia yemen airways(IY)","Air arabia(G9)","Indigo special 1(6E1)","Indigo special 2(6E2)","Indigo special 3(6EC)","Spice jet special 1(SG1)","Spice jet special 2(SG2)","Air Astana(KC)","Fly Dubai(FZ)","Virgin Australia(VA)","West Jet(WS)","Icelandair(FI)","Royal Air Maroc(AT)","Finnair(AY)","Aer Lingus(EI)","LOT Polish(LO)","Virgin Atlantic(VS)","Ukraine International Airlines(PS)","Air Serbia(JU)","Pakistan Airlines(PK)","Pegasus Airline(PC)","Middle East Airline(ME)","Eurowings(EW)","HongKong Airlines(HX)","Air Asia - Thai (FD)","Air Asia X(D7)","Air Asia - Indonesia (QZ)","Air Asia - India(I5)"]; 


   });

   $(document).ready(function() {
      $('.con_flight_1').hide();
      $('.con_flight_2').hide();
      $('.con_flight_3').hide();
      $('.con_flight_0').css('display', 'block');

      $(".getAiportlist").autocomplete({
         source: app_base_url + "index.php/flight/get_flight_suggestions",
         minLength: 2, //search after two characters
         autoFocus: true, // first item will automatically be focused
         select: function(event, ui) {
            //var inputs = $(this).closest('form').find(':input:visible');
            //inputs.eq( inputs.index(this)+ 1 ).focus();
         }
      });


      $("#destination_0").change(function() {
         var d = $('#destination_0').val();
         document.getElementById('origin_1').value = d;
         //$('#origin_1').attr("disabled","disabled");

      });
      $("#sel1").change(function() {
         //con_flight_2
         var flight_stops = $(this).val();
         if (flight_stops == parseInt(0)) {
            $('.con_flight_1').show();
            $('#field_set').removeAttr("disabled");
            //$('#field_set').attr("disabled","disabled"); Jagannath
            $('.con_flight_2').hide();
            addReq("con_flight_1");

            removeReq("con_flight_1_opt");
            removeReq("con_flight_2");
            //$('.con_flight_3').css('display','none');
            /* $('.con_flight_3').css('display','none');
            $('.con_flight_4').css('display','none'); */
         } else if (flight_stops == parseInt(1)) {
            $('.con_flight_1').show();
            $('#field_set').removeAttr("disabled");
            $('.con_flight_2').show();
            addReq("con_flight_1");
            addReq("con_flight_2");
            // destination_0
            //$('.con_flight_2').css('display','block');
            /*$('.con_flight_3').css('display','none');
            $('.con_flight_4').css('display','none'); */
         } else if (flight_stops == parseInt(2)) {
            $('.con_flight_1').show();
            $('#field_set').removeAttr("disabled");
            $('.con_flight_2').show();
            $('.con_flight_3').show();
            addReq("con_flight_1");
            addReq("con_flight_2");
            addReq("con_flight_3");
            // $('.con_flight_3').css('display','block');
            // $('.con_flight_4').css('display','none');
         } else if (flight_stops == parseInt(3)) {
            $('.con_flight_1').css('display', 'block');
            $('.con_flight_2').css('display', 'block');
            addReq("con_flight_1");
            addReq("con_flight_2");
            /*$('.con_flight_3').css('display','block'); */
            //$('.con_flight_4').css('display','block');
         }
      });
      $("#sel1_1").change(function() {
         //con_flight_2
         // var pstop = $("#sel1").val();
         // alert(pstop);
         var flight_stops = $(this).val();
         if (flight_stops == parseInt(0)) {
            $('.con_flight_1_2').show();
            $('#field_set').removeAttr("disabled");
            //$('#field_set').attr("disabled","disabled"); Jagannath
            $('.con_flight_2_2').hide();
            addReq("con_flight_1_2");
            removeReq("con_flight_2_2");
            //$('.con_flight_3').css('display','none');
            /* $('.con_flight_3').css('display','none');
            $('.con_flight_4').css('display','none'); */
         } else if (flight_stops == parseInt(1)) {
            $('.con_flight_1_2').show();
            $('#field_set').removeAttr("disabled");
            $('.con_flight_2_2').show();
            addReq("con_flight_1_2");
            addReq("con_flight_2_2");
            // destination_0
            //$('.con_flight_2').css('display','block');
            /*$('.con_flight_3').css('display','none');
            $('.con_flight_4').css('display','none'); */
         } else if (flight_stops == parseInt(2)) {
            $('.con_flight_1_2').css('display', 'block');
            $('.con_flight_2_2').css('display', 'block');
            addReq("con_flight_1_2");
            addReq("con_flight_2_2");
            // $('.con_flight_3').css('display','block');
            // $('.con_flight_4').css('display','none');
         } else if (flight_stops == parseInt(3)) {
            $('.con_flight_1_2').css('display', 'block');
            $('.con_flight_2_2').css('display', 'block');
            addReq("con_flight_1_2");
            addReq("con_flight_2_2");
            /*$('.con_flight_3').css('display','block'); */
            //$('.con_flight_4').css('display','block');
         }
      });


      $('#operating_air_code').change(function() {


         var airline_code_str = $(this).val();
         console.log(airline_code_str)
         var is_domestic = $('input[name=is_domestic]:checked').val();
         var regExp = /\(([^)]+)\)/;
         var airline_code = regExp.exec(airline_code_str);
         $.ajax({
            url: app_base_url + "index.php/flight/get_flight_fair_rule/" + airline_code[1] + "/" + is_domestic,
            success: function(result) {
               //console.log(result);
               var obj = JSON.parse(result);
               //console.log(obj);
               if (obj.status == true) {
                  CKEDITOR.instances['editor'].setData(obj.fare_rule);
                  //$('#editor').text(obj.fare_rule);
               } else {
                  CKEDITOR.instances['editor'].setData("");
                  //$('#editor').empty();
               }


            }
         });

      });





      $("#save").click(function(e) {

         e.preventDefault();
         var flight1_departure_airport = $('#flight1_departure_airport').val();
         // var flight1_arrival_airport = $('#flight1_arrival_airport').val();

         var departure_airport = '';
         var arrival_airport = '';
         if (flight1_departure_airport.includes("(")) {
            departure_airport = airport_code(flight1_departure_airport);
         }

         // if (flight1_arrival_airport.includes("(")) {
         //    arrival_airport = airport_code(flight1_arrival_airport);
         // }


         var deptime = 0;
         var arrtime = 0;
         var aircode = 0;

         var dep_date = $('#dep_date1').val();
         var arr_date = $('#arr_date1').val();
         var flight_num_1 = $('#flight_num_1').val();
         var departure_time1 = $('#departure_time1').val();
         var arrival_time1 = $('#arrival_time1').val();
         if (departure_time1.includes(":")) {
            deptime = 1;
         }
         if (arrival_time1.includes(":")) {
            arrtime = 1;
         }
         var aircraft = $("#aircraft").val();
         var operating_air_code = $("#operating_air_code").val();

         if (operating_air_code.includes("(")) {
            aircode = 1;
         }



         var days = [];
         $.each($("input[name='days[]']:checked"), function() {
            // alert($(this). val());
            days.push($(this).val());
         });

         var from_seat_range = [];
         $.each($("input[name='from_seat_range[]']"), function() {
            // alert($(this). val());
            from_seat_range.push($(this).val());
         });

         var to_seat_range = [];
         $.each($("input[name='to_seat_range[]']"), function() {
            // alert($(this). val());
            to_seat_range.push($(this).val());
         });

         var range_price = [];
         $.each($("input[name='range_price[]']"), function() {
            // alert($(this). val());
            range_price.push($(this).val());
         });



         var block_seat_from_range = [];
         $.each($("input[name='block_seat_from_range[]']"), function() {
            // alert($(this). val());
            block_seat_from_range.push($(this).val());
         });

         var block_seat_to_range = [];
         $.each($("input[name='block_seat_to_range[]']"), function() {
            // alert($(this). val());
            block_seat_to_range.push($(this).val());
         });

         //console.log(arrival_airport)
         //console.log(dff)


         // if (flight1_departure_airport == '' || departure_airport == '' || arrival_airport == '' || dep_date == '' || arr_date == '' || flight_num_1 == '' || departure_time1 == '' || arrival_time1 == '' || days.length == 0 || deptime == 0 || arrtime == 0 || aircode == 0) {

         //    $('#error').html('<span class="red">Enter All details</span>')
         //    return;

         // } else {
         $.ajax({
            url: app_base_url + 'index.php/ajax/check_flight/',
            type: 'post',
            data: {
               departure_airport: departure_airport,
               arrival_airport: arrival_airport,
               dep_date: dep_date,
               arr_date: arr_date,
               flight_num: flight_num_1,
               departure_time: departure_time1,
               arrival_time: arrival_time1,
               aircraft: aircraft,
               selected_days: days,
               from_seat_range: from_seat_range,
               to_seat_range: to_seat_range,
               block_seat_from_range: block_seat_from_range,
               block_seat_to_range: block_seat_to_range,
               range_price: range_price
            },
            success: function(data) {

               var x = JSON.parse(data)
               console.log(x)
               if (data == '') {
                  return false;
               } else {
                  if (x.sameflightnum_samedate == 1) {
                     $('#error').html('<span class="red">Same Aircraft/Flight Number/Departure for this Time/Date</span>')
                     //$('.save').prop('disabled', true);
                     return false;
                  } else if (x.sameorigin_samedate_sametime == 1) {
                     $('#error').html('<span class="red">Same Aircraft/Flight Number/Departure for this Time/Date</span>')
                     //$('.save').prop('disabled', true);   
                     return false;
                  } else if (x.sameorigin_samedate_sametime_sameaircraft == 1) {
                     $('#error').html('<span class="red">Same Aircraft/Flight Number/Departure for this Time/Date</span>')
                     //$('.save').prop('disabled', true);   
                     return false;
                  } else {
                     $('#seat_temp_id').val(x.seat_temp_id);
                     $('#error').html('')
                     $('.save').prop('disabled', false);



                     $('#addflight').submit();
                  }

               }


            }
         });

         // }


      });


      $("#aircraft").change(function() {
         var is_domestic = $(this).val();
         $.ajax({
            url: app_base_url + "index.php/flight/get_flight_crs_seats/" + is_domestic,
            success: function(result) {
               $('#seats').val(result)
            }
         });
      });



   });


   function airport_code(airport) {
      var c = airport.split('(');
      return String(c[1]).substr(0, 3);
   }


   //Added for seats start here

   $(document).on('click', '.add_seat_price', function(event) {
      event.preventDefault();
      var count = 0;
      var id;
      count += 1;

      var html = '';
      html += '<tr>';

      html += '<td><input required type="text" name="from_seat_range[]" placeholder="Eg : 1A" class="form-control from_seat_range mrgn_rmv" maxlength="3" minlength="2"/></td>';

      html += '<td><input required type="text" name="to_seat_range[]" placeholder="Eg : 6F" class="form-control to_seat_range  mrgn_rmv" maxlength="3" minlength="2"/></td>';

      html += '<td class="td_qty"><input required type="text" name="range_price[]" placeholder="Price" class="form-control range_price mrgn_rmv" /></td>';

      html += '<td><button type="button" name="remove" class="btn btn-danger btn-sm remove size1"><span class="glyphicon glyphicon-minus"></span></button></td></tr>';
      $('#seat_price').append(html);
   });

   $(document).on('click', '.block_seat', function(event) {
      event.preventDefault();
      var count = 0;
      var id;
      count += 1;

      var html = '';
      html += '<tr>';

      html += '<td><input required type="text" name="block_seat_from_range[]" placeholder="Eg : 1A" class="form-control block_seat_from_range mrgn_rmv" maxlength="3" minlength="2"/></td>';

      html += '<td><input required type="text" name="block_seat_to_range[]" placeholder="Eg : 6F" class="form-control block_seat_from_range  mrgn_rmv" maxlength="3" minlength="2"/></td>';

      html += '<td><button type="button" name="remove" class="btn btn-danger btn-sm remove size1"><span class="glyphicon glyphicon-minus"></span></button></td></tr>';
      $('#block_seat').append(html);
   });

   $(document).on('click', '.remove', function() {
      $(this).closest('tr').remove();
   });




   $(document).on('change', '#aircraft', function(event) {
      console.log("Flight ID : " + $(this).val());
      var flight_id = $(this).val();
      var seat_rows = 0;
      var seat_colums = 0;
      $.ajax({
         url: "<?php echo base_url(); ?>index.php/flight/get_seats",
         data: {
            flight_id: flight_id,
         },
         method: "POST",
         dataType: "json"
      }).done(function(data) {
         console.log(data);
         var flight_details = data.response.aircraft.data[0];

         seat_rows = flight_details.seat_row_count;
         var seating_capacity = flight_details.seating_capacity;
         var total_seat_capacity = seating_capacity;
         var seat_colums = flight_details.seat_coulumns;
         // $("#total_seat").val(''+total_seat_capacity);
         $('#aircraft_details,#aircraft_details1').html(' | Aircraft Details | Seat Rows :' + flight_details.seat_row_count + ' | Seat Colums : ' + flight_details.seat_coulumns + ' | Seat Capacity : ' + total_seat_capacity)
      });

   })




   //Seats End here

   function addReq(divCls) {
      $('.' + divCls + ' input').attr('required', 'required');
      $('.' + divCls + ' select').attr('required', 'required');
      $('.' + divCls + ' option').attr('required', 'required');
   }

   function removeReq(divCls) {
      $('.' + divCls + ' input').removeAttr('required');
      $('.' + divCls + ' select').removeAttr('required');
      $('.' + divCls + ' option').removeAttr('required');
   }
</script>
<script type="text/javascript">
   $(document).ready(function() {
      //$('.timepicker-24').wickedpicker({twentyFour: true, title: 'Time', showSeconds: false});
   });
</script>
<!-- <script>
   $('.timepicker-24').timepicker();
</script> -->