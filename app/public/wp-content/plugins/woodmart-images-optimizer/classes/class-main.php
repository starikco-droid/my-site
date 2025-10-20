<?php
/**
 * Main class for WoodMart Images Optimizer plugin.
 *
 * @package WoodMart_Images_Optimizer
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for image optimizer functionality.
 */
class WoodMart_Images_Optimizer_Main {
	
	/**
	 * Optimizer instance.
	 *
	 * @var WoodMart_Images_Optimizer_Optimizer
	 */
	private $optimizer = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		
		// Define auto-optimization constant if not already defined.
		if ( ! defined( 'WOODMART_AUTO_OPTIMIZE_UPLOADS' ) ) {
			define( 'WOODMART_AUTO_OPTIMIZE_UPLOADS', false ); // Disabled for now
		}
		
		$this->optimizer = new WoodMart_Images_Optimizer_Optimizer();

		// Initialize the picture display for WebP replacement
		new WoodMart_Images_Optimizer_Picture_Display();

		add_action( 'init', array( $this, 'init' ) );
		
		// Register the scheduled optimization action.
		add_action( 'xts_auto_optimize_image', array( $this, 'scheduled_optimize_image' ), 10, 1 );
		
		// Add admin notices for optimization results.
		add_action( 'admin_notices', array( $this, 'display_optimization_notices' ) );
		
		// Clean up backup files when attachment is deleted.
		add_action( 'delete_attachment', array( $this, 'cleanup_backup_on_delete' ) );
	}

	/**
	 * Initialize the module.
	 */
	public function init() {
		// Add button to the media library list in a new column before data.
		add_filter( 'manage_media_columns', array( $this, 'add_column' ) );
		add_filter( 'manage_media_custom_column', array( $this, 'add_column_content' ), 10, 2 );

		// Register ajax action to optimize image.
		add_action( 'wp_ajax_xts_optimizer_run', array( $this, 'optimize_image' ) );

		// Register ajax action to restore backup.
		add_action( 'wp_ajax_xts_optimizer_restore', array( $this, 'restore_image' ) );

		// Register ajax action for bulk optimization.
		add_action( 'wp_ajax_xts_optimizer_bulk', array( $this, 'bulk_optimize_images' ) );

		// Add bulk actions to media library.
		add_filter( 'bulk_actions-upload', array( $this, 'add_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-upload', array( $this, 'handle_bulk_actions' ), 10, 3 );

		// Load script on media library page only.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Auto-optimize newly uploaded images.
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'auto_optimize_uploaded_image' ), 10, 2 );

		// Display admin notices for bulk operations.
		add_action( 'admin_notices', array( $this, 'display_optimization_notices' ) );

		// Clean up backup files when attachment is deleted.
		add_action( 'delete_attachment', array( $this, 'cleanup_backup_on_delete' ) );
	}

	/**
	 * Enqueue scripts for admin pages.
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();
		if ( ! $screen || 'upload' !== $screen->base ) {
			return;
		}

		// Enqueue CSS for enhanced styling
		wp_enqueue_style(
			'xts-optimizer-styles',
			WOODMART_IMAGES_OPTIMIZER_PLUGIN_URL . 'assets/css/optimizer-styles.css',
			array(),
			WOODMART_IMAGES_OPTIMIZER_VERSION
		);

		wp_enqueue_script(
			'xts-optimizer',
			WOODMART_IMAGES_OPTIMIZER_PLUGIN_URL . 'assets/js/scripts.js',
			array( 'jquery' ),
			WOODMART_IMAGES_OPTIMIZER_VERSION,
			true
		);
		wp_localize_script(
			'xts-optimizer',
			'xts_optimizer',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'xts_optimizer_nonce' ),
			)
		);
	}

	/**
	 * Add optimizer column to media library.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_column( $columns ) {
		$columns['xts_optimizer'] = esc_html__( 'WoodMart optimizer', 'woodmart' );
		return $columns;
	}

	/**
	 * Add content to optimizer column.
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id Post ID.
	 */
	public function add_column_content( $column_name, $post_id ) {
		if ( 'xts_optimizer' === $column_name ) {
			// Check if WoodMart token is available
			if ( ! $this->optimizer->is_token_available() ) {
				echo '<div class="xts-optimizer-no-token" style="color: #d63638; font-size: 11px; line-height: 1.3;">';
				echo '<span style="display: block; margin-bottom: 2px;">' . esc_html__( 'Activation required', 'woodmart-images-optimizer' ) . '</span>';
				$activation_url = admin_url( 'admin.php?page=xts_license' );
				echo '<span style="color: #666;">' . wp_kses(
					sprintf(
						/* translators: %s: link to WoodMart activation page */
						__( 'Please <a href="%s">activate WoodMart theme</a>', 'woodmart-images-optimizer' ),
						esc_url( $activation_url )
					),
					array(
						'a' => array(
							'href'   => true,
							'target' => true,
						),
					)
				) . '</span>';
				echo '</div>';
				return;
			}

			// Get the server file path of the image.
			$file_path = get_attached_file( $post_id );
			
			if ( $file_path && file_exists( $file_path ) ) {
				// Check if this is a supported image type.
				if ( ! $this->is_supported_image_type( $file_path, $post_id ) ) {
					echo '<span class="xts-optimizer-unsupported" style="color: #999; font-size: 11px;">' . esc_html__( 'Unsupported format', 'woodmart' ) . '</span>';
					return;
				}
				// Get optimization meta data.
				$optimization_meta = $this->optimizer->get_optimization_meta( $post_id );
				$backup_status = $this->optimizer->has_backup_by_id( $post_id );
				$scheduled_optimization = wp_next_scheduled( 'xts_auto_optimize_image', array( $post_id ) );
				
				echo '<div class="xts-optimizer-buttons">';
				
				// Display optimization status if available
				if ( $optimization_meta ) {
					echo '<div class="xts-optimization-info" style="margin-bottom: 5px; font-size: 11px;">';
					
					// Determine if optimized based on having compression ratio or backup
					$is_optimized_meta = ! empty( $optimization_meta['compression_ratio'] ) || ! empty( $optimization_meta['backup_exists'] );
					
					if ( $is_optimized_meta ) {
						echo '<span style="color: green;">✓ Optimized</span>';
						if ( ! empty( $optimization_meta['compression_ratio'] ) ) {
							echo ' (' . $optimization_meta['compression_ratio'] . '% smaller)';
						}
						if ( ! empty( $optimization_meta['timestamp'] ) ) {
							echo '<br><span style="color: #666;">' . human_time_diff( strtotime( $optimization_meta['timestamp'] ) ) . ' ago</span>';
						}
					} else {
						echo '<span style="color: red;">✗ Failed</span>';
					}
					
					echo '</div>';
				} elseif ( $scheduled_optimization ) {
					echo '<div class="xts-optimization-info" style="margin-bottom: 5px; font-size: 11px;">';
					echo '<span style="color: orange;">⏳ Auto-optimization scheduled</span>';
					echo '</div>';
				}
				
				// Only show Optimize button if image is not already optimized
				$is_optimized = $optimization_meta && ( ! empty( $optimization_meta['compression_ratio'] ) || ! empty( $optimization_meta['backup_exists'] ) );
				if ( ! $is_optimized ) {
					echo '<a href="#" class="xts-optimizer-button" data-id="' . esc_attr( $post_id ) . '">' . esc_html__( 'Optimize', 'woodmart' ) . '</a>';
				}
				
				if ( $backup_status['has_backup'] ) {
					echo '<a href="#" class="xts-restore-button" data-id="' . esc_attr( $post_id ) . '" title="Restore from: ' . esc_attr( $backup_status['backup_filename'] ) . '">' . esc_html__( 'Restore Backup', 'woodmart' ) . '</a>';
				}
				echo '</div>';
			} else {
				echo '<span class="xts-optimizer-error">' . esc_html__( 'File not found', 'woodmart' ) . '</span>';
			}
		}
	}

	/**
	 * Handle AJAX request to optimize image.
	 */
	public function optimize_image() {
		// Check nonce and permissions.
		check_ajax_referer( 'xts_optimizer_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to perform this action.', 'woodmart' ) );
		}

		// Check if token is available
		if ( ! $this->optimizer->is_token_available() ) {
			wp_send_json_error( esc_html__( 'WoodMart theme token is required for image optimization. Please ensure WoodMart theme is activated.', 'woodmart-images-optimizer' ) );
		}

		$image_id = isset( $_POST['image_id'] ) ? intval( $_POST['image_id'] ) : 0;

		if ( ! $image_id ) {
			wp_send_json_error( esc_html__( 'Invalid image ID.', 'woodmart' ) );
		}

		// Get the server file path of the image.
		$file_path = get_attached_file( $image_id );

		if ( ! $file_path || ! file_exists( $file_path ) ) {
			wp_send_json_error( esc_html__( 'Could not find the image file.', 'woodmart' ) );
		}

		// Check if this is a supported image type.
		if ( ! $this->is_supported_image_type( $file_path, $image_id ) ) {
			wp_send_json_error( esc_html__( 'Unsupported image format. Only JPEG, PNG, and WebP images are supported.', 'woodmart' ) );
		}

		$result = $this->optimizer->optimize( $file_path, $image_id );

		// Check for minimal optimization case and provide special handling
		if ( isset( $result['minimal_optimization'] ) && $result['minimal_optimization'] ) {
			wp_send_json_error(
				array(
					'message' => $result['replacement_message'],
					'compression_percentage' => $result['compression_percentage'],
					'file_size_before' => $result['file_size_before'],
					'file_size_after' => $result['file_size_after'],
					'size_reduction' => $result['size_reduction'],
					'minimal_optimization' => true,
				)
			);
		}

		// Return result - JavaScript will handle success/error display.
		wp_send_json_success(
			array(
				'message' => $result['message'] ?? esc_html__( 'Optimization request processed.', 'woodmart' ),
				'result'  => $result,
			)
		);
	}

	/**
	 * Handle AJAX request to restore image from backup.
	 */
	public function restore_image() {
		// Check nonce and permissions.
		check_ajax_referer( 'xts_optimizer_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to perform this action.', 'woodmart' ) );
		}

		$image_id = isset( $_POST['image_id'] ) ? intval( $_POST['image_id'] ) : 0;

		if ( ! $image_id ) {
			wp_send_json_error( esc_html__( 'Invalid image ID.', 'woodmart' ) );
		}

		// Get the server file path of the image.
		$file_path = get_attached_file( $image_id );

		if ( ! $file_path || ! file_exists( $file_path ) ) {
			wp_send_json_error( esc_html__( 'Could not find the image file.', 'woodmart' ) );
		}

		$result = $this->optimizer->restore_from_backup( $file_path, $image_id );

		if ( $result['error'] ) {
			wp_send_json_error( $result['message'] );
		}

		// Return result.
		wp_send_json_success(
			array(
				'message' => $result['message'],
				'result'  => $result,
			)
		);
	}

	/**
	 * Auto-optimize newly uploaded images.
	 *
	 * @param array $metadata      Attachment metadata.
	 * @param int   $attachment_id Attachment ID.
	 * @return array Modified metadata.
	 */
	public function auto_optimize_uploaded_image( $metadata, $attachment_id ) {
		// Only process images.
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return $metadata;
		}

		// Get the full file path.
		$file_path = get_attached_file( $attachment_id );

		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return $metadata;
		}

		// Check if this is a supported image type.
		if ( ! $this->is_supported_image_type( $file_path, $attachment_id ) ) {
			return $metadata;
		}

		// Check if auto-optimization is enabled via constant.
		if ( ! defined( 'WOODMART_AUTO_OPTIMIZE_UPLOADS' ) || ! WOODMART_AUTO_OPTIMIZE_UPLOADS ) {
			return $metadata;
		}

		// Check if this image was already optimized (prevent duplicate optimization).
		$optimization_meta = $this->optimizer->get_optimization_meta( $attachment_id );
		if ( $optimization_meta && ( ! empty( $optimization_meta['compression_ratio'] ) || ! empty( $optimization_meta['backup_exists'] ) ) ) {
			return $metadata;
		}

		// Check if optimization is already scheduled for this image.
		$scheduled_optimization = wp_next_scheduled( 'xts_auto_optimize_image', array( $attachment_id ) );
		if ( $scheduled_optimization ) {
			return $metadata;
		}

		// Schedule optimization in background to avoid blocking the upload process.
		wp_schedule_single_event( current_time( 'timestamp', true ) + 10, 'xts_auto_optimize_image', array( $attachment_id ) );

		return $metadata;
	}

	/**
	 * Perform scheduled image optimization.
	 *
	 * @param int $attachment_id Attachment ID.
	 */
	public function scheduled_optimize_image( $attachment_id ) {
		// Get the file path from attachment ID.
		$file_path = get_attached_file( $attachment_id );
		
		// Double-check the file still exists.
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return;
		}

		// Run the optimization.
		$result = $this->optimizer->optimize( $file_path, $attachment_id );

		// Log the result (optional, for debugging).
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			if ( $result['error'] ) {
				error_log( 'Auto-optimization failed for attachment ID ' . $attachment_id . ': ' . $result['message'] );
			} else {
				error_log( 'Auto-optimization succeeded for attachment ID ' . $attachment_id );
			}
		}

		// Store optimization result for admin notice display.
		if ( ! $result['error'] ) {
			$this->add_optimization_notice( $attachment_id, 'success', 'Image optimized successfully in the background.' );
		} else {
			// Check if this is a minimal optimization case
			if ( isset( $result['minimal_optimization'] ) && $result['minimal_optimization'] ) {
				$this->add_optimization_notice( $attachment_id, 'warning', 'Image is already well-optimized (less than 0.5% reduction possible).' );
			} else {
				$this->add_optimization_notice( $attachment_id, 'error', 'Auto-optimization failed: ' . $result['message'] );
			}
		}
	}

	/**
	 * Add optimization notice to display in admin.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $type          Notice type (success, error, warning).
	 * @param string $message       Notice message.
	 */
	private function add_optimization_notice( $attachment_id, $type, $message ) {
		$notices = get_transient( 'xts_optimizer_notices' );
		if ( ! is_array( $notices ) ) {
			$notices = array();
		}

		$notices[] = array(
			'attachment_id' => $attachment_id,
			'type'          => $type,
			'message'       => $message,
			'timestamp'     => time(),
		);

		// Keep only the last 10 notices to avoid memory issues.
		$notices = array_slice( $notices, -10 );

		set_transient( 'xts_optimizer_notices', $notices, 24 * HOUR_IN_SECONDS );
	}

	/**
	 * Display optimization notices in admin.
	 */
	public function display_optimization_notices() {
		$screen = get_current_screen();
		
		// Only show notices on upload or edit media screens.
		if ( ! $screen || ! in_array( $screen->base, array( 'upload', 'post' ), true ) ) {
			return;
		}

		// Display quota information if available
		if ( $this->optimizer->is_token_available() ) {
			$quota_info = $this->optimizer->get_formatted_quota();
			if ( $quota_info ) {
				printf(
					'<div class="notice notice-info"><p><strong>%s</strong> %s</p></div>',
					esc_html__( 'WoodMart Images Optimizer:', 'woodmart-images-optimizer' ),
					$quota_info
				);
			}
		}

		// Display bulk optimization error (no token)
		if ( isset( $_GET['bulk_optimize_error'] ) && 'no_token' === $_GET['bulk_optimize_error'] ) {
			printf(
				'<div class="notice notice-error is-dismissible"><p><strong>%s</strong> %s</p></div>',
				esc_html__( 'Bulk Optimization Failed:', 'woodmart-images-optimizer' ),
				esc_html__( 'WoodMart theme token is required for image optimization. Please ensure WoodMart theme is activated.', 'woodmart-images-optimizer' )
			);
		}

		// Display bulk optimization notices
		if ( isset( $_GET['bulk_optimize'] ) ) {
			$batch_id = sanitize_text_field( $_GET['bulk_optimize'] );
			$image_count = isset( $_GET['image_count'] ) ? intval( $_GET['image_count'] ) : 0;
			
			if ( $image_count > 0 ) {
				printf(
					'<div class="notice notice-info is-dismissible"><p><strong>%s</strong> %s <span id="bulk-progress">0</span>/%d</p><div id="bulk-progress-bar" style="background: #ddd; height: 20px; margin: 10px 0;"><div style="background: #0073aa; height: 100%%; width: 0; transition: width 0.3s;"></div></div></div>',
					esc_html__( 'Bulk Optimization in Progress:', 'woodmart' ),
					esc_html__( 'Processing image', 'woodmart' ),
					$image_count
				);
				
				// Add hidden field with batch info for JavaScript
				printf( '<script type="text/javascript">window.woodmartBulkOptimize = {batch_id: "%s", total: %d};</script>', esc_js( $batch_id ), $image_count );
			}
		}

		// Display bulk restore results
		if ( isset( $_GET['bulk_restored'] ) ) {
			$restored_count = intval( $_GET['bulk_restored'] );
			$error_count = isset( $_GET['bulk_errors'] ) ? intval( $_GET['bulk_errors'] ) : 0;
			
			if ( $restored_count > 0 ) {
				printf(
					'<div class="notice notice-success is-dismissible"><p><strong>%s</strong> %s</p></div>',
					esc_html__( 'Bulk Restore Complete:', 'woodmart' ),
					sprintf( esc_html( _n( '%d image restored successfully.', '%d images restored successfully.', $restored_count, 'woodmart' ) ), $restored_count )
				);
			}
			
			if ( $error_count > 0 ) {
				printf(
					'<div class="notice notice-warning is-dismissible"><p><strong>%s</strong> %s</p></div>',
					esc_html__( 'Bulk Restore Warnings:', 'woodmart' ),
					sprintf( esc_html( _n( '%d image could not be restored.', '%d images could not be restored.', $error_count, 'woodmart' ) ), $error_count )
				);
			}
		}

		$notices = get_transient( 'xts_optimizer_notices' );
		if ( ! is_array( $notices ) || empty( $notices ) ) {
			return;
		}

		// Clear old notices (older than 1 hour).
		$notices = array_filter( $notices, function( $notice ) {
			return ( time() - $notice['timestamp'] ) < HOUR_IN_SECONDS;
		});

		foreach ( $notices as $notice ) {
			$notice_type = $notice['type'];
			$class = 'notice notice-' . ( 'success' === $notice_type ? 'success' : ( 'warning' === $notice_type ? 'warning' : 'error' ) );
			$image_title = get_the_title( $notice['attachment_id'] );
			
			printf(
				'<div class="%1$s is-dismissible"><p><strong>%2$s:</strong> %3$s</p></div>',
				esc_attr( $class ),
				esc_html( $image_title ? $image_title : 'Image #' . $notice['attachment_id'] ),
				esc_html( $notice['message'] )
			);
		}

		// Clear notices after display.
		delete_transient( 'xts_optimizer_notices' );
	}

	/**
	 * Check if the image type is supported for optimization.
	 *
	 * @param string $file_path     Path to the image file.
	 * @param int    $attachment_id Attachment ID.
	 * @return bool True if supported, false otherwise.
	 */
	private function is_supported_image_type( $file_path, $attachment_id ) {
		// Check if this is actually an image attachment.
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return false;
		}

		// Get file extension.
		$file_extension = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );

		// Define supported image formats.
		$supported_formats = array(
			'jpg',
			'jpeg',
			'png',
		);

		// Check if the file extension is supported.
		if ( ! in_array( $file_extension, $supported_formats, true ) ) {
			return false;
		}

		// Additional check using WordPress function to get mime type.
		$mime_type = get_post_mime_type( $attachment_id );
		$supported_mime_types = array(
			'image/jpeg',
			'image/jpg',
			'image/png',
		);

		return in_array( $mime_type, $supported_mime_types, true );
	}

	/**
	 * Add bulk actions to media library.
	 *
	 * @param array $bulk_actions Existing bulk actions.
	 * @return array Modified bulk actions.
	 */
	public function add_bulk_actions( $bulk_actions ) {
		// Only add optimization action if token is available
		if ( $this->optimizer->is_token_available() ) {
			$bulk_actions['woodmart_optimize'] = esc_html__( 'Optimize with WoodMart', 'woodmart' );
		}
		$bulk_actions['woodmart_restore'] = esc_html__( 'Restore from optimizer backup', 'woodmart' );
		return $bulk_actions;
	}

	/**
	 * Handle bulk actions.
	 *
	 * @param string $redirect_to Redirect URL.
	 * @param string $doaction The action being taken.
	 * @param array  $post_ids Array of post IDs.
	 * @return string Modified redirect URL.
	 */
	public function handle_bulk_actions( $redirect_to, $doaction, $post_ids ) {
		if ( 'woodmart_optimize' === $doaction ) {
			// Check if token is available before processing
			if ( ! $this->optimizer->is_token_available() ) {
				$redirect_to = add_query_arg( array(
					'bulk_optimize_error' => 'no_token',
				), $redirect_to );
				return $redirect_to;
			}

			// Filter to only include supported image types
			$image_ids = array();
			foreach ( $post_ids as $post_id ) {
				$file_path = get_attached_file( $post_id );
				if ( $file_path && file_exists( $file_path ) && $this->is_supported_image_type( $file_path, $post_id ) ) {
					$image_ids[] = $post_id;
				}
			}

			if ( ! empty( $image_ids ) ) {
				// Store the IDs in a transient for AJAX processing
				$batch_id = uniqid( 'woodmart_bulk_' );
				set_transient( $batch_id, $image_ids, HOUR_IN_SECONDS );
				
				$redirect_to = add_query_arg( array(
					'bulk_optimize' => $batch_id,
					'image_count' => count( $image_ids ),
				), $redirect_to );
			}
		} elseif ( 'woodmart_restore' === $doaction ) {
			$restored_count = 0;
			$error_count = 0;

			foreach ( $post_ids as $post_id ) {
				$file_path = get_attached_file( $post_id );
				if ( $file_path && file_exists( $file_path ) ) {
					$restore_result = $this->optimizer->restore_from_backup( $file_path, $post_id );
					if ( ! $restore_result['error'] ) {
						$restored_count++;
					} else {
						$error_count++;
					}
				}
			}

			$redirect_to = add_query_arg( array(
				'bulk_restored' => $restored_count,
				'bulk_errors' => $error_count,
			), $redirect_to );
		}

		return $redirect_to;
	}

	/**
	 * Handle AJAX bulk optimization.
	 */
	public function bulk_optimize_images() {
		// Check nonce and permissions
		check_ajax_referer( 'xts_optimizer_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to perform this action.', 'woodmart' ) );
		}

		// Check if token is available
		if ( ! $this->optimizer->is_token_available() ) {
			wp_send_json_error( esc_html__( 'WoodMart theme token is required for bulk image optimization. Please ensure WoodMart theme is activated.', 'woodmart-images-optimizer' ) );
		}

		$batch_id = isset( $_POST['batch_id'] ) ? sanitize_text_field( $_POST['batch_id'] ) : '';
		$offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
		$batch_size = 10; // Process 5 images at a time

		if ( ! $batch_id ) {
			wp_send_json_error( esc_html__( 'Invalid batch ID.', 'woodmart' ) );
		}

		// Get the image IDs from transient
		$image_ids = get_transient( $batch_id );
		if ( false === $image_ids ) {
			wp_send_json_error( esc_html__( 'Batch not found or expired.', 'woodmart' ) );
		}

		$total_images = count( $image_ids );
		$batch_images = array_slice( $image_ids, $offset, $batch_size );
		$results = array();

		foreach ( $batch_images as $image_id ) {
			$file_path = get_attached_file( $image_id );
			if ( ! $file_path || ! file_exists( $file_path ) ) {
				$results[] = array(
					'id' => $image_id,
					'success' => false,
					'message' => esc_html__( 'File not found', 'woodmart' ),
				);
				continue;
			}

			$result = $this->optimizer->optimize( $file_path, $image_id );
			
			$results[] = array(
				'id' => $image_id,
				'success' => ! $result['error'],
				'message' => $result['message'] ?? ( $result['error'] ? esc_html__( 'Optimization failed', 'woodmart' ) : esc_html__( 'Optimized successfully', 'woodmart' ) ),
				'minimal_optimization' => isset( $result['minimal_optimization'] ) ? $result['minimal_optimization'] : false,
				'compression_percentage' => $result['compression_percentage'] ?? null,
			);
		}

		$processed = $offset + count( $batch_images );
		$is_complete = $processed >= $total_images;

		// Clean up transient if complete
		if ( $is_complete ) {
			delete_transient( $batch_id );
		}

		wp_send_json_success( array(
			'results' => $results,
			'processed' => $processed,
			'total' => $total_images,
			'complete' => $is_complete,
			'progress_percentage' => round( ( $processed / $total_images ) * 100, 1 ),
		) );
	}

	/**
	 * Clean up backup files when an attachment is permanently deleted.
	 *
	 * @param int $attachment_id The attachment ID being deleted.
	 */
	public function cleanup_backup_on_delete( $attachment_id ) {
		// Only process images.
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return;
		}

		// Get the original file path.
		$original_path = get_attached_file( $attachment_id );
		if ( ! $original_path ) {
			return;
		}

		// Check if backup exists using the optimizer's method.
		$backup_status = $this->optimizer->has_backup( $original_path );
		if ( ! $backup_status['has_backup'] ) {
			return;
		}

		// Delete the backup file.
		$backup_path = $backup_status['backup_path'];
		if ( file_exists( $backup_path ) ) {
			$deleted = unlink( $backup_path );
			
			// Log the result for debugging.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				if ( $deleted ) {
					error_log( 'Images Optimizer: Backup file deleted for attachment ID ' . $attachment_id . ': ' . basename( $backup_path ) );
				} else {
					error_log( 'Images Optimizer: Failed to delete backup file for attachment ID ' . $attachment_id . ': ' . basename( $backup_path ) );
				}
			}
		}

		// Delete WebP file if it exists.
		$meta_data = $this->optimizer->get_optimization_meta( $attachment_id );
		if ( $meta_data && ! empty( $meta_data['webp_created'] ) && ! empty( $meta_data['webp_filename'] ) ) {
			$webp_path = $original_path . '.webp';
			
			if ( file_exists( $webp_path ) ) {
				$webp_deleted = unlink( $webp_path );
				
				// Log the result for debugging.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					if ( $webp_deleted ) {
						error_log( 'Images Optimizer: WebP file deleted for attachment ID ' . $attachment_id . ': ' . basename( $webp_path ) );
					} else {
						error_log( 'Images Optimizer: Failed to delete WebP file for attachment ID ' . $attachment_id . ': ' . basename( $webp_path ) );
					}
				}
			}
		}

		// Clean up optimization metadata.
		$this->optimizer->clear_optimization_meta( $attachment_id );
	}
}
