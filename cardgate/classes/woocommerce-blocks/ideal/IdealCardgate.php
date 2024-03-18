<?php
namespace Automattic\WooCommerce\Blocks\Payments\Integrations;

/**
 * iDEAL payment method integration
 */
final class IdealCardgate extends AbstractPaymentMethodType {

	/**
	 * @var string
	 */
	protected $iconpath = 'https://cdn.curopayments.net/images/paymentmethods/';
	/**
	 * Payment method name defined by payment methods extending this class.
	 *
	 * @var string
	 */
	protected $name = 'cardgateideal';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = $this->get_settings();
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'];
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$url = plugin_dir_url(__FILE__);
		wp_register_script(
			'wc_payment_method_cardgateideal',
			$url . 'build/index.js',
			[],
			'1020349.' . wp_rand( 0, 9999 ) ,
			true
		);
		return [ 'wc_payment_method_cardgateideal' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return array(
			'title'                             => isset( $this->settings['title'] ) ? $this->settings['title'] : '',
			'description'                       => isset( $this->settings['description'] ) ? $this->settings['description'] : '',
			'instructions'                      => isset( $this->settings['instructions'] ) ? $this->settings['instructions'] : '',
			'icon'                              => $this->iconpath.'ideal.svg',
			'show_icon'                         => $this->settings['show_icon'],
			'supports'                          => ['products'],
			'issuers'                           => $this->settings['issuers'],
			'feeUrl'                            => $this->settings['feeUrl'],
		);
	}
	private function get_settings(){

		$settings = get_option( 'woocommerce_cardgateideal_settings', [] );
		$use_icon = get_option('cgp_checkoutdisplay');
		$settings['show_icon'] = ($use_icon == 'withlogo');
		$settings['issuers'] = get_option( 'sIssuers' );

		$aIssuers = [];
		$aIssuers[] = [ 'value' => '', 'name' => 'Kies uw bank'];
		$availableGateways = WC()->payment_gateways()->get_available_payment_gateways();
		foreach ($availableGateways as $key => $gateway) {
			if ( $key == 'cardgateideal' ) {
				$issuers = $gateway->getBankoptions();
				foreach ( $issuers as $key => $value ) {
					$aIssuers[] = [ 'value' => $key, 'name' => $value ];
				}
			}
		}
		$settings['issuers'] = json_encode(($aIssuers));
		$settings['feeUrl'] =  admin_url('admin-ajax.php');
		return $settings;
	}
}
