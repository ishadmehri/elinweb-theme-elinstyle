<?php
/** @version 9.3.0 */
defined( 'ABSPATH' ) || exit;
$user = wp_get_current_user(); do_action( 'woocommerce_before_account_navigation' ); ?>
<aside class="account-sidebar"><div class="sample-user"><span aria-hidden="true"><?php echo esc_html( mb_substr( $user->display_name, 0, 1 ) ); ?></span><strong><?php echo esc_html( $user->display_name ); ?></strong></div><nav aria-label="حساب کاربری">
<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?><a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php if ( wc_is_current_account_menu_item( $endpoint ) ) { echo 'aria-current="page"'; } ?>><?php echo esc_html( $label ); ?></a><?php endforeach; ?>
</nav><a class="return-shop" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">بازگشت به فروشگاه</a></aside>
<?php do_action( 'woocommerce_after_account_navigation' ); ?>
