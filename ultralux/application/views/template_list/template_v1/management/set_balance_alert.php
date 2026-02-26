    <style>
      /* Vertical Pills Styling */
      .nav-pills>li>a {
        padding: 10px 20px;
        background-color: #f5f5f5;
        border-radius: 4px;
        margin-bottom: 5px;
        display: block;
      }


      .nav-pills>li.active>a {
        background-color: #337ab7;
        color: white !important;
      }

      .dropdown-menu {
        padding: 10px;
      }

      .nested-dropdown .dropdown-menu {
        left: 100%;
        top: 0;
        margin-left: 0;
      }

      .dropdown-toggle::after {
        display: none;
        /* Remove default caret */
      }

      /* Content Styling */
      .tab-content {
        display: none;
        /* Hide all by default */
      }

      .tab-content.active {
        display: block;
        /* Show only active content */
      }

      .content-wrapper {
        background: #fff !important;
      }

        /* Content Styling */
        .tab-content {
            display: none; /* Hide all by default */
        }
        .tab-content.active {
            display: block; /* Show only active content */
        }
        .content-wrapper {
            background: #fff !important;
        }
        .tab-content1 {
            display: block;
        }
        .tab-content1>.tab-pane {
            display: none;
        }
        .tab-content1>.active {
            display: block;
        }
    </style>
    <?php
    //debug($form_data);exit;
    extract($form_data);
    $full_name = get_enum_list('title', $title) . ' ' . $first_name . ' ' . $last_name;
    ?>
    <div id="general_user" class="bodyContent">
      <div class="table_outer_wrper"><!-- PANEL WRAP START -->
        <div class="org_row">
          <div class="col-md-3 rprt_lft">
            <h5>Settings</h5>
            <div class="dropdown">
              <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown">
                <?php echo $dropdown; ?> <i class="far fa-angle-down"></i>
              </button>
              <ul class="dropdown-menu" aria-labelledby="dropdownMenu">
                <!-- Nested Pills Inside Dropdown -->
                <li><a href="#" data-target="set_balance">Set Balance Alert</a></li>
                <li><a href="#" data-target="set_logo">Logo</a></li>
                <li><a href="#" data-target="set_profile">Profile</a></li>
                <li><a href="#" data-target="set_password">Change Password</a></li>
              </ul>
            </div>
          </div>
          <!--         <div class="panel_title">
               <?php include 'b2b_markup_header_tab.php'; ?>
               </div> -->

          <div id="selectedContent">
            <div class="tab-content <?php if ($dropdown == "Set Balance Alert") {
                                      echo 'active';
                                    } ?>" id="set_balance">
              <div class="col-md-12 rprt_rgt mt-15">
                <div class="panel_bdy st_blnce fltr nopad"><!-- PANEL BODY START -->

                  <form name="fffffff"
                    autocomplete="off"
                    action="<?= base_url('index.php/management/set_balance_alert') ?>"

                    id=""
                    role="form"
                    class="form-horizontal">

                    <input type="hidden" name="FID" value="WyYLbgR5Aw9QNVxhU20HYVM5DzsDYwQOVTECOQBtUXJRJ1UHBmIFaw1xC2Y=">

                    <fieldset form="set_balance_alert_form">
                      <legend class="form_legend">SET BALANCE ALERT</legend>

                      <input name="origin" type="hidden" id="origin" class="origin hiddenIp" value="7">

                      <div class="form-group">
                        <label class="col-sm-3 control-label" for="threshold_amount">Amount<span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                          <?php $selected_amount = $form_data_balance['threshold_amount'] ?? ''; ?>
                          <select required name="threshold_amount" class="threshold_amount form-control" id="threshold_amount"
                            data-container="body" data-toggle="popover" data-original-title="" data-placement="bottom"
                            data-trigger="hover focus" data-content="Amount">
                            <option value="INVALIDIP" <?= $selected_amount == 'INVALIDIP' ? 'selected' : '' ?>>Please Select</option>
                            <option value="1000" <?= $selected_amount == '1000' ? 'selected' : '' ?>>USD 1,000</option>
                            <option value="50000" <?= $selected_amount == '50000' ? 'selected' : '' ?>>USD 50,000</option>
                            <option value="40000" <?= $selected_amount == '40000' ? 'selected' : '' ?>>USD 40,000</option>
                            <option value="30000" <?= $selected_amount == '30000' ? 'selected' : '' ?>>USD 30,000</option>
                            <option value="20000" <?= $selected_amount == '20000' ? 'selected' : '' ?>>USD 20,000</option>
                            <option value="10000" <?= $selected_amount == '10000' ? 'selected' : '' ?>>USD 10,000</option>
                            <option value="5000" <?= $selected_amount == '5000' ? 'selected' : '' ?>>USD 5,000</option>
                          </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-3 control-label" for="mobile_number">Mobile Number</label>
                        <div class="col-sm-6">
                          <input value="<?=$form_data_balance['mobile_number']?>" name="mobile_number" type="text" placeholder="Mobile Number"
                            class="mobile_number form-control" id="mobile_number"
                            data-container="body" data-toggle="popover" data-original-title="" data-placement="bottom"
                            data-trigger="hover focus" data-content="Mobile Number">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-3 control-label" for="email_id">Email</label>
                        <div class="col-sm-6">
                          <input value="<?=$form_data_balance['email_id']?>" name="email_id" type="email" placeholder="Email"
                            class="email_id form-control" id="email_id"
                            data-container="body" data-toggle="popover" data-original-title="" data-placement="bottom"
                            data-trigger="hover focus" data-content="Email">
                        </div>
                      </div>

                      <div class="checkbox">
                        <label class="col-sm-3 control-label" for="enable_sms_notification"></label>
                        <label class="radio-inline" for="set_balance_alert_formenable_sms_notification1">
                          <input checked class="enable_sms_notification checkboxIp" type="checkbox"
                            name="enable_sms_notification[]" id="set_balance_alert_formenable_sms_notification1"
                            value="1">Send SMS
                        </label>
            <div id="selectedContent">
               	<div class="tab-content <?php if($dropdown == "Set Balance Alert"){ echo 'active';}?>" id="set_balance">
                  	<div class="col-md-12 rprt_rgt mt-15">
					<div class="panel_bdy st_blnce fltr nopad"><!-- PANEL BODY START -->
					
						<form name="fffffff" 
      autocomplete="off" 
      action="" 
     
      id="" 
      role="form" 
      class="form-horizontal">
    
    <input type="hidden" name="FID" value="WyYLbgR5Aw9QNVxhU20HYVM5DzsDYwQOVTECOQBtUXJRJ1UHBmIFaw1xC2Y=">
    
    <fieldset form="set_balance_alert_form">
        <legend class="form_legend">SET BALANCE ALERT</legend>
        
        <input name="origin" type="hidden" id="origin" class="origin hiddenIp" value="7">
        
        <div class="form-group">
            <label class="col-sm-3 control-label" for="threshold_amount">Amount<span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <select required name="threshold_amount" class="threshold_amount form-control" id="threshold_amount" 
                        data-container="body" data-toggle="popover" data-original-title="" data-placement="bottom" 
                        data-trigger="hover focus" data-content="Amount">
                    <option value="INVALIDIP">Please Select</option>
                    <option value="1000">USD 1,000</option>
                    <option value="50000" selected>USD 50,000</option>
                    <option value="40000">USD 40,000</option>
                    <option value="30000">USD 30,000</option>
                    <option value="20000">USD 20,000</option>
                    <option value="10000">USD 10,000</option>
                    <option value="5000">USD 5,000</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-sm-3 control-label" for="mobile_number">Mobile Number</label>
            <div class="col-sm-6">
                <input value="6767676767" name="mobile_number" type="text" placeholder="Mobile Number" 
                       class="mobile_number form-control" id="mobile_number" 
                       data-container="body" data-toggle="popover" data-original-title="" data-placement="bottom" 
                       data-trigger="hover focus" data-content="Mobile Number">
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-sm-3 control-label" for="email_id">Email</label>
            <div class="col-sm-6">
                <input value="hgfhgfhf@gmail.com" name="email_id" type="email" placeholder="Email" 
                       class="email_id form-control" id="email_id" 
                       data-container="body" data-toggle="popover" data-original-title="" data-placement="bottom" 
                       data-trigger="hover focus" data-content="Email">
            </div>
        </div>
        
        <div class="checkbox">
            <label class="col-sm-3 control-label" for="enable_sms_notification"></label>
            <label class="radio-inline" for="set_balance_alert_formenable_sms_notification1">
                <input checked class="enable_sms_notification checkboxIp" type="checkbox" 
                       name="enable_sms_notification[]" id="set_balance_alert_formenable_sms_notification1" 
                       value="1">Send SMS
            </label>
        </div>
        
        <div class="checkbox">
            <label class="col-sm-3 control-label" for="enable_email_notification"></label>
            <label class="radio-inline" for="set_balance_alert_formenable_email_notification1">
                <input checked class="enable_email_notification checkboxIp" type="checkbox" 
                       name="enable_email_notification[]" id="set_balance_alert_formenable_email_notification1" 
                       value="1">Notify to E-mail
            </label>
        </div>
    </fieldset>
    
    <div class="form-group">
        <div class="col-sm-8 col-sm-offset-4">
            <button type="submit" id="" class="btn btn-success">Update</button>
            <button type="reset" id="set_balance_alert_form_reset" class="btn btn-warning">Reset</button>
        </div>
    </div>
</form>

						
					</div><!-- PANEL BODY END -->
					<div class="col-md-offset-2">
						<?php if(valid_array($balance_alert_details)  == true) {?>
						<span class="text-danger">
						NOTE: You would be alerted, when the credit balance falls below <strong><?=get_enum_list('threshold_amount_range', $balance_alert_details['threshold_amount']);?></strong>
						</span>
						<!-- <br />
						<span class="pull-right">Last Updated on: <strong><?//=app_friendly_date($balance_alert_details['created_datetime'])?> </strong></span>
						 -->
						<?php } ?>
					</div>
					</div><!-- PANEL WRAP END -->
				</div>
               	<div class="tab-content <?php if($dropdown == "Logo"){ echo 'active';}?>" id="set_logo">
                    <div class="col-md-12 rprt_rgt mt-15">
		<div class="panel-heading nopad mb-2"><!-- PANEL HEAD START -->
			<div class="panel-title">
				<ul class="nav nav-tabs nav-justified" role="tablist" id="myTab">
					<!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE START-->
					<li role="presentation" class=""><a href="#fromList" aria-controls="home" role="tab" data-toggle="tab"><?php echo get_app_message('AL00314');?> <span class="fa fa-image"></span> &nbsp;Add Logo</a></li>
					<!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE END -->
				</ul>
			</div>
		</div><!-- PANEL HEAD START -->
    <div class="panel_bdy nopad">         
				  <div role="tabpanel" class="tab-pane active clearfix" id="fromList">
						<div class="col-md-12 nopad">
							<div class="panel panel-info clearfix">
							<div class="col-md-12 domain_logo_align">
							<?php echo get_domain_logo($domain_logo);?>
							</div>
							<div class="col-md-12">
								<form class="form-horizontal" role="form" id="domain_logo" enctype="multipart/form-data" method="POST" action="<?=base_url().'index.php/management/set_balance_alert?'.$_SERVER['QUERY_STRING']?>" autocomplete="off" name="domain_logo">        
									<input type="hidden" value="<?php echo get_domain_auth_id();?>" required="" class=" origin hiddenIp" id="origin" name="origin">
									<input type="hidden" value="logo_upload" name="page_type">
								        <div class="form-group"></div>
								        <div class="form-group">
								            <label form="domain_logo" for="domain_logo" class="col-sm-4 control-label">Change Logo<span class="text-danger">*</span></label>
								            <div class="col-sm-8">
								                <input type="file" id="domain_logo" class=" domain_logo domain_logo" placeholder="" required="" accept="image/*" name="domain_logo" value="">
								                <br>
											<font color="Red"> Size: 180*40 </font>
								            </div>
								        </div>
								    <div class="form-group">
								        <div class="col-sm-8 col-sm-offset-4">
								            <button class=" btn btn-success " id="domain_logo_submit" type="button">Submit</button>
								            <button class=" btn btn-warning " id="domain_logo_reset" type="reset">Reset</button>
								        </div>
								    </div>
								</form>
								</div>
							</div>
						</div>
				  </div>
		
		</div><!-- PANEL BODY END -->
	</div><!-- PANEL WRAP END -->
</div>
               	<div class="tab-content <?php if($dropdown == "Profile"){ echo 'active';}?>" id="set_profile">
                    <div class="col-md-12 rprt_rgt mt-15">
				<div class="b2b_agent_profile">
      <div class="tab-content sidewise_tab">
       
        <div role="tabpanel" class="tab-pane active clearfix" id="profile">
          <div class="dashdiv col-xs-12 nopad">
            <div class="alldasbord nopad">
              <div class="userfstep">
                <div class="step_head">
                  <h3 class="welcmnote">Hi,
                    <?=$full_name?>
                  </h3>
                  <a href="#edit_user_profile" data-aria-controls="home" data-role="tab" data-toggle="tab" class="editpro" id="edit_profile_btn">Edit profile</a> </div>
                <div class="clearfix"></div>
                <!-- Edit User Profile starts-->
                <div class="tab-content1">
                  <div role="tabpanel filldiv" class="tab-pane active" id="show_user_profile">
                    <div class="colusrdash"> <img src="<?=(empty($image) == false ? $GLOBALS['CI']->template->domain_images($image) : $GLOBALS['CI']->template->template_images('face.png'))?>" alt="profile Image" /> </div>
                    <div class="useralldets">
                      <h4 class="dashuser">
                        <?=$full_name?>
                      </h4>
                      <div class="rowother"> <span class="far fa-user"></span> <span class="labrti">
                      	<span class="inlabl_name">Agency Name</span>
                        <?=(empty($agency_name) == true ? 'Agency Name' : $agency_name).' - '.$uuid?>
                        </span>
                       </div>
                      <div class="rowother"> <span class="far fa-envelope"></span> <span class="labrti">
                      	<span class="inlabl_name">Email</span>
                        <?=(empty($email) == true ? '---' : $email)?>
                        </span>
                       </div>
                     
                       
                      <div class="rowother"> <span class="far fa-mobile"></span> <span class="labrti">
                      	<span class="inlabl_name">Phone Number</span>
                        <?=(($phone == 0 || $phone == '') ? '---':$mobile_code.' '.$phone)?>
                        </span>
                      </div>

                      <div class="checkbox">
                        <label class="col-sm-3 control-label" for="enable_email_notification"></label>
                        <label class="radio-inline" for="set_balance_alert_formenable_email_notification1">
                          <input checked class="enable_email_notification checkboxIp" type="checkbox"
                            name="enable_email_notification[]" id="set_balance_alert_formenable_email_notification1"
                            value="1">Notify to E-mail
                        </label>
                      </div>
                    </fieldset>

                    <div class="form-group">
                      <div class="col-sm-8 col-sm-offset-4">
                        <button type="submit" id="" class="btn btn-success">Update</button>
                        <button type="reset" id="set_balance_alert_form_reset" class="btn btn-warning">Reset</button>
                      </div>
                    </div>
                  </form>


                </div><!-- PANEL BODY END -->
                <div class="col-md-offset-2">
                  <?php if (valid_array($balance_alert_details)  == true) { ?>
                    <span class="text-danger">
                      NOTE: You would be alerted, when the credit balance falls below <strong><?= get_enum_list('threshold_amount_range', $balance_alert_details['threshold_amount']); ?></strong>
                    </span>
                    <!-- <br />
						<span class="pull-right">Last Updated on: <strong><? //=app_friendly_date($balance_alert_details['created_datetime'])
                                                              ?> </strong></span>
						 -->
                  <?php } ?>
                </div>
              </div><!-- PANEL WRAP END -->
            </div>
            <div class="tab-content <?php if ($dropdown == "Logo") {
                                      echo 'active';
                                    } ?>" id="set_logo">
              <div class="col-md-12 rprt_rgt mt-15">
                <div class="panel-heading nopad mb-2"><!-- PANEL HEAD START -->
                  <div class="panel-title">
                    <ul class="nav nav-tabs nav-justified" role="tablist" id="myTab">
                      <!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE START-->
                      <li role="presentation" class=""><a href="#fromList" aria-controls="home" role="tab" data-toggle="tab"><?php echo get_app_message('AL00314'); ?> <span class="fa fa-image"></span> &nbsp;Add Logo</a></li>
                      <!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE END -->
                    </ul>
                  </div>
                </div><!-- PANEL HEAD START -->
                <div class="panel_bdy nopad">
                  <div role="tabpanel" class="tab-pane active clearfix" id="fromList">
                    <div class="col-md-12 nopad">
                      <div class="panel panel-info clearfix">
                        <div class="col-md-12 domain_logo_align">
                          <?php echo get_domain_logo($domain_logo); ?>
                        </div>
                        <div class="col-md-12">
                          <form class="form-horizontal" role="form" id="domain_logo" enctype="multipart/form-data" method="POST" action="<?= base_url() . 'index.php/management/set_balance_alert?' . $_SERVER['QUERY_STRING'] ?>" autocomplete="off" name="domain_logo">
                            <input type="hidden" value="<?php echo get_domain_auth_id(); ?>" required="" class=" origin hiddenIp" id="origin" name="origin">
                            <input type="hidden" value="logo_upload" name="page_type">
                            <div class="form-group"></div>
                            <div class="form-group">
                              <label form="domain_logo" for="domain_logo" class="col-sm-4 control-label">Change Logo<span class="text-danger">*</span></label>
                              <div class="col-sm-8">
                                <input type="file" id="domain_logo" class=" domain_logo domain_logo" placeholder="" required="" accept="image/*" name="domain_logo" value="">
                                <br>
                                <font color="Red"> Size: 180*40 </font>
                              </div>
                            </div>
                            <div class="form-group">
                              <div class="col-sm-8 col-sm-offset-4">
                                <button class=" btn btn-success " id="domain_logo_submit" type="button">Submit</button>
                                <button class=" btn btn-warning " id="domain_logo_reset" type="reset">Reset</button>
                              </div>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>

                </div><!-- PANEL BODY END -->
              </div><!-- PANEL WRAP END -->
            </div>
            <div class="tab-content <?php if ($dropdown == "Profile") {
                                      echo 'active';
                                    } ?>" id="set_profile">
              <div class="col-md-12 rprt_rgt mt-15">
                <div class="b2b_agent_profile">
                  <div class="tab-content sidewise_tab">

                    <div role="tabpanel" class="tab-pane active clearfix" id="profile">
                      <div class="dashdiv col-xs-12 nopad">
                        <div class="alldasbord nopad">
                          <div class="userfstep">
                            <div class="step_head">
                              <h3 class="welcmnote">Hi,
                                <?= $full_name ?>
                              </h3>
                              <a href="#edit_user_profile" data-aria-controls="home" data-role="tab" data-toggle="tab" class="editpro" id="edit_profile_btn">Edit profile</a>
                            </div>
                            <div class="clearfix"></div>
                            <!-- Edit User Profile starts-->
                            <!--<div class="tab-content active">-->
                            <div role="tabpanel filldiv" class="tab-pane active" id="show_user_profile">
                              <div class="colusrdash"> <img src="<?= (empty($image) == false ? $GLOBALS['CI']->template->domain_images($image) : $GLOBALS['CI']->template->template_images('face.png')) ?>" alt="profile Image" /> </div>
                              <div class="useralldets">
                                <h4 class="dashuser">
                                  <?= $full_name ?>
                                </h4>
                                <div class="rowother"> <span class="far fa-user"></span> <span class="labrti">
                                    <span class="inlabl_name">Agency Name</span>
                                    <?= (empty($agency_name) == true ? 'Agency Name' : $agency_name) . ' - ' . $uuid ?>
                                  </span>
                                </div>
                                <div class="rowother"> <span class="far fa-envelope"></span> <span class="labrti">
                                    <span class="inlabl_name">Email</span>
                                    <?= (empty($email) == true ? '---' : $email) ?>
                                  </span>
                                </div>


                                <div class="rowother"> <span class="far fa-mobile"></span> <span class="labrti">
                                    <span class="inlabl_name">Phone Number</span>
                                    <?= (($phone == 0 || $phone == '') ? '---' : $mobile_code . ' ' . $phone) ?>
                                  </span>
                                </div>
                                <div class="rowother"> <span class="far fa-phone"></span> <span class="labrti">
                                    <span class="inlabl_name">Office Phone</span>
                                    <?= (($office_phone == 0 || $office_phone == '') ? '---' : $office_phone) ?>
                                  </span>
                                </div>
                                <div class="rowother"> <span class="far fa-map-marker"></span> <span class="labrti">
                                    <span class="inlabl_name">Address</span>
                                    <?= (empty($address) == true ? '---' : $address) ?>
                                  </span> </div>
                              </div>
                            </div>
                            <div role="tabpanel" class="tab-pane clearfix" id="edit_user_profile">
                              <form action="<?= base_url() . 'index.php/management/set_balance_alert?' . $_SERVER['QUERY_STRING'] ?>" method="post" name="edit_user_form" id="edit_user_form" enctype="multipart/form-data" autocomplete="off">
                                <div class="col-md-12 text-danger">
                                  <strong><?php echo validation_errors(); ?></strong>
                                </div>
                                <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                <input type="hidden" name="uuid" value="<?= $uuid ?>">
                                <input type="hidden" name="email" value="<?= $email ?>">
                                <div class="infowone nopad">
                                  <div class="clearfix"></div>
                                  <div class="paspertorgn2 paspertedit">
                                    <div class="col-xs-3 margpas">
                                      <div class="tnlepasport_b2b">
                                        <div class="paspolbl ">Title <span class="text-danger">*</span></div>
                                        <div class="lablmain ">
                                          <select name="title" class="clainput" required="required">
                                            <?= generate_options(get_enum_list('title'), (array)$title) ?>
                                          </select>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-xs-4 margpas">
                                      <div class="tnlepasport_b2b">
                                        <div class="paspolbl ">FirstName <span class="text-danger">*</span></div>
                                        <div class="lablmain ">
                                          <input type="text" name="first_name" placeholder="first name" value="<?= $first_name ?>" class="clainput alpha" maxlength="45" required />
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-xs-4 margpas">
                                      <div class="tnlepasport_b2b">
                                        <div class="paspolbl ">LastName <span class="text-danger">*</span></div>
                                        <div class="lablmain ">
                                          <input type="text" name="last_name" placeholder="last name" value="<?= $last_name ?>" class="clainput alpha" maxlength="45" required="required" />
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-xs-3 margpas">
                                      <div class="tnlepasport_b2b">
                                        <div class="paspolbl ">CountryCode<span class="text-danger">*</span></div>
                                        <div class="lablmain ">
                                          <select name="country_code" id="country_code" class="clainput" required="required">
                                            <?php //debug($country_code_list);exit;
                                            ?>
                                            <?= generate_options($phone_code_array, (array)$form_data['country_code']) ?>
                                          </select>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-xs-4 margpas">
                                      <div class="tnlepasport_b2b">
                                        <div class="paspolbl ">MobileNumber <span class="text-danger">*</span></div>
                                        <div class="lablmain ">
                                          <input type="text" name="phone" placeholder="mobile number" value="<?= (($phone == 0 || $phone == '') ? '' : $phone) ?>" class="clainput numeric" required="required" maxlength="10">
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-xs-4 margpas">
                                      <div class="tnlepasport_b2b">
                                        <div class="paspolbl ">DateofBirth <span class="text-danger">*</span></div>
                                        <div class="lablmain ">
                                          <input type="text" name="date_of_birth" id="date_of_birth" placeholder="dob" value="<?= ((strtotime($date_of_birth) <= 0) ? '' : $date_of_birth) ?>" class="clainput" readonly required="required" />
                                        </div>
                                      </div>
                                    </div>

                                    <div class="col-xs-4 margpas">
                                      <div class="tnlepasport_b2b">
                                        <div class="paspolbl ">Office Phone <span class="text-danger">*</span></div>
                                        <div class="lablmain ">
                                          <input type="text" name="office_phone" placeholder="PAN Number" value="<?= (($office_phone == 0 || $office_phone == '') ? '' : $office_phone) ?>" class="clainput" required="required">
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-xs-5 margpas">
                                      <div class="tnlepasport_b2b">
                                        <div class="paspolbl ">Address <span class="text-danger">*</span></div>
                                        <div class="lablmain ">
                                          <textarea name="address" placeholder="address" class="clainput" required="required"><?= $address ?>
							</textarea>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-xs-5 margpas">
                                      <div class="tnlepasport_b2b">
                                        <div class="paspolbl ">ProfileImage</div>
                                        <div class="lablmain ">
                                          <input type="file" name="image" accept="image/*" />
                                        </div>
                                      </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <button type="submit" class="savepspot">Update</button>
                                    <a href="#show_user_profile" data-aria-controls="home" data-role="tab" data-toggle="tab" class="cancelll">Cancel</a>
                                  </div>
                                </div>
                              </form>
                            </div>
                            <!--</div>-->
                            <!-- Edit User Profile Ends-->

                          </div>
                          <div class="clearfix"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Edit User Profile Ends--> 
                
              </div>
            </div>
            <div class="tab-content" <?php if ($dropdown == "Change Password") {
                                        echo 'active';
                                      } ?> id="set_password">
              <div id="general_change_password" class="bodyContent col-md-12">
                <div class="col-md-12 nopad rprt_rgt mt-15">
                  <div class="panel-body nopad"><!-- PANEL BODY START -->
                    <?php
                    /** Generating Change Password Form**/
                    echo $change_page_obj->generate_form('change_password');
                    //echo $this->current_page->generate_form('change_password');
                    ?>
                  </div><!-- PANEL BODY END -->
                </div><!-- PANEL WRAP END -->
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
    function get_domain_logo($domain_logo)
    {
      if (empty($domain_logo) == false && file_exists($GLOBALS['CI']->template->domain_image_full_path($domain_logo))) {
        return '<img src="' . $GLOBALS['CI']->template->domain_images($domain_logo) . '" height="350px" width="350px" class="img-thumbnail">';
      }
    }
    ?>
    <!-- HTML END -->
    <script type="text/javascript">
      $(document).ready(function() {
        // Handle main dropdown toggle
        $('#dropdownMenu').on('click', function(e) {
          e.preventDefault();
          $(this).next('.dropdown-menu').toggle();
        });

        // Handle nested dropdown toggle
        $('.nested-dropdown > a').on('click', function(e) {
          e.preventDefault();
          var submenu = $(this).next('.dropdown-menu');

          // Hide other open submenus
          $('.nested-dropdown .dropdown-menu').not(submenu).hide();
          submenu.toggle();
        });

        // Handle item selection
        $('.dropdown-menu a[data-target]').on('click', function(e) {
          //alert($(this).attr('href'));
          if ($(this).attr('href') == "#") {
            e.preventDefault();
            var selectedText = $(this).text();
            var target = $(this).data('target');

            // Update the main dropdown button text
            $('#dropdownMenu').html(selectedText + ' <i class="fa fa-angle-down"></i>');

            // Remove active class from all, then add to the selected item
            $('.dropdown-menu a').removeClass('active');
            $(this).addClass('active');

            // Show selected content
            $('.tab-content').removeClass('active');
            $('#' + target).addClass('active');

            // Close the main dropdown (but not nested ones)
            if (!$(this).closest('.nested-dropdown').length) {
              $('.dropdown-menu').hide();
            }
          } else {
            window.location.href = $(this).attr('href');
          }
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
          if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').hide();
          }
        });

        // Prevent dropdown closing when clicking inside a nested menu
        $('.nested-dropdown .dropdown-menu').on('click', function(e) {
          e.stopPropagation();
          $('.dropdown-menu').hide();
        });

        // Initialize Bootstrap Datepicker
        $('.datepick').datepicker({
          format: 'dd-mm-yyyy',
          autoclose: true
        });
      });
    </script>
    <script>
      $('#set_balance_alert_form_submit').click(function() {

        var mobile_number = $('#mobile_number').val();
        var email_id = $('#email_id').val();

        if (mobile_number) {
          if (mobile_number.length > 10) {
            alert('Mobile Number not exceed 10');
            return false;
          } else {
            var sms = $('#set_balance_alert_formenable_sms_notification1').val();
            //alert(sms);
            if ($("#set_balance_alert_formenable_sms_notification1").prop('checked') == false) {
              alert('Please check the Send SMS');
              return false;
            }
          }


        } else {
          if ($("#set_balance_alert_formenable_sms_notification1").prop('checked') == true) {
            if (mobile_number) {
              return true;
            } else {
              alert('Please Enter Mobile Number');
              return false;
            }
          }
        }
        if (email_id) {
          var email = $('#set_balance_alert_formenable_email_notification1').val();
          if ($("#set_balance_alert_formenable_email_notification1").prop('checked') == false) {
            alert('Please check the Notify to E-mail');
            return false;
          }

        } else {
          if ($("#set_balance_alert_formenable_email_notification1").prop('checked') == true) {

            if (email_id) {
              return true;
            } else {
              alert('Please Enter Email id');
              return false;
            }
          }
        }
        if (!email_id && !mobile_number) {
          alert('Please Select any one');
          return false;
        }


      });
    </script>
    <script>
      $(document).ready(function() {
        <?php if (empty(validation_errors()) == false) { ?>
          $('#edit_profile_btn').trigger('click');
        <?php } ?>

        $('.editpasport').click(function() {
          $(this).parent().parent('.infowone').addClass('editsave');
        });
        $('.cancelll').click(function() {
          $(this).parent().parent('.infowone').removeClass('editsave');
        });
        $('#country_code').on('change', function() {
          country_origion = $(this).val();

          if (country_origion == '92') {
            $("#pan_data").css("display", "block");
          } else {
            $("#pan_data").css("display", "none");
          }

        });
      });
    </script>
    <?php
    $datepicker = array(array('date_of_birth', PAST_DATE));
    $GLOBALS['CI']->current_page->set_datepicker($datepicker);
    ?>