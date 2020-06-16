<?php

/**
 * Title: WooCommerce Cardgate OnlineÜberweisen gateway
 * Description:
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class WC_CardgateOnlineueberweisen extends CGP_Common_Gateway {

	var $id = 'cardgateonlineueberweisen';
	var $title = '';
	var $method_title = 'Cardgate OnlineÜberweisen';
	var $admin_title = 'Cardgate OnlineÜberweisen';
	var $payment_name = 'OnlineÜberweisen';
	var $payment_method = 'onlineueberweisen';
	var $company = 'CardGate';
	var $has_fields = false; //extra field for bank data

	public function __construct() {
		parent::__construct();
	}
}