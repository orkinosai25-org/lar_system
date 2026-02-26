

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
$( function() {
	$( ".datepicker" ).datepicker({
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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
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
				Pilot Training <a class="btn btn-info"  href="<?php echo base_url() ?>index.php/flight/pilot_list" >Back</a>
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
		    <div class="bs-docs-example" style="padding-bottom: 24px;">
		      Pilot Name :  <?=$pilot_training[0]['first_name']?> <?=$pilot_training[0]['last_name']?>
		        
					        
       <!--a data-toggle="modal" href="#myModal" class="btn btn-primary btn-large add_leave" data-backdrop="static" >Add Pilot leaves</a-->
       </div>
		<!--button type="button" class="btn btn-primary" id="add_fare"></button-->
		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover table-condensed" id="tab_leave_list">
			<thead>
				<tr>
					<td><i class="fa fa-sort-numeric-asc"></i> S. No.</td>
				
					<td>Training Name</td>
					<td>Date of Training</td>
					<td>Valid Till</td>
					<td>Status</td>
					<td>Action</td>
				</tr>
				</thead><tbody>
				<?php 
				if(count(@$pilot_training)>0){
				foreach($pilot_training as $key => $document_detail){ 
				    
				     
                     
				?>
				<tr>
					<td><?=$key+1?></td>
					<td><?=$document_detail['training_name']?></td>
					<td><?=date("d-m-Y", strtotime($document_detail['training_issue_date']))?></td>
					<td><?=date("d-m-Y", strtotime($document_detail['training_expiry_date']))?></td>
						<?php
					$current = strtotime(date("Y-m-d"));
                     $date    = strtotime($document_detail['training_expiry_date']);
                    
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
					data-training_type="<?=$document_detail['training_origin']?>" 
					data-training_issue_date="<?=$document_detail['training_issue_date']?>" 
					data-training_expiry_date="<?=$document_detail['training_expiry_date']?>" 
				
					class="btn btn-primary btn-large leave_update_fare" data-backdrop="static"  >Edit</a>
					<button type="button" class="btn btn-danger delete_fare" data-origin="<?=$document_detail['pilot_origin']?>" >Delete</button>
				
					</td>
					
				</tr>
				<?php 
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No Training added.</strong></td>
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
                                  
                                  <!--  <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Select Aircraft<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                        <select class="form-control" id="" name="aircraft"> 
                                        <option >select</option>   
                                          <?php foreach($aircraft_data as $ad) {
                                           $selected ='';
                                              if($ad['origin'] == $pilot_list['aircraft'])  { $selected ='selected'; } ?>
                                               <option <?=$selected?> value="<?=$ad['origin']?>"><?=$ad['model']?></option>  
                                        <?php }  ?>
                                      </select>
                                   </div>
                                 </div>
                                </div>-->
                                
                                
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Select Training Type<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                     <select class="form-control" name="training_type">       
                                      <option value='' >select</option>
                                     <?php foreach($training_type as $td){    ?>                     
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
                                     <input type="text" class="wdt25 form-control datepicker"  id='training_datetime_from' readonly  name="training_issue_date" >
                                     
                                   
                                   </div>
                                 </div>
                                </div>
                                <div class="col-sm-6">
                                 <div class="radio">
                                   <label for="value_type" class="col-sm-4 control-label">Expiry Date<span class="text-danger">*</span></label>
                                   <div class="col-md-8">
                                    <input type="text" class="wdt25 form-control datepicker"  id='training_datetime_to' readonly  name="training_expiry_date" >
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
	 		
		var origin =  $(this).data('origin');
	 	var training_origin =  $(this).data('training_origin');
	 	var training_type =  $(this).data('training_type');
	 	var training_issue_date =  $(this).data('training_issue_date');
	 	var training_expiry_date =  $(this).data('training_expiry_date');
	 	var pilot_id =  $(this).data('pilot_id');
	 	
	 	
	 	
	 	
	  	$('input[name="origin"]').val(origin);
	  	$('input[name="pilot_id"]').val(pilot_id);
	  	
	 	$('select[name="training_type"]').val(training_type);
	 	$('input[name="training_issue_date"]').val(training_issue_date);
	 	$('input[name="training_expiry_date"]').val(training_expiry_date);
	 	
	 });
	 //  delete fare rule
      	  $('.delete_fare').on('click', function(){
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
	 });
	 
});

	</script>
