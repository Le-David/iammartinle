<?php
namespace MBViews;

define( 'MBV_VER', '1.6.0' );
define( 'MBV_DIR', __DIR__ );
list( , $url ) = \RWMB_Loader::get_path( __DIR__ );
define( 'MBV_URL', $url );

// Show Meta Box admin menu.
add_filter( 'rwmb_admin_menu', '__return_true' );

load_plugin_textdomain( 'mv-views', false, plugin_basename( __DIR__ ) . '/languages/' );

new PostType;

new Data;
new Location\Data;
if ( is_admin() ) {
	$location = new Location\Settings;
	new Editor( $location );
	new ConditionalLogic;
	new AdminColumns;
} else {
	$meta_box_renderer = new Renderer\MetaBox;
	$renderer = new Renderer( $meta_box_renderer );

	new Shortcode( $renderer );
	new ActionLoader( $renderer );

	new TemplateLoader( 'singular' );
	new TemplateLoader( 'archive' );
	new TemplateLoader( 'code' );

	new ContentLoader( $renderer, 'singular' );
	new ContentLoader( $renderer, 'archive' );
	new ContentLoader( $renderer, 'code' );
}