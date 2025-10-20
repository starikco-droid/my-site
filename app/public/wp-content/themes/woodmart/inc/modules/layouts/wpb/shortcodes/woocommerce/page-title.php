<?php
/**
 * Page title shortcode.
 *
 * @package Woodmart
 */

use XTS\Modules\Layouts\Global_Data as Builder_Data;
use XTS\Modules\Layouts\Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

if ( ! function_exists( 'woodmart_shortcode_page_title' ) ) {
	/**
	 * Page title shortcode.
	 *
	 * @param array $settings Shortcode attributes.
	 */
	function woodmart_shortcode_page_title( $settings ) {
		$default_settings = array(
			'css'                => '',
			'enable_title'       => 'yes',
			'enable_breadcrumbs' => 'yes',
			'enable_categories'  => 'yes',
		);

		$settings = wp_parse_args( $settings, $default_settings );

		$wrapper_classes = apply_filters( 'vc_shortcodes_css_class', '', '', $settings );

		if ( $settings['css'] ) {
			$wrapper_classes .= ' ' . vc_shortcode_custom_css_class( $settings['css'] );
		}

		ob_start();

		Builder_Data::get_instance()->set_data( 'builder', true );
		Builder_Data::get_instance()->set_data( 'layout_id', get_the_ID() );
		Builder_Data::get_instance()->set_data( 'is_post_layout', Main::get_instance()->has_custom_layout( 'single_post' ) );

		Main::setup_preview();

		woodmart_enqueue_inline_style( 'el-page-title-builder' );

		if ( is_product_taxonomy() || woodmart_is_shop_archive() ) {
			woodmart_enqueue_inline_style( 'woo-shop-page-title' );

			if ( ! woodmart_get_opt( 'shop_title' ) ) {
				woodmart_enqueue_inline_style( 'woo-shop-opt-without-title' );
			}

			if ( woodmart_get_opt( 'shop_categories' ) ) {
				woodmart_enqueue_inline_style( 'shop-title-categories' );
				woodmart_enqueue_inline_style( 'woo-categories-loop-nav-mobile-accordion' );
			}
		}

		?>
		<div class="wd-page-title-el wd-wpb<?php echo esc_attr( $wrapper_classes ); ?>">
			<?php woodmart_page_title(); ?>
		</div>
		<?php

		Main::restore_preview();

		return ob_get_clean();
	}
}
