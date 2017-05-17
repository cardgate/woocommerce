<?php

/**
 * Title: WooCommerce Cardgate Creditcard gateway
 * Description: 
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author Richard Schoots
 * @version 1.0
 */
class WC_CardgateCreditcard extends CGP_Common_Gateway {

    /**
     * The unique ID of this payment gateway
     * 
     * @const ID string
     */
    const ID = 'cardgatecreditcard';
    const MethodTitle = 'Cardgate Creditcard';
    const AdminTitle = 'Cardgate Creditcard';
    const PaymentName = 'Creditcard';
    const Company = 'Cardgate';
    const HasFields = false;   //extra field for bank data
    const PaymentMethod = 'creditcard';

    //////////////////////////////////////////////////

    /**
     * Constructs and initialize a gateway
     */
    public function __construct() {

        $this->supports = array( 
            'products'
        );

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
        add_action( 'cancelled_subscription_' . self::ID,array(&$this,'cancel_subscription'), 10, 2 );
        add_action( 'subscription_put_on-hold_' . self::ID,array(&$this,'suspend_subscription'), 10, 2 );
        add_action( 'reactivated_subscription_' . self::ID,array(&$this, 'reactivate_subscription'), 10, 2 );
    }
    
    function cancel_subscription( $order, $product_id = '', $profile_id = '' ) {
        $status = 'cancel';
        parent::change_subscription( $order, $status );
    }

    function suspend_subscription( $order, $product_id ) {

        $status = 'suspend';
        parent::change_subscription( $order, $status );
    }

    function reactivate_subscription( $order, $product_id ) {
        $status = 'reactivate';
        parent::change_subscription( $order, $status );
    }
    
    function trash_subscription ($order){
        $status = 'deactivate';
        parent::change_subscription( $order, $status );
    }
}
