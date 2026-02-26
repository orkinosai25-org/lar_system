<?php
if (isset($login) == false || is_object($login) == false) {
    $login = new Provab_Page_Loader('login');
}
$login_auth_loading_image  = '<div class="text-center loader-image"><img src="'.$GLOBALS['CI']->template->template_images('loader_v3.gif').'" alt="please wait"/></div>';
?>
<link href="<?php echo $GLOBALS['CI']->template->template_css_dir('agent_index.css');?>" rel="stylesheet" defer>
<link href="<?php echo $GLOBALS['CI']->template->template_css_dir('bootstrap-toastr/toastr.min.css');?>" rel="stylesheet" defer>
<script src="<?php echo $GLOBALS['CI']->template->template_js_dir('bootstrap-toastr/toastr.min.js'); ?>"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
 <link href="<?php echo $GLOBALS['CI']->template->template_css_dir('owl.carousel.min.css');?>" rel="stylesheet" defer>
 <script src="<?php echo $GLOBALS['CI']->template->template_js_dir('owl.carousel.min.js'); ?>"></script>

<div class="ult_hom">
  <div class="container">
    <div class="org_row">
      <div class="col-xs-12 text-center utl_in">
        <div class="ult_logo"> <img src="<?php echo $GLOBALS['CI']->template->domain_images($GLOBALS['CI']->template->get_domain_logo()); ?>" alt=""/></div>
        <h1>Create Your Agent Account</h1>
        <p>Partner with us to access exclusive travel deals, manage bookings, and grow your business with us.</p>
        <button type="button" class="btn btn-default get_start">Get started</button>
      </div>
    </div>
  </div>
</div> 


 <div class="topform_main">
 <div class="topform">
 <div class="headagent">
    <div class="container">
        <div class="leftul"> 
            <?php 
            if(!empty($page_content['data'])) { 
                foreach ($page_content['data'] as $k => $v) {
                    if(strtolower(str_replace(' ', '', $v['page_title'])) == 'aboutus'){
            ?>
            <a class="myangr" href="<?php echo base_url () . 'index.php/general/cms/' .$v['page_seo_keyword'] ; ?>" ><?=@$v['page_title']?></a>
            <?php 
                break;
                        } else {
                            continue;
                        }
                    }
                } 
            ?> 
            
        </div>
        <div class="rightsin">
            <a class="myangr" href="<?=base_url().'index.php/user/agentRegister' ?>" >Haven't Registered Yet?</a>
        </div>
    </div>
</div>
 <div class="clearfix"></div>
  <div class="container">
   
    <div class="loginbox">
      <div class="col-sm-7 col-xs-12 nopad">
        <div class="innerfirst">
          <div class="logopart"> <img src="<?php echo $GLOBALS['CI']->template->domain_images($GLOBALS['CI']->template->get_domain_logo()); ?>" alt=""/></div>
          <div class="hmembr fr_mobl">Welcome Agent, Login</div>
          <!-- <div class="lorentt fr_mobl">Login to access exclusive travel deals, manage bookings, and grow your business with us.</div> -->
        </div>
      </div>
      <div class="col-sm-5 col-xs-12 nopad">
      <?php 
      $class ='';
      $otp_class = 'hide';
      $OTP_status = $this->session->userdata('OTP_status');
    
      if(isset($OTP_status) && $OTP_status == 'not verified'){
        $class= 'hide';
        $otp_class = '';
      }
      //echo $this->session->userdata('OTP_status');exit;?>
        <div class="innersecing <?php echo $class; ?>">
          <!-- <div class="signhes">Log in</div> 
          <div class="g_lgn"><button type="button" class="btn btn-default"><i class="fab fa-google"></i> Continue with Google</button></div>
          <div class="lg_or">OR</div>
          <div class="clearfix"></div>-->
          <div class="soc_lgn">
            <ul>
              <li><a href="#"><img src="<?php echo $GLOBALS['CI']->template->template_images('microsoft.svg'); ?>"
                                        alt="microsoft" /></a></li>
              <li><a href="#"><img src="<?php echo $GLOBALS['CI']->template->template_images('apple.svg'); ?>"
                                        alt="apple" /></a></li>
              <li><a href="#"><img src="<?php echo $GLOBALS['CI']->template->template_images('google.svg'); ?>"
                                        alt="google" /></a></li>
            </ul>
          </div>
          <?php $name = 'login' ?>
          <form name="<?=$name?>" autocomplete="off" action="<?php echo base_url(); ?>index.php/general/index" method="POST" enctype="multipart/form-data" id="agentlogin" role="form" class="form-horizontal">
          <?php $FID = $GLOBALS['CI']->encrypt->encode($name); ?>
          <input type="hidden" name="FID" value="<?=$FID?>">
          <div class="inputsing"> <span class="sprite userimg"></span>
            <!-- <label class="formlabel">Email address</label> -->
            <!-- <input type="text" class="mylogbox" placeholder="Username" /> -->
            <input value="" name="email" dt="PROVAB_SOLID_V80" required="" type="email" placeholder="Email address" class="mylogbox login-ip email _guest_validate_field" id="email" data-container="body" data-toggle="popover" data-original-title="" data-placement="bottom" data-trigger="hover focus" data-content="Username Ex: john@bookingsdaily.com">
          </div>
          <div class="inputsing"> <span class="sprite lockimg"></span>
            <!-- <label class="formlabel">Password</label> -->
            <!-- <input type="text" class="mylogbox" placeholder="Password" /> -->
            <input value="" name="password" dt="PROVAB_SOLID_V45" required="" type="Password" placeholder="Password" class="login-ip password mylogbox _guest_validate_field" id="password" data-container="body" data-toggle="popover" data-original-title="" data-placement="bottom" data-trigger="hover focus" data-content="Password Ex: A3#FD*3377^*">
            <span class="lg_hid"><i class="fas fa-eye-slash"></i></span>
          </div>
         
          <!-- <button class="logbtn">Login</button> -->
           <button id="login_submit" class="logbtn">Login</button>
            <div class="signhes frgt"><?php echo $GLOBALS['CI']->template->isolated_view('general/forgot-password');?></div>
            <div id="login_auth_loading_image" style="display: none">
            <?=$login_auth_loading_image?>
          </div>
           <div id="login-status-wrapper" class="alert alert-danger" style="display: none"></div>
          </form>
          <!-- <div class="signhes"> Don’t have an account ? <a href="<?=base_url().'index.php/user/agentRegister' ?>">Sign up</a></div> -->
          
        </div>
         <div class="innersecing <?php echo $otp_class; ?>" id="otp_div">
         <a href="#" class="gobacklink">Back</a> 
            <?php $name = 'otp' ?>
          <form name="<?=$name?>" autocomplete="off" action="" method="POST" enctype="multipart/form-data" id="login" role="form" class="form-horizontal">
            <div class="inputsing">
            <!-- <input type="text" class="mylogbox" placeholder="Password" /> -->
            <input value="" name="opt" required="" type="text" placeholder="Enter OTP" class="login-ip mylogbox _guest_validate_field" id="otp">
          </div>
          <button id="opt_submit" class="logbtn">Login</button>
           <div id="login-otp-wrapper" class="alert alert-danger" style="display: none"></div>
          </form>

         </div>
      </div>
    </div>
  </div>
</div>
</div>

<style type="text/css">
  .invalid-ip {
    border: 1px solid #bf7070!important;
}
.alert-danger{
      background-color: #dd4b39!important;
}
.logbtn.active {
    background: #BF9766;
}
</style>
<script>
$(document).ready(function() {
	var $login = $('.logbtn');

$("input[name='email']").on("keyup", function(){
	
    if($(this).val() != "" && $("input[name='password']").val() != ""){
       $login.toggleClass('active', this.value.length > 0);
    }
	else{
		$login.removeClass('active');
	}
});
	$("input[name='password']").on("keyup", function(){
    if($(this).val() != "" && $("input[name='email']").val() != ""){
        $login.toggleClass('active', this.value.length > 0);
    }
	else{
		$login.removeClass('active');
	}
});
	
	
	
	//var username = $('#email').val();
	
	$('#password').keyup(function(){
		//alert(username);
		//if(username){
   			//$login.toggleClass('active', this.value.length > 0)
		//}
});
  $(".get_start").click(function(){
    $(".ult_hom").addClass("d-none");
    $(".topform_main").addClass("d-block");
  });
$(".logbtn").click(function() {  
    $(this).addClass("active");
  });

  $('#opt_submit').on('click', function(e) {
    
    e.preventDefault();
    var _otp = $('#otp').val();
    if (_otp == '') {
      $('#login-otp-wrapper').text('Please Enter Username And Password To Continue!!!').show();
    } else {
     
      $.post(app_base_url+"index.php/auth/check_otp/", {otp: _otp}, function(response) {
      
        if (response.status) {
          window.location.reload();
        } else {
          $('#login-otp-wrapper').text(response.data).show();
        }
       
      });
    }
  });
  $('.gobacklink').on('click', function(e) {
    var _otp = $('#otp').val();
     $.post(app_base_url+"index.php/auth/back_button/", {otp: _otp}, function(response) {
      
        if (response.status) {
          window.location.reload();
        } else {
          $('#login-otp-wrapper').text(response.data).show();
        }
       
      });
    
  });


$(document).on('click', '.fa-eye-slash', function() {
    $(".lg_hid").html('<i class="fas fa-eye"></i>');
    var input = $("#password");
    
    if (input.attr("type") === "password") { // Changed "Password" to "password"
        input.attr("type", "text");
    } else {
        input.attr("type", "password");
    }
});
$(document).on('click', '.fa-eye', function() {
    $(".lg_hid").html('<i class="fas fa-eye-slash"></i>');
    var input = $("#password");
    
    if (input.attr("type") === "password") { // Changed "Password" to "password"
        input.attr("type", "text");
    } else {
        input.attr("type", "password");
    }
});


});
</script>