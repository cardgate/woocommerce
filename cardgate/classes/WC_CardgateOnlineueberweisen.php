<?php

/**
 * Title: WooCommerce Cardgate OnlineÜberweisen gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 *
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateOnlineueberweisen extends CGP_Common_Gateway {

	public $id             = 'cardgateonlineueberweisen';
	public $title          = '';
	public $method_title   = 'Cardgate OnlineÜberweisen';
	public $admin_title    = 'Cardgate OnlineÜberweisen';
	public $payment_name   = 'OnlineÜberweisen';
	public $payment_method = 'onlineueberweisen';
	public $company        = 'CardGate';
	public $supports    = array( 'products', 'refunds' );
	public $has_fields     = false; // extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}
