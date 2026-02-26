<?php
//debug($data);
?>
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
				Flight Meal Details List 
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
		<button type="button" class="btn btn-primary" id="add_fare">Add Meal Details</button>
		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
			<thead>
				<tr>
					<th><i class="fa fa-sort-numeric-asc"></i> SNo</th>
					<!-- <th>Origin</th> -->
					<!-- <th>Airline</th> -->
					<!-- <th>Airline Code</th> -->
					<!-- <th>Type</th> -->
					<th>Meal Name</th> 
					<th>Price</th> 
					<th>Action</th>
				</tr>
				</thead><tbody>
				<?php 
				if(count(@$meal_detail_list)>0){
				foreach($meal_detail_list as $key => $meal_detail){ 
					// if($flight_list_details['active']==1)
					// 	$chk = "checked";
					// else
					// 	$chk = "";
					?>
				<tr>
					<td> <?=$key+1?></td>
					<!-- <td><?=$meal_detail['origin']?></td> -->
					<!-- <td><?=$meal_detail['carrier_name'].'('.$meal_detail['carrier_code'].')'?></td> -->
					<!-- <td><?=$meal_detail['carrier_code']?></td> -->
					<!-- <td><?=($meal_detail['is_domestic']==0)?'domestic':'international'?></td> -->
					<td><?=$meal_detail['meal_name']?>
					<td><?=$meal_detail['price']?>
					</td>
					<td>
					<button type="button" class="btn btn-primary update_fare" data-origin="<?=$meal_detail['origin']?>" data-airline="<?php echo $meal_detail['carrier_name'].'('.$meal_detail['carrier_code'].')'; ?>" data-isdomestic="<?=$meal_detail['is_domestic']?>" data-name="<?=$meal_detail['meal_name']?>" data-price="<?=$meal_detail['price']?>" >Edit</button>
					<button type="button" class="btn btn-danger delete_fare" data-origin="<?=$meal_detail['origin']?>" >Delete</button><!-- <a class="act" data-adult_basefare="<?=$flight_list_details['adult_basefare']?>" data-adult_tax="<?=$flight_list_details['adult_tax']?>" data-child_basefare="<?=$flight_list_details['child_basefare']?>" data-child_tax="<?=$flight_list_details['child_tax']?>" data-infant_basefare="<?=$flight_list_details['infant_basefare']?>" data-infant_tax="<?=$flight_list_details['infant_tax']?>" >Fare Details</a> <br> <a class="flight_details" data-fsid="<?=$flight_list_details['fsid']?>" href="javascript::void(0)" >Flight Details</a> --></td>
					
				</tr>
				<?php 
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No meal details.</strong></td>
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
        <h4 class="modal-title" id="title"> Add Meal Detail</h4>
      </div>
      <div class="modal-body action_details">
      <div class="text-danger" id="err"></div>
      <form method="post" action="<?php echo base_url().'index.php/flight/add_meal_details'; ?>" id="meal_detail_frm">
      <input type="hidden"  name="origin" value="0">
      	<!-- <div class="col-xs-12 col-sm-12">
	      	<div class="form-group">
	            <div class="col-sm-6">
	                <label class="radio-inline">
	                    <input type="radio" class="crs_is_domestic" name="is_domestic" checked value="0">Domestic
	                </label>
	                <label class="radio-inline">
	                    <input type="radio" class="crs_is_domestic" name="is_domestic" value="1">International
	                </label>
	            </div>
	        </div>
        </div> -->
      <!-- 	<div class="col-xs-12 col-sm-12">
        	<div class="form-group">
        		<div class="col-sm-6">
            	<label form="user" for="title" class="col-sm-6 control-label">Airline Code</label>       
            	</div>
            	<div class="col-sm-6">
            		<input type="text" class="form-control tags" placeholder="SG" name="carrier_code" required/>
            	</div>
         	</div>
         </div> -->
         <div class="col-xs-12 col-sm-12">
        	<div class="form-group">
        		<div class="col-sm-6">
            	<label form="user" for="title" class="col-sm-6 control-label">Meal Name</label>       
            	</div>
            	<div class="col-sm-6">
            		<input type="text" class="form-control" placeholder="Meal" name="meal_name" required/>
            	</div>
         	</div>
         </div>
         <div class="col-xs-12 col-sm-12">
        	<div class="form-group">
        		<div class="col-sm-6">
            	<label form="user" for="title" class="col-sm-6 control-label">Price</label>       
            	</div>
            	<div class="col-sm-6">
            		<input type="text" class="form-control" placeholder="Price" name="price" required/>
            	</div>
         	</div>
         </div>
      	<!-- <div class="table-responsive fare_data dyn_data">          
		 
		</div> -->
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
	 	$('#title').text('Add Meal Details');
	 	$("#err").text('');
	 	$('input[name="is_domestic"]').val('');
	 	$('input[name="origin"]').val('');
	 	$('input[name="carrier_code"]').val('');
	 	$('input[name="meal_name"]').val('');
	 	$('input[name="price"]').val('');
	 	$("#add_fare_rule").modal('show');
	 });

	 // to  Edit and Update the data
	 $('.update_fare').on('click', function(){
	 	$('#title').text('Update Meal Details');
	 	var origin =  $(this).data('origin');
	 	var isdomestic =  $(this).data('isdomestic');
	 	var airline =  $(this).data('airline');
	 	var name =  $(this).data('name');
	 	var price =  $(this).data('price');

	 	$("#add_fare_rule").modal('show');
	 	$('input[name="is_domestic"]').val(isdomestic);
	 	$('input[name="origin"]').val(origin);
	 	$('input[name="carrier_code"]').val(airline);
	 	$('input[name="meal_name"]').val(name);
	 	$('input[name="price"]').val(price);
	 	// $('#editor').setData( '<h1>Your HTML</h1>' );
	 });
	 //  delete fare rule
	  $('.delete_fare').on('click', function(){
	 	var origin =  $(this).data('origin');
	 	$.ajax({
		 		method:'get',
		 		url:app_base_url+'index.php/flight/delete_meal_details/'+origin,
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
	/* $("#save").on('click', function(e){
	 	e.preventDefault();
	 	
	 	var dome = $('input[name="is_domestic"]').val();
	 	var airline = $('input[name="carrier_code"]').val();
	 	var origin = $('input[name="origin"]').val();

	 	if(origin == 0) { 		
		 	$.ajax({
		 		method:'post',
		 		url:app_base_url+'index.php/flight/check_meal_detail',
		 		data:{is_domestic:dome,carrier_code:airline},
		 		dataType: 'json',
		 		success:function(data){
		 			if(data.status == false) {
		 				$("#err").text(data.msg);
		 			} else {
		 				$("#meal_detail_frm").submit();
		 			}
		 		}
		 		});
	 	} else {
	 		$("#meal_detail_frm").submit();
	 	}
	 });*/

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
 
    $('#tab_flight_list').DataTable({
		  "searching": true,
		  "paging" : false
	});
}); 
</script>
