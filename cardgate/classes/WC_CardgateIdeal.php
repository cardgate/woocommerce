<?php

/**
 * Title: WooCommerce Cardgate iDEAL gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateIdeal extends CGP_Common_Gateway {
    
    var $id = 'cardgateideal';
    var $title = '';
    var $method_title = 'Cardgate iDEAL';
    var $admin_title = 'Cardgate iDEAL';
    var $payment_name = 'iDEAL';
    var $payment_method = 'ideal';
    var $company = 'CardGate';
    var $has_fields = true; //extra field for bank data
    
    public function __construct() {
	    parent::__construct();
    }
}
