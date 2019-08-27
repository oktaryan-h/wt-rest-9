<?php

class Testimonials_Custom_Route extends WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version   = '1';
		$namespace = 'rpl/v' . $version;
		$base      = 'testimonials';
		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_items_args(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->create_item_args(),
				),
			)
		);
		register_rest_route(
			$namespace,
			'/' . $base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_item_args(),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					// 'args'                => $this->get_endpoint_args_for_item_schema( false ),
					'args'                => $this->update_item_args(),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => $this->delete_item_args(),
				),
			)
		);
		register_rest_route(
			$namespace,
			'/' . $base . '/schema',
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return string json data.
	 */
	public function get_items( $request ) {

		$params = $request->get_params();

		$page     = isset( $params['page'] ) ? $params['page'] : 1;
		$per_page = isset( $params['per_page'] ) ? $params['per_page'] : 5;

		$args = array(
			'post_type'      => 'testimonials',
			'paged'          => $page,
			'posts_per_page' => $per_page,
			'post_status'    => 'publish',
		);

		$items = new WP_Query( $args );

		if ( $items->have_posts() ) {

			$response = new WP_REST_Response( $items, 200 );

		} else {

			$response = new WP_Error( 'rest_no_posts', __( 'There is no posts found.', 'wt-rest-9' ), array( 'status' => 404 ) );

		}

		return rest_ensure_response( $response );
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return string json data.
	 */
	public function get_item( $request ) {
		// get parameters from request
		$params = $request->get_params();

		$post_id = isset( $params['id'] ) ? $params['id'] : 1;

		$args = array(
			'ID'          => $post_id,
			'post_type'   => 'testimonials',
			'post_status' => 'publish',
		);

		$item = get_post( $post_id );
		// $item = new WP_Post( $args );

		if ( ( $item ) && ( 'testimonials' == $item->post_type ) ) {

			$response = new WP_REST_Response( $item, 200 );

		} else {

			$response = new WP_Error( 'rest_no_post_id', __( 'There is no Testimonial with that ID.', 'wt-rest-9' ), array( 'status' => 404 ) );

		}

		return rest_ensure_response( $response );

	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return
	 */
	public function create_item( $request ) {

		// if ( ! current_user_can( 'edit_posts' ) ) {
		// return new WP_Error( 'rest_cannot_create', __( 'You are not authorized to create post.', 'wt-rest-9' ), array( 'status' => 401 ) );
		// }

		$param = $request->get_params();

		$data = array(
			'post_title'   => isset( $param['author'] ) ? $param['author'] : '',
			'post_content' => isset( $param['content'] ) ? $param['content'] : '',
			'post_date'    => isset( $param['date'] ) ? $param['date'] : '',
			'meta_input'   => array(
				'mb-rate' => isset( $param['rate'] ) ? $param['rate'] : '',
			),
			'post_type'    => 'testimonials',
			'post_status'  => 'publish',
		);

		if ( wp_insert_post( $data ) ) {

			$response = new WP_REST_Response( $data, 200 );

		} else {

			$response = new WP_Error( 'rest_cannot_create', __( 'Failed to create post.', 'wt-rest-9' ), array( 'status' => 401 ) );

		}

		return rest_ensure_response( $response );

	}

	/**
	 * Update one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function update_item( $request ) {

		// if ( ! current_user_can( 'edit_posts' ) ) {
		// return new WP_Error( 'rest_cannot_update', __( 'You are not authorized to update post.', 'wt-rest-9' ), array( 'status' => 401 ) );
		// }

		$param = $request->get_params();

		$post_id = isset( $params['id'] ) ? $params['id'] : null;

		$item = get_post( $post_id );

		if ( ( $item ) && ( 'testimonials' == $item->post_type ) ) {

			$data = array(
				'ID'           => isset( $param['id'] ) ? $param['id'] : '',
				'post_title'   => isset( $param['author'] ) ? $param['author'] : '',
				'post_content' => isset( $param['content'] ) ? $param['content'] : '',
				'post_date'    => isset( $param['date'] ) ? $param['date'] : '',
				'meta_input'   => array(
					'mb-rate' => isset( $param['rate'] ) ? $param['rate'] : '',
				),
				'post_type'    => 'testimonials',
				'post_status'  => 'publish',
			);

			if ( wp_update_post( $data ) ) {

				$response = new WP_REST_Response( $data, 200 );

			} else {

				$response = new WP_Error( 'rest_cannot_update', __( 'Failed to update post.', 'wt-rest-9' ), array( 'status' => 401 ) );

			}
		} else {

			$response = new WP_Error( 'rest_cannot_update', __( 'Failed to update post. The post ID to be updated is either invalid, not a testimonial, or does not exist.', 'wt-rest-9' ), array( 'status' => 401 ) );

		}

		return rest_ensure_response( $response );

	}

	/**
	 * Delete one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function delete_item( $request ) {

		$param = $request->get_params();

		$post_id = isset( $param['id'] ) ? $param['id'] : null;

		$item = get_post( $post_id );

		if ( ( $item ) && ( 'testimonials' == $item->post_type ) ) {

			$data = wp_trash_post( $post_id );

			if ( $data ) {

				$response = new WP_REST_Response( $data, 200 );

			} else {

				$response = new WP_Error( 'rest_cannot_delete', __( 'Failed to delete post.', 'wt-rest-9' ), array( 'status' => 401 ) );

			}
		} else {

			$response = new WP_Error( 'rest_cannot_delete', __( 'Failed to delete post. The post ID to be updated is either invalid, not a testimonial, or does not exist.', 'wt-rest-9' ), array( 'status' => 401 ) );

		}

		return rest_ensure_response( $response );

	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		// return current_user_can( 'edit_something' );
		return true;
	}

	/**
	 * Check if a given request has access to get a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to create items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		// return current_user_can( 'edit_posts' );
		return true;
	}

	/**
	 * Check if a given request has access to update a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		return $this->create_item_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to delete a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		return $this->create_item_permissions_check( $request );
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {
		return array();
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed           $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {
		return array();
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_items_args() {

		return array(
			'page'     => array(
				'description'       => 'Current page of the collection.',
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page' => array(
				'description'       => 'Maximum number of items to be returned in result set.',
				'type'              => 'integer',
				'default'           => 10,
				'sanitize_callback' => 'absint',
			),
		);

	}

	public function get_item_args() {

		return array(
			'context' => array(
				'default' => 'view',
			),
			'id'      => array(
				'required'    => true,
				'type'        => 'integer',
				'description' => 'The ID number of testimonial',
			),
		);

	}

	public function create_item_args() {

		return array(
			'author'  => array(
				'required'    => true,
				'type'        => 'string',
				'description' => 'The Author of testimonial',
			),
			'content' => array(
				'required'    => true,
				'type'        => 'string',
				'description' => 'The Content of testimonial',
			),
			'date'    => array(
				'required'    => true,
				'type'        => 'string',
				'description' => 'The creation date of testimonial',
				'format'      => 'date-time',
			),
			'rate'    => array(
				'required'    => true,
				'type'        => 'string',
				'description' => 'The satisfaction rating of testimonial',
				'enum'        => array( '1', '2', '3', '4', '5' ),
			),
		);

	}

	public function update_item_args() {

		return array(
			'id'      => array(
				'required'    => true,
				'type'        => 'integer',
				'description' => 'The ID number of testimonial to be updated',
			),
			'author'  => array(
				'required'    => false,
				'type'        => 'string',
				'description' => 'The Author of testimonial',
			),
			'content' => array(
				'required'    => false,
				'type'        => 'string',
				'description' => 'The Content of testimonial',
			),
			'date'    => array(
				'required'    => false,
				'type'        => 'string',
				'description' => 'The creation date of testimonial',
				'format'      => 'date-time',
			),
			'rate'    => array(
				'required'    => false,
				'type'        => 'string',
				'description' => 'The satisfaction rating of testimonial',
				'enum'        => array( '1', '2', '3', '4', '5' ),
			),
		);

	}

	public function delete_item_args() {

		return array(
			'id'    => array(
				'required'    => true,
				'type'        => 'integer',
				'description' => 'The ID number of testimonial to be deleted',
			),
			'force' => array(
				'default' => false,
			),
		);

	}

}
