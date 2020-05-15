<?php
/**
 * Kiple plugin for PrestaShop 

 * $ Author: 
 * $ Id: webcash.php 2009-05-23
 */
class webcash extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'webcash';
		$this->tab = 'payments_gateways';
		$this->version = '2.0 (Aug 2016)';

		$this->currencies = true;
		$this->currencies_mode = 'radio';

        parent::__construct();

        /* The parent construct is required for translations */
		$this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Kiple');
        $this->description = $this->l('Accepts payments by Kiple');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
	}

    // the submit url is here !!//
	public function getwebcashUrl()
	{
		$url = "https://uat.kiplepay.com/wcgatewayinit.php";  // Production URL -> https://kiplepay.com/wcgatewayinit.php
    	return $url;   
	}

	public function install()
	{
		if (!parent::install() OR !Configuration::updateValue('webcash_merchantCode', '')
			OR !Configuration::updateValue('webcash_merchantKey','') OR !Configuration::updateValue('webcash_SANDBOX', 1)
            OR !$this->registerHook('payment'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('webcash_merchantCode') OR !Configuration::deleteByName('webcash_merchantKey')
			OR !parent::uninstall())
			return false;
		return true;
	}

	public function getContent()
	{
		$this->_html = '<h2>Kiple</h2>';
		if (isset($_POST['submitwebcash']))
		{
			if (empty($_POST['merchantCode']))
				$this->_postErrors[] = $this->l('Kiple <u><b>Merchant ID</b></u> is required !');
            if (empty($_POST['merchantKey']))
				$this->_postErrors[] = $this->l('Kiple <u><b>Merchant Key</b></u> is required !</br>');

			if (!sizeof($this->_postErrors))
			{
				Configuration::updateValue('webcash_merchantCode', $_POST['merchantCode']);
				Configuration::updateValue('webcash_merchantKey', $_POST['merchantKey']);
				$this->displayConf();
			}
			else
				$this->displayErrors();
		}

		$this->displaywebcash();
		$this->displayFormSettings();
		return $this->_html;
	}

	public function displayConf()
	{
		$this->_html .= '
		<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
			'.$this->l('Settings updated').'
		</div>';
	}

	public function displayErrors()
	{
		$nbErrors = sizeof($this->_postErrors);
		$this->_html .= '
		<div class="alert error">
			<h3>'.($nbErrors > 1 ? $this->l('There are') : $this->l('There is')).' '.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors') : $this->l('error')).'</h3>
			<ol>';
		foreach ($this->_postErrors AS $error)
			$this->_html .= '<li>'.$error.'</li>';
		$this->_html .= '
			</ol>
		</div>';
	}
	
	
	public function displaywebcash()
	{
		$this->_html .= '
		<img src="../modules/webcash/weblogo.gif" style="float:left; margin-right:15px;" />
		<b>'.$this->l('This module allows you to accept payments by Kiple.').'</b><br /><br />
		<br /><br /><br />';
	}

	public function displayFormSettings()
	{
		$conf = Configuration::getMultiple(array('webcash_merchantCode', 'webcash_merchantKey'));
		$merchantCode = array_key_exists('merchantCode', $_POST) ? $_POST['merchantCode'] : (array_key_exists('webcash_merchantCode', $conf) ? $conf['webcash_merchantCode'] : '');
		$merchantKey = array_key_exists('merchantKey', $_POST) ? $_POST['merchantKey'] : (array_key_exists('webcash_merchantKey', $conf) ? $conf['webcash_merchantKey'] : '');

		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Settings').'</legend>

            <label>'.$this->l('Kiple Merchant ID : ').'</label>
			<div class="margin-form"><input type="text" size="33" name="merchantCode" value="'.htmlentities($merchantCode, ENT_COMPAT, 'UTF-8').'" /></div>

            <label>'.$this->l('Kiple Merchant Key  : ').'</label>
			<div class="margin-form"><input type="text" size="33" name="merchantKey" value="'.htmlentities($merchantKey, ENT_COMPAT, 'UTF-8').'" /></div>



            <br /><center><input type="submit" name="submitwebcash" value="'.$this->l('Update settings').'" class="button" /></center>
		</fieldset>
		</form><br /><br />
		<fieldset class="width3">
			<legend><img src="../img/admin/warning.gif" />'.$this->l('Information').'</legend>
			'.$this->l('In order to use your Kiple payment module, you have to configure your kiple account. Website : https://kiplepay.com').'<br /><br />

		</fieldset>';
	}

	
	function webcash_signature($source)
	{
		return base64_encode(hex2bin(sha1($source)));
	}

	function hex2bin($hexSource){
	$strlen = strlen($hexSource);
	for ($i=0;$i<strlen($hexSource);$i=$i+2){
		$bin .= chr(hexdec(substr($hexSource,$i,2)));
	}
	return $bin;
	}

	
	public function hookPayment($params)
	{
		global $smarty,$cart, $cookie;

		$address = new Address(intval($params['cart']->id_address_invoice));
		$customer = new Customer(intval($params['cart']->id_customer));
		$merchantCode = Configuration::get('webcash_merchantCode');
        $merchantKey = Configuration::get('webcash_merchantKey');
		$currency = $this->getCurrency();
		


		if (!Validate::isLoadedObject($address) OR !Validate::isLoadedObject($customer) OR !Validate::isLoadedObject($currency))
			return $this->l('kiple error: (invalid address or customer)');

		$products = $params['cart']->getProducts();

		foreach ($products as $key => $product)
		{
			$products[$key]['name'] = str_replace('"', '\'', $product['name']);
			if (isset($product['attributes']))
				$products[$key]['attributes'] = str_replace('"', '\'', $product['attributes']);
				$products[$key]['name'] = htmlentities(utf8_decode($product['name']));
				$products[$key]['description_short'] = htmlentities(utf8_decode($product['description_short']));
				$products[$key]['webcashAmount'] = number_format(Tools::convertPrice($product['price_wt'], $currency), 2, '.', '');
		}
		
		$RN = intval($params['cart']->id);
		$AMT = number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 4), $currency), 2, '.', '')+ number_format(Tools::convertPrice(($params['cart']->getOrderShippingCost() + $params['cart']->getOrderTotal(true, 6)), $currency), 2, '.', '');

		$HashAmount = str_replace(".","",str_replace(",","",$AMT));
		$str = sha1($merchantKey . $merchantCode . $RN . $HashAmount . "MYR");
		
		for ($i=0;$i<strlen($str);$i=$i+2)
		{
        $webcashSignature .= chr(hexdec(substr($str,$i,2)));
		}
     
        $sg = base64_encode($webcashSignature);

	$amountVal = str_replace(".","",str_replace(",","",$AMT));
	$cart_order_id = $RN . '-' . uniqid();
	$hashvalue = sha1($merchantKey.$merchantCode.$cart_order_id.$amountVal);
			
		$smarty->assign(array(
			'MerchantCode' 	=> $merchantCode,
            'HashKey' 	=> $hashvalue,
			'RefNo'			=> $cart_order_id,
			'Amount'		=> $AMT,
			'Currency' 		=> $Currency,
			'ProdDesc' 		=> $products[$key]['name'],
			'UserName' 		=> $cookie->customer_firstname,
			'UserEmail' 	=> $cookie->email,
			'Remark' 		=> $products[$key]['name'],
			'Lang' 			=> "UTF-8",
			'Signature' 	=> $sg,
			'webcashUrl' 	=> $this->getwebcashUrl(),
			'shipping' 		=> number_format(Tools::convertPrice(($params['cart']->getOrderShippingCost() + $params['cart']->getOrderTotal(true, 6)), $currency), 2, '.', ''),
			'discounts' 	=> $params['cart']->getDiscounts(),
			'id_cart' 		=> intval($params['cart']->id),
			'goBackUrl' 	=> 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.intval($params['cart']->id).'&id_module='.intval($this->id),
			'returnUrl' 	=> 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/webcash/validation.php',
			'this_path' 	=> $this->_path
		));
		

		return $this->display(__FILE__, 'webcash.tpl');
    }

	public function getL($key)
	{
		$translations = array(
			'mc_gross' => $this->l('kiple key \'mc_gross\' not specified, can\'t control amount paid.'),
			'payment_status' => $this->l('kiple key \'payment_status\' not specified, can\'t control payment validity'),
			'payment' => $this->l('Payment: '),
			'custom' => $this->l('kiple key \'custom\' not specified, can\'t rely to cart'),
			'txn_id' => $this->l('kiple key \'txn_id\' not specified, transaction unknown'),
			'mc_currency' => $this->l('kiple key \'mc_currency\' not specified, currency unknown'),
			'cart' => $this->l('Cart not found'),
			'order' => $this->l('Order has already been placed'),
			'transaction' => $this->l('kiple Transaction ID: '),
			'verified' => $this->l('The kiple transaction could not be VERIFIED.'),
			'connect' => $this->l('Problem connecting to the kiple server.'),
			'nomethod' => $this->l('No communications transport available.'),
			'socketmethod' => $this->l('Verification failure (using fsockopen). Returned: '),
			'curlmethod' => $this->l('Verification failure (using cURL). Returned: '),
			'curlmethodfailed' => $this->l('Connection using cURL failed'),
		);
		return $translations[$key];
	}
}

?>