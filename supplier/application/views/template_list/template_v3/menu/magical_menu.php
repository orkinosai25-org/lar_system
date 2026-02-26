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
$accomodation_module = is_active_hotel_module();
// 

?>
<ul class="sidebar-menu">
	<li class="header">MAIN NAVIGATION</li>
	<li>
		<a href="<?php echo base_url() . 'index.php/eco_stays' ?>"> <i class="fa fa-bed"></i> <span>
				Hotel List & Room Allocation </span> </a>

	</li>
	<li class="treeview">
		<a href="#">
			<i class="far fa-chart-bar"></i>
			<span> Reports </span><i class="fa fa-angle-left pull-right"></i>
		</a>
		<ul class="treeview-menu">
			
				<li><a href="<?php echo base_url() . 'index.php/report/b2c_hotel_report'; ?>"><i class="<?= get_arrangement_icon(META_ACCOMODATION_COURSE) ?>"></i> Eco-stays</a></li>
	

		</ul>
	</li>



</ul>