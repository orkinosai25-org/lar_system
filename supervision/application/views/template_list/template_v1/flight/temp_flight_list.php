<?php
//debug($filght_list);
?>
			<table class="table table-bordered table-hover table-condensed">
			<thead>
				<tr>
					<td><i class="fa fa-sort-numeric-asc"></i> SL No</td>
				
					<td>Flight Number</td>
					<td>Flight From</td>
					<td>Flight To</td>
					
					<td>Departure Date</td>
					<td>Departure Time</td>
					<td>Arrival Date</td>
					<td>Arrival Time</td>
					<td>Block Time</td>
					
				
				</tr>
				</thead><tbody>
				<form method='post' action=''>
				<?php 
				if(count(@$filght_list)>0){
					$total_flight_time = 0;
				foreach($filght_list as $key => $document_detail){  
				?>
				<tr>
					<td><?=$key+1?></td>
					<td><?=$document_detail['flight_number']?></td> 
					<td><?=$document_detail['flight_from']?></td>
					<td><?=$document_detail['flight_to']?></td>
					
					<td><?=date("d-m-Y", strtotime($document_detail['chocks_off_date']))?></td>
					<td><?=$document_detail['chocks_off_time']?></td>
					<td><?=date("d-m-Y", strtotime($document_detail['chocks_on_date']))?></td>
					<td><?=$document_detail['chocks_on_time']?></td>
					<td><?=$document_detail['block_time']?></td>
					
				</tr>
				<?php  
					
					
					if (strpos($document_detail['block_time'], ':') !== false){ list($hours, $minutes,$sec) = explode(':', $document_detail['block_time']); } 
					$block_time = $hours * 60 + $minutes; 
					$total_flight_time = $total_flight_time + $block_time;
				} }else{ ?>
				<tr>
					<td colspan="12"> <strong>No FDTL Temp Details.</strong></td>
				</tr>
				
				<?php }?>
				</tbody>
			</table>