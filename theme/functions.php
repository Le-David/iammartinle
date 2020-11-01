<?php

// remove (wautop) tags "p" when using "apply_filters"
remove_filter( 'the_content', 'wpautop' );
remove_filter( 'the_excerpt', 'wpautop' );

initTheme();

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