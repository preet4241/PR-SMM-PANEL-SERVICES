=== SMM API ===
Contributors: softnwords
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7PE62XBR4M9CA
Tags: smm panel, reseller panel, api panel, social panel
Requires at least: 4.9.9
Tested up to: 6.6
Stable tag: 6.0.30
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

SMM API Plugin is an API integrator for SMM servers and Re-Sellers panel website that runs in WordPress platform.

== Description ==

This plugin automates the client order processing and Order placing at SMM server. Customer gets an onetime response back for each SMM item that is bought through panel websites. Many SMM servers do not give free plugin for WordPress Sites. Hence I thought to write simple plugin that does online active integration with supporting server sites. It needs a api server url and api key to pull data from server and place order with server from any Re-seller panel WordPress site. The order is displayed in admin page and it will be a List view in admin page. Users can put their own price tag and route orders to server sites with added options available with this plugin. Try free version and learn to use the plugin before you want any advanced features. If you need technical support please mail me at sam@softnwords.com and you get reply in couple of days as there are many emails in mailbox.

**Some Core Features:**

* This plugin uses WordPress Built-in Libraries. So it runs so fast with any hosting provider.
* It can be set-up very quick with no technical background.
* It uses database to store only active orders.


**Notice**

This plugin framework is fork from yith WooCommerce subscription and the use and features are substantially differs. All credits to original author for the beautiful framework.


== Installation ==

This section describes how to install the plugin and get it working.

Follow:

1. Upload `smm-api.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Enter API url and API Key in Dash Board Menu API SERVER-> Add Server
4. Add SMM Items as per the data displayed SMM server site.
5. Add WooCommerce product in Admin panel and select Api Items from select box.
6. That's it! your shop page can now take orders and trigger the orders remotely at SMM server site.

== Frequently Asked Questions ==

= 1. What is about SMM API? =

This Plugin is for API integration between WordPress Re seller panel website and SMM server website.

= 2. Is this full version  or demo version? =

This is full version as this is simple plugin. But if you want subscription and order listing status, Please get your premium copy of this plugin.

= 3. Does this plugin track back any data from installed website? =

This plugin does not pull any data from installed website and never posts content to any other websites.

= 4. Is this plugin runs other than WordPress sites? =

This plugin runs only for WordPress platform.

=5. How to add API items in plugin API ITEMs Table menu? =

Step 1) you need to go to smm server site find the service listed that you want it for your site.

I found this service on smm server site

ID Service Rate per 1000 Min order Max order Description

208 Instagram Followers – 50k Max – 1-3k/day 1.40 100 50000 5-10k/day Speed

Here you see price for 1000 quantity = 1.4$

and minimum order quantity is = 100

Hence you need each order should take 100 quantity.

STEP 2) Go to your site wp-admin/admin.php?page=smms_woocommerce_subscription&tab=items and click Add Record button:

you need to fill each field: 
Save and  see it on the list.

Step 3) Go to /wp-admin/edit.php?post_type=product and click add product or edit exisitng product with aPI Items from Select Box.

and click API check box enabled



publish or update your product now

copy the product Permalink clip board

http://yoursite/product/1000-instagram-followers/



that’s all you are done!

== Screenshots ==

1. Admin window
2. Setting Window
3. Order Window

== Changelog ==

= 6.0.30=

* HPOS bug fixed, Build/Test query url form added

= 6.0.9=

* variation bug fixed.

= 6.0.8=

* variation quantity and price bug fixed.

= 6.0.1=

* New feature at cart page and subscription features.

= 5.0.1=

* SMM API Review.

= 4.0.1=

* SMM API with Variation product support.

= 3.0.1=

* SMM API with select click feature on Add Item Page.

= 2.0.3=

* SMM API quantity error fixed.


= 2.0.2=

* SMM API server demo Added.
* SMM API items Data IMPORT Added.


= 2.0.1=

* SMM API plugin with additional features for multiple servers

= 1.1.4 =

* SMM API Order Page Corrected.


= 1.1.3 =

* Multiple API SERVER is possible.
* Email Notification is possible.

= 1.1.2 =

* Editing API items added and prevented duplicated order .

= 1.1.1 =

* Coupon  order triggering is added.

= 1.1 =

* sandbox checking added.

= 1.0 =

* Initial release.

== Upgrade Notice ==

= 6.0.30 =

* new features and release.

= 6.0.9=

*variation bug fixed.

= 6.0.8=

*variation quantity and price bug fixed.

= 5.0.1=

*Plugin reviewed.

= 4.0.1=

*New features for variations.

= 3.0.1=

*A Few Bugs Fixed & Added New features.

= 2.0.3=

*A Few Bugs Fixed related to quantity.

= 2.0.2=

*Added support for variable product

= 2.0.1=

* SMM API plugin with additional features for multiple servers

== Arbitrary section ==

Order list View:

1. Change Order scheme
2. Editing API Items
3. Editing API Server Items
4. Import SMM Items from Server.

== External services ==

 This plugin connects to an API to obtain demo content, it's needed to show the API server information and output content in the wp list table at the admin panel window.
 It sends the user's api key every time the demo is loaded and recives the server contents to fill the wp list table. This service is provided by "seoclerks.in": [terms of use](https://www.seoclerks.in/tos), [privacy policy](https://www.seoclerks.in/privacy).
 
 This Plugin connects to softnwords.com for [user guide data](https://softnwords.com/my-account/support/dashboard/smm-api/) and  [terms of use](https://www.softnwords.com/tos), [privacy policy](https://www.softnwords.com/privacy).
