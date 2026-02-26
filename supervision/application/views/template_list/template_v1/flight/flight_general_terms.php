<!DOCTYPE html>
<script src="<?php echo SYSTEM_RESOURCE_LIBRARY?>/ckeditor/ckeditor.js"></script>
<style type="text/css">
a.act { cursor: pointer;} 
.table{margin-bottom: 0;}
.modal-footer { padding: 10px;}
  .toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
  .toggle.ios .toggle-handle { border-radius: 20px; }
</style>

<style>.table-responsive { overflow-x: scroll; } </style>
<script type="text/javascript">
$(document).ready( function () {
    //$('#tab_flight_list').DataTable({"paging" : false});
});
</script>


<!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="panel panel-default">
		<!-- PANEL WRAP START -->
		<div class="panel-heading">
			<!-- PANEL HEAD START -->
			<div class="panel-title">
				Flight General Terms
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
			
			
			<?php if(check_user_previlege('p39')){
	if(count(@$general_terms)< 1){
				?>
				
		<button type="button" class="btn btn-primary" id="add_fare">Add General Terms </button>
			<?php } } ?> 
 		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<thead>
				<tr>
					
					<th>General Terms</th> 
					<?php if(check_user_previlege('p39')){?>
					<th>Action</th>
					<?php } ?>
				</tr>
				</thead><tbody>
				<?php 
				if(count(@$general_terms)>0){
				foreach($general_terms as $key => $fare_rule){ 
					// if($flight_list_details['active']==1)
					// 	$chk = "checked";
					// else
					// 	$chk = "";
					?>
				<tr>
				
					<td><?=$fare_rule['general_terms']?></td>
					<?php if(check_user_previlege('p39')){?>
					<td>
					<button type="button" class="btn btn-primary update_fare" data-origin="<?=$fare_rule['origin']?>" data-rule='<?=$fare_rule['general_terms']?>' >Edit</button>
					<button type="button" class="btn btn-danger delete_fare" data-origin="<?=$fare_rule['origin']?>">Delete</button>
					</td>
					<?php } ?>
					
				</tr>
				<?php  
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No General Terms.</strong></td>
				</tr>
				
				<?php }?>
				</tbody>
			</table>
			
			<?php 
			?>
		  </div>
		</div>
		<!-- PANEL BODY END -->
	</div>
	<!-- PANEL WRAP END -->
</div>
<!-- HTML END -->

<div id="add_fare_rule" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="title"> Add General Terms</h4>
      </div>
      <div class="modal-body action_details">
      <div class="text-danger" id="err"></div>
      <form method="post" action="<?php echo base_url().'index.php/flight/add_genetal_terms'; ?>" id="fare_rule_frm">
      <input type="hidden"  name="origin" value="0">
  
   
         <div class="col-xs-12 col-sm-12">
         	<div class="form-group">
         	<div class="row">
         		<div class="col-xs-12 col-sm-12">
         		<label class="col-sm-6 control-label">General Terms</label>
         		</div>
         		<div class="col-xs-12 col-sm-12">
         		<textarea class="ckeditor" id="editor" name="general_terms" rows="10" cols="80"></textarea>
         			</div>
         	</div>
         	</div>
         </div>
      	<!-- <div class="table-responsive fare_data dyn_data">          
		 
		</div> -->
      </div>
      <div class="modal-footer">
      	<div class="col-xs-12 col-sm-12">
        <button type="submit" class="btn btn-primary" id="save">Submit</button>
        </form>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>

  </div>
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

<script type="text/javascript">


 $(document).ready(function(){
 
	 $('#add_fare').on('click', function(){
	 	$('#title').text('Add General Term');
	 	$('input[name="origin"]').val('');
	 	CKEDITOR.instances['editor'].setData('');
	 	$("#add_fare_rule").modal('show');
	 });
 
	 $('.update_fare').on('click', function(){
	 	$('#title').text('Update General Term');
		
	 	var origin =  $(this).data('origin');
	 	var rule =  $(this).data('rule');
	  console.log(origin)
	  console.log(rule)
	  
	  $('input[name="origin"]').val(origin);
	 	CKEDITOR.instances['editor'].setData(rule);
		
		$("#add_fare_rule").modal('show');
		
	});
		
	 });

	 	 //  delete fare rule
	  $('.delete_fare').on('click', function(){
	 	var origin =  $(this).data('origin');
	 	$.ajax({
		 		method:'get',
		 		url:app_base_url+'index.php/flight/delete_fare_rule/'+origin,
		 		dataType: 'json',
		 		success:function(data){
		 			// if(data.status == false) {
		 			// 	$("#err").text(data.msg);
		 			// } else {
		 			// 	$("#fare_rule_frm").submit();
		 			// }
		 			location.reload();
		 		}
		 		});
	 });
</script>
