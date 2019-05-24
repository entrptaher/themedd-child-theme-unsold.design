<?php
/**
 * Themedd child theme.
 */

# Enable shortcode inside widget area
add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode');
add_action('wp_enqueue_scripts', 'themedd_child_styles');

/**
 * Place any custom functionality/code snippets below.
 */
if (! defined('EDD_SLUG')) {
    define('EDD_SLUG', 'd');
}

include( dirname( __FILE__ ) . '/includes/function-parts.php' );

add_action('admin_init', 'allow_contributor_uploads');
add_action('template_redirect', 'vanila_edd_favorites_link');
add_action('template_redirect', 'my_theme_shift_navigation');
add_action('wp_enqueue_scripts', 'my_scripts_method');
