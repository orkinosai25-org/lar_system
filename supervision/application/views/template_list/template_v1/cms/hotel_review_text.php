<!-- HTML BEGIN -->
<div class="bodyContent">
	<div class="row">
		<div class="pull-left" style="margin:5px 0">
			<a href="<?=base_url().'index.php/cms/add_home_page_heading'?>">
				<button  class="btn btn-primary btn-sm pull-right amarg">Hotel Review Text</button>
			</a>
		</div>
	</div>
	<div class="panel <?=PANEL_WRAPPER?>"><!-- PANEL WRAP START -->
		<div class="panel-heading"><!-- PANEL HEAD START -->
			<div class="panel-title">
				<i class="fa fa-edit"></i> Hotel Review Text
			</div>
		</div><!-- PANEL HEAD START -->
		
		<div class="panel-body">
			<table class="table table-condensed">
				<tr>
						
					<th>Rating</th>
					<th>Description</th>
					<th>Action</th>
				</tr>
				<?php
				// debug($data_list);exit;
				if (valid_array($review_text) == true) {
					foreach ($review_text as $k => $v) :
						
				?>
					<tr>
						
						<td><?=$v['rating']?></td>
						<td><input type="text"  id="review_text<?php echo $v['origin'];?>" class="form-control" placeholder="Title" name="review_text" value="<?=$v['description'];?>" required></td>
						<td style="float: left;" ><button class="btn save_btn btn-primary btn-sm pull-right amarg" data-val="<?php echo $v['origin'];?>"><i class="fa fa-edit"></i>Update</button>
							
						</td></tr>
					</tr>
				<?php
					endforeach;
				} else {
					echo '<tr><td>No Data Found</td></tr>';
				}
				?>
			</table>
		</div>
	</div><!-- PANEL WRAP END -->
</div>
<script type="text/javascript">
$(document).ready(function(){
	//Get cancellation status and refund details from supplier
	$('.save_btn').click(function(e){
		
		e.preventDefault();
		var value =	$(this).data('val');

		var description = $("#review_text"+value).val();
		var params = {'value' : value, 'description': description};
		$.post('<?=base_url()?>index.php/cms/rating_text', params, function(response){
			
			location.reload();
		});
	});
});
</script>