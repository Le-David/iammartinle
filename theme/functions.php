<?php


initTheme();

// add class "active" to active link in navigation
add_filter('nav_menu_css_class' , 'special_nav_class' , 10 , 2);

function special_nav_class ($classes, $item) {
    if (in_array('current-page-ancestor', $classes) || in_array('current-menu-item', $classes) || in_array('current_page_parent', $classes) ){
        $classes[] = 'view-active ';
    }
    return $classes;
}

	function getArticles(){

		$totalPages = get_option('posts_per_page'); // get number of posts per page from settings in admin (settings->reading->Blog pages show at most)

		$query = new WP_Query([
			'post_type' =>'articles',
			'post_status' =>'publish',
			'order' => 'DESC',
			'orderby' => 'date',
			'posts_per_page' => $totalPages,
		]);

		return $query;
		
	}

	function getTaxonomiesArticles(){

		$terms = get_terms( 
			array(
				'taxonomy' => 'article_categories',
				'orderby' => 'name',
				'order' => 'ASC',
				'hide_empty' => false,
			)
		);

		return $terms;
	}

	function getCategoryPosts($category){

		$posts = new WP_Query([
			'post_type' => 'articles',
			'orderby' => 'publish_date',
			'order' => 'DESC',
			'tax_query' => [
				[
					'taxonomy' => 'article_categories',
					'field' => 'slug',
					'terms' => [
						$category
					],
				]
			],
		]);

		return $posts;
	}

	function getMusings(){

		$totalPages = get_option('posts_per_page');

		$query = new WP_Query([
			'post_type' =>'musings',
			'post_status' =>'publish',
			'order' => 'DESC',
			'orderby' => 'date',
			'posts_per_page' => $totalPages,
		]);

		return $query;
		
	}

/**
 * Templates and Page IDs without editor
 * https://www.billerickson.net/disabling-gutenberg-certain-templates/
 */
function ea_disable_editor( $id = false ) {

	$excluded_templates = array(
		'front-page.php',
	);

	$excluded_ids = array(
		// get_option( 'page_on_front' )
	);

	if( empty( $id ) )
		return false;

	$id = intval( $id );
	$template = get_page_template_slug( $id );

	return in_array( $id, $excluded_ids ) || in_array( $template, $excluded_templates );
}

/**
 * Disable Gutenberg by template
 *
 */
function ea_disable_gutenberg( $can_edit, $post_type ) {

	if( ! ( is_admin() && !empty( $_GET['post'] ) ) )
		return $can_edit;

	if( ea_disable_editor( $_GET['post'] ) )
		$can_edit = false;

	return $can_edit;

}
add_filter( 'gutenberg_can_edit_post_type', 'ea_disable_gutenberg', 10, 2 );
add_filter( 'use_block_editor_for_post_type', 'ea_disable_gutenberg', 10, 2 );