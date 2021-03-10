<?php

/**
 * Title: WooCommerce Cardgate Billink gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateBillink extends CGP_Common_Gateway {
    
    var $id = 'cardgatebillink';
    var $title = '';
    var $method_title = 'Cardgate Billink';
    var $admin_title = 'Cardgate Billink';
    var $payment_name = 'Billink';
    var $payment_method = 'billink';
    var $company = 'CardGate';
	public $supports = ['products', 'refunds'];
    var $has_fields = false; //extra field for bank data
    
    public function __construct() {
	    parent::__construct();
    }
}
