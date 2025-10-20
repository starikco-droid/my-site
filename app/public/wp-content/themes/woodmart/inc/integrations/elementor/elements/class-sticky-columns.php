<?php
/**
 * Nested carousel element.
 *
 * @package xts
 */

namespace XTS\Elementor;

use Elementor\Controls_Manager;
use Elementor\Modules\NestedElements\Module as NestedElementsModule;
use Elementor\Modules\NestedElements\Base\Widget_Nested_Base;
use Elementor\Modules\NestedElements\Controls\Control_Nested_Repeater;
use Elementor\Plugin;
use Elementor\Repeater;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Elementor widget that inserts an embeddable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Sticky_Columns extends Widget_Nested_Base {
	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'wd_sticky_columns';
	}

	/**
	 * Get widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Sticky columns', 'woodmart' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'wd-icon-sticky-columns';
	}

	/**
	 * Get widget categories.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'wd-elements' );
	}

	public function show_in_panel() {
		return Plugin::$instance->experiments->is_feature_active( 'nested-elements' );
	}

	protected function column_content_container( $index ) {
		return array(
			'elType'   => 'container',
			'settings' => array(
				'_title'        => sprintf(
					// translators: %s Slide index.
					esc_html__( 'Column #%s', 'woodmart' ),
					$index
				),
				'content_width' => 'full',
			),
		);
	}

	protected function get_default_children_elements() {
		return array(
			$this->column_content_container( 1 ),
			$this->column_content_container( 2 ),
		);
	}

	protected function get_default_children_title() {
		return esc_html__( 'Column #%d', 'woodmart' );
	}

	protected function get_default_children_placeholder_selector() {
		return '.wd-grid-g';
	}

	protected function get_default_repeater_title_setting_key() {
		return '';
	}

	/**
	 * Register the widget controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		/**
		 * General settings
		 */
		$this->start_controls_section(
			'general_section',
			array(
				'label' => esc_html__( 'General', 'woodmart' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'css_classes',
			array(
				'type'         => 'wd_css_class',
				'default'      => 'wd-sticky-columns',
				'prefix_class' => '',
			)
		);

		$this->add_responsive_control(
			'column_width',
			array(
				'label'     => esc_html__( 'Column width', 'woodmart' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .wd-grid-g' => '--wd-width: {{SIZE}}%',
				),
				'range'     => array(
					'%' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
			)
		);

		$this->add_control(
			'sticky_content',
			array(
				'label'        => esc_html__( 'Sticky content on desktop', 'woodmart' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Yes', 'woodmart' ),
				'label_off'    => esc_html__( 'No', 'woodmart' ),
				'return_value' => 'lg',
				'prefix_class' => 'wd-sticky-on-',
			)
		);

		$this->add_control(
			'sticky_content_tablet',
			array(
				'label'        => esc_html__( 'Sticky content on tablet', 'woodmart' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Yes', 'woodmart' ),
				'label_off'    => esc_html__( 'No', 'woodmart' ),
				'return_value' => 'md-sm',
				'prefix_class' => 'wd-sticky-on-',
			)
		);

		$this->add_control(
			'offset',
			array(
				'label'        => esc_html__( 'Sticky column offset (px)', 'woodmart' ),
				'type'         => Controls_Manager::NUMBER,
				'default'      => 150,
				'prefix_class' => 'wd_sticky_offset_',
				'ai'           => array(
					'active' => false,
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function render() {
		$children = $this->get_children();

		woodmart_enqueue_js_library( 'sticky-kit' );
		woodmart_enqueue_js_script( 'sticky-columns-element' );

		$this->add_render_attribute(
			array(
				'wrapper' => array(
					'class' => array(
						'wd-grid-g',
						'wd-sticky-columns-inner',
					),
					'style' => '--wd-col-lg:2;--wd-col-sm:1;--wd-gap-lg:30px;--wd-gap-sm:20px;',
				),
			)
		);

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); // phpcs:ignore ?>>
			<?php if ( $children ) : ?>
				<?php foreach ( $children as $child ) : ?>
					<div class="wd-col">
						<?php $child->print_element(); ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	protected function content_template() {
		?>
		<#
		view.addRenderAttribute( 'wrapper', {
			'class': [ 'wd-grid-g' ],
			'style': '--wd-col-lg:2'
		} );
		#>

		<div {{{ view.getRenderAttributeString( 'wrapper' ) }}}></div>
		<?php
	}
}

if ( Plugin::$instance->experiments->is_feature_active( 'nested-elements' ) ) {
	Plugin::instance()->widgets_manager->register( new Sticky_Columns() );
}
