<?php
/**
 * Image Optimizer	<https://github.com/jamesjohnmcguire/PhpImageOptimizer>
 *
 * @package   ImageOptimizer
 * @author    David Newton <david@davidnewton.ca>
 * @author    James John McGuire <jamesjohnmcguire@gmail.com>
 * @copyright 2021 - 2022 James John McGuire
 * @license   MIT https://opensource.org/licenses/MIT
 * @version   1.4.12
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

// These are the saved results from previous runs.
$respImgResults =
[
	'1A-1.jpg' =>
	[
		'320' =>
		[
			'size' => 17804,
			'width' => 320,
			'height' => 280
		],
		'640' =>
		[
			'size' => 51670,
			'width' => 640,
			'height' => 560
		],
		'1280' =>
		[
			'size' => 159779,
			'width' => 1280,
			'height' => 1120
		]
	],
	'3C-2.png' =>
	[
		'320' =>
		[
			'size' => 27486,
			'width' => 320,
			'height' => 259
		],
		'640' =>
		[
			'size' => 74711,
			'width' => 640,
			'height' => 517
		],
		'1280' =>
		[
			'size' => 177569,
			'width' => 1280,
			'height' => 1035
		]
	],
	'TesterImage6.jpg' =>
	[
		'320' =>
		[
			'size' => 16929,
			'width' => 320,
			'height' => 194
		],
		'640' =>
		[
			'size' => 54047,
			'width' => 640,
			'height' => 388
		],
		'1280' =>
		[
			'size' => 181505,
			'width' => 1280,
			'height' => 776
		]
	]
];

// Setup output directories.
if (!file_exists($path_raster_o))
{
    mkdir($path_raster_o, 0777, true);
}

if (!file_exists($path_svg_o))
{
    mkdir($path_svg_o, 0777, true);
}

// resize raster inputs
$directoryHandle = opendir($path_raster_i);

if ($directoryHandle !== false)
{
	while (($file = readdir($directoryHandle)) !== false)
	 {
		echo "Processing: $file\r\n";

		$base = pathinfo($file, PATHINFO_BASENAME);
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		if (in_array($extension, $extensions))
		{
			foreach ($widths as $width)
			{
				echo 'Resizing to ' . $width . "…\r\n";
				$image = new ImageOptimizer($path_raster_i . '/' . $file);
				$image->smartResize($width, 0, true);
				$destination = $path_raster_o . '/' . $base . '-w' . $width .
					'.' . $extension;
				$image->writeImage($destination);
				
				echo "Comparing to previous results\r\n";
				$baseName = basename($destination);
				$baseName = str_replace(".$extension-w$width", '', $baseName);

				$exists = array_key_exists($baseName,$respImgResults);

				if ($exists === false)
				{
					echo "Warning: Results key missing for $baseName!\r\n";
				}
				else
				{
					$size = filesize($destination);

					if ($size !== $respImgResults[$baseName][$width]['size'])
					{
						echo "File size different from original result!\r\n";
					}

					list($fileWidth, $height) = getimagesize($destination);

					if ($fileWidth !== $respImgResults[$baseName][$width]['width'])
					{
						echo "Image width different from original result!\r\n";
					}

					if ($height !== $respImgResults[$baseName][$width]['height'])
					{
						echo "Image height different from original result!\r\n";
					}
				}
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
