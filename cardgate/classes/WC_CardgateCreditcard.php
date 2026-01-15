<?php

/**
 * Title: WooCommerce Cardgate Creditcard gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateCreditcard extends CGP_Common_Gateway {

	public $id             = 'cardgatecreditcard';
	public $title          = '';
	public $method_title   = 'Cardgate Creditcard';
	public $admin_title    = 'Cardgate Creditcard';
	public $payment_name   = 'Creditcard';
	public $payment_method = 'creditcard';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
