<?php
$tab1 = " active ";
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
                            aria-controls="home" role="tab" data-toggle="tab"> Availability Calander </a></li>
                    <!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE END -->
                </ul>
            </div>
        </div>
        <div>
            <a role="button"
                href="<?php echo base_url() . 'index.php/eco_stays/rooms/' . $room_data['stays_origin']; ?>"
                class="btn btn-sm btn-primary pull-right">Back to Rooms List</a>
        </div>
        <!-- PANEL HEAD START -->
        <div class="panel-body">
            <!-- PANEL BODY START -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane <?php echo $tab1; ?>" id="fromList">
                    <div class="panel-body">

                        <!-- Calander will be here -->

                        <div id='booking-calendar' class=" room-availability-calender " style="width: 700px;">
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- PANEL BODY END -->
    </div>
    <!-- PANEL WRAP END -->
</div>
<?php

?>
<script>
    $(document).ready(function () {

        let event_list = <?=json_encode($room_availability_data)?>;

        //load default calendar
        $('#booking-calendar').fullCalendar({
            header: {
                center: 'title'
            },
            defaultView: 'month',
            events: event_list,
            aspectRatio: 1
        });
        $('#booking-calendar').fullCalendar('option', 'height', 550);
    });
</script>

<link defer href='<?php echo SYSTEM_RESOURCE_LIBRARY; ?>/fullcalendar/fullcalendar.css' rel='stylesheet' />
<link defer href='<?php echo SYSTEM_RESOURCE_LIBRARY; ?>/fullcalendar/fullcalendar.print.css' rel='stylesheet'
    media='print' />
<script defer src='<?php echo SYSTEM_RESOURCE_LIBRARY; ?>/fullcalendar/lib/moment.min.js'></script>
<script defer src='<?php echo SYSTEM_RESOURCE_LIBRARY; ?>/fullcalendar/fullcalendar.min.js'></script>