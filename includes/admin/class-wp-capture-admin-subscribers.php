<?php
/**
 * Admin page for managing local subscribers.
 *
 * @package WP_Capture
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Capture_Admin_Subscribers class.
 */
class WP_Capture_Admin_Subscribers {

	/**
	 * The main plugin instance.
	 *
	 * @var WP_Capture
	 */
	private $plugin;

	/**
	 * The main admin instance.
	 *
	 * @var WP_Capture_Admin
	 */
	private $admin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param WP_Capture       $plugin The main plugin instance.
	 * @param WP_Capture_Admin $admin  The main admin instance.
	 */
	public function __construct( WP_Capture $plugin, WP_Capture_Admin $admin ) {
		$this->plugin = $plugin;
		$this->admin  = $admin;

		// Hook into admin_init to handle actions
		add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
	}

	/**
	 * Handle admin actions like delete, export, etc.
	 */
	public function handle_admin_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle single subscriber deletion
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['subscriber_id'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'delete_subscriber_' . $_GET['subscriber_id'] ) ) {
				wp_die( __( 'Security check failed', 'capture' ) );
			}

			$subscriber = WP_Capture_Subscriber::get_by_id( intval( $_GET['subscriber_id'] ) );
			if ( $subscriber && $subscriber->delete() ) {
				add_action( 'admin_notices', function() {
					echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Subscriber deleted successfully.', 'capture' ) . '</p></div>';
				});
			} else {
				add_action( 'admin_notices', function() {
					echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Failed to delete subscriber.', 'capture' ) . '</p></div>';
				});
			}

			// Redirect to avoid resubmission
			wp_redirect( admin_url( 'admin.php?page=wp-capture-subscribers' ) );
			exit;
		}

		// Handle CSV export
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'export' ) {
			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'export_subscribers' ) ) {
				wp_die( __( 'Security check failed', 'capture' ) );
			}

			$this->export_csv();
			exit;
		}

		// Handle bulk actions
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'bulk_delete' && isset( $_POST['subscriber_ids'] ) ) {
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'bulk_action_subscribers' ) ) {
				wp_die( __( 'Security check failed', 'capture' ) );
			}

			$deleted_count = 0;
			foreach ( $_POST['subscriber_ids'] as $id ) {
				$subscriber = WP_Capture_Subscriber::get_by_id( intval( $id ) );
				if ( $subscriber && $subscriber->delete() ) {
					$deleted_count++;
				}
			}

			add_action( 'admin_notices', function() use ( $deleted_count ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( '%d subscriber(s) deleted successfully.', 'capture' ), $deleted_count ) . '</p></div>';
			});

			// Redirect to avoid resubmission
			wp_redirect( admin_url( 'admin.php?page=wp-capture-subscribers' ) );
			exit;
		}
	}

	/**
	 * Display the subscribers admin page.
	 */
	public function display_page() {
		// Get filter parameters
		$search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
		$form_id = isset( $_GET['form_id'] ) ? sanitize_text_field( $_GET['form_id'] ) : '';
		$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
		$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( $_GET['date_from'] ) : '';
		$date_to = isset( $_GET['date_to'] ) ? sanitize_text_field( $_GET['date_to'] ) : '';
		
		// Get pagination parameters
		$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$per_page = 20;

		// Build query args
		$args = array(
			'page' => $current_page,
			'per_page' => $per_page,
		);

		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}
		if ( ! empty( $form_id ) ) {
			$args['form_id'] = $form_id;
		}
		if ( ! empty( $status ) ) {
			$args['status'] = $status;
		}
		if ( ! empty( $date_from ) ) {
			$args['date_from'] = $date_from;
		}
		if ( ! empty( $date_to ) ) {
			$args['date_to'] = $date_to;
		}

		// Get subscribers
		$result = WP_Capture_Subscriber::get_subscribers( $args );
		$subscribers = $result['subscribers'];
		$total = $result['total'];
		$total_pages = $result['pages'];

		// Get unique form IDs for filter dropdown
		$form_ids = $this->get_unique_form_ids();

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php _e( 'Subscribers', 'capture' ); ?></h1>
			
			<?php if ( WP_Capture_Database::subscribers_table_exists() ) : ?>
				<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=wp-capture-subscribers&action=export' ), 'export_subscribers' ); ?>" class="page-title-action">
					<?php _e( 'Export CSV', 'capture' ); ?>
				</a>
			<?php endif; ?>

			<hr class="wp-header-end">

			<!-- Filters -->
			<div class="wp-capture-filters" style="margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
				<form method="get" action="">
					<input type="hidden" name="page" value="wp-capture-subscribers">
					
					<table class="form-table" style="margin: 0;">
						<tr>
							<td style="padding: 0 15px 0 0;">
								<label for="search"><?php _e( 'Search:', 'capture' ); ?></label><br>
								<input type="text" id="search" name="search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php _e( 'Email or name...', 'capture' ); ?>" style="width: 200px;">
							</td>
							<td style="padding: 0 15px;">
								<label for="form_id"><?php _e( 'Form ID:', 'capture' ); ?></label><br>
								<select id="form_id" name="form_id" style="width: 150px;">
									<option value=""><?php _e( 'All Forms', 'capture' ); ?></option>
									<?php foreach ( $form_ids as $fid ) : ?>
										<option value="<?php echo esc_attr( $fid ); ?>" <?php selected( $form_id, $fid ); ?>>
											<?php echo esc_html( $fid ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
							<td style="padding: 0 15px;">
								<label for="status"><?php _e( 'Status:', 'capture' ); ?></label><br>
								<select id="status" name="status" style="width: 120px;">
									<option value=""><?php _e( 'All Status', 'capture' ); ?></option>
									<option value="active" <?php selected( $status, 'active' ); ?>><?php _e( 'Active', 'capture' ); ?></option>
									<option value="unsubscribed" <?php selected( $status, 'unsubscribed' ); ?>><?php _e( 'Unsubscribed', 'capture' ); ?></option>
								</select>
							</td>
							<td style="padding: 0 15px;">
								<label for="date_from"><?php _e( 'Date From:', 'capture' ); ?></label><br>
								<input type="date" id="date_from" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" style="width: 140px;">
							</td>
							<td style="padding: 0 15px;">
								<label for="date_to"><?php _e( 'Date To:', 'capture' ); ?></label><br>
								<input type="date" id="date_to" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" style="width: 140px;">
							</td>
							<td style="padding: 0;">
								<label>&nbsp;</label><br>
								<input type="submit" class="button" value="<?php _e( 'Filter', 'capture' ); ?>">
								<a href="<?php echo admin_url( 'admin.php?page=wp-capture-subscribers' ); ?>" class="button"><?php _e( 'Reset', 'capture' ); ?></a>
							</td>
						</tr>
					</table>
				</form>
			</div>

			<?php if ( ! WP_Capture_Database::subscribers_table_exists() ) : ?>
				<div class="notice notice-warning">
					<p><?php _e( 'Subscribers table does not exist. The plugin may need to be reactivated.', 'capture' ); ?></p>
				</div>
			<?php elseif ( empty( $subscribers ) ) : ?>
				<div class="notice notice-info">
					<p>
						<?php if ( $search || $form_id || $status || $date_from || $date_to ) : ?>
							<?php _e( 'No subscribers found matching your filters.', 'capture' ); ?>
						<?php else : ?>
							<?php _e( 'No subscribers found. Start capturing emails with your forms!', 'capture' ); ?>
						<?php endif; ?>
					</p>
				</div>
			<?php else : ?>

				<!-- Summary -->
				<div class="wp-capture-summary" style="margin: 20px 0;">
					<p>
						<?php printf( __( 'Showing %d subscriber(s) out of %d total.', 'capture' ), count( $subscribers ), $total ); ?>
					</p>
				</div>

				<!-- Bulk Actions Form -->
				<form method="post" action="">
					<?php wp_nonce_field( 'bulk_action_subscribers' ); ?>
					<input type="hidden" name="action" value="bulk_delete">

					<!-- Bulk Actions -->
					<div class="tablenav top">
						<div class="alignleft actions">
							<select name="bulk_action">
								<option value=""><?php _e( 'Bulk Actions', 'capture' ); ?></option>
								<option value="delete"><?php _e( 'Delete', 'capture' ); ?></option>
							</select>
							<input type="submit" class="button action" value="<?php _e( 'Apply', 'capture' ); ?>" onclick="return confirm('<?php _e( 'Are you sure you want to delete selected subscribers?', 'capture' ); ?>');">
						</div>

						<!-- Pagination -->
						<?php if ( $total_pages > 1 ) : ?>
							<div class="tablenav-pages">
								<?php
								$pagination_args = array(
									'base' => add_query_arg( 'paged', '%#%' ),
									'format' => '',
									'current' => $current_page,
									'total' => $total_pages,
									'prev_text' => '&laquo;',
									'next_text' => '&raquo;',
								);
								echo paginate_links( $pagination_args );
								?>
							</div>
						<?php endif; ?>
					</div>

					<!-- Subscribers Table -->
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<td class="manage-column column-cb check-column">
									<input type="checkbox" id="cb-select-all">
								</td>
								<th class="manage-column"><?php _e( 'Email', 'capture' ); ?></th>
								<th class="manage-column"><?php _e( 'Name', 'capture' ); ?></th>
								<th class="manage-column"><?php _e( 'Form ID', 'capture' ); ?></th>
								<th class="manage-column"><?php _e( 'Date Subscribed', 'capture' ); ?></th>
								<th class="manage-column"><?php _e( 'Status', 'capture' ); ?></th>
								<th class="manage-column"><?php _e( 'Source URL', 'capture' ); ?></th>
								<th class="manage-column"><?php _e( 'Actions', 'capture' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $subscribers as $subscriber ) : ?>
								<tr>
									<th scope="row" class="check-column">
										<input type="checkbox" name="subscriber_ids[]" value="<?php echo esc_attr( $subscriber->id ); ?>">
									</th>
									<td><strong><?php echo esc_html( $subscriber->email ); ?></strong></td>
									<td><?php echo esc_html( $subscriber->name ?: '—' ); ?></td>
									<td><?php echo esc_html( $subscriber->form_id ?: '—' ); ?></td>
									<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $subscriber->date_subscribed ) ) ); ?></td>
									<td>
										<span class="wp-capture-status wp-capture-status-<?php echo esc_attr( $subscriber->status ); ?>">
											<?php echo esc_html( ucfirst( $subscriber->status ) ); ?>
										</span>
									</td>
									<td>
										<?php if ( $subscriber->source_url ) : ?>
											<a href="<?php echo esc_url( $subscriber->source_url ); ?>" target="_blank" title="<?php echo esc_attr( $subscriber->source_url ); ?>">
												<?php echo esc_html( wp_parse_url( $subscriber->source_url, PHP_URL_HOST ) ); ?>
											</a>
										<?php else : ?>
											—
										<?php endif; ?>
									</td>
									<td>
										<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=wp-capture-subscribers&action=delete&subscriber_id=' . $subscriber->id ), 'delete_subscriber_' . $subscriber->id ); ?>" 
										   class="button button-small" 
										   onclick="return confirm('<?php _e( 'Are you sure you want to delete this subscriber?', 'capture' ); ?>');">
											<?php _e( 'Delete', 'capture' ); ?>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<!-- Bottom pagination -->
					<?php if ( $total_pages > 1 ) : ?>
						<div class="tablenav bottom">
							<div class="tablenav-pages">
								<?php echo paginate_links( $pagination_args ); ?>
							</div>
						</div>
					<?php endif; ?>

				</form>

			<?php endif; ?>

		</div>

		<style>
		.wp-capture-status {
			padding: 2px 8px;
			border-radius: 3px;
			font-size: 11px;
			font-weight: bold;
			text-transform: uppercase;
		}
		.wp-capture-status-active {
			background: #d4edda;
			color: #155724;
		}
		.wp-capture-status-unsubscribed {
			background: #f8d7da;
			color: #721c24;
		}
		#cb-select-all {
			margin: 0;
		}
		</style>

		<script>
		jQuery(document).ready(function($) {
			// Handle select all checkbox
			$('#cb-select-all').on('change', function() {
				$('input[name="subscriber_ids[]"]').prop('checked', this.checked);
			});
		});
		</script>
		<?php
	}

	/**
	 * Export subscribers as CSV.
	 */
	private function export_csv() {
		// Get filter parameters from URL
		$search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
		$form_id = isset( $_GET['form_id'] ) ? sanitize_text_field( $_GET['form_id'] ) : '';
		$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
		$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( $_GET['date_from'] ) : '';
		$date_to = isset( $_GET['date_to'] ) ? sanitize_text_field( $_GET['date_to'] ) : '';

		// Build query args for export (get all matching records)
		$args = array( 'per_page' => -1 );

		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}
		if ( ! empty( $form_id ) ) {
			$args['form_id'] = $form_id;
		}
		if ( ! empty( $status ) ) {
			$args['status'] = $status;
		}
		if ( ! empty( $date_from ) ) {
			$args['date_from'] = $date_from;
		}
		if ( ! empty( $date_to ) ) {
			$args['date_to'] = $date_to;
		}

		// Get all subscribers matching filters
		$result = WP_Capture_Subscriber::get_subscribers( $args );
		$subscribers = $result['subscribers'];

		// Set headers for CSV download
		$filename = 'wp-capture-subscribers-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';
		
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Create file pointer
		$output = fopen( 'php://output', 'w' );

		// Add CSV headers
		fputcsv( $output, array(
			__( 'Email', 'capture' ),
			__( 'Name', 'capture' ),
			__( 'Form ID', 'capture' ),
			__( 'Date Subscribed', 'capture' ),
			__( 'Status', 'capture' ),
			__( 'Source URL', 'capture' ),
		) );

		// Add subscriber data
		foreach ( $subscribers as $subscriber ) {
			fputcsv( $output, array(
				$subscriber->email,
				$subscriber->name,
				$subscriber->form_id,
				$subscriber->date_subscribed,
				$subscriber->status,
				$subscriber->source_url,
			) );
		}

		fclose( $output );
	}

	/**
	 * Get unique form IDs for filter dropdown.
	 *
	 * @return array Array of unique form IDs.
	 */
	private function get_unique_form_ids() {
		global $wpdb;
		$table_name = WP_Capture_Database::get_subscribers_table_name();
		
		$results = $wpdb->get_col( "SELECT DISTINCT form_id FROM {$table_name} WHERE form_id IS NOT NULL AND form_id != '' ORDER BY form_id" );
		
		return $results ?: array();
	}
} 