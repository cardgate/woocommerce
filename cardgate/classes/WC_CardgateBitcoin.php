<?php

/**
 * Title: WooCommerce Cardgate Bitcoin gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateBitcoin extends CGP_Common_Gateway {
    
    var $id = 'cardgatebitcoin';
    var $title = '';
    var $method_title = 'Cardgate Bitcoin';
    var $admin_title = 'Cardgate Bitcoin';
    var $payment_name = 'Bitcoin';
    var $payment_method = 'bitcoin';
    var $company = 'CardGate';
    var $has_fields = false; //extra field for bank data
    
    public function __construct() {
	    parent::__construct();
    }
}
