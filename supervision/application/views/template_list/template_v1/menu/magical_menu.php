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
$sightseen_module = is_active_sightseeing_module();
$car_module = is_active_car_module();
$transferv1_module = is_active_transferv1_module();
$bb = 'b2b';
$bc = 'b2c';
$b2b = is_active_module($bb);
$b2c = is_active_module($bc);

//checking social login status 
$social_login = 'facebook';
$social = is_active_social_login($social_login);
//echo "ela".$accomodation_module;exit;
$accomodation_module = 1;
?>
<ul class="sidebar-menu" id="magical-menu">
    <?php if(check_user_previlege('p1')):?>
    <li class="treeview">
        <a href="<?php echo base_url() ?>">
            <i class="far fa-tachometer-alt"></i> <span>Dashboard</span> </a>
    </li>
    <?php endif; ?>
    <?php if (is_domain_user() == false) { // ACCESS TO ONLY PROVAB ADMIN ?>
        <li class="treeview">
            <a href="#">
                <i class="far fa-wrench"></i> <span>Management</span> <i class="far fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="<?php echo base_url() . 'index.php/user/user_management' ?>"><i class="far fa-user"></i> User</a></li>
                <li><a href="<?php echo base_url() . 'index.php/user/domain_management' ?>"><i class="far fa-laptop"></i> Domain</a></li>
                <li><a href="<?php echo base_url() . 'index.php/module/module_management' ?>"><i class="far fa-sitemap"></i> Master Module</a></li>
            </ul>
        </li>
        <?php if ($any_domain_module) { ?>
            <li class="treeview">
                <a href="#">
                    <i class="far fa-user"></i> <span>Markup</span> <i class="far fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <?php if ($airline_module) { ?>
                        <li><a href="<?php echo base_url() . 'index.php/private_management/airline_domain_markup' ?>"><i class="<?= get_arrangement_icon(META_AIRLINE_COURSE) ?>"></i> Flight</a></li>
                    <?php } ?>
                    <?php if ($accomodation_module) { ?>
                        <li><a href="<?php echo base_url() . 'index.php/private_management/hotel_domain_markup' ?>"><i class="<?= get_arrangement_icon(META_ACCOMODATION_COURSE) ?>"></i> Hotel</a></li>
                    <?php } ?>
                    <?php if ($bus_module) { ?>
                        <li><a href="<?php echo base_url() . 'index.php/private_management/bus_domain_markup' ?>"><i class="<?= get_arrangement_icon(META_BUS_COURSE) ?>"></i> Bus</a></li>
                    <?php } ?>
                    <?php if ($transferv1_module) { ?>
                        <li><a href="<?php echo base_url() . 'index.php/private_management/transfer_domain_markup' ?>"><i class="<?= get_arrangement_icon(META_TRANSFERV1_COURSE) ?>"></i>Transfers</a></li>
                    <?php } ?>

                    <?php if ($sightseen_module) { ?>
                        <li><a href="<?php echo base_url() . 'index.php/private_management/sightseeing_domain_markup' ?>"><i class="<?= get_arrangement_icon(META_SIGHTSEEING_COURSE) ?>"></i>Activities</a></li>
                    <?php } ?>

                </ul>
            </li>
        <?php } ?>
        <li class="treeview">
            <a href="<?php echo base_url() . 'index.php/private_management/process_balance_manager' ?>">
                <i class="far fa-google-wallet"></i> 
                <span> Master Balance Manager </span>
            </a>
        </li>
        <li class="treeview">
            <a href="<?php echo base_url() . 'index.php/private_management/event_logs' ?>">
                <i class="far fa-shield"></i> 
                <span> Event Logs </span>
            </a>
        </li>
    <?php
    } else if ((is_domain_user() == true)) {
        // ACCESS TO ONLY DOMAIN ADMIN
        ?>
        <!-- USER ACCOUNT MANAGEMENT -->
        <?php if(check_user_previlege('p2')):?>
        <li class="treeview">
            <a href="#">
                <i class="far fa-user"></i> 
                <span> Users </span><i class="far fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <!-- USER TYPES -->
                <?php if ($b2c) { if(check_user_previlege('p17')): ?>
                    <li><a href="<?php echo base_url() . 'index.php/user/b2c_user?filter=user_type&q=' . B2C_USER; ?>"><i class="far fa-circle"></i> B2C</a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo base_url() . 'index.php/user/b2c_user?filter=user_type&q=' . B2C_USER . '&user_status=' . ACTIVE; ?>"><i class="far fa-check"></i> Active</a></li>
                            <li><a href="<?php echo base_url() . 'index.php/user/b2c_user?filter=user_type&q=' . B2C_USER . '&user_status=' . INACTIVE; ?>"><i class="far fa-times"></i> InActive</a></li>
                            <li><a href="<?php echo base_url() . 'index.php/user/get_logged_in_users?filter=user_type&q=' . B2C_USER; ?>"><i class="far fa-circle"></i> Logged In User</a></li>
                        </ul>
                    </li>
                <?php endif; } ?>
                <?php if ($b2b) { if(check_user_previlege('p24')): ?>
                    <li><a href="<?php echo base_url() . 'index.php/user/b2b_user?filter=user_type&q=' . B2B_USER ?>"><i class="far fa-circle"></i> Agents</a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo base_url() . 'index.php/user/b2b_user?user_status=' . ACTIVE; ?>"><i class="far fa-check"></i> Active</a></li>
                            <li><a href="<?php echo base_url() . 'index.php/user/b2b_user?user_status=' . INACTIVE; ?>"><i class="far fa-times"></i> InActive</a></li>
                            <li><a href="<?php echo base_url() . 'index.php/user/get_logged_in_users?filter=user_type&q=' . B2B_USER; ?>"><i class="far fa-circle"></i> Logged In User</a></li>
                        </ul>
                    </li>
                <?php endif; } ?>
		  <li><a href="<?php echo base_url() . 'index.php/user/ultralux_user?filter=user_type&q=' . ULTRALUX_USER ?>"><i class="far fa-circle"></i> Ultralux</a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo base_url() . 'index.php/user/ultralux_user?user_status=' . ACTIVE; ?>"><i class="far fa-check"></i> Active</a></li>
                            <li><a href="<?php echo base_url() . 'index.php/user/ultralux_user?user_status=' . INACTIVE; ?>"><i class="far fa-times"></i> InActive</a></li>
                            <li><a href="<?php echo base_url() . 'index.php/user/get_logged_in_users?filter=user_type&q=' . ULTRALUX_USER; ?>"><i class="far fa-circle"></i> Logged In User</a></li>
                        </ul>
                    </li>
	<?php if(check_user_previlege('p73')):?>
                <li><a href="<?php echo base_url() . 'index.php/user/user_management?filter=user_type&q=' . SUB_ADMIN ?>"><i class="far fa-circle"></i> Sub Admin</a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo base_url() . 'index.php/user/user_management?filter=user_type&q=' . SUB_ADMIN . '&user_status=' . ACTIVE; ?>"><i class="far fa-check"></i> Active</a></li>
                        <li><a href="<?php echo base_url() . 'index.php/user/user_management?filter=user_type&q=' . SUB_ADMIN . '&user_status=' . INACTIVE; ?>"><i class="far fa-times"></i> InActive</a></li>
                        <li><a href="<?php echo base_url() . 'index.php/user/get_logged_in_users?filter=user_type&q=' . SUB_ADMIN; ?>"><i class="far fa-circle"></i> Logged In User</a></li>
                    </ul>
                </li>
            <?php endif; ?>
            </ul>
                        <li>
        <a href="<?php echo base_url() . 'index.php/user/supplier_management' ?>">
                                <i class="fas fa-circle-notch"></i><span>Supplier</span></a>
        </li>             
        </li>
        <?php endif; 
         if ($any_domain_module) { if(check_user_previlege('p3')):?>
            <li class="treeview">
                <a href="#">
                    <i class="fas fa-shield"></i> 
                    <span> Queues </span><i class="far fa-angle-left pull-right"></i>
                </a>
                <?php if(check_user_previlege('p71')): ?>
                <ul class="treeview-menu">
                    <li><a href="<?php echo base_url() . 'index.php/report/cancellation_queue/'; ?>"><i class="far fa-flight"></i> Flight Cancellation </a>
                </ul>
            <?php endif; ?>
             </li>
            <?php endif; if(check_user_previlege('p4')):?>
            <li class="treeview">
                <a href="#">
                    <i class="fas fa-chart-bar"></i> 
                    <span> Reports </span><i class="far fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <!-- USER TYPES -->
                    <?php if(check_user_previlege('p74')): ?>
                    <li><a href="#"><i class="far fa-circle"></i> B2C</a>
                        <ul class="treeview-menu">
                            <?php if ($airline_module) { if(check_user_previlege('p18')):?>
                                <li><a href="<?php echo base_url() . 'index.php/report/b2c_flight_report/'; ?>"><i class="far fa-plane"></i> Flight</a></li>
                            <?php endif; } ?>
                            <?php if ($accomodation_module) { if(check_user_previlege('p19')):?>
                                <li><a href="<?php echo base_url() . 'index.php/report/b2c_hotel_report/'; ?>"><i class="far fa-bed"></i> Hotel</a></li>
                            <?php endif; } ?>
                            <?php if ($bus_module) { if(check_user_previlege('p20')):?>
                                <li><a href="<?php echo base_url() . 'index.php/report/b2c_bus_report/'; ?>"><i class="<?= get_arrangement_icon(META_BUS_COURSE) ?>"></i> Bus</a></li>
                            <?php endif; } ?>

                            <?php if ($transferv1_module) { if(check_user_previlege('p21')): ?>
                                <li><a href="<?php echo base_url() . 'index.php/report/b2c_transfers_report/'; ?>"><i class="<?= get_arrangement_icon(META_TRANSFERV1_COURSE) ?>"></i> Transfer</a></li>

                            <?php endif; }
                            ?>

                            <?php if ($sightseen_module) { if(check_user_previlege('p22')):?>
                                <li><a href="<?php echo base_url() . 'index.php/report/b2c_activities_report/'; ?>"><i class="<?= get_arrangement_icon(META_SIGHTSEEING_COURSE) ?>"></i> Activities</a></li>

                            <?php endif; } ?>
                            <?php if ($car_module) { if(check_user_previlege('p23')):?>
                                <li><a href="<?php echo base_url() . 'index.php/report/b2c_car_report/'; ?>"><i class="<?= get_arrangement_icon(META_CAR_COURSE) ?>"></i> Car</a></li>
                            <?php  endif; } ?>


                        </ul>
                    </li>
                    <li><a href="#"><i class="far fa-circle"></i> Ultra lux</a>
                        <ul class="treeview-menu">
                            <?php if ($airline_module) { if(check_user_previlege('p18')):?>
                                <li><a href="<?php echo base_url() . 'index.php/report/ultra_flight_report/'; ?>"><i class="far fa-plane"></i> Flight</a></li>
                            <?php endif; } ?>
                            <?php if ($accomodation_module) { if(check_user_previlege('p19')):?>
                                <li><a href="<?php echo base_url() . 'index.php/report/ultra_hotel_report/'; ?>"><i class="far fa-bed"></i> Hotel</a></li>
                            <?php endif; } ?>
                            <?php if ($bus_module) { if(check_user_previlege('p20')):?>
                                <li><a href="<?php echo base_url() . 'index.php/report/b2c_bus_report/'; ?>"><i class="<?= get_arrangement_icon(META_BUS_COURSE) ?>"></i> Bus</a></li>
                            <?php endif; } ?>

                            <?php if ($transferv1_module) { if(check_user_previlege('p21')): ?>
                                <li><a href="<?php echo base_url() . 'index.php/report/ultra_transfers_report/'; ?>"><i class="<?= get_arrangement_icon(META_TRANSFERV1_COURSE) ?>"></i> Transfer</a></li>

                            <?php endif; }
                            ?>

                            <?php if ($sightseen_module) { if(check_user_previlege('p22')):?>
                                <li><a href="<?php echo base_url() . 'index.php/report/b2c_activities_report/'; ?>"><i class="<?= get_arrangement_icon(META_SIGHTSEEING_COURSE) ?>"></i> Activities</a></li>

                            <?php endif; } ?>
                            <?php if ($car_module) { if(check_user_previlege('p23')):?>
                                <li><a href="<?php echo base_url() . 'index.php/report/ultra_car_report/'; ?>"><i class="<?= get_arrangement_icon(META_CAR_COURSE) ?>"></i> Car</a></li>
                            <?php  endif; } ?>


                        </ul>
                    </li>
                <?php endif; if(check_user_previlege('p75')): ?>
                    <li><a href="#"><i class="far fa-circle"></i> Agent</a>
                        <ul class="treeview-menu">
                    <?php if ($airline_module) { if(check_user_previlege('p25')):?>
                                <li><a href="<?php echo base_url() . 'index.php/report/b2b_flight_report/'; ?>"><i class="far fa-plane"></i> Flight</a></li>
                            <?php endif; } ?>
                    <?php if ($accomodation_module) { if(check_user_previlege('p26')): ?>
                                <li><a href="<?php echo base_url() . 'index.php/report/b2b_hotel_report/'; ?>"><i class="far fa-bed"></i> Hotel</a></li>
                    <?php endif; } ?>
                    <?php if ($bus_module) { if(check_user_previlege('p27')): ?>
                                <li><a href="<?php echo base_url() . 'index.php/report/b2b_bus_report/'; ?>"><i class="<?= get_arrangement_icon(META_BUS_COURSE) ?>"></i> Bus</a></li>
                    <?php endif; } 
                    if ($transferv1_module) { if(check_user_previlege('p28')):?>
                        <li><a href="<?php echo base_url() . 'index.php/report/b2b_transfers_report/'; ?>"><i class="<?= get_arrangement_icon(META_TRANSFERV1_COURSE) ?>"></i>Transfers</a></li>
                    <?php endif; } ?>
                    <?php if ($sightseen_module) { if(check_user_previlege('p29')): ?>
                        <li><a href="<?php echo base_url() . 'index.php/report/b2b_activities_report/'; ?>"><i class="<?= get_arrangement_icon(META_SIGHTSEEING_COURSE) ?>"></i> Activities</a></li>
                    <?php endif; } ?>
                            <?php if ($car_module) { if(check_user_previlege('p30')): ?>
                                <li><a href="<?php echo base_url() . 'index.php/report/b2b_car_report/'; ?>"><i class="<?= get_arrangement_icon(META_CAR_COURSE) ?>"></i> Car</a></li>
                            <?php endif; } ?>
                        </ul>
                    </li>
                <?php endif; ?>

                </ul>
                <ul class="treeview-menu">
                    <!--  TYPES -->
                    <li class="treeview">
                        <a href="<?php echo base_url() . 'index.php/transaction/logs' ?>">
                            <i class="far fa-shield"></i> 
                            <span> Transaction Logs </span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="<?php echo base_url() . 'index.php/transaction/search_history' ?>">
                            <i class="far fa-search"></i> 
                            <span> Search History </span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="<?php echo base_url() . 'index.php/transaction/top_destinations' ?>">
                            <i class="far fa-globe"></i> 
                            <span> Top Destinations</span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="<?php echo base_url() . 'index.php/management/account_ledger' ?>">
                            <i class="fas fa-chart-bar "></i> 
                            <span> Account Ledger</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; 
            if(check_user_previlege('p5')):?>
            <li class="treeview">
                <a href="#">
                    <i class="far fa-money-bill"></i> <span>Account</span> <i class="far fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                <?php if(check_user_previlege('p31')):?>
                <li><a href="<?php echo base_url() . 'index.php/private_management/credit_balance' ?>"><i class="far fa-circle"></i> Credit Balance</a></li>
                <?php endif; if(check_user_previlege('p32')):?>    
                <li><a href="<?php echo base_url() . 'index.php/private_management/debit_balance' ?>"><i class="far fa-circle"></i> Debit Balance</a></li>
                <?php endif; ?>
                </ul>
            </li>
            <li class="treeview">
                <a href="#">
                    <i class="far fa-money-bill"></i> <span>Ultralux  Account</span> <i class="far fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                <?php if(check_user_previlege('p31')):?>
                <li><a href="<?php echo base_url() . 'index.php/private_management/ultra_credit_balance' ?>"><i class="far fa-circle"></i> Credit Balance</a></li>
                <?php endif; if(check_user_previlege('p32')):?>    
                <li><a href="<?php echo base_url() . 'index.php/private_management/ultra_debit_balance' ?>"><i class="far fa-circle"></i> Debit Balance</a></li>
                <?php endif; ?>
                </ul>
            </li>
            <?php endif;
            if ($b2b) { if(check_user_previlege('p6')): ?>
                <li class="treeview">
                    <a href="#">
                        <i class="far fa-briefcase"></i> <span>Commission</span> <i class="far fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                    <?php if(check_user_previlege('p33')): ?>
                        <li><a href="<?php echo base_url() . 'index.php/management/agent_commission?default_commission=' . ACTIVE; ?>"><i class="far fa-circle"></i> Default Commission</a></li>
                    <?php endif; if(check_user_previlege('p34')): ?>
                        <li><a href="<?php echo base_url() . 'index.php/management/agent_commission' ?>"><i class="far fa-circle"></i> Agent's Commission</a></li>
                    <?php endif; ?>
                    </ul>
                </li>
                 <li class="treeview">
                    <a href="#">
                        <i class="far fa-briefcase"></i> <span>Ultra Commission</span> <i class="far fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                    <?php if(check_user_previlege('p33')): ?>
                        <li><a href="<?php echo base_url() . 'index.php/management/ultra_agent_commission?default_commission=' . ACTIVE; ?>"><i class="far fa-circle"></i> Default Commission</a></li>
                    <?php endif; if(check_user_previlege('p34')): ?>
                        <li><a href="<?php echo base_url() . 'index.php/management/ultra_agent_commission' ?>"><i class="far fa-circle"></i> Agent's Commission</a></li>
                    <?php endif; ?>
                    </ul>
                </li>
        <?php endif; } if(check_user_previlege('p7')):?>
            <li class="treeview">
                <a href="#">
                    <i class="far fa-plus-square"></i> 
                    <span> Markup </span><i class="far fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <!-- Markup TYPES -->
        <?php  if ($b2c) { if(check_user_previlege('p35')): ?>
                        <li><a href="#"><i class="far fa-circle"></i> B2C</a>
                            <ul class="treeview-menu">
            <?php if ($airline_module) { if(check_user_previlege('p35')):?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2c_airline_markup/'; ?>"><i class="<?= get_arrangement_icon(META_AIRLINE_COURSE) ?>"></i> Flight</a></li>
                                <?php endif; } ?>
                                <?php if ($accomodation_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2c_hotel_markup/'; ?>"><i class="<?= get_arrangement_icon(META_ACCOMODATION_COURSE) ?>"></i> Hotel</a></li>
                                <?php endif; } ?>
                                <?php if ($bus_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2c_bus_markup/'; ?>"><i class="<?= get_arrangement_icon(META_BUS_COURSE) ?>"></i> Bus</a></li>
                                <?php endif; } ?>

                                <?php if ($transferv1_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2c_transfer_markup/'; ?>"><i class="<?= get_arrangement_icon(META_TRANSFERV1_COURSE) ?>"></i> Transfers</a></li>

                                <?php endif; }
                                ?>


                                <?php if ($sightseen_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2c_sightseeing_markup/'; ?>"><i class="<?= get_arrangement_icon(META_SIGHTSEEING_COURSE) ?>"></i> Activities</a></li>

                                <?php endif; }
                                ?>
            <?php if ($car_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2c_car_markup/'; ?>"><i class="<?= get_arrangement_icon(META_CAR_COURSE) ?>"></i> Car</a></li>

                                <?php endif; }
                                ?>
                            </ul>
                        </li>
                        <?php endif; 
                    }
                    if ($b2b) { if(check_user_previlege('p36')):
                                ?>
                        <li><a href="#"><i class="far fa-circle"></i> B2B</a>
                            <ul class="treeview-menu">
                        <?php if ($airline_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2b_airline_markup/'; ?>"><i class="<?= get_arrangement_icon(META_AIRLINE_COURSE) ?>"></i> Flight</a></li>
                            <?php endif; } ?>
                                <?php if ($accomodation_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2b_hotel_markup/'; ?>"><i class="<?= get_arrangement_icon(META_ACCOMODATION_COURSE) ?>"></i> Hotel</a></li>
                                <?php endif; } ?>
                                <?php if ($bus_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2b_bus_markup/'; ?>"><i class="<?= get_arrangement_icon(META_BUS_COURSE) ?>"></i> Bus</a></li>
                                <?php endif; } ?>

                                <?php if ($transferv1_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2b_transfer_markup/'; ?>"><i class="<?= get_arrangement_icon(META_TRANSFERV1_COURSE) ?>"></i>Transfers</a></li>
                                <?php endif; } ?>


                                <?php if ($sightseen_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2b_sightseeing_markup/'; ?>"><i class="<?= get_arrangement_icon(META_SIGHTSEEING_COURSE) ?>"></i> Activities</a></li>

                                <?php endif; }
                                ?>
                                <?php if ($car_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2b_car_markup/'; ?>"><i class="<?= get_arrangement_icon(META_CAR_COURSE) ?>"></i> Car</a></li>

                                <?php endif; }
                                ?>
                            </ul>
                        </li>
        <?php endif; } 

 if (true) { if(check_user_previlege('p36')):
                                ?>
                        <li><a href="#"><i class="far fa-circle"></i>Ultralux</a>
                            <ul class="treeview-menu">
                        <?php if ($airline_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/ultra_airline_markup/'; ?>"><i class="<?= get_arrangement_icon(META_AIRLINE_COURSE) ?>"></i> Flight</a></li>
                            <?php endif; } ?>
                                <?php if ($accomodation_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/ultra_hotel_markup/'; ?>"><i class="<?= get_arrangement_icon(META_ACCOMODATION_COURSE) ?>"></i> Hotel</a></li>
                                <?php endif; } ?>
                                <?php if ($bus_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2b_bus_markup/'; ?>"><i class="<?= get_arrangement_icon(META_BUS_COURSE) ?>"></i> Bus</a></li>
                                <?php endif; } ?>

                                <?php if ($transferv1_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2b_transfer_markup/'; ?>"><i class="<?= get_arrangement_icon(META_TRANSFERV1_COURSE) ?>"></i>Transfers</a></li>
                                <?php endif; } ?>


                                <?php if ($sightseen_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/b2b_sightseeing_markup/'; ?>"><i class="<?= get_arrangement_icon(META_SIGHTSEEING_COURSE) ?>"></i> Activities</a></li>

                                <?php endif; }
                                ?>
                                <?php if ($car_module) { if(check_user_previlege('p35')): ?>
                                    <li><a href="<?php echo base_url() . 'index.php/management/ultra_car_markup/'; ?>"><i class="<?= get_arrangement_icon(META_CAR_COURSE) ?>"></i> Car</a></li>

                                <?php endif; }
                                ?>
                            </ul>
                        </li>
        <?php endif; } 
        ?>





                </ul>
            </li>
            
            <?php endif;
            } if(check_user_previlege('p8')): ?>
        <li class="treeview">
            <a href="<?php echo base_url() . 'index.php/management/gst_master' ?>">
                <i class="fa fa-globe"></i> 
                <span> GST Master </span>
            </a>
        </li>
    <?php endif; 
    if ($b2b) { if(check_user_previlege('p9')):?>
            <li class="treeview">
                <a href="#">
                    <i class="far fa-money-bill"></i> 
                    <span> Master Balance Manager </span><i class="far fa-angle-left pull-right"></i>
                </a>
                <?php if(check_user_previlege('p37')): ?>
                <ul class="treeview-menu">
                    <!-- USER TYPES -->
                            <!--<li><a href="<?php echo base_url() . 'index.php/management/master_balance_manager' ?>"><i class="far fa-circle-o"></i> API</a></li>-->
                    <li><a href="<?php echo base_url() . 'index.php/management/b2b_balance_manager' ?>"><i class="far fa-circle"></i> B2B</a></li>
                </ul>
                 <?php endif; if(check_user_previlege('p38')): ?>
                <ul class="treeview-menu">
                    <li><a href="<?php echo base_url() . 'index.php/management/b2b_credit_request' ?>"><i class="far fa-circle"></i> B2B Credit Limt Requests</a></li>
                </ul> 
            <?php endif; ?>
            </li>

    <?php  endif; } 
     if ($b2b) { if(check_user_previlege('p9')):?>
            <li class="treeview">
                <a href="#">
                    <i class="far fa-money-bill"></i> 
                    <span> Ultralux Balance Manager </span><i class="far fa-angle-left pull-right"></i>
                </a>
                <?php if(check_user_previlege('p37')): ?>
                <ul class="treeview-menu">
                    <!-- USER TYPES -->
                            <!--<li><a href="<?php echo base_url() . 'index.php/management/master_balance_manager' ?>"><i class="far fa-circle-o"></i> API</a></li>-->
                    <li><a href="<?php echo base_url() . 'index.php/management/ultra_balance_manager' ?>"><i class="far fa-circle"></i> B2B</a></li>
                </ul>
                 <?php endif; if(check_user_previlege('p38')): ?>
                <ul class="treeview-menu">
                    <li><a href="<?php echo base_url() . 'index.php/management/ultra_credit_request' ?>"><i class="far fa-circle"></i> ultralux Credit Limt Requests</a></li>
                </ul> 
            <?php endif; ?>
            </li>

    <?php  endif; } 
    if ($package_module) { if(check_user_previlege('p10')):?>
            <li class="treeview">
                <a href="#">
                    <i class="far fa-plus-square"></i> 
                    <span> Package Management </span><i class="far fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <!-- USER TYPES -->

                    <?php if(check_user_previlege('p39')): ?>
                        <li>
                        <a href="<?php echo base_url() . 'index.php/supplier/view_packages_types' ?>"><i class="far fa-circle"></i> View Package Types </a>
                        </li>
                    <?php endif; if(check_user_previlege('p40')): ?>
                    
                    <li>
                    <a href="<?php echo base_url() . 'index.php/supplier/add_with_price' ?>"><i class="far fa-circle"></i> Add New Package </a>
                    </li>
                    <?php endif; if(check_user_previlege('p41')):?>
                    <li><a href="<?php echo base_url() . 'index.php/supplier/view_with_price' ?>"><i class="far fa-circle"></i> View Packages </a></li>
                    <?php endif; if(check_user_previlege('p42')):?>
                    <li><a href="<?php echo base_url() . 'index.php/supplier/enquiries' ?>"><i class="far fa-circle"></i> View Packages Enquiries </a></li>
                    <?php endif; ?>
                </ul>
            </li>
    <?php endif; } if(check_user_previlege('p11')):?>
        <li class="treeview">
            <a href="#">
                <i class="far fa-envelope"></i> 
                <span> Email Subscriptions </span><i class="far fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <!-- USER TYPES -->
                <li><a href="<?php echo base_url() . 'index.php/general/view_subscribed_emails' ?>"><i class="far fa-circle"></i> View Emails </a></li>
                <!-- <li><a href="<?php echo base_url() . 'index.php/supplier/add_with_price' ?>"><i class="far fa-circle"></i> Add New Package </a></li>
                <li><a href="<?php echo base_url() . 'index.php/supplier/view_with_price' ?>"><i class="far fa-circle"></i> View Packages </a></li>
                <li><a href="<?php echo base_url() . 'index.php/supplier/enquiries' ?>"><i class="far fa-circle"></i> View Packages Enquiries </a></li> -->
            </ul>
        </li>
<?php endif; } if(check_user_previlege('p13')): ?>
    <li class="treeview">
        <a href="#">
            <i class="far fa-laptop"></i>
            <span>CMS</span><i class="far fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
        <?php if(check_user_previlege('p44')): ?>
        <li class="hide"><a href="<?php echo base_url() . 'index.php/user/banner_images' ?>"><i class="far fa-image"></i> <span>Main Banner Image</span></a></li>
        <?php endif; if(check_user_previlege('p45')):?>
        <li><a href="<?php echo base_url() . 'index.php/cms/add_cms_page' ?>"><i class="far fa-file-alt"></i> <span>Static Page content</span></a></li>

            <!-- Top Destinations START -->
<?php endif; ?>
                <li><a href="<?php echo base_url() . 'index.php/cms/things_to_do_ultralux' ?>"><i class="far fa-file-alt"></i> <span>Ultralux Things to do</span></a></li>
			   <li><a href="<?php echo base_url() . 'index.php/cms/hotel_partners' ?>"><i class="far fa-file-alt"></i> <span>Hotel Partners</span></a></li>
			  <?php if ($airline_module) { if(check_user_previlege('p46')):?>
                <li class="hide"><a href="<?php echo base_url() . 'index.php/cms/flight_top_destinations' ?>"><i class="far fa-plane"></i> <span>Flight Top Destinations</span></a></li>
<?php endif; } ?>
<?php if ($accomodation_module) { if(check_user_previlege('p47')): ?>
                <li class="hide"><a href="<?php echo base_url() . 'index.php/cms/hotel_top_destinations' ?>"><i class="fas fa-bed"></i> <span>Hotel Top Destinations</span></a></li>
            <?php endif; } ?>
            <?php if ($bus_module) { if(check_user_previlege('p51')): ?>
                <li class="hide"><a href="<?php echo base_url() . 'index.php/cms/bus_top_destinations' ?>"><i class="far fa-bus"></i> <span>Bus Top Destinations</span></a></li>
            <?php endif; } if(check_user_previlege('p52')):?>
            <li class="hide"><a href="<?php echo base_url() . 'index.php/cms/home_page_headings' ?>"><i class="far fa-book"></i> <span>Home Page Headings</span></a></li>
            <?php endif; if(check_user_previlege('p53')):?>
            <li class="hide"><a href="<?php echo base_url() . 'index.php/cms/why_choose_us' ?>"><i class="far fa-question"></i> <span>Why Choose Us</span></a></li>
            <?php endif; if(check_user_previlege('p54')):?>
            <li class="hide"><a href="<?php echo base_url() . 'index.php/cms/top_airlines' ?>"><i class="far fa-plane"></i> <span>Top Airlines</span></a></li>
            <?php endif; if(check_user_previlege('p55')):?>
            <li class="hide"><a href="<?php echo base_url() . 'index.php/cms/tour_styles' ?>"><i class="far fa-binoculars"></i> <span>Tour Styles</span></a></li>
            <?php endif; if(check_user_previlege('p64')):?>
            <li class=""><a href="<?php echo base_url() . 'index.php/cms/add_contact_address' ?>"><i class="far fa-address-card"></i> <span>Contact Address</span></a></li>
            <?php endif; ?>
            <?php if(check_user_previlege('p57')):?>
            <li class=""><a href="<?php echo base_url() . 'index.php/cms/add_contact_address' ?>"><i class="far fa-address-card"></i> <span>Voucher Terms & Conditions</span></a></li>
            <?php endif; ?>
            <!-- Top Destinations END -->
        </ul>
    </li>
<?php endif; if(check_user_previlege('p12')):?>
    <li class="treeview">
        <a href="<?php echo base_url() . 'index.php/cms/seo' ?>" >
            <i class="fa fa-university"></i>
            <span>SEO</span> <i class="fa fa-angle-left pull-right"></i>
        </a>
    </li>
<?php endif; if(check_user_previlege('p15')):?>
    <li class="treeview">
        <a href="<?php echo base_url() . 'index.php/management/bank_account_details' ?>">
            <i class="far fa-university"></i> <span>Bank Account Details</span> </a>
    </li>
<?php endif; if(check_user_previlege('p15')):?>

<li class="treeview">
            <a href="<?php echo base_url().'index.php/general/email_configuration'?>">
            <i class="far fa-envelope"></i> <span>Email Configuration</span> </a>
    </li>
<?php endif; ?>
    <!-- 
    <li class="treeview">
                    <a href="<?php //echo base_url().'index.php/utilities/deal_sheets' ?>">
                            <i class="far fa-hand-o-right "></i> <span>Deal Sheets</span>
                    </a>
    </li>
    -->
    <li class="treeview">
                    <a href="#" data-toggle="tooltip" data-placement="top" title="Hotel CRS"> <i class="fa fa-bed"></i> <span>
                            Hotel CRS </span><i class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                     <li>
                            <a href="<?php echo base_url() . 'index.php/eco_stays' ?>">
                                <i class="fas fa-circle-notch"></i>Hotel- List & Room Allocation
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo base_url() . 'index.php/eco_stays/types' ?>">
                                <i class="fas fa-circle-notch"></i>Hotel-Type
                            </a>
                        </li> 

                        <li>
                            <a href="<?php echo base_url() . 'index.php/eco_stays/room_types' ?>">
                                <i class="fas fa-circle-notch"></i>Room Type
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo base_url() . 'index.php/eco_stays/board_types' ?>">
                                <i class="fas fa-circle-notch"></i>Board Types
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo base_url() . 'index.php/eco_stays/eco_stays_amenities' ?>">
                                <i class="fas fa-circle-notch"></i>Hotel- Amenities types
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo base_url() . 'index.php/eco_stays/eco_stays_room_amenities' ?>">
                                <i class="fas fa-circle-notch"></i>Room Amenities Types
                            </a>
                        </li>

                       

                    </ul>
                </li>
                    <?php if (check_user_previlege('p36')): ?>
        <li class="treeview">
            <a href="#"> <i class="fa fa-plane"></i> <span>Flight CRS </span><i class="fa fa-angle-left pull-right"></i> </a>
            <ul class="treeview-menu">
                <li class="treeview">
                    <a href="#"><i class="fa fa-lists"></i><span>Flight Management </span><i class="fa fa-angle-left pull-right"></i> </a>
                    <ul class="treeview-menu">
                        <?php if (check_user_previlege('p37')): ?>
                            <li><a href="<?php echo base_url() . 'index.php/flight/add_flight' ?>"><i class="fa fa-credit-card"></i> <span>Add Flight</span></a></li>
                        <?php endif; ?>
                        <?php if (check_user_previlege('p38')): ?>
                            <li><a href="<?php echo base_url() . 'index.php/flight/flight_list' ?>"><i class="fa fa-credit-card"></i> <span>Charter Lists</span></a></li>
                        <?php endif; ?>
                      <!--   <?php if (check_user_previlege('p38')): ?>
                            <li><a href="<?php echo base_url() . 'index.php/flight/empty_list' ?>"><i class="fa fa-credit-card"></i> <span>Empty Leg Lists</span></a></li>
                        <?php endif; ?> -->
                        <?php if (check_user_previlege('p40')): ?>
                            <li><a href="<?php echo base_url() . 'index.php/flight/flight_crs_airline_list' ?>"><i class="fa fa-credit-card"></i> <span> Airline List</span></a></li>

                        <?php endif; ?>
                        <?php if (check_user_previlege('p39')): ?>
                            <li><a href="<?php echo base_url() . 'index.php/flight/flight_general_terms' ?>"><i class="fa fa-credit-card"></i> <span>General Terms </span></a></li>
                            <li><a href="<?php echo base_url() . 'index.php/flight/flight_fare_rules' ?>"><i class="fa fa-credit-card"></i> <span>Flight Fare Rules</span></a></li>
                            <li><a href="<?php echo base_url() . 'index.php/flight/baggage_fare_rules' ?>"><i class="fa fa-suitcase"></i> <span>Baggage Rules</span></a></li>
                            <li><a href="<?php echo base_url() . 'index.php/flight/flight_meal_details' ?>"><i class="fa fa-cutlery"></i> <span>Flight Meal Details</span></a></li>
                        <?php endif; ?>
                    </ul>

                </li>
                <?php if (check_user_previlege('p82')): ?>
                    <li class="treeview">
                        <a href="#"> <i class="fa fa-plane"></i> <span>Pilot Management </span><i class="fa fa-angle-left pull-right"></i> </a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo base_url() . 'index.php/flight/add_pilot' ?>"><i class="fa fa-credit-card"></i> <span> Add Pilot</span></a></li>
                            <li><a href="<?php echo base_url() . 'index.php/flight/pilot_list' ?>"><i class="fa fa-credit-card"></i> <span>Pilot List</span></a></li>
                        </ul>
                    </li> 
                <?php endif; ?>
                <?php if (check_user_previlege('p70')): ?>
                    <li class="treeview ">
                        <a href="#"> <i class="fa fa-plane"></i> <span>Aircraft Management </span><i class="fa fa-angle-left pull-right"></i> </a>
                        <ul class="treeview-menu">
                            <?php if (check_user_previlege('p79')): ?>
                                <li><a href="<?php echo base_url() . 'index.php/flight/add_aircraft' ?>"><i class="fa fa-credit-card"></i> <span> Add Aircraft</span></a></li>
                            <?php endif; ?>
                            <?php if (check_user_previlege('p80')): ?>
                                <li><a href="<?php echo base_url() . 'index.php/flight/aircraft_list' ?>"><i class="fa fa-credit-card"></i> <span> Aircraft List</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <li class="treeview hide ">
                    <a href="#"><i class="fa fa-lists"></i><span>Flight Settings </span><i class="fa fa-angle-left pull-right"></i> </a>
                    <ul class="treeview-menu">
                        <?php if (check_user_previlege('p41')): ?>
                            <li><a href="<?php echo base_url() . 'index.php/flight/flight_crs_tax_list' ?>"><i class="fa fa-credit-card"></i> <span> Tax List</span></a></li>
                        <?php endif; ?>

                        <?php if (check_user_previlege('p64')): ?>
                             
                        <?php endif; ?>
                        <?php if (check_user_previlege('p42')): ?>

                             <li><a href="<?php echo base_url() . 'index.php/flight/journey_log' ?>"><i class="fa fa-credit-card"></i> <span> Add Journey Log</span></a></li> 

                        <?php endif; ?>
                        <?php if (check_user_previlege('p43')): ?>
                             <li><a href="<?php echo base_url() . 'index.php/flight/flight_time_duty' ?>"><i class="fa fa-credit-card"></i> <span> FDTL</span></a></li>

                        <?php endif; ?>
                        <?php if (check_user_previlege('p44')): ?>
                            <li><a href="<?php echo base_url() . 'index.php/flight/add_sid' ?>"><i class="fa fa-credit-card"></i> <span> Add SID</span></a></li> 

                        <?php endif; ?>
                        <?php if (check_user_previlege('p45')): ?>
                            <li><a href="<?php echo base_url() . 'index.php/flight/home_base' ?>"><i class="fa fa-credit-card"></i> <span> Add Home Base Airport</span></a></li>
                        <?php endif; ?>
                        <?php if (check_user_previlege('p45')): ?>
                            <li><a href="<?php echo base_url() . 'index.php/flight/api_city_list' ?>"><i class="fa fa-credit-card"></i> <span> City List</span></a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo base_url() . 'index.php/flight/view_enquiry' ?>"><i class="fa fa-credit-card"></i> <span> Privite Flight Enquiry</span></a></li>

                    </ul>
                </li>









                <?php if (check_user_previlege('p72')): ?>
                    <li class="treeview hide ">
                        <a href="#"> <i class="fa fa-plane"></i> <span>Component Management </span><i class="fa fa-angle-left pull-right"></i> </a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo base_url() . 'index.php/flight/add_component' ?>"><i class="fa fa-credit-card"></i> <span> Add Component</span></a></li>
                            <li><a href="<?php echo base_url() . 'index.php/flight/component_list' ?>"><i class="fa fa-credit-card"></i> <span> Component List</span></a></li>
                            <?php if (check_user_previlege('p78')): ?>
                                <li><a href="<?php echo base_url() . 'index.php/flight/component_type_list' ?>"><i class="fa fa-credit-card"></i> <span> Component Type List</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <!-- <?php if (check_user_previlege('p49')): ?>
            
            
            <li><a href="<?php //echo base_url() . 'index.php/flight/duty_type_list' ?>"><i class="fa fa-credit-card"></i> <span> Duty Type List</span></a></li>
            <?php endif; ?>
            <?php if (check_user_previlege('p50')): ?>
            <li><a href="<?php //echo base_url() . 'index.php/flight/leave_type_list' ?>"><i class="fa fa-credit-card"></i> <span> Leave Type List</span></a></li>
            <?php endif; ?>
            <?php if (check_user_previlege('p51')): ?>
            <li><a href="<?php // echo base_url() . 'index.php/flight/training_name_list' ?>"><i class="fa fa-credit-card"></i> <span> Training Name List</span></a></li>
            <?php endif; ?> -->
                <?php if (check_user_previlege('p76')): ?>
                  <!--   <li><a href="<?php echo base_url() . 'index.php/flight/document_name_list' ?>"><i class="fa fa-credit-card"></i> <span>Aircraft Document Type List</span></a></li> -->
                <?php endif; ?>
                <!--    <?php if (check_user_previlege('p53')): ?>
            <li><a href="<?php //echo base_url() . 'index.php/flight/licence_name_list' ?>"><i class="fa fa-credit-card"></i> <span> Licence Type List</span></a></li>
            <?php endif; ?> -->

            </ul>
        </li>
    <?php endif; ?>
    <?php if(check_user_previlege('p16')): ?>
    <li class="treeview">
        <a href="#">
            <i class="far fa-cogs"></i> 
            <span> Settings </span><i class="far fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
            <?php if(check_user_previlege('p58')): ?>
            <li class="hide">
                <a href="<?php echo base_url() . 'index.php/utilities/insurance_fees' ?>"><i class="far fa-credit-card"></i>Travel Insurance</a>
            </li>
        <?php endif; if(check_user_previlege('p59')):?>
            <li>    
                <a href="<?php echo base_url() . 'index.php/utilities/convenience_fees' ?>"><i class="far fa-credit-card"></i>Convenience Fees</a>
            </li>
        <?php endif; if(check_user_previlege('p60')):?>

            <li>
                <a href="<?php echo base_url() . 'index.php/utilities/manage_promo_code' ?>"><i class="far fa-tag"></i>Promo Code</a>
            </li>
        <?php endif; ?>
			 <li class="">
                <a href="<?php echo base_url() . 'index.php/module/module_management' ?>"><i class="far fa-database"></i> Manage Module</a>
            </li>
             <li class="">
                <a href="<?php echo base_url() . 'index.php/module/booking_source_management' ?>"><i class="far fa-database"></i> Manage Supplier</a>
            </li>
			  <li class="">
                <a href="<?php echo base_url() . 'index.php/utilities/convenience_fees_text' ?>"><i class="far fa-database"></i> Convenience Fees Text</a>
            </li>
			<?php if(check_user_previlege('p61')):?>

            <li class="hide">
                <a href="<?php echo base_url() . 'index.php/utilities/manage_source' ?>"><i class="far fa-database"></i> Manage API</a>
            </li>
        <?php endif;  if(check_user_previlege('p61')): ?>
            <li>
                <a href="<?php echo base_url() . 'index.php/utilities/sms_checkpoint' ?>"><i class="far fa-envelope"></i> Manage SMS</a>
            </li>

<?php endif; if (is_domain_user() == false) { // ACCESS TO ONLY PROVAB ADMIN  ?>
                <li>
                    <a href="<?php echo base_url() . 'index.php/utilities/module' ?>"><i class="far fa-circle"></i> <span>Manage Modules</span>
                    </a>
                </li>
            <?php } if(check_user_previlege('p62')):?>

            <li>
                <a href="<?php echo base_url() . 'index.php/utilities/currency_converter' ?>"><i class="fas fa-rupee-sign"></i> Currency Conversion </a>
            </li>
        <?php endif; if(check_user_previlege('p65')):?>
            <li>
                <a href="<?php echo base_url() . 'index.php/management/event_logs' ?>"><i class="far fa-shield"></i> <span> Event Logs </span></a>
            </li>
        <?php endif; if(check_user_previlege('p66')):?>
            <li>
                <a href="<?php echo base_url() . 'index.php/utilities/app_settings' ?>"><i class="far fa-laptop"></i> Appearance </a>
            </li>
        <?php endif; if(check_user_previlege('p67')): ?>
            <li>
                <a href="<?php echo base_url() . 'index.php/utilities/social_network' ?>"><i class="fab fa-facebook-square"></i> Social Networks </a>
            </li>
        <?php endif; if(check_user_previlege('p68')):?>
            <li>
                <a href="<?php echo base_url() . 'index.php/utilities/social_login' ?>"><i class="fab fa-facebook-f"></i> Social Login </a>
            </li>
<?php endif;if(check_user_previlege('p69')): ?>
            <li>
                <a href="<?php echo base_url() . 'index.php/user/manage_domain' ?>">
                    <i class="far fa-image"></i> <span>Manage Domain</span>
                </a>
            </li>
<?php endif; if(check_user_previlege('p70')):?>
            <li>
                <a href="<?php echo base_url() ?>index.php/utilities/timeline"><i class="far fa-desktop"></i> <span>Live Events</span></a>
            </li>
<?php endif; ?>
            <!-- <li>
                    <a href="<?= base_url() . 'index.php/utilities/trip_calendar' ?>"><i class="far fa-calendar"></i> <span>Trip Calendar</span></a>
</li> -->			
        </ul>
    </li>
    <?php endif; ?>

</ul>
