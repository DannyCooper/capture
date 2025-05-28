<?php
/**
 * Handles the registration of custom post types for the WP Capture plugin.
 *
 * @package WP_Capture
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WP_Capture_Post_Types
 */
class WP_Capture_Post_Types {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Register custom post types.
	 */
	public function register_post_types() {
		$labels = array(
			'name'                  => _x( 'Forms', 'Post type general name', 'capture' ),
			'singular_name'         => _x( 'Form', 'Post type singular name', 'capture' ),
			'menu_name'             => _x( 'Forms', 'Admin Menu text', 'capture' ),
			'name_admin_bar'        => _x( 'Form', 'Add New on Toolbar', 'capture' ),
			'add_new'               => __( 'Add New', 'capture' ),
			'add_new_item'          => __( 'Add New Form', 'capture' ),
			'new_item'              => __( 'New Form', 'capture' ),
			'edit_item'             => __( 'Edit Form', 'capture' ),
			'view_item'             => __( 'View Form', 'capture' ),
			'all_items'             => __( 'All Forms', 'capture' ),
			'search_items'          => __( 'Search Forms', 'capture' ),
			'not_found'             => __( 'No forms found.', 'capture' ),
			'not_found_in_trash'    => __( 'No forms found in Trash.', 'capture' ),
			'featured_image'        => _x( 'Form Cover Image', 'Overrides the "Featured Image" phrase', 'capture' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase', 'capture' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase', 'capture' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase', 'capture' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'capture-form' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-feedback',
			'supports'           => array( 'title', 'editor', 'revisions' ),
			'show_in_rest'       => true,
		);

		register_post_type( 'capture_form', $args );
	}
}

new WP_Capture_Post_Types();
