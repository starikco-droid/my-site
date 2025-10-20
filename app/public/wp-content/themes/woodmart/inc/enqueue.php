<?php
/**
 * Enqueue functions.
 *
 * @package woodmart
 */

use Automattic\WooCommerce\Admin\WCAdminHelper;
use Elementor\Plugin;
use XTS\Modules\Checkout_Order_Table;
use XTS\Modules\Layouts\Main;
use XTS\Modules\Parts_Css_Files;
use XTS\Modules\Styles_Storage;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

if ( ! function_exists( 'woodmart_is_combined_needed' ) ) {
	/**
	 * Is combined needed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Combined key.
	 * @param mixed  $default Default value.
	 *
	 * @return bool
	 */
	function woodmart_is_combined_needed( $key, $default = false ) {
		return apply_filters( 'woodmart_enqueue_' . $key, $default ) || ( woodmart_is_elementor_installed() && ( woodmart_elementor_is_edit_mode() || woodmart_elementor_is_preview_mode() ) ) || is_singular( 'woodmart_layout' );
	}
}

if ( ! function_exists( 'woodmart_is_minified_needed' ) ) {
	/**
	 * Is minified JS files needed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	function woodmart_is_minified_needed() {
		return apply_filters( 'woodmart_enqueue_minified_js_files', ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG );
	}
}

if ( ! function_exists( 'woodmart_register_libraries_scripts' ) ) {
	/**
	 * Register libraries scripts.
	 *
	 * @since 1.0.0
	 */
	function woodmart_register_libraries_scripts() {
		$config   = woodmart_get_config( 'js-libraries' );
		$minified = woodmart_is_minified_needed() ? '.min' : '';
		$version  = woodmart_get_theme_info( 'Version' );

		if ( woodmart_is_combined_needed( 'combined_js_libraries' ) ) {
			return;
		}

		foreach ( $config as $key => $libraries ) {
			foreach ( $libraries as $library ) {
				$src = WOODMART_THEME_DIR . $library['file'] . $minified . '.js';

				wp_register_script( 'wd-' . $key . '-library', $src, $library['dependency'], $version, $library['in_footer'] );
			}
		}
	}

	add_action( 'wp_enqueue_scripts', 'woodmart_register_libraries_scripts', 10 );
}

if ( ! function_exists( 'woodmart_register_scripts' ) ) {
	/**
	 * Register scripts.
	 *
	 * @since 1.0.0
	 */
	function woodmart_register_scripts() {
		$config   = woodmart_get_config( 'js-scripts' );
		$minified = woodmart_is_minified_needed() ? '.min' : '';
		$version  = woodmart_get_theme_info( 'Version' );

		if ( woodmart_is_combined_needed( 'combined_js' ) ) {
			return;
		}

		foreach ( $config as $scripts ) {
			foreach ( $scripts as $script ) {
				$src = WOODMART_THEME_DIR . $script['file'] . $minified . '.js';
				$deps = array();

				if ( 'woodmart-theme' !== $script['name'] ) {
					if ( 'scrollbar' !== $script['name'] ) {
						$deps = array( 'woodmart-theme' );
					}

					$name = 'wd-' . $script['name'];
				} else {
					$name = $script['name'];
					$deps = array( 'jquery' );
				}

				wp_register_script( $name, $src, $deps, $version, $script['in_footer'] );
			}
		}
	}

	add_action( 'wp_enqueue_scripts', 'woodmart_register_scripts', 20 );
}

if ( ! function_exists( 'woodmart_enqueue_base_scripts' ) ) {
	/**
	 * Enqueue base scripts.
	 *
	 * @since 1.0.0
	 */
	function woodmart_enqueue_base_scripts() {
		$minified = woodmart_is_minified_needed() ? '.min' : '';
		$version  = woodmart_get_theme_info( 'Version' );

		// General.
		wp_enqueue_script( 'wpb_composer_front_js', false, array(), $version ); // phpcs:ignore
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
		if ( woodmart_is_elementor_installed() && apply_filters( 'woodmart_enqueue_elementor_scripts', true ) ) {
			Elementor\Plugin::$instance->frontend->enqueue_scripts();
		}

		// Libraries.
		if ( woodmart_is_combined_needed( 'combined_js_libraries' ) ) {
			wp_enqueue_script( 'wd-libraries', WOODMART_THEME_DIR . '/js/libs/combine' . $minified . '.js', array( 'jquery' ), $version, true );
		} else {
			woodmart_enqueue_js_library( 'device' );

			if ( woodmart_get_opt( 'ajax_shop' ) && woodmart_is_shop_archive() && ( function_exists( 'woodmart_elementor_has_location' ) && ! woodmart_elementor_has_location( 'archive' ) || ! function_exists( 'woodmart_elementor_has_location' ) ) ) {
				woodmart_enqueue_js_library( 'pjax' );
			}

			if ( ! woodmart_woocommerce_installed() ) {
				woodmart_enqueue_js_library( 'cookie' );
			}

			$config = woodmart_get_config( 'js-libraries' );
			foreach ( $config as $key => $libraries ) {
				foreach ( $libraries as $library ) {
					if ( 'always' === woodmart_get_opt( $library['name'] . '_library' ) ) {
						woodmart_enqueue_js_library( $key );
					}
				}
			}
		}

		// Scripts.
		if ( woodmart_is_combined_needed( 'combined_js' ) ) {
			wp_enqueue_script( 'imagesloaded' );
			wp_enqueue_script( 'woodmart-theme', WOODMART_THEME_DIR . '/js/scripts/combine' . $minified . '.js', array(), $version, true );
		} else {
			woodmart_enqueue_js_script( 'woodmart-theme' );
			woodmart_enqueue_js_script( 'woocommerce-notices' );
			woodmart_enqueue_js_script( 'scrollbar' );

			if ( is_admin_bar_showing() ) {
				woodmart_enqueue_js_script( 'admin-bar-slider-menu' );
			}

			if ( woodmart_woocommerce_installed() ) {
				if ( is_cart() || is_checkout() || is_account_page() ) {
					woodmart_enqueue_js_script( 'woocommerce-wrapp-table' );
				}

				if ( is_cart() || is_checkout() ) {
					wp_enqueue_script( 'wc-cart-fragments' );
				}

				if ( woodmart_get_opt( 'update_cart_quantity_change' ) && is_cart() && ! WC()->cart->is_empty() ) {
					woodmart_enqueue_js_script( 'cart-quantity' );
				}

				if ( is_singular( 'product' ) && apply_filters( 'woodmart_wc_track_recently_product_viewed', true ) ) {
					woodmart_enqueue_js_script( 'track-product-recently-viewed' );
				}
			}

			if ( woodmart_get_opt( 'widget_toggle' ) ) {
				woodmart_enqueue_js_script( 'widgets-hidable' );
			}

			if ( woodmart_get_opt( 'ajax_shop' ) && woodmart_is_shop_archive() && 'subcategories' !== woocommerce_get_loop_display_mode() ) {
				woodmart_enqueue_js_script( 'ajax-filters' );
				woodmart_enqueue_js_script( 'shop-page-init' );
				woodmart_enqueue_js_script( 'back-history' );
			}

			if ( 'disable' !== woodmart_get_opt( 'shop_widgets_collapse', 'disable' ) && woodmart_is_shop_archive() ) {
				woodmart_enqueue_js_script( 'widget-collapse' );
			}

			$scripts_always = woodmart_get_opt( 'scripts_always_use' );
			if ( is_array( $scripts_always ) ) {
				foreach ( $scripts_always as $script ) {
					woodmart_enqueue_js_script( $script );
				}
			}
		}

		if ( woodmart_is_elementor_installed() && ( woodmart_elementor_is_edit_mode() || woodmart_elementor_is_preview_mode() ) ) {
			wp_enqueue_script( 'wd-google-map-api', 'https://maps.google.com/maps/api/js?libraries=geometry&callback=woodmartThemeModule.googleMapsCallback&v=weekly&key=' . woodmart_get_opt( 'google_map_api_key' ), array( 'woodmart-theme' ), $version, true );
			wp_enqueue_script( 'wd-maplace', WOODMART_THEME_DIR . '/js/libs/maplace' . $minified . '.js', array( 'wd-google-map-api' ), $version, true );
		}

		wp_add_inline_script( 'woodmart-theme', woodmart_settings_js() );
		wp_localize_script( 'woodmart-theme', 'woodmart_settings', woodmart_get_localized_string_array() );

		wp_register_style( 'woodmart-inline-css', '' );
	}

	add_action( 'wp_enqueue_scripts', 'woodmart_enqueue_base_scripts', 30 );
}

if ( ! function_exists( 'woodmart_enqueue_js_script' ) ) {
	/**
	 * Enqueue js script.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key        Script name.
	 * @param string $responsive Responsive key.
	 */
	function woodmart_enqueue_js_script( $key, $responsive = '' ) {
		$config          = woodmart_get_config( 'js-scripts' );
		$scripts_not_use = woodmart_get_opt( 'scripts_not_use' );

		if ( ! isset( $config[ $key ] ) || woodmart_is_combined_needed( 'combined_js' ) ) {
			return;
		}

		foreach ( $config[ $key ] as $data ) {
			if ( ( 'only_mobile' === $responsive && ! wp_is_mobile() ) || ( 'only_desktop' === $responsive && wp_is_mobile() ) || ( is_array( $scripts_not_use ) && in_array( $data['name'], $scripts_not_use ) ) ) { // phpcs:ignore
				continue;
			}

			$name = 'woodmart-theme' !== $data['name'] ? 'wd-' . $data['name'] : $data['name'];
			wp_enqueue_script( $name );
		}
	}
}

if ( ! function_exists( 'woodmart_enqueue_js_library' ) ) {
	/**
	 * Enqueue js library.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key        Script name.
	 * @param string $responsive Responsive key.
	 */
	function woodmart_enqueue_js_library( $key, $responsive = '' ) {
		$config = woodmart_get_config( 'js-libraries' );

		if ( ! isset( $config[ $key ] ) || woodmart_is_combined_needed( 'combined_js_libraries' ) ) {
			return;
		}

		foreach ( $config[ $key ] as $data ) {
			if ( ( 'only_mobile' === $responsive && ! wp_is_mobile() ) || ( 'only_desktop' === $responsive && wp_is_mobile() ) || 'not_use' === woodmart_get_opt( $data['name'] . '_library' ) ) {
				continue;
			}

			wp_enqueue_script( 'wd-' . $key . '-library' );
		}
	}
}

if ( ! function_exists( 'woodmart_dequeue_scripts' ) ) {
	/**
	 * Dequeue scripts.
	 *
	 * @since 1.0.0
	 */
	function woodmart_dequeue_scripts() {
		$dequeue_scripts = explode( ',', woodmart_get_opt( 'dequeue_scripts' ) );

		if ( is_array( $dequeue_scripts ) ) {
			foreach ( $dequeue_scripts as $script ) {
				wp_deregister_script( trim( $script ) );
				wp_dequeue_script( trim( $script ) );
			}
		}

		wp_dequeue_script( 'flexslider' );
		wp_dequeue_script( 'photoswipe-ui-default' );
		wp_dequeue_script( 'prettyPhoto-init' );
		wp_dequeue_script( 'prettyPhoto' );
		wp_dequeue_style( 'photoswipe-default-skin' );

		// Remove CF7.
		if ( ! woodmart_get_opt( 'cf7_js', '1' ) ) {
			wp_deregister_script( 'contact-form-7' );
			wp_dequeue_script( 'contact-form-7' );
		}

		// Zoom.
		if ( 'zoom' !== woodmart_get_opt( 'image_action' ) ) {
			wp_deregister_script( 'zoom' );
			wp_dequeue_script( 'zoom' );
		}

		// Gutenberg.
		if ( woodmart_get_opt( 'disable_gutenberg_css' ) ) {
			wp_deregister_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library' );

			wp_deregister_style( 'wc-block-style' );
			wp_dequeue_style( 'wc-block-style' );

			wp_deregister_style( 'wc-blocks-style' );
			wp_dequeue_style( 'wc-blocks-style' );

			wp_deregister_style( 'wc-blocks-packages-style' );
			wp_dequeue_style( 'wc-blocks-packages-style' );

			wp_dequeue_style( 'classic-theme-styles' );

			if ( woodmart_woocommerce_installed() && ! empty( wp_styles()->registered ) ) {
				foreach ( wp_styles()->registered as $key => $data ) {
					if ( false !== strpos( $key, 'wc-blocks-style-' ) ) {
						wp_deregister_style( $key );
						wp_dequeue_script( $key );
					}
				}
			}
		}
	}

	add_action( 'wp_enqueue_scripts', 'woodmart_dequeue_scripts', 2000 );
}

if ( ! function_exists( 'woodmart_clear_menu_transient' ) ) {
	/**
	 * Clear menu session storage key hash on save menu/html block.
	 *
	 * @since 1.0.0
	 */
	function woodmart_clear_menu_transient() {
		delete_transient( 'woodmart-menu-hash-time' );
	}

	add_action( 'wp_update_nav_menu_item', 'woodmart_clear_menu_transient', 11, 1 );
	add_action( 'save_post_cms_block', 'woodmart_clear_menu_transient', 30, 3 );
}

if ( ! function_exists( 'woodmart_get_localized_string_array' ) ) {
	/**
	 * Get localize array
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	function woodmart_get_localized_string_array() {
		$version             = woodmart_get_theme_info( 'Version' );
		$menu_hash_transient = get_transient( 'woodmart-menu-hash-time' );
		if ( false === $menu_hash_transient ) {
			$menu_hash_transient = time();
			set_transient( 'woodmart-menu-hash-time', $menu_hash_transient );
		}

		$site_custom_width     = woodmart_get_opt( 'site_custom_width' );
		$predefined_site_width = woodmart_get_opt( 'site_width' );

		$site_width = '';

		if ( 'full-width' === $predefined_site_width ) {
			$site_width = 1222;
		} elseif ( 'boxed' === $predefined_site_width ) {
			$site_width = 1160;
		} elseif ( 'boxed-2' === $predefined_site_width ) {
			$site_width = 1160;
		} elseif ( 'wide' === $predefined_site_width ) {
			$site_width = 1600;
		} elseif ( 'custom' === $predefined_site_width ) {
			$site_width = $site_custom_width;
		}

		return apply_filters(
			'woodmart_localized_string_array',
			array(
				'menu_storage_key'                       => apply_filters( 'woodmart_menu_storage_key', 'woodmart_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() . $menu_hash_transient . $version ) ),
				'ajax_dropdowns_save'                    => apply_filters( 'xts_ajax_dropdowns_save', true ),
				'photoswipe_close_on_scroll'             => apply_filters( 'woodmart_photoswipe_close_on_scroll', true ),
				'woocommerce_ajax_add_to_cart'           => get_option( 'woocommerce_enable_ajax_add_to_cart' ),
				'variation_gallery_storage_method'       => woodmart_get_opt( 'variation_gallery_storage_method', 'new' ),
				'elementor_no_gap'                       => woodmart_get_opt( 'negative_gap', 'enabled' ),
				'adding_to_cart'                         => esc_html__( 'Processing', 'woodmart' ),
				'added_to_cart'                          => esc_html__( 'Product was successfully added to your cart.', 'woodmart' ),
				'continue_shopping'                      => esc_html__( 'Continue shopping', 'woodmart' ),
				'view_cart'                              => esc_html__( 'View Cart', 'woodmart' ),
				'go_to_checkout'                         => esc_html__( 'Checkout', 'woodmart' ),
				'loading'                                => esc_html__( 'Loading...', 'woodmart' ),
				'countdown_days'                         => esc_html__( 'days', 'woodmart' ),
				'countdown_hours'                        => esc_html__( 'hr', 'woodmart' ),
				'countdown_mins'                         => esc_html__( 'min', 'woodmart' ),
				'countdown_sec'                          => esc_html__( 'sc', 'woodmart' ),
				'cart_url'                               => ( woodmart_woocommerce_installed() ) ? esc_url( wc_get_cart_url() ) : '',
				'ajaxurl'                                => admin_url( 'admin-ajax.php' ),
				'add_to_cart_action'                     => ( woodmart_get_opt( 'add_to_cart_action' ) ) ? esc_js( woodmart_get_opt( 'add_to_cart_action' ) ) : 'widget',
				'added_popup'                            => ( woodmart_get_opt( 'added_to_cart_popup' ) ) ? 'yes' : 'no',
				'categories_toggle'                      => ( woodmart_get_opt( 'categories_toggle' ) ) ? 'yes' : 'no',
				'product_images_captions'                => ( woodmart_get_opt( 'product_images_captions' ) ) ? 'yes' : 'no',
				'ajax_add_to_cart'                       => ( apply_filters( 'woodmart_ajax_add_to_cart', true ) ) ? woodmart_get_opt( 'single_ajax_add_to_cart' ) : false,
				'all_results'                            => esc_html__( 'View all results', 'woodmart' ),
				'zoom_enable'                            => ( woodmart_get_opt( 'image_action' ) === 'zoom' ) ? 'yes' : 'no',
				'ajax_scroll'                            => ( woodmart_get_opt( 'ajax_scroll' ) ) ? 'yes' : 'no',
				'ajax_scroll_class'                      => apply_filters( 'woodmart_ajax_scroll_class', '.wd-page-content' ),
				'ajax_scroll_offset'                     => apply_filters( 'woodmart_ajax_scroll_offset', 100 ),
				'infinit_scroll_offset'                  => apply_filters( 'woodmart_infinit_scroll_offset', 300 ),
				'product_slider_auto_height'             => ( woodmart_get_opt( 'product_slider_auto_height' ) ) ? 'yes' : 'no',
				'price_filter_action'                    => ( apply_filters( 'price_filter_action', 'click' ) === 'submit' ) ? 'submit' : 'click',
				'product_slider_autoplay'                => apply_filters( 'woodmart_product_slider_autoplay', false ),
				'close'                                  => esc_html__( 'Close', 'woodmart' ),
				'close_markup'                           => '<div class="wd-popup-close wd-action-btn wd-cross-icon wd-style-icon"><a title="' . esc_html__( 'Close', 'woodmart' ) . '" href="#" rel="nofollow"><span>' . esc_html__( 'Close', 'woodmart' ) . '</span></a></div>',
				'share_fb'                               => esc_html__( 'Share on Facebook', 'woodmart' ),
				'pin_it'                                 => esc_html__( 'Pin it', 'woodmart' ),
				'tweet'                                  => esc_html__( 'Share on X', 'woodmart' ),
				'download_image'                         => esc_html__( 'Download image', 'woodmart' ),
				'off_canvas_column_close_btn_text'       => esc_html__( 'Close', 'woodmart' ),
				'cookies_version'                        => ( woodmart_get_opt( 'cookies_version' ) ) ? (int) woodmart_get_opt( 'cookies_version' ) : 1,
				'header_banner_version'                  => ( woodmart_get_opt( 'header_banner_version' ) ) ? (int) woodmart_get_opt( 'header_banner_version' ) : 1,
				'promo_version'                          => ( woodmart_get_opt( 'promo_version' ) ) ? (int) woodmart_get_opt( 'promo_version' ) : 1,
				'header_banner_close_btn'                => woodmart_get_opt( 'header_close_btn' ) ? 'yes' : 'no',
				'header_banner_enabled'                  => woodmart_get_opt( 'header_banner' ) ? 'yes' : 'no',
				'whb_header_clone'                       => woodmart_get_config( 'header-clone-structure' ),
				'pjax_timeout'                           => apply_filters( 'woodmart_pjax_timeout', 5000 ),
				'split_nav_fix'                          => apply_filters( 'woodmart_split_nav_fix', false ),
				'shop_filters_close'                     => woodmart_get_opt( 'shop_filters_close' ) ? 'yes' : 'no',
				'woo_installed'                          => woodmart_woocommerce_installed(),
				'base_hover_mobile_click'                => woodmart_get_opt( 'base_hover_mobile_click' ) ? 'yes' : 'no',
				'centered_gallery_start'                 => apply_filters( 'woodmart_centered_gallery_start', 1 ),
				'quickview_in_popup_fix'                 => apply_filters( 'woodmart_quickview_in_popup_fix', false ),
				'one_page_menu_offset'                   => apply_filters( 'woodmart_one_page_menu_offset', 150 ),
				'hover_width_small'                      => apply_filters( 'woodmart_hover_width_small', true ),
				'is_multisite'                           => is_multisite(),
				'current_blog_id'                        => get_current_blog_id(),
				'swatches_scroll_top_desktop'            => woodmart_get_opt( 'swatches_scroll_top_desktop' ) ? 'yes' : 'no',
				'swatches_scroll_top_mobile'             => woodmart_get_opt( 'swatches_scroll_top_mobile' ) ? 'yes' : 'no',
				'lazy_loading_offset'                    => woodmart_get_opt( 'lazy_loading_offset' ),
				'add_to_cart_action_timeout'             => woodmart_get_opt( 'add_to_cart_action_timeout' ) ? 'yes' : 'no',
				'add_to_cart_action_timeout_number'      => woodmart_get_opt( 'add_to_cart_action_timeout_number' ),
				'single_product_variations_price'        => woodmart_get_opt( 'single_product_variations_price' ) ? 'yes' : 'no',
				'google_map_style_text'                  => esc_html__( 'Custom style', 'woodmart' ),
				'quick_shop'                             => woodmart_get_opt( 'quick_shop_variable' ) ? 'yes' : 'no',
				'sticky_product_details_offset'          => apply_filters( 'woodmart_sticky_product_details_offset', 150 ),
				'sticky_add_to_cart_offset'              => apply_filters( 'woodmart_sticky_add_to_cart_offset', 250 ),
				'sticky_product_details_different'       => apply_filters( 'woodmart_sticky_product_details_different', 100 ),
				'preloader_delay'                        => apply_filters( 'woodmart_preloader_delay', 300 ),
				'comment_images_upload_size_text'        => sprintf( esc_html__( 'Some files are too large. Allowed file size is %s.', 'woodmart' ), size_format( (int) woodmart_get_opt( 'single_product_comment_images_upload_size' ) * MB_IN_BYTES ) ), // phpcs:ignore
				'comment_images_count_text'              => sprintf( esc_html__( 'You can upload up to %s images to your review.', 'woodmart' ), woodmart_get_opt( 'single_product_comment_images_count' ) ), // phpcs:ignore
				'single_product_comment_images_required' => woodmart_get_opt( 'single_product_comment_images_required' ) ? 'yes' : 'no', // phpcs:ignore
				'comment_required_images_error_text'     => esc_html__( 'Image is required.', 'woodmart' ), // phpcs:ignore
				'comment_images_upload_mimes_text'       => sprintf( esc_html__( 'You are allowed to upload images only in %s formats.', 'woodmart' ), apply_filters( 'xts_comment_images_upload_mimes', 'png, jpeg' ) ), // phpcs:ignore
				'comment_images_added_count_text'        => esc_html__( 'Added %s image(s)', 'woodmart' ), // phpcs:ignore
				'comment_images_upload_size'             => (int) woodmart_get_opt( 'single_product_comment_images_upload_size' ) * MB_IN_BYTES,
				'comment_images_count'                   => woodmart_get_opt( 'single_product_comment_images_count' ),
				'search_input_padding'                   => apply_filters( 'wd_search_input_padding', false ) ? 'yes' : 'no',
				'comment_images_upload_mimes'            => apply_filters(
					'woodmart_comment_images_upload_mimes',
					array(
						'jpg|jpeg|jpe' => 'image/jpeg',
						'png'          => 'image/png',
					)
				),
				'home_url'                               => home_url( '/' ),
				'shop_url'                               => woodmart_woocommerce_installed() ? esc_url( wc_get_page_permalink( 'shop' ) ) : '',
				'age_verify'                             => ( woodmart_get_opt( 'age_verify' ) ) ? 'yes' : 'no',
				'banner_version_cookie_expires'          => apply_filters( 'woodmart_banner_version_cookie_expires', 60 ),
				'promo_version_cookie_expires'           => apply_filters( 'woodmart_promo_version_cookie_expires', 7 ),
				'age_verify_expires'                     => apply_filters( 'woodmart_age_verify_expires', 30 ),
				'countdown_timezone'                     => apply_filters( 'woodmart_wp_timezone_element', false ) ? get_option( 'timezone_string' ) : 'GMT',
				'cart_redirect_after_add'                => get_option( 'woocommerce_cart_redirect_after_add' ),
				'swatches_labels_name'                   => woodmart_get_opt( 'swatches_labels_name' ) ? 'yes' : 'no',
				'product_categories_placeholder'         => esc_html__( 'Select a category', 'woocommerce' ),
				'product_categories_no_results'          => esc_html__( 'No matches found', 'woocommerce' ),
				'cart_hash_key'                          => apply_filters( 'woocommerce_cart_hash_key', 'wc_cart_hash_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) ),
				'fragment_name'                          => apply_filters( 'woocommerce_cart_fragment_name', 'wc_fragments_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) ),
				'photoswipe_template'                    => '<div class="pswp" aria-hidden="true" role="dialog" tabindex="-1"><div class="pswp__bg"></div><div class="pswp__scroll-wrap"><div class="pswp__container"><div class="pswp__item"></div><div class="pswp__item"></div><div class="pswp__item"></div></div><div class="pswp__ui pswp__ui--hidden"><div class="pswp__top-bar"><div class="pswp__counter"></div><button class="pswp__button pswp__button--close" title="' . esc_html__( 'Close (Esc)', 'woocommerce' ) . '"></button> <button class="pswp__button pswp__button--share" title="' . esc_html__( 'Share', 'woocommerce' ) . '"></button> <button class="pswp__button pswp__button--fs" title="' . esc_html__( 'Toggle fullscreen', 'woocommerce' ) . '"></button> <button class="pswp__button pswp__button--zoom" title="' . esc_html__( 'Zoom in/out', 'woocommerce' ) . '"></button><div class="pswp__preloader"><div class="pswp__preloader__icn"><div class="pswp__preloader__cut"><div class="pswp__preloader__donut"></div></div></div></div></div><div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap"><div class="pswp__share-tooltip"></div></div><button class="pswp__button pswp__button--arrow--left" title="' . esc_html__( 'Previous (arrow left)', 'woocommerce' ) . '"></button> <button class="pswp__button pswp__button--arrow--right" title="' . esc_html__( 'Next (arrow right)', 'woocommerce' ) . '>"></button><div class="pswp__caption"><div class="pswp__caption__center"></div></div></div></div></div>',
				'load_more_button_page_url'              => apply_filters( 'woodmart_load_more_button_page_url', true ) ? 'yes' : 'no',
				'load_more_button_page_url_opt'          => woodmart_get_opt( 'load_more_button_page_url', true ) ? 'yes' : 'no',
				'menu_item_hover_to_click_on_responsive' => apply_filters( 'woodmart_menu_item_hover_to_click_on_responsive', false ) ? 'yes' : 'no',
				'clear_menu_offsets_on_resize'           => apply_filters( 'woodmart_clear_menu_offsets_on_resize', true ) ? 'yes' : 'no',
				'three_sixty_framerate'                  => apply_filters( 'woodmart_three_sixty_framerate', 60 ),
				'three_sixty_prev_next_frames'           => apply_filters( 'woodmart_three_sixty_prev_next_frames', 5 ),
				'ajax_search_delay'                      => apply_filters( 'woodmart_ajax_search_delay', 300 ),
				'animated_counter_speed'                 => apply_filters( 'woodmart_animated_counter_speed', 3000 ),
				'site_width'                             => $site_width,
				'cookie_secure_param'                    => woodmart_cookie_secure_param(),
				'cookie_path'                            => COOKIEPATH,
				'theme_dir'                              => WOODMART_THEME_DIR,
				'slider_distortion_effect'               => 'sliderWithNoise',
				'current_page_builder'                   => woodmart_get_current_page_builder(),
				'collapse_footer_widgets'                => woodmart_get_opt( 'collapse_footer_widgets' ) ? 'yes' : 'no',
				'carousel_breakpoints'                   => woodmart_get_carousel_breakpoints(),
				'grid_gallery_control'                   => woodmart_get_opt( 'grid_gallery_control', 'hover' ),
				'grid_gallery_enable_arrows'             => woodmart_get_opt( 'grid_gallery_enable_arrows', 'none' ),
				'ajax_shop'                              => woodmart_get_opt( 'ajax_shop' ),
				'add_to_cart_text'                       => esc_html__( 'Add to cart', 'woodmart' ),
				// translators: %s The name of the previous menu.
				'mobile_navigation_drilldown_back_to'    => esc_html__( 'Back to %s', 'woodmart' ),
				'mobile_navigation_drilldown_back_to_main_menu' => esc_html__( 'Back to menu', 'woodmart' ),
				'mobile_navigation_drilldown_back_to_categories' => esc_html__( 'Back to categories', 'woodmart' ),
				'search_history_title'                   => esc_html__( 'Search history', 'woodmart' ),
				'search_history_clear_all'               => esc_html__( 'Clear', 'woodmart' ),
				'search_history_items_limit'             => apply_filters( 'woodmart_search_history_items_limit', 5 ),
				'swiper_prev_slide_msg'                  => esc_html__( 'Previous slide', 'woodmart' ),
				'swiper_next_slide_msg'                  => esc_html__( 'Next slide', 'woodmart' ),
				'swiper_first_slide_msg'                 => esc_html__( 'This is the first slide', 'woodmart' ),
				'swiper_last_slide_msg'                  => esc_html__( 'This is the last slide', 'woodmart' ),
				'swiper_pagination_bullet_msg'           => esc_html_x( 'Go to slide {{index}}', 'Message for screen readers for single pagination bullet', 'woodmart' ),
				'swiper_slide_label_msg'                 => esc_html_x( '{{index}} / {{slidesLength}}', 'Message for screen readers describing the label of slide element', 'woodmart' ),
				'tooltip_left_selector'                  => '.wd-buttons[class*="wd-pos-r"] .wd-action-btn, .wd-portfolio-btns .portfolio-enlarge',
				'tooltip_top_selector'                   => '.wd-tooltip, .wd-buttons:not([class*="wd-pos-r"]) > .wd-action-btn, body:not(.catalog-mode-on):not(.login-see-prices) .wd-hover-base .wd-bottom-actions .wd-action-btn.wd-style-icon, .wd-hover-base .wd-compare-btn, body:not(.logged-in) .wd-review-likes a',
				'ajax_links'                             => apply_filters( 'woodmart_ajax_links', '.wd-nav-product-cat a, .wd-page-wrapper .widget_product_categories a, .widget_layered_nav_filters a, .woocommerce-widget-layered-nav a, .filters-area:not(.custom-content) a, body.post-type-archive-product:not(.woocommerce-account) .woocommerce-pagination a, body.tax-product_cat:not(.woocommerce-account) .woocommerce-pagination a, .wd-shop-tools a:not([rel="v:url"]), .woodmart-woocommerce-layered-nav a, .woodmart-price-filter a, .wd-clear-filters a, .woodmart-woocommerce-sort-by a, .woocommerce-widget-layered-nav-list a, .wd-widget-stock-status a, .widget_nav_mega_menu a, .wd-products-shop-view a, .wd-products-per-page a, .wd-cat a, body[class*="tax-pa_"] .woocommerce-pagination a, .wd-product-category-filter a, .widget_brand_nav a' ),
			)
		);
	}
}

// CSS.
if ( ! function_exists( 'woodmart_enqueue_base_styles' ) ) {
	function woodmart_enqueue_base_styles() {
		$uploads = wp_upload_dir();
		$version = woodmart_get_theme_info( 'Version' );
		$is_rtl  = is_rtl() ? '-rtl' : '';

		if ( woodmart_is_elementor_installed() ) {
			Elementor\Plugin::$instance->frontend->enqueue_styles();
		}

		wp_deregister_style( 'font-awesome' );
		wp_dequeue_style( 'font-awesome' );

		wp_dequeue_style( 'vc_pageable_owl-carousel-css' );
		wp_dequeue_style( 'vc_pageable_owl-carousel-css-theme' );

		if ( ! defined( 'YITH_WCWL' ) ) {
			wp_deregister_style( 'woocommerce_prettyPhoto_css' );
			wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
		}

		if ( defined( 'WC_STRIPE_VERSION' ) ) {
			wp_deregister_style( 'stripe_styles' );
			wp_dequeue_style( 'stripe_styles' );
		}

		wp_deregister_style( 'contact-form-7' );
		wp_dequeue_style( 'contact-form-7' );
		wp_deregister_style( 'contact-form-7-rtl' );
		wp_dequeue_style( 'contact-form-7-rtl' );

		wp_dequeue_style( 'brands-styles' );

		$wpbfile = get_option( 'woodmart-generated-wpbcss-file' );
		if ( isset( $wpbfile['name'] ) && 'wpb' === woodmart_get_opt( 'builder', 'wpb' ) ) {
			$wpbfile_path = set_url_scheme( $uploads['basedir'] . $wpbfile['name'] );
			$wpbfile_url  = set_url_scheme( $uploads['baseurl'] . $wpbfile['name'] );

			$wpbfile_data    = file_exists( $wpbfile_path ) ? get_file_data( $wpbfile_path, array( 'Version' => 'Version' ) ) : array();
			$wpbfile_version = isset( $wpbfile_data['Version'] ) ? $wpbfile_data['Version'] : '';
			if ( $wpbfile_version && version_compare( WOODMART_WPB_CSS_VERSION, $wpbfile_version, '==' ) ) {
				$inline_css    = '';
				$inline_styles = wp_styles()->get_data( 'js_composer_front', 'after' );

				if ( woodmart_get_opt( 'inline_critical_css' ) ) {
					$inline_css = wp_unslash( stripslashes( get_option( 'woodmart-generated-wpbcss-css' ) ) );

					if ( $inline_css ) {
						$wpbfile_url = false;
					}
				}

				wp_deregister_style( 'js_composer_front' );
				wp_dequeue_style( 'js_composer_front' );
				wp_register_style( 'js_composer_front', $wpbfile_url, array(), $version );

				if ( $inline_css ) {
					wp_add_inline_style( 'js_composer_front', $inline_css );
				}
				if ( ! empty( $inline_styles ) ) {
					$inline_styles = implode( "\n", $inline_styles );
					wp_add_inline_style( 'js_composer_front', $inline_styles );
				}
			}
		}

		wp_enqueue_style( 'js_composer_front', false, array(), $version );

		if ( 'always' === woodmart_get_opt( 'font_awesome_css' ) ) {
			if ( 'wpb' === woodmart_get_current_page_builder() ) {
				wp_enqueue_style( 'vc_font_awesome_6' );
				wp_enqueue_style( 'vc_font_awesome_5_shims' );
			} else {
				wp_enqueue_style( 'elementor-icons-fa-solid' );
				wp_enqueue_style( 'elementor-icons-fa-brands' );
				wp_enqueue_style( 'elementor-icons-fa-regular' );
			}
		}

		if ( apply_filters( 'woodmart_enqueue_bootstrap_style', false ) ) {
			wp_enqueue_style( 'wd-bootstrap', WOODMART_STYLES . '/bootstrap-light.min.css', array(), $version );
		}

		if ( woodmart_is_combined_needed( 'combined_css' ) ) {
			if ( 'elementor' === woodmart_get_current_page_builder() ) {
				$style_url = WOODMART_STYLES . '/style' . $is_rtl . '-elementor.min.css';
			} else {
				$style_url = WOODMART_THEME_DIR . '/style.min.css';

				if ( $is_rtl ) {
					$style_url = WOODMART_STYLES . '/style' . $is_rtl . '.min.css';
				}
			}

			wp_enqueue_style( 'woodmart-style', $style_url, array(), $version );
		} else {
			wp_register_style( 'woodmart-style', false, array(), $version );
		}

		// Frontend admin bar.
		if ( is_admin_bar_showing() ) {
			wp_enqueue_style( 'woodmart-frontend-admin-bar', WOODMART_ASSETS . '/css/parts/base-adminbar.min.css', array(), $version );
		}

		// load typekit fonts.
		$typekit_id = woodmart_get_opt( 'typekit_id' );

		if ( $typekit_id ) {
			$project_ids = explode( ',', $typekit_id );

			foreach ( $project_ids as $id ) {
				wp_enqueue_style( 'woodmart-typekit-' . $id, 'https://use.typekit.net/' . esc_attr( $id ) . '.css', array(), $version );
			}
		}

		if ( woodmart_is_elementor_installed() && function_exists( 'woodmart_elementor_is_edit_mode' ) && ( woodmart_elementor_is_edit_mode() || woodmart_elementor_is_preview_page() || woodmart_elementor_is_preview_mode() ) ) {
			wp_enqueue_style( 'woodmart-elementor-editor', WOODMART_THEME_DIR . '/inc/integrations/elementor/assets/css/editor.css', array(), $version );
		}

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}

	add_action( 'wp_enqueue_scripts', 'woodmart_enqueue_base_styles', 10000 );
}

if ( ! function_exists( 'woodmart_force_enqueue_styles' ) ) {
	/**
	 * Force enqueue styles.
	 */
	function woodmart_force_enqueue_styles() {
		woodmart_force_enqueue_style( 'style-base' );

		$styles_always = woodmart_get_opt( 'styles_always_use' );
		if ( is_array( $styles_always ) ) {
			foreach ( $styles_always as $style ) {
				woodmart_force_enqueue_style( $style );
			}
		}

		$predefined_site_width = woodmart_get_opt( 'site_width' );

		if ( 'boxed' === $predefined_site_width || 'boxed-2' === $predefined_site_width ) {
			woodmart_force_enqueue_style( 'layout-wrapper-boxed' );
		}

		$header_settings = whb_get_settings();

		if ( post_password_required() ) {
			woodmart_force_enqueue_style( 'post-types-mod-password' );
		}

		if ( ( isset( $header_settings['overlap'] ) && $header_settings['overlap'] ) && ( isset( $header_settings['boxed'] ) && $header_settings['boxed'] ) ) {
			woodmart_force_enqueue_style( 'header-boxed' );
		}

		if ( in_array( woodmart_get_current_page_builder(), array( 'wpb', 'elementor' ), true ) ) {
			woodmart_force_enqueue_style( 'helpers-wpb-elem' );
		}

		if ( is_singular( 'post' ) ) {
			woodmart_force_enqueue_style( 'blog-single-base' );
		}

		if ( woodmart_get_opt( 'lazy_loading' ) || woodmart_get_opt( 'lazy_loading_bg_images' ) ) {
			woodmart_force_enqueue_style( 'lazy-loading' );
		}

		if ( is_singular( 'portfolio' ) || woodmart_is_portfolio_archive() ) {
			woodmart_force_enqueue_style( 'portfolio-base' );
		}

		if ( is_404() ) {
			woodmart_force_enqueue_style( 'page-404' );
		}

		if ( is_search() ) {
			woodmart_force_enqueue_style( 'page-search-results' );
		}

		if ( class_exists( 'ANR' ) ) {
			woodmart_force_enqueue_style( 'advanced-nocaptcha', true );
		}

		if ( function_exists( '_mc4wp_load_plugin' ) && ! get_option( 'wd_import_theme_version' ) ) {
			woodmart_force_enqueue_style( 'mc4wp', true );
		}

		if ( class_exists( 'bbPress' ) ) {
			woodmart_force_enqueue_style( 'bbpress', true );
		}

		if ( class_exists( 'WOOCS_STARTER' ) ) {
			woodmart_force_enqueue_style( 'woo-curr-switch', true );
		}

		if ( class_exists( 'WeDevs_Dokan' ) ) {
			woodmart_force_enqueue_style( 'woo-dokan-vend', true );
		}

		if ( class_exists( 'WooCommerce_Germanized' ) ) {
			woodmart_force_enqueue_style( 'woo-germanized', true );
		}

		if ( defined( 'WC_GATEWAY_PPEC_VERSION' ) ) {
			woodmart_force_enqueue_style( 'woo-paypal-express', true );
		}

		if ( defined( 'RS_REVISION' ) ) {
			woodmart_force_enqueue_style( 'revolution-slider', true );
		}

		if ( defined( 'WC_STRIPE_VERSION' ) && woodmart_woocommerce_installed() && ( is_product() || is_cart() || is_checkout() || is_account_page() ) ) {
			woodmart_force_enqueue_style( 'woo-stripe', true );
		}

		if ( defined( 'WCPAY_PLUGIN_FILE' ) ) {
			woodmart_force_enqueue_style( 'woo-payments', true );
		}

		if ( defined( 'KCO_WC_VERSION' ) || defined( 'WC_KLARNA_PAYMENTS_VERSION' ) ) {
			woodmart_force_enqueue_style( 'woo-klarna', true );
		}

		if ( defined( 'PAYPAL_API_URL' ) ) {
			woodmart_force_enqueue_style( 'woo-paypal-payments', true );
		}

		if ( class_exists( 'WCFM_Dependencies' ) ) {
			woodmart_force_enqueue_style( 'woo-wcfm-fm', true );
			woodmart_force_enqueue_style( 'colorbox-popup', true );
			woodmart_force_enqueue_style( 'select2' );
		}

		if ( class_exists( 'WC_Dependencies_Product_Vendor' ) ) {
			woodmart_force_enqueue_style( 'woo-multivendorx', true );
		}

		if ( class_exists( 'WC_Vendors' ) ) {
			woodmart_force_enqueue_style( 'woo-wc-vendors', true );
		}

		if ( class_exists( 'WC_Subscriptions' ) ) {
			woodmart_force_enqueue_style( 'woo-mod-variation-form', true );
			woodmart_force_enqueue_style( 'int-woo-subscriptions', true );
		}

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			woodmart_force_enqueue_style( 'wpml', true );
		}

		if ( defined( 'WCML_VERSION' ) ) {
			woodmart_force_enqueue_style( 'int-wpml-curr-switch', true );
		}

		if ( defined( 'YITH_WOOCOMPARE_VERSION' ) ) {
			woodmart_force_enqueue_style( 'woo-yith-compare', true );
			woodmart_force_enqueue_style( 'colorbox-popup' );
		}

		if ( defined( 'YITH_WPV_VERSION' ) ) {
			woodmart_force_enqueue_style( 'woo-yith-vendor', true );
		}

		if ( defined( 'YITH_YWRAQ_VERSION' ) ) {
			woodmart_force_enqueue_style( 'woo-yith-req-quote', true );

			woodmart_force_enqueue_style( 'woo-mod-grid' );
			woodmart_force_enqueue_style( 'woo-mod-quantity' );
			woodmart_force_enqueue_style( 'woo-mod-shop-table' );
			woodmart_force_enqueue_style( 'select2' );
		}

		if ( defined( 'YITH_WCWL' ) ) {
			woodmart_force_enqueue_style( 'woo-yith-wishlist', true );
			woodmart_force_enqueue_style( 'page-my-account' );
		}

		if ( woodmart_is_elementor_installed() ) {
			woodmart_force_enqueue_style( 'elementor-base' );
		}

		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			woodmart_force_enqueue_style( 'elementor-pro-base', true );
		}

		if ( defined( 'WPB_VC_VERSION' ) ) {
			woodmart_force_enqueue_style( 'wpbakery-base' );
			woodmart_force_enqueue_style( 'wpbakery-base-deprecated', true );
		}

		if ( defined( 'THWEPOF_VERSION' ) || defined( 'THWEPO_VERSION' ) ) {
			woodmart_force_enqueue_style( 'woo-extra-prod-opt', true );
		}

		if ( class_exists( 'PaymentPlugins\WooCommerce\PPCP\Main' ) ) {
			woodmart_force_enqueue_style( 'woo-payment-plugin-paypal', true );
		}

		if ( defined( 'WC_STRIPE_PLUGIN_FILE_PATH' ) ) {
			woodmart_force_enqueue_style( 'woo-payment-plugin-stripe', true );
		}

		if ( defined( 'WORDFENCE_VERSION' ) ) {
			woodmart_force_enqueue_style( 'int-wordfence', true );
		}

		if ( defined( 'AMELIA_VERSION' ) ) {
			woodmart_force_enqueue_style( 'amelia', true );
		}

		if ( defined( 'FPD_PLUGIN_DIR' ) ) {
			woodmart_force_enqueue_style( 'int-woo-fpd', true );
		}

		if ( woodmart_get_opt( 'sticky_notifications' ) ) {
			woodmart_force_enqueue_style( 'notices-fixed' );
		}

		if ( woodmart_woocommerce_installed() ) {
			woodmart_force_enqueue_style( 'woocommerce-base' );
			woodmart_force_enqueue_style( 'mod-star-rating' );
			woodmart_force_enqueue_style( 'woocommerce-block-notices' );

			if ( is_lost_password_page() ) {
				woodmart_force_enqueue_style( 'woo-page-lost-password' );
			}

			if ( is_cart() || is_checkout() || is_account_page() ) {
				woodmart_force_enqueue_style( 'select2' );
				woodmart_force_enqueue_style( 'woo-mod-shop-table' );
			}

			if ( is_checkout() || is_account_page() ) {
				woodmart_force_enqueue_style( 'woo-mod-grid' );
			}

			if ( is_cart() ) {
				woodmart_force_enqueue_style( 'page-cart' );

				if ( 'layout-2' === woodmart_get_opt( 'cart_totals_layout' ) ) {
					woodmart_force_enqueue_style( 'woo-page-cart-el-cart-totals-layout-2' );
				}

				if ( Main::get_instance()->has_custom_layout( 'cart' ) ) {
					woodmart_force_enqueue_style( 'woo-page-cart-builder' );
				} else {
					woodmart_force_enqueue_style( 'woo-page-cart-predefined' );
				}
			}

			if ( is_cart() || is_product() || ( is_active_widget( 0, 0, 'woocommerce_widget_cart' ) && woodmart_get_opt( 'mini_cart_quantity' ) ) ) {
				woodmart_force_enqueue_style( 'woo-mod-quantity' );
			}

			if ( is_checkout() ) {
				woodmart_force_enqueue_style( 'page-checkout' );
				woodmart_force_enqueue_style( 'page-checkout-payment-methods' );

				if ( ! Main::get_instance()->has_custom_layout( 'checkout_content' ) && ! Main::get_instance()->has_custom_layout( 'checkout_form' ) ) {
					woodmart_force_enqueue_style( 'woo-page-checkout-predefined' );
				} else {
					woodmart_force_enqueue_style( 'woo-page-checkout-builder' );
				}

				if ( Checkout_Order_Table::get_instance()->is_enable_woodmart_product_table_template() ) {
					woodmart_force_enqueue_style( 'woo-opt-manage-checkout-prod' );
				}
			}

			if ( ( is_cart() || is_checkout() ) && ( has_block( 'woocommerce/cart' ) || has_block( 'woocommerce/checkout' ) ) ) {
				woodmart_force_enqueue_style( 'wp-blocks-cart-checkout' );
			}

			if ( woodmart_get_opt( 'shipping_progress_bar_enabled' ) ) {
				if ( woodmart_get_opt( 'shipping_progress_bar_location_mini_cart' )
					|| is_checkout() && woodmart_get_opt( 'shipping_progress_bar_location_checkout' ) && ! Main::get_instance()->has_custom_layout( 'checkout_content' )
					|| is_cart() && woodmart_get_opt( 'shipping_progress_bar_location_card_page' ) && ! Main::get_instance()->has_custom_layout( 'cart' )
					|| is_product() && woodmart_get_opt( 'shipping_progress_bar_location_single_product' ) && ! Main::get_instance()->has_custom_layout( 'single_product' ) ) {
					woodmart_force_enqueue_style( 'woo-opt-free-progress-bar' );
					woodmart_force_enqueue_style( 'woo-mod-progress-bar' );
				}
			}

			if ( is_order_received_page() ) {
				woodmart_force_enqueue_style( 'woo-thank-you-page' );

				if ( ! Main::get_instance()->has_custom_layout( 'thank_you_page' ) ) {
					woodmart_force_enqueue_style( 'woo-thank-you-page-predefined' );
				}
			}

			if ( is_order_received_page() || is_account_page() ) {
				woodmart_force_enqueue_style( 'woo-mod-order-details' );
			}

			if ( is_wc_endpoint_url( 'order-pay' ) ) {
				woodmart_force_enqueue_style( 'woo-page-checkout-predefined' );
			}

			if ( is_account_page() ) {
				if ( is_user_logged_in() ) {
					woodmart_force_enqueue_style( 'page-my-account' );
				}
				if ( is_add_payment_method_page() ) {
					woodmart_force_enqueue_style( 'page-checkout-payment-methods' );
				}
				if ( is_edit_account_page() || is_wc_endpoint_url( 'lost-password' ) ) {
					woodmart_force_enqueue_style( 'woo-mod-login-form' );
				}
			}

			if ( is_product() ) {
				woodmart_force_enqueue_style( 'woo-single-prod-el-base' );
				woodmart_force_enqueue_style( 'woo-mod-stock-status' );
			}

			if ( is_product_taxonomy() || woodmart_is_shop_archive() ) {
				woodmart_force_enqueue_style( 'widget-active-filters' );

				if ( 'disable' !== woodmart_get_opt( 'shop_widgets_collapse', 'disable' ) ) {
					woodmart_force_enqueue_style( 'widget-collapse' );
				}

				if ( Main::get_instance()->has_custom_layout( 'shop_archive' ) ) {
					woodmart_force_enqueue_style( 'woo-shop-builder' );
				} else {
					woodmart_force_enqueue_style( 'woo-shop-predefined' );
				}

				if ( woodmart_get_opt( 'shop_categories' ) && ! Main::get_instance()->has_custom_layout( 'shop_archive' ) ) {
					woodmart_force_enqueue_style( 'shop-title-categories' );
					woodmart_force_enqueue_style( 'woo-categories-loop-nav-mobile-accordion' );
				}
			}

			if ( ! Main::get_instance()->has_custom_layout( 'shop_archive' ) && ( is_product_taxonomy() || woodmart_is_shop_archive() || ( function_exists( 'wcfm_is_store_page' ) && wcfm_is_store_page() ) ) ) {
				woodmart_force_enqueue_style( 'woo-shop-el-products-per-page' );
				woodmart_force_enqueue_style( 'woo-shop-page-title' );
				woodmart_force_enqueue_style( 'woo-mod-shop-loop-head' );

				if ( ! woodmart_get_opt( 'shop_filters' ) ) {
					woodmart_force_enqueue_style( 'woo-shop-el-order-by' );
				}

				if ( woodmart_get_opt( 'per_row_columns_selector' ) && woodmart_get_opt( 'products_columns_variations' ) ) {
					woodmart_force_enqueue_style( 'woo-shop-el-products-view' );
				}

				if ( ! woodmart_get_opt( 'shop_title' ) ) {
					woodmart_force_enqueue_style( 'woo-shop-opt-without-title' );
				}
			}

			if ( woodmart_get_opt( 'bought_together_enabled', 1 ) && ( is_cart() || is_checkout() ) ) {
				woodmart_force_enqueue_style( 'woo-opt-fbt-cart' );
				woodmart_force_enqueue_style( 'woo-mod-cart-labels' );
			}

			if ( woodmart_get_opt( 'free_gifts_enabled', 0 ) && ( is_cart() || is_checkout() ) ) {
				woodmart_force_enqueue_style( 'woo-opt-fg' );
				woodmart_force_enqueue_style( 'woo-mod-cart-labels' );
			}

			$compare_page     = function_exists( 'wpml_object_id_filter' ) ? wpml_object_id_filter( woodmart_get_opt( 'compare_page' ), 'page', true ) : woodmart_get_opt( 'compare_page' );
			$wishlist_page    = function_exists( 'wpml_object_id_filter' ) ? wpml_object_id_filter( woodmart_get_opt( 'wishlist_page' ), 'page', true ) : woodmart_get_opt( 'wishlist_page' );
			$is_wishlist_page = $wishlist_page && (int) woodmart_get_the_ID() === (int) $wishlist_page;

			if ( ( ( is_user_logged_in() && is_account_page() ) || $is_wishlist_page ) && ! Main::get_instance()->has_custom_layout( 'my_account_page' ) ) {
				woodmart_force_enqueue_style( 'page-my-account-predefined' );
			}

			if ( ! is_user_logged_in() && is_account_page() && ! is_wc_endpoint_url( 'lost-password' ) ) {
				woodmart_force_enqueue_style( 'woo-page-login-register' );

				if ( ! Main::get_instance()->has_custom_layout( 'my_account_auth' ) ) {
					woodmart_force_enqueue_style( 'woo-page-login-register-predefined' );
				}
			}

			if ( $compare_page && (int) woodmart_get_the_ID() === (int) $compare_page ) {
				woodmart_force_enqueue_style( 'page-compare' );
				woodmart_force_enqueue_style( 'woo-mod-stock-status' );
			}

			if ( $is_wishlist_page ) {
				woodmart_force_enqueue_style( 'page-wishlist' );
				woodmart_force_enqueue_style( 'page-my-account' );
			}

			if ( woodmart_get_opt( 'hide_larger_price' ) ) {
				woodmart_force_enqueue_style( 'woo-opt-hide-larger-price' );
			}

			if ( woodmart_get_opt( 'attr_after_short_desc' ) || 'additional_info' === woodmart_get_opt( 'base_hover_content' ) || is_product() ) {
				woodmart_force_enqueue_style( 'woo-mod-shop-attributes' );
			}

			if ( get_option( 'woocommerce_coming_soon', 'no' ) === 'yes' && get_option( 'woocommerce_store_pages_only' ) === 'yes' && get_current_user_id() && ( current_user_can( 'shop_manager' ) || current_user_can( 'administrator' ) ) && WCAdminHelper::is_current_page_store_page() ) {
				woodmart_force_enqueue_style( 'woo-opt-coming-soon' );
			}
		}

		if ( woodmart_get_opt( 'disable_owl_mobile_devices' ) ) {
			woodmart_force_enqueue_style( 'opt-carousel-disable' );
		}

		if ( 'underlined' === woodmart_get_opt( 'form_fields_style' ) ) {
			woodmart_force_enqueue_style( 'opt-form-underline' );
		}

		if ( defined( 'WCV_VERSION' ) || ( class_exists( 'WeDevs_Dokan' ) && ( dokan_is_store_page() || dokan_is_store_listing() || dokan_is_seller_dashboard() ) ) ) {
			woodmart_force_enqueue_style( 'select2' );
		}

		if ( ! woodmart_get_opt( 'disable_gutenberg_css' ) ) {
			woodmart_force_enqueue_style( 'wp-blocks' );
		}
	}

	add_action( 'wp_enqueue_scripts', 'woodmart_force_enqueue_styles', 10001 );
}

if ( ! function_exists( 'woodmart_enqueue_product_loop_styles' ) ) {
	/**
	 * Enqueue product loop style files.
	 *
	 * @param string $design Design.
	 */
	function woodmart_enqueue_product_loop_styles( $design ) {
		woodmart_enqueue_inline_style( 'product-loop' );

		if ( 'button' === $design || 'info-alt' === $design ) {
			woodmart_enqueue_inline_style( 'product-loop-button-info-alt' );
		} else {
			woodmart_enqueue_inline_style( 'product-loop-' . $design );
		}

		if ( in_array( $design, array( 'standard', 'button', 'base', 'info-alt', 'quick', 'list', 'fw-button', 'buttons-on-hover' ), true ) && ! woodmart_get_opt( 'catalog_mode' ) ) {
			woodmart_enqueue_inline_style( 'woo-mod-add-btn-replace' );
		}

		if ( woodmart_loop_prop( 'product_quantity' ) ) {
			woodmart_enqueue_inline_style( 'woo-mod-quantity' );
			woodmart_enqueue_inline_style( 'woo-mod-quantity-overlap' );
		}

		if ( woodmart_get_opt( 'grid_swatches_attribute' ) ) {
			woodmart_enqueue_inline_style( 'woo-mod-swatches-base' );
		}

		if ( 'base' === $design || 'fw-button' === $design ) {
			woodmart_enqueue_inline_style( 'mod-more-description' );
		}
	}
}

if ( ! function_exists( 'woodmart_enqueue_portfolio_loop_styles' ) ) {
	/**
	 * Enqueue product loop style files.
	 *
	 * @param string $design Design.
	 */
	function woodmart_enqueue_portfolio_loop_styles( $design ) {
		if ( 'hover' === $design ) {
			woodmart_enqueue_inline_style( 'project-text-hover' );
		}

		if ( 'hover-inverse' === $design ) {
			woodmart_enqueue_inline_style( 'project-alt' );
		}

		if ( 'text-shown' === $design ) {
			woodmart_enqueue_inline_style( 'project-under' );
		}

		if ( 'parallax' === $design ) {
			woodmart_enqueue_inline_style( 'project-parallax' );
		}
	}
}

if ( ! function_exists( 'woodmart_enqueue_multipage_styles' ) ) {
	/**
	 * Enqueue inline style for multipages with page breaks.
	 *
	 * @param mixed $output Wp links output.
	 */
	function woodmart_enqueue_multipage_styles( $output ) {
		global $multipage;

		if ( $multipage ) {
			ob_start();
			woodmart_enqueue_inline_style( 'post-types-mod-pagination' );
			$style  = ob_get_clean();
			$output = $style . $output;
		}

		return $output;
	}

	add_action( 'wp_link_pages', 'woodmart_enqueue_multipage_styles' );
}

if ( ! function_exists( 'woodmart_enqueue_inline_style' ) ) {
	/**
	 * Enqueue inline style by key.
	 *
	 * @param string $key File slug.
	 */
	function woodmart_enqueue_inline_style( $key, $ignore_combined = false ) {
		if ( function_exists( 'wc' ) && ( wc()->is_rest_api_request() || woodmart_is_woocommerce_legacy_rest_api() ) || wp_is_serving_rest_request() ) {
			return;
		}

		Parts_Css_Files::get_instance()->enqueue_inline_style( $key, $ignore_combined );
	}
}

if ( ! function_exists( 'woodmart_force_enqueue_style' ) ) {
	/**
	 * Enqueue style by key.
	 *
	 * @param string $key File slug.
	 */
	function woodmart_force_enqueue_style( $key, $ignore_combined = false ) {
		Parts_Css_Files::get_instance()->enqueue_style( $key, $ignore_combined );
	}
}

if ( ! function_exists( 'woodmart_enqueue_inline_style_anchor' ) ) {
	function woodmart_enqueue_inline_style_anchor() {
		wp_enqueue_style( 'woodmart-inline-css' );
	}

	add_action( 'wp_footer', 'woodmart_enqueue_inline_style_anchor', 10 );
}

if ( ! function_exists( 'woodmart_enqueue_blocks_styles' ) ) {
	/**
	 * Enqueue styles for blocks.
	 *
	 * @param string $value Block content.
	 * @param array  $block Block data.
	 * @return string
	 */
	function woodmart_enqueue_blocks_styles( $value, $block ) {
		if ( 'woocommerce/coming-soon' === $block['blockName'] ) {
			woodmart_force_enqueue_style( 'woo-opt-coming-soon' );
		}

		return $value;
	}

	add_filter( 'pre_render_block', 'woodmart_enqueue_blocks_styles', 10, 2 );
}

if ( ! function_exists( 'woodmart_settings_js' ) ) {
	/**
	 * Get settings JS.
	 *
	 * @return false|string
	 */
	function woodmart_settings_js() {

		$custom_js = woodmart_get_opt( 'custom_js' );
		$js_ready  = woodmart_get_opt( 'js_ready' );

		ob_start();

		if ( ! empty( $custom_js ) || ! empty( $js_ready ) ) :
			?>
			<?php if ( ! empty( $custom_js ) ) : ?>
			<?php echo woodmart_get_opt( 'custom_js' ); ?>
		<?php endif; ?>
			<?php if ( ! empty( $js_ready ) ) : ?>
			jQuery(document).ready(function() {
			<?php echo woodmart_get_opt( 'js_ready' ); ?>
			});
		<?php endif; ?>
		<?php
		endif;

		return ob_get_clean();
	}
}

if ( ! function_exists( 'wooodmart_dequeue_woo_coming_soon_fonts' ) ) {
	/**
	 * Dequeue WooCommerce Coming Soon fonts family.
	 *
	 * @return void
	 */
	function wooodmart_dequeue_woo_coming_soon_fonts() {
		if ( ! class_exists( 'Automattic\WooCommerce\Internal\ComingSoon\ComingSoonRequestHandler' ) || ! method_exists( 'Automattic\WooCommerce\Internal\ComingSoon\ComingSoonRequestHandler', 'experimental_filter_theme_json_theme' ) ) {
			return;
		}

		$container = wc_get_container();

		remove_filter( 'wp_theme_json_data_theme', array( $container->get( Automattic\WooCommerce\Internal\ComingSoon\ComingSoonRequestHandler::class ), 'experimental_filter_theme_json_theme' ) );
	}

	add_action( 'init', 'wooodmart_dequeue_woo_coming_soon_fonts', 500 );
}

if ( ! function_exists( 'woodmart_enqueue_widgets_sidebar_style' ) ) {
	/**
	 * Enqueue widgets sidebar style.
	 *
	 * @param int|string $index Sidebar index.
	 * @return void
	 */
	function woodmart_enqueue_widgets_sidebar_style( $index ) {
		global $sidebars_widgets;

		if ( empty( $sidebars_widgets[ $index ] ) ) {
			return;
		}

		foreach ( $sidebars_widgets[ $index ] as $widget ) {
			if ( str_starts_with( $widget, 'woodmart-price-filter' ) ) {
				woodmart_enqueue_inline_style( 'widget-price-filter' );
			} elseif ( str_starts_with( $widget, 'woodmart-woocommerce-layered-nav' ) ) {
				woodmart_enqueue_inline_style( 'widget-wd-layered-nav' );
				woodmart_enqueue_inline_style( 'woo-mod-swatches-base' );
				woodmart_enqueue_inline_style( 'woo-mod-swatches-filter' );
			} elseif ( str_starts_with( $widget, 'woodmart-recent-posts' ) ) {
				woodmart_enqueue_inline_style( 'widget-wd-recent-posts' );
			} elseif ( str_starts_with( $widget, 'woodmart-user-panel' ) ) {
				woodmart_enqueue_inline_style( 'widget-user-panel' );
			} elseif ( str_starts_with( $widget, 'media_gallery' ) ) {
				woodmart_enqueue_inline_style( 'widget-media-gallery' );
			} elseif ( str_starts_with( $widget, 'woocommerce_rating_filter' ) || str_starts_with( $widget, 'woocommerce_recent_reviews' ) || str_starts_with( $widget, 'woodmart-woocommerce-sort-by' ) ) {
				woodmart_enqueue_inline_style( 'widget-woo-other' );
			} elseif ( str_starts_with( $widget, 'woocommerce_price_filter' ) ) {
				woodmart_enqueue_inline_style( 'widget-slider-price-filter' );
			} elseif ( str_starts_with( $widget, 'calendar' ) ) {
				woodmart_enqueue_inline_style( 'widget-calendar' );
			} elseif ( str_starts_with( $widget, 'rss' ) ) {
				woodmart_enqueue_inline_style( 'widget-rss' );
			} elseif ( str_starts_with( $widget, 'woocommerce_product_tag_cloud' ) || str_starts_with( $widget, 'tag_cloud' ) ) {
				woodmart_enqueue_inline_style( 'widget-tag-cloud' );
			} elseif ( str_starts_with( $widget, 'recent-comments' ) || str_starts_with( $widget, 'recent-posts' ) ) {
				woodmart_enqueue_inline_style( 'widget-recent-post-comments' );
			} elseif ( str_starts_with( $widget, 'nav_mega_menu' ) ) {
				woodmart_enqueue_inline_style( 'el-menu' );
			} elseif ( str_starts_with( $widget, 'categories' ) || str_starts_with( $widget, 'pages' ) || str_starts_with( $widget, 'archives' ) || str_starts_with( $widget, 'nav_menu' ) ) {
				woodmart_enqueue_inline_style( 'widget-nav' );
			} elseif ( str_starts_with( $widget, 'woocommerce_product_categories' ) ) {
				woodmart_enqueue_inline_style( 'widget-product-cat' );
			} elseif ( str_starts_with( $widget, 'woocommerce_layered_nav' ) || str_starts_with( $widget, 'wd-widget-stock-status' ) || str_starts_with( $widget, 'woocommerce_brand_nav' ) ) {
				woodmart_enqueue_inline_style( 'widget-layered-nav-stock-status' );
			} elseif ( str_starts_with( $widget, 'woocommerce_layered_nav_filters' ) ) {
				woodmart_enqueue_inline_style( 'widget-active-filters' );
			} elseif ( str_starts_with( $widget, 'woocommerce_products' ) || str_starts_with( $widget, 'woocommerce_top_rated_products' ) ) {
				woodmart_enqueue_inline_style( 'widget-product-list' );
			} elseif ( str_starts_with( $widget, 'woocommerce_widget_cart' ) ) {
				woodmart_enqueue_inline_style( 'widget-shopping-cart' );
			} elseif ( str_starts_with( $widget, 'wc_brands_brand_description' ) || str_starts_with( $widget, 'wc_brands_brand_thumbnails' ) ) {
				woodmart_enqueue_inline_style( 'widget-brand-thumbnails' );
			}
		}
	}

	add_action( 'dynamic_sidebar_before', 'woodmart_enqueue_widgets_sidebar_style' );
}

if ( ! function_exists( 'woodmart_enqueue_style_in_iframe' ) ) {
	/**
	 * Enqueue styles in iframe.
	 *
	 * @return void
	 */
	function woodmart_enqueue_style_in_iframe() {
		if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST || ! defined( 'IFRAME_REQUEST' ) ) {
			return;
		}

		$storage = new Styles_Storage( 'theme_settings_default' );

		$storage->print_styles();

		$rtl = is_rtl() ? '-rtl' : '';

		wp_enqueue_style( 'wd-widgets-editor-style', WOODMART_THEME_DIR . '/css/parts/wp-editor-widgets' . $rtl . '.min.css', array(), woodmart_get_theme_info( 'Version' ) );
	}

	add_action( 'wp_enqueue_scripts', 'woodmart_enqueue_style_in_iframe', 10001 );
}

if ( ! function_exists( 'woodmart_enqueue_contact_form_style' ) ) {
	/**
	 * Enqueue contact form style.
	 *
	 * @param string $elements Elements.
	 *
	 * @return string
	 */
	function woodmart_enqueue_contact_form_style( $elements ) {
		ob_start();

		woodmart_enqueue_inline_style( 'wpcf7', true );

		return ob_get_clean() . $elements;
	}

	add_action( 'wpcf7_form_elements', 'woodmart_enqueue_contact_form_style' );
}
