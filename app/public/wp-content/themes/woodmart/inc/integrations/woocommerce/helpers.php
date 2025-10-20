<?php if ( ! defined( 'WOODMART_THEME_DIR' ) ) {
	exit( 'No direct script access allowed' );
}

if ( ! function_exists( 'woodmart_get_widget_title_tag' ) ) {
	/**
	 * Get widget title tag
	 */
	function woodmart_get_widget_title_tag() {
		return woodmart_get_opt( 'widget_title_tag', 'h5' );
	}
}

if ( ! function_exists( 'woodmart_is_new_label_needed' ) ) {
	function woodmart_is_new_label_needed( $product_id ) {
		$product = wc_get_product( $product_id );

		if ( ! $product_id || ! $product ) {
			return false;
		}

		$date         = get_post_meta( $product_id, '_woodmart_new_label_date', true );
		$new          = get_post_meta( $product_id, '_woodmart_new_label', true );
		$newness_days = woodmart_get_opt( 'new_label_days_after_create' );
		$date_created = $product->get_date_created();
		$created      = $date_created ? strtotime( $date_created ) : 0;

		if ( $new ) {
			return true;
		}

		if ( $date && time() <= strtotime( $date ) ) {
			return true;
		}

		if ( $newness_days && ( time() - ( 60 * 60 * 24 * $newness_days ) ) < $created ) {
			return true;
		}

		return false;
	}
}

/**
 * ------------------------------------------------------------------------------------------------
 * Add theme support for WooCommerce
 * ------------------------------------------------------------------------------------------------
 */

add_theme_support( 'woocommerce' );
add_theme_support( 'wc-product-gallery-zoom' );


/**
 * ------------------------------------------------------------------------------------------------
 * Check if WooCommerce is active
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_woocommerce_installed' ) ) {
	function woodmart_woocommerce_installed() {
		return class_exists( 'WooCommerce' );
	}
}

/**
 * ------------------------------------------------------------------------------------------------
 * Check if Js composer is active
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_js_composer_installed' ) ) {
	function woodmart_js_composer_installed() {
		return class_exists( 'Vc_Manager' );
	}
}

/**
 * ------------------------------------------------------------------------------------------------
 * is ajax request
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_is_woo_ajax' ) ) {
	function woodmart_is_woo_ajax() {
		$request_headers      = function_exists( 'getallheaders' ) ? getallheaders() : array();
		$exclude_ajax_actions = array(
			'woodmart_load_html_dropdowns',
			'elementor_ajax',
		);

		if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], $exclude_ajax_actions ) ) { // phpcs:ignore
			return false;
		}

		if ( woodmart_is_elementor_installed() && ( function_exists( 'woodmart_elementor_is_edit_mode' ) && woodmart_elementor_is_edit_mode() ) ) {
			return apply_filters( 'xts_is_ajax', false );
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return 'wp-ajax';
		}

		if ( isset( $request_headers['x-pjax'] ) || isset( $request_headers['X-PJAX'] ) || isset( $request_headers['X-Pjax'] ) ) {
			return 'full-page';
		}

		if ( isset( $_REQUEST['woo_ajax'] ) ) {
			return 'fragments';
		}

		if ( woodmart_is_pjax() ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'woodmart_is_pjax' ) ) {
	function woodmart_is_pjax() {
		$request_headers = function_exists( 'getallheaders' ) ? getallheaders() : array();

		return isset( $_REQUEST['_pjax'] ) && ( ( isset( $request_headers['X-Requested-With'] ) && 'xmlhttprequest' === strtolower( $request_headers['X-Requested-With'] ) ) || ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && 'xmlhttprequest' === strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) );
	}
}

if ( ! function_exists( 'woodmart_get_current_term_id' ) ) {
	/**
	 * FIX CMB2 bug
	 */
	function woodmart_get_current_term_id() {
		return isset( $_REQUEST['tag_ID'] ) ? $_REQUEST['tag_ID'] : 0;
	}
}

/**
 * ------------------------------------------------------------------------------------------------
 * Determine is it product attribute archive page
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_is_product_attribute_archive' ) ) {
	function woodmart_is_product_attribute_archive() {
		$queried_object = get_queried_object();
		if ( $queried_object && property_exists( $queried_object, 'taxonomy' ) ) {
			$taxonomy = $queried_object->taxonomy;
			return substr( $taxonomy, 0, 3 ) == 'pa_';
		}
		return false;
	}
}

if ( ! function_exists( 'woodmart_is_woocommerce_legacy_rest_api' ) ) {
	/**
	 * This function checked is woocommerce legacy rest api.
	 *
	 * @return bool
	 */
	function woodmart_is_woocommerce_legacy_rest_api() {
		if ( ! empty( $_SERVER['REQUEST_URI'] ) && 'yes' === get_option( 'woocommerce_api_enabled' ) && false !== strpos( $_SERVER['REQUEST_URI'], 'wc-api' ) ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'woodmart_is_shop_on_front' ) ) {
	/**
	 * Check if shop page is set as front page.
	 *
	 * @return bool
	 */
	function woodmart_is_shop_on_front() {
		return function_exists( 'wc_get_page_id' ) && 'page' === get_option( 'show_on_front' ) && wc_get_page_id( 'shop' ) == get_option( 'page_on_front' );
	}
}

if ( ! function_exists( 'woodmart_attachment_url_to_path' ) ) {
	/**
	 * Get the attachment absolute path from its url.
	 *
	 * @param string $url The attachment url to get its absolute path.
	 *
	 * @return bool|string It returns the absolute path of an attachment.
	 */
	function woodmart_attachment_url_to_path( $url ) {
		$parsed_url = parse_url( $url );

		if ( empty( $parsed_url['path'] ) ) {
			return false;
		}

		$file = ABSPATH . ltrim( $parsed_url['path'], '/' );

		if ( file_exists( $file ) ) {
			return $file;
		}

		return false;
	}
}

if ( ! function_exists( 'woodmart_is_old_category_structure' ) ) {
	/**
	 * Check if the category design refers to the old structure.
	 *
	 * @param string $category_design The design of the category that needs to be checked.
	 *
	 * @return bool
	 */
	function woodmart_is_old_category_structure( $category_design ) {
		$old_categories_designs = array(
			'default',
			'alt',
			'center',
			'replace-title',
		);

		return in_array( $category_design, $old_categories_designs, true );
	}
}

if ( ! function_exists( 'woodmart_is_shop_archive' ) ) {
	/**
	 * Check if current page is shop archive.
	 *
	 * @return bool
	 */
	function woodmart_is_shop_archive() {
		return woodmart_woocommerce_installed() && ( is_shop() || is_product_category() || is_product_tag() || is_tax( 'product_brand' ) || woodmart_is_product_attribute_archive() );
	}
}

if ( ! function_exists( 'woodmart_is_email_preview_request' ) ) {
	/**
	 * Check if the current request is an email preview request.
	 */
	function woodmart_is_email_preview_request() {
		$is_email_preview_request = false;

		if ( wc()->is_rest_api_request() ) {
			$nonce = isset( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : false; // phpcs:ignore.

			$is_email_preview_request = wp_verify_nonce( $nonce, 'email-preview-nonce' );
		}

		return isset( $_GET['preview_woocommerce_mail'] ) || $is_email_preview_request;
	}
}

if ( ! function_exists( 'woodmart_sort_data' ) ) {
	/**
	 * Sort the array by the specified key.
	 * This function is just wrapper for usort function.
	 *
	 * @param array  &$data The input array.
	 * @param string $order_by The name of the key by which the sorting will be performed.
	 * @param string $order Sorting order.
	 *
	 * @return void
	 */
	function woodmart_sort_data( &$data, $order_by, $order ) {
		usort(
			$data,
			function ( $a, $b ) use ( $order_by, $order ) {
				$a_value = isset( $a[ $order_by ] ) ? $a[ $order_by ] : '';
				$b_value = isset( $b[ $order_by ] ) ? $b[ $order_by ] : '';

				if ( is_numeric( $a_value ) && is_numeric( $b_value ) ) {
					$result = $a_value - $b_value;
				} else {
					$result = strcmp( $a_value, $b_value );
				}

				return ( 'asc' === $order ) ? $result : -$result;
			}
		);
	}
}

if ( ! function_exists( 'woodmart_include_files' ) ) {
	/**
	 * Include module files.
	 *
	 * @param string $module_dir The module directory.
	 * @param array  $files List of files to include.
	 */
	function woodmart_include_files( $module_dir, $files ) {
		if ( empty( $files ) || ! is_array( $files ) ) {
			return;
		}

		foreach ( $files as $file ) {
			$path = $file;

			if ( 0 === strpos( $file, './' ) ) {
				$path = $module_dir . '/' . ltrim( substr( $file, 2 ), '/' ) . '.php';
			}

			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}
	}
}
