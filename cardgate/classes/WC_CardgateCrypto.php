<?php

/**
 * Title: WooCommerce Cardgate Bitcoin gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateCrypto extends CGP_Common_Gateway {
    
    var $id = 'cardgatecrypto';
    var $title = '';
    var $method_title = 'Cardgate Crypto';
    var $admin_title = 'Cardgate Crypto';
    var $payment_name = 'Crypto';
    var $payment_method = 'crypto';
    var $company = 'CardGate';
	public $supports = ['products', 'refunds'];
    var $has_fields = false; //extra field for bank data
    
    public function __construct() {
	    parent::__construct();
    }
}
