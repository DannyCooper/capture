<?php
/**
 * Admin page for managing local subscribers.
 *
 * @package Capture
 * @since 1.0.0
 */

namespace Capture;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin_Subscribers class.
 */
class Admin_Subscribers {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Hook into admin_init to handle actions.
		add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
	}

	/**
	 * Handle admin actions like delete, export, etc.
	 */
	public function handle_admin_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle single subscriber deletion.
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['subscriber_id'] ) ) {
			$nonce         = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
			$subscriber_id = isset( $_GET['subscriber_id'] ) ? sanitize_text_field( wp_unslash( $_GET['subscriber_id'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce, 'delete_subscriber_' . $subscriber_id ) ) {
				wp_die( esc_html__( 'Security check failed', 'capture' ) );
			}

			$subscriber = Subscriber::get_by_id( intval( $subscriber_id ) );
			if ( $subscriber && $subscriber->delete() ) {
				add_action(
					'admin_notices',
					function () {
						echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Subscriber deleted successfully.', 'capture' ) . '</p></div>';
					}
				);
			} else {
				add_action(
					'admin_notices',
					function () {
						echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete subscriber.', 'capture' ) . '</p></div>';
					}
				);
			}

			// Redirect to avoid resubmission.
			wp_redirect( admin_url( 'admin.php?page=capture-subscribers' ) );
			exit;
		}

		// Handle CSV export.
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'export' ) {
			$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce, 'export_subscribers' ) ) {
				wp_die( esc_html__( 'Security check failed', 'capture' ) );
			}

			$this->export_csv();
			exit;
		}

		// Handle bulk actions.
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'bulk_delete' && isset( $_POST['subscriber_ids'] ) ) {
			$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'bulk_action_subscribers' ) ) {
				wp_die( esc_html__( 'Security check failed', 'capture' ) );
			}

			$deleted_count  = 0;
			$subscriber_ids = isset( $_POST['subscriber_ids'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['subscriber_ids'] ) ) : array();
			foreach ( $subscriber_ids as $id ) {
				$subscriber = Subscriber::get_by_id( intval( $id ) );
				if ( $subscriber && $subscriber->delete() ) {
					++$deleted_count;
				}
			}

			add_action(
				'admin_notices',
				function () use ( $deleted_count ) {
					/* translators: %d: Number of subscribers */
					echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( '%d subscriber(s) deleted successfully.', 'capture' ), intval( $deleted_count ) ) . '</p></div>';
				}
			);

			// Redirect to avoid resubmission.
			wp_safe_redirect( admin_url( 'admin.php?page=capture-subscribers' ) );
			exit;
		}
	}

	/**
	 * Display the subscribers admin page.
	 */
	public function display_page() {
		// Get filter parameters.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are read-only filter parameters, no nonce needed.
		$search    = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are read-only filter parameters, no nonce needed.
		$form_id   = isset( $_GET['form_id'] ) ? sanitize_text_field( wp_unslash( $_GET['form_id'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are read-only filter parameters, no nonce needed.
		$status    = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are read-only filter parameters, no nonce needed.
		$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are read-only filter parameters, no nonce needed.
		$date_to   = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';

		// Get pagination parameters.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is a read-only pagination parameter, no nonce needed.
		$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$per_page     = 20;

		// Build query args.
		$args = array(
			'page'     => $current_page,
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

		// Get subscribers.
		$result      = Subscriber::get_subscribers( $args );
		$subscribers = $result['subscribers'];
		$total       = $result['total'];
		$total_pages = $result['pages'];

		// Get unique form IDs for filter dropdown.
		$form_ids = $this->get_unique_form_ids();

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Subscribers', 'capture' ); ?></h1>
			
			<?php if ( Database::subscribers_table_exists() ) : ?>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=capture-subscribers&action=export' ), 'export_subscribers' ) ); ?>" class="page-title-action">
					<?php esc_html_e( 'Export CSV', 'capture' ); ?>
				</a>
			<?php endif; ?>

			<hr class="wp-header-end">

			<!-- Filters -->
			<div class="capture-filters" style="margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
				<form method="get" action="">
					<input type="hidden" name="page" value="capture-subscribers">
					
					<table class="form-table" style="margin: 0;">
						<tr>
							<td style="padding: 0 15px 0 0;">
								<label for="search"><?php esc_html_e( 'Search:', 'capture' ); ?></label><br>
								<input type="text" id="search" name="search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_html_e( 'Email or name...', 'capture' ); ?>" style="width: 200px;">
							</td>
							<td style="padding: 0 15px;">
								<label for="form_id"><?php esc_html_e( 'Form ID:', 'capture' ); ?></label><br>
								<select id="form_id" name="form_id" style="width: 150px;">
									<option value=""><?php esc_html_e( 'All Forms', 'capture' ); ?></option>
									<?php foreach ( $form_ids as $fid ) : ?>
										<option value="<?php echo esc_attr( $fid ); ?>" <?php selected( $form_id, $fid ); ?>>
											<?php echo esc_html( $fid ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
							<td style="padding: 0 15px;">
								<label for="status"><?php esc_html_e( 'Status:', 'capture' ); ?></label><br>
								<select id="status" name="status" style="width: 120px;">
									<option value=""><?php esc_html_e( 'All Status', 'capture' ); ?></option>
									<option value="subscribed" <?php selected( $status, 'subscribed' ); ?>><?php esc_html_e( 'Subscribed', 'capture' ); ?></option>
									<option value="unsubscribed" <?php selected( $status, 'unsubscribed' ); ?>><?php esc_html_e( 'Unsubscribed', 'capture' ); ?></option>
								</select>
							</td>
							<td style="padding: 0 15px;">
								<label for="date_from"><?php esc_html_e( 'Date From:', 'capture' ); ?></label><br>
								<input type="date" id="date_from" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" style="width: 140px;">
							</td>
							<td style="padding: 0 15px;">
								<label for="date_to"><?php esc_html_e( 'Date To:', 'capture' ); ?></label><br>
								<input type="date" id="date_to" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" style="width: 140px;">
							</td>
							<td style="padding: 0;">
								<label>&nbsp;</label><br>
								<input type="submit" class="button" value="<?php esc_html_e( 'Filter', 'capture' ); ?>">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=capture-subscribers' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'capture' ); ?></a>
							</td>
						</tr>
					</table>
				</form>
			</div>

			<?php if ( ! Database::subscribers_table_exists() ) : ?>
				<div class="notice notice-warning">
					<p><?php esc_html_e( 'Subscribers table does not exist. The plugin may need to be reactivated.', 'capture' ); ?></p>
				</div>
			<?php elseif ( empty( $subscribers ) ) : ?>
				<div class="notice notice-info">
					<p>
						<?php if ( $search || $form_id || $status || $date_from || $date_to ) : ?>
							<?php esc_html_e( 'No subscribers found matching your filters.', 'capture' ); ?>
						<?php else : ?>
							<?php esc_html_e( 'No subscribers found. Start capturing emails with your forms!', 'capture' ); ?>
						<?php endif; ?>
					</p>
				</div>
			<?php else : ?>

				<!-- Summary -->
				<div class="capture-summary" style="margin: 20px 0;">
					<p>
						<?php
						/* translators: 1: Number of displayed subscribers, 2: Total number of subscribers */
						printf( esc_html__( 'Showing %1$d subscriber(s) out of %2$d total.', 'capture' ), count( $subscribers ), intval( $total ) );
						?>
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
								<option value=""><?php esc_html_e( 'Bulk Actions', 'capture' ); ?></option>
								<option value="delete"><?php esc_html_e( 'Delete', 'capture' ); ?></option>
							</select>
							<input type="submit" class="button action" value="<?php esc_html_e( 'Apply', 'capture' ); ?>" onclick="return confirm('<?php esc_html_e( 'Are you sure you want to delete selected subscribers?', 'capture' ); ?>');">
						</div>

						<!-- Pagination -->
						<?php if ( $total_pages > 1 ) : ?>
							<div class="tablenav-pages">
								<?php
								$pagination_args = array(
									'base'      => add_query_arg( 'paged', '%#%' ),
									'format'    => '',
									'current'   => $current_page,
									'total'     => $total_pages,
									'prev_text' => '&laquo;',
									'next_text' => '&raquo;',
								);
								echo wp_kses_post( paginate_links( $pagination_args ) );
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
								<th class="manage-column"><?php esc_html_e( 'Email', 'capture' ); ?></th>
								<th class="manage-column"><?php esc_html_e( 'Name', 'capture' ); ?></th>
								<th class="manage-column"><?php esc_html_e( 'Form ID', 'capture' ); ?></th>
								<th class="manage-column"><?php esc_html_e( 'Date Subscribed', 'capture' ); ?></th>
								<th class="manage-column"><?php esc_html_e( 'Status', 'capture' ); ?></th>
								<th class="manage-column"><?php esc_html_e( 'Source URL', 'capture' ); ?></th>
								<th class="manage-column"><?php esc_html_e( 'Actions', 'capture' ); ?></th>
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
										<span class="capture-status capture-status-<?php echo esc_attr( $subscriber->status ); ?>">
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
										<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=capture-subscribers&action=delete&subscriber_id=' . $subscriber->id ), 'delete_subscriber_' . $subscriber->id ) ); ?>" 
											class="button button-small" 
											onclick="return confirm('<?php esc_html_e( 'Are you sure you want to delete this subscriber?', 'capture' ); ?>');">
											<?php esc_html_e( 'Delete', 'capture' ); ?>
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
								<?php echo wp_kses_post( paginate_links( $pagination_args ) ); ?>
							</div>
						</div>
					<?php endif; ?>

				</form>

			<?php endif; ?>

		</div>

		<style>
		.capture-status {
			padding: 2px 8px;
			border-radius: 3px;
			font-size: 11px;
			font-weight: bold;
			text-transform: uppercase;
		}
		.capture-status-subscribed {
			background: #d4edda;
			color: #155724;
		}
		.capture-status-unsubscribed {
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
		// Get filter parameters from URL.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are read-only filter parameters for export, no nonce needed.
		$search    = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are read-only filter parameters for export, no nonce needed.
		$form_id   = isset( $_GET['form_id'] ) ? sanitize_text_field( wp_unslash( $_GET['form_id'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are read-only filter parameters for export, no nonce needed.
		$status    = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are read-only filter parameters for export, no nonce needed.
		$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are read-only filter parameters for export, no nonce needed.
		$date_to   = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';

		// Build query args for export (get all matching records).
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

		// Get all subscribers matching filters.
		$result      = Subscriber::get_subscribers( $args );
		$subscribers = $result['subscribers'];

		// Set headers for CSV download.
		$filename = 'capture-subscribers-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Create file pointer.
		$output = fopen( 'php://output', 'w' );

		// Add CSV headers.
		fputcsv(
			$output,
			array(
				__( 'Email', 'capture' ),
				__( 'Name', 'capture' ),
				__( 'Form ID', 'capture' ),
				__( 'Date Subscribed', 'capture' ),
				__( 'Status', 'capture' ),
				__( 'Source URL', 'capture' ),
			)
		);

		// Add subscriber data.
		foreach ( $subscribers as $subscriber ) {
			fputcsv(
				$output,
				array(
					$subscriber->email,
					$subscriber->name,
					$subscriber->form_id,
					$subscriber->date_subscribed,
					$subscriber->status,
					$subscriber->source_url,
				)
			);
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
		$table_name = Database::get_subscribers_table_name();

		$results = $wpdb->get_col( 'SELECT DISTINCT form_id FROM ' . esc_sql( $table_name ) . ' WHERE form_id IS NOT NULL AND form_id != \'\' ORDER BY form_id' );

		return $results ?: array();
	}
}
