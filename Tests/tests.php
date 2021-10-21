<?php
/**
 * Image Optimizer	<https://github.com/jamesjohnmcguire/PhpImageOptimizer>
 *
 * @package   ImageOptimizer
 * @author    David Newton <david@davidnewton.ca>
 * @author    James John McGuire <jamesjohnmcguire@gmail.com>
 * @copyright 2021 James John McGuire
 * @license   MIT https://opensource.org/licenses/MIT
 * @version   1.3.4
 */

// load the library
require_once __DIR__ . '/../SourceCode/ImageOptimizer.php';
use DigitalZenWorks\ImageOptimizer;

// define the types of raster files we’re allowing
$extensions =
[
	'jpeg',
	'jpg',
	'png'
];

// setup
$path_raster_i = __DIR__ . '/assets/raster';
$path_raster_o = __DIR__ . '/generated/default/raster';
$path_svg_i = __DIR__ . '/assets/svg';
$path_svg_o = __DIR__ . '/generated/default/svg';

// widths
$widths =
[
	320, 640, 1280
];

// resize raster inputs
$directoryHandle = opendir($path_raster_i);

if ($directoryHandle !== false)
{
	while (($file = readdir($directoryHandle)) !== false)
	 {
		$base = pathinfo($file, PATHINFO_BASENAME);
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		if (in_array($extension, $extensions))
		{
			foreach ($widths as $width)
			{
				echo 'Resizing ' . $file . ' to ' . $width . '…';
				$image = new ImageOptimizer($path_raster_i . '/' . $file);
				$image->smartResize($width, 0, true);
				$destination = $path_raster_o . '/' . $base . '-w' . $width .
					'.' . $extension;
				$image->writeImage($destination);
				echo "OK\n";
			}
		}
	}
}

// copy SVGs
if ($directoryHandle = opendir($path_svg_i))
{
	while (($file = readdir($directoryHandle)) !== false)
	{
		$base = pathinfo($file, PATHINFO_BASENAME);
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		if ($extension === 'svg')
		{
			echo 'Copying ' . $file . '…';
			$source = $path_svg_i . '/' . $file;
			$destination = $path_svg_o . '/' . $file;
			copy($source, $destination);
			echo "OK\n";
		}
	}
}

$imageOptim = ImageOptimizer::isImageOptimEnabled();
$imageUnderscoreOptim = ImageOptimizer::isImageUnderscoreOptimEnabled();
$picOpt = ImageOptimizer::isPicOptEnabled();
$svgo = ImageOptimizer::isSvgoEnabled();

// only run these tests, if at least one of the programs is present
if ($imageOptim === true || $imageUnderscoreOptim == true || $picOpt === true ||
	$svgo == true)
{
	// optimize outputs
	echo 'Optimizing…';
	$result = ImageOptimizer::optimize( __DIR__ . '/generated', 3, 1, 1, 1);

	if ($result === true)
	{
		echo "OK\n";
	}
	else
	{
		echo "failed\n";
	}
}

echo "Done\n";

?>
