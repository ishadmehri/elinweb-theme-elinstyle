<?php
/** Explicit, namespaced demo imports. No content writes on activation. PHP7.4+. */
defined( 'ABSPATH' ) || exit;
const ELINSTYLE_DEMO_VERSION = '0.4.0';
function elinstyle_demo_option( $type ) { return 'elinstyle_demo_' . $type . '_' . str_replace( '.', '_', ELINSTYLE_DEMO_VERSION ); }
function elinstyle_demo_permalink( $id ) {
	$post = get_post( $id ); if ( ! $post ) { return ''; }
	// Resolve links before publishing the batch without baking draft ?page_id URLs
	// into Woo shop documents. Let WordPress generate the future canonical URL.
	$published = clone $post; $published->post_status = 'publish';
	return get_permalink( $published );
}
function elinstyle_demo_plan( $type ) {
	$names = array( 'home' => 'خانه', 'shop' => 'فروشگاه', 'article' => 'راهنمای استایل', 'about' => 'درباره ما', 'contact' => 'تماس', 'cart' => 'سبد خرید', 'checkout' => 'پرداخت', 'login' => 'ورود', 'account' => 'حساب', 'product' => 'قالب محصول', 'header' => 'سربرگ', 'footer' => 'پابرگ', 'guide' => 'راهنمای اندازه' );
	$plan = array();
	foreach ( $names as $key => $title ) { $part = in_array( $key, array( 'product', 'header', 'footer', 'guide' ), true ); $plan[ $key ] = array( 'slug' => 'elin-' . $type . '-040-' . $key, 'title' => $title . ( 'elementor' === $type ? ' — المنتور' : ' — گوتنبرگ' ), 'post_type' => $part ? ( 'elementor' === $type ? 'elementor_library' : 'wp_block' ) : 'page', 'document_type' => $part ? ( 'product' === $key ? 'product' : ( 'guide' === $key ? 'popup' : $key ) ) : 'wp-page' ); }
	if ( 'elementor' === $type ) { $plan['kit'] = array( 'slug' => 'elin-elementor-040-kit', 'title' => 'Site Settings مستقل الین‌استایل', 'post_type' => 'elementor_library', 'document_type' => 'kit' ); }
	return $plan;
}
function elinstyle_demo_media_plan( $type ) {
	$media = array( 'editorial' => array( 'assets/images/editorial.png', 'image/png' ), 'catalog' => array( 'assets/images/catalog.png', 'image/png' ) );
	if ( 'elementor' === $type ) { $media['logo'] = array( 'assets/brand/logo.svg', 'image/svg+xml' ); $media['logo-light'] = array( 'assets/brand/logo-light.svg', 'image/svg+xml' ); }
	return $media;
}
function elinstyle_demo_preflight( $type ) {
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_theme_options' ) ) { return new WP_Error( 'permission', 'دسترسی مدیریت و تنظیمات قالب لازم است.' ); }
	if ( ! in_array( $type, array( 'gutenberg', 'elementor' ), true ) ) { return new WP_Error( 'type', 'نوع دمو معتبر نیست.' ); }
	if ( 'elinstyle' !== get_template() ) { return new WP_Error( 'theme', 'ابتدا قالب الین‌استایل را فعال کنید.' ); }
	if ( ! function_exists( 'WC' ) ) { return new WP_Error( 'woo', 'برای صفحات خرید و حساب، WooCommerce فعال لازم است. هیچ محتوایی نوشته نشد.' ); }
	foreach ( elinstyle_demo_media_plan( $type ) as $asset ) { if ( ! is_readable( get_template_directory() . '/' . $asset[0] ) ) { return new WP_Error( 'media', 'رسانهٔ لازم در بستهٔ قالب موجود نیست؛ ورود انجام نشد.' ); } }
	if ( 'elementor' === $type ) {
		if ( ! defined( 'ELEMENTOR_VERSION' ) || ! class_exists( '\Elementor\Plugin' ) ) { return new WP_Error( 'elementor', 'Elementor فعال لازم است؛ هنوز داده‌ای نوشته نشده است.' ); }
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) || ! class_exists( '\ElementorPro\Plugin' ) ) { return new WP_Error( 'pro', 'Elementor Pro فعال لازم است؛ ورود پیش از نوشتن متوقف شد.' ); }
		if ( version_compare( ELEMENTOR_VERSION, '4.3.3', '<' ) || version_compare( ELEMENTOR_PRO_VERSION, '4.3.3', '<' ) || version_compare( get_bloginfo( 'version' ), '6.8', '<' ) ) { return new WP_Error( 'version', 'این بستهٔ المنتور نیازمند Core و Pro نسخهٔ 4.3.3 یا بالاتر و WordPress 6.8 یا بالاتر است.' ); }
		if ( ! class_exists( '\ElementorPro\License\API' ) || ! \ElementorPro\License\API::is_license_active() ) { return new WP_Error( 'license', 'مجوز معتبر Pro در API خود افزونه تأیید نشد؛ ورود انجام نمی‌شود.' ); }
		$bundle = elinstyle_elementor_bundle();
		if ( is_wp_error( $bundle ) ) { return $bundle; }
		$widgets = \Elementor\Plugin::$instance->widgets_manager->get_widget_types();
		foreach ( $bundle['required_widgets'] as $name ) { if ( ! isset( $widgets[ $name ] ) ) { return new WP_Error( 'widget', 'ویجت موردنیاز در نسخهٔ نصب‌شده موجود نیست: ' . $name ); } }
		$types = \Elementor\Plugin::$instance->documents->get_document_types();
		foreach ( array( 'wp-page', 'header', 'footer', 'product', 'popup' ) as $name ) { if ( ! isset( $types[ $name ] ) ) { return new WP_Error( 'document', 'نوع سند Pro در نسخهٔ فعال ثبت نشده: ' . $name ); } }
	}
	$job = get_option( elinstyle_demo_option( $type ), array() );
	if ( $job && 'rolled_back' !== $job['status'] ) {
		foreach ( $job['ids'] as $key => $id ) { $post = get_post( $id ); if ( ! $post || 'trash' === $post->post_status || $job['id'] !== get_post_meta( $id, '_elinstyle_import_job', true ) ) { return new WP_Error( 'changed', 'یک شیء ورودی حذف یا مالکیتش عوض شده است؛ برای حفظ داده ورود متوقف شد: ' . $key ); } }
	}
	foreach ( elinstyle_demo_plan( $type ) as $key => $item ) {
		$existing = get_page_by_path( $item['slug'], OBJECT, array( 'page', 'wp_block', 'elementor_library' ) );
		if ( $existing && ( ! $job || empty( $job['ids'][ $key ] ) || (int) $job['ids'][ $key ] !== (int) $existing->ID ) ) { return new WP_Error( 'conflict', 'تعارض نشانی؛ هیچ محتوایی بازنویسی نمی‌شود: ' . $item['slug'] ); }
	}
	$menu = wp_get_nav_menu_object( 'elin-' . $type . '-040-menu' );
	if ( $menu && ( ! $job || empty( $job['menu'] ) || (int) $job['menu'] !== (int) $menu->term_id ) ) { return new WP_Error( 'menu_conflict', 'نام منوی دمو از قبل متعلق به محتوای دیگری است.' ); }
	return true;
}
function elinstyle_demo_lock() {
	global $wpdb;
	$key = 'elinstyle_demo_import_lock'; $old = get_option( $key );
	if ( $old && isset( $old['expires'] ) && $old['expires'] < time() ) { $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name=%s AND option_value=%s", $key, maybe_serialize( $old ) ) ); wp_cache_delete( $key, 'options' ); }
	$lock = array( 'token' => wp_generate_uuid4(), 'expires' => time() + 180 );
	return add_option( $key, $lock, '', false ) ? $lock : new WP_Error( 'locked', 'درون‌ریزی دیگری در حال اجراست؛ کمی بعد دوباره تلاش کنید. قفل حذف نشد.' );
}
function elinstyle_demo_checkpoint( &$job, $lock ) {
	$current = get_option( 'elinstyle_demo_import_lock' );
	if ( ! $current || $current['token'] !== $lock['token'] ) { throw new RuntimeException( 'مالکیت قفل از دست رفت؛ عملیات متوقف شد.' ); }
	$current['expires'] = time() + 180; update_option( 'elinstyle_demo_import_lock', $current, false );
	// Native Documents also create library-type terms. Journal only new terms
	// actually attached to our own documents; pre-existing types remain shared.
	if ( 'elementor' === $job['type'] && isset( $job['native_terms_before'] ) && $job['ids'] ) {
		$terms = wp_get_object_terms( array_values( $job['ids'] ), 'elementor_library_type' );
		if ( ! is_wp_error( $terms ) ) { foreach ( $terms as $term ) {
			if ( in_array( (int) $term->term_id, $job['native_terms_before'], true ) || isset( $job['native_terms'][ $term->term_id ] ) ) { continue; }
			$owner = get_term_meta( $term->term_id, '_elinstyle_import_job', true );
			if ( $owner && $owner !== $job['id'] ) { continue; }
			update_term_meta( $term->term_id, '_elinstyle_import_job', $job['id'] );
			$job['native_terms'][ $term->term_id ] = hash( 'sha256', wp_json_encode( array( $term->name, $term->slug, $term->description ) ) );
		} }
	}
	$job['updated'] = time(); update_option( elinstyle_demo_option( $job['type'] ), $job, false );
}
function elinstyle_demo_fingerprint( $id ) {
	$post = get_post( $id );
	return $post ? hash( 'sha256', wp_json_encode( array( $post->post_content, $post->post_title, $post->post_name, $post->post_status, get_post_meta( $id, '_elementor_data', true ), get_post_meta( $id, '_elementor_page_settings', true ) ) ) ) : '';
}
function elinstyle_demo_media_fingerprint( $id ) {
	$file = get_attached_file( $id );
	return hash( 'sha256', wp_json_encode( array( elinstyle_demo_fingerprint( $id ), get_post_meta( $id, '_wp_attachment_image_alt', true ), wp_get_attachment_metadata( $id ), $file, $file && is_file( $file ) ? hash_file( 'sha256', $file ) : '' ) ) );
}
function elinstyle_demo_menu_item_fingerprint( $id ) {
	$meta = array(); foreach ( array( '_menu_item_object_id', '_menu_item_object', '_menu_item_type', '_menu_item_url', '_menu_item_menu_item_parent', '_menu_item_target', '_menu_item_classes', '_menu_item_xfn' ) as $key ) { $meta[ $key ] = get_post_meta( $id, $key, true ); }
	return hash( 'sha256', wp_json_encode( array( elinstyle_demo_fingerprint( $id ), $meta ) ) );
}
function elinstyle_demo_menu_fingerprint( $id ) {
	$menu = wp_get_nav_menu_object( $id ); return $menu ? hash( 'sha256', wp_json_encode( array( $menu->name, $menu->slug, $menu->description ) ) ) : '';
}
function elinstyle_demo_menu_entries( $job ) {
	$shop = elinstyle_demo_permalink( $job['ids']['shop'] );
	return array( 'new' => array( 'تازه‌ها', $shop ), 'women' => array( 'زنانه', add_query_arg( 'category', 'women', $shop ) ), 'men' => array( 'مردانه', add_query_arg( 'category', 'men', $shop ) ), 'basics' => array( 'لباس‌های پایه', add_query_arg( 'category', 'basics', $shop ) ), 'collections' => array( 'مجموعه‌ها', $shop ) );
}
function elinstyle_demo_content( $key, $job ) {
	if ( 'guide' === $key ) {
		$bundle = elinstyle_elementor_bundle(); if ( is_wp_error( $bundle ) ) { throw new RuntimeException( $bundle->get_error_message() ); }
		$content = '<!-- wp:heading --><h2 class="wp-block-heading">راهنمای اندازه و تن‌خور</h2><!-- /wp:heading --><!-- wp:paragraph --><p>جدول نمونهٔ اندازهٔ بدن، به سانتی‌متر؛ آن را با جدول اختصاصی همان محصول مقایسه کنید.</p><!-- /wp:paragraph -->';
		foreach ( $bundle['templates']['guide']['content'][0]['elements'] as $element ) { if ( 'tabs' !== ( $element['widgetType'] ?? '' ) ) { continue; } foreach ( $element['settings']['tabs'] as $tab ) { $content .= '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html( $tab['tab_title'] ) . '</h3><!-- /wp:heading --><!-- wp:table --><figure class="wp-block-table">' . preg_replace( '/<caption>.*?<\/caption>/s', '', $tab['tab_content'] ) . '</figure><!-- /wp:table -->'; } }
		return $content;
	}
	$key = 'home' === $key ? 'index' : ( 'login' === $key ? 'account' : $key );
	$content = elinstyle_demo_pattern_content( $key );
	if ( '' === trim( $content ) ) { throw new RuntimeException( 'محتوای الگو آماده نیست: ' . $key ); }
	$map = array();
	foreach ( array( 'shop', 'article', 'about', 'contact', 'cart', 'checkout', 'account' ) as $route ) { $map[ home_url( '/' . ( 'account' === $route ? 'my-account' : $route ) . '/' ) ] = elinstyle_demo_permalink( $job['ids'][ $route ] ); }
	$map[ home_url( '/' ) . '"' ] = elinstyle_demo_permalink( $job['ids']['home'] ) . '"';
	foreach ( $job['media'] as $name => $id ) { $map[ get_template_directory_uri() . '/assets/images/' . $name . '.png' ] = wp_get_attachment_url( $id ); }
	return strtr( $content, $map );
}
function elinstyle_demo_run( $type ) {
	$pre = elinstyle_demo_preflight( $type ); if ( is_wp_error( $pre ) ) { return $pre; }
	$lock = elinstyle_demo_lock(); if ( is_wp_error( $lock ) ) { return $lock; }
	$job = get_option( elinstyle_demo_option( $type ), array() );
	try {
		// Recheck under the shared lock before any import writes.
		$pre = elinstyle_demo_preflight( $type ); if ( is_wp_error( $pre ) ) { return $pre; }
		if ( $job && 'complete' === $job['status'] ) { return $job; }
		if ( ! $job || 'rolled_back' === $job['status'] ) { $job = array( 'id' => wp_generate_uuid4(), 'type' => $type, 'version' => ELINSTYLE_DEMO_VERSION, 'status' => 'building', 'ids' => array(), 'media' => array(), 'menu' => 0, 'menu_items' => array(), 'fingerprints' => array(), 'done' => array(), 'error' => '' );
			if ( 'elementor' === $type ) { $terms = get_terms( array( 'taxonomy' => 'elementor_library_type', 'hide_empty' => false, 'fields' => 'ids' ) ); if ( is_wp_error( $terms ) ) { throw new RuntimeException( $terms->get_error_message() ); } $job['native_terms_before'] = array_map( 'intval', $terms ); $job['native_terms'] = array(); }
		}
		$job['status'] = 'building'; $job['error'] = ''; elinstyle_demo_checkpoint( $job, $lock );
		require_once ABSPATH . 'wp-admin/includes/image.php';
		foreach ( elinstyle_demo_media_plan( $type ) as $name => $asset ) {
			if ( ! empty( $job['media'][ $name ] ) ) { if ( ! get_post( $job['media'][ $name ] ) || $job['id'] !== get_post_meta( $job['media'][ $name ], '_elinstyle_import_job', true ) ) { throw new RuntimeException( 'رسانهٔ متعلق به عملیات موجود نیست؛ ورود متوقف شد.' ); } continue; }
			$bytes = file_get_contents( get_template_directory() . '/' . $asset[0] );
			$svg = 'image/svg+xml' === $asset[1];
			// Only the two trusted, bundled path-only logos pass this scoped upload.
			// This does not enable SVG uploads for users or accept request file paths.
			if ( $svg && preg_match( '/<\s*(?:script|foreignObject|iframe|image|use|animate|set)\b|(?:href|on\w+)\s*=/i', $bytes ) ) { throw new RuntimeException( 'لوگوی بسته حاوی محتوای مجاز نیست.' ); }
			$mime_filter = function ( $mimes ) { $mimes['svg'] = 'image/svg+xml'; return $mimes; };
			if ( $svg ) { add_filter( 'upload_mimes', $mime_filter ); }
			try { $upload = wp_upload_bits( 'elin-' . $type . '-040-' . $name . ( $svg ? '.svg' : '.png' ), null, $bytes ); }
			finally { if ( $svg ) { remove_filter( 'upload_mimes', $mime_filter ); } }
			if ( $upload['error'] ) { throw new RuntimeException( $upload['error'] ); }
			$id = wp_insert_attachment( array( 'post_mime_type' => $asset[1], 'post_title' => $name . ' — ' . $type, 'meta_input' => array( '_elinstyle_import_job' => $job['id'] ) ), $upload['file'], 0, true );
			if ( is_wp_error( $id ) ) { wp_delete_file( $upload['file'] ); throw new RuntimeException( $id->get_error_message() ); }
			$job['media'][ $name ] = $id; elinstyle_demo_checkpoint( $job, $lock );
			$metadata = $svg ? array( 'width' => 188, 'height' => 42, 'file' => _wp_relative_upload_path( $upload['file'] ) ) : wp_generate_attachment_metadata( $id, $upload['file'] );
			wp_update_attachment_metadata( $id, $metadata );
			update_post_meta( $id, '_wp_attachment_image_alt', 0 === strpos( $name, 'logo' ) ? 'الین‌استایل' : ( 'editorial' === $name ? 'استایل روزمرهٔ زنانه و مردانه' : 'انتخاب‌های لباس روزمره' ) );
			$job['media_fingerprints'][ $name ] = elinstyle_demo_media_fingerprint( $id ); elinstyle_demo_checkpoint( $job, $lock );
		}
		foreach ( elinstyle_demo_plan( $type ) as $key => $item ) {
			if ( isset( $job['ids'][ $key ] ) ) { continue; }
			$data = array( 'post_title' => $item['title'], 'post_name' => $item['slug'], 'post_type' => $item['post_type'], 'post_status' => 'draft', 'post_content' => '', 'meta_input' => array( '_elinstyle_import_job' => $job['id'], '_elinstyle_demo_key' => $key ) );
			if ( 'elementor' === $type ) { $meta = $data['meta_input']; unset( $data['meta_input'] ); $document = \Elementor\Plugin::$instance->documents->create( $item['document_type'], $data, $meta ); $id = is_wp_error( $document ) ? $document : $document->get_main_id(); }
			else { $id = wp_insert_post( wp_slash( $data ), true ); }
			if ( is_wp_error( $id ) || ! $id ) { throw new RuntimeException( is_wp_error( $id ) ? $id->get_error_message() : 'ساخت سند ناموفق بود.' ); }
			$job['ids'][ $key ] = $id; $job['fingerprints'][ $key ] = elinstyle_demo_fingerprint( $id ); elinstyle_demo_checkpoint( $job, $lock );
			if ( get_post_field( 'post_name', $id ) !== $item['slug'] ) { throw new RuntimeException( 'نشانی سند هنگام ایجاد تغییر کرد؛ برای جلوگیری از تعارض، عملیات متوقف شد.' ); }
			// Hook for observability and deterministic failure testing; never from request input.
			do_action( 'elinstyle_demo_object_created', $key, $id );
		}
		if ( ! $job['menu'] ) { $menu = wp_create_nav_menu( 'elin-' . $type . '-040-menu' ); if ( is_wp_error( $menu ) ) { throw new RuntimeException( $menu->get_error_message() ); } $job['menu'] = $menu; update_term_meta( $menu, '_elinstyle_import_job', $job['id'] ); $job['menu_fingerprint'] = elinstyle_demo_menu_fingerprint( $menu ); elinstyle_demo_checkpoint( $job, $lock ); }
		foreach ( elinstyle_demo_menu_entries( $job ) as $key => $entry ) {
			if ( isset( $job['menu_items'][ $key ] ) ) { continue; }
			$id = wp_update_nav_menu_item( $job['menu'], 0, array( 'menu-item-type' => 'custom', 'menu-item-url' => $entry[1], 'menu-item-status' => 'publish', 'menu-item-title' => $entry[0] ) );
			if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
			update_post_meta( $id, '_elinstyle_import_job', $job['id'] ); $job['menu_items'][ $key ] = $id; $job['menu_item_fingerprints'][ $key ] = elinstyle_demo_menu_item_fingerprint( $id ); elinstyle_demo_checkpoint( $job, $lock );
			do_action( 'elinstyle_demo_menu_item_created', $key, $id );
		}
		foreach ( $job['ids'] as $key => $id ) {
			if ( in_array( $key, $job['done'], true ) ) { continue; }
			if ( elinstyle_demo_fingerprint( $id ) !== $job['fingerprints'][ $key ] ) { throw new RuntimeException( 'سند نیمه‌کاره توسط کاربر تغییر کرده است؛ بازنویسی متوقف شد: ' . $key ); }
			if ( 'elementor' === $type ) {
				$payload = elinstyle_elementor_payload( $key, $job );
				$doc = \Elementor\Plugin::$instance->documents->get( $id );
				$settings = elinstyle_elementor_settings( $key );
				if ( ! $doc || ! $doc->save( array( 'elements' => $payload, 'settings' => $settings ) ) ) { throw new RuntimeException( 'ذخیرهٔ معتبر Elementor ناموفق بود.' ); }
				$result = wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ), true );
			} else { $result = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => elinstyle_demo_content( $key, $job ), 'post_status' => 'publish' ) ), true ); }
			if ( is_wp_error( $result ) ) { throw new RuntimeException( $result->get_error_message() ); }
			$job['fingerprints'][ $key ] = elinstyle_demo_fingerprint( $id ); $job['done'][] = $key; elinstyle_demo_checkpoint( $job, $lock );
		}
		$job['status'] = 'complete'; elinstyle_demo_checkpoint( $job, $lock ); return $job;
	} catch ( Throwable $error ) {
		$job['status'] = 'failed'; $job['error'] = $error->getMessage();
		$current = get_option( 'elinstyle_demo_import_lock' );
		if ( ! $current || $current['token'] !== $lock['token'] ) { return new WP_Error( 'lock_lost', 'مالکیت قفل از دست رفت؛ عملیات قبلی بدون بازنویسی دفتر عملیات جدید متوقف شد.' ); }
		elinstyle_demo_checkpoint( $job, $lock );
		return new WP_Error( 'import_failed', 'ورود کامل نشد. اشیای جدید ثبت شده‌اند و امکان ادامه یا بازگردانی محدود دارید. ' . $error->getMessage() );
	} finally { $current = get_option( 'elinstyle_demo_import_lock' ); if ( $current && $lock['token'] === $current['token'] ) { delete_option( 'elinstyle_demo_import_lock' ); } }
}
function elinstyle_demo_rollback( $type ) {
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_theme_options' ) || ! in_array( $type, array( 'gutenberg', 'elementor' ), true ) ) { return new WP_Error( 'permission', 'درخواست مجاز نیست.' ); }
	$job = get_option( elinstyle_demo_option( $type ) );
	if ( ! $job || ! in_array( $job['status'], array( 'failed', 'building' ), true ) ) { return new WP_Error( 'state', 'فقط عملیات ناقص قابل بازگردانی محدود است.' ); }
	$lock = elinstyle_demo_lock(); if ( is_wp_error( $lock ) ) { return $lock; }
	try {
		$job = get_option( elinstyle_demo_option( $type ) );
		if ( ! $job || ! in_array( $job['status'], array( 'failed', 'building' ), true ) ) { return new WP_Error( 'state', 'وضعیت عملیات تغییر کرده است؛ بازگردانی انجام نشد.' ); }
		foreach ( $job['ids'] as $key => $id ) { if ( get_post( $id ) && $job['id'] === get_post_meta( $id, '_elinstyle_import_job', true ) && elinstyle_demo_fingerprint( $id ) !== $job['fingerprints'][ $key ] ) { return new WP_Error( 'edited', 'کاربر یکی از اسناد جدید را ویرایش کرده؛ برای حفظ آن rollback متوقف شد.' ); } }
		foreach ( $job['media'] as $key => $id ) { if ( get_post( $id ) && ( empty( $job['media_fingerprints'][ $key ] ) || elinstyle_demo_media_fingerprint( $id ) !== $job['media_fingerprints'][ $key ] ) ) { return new WP_Error( 'edited_media', 'رسانهٔ جدید تغییر کرده یا خط مبنای معتبر ندارد؛ بازگردانی برای حفظ آن متوقف شد.' ); } }
		foreach ( $job['menu_items'] as $key => $id ) { if ( get_post( $id ) && ( empty( $job['menu_item_fingerprints'][ $key ] ) || elinstyle_demo_menu_item_fingerprint( $id ) !== $job['menu_item_fingerprints'][ $key ] ) ) { return new WP_Error( 'edited_menu', 'آیتم منوی جدید ویرایش شده است؛ بازگردانی متوقف شد.' ); } }
		if ( $job['menu'] ) {
			$items = wp_get_nav_menu_items( $job['menu'], array( 'post_status' => 'any' ) ); $ids = $items ? wp_list_pluck( $items, 'ID' ) : array();
			if ( empty( $job['menu_fingerprint'] ) || elinstyle_demo_menu_fingerprint( $job['menu'] ) !== $job['menu_fingerprint'] || array_diff( $ids, array_values( $job['menu_items'] ) ) ) { return new WP_Error( 'edited_menu', 'منوی جدید تغییر کرده یا آیتمی خارج از عملیات دارد؛ بازگردانی متوقف شد.' ); }
		}
		global $wpdb;
		if ( ! empty( $job['ids']['kit'] ) && (int) get_option( 'elementor_active_kit' ) === (int) $job['ids']['kit'] ) { return new WP_Error( 'selected_kit', 'بستهٔ تنظیمات این عملیات برای سایت انتخاب شده است؛ بازگردانی برای حفظ تنظیمات متوقف شد.' ); }
		foreach ( $job['native_terms'] ?? array() as $id => $fingerprint ) {
			$term = get_term( $id, 'elementor_library_type' ); if ( ! $term || is_wp_error( $term ) ) { continue; }
			$objects = get_objects_in_term( $id, 'elementor_library_type' );
			if ( $job['id'] !== get_term_meta( $id, '_elinstyle_import_job', true ) || hash( 'sha256', wp_json_encode( array( $term->name, $term->slug, $term->description ) ) ) !== $fingerprint || is_wp_error( $objects ) || array_diff( array_map( 'intval', $objects ), array_map( 'intval', $job['ids'] ) ) ) { return new WP_Error( 'shared_term', 'نوع الگوی جدید ویرایش شده یا در سندی خارج از عملیات استفاده شده؛ بازگردانی متوقف شد.' ); }
		}
		foreach ( $job['media'] as $id ) {
			if ( ! get_post( $id ) ) { continue; }
			$url = wp_get_attachment_url( $id );
			$plain = '%' . $wpdb->esc_like( $url ) . '%';
			$json = '%' . $wpdb->esc_like( str_replace( '/', '\\/', $url ) ) . '%';
			$used = $wpdb->get_var( $wpdb->prepare( "SELECT pm.post_id FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->postmeta} owner ON owner.post_id=pm.post_id AND owner.meta_key='_elinstyle_import_job' WHERE ((pm.meta_key='_thumbnail_id' AND pm.meta_value=%s) OR (pm.meta_key='_elementor_data' AND (pm.meta_value LIKE %s OR pm.meta_value LIKE %s))) AND (owner.meta_value IS NULL OR owner.meta_value<>%s) LIMIT 1", (string) $id, $plain, $json, $job['id'] ) );
			$used_content = $wpdb->get_var( $wpdb->prepare( "SELECT p.ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} owner ON owner.post_id=p.ID AND owner.meta_key='_elinstyle_import_job' WHERE p.post_content LIKE %s AND (owner.meta_value IS NULL OR owner.meta_value<>%s) LIMIT 1", $plain, $job['id'] ) );
			$attachment = get_post( $id );
			if ( $used || $used_content || ( $attachment && $attachment->post_parent && ! in_array( (int) $attachment->post_parent, array_map( 'intval', $job['ids'] ), true ) ) ) { return new WP_Error( 'shared_media', 'رسانه در محتوایی خارج از عملیات استفاده شده است؛ بازگردانی متوقف شد.' ); }
		}
		foreach ( array_merge( array_values( $job['ids'] ), array_values( $job['menu_items'] ) ) as $id ) {
			if ( $job['id'] !== get_post_meta( $id, '_elinstyle_import_job', true ) ) { continue; }
			if ( ! empty( $job['ids']['kit'] ) && (int) $id === (int) $job['ids']['kit'] ) {
				// Use Elementor's own revert API for this unselected, owned kit. Its
				// normal post deletion route intentionally exits to a confirmation UI.
				$manager = \Elementor\Plugin::$instance->kits_manager;
				$manager->revert( (int) $id, (int) get_option( 'elementor_active_kit' ), (int) get_option( 'elementor_previous_kit' ) );
				if ( get_post( $id ) ) { return new WP_Error( 'kit_rollback', 'بازگردانی بستهٔ تنظیمات از API المنتور انجام نشد؛ عملیات متوقف شد.' ); }
			} else { wp_delete_post( $id, true ); }
		}
		foreach ( $job['media'] as $id ) { if ( $job['id'] === get_post_meta( $id, '_elinstyle_import_job', true ) ) { wp_delete_attachment( $id, true ); } }
		if ( $job['menu'] && $job['id'] === get_term_meta( $job['menu'], '_elinstyle_import_job', true ) ) { wp_delete_nav_menu( $job['menu'] ); }
		foreach ( $job['native_terms'] ?? array() as $id => $fingerprint ) { if ( $job['id'] === get_term_meta( $id, '_elinstyle_import_job', true ) ) { wp_delete_term( $id, 'elementor_library_type' ); } }
		$job['status'] = 'rolled_back'; elinstyle_demo_checkpoint( $job, $lock ); return $job;
	} finally { $current = get_option( 'elinstyle_demo_import_lock' ); if ( $current && $current['token'] === $lock['token'] ) { delete_option( 'elinstyle_demo_import_lock' ); } }
}
function elinstyle_demo_activate( $type, $selection ) {
	$pre = elinstyle_demo_preflight( $type ); if ( is_wp_error( $pre ) ) { return $pre; }
	$job = get_option( elinstyle_demo_option( $type ) ); if ( ! $job || 'complete' !== $job['status'] ) { return new WP_Error( 'incomplete', 'فقط دمو با ورود کامل قابل انتخاب است.' ); }
	$lock = elinstyle_demo_lock(); if ( is_wp_error( $lock ) ) { return $lock; }
	try {
		$before = array(); $changes = array();
		if ( ! empty( $selection['home'] ) ) { $changes['show_on_front'] = 'page'; $changes['page_on_front'] = $job['ids']['home']; }
		if ( ! empty( $selection['woo'] ) ) { foreach ( array( 'shop' => 'shop', 'cart' => 'cart', 'checkout' => 'checkout', 'account' => 'myaccount' ) as $key => $option ) { $changes[ 'woocommerce_' . $option . '_page_id' ] = $job['ids'][ $key ]; } }
		if ( ! empty( $selection['parts'] ) ) { $changes['elinstyle_active_demo'] = array( 'type' => $type, 'version' => ELINSTYLE_DEMO_VERSION ); if ( 'gutenberg' === $type ) { foreach ( array( 'header', 'footer', 'product' ) as $key ) { $changes[ 'elinstyle_part_' . $key ] = $job['ids'][ $key ]; } } }
		if ( 'elementor' === $type && ! empty( $selection['site_settings'] ) ) { $changes['elementor_previous_kit'] = get_option( 'elementor_active_kit' ); $changes['elementor_active_kit'] = $job['ids']['kit']; }
		if ( ! empty( $selection['menu'] ) ) { $locations = get_theme_mod( 'nav_menu_locations', array() ); $locations['elinstyle_primary'] = $job['menu']; $changes[ 'theme_mods_' . get_stylesheet() ] = array_merge( get_option( 'theme_mods_' . get_stylesheet(), array() ), array( 'nav_menu_locations' => $locations ) ); }
		foreach ( $changes as $option => $value ) { $before[ $option ] = get_option( $option ); }
		$job['activation_journal'][] = array( 'time' => time(), 'before' => $before, 'after' => $changes ); elinstyle_demo_checkpoint( $job, $lock );
		foreach ( $changes as $option => $value ) { update_option( $option, $value ); }
		return $job;
	} finally { $current = get_option( 'elinstyle_demo_import_lock' ); if ( $current && $current['token'] === $lock['token'] ) { delete_option( 'elinstyle_demo_import_lock' ); } }
}
