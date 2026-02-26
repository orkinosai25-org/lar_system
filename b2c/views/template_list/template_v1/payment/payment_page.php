<?php
$time_stamp = date("Y-m-d H:i:s");
$password = '4VVv!!vg3bd[';
$site_url = 'test_site12345';
$site_test = 'USD'.$pgi_amount.$site_url.$time_stamp.$password;
$secure_hash = hash("sha256", $site_test);
?>
<html>
  <body>
    <form method="POST" id="payment_form" action="https://payments.securetrading.net/process/payments/details">
      <input type="hidden" name="sitereference" value="test_luxuryreso137407">
      <input type="hidden" name="currencyiso3a" value="USD">
      <input type="hidden" name="mainamount" value="<?php echo $pgi_amount;?>">
      <input type="hidden" name="billingfirstname" value="<?php echo $firstname?>">
      <input type="hidden" name="billinglastname" value="<?php echo $last_name?>">
      <input type="hidden" name="billingemail" value="<?php echo $email?>">
      <input type="hidden" name="strequiredfields" value="billingfirstname">
      <input type="hidden" name="strequiredfields" value="billinglastname">
      <input type="hidden" name="strequiredfields" value="billingemail">
      <input type="hidden" name="ruleidentifier" value="STR-6">
      <input type="hidden" name="successfulurlredirect" value="http://localhost/anitha_projects/lar_system_new/index.php/payment_gateway/trust_success">
      <input type="hidden" name="ruleidentifier" value="STR-8">
      <input type="hidden" name="successfulurlnotification" value="http://localhost/anitha_projects/lar_system_new/index.php/payment_gateway/trust_successl">
      <input type="hidden" name="ruleidentifier" value="STR-9">
      <input type="hidden" name="declinedurlnotification" value="http://localhost/anitha_projects/lar_system_new/index.php/payment_gateway/trust_cancel">
      <input type="hidden" name="version" value="2">
      <input type="hidden" name="stprofile" value="default">
      <input type="hidden" name="stdefaultprofile" value="st_cardonly">
      <input type="hidden" name="sitesecurity" value="<?php echo $secure_hash;?>">
      <input type="hidden" name="sitesecuritytimestamp" value="<?php echo $time_stamp;?>">
      <input type="submit" value="Pay">
    </form>
  </body>
</html>
<script>
    // Automatically submit the form once the page loads
    window.onload = function() {
        document.getElementById('payment_form').submit();
    };
</script>