<?php

/**
  Plugin Name: CardGate
  Plugin URI: http://cardgate.com
  Description: Integrates Cardgate Gateway for WooCommerce into WordPress
 
  Version: 3.0.1
  Requires at least: 3.0
 
  Author: CardGate
  Author URI: http://cardgate.com
 
  License: GPL
 */
class cardgate
{

    protected $_Lang = NULL;

    /**
     * Initialize plug-in
     */
    function __construct()
    {
        // Set up localisation.
        $this->load_plugin_textdomain();
        
        $this->current_gateway_title = '';
        $this->current_gateway_extra_charges = '';
        add_action('admin_head', array(
            $this,
            'add_cgform_fields'
        ));
        add_action('woocommerce_cart_calculate_fees', array(
            $this,
            'calculate_totals'
        ), 10, 1);
        add_action('wp_enqueue_scripts', array(
            $this,
            'load_cg_script'
        ));
        
        add_action('admin_menu', array(
            &$this,
            'CGPAdminMenu'
        ));
        add_action('init', array(
            &$this,
            'cardgate_callback'
        ), 20);
        
        register_activation_hook(__FILE__, array(
            &$this,
            'cardgate_install'
        )); // hook for install
        register_deactivation_hook(__FILE__, array(
            &$this,
            'cardgate_uninstall'
        )); // hook for uninstall
        update_option('cardgate_version', $this->plugin_get_version());
        add_action('plugins_loaded', array(
            &$this,
            'initiate_payment_classes'
        ));
        add_filter('woocommerce_subscriptions_list_table_pre_process_actions', array(
            &$this,
            'subscription_preaction'
        ), 20);
        update_option('is_callback_status_change', false);
        
        add_action('woocommerce_cancelled_order', array(
            &$this,
            'capture_payment_failed'
        ));
    }

    // ////////////////////////////////////////////////
    
    /**
     * Install plug-in
     */
    function cardgate_install()
    {
        global $wpdb;
        
        // check if we need to do an update
        $_Do_Update = false;
        $sCurrent_Version = get_option('cgp_version') ? get_option('cgp_version') : '0.0.0';
        $sLatest_Version = '2.1.13';
        
        if (! empty($sCurrent_Version)) {
            if (version_compare($sCurrent_Version, $sLatest_Version, '<') == true) {
                $_Do_Update = true;
            }
        }
        
        $sCharsetCollate = '';
        if (! empty($wpdb->charset)) {
            $sCharsetCollate = 'DEFAULT CHARACTER SET ' . $wpdb->charset;
        }
        if (! empty($wpdb->collate)) {
            $sCharsetCollate .= ' COLLATE ' . $wpdb->collate;
        }
        
        // Cardgate payments table
        $sTableName = $wpdb->prefix . 'cardgate_payments';
        
        // Do the create just in case the db does not exists
        $sCreate_Query = "CREATE TABLE IF NOT EXISTS $sTableName (
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
        dbDelta($sCreate_Query);
        
        if ($_Do_Update == true) {
            
            $qry = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, CHARACTER_MAXIMUM_LENGTH ";
            $qry .= "FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . $sTableName . "' ";
            $qry .= "AND table_schema = '" . DB_NAME . "'";
            $aRows = $wpdb->get_results($qry, ARRAY_A);
            $subscription_exists = false;
            $transaction_id_exists = false;
            $transaction_id_max_length = 0;
            $parent_order_id_exists = false;
            $parent_id_exists = false;
            
            foreach ($aRows as $aRow) {
                switch ($aRow['COLUMN_NAME']) {
                    case 'subscription_id':
                        $subscription_exists = true;
                        break;
                    case 'transaction_id':
                        $transaction_id_exists = true;
                        $transaction_id_max_length = $aRow['CHARACTER_MAXIMUM_LENGTH'];
                        break;
                    case 'parent_order_id':
                        $parent_order_id_exists = true;
                        break;
                    case 'parent_id':
                        $parent_id_exists = true;
                        break;
                }
            }
            
            if (! $parent_id_exists && ! $parent_order_id_exists) {
                $sUpdate_Query = "ALTER TABLE $sTableName ADD `parent_id` VARCHAR(16) NULL AFTER `order_id` ";
                $wpdb->query($sUpdate_Query);
            }
            if ($parent_order_id_exists && ! $parent_id_exists) {
                $sUpdate_Query = "ALTER TABLE $sTableName CHANGE parent_order_id parent_id varchar(16)";
                $wpdb->query($sUpdate_Query);
            }
            if (! $transaction_id_exists) {
                $sUpdate_Query = "ALTER TABLE $sTableName CHANGE session_id transaction_id varchar(16)";
                $wpdb->query($sUpdate_Query);
            }
            if ($transaction_id_max_length != 16) {
                $sUpdate_Query = "ALTER TABLE $sTableName CHANGE transaction_id transaction_id varchar(16)";
                $wpdb->query($sUpdate_Query);
            }
            if (! $subscription_exists) {
                $sUpdate_Query = "ALTER TABLE $sTableName ADD subscription_id VARCHAR(16) NULL AFTER transaction_id ";
                $wpdb->query($sUpdate_Query);
            }
            update_option('cgp_version', $this->plugin_get_version());
        }
    }

    // ////////////////////////////////////////////////
    
    /**
     * Unistall plug-in
     */
    function cardgate_uninstall()
    {
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
    public function load_plugin_textdomain()
    {
        $locale = apply_filters('plugin_locale', get_locale(), 'cardgate');
        
        // load_textdomain( 'woocommerce', WP_LANG_DIR . '/woocommerce/woocommerce-' . $locale . '.mo' );
        load_plugin_textdomain('cardgate', false, plugin_basename(dirname(__FILE__)) . '/i18n/languages');
    }

    // //////////////////////////////////////////////
    
    /**
     * Configuration page
     */
    static function cardgate_config_page()
    {
        global $wpdb;
        
        $icon_file = plugins_url('images/cardgate.png', __FILE__);
        
        if (isset($_POST['Submit'])) {
            if (empty($_POST) || ! wp_verify_nonce($_POST['nonce134'], 'action854')) {
                print 'Sorry, your nonce did not verify.';
                exit();
            } else {
                // process form data
                update_option('cgp_mode', $_POST['cgp_mode']);
                update_option('cgp_siteid', $_POST['cgp_siteid']);
                update_option('cgp_hashkey', $_POST['cgp_hashkey']);
                update_option('cgp_merchant_id', $_POST['cgp_merchant_id']);
                update_option('cgp_merchant_api_key', $_POST['cgp_merchant_api_key']);
            }
        }
        
        if (get_option('cgp_siteid') != '' && get_option('cgp_hashkey') != '') {
            $sNotice = '';
        } else {
            $sNotice = __('The CardGate payment methods will only be visible in the WooCommerce Plugin, once the Site ID and Hashkey have been filled in.', 'cardgate');
        }
        
        $sAction_url = $_SERVER['REQUEST_URI'];
        $sHtml = '<div class="wrap">
				<form name="frmCardgate" action="' . $sAction_url . '" method="post">';
        $sHtml .= wp_nonce_field('action854', 'nonce134');
        $sHtml .= '
				<h2>' . '<img src="' . $icon_file . '" />&nbsp;' . __('CardGate Settings', 'cardgate') . '</h2>
				<b>Version' . get_option('cardgate_version') . '</b>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"
						<td colspan="2">&nbsp</td>
					</tr>
					<tr>
						<th scope="row">
						<label for="cgp_mode">' . __('Mode', 'cardgate') . '</label>
						</th>
						<td>
								<select style="width:60px;" id="cgp_mode" name="cgp_mode">
									<option value="1"' . (get_option('cgp_mode') == '1' ? ('selected="selected"') : '') . '>Test</option>
									<option value="0"' . (get_option('cgp_mode') == '0' ? ('selected="selected"') : '') . '>Live</option>
								</select>
								<span class="description">
									<br>
									' . __('Live or Test mode of the plugin.', 'cardgate') . '
								</span>
						</td>
					</tr>
					<tr>
                        <th scope="row">
                        <label for="cgp_siteid">Site ID</label>
                        </th>
                        <td><input type="text" style="width:60px;" id="cgp_siteid" name="cgp_siteid" value="' . get_option('cgp_siteid') . '" />
                        <span class="description">
                                    <br>
                                    ' . __('The Site ID as created in your ', 'cardgate') . '<a href="https://my.cardgate.com/">merchant back-office.</a>
                                </span>
                        </td>
                    </tr>
					<tr>
						<th scope="row">
						<label for="cgp_hashkey">' . __('Hashkey', 'cardgate') . '</label>
						</th>
						<td><input type="text" style="width:150px;" id="cgp_hashkey" name="cgp_hashkey" value="' . get_option('cgp_hashkey') . '"/>
						<span class="description">
									<br>
									' . __('The Hashkey as entered in your ', 'cardgate') . ' <a href="https://my.cardgate.com/">merchant back-office.</a>
								</span>
						</td>
					</tr>
		            <tr>
						<th scope="row">
						<label for="cgp_merchant_id">Merchant ID</label>
						</th>
						<td><input type="text" style="width:60px;" id="cgp_merchant_id" name="cgp_merchant_id" value="' . get_option('cgp_merchant_id') . '"/>
						<span class="description">
									<br>
									' . __('De Merchant ID as created in your ', 'cardgate') . ' <a href="https://my.cardgate.com/">merchant back-office.</a>
								</span>
						</td>
					</tr>
				    <tr> 
						<th scope="row">
						<label for="cgp_merchant_api_key">' . __('Merchant API Key', 'cardgate') . '</label>
						</th>
						<td><input type="text" style="width:600px;" id="cgp_merchant_api_key" name="cgp_merchant_api_key" value="' . get_option('cgp_merchant_api_key') . '"/>
						<span class="description">
									<br>
									' . __('The Merchant API Key, provided by our acount manager ', 'cardgate') . '</a>
								</span>
						</td>
					</tr>
					<tr>
						<td colspan="2">' . __('These settings apply to all CardGate payment methods used in the WooCommerce plugin.', 'cardgate') . '</td>
					</tr>
					<tr>
						<td colspan="2" style="height=60px;">&nbsp</td>
					</tr>
					<tr>
						<td colspan="2"><b>' . $sNotice . '</b></td>
					</tr>
					<tr>
						<td colspan="2">';
        echo $sHtml;
        submit_button(__('Save Changes'), 'primary', 'Submit', false);
        echo '</td>
					</tr>
					</tbody>
				</table>
			</form>
			</div>';
    }

    // //////////////////////////////////////////////
    
    /**
     * Generate the payment table
     */
    static function cardgate_payments_table()
    {
        global $wp_list_table;
        $wp_list_table = new Cardgate_PaymentsListTable();
        $icon_file = plugins_url('images/cardgate.png', __FILE__);
        $wp_list_table->prepare_items();
        ?>
<div class="wrap">
	<h2>
                <?php '<img src="' . $icon_file . '" />&nbsp;' . __('CardGate Payments','cardgate') ?>
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
    public static function CGPAdminMenu()
    {
        
        add_menu_page('cardgate', $menuTitle = 'CardGate', $capability = 'manage_options', $menuSlug = 'cardgate_menu', $function = array(
            __CLASS__,
            'cardgate_config_page'
        ), $iconUrl = plugins_url('cardgate/images/cgp_icon-16x16.png'));
        
        add_submenu_page($parentSlug = 'cardgate_menu', $pageTitle = __('Settings', 'cardgate'), $menuTitle = __('Settings', 'cardgate'), $capability = 'manage_options', $menuSlug = 'cardgate_menu', $function = array(
            __CLASS__,
            'cardgate_config_page'
        ));
        
        add_submenu_page($parentSlug = 'cardgate_menu', $pageTitle = __('Payments Table', 'cardgate'), $menuTitle = __('Payments Table', 'cardgate'), $capability = 'manage_options', $menuSlug = 'cardgate_payments_table', $function = array(
            __CLASS__,
            'cardgate_payments_table'
        ));
        
        global $submenu;
    }

    // ////////////////////////////////////////////////
    
    /**
     * Check whether a page is published and available
     */
    function page_is_published($id)
    {
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
    private function hashCheck($data, $hashKey, $testMode)
    {
        require_once WP_PLUGIN_DIR . '/cardgate/cardgate-clientlib-php/init.php';
        try {
            
            require 'cardgate-clientlib-php/init.php';
            
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
    function cardgate_callback()
    {
        global $wpdb;
        global $woocommerce;
        
        if (! empty($_REQUEST['cgp_sitesetup']) && ! empty($_REQUEST['token'])) {
            
            try {
                require_once WP_PLUGIN_DIR . '/cardgate/cardgate-clientlib-php/init.php';
                    
                $bIsTest = ($_REQUEST['testmode'] == 1 ? true : false);
                $bIsTest = true;
                $aResult = cardgate\api\Client::pullConfig($_REQUEST['token'], $bIsTest);
                
                $aConfigData = $aResult['pullconfig']['content'];
                update_option('cgp_mode', $aConfigData['testmode']);
                update_option('cgp_siteid', $aConfigData['site_id']);
                update_option('cgp_hashkey', $aConfigData['site_key']);
                update_option('cgp_merchant_id', $aConfigData['merchant_id']);
                update_option('cgp_merchant_api_key', $aConfigData['api_key']);
            } catch (cardgate\api\Exception $oException_) {
                $aResult[0] = [
                    'id' => 'exception_message',
                    'name' => htmlspecialchars($oException_->getMessage())
                ];
            }

        }
        
        // check that the callback came from CardGate
        if (isset($_GET['cgp_notify']) && $_GET['cgp_notify'] == 'true' && empty($_REQUEST['cgp_sitesetup'])) {
            
            // hash check
            $bIsTest = (get_option('cgp_mode') == 1 ? true : false);
            if (! $this->hashCheck($_REQUEST, get_option('cgp_hashkey'), $bIsTest)) {
                exit('HashCheck failed.');
            }
            
            // Refurbish the ref so we get the orderno
            $sOrderType = substr($_REQUEST['reference'], 0, 1);
            $sOrderNo = substr($_REQUEST['reference'], 11);
            
            // check if payment is still pending
            $tableName = $wpdb->prefix . 'cardgate_payments';
            $sql = $wpdb->prepare("SELECT * FROM $tableName WHERE order_id=%d", $sOrderNo);
            
            // Cause we also have recurring we need all records
            $aDB_Row = $wpdb->get_results($sql, ARRAY_A);
            // Get the last record
            $aLastRow = end($aDB_Row);
            
            $sql = $wpdb->prepare("SELECT * FROM $tableName WHERE order_id=%d order by id desc ", $sOrderNo);
            $aDB_Row = $wpdb->get_results($sql, ARRAY_A);
            // Get the first record
            $aFirstRow = end($aDB_Row);
            if (is_null($aFirstRow['transaction_id'])) {
                $sParent_id = $_REQUEST['transaction'];
            } else {
                $sParent_id = $aFirstRow['transaction_id'];
            }
            
            // process order
            $order = new WC_Order($sOrderNo);
            
            $amount = $order->get_total() * 100;
            
            if (($order->get_status() != 'processing' && $order->get_status() != 'completed')) {
                if ($_REQUEST['code'] >= '200' && $_REQUEST['code'] < '300') {
                    $order->payment_complete();
                }
                // process order
                
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
                    $sReturnStatus = 'waiting';
                }
                
                $order->add_order_note('Curo transaction (' . $_REQUEST['transaction'] . ') payment ' . $sReturnStatus . '.');
                
                $sSubscription = (empty($_REQUEST['subscription']) ? 0 : $_REQUEST['subscription']);
                // update payment table
                if ($aLastRow['status'] == 'pending') {
                    $qry = $wpdb->prepare("UPDATE $tableName SET status='" . $sReturnStatus . "', transaction_id='%s', parent_id='%s', subscription_id='%s' WHERE id=%d", $_REQUEST['transaction'], $sParent_id, $sSubscription, $aLastRow['id']);
                    $wpdb->query($qry);
                }
                exit($_REQUEST['transaction'] . '.' . $_REQUEST['code']);
            } else {
                exit('payment already processed');
            }
        }
    }

    // ////////////////////////////////////////////////
    function capture_payment_failed()
    {
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
    function cardgate_pages($pages, $namePrefix, $level = 0)
    {
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
    function cardgate_create_pages($pages, $parent = null)
    {
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

    function get_woocommerce_version()
    {
        if (! function_exists('get_plugins'))
            require_once (ABSPATH . 'wp-admin/includes/plugin.php');
        $plugin_folder = get_plugins('/woocommerce');
        $plugin_file = 'woocommerce.php';
        return $plugin_folder[$plugin_file]['Version'];
    }

    static function plugin_get_version()
    {
        if (! function_exists('get_plugins'))
            require_once (ABSPATH . 'wp-admin/includes/plugin.php');
        $plugin_folder = get_plugins('/' . plugin_basename(dirname(__FILE__)));
        $plugin_file = basename((__FILE__));
        return $plugin_folder[$plugin_file]['Version'];
    }

    function initiate_payment_classes()
    {
        add_filter('woocommerce_payment_gateways', array(
            $this,
            'woocommerce_cardgate_add_gateways'
        ));
    }

    function woocommerce_cardgate_add_gateways($methods)
    {
        $methods[] = 'WC_CardgateAfterpay';
        $methods[] = 'WC_CardgateBancontact';
        $methods[] = 'WC_CardgateBanktransfer';
        $methods[] = 'WC_CardgateBitcoin';
        $methods[] = 'WC_CardgateCreditcard';
        $methods[] = 'WC_CardgateDirectDebit';
        $methods[] = 'WC_CardgateGiropay';
        $methods[] = 'WC_CardgateIdeal';
        $methods[] = 'WC_CardgateKlarna';
        $methods[] = 'WC_CardgatePayPal';
        $methods[] = 'WC_CardgatePrzelewy24';
        $methods[] = 'WC_CardgateSofortbanking';
        
        return $methods;
    }

    function subscription_preaction()
    {
        $custom_action = array(
            'custom_action' => false,
            'messages' => array(),
            'error_messages' => array()
        );
        
        if (empty($_GET['new_status'])) {
            return $custom_action;
        }
        
        if ($_GET['new_status'] != 'trash') {
            return $custom_action;
        }
        
        $subscriptions = array(
            $_GET['user'] => array(
                $_GET['subscription']
            )
        );
        
        foreach ($subscriptions as $user_id => $subscription_keys) {
            foreach ($subscription_keys as $subscription_key) {
                $subscription_data = explode('_', $subscription_key);
                $order_id = $subscription_data[0];
                $order = new WC_Order($order_id);
                
                $gateways = new WC_Payment_Gateways();
                foreach ($gateways->payment_gateways as $id => $gateway) {
                    if (substr($gateway->id, 0, 8) == 'cardgate' && $gateway->enabled == 'yes') {
                        $supports = $gateway->supports;
                        foreach ($supports as $support_id => $support_type) {
                            if ($support_type == 'subscriptions') {
                                $gateway->trash_subscription($order);
                                return $custom_action;
                            }
                        }
                    }
                }
            }
        }
        return $custom_action;
    }

    function add_cgform_fields()
    {
        global $woocommerce;
        
        // Get current tab/section
        $current_tab = (empty($_GET['tab'])) ? '' : sanitize_text_field(urldecode($_GET['tab']));
        $current_section = (empty($_REQUEST['section'])) ? '' : sanitize_text_field(urldecode($_REQUEST['section']));
        
        $pos = strpos($current_section, 'cardgate') === false;
        if ($current_tab == 'checkout' && $current_section != '' && (! $pos)) {
            $gateways = $woocommerce->payment_gateways->payment_gateways();
            
            foreach ($gateways as $gateway) {
                if ((strtolower(get_class($gateway)) == 'wc_' . $current_section)) {
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
                    $data += '<tr valign="top">';
                    $data += '<th scope="row" class="titledesc"><?php echo __('Extra Fee','cardgate');?></th>';
                    $data += '<td class="forminp">';
                    $data += '<fieldset>';
                    $data += '<input style="" name="<?php echo $extra_charges_id?>" id="<?php echo $extra_charges_id?>" type="text" value="<?php echo $extra_charges?>"/>';
                    $data += '<br /></fieldset></td></tr>';
    
                    $data += '<tr valign="top">';
                    $data += '<th scope="row" class="titledesc"><?php echo __('Label for Extra Fee','cardgate');?></th>';
                    $data += '<td class="forminp">';
                    $data += '<fieldset>';
                    $data += '<input style="" name="<?php echo $extra_charges_label?>" id="<?php echo $extra_charges_label?>" type="text" value="<?php echo $extra_charges_cust?>" placeholder="<?php echo __('My Custom Label','cardgate');?>"/>';
                    $data += '<br /></fieldset></td></tr>';
                    $data += '<tr valign="top">';
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

    public function calculate_totals($totals)
    {
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
                
                $this->current_gateway_title = $current_gateway->title;
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

    function load_cg_script()
    {
        wp_enqueue_script('wc-add-extra-charges', $this->plugin_url() . '/assets/app.js', array(
            'wc-checkout'
        ), false, true);
    }

    public function plugin_url()
    {
        return $this->plugin_url = untrailingslashit(plugins_url('/', __FILE__));
    }
}

// end class

$mp = new cardgate();

if (function_exists('spl_autoload_register')) :

    function cardgate_autoload($name)
    {
        $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $name . '.php';
        
        if (is_file($file)) {
            require_once $file;
        }
    }
    
    spl_autoload_register('cardgate_autoload');







endif;

?>
