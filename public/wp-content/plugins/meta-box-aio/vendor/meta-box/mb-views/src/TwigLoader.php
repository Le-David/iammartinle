<?php
namespace MBViews;

use Twig_LoaderInterface;
use Twig_SourceContextLoaderInterface;
use Twig_Source;
use Twig_Error_Loader;
use Twig_ExistsLoaderInterface;

class TwigLoader implements Twig_LoaderInterface, Twig_ExistsLoaderInterface, Twig_SourceContextLoaderInterface {
	/**
	 * Gets the source code of a template, given its name.
	 *
	 * @param string $name The name of the template to load.
	 * @return string The template source code.
	 * @throws Twig_Error_Loader When $name is not found.
	 * @deprecated since 1.27 (to be removed in 2.0), implement Twig_SourceContextLoaderInterface.
	 */
	public function getSource( $name ) {
		return $name;
	}

	/**
	 * Returns the source context for a given template logical name.
	 * @param string $name The template logical name. Can be view ID or view slug.
	 * @return Twig_Source
	 * @throws Twig_Error_Loader When $name is not found.
	 */
	public function getSourceContext( $name ) {
		$view = is_numeric( $name ) ? get_post( $name ) : get_page_by_path( $name, OBJECT, 'mb-views' );

		if ( empty( $view ) ) {
			throw new Twig_Error_Loader( sprintf( __( 'View "%s" is not defined.', 'mb-views' ), $name ) );
		}

		$source = $view->post_content;
		if ( $view->post_excerpt ) {
			$source .= "\n<style>\n{$view->post_excerpt}\n</style>";
		}
		if ( $view->post_content_filtered ) {
			$source .= "\n<script>\n{$view->post_content_filtered}\n</script>";
		}

		return new Twig_Source( $source, $name );
	}

	/**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param string $name The name of the template to load
     * @return string The cache key
     * @throws Twig_Error_Loader When $name is not found
     */
	public function getCacheKey( $name ) {
		if ( ! $this->exists( $name ) ) {
			throw new Twig_Error_Loader( sprintf( __( 'View "%s" is not defined.', 'mb-views' ), $name ) );
		}
		return $name;
	}

    public function exists( $name ) {
    	$view = is_numeric( $name ) ? get_post( $name ) : get_page_by_path( $name, OBJECT, 'mb-views' );
    	return ! empty( $view );
    }

	/**
     * Returns true if the template is still fresh.
     * @param string $name The template name.
     * @param int    $time Timestamp of the last modification time of the cached template.
     * @return bool true if the template is fresh, false otherwise.
     * @throws Twig_Error_Loader When $name is not found.
     */
	public function isFresh( $name, $time ) {
		$view = is_numeric( $name ) ? get_post( $name ) : get_page_by_path( $name, OBJECT, 'mb-views' );

		if ( empty( $view ) ) {
			throw new Twig_Error_Loader( sprintf( __( 'View "%s" is not defined.', 'mb-views' ), $name ) );
		}

		return strtotime( $view->post_modified_date ) <= $time;
	}
}