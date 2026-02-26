 <?php
$_datepicker = array(array('created_datetime_from', PAST_DATE), array('created_datetime_to', FUTURE_DATE), array('created_datetime_to1', FUTURE_DATE));
$this->current_page->set_datepicker($_datepicker);
$this->current_page->auto_adjust_datepicker(array(array('created_datetime_from', 'created_datetime_to', 'created_datetime_to1')));
?> 
<style type="text/css">
a.act { cursor: pointer;} 
.table{margin-bottom: 0;}
.modal-footer { padding: 10px;}
  .toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
  .toggle.ios .toggle-handle { border-radius: 20px; }
</style>
<!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="panel panel-default"> 
		<!-- PANEL WRAP START -->
		<div class="panel-heading">
			<!-- PANEL HEAD START -->
			<div class="panel-title">
			 Dual Pilot Limitations
               
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
		<?php if(check_user_previlege('p67')){?>
		<?php if(count(@$flight_time_limit) == 0){ ?>
		<button type="button" class="btn btn-primary add_fare" data-type='limit' id="">Add Flight Time Limit</button>
		
		<?php } ?>
		<?php } ?>
		<div class="table-responsive">
			<!-- PANEL BODY START -->
				<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<thead>
				</thead><tbody>
				<tr>
					<td  rowspan="3"><i class="fa fa-sort-numeric-asc"></i> Flight Time<br> (in Hrs)</td>
					<td  scope="col" colspan="2">Daily (Day)</td>
					<td  scope="col" colspan="2">Daily (Night)</td>
					<td  scope="col" colspan="2">Last 7 consecutive days </td>
					<td  scope="col" colspan="2">Last 30 consecutive days</td>
					<td  scope="col" colspan="2">Last 365 consecutive days </td>
						<?php if(check_user_previlege('p67')){?><td>Action</td><?php } ?>
				</tr>
				<tr>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<?php if(check_user_previlege('p67')){?><td></td><?php } ?>
				</tr>
				<?php 
				if(count(@$flight_time_limit)>0){
				    $i=1;
				foreach($flight_time_limit as $key => $document_detail){ 
				?>
				<tr>
				    <td> <?=$document_detail['limit_limit']?></td>
				    <td> <?=$document_detail['caution_limit']?></td>					
					<td> <?=$document_detail['night_limit_limit']?></td>
				    <td> <?=$document_detail['night_caution_limit']?></td>	
				    <td> <?=$document_detail['limit_week']?></td>
				    <td> <?=$document_detail['caution_week']?></td>
				    <td> <?=$document_detail['limit_month']?></td>
				    <td> <?=$document_detail['caution_month']?></td>
				    <td> <?=$document_detail['limit_year']?></td>
				    <td> <?=$document_detail['caution_year']?></td>		

	<?php if(check_user_previlege('p67')){?>					
					<td>  <button type="button" class="btn btn-primary edit_flight_time_limit" 
					data-time_origin="<?=$flight_time_limit[0]['origin']?>" 
					data-flight_time_limit="<?=$flight_time_limit[0]['limit_limit']?>" 
					data-flight_time_caution="<?=$flight_time_limit[0]['caution_limit']?>" 
					
					data-night_flight_time_limit="<?=$flight_time_limit[0]['night_limit_limit']?>" 
					data-night_flight_time_caution="<?=$flight_time_limit[0]['night_caution_limit']?>" 
					
					data-flight_time_limit_week="<?=$flight_time_limit[0]['limit_week']?>" 
					data-flight_time_caution_week="<?=$flight_time_limit[0]['caution_week']?>" 
					data-flight_time_limit_month="<?=$flight_time_limit[0]['limit_month']?>" 
					data-flight_time_caution_month="<?=$flight_time_limit[0]['caution_month']?>" 
					data-flight_time_limit_year="<?=$flight_time_limit[0]['limit_year']?>" 
					data-flight_time_caution_year="<?=$flight_time_limit[0]['caution_year']?>" 
					>Edit</button>
					<button type="button" class="btn btn-danger delete_fare_new "  data-common="<?=$document_detail['origin']?>">Delete</button>
					</td>
	<?php } ?>
				</tr>
				<?php } }else{ ?>
				<tr><td colspan="12"> <strong>No Details Found.</strong></td></tr>				
				<?php }?>
				</tbody>
			</table>
		  </div>
		</div>
		<!-- PANEL BODY END -->

<div id="edit_flight_time_limit" class="modal" role="dialog">
  <div class="modal-dialog" style='width: 85%;'>

    <!-- Modal content-->
		<div class="modal-content" style='background-color: #fff;'>
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" id="title"> Edit Flight Time Limit  </h4>
			</div>
			<div class="modal-body action_details"  style='background-color: #fff;'>
				<div class="text-danger" id="err"></div>
      <form method="post" action="" id="meal_detail_frm">
      	<div class="col-xs-12 col-sm-12 fare_info nopad">
				<fieldset>
			   <legend class="sm_titl"> Cumulative flight time limit setup</legend>
			   <div class="col-xs-12 col-sm-12 fare_info nopad">
				  <div class="form-group">
					 <div class="org_row">
						 <div class="radio">
							
							<div class="col-sm-1"></div>
							<div class="col-sm-2">Daily (Day)</div>
							<div class="col-sm-2">Daily (Night)</div>
							<div class="col-sm-2">Last 7 consecutive days</div>
							<div class="col-sm-2">Last 30 consecutive days</div>
							<div class="col-sm-3">Last 365 consecutive days</div> 
						  </div>
						  <div class="radio">
						 
							<input type="hidden"  name="time_origin" value="0">
							<input type='hidden' name='flight' value='1'>
							<div class="col-sm-1"><label for="value_type" class="col-sm-2 control-label">Limit<span class="text-danger">*</span></label></div>
							<div class="col-sm-2">
							<input type="text" class="wdt25 form-control numeric" id='flight_time_limit' placeholder="Daily Limit" name="limit_limit" required maxlength='3'></div>
							
							<!------>
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='night_flight_time_limit' placeholder="Daily Limit" name="night_limit_limit" required="" value=""maxlength='3'></div>
							
							
							
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_time_limit_week' placeholder="Week Limit" name="limit_week" required="" value="" maxlength='3'></div>
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_time_limit_month' placeholder="Month Limit" name="limit_month" required="" value=""maxlength='3'></div>
							<div class="col-sm-3"><input type="text" class="wdt25 form-control numeric " id='flight_time_limit_year' placeholder="Year Limit" name="limit_year" required="" value=""maxlength='3'></div> 
						  </div>
						  <div class="radio">
							<div class="col-sm-1"><label for="value_type" class="col-sm-2 control-label">Caution<span class="text-danger">*</span></label></div>
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_time_caution' placeholder="Caution Daily Limit" name="caution_limit" required="" value=""maxlength='3'></div>
							<!------>
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='night_flight_time_caution' placeholder="Caution Daily Limit" name="night_caution_limit" required="" value=""maxlength='3'></div>
							
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_time_caution_week' placeholder="Week Caution" name="caution_week" required="" value=""maxlength='3'></div>
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_time_caution_month' placeholder="Month Caution" name="caution_month" required="" value=""maxlength='3'></div>
							<div class="col-sm-3"><input type="text" class="wdt25 form-control numeric " id='flight_time_caution_year' placeholder="Year Caution" name="caution_year" required="" value=""maxlength='3'></div> 
						  </div>
					   </div>
					</div>
				 </div>
			</fieldset>
			
			</div>
			</div>
			<div class="modal-footer">
      	<div class="col-xs-12 col-sm-12">
        <button type="submit" class="btn btn-primary" id="save" >Submit</button>
        </form>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>

		</div>
	</div>
</div>










































<div class="panel-body">
	<?php if(check_user_previlege('p67')){?>
		<?php if(count(@$flight_duty_period) == 0){ ?>
		<button type="button" class="btn btn-primary add_fare" data-type='duty' id="">Add Flight Duty Period</button>
		<?php } ?>
		<?php } ?>
		<div class="table-responsive">
			<!-- PANEL BODY START -->
				<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<thead>				
				</thead><tbody>
				<tr>
					<td rowspan="3" > <i class="fa fa-sort-numeric-asc"></i> Flight Duty Period<br> (in Hrs)</td>
					<td  scope="col" colspan="2">Daily (Day)</td>
					<td  scope="col" colspan="2">Daily (Night)</td>
					<td  scope="col" colspan="2">Last 7 consecutive days<br>Duty Period</td>
					<td  scope="col" colspan="2">Last 14 consecutive days<br>Duty Period</td>
					<td  scope="col" colspan="2">Last 28 consecutive days<br>Duty Period</td>					
						<?php if(check_user_previlege('p67')){?><td>Action</td><?php } ?>
				</tr>
				<tr>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td> 
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<?php if(check_user_previlege('p67')){?><td></td><?php } ?>
				</tr>
				<?php 
				
				if(count(@$flight_duty_period)>0){
				    $i=1;
				foreach($flight_duty_period as $key => $document_detail){ 
				?>
				<tr>
				    <td> <?=$document_detail['limit_limit']?></td>
				    <td> <?=$document_detail['caution_limit']?></td>
					
					<td> <?=$document_detail['night_limit_limit']?></td>
				    <td> <?=$document_detail['night_caution_limit']?></td>
					
					
				    <td> <?=$document_detail['limit_week']?></td>
				    <td> <?=$document_detail['caution_week']?></td>				    
				    <td> <?=$document_detail['limit_month']?></td>
				    <td> <?=$document_detail['caution_month']?></td>				    
				    <td> <?=$document_detail['limit_year']?></td>
				    <td> <?=$document_detail['caution_year']?></td>	
	<?php if(check_user_previlege('p67')){?>
					<td  scope="col">  <button type="button" class="btn btn-primary edit_flight_duty_period" 
					data-duty_origin="<?=$flight_duty_period[0]['origin']?>"
					data-flight_duty_limit="<?=$flight_duty_period[0]['limit_limit']?>"
					data-flight_duty_caution="<?=$flight_duty_period[0]['caution_limit']?>"
					data-night_flight_duty_limit="<?=$flight_duty_period[0]['night_limit_limit']?>"
					data-night_flight_duty_caution="<?=$flight_duty_period[0]['night_caution_limit']?>"
					
					
					data-flight_duty_limit_week="<?=$flight_duty_period[0]['limit_week']?>"
					data-flight_duty_caution_week="<?=$flight_duty_period[0]['caution_week']?>"
					data-flight_duty_limit_month="<?=$flight_duty_period[0]['limit_month']?>"
					data-flight_duty_caution_month="<?=$flight_duty_period[0]['caution_month']?>"
					data-flight_duty_limit_year="<?=$flight_duty_period[0]['limit_year']?>"
					data-flight_duty_caution_year="<?=$flight_duty_period[0]['caution_year']?>"
					>Edit</button>
					<button type="button" class="btn btn-danger delete_fare_new "  data-common="<?=$document_detail['origin']?>">Delete</button>
					
					</td>
	<?php } ?>
				</tr>
				<?php } }else{ ?> 
				<tr><td colspan="12"> <strong>No Details Found.</strong></td></tr>
				<?php }?>
				</tbody>
			</table>
		  </div>
		</div>
		<!-- PANEL BODY END -->
		
<div id="edit_flight_duty_period" class="modal" role="dialog">
  <div class="modal-dialog" style='width: 85%;'>

    <!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" id="title"> Edit Flight Time Limit </h4>
			</div>
			<div class="modal-body action_details" style='background-color: #fff;'>
				<div class="text-danger" id="err"></div> 
					<form method="post" action="" id="meal_detail_frm">
					<fieldset>
					   <legend class="sm_titl"> Flight Duty Period</legend>
					   <div class="col-xs-12 col-sm-12 fare_info nopad">
						  <div class="form-group">
							  <div class="org_row">
								 <div class="radio">
									<div class="col-sm-1"></div>
									<div class="col-sm-2">Daily (Day)</div>
									<div class="col-sm-2">Daily (Night)</div>
									<div class="col-sm-2">Last 7 consecutive days</div>
									<div class="col-sm-2">Last 14 consecutive days</div>
									<div class="col-sm-3">Last 28 consecutive days</div> 
								  </div>
									<div class="radio">
									<input type="hidden" name="duty_origin" value="0">
									<input type='hidden' name='flight' value='2'>
									<div class="col-sm-1"><label for="value_type" class="col-sm-2 control-label">Limit<span class="text-danger">*</span></label></div>
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_duty_limit'  placeholder="Daily Limit" name="limit_limit" required="" value=""maxlength='3'></div>
									
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='night_flight_duty_limit'  placeholder="Daily Limit" name="night_limit_limit" required="" value=""maxlength='3'></div>
									
									
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_duty_limit_week'  placeholder="Limit of Last 7 consecutive days" name="limit_week" required="" value=""maxlength='3'></div>
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_duty_limit_month'  placeholder="Limit of Last 14 consecutive days" name="limit_month" required="" value=""maxlength='3'></div>
									<div class="col-sm-3"><input type="text" class="wdt25 form-control numeric " id='flight_duty_limit_year'  placeholder="Limit of Last 28 consecutive days" name="limit_year" required="" value=""maxlength='3'></div> 
								  </div>
								  <div class="radio">
									<div class="col-sm-1"><label for="value_type" class="col-sm-2 control-label">Caution<span class="text-danger">*</span></label></div>
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_duty_caution'  placeholder="Daily Limit Caution" name="caution_limit" required="" value=""maxlength='3'></div>
									
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='night_flight_duty_caution'  placeholder="Daily Limit Caution" name="night_caution_limit" required="" value=""maxlength='3'></div>
									
									
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_duty_caution_week'  placeholder="Caution of Last 7 consecutive days" name="caution_week" required="" value=""maxlength='3'></div>
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_duty_caution_month'  placeholder="Caution of Last 14 consecutive days" name="caution_month" required="" value=""maxlength='3'></div>
									<div class="col-sm-3"><input type="text" class="wdt25 form-control numeric " id='flight_duty_caution_year'  placeholder="Caution of Last 28 consecutive days" name="caution_year" required="" value=""maxlength='3'></div> 
								  </div>
							   </div>
							</div>
						 </div>
					</fieldset>
			</div>
			<div class="modal-footer">
      	<div class="col-xs-12 col-sm-12">
        <button type="submit" class="btn btn-primary" id="save" >Submit</button>
     
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
</form>
		</div>
	</div>
</div>
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		

<div class="panel-body">
		<?php if(check_user_previlege('p67')){?>
		<?php if(count(@$dual_pilot_landings) == 0){ ?>
		<button type="button" class="btn btn-primary add_fare" data-type='dual_edit_landings' id="">Add Landings</button>
		<?php } ?>
		<?php } ?>
		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<tbody>
				<tr>
					<td rowspan="3" > <i class="fa fa-sort-numeric-asc"></i> Landings</td>		
					<td  scope="col" colspan="2">Day </td>
					<td  scope="col" colspan="2">Night</td>
						<?php if(check_user_previlege('p67')){?><td>Action</td><?php } ?>
				</tr>
				<tr>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<?php if(check_user_previlege('p67')){?><td></td><?php } ?>
				</tr>
				<?php 
				if(count(@$dual_pilot_landings)>0){
				    $i=1;
				foreach($dual_pilot_landings as $key => $document_detail){ 
				?>
				<tr>
				    <td> <?=$document_detail['limit_limit']?></td>
				    <td> <?=$document_detail['caution_limit']?></td>
					<td> <?=$document_detail['night_limit_limit']?></td>
				    <td> <?=$document_detail['night_caution_limit']?></td> 
						<?php if(check_user_previlege('p67')){?>
					<td  scope="col">  <button type="button" class="btn btn-primary dual_edit_landings" 
					data-dual_land_origin="<?=$dual_pilot_landings[0]['origin']?>"
					data-dual_day_limit="<?=$dual_pilot_landings[0]['limit_limit']?>"
					data-dual_day_caution="<?=$dual_pilot_landings[0]['caution_limit']?>"
					
					data-dual_night_limit="<?=$dual_pilot_landings[0]['night_limit_limit']?>"
					data-dual_night_caution="<?=$dual_pilot_landings[0]['night_caution_limit']?>"
					
					>Edit </button>
					<button type="button" class="btn btn-danger delete_fare_new "  data-common="<?=$document_detail['origin']?>">Delete</button>
					</td>
						<?php } ?>
				</tr>
				<?php } }else{ ?> 
				<tr><td colspan="12"> <strong>No Details Found.</strong></td></tr>
				<?php }?>
				</tbody>
			</table>
		  </div>
		</div>
	</div>
	<!-- PANEL WRAP END -->


<div id="dual_edit_landings" class="modal" role="dialog">
  <div class="modal-dialog" style='width: 85%;'>

    <!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" id="title"> Edit Landings</h4>
			</div>
			<div class="modal-body action_details" style='background-color: #fff;'>
				<div class="text-danger" id="err"></div> 
					<form method="post" action="" id="meal_detail_frm">
					<fieldset>
					   <legend class="sm_titl"> Flight Landings </legend>
					   <div class="col-xs-12 col-sm-12 fare_info nopad">
						  <div class="form-group">
							  <div class="org_row">
								 <div class="radio">
									<div class="col-sm-1"></div>
									<div class="col-sm-6">Limit </div>
									<div class="col-sm-5">Caution </div>
									
								  </div>
									<div class="radio">
									
									<input type='hidden' name='time_origin' id='dual_land_origin' value='0'>
									<input type='hidden' name='flight' value='5'>
									
									<div class="col-sm-1"><label for="value_type" class="col-sm-2 control-label">Day<span class="text-danger">*</span></label></div>
									
									<div class="col-sm-6"><input type="text" class="wdt25 form-control numeric " id='dual_day_limit'  placeholder="Daily Limit" name="limit_limit" required="" value=""maxlength='3'></div>
									
									
									<div class="col-sm-5"><input type="text" class="wdt25 form-control numeric " id='dual_day_caution'  placeholder="Daily Limit Caution" name="caution_limit" required="" value=""maxlength='3'></div>
									
									
									
									
								
								  </div><br><br>
								  <div class="radio">
									<div class="col-sm-1"><label for="value_type" class="col-sm-2 control-label">Night<span class="text-danger">*</span></label></div>
									
									<div class="col-sm-6"><input type="text" class="wdt25 form-control numeric " id='dual_night_limit'  placeholder="Daily Limit" name="night_limit_limit" required="" value=""maxlength='3'></div>
									
									<div class="col-sm-5"><input type="text" class="wdt25 form-control numeric " id='dual_night_caution'  placeholder="Daily Limit Caution" name="night_caution_limit" required="" value=""maxlength='3'></div>						
									
									
									
									
									
									
									
								
								  </div>
							   </div>
							</div>
						 </div>
					</fieldset>
			</div>
			<div class="modal-footer">
      	<div class="col-xs-12 col-sm-12">
        <button type="submit" class="btn btn-primary" id="save" >Submit</button>
     
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
</form>
		</div>
	</div>
	</div>














































<!----------------------------------------*******************************----------------------->
<div class="panel panel-default"> 
			<!-- PANEL WRAP START -->
		<div class="panel-heading">
			<!-- PANEL HEAD START -->
			<div class="panel-title">
             Single Pilot Limitations
			</div>
		</div>
			<!-- PANEL HEAD START -->
		<div class="panel-body">
			<?php if(check_user_previlege('p67')){?>
		<?php if(count(@$single_flight_time_limit) == 0){ ?>
		<button type="button" class="btn btn-primary add_fare" data-type='single_limit' id="">Add Flight Time Limit</button>
		<?php } ?>
		<?php } ?>
		<div class="table-responsive">
			<!-- PANEL BODY START -->
				<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<thead>
				</thead><tbody>
				<tr>
					<td  rowspan="3"><i class="fa fa-sort-numeric-asc"></i>Flight Time <br> (in Hrs)</td>
					<td  scope="col" colspan="2">Daily (Day)</td>
					<td  scope="col" colspan="2">Daily (Night)</td>
					<td  scope="col" colspan="2">Last 7 consecutive days </td>
					<td  scope="col" colspan="2">Last 30 consecutive days</td>
					<td  scope="col" colspan="2">Last 365 consecutive days </td>
						<?php if(check_user_previlege('p67')){?><td>Action</td><?php } ?>
				</tr> 
				<tr>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<?php if(check_user_previlege('p67')){?><td></td><?php } ?>
				</tr>
				<?php 
				if(count(@$single_flight_time_limit)>0){
				    $i=1;
				foreach($single_flight_time_limit as $key => $document_detail){ 
				?>
				<tr>
				    <td> <?=$document_detail['limit_limit']?></td>
				    <td> <?=$document_detail['caution_limit']?></td>
					
					<td> <?=$document_detail['night_limit_limit']?></td>
				    <td> <?=$document_detail['night_caution_limit']?></td>
					
				    <td> <?=$document_detail['limit_week']?></td>
				    <td> <?=$document_detail['caution_week']?></td>
				    <td> <?=$document_detail['limit_month']?></td>
				    <td> <?=$document_detail['caution_month']?></td>
				    <td> <?=$document_detail['limit_year']?></td>
				    <td> <?=$document_detail['caution_year']?></td>
						<?php if(check_user_previlege('p67')){?>
					<td>  <button type="button" class="btn btn-primary edit_single_flight_time_limit" 
					data-time_origin="<?=$single_flight_time_limit[0]['origin']?>" 
					data-flight_time_limit="<?=$single_flight_time_limit[0]['limit_limit']?>" 
					data-flight_time_caution="<?=$single_flight_time_limit[0]['caution_limit']?>" 
					
					data-night_flight_time_limit="<?=$single_flight_time_limit[0]['night_limit_limit']?>" 
					data-night_flight_time_caution="<?=$single_flight_time_limit[0]['night_caution_limit']?>" 
					
					
					data-flight_time_limit_week="<?=$single_flight_time_limit[0]['limit_week']?>" 
					data-flight_time_caution_week="<?=$single_flight_time_limit[0]['caution_week']?>" 
					data-flight_time_limit_month="<?=$single_flight_time_limit[0]['limit_month']?>" 
					data-flight_time_caution_month="<?=$single_flight_time_limit[0]['caution_month']?>" 
					data-flight_time_limit_year="<?=$single_flight_time_limit[0]['limit_year']?>" 
					data-flight_time_caution_year="<?=$single_flight_time_limit[0]['caution_year']?>" 
					>Edit</button>
					<button type="button" class="btn btn-danger delete_fare_new "  data-common="<?=$document_detail['origin']?>">Delete</button>
					</td>
						<?php } ?>
				</tr>
				<?php  } }else{ ?>
				<tr><td colspan="12"> <strong>No Details Found.</strong></td></tr>				
				<?php }?>
				</tbody>
			</table>
		  </div>
		</div>
		<!-- PANEL BODY END -->




<div id="edit_single_flight_time_limit" class="modal" role="dialog">
  <div class="modal-dialog" style='width: 85%;'>

    <!-- Modal content-->
		<div class="modal-content" style='background-color: #fff;'>
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" id="title"> Edit Flight Time Limit  </h4>
			</div>
			<div class="modal-body action_details"  style='background-color: #fff;'>
				<div class="text-danger" id="err"></div>
      <form method="post" action="" id="meal_detail_frm">
      	<div class="col-xs-12 col-sm-12 fare_info nopad">
				<fieldset>
			   <legend class="sm_titl"> Cumulative flight time limit setup</legend>
			   <div class="col-xs-12 col-sm-12 fare_info nopad">
				  <div class="form-group">
					 <div class="org_row">
						 <div class="radio">
							<div class="col-sm-1"></div>
							<div class="col-sm-2">Daily (Day)</div>
							<div class="col-sm-2">Daily (Night)</div>
							<div class="col-sm-2">Last 7 consecutive days</div>
							<div class="col-sm-2">Last 30 consecutive days</div>
							<div class="col-sm-3">Last 365 consecutive days</div> 
						  </div>
						  <div class="radio">
						 
							  <input type="hidden"  name="time_origin" value="0">
							  <input type='hidden' name='flight' value='3'>
							<div class="col-sm-1"><label for="value_type" class="col-sm-2 control-label">Limit<span class="text-danger">*</span></label></div>
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_single_time_limit' placeholder="Daily Limit" name="limit_limit" required="" value=""maxlength='3'></div>
							<!------>
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='night_flight_single_time_limit' placeholder="Daily Limit" name="night_limit_limit" required="" value=""maxlength='3'></div>
							
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_single_time_limit_week' placeholder="Week Limit" name="limit_week" required="" value="" maxlength='3'></div>
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_single_time_limit_month' placeholder="Month Limit" name="limit_month" required="" value=""maxlength='3'></div>
							<div class="col-sm-3"><input type="text" class="wdt25 form-control numeric " id='flight_single_time_limit_year' placeholder="Year Limit" name="limit_year" required="" value=""maxlength='3'></div> 
						  </div>
						  <div class="radio">
							<div class="col-sm-1"><label for="value_type" class="col-sm-2 control-label">Caution<span class="text-danger">*</span></label></div>
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_single_time_caution' placeholder="Caution Daily Limit" name="caution_limit" required="" value=""maxlength='3'></div>
							<!------->
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='night_flight_single_time_caution' placeholder="Caution Daily Limit" name="night_caution_limit" required="" value=""maxlength='3'></div>
							
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_single_time_caution_week' placeholder="Week Caution" name="caution_week" required="" value=""maxlength='3'></div>
							<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_single_time_caution_month' placeholder="Month Caution" name="caution_month" required="" value=""maxlength='3'></div>
							<div class="col-sm-3"><input type="text" class="wdt25 form-control numeric " id='flight_single_time_caution_year' placeholder="Year Caution" name="caution_year" required="" value=""maxlength='3'></div> 
						  </div>
					   </div>
					</div>
				 </div>
			</fieldset>
			
			</div>
			</div>
			<div class="modal-footer">
      	<div class="col-xs-12 col-sm-12">
        <button type="submit" class="btn btn-primary" id="save" >Submit</button>
        </form>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>

		</div>
	</div>
</div>






<div class="panel-body">
	<?php if(check_user_previlege('p67')){?>
		<?php if(count(@$single_flight_duty_period) == 0){ ?>
		<button type="button" class="btn btn-primary add_fare" data-type='single_duty' id="">Add Flight Duty Period</button>
		<?php } ?>
		<?php } ?>
		<div class="table-responsive">
			<!-- PANEL BODY START -->
				<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<thead>
				</thead><tbody>
				<tr>
					<td rowspan="3" > <i class="fa fa-sort-numeric-asc"></i> Flight Duty Period<br> (in Hrs)</td>
					<td  scope="col" colspan="2">Daily (Day)</td>
					<td  scope="col" colspan="2">Daily (Night)</td>
					<td  scope="col" colspan="2">Last 7 consecutive days<br>Duty Period</td>
					<td  scope="col" colspan="2">Last 14 consecutive days<br>Duty Period</td>
					<td  scope="col" colspan="2">Last 28 consecutive days<br>Duty Period</td>
						<?php if(check_user_previlege('p67')){?><td>Action</td><?php } ?>
				</tr>
				<tr>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td> 
					<td>Limit</th><td>Caution</td>
					<td>Limit</th><td>Caution</td>
					<?php if(check_user_previlege('p67')){?><td></td><?php } ?>
				</tr>
				<?php 
				if(count(@$single_flight_duty_period)>0){
				    $i=1;
				foreach($single_flight_duty_period as $key => $document_detail){ 
				?>
				<tr>
				    <td> <?=$document_detail['limit_limit']?></td>
				    <td> <?=$document_detail['caution_limit']?></td>				    
					<td> <?=$document_detail['night_limit_limit']?></td>
				    <td> <?=$document_detail['night_caution_limit']?></td>				    
					
				    <td> <?=$document_detail['limit_week']?></td>
				    <td> <?=$document_detail['caution_week']?></td>				    
				    <td> <?=$document_detail['limit_month']?></td>
				    <td> <?=$document_detail['caution_month']?></td>				    
				    <td> <?=$document_detail['limit_year']?></td>
				    <td> <?=$document_detail['caution_year']?></td>		
	<?php if(check_user_previlege('p67')){?>					
					<td  scope="col">  <button type="button" class="btn btn-primary edit_single_flight_duty_period" 	
					data-duty_origin="<?=$single_flight_duty_period[0]['origin']?>"
					data-flight_duty_limit="<?=$single_flight_duty_period[0]['limit_limit']?>"
					data-flight_duty_caution="<?=$single_flight_duty_period[0]['caution_limit']?>"
					
					data-night_flight_duty_limit="<?=$single_flight_duty_period[0]['night_limit_limit']?>"
					data-night_flight_duty_caution="<?=$single_flight_duty_period[0]['night_caution_limit']?>"
					
					data-flight_duty_limit_week="<?=$single_flight_duty_period[0]['limit_week']?>"
					data-flight_duty_caution_week="<?=$single_flight_duty_period[0]['caution_week']?>"
					data-flight_duty_limit_month="<?=$single_flight_duty_period[0]['limit_month']?>"
					data-flight_duty_caution_month="<?=$single_flight_duty_period[0]['caution_month']?>"
					data-flight_duty_limit_year="<?=$single_flight_duty_period[0]['limit_year']?>"
					data-flight_duty_caution_year="<?=$single_flight_duty_period[0]['caution_year']?>"
					>Edit</button>
					<button type="button" class="btn btn-danger delete_fare_new "  data-common="<?=$document_detail['origin']?>">Delete</button>
					</td>
	<?php } ?>
				</tr>
				<?php } }else{ ?>
				<tr><td colspan="12"> <strong>No Details Found.</strong></td></tr>				
				<?php }?>
				</tbody>
			</table>
		  </div>
		</div>

	
	
	
<div id="edit_single_flight_duty_period" class="modal" role="dialog">
  <div class="modal-dialog" style='width: 85%;'>

    <!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" id="title"> Edit Flight Time Limit  </h4>
			</div>
			<div class="modal-body action_details" style='background-color: #fff;'>
				<div class="text-danger" id="err"></div> 
				<form method="post" action="" id="meal_detail_frm">
					<fieldset>
					   <legend class="sm_titl"> Flight Duty Period</legend>
					   <div class="col-xs-12 col-sm-12 fare_info nopad">
						  <div class="form-group">
							  <div class="org_row">
								 <div class="radio">
									<div class="col-sm-1"></div>
									<div class="col-sm-2">Daily (Day)</div>
									<div class="col-sm-2">Daily (Night)</div>
									<div class="col-sm-2">Last 7 consecutive days</div>
									<div class="col-sm-2">Last 14 consecutive days</div>
									<div class="col-sm-3">Last 28 consecutive days</div> 
								  </div>
									<div class="radio">		
									<input type="hidden" name="duty_origin" value="0">
									<input type='hidden' name='flight' value='4'>
									<div class="col-sm-1"><label for="value_type" class="col-sm-2 control-label">Limit<span class="text-danger">*</span></label></div>
									
									
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_single_duty_limit'  placeholder="Daily Limit" name="limit_limit" required="" value=""maxlength='3'></div>
									
									<!------->
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='night_flight_single_duty_limit'  placeholder="Daily Limit" name="night_limit_limit" required="" value=""maxlength='3'></div>
									
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_single_duty_limit_week'  placeholder="Limit of Last 7 consecutive days" name="limit_week" required="" value=""maxlength='3'></div>
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_single_duty_limit_month'  placeholder="Limit of Last 14 consecutive days" name="limit_month" required="" value=""maxlength='3'></div>
									<div class="col-sm-3"><input type="text" class="wdt25 form-control numeric " id='flight_single_duty_limit_year'  placeholder="Limit of Last 28 consecutive days" name="limit_year" required="" value=""maxlength='3'></div> 
								  </div>
								  <div class="radio">
									<div class="col-sm-1"><label for="value_type" class="col-sm-2 control-label">Caution<span class="text-danger">*</span></label></div>
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_single_duty_caution'  placeholder="Daily Limit Caution" name="caution_limit" required="" value=""maxlength='3'></div>
									
									<!------->
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='night_flight_single_duty_caution'  placeholder="Daily Limit" name="night_caution_limit" required="" value=""maxlength='3'></div>
									
									
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_single_duty_caution_week'  placeholder="Caution of Last 7 consecutive days" name="caution_week" required="" value=""maxlength='3'></div>
									<div class="col-sm-2"><input type="text" class="wdt25 form-control numeric " id='flight_single_duty_caution_month'  placeholder="Caution of Last 14 consecutive days" name="caution_month" required="" value=""maxlength='3'></div>
									<div class="col-sm-3"><input type="text" class="wdt25 form-control numeric " id='flight_single_duty_caution_year'  placeholder="Caution of Last 28 consecutive days" name="caution_year" required="" value=""maxlength='3'></div> 
								  </div>
							   </div>
							</div>
						 </div>
					</fieldset>
			</div>
			<div class="modal-footer">
      	<div class="col-xs-12 col-sm-12">
        <button type="submit" class="btn btn-primary" id="save" >Submit</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
</form>
		</div>
	</div>
</div>
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	<div class="panel-body">
		<?php if(count(@$single_pilot_landings) == 0){ ?>
		<button type="button" class="btn btn-primary add_fare" data-type='single_edit_landings' id="">Add Landings</button>
		<?php } ?>
		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<tbody>
				<tr>
					<td rowspan="3" > <i class="fa fa-sort-numeric-asc"></i> Landings</td>		
					<td  scope="col" colspan="2">Day  </td>
					<td  scope="col" colspan="2">Night </td>
						<?php if(check_user_previlege('p67')){?>
					<td>Action</td>
						<?php } ?>
				</tr>
				<tr>
					<td>Limit</td><td>Caution</td>
					<td>Limit</td><td>Caution</td>
						<?php if(check_user_previlege('p67')){?><td></td><?php } ?>
				</tr>
				<?php 
				if(count(@$single_pilot_landings)>0){
				    $i=1;
				foreach($single_pilot_landings as $key => $document_detail){ 
				?>
				<tr>
				    <td> <?=$document_detail['limit_limit']?></td>
				    <td> <?=$document_detail['caution_limit']?></td>
					<td> <?=$document_detail['night_limit_limit']?></td>
				    <td> <?=$document_detail['night_caution_limit']?></td> 
					
						<?php if(check_user_previlege('p67')){?>
					<td  scope="col">  <button type="button" class="btn btn-primary single_edit_landings" 
					data-single_land_origin="<?=$single_pilot_landings[0]['origin']?>"
					data-single_day_limit="<?=$single_pilot_landings[0]['limit_limit']?>"
					data-single_day_caution="<?=$single_pilot_landings[0]['caution_limit']?>"
					data-single_night_limit="<?=$single_pilot_landings[0]['night_limit_limit']?>"
					data-single_night_caution="<?=$single_pilot_landings[0]['night_caution_limit']?>"
					>Edit</button>
					<button type="button" class="btn btn-danger delete_fare_new "  data-common="<?=$document_detail['origin']?>">Delete</button>
					</td>
						<?php } ?>
				</tr>
				<?php } }else{ ?> 
				<tr><td colspan="12"> <strong>No Details Found.</strong></td></tr>
				<?php }?>
				</tbody>
			</table>
		  </div>
		</div>
		
</div>
<div id="single_edit_landings" class="modal" role="dialog">
  <div class="modal-dialog" style='width: 85%;'>

    <!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" id="title"> Edit Landings</h4>
			</div>
			<div class="modal-body action_details" style='background-color: #fff;'>
				<div class="text-danger" id="err"></div> 
					<form method="post" action="" id="meal_detail_frm">
					<fieldset>
					   <legend class="sm_titl"> Flight Landings </legend>
					   <div class="col-xs-12 col-sm-12 fare_info nopad">
						  <div class="form-group">
							  <div class="org_row">
								 <div class="radio">
									<div class="col-sm-1"></div>
									<div class="col-sm-6">Limit </div>
									<div class="col-sm-5">Caution </div>
									
								  </div>
									<div class="radio">
									
									<input type='hidden' name='time_origin' id='single_land_origin' value='0'>
									<input type='hidden' name='flight' value='6'>
									<div class="col-sm-1"><label for="value_type" class="col-sm-2 control-label">Day<span class="text-danger">*</span></label></div>
									
									<div class="col-sm-6"><input type="text" class="wdt25 form-control numeric " id='single_day_limit'  placeholder="limit_limit" name="limit_limit" required="" value=""maxlength='3'></div>									
									
									<div class="col-sm-5"><input type="text" class="wdt25 form-control numeric " id='single_day_caution'  name="caution_limit" placeholder="caution_limit" required="" value=""maxlength='3'></div>
								
								
								  </div><br><br>
								  <div class="radio">
									<div class="col-sm-1"><label for="value_type" class="col-sm-2 control-label">Night<span class="text-danger">*</span></label></div>
									
										
									<div class="col-sm-6"><input type="text" class="wdt25 form-control numeric " id='single_night_limit'  placeholder="night_limit_limit" name="night_limit_limit" required="" value=""maxlength='3'></div>
									
									
									<div class="col-sm-5">
									<input type="text" class="wdt25 form-control numeric " id='single_night_caution' placeholder="night_caution_limit" name="night_caution_limit" required="" value="" maxlength='3'></div>
									
									
								
								  </div>
							   </div>
							</div>
						 </div>
					</fieldset>
			</div>
			<div class="modal-footer">
      	<div class="col-xs-12 col-sm-12">
        <button type="submit" class="btn btn-primary" id="save" >Submit</button>
     
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
</form>
		</div>
	</div>
	</div>















<!-- HTML END -->




     <script type='text/javascript' >
     $(document).ready(function(){
 	//  to add meal details and show
	
	
	
	
	
	 $('.add_fare').on('click', function(){
	 	$('#title').text('Add Flight Time Limit');
	 	$("#err").text('');
	 	var type =  $(this).data('type');
    	$('input[name="time_origin"]').val('');
	 	$('input[name="duty_origin"]').val('');
	 	$("#flight_time_limit").val('');
	 	$("#flight_time_caution").val('');
        $("#flight_time_limit_week").val('');
	 	$("#flight_time_caution_week").val('');
	 	$("#flight_time_limit_month").val('');
	 	$("#flight_time_caution_month").val('');
	 	$("#flight_time_limit_year").val('');
	 	$("#flight_time_caution_year").val('');
	 	$("#flight_duty_limit").val('');
	 	$("#flight_duty_caution").val('');
        $("#flight_duty_limit_week").val('');
	 	$("#flight_duty_caution_week").val('');
	 	$("#flight_duty_limit_month").val('');
	 	$("#flight_duty_caution_month").val('');
	 	$("#flight_duty_limit_year").val('');
	 	$("#flight_duty_caution_year").val('');
		
		if(type == 'limit'){
			$("#edit_flight_time_limit").modal('show');
		}else if(type == 'duty'){
			$("#edit_flight_duty_period").modal('show');
		}else if(type == 'single_limit'){
			$("#edit_single_flight_time_limit").modal('show');
		}else if(type == 'single_duty'){
			$("#edit_single_flight_duty_period").modal('show');
		}
		
		else if(type == 'dual_edit_landings'){
			$("#dual_edit_landings").modal('show');
		}
		else if(type == 'single_edit_landings'){
			$("#single_edit_landings").modal('show');
		}
		
	 	//$("#add_fare_rule").modal('show');
	 });
	 



	$('.single_edit_landings').on('click', function()
	{
	
		var single_land_origin =  $(this).data('single_land_origin');
		
	 	var single_day_limit =  $(this).data('single_day_limit');
	 	var single_day_caution =  $(this).data('single_day_caution');
		var single_night_limit =  $(this).data('single_night_limit');
	 	var single_night_caution =  $(this).data('single_night_caution');
		$('#single_land_origin').val(single_land_origin);
	 	$("#single_day_limit").val(single_day_limit);
	 	$("#single_day_caution").val(single_day_caution);
		$("#single_night_limit").val(single_night_limit);
	 	$("#single_night_caution").val(single_night_caution);
		$("#single_edit_landings").modal('show');
	});
	
	$('.dual_edit_landings').on('click', function()
	{
		var dual_land_origin =  $(this).data('dual_land_origin');
	
	 	var dual_day_limit =  $(this).data('dual_day_limit');
	 	var dual_day_caution =  $(this).data('dual_day_caution');
		var dual_night_limit =  $(this).data('dual_night_limit');
	 	var dual_night_caution =  $(this).data('dual_night_caution');
		$('#dual_land_origin').val(dual_land_origin);
	 	$("#dual_day_limit").val(dual_day_limit);
	 	$("#dual_day_caution").val(dual_day_caution);
		$("#dual_night_limit").val(dual_night_limit);
	 	$("#dual_night_caution").val(dual_night_caution);
		$("#dual_edit_landings").modal('show');
		
	});
	
	

 $('.edit_single_flight_time_limit').on('click', function(){		
		var time_origin =  $(this).data('time_origin');
	 	var flight_time_limit =  $(this).data('flight_time_limit');
	 	var flight_time_caution =  $(this).data('flight_time_caution');
		
		var night_flight_time_limit =  $(this).data('night_flight_time_limit');
	 	var night_flight_time_caution =  $(this).data('night_flight_time_caution');
		
	 	var flight_time_limit_week =  $(this).data('flight_time_limit_week');
	 	var flight_time_caution_week =  $(this).data('flight_time_caution_week');
	 	var flight_time_limit_month =  $(this).data('flight_time_limit_month');
	 	var flight_time_caution_month =  $(this).data('flight_time_caution_month');
	 	var flight_time_limit_year =  $(this).data('flight_time_limit_year');
	 	var flight_time_caution_year =  $(this).data('flight_time_caution_year');




		$('input[name="time_origin"]').val(time_origin);
	 	$("#flight_single_time_limit").val(flight_time_limit);
	 	$("#flight_single_time_caution").val(flight_time_caution);
		
		$("#night_flight_single_time_limit").val(night_flight_time_limit);
	 	$("#night_flight_single_time_caution").val(night_flight_time_caution);
		
        $("#flight_single_time_limit_week").val(flight_time_limit_week);
	 	$("#flight_single_time_caution_week").val(flight_time_caution_week);
	 	$("#flight_single_time_limit_month").val(flight_time_limit_month);
	 	$("#flight_single_time_caution_month").val(flight_time_caution_month);
	 	$("#flight_single_time_limit_year").val(flight_time_limit_year);
	 	$("#flight_single_time_caution_year").val(flight_time_caution_year);
		$("#edit_single_flight_time_limit").modal('show');
});


 $('.edit_single_flight_duty_period').on('click', function(){		
			var duty_origin =  $(this).data('duty_origin');
	 	var duty =  $(this).data('duty');
	 	var flight_duty_limit =  $(this).data('flight_duty_limit');
	 	var flight_duty_caution =  $(this).data('flight_duty_caution');
		
		var night_flight_duty_limit =  $(this).data('night_flight_duty_limit');
	 	var night_flight_duty_caution =  $(this).data('night_flight_duty_caution');
		
		
	 	var flight_duty_limit_week =  $(this).data('flight_duty_limit_week');
	 	var flight_duty_caution_week =  $(this).data('flight_duty_caution_week');
	 	var flight_duty_limit_month =  $(this).data('flight_duty_limit_month');
	 	var flight_duty_caution_month =  $(this).data('flight_duty_caution_month');
	 	var flight_duty_limit_year =  $(this).data('flight_duty_limit_year');
	 	var flight_duty_caution_year =  $(this).data('flight_duty_caution_year');
		




		$('input[name="duty_origin"]').val(duty_origin);
		$("#flight_single_duty_limit").val(flight_duty_limit);
	 	$("#flight_single_duty_caution").val(flight_duty_caution);
		
		
		$("#night_flight_single_duty_limit").val(night_flight_duty_limit);
	 	$("#night_flight_single_duty_caution").val(night_flight_duty_caution);
		
		
        $("#flight_single_duty_limit_week").val(flight_duty_limit_week);
	 	$("#flight_single_duty_caution_week").val(flight_duty_caution_week);
	 	$("#flight_single_duty_limit_month").val(flight_duty_limit_month);
	 	$("#flight_single_duty_caution_month").val(flight_duty_caution_month);
	 	$("#flight_single_duty_limit_year").val(flight_duty_limit_year);
	 	$("#flight_single_duty_caution_year").val(flight_duty_caution_year);
		$("#edit_single_flight_duty_period").modal('show');
});

 $('.edit_flight_time_limit').on('click', function(){
		var time_origin =  $(this).data('time_origin');
	 	var time =  $(this).data('time');
	 	var flight_time_limit =  $(this).data('flight_time_limit');
	 	var flight_time_caution =  $(this).data('flight_time_caution');
		
		var night_flight_time_limit =  $(this).data('night_flight_time_limit');
	 	var night_flight_time_caution =  $(this).data('night_flight_time_caution');
		
		
	 	var flight_time_limit_week =  $(this).data('flight_time_limit_week');
	 	var flight_time_caution_week =  $(this).data('flight_time_caution_week');
	 	var flight_time_limit_month =  $(this).data('flight_time_limit_month');
	 	var flight_time_caution_month =  $(this).data('flight_time_caution_month');
	 	var flight_time_limit_year =  $(this).data('flight_time_limit_year');
	 	var flight_time_caution_year =  $(this).data('flight_time_caution_year');
		$('input[name="time_origin"]').val(time_origin);
		
	 	$("#flight_time_limit").val(flight_time_limit);
	 	$("#flight_time_caution").val(flight_time_caution);
		
		$("#night_flight_time_limit").val(night_flight_time_limit);
	 	$("#night_flight_time_caution").val(night_flight_time_caution);
		
		
        $("#flight_time_limit_week").val(flight_time_limit_week);
	 	$("#flight_time_caution_week").val(flight_time_caution_week);
	 	$("#flight_time_limit_month").val(flight_time_limit_month);
	 	$("#flight_time_caution_month").val(flight_time_caution_month);
	 	$("#flight_time_limit_year").val(flight_time_limit_year);
	 	$("#flight_time_caution_year").val(flight_time_caution_year);
		$("#edit_flight_time_limit").modal('show');
});

 $('.edit_flight_duty_period').on('click', function(){
		var duty_origin =  $(this).data('duty_origin');
	 	var duty =  $(this).data('duty');
	 	var flight_duty_limit =  $(this).data('flight_duty_limit');
	 	var flight_duty_caution =  $(this).data('flight_duty_caution');
		
		var night_flight_duty_limit =  $(this).data('night_flight_duty_limit');
	 	var night_flight_duty_caution =  $(this).data('night_flight_duty_caution');
		
	 	var flight_duty_limit_week =  $(this).data('flight_duty_limit_week');
	 	var flight_duty_caution_week =  $(this).data('flight_duty_caution_week');
	 	var flight_duty_limit_month =  $(this).data('flight_duty_limit_month');
	 	var flight_duty_caution_month =  $(this).data('flight_duty_caution_month');
	 	var flight_duty_limit_year =  $(this).data('flight_duty_limit_year');
	 	var flight_duty_caution_year =  $(this).data('flight_duty_caution_year');
	 	$('input[name="duty_origin"]').val(duty_origin);
		$("#flight_duty_limit").val(flight_duty_limit);
	 	$("#flight_duty_caution").val(flight_duty_caution);
		
		$("#night_flight_duty_limit").val(night_flight_duty_limit);
	 	$("#night_flight_duty_caution").val(night_flight_duty_caution);
		
		
        $("#flight_duty_limit_week").val(flight_duty_limit_week);
	 	$("#flight_duty_caution_week").val(flight_duty_caution_week);
	 	$("#flight_duty_limit_month").val(flight_duty_limit_month);
	 	$("#flight_duty_caution_month").val(flight_duty_caution_month);
	 	$("#flight_duty_limit_year").val(flight_duty_limit_year);
	 	$("#flight_duty_caution_year").val(flight_duty_caution_year);
		$("#edit_flight_duty_period").modal('show');
});



	 //  delete fare rule
	  $('.delete_fare_new').on('click', function(){
	        var result = confirm("Want to delete?");
            if (result) {
    	 	var common =  $(this).data('common');
    	 	
    	 	    $.ajax({
    	 	        
    	 	        url: app_base_url+'index.php/ajax/delete_time_duty_details/',
                        type: 'post',
                        dataType: "json",
                        data: { 
                            common: common
                        },
                    success:function(data){
    		 			location.reload();
    		 		    }
    		 		});
    	       }
	 });
}); 
    </script>