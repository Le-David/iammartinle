<?php
/**
 * The template file for post content.
 *
 * @package    Meta Box
 * @subpackage MB Frontend Submission
 */

$content = $data->post_id ? get_post_field( 'post_content', $data->post_id ) : '';
$name    = ! empty( $data->config['label_content'] ) ? $data->config['label_content'] : esc_html__( 'Content', 'rwmb-frontend-submission' );
$field   = apply_filters(
	'rwmb_frontend_post_content',
	array(
		'type' => 'wysiwyg',
		'name' => $name,
		'id'   => 'post_content',
		'std'  => $content,
	)
);
$field   = RWMB_Field::call( 'normalize', $field );
RWMB_Field::call( $field, 'add_actions' );
RWMB_Field::call( $field, 'admin_enqueue_scripts' );
RWMB_Field::call( 'show', $field, false, $data->post_id );
