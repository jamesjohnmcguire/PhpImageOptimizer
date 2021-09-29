<?php

/**
 * php-respimg <https://github.com/nwtn/php-respimg>
 */

namespace nwtn;

use Gumlet\ImageResize;

if ( !class_exists('Client') || !class_exists('ServiceContainer') ) {
	if ( file_exists(__DIR__ . '/../vendor/autoload.php') ) {
		require_once(__DIR__ . '/../vendor/autoload.php');
	} elseif ( file_exists(__DIR__ . '/../../../autoload.php') ) {
		require_once(__DIR__ . '/../../../autoload.php');
	} else {
		die('Couldn’t load required libraries.');
	}
}

/**
 * An Imagick extension to provide better (higher quality, lower file size) image resizes.
 *
 */
class Respimg {

	/**
	 * @param $file
	 * @param $width
	 * @param $height
	 * @param $output
	 *
	 * @return mixed
	 * @throws \Gumlet\ImageResizeException
	 */
	public function resize ( $file, $width, $height, $output ) {

		$image = new ImageResize($file);
		$image->quality_jpg = 100;

		if ( $width && !$height ) {
			$image->resizeToWidth($width);
		};
		if ( $height && !$width ) {
			$image->resizeToHeight($height);
		};
		if ( $width && $height ) {
			$image->resizeToBestFit($width, $height);
		};

		$image->save($output);

		$mozjpegCommand = "cjpeg -quality 85 -outfile {$output} {$output}";
		exec($mozjpegCommand);

		return $output;

	}

}

?>
