<?php
if ($origin > 0) {
    $tab1 = " active ";
    $tab2 = "";
} else {
    $tab2 = " active ";
    $tab1 = "";
}
?>
<style>
    .master_tabs li a {
        background: #cadeef !important;
        color: #000 !important;
        font-size: 16px;
    }

    .master_tabs li.active a {
        background: #0784b5 !important;
        color: #fff !important;
        font-size: 16px;
    }
</style>
<div id="general_user" class="bodyContent">
    <div class="panel panel-default">
        <!-- PANEL WRAP START -->
        <div class="panel-heading" style="padding: 0px;">
            <!-- PANEL HEAD START -->
            <div class="panel-title">
                <ul class="nav nav-tabs nav-justified master_tabs" role="tablist" id="myTab">
                    <!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE START-->
                    <li role="presentation" class="<?php echo $tab1; ?>"><a id="fromListHead" href="#fromList"
                            aria-controls="home" role="tab" data-toggle="tab"> Create/Update Rooms </a></li>
                    <li role="presentation" class="<?php echo $tab2; ?>"><a href="#tableList" aria-controls="profile"
                            role="tab" data-toggle="tab">  [
                            <?= $stays_data['name'] ?> ] Rooms List
                        </a></li>
                    <!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE END -->
                </ul>
            </div>
        </div>
        <div>
            <a role="button" href="<?php echo base_url() . 'index.php/eco_stays/stays/' . @$stays_origin; ?>"
                class="btn btn-sm btn-primary pull-right">Back to hotel List</a>
        </div>
        <!-- PANEL HEAD START -->
        <div class="panel-body">
            <!-- PANEL BODY START -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane <?php echo $tab1; ?>" id="fromList">
                    <div class="panel-body">
                        <?php
                        /**
                         * ********************** GENERATE CURRENT PAGE FORM ***********************
                         */

                        if ($origin > 0) {
                            echo $this->current_page->generate_form('rooms_edit', $form_data);
                        } else {
                            echo $this->current_page->generate_form('rooms', $form_data);
                        }
                        /**
                         * ********************** GENERATE UPDATE PAGE FORM ***********************
                         */
                        ?>
                    </div>
                </div>
                <div role=" tabpanel" class="tab-pane <?php echo $tab2; ?>" id="tableList">
                    <div class="panel-body">
                        <?php
                        /**
                         * ********************** GENERATE CURRENT PAGE TABLE ***********************
                         */
                        echo get_table(@$data_list);
                        /**
                         * ********************** GENERATE CURRENT PAGE TABLE ***********************
                         */
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- PANEL BODY END -->
    </div>
    <!-- PANEL WRAP END -->
</div>
<?php
function get_table($table_data = '')
{
    // debug($table_data);exit;
    $table = '';
    $table .= '
   <div class="table-responsive">
   <table class="table table-hover table-striped table-bordered table-condensed">';
    $table .= '<thead><tr>  
	<th> Sr No.</th> 
   <th>Name</th> 
   <th>Type</th>
   <th>Board Type</th>
   <th>Maximum Adults</th>
   <th>Maximum Childrens</th>
   <th>Action</th>
   </tr></thead><tbody>';
    if (valid_array($table_data) == true) {
        $current_record = 0;
        foreach ($table_data as $k => $v) {
            $table .= '<tr>
			<td>' . (++$current_record) . '</td>
			<td>' . $v['name'] . '</td>			
			<td>' . $v['type'] . '</td>	
			<td>' . $v['board_type'] . '</td>	
			<td>' . $v['max_adults'] . '</td>	
			<td>' . $v['max_childs'] . '</td>							
			<td>' . get_edit_button($v['stays_origin'], $v['origin']) . '<br>' . get_price_management_button($v['origin']) . '<br>' . get_cancellation_policy_button($v['origin']) . '<br>' . get_availability_calendar($v['origin']) . '<br>' . get_delete_button($v['stays_origin'], $v['origin']) . '</td>
			</tr>';
        }
    }
    $table .= '</tbody></table></div>';
    return $table;
}

function get_edit_button($stays_origin, $id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/rooms/' . $stays_origin . '/' . $id . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>
		' . get_app_message('AL0022') . '</a>';
}

function get_price_management_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/room_price_management/' . $id . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>
    ' . 'Price Management' . '</a>';
}
function get_cancellation_policy_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/room_cancellation_policy/' . $id . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>
    ' . 'Cancellation Policy' . '</a>';
}

function get_delete_button($stays_origin, $id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/delete_rooms/' . $stays_origin . '/' . $id . '" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i>
    ' . 'Delete' . '</a>';
}

function get_availability_calendar($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/room_availability_calendar/' .  $id . '" class="btn btn-sm btn-primary"><i class="fa fa-calendar"></i>
    ' . 'Availability Calender' . '</a>';
}
?>

<script>

    $(document).ready(function () {


    });
</script>