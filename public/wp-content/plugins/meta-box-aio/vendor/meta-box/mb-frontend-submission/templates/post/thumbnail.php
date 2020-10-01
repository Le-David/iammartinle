<?php
/**
 * The template file for post thumbnail.
 *
 * @package    Meta Box
 * @subpackage MB Frontend Submission
 */

$default      = array();
$thumbnail_id = $data->post_id ? get_post_thumbnail_id( $data->post_id ) : '';
$name         = ! empty( $data->config['label_thumbnail'] ) ? $data->config['label_thumbnail'] : esc_html__( 'Thumbnail', 'rwmb-frontend-submission' );
if ( $thumbnail_id ) {
	$default = array( $thumbnail_id );
}
$field = apply_filters(
	'rwmb_frontend_post_thumbnail',
	array(
		'type'             => 'image',
		'name'             => $name,
		'id'               => '_thumbnail_id',
		'max_file_uploads' => 1,
		'std'              => $default,
	)
);
$field = RWMB_Field::call( 'normalize', $field );
RWMB_Field::call( $field, 'add_actions' );
RWMB_Field::call( $field, 'admin_enqueue_scripts' );
RWMB_Field::call( 'show', $field, false, $data->post_id );
