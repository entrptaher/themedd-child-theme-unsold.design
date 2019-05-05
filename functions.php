<?php
/**
 * Themedd child theme.
 */
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

add_action(‘admin_init’, ‘allow_contributor_uploads’);
function allow_contributor_uploads()
{
    $contributor = get_role(‘contributor’);
    $contributor->add_cap(‘upload_files’);
    $contributor->add_cap(‘edit_published_posts’);
    $contributor->add_cap(‘edit_others_posts’);
}

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
    
    if (edd_is_free_download($download_id)) {
        $price = '<span id="edd_price_' . get_the_ID() . '" class="edd_price">This logo is 100% unique and can be yours for <b>' . __('Free', 'themedd') . '</b></span>';
    } elseif (edd_has_variable_prices($download_id)) {
        $price = '<span id="edd_price_' . get_the_ID() . '" class="edd_price">This logo is 100% unique and can be yours for <b>' . __('From', 'themedd') . '&nbsp;' . edd_currency_filter(edd_format_amount(edd_get_lowest_price_option($download_id))) . '</b></span>';
    } else {
        $price = '<span class="edd_price">This logo is 100% unique and can be yours for  <b>'.edd_price($download_id, false).'</b></span>';
    }

    echo $price;
}

function vanila_themedd_edd_title($download_id)
{
    # Hard coded title
    the_title('<h3 class="vanila-downloadDetails-title">Buy <b>', '</b></h3>');
}

function vanila_themedd_edd_content($download_id)
{
    
    # big highlighted text box
    echo '<ul class="details_highlighted"><li>✨ Premium Logos <b>Sold Once</b></li><li>🤝 Fair <b>Money Back</b> Gurantee</li><li><b>👌 Manually approved</b> by our staff</li></ul>';
    
    echo '<b>Files included:</b> <span class="file-formats">AI, PNG, PDF, SVG</span>';

    echo '<b>Description:</b>';
    echo the_content();

    $categories = themedd_edd_download_categories($download_id);
    if ($categories) :
        echo '<li class="vanila-downloadDetails-categories"><span class="downloadDetails-name">'._e('Categories:', 'themedd').'</span><span class="downloadDetails-value">'.$categories.'</span></li>';
    endif;

    $tags = themedd_edd_download_tags($download_id);
    if ($tags) :
        echo '<li class="vanila-downloadDetails-tags"><span class="downloadDetails-name">'._e('Tags:', 'themedd').'</span><span class="downloadDetails-value">'.$tags.'</span></li>';
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
