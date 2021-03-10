<?php

/**
 * Title: WooCommerce Cardgate Giropay gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateGiropay extends CGP_Common_Gateway {
    
    var $id = 'cardgategiropay';
    var $title = '';
    var $method_title = 'Cardgate Giropay';
    var $admin_title = 'Cardgate Giropay';
    var $payment_name = 'Giropay';
    var $payment_method = 'giropay';
    var $company = 'CardGate';
	public $supports = ['products', 'refunds'];
    var $has_fields = false; //extra field for bank data
    
    public function __construct() {
	    parent::__construct();
    }
}
