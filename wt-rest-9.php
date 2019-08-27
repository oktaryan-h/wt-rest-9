<?php

/**
 * Plugin Name: WT REST API 9
 * Description: Description
 * Plugin URI: http://#
 * Author: Author
 * Author URI: http://#
 * Version: 1.0.0
 * License: GPL2
 * Text Domain: wt-rest-9
 * Domain Path: domain/path
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WT_REST_API_9 {

	public function init() {

		add_action( 'rest_api_init', array( $this, 'register_custom_rest_routes' ) );

		add_action( 'init', array( $this, 'register_cpt_testimonials' ) );
		add_action( 'init', array( $this, 'register_meta_testimonials' ) );
		// add_action( 'plugins_loaded', array( $this, 'get_instance' ) );
		// 
		add_action( 'add_meta_boxes', array( $this, 'testimonials_metabox' ) );
		add_action( 'save_post_testimonials', array( $this, 'testimonials_save_metabox' ) );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		// register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );
	}

	public function activate() {}

	public function deactivate() {}

	public function register_custom_rest_routes() {

		// register_rest_route( 'rpl/v1', '/testimonials/', array(
		// 'methods' => WP_REST_Server::READABLE,
		// 'callback' => array( $this, 'get_testimonials' ),
		// ) );

		// register_rest_route( 'rpl/v1', '/testimonials/(?P<id>\d+)', array(
		// 'methods' => WP_REST_Server::READABLE,
		// 'callback' => array( $this, 'get_single_testimonial' ),
		// ) );
	}

	public function get_testimonials( $request ) {

		// var_dump($request->get_params());

		$page     = 1;
		$per_page = 3;

		if ( isset( $request['page'] ) ) {
			$page = $request['page'];
		}

		if ( isset( $request['per_page'] ) ) {
			$per_page = $request['per_page'];
		}

		$args = array(
			'post_type'      => 'testimonials',
			'paged'          => $page,
			'posts_per_page' => $per_page,
			'post-status'    => 'publish',
		);

		$testimonials = new WP_Query( $args );

		return rest_ensure_response( $testimonials );

	}

	public function get_single_testimonial( $request ) {

		if ( isset( $request['id'] ) ) {
			$post_id = $request['id'];
		}

		$args = array(
			'p'           => $post_id,
			'post_type'   => 'testimonials',
			'post-status' => 'publish',
		);

		$testimonial = new WP_Query( $args );

		return rest_ensure_response( $testimonial );

	}

	public function testimonials_metabox() {

		add_meta_box(
			'testimonials-meta',	// Unique ID
			'Testimonials Meta',	// Box title
			array( $this, 'testimonials_metabox_html' ),	// Content callback, must be of type callable
			'testimonials'			// Post type
		);
	}

	public function testimonials_metabox_html( $post ) {

		$post_id = $post->ID;

		$rate_value = get_post_meta( $post_id, 'mb-rate', true );

		?>
		<p>
			Rate : 
			<select name="mb-rate">
				<option value="1" <?php echo ( 1 == $rate_value ) ? 'selected="selected"' : ''; ?>>1</option>
				<option value="2" <?php echo ( 2 == $rate_value ) ? 'selected="selected"' : ''; ?>>2</option>
				<option value="3" <?php echo ( 3 == $rate_value ) ? 'selected="selected"' : ''; ?>>3</option>
				<option value="4" <?php echo ( 4 == $rate_value ) ? 'selected="selected"' : ''; ?>>4</option>
				<option value="5" <?php echo ( 5 == $rate_value ) ? 'selected="selected"' : ''; ?>>5</option>
			</select>
		</p>
		<?php
	}

	public function testimonials_save_metabox( $post_id ) {

		if ( isset( $_POST['mb-rate'] ) ) {
			update_post_meta( $post_id, 'mb-rate', sanitize_text_field( $_POST['mb-rate'] ) );
		}

	}

	public function register_meta_testimonials() {

		register_post_meta(
			'testimonials',
			'rate',
			array(
				'type'         => 'string',
				'description'  => 'Rate',
				'single'       => true,
				'show_in_rest' => true,
			)
		);

	}

	/**
	 * Registers a new post type
	 *
	 * @uses $wp_post_types Inserts new post type object into the list
	 *
	 * @param string  Post type key, must not exceed 20 characters
	 * @param array|string  See optional args description above.
	 * @return object|WP_Error the registered post type object, or an error object
	 */
	public function register_cpt_testimonials() {

		$labels = array(
			'name'               => __( 'Testimonials', 'wt-rest-9' ),
			'singular_name'      => __( 'Testimonial', 'wt-rest-9' ),
			'add_new'            => _x( 'Add New Testimonial', 'wt-rest-9', 'wt-rest-9' ),
			'add_new_item'       => __( 'Add New Testimonial', 'wt-rest-9' ),
			'edit_item'          => __( 'Edit Testimonial', 'wt-rest-9' ),
			'new_item'           => __( 'New Testimonial', 'wt-rest-9' ),
			'view_item'          => __( 'View Testimonial', 'wt-rest-9' ),
			'search_items'       => __( 'Search Testimonials', 'wt-rest-9' ),
			'not_found'          => __( 'No Testimonials found', 'wt-rest-9' ),
			'not_found_in_trash' => __( 'No Testimonials found in Trash', 'wt-rest-9' ),
			'parent_item_colon'  => __( 'Parent Testimonial:', 'wt-rest-9' ),
			'menu_name'          => __( 'Testimonials', 'wt-rest-9' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'description'         => 'description',
			'taxonomies'          => array(),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			// 'show_in_rest'        => true,
			'menu_position'       => 16,
			'menu_icon'           => 'dashicons-controls-forward',
			'show_in_nav_menus'   => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'capability_type'     => 'post',
			'supports'            => array(
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				'custom-fields',
				'trackbacks',
				'comments',
				'revisions',
				'page-attributes',
				'post-formats',
			),
		);

		register_post_type( 'testimonials', $args );

	}

	/*
	public static function uninstall() {
		if ( __FILE__ != WP_UNINSTALL_PLUGIN )
			return;
	}
	*/
}

function plugin_init() {

	$plugin = new WT_REST_API_9();
	$plugin->init();

}

function routes_init() {

	require_once plugin_dir_path( __FILE__ ) . 'class-testimonials-custom-route.php';

	$route = new Testimonials_Custom_Route();
	$route->register_routes();

}

add_action( 'plugins_loaded', 'plugin_init' );
add_action( 'rest_api_init', 'routes_init' );
