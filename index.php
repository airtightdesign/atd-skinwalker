<?php

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Http\Request as Request;

// load the image manipulation library
require 'vendor/autoload.php';

// image manipulation object
Image::configure(['driver' => 'imagick']);

// the request
$request = Request::createFromGlobals();

// the original image to process
$img_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/' . $request->path();

// where we will write the cache file
$cache_path = dirname(__FILE__) . '/cache/' . http_build_query($request->query()) . '_' . basename($img_path);

try {
    // if CACHE HIT
    if (file_exists($cache_path)) {

        $img = Image::make($cache_path);

    }
    // else CACHE MISS
    else {

        $img = Image::make($img_path);

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
        
        $img->save($cache_path);

    }

    echo $img->response();

} catch (Exception $e) {
	http_response_code(404);
	exit;
}


