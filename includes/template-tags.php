<?php

/**
 * Display the post header
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'vanila_themedd_page_header' ) ) :

	function vanila_themedd_page_header( $args = array() ) {

		/**
		 * Allow header to be removed via filter
		 */
		if ( ! apply_filters( 'themedd_page_header', true ) ) {
			return;
		}

		do_action( 'themedd_page_header_before' );

		if ( is_404() ) {
			$title = esc_html__( 'Oops! That page can&rsquo;t be found.', 'themedd' );
		} else {
			$title = ! empty( $args['title'] ) ? $args['title'] : get_the_title();
		}

		// Process any classes passed in.
		if ( ! empty( $args['classes'] ) ) {
			if ( is_array( $args['classes'] ) ) {
				// array of classes
				$classes = $args['classes'];
			} else {
				// must be string, explode it into an array
				$classes = explode( ' ', $args['classes'] );
			}
		} else {
			$classes = array();
		}

        $defaults = apply_filters( 'themedd_header_defaults',
            array(
                'subtitle' => ! empty( $args['subtitle'] ) ? $args['subtitle'] : '',
                'title'    => ! empty( $args['title'] ) ? $args['title'] : get_the_title(),
            )
        );

        $args = wp_parse_args( $args, $defaults );
		?>

		<header class="page-header<?php echo themedd_page_header_classes( $classes ); ?>">
			<?php do_action( 'themedd_page_header_start' ); ?>
			<div class="wrapper">
				<?php do_action( 'themedd_page_header_wrapper_start' ); ?>
				<h1 class="<?php echo get_post_type(); ?>-title">
					<?php if ( $args['subtitle'] ) : ?>
						<span class="entry-title-primary"><b>Premium </b><?php echo $args['title']; ?> <b>for Sale</b></span>
						<span class="subtitle"><?php echo $args['subtitle']; ?></span>
					<?php elseif ( $args['title'] ) : ?>
            <b>Premium </b><?php echo $args['title']; ?> <b>for Sale</b>
					<?php endif; ?>
				</h1>
				
        <?php quotescollection_quote( array( 'ajax_refresh' => false, 'char_limit' => 300 ) ); ?>

				<?php do_action( 'themedd_page_header_wrapper_end' ); ?>
			</div>
			<?php do_action( 'themedd_page_header_end' ); ?>
		</header>

	<?php

	}

endif;