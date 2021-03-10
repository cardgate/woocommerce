<?php

/**
 * Title: WooCommerce Cardgate Paysafecard gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgatePaysafecard extends CGP_Common_Gateway {
    
    var $id = 'cardgatepaysafecard';
    var $title = '';
    var $method_title = 'Cardgate Paysafecard';
    var $admin_title = 'Cardgate Paysafecard';
    var $payment_name = 'Paysafecard';
    var $payment_method = 'paysafecard';
    var $company = 'CardGate';
	public $supports = ['products', 'refunds'];
    var $has_fields = false; //extra field for bank data

    public function __construct() {
	    parent::__construct();
    }
}
