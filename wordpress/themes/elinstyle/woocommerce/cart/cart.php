<?php
/** Approved cart layout; maintained against WooCommerce cart.php @version 10.8.0. */
defined( 'ABSPATH' ) || exit;
do_action( 'woocommerce_before_cart' );
?>
<div class="commerce-layout cart-layout" data-elinstyle-cart>
<section class="cart-main"><div class="cart-toolbar"><h2><?php esc_html_e( 'کالاهای سبد', 'elinstyle' ); ?></h2><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'ادامهٔ خرید', 'elinstyle' ); ?></a></div>
<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
<?php do_action( 'woocommerce_before_cart_table' ); ?>
<div class="cart-table-wrap"><table class="cart-table shop_table cart"><thead><tr><th scope="col"><?php esc_html_e( 'محصول', 'elinstyle' ); ?></th><th scope="col"><?php esc_html_e( 'تعداد', 'elinstyle' ); ?></th><th scope="col"><?php esc_html_e( 'جمع کالا', 'elinstyle' ); ?></th></tr></thead><tbody>
<?php do_action( 'woocommerce_before_cart_contents' ); ?>
<?php foreach ( WC()->cart->get_cart() as $key => $item ) :
	$product = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $key );
	if ( ! $product || ! $product->exists() || $item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $item, $key ) ) { continue; }
	$url = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $item ) : '', $item, $key ); ?>
<tr class="cart_item"><td class="cart-product"><a href="<?php echo esc_url( $url ); ?>"><?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?></a><div><h3><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3><?php echo wp_kses_post( wc_get_formatted_cart_item_data( $item ) ); ?><p><?php esc_html_e( 'قیمت واحد:', 'elinstyle' ); ?> <?php echo wp_kses_post( WC()->cart->get_product_price( $product ) ); ?></p><a class="remove" href="<?php echo esc_url( wc_get_cart_remove_url( $key ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'حذف %s از سبد', 'elinstyle' ), $product->get_name() ) ); ?>"><?php esc_html_e( 'حذف از سبد', 'elinstyle' ); ?></a></div></td>
<td data-title="<?php esc_attr_e( 'تعداد', 'elinstyle' ); ?>"><div class="cart-quantity"><button type="button" data-cart-step="-1" aria-label="کاهش تعداد">−</button><?php woocommerce_quantity_input( array( 'input_name' => 'cart[' . $key . '][qty]', 'input_value' => $item['quantity'], 'min_value' => 1, 'max_value' => $product->get_max_purchase_quantity(), 'product_name' => $product->get_name() ), $product ); ?><button type="button" data-cart-step="1" aria-label="افزایش تعداد">+</button></div></td>
<td data-title="<?php esc_attr_e( 'جمع کالا', 'elinstyle' ); ?>"><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $product, $item['quantity'] ) ); ?></td></tr>
<?php endforeach; do_action( 'woocommerce_cart_contents' ); ?>
</tbody></table></div>
<button type="submit" class="btn" name="update_cart" value="1"><?php esc_html_e( 'به‌روزرسانی سبد', 'elinstyle' ); ?></button>
<?php if ( wc_coupons_enabled() ) : ?><div class="coupon-form coupon"><h3><?php esc_html_e( 'کد تخفیف داری؟', 'elinstyle' ); ?></h3><label for="coupon_code"><?php esc_html_e( 'کد تخفیف', 'elinstyle' ); ?></label><div><input id="coupon_code" name="coupon_code" type="text" autocomplete="off"><button type="submit" class="coupon-submit button" name="apply_coupon" value="1"><?php esc_html_e( 'اعمال کد', 'elinstyle' ); ?></button></div></div><?php endif; ?>
<?php do_action( 'woocommerce_cart_actions' ); wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); do_action( 'woocommerce_after_cart_contents' ); do_action( 'woocommerce_after_cart_table' ); ?>
</form><div class="shipping-help"><h3><?php esc_html_e( 'هزینهٔ ارسال بعد از انتخاب نشانی مشخص می‌شود.', 'elinstyle' ); ?></h3><p><?php esc_html_e( 'در مرحلهٔ بعد، مشخصات گیرنده و روش ارسال را انتخاب کن.', 'elinstyle' ); ?></p></div></section>
<aside class="commerce-summary cart-summary"><h2><?php esc_html_e( 'خلاصهٔ سبد', 'elinstyle' ); ?></h2><dl><div><dt><?php esc_html_e( 'تعداد کالا', 'elinstyle' ); ?></dt><dd><?php echo esc_html( number_format_i18n( WC()->cart->get_cart_contents_count() ) ); ?></dd></div><div><dt><?php esc_html_e( 'جمع کالا', 'elinstyle' ); ?></dt><dd><?php wc_cart_totals_subtotal_html(); ?></dd></div>
<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?><div><dt><?php echo esc_html( $code ); ?></dt><dd><?php wc_cart_totals_coupon_html( $coupon ); ?></dd></div><?php endforeach; ?>
<?php foreach ( WC()->cart->get_fees() as $fee ) : ?><div><dt><?php echo esc_html( $fee->name ); ?></dt><dd><?php wc_cart_totals_fee_html( $fee ); ?></dd></div><?php endforeach; ?>
<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : foreach ( WC()->cart->get_tax_totals() as $tax ) : ?><div><dt><?php echo esc_html( $tax->label ); ?></dt><dd><?php echo wp_kses_post( $tax->formatted_amount ); ?></dd></div><?php endforeach; endif; ?>
<div class="summary-rule"><dt><?php esc_html_e( 'ارسال', 'elinstyle' ); ?></dt><dd><?php esc_html_e( 'پس از انتخاب نشانی', 'elinstyle' ); ?></dd></div><div class="summary-rule summary-total"><dt><?php esc_html_e( 'جمع فعلی کالا', 'elinstyle' ); ?></dt><dd><?php echo wp_kses_post( elinstyle_cart_current_total() ); ?></dd></div></dl><small><?php esc_html_e( 'هزینهٔ ارسال در مرحلهٔ تسویه محاسبه می‌شود.', 'elinstyle' ); ?></small><a class="btn commerce-cta" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'ادامه برای ثبت نشانی', 'elinstyle' ); ?></a></aside>
</div>
<?php do_action( 'woocommerce_after_cart' ); ?>
