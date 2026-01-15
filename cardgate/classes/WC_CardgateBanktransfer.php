<?php

/**
 * Title: WooCommerce Cardgate Banktransfer gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateBanktransfer extends CGP_Common_Gateway {

	public $id             = 'cardgatebanktransfer';
	public $title          = '';
	public $method_title   = 'Cardgate Banktransfer';
	public $admin_title    = 'Cardgate Banktransfer';
	public $payment_name   = 'Banktransfer';
	public $payment_method = 'banktransfer';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
