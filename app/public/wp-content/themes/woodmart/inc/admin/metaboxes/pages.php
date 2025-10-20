<?php
/**
 * Page metaboxes
 *
 * @package xts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

use XTS\Admin\Modules\Options\Metaboxes;

if ( ! function_exists( 'woodmart_register_page_metaboxes' ) ) {
	/**
	 * Register page metaboxes
	 *
	 * @since 1.0.0
	 */
	function woodmart_register_page_metaboxes() {
		global $woodmart_transfer_options, $woodmart_prefix;

		$woodmart_prefix = '_woodmart_';

		$page_metabox = Metaboxes::add_metabox(
			array(
				'id'         => 'xts_page_metaboxes',
				'title'      => esc_html__( 'Page Setting (custom metabox from theme)', 'woodmart' ),
				'post_types' => array( 'page', 'post', 'portfolio' ),
			)
		);

		$page_metabox->add_section(
			array(
				'id'       => 'header',
				'name'     => esc_html__( 'Header', 'woodmart' ),
				'priority' => 10,
				'icon'     => 'xts-i-header-builder',
			)
		);

		$page_metabox->add_section(
			array(
				'id'       => 'page_title',
				'name'     => esc_html__( 'Page title', 'woodmart' ),
				'priority' => 20,
				'icon'     => 'xts-i-page-title',
			)
		);

		$page_metabox->add_section(
			array(
				'id'       => 'sidebar',
				'name'     => esc_html__( 'Sidebar', 'woodmart' ),
				'priority' => 30,
				'icon'     => 'xts-i-sidebars',
			)
		);

		$page_metabox->add_section(
			array(
				'id'       => 'footer',
				'name'     => esc_html__( 'Footer', 'woodmart' ),
				'priority' => 40,
				'icon'     => 'xts-i-footer',
			)
		);

		if ( woodmart_get_opt( 'preload_lcp_image' ) ) {
			$page_metabox->add_section(
				array(
					'id'         => 'preload',
					'name'       => esc_html__( 'Preload image', 'woodmart' ),
					'priority'   => 45,
					'icon'       => 'xts-i-performance',
					'post_types' => array( 'page' ),
				)
			);
		}

		$page_metabox->add_section(
			array(
				'id'         => 'mobile',
				'name'       => esc_html__( 'Mobile version', 'woodmart' ),
				'priority'   => 50,
				'icon'       => 'xts-i-phone',
				'post_types' => array( 'page' ),
			)
		);

		$page_metabox->add_field(
			array(
				'id'           => $woodmart_prefix . 'mobile_content',
				'name'         => esc_html__( 'Mobile version HTML block (experimental)', 'woodmart' ),
				'description'  => esc_html__( 'You can create a separate content that will be displayed on mobile devices to optimize the performance.', 'woodmart' ),
				'type'         => 'select',
				'section'      => 'mobile',
				'select2'      => true,
				'empty_option' => true,
				'autocomplete' => array(
					'type'   => 'post',
					'value'  => 'cms_block',
					'search' => 'woodmart_get_post_by_query_autocomplete',
					'render' => 'woodmart_get_post_by_ids_autocomplete',
				),
				'priority'     => 10,
			)
		);

		$page_metabox->add_field(
			array(
				'id'          => $woodmart_prefix . 'open_categories',
				'type'        => 'checkbox',
				'name'        => esc_html__( 'Open categories menu', 'woodmart' ),
				'hint'        => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'open-categories-menu.jpg" alt="">', true ),
				'description' => esc_html__( 'Always shows categories menu on this page', 'woodmart' ),
				'section'     => 'header',
				'on-text'     => esc_html__( 'Yes', 'woodmart' ),
				'off-text'    => esc_html__( 'No', 'woodmart' ),
				'priority'    => 10,
				'class'       => 'xts-field-sidebar-switcher',
			)
		);

		$page_metabox->add_field(
			array(
				'id'          => $woodmart_prefix . 'whb_header',
				'name'        => esc_html__( 'Custom header for this page', 'woodmart' ),
				'description' => esc_html__( 'If you are using our header builder for your header configuration you can select different layout from the list for this particular page.', 'woodmart' ),
				'type'        => 'select',
				'section'     => 'header',
				'options'     => '',
				'callback'    => 'woodmart_get_theme_settings_headers_array',
				'default'     => 'none',
				'priority'    => 20,
			)
		);

		$page_metabox->add_field(
			array(
				'id'          => $woodmart_prefix . 'title_off',
				'type'        => 'checkbox',
				'name'        => esc_html__( 'Disable page title', 'woodmart' ),
				'hint'        => '<video data-src="' . WOODMART_TOOLTIP_URL . 'disable-page-title.mp4" autoplay loop muted></video>',
				'description' => esc_html__( 'Hide page heading on this page.', 'woodmart' ),
				'section'     => 'page_title',
				'on-text'     => esc_html__( 'Yes', 'woodmart' ),
				'off-text'    => esc_html__( 'No', 'woodmart' ),
				'priority'    => 30,
				'class'       => 'xts-field-sidebar-switcher',
			)
		);

		$page_metabox->add_field(
			array(
				'id'          => $woodmart_prefix . 'page-title-size',
				'name'        => esc_html__( 'Page title size', 'woodmart' ),
				'description' => esc_html__( 'Set different size for page title.', 'woodmart' ),
				'type'        => 'buttons',
				'section'     => 'page_title',
				'options'     => array(
					'inherit' => array(
						'name'  => esc_html__( 'Inherit', 'woodmart' ),
						'value' => 'inherit',
					),
					'default' => array(
						'name'  => esc_html__( 'Default', 'woodmart' ),
						'hint'  => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'page-title-size-default.jpg" alt="">', true ),
						'value' => 'default',
					),
					'small'   => array(
						'name'  => esc_html__( 'Small', 'woodmart' ),
						'hint'  => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'page-title-size-small.jpg" alt="">', true ),
						'value' => 'small',
					),
					'large'   => array(
						'name'  => esc_html__( 'Large', 'woodmart' ),
						'hint'  => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'page-title-size-large.jpg" alt="">', true ),
						'value' => 'large',
					),
				),
				'default'     => 'inherit',
				'priority'    => 40,
			)
		);

		$page_metabox->add_field(
			array(
				'id'          => $woodmart_prefix . 'title_image',
				'type'        => 'upload',
				'name'        => esc_html__( 'Image for page title', 'woodmart' ),
				'description' => esc_html__( 'Upload an image', 'woodmart' ),
				'section'     => 'page_title',
				'priority'    => 50,
				'class'       => 'xts-col-6',
			)
		);

		$page_metabox->add_field(
			array(
				'id'          => $woodmart_prefix . 'title_bg_color',
				'type'        => 'color',
				'name'        => esc_html__( 'Page title background color', 'woodmart' ),
				'description' => esc_html__( 'Choose a color', 'woodmart' ),
				'section'     => 'page_title',
				'data_type'   => 'hex',
				'priority'    => 60,
				'class'       => 'xts-col-6',
			)
		);

		$page_metabox->add_field(
			array(
				'id'       => $woodmart_prefix . 'title_image_size',
				'name'     => esc_html__( 'Image size', 'woodmart' ),
				'type'     => 'select',
				'section'  => 'page_title',
				'options'  => woodmart_get_default_image_sizes(),
				'default'  => 'full',
				'priority' => 61,
				'requires' => array(
					array(
						'key'     => $woodmart_prefix . 'title_image',
						'compare' => 'not_equals',
						'value'   => '',
					),
				),
				'class'    => 'xts-col-4',
			)
		);

		$page_metabox->add_field(
			array(
				'id'       => $woodmart_prefix . 'title_image_size_custom_width',
				'name'     => esc_html__( 'Custom width (px)', 'woodmart' ),
				'type'     => 'text_input',
				'section'  => 'page_title',
				'default'  => '',
				'requires' => array(
					array(
						'key'     => $woodmart_prefix . 'title_image',
						'compare' => 'not_equals',
						'value'   => '',
					),
					array(
						'key'     => $woodmart_prefix . 'title_image_size',
						'compare' => 'equals',
						'value'   => array( 'custom' ),
					),
				),
				'priority' => 62,
				'class'    => 'xts-col-4',
			)
		);

		$page_metabox->add_field(
			array(
				'id'       => $woodmart_prefix . 'title_image_size_custom_height',
				'name'     => esc_html__( 'Custom height (px)', 'woodmart' ),
				'type'     => 'text_input',
				'section'  => 'page_title',
				'default'  => '',
				'requires' => array(
					array(
						'key'     => $woodmart_prefix . 'title_image',
						'compare' => 'not_equals',
						'value'   => '',
					),
					array(
						'key'     => $woodmart_prefix . 'title_image_size',
						'compare' => 'equals',
						'value'   => array( 'custom' ),
					),
				),
				'priority' => 63,
				'class'    => 'xts-col-4',
			)
		);

		$page_metabox->add_field(
			array(
				'id'       => $woodmart_prefix . 'title_color',
				'name'     => esc_html__( 'Text color for title', 'woodmart' ),
				'type'     => 'buttons',
				'section'  => 'page_title',
				'options'  => array(
					'default' => array(
						'name'  => esc_html__( 'Inherit', 'woodmart' ),
						'value' => 'default',
					),
					'light'   => array(
						'name'  => esc_html__( 'Light', 'woodmart' ),
						'value' => 'light',
					),
					'dark'    => array(
						'name'  => esc_html__( 'Dark', 'woodmart' ),
						'value' => 'dark',
					),
				),
				'default'  => 'default',
				'priority' => 70,
			)
		);

		$page_metabox->add_field(
			array(
				'id'          => $woodmart_prefix . 'main_layout',
				'name'        => esc_html__( 'Sidebar position', 'woodmart' ),
				'description' => esc_html__( 'Select main content and sidebar alignment.', 'woodmart' ),
				'type'        => 'buttons',
				'section'     => 'sidebar',
				'options'     => array(
					'default'       => array(
						'name'  => esc_html__( 'Inherit', 'woodmart' ),
						'value' => 'default',
					),
					'full-width'    => array(
						'name'  => esc_html__( 'Without', 'woodmart' ),
						'value' => 'full-width',
					),
					'sidebar-left'  => array(
						'name'  => esc_html__( 'Left', 'woodmart' ),
						'value' => 'sidebar-left',
					),
					'sidebar-right' => array(
						'name'  => esc_html__( 'Right', 'woodmart' ),
						'value' => 'sidebar-right',
					),
				),
				'default'     => 'default',
				'priority'    => 80,
			)
		);

		$page_metabox->add_field(
			array(
				'id'          => $woodmart_prefix . 'sidebar_width',
				'name'        => esc_html__( 'Sidebar size', 'woodmart' ),
				'description' => esc_html__( 'Set different size for sidebar on this page.', 'woodmart' ),
				'type'        => 'buttons',
				'section'     => 'sidebar',
				'options'     => array(
					'default' => array(
						'name'  => esc_html__( 'Inherit', 'woodmart' ),
						'value' => 'default',
					),
					2         => array(
						'name'  => esc_html__( 'Small', 'woodmart' ),
						'value' => 2,
					),
					3         => array(
						'name'  => esc_html__( 'Medium', 'woodmart' ),
						'value' => 3,
					),
					4         => array(
						'name'  => esc_html__( 'Large', 'woodmart' ),
						'value' => 4,
					),
				),
				'default'     => 'default',
				'priority'    => 90,
				'class'       => 'xts-tooltip-bordered',
			)
		);

		$woodmart_transfer_options[] = 'page-title-size';
		$woodmart_transfer_options[] = 'main_layout';
		$woodmart_transfer_options[] = 'sidebar_width';

		$page_metabox->add_field(
			array(
				'id'       => $woodmart_prefix . 'custom_sidebar',
				'name'     => esc_html__( 'Custom sidebar for this page', 'woodmart' ),
				'type'     => 'select',
				'section'  => 'sidebar',
				'options'  => '',
				'callback' => 'woodmart_get_theme_settings_sidebars_array',
				'default'  => 'none',
				'priority' => 100,
			)
		);

		$page_metabox->add_field(
			array(
				'id'          => $woodmart_prefix . 'footer_off',
				'type'        => 'checkbox',
				'name'        => esc_html__( 'Disable footer', 'woodmart' ),
				'hint'        => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'disable-footer.jpg" alt="">', true ),
				'description' => esc_html__( 'Disable footer on this page.', 'woodmart' ),
				'section'     => 'footer',
				'on-text'     => esc_html__( 'Yes', 'woodmart' ),
				'off-text'    => esc_html__( 'No', 'woodmart' ),
				'priority'    => 110,
				'class'       => 'xts-tooltip-bordered xts-field-sidebar-switcher',
			)
		);

		$page_metabox->add_field(
			array(
				'id'          => $woodmart_prefix . 'prefooter_off',
				'type'        => 'checkbox',
				'name'        => esc_html__( 'Disable prefooter', 'woodmart' ),
				'hint'        => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'disable-prefooter.jpg" alt="">', true ),
				'description' => esc_html__( 'Disable prefooter on this page.', 'woodmart' ),
				'section'     => 'footer',
				'on-text'     => esc_html__( 'Yes', 'woodmart' ),
				'off-text'    => esc_html__( 'No', 'woodmart' ),
				'priority'    => 120,
				'class'       => 'xts-tooltip-bordered xts-field-sidebar-switcher',
			)
		);

		$page_metabox->add_field(
			array(
				'id'          => $woodmart_prefix . 'copyrights_off',
				'type'        => 'checkbox',
				'name'        => esc_html__( 'Disable copyrights', 'woodmart' ),
				'hint'        => wp_kses( '<img data-src="' . WOODMART_TOOLTIP_URL . 'disable-copyrights.jpg" alt="">', true ),
				'description' => esc_html__( 'Disable copyrights on this page.', 'woodmart' ),
				'section'     => 'footer',
				'on-text'     => esc_html__( 'Yes', 'woodmart' ),
				'off-text'    => esc_html__( 'No', 'woodmart' ),
				'priority'    => 130,
				'class'       => 'xts-tooltip-bordered xts-field-sidebar-switcher',
			)
		);

		if ( woodmart_get_opt( 'preload_lcp_image' ) ) {
			$page_metabox->add_field(
				array(
					'id'          => $woodmart_prefix . 'preload_image',
					'type'        => 'upload',
					'name'        => esc_html__( 'Image', 'woodmart' ),
					'description' => esc_html__( 'Upload an image', 'woodmart' ),
					'section'     => 'preload',
					't_tab'       => array(
						'id'    => 'preload_image_tabs',
						'tab'   => esc_html__( 'Desktop', 'woodmart' ),
						'style' => 'default',
					),
					'priority'    => 10,
					'class'       => 'xts-col-6',
				)
			);

			$page_metabox->add_field(
				array(
					'id'       => $woodmart_prefix . 'preload_image_size',
					'name'     => esc_html__( 'Image size', 'woodmart' ),
					'type'     => 'select',
					'section'  => 'preload',
					'options'  => woodmart_get_default_image_sizes(),
					'default'  => 'full',
					't_tab'    => array(
						'id'   => 'preload_image_tabs',
						'icon' => 'xts-i-desktop',
						'tab'  => esc_html__( 'Desktop', 'woodmart' ),
					),
					'priority' => 20,
					'class'    => 'xts-col-6',
				)
			);

			$page_metabox->add_field(
				array(
					'id'       => $woodmart_prefix . 'preload_image_size_custom',
					'name'     => esc_html__( 'Custom image size', 'woodmart' ),
					'type'     => 'text_input',
					'section'  => 'preload',
					'default'  => '',
					't_tab'    => array(
						'id'   => 'preload_image_tabs',
						'icon' => 'xts-i-desktop',
						'tab'  => esc_html__( 'Desktop', 'woodmart' ),
					),
					'requires' => array(
						array(
							'key'     => $woodmart_prefix . 'preload_image_size',
							'compare' => 'equals',
							'value'   => array( 'custom' ),
						),
					),
					'priority' => 25,
				)
			);

			$page_metabox->add_field(
				array(
					'id'       => $woodmart_prefix . 'preload_image_type',
					'name'     => esc_html__( 'Image type', 'woodmart' ),
					'type'     => 'buttons',
					'section'  => 'preload',
					'options'  => array(
						'image'      => array(
							'name'  => esc_html__( 'Image tag (<img>)', 'woodmart' ),
							'value' => 'image',
						),
						'background' => array(
							'name'  => esc_html__( 'Background image (CSS)', 'woodmart' ),
							'value' => 'background',
						),
					),
					't_tab'    => array(
						'id'   => 'preload_image_tabs',
						'icon' => 'xts-i-desktop',
						'tab'  => esc_html__( 'Desktop', 'woodmart' ),
					),
					'description'  => esc_html__( 'Choose whether your image is added to the page using an "img" tag or as a background via CSS. Selecting the correct placement type will help determine whether srcsets are used for the image, ensuring each of them is considered in the LCP option. If you set the image using "Find" function, this value will be selected automatically.', 'woodmart' ),
					'default'  => 'image',
					'priority' => 30,
				)
			);

			$page_metabox->add_field(
				array(
					'id'          => $woodmart_prefix . 'preload_image_mobile',
					'type'        => 'upload',
					'name'        => esc_html__( 'Image', 'woodmart' ),
					'description' => esc_html__( 'Upload an image', 'woodmart' ),
					'section'     => 'preload',
					'priority'    => 40,
					't_tab'       => array(
						'id'   => 'preload_image_tabs',
						'icon' => 'xts-i-phone',
						'tab'  => esc_html__( 'Mobile', 'woodmart' ),
					),
					'class'       => 'xts-col-6',
				)
			);

			$page_metabox->add_field(
				array(
					'id'       => $woodmart_prefix . 'preload_image_mobile_size',
					'name'     => esc_html__( 'Image size', 'woodmart' ),
					'type'     => 'select',
					'section'  => 'preload',
					'options'  => woodmart_get_default_image_sizes(),
					'default'  => 'full',
					't_tab'    => array(
						'id'   => 'preload_image_tabs',
						'icon' => 'xts-i-phone',
						'tab'  => esc_html__( 'Mobile', 'woodmart' ),
					),
					'priority' => 50,
					'class'    => 'xts-col-6',
				)
			);

			$page_metabox->add_field(
				array(
					'id'       => $woodmart_prefix . 'preload_image_mobile_custom_size',
					'name'     => esc_html__( 'Custom image size', 'woodmart' ),
					'type'     => 'text_input',
					'section'  => 'preload',
					'default'  => '',
					'priority' => 55,
					't_tab'    => array(
						'id'   => 'preload_image_tabs',
						'icon' => 'xts-i-phone',
						'tab'  => esc_html__( 'Mobile', 'woodmart' ),
					),
					'requires' => array(
						array(
							'key'     => $woodmart_prefix . 'preload_image_mobile_size',
							'compare' => 'equals',
							'value'   => array( 'custom' ),
						),
					),
				)
			);

			$page_metabox->add_field(
				array(
					'id'       => $woodmart_prefix . 'preload_image_mobile_type',
					'name'     => esc_html__( 'Image type', 'woodmart' ),
					'type'     => 'buttons',
					'section'  => 'preload',
					'options'  => array(
						'image'      => array(
							'name'  => esc_html__( 'Image tag (<img>)', 'woodmart' ),
							'value' => 'image',
						),
						'background' => array(
							'name'  => esc_html__( 'Background image (CSS)', 'woodmart' ),
							'value' => 'background',
						),
					),
					'default'  => 'image',
					't_tab'    => array(
						'id'   => 'preload_image_tabs',
						'icon' => 'xts-i-phone',
						'tab'  => esc_html__( 'Mobile', 'woodmart' ),
					),
					'description'  => esc_html__( 'Choose whether your image is added to the page using an "img" tag or as a background via CSS. Selecting the correct placement type will help determine whether srcsets are used for the image, ensuring each of them is considered in the LCP option. If you set the image using "Find" function, this value will be selected automatically.', 'woodmart' ),
					'priority' => 60,
				)
			);
		}
	}

	add_action( 'init', 'woodmart_register_page_metaboxes', 100 );
}


$post_category_metabox = Metaboxes::add_metabox(
	array(
		'id'         => 'xts_post_category_metaboxes',
		'title'      => esc_html__( 'Extra options from theme', 'woodmart' ),
		'object'     => 'term',
		'taxonomies' => array( 'category' ),
	)
);

$post_category_metabox->add_section(
	array(
		'id'       => 'general',
		'name'     => esc_html__( 'General', 'woodmart' ),
		'icon'     => 'dashicons dashicons-welcome-write-blog',
		'priority' => 10,
	)
);

$post_category_metabox->add_field(
	array(
		'id'          => '_woodmart_blog_design',
		'name'        => esc_html__( 'Blog design', 'woodmart' ),
		'description' => esc_html__( 'Choose one of the blog designs available in the theme.', 'woodmart' ),
		'type'        => 'select',
		'section'     => 'general',
		'options'     => array(
			'inherit'      => array(
				'name'  => esc_html__( 'Inherit', 'woodmart' ),
				'value' => 'inherit',
			),
			'default'      => array(
				'name'  => esc_html__( 'Default', 'woodmart' ),
				'value' => 'Default',
			),
			'default-alt'  => array(
				'name'  => esc_html__( 'Default alternative', 'woodmart' ),
				'value' => 'default-alt',
			),
			'small-images' => array(
				'name'  => esc_html__( 'Small images', 'woodmart' ),
				'value' => 'small-images',
			),
			'chess'        => array(
				'name'  => esc_html__( 'Chess', 'woodmart' ),
				'value' => 'chess',
			),
			'masonry'      => array(
				'name'  => esc_html__( 'Grid', 'woodmart' ),
				'value' => 'default',
			),
			'mask'         => array(
				'name'  => esc_html__( 'Mask on image', 'woodmart' ),
				'value' => 'mask',
			),
			'meta-image'   => array(
				'name'  => esc_html__( 'Meta on image', 'woodmart' ),
				'value' => 'meta-image',
			),
		),
		'priority'    => 10,
	)
);
