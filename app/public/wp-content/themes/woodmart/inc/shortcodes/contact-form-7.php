<?php
/**
 * contact_form_7 shortcode.
 *
 * @package Elements
 */

if ( ! defined( 'WOODMART_THEME_DIR' ) ) {
	exit( 'No direct script access allowed' );
}

if ( ! function_exists( 'woodmart_shortcode_contact_form_7' ) ) {
	/**
	 * Render contact_form_7 shortcode.
	 *
	 * @param array  $settings Shortcode attributes.
	 * @param string $content Inner content (shortcode).
	 *
	 * @return false|string
	 */
	function woodmart_shortcode_contact_form_7( $settings, $content ) {
		$settings = wp_parse_args(
			$settings,
			array(
				'css'     => '',
				'form_id' => '0',
			)
		);

		$wrapper_classes  = 'wd-cf7 wd-wpb';
		$wrapper_classes .= apply_filters( 'vc_shortcodes_css_class', '', '', $settings ); // phpcs:ignore.

		if ( function_exists( 'vc_shortcode_custom_css_class' ) && ! empty( $settings['css'] ) ) {
			$wrapper_classes .= ' ' . vc_shortcode_custom_css_class( $settings['css'] );
		}

		woodmart_enqueue_inline_style( 'wpcf7', true );

		if ( defined( 'WPCF7_PLUGIN' ) && function_exists( 'wpcf7_enqueue_scripts' ) && ! wp_script_is( 'contact-form-7', 'registered' ) ) {
			$assets = include wpcf7_plugin_path( 'includes/js/index.asset.php' );

			$assets = wp_parse_args(
				$assets,
				array(
					'dependencies' => array(),
					'version'      => WPCF7_VERSION,
				)
			);

			wp_register_script(
				'contact-form-7',
				wpcf7_plugin_url( 'includes/js/index.js' ),
				array_merge(
					$assets['dependencies'],
					array( 'swv' )
				),
				$assets['version'],
				array( 'in_footer' => true )
			);

			wpcf7_enqueue_scripts();
		}

		ob_start();
		?>
		<?php if ( ! $settings['form_id'] || ! defined( 'WPCF7_PLUGIN' ) ) : ?>
			<div class="wd-notice wd-info">
				<?php echo esc_html__( 'You need to create a form using Contact form 7 plugin to be able to display it using this element.', 'woodmart' ); ?>
			</div>
		<?php else : ?>
			<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
				<?php echo do_shortcode( '[contact-form-7 id="' . esc_attr( $settings['form_id'] ) . '"]' ); ?>
			</div>
		<?php endif; ?>
		<?php

		return ob_get_clean();
	}
}
