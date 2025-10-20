<?php
/**
 * WooCommerce hook shortcode.
 *
 * @package Woodmart
 */

use XTS\Modules\Layouts\Main;
use XTS\WC_Wishlist\Ui as Wishlist;
use XTS\Modules\Compare\Ui as Compare;
use XTS\Modules\Linked_Variations\Frontend as Linked_Variations;
use XTS\Modules\Shipping_Progress_Bar\Frontend as Shipping_Progress_Bar;
use XTS\Modules\Visitor_Counter\Main as Visitor_Counter;
use XTS\Modules\Sold_Counter\Main as Sold_Counter;
use XTS\Modules\Estimate_Delivery\Frontend as Estimate_Delivery_Frontend;
use XTS\Modules\Dynamic_Discounts\Frontend as Dynamic_Discounts_Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

if ( ! function_exists( 'woodmart_shortcode_woocommerce_hook' ) ) {
	/**
	 * WooCommerce hook shortcode.
	 *
	 * @param array $settings Shortcode attributes.
	 */
	function woodmart_shortcode_woocommerce_hook( $settings ) {
		$default_settings = array(
			'clean_actions' => 'yes',
			'css'           => '',
			'hook'          => '0',
		);

		$settings = wp_parse_args( $settings, $default_settings );

		$settings['hook'] = woodmart_vc_get_control_data( $settings['hook'], 'desktop' );

		if ( '0' === $settings['hook'] ) {
			return '';
		}

		$wrapper_classes = apply_filters( 'vc_shortcodes_css_class', '', '', $settings );

		if ( $settings['css'] ) {
			$wrapper_classes .= ' ' . vc_shortcode_custom_css_class( $settings['css'] );
		}

		ob_start();

		Main::setup_preview();

		if ( 'yes' === $settings['clean_actions'] ) {
			if ( 'woocommerce_checkout_billing' === $settings['hook'] ) {
				remove_action( 'woocommerce_checkout_billing', array( WC()->checkout(), 'checkout_form_billing' ) );

				if ( woodmart_get_opt( 'shipping_progress_bar_enabled' ) ) {
					remove_action( 'woocommerce_checkout_billing', array( Shipping_Progress_Bar::get_instance(), 'render_shipping_progress_bar_with_wrapper' ) );
				}
			} elseif ( 'woocommerce_checkout_shipping' === $settings['hook'] ) {
				remove_action( 'woocommerce_checkout_shipping', array( WC()->checkout(), 'checkout_form_shipping' ) );
			} elseif ( 'woocommerce_checkout_before_customer_details' === $settings['hook'] ) {
				remove_action( 'woocommerce_checkout_before_customer_details', 'wc_get_pay_buttons', 30 );
			} elseif ( 'woocommerce_before_checkout_form' === $settings['hook'] ) {
				remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
				remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
				remove_action( 'woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10 );
			} elseif ( 'woocommerce_cart_collaterals' === $settings['hook'] ) {
				remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
				remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 20 );
				remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );
			} elseif ( 'woocommerce_before_cart' === $settings['hook'] ) {
				remove_action( 'woocommerce_before_cart', 'woocommerce_output_all_notices', 10 );
			} elseif ( 'woocommerce_before_single_product' === $settings['hook'] ) {
				remove_action( 'woocommerce_before_single_product', 'woocommerce_output_all_notices' );
				remove_action( 'woocommerce_before_single_product', 'wc_print_notices' );
				remove_action( 'woocommerce_before_single_product', 'woodmart_product_extra_content', 20 );
			} elseif ( 'woocommerce_before_single_product_summary' === $settings['hook'] ) {
				remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash' );
				remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
			} elseif ( 'woocommerce_product_thumbnails' === $settings['hook'] ) {
				remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
			} elseif ( 'woocommerce_single_product_summary' === $settings['hook'] ) {
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
				remove_action( 'woocommerce_single_product_summary', 'woodmart_single_product_countdown', 15 );
				remove_action( 'woocommerce_single_product_summary', 'woodmart_stock_progress_bar', 16 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 60 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating' );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price' );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
				remove_action( 'woocommerce_single_product_summary', 'woodmart_product_brand', 3 );
				remove_action( 'woocommerce_single_product_summary', 'woodmart_product_brand', 8 );
				remove_action( 'woocommerce_single_product_summary', 'woodmart_product_share_buttons', 62 );
				remove_action( 'woocommerce_single_product_summary', 'woodmart_display_product_attributes', 21 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_loop_add_to_cart', 30 );
				remove_action( 'woocommerce_single_product_summary', 'woodmart_sguide_display', 38 );
				remove_action( 'woocommerce_single_product_summary', 'woodmart_before_add_to_cart_area', 25 );
				remove_action( 'woocommerce_single_product_summary', 'woodmart_after_add_to_cart_area', 31 );
				remove_action( 'woocommerce_single_product_summary', array( $GLOBALS['woocommerce']->structured_data, 'generate_product_data' ), 60 );

				if ( woodmart_get_opt( 'wishlist' ) ) {
					remove_action( 'woocommerce_single_product_summary', array( Wishlist::get_instance(), 'add_to_wishlist_single_btn' ), 33 );
				}
				if ( woodmart_get_opt( 'compare' ) ) {
					remove_action( 'woocommerce_single_product_summary', array( Compare::get_instance(), 'add_to_compare_single_btn' ), 33 );
				}
				if ( woodmart_get_opt( 'linked_variations' ) ) {
					remove_action( 'woocommerce_single_product_summary', array( Linked_Variations::get_instance(), 'output' ), 25 );
				}
				if ( woodmart_get_opt( 'counter_visitor_enabled' ) ) {
					remove_action( 'woocommerce_single_product_summary', array( Visitor_Counter::get_instance(), 'output_count_visitors' ), 39 );
				}
				if ( woodmart_get_opt( 'sold_counter_enabled' ) ) {
					remove_action( 'woocommerce_single_product_summary', array( Sold_Counter::get_instance(), 'render' ), 25 );
				}
				if ( woodmart_get_opt( 'estimate_delivery_enabled' ) && woodmart_get_opt( 'estimate_delivery_show_on_single_product' ) ) {
					remove_action( 'woocommerce_single_product_summary', array( Estimate_Delivery_Frontend::get_instance(), 'render_on_single_product' ), 39 );
				}
				if ( woodmart_get_opt( 'discounts_enabled' ) && woodmart_get_opt( 'show_discounts_table' ) ) {
					remove_action( 'woocommerce_single_product_summary', array( Dynamic_Discounts_Frontend::get_instance(), 'render_dynamic_discounts_table' ), 25 );
				}
			} elseif ( 'woocommerce_before_add_to_cart_form' === $settings['hook'] ) {
				remove_action( 'woocommerce_before_add_to_cart_form', 'woodmart_single_product_add_to_cart_scripts' );
			} elseif ( 'woocommerce_before_variations_form' === $settings['hook'] ) {
				remove_action( 'woocommerce_before_variations_form', 'woocommerce_single_variation' );
			} elseif ( 'woocommerce_single_variation' === $settings['hook'] ) {
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation' );
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
				remove_action( 'woocommerce_before_variations_form', 'woocommerce_single_variation' );
			} elseif ( 'woocommerce_after_single_product_summary' === $settings['hook'] ) {
				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs' );
				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
				remove_action( 'woocommerce_after_single_product_summary', 'woodmart_wc_comments_template', 50 );
			} elseif ( 'woocommerce_checkout_order_review' === $settings['hook'] ) {
				remove_action( 'woocommerce_checkout_order_review', 'woodmart_open_table_wrapper_div', 7 );
				remove_action( 'woocommerce_checkout_order_review', 'woodmart_close_table_wrapper_div', 13 );
				remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
				remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 20 );
				remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
				remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 10 );
			} elseif ( 'woocommerce_order_details_after_order_table' === $settings['hook'] ) {
				remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' );
			} elseif ( 'woocommerce_thankyou' === $settings['hook'] ) {
				remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table' );
			} elseif ( 'woocommerce_before_customer_login_form' === $settings['hook'] ) {
				remove_action( 'woocommerce_before_customer_login_form', 'woocommerce_output_all_notices' );
			} elseif ( 'woocommerce_register_form' === $settings['hook'] ) {
				remove_action( 'woocommerce_register_form', 'wc_registration_privacy_policy_text', 20 );
			} elseif ( 'woocommerce_before_lost_password_form' === $settings['hook'] ) {
				remove_action( 'woocommerce_before_lost_password_form', 'woocommerce_output_all_notices' );
			}
		}

		if ( ! has_action( $settings['hook'] ) ) {
			Main::restore_preview();

			return '';
		}

		?>
		<div class="wd-wpb<?php echo esc_attr( $wrapper_classes ); ?>"><?php // Must be in one line.
			if ( 'woocommerce_before_checkout_form' === $settings['hook'] || 'woocommerce_after_checkout_form' === $settings['hook'] ) {
				do_action( $settings['hook'], WC()->checkout() );
			} elseif ( in_array( $settings['hook'], array( 'woocommerce_thankyou', 'woocommerce_before_thankyou', 'woocommerce_order_details_after_order_table' ), true ) ) {
				$order_id = (int) get_query_var( 'order-received' );
				$order    = $order_id ? wc_get_order( $order_id ) : '';
				if ( $order ) {
					if ( 'woocommerce_order_details_after_order_table' === $settings['hook'] ) {
						do_action( $settings['hook'], $order );
					} else {
						do_action( $settings['hook'], $order_id );
					}
				}
			} else {
				do_action( $settings['hook'] );
			}
		?></div>
		<?php

		Main::restore_preview();

		return ob_get_clean();
	}
}
