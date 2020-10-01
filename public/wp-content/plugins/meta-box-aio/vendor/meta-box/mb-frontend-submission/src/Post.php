<?php
namespace MBFS;

use RWMB_Field;

class Post {
	/**
	 * Post type.
	 *
	 * @var string
	 */
	public $post_type;

	/**
	 * Post ID.
	 *
	 * @var int
	 */
	public $post_id;

	/**
	 * List of post fields for rendering/saving.
	 *
	 * @var array
	 */
	public $fields;

	/**
	 * Configuration for the post model.
	 *
	 * @var array
	 */
	public $config;

	private $template_loader;

	/**
	 * Constructor.
	 *
	 * @param string         $post_type       Post type.
	 * @param int            $post_id         Post ID.
	 * @param array          $config          Custom parameters.
	 * @param TemplateLoader $template_loader Template loader for loading form templates.
	 */
	public function __construct( $post_type = 'post', $post_id = 0, $config = array(), $template_loader ) {
		$this->post_id         = (int) $post_id;
		$this->post_type       = $post_id ? get_post_type( $post_id ) : $post_type;
		$this->config          = $config;
		$this->fields          = array_map( 'trim', array_filter( explode( ',', $this->config['post_fields'] . ',' ) ) );
		$this->template_loader = $template_loader;
	}

	/**
	 * Render post fields in the frontend.
	 */
	public function render() {
		$data = array(
			'post_id'   => $this->post_id,
			'post_type' => $this->post_type,
			'config'    => $this->config,
		);

		foreach ( $this->fields as $field ) {
			$this->template_loader->set_template_data( $data )->get_template_part( "post/$field" );
		}
	}

	/**
	 * Save post data.
	 */
	public function save() {
		do_action( 'rwmb_frontend_before_save_post', $this );

		if ( $this->post_id ) {
			$this->update();
		} else {
			$this->create();
		}

		$this->save_thumbnail();
		do_action( 'rwmb_frontend_after_save_post', $this );
		return $this->post_id;
	}

	/**
	 * Update the post.
	 */
	private function update() {
		$data       = $this->get_data();
		$data['ID'] = $this->post_id;
		$data       = apply_filters( 'rwmb_frontend_update_post_data', $data, $this->config );
		wp_update_post( $data );
	}

	/**
	 * Create a new post.
	 */
	private function create() {
		$data                = $this->get_data();
		$data['post_type']   = $this->post_type;
		$data['post_status'] = $this->config['post_status'];
		$data                = apply_filters( 'rwmb_frontend_insert_post_data', $data, $this->config );
		$this->post_id       = wp_insert_post( $data );
	}

	/**
	 * Get submitted data to save into the database.
	 *
	 * @return array
	 */
	private function get_data() {
		$data        = array();
		$post_fields = array(
			'title'   => 'post_title',
			'content' => 'post_content',
			'excerpt' => 'post_excerpt',
			'date'    => 'post_date',
		);

		foreach ( $this->fields as $field ) {
			if ( ! isset( $post_fields[ $field ] ) ) {
				continue;
			}
			$field          = $post_fields[ $field ];
			$data[ $field ] = (string) filter_input( INPUT_POST, $field );
		}

		// If developer sets the post parent using 'post' field.
		$data['post_parent'] = filter_input( INPUT_POST, 'parent_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $data['post_title'] ) ) {
			$data['post_title'] = __( '(No title)', 'mb-frontend-submission' );
		}

		return $data;
	}

	/**
	 * Save post thumbnail.
	 */
	private function save_thumbnail() {
		if ( ! in_array( 'thumbnail', $this->fields, true ) ) {
			return;
		}
		$field = array(
			'type'             => 'image',
			'name'             => esc_html__( 'Thumbnail', 'rwmb-frontend-submission' ),
			'id'               => '_thumbnail_id',
			'max_file_uploads' => 1,
			'storage'          => rwmb_get_storage( 'post' ),
		);
		$field = RWMB_Field::call( 'normalize', $field );

		$old = RWMB_Field::call( $field, 'raw_meta', $this->post_id );
		// @codingStandardsIgnoreLine
		$new = isset( $_POST[ $field['id'] ] ) ? $_POST[ $field['id'] ] : array();

		$new = RWMB_Field::process_value( $new, $this->post_id, $field );

		// Filter to allow the field to be modified.
		$field = RWMB_Field::filter( 'field', $field, $field, $new, $old );

		// Call defined method to save meta value, if there's no methods, call common one.
		RWMB_Field::call( $field, 'save', $new, $old, $this->post_id );

		RWMB_Field::filter( 'after_save_field', null, $field, $new, $old, $this->post_id, $field );
	}
}
