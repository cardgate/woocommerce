<?php

/**
 * Title: WooCommerce Cardgate Afterpay gateway
 * Description: 
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author Richard Schoots
 * @version 1.0
 */
class WC_CardgateAfterpay extends CGP_Common_Gateway {

    /**
     * The unique ID of this payment gateway
     * 
     * @const ID string
     */
    const ID = 'cardgateafterpay';
    const MethodTitle = 'Cardgate Afterpay';
    const AdminTitle = 'Cardgate Afterpay';
    const PaymentName = 'Afterpay';
    const Company = 'Cardgate';
    const HasFields = false;   //extra field for bank data
    const PaymentMethod = 'afterpay';

    //////////////////////////////////////////////////
    var $DOB_Option;

    /**
     * Constructs and initialize a gateway
     */
    public function __construct() {

        $this->id = self::ID;
        $this->method_title = self::MethodTitle;
        $this->admin_title = self::AdminTitle;
        $this->company = self::Company;
        $this->payment_name = self::PaymentName;
        $this->payment_method = self::PaymentMethod;

        // The iDEAL payment gateway has an issuer select field for the bank options
        $this->has_fields = self::HasFields;

        // Load the form fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Define user set variables
        $this->title = $this->settings['title'];
        $this->description = $this->settings['description'];

        // Actions
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_receipt_' . self::ID, array( $this, 'receiptPage' ) );
    }
}
