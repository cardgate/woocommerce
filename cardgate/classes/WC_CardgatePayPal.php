<?php

/**
 * Title: WooCommerce Cardgate PayPal gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgatePayPal extends CGP_Common_Gateway {
    
    var $id = 'cardgatepaypal';
    var $title = '';
    var $method_title = 'Cardgate PayPal';
    var $admin_title = 'Cardgate PayPal';
    var $payment_name = 'PayPal';
    var $payment_method = 'paypal';
    var $company = 'CardGate';
	public $supports = ['products', 'refunds'];
    var $has_fields = false; //extra field for bank data

    public function __construct() {
	    parent::__construct();
    }
}
