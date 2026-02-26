<?php
?>
<div class="bodyContent col-md-12">
	<div class="panel panel-default clearfix">
		<!-- PANEL WRAP START -->
		<div class="panel-heading">
			<!-- PANEL HEAD START -->
			<?php //$GLOBALS['CI']->template->isolated_view('management/group_request_tabs.php')?>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
 			<i class="fa fa-users"></i> <span>Flight Group Request</span>
			<hr>


			<div class="clearfix">
				<!-- PANEL BODY START -->
				<table class="table table-condensed table-bordered" id="example">
					<thead>
						<tr>
							<th>Sno</th>
							<th>Refernce No</th>
							<th>User Type</th>
							<th>From - to</th>
							<th>Journey Date</th>
							<th>Adults</th>
							<th>Children</th>
							<th>Infants</th>
							
							<th>Trip Type</th>
							<th>Class</th>
							<th>Remarks</th>
							<th>Name</th>
							<th>Email iD</th>
							<th>Phone Number</th>
						</tr>
						</tr>
					</thead>
					<tbody>
	<?php
	//debug($page_data);exit;
	if (valid_array ( $page_data ) == true) {
		
		foreach ( $page_data as $k => $v ) {
			if($v['trip_type']!=''){
			?>
				<tr>
							<td><?=(@$k+1)?></td>
							<td><?php echo @$v['refernce_no'];?></td>
							<td><?php echo @$v['user_type'];?></td>
							<td><?php echo @$v['from_loc'];?> - <?php echo @$v['to_loc'];?></td>
							<td><?php echo @$v['departure'];?>  <?php if($v['trip_type']=="circle") echo '-'.@$v['return_date'];?></td>
							  <td><?php echo @$v['adults'];?></td>
							<td><?php echo @$v['children'];?></td>
							<td><?php echo @$v['infants'];?></td>
							<td><?php 
							if($v['trip_type']=="circle")
								echo "Round Trip";
							else 
							echo @$v['trip_type'];?></td>
							
							<td><?php echo @$v['class_type'];?></td>
							<td><?php echo @$v['remarks'];?></td>
							<td><?php echo @$v['name'];?> </td>
							<td><?php echo @$v['email_id'];?></td>
							<td><?php echo @$v['contact_number'];?></td>

						</tr>
			<?php
			}
		}
	} else {
		echo '<tr><td colspan="3">No Data Found</td></tr>';
	}
	echo '</tbody>';
	?>

				
				</table>

				<script type="text/javascript">
	$(document).ready(function() {
		$('#example').DataTable();
	});
</script>
<?php

Js_Loader::$js [] = array (
		'src' => SYSTEM_RESOURCE_LIBRARY . '/datatables/jquery.dataTables.js',
		'defer' => 'defer' 
);
Js_Loader::$js [] = array (
		'src' => SYSTEM_RESOURCE_LIBRARY . '/datatables/jquery.dataTables.min.js',
		'defer' => 'defer' 
);
array_unshift(Js_Loader::$css, array('href' => SYSTEM_RESOURCE_LIBRARY.'/datatables/jquery.dataTables.css'));
array_unshift(Js_Loader::$css, array('href' => SYSTEM_RESOURCE_LIBRARY.'/datatables/jquery.dataTables.min.css'));
array_unshift(Js_Loader::$css, array('href' => SYSTEM_RESOURCE_LIBRARY.'/datatables/jquery.dataTables_themeroller.css'));
//jquery.dataTables.min.css jquery.dataTables_themeroller.css
?>
			</div>
		</div>
	</div>
</div>
