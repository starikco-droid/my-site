<?php
/**
 * This file describes class for render view all checkout detail fields.
 *
 * @package Woodmart.
 */

namespace XTS\Modules\Checkout_Fields\List_Table;

use WP_List_Table;
use XTS\Modules\Checkout_Fields\Helper;
use XTS\Modules\Checkout_Fields\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * Create a new table class that will extend the WP_List_Table.
 */
class Fields_Table extends WP_List_Table {
	/**
	 * This table data.
	 *
	 * @var array|array[]
	 */
	public $table_data;

	/**
	 * Instance of the Helper class.
	 *
	 * @var Helper
	 */
	public $helper;

	/**
	 * Instance of the Admin class.
	 *
	 * @var Admin
	 */
	public $admin;

	/**
	 * Constructor.
	 *
	 * @param array|string $args Array or string of arguments.
	 */
	public function __construct( $args = array() ) {
		if ( ! woodmart_get_opt( 'checkout_fields_enabled' ) || ! woodmart_woocommerce_installed() ) {
			return;
		}

		parent::__construct( $args );

		$this->helper = Helper::get_instance();
		$this->admin  = Admin::get_instance();
	}

	/**
	 * Define what data to show on each column of the table.
	 *
	 * @param array  $item        Data.
	 * @param string $column_name - Current column name.
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		return array_key_exists( $column_name, $item ) ? esc_html( $item[ $column_name ] ) : '';
	}

	/**
	 * Print sort column.
	 *
	 * @param array $item Item to use to print record.
	 *
	 * @return string
	 */
	public function column_sort( $item ) {
		ob_start();
		?>

		<div class="xts-ui-sortable-handle"></div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Print position column.
	 *
	 * @param array $item Item to use to print record.
	 *
	 * @return string
	 */
	public function column_position( $item ) {
		$current = '';

		if ( ! empty( $item['class'] ) ) {
			if ( is_string( $item['class'] ) ) {
				$item['class'] = explode( ' ', $item['class'] );
			}

			if ( in_array( 'form-row-first', $item['class'], true ) ) {
				$current = 'form-row-first';
			} elseif ( in_array( 'form-row-wide', $item['class'], true ) ) {
				$current = 'form-row-wide';
			} elseif ( in_array( 'form-row-last', $item['class'], true ) ) {
				$current = 'form-row-last';
			}
		}

		if ( ! isset( $item['field_name'] ) ) {
			return '';
		}

		ob_start();

		$this->helper->get_template(
			'select',
			array(
				'id'      => $item['field_name'],
				'label'   => esc_html__( 'Position', 'woodmart' ),
				'options' => array(
					'form-row-first' => esc_html__( 'Left', 'woodmart' ),
					'form-row-wide'  => esc_html__( 'Wide', 'woodmart' ),
					'form-row-last'  => esc_html__( 'Right', 'woodmart' ),
				),
				'current' => $current,
			)
		);

		return ob_get_clean();
	}

	/**
	 * Print required column.
	 *
	 * @param array $item Item to use to print record.
	 *
	 * @return string
	 */
	public function column_required( $item ) {
		if ( ! isset( $item['field_name'] ) || ! isset( $item['required'] ) ) {
			return '';
		}

		ob_start();

		$this->helper->get_template(
			'status-button',
			array(
				'id'       => $item['field_name'],
				'status'   => $item['required'],
				'text_on'  => esc_html__( 'Yes', 'woodmart' ),
				'text_off' => esc_html__( 'No', 'woodmart' ),
			)
		);

		return ob_get_clean();
	}

	/**
	 * Print label column.
	 *
	 * @param array $item Item to use to print record.
	 *
	 * @return string
	 */
	public function column_label( $item ) {
		return ! empty( $item['label'] ) ? esc_html( $item['label'] ) : '';
	}

	/**
	 * Print field_name column.
	 *
	 * @param array $item Item to use to print record.
	 *
	 * @return string
	 */
	public function column_field_name( $item ) {
		return ! empty( $item['field_name'] ) ? esc_html( $item['field_name'] ) : '';
	}

	/**
	 * Print status column.
	 *
	 * @param array $item Item to use to print record.
	 *
	 * @return string
	 */
	public function column_status( $item ) {
		if ( ! isset( $item['field_name'] ) ) {
			return '';
		}

		ob_start();

		$this->helper->get_template(
			'status-button',
			array(
				'id'     => $item['field_name'],
				'status' => isset( $item['status'] ) ? $item['status'] : true,
			)
		);

		return ob_get_clean();
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'sort'       => esc_html__( 'Sort', 'woodmart' ),
			'field_name' => esc_html__( 'Name', 'woodmart' ),
			'label'      => esc_html__( 'Label', 'woodmart' ),
			'position'   => esc_html__( 'Position', 'woodmart' ),
			'required'   => esc_html__( 'Required', 'woodmart' ),
			'status'     => esc_html__( 'Status', 'woodmart' ),
		);
	}

	/**
	 * Prepare the items for the table to process.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$this->table_data = $this->table_data();

		woodmart_sort_data( $this->table_data, 'priority', 'asc' );

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->table_data;
	}

	/**
	 * Get the table data.
	 *
	 * @return array
	 */
	public function table_data() {
		$current_tab    = $this->admin->get_current_tab();
		$change_options = get_option( 'xts_checkout_fields_manager_options', array() );
		$default_fields = $this->helper->get_default_fields();

		$change_options = isset( $change_options[ $current_tab ] ) ? $change_options[ $current_tab ] : array();
		$default_fields = isset( $default_fields[ $current_tab ] ) ? $default_fields[ $current_tab ] : array();

		$updated_checkout_fields = $this->helper->recursive_parse_args( $default_fields, $change_options );

		return array_filter(
			$updated_checkout_fields,
			function ( $field ) {
				if ( array_key_exists( 'field_name', $field ) ) {
					return $field;
				}
			}
		);
	}

	/**
	 * Print filters for current table
	 *
	 * @param string $which Top / Bottom.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function extra_tablenav( $which ) {
		if ( 'bottom' !== $which ) {
			return;
		}

		$change_options = get_option( 'xts_checkout_fields_manager_options', array() );
		?>
		<a href="<?php echo esc_attr( add_query_arg( 'reset-all-fields', true, $this->admin->get_base_url() ) ); ?>" class="xts-reset-all-fields xts-bordered-btn xts-color-warning<?php echo empty( $change_options ) ? ' xts-hidden' : ''; ?>">
			<?php esc_html_e( 'Reset all', 'woodmart' ); ?>
		</a>
		<?php
	}

	/**
	 * Override the parent method. Add custom css class to table.
	 *
	 * @return string[] Array of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		$css_classes   = parent::get_table_classes();
		$css_classes[] = 'xts-ui-sortable';

		return $css_classes;
	}

	/**
	 * Override the parent method. Add custom attributes to single row.
	 *
	 * @param object|array $item The current item.
	 */
	public function single_row( $item ) {
		?>
		<tr data-field-id="<?php echo isset( $item['field_name'] ) ? esc_attr( $item['field_name'] ) : ''; ?>">
			<?php $this->single_row_columns( $item ); ?>
		</tr>
		<?php
	}
}
