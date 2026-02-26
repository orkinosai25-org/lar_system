<?php
$active_domain_modules = $this->active_domain_modules;
/**
 * Need to make privilege based system
 * Privilege only for loading menu and access of the web page
 * 
 * Data loading will not be based on privilege.
 * Data loading logic will be different.
 * It depends on many parameters
 */
$menu_list = array();
if (count($active_domain_modules) > 0) {
	$any_domain_module = true;
} else {
	$any_domain_module = false;
}
$airline_module = is_active_airline_module();
$accomodation_module = is_active_hotel_module();
$bus_module = is_active_bus_module();
$package_module = is_active_package_module();
$sightseeing_module = is_active_sightseeing_module();
$car_module = is_active_car_module();
$transfer_module =is_active_transferv1_module();
// debug($car_module);exit;
?>
<ul class="sidebar-menu" id="magical-menu">
	<li class="header">MAIN NAVIGATION</li>
	<li class="active treeview">
		<a href="<?php echo base_url()?>">
			<img src="<?php echo $GLOBALS['CI']->template->template_images('dashboard.svg')?>" alt="flight">
			<img src="<?php echo $GLOBALS['CI']->template->template_images('dashboard-active.svg')?>" alt="flight"> <span>Dashboard</span>
		</a>
	</li>
	<!-- USER ACCOUNT MANAGEMENT -->
	<li class="treeview hide">
		<a href="#">
			<i class="fa fa-search"></i><span> Search </span><i class="fa fa-angle-left pull-right"></i></a>
		<ul class="treeview-menu">
		<!-- USER TYPES -->
			<?php if ($airline_module) { ?>
			<li><a href="<?=base_url().'menu/index/flight/?default_view='.META_AIRLINE_COURSE?>"><i class="<?=get_arrangement_icon(META_AIRLINE_COURSE)?>"></i> <span class="hidden-xs">Flight</span></a></li>
			<?php } ?>
			<?php if ($accomodation_module) { ?>
			<li><a href="<?=base_url().'menu/index/hotel/?default_view='.META_ACCOMODATION_COURSE?>"><i class="<?=get_arrangement_icon(META_ACCOMODATION_COURSE)?>"></i> <span class="hidden-xs">Hotel</span></a></li>
			<?php } ?>
			<?php if ($bus_module) { ?>
			<li><a href="<?=base_url().'menu/index/bus/?default_view='.META_BUS_COURSE?>"><i class="<?=get_arrangement_icon(META_BUS_COURSE)?>"></i> <span class="hidden-xs">Bus</span></a></li>
			<?php } ?>
			<?php if($transfer_module){?>
				<li><a href="<?=base_url().'menu/index/transfers/?default_view='.META_TRANSFERV1_COURSE?>"><i class="<?=get_arrangement_icon(META_TRANSFERV1_COURSE)?>"></i> <span class="hidden-xs">Transfers</span></a></li>
			<?php }?>
			<?php if($sightseeing_module){?>
				<li><a href="<?=base_url().'menu/index/sightseeing/?default_view='.META_SIGHTSEEING_COURSE?>"><i class="<?=get_arrangement_icon(META_SIGHTSEEING_COURSE)?>"></i> <span class="hidden-xs">Activities</span></a></li>
			<?php }?>
			<?php if($car_module){?>
				<li><a href="<?=base_url().'menu/index/car/?default_view='.META_CAR_COURSE?>"><i class="<?=get_arrangement_icon(META_CAR_COURSE)?>"></i> <span class="hidden-xs">Car</span></a></li>
			<?php }?>
			<?php if ($package_module) { ?>
			<li><a href="<?=base_url().'menu/index/package/?default_view='.META_PACKAGE_COURSE?>"><i class="<?=get_arrangement_icon(META_PACKAGE_COURSE)?>"></i> <span class="hidden-xs">Holiday</span></a></li>
			<?php } ?>
		</ul>
	</li>
	<?php if ($any_domain_module) {?>
	
	<li>
		<a href="<?=base_url().'management/b2b_airline_markup';?>">
			<img src="<?php echo $GLOBALS['CI']->template->template_images('business.svg')?>" alt="flight"> 
			<img src="<?php echo $GLOBALS['CI']->template->template_images('business-active.svg')?>" alt="flight"> 
			<span> My Business </span>
		</a>
		
	
	
		
	</li>
	<?php } ?>
	<li>
		<a href="<?php echo base_url().'management/set_balance_alert?uid=' . intval($GLOBALS['CI']->entity_user_id);?>">
			<img src="<?php echo $GLOBALS['CI']->template->template_images('settings.svg')?>" alt="flight">
			<img src="<?php echo $GLOBALS['CI']->template->template_images('settings-active.svg')?>" alt="flight">
			<span> Settings </span>
		</a>
	</li>
	<li>
		<a href="<?php echo base_url().'report/flight/';?>">
			<img src="<?php echo $GLOBALS['CI']->template->template_images('reports.svg')?>" alt="flight">
			<img src="<?php echo $GLOBALS['CI']->template->template_images('reports-active.svg')?>" alt="flight">
			<span> My Reports </span>
		</a>
		
	</li>
	</ul>