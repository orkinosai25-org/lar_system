<?php
?>
<div class="b2b_agent_profile">
      <div class="tab-content sidewise_tab">
       
        <div role="tabpanel" class="tab-pane active clearfix" id="profile">
          <div class="dashdiv">
            <div class="alldasbord">
              <div class="userfstep">
              
                <div class="">
                                <a href="<?php echo base_url() ?>management/b2b_balance_manager">
                                    <strong>
                                        <span>Balance</span> : 
                                        <span class="crncy"><?php $balance = agent_current_application_balance();
                    echo agent_base_currency() . ' ' . number_format($balance['value'], '2');
                            ?></span>
                                    </strong></a>
                                    <a href="<?php echo base_url() ?>management/b2b_credit_limit">
                                    <strong>
                                        <span>Credit Limit</span> : 
                                        <span class="crncy"> <?php echo agent_base_currency() . ' ' . number_format($balance['credit_limit'], '2'); ?></span>
                                    </strong>
                                    <strong>
                                        <span>Due Amount</span> : 
                                        <span class="crncy"> <?php echo agent_base_currency() . ' ' . number_format($balance['due_amount'], '2'); ?></span>
                                    </strong>
                                    </a>
                            </div> 
                
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
        </div>
      </div>
</div>
