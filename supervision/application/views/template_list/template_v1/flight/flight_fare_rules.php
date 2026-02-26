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
				Flight Fare Rules List 
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
			<?php if(check_user_previlege('p39')){?>
		<button type="button" class="btn btn-primary" id="add_fare">Add Fare Rules</button>
			<?php } ?> 
 		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<thead>
				<tr>
					<th><i class="fa fa-sort-numeric-asc"></i> S. No.</th>
					<th>Airline</th>
					<th>Type</th>
					<th>Fare Rules</th> 
					<?php if(check_user_previlege('p39')){?>
					<th>Action</th>
					<?php } ?>
				</tr>
				</thead><tbody>
				<?php 
				if(count(@$fare_rule_list)>0){
				foreach($fare_rule_list as $key => $fare_rule){ 
					// if($flight_list_details['active']==1)
					// 	$chk = "checked";
					// else
					// 	$chk = "";
					?>
				<tr>
					<td> <?=$key+1?></td>
					<!-- <td><?=$fare_rule['origin']?></td> -->
					<td><?=$fare_rule['carrier_name']?></td>
					<!-- <td><?=$fare_rule['carrier_code']?></td> -->
					<td><?=($fare_rule['is_domestic'] == 0)?'Domestic':'International'?></td>
					<td><?=$fare_rule['fare_rule']?>
					</td>
					
					
					<?php if(check_user_previlege('p39')){?>
					<td>
					<button type="button" class="btn btn-primary update_fare" data-origin="<?=$fare_rule['origin']?>" data-airline="<?php echo $fare_rule['carrier_name'].'('.$fare_rule['carrier_code'].')'; ?>" data-isdomestic="<?=$fare_rule['is_domestic']?>" data-rule='<?=$fare_rule['fare_rule']?>' >Edit</button>
					<button type="button" class="btn btn-danger delete_fare" data-origin="<?=$fare_rule['origin']?>">Delete</button>
					</td>
					<?php } ?>
					
				</tr>
				<?php 
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No flight fare rules.</strong></td>
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
        <h4 class="modal-title" id="title"> Add Fare Rule</h4>
      </div>
      <div class="modal-body action_details">
      <div class="text-danger" id="err"></div>
      <form method="post" action="<?php echo base_url().'index.php/flight/add_fare_rule'; ?>" id="fare_rule_frm">
      <input type="hidden"  name="origin" value="0">
      	<div class="col-xs-12 col-sm-12">
	      	<div class="form-group">
	            <div class="col-sm-6">
	                <label class="radio-inline">
	                    <input type="radio" class="crs_is_domestic" name="is_domestic"  value="0">Domestic
	                </label>
	                <label class="radio-inline">
	                    <input type="radio" class="crs_is_international" name="is_domestic" value="1">International
	                </label>
	            </div>
	        </div>
        </div>
      	<div class="col-xs-12 col-sm-12">
        	<div class="form-group">
        		<div class="col-sm-6">
            	<label form="user" for="title" class="col-sm-6 control-label">Airline Code</label>       
            	</div>
            	<div class="col-sm-6"> 
            		<input type="text" class="form-control tags" required placeholder="SG" name="carrier_code"  maxlength='15' />
            	</div>
         	</div>
         </div>
         <div class="col-xs-12 col-sm-12">
         	<div class="form-group">
         	<div class="row">
         		<div class="col-xs-12 col-sm-12">
         		<label class="col-sm-6 control-label">Fare Rules</label>
         		</div>
         		<div class="col-xs-12 col-sm-12">
         		<textarea class="ckeditor" id="editor" name="fare_rule"
								rows="10" cols="80"></textarea>
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
 	
	/*  $('#tab_flight_list').DataTable({
		  "searching": true,
		  "paging" : false
	});*/
	
 	//  to add fare rule and show
	 $('#add_fare').on('click', function(){
	 	$('#title').text('Add Fare Rule');
	 	$("#err").text("");
		
		
	 	$('input[class="crs_is_domestic"]').val('0').attr('disabled', false);
		$('input[class="crs_is_international"]').val('1').attr('disabled', false);
	 	$('input[name="origin"]').val('');
		$('input[name="is_domestic"]'). prop("checked", false);
		
	 	$('input[name="carrier_code"]').val('').removeAttr('readonly');
	 	CKEDITOR.instances['editor'].setData('');
	 	$("#add_fare_rule").modal('show');
	 });
 
	 // to  Edit and Update the data
	 $('.update_fare').on('click', function(){
	 	$('#title').text('Update Fare Rule');
	 	var origin =  $(this).data('origin');
	 	var isdomestic =  $(this).data('isdomestic');
	 	var airline =  $(this).data('airline');
	 	var rule =  $(this).data('rule');
	 	$("#add_fare_rule").modal('show');
		
		

		$( 'input[name="is_domestic"]').each(function( index ) {
		if($( this ).val()  == isdomestic){
		var radioclass =  $( this ).attr('class');
		$('.'+radioclass).prop("checked", true);
		}
	});
		
		$('input[name="is_domestic"]').attr('disabled', true);		
	 	$('input[name="origin"]').val(origin);
	 	$('input[name="carrier_code"]').val(airline).attr('readonly', true);
	 	CKEDITOR.instances['editor'].setData(rule);
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

	 // check exixts
	 $("#save").on('click', function(e){
	 	e.preventDefault();
	 	
		
		//var radioValue = $("input[name='gender']:checked"). val();
		
	 	var dome = $('input[name="is_domestic"]:checked').val();
	 	var a = $('input[name="carrier_code"]').val();
	 	var o = $('input[name="origin"]').val();
		
		
	 	var airline = a.trim()
	 	var origin = o.trim()
	if(airline && (dome == 1 || dome == 0))
	{	
		//if(airline.includes("("))
		//{

			if(origin == 0) { 		
				$.ajax({
					method:'post',
					url:app_base_url+'index.php/flight/check_fare_rule',
					data:{is_domestic:dome,carrier_code:airline},
					dataType: 'json',
					success:function(data){
						if(data.status == false) {
							$("#err").text(data.msg);
						} else {
							$("#fare_rule_frm").submit();
						}
					}
					});
			} else {
				$("#fare_rule_frm").submit();
			}
			
		/*}
		else
		{
				$("#err").text('Please Select Airline Code ');
		}*/
	}
	else{
			$("#err").text('Please fill all details');
	}
		
		
	 });


	 // show flight list in the form
	  $(".crs_is_domestic").change(function(){
		   var is_domestic = $('input[name=is_domestic]:checked').val();
         $("#returnflightinfo").hide(500);
         if(is_domestic == 1){
            $("#triptype").show(500);
            $('#triptype').each(function(){
               $('input[type=radio]', this).get(0).checked = true;
            });
         }else{
            $("#triptype").hide(500);
         }

		   //$.ajax({url: app_base_url + "index.php/ajax/get_airline_list/"+is_domestic, success: function(result){
		   $.ajax({url: app_base_url + "index.php/ajax/get_flight_crs_airline_list/"+is_domestic, success: function(result){
				
			   var obj = JSON.parse(result);

			   var availableTags = new Array();
			   var availableTags_obj = new Array();
			   availableTags_obj = obj.airline;
			   for(i=0;i<obj.airline.length;i++){
				   availableTags.push(availableTags_obj[i]); 
				}
		
			   $( ".tags" ).autocomplete({
			      source: availableTags
			   });
			   
			   
			   
			  $('input[name="carrier_code"]').attr('readonly', false);

		   }});
	   });
	   $( window ).load(function() {
		  var is_domestic = $('input[name=is_domestic]:checked').val();
		  $.ajax({url: app_base_url + "index.php/ajax/get_airline_list/"+is_domestic, success: function(result){
				
			   var obj = JSON.parse(result);

			   var availableTags = new Array();
			   var availableTags_obj = new Array();
			   availableTags_obj = obj.airline;
			   for(i=0;i<obj.airline.length;i++){
				
				   availableTags.push(availableTags_obj[i]); 
				 }
		
			  $( ".tags" ).autocomplete({
			      source: availableTags
			    });
			   
		    }});
		});

    $(".act").click(function(){
    var adult_basefare = $(this).data('adult_basefare'); 
    var adult_tax = $(this).data('adult_tax');
    var child_basefare = $(this).data('child_basefare');
    var child_tax = $(this).data('child_tax');
    var infant_basefare = $(this).data('infant_basefare');
    var infant_tax = $(this).data('infant_tax');

    var total_adult_fare = adult_basefare + adult_tax;
    var total_child_fare = child_basefare + child_tax;
    var total_infant_fare = infant_basefare + infant_tax;
    	
    
    var str = '<table class="table table-bordered"><thead><tr><th></th><th>Adult</th><th>Infant</th><th>Child</th></tr></thead><tbody><tr><td><strong>Base Fare</strong></td><td>'+adult_basefare+'</td><td>'+child_basefare+'</td><td>'+infant_basefare+'</td></tr><tr><td><strong>Tax</strong></td><td>'+adult_tax+'</td><td>'+child_tax+'</td><td>'+infant_tax+'</td></tr><tr><td><strong>Total Fare</strong></td><td>'+total_adult_fare+'</td><td>'+total_child_fare+'</td><td>'+total_infant_fare+'</td></tr></tbody></table>';
    $('.fare_data').html(str);
    $('#dynamic_text').text('Fare Details');
	$("#action").modal('show'); 
    });
    
    $(".flight_details").click(function(){
        var fsid = $(this).data('fsid'); 
        $.ajax({url: "<?= base_url();?>index.php/flight/get_flight_details/"+fsid, success: function(result){
        	
           var res = JSON.parse(result);
         //  alert(result)
           if(res.length>0){
        	 
        	   var flight_data = '';
        	   var flight_data = '<table class="table table-bordered"><thead><tr><th>#SL</th><th>Origin</th><th>Destination</th><th>Deparature From Date</th><th>Deparature To Date</th><th>Flight Num</th><th>Carrier code</th><th>Airline Name</th><th>Class</th><th>Trip Type</th></tr></thead><tbody>';
				for(var i=0;i<res.length;i++){
					flight_data +='<tr><td>'+parseInt(i+1)+'</td><td>'+res[i]['origin']+'</td><td>'+res[i]['destination']+'</td><td>'+res[i]['departure_date_from']+' ' + res[i]['departure_time']+'</td><td>'+res[i]['departure_date_to']+' ' + res[i]['arrival_time']+'</td><td>'+res[i]['flight_num']+'</td><td>'+res[i]['carrier_code']+'</td><td>'+res[i]['airline_name']+'</td><td>'+res[i]['class_type']+'</td><td>'+((res[i]['trip_type']==0)?"Onward":"Return")+'</td></tr>';
				}	
				flight_data +='</tbody></table>';
           }
           $('.fare_data').html(flight_data);
           $('#dynamic_text').text('Flight Details'); 
           $("#action").modal('show'); 
        
        }});
       // var str='<table class="table table-bordered"><thead><tr><th></th><th>Origin</th><th>Destination</th><th>Flight Num</th><th>Carrier code</th><th>Airline Name</th></tr></thead><tbody>";
        // </tbody></table>';
       // $('.dyn_data').html(str);
    	//$("#action").modal('show'); 
    	//$(".dyn_title").text("Flight Details");
        });
 
  
}); 
</script>
