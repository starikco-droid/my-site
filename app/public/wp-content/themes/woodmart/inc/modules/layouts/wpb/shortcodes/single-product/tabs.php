<?php
/**
 * Tabs shortcode.
 *
 * @package Woodmart
 */

use XTS\Modules\Layouts\Global_Data;
use XTS\Modules\Layouts\Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

if ( ! function_exists( 'woodmart_shortcode_single_product_tabs' ) ) {
	/**
	 * Single product tabs shortcode.
	 *
	 * @param array $settings Shortcode attributes.
	 */
	function woodmart_shortcode_single_product_tabs( $settings ) {
		$default_settings = array(
			'layout'                              => 'tabs',
			'accordion_on_mobile'                 => 'no',
			'attr_hide_name'                      => 'no',
			'attr_hide_image'                     => 'no',
			'enable_additional_info'              => 'yes',
			'enable_reviews'                      => 'yes',
			'enable_description'                  => 'yes',
			'additional_info_style'               => 'bordered',
			'additional_info_layout'              => 'list',
			'reviews_layout'                      => 'one-column',
			'reviews_columns'                     => '',
			'css'                                 => '',
			'side_hidden_content_position'        => 'right',
			'side_hidden_title_text_color_scheme' => 'inherit',

			/**
			 * Tabs Settings.
			 */
			'tabs_style'                          => 'default',
			'tabs_title_text_color_scheme'        => 'inherit',
			'tabs_alignment'                      => 'center',
			'tabs_content_text_color_scheme'      => 'inherit',

			'tabs_bg_color_enable'                => 'no',
			'tabs_bg_hover_color_enable'          => 'no',
			'tabs_bg_active_color_enable'         => 'no',
			'tabs_border_enable'                  => 'no',
			'tabs_border_hover_enable'            => 'no',
			'tabs_border_active_enable'           => 'no',
			'tabs_box_shadow_enable'              => 'no',
			'tabs_box_shadow_hover_enable'        => 'no',
			'tabs_box_shadow_active_enable'       => 'no',

			/**
			 * Accordion Settings.
			 */
			'accordion_state'                     => 'first',
			'accordion_style'                     => 'default',
			'accordion_title_text_color_scheme'   => 'inherit',
			'accordion_alignment'                 => 'left',
			'accordion_hide_top_bottom_border'    => 'no',

			/**
			 * Opener Settings.
			 */
			'accordion_opener_alignment'          => 'left',
			'accordion_opener_style'              => 'arrow',
			'all_open_style'                      => 'default',
		);

		$settings = wp_parse_args( $settings, $default_settings );

		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
			$key   = 'reviews_columns' . ( 'desktop' === $device ? '' : '_' . $device );
			$value = woodmart_vc_get_control_data( $settings['reviews_columns'], $device );

			if ( ! $value ) {
				$value = 1;
			}

			Global_Data::get_instance()->set_data( $key, $value );
		}

		$wrapper_classes  = apply_filters( 'vc_shortcodes_css_class', '', '', $settings );

		if ( $settings['css'] ) {
			$wrapper_classes .= ' ' . vc_shortcode_custom_css_class( $settings['css'] );
		}

		if ( 'all-open' === $settings['layout'] ) {
			$wrapper_classes .= ' tabs-layout-all-open';
			$wrapper_classes .= ' wd-title-style-' . $settings['all_open_style'];
		}

		$additional_info_classes  = ' wd-layout-' . $settings['additional_info_layout'];
		$additional_info_classes .= ' wd-style-' . $settings['additional_info_style'];
		$reviews_classes          = ' wd-layout-' . woodmart_vc_get_control_data( $settings['reviews_layout'], 'desktop' );
		$reviews_classes         .= ' wd-form-pos-' . woodmart_get_opt( 'reviews_form_location', 'after' );
		$args                     = array();
		$title_content_classes    = '';

		if ( 'yes' === $settings['enable_additional_info'] ) {
			$additional_info_classes .= 'yes' === $settings['attr_hide_name'] ? ' wd-hide-name' : '';
			$additional_info_classes .= 'yes' === $settings['attr_hide_image'] ? ' wd-hide-image' : '';
		}

		if ( 'inherit' !== $settings['tabs_content_text_color_scheme'] ) {
			$title_content_classes .= ' color-scheme-' . $settings['tabs_content_text_color_scheme'];
		}

		if ( 'side-hidden' === $settings['layout'] ) {
			$title_content_classes .= ' wd-' . woodmart_vc_get_control_data( $settings['side_hidden_content_position'], 'desktop' );
		}

		$default_args = array(
			'builder_additional_info_classes' => $additional_info_classes,
			'builder_reviews_classes'         => $reviews_classes,
			'builder_content_classes'         => $title_content_classes,
		);

		if ( 'tabs' === $settings['layout'] ) {
			$title_wrapper_classes = ' text-' . woodmart_vc_get_control_data( $settings['tabs_alignment'], 'desktop' );
			$title_classes         = ' wd-style-' . $settings['tabs_style'];

			if ( 'inherit' !== $settings['tabs_title_text_color_scheme'] && 'custom' !== $settings['tabs_title_text_color_scheme'] ) {
				$title_wrapper_classes .= ' color-scheme-' . $settings['tabs_title_text_color_scheme'];
			}

			$tabs_title_bg_activated      = 'yes' === $settings['tabs_bg_color_enable'] || 'yes' === $settings['tabs_bg_hover_color_enable'] || 'yes' === $settings['tabs_bg_active_color_enable'];
			$tabs_title_box_shadow_active = 'yes' === $settings['tabs_box_shadow_enable'] || 'yes' === $settings['tabs_box_shadow_hover_enable'] || 'yes' === $settings['tabs_box_shadow_active_enable'];
			$tabs_title_border_active     = 'yes' === $settings['tabs_border_enable'] || 'yes' === $settings['tabs_border_hover_enable'] || 'yes' === $settings['tabs_border_active_enable'];

			if ( $tabs_title_bg_activated || $tabs_title_box_shadow_active || $tabs_title_border_active ) {
				$title_classes .= ' wd-add-pd';
			}

			$args = array(
				'builder_tabs_classes'             => $title_classes,
				'builder_tabs_wrapper_classes'     => 'yes' === $settings['accordion_on_mobile'] ? ' wd-opener-pos-right' : '',
				'builder_nav_tabs_wrapper_classes' => $title_wrapper_classes,
				'accordion_on_mobile'              => $settings['accordion_on_mobile'],
			);
		} elseif ( 'accordion' === $settings['layout'] ) {
			$accordion_classes  = ' wd-style-' . $settings['accordion_style'];
			$accordion_state    = $settings['accordion_state'];
			$accordion_classes .= ' wd-opener-style-' . $settings['accordion_opener_style'];
			$accordion_classes .= ' wd-titles-' . woodmart_vc_get_control_data( $settings['accordion_alignment'], 'desktop' );
			$accordion_classes .= ' wd-opener-pos-' . woodmart_vc_get_control_data( $settings['accordion_opener_alignment'], 'desktop' );
			$title_classes      = '';

			if ( 'inherit' !== $settings['accordion_title_text_color_scheme'] && 'custom' !== $settings['accordion_title_text_color_scheme'] ) {
				$title_classes .= ' color-scheme-' . $settings['accordion_title_text_color_scheme'];
			}

			if ( 'yes' === $settings['accordion_hide_top_bottom_border'] ) {
				$accordion_classes .= ' wd-border-off';
			}

			$args = array(
				'builder_accordion_classes' => $accordion_classes,
				'builder_state'             => $accordion_state,
				'builder_title_classes'     => $title_classes,
			);
		} elseif ( 'side-hidden' === $settings['layout'] ) {
			$title_classes = '';

			if ( 'inherit' !== $settings['side_hidden_title_text_color_scheme'] && 'custom' !== $settings['side_hidden_title_text_color_scheme'] ) {
				$title_classes .= ' color-scheme-' . $settings['side_hidden_title_text_color_scheme'];
			}

			$args = array(
				'builder_title_classes' => $title_classes,
			);
		}

		$args = array_merge( $default_args, $args );

		add_filter(
			'woocommerce_product_tabs',
			function ( $tabs ) use ( $settings ) {
				if ( isset( $tabs['description'] ) ) {
					$tabs['description']['wd_show'] = $settings['enable_description'];
				}

				if ( isset( $tabs['additional_information'] ) ) {
					$tabs['additional_information']['wd_show'] = $settings['enable_additional_info'];
				}

				if ( isset( $tabs['reviews'] ) ) {
					$tabs['reviews']['wd_show'] = $settings['enable_reviews'];
				}

				return $tabs;
			},
			97 // The priority must be lower than the one used in the woodmart_maybe_unset_wc_tabs fucntion.
		);

		ob_start();

		wp_enqueue_script( 'wc-single-product' );

		Main::setup_preview();

		if ( 'yes' === $settings['enable_reviews'] ) {
			woodmart_enqueue_inline_style( 'post-types-mod-comments' );
		}

		if ( comments_open() ) {
			if ( woodmart_get_opt( 'reviews_rating_summary' ) && function_exists( 'wc_review_ratings_enabled' ) && wc_review_ratings_enabled() ) {
				woodmart_enqueue_inline_style( 'woo-single-prod-opt-rating-summary' );
			}

			woodmart_enqueue_inline_style( 'woo-single-prod-el-reviews' );
			woodmart_enqueue_inline_style( 'woo-single-prod-el-reviews-' . woodmart_get_opt( 'reviews_style', 'style-1' ) );
			woodmart_enqueue_js_script( 'woocommerce-comments' );
		}

		if ( 'accordion' === $settings['layout'] ) {
			woodmart_enqueue_inline_style( 'accordion-elem-wpb' );
		}
		?>
		<div class="wd-single-tabs wd-wpb<?php echo esc_attr( $wrapper_classes ); ?>">
			<?php
			wc_get_template(
				'single-product/tabs/tabs-' . sanitize_file_name( $settings['layout'] ) . '.php',
				$args
			);
			?>
		</div>
		<?php
		Main::restore_preview();

		return ob_get_clean();
	}
}
