<?php
$booking_list = $data['booking_details'] ?? [];
$booking_info = !empty($booking_list) ? $booking_list[0] : [];
$app_reference    = $booking_info['app_reference'] ?? '';
$from_loc         = $booking_info['from_loc'] ?? '';
$to_loc           = $booking_info['to_loc'] ?? '';
$journey_start    = $booking_info['journey_start'] ?? '';
$booking_status   = $booking_info['status'] ?? '';
$booking_made_on  = !empty($booking_info['created_datetime']) ? app_friendly_date($booking_info['created_datetime']) : '';
$trip_type        = $booking_info['trip_type'] ?? '';
$currency         = $booking_info['currency'] ?? '';
$grand_total      = $booking_info['grand_total'] ?? '0';
$transaction_details = $booking_info['booking_transaction_details'] ?? [];
?>
<div class="bgcolor">
	<div class="container-fluid pad0">
		<div class="row">
		<?php if (is_logged_in_user()) {
			$col_md = '9';
		} else {
			$col_md = '12';
		} ?>
		<div class="col-sm-<?php echo $col_md; ?>">
			<div class="row top20">
				<?php if (is_logged_in_user()) { ?>
				<div class="col-md-11 blue bold mb10 get_hand_cursor f12">
					<a href="<?php echo base_url(); ?>flight/manage_booking">&lt; Back to My Bookings</a>
				</div>
				<?php } ?>
				<div class="col-sm-6 font12">
					<h1 class="details_bus_mb">View Flight Booking Details</h1>
					<h5 class="mb10 mt30 colorgray font12">
						<span class="bold"><i class="fa fa-plane"></i> <?php echo htmlspecialchars($from_loc); ?> to <?php echo htmlspecialchars($to_loc); ?></span>
						<?php if (!empty($journey_start)) { echo ' &mdash; ' . app_friendly_datetime($journey_start); } ?>
					</h5>
				</div>
				<div class="col-sm-6">
					<h5 class="pull-right m0 font13">
						<span class="colorgray">Booking ID:</span>
						<span class="bold"><?php echo htmlspecialchars($app_reference); ?></span>
					</h5>
					<div class="clearfix"></div>
					<h5 class="pull-right mb0 mt5 colorgray font12">Booking Date: <?php echo $booking_made_on; ?></h5>
				</div>
			</div>

			<div class="right_part_itry">
				<div class="col-md-12 pad0 f12">
					<div class="bs-example bg_top_itry_mbb">
						<div class="col-md-8 pad20">
							<p class="m0"><strong>From:</strong> <?php echo htmlspecialchars($from_loc); ?></p>
							<p class="m0"><strong>To:</strong> <?php echo htmlspecialchars($to_loc); ?></p>
							<p class="m0"><strong>Trip Type:</strong> <?php echo htmlspecialchars(ucfirst($trip_type)); ?></p>
							<p class="m0"><strong>Booking Status:</strong>
								<span class="<?php echo booking_status_label($booking_status); ?>">
									<?php echo htmlspecialchars($booking_status); ?>
								</span>
							</p>
						</div>
						<div class="col-md-4 pad0">
							<div class="depart_hotel_rit11 pad30 black">
								<p class="m0"><strong>Grand Total:</strong> <?php echo htmlspecialchars($currency . ' ' . $grand_total); ?></p>
							</div>
						</div>
					</div>

					<?php if (valid_array($transaction_details)) { ?>
					<div class="col-md-12 pad0 f12 top20">
						<h3 class="details_fly_h3">Flight Segments</h3>
						<?php foreach ($transaction_details as $trans) {
							$pnr = $trans['pnr'] ?? '';
							$segment_details = $trans['segment_details'] ?? [];
							$customer_details = $trans['booking_customer_details'] ?? [];
						?>
						<div class="bs-example bg_top_itry_mbb">
							<?php if (!empty($pnr)) { ?>
							<p class="m0"><strong>PNR:</strong> <?php echo htmlspecialchars($pnr); ?></p>
							<?php } ?>
							<?php if (valid_array($segment_details)) {
								foreach ($segment_details as $seg) { ?>
								<div class="col-md-12 pad0">
									<p class="m0">
										<strong><?php echo htmlspecialchars(@$seg['from_loc']); ?></strong>
										&rarr;
										<strong><?php echo htmlspecialchars(@$seg['to_loc']); ?></strong>
										<?php if (!empty($seg['departure_time'])) { echo ' | Dep: ' . app_friendly_datetime($seg['departure_time']); } ?>
										<?php if (!empty($seg['arrival_time'])) { echo ' | Arr: ' . app_friendly_datetime($seg['arrival_time']); } ?>
										<?php if (!empty($seg['airline_pnr'])) { echo ' | Airline PNR: ' . htmlspecialchars($seg['airline_pnr']); } ?>
									</p>
								</div>
							<?php } } ?>
							<?php if (valid_array($customer_details)) { ?>
							<div class="col-md-12 pad0 top20">
								<h4>Passengers</h4>
								<table class="table table-condensed table-bordered">
									<thead>
										<tr>
											<th>Name</th>
											<th>Type</th>
											<th>Ticket Number</th>
										</tr>
									</thead>
									<tbody>
									<?php foreach ($customer_details as $pax) { ?>
										<tr>
											<td><?php echo htmlspecialchars(trim((@$pax['title'] ?? '') . ' ' . (@$pax['first_name'] ?? '') . ' ' . (@$pax['last_name'] ?? ''))); ?></td>
											<td><?php echo htmlspecialchars(@$pax['passenger_type'] ?? ''); ?></td>
											<td><?php echo htmlspecialchars(@$pax['TicketNumber'] ?? '---'); ?></td>
										</tr>
									<?php } ?>
									</tbody>
								</table>
							</div>
							<?php } ?>
						</div>
						<?php } ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		</div>
	</div>
</div>
