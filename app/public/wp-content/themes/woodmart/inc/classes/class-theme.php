<?php
/**
 * Main theme class.
 *
 * @package xts
 */

namespace XTS;

if ( ! defined( 'WOODMART_THEME_DIR' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * Main theme class.
 */
class Theme {
	/**
	 * List with main theme class names.
	 *
	 * @var string[]
	 */
	private $register_classes = array(
		'notices',
		'layout',
		'api',
		'autoupdates',
		'activation',
		'themesettingscss',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->core_plugin_classes();
		$this->dashboard_files();
		$this->general_files_include();
		$this->wpb_files_include();
		$this->register_classes();
		$this->wpb_element_files_include();
		$this->shortcodes_files_include();

		if ( is_admin() ) {
			$this->admin_files_include();
		}

		add_action( 'init', array( $this, 'enqueue_theme_settings_options' ), 5 );
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'woodmart_scheduled_update', array( __CLASS__, 'run_update' ), 10, 1 );
	}

	/**
	 * Enqueue general theme files.
	 *
	 * @return void
	 */
	private function general_files_include() {
		$files = array(
			'helpers',
			'functions',
			'actions',
			'template-tags/template-tags',
			'template-tags/portfolio',
			'theme-setup',
			'enqueue',
			'deprecated',

			'widgets/widgets',

			// Import.
			'admin/modules/import/class-import',

			// Woocommerce integration.
			'integrations/woocommerce/functions',
			'integrations/woocommerce/helpers',
			'integrations/woocommerce/template-tags',

			// General modules.
			'modules/parts-css-files/class-parts-css-files',
			'modules/inline-css-files/class-inline-css-files',
			'modules/styles-storage/class-styles-storage',
			'modules/lazy-loading',
			'modules/mobile-optimization',
			'modules/nav-menu-images/nav-menu-images',
			'modules/sticky-toolbar',
			'modules/white-label',
			'modules/layouts/class-main',
			'modules/layouts/class-global-data',
			'modules/patcher/class-main',
			'modules/theme-settings-backup/class-main',
			'modules/images/functions',
			'modules/mega-menu-walker/class-mega-menu-walker',
			'modules/header-builder/functions',
			'modules/header-builder/class-header-builder',
			'modules/twitter',
			'modules/seo-scheme/class-faq',
			'modules/seo-scheme/class-breadcrumbs',
			'modules/search/class-main',
			'modules/floating-blocks/class-main',
			'modules/performance/class-lcp',

			'admin/modules/options/class-themesettingscss',
			'admin/modules/options/class-options',

			// Woocommerce modules.
			'integrations/woocommerce/managers/class-module-endpoints-manager',
			'integrations/woocommerce/modules/attributes-meta-boxes',
			'integrations/woocommerce/modules/product-360-view',
			'integrations/woocommerce/modules/size-guide',
			'integrations/woocommerce/modules/swatches',
			'integrations/woocommerce/modules/catalog-mode',
			'integrations/woocommerce/modules/maintenance',
			'integrations/woocommerce/modules/progress-bar',
			'integrations/woocommerce/modules/quick-shop',
			'integrations/woocommerce/modules/quick-view',
			'integrations/woocommerce/modules/brands',
			'integrations/woocommerce/modules/compare/class-compare',
			'integrations/woocommerce/modules/quantity',
			'integrations/woocommerce/modules/class-adjacent-products',
			'integrations/woocommerce/modules/checkout-order-table/class-checkout-order-table',
			'integrations/woocommerce/modules/product-reviews/class-product-reviews',
			'integrations/woocommerce/modules/sticky-navigation/class-main',
			'integrations/woocommerce/modules/product-gallery-video/class-main',
			'integrations/woocommerce/modules/checkout-fields/class-main',
			'integrations/woocommerce/modules/variation-gallery',
			'integrations/woocommerce/modules/variation-gallery-new',
			'integrations/woocommerce/modules/wishlist/class-wc-wishlist',
			'integrations/woocommerce/modules/shipping-progress-bar/class-main',
			'integrations/woocommerce/modules/quick-buy/class-main',
			'integrations/woocommerce/modules/counter-visitors/class-main',
			'integrations/woocommerce/modules/linked-variations/class-main',
			'integrations/woocommerce/modules/unit-of-measure/class-main',
			'integrations/woocommerce/modules/show-single-variations/class-main',
			'integrations/woocommerce/modules/frequently-bought-together/class-main',
			'integrations/woocommerce/modules/sold-counter/class-main',
			'integrations/woocommerce/modules/dynamic-discounts/class-main',
			'integrations/woocommerce/modules/free-gifts/class-main',
			'integrations/woocommerce/modules/out-of-stock-manager/class-main',
			'integrations/woocommerce/modules/waitlist/class-main',
			'integrations/woocommerce/modules/estimate-delivery/class-main',
			'integrations/woocommerce/modules/product-tabs/class-main',
			'integrations/woocommerce/modules/abandoned-cart/class-main',
			'integrations/woocommerce/modules/review-reminder/class-main',
			'integrations/woocommerce/modules/price-tracker/class-main',
			'integrations/woocommerce/modules/marketing-consent/class-main',

			// Plugin integrations.
			'integrations/wcmp',
			'integrations/wpml',
			'integrations/wordfence',
			'integrations/aioseo',
			'integrations/yoast',
			'integrations/wcfm',
			'integrations/wcfmmp',
			'integrations/gutenberg/functions',
			'integrations/gutenberg/class-gutenberg',
			'integrations/imagify',
			'integrations/dokan',
			'integrations/tgm-plugin-activation',
			'integrations/rocket',
			'integrations/woo-preview-emails',
			'integrations/woocs',
			'integrations/wcpay',
			'integrations/curcy',
			'integrations/rank-math',
			'integrations/cartflows',
			'integrations/vgse',
			'integrations/woo-subscriptions',
			'integrations/revslider',
		);

		if ( did_action( 'elementor/loaded' ) ) {
			$files[] = 'integrations/elementor/helpers';
			$files[] = '/integrations/elementor/class-elementor';
		}

		$this->enqueue_files( $files );
	}

	/**
	 * Register main theme classes.
	 *
	 * @return void
	 */
	private function register_classes() {
		foreach ( $this->register_classes as $class ) {
			Registry::getInstance()->$class;
		}
	}

	/**
	 * Enqueue files for WPB page builder.
	 *
	 * @return void
	 */
	private function wpb_files_include() {
		if ( 'wpb' !== woodmart_get_current_page_builder() || ! defined( 'WPB_VC_VERSION' ) ) {
			return;
		}

		$files = array(
			'integrations/visual-composer/classes/class-vctemplates',
			'integrations/visual-composer/classes/class-wpbcssgenerator',
			'integrations/visual-composer/classes/class-short-code-fix',

			'integrations/visual-composer/functions',
			'integrations/visual-composer/global-maps',
			'integrations/visual-composer/fields/vc-functions',
			'integrations/visual-composer/fields/fields-css',
			'integrations/visual-composer/fields/image-hotspot',
			'integrations/visual-composer/fields/title-divider',
			'integrations/visual-composer/fields/slider',
			'integrations/visual-composer/fields/responsive-size',
			'integrations/visual-composer/fields/responsive-spacing',
			'integrations/visual-composer/fields/image-select',
			'integrations/visual-composer/fields/dropdown',
			'integrations/visual-composer/fields/css-id',
			'integrations/visual-composer/fields/gradient',
			'integrations/visual-composer/fields/colorpicker',
			'integrations/visual-composer/fields/datepicker',
			'integrations/visual-composer/fields/switch',
			'integrations/visual-composer/fields/button-set',
			'integrations/visual-composer/fields/empty-space',

			'integrations/visual-composer/fields/new/slider',
			'integrations/visual-composer/fields/new/colorpicker',
			'integrations/visual-composer/fields/new/box-shadow',
			'integrations/visual-composer/fields/new/number',
			'integrations/visual-composer/fields/new/select',
			'integrations/visual-composer/fields/new/fonts',
			'integrations/visual-composer/fields/new/dimensions',
			'integrations/visual-composer/fields/new/upload',
			'integrations/visual-composer/fields/new/notice',
		);

		$this->enqueue_files( $files );
	}

	/**
	 * Enqueue elements map for WPB page builder.
	 *
	 * @return void
	 */
	private function wpb_element_files_include() {
		$files = array(
			'social',
			'info-box',
			'button',
			'author-area',
			'promo-banner',
			'instagram',
			'images-gallery',
			'size-guide',
		);

		$woo_files = array(
			'products-tabs',
			'brands',
			'categories',
			'product-filters',
			'products-widget',
			'products',
		);

		$wpb_files = array(
			'register-maps',
			'parallax-scroll',
			'3d-view',
			'products-tabs',
			'ajax-search',
			'counter',
			'blog',
			'brands',
			'breadcrumbs',
			'countdown-timer',
			'extra-menu',
			'google-map',
			'image-hotspot',
			'list',
			'mega-menu',
			'menu-price',
			'nested-carousel',
			'page-heading',
			'popup',
			'portfolio',
			'pricing-tables',
			'categories',
			'product-filters',
			'products',
			'responsive-text-block',
			'text-block',
			'marquee',
			'contact-form-7',
			'image',
			'mailchimp',
			'title',
			'row-divider',
			'slider',
			'team-member',
			'testimonials',
			'timeline',
			'twitter',
			'video-poster',
			'compare',
			'wishlist',
			'html-block',
			'tabs',
			'accordion',
			'sidebar',
			'products-widget',
			'off-canvas-column-btn',
			'open-street-map',
			'table',
			'video',
			'compare-images',
			'toggle',
		);

		if ( defined( 'WPB_VC_VERSION' ) ) {
			$files = array_merge( $files, $wpb_files );

			if ( ! woodmart_woocommerce_installed() ) {
				$files = array_diff( $files, $woo_files );
			}
		}

		foreach ( $files as $file ) {
			require_once get_template_directory() . '/inc/integrations/visual-composer/maps/' . $file . '.php';
		}
	}

	/**
	 * Enqueue elements template for WPB page builder.
	 *
	 * @return void
	 */
	private function shortcodes_files_include() {
		$files = array(
			'social',
			'html-block',
			'products',
			'info-box',
			'button',
			'author-area',
			'promo-banner',
			'instagram',
			'user-panel',
			'posts-slider',
			'slider',
			'images-gallery',
			'size-guide',
			'blog',
			'gallery',
		);

		$wpb_files = array(
			'3d-view',
			'ajax-search',
			'countdown-timer',
			'counter',
			'extra-menu',
			'google-map',
			'mega-menu',
			'menu-price',
			'nested-carousel',
			'popup',
			'portfolio',
			'pricing-tables',
			'responsive-text-block',
			'text-block',
			'marquee',
			'contact-form-7',
			'image',
			'mailchimp',
			'row-divider',
			'team-member',
			'testimonials',
			'timeline',
			'title',
			'twitter',
			'list',
			'image-hotspot',
			'products-tabs',
			'page-heading',
			'brands',
			'categories',
			'product-filters',
			'tabs',
			'accordion',
			'sidebar',
			'off-canvas-column-btn',
			'open-street-map',
			'table',
			'video',
			'compare-images',
			'breadcrumbs',
			'toggle',
		);

		$woo_files = array(
			'products-tabs',
			'brands',
			'categories',
			'product-filters',
			'products',
			'size-guide',
		);

		$files = array_merge( $files, $wpb_files );

		if ( ! woodmart_woocommerce_installed() ) {
			$files = array_diff( $files, $woo_files );
		}

		foreach ( $files as $file ) {
			require_once get_template_directory() . '/inc/shortcodes/' . $file . '.php';
		}
	}

	/**
	 * Enqueue theme dashboard files.
	 *
	 * @return void
	 */
	private function dashboard_files() {
		$this->enqueue_files(
			array(
				'admin/modules/dashboard/class-dashboard',
				'admin/modules/dashboard/class-menu',
				'admin/modules/dashboard/class-slider',
				'admin/modules/dashboard/class-status-button',
				'admin/modules/guide-tour/class-main',
			)
		);
	}

	/**
	 * Enqueue theme setting files.
	 *
	 * @return void
	 */
	private function admin_files_include() {
		$this->enqueue_files(
			array(
				'admin/modules/setup-wizard/class-setup-wizard',
				'admin/modules/setup-wizard/class-install-child-theme',
				'admin/modules/setup-wizard/class-install-plugins',
				'admin/init',
			)
		);
	}

	/**
	 * Enqueue core classes.
	 *
	 * @return void
	 */
	private function core_plugin_classes() {
		if ( class_exists( 'WOODMART_Auth' ) ) {
			$files = array(
				'vendor/opauth/twitteroauth/twitteroauth',
				'vendor/autoload',
			);

			foreach ( $files as $file ) {
				require_once apply_filters( 'woodmart_require', WOODMART_PT_3D . $file . '.php' );
			}

			$this->register_classes[] = 'auth';
		}
	}

	/**
	 * Enqueue theme settings options.
	 *
	 * @return void
	 */
	public function enqueue_theme_settings_options() {
		$this->enqueue_files(
			array(
				'admin/settings/sections',
				'admin/settings/general',
				'admin/settings/general-layout',
				'admin/settings/api-integrations',
				'admin/settings/product-archive',
				'admin/settings/page-title',
				'admin/settings/footer',
				'admin/settings/typography',
				'admin/settings/colors',
				'admin/settings/carousel',
				'admin/settings/blog',
				'admin/settings/portfolio',
				'admin/settings/shop',
				'admin/settings/product',
				'admin/settings/login',
				'admin/settings/custom-css',
				'admin/settings/custom-js',
				'admin/settings/social',
				'admin/settings/performance',
				'admin/settings/other',
				'admin/settings/maintenance',
				'admin/settings/white-label',
				'admin/settings/import',
				'admin/settings/wishlist',
			)
		);
	}

	/**
	 * Enqueue files.
	 *
	 * @param array $files List with files to include.
	 * @return void
	 */
	private function enqueue_files( $files ) {
		foreach ( $files as $file ) {
			require_once get_parent_theme_file_path( WOODMART_FRAMEWORK . '/' . $file . '.php' );
		}
	}

	/**
	 * Check theme version and run the updater is required.
	 */
	public static function check_version() {
		$current_version = get_option( 'woodmart_version' );
		$target_version  = woodmart_get_theme_info( 'Version' );

		if ( version_compare( $current_version, $target_version, '<' ) ) {
			if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
				self::run_update( $target_version );
			} elseif ( ! wp_next_scheduled( 'woodmart_scheduled_update' ) ) {
				wp_schedule_single_event( time() + 10, 'woodmart_scheduled_update', array( $target_version ) );
			}
		}
	}

	/**
	 * Do action and update theme version in db.
	 *
	 * @param string $version Actual theme version.
	 */
	public static function run_update( $version ) {
		do_action( 'woodmart_updated', $version );

		update_option( 'woodmart_version', $version );
	}
}
