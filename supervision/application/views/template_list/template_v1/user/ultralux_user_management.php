<?php if (form_visible_operation()) {
	$tab1 = " active ";
	$tab2 = "";
} else {
	$tab2 = " active ";
	$tab1 = "";
}
$_datepicker = array(array('created_datetime_from', PAST_DATE), array('created_datetime_to', PAST_DATE));
$this->current_page->set_datepicker($_datepicker);
if (is_array($search_params)) {
	extract($search_params);
}
?>
<!-- HTML BEGIN -->
<div id="general_user" class="bodyContent">
<div class="panel panel-default"><!-- PANEL WRAP START -->
<div class="panel-heading"><!-- PANEL HEAD START -->
<div class="panel-title">
<?php
if (intval(@$eid) > 0) {
	$i_fil = '';
	if (@$_GET['user_status']) {
		$i_fil .= 'user_status='.intval($_GET['user_status']);
	}
	$cancel_edit_btn = '<a class="btn btn-sm btn-danger pull-right" href="'.base_url().'index.php/user/b2b_user?'.$i_fil.'"><i class="fa fa-trash"></i> Click here to Cancel Editing</a>';
} else {
	$cancel_edit_btn = '';
}
echo $cancel_edit_btn;
?>
<ul class="nav nav-tabs" role="tablist" id="myTab">
	<!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE START-->
	<li role="presentation" class="<?php echo $tab1; ?>"><a
		id="fromListHead" href="#fromList" aria-controls="home" role="tab"
		data-toggle="tab"> <i class="fa fa-edit"></i> <?php echo get_app_message('AL0014');?>
	</a></li>
	<li role="presentation" class="<?php echo $tab2; ?>"><a
		href="#tableList" aria-controls="profile" role="tab" data-toggle="tab">
	<i class="fa fa-users"></i> <?=(isset($_GET['user_status']) == true ? empty($_GET['user_status']) == true ? 'Inactive' : 'Active' : '')?> Agent List </a>
	</li>
	<!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE END -->
</ul>
</div>
</div>
<!-- PANEL HEAD START -->
<div class="panel-body"><!-- PANEL BODY START -->
<div class="tab-content">
<div role="tabpanel" class="clearfix tab-pane <?php echo $tab1; ?>" id="fromList">
<div class="clearfix">
<?php 
/************************ GENERATE CURRENT PAGE FORM ************************/
$form_data['user_type'] = ULTRALUX_USER;
$city_text = '';
$country_text = '';
$dafaultCity = '';
if (isset($eid) == false || empty($eid) == true) {
	/*** GENERATE ADD PAGE FORM ***/
	$form_data['country_code'] = (isset($form_data['country_code']) == false ? INDIA_CODE : $form_data['country_code']);
	$form_data['country_name'] = $form_data['api_country_list']['api_country_list_fk'];
	//$form_data['city'] = $form_data['api_country_list']['api_city_list_fk'];
	$dafaultCity = $form_data['api_country_list']['api_city_list_fk'];
	//$form_data['country_name'] = (isset($form_data['country_code']) == false ? INDIA : $form_data['country_code']);
	//$form_data['country_name'] = (isset($form_data['country_name']) == false ? INDIA : $form_data['country_name']);
	echo $this->current_page->generate_form('b2b_user', $form_data);
} else {
	//$form_data['country_name'] =  INDIA;
	$city_text = $form_data['city_name'];
	$dafaultCity = $city_text;
	$country_text = $form_data['country_name'];
	
	echo $this->current_page->generate_form('b2b_user_edit', $form_data);
}
/************************ GENERATE UPDATE PAGE FORM ************************/
?></div>
</div>
<div role="tabpanel" class="clearfix tab-pane <?php echo $tab2; ?>"	id="tableList">
<!--/************************ GENERATE Filter Form ************************/-->
<h4>Search Panel</h4>
<button class="btn btn-info" id="clear_search_filters">Clear Search Filter <i class="fa fa-close"></i></button>
<hr>
<form method="GET" autocomplete="off" id="search_filter_form">
	<input type="hidden" name="user_status" value="<?=@$user_status?>" >
	<div class="clearfix form-group">
		<div class="col-xs-4">
			<label>Agency Name</label>
			<input type="text" placeholder="Agency Name" value="<?=@$agency_name?>" name="agency_name" class="search_filter form-control">
		</div>
		<div class="col-xs-4">
			<label>Agency ID</label>
			<input type="text" placeholder="Agency ID" value="<?=@$uuid?>" name="uuid" class="search_filter form-control">
		</div>
		<div class="col-xs-4">
			<label>PAN</label>
			<input type="text" placeholder="PAN" value="<?=@$pan_number?>" name="pan_number" class="search_filter form-control">
		</div>
		<div class="col-xs-4">
			<label>Email</label>
			<input type="text" placeholder="Email" value="<?=@$email?>" name="email" class="search_filter form-control">
		</div>
		<div class="col-xs-4">
			<label>Phone</label>
			<input type="text" placeholder="Phone Number" value="<?=@$phone?>" name="phone" class="search_filter numeric form-control">
		</div>
		<div class="col-xs-4">
			<label>Member Since</label>
			<input type="text" placeholder="Registration Date" readonly value="<?=@$created_datetime_from?>" id="created_datetime_from" name="created_datetime_from" class="search_filter form-control">
		</div>
	</div>
	<div class="col-sm-12 well well-sm">
		<button class="btn btn-primary" type="submit">Search</button> 
		<button class="btn btn-warning" type="reset">Reset</button>
	</div>
</form>
<div class="clearfix"></div>
<!--/************************ GENERATE Filter Form ************************/-->
<div class="clearfix">
<?php
/************************ GENERATE CURRENT PAGE TABLE ************************/
echo get_table(@$table_data, $total_rows);
/************************ GENERATE CURRENT PAGE TABLE ************************/
?>
</div>
</div>
</div>
</div>
<!-- PANEL BODY END --></div>
<!-- PANEL WRAP END --></div>
<!-- HTML END -->
<?php
function get_table($table_data='', $total_rows=0)
{
	$table = '';
	$pagination = '<div>'. $GLOBALS['CI']->pagination->create_links().'<span class="">Total '.$total_rows.' agents</span></div>';
	$table .= $pagination;
	$search_filter = '<div class="">
					<form method="GET" role="search" class="navbar-form" id="filter_agency_form">
					<input type="hidden" name="user_status" value="'.$_GET['user_status'].'" >
					<div class="form-group">
					<input type="hidden" name="filter" value="search_agent">
					<input type="text" autocomplete="off" placeholder="Search" class="form-control ui-autocomplete-input" id="filter_agency" name="filter_agency" value="'.$_GET['filter_agency'].'">
					</div>
					<button title="Search:Agency,Email,Mobile,ID" class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
					</form>';
	$table .= $search_filter;
	$table .= '
   <div class="clearfix">
   <div class="col-md-12 table-responsive " >
   <table class="table table-condensed table-bordered">';
	$table .= '<thead><tr>
   <th><i class="fa fa-sort-numeric-asc"></i> '.get_app_message('AL006').'</th>
   <th>Agency Name</th>
   <th>Agency ID</th>
   <th>Agent Name</th>
    <th>Country</th>
   <th>City</th>
   <th>Balance</th>
   <th>Credit Limit</th>
   <th>Due Amount</th>
   <th>Mobile</th>
   <th>Email</th>
   <th><abbr title="Pending Deposit Request">Deposit Req</abbr></th>';
	if (is_active_airline_module()) {
		$table .= '<th>Flight</th>';
	}
	if (is_active_hotel_module()) {
		$table .= '<th>Hotel</th>';
	}
	if (is_active_car_module()) {
		$table .= '<th>Car</th>';
	}
	if (is_active_transferv1_module()) {
		$table .= '<th>Transfers</th>';
	}
	if (is_active_sightseeing_module()) {
		$table .= '<th>Sightseeing</th>';
	}

   $table .= '
   
   <th>Status</th>
   <th>CreatedOn</th>
   <th>Action</th>
   </tr></thead><tbody>';
   // debug($table_data);exit;
	if (valid_array($table_data) == true) {
		$segment_3 = $GLOBALS['CI']->uri->segment(3);
		$current_record = (empty($segment_3) ? 0 : $segment_3);
		$rep_url = base_url().'index.php/report/';
		//$dep_url = base_url().'index.php/management/b2b_balance_manager';
		$dep_url = base_url().'index.php/management/ultra_balance_manager';
	
		foreach ($table_data as  $v) {

			/*$last_login = 'Last Login : '.last_login($v['last_login']);
			$login_status = login_status($v['logout_date_time']);*/
			$dep_req = '';
			if (isset($v['dep_req']) == true && isset($v['dep_req']['pending']) == true) {
				$dep_req = intval($v['dep_req']['pending']['count']);
			} else {
				$dep_req = 0;
			}
			
			$booking_summ = '';

			if (is_active_airline_module()) {
				$booking_summ .= '<td>'.intval($v['booking_summ']['flight']['BOOKING_CONFIRMED']['count']).' <a target="_blank" href="'.$rep_url.'ultra_flight_report?created_by_id='.$v['user_id'].'">view</a></td>';
			}
			
			if (is_active_hotel_module()) {
				$booking_summ .= '<td>'.intval($v['booking_summ']['hotel']['BOOKING_CONFIRMED']['count']).'  <a target="_blank" href="'.$rep_url.'ultra_hotel_report?created_by_id='.$v['user_id'].'">view</a></td>';
			}
			
			if (is_active_car_module()) {
				$booking_summ .= '<td>'.intval($v['booking_summ']['bus']['BOOKING_CONFIRMED']['count']).'  <a target="_blank" href="'.$rep_url.'ultra_car_report?created_by_id='.$v['user_id'].'">view</a></td>';
			}
			if (is_active_transferv1_module()) {
				$booking_summ .= '<td>'.intval($v['booking_summ']['transfer']['BOOKING_CONFIRMED']['count']).'  <a target="_blank" href="'.$rep_url.'b2b_transfers_report?created_by_id='.$v['user_id'].'">view</a></td>';
			}
			if (is_active_sightseeing_module()) {
				$booking_summ .= '<td>'.intval($v['booking_summ']['sightseeing']['BOOKING_CONFIRMED']['count']).'  <a target="_blank" href="'.$rep_url.'b2b_activities_report?created_by_id='.$v['user_id'].'">view</a></td>';
			}
			$action_tab = '';
			$action_tab .= get_edit_button($v['user_id']);
			if($v['status'] == ACTIVE) {
				$action_tab .= '<br />'.send_password($v['user_id'], $v['uuid']);
			}
			$action_tab .= '<br />'.delete_agent_button($v['user_id'], $v['uuid']);
			$action_tab .='<br />'.view_account_ledger($v['user_id'],$v['created_datetime']);
			$action_tab .='<br />'.update_credit_limit($v['user_id']);
			//Booking
			$table .= '<tr>
			<td>'.(++$current_record).'</td>
			<td>'.(empty($v['agency_name']) == false ? $v['agency_name'] : 'Not Added' ).'</td>
			<td>'.provab_decrypt($v['uuid']).'</td>
			<td>'.get_enum_list('title', $v['title']).' '.$v['first_name'].' '.$v['last_name'].'</td>
			<td>'.$v['country_name'].'</td>
			<td>'.$v['city_name'].'</td>
			<td>'.roundoff_number($v['agent_balance']).'</td>
			<td>'.roundoff_number($v['credit_limit']).'</td>
			<td>'.roundoff_number($v['due_amount']).'</td>
			<td>'.$v['phone'].'</td>
			<td>'.provab_decrypt($v['email']).'</td>
			<td>'.$dep_req.' <a target="_blank" href="'.$dep_url.'?uuid='.$v['uuid'].'">view</a></td>
			'.$booking_summ.'
			<td>'.get_status_toggle_button($v['status'], $v['user_id'], $v['uuid']).'</td>
			<td>'.app_friendly_absolute_date($v['created_datetime']).'</td>
			<td>'.$action_tab.'</td>
</tr>';
		}
	} else {
		$table .= '<tr><td colspan="8">'.get_app_message('AL005').'</td></tr>';
	}
	$table .= '</tbody></table></div></div>';
	return $table;
}

function get_status_label($status)
{
	if (intval($status) == ACTIVE) {
		return '<span class="label label-success"><i class="fa fa-circle-o"></i> '.get_enum_list('status', ACTIVE).'</span>
	<a role="button" href="" class="hide">'.get_app_message('AL0021').'</a>';
	} else {
		return '<span class="label label-danger"><i class="fa fa-circle-o"></i> '.get_enum_list('status', INACTIVE).'</span>
		<a role="button" href="" class="hide">'.get_app_message('AL0020').'</a>';
	}
}

function get_status_toggle_button($status, $user_id, $uuid)
{
	$status_options = get_enum_list('status');
	return '<select autocomplete="off" class="toggle-user-status" data-user-id="'.$user_id.'" data-uuid="'.$uuid.'">'.generate_options($status_options, array($status)).'</select>';
	/*if (intval($status) == INACTIVE) {
		return '<a role="button" href="'.base_url().'user/activate_account/'.$user_id.'/'.$uuid.'" class="text-success">Activate</a>';
	} else {
		return '<a role="button" href="'.base_url().'user/deactivate_account/'.$user_id.'/'.$uuid.'" class="text-danger">Deactivate</a>';
	}*/
}

function get_edit_button($id)
{
	return '<a role="button" href="'.base_url().'index.php/user/b2b_user?'.$_SERVER['QUERY_STRING'].'&	eid='.$id.'" class="btn btn-sm btn-primary">
				<i class="fa fa-edit"></i> ViewProfile
			</a>
		';
}
function send_password($user_id, $uuid)
{
	return '<a role="button" href="#" class="btn btn-sm btn-info send_agent_new_password" data-user-id="'.$user_id.'" data-uuid="'.$uuid.'">
				<i class="fa fa-send"></i> SendPassword
			</a>
		';
}
function delete_agent_button($user_id, $uuid)
{
	return '<a role="button" href="#" class="btn btn-sm btn-danger delete_agent" data-user-id="'.$user_id.'" data-uuid="'.$uuid.'">
				<i class="fa fa-trash-o"></i> DeleteAgent
			</a>
		';
}
function view_account_ledger($user_id,$date = ''){
	
	return '<a role="button" href="'.base_url().'index.php/management/ultra_account_ledger?agent_id='.$user_id.'" target="_blank" class="btn btn-sm btn-success" >
				<i class="fa fa-bar-chart"></i> Account Ledger
			</a>
		';	  
}
function update_credit_limit($user_id){
	return '<a role="button" href="'.base_url().'index.php/management/ultra_credit_balance_show?agent_id='.$user_id.'" target="_blank" class="btn btn-sm btn-default" >
				<i class="fa fa-bar-chart"></i>Credit Limit
			</a>
		';	
}

?>
<script>
$(document).ready(function() {
	
	 //set dropdownlist selected
	var objSelect =document.getElementById("country_name");	
	var citySelect = document.getElementById("city");

	var country_edit_text = "<?= $country_text; ?>";
	var city_edit_text = "<?= $city_text; ?>";
	var default_city = "<?= $dafaultCity; ?>";

	if(country_edit_text !=''){

		setSelectedValue(objSelect,country_edit_text);
		
	}
	//set country dropdown list selected
	function setSelectedValue(selectObj, textToSet) {
	    for (var i = 0; i < selectObj.options.length; i++) {	    	
	        if (selectObj.options[i].text.toLowerCase() == textToSet.toLowerCase()) {	

	            selectObj.options[i].selected = true;
	            
	            return;
	        }
	    }
	}
	
	get_city_lists();
	//Enter only numbers
	$("#phone").on("keypress", function(evt){
  
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    });	
	//Enter only numbers and letters
	$("#pan_number").on("keypress", function(event){		
        var ew = event.which;
      
        if ((ew == 0 || ew == 8 )||(ew >= 48 && ew <= 57) || (ew >= 65 && ew <= 90) || (ew >= 97 && ew <= 122 ) ) {
           
            return true;
        }
        return false;
    });
    
    //Enter only letters 
     $("#first_name,#last_name").on("keypress",function(event){
         var inputValue = event.which;
       
        if(!(inputValue >= 65 && inputValue <= 122) && (inputValue != 32 && inputValue != 0 && inputValue != 8)) { 
            event.preventDefault(); 
        }
    });
    
	//Reset the Search Filters
	$('#clear_search_filters').click(function(){
		$('.search_filter', "form#search_filter_form").val('');
		$("form#search_filter_form").submit();
	});
	//Active/Deactive Agent
	$('.toggle-user-status').on('change', function(e) {
		e.preventDefault();
		var _user_status = this.value;
		var _opp_url = app_base_url+'index.php/user/';
		if (parseInt(_user_status) == 1) {
			_opp_url = _opp_url+'activate_account/';
		} else {
			_opp_url = _opp_url+'deactivate_account/';
		}
		_opp_url = _opp_url+$(this).data('user-id')+'/'+$(this).data('uuid');
		toastr.info('Please Wait!!!');
		$.get(_opp_url, function() {
			toastr.info('Updated Successfully!!!');
		});
	});
	//Send Agent Password
	$('.send_agent_new_password').on('click', function(e) {
		e.preventDefault();
		var _opp_url = app_base_url+'index.php/user/send_agent_new_password/';
		_opp_url = _opp_url+$(this).data('user-id')+'/'+$(this).data('uuid');
		toastr.info('Please Wait!!!');
		$.get(_opp_url, function() {
			toastr.info('Updated Successfully!!!');
		});
	});
	//Delete Agent
	$('.delete_agent').on('click', function(e) {
		e.preventDefault();
		var _opp_url = app_base_url+'index.php/user/delete_agent/';
		_opp_url = _opp_url+$(this).data('user-id')+'/'+$(this).data('uuid');
		toastr.info('Please Wait!!!');
		$.get(_opp_url, function() {
			toastr.info('Updated Successfully!!!');
		});
	});
	//Fiter Agent
	var cache = {};
	$('#filter_agency', 'form#filter_agency_form').autocomplete({
		source:  function( request, response ) {
	        var term = request.term;
	        if ( term in cache ) {
	          response( cache[ term ] );
	          return;
	        } else {
	        	$.getJSON( app_base_url+"index.php/ajax/auto_suggest_agency_name", request, function( data, status, xhr ) {
	                cache[ term ] = data;
	                response( cache[ term ] );
	              });
	        }
	      },
	    minLength: 1
	 });
	 
	

	//get city list based on country code
	
	$("#country_name").on("change",function(){
		get_city_lists();
	});

	function get_city_lists()
    {
      var country_id = $("#country_name").val();
    
      if(country_id == '' || country_id == 'INVALIDIP'){
          $("#city").empty().html('<option value = "" selected="">Select City</option>');
         return false;
      } 
      //console.log("country_id"+country_id);
        
      	$.get(app_base_url+'index.php/ajax/get_city_lists',{country_id : country_id},function( data ) {
      		
	         $("#city").empty().html(data);
	        //console.log("change called,,,");
	       // console.log(city_edit_text.toLowerCase());
	        if(city_edit_text !=''){
	         	 for (var i = 0; i < citySelect.options.length; i++) {
			     	    	
			        if (citySelect.options[i].text.toLowerCase() == city_edit_text.toLowerCase()) {
			      	        	
			            citySelect.options[i].selected = true;
			            return;
			        }
				 }
	         }
	        $("#city").val(default_city);
	        
      	});
    }

});
</script>
