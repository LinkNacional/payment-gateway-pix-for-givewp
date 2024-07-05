=== Payment Gateway Pix For GiveWP ===
Contributors: linknacional
Donate link: https://www.linknacional.com.br/
Tags: gateway, payments, givewp
Requires at least: 6.0
Tested up to: 6.6
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add Pix Payment Gateway for GiveWP

== Description ==

Streamline your donation process and expand your reach to Brazilian donors by integrating PIX, the instant payment system, into your GiveWP donation forms.

**Dependencies**

[GiveWP](https://wordpress.org/plugins/give/) is needed for the plugin to work.

JS Libraries used:
[QR Code JS by davidshimjs](https://github.com/davidshimjs/qrcodejs)

**Features**
 * Boost Donations: Offer your Brazilian donors their preferred payment method, leading to increased contribution amounts and conversion rates.
 * Skip the Fees: Ditch hefty credit card processing fees and embrace the lower costs of PIX transactions.
 * Instant Gratification: Donations land in your account instantly, improving donor satisfaction and encouraging repeat contributions.
 * Enhanced Security: Leverage the robust PIX infrastructure for safe and secure transactions.
 * Seamless Integration: Works flawlessly with GiveWP, making setup and donation flows a breeze.

== Installation ==

1. Look in the sidebar for the WordPress plugins area;

2. In installed plugins look for the 'add new' option in the header;

3. Click on the 'submit plugin' option in the page title and upload the payment-gateway-pix-for-givewp.zip plugin;

4. Click on the 'install now' button and then activate the installed plugin;

The Payment Gateway Pix for GiveWP is now activated.

== Frequently Asked Questions ==

= What is the Pix Payment Gateway for GiveWP? =

* It's a plugin that lets you add PIX as a payment option for your GiveWP donation forms. Donors scan a QR code or enter a payment key, and funds are transferred directly to your account – instantly and securely.

= Why use PIX for GiveWP donations? =
 * Faster donations: No more waiting for credit card processing, donations arrive in real-time.
 * Happier donors: Paying with PIX is familiar and convenient for Brazilian donors, leading to better donation experiences.
 * Lower fees: Save money on processing fees compared to traditional payment methods.
 * More donations: Offering PIX as an option can increase your overall donation volume.

= How do donors use PIX to donate? =
 1. Fill out your GiveWP donation form as usual.
 2. Choose the PIX payment option.
 3. A QR code will appear on the screen. Donors can scan it with their mobile banking app.
 4. Alternatively, they can enter the payment key manually.
 5. Once the payment is confirmed, the donation is processed instantly.

== Screenshots ==

 1. Settings page
 2. Form view (donate to us)

== Changelog ==
=  2.0.0 =
**24/06/2024**
* Migration to object-oriented programming;
* Implementation of autoloader;
* Addition of compatibility with GiveWP template 3.0.0;
* Addition of automatic logs cleanup using WP-CRON;
* Code optimization and removal of redundancies;
* Update with new WordPress guidelines.

= 1.0.0 =
**05/01/2024**
* Added options to set the recipient's key using provided information.
* Added option to add the recipient's key to GiveWP donation forms.
* Added option to add billing details to donation forms.
* Added debug mode for advanced users.

== Upgrade Notice ==

= 1.0.0 =
**05/01/2024**
* Plugin launch;