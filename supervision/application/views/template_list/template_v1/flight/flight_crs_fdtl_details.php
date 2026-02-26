<style type="text/css">
a.act { cursor: pointer;} 
.table{margin-bottom: 0;}
.modal-footer { padding: 10px;}
  .toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
  .toggle.ios .toggle-handle { border-radius: 20px; }
}
</style>
<!--link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script-->

<!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="panel panel-default">
		<!-- PANEL WRAP START -->
		<div class="panel-heading">
			<!-- PANEL HEAD START -->  
			<div class="panel-title">
					FDTL Details <a class="btn btn-info"  href="<?php echo base_url() ?>index.php/flight/pilot_list" >Back</a>
					<a href = "<?php echo base_url(); ?>index.php/flight/export_fdtl_details/excel?origin=<?= $pilot_name[0]['pilot_id'];?>"   class="btn btn-primary">Export FDTL detail</a>
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
		
			<div class="org_row"> 
				<div class="col-sm-12">
					<div class="radio">  
						<label for="value_type" style='padding-left: 7px !important;' class="col-sm-1 control-label">Pilot Name : </label> 
						<div class="col-sm-3">   
							<?=$pilot_name[0]['first_name']?> <?=$pilot_name[0]['last_name']?>
						</div> 
					</div> 
				</div> 
			</div> 
		
			
			<div class="org_row" > 
				<div class="col-sm-12" style='padding-bottom: 25px;'> 
					<div class="radio">  
						<label for="value_type"  style='padding-left: 7px !important;' class="col-sm-1 control-label">Date : </label> 
						<div class="col-sm-4">   
							<input type="text" class="wdt25 form-control" readonly="" id="datepicker" > 
						</div> 
						<div class="col-sm-2">   
							<input type='button' value='Search' class='btn btn-success search'>
							<input type='button' value='Clear' class='btn btn-info search' >
						</div>
					</div> 
		                        
				</div>
			</div>
			
			
			<div class="table-responsive">
				<!-- PANEL BODY START -->
				<table class="table table-bordered table-hover table-condensed table-responsive " id="tab_leave_list">
				<thead>
					 <tr>
						<th rowspan='2'>S. No.</th>
						<th rowspan="2">Date</th>
						<th rowspan="2">Duty Start Time</th>
						<th rowspan="2">Duty End Time</th>
						
						<th rowspan="2">Flight Duty Start Time</th>
						<th rowspan="2">Flight Duty End Time</th>
						
						<th rowspan="2">Total Daily <br>Flight Time</th>
						<th rowspan="2">Total Daily <br>Flight Time <br>(Night)</th>
						<th rowspan="2">Total Daily <br>Flight Duty <br>Period</th>
						<th rowspan="2">Total Daily <br>Duty Period</th>
						<th rowspan="2">No of <br>Landindgs</th>
						<th colspan="3">Cumulative Flight Time</th>
						<th colspan="3">Cumulative Duty Period</th>
						<th rowspan='2'>Flights</th>
					  </tr>
						<td>In last 7 days</td>  
						<td>In last 30 days</td>  
						<td>In last 365 days</td>  
						<td>In last 7 days</td>  
						<td>In last 14 days</td>  
						<td>In last 28 days</td> 
					  </tr>
					</thead>
					
					<tbody>
					<?php 
					if(count(@$fdtl_details_temp)>0){
					foreach($fdtl_details_temp as $key => $document_detail){  
					$date = date("d-m-Y", strtotime($document_detail['current_date']));
					?>
					<tr class='searchtable' data-date='<?=$document_detail['current_date']?>'>
						<td class='sno'><?=$key+1?></td>					
						<td><?=$date?></td>  
						<td><?=date('H:i',strtotime($document_detail['duty_start_time']));?></td> 
						<td><?=date('H:i',strtotime($document_detail['duty_end_time']));?></td>		
                        
						<td><?=date('H:i',strtotime($document_detail['duty_start_time']));?></td> 
						<td><?=date("H:i", strtotime("-15 minutes",strtotime($document_detail['duty_end_time'])));?></td>	  	
						
						<td><?=date('H:i',strtotime($document_detail['total_flight_time']));?></td>
						<td><?=date('H:i',strtotime($document_detail['total_flight_night_time']));?></td>
						<td><?=date("H:i", strtotime("-15 minutes",strtotime($document_detail['total_duty_time'])));?></td>	  
						<td><?=date('H:i',strtotime($document_detail['total_duty_time']));?></td>
						<td><?=$document_detail['no_of_landindgs']?></td>				
						<!--<td><?=date('H:i',strtotime($document_detail['flight_time_7_days']));?></td>-->
						<!--<td><?=date('H:i',strtotime($document_detail['flight_time_30_days']));?></td>-->
						<!--<td><?=date('H:i',strtotime($document_detail['flight_time_365_days']));?></td>-->
						<!--<td><?=date('H:i',strtotime($document_detail['duty_period_7_days']));?></td>-->
						<!--<td><?=date('H:i',strtotime($document_detail['duty_period_14_days']));?></td> -->
						<!--<td><?=date('H:i',strtotime($document_detail['duty_period_28_days']));?></td>-->
						
						<td><?=$document_detail['flight_time_7_days'];?></td>
                    	<td><?=$document_detail['flight_time_30_days'];?></td>
                    	<td><?=$document_detail['flight_time_365_days'];?></td>
                    	<td><?=$document_detail['duty_period_7_days'];?></td>
                    	<td><?=$document_detail['duty_period_14_days'];?></td> 
                    	<td><?=$document_detail['duty_period_28_days'];?></td>
						
						<td><button type="button" class="btn btn-primary flight_info" data-pilot_id='<?=$pilot_name[0]['pilot_id']?>' data-date='<?=$date?>' data-toggle="modal"  data-target="#exampleModal">Flight List</button></td>	
					</tr>
					<?php  
					} }else{ ?>
					<tr>
						<td colspan="12"> <strong>No FDTL Details added.</strong></td>
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

<!-- Modal -->

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Flight List</h5> 
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
  $(document).on('click', '.search',function(){
	  
   	    	var date = $('#datepicker').val();
	    	var val = $(this).val();
			if(val == 'Clear')
			{
				date = '';
				$('#datepicker').val('');
			}
			var count=1;
			var empty=0;
   	   		$('.searchtable').each(function(key, value) {
         	search_date = $(this).data('date');
         	var res = search_date.includes(date);
			  if (res) 
			  {
				$(this).removeClass('hide');
				$(this).find('.sno').text(count);
				count = count+1 ;  
			  }
			  else
			  {
				$(this).addClass('hide');
				empty = empty+1 ;  
			  } 
      		});
			if(empty == <?=$key+1?>)
			{
				alert('No Data Found')
			}
			
   	   	});
		
$( function() {
	$( "#datepicker" ).datepicker({
		numberOfMonths: 1,
		dateFormat: 'yy-mm-dd',
		
	});
} );
$('.flight_info').on('click', function(){
	  var pilot_id = $(this).data('pilot_id');
	  var date = $(this).data('date');
	console.log(pilot_id)
	console.log(date)
	
	$.ajax({
		url:app_base_url+'index.php/ajax/fdtl_temp_flight_list/',
		type: 'post',                    
		data: { pilot_id: pilot_id,date:date},
		success:function(data){ 
		$('.modal-body').html(data);
		}
		});
});
</script>



