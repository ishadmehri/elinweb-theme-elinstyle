<?php
/**
 * Plugin Name: Elinstyle Companion
 * Description: Dynamic WooCommerce catalog and explicit local Gutenberg demo import.
 * Version: 0.3.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Author: Iman Shadmehri
 * Author URI: https://elinweb.ir
 * Text Domain: elinstyle-companion
 * License: Pending project license selection; private review build
 */
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/product.php';
require_once __DIR__ . '/commerce.php';
require_once __DIR__ . '/catalog.php';
require_once __DIR__ . '/account.php';

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
	if ( ! in_array( $slug, array( 'index', 'shop', 'article', 'about', 'contact', 'header', 'footer', 'product' ), true ) ) { return ''; }
	$file = get_template_directory() . '/patterns/' . $slug . '.php';
	if ( ! is_readable( $file ) ) { return ''; }
	ob_start();
	include $file;
	return ob_get_clean();
}

function elinstyle_import_demo() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'دسترسی مجاز نیست.', 'elinstyle-companion' ) ); }
	check_admin_referer( 'elinstyle_import' );
	if ( 'elinstyle' !== get_template() ) { wp_die( esc_html__( 'ابتدا قالب Elinstyle را فعال کنید.', 'elinstyle-companion' ) ); }
	if ( get_option( 'elinstyle_demo_imported' ) ) { wp_safe_redirect( admin_url( 'tools.php?page=elinstyle-demo&done=1' ) ); exit; }
	$ids = array();
	$contents = array();
	$reuse = array();
	foreach ( array( 'index' => 'خانه', 'shop' => 'فروشگاه', 'article' => 'راهنمای استایل', 'about' => 'دربارهٔ ما', 'contact' => 'تماس با ما', 'header' => 'سربرگ الین‌استایل', 'footer' => 'پابرگ الین‌استایل' ) as $slug => $title ) {
		$is_part = in_array( $slug, array( 'header', 'footer' ), true );
		$existing = get_page_by_path( $slug, OBJECT, $is_part ? 'wp_block' : 'page' );
		if ( $existing ) {
			if ( 'shop' === $slug && (int) $existing->ID === (int) get_option( 'woocommerce_shop_page_id' ) && '' === trim( $existing->post_content ) ) {
				$reuse[ $slug ] = $existing->ID; // Only the empty Woo-created shop page; never overwrite user content.
			} else {
				wp_die( esc_html__( 'نشانی دمو از قبل وجود دارد؛ برای حفظ محتوا ورود متوقف شد. صفحات قبلی حذف یا بازنویسی نشده‌اند.', 'elinstyle-companion' ) );
			}
		}
		$contents[ $slug ] = elinstyle_demo_pattern_content( $slug );
		if ( '' === trim( $contents[ $slug ] ) ) { wp_die( esc_html__( 'محتوای الگوی دمو خالی است؛ ورود انجام نشد.', 'elinstyle-companion' ) ); }
	}
	foreach ( array( 'index' => 'خانه', 'shop' => 'فروشگاه', 'article' => 'راهنمای استایل', 'about' => 'دربارهٔ ما', 'contact' => 'تماس با ما', 'header' => 'سربرگ الین‌استایل', 'footer' => 'پابرگ الین‌استایل' ) as $slug => $title ) {
		$is_part = in_array( $slug, array( 'header', 'footer' ), true );
		$post_data = array( 'post_type' => $is_part ? 'wp_block' : 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_content' => $contents[ $slug ] );
		if ( isset( $reuse[ $slug ] ) ) { $post_data['ID'] = $reuse[ $slug ]; }
		$id = wp_insert_post( wp_slash( $post_data ), true );
		if ( is_wp_error( $id ) ) { wp_die( esc_html( $id->get_error_message() ) ); }
		$ids[ $slug ] = $id;
		if ( $is_part ) { update_option( 'elinstyle_part_' . $slug, $id ); }
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['index'] );
	if ( function_exists( 'wc_get_page_id' ) ) { update_option( 'woocommerce_shop_page_id', $ids['shop'] ); }
	update_option( 'elinstyle_demo_imported', $ids );
	wp_safe_redirect( admin_url( 'tools.php?page=elinstyle-demo&done=1' ) );
	exit;
}
add_action( 'admin_post_elinstyle_import', 'elinstyle_import_demo' );
add_action( 'admin_menu', function () {
	add_management_page( __( 'دموی الین‌استایل', 'elinstyle-companion' ), __( 'دموی الین‌استایل', 'elinstyle-companion' ), 'manage_options', 'elinstyle-demo', 'elinstyle_demo_screen' );
} );
function elinstyle_demo_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	echo '<div class="wrap"><h1>' . esc_html__( 'دموی گوتنبرگ الین‌استایل', 'elinstyle-companion' ) . '</h1><p>' . esc_html__( 'فقط برای سایت محلی خالی: پنج برگه و دو بخش مشترک ایجاد می‌شود و صفحهٔ خانه تغییر می‌کند. محصول و تنظیم پرداخت ساخته نمی‌شود. پیوندهای یکتا را روی نام نوشته بگذارید.', 'elinstyle-companion' ) . '</p>';
	if ( get_option( 'elinstyle_demo_imported' ) ) {
		echo '<p>' . esc_html__( 'دمو وارد شده است. صفحات را از بخش برگه‌ها و بخش‌های مشترک را از پیوندهای زیر ویرایش کنید.', 'elinstyle-companion' ) . '</p>';
		foreach ( array( 'header', 'footer' ) as $part ) {
			$id = absint( get_option( 'elinstyle_part_' . $part ) );
			echo '<p><a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . esc_html( $part ) . '</a></p>';
		}
	} else {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="elinstyle_import">';
		wp_nonce_field( 'elinstyle_import' );
		submit_button( __( 'ورود دموی محلی', 'elinstyle-companion' ) );
		echo '</form>';
	}
	echo '</div>';
}
