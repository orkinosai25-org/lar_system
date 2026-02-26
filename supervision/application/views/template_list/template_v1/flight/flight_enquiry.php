<div id="enquiries" class="bodyContent col-md-12">
	<div class="panel panel-default">
		<!-- PANEL WRAP START -->
		<div class="panel-heading">
			<!-- PANEL HEAD START -->
			<div class="panel-title">
				<ul class="nav nav-tabs nav-justified" role="tablist" id="myTab">
					<!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE START-->
					<li role="presentation" class="active"><a href="#fromList"
						aria-controls="home" role="tab" data-toggle="tab"><h1>View Enquiries </h1></a></li>
					<!-- INCLUDE TAB FOR ALL THE DETAILS ON THE PAGE END -->
				</ul>
			</div>
		</div>
		<!-- PANEL HEAD START -->
		<div class="panel-body">
			<!-- PANEL BODY START -->
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane active" id="fromList">
					<div class="col-md-12">
						<div class='row'>
						
						<div class='row'>
                <div class='col-sm-14'>
                  <div class='' style='margin-bottom:0;'>
                    <div class=''>
                      <div class='responsive-table'>
                        <div class='scrollable-area'>
                          <table class='data-table-column-filter table table-bordered table-striped' style='margin-bottom:0;'>
                            <thead>
                              <tr>
                              <th>S.No</th>
                              <th>From</th>
                            <th>To</th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Pax Count</th>
                            <th>Phone Number</th>
                              </tr>
                            </thead>
                            <tbody>
                          <?php 
                        //debug($enquiries);exit;
                          $count = 1; 
                        foreach($enquiries as $key => $package) { 
                           
                           // debug($package);exit;
                         
                            // strip tags to avoid breaking any html
                           
                          ?>
                      <tr>
                        <td><?php echo $count; ?></td>
                        <td><?php echo $package->from; ?></td>
                        <td><?php echo $package->to; ?></td>
                        <td><?php echo $package->date; ?></td>
                        <td><?php echo $package->name; ?></td>
                      
                        <td><?php echo $package->email; ?></td> 
                         <td><?php echo $package->adult; ?></td> 
                         <td><?php echo $package->country_code.'-'.$package->phone_no; ?></td> 
                      </tr>   
                  <?php  
                  $count++; 
                  } ?>  
                      </tbody>
                          </table>
                        </div>
                      </div> 
                    </div>
                  </div>
                </div>
              </div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- PANEL BODY END -->
	</div>
	<!-- PANEL WRAP END -->
</div>
<!-- Modal -->
  
<script type="text/javascript">
$(document).ready(function(){
    $('.openPopup').on('click',function(){
        var dataURL = $(this).attr('data-href');
        
            $('#myModal').modal({show:true});
       
    }); 
});
</script>
