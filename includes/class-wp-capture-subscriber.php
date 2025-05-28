<?php
/**
 * Subscriber model for WP Capture plugin.
 *
 * @package WP_Capture
 * @since 1.0.0
 */

/**
 * Subscriber model class.
 *
 * @since 1.0.0
 */
class WP_Capture_Subscriber {

	/**
	 * Subscriber ID.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Subscriber email.
	 *
	 * @var string
	 */
	public $email;

	/**
	 * Subscriber name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Form ID that captured this subscriber.
	 *
	 * @var string
	 */
	public $form_id;

	/**
	 * Date when subscriber was added.
	 *
	 * @var string
	 */
	public $date_subscribed;

	/**
	 * User agent string.
	 *
	 * @var string
	 */
	public $user_agent;

	/**
	 * Subscriber status.
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Source URL where subscriber signed up.
	 *
	 * @var string
	 */
	public $source_url;

	/**
	 * Constructor.
	 *
	 * @param array $data Subscriber data.
	 */
	public function __construct( $data = array() ) {
		if ( ! empty( $data ) ) {
			$this->populate( $data );
		}
	}

	/**
	 * Populate object properties from array.
	 *
	 * @param array $data Subscriber data.
	 */
	private function populate( $data ) {
		$this->id = isset( $data['id'] ) ? intval( $data['id'] ) : null;
		$this->email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		$this->name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
		$this->form_id = isset( $data['form_id'] ) ? sanitize_text_field( $data['form_id'] ) : '';
		$this->date_subscribed = isset( $data['date_subscribed'] ) ? $data['date_subscribed'] : '';
		$this->user_agent = isset( $data['user_agent'] ) ? sanitize_textarea_field( $data['user_agent'] ) : '';
		$this->status = isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'active';
		$this->source_url = isset( $data['source_url'] ) ? esc_url_raw( $data['source_url'] ) : '';
	}

	/**
	 * Save subscriber to database.
	 *
	 * @return int|WP_Error Subscriber ID on success, WP_Error on failure.
	 */
	public function save() {
		global $wpdb;

		$table_name = WP_Capture_Database::get_subscribers_table_name();

		// Validate required fields.
		if ( empty( $this->email ) || ! is_email( $this->email ) ) {
			return new WP_Error( 'invalid_email', __( 'Valid email address is required.', 'capture' ) );
		}

		// Check for duplicate email/form_id combination (only for new subscribers).
		if ( ! $this->id ) {
			$existing = self::get_by_email_and_form( $this->email, $this->form_id );
			if ( $existing ) {
				return new WP_Error( 'duplicate_subscriber', __( 'This email is already subscribed to this form.', 'capture' ) );
			}
		}

		$data = array(
			'email' => $this->email,
			'name' => $this->name,
			'form_id' => $this->form_id,
			'user_agent' => $this->user_agent,
			'status' => $this->status,
			'source_url' => $this->source_url,
		);

		$formats = array( '%s', '%s', '%s', '%s', '%s', '%s' );

		if ( $this->id ) {
			// Update existing subscriber.
			$result = $wpdb->update( $table_name, $data, array( 'id' => $this->id ), $formats, array( '%d' ) );
			return $result !== false ? $this->id : new WP_Error( 'update_failed', __( 'Failed to update subscriber.', 'capture' ) );
		} else {
			// Insert new subscriber.
			$data['date_subscribed'] = current_time( 'mysql' );
			$formats[] = '%s';

			$result = $wpdb->insert( $table_name, $data, $formats );
			if ( $result !== false ) {
				$this->id = $wpdb->insert_id;
				$this->date_subscribed = $data['date_subscribed'];
				return $this->id;
			}
			return new WP_Error( 'insert_failed', __( 'Failed to save subscriber.', 'capture' ) );
		}
	}

	/**
	 * Get subscriber by ID.
	 *
	 * @param int $id Subscriber ID.
	 * @return WP_Capture_Subscriber|null Subscriber object or null if not found.
	 */
	public static function get_by_id( $id ) {
		global $wpdb;

		$table_name = WP_Capture_Database::get_subscribers_table_name();
		$result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . esc_sql( $table_name ) . ' WHERE id = %d', $id ), ARRAY_A );

		return $result ? new self( $result ) : null;
	}

	/**
	 * Get subscriber by email and form ID.
	 *
	 * @param string $email Email address.
	 * @param string $form_id Form ID.
	 * @return WP_Capture_Subscriber|null Subscriber object or null if not found.
	 */
	public static function get_by_email_and_form( $email, $form_id ) {
		global $wpdb;

		$table_name = WP_Capture_Database::get_subscribers_table_name();
		$result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . esc_sql( $table_name ) . ' WHERE email = %s AND form_id = %s', $email, $form_id ), ARRAY_A );

		return $result ? new self( $result ) : null;
	}

	/**
	 * Get all subscribers with pagination and filtering.
	 *
	 * @param array $args Query arguments.
	 * @return array Array of subscribers and total count.
	 */
	public static function get_subscribers( $args = array() ) {
		$defaults = array(
			'per_page' => 20,
			'page' => 1,
			'search' => '',
			'form_id' => '',
			'status' => '',
			'date_from' => '',
			'date_to' => '',
			'orderby' => 'date_subscribed',
			'order' => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		// Build WHERE clause and get values.
		$where_data = self::build_where_clause( $args );
		$where_clause = $where_data['clause'];
		$where_values = $where_data['values'];

		// Get total count.
		$total = self::get_total_count( $where_clause, $where_values );

		// Get subscribers.
		$results = self::execute_subscribers_query( $args, $where_clause, $where_values );

		// Process results.
		$subscribers = self::process_subscribers_results( $results );

		return array(
			'subscribers' => $subscribers,
			'total' => $total,
			'pages' => $args['per_page'] === -1 ? 1 : ceil( $total / $args['per_page'] ),
		);
	}

	/**
	 * Build WHERE clause for subscriber queries.
	 *
	 * @param array $args Query arguments.
	 * @return array WHERE clause and values.
	 */
	private static function build_where_clause( $args ) {
		global $wpdb;

		$where_conditions = array( '1=1' );
		$where_values = array();

		if ( ! empty( $args['search'] ) ) {
			$where_conditions[] = '(email LIKE %s OR name LIKE %s)';
			$search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where_values[] = $search_term;
			$where_values[] = $search_term;
		}

		if ( ! empty( $args['form_id'] ) ) {
			$where_conditions[] = 'form_id = %s';
			$where_values[] = $args['form_id'];
		}

		if ( ! empty( $args['status'] ) ) {
			$where_conditions[] = 'status = %s';
			$where_values[] = $args['status'];
		}

		if ( ! empty( $args['date_from'] ) ) {
			$where_conditions[] = 'date_subscribed >= %s';
			$where_values[] = $args['date_from'];
		}

		if ( ! empty( $args['date_to'] ) ) {
			$where_conditions[] = 'date_subscribed <= %s';
			$where_values[] = $args['date_to'] . ' 23:59:59';
		}

		return array(
			'clause' => implode( ' AND ', $where_conditions ),
			'values' => $where_values,
		);
	}

	/**
	 * Get total count of subscribers matching criteria.
	 *
	 * @param string $where_clause WHERE clause.
	 * @param array  $where_values WHERE values.
	 * @return int Total count.
	 */
	private static function get_total_count( $where_clause, $where_values ) {
		global $wpdb;

		$table_name = WP_Capture_Database::get_subscribers_table_name();

		if ( ! empty( $where_values ) ) {
			$count_query = $wpdb->prepare(
				'SELECT COUNT(*) FROM ' . esc_sql( $table_name ) . ' WHERE ' . $where_clause, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$where_values
			);
			return (int) $wpdb->get_var( $count_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . esc_sql( $table_name ) . ' WHERE ' . $where_clause ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	/**
	 * Execute the main subscribers query.
	 *
	 * @param array  $args Query arguments.
	 * @param string $where_clause WHERE clause.
	 * @param array  $where_values WHERE values.
	 * @return array Query results.
	 */
	private static function execute_subscribers_query( $args, $where_clause, $where_values ) {
		global $wpdb;

		$table_name = WP_Capture_Database::get_subscribers_table_name();
		$order_clause = self::build_order_clause( $args );

		if ( $args['per_page'] === -1 ) {
			// Export mode - get all records without LIMIT.
			if ( ! empty( $where_values ) ) {
				$query = $wpdb->prepare(
					'SELECT * FROM ' . esc_sql( $table_name ) . ' WHERE ' . $where_clause . ' ' . $order_clause, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$where_values
				);
				return $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			} else {
				return $wpdb->get_results( 'SELECT * FROM ' . esc_sql( $table_name ) . ' WHERE ' . $where_clause . ' ' . $order_clause, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
		} else {
			// Paginated mode - use LIMIT and OFFSET.
			$offset = ( $args['page'] - 1 ) * $args['per_page'];
			$query_values = array_merge( $where_values, array( $args['per_page'], $offset ) );
			$query = $wpdb->prepare(
				'SELECT * FROM ' . esc_sql( $table_name ) . ' WHERE ' . $where_clause . ' ' . $order_clause . ' LIMIT %d OFFSET %d', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$query_values
			);
			return $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	/**
	 * Build ORDER BY clause for subscriber queries.
	 *
	 * @param array $args Query arguments.
	 * @return string ORDER BY clause.
	 */
	private static function build_order_clause( $args ) {
		$allowed_orderby = array( 'id', 'email', 'name', 'form_id', 'date_subscribed', 'status' );
		$orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'date_subscribed';
		$order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		return 'ORDER BY ' . esc_sql( $orderby ) . ' ' . esc_sql( $order );
	}

	/**
	 * Process query results into subscriber objects.
	 *
	 * @param array $results Raw query results.
	 * @return array Array of WP_Capture_Subscriber objects.
	 */
	private static function process_subscribers_results( $results ) {
		$subscribers = array();
		foreach ( $results as $result ) {
			$subscribers[] = new self( $result );
		}
		return $subscribers;
	}

	/**
	 * Delete subscriber.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if ( ! $this->id ) {
			return false;
		}

		global $wpdb;
		$table_name = WP_Capture_Database::get_subscribers_table_name();

		$result = $wpdb->delete( $table_name, array( 'id' => $this->id ), array( '%d' ) );
		return $result !== false;
	}

	/**
	 * Update subscriber status.
	 *
	 * @param string $status New status.
	 * @return bool True on success, false on failure.
	 */
	public function update_status( $status ) {
		if ( ! in_array( $status, array( 'active', 'unsubscribed' ) ) ) {
			return false;
		}

		$this->status = $status;
		$result = $this->save();
		return ! is_wp_error( $result );
	}

	/**
	 * Get subscriber data as array for export.
	 *
	 * @return array Subscriber data.
	 */
	public function to_array() {
		return array(
			'id' => $this->id,
			'email' => $this->email,
			'name' => $this->name,
			'form_id' => $this->form_id,
			'date_subscribed' => $this->date_subscribed,
			'status' => $this->status,
			'source_url' => $this->source_url,
		);
	}
}
