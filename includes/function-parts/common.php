<?php

function themedd_child_styles()
{
    $parent_style = 'themedd';

    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'themedd-child',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}

function is_free()
{
    return floatval(get_post_meta(get_the_ID(), 'edd_price', true)) == 0;
}

function is_service()
{
    return get_post_meta(get_the_ID(), '_edd_das_enabled', true)  == '1';
}

function is_sold_out()
{
    $limit = floatval(get_post_meta(get_the_ID(), '_edd_purchase_limit', true));
    $sales = floatval(get_post_meta(get_the_ID(), '_edd_download_sales', true));
    if ($limit == 0) {
        return false;
    }
    if ($sales >= $limit) {
        return true;
    }
}

/* Add custom scripts: https://wordpress.stackexchange.com/a/177160 */
function my_scripts_method()
{
    wp_enqueue_script(
        'custom-script',
        get_stylesheet_directory_uri() . '/js/custom_script.js',
        array( 'jquery' )
    );
}