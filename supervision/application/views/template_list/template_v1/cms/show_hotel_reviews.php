<?php
   $tab1 = 'active';

 		$action = base_url().'index.php/cms/show_hotel_reviews';
 	
 	// echo $action;exit;
?>
<!-- HTML BEGIN -->
<div class="bodyContent">
<div class="panel panel-default"><!-- PANEL WRAP START -->
<div class="panel-heading"><!-- PANEL HEAD START -->

</div>
<!-- PANEL HEAD START -->
<div class="panel-body"><!-- PANEL BODY START -->
<span class="error_msg"><?php $msg = $this->uri->segment(3); if(isset($msg)){ echo urldecode($msg); }?></span>
<div class="tab-content">
<div role="tabpanel" class="clearfix tab-pane <?=$tab1?>" id="fromList">
<div class="panel-body">


<div class="tab-content">
   <div id="fromList" class="clearfix tab-pane  active " role="tabpanel">
      <div class="panel-body">
         <form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="<?= $action; ?>" autocomplete="off" name="home_page_heading">
            <fieldset form="promo_codes_form_edit">
               
               <div class="radio">
                  <label form="promo_codes_form_edit" for="status" class="col-sm-3 control-label">Show Reviews On Search Results Page<span class="text-danger">*</span></label>
                     <label for="promo_codes_form_editstatus0" class="radio-inline">  
                        <input type="radio" value="0" id="promo_codes_form_editstatus0"  name="status" class=" status radioIp" dt="" <?php if($show_review == 0){ ?> checked="checked" <?php } ?>required="">Inactive
                     </label>
                     <label for="promo_codes_form_editstatus1" class="radio-inline"> 
                      <input type="radio" value="1" id="promo_codes_form_editstatus1" name="status" class=" status radioIp" <?php if($show_review == 1){ ?>checked="checked"<?php } ?>  required="">Active
                      </label>
               </div>
             
              	
              </fieldset>
            <div class="form-group">
           
               <div class="col-sm-8 col-sm-offset-4"> <button class="btn btn-success" type="submit">Save</button> <button class=" btn btn-warning " id="promo_codes_form_edit_reset" type="reset">Reset</button></div>
            </div>
         </form>
      </div>
   </div>
</div>


</div>
</div>

</div>
</div>
<!-- PANEL BODY END --></div>
<!-- PANEL WRAP END --></div>
<!-- HTML END -->

