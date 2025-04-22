<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Exit if accessed directly

/*
 * Title: WooCommerce CGP Common Gateway
 * Description: Gateway class
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Cardgate
 * @author CardGate
 * @version 1.0.0
 */

class CGP_Common_Gateway extends WC_Payment_Gateway {

	var $bankOption;
	var $logo;
	var $bSeperateSalesTax;
    var $instructions;

	// ////////////////////////////////////////////////
	public function __construct() {
		$this->init_form_fields();
		$this->init_settings();
		$this->title = (isset($this->settings['title']) && !empty($this->settings['title']) ? $this->settings['title'] : $this->payment_name);
		$this->description = $this->settings['description'];
        $this->instructions = (!empty($this->settings['instructions']) ? $this->settings['instructions'] : '');

		add_filter ( 'woocommerce_gateway_icon', array($this, 'modify_icon'), 20, 2 );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receiptPage' ) );
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}

	/**
	 * Show the description if set, and show the bank options.
	 */
	public function payment_fields() {
		if ( $this->description ) {
			echo wpautop( wptexturize( $this->description ) );
		}
	}

    /**
     * Output for the order received page.
     */
    public function thankyou_page() {
        if ( $this->instructions ) {
            echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
        }
    }

	// //////////////////////////////////////////////

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'enabled'     => [
				'title'   => __( 'Enable/Disable', 'cardgate' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable ' . $this->payment_name, 'cardgate' ),
				'default' => 'no'
			],
			'title'       => [
				'title'       => __( 'Title', 'cardgate' ),
				'type'        => 'text',
				'description' => __( 'Payment method title that the customer will see on your checkout.', 'cardgate' ),
				'default'     => $this->payment_name,
				'desc_tip'    => true
			],
			'description' => [
				'title'       => __( 'Description', 'cardgate' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your website.', 'cardgate' ),
				'default'     => __( 'Pay with ', 'cardgate' ) . $this->payment_name,
				'desc_tip'    => true
			],
            'instructions'       => [
                'title'       => __( 'Instructions', 'cardgate' ),
                'type'        => 'textarea',
                'description' => __( 'Instructions that will be added to the thank you page.', 'cardgate' ),
                'default'     => __( '', 'cardgate' ),
                'desc_tip'    => true
            ]
		];
	}

	// ////////////////////////////////////////////////

	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {
		?>
        <h3>
			<?php _e( $this->admin_title, $this->id ); ?>
        </h3>
        <table class="form-table">
			<?php $this->generate_settings_html(); ?>
        </table>
        <!--/.form-table-->
		<?php
	}

	// ////////////////////////////////////////////////

	/**
	 * Process the payment and return the result
	 *
	 * @param integer $iOrderId
	 */
	public function process_payment( $iOrderId ) {
		global $woocommerce;
		try {
			$oOrder = new WC_Order( $iOrderId );
            $this->correct_payment_fee($oOrder);
            $oOrder->calculate_totals(false);
            $oOrder->save();
			$this->savePaymentData( $iOrderId );

			$iMerchantId     = ( get_option( 'cgp_merchant_id' ) ? get_option( 'cgp_merchant_id' ) : 0 );
			$sMerchantApiKey = ( get_option( 'cgp_merchant_api_key' ) ? get_option( 'cgp_merchant_api_key' ) : 0 );
			$bIsTest         = ( get_option( 'cgp_mode' ) == 1 ? true : false );
			$sLanguage       = substr( get_locale(), 0, 2 );

			$sVersion = ( $this->get_woocommerce_version() == '' ? 'unkown' : $this->get_woocommerce_version() );

			$oCardGate = new cardgate\api\Client( (int) $iMerchantId, $sMerchantApiKey, $bIsTest );

			$oCardGate->setIp( $_SERVER['REMOTE_ADDR'] );

			$oCardGate->setLanguage( $sLanguage );
			$oCardGate->version()->setPlatformName( 'Woocommerce' );
			$oCardGate->version()->setPlatformVersion( $sVersion );
			$oCardGate->version()->setPluginName( 'CardGate' );
			$oCardGate->version()->setPluginVersion( get_option( 'cardgate_version' ) );

			$iSiteId  = (int) get_option( 'cgp_siteid' );
			$amount   = (int) round( $oOrder->get_total() * 100 );
			$currency = get_woocommerce_currency();

			$oTransaction = $oCardGate->transactions()->create( $iSiteId, $amount, $currency );

			// Configure payment option.
			$oTransaction->setPaymentMethod( $this->payment_method );

			method_exists( $oOrder, 'get_billing_email' ) ? $billing_email = $oOrder->get_billing_email() : $billing_email = $oOrder->billing_email;
			method_exists( $oOrder, 'get_billing_phone' ) ? $billing_phone = $oOrder->get_billing_phone() : $billing_phone = $oOrder->billing_phone;
			method_exists( $oOrder, 'get_billing_first_name' ) ? $billing_first_name = $oOrder->get_billing_first_name() : $billing_first_name = $oOrder->billing_first_name;
			method_exists( $oOrder, 'get_billing_last_name' ) ? $billing_last_name = $oOrder->get_billing_last_name() : $billing_last_name = $oOrder->billing_last_name;
			method_exists( $oOrder, 'get_billing_address_1' ) ? $billing_address_1 = $oOrder->get_billing_address_1() : $billing_address_1 = $oOrder->billing_address_1;
			method_exists( $oOrder, 'get_billing_address_2' ) ? $billing_address_2 = $oOrder->get_billing_address_2() : $billing_address_2 = $oOrder->billing_address_2;
			method_exists( $oOrder, 'get_billing_postcode' ) ? $billing_postcode = $oOrder->get_billing_postcode() : $billing_postcode = $oOrder->billing_postcode;
			method_exists( $oOrder, 'get_billing_state' ) ? $billing_state = $oOrder->get_billing_state() : $billing_state = $oOrder->billing_state;
			method_exists( $oOrder, 'get_billing_city' ) ? $billing_city = $oOrder->get_billing_city() : $billing_city = $oOrder->billing_city;
			method_exists( $oOrder, 'get_billing_country' ) ? $billing_country = $oOrder->get_billing_country() : $billing_country = $oOrder->billing_country;

			// Configure customer.
			$billing_address = trim( $billing_address_1 . ' ' . $billing_address_2 );

			$oConsumer = $oTransaction->getConsumer();
			if ( $billing_email != '' ) {
				$oConsumer->setEmail( $billing_email );
			}
			if ( $billing_phone != '' ) {
				$oConsumer->setPhone( $billing_phone );
			}
			if ( $billing_first_name != '' ) {
				$oConsumer->address()->setFirstName( $billing_first_name );
			}
			if ( $billing_last_name != '' ) {
				$oConsumer->address()->setLastName( $billing_last_name );
			}
			if ( $billing_address != '' ) {
				$oConsumer->address()->setAddress( trim( $billing_address_1 . ' ' . $billing_address_2 ) );
			}
			if ( $billing_postcode != '' ) {
				$oConsumer->address()->setZipCode( $billing_postcode );
			}
			if ( $billing_city != '' ) {
				$oConsumer->address()->setCity( $billing_city );
			}
			if ( $billing_state != '' ) {
				$oConsumer->address()->setState( $billing_state );
			}
			if ( $billing_country != '' ) {
				$oConsumer->address()->setCountry( $billing_country );
			}

			method_exists( $oOrder, 'get_shipping_first_name' ) ? $shipping_first_name = $oOrder->get_shipping_first_name() : $shipping_first_name = $oOrder->shipping_first_name;
			method_exists( $oOrder, 'get_shipping_last_name' ) ? $shipping_last_name = $oOrder->get_shipping_last_name() : $shipping_last_name = $oOrder->shipping_last_name;
			method_exists( $oOrder, 'get_shipping_last_name' ) ? $shipping_last_name = $oOrder->get_shipping_last_name() : $shipping_last_name = $oOrder->shipping_last_name;
			method_exists( $oOrder, 'get_shipping_address_1' ) ? $shipping_address_1 = $oOrder->get_shipping_address_1() : $shipping_address_1 = $oOrder->shipping_address_1;
			method_exists( $oOrder, 'get_shipping_address_2' ) ? $shipping_address_2 = $oOrder->get_shipping_address_2() : $shipping_address_2 = $oOrder->shipping_address_2;
			method_exists( $oOrder, 'get_shipping_postcode' ) ? $shipping_postcode = $oOrder->get_shipping_postcode() : $shipping_postcode = $oOrder->shipping_postcode;
			method_exists( $oOrder, 'get_shipping_state' ) ? $shipping_state = $oOrder->get_shipping_state() : $shipping_state = $oOrder->shipping_state;
			method_exists( $oOrder, 'get_shipping_city' ) ? $shipping_city = $oOrder->get_shipping_city() : $shipping_city = $oOrder->shipping_city;
			method_exists( $oOrder, 'get_shipping_country' ) ? $shipping_country = $oOrder->get_shipping_country() : $shipping_country = $oOrder->shipping_country;

			$shipping_address = trim( $shipping_address_1 . ' ' . $shipping_address_2 );

			if ( $shipping_first_name != '' ) {
				$oConsumer->shippingAddress()->setFirstName( $shipping_first_name );
			}
			if ( $shipping_last_name != '' ) {
				$oConsumer->shippingAddress()->setLastName( $shipping_last_name );
			}
			if ( $shipping_address != '' ) {
				$oConsumer->shippingAddress()->setAddress( trim( $shipping_address_1 . ' ' . $shipping_address_2 ) );
			}
			if ( $shipping_postcode != '' ) {
				$oConsumer->shippingAddress()->setZipCode( $shipping_postcode );
			}
			if ( $shipping_city != '' ) {
				$oConsumer->shippingAddress()->setCity( $shipping_city );
			}
			if ( $shipping_state != '' ) {
				$oConsumer->shippingAddress()->setState( $shipping_state );
			}
			if ( $shipping_country != '' ) {
				$oConsumer->shippingAddress()->setCountry( $shipping_country );
			}

			$oCart      = $oTransaction->getCart();
			$aCartItems = $this->getCartItems( $iOrderId );

			foreach ( $aCartItems as $item ) {

				switch ( $item['type'] ) {
					case 'product':
						$iItemType = \cardgate\api\Item::TYPE_PRODUCT;
						break;
					case 'shipping':
						$iItemType = \cardgate\api\Item::TYPE_SHIPPING;
						break;
					case 'paymentfee':
						$iItemType = \cardgate\api\Item::TYPE_HANDLING;
						break;
					case 'discount':
						$iItemType = \cardgate\api\Item::TYPE_DISCOUNT;
						break;
					case 'correction':
						$iItemType = \cardgate\api\Item::TYPE_CORRECTION;
						break;
					case 'vatcorrection':
						$iItemType = \cardgate\api\Item::TYPE_VAT_CORRECTION;
						break;
				}

				$oItem = $oCart->addItem( $iItemType, $item['model'], $item['name'], (int) $item['quantity'], (int) $item['price_wt'] );
				$oItem->setVat( $item['vat'] );
				$oItem->setVatAmount( $item['vat_amount'] );
				$oItem->setVatIncluded( 0 );
			}
			if ( method_exists( $oOrder, 'get_cancel_order_url_raw' ) ) {
				$sCanceUrl = $oOrder->get_cancel_order_url_raw();
			} else {
				$sCanceUrl = $oOrder->get_cancel_order_url();
			}

			$oTransaction->setCallbackUrl( site_url() . '/index.php?cgp_notify=true' );
			$oTransaction->setSuccessUrl( $this->get_return_url( $oOrder ) );
			$oTransaction->setFailureUrl( $sCanceUrl );
			$oTransaction->setReference( 'O' . time() . $iOrderId );
			$oTransaction->setDescription( 'Order ' . $this->swap_order_number( $iOrderId ) );

			$oTransaction->register();

			$sActionUrl = $oTransaction->getActionUrl();

			if ( null !== $sActionUrl ) {
				return [
					'result'   => 'success',
					'redirect' => trim( $sActionUrl )
				];
			} else {
				$sErrorMessage = 'CardGate error: ' .'no redirect URL';
				wc_add_notice( $sErrorMessage, 'error' );

				return [
					'result'   => 'success',
					'redirect' => wc_get_checkout_url()
				];
			}
		} catch ( cardgate\api\Exception $oException_ ) {
			$sErrorMessage = 'CardGate error: ' . htmlspecialchars( $oException_->getMessage() );
			wc_add_notice( $sErrorMessage, 'error' );

			return [
				'result'   => 'success',
				'redirect' => wc_get_checkout_url()
			];
		}
	}

    protected function correct_payment_fee(&$oOrder) {
        if ($this->has_block_checkout()){
	        $fees = $oOrder->get_fees();
            $feeData = $this->getFeeData($oOrder->get_payment_method());
	        $hasFee = array_key_exists('fee',$feeData) && $feeData['fee'] !== 0.0;
            $correctedFee = false;
	        foreach ($fees as $fee) {
		        $feeName = $fee->get_name();
		        $feeId = $fee->get_id();
		        $hasCardgateFee = strpos($feeName, $feeData['label']) !== false;
		        if ($hasCardgateFee) {
			        if ($feeData['amount'] == (float)$fee->get_amount('edit')) {
				        $correctedFee = true;
				        continue;
			        }
			        if (!$correctedFee) {
				        $this->removeOrderFee($oOrder, $feeId);
				        $correctedFee = true;
				        continue;
			        }
			        $this->removeOrderFee($oOrder, $feeId);
			        $this->orderAddFee($oOrder, $feeData['fee'], $feeData['label']);
			        $correctedFee = true;
		        }
	        }
	        if (!$correctedFee) {
		        if ($hasFee) {
			        $this->orderAddFee($oOrder, $feeData['fee'], $feeData['label']);
		        }
	        }
        }
        if ($hasFee) {
            $feeName = $feeData['label'];
	        $this->setSessionfee( $oOrder, $feeName );
        }
        return $oOrder;
    }

    function setSessionFee($oOrder, $feeName){
	    WC()->session->extra_cart_fee = WC()->session->extra_cart_fee_tax = 0;
	    $aFees = $oOrder->get_fees();
	    foreach($aFees as $fee){
		    if($fee['name'] == $feeName){
			    WC()->session->extra_cart_fee = $fee->get_total();
			    WC()->session->extra_cart_fee_tax = $fee->get_total_tax();
		    }
	    }
    }

	protected function removeOrderFee(&$oOrder, int $feeId) {
		$oOrder->remove_item($feeId);
		wc_delete_order_item($feeId);
		$oOrder->calculate_totals();
	}

	protected function orderAddFee(&$oOrder, $amount, $feeName) {
		$item_fee = new \WC_Order_Item_Fee();
		$item_fee->set_name($feeName);
		$item_fee->set_amount($amount);
		$item_fee->set_total($amount);
		$item_fee->set_tax_status(true);
		$oOrder->add_item($item_fee);
		$oOrder->calculate_totals();
	}

	protected function getFeeData($method) {
		global $woocommerce;
		$woocommerce->cart;
		$woocommerce->cart->calculate_totals();
		$data = [];
		$fee = get_option('woocommerce_' . $method . '_extra_charges');
		$fee = $fee == "" ? 0: $fee;
		$label = get_option( 'woocommerce_' . $method . '_extra_charges_label');
		$type = get_option('woocommerce_' . $method . '_extra_charges_type');
		if (isset($label) && strlen($label) > 2) {
			if ($type == 'percentage'){
				$label .= ' '. $fee.'%';
			}
		} else {
			$label= $this->current_gateway_title . '  Payment Charges ';
		}

		if ($type == "percentage") {
			$cart_total = (float) $woocommerce->cart->get_subtotal('edit');
			$payment_fee = ($cart_total * $fee) / 100;
		} else {
			$payment_fee = $fee;
		}
		$data['fee'] = $payment_fee;
		$data['type'] = ($type == "percentage" ? $fee . '%' : 'Fixed');
		$data['label'] = $label;
		return $data;
	}

    public function has_block_checkout(){
        $uses_blocks = class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType');
	    $isClassicCheckout = isset($_REQUEST["wc-ajax"]) && $_REQUEST["wc-ajax"] === "checkout";
        return ($uses_blocks && !$isClassicCheckout);
    }

	// ////////////////////////////////////////////////

	/**
	 * Process refund.
	 *
	 * If the gateway declares 'refunds' support, this will allow it to refund.
	 * a passed in amount.
	 *
	 * @param  int        $order_id Order ID.
	 * @param  float|null $amount Refund amount.
	 * @param  string     $reason Refund reason.
	 * @return boolean True or false based on success, or a WP_Error object.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		$iMerchantId     = ( get_option( 'cgp_merchant_id' ) ? get_option( 'cgp_merchant_id' ) : 0 );
		$sMerchantApiKey = ( get_option( 'cgp_merchant_api_key' ) ? get_option( 'cgp_merchant_api_key' ) : 0 );
		$bIsTest         = ( get_option( 'cgp_mode' ) == 1 ? true : false );
		$sLanguage       = substr( get_locale(), 0, 2 );

		$sVersion = ( $this->get_woocommerce_version() == '' ? 'unkown' : $this->get_woocommerce_version() );

		$oClient = new cardgate\api\Client( (int) $iMerchantId, $sMerchantApiKey, $bIsTest );

		$oClient->setIp( $_SERVER['REMOTE_ADDR'] );
		$oClient->setLanguage( $sLanguage );
		$oClient->version()->setPlatformName( 'Woocommerce' );
		$oClient->version()->setPlatformVersion( $sVersion );
		$oClient->version()->setPluginName( 'CardGate' );
		$oClient->version()->setPluginVersion( get_option( 'cardgate_version' ) );



		$iSiteId  = (int) get_option( 'cgp_siteid' );
		$amount   = (int) round( $amount * 100 );
		$currency = get_woocommerce_currency();
		$aData = [
			'amount'		=> $amount,
			'currency_id'	=> $currency,
			'description'	=> $reason
		];

		$order   = wc_get_order($order_id);
		$sTransactionId = $order->get_transaction_id();

		$sResource = "refund/{$sTransactionId}/";

		$aData = array_filter( $aData ); // remove NULL values
		$aResult = $oClient->doRequest( $sResource, $aData, 'POST' );
        if ($aResult['success'] == false){
	        return new WP_Error ('cardgate', 'Curopayments code: '.$aResult['code'].', '.$aResult['message']);
        } else {
	        $order->add_order_note('Curo transaction (' . $aResult['refund']['transaction'] . ') Refund amount = ' . round($amount/100,2) . '.');
	        return true;
        }
        return false;
	}

	/**
	 * Save the payment data in the database
	 *
	 * @param integer $iOrderId
	 */
	private function savePaymentData( $iOrderId, $sParent_ID = false ) {
		global $wpdb, $woocommerce;

		$order      = new WC_Order( $iOrderId );
		$payment_id = null;
		$table      = $wpdb->prefix . 'cardgate_payments';
		if ( empty( $sParent_ID ) ) {
			$query = $wpdb->prepare( "
                SELECT
                payment.id As id ,
                payment.order_id ,
                payment.parent_id ,
                payment.currency ,
                payment.amount ,
                payment.gateway_language ,
                payment.payment_method ,
                payment.first_name ,
                payment.last_name ,
                payment.address ,
                payment.postal_code ,
                payment.city ,
                payment.country ,
                payment.email ,
                payment.date_gmt
                FROM
                $table AS payment
                WHERE order_id = %d AND transaction_id = %s", $iOrderId, $sParent_ID );

			$result = $wpdb->get_row( $query, ARRAY_A );
			if ( $result ) {
				$payment_id = $result['id'];
			}
		}

        $order_id = $order->get_id();

		$data = [
			'order_id'         => $order_id,
			'currency'         => get_woocommerce_currency(),
			'amount'           => $order->get_total() * 100,
			'gateway_language' => $this->getLanguage(),
			'payment_method'   => $this->payment_method,
			'bank_option'      => $this->bankOption,
			'first_name'       => $order->get_billing_first_name(),
			'last_name'        => $order->get_billing_last_name(),
			'address'          => $order->get_billing_address_1(),
			'postal_code'      => $order->get_billing_postcode(),
			'city'             => $order->get_billing_city(),
			'country'          => $order->get_billing_country(),
			'email'            => $order->get_billing_email(),
			'status'           => 'pending',
			'date_gmt'         => date( 'Y-m-d H:i:s' )
		];

		$format = [
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s'
		];

		if ( $payment_id == null || ! empty( $sParent_ID ) ) {
			$wpdb->insert( $table, $data, $format );
		} else {
			$wpdb->update( $table, $data, [
				'id' => $payment_id
			], $format, [
				'%d'
			] );
		}
	}

	// ////////////////////////////////////////////////

	/**
	 * Collect the product data from an order
	 *
	 * @param integer $iOrderId
	 */
	private function getCartItems( $iOrderId ) {
		global $woocommerce;

		$sDefaultCountry = get_option( 'woocommerce_default_country' );

		$this->bSeperateSalesTax = ( stripos( $sDefaultCountry, 'US' ) === false ? false : true );

		$nr                = 0;
		$iCartItemTotal    = 0;
		$iCartItemTaxTotal = 0;

		$oOrder      = new WC_Order( $iOrderId );
		$iOrderTotal = round( $oOrder->get_total() * 100 );

		// any discount will be already calculated in the item total
		$aOrder_items = $oOrder->get_items();

		foreach ( $aOrder_items as $oItem ) {

			if ( is_object( $oItem ) ) {
				$oProduct = $oItem->get_product();
				$sName    = $oProduct->get_name();
				$sModel   = $this->formatSku( $oProduct );
				$iQty     = $oItem->get_quantity();
				$iPrice   = round( ( $oItem->get_subtotal() * 100 ) / $iQty );
				$iTax     = round( ( $oItem->get_subtotal_tax() * 100 ) / $iQty );
				$iTotal   = round( $iPrice + $iTax );
				$iTaxrate = $this->get_tax_rate( $oProduct );
			} else {

				$aItem    = $oItem;
				$sName    = $aItem['name'];
				$sModel   = 'product_' . $aItem['item_meta']['_product_id'][0];
				$oProduct = $oOrder->get_product_from_item( $aItem );
				$iQty     = (int) $aItem['item_meta']['_qty'][0];
				$iPrice   = round( ( $oOrder->get_item_total( $aItem, false, false ) * 100 ) );
				$iTax     = round( ( $oOrder->get_item_tax( $aItem, false ) * 100 ) );
				$iTotal   = round( $iPrice + $iTax );
				$iTaxrate = ( $iTax > 0 ? round( $oOrder->get_item_tax( $aItem, false ) / $oOrder->get_item_total( $aItem, false, false ) * 100, 1 ) : 0 );
			}

			$nr ++;
			$items[ $nr ]['type']       = 'product';
			$items[ $nr ]['model']      = $sModel;
			$items[ $nr ]['name']       = $sName;
			$items[ $nr ]['quantity']   = $iQty;
			$items[ $nr ]['price_wt']   = $iPrice;
			$items[ $nr ]['vat']        = $iTaxrate;
			$items[ $nr ]['vat_amount'] = $iTax;

			$iCartItemTotal    += round( $iPrice * $iQty );
			$iCartItemTaxTotal += round( $iTax * $iQty );
		}

		$iShippingTotal    = 0;
		$ishippingTaxTotal = 0;

		$aShipping_methods = $oOrder->get_shipping_methods();

		if ( ! empty( $aShipping_methods ) && is_array( $aShipping_methods ) ) {
			foreach ( $aShipping_methods as $oShipping ) {
				if ( is_object( $oShipping ) ) {

					$sName  = $oShipping->get_name();
					$sModel = $oShipping->get_type();
					$iPrice = round( $oShipping->get_total() * 100 );
					$iTax   = round( $oShipping->get_total_tax() * 100 );
					$iTotal = round( $iPrice + $iTax );
				} else {
					$aShipping = $oShipping;
					$sName     = $aShipping['name'];
					$sModel    = 'shipping_' . $aShipping['item_meta']['method_id'][0];
					$iPrice    = round( $oOrder->get_total_shipping() * 100 );
					$iTax      = round( $oOrder->get_shipping_tax() * 100 );
					$iTotal    = round( $iPrice + $iTax );
				}
				$iTaxrate = $this->get_shipping_tax_rate( $iTotal );

				$nr ++;
				$items[ $nr ]['type']       = 'shipping';
				$items[ $nr ]['model']      = $sModel;
				$items[ $nr ]['name']       = $sName;
				$items[ $nr ]['quantity']   = 1;
				$items[ $nr ]['price_wt']   = $iPrice;
				$items[ $nr ]['vat']        = $iTaxrate;
				$items[ $nr ]['vat_amount'] = $iTax;

				$iShippingTotal    = $iPrice;
				$ishippingTaxTotal = $iTax;
			}
		}

		$fpExtraFee = ( empty( $woocommerce->session->extra_cart_fee ) ? 0 : $woocommerce->session->extra_cart_fee );
		$iExtraFee  = round( $fpExtraFee * 100 );
		$fpExtraFeeTax = ( empty( $woocommerce->session->extra_cart_fee_tax ) ? 0 : $woocommerce->session->extra_cart_fee_tax );
		$iExtraFeeTax  = round( $fpExtraFeeTax * 100 );

        if ($iExtraFeeTax > 0){
            $iTaxRate = round($iExtraFeeTax / $fpExtraFee,2);
	        $nr ++;
	        $items[ $nr ]['type']       = 'paymentfee';
	        $items[ $nr ]['model']      = 'extra_costs';
	        $items[ $nr ]['name']       = 'payment_fee';
	        $items[ $nr ]['quantity']   = 1;
	        $items[ $nr ]['price_wt']   = $iExtraFee;
	        $items[ $nr ]['vat']        = $iTaxRate;
	        $items[ $nr ]['vat_amount'] = $iExtraFeeTax;

        } elseif ( $iExtraFee > 0 ) {

			$nr ++;
			$items[ $nr ]['type']       = 'paymentfee';
			$items[ $nr ]['model']      = 'extra_costs';
			$items[ $nr ]['name']       = 'payment_fee';
			$items[ $nr ]['quantity']   = 1;
			$items[ $nr ]['price_wt']   = $iExtraFee;
			$items[ $nr ]['vat']        = 0;
			$items[ $nr ]['vat_amount'] = 0;
		}

        $iDiscountTotal = 0;
        $iDiscountTaxTotal = 0;

        $aOrderData = $oOrder->get_data();
        if ( $aOrderData['discount_total'] > 0 ) {
            $iDiscountTaxTotal = round($aOrderData['discount_tax'] * -100);
            $iDiscountTotal = round($aOrderData['discount_total'] * -100);
            $iDiscountVat = round($aOrderData['discount_tax'] / $aOrderData['discount_total'] * 100);

            $nr ++;
            $items[ $nr ]['type']       = 'discount';
            $items[ $nr ]['model']      = 'discount_total';
            $items[ $nr ]['name']       = 'Discount';
            $items[ $nr ]['quantity']   = 1;
            $items[ $nr ]['price_wt']   = $iDiscountTotal;
            $items[ $nr ]['vat']        = $iDiscountVat;
            $items[ $nr ]['vat_amount'] = $iDiscountTaxTotal;
        }

		$iTaxDifference = round( $oOrder->get_total_tax() * 100 ) - $iCartItemTaxTotal - $ishippingTaxTotal - $iExtraFeeTax - $iDiscountTaxTotal;
		if ( $iTaxDifference != 0 ) {
			$nr ++;
			$items[ $nr ]['type']       = 'vatcorrection';
			$items[ $nr ]['model']      = 'Correction';
			$items[ $nr ]['name']       = 'vat_correction';
			$items[ $nr ]['quantity']   = 1;
			$items[ $nr ]['price_wt']   = $iTaxDifference;
			$items[ $nr ]['vat']        = 0;
			$items[ $nr ]['vat_amount'] = 0;
		}

		$iCorrection = round( $iOrderTotal - $iCartItemTotal - $iCartItemTaxTotal - $iShippingTotal - $ishippingTaxTotal - $iExtraFee - $iExtraFeeTax - $iTaxDifference - $iDiscountTotal - $iDiscountTaxTotal);

		if ( $iCorrection != 0 ) {

			$nr ++;
			$items[ $nr ]['type']       = 'correction';
			$items[ $nr ]['model']      = 'Correction';
			$items[ $nr ]['name']       = 'item_correction';
			$items[ $nr ]['quantity']   = 1;
			$items[ $nr ]['price_wt']   = $iCorrection;
			$items[ $nr ]['vat']        = 0;
			$items[ $nr ]['vat_amount'] = 0;
		}

		return $items;
	}

	// ////////////////////////////////////////////////

	/**
	 * Validate Frontend Fields
	 *
	 * Validate payment fields on the frontend.
	 *
	 * @since 1.0.0
	 */
	public function validate_fields() {
		return true;
	}


	public function get_tax_rate( $oProduct ) {
		$sDefaultCountry = get_option( 'woocommerce_default_country' );
		if ( stripos( $sDefaultCountry, 'US' ) === false ) {
			$oTax       = new WC_Tax();
			$aTempRates = $oTax->get_rates( $oProduct->get_tax_class() );
			$aVat       = array_shift( $aTempRates );
			if ( isset( $aVat['rate'] ) ) {
				$dItemTaxRate = round( $aVat['rate'], 2 );
			} else {
				$dItemTaxRate = 0;
			}
		} else {
			$dItemTaxRate = 0;
		}

		return $dItemTaxRate;
	}

	public function get_shipping_tax_rate( $iTotal ) {

		if ( $iTotal > 0 && ! $this->bSeperateSalesTax ) {
			$oTax           = new WC_Tax();
			$aShippingRates = $oTax->get_shipping_tax_rates();
			$aVat           = array_shift( $aShippingRates );
			if ( isset( $aVat['rate'] ) ) {
				$dShippingTaxRate = round( $aVat['rate'], 2 );
			} else {
				$dShippingTaxRate = 0;
			}
		} else {
			$dShippingTaxRate = 0;
		}

		return $dShippingTaxRate;
	}

	// ////////////////////////////////////////////////

	/**
	 * retrieve the Woocommerce version used
	 */
	public function get_woocommerce_version() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_folder = get_plugins( '/woocommerce' );
		$plugin_file   = 'woocommerce.php';

		if ( array_key_exists( $plugin_file, $plugin_folder ) ) {
			return $plugin_folder[ $plugin_file ]['Version'];
		} else {
			return 'unknown';
		}
	}

	// ////////////////////////////////////////////////
	private function swap_order_number( $order_id ) {
		global $wpdb;

		// swap order_id with sequetial order_id if it exists
		$tableName = $wpdb->prefix . 'postmeta';
		$qry       = $wpdb->prepare( "SELECT post_id, meta_value FROM $tableName WHERE  meta_key='%s' AND post_id=%s", '_order_number', $order_id );

		$seq_order_ids = $wpdb->get_results( $qry, ARRAY_A );
		if ( count( $seq_order_ids ) > 0 ) {
			foreach ( $seq_order_ids as $k => $v ) {
				return $v['meta_value'];
			}
		}

		return $order_id;
	}

	function getLanguage() {
		return substr( get_locale(), 0, 2 );
	}

	private function formatSku( $oProduct ) {
		if ( is_object( $oProduct ) && method_exists( $oProduct, 'get_sku' ) ) {
			$sSku = $oProduct->get_sku();

			if ( $sSku == null || $sSku == '' ) {
				return 'SKU_' . $oProduct->get_id();
			}

			return $sSku;
		}

		return 'SKU_UNDETERMINED';
	}

	public function modify_icon( $icon, $id ) {
		if ( ! $id || $id != $this->id ) {
			return $icon;
		}

		$payment_gateways = WC()->payment_gateways()->payment_gateways();
		if ( ! isset( $payment_gateways[ $id ] ) ) {
			return $icon;
		}

		$payment_gateway = $payment_gateways[ $id ];
		if ( isset( $payment_gateway->company ) && $payment_gateway->company == 'CardGate' ) {
			$icon    = 'https://cdn.curopayments.net/images/paymentmethods/' . $this->payment_method . '.svg';
			$img     = '<img style="max-width:40px; max-height:40px;float:right;" src="' . WC_HTTPS::force_https_url( esc_url( $icon ) ) . '" alt="' . esc_attr( $payment_gateway->get_title() ) . '" />';
			$display = get_option( 'cgp_checkoutdisplay', 'withoutlogo' );
			switch ( $display ) {
				case 'withoutlogo':
					return '';
					break;
				case 'withlogo':
					return $img;
					break;
			}
		} else {
			return $icon;
		}
	}
}

