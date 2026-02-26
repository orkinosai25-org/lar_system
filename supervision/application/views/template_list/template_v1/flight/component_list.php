<style type="text/css"> 
a.act { cursor: pointer;} 
.table{margin-bottom: 0;}
.modal-footer { padding: 10px;}
  .toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
  .toggle.ios .toggle-handle { border-radius: 20px; }
  
  .table-responsive {
    overflow-x: scroll;
}

  
  .box {
  /*float: left;*/
  height: 20px;
  width: 20px;
  margin-bottom: 15px;
  border: 1px solid black;
  /*clear: both;*/
  border: #000 solid 1px;
  font-size: 12px;
}

.red {
  background-color: red;
}.orange {
  background-color: orange;
}
</style>
<link href="http://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
<!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="panel panel-default">
		<!-- PANEL WRAP START -->
		<div class="panel-heading">  
			<!-- PANEL HEAD START -->   
			<div class="panel-title">
				Component List 
				   
					
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
		<a class="btn btn-primary" href="<?=base_url().'index.php/flight/add_component'?>">Add Component</a>
		 <a href = "<?php echo base_url(); ?>index.php/flight/export_component_details/excel"   class="btn btn-primary">Export Component detail</a>
		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover table-condensed" id="tab_component_list">
			<thead>
				<tr>
					<th><i class="fa fa-sort-numeric-asc"></i> S. No.</th>
					<th>Component Name</th>
					<th>Component Type</th>
					<th>Part Number</th>
					<th>Serial Number</th>
					
					<th>Shelf Life Due (In Days)</th>
					<th>Overhaul due (In Days) </th>
					<th>Overhaul due (In Hours) </th>
					<th>Next Maintenance due (In Days) </th>
					<th>Next Maintenance due (In Hours) </th>
					
					
					
					<?php if(check_user_previlege('p72')){?><th>Action</th><?php } ?>
				</tr>
				</thead><tbody>
				<?php 
				
				if(count(@$component_list)>0){
				foreach($component_list as $key => $component_detail){ 

					?>
				<tr>
					<td> <?=$key+1?></td>
					<td><?=$component_detail['component_name'] ?></td>
					<td><?=$component_detail['type'] ?></td>
					<td><?=$component_detail['part_number'] ?></td>
					<td><?=$component_detail['serial_number'] ?></td>
					
					
					<td><?=$component_detail['shelf_life_due'] ?></td>
					
					<?php
						$date1=date_create();
						$date2=date_create($component_detail['tbo_date']);
						$diff=date_diff($date1,$date2);
						$rem = $diff->format("%R%a");
						  
						if($rem < 0)
						{
							echo '<td style="background:red;"> </td>';
						}
						else{
							if($component_detail['set_caution_limit_date'] > $rem )
							{
								echo '<td style="background:orange;color:#fff">'.$component_detail['overhaul_due_days'].'</td>';
							}else{
								echo '<td>'.$component_detail['overhaul_due_days'].'</td>';
							}
						}
						
						
							


							$t1 = EXPLODE(":", $component_detail['set_caution_limit_time']); 
							$h1 = $t1[0]; 
							IF (ISSET($t1[1])) { $m1 = $t1[1]; } ELSE { $m1 = "00"; } 
							$set_caution_limit_time = ($h1 * 60) +  $m1;

		
							$t = EXPLODE(":", $component_detail['overhaul_due_hours']); 
							$h = $t[0]; 
							IF (ISSET($t[1])) { $m = $t[1]; } ELSE { $m = "00"; } 
							$overhaul_due_hours = ($h * 60) +  $m;
							
							if($overhaul_due_hours < 0){
								
								echo '<td style="background:red;"> </td>';
							}else{
							if($set_caution_limit_time > $overhaul_due_hours )
							{
								echo '<td style="background:orange;color:#fff">'.$component_detail['overhaul_due_hours'].'</td>';
							}else{
								echo '<td>'.$component_detail['overhaul_due_hours'].'</td>';
							}
							}
						
						$date3=date_create($component_detail['next_maintenance_due_days']);
						$diff3=date_diff($date1,$date3);
						$rem3 = $diff3->format("%R%a");
						
						
						if($rem3 < 0)
						{
							echo '<td style="background:red;"> </td>';
						}else{
							if($component_detail['set_caution_limit_date'] > $rem3 )
							{
								echo '<td style="background:orange;color:#fff">'.$component_detail['next_maintenance_due_days'].'</td>';
							}else{
								echo '<td>'.$component_detail['next_maintenance_due_days'].'</td>';
							}
						}
						
						
						
							$tt = EXPLODE(":", $component_detail['next_maintenance_due_hours']); 
					
							$hh = $tt[0]; 
							IF (ISSET($tt[1])) { $mm = $tt[1]; } ELSE { $mm = "00"; } 
							 $next_maintenance_due_hours = ($hh * 60) +  $mm;
							
							
							if($next_maintenance_due_hours < 0){
								
								echo '<td style="background:red;"> </td>';
							}else{
								
							if($set_caution_limit_time > $next_maintenance_due_hours )
							{
								echo '<td style="background:orange;color:#fff">'.$component_detail['next_maintenance_due_hours'].'</td>';
							}else{
								echo '<td>'.$component_detail['next_maintenance_due_hours'].'</td>';
							}
							}
						
					?>
		
					
					
					<?php if(check_user_previlege('p72')){?>
					<td>
					<a class="btn btn-primary" href="<?=base_url().'index.php/flight/add_component/'.$component_detail['org']?>" >Edit</a>
					<button type="button" class="btn btn-danger delete_fare" data-table='flight_crs_components' data-origin="<?=$component_detail['org']?>" >Delete</button>
					</td>
					<?php } ?>

					
				</tr>
				<?php 
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No Component added.</strong></td>
				</tr>
				
				<?php }?>
				</tbody>
			</table>
			
		  </div>
		</div>
		<!-- PANEL BODY END -->
	</div>
	<!-- PANEL WRAP END -->
	<div>
	<div class='row'>
			<div style='border: #000 solid 1px;font-size: 12px;padding:5px' class='col-md-3'>
				<span class='box red'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Due day/hour has passed
				<br>
				<span class='box orange'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> With-in caution limit, due day/hour approaching
			
			
			</div>
	</div>
</div>
<!-- HTML END -->

<div id="add_fare_rule" class="modal fade" role="dialog">

</div>
<div id="action" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title dyn_title" id="dynamic_text"></h4>
      </div>
      <div class="modal-body action_details">
      	<div class="table-responsive fare_data dyn_data">          
		 
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<script>
/*   $(function() {
    $('#toggle').bootstrapToggle({
      on: 'Enabled',
      off: 'Disabled'
    });
	
	   $('#tab_component_list').DataTable({
	"searching": true,
	"paging" : false
	});
	
  }) */
</script>

<!-- <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script> -->
<script type="text/javascript">
// $(document).ready( function () {
//     $('#tab_component_list').DataTable({"paging" : false});
// });


$(document).on('click', '.dyna_status', function(){
	var thisss = $(this);
	var fsid   = $(this).data('fsid'); 
	var status = $(this).attr('data-status'); 
	if(parseInt(status) === parseInt(1)){
		status = 0;
	} else {
		status = 1;
	}    
    $.ajax({
        url: "<?=base_url();?>index.php/flight/get_flight_status/"+fsid+"/"+status, 
        async:false, 
        success: function(result){
            thisss.attr('data-status',status);
    	}
    });
});

 $(document).ready(function(){
 	//  to add meal details and show
	 $('#add_fare').on('click', function(){
	 	$('#title').text('Add Duty Type');
	 	$("#err").text('');
	 	$('input[name="origin"]').val('');
	 	$('input[name="duty_type_name"]').val('');
	 	$('input[name="duty_type_code"]').val('');
	 	$("#add_fare_rule").modal('show');
	 });

	 // to  Edit and Update the data
	 $('.update_fare').on('click', function(){
	 	$('#title').text('Update Duty Type');
	 	var origin =  $(this).data('origin');
	 	var duty_type_name =  $(this).data('duty_type_name');
	 	var duty_type_code =  $(this).data('duty_type_code');

	 	$("#add_fare_rule").modal('show');
	 	$('input[name="origin"]').val(origin);
	 	$('input[name="duty_type_name"]').val(duty_type_name);
	 	$('input[name="duty_type_code"]').val(duty_type_code);
	 });

}); 






</script>
