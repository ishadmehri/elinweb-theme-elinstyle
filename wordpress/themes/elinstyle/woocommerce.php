<?php
/** Default Woo endpoints use WooCommerce's supported content rendering. */
defined( 'ABSPATH' ) || exit;
get_header();
$shop_page = function_exists( 'wc_get_page_id' ) ? get_post( wc_get_page_id( 'shop' ) ) : null;
if ( ! $shop_page || 'page' !== $shop_page->post_type ) { $shop_page = get_page_by_path( 'shop' ); }
if ( is_product() && function_exists( 'elinstyle_render_product' ) && elinstyle_current_product() ) {
	elinstyle_part( 'product' );
} elseif ( is_shop() && $shop_page && has_block( 'elinstyle/catalog', $shop_page->post_content ) ) {
	echo do_blocks( $shop_page->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress block rendering.
} else {
	echo '<main id="main" class="wrap">';
	woocommerce_content();
	echo '</main>';
}
get_footer();
