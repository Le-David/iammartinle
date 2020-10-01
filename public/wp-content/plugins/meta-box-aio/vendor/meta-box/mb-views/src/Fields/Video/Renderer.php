<?php
namespace MBViews\Fields\Video;

use MBViews\Fields\BaseRenderer;
use RWMB_Video_Field;

class Renderer extends BaseRenderer {
	public static function get_single_value( $value ) {
		return array_merge( $value, [
			'rendered' => RWMB_Video_Field::format_clone_value( null, [$value], null, null ),
		] );
	}
}