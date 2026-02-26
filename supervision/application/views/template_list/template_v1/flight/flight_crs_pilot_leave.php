<style type="text/css">
a.act { cursor: pointer;} 
.table{margin-bottom: 0;}
.modal-footer { padding: 10px;}
  .toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
  .toggle.ios .toggle-handle { border-radius: 20px; }
</style>
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
				Pilot Leaves <a class="btn btn-info"  href="<?php echo base_url() ?>index.php/flight/pilot_list" >Back</a>
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
		    <div class="bs-docs-example" style="padding-bottom: 24px;">
		      Pilot Name :  <?=$pilots[0]['first_name']?> <?=$pilots[0]['last_name']?>
		        
					        
       <!--a data-toggle="modal" href="#myModal" class="btn btn-primary btn-large add_leave" data-backdrop="static" >Add Pilot leaves</a-->
       </div>
		<!--button type="button" class="btn btn-primary" id="add_fare"></button-->
		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<thead>
				<tr>
					<td><i class="fa fa-sort-numeric-asc"></i> S. No.</td>
				
					<td>Type of Leave </td>
					<td>From</td>
					<td>To</td>
					<td>Status</td>
					<td>Action</td>
				</tr>
				</thead><tbody>
				<?php 
				if(count(@$pilot_leaves)>0){
				foreach($pilot_leaves as $key => $document_detail){ 
				    
				     
                     
				?>
				<tr>
					<td><?=$key+1?></td>
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
					<td><?=date("d-m-Y", strtotime($document_detail['leave_from']))?></td>
					<td><?=date("d-m-Y", strtotime($document_detail['leave_to']))?></td>
						<?php
					$current = strtotime(date("Y-m-d"));
                     $date    = strtotime($document_detail['leave_to']);
                    
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
					data-origin="<?=$document_detail['origin']?>" 
					data-leave_type="<?=$document_detail['leave_type']?>" 
					data-leave_from="<?=$document_detail['leave_from']?>" 
					data-leave_to="<?=$document_detail['leave_to']?>" 
					data-pilot_id="<?=$document_detail['pilot_id']?>" 
					class="btn btn-primary btn-large leave_update_fare" data-backdrop="static"  >Edit</a>
					<button type="button" class="btn btn-danger delete_fare" data-table='pilot_leaves' data-origin="<?=$document_detail['origin']?>" >Delete</button>
				
					</td>
					
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
      <form method="post" action="<?php echo base_url();?>index.php/flight/leaves" id="meal_detail_frm">
      <input type="hidden"  name="origin" value="0">
      <input name='pilot_id' value='0' type='hidden'>
      	<div class="col-xs-12 col-sm-12 fare_info nopad">
		<div class="form-group">         
        <div class="org_row">  
        
	
		
        <div class="col-sm-6">                        
        <div class="radio">                             
        <label for="value_type" class="col-sm-4 control-label">Leaves<span class="text-danger">*</span></label>
        <div class="col-md-8">       
            
            
           <select class="form-control component_serial chosen-select" name='leave_type'>
             <option >select</option>
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
        <input type="text" class="wdt25 form-control datepicker"  name='leave_from'   requried readonly/>
        </div>                         
        </div>                            
        </div>
		<div class="col-sm-6">         
		<div class="radio">            
		<label for="value_type" class="col-sm-4 control-label">To<span class="text-danger">*</span></label>
		<div class="col-sm-8">      
		<input type="text" class="wdt25 form-control datepicker"  name='leave_to' requried readonly/>      
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
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script>
	$( function() {
		$( ".datepicker" ).datepicker({
			numberOfMonths: 2
		});
	} );
	</script>
  

<script>
      $(document).ready(function(){
 	//  to add meal details and show
	 $('.add_leave').on('click', function(){
	 	$('#title').text('Add Leave');
	 	$("#err").text('');
	 	$('input[name="origin"]').val('0');
	 	$('select[name="leave_type"]').val('');
	 	$('input[name="leave_from"]').val("");
	 	$('input[name="leave_to"]').val("");
	 	
	 });
	 
	 // to  Edit and Update the data
	 $('.leave_update_fare').on('click', function(){
	     console.log('here');
	 	$('#title').text('Update Leave');
	 	var origin =  $(this).data('origin');
	 	var leave_type =  $(this).data('leave_type');
	 	var leave_from =  $(this).data('leave_from');
	 	var leave_to =  $(this).data('leave_to');
	 	var pilot_id =  $(this).data('pilot_id');
	  	$('input[name="origin"]').val(origin);
	 	$('select[name="leave_type"]').val(leave_type);
	 	$('input[name="leave_from"]').val(leave_from);
	 	$('input[name="leave_to"]').val(leave_to);
	 	$('input[name="pilot_id"]').val(pilot_id);
	 });

	 
	 
});

	</script>
