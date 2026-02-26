<div class="htl_review">
	<div class="container">
		<div class="col-xs-12 rvw_in">
		<h3><?php echo $reviews[0]['hotel_name'];?></h3>
		<?php if(valid_array($reviews)){
		 foreach ($reviews as $key => $review) {
			?>                    
		 <div class="col-sm-12 tst_sctn">
		    <h4><?php echo $review['title'];?></h4>
		    <div class="bokratinghotl rating-no">
		    <?php echo print_star_rating($review['star_rating']);?>
		    </div>
		    <div class="clearfix"></div>                           
		    <?php echo $review['description'];?>                         
		    <strong>---  <?php echo $review['reviewer_name'];?>  ---</strong>                           
		 </div>
		<?php } } ?> 
		</div>         
	</div>
</div>