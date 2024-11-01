<?php
/**
 * Plugin Name: WooCommerce Prune Orders
 * Plugin URI: https://github.com/Coded-Commerce-LLC/WooCommerce-Prune-Orders
 * Description: Adds a tool to the WooCommerce tools page which puts orders of selected status into the trash where they can be permanently deleted.
 * Version: 1.4
 * Author: Coded Commerce, LLC
 * Author URI: https://codedcommerce.com
 * WC requires at least: 6.0
 * WC tested up to: 9.3.3
 * License: GPLv2 or later
 */

// Declare Support For HPOS
add_action( 'before_woocommerce_init', function() {
	if( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables', __FILE__, true
		);
	}
} );

// Plugins Page Link To Settings
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {

	$settings = [
		'settings' => sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'admin.php?page=wc-status&tab=tools' ),
			__( 'Settings', 'benchmark-email-lite' )
		),
	];

	return array_merge( $settings, $links );

} );

// JavaScripts
add_action( 'admin_enqueue_scripts', function( $page ) {

	// For Woo Tools Page Only
	if( $page != 'woocommerce_page_wc-status' ) {
		return;
	}

	// Enqueue With jQuery
	wp_enqueue_script( 'jquery' );
	wp_add_inline_script( 'jquery', "

		// DOM Loaded
		jQuery( document ).ready( function( $ ) {

			// Select All Plugin Buttons
			$(
				'form#form_prune_cancelled_orders, ' +
				'form#form_prune_completed_orders, ' +
				'form#form_prune_failed_orders, ' +
				'form#form_prune_pending_orders, ' +
				'form#form_prune_refunded_orders'

			// On Click
			).submit( function( e ) {
				e.preventDefault();

				// Prompt For Cutoff Date
				var today = new Date();
				var date = prompt(
					'Please enter a cutoff date MM/DD/YYYY to trim orders up to.',
					( '0' + ( today.getMonth() + 1 ) ).slice( -2 )
					+ '/' + ( '0' + today.getDate() ).slice( -2 )
					+ '/' + today.getFullYear()
				);

				// Handle Cancellation
				if( date == null || date == '' ) {
					return false;
				}

				// Send Value To PHP
				$( '<input>' ).attr( {
					type: 'hidden',
					id: 'foo',
					name: 'post_date',
					value: date
				} ).appendTo( $(this) );
				return $(this).unbind( 'submit' ).submit();

			} );

		} );

	", 'after' );

} );

// Adds Tools To WooCommerce
add_filter( 'woocommerce_debug_tools', function( $tools ) {

	$tools['prune_cancelled_orders'] = [
		'button' => __( 'Trash Cancelled orders', 'woocommerce-prune-orders' ),
		'callback' => [ 'woo_prune_orders', 'run_tool' ],
		'name' => __( 'Trash all Cancelled WooCommerce orders', 'woocommerce-prune-orders' ),
		'desc' => sprintf(
			"<strong class='red'>%s</strong> %s %s",
			__( 'Caution!', 'woocommerce-prune-orders' ),
			__( 'This option will move all Cancelled orders to the trash.', 'woocommerce-prune-orders' ),
			__( 'Are you sure?', 'woocommerce-prune-orders' )
		),
	];

	$tools['prune_completed_orders'] = [
		'button' => __( 'Trash Completed orders', 'woocommerce-prune-orders' ),
		'callback' => [ 'woo_prune_orders', 'run_tool' ],
		'name' => __( 'Trash all Completed WooCommerce orders', 'woocommerce-prune-orders' ),
		'desc' => sprintf(
			"<strong class='red'>%s</strong> %s %s",
			__( 'Caution!', 'woocommerce-prune-orders' ),
			__( 'This option will move all Completed orders to the trash.', 'woocommerce-prune-orders' ),
			__( 'Are you sure?', 'woocommerce-prune-orders' )
		),
	];

	$tools['prune_failed_orders'] = [
		'button' => __( 'Trash Failed orders', 'woocommerce-prune-orders' ),
		'callback' => [ 'woo_prune_orders', 'run_tool' ],
		'name' => __( 'Trash all Failed WooCommerce orders', 'woocommerce-prune-orders' ),
		'desc' => sprintf(
			"<strong class='red'>%s</strong> %s %s",
			__( 'Caution!', 'woocommerce-prune-orders' ),
			__( 'This option will move all Failed orders to the trash.', 'woocommerce-prune-orders' ),
			__( 'Are you sure?', 'woocommerce-prune-orders' )
		),
	];

	$tools['prune_pending_orders'] = [
		'button' => __( 'Trash Pending orders', 'woocommerce-prune-orders' ),
		'callback' => [ 'woo_prune_orders', 'run_tool' ],
		'name' => __( 'Trash all Pending WooCommerce orders', 'woocommerce-prune-orders' ),
		'desc' => sprintf(
			"<strong class='red'>%s</strong> %s %s",
			__( 'Caution!', 'woocommerce-prune-orders' ),
			__( 'This option will move all Pending orders to the trash.', 'woocommerce-prune-orders' ),
			__( 'Are you sure?', 'woocommerce-prune-orders' )
		),
	];

	$tools['prune_refunded_orders'] = [
		'button' => __( 'Trash Refunded orders', 'woocommerce-prune-orders' ),
		'callback' => [ 'woo_prune_orders', 'run_tool' ],
		'name' => __( 'Trash all Refunded WooCommerce orders', 'woocommerce-prune-orders' ),
		'desc' => sprintf(
			"<strong class='red'>%s</strong> %s %s",
			__( 'Caution!', 'woocommerce-prune-orders' ),
			__( 'This option will move all Refunded orders to the trash.', 'woocommerce-prune-orders' ),
			__( 'Are you sure?', 'woocommerce-prune-orders' )
		),
	];

	return $tools;

} );

// Plugin Class
class woo_prune_orders {

	// Handle Tool Submissions
	static function run_tool() {

		// Security Check
		if( ! current_user_can( 'manage_woocommerce' ) ) {
			return false;
		}

		// Ensure Action Provided
		if( empty( $_REQUEST['action'] ) ) {
			return false;
		}

		// Map To WooCommerce Order Status
		$status_mappings = [
			'wc-cancelled' => 'prune_cancelled_orders',
			'wc-completed' => 'prune_completed_orders',
			'wc-failed' => 'prune_failed_orders',
			'wc-pending' => 'prune_pending_orders',
			'wc-refunded' => 'prune_refunded_orders',
		];
		$post_status = isset( $_REQUEST['action'] )
			? sanitize_text_field( $_REQUEST['action'] ) : '';
		$post_status = array_search( $post_status, $status_mappings );

		// Get Requested Date Limit
		$post_date = isset( $_REQUEST['post_date'] )
			? sanitize_text_field( $_REQUEST['post_date'] ) : '';

		// Handle Mapping Or Date Failures
		if( empty( $post_status ) || empty( $post_date ) ) {
			return false;
		}

		// Get Matching Orders
		$args = [
			'date_created' => '<=' . date( 'Y-m-d H:i:s', strtotime( $post_date ) ),
			'limit' => 500,
			'status' => $post_status,
			'type' => 'shop_order',
		];
		$orders = wc_get_orders( $args );

		// Send Orders To Trash
		foreach( $orders as $order ) {
			$order->delete();
		}

		// Response
		$message = sizeof( $orders ) === 1
			? __( 'order was moved to the trash.', 'woocommerce-prune-orders' )
			: __( 'orders were moved to the trash.', 'woocommerce-prune-orders' );
		return sizeof( $orders ) . ' ' . $message;

	}

}