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
                            aria-controls="home" role="tab" data-toggle="tab"> Create/Update </a></li>
                    <li role="presentation" class="<?php echo $tab2; ?>"><a href="#tableList" aria-controls="profile"
                            role="tab" data-toggle="tab"> Eco Stays Room Meal Types List </a></li>
                    <!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE END -->
                </ul>
            </div>
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
                            echo $this->current_page->generate_form('types_edit', $form_data);

                        } else {
							
							
                            echo $this->current_page->generate_form('types');
                        }
						
						
                        /**
                         * ********************** GENERATE UPDATE PAGE FORM ***********************
                         */
                        ?>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane <?php echo $tab2; ?>" id="tableList">
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
   
   <th>Status</th>
   <th>Action</th>
   </tr></thead><tbody>';
    if (valid_array($table_data) == true) {
        $current_record = 0;
        foreach ($table_data as $k => $v) {
            $table .= '<tr>
			<td>' . (++$current_record) . '</td>
			<td>' . $v['name'] . '</td>			
           
            <td>' . get_enum_list('status', $v['status']) . '</td>									
			<td>' . get_edit_button($v['origin']) . '<br>' . get_delete_button($v['origin']) . '</td>
			</tr>';
        }
    }
    $table .= '</tbody></table></div>';
    return $table;
}

function get_edit_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/eco_stays_room_meal_types/' . $id . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>
		' . get_app_message('AL0022') . '</a>';
}

function get_delete_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/delete_eco_stays_room_meal_types/' . $id . '" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i>Delete</a>';
}
?>

<script>
    $(document).ready(function () {

    });
</script>