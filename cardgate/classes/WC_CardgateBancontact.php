<?php

/**
 * Title: WooCommerce Cardgate Bancontact gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateBancontact extends CGP_Common_Gateway {

	public $id             = 'cardgatebancontact';
	public $title          = '';
	public $method_title   = 'Cardgate Bancontact';
	public $admin_title    = 'Cardgate Bancontact';
	public $payment_name   = 'Bancontact';
	public $payment_method = 'bancontact';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
