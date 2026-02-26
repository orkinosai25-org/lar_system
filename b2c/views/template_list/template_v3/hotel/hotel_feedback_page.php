<!--  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> -->
<style>
    .panel-primary {
        border-color: #BF9766;
    }
    .panel-primary>.panel-heading {
        color: #fff;
        background-color: #BF9766;
        border-color: #BF9766;
    }
    .cke_chrome {
    border: 1px solid #bf9766;
    }
    .cke_bottom {
    background: #bf9766;
    }
   .stars a {
   display: inline-block;
   padding-right: 4px;
   text-decoration: none;
   margin: 0;
   }
   .stars a:after {
   position: relative;
   font-size: 18px;
   font-family: 'Font Awesome 5 Pro';
   display: block;
   content: "\f005";
   color: #9e9e9e;
   font-weight: 600
   }
   .stars a:hover~a:after {
   color: #9e9e9e !important;
   }
   span.active a.active~a:after {
   color: #9e9e9e;
   }
   span:hover a:after {
   color: #BF9766 !important;
   }
   .fd_str{font-size: 0}
   span.active a:after,
   .stars a.active:after {
   color: #BF9766;
   }
   .m-5 { margin: 3rem 0 }
   .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>td{
    border-top:none;
    }
    .mb-0 { margin-bottom: 0 }
    .m-0 { margin: 0 }
    .table>tbody>tr>td { vertical-align: middle; }
    .stars a.active:after {
        font-weight: 600;
    }
    input.btn.btn-sm.btn-success {
        background: #3f3f3f;
        border: 1px solid #3f3f3f;
        padding: 5px 15px;
        font-size: 14px;
        line-height: 21px;
        font-weight: 500;
    }
</style>
<script src="<?php echo SYSTEM_RESOURCE_LIBRARY?>/ckeditor/ckeditor.js"></script>
<div class="bodyContent m-5 col-xs-12 nopad">
    <div class="container">
       <div class="panel <?=PANEL_WRAPPER?>">
          <!-- PANEL WRAP START -->
          <div class="panel-heading">
             <!-- PANEL HEAD START -->
             <div class="panel-title"> Hotel Feedback
             </div>
          </div>
          <!-- PANEL HEAD START -->
          <div class="panel-body">
             <!-- PANEL BODY START -->
             <form method="post" autocomplete="off" action="<?php echo base_url();?>index.php/hotel/hotel_feedback/<?php echo $app_reference;?>" id="profile_form">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-condensed m-0">
                   <tr>
                      <td>Star Rating<span class="text-danger">*</span></td>
                      <td>
                         <p class="stars mb-0">
                            <span class="fd_str">
                            <a class="star-1 active" href="#">1</a>
                            <a class="star-2" href="#">2</a>
                            <a class="star-3" href="#">3</a>
                            <a class="star-4" href="#">4</a>
                            <a class="star-5" href="#">5</a>
                            </span>
                         </p>
                         <input type="hidden" id="star_rating" name="star_raing" value="1" />
                      </td>
                   </tr>
                   <tr>
                      <td>Review Title<span class="text-danger">*</span></td>
                      <td><input class="form-control" type="text" name="page_title" value="" required>
                      </td>
                   </tr>
                   <tr>
                      <td>Review Description<span class="text-danger">*</span></td>
                      <td><textarea class="ckeditor" id="editor" name="page_description" rows="10" cols="80" required></textarea>
                      </td>
                   </tr>
                   <tr>
                      <td colspan="3" align="center"><input type="submit" class="btn btn-sm btn-success" value="Submit"/></td>
                   </tr>
                </table>
             </form>
          </div>
       </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>
   $('.stars a').on('click', function() {
       $('.stars span, .stars a').removeClass('active');
   
       $(this).addClass('active');
       $('.stars span').addClass('active');
       $('#star_rating').val($(this).text());
   });
</script>