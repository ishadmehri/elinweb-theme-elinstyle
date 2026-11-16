<?php
defined( 'ABSPATH' ) || exit;
function elinstyle_elementor_bundle() {
	$file = __DIR__ . '/demo-content/elementor-0.4.0.json';
	if ( ! is_readable( $file ) ) { return new WP_Error( 'package', 'بستهٔ محتوای المنتور موجود نیست.' ); }
	$bundle = json_decode( file_get_contents( $file ), true );
	if ( ! is_array( $bundle ) || '0.4.0' !== $bundle['version'] || empty( $bundle['templates'] ) ) { return new WP_Error( 'package', 'ساختار بسته معتبر نیست.' ); }
	return $bundle;
}
function elinstyle_elementor_settings( $key ) {
	$bundle = elinstyle_elementor_bundle();
	return isset( $bundle['templates'][ $key ]['page_settings'] ) ? $bundle['templates'][ $key ]['page_settings'] : array( 'hide_title' => 'yes' );
}
function elinstyle_elementor_payload( $key, $job ) {
	$bundle = elinstyle_elementor_bundle(); if ( is_wp_error( $bundle ) ) { throw new RuntimeException( $bundle->get_error_message() ); }
	$elements = $bundle['templates'][ $key ]['content'];
	$replace = function ( $value ) use ( &$replace, $job ) {
		if ( is_array( $value ) ) {
			if ( isset( $value['url'] ) ) { foreach ( $job['media'] as $name => $id ) { if ( '{{image:' . $name . '}}' === $value['url'] ) { $value['id'] = (int) $id; } } }
			foreach ( $value as $key => $item ) { $value[ $key ] = $replace( $item ); } return $value;
		}
		if ( ! is_string( $value ) ) { return $value; }
		foreach ( $job['ids'] as $key => $id ) { $value = str_replace( '{{page:' . $key . '}}', elinstyle_demo_permalink( $id ), $value ); }
		foreach ( $job['media'] as $name => $id ) { $value = str_replace( '{{image:' . $name . '}}', wp_get_attachment_url( $id ), $value ); }
		$value = str_replace( '{{theme}}', get_template_directory_uri(), $value );
		$value = str_replace( '{{menu}}', (string) $job['menu'], $value );
		$value = str_replace( '{{popup}}', (string) $job['ids']['guide'], $value );
		return $value;
	};
	$elements = $replace( $elements );
	// Elementor's documented dynamic Popup URL tag uses the actual imported ID.
	$walk = function ( &$nodes ) use ( &$walk, $job ) { foreach ( $nodes as &$node ) { if ( isset( $node['settings']['elinstyle_popup_link'] ) ) { unset( $node['settings']['elinstyle_popup_link'] ); $settings = rawurlencode( wp_json_encode( array( 'popup' => $job['ids']['guide'], 'action' => 'open' ) ) ); $node['settings']['__dynamic__']['link'] = '[elementor-tag id="elinpopup" name="popup" settings="' . $settings . '"]'; } if ( ! empty( $node['elements'] ) ) { $walk( $node['elements'] ); } } };
	$walk( $elements ); return $elements;
}
function elinstyle_is_elementor_demo( $id = 0 ) {
	if ( ! $id && function_exists( 'is_shop' ) && is_shop() ) { $id = wc_get_page_id( 'shop' ); }
	if ( ! $id && function_exists( 'is_product' ) && is_product() ) { $active = get_option( 'elinstyle_active_demo' ); if ( ! empty( $active['type'] ) && 'elementor' === $active['type'] ) { return true; } }
	$id = $id ? $id : get_queried_object_id();
	return $id && get_post_meta( $id, '_elinstyle_import_job', true ) && 'builder' === get_post_meta( $id, '_elementor_edit_mode', true );
}
function elinstyle_is_elementor_content() {
	if ( elinstyle_is_elementor_demo() ) { return true; }
	$id = function_exists( 'is_shop' ) && is_shop() ? wc_get_page_id( 'shop' ) : get_queried_object_id();
	return $id && 'builder' === get_post_meta( $id, '_elementor_edit_mode', true );
}
add_action( 'elementor/widgets/register', function ( $manager ) { require_once __DIR__ . '/elementor-widgets.php'; $manager->register( new Elinstyle_Elementor_Catalog() ); } );
add_action( 'elementor/theme/register_locations', function ( $manager ) { $manager->register_all_core_location(); } );
add_filter( 'elementor/fonts/additional_fonts', function ( $fonts ) { $fonts['Yekan'] = 'system'; return $fonts; } );
add_filter( 'elementor/frontend/print_google_fonts', function ( $enabled ) { return elinstyle_is_elementor_demo() ? false : $enabled; } );
add_filter( 'formatted_woocommerce_price', function ( $formatted ) { return elinstyle_is_elementor_demo() ? strtr( $formatted, array( '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',','=>'٬' ) ) : $formatted; } );
add_filter( 'woocommerce_shipping_package_name', function ( $name, $index ) { return elinstyle_is_elementor_demo() && 0 === strpos( determine_locale(), 'fa' ) ? 'ارسال' . ( $index ? ' ' . ( $index + 1 ) : '' ) : $name; }, 10, 2 );
add_filter( 'elementor/widget/render_content', function ( $content, $widget ) {
	if ( 'login' !== $widget->get_name() || ! elinstyle_is_elementor_demo() || ! class_exists( 'WP_HTML_Tag_Processor' ) ) { return $content; }
	// Keep Pro's editable form UI; route its fields through the same native Woo nonce/auth handler.
	$tags = new WP_HTML_Tag_Processor( $content );
	$error = wc_notice_count( 'error' ) > 0;
	while ( $tags->next_tag() ) {
		if ( 'FORM' === $tags->get_tag() ) { $tags->set_attribute( 'method', 'post' ); $tags->set_attribute( 'action', get_permalink() ); $tags->set_attribute( 'data-elinstyle-login', '1' ); }
		if ( 'INPUT' === $tags->get_tag() && 'log' === $tags->get_attribute( 'name' ) ) { $tags->set_attribute( 'name', 'username' ); $tags->set_attribute( 'autocomplete', 'username' ); if ( isset( $_POST['elinstyle_auth'], $_POST['username'] ) && is_string( $_POST['username'] ) ) { $tags->set_attribute( 'value', wp_unslash( $_POST['username'] ) ); } }
		if ( 'INPUT' === $tags->get_tag() && 'pwd' === $tags->get_attribute( 'name' ) ) { $tags->set_attribute( 'name', 'password' ); $tags->set_attribute( 'autocomplete', 'current-password' ); $tags->set_attribute( 'value', '' ); if ( $error ) { $tags->set_attribute( 'aria-invalid', 'true' ); $tags->set_attribute( 'aria-describedby', 'elinstyle-pro-auth-error' ); } }
	}
	$content = $tags->get_updated_html();
	// Pro has no label controls for these auxiliary links. Use Woo's supported
	// recovery endpoint and localize only this owned Persian login document.
	$content = str_replace( 'Lost your password?', 'رمزت را فراموش کرده‌ای؟', $content );
	$content = str_replace( 'Remember Me', 'مرا به خاطر بسپار', $content );
	$link_tags = new WP_HTML_Tag_Processor( $content );
	while ( $link_tags->next_tag( array( 'tag_name' => 'A', 'class_name' => 'elementor-lost-password' ) ) ) { $link_tags->set_attribute( 'href', wc_lostpassword_url() ); }
	$content = $link_tags->get_updated_html();
	$hidden = wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce', true, false ) . '<input type="hidden" name="login" value="1"><input type="hidden" name="elinstyle_auth" value="1"><input type="hidden" name="redirect" value="' . esc_attr( elinstyle_account_destination( $widget->get_settings_for_display( 'redirect_url' )['url'] ?? wc_get_page_permalink( 'myaccount' ) ) ) . '">';
	$content = preg_replace( '/(<form\b[^>]*>)/', '$1' . $hidden, $content, 1 );
	$content = preg_replace( '/(<input\b[^>]*name="password"[^>]*>)/', '$1<button type="button" class="elinstyle-password-toggle" aria-label="نمایش رمز" aria-pressed="false"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>', $content, 1 );
	return ( $error ? '<div id="elinstyle-pro-auth-error" class="elementor-message elementor-message-danger" role="alert">' . wc_print_notices( true ) . '</div>' : '' ) . $content;
}, 20, 2 );
add_action( 'wp_enqueue_scripts', function () { if ( elinstyle_is_elementor_demo() ) { wp_enqueue_script( 'elinstyle-elementor-auth', plugins_url( 'elementor-auth.js', __FILE__ ), array(), filemtime( __DIR__ . '/elementor-auth.js' ), true ); } } );
function elinstyle_demo_elementor_part( $part ) {
	$active = get_option( 'elinstyle_active_demo' );
	if ( empty( $active['type'] ) || 'elementor' !== $active['type'] || ! defined( 'ELEMENTOR_PRO_VERSION' ) || ! class_exists( '\Elementor\Plugin' ) ) { return false; }
	$job = get_option( elinstyle_demo_option( 'elementor' ) );
	if ( empty( $job['ids'][ $part ] ) || 'complete' !== $job['status'] ) { return false; }
	// The operator explicitly selected these owned parts. Render that document
	// without editing or deleting any other Theme Builder display conditions.
	echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $job['ids'][ $part ], true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor rendering API.
	return true;
}
