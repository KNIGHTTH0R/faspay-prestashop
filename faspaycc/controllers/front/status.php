<?php

class FaspayccstatusModuleFrontController extends ModuleFrontController {
	public $display_column_right	= false;
	public $display_column_left 	= false;
	public $ssl = true;
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent() {
		$this->bootstrap = true;
		parent::initContent();
		$data = $_POST;


		$fcc = new Faspaycc();

		$order = $fcc->doPayment($data);
		$data = array('current_state' => $order->current_state , 
					'id' => $order->id,
					'total_paid' => "Rp ". number_format($order->total_paid, 2),
					'total_products' => "Rp ".number_format($order->total_products, 2),
					'shipping' => "Rp ".number_format($order->total_shipping, 2));
		global $smarty;
		$smarty->assign('order', $data);
		$this->setTemplate('payment_thanks.tpl');
	}
}
