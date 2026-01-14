<?php

/**
 * Title: WooCommerce Cardgate PayPal gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgatePayPal extends CGP_Common_Gateway {

	public $id             = 'cardgatepaypal';
	public $title          = '';
	public $method_title   = 'Cardgate PayPal';
	public $admin_title    = 'Cardgate PayPal';
	public $payment_name   = 'PayPal';
	public $payment_method = 'paypal';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
