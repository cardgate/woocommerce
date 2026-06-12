<?php

/**
 * Title: WooCommerce Cardgate Przelewy24 gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgatePrzelewy24 extends CGP_Common_Gateway {

	public $id             = 'cardgateprzelewy24';
	public $title          = '';
	public $method_title   = 'Cardgate Przelewy24';
	public $admin_title    = 'Cardgate Przelewy24';
	public $payment_name   = 'Przelewy24';
	public $payment_method = 'przelewy24';
	public $company        = 'CardGate';
	public $supports       = array(
		'products',
		'refunds',
		'subscriptions',
		'subscription_cancellation',
		'subscription_suspension',
		'subscription_reactivation',
		'subscription_amount_changes',
		'subscription_date_changes',
		'subscription_payment_method_change',
		'subscription_payment_method_change_customer',
		'subscription_payment_method_change_admin',
		'multiple_subscriptions',
	);
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
