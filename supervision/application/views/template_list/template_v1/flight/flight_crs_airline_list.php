<?php
//debug($data);
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
				Flight Airline List
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
			<?php if (check_user_previlege('p64')) { ?>
				<button type="button" class="btn btn-primary" id="add_fare">Add Airline</button>
			<?php } ?>
			<div class="table-responsive">
				<!-- PANEL BODY START -->
				<table class="table table-bordered table-hover table-condensed" id="tab_flight_list">
					<thead>
						<tr>
							<th><i class="fa fa-sort-numeric-asc"></i> S. No.</th>
							<!-- <th>Origin</th> -->
							<th>Airline Name</th>
							<th>Airline Code</th>
							<th>Airline Logo</th>
							<?php if (check_user_previlege('p64')) { ?>
								<th>Action</th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php
						if (count(@$airline_list) > 0) {
							foreach ($airline_list as $key => $airline_detail) {
								// if($flight_list_details['active']==1)
								// 	$chk = "checked";
								// else
								// 	$chk = "";
						?>
								<tr>
									<td> <?= $key + 1 ?></td>
									<td><?= $airline_detail['airline_name'] ?></td>
									<td><?= $airline_detail['airline_code'] ?></td>
									<td><img src="<?php echo $GLOBALS['CI']->template->domain_airline_full_path($airline_detail['airline_img']) ?>" height="100px" width="100px" class="img-thumbnail"></td>
									<?php if (check_user_previlege('p64')) { ?>
										<td>
											<button type="button" class="btn btn-primary update_fare" data-origin="<?= $airline_detail['origin'] ?>" data-airline_name="<?php echo $airline_detail['airline_name']; ?>" data-airline_code="<?= $airline_detail['airline_code'] ?>">Edit</button>
											<button type="button" class="btn btn-danger delete_fare" data-origin="<?= $airline_detail['origin'] ?>">Delete</button>
										</td>
									<?php } ?>
								</tr>
							<?php
							}
						} else { ?>
							<tr>
								<td colspan="12"> <strong>No airline added.</strong></td>
							</tr>

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

<div id="add_fare_rule" class="modal fade" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" id="title"> Add Airline</h4>
			</div>
			<div class="modal-body action_details">
				<div class="text-danger" id="err"></div>
				<form method="post" action="<?php echo base_url() . 'index.php/flight/add_airline'; ?>" id="meal_detail_frm" enctype="multipart/form-data" autocomplete="off">
					<input type="hidden" name="origin" value="0">
					<div class="col-xs-12 col-sm-12">
						<div class="form-group">
							<div class="col-sm-6">
								<label form="user" for="title" class="col-sm-6 control-label">Airline Name</label>
							</div>
							<div class="col-sm-6">
								<input type="text" class="form-control" placeholder="Airline Name" name="airline_name" required maxlength='15' />
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-12">
						<div class="form-group">
							<div class="col-sm-6">
								<label form="user" for="title" class="col-sm-6 control-label">Airline Code</label>
							</div>
							<div class="col-sm-6">
								<input type="text" class="form-control tags" placeholder="SG" name="airline_code" required maxlength='10' />
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-12">
						<div class="form-group">
							<div class="col-sm-6">
								<label form="user" for="title" class="col-sm-6 control-label">Airline Logo</label>
							</div>
							<div class="col-sm-6">
								<input type="file" class="wdt25 form-control aaa" id='airline_img' name="airline_img">
								<p id="file-name-display"></p>
							</div>
						</div>
					</div>

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
		//  to add meal details and show
		$('#add_fare').on('click', function() {
			$('#title').text('Add Airline');
			$("#err").text('');
			$('input[name="origin"]').val('');
			$('input[name="airline_name"]').val('');
			$('input[name="airline_code"]').val('');
			$('#file-name-display').text('');

			$("#add_fare_rule").modal('show');
		});

		// to  Edit and Update the data
		$('.update_fare').on('click', function() {
			$('#title').text('Update Airline');
			var origin = $(this).data('origin');
			var airline_name = $(this).data('airline_name');
			var airline_code = $(this).data('airline_code');
			//var airline_img =  $(this).data('airline_img');

			$("#add_fare_rule").modal('show');
			$('input[name="origin"]').val(origin);
			$('input[name="airline_code"]').val(airline_code);
			$('input[name="airline_name"]').val(airline_name);
			// $('#file-name-display').text(airline_img);
		});
		//  delete fare rule 
		$('.delete_fare').on('click', function() {
			var origin = $(this).data('origin');
			$.ajax({
				method: 'get',
				url: app_base_url + 'index.php/flight/delete_airline/' + origin,
				dataType: 'json',
				success: function(data) {
					// if(data.status == false) {
					// 	$("#err").text(data.msg);
					// } else {
					// 	$("#fare_rule_frm").submit();
					// }
					location.reload();
				}
			});
		});






		/*
    $('#tab_flight_list').DataTable({
		  "searching": true,
		  "paging" : false
	});*/
	});
</script>