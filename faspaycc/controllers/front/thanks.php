<?php

class FaspayccThanksModuleFrontController extends ModuleFrontController {
	public $display_column_right	= false;
	public $display_column_left 	= false;
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent() {
		parent::initContent();
		$pg  = Tools::getValue('pg');
		$trx = Tools::getValue('trx_id');
		$fp  = new Faspay();
		$dat = $fp->thanks($trx);
		$this->context->smarty->assign(array(
			'pg'  => $fp->pglist[$pg],
			'trx' => $trx,
			'dat' => $dat
		));

		$this->setTemplate('payment_thanks.tpl');
	}
}
