![CardGate](https://cdn.curopayments.net/thumb/200/logos/cardgate.png)

# CardGate module for WooCommerce

[![Build Status](https://travis-ci.org/cardgate/woocommerce.svg?branch=master)](https://travis-ci.org/cardgate/woocommerce)

## Support

This plugin supports WooCommerce version **3.x** - **7.x**

## Preparation

The usage of this module requires that you have obtained CardGate RESTful API credentials.  
Please visit [My CardGate](https://my.cardgate.com/) and retrieve your credentials, or contact your accountmanager.

## Installation

1. Go to your **WordPress admin**.

2. Uninstall and **delete** the CardGate plug-in.

3. Install the new plug-in like a **first installation**.

4. The previous settings will be left unchanged.
   **Note:** When you update from **Woocommerce 2.x** to **Woocommerce 3.x**,  
   you must also fill in the **Merchant ID** and the **API key** in your CardGate plugin settings.

5. For the settings of the plug-in:  
   Go to [My CardGate](https://my.cardgate.com/).  
   **N.B.** For the **Testmode settings** click your **user avatar** on the **top right** and choose **To Staging**.  
   Go to **Management, Sites** and click on the ID of the site you wish to set.  
   Under **Connection to the website**, click on the **Setup your webshop** button, choose your plug-in type, and send the data to your website.

## First Installation

1. This plug-in assumes that **WordPress version 4.4  or higher** is already installed,  
   and **WooCommerce plug-in 3.x or higher**.

2. Go to your **WordPress admin**, select **plug-ins**, and then **Add plug-in**.

3. Fill **cardgate** into the **search field**.

4. Click on **Install now** and then **Activate**.

5. For the settings of the plug-in:  
   Go to [My CardGate](https://my.cardgate.com/).  
   **N.B.** For the **Testmode settings** click your **user avatar** on the **top right** and choose **To Staging**.  
   Go to **Management, Sites** and click on the ID of the site you wish to set.  
   Under **Connection to the website**, click on the **Setup your webshop** button, choose your plug-in type, and send the data to your website.

## Configuration

This plug-in assumes that **WordPress version 4.4  or higher** is already installed,
and **WooCommerce plug-in 3.x or higher**.

1. Go to your **WordPress admin**, select **plug-ins**, and then **Add plug-in**.

2. Fill **cardgate** into the **search field**.

3. Click on **Install now** and then **Activate**. 

4. For the settings of the plug-in:
   Go to [My CardGate](https://my.cardgate.com/)  
   **N.B.** For the **Testmode settings** click your **user avatar** on the **top right** and choose **To Staging**.  
   Go to **Management, Sites** and click on the **ID** of the site you wish to set.  
   Under **Connection to the website**, click on the **Setup your webshop** button,  
   choose your plug-in type, and send the data to your website.

5. On the left side of your WordPress admin, select **CardGate, Settings**.

6. The settings should now be visible here, and can be changed manually if you wish to do so.

7. In the **admin** select **WooCommerce, Settings, Checkout**.
   Here you see the CardGate payment methods.  
   **Attention**: The CardGate payment methods are only visible in WooCommerce,  
   if the site ID and hash key were entered correctly.

8. Select a **payment method** and set it correctly.
   Repeat this **for every payment method** you wish to activate.

9. When you are **finished testing** go to **CardGate settings** and switch from **Test mode** to **Live mode** and save it (**Save**).

10. **N.B.** The settings for **Live mode** can differ from those of **Test mode**, See also point number 4.

## Subscriptions

The CardGate plug-in supports **recurring payments** through the
[WooCommerce Subscriptions](https://github.com/pronamic/woocommerce-subscriptions) plug-in.
Subscription functionality is only active when **WooCommerce Subscriptions is installed and activated**;
without it the CardGate payment methods behave as regular one-off payment methods.

### Supported payment methods

Subscriptions are available for the following CardGate payment methods:

- **Creditcard**
- **iDEAL**
- **Bancontact**
- **SEPA Direct Debit**
- **Sofort / Online Ueberweisen**
- **Przelewy24**

All other CardGate payment methods can only be used for **one-off** orders; WooCommerce
Subscriptions does not offer them in the checkout when the cart contains a subscription product.

### Supported subscription options

For the payment methods listed above the plug-in supports:

- **Automatic renewals** – renewal orders are charged automatically by CardGate.
- **Cancellation**, **suspension** and **reactivation** of a subscription.
- **Amount changes** and **date changes** on an existing subscription.
- **Payment method changes** by the **customer** as well as by the **shop admin**.
- **Multiple subscriptions** in one order.

### How it works

1. On the **first (parent) payment** the transaction is flagged at CardGate as **recurring**.
   The customer authorizes the mandate/token during that payment.
2. The CardGate transaction ID of that first payment is stored on the order and on the
   subscription, and is used as the **parent transaction** for all following renewals.
3. When WooCommerce Subscriptions schedules a **renewal payment**, the plug-in queues a job in the
   **Action Scheduler** that ships with WooCommerce. The actual call to CardGate is done in the
   background, so a slow API call can never time out the checkout or the Subscriptions worker.
4. The renewal order is set to **on-hold** until the CardGate **callback (notify)** confirms the
   payment. Only then the order is set to **processing/completed**. When the recurring transaction
   cannot be created, or the callback reports a failure, the renewal order is set to **failed** and
   WooCommerce Subscriptions handles the retry.
5. When a customer changes the payment method of a subscription, a **zero-amount transaction** is
   created to re-authorize the mandate. The new transaction ID replaces the stored one.

### Requirements and notes for subscriptions

- **WooCommerce Subscriptions** must be installed and activated.
- **WP-Cron must be able to run**, because renewals are processed by the Action Scheduler, which is
  triggered by `wp-cron.php`. On low-traffic sites, or when `DISABLE_WP_CRON` is set, the plug-in
  triggers a throttled (max once per minute) loopback request to `wp-cron.php` so queued renewals are
  not left waiting. If you have disabled WP-Cron, make sure a **system cron** calls
  `wp-cron.php` (or `wp cron event run --due-now`) at least **every 5 minutes**.
- The **CardGate callback URL** must be publicly reachable; renewals are only completed after the
  callback has been received.
- Make sure recurring payments are **enabled for your site in [My CardGate](https://my.cardgate.com/)**
  for every payment method you want to use for subscriptions.
- Pending renewal jobs can be inspected under **WooCommerce, Status, Scheduled Actions**
  (group `cardgate`, action `cardgate_process_recurring_payment`). Deactivating the plug-in removes
  the pending CardGate renewal jobs.
- Test subscriptions in **Test mode** first; renewal dates can be shortened with the WooCommerce
  Subscriptions test settings.

## Requirements

No further requirements.
