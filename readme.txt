=== Duitku for GiveWP ===
Plugin Name: Duitku for GiveWP
Plugin URI:  https://docs.duitku.com/payment-gateway/plugin
Description: Duitku Payment Gateway
Version:     1.3.5
Author:      Duitku Development Team
Author URI:  https://www.duitku.com
Text Domain: give-Duitku
Domain Path: /languages
Contributors: anggiyawan@duitku.com, charisch09, rayhanduitku
Tags: duitku, indonesia, paymentgateways, donation, BCA, Mandiri, BRI, CIMB, BNI
Requires at least: 6.0.1
Tested up to: 6.7.2
Stable tag: 1.3.5
Requires PHP: 5.6
License: GPLv3
License URI: https://opensource.org/licenses/GPL-3.0

Duitku Add-on for Give

== Description ==
Easy accept E-wallet, and Various Bank Transfers to maximize your fundrising. Our Payment Gateway for GiveWP plugin integrates with your GiveWP Donations and lets you accept those Donations through our payment gateway.
Securely accept Donations. View and manage donations transfer from one convenient place – [your Duitku dashboard](https://sandbox.duitku.com/merchant/Project).

Supported Payment Channels :

1.	Credit Card Aggregator full-payment (Visa, Master, JCB)
2.	Credit Card Facilitator installment and full-payment (Visa, Master, JCB, AMEX)
3.	BCA KlikPay
4.	BCA Virtual Account
5.	Mandiri Virtual Account
6.	Permata Bank Virtual Account
7.	ATM Bersama
8.	CIMB Niaga Virtual Account
9.	BNI Virtual Account
10.	Maybank Virtual Account
11.	Retail (Alfamart,  Pegadaian and Pos Indonesia)
12.	OVO
13.	Shopee Pay
14.	Shopee Pay Apps
15.	Bank Artha Graha
16.	LinkAja Apps (Percentage Fee)
17.	LinkAja Apps (Fixed Fee)
18.	DANA
19.	LinkAja QRIS
20.	Indomaret
21.	PosPay
22.	BNC
23.	BRIVA
24.	QRIS by Nobu
25. Gudang Voucher QRIS
26. Jenius Pay
27. Danamon Virtual Account
28. Sahabat Sampoerna Virtual Account
29. Bank Syariah Indonesia Virtual Account
30. Nusapay QRIS
31. BNI QRIS

== Installation ==

Guide to installing the Duitku plugin for GiveWP

1. Download the Duitku plugin for GiveWP here.

2. Open your Wordpress Admin menu (generally in / wp-admin).

3. Open the Plugins menu -> Add New Page.

4. Upload the Duitku plugin file (Make sure GiveWP is installed before adding the Duitku plugin).

5. After the plugin is installed, Duitku will appear in the list of installed plugins. Open the Plugin -> Installled Plugins page, then activate the Duitku plugin.

6. Open Donations -> Settings then Payment Gateways tab under these tab you'll see 'Duitku' hyperlink menu. Klik to set your Duitku Settings.

7. Enter the Merchant Code and API Key, these parameters are created on the Duitku merchant page in the Project menu section

	Addition:

		Endpoint for the trial phase https://sandbox.duitku.com/webapi

		Endpoint for stage production https://passport.duitku.com/webapi
		
8. After the 'Duitku' setting is complete, open the Gateways on The Payment Gateways Tab.

9. Select the payment channel that you will use (example: Duitku Mandiri, Duitku CIMB, Duitku Wallet, Duitku Credit Card, Duitku BCA Klikpay).

== Frequently Asked Questions ==

= What is Duitku? =

Duitku is a Payment Solution service with the best MDR (Merchant Discount Rate) fees from many Payment Channels in Indonesia. As your payment service provider, Duitku can serve payments via credit cards, bank transfers and internet banking directly to your online shop.

= How do I integrate Duitku with my website? =

Integrating online payments with Duitku is very easy, web integration using our API. (API doc: http://docs.duitku.com/docs-api.html) or using plugins for e-commerce.

== Screenshots ==

1. Donations

2. Payment List Settings

3. Duitku Credentials Settings

== Changelog ==

= 1.3.5 =
* Add Payment BNI QRIS

= 1.3.4 =
* Implement Visual Donation Form
* Add Nusapay QRIS
* Add Prefix Order Id
* Changed Environment field from URL input to dropdown for effective option control
* Remove Credential Code
* Update logging for transaction processing, transaction status checks, and callback handling
* Add secureRouteMethods for encrypted returnUrl in Visual Donation Form
* Update callback listener to use registered routes for fixed callback URL
* Add Duitku block for Visual Donation Form integration

= 1.3.3 =
* Add Bank Syariah Indonesia

= 1.3.2 =
* Add Sahabat Sampoerna
* Add Danamon

= 1.2.0 =
* Requires at least -> 1.0.0 changed into 1.2.0
* Tested up to -> 1.0.0 changed into 6.0.1
* Stable tag -> 1.0.0 changed into 1.2.0
* Add Sanitized and Validation
* Remove M1 
* Add payment Indomaret, PosPay, BNC, BRIVA, and QRIS by Nobu

= 1.0 =
* Initial release.
