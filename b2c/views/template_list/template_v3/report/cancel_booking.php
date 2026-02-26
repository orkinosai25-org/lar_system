<div class="fromtopmargin">
   <div class="container">
      <div class="col-md-12 col-xs-12 pnr-section cncl_bk">
            <h3>Enter the booking reference number for cancel booking</h3>
         <div class="cncl_in clearfix">
           <form autocomplete="off" name="pnr_search" id="pnr_search" action="<?php echo base_url();?>index.php/report/cancel_booking" method="POST" class="activeForm oneway_frm">
		   <div class="col-xs-12 col-md-12 padfive full_smal_tab mobile_width">
		      <div class="lablform">Reference Number</div>
		      <div class="col-xs-12 col-sm-12 nopad">           
		         <input type="text" id="pnr_number" class="cn_input normalinput form-control b-r-0" placeholder="Enter Reference Number" name="pnr_number" required="">          
		      </div>
		   </div>
		   <?php
         		$re = $this->session->flashdata('msg');
         		if($re)
         			{?>
         				<span style="color: red;font-size: larger;"><?=$re;?></span>
         			<?php
         			}
         	?>

	   <div class="col-xs-12 col-md-12 padfive full_smal_tab pnr_module">
	      <div class="form-group">
	         <div class="lablform" for="bus-date-1">Module</div>
	         <div class="input-group">
				 <select required="" dt="" name="module" class="cn_input  module form-control" id="module" data-container="body" data-toggle="popover" data-original-title="Here To Help" data-placement="bottom" data-trigger="hover focus" data-content="Category">
					 <option value="">Plese select type of cancellation</option>
					 <option value="<?php echo PROVAB_FLIGHT_BOOKING_SOURCE; ?>">Flight</option>
					  <option value="<?php echo PROVAB_HOTEL_BOOKING_SOURCE; ?>">Hotel</option>
					  <option value="<?php echo PROVAB_CAR_BOOKING_SOURCE; ?>">Car</option>
				 </select>
	        
	         </div>
	      </div>
	   </div>
	   <div class="col-xs-12 text-center nopad">
	     <div class="searchsbmtfot"><i class="fas fa-search"></i><input type="submit" name="search_flight" id="flight-form-submit" class="searchsbmt flight_search_btn" value="search"></div>
	   </div>
	</form>
         </div>
      </div>
   </div>
</div>