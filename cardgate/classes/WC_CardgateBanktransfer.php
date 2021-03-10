<?php

/**
 * Title: WooCommerce Cardgate Banktransfer gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateBanktransfer extends CGP_Common_Gateway {
    
    var $id = 'cardgatebanktransfer';
    var $title = '';
    var $method_title = 'Cardgate Banktransfer';
    var $admin_title = 'Cardgate Banktransfer';
    var $payment_name = 'Banktransfer';
    var $payment_method = 'banktransfer';
    var $company = 'CardGate';
	public $supports = ['products', 'refunds'];
    var $has_fields = false; //extra field for bank data

    public function __construct() {
	    parent::__construct();
    }
}
