<?php
defined( 'ABSPATH' ) || exit;
$first = $data['gallery'][0];
$image_markup = function ( $view ) use ( $product ) {
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' . esc_attr( $view['box'] ) . '" preserveAspectRatio="xMidYMin slice" role="img" aria-label="' . esc_attr( $product->get_name() . '؛ ' . $view['label'] ) . '"><image href="' . esc_url( $view['url'] ) . '" width="' . esc_attr( $view['width'] ) . '" height="' . esc_attr( $view['height'] ) . '"/></svg>';
};
?>
<div class="wrap product-breadcrumb crumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'elinstyle-companion' ); ?></a> / <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'فروشگاه', 'elinstyle-companion' ); ?></a> / <?php echo esc_html( $product->get_name() ); ?></div>
<div class="wrap product-layout" data-elinstyle-product="<?php echo esc_attr( wp_json_encode( $data ) ); ?>">
<section class="purchase" aria-labelledby="product-title">
<p class="product-category"><?php echo esc_html( wp_strip_all_tags( wc_get_product_category_list( $product->get_id(), ' / ' ) ) ); ?></p>
<h1 id="product-title"><?php echo esc_html( $product->get_name() ); ?></h1>
<div class="product-lead"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
<div class="product-prices"><div><span id="product-discount" hidden></span><del id="product-regular" hidden></del></div><strong id="product-price"><?php echo wp_kses_post( $data['price'] ); ?></strong></div>
<form id="product-form" onsubmit="return false" novalidate>
<?php foreach ( $data['groups'] as $group ) : $is_color = false !== strpos( $group['name'], 'color' ); ?>
<fieldset class="<?php echo $is_color ? 'color-choice' : 'product-size'; ?>" data-attribute-group="<?php echo esc_attr( $group['name'] ); ?>">
<legend><?php echo esc_html( $group['label'] ); ?>: <bdi data-attribute-label="<?php echo esc_attr( $group['name'] ); ?>"><?php echo count( $group['values'] ) === 1 ? esc_html( $group['values'][0]['label'] ) : esc_html__( 'انتخاب کن', 'elinstyle-companion' ); ?></bdi></legend>
<?php if ( ! $is_color ) : ?><button type="button" class="textbutton product-guide" data-size-guide><?php esc_html_e( 'راهنمای اندازه', 'elinstyle-companion' ); ?></button><?php endif; ?>
<div class="<?php echo $is_color ? 'product-colors' : 'product-sizes'; ?>" role="group" aria-label="<?php echo esc_attr( $group['label'] ); ?>">
<?php foreach ( $group['values'] as $option ) : ?>
<button type="button" data-product-attribute="<?php echo esc_attr( $group['name'] ); ?>" data-value="<?php echo esc_attr( $option['value'] ); ?>" aria-pressed="<?php echo count( $group['values'] ) === 1 ? 'true' : 'false'; ?>" aria-describedby="selection-message">
<?php if ( $is_color && 'cream' === $option['value'] ) : ?><span class="ivory-dot" aria-hidden="true"></span><?php endif; ?>
<?php echo esc_html( $option['label'] ); ?><?php if ( $is_color ) : ?><span class="color-check" aria-hidden="true">✓</span><?php endif; ?></button>
<?php endforeach; ?></div></fieldset>
<?php endforeach; ?>
<div class="selection-tools"><small id="unavailable-sizes"></small><button type="button" class="textbutton" id="clear-selection"><?php esc_html_e( 'پاک‌کردن انتخاب', 'elinstyle-companion' ); ?></button></div>
<p id="selection-message" role="status" aria-live="polite"><?php esc_html_e( 'رنگ و اندازه را انتخاب کن.', 'elinstyle-companion' ); ?></p>
<p class="product-sku"><?php esc_html_e( 'شناسه:', 'elinstyle-companion' ); ?> <bdi id="product-sku"><?php echo esc_html( $product->get_sku() ); ?></bdi></p>
<div class="quantity-field"><label for="product-quantity"><?php esc_html_e( 'تعداد', 'elinstyle-companion' ); ?></label><div class="quantity-control"><button type="button" id="quantity-minus" aria-label="<?php esc_attr_e( 'کاهش تعداد', 'elinstyle-companion' ); ?>" disabled>−</button><input id="product-quantity" type="number" min="1" max="1" step="1" value="1" inputmode="numeric" aria-describedby="quantity-error"><button type="button" id="quantity-plus" aria-label="<?php esc_attr_e( 'افزایش تعداد', 'elinstyle-companion' ); ?>" disabled>+</button></div><small id="quantity-error" role="status"></small></div>
<button class="btn product-add" id="product-add" type="submit" disabled><?php esc_html_e( 'افزودن به سبد خرید', 'elinstyle-companion' ); ?></button>
<p class="product-policy"><?php esc_html_e( 'ارسال و شرایط تعویض را پیش از خرید بررسی کن.', 'elinstyle-companion' ); ?></p>
<div id="product-cart-status" role="status" aria-live="polite"></div><a id="product-view-cart" class="textbutton" href="<?php echo esc_url( wc_get_cart_url() ); ?>" hidden><?php esc_html_e( 'مشاهدهٔ سبد', 'elinstyle-companion' ); ?></a>
</form></section>
<section class="product-gallery" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
<button class="gallery-main" id="gallery-main" type="button" aria-haspopup="dialog" aria-controls="product-zoom" aria-label="<?php esc_attr_e( 'بزرگ‌نمایی تصویر محصول', 'elinstyle-companion' ); ?>"><?php echo $image_markup( $first ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in closure. ?></button>
<button class="heart" id="product-favorite" type="button" aria-pressed="false" aria-label="<?php esc_attr_e( 'علاقه‌مندی محصول در این مرورگر', 'elinstyle-companion' ); ?>"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 21L3 12C-3 5 6 -1 12 6C18 -1 27 5 21 12Z"/></svg></button>
<div class="gallery-thumbs" role="group" aria-label="<?php esc_attr_e( 'انتخاب نمای محصول', 'elinstyle-companion' ); ?>">
<?php foreach ( $data['gallery'] as $index => $view ) : ?><button type="button" data-gallery="<?php echo esc_attr( $index ); ?>" aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"><span class="thumb-art"><?php echo $image_markup( $view ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span><?php echo esc_html( $view['label'] ); ?></span></button><?php endforeach; ?></div>
<p id="gallery-caption" class="gallery-caption" role="status"><?php echo esc_html( $first['caption'] ); ?></p></section>
</div>
<dialog id="product-zoom" aria-labelledby="zoom-title"><header><h2 id="zoom-title"><?php esc_html_e( 'تصویر محصول', 'elinstyle-companion' ); ?></h2><button type="button" class="iconbtn zoom-close" aria-label="<?php esc_attr_e( 'بستن بزرگ‌نمایی', 'elinstyle-companion' ); ?>">×</button></header><button type="button" class="textbutton" id="zoom-toggle" aria-pressed="false"><?php esc_html_e( 'بزرگ‌نمایی ۲×', 'elinstyle-companion' ); ?></button><div id="zoom-viewport" tabindex="0" role="region" aria-label="<?php esc_attr_e( 'تصویر بزرگ‌شده؛ برای جابه‌جایی از کلیدهای جهت استفاده کن', 'elinstyle-companion' ); ?>"></div><p id="zoom-caption"></p><button type="button" class="btn zoom-close"><?php esc_html_e( 'بستن تصویر', 'elinstyle-companion' ); ?></button></dialog>
