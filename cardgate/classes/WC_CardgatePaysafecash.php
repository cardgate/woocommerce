<?php

/**
 * Title: WooCommerce Cardgate Paysafecash gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgatePaysafecash extends CGP_Common_Gateway {
    
    var $id = 'cardgatepaysafecash';
    var $title = '';
    var $method_title = 'Cardgate Paysafecash';
    var $admin_title = 'Cardgate Paysafecash';
    var $payment_name = 'Paysafecash';
    var $payment_method = 'paysafecash';
    var $company = 'CardGate';
	public $supports = ['products', 'refunds'];
    var $has_fields = false; //extra field for bank data

    public function __construct() {
	    parent::__construct();
    }
}
