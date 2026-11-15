<?php
defined( 'ABSPATH' ) || exit;
add_filter( 'woocommerce_checkout_posted_data', function ( $data ) {
	$digits = array_combine( preg_split( '//u', '۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩', -1, PREG_SPLIT_NO_EMPTY ), str_split( '01234567890123456789' ) );
	foreach ( array( 'billing_phone', 'billing_postcode', 'shipping_postcode' ) as $key ) { if ( isset( $data[ $key ] ) ) { $data[ $key ] = strtr( $data[ $key ], $digits ); } }
	return $data;
} );
add_action( 'init', function () {
	register_block_type( 'elinstyle/commerce', array( 'api_version' => 3, 'editor_script' => 'elinstyle-blocks', 'attributes' => array( 'kind' => array( 'type' => 'string', 'default' => 'cart' ) ), 'render_callback' => function ( $attributes ) {
		if ( ! function_exists( 'WC' ) || ! function_exists( 'elinstyle_commerce_heading' ) ) { return '<p>' . esc_html__( 'WooCommerce و قالب الین‌استایل باید فعال باشند.', 'elinstyle-companion' ) . '</p>'; }
		if ( ! WC()->cart ) { wc_load_cart(); }
		$kind = isset( $attributes['kind'] ) && 'checkout' === $attributes['kind'] ? 'checkout' : 'cart';
		ob_start(); elinstyle_commerce_heading( $kind );
		echo '<div class="wrap commerce-body" data-commerce-body><p class="commerce-live" role="status" aria-live="polite" data-commerce-status></p>';
		echo do_shortcode( 'cart' === $kind ? '[woocommerce_cart]' : '[woocommerce_checkout]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- official Woo shortcodes.
		echo '</div>'; return ob_get_clean();
	} ) );
} );
