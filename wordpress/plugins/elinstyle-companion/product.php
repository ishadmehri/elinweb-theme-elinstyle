<?php
/** Product presentation data and Store API revision validation. PHP 7.4+. */
defined( 'ABSPATH' ) || exit;
function elinstyle_price_markup( $html ) {
	$parts = wp_html_split( wp_kses_post( $html ) );
	foreach ( $parts as &$part ) { if ( '' !== $part && '<' !== $part[0] ) { $part = esc_html( strtr( html_entity_decode( $part, ENT_QUOTES, 'UTF-8' ), array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹', ',' => '٬' ) ) ); } }
	return implode( '', $parts );
}

function elinstyle_product_revision( $product ) {
	return wp_hash( wp_json_encode( array( $product->get_id(), $product->get_price(), $product->get_regular_price(), $product->get_stock_quantity(), $product->get_stock_status(), $product->get_backorders(), $product->get_sold_individually() ) ) );
}

function elinstyle_product_variant( $product, $attributes = array() ) {
	$max = $product->get_max_purchase_quantity();
	return array(
		'id' => $product->get_id(), 'attributes' => $attributes, 'sku' => $product->get_sku(),
		'available' => $product->is_purchasable() && $product->is_in_stock(), 'max' => $max,
		'price' => elinstyle_price_markup( wc_price( wc_get_price_to_display( $product ) ) ),
		'regular' => $product->is_on_sale() ? elinstyle_price_markup( wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) ) ) : '',
		// Percentage is display-only; WooCommerce remains the source of monetary totals.
		'discount' => $product->is_on_sale() && (float) $product->get_regular_price() > 0 ? (int) round( 100 * ( 1 - (float) $product->get_price() / (float) $product->get_regular_price() ) ) : 0,
		'image' => wp_get_attachment_image_url( $product->get_image_id( 'edit' ), 'full' ),
		'revision' => elinstyle_product_revision( $product ),
	);
}

function elinstyle_product_data( $product ) {
	$groups = array(); $variants = array();
	if ( $product->is_type( 'variable' ) ) {
		foreach ( $product->get_variation_attributes() as $taxonomy => $options ) {
			$values = array();
			foreach ( $options as $option ) {
				$term = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', $option, $taxonomy ) : null;
				$values[] = array( 'value' => $option, 'label' => $term ? $term->name : $option );
			}
			$groups[] = array( 'name' => $taxonomy, 'label' => wc_attribute_label( $taxonomy, $product ), 'values' => $values );
		}
		foreach ( $product->get_children() as $id ) {
			$variation = wc_get_product( $id );
			if ( $variation && 'publish' === $variation->get_status() && $variation->variation_is_visible() ) {
				$variants[] = elinstyle_product_variant( $variation, $variation->get_attributes() );
			}
		}
	} else { $variants[] = elinstyle_product_variant( $product ); }
	usort( $groups, function ( $a, $b ) { return ( false !== strpos( $a['name'], 'color' ) ? 0 : 1 ) <=> ( false !== strpos( $b['name'], 'color' ) ? 0 : 1 ); } );
	$gallery = array();
	$custom = $product->get_meta( '_elinstyle_gallery_views' );
	if ( is_array( $custom ) ) {
		foreach ( $custom as $view ) {
			$url = wp_get_attachment_image_url( absint( $view['image_id'] ?? 0 ), 'full' );
			if ( ! $url ) { continue; }
			$box = isset( $view['box'] ) && preg_match( '/^\d+(?:\.\d+)? \d+(?:\.\d+)? \d+(?:\.\d+)? \d+(?:\.\d+)?$/', $view['box'] ) ? $view['box'] : '0 0 100 100';
			$gallery[] = array( 'url' => esc_url_raw( $url ), 'label' => sanitize_text_field( $view['label'] ?? '' ), 'caption' => sanitize_text_field( $view['caption'] ?? '' ), 'box' => $box, 'width' => absint( $view['width'] ?? 100 ), 'height' => absint( $view['height'] ?? 100 ) );
		}
	}
	if ( ! $gallery ) {
		foreach ( array_unique( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) ) as $id ) {
			$url = wp_get_attachment_image_url( $id, 'full' ); $meta = wp_get_attachment_metadata( $id );
			if ( $url ) { $gallery[] = array( 'url' => $url, 'label' => get_the_title( $id ), 'caption' => wp_get_attachment_caption( $id ), 'box' => '0 0 ' . absint( $meta['width'] ?? 100 ) . ' ' . absint( $meta['height'] ?? 100 ), 'width' => absint( $meta['width'] ?? 100 ), 'height' => absint( $meta['height'] ?? 100 ) ); }
		}
	}
	if ( ! $gallery ) { $gallery[] = array( 'url' => wc_placeholder_img_src(), 'label' => $product->get_name(), 'caption' => '', 'box' => '0 0 100 100', 'width' => 100, 'height' => 100 ); }
	return array( 'id' => $product->get_id(), 'name' => $product->get_name(), 'sku' => $product->get_sku(), 'price' => elinstyle_price_markup( $product->get_price_html() ), 'groups' => $groups, 'variants' => $variants, 'gallery' => $gallery );
}

function elinstyle_current_product( $attributes = array() ) {
	if ( ! function_exists( 'wc_get_product' ) ) { return false; }
	$id = is_singular( 'product' ) ? get_queried_object_id() : absint( $attributes['productId'] ?? 0 );
	if ( ! $id ) { $ids = wc_get_products( array( 'status' => 'publish', 'type' => 'variable', 'limit' => 1, 'return' => 'ids' ) ); $id = $ids ? $ids[0] : 0; }
	$product = wc_get_product( $id );
	return $product && 'publish' === $product->get_status() && $product->is_type( array( 'simple', 'variable' ) ) ? $product : false;
}

function elinstyle_render_product( $attributes ) {
	$product = elinstyle_current_product( $attributes );
	if ( ! $product ) { return '<p>' . esc_html__( 'محصول قابل نمایش در دسترس نیست.', 'elinstyle-companion' ) . '</p>'; }
	$data = elinstyle_product_data( $product );
	ob_start(); include __DIR__ . '/views/product.php'; return ob_get_clean();
}

function elinstyle_render_product_content( $attributes ) {
	$product = elinstyle_current_product( $attributes );
	if ( ! $product ) { return ''; }
	$part = $attributes['part'] ?? 'description';
	if ( 'description' === $part ) { return wp_kses_post( wpautop( $product->get_description() ) ); }
	if ( 'attributes' === $part ) {
		$html = '<dl>';
		foreach ( $product->get_attributes() as $attribute ) {
			$html .= '<div><dt>' . esc_html( wc_attribute_label( $attribute->get_name(), $product ) ) . '</dt><dd>' . esc_html( $product->get_attribute( $attribute->get_name() ) ) . '</dd></div>';
		}
		return $html . '</dl>';
	}
	$ids = wc_get_related_products( $product->get_id(), 3 );
	if ( count( $ids ) < 3 ) { $ids = array_merge( $ids, wc_get_products( array( 'status' => 'publish', 'limit' => 3 - count( $ids ), 'exclude' => array_merge( array( $product->get_id() ), $ids ), 'return' => 'ids', 'visibility' => 'visible' ) ) ); }
	$html = '<div class="product-related-grid">';
	foreach ( $ids as $id ) {
		$item = wc_get_product( $id ); if ( ! $item || ! $item->is_visible() ) { continue; }
		$html .= '<article class="product"><a class="product-photo" href="' . esc_url( $item->get_permalink() ) . '">' . wp_kses_post( $item->get_image( 'woocommerce_thumbnail' ) ) . '</a><h3><a href="' . esc_url( $item->get_permalink() ) . '">' . esc_html( $item->get_name() ) . '</a></h3><p>' . esc_html( $item->get_attribute( 'pa_fabric' ) ) . '</p><div class="price">' . wp_kses_post( $item->get_price_html() ) . '</div></article>';
	}
	return $html . '</div>';
}

add_action( 'init', function () {
	foreach ( array( 'product' => 'elinstyle_render_product', 'product-content' => 'elinstyle_render_product_content' ) as $name => $callback ) {
		register_block_type( 'elinstyle/' . $name, array( 'api_version' => 3, 'editor_script' => 'elinstyle-blocks', 'attributes' => array( 'productId' => array( 'type' => 'number', 'default' => 0 ), 'part' => array( 'type' => 'string', 'default' => 'description' ) ), 'render_callback' => $callback ) );
	}
	register_rest_route( 'elinstyle/v1', '/products/(?P<id>\d+)', array( 'methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => function ( $request ) {
		$product = elinstyle_current_product( array( 'productId' => absint( $request['id'] ) ) );
		if ( ! $product ) { return new WP_Error( 'not_found', __( 'محصول پیدا نشد.', 'elinstyle-companion' ), array( 'status' => 404 ) ); }
		$response = rest_ensure_response( elinstyle_product_data( $product ) ); $response->header( 'Cache-Control', 'no-store' ); return $response;
	} ) );
} );

// Price/stock confirmation is checked on the same server request that adds to cart.
add_filter( 'woocommerce_store_api_add_to_cart_data', function ( $data, $request ) {
	$revision = $request->get_param( 'elinstyle_revision' );
	if ( ! $revision ) { return $data; } // Other WooCommerce clients retain their native behavior.
	$product = wc_get_product( absint( $data['id'] ) );
	if ( ! is_string( $revision ) || ! $product || ! hash_equals( elinstyle_product_revision( $product ), $revision ) ) {
		throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException( 'elinstyle_product_changed', __( 'قیمت یا موجودی تغییر کرده است؛ مقادیر تازه را بررسی و دوباره تأیید کن.', 'elinstyle-companion' ), 409 );
	}
	if ( (float) $data['quantity'] !== (float) (int) $data['quantity'] ) {
		throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException( 'elinstyle_quantity', __( 'تعداد باید عدد صحیح باشد.', 'elinstyle-companion' ), 400 );
	}
	return $data;
}, 10, 2 );
