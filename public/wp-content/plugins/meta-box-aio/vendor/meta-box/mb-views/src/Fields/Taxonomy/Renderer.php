<?php
namespace MBViews\Fields\Taxonomy;

use MBViews\Fields\BaseRenderer;
use RWMB_Taxonomy_Field;

class Renderer extends BaseRenderer {
	public static function get_single_value( $value ) {
		if ( ! $value ) {
			return null;
		}
		$value = (array) $value;
		return array_merge( $value, [
			'id'  => $value['term_id'],
			'url' => get_term_link( $value['term_id'] ),
		] );
	}
}