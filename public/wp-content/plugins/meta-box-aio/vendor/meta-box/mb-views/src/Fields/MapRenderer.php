<?php
/**
 * Renderer for map fields: map, osm. These fields are not cloneable.
 */
namespace MBViews\Fields;

use RWMB_Field;

class MapRenderer extends BaseRenderer {
	public static function get_single_value( $value ) {
		return array_merge( $value, [
			'rendered' => RWMB_Field::call( 'the_value', self::$field, $value, [], null ),
		] );
	}
}