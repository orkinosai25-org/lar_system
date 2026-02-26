<script src="http://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<link href="http://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.js"></script>
<script type="text/javascript">
$(document).ready( function () {
    $('#tab_leave_list').DataTable({"paging" : false});
});
</script>
<style type="text/css">
a.act { cursor: pointer;} 
.table{margin-bottom: 0;}
.modal-footer { padding: 10px;}
  .toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
  .toggle.ios .toggle-handle { border-radius: 20px; }
</style>
<div class="bodyContent">
	<div class="panel panel-default"> 
		<div class="panel-heading">
			<div class="panel-title">
					FDTL Temp Details <a class="btn btn-info"  href="<?php echo base_url() ?>index.php/flight/pilot_list" >Back</a>
			</div>
		</div>
		<div class="panel-body">
		    <div class="bs-docs-example" style="padding-bottom: 24px;">
		      Pilot Name :  <?=$pilot_name[0]['first_name']?> <?=$pilot_name[0]['last_name']?>
			  <br>
			  Pending Records : 
			  <?php 
			  if(count(@$fdtl_details_temp)>0){
				echo count(@$fdtl_details_temp) ;
			  }else{
				echo 'No Details Found';				  
			  } ?>
			</div>
			
		</div>
	</div>
</div>
			
			<?php
			$landings = 0;
			foreach($fdtl_details_temp as $k => $fdtl_details_temp ){
				$fdtl_details_temp = array_values($fdtl_details_temp);
			?>
			<div class="panel panel-default"> 
				<div class="panel-heading">
					<div class="panel-title">
						<?=date("d-m-Y", strtotime($k))?> 
					</div>
				</div>
	
			<div class="bodyContent">
	
				<div class="panel-body">
					<div class="table-responsive">
			
			
			
			<table class="table table-bordered table-hover table-condensed">
			<thead>
				<tr>
					<td><i class="fa fa-sort-numeric-asc"></i> S. No.</td>
				
					<td>Flight Number</td>
					<td>From</td>
					<td>To</td>
					<td>Total Landings</td>
					<td>Departure Date</td>
					<td>Departure Time</td>
					<td>Arrival Date</td>
					<td>Arrival Time</td>
					<td>Block Time</td>
					<td>Flight</td>
				
				</tr>
				</thead><tbody>
				<form method='post' action=''>
				<?php 
				if(count(@$fdtl_details_temp)>0){
					$total_flight_time = 0;
					$i=1;
					$night_time = 0;
				foreach($fdtl_details_temp as $key => $document_detail){  
				?>
				<tr>
					<td><?=$i?></td>
					<td><?=$document_detail['flight_number']?></td> 
					<td><?=$document_detail['flight_from']?></td>
					<td><?=$document_detail['flight_to']?></td>
					<td><?=$document_detail['total_no_of_landings']?></td>
					<td><?=date("d-m-Y", strtotime($document_detail['chocks_off_date']))?></td>
					<td><?=$document_detail['chocks_off_time']?></td>
					<td><?=date("d-m-Y", strtotime($document_detail['chocks_on_date']))?></td>
					<td><?=$document_detail['chocks_on_time']?></td>
					<td><?=$document_detail['block_time']?></td>
					<td>
					<?php 
					
					
					$landings+=$document_detail['total_no_of_landings'];
					$first = '';
					$middle = '';
					$last = ''; 
					
					$l = count(@$fdtl_details_temp) - 1;
					
					if( $key == 0){ $first = 'checked'; 
					$first_flight = $document_detail['chocks_off_time'];	}
					if(( $key != 0) && ( $key != count(@$fdtl_details_temp))){ $middle = 'checked'; 	}
					if( $key == $l){ $last = 'checked';  
					$last_flight = $document_detail['chocks_on_time'];	
					
					
					
					if (strpos($first_flight, ':') !== false){ list($hours, $minutes,$sec) = explode(':', $first_flight); } 
					$first_flight_time = $hours * 60 + $minutes; 
					if (strpos($last_flight, ':') !== false){ list($hours, $minutes,$sec) = explode(':', $last_flight); } 
					$last_flight_time = $hours * 60 + $minutes; 
					$total_duty_time = ($last_flight_time) - ($first_flight_time);
					//debug(date('h:i',strtotime($total_duty_time)));
					}
						
					?>
					
					
					<?php if(count($fdtl_details_temp) == 1 ){ ?>
					<input type="radio" class="crs_is_domestic" id='only_flight' name="flight_<?=$document_detail['origin']?>" value="4" checked > &nbsp; Only Flight<br>
					<?php }else{ ?>
					 <input type="radio" class="crs_is_domestic" id='first_flight' name="flight_<?=$document_detail['origin']?>" value="1" <?=$first?> >  &nbsp; First Flight<br>
					 <input type="radio" class="crs_is_domestic" id='middle_flight' name="flight_<?=$document_detail['origin']?>" value="2" <?=$middle?>> &nbsp; Middle Flight<br>
					 <input type="radio" class="crs_is_domestic" id='last_flight' name="flight_<?=$document_detail['origin']?>" value="3" <?=$last?> > &nbsp; Last Flight<br>
					<?php } 
					$chocks_off_time = $document_detail['chocks_off_time'];
					//$chocks_off_time = '12:15';
					//debug($chocks_off_time);
					if(strpos($chocks_off_time, ':') !== false){ list($hours, $minutes,$sec)= explode(':', $chocks_off_time); } 
					$chocks_off = $hours * 60 + $minutes; 
					
					
					
					$chocks_on_time = $document_detail['chocks_on_time'];
					//debug($chocks_on_time);
					//$chocks_on_time = '12:15';
					if (strpos($chocks_on_time, ':') !== false){ list($hours, $minutes,$sec) = explode(':', $chocks_on_time); } 
					$chocks_on = $hours * 60 + $minutes; 
					
					//debug('$chocks_off : '.$chocks_off);
				    //debug('$chocks_on : '.$chocks_on);
					if($chocks_off > 735 || $chocks_on < 35)
					{ 

						$bk = $document_detail['block_time'];
						//debug($bk);
						if(strpos($bk, ':') !== false){ list($hours, $minutes,$sec)= explode(':', $bk); } 
						$block_time = $hours * 60 + $minutes; 
						$night_time +=  $block_time;
					
					}
					//debug('night_time : '.$night_time);
					?>
					</td>
				</tr>
				<?php
					$b = $document_detail['block_time'];
					if (strpos($b, ':') !== false){ list($hours, $minutes,$sec) = explode(':', $b); } 
					$block_time = $hours * 60 + $minutes; 
					$total_flight_time = $total_flight_time + $block_time;
					$i++;
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No FDTL Temp Details.</strong></td>
				</tr>
				
				<?php }?>
				</tbody>
			</table>
		<?php if(count(@$fdtl_details_temp)>0){ ?>		
	<table  class="table table-bordered table-hover table-condensed">
		<tbody>
		<tr>
	
		<td>Total Daily Flight Time</td>
		<td>Total Daily Flight Time (Night) </td>
		<td>Total Daily Flight Duty Period</td>
		<td>No of Landings</td>
		<td>Action</td> 
		</tr>
		 
		<tr>
		<?php $total_duty_time = $total_duty_time + 60; ?>
		<td><?php $b_time = floor($total_flight_time / 60).':'. ($total_flight_time % 60); 
		
		echo $b_time;   
		
		?></td>
		<td><?php  $aa=floor($night_time / 60).':'. ($night_time % 60);
                     if($aa == '0:0'){echo '00:00';}else{ echo $aa; } 	?></td>
		<td><?php $d_time = floor($total_duty_time / 60).':'. ($total_duty_time % 60); echo date('h:i', strtotime($d_time));   ?></td>
		<td><?=$landings?> 
			<input type='hidden' name='no_of_landindgs'  value='<?=$landings?>'>
			<input type='hidden' name='common'  value='<?=$document_detail['common']?>'>
			<input type='hidden' name='block_time'  value='<?=$b_time?>'>
			<input type='hidden' name='night_time'  value='<?=floor($night_time / 60).':'. ($night_time % 60);	?>'>
		
		</td>
		
		<td><input type='submit' class='btn btn-success' value='Submit'></td>
		
		</form>
		</tr>
		</tbody>
	</table>
			<?php } ?>
		  </div>
		</div>
	</div>
</div>
<?php break; } ?>
<!-- HTML END -->

<script>
      $(document).ready(function(){
	 // to  Edit and Update the data
	 $('.leave_update_fare').on('click', function(){
	 	$('#title').text('Update Licence');
	 		
		var origin =  $(this).data('origin');
	 	var training_origin =  $(this).data('training_origin');
	 	var training_type =  $(this).data('training_type');
	 	var training_issue_date =  $(this).data('training_issue_date');
	 	var training_expiry_date =  $(this).data('training_expiry_date');
	 	var pilot_id =  $(this).data('pilot_id');
	 	
	 	
	 	
	 	
	  	$('input[name="origin"]').val(origin);
	  	$('input[name="pilot_id"]').val(pilot_id);
	  	
	 	$('select[name="training_type"]').val(training_type);
	 	$('input[name="training_issue_date"]').val(training_issue_date);
	 	$('input[name="training_expiry_date"]').val(training_expiry_date);
	 	
	 });
	 //  delete fare rule
      	  $('.delete_fare').on('click', function(){
	      var result = confirm("Want to delete?");
        if (result) {
	 	var origin =  $(this).data('origin');
	 	    $.ajax({
		 		method:'get',
		 		url:app_base_url+'index.php/ajax/delete_pilot_training/'+origin,
		 		dataType: 'json',
		 		success:function(data){
		 			location.reload();
		 		    }
		 		});
	       }
	 });
	 
});

	</script>
