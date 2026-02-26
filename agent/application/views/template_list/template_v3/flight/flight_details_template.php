<?php
// debug($data);exit;
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

<style type="text/css">
div, p, a, li, td {
	-webkit-text-size-adjust:none;
}
#outlook a {
	padding:0;
}
html {
	width: 100%;
}

body {
	width:100% !important;
	-webkit-text-size-adjust:100%;
	-ms-text-size-adjust:100%;
	margin:0;
	padding:0;
}
.ExternalClass {
	width:100%;
}
.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
	line-height: 100%;
}
#backgroundTable {
	margin:0;
	padding:0;
	width:100% !important;
	line-height: 100% !important;
}
img {
	outline:none;
	text-decoration:none;
	border:none;
	-ms-interpolation-mode: bicubic;
}
a img {
	border:none;
}
.image_fix {
	display:block;
}
p {
	margin: 0px 0px !important;
}
table td {
	border-collapse: collapse;
}
table {
	border-collapse:collapse;
	mso-table-lspace:0pt;
	mso-table-rspace:0pt;
}
table[class=full] {
	width: 100%;
	clear: both;
}
 @media only screen and (max-width: 640px) {
a[href^="tel"], a[href^="sms"] {
	text-decoration: none;
	color: #33b9ff;
	pointer-events: none;
	cursor: default;
}
.mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
	text-decoration: default;
	color: #33b9ff !important;
	pointer-events: auto;
	cursor: default;
}
table[class=devicewidth] {
	width: 440px!important;
	text-align:center!important;
}
table[class=devicewidth2] {
	width: 440px!important;
	text-align:center!important;
}
table[class=devicewidth3] {
	width: 400px!important;
	text-align:center!important;
}
table[class=devicewidth33] {
	width: 420px!important;
	text-align:center!important;
}
table[class=devicewidthinner] {
	width: 420px!important;
	text-align:center!important;
}
img[class=banner] {
	width: 440px!important;
	height:220px!important;
}
img[class=col2img] {
	display:block;
	margin:0 auto;
}
}
 @media only screen and (max-width: 480px) {
a[href^="tel"], a[href^="sms"] {
	text-decoration: none;
	color: #33b9ff;
	pointer-events: none;
	cursor: default;
}
.mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
	text-decoration: default;
	color: #33b9ff !important;
	pointer-events: auto;
	cursor: default;
}
table[class=devicewidth] {
	width: 280px!important;
	text-align:center!important;
}
table[class=devicewidth33] {
	width: 260px!important;
	text-align:center!important;
}
table[class=devicewidth2] {
	width: 280px!important;
	text-align:center!important;
}
table[class=devicewidth3] {
	width: 240px!important;
	text-align:center!important;
}
table[class=devicewidthinner] {
	width: 260px!important;
	text-align:center!important;
}
img[class=banner] {
	width: 280px!important;
	height:140px!important;
}
img[class=col2img] {
	width: 260px!important;
	height:140px!important;
}
.social {
	display: block;
	float: none;
	margin: 0 auto;
	overflow: hidden;
	padding: 10px 0;
	text-align: center !important;
	width: 100%;
}
.social div {
}
}
.rowresult {
    float: left;
    transition: all 400ms ease-in-out 0s;
    width: 100%;
}
.p-0 {
    padding: 0!important;
}
.madgrid {
    background: #fff none repeat scroll 0 0;
    position: relative;
    display: block;
   
}
.nopad {
    padding: 0!important;
}
.col-xs-1, .col-xs-10, .col-xs-11, .col-xs-12, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9 {
    float: left;
}
.col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-xs-1, .col-xs-10, .col-xs-11, .col-xs-12, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9 {
    position: relative;
    min-height: 1px;
    padding-right: 15px;
    padding-left: 15px;
}
.allsegments {
    padding: 10px;
}
.round-trip .allsegments:nth-child(odd) {
    background: #fff;
}
.allsegments {
    float: left;
    width: 100%;
}
.fligthsmll {
    display: block;
    margin: 5px;
    overflow: hidden;
    text-align: center;
}
.fligthsmll img {
    max-width: 40px;
    height: 24px;
}
img {
    vertical-align: middle;
}
.m-b-0.text-center {
    width: 100%;
    text-align: center;
    float: left;
}
.m-b-0 {
    margin-bottom: 0!important;
}
.airlinename, #flight_search_result .m-b-0.text-center > strong {
    display: inline-block;
    vertical-align: middle;
}
.airlinename {
    padding: 0 10px;
    text-overflow: ellipsis;
    white-space: nowrap;
    width: 100%;
}
.airlinename {
    color: #666;
    display: block;
    overflow: hidden;
    text-align: center;
    font-family: arial,serif;
}
.airlinename strong{
    color: #666;
    display: block;
    overflow: hidden;
    text-align: center;
    font-family: arial,serif!important;
}
.insidesame {
    padding: 2px 5px 0;
    text-align: center;
}
.insidesame {
    display: table;
    margin: 0 auto;
    padding: 2px 5px;
}

.bigtimef {
    color: #535353;
    display: block;
    font-size: 18px;
    font-weight: 400;
    line-height: 26px;
    overflow: hidden;
    font-family: arial,serif;
}
.smalairport_code {
    display: none;
}
.smalairport {
    color: #0545b2;
    display: block;
    overflow: hidden;
    font-size: 14px;
    text-align: center;
    font-weight: 500;
    font-family: arial,serif;
}
.hide {
    display: none!important;
}
.arocl.fa {
    color: #666;
    display: block;
    font-size: 14px;
    margin: 10px 0;
    overflow: hidden;
    text-align: center;
    font-family: arial,serif;
}
.col-xs-3 {
    width: 25%;
}
.durtntime {
    color: #848484;
    display: block;
    font-size: 13px !important;
    font-weight: 300;
    line-height: 18px;
    overflow: hidden;
    text-align: center;
    font-family: arial,serif;
}
.stop-value {
    display: inline-block;
    vertical-align: middle;
}
.stop-value {
    color: #0545b2;
    display: block;
    overflow: hidden;
    font-size: 13px;
    text-align: center;
    font-weight: 500;
    font-family: arial,serif;
}
.city_code1 {
    text-align: center;
    display: inline-block;
    vertical-align: middle;
    font-weight: 500;
    font-family: arial,serif;
}
.cabinclass {
    color: #848484;
    display: block;
    font-size: 13px !important;
    font-weight: 300;
    line-height: 18px;
    overflow: hidden;
    text-align: center;
    font-family: arial,serif;
}

#flight_search_result .wayeght.full_same {
    width: 80%;
}
.col-xs-8 {
    width: 66.66666667%;
}
#flight_search_result .wayfour.full_same {
    width: 20%;
}
.col-xs-4 {
    width: 27.33333333%;
}
.col-xs-9{
	width: 75%;
}
.round-trip .priceanbook {
    margin: 32px 0 0;
}
.priceanbook {
    display: block;
    margin: 0;
    overflow: hidden;
}
#flight_search_result .wayprice, #flight_search_result .waybook {
    width: 100%;
}
#flight_search_result .priceflights {
    color: #0545b2;
    font-size: 20px;
    font-weight: 600;
    font-family: arial,serif;
}
.priceflights {
    color: #fc901b;
    display: block;
    font-size: 19px;
    overflow: hidden;
    text-align: right;
    line-height: 22px;
    font-weight: 600;
    font-family: arial,serif;
}
.priceflights strong {
    font-weight: normal;
    margin-right: 3px;
}
.allsegments {
    padding: 10px!important;
}
.priceanbook{padding: 22px 10px;}
.segtwo{background-color: #fafafa;}
.segone{background-color: #eff6de;}
.rwbrd{border: 3px solid #ededed;}
/*.m-b-0.text-center > strong {
    display: inline-block;
    vertical-align: middle;

}*/
.tabmarg {
    display: block;
    margin: 15px 0 0;
    overflow: hidden;
}
.alltwobnd {
    display: table;
    width: 100%;
}
.inboundiv {
    display: block;
    overflow: hidden;
    padding: 10px;
}
.hedtowr {
    color: #03A9F4;
    display: block;
    font-size: 16px;
    font-weight: 500;
    overflow: hidden;
    padding: 5px 10px;
    clear: both;
    background: #f7f7f7;
    font-family: arial,serif;
}
.hedtowr strong {
    color: #666;
    font-weight: 300;
    font-family: arial,serif;
}
.flitone {
    padding: 8px 0px 0px 0px;
    float: left;
    width: 100%;
    background: #fdfdfd;
}
.nopad5 {
    padding: 5px;
}
.imagesmflt {
    float: left;
    margin-right: 5px;
}
.flitsmdets {
    display: block;
    line-height: 14px;
    overflow: hidden;
    font-family: arial,serif;
}
.flitsmdets strong {
    color: #666;
    display: block;
    font-weight: 300;
    margin: 5px 0 0;
    overflow: hidden;
    font-family: arial,serif;
}
.col-xs-7 {
    width: 58.33333333%;
}
.col-xs-5 {
    width: 41.66666667%;
}
.dateone {
    display: block;
    font-size: 12px;
    font-weight: 500;
    overflow: hidden;
    font-family: arial,serif;
}
.termnl {
    color: #0545b2;
    display: block;
    overflow: hidden;
    line-height: 20px;
    font-size: 14px;
    font-family: arial,serif;
}
.arocl.fa {
    color: #666;
    display: block;
    font-size: 14px;
    margin: 10px 0;
    overflow: hidden;
    text-align: center;
    font-family: arial,serif;
}
.fa-long-arrow-right:before {
    content: "\f178";
}
.ritstop {
    display: block;
    overflow: hidden;
    text-align: right;
}
.termnl1 {
    display: block;
    margin: 0 0 3px;
    overflow: hidden;
}
.Baggage_block {
    width: 100%;
    float: left;
}
.termnl1 {
    display: block;
    margin: 0 0 3px;
    overflow: hidden;
}
.flo_w {
    width: auto;
    float: right;
    font-size: 15px;
    color: #666;
    text-align: left;
    padding: 0px 10px;
    font-family: arial,serif;
}
.bag_icon {
    width: 46px;
    height: 20px;
    margin-right: 7px;
    float: left;
    font-size: 14px;
    color: #384057;
    font-family: arial,serif;
}
.layoverdiv {
    display: block;
    margin: 10px 0;
    
    position: relative;
    text-align: center;
}
.centovr {
    background: none repeat scroll 0 0 #c33673;
    border-radius: 3px;
    color: #fff;
    display: block;
    margin: 0 auto;
    overflow: hidden;
    padding: 5px;
    position: relative;
    width: 80%;
    z-index: 10;
    font-family: arial,serif;
}
.layoverdiv {
   text-align: center;
}
.centovr .fa {
    color: #fff;
    margin: 0 5px;
    font-family: arial,serif;
}
.inboundiv.seg-1 {
    border-bottom: 1px dashed #ddd;
}
.inboundiv {
    display: block;
    overflow: hidden;
    padding: 10px;
}
.inboundiv.sidefare {
    border: 1px solid #fff;
    box-shadow: 0 1px 2px 0 #ccc;
    margin: 10px 20px;
    background: #f5f5f5;
}
.farehdng {
    border-bottom: 1px solid #ccc;
    color: #666;
    display: block;
    font-size: 14px;
    margin: 0 0 15px;
    overflow: hidden;
    padding: 0 0 10px;
    font-family: arial,serif;
}
.rowfare {
    border-bottom: 1px solid #eee;
    color: #666;
    display: block;
    font-size: 14px;
    overflow: hidden;
    padding: 10px 0;
    font-family: arial,serif;
}
.pricelbl {
    display: block;
    overflow: hidden;
    text-align: right;
}
.col-xs-12{width: 100%;}
.centovr {
    background: none repeat scroll 0 0 #047297!important;
    border-radius: 3px;
    font-size: 14px;
    color: #fff;
    display: block;
    margin: 0 auto;
    overflow: hidden;
    padding: 5px;
    position: relative;
    width: 80%;
    z-index: 10;
    font-family: arial,serif;
}
.layoverdiv::after {
    border-top: 1px dashed #ccc;
    content: "";
    height: 0px;
    left: 0;
    position: absolute;
    right: 0;
    top: 89%;
    width: 100%;
    z-index: 0;
}
div {
    display: block;
}
.btn-group-vertical>.btn-group:after, .btn-group-vertical>.btn-group:before, .btn-toolbar:after, .btn-toolbar:before, .clearfix:after, .clearfix:before, .container-fluid:after, .container-fluid:before, .container:after, .container:before, .dl-horizontal dd:after, .dl-horizontal dd:before, .form-horizontal .form-group:after, .form-horizontal .form-group:before, .modal-footer:after, .modal-footer:before, .nav:after, .nav:before, .navbar-collapse:after, .navbar-collapse:before, .navbar-header:after, .navbar-header:before, .navbar:after, .navbar:before, .pager:after, .pager:before, .panel-body:after, .panel-body:before, .row:after, .row:before {
    display: table;
    content: " ";
}
/*.timings{background-image: url(/quaqua/extras/system/template_list/template_v3/images/timing.png) no-repeat 0 0}*/
.air_seat {
    width: 18px;
    height: 20px;
    float: left;
    margin-right: 0px;
    background: url("https://clickyourjourney.com/extras/system/template_list/template_v3/images/flight_seat.png") no-repeat 0 0 !important;
    background-position: 0px 0 !important;
}
.suitcase{
	width: 30px;
    height: 20px;
    float: left;
    margin-right: 0px;
    background: url("https://clickyourjourney.com/extras/system/template_list/template_v3/images/baggage_icon.png") no-repeat 0 0 !important;
    background-position: 0px 0 !important;
}
.col-xs-5 {
    width: 41.66666667%;
}
.infolblstr{font-weight: 600;}

.inboundiv.splfares {
    max-height: 250px;
    overflow-y: scroll;
}
.inboundiv {
    display: block;
    overflow: hidden;
    padding: 10px;
    border-radius: 8px;
}
.farehdng {
    border-bottom: 1px solid #ccc;
    color: #666;
    display: block;
    font-size: 18px;
    margin: 0 0 15px;
    overflow: hidden;
    padding: 0 0 10px;
}
.rowfare {
    border-bottom: 1px solid #eee;
    color: #666;
    display: block;
    font-size: 14px;
    overflow: hidden;
    padding: 10px 0;
}
.lablfare {
    color: #333;
    display: block;
    font-size: 15px;
    margin: 0 0 5px;
    overflow: hidden;
}
.feenotes {
    color: #999;
    display: block;
    font-size: 12px;
    overflow: hidden;
}
</style>
	</head>
	<body>

<!-- Start of header -->
<table width="100%"  cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="header">
        <tbody>
    <tr>
        <td>
            <table width="700" cellpadding="0" cellspacing="0"  align="center" class="devicewidth" style="border: 1px solid #ddd;">


            
<table width="100%"  cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="header">
		<tbody>
	<tr>
			<td><table width="700" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
				<tbody>
				<tr>
					<td width="100%" style="padding: 10px 10px 0; border:1px solid #ddd; border-bottom:none">
						<table width="700" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
						<tbody>
						<tr>
							<td>
								<!-- logo -->
							
							<table  width="50%" align="left" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
								<tbody>
								<tr>
									<td align="right">
										<div class="imgpop" style="float: left;padding: 5px 0px 5px 10;"><img src="https://clickyourjourney.com/extras/custom/TMX1512291534825461/images/TMX1512291534825461clickyourjourney_logo1.png" width="150" height="50"></div>
										
									</td>
								</tr>
								
							</tbody>
							</table>
							
							<!-- end of logo --> 
							<!-- logo -->
							
							<table  width="50%" align="right" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
								<tbody>
								<tr>
									<td align="center">
										
										<div class="imgpop" style="float: right;padding: 5px 10 5px 0px;font-family:arial,serif; font-size: 14px;"><strong>Date:</strong><?=date('d-m-Y');?> <span><?=date('H:i');?></span></div>
									</td>
								</tr>
									
							</tbody>
							</table>
							
							<!-- end of logo --> 
							<!-- start of menu -->
							
							<table width="100%" height="30" align="right" valign="middle" cellpadding="0" cellspacing="0" border="0" class="devicewidth">
								<tbody>
								<tr>
									<td height="30" align="center" valign="middle" style="color: #666;font-family: arial;font-size: 18px;font-weight: 600;line-height: 36px;text-align: center;">Flight Details Email</td>
								</tr>
							</tbody>
							</table>
							
							<!-- end of menu -->
						</td>
						</tr>
					</tbody>
					</table></td>
				</tr>
			</tbody>
			</table></td>
		</tr>
  </tbody>
	</table>
<!-- End of Header --> 
<?php
// debug($flight_details);exit;
?>
<!-- start of Full text -->
<table width="100%"  cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="full-text">
		<tbody>
	<tr>
			<td><table width="700" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth2">
				<tbody>
					<tr>
					<td width="100%" style="padding: 10px 10px 0; border-left:1px solid #ddd;border-right:1px solid #ddd;">
						<table bgcolor="#ffffff" width="700" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth2">

							<h4 style="font-family: arial,serif;">Flight Details</h4>
						<tbody>
						
						<tr>
							<td>
								<table width="700" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
									
    								<tbody style="border: 1px solid #ccc;">
    									<div class="rowresult p-0 rwbrd">
    										<div class="madgrid" data-key="10">
    											<div class="f-s-d-w col-xs-9 nopad wayeght full_same">
                                                    <?php 

                                                    $cur_IsRefundable = $flight_details['Attr']['IsRefundable'];
                                                    $Refundable_lab = ($cur_IsRefundable == false ? 'Non-Refundable' : 'Refundable');

                                                        if ($flight_details['SegmentSummary'][0]['TotalStops'] > 0) {
                $stop_air='';
            $stop_air = $flight_details['SegmentDetails'][0][0]['DestinationDetails']['AirportCode'];
        
            if(isset($flight_details['SegmentDetails'][0][1]['DestinationDetails']['AirportCode']) && $flight_details['SegmentSummary'][0]['TotalStops'] > 1) {
                $stop_air.= '|'.$flight_details['SegmentDetails'][0][1]['DestinationDetails']['AirportCode'];
                 
            }
            $stop_air1 = '<div class="city_code1">' . $stop_air . '   </div>';
        } else {
            $stop_air1 = '';
        }




                                                        if(!empty($flight_details['SegmentSummary'])){
                                                            $SegmentSummary_cnt=0;
                                                        foreach ($flight_details['SegmentSummary'] as $key => $value) {
    $stop_image = '';
     $__stop_count = $value['TotalStops'];
    for ($image_name = 0; $image_name < 5; $image_name++) {
        if ($__stop_count == $image_name) {
            $stop_image = 'stop_' . $image_name . '.png';
        }
    }
    if ($__stop_count > 4) {
        $stop_image = 'more_stop.png';
    }                                                        
                                                    ?>
    												<div class="allsegments <?php if($SegmentSummary_cnt==0){echo "segone";}else{echo "segtwo";}?>"><!--first section start-->
    													<div class="quarter_wdth nopad col-xs-3">
    														<div class="fligthsmll">
    															<img class="airline-logo" src="https://clickyourjourney.com/extras/system/library/images/airline_logo/<?=$value['AirlineDetails']['AirlineCode']?>.gif">
    														</div>
    														<div class="m-b-0 text-center">
    															<div class="a-n airlinename"><?=$value['AirlineDetails']['AirlineName']?></div>
    															<strong style="font-family: arial,serif;font-weight: 300;color: #999;"><?=$value['AirlineDetails']['AirlineCode'] . ' ' . $value['AirlineDetails']['FlightNumber'] . ' '.$value['AirlineDetails']['FareClassCode']?></strong>
    															
    														</div>
    														
    													</div>

    													<div class="col-xs-3 nopad quarter_wdth">
    														<div class="insidesame">
    															<div class="f-d-t bigtimef"><?=$value['OriginDetails']['_DateTime']?></div>
    															<div class="smalairport_code"><?=$value['OriginDetails']['AirportCode']?></div>
    															<div class="from-loc smalairport"><?=$value['OriginDetails']['CityName'].' ('.$value['OriginDetails']['AirportCode'].')'?></div>
    														</div>
    													</div>

    													<div class="col-md-1 p-tb-10 hide">
    														<div class="arocl fa fa-long-arrow-right"></div>
    													</div>
    													
    													<div class="smal_udayp nopad col-xs-3">
    														<div class="insidesame">
    															<div class="durtntime"><?=$value['TotalDuaration']?></div>
    															<div class="stop_image">
    																<img src="https://clickyourjourney.com/extras/system/template_list/template_v3/images/<?=$stop_image?>">
    															</div>
    															<div class="stop-value">Stop:<?=$__stop_count?></div>
    															<?=$stop_air1?>
    														</div>
    													</div>
    													
    													<div class="col-xs-3 nopad quarter_wdth">
    														<div class="insidesame">
    															<div class="f-a-t bigtimef"><?=$value['DestinationDetails']['_DateTime']?></div>
    															<div class="to-loc smalairport"><?=$value['DestinationDetails']['CityName'].' ('.$value['DestinationDetails']['AirportCode'].')'?></div>
    															<div class="smalairport_code"><?=$value['DestinationDetails']['AirportCode']?></div>
    														</div>
    													</div>
    													
    												</div><!--first section end-->
                                                <?php 

                                                    $SegmentSummary_cnt++;
                                                        }
                                                    }?>


    												
    												
    											</div>
    											<div class="col-xs-3 nopad wayfour full_same">
    												<div class="priceanbook">
    													   <div class="insidesame">
    															<div class="priceflights">
    																<strong class="display_currency">Rs</strong>
    																<span class="f-p"><?=roundoff_number($flight_details['FareDetails']['b2b_PriceDetails']['_BaseFare']+$flight_details['FareDetails']['b2b_PriceDetails']['_TaxSum'])?></span>
    																
    															</div>
    															<span><?=$Refundable_lab?></span>
    														</div>
    												</div>
    											</div>
    											
    										</div>
    										
    									</div>
    								
    								
    								
    								
    							   </tbody>
                                </table>


						      </td>
						</tr>
						<!-- Spacing -->
						<tr>
							<td height="20" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
						</tr>
						<!-- Spacing -->
					</tbody>
					</table>
				</td>
				</tr>
				
                  <tr>
					<td width="100%" style="padding: 10px 10px 0; border-left:1px solid #ddd;border-right:1px solid #ddd;">
						<table bgcolor="#ffffff" width="700" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth2">

							<h4 style="font-family: arial,serif;">Flight Itinerary Details</h4>
						<tbody>
						
						<tr>
							<td>
								<table width="700" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
									
								<tbody style="border: 1px solid #ccc;">
								
								<!-- Seperator -->
								<!--<tr>
									<td style="color: #666666;font-family:arial,serif; font-size: 14px;line-height: 24px;text-align: left;">xfbvbnfhmhmmj</td>
								</tr>-->
								<!-- End of Seperator --> 
								<div class="tab-pane rwbrd ">
									<div class="tabmarg">
										<div class="alltwobnd">
											<div class="col-xs-12 nopad full_wher">
                                                <?php
                                                    if(!empty($flight_details['SegmentDetails']))
                                                    {

                                                        foreach ($flight_details['SegmentDetails'] as $__segment_k => $__segment_v)
                                                        {

                                                            $segment_summary = $flight_details['SegmentSummary'][$__segment_k];


                                                            $segmentcount=0;
                                                        
                                                            ?>
            												<div class="inboundiv seg-0"><!--first section start-->
            													<div class="hedtowr">
                                                                    <?=
                                                                    $segment_summary['OriginDetails']['CityName'] . ' to ' . $segment_summary['DestinationDetails']['CityName'] . ' <strong>(' . $segment_summary['TotalDuaration'] . ')</strong>'?>
                                                                </div>
                                                                <?php

                                                                    foreach ($__segment_v as $key => $value)
                                                                    {
                                                                        $Baggage = trim($value['Baggage']);
                                                                        $AvailableSeats = isset($value['AvailableSeats']) ? $value['AvailableSeats'] . ' seats' : '';


                                                                        ?>
                    													<div class="flitone"><!--filt first start-->
                    														<div class="col-xs-3 nopad5">
                    															<div class="imagesmflt">
                    																<img src="https://clickyourjourney.com/extras/system/library/images/airline_logo/<?=$value['AirlineDetails']['AirlineCode']?>.gif">
                    															</div>
                    															<div class="flitsmdets">
                    																<?=$value['AirlineDetails']['AirlineName']?><strong><?=$value['AirlineDetails']['AirlineCode'] . ' ' . $value['AirlineDetails']['FlightNumber'] . ' '.$value['AirlineDetails']['FareClassCode']?> </strong>
                    															</div>
                    															
                    														</div>
                    														<div class="col-xs-7 nopad5">
                    															<div class="col-xs-4 nopad5">
                    																<div class="dateone"><?=$value['OriginDetails']['_DateTime']?></div>
                    																<div class="dateone"><?=$value['OriginDetails']['_Date']?></div>
                    																<div class="termnl"><?=$value['OriginDetails']['AirportName'].' ('.$value['OriginDetails']['AirportCode'].')'?></div>
                    															</div>
                    															<div class="col-xs-4 nopad">
                    																<img src="https://clickyourjourney.com/extras/system/template_list/template_v3/images/frwdarrow.png" width="30" height="30">
                    															</div>
                    															<div class="col-xs-4 nopad5">
                    																<div class="dateone"><?=$value['DestinationDetails']['_DateTime']?></div>
                    																<div class="dateone"><?= $value['DestinationDetails']['_Date']?></div>
                    																<div class="termnl"><?= $value['DestinationDetails']['AirportName'].' ('.$value['DestinationDetails']['AirportCode'].')'?></div>
                    															</div>
                    															
                    														</div>

                    														<div class="col-xs-2 nopad5">
                    															<div class="ritstop">
                    																<div class="termnl"><?=$value['SegmentDuration']?> </div>
                    																<div class="termnl1">Stop : <?=$key?></div>
                    																
                    															</div>
                    															
                    														</div>
                    														<div class="Baggage_block">
                                                                                <?php
                                                                                if (empty($Baggage) == false) {
                                                                                ?>

                                                                                    <div class="termnl1 flo_w"><em><i class="suitcase bag_icon"></i><?=$Baggage?></em></div>

                                                                                    <?php
                                                                                }

                                                                                if (empty($AvailableSeats) == false) {?>
                                                                                    <div class="termnl1 flo_w"><em><i class="air_seat timings icseats" ></i><?=$AvailableSeats?></em></div>
                                                                                <?php
                                                                                    }

                                                                                ?>
                    															 
                    															
                    															
                    														</div>
                    														
                    													</div>
                    													
                    													<?php
                                                                       
                                                                        if (isset($value['WaitingTime']) == true) 
                                                                        {
                                                                            $next_seg_info = $__segment_v[$key+1];
                                                                            $waiting_time = $value['WaitingTime'];
                                                                              ?>
                                                                            <div class="clearfix"></div>
                                                                            <div class="layoverdiv">
                                                                                <div class="centovr">
                                                                                <span class="fa fa-plane"></span>Plane change at <?=$next_seg_info['OriginDetails']['CityName'] ?> | <span class="fa fa-clock-o"></span> Waiting: <?= $waiting_time?>
                                                                            </div></div>
                                                                            <div class="clearfix"></div>
                                                                            <?php
                                                                        }
                                                                        
                                                                    }

                                                                        ?>
            													

            													
            													
            												</div>  <!--first section end-->
                                                                    <?php
                                                                    
                                                        }
                                                    }


                                                ?>

												
												
											</div>

                                            <?php 

                                             $cur_FareDetails = $flight_details['FareDetails']['b2b_PriceDetails'];
                                             $o_BaseFare = ($cur_FareDetails['_BaseFare']);
                                            $cur_Currency = $cur_FareDetails['CurrencySymbol'];
                                            $o_Total_Tax = ($cur_FareDetails['_TaxSum']);
                                            // $o_Total_Fare = ceil($cur_FareDetails['TotalFare']);
                                            $o_Total_Fare = $o_BaseFare + $o_Total_Tax;
                                            ?>

											<div class="col-xs-12 nopad full_wher">
												<div class="inboundiv sidefare">
													<h4 class="farehdng">Total Fare Breakup</h4>
													<div class="inboundivinr">
														<div class="rowfare"><!--rowfare start-->
															<div class="col-xs-8 nopad">
																<span class="infolbl">Total Base Fare</span>
															</div>
															<div class="col-xs-4 nopad">
																<span class="pricelbl">
																	<span class="display_currency"><?=$cur_Currency?></span>
																	<span class="base_price"><?=roundoff_number($o_BaseFare)?></span>
																</span>
																
															</div>
															
														</div><!--rowfare end-->

														<div class="rowfare"><!--rowfare start-->
															<div class="col-xs-8 nopad">
																<span class="infolbl">Taxes & Fees</span>
															</div>
															<div class="col-xs-4 nopad">
																<span class="pricelbl">
																	<span class="display_currency"><?=$cur_Currency?></span>
																	<span class="base_price">  <?=roundoff_number($o_Total_Tax)?></span>
																</span>
																
															</div>
															
														</div><!--rowfare end-->

														<div class="rowfare grandtl"><!--rowfare start-->
															<div class="col-xs-8 nopad">
																<span class="infolblstr">Grand Total</span>
															</div>
															<div class="col-xs-4 nopad">
																<span class="pricelbl">
																	<span class="display_currency infolblstr"><?=$cur_Currency?></span>
																	<span class="base_price infolblstr">  <?=roundoff_number($o_Total_Fare)?></span>
																</span>
																
															</div>
															
														</div><!--rowfare end-->
														
													</div>
												</div>
												
											</div>
											
										</div>
										
									</div>
									
								</div>





								
							</tbody>
							</table>


						</td>
						</tr>
						<!-- Spacing -->
						<!-- <tr>
							<td height="20" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
						</tr> -->
						<!-- Spacing -->
					</tbody>
					</table>
				</td>
				</tr>
				

				
				<tr>
					<td width="100%" style="padding: 10px 10px 0; border-left:1px solid #ddd;border-right:1px solid #ddd;">
						<table bgcolor="#ffffff" width="700" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth2">
						<tbody>
						<!-- Spacing -->
						<tr>
							<td height="20" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
						</tr>
						<!-- Spacing -->
						  <tr>
							<td>
								<table width="700" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner" style="border: 3px solid #ededed">
									<thead>
										<tr>
											<th style="color: #000;font-family:arial,serif; font-size: 14px;line-height: 24px;text-align: left;background-color: #f7f7f7;padding: 5px 10px;border-bottom:1px solid #ccc;font-weight: 600;">Fare Details</th>
										</tr>
									</thead>
								</table>
								</td>
                            </tr>
								<!-- Seperator -->
								<!--<tr>
									<td style="color: #666666;font-family:arial,serif; font-size: 14px;line-height: 24px;text-align: left;">xfbvbnfhmhmmj</td>
								</tr>-->
								<!-- End of Seperator --> 
								
								<tr>
									<td style="color: #000;font-family:arial,serif; font-size: 12px;line-height: 24px;text-align: left;border: 1px solid #ddd;">
                                        <table width="700" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                            <tbody>
                                               <tr>
                                        <?php

                                            $flight_segment_fare = force_multple_data_format($fare_rules);
                                            // debug($flight_segment_fare);
                                        $rules = '';
                                        $rules .= '<td>';
                                            // $rules .= '<div class="inboundiv splfares">';
                                            $rules .= '<h4 class="farehdng">Fare Rules</h4>';
                                        foreach ($flight_segment_fare[0]['data'] as $__fare_key => $__fare_rules) {
                                            
                                                    $rules .= $__fare_rules['Origin'].'  '.$__fare_rules['Destination'];
                                                
                                              
                                                $rules .= (isset($__fare_rules['FareRules']) == true ? $__fare_rules['FareRules'] : 'Not Available.');
                                               
                                        }
                                            // $rules .= '</div>';
                                        $rules .= '</td>';
                                        echo $rules;



                                        ?> 
                                       </tr>
                                   
                                        </tbody>
             
                                        </table>


                                        <!-- Start of Postfooter -->
<!-- <table width="100%"  cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="postfooter"> -->
<table width="700" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth2">
        <tbody>
    <tr>
            <td><table width="700" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth2">
                <tbody>
                <tr>
                    <td width="100%" style="padding: 10px 10px 0; border:1px solid #ddd; border-top:none">
                        <table width="700" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth" bgcolor="#ffffff">
                        <tbody>
                        <tr>
                        <td>
                        <table width="100%" align="left" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td align="left" valign="middle" style="color: #0545b2;font-family: arial;font-size: 12px;padding:22px 10px;text-align: left;" st-content="preheader"><a href="https://clickyourjourney.com/terms-conditions" style="text-decoration: underline; color: #0545b2">Terms & Conditions</a> </td>
                            
                        </tr>
                        </table>
                        
                        </td>
                        </tr>
                    </tbody>
                    </table>
                </td>
                </tr>
                
            </tbody>
            </table></td>
        </tr>
  </tbody>
</table>
<!-- End of postfooter --> 
<!-- Start of Postfooter -->
<!-- <table width="100%"  cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="postfooter" > -->
    <table width="700" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth2">
        <tbody>
    <tr>
            <td><table width="700" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth2"  bgcolor="#047297">
                <tbody>
                <tr>
                    <td width="100%" style="padding: 10px 10px 0; border:1px solid #ddd; border-top:none"><table width="700" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                        <tbody>
                        <tr>
                        <td>
                            
                            <table width="35%" align="left" cellpadding="0" cellspacing="0" border="0">
                               <tr>
                                    <td align="right">
                                        <div class="imgpop" style="float: left;padding: 5px 0px 5px 10;"><img src="https://clickyourjourney.com/extras/custom/TMX1512291534825461/images/TMX1512291534825461clickyourjourney_logo1.png" width="150" height="50"></div>
                                        
                                    </td>
                                </tr>
                        </table>
                        <table width="35%" align="left" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td align="left" valign="middle" style="color: #666;font-family: arial;font-size: 12px;padding:22px 10px;text-align: left;" st-content="preheader"><span style="color: #fff">375 Rajadanga Main Road, Kasba, Kolkata - 700107 </span> </td>
                            
                        </tr>
                        </table>
                        <table  width="30%" align="left" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td align="left" valign="middle" style="color: #666;font-family: arial;font-size: 12px;padding:22px 10px;text-align: left;" st-content="preheader"><span style="color: #fff"> Land Line: +91-33-44558899</span><br><span style="color: #fff">Toll-free No : 1800 212 999 000</span>
                             
                            </td>
                        </tr>
                        </table>
                       
                        </td>
                        </tr>
                    </tbody>
                    </table></td>
                </tr>
                <!-- Spacing -->
                <tr>
                    <td width="100%" height="10"></td>
                </tr>
                <!-- Spacing -->
            </tbody>
            </table></td>
        </tr>
  </tbody>
    </table>
<!-- End of postfooter --> 







                                    </td>
									
								</tr>
								
							</tbody>
							</table>


						</td>
						</tr>
						<!-- Spacing -->
						<tr>
							<td height="20" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
						</tr>
						<!-- Spacing -->
					</tbody>
					</table>
				</td>
				</tr>
			</tbody>
			</table>




		 
		
         </table>
        </td>
    </tr>
  </tbody>
	</table> 
<!-- End of Full Text --> 





</body>
</html>