<?php

/**
 * Title: WooCommerce Cardgate Bancontact gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateBancontact extends CGP_Common_Gateway {
    
    var $id = 'cardgatebancontact';
    var $title = '';
    var $method_title = 'Cardgate Bancontact';
    var $admin_title = 'Cardgate Bancontact';
    var $payment_name = 'Bancontact';
    var $payment_method = 'bancontact';
    var $company = 'CardGate';
    var $has_fields = false; //extra field for bank data

    public function __construct() {
	    parent::__construct();
    }
}
