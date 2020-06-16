<?php

/**
 * Title: WooCommerce Cardgate Klarna gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateKlarna extends CGP_Common_Gateway {
    
    var $id = 'cardgateklarna';
    var $title = '';
    var $method_title = 'Cardgate Klarna';
    var $admin_title = 'Cardgate Klarna';
    var $payment_name = 'Klarna';
    var $payment_method = 'klarna';
    var $company = 'CardGate';
    var $has_fields = false; //extra field for bank data

    public function __construct() {
	    parent::__construct();
    }
}
