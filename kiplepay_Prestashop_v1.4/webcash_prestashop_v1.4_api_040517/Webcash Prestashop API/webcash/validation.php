<?php
/**
 * webcash plugin for PrestaShop 

 * $ Author:  
 * $ Id: validation.php 2009-05-23
 */
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/webcash.php');

$webcash = new webcash();
$webcash_name = 'Webcash Online Payment Gateway';

$merchantCode = Configuration::get('webcash_merchantCode');
$merchantKey = Configuration::get('webcash_merchantKey');
       
$RN = $_REQUEST['ord_mercref'];
$RN = explode('-', $RN);

$cart = new Cart(intval($RN[0]));

$amount_cart = number_format($cart->getOrderTotal(), 2, '.', '');

if (round($amount_cart)== $_REQUEST['ord_totalamt']) { $amt_cart = $amount_cart; }


$retcode=$_REQUEST['returncode'];
$amount = $_REQUEST['ord_totalamt'];
$merchantID = $_REQUEST['ord_mercID'];
$referenceNo = $_REQUEST['ord_mercref'];

$amountVal = str_replace(".","",str_replace(",","",$amount));
$hashvalue = sha1($merchantKey.$merchantCode.$referenceNo.$amountVal.$retcode);
  
function Requery($ref, $mercId, $amount){
  $query = "https://webcash.com.my/enquiry.php?ord_mercref=" . $ref . "&ord_mercID=" . $mercId . "&ord_totalamt=" . $amount;

  $response = file_get_contents($query);
  if ($response == 'S') {
    return true;
  }
  return false;
}

if ($retcode == "100" && $_REQUEST['ord_key'] == $hashvalue) {  // successful transaction
    if (Requery($referenceNo, $merchantID, $amount) == true) {
      $webcash->validateOrder(intval($cart->id), _PS_OS_PAYMENT_, $amount_cart, $webcash->displayName);
      Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$webcash->id);    
    } else {
      Tools::redirectLink(__PS_BASE_URI__.'order-failed.php?id_cart='.$cart->id.'&id_module='.$webcash->id);
    }
} else if ($retcode == "E2" || $retcode == "E1") { // failure transaction
  Tools::redirectLink(__PS_BASE_URI__.'order-failed.php?id_cart='.$cart->id.'&id_module='.$webcash->id);

//  Tools::redirectLink(__PS_BASE_URI__.'order.php');

 
//  header('Location: http://meyour.com/presta');
}

?>