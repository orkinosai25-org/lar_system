<?php
//debug($aircrafts_list); die;
?>
<style type="text/css">
    a.act {
        cursor: pointer;
    }

    .table {
        margin-bottom: 0;
    }

    .modal-footer {
        padding: 10px;
    }

    .toggle.ios,
    .toggle-on.ios,
    .toggle-off.ios {
        border-radius: 20px;
    }

    .toggle.ios .toggle-handle {
        border-radius: 20px;
    }


    .box {
        /*float: left;*/
        height: 20px;
        width: 20px;
        margin-bottom: 15px;
        border: 1px solid black;
        /*clear: both;*/
    }

    .red {
        background-color: red;
    }

    .green {
        background-color: green;
    }

    .yellow {
        background-color: yellow;
    }

    .orange {
        background-color: orange;
    }

    .white {
        background-color: #fff;
    }
</style>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
<script type="text/javascript">
    $(document).ready(function() {
        $('#tab_flight_list').DataTable({
            "paging": false
        });
    });
</script>

<div class="bodyContent">
    <div class="panel panel-default">
        <!-- PANEL WRAP START -->
        <div class="panel-heading">
            <!-- PANEL HEAD START -->
            <div class="panel-title">
                Aircraft List
            </div>
        </div>
        <div class="panel-body">
            <a class="btn btn-primary" href="<?= base_url() . 'index.php/flight/add_aircraft' ?>">Add Aircraft</a>
            <div class="table-responsive">
                <!-- PANEL BODY START -->

                <table class="table table-bordered table-hover table-condensed">
                    <thead>
                        <tr>
                            <th><i class="fa fa-sort-numeric-asc"></i> S. No.</th>
                            <th>Manufacturer</th>
                            <!-- <th>Aircraft Image</th> -->
                            <th>Type</th>
                            <th>Model</th>
                            <th>MSN</th>
                            <th>A/C Reg.</th>
                            <th>Action</th>
                        </tr>

                        <tr>

                        </tr>
                    </thead>
                    <tbody>

                        <?php $x = 0;
                        if (count(@$aircrafts_list) > 0) {
                            foreach ($aircrafts_list as $key => $aircraft_detail) {

                        ?>
                                <tr>
                                    <td> <?= $key + 1 ?></td>
                                    <td><?= $aircraft_detail['manufacturer'] ?></td>
                                    <!-- <td><img id="preview" src="<?= DOMAIN_IMAGE_DIR . $aircraft_detail['aircraft_image'] ?>" alt="Image Preview" style=" margin-top: 10px; max-width: 100px;" /></td> -->
                                    <td><?= $aircraft_detail['type'] ?></td>
                                    <td><?= $aircraft_detail['model'] ?></td>
                                    <td><?= $aircraft_detail['msn'] ?></td>
                                    <td><?= $aircraft_detail['reg'] ?></td>
                                    <td>
                                        <?php if (check_user_previlege('p70')) { ?>
                                            <a class="btn btn-primary" href="<?= base_url() . 'index.php/flight/add_aircraft/' . $aircraft_detail['origin'] ?>">Edit</a>
                                            <button type="button" class="btn btn-danger delete_fare" data-table='aircrafts' data-origin="<?= $aircraft_detail['origin'] ?>">Delete</button>
                                        <?php } ?>
                                        <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#demo<?= $x ?>">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="8">
                                        <div id="demo<?= $x++ ?>" class="collapse">
                                            <table class="table table-bordered table-hover table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th>Passenger<br>Seats</th>
                                                        <th>Crew<br>Seats</th>
                                                        <th>Cockpit</th>
                                                        <th>Auto<br>Pilot</th>
                                                        <th>Fuel Type</th>
                                                        <th>MTOW</th>
                                                        <th>Empty Weight</th>
                                                        <th>Max Usable Fuel</th>
                                                        <th>Maximum Baggage</th>
                                                        <th>Airport Name</th>
                                                        <th>Date of<br>Induction at Base</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <td><?= $aircraft_detail['passenger'] ?></td>
                                                    <td><?= $aircraft_detail['crew'] ?></td>
                                                    <td><?php echo ($aircraft_detail['cockpit'] == 0) ? 'Glass' : 'Analog';  ?> </td>
                                                    <td><?php echo ($aircraft_detail['auto_pilot'] == 0) ? 'Yes' : 'No';  ?></td>

                                                    <td><?php foreach ($aircraft_detail['fuel_type'] as $key => $value) {
                                                            echo $value . '<br>';
                                                        } ?></td>
                                                    <td><?= $aircraft_detail['mtow'] ?></td>
                                                    <td><?= $aircraft_detail['empty_weight'] ?></td>
                                                    <td><?= $aircraft_detail['max_usable_fuel'] ?></td>
                                                    <td><?= $aircraft_detail['maximum_baggage'] ?></td>
                                                    <td><?= $aircraft_detail['icao_code_of_airport'] ?></td>
                                                    <td><?= $aircraft_detail['induction_at_base'] ?></td>
                                                </tbody>
                                            </table>
                                            <table class="table table-bordered table-hover table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th>Component Name</th>
                                                        <th>Part Number</th>
                                                        <th>Serial Number</th>
                                                        <th>Fitted Date </th>
                                                        <th>Fitted Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $c = json_decode($aircraft_detail['component']);
                                                    foreach ($c as $key => $value) {
                                                        if ($value->partcomponent) {
                                                            echo '<tr><td>' . $value->component_name . '</td>';
                                                            echo '<td>' . $value->partcomponent . '</td>';
                                                            echo '<td>' . $value->serialnumber . '</td>';
                                                            echo '<td>' . $value->fitted_date . '</td>';
                                                            echo '<td>' . $value->fitted_time . '</td></tr>';
                                                        } else {
                                                            echo '<tr><td colspan="4">no data</td></tr>';
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                            <table class="table table-bordered table-hover table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th>Documentation Type</th>
                                                        <th>Issue Date</th>
                                                        <th>Date of Expiry</th>
                                                        <th>Remark</th>
                                                        <th>Document</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $c = json_decode($aircraft_detail['documentation']);
                                                    foreach ($c as $key => $value) {
                                                        if ($value->documentation_type) {
                                                            echo '<tr>';
                                                            $rem = 0;
                                                            $c_date = date('Y-m-d');
                                                            $le_date  = date_create($value->date_of_expiry);

                                                            if ($le_date > $c_date) {
                                                                $date1 = date_create();
                                                                $date2 = date_create($value->date_of_expiry);
                                                                $diff = date_diff($date1, $date2);
                                                                $rem = $diff->format("%a");
                                                                if ($rem < 60) {
                                                                    if ($rem < 30) {
                                                                        $c = 'orange';
                                                                    } else {

                                                                        $c = 'yellow';
                                                                    }
                                                                } else {
                                                                    $c = 'green';
                                                                }
                                                            } else {
                                                                $c = 'red';
                                                            }
                                                            echo '<td>' . $value->document_name . '</td>';
                                                            echo '<td>' . date('Y-m-d', strtotime($value->issue_date)) . '</td>';
                                                            echo '<td style="background:' . $c . '">' . date('Y-m-d',                                                                         strtotime($value->date_of_expiry)) . '</td>';
                                                            echo '<td>' . $value->remarks . '</td>';
                                                            echo '<td>';
                                                            if (!empty($value->file)) {
                                                                $ext = explode('.', $aircraft_detail['file']);
                                                    ?>
                                                                <?php if ($ext[1] == 'doc' || $ext[1] == 'pdf') { ?>
                                                                    <a href="<?php echo $GLOBALS['CI']->template->domain_image_full_path123($value->file) ?>" target="_blank">View Document</a>

                                                                <?php } else { ?>
                                                                    <a href="<?php echo $GLOBALS['CI']->template->domain_image_full_path123($value->file) ?>" target="_blank">View</a>
                                                    <?php  }
                                                            }

                                                            echo '</td>';
                                                        } else {
                                                            //echo '<tr><td colspan="4">no data</td></tr>';
                                                        }
                                                    }
                                                    ?>



                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="12"> <strong>No Aircraft added.</strong></td>
                            </tr>
                        <?php
                        }

                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div>

    <div class='col-md-4' style='border:#000 solid 1px; padding-top:5px'>
        <ol style="padding: 5px;">
            <li><span class='box red'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Aircraft Document is expired/invalid - Red</li>
            <hr>
            <li><span class='box orange'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Aircraft Document is expiring in next 30 days - Orange</li>
            <hr>
            <li><span class='box yellow'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Aircraft Document is expiring in next 60 days - Yellow</li>
            <hr>
            <li><span class='box green'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Otherwise - Green</li>
        </ol>
    </div>
    <div class="clearfix"></div>
</div>
<hr>
<div id="add_fare_rule" class="modal fade" role="dialog">

</div>
<div id="action" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title dyn_title" id="dynamic_text"></h4>
            </div>
            <div class="modal-body action_details">
                <div class="table-responsive fare_data dyn_data">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>
<script>
    /*   $(function() {
    $('#toggle').bootstrapToggle({
      on: 'Enabled',
      off: 'Disabled'
    });
  }) */
</script>

<script type="text/javascript">
    $(document).on('click', '.dyna_status', function() {
        var thisss = $(this);
        var fsid = $(this).data('fsid');
        var status = $(this).attr('data-status');
        if (parseInt(status) === parseInt(1)) {
            status = 0;
        } else {
            status = 1;
        }
        $.ajax({
            url: "<?= base_url(); ?>index.php/flight/get_flight_status/" + fsid + "/" + status,
            async: false,
            success: function(result) {
                thisss.attr('data-status', status);
            }
        });
    });

    $(document).ready(function() {
        //  to add meal details and show
        $('#add_fare').on('click', function() {
            $('#title').text('Add Duty Type');
            $("#err").text('');
            $('input[name="origin"]').val('');
            $('input[name="duty_type_name"]').val('');
            $('input[name="duty_type_code"]').val('');
            $("#add_fare_rule").modal('show');
        });

        // to  Edit and Update the data
        $('.update_fare').on('click', function() {
            $('#title').text('Update Duty Type');
            var origin = $(this).data('origin');
            var duty_type_name = $(this).data('duty_type_name');
            var duty_type_code = $(this).data('duty_type_code');

            $("#add_fare_rule").modal('show');
            $('input[name="origin"]').val(origin);
            $('input[name="duty_type_name"]').val(duty_type_name);
            $('input[name="duty_type_code"]').val(duty_type_code);
        });



    });
</script>