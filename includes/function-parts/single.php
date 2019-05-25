<?php

/**
 * Display the post header
 *
 * @since 1.0.0
 */

function vanila_themedd_page_header($args = array())
{
    /**
     * Allow header to be removed via filter
     */
    if (!apply_filters('themedd_page_header', true)) {
        return;
    }

    do_action('themedd_page_header_before');

    if (is_404()) {
        $title = esc_html__('Oops! That page can&rsquo;t be found.', 'themedd');
    } else {
        $title = !empty($args['title']) ? $args['title'] : get_the_title();
    }

    // Process any classes passed in.
    if (!empty($args['classes'])) {
        if (is_array($args['classes'])) {
            // array of classes
            $classes = $args['classes'];
        } else {
            // must be string, explode it into an array
            $classes = explode(' ', $args['classes']);
        }
    } else {
        $classes = array();
    }

    $defaults = apply_filters(
        'themedd_header_defaults',
        array(
            'subtitle' => !empty($args['subtitle']) ? $args['subtitle'] : '',
            'title' => !empty($args['title']) ? $args['title'] : get_the_title(),
        )
    );

    $args = wp_parse_args($args, $defaults);?>

		<header class="page-header<?php echo themedd_page_header_classes($classes); ?>">
			<?php do_action('themedd_page_header_start');?>
			<div class="wrapper">
				<?php do_action('themedd_page_header_wrapper_start');?>
				<h1 class="<?php echo get_post_type(); ?>-title">
					<?php if ($args['title']): ?>
                        <?php if (is_service()) {?>
                            <b>Premium </b><?php echo $args['title']; ?> <b>Service</b>
						<?php } elseif (is_free()) {?>
							<b>FREE </b><?php echo $args['title']; ?> <b>for Download</b>
						<?php } else {?>
							<b>Premium </b><?php echo $args['title']; ?> <?php if (is_sold_out()) {?> <b>is SOLD</b> <?php } else {?> <b>for Sale</b> <?php }?>
							<?php }?>
					<?php endif;?>
				</h1>

                <?php quotescollection_quote(array('ajax_refresh' => false, 'char_limit' => 300));?>

				<?php do_action('themedd_page_header_wrapper_end');?>
			</div>
			<?php do_action('themedd_page_header_end');?>
		</header>

	<?php
}

function vanila_themedd_edd_title($download_id)
{
    if (is_service()) {
        return the_title('<h3 class="vanila-downloadDetails-title"><b>Order ', ' Service</b></h3>');
    }
    # Hard coded title
    if (is_sold_out()) {
        the_title('<h3 class="vanila-downloadDetails-title"><b>', ' is SOLD</b></h3>');
    } else {
        if (is_free()) {
            the_title('<h3 class="vanila-downloadDetails-title">Download FREE <b>', '</b></h3>');
        } else {
            the_title('<h3 class="vanila-downloadDetails-title">Buy <b>', '</b></h3>');
        }
    }
}

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
    $is_service = is_service();

    $prefix = '<span class="edd_price_content">';
    if ($is_service) {
        $content = 'based on your business profile and design preferences, by top rated designers just for:';
    } else if ($is_sold_out) {
        $content = 'This logo is <b>sold</b> but you can order a custom one.';
    } else {
        $content = 'This logo is 100% unique and can be yours for ';
    }
    $suffix = '</span>';

    if (edd_is_free_download($download_id)) {
        if ($is_sold_out) {
            $price = $prefix . $content . $suffix;
        } else {
            $price = $prefix . $content . __('Free', 'themedd') . $suffix;
        }
    } else {
        if ($is_service) {
            $price = $prefix . $content . '<div class="price_text">' . edd_price($download_id, false) . ', 3 Concepts</div>' . $suffix;
        } else if ($is_sold_out) {
            $price = $prefix . $content . '<div class="price_text">Sold for ' . edd_price($download_id, false) . '</div>' . $suffix;
        } else {
            $price = $prefix . $content . edd_price($download_id, false) . $suffix;
        }
    }

    echo $price;
}

function vanila_themedd_edd_content($download_id)
{
    $is_service = is_service();

    /**
     * big highlighted text box under price tag
     **/
    echo '<ul class="details_highlighted">';
    if ($is_service) {
        echo '<li>✨ Full <b>copyright ownership</b></li>';
    } else {
        echo '<li>✨ Premium Logos <b>Sold Once</b></li>';
    }
    echo '<li>🤝 Fair <b>Money Back</b> Gurantee</li><li><b>👌 Manually approved</b> by our staff</li></ul>';

    /**
     * File formats like AI, PNG etc
     */
    $files_included = get_post_meta($download_id, 'file_formats', true);

    echo '<p class="file-formats"><b>Files included:</b> <span>' . ($files_included ? implode(", ", $files_included) : "AI, PNG, SVG, PDF") . '</span></p>';

    /**
     * Post content, the logo will be designed by xyz etc
     */
    echo '<b class="description-prefix">Description:</b>';

    echo the_content();

    /**
     * Category and other meta
     */
    $categories = themedd_edd_download_categories($download_id);
    if ($categories):
        echo '<li class="vanila-downloadDetails-categories"><span class="downloadDetails-name downloadDetails-label">Categories:</span><span class="downloadDetails-value">' . $categories . '</span></li>';
    endif;

    $tags = themedd_edd_download_tags($download_id);
    if ($tags):
        echo '<li class="vanila-downloadDetails-tags"><span class="downloadDetails-name downloadDetails-label">Tags:</span><span class="downloadDetails-value">' . $tags . '</span></li>';
    endif;
}

function price_and_content_order()
{
    remove_action('themedd_edd_download_info', 'themedd_edd_price');
    remove_action('themedd_edd_download_info', 'themedd_edd_purchase_link');
    add_action('themedd_edd_download_info', 'vanila_themedd_edd_title', 10, 1);
    add_action('themedd_edd_download_info', 'vanila_themedd_edd_price', 10, 1);
    add_action('themedd_edd_download_info', 'vanila_themedd_edd_content', 10, 1);
    add_action('themedd_edd_download_info', 'vanila_themedd_edd_purchase_link', 10, 1);
}
