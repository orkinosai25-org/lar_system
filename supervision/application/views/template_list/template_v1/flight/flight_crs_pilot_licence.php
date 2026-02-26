

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
$( function() {
	$( ".datepickerfrom" ).datepicker({
		dateFormat: "dd-mm-yy" , 
			maxDate: 0 ,  
			changeMonth: true, 
			changeYear: true,
		numberOfMonths: 2
	});

	$( ".datepickerto" ).datepicker({
		dateFormat: "dd-mm-yy" , 
			
			minDate: 0 ,
			changeMonth: true, 
			changeYear: true,
		numberOfMonths: 2
	});
} );
</script>
<style type="text/css">
a.act { cursor: pointer;} 
.table{margin-bottom: 0;}
.modal-footer { padding: 10px;}
  .toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
  .toggle.ios .toggle-handle { border-radius: 20px; }
</style>
<script src="http://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<link href="http://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
<script type="text/javascript">
$(document).ready( function () {
    $('#tab_leave_list').DataTable({"paging" : false});
});
</script>
<!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="panel panel-default">
		<!-- PANEL WRAP START -->
		<div class="panel-heading">
			<!-- PANEL HEAD START -->  
			<div class="panel-title">
				Pilot Licence & Checks <a class="btn btn-info"  href="<?php echo base_url() ?>index.php/flight/pilot_list" >Back</a>
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
		    <div class="bs-docs-example" style="padding-bottom: 24px;">
		      Pilot Name :  <?=$pilot_licence[0]['first_name']?> <?=$pilot_licence[0]['last_name']?>
		        
					        
       <!--a data-toggle="modal" href="#myModal" class="btn btn-primary btn-large add_leave" data-backdrop="static" >Add Pilot leaves</a-->
       </div>
		<!--button type="button" class="btn btn-primary" id="add_fare"></button-->
		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover table-condensed" id="tab_leave_list">
			<thead>
				<tr>
					<td><i class="fa fa-sort-numeric-asc"></i> S. No.</td>
				
					<td>Licence Type</td>
					<td>Licence Number</td>
					<td>Issue Date</td>
					<td>Valid Till</td>
					<td>Status</td>
					<td>Action</td>
				</tr>
				</thead><tbody>
				<?php 
				if(count(@$pilot_licence)>0){
				foreach($pilot_licence as $key => $document_detail){ 
				    
				     
                     
				?>
				<tr>
					<td><?=$key+1?></td>
					<td><?=$document_detail['licence_name']?></td>
					<td><?=$document_detail['licence_number']?></td>
					<td><?=date("d-m-Y", strtotime($document_detail['licence_issue_date']))?></td>
					<td><?=date("d-m-Y", strtotime($document_detail['licence_expiry_date']))?></td>
						<?php
					$current = strtotime(date("Y-m-d"));
                     $date    = strtotime($document_detail['licence_expiry_date']);
                    
                     $datediff = $date - $current;
                     $difference = floor($datediff/(60*60*24));
                     $color = '#fff';
			
                     if($difference==0)
                     {
                        //echo 'today';
                        $message =  'Valid';
                        $color = '#0f0';
                     }
                     else if($difference > 1)
                     {
                        // echo 'Future Date';
                        $message =  'Valid';
                        $color = '#0f0';
                     }
                     else if($difference > 0)
                     {
                        //echo 'tomorrow';
                        $message =  'Valid';
                        $color = '#0f0';
                     }
                     else if($difference < -1)
                     {
                        //echo 'Long Back';
                        //echo 'red';
                        $message =  'Expired';
                        $color = '#ff0000f2';
                     }
                     else
                     {
                         //echo 'yesterday';
                         $message =  'About to Expire';
                         $color = '#ffff00';
                        
                     } 
					?>
					
					<td style='background-color: <?=$color?>'>
				        <?= $message?>
					</td>
					<td > <a data-toggle="modal" href="#myModal"	
					
					data-origin="<?=$document_detail['pilot_origin']?>" 
					data-pilot_id="<?=$document_detail['pilot_id']?>" 
					data-licence_number="<?=$document_detail['licence_number']?>" 
					data-licence_type="<?=$document_detail['licence_name_origin']?>" 
					data-licence_issue_date="<?=$document_detail['licence_issue_date']?>" 
					data-licence_expiry_date="<?=$document_detail['licence_expiry_date']?>" 
				
					class="btn btn-primary btn-large leave_update_fare" data-backdrop="static"  >Edit</a>
					<button type="button" class="btn btn-danger delete_fare" data-table="pilot_licence" data-origin="<?=$document_detail['pilot_origin']?>" >Delete</button>
				
					</td>
					
				</tr>
				<?php 
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No Licence added.</strong></td>
				</tr>
				
				<?php }?>
				</tbody>
			</table>
				
	

		  </div>
		</div>
		<!-- PANEL BODY END -->
	</div>
	<!-- PANEL WRAP END -->
</div>
<!-- HTML END -->

<div  id="myModal" class="modal">
  <div class="modal-dialog" style='width: 85%;'>

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="title"> Add  details</h4>
      </div>
      <div class="modal-body action_details">
      <div class="text-danger" id="err"></div>
      <form method="post" action="" id="meal_detail_frm">
      <input type="hidden"  name="origin" value="0">
      <input name='pilot_id' value='0' type='hidden'>
      	<div class="col-xs-12 col-sm-12 fare_info nopad">
	
	
	
	
	   <div class="form-group">
                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Select Licence Type<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <select class="form-control" id="licence_type" name="licence_type">      
                                      <option value=''>select</option>   
                                          <?php foreach($licence_type as $key => $ll) {
                                          ?>
                                               <option value="<?=$ll['origin']?>"><?=$ll['licence_name']?></option>  
                                        <?php }  ?>
                                      
                                                              
                                    </select>
                                   </div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Number<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                       
                                     <input type="text" class="wdt25 form-control numeric"  placeholder="" name="licence_number" id="licence_number"  value='<?php echo (!empty($pilot_list)) ? $pilot_list['licence_number'] : ''; ?>'    maxlength='10'>
                                   </div>
                                 </div>
                                </div>

                              </div>

                              <div class="org_row">
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Issue date<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control datepickerfrom"  id='licence_datetime_from' readonly  placeholder="" name="licence_issue_date"  value='<?php echo (!empty($pilot_list)) ? $pilot_list['licence_issue_date'] : ''; ?>'  >
                                   </div>
                                 </div>
                                </div>

                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Expiry date<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <input type="text" class="wdt25 form-control datepickerto"  id='licence_datetime_to' readonly  placeholder="" name="licence_expiry_date"    >
                                   </div>
                                 </div>
                                </div>

                              </div>

                           </div>
		
		
		
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

<script>
      $(document).ready(function(){
	 // to  Edit and Update the data
	 $('.leave_update_fare').on('click', function(){
	 	$('#title').text('Update Licence');
	 	console.log('hi')
				
				
	 	var origin =  $(this).data('origin');
	 	var licence_number =  $(this).data('licence_number');
	 	var licence_type =  $(this).data('licence_type');
	 	var licence_issue_date =  $(this).data('licence_issue_date');
	 	var licence_expiry_date =  $(this).data('licence_expiry_date');
	 	var pilot_id =  $(this).data('pilot_id');
	 	
	 		console.log(origin)
	 			console.log(licence_number)
	 			console.log(licence_type)
	 			console.log(licence_issue_date)
	 			console.log(licence_expiry_date)
	 			console.log(pilot_id)
	 	
	 	
	  	$('input[name="origin"]').val(origin);
	 	$('select[id="licence_type"]').val(licence_type);
	 	$('input[name="licence_number"]').val(licence_number);
	 	$('input[name="licence_issue_date"]').val(licence_issue_date);
	 	$('input[name="licence_expiry_date"]').val(licence_expiry_date);
	 	$('input[name="pilot_id"]').val(pilot_id);
	 });

	 
});

	</script>
