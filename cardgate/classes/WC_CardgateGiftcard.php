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
        
        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->payment_name;
        $this->description = $this->settings['description'];
 
        add_filter ( 'woocommerce_gateway_icon', array($this, 'modify_icon'), 20, 2 );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receiptPage' ) );
    }
}
