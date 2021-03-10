<?php

/**
 * Title: WooCommerce Cardgate Mister Cash gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateMisterCash extends CGP_Common_Gateway {
    
    var $id = 'cardgatemistercash';
    var $title = '';
    var $method_title = 'Cardgate Mister Cash';
    var $admin_title = 'Cardgate Mister Cash';
    var $payment_name = 'Mister Cash';
    var $payment_method = 'mistercash';
    var $company = 'CardGate';
	public $supports = ['products', 'refunds'];
    var $has_fields = false; //extra field for bank data

    public function __construct() {
	    parent::__construct();
    }
}
