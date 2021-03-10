<?php

/**
 * Title: WooCommerce Cardgate iDEAL QR gateway
 * Description: 
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateIdealqr extends CGP_Common_Gateway {

    var $id = 'cardgateaIdealqr';
    var $title = '';
    var $method_title = 'Cardgate iDEAL QR';
    var $admin_title = 'Cardgate iDEAL QR';
    var $payment_name = 'iDEAL QR';
    var $payment_method = 'idealqr';
    var $company = 'CardGate';
	public $supports = ['products', 'refunds'];
    var $has_fields = false; //extra field for bank data

    public function __construct() {
	    parent::__construct();
    }
}
