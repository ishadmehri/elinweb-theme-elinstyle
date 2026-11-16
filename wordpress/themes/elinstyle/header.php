<?php defined( 'ABSPATH' ) || exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main"><?php esc_html_e( 'رفتن به محتوا', 'elinstyle' ); ?></a>
<?php if ( ! function_exists( 'elinstyle_demo_elementor_part' ) || ! elinstyle_demo_elementor_part( 'header' ) ) { echo '<div class="elinstyle-surface">'; elinstyle_part( 'header' ); echo '</div>'; } ?>
