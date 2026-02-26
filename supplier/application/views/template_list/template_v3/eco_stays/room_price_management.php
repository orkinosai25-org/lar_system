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
                            aria-controls="home" role="tab" data-toggle="tab"> Create/Update Room Price</a></li>
                    <li role="presentation" class="<?php echo $tab2; ?>"><a href="#tableList" aria-controls="profile"
                            role="tab" data-toggle="tab"> Room [
                            <?= $room_data['name'] ?>] Prices
                        </a></li>
                    <!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE END -->
                </ul>
            </div>
        </div>
        <div>
            <a role="button"
                href="<?php echo base_url() . 'index.php/eco_stays/rooms/' . $room_data['stays_origin']; ?>"
                class="btn btn-sm btn-primary pull-right">Back to Rooms List</a>
        </div>
        <!-- PANEL HEAD START -->
        <div class="panel-body">
            <!-- PANEL BODY START -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane <?php echo $tab1; ?>" id="fromList">
                    <div class="panel-body">
                        <form name="room_price_management" autocomplete="off" action="" method="POST"
                            enctype="multipart/form-data" id="room_price_management" role="form"
                            class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="penality_value"
                                    form="room_price_management">Season<span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-6">
                                    <select value="" name="season_origin" required="" class=" season form-control"
                                        id="season">
                                        <option value>Select</option>
                                        <?php
                                        echo generate_options(magical_converter(array('k' => 'origin', 'v' => 'name'), array('data' => $seasons)), @$form_data['season_origin']);
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <?php
                            $i = 0;
                            while (++$i <= $room_data['max_adults']) {
                                ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="<?= $i ?>_adult_price"
                                        form="room_price_management">
                                        <?= $i ?> Adult Price<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-6">
                                        <input value="<?= @$form_data[$i . '_adult_price'] ?>" name="<?= $i ?>_adult_price"
                                            required="" type="number" class=" <?= $i ?>_adult_price form-control"
                                            id="<?= $i ?>_adult_price">
                                    </div>
                                </div>
                            <?php } ?>
                            <?php
                            $i = 0;
                            while (++$i <= $room_data['max_childs']) {
                                ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="<?= $i ?>_child_price"
                                        form="room_price_management">
                                        <?= $i ?> Children Price<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-6">
                                        <input value="<?= @$form_data[$i . '_child_price'] ?>" name="<?= $i ?>_child_price"
                                            required="" type="number" class=" <?= $i ?>_child_price form-control"
                                            id="<?= $i ?>_child_price">
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($room_data['extra_bed'] == true) { ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="extra_bed_price"
                                        form="room_price_management">
                                        Extra Bed Price<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-6">
                                        <input value="<?= @$form_data['extra_bed_price'] ?>" name="extra_bed_price"
                                            required="" type="number" class=" extra_bed_price form-control"
                                            id="extra_bed_price">
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="radio">
                                <label class="col-sm-3 control-label" for="status" form="room_price_management">Status
                                    <span class="text-danger">*</span>
                                </label>
                                <label class="radio-inline" for="room_price_managementstatus0">
                                    <input required="" <?= @$form_data['status'] == 0 ? 'checked="checked"' : '' ?> dt=""
                                        class=" status radioIp" type="radio" name="status"
                                        id="room_price_managementstatus0" value="0">Inactive </label>
                                <label class="radio-inline" for="room_price_managementstatus1">
                                    <input required="" <?= @$form_data['status'] == 1 ? 'checked="checked"' : '' ?> dt=""
                                        class=" status radioIp" type="radio" name="status"
                                        id="room_price_managementstatus1" value="1">Active </label>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-8 col-sm-offset-4">
                                    <button type="submit" id="room_price_management_submit"
                                        class=" btn btn-success ">Save</button>
                                    <button type="reset" id="room_price_management_reset"
                                        class=" btn btn-warning ">Reset</button>
                                </div>
                            </div>
                        </form>
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
   <th>Season</th> 
   <th>From</th>
   <th>To</th>
   <th>Prices</th>
   <th>Action</th>
   </tr></thead><tbody>';
    if (valid_array($table_data) == true) {
        $current_record = 0;
        foreach ($table_data as  $v) {
            $table .= '<tr>
			<td>' . (++$current_record) . '</td>
			<td>' . $v['season_name'] . '</td>			
			<td>' . $v['start_date'] . '</td>	
			<td>' . $v['end_date'] . '</td>	
			<td><pre>' . json_encode(json_decode($v['prices'], TRUE), JSON_PRETTY_PRINT) . '</pre></td>								
			<td>' . get_edit_button($v['room_origin'], $v['origin']) . '<br>' . get_delete_button($v['room_origin'], $v['origin']) . '</td>
			</tr>';
        }
    }
    $table .= '</tbody></table></div>';
    return $table;
}

function get_edit_button($room_origin, $id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/room_price_management/' . $room_origin . '/' . $id . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>
		' . get_app_message('AL0022') . '</a>';
}

function get_delete_button($room_origin, $id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/delete_room_price/' . $room_origin . '/' . $id . '" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i>
    ' . 'Delete' . '</a>';
}
?>

<script>

    $(document).ready(function () {


    });
</script>