<?php
/**
 * @author    James John McGuire <jamesjohnmcguire@gmail.com>
 * @copyright 2021 James John McGuire
 */
declare(strict_types=1);

namespace DigitalZenWorks\PhpImageOptimizer\UnitTests;

require 'vendor/autoload.php';
require_once 'SourceCode/Respimg.php';

use PHPUnit\Framework\TestCase;
use nwtn\Respimg as Respimg;

/**
 */
final class UnitTests extends TestCase
{
	protected function setUp() : void
	{
	}

	public static function setUpBeforeClass() : void
	{
	}

	public function testImageCopy()
	{
		$source = "Tests/assets/raster/1A-1.jpg";
		$destination = "Tests/assets/raster/2.jpg";
		$image = new Respimg($source);

		$result = $image->writeImage($destination);
		$this->assertTrue($result);

		$exists = file_exists($destination);
		$this->assertTrue($exists);

		// clean up
		unlink($destination);
	}

	public function testSimpleSmartResize()
	{
		$source = "Tests/assets/raster/TesterImage6.jpg";
		$temp = "Tests/assets/raster/TesterImage4temp.jpg";
		$destination = "Tests/assets/raster/TesterImage4b_1280.jpg";
		$image = new Respimg($source);

		$size = filesize($source);
		echo "size of $source is $size\r\n";
		list($width, $height) = getimagesize($source);

		echo "width of $source is $width\r\n";
		echo "width of $height is $height\r\n";
		$image->smartResize($width, $height, false);
		$result = $image->writeImage($temp);
		$this->assertTrue($result);
		$size = filesize($temp);
		echo "size of $temp is $size\r\n";

		$image = new Respimg($temp);

		$width = 1280;
		$image->smartResize($width, 0, false);

		$result = $image->writeImage($destination);
		$this->assertTrue($result);

		$exists = file_exists($destination);
		$this->assertTrue($exists);

		list($width, $height) = getimagesize($destination);

		$this->assertEquals($width, 1280);

		// clean up
		// unlink($destination);
	}
}
