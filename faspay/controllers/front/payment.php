<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 13573 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */
class FaspayPaymentModuleFrontController extends ModuleFrontController {
	public $display_column_left = false;
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent() {
		parent::initContent();

		$cart = $this->context->cart;
		$cartProducts = $cart->getProducts();
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');
		$pg = Tools::getValue('pg');
		$fp = new Faspay();
		$this->context->smarty->assign(array(
			'pg' => $fp->pglist[$pg],
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->module->getCurrency((int)$cart->id_currency),
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
			'mid_visamaster' => Configuration::get('FASPAY_MID_VISAMASTER'),
			'pas_visamaster' => Configuration::get('FASPAY_PAS_VISAMASTER'),
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));

		$this->setTemplate('payment_execution.tpl');
	}
}
