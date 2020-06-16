<?php

/**
 * Title: WooCommerce Cardgate Gift Card gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateGiftcard extends CGP_Common_Gateway {
    
    var $id = 'cardgategiftcard';
    var $title = '';
    var $method_title = 'Cardgate Gift Card';
    var $admin_title = 'Cardgate Gift Card';
    var $payment_name = 'Gift Card';
    var $payment_method = 'giftcard';
    var $company = 'CardGate';
    var $has_fields = false; //extra field for bank data
    
    public function __construct() {
	    parent::__construct();
    }
}
