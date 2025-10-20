<?php use XTS\Modules\Layouts\Main;

if ( ! defined( 'WOODMART_THEME_DIR' ) ) exit( 'No direct script access allowed' );

/**
 * ------------------------------------------------------------------------------------------------
 * Portfolio shortcode
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_shortcode_portfolio' ) ) {
	function woodmart_shortcode_portfolio( $atts ) {
		if ( ! woodmart_get_opt( 'portfolio', '1' ) ) {
			return;
		}

		$output      = '';
		$parsed_atts = shortcode_atts(
			array_merge(
				woodmart_get_carousel_atts(),
				array(
					'post_type'              => 'portfolio',
					'posts_per_page'         => woodmart_get_opt( 'portoflio_per_page' ),
					'filters'                => false,
					'filters_type'           => 'masonry',
					'include'                => '',
					'categories'             => '',
					'style'                  => woodmart_get_opt( 'portoflio_style' ),
					'columns'                => 3,
					'columns_tablet'         => 'auto',
					'columns_mobile'         => 'auto',
					'spacing'                => woodmart_get_opt( 'portfolio_spacing' ),
					'spacing_tablet'         => '',
					'spacing_mobile'         => '',
					'pagination'             => woodmart_get_opt( 'portfolio_pagination' ),
					'ajax_page'              => '',
					'orderby'                => woodmart_get_opt( 'portoflio_orderby' ),
					'order'                  => woodmart_get_opt( 'portoflio_order' ),
					'layout'                 => 'grid',
					'slides_per_view'        => '3',
					'slides_per_view_tablet' => 'auto',
					'slides_per_view_mobile' => 'auto',
					'lazy_loading'           => 'no',
					'el_class'               => '',
					'el_id'                  => '',
					'wrapper_classes'        => '',
					'image_size'             => 'large',
					'image_size_custom'      => array(),
					'element_title'          => '',
					'element_title_tag'      => 'h3',
					'inner_content'          => '',
					'css'                    => '',
					'woodmart_css_id'        => '',
				)
			),
			$atts
		);

		extract( $parsed_atts );

		$encoded_atts     = json_encode( $parsed_atts );
		$wrapper_classes .= apply_filters( 'vc_shortcodes_css_class', '', '', $parsed_atts );

		if ( $el_class ) {
			$wrapper_classes .= ' ' . $el_class;
		}

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$paged   = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		if ( $ajax_page > 1 ) {
			$paged = $ajax_page;
		}

		if ( $parsed_atts['css'] ) {
			$wrapper_classes .= ' ' . vc_shortcode_custom_css_class( $parsed_atts['css'] );
		}

		$s = false;

		if ( isset( $_REQUEST['s'] ) ) {
			$s = sanitize_text_field( $_REQUEST['s'] );
		}

		$args = array(
			'post_type'      => 'portfolio',
			'post_status'    => 'publish',
			'posts_per_page' => $posts_per_page,
			'orderby'        => $orderby,
			'order'          => $order,
			'paged'          => $paged,
		);

		if ( 'ids' === $post_type && '' !== $include ) {
			$args['post__in']            = array_map( 'trim', explode( ',', $include ) );
			$args['ignore_sticky_posts'] = true;
		}

		if ( $s ) {
			$args['s'] = $s;
		}

		if ( get_query_var( 'project-cat' ) != '' ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'project-cat',
					'field'    => 'slug',
					'terms'    => get_query_var( 'project-cat' ),
				),
			);
		}

		if ( $categories ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'project-cat',
					'field'    => 'term_id',
					'operator' => 'IN',
					'terms'    => $categories,
				),
			);
		}

		if ( 'related_projects' === $post_type ) {
			Main::setup_preview();
			$args = array_merge( $args, woodmart_get_related_projects_args( get_the_ID() ) );
			Main::restore_preview();
		}

		ob_start();

		if ( empty( $style ) || $style == 'inherit' ) {
			$style = woodmart_get_opt( 'portoflio_style' );
		}

		woodmart_set_loop_prop( 'portfolio_style', $style );
		woodmart_set_loop_prop( 'portfolio_column', $columns );
		woodmart_set_loop_prop( 'portfolio_columns_tablet', $columns_tablet );
		woodmart_set_loop_prop( 'portfolio_columns_mobile', $columns_mobile );
		woodmart_set_loop_prop( 'portfolio_image_size', $image_size );

		if ( 'custom' === $image_size && ! empty( $image_size_custom ) ) {
			woodmart_set_loop_prop( 'portfolio_image_size_custom', $image_size_custom );
		}

		if ( $style == 'parallax' ) {
			woodmart_enqueue_js_library( 'panr-parallax-bundle' );
			woodmart_enqueue_js_script( 'portfolio-effect' );
		}

		woodmart_enqueue_portfolio_loop_styles( $style );

		$query = new WP_Query( $args );

		$parsed_atts['custom_sizes'] = apply_filters( 'woodmart_portfolio_shortcode_custom_sizes', false );

		wp_enqueue_script( 'imagesloaded' );
		woodmart_enqueue_js_library( 'isotope-bundle' );
		woodmart_enqueue_js_script( 'masonry-layout' );

		woodmart_enqueue_js_library( 'photoswipe-bundle' );
		woodmart_enqueue_inline_style( 'photoswipe' );
		woodmart_enqueue_js_script( 'portfolio-photoswipe' );

		if ( $lazy_loading == 'yes' ) {
			woodmart_lazy_loading_init( true );
			woodmart_enqueue_inline_style( 'lazy-loading' );
		}

		woodmart_enqueue_inline_style( 'portfolio-base' );

		if ( '' === $parsed_atts['spacing'] ) {
			$parsed_atts['spacing'] = woodmart_get_opt( 'products_spacing' );

			if ( '' === $parsed_atts['spacing_tablet'] ) {
				$parsed_atts['spacing_tablet'] = woodmart_get_opt( 'products_spacing_tablet' );
			}
			if ( '' === $parsed_atts['spacing_mobile'] ) {
				$parsed_atts['spacing_mobile'] = woodmart_get_opt( 'products_spacing_mobile' );
			}
		}

		if ( 'carousel' === $layout ) {
			if ( 'wpb' === woodmart_get_current_page_builder() ) {
				$parsed_atts['carousel_classes'] = 'wd-wpb';
			}

			if ( ( 'auto' !== $slides_per_view_tablet && ! empty( $slides_per_view_tablet ) ) || ( 'auto' !== $slides_per_view_mobile && ! empty( $slides_per_view_mobile ) ) ) {
				$parsed_atts['custom_sizes'] = array(
					'desktop' => $slides_per_view,
					'tablet'  => $slides_per_view_tablet,
					'mobile'  => $slides_per_view_mobile,
				);
			}

			return woodmart_generate_posts_slider( $parsed_atts, $query );
		}

		$style_attrs = woodmart_get_grid_attrs(
			array(
				'columns'        => woodmart_loop_prop( 'portfolio_column' ),
				'columns_tablet' => woodmart_loop_prop( 'portfolio_columns_tablet' ),
				'columns_mobile' => woodmart_loop_prop( 'portfolio_columns_mobile' ),
				'spacing'        => $parsed_atts['spacing'],
				'spacing_tablet' => $parsed_atts['spacing_tablet'],
				'spacing_mobile' => $parsed_atts['spacing_mobile'],
			)
		);

		?>
		<?php if ( $query->have_posts() ) : ?>
			<?php if ( ! $is_ajax ) : ?>
				<div
				<?php if ( $el_id ) : ?>
				id="<?php echo esc_attr( $el_id ); ?>"
				<?php endif ?>
				class="wd-portfolio-element<?php echo esc_attr( $wrapper_classes ); ?>">
					<?php
					if ( ! empty( $parsed_atts['inner_content'] ) ) {
						echo do_shortcode( $parsed_atts['inner_content'] );
					}

					if ( $element_title ) {
						$element_title_tag = in_array( $element_title_tag, array_keys( woodmart_get_allowed_html() ), true ) ? $element_title_tag : 'h4';

						printf( '<%1$s class="wd-el-title title element-title">%2$s</%1$s>', esc_attr( $element_title_tag ), esc_html( $element_title ) );
					}
					?>
					<?php if ( ! is_tax() && $filters && ! $s ) : ?>
						<?php woodmart_portfolio_filters( $categories, $filters_type ); ?>
					<?php endif ?>

					<div class="wd-projects wd-masonry wd-grid-f-col" data-atts="<?php echo esc_attr( $encoded_atts ); ?>" data-source="shortcode" data-paged="1" style="<?php echo esc_attr( $style_attrs ); ?>">
			<?php endif ?>
			<?php

			while ( $query->have_posts() ) {
				$query->the_post();
				get_template_part( 'content', 'portfolio' );
			}
			?>

			<?php if ( ! $is_ajax ) : ?>
					</div>
					<?php if ( $query->max_num_pages > 1 && ! $is_ajax && $pagination != 'disable' && $layout != 'carousel' ) : ?>
						<?php wp_enqueue_script( 'imagesloaded' ); ?>
						<?php woodmart_enqueue_js_script( 'portfolio-load-more' ); ?>
						<?php woodmart_enqueue_js_library( 'waypoints' ); ?>
						<div class="wd-loop-footer portfolio-footer">
							<?php if ( $pagination == 'infinit' || $pagination == 'load_more' ) : ?>
								<?php woodmart_enqueue_inline_style( 'load-more-button' ); ?>
								<a href="#" rel="nofollow noopener" class="btn wd-load-more wd-portfolio-load-more load-on-<?php echo 'load_more' === $pagination ? 'click' : 'scroll'; ?>"><span class="load-more-label"><?php esc_html_e( 'Load more projects', 'woodmart' ); ?></span></a>
								<div class="btn wd-load-more wd-load-more-loader"><span class="load-more-loading"><?php esc_html_e( 'Loading...', 'woodmart' ); ?></span></div>
							<?php else : ?>
								<?php query_pagination( $query->max_num_pages ); ?>
							<?php endif ?>
						</div>
					<?php endif ?>
				</div>
			<?php endif ?>

		<?php elseif ( ! $is_ajax ) : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>
		<?php

		$output .= ob_get_clean();

		if ( $lazy_loading == 'yes' ) {
			woodmart_lazy_loading_deinit();
		}

		wp_reset_postdata();

		woodmart_reset_loop();

		if ( $is_ajax ) {
			$output = array(
				'items'  => $output,
				'status' => ( $query->max_num_pages > $paged ) ? 'have-posts' : 'no-more-posts',
			);
		}

		return $output;
	}
}
