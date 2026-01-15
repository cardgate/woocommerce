<?php

/**
 * Title: WooCommerce Cardgate SprayPay gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateSpraypay extends CGP_Common_Gateway {

	public $id             = 'cardgatespraypay';
	public $title          = '';
	public $method_title   = 'Cardgate SprayPay';
	public $admin_title    = 'Cardgate SprayPay';
	public $payment_name   = 'SprayPay';
	public $payment_method = 'spraypay';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
