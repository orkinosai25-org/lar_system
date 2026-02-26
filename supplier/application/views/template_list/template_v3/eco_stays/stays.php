<?php
if ($origin > 0) {
    $tab1 = " active ";
    $tab2 = "";
} else {
    $tab2 = " active ";
    $tab1 = "";
}
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
   .main-header .sidebar-toggle {
    font-size: 22px;
    display: block;
    color: #000;
    padding: 14px 15px;
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
                            aria-controls="home" role="tab" data-toggle="tab"> Create/Update Hotel </a></li>
                    <li role="presentation" class="<?php echo $tab2; ?>"><a href="#tableList" aria-controls="profile"
                            role="tab" data-toggle="tab"> Hotel List </a></li>
                    <!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE END -->
                </ul>
            </div>
        </div>
        <!-- PANEL HEAD START -->
        <div class="panel-body">
            <!-- PANEL BODY START -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane <?php echo $tab1; ?>" id="fromList">
                    <div class="panel-body">
                        <?php
                        /**
                         * ********************** GENERATE CURRENT PAGE FORM ***********************
                         */

                        if ($origin > 0) {

                            echo $this->current_page->generate_form('stays_edit', $form_data);
                            ?>
                            <img width="20%"
                                src="<?= base_url() . $GLOBALS['CI']->template->domain_eco_stays_images_upload_dir('stays/' . $form_data['image']) ?>">
                            <?php
                        } else {
                            echo $this->current_page->generate_form('stays', $form_data);
                        }
                        /**
                         * ********************** GENERATE UPDATE PAGE FORM ***********************
                         */
                        ?>
                    </div>
                </div>
                <div role=" tabpanel" class="tab-pane <?php echo $tab2; ?>" id="tableList">
                    <div class="panel-body">
                        <?php
                        /**
                         * ********************** GENERATE CURRENT PAGE TABLE ***********************
                         */
                        echo get_table(@$data_list);
                        /**
                         * ********************** GENERATE CURRENT PAGE TABLE ***********************
                         */
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- PANEL BODY END -->
    </div>
    <!-- PANEL WRAP END -->
</div>
<?php
function get_table($table_data = '')
{
    // debug($table_data);exit;
    $table = '';
    $table .= '
   <div class="table-responsive">
   <table class="table table-hover table-striped table-bordered table-condensed">';
    $table .= '<thead><tr>  
	<th> Sr No.</th> 
   <th>Hotel Name</th> 
   <th>Hotel Address</th>
   <th>Ratings</th>
   <th>Contact No</th>
   <th>Email</th>
   
   <th>Status</th>
   <th>Action</th>
   </tr></thead><tbody>';
    if (valid_array($table_data) == true) {
        $current_record = 0;
        foreach ($table_data as $k => $v) {
            $table .= '<tr>
			<td>' . (++$current_record) . '</td>
			<td>' . $v['name'] . '</td>			
			<td>' . $v['address'] . '</td>	
			<td>' . $v['ratings'] . '</td>	
			<td>' . $v['phone'] . '</td>	
			<td>' . $v['email'] . '</td>	
			
            <td>' . get_enum_list('status', $v['status']) . '</td>									
			<td>' . get_edit_button($v['origin']) . '<br>' . get_gallery_images_button($v['origin']) . '<br>' . get_seasons_button($v['origin']) . '<br>' . get_rooms_button($v['origin']) . '<br>' . get_delete_button($v['origin']) . '<br></td>
			</tr>';
        }
    }
    $table .= '</tbody></table></div>';
    return $table;
}

function get_edit_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/stays/' . $id . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>
		'  . 'Update' . '</a>';
}
function get_seasons_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/seasons/' . $id . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>
		' . 'Seasons' . '</a>';
}

function get_rooms_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/rooms/' . $id . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>
		' . 'Rooms' . '</a>';
}
function get_delete_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/delete_eco_stays/' . $id . '" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i>
    ' . 'Delete' . '</a>';
}

function get_review_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/reviews/' . $id . '" class="btn btn-sm btn-primary"><i class="fa fa-comments"></i>
    ' . 'Review' . '</a>';
}

function get_gallery_images_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/gallery_images/' . $id . '" class="btn btn-sm btn-primary"><i class="fa fa-comments"></i>
    ' . 'Gallery Images' . '</a>';
}
?>

<script>
$(document).ready(function(){
  $("#stays_reset").click(function(){
    location.replace("https://www.travelsoho.com/LAR/supplier/index.php/eco_stays/stays");
  });
  
  $("#stays_edit_cancel").click(function(){
    
    window.location.href='https://www.travelsoho.com/LAR/supplier/index.php/eco_stays/stays';
    return false;
  });
  
});
</script>

<script type="text/javascript"
    src="https://maps.googleapis.com/maps/api/js?sensor=false&key=AIzaSyCJfvWH36KY3rrRfopWstNfduF5-OzoywY"></script>

<script>

    $('<div class="form-group"><label for="field-1" class="col-sm-3 control-label">Hotel Map<span class="text-danger">*</span></label>	<div class="col-sm-5"><div id="map_canvas" style="height:300px;width:700px;margin: 0.6em;"></div></div></div>').insertBefore($('#latitude').closest('.form-group'));

    var map;
    var geocoder;
    var mapOptions = {
        center: new google.maps.LatLng(0.0, 0.0), zoom: 2,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    function initialize() {
        var myOptions = {
            center: new google.maps.LatLng(12.851, 77.659),
            //center: new google.maps.LatLng(-1.9501,30.0588),
            zoom: 10,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        geocoder = new google.maps.Geocoder();
        var map = new google.maps.Map(document.getElementById("map_canvas"),
            myOptions);
        google.maps.event.addListener(map, 'click', function (event) {
            placeMarker(event.latLng);
        });

        var marker;
        function placeMarker(location) {
            if (marker) { //on vérifie si le marqueur existe
                marker.setPosition(location); //on change sa position
            } else {
                marker = new google.maps.Marker({ //on créé le marqueur
                    position: location,
                    map: map
                });
            }
            $('#latitude').val(location.lat());
            $('#longitude').val(location.lng());
        }
    }

    function geocodeAddress(address) {
        geocoder.geocode({ address: address }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var p = results[0].geometry.location;
                var lat = p.lat();
                var lng = p.lng();
                //createMarker(address,lat,lng);
                ///alert(lng);
                var myOptions = {
                    center: new google.maps.LatLng(lat, lng),
                    //center: new google.maps.LatLng(-1.9501,30.0588),
                    zoom: 10,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
                google.maps.event.addListener(map, 'click', function (event) {
                    placeMarker(event.latLng);
                });

                var marker;
                function placeMarker(location) {
                    if (marker) { //on vérifie si le marqueur existe
                        marker.setPosition(location); //on change sa position
                    } else {
                        marker = new google.maps.Marker({ //on créé le marqueur
                            position: location,
                            map: map
                        });
                    }
                    document.getElementById('latitude').value = location.lat();
                    document.getElementById('longitude').value = location.lng();
                    getAddress(location);
                }

                function getAddress(latLng) {
                    geocoder.geocode({ 'latLng': latLng },
                        function (results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                if (results[0]) {
                                    document.getElementById("hotel_address").value = results[0].formatted_address;
                                    var address = results[0].address_components;
                                    var zipcode = address[address.length - 1].long_name;
                                    //document.getElementById("city").value 		= results[0].address_components[1]['long_name'];
                                    document.getElementById("postal_code").value = zipcode;
                                }
                                else {
                                    //document.getElementById("city").value = "No results";
                                }
                            }
                            else {
                                //document.getElementById("city").value = status;
                            }
                        });
                }
            }

        }
        );
    }


    google.maps.event.addDomListener(window, 'load', initialize);

    $('#city').on('change', function () {
        setMap();
    });

    function setMap() {
        let setMapInterval = setInterval(() => {
            if (document.querySelector("#city").selectedOptions[0] !== undefined) {
                clearInterval(setMapInterval);
                let search_city = document.querySelector("#city").selectedOptions[0].innerText;
                let country = $('#country').val();

                if (search_city != '') {
                    geocodeAddress(search_city + ',' + country);
                }
            }
        }, 1000);
    }

    function setCityList(country, defaultValues) {
        if (country != '') {
            $.ajax({
                url: '<?= base_url() ?>index.php/ajax/get_city_list/' + country,
                type: "POST",
                data: { default_value: defaultValues },
                dataType: 'json',
                success: function (result) {
                    $('#city').html(result.data);
                },
                error: function (request, status, error) {
                    alert('Server Error');
                }
            });
        }
    }

    $(document).ready(function () {
        setCityList('<?= $form_data['country'] ?>', '<?= json_encode($form_data['city']) ?>');
        setMap();
        $("#country").on("change", function () {
            setCityList(this.value);
        });

        $('select').select2({
            width: '100%'
        });
    });
</script>
