<?php
/**
 * Image Optimizer	<https://github.com/jamesjohnmcguire/PhpImageOptimizer>
 *
 * @package   ImageOptimizer
 * @author    David Newton <david@davidnewton.ca>
 * @author    James John McGuire <jamesjohnmcguire@gmail.com>
 * @copyright 2021 - 2025 James John McGuire
 * @license   MIT https://opensource.org/licenses/MIT
 * @version   1.5.25
 * @link      https://github.com/DigitalZenWorks/PhpImageOptimizer
 */

declare(strict_types=1);

require_once __DIR__ . '/../SourceCode/ImageOptimizer.php';
use DigitalZenWorks\ImageOptimizer;

$extensions =
[
	'jpeg',
	'jpg',
	'png'
];

$pathRasterI = __DIR__ . '/assets/raster';
$pathRasterO = __DIR__ . '/generated/default/raster';
$pathSvgI = __DIR__ . '/assets/svg';
$pathSvgO = __DIR__ . '/generated/default/svg';

$widths =
[
	320,
	640,
	1280
];

// These are the saved results from previous runs.
$respImgResults =
[
	'1A-1.jpg'         =>
	[
		'320'  =>
		[
			'size'   => 17804,
			'width'  => 320,
			'height' => 280
		],
		'640'  =>
		[
			'size'   => 51670,
			'width'  => 640,
			'height' => 560
		],
		'1280' =>
		[
			'size'   => 159779,
			'width'  => 1280,
			'height' => 1120
		]
	],
	'3C-2.png'         =>
	[
		'320'  =>
		[
			'size'   => 27486,
			'width'  => 320,
			'height' => 259
		],
		'640'  =>
		[
			'size'   => 74711,
			'width'  => 640,
			'height' => 517
		],
		'1280' =>
		[
			'size'   => 177569,
			'width'  => 1280,
			'height' => 1035
		]
	],
	'TesterImage6.jpg' =>
	[
		'320'  =>
		[
			'size'   => 16929,
			'width'  => 320,
			'height' => 194
		],
		'640'  =>
		[
			'size'   => 54047,
			'width'  => 640,
			'height' => 388
		],
		'1280' =>
		[
			'size'   => 181505,
			'width'  => 1280,
			'height' => 776
		]
	]
];

// Setup output directories.
if (!file_exists($pathRasterO))
{
    mkdir($pathRasterO, 0777, true);
}

if (!file_exists($pathSvgO))
{
    mkdir($pathSvgO, 0777, true);
}

// Resize raster inputs.
$directoryHandle = opendir($pathRasterI);

if ($directoryHandle !== false)
{
	$file = readdir($directoryHandle);

	while ($file !== false)
	{
		echo "Processing: $file\r\n";

		$base = pathinfo($file, PATHINFO_BASENAME);
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		$inArray = in_array($extension, $extensions);

		if ($inArray === true)
		{
			foreach ($widths as $width)
			{
				echo 'Resizing to ' . $width . "…\r\n";
				$image = new ImageOptimizer($pathRasterI . '/' . $file);
				$image->smartResize($width, 0, true);
				$destination = $pathRasterO . '/' . $base . '-w' . $width .
					'.' . $extension;
				$image->writeImage($destination);
				
				echo "Comparing to previous results\r\n";
				$baseName = basename($destination);
				$original = ".$extension-w$width";
				$baseName = str_replace($original, '', $baseName);

				$exists = array_key_exists($baseName, $respImgResults);

				if ($exists === false)
				{
					echo "Warning: Results key missing for $baseName!\r\n";
				}
				else
				{
					$baseNameWidth = $respImgResults[$baseName][$width];
					$size = filesize($destination);

					if ($size !== $baseNameWidth['size'])
					{
						echo "File size different from original result!\n";
					}

					[$fileWidth, $height] = getimagesize($destination);

					if ($fileWidth !== $baseNameWidth['width'])
					{
						echo "Image width different from original result!\n";
					}

					if ($height !== $baseNameWidth['height'])
					{
						echo "Image height different from original result!\n";
					}
				}
			}
		}

		$file = readdir($directoryHandle);
	}
}

// Copy SVGs.
$directoryHandle = opendir($pathSvgI);

if ($directoryHandle !== false)
{
	$file = readdir($directoryHandle);

	while ($file !== false)
	{
		$base = pathinfo($file, PATHINFO_BASENAME);
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		if ($extension === 'svg')
		{
			echo 'Copying ' . $file . '…';
			$source = $pathSvgI . '/' . $file;
			$destination = $pathSvgO . '/' . $file;
			copy($source, $destination);
			echo "OK\n";
		}

		$file = readdir($directoryHandle);
	}
}

$imageOptim = ImageOptimizer::isImageOptimEnabled();
$imageUnderscoreOptim = ImageOptimizer::isImageUnderscoreOptimEnabled();
$picOpt = ImageOptimizer::isPicOptEnabled();
$svgo = ImageOptimizer::isSvgoEnabled();

// Only run these tests, if at least one of the programs is present.
if ($imageOptim === true || $imageUnderscoreOptim === true ||
	$picOpt === true || $svgo === true)
{
	// Optimize outputs.
	echo 'Optimizing…';
	$path = __DIR__ . '/generated';
	$result = ImageOptimizer::optimize($path, 3, 1, 1, 1);

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
