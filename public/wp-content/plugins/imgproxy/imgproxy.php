<?php

/*
Plugin Name: Imgproxy
Description: Dynamic image resizing
Author: manGoweb / Mikulas Dite
Version: 2.1
Author URI: https://www.mangoweb.cz
*/

add_action('plugins_loaded', 'imgproxy_init');

function imgproxy_init()
{
	if (defined('IMGPROXY_ENABLED') && IMGPROXY_ENABLED === false) {
		return;
	}
	if (!defined('IMGPROXY_KEY') || empty(IMGPROXY_KEY)) {
		throw new \Exception('IMGPROXY_KEY undefined');
	}
	if (!defined('IMGPROXY_SALT') || empty(IMGPROXY_SALT)) {
		throw new \Exception('IMGPROXY_SALT undefined');
	}

	$keyBin = pack('H*', IMGPROXY_KEY);
	if (empty($keyBin)) {
		throw new \Exception('IMGPROXY_KEY expected to be hex-encoded');
	}
	define('IMGPROXY_KEY_BIN', $keyBin);
	$saltBin = pack('H*', IMGPROXY_SALT);
	if (empty($saltBin)) {
		throw new \Exception('IMGPROXY_SALT expected to be hex-encoded');
	}
	define('IMGPROXY_SALT_BIN', $saltBin);

	define('IMGPROXY_IN_SCALE', 5000);

	imgproxy_define_class();
	add_filter('big_image_size_threshold', '__return_false');
	add_filter('wp_image_editors', 'imgproxy_noop_editor', 50, 1);
	add_filter('image_downsize', 'imgproxy_image_downsize', 99, 3); // must run after s3 filters
	add_filter('wp_calculate_image_srcset_meta', 'imgproxy_srcset_meta', 50, 3);
	add_filter('wp_calculate_image_srcset', 'imgproxy_srcset', 50, 3);
}

function imgproxy_define_class()
{
	require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
	require_once ABSPATH . WPINC . '/class-wp-image-editor-imagick.php';

	// Dynamically inherit from S3 Editor (if defined) or WP Editor.
	try {
		new ReflectionClass('S3_Uploads_Image_Editor_Imagick');
	} catch (Throwable $_) {
	}
	if (class_exists('S3_Uploads_Image_Editor_Imagick')) {
		class Imgproxy_Parent extends S3_Uploads_Image_Editor_Imagick
		{
		}
	} else {
		class Imgproxy_Parent extends WP_Image_Editor_Imagick
		{
		}
	}

	class WP_Image_Editor_Noop extends Imgproxy_Parent
	{
		// Dummy method that instead of resizing only returns
		// the metadata, which is later send to imgproxy.
		public function multi_resize($sizes)
		{
			$return = [];
			foreach ($sizes as $size => $info) {
				$return[$size] = [
					'path' => 'http://path' . $this->file,
					'file' => 'http://file' . $this->file,
					'width' => $info['width'] ?? null,
					'height' => $info['height'] ?? null,
					'mime-type' => $this->mime_type
				];
			}
			return $return;
		}
	}
}

/**
 * Fix for media.php:1135 wp_calculate_image_srcset,
 * which matches resized url against original url. It must be a substring
 * that is in both urls, length >= 4 and not be a prefix match.
 * Current fix assumes both images are on .org domain.
 */
function imgproxy_srcset_meta($image_meta, $size_array, $image_src, $attachment_id = '')
{
	$image_meta['file'] = '.org';

	// Filter out obsolete image sizes
	if (isset($image_meta['sizes'])) {
		$filteredSizes = [];
		foreach ($image_meta['sizes'] as $key => $value) {
			if (!preg_match('/_old_/', $key)) {
				$filteredSizes[$key] = $value;
			}
		}

		$image_meta['sizes'] = $filteredSizes;
	}

	return $image_meta;
}

/**
 * Splits botched url in format "<httpS3DirOnly>http://files3://<s3domain><s3path>",
 * and builds s3url as
 *  scheme and domain from <httpS3DirOnly>
 *  path and filename from <s3path>
 * and finally creates imgproxy url
 */
function imgproxy_srcset($sources)
{
	foreach ($sources as &$source) {
		$parts = preg_split('~http://files3://~', $source['url'], 2);
		if (count($parts) <= 1) {
			// Filter out invalid file URLs ending with .org (inserted in imgproxy_srcset_meta)
			if (preg_match('/\.org$/', $source['url'])) {
				$source = null;
			}
			continue;
		}
		// split to <scheme://domain> and <path>
		$host = preg_split('~(?<=[^:/])/~', $parts[0], 2)[0];
		$s3url = "$host/$parts[1]";
		// srcset only defines width, keep the second dimension in scale
		$source['url'] = imgproxy_url($s3url, $source['value'], IMGPROXY_IN_SCALE, false);
	}
	return array_filter($sources);
}

function imgproxy_image_downsize($param, $id, $size = 'medium')
{
	// original image does not need resizing, prevent infinite nesting
	if ($size === 'imgproxy_original_url') {
		return false;
	}

	global $_wp_additional_image_sizes;

	$meta = wp_get_attachment_metadata($id);

	if (empty($meta)) {
		return false;
	}

	$ext = 'jpg';
	$scale = 1;

	if (is_string($size)) {
		$parts = explode('-', $size);
		$size = array_shift($parts);

		foreach ($parts as $p) {
			$scalePart = Nette\Utils\Strings::match($p, '~^([0-9\\.,]+)x$~');
			if ($scalePart) {
				$scale = (float) Nette\Utils\Strings::replace($scalePart[1], '~,~', '.');
			} else {
				$ext = $p;
			}
		}
	}

	$sizeIsOk = false;
	// get dimensions for requested size
	if (is_array($size)) {
		$width = $size[0];
		$height = $size[1] ?? 0;
		$crop = $size[2] ?? false;
		$sizeIsOk = true;
	} elseif (!empty($_wp_additional_image_sizes[$size])) {
		$sizeDef = $_wp_additional_image_sizes[$size];
		$width = $sizeDef['width'];
		$height = $sizeDef['height'] ?? 0;
		$crop = $sizeDef['crop'] ?? false;
		$sizeIsOk = true;
	} else {
		$width = get_option("{$size}_size_w");
		$height = get_option("{$size}_size_h") ?: 0;
		$crop = false;
		if ($width) {
			$sizeIsOk = true;
		}
	}

	if (!$sizeIsOk) {
		return false;
	}

	// get original url
	$url = wp_get_attachment_image_url($id, 'imgproxy_original_url', false);
	if ($url === false) {
		return false;
	}

	if (empty($meta['width'])) {
		return false;
	}

	$realWidth = $meta['width'];
	$realHeight = $meta['height'];

	$realRatio = $realWidth / $realHeight;

	$dimensions = image_resize_dimensions(
		$realWidth,
		$realHeight,
		$width * $scale,
		$height * $scale,
		$crop
	);

	if (!$dimensions) {
		if ($width > 3000 || $height > 3000) {
			return false;
		}
		$dimensions = image_resize_dimensions(
			$realWidth,
			$realHeight,
			$realWidth,
			$realWidth * ($height / $width),
			$crop
		);
	}

	list(, , , , $width, $height) = $dimensions;

	if (empty($width) || empty($height)) {
		return false;
	}

	return [imgproxy_url($url, $width, $height, $crop, 'ce', $ext), $width, $height, $crop];
}

function imgproxy_url($url, $width, $height, $crop, $gravity = 'ce', $extension = null)
{
	if (empty($width) || empty($height)) {
		throw new \Exception("Imgproxy: missing width or height. $url $width x $height");
	}

	$originalExt = strtolower(pathinfo($url, PATHINFO_EXTENSION));
	if ($originalExt === 'svg') {
		return $url;
	}
	$resize = $crop ? 'fill' : 'fit';
	$enlarge = 1;
	$extension = $extension ?: ($originalExt === 'png' ? 'png' : 'jpg');
	if ($extension === 'jpg' && $originalExt === 'png') {
		$extension = 'png';
	}
	$extension = trim($extension, '!');
	$encodedUrl = rtrim(strtr(base64_encode($url), '+/', '-_'), '=');
	$path = sprintf(
		'/%s/%d/%d/%s/%d/%s.%s',
		$resize,
		$width,
		$height,
		$gravity,
		$enlarge,
		$encodedUrl,
		$extension
	);
	$signature = rtrim(
		strtr(
			base64_encode(hash_hmac('sha256', IMGPROXY_SALT_BIN . $path, IMGPROXY_KEY_BIN, true)),
			'+/',
			'-_'
		),
		'='
	);
	if (!defined('IMGPROXY_BASE_URL')) {
		throw new \Exception('Missing IMGPROXY_BASE_URL constant.');
	}
	$baseUrl = function_exists('get_img_proxy_base_url')
		? get_img_proxy_base_url()
		: IMGPROXY_BASE_URL;
	return $baseUrl . sprintf('/%s%s', $signature, $path);
}

function imgproxy_noop_editor($editors)
{
	return ['WP_Image_Editor_Noop'];
}
