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
    
    echo '<p class="file-formats"><b>Files included:</b> <span>AI, PNG, PDF, SVG</span></p>';

    echo '<b class="description-prefix">Description:</b>';

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

/**
 * Override the add to cart button if the max purchase limit has been reached
 *
 * @since       1.0.0
 * @param       string $purchase_form the actual purchase form code
 * @param       array $args the info for the specific download
 * @global      string $user_email The email address of the current user
 * @global      boolean $edd_prices_sold_out Variable price sold out check
 * @return      string $purchase_form if conditions are not met
 * @return      string $sold_out if conditions are met
 */
function edd_pl_override_purchase_button( $purchase_form, $args ) {
	global $user_email, $edd_prices_sold_out;

	// Get options
	$sold_out_label = edd_get_option( 'edd_purchase_limit_sold_out_label' ) ? edd_get_option( 'edd_purchase_limit_sold_out_label' ) : __( 'Sold Out', 'edd-purchase-limit' );
	$scope          = edd_get_option( 'edd_purchase_limit_scope' ) ? edd_get_option( 'edd_purchase_limit_scope' ) : 'site-wide';
	$form_id        = ! empty( $args['form_id'] ) ? $args['form_id'] : 'edd_purchase_' . $args['download_id'];

	// Get purchase limits
	$max_purchases  = edd_pl_get_file_purchase_limit( $args['download_id'] );
	$is_sold_out    = false;
	$date_range     = edd_pl_is_date_restricted( $args['download_id'] );

	if( $scope == 'site-wide' ) {
		$purchases = edd_get_download_sales_stats( $args['download_id'] );

		if( ( $max_purchases && $purchases >= $max_purchases ) || !empty( $edd_prices_sold_out ) ) {
			$is_sold_out = true;
		}
	} elseif( is_user_logged_in() ) {
		$purchases = edd_pl_get_user_purchase_count( get_current_user_id(), $args['download_id'] );

		if( ( $max_purchases && $purchases >= $max_purchases ) || !empty( $edd_prices_sold_out ) ) {
			$is_sold_out = true;
		}
	}

	if( $is_sold_out ) {
		$purchase_form  = '<form id="' . $form_id . '" class="edd_download_purchase_form">';
		$purchase_form .= '<div class="edd_purchase_submit_wrapper">';

		if( edd_is_ajax_enabled() ) {
			$purchase_form .= sprintf(
				'<div class="edd-add-to-cart vanila-sold-out %1$s"><span>%2$s</span></a>',
				implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
				esc_attr( $sold_out_label )
			);
			$purchase_form .= '</div>';
		} else {
			$purchase_form .= sprintf(
				'<input type="submit" class="edd-no-js vanila-sold-out %1$s" name="edd_purchase_download" value="%2$s" disabled />',
				implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
				esc_attr( $sold_out_label )
			);
		}

		$purchase_form .= '</div></form>';
	} elseif( is_array( $date_range ) ) {
		$now = date( 'YmdHi' );
		$date_label = null;

		if( isset( $date_range['start'] ) ) {
			$start_time = date( 'YmdHi', strtotime( $date_range['start'][0] . $date_range['start'][1] ) );

			if( $start_time > $now ) {
				$date_label = edd_get_option( 'edd_purchase_limit_pre_date_label' ) ? edd_get_option( 'edd_purchase_limit_pre_date_label' ) : __( 'This product is not yet available!', 'edd-purchase-limit' );
			}
		}

		if( isset( $date_range['end'] ) ) {
			$end_time = date( 'YmdHi', strtotime( $date_range['end'][0] . $date_range['end'][1] ) );

			if( $end_time < $now ) {
				$date_label = edd_get_option( 'edd_purchase_limit_post_date_label' ) ? edd_get_option( 'edd_purchase_limit_post_date_label' ) : __( 'This product is no longer available!', 'edd-purchase-limit' );
			}
		}

		if( isset( $date_label ) ) {
			$purchase_form  = '<form id="' . $form_id . '" class="edd_download_purchase_form">';
			$purchase_form .= '<div class="edd_purchase_submit_wrapper">';

			if( edd_is_ajax_enabled() ) {
				$purchase_form .= sprintf(
					'<div class="edd-add-to-cart %1$s"><span>%2$s</span></div>',
					implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
					esc_attr( $date_label )
				);
			} else {
				$purchase_form .= sprintf(
					'<input type="submit" class="edd-no-js %1$s" name="edd_purchase_download" value="%2$s" disabled />',
					implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
					esc_attr( $date_label )
				);
			}

			$purchase_form .= '</div></form>';
		} elseif( edd_get_option( 'edd_purchase_limit_show_counts' ) ) {
			$label            = edd_get_option( 'add_to_cart_text' ) ? edd_get_option( 'add_to_cart_text' ) : __( 'Purchase', 'edd' );
			$remaining_label  = edd_get_option( 'edd_purchase_limit_remaining_label' ) ? edd_get_option( 'edd_purchase_limit_remaining_label' ) : __( 'Remaining', 'edd-purchase-limit' );
			$variable_pricing = edd_has_variable_prices( $args['download_id'] );

			if( ! $variable_pricing ) {
				$remaining = $max_purchases - $purchases;

				if( $remaining > '0' ) {
					$purchase_form = str_replace( $label . '</span>', $label . '</span> <span class="edd-pl-remaining-label">(' . $remaining . ' ' . $remaining_label . ')</span>', $purchase_form );
				}
			}
		}
	} elseif( edd_get_option( 'edd_purchase_limit_show_counts' ) ) {
		$label            = edd_get_option( 'add_to_cart_text' ) ? edd_get_option( 'add_to_cart_text' ) : __( 'Purchase', 'edd' );
		$remaining_label  = edd_get_option( 'edd_purchase_limit_remaining_label' ) ? edd_get_option( 'edd_purchase_limit_remaining_label' ) : __( 'Remaining', 'edd-purchase-limit' );
		$variable_pricing = edd_has_variable_prices( $args['download_id'] );

		if( ! $variable_pricing ) {
			$remaining = $max_purchases - $purchases;

			if( $remaining > '0' ) {
				$purchase_form = str_replace( $label . '</span>', $label . '</span> <span class="edd-pl-remaining-label">(' . $remaining . ' ' . $remaining_label . ')</span>', $purchase_form );
			}
		}
	}

	return $purchase_form;
}
remove_filter( 'edd_purchase_download_form', 'edd_pl_override_purchase_button', 200, 2 );
add_filter( 'edd_purchase_download_form', 'vanila_edd_pl_override_purchase_button', 200, 2 );

/**
 * Override the add to cart button if the max purchase limit has been reached
 *
 * Variable prices function
 *
 * @since       1.0.4
 * @param       int $download_id the ID for the specific download
 * @global      boolean $edd_prices_sold_out Variable price sold out check
 * @return      string $purchase_form if conditions are not met
 * @return      string $sold_out if conditions are met
 */
function edd_pl_override_variable_pricing( $download_id = 0 ) {
	global $edd_prices_sold_out;

	$variable_pricing = edd_has_variable_prices( $download_id );

	if( $variable_pricing ) {
		// Get options
		$sold_out_label = edd_get_option( 'edd_purchase_limit_sold_out_label' ) ? edd_get_option( 'edd_purchase_limit_sold_out_label' ) : __( 'Sold Out', 'edd-purchase-limit' );
		$scope          = edd_get_option( 'edd_purchase_limit_scope' ) ? edd_get_option( 'edd_purchase_limit_scope' ) : 'site-wide';
		$type           = edd_single_price_option_mode( $download_id ) ? 'checkbox' : 'radio';
		$sold_out       = array();

		// Get variable prices
		$prices = apply_filters( 'edd_purchase_variable_prices', edd_get_variable_prices( $download_id ), $download_id );

		do_action( 'edd_before_price_options', $download_id );

		echo '<div class="edd_price_options">';
		echo '<ul>';

		if( $prices ) {
			$disable_all = get_post_meta( $download_id, '_edd_purchase_limit_variable_disable', true );
			$disabled    = false;

			if( $disable_all ) {
				foreach( $prices as $price_id => $price_data ) {
					if( edd_pl_is_item_sold_out( $download_id, $price_id ) ) {
						$disabled = true;
						break;
					}
				}
			}

			foreach( $prices as $price_id => $price_data ) {
				$checked_key = isset( $_GET['price_option'] ) ? absint( $_GET['price_option'] ) : edd_get_default_variable_price( $download_id );

				// Output label
				echo '<li id="edd_price_option_' . $download_id . '_' . sanitize_key( $price_data['name'] ) . '">';

				// Output option or 'sold out'
				if( edd_pl_is_item_sold_out( $download_id, $price_id ) || $disabled ) {
					// Update $sold_out
					$sold_out[] = $price_id;

					printf(
						'<label for="%2$s"><input type="%1$s" name="edd_options[price_id][]" id="%2$s" class="%3$s" value="%4$s" disabled /> %5$s</label>',
						$type,
						esc_attr( 'edd_price_option_' . $download_id . '_' . $price_id ),
						esc_attr( 'edd_price_option_' . $download_id ),
						esc_attr( $price_id ),
						'<span class="edd_price_option_name">' . esc_html( $price_data['name'] ) . '</span><span class="edd_price_option_sep">&nbsp;&ndash;&nbsp;</span><span class="edd_price_option_price vanila-sold-out">' . $sold_out_label . '</span>'
					);
				} else {
					$max_purchases    = edd_pl_get_file_purchase_limit( $download_id, null, $price_id );
					$purchases        = edd_pl_get_file_purchases( $download_id, $price_id );
					$remaining        = $max_purchases - $purchases;
					$remaining_output = null;

					if( edd_get_option( 'edd_purchase_limit_show_counts' ) && ( $remaining > '0' ) ) {
						$remaining_label  = edd_get_option( 'edd_purchase_limit_remaining_label' ) ? edd_get_option( 'edd_purchase_limit_remaining_label' ) : __( 'Remaining', 'edd-purchase-limit' );
						$remaining_output = ' <span class="edd-pl-variable-remaining-label">(' . $remaining . ' ' . $remaining_label . ')</span>';
					}

					printf(
						'<label for="%3$s"><input type="%2$s" %1$s name="edd_options[price_id][]" id="%3$s" class="%4$s" value="%5$s" %7$s/> %6$s</label>',
						checked( apply_filters( 'edd_price_option_checked', $checked_key, $download_id, $price_id ), $price_id, false ),
						$type,
						esc_attr( 'edd_price_option_' . $download_id . '_' . $price_id ),
						esc_attr( 'edd_price_option_' . $download_id ),
						esc_attr( $price_id ),
						'<span class="edd_price_option_name">' . esc_html( $price_data['name'] ) . '</span><span class="edd_price_option_sep">&nbsp;&ndash;&nbsp;</span><span class="edd_price_option_price">' . edd_currency_filter( edd_format_amount( $price_data['amount'] ) ) . '</span>' . ( isset( $remaining_output ) ? $remaining_output : '' ),
						checked( apply_filters( 'edd_price_option_checked', $checked_key, $download_id, $price_id ), $price_id, false )
					);
				}

				remove_action( 'edd_after_price_option', 'edd_variable_price_quantity_field', 10, 3 );
				do_action( 'edd_after_price_option', $price_id, $price_data, $download_id );
				echo '</li>';
			}
		} else {
			foreach( $prices as $price_id => $price_data ) {
				// Output label
				echo '<li id="edd_price_option_' . $download_id . '_' . sanitize_key( $price_data['name'] ) . '">';

				// Output option or 'sold out'
				printf(
					'<label for="%3$s"><input type="%2$s" %1$s name="edd_options[price_id][]" id="%3$s" class="%4$s" value="%5$s" %7$s/> %6$s</label>',
					checked( apply_filters( 'edd_price_option_checked', $checked_key, $download_id, $price_id ), $price_id, false ),
					$type,
					esc_attr( 'edd_price_option_' . $download_id . '_' . $price_id ),
					esc_attr( 'edd_price_option_' . $download_id ),
					esc_attr( $price_id ),
					'<span class="edd_price_option_name">' . esc_html( $price_data['name'] ) . '</span><span class="edd_price_option_sep">&nbsp;&ndash;&nbsp;</span><span class="edd_price_option_price">' . edd_currency_filter( edd_format_amount( $price_data['amount'] ) ) . '</span>',
					checked( apply_filters( 'edd_price_option_checked', $checked_key, $download_id, $price_id ), $price_id, false )
				);

				remove_action( 'edd_after_price_option', 'edd_variable_price_quantity_field', 10, 3 );
				do_action( 'edd_after_price_option', $price_id, $price_data, $download_id );
				echo '</li>';
			}
		}

		do_action( 'edd_after_price_options_list', $download_id, $prices, $type );

		echo '</ul>';
		echo '</div>';

		if( count( $sold_out ) == count( $prices ) ) {
			$edd_prices_sold_out = true;
		} else {
			$edd_prices_sold_out = false;
		}

		do_action( 'edd_after_price_options', $download_id );
	}
}
remove_action( 'edd_purchase_link_top', 'edd_purchase_variable_pricing', 10 );
remove_action( 'edd_purchase_link_top', 'edd_pl_override_variable_pricing', 10 );
add_action( 'edd_purchase_link_top', 'vanila_edd_pl_override_variable_pricing', 10 );
