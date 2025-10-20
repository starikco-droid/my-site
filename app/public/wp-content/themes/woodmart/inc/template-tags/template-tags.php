<?php if ( ! defined( 'WOODMART_THEME_DIR' ) ) {
	exit( 'No direct script access allowed' );}
use Elementor\Plugin;
use XTS\Modules\Layouts\Main as Builder;
use XTS\Modules\Layouts\Global_Data as Builder_Data;
use XTS\Modules\Mega_Menu_Walker;
use XTS\Modules\Seo_Scheme\Breadcrumbs;
use XTS\Registry;

if ( ! function_exists( 'woodmart_get_skip_main_content_button' ) ) {
	/**
	 * Skip main content button.
	 *
	 * @return void
	 */
	function woodmart_get_skip_main_content_button() {
		$locations = get_nav_menu_locations();

		?>
		<div class="wd-skip-links">
			<?php
			if ( ! empty( $locations['main-menu'] ) ) {
				$menu_obj = wp_get_nav_menu_object( $locations['main-menu'] );

				if ( $menu_obj ) {
					?>
					<a href="#menu-<?php echo esc_attr( $menu_obj->slug ); ?>" class="wd-skip-navigation btn">
						<?php esc_html_e( 'Skip to navigation', 'woodmart' ); ?>
					</a>
					<?php
				}
			}
			?>
			<a href="#main-content" class="wd-skip-content btn">
				<?php esc_html_e( 'Skip to main content', 'woodmart' ); ?>
			</a>
		</div>
		<?php
	}

	add_action( 'wp_body_open', 'woodmart_get_skip_main_content_button' );
}

if ( ! function_exists( 'woodmart_get_carousel_nav_template' ) ) {
	/**
	 * Navigation carousel template.
	 *
	 * @param string $nav_classes Navigation classes.
	 */
	function woodmart_get_carousel_nav_template( $nav_classes = '', $settings = array() ) {
		$attributes    = '';
		$inner_classes = 'wd-arrow-inner';

		if ( ! empty( $settings ) ) {
			if ( isset( $settings['hide_prev_next_buttons'] ) || isset( $settings['hide_prev_next_buttons_tablet'] ) || isset( $settings['hide_prev_next_buttons_mobile'] ) ) {
				if ( 'yes' === $settings['hide_prev_next_buttons'] && ( empty( $settings['hide_prev_next_buttons_tablet'] ) || 'yes' === $settings['hide_prev_next_buttons_tablet'] ) && ( empty( $settings['hide_prev_next_buttons_mobile'] ) || 'yes' === $settings['hide_prev_next_buttons_mobile'] ) ) {
					return;
				} else {
					$nav_classes .= 'yes' === $settings['hide_prev_next_buttons'] ? ' wd-hide-lg' : '';
					$nav_classes .= 'yes' === $settings['hide_prev_next_buttons_tablet'] ? ' wd-hide-md-sm' : '';
					$nav_classes .= 'yes' === $settings['hide_prev_next_buttons_mobile'] ? ' wd-hide-sm' : '';
				}
			}

			if ( isset( $settings['tabindex'] ) ) {
				$attributes .= ' tabindex="' . esc_attr( $settings['tabindex'] ) . '"';
			}

			if ( ! empty( $settings['inner_classes'] ) ) {
				$inner_classes .= ' ' . $settings['inner_classes'];
			}
		}

		$arrows_icon_type = woodmart_get_opt( 'carousel_arrows_icon_type', '1' );

		if ( ! $arrows_icon_type ) {
			$arrows_icon_type = 1;
		}

		$nav_classes .= ' wd-icon-' . $arrows_icon_type;

		?>
		<div class="wd-nav-arrows<?php echo esc_attr( $nav_classes ); ?>">
			<div class="wd-btn-arrow wd-prev wd-disabled">
				<div class="<?php echo esc_attr( $inner_classes ); ?>"<?php echo wp_kses( $attributes, true ); ?>></div>
			</div>
			<div class="wd-btn-arrow wd-next">
				<div class="<?php echo esc_attr( $inner_classes ); ?>"<?php echo wp_kses( $attributes, true ); ?>></div>
			</div>
		</div>
		<?php

		woodmart_enqueue_inline_style( 'swiper-arrows' );
	}
}

if ( ! function_exists( 'woodmart_get_carousel_pagination_template' ) ) {
	/**
	 * Pagination carousel template.
	 *
	 * @param array  $settings Carousel settings.
	 * @param string $classes Pagination extra classes.
	 * @return void
	 */
	function woodmart_get_carousel_pagination_template( $settings = array(), $classes = ' wd-style-shape' ) {
		if ( ! empty( $settings ) ) {
			if ( 'yes' === $settings['hide_pagination_control'] && 'yes' === $settings['hide_pagination_control_tablet'] && 'yes' === $settings['hide_pagination_control_mobile'] ) {
				return;
			} else {
				$classes .= 'yes' === $settings['hide_pagination_control'] ? ' wd-hide-lg' : '';
				$classes .= 'yes' === $settings['hide_pagination_control_tablet'] ? ' wd-hide-md-sm' : '';
				$classes .= 'yes' === $settings['hide_pagination_control_mobile'] ? ' wd-hide-sm' : '';
			}

			$classes .= 'yes' === $settings['dynamic_pagination_control'] ? ' wd-dynamic' : '';
		}

		woodmart_enqueue_inline_style( 'swiper-pagin' );
		?>
		<div class="wd-nav-pagin-wrap text-center<?php echo esc_attr( $classes ); ?>">
			<ul class="wd-nav-pagin"></ul>
		</div>
		<?php
	}
}

if ( ! function_exists( 'woodmart_get_carousel_scrollbar_template' ) ) {
	/**
	 * Scrollbar carousel template.
	 *
	 * @param array  $settings Carousel settings.
	 * @param string $classes Scrollbar extra classes.
	 * @return void
	 */
	function woodmart_get_carousel_scrollbar_template( $settings = array(), $classes = '' ) {
		if ( ! empty( $settings ) ) {
			if ( 'yes' === $settings['wrap'] || 'yes' === $settings['hide_scrollbar'] && 'yes' === $settings['hide_scrollbar_tablet'] && 'yes' === $settings['hide_scrollbar_mobile'] ) {
				return;
			} else {
				$classes .= 'yes' === $settings['hide_scrollbar'] ? ' wd-hide-lg' : '';
				$classes .= 'yes' === $settings['hide_scrollbar_tablet'] ? ' wd-hide-md-sm' : '';
				$classes .= 'yes' === $settings['hide_scrollbar_mobile'] ? ' wd-hide-sm' : '';
			}
		}

		woodmart_enqueue_inline_style( 'swiper-scrollbar' );
		?>
		<div class="wd-nav-scroll<?php echo esc_attr( $classes ); ?>"></div>
		<?php
	}
}

if ( ! function_exists( 'woodmart_sticky_loader' ) ) {
	/**
	 * Sticky loader.
	 *
	 * @param string $extra_classes Extra classes.
	 */
	function woodmart_sticky_loader( $extra_classes = '' ) {
		woodmart_enqueue_inline_style( 'sticky-loader' );

		?>
		<div class="wd-sticky-loader<?php echo esc_attr( $extra_classes ); ?>"><span class="wd-loader"></span></div>
		<?php
	}
}

if ( ! function_exists( 'woodmart_meta_viewport' ) ) {
	/**
	 * Meta viewport tag.
	 */
	function woodmart_meta_viewport() {
		?>
		<?php if ( 'not_scalable' === woodmart_get_opt( 'site_viewport', 'not_scalable' ) ) : ?>
			<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<?php else : ?>
			<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php endif; ?>
		<?php
	}

	add_action( 'wp_head', 'woodmart_meta_viewport' );
}

if ( ! function_exists( 'woodmart_preloader_template' ) ) {
	function woodmart_preloader_template() {
		if ( ! woodmart_get_opt( 'preloader' ) ) {
			return;
		}

		$background_color = woodmart_get_opt( 'preloader_background_color' );
		$image            = woodmart_get_opt( 'preloader_image' );
		$color_scheme     = woodmart_get_opt( 'preloader_color_scheme', 'dark' );
		$classes          = ' color-scheme-' . $color_scheme;
		?>
			<style class="wd-preloader-style">
				html {
					/* overflow: hidden; */
					overflow-y: scroll;
				}

				html body {
					overflow: hidden;
					max-height: calc(100vh - var(--wd-admin-bar-h));
				}
			</style>
			<div class="wd-preloader<?php echo esc_attr( $classes ); ?>">
				<style>
					<?php if ( isset( $background_color['idle'] ) && $background_color['idle'] ) : ?>
						.wd-preloader {
							background-color: <?php echo esc_attr( $background_color['idle'] ); ?>
						}
					<?php endif; ?>

					<?php if ( ! isset( $image['id'] ) || ( isset( $image['id'] ) && ! $image['id'] ) ) : ?>

						@keyframes wd-preloader-Rotate {
							0%{
								transform:scale(1) rotate(0deg);
							}
							50%{
								transform:scale(0.8) rotate(360deg);
							}
							100%{
								transform:scale(1) rotate(720deg);
							}
						}

						.wd-preloader-img:before {
							content: "";
							display: block;
							width: 50px;
							height: 50px;
							border: 2px solid #BBB;
							border-top-color: #000;
							border-radius: 50%;
							animation: wd-preloader-Rotate 2s cubic-bezier(0.63, 0.09, 0.26, 0.96) infinite ;
						}

						.color-scheme-light .wd-preloader-img:before {
							border-color: rgba(255,255,255,0.2);
							border-top-color: #fff;
						}
					<?php endif; ?>

					@keyframes wd-preloader-fadeOut {
						from {
							visibility: visible;
						}
						to {
							visibility: hidden;
						}
					}

					.wd-preloader {
						position: fixed;
						top: 0;
						left: 0;
						right: 0;
						bottom: 0;
						opacity: 1;
						visibility: visible;
						z-index: 2500;
						display: flex;
						justify-content: center;
						align-items: center;
						animation: wd-preloader-fadeOut 20s ease both;
						transition: opacity .4s ease;
					}

					.wd-preloader.preloader-hide {
						pointer-events: none;
						opacity: 0 !important;
					}

					.wd-preloader-img {
						max-width: 300px;
						max-height: 300px;
					}
				</style>

				<div class="wd-preloader-img">
					<?php if ( isset( $image['id'] ) && $image['id'] ) : ?>
						<img src="<?php echo esc_url( wp_get_attachment_url( $image['id'] ) ); ?>" alt="preloader">
					<?php endif; ?>
				</div>
			</div>
		<?php
	}

	add_action( 'woodmart_after_body_open', 'woodmart_preloader_template', 500 );
}

if ( ! function_exists( 'woodmart_age_verify_popup' ) ) {
	function woodmart_age_verify_popup() {
		if ( ! woodmart_get_opt( 'age_verify' ) ) {
			return;
		}

		woodmart_enqueue_js_library( 'magnific' );
		woodmart_enqueue_js_script( 'age-verify' );
		
		woodmart_enqueue_inline_style( 'age-verify' );
		woodmart_enqueue_inline_style( 'mfp-popup' );
		woodmart_enqueue_inline_style( 'mod-animations-transform' );
		woodmart_enqueue_inline_style( 'mod-transform' );

		$wrapper_classes = ' color-scheme-' . woodmart_get_opt( 'age_verify_color_scheme' );

		?>
			<div class="wd-popup wd-age-verify wd-scroll-content<?php echo esc_attr( $wrapper_classes ); ?>" role="complementary" aria-label="<?php esc_attr_e( 'Age verification', 'woodmart' ); ?>">
				<div class="wd-age-verify-text reset-last-child">
					<?php echo do_shortcode( woodmart_get_opt( 'age_verify_text' ) ); ?>
				</div>

				<div class="wd-age-verify-text-error reset-last-child">
					<?php echo do_shortcode( woodmart_get_opt( 'age_verify_text_error' ) ); ?>
				</div>

				<div class="wd-age-verify-buttons">
					<a href="#" rel="nofollow noopener" class="btn wd-age-verify-allowed">
						<?php esc_html_e( 'I am 18 or Older', 'woodmart' ); ?>
					</a>

					<a href="#" rel="nofollow noopener" class="btn wd-age-verify-forbidden">
						<?php esc_html_e( 'I am Under 18', 'woodmart' ); ?>
					</a>
				</div>
			</div>
		<?php
	}

	add_action( 'woodmart_before_wp_footer', 'woodmart_age_verify_popup', 400 );
}

/**
 * ------------------------------------------------------------------------------------------------
 * Main loop
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_main_loop' ) ) {
	add_action( 'woodmart_main_loop', 'woodmart_main_loop' );

	function woodmart_main_loop( $settings = array() ) {
		global $paged, $wp_query;

		$max_page         = $wp_query->max_num_pages;
		$is_builder       = Builder::get_instance()->has_custom_layout( 'blog_archive' );
		$is_ajax          = woodmart_is_woo_ajax();
		$encoded_settings = $settings ? wp_json_encode( $settings ) : '';
		$wrapper_classes  = ! empty( $settings['wrapper_classes'] ) ? $settings['wrapper_classes'] : '';

		$classes    = '';
		$attributes = '';

		$id = uniqid();

		if ( is_home() && ! $is_builder ) {
			woodmart_set_loop_prop( 'parts_published_date', woodmart_get_opt( 'parts_published_date', true ) );
			woodmart_set_loop_prop( 'parts_title', woodmart_get_opt( 'parts_title', true ) );
			woodmart_set_loop_prop( 'parts_meta', woodmart_get_opt( 'parts_meta', true ) );
			woodmart_set_loop_prop( 'parts_text', woodmart_get_opt( 'parts_text', true ) );
			woodmart_set_loop_prop( 'parts_btn', woodmart_get_opt( 'parts_btn', true ) );
		}

		if ( $is_ajax && ! empty( $_GET['atts'] ) ) { //phpcs:ignore.
			$atts = woodmart_clean( $_GET['atts'] ); //phpcs:ignore.

			foreach ( $atts as $key => $value ) {
				if ( 'false' === $value ) {
					$value = false; // Validate for gutenberg toggle.
				}

				if ( 'inherit' !== $value ) {
					woodmart_set_loop_prop( $key, $value );
				}
			}
		}

		$pagination   = woodmart_loop_prop( 'blog_pagination' );
		$blog_design  = woodmart_loop_prop( 'blog_design' );
		$blog_masonry = woodmart_loop_prop( 'blog_masonry' );

		// fix bug with wrong escaped url generated by next_posts() call.
		if ( is_search() ) {
			$pagination = 'pagination';
		}

		$attributes .= 'data-paged="1" data-source="main_loop"';

		if ( $encoded_settings ) {
			$attributes .= ' data-atts="' . esc_attr( $encoded_settings ) . '"';
		}

		if ( 'masonry' === $blog_design || 'mask' === $blog_design || 'meta-image' === $blog_design ) {
			if ( 'meta-image' !== $blog_design && $blog_masonry ) {
				$classes .= ' wd-masonry wd-grid-f-col';
				wp_enqueue_script( 'imagesloaded' );
				woodmart_enqueue_js_library( 'isotope-bundle' );
				woodmart_enqueue_js_script( 'masonry-layout' );
			} else {
				$classes .= ' wd-grid-g';
			}

			$attributes .= ' style="' . woodmart_get_grid_attrs(
				array(
					'columns'        => woodmart_loop_prop( 'blog_columns' ),
					'columns_tablet' => woodmart_loop_prop( 'blog_columns_tablet' ),
					'columns_mobile' => woodmart_loop_prop( 'blog_columns_mobile' ),
					'spacing'        => woodmart_loop_prop( 'blog_spacing' ),
					'spacing_tablet' => woodmart_loop_prop( 'blog_spacing_tablet' ),
					'spacing_mobile' => woodmart_loop_prop( 'blog_spacing_mobile' ),
				)
			) . '"';
		}

		if ( ! $paged ) {
			$paged = 1;
		}

		if ( ! $is_ajax ) {
			woodmart_enqueue_inline_style( 'blog-loop-base' );
			woodmart_enqueue_inline_style( 'post-types-mod-predefined' );

			if ( 'small-images' === $blog_design || 'chess' === $blog_design ) {
				woodmart_enqueue_inline_style( 'blog-loop-design-small-img-chess' );
			} else {
				woodmart_enqueue_inline_style( 'blog-loop-design-' . $blog_design );
			}
		}

		?>

			<?php if ( have_posts() ) : ?>

				<?php if ( ! $is_ajax ) : ?>
					<div
					<?php if ( ! empty( $settings['el_id'] ) ) : ?>
						id="<?php echo esc_attr( $settings['el_id'] ); ?>"
					<?php endif; ?>
					class="wd-blog-element<?php echo esc_attr( $wrapper_classes ); ?>">

					<?php if ( is_tag() && tag_description() ) : // Show an optional tag description ?>
						<div class="archive-meta"><?php echo tag_description(); ?></div>
					<?php endif; ?>

					<?php if ( is_category() && category_description() ) : // Show an optional category description ?>
						<div class="archive-meta"><?php echo category_description(); ?></div>
					<?php endif; ?>

					<?php if ( is_author() && get_the_author_meta( 'description' ) && ! $is_builder ) : ?>
						<?php get_template_part( 'author-bio' ); ?>
					<?php endif ?>

					<div class="wd-posts wd-blog-holder wd-grid-g<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>" <?php echo wp_kses( $attributes, true ); ?>>
				<?php endif ?>


					<?php
					if ( $is_ajax ) {
						ob_start();
					}
					$name = woodmart_get_blog_design_name( $blog_design );
					?>

					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<?php get_template_part( 'templates/content', $name ); ?>
					<?php endwhile; ?>

					<?php
					if ( $is_ajax ) {
						$output = ob_get_clean();}
					?>

				<?php if ( ! $is_ajax ) : ?>
					</div>

					<?php if ( $max_page > 1 && $pagination ) : ?>
						<div class="wd-loop-footer blog-footer">
							<?php if ( $pagination == 'infinit' || $pagination == 'load_more' ) : ?>
								<?php if ( get_next_posts_link() ) : ?>
									<?php wp_enqueue_script( 'imagesloaded' ); ?>
									<?php woodmart_enqueue_js_script( 'blog-load-more' ); ?>
									<?php if ( 'infinit' === $pagination ) : ?>
										<?php woodmart_enqueue_js_library( 'waypoints' ); ?>
									<?php endif; ?>
									<?php woodmart_enqueue_inline_style( 'load-more-button' ); ?>
									<a href="<?php echo add_query_arg( 'woo_ajax', '1', next_posts( $max_page, false ) ); ?>" rel="nofollow noopener" data-holder-id="<?php echo esc_attr( $id ); ?>" class="btn wd-load-more wd-blog-load-more load-on-<?php echo 'load_more' === $pagination ? 'click' : 'scroll'; ?>"><span class="load-more-label"><?php esc_html_e( 'Load more posts', 'woodmart' ); ?></span></a>
									<div class="btn wd-load-more wd-load-more-loader"><span class="load-more-loading"><?php esc_html_e('Loading...', 'woodmart'); ?></span></div>
								<?php endif; ?>
							<?php else : ?>
								<?php woodmart_paging_nav(); ?>
							<?php endif ?>
						</div>
					<?php endif; ?>
					</div>
				<?php endif ?>


			<?php else : ?>
				<?php get_template_part( 'content', 'none' ); ?>
			<?php endif; ?>

		<?php

		if ( $is_ajax ) {
			wp_send_json(
				array(
					'items'       => $output,
					'status'      => ( $max_page > $paged ) ? 'have-posts' : 'no-more-posts',
					'nextPage'    => add_query_arg( 'woo_ajax', '1', next_posts( $max_page, false ) ),
					'currentPage' => strtok( woodmart_get_current_url(), '?' ),
				)
			);
		}
	}
}

/**
 * ------------------------------------------------------------------------------------------------
 * Footer woodmart extra action
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_extra_footer_action' ) ) {
	function woodmart_extra_footer_action() {
		if ( woodmart_needs_footer() ) {
			do_action( 'woodmart_after_footer' );
		}
	}

	add_action( 'wp_footer', 'woodmart_extra_footer_action', 500 );
}


/**
 * ------------------------------------------------------------------------------------------------
 * Read more button
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_modify_read_more_link' ) ) {
	function woodmart_modify_read_more_link() {
		return '</p><p class="read-more-section">' . woodmart_read_more_tag();
	}
}

add_filter( 'the_content_more_link', 'woodmart_modify_read_more_link' );



if ( ! function_exists( 'woodmart_read_more_tag' ) ) {
	function woodmart_read_more_tag( $style = '' ) {
		return '<a class="' . ( 'button' === $style ? ' btn' : '' ) . '" href="' . get_permalink() . '">' . esc_html__( 'Continue reading', 'woodmart' ) . '</a>';
	}
}


/**
 * ------------------------------------------------------------------------------------------------
 * Get post image
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_get_post_thumbnail' ) ) {
	function woodmart_get_post_thumbnail( $size = 'medium', $attach_id = false ) {
		global $post;

		if ( has_post_thumbnail() ) {
			if ( ! $attach_id ) {
				$attach_id = get_post_thumbnail_id();
			}

			if ( woodmart_loop_prop( 'img_size' ) ) {
				$size = woodmart_loop_prop( 'img_size' );
			}

			return woodmart_otf_get_image_html( $attach_id, $size, woodmart_loop_prop( 'img_size_custom' ) );
		}
	}
}

/**
 * ------------------------------------------------------------------------------------------------
 * Get post content
 * ------------------------------------------------------------------------------------------------
 */


if ( ! function_exists( 'woodmart_get_content' ) ) {
	function woodmart_get_content( $btn = true, $force_full = false, $force_return = false ) {
		global $post;

		$type = woodmart_get_opt( 'blog_excerpt' );

		if ( $force_full ) {
			$type = 'full';
		}

		if ( $force_return ) {
			ob_start();
		}

		if ( $type == 'full' ) {
			woodmart_get_full_content( $btn );
		} elseif ( $type == 'excerpt' ) {

			if ( ! empty( $post->post_excerpt ) ) {
				echo wp_kses_post( get_the_excerpt() );
			} else {
				$excerpt_length = apply_filters( 'woodmart_get_excerpt_length', woodmart_get_opt( 'blog_excerpt_length' ) );
				echo woodmart_excerpt_from_content( $post->post_content, intval( $excerpt_length ) );
			}
		}

		if ( $force_return ) {
			return ob_get_clean();
		}
	}
}

if ( ! function_exists( 'woodmart_render_read_more_btn' ) ) {
	/**
	 * Render read more button.
	 *
	 * @param string $style Button style name.
	 *
	 * @return void
	 */
	function woodmart_render_read_more_btn( $style = 'link' ) {
		switch ( $style ) {
			case 'button':
				?>
					<div class="wd-post-read-more wd-style-btn">
						<?php echo woodmart_read_more_tag('button'); // phpcs:ignore ?>
					</div>
				<?php
				break;
			case 'link':
				?>
					<div class="wd-post-read-more wd-style-link read-more-section">
						<?php echo woodmart_read_more_tag('link'); //phpcs:ignore. ?>
					</div>
				<?php
				break;
		}
	}
}

if ( ! function_exists( 'woodmart_get_full_content' ) ) {
	function woodmart_get_full_content( $btn = false ) {

		$strip_gallery = apply_filters( 'woodmart_strip_gallery', true );

		if ( get_post_format() == 'gallery' && $strip_gallery ) {
			if ( $btn ) {
				$content = woodmart_strip_shortcode_gallery( get_the_content() );
			} else {
				$content = woodmart_strip_shortcode_gallery( get_the_content( '' ) );
			}
			echo str_replace( ']]>', ']]&gt;', apply_filters( 'the_content', $content ) );
		} else {
			if ( $btn ) {
				the_content();
			} else {
				the_content( '' );
			}
		}
	}
}

if ( ! function_exists( 'woodmart_get_the_content' ) ) {
	function woodmart_get_the_content() {
		$id = get_the_ID();

		if ( get_post_meta( $id, '_woodmart_mobile_content', true ) && wp_is_mobile() ) {
			$content = woodmart_get_html_block( get_post_meta( $id, '_woodmart_mobile_content', true ) );
		} else {
			$content = get_the_content();

			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
		}

		return $content;
	}
}

/**
 * ------------------------------------------------------------------------------------------------
 * Display meta information for a specific post
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_post_modified_date' ) ) {
	function woodmart_post_modified_date() {
		?>
		<time class="updated" datetime="<?php echo get_the_modified_date( 'c' ); // phpcs:ignore ?>">
			<?php echo get_the_modified_date(); // phpcs:ignore ?>
		</time>
		<?php
	}
}

if ( ! function_exists( 'woodmart_post_meta_author' ) ) {
	function woodmart_post_meta_author( $avatar = true, $label = 'short', $author_name = true, $size = 18 ) {
		?>
		<?php if ( 'short' === $label ) : ?>
			<span><?php esc_html_e( 'By', 'woodmart' ); ?></span>
		<?php elseif ( 'long' === $label ) : ?>
			<span><?php esc_html_e( 'Posted by', 'woodmart' ); ?></span>
		<?php endif; ?>

		<?php if ( $avatar && $size ) : ?>
			<?php echo get_avatar( get_the_author_meta( 'ID' ), $size, '', 'author-avatar' ); ?>
		<?php endif; ?>

		<?php if ( $author_name ) : ?>
			<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="author" rel="author"><?php echo get_the_author(); ?></a>
		<?php endif; ?>
		<?php
	}
}

if ( ! function_exists( 'woodmart_post_meta_reply' ) ) {
	function woodmart_post_meta_reply( $with_text = false ) {
		$comment_link_template = $with_text ? '<span class="wd-replies-count">%s</span> <span class="wd-replies-count-label">%s</span>' : '<span class="wd-replies-count">%s</span>';
		comments_popup_link(
			sprintf( $comment_link_template, '0', $with_text ? esc_html__( 'comments', 'woodmart' ) : '' ),
			sprintf( $comment_link_template, '1', $with_text ? esc_html__( 'comment', 'woodmart' ) : '' ),
			sprintf( $comment_link_template, '%', $with_text ? esc_html__( 'comments', 'woodmart' ) : '' )
		);
	}
}

if ( ! function_exists( 'woodmart_post_date' ) ) {
	function woodmart_post_date() {
		woodmart_enqueue_inline_style( 'post-types-mod-date-style-bg' );
		?>
			<div class="wd-post-date wd-style-with-bg">
				<span class="post-date-day">
					<?php echo get_the_time( 'd' ); ?>
				</span>
				<span class="post-date-month">
					<?php echo get_the_time( 'M' ); ?>
				</span>
			</div>
		<?php
	}
}


/**
 * ------------------------------------------------------------------------------------------------
 * Display posts next/prev navigation
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_posts_navigation' ) ) {
	function woodmart_posts_navigation() {
		woodmart_enqueue_inline_style( 'post-types-el-page-navigation' );
		?>
		<div class="wd-page-nav wd-design-1<?php echo woodmart_get_old_classes( ' single-post-navigation' ); ?>">
				<?php
						$next_post = get_next_post();
						$prev_post = get_previous_post();

						$archive_url = false;

				if ( get_post_type() == 'post' ) {
					$archive_page = get_option( 'page_for_posts' );
					$archive_url  = get_permalink( $archive_page );
				} elseif ( get_post_type() == 'portfolio' ) {
					$archive_page = woodmart_get_portfolio_page_id();
					$archive_url  = get_permalink( $archive_page );
				}
				?>
					<div class="wd-page-nav-btn prev-btn<?php echo woodmart_get_old_classes( ' blog-posts-nav-btn' ); ?>">
						<?php if ( ! empty( $next_post ) ) : ?>
							<a href="<?php echo get_permalink( $next_post->ID ); ?>">
								<div class="wd-label"><?php esc_html_e( 'Newer', 'woodmart' ); ?></div>
								<span class="wd-entities-title"><?php echo get_the_title( $next_post->ID ); ?></span>
								<span class="wd-page-nav-icon"></span>
							</a>
						<?php endif; ?>
					</div>

					<?php if ( $archive_url && 'page' == get_option( 'show_on_front' ) ) : ?>
						<?php woodmart_enqueue_js_script( 'btns-tooltips' ); ?>
						<?php woodmart_enqueue_js_library( 'tooltips' ); ?>
						<a href="<?php echo esc_url( $archive_url ); ?>" class="back-to-archive wd-tooltip"><?php esc_html_e( 'Back to list', 'woodmart' ); ?></a>
					<?php endif ?>

					<div class="wd-page-nav-btn next-btn<?php echo woodmart_get_old_classes( ' blog-posts-nav-btn' ); ?>">
						<?php if ( ! empty( $prev_post ) ) : ?>
							<a href="<?php echo get_permalink( $prev_post->ID ); ?>">
								<span class="wd-label"><?php esc_html_e( 'Older', 'woodmart' ); ?></span>
								<span class="wd-entities-title"><?php echo get_the_title( $prev_post->ID ); ?></span>
								<span class="wd-page-nav-icon"></span>
							</a>
						<?php endif; ?>
					</div>
			</div>
		<?php
	}
}


/**
 * ------------------------------------------------------------------------------------------------
 * Display entry meta
 * ------------------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'woodmart_entry_meta' ) ) {
	function woodmart_entry_meta() {
		if ( apply_filters( 'woodmart_entry_meta', false ) ) {
			?>
				<footer class="entry-meta">
					<?php if ( is_user_logged_in() ) : ?>
						<p><?php edit_post_link( esc_html__( 'Edit', 'woodmart' ), '<span class="edit-link">', '</span>' ); ?></p>
					<?php endif; ?>
				</footer>
			<?php
		}
	}
}


/**
 * ------------------------------------------------------------------------------------------------
 * Display navigation to the next/previous set of posts.
 * ------------------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'woodmart_paging_nav' ) ) {
	function woodmart_paging_nav() {
		$enable_pagination = apply_filters( 'woodmart_enable_pagination', true );

		if ( $enable_pagination ) {
			query_pagination();
			return;
		}
		?>

			<ul>
				<?php if ( get_previous_posts_link() ) : ?>
					<li class="next">
						<?php previous_posts_link( esc_html__( 'Newer Posts &rarr;', 'woodmart' ) ); ?>
					</li>
				<?php endif; ?>

				<?php if ( get_next_posts_link() ) : ?>
					<li class="previous">
						<?php next_posts_link( esc_html__( '&larr; Older Posts', 'woodmart' ) ); ?>
					</li>
				<?php endif; ?>
			</ul>

		<?php
	}
}

if ( ! function_exists( 'query_pagination' ) ) {
	function query_pagination( $pages = '', $range = 2 ) {
		$show_items = ( $range * 2 ) + 1;

		global $paged;

		$page = $paged;

		if ( empty( $page ) ) {
			$page = 1;
		}

		if ( '' === $pages ) {
			global $wp_query;
			$pages = $wp_query->max_num_pages;
			if ( ! $pages ) {
				$pages = 1;
			}
		}

		if ( 1 !== $pages ) {
			echo '<nav class="wd-pagination' . woodmart_get_old_classes( ' woodmart-pagination' ) . '">';
			echo '<ul>';

			if ( $page > 2 && $page > $range + 1 && $show_items < $pages ) {
				echo '<li><a href="' . esc_url( get_pagenum_link() ) . '" class="page-numbers">&laquo;</a></li>';
			}

			if ( $page > 1 && $show_items < $pages ) {
				echo '<li><a href="' . esc_url( get_pagenum_link( $page - 1 ) ) . '" class="page-numbers">&lsaquo;</a></li>';
			}

			for ( $i = 1; $i <= $pages; $i ++ ) {
				if ( 1 !== $pages && ( ! ( $i >= $page + $range + 1 || $i <= $page - $range - 1 ) || $pages <= $show_items ) ) {
					echo ( $page === $i ) ? '<li><span class="current page-numbers">' . esc_html( $i ) . '</span></li>' : '<li><a href="' . esc_url( get_pagenum_link( $i ) ) . '" class="page-numbers" >' . esc_html( $i ) . '</a></li>';
				}
			}

			if ( $page < $pages && $show_items < $pages ) {
				echo '<li><a href="' . esc_url( get_pagenum_link( $page + 1 ) ) . '" class="page-numbers">&rsaquo;</a></li>';
			}

			if ( $page < $pages - 1 && $page + $range - 1 < $pages && $show_items < $pages ) {
				echo '<li><a href="' . esc_url( get_pagenum_link( $pages ) ) . '" class="page-numbers">&raquo;</a></li>';
			}

			echo '</ul>';
			echo '</nav>';
		}
	}
}

// **********************************************************************//
// ! Page top part
// **********************************************************************//

if ( ! function_exists( 'woodmart_page_top_part' ) ) {
	function woodmart_page_top_part() {
		$style_attr = '';

		if ( woodmart_has_sidebar_in_page() ) {
			$style_attr  = '--wd-col-lg:12;';
			$style_attr .= '--wd-gap-lg:30px;';
			$style_attr .= '--wd-gap-sm:20px;';

			$style_attr = ' style="' . $style_attr . '"';
		}
		?>
		<?php if ( ! woodmart_is_woo_ajax() ) : ?>
			<div class="wd-page-content main-page-wrapper">
		<?php elseif ( woodmart_is_pjax() ) : ?>
			<title><?php echo esc_html( woodmart_get_document_title() ); ?></title>
			<?php do_action( 'woodmart_pjax_top_part' ); ?>
		<?php endif ?>

		<?php
			/**
			 * Hook woodmart_after_header.
			 *
			 * @hooked render_all_floating_blocks - 10
			 * @hooked woodmart_show_page_title - 20
			 */
			do_action( 'woodmart_after_header' );
		?>

		<main id="main-content" class="wd-content-layout content-layout-wrapper<?php echo esc_attr( Registry::getInstance()->layout->get_main_container_class() ); ?>" role="main"<?php echo wp_kses( $style_attr, true ); ?>>
		<?php
	}
}

// **********************************************************************//
// ! Page bottom part
// **********************************************************************//

if ( ! function_exists( 'woodmart_page_bottom_part' ) ) {
	function woodmart_page_bottom_part() {
		?>
		</main>
		<?php
	}
}

if ( ! function_exists( 'woodmart_get_carousel_atts' ) ) {
	function woodmart_get_carousel_atts() {
		return array(
			'carousel_id'                    => '5000',
			'speed'                          => '5000',
			'slides_per_view'                => '3',
			'slides_per_view_tablet'         => woodmart_is_elementor_installed() ? array( 'size' => '' ) : 'auto',
			'slides_per_view_mobile'         => woodmart_is_elementor_installed() ? array( 'size' => '' ) : 'auto',
			'wrap'                           => '',
			'loop'                           => false,
			'autoplay'                       => 'no',
			'autoheight'                     => 'no',
			'hide_pagination_control'        => 'no',
			'hide_pagination_control_tablet' => 'yes',
			'hide_pagination_control_mobile' => 'yes',
			'dynamic_pagination_control'     => 'no',
			'hide_prev_next_buttons'         => '',
			'hide_prev_next_buttons_tablet'  => '',
			'hide_prev_next_buttons_mobile'  => '',
			'carousel_arrows_position'       => '',
			'hide_scrollbar'                 => 'yes',
			'hide_scrollbar_tablet'          => 'yes',
			'hide_scrollbar_mobile'          => 'yes',
			'scroll_per_page'                => 'yes',
			'dragEndSpeed'                   => 200,
			'center_mode'                    => 'no',
			'custom_sizes'                   => '',
			'sliding_speed'                  => false,
			'animation'                      => false,
			'content_animation'              => false,
			'post_type'                      => '',
			'slider'                         => '',
			'library'                        => '',
			'css'                            => '',
			'effect'                         => '',
			'spacing'                        => '',
			'spacing_tablet'                 => '',
			'spacing_mobile'                 => '',
			'carousel_sync'                  => '',
			'sync_parent_id'                 => '',
			'sync_child_id'                  => '',
			'scroll_carousel_init'           => 'no',
			'disable_overflow_carousel'      => 'no',
		);
	}
}

if ( ! function_exists( 'woodmart_get_carousel_attributes' ) ) {
	function woodmart_get_carousel_attributes( $atts = array() ) {
		$default_atts = woodmart_get_carousel_atts();
		$atts         = shortcode_atts( $default_atts, $atts );
		$output       = array();

		wp_enqueue_script( 'imagesloaded' );

		$slides_per_view = isset( $atts['slides_per_view'] ) ? $atts['slides_per_view'] : $default_atts['slides_per_view'];
		$post_type       = isset( $atts['post_type'] ) ? $atts['post_type'] : $default_atts['post_type'];

		$custom_sizes = isset( $atts['custom_sizes'] ) ? $atts['custom_sizes'] : false;

		$items = woodmart_get_col_sizes( $slides_per_view, $post_type );

		if ( $items['desktop'] == 1 ) {
			$items['mobile'] = 1;
		}

		if ( is_array( $custom_sizes ) && ! empty( $custom_sizes['desktop'] ) ) {
			$auto_columns = woodmart_get_col_sizes( $custom_sizes['desktop'], $post_type );
			$items        = $custom_sizes;

			if ( empty( $custom_sizes['tablet'] ) || 'auto' === $custom_sizes['tablet'] ) {
				$items['tablet'] = $auto_columns['tablet'];
			}

			if ( empty( $custom_sizes['mobile'] ) || 'auto' === $custom_sizes['mobile'] ) {
				$items['mobile'] = $auto_columns['mobile'];
			}
		}

		$include = apply_filters(
			'woodmart_allows_output_carousel_attributes',
			array(
				'speed',
				'wrap',
				'loop',
				'autoplay',
				'autoheight',
				'scroll_per_page',
				'center_mode',
				'sliding_speed',
				'animation',
				'effect',
				'sync_parent_id',
				'sync_child_id',
				'slider',
			)
		);

		$child_id_key  = '';
		$parent_id_key = '';

		if ( 'child' !== $atts['carousel_sync'] ) {
			$child_id_key = array_search( 'sync_child_id', $include, true );
		}
		if ( 'parent' !== $atts['carousel_sync'] ) {
			$parent_id_key = array_search( 'sync_parent_id', $include, true );
		}

		if ( isset( $include[ $parent_id_key ] ) ) {
			unset( $include[ $parent_id_key ] );
		}
		if ( isset( $include[ $child_id_key ] ) ) {
			unset( $include[ $child_id_key ] );
		}
		if ( empty( $atts['autoplay'] ) || 'yes' !== $atts['autoplay'] ) {
			$speed_key = array_search( 'speed', $include, true );

			if ( isset( $include[ $speed_key ] ) ) {
				unset( $include[ $speed_key ] );
			}
		}

		foreach ( $atts as $key => $value ) {
			if ( ! in_array( $key, $include, true ) || ! $value || 'no' === $value ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$value = '\'' . wp_json_encode( $value ) . '\'';
			} else {
				$value = '"' . esc_attr( $value ) . '"';
			}

			$output[] = 'data-' . esc_attr( $key ) . '=' . $value . '';
		}

		if ( $slides_per_view ) {
			$style_attr = '--wd-col-lg:' . $slides_per_view . ';';
		} else {
			$style_attr = ! empty( $items['desktop'] ) ? '--wd-col-lg:' . $items['desktop'] . ';' : '';
		}

		$style_attr .= ! empty( $items['tablet'] ) ? '--wd-col-md:' . $items['tablet'] . ';' : '';
		$style_attr .= ! empty( $items['mobile'] ) ? '--wd-col-sm:' . $items['mobile'] . ';' : '';

		if ( isset( $atts['spacing'], $atts['spacing_tablet'], $atts['spacing_mobile'] ) && '' !== $atts['spacing'] && $atts['spacing'] === $atts['spacing_tablet'] && $atts['spacing'] === $atts['spacing_mobile'] ) {
			$style_attr .= '--wd-gap:' . $atts['spacing'] . 'px;';
		} else {
			if ( isset( $atts['spacing'] ) && '' !== (string) $atts['spacing'] ) {
				$style_attr .= '--wd-gap-lg:' . $atts['spacing'] . 'px;';
			}

			if ( isset( $atts['spacing_tablet'] ) && '' !== (string) $atts['spacing_tablet'] && ( empty( $atts['spacing'] ) || $atts['spacing'] !== $atts['spacing_tablet'] ) ) {
				$style_attr .= '--wd-gap-md:' . $atts['spacing_tablet'] . 'px;';
			}

			if ( ! $atts['spacing_mobile'] && in_array( (int) $atts['spacing'], array( 20, 30 ), true ) ) {
				$atts['spacing_mobile'] = 10;
			}

			if ( isset( $atts['spacing_mobile'] ) && '' !== (string) $atts['spacing_mobile'] && ( empty( $atts['spacing_tablet'] ) || $atts['spacing_tablet'] !== $atts['spacing_mobile'] ) ) {
				$style_attr .= '--wd-gap-sm:' . $atts['spacing_mobile'] . 'px;';
			}
		}

		if ( 'yes' === $atts['disable_overflow_carousel'] ) {
			$style_attr .= '--wd-carousel-overflow: visible';
		}

		if ( $style_attr ) {
			$output[] = 'style="' . $style_attr . '"';
		}

		return implode( ' ', $output );
	}
}

if ( ! function_exists( 'woodmart_page_title' ) ) {
	/**
	 * Page title.
	 */
	function woodmart_page_title() {
		global $wp_query, $post;

		if ( function_exists( 'dokan_is_store_page' ) && dokan_is_store_page() ) {
			return '';
		}

		$disable               = false;
		$page_title            = true;
		$breadcrumbs           = woodmart_get_opt( 'breadcrumbs' );
		$style                 = '';
		$page_for_posts        = get_option( 'page_for_posts' );
		$title_class           = ' page-title-';
		$title_type            = 'default';
		$title_design          = woodmart_get_opt( 'page-title-design' );
		$title_size            = woodmart_get_opt( 'page-title-size' );
		$title_color           = woodmart_get_opt( 'page-title-color' );
		$shop_title            = woodmart_get_opt( 'shop_title' );
		$shop_categories       = woodmart_get_opt( 'shop_categories' );
		$single_post_design    = woodmart_get_opt( 'single_post_design' );
		$title_tag             = 'h1';
		$custom_page_title_tag = woodmart_get_opt( 'page_title_tag' );
		$background_image      = woodmart_get_opt( 'title-background' );
		$page_id               = woodmart_page_ID();
		$is_post               = is_singular( 'post' ) || Builder_Data::get_instance()->get_data( 'is_post_layout' );
		$image_id              = false;
		$image_size            = 'full';

		if ( ! empty( $background_image['id'] ) && ! apply_filters( 'woodmart_generate_legacy_page_title_bg', false ) ) {
			$image_id = $background_image['id'];
		}

		if ( ! empty( $background_image['image_size'] ) ) {
			if ( 'custom' === $background_image['image_size'] && ( ! empty( $background_image['image_size_custom_width'] ) || ! empty( $background_image['image_size_custom_height'] ) ) ) {
				$image_size_width  = ! empty( $background_image['image_size_custom_width'] ) ? (int) $background_image['image_size_custom_width'] : 0;
				$image_size_height = ! empty( $background_image['image_size_custom_height'] ) ? (int) $background_image['image_size_custom_height'] : 0;

				$image_size = array( $image_size_width, $image_size_height );
			} elseif ( 'custom' !== $background_image['image_size'] ) {
				$image_size = $background_image['image_size'];
			}
		}

		if ( 'default' !== $custom_page_title_tag && $custom_page_title_tag ) {
			$title_tag = $custom_page_title_tag;
		}

		/*
		 * Builder.
		 */
		$is_builder_element    = Builder_Data::get_instance()->get_data( 'builder' );
		$builder_title_classes = Builder_Data::get_instance()->get_data( 'title_classes' );
		$active_builder        = Builder::get_instance()->is_custom_layout() || 'woodmart_layout' === get_post_type();

		if ( $active_builder && ! $is_builder_element ) {
			return '';
		}
		/*
		 * End Builder.
		 */

		if ( 0 !== (int) $page_id ) {
			$disable = get_post_meta( $page_id, '_woodmart_title_off', true );

			$page_image      = get_post_meta( $page_id, '_woodmart_title_image', true );
			$page_image_size = get_post_meta( $page_id, '_woodmart_title_image_size', true );

			$custom_title_color    = get_post_meta( $page_id, '_woodmart_title_color', true );
			$custom_title_bg_color = get_post_meta( $page_id, '_woodmart_title_bg_color', true );

			if ( is_array( $page_image ) && ! empty( $page_image['id'] ) ) {
				$image_id = $page_image['id'];
			} elseif ( $page_image && is_string( $page_image ) ) {
				$image_id = attachment_url_to_postid( $page_image );
			}

			if ( ( is_array( $page_image ) && ! empty( $page_image['id'] ) ) || ( $page_image && is_string( $page_image ) ) ) {
				if ( 'custom' === $page_image_size ) {
					$image_size_width  = get_post_meta( $page_id, '_woodmart_title_image_size_custom_width', true );
					$image_size_height = get_post_meta( $page_id, '_woodmart_title_image_size_custom_height', true );

					if ( $image_size_width || $image_size_height ) {
						$page_image_size = array( (int) $image_size_width, (int) $image_size_height );
					} else {
						$page_image_size = 'full';
					}
				}

				$image_size = $page_image_size ? $page_image_size : 'full';
			}

			if ( $custom_title_bg_color && ! is_array( $custom_title_bg_color ) ) {
				$style .= 'background-color: ' . $custom_title_bg_color . ';';
			}

			if ( '' !== $custom_title_color && 'default' !== $custom_title_color ) {
				$title_color = $custom_title_color;
			}
		}

		if ( 'disable' === $title_design ) {
			$page_title = false;

			if ( $is_builder_element ) {
				$title_design = 'default';
				$page_title   = true;
			}
		}

		if ( ! $page_title || ( ! $page_title && ! $breadcrumbs ) ) {
			$disable = true;
		}

		if ( $is_post && 'large_image' === $single_post_design ) {
			$disable = false;
		}

		if ( $disable && ! $is_builder_element ) {
			return '';
		}

		woodmart_enqueue_inline_style( 'page-title' );

		if ( woodmart_woocommerce_installed() && ( is_product_taxonomy() || woodmart_is_shop_archive() ) ) {
			woodmart_enqueue_inline_style( 'woo-shop-page-title' );

			if ( ! woodmart_get_opt( 'shop_title' ) ) {
				woodmart_enqueue_inline_style( 'woo-shop-opt-without-title' );
			}

			if ( woodmart_get_opt( 'shop_categories' ) ) {
				woodmart_enqueue_inline_style( 'shop-title-categories' );
				woodmart_enqueue_inline_style( 'woo-categories-loop-nav-mobile-accordion' );
			}
		}

		$title_class .= $title_type;
		$title_class .= ' title-size-' . $title_size;
		$title_class .= ' title-design-' . $title_design;

		if ( $builder_title_classes ) {
			$title_class .= $builder_title_classes;
		}

		if ( 'large_image' === $single_post_design && $is_post ) {
			$title_class .= ' color-scheme-light';
		} else {
			$title_class .= ' color-scheme-' . $title_color;
		}

		do_action( 'woodmart_page_title', $title_class, $style, $title_tag, $breadcrumbs, $page_title );

		if ( 'large_image' === $single_post_design && $is_post ) {
			woodmart_enqueue_inline_style( 'post-design-large-image' );

			if ( empty( $page_image['id'] ) ) {
				$post_image_id = get_post_thumbnail_id( $page_id );

				if ( $post_image_id ) {
					$image_id   = $post_image_id;
					$image_size = 'full';
				}
			}

			$title_class .= ' post-title-large-image';
			?>
				<div class="wd-page-title page-title<?php echo esc_attr( $title_class ); ?>" style="<?php echo esc_attr( $style ); ?>">
					<div class="wd-page-title-bg wd-fill">
						<?php echo woodmart_otf_get_image_html( $image_id, $image_size ); // phpcs:ignore ?>
					</div>
					<div class="container">
						<?php if ( get_the_category_list( ', ' ) ) : ?>
							<div class="wd-post-cat wd-style-with-bg"><?php echo get_the_category_list( ', ' ); // phpcs:ignore ?></div>
						<?php endif ?>

						<<?php echo esc_attr( $title_tag ); ?> class="entry-title title"><?php the_title(); ?></<?php echo esc_attr( $title_tag ); ?>>

						<?php do_action( 'woodmart_page_title_after_title' ); ?>

						<div class="wd-post-meta">
							<?php
							woodmart_enqueue_inline_style( 'blog-mod-author' );
							woodmart_enqueue_inline_style( 'blog-mod-comments-button' );
							?>
							<div class="wd-post-author wd-meta-author">
								<?php woodmart_post_meta_author( true, 'long' ); ?>
							</div>
							<?php if ( woodmart_get_opt( 'blog_published_date', true ) ) : ?>
								<div class="wd-modified-date">
									<?php woodmart_post_modified_date(); ?>
								</div>

								<div class="wd-post-date wd-style-default">
									<time class="published" datetime="<?php echo get_the_date( 'c' ); // phpcs:ignore ?>">
										<?php echo esc_html( _x( 'On', 'meta-date', 'woodmart' ) ) . ' ' . get_the_date(); ?>
									</time>
								</div>
							<?php endif; ?>
							<?php if ( comments_open() || pings_open() ) : ?>
							<div class="wd-post-reply wd-meta-reply wd-style-1">
								<?php woodmart_post_meta_reply(); ?>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php
			return '';
		}

		// Heading for pages.
		if ( $post && 'page' === $post->post_type && ( ! $page_for_posts || ! is_page( $page_for_posts ) ) || Builder::get_instance()->has_custom_layout( 'single_product' ) || ( Builder::get_instance()->has_custom_layout( 'cart' ) || Builder::get_instance()->has_custom_layout( 'empty_cart' ) ) || Builder::get_instance()->has_custom_layout( 'checkout_content' ) || Builder::get_instance()->has_custom_layout( 'checkout_form' ) ) {
			$title = get_the_title();

			if ( woodmart_woocommerce_installed() && is_wc_endpoint_url( 'lost-password' ) ) {
				$title = esc_html__( 'Lost password', 'woocommerce' );
			}
			?>
				<div class="wd-page-title page-title <?php echo esc_attr( $title_class ); ?>" style="<?php echo esc_attr( $style ); ?>">
					<div class="wd-page-title-bg wd-fill">
						<?php echo woodmart_otf_get_image_html( $image_id, $image_size ); // phpcs:ignore ?>
					</div>
					<div class="container">
						<?php if ( woodmart_woocommerce_installed() && ( is_cart() || is_checkout() || ( Builder::get_instance()->has_custom_layout( 'cart' ) || Builder::get_instance()->has_custom_layout( 'empty_cart' ) ) || Builder::get_instance()->has_custom_layout( 'checkout_content' ) || Builder::get_instance()->has_custom_layout( 'checkout_form' ) ) ) : ?>
							<?php woodmart_checkout_steps(); ?>
						<?php else : ?>
							<?php if ( Builder::get_instance()->has_custom_layout( 'single_product' ) || $page_title ) : ?>
								<<?php echo esc_attr( $title_tag ); ?> class="entry-title title">
									<?php echo esc_html( $title ); ?>
								</<?php echo esc_attr( $title_tag ); ?>>

								<?php do_action( 'woodmart_page_title_after_title' ); ?>
							<?php endif; ?>

							<?php if ( $breadcrumbs ) : ?>
								<?php woodmart_current_breadcrumbs( 'pages' ); ?>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			<?php
			return '';
		}

		// Heading for blog and archives.
		if ( $post && 'post' === $post->post_type || woodmart_is_blog_archive() ) {
			$title = ( ! empty( $page_for_posts ) ) ? get_the_title( $page_for_posts ) : esc_html__( 'Blog', 'woodmart' );

			if ( is_tag() ) {
				$title = esc_html__( 'Tag Archives: ', 'woodmart' ) . single_tag_title( '', false );
			}

			if ( is_category() ) {
				$title = '<span>' . single_cat_title( '', false ) . '</span>';
			}

			if ( is_date() ) {
				if ( is_day() ) {
					$title = esc_html__( 'Daily Archives: ', 'woodmart' ) . get_the_date();
				} elseif ( is_month() ) {
					$title = esc_html__( 'Monthly Archives: ', 'woodmart' ) . get_the_date( _x( 'F Y', 'monthly archives date format', 'woodmart' ) );
				} elseif ( is_year() ) {
					$title = esc_html__( 'Yearly Archives: ', 'woodmart' ) . get_the_date( _x( 'Y', 'yearly archives date format', 'woodmart' ) );
				} else {
					$title = esc_html__( 'Archives', 'woodmart' );
				}
			}

			if ( is_author() ) {
				the_post();

				$title = esc_html__( 'Posts by ', 'woodmart' ) . '<span class="vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" title="' . esc_attr( get_the_author() ) . '" rel="me">' . get_the_author() . '</a></span>';

				rewind_posts();
			}

			if ( is_search() ) {
				$title = esc_html__( 'Search Results for: ', 'woodmart' ) . get_search_query();
			}

			?>
				<div class="wd-page-title page-title <?php echo esc_attr( $title_class ); ?> title-blog" style="<?php echo esc_attr( $style ); ?>">
					<div class="wd-page-title-bg wd-fill">
						<?php echo woodmart_otf_get_image_html( $image_id, $image_size ); // phpcs:ignore ?>
					</div>
					<div class="container">
						<?php if ( $page_title && is_single() ) : ?>
							<h3 class="entry-title title"><?php echo wp_kses( $title, woodmart_get_allowed_html() ); ?></h3>
						<?php elseif ( $page_title ) : ?>
							<<?php echo esc_attr( $title_tag ); ?> class="entry-title title"><?php echo wp_kses( $title, woodmart_get_allowed_html() ); ?></<?php echo esc_attr( $title_tag ); ?>>
						<?php endif; ?>

						<?php do_action( 'woodmart_page_title_after_title' ); ?>

						<?php if ( $breadcrumbs && ! is_search() ) : ?>
							<?php woodmart_current_breadcrumbs( 'pages' ); ?>
						<?php endif; ?>
					</div>
				</div>
			<?php
			return '';
		}

		// Heading for portfolio.
		if ( $post && 'portfolio' === $post->post_type || woodmart_is_portfolio_archive() ) {
			if ( woodmart_get_opt( 'single_portfolio_title_in_page_title' ) && is_singular( 'portfolio' ) ) {
				$title = get_the_title();
			} else {
				$title = get_the_title( woodmart_get_portfolio_page_id() );
			}

			if ( is_tax( 'project-cat' ) ) {
				$title = single_term_title( '', false );
			}

			?>
			<div class="wd-page-title page-title <?php echo esc_attr( $title_class ); ?> title-blog" style="<?php echo esc_attr( $style ); ?>">
				<div class="wd-page-title-bg wd-fill">
					<?php echo woodmart_otf_get_image_html( $image_id, $image_size ); // phpcs:ignore ?>
				</div>
				<div class="container">
					<?php if ( $page_title ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="entry-title title"><?php echo esc_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>

					<?php do_action( 'woodmart_page_title_after_title' ); ?>

					<?php if ( $breadcrumbs ) : ?>
						<?php woodmart_current_breadcrumbs( 'pages' ); ?>
					<?php endif; ?>
				</div>
			</div>
			<?php
			return '';
		}

		// Page heading for shop page.
		if ( ( woodmart_is_shop_archive() || Builder::get_instance()->has_custom_layout( 'shop_archive' ) ) && ( $shop_categories || $shop_title ) ) {
			if ( is_product_category() ) {
				$cat       = $wp_query->get_queried_object();
				$cat_image = woodmart_get_category_page_title_image( $cat );

				if ( is_array( $cat_image ) && ! empty( $cat_image['id'] ) ) {
					$image_id   = $cat_image['id'];
					$image_size = 'full';
				} elseif ( $cat_image && is_string( $cat_image ) ) {
					$image_id   = attachment_url_to_postid( $cat_image );
					$image_size = 'full';
				}
			}

			if ( is_product_category() || is_product_tag() ) {
				$title_class .= ' with-back-btn';
			}

			if ( ! $shop_title ) {
				$title_class .= ' without-title';
			}

			if ( woodmart_get_opt( 'shop_categories' ) ) {
				$title_class .= ' wd-nav-' . woodmart_get_opt( 'mobile_categories_layout', 'accordion' ) . '-mb-on';
			}

			$title_class .= woodmart_get_old_classes( ' nav-shop' );

			?>
			<?php if ( apply_filters( 'woocommerce_show_page_title', true ) && ! is_singular( 'product' ) ) : ?>
				<div class="wd-page-title page-title <?php echo esc_attr( $title_class ); ?>" style="<?php echo esc_attr( $style ); ?>">
					<div class="wd-page-title-bg wd-fill">
						<?php echo woodmart_otf_get_image_html( $image_id, $image_size ); // phpcs:ignore ?>
					</div>
					<div class="container">
						<div class="wd-title-wrapp">
							<?php if ( is_product_category() || is_product_tag() ) : ?>
								<?php woodmart_back_btn(); ?>
							<?php endif ?>

							<?php if ( $shop_title ) : ?>
								<<?php echo esc_attr( $title_tag ); ?> class="entry-title title">
									<?php woocommerce_page_title(); ?>
								</<?php echo esc_attr( $title_tag ); ?>>

								<?php do_action( 'woodmart_page_title_after_title' ); ?>
							<?php endif; ?>
						</div>

						<?php if ( ! is_search() && ! is_singular( 'product' ) && $shop_categories ) : ?>
							<?php woodmart_product_categories_nav(); ?>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
			<?php
			return '';
		}
	}

	add_action( 'woodmart_after_header', 'woodmart_page_title', 20 );
}

if ( ! function_exists( 'woodmart_back_btn' ) ) {
	function woodmart_back_btn() {
		woodmart_enqueue_js_script( 'back-history' );
		?>
			<div class="wd-back-btn wd-action-btn wd-style-icon<?php echo woodmart_get_old_classes( ' woodmart-back-btn' ); ?>"><a href="#" rel="nofollow noopener" aria-label="<?php esc_attr_e( 'Go back', 'woodmart' ); ?>"></a></div>
		<?php
	}
}

// **********************************************************************//
// ! Recursive function to get page title image for the category or
// ! take it from some parent term
// **********************************************************************//

if ( ! function_exists( 'woodmart_get_category_page_title_image' ) ) {
	function woodmart_get_category_page_title_image( $cat ) {
		$taxonomy  = 'product_cat';
		$meta_key  = 'title_image';
		$cat_image = get_term_meta( $cat->term_id, $meta_key, true );

		if ( is_array( $cat_image ) && ! empty( $cat_image['id'] ) ) {
			return $cat_image;
		} elseif ( is_array( $cat_image ) && ! empty( $cat_image['url'] ) ) {
			return $cat_image['url'];
		} elseif ( ! empty( $cat_image ) && ! is_array( $cat_image ) ) {
			return $cat_image;
		} elseif ( ! empty( $cat->parent ) && apply_filters( 'woodmart_show_parent_category_page_title_image', true ) ) {
			$parent = get_term_by( 'term_id', $cat->parent, $taxonomy );
			return woodmart_get_category_page_title_image( $parent );
		} else {
			return '';
		}
	}
}

if ( ! function_exists( 'woodmart_breadcrumbs' ) ) {
	/**
	 * Breadcrumbs function.
	 * Snippet from http://dimox.net/wordpress-breadcrumbs-without-a-plugin/
	 *
	 * @param string $builder_wrapper_classes Wrapper classes.
	 * @return void
	 */
	function woodmart_breadcrumbs( $builder_wrapper_classes = '' ) {
		global $post;

		$home_link = home_url( '/' );

		$text = [
			'home'     => esc_html__( 'Home', 'woodmart' ),
			'category' => esc_html__( 'Archive by Category "%s"', 'woodmart' ),
			'search'   => esc_html__( 'Search Results for "%s" Query', 'woodmart' ),
			'tag'      => esc_html__( 'Posts Tagged "%s"', 'woodmart' ),
			'author'   => esc_html__( 'Articles Posted by %s', 'woodmart' ),
			'404'      => esc_html__( 'Error 404', 'woodmart' )
		];

		$options = apply_filters( 'woodmart_breadcrumbs_options',
			array(
				'show_current_post' => 0, // show current post.
				'show_current'      => 1,
				'show_on_home'      => 0,
				'show_home_link'    => 1,
				'show_title'        => 1,
				'delimiter'         => '<span class="wd-delimiter"></span>',
				'before'            => '<span class="wd-last">',
				'after'             => '</span>',
				'link_format'       => '<a href="%1$s">%2$s</a>',
			)
		);

		if ( is_front_page() ) {
			if ( $options['show_on_home'] ) {
				echo '<nav class="wd-breadcrumbs' . $builder_wrapper_classes . '"><a href="' . $home_link . '">' . $text['home'] . '</a></nav>';
			}

			return;
		}

		$link_before  = '<span>';
		$link_after   = '</span>';
		$parent_id    = ( ! empty( $post ) && is_a( $post, 'WP_Post' ) ) ? $post->post_parent : 0;
		$parent_id_2  = $parent_id;
		$frontpage_id = get_option( 'page_on_front' );
		$projects_id  = woodmart_get_portfolio_page_id();
		$schema_items = array();

		if ( get_query_var( 'paged' ) ) {
			$options['before'] = '<a class="wd-last-link" href="' . get_pagenum_link( 1 ) . '">';
			$options['after']  = '</a>';

			$link_before = '<span class="wd-last">';
			$link_after  = '</span>';
		}

		echo '<nav class="wd-breadcrumbs' . esc_attr( $builder_wrapper_classes ) . '">';

		ob_start();

		if ( $options['show_home_link'] ) {
			echo '<a href="' . $home_link . '">' . $text['home'] . '</a>';
			if ( $frontpage_id == 0 || $parent_id != $frontpage_id ) {
				if ( is_home() ) {
					echo $options['delimiter'] . $options['before'] . esc_html__( 'Blog', 'woodmart' ) . $options['after'];
				} else {
					echo $options['delimiter'];
				}
			}
		}

		if ( is_category() ) {
			$this_cat = get_category( get_query_var( 'cat' ), false );
			if ( $this_cat->parent ) {
				$cats = get_term_parents_list( $this_cat->parent, 'category', array( 'separator' => $options['delimiter'] ) );

				if ( $options['show_current'] == 0 ) {
					$delimiter = $options['delimiter'];
					$cats = preg_replace( "#^(.+)$delimiter$#", '$1', $cats );
				}
				echo $cats;
			}
			if ( $options['show_current'] == 1 ) {
				echo wp_kses_post( $options['before'] ) . sprintf( $text['category'], single_cat_title( '', false ) ) . wp_kses_post( $options['after'] );
			}
		} elseif ( ! is_single() && 'portfolio' === get_post_type() ) {
			if ( is_tax( 'project-cat' ) ) {
				$cat  = get_queried_object();
				$cats = get_term_parents_list( $cat->term_id, 'project-cat', array( 'separator' => $options['delimiter'] ) );

				$cats = substr_replace( $cats, '', -strlen( $options['delimiter'] ) );

				echo $cats;
			} else {
				echo $options['before'] . get_the_title( $projects_id ) . $options['after'];
			}
		} elseif ( is_search() ) {
			echo wp_kses_post( $options['before'] ) . sprintf( $text['search'], get_search_query() ) . wp_kses_post( $options['after'] );

		} elseif ( is_day() ) {
			echo sprintf( $options['link_format'], get_year_link( get_the_time( 'Y' ) ), get_the_time( 'Y' ) ) . $options['delimiter'];
			echo sprintf( $options['link_format'], get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ), get_the_time( 'F' ) ) . $options['delimiter'];
			echo wp_kses_post( $options['before'] ) . get_the_time( 'd' ) . wp_kses_post( $options['after'] );
		} elseif ( is_month() ) {
			echo sprintf( $options['link_format'], get_year_link( get_the_time( 'Y' ) ), get_the_time( 'Y' ) ) . $options['delimiter'];
			echo wp_kses_post( $options['before'] ) . get_the_time( 'F' ) . wp_kses_post( $options['after'] );

		} elseif ( is_year() ) {
			echo wp_kses_post( $options['before'] ) . get_the_time( 'Y' ) . wp_kses_post( $options['after'] );

		} elseif ( is_single() && ! is_attachment() ) {
			if ( get_post_type() == 'portfolio' ) {
				printf( $options['link_format'], get_the_permalink( $projects_id ), get_the_title( $projects_id ) );
				if ( $options['show_current'] == 1 ) {
					echo $options['delimiter'] . $options['before'] . get_the_title() . $options['after'];
				}

			} elseif ( get_post_type() != 'post' ) {
				$post_type = get_post_type_object( get_post_type() );
				$slug      = $post_type->rewrite;
				if ( is_array( $slug ) ) {
					printf( $options['link_format'], $home_link . $slug['slug'] . '/', $post_type->labels->singular_name );
				} else {
					printf( $options['link_format'], $home_link, $post_type->labels->singular_name );
				}

				if ( $options['show_current'] == 1 ) {
					echo $options['delimiter'] . $options['before'] . get_the_title() . $options['after'];
				}
			} else {
				$cat = get_the_category();

				if ( $cat && isset( $cat[0] ) ) {
					$cat  = $cat[0];
					$cats = get_category_parents( $cat, true, $options['delimiter'] );

					if ( $options['show_current'] == 0 ) {
						$delimiter = $options['delimiter'];
						$cats = preg_replace( "#^(.+)$delimiter$#", '$1', $cats );
					}
					$cats = substr_replace( $cats, '', -strlen( $options['delimiter'] ) );
					if ( $options['show_title'] == 0 ) {
						$cats = preg_replace( '/ title="(.*?)"/', '', $cats );
					}
					echo $cats;

					if ( $options['show_current_post'] == 1 ) {
						echo wp_kses_post( $options['before'] ) . get_the_title() . wp_kses_post( $options['after'] );
					}
				}
			}
		} elseif ( ! is_single() && ! is_page() && get_post_type() != 'post' && ! is_404() ) {
			$post_type = get_post_type_object( get_post_type() );
			if ( is_object( $post_type ) ) {
				echo wp_kses_post( $options['before'] ) . $post_type->labels->singular_name . wp_kses_post( $options['after'] );
			}
		} elseif ( is_attachment() ) {
			$parent = get_post( $parent_id );
			$cat    = get_the_category( $parent->ID );
			$cat    = $cat[0];
			if ( $cat ) {
				$cats = get_category_parents( $cat, true, $options['delimiter'] );
				if ( $options['show_title'] == 0 ) {
					$cats = preg_replace( '/ title="(.*?)"/', '', $cats );
				}
				echo wp_kses_post( $cats );
			}
			printf( $options['link_format'], get_permalink( $parent ), $parent->post_title );
			if ( $options['show_current'] == 1 ) {
				echo $options['delimiter'] . $options['before'] . get_the_title() . $options['after'];
			}
		} elseif ( is_page() && ! $parent_id ) {
			if ( $options['show_current'] == 1 ) {
				echo wp_kses_post( $options['before'] ) . get_the_title() . wp_kses_post( $options['after'] );
			}
		} elseif ( is_page() && $parent_id ) {
			if ( $parent_id != $frontpage_id ) {
				$breadcrumbs = array();
				while ( $parent_id ) {
					$page = get_page( $parent_id );
					if ( $parent_id != $frontpage_id ) {
						$breadcrumbs[] = sprintf( $options['link_format'], get_permalink( $page->ID ), get_the_title( $page->ID ) );
					}
					$parent_id = $page->post_parent;
				}
				$breadcrumbs = array_reverse( $breadcrumbs );
				for ( $i = 0; $i < count( $breadcrumbs ); $i++ ) {
					echo wp_kses_post( $breadcrumbs[ $i ] );
					if ( $i != count( $breadcrumbs ) - 1 ) {
						echo $options['delimiter'];
					}
				}
			}
			if ( $options['show_current'] == 1 ) {
				if ( $options['show_home_link'] || ( $parent_id_2 != 0 && $parent_id_2 != $frontpage_id ) ) {
					echo $options['delimiter'];
				}
				echo wp_kses_post( $options['before'] ) . get_the_title() . wp_kses_post( $options['after'] );
			}
		} elseif ( is_tag() ) {
			echo wp_kses_post( $options['before'] ) . sprintf( $text['tag'], single_tag_title( '', false ) ) . wp_kses_post( $options['after'] );

		} elseif ( is_author() ) {
			global $author;
			$userdata = get_userdata( $author );
			echo wp_kses_post( $options['before'] ) . sprintf( $text['author'], $userdata->display_name ) . wp_kses_post( $options['after'] );

		} elseif ( is_404() ) {
			echo wp_kses_post( $options['before'] ) . $text['404'] . wp_kses_post( $options['after'] );
		}

		$content = ob_get_clean();

		echo wp_kses_post( $content );

		if ( get_query_var( 'paged' ) ) {
			echo $options['delimiter'] . $link_before;
			echo esc_html__( 'Page', 'woodmart' ) . ' ' . get_query_var( 'paged' );
			echo $link_after;
		}

		echo '</nav>';

		if ( $content ) {
			$position = 1;
			$parts    = explode( $options['delimiter'], $content );

			foreach ( $parts as $part ) {
				if ( strpos( $part, '<a' ) !== false ) {
					preg_match( '/<a href="([^"]+)">([^<]+)<\/a>/', $part, $matches );
					if ( ! empty( $matches ) ) {
						$schema_items[] = array(
							'@type'    => 'ListItem',
							'position' => $position++,
							'name'     => $matches[2],
							'item'     => $matches[1],
						);
					}
				}
			}

			if ( is_category() ) {
				$schema_items[] = array(
					'@type'    => 'ListItem',
					'position' => $position++,
					'name'     => single_cat_title( '', false ),
				);
			} elseif ( is_tag() ) {
				$schema_items[] = array(
					'@type'    => 'ListItem',
					'position' => $position++,
					'name'     => single_tag_title( '', false ),
				);
			} elseif ( is_page() || is_single() ) {
				$schema_items[] = array(
					'@type'    => 'ListItem',
					'position' => $position++,
					'name'     => get_the_title(),
				);
			}

			if ( $schema_items ) {
				Breadcrumbs::get_instance()->set_schema_items( $schema_items );
			}
		}
	}
}

// **********************************************************************//
// ! Cookies law popup
// **********************************************************************//

if ( ! function_exists( 'woodmart_cookies_popup' ) ) {
	add_action( 'woodmart_before_wp_footer', 'woodmart_cookies_popup', 300 );

	function woodmart_cookies_popup() {
		if ( ! woodmart_get_opt( 'cookies_info' ) ) {
			return;
		}

		woodmart_enqueue_js_script( 'cookies-popup' );
		woodmart_enqueue_inline_style( 'cookies-popup' );

		$page_id = woodmart_get_opt( 'cookies_policy_page' );

		?>
			<div class="wd-cookies-popup<?php echo woodmart_get_old_classes( ' woodmart-cookies-popup' ); ?>" role="complementary" aria-label="<?php esc_attr_e( 'Cookies', 'woodmart' ); ?>">
				<div class="wd-cookies-inner<?php echo woodmart_get_old_classes( ' woodmart-cookies-inner' ); ?>">
					<div class="cookies-info-text">
						<?php echo do_shortcode( woodmart_get_opt( 'cookies_text' ) ); ?>
					</div>
					<div class="cookies-buttons">
						<?php if ( $page_id ) : ?>
							<a href="<?php echo get_permalink( $page_id ); ?>" class="cookies-more-btn">
								<?php esc_html_e( 'More info', 'woodmart' ); ?>
								<span class="screen-reader-text"><?php esc_html_e( 'More info', 'woodmart' ); ?></span>
							</a>
						<?php endif ?>
						<a href="#" rel="nofollow noopener" class="btn cookies-accept-btn"><?php esc_html_e( 'Accept', 'woodmart' ); ?></a>
					</div>
				</div>
			</div>
		<?php
	}
}


class WOODMART_Custom_Walker_Category extends Walker_Category {

	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		/** This filter is documented in wp-includes/category-template.php */
		$cat_name = apply_filters(
			'list_cats',
			esc_attr( $category->name ),
			$category
		);

		// Don't generate an element if the category name is empty.
		if ( ! $cat_name ) {
			return;
		}

		$link = get_term_link( $category );

		if ( ! empty( $args['active_filter_url'] ) ) {
			if ( ! empty( $args['current_category'] ) && $category->term_id === $args['current_category'] ) {
				$link = get_permalink( wc_get_page_id( 'shop' ) );
			}

			$parsed_url = wp_parse_url( $args['active_filter_url'] );

			if ( ! empty( $parsed_url['query'] ) ) {
				wp_parse_str( $parsed_url['query'], $query_args );

				$link = add_query_arg( $query_args, $link );
			}
		}

		$link = '<a class="pf-value" href="' . esc_url( $link ) . '" data-val="' . esc_attr( $category->slug ) . '" data-title="' . esc_attr( $category->name ) . '" ';
		if ( $args['use_desc_for_title'] && ! empty( $category->description ) ) {
			/**
			 * Filters the category description for display.
			 *
			 * @since 1.2.0
			 *
			 * @param string $description Category description.
			 * @param object $category    Category object.
			 */
			$link .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';
		}

		$link .= '>';
		$link .= $cat_name . '</a>';

		if ( ! empty( $args['feed_image'] ) || ! empty( $args['feed'] ) ) {
			$link .= ' ';

			if ( empty( $args['feed_image'] ) ) {
				$link .= '(';
			}

			$link .= '<a href="' . esc_url( get_term_feed_link( $category->term_id, $category->taxonomy, $args['feed_type'] ) ) . '"';

			if ( empty( $args['feed'] ) ) {
				$alt = ' alt="' . sprintf( esc_html__( 'Feed for all posts filed under %s', 'woodmart' ), $cat_name ) . '"';
			} else {
				$alt   = ' alt="' . $args['feed'] . '"';
				$name  = $args['feed'];
				$link .= empty( $args['title'] ) ? '' : $args['title'];
			}

			$link .= '>';

			if ( empty( $args['feed_image'] ) ) {
				$link .= $name;
			} else {
				$link .= "<img src='" . $args['feed_image'] . "'$alt" . ' />';
			}
			$link .= '</a>';

			if ( empty( $args['feed_image'] ) ) {
				$link .= ')';
			}
		}

		if ( ! empty( $args['show_count'] ) ) {
			$link .= ' (' . number_format_i18n( $category->count ) . ')';
		}
		if ( 'list' == $args['style'] ) {
			$output     .= "\t<li";
			$css_classes = array(
				'cat-item',
				'cat-item-' . $category->term_id,
			);

			if ( ! empty( $args['current_category'] ) ) {
				// 'current_category' can be an array, so we use `get_terms()`.
				$_current_terms = get_terms(
					$category->taxonomy,
					array(
						'include'    => $args['current_category'],
						'hide_empty' => false,
					)
				);

				foreach ( $_current_terms as $_current_term ) {
					if ( $category->term_id == $_current_term->term_id ) {
						$css_classes[] = 'current-cat wd-active';
					} elseif ( $category->term_id == $_current_term->parent ) {
						$css_classes[] = 'current-cat-parent wd-current-active-parent';
					}
					while ( $_current_term->parent ) {
						if ( $category->term_id == $_current_term->parent ) {
							$css_classes[] = 'current-cat-ancestor wd-current-active-ancestor';
							break;
						}
						$_current_term = get_term( $_current_term->parent, $category->taxonomy );
					}
				}
			}

			/**
			 * Filters the list of CSS classes to include with each category in the list.
			 *
			 * @since 4.2.0
			 *
			 * @see wp_list_categories()
			 *
			 * @param array  $css_classes An array of CSS classes to be applied to each list item.
			 * @param object $category    Category data object.
			 * @param int    $depth       Depth of page, used for padding.
			 * @param array  $args        An array of wp_list_categories() arguments.
			 */
			$css_classes = implode( ' ', apply_filters( 'category_css_class', $css_classes, $category, $depth, $args ) );

			$output .= ' class="' . $css_classes . '"';
			$output .= ">$link\n";
		} elseif ( isset( $args['separator'] ) ) {
			$output .= "\t$link" . $args['separator'] . "\n";
		} else {
			$output .= "\t$link<br />\n";
		}
	}

	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( ! $element || ( 0 === $element->count && ! empty( $args[0]['hide_empty'] ) ) ) {
			return;
		}
		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}
}

if ( ! function_exists( 'woodmart_mobile_menu' ) ) {
	function woodmart_mobile_menu() {

		$menu_locations = get_nav_menu_locations();

		$location = apply_filters( 'woodmart_main_menu_location', 'main-menu' );

		$menu_link = get_admin_url( null, 'nav-menus.php' );

		$search_args        = array();
		$nav_classes        = '';
		$tab_classes        = '';
		$extra_menu_classes = '';
		$settings           = whb_get_settings();

		$toolbar_fields = woodmart_get_opt( 'sticky_toolbar_fields' ) ? woodmart_get_opt( 'sticky_toolbar_fields' ) : array();

		if ( isset( $settings['burger'] ) || in_array( 'mobile', $toolbar_fields ) || in_array( 'search_args', $toolbar_fields ) ) {
			$mobile_categories      = isset( $settings['burger']['categories_menu'] ) ? $settings['burger']['categories_menu'] : false;
			$search_form            = isset( $settings['burger']['search_form'] ) ? $settings['burger']['search_form'] : true;
			$close_btn              = isset( $settings['burger']['close_btn'] ) ? $settings['burger']['close_btn'] : false;
			$menu_layout            = isset( $settings['burger']['menu_layout'] ) ? $settings['burger']['menu_layout'] : 'dropdown';
			$position               = isset( $settings['burger']['position'] ) ? $settings['burger']['position'] : 'left';
			$mobile_categories_menu = ( $mobile_categories ) ? $settings['burger']['menu_id'] : '';
			$primary_menu_title     = ! empty( $settings['burger']['primary_menu_title'] ) ? $settings['burger']['primary_menu_title'] : esc_html__( 'Menu', 'woodmart' );
			$secondary_menu_title   = ! empty( $settings['burger']['secondary_menu_title'] ) ? $settings['burger']['secondary_menu_title'] : esc_html__( 'Categories', 'woodmart' );

			$search_extra_content = 'disable';

			if ( ! empty( $settings['burger']['search_extra_content_enabled'] ) ) {
				$search_extra_content = ! empty( $settings['burger']['search_extra_content'] ) ? $settings['burger']['search_extra_content'] : 'inherit';
			}

			$search_args['search_history_enabled'] = isset( $settings['burger']['search_history_enabled'] ) ? $settings['burger']['search_history_enabled'] : false;
			$search_args['popular_requests']       = isset( $settings['burger']['popular_requests'] ) ? $settings['burger']['popular_requests'] : false;
			$search_args['ajax']                   = isset( $settings['burger']['ajax'] ) ? $settings['burger']['ajax'] : true;
			$search_args['count']                  = isset( $settings['burger']['ajax_result_count'] ) ? $settings['burger']['ajax_result_count'] : 20;
			$search_args['post_type']              = isset( $settings['burger']['post_type'] ) ? $settings['burger']['post_type'] : 'product';
			$search_args['include_cat_search']     = isset( $settings['burger']['include_cat_search'] ) ? $settings['burger']['include_cat_search'] : false;
			$search_args['search_extra_content']   = $search_extra_content;
		} else {
			return '';
		}

		$nav_classes .= ' wd-' . $position;
		$nav_classes .= woodmart_get_old_classes( ' wd-' . $position );

		if ( isset( $settings['burger']['submenu_opening_action'] ) ) {
			$submenu_opening_action = $settings['burger']['submenu_opening_action'];

			if ( 'only_arrow' === $submenu_opening_action ) {
				$nav_classes .= ' wd-opener-arrow';
			} elseif ( 'item_and_arrow' === $submenu_opening_action ) {
				$nav_classes .= ' wd-opener-item';
			}
		} else {
			$nav_classes .= ' wd-opener-arrow';
		}

		if ( 'light' === whb_get_dropdowns_color() ) {
			$nav_classes .= ' color-scheme-light';
		}

		$pages_active      = ' wd-active';
		$categories_active = '';

		if ( $mobile_categories && isset( $settings['burger']['tabs_swap'] ) && $settings['burger']['tabs_swap'] ) {
			$pages_active       = '';
			$categories_active .= ' wd-active';
			$tab_classes       .= ' wd-swap';
		}
		if ( ! empty( $settings['burger']['show_html_block'] ) ) {
			$extra_menu_classes .= ' wd-html-block-on';
		}

		$extra_menu_classes .= ' wd-layout-' . $menu_layout;

		if ( 'drilldown' === $menu_layout && isset( $settings['burger']['drilldown_animation'] ) ) {
			$extra_menu_classes .= ' wd-drilldown-' . $settings['burger']['drilldown_animation'];
		}

		if ( ! empty( $settings['burger']['icon_alignment'] ) && 'inherit' !== $settings['burger']['icon_alignment'] ) {
			$extra_menu_classes .= ' wd-icon-' . $settings['burger']['icon_alignment'];
		}

		woodmart_enqueue_js_script( 'mobile-navigation' );

		echo '<div class="mobile-nav wd-side-hidden wd-side-hidden-nav' . esc_attr( $nav_classes ) . '" role="navigation" aria-label="' . esc_attr__( 'Mobile navigation', 'woodmart' ) . '">';

		if ( $close_btn ) {
			echo '<div class="wd-heading"><div class="close-side-widget wd-action-btn wd-style-text wd-cross-icon"><a href="#" rel="nofollow">' . esc_html__( 'Close', 'woodmart' ) . '</a></div></div>';
		}

		if ( $search_form ) {
			woodmart_search_form( $search_args );
		}

		$tab_classes .= woodmart_get_old_classes( ' mobile-menu-tab mobile-nav-tabs' );

		if ( $mobile_categories ) {
			?>
				<ul class="wd-nav wd-nav-mob-tab wd-style-underline<?php echo esc_attr( $tab_classes ); ?>">
					<li class="mobile-tab-title mobile-pages-title <?php echo esc_attr( $pages_active ); ?>" data-menu="pages">
						<a href="#" rel="nofollow noopener">
							<span class="nav-link-text">
								<?php echo esc_html( $primary_menu_title ); ?>
							</span>
						</a>
					</li>
					<li class="mobile-tab-title mobile-categories-title <?php echo esc_attr( $categories_active ); ?>" data-menu="categories">
						<a href="#" rel="nofollow noopener">
							<span class="nav-link-text">
								<?php echo esc_html( $secondary_menu_title ); ?>
							</span>
						</a>
					</li>
				</ul>
			<?php
			if ( ! empty( $mobile_categories_menu ) ) {
				wp_nav_menu(
					array(
						'container'  => '',
						'menu'       => $mobile_categories_menu,
						'menu_class' => 'mobile-categories-menu menu wd-nav wd-nav-mobile wd-dis-hover' . $extra_menu_classes . $categories_active . woodmart_get_old_classes( ' site-mobile-menu' ),
						'walker'     => new Mega_Menu_Walker(),
					)
				);
			} else {
				?>
					<div class="create-nav-msg"><?php esc_html_e( 'Set your categories menu in Header builder -> Mobile -> Mobile menu element -> Show/Hide -> Choose menu', 'woodmart' ); ?></div>
				<?php
			}
		}

		if ( isset( $menu_locations['mobile-menu'] ) && $menu_locations['mobile-menu'] != 0 ) {
			$location = 'mobile-menu';
		}

		if ( has_nav_menu( $location ) ) {
			wp_nav_menu(
				array(
					'container'      => '',
					'theme_location' => $location,
					'menu_class'     => 'mobile-pages-menu menu wd-nav wd-nav-mobile wd-dis-hover' . $extra_menu_classes . $pages_active . woodmart_get_old_classes( ' site-mobile-menu' ),
					'walker'         => new Mega_Menu_Walker(),
				)
			);
		} else {
			?>
			<div class="create-nav-msg">
			<?php
				printf(
					wp_kses(
						__( 'Create your first <a href="%s"><strong>navigation menu here</strong></a>', 'woodmart' ),
						array(
							'a' => array(
								'href' => array(),
							),
						)
					),
					$menu_link
				);
			?>
			</div>
			<?php
		}
		?>

		<?php if ( is_active_sidebar( 'mobile-menu-widgets' ) ) : ?>
			<div class="widgetarea-mobile">
				<?php dynamic_sidebar( 'mobile-menu-widgets' ); ?>
			</div>
			<?php
		endif;

		echo '</div>';
	}

	add_action( 'woodmart_before_wp_footer', 'woodmart_mobile_menu', 130 );

}

// **********************************************************************//
// Header banner
// **********************************************************************//
if ( ! function_exists( 'woodmart_header_banner' ) ) {
	function woodmart_header_banner() {
		if ( ! woodmart_get_opt( 'header_banner' ) ) {
			return;
		}

		woodmart_enqueue_js_script( 'header-banner' );
		woodmart_enqueue_inline_style( 'header-banner' );

		$banner_link = woodmart_get_opt( 'header_banner_link' );

		?>
		<div class="header-banner color-scheme-<?php echo esc_attr( woodmart_get_opt( 'header_banner_color' ) ); ?>" role="complementary" aria-label="<?php esc_attr_e( 'Header banner', 'woodmart' ); ?>">
			<?php if ( woodmart_get_opt( 'header_close_btn' ) ) : ?>
				<div class="close-header-banner wd-action-btn wd-style-icon wd-cross-icon"><a href="#" rel="nofollow noopener" aria-label="<?php esc_attr_e( 'Close header banner', 'woodmart' ); ?>"></a></div>
			<?php endif; ?>

			<?php if ( $banner_link ) : ?>

				<a href="<?php echo esc_url( $banner_link ); ?>" class="header-banner-link wd-fill" aria-label="<?php esc_attr_e( 'Header banner link', 'woodmart' ); ?>"></a>
			<?php endif; ?>

			<div class="container header-banner-container wd-entry-content">
				<?php if ( 'text' === woodmart_get_opt( 'header_banner_content_type', 'text' ) ) : ?>
					<?php echo do_shortcode( woodmart_get_opt( 'header_banner_shortcode' ) ); ?>
				<?php else : ?>
					<?php echo woodmart_get_html_block( woodmart_get_opt( 'header_banner_html_block' ) ); //phpcs:ignore ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	add_action( 'woodmart_before_wp_footer', 'woodmart_header_banner', 160 );
}

// **********************************************************************//
// Get star rating
// **********************************************************************//
if ( ! function_exists( 'woodmart_get_star_rating' ) ) {
	function woodmart_get_star_rating( $rating ) {
		?>
			<div class="star-rating">
				<span style="width:<?php echo ( ( $rating / 5 ) * 100 ); ?>%">
					<?php
					printf(
						esc_html__( '%1$s out of %2$s', 'woodmart' ),
						'<strong class="rating">' . esc_html( $rating ) . '</strong>',
						'<span>5</span>'
					);
					?>
				</span>
			</div>
		<?php
	}
}

// **********************************************************************//
// Get twitter posts
// **********************************************************************//

if ( ! function_exists( 'woodmart_full_screen_main_nav' ) ) {
	function woodmart_full_screen_main_nav() {
		if ( ! whb_is_full_screen_menu() || ( wp_is_mobile() && woodmart_get_opt( 'mobile_optimization', 0 ) ) || woodmart_is_maintenance_active() ) {
			return;
		}

		$settings      = whb_get_settings();
		$location      = apply_filters( 'woodmart_main_menu_location', $settings['mainmenu']['menu_id'] );
		$extra_classes = '';

		$sidebar_name = 'sidebar-full-screen-menu';

		if ( ! empty( $settings['mainmenu']['icon_alignment'] ) && 'inherit' !== $settings['mainmenu']['icon_alignment'] ) {
			$extra_classes .= ' wd-icon-' . $settings['mainmenu']['icon_alignment'];
		}

		woodmart_enqueue_js_script( 'full-screen-menu' );
		woodmart_enqueue_inline_style( 'header-fullscreen-menu' );
		?>
			<div class="wd-fs-menu wd-fill wd-scroll color-scheme-light<?php echo woodmart_get_old_classes( ' full-screen-wrapper' ); ?>" role="navigation" aria-label="<?php esc_attr_e( 'Full screen menu navigation', 'woodmart' ) ?>">
				<div class="wd-fs-close wd-action-btn wd-style-icon wd-cross-icon<?php echo woodmart_get_old_classes( ' full-screen-close-icon' ); ?>">
					<a href="#" rel="nofollow" aria-label="<?php esc_attr_e( 'Close main menu', 'woodmart' ); ?>"></a>
				</div>
				<div class="container wd-scroll-content">
					<div class="wd-fs-inner<?php echo woodmart_get_old_classes( ' full-screen-inner' ); ?>">
						<?php woodmart_get_main_nav( $location, $extra_classes ); ?>

						<?php if ( is_active_sidebar( $sidebar_name ) ) : ?>
							<div class="wd-fs-widget-area">
								<?php dynamic_sidebar( $sidebar_name ); ?>
							</div>
						<?php endif ?>
					</div>
				</div>
			</div>
		<?php
	}

	add_action( 'woodmart_before_wp_footer', 'woodmart_full_screen_main_nav', 120 );
}

// **********************************************************************//
// Get main nav
// **********************************************************************//
if ( ! function_exists( 'woodmart_get_main_nav' ) ) {
	function woodmart_get_main_nav( $location, $extra_classes = '' ) {
		if ( wp_get_nav_menu_object( $location ) && wp_get_nav_menu_items( $location ) ) {
			wp_nav_menu(
				array(
					'container'  => '',
					'menu'       => $location,
					'menu_class' => 'menu wd-nav wd-nav-fs wd-style-underline' . woodmart_get_old_classes( ' full-screen-nav' ) . $extra_classes,
					'walker'     => new Mega_Menu_Walker(),
				)
			);
		} else {
			$menu_link = get_admin_url( null, 'nav-menus.php' );
			?>
					<div class="create-nav-msg">
				<?php
					printf(
						wp_kses(
							__( 'Create your first <a href="%s"><strong>navigation menu here</strong></a>', 'woodmart' ),
							array(
								'a' => array(
									'href' => array(),
								),
							)
						),
						$menu_link
					);
				?>
					</div>
				<?php
		}
		?>
		<?php
	}
}

// **********************************************************************//
// Get sticky social icon
// **********************************************************************//
if ( ! function_exists( 'woodmart_get_sticky_social' ) ) {
	function woodmart_get_sticky_social() {
		if ( ! woodmart_get_opt( 'sticky_social' ) ) {
			return;
		}

		$classes  = 'wd-sticky-social';
		$classes .= ' wd-sticky-social-' . woodmart_get_opt( 'sticky_social_position' );
		$atts     = array(
			'type'     => woodmart_get_opt( 'sticky_social_type' ),
			'el_class' => $classes,
			'style'    => 'colored',
			'size'     => 'custom',
			'form'     => 'square',
			'sticky'   => true,
		);

		echo woodmart_shortcode_social( $atts );

		woodmart_enqueue_js_script( 'sticky-social-buttons' );
		woodmart_enqueue_inline_style( 'sticky-social-buttons' );
	}
	add_action( 'woodmart_before_wp_footer', 'woodmart_get_sticky_social', 200 );
}

// **********************************************************************//
// Get current breadcrumbs
// **********************************************************************//

if ( ! function_exists( 'woodmart_current_breadcrumbs' ) ) {
	/**
	 * Get current breadcrumbs.
	 *
	 * @param string $type post type.
	 */
	function woodmart_current_breadcrumbs( $type, $return = false ) {
		if ( $return ) {
			ob_start();
		}

		if ( woodmart_get_opt( 'yoast_' . $type . '_breadcrumbs' ) && function_exists( 'yoast_breadcrumb' ) ) {
			?>
			<div class="yoast-breadcrumb">
				<?php echo yoast_breadcrumb(); // phpcs:ignore ?>
			</div>
			<?php
		} elseif ( 'shop' === $type && woodmart_woocommerce_installed() ) {
			woocommerce_breadcrumb();
		} else {
			woodmart_breadcrumbs();
		}

		if ( $return ) {
			return ob_get_clean();
		}
	}
}

// **********************************************************************//
// Display icon
// **********************************************************************//
if ( ! function_exists( 'woodmart_display_icon' ) ) {
	function woodmart_display_icon( $img_id, $img_size, $default_size ) {
		$icon                      = woodmart_otf_get_image_html( $img_id, $img_size );
		$icon_src                  = wp_get_attachment_image_url( $img_id );
		$icon_id                   = wp_rand( 999, 9999 );
		$sizes                     = woodmart_get_explode_size( $img_size, $default_size );
		$render_svg_with_image_tag = apply_filters( 'woodmart_render_svg_with_image_tag', true );

		if ( woodmart_is_svg( $icon_src ) ) {
			if ( $render_svg_with_image_tag ) {
				$image_output = '<span class="img-wrapper">' . woodmart_get_svg_html( $img_id, $sizes ) . '</span>';
			} else {
				$image_output = '<span class="img-wrapper"><span class="svg-icon" style="width: ' . $sizes[0] . 'px;height: ' . $sizes[1] . 'px;">' . woodmart_get_any_svg( $icon_src, $icon_id ) . '</span></span>';
			}

			return $image_output;
		} else {
			return '<span class="img-wrapper">' . $icon . '</span>';
		}
	}
}

if ( ! function_exists( 'woodmart_scroll_top_btn' ) ) {
	/**
	 * Scroll top button.
	 *
	 * @return void
	 */
	function woodmart_scroll_top_btn() {
		if ( ! woodmart_get_opt( 'scroll_top_btn' ) ) {
			return;
		}

		woodmart_enqueue_js_script( 'scroll-top' );
		woodmart_enqueue_inline_style( 'scroll-top' );
		?>
		<a href="#" class="scrollToTop" aria-label="<?php esc_attr_e( 'Scroll to top button', 'woodmart' ); ?>"></a>
		<?php
	}

	add_action( 'woodmart_before_wp_footer', 'woodmart_scroll_top_btn' );
}

if ( ! function_exists( 'woodmart_get_scheme_switcher' ) ) {
	/**
	 * Scheme switcher.
	 *
	 * @return void
	 */
	function woodmart_editor_scheme_switcher() {
		if ( ! woodmart_is_elementor_installed() || ! ( woodmart_elementor_is_edit_mode() || woodmart_elementor_is_preview_page() || woodmart_elementor_is_preview_mode() ) ) {
			return;
		}
		?>
			<div class="wd-scheme-switcher">
				<div class="wd-scheme-switcher-dark" data-color="#ffffff">
					<?php esc_html_e( 'Dark', 'woodmart' ); ?>
				</div>
		
				<div class="wd-scheme-switcher-light" data-color="#212121">
					<?php esc_html_e( 'Light', 'woodmart' ); ?>
				</div>
			</div>
		
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('.wd-scheme-switcher > div').on('click', function() {
						var color          = jQuery(this).data('color');
						var websiteWrapper = jQuery('.wd-page-wrapper');
		
						websiteWrapper.css('background-color', color);
		
						if ( '#212121' === color && ! websiteWrapper.hasClass('color-scheme-light') ) {
							websiteWrapper.addClass('color-scheme-light');
						} else if ( '#ffffff' === color ) {
							websiteWrapper.removeClass('color-scheme-light');
						}
					});
				});
			</script>
		<?php
	}
}