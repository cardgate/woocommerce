<?php

/**
 * Title: WooCommerce Cardgate Klarna gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateKlarna extends CGP_Common_Gateway {

	public $id             = 'cardgateklarna';
	public $title          = '';
	public $method_title   = 'Cardgate Klarna';
	public $admin_title    = 'Cardgate Klarna';
	public $payment_name   = 'Klarna';
	public $payment_method = 'klarna';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
