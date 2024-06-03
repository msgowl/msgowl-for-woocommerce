=== MSGOWL for WooCommerce ===
Contributors: mostafa.s1990, kashani, mehrshaddarzi, alifallahrn, panicoschr10
Donate link: 
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: sms, wordpress, send, subscribe, message, register, notification, msgowl, woocommerce, subscribes-sms, bulksms
Requires at least: 3.0
Tested up to: 6.5
Stable tag: 1.0.14
Requires PHP: 5.6
WC requires at least: 3.0
WC tested up to: 8.7.0

Improve your WordPress Website: Communicate with SMS using the MSGOWL for WooCommerce.

== Plugin Description ==

### MSGOWL for WooCommerce: A Wordpress plugin for SMS Messaging/Texting for WooCommerce

This plugin is a fork from https://wordpress.org/plugins/wp-sms/ developed by VeronaLabs.
It is completely free to download and use. You just need to have an account in https://msgowl.com/ 

Using MSGOWL for WooCommerce plugin you can integrate Wordpress with WooCommerce plugin

= Features =
 * Send SMS to customers when a new product is published
 * Send SMS to operator when a new order is placed
 * Send SMS to SMS receiver when a new order is placed
 * Send SMS to customer when a new order is placed
 * Send SMS to customer when the order status changes
 * Send SMS to administrator/shop manager/customer for different order statuses set

= Send SMS to customers when a new product is published = 
By enabling this option, the customers will receive SMS when a new product is published. 
The following variables can be set in the message content in the Notifications settings
Product title: %product_title%, Product url: %product_url%, Product date: %product_date%, Product price: %product_price%

= Send SMS to Operator when a new order is placed = 
By enabling this option, the operator will receive SMS when a new order is placed. 
The following variables can be set in the message content in the Notifications settings
Order ID: %order_id%, Order status: %status%

= Send SMS to SMS receiver when a new order is placed = 
By enabling this option, the SMS receiver set will receive SMS when a new order is placed. 
The following variables can be set in the message content in the Notifications settings
Billing First Name: %billing_first_name%, Billing Last Name: %billing_last_name%,Billing Phone Number: %billing_phone%, Order id: %order_id%, Order number: %order_number%, Order Total: %order_total%, Order status: %status%

= Send SMS to customer when new order = 
By enabling this option, the customer will receive SMS when new order. 
The following variables can be set in the message content in the Notifications settings
Order id: %order_id%, Order number: %order_number%, Order status: %status%, Order Total: %order_total%, Customer name: %billing_first_name%, Customer family: %billing_last_name%

= Send SMS to customer when status is changed = 
By enabling this option, the customers will receive SMS when the status of their order is changed. 
The following variables can be set in the message content in the Notifications settings
Order status: %order_number%, Order number: %status%, Customer name: %customer_first_name%, Customer family: %customer_last_name%

= Send SMS to administrator/shop manager/customer for different order statuses = 
By enabling this option, the administrator and/or shop manager and/or customer will receive SMS when the order status changes to what we have set. 
So the administrator may receive SMS if the order is completed or cancelled, and the customer and Shop manager may receive SMS if the order is completed.
The following variables can be set in the message content in the Notifications settings
Order status: %status%, Order number: %order_number%, Customer name: %billing_first_name%, Customer family: %billing_last_name%


== MSGOWL for WooCommerce plugin Installation ==

* Prerequisites
1. Installation of Wordpress
2. Installation of WooCommerce 
3. Active msgowl account
4. Set user mobile phone value whether it will be taken from user profile or billing phone

### INSTALL MSGOWL for WooCommerce THROUGH WORDPRESS 

1. Upload provided zip file using Plugins page;
2. Activate MSGOWL for WooCommerce from your Plugins page;

### UNINSTALL MSGOWL for WooCommerce
1. De-activate MSGOWL for WooCommerce from your Plugins page;
2. Delete Plugin

### AFTER ACTIVATION

1. Sign in with your msgowl account [Msgowl login](https://msgowl.com/login#/) or Sign Up [Msgowl Sign Up](https://msgowl.com/register#/)
2. Visit [Msgowl api](https://msgowl.com/dev)  
3. Go to API Key Authentication, enter a Title and generate an API Key
4. Copy the generated API Key into the MSGOWL for WooCommerce plugin - Settings - Gateway - API Key
5. Save
6. Visit Settings - Features - Add Mobile number field - and select it. So, now you can get the mobile phone of new subscribers and
also update existing subscribers/users with their mobile phone.
7. Enable WP User registration to allow get the mobile phone of new subscribers at the time of registration.
8. Visit MSGOWL for WooCommerce  - Settings -> Integration -> Orders related Customer Phone Number field - Customer profile mobile phone or Customer billing phone number as on order - Which will be 
the mobile phone to use for WooCommerce Customer order notifications.

You’re done! You can now SMS to your customers!!

== Frequently Asked Questions ==
= 1.0.0 =
* No FAQ

== Changelog ==
= 1.0.13 =
* Compatibility with WordPress 6.4
* Compatibility with WooCommerce 8.2.1
= 1.0.12 =
* Compatibility with WordPress v6.3
* Compatibility with WooCommerce v7.9.0
* Fix product price - add currency in sms sent
= 1.0.11 =
* Compatibility with WordPress v6.2
* Compatibility with WooCommerce v7.5.1
= 1.0.10 =
* Compatibility with WordPress v6.1.1
* Compatibility with WooCommerce v7.3.0
= 1.0.9 =
* Optimization of API
* Compatibility with WordPress v6.1
= 1.0.8 =
* Compatibility with WordPress v6.0
* Compatibility with WooCommerce v6.5.1
= 1.0.7 =
* Fix the link for information on how messages are charged
* Compatibility with WordPress v5.9
* Compatibility with WooCommerce v6.1.1
= 1.0.6 =
* Compatibility with WordPress v5.8
* Compatibility with WooCommerce v5.5.2
= 1.0.5 =
* Escape output
* Fix ordering in Reports
= 1.0.4 =
* Fix get Customer phone based on setting
* Added in description as a forked plugin from https://wordpress.org/plugins/wp-sms/ by VeronaLabs
* Added inline documentation that this plugin is a fork from https://wordpress.org/plugins/wp-sms/ by VeronaLabs, credited all authors and added copyright
* Added license file
* Added info Sender ID Can contain only letters digits and spaces
= 1.0.3 =
* Fix sending of single message to return message_id in response
* Fix Report queries for Messages and Campaigns
* Do not sum to Total messages in Reports - Campaigns if there are no sent, pending or failed messages
* Automatic data refresh on Reports interval changed to query DB only every 24 seconds - 10 times maximum for as long the Reports screen remains open
= 1.0.2 =
* Compatibility with WordPress v5.7
= 1.0.1 =
* Admin mobile phone number is renamed to Operator mobile phone number
* Added automatic data refresh on Reports 
* Added links to Report for 'Information on how messages are charged' and 'Contact Support'
* Balance in Admin Menu-Bar is updated automatically when a message is sent
* Updated Documentation on how messages are charged
* Fix filtering in Reports view
* Fix Privacy error on function
= 1.0.0 =
* First version of plugin

== Upgrade Notice ==
= 1.0 =
* No upgrade

== Screenshots ==

1. General
2. Gateway configuration.
3. Features page.
4. WooCommerce page.
5. Reports Page.
6. Documentation
7. Privacy Page