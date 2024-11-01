=== WooCommerce Prune Orders ===
Contributors: seanconklin
Donate link: https://codedcommerce.com/donate
Tags: woocommerce, administrator, tool, trash, prune, trim, clean, performance
Requires at least: 5.9
Tested up to: 6.7-RC2
Requires PHP: 7.4
Stable tag: 1.4
License: GPLv2 or later

Adds tools to the WP Admin > WooCommerce > Status > Tools page to move all orders of the selected status and cutoff date into the trash, where they can then be permanently deleted to improve site performance.

Many thanks to Chris Mospaw, Sr. Technical Project Manager, Pagely for describing this plugin during the WooSesh 2022 session How To Fix the Top 5 Performance Issues in WooCommerce :)

Greatly improve the performance of a WooCommerce site bogged down by tens of thousands of historic orders. Back orders up using your favorite Order Exports plugin, or rely upon integrated accounting software to keep history beyond the currently active orders in processing.

If you empty the WP Admin > WooCommerce > Orders > Trash with hundreds of orders inside, you may receive a timeout error of one form or another. Usually the trash will continue to clear. If not, simply return to the Trash later to clear out the remaining orders.

== Screenshots ==
 
1. Displays the tools added into WP Admin > WooCommerce > Status > Tools section.
2. Displays a prompt for the date to trim orders up to.
3. Displays one of the tools after being ran with some orders.
4. Displays the orders just moved into the trash.

== Changelog ==

= 1.4 on 2023-01-25 =
* Added: Support for HPOS, move to C.R.U.D. functions, declaration of support.
* Updated: WP and Woo version declarations, bumping minimum PHP to v7.4 which is on the way out.
* Fixed: Data sanitization to the date field.

= 1.3 on 2021-08-21 =
* Fixed: Latest WooCommerce compatibility.

= 1.2 on 2020-02-06 =
* Added: settings shortcut into plugin action links.
* Updated: code cleanup and updating tested-to metadata.

= 1.1 on 2018-10-04 =
* Added: feature to set date to prune up to.
* Added: plugin metadata for WooCommerce.
* Updated: cleaned PHP array instances to newer standard.
* Fixed: singular/plural response message.

= 1.0 on 2018-07-09 =
* Initial commit.
