<?php
/**
 * Plugin Name: Bench Woo Member Discount
 * Description: Applies a 10 percent discount to WooCommerce product prices for logged-in users with the customer role.
 * Version:     1.0.0
 * Author:      Bench
 * Text Domain: bench-woo-member-discount
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'bench_woo_member_discount_init' );

/**
 * Hook into WooCommerce price filters.
 */
function bench_woo_member_discount_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	$user = wp_get_current_user();

	if ( ! in_array( 'customer', (array) $user->roles, true ) ) {
		return;
	}

	// Filter the regular price.
	add_filter( 'woocommerce_product_get_price', 'bench_woo_member_discount_price', 10, 2 );
	add_filter( 'woocommerce_product_get_sale_price', 'bench_woo_member_discount_price', 10, 2 );
	add_filter( 'woocommerce_product_get_regular_price', 'bench_woo_member_discount_price', 10, 2 );

	// Filter the price used in variable product min/max calculations.
	add_filter( 'woocommerce_variation_get_price', 'bench_woo_member_discount_price', 10, 2 );
	add_filter( 'woocommerce_variation_get_sale_price', 'bench_woo_member_discount_price', 10, 2 );
	add_filter( 'woocommerce_variation_get_regular_price', 'bench_woo_member_discount_price', 10, 2 );
}

/**
 * Apply the 10 percent discount to a product price.
 *
 * @param string|float $price The original price.
 * @param WC_Product   $product The product object.
 * @return string|float The discounted price.
 */
function bench_woo_member_discount_price( $price, $product ) {
	if ( is_admin() ) {
		return $price;
	}

	if ( empty( $price ) || $price <= 0 ) {
		return $price;
	}

	$discount_rate = 0.10;
	$discounted    = (float) $price * ( 1 - $discount_rate );

	// Preserve WooCommerce numeric formatting.
	return wc_format_decimal( $discounted, wc_get_price_decimals() );
}
