<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Exit if accessed directly

/**
 * Title: WooCommerce CGP Common Gateway
 * Description: Gateway class
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Cardgate
 *
 * @package CardGate
 */

/**
 * CGP_Common_Gateway Class.
 */
class CGP_Common_Gateway extends WC_Payment_Gateway {

	/**
	 * Bank option.
	 *
	 * @var string
	 */
	public $bankOption;
	/**
	 * Logo.
	 *
	 * @var string
	 */
	public $logo;
	/**
	 * Separate sales tax.
	 *
	 * @var bool
	 */
	public $bSeperateSalesTax;
	/**
	 * Instructions.
	 *
	 * @var string
	 */
	public $instructions;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_form_fields();
		$this->init_settings();
		$this->title        = ( isset( $this->settings['title'] ) && ! empty( $this->settings['title'] ) ? $this->settings['title'] : $this->payment_name );
		$this->description  = $this->settings['description'];
		$this->instructions = ( ! empty( $this->settings['instructions'] ) ? $this->settings['instructions'] : '' );

		add_filter( 'woocommerce_gateway_icon', array( $this, 'modify_icon' ), 20, 2 );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receiptPage' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}

	/**
	 *  Check if the currency is allowed for this payment method.
	 *
	 * @param string $currency Currency.
	 * @param string $payment_method Payment method.
	 *
	 * @return bool
	 */
	public function check_payment_currency( $currency, $payment_method ) {
		$strictly_euro = in_array(
			$payment_method,
			array(
				'cardgateideal',
				'cardgateidealqr',
				'cardgatebancontact',
				'cardgatebanktransfer',
				'cardgatebillink',
				'cardgatesofortbanking',
				'cardgatedirectdebit',
				'cardgateonlineueberweisen',
				'cardgatespraypay',
			),
			true
		);
		if ( $strictly_euro && 'EUR' !== $currency ) {
			return false;
		}

		$strictly_pln = in_array( $payment_method, array( 'cardgateprzelewy24' ), true );
		if ( $strictly_pln && 'PLN' !== $currency ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {
		$is_available  = ( 'yes' === $this->enabled );
		$site_currency = get_woocommerce_currency();
		if ( WC()->cart && ! $this->check_payment_currency( $site_currency, $this->id ) ) {
			$is_available = false;
		}
		return $is_available;
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
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'      => array(
				'title'   => __( 'Enable/Disable', 'cardgate' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable ' . $this->payment_name, 'cardgate' ),
				'default' => 'no',
			),
			'title'        => array(
				'title'       => __( 'Title', 'cardgate' ),
				'type'        => 'text',
				'description' => __( 'Payment method title that the customer will see on your checkout.', 'cardgate' ),
				'default'     => $this->payment_name,
				'desc_tip'    => true,
			),
			'description'  => array(
				'title'       => __( 'Description', 'cardgate' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your website.', 'cardgate' ),
				'default'     => __( 'Pay with ', 'cardgate' ) . $this->payment_name,
				'desc_tip'    => true,
			),
			'instructions' => array(
				'title'       => __( 'Instructions', 'cardgate' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page.', 'cardgate' ),
				'default'     => '',
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Admin Panel Options.
	 */
	public function admin_options() {
		?>
		<h3>
			<?php echo esc_html( $this->admin_title ); ?>
		</h3>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table>
		<!--/.form-table-->
		<?php
	}

	// ////////////////////////////////////////////////

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		try {
			$order            = new WC_Order( $order_id );
			$merchant_id      = ( get_option( 'cgp_merchant_id' ) ? get_option( 'cgp_merchant_id' ) : 0 );
			$merchant_api_key = ( get_option( 'cgp_merchant_api_key' ) ? get_option( 'cgp_merchant_api_key' ) : 0 );
			$is_test          = ( 1 === (int) get_option( 'cgp_mode' ) ? true : false );
			$language         = substr( get_locale(), 0, 2 );
			$version          = ( '' === $this->get_woocommerce_version() ? 'unkown' : $this->get_woocommerce_version() );

			$cardgate = new cardgate\api\Client( (int) $merchant_id, $merchant_api_key, $is_test );

			$cardgate->setIp( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) );
			$cardgate->setLanguage( $language );
			$cardgate->version()->setPlatformName( 'Woocommerce' );
			$cardgate->version()->setPlatformVersion( $version );
			$cardgate->version()->setPluginName( 'CardGate' );
			$cardgate->version()->setPluginVersion( get_option( 'cardgate_version' ) );

			$site_id  = (int) get_option( 'cgp_siteid' );
			$amount   = (int) round( $order->get_total() * 100 );
			$currency = get_woocommerce_currency();

			$transaction = $cardgate->transactions()->create( $site_id, $amount, $currency );

			// Configure payment option.
			$transaction->setPaymentMethod( $this->payment_method );

			$billing_email      = $order->get_billing_email();
			$billing_phone      = $order->get_billing_phone();
			$billing_first_name = $order->get_billing_first_name();
			$billing_last_name   = $order->get_billing_last_name();
			$billing_address_1  = $order->get_billing_address_1();
			$billing_address_2  = $order->get_billing_address_2();
			$billing_postcode   = $order->get_billing_postcode();
			$billing_state      = $order->get_billing_state();
			$billing_city       = $order->get_billing_city();
			$billing_country    = $order->get_billing_country();

			// Configure customer.
			$consumer = $transaction->getConsumer();
			if ( '' !== $billing_email ) {
				$consumer->setEmail( $billing_email );
			}
			if ( '' !== $billing_phone ) {
				$consumer->setPhone( $billing_phone );
			}
			if ( '' !== $billing_first_name ) {
				$consumer->address()->setFirstName( $billing_first_name );
			}
			if ( '' !== $billing_last_name ) {
				$consumer->address()->setLastName( $billing_last_name );
			}
			if ( '' !== $billing_address_1 || '' !== $billing_address_2 ) {
				$consumer->address()->setAddress( trim( $billing_address_1 . ' ' . $billing_address_2 ) );
			}
			if ( '' !== $billing_postcode ) {
				$consumer->address()->setZipCode( $billing_postcode );
			}
			if ( '' !== $billing_city ) {
				$consumer->address()->setCity( $billing_city );
			}
			if ( '' !== $billing_state ) {
				$consumer->address()->setState( $billing_state );
			}
			if ( '' !== $billing_country ) {
				$consumer->address()->setCountry( $billing_country );
			}

			$shipping_first_name = $order->get_shipping_first_name();
			$shipping_last_name  = $order->get_shipping_last_name();
			$shipping_address_1  = $order->get_shipping_address_1();
			$shipping_address_2  = $order->get_shipping_address_2();
			$shipping_postcode   = $order->get_shipping_postcode();
			$shipping_state      = $order->get_shipping_state();
			$shipping_city       = $order->get_shipping_city();
			$shipping_country    = $order->get_shipping_country();

			if ( '' !== $shipping_first_name ) {
				$consumer->shippingAddress()->setFirstName( $shipping_first_name );
			}
			if ( '' !== $shipping_last_name ) {
				$consumer->shippingAddress()->setLastName( $shipping_last_name );
			}
			if ( '' !== $shipping_address_1 || '' !== $shipping_address_2 ) {
				$consumer->shippingAddress()->setAddress( trim( $shipping_address_1 . ' ' . $shipping_address_2 ) );
			}
			if ( '' !== $shipping_postcode ) {
				$consumer->shippingAddress()->setZipCode( $shipping_postcode );
			}
			if ( '' !== $shipping_city ) {
				$consumer->shippingAddress()->setCity( $shipping_city );
			}
			if ( '' !== $shipping_state ) {
				$consumer->shippingAddress()->setState( $shipping_state );
			}
			if ( '' !== $shipping_country ) {
				$consumer->shippingAddress()->setCountry( $shipping_country );
			}

			$cart       = $transaction->getCart();
			$cart_items = $this->getCartItems( $order_id );

			foreach ( $cart_items as $item ) {

				switch ( $item['type'] ) {
					case 'product':
						$item_type = \cardgate\api\Item::TYPE_PRODUCT;
						break;
					case 'shipping':
						$item_type = \cardgate\api\Item::TYPE_SHIPPING;
						break;
					case 'paymentfee':
						$item_type = \cardgate\api\Item::TYPE_HANDLING;
						break;
					case 'discount':
						$item_type = \cardgate\api\Item::TYPE_DISCOUNT;
						break;
					case 'correction':
						$item_type = \cardgate\api\Item::TYPE_CORRECTION;
						break;
					case 'vatcorrection':
						$item_type = \cardgate\api\Item::TYPE_VAT_CORRECTION;
						break;
					default:
						$item_type = \cardgate\api\Item::TYPE_PRODUCT;
						break;
				}

				$cart_item = $cart->addItem( $item_type, $item['model'], $item['name'], (int) $item['quantity'], (int) $item['price_wt'] );
				$cart_item->setVat( $item['vat'] );
				$cart_item->setVatAmount( $item['vat_amount'] );
				$cart_item->setVatIncluded( 0 );
			}

			$cancel_url = $order->get_cancel_order_url();

			$transaction->setCallbackUrl( site_url() . '/index.php?cgp_notify=true' );
			$transaction->setSuccessUrl( $this->get_return_url( $order ) );
			$transaction->setFailureUrl( $cancel_url );
			$transaction->setReference( 'O' . time() . $order_id );
			$transaction->setDescription( 'Order ' . $this->swap_order_number( $order_id ) );

			$transaction->register();

			$action_url = $transaction->getActionUrl();

			if ( null !== $action_url ) {
				return array(
					'result'   => 'success',
					'redirect' => trim( $action_url ),
				);
			} else {
				$error_message = 'CardGate error: ' . 'no redirect URL';
				wc_add_notice( $error_message, 'error' );

				return array(
					'result'   => 'success',
					'redirect' => wc_get_checkout_url(),
				);
			}
		} catch ( cardgate\api\Exception $e ) {
			$error_message = 'CardGate error: ' . esc_html( $e->getMessage() );
			wc_add_notice( $error_message, 'error' );

			return array(
				'result'   => 'success',
				'redirect' => wc_get_checkout_url(),
			);
		}
	}
	/**
	 * Set session fee.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $fee_name Fee name.
	 */
	public function set_session_fee( $order, $fee_name ) {
		WC()->session->extra_cart_fee     = 0;
		WC()->session->extra_cart_fee_tax = 0;
		$fees                             = $order->get_fees();
		foreach ( $fees as $fee ) {
			if ( $fee['name'] === $fee_name ) {
				WC()->session->extra_cart_fee     = $fee->get_total();
				WC()->session->extra_cart_fee_tax = $fee->get_total_tax();
			}
		}
	}

	/**
	 * Remove order fee.
	 *
	 * @param WC_Order $order Order.
	 * @param int      $fee_id Fee ID.
	 */
	protected function remove_order_fee( &$order, int $fee_id ) {
		$order->remove_item( $fee_id );
		wc_delete_order_item( $fee_id );
		$order->calculate_totals();
	}

	/**
	 * Add fee to order.
	 *
	 * @param WC_Order $order Order.
	 * @param float    $amount Amount.
	 * @param string   $fee_name Fee name.
	 */
	protected function order_add_fee( &$order, $amount, $fee_name ) {
		$item_fee = new \WC_Order_Item_Fee();
		$item_fee->set_name( $fee_name );
		$item_fee->set_amount( $amount );
		$item_fee->set_total( $amount );
		$order->add_item( $item_fee );
	}

	/**
	 * Get fee data.
	 *
	 * @param string $method Method.
	 * @return array
	 */
	protected function get_fee_data( $method ) {
		global $woocommerce;
		$woocommerce->cart->calculate_totals();
		$data  = array();
		$fee   = get_option( 'woocommerce_' . $method . '_extra_charges' );
		$fee   = '' === $fee ? 0 : $fee;
		$label = get_option( 'woocommerce_' . $method . '_extra_charges_label' );
		$type  = get_option( 'woocommerce_' . $method . '_extra_charges_type' );
		if ( isset( $label ) && strlen( $label ) > 2 ) {
			if ( 'percentage' === $type ) {
				$label .= ' ' . $fee . '%';
			}
		} else {
			$label = $this->current_gateway_title . '  Payment Charges ';
		}

		if ( 'percentage' === $type ) {
			$cart_total  = (float) $woocommerce->cart->get_subtotal( 'edit' );
			$payment_fee = ( $cart_total * $fee ) / 100;
		} else {
			$payment_fee = $fee;
		}
		$data['fee']        = $payment_fee;
		$data['type']       = ( 'percentage' === $type ? $fee . '%' : 'Fixed' );
		$data['label']      = $label;
		$data['tax_status'] = 'taxable';
		return $data;
	}

	/**
	 * Has block checkout.
	 *
	 * @return bool
	 */
	public function has_block_checkout() {
		$uses_blocks        = class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' );
		$is_classic_checkout = isset( $_REQUEST['wc-ajax'] ) && 'checkout' === $_REQUEST['wc-ajax'];
		return ( $uses_blocks && ! $is_classic_checkout );
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

		$merchant_id      = ( get_option( 'cgp_merchant_id' ) ? get_option( 'cgp_merchant_id' ) : 0 );
		$merchant_api_key = ( get_option( 'cgp_merchant_api_key' ) ? get_option( 'cgp_merchant_api_key' ) : 0 );
		$is_test          = ( 1 === (int) get_option( 'cgp_mode' ) ? true : false );
		$language         = substr( get_locale(), 0, 2 );

		$version = ( '' === $this->get_woocommerce_version() ? 'unkown' : $this->get_woocommerce_version() );

		$client = new cardgate\api\Client( (int) $merchant_id, $merchant_api_key, $is_test );

		$client->setIp( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) );
		$client->setLanguage( $language );
		$client->version()->setPlatformName( 'Woocommerce' );
		$client->version()->setPlatformVersion( $version );
		$client->version()->setPluginName( 'CardGate' );
		$client->version()->setPluginVersion( get_option( 'cardgate_version' ) );

		$currency = get_woocommerce_currency();
		$data     = array(
			'amount'      => (int) round( $amount * 100 ),
			'currency_id' => $currency,
			'description' => $reason,
		);

		$order          = wc_get_order( $order_id );
		$transaction_id = $order->get_transaction_id();

		$resource = "refund/{$transaction_id}/";

		$data   = array_filter( $data ); // remove NULL values.
		$result = $client->doRequest( $resource, $data, 'POST' );
		if ( false === $result['success'] ) {
			return new WP_Error( 'cardgate', 'Curopayments code: ' . $result['code'] . ', ' . $result['message'] );
		} else {
			$order->add_order_note( 'Curo transaction (' . $result['refund']['transaction'] . ') Refund amount = ' . round( (int) round( $amount * 100 ) / 100, 2 ) . '.' );
			return true;
		}
	}

	/**
	 * Save the payment data in the database.
	 *
	 * @param int         $order_id Order ID.
	 * @param string|bool $parent_id Parent ID.
	 */
	private function save_payment_data( $order_id, $parent_id = false ) {
		global $wpdb;

		$order      = new WC_Order( $order_id );
		$payment_id = null;
		$table      = $wpdb->prefix . 'cardgate_payments';
		if ( empty( $parent_id ) ) {
			$query = $wpdb->prepare(
				"SELECT id FROM $table WHERE order_id = %d AND transaction_id = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$order_id,
				$parent_id
			);

			$payment_id = $wpdb->get_var( $query );
		}

		$data = array(
			'order_id'         => $order->get_id(),
			'currency'         => get_woocommerce_currency(),
			'amount'           => $order->get_total() * 100,
			'gateway_language' => $this->get_language(),
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
			'date_gmt'         => current_time( 'mysql' ),
		);

		if ( null === $payment_id || ! empty( $parent_id ) ) {
			$wpdb->insert( $table, $data );
		} else {
			$wpdb->update(
				$table,
				$data,
				array( 'id' => $payment_id )
			);
		}
	}

	// ////////////////////////////////////////////////

	/**
	 * Collect the product data from an order.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	private function getCartItems( $order_id ) {
		global $woocommerce;

		$default_country = get_option( 'woocommerce_default_country' );

		$this->bSeperateSalesTax = ( stripos( $default_country, 'US' ) === false ? false : true );

		$nr                 = 0;
		$cart_item_total    = 0;
		$cart_item_tax_total = 0;

		$order       = new WC_Order( $order_id );
		$order_total = (int) round( $order->get_total() * 100 );

		// any discount will be already calculated in the item total.
		$order_items = $order->get_items();

		$items = array();

		foreach ( $order_items as $item ) {

			$product = $item->get_product();
			$name    = $product->get_name();
			$model   = $this->formatSku( $product );
			$qty     = $item->get_quantity();
			$price   = (int) round( ( $item->get_subtotal() * 100 ) / $qty );
			$tax     = (int) round( ( $item->get_subtotal_tax() * 100 ) / $qty );
			$taxrate = $this->get_tax_rate( $product );

			++$nr;
			$items[ $nr ]['type']       = 'product';
			$items[ $nr ]['model']      = $model;
			$items[ $nr ]['name']       = $name;
			$items[ $nr ]['quantity']   = $qty;
			$items[ $nr ]['price_wt']   = $price;
			$items[ $nr ]['vat']        = $taxrate;
			$items[ $nr ]['vat_amount'] = $tax;

			$cart_item_total    += (int) round( $price * $qty );
			$cart_item_tax_total += (int) round( $tax * $qty );
		}

		$shipping_total     = 0;
		$shipping_tax_total = 0;

		$shipping_methods = $order->get_shipping_methods();

		if ( ! empty( $shipping_methods ) && is_array( $shipping_methods ) ) {
			foreach ( $shipping_methods as $shipping ) {
				$name    = $shipping->get_name();
				$model   = $shipping->get_type();
				$price   = (int) round( $shipping->get_total() * 100 );
				$tax     = (int) round( $shipping->get_total_tax() * 100 );
				$taxrate = $this->get_shipping_tax_rate( $price + $tax );

				++$nr;
				$items[ $nr ]['type']       = 'shipping';
				$items[ $nr ]['model']      = $model;
				$items[ $nr ]['name']       = $name;
				$items[ $nr ]['quantity']   = 1;
				$items[ $nr ]['price_wt']   = $price;
				$items[ $nr ]['vat']        = $taxrate;
				$items[ $nr ]['vat_amount'] = $tax;

				$shipping_total     = $price;
				$shipping_tax_total = $tax;
			}
		}

		$extra_fee       = ( empty( $woocommerce->session->extra_cart_fee ) ? 0 : $woocommerce->session->extra_cart_fee );
		$i_extra_fee     = (int) round( $extra_fee * 100 );
		$extra_fee_tax   = ( empty( $woocommerce->session->extra_cart_fee_tax ) ? 0 : $woocommerce->session->extra_cart_fee_tax );
		$i_extra_fee_tax = (int) round( $extra_fee_tax * 100 );

		if ( $i_extra_fee_tax > 0 ) {
			$tax_rate = round( $i_extra_fee_tax / $extra_fee, 2 );
			++$nr;
			$items[ $nr ]['type']       = 'paymentfee';
			$items[ $nr ]['model']      = 'extra_costs';
			$items[ $nr ]['name']       = 'payment_fee';
			$items[ $nr ]['quantity']   = 1;
			$items[ $nr ]['price_wt']   = $i_extra_fee;
			$items[ $nr ]['vat']        = $tax_rate;
			$items[ $nr ]['vat_amount'] = $i_extra_fee_tax;

		} elseif ( $i_extra_fee > 0 ) {

			++$nr;
			$items[ $nr ]['type']       = 'paymentfee';
			$items[ $nr ]['model']      = 'extra_costs';
			$items[ $nr ]['name']       = 'payment_fee';
			$items[ $nr ]['quantity']   = 1;
			$items[ $nr ]['price_wt']   = $i_extra_fee;
			$items[ $nr ]['vat']        = 0;
			$items[ $nr ]['vat_amount'] = 0;
		}

		$discount_total     = 0;
		$discount_tax_total = 0;

		$order_data = $order->get_data();
		if ( $order_data['discount_total'] > 0 ) {
			$discount_tax_total = (int) round( $order_data['discount_tax'] * -100 );
			$discount_total     = (int) round( $order_data['discount_total'] * -100 );
			$discount_vat       = round( $order_data['discount_tax'] / $order_data['discount_total'] * 100 );

			++$nr;
			$items[ $nr ]['type']       = 'discount';
			$items[ $nr ]['model']      = 'discount_total';
			$items[ $nr ]['name']       = 'Discount';
			$items[ $nr ]['quantity']   = 1;
			$items[ $nr ]['price_wt']   = $discount_total;
			$items[ $nr ]['vat']        = $discount_vat;
			$items[ $nr ]['vat_amount'] = $discount_tax_total;
		}

		$tax_difference = (int) round( $order->get_total_tax() * 100 ) - $cart_item_tax_total - $shipping_tax_total - $i_extra_fee_tax - $discount_tax_total;
		if ( 0 !== $tax_difference ) {
			++$nr;
			$items[ $nr ]['type']       = 'vatcorrection';
			$items[ $nr ]['model']      = 'Correction';
			$items[ $nr ]['name']       = 'vat_correction';
			$items[ $nr ]['quantity']   = 1;
			$items[ $nr ]['price_wt']   = $tax_difference;
			$items[ $nr ]['vat']        = 0;
			$items[ $nr ]['vat_amount'] = 0;
		}

		$correction = (int) round( $order_total - $cart_item_total - $cart_item_tax_total - $shipping_total - $shipping_tax_total - $i_extra_fee - $i_extra_fee_tax - $tax_difference - $discount_total - $discount_tax_total );

		if ( 0 !== $correction ) {

			++$nr;
			$items[ $nr ]['type']       = 'correction';
			$items[ $nr ]['model']      = 'Correction';
			$items[ $nr ]['name']       = 'item_correction';
			$items[ $nr ]['quantity']   = 1;
			$items[ $nr ]['price_wt']   = $correction;
			$items[ $nr ]['vat']        = 0;
			$items[ $nr ]['vat_amount'] = 0;
		}

		return $items;
	}

	// ////////////////////////////////////////////////

	/**
	 * Validate fields.
	 *
	 * @return bool
	 */
	public function validate_fields() {
		return true;
	}

	/**
	 * Get tax rate.
	 *
	 * @param WC_Product $product Product.
	 * @return float
	 */
	public function get_tax_rate( $product ) {
		$default_country = get_option( 'woocommerce_default_country' );
		if ( stripos( $default_country, 'US' ) === false ) {
			$tax       = new WC_Tax();
			$temp_rates = $tax->get_rates( $product->get_tax_class() );
			$vat       = array_shift( $temp_rates );
			if ( isset( $vat['rate'] ) ) {
				$item_tax_rate = round( $vat['rate'], 2 );
			} else {
				$item_tax_rate = 0;
			}
		} else {
			$item_tax_rate = 0;
		}

		return (float) $item_tax_rate;
	}

	/**
	 * Get shipping tax rate.
	 *
	 * @param float $total Total.
	 * @return float
	 */
	public function get_shipping_tax_rate( $total ) {

		if ( $total > 0 && ! $this->bSeperateSalesTax ) {
			$tax            = new WC_Tax();
			$shipping_rates = $tax->get_shipping_tax_rates();
			$vat            = array_shift( $shipping_rates );
			if ( isset( $vat['rate'] ) ) {
				$shipping_tax_rate = round( $vat['rate'], 2 );
			} else {
				$shipping_tax_rate = 0;
			}
		} else {
			$shipping_tax_rate = 0;
		}

		return (float) $shipping_tax_rate;
	}

	/**
	 * Get WooCommerce version.
	 *
	 * @return string
	 */
	public function get_woocommerce_version() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_folder = get_plugins( '/woocommerce' );
		$plugin_file   = 'woocommerce.php';

		if ( array_key_exists( $plugin_file, $plugin_folder ) ) {
			return $plugin_folder[ $plugin_file ]['Version'];
		} else {
			return 'unknown';
		}
	}

	/**
	 * Swap order number.
	 *
	 * @param int $order_id Order ID.
	 * @return string
	 */
	private function swap_order_number( $order_id ) {
		global $wpdb;

		// swap order_id with sequetial order_id if it exists.
		$table_name = $wpdb->prefix . 'postmeta';
		$qry       = $wpdb->prepare( "SELECT post_id, meta_value FROM $table_name WHERE  meta_key='%s' AND post_id=%s", '_order_number', $order_id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$seq_order_ids = $wpdb->get_results( $qry, ARRAY_A );
		if ( count( $seq_order_ids ) > 0 ) {
			foreach ( $seq_order_ids as $k => $v ) {
				return $v['meta_value'];
			}
		}

		return $order_id;
	}

	/**
	 * Get language.
	 *
	 * @return string
	 */
	public function get_language() {
		return substr( get_locale(), 0, 2 );
	}

	/**
	 * Format SKU.
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	private function formatSku( $product ) {
		if ( is_object( $product ) && method_exists( $product, 'get_sku' ) ) {
			$sku = $product->get_sku();

			if ( null === $sku || '' === $sku ) {
				return 'SKU_' . $product->get_id();
			}

			return $sku;
		}

		return 'SKU_UNDETERMINED';
	}

	/**
	 * Modify icon.
	 *
	 * @param string $icon Icon.
	 * @param string $id ID.
	 * @return string
	 */
	public function modify_icon( $icon, $id ) {
		if ( ! $id || $id !== $this->id ) {
			return $icon;
		}

		$payment_gateways = WC()->payment_gateways()->payment_gateways();
		if ( ! isset( $payment_gateways[ $id ] ) ) {
			return $icon;
		}

		$payment_gateway = $payment_gateways[ $id ];
		if ( isset( $payment_gateway->company ) && 'CardGate' === $payment_gateway->company ) {
			$icon    = 'https://cdn.curopayments.net/images/paymentmethods/' . $this->payment_method . '.svg';
			$img     = '<img style="max-width:40px; max-height:40px;float:right;" src="' . WC_HTTPS::force_https_url( esc_url( $icon ) ) . '" alt="' . esc_attr( $payment_gateway->get_title() ) . '" />';
			$display = get_option( 'cgp_checkoutdisplay', 'withoutlogo' );
			switch ( $display ) {
				case 'withlogo':
					$icon = $payment_gateway->get_title() . $img;
					break;
				case 'withoutlogo':
				default:
					$icon = $payment_gateway->get_title();
					break;
			}
		}
		return $icon;
	}
}