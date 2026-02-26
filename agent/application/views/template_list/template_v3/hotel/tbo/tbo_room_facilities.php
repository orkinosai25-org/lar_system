<?php

?>
<p><?php echo ucfirst(strtolower($room_name));?></p>
 
<?php if(valid_array($room_facilities)){?>
<div class="room_amnt">
	<h5>Room Facilities</h5>
	<ul>
		<?php foreach($room_facilities as $facility){?>
		<li><?php echo $facility;?></li>
		<?php } ?>
	</ul>               
</div>
<?php } 
if(valid_array($inclusions)){?>
<div class="room_amnt">
	<h5>Room Inclusions</h5>
	<ul>
		<?php foreach($inclusions as $inclusion){?>
		<li><?php echo $inclusion;?></li>
		<?php } ?>
	</ul>               
</div>
<?php }else{?>
<h5>Room Inclusions</h5>
	<ul>
		<li>Room Only</li>
	</ul> 
<?php } ?>
<div class="clearfix"></div>            
<div class="room_amnt hide">
	<h5>Room Policies</h5>
	<ul>
		<li>Inclusions valid for two guests.</li>
		<li>Maximum Room Capacity: Two guests and one infant.</li>
		<li>Infants (under 2): Free of charge</li>
	</ul>              
</div>
<?php if(empty($cancellation_policy) == false){?>
<div class="clearfix"></div> 
<div class="room_amnt">
	<h5>Cancellation Policy</h5>
	<p><?php echo $cancellation_policy;?></p>                
</div>
<?php } ?>
