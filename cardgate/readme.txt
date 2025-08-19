=== CardGate Payments for WooCommerce ===
Plugin Name: CardGate Payments for WooCommerce
Contributors: cardgate
Tags: CardGate, iDEAL, Creditcard, WooCommerce, Payment, Bancontact, SofortBanking, OverBoeking, PayPal, DirectDebit, Webmoney
Requires at least: 4.4
Tested up to: 6.8
Stable tag: 4.0.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

CardGate Payment methods for WooCommerce

== Description ==

This plug-in contains the following payment methods from CardGate for WooCommerce:

<ul>
<li>Afterpay</li>
<li>Bancontact</li>
<li>Banktransfer</li>
<li>Billink</li>
<li>Bitcoin</li>
<li>Creditcard</li>
<li>DirectDebit</li>
<li>Gift Card</li>
<li>iDEAL</li>
<li>iDEAL QR</li>
<li>Klarna</li>
<li>OnlineÜberweisen</li>
<li>PayPal</li>
<li>Paysafecard</li>
<li>Paysafecash</li>
<li>Przelewy24</li>
<li>Sofortbanking</li>
<li>SprayPay</li>
</ul>

== Installation ==

= Minimum Requirements =

* PHP version 5.6 or greater
* PHP extensions enabled: cURL
* WordPress 3.8 or greater
* WooCommerce 3.0.0 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of CardGate for WooCommerce, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “CardGate” and click Search Plugins. Once you’ve found our eCommerce plugin you can install it by simply clicking “Install Now”.

= Manual installation =

1. Upload the zip file with the Wordpress plug-in manager
2. Configure the plug-in using [CardGate Settings](https://www.cardgate.com/plug_in/woocommerce/?link=WooCommerce3x-1-manual)
3. Activate the desired payment methods in WooCommerce


To make use of this plug-in you need an account from www.cardgate.com

== Frequently Asked Questions ==

To make use of this plug-in you need an account from CardGate
Please contact us at www.cardgate.com
= I have updated to version 3.0.4 and now it doesn’t work anymore? =
If you are updating from an older version then 3.0.4 you will need new [CardGate Settings](https://www.cardgate.com/plug_in/woocommerce/?link=WooCommerce3x-1-manual), or the plugin will not work!

== Screenshots ==
1. The global CardGate settings, which you can configure using [CardGate Settings](https://www.cardgate.com/plug_in/woocommerce/?link=WooCommerce3x-1-manual)
2. The CardGate payment options in Woocommerce
3. Payment settings for a specific CardGate payment method.

== Changelog ==

= 4.0.1 =
* Removed: MisterCash payment method.

= 4.0.0 =
* Added: Payment method Crypto
* Fix: Double payment fee.
* Removed: Payment table.

= 3.2.6 =
* Added: Currency check

= 3.2.5 =
* Restrict payment methods by currency

= 3.2.4 =
* Update: Removed all issuer code

= 3.2.3 =
* Added: iDEAL issuer options

= 3.2.2 =
* Fix: Security issue

= 3.2.1 =
* Fix: Payments list

= 3.2.0 =
* Fix: Block checkout scripts

= 3.1.29 =
* Removed: Giropay

= 3.1.28 =
* Added: Woocommerce block compatibility

= 3.1.27 =
* Fix: HPOS compatibility

= 3.1.26 =
* Fix: deprecated calls

= 3.1.25 =
* Fix: deprecated dynamic properties

= 3.1.24 =
* Fix: on-hold status for pending payments

= 3.1.23 =
* Tested version updates

= 3.1.22 =
* Added: Instructions field
* Added: Discount items
* update: Client Lib

= 3.1.21 =
* New payment method: SprayPay

= 3.1.20 =
* Tested version updates

= 3.1.19 =
* Refund implementation

= 3.1.18 =
* Edit requirements

= 3.1.17 =
* Fix: Payment method title
* new admin logo

= 3.1.16 =
* New pullConfig method implementation

= 3.1.15 =
* New payment method: OnlineÜberweisen

= 3.1.14 =
* Check for valid bank issuers

= 3.1.13 =
* Fix: Text translation

= 3.1.12 =
* Fix: Missing Bank issuer

= 3.1.11 =
* New payment methods: Billink, Gift Card, Paysafecash
* Cache for Bank issuers. 

= 3.1.10 =
* New payment method: Paysafecard 

= 3.1.9 =
* Fix: Cart item tax rounding error 

= 3.1.8 =
* Fix: Cart item tax rounding error

= 3.1.7 =
* Updated customer phone number

= 3.1.6 =
* Updated Dutch translation text

= 3.1.5 =
* Updated iDEAL bank issuer text

= 3.1.4 =
* Updated consumer address data

= 3.1.3 =
* Updated ClientLib Library

= 3.1.2 =
* Address data no longer mandatory

= 3.1.1 =
* Error notice for incomplete CardGate settings

= 3.1.0 =
* Now compatible with all WooCommerce versions higher than 2.1.0

= 3.0.5 =
* Changed text of Settings page

= 3.0.4 =
* Plugin now available via wordpress.org

== Upgrade Notice ==

= 3.0.5 =
 NB: If you update from a CardGate 2.x version, you need new settings or the plugin will not work!
See: [CardGate Settings](https://www.cardgate.com/plug_in/woocommerce/?link=WooCommerce3x-1-manual)