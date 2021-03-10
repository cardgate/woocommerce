<?php

/**
 * Title: WooCommerce Cardgate DirectDebit gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateDirectDebit extends CGP_Common_Gateway {
    
    var $id = 'cardgatedirectdebit';
    var $title = '';
    var $method_title = 'Cardgate DirectDebit';
    var $admin_title = 'Cardgate DirectDebit';
    var $payment_name = 'DirectDebit';
    var $payment_method = 'directdebit';
    var $company = 'CardGate';
	public $supports = ['products', 'refunds'];
    var $has_fields = false; //extra field for bank data
    
    public function __construct() {
	    parent::__construct();
    }
}
