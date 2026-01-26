<?php

/**
 * Plugin Name: CardGate
 * Plugin URI: http://cardgate.com
 * Description: Integrates Cardgate Gateway for WooCommerce into WordPress
 * Author: CardGate
 * Author URI: https://www.cardgate.com
 * Version: 4.1.1
 * Text Domain: cardgate
 * Domain Path: /i18n/languages
 * Requires at least: 4.4
 * WC requires at least: 3.0.0
 * WC tested up to: 10.3.5
 * License: GPLv3 or later
 * Requires Plugins: woocommerce
 *
 * @package CardGate
 */

require_once WP_PLUGIN_DIR . '/cardgate/cardgate-clientlib-php/init.php';

/**
 * CardGate Class.
 */
class Cardgate {
	/**
	 * Current gateway title.
	 *
	 * @var string
	 */
	protected $current_gateway_title = '';
	/**
	 * Current gateway extra charges.
	 *
	 * @var string
	 */
	protected $current_gateway_extra_charges = '';
	/**
	 * Current gateway extra charges type value.
	 *
	 * @var string
	 */
	protected $current_gateway_extra_charges_type_value = '';
	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	protected $plugin_url;
	/**
	 * Payment names.
	 *
	 * @var array
	 */
	protected $payment_names = array(
		'Afterpay',
		'Bancontact',
		'Banktransfer',
		'Billink',
		'Bitcoin',
		'Crypto',
		'Creditcard',
		'DirectDebit',
		'Giftcard',
		'Ideal',
		'Idealqr',
		'Klarna',
		'Onlineueberweisen',
		'PayPal',
		'Paysafecard',
		'Przelewy24',
		'Sofortbanking',
		'Spraypay',
	);
	/**
	 * Initialize plug-in.
	 */
	public function __construct() {
		// Set up localisation.
		$this->load_plugin_textdomain();
		$this->set_plugin_url();

		add_action( 'plugins_loaded', array( &$this, 'includes' ), 0 );
		add_action( 'plugins_loaded', array( &$this, 'initiate_payment_classes' ) );
		add_action(
			'before_woocommerce_init',
			function () {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
				}
			}
		);

		add_action( 'admin_head', array( $this, 'add_cgform_fields' ) );
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'calculate_fees' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_cg_script' ), 10, 1 );
		add_action( 'admin_menu', array( &$this, 'cgp_admin_menu' ) );
		add_action( 'init', array( &$this, 'cardgate_callback' ), 20 );
		add_action( 'woocommerce_blocks_loaded', array( $this, 'woocommerce_cardgate_blocks_support' ) );
		// add_action('wp_loaded', array($this,'cardgate_checkout_fees'));

		register_activation_hook( __FILE__, array( &$this, 'cardgate_install' ) ); // hook for install
		register_deactivation_hook( __FILE__, array( &$this, 'cardgate_uninstall' ) ); // hook for uninstall
		update_option( 'cardgate_version', $this->plugin_get_version() );
		update_option( 'is_callback_status_change', false );
		add_action( 'woocommerce_cancelled_order', array( &$this, 'capture_payment_failed' ) );

		if ( ! $this->cardgate_settings() ) {
			add_action( 'admin_notices', array( &$this, 'my_error_notice' ) );
		}
	}

	/**
	 * Install plug-in.
	 */
	public function cardgate_install() {
		global $wpdb;

		// Cardgate payments table.
		$table_name      = $wpdb->prefix . 'cardgate_payments';
		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = 'DEFAULT CHARACTER SET ' . $wpdb->charset;
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= ' COLLATE ' . $wpdb->collate;
		}

		// Do the create just in case the db does not exists.
		$create_query = "CREATE TABLE IF NOT EXISTS $table_name (
			id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
                        order_id VARCHAR(16) NULL ,
			parent_id VARCHAR(16) NULL ,
			transaction_id VARCHAR(16) NULL ,
                        subscription_id VARCHAR(16) NULL,
			currency VARCHAR(8) NOT NULL ,
			amount DECIMAL(10, 0) NOT NULL ,
			gateway_language VARCHAR(8) NOT NULL ,
			payment_method VARCHAR(25) NOT NULL ,
			bank_option VARCHAR(10) NULL ,
			first_name VARCHAR(255) NOT NULL ,
			last_name VARCHAR(255) NOT NULL ,
			address VARCHAR(255) NOT NULL ,
			postal_code VARCHAR(255) NOT NULL ,
			city VARCHAR(255) NOT NULL ,
			country VARCHAR(5) NOT NULL ,
			email VARCHAR(255) NOT NULL ,
			status VARCHAR(10) NOT NULL ,
  			date_gmt DATETIME NOT NULL ,
			PRIMARY KEY  (id) ,
			KEY order_id (order_id) 
			) $charset_collate;";

		require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		dbDelta( $create_query );
		update_option( 'cgp_version', $this->plugin_get_version() );
	}

	/**
	 * Unistall plug-in.
	 */
	public function cardgate_uninstall() {
		// no data is deleted.
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 * - WP_LANG_DIR/woocommerce/woocommerce-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'cardgate', false, plugin_basename( __DIR__ ) . '/i18n/languages' );
	}

	/**
	 * Plugin includes.
	 */
	public function includes() {

		// Make the WC_Gateway_Dummy class available.
		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			require_once 'classes/CGP_Common_Gateway.php';
			require_once 'classes/WC_CardgateAfterpay.php';
			require_once 'classes/WC_CardgateBancontact.php';
			require_once 'classes/WC_CardgateBanktransfer.php';
			require_once 'classes/WC_CardgateBillink.php';
			require_once 'classes/WC_CardgateBitcoin.php';
			require_once 'classes/WC_CardgateCrypto.php';
			require_once 'classes/WC_CardgateCreditcard.php';
			require_once 'classes/WC_CardgateDirectDebit.php';
			require_once 'classes/WC_CardgateGiftcard.php';
			require_once 'classes/WC_CardgateIdeal.php';
			require_once 'classes/WC_CardgateIdealqr.php';
			require_once 'classes/WC_CardgateKlarna.php';
			require_once 'classes/WC_CardgateOnlineueberweisen.php';
			require_once 'classes/WC_CardgatePayPal.php';
			require_once 'classes/WC_CardgatePaysafecard.php';
			require_once 'classes/WC_CardgatePaysafecash.php';
			require_once 'classes/WC_CardgatePrzelewy24.php';
			require_once 'classes/WC_CardgateSofortbanking.php';
			require_once 'classes/WC_CardgateSpraypay.php';
		}
	}

	/**
	 * Configuration page.
	 */
	public static function cardgate_config_page() {
		global $wpdb;

		$icon_file = plugins_url( 'images/cardgate.png', __FILE__ );
		$notice    = '';

		if ( isset( $_POST['Submit'] ) ) {
			if ( empty( $_POST ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce134'] ) ), 'action854' ) ) {
				print 'Sorry, your nonce did not verify.';
				exit();
			} else {
				// process form data.
				update_option( 'cgp_mode', sanitize_text_field( wp_unslash( $_POST['cgp_mode'] ) ) );
				update_option( 'cgp_siteid', trim( sanitize_text_field( wp_unslash( $_POST['cgp_siteid'] ) ) ) );
				update_option( 'cgp_hashkey', sanitize_text_field( wp_unslash( $_POST['cgp_hashkey'] ) ) );
				update_option( 'cgp_merchant_id', trim( sanitize_text_field( wp_unslash( $_POST['cgp_merchant_id'] ) ) ) );
				update_option( 'cgp_merchant_api_key', sanitize_text_field( wp_unslash( $_POST['cgp_merchant_api_key'] ) ) );
				update_option( 'cgp_checkoutdisplay', sanitize_text_field( wp_unslash( $_POST['cgp_checkoutdisplay'] ) ) );

				$is_test          = ( 1 === (int) $_POST['cgp_mode'] ? true : false );
				$merchant_id      = (int) $_POST['cgp_merchant_id'];
				$merchant_api_key = sanitize_text_field( wp_unslash( $_POST['cgp_merchant_api_key'] ) );

				$c       = new Cardgate();
				$site_id = (int) $_POST['cgp_siteid'];
				$methods = $c->get_methods( $site_id, $merchant_id, $merchant_api_key, $is_test );
				$method  = $methods[0];

				if ( ! is_object( $method ) ) {
					$notice = sprintf(
						'%s<br>%s',
						esc_html__( 'The settings are not correct for the Mode you chose.', 'cardgate' ),
						esc_html__( 'See the instructions above. ', 'cardgate' )
					);
				}
				$methods = $method = null;
			}
		}

		if ( '' !== get_option( 'cgp_siteid' ) && '' === get_option( 'cgp_hashkey' ) ) {
			$notice = esc_html__( 'The CardGate payment methods will only be visible in the WooCommerce Plugin, once the Site ID and Hashkey have been filled in.', 'cardgate' );
		}
		self::get_config_html( $icon_file, $notice );
	}

	/**
	 * Get configuration HTML.
	 *
	 * @param string $icon_file Icon file URL.
	 * @param string $notice Notice message.
	 */
	public static function get_config_html( $icon_file, $notice ) {
		$action_url = $_SERVER['REQUEST_URI'];
		?>
		<div class="wrap">
			<form name="frmCardgate" action="<?php echo esc_url( $action_url ); ?>" method="post">
			<?php wp_nonce_field( 'action854', 'nonce134' ); ?>
			<img style="max-width:100px;" src="<?php echo esc_url( $icon_file ); ?>" />
			<b>Version <?php echo esc_html( get_option( 'cardgate_version' ) ); ?></b>
				<h2> <?php echo esc_html__( 'CardGate Settings', 'cardgate' ); ?></h2>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">&nbsp</th>
							<td colspan="2">&nbsp</td>
					</tr>
					<tr>
						<th scope="row">
						<label for="cgp_mode"><?php echo esc_html__( 'Mode', 'cardgate' ); ?></label>
						</th>
						<td>
								<select style="width:60px;" id="cgp_mode" name="cgp_mode">
									<option value="1" <?php echo ( get_option( 'cgp_mode' ) == '1' ? ( 'selected="selected"' ) : '' ); ?>><?php echo esc_html__( 'Test', 'cardgate' ); ?></option>
									<option value="0" <?php echo ( get_option( 'cgp_mode' ) == '0' ? ( 'selected="selected"' ) : '' ); ?>><?php echo esc_html__( 'Live', 'cardgate' ); ?></option>
								</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
						<label for="cgp_siteid"><?php echo esc_html__( 'Site ID', 'cardgate' ); ?></label>
						</th>
						<td><input type="text" style="width:60px;" id="cgp_siteid" name="cgp_siteid" value=" <?php echo esc_attr( get_option( 'cgp_siteid' ) ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
						<label for="cgp_hashkey"><?php echo esc_html__( 'Hash key', 'cardgate' ); ?></label>
						</th>
						<td><input type="text" style="width:150px;" id="cgp_hashkey" name="cgp_hashkey" value="<?php echo esc_attr( get_option( 'cgp_hashkey' ) ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
						<label for="cgp_merchant_id"><?php echo esc_html__( 'Merchant ID', 'cardgate' ); ?></label>
						</th>
						<td><input type="text" style="width:60px;" id="cgp_merchant_id" name="cgp_merchant_id" value="<?php echo esc_attr( get_option( 'cgp_merchant_id' ) ); ?> "/>
						</td>
					</tr>
					<tr>
						<th scope="row">
						<label for="cgp_merchant_api_key"><?php echo esc_html__( 'API key', 'cardgate' ); ?></label>
						</th>
						<td><input type="password" style="width:600px;" id="cgp_merchant_api_key" name="cgp_merchant_api_key" value="<?php echo esc_attr( get_option( 'cgp_merchant_api_key' ) ); ?>"/>
						</td>
					</tr>
					<tr>
						<th scope="row">
						<label for="cgp_checkoutdisplay"><?php echo esc_html__( 'Checkout display', 'cardgate' ); ?></label>
						</th>
						<td>
								<select style="width:140px;" id="cgp_checkoutdisplay" name="cgp_checkoutdisplay">
									<option value="withoutlogo"<?php echo ( get_option( 'cgp_checkoutdisplay' ) == 'withoutlogo' ? ( 'selected="selected"' ) : '' ); ?> > <?php echo esc_html__( 'Without Logo', 'cardgate' ); ?></option>
									<option value="withlogo"<?php echo ( get_option( 'cgp_checkoutdisplay' ) == 'withlogo' ? ( 'selected="selected"' ) : '' ); ?> > <?php echo esc_html__( 'With Logo', 'cardgate' ); ?></option>
								</select>
						</td>
					</tr>
					<tr>
						<td colspan="2">
						<?php
						sprintf(
							'%s <b>%s</b> %s <a href="https://my.cardgate.com/">%s </a> &nbsp %s <a href="https://github.com/cardgate/woocommerce/blob/master/%s" target="_blank"> %s</a> %s.',
							__( 'Use the ', 'cardgate' ),
							__( 'Settings button', 'cardgate' ),
							__( 'in your', 'cardgate' ),
							__( 'My CardGate', 'cardgate' ),
							__( 'to set these values, as explained in the', 'cardgate' ),
							__( 'README.md', 'cardgate' ),
							__( 'installation instructions', 'cardgate' ),
							__( 'of this plugin', 'cardgate' )
						)
						?>
			</td>
					</tr>
					<tr>
						<td colspan="2"><?php echo __( 'These settings apply to all CardGate payment methods used in the WooCommerce plugin.', 'cardgate' ); ?></td>
					</tr>
					<tr>
						<td colspan="2" style="height=60px;">&nbsp</td>
					</tr>
					<tr>
						<td colspan="2"><b><?php echo $notice; ?></b></td>
					</tr>
					<tr>
						<td colspan="2"><?php submit_button( __( 'Save Changes' ), 'primary', 'Submit', false ); ?>
					</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php
	}

	// //////////////////////////////////////////////

	/**
	 * Generate the payment table
	 */
	static function cardgate_payments_table() {
		require_once 'classes/Cardgate_PaymentsListTable.php';
		global $wp_list_table;
		$wp_list_table = new Cardgate_PaymentsListTable();
		$icon_file     = plugins_url( 'images/cardgate.png', __FILE__ );
		$wp_list_table->prepare_items();
		?>
<div class="wrap">
			<div><?php echo '<img style="max-width:100px;" src="' . $icon_file . '" />&nbsp;'; ?></div>
			<h2>
				<?php echo __( 'CardGate Payments', 'cardgate' ); ?>
			</h2>

			<?php $wp_list_table->views(); ?>

			<form method="post" action="">
				<?php $wp_list_table->search_box( __( 'Search Payments', 'cardgate' ), 'payment' ); ?>

				<?php $wp_list_table->display(); ?>
			</form>

	<br class="clear" />
</div>
		<?php
	}

	// ////////////////////////////////////////////////

	/**
	 * Create the admin menu.
	 */
	public static function cgp_admin_menu() {
		add_menu_page(
			'cardgate',
			'CardGate',
			'manage_options',
			'cardgate_menu',
			array(
				__CLASS__,
				'cardgate_config_page',
			),
			plugins_url( 'cardgate/images/cgp_icon-16x16.png' )
		);

		add_submenu_page(
			'cardgate_menu',
			__( 'Settings', 'cardgate' ),
			__( 'Settings', 'cardgate' ),
			'manage_options',
			'cardgate_menu',
			array(
				__CLASS__,
				'cardgate_config_page',
			)
		);
	}

	// ////////////////////////////////////////////////

	/**
	 * Check whether a page is published and available.
	 *
	 * @param int $id Page ID.
	 * @return bool
	 */
	public function page_is_published( $id ) {
		global $wpdb;
		$status = $wpdb->get_var( $wpdb->prepare( 'SELECT post_status FROM ' . $wpdb->prefix . 'posts WHERE ID=%d', $id ) );
		if ( 'publish' === $status ) {
			return true;
		} else {
			return false;
		}
	}

	// ////////////////////////////////////////////////

	/**
	 * Perform Hashcheck authentication.
	 *
	 * @param array  $data Data.
	 * @param string $hash_key Hash key.
	 * @param bool   $test_mode Test mode.
	 * @return bool
	 */
	private function hash_check( $data, $hash_key, $test_mode ) {

		try {

			$merchant_id      = (int) ( get_option( 'cgp_merchant_id' ) ? get_option( 'cgp_merchant_id' ) : 0 );
			$merchant_api_key = ( get_option( 'cgp_merchant_api_key' ) ? get_option( 'cgp_merchant_api_key' ) : 0 );

			$cardgate = new cardgate\api\Client( $merchant_id, $merchant_api_key, $test_mode );
			$cardgate->setIp( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) );

			if ( false === $cardgate->transactions()->verifyCallback( $data, $hash_key ) ) {
				return false;
			} else {
				return true;
			}
		} catch ( cardgate\api\Exception $e ) {
			return false;
		}
	}

	// ////////////////////////////////////////////////

	/**
	 * Handle callback from payment gateway.
	 */
	public function cardgate_callback() {
		global $wpdb;

		if ( isset( $_REQUEST['cgp_sitesetup'] ) && ! empty( $_REQUEST['cgp_sitesetup'] ) && ! empty( $_REQUEST['token'] ) ) {

			try {

				$version          = ( '' === $this->get_woocommerce_version() ? 'unkown' : $this->get_woocommerce_version() );
				$language         = substr( get_locale(), 0, 2 );
				$is_test          = ( 1 === (int) $_REQUEST['testmode'] ? true : false );
				$merchant_id      = (int) ( false === get_option( 'cgp_merchant_id' ) ? 0 : get_option( 'cgp_merchant_id' ) );
				$merchant_api_key = ( false === get_option( 'cgp_merchant_api_key' ) ? 'initconfig' : get_option( 'cgp_merchant_api_key' ) );
				$cardgate         = new cardgate\api\Client( $merchant_id, $merchant_api_key, $is_test );
				$cardgate->setIp( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) );
				$cardgate->setLanguage( $language );
				$cardgate->version()->setPlatformName( 'Woocommerce' );
				$cardgate->version()->setPlatformVersion( $version );
				$cardgate->version()->setPluginName( 'CardGate' );
				$cardgate->version()->setPluginVersion( get_option( 'cardgate_version' ) );
				$result = $cardgate->pullConfig( sanitize_text_field( wp_unslash( $_REQUEST['token'] ) ) );
				if ( isset( $result['success'] ) && 1 === (int) $result['success'] ) {
					$config_data = $result['pullconfig']['content'];
					update_option( 'cgp_mode', $config_data['testmode'] );
					update_option( 'cgp_siteid', $config_data['site_id'] );
					update_option( 'cgp_hashkey', $config_data['site_key'] );
					update_option( 'cgp_merchant_id', $config_data['merchant_id'] );
					update_option( 'cgp_merchant_api_key', $config_data['api_key'] );
					die( esc_html( $config_data['merchant'] . '.' . get_option( 'cgp_siteid' ) . '.200' ) );
				} else {
					die( 'Token retrieval failed.' );
				}
			} catch ( cardgate\api\Exception $e ) {
				die( esc_html( $e->getMessage() ) );
			}
		}

		// check that the callback came from CardGate.
		if ( isset( $_GET['cgp_notify'] ) && 'true' === $_GET['cgp_notify'] && empty( $_REQUEST['cgp_sitesetup'] ) ) {
			// hash check.
			$is_test = ( 1 === (int) get_option( 'cgp_mode' ) ? true : false );
			if ( ! $this->hash_check( $_REQUEST, get_option( 'cgp_hashkey' ), $is_test ) ) {
				exit( 'HashCheck failed.' );
			}

			$order_no = (int) substr( sanitize_text_field( wp_unslash( $_REQUEST['reference'] ) ), 11 );

			// process order.
			$order        = new WC_Order( $order_no );
			$order_status = $order->get_status();

			if ( ( 'processing' !== $order_status && 'completed' !== $order_status ) ) {

				$code = (int) $_REQUEST['code'];

				if ( $code >= 200 && $code < 300 ) {
					// success.
					$order->set_transaction_id( sanitize_text_field( wp_unslash( $_REQUEST['transaction'] ) ) );
					$order->payment_complete();
				}

				if ( 0 === (int) $_REQUEST['code'] || 100 === (int) $_REQUEST['code'] ) {
					$return_status = 'pending';
				}
				if ( (int) $_REQUEST['code'] >= 200 && (int) $_REQUEST['code'] < 300 ) {
						$return_status = 'completed';
				}
				if ( $code >= 300 && $code < 400 ) {
					$order->update_status( 'failed' );
					$return_status = 'failed';
				}
				if ( $code >= 700 && $code < 800 ) {
					$order->update_status( 'on-hold' );
					$return_status = 'waiting';
				}

				$order->add_order_note( 'Curo transaction (' . sanitize_text_field( wp_unslash( $_REQUEST['transaction'] ) ) . ') payment ' . $return_status . '.' );
				exit( esc_html( sanitize_text_field( wp_unslash( $_REQUEST['transaction'] ) ) . '.' . (int) $_REQUEST['code'] ) );
			} else {
				exit( 'payment already processed' );
			}
		}
	}

	/**
	 * Capture payment failed.
	 *
	 * @return bool
	 */
	public function capture_payment_failed() {
		if ( isset( $_REQUEST['cancel_order'] ) && ( true === $_REQUEST['cancel_order'] || 'true' === $_REQUEST['cancel_order'] ) && isset( $_REQUEST['transaction'] ) && strpos( $_REQUEST['transaction'], 'T' ) && isset( $_REQUEST['status'] ) && 'failure' === $_REQUEST['status'] ) {
			wc_clear_notices();
			wc_add_notice( __( 'Your payment has failed. Please choose an other payment method.', 'cardgate' ), 'error' );
		}
		return true;
	}

	// ////////////////////////////////////////////////

	/**
	 * Create form to create specific CardGate pages for error, response, and complete status
	 *
	 * @param array   $pages
	 * @param string  $namePrefix
	 * @param integer $level
	 */
	function cardgate_pages( $pages, $namePrefix, $level = 0 ) {
		?>
<ul style="padding-left: <?php echo $level * 25; ?>px">

			<?php foreach ( $pages as $i => $page ) : ?>

				<li>
					<?php $name = $namePrefix . '[' . $i . ']'; ?>

					<h3><?php echo $page['post_title']; ?></h3>

		<table class="form-table">
			<tr>
				<th scope="row"><label
					for="cardgate_page_<?php echo $i; ?>_post_title">
									<?php _e( 'Title', 'cardgate' ); ?>
								</label></th>
				<td><input id="cardgate_page_<?php echo $i; ?>_post_title"
					name="<?php echo $name; ?>[post_title]"
					value="<?php echo $page['post_title']; ?>" type="text"
					class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label
					for="cardgate_page_<?php echo $i; ?>_post_name">
									<?php _e( 'Slug', 'cardgate' ); ?>
								</label></th>
				<td><input id="cardgate_page_<?php echo $i; ?>_post_name"
					name="<?php echo $name; ?>[post_name]"
					value="<?php echo $page['post_name']; ?>" type="text"
					class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label
					for="cardgate_page_<?php echo $i; ?>_post_content">
									<?php _e( 'Content', 'cardgate' ); ?>
								</label></th>
				<td><textarea id="cardgate_page_<?php echo $i; ?>_post_content"
						name="<?php echo $name; ?>[post_content]" rows="2" cols="60"><?php echo $page['post_content']; ?></textarea>
				</td>
			</tr>
		</table>

					<?php
					if ( isset( $page['children'] ) ) {
						self::cardgate_pages( $page['children'], $name . '[children]', $level + 1 );
					}
					?>
				</li>

			<?php endforeach; ?>

		</ul>
		<?php
	}

	// ////////////////////////////////////////////////

	/**
	 * Create WordPress Cardgate pages for error, response, and complete status
	 *
	 * @param array $pages
	 * @param array $parent
	 */
	function cardgate_create_pages( $pages, $parent = null ) {
		$i = 0;
		foreach ( $pages as $page ) {
			$post = array(
				'post_title'     => $page['post_title'],
				'post_name'      => $page['post_name'],
				'post_content'   => $page['post_content'],
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'comment_status' => 'closed',
			);

			if ( isset( $parent ) ) {
				++$i;
				$post['post_parent'] = $parent;
			}

			$result = wp_insert_post( $post, true );
			switch ( $i ) {
				case 0:
					break;
				case 1:
					$option = 'cgp_completed';
					break;
				case 2:
					$option = 'cgp_cancelled';
					break;
				case 3:
					$option = 'cgp_error';
					break;
			}
			if ( $i > 0 ) {
				update_option( $option, $result );
			}
			if ( ! is_wp_error( $result ) ) {
				if ( isset( $page['children'] ) ) {
					self::cardgate_create_pages( $page['children'], $result );
				}
			}
		}
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
		return isset( $plugin_folder[ $plugin_file ]['Version'] ) ? $plugin_folder[ $plugin_file ]['Version'] : '';
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public static function plugin_get_version() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_folder = get_plugins( '/' . plugin_basename( __DIR__ ) );
		$plugin_file   = basename( ( __FILE__ ) );
		return isset( $plugin_folder[ $plugin_file ]['Version'] ) ? $plugin_folder[ $plugin_file ]['Version'] : '';
	}

	/**
	 * Initiate payment classes.
	 */
	public function initiate_payment_classes() {
		add_filter( 'woocommerce_payment_gateways', array( $this, 'woocommerce_cardgate_add_gateways' ) );
	}

	/**
	 * Add CardGate gateways.
	 *
	 * @param array $methods Methods.
	 * @return array
	 */
	public function woocommerce_cardgate_add_gateways( $methods ) {
		foreach ( $this->payment_names as $payment_name ) {
			$methods[] = 'WC_Cardgate' . $payment_name;
		}
		return $methods;
	}

	/**
	 * Add CardGate form fields.
	 */
	public function add_cgform_fields() {
		global $woocommerce;

		// Get current tab/section.
		$current_tab     = ( empty( $_GET['tab'] ) ) ? '' : sanitize_text_field( wp_unslash( $_GET['tab'] ) );
		$current_section = ( empty( $_REQUEST['section'] ) ) ? '' : sanitize_text_field( wp_unslash( $_REQUEST['section'] ) );

		$pos = strpos( $current_section, 'cardgate' ) === false;
		if ( 'checkout' === $current_tab && '' !== $current_section && ( ! $pos ) ) {
			$gateways = $woocommerce->payment_gateways->payment_gateways();

			foreach ( $gateways as $gateway ) {
				if ( ( strtolower( get_class( $gateway ) ) === 'wc_' . $current_section ) || ( strtolower( get_class( $gateway ) ) === $current_section ) ) {
					$current_gateway     = $gateway->id;
					$extra_charges_id    = 'woocommerce_' . $current_gateway . '_extra_charges';
					$extra_charges_type  = $extra_charges_id . '_type';
					$extra_charges_label = $extra_charges_id . '_label';
					if ( isset( $_REQUEST['save'] ) ) {
						update_option( $extra_charges_id, sanitize_text_field( wp_unslash( $_REQUEST[ $extra_charges_id ] ) ) );
						update_option( $extra_charges_type, sanitize_text_field( wp_unslash( $_REQUEST[ $extra_charges_type ] ) ) );

						update_option( $extra_charges_label, sanitize_text_field( wp_unslash( $_REQUEST[ $extra_charges_label ] ) ) );
					}
					$extra_charges            = get_option( $extra_charges_id );
					$extra_charges_cust       = get_option( $extra_charges_label );
					$extra_charges_type_value = get_option( $extra_charges_type );
				}
			}

			?>
<script>
				jQuery(document).ready(function($){
					$data = '<h3><?php echo esc_html__( 'Add Extra Fees', 'cardgate' ); ?></h3><table class="form-table">';
					$data += '<tr vertical-align="top">';
					$data += '<th scope="row" class="titledesc"><?php echo esc_html__( 'Extra Fee', 'cardgate' ); ?></th>';
					$data += '<td class="forminp">';
					$data += '<fieldset>';
					$data += '<input style="" name="<?php echo esc_attr( $extra_charges_id ); ?>" id="<?php echo esc_attr( $extra_charges_id ); ?>" type="text" value="<?php echo esc_attr( $extra_charges ); ?>"/>';
					$data += '<br /></fieldset></td></tr>';
	
					$data += '<tr vertical-align="top">';
					$data += '<th scope="row" class="titledesc"><?php echo esc_html__( 'Label for Extra Fee', 'cardgate' ); ?></th>';
					$data += '<td class="forminp">';
					$data += '<fieldset>';
					$data += '<input style="" name="<?php echo esc_attr( $extra_charges_label ); ?>" id="<?php echo esc_attr( $extra_charges_label ); ?>" type="text" value="<?php echo esc_attr( $extra_charges_cust ); ?>" placeholder="<?php echo esc_attr__( 'My Custom Label', 'cardgate' ); ?>"/>';
					$data += '<br /></fieldset></td></tr>';
					$data += '<tr vertical-align="top">';
					$data += '<th scope="row" class="titledesc"><?php echo esc_html__( 'Fee type', 'cardgate' ); ?></th>';
					$data += '<td class="forminp">';
					$data += '<fieldset>';
					$data += '<select name="<?php echo esc_attr( $extra_charges_type ); ?>"><option 
					<?php
					if ( 'add' === $extra_charges_type_value ) {
						echo 'selected=selected';}
					?>
					value="add"><?php echo esc_html__( 'Add Fee to Total', 'cardgate' ); ?></option>';
					$data += '<option 
					<?php
					if ( 'percentage' === $extra_charges_type_value ) {
						echo 'selected=selected';}
					?>
					value="percentage"><?php echo esc_html__( 'Percentage of Total', 'cardgate' ); ?></option>';
					$data += '<br /></fieldset></td></tr></table>';
					$('.form-table:last').after($data);
	
				});
	</script>
			<?php
		}
	}
	/**
	 * Calculate fees.
	 *
	 * @param array $totals Totals.
	 * @return array
	 */
	public function calculate_fees( $totals ) {
		global $woocommerce;

		$woocommerce->session->extra_cart_fee = 0;
		$available_gateways                   = $woocommerce->payment_gateways->get_available_payment_gateways();
		$current_gateway                      = '';
		if ( ! empty( $available_gateways ) ) {
			// Chosen Method.
			if ( isset( $woocommerce->session->chosen_payment_method ) && isset( $available_gateways[ $woocommerce->session->chosen_payment_method ] ) ) {
				$current_gateway = $available_gateways[ $woocommerce->session->chosen_payment_method ];
			} elseif ( isset( $available_gateways[ get_option( 'woocommerce_default_gateway' ) ] ) ) {
				$current_gateway = $available_gateways[ get_option( 'woocommerce_default_gateway' ) ];
			} else {
				$current_gateway = current( $available_gateways );
			}
		}

		if ( '' !== $current_gateway ) {
			$current_gateway_id = $current_gateway->id;
			if ( strpos( $current_gateway_id, 'cardgate' ) === false ) {
				return $totals;
			}
			$extra_charges_id          = 'woocommerce_' . $current_gateway_id . '_extra_charges';
			$extra_charges_type        = $extra_charges_id . '_type';
			$extra_charges_cust        = $extra_charges_id . '_label';
			$extra_charges             = (float) get_option( $extra_charges_id );
			$extra_charges_type_value  = get_option( $extra_charges_type );
			$extra_charges_label_value = get_option( $extra_charges_cust );
			if ( $extra_charges ) {
				if ( 'percentage' === $extra_charges_type_value ) {
					$t1 = ( $totals->cart_contents_total * $extra_charges ) / 100;
				} else {
					$t1 = $extra_charges;
				}

				$this->current_gateway_title                    = $current_gateway->settings['title'];
				$this->current_gateway_extra_charges            = $extra_charges;
				$this->current_gateway_extra_charges_type_value = $extra_charges_type_value;

				$t5 = ( 'percentage' === $extra_charges_type_value ? $extra_charges . '%' : 'Fixed' );

				if ( isset( $extra_charges_label_value ) && strlen( $extra_charges_label_value ) > 2 ) {
					$t6 = $extra_charges_label_value . ' - ';
				} else {
					$t6 = $this->current_gateway_title . '  Extra Charges -  ';
				}

				$woocommerce->cart->add_fee( $t6 . $t5, $t1 );
				$woocommerce->session->extra_cart_fee = $t1;
			}
		}
		return $totals;
	}
	/**
	 * Check if AJAX block update.
	 *
	 * @param array $post Post data.
	 * @return bool
	 */
	public function is_ajax_block_update( $post ) {
		return ( isset( $post['action'] ) && 'wp_ajax_cardgate_checkout_fees' === $post['action'] ) ? true : false;
	}

	/**
	 * Load CardGate script.
	 */
	public function load_cg_script() {
		wp_enqueue_script(
			'wc-add-extra-charges',
			$this->plugin_url . '/assets/app.js',
			array(
				'wc-checkout',
			),
			false,
			true
		);
	}

	/**
	 * WooCommerce CardGate blocks support.
	 */
	public function woocommerce_cardgate_blocks_support() {
		// Check if the required class exists.
		if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			return;
		}

		// Include the custom Blocks Checkout class.
		require_once 'classes/woocommerce-blocks/bancontact/BancontactCardgate.php';
		require_once 'classes/woocommerce-blocks/afterpay/AfterpayCardgate.php';
		require_once 'classes/woocommerce-blocks/banktransfer/BanktransferCardgate.php';
		require_once 'classes/woocommerce-blocks/billink/BillinkCardgate.php';
		require_once 'classes/woocommerce-blocks/bitcoin/BitcoinCardgate.php';
		require_once 'classes/woocommerce-blocks/crypto/CryptoCardgate.php';
		require_once 'classes/woocommerce-blocks/creditcard/CreditcardCardgate.php';
		require_once 'classes/woocommerce-blocks/directdebit/DirectDebitCardgate.php';
		require_once 'classes/woocommerce-blocks/giftcard/GiftcardCardgate.php';
		require_once 'classes/woocommerce-blocks/ideal/IdealCardgate.php';
		require_once 'classes/woocommerce-blocks/idealqr/IdealqrCardgate.php';
		require_once 'classes/woocommerce-blocks/klarna/KlarnaCardgate.php';
		require_once 'classes/woocommerce-blocks/onlineueberweisen/OnlineueberweisenCardgate.php';
		require_once 'classes/woocommerce-blocks/paypal/PaypalCardgate.php';
		require_once 'classes/woocommerce-blocks/paysafecard/PaysafecardCardgate.php';
		require_once 'classes/woocommerce-blocks/paysafecash/PaysafecashCardgate.php';
		require_once 'classes/woocommerce-blocks/przelewy24/Przelewy24Cardgate.php';
		require_once 'classes/woocommerce-blocks/sofortbanking/SofortbankingCardgate.php';
		require_once 'classes/woocommerce-blocks/spraypay/SpraypayCardgate.php';

		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function ( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				foreach ( $this->payment_names as $name ) {
					$blockmethod = $name . 'Cardgate';
					$payment_method_registry->register( new $blockmethod() );
				}
			}
		);
	}

	/**
	 * Set plugin URL.
	 */
	public function set_plugin_url() {
		$this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get CardGate methods.
	 *
	 * @param int    $site_id Site ID.
	 * @param int    $merchant_id Merchant ID.
	 * @param string $merchant_api_key Merchant API key.
	 * @param bool   $test_mode Test mode.
	 * @return array
	 */
	public function get_methods( $site_id, $merchant_id, $merchant_api_key, $test_mode ) {
		try {

			$cardgate = new cardgate\api\Client( $merchant_id, $merchant_api_key, $test_mode );
			$cardgate->setIp( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) );

			$methods = $cardgate->methods()->all( $site_id );
		} catch ( cardgate\api\Exception $e ) {
			$methods[0] = array(
				'id'   => 0,
				'name' => esc_html( $e->getMessage() ),
			);
		}
		return $methods;
	}

	/**
	 * My error notice.
	 */
	public function my_error_notice() {
		?>
<div class="error notice">
	<p>
		<b>CardGate: </b> 
		<?php
		printf(
			/* translators: 1: opening b tag, 2: closing b tag, 3: My CardGate link, 4: README.md link */
			esc_html__( 'Use the %1$sSettings button%2$s in your %3$s to set these values, as explained in the %4$s installation instructions of this plugin.', 'cardgate' ),
			'<b>',
			'</b>',
			'<a href="https://my.cardgate.com/">' . esc_html__( 'My CardGate', 'cardgate' ) . '</a>',
			'<a href="https://github.com/cardgate/woocommerce/blob/master/README.md" target="_blank">' . esc_html__( 'README.md', 'cardgate' ) . '</a>'
		);
		?>
							</p>
</div>
		<?php
	}

	/**
	 * Check CardGate settings.
	 *
	 * @return bool
	 */
	public function cardgate_settings() {
		if ( ! get_option( 'cgp_siteid' ) ) {
			return false;
		}
		if ( ! get_option( 'cgp_hashkey' ) ) {
			return false;
		}
		if ( ! get_option( 'cgp_merchant_id' ) ) {
			return false;
		}
		if ( ! get_option( 'cgp_merchant_api_key' ) ) {
			return false;
		}
		return true;
	}
}

new Cardgate();
?>
