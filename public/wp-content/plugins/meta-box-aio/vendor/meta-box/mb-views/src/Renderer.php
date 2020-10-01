<?php
namespace MBViews;

use Twig_Environment;

class Renderer {
	private $meta_box_renderer;

	public function __construct( $meta_box_renderer ) {
		$this->meta_box_renderer = $meta_box_renderer;
	}

	/**
	 * Render a view.
	 *
	 * @param int|obj $post Post object or ID.
	 */
	public function render( $view, $data = [] ) {
		// ALlow developers to bypass the default renderer and use an alternative method like Timber.
		if ( array_key_exists( 'mbv_render_output', $GLOBALS['wp_filter'] ) ) {
			return apply_filters( 'mbv_render_output', '', $view, $data );
		}

		$view = get_post( $view );

		// Render the view with Twig.
		$loader = new TwigLoader;
		$twig   = new Twig_Environment( $loader, ['autoescape' => false] );

		// Proxy for all PHP/WordPress functions under 'mb' namespace.
		$twig->addGlobal( 'mb', new TwigProxy );

		$data   = array_merge( $this->get_data(), $data );
		$output = $twig->render( $view->post_name, $data );

		// Allow to run shortcodes, blocks.
		$output = do_shortcode( $output );
		$output = do_blocks( $output );

		return $output;
	}

	private function get_data() {
		$data = [];
		$data = array_merge( $data, $this->get_post_data() );
		$data = array_merge( $data, $this->get_term_data() );
		$data = array_merge( $data, $this->get_user_data() );
		$data = array_merge( $data, $this->get_site_data() );
		if ( class_exists( 'MB_Relationships_API' ) ) {
			$data = array_merge( $data, $this->get_relationship_data() );
		}

		return $data;
	}

	private function get_post_data() {
		$posts = array_map( function( $post ) {
			$post_object = new Renderer\Post( $this->meta_box_renderer );
			$post_object->set_post( $post );
			return $post_object;
		}, $GLOBALS['wp_query']->posts );

		return [
			'query' => ['posts' => $posts],
			'post'  => empty( $posts ) ? null : reset( $posts )
		];
	}

	private function get_term_data() {
		$term_object = new Renderer\Term( $this->meta_box_renderer );
		$term_object->set_term( get_queried_object() );

		return [
			'term' => $term_object,
		];
	}

	private function get_user_data() {
		$user_object = new Renderer\User( $this->meta_box_renderer );
		$user_object->set_user_id( get_current_user_id() );

		$post          = get_post();
		$author_object = new Renderer\User( $this->meta_box_renderer );
		$author_object->set_user_id( $post->post_author );

		return [
			'user'   => $user_object,
			'author' => $author_object,
		];
	}

	private function get_site_data() {
		return [ 'site' => new Renderer\Site( $this->meta_box_renderer ) ];
	}

	private function get_relationship_data() {
		$relationships = array_map( function( $relationship ) {
			$relationship_object = new Renderer\Relationship( $this->meta_box_renderer );
			$relationship_object->set_relationship( $relationship );
			return $relationship_object;
		}, \MB_Relationships_API::get_all_relationships_settings() );
		return [ 'relationships' => $relationships ];
	}
}