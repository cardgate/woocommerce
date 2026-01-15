<?php

/**
 * Title: WooCommerce Cardgate Afterpay gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateAfterpay extends CGP_Common_Gateway {

	public $id             = 'cardgateafterpay';
	public $title          = '';
	public $method_title   = 'Cardgate Afterpay';
	public $admin_title    = 'Cardgate Afterpay';
	public $payment_name   = 'Afterpay';
	public $payment_method = 'afterpay';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
