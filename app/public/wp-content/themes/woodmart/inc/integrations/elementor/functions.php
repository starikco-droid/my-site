<?php
/**
 * Elementor functions file.
 *
 * @package Woodmart
 */

use Elementor\Plugin;
use XTS\Elementor\Controls\Autocomplete;
use XTS\Elementor\Controls\CSS_Class;
use XTS\Elementor\Controls\Google_Json;
use XTS\Elementor\Controls\Buttons;
use XTS\Modules\Layouts\Main;

if ( ! defined( 'WOODMART_THEME_DIR' ) ) {
	exit; // Direct access not allowed.
}

if ( ! function_exists( 'woodmart_elementor_maybe_init_cart' ) ) {
	/**
	 * Ini woo cart in elementor.
	 */
	function woodmart_elementor_maybe_init_cart() {
		if ( ! woodmart_woocommerce_installed() ) {
			return;
		}

		WC()->initialize_session();
	}

	add_action( 'elementor/editor/before_enqueue_scripts', 'woodmart_elementor_maybe_init_cart' );
}

if ( ! function_exists( 'woodmart_elementor_enqueue_scripts' ) ) {
	/**
	 * Enqueue script for editor Elementor.
	 */
	function woodmart_elementor_enqueue_scripts() {
		wp_enqueue_script( 'wd-nested-elements', WOODMART_THEME_DIR . '/inc/integrations/elementor/assets/js/nestedElements.js', array(), woodmart_get_theme_info( 'Version' ), true );
		wp_enqueue_script( 'wd-reload-preview', WOODMART_THEME_DIR . '/inc/integrations/elementor/assets/js/reloadPreview.js', array(), woodmart_get_theme_info( 'Version' ), true );

		if ( Main::is_layout_type( 'single_product' ) ) {
			wp_enqueue_script( 'wd-single-gallery-fix', WOODMART_THEME_DIR . '/inc/integrations/elementor/assets/js/singleGalleryFix.js', array(), woodmart_get_theme_info( 'Version' ), true );
		}
	}

	add_action( 'elementor/preview/enqueue_scripts', 'woodmart_elementor_enqueue_scripts' );
	add_action( 'elementor/editor/before_enqueue_scripts', 'woodmart_elementor_enqueue_scripts' );
}

if ( ! function_exists( 'woodmart_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function woodmart_elementor_register_elementor_locations( $elementor_theme_manager ) {
		$elementor_theme_manager->register_location(
			'header',
			[
				'is_core'         => false,
				'public'          => false,
				'label'           => esc_html__( 'Header', 'woodmart' ),
				'edit_in_content' => false,
			]
		);

		$elementor_theme_manager->register_location(
			'footer',
			[
				'is_core'         => false,
				'public'          => false,
				'label'           => esc_html__( 'Footer', 'woodmart' ),
				'edit_in_content' => false,
			]
		);
	}

	add_action( 'elementor/theme/register_locations', 'woodmart_elementor_register_elementor_locations' );
}

if ( ! function_exists( 'woodmart_elementor_custom_shapes' ) ) {
	/**
	 * Custom shapes.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	function woodmart_elementor_custom_shapes() {
		return [
			'wd_clouds'       => [
				'title'    => '[XTemos] Clouds',
				'has_flip' => false,
				'path'     => WOODMART_THEMEROOT . '/images/svg/clouds-top.svg',
				'url'      => WOODMART_IMAGES . '/svg/clouds-top.svg',
			],
			'wd_curved_line'  => [
				'title'    => '[XTemos] Curved line',
				'has_flip' => true,
				'path'     => WOODMART_THEMEROOT . '/images/svg/curved-line-top.svg',
				'url'      => WOODMART_IMAGES . '/svg/curved-line-top.svg',
			],
			'wd_paint_stroke' => [
				'title'    => '[XTemos] Paint stroke',
				'has_flip' => true,
				'path'     => WOODMART_THEMEROOT . '/images/svg/paint-stroke-top.svg',
				'url'      => WOODMART_IMAGES . '/svg/paint-stroke-top.svg',
			],
			'wd_sweet_wave'   => [
				'title'    => '[XTemos] Sweet wave',
				'has_flip' => true,
				'path'     => WOODMART_THEMEROOT . '/images/svg/sweet-wave-top.svg',
				'url'      => WOODMART_IMAGES . '/svg/sweet-wave-top.svg',
			],
			'wd_triangle'     => [
				'title'    => '[XTemos] Triangle',
				'has_flip' => false,
				'path'     => WOODMART_THEMEROOT . '/images/svg/triangle-top.svg',
				'url'      => WOODMART_IMAGES . '/svg/triangle-top.svg',
			],
			'wd_waves_small'  => [
				'title'    => '[XTemos] Waves small',
				'has_flip' => false,
				'path'     => WOODMART_THEMEROOT . '/images/svg/waves-small-top.svg',
				'url'      => WOODMART_IMAGES . '/svg/waves-small-top.svg',
			],
			'wd_waves_wide'   => [
				'title'    => '[XTemos] Waves wide',
				'has_flip' => false,
				'path'     => WOODMART_THEMEROOT . '/images/svg/waves-wide-top.svg',
				'url'      => WOODMART_IMAGES . '/svg/waves-wide-top.svg',
			],
		];
	}

	add_filter( 'elementor/shapes/additional_shapes', 'woodmart_elementor_custom_shapes' );
}

if ( ! function_exists( 'woodmart_elementor_custom_animations' ) ) {
	/**
	 * Custom animations.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	function woodmart_elementor_custom_animations() {
		return [
			'XTemos' => [
				'wd-anim-slide-from-bottom'           => 'Slide From Bottom',
				'wd-anim-slide-from-top'              => 'Slide From Top',
				'wd-anim-slide-from-left'             => 'Slide From Left',
				'wd-anim-slide-from-right'            => 'Slide From Right',
				'wd-animation-slide-short-from-left'  => 'Slide Short From Left',
				'wd-animation-slide-short-from-right' => 'Slide Short From Right',
				'wd-anim-left-flip-y'                 => 'Left Flip Y',
				'wd-anim-right-flip-y'                => 'Right Flip Y',
				'wd-anim-top-flip-x'                  => 'Top Flip X',
				'wd-anim-bottom-flip-x'               => 'Bottom Flip X',
				'wd-anim-zoom-in'                     => 'Zoom In',
				'wd-anim-rotate-z'                    => 'Rotate Z',
			],
		];
	}

	add_filter( 'elementor/controls/animations/additional_animations', 'woodmart_elementor_custom_animations' );
}

if ( ! function_exists( 'woodmart_get_posts_by_query' ) ) {
	/**
	 * Get post by search
	 *
	 * @since 1.0.0
	 */
	function woodmart_get_posts_by_query() {
		check_ajax_referer( 'woodmart_autocomplete_control_nonce', 'security' );

		$search_string = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : ''; // phpcs:ignore
		$post_type     = isset( $_POST['post_type'] ) ? $_POST['post_type'] : 'post'; // phpcs:ignore
		$query_type    = isset( $_POST['query_type'] ) ? $_POST['query_type'] : ''; // phpcs:ignore
		$results       = array();

		switch ( $query_type ) {
			case 'post_type':
				$post_types = get_post_types( array( 'public' => true ), 'objects' );
				foreach ( $post_types as $post_type_obj ) {
					if ( $search_string && stripos( $post_type_obj->label, $search_string ) === false ) {
						continue;
					}
					$results[] = array(
						'id'   => $post_type_obj->name,
						'text' => $post_type_obj->label,
					);
				}
				break;

			case 'single_post_type':
				$post_types = get_post_types( array( 'public' => true ), 'objects' );
				foreach ( $post_types as $post_type_obj ) {
					if ( $search_string && stripos( $post_type_obj->label, $search_string ) === false ) {
						continue;
					}
					$results[] = array(
						'id'   => $post_type_obj->name,
						'text' => $post_type_obj->label,
					);
				}
				break;

			case 'post_id':
				$args = array(
					'post_type'      => get_post_types( array( 'public' => true ) ),
					'posts_per_page' => 100,
					's'              => $search_string,
				);

				$posts = get_posts( $args );

				if ( count( $posts ) > 0 ) {
					foreach ( $posts as $post ) {
						$results[] = array(
							'id'   => $post->ID,
							'text' => $post->post_title . ' (ID:' . $post->ID . ')',
						);
					}
				}
				break;

			case 'taxonomy':
				$taxonomies = get_taxonomies(
					array(
						'public' => true,
					),
					'objects'
				);

				foreach ( $taxonomies as $taxonomy ) {
					if ( $search_string && stripos( $taxonomy->label, $search_string ) === false ) {
						continue;
					}
					$results[] = array(
						'id'   => $taxonomy->name,
						'text' => $taxonomy->label,
					);
				}
				break;

			case 'term_id':
			case 'single_posts_term_id':
				$taxonomies = get_taxonomies();

				foreach ( $taxonomies as $taxonomy ) {
					$terms = get_terms(
						array(
							'taxonomy'   => $taxonomy,
							'hide_empty' => false,
							'search'     => $search_string,
						)
					);

					if ( ! empty( $terms ) ) {
						foreach ( $terms as $term ) {
							$results[] = array(
								'id'   => $term->term_id,
								'text' => $term->name . ' (ID: ' . $term->term_id . ')',
							);
						}
					}
				}
				break;

			default:
				$query = new WP_Query(
					array(
						's'              => $search_string,
						'post_type'      => $post_type,
						'post_status'    => 'publish',
						'posts_per_page' => - 1,
					)
				);

				foreach ( $query->posts as $post ) {
					$results[] = array(
						'id'   => $post->ID,
						'text' => $post->post_title,
					);
				}

				break;
		}

		wp_send_json( $results );
	}

	add_action( 'wp_ajax_woodmart_get_posts_by_query', 'woodmart_get_posts_by_query' );
}

if ( ! function_exists( 'woodmart_get_posts_title_by_id' ) ) {
	/**
	 * Get post title by ID
	 *
	 * @since 1.0.0
	 */
	function woodmart_get_posts_title_by_id() {
		$ids        = isset( $_POST['id'] ) ? $_POST['id'] : array(); // phpcs:ignore
		$post_type  = isset( $_POST['post_type'] ) ? $_POST['post_type'] : 'post'; // phpcs:ignore
		$query_type = isset( $_POST['query_type'] ) ? $_POST['query_type'] : ''; // phpcs:ignore
		$results    = array();

		switch ( $query_type ) {
			case 'post_type':
			case 'single_post_type':
				foreach ( $ids as $id ) {
					$post_type_object = get_post_type_object( $id );
					if ( $post_type_object ) {
						$results[ $id ] = $post_type_object->label;
					}
				}
				break;

			case 'post_id':
				$query = new WP_Query(
					array(
						'post_type'      => get_post_types( array( 'public' => true ) ),
						'post__in'       => $ids,
						'posts_per_page' => - 1,
						'orderby'        => 'post__in',
					)
				);

				if ( isset( $query->posts ) ) {
					foreach ( $query->posts as $post ) {
						$results[ $post->ID ] = $post->post_title . ' (ID:' . $post->ID . ')';
					}
				}
				break;

			case 'taxonomy':
				foreach ( $ids as $id ) {
					$taxonomy = get_taxonomy( $id );
					if ( $taxonomy ) {
						$results[ $id ] = $taxonomy->label;
					}
				}
				break;

			case 'term_id':
			case 'single_posts_term_id':
				foreach ( $ids as $id ) {
					$term = get_term( $id );
					if ( $term && ! is_wp_error( $term ) ) {
						$results[ $id ] = $term->name . ' (ID: ' . $term->term_id . ')';
					}
				}
				break;

			default:
				$query = new WP_Query(
					array(
						'post_type'      => $post_type,
						'post__in'       => $ids,
						'posts_per_page' => - 1,
						'orderby'        => 'post__in',
					)
				);

				if ( isset( $query->posts ) ) {
					foreach ( $query->posts as $post ) {
						$results[ $post->ID ] = $post->post_title;
					}
				}
				break;
		}

		wp_send_json( $results );
	}

	add_action( 'wp_ajax_woodmart_get_posts_title_by_id', 'woodmart_get_posts_title_by_id' );
	add_action( 'wp_ajax_nopriv_woodmart_get_posts_title_by_id', 'woodmart_get_posts_title_by_id' );
}

if ( ! function_exists( 'woodmart_get_taxonomies_title_by_id' ) ) {
	/**
	 * Get taxonomies title by id
	 *
	 * @since 1.0.0
	 */
	function woodmart_get_taxonomies_title_by_id() {
		$ids     = isset( $_POST['id'] ) ? $_POST['id'] : array(); // phpcs:ignore
		$results = array();

		$args = array(
			'include'    => $ids,
			'hide_empty' => false,
		);

		$terms = get_terms( $args );

		if ( is_array( $terms ) && $terms ) {
			foreach ( $terms as $term ) {
				if ( is_object( $term ) ) {
					$results[ $term->term_id ] = $term->name . ' (' . $term->taxonomy . ')';
				}
			}
		}

		wp_send_json( $results );
	}

	add_action( 'wp_ajax_woodmart_get_taxonomies_title_by_id', 'woodmart_get_taxonomies_title_by_id' );
	add_action( 'wp_ajax_nopriv_woodmart_get_taxonomies_title_by_id', 'woodmart_get_taxonomies_title_by_id' );
}

if ( ! function_exists( 'woodmart_get_taxonomies_by_query' ) ) {
	/**
	 * Get taxonomies by search
	 *
	 * @since 1.0.0
	 */
	function woodmart_get_taxonomies_by_query() {
		$search_string = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : ''; // phpcs:ignore
		$taxonomy      = isset( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : ''; // phpcs:ignore
		$results       = array();

		if ( is_array( $taxonomy ) ) {
			$taxonomy = array_filter(
				$taxonomy,
				function( $tax ) {
					return taxonomy_exists( $tax );
				}
			);
		}

		$args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'search'     => $search_string,
		);

		$terms = get_terms( $args );

		if ( is_array( $terms ) && $terms ) {
			foreach ( $terms as $term ) {
				if ( is_object( $term ) ) {
					$results[] = array(
						'id'   => $term->term_id,
						'text' => $term->name . ' (' . $term->taxonomy . ')',
					);
				}
			}
		}

		wp_send_json( $results );
	}

	add_action( 'wp_ajax_woodmart_get_taxonomies_by_query', 'woodmart_get_taxonomies_by_query' );
	add_action( 'wp_ajax_nopriv_woodmart_get_taxonomies_by_query', 'woodmart_get_taxonomies_by_query' );
}

if ( ! function_exists( 'woodmart_elementor_enqueue_editor_styles' ) ) {
	/**
	 * Enqueue elementor editor custom styles
	 *
	 * @since 1.0.0
	 */
	function woodmart_elementor_enqueue_editor_styles() {
		wp_enqueue_style( 'woodmart-elementor-editor-style', WOODMART_THEME_DIR . '/inc/integrations/elementor/assets/css/editor.css', array( 'elementor-editor' ), woodmart_get_theme_info( 'Version' ) );
	}

	add_action( 'elementor/editor/before_enqueue_styles', 'woodmart_elementor_enqueue_editor_styles' );
}

if ( ! function_exists( 'woodmart_add_custom_font_group' ) ) {
	/**
	 * Add custom font group to font control
	 *
	 * @since 1.0.0
	 *
	 * @param array $font_groups Default font groups.
	 *
	 * @return array
	 */
	function woodmart_add_custom_font_group( $font_groups ) {
		return array( 'wd_fonts' => esc_html__( 'Theme fonts', 'woodmart' ) ) + $font_groups;
	}

	add_filter( 'elementor/fonts/groups', 'woodmart_add_custom_font_group' );
}

if ( ! function_exists( 'woodmart_add_custom_fonts_to_theme_group' ) ) {
	/**
	 * Add custom fonts to theme group
	 *
	 * @since 1.0.0
	 *
	 * @param array $additional_fonts Additional fonts.
	 *
	 * @return array
	 */
	function woodmart_add_custom_fonts_to_theme_group( $additional_fonts ) {
		$theme_fonts       = array();
		$content_font      = woodmart_get_opt( 'primary-font' );
		$title_font        = woodmart_get_opt( 'text-font' );
		$alt_font          = woodmart_get_opt( 'secondary-font' );
		$custom_fonts_data = woodmart_get_opt( 'multi_custom_fonts' );
		$typekit_fonts     = woodmart_get_opt( 'typekit_fonts' );

		if ( isset( $content_font[0] ) && isset( $content_font[0]['font-family'] ) && $content_font[0]['font-family'] ) {
			$theme_fonts[ $content_font[0]['font-family'] ] = 'wd_fonts';
		}

		if ( isset( $title_font[0] ) && isset( $title_font[0]['font-family'] ) && $title_font[0]['font-family'] ) {
			$theme_fonts[ $title_font[0]['font-family'] ] = 'wd_fonts';
		}

		if ( isset( $alt_font[0] ) && isset( $alt_font[0]['font-family'] ) && $alt_font[0]['font-family'] ) {
			$theme_fonts[ $alt_font[0]['font-family'] ] = 'wd_fonts';
		}

		if ( isset( $custom_fonts_data['{{index}}'] ) ) {
			unset( $custom_fonts_data['{{index}}'] );
		}

		if ( is_array( $custom_fonts_data ) ) {
			foreach ( $custom_fonts_data as $font ) {
				if ( ! $font['font-name'] ) {
					continue;
				}

				$theme_fonts[ $font['font-name'] ] = 'wd_fonts';
			}
		}

		if ( $typekit_fonts ) {
			$typekit = explode( ',', $typekit_fonts );
			foreach ( $typekit as $font ) {
				$theme_fonts[ ucfirst( trim( $font ) ) ] = 'wd_fonts';
			}
		}

		return $theme_fonts + $additional_fonts;
	}

	add_filter( 'elementor/fonts/additional_fonts', 'woodmart_add_custom_fonts_to_theme_group' );
}

if ( ! function_exists( 'woodmart_add_custom_post_types_for_elementor' ) ) {
	/**
	 * Add custom post types for elementor
	 *
	 * @param array $post_types Post types.
	 * @return array
	 */
	function woodmart_add_custom_post_types_for_elementor( $post_types ) {
		$post_types_keys = array( 'woodmart_size_guide', 'wd_popup', 'wd_floating_block', 'wd_product_tabs' );
		foreach ( $post_types_keys as $key ) {
			$cpt = get_post_type_object( $key );

			if ( $cpt ) {
				$post_types[ $key ] = $cpt;
			}
		}

		return $post_types;
	}

	add_filter( 'elementor/settings/controls/checkbox_list_cpt/post_type_objects', 'woodmart_add_custom_post_types_for_elementor' );
}
