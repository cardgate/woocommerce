<?php

/**
 * Plugin Name: CardGate
 * Plugin URI: http://cardgate.com
 * Description: Integrates Cardgate Gateway for WooCommerce into WordPress
 * Author: CardGate
 * Author URI: https://www.cardgate.com
 * Version: 4.0.1
 * Text Domain: cardgate
 * Domain Path: /i18n/languages
 * Requires at least: 4.4
 * WC requires at least: 3.0.0
 * WC tested up to: 10.1.0
 * License: GPLv3 or later
 * Requires Plugins: woocommerce
 */

require_once WP_PLUGIN_DIR . '/cardgate/cardgate-clientlib-php/init.php';

class cardgate {
    protected $current_gateway_title = '';
    protected $current_gateway_extra_charges = '';
	protected $current_gateway_extra_charges_type_value = '';
    protected $plugin_url;
    protected $payment_names = ['Afterpay',
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
                                'Spraypay'];
    /**
     * Initialize plug-in
     */
    function __construct() {
        // Set up localisation.
        $this->load_plugin_textdomain();
        $this->set_plugin_url();

        add_action( 'plugins_loaded', array(&$this, 'includes' ), 0 );
        add_action('plugins_loaded', array(&$this,'initiate_payment_classes'));
	    add_action( 'before_woocommerce_init', function() {
		    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		    }
	    } );


	    add_action('admin_head', array($this,'add_cgform_fields'));
	    add_action('woocommerce_cart_calculate_fees', array($this,'calculate_fees'),1);
        add_action('wp_enqueue_scripts', array($this,'load_cg_script'),10, 1);
        add_action('admin_menu', array(&$this,'CGPAdminMenu'));
        add_action('init', array(&$this,'cardgate_callback'), 20);
	    add_action( 'woocommerce_blocks_loaded', array($this,'woocommerce_cardgate_blocks_support' ));
	 //   add_action('wp_loaded', array($this,'cardgate_checkout_fees'));

        register_activation_hook(__FILE__, array(&$this,'cardgate_install')); // hook for install
        register_deactivation_hook(__FILE__, array(&$this,'cardgate_uninstall')); // hook for uninstall
        update_option('cardgate_version', $this->plugin_get_version());
        update_option('is_callback_status_change', false);
        add_action('woocommerce_cancelled_order', array(&$this,'capture_payment_failed'));

        if (! $this->cardgate_settings())
            add_action('admin_notices', array(&$this,'my_error_notice'));
    }
    
    /**
     * Install plug-in
     */
    function cardgate_install() {
        global $wpdb;

        // Cardgate payments table
        $sTableName = $wpdb->prefix . 'cardgate_payments';
        $sCharsetCollate = '';
        if (! empty($wpdb->charset)) {
            $sCharsetCollate = 'DEFAULT CHARACTER SET ' . $wpdb->charset;
        }
        if (! empty($wpdb->collate)) {
            $sCharsetCollate .= ' COLLATE ' . $wpdb->collate;
        }
        
        // Do the create just in case the db does not exists
        $sCreateQuery = "CREATE TABLE IF NOT EXISTS $sTableName (
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
			) $sCharsetCollate;";
        
        require_once ABSPATH . '/wp-admin/includes/upgrade.php';
        dbDelta($sCreateQuery);
        update_option('cgp_version', $this->plugin_get_version());
    }

    // ////////////////////////////////////////////////
    
    /**
     * Unistall plug-in
     */
    function cardgate_uninstall() {
        // no data is deleted
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
        load_plugin_textdomain('cardgate', false, plugin_basename(dirname(__FILE__)) . '/i18n/languages');
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
     * Configuration page
     */
    static function cardgate_config_page() {
        global $wpdb;
        
        $icon_file = plugins_url('images/cardgate.png', __FILE__);
        $notice = '';
        
        if (isset($_POST['Submit'])) {
            if (empty($_POST) || ! wp_verify_nonce($_POST['nonce134'], 'action854')) {
                print 'Sorry, your nonce did not verify.';
                exit();
            } else {
                // process form data
                update_option('cgp_mode', $_POST['cgp_mode']);
                update_option('cgp_siteid', trim($_POST['cgp_siteid']));
                update_option('cgp_hashkey', $_POST['cgp_hashkey']);
                update_option('cgp_merchant_id', trim($_POST['cgp_merchant_id']));
                update_option('cgp_merchant_api_key', $_POST['cgp_merchant_api_key']);
                update_option('cgp_checkoutdisplay', $_POST['cgp_checkoutdisplay']);
                
                $bIsTest = ($_POST['cgp_mode'] == 1 ? TRUE : FALSE);
                $iMerchantId = (int) $_POST['cgp_merchant_id'];
                $sMerchantApiKey = $_POST['cgp_merchant_api_key'];
                
                $c = new cardgate();
                $iSiteId = (int) $_POST['cgp_siteid'];
                $aMethods = $c->get_methods($iSiteId, $iMerchantId, $sMerchantApiKey, $bIsTest);
                $oMethod = $aMethods[0];
                
                if (! is_object($oMethod)) {
                    $notice = sprintf('%s<br>%s'
                        ,__('The settings are not correct for the Mode you chose.','cardgate'),__('See the instructions above. ', 'cardgate'));
                }
                $aMethods = $oMethod = null;
            }
        }

        if (get_option('cgp_siteid') != '' && get_option('cgp_hashkey') == '') {
            $notice = __('The CardGate payment methods will only be visible in the WooCommerce Plugin, once the Site ID and Hashkey have been filled in.', 'cardgate');
        }
        cardgate::get_config_html($icon_file, $notice);
    }

    static function get_config_html($icon_file, $notice){
        $action_url = $_SERVER['REQUEST_URI'];
        ?>
        <div class="wrap">
            <form name="frmCardgate" action="<?php echo $action_url ?>" method="post">
            <?php wp_nonce_field('action854', 'nonce134')?>
            <img style="max-width:100px;" src="<?php echo $icon_file ?>" />
            <b>Version <?php echo get_option('cardgate_version') ?></b>
                <h2> <?php echo __('CardGate Settings', 'cardgate')?></h2>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">&nbsp</th>
                            <td colspan="2">&nbsp</td>
                    </tr>
                    <tr>
                        <th scope="row">
                        <label for="cgp_mode"><?php echo __('Mode', 'cardgate') ?></label>
                        </th>
                        <td>
                                <select style="width:60px;" id="cgp_mode" name="cgp_mode">
                                    <option value="1" <?php echo ( get_option('cgp_mode') == '1' ? ('selected="selected"') : '') ?>>Test</option>
                                    <option value="0" <?php echo ( get_option('cgp_mode') == '0' ? ('selected="selected"') : '') ?>>Live</option>
                                </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                        <label for="cgp_siteid">Site ID</label>
                        </th>
                        <td><input type="text" style="width:60px;" id="cgp_siteid" name="cgp_siteid" value=" <?php echo get_option('cgp_siteid') ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                        <label for="cgp_hashkey"><?php echo __('Hash key', 'cardgate') ?></label>
                        </th>
                        <td><input type="text" style="width:150px;" id="cgp_hashkey" name="cgp_hashkey" value="<?php echo get_option('cgp_hashkey')?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                        <label for="cgp_merchant_id">Merchant ID</label>
                        </th>
                        <td><input type="text" style="width:60px;" id="cgp_merchant_id" name="cgp_merchant_id" value="<?php echo get_option('cgp_merchant_id') ?> "/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                        <label for="cgp_merchant_api_key"><?php echo __('API key', 'cardgate') ?></label>
                        </th>
                        <td><input type="password" style="width:600px;" id="cgp_merchant_api_key" name="cgp_merchant_api_key" value="<?php echo get_option('cgp_merchant_api_key') ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                        <label for="cgp_checkoutdisplay"><?php echo __('Checkout display', 'cardgate') ?></label>
                        </th>
                        <td>
                                <select style="width:140px;" id="cgp_checkoutdisplay" name="cgp_checkoutdisplay">
                                    <option value="withoutlogo"<?php echo (get_option('cgp_checkoutdisplay') == 'withoutlogo' ? ('selected="selected"') : '') ?> > <?php echo __('Without Logo','cardgate')?></option>
                                    <option value="withlogo"<?php echo (get_option('cgp_checkoutdisplay') == 'withlogo' ? ('selected="selected"') : '') ?> > <?php echo __('With Logo','cardgate') ?></option>
                                </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php sprintf('%s <b>%s</b> %s <a href="https://my.cardgate.com/">%s </a> &nbsp %s <a href="https://github.com/cardgate/woocommerce/blob/master/%s" target="_blank"> %s</a> %s.'
            , __('Use the ','cardgate'),  __('Settings button', 'cardgate'), __('in your','cardgate'), __('My CardGate','cardgate'), __('to set these values, as explained in the','cardgate'),__('README.md','cardgate'), __('installation instructions','cardgate'), __('of this plugin','cardgate'))?></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo __('These settings apply to all CardGate payment methods used in the WooCommerce plugin.', 'cardgate') ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="height=60px;">&nbsp</td>
                    </tr>
                    <tr>
                        <td colspan="2"><b><?php echo $notice ?></b></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php submit_button(__('Save Changes'), 'primary', 'Submit', false); ?>
                    </tr>
                    </tbody>
                </table>
            </form>
        </div>
    <?php }

    // //////////////////////////////////////////////
    
    /**
     * Generate the payment table
     */
    static function cardgate_payments_table() {
	    require_once 'classes/Cardgate_PaymentsListTable.php';
        global $wp_list_table;
        $wp_list_table = new Cardgate_PaymentsListTable();
        $icon_file = plugins_url('images/cardgate.png', __FILE__);
        $wp_list_table->prepare_items();
        ?>
<div class="wrap">
            <div><?php echo '<img style="max-width:100px;" src="' . $icon_file . '" />&nbsp;' ?></div>
	        <h2>
                <?php echo __('CardGate Payments','cardgate') ?>
            </h2>

            <?php $wp_list_table->views(); ?>

            <form method="post" action="">
                <?php $wp_list_table->search_box( __('Search Payments','cardgate'), 'payment' ); ?>

                <?php $wp_list_table->display(); ?>
            </form>

	<br class="clear" />
</div>
<?php
    }

    // ////////////////////////////////////////////////
    
    /**
     * Create the admin menu
     *
     * @param array $menus            
     */
    public static function CGPAdminMenu() {
        add_menu_page('cardgate', $menuTitle = 'CardGate', $capability = 'manage_options', $menuSlug = 'cardgate_menu', $function = array(
            __CLASS__, 'cardgate_config_page' ), $iconUrl = plugins_url('cardgate/images/cgp_icon-16x16.png'));
        
        add_submenu_page($parentSlug = 'cardgate_menu', $pageTitle = __('Settings', 'cardgate'), $menuTitle = __('Settings', 'cardgate'), $capability = 'manage_options', $menuSlug = 'cardgate_menu', $function = array(
            __CLASS__, 'cardgate_config_page' ));
    }

    // ////////////////////////////////////////////////
    
    /**
     * Check whether a page is published and available
     */
    function page_is_published($id) {
        global $wpdb;
        $status = $wpdb->get_var($wpdb->prepare("SELECT post_status FROM " . $wpdb->prefix . 'posts WHERE ID=%d', $id));
        if ($status == 'publish') {
            return true;
        } else {
            return false;
        }
    }

    // ////////////////////////////////////////////////
    
    /**
     * Perfrom Hashcheck authentication
     * Return Boolean
     */
    private function hashCheck($data, $hashKey, $testMode) {

        try {

            $iMerchantId = (int) (get_option('cgp_merchant_id') ? get_option('cgp_merchant_id') : 0);
            $sMerchantApiKey = (get_option('cgp_merchant_api_key') ? get_option('cgp_merchant_api_key') : 0);
            
            $oCardGate = new cardgate\api\Client($iMerchantId, $sMerchantApiKey, $testMode);
            $oCardGate->setIp($_SERVER['REMOTE_ADDR']);
            
            if (FALSE == $oCardGate->transactions()->verifyCallback($data, $hashKey)) {
                return FALSE;
            } else {
                return TRUE;
            }
        } catch (cardgate\api\Exception $oException_) {
            return FALSE;
        }
    }

    // ////////////////////////////////////////////////
    
    /**
     * Handle callback from payment gateway
     */
    function cardgate_callback() {
        global $wpdb;
        global $woocommerce;
        
        if (! empty($_REQUEST['cgp_sitesetup']) && ! empty($_REQUEST['token'])) {

            try {

	            $sVersion = ( $this->get_woocommerce_version() == '' ? 'unkown' : $this->get_woocommerce_version() );
	            $sLanguage = substr( get_locale(), 0, 2 );
                $bIsTest = ($_REQUEST['testmode'] == 1 ? true : false);
                $iMerchantId = (int)(get_option('cgp_merchant_id')== false ? 0 : get_option('cgp_merchant_id'));
                $sMerchantApiKey = (get_option('cgp_merchant_api_key')== false ? 'initconfig' : get_option('cgp_merchant_api_key'));
	            $oCardGate = new cardgate\api\Client( $iMerchantId, $sMerchantApiKey, $bIsTest );
	            $oCardGate->setIp( $_SERVER['REMOTE_ADDR'] );
	            $oCardGate->setLanguage( $sLanguage );
	            $oCardGate->version()->setPlatformName( 'Woocommerce' );
	            $oCardGate->version()->setPlatformVersion( $sVersion );
	            $oCardGate->version()->setPluginName( 'CardGate' );
	            $oCardGate->version()->setPluginVersion( get_option( 'cardgate_version' ) );
	            $aResult = $oCardGate->pullConfig($_REQUEST['token']);
	            if (isset($aResult['success']) && $aResult['success'] == 1){
		            $aConfigData = $aResult['pullconfig']['content'];
		            update_option('cgp_mode', $aConfigData['testmode']);
		            update_option('cgp_siteid', $aConfigData['site_id']);
		            update_option('cgp_hashkey', $aConfigData['site_key']);
		            update_option('cgp_merchant_id', $aConfigData['merchant_id']);
		            update_option('cgp_merchant_api_key', $aConfigData['api_key']);
		            die ($aConfigData['merchant'] . '.' . get_option('cgp_siteid') . '.200');
                } else {
	                die('Token retrieval failed.');
                }
            } catch (cardgate\api\Exception $oException_) {
                die(htmlspecialchars($oException_->getMessage()));
            }
        }
        
        // check that the callback came from CardGate
        if (isset($_GET['cgp_notify']) && $_GET['cgp_notify'] == 'true' && empty($_REQUEST['cgp_sitesetup'])) {
            // hash check
            $bIsTest = (get_option('cgp_mode') == 1 ? true : false);
            if (! $this->hashCheck($_REQUEST, get_option('cgp_hashkey'), $bIsTest)) {
                exit('HashCheck failed.');
            }

            $sOrderNo = (int) substr($_REQUEST['reference'], 11);

            // process order
            $order        = new WC_Order($sOrderNo);
            $sOrderStatus = $order->get_status();
            
            if (($sOrderStatus != 'processing' && $sOrderStatus != 'completed')) {
                if ($_REQUEST['code'] >= '200' && $_REQUEST['code'] < '300') {
                    $order->set_transaction_id( $_REQUEST['transaction'] );
                    $order->payment_complete();
                }
                
                if ($_REQUEST['code'] == '0') {
                    $sReturnStatus = 'pending';
                }
                if ($_REQUEST['code'] >= '200' && $_REQUEST['code'] < '300') {
                    $sReturnStatus = 'completed';
                }
                if ($_REQUEST['code'] >= '300' && $_REQUEST['code'] < '400') {
                    $order->update_status('failed');
                    $sReturnStatus = 'failed';
                }
                if ($_REQUEST['code'] >= '700' && $_REQUEST['code'] < '800') {
	                $order->update_status('on-hold');
                    $sReturnStatus = 'waiting';
                }
                
                $order->add_order_note('Curo transaction (' . $_REQUEST['transaction'] . ') payment ' . $sReturnStatus . '.');
                exit($_REQUEST['transaction'] . '.' . $_REQUEST['code']);
            } else {
                exit('payment already processed');
            }
        }
    }

    // ////////////////////////////////////////////////
    function capture_payment_failed() {
        if ($_REQUEST['cancel_order'] == TRUE || $_REQUEST['cancel_order'] == 'true' && strpos($_REQUEST['transaction'], 'T') && $_REQUEST['status'] == 'failure') {
            wc_clear_notices();
            wc_add_notice(__('Your payment has failed. Please choose an other payment method.', 'cardgate'), 'error');
        }
        return TRUE;
    }

    // ////////////////////////////////////////////////
    
    /**
     * Create form to create specific CardGate pages for error, response, and complete status
     *
     * @param array $pages            
     * @param string $namePrefix            
     * @param integer $level            
     */
    function cardgate_pages($pages, $namePrefix, $level = 0) {
        ?>
<ul style="padding-left: <?php echo $level * 25; ?>px">

            <?php foreach ( $pages as $i => $page ): ?>

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
            if (isset($page['children'])) {
                cardgate::cardgate_pages($page['children'], $name . '[children]', $level + 1);
            }
            ?>
                </li>

            <?php endforeach; ?>

        </ul>
<?php
    }

    // ////////////////////////////////////////////////
    
    /**
     * Create Wordpresss Cardgate pages for error, response, and complete status
     *
     * @param array $pages            
     * @param array $parent            
     */
    function cardgate_create_pages($pages, $parent = null) {
        $i = 0;
        foreach ($pages as $page) {
            $post = array(
                'post_title' => $page['post_title'],
                'post_name' => $page['post_name'],
                'post_content' => $page['post_content'],
                'post_status' => 'publish',
                'post_type' => 'page',
                'comment_status' => 'closed'
            );
            
            if (isset($parent)) {
                $i ++;
                $post['post_parent'] = $parent;
            }
            
            $result = wp_insert_post($post, true);
            switch ($i) {
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
            if ($i > 0) {
                update_option($option, $result);
            }
            if (! is_wp_error($result)) {
                if (isset($page['children'])) {
                    cardgate::cardgate_create_pages($page['children'], $result);
                }
            }
        }
    }

    function get_woocommerce_version() {
        if (! function_exists('get_plugins'))
            require_once (ABSPATH . 'wp-admin/includes/plugin.php');
        $plugin_folder = get_plugins('/woocommerce');
        $plugin_file = 'woocommerce.php';
        return $plugin_folder[$plugin_file]['Version'];
    }

    static function plugin_get_version() {
        if (! function_exists('get_plugins'))
            require_once (ABSPATH . 'wp-admin/includes/plugin.php');
        $plugin_folder = get_plugins('/' . plugin_basename(dirname(__FILE__)));
        $plugin_file = basename((__FILE__));
        return $plugin_folder[$plugin_file]['Version'];
    }

    function initiate_payment_classes() {
        add_filter('woocommerce_payment_gateways', array( $this, 'woocommerce_cardgate_add_gateways'));
    }

    function woocommerce_cardgate_add_gateways($methods) {
        foreach($this->payment_names as $payment_name){
            $methods[] = 'WC_Cardgate'.$payment_name;
        }
        return $methods;
    }

    function add_cgform_fields() {
        global $woocommerce;
        
        // Get current tab/section
        $current_tab = (empty($_GET['tab'])) ? '' : sanitize_text_field(urldecode($_GET['tab']));
        $current_section = (empty($_REQUEST['section'])) ? '' : sanitize_text_field(urldecode($_REQUEST['section']));
        
        $pos = strpos($current_section, 'cardgate') === false;
        if ($current_tab == 'checkout' && $current_section != '' && (! $pos)) {
            $gateways = $woocommerce->payment_gateways->payment_gateways();
            
            foreach ($gateways as $gateway) {
                if ((strtolower(get_class($gateway)) == 'wc_' . $current_section) || (strtolower(get_class($gateway)) == $current_section)) {
                    $current_gateway = $gateway->id;
                    $extra_charges_id = 'woocommerce_' . $current_gateway . '_extra_charges';
                    $extra_charges_type = $extra_charges_id . '_type';
                    $extra_charges_label = $extra_charges_id . '_label';
                    if (isset($_REQUEST['save'])) {
                        update_option($extra_charges_id, $_REQUEST[$extra_charges_id]);
                        update_option($extra_charges_type, $_REQUEST[$extra_charges_type]);
                        
                        update_option($extra_charges_label, $_REQUEST[$extra_charges_label]);
                    }
                    $extra_charges = get_option($extra_charges_id);
                    $extra_charges_cust = get_option($extra_charges_label);
                    $extra_charges_type_value = get_option($extra_charges_type);
                }
            }
            
            ?>
<script>
                jQuery(document).ready(function($){
                    $data = '<h3><?php echo __('Add Extra Fees','cardgate');?></h3><table class="form-table">';
                    $data += '<tr vertical-align="top">';
                    $data += '<th scope="row" class="titledesc"><?php echo __('Extra Fee','cardgate');?></th>';
                    $data += '<td class="forminp">';
                    $data += '<fieldset>';
                    $data += '<input style="" name="<?php echo $extra_charges_id?>" id="<?php echo $extra_charges_id?>" type="text" value="<?php echo $extra_charges?>"/>';
                    $data += '<br /></fieldset></td></tr>';
    
                    $data += '<tr vertical-align="top">';
                    $data += '<th scope="row" class="titledesc"><?php echo __('Label for Extra Fee','cardgate');?></th>';
                    $data += '<td class="forminp">';
                    $data += '<fieldset>';
                    $data += '<input style="" name="<?php echo $extra_charges_label?>" id="<?php echo $extra_charges_label?>" type="text" value="<?php echo $extra_charges_cust?>" placeholder="<?php echo __('My Custom Label','cardgate');?>"/>';
                    $data += '<br /></fieldset></td></tr>';
                    $data += '<tr vertical-align="top">';
                    $data += '<th scope="row" class="titledesc"><?php echo __('Fee type','cardgate');?></th>';
                    $data += '<td class="forminp">';
                    $data += '<fieldset>';
                    $data += '<select name="<?php echo $extra_charges_type?>"><option <?php if($extra_charges_type_value=="add") echo "selected=selected"?> value="add"><?php echo __('Add Fee to Total','cardgate');?></option>';
                    $data += '<option <?php if($extra_charges_type_value=="percentage") echo "selected=selected"?> value="percentage"><?php echo __('Percentage of Total','cardgate');?></option>';
                    $data += '<br /></fieldset></td></tr></table>';
                    $('.form-table:last').after($data);
    
                });
    </script>
<?php
        }
    }
    public function calculate_fees($totals) {
        global $woocommerce;

        $woocommerce->session->extra_cart_fee = 0;
        $available_gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
        $current_gateway = '';
        if (! empty($available_gateways)) {
            // Chosen Method
            if (isset($woocommerce->session->chosen_payment_method) && isset($available_gateways[$woocommerce->session->chosen_payment_method])) {
                $current_gateway = $available_gateways[$woocommerce->session->chosen_payment_method];
            } elseif (isset($available_gateways[get_option('woocommerce_default_gateway')])) {
                $current_gateway = $available_gateways[get_option('woocommerce_default_gateway')];
            } else {
                $current_gateway = current($available_gateways);
            }
        }

        if ($current_gateway != '') {
            $current_gateway_id = $current_gateway->id;
            if(strpos($current_gateway_id, 'cardgate') === false){
                return $totals;
            }
            $extra_charges_id = 'woocommerce_' . $current_gateway_id . '_extra_charges';
            $extra_charges_type = $extra_charges_id . '_type';
            $extra_charges_cust = $extra_charges_id . '_label';
            $extra_charges = (float) get_option($extra_charges_id);
            $extra_charges_type_value = get_option($extra_charges_type);
            $extra_charges_label_value = get_option($extra_charges_cust);
            if ($extra_charges) {
                if ($extra_charges_type_value == "percentage") {
                    $decimal_sep = wp_specialchars_decode(stripslashes(get_option('woocommerce_price_decimal_sep')), ENT_QUOTES);
                    $thousands_sep = wp_specialchars_decode(stripslashes(get_option('woocommerce_price_thousand_sep')), ENT_QUOTES);
                    
                    $t1 = ($totals->cart_contents_total * $extra_charges) / 100;
                    $t3 = ($totals->cart_contents_total * 0.1) / 100;
                } else {
                    $t1 = $extra_charges;
                }
                
                $this->current_gateway_title = $current_gateway->settings['title'];
                $this->current_gateway_extra_charges = $extra_charges;
                $this->current_gateway_extra_charges_type_value = $extra_charges_type_value;
                
                $t5 = ($extra_charges_type_value == "percentage" ? $extra_charges . '%' : 'Fixed');
                
                if (isset($extra_charges_label_value) && strlen($extra_charges_label_value) > 2) {
                    $t6 = $extra_charges_label_value . ' - ';
                } else {
                    $t6 = $this->current_gateway_title . '  Extra Charges -  ';
                }

                $woocommerce->cart->add_fee(__($t6 . $t5), $t1);
                $woocommerce->session->extra_cart_fee = $t1;
            }
        }
        return $totals;
    }
    public function is_ajax_block_update($post){
        return ( isset( $post['action'] ) && $post['action'] == 'wp_ajax_cardgate_checkout_fees' ) ? true : false;
    }

    function load_cg_script() {
        wp_enqueue_script('wc-add-extra-charges', $this->plugin_url . '/assets/app.js', array(
            'wc-checkout'
        ), false, true);
    }

    function woocommerce_cardgate_blocks_support(){
	    // Check if the required class exists
	    if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		    return;
	    }

	    // Include the custom Blocks Checkout class

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
		    function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
                foreach ($this->payment_names as $name){
                    $blockmethod = $name.'Cardgate';
                    $payment_method_registry->register( new $blockmethod() );
                };
		    }
	    );
    }
    public function set_plugin_url() {
        $this->plugin_url = untrailingslashit(plugins_url('/', __FILE__));
    }

    private function get_methods($iSiteId, $iMerchantId, $sMerchantApiKey, $bIsTest) {
        try {

            $oCardGate = new cardgate\api\Client($iMerchantId, $sMerchantApiKey, $bIsTest);
            $oCardGate->setIp($_SERVER['REMOTE_ADDR']);
            
            $oMethods = $oCardGate->methods()->all($iSiteId);
        } catch (cardgate\api\Exception $oException_) {
            $oMethods[0] = [
                'id' => 0,
                'name' => htmlspecialchars($oException_->getMessage())
            ];
        }
        return $oMethods;
    }

    function my_error_notice() {
        ?>
<div class="error notice">
	<p>
		<b>CardGate: </b> <?php echo sprintf('%s <b>%s</b> %s <a href="https://my.cardgate.com/">%s </a> &nbsp %s <a href="https://github.com/cardgate/woocommerce/blob/master/%s" target="_blank"> %s</a> %s.'
						    , __('Use the ','cardgate'),__('Settings button', 'cardgate'), __('in your','cardgate'), __('My CardGate','cardgate'), __('to set these values, as explained in the','cardgate'),__('README.md','cardgate'), __('installation instructions','cardgate'), __('of this plugin','cardgate')) ?></p>
</div>
<?php
    }

    function cardgate_settings() {
        if (! get_option('cgp_siteid') || get_option('cgp_siteid') == '')
            return false;
        if (! get_option('cgp_hashkey') || get_option('cgp_hashkey') == '')
            return false;
        if (! get_option('cgp_merchant_id') || get_option('cgp_merchant_id') == '')
            return false;
        if (! get_option('cgp_merchant_api_key') || get_option('cgp_merchant_api_key') == '')
            return false;
        return true;
    }
}

new cardgate();
?>
