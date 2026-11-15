<?php
defined( 'ABSPATH' ) || exit;
function elinstyle_commerce_heading( $kind ) {
	$cart = 'cart' === $kind; ?>
	<div class="wrap commerce-heading"><p class="crumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'elinstyle' ); ?></a> / <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'فروشگاه', 'elinstyle' ); ?></a></p><h1><?php echo $cart ? esc_html__( 'سبد خرید شما', 'elinstyle' ) : esc_html__( 'ثبت سفارش و پرداخت', 'elinstyle' ); ?></h1><ol class="commerce-steps"><li <?php if ( $cart ) { echo 'aria-current="step"'; } ?>><a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><span>۱</span><?php esc_html_e( 'سبد خرید', 'elinstyle' ); ?></a></li><li <?php if ( ! $cart ) { echo 'aria-current="step"'; } ?>><span>۲</span><?php esc_html_e( 'ثبت نشانی و پرداخت', 'elinstyle' ); ?></li><li><span>۳</span><?php esc_html_e( 'نتیجهٔ سفارش', 'elinstyle' ); ?></li></ol></div>
	<?php
}
// Woo's address locale JS reapplies field metadata after every country change.
// Keep the visible requirement and order consistent with server validation.
add_filter( 'woocommerce_get_country_locale_default', function ( $fields ) {
	$fields['phone'] = array_merge( isset( $fields['phone'] ) ? $fields['phone'] : array(), array( 'required' => true, 'label' => __( 'شمارهٔ همراه', 'elinstyle' ), 'priority' => 25 ) );
	return $fields;
} );
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
	if ( isset( $fields['billing']['billing_email'] ) ) { $fields['billing']['billing_email']['required'] = false; }
	if ( isset( $fields['billing']['billing_phone'] ) ) { $fields['billing']['billing_phone']['required'] = true; $fields['billing']['billing_phone']['priority'] = 25; }
	if ( isset( $fields['billing']['billing_email'] ) ) { $fields['billing']['billing_email']['priority'] = 26; }
	foreach ( array( 'billing', 'shipping' ) as $group ) {
		foreach ( array( 'company', 'address_2' ) as $optional ) { $key = $group . '_' . $optional; if ( isset( $fields[ $group ][ $key ] ) && empty( $fields[ $group ][ $key ]['required'] ) ) { unset( $fields[ $group ][ $key ] ); } }
	}
	$labels = array( 'first_name' => 'نام', 'last_name' => 'نام خانوادگی', 'phone' => 'شمارهٔ همراه', 'email' => 'ایمیل', 'state' => 'استان', 'city' => 'شهر', 'address_1' => 'نشانی کامل', 'postcode' => 'کد پستی' );
	foreach ( array( 'billing', 'shipping' ) as $group ) { foreach ( $labels as $key => $label ) { $field = $group . '_' . $key; if ( isset( $fields[ $group ][ $field ] ) ) { $fields[ $group ][ $field ]['label'] = $label; } } }
	return $fields;
}, 999 );
function elinstyle_cart_current_total() {
	$cart = WC()->cart;
	$parts = array( $cart->get_cart_contents_total(), $cart->get_fee_total(), $cart->get_cart_contents_tax(), $cart->get_fee_tax() );
	return wc_price( wc_remove_number_precision( array_sum( array_map( 'wc_add_number_precision', $parts ) ) ) );
}
function elinstyle_checkout_shipping_ready() {
	// WC_Cart::needs_shipping() is false when no methods have been configured.
	// A physical item still needs a real delivery rate before this flow can proceed.
	$physical = false;
	foreach ( WC()->cart->get_cart() as $item ) { if ( $item['data']->needs_shipping() ) { $physical = true; break; } }
	if ( ! $physical ) { return true; }
	$country = WC()->customer->get_shipping_country();
	if ( ! $country ) { return false; }
	$fields = WC()->countries->get_address_fields( $country, 'shipping_' );
	foreach ( array( 'state', 'city', 'address_1', 'postcode' ) as $key ) {
		$getter = 'get_shipping_' . $key;
		if ( ! empty( $fields[ 'shipping_' . $key ]['required'] ) && ! WC()->customer->$getter() ) { return false; }
	}
	$chosen = WC()->session->get( 'chosen_shipping_methods', array() ); $packages = WC()->shipping()->get_packages();
	if ( ! $packages ) { return false; }
	foreach ( $packages as $key => $package ) { if ( empty( $chosen[ $key ] ) || empty( $package['rates'][ $chosen[ $key ] ] ) ) { return false; } }
	return true;
}
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	if ( ! elinstyle_checkout_shipping_ready() ) { $errors->add( 'elinstyle_shipping_required', __( 'برای ثبت سفارش، نشانی و روش ارسال معتبر لازم است.', 'elinstyle' ) ); }
}, 10, 2 );
add_filter( 'woocommerce_cart_totals_order_total_html', function ( $html ) {
	return is_checkout() && ! elinstyle_checkout_shipping_ready() ? esc_html__( 'در انتظار نشانی و روش ارسال معتبر', 'elinstyle' ) : $html;
} );
add_filter( 'woocommerce_order_button_html', function ( $html ) {
	$payment_missing = WC()->cart->needs_payment() && ! WC()->payment_gateways()->get_available_payment_gateways();
	$shipping_missing = ! elinstyle_checkout_shipping_ready();
	if ( $payment_missing || $shipping_missing ) { return '<p role="status">' . ( $payment_missing ? esc_html__( 'در حال حاضر روش پرداخت در دسترس نیست.', 'elinstyle' ) : esc_html__( 'ابتدا نشانی معتبر و روش ارسال را مشخص کن.', 'elinstyle' ) ) . '</p><button type="button" class="btn commerce-cta" id="place_order" disabled>' . esc_html__( 'پرداخت در دسترس نیست', 'elinstyle' ) . '</button>'; }
	return $html;
} );
