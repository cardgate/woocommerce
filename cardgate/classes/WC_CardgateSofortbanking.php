<?php

/**
 * Title: WooCommerce Cardgate Sofortbanking gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateSofortbanking extends CGP_Common_Gateway {

	public $id             = 'cardgatesofortbanking';
	public $title          = '';
	public $method_title   = 'Cardgate Sofortbanking';
	public $admin_title    = 'Cardgate Sofortbanking';
	public $payment_name   = 'Sofortbanking';
	public $payment_method = 'sofortbanking';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
