<?php

/**
 * Title: WooCommerce Cardgate Bitcoin gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateBitcoin extends CGP_Common_Gateway {

	public $id             = 'cardgatebitcoin';
	public $title          = '';
	public $method_title   = 'Cardgate Bitcoin';
	public $admin_title    = 'Cardgate Bitcoin';
	public $payment_name   = 'Bitcoin';
	public $payment_method = 'bitcoin';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
