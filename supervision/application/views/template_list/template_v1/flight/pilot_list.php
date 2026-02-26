 <?php

$_datepicker = array(array('created_datetime_to', FUTURE_DATE),array('created_datetime_from', PAST_DATE));
$this->current_page->set_datepicker($_datepicker);
$this->current_page->auto_adjust_datepicker(array(array( 'created_datetime_to')));
?>

<style type="text/css">
a.act { cursor: pointer;} 
.table{margin-bottom: 0;}
.modal-footer { padding: 10px;}
  .toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
  .toggle.ios .toggle-handle { border-radius: 20px; }
  .red{
	  color : red;
  }
</style>
<style>.table-responsive { overflow-x: scroll; } </style>


<!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="panel panel-default">
		<!-- PANEL WRAP START -->
		<div class="panel-heading">
			<!-- PANEL HEAD START -->
			<div class="panel-title">
				Pilot List 
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
		<a class="btn btn-primary" href="<?=base_url().'index.php/flight/add_pilot'?>">Add Pilot</a>
		<div class="table-responsive">
			<!-- PANEL BODY START -->
			
			<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<thead>
				<tr>
					<th><i class="fa fa-sort-numeric-asc"></i> S. No.</th>
					<!-- <th>Origin</th> -->
					<th>Pilot Name</th>
					<th>Employee Code</th>
					
					<th>Action</th>
				</tr>
				</thead><tbody>
				<?php 
				if(count(@$pilot_list)>0){
				foreach($pilot_list as $key => $pilot_detail){ 
					?>
				<tr>
					<td> <?=$key+1?></td>
					<td><?=$pilot_detail['first_name'] ?></td>
					<td><?=$pilot_detail['emp_code'] ?></td>
					
					<td>
					<?php if(check_user_previlege('p71')){?>
					<a class="btn btn-primary" href="<?=base_url().'index.php/flight/add_pilot/'.$pilot_detail['origin']?>" >Edit</a>
					<button type="button" class="btn btn-danger delete_fare" data-table="pilot" data-origin="<?=$pilot_detail['origin']?>" >Delete</button>
					<?php } ?>
				
					<a data-toggle="modal" href="<?php echo base_url().'index.php/flight/pilot_licence/'.$pilot_detail['origin'] ?> "	class="btn btn-primary btn-large leave_update_fare" data-backdrop="static"  >View Licence & Checks</a>
					<a data-toggle="modal" href="<?php echo base_url().'index.php/flight/pilot_training/'.$pilot_detail['origin'] ?> "	class="btn btn-primary btn-large leave_update_fare" data-backdrop="static"  >View Training</a>
					
						<a data-toggle="modal" href="<?php echo base_url().'index.php/flight/pilot_leave/'.$pilot_detail['origin'] ?> "	class="btn btn-primary btn-large leave_update_fare" data-backdrop="static"  >View Leaves</a>
					
					<a data-toggle="modal" href="<?php echo base_url().'index.php/flight/fdtl_temp_details/'.$pilot_detail['origin'] ?> "	class="btn btn-warning btn-large leave_update_fare" data-backdrop="static"  >View FDTL temporary Details</a>
					<a data-toggle="modal" href="<?php echo base_url().'index.php/flight/fdtl_details/'.$pilot_detail['origin'] ?> "	class="btn btn-warning btn-large leave_update_fare" data-backdrop="static"  >View FDTL Details</a>
					</td>
					
					
				</tr>
				<?php 
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No Pilots added.</strong></td>
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
  }) */
</script>

<script type="text/javascript">
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

<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>



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
				Pilot Leaves
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
		<?php if(check_user_previlege('p71')){?>
		    <div class="bs-docs-example" style="padding-bottom: 24px;">
       <a data-toggle="modal" href="#myModal" class="btn btn-primary btn-large add_leave" data-backdrop="static" >Add Pilot leaves</a>
       </div>
		<?php } ?>
		<!--button type="button" class="btn btn-primary" id="add_fare"></button-->
		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover table-condensed" id="tab_leave_list">
			<thead>
				<tr>
					<td><i class="fa fa-sort-numeric-asc"></i> S. No.</td>
					<td>Pilot </td>
					<td>Type of Leave</td>
					<td>From</td>
					<td>To</td>
					<?php if(check_user_previlege('p71')){?><td>Action</td><?php } ?>
				</tr>
				</thead><tbody>
				<?php 
				if(count(@$pilot_leaves)>0){
				foreach($pilot_leaves as $key => $document_detail){ 
				?>
				<tr><td> <?=$key+1?></td>
					<td> <?php  foreach($pilots as $pl)
					           {
    					            if((int)$pl['origin'] == (int)$document_detail['pilot_id'])
    					            {
    					                echo $pl['first_name'].' '.$pl['last_name'].' - '.$pl['emp_id'];
										
										$pilot_id = (int)$document_detail['pilot_id'];
										$pilot_name = $pl['first_name'].' '.$pl['last_name'].' - '.$pl['emp_id'];
    					            }
					        }
					        ?>
					</td>
					<td>
					    <?php  foreach($leaves_type as $pl)
					           {
    					            if((int)$pl['origin'] == (int)$document_detail['leave_type'])
    					            {
    					                echo $pl['leave_type_name'];
    					            }
					        }
					        ?>
					</td>
					<td><?=date('d-M-Y',strtotime($document_detail['leave_from'])); ?> </td>
					<td><?=date('d-M-Y',strtotime($document_detail['leave_to'])); ?> </td>
					<?php if(check_user_previlege('p71')){?>
					<td><a data-toggle="modal" href="#myModal"	
							data-origin="<?=$document_detail['origin']?>" 
							data-pilot_id="<?=$pilot_id?>" 
							data-pilot_name="<?=$pilot_name?>" 
							data-leave_type="<?=$document_detail['leave_type']?>" 
							data-leave_from="<?=$document_detail['leave_from']?>" 
							data-leave_to="<?=$document_detail['leave_to']?>" class="btn btn-primary btn-large leave_update_fare" data-backdrop="static"  >Edit</a>
							<button type="button" class="btn btn-danger delete_fare" data-table="pilot_leaves" data-origin="<?=$document_detail['origin']?>" >Delete</button>
					</td>
					<?php } ?>
				</tr>
				<?php 
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No Leaves added.</strong></td>
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
					<form method="post" action="<?php echo base_url();?>index.php/flight/leaves" id="meal_detail_frm" onsubmit='return validate()'>
					<input type="hidden"  name="origin" value="0">			
					<div class="col-xs-12 col-sm-12 fare_info nopad">
						<div class="form-group">         
							<div class="org_row">  
								<div class="col-sm-6">                        
									<div class="radio">                             
										<label for="value_type" class="col-sm-4 control-label">Select Pilot <span class="text-danger">*</span></label>
										<div class="col-md-8">       
											<input name='origin' value='0' type='hidden'>
											<input type="text" class="wdt25 form-control "   id="autocomplete1"  required="" value="" placeholder='Type Pilot Name'>
											<input type='hidden' id='pilot_id' name="pilot_id" />
										</div>                      
									</div>       
								</div>
								<div class="col-sm-6">                        
									<div class="radio">                             
										<label for="value_type" class="col-sm-4 control-label">Leaves<span class="text-danger">*</span></label>
										<div class="col-md-8">       
											<input name='origin' value='0' type='hidden'>
											<select class="form-control component_serial chosen-select" required name='leave_type'>
											 <option value=''>select</option>
											 <?php 
											 foreach($leaves_type as $ct){ 
												  $selected ='';
											 ?>
											 <option value="<?=$ct['origin']?>"><?=$ct['leave_type_name'].'('.$ct["leave_type_code"].')'?></option>    
											  <?php } ?>
											</select>
									  
										</div>                      
									</div>                        
								</div>
							</div>                           
							<div class="org_row"> 
								<div class="col-sm-6">       
									<div class="radio">           
										<label for="value_type" class="col-sm-4 control-label">From<span class="text-danger">*</span></label>
										<div class="col-sm-8">                
											<input type="text" class="wdt25 form-control datepicker" onchange='check_leave()' id='leave_from' name='leave_from'   required readonly />
										</div>                         
									</div>                            
								</div>
								<div class="col-sm-6">         
									<div class="radio">            
										<label for="value_type" class="col-sm-4 control-label">To<span class="text-danger">*</span></label>
										<div class="col-sm-8">      
											<input type="text" class="wdt25 form-control datepicker "  onchange='check_leave()' id='leave_to' name='leave_to' required readonly />      
										</div>                           
									</div>                            
								</div> 
							</div>   
						</div> 
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-xs-12 col-sm-12">
						<p class='error'></p>
						<button type="submit" class="btn btn-primary" id="save" >Submit</button>
						</form>
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>

		</div>
	</div>
<!-- Script -->
<script src='../jquery.js' type='text/javascript'></script>
<link href='jquery-ui.min.css' rel='stylesheet' type='text/css'>
<script src='jquery-ui.min.js' type='text/javascript'></script> 
<!-- Script -->
<script type='text/javascript' >
function check_leave()
{
	leave_from = $('#leave_from').val();
	leave_to = $('#leave_to').val();
	pilot_id = $('#pilot_id').val(); 
	autocomplete1 = $('#autocomplete1').val(); 
	$x =0;
	if( leave_from == '' || leave_to == '' || pilot_id =='' || autocomplete1 == '')
	{
		$('.error').html('<span class="red" >Please Fill All details.</span>')
		return false;
	}
	$.ajax({
		url:app_base_url+'index.php/ajax/check_leave/',
		type: 'post',        
		async:false,
		data: { leave_from: leave_from,leave_to:leave_to,pilot_id:pilot_id},
		success:function(data){ 
		var x = JSON.parse(data)
	
			if(data)
			{
				if(x.already_assigned == 1){
					$('.error').html('<span class="red" >Pilot Duty Already Assigned. Cannot be on leave.</span>')
				}
				else if(x.already_inleave == 1){
					$('.error').html('<span class="red" >Pilot is Already in Leave. Cannot give leave again.</span>')
				}
				 $(this).attr('disabled','disabled');
				
			}
		}
	}); 
	
}
function validate()
{
	leave_from = $('#leave_from').val();
	leave_to = $('#leave_to').val();
	pilot_id = $('#pilot_id').val(); 
	autocomplete1 = $('#autocomplete1').val(); 
	if( leave_from == '' || leave_to == '' || pilot_id =='' || autocomplete1 == '')
	{
		$('.error').html('<span class="red" >Please Fill All details.</span>')
		return false;
	}
}


$(document).ready(function(){
     $( function() {
        $( "#autocomplete1" ).autocomplete({
            source: function( request, response ) {
                url = app_base_url+"index.php/ajax/auto_suggest_pilot";
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
                $('#autocomplete1').val(ui.item.label); // display the selected text
                $('#pilot_id').val(ui.item.value); // save selected id to input
                return false;
            }
        });
    });
    function split( val ) {
      return val.split( /,\s*/ );
    }
    function extractLast( term ) {
      return split( term ).pop();
    }
    
    
 	//  to add meal details and show
	$('.add_leave').on('click', function(){
	 	$('#title').text('Add Leave');
	 	$("#err").text('');
	 	$('input[name="origin"]').val('0');
		$('#autocomplete1').val('');
		$('#autocomplete1').attr('readonly', false); 
		$('#pilot_id').val('');
		$('select[name="leave_type"]').prop('selectedIndex', 0);
	 	$('input[name="leave_from"]').val("");
	 	$('input[name="leave_to"]').val("");
		$('#save').removeAttr('disabled');
	 	
	});
	 
		// to  Edit and Update the data
	$('.leave_update_fare').on('click', function(){
	    $('#title').text('Update Leave');
		var origin =  $(this).data('origin');
		var pilot_id =  $(this).data('pilot_id');
	 	var pilot_name =  $(this).data('pilot_name');
	 	var leave_type =  $(this).data('leave_type');
	 	var leave_from =  $(this).data('leave_from');
	 	var leave_to =  $(this).data('leave_to');
		$('input[name="origin"]').val(origin);
	 	$('#autocomplete1').val(pilot_name);
	 	$('#pilot_id').val(pilot_id);
	 	$('#autocomplete1').attr('readonly', true); 
		$('select[name="leave_type"]').val(leave_type);
	 	$('input[name="leave_from"]').val(leave_from);
	 	$('input[name="leave_to"]').val(leave_to);
	});
	 
}); 

$(function(){
	$( ".datepicker" ).datepicker({
		dateFormat: "dd-mm-yy" , 
		minDate: 0 ,
		changeMonth: true, 
		changeYear: true,
		numberOfMonths: 2,
	});
});
    </script>
    
    
    
