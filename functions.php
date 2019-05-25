<?php
/**
 * Themedd child theme.
 */

/**
 * Place any custom functionality/code snippets below.
 */
if (! defined('EDD_SLUG')) {
    define('EDD_SLUG', 'd');
}

/**
 * Admin panel
 */
add_action('admin_init', 'allow_contributor_uploads');

/**
 * Common
 */
# Enable shortcode inside widget area
add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode');
add_action('wp_enqueue_scripts', 'themedd_child_styles');

# Custom js to change color of sold out button
add_action('wp_enqueue_scripts', 'my_scripts_method');

# Various small parts with different functions
include( dirname( __FILE__ ) . '/includes/function-parts/common.php' );
include( dirname( __FILE__ ) . '/includes/function-parts/common/purchase-link.php' );
include( dirname( __FILE__ ) . '/includes/function-parts/admin.php' );
include( dirname( __FILE__ ) . '/includes/function-parts/homepage.php' );
include( dirname( __FILE__ ) . '/includes/function-parts/single.php' );

/**
 * Home Page
 */
# show like button after buy now button on homepage
add_action('template_redirect', 'vanila_edd_favorites_link');

/**
 * Single Page
 */
# change order of title, price, content, link on single download page
add_action('template_redirect', 'price_and_content_order');
