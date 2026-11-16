<?php
defined( 'ABSPATH' ) || exit;
add_action( 'admin_menu', function () {
	add_theme_page( 'الین‌استایل', 'الین‌استایل', 'manage_options', 'elinstyle', 'elinstyle_demo_screen' );
	$hook = add_management_page( 'دموی الین‌استایل', 'دموی الین‌استایل', 'manage_options', 'elinstyle-demo', 'elinstyle_demo_screen' );
	add_action( 'load-' . $hook, function () { wp_safe_redirect( admin_url( 'themes.php?page=elinstyle' ) ); exit; } );
} );
function elinstyle_demo_admin_form( $type, $operation, $label ) {
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="elinstyle_demo"><input type="hidden" name="demo_type" value="' . esc_attr( $type ) . '"><input type="hidden" name="operation" value="' . esc_attr( $operation ) . '">';
	wp_nonce_field( 'elinstyle_demo_' . $type . '_' . $operation );
	if ( 'activate' === $operation ) { foreach ( array( 'home' => 'این خانه به صفحهٔ نخست سایت نسبت داده شود.', 'woo' => 'صفحات فروشگاه، سبد، پرداخت و حساب ووکامرس به این دمو جابه‌جا شوند.', 'menu' => 'منوی مستقل دمو به جایگاه اصلی قالب نسبت داده شود.', 'parts' => 'سربرگ، پابرگ و قالب محصول این دمو در سایت فعال شوند.', 'site_settings' => 'کیت مستقل Site Settings این دمو در Elementor فعال شود؛ کیت قبلی حذف/ویرایش نمی‌شود.' ) as $key => $text ) { if ( 'gutenberg' === $type && 'site_settings' === $key ) { continue; } echo '<p><label><input type="checkbox" name="apply[' . esc_attr( $key ) . ']" value="1"> ' . esc_html( $text ) . '</label></p>'; } echo '<p><label><input type="checkbox" required name="confirm_activation" value="1">تغییر فقط تنظیمات انتخاب‌شده را تأیید می‌کنم.</label></p>'; }
	submit_button( $label, 'activate' === $operation ? 'secondary' : 'primary', 'submit', false ); echo '</form>';
}
function elinstyle_demo_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	echo '<div class="wrap"><p>نمایش ← الین‌استایل ← درون‌ریزی دمو</p><h1>درون‌ریزی دمو</h1><p>نسخه ' . esc_html( ELINSTYLE_DEMO_VERSION ) . ' — یک نوع را صریحاً انتخاب کنید. ورود، تبدیل صفحات قبلی نیست. اسناد با نشانی‌های جدا ایجاد می‌شوند و ویرایش شما بازنویسی نمی‌شود. انتخاب خانه، صفحات Woo و کیت/منو مرحلهٔ جدا و اختیاری است.</p>';
	$message = get_transient( 'elinstyle_demo_notice_' . get_current_user_id() ); if ( $message ) { echo '<div class="notice ' . ( $message['error'] ? 'notice-error' : 'notice-success' ) . '"><p>' . esc_html( $message['text'] ) . '</p></div>'; delete_transient( 'elinstyle_demo_notice_' . get_current_user_id() ); }
	echo '<div style="display:flex;gap:24px;flex-wrap:wrap">';
	foreach ( array( 'gutenberg' => 'گوتنبرگ', 'elementor' => 'المنتور + Pro' ) as $type => $title ) {
		$pre = elinstyle_demo_preflight( $type ); $job = get_option( elinstyle_demo_option( $type ) );
		echo '<section style="background:white;padding:24px;max-width:560px;flex:1;min-width:280px;border:1px solid #dadcd2"><h2>' . esc_html( $title ) . '</h2><img alt="پیش‌نمایش سبک الین‌استایل" style="width:100%;max-height:180px;object-fit:cover" src="' . esc_url( get_template_directory_uri() . '/assets/images/editorial.png' ) . '"><p>' . ( 'gutenberg' === $type ? 'بلوک‌های هسته و بلوک‌های پویای Woo؛ بدون وابستگی Elementor.' : 'ویجت‌های کلاسیک Elementor/Pro با کنترل‌های بومی؛ نیازمند Pro مجاز و ویجت‌های ثبت‌شده.' ) . '</p><p>خانه، فروشگاه، مقاله، درباره، تماس، سبد، پرداخت، ورود، حساب، هدر/فوتر، قالب محصول و راهنمای اندازه؛ رسانه‌های محلی مستقل (دو تصویر در گوتنبرگ؛ دو تصویر و دو لوگو در المنتور) و یک منوی مستقل، بدون ساخت محصول/سفارش/مشتری.</p>';
		if ( is_wp_error( $pre ) ) { echo '<p role="alert"><strong>' . esc_html( $pre->get_error_message() ) . '</strong></p>'; }
		if ( $job ) { echo '<p>وضعیت عملیات: <strong>' . esc_html( $job['status'] ) . '</strong></p>'; if ( ! empty( $job['error'] ) ) { echo '<p>' . esc_html( $job['error'] ) . '</p>'; } }
		if ( ! is_wp_error( $pre ) && ( ! $job || 'complete' !== $job['status'] ) ) { elinstyle_demo_admin_form( $type, 'import', $job && 'failed' === $job['status'] ? 'ادامهٔ عملیات ثبت‌شده' : 'انتخاب و ورود این دمو' ); }
		if ( $job && 'complete' === $job['status'] ) {
			echo '<p>ورود کامل است؛ اجرای مجدد همان شناسه‌ها را برمی‌گرداند و محتوا را بازنویسی نمی‌کند.</p><ul>';
			foreach ( $job['ids'] as $key => $id ) { echo '<li>' . esc_html( $key ) . ': <a href="' . esc_url( get_edit_post_link( $id ) ) . '">ویرایش</a> · <a href="' . esc_url( get_permalink( $id ) ) . '">پیش‌نمایش</a></li>'; } echo '</ul>';
			if ( ! is_wp_error( $pre ) ) { elinstyle_demo_admin_form( $type, 'import', 'بررسی ورود مجدد بدون بازنویسی' ); echo '<h3>فعال‌سازی اختیاری تنظیمات انتخابی</h3>'; elinstyle_demo_admin_form( $type, 'activate', 'اعمال فقط موارد تأییدشده' ); }
		}
		if ( $job && in_array( $job['status'], array( 'failed', 'building' ), true ) ) { echo '<p>بازگردانی فقط اشیای جدید متعلق به همین عملیات را حذف می‌کند؛ اگر ویرایش کاربر تشخیص داده شود متوقف می‌شود.</p>'; elinstyle_demo_admin_form( $type, 'rollback', 'بازگردانی محدود عملیات ناقص' ); }
		echo '</section>';
	} echo '</div></div>';
}
add_action( 'admin_post_elinstyle_demo', function () {
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_theme_options' ) ) { wp_die( 'دسترسی مجاز نیست.', '', array( 'response' => 403 ) ); }
	$type = isset( $_POST['demo_type'] ) && is_string( $_POST['demo_type'] ) ? sanitize_key( $_POST['demo_type'] ) : '';
	$op = isset( $_POST['operation'] ) && is_string( $_POST['operation'] ) ? sanitize_key( $_POST['operation'] ) : '';
	if ( ! in_array( $type, array( 'gutenberg', 'elementor' ), true ) || ! in_array( $op, array( 'import', 'rollback', 'activate' ), true ) ) { wp_die( 'درخواست معتبر نیست.', '', array( 'response' => 400 ) ); }
	check_admin_referer( 'elinstyle_demo_' . $type . '_' . $op );
	if ( 'activate' === $op && empty( $_POST['confirm_activation'] ) ) { wp_die( 'تأیید تغییر تنظیمات لازم است.', '', array( 'response' => 400 ) ); }
	$selection = array(); foreach ( array( 'home', 'woo', 'parts', 'menu', 'site_settings' ) as $key ) { $selection[ $key ] = isset( $_POST['apply'][ $key ] ) && '1' === $_POST['apply'][ $key ]; }
	$result = 'rollback' === $op ? elinstyle_demo_rollback( $type ) : ( 'activate' === $op ? elinstyle_demo_activate( $type, $selection ) : elinstyle_demo_run( $type ) );
	set_transient( 'elinstyle_demo_notice_' . get_current_user_id(), array( 'error' => is_wp_error( $result ), 'text' => is_wp_error( $result ) ? $result->get_error_message() : ( 'activate' === $op ? 'تنظیمات انتخاب‌شده اعمال شدند.' : ( 'rollback' === $op ? 'اشیای جدید عملیات ناقص بازگردانی شدند.' : 'ورود این دمو کامل و شناسه‌ها ثبت شد.' ) ) ), 120 );
	wp_safe_redirect( admin_url( 'themes.php?page=elinstyle' ) ); exit;
} );


