<?php
/**
 * Breadcrumbs map.
 *
 * @package Woodmart
 */

namespace XTS\Elementor;

use Elementor\Group_Control_Typography;
use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Widget_Base;
use XTS\Modules\Layouts\Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Elementor widget that inserts an embeddable content into the page, from any given URL.
 */
class Breadcrumbs extends Widget_Base {
	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'wd_element_breadcrumbs';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Breadcrumbs', 'woodmart' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'wd-icon-breadcrumbs';
	}

	/**
	 * Show in panel.
	 *
	 * @return bool Whether to show the widget in the panel or not.
	 */
	public function show_in_panel() {
		return ! Main::is_layout_type( 'single_product' ) &&
		! Main::is_layout_type( 'shop_archive' ) &&
		! Main::is_layout_type( 'checkout_form' ) &&
		! Main::is_layout_type( 'cart' ) &&
		! Main::is_layout_type( 'checkout_content' ) &&
		! Main::is_layout_type( 'thank_you_page' ) &&
		! Main::is_layout_type( 'my_account_page' ) &&
		! Main::is_layout_type( 'my_account_auth' ) &&
		! Main::is_layout_type( 'my_account_lost_password' );
	}

	/**
	 * Get widget categories.
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'wd-site-elements' );
	}

	/**
	 * Register the widget controls.
	 */
	protected function register_controls() {

		/**
		 * Style tab
		 */

		/**
		 * General settings
		 */
		$this->start_controls_section(
			'general_style_section',
			array(
				'label' => esc_html__( 'General', 'woodmart' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'css_classes',
			array(
				'type'         => 'wd_css_class',
				'default'      => 'wd-el-breadcrumbs',
				'prefix_class' => '',
			)
		);

		$this->add_control(
			'alignment',
			array(
				'label'        => esc_html__( 'Alignment', 'woodmart' ),
				'type'         => 'wd_buttons',
				'options'      => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'woodmart' ),
						'image' => WOODMART_ASSETS_IMAGES . '/settings/align/left.jpg',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'woodmart' ),
						'image' => WOODMART_ASSETS_IMAGES . '/settings/align/center.jpg',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'woodmart' ),
						'image' => WOODMART_ASSETS_IMAGES . '/settings/align/right.jpg',
					),
				),
				'prefix_class' => 'text-',
				'default'      => 'left',
			)
		);

		$this->add_control(
			'nowrap_md',
			array(
				'label'        => esc_html__( 'No wrap on mobile devices', 'woodmart' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'md',
				'prefix_class' => 'wd-nowrap-',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'woodmart' ),
				'selector' => '{{WRAPPER}} :is(.wd-breadcrumbs,.yoast-breadcrumb)',
			)
		);

		$this->start_controls_tabs( 'text_color_tabs' );

		$this->start_controls_tab(
			'text_color_tab',
			array(
				'label' => esc_html__( 'Idle', 'woodmart' ),
			)
		);

		$this->add_control(
			'text_idle_color',
			array(
				'label'     => esc_html__( 'Color', 'woodmart' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} :is(.wd-breadcrumbs,.yoast-breadcrumb)' => '--wd-link-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'delimiter_color',
			array(
				'label'     => esc_html__( 'Delimiter color', 'woodmart' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} :is(.wd-breadcrumbs,.yoast-breadcrumb)' => '--wd-bcrumb-delim-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'text_hover_color_tab',
			array(
				'label' => esc_html__( 'Hover', 'woodmart' ),
			)
		);

		$this->add_control(
			'text_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'woodmart' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} :is(.wd-breadcrumbs,.yoast-breadcrumb)' => '--wd-link-color-hover: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'text_active_color_tab',
			array(
				'label' => esc_html__( 'Active', 'woodmart' ),
			)
		);

		$this->add_control(
			'text_active_color',
			array(
				'label'     => esc_html__( 'Color', 'woodmart' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} :is(.wd-breadcrumbs,.yoast-breadcrumb)' => '--wd-bcrumb-color-active: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the frontend.
	 */
	protected function render() {
		$settings = wp_parse_args(
			$this->get_settings_for_display(),
			array(
				'nowrap_md' => '',
			)
		);

		Main::setup_preview();

		if ( ! empty( $settings['nowrap_md'] ) ) {
			woodmart_enqueue_inline_style( 'woo-el-breadcrumbs-builder' );
		}

		woodmart_current_breadcrumbs( 'pages' );
		Main::restore_preview();
	}
}

Plugin::instance()->widgets_manager->register( new Breadcrumbs() );
