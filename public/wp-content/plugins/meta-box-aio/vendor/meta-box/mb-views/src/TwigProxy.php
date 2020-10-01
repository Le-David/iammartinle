<?php
namespace MBViews;

/**
 * Make all functions available via function mb.function.
 *
 * @link https://inchoo.net/dev-talk/wordpress/twig-wordpress-part2/
 */
class TwigProxy {
	public function __call( $function, $arguments ) {
		return function_exists( $function ) ? call_user_func_array( $function, $arguments ) : null;
	}

	public function map( $field_id, $width = '100%', $height = '480px', $zoom = 14, $marker_icon = '', $marker_title = '', $info_window = '' ) {
		$args = compact( 'width', 'height', 'zoom', 'marker_icon', 'marker_title', 'info_window' );
		$field_id = str_replace( 'post.', '', $field_id );
		return rwmb_the_value( $field_id, $args, null, false );
	}

	public function checkbox( $value, $checked = '', $unchecked = '' ) {
		return $value ? $checked : $unchecked;
	}

	public function post_comments() {
		$comments = '';
		if ( comments_open() || get_comments_number() ) {
			ob_start();
			comments_template();
			$comments = ob_get_clean();
		}
		return $comments;
	}
}