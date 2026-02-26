<style type="text/css">
a.act { cursor: pointer;} 
.table{margin-bottom: 0;}
.modal-footer { padding: 10px;}
  .toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
  .toggle.ios .toggle-handle { border-radius: 20px; }
</style>
<script type="text/javascript">


function save()
{
	alert('here')
	return false;
}
</script>
<style>.table-responsive { overflow-x: scroll; } </style>
<!--script src="http://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<link href="http://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
<script type="text/javascript">
$(document).ready( function () {
    $('#tab_flight_list').DataTable({"paging" : false});
});
</script-->
<!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="panel panel-default">
		<!-- PANEL WRAP START -->
		<div class="panel-heading">
			<!-- PANEL HEAD START -->
			<div class="panel-title">
				Journey Log
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
			<?php if(check_user_previlege('p66')){?>
		<button type="button" class="btn btn-primary" id="add_fare">Add Journey Log</button>
			<?php } ?>
		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<thead>
				<tr>
					<td><i class="fa fa-sort-numeric-asc"></i> S. No.</td>
					<td>A/C Reg.</td>
					<td>Total Landings Brought Forward </td>
					<td>AIRFRAME Hours Brought Forward </td>
					<td>Date </td>
					<td>JLB Serial Number</td>
					
					<td>Action</td>
					
				</tr>
				</thead><tbody>
				<?php 
				if(count(@$journey_list)>0){
				foreach($journey_list as $key => $document_detail){ 
				?>
				<tr>
					<td> <?=$key+1?></td>
					<td><p>
					
					<?php foreach($regno as $key => $rg) {
					     if($rg['origin'] == $document_detail['reg_number'])
                          {
                              echo $rg['reg'];
                          }
                         } ?>
                    </td>
					<td><?=$document_detail['total_landings_brought_forward'] ?></td>
					<td><?=$document_detail['airframe_hours_brought_forward'] ?></td>
					<td><?=date('d-M-Y',strtotime($document_detail['date'])); ?></td>
					<td><?=$document_detail['jlb_serial_number'] ?></td>
					
					<td>
					<?php if(check_user_previlege('p66')){?>
					<button type="button" class="btn btn-primary update_fare" data-origin="<?=$document_detail['origin']?>" data-reg="<?=$document_detail['reg_number']?>" data-landing="<?=$document_detail['total_landings_brought_forward']?>"  data-airframe="<?=$document_detail['airframe_hours_brought_forward']?>"  data-date="<?=$document_detail['date']?>"  data-jlb="<?=$document_detail['jlb_serial_number']?>">Edit</button>
				   
					<button type="button" class="btn btn-danger delete_fare" data-table='journey_log' data-origin="<?=$document_detail['origin']?>" >Delete</button>
					<?php } ?>
					<br>   
					<a type="button" class="btn btn-info"  href="<?php base_url() ?>add_jlb_details?origin=<?=$document_detail['origin']?>&reg=<?=$document_detail['reg_number']?>&date=<?=$document_detail['date']?>" >Add JLB Details</a>
					<br>
					
					</td>
					
				</tr>
				<?php 
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No Journey log added.</strong></td>
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
  <div class="modal-dialog" style='width: 85%;'>

    <!-- Modal content-->
    <div class="modal-content">
      <form method="post" action="" id="meal_detail_frm" >
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="title"> Add Document Name</h4>
      </div>
      <div class="modal-body action_details">
      <div class="text-danger" id="err"></div>
      <input type="hidden"  name="origin" value="0">
      	<div class="col-xs-12 col-sm-12 fare_info nopad">
		<div class="form-group">         
			<div class="org_row">             
					<div class="col-sm-6">                        
						<div class="radio">                             
						<label for="value_type" class="col-sm-4 control-label">Reg Number<span class="text-danger">*</span></label>
							<div class="col-md-8">       
							
							
							<select class="form-control" id="" name="reg_number" required >      
                                      <option value="">select</option>    
                                      <?php foreach($regno as $key => $rg) { ?>                     
                                      <option value="<?=$rg['origin']?>"><?=$rg['reg']?></option>    
                                      <?php } ?>
                                    </select>
                                                      
							</div>                      
						</div>                        
					</div>                        
				<div class="col-sm-6">         
					<div class="radio">              
						<label for="value_type" class="col-sm-4 control-label">Total Landings Brought Forward<span class="text-danger">*</span></label>
						<div class="col-md-8">                        
						<input type="text" class="wdt25 form-control numeric" name="total_landings_brought_forward" required="" maxlength="10" value="">
						</div>                 
					</div>                     
				</div>                         
			</div>                           
		<div class="org_row">             
		<div class="col-sm-6">       
		<div class="radio">           
		<label for="value_type" class="col-sm-4 control-label">AIRFRAME Hours Brought Forward<span class="text-danger">*</span></label>
		<div class="col-sm-8">                
		<input type="text" class="wdt25 form-control " name="airframe_hours_brought_forward" required="" value="" maxlength="10">
		</div>                         
		</div>                            
		</div>                             
		<div class="col-sm-6">                    
		<div class="radio">                             
		<label for="value_type" class="col-sm-4 control-label">Date<span class="text-danger">*</span></label>
		<div class="col-sm-8">                  
	        <input type="text" id="txtDateRow" class="wdt25 form-control" name="date" readonly required />
		</div>          
		</div>       
		</div>         
		</div>         
		<div class="org_row"> 
		<div class="col-sm-6">         
		<div class="radio">            
		<label for="value_type" class="col-sm-4 control-label">JLB Serial Number<span class="text-danger">*</span></label>
		<div class="col-sm-8">      
		<input type="text" class="wdt25 form-control " name="jlb_serial_number" required maxlength="10" />       
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
       $("#txtDateRow").datepicker({ numberOfMonths:[1,2],dateFormat: 'dd-mm-yy' });
   });

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
	 	$('#title').text('Add Journey Log');
	 	$("#err").text('');
	 	$('input[name="origin"]').val('');
	 	$('input[name="document_name"]').val('');
	 	$("#add_fare_rule").modal('show');
	 });
	 
	 	 
	 // to  Edit and Update the data
	 $('.update_fare').on('click', function(){
	 	$('#title').text('Update Document Name');
	 	var origin =  $(this).data('origin');
	 	var reg_number =  $(this).data('reg');
	 	var landing =  $(this).data('landing');
	 	var airframe =  $(this).data('airframe');
	 	var date =  $(this).data('date');
	 	var jlb =  $(this).data('jlb');
	 	$("#add_fare_rule").modal('show');
	 	$('input[name="origin"]').val(origin);
	 	$('select[name="reg_number"] option[value='+reg_number+']').attr("selected","selected");
	 	$('input[name="total_landings_brought_forward"]').val(landing);
	 	$('input[name="airframe_hours_brought_forward"]').val(airframe);
	 	$('input[name="date"]').val(date);
	 	$('input[name="jlb_serial_number"]').val(jlb);
	 });
}); 
</script>
