<?php
namespace MBViews\Location;

use WP_REST_Server;
use WP_REST_Request;
use WP_Query;

class Data {
	public function __construct() {
		add_action( 'rest_api_init', [$this, 'register_routes'] );
	}

	public function register_routes() {
		$params = [
			'method' => WP_REST_Server::READABLE,
			'permission_callback' => [$this, 'has_permission'],
			'args' => [
				'name' => [
					'sanitize_callback' => 'sanitize_text_field',
				],
				'term' => [
					'sanitize_callback' => 'sanitize_text_field',
				],
				'selected' => [
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		];
		register_rest_route( 'mbv/location', 'terms', array_merge( $params, [
			'callback' => [$this, 'get_terms'],
		] ) );
		register_rest_route( 'mbv/location', 'posts', array_merge( $params, [
			'callback' => [$this, 'get_posts'],
		] ) );
	}

	public function get_terms( WP_REST_Request $request ) {
		$search_term = $request->get_param( 'term' );
		$selected    = $request->get_param( 'selected' );

		$name = $request->get_param( 'name' );
		list( $post_type, $taxonomy ) = explode( ':', $name );

		$args = array_filter( [
			'taxonomy'   => $taxonomy,
			'name__like' => $search_term,
			'number'     => 10,
			'fields'     => 'id=>name',
			'orderby'    => 'name',
		] );
		$args['hide_empty']             = false;
		$args['count']                  = false;
		$args['update_term_meta_cache'] = false;

		if ( $selected ) {
			$args['exclude'] = $selected;
		}

		$cache_key = md5( serialize( $args ) );
		$items     = wp_cache_get( $cache_key, 'mbv-terms' );
		if ( false === $items ) {
			$terms = get_terms( $args );
			$items = [];
			if ( ! $search_term ) {
				$items[] = [
					'value' => 'all',
					'label' => __( 'All', 'mb-views' ),
				];
			}
			if ( $selected ) {
				$items[] = [
					'value' => $selected,
					'label' => get_term( $selected )->name,
				];
			}
			foreach ( $terms as $id => $name ) {
				$items[] = [
					'value' => $id,
					'label' => $name
				];
			}

			// Cache the query.
			wp_cache_set( $cache_key, $items, 'mbv-terms' );
		}

		return $items;
	}

	public function get_posts( WP_REST_Request $request ) {
		$search_term = $request->get_param( 'term' );
		$selected    = $request->get_param( 'selected' );

		$name = $request->get_param( 'name' );
		list( $post_type ) = explode( ':', $name );

		$args = array_filter( [
			'post_type'      => $post_type,
			'post_status'    => 'any',
			'no_found_rows'  => true,
			's'              => $search_term,
			'posts_per_page' => 10,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );
		if ( $selected ) {
			$args['post__not_in'] = [ $selected ];
		}

		$args['update_post_meta_cache'] = false;
		$args['update_post_term_cache'] = false;

		// Get from cache to prevent same queries.
		$last_changed = wp_cache_get_last_changed( 'posts' );
		$key          = md5( serialize( $args ) );
		$cache_key    = "$key:$last_changed";
		$items        = wp_cache_get( $cache_key, 'mbv-posts' );

		if ( false === $items ) {
			$items = [];
			if ( ! $search_term ) {
				$items[] = [
					'value' => 'all',
					'label' => __( 'All', 'mb-views' ),
				];
			}
			if ( $selected ) {
				$items[] = [
					'value' => $selected,
					'label' => get_the_title( $selected ),
				];
			}
			add_filter( 'posts_search', [ $this, 'search_by_post_title_only' ], 10, 2 );
			$query = new WP_Query( $args );
			foreach ( $query->posts as $post ) {
				$items[] = [
					'value' => $post->ID,
					'label' => $post->post_title,
				];
			}
			remove_filter( 'posts_search', [ $this, 'search_by_post_title_only' ] );

			// Cache the query.
			wp_cache_set( $cache_key, $items, 'mbv-posts' );
		}

		return $items;
	}

	public function search_by_post_title_only( $search, &$wp_query ) {
		global $wpdb;
		if ( empty( $search ) || ! empty( $wp_query->query_vars['search_terms'] ) ) {
			return $search;
		}

		$q = $wp_query->query_vars;
		$n = ! empty( $q['exact'] ) ? '' : '%';

		$search = [];
		foreach ( ( array ) $q['search_terms'] as $term ) {
			$search[] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
		}

		$search = ' AND ' . implode( ' AND ', $search );

		return $search;
	}


	public function has_permission() {
		return current_user_can( 'manage_options' );
	}
}