<?php if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' );
/**
 *
 * @package Provab
 * @subpackage paypal
 * @author Pankaj kumar <pankajprovab212@gmail.com>
 * @version V1
 */
class paypal {
  
  /*
   * Client Live credentials -_-
   * ------------------------------
   * Merchant ID    : 5331200
   * ------------------------------
   * Merchant Key   : sZqbYi
   * Merchant Salt  : eMfHb7uk
   * URL  :https://secure.payu.in
   * ______________________________
   */

  /**
   * Client Test credentials -_-
   * ------------------------------
   * Merchant ID    : 4933825
   * ------------------------------
   * Merchant Key   : 4USjgC
   * Merchant Salt  : SCVEtzhP
   * URL : https://test.payu.in
   * ______________________________
   */

  static $url;
  static $client_email;

  var $active_payment_system;

  var $book_id = '';
  var $book_origin = '';
  var $pgi_amount = '';
  var $firstname = '';
  var $name = '';
  var $email = '';
  var $phone = '';
  var $productinfo = '';
  protected $CI;

  public function __construct() {
    $this->CI = & get_instance ();
    //$this->CI->load->helper('custom/paypal_pgi_helper');
    $this->active_payment_system = $this->CI->config->item('active_payment_system');
  }

  function initialize($data)
  { 


    if ($this->active_payment_system == 'test') {
      self::$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
      
      //self::$client_email = 'sb-li4j818051176@business.example.com';//password : admin@123  7FwDB?m7

    } else {
      //die("carefully its live");
      self::$url = 'https://www.paypal.com/cgi-bin/webscr';
      //self::$client_email = '';
    }
    $this->book_id = $data['txnid'];
    $this->pgi_amount = $data['pgi_amount'];
    $this->firstname = $data['firstname'];
    $this->email = $data['email'];
    $this->phone = $data['phone'];
    $this->productinfo = $data['productinfo'];
  }
  
  function process_payment() {
    $surl = base_url () . 'index.php/payment_gateway/verify/'.$this->book_id.'/'.$this->productinfo;
    $furl = base_url () . 'index.php/payment_gateway/cancel/'.$this->book_id.'/'.$this->productinfo;
    $PAYPAL_BASE_URL = 'https://www.paypal.com/cgi-bin/webscr';
    //$url = $PAYPAL_BASE_URL . '/_payment';
    // $hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10"; 
    $post_data = array ();
    $post_data ['txnid'] = $this->book_id;
    $post_data ['amount'] = $this->pgi_amount;
    $post_data ['firstname'] = $this->firstname;
    $post_data ['email'] = $this->email;
    $post_data ['phone'] = $this->phone;
    $post_data ['productinfo'] = $this->productinfo;
    $post_data ['surl'] = $surl;
    $post_data ['furl'] = $furl;
    $post_data ['service_provider'] = 'Paypal';
    $post_data ['pay_target_url'] = self::$url;
    $post_data ['client_email'] = self::$client_email;
    // /debug($post_data); exit("process_payment");
    return $post_data;
  }

  function process_payment_test($data1){


$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api-m.sandbox.paypal.com/v1/oauth2/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/x-www-form-urlencoded',
    'Authorization: Basic QWJVWUhKYjFkWWFCeVdfQVMyVU9jWWo1N0s2R3R5QnZpX0FWNHk5T2xTS1Q0cnVYa1FLbzE3WE5uQ2xYeXAyZXZzZ3dxT2dESmNTQzIzc0g6RUxubTk2Q0NwbExBSkhuSjczMWdZQkIyeG9kVllUY2U0ZHdWZ3p2dHhiRUxkN0VrcGZrN1Jsd0Q4bVF4aXFXNXd2aE5kbXZZSVpuMFlKR1E='
  ),
));

$response = curl_exec($curl);
$GLOBALS['CI']->custom_db->insert_record('test', array('test' => ($response)));



curl_close($curl);
$test=json_decode($response,true);

//debug($test);
  

$curl1 = curl_init();
$surl = base_url().'index.php/payment_gateway/response';
$order_request='{
      "purchase_units": [
        {
          "amount": {
            "currency_code": "USD",
            "value": "'.$data1['amount'].'"
          },
          "reference_id": "'.$data1['txnid'].'"
        }
      ],
      "intent": "CAPTURE",
      "payment_source": {
        "paypal": {
          "experience_context": {
            "payment_method_preference": "IMMEDIATE_PAYMENT_REQUIRED",
            "payment_method_selected": "PAYPAL",
            "brand_name": "LAR ",
            "locale": "en-US",
            "landing_page": "LOGIN",
            "shipping_preference": "GET_FROM_FILE",
            "user_action": "PAY_NOW",
            "return_url": "'.$surl.'",
            "cancel_url": "'.$surl.'"
          }
        }
      }
}';
curl_setopt_array($curl1, array(
  CURLOPT_URL => 'https://api-m.sandbox.paypal.com/v2/checkout/orders',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>$order_request,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer '.$test['access_token'].''
  ),
));

$response1 = curl_exec($curl1);
$order_response=json_decode($response1,true);
$order_response['id'];
$pg_arr=array(
  'access_toekn' => $test['access_token'],
  'payment_id' =>$order_response['id'],
  'complete_resp' => $response,
  'app_referance'=> $data1['txnid']

);
// debug($pg_arr);die;
$GLOBALS['CI']->custom_db->insert_record('paypal_access_token', $pg_arr);

curl_close($curl);
//debug($order_response);exit;
//$test12121=json_decode($order_response,true);

return $response1;

}
function process_paypal_access_token1($access_token,$data1){


$curl = curl_init();
/*$json_request='{
  "intent": "CAPTURE",
  "payment_source": {
    "paypal": {
      "experience_context": {
        "payment_method_preference": "IMMEDIATE_PAYMENT_REQUIRED",
        "landing_page": "LOGIN",
        "shipping_preference": "GET_FROM_FILE",
        "user_action": "PAY_NOW",
        "return_url": "https://example.com/returnUrl",
        "cancel_url": "https://example.com/cancelUrl"
      }
    }
  },
  "purchase_units": [
    {
      "invoice_id": "90210",
      "amount": {
        "currency_code": "USD",
        "value": "230.00",
        "breakdown": {
          "item_total": {
            "currency_code": "USD",
            "value": "220.00"
          },
          "shipping": {
            "currency_code": "USD",
            "value": "10.00"
          }
        }
      },
      "items": [
        {
          "name": "T-Shirt",
          "description": "Super Fresh Shirt",
          "unit_amount": {
            "currency_code": "USD",
            "value": "20.00"
          },
          "quantity": "1",
          "category": "PHYSICAL_GOODS",
          "sku": "sku01",
          "image_url": "https://example.com/static/images/items/1/tshirt_green.jpg",
          "url": "https://example.com/url-to-the-item-being-purchased-1",
          "upc": {
            "type": "UPC-A",
            "code": "123456789012"
          }
        },
        {
          "name": "Shoes",
          "description": "Running, Size 10.5",
          "sku": "sku02",
          "unit_amount": {
            "currency_code": "USD",
            "value": "100.00"
          },
          "quantity": "2",
          "category": "PHYSICAL_GOODS",
          "image_url": "https://example.com/static/images/items/1/shoes_running.jpg",
          "url": "https://example.com/url-to-the-item-being-purchased-2",
          "upc": {
            "type": "UPC-A",
            "code": "987654321012"
          }
        }
      ]
    }
  ]
}';*/
$surl = base_url().'index.php/payment_gateway/response1';
$order_request='{
      "purchase_units": [
        {
          "amount": {
            "currency_code": "USD",
            "value": "'.$data1['amount'].'"
          },
          "reference_id": "'.$data1['txnid'].'"
        }
      ],
      "intent": "CAPTURE",
      "payment_source": {
        "paypal": {
          "experience_context": {
            "payment_method_preference": "IMMEDIATE_PAYMENT_REQUIRED",
            "payment_method_selected": "PAYPAL",
            "brand_name": "LAR ",
            "locale": "en-US",
            "landing_page": "LOGIN",
            "shipping_preference": "GET_FROM_FILE",
            "user_action": "PAY_NOW",
            "return_url": "'.$surl.'",
            "cancel_url": "'.$surl.'"
          }
        }
      }
}';
$header= array(
    'Content-Type: application/json',
    'Authorization: Bearer '.$access_token.''
  );
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api-m.sandbox.paypal.com/v2/checkout/orders',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'',
  CURLOPT_HTTPHEADER =>$header,
));

$response = curl_exec($curl);

curl_close($curl);
return $response;
}

}
