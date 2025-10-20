<?php
/**
 * Setup wizard class.
 *
 * @package woodmart
 */

namespace XTS\Admin\Modules;

use XTS\Singleton;
use XTS\Admin\Modules\Options as ThemeSettings;

if ( ! defined( 'WOODMART_THEME_DIR' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * Setup wizard class.
 */
class Setup_Wizard extends Singleton {
	/**
	 * Available pages.
	 *
	 * @var array
	 */
	public $available_pages = array();

	/**
	 * Constructor.
	 */
	public function init() {
		if ( isset( $_GET['skip_setup'] ) ) {
			update_option( 'woodmart_setup_status', 'done', false );
		}

		if ( 'done' !== get_option( 'woodmart_setup_status' ) ) { // phpcs:ignore
			add_action( 'admin_init', array( $this, 'prevent_plugins_redirect' ), 1 );
			do_action( 'woodmart_setup_wizard' );
		}

		if ( defined( 'DOING_AJAX' ) || isset( $_GET['page'] ) && ( 'xts_dashboard' === $_GET['page'] || 'tgmpa-install-plugins' === $_GET['page'] ) ) {
			add_action( 'admin_init', array( $this, 'prevent_plugins_redirect' ), 1 );
		}

		add_action( 'admin_init', array( $this, 'theme_activation_redirect' ) );

		add_action( 'admin_init', array( $this, 'set_page_builder' ) );

		add_filter( 'leadin_impact_code', array( $this, 'get_hubspot_affiliate_code' ) );
	}

	/**
	 * Setup available pages.
	 *
	 * @return void
	 */
	public function set_available_pages() {
		$this->available_pages = array(
			'welcome'           => esc_html__( 'Welcome', 'woodmart' ),
			'activation'        => esc_html__( 'Activation', 'woodmart' ),
			'child-theme'       => esc_html__( 'Child theme', 'woodmart' ),
			'page-builder'      => esc_html__( 'Page builder', 'woodmart' ),
			'plugins'           => esc_html__( 'Plugins', 'woodmart' ),
			'prebuilt-websites' => esc_html__( 'Prebuilt websites', 'woodmart' ),
			'done'              => esc_html__( 'Done', 'woodmart' ),
		);
	}

	/**
	 * Prevent plugins redirect.
	 */
	public function prevent_plugins_redirect() {
		delete_transient( '_revslider_welcome_screen_activation_redirect' );
		delete_transient( '_vc_page_welcome_redirect' );
		delete_transient( 'elementor_activation_redirect' );
		add_filter( 'woocommerce_enable_setup_wizard', '__return_false' );
		remove_action( 'admin_menu', 'vc_menu_page_build' );
		remove_action( 'network_admin_menu', 'vc_network_menu_page_build' );
		remove_action( 'vc_activation_hook', 'vc_page_welcome_set_redirect' );
		remove_action( 'admin_init', 'vc_page_welcome_redirect' );
	}

	/**
	 * Hubspot affiliate.
	 */
	public function get_hubspot_affiliate_code() {
		return '7m0A9V';
	}

	/**
	 * Redirect to setup wizard after theme activated.
	 */
	public function theme_activation_redirect() {
		if ( 'done' === get_option( 'woodmart_setup_status' ) ) {
			return;
		}

		global $pagenow;

		$args = array(
			'page' => 'xts_dashboard',
			'tab'  => 'wizard',
		);

		if ( 'themes.php' === $pagenow && is_admin() && isset( $_GET['activated'] ) ) { // phpcs:ignore
			wp_safe_redirect( esc_url_raw( add_query_arg( $args, admin_url( 'admin.php' ) ) ) );
		}
	}

	/**
	 * Template.
	 */
	public function setup_wizard_template() {
		if ( 'done' === get_option( 'woodmart_setup_status' ) ) {
			return;
		}

		$this->set_available_pages();

		wp_enqueue_script( 'wd-setup-wizard', WOODMART_ASSETS . '/js/wizard.js', array(), WOODMART_VERSION, true );

		$page = 'welcome';

		if ( ! empty( $_GET['step'] ) && in_array( $_GET['step'], array( 'activation', 'child-theme',  'page-builder', 'plugins', 'prebuilt-websites', 'done') ) ) { // phpcs:ignore
			$page = trim( wp_unslash( $_GET['step'] ) ); // phpcs:ignore
		}

		$this->show_page( $page );
	}

	/**
	 * Show page.
	 *
	 * @param string $name Template file name.
	 */
	public function show_page( $name ) {
		?>
		<div class="xts-setup-wizard-wrap xts-theme-style">
			<div class="xts-setup-wizard">
				<div class="xts-wizard-nav">
					<?php $this->show_part( 'sidebar' ); ?>
				</div>

				<div class="xts-wizard-content">
					<?php $this->show_part( $name ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get previous page button.
	 *
	 * @param string  $page Page slug.
	 * @param string  $builder Builder name.
	 * @param boolean $disabled Is button disabled.
	 */
	public function get_next_button( $page, $builder = '', $disabled = false ) {
		$classes = '';
		$url     = $this->get_page_url( $page );

		if ( $builder ) {
			$url     .= '&wd_builder=' . $builder;
			$classes .= ' xts-' . $builder;
		}

		if ( 'elementor' === $builder ) {
			$classes .= ' xts-shown';
		} elseif ( 'gutenberg' === $builder ) {
			$classes .= ' xts-hidden';
		} elseif ( 'wpb' === $builder ) {
			$classes .= ' xts-hidden';
		}

		if ( $disabled ) {
			$classes .= ' xts-disabled';
		}

		?>
		<a class="xts-btn xts-color-primary xts-next<?php echo esc_attr( $classes ); ?>" href="<?php echo esc_url( $url ); ?>">
			<?php esc_html_e( 'Continue', 'woodmart' ); ?>
		</a>
		<?php
	}

	/**
	 * Get skip page button.
	 *
	 * @param string $page Page slug.
	 */
	public function get_skip_button( $page ) {
		?>
		<a class="xts-inline-btn xts-color-primary xts-skip" href="<?php echo esc_url( $this->get_page_url( $page ) ); ?>">
			<?php esc_html_e( 'Skip this step', 'woodmart' ); ?>
		</a>
		<?php
	}

	/**
	 * Show template part.
	 *
	 * @param string $name Template file name.
	 */
	public function show_part( $name ) {
		include_once get_parent_theme_file_path( WOODMART_FRAMEWORK . '/admin/modules/setup-wizard/templates/' . $name . '.php' );
	}

	/**
	 * Is active page.
	 *
	 * @param string $name Page name.
	 */
	public function is_active_page( $name ) {
		$page = 'welcome';

		if ( isset( $_GET['step'] ) && ! empty( $_GET['step'] ) ) { // phpcs:ignore
			$page = trim( wp_unslash( $_GET['step'] ) ); // phpcs:ignore
		}

		return $name === $page; // phpcs:ignore
	}

	/**
	 * Get page url.
	 *
	 * @param string $name Page name.
	 */
	public function get_page_url( $name ) {
		return admin_url( 'admin.php?page=xts_dashboard&tab=wizard&step=' . $name ); // phpcs:ignore
	}

	/**
	 * Get image url.
	 *
	 * @param string $name Image name.
	 */
	public function get_image_url( $name ) {
		return WOODMART_THEME_DIR . '/inc/admin/modules/setup-wizard/images/' . $name;
	}

	/**
	 * Get plugin image url.
	 *
	 * @param string $name Image name.
	 */
	public function get_plugin_image_url( $name ) {
		return WOODMART_THEME_DIR . '/inc/admin/assets/images/plugins/' . $name;
	}

	/**
	 * Is setup wizard.
	 *
	 * @return bool
	 */
	public function is_setup() {
		return isset( $_GET['tab'] ) && 'wizard' === $_GET['tab']; //phpcs:ignore
	}

	/**
	 * Set page builder.
	 */
	public function set_page_builder() {
		if ( ! $this->is_setup() || ! isset( $_GET['step'] ) || 'plugins' !== $_GET['step'] || ! isset( $_GET['wd_builder'] ) ) { // phpcs:ignore
			return;
		}

		$builder = sanitize_text_field( wp_unslash( $_GET['wd_builder'] ) ); // phpcs:ignore

		if ( $builder ) {
			global $xts_woodmart_options;

			$options = ThemeSettings::get_instance();

			if ( 'gutenberg' === $builder ) {
				$xts_woodmart_options['current_builder']               = 'native';
				$xts_woodmart_options['gutenberg_blocks']              = true;
				$xts_woodmart_options['enable_gutenberg_for_products'] = true;
			} else {
				$xts_woodmart_options['current_builder']               = 'external';
				$xts_woodmart_options['gutenberg_blocks']              = false;
				$xts_woodmart_options['enable_gutenberg_for_products'] = false;
			}

			$options->update_options( $xts_woodmart_options );
		}
	}
}

Setup_Wizard::get_instance();
