<?php

	/**
	 * An Imagick extension to provide better (higher quality, lower file size) image resizes.
	 *
	 * php-respimg
	 * <https://github.com/nwtn/php-respimg>
	 *
	 * This class extends Imagick (<http://php.net/manual/en/book.imagick.php>) based on
	 * research into optimal image resizing techniques (<https://github.com/nwtn/image-resize-tests>).
	 *
	 * Using these methods with their default settings should provide image resizing that is
	 * visually indistinguishable from Photoshop’s “Save for Web…”, but at lower file sizes.
	 *
	 * @author		David Newton <david@davidnewton.ca>
	 * @copyright	2015 David Newton
	 * @license		https://raw.githubusercontent.com/nwtn/php-respimg/master/LICENSE MIT
	 * @version		1.0.1
	 */

	// load the library
	require_once __DIR__ . '/../src/Respimg.php';
	use nwtn\Respimg;


	// define the types of raster files we’re allowing
	$exts = array(
		'jpeg',
		'jpg',
		'png'
	);

	// setup
	$path_raster_i = __DIR__ . '/assets/raster';
	$path_raster_o = __DIR__ . '/generated/default/raster';
	$path_svg_i = __DIR__ . '/assets/svg';
	$path_svg_o = __DIR__ . '/generated/default/svg';

	// widths
	$widths = array(320, 640, 1280);

	// resize raster inputs
	if ($dir = opendir($path_raster_i)) {
		while (($file = readdir($dir)) !== false) {
			$base = pathinfo($file, PATHINFO_BASENAME);
			$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

			if (in_array($ext, $exts)) {
				foreach ($widths as $w) {
					echo 'Resizing ' . $file . ' to ' . $w . '…';
					$image = new Respimg($path_raster_i . '/' . $file);
					$image->smartResize($w, 0, true);
					$image->writeImage($path_raster_o . '/' . $base . '-w' . $w . '.' . $ext);
					echo "OK\n";
				}
			}
		}
	}

	// copy SVGs
	if ($dir = opendir($path_svg_i)) {
		while (($file = readdir($dir)) !== false) {
			$base = pathinfo($file, PATHINFO_BASENAME);
			$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

			if ($ext === 'svg') {
				echo 'Copying ' . $file . '…';
				copy($path_svg_i . '/' . $file, $path_svg_o . '/' . $file);
				echo "OK\n";
			}
		}
	}

	// optimize outputs
	echo 'Optimizing…';
	if (Respimg::optimize( __DIR__ . '/generated', 3, 1, 1, 1)) {
		echo "OK\n";
	} else {
		echo "failed\n";
	}

	echo "Done\n";

?>
