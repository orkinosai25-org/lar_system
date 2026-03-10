<?php
if (is_array($search_params)) {
	extract($search_params);
}
$_datepicker = array(array('from_date', PAST_DATE), array('to_date', PAST_DATE));
$this->current_page->set_datepicker($_datepicker);
$this->current_page->auto_adjust_datepicker(array(array('from_date', 'to_date')));
?>
<div class="bodyContent col-md-12">
	<div class="panel panel-default clearfix">
		<div class="panel-heading">
			<?=$GLOBALS['CI']->template->isolated_view('report/report_tab_b2c')?>
		</div>
		<div class="panel-body">
			<div class="clearfix">
				<?php echo $GLOBALS['CI']->template->isolated_view('report/make_search_easy'); ?>
			</div>
			<hr>
			<h4>Search Panel <button class="btn btn-primary btn-sm toggle-btn" data-toggle="collapse" data-target="#show-search">+</button></h4>
			<hr>
			<div id="show-search" class="collapse">
				<form method="GET" autocomplete="off">
					<div class="clearfix form-group">
						<div class="col-xs-4">
							<label>Package Name</label>
							<input type="text" class="form-control" name="package_name" value="<?=@$package_name?>" placeholder="Package Name">
						</div>
						<div class="col-xs-4">
							<label>Enquiry From Date</label>
							<input type="text" readonly id="from_date" class="form-control" name="from_date" value="<?=@$from_date?>" placeholder="From Date">
						</div>
						<div class="col-xs-4">
							<label>Enquiry To Date</label>
							<input type="text" readonly id="to_date" class="form-control disable-date-auto-update" name="to_date" value="<?=@$to_date?>" placeholder="To Date">
						</div>
					</div>
					<div class="col-sm-12 well well-sm">
						<button type="submit" class="btn btn-primary">Search</button>
						<button type="reset" class="btn btn-warning">Reset</button>
						<a href="<?php echo base_url().'index.php/report/b2c_package_report?' ?>" id="clear-filter" class="btn btn-primary">Clear Filter</a>
					</div>
				</form>
			</div>
		</div>

		<div class="clearfix" style="overflow: auto">
			<?php echo get_package_table($table_data, $total_rows); ?>
		</div>
	</div>
</div>

<?php
function get_package_table($table_data, $total_rows)
{
	$pagination = '<div class="pull-left">' . $GLOBALS['CI']->pagination->create_links() . ' <span class="">Total ' . $total_rows . ' Enquiries</span></div>';
	$report_data  = '<div id="tableList" class="clearfix">';
	$report_data .= $pagination;
	$report_data .= '<table class="table table-condensed table-bordered" id="b2c_report_package_table">
		<thead>
		<tr>
			<th>S.No</th>
			<th>Package Name</th>
			<th>Customer Name</th>
			<th>Email</th>
			<th>Phone</th>
			<th>Enquiry Date</th>
			<th>Status</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th>S.No</th>
			<th>Package Name</th>
			<th>Customer Name</th>
			<th>Email</th>
			<th>Phone</th>
			<th>Enquiry Date</th>
			<th>Status</th>
		</tr>
		</tfoot>
		<tbody>';

	if (valid_array($table_data)) {
		$sno = 1;
		foreach ($table_data as $row) {
			$enquiry_date = !empty($row['date']) ? date('d-m-Y', strtotime($row['date'])) : '---';
			$status_label = !empty($row['enquiry_status']) ? $row['enquiry_status'] : 'NEW';
			$report_data .= '<tr>
				<td>' . $sno++ . '</td>
				<td>' . htmlspecialchars(@$row['package_name']) . '</td>
				<td>' . htmlspecialchars(@$row['first_name']) . '</td>
				<td>' . htmlspecialchars(@$row['email']) . '</td>
				<td>' . htmlspecialchars(@$row['phone']) . '</td>
				<td>' . $enquiry_date . '</td>
				<td><span class="label label-info">' . htmlspecialchars($status_label) . '</span></td>
			</tr>';
		}
	} else {
		$report_data .= '<tr><td colspan="7" class="text-center">No Records Found</td></tr>';
	}

	$report_data .= '</tbody></table></div>';
	return $report_data;
}
?>
