<?php

/**
 * Title: WooCommerce Cardgate SprayPay gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateSpraypay extends CGP_Common_Gateway {
    
    var $id = 'cardgatespraypay';
    var $title = '';
    var $method_title = 'Cardgate SprayPay';
    var $admin_title = 'Cardgate SprayPay';
    var $payment_name = 'SprayPay';
    var $payment_method = 'spraypay';
    var $company = 'CardGate';
	public $supports = ['products', 'refunds'];
    var $has_fields = false; //extra field for bank data
    
    public function __construct() {
	    parent::__construct();
    }
}
