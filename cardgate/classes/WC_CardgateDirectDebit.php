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
