<?php
/** Catalog presentation follows the HTML reference; all results come from Woo. */
defined( 'ABSPATH' ) || exit;

function elinstyle_catalog_values( $key ) {
	$raw = isset( $_GET[ $key ] ) ? wp_unslash( $_GET[ $key ] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filters.
	$values = is_array( $raw ) ? $raw : array( $raw );
	return array_values( array_unique( array_filter( array_map( function ( $value ) { return is_scalar( $value ) ? sanitize_title( (string) $value ) : ''; }, array_slice( $values, 0, 100 ) ) ) ) );
}

function elinstyle_catalog_number( $value, $decimals = 0 ) {
	return strtr( number_format_i18n( $value, $decimals ), array_merge( array_combine( range( 0, 9 ), preg_split( '//u', '۰۱۲۳۴۵۶۷۸۹', -1, PREG_SPLIT_NO_EMPTY ) ), array( ',' => '٬', '.' => '٫' ) ) );
}

function elinstyle_catalog_taxonomies() {
	return array( 'category' => 'product_cat', 'size' => 'pa_size', 'color' => 'pa_color', 'fabric' => 'pa_fabric' );
}

function elinstyle_catalog_exclusions() {
	$visibility = wc_get_product_visibility_term_ids();
	$ids = array( $visibility['exclude-from-catalog'] );
	if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) { $ids[] = $visibility['outofstock']; }
	return array_filter( array_map( 'absint', $ids ) );
}

function elinstyle_catalog_bounds() {
	global $wpdb;
	$excluded = elinstyle_catalog_exclusions();
	$visibility_sql = $excluded ? ' AND p.ID NOT IN (SELECT object_id FROM ' . $wpdb->term_relationships . ' WHERE term_taxonomy_id IN (' . implode( ',', $excluded ) . '))' : '';
	// Woo maintains this lookup for parent products, including variable price ranges.
	$prices = $wpdb->get_row( "SELECT MIN(l.min_price) AS low, MAX(l.max_price) AS high FROM {$wpdb->prefix}wc_product_meta_lookup l INNER JOIN {$wpdb->posts} p ON p.ID=l.product_id WHERE p.post_type='product' AND p.post_status='publish'" . $visibility_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- identifiers from WP; IDs are absint.
	$low = $prices && null !== $prices->low ? max( 0, (float) $prices->low ) : 0;
	$high = $prices && null !== $prices->high ? max( $low, (float) $prices->high ) : 0;
	if ( wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) && ! wc_prices_include_tax() ) {
		$rates = WC_Tax::get_rates( apply_filters( 'woocommerce_price_filter_widget_tax_class', '' ) );
		$low += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $low, $rates ) );
		$high += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $high, $rates ) );
	}
	$unit = $high > 0 ? pow( 10, max( 0, floor( log10( $high ) ) ) ) : 1;
	return array( 'min' => floor( $low / $unit ) * $unit, 'max' => max( $unit, ceil( $high / $unit ) * $unit ), 'step' => max( pow( 10, -wc_get_price_decimals() ), $unit / 10 ), 'limit' => max( $unit, ceil( $high / $unit ) * $unit ) + $unit );
}

function elinstyle_catalog_state() {
	$state = array();
	foreach ( elinstyle_catalog_taxonomies() as $key => $taxonomy ) { $state[ $key ] = elinstyle_catalog_values( $key ); }
	$state['keyword'] = elinstyle_query_value( 'keyword' );
	if ( '' === $state['keyword'] ) { $state['keyword'] = elinstyle_query_value( 'q' ); }
	$sort = elinstyle_query_value( 'sort' );
	$state['sort'] = in_array( $sort, array( 'cheap', 'expensive' ), true ) ? $sort : '';
	$state['catalog_page'] = max( 1, absint( elinstyle_query_value( 'catalog_page' ) ) );
	$state['bounds'] = elinstyle_catalog_bounds();
	$state['price'] = 'all' === elinstyle_query_value( 'price' ) ? 'all' : '';
	foreach ( array( 'minprice' => 'min', 'maxprice' => 'max' ) as $key => $default ) {
		$value = strtr( elinstyle_query_value( $key ), array_combine( preg_split( '//u', '۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩', -1, PREG_SPLIT_NO_EMPTY ), str_split( '01234567890123456789' ) ) );
		$state[ $key ] = is_numeric( $value ) && is_finite( (float) $value ) && (float) $value >= 0 ? (float) $value : $state['bounds'][ $default ];
	}
	$state['maxprice'] = max( $state['minprice'], $state['maxprice'] );
	return $state;
}

function elinstyle_catalog_url( $state, $changes = array() ) {
	$state = array_merge( $state, $changes );
	unset( $state['bounds'] );
	if ( 'all' === $state['price'] ) { unset( $state['minprice'], $state['maxprice'] ); }
	$state = array_filter( $state, function ( $value ) { return '' !== $value && array() !== $value; } );
	if ( isset( $state['catalog_page'] ) && 1 === $state['catalog_page'] ) { unset( $state['catalog_page'] ); }
	return add_query_arg( $state, wc_get_page_permalink( 'shop' ) );
}

function elinstyle_catalog_query( $state, $shop = true ) {
	$tax_query = array( array( 'taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => elinstyle_catalog_exclusions(), 'operator' => 'NOT IN' ) );
	if ( $shop ) { foreach ( elinstyle_catalog_taxonomies() as $key => $taxonomy ) { if ( $state[ $key ] && taxonomy_exists( $taxonomy ) ) { $tax_query[] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $state[ $key ] ); } } }
	$args = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => $shop ? 12 : 4, 'paged' => $shop ? $state['catalog_page'] : 1, 'tax_query' => $tax_query, 'meta_query' => WC()->query->get_meta_query(), 'orderby' => $shop ? array( 'menu_order' => 'ASC', 'ID' => 'ASC' ) : 'date', 'order' => 'DESC', 's' => $shop ? $state['keyword'] : '', 'elinstyle_catalog_query' => true );
	$clauses = function ( $parts, $query ) use ( $state, $shop ) {
		if ( ! $shop || ! $query->get( 'elinstyle_catalog_query' ) ) { return $parts; }
		global $wpdb;
		$parts['join'] .= " LEFT JOIN {$wpdb->prefix}wc_product_meta_lookup elinstyle_prices ON {$wpdb->posts}.ID=elinstyle_prices.product_id ";
		if ( 'all' !== $state['price'] ) {
			$min = $state['minprice']; $max = $state['maxprice'];
			// Match Woo's price-filter tax adjustment and overlap semantics.
			if ( wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) && ! wc_prices_include_tax() ) {
				$rates = WC_Tax::get_rates( apply_filters( 'woocommerce_price_filter_widget_tax_class', '' ) );
				$min -= WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $min, $rates ) );
				$max -= WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $max, $rates ) );
			}
			$parts['where'] .= $wpdb->prepare( ' AND NOT (%f < elinstyle_prices.min_price OR %f > elinstyle_prices.max_price)', $max, $min );
		}
		if ( 'cheap' === $state['sort'] ) { $parts['orderby'] = "elinstyle_prices.min_price ASC, {$wpdb->posts}.ID ASC"; }
		if ( 'expensive' === $state['sort'] ) { $parts['orderby'] = "elinstyle_prices.max_price DESC, {$wpdb->posts}.ID DESC"; }
		return $parts;
	};
	add_filter( 'posts_clauses', $clauses, 20, 2 );
	try { return new WP_Query( $args ); } finally { remove_filter( 'posts_clauses', $clauses, 20 ); }
}

function elinstyle_catalog_swatch( $term ) {
	$custom = sanitize_hex_color( get_term_meta( $term->term_id, 'color', true ) );
	$palette = array( 'cream' => '#EDE7D6', 'olive' => '#62694A', 'charcoal' => '#494B47', 'oat' => '#C4B99F' );
	return $custom ? $custom : ( isset( $palette[ $term->slug ] ) ? $palette[ $term->slug ] : '#DADCD2' );
}

function elinstyle_catalog_terms( $taxonomy ) {
	$terms = taxonomy_exists( $taxonomy ) ? get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true, 'orderby' => 'term_id', 'order' => 'ASC' ) ) : array();
	return is_wp_error( $terms ) ? array() : $terms;
}

function elinstyle_catalog_cards( $query, $shop ) {
	echo '<div class="' . ( $shop ? 'results' : 'grid' ) . '">';
	foreach ( $query->posts as $post ) {
		$product = wc_get_product( $post->ID );
		if ( ! $product || ! $product->is_visible() ) { continue; } ?>
		<article class="product"><a class="product-photo" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo wp_kses_post( $product->get_image( 'woocommerce_single' ) ); ?></a>
		<h3><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
		<p><?php echo esc_html( trim( $product->get_attribute( 'pa_fabric' ) . ' · ' . $product->get_attribute( 'pa_color' ), ' ·' ) ); ?></p>
		<div class="price"><?php echo elinstyle_price_markup( $product->get_price_html() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized price helper. ?></div>
		<?php if ( ! $product->is_in_stock() ) : ?><p><?php esc_html_e( 'ناموجود', 'elinstyle-companion' ); ?></p><?php endif; ?>
		</article>
	<?php }
	if ( ! $query->posts ) { echo '<div class="empty"><h3>' . esc_html__( 'لباسی با این انتخاب‌ها پیدا نشد', 'elinstyle-companion' ) . '</h3><p>' . esc_html__( 'یک فیلتر را حذف کن یا همهٔ محصولات را ببین.', 'elinstyle-companion' ) . '</p><a class="btn" data-catalog-reset href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'پاک‌کردن فیلترها', 'elinstyle-companion' ) . '</a></div>'; }
	echo '</div>';
}

function elinstyle_catalog( $attributes ) {
	if ( ! function_exists( 'wc_get_products' ) ) { return '<div class="catalog-status" role="status">' . esc_html__( 'برای نمایش محصولات واقعی، WooCommerce را نصب و فعال کنید. خرید در این حالت در دسترس نیست.', 'elinstyle-companion' ) . '</div>'; }
	$shop = isset( $attributes['mode'] ) && 'shop' === $attributes['mode'];
	$state = $shop ? elinstyle_catalog_state() : array();
	$query = elinstyle_catalog_query( $state, $shop );
	ob_start();
	if ( ! $shop ) { elinstyle_catalog_cards( $query, false ); return ob_get_clean(); }
	$base = wc_get_page_permalink( 'shop' );
	$taxonomies = elinstyle_catalog_taxonomies();
	$labels = array( 'category' => 'دسته‌بندی', 'size' => 'اندازه', 'color' => 'رنگ', 'fabric' => 'جنس' );
	$terms = array(); foreach ( $taxonomies as $key => $tax ) { $terms[ $key ] = elinstyle_catalog_terms( $tax ); }
	$count_args = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'tax_query' => array( array( 'taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => elinstyle_catalog_exclusions(), 'operator' => 'NOT IN' ) ) );
	$all_products = new WP_Query( $count_args );
	$currency = html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' );
	$caption_min = $state['minprice']; $caption_max = $state['maxprice']; $million = $caption_min >= 1000000 && 0.0 === fmod( $caption_min, 1000000 ) && 0.0 === fmod( $caption_max, 1000000 );
	$caption = sprintf( __( 'از %1$s تا %2$s %3$s', 'elinstyle-companion' ), elinstyle_catalog_number( $million ? $caption_min / 1000000 : $caption_min ), elinstyle_catalog_number( $million ? $caption_max / 1000000 : $caption_max ), ( $million ? __( 'میلیون ', 'elinstyle-companion' ) : '' ) . $currency );
	?>
	<section class="shop-layout wrap" data-elinstyle-catalog data-keyword="<?php echo esc_attr( $state['keyword'] ); ?>">
	<aside class="filters shop-filter-form"><form id="elinstyle-catalog-form" class="catalog-form" method="get" action="<?php echo esc_url( $base ); ?>">
	<div class="filter-title"><h3 tabindex="-1"><?php esc_html_e( 'فیلترها', 'elinstyle-companion' ); ?></h3><a class="textbutton" data-catalog-reset href="<?php echo esc_url( $base ); ?>"><?php esc_html_e( 'پاک‌کردن', 'elinstyle-companion' ); ?></a></div>
	<input type="hidden" name="keyword" value="<?php echo esc_attr( $state['keyword'] ); ?>">
	<?php if ( 'all' === $state['price'] ) : ?><input type="hidden" name="price" value="all"><?php endif; ?>
	<?php foreach ( array( 'category', 'size', 'color', 'price', 'fabric' ) as $key ) : ?>
	<fieldset><legend><?php echo esc_html( 'price' === $key ? __( 'محدودهٔ قیمت', 'elinstyle-companion' ) : $labels[ $key ] ); ?></legend>
	<?php if ( 'price' === $key ) : ?>
	<p data-catalog-price-caption><?php echo esc_html( $caption ); ?></p>
	<input data-catalog-range type="range" min="0" max="<?php echo esc_attr( max( $state['bounds']['limit'], $state['maxprice'] ) ); ?>" step="<?php echo esc_attr( $state['bounds']['step'] ); ?>" value="<?php echo esc_attr( $state['maxprice'] ); ?>" aria-label="<?php esc_attr_e( 'حداکثر قیمت', 'elinstyle-companion' ); ?>">
	<div class="price-inputs"><label><?php esc_html_e( 'از', 'elinstyle-companion' ); ?><input type="number" min="0" step="any" name="minprice" value="<?php echo esc_attr( $state['minprice'] ); ?>"></label><label><?php esc_html_e( 'تا', 'elinstyle-companion' ); ?><input type="number" min="0" step="any" name="maxprice" value="<?php echo esc_attr( $state['maxprice'] ); ?>"></label></div><small><?php echo esc_html( $currency ); ?></small>
	<?php else : ?>
	<?php if ( 'category' === $key ) : ?><a class="textbutton" data-all aria-current="<?php echo $state['category'] ? 'false' : 'true'; ?>" href="<?php echo esc_url( elinstyle_catalog_url( $state, array( 'category' => array(), 'catalog_page' => 1 ) ) ); ?>"><?php esc_html_e( 'همهٔ محصولات', 'elinstyle-companion' ); ?> <small><?php echo esc_html( elinstyle_catalog_number( $all_products->found_posts ) ); ?></small></a><?php endif; ?>
	<?php if ( 'size' === $key ) { echo '<div class="sizes">'; } ?>
	<?php foreach ( $terms[ $key ] as $term ) : ?>
	<label<?php if ( 'color' === $key ) { echo ' class="catalog-color"'; } ?>><input type="checkbox" name="<?php echo esc_attr( $key ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( in_array( $term->slug, $state[ $key ], true ) ); ?>><span<?php if ( 'color' === $key ) { echo ' style="--swatch:' . esc_attr( elinstyle_catalog_swatch( $term ) ) . '"'; } ?>><?php echo esc_html( $term->name ); ?></span><?php if ( 'category' === $key ) : ?> <small><?php $category_args = $count_args; $category_args['tax_query'][] = array( 'taxonomy' => 'product_cat', 'terms' => array( $term->term_id ) ); $category_query = new WP_Query( $category_args ); echo esc_html( elinstyle_catalog_number( $category_query->found_posts ) ); ?></small><?php endif; ?></label>
	<?php endforeach; ?>
	<?php if ( 'size' === $key ) : ?></div><small><?php esc_html_e( 'انتخاب اندازه، موجودی را تضمین نمی‌کند؛ برای موجودی صفحهٔ محصول را ببینید.', 'elinstyle-companion' ); ?></small><?php endif; ?>
	<?php if ( ! $terms[ $key ] ) : ?><small><?php esc_html_e( 'ویژگی‌ای برای فیلتر ثبت نشده است.', 'elinstyle-companion' ); ?></small><?php endif; ?>
	<?php endif; ?></fieldset>
	<?php endforeach; ?>
	<p class="muted"><?php esc_html_e( 'فیلترها با انتخاب شما', 'elinstyle-companion' ); ?><br><?php esc_html_e( 'نتایج را محدود می‌کنند.', 'elinstyle-companion' ); ?></p>
	<noscript><button class="btn" type="submit"><?php esc_html_e( 'اعمال فیلتر', 'elinstyle-companion' ); ?></button></noscript>
	</form></aside>
	<section class="shop-results"><div class="toolbar"><strong id="catalog-count"><?php echo esc_html( elinstyle_catalog_number( $query->found_posts ) . ' ' . __( 'محصول', 'elinstyle-companion' ) ); ?></strong><label><?php esc_html_e( 'مرتب‌سازی:', 'elinstyle-companion' ); ?> <select name="sort" form="elinstyle-catalog-form" aria-label="<?php esc_attr_e( 'مرتب‌سازی', 'elinstyle-companion' ); ?>"><?php foreach ( array( '' => 'پیشنهادی', 'cheap' => 'ارزان‌ترین', 'expensive' => 'گران‌ترین' ) as $value => $label ) : ?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $state['sort'], $value ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></label></div>
	<p class="catalog-live" role="status" aria-live="polite" data-catalog-status></p>
	<div class="chips" aria-label="<?php esc_attr_e( 'فیلترهای فعال', 'elinstyle-companion' ); ?>">
	<?php if ( $state['keyword'] ) : ?><a class="catalog-chip" data-catalog-chip href="<?php echo esc_url( elinstyle_catalog_url( $state, array( 'keyword' => '', 'catalog_page' => 1 ) ) ); ?>"><?php echo esc_html( __( 'جست‌وجو: ', 'elinstyle-companion' ) . $state['keyword'] ); ?> ×</a><?php endif; ?>
	<?php foreach ( $taxonomies as $key => $tax ) : foreach ( $state[ $key ] as $value ) : $term = get_term_by( 'slug', $value, $tax ); ?>
	<a class="catalog-chip" data-catalog-chip href="<?php echo esc_url( elinstyle_catalog_url( $state, array( $key => array_values( array_diff( $state[ $key ], array( $value ) ) ), 'catalog_page' => 1 ) ) ); ?>"><?php echo esc_html( $term ? $term->name : $value ); ?> ×</a>
	<?php endforeach; endforeach; ?>
	<?php if ( 'all' !== $state['price'] ) : ?><a class="catalog-chip" data-catalog-chip href="<?php echo esc_url( elinstyle_catalog_url( $state, array( 'price' => 'all', 'catalog_page' => 1 ) ) ); ?>"><?php echo esc_html( sprintf( __( 'قیمت: %1$s تا %2$s %3$s', 'elinstyle-companion' ), elinstyle_catalog_number( $state['minprice'] ), elinstyle_catalog_number( $state['maxprice'] ), $currency ) ); ?> ×</a><?php endif; ?>
	</div>
	<?php elinstyle_catalog_cards( $query, true ); ?>
	<div class="pagination"><?php if ( $query->found_posts ) : ?><p><?php echo esc_html( $query->max_num_pages <= 1 ? sprintf( __( 'هر %s محصول را دیدید.', 'elinstyle-companion' ), elinstyle_catalog_number( $query->found_posts ) ) : sprintf( __( '%1$s از %2$s محصول', 'elinstyle-companion' ), elinstyle_catalog_number( count( $query->posts ) ), elinstyle_catalog_number( $query->found_posts ) ) ); ?></p><?php endif; ?>
	<?php if ( $query->max_num_pages > 1 ) { echo wp_kses_post( paginate_links( array( 'base' => str_replace( '999999', '%#%', elinstyle_catalog_url( $state, array( 'catalog_page' => 999999 ) ) ), 'format' => '', 'current' => $state['catalog_page'], 'total' => $query->max_num_pages ) ) ); } elseif ( $query->found_posts ) { echo '<span aria-current="page">۱</span>'; } ?>
	</div></section></section>
	<?php return ob_get_clean();
}
