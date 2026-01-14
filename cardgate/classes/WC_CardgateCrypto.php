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
class WC_CardgateCrypto extends CGP_Common_Gateway {

	public $id             = 'cardgatecrypto';
	public $title          = '';
	public $method_title   = 'Cardgate Crypto';
	public $admin_title    = 'Cardgate Crypto';
	public $payment_name   = 'Crypto';
	public $payment_method = 'crypto';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
