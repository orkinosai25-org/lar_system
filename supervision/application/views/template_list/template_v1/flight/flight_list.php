<?php
// debug($data);
?>
<style type="text/css">
	a.act {
		cursor: pointer;
	}

	.table {
		margin-bottom: 0;
	}

	.modal-footer {
		padding: 10px;
	}

	.toggle.ios,
	.toggle-on.ios,
	.toggle-off.ios {
		border-radius: 20px;
	}

	.toggle.ios .toggle-handle {
		border-radius: 20px;
	}

	.tab_dat {
		overflow-x: scroll;
	}
</style>
<!--link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script-->
<!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="panel panel-default">
		<!-- PANEL WRAP START -->
		<div class="panel-heading">
			<!-- PANEL HEAD START -->
			<div class="panel-title">
				Flight List
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">

			<!-- Dinesh Start 19 -02-2018-->
			<h4>Advanced Search Panel</h4>

			<!-- End 19 -02-2018 -->
			<div class="table-responsive">
				<form method="GET" autocomplete="off">

					<div class="clearfix form-group padding0">

						<div class="col-xs-4">
							<label>
								Flight Number
							</label>
							<input type="text" class="form-control " placeholder="Flight no" name="flight_no" value="<?= $this->input->get('flight_no'); ?>" />

						</div>

						<div class="col-xs-4">
							<label>
								Departure Airport Code
							</label>
							<input type="text" class="form-control getAiportlist" placeholder="Departure Airport" name="dep_origin" value="<?= $this->input->get('dep_origin'); ?>" />

						</div>
						<div class="col-xs-4">
							<label>
								Arrival Airport Code
							</label>
							<input type="text" class="form-control getAiportlist" placeholder="Departure Airport" name="arival_origin" value="<?= $this->input->get('arival_origin'); ?>" />

						</div>
						<div class="col-xs-4">
							<label>
								Date
							</label>
							<input type="text" class="wdt25 form-control" name='arr_date' id="datepicker" value="<?= $this->input->get('arr_date'); ?>">

						</div>
						<div class="col-xs-4">
							<?php $month_list = generate_month_list(); ?>
							<label>
								Month
							</label>
							<select class="form-control" name="month">
								<option value="">Month </option>
								<?php

								foreach ($month_list as $key => $value) {
									$select = '';
									if (($this->input->get('month') == $key)) {
										$select = "selected";
									}
									echo '<option value="' . $key . '" ' . $select . '>' . $value . '</option>';
								}
								?>
							</select>
						</div>

						<div class="col-xs-4">
							<label>
								Year
							</label>
							<select class="form-control" name="year">
								<option value="">Year </option>
								<?php
								$c_year = date('2020');
								for ($i = 0; $i < 5; $i++) {

									$select = '';
									if (($this->input->get('year') == $c_year)) {
										$select = "selected";
									}

									echo '<option value="' . $c_year . '" ' . $select . '>' . $c_year . '</option>';
									$c_year = $c_year + 1;
								}
								?>
							</select>
						</div>








					</div>
					<div class="col-sm-12 well well-sm">
						<button type="submit" class="btn btn-primary">Search</button>
						<button type="reset" class="btn btn-warning reset margin_left">Reset</button>

						<a href="<?php echo base_url(); ?>index.php/flight/export_flight_details/excel" class="btn btn-primary">Export Flight List</a>


					</div>
				</form>

				<!-- PANEL BODY START -->
				<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
					<thead>
						<tr>
							<th width="4%"><i class="fa fa-sort-numeric-asc"></i> S. No.</th>
							<!-- 					<th>Origin</th>
<th>Destination</th> -->
							<th width="12%">Departure (Date Time)</th>
							<th width="12%">Arrival (Date Time)</th>
							<th width="10%">Flight Number</th>
							<th width="10%">Airline Code</th>
							<th width="10%">Airline Name</th>
							<th width="10%">Status</th>
							<th width="10%">Action</th>
						</tr>
					</thead>
					<tbody>





						<?php

						$temp_origin = "temp_origin";
						$temp_destination = "temp_destination";
						$temp_flight_num = "temp_flight_num";
						$temp_carrier_code = "temp_carrier_code";
						$temp_class = "temp_class";
						$arrComp = array();
						if ($_SERVER['REMOTE_ADDR'] == "14.97.94.42") {
							// debug($data);
							// debug($fsid);
							// die;
						}
						if (count($data) > 0) {
							$filter_data = base64_encode(json_encode($_GET));

							foreach ($data as $key => $flight_list_details) {
								// debug($flight_list_details);exit;
								//debug($temp_origin);
								$strData_comp = $flight_list_details['origin'] . "_" . $flight_list_details['destination'] . "_" . $flight_list_details['destination'] . "_" . $flight_list_details['flight_num'] . "_" . $flight_list_details['carrier_code'] . "_" . $flight_list_details['class_type'] . "_" . $flight_list_details['fsid'];
								if (!in_array($strData_comp, $arrComp)) {
									$arrComp[] = $strData_comp;
									$s_f_d = 1;
									echo '<tr style="background: #fff;">
						             <td colspan="6">
									 
									 
	<strong>


	<span style="font-weight: bold;">' . $flight_list_details['carrier_code'] . '-' . $flight_list_details['flight_num'] . ' : </span>
	' . $flight_list_details['origin'] . '' . $flight_list_details['destination'] . '
	
	</strong>
	
	</td>
						           
						          </tr>';

									$temp_origin = $flight_list_details['origin'];
									$temp_destination = $flight_list_details['destination'];
									$temp_flight_num = $flight_list_details['flight_num'];
									$temp_carrier_code = $flight_list_details['carrier_code'];
									$temp_class = $flight_list_details['class_type'];
								}

								$dep_from_date	=  date('d-M-Y', strtotime($flight_list_details['dep_from_date']));
								$departure_time	= date('H:i', strtotime($flight_list_details['departure_time']));

								$dep_to_date	=  date('d-M-Y', strtotime($flight_list_details['dep_to_date']));
								$arrival_time	= date('H:i', strtotime($flight_list_details['arrival_time']));

								echo ' <tr style="background: #e6f2f5;">
						               <td> 
							               <span disply:block;>' . $s_f_d++ . '</span>
						               </td>
						               <td >' . $dep_from_date . ' ' . $departure_time . '</td>
						               <td >' . $dep_to_date . ' ' . $arrival_time . '</td>
						              
						               <td >' . $flight_list_details['flight_num'] . '</td>
						               <td >' . $flight_list_details['carrier_code'] . '</td>
						               <td >' . $flight_list_details['airline_name'] . '</td>
						             ';
						?>
								<td><button type="button" class="btn btn-sm btn-toggle stus dyna_status <?= ($flight_list_details['active'] == 1) ? 'active' : '' ?> act-<?= $flight_list_details['fsid'] ?>" data-toggle="button" aria-pressed="<?= ($flight_list_details['active'] == 1) ? 'true' : 'false' ?>" data-fsid="<?= $flight_list_details['fsid'] ?>" data-status="<?= $flight_list_details['active'] ?>" autocomplete="off">
										<div class="handle"></div>
									</button>
								</td>
								<td><a class="act" data-charter_basefare="<?= $flight_list_details['charter_basefare'] ?>" data-charter_tax="<?= $flight_list_details['charter_tax'] ?>" data-charter_vat="<?= $flight_list_details['charter_vat'] ?>">Fare Details</a> <br> <a class="flight_details" data-fsid="<?= $flight_list_details['fsid'] ?>" href="javascript::void(0)">Flight Details</a><br>
									<!-- <a href="<?= base_url(); ?>index.php/flight/update_flight_details/<?= $flight_list_details['fsid'] ?>" >Update Flight Details</a> -->
									<?php
									// debug($flight_list_details['fsid']);
									// debug($fsid); die;
									if (in_array($flight_list_details['fsid'], $fsid)) {
									} else { ?>
										<a href="<?= base_url(); ?>index.php/flight/delete_flight_details/<?= $flight_list_details['fsid'] ?>">Delete Flight Details</a>
									<?php }
									?>

								</td>
						<?php
								echo '</tr>';

								//debug($flight_list_details); exit;
							}
							//	exit;
						} else {
							echo '<tr>
					<td colspan="12"> <strong>No flights available.</strong></td>
				</tr>';
						}
						?>


						<!-- new view -->




						<!-- 
		<tr style="background: #fff;">
             <td colspan="6"><strong>DEL - BLR <span style="font-weight: normal;">(SG-123)</span></strong></td>
             <td colspan="6" align="right"><a class="btn btn-primary btn-sm  show-bal-btn" href="http://192.168.0.40/travelimpression/supervision/index.php/flight/update_flight_details/2">Update Flight Details</a></td>
          </tr>

            <tr style="background: #e6f2f5;">
               <td>
	               <span style="display: block; visibility: hidden;"><i class="fa fa-sort-numeric-asc"></i> SNo</span> 
	               <span disply:block;>1</span>
               </td>
               <td >01-02-2018 11:45</td>
               <td >28-02-2018 12:30</td>
               <td >123</td>
               <td >SG</td>
               <td >Spicejet</td>
               <td >Economy</td>
               <td >0</td>
               <td >
                  <button type="button" class="btn btn-sm btn-toggle stus dyna_status active act-1" data-toggle="button" aria-pressed="true" data-fsid="1" data-status="1" autocomplete="off">
                     <div class="handle"></div>
                  </button>
                  <input checked data-toggle="toggle" class="status_change" data-style="ios" type="checkbox" data-status=1 />
               </td>
               <td ><a class="act" data-adult_basefare="5000" data-adult_tax="0" data-child_basefare="" data-child_tax="" data-infant_basefare="1250" data-infant_tax="0">Fare Details</a> <br> <a class="flight_details" data-fsid="1" href="javascript::void(0)">Flight Details</a></td>
            </tr>
            <tr style="background: #e6f2f5;">
               <td> 2</td>
               <td>14-02-2018 11:30</td>
               <td>27-02-2018 01:45</td>
               <td>123</td>
               <td>SG</td>
               <td>Spicejet</td>
               <td>Economy</td>
               <td>0</td>
               <td>
                  <button type="button" class="btn btn-sm btn-toggle stus dyna_status active act-2" data-toggle="button" aria-pressed="true" data-fsid="2" data-status="1" autocomplete="off">
                     <div class="handle"></div>
                  </button>
                  <input checked data-toggle="toggle" class="status_change" data-style="ios" type="checkbox" data-status=1 />
               </td>
               <td><a class="act" data-adult_basefare="4000" data-adult_tax="0" data-child_basefare="" data-child_tax="" data-infant_basefare="1250" data-infant_tax="0">Fare Details</a> <br> <a class="flight_details" data-fsid="2" href="javascript::void(0)">Flight Details</a></td>
            </tr>
		<tr style="background: #fff;">
             <td colspan="6"><strong>DEL - BLR</strong></td>
             <td colspan="6" align="right"><a class="btn btn-primary btn-sm  show-bal-btn" href="http://192.168.0.40/travelimpression/supervision/index.php/flight/update_flight_details/2">Update Flight Details</a></td>
          </tr>
            <tr style="background: #ffdfd2;">
               <td>
	               <span style="display: block; visibility: hidden;"><i class="fa fa-sort-numeric-asc"></i> SNo</span> 
	               <span disply:block;>1</span>
               </td>
               <td >01-02-2018 11:45</td>
               <td >28-02-2018 12:30</td>
               <td >123</td>
               <td >SG</td>
               <td >Spicejet</td>
               <td >Economy</td>
               <td >0</td>
               <td >
                  <button type="button" class="btn btn-sm btn-toggle stus dyna_status active act-1" data-toggle="button" aria-pressed="true" data-fsid="1" data-status="1" autocomplete="off">
                     <div class="handle"></div>
                  </button>
                  <input checked data-toggle="toggle" class="status_change" data-style="ios" type="checkbox" data-status=1 />
               </td>
               <td ><a class="act" data-adult_basefare="5000" data-adult_tax="0" data-child_basefare="" data-child_tax="" data-infant_basefare="1250" data-infant_tax="0">Fare Details</a> <br> <a class="flight_details" data-fsid="1" href="javascript::void(0)">Flight Details</a></td>
            </tr>
            <tr style="background:#ffdfd2;">
               <td> 2</td>
               <td>14-02-2018 11:30</td>
               <td>27-02-2018 01:45</td>
               <td>123</td>
               <td>SG</td>
               <td>Spicejet</td>
               <td>Economy</td>
               <td>0</td>
               <td>
                  <button type="button" class="btn btn-sm btn-toggle stus dyna_status active act-2" data-toggle="button" aria-pressed="true" data-fsid="2" data-status="1" autocomplete="off">
                     <div class="handle"></div>
                  </button>
                  <input checked data-toggle="toggle" class="status_change" data-style="ios" type="checkbox" data-status=1 />
               </td>
               <td><a class="act" data-adult_basefare="4000" data-adult_tax="0" data-child_basefare="" data-child_tax="" data-infant_basefare="1250" data-infant_tax="0">Fare Details</a> <br> <a class="flight_details" data-fsid="2" href="javascript::void(0)">Flight Details</a><br></td>
            </tr> -->

						<!-- static ends here -->

						<?php
						if (0) {
							foreach ($data as $key => $flight_list_details) {

								if ($flight_list_details['active'] == 1)
									$chk = "checked";
								else
									$chk = "";
						?>
								<tr>
									<td> <?= $key + 1 ?></td>
									<!-- <td><?= $flight_list_details['origin'] ?></td>
					<td><?= $flight_list_details['destination'] ?></td> -->
									<td><?= date('d-m-Y', strtotime($flight_list_details['dep_from_date'])) ?>
										<?= date('H:i', strtotime($flight_list_details['departure_time'])) ?>
									</td>
									<td><?= date('d-m-Y', strtotime($flight_list_details['dep_to_date'])) ?>
										<?= date('H:i', strtotime($flight_list_details['arrival_time'])) ?>
									</td>
									<td><?= $flight_list_details['flight_num'] ?></td>
									<td><?= $flight_list_details['carrier_code'] ?></td>
									<td><?= $flight_list_details['airline_name'] ?></td>
									<td><button type="button" class="btn btn-sm btn-toggle stus dyna_status <?= ($flight_list_details['active'] == 1) ? 'active' : '' ?> act-<?= $flight_list_details['fsid'] ?>" data-toggle="button" aria-pressed="<?= ($flight_list_details['active'] == 1) ? 'true' : 'false' ?>" data-fsid="<?= $flight_list_details['fsid'] ?>" data-status="<?= $flight_list_details['active'] ?>" autocomplete="off">
											<div class="handle"></div>
										</button>
									</td>
									<td><a class="act" data-adult_basefare="<?= $flight_list_details['adult_basefare'] ?>" data-adult_tax="<?= $flight_list_details['adult_tax'] ?>" data-child_basefare="<?= $flight_list_details['child_basefare'] ?>" data-child_tax="<?= $flight_list_details['child_tax'] ?>" data-infant_basefare="<?= $flight_list_details['infant_basefare'] ?>" data-infant_tax="<?= $flight_list_details['infant_tax'] ?>" data-local-adult_basefare="<?= $flight_list_details['adult_local_basefare'] ?>" data-local-adult_tax="<?= $flight_list_details['adult_local_tax'] ?>" data-local-child_basefare="<?= $flight_list_details['child_local_basefare'] ?>" data-local-child_tax="<?= $flight_list_details['child_local_tax'] ?>" data-local-infant_basefare="<?= $flight_list_details['infant_local_basefare'] ?>" data-local-infant_tax="<?= $flight_list_details['infant_local_tax'] ?>">Fare Details</a> <br> <a class="flight_details" data-fsid="<?= $flight_list_details['fsid'] ?>" href="javascript::void(0)">Flight Details</a><br>
										<a href="<?= base_url(); ?>index.php/flight/update_flight_details/<?= $flight_list_details['fsid'] ?>">Update Flight Details</a>
									</td>

								</tr>
							<?php
							}
						} else { ?>


						<?php } ?>
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

<div id="action" class="modal fade" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title dyn_title" id="dynamic_text"></h4>
			</div>
			<div class="modal-body action_details">
				<div class="table-responsive fare_data dyn_data tab_dat">

				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>

	</div>
</div>
<script>
	$(function() {
		$("#datepicker").datepicker({
			numberOfMonths: 1,
			dateFormat: 'yy-mm-dd',

		});
	});
</script>

<script type="text/javascript">
	$(document).on('click', '.reset', function() {

		window.location.replace("<?= base_url(); ?>index.php/flight/flight_list");
	});


	$(document).on('click', '.dyna_status', function() {
		var thisss = $(this);
		var fsid = $(this).data('fsid');
		var status = $(this).attr('data-status');
		if (parseInt(status) === parseInt(1)) {
			status = 0;
		} else {
			status = 1;
		}
		$.ajax({
			url: "<?= base_url(); ?>index.php/flight/get_flight_status/" + fsid + "/" + status,
			async: false,
			success: function(result) {
				thisss.attr('data-status', status);
			}
		});
	});

	$(document).ready(function() {


		$(".act").click(function() {
			var charter_basefare = $(this).data('charter_basefare');
			var charter_tax = $(this).data('charter_tax');
			var charter_vat = $(this).data('charter_vat');

			var total = charter_basefare + charter_tax + charter_vat



			var str = '<table class="table table-bordered"><tbody><tr><td><strong>Base Fare</strong></td><td>' + charter_basefare + '</td></tr><tr><td><strong>Tax</strong></td><td>' + charter_tax + '</td></tr><tr><td><strong>VAT</strong></td><td>' + charter_vat + '</td></tr><tr><td><strong>Total Fare</strong></td><td>' + total + '</td></tr></tbody></table>';
			$('.fare_data').html(str);
			$('#dynamic_text').text('Fare Details');
			$("#action").modal('show');
		});

		$(".flight_details").click(function() {
			var fsid = $(this).data('fsid');
			$.ajax({
				url: "<?= base_url(); ?>index.php/flight/get_flight_details/" + fsid,
				success: function(result) {

					var res = JSON.parse(result);
					//  alert(result)
					if (res.length > 0) {

						var flight_data = '';
						var flight_data = '<table class="table table-bordered"><thead><tr><th>#SL</th><th>Origin</th><th>Deparature From Date</th><th>Deparature To Date</th><th>Flight Num</th><th>Carrier code</th><th>Airline Name</th><th>Flight Type</th></tr></thead><tbody>';
						for (var i = 0; i < res.length; i++) {
							flight_data += '<tr><td>' + parseInt(i + 1) + '</td><td>' + res[i]['origin'] + '</td><td>' + res[i]['departure_date_from'] + ' ' + res[i]['departure_time'] + '</td><td>' + res[i]['departure_date_to'] + ' ' + res[i]['arrival_time'] + '</td><td>' + res[i]['flight_num'] + '</td><td>' + res[i]['carrier_code'] + '</td><td>' + res[i]['airline_name'] + '</td><td>' + ((res[i]['trip_type'] == 0) ? "Charter" : "") + '</td></tr>';
						}
						flight_data += '</tbody></table>';
					}
					$('.fare_data').html(flight_data);
					$('#dynamic_text').text('Flight Details');
					$("#action").modal('show');

				}
			});
			// var str='<table class="table table-bordered"><thead><tr><th></th><th>Origin</th><th>Destination</th><th>Flight Num</th><th>Carrier code</th><th>Airline Name</th></tr></thead><tbody>";
			// </tbody></table>';
			// $('.dyn_data').html(str);
			//$("#action").modal('show'); 
			//$(".dyn_title").text("Flight Details");
		});

		/*$('#tab_flight_list').DataTable({
		  "searching": true,
		  "paging" : false
	});*/


	});
	$(".getAiportlist").autocomplete({
		source: app_base_url + "index.php/flight/get_flight_suggestions",
		minLength: 2, //search after two characters
		autoFocus: true, // first item will automatically be focused
		select: function(event, ui) {
			//var inputs = $(this).closest('form').find(':input:visible');
			//inputs.eq( inputs.index(this)+ 1 ).focus();
		}
	});
</script>