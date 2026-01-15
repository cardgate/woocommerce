<?php

/**
 * Title: WooCommerce Cardgate iDEAL QR gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateIdealqr extends CGP_Common_Gateway {

	public $id             = 'cardgateidealqr';
	public $title          = '';
	public $method_title   = 'Cardgate iDEAL QR';
	public $admin_title    = 'Cardgate iDEAL QR';
	public $payment_name   = 'iDEAL QR';
	public $payment_method = 'idealqr';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
