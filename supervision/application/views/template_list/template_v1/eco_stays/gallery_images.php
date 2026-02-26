<?php
if ($origin > 0) {
    $tab1 = " active ";
    $tab2 = "";
} else {
    $tab2 = " active ";
    $tab1 = "";
}
$base_url = strstr(base_url(), 'supervision', true);   
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
                            aria-controls="home" role="tab" data-toggle="tab"> Upload Image </a></li>
                    <li role="presentation" class="<?php echo $tab2; ?>"><a href="#tableList" aria-controls="profile"
                            role="tab" data-toggle="tab"> Gallery Images </a></li>
                    <!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE END -->
                </ul>
            </div>
        </div>
        <div>
            <a role="button" href="<?php echo base_url() . 'index.php/eco_stays/stays/' ?>"
                class="btn btn-sm btn-primary pull-right">Back to Hotel List</a>
        </div>
        <!-- PANEL HEAD START -->
        <div class="panel-body">
            <!-- PANEL BODY START -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane <?php echo $tab1; ?>" id="fromList">
                    <div class="panel-body">
                        <form name="types" autocomplete="off" action="" method="POST" enctype="multipart/form-data"
                            id="types" role="form" class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="image" form="types">Image <span
                                        class="text-danger">*</span>
                                </label>
                                <div class="col-sm-6">
                                    <input value="" name="image" dt="PROVAB_SOLID_IMAGE_TYPE" required="" type="file"
                                        placeholder="Image" class=" image image" id="image" accept="image/*" data-original-title=""
                                        title="">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-8 col-sm-offset-4">
                                    <button type="submit" id="types_submit" class=" btn btn-success ">Upload</button>
                                </div>
                            </div>
                        </form>
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
   <th>Image</th>
   <th>Action</th>
   </tr></thead><tbody>';
    if (valid_array($table_data) == true) {
        $base_url = strstr(base_url(), 'supervision', true);  
        $current_record = 0;
        foreach ($table_data as $v) {
            $table .= '<tr>
			<td>' . (++$current_record) . '</td>
            <td><img width="20%" src="' . $base_url . $GLOBALS['CI']->template->domain_view_ecoimage() .$v['image']. '"></td>								
			<td>' . get_delete_button($v['stays_origin'], $v['origin']) . '</td>
			</tr>';
        }
    }
    $table .= '</tbody></table></div>';
    return $table;
}
function get_delete_button($stays_origin, $id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/delete_gallery_images/' . $stays_origin . '/' . $id . '" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i>Delete</a>';
}
?>

<script>
    $(document).ready(function () {

    });
</script>

<script>

$( document ).ready(function() {
    $('#types_submit').click(function(){
    var ext = $('#image').val().split('.').pop().toLowerCase();
    if(ext !== 'jpg' && ext !== 'png' && ext !== 'jpeg' && ext !== 'gif') {
    alert('Please select the image files only (gif|jpg|png|jpeg)');
    return false;
}
});
});
</script>
<script type="text/javascript">
	
	$('#types_submit').click(function(){
		$("#file_error").html("");
	$(".demoInputBox").css("border-color","#F0F0F0");
	var file_size = $('#image')[0].files[0].size;
	if(file_size>=1097152) {
	    alert("File size should be  1MB");
	//	$("#file_error").html("File size sholud be 1MB");
	//	$(".demoInputBox").css("border-color","#FF0000");
		return false;
	} 
	return true;
	})
	
</script>