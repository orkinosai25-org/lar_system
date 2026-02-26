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

    .supplier_div {
        position: absolute;
        top: 141px;
        right: 21px;
    }

    .supplier_div ul li {
        list-style: none;
        padding: 0;
        line-height: 35px;
        font-size: 16px;
    }

    .content-wrapper {
        min-height: auto !important;
    }

    .supplier_div ul li span {
        text-align: left;
        padding-left: 5px;
        padding: 5px;
        border-radius: 3px;
        color: #000;
        letter-spacing: 0.5px;
        margin-left: 0;
        font-weight: 600;
    }


    .supplier_div h4 {
        margin-left: 0px;
        font-weight: 600;
        font-size: 18px;

    }

    .supplier_div p {
        font-weight: 700;
        background: #197abb;
        color: #fff;
        padding: 10px;
        letter-spacing: 0.5px;
        border-radius: 5px;
    }

    .supplier_div .card {
        height: auto;
        width: 100%;
        max-width: 320px !important;
        background: #e8e8e8;
        box-shadow: 1px 1px 5px lightgrey;
        padding: 10px;
        border: 1px solid lightgrey;
        border-radius: 4px;
    }

    /*!Don't remove this!
 * jQuery MDTimePicker v1.0 plugin
 * 
 * Author: Dionlee Uy
 * Email: dionleeuy@gmail.com
 *
 * Date: Tuesday, August 28 2017
 */
    @import url(https://fonts.googleapis.com/css?family=Roboto);

    .mdtp__wrapper,
    body[mdtimepicker-display="on"] {
        overflow: hidden;
    }

    .mdtimepicker {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        font-family: Roboto, sans-serif;
        font-size: 14px;
        background-color: rgba(10, 10, 10, 0.7);
        transition: background-color 0.28s ease;
        z-index: 100001;
    }

    .mdtimepicker.hidden {
        display: none;
    }

    .mdtimepicker.animate {
        background-color: transparent;
    }

    .mdtp__wrapper {
        position: absolute;
        display: flex;
        flex-direction: column;
        left: 50%;
        bottom: 24px;
        min-width: 280px;
        opacity: 1;
        user-select: none;
        border-radius: 2px;
        transform: translateX(-50%) scale(1);
        box-shadow: 0 11px 15px -7px rgba(0, 0, 0, 0.2),
            0 24px 38px 3px rgba(0, 0, 0, 0.14), 0 9px 46px 8px rgba(0, 0, 0, 0.12);
        transition: transform 0.28s ease, opacity 0.28s ease;
    }

    .mdtp__wrapper.animate {
        transform: translateX(-50%) scale(1.05);
        opacity: 0;
    }

    .mdtp__time_holder {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        font-size: 46px;
        padding: 20px 24px;
        color: rgba(255, 255, 255, 0.5);
        text-align: center;
        background-color: #1565c0;
    }

    .mdtp__time_holder>span {
        display: inline-block;
        line-height: 48px;
        cursor: default;
    }

    .mdtp__time_holder>span:not(.mdtp__timedots):not(.mdtp__ampm) {
        cursor: pointer;
        margin: 0 4px;
    }

    .mdtp__time_holder .mdtp__time_h.active,
    .mdtp__time_holder .mdtp__time_m.active {
        color: #fafafa;
    }

    .mdtp__time_holder .mdtp__ampm {
        font-size: 18px;
    }

    .mdtp__clock_holder {
        position: relative;
        padding: 20px;
        background-color: #fff;
    }

    .mdtp__clock_holder .mdtp__clock {
        position: relative;
        width: 250px;
        height: 250px;
        margin-bottom: 20px;
        border-radius: 50%;
        background-color: #eee;
    }

    .mdtp__clock .mdtp__am,
    .mdtp__clock .mdtp__pm {
        display: block;
        position: absolute;
        bottom: -8px;
        width: 36px;
        height: 36px;
        line-height: 36px;
        text-align: center;
        cursor: pointer;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.1);
        background: rgba(0, 0, 0, 0.05);
        transition: background-color 0.2s ease, color 0.2s;
        z-index: 3;
    }

    .mdtp__clock .mdtp__am {
        left: -8px;
    }

    .mdtp__clock .mdtp__pm {
        right: -8px;
    }

    .mdtp__clock .mdtp__am:hover,
    .mdtp__clock .mdtp__pm:hover {
        background-color: rgba(0, 0, 0, 0.1);
    }

    .mdtp__clock .mdtp__am.active,
    .mdtp__clock .mdtp__pm.active {
        color: #fafafa;
        background-color: #1565c0;
    }

    .mdtp__clock .mdtp__clock_dot {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 4px;
        background-color: #1565c0;
        border-radius: 50%;
    }

    .mdtp__clock .mdtp__hour_holder,
    .mdtp__clock .mdtp__minute_holder {
        position: absolute;
        top: 0;
        width: 100%;
        height: 100%;
        opacity: 1;
        transform: scale(1);
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.35s ease;
        overflow: hidden;
    }

    .mdtp__clock .mdtp__hour_holder.animate {
        transform: scale(1.2);
        opacity: 0;
    }

    .mdtp__clock .mdtp__minute_holder.animate {
        transform: scale(0.8);
        opacity: 0;
    }

    .mdtp__clock .mdtp__hour_holder.hidden,
    .mdtp__clock .mdtp__minute_holder.hidden {
        display: none;
    }

    .mdtp__clock .mdtp__digit {
        position: absolute;
        width: 50%;
        top: 50%;
        left: 0;
        margin-top: -16px;
        transform-origin: right center;
        z-index: 1;
    }

    .mdtp__clock .mdtp__digit span {
        display: inline-block;
        width: 32px;
        height: 32px;
        line-height: 32px;
        margin-left: 8px;
        text-align: center;
        border-radius: 50%;
        cursor: pointer;
        transition: background-color 0.28s, color 0.14s;
    }

    .mdtp__clock .mdtp__digit span:hover,
    .mdtp__digit.active span {
        background-color: #1565c0 !important;
        color: #fff;
        z-index: 2;
    }

    .mdtp__button,
    .mdtp__wrapper[data-theme="blue"] .mdtp__button {
        color: #1565c0;
    }

    .mdtp__digit.active:before {
        content: "";
        display: block;
        position: absolute;
        top: calc(50% - 1px);
        right: 0;
        height: 2px;
        width: calc(100% - 40px);
        background-color: #1565c0;
    }

    .mdtp__clock .mdtp__minute_holder .mdtp__digit {
        font-size: 13px;
    }

    .mdtp__clock .mdtp__minute_holder .mdtp__digit:not(.marker) {
        margin-top: -6px;
        height: 12px;
    }

    .mdtp__clock .mdtp__minute_holder .mdtp__digit:not(.marker).active:before {
        width: calc(100% - 26px);
    }

    .mdtp__clock .mdtp__minute_holder .mdtp__digit:not(.marker) span {
        width: 12px;
        height: 12px;
        line-height: 12px;
        margin-left: 14px;
    }

    .mdtp__clock .mdtp__minute_holder .mdtp__digit.marker {
        margin-top: -12px;
    }

    .mdtp__clock .mdtp__minute_holder .mdtp__digit.marker.active:before {
        width: calc(100% - 34px);
    }

    .mdtp__clock .mdtp__minute_holder .mdtp__digit.marker span {
        width: 24px;
        height: 24px;
        line-height: 24px;
        margin-left: 10px;
    }

    .mdtp__digit.rotate-6 {
        transform: rotate(6deg);
    }

    .mdtp__digit.rotate-6 span {
        transform: rotate(-6deg);
    }

    .mdtp__digit.rotate-12 {
        transform: rotate(12deg);
    }

    .mdtp__digit.rotate-12 span {
        transform: rotate(-12deg);
    }

    .mdtp__digit.rotate-18 {
        transform: rotate(18deg);
    }

    .mdtp__digit.rotate-18 span {
        transform: rotate(-18deg);
    }

    .mdtp__digit.rotate-24 {
        transform: rotate(24deg);
    }

    .mdtp__digit.rotate-24 span {
        transform: rotate(-24deg);
    }

    .mdtp__digit.rotate-30 {
        transform: rotate(30deg);
    }

    .mdtp__digit.rotate-30 span {
        transform: rotate(-30deg);
    }

    .mdtp__digit.rotate-36 {
        transform: rotate(36deg);
    }

    .mdtp__digit.rotate-36 span {
        transform: rotate(-36deg);
    }

    .mdtp__digit.rotate-42 {
        transform: rotate(42deg);
    }

    .mdtp__digit.rotate-42 span {
        transform: rotate(-42deg);
    }

    .mdtp__digit.rotate-48 {
        transform: rotate(48deg);
    }

    .mdtp__digit.rotate-48 span {
        transform: rotate(-48deg);
    }

    .mdtp__digit.rotate-54 {
        transform: rotate(54deg);
    }

    .mdtp__digit.rotate-54 span {
        transform: rotate(-54deg);
    }

    .mdtp__digit.rotate-60 {
        transform: rotate(60deg);
    }

    .mdtp__digit.rotate-60 span {
        transform: rotate(-60deg);
    }

    .mdtp__digit.rotate-66 {
        transform: rotate(66deg);
    }

    .mdtp__digit.rotate-66 span {
        transform: rotate(-66deg);
    }

    .mdtp__digit.rotate-72 {
        transform: rotate(72deg);
    }

    .mdtp__digit.rotate-72 span {
        transform: rotate(-72deg);
    }

    .mdtp__digit.rotate-78 {
        transform: rotate(78deg);
    }

    .mdtp__digit.rotate-78 span {
        transform: rotate(-78deg);
    }

    .mdtp__digit.rotate-84 {
        transform: rotate(84deg);
    }

    .mdtp__digit.rotate-84 span {
        transform: rotate(-84deg);
    }

    .mdtp__digit.rotate-90 {
        transform: rotate(90deg);
    }

    .mdtp__digit.rotate-90 span {
        transform: rotate(-90deg);
    }

    .mdtp__digit.rotate-96 {
        transform: rotate(96deg);
    }

    .mdtp__digit.rotate-96 span {
        transform: rotate(-96deg);
    }

    .mdtp__digit.rotate-102 {
        transform: rotate(102deg);
    }

    .mdtp__digit.rotate-102 span {
        transform: rotate(-102deg);
    }

    .mdtp__digit.rotate-108 {
        transform: rotate(108deg);
    }

    .mdtp__digit.rotate-108 span {
        transform: rotate(-108deg);
    }

    .mdtp__digit.rotate-114 {
        transform: rotate(114deg);
    }

    .mdtp__digit.rotate-114 span {
        transform: rotate(-114deg);
    }

    .mdtp__digit.rotate-120 {
        transform: rotate(120deg);
    }

    .mdtp__digit.rotate-120 span {
        transform: rotate(-120deg);
    }

    .mdtp__digit.rotate-126 {
        transform: rotate(126deg);
    }

    .mdtp__digit.rotate-126 span {
        transform: rotate(-126deg);
    }

    .mdtp__digit.rotate-132 {
        transform: rotate(132deg);
    }

    .mdtp__digit.rotate-132 span {
        transform: rotate(-132deg);
    }

    .mdtp__digit.rotate-138 {
        transform: rotate(138deg);
    }

    .mdtp__digit.rotate-138 span {
        transform: rotate(-138deg);
    }

    .mdtp__digit.rotate-144 {
        transform: rotate(144deg);
    }

    .mdtp__digit.rotate-144 span {
        transform: rotate(-144deg);
    }

    .mdtp__digit.rotate-150 {
        transform: rotate(150deg);
    }

    .mdtp__digit.rotate-150 span {
        transform: rotate(-150deg);
    }

    .mdtp__digit.rotate-156 {
        transform: rotate(156deg);
    }

    .mdtp__digit.rotate-156 span {
        transform: rotate(-156deg);
    }

    .mdtp__digit.rotate-162 {
        transform: rotate(162deg);
    }

    .mdtp__digit.rotate-162 span {
        transform: rotate(-162deg);
    }

    .mdtp__digit.rotate-168 {
        transform: rotate(168deg);
    }

    .mdtp__digit.rotate-168 span {
        transform: rotate(-168deg);
    }

    .mdtp__digit.rotate-174 {
        transform: rotate(174deg);
    }

    .mdtp__digit.rotate-174 span {
        transform: rotate(-174deg);
    }

    .mdtp__digit.rotate-180 {
        transform: rotate(180deg);
    }

    .mdtp__digit.rotate-180 span {
        transform: rotate(-180deg);
    }

    .mdtp__digit.rotate-186 {
        transform: rotate(186deg);
    }

    .mdtp__digit.rotate-186 span {
        transform: rotate(-186deg);
    }

    .mdtp__digit.rotate-192 {
        transform: rotate(192deg);
    }

    .mdtp__digit.rotate-192 span {
        transform: rotate(-192deg);
    }

    .mdtp__digit.rotate-198 {
        transform: rotate(198deg);
    }

    .mdtp__digit.rotate-198 span {
        transform: rotate(-198deg);
    }

    .mdtp__digit.rotate-204 {
        transform: rotate(204deg);
    }

    .mdtp__digit.rotate-204 span {
        transform: rotate(-204deg);
    }

    .mdtp__digit.rotate-210 {
        transform: rotate(210deg);
    }

    .mdtp__digit.rotate-210 span {
        transform: rotate(-210deg);
    }

    .mdtp__digit.rotate-216 {
        transform: rotate(216deg);
    }

    .mdtp__digit.rotate-216 span {
        transform: rotate(-216deg);
    }

    .mdtp__digit.rotate-222 {
        transform: rotate(222deg);
    }

    .mdtp__digit.rotate-222 span {
        transform: rotate(-222deg);
    }

    .mdtp__digit.rotate-228 {
        transform: rotate(228deg);
    }

    .mdtp__digit.rotate-228 span {
        transform: rotate(-228deg);
    }

    .mdtp__digit.rotate-234 {
        transform: rotate(234deg);
    }

    .mdtp__digit.rotate-234 span {
        transform: rotate(-234deg);
    }

    .mdtp__digit.rotate-240 {
        transform: rotate(240deg);
    }

    .mdtp__digit.rotate-240 span {
        transform: rotate(-240deg);
    }

    .mdtp__digit.rotate-246 {
        transform: rotate(246deg);
    }

    .mdtp__digit.rotate-246 span {
        transform: rotate(-246deg);
    }

    .mdtp__digit.rotate-252 {
        transform: rotate(252deg);
    }

    .mdtp__digit.rotate-252 span {
        transform: rotate(-252deg);
    }

    .mdtp__digit.rotate-258 {
        transform: rotate(258deg);
    }

    .mdtp__digit.rotate-258 span {
        transform: rotate(-258deg);
    }

    .mdtp__digit.rotate-264 {
        transform: rotate(264deg);
    }

    .mdtp__digit.rotate-264 span {
        transform: rotate(-264deg);
    }

    .mdtp__digit.rotate-270 {
        transform: rotate(270deg);
    }

    .mdtp__digit.rotate-270 span {
        transform: rotate(-270deg);
    }

    .mdtp__digit.rotate-276 {
        transform: rotate(276deg);
    }

    .mdtp__digit.rotate-276 span {
        transform: rotate(-276deg);
    }

    .mdtp__digit.rotate-282 {
        transform: rotate(282deg);
    }

    .mdtp__digit.rotate-282 span {
        transform: rotate(-282deg);
    }

    .mdtp__digit.rotate-288 {
        transform: rotate(288deg);
    }

    .mdtp__digit.rotate-288 span {
        transform: rotate(-288deg);
    }

    .mdtp__digit.rotate-294 {
        transform: rotate(294deg);
    }

    .mdtp__digit.rotate-294 span {
        transform: rotate(-294deg);
    }

    .mdtp__digit.rotate-300 {
        transform: rotate(300deg);
    }

    .mdtp__digit.rotate-300 span {
        transform: rotate(-300deg);
    }

    .mdtp__digit.rotate-306 {
        transform: rotate(306deg);
    }

    .mdtp__digit.rotate-306 span {
        transform: rotate(-306deg);
    }

    .mdtp__digit.rotate-312 {
        transform: rotate(312deg);
    }

    .mdtp__digit.rotate-312 span {
        transform: rotate(-312deg);
    }

    .mdtp__digit.rotate-318 {
        transform: rotate(318deg);
    }

    .mdtp__digit.rotate-318 span {
        transform: rotate(-318deg);
    }

    .mdtp__digit.rotate-324 {
        transform: rotate(324deg);
    }

    .mdtp__digit.rotate-324 span {
        transform: rotate(-324deg);
    }

    .mdtp__digit.rotate-330 {
        transform: rotate(330deg);
    }

    .mdtp__digit.rotate-330 span {
        transform: rotate(-330deg);
    }

    .mdtp__digit.rotate-336 {
        transform: rotate(336deg);
    }

    .mdtp__digit.rotate-336 span {
        transform: rotate(-336deg);
    }

    .mdtp__digit.rotate-342 {
        transform: rotate(342deg);
    }

    .mdtp__digit.rotate-342 span {
        transform: rotate(-342deg);
    }

    .mdtp__digit.rotate-348 {
        transform: rotate(348deg);
    }

    .mdtp__digit.rotate-348 span {
        transform: rotate(-348deg);
    }

    .mdtp__digit.rotate-354 {
        transform: rotate(354deg);
    }

    .mdtp__digit.rotate-354 span {
        transform: rotate(-354deg);
    }

    .mdtp__digit.rotate-360 {
        transform: rotate(360deg);
    }

    .mdtp__digit.rotate-360 span {
        transform: rotate(-360deg);
    }

    .mdtp__buttons {
        margin: 0 -10px -10px;
        text-align: right;
    }

    .mdtp__button {
        display: inline-block;
        padding: 0 16px;
        min-width: 50px;
        text-align: center;
        text-transform: uppercase;
        line-height: 32px;
        font-weight: 500;
        cursor: pointer;
    }

    .mdtp__button:hover {
        background-color: #e0e0e0;
    }

    .mdtp__wrapper[data-theme="blue"] .mdtp__clock .mdtp__am.active,
    .mdtp__wrapper[data-theme="blue"] .mdtp__clock .mdtp__clock_dot,
    .mdtp__wrapper[data-theme="blue"] .mdtp__clock .mdtp__pm.active,
    .mdtp__wrapper[data-theme="blue"] .mdtp__time_holder {
        background-color: #1565c0;
    }

    .mdtp__wrapper[data-theme="blue"] .mdtp__clock .mdtp__digit span:hover,
    .mdtp__wrapper[data-theme="blue"] .mdtp__digit.active span {
        background-color: #1565c0 !important;
    }

    .mdtp__wrapper[data-theme="blue"] .mdtp__digit.active:before {
        background-color: #1565c0;
    }

    .mdtp__wrapper[data-theme="red"] .mdtp__clock .mdtp__am.active,
    .mdtp__wrapper[data-theme="red"] .mdtp__clock .mdtp__clock_dot,
    .mdtp__wrapper[data-theme="red"] .mdtp__clock .mdtp__pm.active,
    .mdtp__wrapper[data-theme="red"] .mdtp__time_holder {
        background-color: #c62828;
    }

    .mdtp__wrapper[data-theme="red"] .mdtp__clock .mdtp__digit span:hover,
    .mdtp__wrapper[data-theme="red"] .mdtp__digit.active span {
        background-color: #c62828 !important;
    }

    .mdtp__wrapper[data-theme="red"] .mdtp__digit.active:before {
        background-color: #c62828;
    }

    .mdtp__wrapper[data-theme="red"] .mdtp__button {
        color: #c62828;
    }

    .mdtp__wrapper[data-theme="purple"] .mdtp__clock .mdtp__am.active,
    .mdtp__wrapper[data-theme="purple"] .mdtp__clock .mdtp__clock_dot,
    .mdtp__wrapper[data-theme="purple"] .mdtp__clock .mdtp__pm.active,
    .mdtp__wrapper[data-theme="purple"] .mdtp__time_holder {
        background-color: #6a1b9a;
    }

    .mdtp__wrapper[data-theme="purple"] .mdtp__clock .mdtp__digit span:hover,
    .mdtp__wrapper[data-theme="purple"] .mdtp__digit.active span {
        background-color: #6a1b9a !important;
    }

    .mdtp__wrapper[data-theme="purple"] .mdtp__digit.active:before {
        background-color: #6a1b9a;
    }

    .mdtp__wrapper[data-theme="purple"] .mdtp__button {
        color: #6a1b9a;
    }

    .mdtp__wrapper[data-theme="indigo"] .mdtp__clock .mdtp__am.active,
    .mdtp__wrapper[data-theme="indigo"] .mdtp__clock .mdtp__clock_dot,
    .mdtp__wrapper[data-theme="indigo"] .mdtp__clock .mdtp__pm.active,
    .mdtp__wrapper[data-theme="indigo"] .mdtp__time_holder {
        background-color: #283593;
    }

    .mdtp__wrapper[data-theme="indigo"] .mdtp__clock .mdtp__digit span:hover,
    .mdtp__wrapper[data-theme="indigo"] .mdtp__digit.active span {
        background-color: #283593 !important;
    }

    .mdtp__wrapper[data-theme="indigo"] .mdtp__digit.active:before {
        background-color: #283593;
    }

    .mdtp__wrapper[data-theme="indigo"] .mdtp__button {
        color: #283593;
    }

    .mdtp__wrapper[data-theme="teal"] .mdtp__clock .mdtp__am.active,
    .mdtp__wrapper[data-theme="teal"] .mdtp__clock .mdtp__clock_dot,
    .mdtp__wrapper[data-theme="teal"] .mdtp__clock .mdtp__pm.active,
    .mdtp__wrapper[data-theme="teal"] .mdtp__time_holder {
        background-color: #00695c;
    }

    .mdtp__wrapper[data-theme="teal"] .mdtp__clock .mdtp__digit span:hover,
    .mdtp__wrapper[data-theme="teal"] .mdtp__digit.active span {
        background-color: #00695c !important;
    }

    .mdtp__wrapper[data-theme="teal"] .mdtp__digit.active:before {
        background-color: #00695c;
    }

    .mdtp__wrapper[data-theme="teal"] .mdtp__button {
        color: #00695c;
    }

    .mdtp__wrapper[data-theme="green"] .mdtp__clock .mdtp__am.active,
    .mdtp__wrapper[data-theme="green"] .mdtp__clock .mdtp__clock_dot,
    .mdtp__wrapper[data-theme="green"] .mdtp__clock .mdtp__pm.active,
    .mdtp__wrapper[data-theme="green"] .mdtp__time_holder {
        background-color: #2e7d32;
    }

    .mdtp__wrapper[data-theme="green"] .mdtp__clock .mdtp__digit span:hover,
    .mdtp__wrapper[data-theme="green"] .mdtp__digit.active span {
        background-color: #2e7d32 !important;
    }

    .mdtp__wrapper[data-theme="green"] .mdtp__digit.active:before {
        background-color: #2e7d32;
    }

    .mdtp__wrapper[data-theme="green"] .mdtp__button {
        color: #2e7d32;
    }

    @media (max-height: 360px) {
        .mdtp__wrapper {
            flex-direction: row;
            bottom: 8px;
        }

        .mdtp__time_holder {
            width: 160px;
            padding: 20px;
        }

        .mdtp__clock_holder {
            padding: 16px;
        }

        .mdtp__clock .mdtp__am,
        .mdtp__clock .mdtp__pm {
            bottom: -4px;
        }

        .mdtp__clock .mdtp__am {
            left: -4px;
        }

        .mdtp__clock .mdtp__pm {
            right: -4px;
        }
    }

    @media (max-height: 320px) {
        .mdtp__wrapper {
            bottom: 0;
        }
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
                    <li role="presentation" class="<?php echo $tab1; ?>"><a id="fromListHead" href="#fromList" aria-controls="home" role="tab" data-toggle="tab"> Create/Update Hotel </a></li>
                    <li role="presentation" class="<?php echo $tab2; ?>"><a href="#tableList" aria-controls="profile" role="tab" data-toggle="tab"> Hotel List </a></li>
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
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);*/
                            $base_url = strstr(base_url(), 'supervision', true);
                               
                            echo $this->current_page->generate_form('stays_edit', $form_data);
                        ?>
                            <img width="20%" src="<?= $base_url . $GLOBALS['CI']->template->domain_view_ecoimage() . $form_data['image'] ?>">
                        <?php
                        } else {
                            
                            echo $this->current_page->generate_form('stays', $form_data);
                           // echo "test";die;
                        }
                        /**
                         * ********************** GENERATE UPDATE PAGE FORM ***********************
                         */
                        ?>


                    </div>
                    <div class="supplier_div" style="display:none">
                        <div id="supplier_info"></div>
                    </div>
                </div>
                <div role=" tabpanel" class="tab-pane <?php echo $tab2; ?>" id="tableList">
                    <div class="panel-body">
                        <?php
                        /**
                         * ********************** GENERATE CURRENT PAGE TABLE ***********************
                         */
                        //echo  $this->pagination->create_links(); 
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
   <table class="table table-hover table-striped table-bordered table-condensed" id="table">';
    $table .= '<thead><tr>  
	<th> Sno</th> 
   <th>Name</th> 
   <th>Address</th>
   <th>Ratings</th>
   <th>Contact No</th>
   <th>Email</th>
   <th>Host</th>
   <th>Status</th>
   <th>Action</th>
   </tr></thead><tbody>';
    if (valid_array($table_data) == true) {
        $current_record = 0;
        foreach ($table_data as $v) {
            $table .= '<tr>
			<td>' . (++$current_record) . '</td>
			<td>' . $v['name'] . '</td>			
			<td>' . $v['address'] . '</td>	
			<td>' . $v['ratings'] . '</td>	
			<td>' . $v['phone'] . '</td>	
			<td>' . $v['email'] . '</td>	
			<td>' . $v['host_name'] . '</td>	
            <td>' . get_enum_list('status', $v['status']) . '</td>									
			<td>' . get_edit_button($v['origin'])  . '<br/>' . get_gallery_images_button($v['origin']) . '<br>' . get_seasons_button($v['origin']) . '<br>' . get_rooms_button($v['origin']) . '<br>' . get_delete_button($v['origin']) . '</td>
			</tr>';
        }
    }
    $table .= '</tbody></table></div>';
    return $table;
    // '<br>' . get_review_button($v['origin']) .
}

function get_edit_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/stays/' . provab_encrypt($id) . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>
		' . get_app_message('AL0022') . '</a>';
}
function get_seasons_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/seasons/' . provab_encrypt($id) . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>
		' . 'Seasons' . '</a>';
}
function set_part_pay($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/partial_pay/' . provab_encrypt($id) . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>
		' . 'Update Partial Pay' . '</a>';
}

function get_rooms_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/rooms/' . provab_encrypt($id) . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>
		' . 'Rooms' . '</a>';
}
function get_delete_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/delete_eco_stays/' . $id . '" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i>
    ' . 'Delete' . '</a>';
}

function get_review_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/reviews/' . provab_encrypt($id) . '" class="btn btn-sm btn-primary"><i class="fa fa-comments"></i>
    ' . 'Review' . '</a>';
}

function get_gallery_images_button($id)
{
    return '<a role="button" href="' . base_url() . 'index.php/eco_stays/gallery_images/' . provab_encrypt($id) . '" class="btn btn-sm btn-primary"><i class="fa fa-comments"></i>
    ' . 'Gallery Images' . '</a>';
}
?>


</script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css">
<script type="text/javascript" src="https://cdn.datatables.net/2.0.2/js/dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('.table').DataTable({
            "pageLength": 10,

        });
    });
</script>

<script type="text/javascript">
  $(document).ready(function() {
    $('select[name="amenities"]').attr('multiple', 'multiple');
    $('#amenities').attr('name', 'amenities[]');

  
});
</script>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false&key=AIzaSyCJfvWH36KY3rrRfopWstNfduF5-OzoywY"></script>

<script>
    $('<div class="form-group"><label for="field-1" class="col-sm-3 control-label">Hotel Map<span class="text-danger">*</span></label>	<div class="col-sm-5"><div id="map_canvas" style="height:300px;width:700px;margin: 0.6em;"></div></div></div>').insertBefore($('#latitude').closest('.form-group'));

    var map;
    var geocoder;
    var mapOptions = {
        center: new google.maps.LatLng(0.0, 0.0),
        zoom: 2,
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
        google.maps.event.addListener(map, 'click', function(event) {
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
        geocoder.geocode({
            address: address
        }, function(results, status) {
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
                google.maps.event.addListener(map, 'click', function(event) {
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
                    geocoder.geocode({
                            'latLng': latLng
                        },
                        function(results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                if (results[0]) {
                                    document.getElementById("hotel_address").value = results[0].formatted_address;
                                    var address = results[0].address_components;
                                    var zipcode = address[address.length - 1].long_name;
                                    //document.getElementById("city").value 		= results[0].address_components[1]['long_name'];
                                    document.getElementById("postal_code").value = zipcode;
                                } else {
                                    //document.getElementById("city").value = "No results";
                                }
                            } else {
                                //document.getElementById("city").value = status;
                            }
                        });
                }
            }

        });
    }


    google.maps.event.addDomListener(window, 'load', initialize);

    $('#city').on('change', function() {
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
                url: '<?= base_url() ?>index.php/ajax/get_city_list1/' + country,
                type: "POST",
                data: {
                    default_value: defaultValues
                },
                dataType: 'json',
                success: function(result) {
                    $('#city').html(result.data);
                },
                error: function(request, status, error) {
                    alert('Server Error');
                }
            });
        }
    }

    function get_supplier_details(sup_id) {
        if (country != '') {
            $.ajax({
                url: '<?= base_url() ?>index.php/ajax/get_supplier_data',
                type: "POST",
                data: {
                    supplier_id: sup_id
                },
                dataType: 'json',
                success: function(result) {
                    $('.supplier_div').css("display", "block");
                    let tab_data = `<div class="card ">
                            <h4>Supplier Info</h4>
                            <ul style="padding:0">
                                <li>Supplier Name:<span> ${result['supplier_name']}</span></li>
                                <li>Supplier Email:<span> ${result['email']}</span></li>
                                <li class='hide'>Supplier Currency:<span> ${result['currency']}</span></li>
                                <li class='hide'>Supplier Portal:<span> ${result['status']}</span></li>
                            </ul>
                            <p class="hide">Please note this supplier selected currency is ${result['currency']}.<br>Hotel price will be added on ${result['currency']}.</p>
                        </div>`;
                    $('#supplier_info').html(tab_data);
                },
                error: function(request, status, error) {
                    // alert('Server Error');
                }
            });
        }
    }

    $(document).ready(function() {
        get_supplier_details('<?= $form_data['host'] ?>');
        setCityList('<?= $form_data['country'] ?>', '<?= json_encode($form_data['city']) ?>');
        setMap();
        $("#country").on("change", function() {

            setCityList(this.value);
        });

        $(document).on("change", "#host", function() {
            $('.supplier_div').css("display", "none");
            get_supplier_details(this.value);
        });

        $('select').select2({
            width: '100%'
        });
    });
</script>
<script>
    $(document).ready(function() {
        $("#expiry").datepicker({
            minDate: 0,
            dateFormat: "yy-mm-dd",
        });
        $('#stays_edit_submit').click(function() {
            var ext = $('#image').val().split('.').pop().toLowerCase();
            if (ext !== 'jpg' && ext !== 'png' && ext !== 'jpeg' && ext !== 'gif') {
                alert('Please select the image files only (gif|jpg|png|jpeg)');
                return false;
            }
        });
    });
</script>

<script type="text/javascript">
    $('#stays_edit_submit').click(function() {
        $("#file_error").html("");
        $(".demoInputBox").css("border-color", "#F0F0F0");
        var file_size = $('#image')[0].files[0].size;
        if (file_size >= 1097152) {
            alert("File size should be  1MB");
            //	$("#file_error").html("File size sholud be 1MB");
            //	$(".demoInputBox").css("border-color","#FF0000");
            return false;
        }
        return true;
    })
</script>
<script>
$(document).ready(function(){
  $("#stays_reset").click(function(){
    location.replace("https://www.travelsoho.com/LAR/supervision/eco_stays/stays");
  });
  
  $("#stays_edit_cancel").click(function(){
    window.location.href='https://www.travelsoho.com/LAR/supervision/index.php/eco_stays/stays/';
    return false;
  });
  
});
</script>
<script>
//     $(document).ready(function(){
  
//         $('#email').attr('pattern', '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$');
//         $('#email').attr('title', 'Please enter a valid email address');
    
// });
</script>
<script>
document.querySelector('form').addEventListener('submit', function (e) {
    var emailField = document.querySelector('input[name="email"]');
    var emailPattern = /^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (!emailPattern.test(emailField.value)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
    }
    


})
</script>
<?php $base_url = strstr(base_url(), 'supervision', true); ?>
<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript" src="<?= $base_url; ?>extras/system/template_list/template_v3/javascript/page_resource/car_times.js"></script>
 -->
