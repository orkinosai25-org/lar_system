<?php

$_datepicker = array(array('created_datetime_from', PAST_DATE), array('created_datetime_to', FUTURE_DATE), array('created_datetime_to1', FUTURE_DATE));
$this->current_page->set_datepicker($_datepicker);
$this->current_page->auto_adjust_datepicker(array(array('created_datetime_from', 'created_datetime_to', 'created_datetime_to1')));
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style type="text/css">
	.label {
		color: black;
	}

	.error {
		color: red;
	}
</style>
<link href="<?= base_url() ?>../extras/system/template_list/template_v3/css/flight_extra_services.css" rel="stylesheet" />
<div class="row">
	<!-- HTML BEGIN -->
	<div class="bodyContent">
		<div class="panel panel-primary">
			<!-- PANEL WRAP START -->
			<div class="panel-heading">
				<!-- PANEL HEAD START -->
				<div class="panel-title"><i class="fa fa-edit"></i> Add Aircraft</div>
			</div>
			<!-- PANEL HEAD START -->
			<form id='add_aircraft' enctype="multipart/form-data" class="form-horizontal" method="POST" autocomplete="off" onsubmit='return Validate()'>
				<div class="panel-body ad_flt">
					<fieldset>
						<legend class="sm_titl"> Basic Information</legend>
						<div class="col-xs-12 col-sm-12 fare_info nopad">
							<div class="form-group">
								<div class="org_row">
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Manufacturer<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control " maxlength='35' name="manufacturer" required value='<?php echo (!empty($aircrafts)) ? $aircrafts['manufacturer'] : ''; ?>'>
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Type<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control " maxlength='35' name="type" required value='<?php echo (!empty($aircrafts)) ? $aircrafts['type'] : ''; ?>'>
											</div>
										</div>
									</div>
								</div>
								<div class="org_row">
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Model<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control " maxlength='35' name="model" required value='<?php echo (!empty($aircrafts)) ? $aircrafts['model'] : ''; ?>'>
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">MSN<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control " maxlength='35' name="msn" required value='<?php echo (!empty($aircrafts)) ? $aircrafts['msn'] : ''; ?>'>
											</div>
										</div>
									</div>
								</div>
								<div class="org_row">
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">A/C Reg.<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control " maxlength='35' name="reg" required value='<?php echo (!empty($aircrafts)) ? $aircrafts['reg'] : ''; ?>'>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</fieldset>
					<!-- 		<fieldset>
		<legend class="sm_titl"> Seat Configuration</legend>
		<div class="col-xs-12 col-sm-12 fare_info nopad">
			<div class="form-group">
				<div class="org_row">
					<div class="col-sm-6">
						<div class="rad">
						<label for="value_type" class="col-sm-4 control-label">Passenger<span class="text-danger">*</span></label>
						<div class="col-md-8">
							<input type="text" class="wdt25 form-control numeric" maxlength='3'  name="passenger" required  value='<?php echo (!empty($aircrafts)) ? $aircrafts['passenger'] : ''; ?>'>
						</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="rad">
						<label for="value_type" class="col-sm-4 control-label">Crew<span class="text-danger">*</span></label>
						<div class="col-md-8">
							<input type="text" class="wdt25 form-control numeric" maxlength='3'  name="crew" required  value='<?php echo (!empty($aircrafts)) ? $aircrafts['crew'] : ''; ?>'>
						</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</fieldset> -->
					<fieldset>
						<legend class="sm_titl">Technical</legend>
						<div class="col-xs-12 col-sm-12 fare_info nopad">
							<div class="form-group radio">
								<label for="value_type" class="col-sm-2 control-label">Cockpit<span class="text-danger">*</span></label>
								<div class="col-sm-4">
									<label class="radio-inline">
										<input type="radio" class="crs_is_domestic" name="cockpit" <?php echo ($aircrafts['cockpit'] == 0) ? 'checked' : ''; ?> <?php echo (isset($aircrafts)) ? '' : 'checked'; ?> value="0">Glass
									</label>
									<label class="radio-inline">
										<input type="radio" class="crs_is_domestic" name="cockpit" <?php echo ($aircrafts['cockpit'] == 1) ? 'checked' : ''; ?> value="1">Analog
									</label>
								</div>
							</div>

							<div class="form-group radio">
								<label for="value_type" class="col-sm-2 control-label">Auto Pilot<span class="text-danger">*</span></label>
								<div class="col-sm-4">
									<label class="radio-inline">
										<input type="radio" class="crs_is_domestic" name="auto_pilot" <?php echo ($aircrafts['auto_pilot'] == 0) ? 'checked' : ''; ?> <?php echo (isset($aircrafts)) ? '' : 'checked'; ?> value="0">Yes
									</label>
									<label class="radio-inline">
										<input type="radio" class="crs_is_domestic" name="auto_pilot" <?php echo ($aircrafts['auto_pilot'] == 1) ? 'checked' : ''; ?> value="1">No
									</label>
								</div>
							</div>
							<div class="form-group">
								<div class="rad">
									<label for="value_type" class="col-sm-2 control-label">Pilots<span class="text-danger">*</span></label>
									<div class="col-sm-2">
										<input type="text" class="wdt25 form-control numeric" maxlength='5' name="pilots" required="" value='<?php echo (!empty($aircrafts)) ? $aircrafts['pilots'] : ''; ?>'>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="rad">
									<label for="value_type" class="col-sm-2 control-label">Passengers<span class="text-danger">*</span></label>
									<div class="col-sm-2">
										<input type="text" class="wdt25 form-control numeric" maxlength='5' name="passenger" required="" value='<?php echo (!empty($Passengers)) ? $aircrafts['passenger'] : ''; ?>'>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="rad">
									<label for="value_type" class="col-sm-2 control-label">Flight Speed<span class="text-danger">*</span></label>
									<div class="col-sm-2">
										<input type="text" class="wdt25 form-control numeric" maxlength='5' name="speed" required="" value='<?php echo (!empty($aircrafts)) ? $aircrafts['speed'] : ''; ?>'> KMH or MPH
									</div>
								</div>
							</div>

						</div>

					</fieldset>
					<fieldset>
						<legend class="sm_titl">Weight & Fuel</legend>
						<div class="col-xs-12 col-sm-12 fare_info nopad">
							<?php $fuel = explode(',', $aircrafts['fuel_type']); ?>
							<div class="form-group radio">
								<label for="value_type" class="col-sm-2 control-label">Fuel Type<span class="text-danger">*</span></label>
								<div class="col-sm-5 pl-30">
									<label class="radio-inline">
										<input type="checkbox" class="crs_is_domestic" name="fuel_type[]" <?php echo (in_array("0", $fuel)) ? 'checked' : ''; ?> value="0">AVGAS 100LL
									</label>
									<label class="radio-inline">
										<input type="checkbox" class="crs_is_domestic" name="fuel_type[]" <?php echo (in_array("1", $fuel)) ? 'checked' : ''; ?> value="1">MOGAS 95
									</label>
									<label class="radio-inline">
										<input type="checkbox" class="crs_is_domestic" name="fuel_type[]" <?php echo (in_array("2", $fuel)) ? 'checked' : ''; ?> value="2">JET A1
									</label>
								</div>
							</div>
							<div class="form-group">
								<div class="org_row">
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">MTOW<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control numeric" maxlength='5' name="mtow" required="" value='<?php echo (!empty($aircrafts)) ? $aircrafts['mtow'] : ''; ?>'>
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Empty Weight<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control numeric" maxlength='5' name="empty_weight" required value='<?php echo (!empty($aircrafts)) ? $aircrafts['empty_weight'] : ''; ?>'>
											</div>
										</div>
									</div>
								</div>
								<div class="org_row">
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Max Usable Fuel<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control numeric" maxlength='5' name="max_usable_fuel" required value='<?php echo (!empty($aircrafts)) ? $aircrafts['max_usable_fuel'] : ''; ?>'>
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Maximum Baggage<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control numeric" maxlength='5' name="maximum_baggage" required value='<?php echo (!empty($aircrafts)) ? $aircrafts['maximum_baggage'] : ''; ?>'>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</fieldset>
					<fieldset>
						<legend class="sm_titl"> Add Component</legend>
						<div class="col-xs-12 col-sm-12 fare_info nopad">
							<div class="form-group">
								<?php if (isset($aircrafts['component'])) {	 ?>
									<div class="col-xs-12 col-sm-12 fare_info">
										<strong class="padL5">Added Component</strong>
										<ol>
											<?php

											foreach ($aircrafts['component'] as $compo) {
												$r = rand();
												echo '<li class="close' . $r . '">
							<span  class="choice ">' . $compo->partcomponent . '-' . $compo->component_name . '-' . $compo->serialnumber . '<i id="close' . $r . '" onclick=removeadded(this.id) class="fa fa-close"></i> 
							<input type="hidden" name="component_name[]" class="" value="' . $compo->component_name . '">
							<input type="hidden" name="serialcomponent[]" class="chosen" value="' . $compo->serialcomponent . '">
							<input type="hidden" name="partcomponent[]"  value="' . $compo->partcomponent . '">
							<input type="hidden" name="fitted_date[]" class="" value="' . $compo->fitted_date . '">
							<input type="hidden" name="fitted_time[]" class="" value="' . $compo->fitted_time . '">
							</span></li>';
											}

											?>
										</ol>
									</div>
								<?php } ?>
								<div class="org_row">
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Select Component Name or Part Number <span class="text-danger">*</span></label>
											<div class="col-md-8">
												<select class="form-control component_serial chosen-select" id='1' name="partcomponent[]">
													<option value=''>select</option>
													<?php foreach ($allcomp_type as $ct) {
														$selected = '';
														$selected = ($ct['origin'] == $aircrafts['component']) ? 'selected' : '';
													?>
														<option <?= $selected ?> value="<?= $ct['part_number'] ?>"><?= $ct['component_name'] . ' (' . $ct["part_number"] . ')' ?></option>
													<?php } ?>
												</select>
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Select Component Serial Number<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input class='component_name-1' type='hidden' name='component_name[]'>
												<select class="form-control component_part-1  chosen-selecttt" name="serialcomponent[]">
													<option value=''>select</option>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</fieldset>
					<fieldset>
						<div class="col-xs-12 col-sm-12 fare_info nopad">
							<div class="form-group">
								<div class="org_row">
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Component Fitted Date<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control datepicker" readonly id="fitted_datetime_from" name="fitted_date[]">
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Component Fitted Time<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control" maxlength='5' name="fitted_time[]">
											</div>
										</div>
									</div>
								</div>
								<div class=" component-2"></div>
								<div class="col-xs-12">
									<button class="btn btn-info pull-right add_component" id='1' type="button">Add</button>
								</div>
							</div>
						</div>
					</fieldset>
					<fieldset>
						<legend class="sm_titl">Documentation</legend>
						<div class="col-xs-12 col-sm-12 fare_info nopad">

							<div class="form-group">

								<?php if (isset($aircrafts['documentation'])) {	 ?>
									<div class="col-xs-12 col-sm-12 fare_info">
										<?php foreach ($aircrafts['documentation'] as $k => $document) {
											//debug($document);	

										?>


											<div class="doc-<?= $k ?>">
												<div class="org_row">
													<div class="col-sm-6">
														<div class="rad">
															<label for="value_type" class="col-sm-4 control-label">Select Documentation<span class="text-danger">*</span></label>
															<div class="col-md-8">
																<select class="form-control" required name="documentation_type[]">
																	<option value=''>select</option>

																	<?php foreach ($doc_type as $dt) {
																		$selected = '';
																		$selected = ($dt['origin'] == $document->documentation_type) ? 'selected' : '';
																	?>

																		<option <?= $selected ?> value="<?= $dt['origin'] ?>"><?= $dt['document_name'] ?></option>
																	<?php } ?>



																</select>
															</div>
														</div>
													</div>

													<div class="col-sm-6">
														<div class="rad">
															<label for="value_type" class="col-sm-4 control-label">Issue Date<span class="text-danger">*</span></label>
															<div class="col-md-8">
																<input type="text" class="wdt25 form-control datepicker " readonly id="created_datetime_from<?= $k ?>" name="issue_date[]" required value='<?php echo (!empty($aircrafts)) ? $document->issue_date : ''; ?>'>
															</div>
														</div>
													</div>
												</div>

												<div class="org_row">
													<div class="col-sm-6">
														<div class="rad">
															<label for="value_type" class="col-sm-4 control-label">Date of Expiry<span class="text-danger">*</span></label>
															<div class="col-md-8">
																<input type="text" class="wdt25 form-control datepicker1 " id="created_datetime_to<?= $k ?>" readonly name="date_of_expiry[]" required value='<?php echo (!empty($aircrafts)) ?  $document->date_of_expiry : ''; ?>'>
															</div>
														</div>
													</div>

													<div class="col-sm-6">
														<div class="rad">
															<label for="value_type" class="col-sm-4 control-label">Select File<span class="text-danger">*</span></label>
															<div class="col-md-8">
																<?php if (!empty($aircrafts)) {
																	if (!empty($document->file)) { ?>
																		<input type="file" class="wdt25 form-control" id='newimage_<?= $k ?>' name="files[]">
																	<?php } else { ?>
																		<input type="file" class="wdt25 form-control" id='newimage_<?= $k ?>' name="files[]" required>
																	<?php }
																	?>


																<?php } else { ?>
																	<input type="files" class="wdt25 form-control" name="files[]" required>
																<?php } ?>

															</div>
														</div>
													</div>
													<?php
													if (!empty($document->file)) {
														$ext = explode('.', $document->file);

														echo '<div class="img_' . $k . ' pull-right"><a target="_blank" href="' . $GLOBALS['CI']->template->domain_image_full_path123($document->file) . '" >View </a><i id="img_' . $k . '" onclick=removeimg(this.id) class="fa fa-close"></i>
							  <input type="hidden" name="files[]" value=' . $document->file . '></div>';
													}
													?>
												</div>
												<div class="org_row">
													<div class="col-sm-12">
														<div class="rad">
															<label for="value_type" class="col-sm-2 control-label">Remarks</label>
															<div class="col-md-10" style="padding-left:9px">
																<textarea class="wdt25 form-control" name="remarks[]"><?php echo (!empty($aircrafts)) ? $document->remarks : ''; ?></textarea>
															</div>
														</div>
													</div>
												</div>
												<button type="button" class="btn btn-danger pull-right  delete_fare" id="doc-<?= $k ?>" onclick="remove(this.id)" )>Remove</button>
											</div>
										<?php
										}




										?>
									</div>
								<?php } ?>


								<div class="org_row">
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Select Documentation</label>
											<div class="col-md-8">


												<select class="form-control" name="documentation_type[]">
													<option value=''>select</option>

													<?php foreach ($doc_type as $dt) {
														$selected = '';
														$selected = ($dt['origin'] == $aircrafts['documentation_type']) ? 'selected' : '';
													?>

														<option <?= $selected ?> value="<?= $dt['origin'] ?>"><?= $dt['document_name'] ?></option>
													<?php } ?>



												</select>
											</div>
										</div>
									</div>

									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Issue Date</label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control datepicker " readonly id="sdf" name="issue_date[]" value='<?php echo (!empty($aircrafts)) ? $aircrafts['issue_date'] : ''; ?>'>
											</div>
										</div>
									</div>
								</div>

								<div class="org_row">
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Date of Expiry</label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control datepicker1 " id="ds" readonly name="date_of_expiry[]" value='<?php echo (!empty($aircrafts)) ? $aircrafts['date_of_expiry'] : ''; ?>'>
											</div>
										</div>
									</div>

									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Select File</label>
											<div class="col-md-8">
												<?php


												if (!empty($aircrafts)) { ?>
													<input type="file" class="wdt25 form-control aaa" id='newimage' name="files[]">
												<?php } else { ?>
													<input type="file" class="wdt25 form-control" name="files[]" required>
												<?php } ?>

											</div>
										</div>
									</div>
									<?php
									/*if(!empty($aircrafts['file'])) 
						{   
							
								echo '<div class="img pull-right"><a target="_blank" href="'. $GLOBALS['CI']->template->domain_image_full_path123($aircrafts['file']).' " >View </a><i id="img" onclick=removeittmg(this.id) class="fa fa-close"></i>
							  <input type="hidden" name="files[]" value='.$aircrafts['file'].'></div>' ;
							
						} */
									?>
								</div>
								<div class="org_row">
									<div class="col-sm-12">
										<div class="rad">
											<label for="value_type" class="col-sm-2 control-label">Remarks</label>
											<div class="col-md-10" style="padding-left:9px">
												<textarea class="wdt25 form-control" name="remarks[]"><?php echo (!empty($aircrafts)) ? $aircrafts['remarks'] : ''; ?></textarea>
											</div>
										</div>
									</div>
								</div>

								<div class="document-2"></div>
								<div class="col-xs-12">
									<button class="btn btn-info pull-right add_document" id='1' type="button">Add</button>
								</div>

							</div>
						</div>
					</fieldset>

					<fieldset>
						<legend class="sm_titl"> Aircraft Base</legend>
						<div class="col-xs-12 col-sm-12 fare_info nopad">
							<div class="form-group">
								<div class="org_row">
									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Select Airport Name<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" autocomplete="off" placeholder="Search" id="autocomplete" required value='<?php echo (!empty($aircrafts)) ? $aircrafts['icao_code_of_airport'] : ''; ?>' class="form-control ui-autocomplete-input">

												<input type="hidden" id="selectuser_id" name="icao_code_of_airport" value='<?php echo (!empty($aircrafts)) ? $aircrafts['icao_code'] : ''; ?>'>
											</div>
										</div>
									</div>

									<div class="col-sm-6">
										<div class="rad">
											<label for="value_type" class="col-sm-4 control-label">Date of Induction at Base<span class="text-danger">*</span></label>
											<div class="col-md-8">
												<input type="text" class="wdt25 form-control " id='inductiondatepicker' readonly name="induction_at_base" required value='<?php echo (!empty($aircrafts)) ? $aircrafts['induction_at_base'] : ''; ?>'>
											</div>
										</div>
									</div>

								</div>
							</div>
						</div>
					</fieldset>
					<div class="clearfix"></div>

					<?php if (empty($aircrafts)) { ?>

					<?php } else { ?>

					<?php } ?>
					<div class="col-xs-12 col-sm-12">
						<p id='error' style='color:red'></p>
						<div class="clearfix col-md-offset-1">
							<input type="hidden" name="origin" value='<?php echo (!empty($aircrafts)) ? $aircrafts['origin'] : '0'; ?>'>
							<button class="btn btn-sm btn-success pull-right" type="submit">Submit</button>
						</div>
					</div>

				</div>
			</form>

			<?php $aircrafts_count = isset($aircrafts['documentation']) ? count($aircrafts['documentation']) : 0;   ?>
			<input type='hidden' id='nodoc' value='<?= $aircrafts_count ?>'>
		</div>
	</div>
</div>
<script src='../jquery.js' type='text/javascript'></script>
<link href='jquery-ui.min.css' rel='stylesheet' type='text/css'>
<script src='jquery-ui.min.js' type='text/javascript'></script>
<!-- Script -->
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script src="https://cdn.rawgit.com/harvesthq/chosen/gh-pages/chosen.jquery.min.js"></script>
<link href="https://cdn.rawgit.com/harvesthq/chosen/gh-pages/chosen.min.css" rel="stylesheet" />


<script type='text/javascript'>
	$(document).on('focus', ".datepicker", function() {
		$('.datepicker').datepicker({
			dateFormat: "dd-mm-yy",
			maxDate: 0,
			changeMonth: true,
			changeYear: true,
			numberOfMonths: [1, 2]
		});
	});

	$(document).on('focus', ".datepicker1", function() {
		$('.datepicker1').datepicker({
			dateFormat: "dd-mm-yy",
			minDate: 0,
			changeMonth: true,
			changeYear: true,
			numberOfMonths: [1, 2]
		});
	});

	function Validate() {

		var arr = [];
		$(".chosen-selecttt").each(function(index) {
			if ($(this).val().trim()) {
				arr.push($(this).val().trim());
			}
		});

		$(".chosen").each(function(index) {
			if ($(this).val().trim()) {
				arr.push($(this).val().trim());
			}
		});

		console.log(arr)
		console.log(arr.length)
		// return false;
		if (arr.length == 0) {
			$('#error').html('Select Component Name / Serial Number');
			return false;
		} else {

			console.log(arr)
			let result = false;
			// create a Set with array elements
			const s = new Set(arr);
			// compare the size of array and Set
			if (arr.length !== s.size) {
				result = true;
			}

			if (result) {
				$('#error').html('Add Component contains duplicate elements');
				return false;
			} else {
				$('#error').html('');
				return true;
				//$('#add_aircraft').submit();
			}
		}
	}
	$(function() {
		$("#inductiondatepicker").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			numberOfMonths: 2
		});
	});




	$(function() {
		url = app_base_url + "index.php/ajax/auto_suggest_airport_name";
		$("#autocomplete").autocomplete({
			source: function(request, response) {

				$.ajax({
					url: url,
					type: 'post',
					dataType: "json",
					data: {
						search: request.term
					},
					success: function(data) {
						response(data);
					}
				});
			},
			select: function(event, ui) {
				$('#autocomplete').val(ui.item.label); // display the selected text
				$('#selectuser_id').val(ui.item.value); // save selected id to input
				return false;
			}
		});
	});

	function split(val) {
		return val.split(/,\s*/);
	}

	function extractLast(term) {
		return split(term).pop();
	}


	$(document).ready(function() {

		$('.datepicker').datepicker({
			dateFormat: "dd-mm-yy",
			maxDate: 0,
			changeMonth: true,
			changeYear: true,
			numberOfMonths: [1, 2]
		});





		$('.add_component').click(function() {

			id = $('.add_component').attr('id');
			nextid = parseInt(id) + 1;
			$('.add_component').attr('id', nextid);
			nid = parseInt(nextid) + 1;
			add = '<div class="form-group"><div class="org_row"><div class="col-sm-6"><div class="rad"><label for="value_type" class="col-sm-4 control-label">Select Component Name or Part Number<span class="text-danger">*</span></label><div class="col-md-8"><select class="form-control component_serial chosen-select"  name="partcomponent[]" id="' + nextid + '"><option value="" >select</option><?php foreach ($allcomp_type as $ct) {
																																																																																																				$selected = '';
																																																																																																				$selected = ($ct['origin'] == $aircrafts['component']) ? 'selected' : ''; ?><option <?= $selected ?> value="<?= $ct['part_number'] ?>"><?= $ct['component_name'] . '(' . $ct['part_number'] . ')' ?></option><?php } ?></select></div></div></div><div class="col-sm-6"><div class="rad"><label for="value_type" class="col-sm-4 control-label">Select Component Serial Number<span class="text-danger">*</span></label><div class="col-md-8"><input type="hidden" class="component_name-' + nextid + '" name="component_name[]"><select name="serialcomponent[]" class="form-control component_part-' + nextid + ' chosen-selecttt"><option value="">select</option></select></div></div></div></div></div><div class="form-group"><div class="org_row"> <div class="col-sm-6"> <div class="rad"><label for="value_type" class="col-sm-4 control-label">Component Fitted Date<span class="text-danger">*</span></label> <div class="col-md-8"> <input type="text" class="wdt25 form-control datepicker"  readonly   name="fitted_date[]" required=""  > </div> </div> </div> <div class="col-sm-6"> <div class="rad">  <label for="value_type" class="col-sm-4 control-label">Component Fitted Time<span class="text-danger">*</span></label> <div class="col-md-8"> <input type="text" class="wdt25 form-control" maxlength="5" name="fitted_time[]" required="" ></div></div></div></div></div>     <button type="button" class="btn btn-danger pull-right  delete_fare" id="component-' + nextid + '" onclick="remove(this.id)")>Remove</button>';


			$(".component-" + nextid).append(add);
			ad = '<div class="component-' + nid + '"></div>';
			$(ad).insertAfter($(".component-" + nextid));

			$(".chosen-select").chosen({
				no_results_text: "Oops, nothing found!"
			})

			$("select").on('change', function() {
				id = $(this).attr('id');
				var origin = $(this).val();

				$.ajax({
					type: "POST",
					url: app_base_url + "index.php/flight/component_partno",
					data: {
						origin: origin
					},
					dataType: "text",
					cache: false,
					success: function(results) {

						result1 = JSON.parse(results)

						// var name = result1[0].split('-');
						$('.component_name-' + id).val(result1[0]);
						$('.component_part-' + id).html(result1[1]);


						//$('.component_part-'+id).html(results);

					}
				});
			});

		});





		$('.add_document').click(function() {

			var cou = $('#nodoc').val();
			if (cou > 0) {
				cou++;
				$('#nodoc').val(cou);

			}



			n_id = $('.add_document').attr('id');
			n_nextid = parseInt(n_id) + 1;
			$('.add_document').attr('id', n_nextid);
			n_nid = parseInt(n_nextid) + 1;
			n_add = '<div class="form-group" style="padding-left:9px" ><div class="org_row"><div class="col-sm-6"> <div class="rad"><label for="value_type" class="col-sm-4 control-label">Select Documentation<span class="text-danger">*</span></label>   <div class="col-md-8"><select class="form-control component_serial"  name="documentation_type[]" id="' + n_nextid + '"><option value="" >select</option><?php foreach ($doc_type as $ct) {
																																																																																																						$selected = ''; ?><option  value="<?= $ct['origin'] ?>"><?= $ct['document_name'] ?></option><?php } ?></select>  </div> </div></div><div class="col-sm-6"> <div class="rad">   <label for="value_type" class="col-sm-4 control-label">Issue Date<span class="text-danger">*</span></label>   <div class="col-md-8"> <input type="text" class="wdt25 form-control  datepicker" readonly="" name="issue_date[]" required="" value="">   </div> </div></div>  </div>  <div class="org_row">  <div class="col-sm-6">   <div class="rad"> <label for="value_type" class="col-sm-4 control-label">Date of Expiry<span class="text-danger">*</span></label> <div class="col-md-8">   <input type="text" class="wdt25 form-control  datepicker1" readonly="" name="date_of_expiry[]" required="" value=""> </div>   </div>  </div>  <div class="col-sm-6">   <div class="rad"> <label for="value_type" class="col-sm-4 control-label">Select File<span class="text-danger">*</span></label> <div class="col-md-8">  <input type="file" class="wdt25 form-control" name="files[]" required=""> 						 </div>   </div>  </div></div><div class="org_row">  <div class="col-sm-12">   <div class="rad"> <label for="value_type" class="col-sm-2 control-label">Remarks</label> <div class="col-md-10" style="padding-left:9px">   <textarea class="wdt25 form-control" name="remarks[]"></textarea> </div>   </div>  </div></div></div> <button type="button" class="btn btn-danger pull-right  delete_fare" id="document-' + n_nextid + '" onclick="remove(this.id)")>Remove</button>  ';


			$(".document-" + n_nextid).append(n_add);
			n_ad = '<div class="document-' + n_nid + '"></div>';
			$(n_ad).insertAfter($(".document-" + n_nextid));
		});
		$('.add_exit').click(function() {





			n_id = $('.add_exit').attr('id');
			n_nextid = parseInt(n_id) + 1;
			$('.add_exit').attr('id', n_nextid);
			n_nid = parseInt(n_nextid) + 1;
			n_add = ' <div class="form-group"><div class="org_row">      <div class="col-sm-6"> <div class="rad"><label for="value_type" class="col-sm-4 control-label">From Row<span class="text-danger">*</span></label>           <div class="col-md-8"><input type="text" class="wdt25 form-control rows_columns" onchange="myFunction(this.value)" name="from_exit[]" id="from_exit" required="" value="">   </div> </div></div><div class="col-sm-6"> <div class="rad">   <label for="value_type" class="col-sm-4 control-label">To Row<span class="text-danger">*</span></label>         <div class="col-md-8"> <input type="text" class="wdt25 form-control rows_columns"  name="to_exit[]" required="" value="">           </div> </div></div>  </div> </div> <button type="button" class="btn btn-danger pull-right  delete_fare" id="exit-' + n_nextid + '" onclick="remove(this.id); myFunction(this.id);">Remove</button>   ';


			$(".exit-" + n_nextid).append(n_add);
			n_ad = '<div class="exit-' + n_nid + '"></div>';
			$(n_ad).insertAfter($(".exit-" + n_nextid));
		});


		$('.component_serial').on('change', function() {
			id = $('.component_serial').attr('id');
			var origin = $(this).val();


			$.ajax({
				type: "POST",
				url: app_base_url + "index.php/flight/component_partno",
				data: {
					origin: origin
				},
				dataType: "text",
				cache: false,
				success: function(results) {
					result1 = JSON.parse(results)

					//var name = result1[0].split('-');
					$('.component_name-' + id).val(result1[0]);
					$('.component_part-' + id).html(result1[1]);
				}
			});
		});


		$(".chosen-select").chosen({
			no_results_text: "Oops, nothing found!"
		})
	});

	function remove(x) {
		$('.' + x).remove();

		var cou = $('#nodoc').val();
		if (cou > 0) {
			cou--;
			$('#nodoc').val(cou);

		}
		if (cou == 0) {

			$(".aaa").attr("required", "true");
		}

	}

	function removeimg(x) {
		console.log(x);
		var s = x.split('_');
		console.log(s);
		$('.' + x).remove();
		$("#newimage_" + s[1]).attr("required", "true");
	}

	function removeadded(x) {
		$('.' + x).remove();
		// $('#add_aircraft').submit();
	}
</script>
<!----Added code for seat layout---->
<script>
	var myArray = [];

	function myFunction(val) {
		$('#flight_table').html("");

		var myArray = [];
		var inputs = document.getElementsByName('from_exit[]');

		// Create an array to hold their values

		// Loop through the NodeList and push each value into the array
		for (var i = 0; i < inputs.length; i++) {
			myArray.push(parseInt(inputs[i].value));
		}

		// Do something with the values
		console.log("Input values:", myArray);
		//console.log(myArray);
		$('#flight_table').append(appending);

	}

	function appending() {

		var seat_gap = parseInt($('#seat_gap').val());
		var seats = "";
		var rows = parseInt($('#rows').val());
		var columns = $('#columns').val();
		var myArray = [];
		var inputs = document.getElementsByName('from_exit[]');

		// Create an array to hold their values

		// Loop through the NodeList and push each value into the array
		for (var i = 0; i < inputs.length; i++) {
			myArray.push(parseInt(inputs[i].value));
		}


		var first_row = "<tr><td></td>";

		for (var m = 0; m < columns.length; m++) {
			first_row += "<td>" + columns[m] + "</td>";
			if ((m + 1) % seat_gap == 0) {
				first_row += "<td></td><td></td>";
			}

		}
		first_row += "</tr>";
		//console.log(myArray);
		if (!isNaN(rows) && columns != undefined && columns != null && columns != "") {
			seats = "";
			for (var k = 1; k <= rows; k++) {
				if (myArray.includes(parseInt(k))) {
					//      	  alert(myArray);
					seats += '<tr><td>' + k + '</td>';
					for (var j = 0; j < columns.length; j++) {
						seats += '<td><input class="form-control" name="seat_numbers[]" value="' + ((k.toString()) + columns[j]) + '" type="hidden" /><img class="choose_seat_0" data-toggle="tooltip" src="<?php echo base_url() ?>../extras/system/template_list/template_v1/images/available.png" title="Seat No. : ' + ((k.toString()) + columns[j]) + '"></td>';
						if ((j + 1) % seat_gap == 0) {
							seats += "<td></td><td></td>";
						}
					}
					seats += '</tr>';
					seats += '<tr>';
					var p = 0;
					for (var j = 0; j < columns.length; j++) {
						//seats+='<td></td>';
						if ((j + 1) % seat_gap == 0) {
							// seats+="";
							p = p + 2;
						}
					}
					seats += '<td colspan="' + (j + 1 + p) + '" class="p-10"><span class="exit_door">EXIT</span><span class="exit_door e_rght">EXIT</span></td></tr>';
				} else {

					seats += '<tr><td>' + k + '</td>';
					for (var j = 0; j < columns.length; j++) {
						seats += '<td><input class="form-control" name="seat_numbers[]" value="' + ((k.toString()) + columns[j]) + '" type="hidden" /><img class="choose_seat_0" data-toggle="tooltip" src="<?php echo base_url() ?>../extras/system/template_list/template_v1/images/available.png" title="Seat No. : ' + ((k.toString()) + columns[j]) + '"></td>';
						if ((j + 1) % seat_gap == 0) {
							seats += "<td></td><td></td>";
						}
					}
					seats += '</tr>';

				}

			}
			return first_row + seats
		} else {
			return first_row + seats
		}

	}
</script>


<script>
	$(document).ready(function() {
		var myArray = [];
		$('.rows_columns').on('keyup', function(e) {

			console.log('ASCII' + e.keyCode);
			if ((e.keyCode > 64 && e.keyCode < 91) || (e.keyCode >= 97 && e.keyCode <= 122)) {
				var clicked_element = $(this);
				var val = clicked_element.val(); //fetching the entered value
				var typed_character = val.substring(val.length - 1, val.length).toUpperCase(); //extracting the last typed alphabet
				console.log(typed_character + " Typed Chanracter");
				if (clicked_element.attr('id') == 'columns') {
					if (/^[a-zA-Z]+$/.test(val)) { //testing regular expression with entered value to allow only alphabets
						if (typed_character && typed_character != null && typed_character != undefined) {
							if (val.match(typed_character)) { //checking alphabet already exist 
								// alert(clicked_element.val());
								clicked_element.val(val.substring(0, val.length - 1).toUpperCase()); //deleting the last character id already exist
								// alert(val.substring(0, val.length-1).toUpperCase());
								alert('Coulumn ' + typed_character + ' alredy entered.');
							} else {
								clicked_element.val(val.toUpperCase());
							}
						}
					} else {
						console.log('Last Character Deleted')
						clicked_element.val(val.substring(0, val.length - 1).toUpperCase()); //deleting last character 
					}
				}
			}
			$('#col_count').html('Column Count : ' + $('#columns').val().length)
			// $('#lopa').html('');
			$('#flight_table').html("");
			// $('#lopa').append(appending);

			$('#flight_table').append(appending);

			col_count = parseInt(($('#columns').val()).length);
			// console.log(col_count);
			row_count = parseInt($('#rows').val());
			if (!isNaN(col_count * row_count)) {
				$('#seating_capacity').val(col_count * row_count)
			}
		});


		// function appending(){
		//    var seat_gap=parseInt($('#seat_gap').val());
		//    var seats="";
		//    var rows=parseInt($('#rows').val());
		//    var columns=$('#columns').val();
		//    if(!isNaN(rows) && columns!=undefined && columns!=null && columns!=""){
		//       for(var i=1;i<=columns.length;i++){
		//          seats+='<div class="col-md-1">';
		//          for(var j=1;j<=rows;j++){
		//             seats+='<input class="form-control" name="seat_numbers[]" value="'+((j.toString())+columns[i-1])+'" type="text" /><br/>';
		//             // console.log(columns[i]+j);
		//          }
		//          seats+='</div>';
		//           if(i%seat_gap==0){
		//             seats+="<div class='col-md-2'>   </div>";
		//          }
		//       }            
		//       return seats
		//    }else{
		//       return seats
		//    }

		// }

	});
</script>