<?php
if (isset($login) == false || is_object($login) == false) {
	$login = new Provab_Page_Loader('login');
}
?>
<a class="handCursor " data-toggle="modal" data-target="#myModal" href="#" id="forgot-password">Forgot Password ? </a>
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><i class="fa fa-desktop"></i> Reset Password</h4>
      </div>
      <div class="modal-body">
        <div class="alert ">
        	<p><i class="fa fa-lock"></i> Please Provide Us Your Details To Reset Your Password</p>
        </div>
        <?php echo get_default_image_loader();//data-utility-loader?>
        <?php 
        echo $login->generate_form('forgot_password');
        ?>
        <div id="recover-title-wrapper" class="alert alert-success" style="display:none"><p><i class="fa fa-warning"></i> <span id="recover-title"></span></p></div>
      </div>
      <div class="modal-footer ">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="reset-password-trigger">Reset Password Now</button>
      </div>
    </div>
  </div>
</div>