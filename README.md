# PhpImageOptimizer

A responsive image workflow for optimizing and resizing your images.  Details can be found in the excellent article at [Efficient Image Resizing With ImageMagick](https://www.smashingmagazine.com/2015/06/efficient-image-resizing-with-imagemagick/)

Originally based off (forked) <https://github.com/nwtn/php-respimg>

## Requirements/dependencies

* [PHP >= 7.1.0](http://php.net/)
* [ImageMagick](http://imagemagick.org/)
* [ext-imagick](http://php.net/manual/en/book.imagick.php)

* Optional: For optimization, depending on what settings you pass:
	* [SVGO](https://github.com/svg/svgo)
	* [image_optim](https://github.com/toy/image_optim)
	* [picopt](https://github.com/ajslater/picopt)
	* [ImageOptim](https://imageoptim.com/)

## Installation
### Git
git clone https://github.com/jamesjohnmcguire/PhpImageOptimizer

### Composer
composer require https://packagist.org/packages/digitalzenworks/php-image-optimizer


## Examples

To resize one raster image, without optimization:

```php
$image = new DigitalZenWorks\ImageOptimizer($input_filename);
$image->smartResize($output_width, $output_height, false);
$image->writeImage($output_filename);
```

To resize one raster image and maintain aspect ratio, without optimization:

```php
$image = new DigitalZenWorks\ImageOptimizer($input_filename);
$image->smartResize($output_width, 0, false);
$image->writeImage($output_filename);
```

To resize one raster image and maintain aspect ratio, with optimization:

```php
$image = new DigitalZenWorks\ImageOptimizer($input_filename);
$image->smartResize($output_width, 0, true);
$image->writeImage($output_filename);
nwtn\Respimg::optimize($output_filename, 0, 1, 1, 1);
```

To resize a directory of raster images and maintain aspect ratio, with optimization:

```php
$exts = array('jpeg', 'jpg', 'png');
if ($dir = opendir($input_path)) {
	while (($file = readdir($dir)) !== false) {
		$base = pathinfo($file, PATHINFO_BASENAME);
		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		if (in_array($ext, $exts)) {
			$image = new DigitalZenWorks\ImageOptimizer($input_path . '/' . $file);
			$image->smartResize($width, 0, true);
			$image->writeImage($output_path . '/' . $base . '-w' . $w . '.' . $ext);
		}
	}
}
DigitalZenWorks\ImageOptimizer::optimize($output_path, 0, 1, 1, 1);
```

To resize a directory of raster images and SVGs and maintain aspect ratio, with optimization:

```php
$exts = array('jpeg', 'jpg', 'png');
if ($dir = opendir($input_path)) {
	while (($file = readdir($dir)) !== false) {
		$base = pathinfo($file, PATHINFO_BASENAME);
		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		if (in_array($ext, $exts)) {
			$image = new DigitalZenWorks\ImageOptimizer($input_path . '/' . $file);
			$image->smartResize($width, 0, true);
			$image->writeImage($output_path . '/' . $base . '-w' . $w . '.' . $ext);
		} elseif ($ext === 'svg') {
			copy($input_path . '/' . $file, $output_path . '/' . $file);
			DigitalZenWorks\ImageOptimizer::rasterize($input_path . '/' . $file, $output_path . '/', $width, 0);
		}
	}
}
DigitalZenWorks\ImageOptimizer::optimize($output_path, 3, 1, 1, 1);
```

## Contributing

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

Distributed under the MIT License. See `LICENSE` for more information.

## Contact

James John McGuire - [@jamesmc](https://twitter.com/jamesmc) - jamesjohnmcguire@gmail.com

Project Link: [https://github.com/jamesjohnmcguire/PhpImageOptimizer](https://github.com/jamesjohnmcguire/PhpImageOptimizer)
