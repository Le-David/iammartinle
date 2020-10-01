<?php
namespace MBViews;

class Shortcode {
	private $renderer;

	public function __construct( $renderer ) {
		$this->renderer = $renderer;

		add_shortcode( 'mbv', [ $this, 'render' ] );
	}

	public function render( $atts ) {
		$atts = shortcode_atts( [
			'id' => null,
		], $atts );
		$id = $atts['id'];
		unset( $atts['id'] );

		return $id ? $this->renderer->render( $id, $atts ) : '';
	}
}