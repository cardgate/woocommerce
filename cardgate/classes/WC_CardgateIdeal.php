<?php

/**
 * Title: WooCommerce Cardgate iDEAL gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateIdeal extends CGP_Common_Gateway {

	public $id             = 'cardgateideal';
	public $title          = '';
	public $method_title   = 'Cardgate iDEAL';
	public $admin_title    = 'Cardgate iDEAL';
	public $payment_name   = 'iDEAL';
	public $payment_method = 'ideal';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // no more bank field

	public function __construct() {
		parent::__construct();
	}
}
