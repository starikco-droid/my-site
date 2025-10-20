<?php if ( ! defined( 'WOODMART_THEME_DIR' ) ) {
	exit( 'No direct script access allowed' );}

/**
 * ------------------------------------------------------------------------------------------------
 * Products tabs shortcode
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_shortcode_products_tabs' ) ) {
	function woodmart_shortcode_products_tabs( $atts = array(), $content = null ) {
		$output = $class = $autoplay = $header_classes = '';

		$atts = shortcode_atts(
			array(
				'title'                         => '',
				'image'                         => '',
				'img_size'                      => '30x30',
				'design'                        => 'default',
				'alignment'                     => 'center',
				'icon_position'                 => 'left',
				'color'                         => '#83b735',
				'description'                   => '',
				'tabs_title_color_scheme'       => 'inherit',
				'tabs_style'                    => 'underline',
				'enable_heading_bg'             => 'no',

				'woodmart_css_id'               => '',
				'el_class'                      => '',
				'css'                           => '',
				'wd_animation'                  => '',
				'wd_animation_delay'            => '',
				'wd_hide_on_desktop'            => '',
				'wd_hide_on_tablet'             => '',
				'wd_hide_on_mobile'             => '',

				'tabs_bg_color_enable'          => 'no',
				'tabs_bg_hover_color_enable'    => 'no',
				'tabs_bg_active_color_enable'   => 'no',
				'tabs_border_enable'            => 'no',
				'tabs_border_hover_enable'      => 'no',
				'tabs_border_active_enable'     => 'no',
				'tabs_box_shadow_enable'        => 'no',
				'tabs_box_shadow_hover_enable'  => 'no',
				'tabs_box_shadow_active_enable' => 'no',
			),
			$atts
		);
		extract( $atts );

		$img_id = preg_replace( '/[^\d]/', '', $image );

		if ( ! $woodmart_css_id ) {
			$woodmart_css_id = uniqid();
		}
		$tabs_id = 'wd-' . $woodmart_css_id;

		// Extract tab titles
		preg_match_all( '/products_tab([^\]]+)/i', $content, $matches, PREG_OFFSET_CAPTURE );
		$tab_titles = array();

		if ( isset( $matches[1] ) ) {
			$tab_titles = $matches[1];
		}

		$tabs_nav        = '';
		$first_tab_title = '';
		$_i              = 0;
		$wd_nav_classes  = '';

		$wd_nav_classes .= ' wd-style-' . $tabs_style;
		$wd_nav_classes .= ' wd-icon-pos-' . $icon_position;

		$tabs_bg_activated      = 'yes' === $tabs_bg_color_enable || 'yes' === $tabs_bg_hover_color_enable || 'yes' === $tabs_bg_active_color_enable;
		$tabs_border_active     = 'yes' === $tabs_border_enable || 'yes' === $tabs_border_hover_enable || 'yes' === $tabs_border_active_enable;
		$tabs_box_shadow_active = 'yes' === $tabs_box_shadow_enable || 'yes' === $tabs_box_shadow_hover_enable || 'yes' === $tabs_box_shadow_active_enable;

		if ( $tabs_bg_activated || $tabs_box_shadow_active || $tabs_border_active ) {
			$wd_nav_classes .= ' wd-add-pd';
		}

		$tabs_nav .= '<ul class="wd-nav wd-nav-tabs products-tabs-title' . esc_attr( $wd_nav_classes ) . '">';

		foreach ( $tab_titles as $tab ) {
			$_i++;
			$tab_atts          = shortcode_parse_atts( $tab[0] );
			$icon_output       = '';
			$tabs_icon_library = '';

			if ( 'simple' === $design ) {
				if ( empty( $tab_atts['pagination_arrows_position'] ) ) {
					$tab_atts['pagination_arrows_position'] = 'together';
				}
				if ( empty( $tab_atts['carousel_arrows_position'] ) ) {
					$tab_atts['carousel_arrows_position'] = 'together';
				}
			}

			if ( isset( $tab_atts['title_icon_type'] ) && 'icon' === $tab_atts['title_icon_type'] ) {
				if ( ! isset( $tab_atts['tabs_icon_libraries'] ) || ! $tab_atts['tabs_icon_libraries'] ) {
					$tab_atts['tabs_icon_libraries'] = 'fontawesome';
				}

				$tabs_icon_library = $tab_atts[ 'icon_' . $tab_atts['tabs_icon_libraries'] ];
				vc_icon_element_fonts_enqueue( $tab_atts['tabs_icon_libraries'] );
			}

			if ( empty( $tab_atts['icon_size'] ) ) {
				$tab_atts['icon_size'] = '25x25';
			}

			// Tab icon
			if ( isset( $tab_atts['icon'] ) && $tab_atts['icon'] ) {
				$icon_output = woodmart_display_icon( $tab_atts['icon'], $tab_atts['icon_size'], 25 );

				if ( woodmart_is_svg( wp_get_attachment_image_src( $tab_atts['icon'] )[0] ) ) {
					$icon_output = '<span class="img-wrapper">' . woodmart_get_svg_html( $tab_atts['icon'], $tab_atts['icon_size'] ) . '</span>';
				}
			} elseif ( $tabs_icon_library ) {
				$icon_output = '<span class="img-wrapper"><i class="' . esc_attr( $tabs_icon_library ) . '"></i></span>';
			}

			if ( $_i == 1 && isset( $tab_atts['title'] ) ) {
				$first_tab_title = $tab_atts['title'];
			}
			$class = ( $_i == 1 ) ? ' wd-active' : '';
			if ( isset( $tab_atts['title'] ) ) {
				$tabs_nav .= '<li data-atts="' . esc_attr( json_encode( $tab_atts ) ) . '" class="' . esc_attr( $class ) . '"><a href="#" class="wd-nav-link">' . $icon_output . '<span class="tab-label nav-link-text">' . $tab_atts['title'] . '</span></a></li>';
			}
		}

		$tabs_nav .= '</ul>';

		$class .= ' tabs-' . $tabs_id;

		$class .= ' tabs-design-' . $design;

		$class .= ' ' . $el_class;

		$class .= woodmart_get_old_classes( ' woodmart-products-tabs' );

		$class .= apply_filters( 'vc_shortcodes_css_class', '', '', $atts );

		if ( $css ) {
			$class .= ' ' . vc_shortcode_custom_css_class( $css );
		}

		if ( 'yes' === $enable_heading_bg ) {
			$class .= ' wd-header-with-bg';
		}

		$nav_tabs_wrapper_classes = '';

		if ( 'inherit' !== $tabs_title_color_scheme && 'custom' !== $tabs_title_color_scheme ) {
			$nav_tabs_wrapper_classes .= ' color-scheme-' . $tabs_title_color_scheme;
		}

		if ( 'default' === $design ) {
			$header_classes .= ' text-' . $alignment;
		}

		woodmart_enqueue_js_script( 'products-tabs' );

		ob_start();
		woodmart_enqueue_inline_style( 'tabs' );
		woodmart_enqueue_inline_style( 'product-tabs' );
		?>
		<div id="<?php echo esc_attr( $tabs_id ); ?>" class="wd-tabs wd-products-tabs wd-wpb<?php echo esc_attr( $class ); ?>">
			<div class="wd-tabs-header<?php echo esc_attr( $header_classes ); ?>">
				<?php if ( ! empty( $title ) ) : ?>
					<div class="tabs-name title">
						<?php
						if ( $img_id ) {
							echo woodmart_display_icon( $img_id, $img_size, 30 );}
						?>
						<span class="tabs-text"><?php echo wp_kses( $title, woodmart_get_allowed_html() ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $description ) : ?>
					<div class="wd-tabs-desc"><?php echo wp_kses( $description, woodmart_get_allowed_html() ); ?></div>
				<?php endif; ?>

				<div class="wd-nav-wrapper wd-nav-tabs-wrapper tabs-navigation-wrapper<?php echo esc_attr( $nav_tabs_wrapper_classes ); ?>">
					<?php
					echo ! empty( $tabs_nav ) ? $tabs_nav : '';
					?>
				</div>
			</div>
			<?php
			if ( isset( $tab_titles[0][0] ) ) {
				$first_tab_atts = shortcode_parse_atts( $tab_titles[0][0] );

				if ( 'simple' === $design ) {
					if ( empty( $first_tab_atts['pagination_arrows_position'] ) ) {
						$first_tab_atts['pagination_arrows_position'] = 'together';
					}
					if ( empty( $first_tab_atts['carousel_arrows_position'] ) ) {
						$first_tab_atts['carousel_arrows_position'] = 'together';
					}
				}

				echo woodmart_shortcode_products_tab( $first_tab_atts );
			}
			?>
		</div>
		<?php
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
}

if ( ! function_exists( 'woodmart_shortcode_products_tab' ) ) {
	function woodmart_shortcode_products_tab( $atts ) {
		global $wpdb, $post;

		$output = $class = '';

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );

		$parsed_atts = shortcode_atts(
			array_merge(
				array(
					'title'     => '',
					'icon'      => '',
					'icon_size' => '',
				),
				woodmart_get_default_product_shortcode_atts()
			),
			$atts
		);

		extract( $parsed_atts );

		$parsed_atts['force_not_ajax']  = 'yes';
		$parsed_atts['wrapper_classes'] = ' wd-tab-content';

		if ( ! $is_ajax ) {
			$parsed_atts['wrapper_classes'] .= ' wd-active wd-in';
		}

		$class .= woodmart_get_old_classes( ' woodmart-tab-content' );

		ob_start();
		?>
		<?php if ( ! $is_ajax ) : ?>
			<div class="wd-tabs-content-wrapper<?php echo esc_attr( $class ); ?>" >
			<?php woodmart_sticky_loader(); ?>
		<?php endif; ?>
		
		<?php
		echo woodmart_shortcode_products( $parsed_atts );
		?>
		<?php if ( ! $is_ajax ) : ?>
			</div>
		<?php endif; ?>
		<?php
		$output = ob_get_clean();

		if ( $is_ajax ) {
			$output = array(
				'html' => $output,
			);
		}

		return $output;
	}
}
