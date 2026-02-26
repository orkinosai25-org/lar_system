<?php 
//debug($insurance[0]['amount']);exit;
?>
<!-- HTML BEGIN -->
<div class="bodyContent">
    <div class="panel panel-default clearfix"><!-- PANEL WRAP START -->
        <div class="panel-heading"><!-- PANEL HEAD START -->
            <div class="panel-title"><i class="fa fa-credit-card"></i>  Convenience Fee Text</div>
        </div>
        <!-- PANEL HEAD START -->
        <div class="panel-body"><!-- PANEL BODY START -->
            <div class="table-responsive" id="checkbox_div">
                <form action="" method="POST" autocomplete="off">

                    <div class="col-sm-12">
                        <label class="col-sm-3 control-label">Convenience Fee Text<span class="text-danger">*</span></label>
                        <div class="col-sm-3">
                            <input type="text" value="<?php echo $text; ?>" name="conv_text">
                             <input type="hidden" value="<?php echo $origin; ?>" name="origin">
                        </div>
                        <div class="clearfix"></div>
                        <br />
                    </div>
                    <div class="col-sm-12">
                        <label class="col-sm-3 control-label">&nbsp;</label>
                        <div class="col-sm-3">
                            <input type="submit" name="submit" class="btn btn-primary btn-sm">
                        </div>
                    </div>

                </form>
            </div>
        </div><!-- PANEL BODY END -->
    </div><!-- PANEL END -->
</div>
