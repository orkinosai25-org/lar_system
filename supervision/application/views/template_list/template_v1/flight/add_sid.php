
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
</style>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
<script type="text/javascript">
$(document).ready( function () {
    $('#tab_flight_list').DataTable({"paging" : false});
});
</script>
<!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="panel panel-default">
		<!-- PANEL WRAP START -->
		<div class="panel-heading">
			<!-- PANEL HEAD START -->  
			<div class="panel-title">
				Supplemental Structural Inspection Programme Inspections
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
	<?php if(check_user_previlege('p68')){?>
		<button type="button" class="btn btn-primary" id="add_fare">Add SID</button>
		
		<?php } ?>
		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<thead>
				<tr>
					<td><i class="fa fa-sort-numeric-asc"></i> S. No.</td>
					<td>Nomenclature </td>
					<td>SID Number</td>
					<td>Done At</td>
					<td>Due At</td>
					<?php if(check_user_previlege('p68')){?><td>Action</td><?php } ?>
					
				</tr>
				</thead><tbody>
				<?php 
				if(count(@$sid)>0){
				foreach($sid as $key => $document_detail){ 
				?>
				<tr>
					<td> <?=$key+1?></td>
					<td><?=$document_detail['nomenclature'] ?></td>
					<td><?=$document_detail['sid_number'] ?></td>
					<td><?=$document_detail['doneat'] ?></td>
					<td><?=$document_detail['dueat'] ?></td>
				<?php if(check_user_previlege('p68')){?>
					<td>
					<button type="button" class="btn btn-primary update_fare" 
					data-origin="<?=$document_detail['origin']?>" 
					data-nomenclature="<?=$document_detail['nomenclature']?>" 
					data-sid_number="<?=$document_detail['sid_number']?>" 
					data-doneat="<?=$document_detail['doneat']?>" 
					data-dueat="<?=$document_detail['dueat']?>" 
					>Edit</button>
					<button type="button" class="btn btn-danger delete_fare" data-table="structural_inspection_programme" data-origin="<?=$document_detail['origin']?>" >Delete</button>
				
					</td>
				<?php } ?>
				</tr>
				<?php 
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No SID added.</strong></td>
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



<div id="add_fare_rule" class="modal" role="dialog">
  <div class="modal-dialog" style='width: 85%;'>
    <form method="post" action="" id="meal_detail_frm">

    <!-- Modal content-->
		<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="title"> Add JLB details</h4>
      </div>
      <div class="modal-body action_details">
      <div class="text-danger" id="err"></div>
      <input type="hidden"  name="origin" value="0">			
      	<div class="col-xs-12 col-sm-12 fare_info nopad">
		<div class="form-group">         
        <div class="org_row">             
        <div class="col-sm-6">                        
        <div class="radio">                             
        <label for="value_type" class="col-sm-4 control-label">Nomenclature<span class="text-danger">*</span></label>
        <div class="col-md-8">       
        <input type="text" class="wdt25 form-control" name="nomenclature" id="nomenclature" required maxlength='15' minlength='3'>
        </div>                      
        </div>                        
        </div>     
        <div class="col-sm-6">       
        <div class="radio">           
        <label for="value_type" class="col-sm-4 control-label">SID Number<span class="text-danger">*</span></label>
        <div class="col-sm-8">                
        <input type="text" class="wdt25 form-control numeric" name="sid_number"  id="sid_number"  required   maxlength='14' minlength='3'>
        </div>                         
        </div>                            
        </div>
		</div>                           
		<div class="org_row"> 
		<div class="col-sm-6">         
		<div class="radio">            
		<label for="value_type" class="col-sm-4 control-label">Done At<span class="text-danger">*</span></label>
		<div class="col-sm-8">      
		<input type="text" class="wdt25 form-control " name="doneat" id="created_datetime_from" required readonly />      
		</div>                           
		</div>                            
		</div> 
		<div class="col-sm-6">         
		<div class="radio">            
		<label for="value_type" class="col-sm-4 control-label">Due At<span class="text-danger">*</span></label>
		<div class="col-sm-8">      
		<input type="text" class="wdt25 form-control " name="dueat" id="created_datetime_to" required readonly />      
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
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>

    </form>
  </div>
</div>

<script type="text/javascript">

$(document).ready(function(){   
     $("#txtDateRow").datepicker({ numberOfMonths:[1,2] });
});
</script>

    <!-- Script -->
    <script src='../jquery.js' type='text/javascript'></script>

    <link href='jquery-ui.min.css' rel='stylesheet' type='text/css'>
    <script src='jquery-ui.min.js' type='text/javascript'></script>

    


    <!-- Script -->
    <script type='text/javascript' >
  $( function() {
  
        $( "#autocomplete" ).autocomplete({
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
                $('#autocomplete').val(ui.item.label); // display the selected text
                $('#selectuser_id').val(ui.item.value); // save selected id to input
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
    
    
     $(document).ready(function(){
 	//  to add meal details and show
	 $('#add_fare').on('click', function(){
	 	$('#title').text('Add SID');
	 	$("#err").text('');
	 	$('input[name="origin"]').val('0');
	 	$('input[name="nomenclature"]').val("");
	 	$('input[name="sid_number"]').val("");
	 	$('input[name="doneat"]').val("");
	 	$('input[name="dueat"]').val("");
	 	
	 	
	 	$("#add_fare_rule").modal('show');
	 });
	 
	 // to  Edit and Update the data
	 $('.update_fare').on('click', function(){
	 	$('#title').text('Update SID');
	 	$("#add_fare_rule").modal('show');
	 		
	 		
	 			
	 	var origin =  $(this).data('origin');
	 	var nomenclature =  $(this).data('nomenclature');
	 	var sid_number =  $(this).data('sid_number');
	 	var doneat =  $(this).data('doneat');
	 	var dueat =  $(this).data('dueat');
	 
	 	
	 	$('input[name="origin"]').val(origin);
	 	$('input[name="nomenclature"]').val(nomenclature);
	 	$('input[name="sid_number"]').val(sid_number);
	 	$('input[name="doneat"]').val(doneat);
	 	$('input[name="dueat"]').val(dueat);
					
	 });

	 
	 
}); 
    </script>