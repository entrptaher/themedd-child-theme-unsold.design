<?php
/**
 * Themedd child theme.
 */

# Enable shortcode inside widget area
add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode');

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
add_action('wp_enqueue_scripts', 'themedd_child_styles');

/**
 * Place any custom functionality/code snippets below.
 */
if (! defined('EDD_SLUG')) {
    define('EDD_SLUG', 'd');
}

function allow_contributor_uploads()
{
    $contributor = get_role('contributor');
    $contributor->add_cap('upload_files');
    $contributor->add_cap('edit_published_posts');
    $contributor->add_cap('edit_others_posts');
}
add_action('admin_init', 'allow_contributor_uploads');

/**
 * Remove standard wish list links
 * @return [type] [description]
 * @uses  edd_favorites_load_link()
 */
function vanila_edd_favorites_link()
{
    // remove standard add to wish list link
    remove_action('edd_purchase_link_top', 'edd_favorites_load_link');

    // add our new link
    add_action('edd_purchase_link_end', 'edd_favorites_load_link');
}
add_action('template_redirect', 'vanila_edd_favorites_link');


/**
 * Download price
 *
 * @since 1.0.0
 */
function vanila_themedd_edd_price($download_id)
{
    // Return early if price enhancements has been disabled.
    if (false === themedd_edd_price_enhancements()) {
        return;
    }
    $is_sold_out = is_sold_out();
    
    $prefix = '<span class="edd_price_content">';
    if ($is_sold_out) {
        $content = 'This logo is <b>sold</b> but you can order a custom one.';
    } else {
        $content = 'This logo is 100% unique and can be yours for ';
    }
    $suffix = '</span>';
    

    if (edd_is_free_download($download_id)) {
        if ($is_sold_out) {
            $price = $prefix.$content.$suffix;
        } else {
            $price =  $prefix.$content . __('Free', 'themedd') . $suffix;
        }
    } else {
        if ($is_sold_out) {
            $price = $prefix.$content.'<div class="price_text">Sold for ' .edd_price($download_id, false) .'</div>'.$suffix;
        } else {
            $price = $prefix.$content.edd_price($download_id, false) .$suffix;
        }
    }
    
    echo $price;
}

function vanila_themedd_edd_title($download_id)
{
    $is_free = floatval(get_post_meta(get_the_ID(), 'edd_price')[0]) == 0;

    # Hard coded title
    if (is_sold_out()) {
        the_title('<h3 class="vanila-downloadDetails-title"><b>', ' is SOLD</b></h3>');
    } else {
        if($is_free){
            the_title('<h3 class="vanila-downloadDetails-title">Download FREE <b>', '</b></h3>');    
        }else{
            the_title('<h3 class="vanila-downloadDetails-title">Buy <b>', '</b></h3>');
        }
    }
}

function is_sold_out()
{
    return strpos(do_shortcode('[remaining_purchases]'), 'Sold Out') !== false;
}


function vanila_themedd_edd_content($download_id)
{
    # big highlighted text box
    echo '<ul class="details_highlighted"><li>✨ Premium Logos <b>Sold Once</b></li><li>🤝 Fair <b>Money Back</b> Gurantee</li><li><b>👌 Manually approved</b> by our staff</li></ul>';
    
    $files_included = get_field('files_included');

    echo '<p class="file-formats"><b>Files included:</b> <span>'. ($files_included ? implode(", ", $files_included) : "AI, PNG, SVG, PDF") .'</span></p>';

    echo '<b class="description-prefix">Description:</b>';

    echo the_content();

    $categories = themedd_edd_download_categories($download_id);
    if ($categories) :
        echo '<li class="vanila-downloadDetails-categories"><span class="downloadDetails-name downloadDetails-label">Categories:</span><span class="downloadDetails-value">'.$categories.'</span></li>';
    endif;

    $tags = themedd_edd_download_tags($download_id);
    if ($tags) :
        echo '<li class="vanila-downloadDetails-tags"><span class="downloadDetails-name downloadDetails-label">Tags:</span><span class="downloadDetails-value">'.$tags.'</span></li>';
    endif;
}

function my_theme_shift_navigation()
{
    remove_action('themedd_edd_download_info', 'themedd_edd_price');
    remove_action('themedd_edd_download_info', 'themedd_edd_purchase_link');
    add_action('themedd_edd_download_info', 'vanila_themedd_edd_title', 10, 1);
    add_action('themedd_edd_download_info', 'vanila_themedd_edd_price', 10, 1);
    add_action('themedd_edd_download_info', 'vanila_themedd_edd_content', 10, 1);
    add_action('themedd_edd_download_info', 'themedd_edd_purchase_link', 10, 1);
}

add_action('template_redirect', 'my_theme_shift_navigation');

/* Add custom scripts: https://wordpress.stackexchange.com/a/177160 */
function my_scripts_method()
{
    wp_enqueue_script(
        'custom-script',
        get_stylesheet_directory_uri() . '/js/custom_script.js',
        array( 'jquery' )
    );
}

add_action('wp_enqueue_scripts', 'my_scripts_method');
