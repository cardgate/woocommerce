<?php

/**
 * Title: WooCommerce Cardgate Sofortbanking gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateSofortbanking extends CGP_Common_Gateway {
    
    var $id = 'cardgatesofortbanking';
    var $title = '';
    var $method_title = 'Cardgate Sofortbanking';
    var $admin_title = 'Cardgate Sofortbanking';
    var $payment_name = 'Sofortbanking';
    var $payment_method = 'sofortbanking';
    var $company = 'CardGate';
    var $has_fields = false; //extra field for bank data
  
    public function __construct() {
	    parent::__construct();
    }
}
