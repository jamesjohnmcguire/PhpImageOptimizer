<?php
/**
 * Image Optimizer	<https://github.com/jamesjohnmcguire/PhpImageOptimizer>
 *
 * @package   ImageOptimizer
 * @author    David Newton <david@davidnewton.ca>
 * @author    James John McGuire <jamesjohnmcguire@gmail.com>
 * @copyright 2021 - 2023 James John McGuire
 * @license   MIT https://opensource.org/licenses/MIT
 * @version   1.5.16
 */

namespace DigitalZenWorks;

/**
 * ImageOptimizer - An Imagick extension to provide better (higher quality,
 * lower file size) image resizes.
 *
 * This class extends Imagick (<http://php.net/manual/en/book.imagick.php>)
 * based on research into optimal image resizing techniques
 * (<https://github.com/nwtn/image-resize-tests>).
 *
 * Using these methods with their default settings should provide image
 * resizing that is visually indistinguishable from Photoshop’s “Save for Web…”,
 * but at lower file sizes.
 */
class ImageOptimizer extends \Imagick
{
	/**
	 * AlternateResize method.
	 *
	 * Variant method from forked repository avonis/respimg.
	 * Untested! Unverified!  Requires Gumlet\ImageResize.
	 *
	 * @param string  $file        The file to process.
	 * @param integer $width       The width to resize to.
	 * @param integer $height      The height to resize to.
	 * @param string  $destination The file path to save to.
	 *
	 * @return mixed
	 * @throws \Gumlet\ImageResizeException Something went wrong.
	 */
	public function alternateResize(
		string $file,
		int $width,
		int $height,
		string $destination)
	{
		$ouput = null;
		if(defined('USE_VARIANTS') === true)
		{
			$image = new \Gumlet\ImageResize($file);
			$image->quality_jpg = 100; // phpcs:ignore

			if ($width > 0 && $height === 0)
			{
				$image->resizeToWidth($width);
			};

			if ($height > 0 && $width === 0)
			{
				$image->resizeToHeight($height);
			};

			if ($width > 0 && $height > 0)
			{
				$image->resizeToBestFit($width, $height);
			};

			$image->save($destination);

			$mozjpegCommand = "cjpeg -quality 85 -outfile {$destination} {$destination}";
			exec($mozjpegCommand);
		}

		return $destination;
	}

	/**
	 * Generate Webp image.
	 *
	 * Uses either Imagick or GD imagewebp to generate webp image.  Originally
	 * copied from https://www.jclabs.co.uk
	 * /generate-webp-images-in-php-using-and-gd-or-imagick/.
	 *
	 * @param string  $file               Path to image being converted.
	 * @param integer $compressionQuality Quality ranges from 0 (worst quality,
	 *                                    smaller file) to 100 (best quality,
	 *                                    biggest file).
	 *
	 * @return false|string Returns path to generated webp image,
	 *                      otherwise returns false.
	 */
	public static function generateWebpFile(
		string $file,
		int $compressionQuality = 80)
	{
		$result = false;

		$exists = file_exists($file);
		if ($exists === true)
		{
			// If output file already exists return path.
			$outputFile = self::replaceExtension($file, '.webp');

			$exists = file_exists($outputFile);
			if ($exists === true)
			{
				$result = $outputFile;
			}
			else
			{
				$fileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));

				$exists = class_exists('\Imagick');
				if ($exists === true)
				{
					$image = new \Imagick();
					$image->readImage($file);

					if ($fileType === 'png')
					{
						$image->setImageFormat('webp');
						$image->setImageCompressionQuality(
							$compressionQuality);
						$image->setOption('webp:lossless', 'true');
					}

					$image->writeImage($outputFile);
					$result = $outputFile;
				}
			 	else
				{
					$exists = function_exists('imagewebp');

					if ($exists === true)
					{
						switch ($fileType)
						{
							case 'jpeg':
							case 'jpg':
								$image = imagecreatefromjpeg($file);
								break;
							case 'png':
								$image = imagecreatefrompng($file);
								imagepalettetotruecolor($image);
								imagealphablending($image, true);
								imagesavealpha($image, true);
								break;
							case 'gif':
								$image = imagecreatefromgif($file);
								break;
							default:
								$image = null;
								break;
						}
	
						// Save the image.
						$result =
							imagewebp($image, $outputFile, $compressionQuality);
	
						if (false !== $result)
						{
							// Free up memory.
							imagedestroy($image);
	
							$result = $outputFile;
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Checks if the imageoptim program is available on the $PATH.
	 *
	 * @return boolean Indicates whether the imageoptim program is available on
	 *                 the $PATH.
	 */
	public static function isImageOptimEnabled()
	{
		$result = self::isExternalProgramEnabled('imageoptim');

		return $result;
	}

	/**
	 * Checks if the image_optim program is available on the $PATH.
	 *
	 * @return boolean Indicates whether the image_optim program is available on
	 *                 the $PATH.
	 */
	public static function isImageUnderscoreOptimEnabled()
	{
		$result = self::isExternalProgramEnabled('image_optim');

		return $result;
	}

	/**
	 * Checks if the picopt program is available on the $PATH.
	 *
	 * @return boolean Indicates whether the picopt program is available on
	 *                 the $PATH.
	 */
	public static function isPicOptEnabled()
	{
		$result = self::isExternalProgramEnabled('picopt');

		return $result;
	}

	/**
	 * Checks if the svgo program is available on the $PATH.
	 *
	 * @return boolean Indicates whether the svgo program is available on
	 *                 the $PATH.
	 */
	public static function isSvgoEnabled()
	{
		$result = self::isExternalProgramEnabled('svgo');

		return $result;
	}

	/**
	 * Changes the size of an image to the given dimensions and
	 * removes any associated profiles.
	 *
	 * `optimalImage` was originally named thumbnailImage.  But due to PHP's
	 * stricter checking, along the modified default values would result in the
	 * PHP warning of 'Declaration of should be compatible with
	 * Imagick::thumbnailimage.
	 *
	 * `optimalImage` changes the size of an image to the given dimensions and
	 * removes any associated profiles.  The goal is to produce small low cost
	 * thumbnail images suited for display on the Web.
	 *
	 * With the original Imagick optimalImage implementation, there is no way
	 * to choose a resampling filter. This class recreates Imagick’s C
	 * implementation and adds this additional feature.
	 *
	 * Note: <https://github.com/mkoppanen/imagick/issues/90> has been filed
	 * for this issue.
	 *
	 * @param integer $columns The number of columns in the output image.
	 *                         0 = maintain aspect ratio based on $rows.
	 * @param integer $rows    The number of rows in the output image.
	 *                         0 = maintain aspect ratio based on $columns.
	 * @param boolean $bestfit Treat $columns and $rows as a bounding box
	 *                         in which to fit the image.
	 * @param boolean $fill    Fill in the bounding box with
	 *                         the background colour.
	 * @param integer $filter  The resampling filter to use. Refer to
	 *                         the list of filter constants at
	 *                         <http://php.net/manual/en/imagick.constants.php>.
	 *
	 * @return boolean Indicates whether the operation was performed
	 *                 successfully.
	 */
	public function optimalImage(
		int $columns,
		int $rows,
		bool $bestfit = false,
		bool $fill = false,
		int $filter = \Imagick::FILTER_TRIANGLE)
	{
		// Sample factor; defined in original ImageMagick thumbnailImage
		// function the scale to which the image should be resized using
		// the `sample` function.
		$sampleFactor = 5;

		// Filter whitelist.
		$filters =
		[
			\Imagick::FILTER_POINT,
			\Imagick::FILTER_BOX,
			\Imagick::FILTER_TRIANGLE,
			\Imagick::FILTER_HERMITE,
			\Imagick::FILTER_HANNING,
			\Imagick::FILTER_HAMMING,
			\Imagick::FILTER_BLACKMAN,
			\Imagick::FILTER_GAUSSIAN,
			\Imagick::FILTER_QUADRATIC,
			\Imagick::FILTER_CUBIC,
			\Imagick::FILTER_CATROM,
			\Imagick::FILTER_MITCHELL,
			\Imagick::FILTER_LANCZOS,
			\Imagick::FILTER_BESSEL,
			\Imagick::FILTER_SINC
		];

		// Parse parameters given to function.
		$columns = (double) ($columns);
		$rows = (double) ($rows);
		$bestfit = (bool) $bestfit;
		$fill = (bool) $fill;

		// We can’t resize to (0,0).
		if ($rows < 1 && $columns < 1)
		{
			return false;
		}

		// Set a default filter if an acceptable one wasn’t passed.
		$check = in_array($filter, $filters);

		if ($check === false)
		{
			$filter = \Imagick::FILTER_TRIANGLE;
		}

		// Figure out the output width and height.
		$width = (double) $this->getImageWidth();
		$height = (double) $this->getImageHeight();
		$newWidth = $columns;
		$newHeight = $rows;

		$widthFactor = ($columns / $width);
		$heightFactor = ($rows / $height);
		if ($rows < 1)
		{
			$newHeight = round($widthFactor * $height);
		}
		elseif ($columns < 1)
		{
			$newWidth = round($heightFactor * $width);
		}

		// If bestfit is true, the newWidth/newHeight of the image will be
		// different than the columns/rows parameters; those will define a
		// bounding box in which the image will be fit.
		if ($bestfit === true && $widthFactor > $heightFactor)
		{
			$widthFactor = $heightFactor;
			$newWidth = round($heightFactor * $width);
		}
		elseif ($bestfit === true && $heightFactor > $widthFactor)
		{
			$heightFactor = $widthFactor;
			$newHeight = round($widthFactor * $height);
		}

		if ($newWidth < 1)
		{
			$newWidth = 1;
		}

		if ($newHeight < 1)
		{
			$newHeight = 1;
		}

		// If we’re resizing the image to more than about 1/3 it’s original size
		// then just use the resize function.
		if (($widthFactor * $heightFactor) > 0.1)
		{
			$this->resizeImage($newWidth, $newHeight, $filter, 1);
		}
		// If we’d be using sample to scale to smaller than 128x128, use resize.
		elseif ((($sampleFactor * $newWidth) < 128) ||
			(($sampleFactor * $newHeight) < 128))
		{
			$this->resizeImage($newWidth, $newHeight, $filter, 1);
		}
		// Otherwise, use sample first, then resize.
		else
		{
			$temporaryWidth = ($sampleFactor * $newWidth);
			$temporaryHeight = ($sampleFactor * $newHeight);
			$this->sampleImage($temporaryWidth, $temporaryHeight);
			$this->resizeImage($newWidth, $newHeight, $filter, 1);
		}

		// If the alpha channel is not defined, make it opaque.
		$channel = $this->getImageAlphaChannel();
		if ($channel === \Imagick::ALPHACHANNEL_UNDEFINED)
		{
			if (defined('\Imagick::ALPHACHANNEL_OFF') === true)
			{
				$channel = \Imagick::ALPHACHANNEL_OFF;
			}
			else
			{
				$channel = \Imagick::ALPHACHANNEL_OPAQUE;
			}

			$this->setImageAlphaChannel($channel);
		}

		// Set the image’s bit depth to 8 bits.
		$this->setImageDepth(8);

		// Turn off interlacing.
		$this->setInterlaceScheme(\Imagick::INTERLACE_NO);

		// Strip all profiles except color profiles.
		$profiles = $this->getImageProfiles('*', true);

		foreach ($profiles as $key => $value)
		{
			if ($key !== 'icc' && $key !== 'icm' && $key !== 'iptc')
			{
				$this->removeImageProfile($key);
			}
		}

		$exists = method_exists($this, 'deleteImageProperty');

		if ($exists === true)
		{
			$this->deleteImageProperty('comment');
			$this->deleteImageProperty('Thumb::URI');
			$this->deleteImageProperty('Thumb::MTime');
			$this->deleteImageProperty('Thumb::Size');
			$this->deleteImageProperty('Thumb::Mimetype');
			$this->deleteImageProperty('software');
			$this->deleteImageProperty('Thumb::Image::Width');
			$this->deleteImageProperty('Thumb::Image::Height');
			$this->deleteImageProperty('Thumb::Document::Pages');
		}
		else
		{
			$this->setImageProperty('comment', '');
			$this->setImageProperty('Thumb::URI', '');
			$this->setImageProperty('Thumb::MTime', '');
			$this->setImageProperty('Thumb::Size', '');
			$this->setImageProperty('Thumb::Mimetype', '');
			$this->setImageProperty('software', '');
			$this->setImageProperty('Thumb::Image::Width', '');
			$this->setImageProperty('Thumb::Image::Height', '');
			$this->setImageProperty('Thumb::Document::Pages', '');
		}

		// In case user wants to fill use extent for it rather than creating a
		// new canvas …fill out the bounding box.
		if ($bestfit === true && $fill === true && ($newWidth !== $columns ||
			$newHeight !== $rows))
		{
			$extentWidth = 0;
			$extentHeight = 0;

			if ($columns > $newWidth)
			{
				$extentWidth = (($columns - $newWidth) / 2);
			}

			if ($rows > $newHeight)
			{
				$extentHeight = (($rows - $newHeight) / 2);
			}

			$temporaryWidth = (0 - $extentWidth);
			$this->extentImage($columns, $rows, $temporaryWidth, $extentHeight);
		}

		return true;
	}

	/**
	 * Optimizes the image without reducing quality.
	 *
	 * This function calls up to four external programs, which must be installed
	 * and available in the $PATH:
	 *
	 * * SVGO
	 * * image_optim
	 * * picopt
	 * * ImageOptim
	 *
	 * Note that these are executed using PHP’s `exec` command, so there may be
	 * security implications.
	 *
	 * @param string  $path            The path to the file or directory that
	 *                                 should be optimized.
	 * @param integer $svgo            The number of times to optimize using
	 *                                 SVGO.
	 * @param integer $imageOptimizer1 The number of times to optimize using
	 *                                 image_optim.
	 * @param integer $picopt          The number of times to optimize using
	 *                                 picopt.
	 * @param integer $imageOptim      The number of times to optimize using
	 *                                 ImageOptim.
	 *
	 * @return string $output
	 */
	public static function optimize(
		string $path,
		int $svgo = 0,
		int $imageOptimizer1 = 0,
		int $picopt = 0,
		int $imageOptim = 0) : string
	{
		$output = false;

		// Make sure the path is real.
		$exists = file_exists($path);

		if ($exists === true)
		{
			$isDir = is_dir($path);

			if ($isDir === false)
			{
				$position = strrpos($path, '/');
				$baseName = substr($path, 0, $position);
				$dir = escapeshellarg($baseName);

				$position = strrpos($path, '/');
				$position++;
				$baseName = substr($path, $position);
				$file = escapeshellarg($baseName);
			}

			$path = escapeshellarg($path);

			// Make sure we got some ints up in here.
			$svgo = (int) $svgo;
			$imageOptimizer1 = (int) $imageOptimizer1;
			$picopt = (int) $picopt;
			$imageOptim = (int) $imageOptim;

			// Create some vars to store output.
			$output = [];
			$returnVar = 0;

			// If we’re using imageOptimizer1,
			// need to create the YAML config file.
			if ($imageOptimizer1 > 0)
			{
				$contents = "verbose: true\njpegtran:\n  progressive: false\n" .
					"optipng:\n  level: 7\n  interlace: false\npngcrush:\n  " .
					"fix: true\n  brute: true\npngquant:\n  speed: 11\n";
				$tempDirectory = sys_get_temp_dir();
				$yml = tempnam($tempDirectory, 'yml');
				file_put_contents($yml, $contents);
			}

			// Do the svgo optimizations.
			$result = self::svgoOptimize($svgo, $isDir);

			if ($result === false)
			{
				return false;
			}

			if(defined('USE_VARIANTS') === true)
			{
				$disableSVGO = '';

				if ($svgo < 1)
				{
					$disableSVGO = '--no-svgo';
				}
			}

			// Do the imageOptimizer1 optimizations.
			for ($i = 0; $i < $imageOptimizer1; $i++)
			{
				$baseCommand =
					'image_optim -r ' . $path . ' --config-paths ' . $yml;

				if(defined('USE_VARIANTS') === true)
				{
					$baseCommand .= ' ' . $disableSVGO;
				}

				$command = escapeshellcmd($baseCommand);
				exec($command, $output, $returnVar);

				if ($returnVar !== 0)
				{
					unlink($yml);
					return false;
				}
			}

			// Do the picopt optimizations.
			for ($i = 0; $i < $picopt; $i++)
			{
				$command = escapeshellcmd('picopt -r ' . $path);
				exec($command, $output, $returnVar);

				if ($returnVar !== 0)
				{
					unlink($yml);
					return false;
				}
			}

			/*
			 * Do the ImageOptim optimizations
			 * ImageOptim can’t handle the path with single quotes,
			 * so we have to strip them
			 * ImageOptim-CLI has an issue where it only works with a directory,
			 * not a single file
			 */
			for ($i = 0; $i < $imageOptim; $i++)
			{
				if ($isDir === true)
				{
					$baseCommand = 'imageoptim -d ' . $path . ' -q';
				}
				else
				{
					$baseCommand = 'find ' . $dir . ' -name ' . $file;
				}

				if(defined('USE_VARIANTS') === true)
				{
					if ($isDir === true)
					{
						$baseCommand .= $disableSVGO;
					}
					else
					{
						$baseCommand = 'find ' . $dir . ' -name ' . $file;
						$command .= ' | imageoptim ' . $disableSVGO;
					}
				}

				$command = escapeshellcmd($baseCommand);

				if(defined('USE_VARIANTS') === true)
				{
					if ($isDir === false)
					{
						$command .= $disableSVGO;
					}
				}

				exec($command, $output, $returnVar);

				if ($returnVar !== 0)
				{
					unlink($yml);
					return false;
				}
			}
		}

		return $output;
	}

	/**
	 * Resizes the image using smart defaults for high quality and
	 * low file size.
	 *
	 * This function is basically equivalent to:
	 *
	 * $optim == true: `mogrify -path OUTPUT_PATH -filter Triangle \
	 *  -define filter:support=2.0 -thumbnail OUTPUT_WIDTH \
	 *  -unsharp 0.25x0.08+8.3+0.045 -dither None -posterize 136 -quality 82 \
	 *  -define jpeg:fancy-upsampling=off -define png:compression-filter=5 \
	 *  -define png:compression-level=9 -define png:compression-strategy=1 \
	 *  -define png:exclude-chunk=all -interlace none \
	 *  -colorspace sRGB INPUT_PATH`
	 *
	 * $optim == false: `mogrify -path OUTPUT_PATH -filter Triangle \
	 *  -define filter:support=2.0 -thumbnail OUTPUT_WIDTH \
	 *  -unsharp 0.25x0.25+8+0.065 -dither None -posterize 136 -quality 82 \
	 *  -define jpeg:fancy-upsampling=off -define png:compression-filter=5 \
	 *  -define png:compression-level=9 -define png:compression-strategy=1 \
	 *  -define png:exclude-chunk=all -interlace none -colorspace sRGB \
	 *  -strip INPUT_PATH`
	 *
	 * @param integer $columns The number of columns in the output image.
	 *                         0 = maintain aspect ratio based on $rows.
	 * @param integer $rows    The number of rows in the output image.
	 *                         0 = maintain aspect ratio based on $columns.
	 * @param boolean $optim   Whether you intend to perform optimization
	 *                         on the resulting image. Note that setting this to
	 *                         `true` doesn’t actually perform any optimization.
     * @param integer $filter  The filter to use when generating
	 *                         thumbnail image.
     * @param boolean $bestfit Treat $columns and $rows as a bounding box
	 *                         in which to fit the image.
     * @param boolean $crop    Whether you want to crop the image.
	 *
	 * @return void
	 */
	public function smartResize(
		int $columns,
		int $rows,
		bool $optim = false,
		int $filter = \Imagick::FILTER_TRIANGLE,
		bool $bestfit = false,
		bool $crop = false)
	{
		$this->setOption('filter:support', '2.0');

		if(defined('USE_VARIANTS') === true)
		{
			$originalWidth = $this->getImageWidth();
			$originalHeight = $this->getImageHeight();

			if ($originalWidth > $columns && $originalHeight > $rows)
			{
				if ($columns !== 0 && $rows !== 0)
				{
					$newWidth = min($columns, $originalWidth);
					$newHeight = min($rows, $originalHeight);

					$temporaryWidth = ($newWidth / $originalWidth);
					$temporaryHeight = ($newHeight / $originalHeight);
					$sizeRatio = max($temporaryWidth, $temporaryHeight);

					$cropWidth = round($newWidth / $sizeRatio);
					$cropHeight = round($newHeight / $sizeRatio);
					$cropX = floor(($originalWidth - $cropWidth) / 2);
					$cropY = floor(($originalHeight - $cropHeight) / 2);
					$this->cropImage($cropWidth, $cropHeight, $cropX, $cropY);
					$this->setImagePage($cropWidth, $cropHeight, 0, 0);
					$columns = $newWidth;
					$rows = $newHeight;
				}
			}
		}

		if(defined('USE_VARIANTS') === true)
		{
			if(defined('USE_VARIANTS_LANCZOS') === true)
			{
				$this->optimalImage(
					$columns,
					$rows,
					$bestfit,
					false,
					\Imagick::FILTER_LANCZOS);
			}
			else
			{
				$this->optimalImage(
					$columns,
					$rows,
					$bestfit,
					false,
					$filter);
			}

			if ($crop === true)
			{
				$this->cropThumbnailImage($columns, $rows);
			}
		}
		else
		{
			$this->optimalImage(
				$columns,
				$rows,
				false,
				false,
				\Imagick::FILTER_TRIANGLE);
		}

		if ($optim === true)
		{
			$this->unsharpMaskImage(0.25, 0.08, 8.3, 0.045);
		}
		else
		{
			$this->unsharpMaskImage(0.25, 0.25, 8, 0.065);
		}

		$this->posterizeImage(136, false);
		$this->setImageCompressionQuality(82);
		$this->setOption('jpeg:fancy-upsampling', 'off');
		$this->setOption('png:compression-filter', '5');
		$this->setOption('png:compression-level', '9');
		$this->setOption('png:compression-strategy', '1');
		$this->setOption('png:exclude-chunk', 'all');
		$this->setInterlaceScheme(\Imagick::INTERLACE_NO);
		$this->setColorspace(\Imagick::COLORSPACE_SRGB);

		if ($optim === false)
		{
			$this->stripImage();
		}
	}

	/**
	 * Easy image resize function.
	 *
	 * Uses PHP GD library functions.
	 *
	 * @param string  $file           File name to resize.
	 * @param string  $output         Name of the new file. Iinclude path if
	 *                                needed.
	 * @param integer $width          New image width.
	 * @param integer $height         New image height.
	 * @param boolean $proportional   Keep image proportional, default is false.
	 * @param integer $quality        Enter 1-100. 100 is best quality.
	 *                                Default is 100.
	 * @param boolean $grayscale      If true, image will be grayscale.
	 *                                Default is false.
	 * @param boolean $deleteOriginal If true the original file will be deleted.
	 *
	 * @return boolean|resource
	 */
	public static function smartResizeFile(
		string $file,
		string $output = 'file',
		int $width = 0,
		int $height = 0,
		bool $proportional = false,
		int $quality = 100,
		bool $grayscale = false,
		bool $deleteOriginal = true)
	{
		$data = file_get_contents($file);

		$result = self::smartResizeImage(
			$data,
			$output,
			$width,
			$height,
			$proportional,
			$quality,
			$grayscale,
			$deleteOriginal);

		return $result;
	}

	/**
	 * Easy image resize function.
	 *
	 * Uses PHP GD library functions
	 *
	 * @param string  $string         The image data, as a string.
	 * @param string  $output         Name of the new file. Iinclude path if
	 *                                needed.
	 * @param integer $width          New image width.
	 * @param integer $height         New image height.
	 * @param boolean $proportional   Keep image proportional, default is false.
	 * @param integer $quality        Enter 1-100. 100 is best quality.
	 *                                Default is 100.
	 * @param boolean $grayscale      If true, image will be grayscale.
	 *                                Default is false.
	 * @param boolean $deleteOriginal If true the original file will be deleted.
	 *
	 * @return boolean|resource
	 */
	public static function smartResizeImage(
		string $string = null,
		string $output = 'file',
		int $width = 0,
		int $height = 0,
		bool $proportional = false,
		int $quality = 100,
		bool $grayscale = false,
		bool $deleteOriginal = true)
	{
		$result = false;

		if (($height > 0 || $width > 0) && $string === null)
		{
			// Setting defaults and meta.
			$image = '';

			$info = getimagesizefromstring($string);

			list($sourceWidth, $sourceHeight, $imageType) = $info;

			$destinationDimensions = self::calculateDestinationDimensions(
				$width,
				$height,
				$sourceWidth,
				$sourceHeight);

			list($destinationWidth, $destinationHeight) =
				 $destinationDimensions;

			$cropWidth = 0;
			$cropHeight = 0;

			// Calculating proportionality.
			if ($proportional === false)
			{
				$widthFactor = ($sourceWidth / $width);
				$heightFactor = ($sourceHeight / $height);

				$factor = min($widthFactor, $heightFactor);

				$cropWidth = ($width * $factor);
				$cropWidth = ($sourceWidth - $cropWidth);
				$cropWidth = ($cropWidth / 2);

				$cropHeight = ($height * $factor);
				$cropHeight = ($sourceHeight - $cropHeight);
				$cropHeight = ($cropHeight / 2);
			}

			// Loading image to memory according to type.
			$image = imagecreatefromstring($string);

			if ($image !== null)
			{
				// Make the image grayscale, if needed.
				if ($grayscale === true)
				{
					imagefilter($image, IMG_FILTER_GRAYSCALE);
				}

				// This is the resizing/resampling/transparency-preserving
				// magic.
				$imageResized =
					imagecreatetruecolor($destinationWidth, $destinationHeight);

				if ($imageType === IMAGETYPE_GIF ||
					$imageType === IMAGETYPE_PNG)
				{
					$transparency = imagecolortransparent($image);
					$palletsize = imagecolorstotal($image);

					if ($transparency >= 0 && $transparency < $palletsize)
					{
						$transparentColor =
							imagecolorsforindex($image, $transparency);
						$transparency = imagecolorallocate(
							$imageResized,
							$transparentColor['red'],
							$transparentColor['green'],
							$transparentColor['blue']);

						imagefill($imageResized, 0, 0, $transparency);
						imagecolortransparent($imageResized, $transparency);
					}
					elseif ($imageType === IMAGETYPE_PNG)
					{
						imagealphablending($imageResized, false);
						$color = imagecolorallocatealpha(
							$imageResized,
							0,
							0,
							0,
							127);

						imagefill($imageResized, 0, 0, $color);
						imagesavealpha($imageResized, true);
					}
				}

				$sourceWidth = (2 * $cropWidth);
				$sourceWidth = ($sourceWidth - $sourceWidth);
				$sourceHeight = (2 * $cropHeight);
				$sourceHeight = ($sourceHeight - $sourceHeight);

				imagecopyresampled(
					$imageResized,
					$image,
					0,
					0,
					$cropWidth,
					$cropHeight,
					$destinationWidth,
					$destinationHeight,
					$sourceWidth,
					$sourceHeight);

				// Taking care of original, if needed.
				if ($deleteOriginal === true)
				{
					@unlink($output);
				}

				$destination = $output;

				switch ($destination)
				{
					case 'browser':
						$mime = image_type_to_mime_type($imageType);
						header("Content-type: $mime");
						$output = null;
						break;
					case 'file':
						break;
					case 'return':
						$result = $imageResized;
						break;
					default:
						// Nothing to be done.
						break;
				}

				// Writing image according to type to the output destination
				// and image quality.
				switch ($imageType)
				{
					case IMAGETYPE_GIF:
						imagegif($imageResized, $output);
						break;
					case IMAGETYPE_JPEG:
						imagejpeg($imageResized, $output, $quality);
						break;
					case IMAGETYPE_PNG:
						$quality = (0.9 * $quality);
						$quality = ($quality / 10.0);
						$quality = (int) $quality;
						$quality = (9 - $quality);
						imagepng($imageResized, $output, $quality);
						break;
					default:
						// Nothing to be done.
						break;
				}

				$result = true;
			}
		}

		return $result;
	}

	/**
	 * Calculates the destination dimensions.
	 *
	 * @param integer $width        The width to calcuate to.
	 * @param integer $height       The height to calcuate to.
	 * @param integer $sourceWidth  The width of the source image.
	 * @param integer $sourceHeight The height of the source image.
	 *
	 * @return array An array of destination width and height.
	 */
	private static function calculateDestinationDimensions(
		int $width,
		int $height,
		int $sourceWidth,
		int $sourceHeight)
	{
		$dimensions = [];
		$destinationWidth = 0;
		$destinationHeight = 0;

		// Calculating proportionality.
		if ($proportional === true)
		{
			if ($width === 0)
			{
				$factor = ($height / $sourceHeight);
			}
			elseif ($height === 0)
			{
				$factor = ($width / $sourceWidth);
			}
			else
			{
				$widthFactor = ($width / $sourceWidth);
				$heightFactor = ($height / $sourceHeight);
				$factor = min($widthFactor, $heightFactor);
			}

			$destinationWidth = ($sourceWidth * $factor);
			$destinationWidth = round($destinationWidth);

			$destinationHeight = ($sourceHeight * $factor);
			$destinationHeight = round($destinationHeight);
		}
		else
		{
			if ($width > 0)
			{
				$destinationWidth = $width;
			}
			else
			{
				$destinationWidth = $sourceWidth;
			}

			if ($height > 0)
			{
				$destinationHeight = $height;
			}
			else
			{
				$destinationHeight = $sourceHeight;
			}
		}

		$dimensions['width'] = $destinationWidth;
		$dimensions['height'] = $destinationHeight;

		return $dimensions;
	}

	/**
	 * Checks if the external program is available on the $PATH.
	 *
	 * @param string $program The name of the program to check for.
	 *
	 * @return boolean Indicates whether the external program is available on
	 *                 the $PATH.
	 */
	private static function isExternalProgramEnabled(string $program) : bool
	{
		$result = false;

		// Test if external program is present.
		if (PHP_OS_FAMILY === 'Windows')
		{
			$redirect = ' >nul 2>nul';
		}
		else
		{
			$redirect = ' > /dev/null 2>&1';
		}

		$command = $program . $redirect;
		exec($command, $output, $returnResult);

		$isArray = is_array($output);

		if ($isArray === true)
		{
			$empty = empty($output['result']);
			$exists = array_key_exists('result', $output);

			if ($empty !== false && $exists === true && $output['result'] === true)
			{
				$result = $output['result'];
			}
		}

		return $result;
	}

	/**
	 * Replace file extension.
	 *
	 * @param string $fileName     Path to file in question.
	 * @param string $newExtension The new extension.
	 *
	 * @return string Returns path to file with new extension.
	 */
	private static function replaceExtension(
		string $fileName,
		string $newExtension)
	{
		$info = pathinfo($fileName);

		$newName = $info['dirname'] . '/' . $info['filename'] . $newExtension;

		return $newName;
	}

	/**
	 * Optimize an image using the svgo program.
	 *
	 * @param string  $path  The path to the file or directory that should be
	 *                       optimized.
	 * @param integer $svgo  The number of times to optimize the image.
	 * @param boolean $isDir Indicates that the given path is a directory
	 *                       or a file.
	 *
	 * @return boolean Indicates whether the operation was performed
	 *                 successfully.
	 */
	private static function svgoOptimize(string $path, int $svgo, bool $isDir)
	{
		$result = false;

		// Test if external program is present.
		$command = escapeshellcmd('svgo');
		exec($command, $output, $returnVar);

		if ($returnVar === 0)
		{
			// Do the svgo optimizations.
			for ($index = 0; $index < $svgo; $index++)
			{
				$additionalArguments =
					$path . ' --disable removeUnknownsAndDefaults';

				if ($isDir === true)
				{
					$rawCommand = 'svgo -f ' . $additionalArguments;
				}
				else
				{
					$rawCommand = 'svgo -i ' . $additionalArguments;
				}

				$command = escapeshellcmd($rawCommand);
				exec($command, $output, $returnVar);

				if ($returnVar !== 0)
				{
					return false;
				}
			}

			$result = true;
		}

		return $result;
	}
}
