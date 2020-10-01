<?php
/**
 * Plugin Name: MB Views
 * Plugin URI:  https://metabox.io/plugins/mb-views/
 * Description: Create views for Meta Box fields and content.
 * Version:     1.6.0
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPL2+
 * Text Domain: mb-views
 * Domain Path: /languages/
 */

// Prevent loading this file directly.
defined( 'ABSPATH' ) || die;

if ( ! function_exists( 'mb_views_load' ) ) {
	if ( file_exists( __DIR__ . '/vendor' ) ) {
		require __DIR__ . '/vendor/autoload.php';
		require __DIR__ . '/vendor/meta-box/meta-box-conditional-logic/meta-box-conditional-logic.php';
	}

	add_action( 'init', 'mb_views_load', 5 );

	function mb_views_load() {
		if ( defined( 'RWMB_VER' ) ) {
			require __DIR__ . '/bootstrap.php';
		}
	}
}
