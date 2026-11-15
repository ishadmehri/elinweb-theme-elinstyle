<?php
/** Theme setup and scoped canvas assets. */
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/inc/commerce.php';
add_filter( 'woocommerce_locate_template', function ( $template, $name ) {
	if ( 0 === strpos( $name, 'myaccount/' ) && ! function_exists( 'elinstyle_render_account' ) ) { return WC()->plugin_path() . '/templates/' . $name; }
	return $template;
}, 20, 2 );

function elinstyle_setup() {
	load_theme_textdomain( 'elinstyle', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'woocommerce' );
	add_editor_style( 'assets/editor.css' );
	register_block_pattern_category( 'elinstyle', array( 'label' => __( 'الین‌استایل', 'elinstyle' ) ) );
}
add_action( 'after_setup_theme', 'elinstyle_setup' );

/** Keep junction-based development changes visible after a normal reload. */
function elinstyle_asset_version( $filename ) {
	$file = get_template_directory() . '/assets/' . $filename;
	return is_file( $file ) ? (string) filemtime( $file ) : wp_get_theme()->get( 'Version' );
}

function elinstyle_block_assets() {
	// enqueue_block_assets also targets the editor content iframe on WP 6.3+.
	foreach ( array( 'shared', 'blocks', 'product', 'commerce', 'catalog', 'account' ) as $asset ) {
		wp_enqueue_style( 'elinstyle-' . $asset, get_template_directory_uri() . '/assets/' . $asset . '.css', array(), elinstyle_asset_version( $asset . '.css' ) );
	}
}
add_action( 'enqueue_block_assets', 'elinstyle_block_assets' );

function elinstyle_front_assets() {
	if ( function_exists( 'is_account_page' ) && ( is_account_page() || is_page( 'login' ) ) ) { wp_enqueue_script( 'elinstyle-account', get_template_directory_uri() . '/assets/account.js', array(), elinstyle_asset_version( 'account.js' ), true ); }
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		wp_enqueue_script( 'elinstyle-catalog', get_template_directory_uri() . '/assets/catalog.js', array( 'wp-i18n' ), elinstyle_asset_version( 'catalog.js' ), true );
	}
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
		wp_enqueue_script( 'elinstyle-commerce', get_template_directory_uri() . '/assets/commerce.js', array( 'jquery', 'wp-i18n' ), elinstyle_asset_version( 'commerce.js' ), true );
	}
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_script( 'elinstyle-product', get_template_directory_uri() . '/assets/product.js', array( 'elinstyle-motion', 'wp-i18n' ), elinstyle_asset_version( 'product.js' ), true );
		wp_localize_script( 'elinstyle-product', 'elinstyleProduct', array( 'api' => esc_url_raw( rest_url( 'wc/store/v1/' ) ), 'products' => esc_url_raw( rest_url( 'elinstyle/v1/products/' ) ), 'nonce' => wp_create_nonce( 'wc_store_api' ) ) );
	}
	wp_enqueue_script( 'elinstyle-motion', get_template_directory_uri() . '/assets/motion.js', array(), elinstyle_asset_version( 'motion.js' ), true );
	wp_enqueue_script( 'elinstyle-size-guide', get_template_directory_uri() . '/assets/size-guide.js', array( 'elinstyle-motion' ), elinstyle_asset_version( 'size-guide.js' ), true );
	wp_enqueue_script( 'elinstyle-front', get_template_directory_uri() . '/assets/front.js', array( 'elinstyle-motion' ), elinstyle_asset_version( 'front.js' ), true );
	wp_localize_script( 'elinstyle-front', 'elinstyleLinks', array(
		'cart' => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
		'account' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '',
		'demo' => __( 'این بخش نمایشی است؛ هیچ خرید یا ارسالی انجام نمی‌شود.', 'elinstyle' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'elinstyle_front_assets' );
add_filter( 'body_class', function ( $classes ) { $classes[] = 'elinstyle-surface'; return $classes; } );

/** Shared parts are editable reusable blocks after explicit demo import. */
function elinstyle_part( $name ) {
	$id = absint( get_option( 'elinstyle_part_' . $name ) );
	$post = $id ? get_post( $id ) : null;
	if ( $post && 'wp_block' === $post->post_type && 'publish' === $post->post_status ) {
		echo do_blocks( $post->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- saved blocks render through WordPress.
		return;
	}
	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'elinstyle/' . $name );
	if ( $pattern ) {
		echo do_blocks( $pattern['content'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
