<?php
/**
 * Admin class.
 *
 * @package woodmart
 */

namespace XTS\Modules\Waitlist;

use WP_User_Query;
use XTS\Singleton;
use XTS\Modules\Waitlist\DB_Storage;
use XTS\Modules\Waitlist\List_Table\Waitlist_Table;
use XTS\Modules\Waitlist\List_Table\Users_Table;

/**
 * Admin class.
 */
class Admin extends Singleton {
	/**
	 * DB_Storage instance.
	 *
	 * @var DB_Storage
	 */
	protected $db_storage;

	/**
	 * Page slug for the waitlist admin page.
	 *
	 * @var string
	 */
	public $waitlist_page;

	/**
	 * Constructor.
	 */
	public function init() {
		if ( ! woodmart_get_opt( 'waitlist_enabled' ) || ! woodmart_woocommerce_installed() ) {
			return;
		}

		$this->db_storage = DB_Storage::get_instance();

		add_action( 'init', array( $this, 'delete_waitlist' ) );

		add_action( 'admin_menu', array( $this, 'register_waitlist_page' ) );

		add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );
	}

	/**
	 * Register waitlist page on admin panel.
	 *
	 * @return void
	 */
	public function register_waitlist_page() {
		$this->waitlist_page = add_submenu_page(
			'edit.php?post_type=product',
			esc_html__( 'Waitlists', 'woodmart' ),
			esc_html__( 'Waitlists', 'woodmart' ),
			apply_filters( 'woodmart_capability_menu_page', 'edit_products', 'xts-waitlist-page' ),
			'xts-waitlist-page',
			array( $this, 'render_waitlist_page' )
		);

		add_action( 'load-' . $this->waitlist_page, array( $this, 'waitlist_screen_options' ) );
	}

	/**
	 * Render waitlist page on admin panel.
	 *
	 * @codeCoverageIgnore
	 */
	public function render_waitlist_page() {
		$list_table = new Waitlist_Table();

		if ( ! empty( $_GET['tab'] ) && 'users' === $_GET['tab'] ) {
			$list_table = new Users_Table();

			$product_id   = isset( $_GET['product_id'] ) ? intval( $_GET['product_id'] ) : false;
			$variation_id = isset( $_GET['variation_id'] ) ? intval( $_GET['variation_id'] ) : false;

			$product_id = $variation_id ? $variation_id : $product_id;

			if ( $product_id ) {
				$product      = wc_get_product( $product_id );
				$product_name = $product->get_name();
			}
		}

		if ( $list_table instanceof Waitlist_Table ) {
			wp_enqueue_style( 'woocommerce_admin_styles' );
		}

		$list_table->prepare_items();
		?>
			<div class="wrap xts-post-type-table xts-wtl-page-wrap">
				<h2 class="wp-heading-inline"><?php echo esc_html__( 'Waitlists', 'woodmart' ); ?></h2>

				<?php if ( ! empty( $product_name ) ) : ?>
					<h3>
						<?php echo esc_html( $product_name ); ?>
					</h3>
				<?php endif; ?>

				<form id="xts-waitlist-settings-page-form" method="get" action="">
					<input type="hidden" name="page" value="xts-waitlist-page" />
					<input type="hidden" name="post_type" value="product" />
					<?php
					if ( $list_table instanceof Waitlist_Table ) {
						$list_table->search_box( esc_html__( 'Search', 'woodmart' ), 'xts-search' );
					}

					$list_table->display();
					?>
				</form>
			</div>
		<?php
	}

	/**
	 * Add screen options to waitlist admin page.
	 */
	public function waitlist_screen_options() {
		$screen = get_current_screen();

		if ( ! is_object( $screen ) || $screen->id !== $this->waitlist_page ) {
			return;
		}

		add_screen_option(
			'per_page',
			array(
				'label'   => esc_html__( 'Number of items per page', 'woodmart' ),
				'default' => 20,
				'option'  => 'waitlist_per_page',
			)
		);
	}

	/**
	 * Save screen options.
	 *
	 * @param mixed  $screen_option The value to save instead of the option value.
	 *                              Default false (to skip saving the current option).
	 * @param string $option        The option name.
	 * @param int    $value         The option value.
	 */
	public function set_screen_option( $screen_option, $option, $value ) {
		if ( 'waitlist_per_page' === $option ) {
			return $value;
		}

		return $screen_option;
	}

	public function delete_waitlist() {
		if ( ! isset( $_GET['action'] ) || 'woodmart_delete_waitlist' !== $_GET['action'] ||  ! isset( $_GET['token'] ) ||  ! isset( $_GET['product_id'] ) ) { //phpcs:ignore
			return;
		}

		$token = woodmart_clean( $_GET['token'] ); //phpcs:ignore.

		$this->db_storage->unsubscribe_by_token( $token );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'       => 'xts-waitlist-page',
					'tab'        => 'users',
					'product_id' => $_GET['product_id'],
				),
				admin_url( 'edit.php?post_type=product' )
			)
		);
		die();
	}
}

Admin::get_instance();
