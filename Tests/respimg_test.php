<?php
/**
 * php-respimg <https://github.com/nwtn/php-respimg>
 *
 * @author		David Newton <david@davidnewton.ca>
 * @author		James John McGuire <jamesjohnmcguire@gmail.com>
 * @copyright	2021 James John McGuire
 * @license     MIT https://opensource.org/licenses/MIT
 * @version		1.1.1
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
