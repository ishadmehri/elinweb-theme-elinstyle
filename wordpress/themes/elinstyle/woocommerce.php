<?php
/** Default Woo endpoints use WooCommerce's supported content rendering. */
defined( 'ABSPATH' ) || exit;
get_header();
$shop_page = function_exists( 'wc_get_page_id' ) ? get_post( wc_get_page_id( 'shop' ) ) : null;
if ( ! $shop_page || 'page' !== $shop_page->post_type ) { $shop_page = get_page_by_path( 'shop' ); }
if ( is_product() && function_exists( 'elinstyle_demo_elementor_part' ) && elinstyle_demo_elementor_part( 'product' ) ) {
	// Pro's selected product document rendered once, using native Woo widgets.
} elseif ( is_product() && function_exists( 'elinstyle_render_product' ) && elinstyle_current_product() ) {
	elinstyle_part( 'product' );
} elseif ( is_shop() && $shop_page && function_exists( 'elinstyle_is_elementor_demo' ) && elinstyle_is_elementor_demo( $shop_page->ID ) ) {
	// This is the queried shop page itself, not an embedded template. The display
	// helper rejects the current ID as recursive; the native document renderer
	// is also used by Elementor's normal the_content integration.
	echo \Elementor\Plugin::$instance->frontend->get_builder_content( $shop_page->ID, true );
} elseif ( is_shop() && $shop_page && has_block( 'elinstyle/catalog', $shop_page->post_content ) ) {
	echo do_blocks( $shop_page->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress block rendering.
} else {
	echo '<main id="main" class="wrap">';
	woocommerce_content();
	echo '</main>';
}
get_footer();
