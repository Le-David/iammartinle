<?php
$view = MBViews\ActiveView::get_view();
$mode = get_post_meta( $view->ID, 'mode', true );

if ( 'layout' === $mode ) {
	get_header();
}

$meta_box_renderer = new MBViews\Renderer\MetaBox;
$renderer = new MBViews\Renderer( $meta_box_renderer );
echo $renderer->render( $view );

if ( 'layout' === $mode ) {
	get_footer();
}