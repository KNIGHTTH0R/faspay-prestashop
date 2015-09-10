<?php

if (!defined('_PS_VERSION_'))
	exit;

include_once(dirname(__FILE__).'/mid.php');

class FaspayCc extends PaymentModule {

	public 	$extra_mail_vars;
	public 	$midlist;
	private $_html = '';
	private $_postErrors = array();
	private $status;
	private $urlserver = "https://ucdev.faspay.co.id/payment/PaymentWindow.jsp";
	private $urlinterface = "https://ucdev.faspay.co.id/payment/PaymentInterface.jsp";
	private $_faspaycc = array('status' => 0, 'merchant_name' => null, 'auto_void' => false, 'server' => false);
	private $enabled = false;
	
	public function __construct() {
		$this->name 	= 'faspaycc';
		$this->tab 		= 'payments_gateways';
		$this->version 	= '1.0.0';
		$this->author  	= 'Media Indonusa';
		$this->limited_countries = array('id');
		$this->bootstrap = true;
		$this->displayBackOfficeTop = '';
		$this->displayBackOfficeHeader = false;

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		parent::__construct();
		$this->displayName = 'Faspay Credit Card';
		$this->description = 'Faspay Payment Aggregator - Transaction secure, anytime, and anywhere';
		$this->confirmUninstall = $this->l('Are you sure you want to delete faspay account ?');
		
		if(Configuration::get('FASPAY_CC_STATUS') == 1){			
			$this->_getMids();
			$this->_setInitialValue();
		}
		if (!count($this->midlist))
			$this->warning = $this->l('There must be at least one merchant id for this module');
		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency set for this module');
	}

	public function install() {
		Configuration::updateValue('FASPAY_CC_STATUS', 0);
		Configuration::updateValue('FASPAY_CC_MERCHANT_NAME', null);
		Configuration::updateValue('FASPAY_CC_SERVER', 0);
		Configuration::updateValue('FASPAY_CC_AUTO_VOID', 0);

		$orderstate = $this->_addFaspayOS();
		Configuration::updateValue('PS_OS_FASPAY_CC_PENDING', $orderstate);

		if (!parent::install() ||
			!$this->registerHook('payment') ||
			!$this->registerHook('paymentReturn') ||
			!$this->_createTabs()) return false;
		return true;
	}
	
	public function uninstall() {
		if (!$this->_removeFaspaycc() || !parent::uninstall()) return false;
		else return true;
	}

	private function _updateMid(){
		
		$mids = array('status' 	 => Tools::getValue('cc_status'),
					  'name' 	 => Tools::getValue('cc_title'),
					  'mid'	 	 => Tools::getValue('cc_mid'),
					  'password' => Tools::getValue('cc_pass'),
					  'pymt_ind' => Tools::getValue('cc_pymt_ind'),
					  'pymy_crt' => Tools::getValue('cc_pymy_crt'));

		Mid::truncate();
		for ($i = 0;$i < count($mids['status']);$i++) {
			$mid = new Mid();

			$mid->status = $mids['status'][$i] == 'on' ? true : false;
			$mid->mid = $mids['mid'][$i];
			$mid->password = $mids['password'][$i];
			$mid->name = $mids['name'][$i];
			$mid->pymt_ind = $mids['pymt_ind'][$i];
			$mid->pymt_crt = $mids['pymy_crt'][$i];
			
			$mid->add();
		}

		$this->_getMids();
	}

	private function _updateConfig(){
		$enabled 	= Tools::getValue('enabled');
		$name 		= Tools::getValue('merchant_name'); 
		$server 	= Tools::getValue('server');
		$auto_void 	= Tools::getValue('auto_void') == "on" ? true : false;

		Configuration::updateValue('FASPAY_CC_STATUS', $enabled);
		Configuration::updateValue('FASPAY_CC_MERCHANT_NAME', $name);
		Configuration::updateValue('FASPAY_CC_SERVER', $server);
		Configuration::updateValue('FASPAY_CC_AUTO_VOID', $auto_void);

		$this->_setInitialValue();
	}

	private function _deleteTabs(){
		Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'faspay_cc_config');
		return true;
	}

	private function _createTabs(){
		$query = "CREATE TABLE `"._DB_PREFIX_."faspay_cc_config` (
		  `mid` varchar(32) DEFAULT NULL,
		  `password` varchar(32) DEFAULT NULL,
		  `name` varchar(32) DEFAULT NULL,
		  `pymt_ind` varchar(32) DEFAULT NULL,
		  `pymt_crt` varchar(32) NULL DEFAULT NULL,
		  `status` boolean DEFAULT NULL
		) ENGINE = MyISAM";

		Db::getInstance()->Execute($query);
		return true;
	}

	public function getContent() {
		global $smarty;
		
		if (Tools::isSubmit('btnSubmit')) {
			$this->_updateMid();
			$this->_updateConfig();
		}

		$current_url = $this->context->link->getAdminLink('AdminModules').'&configure=faspaycc&tab_module=payments_gateways&module_name=faspaycc';
		
		$smarty->assign('config', $this->_faspaycc);
		$smarty->assign('mids', $this->midlist);
		$smarty->assign('current_url', $current_url);

		return $this->display(dirname(__FILE__), '../admin/config.tpl');
	}

	private function _setInitialValue(){
		$config = Configuration::getmultiple(array('FASPAY_CC_STATUS',
												   'FASPAY_CC_MERCHANT_NAME',
												   'FASPAY_CC_SERVER',
												   'FASPAY_CC_AUTO_VOID'));

		if($config['FASPAY_CC_STATUS'])			$this->_faspaycc['status'] = $config['FASPAY_CC_STATUS'];
		if($config['FASPAY_CC_MERCHANT_NAME'])	$this->_faspaycc['merchant_name'] = $config['FASPAY_CC_MERCHANT_NAME'];
		if($config['FASPAY_CC_SERVER'])			$this->_faspaycc['server'] = $config['FASPAY_CC_SERVER'];
		if($config['FASPAY_CC_AUTO_VOID'])		$this->_faspaycc['auto_void'] = $config['FASPAY_CC_AUTO_VOID'];
		if($this->_faspaycc["server"] == 1)  
		{
			$this->urlserver = "https://uc.faspay.co.id/payment/PaymentWindow.jsp";
			$this->urlinterface = "https://uc.faspay.co.id/payment/PaymentInterface.jsp";
		}
		foreach ($this->midlist as $key => $value) {
			if($value['status'] == 1) $this->enabled = true;
		}
	}

	private function _getMids(){
		$query = "SELECT * FROM "._DB_PREFIX_."faspay_cc_config";
		$this->midlist = Db::getInstance()->Executes($query);

	}

	public function hookPayment($params) {
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;

		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
			'midlist' => $this->midlist,
			'pgexist'=> $this->enabled
		));

		return $this->display(__FILE__, 'payment.tpl');
	}	

	public function hookPaymentReturn($params){

		$state = $params['objOrder']->getCurrentState();
		$currency 	= $this->context->currency;

		$cust	= new Customer($params['objOrder']->id_customer);
		
		$channel = Tools::getValue('channel');
		$merchant = $this->selectMidByName($channel);

		$data = $this->prep($params, $merchant);

		global $smarty;

		if ($state == Configuration::get('PS_OS_BANKWIRE') || $state == Configuration::get('PS_OS_OUTOFSTOCK')) {
			$smarty->assign(array('url'=> $this->urlserver ,
								  'state' => 'ok', 
								  'this_path' => $this->_path));

			$smarty->assign('data', $data);
		} else {
			$smarty->assign('state', 'not ok');
		}

		return $this->display(__FILE__, 'payment_processor.tpl');

	}

	public function checkCurrency($cart) {
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}

	private function prep($params, $merchant){

		$cust	= new Customer($params['objOrder']->id_customer);
		$tranid = $params['objOrder']->id;
	  	$total = ((float) $params['total_to_pay']) . '.00';
		$shipping = ((float) $params['objOrder']->total_shipping) . '.00';
		$shopname = $this->context->shop->name;
		$signaturecc = sha1('##'.strtoupper($merchant['mid']).'##'.strtoupper($merchant['password']).'##'.$tranid.'##'.$total.'##'.'0'.'##');

	  	$delivery_address	= $this->getOrderAddress($params['objOrder']->id_address_delivery);
		$billing_address	= $this->getOrderAddress($params['objOrder']->id_address_invoice);

		if($params['objOrder']->id_address_delivery == $params['objOrder']->id_address_invoice)
			$billing_address = $delivery_address;
		else
			$billing_address = $this->getOrderAddress($params['objOrder']->id_address_invoice);

		$data['LANG'] = "";
		$data['MERCHANTID'] = $merchant['mid'];
		$data['PAYMENT_METHOD'] = '1';
		$data['TXN_PASSWORD'] = $merchant['password'];
		$data['MERCHANT_TRANID'] = $tranid;// $params['objOrder']->id;
		$data['CURRENCYCODE'] = "IDR";
		$data['AMOUNT'] = $total;
		$data['CUSTNAME'] = $cust->firstname.' '.$cust->lastname;
		$data['CUSTEMAIL'] = $cust->email;
		$data['DESCRIPTION'] = "Pembelian Barang di $shopname";
		$data['RETURN_URL'] = $this->context->link->getModuleLink('faspaycc', 'status');
		$data['SIGNATURE'] = $signaturecc;
		$data['BILLING_ADDRESS'] = $billing_address['address1'];
		$data['BILLING_ADDRESS_CITY'] = $billing_address['city'];
		$data['BILLING_ADDRESS_REGION'] = $billing_address['region'];
		$data['BILLING_ADDRESS_POSCODE'] = $billing_address['postcode'];
		$data['BILLING_ADDRESS_COUNTRY_CODE'] = $billing_address['iso_code'];
		$data['RECEIVER_NAME_FOR_SHIPPING'] = $billing_address['firstname'].' '.$billing_address['lastname'];
		$data['SHIPPING_ADDRESS'] = $delivery_address['address1'];
		$data['SHIPPING_ADDRESS_CITY'] = $delivery_address['city'];
		$data['SHIPPING_ADDRESS_REGION'] = $delivery_address['region'];
		$data['SHIPPING_ADDRESS_STATE'] = $delivery_address['city'];
		$data['SHIPPING_ADDRESS_POSCODE'] = $delivery_address['postcode'];
		$data['SHIPPING_ADDRESS_COUNTRY_CODE'] = $delivery_address['iso_code'];
		$data['SHIPPINGCOST'] = $shipping;
		$phone = ($billing_address['phone_mobile'] != '' ? 
			$billing_address['phone_mobile'] : $billing_address['phone']);
		$data['PHONE_NO'] = $phone;
		$data['PYMT_IND'] = $merchant['pymt_ind'];
		$data['PYMT_CRITERIA'] = $merchant['pymt_crt'];
		
		return $data;
	}

	private function selectMidByName($mid){
		$query = "SELECT * FROM "._DB_PREFIX_."faspay_cc_config WHERE name = '".$mid."'"; 
		return Db::getInstance()->executeS($query)[0];
	}
	private function selectMidById($mid){
		$query = "SELECT * FROM "._DB_PREFIX_."faspay_cc_config WHERE mid = '".$mid."'"; 
		return Db::getInstance()->executeS($query)[0];
	}

	public function getOrderAddress($id_adresss){
		
		$sql_address = "SELECT a.phone_mobile, a.lastname, a.firstname, a.phone, a.phone_mobile,c.iso_code , cl.`name` as region,s.name as city, a.`postcode`, a.address1 
            		FROM `"._DB_PREFIX_."address` a
            		LEFT JOIN `"._DB_PREFIX_."country` c ON (a.`id_country` = c.`id_country`)
            		LEFT JOIN `"._DB_PREFIX_."country_lang` cl ON (c.`id_country` = cl.`id_country`)
            		LEFT JOIN `"._DB_PREFIX_."state` s ON (s.`id_state` = a.`id_state`)
            		WHERE `id_lang` = 1 AND `id_address` = ".$id_adresss;
		return Db::getInstance()->getRow($sql_address);
	}

	public function doPayment($data){

		$MERCHANT_TRANID = $data["MERCHANT_TRANID"];

		if( $data["TXN_STATUS"]=="F" ){
			// F = gagal
			$this->processPayment($MERCHANT_TRANID, Configuration::get('PS_OS_ERROR'));
		}
		elseif($this->_faspaycc['auto_void'] == "on" && $data["TXN_STATUS"]=="S" && strtoupper($data["EXCEED_HIGH_RISK"])=="YES" )
		{
			$void = $this->_autoVoid($data);

			if($void["TXN_STATUS"] == "V"){		
					$this->processPayment($MERCHANT_TRANID, Configuration::get('PS_OS_ERROR'));
				} else{
					$this->processPayment($MERCHANT_TRANID, Configuration::get('PS_OS_WS_PAYMENT'));
				}
		}
		elseif( $data["TXN_STATUS"]=="A" && strtoupper($data["EXCEED_HIGH_RISK"])=="NO" ){

				$inquiry 	= $this->_inquiry($data);
				
				if($inquiry["TXN_STATUS"] == "A" || $inquiry["TXN_STATUS"] == "CR"){
					
					$this->processPayment($MERCHANT_TRANID, Configuration::get('PS_OS_FASPAY_CC_PENDING'));
				} else{
					$this->processPayment($MERCHANT_TRANID, Configuration::get('PS_OS_WS_PAYMENT'));
				}
		}
		elseif( $data["TXN_STATUS"]=="A" && strtoupper($data["EXCEED_HIGH_RISK"])=="YES" ){
				$this->processPayment($MERCHANT_TRANID, Configuration::get('PS_OS_FASPAY_CC_PENDING'));
		}	
		elseif($data["TXN_STATUS"] == "S" && strtoupper($data["EXCEED_HIGH_RISK"])=="NO"){
			// S = Sales
			$this->processPayment($MERCHANT_TRANID, Configuration::get('PS_OS_WS_PAYMENT'));
		}

		$responseorder = new Order((int)$MERCHANT_TRANID);

		return $responseorder;
	}

	private function processPayment($id_order, $id_order_state){
		$order = new Order((int)$id_order);

		if (!Validate::isLoadedObject($order))
		{
			$this->errors[] = sprintf(Tools::displayError('Order #%d cannot be loaded'), $id_order);
		}
		else
		{
			$current_order_state = $order->getCurrentOrderState();
			$order_state = new OrderState($id_order_state);
			
			if($current_order_state->id == $order_state->id){
				$response['message'] = "This order has been processed";
			}else{

				$history = new OrderHistory();
				$history->id_order = $order->id;
				$history->changeIdOrderState((int)$order_state->id, $order);
				$history->add();
			}
		}
	}

	private function _autoVoid($data){
		$merchant = $this->selectMidById($data['MERCHANTID']);

		$sign = sha1('##'.strtoupper($data["MERCHANTID"]).'##'.strtoupper($merchant['password']).'##'.$data["MERCHANT_TRANID"].'##'.$data["AMOUNT"].'##'.$data["TRANSACTIONID"].'##');
		
		$post = array(
			"PAYMENT_METHOD"		=> '1',
			"TRANSACTIONTYPE"		=> '10',
			"MERCHANTID"			=> $data["MERCHANTID"],
			"MERCHANT_TRANID"		=> $data["MERCHANT_TRANID"],
			"TRANSACTIONID"			=> $data["TRANSACTIONID"],
			"AMOUNT"				=> $data["AMOUNT"],
			"RESPONSE_TYPE"			=> '3',
			"SIGNATURE"				=> $sign
		);

		$response = $this->_postData($post);
		$lines	= explode(';',$response);
		$response = array();

		foreach ($lines as $key) {
			list($key, $value) = explode('=', $key);
			$response[trim($key)] = trim($value);
		}

		return $response;
	}

	private function _inquiry($data){
		$merchant = $this->selectMidById($data['MERCHANTID']);

		$sign = sha1('##'.strtoupper($merchant['mid']).'##'.strtoupper($merchant['password']).'##'.$data["MERCHANT_TRANID"].'##'.$data["AMOUNT"].'##0##');

		$post = array(
			"TRANSACTIONTYPE"		=> '4',
			"MERCHANTID" 			=> $merchant['mid'],
			"MERCHANT_TRANID"		=> $data["MERCHANT_TRANID"],
			"AMOUNT"				=> $data["AMOUNT"],
			"RESPONSE_TYPE"			=> '3',
			"SIGNATURE"				=> $sign
			);

		$response = $this->_postData($post);
		$lines	= explode(';',$response);
		$response = array();
		foreach ($lines as $key) {
			list($key, $value) = explode('=', $key);
			$response[trim($key)] = trim($value);
		}

		return $response;
	}

	private function _postData($post){
		$data = http_build_query($post);

		$ch = curl_init();
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt($ch, CURLOPT_URL, $this->urlinterface);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$result	= curl_exec($ch);
		curl_close($ch);

		return $result;
	}

	private function _addFaspayOS(){
		$orderState = new OrderState(14);
		// $orderstate->id = 14;
		foreach (Language::getLanguages() AS $language)
			{
				if (strtolower($language['iso_code']) == 'id')
				$orderState->name[$language['id_lang']] = 'Payment Pending Hubungi Administrator';
				else
					$orderState->name[$language['id_lang']] = 'Payment Pending Contact Administrator';
			}

		$OrderState->unremovable = false;
		$orderState->send_email = false;
		$orderState->color = '#DDEEFF';
		$orderState->hidden = false;
		$orderState->delivery = false;
		$orderState->logable = true;
		$orderState->invoice = true;
		if ($orderState->add())
			return $orderState->id;
	}

	private function _removeFaspaycc(){

		$orderState = new OrderState(Configuration::get('PS_OS_FASPAY_CC_PENDING'));
		!Configuration::deleteByName('FASPAY_CC_STATUS');
		
		if ($orderState->delete() || 
			Configuration::deleteByName('FASPAY_CC_STATUS') ||
			Configuration::deleteByName('FASPAY_CC_MERCHANT_NAME') ||
			Configuration::deleteByName('FASPAY_CC_SERVER') ||
			Configuration::deleteByName('FASPAY_CC_AUTO_VOID')
			) return true;
	}
}