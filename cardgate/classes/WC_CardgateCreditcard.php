<?php

/**
 * Title: WooCommerce Cardgate Creditcard gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateCreditcard extends CGP_Common_Gateway {
    
    var $id = 'cardgatecreditcard';
    var $title = '';
    var $method_title = 'Cardgate Creditcard';
    var $admin_title = 'Cardgate Creditcard';
    var $payment_name = 'Creditcard';
    var $payment_method = 'creditcard';
    var $company = 'CardGate';
    var $has_fields = false; //extra field for bank data
    
    public function __construct() {
       parent::__construct();
    }
}
