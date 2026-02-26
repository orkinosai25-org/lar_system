<div class="row">
   <div class="bodyContent col-md-12 mis_rep">
      <div class="panel panel-default clearfix">
         <!-- PANEL WRAP START -->
         <div class="panel-heading">
            
         </div>
         <!-- PANEL HEAD START -->
         <div class="panel-body">
            <h4>Search Panel</h4>
            <hr>
            <form action="#" method="GET" autocomplete="off">
               <input type="hidden" name="created_by_id" value="">
               <div class="clearfix form-group">
                 
                  <div class="col-xs-4"><label>Application Reference</label><input type="text" class="form-control" name="app_reference" value="<?php if(isset($_GET['app_reference'])){ echo $_GET['app_reference'];} ?>" placeholder="Application Reference"></div>
                
                  <div class="col-xs-4">
                     <label>Status</label>
                     <select class="form-control" name="status">
                        <option>All</option>
                        Array
                        <option value="BOOKING_CONFIRMED" <?php if($_GET['status']=='BOOKING_CONFIRMED'){ echo 'selected';} ?>>BOOKING_CONFIRMED</option>
                        <option value="BOOKING_HOLD" <?php if($_GET['status']=='BOOKING_HOLD'){ echo 'selected';} ?>>BOOKING_HOLD</option>
                        <option value="BOOKING_CANCELLED" <?php if($_GET['status']=='BOOKING_CANCELLED'){ echo 'selected';} ?>>BOOKING_CANCELLED</option>
                        <option value="BOOKING_ERROR" <?php if($_GET['status']=='BOOKING_ERROR'){ echo 'selected';} ?>>BOOKING_ERROR</option>
                        <option value="BOOKING_PENDING" <?php if($_GET['status']=='BOOKING_PENDING'){ echo 'selected';} ?>>BOOKING_PENDING</option>
                        <option value="BOOKING_FAILED" <?php if($_GET['status']=='BOOKING_FAILED'){ echo 'selected';} ?>>BOOKING_FAILED</option>
                     </select>
                  </div>
                  <div class="col-xs-4"><label>Booked From Date</label><input type="date"  class="form-control" name="created_datetime_from" value="<?php if(isset($_GET['created_datetime_from'])){ echo $_GET['created_datetime_from'];} ?>" placeholder="Request Date"></div>
                  <div class="col-xs-4"><label>Booked To Date</label><input type="date"  class="form-control" name="created_datetime_to" value="<?php if(isset($_GET['created_datetime_to'])){ echo $_GET['created_datetime_to'];} ?>" placeholder="Request Date"></div>
               </div>
               <div class="col-sm-12 well well-sm"><button type="submit" class="btn btn-primary">Search</button> <a href="<?php echo base_url();?>index.php/report/b2c_hotel_report" id="s-clear-filter" class="btn btn-warning">Clear Filter</a><!-- <a href="#" target="_blank" class="btn btn-info mis_rep_btn">MIS Export Excel</a> --></div>
            </form>
         </div>
        
          
         <div id="airlineList" class="clearfix table-responsive col-sm-12">
            <!-- PANEL BODY START -->
            <div class="pull-left">
             <?php echo $GLOBALS['CI']->pagination->create_links();?>            
            </div>
             <span class="totl_bkg">Total <?php echo $total_rows;?> Bookings</span>
            <table class="table table-condensed table-bordered rpt_flgt">
               <tbody>
                  <tr>
                     <th>Sno</th>
                     <th>Application Reference</th>
                     <th>Customer Name</th> 
                     <th>Hotel Name</th>
                     <th>Room Type</th>
					 <th>No. of rooms<br/>(Adult + Child)</th>
                     <th>City</th>
                     <th>CheckIn/CheckOut</th>
                     <th>Booked On</th>
                     <th>Status</th>                    
                     <!-- <th>Action</th> -->
                  </tr>
<?php
                  if (isset($table_data) == true and valid_array($table_data['booking_details']) == true) {
			$segment_3 = $GLOBALS['CI']->uri->segment(3);
			$current_record = (empty($segment_3) ? 1 : $segment_3);
			$booking_details = $table_data['booking_details'];
			
			//debug($booking_details);exit;
		    foreach($booking_details as $parent_k => $parent_v) { 
		    	$roomTypeCounts = [];
		    	extract($parent_v);

		    	foreach ($itinerary_details as $itinerary_detail) {
				    // Extract room type name
				    $roomTypeName = $itinerary_detail['room_type_name'];

				    // Increment the count for the room type
				    if (isset($roomTypeCounts[$roomTypeName])) {
				        $roomTypeCounts[$roomTypeName]++;
				    } else {
				        $roomTypeCounts[$roomTypeName] = 1;
				    }
				}


		    	$action = '';
				?>
			
		<tr>
					<td><?php echo $current_record++;?></td>
					<td><?php echo $app_reference;?></td>
					<td><?php echo $customer_details[0]['title'];?> <?php echo $customer_details[0]['first_name'];?> <?php echo $customer_details[0]['last_name'];?></td>
					<td><?php echo $hotel_name;?></td>
					<td><?php 
						foreach ($roomTypeCounts as $roomTypeName => $count) {
						    echo $count.'*'.$roomTypeName.'<br>';
						}
					;?></td>
					<td><?php echo $total_rooms;?><br/>(<?php echo $adult_count;?>+<?php echo $child_count;?>)</td>
					<td><?php echo $hotel_location;?></td>
					
					<td><?php echo app_friendly_absolute_date($hotel_check_in);?>/<br/><?php echo app_friendly_absolute_date($hotel_check_out);?></td>
					<td><?php echo $voucher_date;?></td>
					<td><span class=""><?php echo $status;?></span></td>
					<!-- <td><div class="" role="group"><button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#myModal">Cancellation</button></div></td> -->
				</tr>
				<?php
			}
		} else {
			?>
			<tr>
				<td>No Data Found</td>
			</tr>
			<?php
				}
               
				?>

               </tbody>
            </table>
         </div>
      </div>
   </div>       
</div>
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
     
      <div class="modal-body">
      	<label>Reason for Cancellation</label>
       <textarea class="form-control"></textarea>
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-primary" data-dismiss="modal">Submit</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>