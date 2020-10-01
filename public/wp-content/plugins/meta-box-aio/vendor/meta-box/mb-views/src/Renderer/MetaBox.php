<?php
namespace MBViews\Renderer;

use MBViews\Fields\ArrayRenderer;
use MBViews\Fields\Choice\Renderer as ChoiceRenderer;
use MBViews\Fields\TableRenderer;
use MBViews\Fields\BaseRenderer;
use MBViews\Fields\Image\Renderer as ImageRenderer;
use MBViews\Fields\MapRenderer;
use MBViews\Fields\Oembed\Renderer as OembedRenderer;
use MBViews\Fields\Post\Renderer as PostRenderer;
use MBViews\Fields\Taxonomy\Renderer as TaxonomyRenderer;
use MBViews\Fields\TaxonomyAdvancedRenderer;
use MBViews\Fields\User\Renderer as UserRenderer;
use MBViews\Fields\Video\Renderer as VideoRenderer;

class MetaBox {
	public function get_data( $meta_box, $object_type = 'post', $object_id = null ) {
		$data = [];
		$args = [ 'object_type' => $object_type ];
		foreach ( $meta_box->fields as $field ) {
			$value = rwmb_get_value( $field['id'], $args, $object_id );

			if ( 'group' === $field['type'] ) {
				$value = $this->parse_group_value( $value, $field );
			} else {
				$value = $this->parse_field_value( $value, $field );
			}

			$data[ $field['id'] ] = $value;
		}
		return $data;
	}

	private function parse_group_value( $value, $field ) {
		if ( $field['clone'] ) {
			foreach ( $value as $k => $clone ) {
				$value[ $k ] = $this->parse_group_clone_value( $clone, $field );
			}
		} else {
			$value = $this->parse_group_clone_value( $value, $field );
		}
		return $value;
	}

	private function parse_group_clone_value( $value, $field ) {
		foreach ( $field['fields'] as $child ) {
			if ( ! isset( $value[ $child['id'] ] ) ) {
				continue;
			}
			$child_value = $value[ $child['id'] ];
			if ( 'group' === $child['type'] ) {
				$child_value = $this->parse_group_value( $child_value, $child );
			} else {
				$child_value = $this->parse_field_value( $child_value, $child );
			}
			$value[ $child['id'] ] = $child_value;
		}
		return $value;
	}

	private function parse_field_value( $value, $field ) {
		if ( in_array( $field['type'], ['background'] ) ) {
			$value = ArrayRenderer::parse( $value, $field );
		}
		if ( in_array( $field['type'], ['button_group', 'checkbox_list', 'radio', 'select', 'select_advanced'] ) ) {
			$value = ChoiceRenderer::parse( $value, $field );
		}
		if ( in_array( $field['type'], ['fieldset_text', 'text_list'] ) ) {
			$value = TableRenderer::parse( $value, $field );
		}
		if ( in_array( $field['type'], ['sidebar'] ) ) {
			$value = BaseRenderer::parse( $value, $field );
		}
		if ( in_array( $field['type'], ['image', 'image_advanced', 'image_upload', 'plupload_image', 'single_image'] ) ) {
			$value = ImageRenderer::parse( $value, $field );
		}
		if ( in_array( $field['type'], ['map', 'osm'] ) ) {
			$value = MapRenderer::parse( $value, $field );
		}
		if ( in_array( $field['type'], ['oembed'] ) ) {
			$value = OembedRenderer::parse( $value, $field );
		}
		if ( in_array( $field['type'], ['post'] ) ) {
			$value = PostRenderer::parse( $value, $field );
		}
		if ( in_array( $field['type'], ['taxonomy'] ) ) {
			$value = TaxonomyRenderer::parse( $value, $field );
		}
		if ( in_array( $field['type'], ['taxonomy_advanced'] ) ) {
			$value = TaxonomyAdvancedRenderer::parse( $value, $field );
		}
		if ( in_array( $field['type'], ['user'] ) ) {
			$value = UserRenderer::parse( $value, $field );
		}
		if ( in_array( $field['type'], ['video'] ) ) {
			$value = VideoRenderer::parse( $value, $field );
		}
		return $value;
	}
}