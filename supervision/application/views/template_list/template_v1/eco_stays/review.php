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
                            aria-controls="home" role="tab" data-toggle="tab"> Stays Review </a></li>
                    <!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE END -->
                </ul>
            </div>
        </div>
        <!-- PANEL HEAD START -->
        <div>
            <a role="button" href="<?php echo base_url() . 'index.php/eco_stays/stays/' ?>"
                class="btn btn-sm btn-primary pull-right">Back to Eco Stays List</a>
        </div>
        <div class="panel-body">
            <!-- PANEL BODY START -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane <?php echo $tab1; ?>" id="fromList">
                    <div class="panel-body">

                        <form name="types" autocomplete="off"
                            action="/supervision/index.php/eco_stays/reviews/<?= $stays_origin ?>" method="POST"
                            enctype="multipart/form-data" id="types" role="form" class="form-horizontal">

                            <?php
                            foreach ($reviews as $k => $review) {
                                ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="name" form="types">
                                        <?= $review['crateria_name'] ?> Rating<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-6"><input
                                            value="<?= empty($review['rating']) ? 0 : $review['rating'] ?>"
                                            name="<?= 'criteria_' . $review['origin'] ?>" required="" type="number"
                                            placeholder="" class=" name form-control" min="0" max="5" step='.1'
                                            id="<?= 'criteria_' . $review['origin'] ?>" data-original-title="" title="">
                                    </div>
                                </div>

                            <?php } ?>

                            <div class="form-group">
                                <div class="col-sm-8 col-sm-offset-4"> <button type="submit" id="types_submit"
                                        class=" btn btn-success ">Save</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        <!-- PANEL BODY END -->
    </div>
    <!-- PANEL WRAP END -->
</div>