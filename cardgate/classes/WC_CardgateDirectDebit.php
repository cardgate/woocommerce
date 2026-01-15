<?php

/**
 * Title: WooCommerce Cardgate DirectDebit gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateDirectDebit extends CGP_Common_Gateway {

	public $id             = 'cardgatedirectdebit';
	public $title          = '';
	public $method_title   = 'Cardgate DirectDebit';
	public $admin_title    = 'Cardgate DirectDebit';
	public $payment_name   = 'DirectDebit';
	public $payment_method = 'directdebit';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
