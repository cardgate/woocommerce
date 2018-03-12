![CardGate](https://cdn.curopayments.net/thumb/200/logos/cardgate.png)

# CardGate module for WooCommerce

[![Total Downloads](https://img.shields.io/packagist/dt/cardgate/woocommerce.svg)](https://packagist.org/packages/cardgate/woocommerce)
[![Latest Version](https://img.shields.io/packagist/v/cardgate/woocommerce.svg)](https://github.com/cardgate/woocommerce/releases)
[![Build Status](https://travis-ci.org/cardgate/woocommerce.svg?branch=master)](https://travis-ci.org/cardgate/woocommerce)

## Support

This plugin supports WooCommerce version **3.X**.

## Preparation

The usage of this module requires that you have obtained CardGate RESTful API credentials.
Please visit [My Cardgate](https://my.cardgate.com/) and retrieve your RESTful API username and password, or contact your accountmanager.

## Installation

1. Go to your **WordPress admin**.

2. Uninstall and **delete** the CardGate plug-in.

3. Install the new plug-in like a **first installation**.

4. The previous settings will be left unchanged.
   **Note:** When you update from **Woocommerce 2.x** to **Woocommerce 3.x**,  
   you must also fill in the **Merchant ID** and the **Merchant API key** in your CardGate plugin settings.

5. For the settings of the plug-in:  
   Go to [My Cardgate](https://my.cardgate.com/)  
   **N.B.** For the **Testmode settings** click your **user avatar** on the **top right** and choose **To Staging**.  
   Go to **Management, Sites** and click on the ID of the site you wish to set.  
   Under **Connection to the website**, click on the **Setup your webshop** button, choose your plug-in type, and send the data to your website

## First Installation

1. This plug-in assumes that **WordPress version 4.4  or higher** is already installed,  
   and **WooCommerce plug-in 3.x or higher**.

2. Go to your **WordPress admin**, select **plug-ins**, and then **Add plug-in**.

3. Fill **cardgate** into the **search field.**

4. Click on **Install now** and then **Activate**

5. For the settings of the plug-in:  
   Go to [My Cardgate](https://my.cardgate.com/)  
   **N.B.** For the **Testmode settings** click your **user avatar** on the **top right** and choose **To Staging**.  
   Go to **Management, Sites** and click on the ID of the site you wish to set.  
   Under **Connection to the website**, click on the **Setup your webshop** button, choose your plug-in type, and send the data to your website

## Configuration

This plug-in assumes that **WordPress version 4.4  or higher** is already installed,
and **WooCommerce plug-in 3.x or higher**.

1. Go to your **WordPress admin**, select **plug-ins**, and then **Add plug-in**.

2. Fill **cardgate** into the **search field**.

3. Click on **Install now** and then **Activate**. 

4. For the settings of the plug-in:
   Go to [My Cardgate](https://my.cardgate.com/) 
   **N.B.** For the **Testmode settings** click your **user avatar** on the **top right** and choose **To Staging**.
   Go to **Management, Sites** and click on the **ID** of the site you wish to set.
   Under **Connection to the website**, click on the **Setup your webshop** button,  
   choose your plug-in type, and send the data to your website

5. On the left side of your WordPress admin, select **CardGate, Settings**.

6. The settings should now be visible here, and can be changed manually if you wish to do so.

7. In the **admin** select **WooCommerce, Settings, Checkout**.
   Here you see the CardGate payment methods.
   **Attention**: The CardGate payment methods are only visible in WooCommerce,  
   if the Site ID and Hash Key were entered correctly.

8. Select a **payment method** and set it correctly.
   Repeat this **for every payment method** you wish to activate.

9. When you are **finished testing** go to **CardGate settings** and switch from **Test mode** to **Live mode** and save it (**Save**).

10. **N.B.** The settings for **Live mode** can differ from those of **Test mode**, See also point number 4.

## Requirements

No further requirements.
