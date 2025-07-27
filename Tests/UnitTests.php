<?php
/**
 * @package   PhpImageOptimizer
 * @author    David Newton <david@davidnewton.ca>
 * @author    James John McGuire <jamesjohnmcguire@gmail.com>
 * @copyright 2021 - 2025 James John McGuire
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   1.5.25
 * @link      https://github.com/DigitalZenWorks/PhpImageOptimizer
 */

declare(strict_types=1);

namespace DigitalZenWorks\PhpImageOptimizer\UnitTests;

require 'vendor/autoload.php';
require_once 'SourceCode/ImageOptimizer.php';

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use DigitalZenWorks\ImageOptimizer;

/**
 * Unit tests class for the ImageOptimizer library.
 *
 * This class contains unit tests for various functionality of the ImageOptimizer
 * including WebP generation, image copying, resizing, and file operations.
 */
final class UnitTests extends TestCase
{
	/**
	 * Set up test environment before each test method.
	 *
	 * This method is called before each test method execution.
	 * Currently empty but available for future test setup requirements.
	 *
	 * @return void
	 */
	protected function setUp() : void
	{
	}

	/**
	 * Set up test environment before the first test method.
	 *
	 * This method is called once before the first test method execution.
	 * Currently empty but available for future test setup requirements.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass() : void
	{
	}

	/**
	 * Test WebP generation with a non-existent file.
	 *
	 * Tests that the generateWebpFile method returns false when attempting
	 * to generate a WebP file from a non-existent source file.
	 *
	 * @return void
	 */
	#[Test]
	public function testGenerateWebpFail()
	{
		$source = 'Tests/assets/raster/1xx.jpg';
		$result = ImageOptimizer::generateWebpFile($source);

		$this->assertEquals(false, $result);
	}

	/**
	 * Test successful WebP generation.
	 *
	 * Tests that the generateWebpFile method successfully creates a WebP file
	 * from a valid JPEG source and returns the path to the generated file.
	 * Also verifies the generated file exists and cleans it up after testing.
	 *
	 * @return void
	 */
	#[Test]
	public function testGenerateWebpSuccess()
	{
		$source = 'Tests/assets/raster/1A-1.jpg';
		$result = ImageOptimizer::generateWebpFile($source);

		$this->assertNotEquals(false, $result);

		$exists = file_exists($result);
		$this->assertTrue($exists);

		// Clean up.
		unlink($result);
	}

	/**
	 * Test basic image copying functionality.
	 *
	 * Tests that an ImageOptimizer instance can successfully copy an image
	 * from source to destination using the writeImage method. Verifies the
	 * destination file is created and cleans it up after testing.
	 *
	 * @return void
	 */
	#[Test]
	public function testImageCopy()
	{
		$source = 'Tests/assets/raster/1A-1.jpg';
		$destination = 'Tests/assets/raster/2.jpg';
		$image = new ImageOptimizer($source);

		$result = $image->writeImage($destination);
		$this->assertTrue($result);

		$exists = file_exists($destination);
		$this->assertTrue($exists);

		// Clean up.
		unlink($destination);
	}

	/**
	 * Test image copying with size verification.
	 *
	 * Tests that an ImageOptimizer instance can copy an image while maintaining
	 * specific dimensions through smartResize. Verifies both the source and
	 * destination file sizes are as expected, and cleans up the test file.
	 *
	 * @return void
	 */
	#[Test]
	public function testImageCopyCheckSizes()
	{
		$source = 'Tests/assets/raster/TesterImage6.jpg';
		$destination = 'Tests/assets/raster/TesterImage4temp.jpg';

		$image = new ImageOptimizer($source);

		[$width, $height] = getimagesize($source);
		$image->smartResize($width, $height, false);

		$result = $image->writeImage($destination);
		$this->assertTrue($result);

		$exists = file_exists($destination);
		$this->assertTrue($exists);

		$size = filesize($source);
		$this->assertEquals(457930, $size);

		$size = filesize($destination);
		$this->assertEquals(454315, $size);

		// Clean up.
		unlink($destination);
	}

	/**
	 * Test simple smart resize functionality.
	 *
	 * Tests that the smartResize method can resize an image to a specific width
	 * while maintaining aspect ratio (height set to 0). Verifies the output
	 * image has the expected width and cleans up the test file.
	 *
	 * @return void
	 */
	#[Test]
	public function testSimpleSmartResize()
	{
		$source = 'Tests/assets/raster/TesterImage6.jpg';
		$temp = 'Tests/assets/raster/TesterImage4temp.jpg';
		$destination = 'Tests/assets/raster/TesterImage4b_1280.jpg';
		$image = new ImageOptimizer($source);

		$width = 1280;
		$image->smartResize($width, 0, false);

		$result = $image->writeImage($destination);
		$this->assertTrue($result);

		$exists = file_exists($destination);
		$this->assertTrue($exists);

		[$width, $height] = getimagesize($destination);

		$this->assertEquals(1280, $width);

		// Clean up.
		unlink($destination);
	}
}
