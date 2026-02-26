
<?php
//debug($sid); die;

?>

<style type="text/css">
a.act { cursor: pointer;} 
.table{margin-bottom: 0;}
.modal-footer { padding: 10px;}
  .toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
  .toggle.ios .toggle-handle { border-radius: 20px; }
</style>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">
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
				Add Home Base Airport  details
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
			<button type="button" class="btn btn-primary" id="add_fare">Add SID</button>
		<div class="table-responsive">
			<!-- PANEL BODY START -->
			<table class="table table-bordered table-hover" id="tab_flight_list">
			
			<thead>
				<tr>
					<td><i class="fa fa-sort-numeric-asc"></i> S. No.</td>
					<td>Home Base Airport </td>
					<td>Type of base</td>
					<?php if(check_user_previlege('p69')){?><td>Action</td><?php } ?>
					
				</tr>
				</thead><tbody>
				<?php 
				if(count(@$sid)>0){
					
				foreach($sid as $key => $document_detail){ 
				?>
				<tr>
					<td> <?=$key+1?></td>
					<td><?=$document_detail['airport_name'] ?>(<?=$document_detail['airport_code'] ?>)</td>
					<td><?=$document_detail['base'] ?></td>
				
				<?php if(check_user_previlege('p69')){?>
					<td>
					<button type="button" class="btn btn-primary update_fare" 
					data-origin="<?=$document_detail['origin']?>" 
					data-airport_name="<?=$document_detail['airport_name']?>" 
					data-airport_code="<?=$document_detail['airport_code']?>" 
					data-base="<?=$document_detail['base']?>" 
					
					>Edit</button>
					
				
					</td>
				<?php } ?>
				</tr>
				<?php 
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No Airport added.</strong></td>
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

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="title"> Add Airport details</h4>
      </div>
      <div class="modal-body action_details">
      <div class="text-danger" id="err"></div>
      <form method="post" action="" id="meal_detail_frm">
      <input type="hidden"  name="origin" value="0">			
      	<div class="col-xs-12 col-sm-12 fare_info nopad">
		<div class="form-group">         
        <div class="org_row">             
        <div class="col-sm-6">                        
        <div class="radio">                             
        <label for="value_type" class="col-sm-4 control-label">Select Home Base Airport<span class="text-danger">*</span></label>
        <div class="col-md-8">       
        <input type="text" class="wdt25 form-control"  name="airport_name" id="airport_name" required="" >
        </div>                      
        </div>                        
        </div>  
        
        
        <div class="col-sm-6">  
        <div class="radio"> 
        <label for="value_type" class="col-sm-4 control-label">Type of Base<span class="text-danger">*</span></label>    
        <div class="col-sm-4">                   
        <label class="radio-inline">      

		
        <input type="radio" class="crs_is_domestic" id='home' name="base" value="Home">Home
       </label>                  
       <label class="radio-inline">    
       <input type="radio" class="crs_is_domestic" id='layover' name="base" value="Layover">Layover
       </label>      
       </div>                   
       </div>
       </div>
		</div>                           
		  
		</div> 
	</div>
       
      </div>
      <div class="modal-footer">
      	<div class="col-xs-12 col-sm-12">
        <button type="button" class="btn btn-info" id="clear" >Clear</button>
        <button type="submit" class="btn btn-primary" id="save" >Save</button>
        </form>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>

  </div>
</div>
<div id="add_home_airport" class="modal" role="dialog">
  <div class="modal-dialog" style='width: 85%;'>

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="title"> Add Airport details</h4>
      </div>
      <div class="modal-body action_details">
      <div class="text-danger" id="err"></div>
      <form method="post" action="" id="add_home_airport">
      <input type="hidden"  name="origin" value="0">			
      	<div class="col-xs-12 col-sm-12 fare_info nopad">
		<div class="form-group">         
        <div class="org_row">             
        <div class="col-sm-6">                        
        <div class="radio">                             
        <label for="value_type" class="col-sm-4 control-label">Home Base Airport Name<span class="text-danger">*</span></label>
        <div class="col-md-8">       
        <input type="text" class="wdt25 form-control"  name="home_airport_name" id="home_airport_name" required="" >
        </div>                      
        </div>                        
        </div>  
			 <div class="col-sm-6">                        
        <div class="radio">                             
        <label for="value_type" class="col-sm-4 control-label">Home Base Airport Code<span class="text-danger">*</span></label>
        <div class="col-md-8">       
        <input type="text" class="wdt25 form-control"  name="airport_code" id="airport_code" required="" >
        </div>                      
        </div>                        
        </div>  
			 <div class="col-sm-6">                        
        <div class="radio">                             
        <label for="value_type" class="col-sm-4 control-label">Home Base Airport City<span class="text-danger">*</span></label>
        <div class="col-md-8">       
        <input type="text" class="wdt25 form-control"  name="airport_city" id="airport_city" required="" >
        </div>                      
        </div>                        
        </div>  
			 <div class="col-sm-6">                        
        <div class="radio">                             
        <label for="value_type" class="col-sm-4 control-label">Home Base Airport Country<span class="text-danger">*</span></label>
        <div class="col-md-8">       
        <input type="text" class="wdt25 form-control"  name="airport_country" id="airport_country" required="" >
        </div>                      
        </div>                        
        </div>  
        
        
       
		</div>                           
		  
		</div> 
	</div>
       
      </div>
      <div class="modal-footer">
      	<div class="col-xs-12 col-sm-12">
        <button type="button" class="btn btn-info" id="clear" >Clear</button>
        <button type="submit" class="btn btn-primary" id="add_airport" >Save</button>
        </form>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>

  </div>
</div>
    <!-- Script -->
    <script type='text/javascript' >    
     $(document).ready(function(){
		 
		  $('#clear').on('click', function(){
				$('input[id="home"]').prop('checked', false);
				$('input[id="layover"]').prop('checked', false);
		  });
	 // to  Edit and Update the data
	 $('.update_fare').on('click', function(){
	 	$('#title').text('Update Home Base Airport');
	 	$("#add_fare_rule").modal('show');
	 		
	 		
	 	var origin =  $(this).data('origin');
	 	var base =  $(this).data('base');
		var airport_name =  $(this).data('airport_name') ;
	 	var base =  '';
	 	$('input[name="origin"]').val(origin);
	 	$('input[name="airport_name"]').val(airport_name);
		 base =  $(this).data('base');
		if(base =='home')
		{
			$('input[id="home"]').prop('checked', true);
			$('input[id="layover"]').prop('checked', false);
		}
		if(base =='layover')
		{	
			$('input[id="home"]').prop('checked', false);
			$('input[id="layover"]').prop('checked', true);
		}


		
	 	
					
	 });
	 //  delete fare rule
	  $('.delete_fare').on('click', function(){
	          var result = confirm("Want to delete?");
        if (result) {
	 	var origin =  $(this).data('origin');
	 	    $.ajax({
		 		method:'get',
		 		url:app_base_url+'index.php/ajax/delete_sid_details/'+origin,
		 		dataType: 'json',
		 		success:function(data){
		 			location.reload();
		 		    }
		 		});
	       }
	 });
	 
	 
	 
}); 
		
		$('#add_fare').click(function(){
			$('#title').text('Add Home Base Airport');
			$('#meal_detail_frm').trigger('reset');
			$("#add_home_airport").modal('show');
		});
		
		
		$('#add_home_airport').submit(function(e){
			e.preventDefault();
			//alert('hi');
			let airport_name=$('#home_airport_name').val();
			let airport_code=$('#airport_code').val();
			let airport_city=$('#airport_city').val();
			let airport_country=$('#airport_country').val();
			alert(airport_name+" "+airport_code+" "+airport_city+" "+airport_country);
			//return false;
			$.ajax({
		 		method:'post',
		 		url:app_base_url+'index.php/ajax/add_home_airport/',
		 		dataType: 'json',
				data:{airport_name:airport_name,airport_code:airport_code,airport_city:airport_city,airport_country:airport_country},
		 		success:function(data){
					if(data=='10'){
						alert('airport already exists, please try again');
						location.reload();
					}
					else{
						alert('airport added succesfully');
						location.reload();
					}
		 			
		 		    }
		 		});
			
		});
    </script>