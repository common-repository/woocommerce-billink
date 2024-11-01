=== Billink - Legacy ===
Contributors: Tussendoor
Tags: tussendoor, billink, woocommerce, gateway, legacy
Requires at least: 4.0
Tested up to: 6.5.3
Stable tag: 2.5.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Billink is specialist op het gebied van achteraf betalen, zowel voor jouw particuliere als zakelijke klant.

== Description ==
= Nederlands / Dutch =
Billink is specialist op het gebied van achteraf betalen, zowel voor jouw particuliere als zakelijke klant. Wij bieden een scherp tarief, inclusief garantie op uitbetaling. Bovendien ben je niet gebonden aan een jaarcontract of abonnement! Registreren is snel en eenvoudig. Middels een kant-en-klare plug-in van Tussendoor kan er vrijwel met iedere webshop binnen 24 uur worden gekoppeld.

__Voordelen van Billink achteraf betalen:__
- Acceptatie van particuliere én zakelijke transacties
- Garantie tot € 750 voor particulieren en € 10.000 voor bedrijven
- Nederland en België
- Gemiddelde acceptatie van 85-90%
- Uitbetaaltermijn vanaf 7 dagen
- Geen jaarcontract of abonnement
- Scherpe tarieven met gegarandeerde uitbetaling
- Eenvoudige integratie

__Meer informatie__
Bezoek onze website [www.billink.nl/webwinkels](www.billink.nl/webwinkels) voor meer informatie. Wil je starten met het aanbieden van Billink achteraf betalen? Neem contact op via [sales@billink.nl](mailto:sales@billink.nl) of [010 - 41 41 473](tel:0104141473).

Wil je meer informatie over deze plugin? Bezoek dan de website van tussendoor.nl: [https://tussendoor.nl/wordpress-plugins/woocommerce-bilink](https://tussendoor.nl/wordpress-plugins/woocommerce-bilink)

= Engels / English =
Billink is an afterpayment service for both your private and business clients. We offer a competitive rate with guaranteed payment. Moreover, you are not bounded by a year contract or subscription! Registering is quick and easy. With a ready-to-use plug-in from Tussendoor, virtually every webshop can be connected within 24 hours.

__Advantags of Billink Afterpayment services:__
- Acceptance of private and business transactions
- Guarantee up to € 750 for individuals and € 10.000 for companies
- Netherlands and Belgium
- Average acceptance of 85-90%
- Payout period from 7 days
- No year contract or subscription
- Competitive rates with guaranteed payment
- Plug and play

__More information__
Visit our website [www.billink.nl/webwinkels](www.billink.nl/webwinkels) for more information. Do you want to signup for an account? Contact us via [sales@billink.nl](mailto:sales@billink.nl) or [+31 (0) 10 - 41 41 473](tel:031104141473).

Want more information about this plugin? Visit the website of tussendoor.nl: [https://tussendoor.nl/wordpress-plugins/woocommerce-bilink](https://tussendoor.nl/wordpress-plugins/woocommerce-bilink)

== Changelog ==
= 2.5.0 =
* Plugin is marked as Legacy. Please update to the new Billink plugin via: https://wordpress.org/plugins/woocommerce-billink-gateway

= 2.4.2 =
* Changed: Now utilizing new Billink v1 endpoints.

= 2.4.1 =
* Fixed: Billink fee bug.
* Fixed: PHP warning in back-end and customer e-mails.

= 2.4.0 =
* Changed: Billink fee workflow.

= 2.3.0 =
* Added: Filters for extensibility on addresses for communication towards Billink.
* Fixed: Updated dependency to fix a PHP version related bug.
* Fixed: Compatibility with PHP 8.1

= 2.2.0 =
* Update: Added compatibility with the WooCommerce HPOS update.

= 2.1.11 =
* Fixed: Fees names in checkout showing proper name.

= 2.1.10 =
* Fixed: A selectbox could be reset during a quick edit of a product. This is now fixed.

= 2.1.9 =
* Fixed: Billink fee calculation (VAT) bug

= 2.1.8 =
* Added: Optional VAT-number field to checkout for corporate customers

= 2.1.7 =
* Fixed: By-pass Billink payment gateway fees on order-pay page

= 2.1.6 =
* Updated: WordPress 6.0 and WooCommerce 6.5 support.

= 2.1.5 =
* Fixed: Array to string issue

= 2.1.4 =
* Updated: some translation support

= 2.1.3 =
* Fixed: Only trigger workflow for orders that use Billink payment method

= 2.1.2 =
* Fixed: Set a max-height to SVG logo

= 2.1.1 =
* Updated: Logo to SVG (users experiened image size issues).

= 2.1.0 =
* Added: Option to automatically start a workflow when a certain order status is reached.
* Added: New Billink logo.
* Updated: WordPress 5.7 and WooCommerce 5.2 support.

= 2.0.12 =
* Fixed: An issue where addresses with accented characters (e.g. umlaut) would be formatted incorrectly.
* Fixed: A potential bug where the logger was not properly initialized.

= 2.0.11 =
* Added: WooCommerce 5.0 support
* Fixed: an issue when no tax is calculated because a customer is VAT exempt.
* Fixed: a HTML typo causing malformed checkouts.
* Fixed: a deprecated WC_Cart property.

= 2.0.10 =
* Fixed: a compatibility issue

= 2.0.9 =
* Fixed: a bug where address containing spaces were not properly handled.
* Fixed: a small warning where no WC_Cart instance is bound to the WooCommerce object.

= 2.0.8 =
* Fixed: an autoloader issue

= 2.0.7 =
* Fixed: a bug where the wrong error message was displayed.
* Fixed: a compatibility issue with PHP 5.6.
* Fixed: a bug where a logged variable could be undefined.
* Fixed: an issue where empty-ish orders would cause errors.
* Fixed: splitting an address should work much better now.
* Fixed: a compatibility issue with some themes, where is_checkout() is called too early.
* Fixed: a jQuery deprecation warning.

= 2.0.6 =
* Added: WooCommerce 4.1 support.
* Added: 'billink_is_available' filter to overwrite the is_available() method of the gateway.
* Fixed: an issue where errors were not properly handled.
* Fixed: an issue where the order amount in Billink was not equal to the order amount in WooCommerce because of coupons

= 2.0.5 =
* Added: WordPress 5.4 support.
* Fixed: An issue where some XML values were not properly escaped.
* Fixed: An rounding issue

= 2.0.4 =
* Added: WooCommerce 4 support.
* Added: Discounts are now presented on a separate order line.
* Updated: Set the default "Fallback Workflow" to 1.
* Fixed: An issue where some discounts were not applied in Billink.
* Fixed: An issue where crediting an order through Billink would cause stock to be restored twice.
* Fixed: Removed benchmark code from a dependency, as it was setting off a malware scanner.
* Fixed: An issue where the payment costs were not properly calculated.

= 2.0.3 =
* Added: Phonenumber field if the checkout does not contain one
* Added: Backward compatibility for crediting 'old' orders
* Fixed: An issue where the paymentcosts could not be properly resolved
* Fixed: An issue where the order ID was used instead of the order number
* Fixed: An issue where the paymentcosts would not be recalculated when the cart updated

= 2.0.2 =
* Fixed: An issue where the endpoint was not properly switched when enabling testmode.

= 2.0.1 =
* Added: Support for the 'Postcode Checkout - WooCommerce address validation' plugin.
* Fixed: A bug where the plugin would ask for a chamber of commerce number even if it wasn't a business order.
* Fixed: An issue where the debug mode was enabled by default.

= 2.0.0 =
* Added: Enable or disable Billink based on the customer country
* Added: Additional paymentcosts are much more flexible
* Added: Workflownumbers can be configured much more flexible
* Added: Custom WooCommerce order status after a payment through Billink
* Added: Enhanced and more extensive logging
* Added: Credit a Billink order directly through WooCommerce
* Added: View the Billink order status directly through WooCommerce
* Added: Start the workflow of a Billink order directly through WooCommerce
* Added: Minimum and maximum order amounts that can be settled through Billink
* Added: ... it's basically a complete rewrite :)

= 1.3.7 =
* Fixed: an issue where the additional costs we're not properly added to the order.
* Added: Preparations for version 2

= 1.3.6 =
* Updated: Tested with WooCommerce 3.5

= 1.3.5 =
* Added: IP info in the order send to Billink’s order API.

= 1.3.4 =
* Fixed: A problem with the max order amount function.

= 1.3.3 =
* Fixed: a non-prefixed class which could cause a conflict.

= 1.3.2 =
* Added: Option to set a max order amount for orders paid with Billink.

= 1.3.1 =
* Updated: Tested with WooCommerce 3.4

= 1.3.0 =
* Added: Support for refunds. Note: refunds only work for WooCommerce 3+.
* Fixed: A few deprecated function calls.

= 1.2.20 =
* Fixed: A problem with the tax
* Fixed: A problem with the calculation of the discount

= 1.2.10 =
* Added: Editing the link to the Billink terms.

= 1.2.0 =
*   Added option to disable birthday field by business order
*   Made birthday field optional when viewing by business order

= 1.0 =
*   Initial release

== Translations ==

*   nl_NL
*   en_EN
