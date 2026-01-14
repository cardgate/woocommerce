<?php

/**
 * Title: WooCommerce Cardgate Paysafecash gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgatePaysafecash extends CGP_Common_Gateway {

	public $id             = 'cardgatepaysafecash';
	public $title          = '';
	public $method_title   = 'Cardgate Paysafecash';
	public $admin_title    = 'Cardgate Paysafecash';
	public $payment_name   = 'Paysafecash';
	public $payment_method = 'paysafecash';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
