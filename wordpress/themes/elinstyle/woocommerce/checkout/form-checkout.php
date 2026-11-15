<?php
/** Approved checkout shell; WooCommerce owns fields, review, gateway and order processing. @version 9.4.0 */
defined( 'ABSPATH' ) || exit;
do_action( 'woocommerce_before_checkout_form', $checkout );
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) { echo '<p>' . esc_html__( 'برای ادامه وارد حساب کاربری شو.', 'elinstyle' ) . '</p>'; return; }
?>
<form name="checkout" method="post" class="checkout woocommerce-checkout commerce-layout checkout-layout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php esc_attr_e( 'ثبت سفارش و پرداخت', 'elinstyle' ); ?>">
<div class="checkout-main">
<?php if ( $checkout->get_checkout_fields() ) : do_action( 'woocommerce_checkout_before_customer_details' ); ?>
<div id="customer_details"><section class="recipient-panel"><?php do_action( 'woocommerce_checkout_billing' ); ?></section><section class="shipping-panel"><?php do_action( 'woocommerce_checkout_shipping' ); ?></section></div>
<?php do_action( 'woocommerce_checkout_after_customer_details' ); endif; ?>
<div class="checkout-bottom"><a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'بازگشت به سبد خرید', 'elinstyle' ); ?></a><p><?php esc_html_e( 'اطلاعات کارت بانکی در این فرم دریافت نمی‌شود.', 'elinstyle' ); ?></p></div></div>
<aside class="commerce-summary checkout-summary"><h2><?php esc_html_e( 'سفارش شما', 'elinstyle' ); ?></h2><a class="edit-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'ویرایش سبد', 'elinstyle' ); ?></a>
<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); do_action( 'woocommerce_checkout_before_order_review' ); ?>
<div id="order_review" class="woocommerce-checkout-review-order"><?php do_action( 'woocommerce_checkout_order_review' ); ?></div>
<?php do_action( 'woocommerce_checkout_after_order_review' ); ?></aside></form>
<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
