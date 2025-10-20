<?php
/**
 * Estimate delivery class.
 *
 * @package woodmart
 */

namespace XTS\Modules\Estimate_Delivery;

use XTS\Singleton;
use WC_Shipping_Zones;
use WC_Product;

/**
 * Estimate delivery class.
 */
class Manager extends Singleton {
	/**
	 * Default ist of meta box arguments for rendering template.
	 *
	 * @var array $meta_boxes_fields List of meta box arguments for rendering template.
	 */
	private $meta_boxes_fields_keys = array();

	/**
	 * Transient name for 'All Estimate Delivery post ids'.
	 *
	 * @var string $transient_est_del_ids .
	 */
	public $transient_est_del_ids = 'wd_transient_est_del_ids';

	/**
	 * Transient name for 'Single estimate discount rule'. Has a dynamic part at the end of the name '_${est_del_id}'.
	 *
	 * @var string $transient_est_del_rule .
	 */
	public $transient_est_del_rule = 'wd_transient_est_del_rule';

	/**
	 * Constructor.
	 */
	public function init() {}

	/**
	 * Set default list of meta box arguments for rendering template.
	 *
	 * @param array $meta_boxes_fields_keys List of default meta boxes fields.
	 *
	 * @return void
	 */
	public function set_meta_boxes_fields_keys( $meta_boxes_fields_keys ) {
		$this->meta_boxes_fields_keys = $meta_boxes_fields_keys;
	}

	/**
	 * Get default list of meta box arguments for rendering template.
	 *
	 * @return array List of meta box arguments for rendering template.
	 */
	public function get_meta_boxes_fields_keys() {
		return $this->meta_boxes_fields_keys;
	}

	/**
	 * Get list of estimate delivery post ids.
	 *
	 * @return int[]
	 */
	public function get_all_rule_posts_ids() {
		$cache = get_transient( $this->transient_est_del_ids );

		if ( $cache ) {
			return $cache;
		}

		$all_est_del_post_ids = get_posts(
			array(
				'fields'         => 'ids',
				'posts_per_page' => apply_filters( 'woodmart_est_del_rule_limit', 100 ),
				'post_type'      => 'wd_woo_est_del',
				'post_status'    => 'publish',
			)
		);

		set_transient( $this->transient_est_del_ids, $all_est_del_post_ids );

		return $all_est_del_post_ids;
	}

	/**
	 * Get list of meta box arguments for single estimate delivery post.
	 *
	 * @param int|string $id Estimate delivery post id.
	 *
	 * @return array List of meta box arguments.
	 */
	public function get_single_post_meta_boxes( $id = '' ) {
		if ( ! $id ) {
			$id = get_the_ID();
		}

		$cache = get_transient( $this->transient_est_del_rule . '_' . $id );

		if ( $cache ) {
			return $cache;
		}

		$meta_boxes_keys    = $this->get_meta_boxes_fields_keys();
		$current_meta_boxes = array();

		foreach ( $meta_boxes_keys as $meta_box_id ) {
			$meta_box_value = maybe_unserialize( get_post_meta( $id, $meta_box_id, true ) );

			if ( 'est_del_shipping_method' === $meta_box_id && is_array( $meta_box_value ) ) {
				$meta_box_value = array_filter( $meta_box_value );
			}

			$current_meta_boxes[ $meta_box_id ] = $meta_box_value;
		}

		set_transient( $this->transient_est_del_rule . '_' . $id, $current_meta_boxes );

		return $current_meta_boxes;
	}

	/**
	 * Helper for check product stock status.
	 *
	 * @param WC_Product $product Instance of WC_Product class.
	 *
	 * @return bool
	 */
	public function check_product_in_stock_on_single_page( $product ) {
		$product_in_stock = $product->is_in_stock();

		if ( $product->is_type( 'variable' ) ) {
			$variations_ids = $product->get_children();
			$var_in_stock   = array_filter(
				$variations_ids,
				function ( $id ) {
					$variation = wc_get_product( $id );

					return $variation instanceof WC_Product && $variation->is_in_stock();
				}
			);

			$product_in_stock = ! empty( $var_in_stock );
		}

		return $product_in_stock;
	}

	/**
	 * Helper get rules.
	 *
	 * @return array A list of rules where those that are not empty est_del_shipping_method go first.
	 */
	public function group_rules_by_shipping_method() {
		$rule_ids = $this->get_all_rule_posts_ids();

		if ( empty( $rule_ids ) ) {
			return array();
		}

		$rules_with_shipping_method    = array();
		$rules_without_shipping_method = array();

		foreach ( $rule_ids as $id ) {
			$rule = $this->get_single_post_meta_boxes( $id );

			if ( empty( $rule['est_del_priority'] ) ) {
				$rule['est_del_priority'] = 1;
			}

			$rule['key'] = $id;

			if ( ! empty( $rule['est_del_shipping_method'] ) ) {
				$rules_with_shipping_method[ $id ] = $rule;
			} else {
				$rules_without_shipping_method[ $id ] = $rule;
			}
		}

		uasort( $rules_with_shipping_method, array( $this, 'sort_by_priority' ) );
		uasort( $rules_without_shipping_method, array( $this, 'sort_by_priority' ) );

		return array_merge( $rules_with_shipping_method, $rules_without_shipping_method );
	}

	/**
	 * Get delivery rules.
	 *
	 * @param WC_Product $product WC_Product instance.
	 * @param int|false  $shipping_method_id Shipping method id for calculate date on admin panel.
	 *
	 * @return array List of meta box arguments.
	 */
	public function get_rule_for_product( $product, $shipping_method_id = false ) {
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$product_in_stock = is_single() || is_ajax() ? $this->check_product_in_stock_on_single_page( $product ) : true;
		$ignore           = ! $product->exists() || ! $product->is_purchasable() || ! $product_in_stock || $product->is_type( 'external' ) || $product->is_virtual();

		if ( apply_filters( 'woodmart_est_del_ignore', $ignore, $product ) ) {
			return array();
		}

		$user_method = $shipping_method_id ? $shipping_method_id : $this->get_selected_method();
		$rules       = $this->group_rules_by_shipping_method();

		if ( empty( $rules ) ) {
			return array();
		}

		foreach ( $rules as $id => $rule ) {
			if ( ! is_array( $rule['est_del_shipping_method'] ) ) {
				$rule['est_del_shipping_method'] = array( $rule['est_del_shipping_method'] );
			}

			if (
				( ! empty( array_filter( $rule['est_del_shipping_method'] ) ) && ( ! $user_method || ( ! in_array( $user_method, $rule['est_del_shipping_method'], true ) ) ) )
				|| ( is_array( $rule['est_del_skipped_date'] ) && 7 === count( $rule['est_del_skipped_date'] ) )
				|| ! $this->check_condition( $rule, $product )
			) {
				continue;
			}

			return $rule;
		}
	}

	/**
	 * Check condition.
	 *
	 * @param array      $rule List of meta box arguments.
	 * @param WC_Product $product The product object for which you need to check rules.
	 *
	 * @return bool
	 */
	public function check_condition( $rule, $product ) {
		$conditions = $rule['est_del_condition'];
		$is_active  = false;
		$is_exclude = false;

		if ( ! is_array( $conditions ) || empty( $conditions ) ) {
			return false;
		}

		if ( 'variation' === $product->get_type() ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		foreach ( $conditions as $id => $condition ) {
			$conditions[ $id ]['est_del_priority'] = $this->get_condition_priority( $condition['type'] );
		}

		uasort( $conditions, array( $this, 'sort_by_priority' ) );

		foreach ( $conditions as $condition ) {
			if ( isset( $condition['query'] ) ) {
				$condition['query'] = apply_filters( 'wpml_object_id', $condition['query'], $condition['type'], true, apply_filters( 'wpml_current_language', null ) );
			}

			switch ( $condition['type'] ) {
				case 'all':
					$is_active = 'include' === $condition['comparison'];

					if ( 'exclude' === $condition['comparison'] ) {
						$is_exclude = true;
					}
					break;
				case 'product':
					$is_needed_product = (int) $product->get_id() === (int) $condition['query'];

					if ( $is_needed_product ) {
						if ( 'exclude' === $condition['comparison'] ) {
							$is_active  = false;
							$is_exclude = true;
						} else {
							$is_active = true;
						}
					}

					break;
				case 'product_type':
					$is_needed_type = $product->get_type() === $condition['product-type-query'];

					if ( $is_needed_type ) {
						if ( 'exclude' === $condition['comparison'] ) {
							$is_active  = false;
							$is_exclude = true;
						} else {
							$is_active = true;
						}
					}
					break;
				case 'product_cat':
				case 'product_tag':
				case 'product_brand':
				case 'product_attr_term':
				case 'product_shipping_class':
					$terms = wp_get_post_terms( $product->get_id(), get_taxonomies(), array( 'fields' => 'ids' ) );

					if ( $terms ) {
						$is_needed_term = in_array( (int) $condition['query'], $terms, true );

						if ( $is_needed_term ) {
							if ( 'exclude' === $condition['comparison'] ) {
								$is_active  = false;
								$is_exclude = true;
							} else {
								$is_active = true;
							}
						}
					}
					break;
				case 'product_cat_children':
					$terms         = wp_get_post_terms( $product->get_id(), get_taxonomies(), array( 'fields' => 'ids' ) );
					$term_children = get_term_children( $condition['query'], 'product_cat' );

					if ( $terms ) {
						$is_needed_cat_children = count( array_diff( $terms, $term_children ) ) !== count( $terms );

						if ( $is_needed_cat_children ) {
							if ( 'exclude' === $condition['comparison'] ) {
								$is_active  = false;
								$is_exclude = true;
							} else {
								$is_active = true;
							}
						}
					}
					break;
				case 'product_stock_status':
					$is_needed_stock_status = $product->get_stock_status() === $condition['product-stock-status'];

					if ( $is_needed_stock_status ) {
						if ( 'exclude' === $condition['comparison'] ) {
							$is_active  = false;
							$is_exclude = true;
						} else {
							$is_active = true;
						}
					}
					break;
			}

			if ( $is_exclude || $is_active ) {
				break;
			}
		}

		$is_active = apply_filters( 'woodmart_check_estimate_delivery_condition', $is_active, $rule, $product );

		return $is_active;
	}

	/**
	 * Sort the conditions rule by priority DESC.
	 *
	 * @param array $a The first array to compare.
	 * @param array $b The first array to compare.
	 *
	 * @return int
	 */
	public function sort_by_priority( $a, $b ) {
		return $b['est_del_priority'] <=> $a['est_del_priority'];
	}

	/**
	 * Get condition priority;
	 *
	 * @param string $type Condition type.
	 *
	 * @return int
	 */
	public function get_condition_priority( $type ) {
		$priority = 50;

		switch ( $type ) {
			case 'all':
				$priority = 10;
				break;
			case 'product_cat_children':
				$priority = 20;
				break;
			case 'product_type':
			case 'product_cat':
			case 'product_tag':
			case 'product_brand':
			case 'product_attr_term':
			case 'product_shipping_class':
				$priority = 30;
				break;
			case 'product':
				$priority = 40;
				break;
		}

		return apply_filters( 'woodmart_condition_priority', $priority, $type );
	}

	/**
	 * Get current shipping method.
	 *
	 * @return string|null
	 */
	public function get_selected_method() {
		if ( ! isset( WC()->session ) ) {
			return null;
		}

		$selected_shipping_method = WC()->session->get( 'chosen_shipping_methods' );

		// If the delivery method has not yet been selected then set the first of the list.
		if ( empty( $selected_shipping_method ) && ! empty( WC()->cart ) ) {
			add_filter( 'woocommerce_cart_needs_shipping', '__return_true' );

			WC()->cart->calculate_shipping();

			remove_filter( 'woocommerce_cart_needs_shipping', '__return_true' );

			$packages = WC()->shipping()->get_packages();

			foreach ( $packages as $i => $package ) {
				foreach ( $package['rates'] as $key => $rate ) {
					$selected_shipping_method[ $i ] = $key;

					break;
				}
			}

			WC()->session->set( 'chosen_shipping_methods', $selected_shipping_method );
		}

		if ( empty( $selected_shipping_method ) ) {
			return null;
		}

		foreach ( $selected_shipping_method as $method ) {
			if ( false !== $method ) {
				$method = explode( ':', $method );

				return isset( $method[1] ) ? $method[1] : null;
			}
		}

		return null;
	}
}

Manager::get_instance();
