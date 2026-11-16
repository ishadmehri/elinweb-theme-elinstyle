<?php
/**
 * Plugin Name: Elinstyle Companion
 * Description: Native WooCommerce presentation and explicit Gutenberg or classic Elementor/Pro demo import.
 * Version: 0.4.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Author: Iman Shadmehri
 * Author URI: https://elinweb.ir
 * Text Domain: elinstyle-companion
 * License: Pending project license selection
 */
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/product.php';
require_once __DIR__ . '/commerce.php';
require_once __DIR__ . '/catalog.php';
require_once __DIR__ . '/account.php';
require_once __DIR__ . '/elementor.php';
require_once __DIR__ . '/importer.php';
require_once __DIR__ . '/importer-admin.php';

function elinstyle_register_blocks() {
	load_plugin_textdomain( 'elinstyle-companion', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	wp_register_script( 'elinstyle-blocks', plugins_url( 'blocks.js', __FILE__ ), array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-i18n' ), (string) filemtime( __DIR__ . '/blocks.js' ), true );
	register_block_type( 'elinstyle/catalog', array(
		'api_version' => 3,
		'editor_script' => 'elinstyle-blocks',
		'attributes' => array( 'mode' => array( 'type' => 'string', 'default' => 'featured' ) ),
		'render_callback' => 'elinstyle_catalog',
	) );
	register_block_type( 'elinstyle/contact', array( 'api_version' => 3, 'editor_script' => 'elinstyle-blocks', 'render_callback' => 'elinstyle_contact' ) );
	register_block_type( 'elinstyle/fragment', array( 'api_version' => 3, 'editor_script' => 'elinstyle-blocks', 'attributes' => array( 'content' => array( 'type' => 'string', 'source' => 'html' ) ) ) );
}
add_action( 'init', 'elinstyle_register_blocks' );

function elinstyle_query_value( $key ) {
	return isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public read-only catalog filtering.
}

function elinstyle_contact() {
	ob_start(); ?>
	<form data-elin-contact onsubmit="return false">
	<h2><?php esc_html_e( 'پیامت را برای ما بنویس', 'elinstyle-companion' ); ?></h2>
	<p role="status"><?php esc_html_e( 'فرم نمایشی است؛ پیامی ارسال نمی‌شود.', 'elinstyle-companion' ); ?></p>
	<div class="form-row"><div class="field"><label><?php esc_html_e( 'نام', 'elinstyle-companion' ); ?><input autocomplete="name" required></label></div>
	<div class="field"><label><?php esc_html_e( 'ایمیل یا شماره تماس', 'elinstyle-companion' ); ?><input required></label></div></div>
	<div class="field"><label><?php esc_html_e( 'موضوع', 'elinstyle-companion' ); ?><select><option><?php esc_html_e( 'پرسش درباره محصول', 'elinstyle-companion' ); ?></option><option><?php esc_html_e( 'سایر', 'elinstyle-companion' ); ?></option></select></label></div>
	<div class="field"><label><?php esc_html_e( 'پیام', 'elinstyle-companion' ); ?><textarea required></textarea></label></div>
	<button class="btn" type="submit"><?php esc_html_e( 'بررسی پیام نمایشی', 'elinstyle-companion' ); ?></button></form>
	<?php return ob_get_clean();
}

/** No writes on activation. Explicit capability and nonce protected demo import. */
function elinstyle_demo_pattern_content( $slug ) {
	if ( ! in_array( $slug, array( 'index', 'shop', 'article', 'about', 'contact', 'header', 'footer', 'product', 'cart', 'checkout', 'account' ), true ) ) { return ''; }
	$file = get_template_directory() . '/patterns/' . $slug . '.php';
	if ( ! is_readable( $file ) ) { return ''; }
	ob_start();
	include $file;
	return ob_get_clean();
}

/** Backward entry point now opens the explicit two-demo selection, without writes. */
function elinstyle_import_demo() {
 if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'دسترسی مجاز نیست.' ); }
 check_admin_referer( 'elinstyle_import' );
 wp_safe_redirect( admin_url( 'themes.php?page=elinstyle' ) ); exit;
}
add_action( 'admin_post_elinstyle_import', 'elinstyle_import_demo' );
