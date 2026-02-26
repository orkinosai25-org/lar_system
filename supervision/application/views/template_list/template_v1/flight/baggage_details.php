<!DOCTYPE html>
<script src="https://cdn.ckeditor.com/4.15.1/standard/ckeditor.js"></script>
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

				Baggage Rules

			</div>

		</div>

		<!-- PANEL HEAD START -->

		<div class="panel-body">

			<?php if(check_user_previlege('p39')){

	if(count(@$baggage_rule_list)< 1){

				?>



		<button type="button" class="btn btn-primary" id="add_fare">Add Baggage Rules</button>

			<?php } ?> 

			<?php } ?> 

 		<div class="table-responsive">

			<!-- PANEL BODY START -->

			<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">

			<thead>

				<tr>

					

					<th>Image</th> 
					<th>Baggage Rules</th> 

					<?php if(check_user_previlege('p39')){?>

					<th>Action</th>

					<?php } ?>

				</tr>

				</thead><tbody>

				<?php 

				if(count(@$baggage_rule_list)>0){

				foreach($baggage_rule_list as $key => $fare_rule){ 

					?>

				<tr>
				
				<td><img src="<?php echo $GLOBALS ['CI']->template->domain_ban_images ($fare_rule['image']) ?>" height="250px" width="250px" class="img-thumbnail"></td>
				
				<td><?=$fare_rule['rules']?></td>
					<?php if(check_user_previlege('p39')){?>

					<td>

					<button type="button" class="btn btn-primary update_fare" data-origin="<?=$fare_rule['origin']?>" >Edit</button>

					<button type="button" class="btn btn-danger delete_fare" data-table='flight_crs_baggage_rule' data-origin="<?=$fare_rule['origin']?>">Delete</button>

					</td>

					<?php } ?>

					

				</tr>

				<?php 

				} }else{ ?>

				<tr>

					<td colspan="12"> <strong>No Baggage Rules.</strong></td>

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

  <div class="modal-dialog modal-lg">



    <!-- Modal content-->

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal">&times;</button>

        <h4 class="modal-title" id="title"> Common Baggage Rule</h4>

      </div>

      <div class="modal-body action_details">

      <div class="text-danger" id="err"></div>

   <form class="form-horizontal" role="form" id="fare_rule_frm" enctype="multipart/form-data" method="POST" autocomplete="off" >
   

      <input type="hidden"  name="origin" value="0">

      

      	

         <div class="col-xs-12 col-sm-12">

         	<div class="form-group">

         	<div class="row">				
				
				
         		<div class="col-xs-12 col-sm-12">
         		    <label class="col-sm-6 control-label">Baggage Rules</label>
         		</div>

         		<div class="col-xs-12 col-sm-12">
         			<input type="hidden"  name="origin" value="0">
		            <textarea class="ckeditor" id="editor1" name="rules"><?=$fare_rule['rules']?></textarea>
         		</div>

         	</div>

         	</div>

         </div>

      

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

        
                <script>
                        CKEDITOR.replace( 'editor1' );
                </script>
				

<script type="text/javascript">

 $(document).ready(function(){

	 $('#add_fare').on('click', function(){

	 	$('#title').text('Add Baggage Rule');

	 	$("#err").text("");

	 	$('input[name="origin"]').val('');	

	 	$("#add_fare_rule").modal('show');

	 });
	 // to  Edit and Update the data

	 $('.update_fare').on('click', function(){

	 	$('#title').text('Update Baggage Rule');

	 	var origin =  $(this).data('origin');

	 	var rule =  $(this).data('rule');

	 	$('input[name="origin"]').val(origin);

	 	$("#add_fare_rule").modal('show');

	 	 });
}); 
</script>