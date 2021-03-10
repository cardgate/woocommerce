<?php

/**
 * Title: WooCommerce Cardgate Afterpay gateway
 * Description: 
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateAfterpay extends CGP_Common_Gateway {

    var $id = 'cardgateafterpay';
    var $title = '';
    var $method_title = 'Cardgate Afterpay';
    var $admin_title = 'Cardgate Afterpay';
    var $payment_name = 'Afterpay';
    var $payment_method = 'afterpay';
    var $company = 'CardGate';
	public $supports = ['products', 'refunds'];
    var $has_fields = false; //extra field for bank data

    public function __construct() {
	    parent::__construct();
    }
}
