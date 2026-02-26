 <?php

$_datepicker = array(array('passport_datetime_from', PAST_DATE), array('passport_datetime_to', FUTURE_DATE), array('licence_datetime_from', PAST_DATE), array('licence_datetime_to', FUTURE_DATE), array('training_datetime_from', PAST_DATE), array('training_datetime_to', FUTURE_DATE));
$this->current_page->set_datepicker($_datepicker);
$this->current_page->auto_adjust_datepicker(array(array('passport_datetime_from', 'passport_datetime_to','licence_datetime_from','licence_datetime_to','training_datetime_from','training_datetime_to')));
?> 
<style>
.form-group.endo {
    padding-left: 2pc;
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
         <script src="https://cdn.rawgit.com/harvesthq/chosen/gh-pages/chosen.jquery.min.js"></script>
                  <link href="https://cdn.rawgit.com/harvesthq/chosen/gh-pages/chosen.min.css" rel="stylesheet"/>
       <div class="row">
      <!-- HTML BEGIN -->
      <div class="bodyContent">
         <div class="panel panel-primary">
            <div class="panel-heading">
               <div class="panel-title"><i class="fa fa-edit"></i> Add Pilot Details</div>
            </div>
            <form action="" class="form-horizontal" method="POST" autocomplete="off" onsubmit='return save()'>
                <div class="panel-body ad_flt">
                <fieldset>
                      <legend class="sm_titl"> Personal Information</legend>
                        <div class="col-xs-12 col-sm-12 fare_info nopad">
                          <strong class="padL5">Personal Details</strong>
                           <div class="form-group">
                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">First Name<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control required" value='<?php echo (!empty($pilot_list)) ? $pilot_list['first_name'] : ''; ?>' name="first_name" required maxlength='35' minlength='3'>
                                   </div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                  <label for="value_type" class="col-sm-4 control-label">Last Name<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control required "  placeholder="" name="last_name" value='<?php echo (!empty($pilot_list)) ? $pilot_list['last_name'] : ''; ?>' required="" maxlength='35' minlength='3'>
                                   </div>
                                 </div>
                                </div>
                              </div>
							  
							  <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Gender<span class="text-danger">*</span></label>
                                  <div class="col-sm-8">
								<label class="radio-inline">
								<input type="radio" class="crs_is_domestic" name="gender" <?php echo ($pilot_list['gender'] == 0) ? 'checked' : ''; ?>  <?php echo (isset($pilot_list)) ? '' : 'checked'; ?> value="0">Male</label>
								<label class="radio-inline">
								<input type="radio" class="crs_is_domestic" name="gender" <?php echo ($pilot_list['gender'] == 1) ? 'checked' : ''; ?>  <?php echo (isset($pilot_list)) ? '' : 'checked'; ?> value="1">Female</label>
								<!--label class="radio-inline">
								<input type="radio" class="crs_is_domestic" name="gender" <?php echo ($pilot_list['gender'] == 2) ? 'checked' : ''; ?>  <?php echo (isset($pilot_list)) ? '' : 'checked'; ?> value="2">Others</label-->
								</div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                  <label for="value_type" class="col-sm-4 control-label">Date Of Joining<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                    <input type="text" class="wdt25 form-control required doj" required  readonly id='doj' name="doj" value='<?php echo (!empty($pilot_list)) ? date("d-m-Y", strtotime( $pilot_list['doj'])) : ''; ?>'  >
                                   </div>
                                 </div>
                                </div>
                              </div>                           
                           </div>
                         </div>
                     
                        <div class="col-xs-12 col-sm-12 fare_info nopad">
                           <strong class="padL5">Contact Details</strong>
                         
                          <div class="form-group">
                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Email<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <input type="email" class="wdt25 form-control required "  placeholder="" name="email"   value='<?php echo (!empty($pilot_list)) ? $pilot_list['email'] : ''; ?>' required="" maxlength='35' minlength='11'>
                                   </div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Phone Number<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control required numeric"  placeholder="" name="phone_number"   value='<?php echo (!empty($pilot_list)) ? $pilot_list['phone_number'] : ''; ?>'  required="" maxlength='15' minlength='10'>
                                   </div>
                                 </div>
                                </div>
                              </div>

                              <div class="org_row">
                              <div class="col-sm-6">
                               <div class="radio">
                                 <label for="value_type" class="col-sm-4 control-label">Current Address<span class="text-danger">*</span></label>
                                 <div class="col-md-8">
                                   <input type="text" class="wdt25 form-control required "  placeholder="" name="current_address"   value='<?php echo (!empty($pilot_list)) ? $pilot_list['current_address'] : ''; ?>'  required=""  maxlength='50' minlength='3'>
                                 </div>
                               </div>
                              </div>

                              <div class="col-sm-6">
                               <div class="radio">
                                 <label for="value_type" class="col-sm-4 control-label">Permanent Address<span class="text-danger">*</span></label>
                                 <div class="col-md-8">
                                   <input type="text" class="wdt25 form-control required "  placeholder="" name="permanent_address"   value='<?php echo (!empty($pilot_list)) ? $pilot_list['permanent_address'] : ''; ?>'  required=""  maxlength='50' minlength='3'>
                                 </div>
                               </div>
                              </div>
                            </div>

                            <div class="org_row">
                              
                              
                              <div class="col-sm-6">
                               <div class="radio">
                                 <label for="value_type" class="col-sm-4 control-label">Emergency Phone No<span class="text-danger">*</span></label>
                                 <div class="col-md-8">
                                   <input type="text" class="wdt25 form-control required numeric"  placeholder="" name="emergency_contact"   value='<?php echo (!empty($pilot_list)) ? $pilot_list['emergency_contact'] : ''; ?>'  required="" maxlength='15' minlength='10'>
                                 </div>
                               </div>
                              </div>
                              
                                <div class="col-sm-6">
                               <div class="radio">
                                 <label for="value_type" class="col-sm-4 control-label">Relationship<span class="text-danger">*</span></label>
                                 <div class="col-md-8">
                                   <input type="text" class="wdt25 form-control required "  placeholder=" Relationship with Emergency contact" name="relationship"   value='<?php echo (!empty($pilot_list)) ? $pilot_list['relationship'] : ''; ?>'  required="" maxlength='10' minlength='3'>
                                 </div>
                               </div>
                              </div>
                              
                               <div class="col-sm-6">
                               </div>
                             
                             
                              <div class="col-sm-6">
                               <div class="radio">
                                 <label for="value_type" class="col-sm-4 control-label">Relative's Name<span class="text-danger">*</span></label>
                                 <div class="col-md-8">
                                   <input type="text" class="wdt25 form-control required "  placeholder="" name="person_name"   value='<?php echo (!empty($pilot_list)) ? $pilot_list['person_name'] : ''; ?>'  required="" maxlength='35' minlength='3'>
                                 </div>
                               </div>
                              </div>

                              
                              
                            </div>


                           </div>
                         </div>

                         <div class="col-xs-12 col-sm-12 fare_info nopad">
                           <strong class="padL5">Employment Details</strong>
                         
                          <div class="form-group">
                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Designation<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control required "  placeholder="" name="designation"   value='<?php echo (!empty($pilot_list)) ? $pilot_list['designation'] : ''; ?>'  required="" >
                                   </div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Department</label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control "  placeholder="" name="department"  value='<?php echo (!empty($pilot_list)) ? $pilot_list['department'] : ''; ?>' >
                                   </div>
                                 </div>
                                </div>
                              </div>

                              <div class="org_row">
                              <div class="col-sm-6">
                               <div class="radio">
                                 <label for="value_type" class="col-sm-4 control-label">Employee Code<span class="text-danger">*</span></label>
                                 <div class="col-md-8">
                                   <input type="text" class="wdt25 form-control required  "  placeholder="" name="emp_code"  value='<?php echo (!empty($pilot_list)) ? $pilot_list['emp_code'] : ''; ?>'  required="" >
                                 </div>
                               </div>
                              </div>

                              <div class="col-sm-6">
                               <div class="radio">
                                 <label for="value_type" class="col-sm-4 control-label">Emp ID number<span class="text-danger">*</span></label>
                                 <div class="col-md-8">
                                   <input type="text" class="wdt25 form-control required "  placeholder="" name="emp_id"   value='<?php echo (!empty($pilot_list)) ? $pilot_list['emp_id'] : ''; ?>' required=""  >
                                 </div>
                               </div>
                              </div>
                            </div>

                            

                           </div>
                         </div>

                         <div class="col-xs-12 col-sm-12 fare_info nopad">
                           <strong class="padL5">Passport Details</strong>
                         
                          <div class="form-group">
                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Number<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control required"   name="passport_number"  value='<?php echo (!empty($pilot_list)) ? $pilot_list['passport_number'] : ''; ?>'  required >
                                   </div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Issue Date<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
								     <input type="text" class="wdt25 form-control required trainingissueDateRow " required readonly  name="passport_issue_date" id='passport_issue_date' value='<?php echo (!empty($pilot_list)) ? date("d-m-Y", strtotime( $pilot_list['passport_issue_date'])) : ''; ?>'   >
                                   </div>
                                 </div>
                                </div>
                              </div>

                              <div class="org_row">
                              <div class="col-sm-6">
                               <div class="radio">
                                 <label for="value_type" class="col-sm-4 control-label">Date of Expiry<span class="text-danger">*</span></label>
                                 <div class="col-md-8">
                                   <input type="text" class="wdt25 form-control required trainingexpDateRow "  required readonly id='passport_expiry_date' name="passport_expiry_date"  value='<?php echo (!empty($pilot_list)) ? date("d-m-Y", strtotime( $pilot_list['passport_expiry_date']))  : ''; ?>'    >
                                 </div>
                               </div>
                              </div>

                              <div class="col-sm-6">
                               <div class="radio">
                                 <label for="value_type" class="col-sm-4 control-label">Issue Place and Country<span class="text-danger">*</span></label>
                                 <div class="col-md-8">
                                   <input type="text" class="wdt25 form-control required"  placeholder="" name="passport_issue_country"  value='<?php echo (!empty($pilot_list)) ? $pilot_list['passport_issue_country'] : ''; ?>'  required="" />
                                 </div>
                               </div>
                              </div>
                            </div>
                           </div>
                         </div>


                        <div class="col-xs-12 col-sm-12 fare_info nopad">
                          <strong class="padL5">Past Experience Details</strong>
                           <div class="form-group">
                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Total Hours at joining</label>
                                   <div class="col-md-8">
                                    <input type="text" class="wdt25 form-control "  placeholder="" name="total_hours_joining"  value='<?php echo (!empty($pilot_list)) ? $pilot_list['total_hours_joining'] : ''; ?>'  />
                                   </div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Total Flying in last 365 Days</label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control "  placeholder="" name="total_flying_year"   value='<?php echo (!empty($pilot_list)) ? $pilot_list['total_flying_year'] : ''; ?>' />
                                   </div>
                                 </div>
                                </div>

                              </div>

                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Total Flying in last 30 Days</label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control  "  placeholder="" name="total_flying_month"  value='<?php echo (!empty($pilot_list)) ? $pilot_list['total_flying_month'] : ''; ?>' />
                                   </div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Total Flying in last 7 Days</label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control "  placeholder="" name="total_flying_week"  value='<?php echo (!empty($pilot_list)) ? $pilot_list['total_flying_week'] : ''; ?>'  />
                                   </div>
                                 </div>
                                </div>

                              </div>

                           </div>
                         </div>
                </fieldset >
                <fieldset>
                    <legend class="sm_titl">Licence Details</legend>
                        <div class="col-xs-12 col-sm-12 fare_info nopad">
                        <strong class="padL5"></strong>
                          
                          
						<?php foreach($pilot_licence as $pl){ ?>
                         <div class="form-group">
                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Select Licence Type<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <select class="form-control required licence" name="licence_type[]" required>      
                                      <option  value="">select</option>   
                                          <?php foreach($licence_list as $key => $ll) {
                                           $selected ='';
                                           
                                              if($ll['origin'] == $pl['licence_type'])  { $selected ='selected'; } ?>
                                               <option <?=$selected?> value="<?=$ll['origin']?>"><?=$ll['licence_name']?></option>  
                                        <?php }  ?>
                                      
                                                              
                                    </select>
                                   </div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Number<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                        <input type="hidden" name="licence_origin[]"  value="<?=$pl['origin'] ?>">
                                     <input type="text" class="wdt25 form-control required numeric"  placeholder="" name="licence_number[]" required  value='<?php echo (!empty($pilot_list)) ? $pl['licence_number'] : ''; ?>'   maxlength='10'>
                                   </div>
                                 </div>
                                </div>

                              </div>

                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Issue date<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
								   
                                     <input type="text" class="wdt25 form-control required licenceissueDateRow"   readonly  placeholder="" name="licence_issue_date[]" required value='<?php echo (!empty($pilot_list)) ? date("d-m-Y", strtotime($pl['licence_issue_date']))  : ''; ?>' >
                                   </div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Expiry date<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
								   
                                     <input type="text" class="wdt25 form-control required licenceexpDateRow"  readonly  placeholder="" name="licence_expiry_date[]" required value='<?php echo (!empty($pilot_list)) ? date("d-m-Y", strtotime($pl['licence_expiry_date']))  : ''; ?>' >
                                   </div>
                                 </div>
                                </div>

                              </div>
                                   <button type="button" class="btn btn-danger pull-right  delete_licence" data-origin="<?=$pl['origin'] ?>" >Remove</button>
                           </div>
                           
                           <?php } ?>
                         
                         
                         
                           <div class="form-group">
                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Select Licence Type<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <select class="form-control  licence"  name="licence_type[]">      
                                      <option value=''>select</option>   
                                          <?php foreach($licence_list as $key => $ll) { ?>
                                               <option  value="<?=$ll['origin']?>"><?=$ll['licence_name']?></option>  
                                        <?php }  ?>
                                      
                                                              
                                    </select>
                                   </div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Number<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                       
                                     <input type="text" class="wdt25 form-control  "  name="licence_number[]"  />
                                   </div>
                                 </div>
                                </div>

                              </div>

                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Issue date<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control  "   id='licence_datetime_from' readonly   name="licence_issue_date[]" />
									 
									
									 
                                   </div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Expiry date<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control "  id='licence_datetime_to'  readonly   name="licence_expiry_date[]" />
                                   </div>
                                 </div>
                                </div>

                              </div>

                           </div>
                           
                            <div class=" licence-2"></div>
                                  <button class="btn btn-info pull-right add_licence"  id='1'  type="button" >Add</button> 
                                  
                                  
                         </div>

                     </fieldset>
                    
                     <fieldset >
                      <legend class="sm_titl">Endorsements</legend>
                      <div class="col-xs-12 col-sm-12 fare_info nopad training" >  
                        <div class="form-group">
                                <div class="org_row">
                               
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Select Aircraft<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                       
                                        <select multiple class="form-control  chosen-select" name="aircraft[]"  >
                                         <option value=''>select</option>
                                     
                                  <?php foreach($aircraft_data as $ad) {
                                           $selected ='';
                                            $pilot_aircraft = explode(',',$pilot_list['aircraft']);
                                            if (in_array($ad['origin'], $pilot_aircraft)) { $selected ='selected'; } ?>
                                               <option <?=$selected?> value="<?=$ad['origin']?>"><?=$ad['type'].'-'.$ad['model']?></option>  
                                        <?php }  ?>
                                    </select>
                                    
                                     
                                   </div>
                                 </div>
                                </div>
                                <!--div class="col-sm-6">
                               <div class="radio">
                                  <label for="value_type" class="col-sm-4 control-label">Select Aircraft Type<span class="text-danger">*</span></label>
                                  <div class="col-md-8">
                                    <select class="form-control" name="training_type[]">
                                                <option >select</option><?php foreach($training_data as $td){    ?>         
                                                <option  value="<?=$td["origin"]?>"><?=$td["training_name"]?></option>    <?php } ?>
                                                </select>
                                  </div>
                               </div>
                            </div-->
                                <div class=" endorsements-2"></div>
                                  <!--<button class="btn btn-info pull-right add_endorsements"  id='1'  type="button" >Add</button--> 
                           </div>
                         </div>  
                         </div> 
                         
                      </fieldset >
                      <fieldset >
                      <legend class="sm_titl">Check and Training</legend>
                      
                      <div class="col-xs-12 col-sm-12 fare_info nopad training" >   
                    
                     <?php 
                     
                     foreach($pilot_training as $pl)
                     {
                         ?>
                         
                           <div class="form-group">
                                <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Select Training Type<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                    <input type="hidden" name="pilot_origin[]"  value="<?=$pl['origin'] ?>">
                                    <input type="hidden" name="pilot_id[]"  value="<?=$pl['pilot_id'] ?>">
                                     <select class="form-control required training" required name="training_type[]">
									 <option value=''>select</option>
                                     <?php foreach($training_data as $td){
                                         $selected ='';
                                       $selected = ($td['origin'] == $pl['training_type']) ? 'selected' : '';
                                       ?>                     
                                      <option <?=$selected ?> value="<?=$td['origin']?>"><?=$td['training_name']?></option>    
                                      <?php } ?>
                                    </select>
                                   </div>
                                 </div>
                                </div>
                              </div>
                              
                              
                              <div class="org_row">
                                
                                
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Issue Date<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                    <input type="text" class="wdt25 form-control required trainingissueDateRow"  required readonly  name="training_issue_date[]"  value="<?=date("d-m-Y", strtotime($pl['training_issue_date']))?>">
                                   </div>
                                 </div>
                                </div>
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Expiry Date<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                        <input type="text" class="wdt25 form-control required trainingexpDateRow" required  readonly  name="training_expiry_date[]" value="<?=date("d-m-Y", strtotime($pl['training_expiry_date'])) ?>">
                                        
                                   
                                   </div>
                                 </div>
                                </div>
                              </div>

                              <button type="button" class="btn btn-danger pull-right  delete_fare" data-table="pilot_training" data-origin="<?=$pl['origin'] ?>" >Remove</button>
                              
                           </div>
                        
                         
                         
                         <?php
                         
                         
                     }
                     
                     
                     ?> </div>
                        <div class="col-xs-12 col-sm-12 fare_info nopad training"  >
                          <strong class="padL5">Training</strong>
                           <div class="form-group">
                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Select Training Type<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <select class="form-control " name="training_type[]" >       
                                      <option value='' >select</option>
                                     <?php foreach($training_data as $td){    ?>                     
                                      <option  value="<?=$td['origin']?>"><?=$td['training_name']?></option>    
                                      <?php } ?>
                                    </select>
                                   </div>
                                 </div>
                                </div>
                              
                              </div>
                              <div class="org_row">
                                    <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Issue Date<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control "  id='training_datetime_from'  readonly  name="training_issue_date[]" >
                                     
                                   
                                   </div>
                                 </div>
                                </div>
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Expiry Date<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                    <input type="text" class="wdt25 form-control "  id='training_datetime_to'  readonly  name="training_expiry_date[]" >
                                   </div>
                                 </div>
                                </div>
                              </div>
                           </div>
                         </div>
                         <div class="col-xs-12 col-sm-12 fare_info nopad training-2"></div>
                         
                         <br><br><br><br>
                         <button class="btn btn-info pull-right add_training"  id='1'  type="button" >Add</button> 
                     </fieldset>
                      <fieldset>
                      <legend class="sm_titl"> Home Base</legend>
                        <div class="col-xs-12 col-sm-12 fare_info nopad">
                           <div class="form-group">
                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Select Airport Name<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                   <input type="text" autocomplete="off" placeholder="Search" id="autocomplete" required value='<?php echo (!empty($pilot_list)) ? $pilot_list['airport_name'] : ''; ?>'   class="form-control required ui-autocomplete-input" >
                                    <input type="hidden" id="selectuser_id" name="airport_name" value='<?php echo (!empty($pilot_list)) ? $pilot_list['airport_code'] : ''; ?>' >
                               
                                    <input type="hidden" value='<?php echo (!empty($pilot_list)) ? $pilot_list['origin'] : '0'; ?>'  name="origin">                                    
                                   </div>
                                 </div>
                                </div>
                              </div>
                           </div>
                         </div>
                     </fieldset>
                     <div class="clearfix"></div>
                   <div class="col-xs-12 col-sm-12">                        
                    <div class="clearfix col-md-offset-1">
					<p id='err' style='color:red'></p>
                      <button class="btn btn-sm btn-success pull-right" type="submit">Submit</button>                     
                    </div>                     
                  </div>
                  </div>
                  </form>
                  </div>
                  </div>
                  </div>
<script>
function find_duplicate_in_array(arra1) {
        var object = {};
        var result = [];

        arra1.forEach(function (item) {
          if(!object[item])
              object[item] = 0;
            object[item] += 1;
        })

        for (var prop in object) {
           if(object[prop] >= 2) {
               result.push(prop);
           }
        }

        return result;

    }
function save()
{
		var arr = [];
		var lic = [];
		var train = [];
		var i=0;
		$( ".required" ).each(function( index ) {
			i=i+1
			if($(this).val())
			{
				//console.log($(this).val())
				arr.push($(this).val());
			}else{
				
				console.log($(this).attr('name'))
			}
		
		});
		
		
		$( ".licence" ).each(function( index ) {
			if($(this).val())
			{
				lic.push($(this).val());
			}
		
		});
		
			$( ".training" ).each(function( index ) {
			if($(this).val())
			{
				train.push($(this).val());
			}
		
		});
		
		console.log(i)
		console.log(arr)
		console.log(arr.length)
		console.log(train)
	if(i == arr.length)
		{
			var licence_error = find_duplicate_in_array(lic);
			var train_error = find_duplicate_in_array(train);
			console.log(licence_error)
			
			if(licence_error.length > 0)
			{
				$('#err').html('Cannot select same Licence.');				
				return false;
			}
			if(train_error.length > 0)
			{
				$('#err').html('Cannot select same Training.');				
				return false;
			}
			
			
			
			return true;
		}
		else{
			$('#err').html('Please fill all data');
			return false;
		}
}
$( document ).ready(function() {
  $('.add_training').click(function(){
     // $("#txtDateRow1").datepicker({ numberOfMonths:[1,2] });
       id =  $('.add_training').attr('id');
      nextid = parseInt(id)+1;
      $('.add_training').attr('id',nextid);
      nid= parseInt(nextid)+1;
      add = '<div class="form-group"><div class="org_row"><div class="col-sm-6"><div class="radio"><label for="value_type" class="col-sm-4 control-label">Select Training Type<span class="text-danger">*</span></label><div class="col-md-8"><select class="form-control required training" name="training_type[]">       <option value="">select</option><?php foreach($training_data as $td){    ?>                     <option  value="<?=$td["origin"]?>"><?=$td["training_name"]?></option>    <?php } ?></select></div></div></div></div><div class="org_row"><div class="col-sm-6"><div class="radio"><label for="value_type" class="col-sm-4 control-label">Issue Date<span class="text-danger">*</span></label><div class="col-md-8"><input type="text" class="wdt25 form-control required txtDateRow1" id="" readonly  name="training_issue_date[]" ></div></div></div><div class="col-sm-6"><div class="radio"><label for="value_type" class="col-sm-4 control-label">Expiry Date<span class="text-danger">*</span></label><div class="col-md-8"><input type="text" class="wdt25 form-control required txtDateRow2"  id="" readonly  name="training_expiry_date[]" ></div></div></div></div></div></div><button type="button" class="btn btn-danger pull-right  delete_fare" id="training-'+nextid+'" onclick="remove(this.id)")>Remove</button>';
      $(".training-"+nextid).append(add);
       ad = '<div class="training-'+nid+'"></div>';
       $( ad ).insertAfter( $(".training-"+nextid) );
  });
  
  
    $('.add_licence').click(function(){
      id =  $('.add_licence').attr('id');
      nextid = parseInt(id)+1;
      $('.add_licence').attr('id',nextid);
      nid= parseInt(nextid)+1;
      add = '<div class="form-group"><div class="org_row"><div class="col-sm-6"><div class="radio"><label for="value_type" class="col-sm-4 control-label">Select Licence Type<span class="text-danger">*</span></label><div class="col-md-8"><select class="form-control required licence" id="" name="licence_type[]">      <option value="">select</option>   <?php foreach($licence_list as $key => $ll) { ?><option value="<?=$ll["origin"]?>"><?=$ll["licence_name"]?></option>  <?php }  ?></select></div></div></div><div class="col-sm-6"><div class="radio"><label for="value_type" class="col-sm-4 control-label">Number<span class="text-danger">*</span></label><div class="col-md-8"><input type="text" class="wdt25 form-control required numeric"  placeholder="" name="licence_number[]"  value="<?php echo (!empty($pilot_list)) ? $pilot_list["licence_number"] : ""; ?>"  required="" ></div></div></div></div><div class="org_row"><div class="col-sm-6"><div class="radio"><label for="value_type" class="col-sm-4 control-label">Issue date<span class="text-danger">*</span></label><div class="col-md-8"><input type="text" class="wdt25 form-control required txtDateRow1"  readonly  placeholder="" name="licence_issue_date[]"  required=""  ></div></div></div><div class="col-sm-6"><div class="radio"><label for="value_type" class="col-sm-4 control-label">Expiry date<span class="text-danger">*</span></label ><div class="col-md-8"><input  maxlength="15" minlenght="5" type="text" class="wdt25 form-control required txtDateRow2"   readonly  placeholder="" name="licence_expiry_date[]" required=""></div></div></div></div></div><button type="button" class="btn btn-danger pull-right  delete_fare" id="licence-'+nextid+'" onclick="remove(this.id)")>Remove</button>';
      $(".licence-"+nextid).append(add);
      ad = '<div class="licence-'+nid+'"></div>';
      $( ad ).insertAfter( $(".licence-"+nextid) );
        
  });
  
  
    $('.add_endorsements').click(function(){
       id =  $('.add_endorsements').attr('id');
      nextid = parseInt(id)+1;
      $('.add_endorsements').attr('id',nextid);
      nid= parseInt(nextid)+1;
      add = '<div class="form-group endo"><div class="org_row"><div class="col-sm-6"><div class="radio"><label for="value_type" class="col-sm-4 control-label">Select Aircraft<span class="text-danger">*</span></label><div class="col-md-8"><select class="form-control required" id="" name="aircraft"><option value="">select</option><?php foreach($aircraft_data as $ad) {$selected ="";if($ad["origin"] == $pilot_list["aircraft"])  { $selected ="selected"; } ?><option <?=$selected?> value="<?=$ad["origin"]?>"><?=$ad['model']?></option><?php }  ?></select></div></div></div><!--div class="col-sm-6"><div class="radio"><label for="value_type" class="col-sm-4 control-label">Select Training Type<span class="text-danger">*</span></label><div class="col-md-8"><select class="form-control required" name="training_type[]"><option  value="">select</option><?php foreach($training_data as $td){    ?>         <option  value="<?=$td["origin"]?>"><?=$td["training_name"]?></option>    <?php } ?></select></div></div></div--></div></div><button type="button" class="btn btn-danger pull-right  delete_fare" id="endorsements-'+nextid+'" onclick="remove(this.id)")>Remove</button>';
      $(".endorsements-"+nextid).append(add);
       ad = '<div class="endorsements-'+nid+'"></div>';
       $( ad ).insertAfter( $(".endorsements-"+nextid) );
  });
  
  
});


function remove(x)
{  
   $('.'+x).remove();
}

</script>

    <script src='../jquery.js' type='text/javascript'></script>
    <link href='jquery-ui.min.css' rel='stylesheet' type='text/css'>
    <script src='jquery-ui.min.js' type='text/javascript'></script>
    <!-- Script -->
    <script type='text/javascript' >
    $( function() {
        url = app_base_url+"index.php/ajax/auto_suggest_airport_name";
        $( "#autocomplete" ).autocomplete({
            source: function( request, response ) {
                
                $.ajax({
                    url: url,
                    type: 'post',
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function( data ) {
                        response( data );
                    }
                });
            },
            select: function (event, ui) {
                $('#autocomplete').val(ui.item.label); // display the selected text
                $('#selectuser_id').val(ui.item.value); // save selected id to input
                return false;
            }
        });
        
        
       /* 	  $('.delete_fare').on('click', function(){
	      var result = confirm("Want to delete?");
        if (result) {
	 	var origin =  $(this).data('origin');
	 	    $.ajax({
		 		method:'get',
		 		url:app_base_url+'index.php/ajax/delete_pilot_training/'+origin,
		 		dataType: 'json',
		 		success:function(data){
		 			location.reload();
		 		    }
		 		});
	       }
	 }); */
        	  $('.delete_licence').on('click', function(){
	      var result = confirm("Want to delete?");
        if (result) {
	 	var origin =  $(this).data('origin');
		alert(origin);
	 	    $.ajax({
		 		method:'get',
		 		url:app_base_url+'index.php/ajax/delete_pilot_licence/'+origin,
		 		dataType: 'json',
		 		success:function(data){
					alert(origin);
		 			location.reload();
		 		    }
		 		});
	       }
	 });


    });
    function split( val ) {
      return val.split( /,\s*/ );
    } 
    function extractLast( term ) {
      return split( term ).pop();
    } 
        $( document ).ready(function() {
       $(".chosen-select").chosen({
  no_results_text: "Oops, nothing found!"
})
});
    $( document ).ready(function() {
		
		/* $(document).on('focus',".hasDatepicker", function(){
            $('.txtDateRow1').datepicker({  dateFormat: "dd-mm-yy" });
			
            });*/
			
			
			
         $(document).on('focus',".txtDateRow1", function(){
            $('.txtDateRow1').datepicker({  dateFormat: "dd-mm-yy", maxDate: 0 , changeMonth: true, changeYear: true, numberOfMonths:[1,2] });
			
            });
            
            $(document).on('focus',".txtDateRow2", function(){
            $('.txtDateRow2').datepicker({ dateFormat: "dd-mm-yy", minDate: 0 , changeMonth: true, changeYear: true,  numberOfMonths:[1,2] });
            });
            
            $(document).on('focus',".trainingissueDateRow", function(){
            $('.trainingissueDateRow').datepicker({dateFormat: "dd-mm-yy" , maxDate: 0 , changeMonth: true, changeYear: true,  numberOfMonths:[1,2] });
            });
            
            $(document).on('focus',".trainingexpDateRow", function(){
            $('.trainingexpDateRow').datepicker({dateFormat: "dd-mm-yy" ,  minDate: 0 , changeMonth: true, changeYear: true, numberOfMonths:[1,2] });
            });
            
            
              $(document).on('focus',".licenceissueDateRow", function(){
            $('.licenceissueDateRow').datepicker({ dateFormat: "dd-mm-yy", maxDate: 0 , changeMonth: true, changeYear: true, numberOfMonths:[1,2] });
            });
            
            $(document).on('focus',".licenceexpDateRow", function(){
            $('.licenceexpDateRow').datepicker({ dateFormat: "dd-mm-yy" , minDate: 0 , changeMonth: true, changeYear: true, numberOfMonths:[1,2] });
            });
            
            $(document).on('focus',".doj", function(){
            $('.doj').datepicker({ dateFormat: "dd-mm-yy" , maxDate: 0 , changeMonth: true, changeYear: true, numberOfMonths:[1,2] });
            });
            
     });
  </script>