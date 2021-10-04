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

	public function testBasic()
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
}
