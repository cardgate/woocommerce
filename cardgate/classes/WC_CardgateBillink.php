<?php

/**
 * Title: WooCommerce Cardgate Billink gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateBillink extends CGP_Common_Gateway {

	public $id             = 'cardgatebillink';
	public $title          = '';
	public $method_title   = 'Cardgate Billink';
	public $admin_title    = 'Cardgate Billink';
	public $payment_name   = 'Billink';
	public $payment_method = 'billink';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
