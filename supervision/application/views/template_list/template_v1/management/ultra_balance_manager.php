<?php
if (is_array($search_params)) {
	extract($search_params);
}
$_datepicker = array(array('created_datetime_from', PAST_DATE), array('created_datetime_to', PAST_DATE));
$this->current_page->set_datepicker($_datepicker);
$this->current_page->auto_adjust_datepicker(array(array('created_datetime_from', 'created_datetime_to')));
?>
<!-- HTML BEGIN -->
<div id="general_user" class="bodyContent clearfix">
	<div class="panel panel-default clearfix"><!-- PANEL WRAP START -->
		<div class="panel-heading"><!-- PANEL HEAD START -->
			<?php echo $heading; ?>
		</div><!-- PANEL HEAD START -->
		<div class="panel-body">
			<h4>Search Panel</h4>
			<hr>
			<form method="GET" autocomplete="off">
				<div class="clearfix form-group">
					<div class="col-xs-4">
						<label>
						Agency Name
						</label>
						<input type="text" class="form-control" name="agency_name" value="<?=@$agency_name?>" placeholder="Agency Name">
					</div>
					<div class="col-xs-4">
						<label>
						Agency ID
						</label>
						<input type="text" class="form-control" name="uuid" value="<?=@$uuid?>" placeholder="Agency ID">
					</div>
					<div class="col-xs-4">
						<label>
						Transaction Number
						</label>
						<input type="text" class="form-control" name="system_transaction_id" value="<?=@$system_transaction_id?>" placeholder="Transaction Number">
					</div>
					<div class="col-xs-4">
						<label>
						Status
						</label>
						<select class="form-control" name="status">
							<option>All</option>
							<?=generate_options($status_options, array(@$status))?>
						</select>
					</div>
					<div class="col-xs-4">
						<label>
						Request From
						</label>
						<input type="text" readonly id="created_datetime_from" class="form-control" name="created_datetime_from" value="<?=@$created_datetime_from?>" placeholder="Request Date">
					</div>
					<div class="col-xs-4">
						<label>
						Request To
						</label>
						<input type="text" readonly id="created_datetime_to" class="form-control disable-date-auto-update" name="created_datetime_to" value="<?=@$created_datetime_to?>" placeholder="Request Date">
					</div>
				</div>
				<div class="col-sm-12 well well-sm">
				<button type="submit" class="btn btn-primary">Search</button> 
				<button type="reset" class="btn btn-warning">Reset</button>
				<a href="<?php echo base_url(); ?>index.php/management/b2b_balance_manager" id="clear-filter" class="btn btn-primary">ClearFilter</a>
				</div>
			</form>
		</div>
		<?php
			/************************ GENERATE CURRENT PAGE TABLE ************************/
			echo get_table($table_data);
			/************************ GENERATE CURRENT PAGE TABLE ************************/
		?>
	</div><!-- PANEL WRAP END -->
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<form autocomplete="off" method="POST" action="">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><i class="fa fa-lock"></i> Please Verify Before You Process This Transaction Request</h4>
			</div>
			<div class="modal-body clearfix">
				<div class="alert alert-info text-bold">
					<span>Domain : <span id="request-domain-name"></span></span><br>
					<span>Amount : <span id="request-default-amount"></span></span> <?php echo COURSE_LIST_DEFAULT_CURRENCY_VALUE?><br>
					<span>Processed By : <?php echo $this->entity_name?></span>
				</div>
				
					<input type="hidden"	value=""	name="request_origin"		id="request-origin">
					<input type="hidden"	value=""	name="request_user_email"	id="request_user_email">
					<input type="hidden"	value=""	name="system_request_id"	id="system-request-id">
					<input type="hidden"	value=""	name="request_user"     	id="request_user">
					<div class="form-group">
						<label for="update_remarks" class="col-sm-4 control-label">Status</label>
						<div class="col-sm-8">
							<select id="status-id" name="status_id" class="form-control" autocomplete="off">
							<?php echo generate_options($provab_balance_status);?>
						</select>
						</div>
					</div>
					<div class="form-group">
						<label for="update-remarks" class="col-sm-4 control-label">Remarks</label>
						<div class="col-sm-8">
							<textarea class="update_remarks form-control" id="update-remarks" name="update_remarks" data-original-title="" title=""></textarea>
						</div>
					</div>
			</div>
			<div class="modal-footer clearfix">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary">Save changes</button>
			</div>
		</form>
		</div>
	</div>
</div>


<script>
$(document).ready(function() {
	$(document).on('click', '.request-process-btn', function(e) {
		e.preventDefault();
		var _current_status			= $(this).data('status-id');
		var _request_origin			= $(this).data('request-origin');
		var _system_request_id		= $(this).data('system-request-id');
		var _request_domain_name	= $(this).data('request-domain-name');
		var _request_default_amount	= $(this).data('request-default-amount');
		var _request_user_email	= $(this).data('request-user_email');
		var _request_user	= $(this).data('request-user');
		
		update_process_request_details(_current_status, _request_origin, _system_request_id, _request_domain_name, _request_default_amount,_request_user_email,_request_user);
	});
	function update_process_request_details(status, request_origin, system_request_id, request_domain_name, request_default_amount,request_user_email,request_user)
	{
		$('#status-id').val(status);
		$('#request-origin').val(request_origin);
		$('#system-request-id').val(system_request_id);
		$('#request-domain-name').text(request_domain_name);
		$('#request-default-amount').text(request_default_amount);
		$('#request_user_email').val(request_user_email);
		$('#request_user').val(request_user);
	}
});
</script>

<!-- HTML END -->
<?php 
function get_table($table_data='')
{
	$table = '
   <div class="table-responsive col-md-12">
   <table class="table table-hover table-striped table-bordered table-condensed">';
      $table .= '<thead><tr>
   <th>Sno</th>
   <th>System Transaction</th>
   <th>Request From</th>
   <th>Mode Of Payment</th>
   <th>Amount</th>
   <th>Status</th>
   <th>Request Sent On</th>
   <th>User Remarks</th>
   <th>Update Remarks</th>
   <th>Action</th>
   </tr></thead><tbody>';
	if (valid_array($table_data) == true) {
		foreach ($table_data as $k => $v) {
			//echo debug($v);exit;
			$current_request_status = strtoupper($v['status']);
			$default_currency_value = $v['amount'];
			$action = '';
			$action .= request_process_button($current_request_status, $v['origin'], $v['system_transaction_id'], $default_currency_value, $v['requested_from'],$v['email'],$v['request_user']);
			
			$current_request_status = strtoupper($v['status']);
			$table .= '<tr>
			<td>'.($k+1).'</td>
			<td>'.$v['system_transaction_id'].'</td>
			<td>'.$v['requested_from'].'</td>
			<td>'.get_enum_list('provab_balance_requests', strtoupper($v['transaction_type'])).'</td>
			<td>'.$v['amount'].'</td>
			<td><span class="label '.balance_status_label($current_request_status).'">'.$current_request_status.'</span></td>
			<td>'.app_friendly_absolute_date($v['created_datetime']).'</td>
			<td><abbr title="'.($v['remarks'] == '' ? '--' : $v['remarks']).'">'.$v['request_user'].'\'s Remarks</abbr></td>
			<td>'.$v['update_remarks'].'<br>'.app_friendly_absolute_date($v['updated_datetime']).'</td>
			<td>'.$action.'</td>
	</tr>';
		}
	} else {
		$table .= '<tr><td colspan="3">'.get_app_message('AL005').'</td></tr>';
	}
	$table .= '</tbody></table></div>';
	return $table;
}

function request_process_button($status, $request_origin, $system_request_id, $default_currency_value, $agency_name,$email,$requested_user)
{
	$action = '';
	if ($status == 'PENDING') {
		$action = '<button data-toggle="modal"   data-request-user="'.$requested_user.'" data-request-user_email="'.$email.'" data-request-domain-name="'.$agency_name.' Travels" data-request-default-amount="'.$default_currency_value.'" data-target="#myModal" class="request-process-btn btn btn-success btn-sm" data-system-request-id="'.$system_request_id.'" data-request-origin="'.$request_origin.'" data-status-id="'.$status.'">Process</button>';
	} 
	return $action;
}
?>
