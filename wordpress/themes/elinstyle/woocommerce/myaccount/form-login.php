<?php
/** Approved auth shell; native WC_Form_Handler owns authentication. @version 9.9.0 */
defined( 'ABSPATH' ) || exit;
$register = isset( $_GET['mode'] ) && 'register' === $_GET['mode'] && 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
do_action( 'woocommerce_before_customer_login_form' ); ?>
<div class="wrap login-layout"><section class="auth-card"><h2><?php echo $register ? 'ساخت حساب' : 'دوباره خوش آمدی'; ?></h2><p class="auth-intro">با اطلاعات حساب الین‌استایل وارد شو.</p>
<?php if ( ! $register ) : ?>
<form method="post" class="woocommerce-form-login" data-account-auth>
<?php do_action( 'woocommerce_login_form_start' ); ?>
<div class="auth-field"><label for="username">نام کاربری یا ایمیل *</label><input id="username" name="username" type="text" autocomplete="username" required value="<?php echo isset( $_POST['username'] ) && is_string( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"></div>
<div class="auth-field"><label for="password">رمز عبور *</label><div class="password-control"><input id="password" name="password" type="password" autocomplete="current-password" required><button type="button" class="iconbtn password-eye" data-password-eye aria-label="نمایش رمز" aria-pressed="false"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button></div></div>
<?php do_action( 'woocommerce_login_form' ); ?>
<div class="auth-options"><label><input type="checkbox" name="rememberme" value="forever">مرا به خاطر بسپار</label><a href="<?php echo esc_url( wc_lostpassword_url() ); ?>">رمزت را فراموش کرده‌ای؟</a></div>
<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
<input type="hidden" name="elinstyle_auth" value="1"><input type="hidden" name="redirect" value="<?php echo esc_url( elinstyle_account_destination( isset( $_GET['redirect'] ) && is_string( $_GET['redirect'] ) ? wp_unslash( $_GET['redirect'] ) : ( is_account_page() ? home_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : wc_get_page_permalink( 'myaccount' ) ) ) ); ?>">
<button name="login" value="1" class="btn auth-submit" type="submit">ورود به حساب</button>
<?php do_action( 'woocommerce_login_form_end' ); ?></form>
<?php else : ?>
<form method="post" class="woocommerce-form-register" data-account-auth <?php do_action( 'woocommerce_register_form_tag' ); ?>>
<?php do_action( 'woocommerce_register_form_start' ); ?>
<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?><div class="auth-field"><label for="reg_username">نام کاربری *</label><input id="reg_username" name="username" autocomplete="username" required></div><?php endif; ?>
<div class="auth-field"><label for="reg_email">ایمیل *</label><input id="reg_email" name="email" type="email" autocomplete="email" required value="<?php echo isset( $_POST['email'] ) && is_string( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>"></div>
<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?><div class="auth-field"><label for="reg_password">رمز عبور *</label><input id="reg_password" name="password" type="password" autocomplete="new-password" required></div><?php else : ?><p class="auth-mode-note">لینک تعیین رمز از طریق جریان ایمیل فروشگاه ارسال می‌شود.</p><?php endif; ?>
<?php do_action( 'woocommerce_register_form' ); wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
<button type="submit" name="register" value="1" class="btn auth-submit">ساخت حساب</button><?php do_action( 'woocommerce_register_form_end' ); ?></form>
<?php endif; ?>
<div class="auth-join"><?php if ( $register ) : ?><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">بازگشت به ورود</a><?php elseif ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?><span>هنوز حساب نداری؟</span><a href="<?php echo esc_url( add_query_arg( 'mode', 'register', wc_get_page_permalink( 'myaccount' ) ) ); ?>">ساخت حساب</a><?php else : ?><p>ثبت‌نام غیرفعال است؛ برای راهنمایی با پشتیبانی تماس بگیر.</p><?php endif; ?></div><div class="auth-bottom"><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">ادامهٔ خرید بدون ورود</a></div></section>
<?php wc_get_template( 'myaccount/elinstyle-editorial.php' ); ?></div>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
