<?php
/**
 * Plugin Name: WoodMart Images Optimizer
 * Plugin URI: https://woodmart.xtemos.com
 * Description: Image optimization plugin exclusively for WoodMart theme. Requires WoodMart theme to be active.
 * Version: 1.3.3
 * Author: XTemos
 * Author URI: https://xtemos.com
 * Text Domain: woodmart-images-optimizer
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WOODMART_IMAGES_OPTIMIZER_VERSION', '1.3.3' );
define( 'WOODMART_IMAGES_OPTIMIZER_PLUGIN_FILE', __FILE__ );
define( 'WOODMART_IMAGES_OPTIMIZER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOODMART_IMAGES_OPTIMIZER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WOODMART_IMAGES_OPTIMIZER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main WoodMart Images Optimizer Plugin Class
 */
class WoodMart_Images_Optimizer_Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var WoodMart_Images_Optimizer_Plugin
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return WoodMart_Images_Optimizer_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Initialize the plugin.
	 */
	public function init() {
		// Check if WoodMart theme is active.
		if ( ! $this->is_woodmart_theme_active() ) {
			add_action( 'admin_notices', array( $this, 'show_theme_dependency_notice' ) );
			return;
		}

		// Load text domain for translations.
		load_plugin_textdomain( 'woodmart-images-optimizer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		// Include required files.
		$this->include_files();

		// Initialize the main functionality.
		if ( class_exists( 'WoodMart_Images_Optimizer_Main' ) ) {
			new WoodMart_Images_Optimizer_Main();
		}

		// Add theme settings.
		add_action( 'init', array( $this, 'add_theme_settings' ), 20 );
	}

	/**
	 * Include required files.
	 */
	private function include_files() {
		require_once WOODMART_IMAGES_OPTIMIZER_PLUGIN_DIR . 'classes/class-main.php';
		require_once WOODMART_IMAGES_OPTIMIZER_PLUGIN_DIR . 'classes/class-optimizer.php';
		require_once WOODMART_IMAGES_OPTIMIZER_PLUGIN_DIR . 'classes/class-api-client.php';
		require_once WOODMART_IMAGES_OPTIMIZER_PLUGIN_DIR . 'classes/class-picture-display.php';
	}

	/**
	 * Check if WoodMart theme is active.
	 *
	 * @return bool
	 */
	private function is_woodmart_theme_active() {
		$theme = wp_get_theme();
		$template = strtolower( $theme->get_template() );
		$stylesheet = strtolower( $theme->get_stylesheet() );
		
		return false !== strpos( $template, 'woodmart' ) || false !== strpos( $stylesheet, 'woodmart' );
	}

	/**
	 * Show admin notice when WoodMart theme is not active.
	 */
	public function show_theme_dependency_notice() {
		$class = 'notice notice-error';
		$message = __( 'WoodMart Images Optimizer requires WoodMart theme to be active. Please activate WoodMart theme or deactivate this plugin.', 'woodmart-images-optimizer' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	/**
	 * Add settings to WoodMart theme options.
	 */
	public function add_theme_settings() {
		if ( ! class_exists( 'XTS\Admin\Modules\Options' ) ) {
			return;
		}

		// Add the settings to WoodMart theme performance section.
		$this->add_woodmart_theme_settings();
	}

	/**
	 * Add settings to WoodMart theme performance section.
	 */
	private function add_woodmart_theme_settings() {
		if ( ! class_exists( 'XTS\Admin\Modules\Options' ) ) {
			return;
		}

		$options_class = 'XTS\Admin\Modules\Options';

		// Add images optimizer section to performance.
		$options_class::add_section(
			array(
				'id'       => 'performance_images_optimizer',
				'name'     => esc_html__( 'Images optimizer', 'woodmart-images-optimizer' ),
				'parent'   => 'general_performance',
				'priority' => 45,
				'icon'     => 'xts-i-performance',
			)
		);

		// Add optimization quality setting.
		$options_class::add_field(
			array(
				'id'          => 'woodmart_optimizer_quality',
				'name'        => esc_html__( 'Optimization quality', 'woodmart-images-optimizer' ),
				'description' => esc_html__( 'Set the quality level for image optimization. Higher values preserve more quality but result in larger file sizes.', 'woodmart-images-optimizer' ),
				'type'        => 'range',
				'section'     => 'performance_images_optimizer',
				'default'     => 80,
				'min'         => 10,
				'max'         => 100,
				'step'        => 1,
				'priority'    => 10,
			)
		);

		// Add WebP generation setting.
		$options_class::add_field(
			array(
				'id'          => 'woodmart_optimizer_generate_webp',
				'name'        => esc_html__( 'Generate WebP images', 'woodmart-images-optimizer' ),
				'description' => esc_html__( 'Generate WebP versions of optimized images alongside the original optimized images. WebP format provides better compression for modern browsers.', 'woodmart-images-optimizer' ),
				'type'        => 'switcher',
				'section'     => 'performance_images_optimizer',
				'default'     => false,
				'priority'    => 15,
			)
		);

		// Add informational notice.
		$options_class::add_field(
			array(
				'id'       => 'images_optimizer_notice',
				'type'     => 'notice',
				'style'    => 'info',
				'name'     => '',
				'content'  => wp_kses(
					__( 'Use the WoodMart Images Optimizer to manually optimize images from the <strong>Media Library</strong> using the "Optimize" button in the WoodMart optimizer column. The quality and WebP settings above will be applied to all optimizations.', 'woodmart-images-optimizer' ),
					array(
						'a'      => array(
							'href'   => true,
							'target' => true,
						),
						'br'     => array(),
						'strong' => array(),
						'u'      => array(),
					)
				),
				'section'  => 'performance_images_optimizer',
				'priority' => 20,
			)
		);
	}

	/**
	 * Plugin activation.
	 */
	public function activate() {
		// Check if WoodMart theme is active during activation.
		if ( ! $this->is_woodmart_theme_active() ) {
			wp_die(
				esc_html__( 'WoodMart Images Optimizer requires WoodMart theme to be active. Please activate WoodMart theme first.', 'woodmart-images-optimizer' ),
				esc_html__( 'Plugin Activation Error', 'woodmart-images-optimizer' ),
				array( 'back_link' => true )
			);
		}
		
		// Clear any existing optimization schedules.
		wp_clear_scheduled_hook( 'xts_auto_optimize_image' );
	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {
		// Clear all scheduled optimization events.
		wp_clear_scheduled_hook( 'xts_auto_optimize_image' );
	}
}

// Initialize the plugin.
WoodMart_Images_Optimizer_Plugin::get_instance();
