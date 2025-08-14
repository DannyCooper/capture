<?php
/**
 * Handles the analytics page for WP Capture.
 *
 * @package    Capture
 * @subpackage Capture/includes/admin
 */

namespace Capture;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin_Analytics class.
 */
class Admin_Analytics {

	/**
	 * Render the Analytics page.
	 */
	public function display_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Submission Analytics', 'capture' ); ?></h1>
			<p><?php esc_html_e( 'View submission analytics for your forms.', 'capture' ); ?></p>
			<?php $this->analytics_table_callback(); ?>
		</div>
		<?php
	}

	/**
	 * Callback for rendering the analytics table.
	 */
	public function analytics_table_callback() {
		$analytics_data = get_option( 'capture_analytics', array() );

		if ( empty( $analytics_data ) ) {
			echo '<p>' . esc_html__( 'No submission data yet.', 'capture' ) . '</p>';
			return;
		}

		echo '<table class="wp-list-table widefat striped fixed">';
		echo '<thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Page/Post Title', 'capture' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Form Identifier', 'capture' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Submissions', 'capture' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Last Submission', 'capture' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		foreach ( $analytics_data as $form_id => $data ) {
			$post_id                   = isset( $data['post_id'] ) ? intval( $data['post_id'] ) : 0;
			$count                     = isset( $data['count'] ) ? intval( $data['count'] ) : 0;
			$last_submission_timestamp = isset( $data['last_submission_timestamp'] ) ? $data['last_submission_timestamp'] : 0;
			$form_identifier_display   = esc_html( substr( $form_id, 0, 12 ) . ( strlen( $form_id ) > 12 ? '...' : '' ) );

			$title_display = $this->get_title_display( $post_id, $data );

			echo '<tr>';
			echo '<td>' . wp_kses_post( $title_display ) . '</td>';
			echo '<td>' . esc_html( $form_identifier_display ) . '</td>';
			echo '<td><a href="' . esc_url( admin_url( 'admin.php?page=capture-subscribers&form_id=' . $form_id ) ) . '">' . absint( $count ) . '</a></td>';
			echo '<td>';
			if ( $last_submission_timestamp > 0 ) {
				printf(
					/* translators: 1: Date, 2: Time */
					esc_html__( '%1$s at %2$s', 'capture' ),
					esc_html( wp_date( get_option( 'date_format' ), $last_submission_timestamp ) ),
					esc_html( wp_date( get_option( 'time_format' ), $last_submission_timestamp ) )
				);
			} else {
				echo esc_html__( 'N/A', 'capture' );
			}
			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Get the title display for a given post ID and data.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $data    The analytics data.
	 * @return string The formatted title display.
	 */
	private function get_title_display( $post_id, $data ) {
		// No post ID, check if we have a title in data.
		if ( $post_id <= 0 ) {
			return $this->get_title_display_no_post_id( $data );
		}

		$post = get_post( $post_id );

		// Post exists, create a link to it.
		if ( $post ) {
			return $this->get_title_display_existing_post( $post_id );
		}

		// Post was deleted, show appropriate message.
		return $this->get_title_display_deleted_post( $post_id, $data );
	}

	/**
	 * Get title display when no post ID is available.
	 *
	 * @param array $data The analytics data.
	 * @return string The formatted title display.
	 */
	private function get_title_display_no_post_id( $data ) {
		if ( ! empty( $data['post_title'] ) && $data['post_title'] !== 'N/A' ) {
			return '<em>' . esc_html( $data['post_title'] ) . ' (' . __( 'No Associated Post', 'capture' ) . ')</em>';
		}

		return __( 'N/A', 'capture' );
	}

	/**
	 * Get title display for an existing post.
	 *
	 * @param int $post_id The post ID.
	 * @return string The formatted title display.
	 */
	private function get_title_display_existing_post( $post_id ) {
		$post_title = get_the_title( $post_id );
		$edit_link  = get_edit_post_link( $post_id );

		if ( empty( $post_title ) ) {
			return '<a href="' . esc_url( $edit_link ) . '">' . __( 'No Title', 'capture' ) . ' (ID: ' . $post_id . ')</a>';
		}

		return '<a href="' . esc_url( $edit_link ) . '">' . esc_html( $post_title ) . '</a>';
	}

	/**
	 * Get title display for a deleted post.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $data    The analytics data.
	 * @return string The formatted title display.
	 */
	private function get_title_display_deleted_post( $post_id, $data ) {
		$has_valid_title = ! empty( $data['post_title'] )
			&& $data['post_title'] !== 'N/A'
			&& strpos( $data['post_title'], 'Post ID: ' ) === false;

		if ( $has_valid_title ) {
			return '<em>' . esc_html( $data['post_title'] ) . ' (' . __( 'Post Deleted', 'capture' ) . ' - ID: ' . $post_id . ')</em>';
		}

		return '<em>' . __( 'Post Deleted', 'capture' ) . ' (ID: ' . $post_id . ')</em>';
	}
}
