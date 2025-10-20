<?php
/**
 * Estimate delivery class.
 *
 * @package woodmart
 */

namespace XTS\Modules\Estimate_Delivery;

use XTS\Admin\Modules\Options;

/**
 * Estimate delivery class.
 */
class Main {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_options' ) );

		woodmart_include_files(
			__DIR__,
			array(
				'./class-manager',
				'./class-delivery-date',
				'./class-overal-delivery-date',
				'./class-admin',
				'./class-frontend',
			)
		);
	}

	/**
	 * Add options in theme settings.
	 *
	 * @return void
	 */
	public function add_options() {
		Options::add_field(
			array(
				'id'          => 'estimate_delivery_enabled',
				'name'        => esc_html__( 'Enable "Estimate Delivery"', 'woodmart' ),
				'hint'     => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'estimate-delivery-show-on-single-product.jpg" alt="">', true ),
				'description' => esc_html__( 'The option allows you to display the expected delivery date for orders. When this option is enabled, customers can see the estimated delivery dates.', 'woodmart' ),
				'type'        => 'switcher',
				'section'     => 'estimate_delivery_section',
				'default'     => false,
				'on-text'     => esc_html__( 'Yes', 'woodmart' ),
				'off-text'    => esc_html__( 'No', 'woodmart' ),
				'priority'    => 10,
				'class'       => 'xts-preset-field-disabled',
			)
		);

		Options::add_field(
			array(
				'id'       => 'estimate_delivery_show_on_single_product',
				'name'     => esc_html__( 'Single Product', 'woodmart' ),
				'hint'     => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'estimate-delivery-show-on-single-product.jpg" alt="">', true ),
				'group'    => esc_html__( 'Locations', 'woodmart' ),
				'type'     => 'switcher',
				'section'  => 'estimate_delivery_section',
				'default'  => true,
				'on-text'  => esc_html__( 'On', 'woodmart' ),
				'off-text' => esc_html__( 'Off', 'woodmart' ),
				'priority' => 20,
				'class'    => 'xts-col-6',
			)
		);

		Options::add_field(
			array(
				'id'       => 'estimate_delivery_show_on_mini_cart',
				'name'     => esc_html__( 'Mini cart', 'woodmart' ),
				'hint'     => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'estimate-delivery-show-on-mini-cart.jpg" alt="">', true ),
				'group'    => esc_html__( 'Locations', 'woodmart' ),
				'type'     => 'switcher',
				'section'  => 'estimate_delivery_section',
				'default'  => false,
				'on-text'  => esc_html__( 'On', 'woodmart' ),
				'off-text' => esc_html__( 'Off', 'woodmart' ),
				'priority' => 30,
				'class'    => 'xts-col-6',
			)
		);

		Options::add_field(
			array(
				'id'       => 'estimate_delivery_show_on_cart_page',
				'name'     => esc_html__( 'Cart page', 'woodmart' ),
				'hint'     => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'estimate-delivery-show-on-cart-page.jpg" alt="">', true ),
				'group'    => esc_html__( 'Locations', 'woodmart' ),
				'type'     => 'switcher',
				'section'  => 'estimate_delivery_section',
				'default'  => true,
				'on-text'  => esc_html__( 'On', 'woodmart' ),
				'off-text' => esc_html__( 'Off', 'woodmart' ),
				'priority' => 40,
				'class'    => 'xts-col-6',
			)
		);

		Options::add_field(
			array(
				'id'       => 'estimate_delivery_show_on_checkout_page',
				'name'     => esc_html__( 'Checkout page', 'woodmart' ),
				'hint'     => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'estimate-delivery-show-on-checkout-page.jpg" alt="">', true ),
				'group'    => esc_html__( 'Locations', 'woodmart' ),
				'type'     => 'switcher',
				'section'  => 'estimate_delivery_section',
				'default'  => false,
				'on-text'  => esc_html__( 'On', 'woodmart' ),
				'off-text' => esc_html__( 'Off', 'woodmart' ),
				'priority' => 50,
				'class'    => 'xts-col-6',
			)
		);

		Options::add_field(
			array(
				'id'       => 'estimate_delivery_show_on_order_details',
				'name'     => esc_html__( 'Order details', 'woodmart' ),
				'hint'     => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'estimate-delivery-show-on-order-details.jpg" alt="">', true ),
				'group'    => esc_html__( 'Locations', 'woodmart' ),
				'type'     => 'switcher',
				'section'  => 'estimate_delivery_section',
				'default'  => true,
				'on-text'  => esc_html__( 'On', 'woodmart' ),
				'off-text' => esc_html__( 'Off', 'woodmart' ),
				'priority' => 60,
				'class'    => 'xts-col-6',
			)
		);

		Options::add_field(
			array(
				'id'       => 'estimate_delivery_show_on_email_order',
				'name'     => esc_html__( 'Order received email', 'woodmart' ),
				'hint'     => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'estimate-delivery-show-on-email-order.jpg" alt="">', true ),
				'group'    => esc_html__( 'Locations', 'woodmart' ),
				'type'     => 'switcher',
				'section'  => 'estimate_delivery_section',
				'default'  => true,
				'on-text'  => esc_html__( 'On', 'woodmart' ),
				'off-text' => esc_html__( 'Off', 'woodmart' ),
				'priority' => 70,
				'class'    => 'xts-col-6',
			)
		);

		Options::add_field(
			array(
				'id'          => 'estimate_delivery_show_overall',
				'name'        => esc_html__( 'Overall delivery dates', 'woodmart' ),
				'description' => esc_html__( 'Display delivery dates common to all products in the cart on the cart and checkout pages.', 'woodmart' ),
				'hint'        => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'estimate-delivery-show-overall.jpg" alt="">', true ),
				'group'       => esc_html__( 'Settings', 'woodmart' ),
				'type'        => 'switcher',
				'section'     => 'estimate_delivery_section',
				'default'     => false,
				'on-text'     => esc_html__( 'On', 'woodmart' ),
				'off-text'    => esc_html__( 'Off', 'woodmart' ),
				'priority'    => 80,
				'class'       => 'xts-col-6',
			)
		);

		Options::add_field(
			array(
				'id'       => 'estimate_delivery_date_format',
				'name'     => esc_html__( 'Date format', 'woodmart' ),
				'group'    => esc_html__( 'Settings', 'woodmart' ),
				'type'     => 'select',
				'section'  => 'estimate_delivery_section',
				'callback' => array( $this, 'get_date_format_options' ),
				'default'  => 'default',
				'priority' => 90,
				'class'    => 'xts-col-6',
			)
		);

		Options::add_field(
			array(
				'id'          => 'estimate_delivery_fragments_enable',
				'name'        => esc_html__( 'Enable fragments updating', 'woodmart' ),
				'description' => esc_html__( 'Enable this option to ensure that the estimated delivery date is correctly updated when caching is enabled.', 'woodmart' ),
				'group'       => esc_html__( 'Settings', 'woodmart' ),
				'type'        => 'switcher',
				'section'     => 'estimate_delivery_section',
				'default'     => false,
				'on-text'     => esc_html__( 'Yes', 'woodmart' ),
				'off-text'    => esc_html__( 'No', 'woodmart' ),
				'priority'    => 100,
				'class'       => 'xts-col-6',
			)
		);
	}

	/**
	 * Get options value for estimate_delivery_date_format option.
	 *
	 * @return array
	 */
	public function get_date_format_options() {
		$formats = array(
			'Y/m/d',
			'd/m/Y',
			'm/d/y',
			'm/d/Y',
			'Y-m-d',
			'd-m-Y',
			'm-d-y',
			'Y.m.d',
			'd.m.Y',
			'm.d.y',
			'D, d.m.',
			'F j, Y',
			'M j, Y',
			'jS \of F',
			'jS F',
			'j. F',
			'l j. F',
			'F jS',
			'jS M',
			'M jS',
		);

		$now     = time();
		$options = array(
			'default' => array(
				'name'  => esc_html__( 'From WordPress settings', 'woodmart' ),
				'value' => 'default',
			),
		);

		foreach ( $formats as $format ) {
			$date = wp_date( $format, $now );

			$options[ $format ] = array(
				'name'  => $date,
				'value' => $format,
			);
		}

		return $options;
	}
}

new Main();
