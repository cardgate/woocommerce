<?php

/**
 * Title: WooCommerce Cardgate Gift Card gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateGiftcard extends CGP_Common_Gateway {

	public $id             = 'cardgategiftcard';
	public $title          = '';
	public $method_title   = 'Cardgate Gift Card';
	public $admin_title    = 'Cardgate Gift Card';
	public $payment_name   = 'Gift Card';
	public $payment_method = 'giftcard';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
