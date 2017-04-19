<?php

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Http\Request as Request;

// load the image manipulation library, cache library, etc
require 'vendor/autoload.php';




// configuration
Image::configure([
	'driver' => 'imagick'
]);

// instantiate request object
$request = Request::createFromGlobals();

// the original image to process
$img_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/' . $request->path();

// attempt to create the image, 404 on failure
try {
	$img = Image::make($img_path);
} catch (Exception $e) {
	http_response_code(404);
	exit;
}

// Image Resize
if ($request->query('a', 'resize') == 'resize') {
	switch ($request->query('r')) {
		case 'contain':
			$img->resize($request->query('w'), $request->query('h'), function($constraint) {
				$constraint->aspectRatio();
			});
		break;
		case 'widen':
			$img->widen($request->query('w'));
		break;
		case 'heighten':
			$img->heighten($request->query('h'));
		break;
		case 'cover':
			$img->fit(
				$request->query('w'), 
				$request->query('h')
			);
	}
}
// Image Crop
else if ($request->query('a') == 'crop') {
	$img->crop(
		$request->query('w'), 
		$request->query('h'), 
		$request->query('x'), 
		$request->query('y')
	);
}

// write DEBUG query params to text
$text = "ATD Skinwalker DEBUG\n";
$text.= "--------------------\n";
$text.= "Image: " . basename($request->path()) . "\n";
foreach ($request->query() as $key => $val) {
	$text.= "$key: $val\n";
}

// write it twice to help reading with overly dark/light images
$img->text($text, 10, 22, function($font) {
    $font->file('assets/InconsolataLGC-OT/Inconsolata-LGC.otf');
    $font->size(12);
    $font->color('#000000');
});
$img->text($text, 11, 21, function($font) {
    $font->file('assets/InconsolataLGC-OT/Inconsolata-LGC.otf');
    $font->size(12);
    $font->color('#ffffff');
});

// output image
echo $img->response();