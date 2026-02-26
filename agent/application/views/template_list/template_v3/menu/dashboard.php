<style>
    .fc-day-number {
        font-size: inherit;
        font-weight: inherit;
        padding-right: 10px;
    }
</style>
<?php
$active_domain_modules = $this->active_domain_modules;
$tiny_loader = $GLOBALS['CI']->template->template_images('tiny_loader_v1.gif');
$tiny_loader_img = '<img src="' . $tiny_loader . '" class="loader-img" alt="Loading">';
$booking_summary = array();
?>
<div class="container-fluid nopad">
    <div class="row row_bookings_section">
        <div class="clearfix"></div>
        <div class="org_row">
        <div class="col-md-9 col-xs-12">
        <div class="dsh_in">
            <!-- <h3>Dashboard</h3> -->
            <button type="button" class="btn btn-default"><i class="fal fa-calendar-alt"></i> This Month <i class="fa fa-angle-down"></i></button>
        </div>
        <div class="dsh_bk">
        <div class="tab">
          <button class="tablinks" onclick="openCity(event, 'London')" id="defaultOpen"><span>Upcoming Reservation</span></button>
          <button class="tablinks" onclick="openCity(event, 'Paris')"><span>Completed Bookings</span></button>
        </div>
        <div id="London" class="tabcontent">
        <div class="org_row">
        <?php if (is_active_airline_module()) { ?>
            <div class="col-md-4 col-sm-6 col-xs-6">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-text">Flights</span>
                        <span class="info-box-number <?= META_AIRLINE_COURSE ?>"><?= $flight_booking_count ?></span>
                        <span class="chrt"><img src="<?=$GLOBALS['CI']->template->template_images('chart.svg')?>" alt="increasee"> 1.24%</span>
                        <a href="<?= base_url() ?>index.php/report/flight" class=""><i class="far fa-ellipsis-h"></i>
                        </a>
                    </div><!-- /.info-box-content -->
                    <span class="info-box-icon flight-l-bg"><img src="<?=$GLOBALS['CI']->template->template_images('flt1.svg')?>" alt="flight"></span>
                </div><!-- /.info-box -->
            </div>
        <?php } ?>
        <?php if (is_active_hotel_module()) { ?>
            <div class="col-md-4 col-sm-6 col-xs-6">
                <div class="info-box htl">
                    <div class="info-box-content">
                        <span class="info-box-text">Hotels</span>
                        <span class="info-box-number <?= META_ACCOMODATION_COURSE ?>"><?= $hotel_booking_count ?></span>
                        <span class="chrt"><img src="<?=$GLOBALS['CI']->template->template_images('chart.svg')?>" alt="increasee"> 1.24%</span>
                        <a href="<?= base_url() ?>index.php/report/hotel" class=""><i class="far fa-ellipsis-h"></i>
                        </a>
                    </div><!-- /.info-box-content -->
                    <span class="info-box-icon hotel-l-bg"><img src="<?=$GLOBALS['CI']->template->template_images('htl1.svg')?>" alt="hotel"></span>
                </div><!-- /.info-box -->
            </div>
        <?php } ?>
        <?php if (is_active_bus_module()) { ?>
            <div class="col-md-4 col-sm-6 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bus-l-bg"><i class="fa fa-bus"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Bus Booking</span>
                        <span class="info-box-number <?= META_BUS_COURSE ?>"><?= $bus_booking_count ?></span>
                        <a href="<?= base_url() ?>index.php/report/bus" class=""><i class="far fa-ellipsis-h"></i>
                        </a>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div>

        <?php } ?>
        <?php if (is_active_transferv1_module()) { ?>
            <div class="col-md-4 col-sm-6 col-xs-6">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-text">Transfers</span>
                        <span class="info-box-number <?= META_TRANSFERV1_COURSE ?>"><?= @$transfer_booking_count ?></span>

                        <a target="_blank"  href="<?= base_url() ?>index.php/report/transfers" class=""><i class="far fa-ellipsis-h"></i>
                        </a>
                    </div><!-- /.info-box-content -->
                    <span class="info-box-icon bg-yellow"><img src="<?=$GLOBALS['CI']->template->template_images('car1.svg')?>" alt="car"></span>
                </div><!-- /.info-box -->
            </div>
        <?php } ?>
        <?php if (is_active_sightseeing_module()) { ?>
            <div class="col-md-4 col-sm-6 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-navy"><i class="fa fa-binoculars"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Activities</span>
                        <span class="info-box-number <?= META_SIGHTSEEING_COURSE ?>"><?= $sightseeing_booking_count ?></span>

                        <a target="_blank"  href="<?= base_url() ?>index.php/report/activities" class=""><i class="far fa-ellipsis-h"></i>
                        </a>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div>
        <?php } ?>

        <?php if (is_active_package_module()) { ?>
            <div class="col-md-4 col-sm-6 col-xs-6">
                <div class="info-box hldy">
                    <div class="info-box-content">
                        <span class="info-box-text">Holidays</span>
                        <span class="info-box-number">0</span>
                        <span class="chrt"><img src="<?=$GLOBALS['CI']->template->template_images('chart.svg')?>" alt="increasee"> 1.24%</span>
                        <a href="<?= base_url() ?>index.php/report/package_enquiries" class=""><i class="far fa-ellipsis-h"></i>
                        </a>
                    </div><!-- /.info-box-content -->
                    <span class="info-box-icon bg-maroon"><img src="<?=$GLOBALS['CI']->template->template_images('hldy1.svg')?>" alt="holiday"></span>
                </div><!-- /.info-box -->
            </div>
        <?php } ?>

        <?php if (is_active_car_module()) { ?>
            <div class="col-md-4 col-sm-6 col-xs-6">
                <div class="info-box car">
                    <div class="info-box-content">
                        <span class="info-box-text">Cars</span>
                        <span class="info-box-number <?= META_CAR_COURSE ?>"><?= $car_booking_count ?></span>
                        <span class="chrt"><img src="<?=$GLOBALS['CI']->template->template_images('chart.svg')?>" alt="increasee"> 1.24%</span>
                        <a href="<?= base_url() ?>index.php/report/car" class=""><i class="far fa-ellipsis-h"></i>
                        </a>
                    </div><!-- /.info-box-content -->
                    <span class="info-box-icon bg-teal"><img src="<?=$GLOBALS['CI']->template->template_images('car1.svg')?>" alt="car"></span>
                </div><!-- /.info-box -->
            </div>
        <?php } ?>
            <div class="col-md-4 col-sm-6 col-xs-6">
                <div class="info-box cruise">
                    <div class="info-box-content">
                        <span class="info-box-text">Cruises</span>
                        <span class="info-box-number">0</span>
                        <span class="chrt"><img src="<?=$GLOBALS['CI']->template->template_images('chart.svg')?>" alt="increasee"> 1.24%</span>
                        <a href="#" class=""><i class="far fa-ellipsis-h"></i>
                        </a>
                    </div><!-- /.info-box-content -->
                    <span class="info-box-icon bg-teal"><img src="<?=$GLOBALS['CI']->template->template_images('cruis1.svg')?>" alt="cruise"></span>
                </div><!-- /.info-box -->
            </div>
            <div class="col-md-4 col-sm-6 col-xs-6">
                <div class="info-box air">
                    <div class="info-box-content">
                        <span class="info-box-text">Air Charters</span>
                        <span class="info-box-number">0</span>
                        <span class="chrt"><img src="<?=$GLOBALS['CI']->template->template_images('chart.svg')?>" alt="increasee"> 1.24%</span>
                        <a href="#" class=""><i class="far fa-ellipsis-h"></i>
                        </a>
                    </div><!-- /.info-box-content -->
                    <span class="info-box-icon bg-teal"><img src="<?=$GLOBALS['CI']->template->template_images('air1.svg')?>" alt="cruise"></span>
                </div><!-- /.info-box -->
            </div>
        </div>
        </div>
        <div id="Paris" class="tabcontent">
        </div>
        </div>
    </div>
    <div class="col-md-3 col-xs-12 nopadL">
        <div class="things_do">
            <div class="container-fluid">
                <div class="org_row">
                <div class="col-xs-12 nopad">
                    <h3>Things To Do</h3>
                    <div class="thngs_in">
                        <ul>
                            <li class="t_water"><span>Lorem ipsum dolor sit amet consectetur. Odio eleifend cursus</span></li>
                            <li class="t_hldy"><span>Lorem ipsum dolor sit amet consectetur. Odio eleifend cursus</span></li>
                            <li class="t_sight"><span>Lorem ipsum dolor sit amet consectetur. Odio eleifend cursus</span></li>
                            <li class="t_sight"><span>Lorem ipsum dolor sit amet consectetur. Odio eleifend cursus</span></li>
                        </ul>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    </div>
</div>
<!-- Search Engine Start -->
<div class="clearfix"></div>
<?php echo $search_engine; ?>
<div class="clearfix"></div>
<!-- Search Engine End -->
<div class="panel panel-default nobrdr">
    <div class="panel-body nopad">
        <div class="row">
            <div class="col-md-6">
                <div id='booking-calendar' class="">
                </div>
            </div>
            <div class="col-md-6">
                <div id='booking-timeline' class="">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
    <div class="top_things">
        <div class="container-fluid nopad">
            <div class="org_row">
            <div class="col-xs-12">
                <h3>Top Things To Do</h3>
                <div class="to_do">
                  <ul class="nav nav-pills">
                    <li class="active"><a data-toggle="pill" href="#culture">Culture</a></li>
                    <li><a data-toggle="pill" href="#adventure">Adventure</a></li>
                    <li><a data-toggle="pill" href="#nature">Nature</a></li>
                    <li><a data-toggle="pill" href="#relaxation">Relaxation</a></li>
                    <li><a data-toggle="pill" href="#family">Family</a></li>
                  </ul>
                  <div class="tab-content">
                    <div id="culture" class="tab-pane fade in active">
                        <ul class="lst_img">
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_do1.jpg')?>" alt="img/jpg" /><span>Explore Unique Dining Experiences</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_do2.jpg')?>" alt="img/jpg" /><span>Explore Natural Landmarks</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_do3.jpg')?>" alt="img/jpg" /><span>Explore Luxurious Resorts</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_do1.jpg')?>" alt="img/jpg" /><span>Explore Unique Dining Experiences</span></li>
                        </ul>
                        <div class="clearfix"></div>
                        <div class="col-xs-12 text-center load_more">
                            <button type="button" class="btn btn-default">Load more</button>
                        </div>
                    </div>
                    <!-- <div id="adventure" class="tab-pane fade">
                    </div>
                    <div id="nature" class="tab-pane fade">
                    </div>
                    <div id="relaxation" class="tab-pane fade">
                    </div>
                    <div id="family" class="tab-pane fade">
                    </div> -->
                  </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-xs-12">
                <h3>Top Places To Go</h3>
                <div class="to_do">
                  <ul class="nav nav-pills">
                    <li class="active"><a data-toggle="pill" href="#culture">Popular</a></li>
                    <li><a data-toggle="pill" href="#adventure">Cities</a></li>
                    <li><a data-toggle="pill" href="#nature">States</a></li>
                    <li><a data-toggle="pill" href="#relaxation">Beaches</a></li>
                    <li><a data-toggle="pill" href="#family">Islands</a></li>
                    <li><a data-toggle="pill" href="#family">Resorts</a></li>
                    <li><a data-toggle="pill" href="#family">Mountains</a></li>
                  </ul>
                  <div class="tab-content">
                    <div id="culture" class="tab-pane fade in active">
                        <ul class="lst_img">
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_go1.jpg')?>" alt="img/jpg" /><span>Explore Australia's Landmarks</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_go2.jpg')?>" alt="img/jpg" /><span>Explore Beauty Of Europe</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_go3.jpg')?>" alt="img/jpg" /><span>Explore Cruise Destinations</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('to_go1.jpg')?>" alt="img/jpg" /><span>Explore Australia's Landmarks</span></li>
                        </ul>
                        <div class="clearfix"></div>
                        <div class="col-xs-12 text-center load_more">
                            <button type="button" class="btn btn-default">Load more</button>
                        </div>
                    </div>
                    <!-- <div id="adventure" class="tab-pane fade">
                    </div>
                    <div id="nature" class="tab-pane fade">
                    </div>
                    <div id="relaxation" class="tab-pane fade">
                    </div>
                    <div id="family" class="tab-pane fade">
                    </div> -->
                  </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-xs-12">
                <h3>Must Visit Restaurants</h3>
                <div class="to_do">
                  <ul class="nav nav-pills">
                    <li class="active"><a data-toggle="pill" href="#culture">Australian</a></li>
                    <li><a data-toggle="pill" href="#adventure">Italian</a></li>
                    <li><a data-toggle="pill" href="#nature">Thai</a></li>
                    <li><a data-toggle="pill" href="#relaxation">Chinese</a></li>
                    <li><a data-toggle="pill" href="#family">Indian</a></li>
                  </ul>
                  <div class="tab-content">
                    <div id="culture" class="tab-pane fade in active">
                        <ul class="lst_img">
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('rest1.jpg')?>" alt="img/jpg" /><span>Barbecue Snag Home</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('rest2.jpg')?>" alt="img/jpg" /><span>Lamington Corner</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('rest3.jpg')?>" alt="img/jpg" /><span>Sausage House</span></li>
                            <li><img src="<?=$GLOBALS['CI']->template->domain_images('rest1.jpg')?>" alt="img/jpg" /><span>Barbecue Snag Home</span></li>
                        </ul>
                        <div class="clearfix"></div>
                        <div class="col-xs-12 text-center load_more">
                            <button type="button" class="btn btn-default">Load more</button>
                        </div>
                    </div>
                   <!--  <div id="adventure" class="tab-pane fade">
                    </div>
                    <div id="nature" class="tab-pane fade">
                    </div>
                    <div id="relaxation" class="tab-pane fade">
                    </div>
                    <div id="family" class="tab-pane fade">
                    </div> -->
                  </div>
                </div>
            </div>
        </div>
        </div>
    </div>
    <div class="clearfix"></div>
<?php if (false) { ?>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <div id='booking-summary' class="col-md-12">
                </div>
            </div>
        </div>
    </div>
</div>
<hr>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                Notification!!!
            </div>
            <?php
            $latest_trans_list = '';
            $latest_trans_summary = '';
            if (valid_array($latest_transaction)) {
                // debug($latest_transaction);exit;
                foreach ($latest_transaction as $k => $v) {
                    $latest_trans_list .= '<li class="item">';
                    $latest_trans_list .= '<div class="product-img image"><i class="' . get_arrangement_icon(module_name_to_id($v['transaction_type'])) . '"></i></div>';
                    $latest_trans_list .= '<div class="product-info">
                                    <a class="product-title" href="' . base_url() . 'index.php/transaction/logs?app_reference=' . trim($v['app_reference']) . '">
                                        ' . $v['app_reference'] . ' -' . app_friendly_day($v['created_datetime']) . ' <span class="label label-primary pull-right"><i class="fa fa-inr"></i> ' . ($v['grand_total']) . '</span>
                                    </a>
                                    <span class="product-description">
                                        ' . $v['remarks'] . '
                                    </span>
                                </div>';
                    $latest_trans_list .= '</li>';
                }
            }
            ?>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Recent Booking Transactions</h3>
                    </div>
                    <div class="box-body">
                        <ul class="products-list product-list-in-box">
                            <?= $latest_trans_list ?>
                        </ul>
                    </div>
                    <div class="box-footer text-center">
                        <a class="uppercase" href="<?= base_url() . 'index.php/transaction/logs' ?>">View All Transactions</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<?php
Js_Loader::$css[] = array('href' => SYSTEM_RESOURCE_LIBRARY . '/fullcalendar/fullcalendar.css', 'media' => 'screen');
Js_Loader::$css[] = array('href' => SYSTEM_RESOURCE_LIBRARY . '/fullcalendar/fullcalendar.print.css', 'media' => 'print');
Js_Loader::$js[] = array('src' => SYSTEM_RESOURCE_LIBRARY . '/fullcalendar/lib/moment.min.js', 'defer' => 'defer');
Js_Loader::$js[] = array('src' => SYSTEM_RESOURCE_LIBRARY . '/fullcalendar/fullcalendar.min.js', 'defer' => 'defer');
Js_Loader::$js[] = array('src' => SYSTEM_RESOURCE_LIBRARY . '/Highcharts/js/highcharts.js', 'defer' => 'defer');
Js_Loader::$js[] = array('src' => SYSTEM_RESOURCE_LIBRARY . '/Highcharts/js/modules/exporting.js', 'defer' => 'defer');
?>
<script>
function openCity(evt, cityName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " active";
}

// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();
</script>
<script>
    $(function () {
    //LEAD REPORT -line graph
    $('#booking-timeline').highcharts({
    credits: {
        enabled: false
    },
    chart: {
        type: 'spline'
    },
    title: {
        text: 'Booking Details',
        x: -20 //center
    },
    subtitle: {
        text: '',
        x: -20
    },
    xAxis: {
        categories: <?= json_encode($time_line_interval); ?>,
        tickPixelInterval: 0
    },
    yAxis: {
        allowDecimals: false,
        min: 0,
        max: <?php echo $max_count; ?>,
        title: {
            text: 'No Of Booking'
        },
        plotLines: [{
            value: 0,
            width: 1,
            color: '#808080'
        }]
    },
    tooltip: {
        valueSuffix: ''
    },
    legend: {
        title: {
            text: 'No Of Booking'
        },
        subtitle: {
            text: 'count'
        },
        layout: 'vertical',
        align: 'right',
        verticalAlign: 'middle',
        borderWidth: 0,
        labelFormatter: function() {
            var total = 0;
            var total_face_value = this.userOptions.total_earned || 0;
            for (var i = this.yData.length; i--; ) {
                total += this.yData[i];
            }
            return this.name + '(' + total + ')';
        }
    },
    series: <?= json_encode(array_values($time_line_report)); ?>,
    navigation: {
        buttonOptions: {
            align: 'right',
            verticalAlign: 'top',
            x: 0,
            y: 0,
            enabled: false // Disable the buttons
        }
    }
});

    $('#booking-summary').highcharts({
    title: {
    text: 'Monthly Recap Report'
    },
            xAxis: {
            categories: <?= json_encode($time_line_interval); ?>
            },
            yAxis: {
            allowDecimals: false,
                    title: {
                    text: 'Profit In <?= COURSE_LIST_DEFAULT_CURRENCY_VALUE ?>'
                    }
            },
            labels: {
            items: [{
            html: 'Total Profit Earned in <?= COURSE_LIST_DEFAULT_CURRENCY_VALUE ?>',
                    style: {
                    left: '50px',
                            top: '18px',
                            color: (Highcharts.theme && Highcharts.theme.textColor) || 'black'
                    }
            }]
            },
            series: [<?= (isset($group_time_line_report[0]) ? json_encode($group_time_line_report[0]) . ',' : ''); ?>
<?= (isset($group_time_line_report[1]) ? json_encode($group_time_line_report[1]) . ',' : ''); ?>
<?= (isset($group_time_line_report[2]) ? json_encode($group_time_line_report[2]) . ',' : ''); ?>
<?= (isset($group_time_line_report[3]) ? json_encode($group_time_line_report[3]) . ',' : ''); ?>
<?= (isset($group_time_line_report[4]) ? json_encode($group_time_line_report[4]) . ',' : ''); ?>
<?= (isset($group_time_line_report[5]) ? json_encode($group_time_line_report[5]) . ',' : ''); ?>

            {
            type: 'pie',
                    name: 'Total Earning',
                    data: <?= json_encode($module_total_earning) ?>,
                    center: [100, 80],
                    size: 100,
                    showInLegend: false,
                    dataLabels: {
                    enabled: false
                    }
            }]
    });
    });
    $(document).ready(function() {

    var event_list = {};
    function enable_default_calendar_view()
    {
    load_calendar('');
    get_event_list();
    set_event_list();
    $('[data-toggle="tooltip"]').tooltip();
    }
    function reset_calendar()
    {
    $("#booking-calendar").fullCalendar('removeEvents');
    get_event_list();
    set_event_list();
    }
    //Reload Events
    setInterval(function(){
    reset_calendar();
    $('[data-toggle="tooltip"]').tooltip();
    }, <?php echo SCHEDULER_RELOAD_TIME_LIMIT; ?>);
    enable_default_calendar_view();
    //sets all the events
    function get_event_list()
    {
    set_booking_event_list();
    }
    //loads all the loaded events
    function set_event_list()
    {
    $("#booking-calendar").fullCalendar('addEventSource', event_list.booking_event_list);
    if ("booking_event_list" in event_list && event_list.booking_event_list.hasOwnProperty(0)) {
    //focus_date(event_list.booking_event_list[0]['start']);
    }
    }

    //getting the value of arrangment details
    function set_booking_event_list()
    {
    $.ajax({
    url:app_base_url + "index.php/ajax/booking_events",
            async:false,
            success:function(response){
            //console.log(response)
            event_list.booking_event_list = response.data;
            }
    });
    }

    //load default calendar with scheduled query
    function load_calendar(event_list)
    {
    $('#booking-calendar').fullCalendar({
    header: {
    center: 'title'
    },
            //defaultDate: '2014-11-12', 
            editable: false,
            eventLimit: false, // allow "more" link when too many events
            events: event_list,
            eventRender: function(event, element) {
            element.attr('data-toggle', 'tooltip');
            element.attr('data-placement', 'bottom');
            element.attr('title', event.tip);
            element.attr('id', event.optid);
            element.find('.fc-time').attr('class', "hide");
            element.attr('class', event.add_class + ' fc-day-grid-event fc-event fc-start fc-end');
            element.attr('href', event.href);
            element.attr('target', '_blank');
            element.css({'font-size':'10px', 'padding':'1px'});
            if (event.prepend_element) {
            element.prepend(event.prepend_element);
            }
            },
            eventDrop : function (event, delta) {
            event.end = event.end || event.start;
            if (event.start && event.end) {
            update_event_list(event.optid, event.start.format(), event.end.format());
            focus_date(event.start.format());
            } else {
            reset_calendar();
            }
            }
    });
    }
    function focus_date(date)
    {
    $('#booking-calendar').fullCalendar('gotoDate', date);
    }
    });
</script>
