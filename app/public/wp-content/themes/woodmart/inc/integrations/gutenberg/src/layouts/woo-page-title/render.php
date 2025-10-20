<?php

use XTS\Modules\Layouts\Global_Data as Builder_Data;
use XTS\Modules\Layouts\Main;

if ( ! function_exists( 'wd_gutenberg_woo_page_title' ) ) {
	function wd_gutenberg_woo_page_title( $block_attributes ) {
		$classes = wd_get_gutenberg_element_classes( $block_attributes );

		if ( ! empty( $block_attributes['stretch'] ) ) {
			$classes .= ' wd-stretched';
		}

		Builder_Data::get_instance()->set_data( 'is_post_layout', Main::get_instance()->has_custom_layout( 'single_post' ) );

		Main::setup_preview();

		Builder_Data::get_instance()->set_data( 'builder', true );
		Builder_Data::get_instance()->set_data( 'layout_id', get_the_ID() );

		ob_start();

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
		<div id="<?php echo esc_attr( wd_get_gutenberg_element_id( $block_attributes ) ); ?>" class="wd-page-title-el<?php echo esc_attr( $classes ); ?>">
			<?php woodmart_page_title(); ?>
		</div>
		<?php
		Main::restore_preview();

		return ob_get_clean();
	}
}
