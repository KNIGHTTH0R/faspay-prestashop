<?php

if (!defined('_PS_VERSION_'))
	exit;

class Faspay extends PaymentModule {
	private $_html = '';
	private $_postErrors = array();
	private $_faspay = array('merchant_id'=>null, 'merchant_name'=>null,
		'order_expire'=>1,
		'userid'=>null, 'userpswd'=>null,
		'server'=>'development',
		'encsalt'=>'!kQm*fF3pXe1Kbm%9',
		'encpswd'=>'*!nD0n3s5!4#',
		'pg_exist'=>false,
		'bcakp_clearkey'=>'', 'bcakp_code'=>'', 'bca_installment' => '', 'mid_3' => '', 'min_price_3' => '',
		'mid_6' => '', 'min_price_6' => '','mid_12' => '', 'min_price_12' => '',
		'mid_24' => '', 'min_price_24' => '', 'status_mid_3' => '', 'status_mid_6' => '', 'status_mid_12' => '', 'status_mid_24' => ''
		);

	public $pglist = array(
		'tcash' => array('checked' => ''),
		'xltunai' => array('checked' => ''),
		'mynt' => array('checked' => ''),
		'dompetku' => array('checked' => ''),
		'bri_mocash' => array('checked' => ''), 
		'bri_epay' => array('checked' => ''),
		'permata_va' => array('checked' => ''),
		'bca_klikpay' => array('checked' => ''),
		'mandiri_clickpay' => array('checked' => ''),
		'bii_mobile' => array('checked' => ''), 
		'bii_inet' => array('checked' => ''),
		'visamaster' => array('checked' => ''),
		'cimb_clicks' => array('checked' => '')
		
	);
	public $extra_mail_vars;
	public function __construct() {
		$this->name 	= 'faspay';
		$this->tab 		= 'payments_gateways';
		$this->version 	= '0.97';
		$this->author  	= 'MediaIndonusa.com';
		$this->limited_countries = array('id');

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$this->_setConfigValue();
		parent::__construct();

		$this->displayName = 'Faspay';
		$this->description = 'Faspay Payment Aggregator - Transaction secure, anytime, and anywhere';
		$this->confirmUninstall = $this->l('Are you sure you want to delete faspay account ?');
		if (!isset($this->_faspay['merchant_id']) || !isset($this->_faspay['merchant_name']) || !count($this->pglist))
			$this->warning = $this->l('Merchant ID and Name must be configured in order to use this module correctly.');
		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency set for this module');

		$this->extra_mail_vars = array(
			'{bankwire_owner}'   => Configuration::get('FASPAY_MERCHANT_ID'),
			'{bankwire_details}' => nl2br(Configuration::get('FASPAY_MERCHANT_NAME')),
			'{bankwire_address}' => nl2br(Configuration::get('FASPAY_MERCHANT_ADDRESS'))
		);
	}
	public function install() {
		if (!parent::install() ||
			!$this->registerHook('payment') ||
			!$this->registerHook('paymentReturn') ||
			!$this->_createPaymentFaspaytbl()
		) return false;
		return true;
	}
	public function uninstall() {
		if (!Configuration::deleteByName('FASPAY_MERCHANT_ID')
				|| !Configuration::deleteByName('FASPAY_MERCHANT_NAME')
				|| !Configuration::deleteByName('FASPAY_ORDER_EXPIRE')
				|| !Configuration::deleteByName('FASPAY_USERID')
				|| !Configuration::deleteByName('FASPAY_USERPSWD')
				|| !Configuration::deleteByName('FASPAY_SERVER')
				|| !Configuration::deleteByName('FASPAY_PG_TCASH')
				|| !Configuration::deleteByName('FASPAY_PG_XLTUNAI')
				|| !Configuration::deleteByName('FASPAY_PG_MYNT')
				|| !Configuration::deleteByName('FASPAY_PG_DOMPETKU')
				|| !Configuration::deleteByName('FASPAY_PG_BRI_MOCASH')
				|| !Configuration::deleteByName('FASPAY_PG_BRI_EPAY')
				|| !Configuration::deleteByName('FASPAY_PG_PERMATA_VA')
				|| !Configuration::deleteByName('FASPAY_PG_BCA_KLIKPAY')
				|| !Configuration::deleteByName('FASPAY_PG_MANDIRI_CLICKPAY')
				|| !Configuration::deleteByName('FASPAY_PG_BII_MOBILE')
				|| !Configuration::deleteByName('FASPAY_PG_BII_INET')
				|| !Configuration::deleteByName('FASPAY_PG_VISMASTER')
				|| !Configuration::deleteByName('FASPAY_PG_CIMB_CLICKS')
				|| !Configuration::deleteByName('FASPAY_BCAKP_CLEARKEY')
				|| !Configuration::deleteByName('FASPAY_BCAKP_CODE')
				|| !Configuration::deleteByName('FASPAY_BCA_INSTALLMENT')
				|| !Configuration::deleteByName('FASPAY_MID_3')
				|| !Configuration::deleteByName('FASPAY_MIN_PRICE_3')
				|| !Configuration::deleteByName('FASPAY_MID_6')
				|| !Configuration::deleteByName('FASPAY_MIN_PRICE_6')
				|| !Configuration::deleteByName('FASPAY_MID_12')
				|| !Configuration::deleteByName('FASPAY_MIN_PRICE_12')
				|| !Configuration::deleteByName('FASPAY_MID_24')
				|| !Configuration::deleteByName('FASPAY_MIN_PRICE_24')
				|| !Configuration::deleteByName('FASPAY_STATUS_MID_3')
				|| !Configuration::deleteByName('FASPAY_STATUS_MID_6')
				|| !Configuration::deleteByName('FASPAY_STATUS_MID_12')
				|| !Configuration::deleteByName('FASPAY_STATUS_MID_24')
				|| !parent::uninstall())
			return false;
		return true;
	}
	private function _setConfigValue() {
		$config = Configuration::getMultiple(array(
					'FASPAY_MERCHANT_ID', 'FASPAY_MERCHANT_NAME', 'FASPAY_USERID', 'FASPAY_USERPSWD', 'FASPAY_SERVER', 'FASPAY_ORDER_EXPIRE',
					'FASPAY_PG_TCASH',
					'FASPAY_PG_XLTUNAI',
					'FASPAY_PG_MYNT',
					'FASPAY_PG_DOMPETKU',
					'FASPAY_PG_BRI_MOCASH', 'FASPAY_PG_BRI_EPAY',
					'FASPAY_PG_PERMATA_VA',
					'FASPAY_PG_BCA_KLIKPAY', 'FASPAY_BCAKP_CLEARKEY', 'FASPAY_BCAKP_CODE', 'FASPAY_BCA_INSTALLMENT', 'FASPAY_MID_3', 'FASPAY_MIN_PRICE_3',
					'FASPAY_MID_6', 'FASPAY_MIN_PRICE_6','FASPAY_MID_12', 'FASPAY_MIN_PRICE_12','FASPAY_MID_24', 'FASPAY_MIN_PRICE_24', 'FASPAY_STATUS_MID_3',
					'FASPAY_STATUS_MID_6','FASPAY_STATUS_MID_12','FASPAY_STATUS_MID_24',
					'FASPAY_PG_MANDIRI_CLICKPAY',
					'FASPAY_PG_BII_MOBILE', 'FASPAY_PG_BII_INET',
					'FASPAY_PG_VISMASTER',
					'FASPAY_PG_CIMB_CLICKS'
				));
		if (isset($config['FASPAY_MERCHANT_ID'])) 			$this->_faspay['merchant_id']   = $config['FASPAY_MERCHANT_ID'];
		if (isset($config['FASPAY_MERCHANT_NAME']))			$this->_faspay['merchant_name'] = $config['FASPAY_MERCHANT_NAME'];
		if (isset($config['FASPAY_ORDER_EXPIRE']))			$this->_faspay['order_expire']	= $config['FASPAY_ORDER_EXPIRE'] ? $config['FASPAY_ORDER_EXPIRE'] : 1;
		if (isset($config['FASPAY_USERID']))				$this->_faspay['userid'] 	= $config['FASPAY_USERID'];
		if (isset($config['FASPAY_USERPSWD']))				$this->_faspay['userpswd']  = $config['FASPAY_USERPSWD'];
		if (isset($config['FASPAY_SERVER']))				$this->_faspay['server'] 	= $config['FASPAY_SERVER'];
		if (isset($config['FASPAY_PG_TCASH']))				$this->pglist['tcash'] 		= array('id'=>302, 'cd'=>'tcash', 'nm'=> 'tCash Telkomsel' , 'desc'=>'Pembayaran melalui eMoney Telkomsel tCash', 'checked' => $config['FASPAY_PG_TCASH'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_TCASH'] ? 1:0);
		if (isset($config['FASPAY_PG_XLTUNAI']))			$this->pglist['xltunai'] 	= array('id'=>303, 'cd'=>'xltunai', 'nm'=> 'XL Tunai' , 'desc'=>'Pembayaran melalui eMoney XL Tunai', 'checked' => $config['FASPAY_PG_XLTUNAI'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_XLTUNAI'] ? 1:0);
		if (isset($config['FASPAY_PG_MYNT']))				$this->pglist['mynt'] 		= array('id'=>304, 'cd'=>'mynt', 'nm'=> 'MYNT eMoney Artajasa' , 'desc'=>'Pembayaran melalui eMoney MYNT Artajasa', 'checked' => $config['FASPAY_PG_MYNT'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_MYNT'] ? 1:0);
		if (isset($config['FASPAY_PG_DOMPETKU']))			$this->pglist['dompetku'] 	= array('id'=>307, 'cd'=>'dompetku', 'nm'=> 'Dompetku' , 'desc'=>'Pembayaran melalui Indosat Dompetku', 'checked' => $config['FASPAY_PG_DOMPETKU'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_DOMPETKU'] ? 1:0);
		if (isset($config['FASPAY_PG_BRI_MOCASH']))			$this->pglist['bri_mocash'] = array('id'=>400, 'cd'=>'bri_mocash', 'nm'=> 'Mobile Cash BRI' , 'desc'=>'Pembayaran melalui Mobile Cash BRI', 'checked' => $config['FASPAY_PG_BRI_MOCASH'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_BRI_MOCASH'] ? 1:0);
		if (isset($config['FASPAY_PG_BRI_EPAY']))			$this->pglist['bri_epay'] 	= array('id'=>401, 'cd'=>'bri_epay', 'nm'=> 'ePay BRI' , 'desc'=>'Pembayaran melalui ePay BRI', 'checked' => $config['FASPAY_PG_BRI_EPAY'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_BRI_EPAY'] ? 1:0);
		if (isset($config['FASPAY_PG_PERMATA_VA']))			$this->pglist['permata_va'] = array('id'=>402, 'cd'=>'permata_va', 'nm'=> 'Virtual Account Bank Permata' , 'desc'=>'Pembayaran melalui Virtual Account Permata', 'checked' => $config['FASPAY_PG_PERMATA_VA'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_PERMATA_VA'] ? 1:0);
		if (isset($config['FASPAY_PG_BCA_KLIKPAY']))		$this->pglist['bca_klikpay']= array('id'=>405, 'cd'=>'bca_klikpay', 'nm'=> 'BCA KlikPay' , 'desc'=>'Pembayaran melalui BCA KlikPay', 'checked' => $config['FASPAY_PG_BCA_KLIKPAY'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_BCA_KLIKPAY'] ? 1:0);
		if (isset($config['FASPAY_PG_MANDIRI_CLICKPAY']))	$this->pglist['mandiri_clickpay'] = array('id'=>406, 'cd'=>'mandiri_clickpay', 'nm'=> 'clickPay Mandiri' , 'desc'=>'Pembayaran melalui Mandiri clickPay', 'checked' => $config['FASPAY_PG_MANDIRI_CLICKPAY'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_MANDIRI_CLICKPAY'] ? 1:0);
		if (isset($config['FASPAY_PG_BII_MOBILE']))			$this->pglist['bii_mobile'] = array('id'=>407, 'cd'=>'bii_mobile', 'nm'=> 'Mobile Banking BII' , 'desc'=>'Pembayaran melalui Mobile Banking BII', 'checked' => $config['FASPAY_PG_BII_MOBILE'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_BII_MOBILE'] ? 1:0);
		if (isset($config['FASPAY_PG_BII_INET']))			$this->pglist['bii_inet'] 	= array('id'=>408, 'cd'=>'bii_inet', 'nm'=> 'Internet Banking BII' , 'desc'=>'Pembayaran melalui Internet Banking BII', 'checked' => $config['FASPAY_PG_BII_INET'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_BII_INET'] ? 1:0);
		if (isset($config['FASPAY_PG_VISMASTER']))			$this->pglist['visamaster'] = array('id'=>500, 'cd'=>'visamaster', 'nm'=> 'Visa/Master' , 'desc'=>'Pembayaran melalui Kartu Kredit Visa/Master', 'checked' => $config['FASPAY_PG_VISMASTER'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_VISMASTER'] ? 1:0);
		if (isset($config['FASPAY_PG_CIMB_CLICKS']))		$this->pglist['cimb_clicks'] = array('id'=>700, 'cd'=>'cimb_clicks', 'nm'=> 'CIMB Clicks' , 'desc'=>'Pembayaran melalui CIMB Clicks', 'checked' => $config['FASPAY_PG_CIMB_CLICKS'] ? ' checked="checked"':'', 'active' => $config['FASPAY_PG_CIMB_CLICKS'] ? 1:0);
		if (isset($config['FASPAY_BCA_INSTALLMENT']))		$this->_faspay['bca_installment']	= $config['FASPAY_BCA_INSTALLMENT'];
		if (isset($config['FASPAY_MID_3']))					$this->_faspay['mid_3']			= $config['FASPAY_MID_3'];
		if (isset($config['FASPAY_MIN_PRICE_3']))			$this->_faspay['min_price_3']	= $config['FASPAY_MIN_PRICE_3'];
		if (isset($config['FASPAY_MID_6']))					$this->_faspay['mid_6']			= $config['FASPAY_MID_6'];
		if (isset($config['FASPAY_MIN_PRICE_6']))			$this->_faspay['min_price_6']	= $config['FASPAY_MIN_PRICE_6'];
		if (isset($config['FASPAY_MID_12']))				$this->_faspay['mid_12']		= $config['FASPAY_MID_12'];
		if (isset($config['FASPAY_MIN_PRICE_12']))			$this->_faspay['min_price_12']	= $config['FASPAY_MIN_PRICE_12'];
		if (isset($config['FASPAY_MID_24']))				$this->_faspay['mid_24']		= $config['FASPAY_MID_24'];
		if (isset($config['FASPAY_MIN_PRICE_24']))			$this->_faspay['min_price_24']	= $config['FASPAY_MIN_PRICE_24'];
		if (isset($config['FASPAY_STATUS_MID_3']))			$this->_faspay['status_mid_3']	= $config['FASPAY_STATUS_MID_3'];
		if (isset($config['FASPAY_STATUS_MID_6']))			$this->_faspay['status_mid_6']	= $config['FASPAY_STATUS_MID_6'];
		if (isset($config['FASPAY_STATUS_MID_12']))			$this->_faspay['status_mid_12']	= $config['FASPAY_STATUS_MID_12'];
		if (isset($config['FASPAY_STATUS_MID_24']))			$this->_faspay['status_mid_24']	= $config['FASPAY_STATUS_MID_24'];

		if (isset($config['FASPAY_BCAKP_CLEARKEY']))		$this->_faspay['bcakp_clearkey']= $config['FASPAY_BCAKP_CLEARKEY'];
		if (isset($config['FASPAY_BCAKP_CODE']))			$this->_faspay['bcakp_code']	= $config['FASPAY_BCAKP_CODE'];
		foreach($this->pglist as $k => $v) {
			if($v['active']) $this->_faspay['pg_exist'] = true;
		}
	}
	private function _postConfigValidation() {
		if (Tools::isSubmit('btnSubmit')) {
			if (!Tools::getValue('merchant_id')) 		$this->_postErrors[] = $this->l('Merchant ID is required.');
			elseif (!Tools::getValue('merchant_name'))	$this->_postErrors[] = $this->l('Merchant Name is required.');
			elseif (!Tools::getValue('order_expire'))	$this->_postErrors[] = $this->l('Order Expire is required.');
			elseif (!Tools::getValue('userid'))		$this->_postErrors[] = $this->l('UserID is required.');
			elseif (!Tools::getValue('userpswd'))	$this->_postErrors[] = $this->l('Password is required.');
			else {
				$pg = 0;
				$pg += Tools::getValue('tcash')=='on' ? 1:0;
				$pg += Tools::getValue('xltunai')=='on' ? 1:0;
				$pg += Tools::getValue('mynt')=='on' ? 1:0;
				$pg += Tools::getValue('dompetku')=='on' ? 1:0;
				$pg += Tools::getValue('bri_mocash')=='on' ? 1:0;
				$pg += Tools::getValue('bri_epay')=='on' ? 1:0;
				$pg += Tools::getValue('permata_va')=='on' ? 1:0;
				$pg += Tools::getValue('bca_klikpay')=='on' ? 1:0;
				$pg += Tools::getValue('mandiri_clickpay')=='on' ? 1:0;
				$pg += Tools::getValue('bii_mobile')=='on' ? 1:0;
				$pg += Tools::getValue('bii_inet')=='on' ? 1:0;
				$pg += Tools::getValue('visamaster')=='on' ? 1:0;
				$pg += Tools::getValue('cimb_clicks')=='on' ? 1:0;
				if(!$pg) $this->_postErrors[] = $this->l('At least one of payment gateway must be choosen ');
				if(Tools::getValue('bca_klikpay')=='on' && !Tools::getValue('bcakp_clearkey')&& !Tools::getValue('bca_installment')&& !Tools::getValue('mid_3') && !Tools::getValue('min_price_3') 
				&& !Tools::getValue('mid_6') && !Tools::getValue('min_price_6')&& !Tools::getValue('mid_12') && !Tools::getValue('min_price_12')
				&& !Tools::getValue('mid_24') && !Tools::getValue('min_price_24') && !Tools::getValue('status_mid_3')&& !Tools::getValue('status_mid_6')&& !Tools::getValue('status_mid_12')&& !Tools::getValue('status_mid_24'))
					$this->_postErrors[] = $this->l('Please fill BCA klikPay Parameters');
			}
		}
	}
	private function _postConfigProcess() {
		if (Tools::isSubmit('btnSubmit')) {
			Configuration::updateValue('FASPAY_MERCHANT_ID', 	Tools::getValue('merchant_id'));
			Configuration::updateValue('FASPAY_MERCHANT_NAME', 	Tools::getValue('merchant_name'));
			Configuration::updateValue('FASPAY_ORDER_EXPIRE', 	Tools::getValue('order_expire'));
			Configuration::updateValue('FASPAY_USERID', 		Tools::getValue('userid'));
			Configuration::updateValue('FASPAY_USERPSWD', 		$this->_encrypt(Tools::getValue('userpswd')));
			Configuration::updateValue('FASPAY_SERVER', 		Tools::getValue('server'));
			Configuration::updateValue('FASPAY_PG_TCASH', 		Tools::getValue('tcash')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_PG_XLTUNAI', 	Tools::getValue('xltunai')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_PG_MYNT', 		Tools::getValue('mynt')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_PG_DOMPETKU', 	Tools::getValue('dompetku')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_PG_BRI_MOCASH', 	Tools::getValue('bri_mocash')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_PG_BRI_EPAY', 	Tools::getValue('bri_epay')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_PG_PERMATA_VA', 	Tools::getValue('permata_va')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_PG_BCA_KLIKPAY', Tools::getValue('bca_klikpay')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_PG_MANDIRI_CLICKPAY', Tools::getValue('mandiri_clickpay')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_PG_BII_MOBILE', 	Tools::getValue('bii_mobile')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_PG_BII_INET', 	Tools::getValue('bii_inet')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_PG_VISMASTER', 	Tools::getValue('visamaster')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_PG_CIMB_CLICKS', Tools::getValue('cimb_clicks')=='on' ? 1 : 0);
			Configuration::updateValue('FASPAY_BCAKP_CLEARKEY', $this->_encrypt(Tools::getValue('bcakp_clearkey')));
			Configuration::updateValue('FASPAY_BCAKP_CODE', 	$this->_encrypt(Tools::getValue('bcakp_code')));
			Configuration::updateValue('FASPAY_MID_3',			Tools::getValue('mid_3'));
			Configuration::updateValue('FASPAY_MIN_PRICE_3',	Tools::getValue('min_price_3'));
			Configuration::updateValue('FASPAY_MID_6',			Tools::getValue('mid_6'));
			Configuration::updateValue('FASPAY_MIN_PRICE_6',	Tools::getValue('min_price_6'));
			Configuration::updateValue('FASPAY_MID_12',			Tools::getValue('mid_12'));
			Configuration::updateValue('FASPAY_MIN_PRICE_12',	Tools::getValue('min_price_12'));
			Configuration::updateValue('FASPAY_MID_24',			Tools::getValue('mid_24'));
			Configuration::updateValue('FASPAY_MIN_PRICE_24',	Tools::getValue('min_price_24'));
			Configuration::updateValue('FASPAY_STATUS_MID_3',	Tools::getValue('status_mid_3'));
			Configuration::updateValue('FASPAY_STATUS_MID_6',	Tools::getValue('status_mid_6'));
			Configuration::updateValue('FASPAY_STATUS_MID_12',	Tools::getValue('status_mid_12'));
			Configuration::updateValue('FASPAY_STATUS_MID_24',	Tools::getValue('status_mid_24'));
			$this->_setConfigValue();
		}
		$this->_html .= '<div class="conf confirm"> '.$this->l('Settings updated').'</div>';
	}
	private function _displayConfigForm() {
		$this->_html .= '<b>'.$this->l('This module allows you to accept payments by Bank / Credit Card / eMoney.').'</b><br /><br />'
			.$this->l('If the client chooses this payment mode, the order will change its status into a \'Waiting for payment\' status.').'<br /><br />';

		$sel_dev = $this->_faspay['server']=='development'? ' selected':'';
		$sel_prd = $this->_faspay['server']=='production' ? ' selected':'';
		$bca_cls = Configuration::get('FASPAY_PG_BCA_KLIKPAY') ? 'block' : 'none';
		$html = '
			<form class="" action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
				<fieldset>
				<legend><img src="../img/admin/contact.gif" />'.$this->l('Account Details').'</legend>
					<h3>'.$this->l('Please specify account details').'</h3>
					<p class="required merchant_id numeric"><label for="merchant_id">'.$this->l('Merchant ID').'</label><input type="text" id="merchant_id" name="merchant_id" value="'.$this->_faspay['merchant_id'].'" style="width: 100px;" /></p>
					<p class="required merchant_name text"><label for="merchant_name">'.$this->l('Merchant Name').'</label><input type="text" id="merchant_name" name="merchant_name" value="'.$this->_faspay['merchant_name'].'" style="width: 300px;" /></p>
					<p class="required order_expire text"><label for="order_expire">'.$this->l('Order Expire in').'</label><input type="text" id="order_expire" name="order_expire" value="'.$this->_faspay['order_expire'].'" style="width: 100px;" /> '.$this->l('Hour(s)').'</p>
					<p class="required userid text"><label for="userid">'.$this->l('User ID').'</label><input type="text" id="userid" name="userid" value="'.$this->_faspay['userid'].'" style="width: 300px;" /></p>
					<p class="required userpswd text"><label for="userpswd">'.$this->l('Password').'</label><input type="password" id="userpswd" name="userpswd" value="'.$this->_decrypt($this->_faspay['userpswd']).'" style="width: 150px;" /></p>
					<p class="required server select"><label for="server">'.$this->l('Server').'</label><select id="server" name="server">
						<option value="development"'.$sel_dev.'>Development</option>
						<option value="production"' .$sel_prd.'>Production</option>
						</select>
					</p>
					<p class="checkbox">
						<label>'.$this->l('Payment Gateways').'</label>
						<input type="checkbox" id="tcash" name="tcash"'.$this->pglist['tcash']['checked'].'/><label  class="t" for="tcash">tCash</label><br clear="both"/>
						<label></label><input type="checkbox" id="xltunai" name="xltunai"'.$this->pglist['xltunai']['checked'].'/><label class="t" for="xltunai">XLTunai</label><br clear="both"/>
						<label></label><input type="checkbox" id="mynt" name="mynt"'.$this->pglist['mynt']['checked'].'/><label class="t" for="mynt">MYNT Artajasa</label><br clear="both"/>
						<label></label><input type="checkbox" id="dompetku" name="dompetku"'.$this->pglist['dompetku']['checked'].'/><label class="t" for="mynt">Indosat Dompetku</label><br clear="both"/>
						<label></label><input type="checkbox" id="bri_mocash" name="bri_mocash"'.$this->pglist['bri_mocash']['checked'].'/><label class="t" for="bri_mocash">BRI Mobile Cash</label><br clear="both"/>
						<label></label><input type="checkbox" id="bri_epay" name="bri_epay"'.$this->pglist['bri_epay']['checked'].'/><label class="t" for="bri_epay">BRI ePay</label><br clear="both"/>
						<label></label><input type="checkbox" id="permata_va" name="permata_va"'.$this->pglist['permata_va']['checked'].'/><label class="t" for="permata_va">Permata Virtual Account</label><br clear="both"/>
						<label></label><input type="checkbox" id="bca_klikpay" name="bca_klikpay"'.$this->pglist['bca_klikpay']['checked'].'/><label class="t" for="bca_klikpay">BCA KlikPay</label><br clear="both"/>
						<label></label><input type="checkbox" id="mandiri_clickpay" name="mandiri_clickpay"'.$this->pglist['mandiri_clickpay']['checked'].'/><label class="t" for="mandiri_clickpay">Mandiri clickPay</label><br clear="both"/>
						<label></label><input type="checkbox" id="bii_mobile" name="bii_mobile"'.$this->pglist['bii_mobile']['checked'].'/><label class="t" for="bii_mobile">BII Mobile Banking</label><br clear="both"/>
						<label></label><input type="checkbox" id="bii_inet" name="bii_inet"'.$this->pglist['bii_inet']['checked'].'/><label class="t" for="bii_inet">BII Internet Banking</label><br clear="both"/>
						<label></label><input type="checkbox" id="visamaster" name="visamaster"'.$this->pglist['visamaster']['checked'].'/><label class="t" for="visamaster">Kartu Kredit Visa/Master</label><br clear="both"/>
						<label></label><input type="checkbox" id="cimb_clicks" name="cimb_clicks"'.$this->pglist['cimb_clicks']['checked'].'/><label class="t" for="mynt">CIMB Clicks</label><br clear="both"/>
						
					</p>
					<p id="bcakp_conf" style="display:'.$bca_cls.';">
						<label>'.$this->l('BCA klikPay Parameters').':</label><br clear="both"/>
						<label for="bcakp_clearkey">Clear Key</label><input type="password" id="bcakp_clearkey" name="bcakp_clearkey" value="'.$this->_decrypt($this->_faspay['bcakp_clearkey']).'" style="width: 300px;"/><br clear="both"/>
						<label for="bcakp_code">klikPay Code</label><input type="password" id="bcakp_code" name="bcakp_code" value="'.$this->_decrypt($this->_faspay['bcakp_code']).'" style="width: 300px;"/><br clear="both"/>
						<label for="bca_installment">Konfigurasi untuk cicilan 3 bulan</label><input type="hidden" id="bca_installment" name="bca_installment" value="" style="width: 300px;"/><br clear="both"/>
						<label for="mid_3">MID</label><input type="text" id="mid_3" name="mid_3" value="'.$this->_faspay['mid_3'].'" style="width: 300px;"/><br clear="both"/>
						<label for="min_price_3">Price Minimum</label><input type="text" id="min_price_3" name="min_price_3" value="'.$this->_faspay['min_price_3'].'" style="width: 300px;"/><br clear="both"/>
						<label for="min_price_3">Status</label><select id="status_mid_3" name="status_mid_3" value="'.$this->_faspay['status_mid_3'].'">';
			if($this->_faspay['status_mid_3'] == "active"){
				$html .='<option value="">Pilih activasi cicilan</option>
						<option value="active" selected="selected">active</option>
						<option value="inactive">inactive</option>
							</select><br clear="both"/>';
			}elseif($this->_faspay['status_mid_3'] == "inactive"){
				$html .='<option value="">Pilih activasi cicilan</option>
						<option value="active">active</option>
						<option value="inactive" selected="selected">inactive</option>
							</select><br clear="both"/>';
			}else{
				$html .='<option value="" selected="selected">Pilih activasi cicilan</option>
						<option value="active">active</option>
						<option value="inactive">inactive</option>
							</select><br clear="both"/>';
			}	
			$html .=	'<label for="bca_installment">Konfigurasi untuk cicilan 6 bulan</label><input type="hidden" id="bca_installment" name="bca_installment" value="" style="width: 300px;"/><br clear="both"/>
						<label for="mid_6">MID</label><input type="text" id="mid_6" name="mid_6" value="'.$this->_faspay['mid_6'].'" style="width: 300px;"/><br clear="both"/>
						<label for="min_price_6">Price Minimum</label><input type="text" id="min_price_6" name="min_price_6" value="'.$this->_faspay['min_price_6'].'" style="width: 300px;"/><br clear="both"/>
						<label for="min_price_6">Status</label><select id="status_mid_6" name="status_mid_6" value="'.$this->_faspay['status_mid_6'].'">';
			if($this->_faspay['status_mid_6'] == "active"){
				$html .='<option value="">Pilih activasi cicilan</option>
						<option value="active" selected="selected">active</option>
						<option value="inactive">inactive</option>
							</select><br clear="both"/>';
			}elseif($this->_faspay['status_mid_6'] == "inactive"){
				$html .='<option value="">Pilih activasi cicilan</option>
						<option value="active">active</option>
						<option value="inactive" selected="selected">inactive</option>
							</select><br clear="both"/>';
			}else{
				$html .='<option value="" selected="selected">Pilih activasi cicilan</option>
						<option value="active">active</option>
						<option value="inactive">inactive</option>
							</select><br clear="both"/>';
			}	
			$html .=	'<label for="bca_installment">Konfigurasi untuk cicilan 12 bulan</label><input type="hidden" id="bca_installment" name="bca_installment" value="" style="width: 300px;"/><br clear="both"/>
						<label for="mid_12">MID</label><input type="text" id="mid_12" name="mid_12" value="'.$this->_faspay['mid_12'].'" style="width: 300px;"/><br clear="both"/>
						<label for="min_price_12">Price Minimum</label><input type="text" id="min_price_12" name="min_price_12" value="'.$this->_faspay['min_price_12'].'" style="width: 300px;"/><br clear="both"/>
						<label for="min_price_12">Status</label><select id="status_mid_12" name="status_mid_12" value="'.$this->_faspay['status_mid_12'].'">';
			if($this->_faspay['status_mid_12'] == "active"){
				$html .='<option value="">Pilih activasi cicilan</option>
						<option value="active" selected="selected">active</option>
						<option value="inactive">inactive</option>
							</select><br clear="both"/>';
			}elseif($this->_faspay['status_mid_12'] == "inactive"){
				$html .='<option value="">Pilih activasi cicilan</option>
						<option value="active">active</option>
						<option value="inactive" selected="selected">inactive</option>
							</select><br clear="both"/>';
			}else{
				$html .='<option value="" selected="selected">Pilih activasi cicilan</option>
						<option value="active">active</option>
						<option value="inactive">inactive</option>
							</select><br clear="both"/>';
			}		
			$html .=	'<label for="bca_installment">Konfigurasi untuk cicilan 24 bulan</label><input type="hidden" id="bca_installment" name="bca_installment" value="" style="width: 300px;"/><br clear="both"/>
						<label for="mid_24">MID</label><input type="text" id="mid_24" name="mid_24" value="'.$this->_faspay['mid_24'].'" style="width: 300px;"/><br clear="both"/>
						<label for="min_price_24">Price Minimum</label><input type="text" id="min_price_24" name="min_price_24" value="'.$this->_faspay['min_price_24'].'" style="width: 300px;"/><br clear="both"/>
						<label for="min_price_24">Status</label><select id="status_mid_24" name="status_mid_24" value="'.$this->_faspay['status_mid_24'].'">';
			if($this->_faspay['status_mid_24'] == "active"){
				$html .='<option value="">Pilih activasi cicilan</option>
						<option value="active" selected="selected">active</option>
						<option value="inactive">inactive</option>
							</select><br clear="both"/>';
			}elseif($this->_faspay['status_mid_24'] == "inactive"){
				$html .='<option value="">Pilih activasi cicilan</option>
						<option value="active">active</option>
						<option value="inactive" selected="selected">inactive</option>
							</select><br clear="both"/>';
			}else{
				$html .='<option value="" selected="selected">Pilih activasi cicilan</option>
						<option value="active">active</option>
						<option value="inactive">inactive</option>
							</select><br clear="both"/>';
			}
			
						
			$html	.=	'</p>
					<p><label></label><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></p>
				</fieldset>
			</form>
			<script>
				$(function() {
					$("#bca_klikpay").click(function() {
						if ($(this).is(":checked")) $("#bcakp_conf").show();
						else $("#bcakp_conf").hide();
					});
				});
			</script>
			';
		$this->_html .= $html;
	}
	public function getContent() {
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('btnSubmit')) {
			$this->_postConfigValidation();
			if (!count($this->_postErrors))
				$this->_postConfigProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= '<div class="alert error">'.$err.'</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayConfigForm();
		return $this->_html;
	}
	public function hookPayment($params) {
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;


		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
			'pglist' => $this->pglist,
			'pgexist'=> $this->_faspay['pg_exist']
		));
		return $this->display(__FILE__, 'payment.tpl');
	}
	public function hookPaymentReturn($params) {
		if (!$this->active)
			return;
		$state = $params['objOrder']->getCurrentState();
		if ($state == Configuration::get('PS_OS_BANKWIRE') || $state == Configuration::get('PS_OS_OUTOFSTOCK')) {
			
			global $smarty;
			$smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->id) && !empty($params['objOrder']->id))
				 $smarty->assign('id', $params['objOrder']->id);

			$pg = $this->pglist[Tools::getValue('pg')];
		

			$smarty->assign('pg', $pg['id']);
			$a = $_GET;
		
			$ack = $this->_xml2array($this->_post_data($params, $a));
			
			
			$rsp = array_merge(array('order_id'=>$params['objOrder']->id, 'payment_channel'=>$pg['nm']), $ack);
			
		
			
			$this->_resp_faspay('post_data', $rsp);

			$sg = $this->_bcakp_signature($params['objOrder']->id);
			$qs = 'trx_id='.$rsp['trx_id'].'&merchant_id='.$this->_faspay['merchant_id'].'&bill_no='.$params['objOrder']->id;
			

			if($pg['id']==405)  {
				$rd = $this->_faspay['server'] == 'development' ?
					//'https://202.6.215.230:8081/purchasing/purchase.do?action=loginRequest' :
					'http://faspaydev.mediaindonusa.com/redirectbca':
					'https://klikpay.klikbca.com/purchasing/purchase.do?action=loginRequest';
				$srv = $_SERVER['HTTP_HOST'];
				$srv .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
				
				$statusPayType = 1;
				$indexStatus = 1;
				$index = 0;
				$last = 0;								
				$orDet = $this->_resp_faspay('getlist', array('trx_id'=>$params['objOrder']->id));
				
				foreach($orDet as $k => $v) {

					$test = "payment_tenor_".$index;
					if($index == 0){
						if($a[$test]== '00' or !isset($a[$test])){
						$statusPayType = 1;
						$last = 1;
					}else{
						$statusPayType = 2;
						$last = 2;
						}
					}else{
						if($a[$test]== '00' or !isset($a[$test])){
						$statusPayType = 1;
						}else{
						$statusPayType = 2;
						}
					
					}
					if($last != $statusPayType){
						$last = 3;
					}
					$index++;
				}
				if($last == 1){
					$payType= '01';
				}else if($last == 2 ){
					$payType= '02';
				}else{
					$payType= '03';
				}
				
				$dat = array(
					'klikPayCode'=>$this->_decrypt($this->_faspay['bcakp_code']),
					'transactionNo'=>$rsp['trx_id'],
					'totalAmount'=>($params['objOrder']->total_products)-($params['objOrder']->total_discounts),
					'payType'=>$payType,
					'callback'=> "http://$srv"."index.php?fc=module&module=faspay&controller=thanks&pg=bca_klikpay&trx_id=$rsp[trx_id]",
					'transactionDate'=>date('d/m/Y H:i:s',strtotime($params['objOrder']->date_upd)),
					'descp'=>'Pembelian Barang (' . $this->_faspay['merchant_name'] . ')',
					'miscFee'=>$params['objOrder']->total_shipping,
					'signature'=>$this->_bcakp_signature_ori($rsp['trx_id']));

				$smarty->assign('dat', $dat);
			}
			
			else{
				$rd = $this->_faspay['server'] == 'development' ?
					"http://faspaydev.mediaindonusa.com/pws/100003/0830000010100000/$sg?$qs" :
					"https://faspaydev.mediaindonusa.com/pws/100003/2830000010100000/$sg?$qs";
					//echo $rd;exit;
			}
			$smarty->assign('uri', $rd);
		}
		else
			$smarty->assign('status', 'failed');

		return $this->display(dirname(__FILE__), 'payment_return.tpl');
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
	public function report() {
		// http://<server>/index.php?fc=module&module=faspay&controller=api&act=report
		$reqd = $this->_xml2array(urldecode(file_get_contents('php://input')));

		
		$this->_resp_faspay('faspay_report', $reqd);

		$xml  = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		$xml .= "<faspay>" . "\n";
		$xml .= "<request>Payment Notification</request>" . "\n";
		$xml .= "<trx_id>".$reqd['trx_id']."</trx_id>" . "\n";
		$xml .= "<merchant_id>".$reqd['merchant_id']."</merchant_id>" . "\n";
		$xml .= "<bill_no>".$reqd['bill_no']."</bill_no>" . "\n";
		$xml .= "<response_code>00</response_code>" . "\n";
		$xml .= "<response_desc>Sukses</response_desc>" . "\n";
		$xml .= "<response_date>".date("Y-m-d H:i:s")."</response_date>" . "\n";
		$xml .= "</faspay>" . "\n";
		echo header("Content-type: application/xml");
		echo $xml;
	}
	
	public function bcakp_signature() {
		// http://<server>/index.php?fc=module&module=faspay&controller=api&act=bcakp_signature&trx_id=<trx_id>&signature=<signature>
		$ack 			= 0;
		$transactionNo 	= Tools::getValue('trx_id');
		$signature		= Tools::getValue('signature');
		$authKey		= Tools::getValue('authkey');
		if($transactionNo && $signature){
			$sql = "select a.total_products,a.total_discounts,a.date_upd from "._DB_PREFIX_."orders a, "._DB_PREFIX_."order_payment_faspay b where a.id_order = b.order_id and b.trx_id = '$transactionNo'";
			$rsp = Db::getInstance()->executeS($sql);
			if(count($rsp)) {
				$keyId		= $this->_bcakp_keyId();
				$klikPayCode= $this->_decrypt($this->_faspay['bcakp_code']);

				$currency	= 'IDR';
				$tempKey1	= $klikPayCode . $transactionNo . $currency . $keyId;
				$hashKey1	= $this->_getHash($tempKey1);
				$transactionDate = $rsp[0]['date_upd'];
			    $amount		= ($rsp[0]['total_products'])-($rsp[0]['total_discounts']);
				$expDate	= date('dmY', strtotime($transactionDate));
				$strDate 	= $this->_intval32bits($expDate);
				$amt 		= $this->_intval32bits((int)$amount);
				$tempKey2 	= $strDate + $amt;
				$hashKey2 	= $this->_getHash((string)$tempKey2);
				$sig 		= abs($hashKey1 + $hashKey2);			
			  $ack 		= $sig == $signature ? 1 : 0;}
							 
			}
			  
			else if($transactionNo && $authKey ){
				$sql = "select a.total_products, a.date_upd from "._DB_PREFIX_."orders a, "._DB_PREFIX_."order_payment_faspay b where a.id_order = b.order_id and b.trx_id = '$transactionNo'";
			  $rsp = Db::getInstance()->executeS($sql);
			  if(count($rsp)){
			  	$keyId		= $this->_bcakp_keyId();
					$klikPayCode= $this->_decrypt($this->_faspay['bcakp_code']);
					$currency	= 'IDR';
			  	$transactionDate = $rsp[0]['date_upd'];
			  	$transactionDate= date('d/m/Y H:i:s', strtotime($transactionDate));
					$klikPayCode 	= str_pad($klikPayCode, 10, "0");
			  	$transactionNo 	= str_pad($transactionNo, 18, "A");
			  	$currency 		= str_pad($currency, 5, "1");
			  	$value_1 = $klikPayCode . $transactionNo . $currency . $transactionDate . $keyId;
			  	$hashv_1 		= strtoupper(md5($value_1));
					if (strlen($keyId) == 32)
				   $key = $keyId . substr($keyId,0,16);
			  	else if (strlen($keyId) == 48)
				  $key = $keyId;
					$aKey = strtoupper(bin2hex(mcrypt_encrypt(MCRYPT_3DES, pack("H" . strlen($key), $key), pack("H" . strlen($hashv_1), $hashv_1), MCRYPT_MODE_ECB)));
			  	$ack  = $aKey==$authKey ? 1 : 0;}	
			  	
			}
		
		echo $ack;
		
	}
	public function thanks($trx) {
		return $this->_resp_faspay('thanks', array('trx_id'=>$trx));
	}
	public function get_list($trx) {
		return $this->_resp_faspay('getlist', array('trx_id'=>$trx));
	}
	
	private function _bcakp_keyId() {
		$clearKey = $this->_decrypt($this->_faspay['bcakp_clearkey']);
		return strtoupper(bin2hex(pack("a" . strlen($clearKey), $clearKey)));
	}
	private function _getHash($value) {
		$h = 0;
		for ($i = 0;$i < strlen($value);$i++) {
			$h = $this->_intval32bits($this->_add31T($h) + ord($value{$i}));
		}
		return $h;
	}
	private function _intval32bits($value) {
        if ($value > 2147483647)
            $value = ($value - 4294967296);
		else if ($value < -2147483648)
            $value = ($value + 4294967296);
        return $value;
    }
	private function _add31T($value) {
		$result = 0;
		for($i=1;$i <= 31;$i++) {
			$result = $this->_intval32bits($result + $value);
		}
		return $result;
	}
	private function _createPaymentFaspaytbl() {
        $db = Db::getInstance();
		$query = "CREATE TABLE `"._DB_PREFIX_."order_payment_faspay` (
		  `order_id` int(11) DEFAULT NULL,
		  `trx_id` varchar(32) DEFAULT NULL,
		  `payment_channel` varchar(32) DEFAULT NULL,
		  `payment_status` varchar(32) DEFAULT NULL,
		  `payment_date` timestamp NULL DEFAULT NULL,
		  `payment_reff` varchar(32) DEFAULT NULL
		) ENGINE=MyISAM";
        $db->Execute($query);

		return true;
	}
	private function _bcakp_signature($billno) {
		return sha1(md5($this->_faspay['userid'].$this->_decrypt($this->_faspay['userpswd']).$billno));
	}
	private function _bcakp_signature_ori($billno) {
		  $sig='';	
			$sql = "select a.total_products,a.total_discounts,a.date_upd from "._DB_PREFIX_."orders a, "._DB_PREFIX_."order_payment_faspay b where a.id_order = b.order_id and b.trx_id = '$billno'";
			$rsp = Db::getInstance()->executeS($sql);
			if(count($rsp)) {
				$keyId		= $this->_bcakp_keyId();
				$klikPayCode= $this->_decrypt($this->_faspay['bcakp_code']);

				$currency	= 'IDR';
				$tempKey1	= $klikPayCode . $billno . $currency . $keyId;
				
				$hashKey1	= $this->_getHash($tempKey1);

				$transactionDate =$rsp[0]['date_upd'];
				$amount		= ($rsp[0]['total_products'])-($rsp[0]['total_discounts']);
				$expDate	= date('dmY',strtotime ($transactionDate));
				$strDate = $this->_intval32bits($expDate); 
				$amt 		= $this->_intval32bits((int)$amount);
				$tempKey2 	= $strDate + $amt;
				$hashKey2 	= $this->_getHash((string)$tempKey2);
				$sig 		= abs($hashKey1 + $hashKey2);
					
			} 
		
		return $sig;
	}
	private function _encrypt($decrypted) {
		$key = hash('SHA256', $this->_faspay['encsalt'] . $this->_faspay['encpswd'], true);
		srand();
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
		if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) return false;
		$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $decrypted . md5($decrypted), MCRYPT_MODE_CBC, $iv));
		return $iv_base64 . $encrypted;
	}
	private function _decrypt($encrypted) {
		$key = hash('SHA256', $this->_faspay['encsalt'] . $this->_faspay['encpswd'], true);
		$iv = base64_decode(substr($encrypted, 0, 22) . '==');
		$encrypted = substr($encrypted, 22);
		$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($encrypted), MCRYPT_MODE_CBC, $iv), "\0\4");
		$hash = substr($decrypted, -32);
		$decrypted = substr($decrypted, 0, -32);
		if (md5($decrypted) != $hash) return false;
		return $decrypted;
	}

	private function _prep_data(&$params, $a) {
		$order	= (array)$params['objOrder'];		
		$orObj 	= new OrderDetail();
		$orDet = $this->_resp_faspay('getlist', array('trx_id'=>$params['objOrder']->id));
		//$this->dump($orDet);exit;
		$order['total_products'] 			= $order['total_products']*100;
		$order['total_shipping_tax_incl']	= $order['total_shipping_tax_incl']*100;
		$params['total_to_pay']				= $params['total_to_pay']*100;
		$tax 	= 100*((float)$order['total_paid_tax_incl'] - (float)$order['total_paid_tax_excl']);

		$cust	= new Customer($params['objOrder']->id_customer);
		$delivery_address	= $this->getOrderAddress($params['objOrder']->id_address_delivery);
		
		if($params['objOrder']->id_address_delivery == $params['objOrder']->id_address_invoice)
			$billing_address = $delivery_address;
		else
			$billing_address = $this->getOrderAddress($params['objOrder']->id_address_invoice);
				
		//$this->dump($delivery_address);exit;
		$pg		= $this->pglist[Tools::getValue('pg')];

	
		$exp	= date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s", strtotime($order['date_upd'])) . " +".$this->_faspay['order_expire']." hour"));
		
		$xml .= "<faspay>" . "\n";
		$xml .= "<request>Post Data Transaksi</request>" . "\n";
		$xml .= "<merchant_id>".$this->_faspay['merchant_id']."</merchant_id>" . "\n";
		$xml .= "<merchant>".$this->_faspay['merchant_name']."</merchant>" . "\n";
		$xml .= "<bill_no>".$order['id']."</bill_no>" . "\n";
		$xml .= "<bill_reff>".$order['id']."</bill_reff>" . "\n";
		$xml .= "<bill_date>".$order['date_upd']."</bill_date>" . "\n";
		$xml .= "<bill_expired>".$exp."</bill_expired>" . "\n";
		$xml .= "<bill_desc>Pembelian Barang</bill_desc>" . "\n";
		$xml .= "<bill_currency>IDR</bill_currency>" . "\n";
		$xml .= "<bill_gross>".$order['total_products']."</bill_gross>" . "\n";
		$xml .= "<bill_tax>".$tax."</bill_tax>" . "\n";
		$xml .= "<bill_miscfee>".$order['total_shipping_tax_incl']."</bill_miscfee>" . "\n";
		$xml .= "<bill_total>".$params['total_to_pay']."</bill_total>" . "\n";
		$xml .= "<cust_no>".$params['objOrder']->id_customer."</cust_no>" . "\n";
		$xml .= "<cust_name>".$cust->firstname.' '.$cust->lastname."</cust_name>" . "\n";
		if($pg['id']==500 or $pg['id']==503 or $pg['id']==506 or $pg['id']==512){
		$xml .= "<payment_channel>500</payment_channel>" . "\n";
		}else{
        $xml .= "<payment_channel>".$pg['id']."</payment_channel>" . "\n";}		
		$xml .= "<bank_userid>-</bank_userid>" . "\n";
		$xml .= "<msisdn></msisdn>" . "\n";
		$xml .= "<email>".$cust->email."</email>" . "\n";
		$xml .= "<terminal>10</terminal>" . "\n";
		$xml .= "<billing_address>".$billing_address['address1']."</billing_address>" . "\n";
		$xml .= "<billing_address_city>".$billing_address['city']."</billing_address_city>" . "\n";
		$xml .= "<billing_address_region>".$billing_address['region']."</billing_address_region>" . "\n";
		$xml .= "<billing_address_state>Indonesia</billing_address_state>" . "\n";  
		$xml .= "<billing_address_poscode>".$billing_address['postcode']."</billing_address_poscode>" . "\n";
		$xml .= "<billing_address_country_code>ID</billing_address_country_code>" . "\n";  
		$xml .= "<receiver_name_for_shipping>".$delivery_address['firstname']." ".$delivery_address['lastname']."</receiver_name_for_shipping>" . "\n";  
		$xml .= "<shipping_address>".$delivery_address['address1']."</shipping_address>" . "\n";
		$xml .= "<shipping_address_city>".$delivery_address['city']."</shipping_address_city>" . "\n";
		$xml .= "<shipping_address_region>".$delivery_address['region']."</shipping_address_region>" . "\n";
		$xml .= "<shipping_address_state>Indonesia</shipping_address_state>" . "\n";
		$xml .= "<shipping_address_poscode>".$delivery_address['postcode']."</shipping_address_poscode>" . "\n";
		$statusPayType = 1;
		$indexStatus = 1;
		$index = 0;
		$last = 0;
		$countercicilan = 0;
		
		foreach($orDet as $k => $v) {
			$test = "payment_tenor_".$index;
			if($index == 0){
				if($a[$test]== '00' or !isset($a[$test])){
					$statusPayType = 1;
					$last = 1;
				}else{
					$statusPayType = 2;
					$last = 2;
					$countercicilan++;
				}
			}else{
				if($a[$test]== '00' or !isset($a[$test])){
					$statusPayType = 1;
				}else{
					$statusPayType = 2;
					$countercicilan++;
				}
				
			}
			if($last != $statusPayType){
				$last = 3;
			}
			$index++;
		}
		
		if($last == 1){
			$xml .= "<pay_type>1</pay_type>" . "\n";
		}else if($last == 2 ){
			$xml .= "<pay_type>2</pay_type>" . "\n";
		}else{
			echo "<script language=\"Javascript\">\n";
			echo "window.alert('Pembelian Tidak Bisa Dilakukan Dengan Sebagian Cicilan dan Sebagian Full Payment');";
			echo "</script>";
			exit;
			//$xml .= "<pay_type>3</pay_type>" . "\n";
		}
		$index = 0;
		foreach($orDet as $d => $od) {
			$price = $od['product_price']*100;
			$test = "payment_tenor_".$index;
			$xml .= "<item>" . "\n";
			$xml .= "<product>".$od['product_name']."</product>" . "\n";
			$xml .= "<qty>".$od['product_quantity']."</qty>" . "\n";
			$xml .= "<amount>".$price."</amount>" . "\n";
			if($a[$test]== '00'or !isset($a[$test])){
				$xml .= "<payment_plan>01</payment_plan>" . "\n";
				$xml .= "<tenor>00</tenor>" . "\n";
			}else{
				$xml .= "<payment_plan>02</payment_plan>" . "\n";
				$xml .= "<tenor>".$a[$test]."</tenor>" . "\n";
			}
			if($pg['id'] == '405'){
				if($a[$test] == '03'){
					$xml .= "<merchant_id>".$this->_faspay['mid_3']."</merchant_id>" . "\n";
				}elseif($a[$test] == '06'){
					$xml .= "<merchant_id>".$this->_faspay['mid_6']."</merchant_id>" . "\n";
				}elseif($a[$test] == '12'){
					$xml .= "<merchant_id>".$this->_faspay['mid_12']."</merchant_id>" . "\n";
				}elseif($a[$test] == '24'){
					$xml .= "<merchant_id>".$this->_faspay['mid_24']."</merchant_id>" . "\n";
				}else{
					$xml .= "<merchant_id>-</merchant_id>" . "\n";
				}
			}else{
				$xml .= "<merchant_id>-</merchant_id>" . "\n";
			}
			$xml .= "</item>" . "\n";
			$index++;
		}
		
		$xml .= "<reserve1></reserve1>" . "\n";
		$xml .= "<reserve2></reserve2>" . "\n";
		$xml .= "<signature>".$this->_bcakp_signature($order['id'])."</signature>" . "\n";
		$xml .= "</faspay>" . "\n";
		if($countercicilan>5) {
			echo "<script language=\"Javascript\">\n";
			echo "window.alert('Pembelian dengan Cicilan Tidak Bisa Lebih dari 5 Jenis Barang');";
			echo "</script>";
		exit;}
		return $xml;
		//$this->dump($xml);
	}
	private function _post_data(&$params, $a) {
		$xml = $this->_prep_data($params, $a);
		//$this->dump($xml);
		$header	= "POST HTTP/1.0 \r\n";
		$header .= "Content-type: text/xml \r\n";
		$header .= "Content-length: " . strlen($xml) . "\r\n";
		$header .= "Content-transfer-encoding: text \r\n";
		$header .= "Connection: close \r\n\r\n";
		$url 	= $this->_faspay['server']=='production' ? "https://faspay.mediaindonusa.com/pws/300002/383xx00010100000" :
			"http://faspaydev.mediaindonusa.com/pws/300002/183xx00010100000";
		$ch 	= curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($xml));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		return curl_exec($ch);
	}
	private function _resp_faspay($act, $data=null) {
		switch($act) {
			case 'post_data':
				$sql = "INSERT INTO "._DB_PREFIX_."order_payment_faspay(order_id, trx_id, payment_channel)
								values(".$data['order_id'].", '".$data['trx_id']."','".$data['payment_channel']."')";
				if($data['response_code']=='00') {
					$sql = "INSERT INTO "._DB_PREFIX_."order_payment_faspay(order_id, trx_id, payment_channel)
								values(".$data['order_id'].", '".$data['trx_id']."','".$data['payment_channel']."')";
					return Db::getInstance()->executeS($sql);
				}
			break;
			case 'faspay_report':
			if (is_array($data['payment_reff'])) $payment_reff='null';
			else $payment_reff=$data['payment_reff'];
				   //print_r($data);exit;
			$sql = "update  "._DB_PREFIX_."order_payment_faspay set
					payment_status = '$data[payment_status_desc]',
					payment_date = '$data[payment_date]',
					payment_reff = '$payment_reff'
				  where  trx_id = '$data[trx_id]'";
				Db::getInstance()->executeS($sql);
							
			$rc = array(2=>2, 3=>8, 4=>7, 7=>6, 8=>6);
			$sql = "update  "._DB_PREFIX_."orders a, "._DB_PREFIX_."order_payment_faspay b
					set  current_state = ".$rc[$data['payment_status_code']]."
					where a.id_order = b.order_id
					and  trx_id = '$data[trx_id]'";
			return Db::getInstance()->executeS($sql);   
				
				//Customer Update
				//$now = date('Y-m-d H:i:s ');
				
				//$sql="INSERT INTO "._DB_PREFIX_."order_history(id_employee, id_order, id_order_state, date_add) VALUES('0', '$data[bill_no]', '2', '$now' )";
				//Db::getInstance()->execute($sql);
				
				break;
			case 'thanks':
				
				$trx = $data['trx_id'];
				$sql = "select order_id from "._DB_PREFIX_."order_payment_faspay where trx_id = '$trx'";
			    $rsp = Db::getInstance()->executeS($sql);
				$merchantID=$this->_faspay['merchant_id'];
				$billno=$rsp[0]['order_id'];
				$sg = $this->_bcakp_signature($billno);
		
			
				$xml .= "<faspay>" . "\n";
				$xml .= "<request>Inquiry Status Payment</request>" . "\n";
				$xml .= "<trx_id>".$trx."</trx_id>" . "\n";
				$xml .= "<merchant_id>".$merchantID."</merchant_id>" . "\n";
				$xml .= "<bill_no>".$billno."</bill_no>" . "\n";
				$xml .= "<signature>".$sg."</signature>" . "\n";
				$xml .= "</faspay>" . "\n";
		
				$url 	= $this->_faspay['server']=='production' ? "https://faspay.mediaindonusa.com/pws/100004/383xx00010100000" :
						"http://faspaydev.mediaindonusa.com/pws/100004/183xx00010100000";
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 15);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
				curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($xml));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, $header);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
				$data=curl_exec($ch);
		
		
		
					$p = xml_parser_create();
					xml_parse_into_struct($p, $data, $vals, $index);
					xml_parser_free($p);

					for($i=0;$i<count($vals)&&$i<=20;$i++){
						if($i % 2 == 1){
						$a = $vals[15]['value'];		
							}
					}
				return $a;
				break;
			case 'getlist':
				$sql = "SELECT * FROM "._DB_PREFIX_."order_detail  
				        WHERE  id_order = '$data[trx_id]'";
				return Db::getInstance()->executeS($sql);
				break;
		}
	}
	private function _xml2array( $input, $callback = NULL, $_recurse = FALSE ) {
		$data = ( ( !$_recurse ) && is_string( $input ) ) ? simplexml_load_string( $input ) : $input;
		if ( $data instanceof SimpleXMLElement ) $data = (array) $data;
		if ( is_array( $data ) ) foreach ( $data as &$item ) $item = $this->_xml2array( $item, $callback, TRUE );
		return ( !is_array( $data ) && is_callable( $callback ) ) ? call_user_func( $callback, $data ) : $data;
	}
	
	public function execPayment($cart)
	{

		
		$pg = Tools::getValue('pg');

		if (!$this->active)
			return ;
		if (!$this->_checkCurrency($cart))
			Tools::redirectLink(__PS_BASE_URI__.'order.php');		
		$cartProducts = $cart->getProducts();
		
		global $cookie, $smarty;
		
		$smarty->assign(array(
			'pg' => $pg,
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->getCurrency((int)$cart->id_currency),
			'total' => $cart->getOrderTotal(true, Cart::BOTH),
			'cartProd' => $cartProducts,
			'mid_3' => Configuration::get('FASPAY_MID_3'),
			'status_mid_3' => Configuration::get('FASPAY_STATUS_MID_3'),
			'min_price_3' => Configuration::get('FASPAY_MIN_PRICE_3'),
			'mid_6' => Configuration::get('FASPAY_MID_6'),
			'min_price_6' => Configuration::get('FASPAY_MIN_PRICE_6'),
			'status_mid_6' => Configuration::get('FASPAY_STATUS_MID_6'),
			'mid_12' => Configuration::get('FASPAY_MID_12'),
			'min_price_12' => Configuration::get('FASPAY_MIN_PRICE_12'),
			'status_mid_12' => Configuration::get('FASPAY_STATUS_MID_12'),
			'mid_24' => Configuration::get('FASPAY_MID_24'),
			'min_price_24' => Configuration::get('FASPAY_MIN_PRICE_24'),
			'status_mid_24' => Configuration::get('FASPAY_STATUS_MID_24'),
			'this_path' => __PS_BASE_URI__,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
			
			
		));
		
		return $this->display(dirname(__FILE__), 'payment_execution.tpl');
	}
	private function _checkCurrency($cart)
	{
		$currency_order = new Currency((int)($cart->id_currency));
		$currencies_module = $this->getCurrency((int)$cart->id_currency);
		$currency_default = Configuration::get('PS_CURRENCY_DEFAULT');

		if (is_array($currencies_module))
			foreach ($currencies_module AS $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}
	public function execThanks($trx_id) {
		include(dirname(__FILE__).'/../../header.php');
		
		$dat = $this->thanks($trx_id);
		//$dat=2;
		global $cookie, $smarty;
		$smarty->assign(array(		
			'trx' => $trx_id,
			'dat' => $dat
		));		
		return $this->display(dirname(__FILE__), 'payment_thanks.tpl');
	}
	
	public function postProcess($cart,$pg) {
		
		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'faspay') {
				$authorized = true;
				break;
			}
		if (!$authorized)
			die($this->l('This payment method is not available.', 'validation'));

	
		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$pg 		= Tools::getValue('pg');
		$fp 		= new Faspay();
		$pgName		= $fp->pglist[$pg]['nm']." (via faspay)";
		$currency 	= $this->context->currency;
		$total 		= (float)$cart->getOrderTotal(true, Cart::BOTH);
		$mailVars 	= array(
			'{bankwire_owner}'   => Configuration::get('FASPAY_MERCHANT_NAME'),
			'{bankwire_details}' => nl2br(Configuration::get('FASPAY_MERCHANT_NAME')),
			'{bankwire_address}' => nl2br(Configuration::get('FASPAY_MERCHANT_NAME'))
		);
		$dat = $_POST;
		$concat = "";
		foreach($dat as $key => $value){
			$concat = $concat."&".$key."=".$value;
		}
		
		$this->validateOrder($cart->id, Configuration::get('PS_OS_BANKWIRE'), $total, $this->displayName, $pgName, $mailVars, (int)$currency->id, false, $customer->secure_key);		
		Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$this->id.'&id_order='.$this->currentOrder.'&key='.$customer->secure_key.'&pg='.$pg.$concat);

	}
	function dump($arg, $die=true) {
		if (is_string($arg) && preg_match("/xml/i", $arg)) {
			echo header("Content-type: application/xml");
			echo $arg;
		}
		else {
			echo "<br /><pre>";
			if(is_string($arg)) echo $arg;
			else print_r($arg);
			echo "</pre><br />";
		}
		if($die) die();
	}
	
	//webarq-tom: get delivery and billing address
	public function getOrderAddress($id_adresss){
		
		$sql_address = "SELECT a.phone_mobile, a.lastname, a.firstname, a.phone, cl.`name` as region,s.name as city, a.`postcode`, a.address1 
            		FROM `"._DB_PREFIX_."address` a
            		LEFT JOIN `"._DB_PREFIX_."country` c ON (a.`id_country` = c.`id_country`)
            		LEFT JOIN `"._DB_PREFIX_."country_lang` cl ON (c.`id_country` = cl.`id_country`)
            		LEFT JOIN `"._DB_PREFIX_."state` s ON (s.`id_state` = a.`id_state`)
            		WHERE `id_lang` = 1 AND `id_address` = ".$id_adresss;
		return Db::getInstance()->getRow($sql_address);
	}
}