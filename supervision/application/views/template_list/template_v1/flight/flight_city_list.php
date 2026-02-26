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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
<script type="text/javascript">
	$(document).ready(function() {
		$('#tab_flight_list').DataTable({
			"paging": false
		});
	});
</script>
<!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="panel panel-default">
		<!-- PANEL WRAP START -->
		<div class="panel-heading">
			<!-- PANEL HEAD START -->
			<div class="panel-title">
				City List
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
			<button type="button" class="btn btn-primary update_fare" id="add_fare">Add City</button>
			<div class="table-responsive">
				<!-- PANEL BODY START -->
				<table class="table table-bordered table-hover" id="tab_flight_list">

					<thead>
						<tr>
							<td><i class="fa fa-sort-numeric-asc"></i> S. No.</td>
							<td>Code</td>
							<td>Airport name</td>
							<td>City</td>

							<?php if (check_user_previlege('p69')) { ?><td>Action</td><?php } ?>

						</tr>
					</thead>
					<tbody>
						<?php
						if (count(@$sid) > 0) {
							foreach ($sid as $key => $document_detail) {
						?>
								<tr>
									<td> <?= $key + 1 ?></td>
									<td><?= $document_detail['airport_code'] ?></td>
									<td><?= $document_detail['airport_name'] ?></td>
									<td><?= $document_detail['airport_city'] ?></td>


									<?php if (check_user_previlege('p69')) { ?>
										<td>
											<button type="button" class="btn btn-primary update_fare"
												data-origin="<?= $document_detail['origin'] ?>"
												data-airport_code="<?= $document_detail['airport_code'] ?>"
												data-airport_name="<?= $document_detail['airport_name'] ?>"
												data-airport_city="<?= $document_detail['airport_city'] ?>">Edit</button>


										</td>
									<?php } ?>
								</tr>
							<?php
							}
						} else { ?>
							<tr>
								<td colspan="12"> <strong>No Cities.</strong></td>
							</tr>

						<?php } ?>
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
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" id="title"> City details</h4>
			</div>
			<div class="modal-body action_details">
				<div class="text-danger" id="err"></div>
				<form method="post" action="" id="meal_detail_frm">
					<input type="hidden" name="origin" value="0">
					<div class="col-xs-12 col-sm-12 fare_info nopad">
						<div class="form-group">
							<div class="org_row">
								<div class="col-sm-12 up_city">




									<div class="radio">
										<label for="value_type" class="col-sm-5 control-label">Iata code<span class="text-danger">*</span></label>
										<div class="col-md-7">
											<input type="text" class="wdt25 form-control" name="airport_code" id="airport_name" required="">
										</div>
									</div>

									<div class="radio">
										<label for="value_type" class="col-sm-5 control-label">Airport name<span class="text-danger">*</span></label>
										<div class="col-md-7">
											<input type="text" class="wdt25 form-control" name="airport_name" id="airport_name" required="">
										</div>
									</div>

									<div class="radio">
										<label for="value_type" class="col-sm-5 control-label">City<span class="text-danger">*</span></label>
										<div class="col-md-7">
											<input type="text" class="wdt25 form-control" name="airport_city" id="airport_city" required="">
										</div>
									</div>
									<div class="radio">
										<label for="value_type" class="col-sm-5 control-label">Latitude<span class="text-danger">*</span></label>
										<div class="col-md-7">
											<input type="text" class="wdt25 form-control" name="latitude" id="latitude" required="">
										</div>
									</div>
									<div class="radio">
										<label for="value_type" class="col-sm-5 control-label">Longitude<span class="text-danger">*</span></label>
										<div class="col-md-7">
											<input type="text" class="wdt25 form-control" name="longitude" id="longitude" required="">
										</div>
									</div>

								</div>


							</div>

						</div>
					</div>

			</div>
			<div class="modal-footer">
				<div class="col-xs-12 col-sm-12">

					<button type="submit" class="btn btn-primary" id="save">Save</button>
					</form>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>

	</div>
</div>
<!-- Script -->
<script type='text/javascript'>
	$(document).ready(function() {


		// to  Edit and Update the data


		$('.update_fare').on('click', function() {
			$('#title').text('Add City');
			$("#add_fare_rule").modal('show');


			$('input[name="origin"]').val('');
			$('input[name="airport_code"]').val('');
			$('input[name="airport_name"]').val('');
			$('input[name="airport_city"]').val('');



		});



		$('.update_fare').on('click', function() {
			$('#title').text('Update city');
			$("#add_fare_rule").modal('show');


			var origin = $(this).data('origin');
			var base = $(this).data('base');
			var airport_code = $(this).data('airport_code');
			var airport_name = $(this).data('airport_name');
			var airport_city = $(this).data('airport_city');

			var base = '';
			$('input[name="origin"]').val(origin);
			$('input[name="airport_code"]').val(airport_code);
			$('input[name="airport_name"]').val(airport_name);
			$('input[name="airport_city"]').val(airport_city);





		});
		//  delete fare rule
		$('.delete_fare').on('click', function() {
			var result = confirm("Want to delete?");
			if (result) {
				var origin = $(this).data('origin');
				$.ajax({
					method: 'get',
					url: app_base_url + 'index.php/ajax/delete_sid_details/' + origin,
					dataType: 'json',
					success: function(data) {
						location.reload();
					}
				});
			}
		});



	});
</script>