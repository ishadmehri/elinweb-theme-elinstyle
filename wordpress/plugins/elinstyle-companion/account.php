<?php
defined( 'ABSPATH' ) || exit;
function elinstyle_account_destination( $url ) {
	$fallback = wc_get_page_permalink( 'myaccount' );
	$url = wp_validate_redirect( $url, $fallback );
	$home = wp_parse_url( home_url( '/' ) ); $target = wp_parse_url( $url );
	return isset( $target['host'] ) && ( $target['host'] !== $home['host'] || ( $target['port'] ?? null ) !== ( $home['port'] ?? null ) ) ? $fallback : $url;
}
add_filter( 'woocommerce_login_redirect', 'elinstyle_account_destination', 999 );
add_filter( 'login_errors', function ( $message ) { return isset( $_POST['elinstyle_auth'] ) ? __( 'ورود انجام نشد؛ اطلاعات ورود را بررسی کن.', 'elinstyle-companion' ) : $message; } );
add_action( 'wp_loaded', function () {
	if ( ! function_exists( 'WC' ) || empty( $_POST['elinstyle_auth'] ) ) { return; }
	if ( isset( $_POST['wc_reset_password'], $_POST['user_login'] ) && ! isset( $_POST['reset_key'] ) ) {
		$nonce = isset( $_POST['woocommerce-lost-password-nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce-lost-password-nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lost_password' ) ) { wc_add_notice( __( 'نشست فرم معتبر نیست؛ صفحه را تازه کن.', 'elinstyle-companion' ), 'error' ); unset( $_POST['wc_reset_password'] ); return; }
		WC_Shortcode_My_Account::retrieve_password();
		wc_clear_notices();
		wp_safe_redirect( add_query_arg( 'reset-link-sent', 'true', wc_get_account_endpoint_url( 'lost-password' ) ) ); exit;
	}
}, 19 );
add_action( 'wp_loaded', function () {
	if ( isset( $_POST['elinstyle_auth'], $_POST['login'] ) && ! wp_verify_nonce( isset( $_POST['woocommerce-login-nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce-login-nonce'] ) ) : '', 'woocommerce-login' ) ) { wc_add_notice( __( 'نشست فرم معتبر نیست؛ صفحه را تازه کن.', 'elinstyle-companion' ), 'error' ); }
}, 21 );
add_action( 'template_redirect', function () {
	if ( function_exists( 'is_account_page' ) && ( is_account_page() || is_page( 'login' ) ) ) {
		nocache_headers();
		if ( is_page( 'login' ) && is_user_logged_in() ) { wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) ); exit; }
	}
} );
function elinstyle_render_account() {
	if ( ! function_exists( 'WC' ) ) { return '<p>WooCommerce باید فعال باشد.</p>'; }
	if ( ! WC()->cart ) { wc_load_cart(); }
	ob_start(); ?>
	<div class="account-native"><div class="wrap page-heading account-heading"><div class="crumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a> / حساب کاربری</div><h1><?php echo is_user_logged_in() ? 'حساب کاربری' : 'ورود به حساب'; ?></h1><p><?php echo is_user_logged_in() ? 'سفارش‌ها و اطلاعات حسابت را اینجا مدیریت کن.' : 'برای دیدن سفارش‌ها و مدیریت اطلاعات حسابت وارد شو.'; ?></p></div>
	<?php echo do_shortcode( '[woocommerce_my_account]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>'; return ob_get_clean();
}
add_action( 'init', function () { register_block_type( 'elinstyle/account', array( 'api_version' => 3, 'editor_script' => 'elinstyle-blocks', 'render_callback' => 'elinstyle_render_account' ) ); } );
add_filter( 'woocommerce_account_menu_items', function ( $items ) { unset( $items['downloads'] ); return $items; } );
add_action( 'woocommerce_lostpassword_form', function () { echo '<input type="hidden" name="elinstyle_auth" value="1">'; } );
