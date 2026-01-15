<?php

/**
 * Title: WooCommerce Cardgate Paysafecard gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgatePaysafecard extends CGP_Common_Gateway {

	public $id             = 'cardgatepaysafecard';
	public $title          = '';
	public $method_title   = 'Cardgate Paysafecard';
	public $admin_title    = 'Cardgate Paysafecard';
	public $payment_name   = 'Paysafecard';
	public $payment_method = 'paysafecard';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
