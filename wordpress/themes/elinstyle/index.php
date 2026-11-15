<?php
defined( 'ABSPATH' ) || exit;
get_header();
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		the_content();
	}
} else {
	echo '<main id="main" class="wrap"><p>' . esc_html__( 'محتوایی یافت نشد.', 'elinstyle' ) . '</p></main>';
}
get_footer();
