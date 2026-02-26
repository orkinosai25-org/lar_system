<?php
if(empty(validation_errors()) == false) {
  $view_tab = '';
  $edit_tab = ' active ';
} else {
  $view_tab = ' active ';
  $edit_tab = '';
}
if(empty(validation_errors()) == false){
  $message = 'hide';
}
//$message = 'hide';//Remove it in Soorya Travel


?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<style type="text/css">
  .pop-info{
    font-family: monospace;
    font-size: 15px;
    margin-left: 19%;
    opacity: 0.9;
    position: fixed;
    z-index: 1000;
  }

</style>


<!-- <div class="newaddtab"></div> -->
<div class="background_login">    
    <div class="loadcity"></div>    
    <div class="clodnsun"></div>        
        <div class="reltivefligtgo">        
          <div class="flitfly"></div>       
         </div>  
        <div class="clearfix"></div>   
       <div class="busrunning">       
            <div class="runbus"></div>           
             <div class="runbus2"></div>        
                 <div class="roadd"></div>       
         </div>   
  </div>
<div class="b2b_agent_profile agent_regpage agentmyn">
<div class="container">
  <?php if(!empty($this->session->flashdata('message'))) {?>
  <div class="alert alert-success"> <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> <strong><?php echo $this->session->flashdata('message'); ?></strong> </div>
  <?php } ?>
  <div class="tab-content sidewise_tab">
    <div data-role="tabpanel" class="tab-pane active clearfix" id="profile">
      <div class="agent_regtr">
        <a href="<?php echo base_url();?>" class="gobacklink"><i class="far fa-arrow-left"></i> Back</a> 
        
        <div class="agentreg_heading"> AGENT REGISTRATION
        
        
        </div>
        
        <div class="clearfix"></div>
        <!-- Edit User Profile starts-->
        <div class="tab-content">
          <div data-role="tabpanel filldiv" class="tab-pane active" id="show_user_profile">
            <form action="<?=base_url().'index.php/user/agentRegister'; ?>" method="post" name="edit_user_form" id="register_user_form" enctype="multipart/form-data" autocomplete="off">
              <div class="each_sections">
                <div class="sec_heading"><strong>1</strong>Personal Info</div>
                <div class="inside_regwrp">
                  <div class="col-sm-8 nopad">
                    <div class="col-sm-12 nopad">
                      <div class="wrap_space">
                        <div class="label_form">First Name <span class="text-danger">*</span></div>
                        <div class="col-xs-3 nopad">
                          <div class="select_wrap">
                            <select name="title" class="select_form noborderit smaltext" required>
                              <?=
                                               
                                          generate_options(get_enum_list('title'), (array)@$title)?>
                            </select>
                            
                          </div>
                        </div>
                        <div class="col-xs-9 nopad">
                          <div class="div_wrap">
                            <input type="text" name="first_name"  placeholder="First name" value="<?php echo set_value('first_name'); ?>" class="input_form alpha_space _guest_validate_field" required />
                          </div>
                        </div>
                         <?php if(!empty(form_error('first_name'))) { ?>
                        <div class="agent_error"><?php echo form_error('first_name');?></div>
                        <?php } ?>
                      </div>
                    </div>
                    <!-- <span>This Field is mandatory</span> -->
                    <div class="col-sm-12 nopad">
                      <div class="wrap_space">
                        <div class="label_form">Last Name <span class="text-danger">*</span></div>
                        <div class="div_wrap">
                          <input type="text" name="last_name" placeholder="last name" value="<?php echo set_value('last_name'); ?>" class="input_form alpha_space _guest_validate_field" required="required"/>
                          
                        </div>
                        <?php if(!empty(form_error('last_name'))) { ?>
                        <div class="agent_error"><?php echo form_error('last_name');?></div>
                        <?php }?>
                      </div>
                    </div>
                    <div class="col-sm-12 nopad">
                      <div class="wrap_space">
                        <div class="label_form">Mobile Number <span class="text-danger">*</span></div>
                        <div class="col-xs-6 nopad">
                          <div class="select_wrap">
                            <select name="country_code" class="select_form noborderit smaltext" required>
                              <option value="" disabled selected>Select Country Code</option>
                              <?=generate_options($phone_code_array, (array)@$country_code)?>
                         
                            </select>
                          </div>
                        </div>
                        <div class="col-xs-6 nopad">
                          <div class="div_wrap">
                            <input type="text" name="phone" placeholder="mobile number" value="<?php echo set_value('phone'); ?>" class="input_form numeric _guest_validate_field"  required="required" maxlength="10">
                            
                          </div>
                        </div>
                       
                          <?php if(!empty(form_error('phone'))) { ?>
                        <div class="agent_error"><?php echo form_error('phone');?></div>
                        <?php }?>
                      </div>
                    </div>
                    <div class="col-sm-12 nopad">
                      <div class="wrap_space">
                        <div class="label_form">Email <span class="text-danger">*</span></div>
                        <div class="div_wrap">
                          <input type="email" id="user_email" name="email" maxlength="80" placeholder="email" value="<?php echo set_value('email'); ?>" class="input_form email" required="required"/>
                          
                        </div>
                        
                          <?php if(!empty(form_error('email'))) { ?>
                          <div class="agent_error"><?php echo form_error('email');?></div>
                       <?php } ?>
                      </div>
                    </div>
                  </div>
                  
                  <!--<div class="col-sm-4 nopad">
                    <div class="tnlepasport_b2b upload_wrap wrap_space">
                      <div class="label_form">Profile Image</div>
                      <div class="uplod_image"  style="background-image:url(<?=$GLOBALS['CI']->template->template_images('agent_demo.png')?>)">
                        <input type="file" id="profile_img_id" name="image" accept="image/*" class="hideupload" />
                        
                      </div>
                      
                    </div>
                  </div>-->

                </div>
              </div>
              <div class="clearfix"></div>
              
              <div class="each_sections">
                <div class="sec_heading"><strong>2</strong>Company Details</div>
                <div class="inside_regwrp">
                  
                    <div class="col-sm-6 nopad">
                      <div class="wrap_space">
                        <div class="label_form">Company Name <span class="text-danger">*</span></div>
                        <div class="div_wrap">
                          <input type="text" name="company_name" placeholder="Company name" value="<?php echo set_value('company_name'); ?>" class="input_form _guest_validate_field alpha_space" maxlength="45" required="required" />
                          
                        </div>
                         <?php if(!empty(form_error('company_name'))) { ?>
                        <div class="agent_error"><?php echo form_error('company_name');?></div>
                        <?php } ?>
                      </div>
                    </div>

                    <?php //if($active_data['api_country_list_fk'] == 92) { ?>
                  
                    <?php //} ?>
                    
                    <div class="col-sm-6 nopad">
                      <div class="wrap_space">
                        <div class="label_form">Address <span class="text-danger">*</span></div>
                        <div class="div_wrap">
                          <textarea class="input_textarea _guest_validate_field" name="address" placeholder="Address" required><?php echo set_value('address'); ?></textarea>
                          
                        </div>
                         <?php if(!empty(form_error('address'))) { ?>
                        <div class="agent_error"><?php echo form_error('address');?></div>
                        <?php } ?>
                      </div>
                    </div>
                    <div class="col-sm-6 nopad">
                      <div class="wrap_space">
                        <div class="label_form">Postal Code <span class="text-danger">*</span></div>
                        <div class="div_wrap">
                          <input type="text" name="pin_code" placeholder="Pin"  value="<?php echo set_value('pin_code'); ?>" class="input_form _guest_validate_field numeric" maxlength="10" required />
                          
                        </div>
                       <?php if(!empty(form_error('pin_code'))) { ?>
                        <div class="agent_error"><?php echo form_error('pin_code');?></div>
                        <?php } ?>
                      </div>
                    </div>  
                    <div class="col-sm-6 nopad">
                      <div class="wrap_space">
                        <div class="label_form">City <span class="text-danger">*</span></div>
                        <div class="select_wrap">
                            <select name="city"  id="city_id" class="select_form" required>
                              <option value = '' selected="">Select City</option>
                            </select>
                            
                          </div>
                          <?php if(!empty(form_error('city'))) { ?>
                        <div class="agent_error"><?php echo form_error('city');?></div>
                        <?php } ?>
                      </div>
                    </div>
                      <div id="pan_data" style="display: none">
                    <div class="col-sm-6 nopad">
                      <div class="wrap_space">
                        <div class="label_form">Pan No</div>
                        <div class="div_wrap">
                          <input type="text" name="pan_number" placeholder="Pan No" value="<?php echo set_value('pan_number'); ?>" id="pan_number" class="input_form" maxlength="10"/>
                          
                        </div>
                         <?php if(!empty(form_error('pan_number'))) { ?>
                        <div class="agent_error"><?php echo form_error('pan_number');?></div>
                        <?php } ?>
                      </div>
                    </div>
                    
                    
                    <div class="col-sm-6 nopad">
                      <div class="wrap_space">
                        <div class="label_form">Pan Card Holder Name</div>
                        <div class="div_wrap">
                          <input type="text" name="pan_holdername" placeholder="Pan Holder Name"  value="<?php echo set_value('pan_holdername'); ?>" class="input_form alpha_space" maxlength="45" />
                          
                        </div>
                       <?php if(!empty(form_error('pan_holdername'))) { ?>
                        <div class="agent_error"><?php echo form_error('pan_holdername');?></div>
                        <?php } ?>
                      </div>
                    </div>
                    </div>   
                    <div class="col-sm-6 nopad">
                      <div class="wrap_space">
                        <div class="label_form">Country <span class="text-danger">*</span></div>
                          <div class="select_wrap">
                              <?php 
                              if(empty(set_value('country')) == false) {
                                $default_country = set_value('country');
                              } else {
                                $default_country = $active_data['api_country_list_fk'];
                              }
                              if(empty(set_value('city')) == false) {
                                $default_city = set_value('city');
                              } else {
                                $default_city = $active_data['api_city_list_fk'];
                              }
                              ?>
                            <select name="country" id="country_id" class="select_form" required>
                              <option value="" disabled="" selected="">Select Country</option>
                              <?=generate_options($country_list, array($default_country));?>
                            </select>
                          </div>
                         <?php if(!empty(form_error('country'))) { ?>
                        <div class="agent_error"><?php echo form_error('country');?></div>
                        <?php } ?>
                      </div>
                    </div>                 
                    <div class="col-sm-6 nopad">
                      <div class="wrap_space">
                        <div class="label_form">Office Phone <span class="text-danger">*</span></div>
                        <div class="div_wrap">
                          <input type="text" name="office_phone" placeholder="Office Number" value="<?php echo set_value('office_phone'); ?>" class="input_form numeric _guest_validate_field"  required="required" maxlength="15">
                          
                        </div>
                        <?php if(!empty(form_error('office_phone'))) { ?>
                        <div class="agent_error"><?php echo form_error('office_phone');?></div>
                        <?php } ?>
                      </div>
                    </div>
                    
                    
                    
                </div>
              </div>
        
              
              
              
              
              <div class="clearfix"></div>
              
              <div class="each_sections">
                <div class="sec_heading"> <strong>3</strong>Login Info</div>
                <div class="inside_regwrp">
                  <div class="col-sm-6 nopad">
                      <div class="wrap_space">
                        <div class="label_form">User Name <span class="text-danger">*</span></div>
                        <div class="div_wrap">
                          <input type="email" id="user_name" name="user_name" placeholder="email" maxlength="80" value="<?php echo set_value('email'); ?>" class="input_form email" required="required"/>
                        </div>
                        <?php if(!empty(form_error('user_name'))) { ?>
                        <div class="agent_error"><?php echo form_error('user_name');?></div>
                        <?php } ?>
                      </div>
                    </div>
                    
                    <div class="clearfix"></div>
                    
                    <div class="col-sm-6 nopad">
                      <div class="wrap_space">
                        <div class="label_form">Password <span class="text-danger">*</span></div>
                        <div class="div_wrap">
                          <input type="password" name="password" placeholder="password" value="<?php echo set_value('password'); ?>" class="input_form pass _guest_validate_field" required="required"/>
                        </div>
                        <?php if(!empty(form_error('password'))) { ?>
                        <div class="agent_error"><?php echo form_error('password');?></div>
                        <?php } ?>
                      </div>
                    </div>
                    
                    <div class="col-sm-6 nopad">
                      <div class="wrap_space">
                        <div class="label_form">Confirm Password <span class="text-danger">*</span></div>
                        <div class="div_wrap">
                          <input type="password" name="password_c" placeholder="retype password" value="<?php echo set_value('password_c'); ?>" class="input_form pass _guest_validate_field" required="required"/>
                        </div>
                          <?php if(!empty(form_error('password_c'))) { ?>
                        <div class="agent_error"><?php echo form_error('password_c');?></div>
                        <?php } ?>
                      </div>
                    </div>
                    
                </div>
              </div>
              
              
              
              <div class="clearfix"></div>
              
              
              <div class="submitsection">
              
                <div class="acceptrms">
                  <div class="squaredThree">
                      <input type="checkbox" value="<?php echo set_value('term_condition'); ?>" required="" name="term_condition" class="airlinecheckbox validate_user_register" id="term_condition" >
                      <label for="term_condition"></label>
                    </div>
                    <label for="term_condition" class="lbllbl">I accept the <a target="_balnk" href="<?=base_url()?>index.php/general/cms/terms&conditions/8">agency Terms and Conditions</a></label>
                </div>
                <div class="clearfix"></div>
                <button type="submit" class="btnreg_agent">Register</button>
              </div>
              
              
              

              
            </form>
          </div>
          
          
          
          <div data-role="tabpanel" class="tab-pane clearfix" id="edit_user_profile"> </div>
        </div>
        <!-- Edit User Profile Ends--> 
        
      </div>
    </div>
  </div>
</div>
<?php
$datepicker = array(array('date_of_birth', PAST_DATE));
$GLOBALS['CI']->current_page->set_datepicker($datepicker);
?>
<script type="text/javascript">
var default_city = '<?=$default_city;?>';
  $(document).ready(function(){
    
    get_city_list();
    //get the state
    $('#country_id').on('change', function(){
      country_origion = $(this).val();
      if(country_origion == '92'){
        $("#pan_data").css("display", "block");
        $("#pan_number").addClass('_guest_validate_field');
      }
      else{
        $("#pan_data").css("display", "none");
        $("#pan_number").removeClass('_guest_validate_field');
      }
      get_city_list();
    });
    function get_city_list(country_id)
    {
      var country_id = $('#country_id').val();
      if(country_id == ''){
          $("#city_id").empty().html('<option value = "" selected="">Select City</option>');
         return false;
         } 
      $.post(app_base_url+'index.php/ajax/get_city_lists',{  country_id : country_id},function( data ) {
         $("#city_id").empty().html(data);
         $('#city_id').val(default_city)
      });
    }

    //Auto populate the user email to the user name
    $('#user_email').on('blur', function(){
      var user_email = $(this).val().trim();
      if(user_email !='') {
        $('#user_name').val(user_email);
      }
    });

      $(".btnreg_agent").on('click',function(){
                  
              var count = 0;
              $('._guest_validate_field').each( function () {
                      if(this.value.trim() == '') {
                             count++;
                           $(this).addClass('invalid-ip').parent().append( 
                 "<span id='name_error'><div class='formerror'style='color:red'>This Field is mandatory</div></span>");
                      }
                    

                     });
                if(count > 0){
                        return false;
                      }
      })


$('._guest_validate_field').focus( function () {
    $(this).removeClass('invalid-ip');
    $(this).parent().find(".formerror").hide();
  });


    $("#term_condition").on('click',function(){
      if($('#term_condition').is(':checked')){
      $('#term_condition').val('1');
          }else{
      $('#term_condition').val('0');
          } 

})

    
    
    });

//image preview
$(function() {
    $("#profile_img_id").on("change", function()
    {
        var files = !!this.files ? this.files : [];
        if (!files.length || !window.FileReader) return; // no file selected, or no FileReader support
 
        if (/^image/.test( files[0].type)){ // only image file
            var reader = new FileReader(); // instance of the FileReader
            reader.readAsDataURL(files[0]); // read the local file
 
            reader.onloadend = function(){ // set image data as background of div
                $(".uplod_image").css("background-image", "url("+this.result+")");
            }
        }else{
          $("#profile_img_id").val('');
        }
    });
});
$('.gobacklink').on('click', function(e) {
	
     $(".ult_hom").addClass("d-none");
	$(".topform_main").addClass("d-block");
	
       
});

</script> 

