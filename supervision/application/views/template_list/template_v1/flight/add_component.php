 <?php

$_datepicker = array(
//array('last_date_overhaul', FUTURE_DATE), 
array('warranty_expire', FUTURE_DATE),
array('manufacturing_datetime', PAST_DATE), 
array('induction_date', PAST_DATE), 
array('last_maintenance_datetime_to', PAST_DATE), 
array('maintenance_due_datetime_to', FUTURE_DATE), 
array('tbo_datetime_to', PAST_DATE), 
array('expiry_datetime_to', FUTURE_DATE), 
array('tns_datetime_to', PAST_DATE), 
array('tso_datetime_to', PAST_DATE), 
array('warranty_start', PAST_DATE),
//array('tt_datetime_to', PAST_DATE), 
array('totaltime', PAST_DATE), 
array('shelf_datetime_to', PAST_DATE));
$this->current_page->set_datepicker($_datepicker);
$this->current_page->auto_adjust_datepicker(array(array('manufacturing_datetime','totaltime', 'induction_date','last_maintenance_datetime_to','maintenance_due_datetime_to', 'warranty_start','tbo_datetime_to','expiry_datetime_to','tns_datetime_to','tso_datetime_to','shelf_datetime_to','warranty_expire')));
?>      
<style>
.error {
    border: 1px solid #cf2700 !important;
}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.14.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment-precise-range-plugin@1.3.0/moment-precise-range.js"></script>
 
 <div class="row">
      <div class="bodyContent">
        <form action="" class="form-horizontal" method="POST" autocomplete="off" onsubmit='return Validate()'>
         <div class="panel panel-primary">
            <div class="panel-heading">
               <div class="panel-title"><i class="fa fa-edit"></i> Add Component</div>
            </div>
             <div class="panel-body ad_flt">
			     <input  name="origin" type="hidden"   value='<?php echo (!empty($component)) ? $component['origin'] : '0'; ?>'>
                 <fieldset>
					<legend class="sm_titl"> Add Component 
						<?php echo (!empty($component)) ? '<a class="btn btn-info"  href="'. base_url().'index.php/flight/component_list" >Back</a>': ''; ?></legend>
                        <div class="col-xs-12 col-sm-12 fare_info nopad">
                           <div class="form-group">
						   
						     <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label"> Induction Date <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                     <input type="text" class="wdt25 form-control datepicker" required readonly id='induction_date' name="induction_date" onchange="calcdate('total_time')" value='<?php echo (!empty($component)) ? $component['induction_date'] : ''; ?>'>
                                     
                                    </div>
                                 </div>
                                </div>
								
								 <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label"> Induction <br>(Flight Hours Done) </label>
                                    <div class="col-sm-8">
									<input type="text" class="wdt25 form-control " name="induction_time" id="induction_time"   placeholder='hh:mm' maxlength='9' required="" autocomplete="off" value='<?php echo (!empty($component)) ? $component['induction_time'] : ''; ?>'>
                                    </div>
                                 </div>
                                </div>
                              </div>
							  
                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Component Type<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                       
                                       
                                    <select class="form-control" name="component_type" required>     
                                    <option value=''>select</option>
                                    <?php foreach($comp_type as $ct) {
                                           $selected ='';
                                        $selected = ($ct['origin'] == $component['component_type']) ? 'selected' : '';
                                    ?>
                                      <option <?=$selected?> value="<?=$ct['origin']?>"><?=$ct['component_type']?></option>                               
                                      <?php } ?>                           
                                    </select>
                                   </div>
                                 </div>
                                </div>
                                <div class="col-sm-6">
                                 <div class="radio">
                                  <label for="value_type" class="col-sm-4 control-label">Component Name<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control" name="component_name"  required maxlength='35' minlength='3' value='<?php echo (!empty($component)) ? $component['component_name'] : ''; ?>'>
                                   </div>
                                 </div>
                                </div>
                              </div>
								<div class="org_row">
									<div class="col-sm-6">
										<div class="radio">
											<label for="value_type" class="col-sm-4 control-label">Part Number<span class="text-danger">*</span></label>
											  <div class="col-sm-8">
												<input type="text" class="wdt25 form-control" name="part_number" required maxlength='35'  value='<?php echo (!empty($component)) ? $component['part_number'] : ''; ?>'>
											  </div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="radio">
											<label for="value_type" class="col-sm-4 control-label">Serial Number</label>
										<div class="col-sm-8">
											<input type="text" class="wdt25 form-control" name="serial_number" maxlength='35'  required="" value='<?php echo (!empty($component)) ? $component['serial_number'] : ''; ?>'/>
										</div>
										</div>
									</div>
								</div>
                              
								<div class="org_row">
                                    <div class="col-sm-6">
										<div class="radio">
											<label for="value_type" class="col-sm-4 control-label ">Date of Manufacturing<span class="text-danger">*</span></label>
											<div class="col-sm-8">
												<input type="text" required class="wdt25 form-control datepicker calcuatedate" data-get_date='manufacturing_date'  data-lable='time_since_new' id="manufacturing_date" readonly placeholder='Manufactured Date' name="manufacturing_date" onchange="calcdate('time_since_new')" value='<?php echo (!empty($component)) ? $component['manufacturing_date'] : ''; ?>'>
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="radio">
											<label for="value_type" class="col-sm-4 control-label"> Shelf Life Date<span class="text-danger">*</span> </label>
												<div class="col-sm-8">
													<input type="text" class="wdt25 form-control datepicker calcuatedate" data-get_date='shelf_life_date' required data-lable='shelf_life_due' id="shelf_life_date" readonly name='shelf_life_date' value='<?php echo (!empty($component)) ? $component['shelf_life_date'] : ''; ?>'>
												 
												</div>
										</div>
									</div>
								</div>
							  
							  
							   
							  
							 
									
                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Date of Warranty Start</label>
                                    <div class="col-sm-8">
                                     <input type="text" class="wdt25 form-control datepicker" readonly id='warranty_start' name="warranty_start" value='<?php echo (!empty($component)) ? $component['warranty_start'] : ''; ?>'>
                                    </div>
                                 </div>
                                </div>
                                <div class="col-sm-6">
                                 <div class="radio">
                                     <label for="value_type" class="col-sm-4 control-label">Warranty Expiry<span class="text-danger">*</span></label>
                                       <div class="col-sm-8">
                                        <input type="text" class="wdt25 form-control datepicker" readonly  id='warranty_expire' name="warranty_expire" required="" value='<?php echo (!empty($component)) ? $component['warranty_expire'] : ''; ?>'>
                                       </div>
                                 </div>
                                </div> 
                              </div>
							  
							  
							  
							  
							   <div class="org_row">
                                    <div class="col-sm-6">
										<div class="radio"><!--last_date_overhaul-->
											<label for="value_type" class="col-sm-4 control-label">Last date of Overhaul<span class="text-danger">*</span></label>										 
											<div class="col-sm-8">
												<input type="text" class="wdt25 form-control datepicker calcuatedate" data-get_date='date' required  data-lable='time_since_overhaul' id="last_date_overhaul" readonly placeholder='' name="last_date_overhaul" value='<?php echo (!empty($component)) ? $component['last_date_overhaul'] : ''; ?>'>
											</div>
										</div>
									</div>
									
									<div class="col-sm-6">
										<div class="radio">
											<label for="value_type" class="col-sm-4 control-label">Time of Overhaul<span class="text-danger">*</span></label>
											<div class="col-sm-8">
												<input type="text" class="wdt25 form-control time " data-get_date='tbo_date' data-lable='tbo_datetime_to' id="time_of_overhaul" name="time_of_overhaul"  placeholder='hh:mm' required maxlength='9' required autocomplete="off" value='<?php echo (!empty($component)) ? $component['time_of_overhaul'] : ''; ?>'>
											</div>
										</div>
									</div> 
									
									
                                </div>
								
								
								
                               <div class="org_row">                               
									<div class="col-sm-6">
										<div class="radio">
											<label for="value_type" class="col-sm-4 control-label">Overhaul due date<span class="text-danger">*</span></label>                                     
												<div class="col-sm-8">
													<input type="text" class="wdt25 form-control datepicker calcuatedate" data-get_date='tbo_date'  data-lable='tbo_datetime_too' id="tbo_date" onchange="calcdate('overhaul_due_days')" readonly name="tbo_date"  required value='<?php echo (!empty($component)) ? $component['tbo_date'] : ''; ?>'>                                  
												</div>   
										</div>
									</div>
									<div class="col-sm-6">
										<div class="radio">
											<label for="value_type" class="col-sm-4 control-label"> Overhaul Cycle <br>(Hours Cycle)<span class="text-danger">*</span> </label>
												<div class="col-sm-8">
													<input type="text" class="wdt25 form-control required" id="overhaul_cycle" required placeholder='hh:mm' name='overhaul_cycle' value='<?php echo (!empty($component)) ? $component['overhaul_cycle'] : ''; ?>'>
												 
												</div>
										</div>
									</div>
								</div>
                                <div class="org_row"> 
									<div class="col-sm-6">
										<div class="radio">
											<label for="value_type" class="col-sm-4 control-label">Last Maintenance date<span class="text-danger">*</span></label>
											<div class="col-sm-8">
												<input type="text" required class="wdt25 form-control datepicker" readonly placeholder='' name="last_maintenance_date" value='<?php echo (!empty($component)) ? $component['last_maintenance_date'] : ''; ?>'>
											</div>
										</div>
									</div> 
									
									<div class="col-sm-6">
										<div class="radio">
											<label for="value_type" class="col-sm-4 control-label"> Maintenance Cycle <br>(Hours Cycle) </label>
												<div class="col-sm-8">
													<input type="text" class="wdt25 form-control" id="maintenance_cycle" placeholder='hh:mm' name='maintenance_cycle' value='<?php echo (!empty($component)) ? $component['maintenance_cycle'] : ''; ?>'>
												 
												</div>
										</div>
									</div>
                                </div>
								<div class="org_row">
									<div class="col-sm-6">
										<div class="radio">
										<label for="value_type" class="col-sm-4 control-label ">Next Maintenance date<span class="text-danger">*</span></label>
											<div class="col-sm-8">
												<input type="text" required class="wdt25 form-control datepicker calcuatedate" data-lable='maintenance_rem_time' id="next_maintenance_date" readonly placeholder='' name="next_maintenance_date" value='<?php echo (!empty($component)) ? $component['next_maintenance_date'] : ''; ?>'>
											</div>                                       
                                       </div>
									</div>
									<div class="col-sm-6">
										<div class="radio">
											<label for="value_type" class="col-sm-4 control-label"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </label>
												<div class="col-sm-8">
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												 
												</div>
										</div>
									</div>
								</div>
								
								
								 <div class="org_row">
							    <div class="col-sm-6">
                                 <div class="radio">
                                     <label for="value_type" class="col-sm-4 control-label">Set Caution Limit<br>(In Days)<span class="text-danger">*</span></label>
                                       <div class="col-sm-8">
                                        <input type="text" class="wdt25 form-control"  name="set_caution_limit_date" required placeholder='Days'  value='<?php echo (!empty($component)) ? $component['set_caution_limit_date'] : ''; ?>'> 
                                        
                                       </div>
                                 </div>
                                </div> 
								
                             
                                <div class="col-sm-6">
                                 <div class="radio">
                                     <label for="value_type" class="col-sm-4 control-label">Set Caution Limit Hours<br>(In Hours)<span class="text-danger">*</span></label>
                                       <div class="col-sm-8">
                                        <input type="text" class="wdt25 form-control time" name="set_caution_limit_time" placeholder='hh:mm' maxlength='7'  required autocomplete="off" value='<?php echo (!empty($component)) ? $component['set_caution_limit_time'] : ''; ?>'>
                                       </div>
                                 </div>
                                </div> 
                              </div>
								
								
								
                              </div>
                              </div>
                         </div>
                             
                              <div style='border-bottom: 1px solid #44383852'>&nbsp;&nbsp;&nbsp;</div>
                               <div class="row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label "> Time Since New <br>(In Days)</label>
                                    <div class="col-sm-8">
                                     
                                     <input type="text" class="wdt25 form-control " id="time_since_new"  name="time_since_new"  readonly required="" value='<?php echo (!empty($component)) ? $component['time_since_new'] : ''; ?>'>
                                    </div>
                                 </div>
                                </div>
								<div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label"> Time Since New <br>(In Hours) </label>
                                    <div class="col-sm-8">
                                     
                                     <input type="text" class="wdt25 form-control" id="" name="total_time" required readonly value='<?php echo (!empty($component)) ? $component['total_time'] : ''; ?>'>
                                    </div>
                                 </div>
                                </div>
                              </div>
                                    <div class="row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label "> Shelf Life due <br>(In Days)</label>
                                    <div class="col-sm-8">
									
									 <?php 
										$date1=date_create();
										$date2=date_create($component['shelf_life_due']);
										$diff=date_diff($date1,$date2);
										$rem = $diff->format("%R%a");
										if($rem < 0 && !empty($component))
										{?>
                                     <input type="text" class="wdt25 form-control" id="shelf_life_due" name="shelf_life_due"  readonly >
									 <span style="color:red">DUE day has passed</span>
											<?php  }else{ ?>
									  <input type="text" class="wdt25 form-control" id="shelf_life_due" name="shelf_life_due" required readonly value='<?php echo (!empty($component)) ? $component['shelf_life_due'] : ''; ?>'>
										<?php } ?>
                                    </div>
                                 </div>
                                </div>
                              </div>

							  
                               <div class="row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label "> Time Since Overhaul<br>(In Days) </label>
                                    <div class="col-sm-8">                                     
                                    <input type="text" class="wdt25 form-control" id="time_since_overhaul" name="time_since_overhaul"  required readonly value='<?php echo (!empty($component)) ? $component['time_since_overhaul'] : ''; ?>'>
                                    </div>
                                 </div>
                                </div>
								    <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label "> Time Since Overhaul<br>(In Hours) </label>
                                    <div class="col-sm-8">
                                     <input type="text" class="wdt25 form-control " id="time_since_overhaul_hours"  name="time_since_overhaul_hours"  readonly required="" value='<?php echo (!empty($component)) ? $component['time_since_overhaul_hours'] : ''; ?>'>
                                    </div>
                                 </div>
                                </div>
                              </div>
                               
                                <div class="row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label "> Overhaul due <br>(In Days) </label>
                                    <div class="col-sm-8">									 
									 <?php 
										$date1=date_create();
										$date2=date_create($component['tbo_date']);
										$diff=date_diff($date1,$date2);
										$rem = $diff->format("%R%a");
										if($rem < 0 && !empty($component)){	
										?>
										<input type="text" class="wdt25 form-control" id="tbo_datetime_too" name="overhaul_due_days"  readonly >
										<span style="color:red">DUE day has passed</span>
										<?php
											 }else{
										?>
										<input type="text" class="wdt25 form-control" id="tbo_datetime_too" name="overhaul_due_days" required readonly value='<?php echo (!empty($component)) ? $component['overhaul_due_days'] : ''; ?>'>
										<?php
										}
									 ?>
                                    </div>
                                 </div>
                                </div>
								
								<div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label "> Overhaul due <br>(In Hours)</label>
                                    <div class="col-sm-8">
										
										
										
										<?php
										$t1 = EXPLODE(":", $component['set_caution_limit_time']);
										$h1 = 0;
								//debug($t1);	
								if($t1[0] !=''){
								    	$h1 = $t1[0];
								}
						 
							//debug($h1);
							IF (ISSET($t1[1])) { $m1 = $t1[1]; } ELSE { $m1 = "00"; } 
							$set_caution_limit_time = ($h1 * 60) +  $m1;

		
							$t = EXPLODE(":", $component['overhaul_due_hours']); 
							$h = 0;
								if($t[0] !=''){
								    	$h = $t[0];
								}
						
							IF (ISSET($t[1])) { $m = $t[1]; } ELSE { $m = "00"; } 
							$overhaul_due_hours = ($h * 60) +  $m;
							
							if($overhaul_due_hours < 0 && !empty($component)){
								
								?>
							<input type="text" class="wdt25 form-control" id="overhaul_due_hours" name="overhaul_due_hours" readonly value=''>
								<span style="color:red">DUE hour has passed</span><?php
								 }else{
								?>
								<input type="text" class="wdt25 form-control" id="overhaul_due_hours" name="overhaul_due_hours"  required readonly value='<?php echo (!empty($component)) ? $component['overhaul_due_hours'] : ''; ?>'>
								<?php
							}
										
										?>
								    </div>
                                 </div>
                                </div>
								
                              </div>
                            
							  
							  
                          

							
							  
							<div class="row">
							    <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label "> Next Maintenance due <br>(In Days)</label>
                                    <div class="col-sm-8">
                                   
                                    
									 <?php
									 $date3=date_create($component['next_maintenance_date']);
						$diff3=date_diff($date1,$date3);
						$rem3 = $diff3->format("%R%a");
						if($rem3 < 0 && !empty($component))
						{
							
							?><input type="text" class="wdt25 form-control" name='next_maintenance_due_days' id="maintenance_rem_time" readonly >
							<span style="color:red">DUE day has passed</span>
							<?php
							 }else{
							?>
							
							<input type="text" class="wdt25 form-control" name='next_maintenance_due_days' id="maintenance_rem_time" required readonly  value='<?php echo (!empty($component)) ? $component['next_maintenance_due_days'] : ''; ?>' >
							
							<?php
						}
									 
									 ?>
                                    </div>
                                 </div>
                                </div>
								
							    
								<div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Next Maintenance due <br>(In Hours)  </label>
                                    <div class="col-sm-8">
									 <?php
										$t1 = EXPLODE(":", $component['set_caution_limit_time']); 
										$h1 = 0;
										if($t1[0] !=''){
										    $h1 = $t1[0]; 
										}
										
										IF (ISSET($t1[1])) { $m1 = $t1[1]; } ELSE { $m1 = "00"; } 
										$set_caution_limit_time = ($h1 * 60) +  $m1;
										$tt = EXPLODE(":", $component_detail['next_maintenance_due_hours']);
										$hh = 0;
											if($tt[0] !=''){
										    $hh = $tt[0]; 
										}
										 
										IF (ISSET($tt[1])) { $mm = $tt[1]; } ELSE { $mm = "00"; } 
										 $next_maintenance_due_hours = ($hh * 60) +  $mm;
										if($next_maintenance_due_hours < 0  && !empty($component)){
											
								?>
								<input type="text" class="wdt25 form-control" id="next_maintenance_due_hours" name="next_maintenance_due_hours"  readonly >
								<span style="color:red">DUE hour has passed</span>
								<?php
											 }else{ ?>
								<input type="text" class="wdt25 form-control" id="next_maintenance_due_hours" name="next_maintenance_due_hours" required readonly value='<?php echo (!empty($component)) ? $component['next_maintenance_due_hours'] : ''; ?>'>
							<?php }		
										?>
										
										
                                    </div>
                                 </div>
                                </div>
							</div>
                     </fieldset>
                     <div class="clearfix"></div>
                   <div class="col-xs-12 col-sm-12 col-md-12">         
                    <div class="clearfix col-md-offset-1">
					<p id='err' style='color : red'></p>
                      <button class="btn btn-sm btn-success pull-right" type="submit">Submit</button>                     
                    </div>                     
                  </div>
                  </div>
                  </form>
                  </div>
                  </div>
                  </div>
                   
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link rel="stylesheet" href="/resources/demos/style.css">
    <script>
	  function changeDateFormat(date){
		var sdate = date.split('-')
		var dd =sdate[1]
		var mm =sdate[0]
		var yyyy = sdate[2]
		var result = dd + '-' + mm + '-' + yyyy;
		return result;
	  }
	$(".calcuatedate").on('change', function (){ // this function is only for Shelf lifedate and Tbo
		
		var lable = $(this).data('lable');
		var newtime = '00:00:00';		
		console.log(lable);		
		var today = $(this).val();
		var newdate =changeDateFormat(today);	
		
			var starts =moment(newdate+' '+newtime);
			var ends   = moment();
			var output = calcuatedate(starts,ends)
			$('#'+lable).val(output);
			
	});

	function calcuatedate(starts,ends){
		
		var duration = moment.duration(ends.diff(starts));
		var diff = moment.preciseDiff(starts, ends, true); 
	    var result = diff.years+' years '+diff.months+' months '+diff.days+' days ';
		return result;
	}
	  
	$('#induction_time').keyup(function(e) 
	{
		get_time = ($('input[name=induction_time]').val()) ? $('input[name=induction_time]').val() : 0;
		$('input[name=total_time]').val(get_time);
	});
	$('#time_of_overhaul').keyup(function(e) 
	{
		get_time = ($('input[name=time_of_overhaul]').val()) ? $('input[name=time_of_overhaul]').val() : 0;
		$('input[name=time_since_overhaul_hours]').val(get_time);
		
	});	
	$('#overhaul_cycle').keyup(function(e) 
	{
		get_time = ($('input[name=overhaul_cycle]').val()) ? $('input[name=overhaul_cycle]').val() : 0;
		$('input[name=overhaul_due_hours]').val(get_time);
		
	});
	$('#maintenance_cycle').keyup(function(e) 
	{
		get_time = ($('input[name=maintenance_cycle]').val()) ? $('input[name=maintenance_cycle]').val() : 0;
		$('input[name=next_maintenance_due_hours]').val(get_time);
		
	});
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	 var timevalid =0;
	 var yearvalid =0;
	 var monthvalid =0;
	 var dayvalid =0;
	 
	  $('.numberonly').keypress(function(e) {
		  if (e.which != 58 && e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
				return false;
			}
	  });
	  
	  $('.timevalid').keyup(function(e) 
		{
			var inputField = $(this).val();
			var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(inputField);
			if (isValid) {
				$(this).removeClass("error");
			
			} else {
				
				$(this).addClass("error");
			}
		});
	  $('.yearvalid').keyup(function(e) 
		{
			var inputField = $(this).val();
				 if (inputField.length > 3) {
					$(this).removeClass("error");
					var inputField = parseInt(inputField);
					yearvalid -= 1;
				} else {
					yearvalid += 1;
					$(this).addClass("error");
				}
		}); 
		
	  $('.monthvalid').keyup(function(e) 
		{
			var inputField = parseInt($(this).val());
			if (inputField < 13){			
				$(this).removeClass("error");
				monthvalid -= 1;
			} else {
				monthvalid += 1;
				$(this).addClass("error");
			}
		});
		
	  $('.dayvalid').keyup(function(e) 
		{
			var inputField = parseInt($(this).val());
			if (inputField < 32) {
				$(this).removeClass("error");
				dayvalid -= 1;
			} else {
				dayvalid += 1;
				$(this).addClass("error");
			}
			
		});
		
		
	

		
		
		
		
		function  Validate(){
			
			var nullable = 0;
				$( ".timevalid" ).each(function( index ) {
			var inputField = $(this).val();
			var id = $(this).attr('id');
			var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(inputField);
				if (isValid) {
					$(this).removeClass("error");
					
				} else {
					nullable += 1;
					console.log(id)
					$(this).addClass("error");
				}
			});
			
			console.log('nullable : '+nullable)
			
			
			$( ".yearvalid" ).each(function( index ) {
				var inputField = $(this).val();
				 if (inputField.length > 3) {
					$(this).removeClass("error");
					var inputField = parseInt(inputField);
				
				} else {
						nullable += 1;
					$(this).addClass("error");
				}
			});
			
			
			$( ".monthvalid" ).each(function( index ) {
				var inputField = parseInt($(this).val());
				if (inputField < 13){			
					$(this).removeClass("error");
					monthvalid -= 1;
				} else {
					monthvalid += 1;
					$(this).addClass("error");
				}
			});
		$( ".dayvalid" ).each(function( index ) {
			var inputField = parseInt($(this).val());
			if (inputField < 32) {
				$(this).removeClass("error");
				dayvalid -= 1;
			} else {
				dayvalid += 1;
				$(this).addClass("error");
			}			
		});
		
		
			
			
			if(nullable == 0){
				$('#err').html('');
				return true;
			}
			$('#err').html('Invalid data');
			return false;
		}
		
		
	$( function() {
		$( ".datepicker" ).datepicker({
			dateFormat: "dd-mm-yy", changeMonth: true, changeYear: true,
			numberOfMonths: 2,
			
		});
	} );

		var year = 0;
		var month = 01;
		var date = 01;
		var time = "00:00";
		
	function calculateTime(display,lable,value)
	{
		if(lable == 'Y'){year = value}
		if(lable == 'M'){month = value}
		if(lable == 'D'){date = value}
		if(lable == 'T'){time = value}
		//datehere = month+'/'+date+'/'+year;
		datehere = month+'/'+date+'/'+year+' '+time+':00';
		 if(display != 'maintenance_rem_time'){
			var date1= new Date(datehere);
			var date2= new Date();  
		 }else{
			var date1= new Date(datehere);//next_maintenance_date
			var last_main_year = ($('#last_maintenance_year').val()) ? $('#last_maintenance_year').val() : 0; 
			var last_main_mon = ($('#last_maintenance_month').val()) ? $('#last_maintenance_month').val() : 0;
			var last_main_date = ($('#last_maintenance_date').val()) ? $('#last_maintenance_date').val() : 0;
			var last_main_time = ($('#last_maintenance_time').val()) ? $('#last_maintenance_time').val() : 0;
			last_maintaince = last_main_mon+'/'+last_main_date+'/'+last_main_year+' '+last_main_time+':00';
			var date2= new Date(last_maintaince);  
		 }
		datetime = caltime(date1 , date2)
		$('#'+display).val(datetime)	
	}
	function caltime(d1,d2){
         date1 = d1;
         date2 = d2;
         var res = Math.abs(date1 - date2) / 1000;
         var numberOfDays = Math.floor(res / 86400);
		 var years = Math.floor(numberOfDays / 365);
		 var months = Math.floor(numberOfDays % 365 / 30);
		 var days = Math.floor(numberOfDays % 365 % 30);
		 var yearsDisplay = years > 0 ? years + (years == 1 ? " year" : " years") : "0 years";
		 var monthsDisplay = months > 0 ? months + (months == 1 ? " month" : " months") : "0 months";
		 var daysDisplay = days > 0 ? days + (days == 1 ? " day" : " days") : "0 days";
         var hours = Math.floor(res / 3600) % 24;        
         var minutes = Math.floor(res / 60) % 60;
         var datetime = yearsDisplay +' '+monthsDisplay+' '+daysDisplay+' '+hours+':'+minutes; 
		 return datetime;
	}
	function calcdate(place)
	{  
		get_date =  '';
		 if(place == 'time_since_overhaul')
		{
			get_date = ($('input[name=last_date_overhaul]').val()) ? $('input[name=last_date_overhaul]').val() : 0;
			get_time = '24:00';		
			$.ajax({
			url:app_base_url+'index.php/flight/calctime/',
			type: 'post',                    
			data: { get_date: get_date,get_time:get_time},
			success:function(data){ $('input[name='+place+']').val(data); }
			}); 
		} 
		else if(place == 'maintenance_due_h')
		{
			var year1 = $('#last_maintenance_year').val()
			var month1 = $('#last_maintenance_month').val()
			var date1 = $('#last_maintenance_date').val()
			var time1 = $('#last_maintenance_time').val()
			get_date1 = year1+'-'+month1+'-'+date1;
			get_time1 = time1;
			var year2 =  $('#next_maintenance_year').val()
			var month2 = $('#next_maintenance_month').val()
			var date2 =  $('#next_maintenance_date').val()
			var time2 =  $('#next_maintenance_time').val()
			get_date2 = year2+'-'+month2+'-'+date2;
			get_time2 = time2;
			$.ajax({
			url:app_base_url+'index.php/flight/calctwotime/',
			type: 'post',                    
			data: {get_date1: get_date1,get_time1:get_time1,get_date2: get_date2,get_time2:get_time2},
			success:function(data){
			$('input[name='+place+']').val(data);
			}
			}); 

			$.ajax({
			//method:'get',
			url:app_base_url+'index.php/flight/calctwodate/',
			type: 'post',                    
			data: {get_date1: get_date1,get_time1:get_time1,get_date2: get_date2,get_time2:get_time2},
			success:function(data){		 			
			$('input[name="maintenance_due_days"]').val(data);
			}
			});
			
		}
		else
		{
			if(place == 'time_since_new'){
				get_date = ($('input[name=manufacturing_date]').val()) ? $('input[name=manufacturing_date]').val() : 0;
			}	
			if(place == 'shelf_life_due'){
				  get_date = ($('input[name=shelf_life_date]').val()) ? $('input[name=shelf_life_date]').val() : 0;
			}
			if(place == 'overhaul_due_days'){
				get_date = ($('input[name=tbo_date]').val()) ? $('input[name=tbo_date]').val() : 0;
			}	  
			$.ajax({
			method:'get',
			url:app_base_url+'index.php/flight/calcdate/'+get_date,
			dataType: 'json',
			success:function(data){		 			
			//$('input[name='+place+']').val(data);
			}
			});
		}
	}
    </script>