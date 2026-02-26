<!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="row">
		<div class="pull-left" style="margin:5px 0">
			<a href="<?=base_url().'index.php/cms/add_hotel_partners'?>">
				<button  class="btn btn-primary btn-sm pull-right amarg">Add Hotel Partners</button>
			</a>
		</div>
	</div>
	<div class="panel <?=PANEL_WRAPPER?>"><!-- PANEL WRAP START -->
		<div class="panel-heading"><!-- PANEL HEAD START -->
			<div class="panel-title">
				<i class="fa fa-edit"></i> Headings
			</div>
		</div><!-- PANEL HEAD START -->
		
		<div class="panel-body">
			<table class="table table-condensed">
				<tr>
					<th>Sl no</th>
					<th>Partner Image</th>
					<th>Status</th>
					<th>Action</th>
				</tr>
				<?php
				// debug($data_list);exit;
				if (valid_array($data_list)) {
				foreach ($data_list as $k => $v) { ?>
					<tr>
						<td><?= $k + 1 ?></td>
						<td>
							<img src="<?= $GLOBALS['CI']->template->domain_hotel_partner_images($v['partner_image']) ?>" class="img-thumbnail" height="100px" width="100px">
						</td>
						<td><?= get_status_toggle_button($v['status'], $v['origin']) ?></td>
						<td>
							<?= get_edit_button($v['origin']) ?>
							<a href="<?= base_url(); ?>index.php/cms/delete_hotel_partner/<?= $v['origin']; ?>"
							   onclick="return confirm('Do you want delete this record');"
							   class="btn btn-danger btn-xs has-tooltip" data-original-title="Delete">
								<i class="icon-remove"></i> Delete
							</a>
						</td>
					</tr>
				<?php } ?>
			<?php return; } ?>

			<tr>
				<td colspan="4">No Data Found</td>
			</tr>

				?>
			</table>
		</div>
	</div><!-- PANEL WRAP END -->
</div>
<?php 
function get_status_label($status): string
{
	if (intval($status) !== ACTIVE) {
		return '';
	}

	return '<span class="label label-success"><i class="fa fa-circle-o"></i> '
		. get_enum_list('status', ACTIVE) . '</span>
		<a role="button" href="" class="hide">'
		. get_app_message('AL0021') . '</a>';
}

function get_status_toggle_button($status, $origin): string
{
	$status_options = get_enum_list('status');
	return '<select class="toggle-user-status" data-origin="' . $origin . '">'
		. generate_options($status_options, [$status]) . '</select>';
}

function get_edit_button($origin): string
{
	$url = base_url() . 'index.php/cms/add_hotel_partners?' . $_SERVER['QUERY_STRING'] . '&origin=' . $origin;
	return '<a role="button" href="' . $url . '" class="btn btn-sm btn-primary">
		<i class="fa fa-edit"></i> ' . get_app_message('AL0022') . '</a>';
}


?>
<script>
$(document).ready(function() {
	$('.toggle-user-status').on('change', function(e) {
		e.preventDefault();
		var _user_status = this.value;
		var _opp_url = app_base_url+'index.php/cms/';
		if (parseInt(_user_status) == 1) {
			_opp_url = _opp_url+'activate_hotel_partners/';
		} else {
			_opp_url = _opp_url+'deactivate_hotel_partners/';
		}
		_opp_url = _opp_url+$(this).data('origin');
		toastr.info('Please Wait!!!');
		$.get(_opp_url, function() {
			toastr.info('Updated Successfully!!!');
		});
	});
});
</script>
