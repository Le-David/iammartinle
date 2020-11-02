<?php

// remove (wautop) tags "p" when using "apply_filters"
remove_filter( 'the_content', 'wpautop' );
remove_filter( 'the_excerpt', 'wpautop' );

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