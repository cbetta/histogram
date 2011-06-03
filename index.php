<?php
/*
 * This file has been distributed via CristianoBetta.com
 * (c) 2008 Cristiano Betta <code@cristianobetta.com>
 *
 * This code has been licensed under the GPL2.0 License
 * http://creativecommons.org/licenses/GPL/2.0/
 */

//redirect to a help page if this was an empty call
$help_url = 'http://blog.cristianobetta.com/2008/08/31/photo-histograms-everywhere/';
if (empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == "/") 
	header('Location: '.$help_url);

//caching toggle
$caching = true;

//time to cache this baby
$cachetime = 24 * 60 * 60; //I have set the cache to a day, assuming pictures don't change that often.

//check if the cache dir exists
$cachedir = 'cache';
if (!file_exists($cachedir)) mkdir($cachedir);

//calculate the filename
$request = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // Requested 
$salt = '0v7b2mr8aoeas39znfuqnobeumtdd8r34zm49q5xc9cjnwz4zdkz0g1xs45goi9b';
$filename = $cachedir."/".md5($request.$salt);

//check if this output already has been cached or has expired
if (file_exists($filename) && (time() - $cachetime < filemtime($filename)) && $caching) 
{
	if(@$HTTP_GET_VARS['type'] == 'image')
		header("Content-Type: image/png");
	
	//show cache if it existed and was valid
	readfile($filename);

  	//make sure to quit
	exit;
}

//start the output buffer
ob_start();

//get the source file url
$source_file = @$HTTP_GET_VARS['image'];
//make sure to replace spaces with %20
$source_file = str_replace(" ", "%20", $source_file);
//error_log($source_file."\n", 3, '/home/histo/logs/debug.log');
//get the mime type
$mime = getMime($source_file);
//create the image
if ($mime == "image/jpeg" || $mime == "image/pjpeg") $image = imagecreatefromjpeg($source_file); 
if ($mime == "image/gif") $image = imagecreatefromgif($source_file); 
if ($mime == "image/png") $image = imagecreatefrompng($source_file); 

///// exit if this image could not be loaded
if (!@$image) {
	//only give a response if this was a javascript call
	if (@$HTTP_GET_VARS['type'] == "js") echo "function getSrc(){return ''}";
		
	//make sure to even cache this response
	cache($caching, $filename);
	exit(0);
}

//check if we need to run a generic histogram or a rgb one
$do_rgb = @$HTTP_GET_VARS['rgb'];
if ($do_rgb != 'true') $do_rgb = false;
else $do_rgb = true;

//check if we need to output a image or javascript
$type = @$HTTP_GET_VARS['type'];

///// histogram options

//width of the histogram
$height = 175;
if (@$HTTP_GET_VARS['height']) $height = $HTTP_GET_VARS['height'];
if ($height > 385) $height = 385;
if ($height < 0) $height = 0;

//height of the histogram
$width = 2.36*$height;
//width of 1 bar in the histogram
$bar_width = 1;

///// get the original image dimensions

//image width
$image_width = imagesx($image);
//image height
$image_height = imagesy($image);
//megapixel
$megapixel = $image_width * $image_height;


///// Initialize all the arrays that will hold the different histograms
$histogram_all = array();
$histogram_red = array();
$histogram_green = array();
$histogram_blue = array();

///// Initialize the histograms
for ($i = 0; $i <= 85; $i++) {
	$histogram_all[$i] = 0;
	$histogram_red[$i] = 0;
	$histogram_green[$i] = 0;
	$histogram_blue[$i] = 0;
}

///// Loop through all the pixels and record them in all the histograms
for ($i=0; $i<$image_width; $i++) {
	for ($j=0; $j<$image_height; $j++) {
        //get the rgb value for current pixel
        $rgb = @ImageColorAt($image, $i, $j); 

        //extract each value for r, g, b  
		$cols = imagecolorsforindex($image, $rgb);
		$r = $cols['red'];
		$g = $cols['green'];
		$b = $cols['blue'];
                
        // get the luminanse from the RGB value
        //$l = round(($r + $g + $b)/3);
        $l = round((0.3*$r + 0.59*$g + 0.11*$b));


		//calculate the indexes (rounding to the nearest (lowest) 5)
		$l_index = ($l - $l%3)/3;
		$r_index = ($r - $r%3)/3;
		$g_index = ($g - $g%3)/3;
		$b_index = ($b - $b%3)/3;
		
        
		// add the points to the histogram
        $histogram_all[$l_index] 	+= $l / $megapixel;    
        $histogram_red[$r_index] 	+= $r / $megapixel;
        $histogram_green[$g_index]  += $g / $megapixel;
        $histogram_blue[$b_index] 	+= $b / $megapixel;
    }
}

//build up the url
$url =  "http://chart.apis.google.com/chart?cht=ls";
//decide what histogram to build
if ($do_rgb == true) {
	//get the max value for any histogram
	$max_array[0] = max($histogram_red);
	$max_array[1] = max($histogram_green);
	$max_array[2] = max($histogram_blue);
	$max = max($max_array);
	//encode the histograms
	$encoded_red  = 	encodeHistogram($histogram_red,$max);
	$encoded_green  = 	encodeHistogram($histogram_green,$max);
	$encoded_blue  = 	encodeHistogram($histogram_blue,$max);

	
	//build up the url more
	$url .= "&chd=s:".$encoded_red.",".$encoded_green.",".$encoded_blue;
	$url .= "&chco=c21f1fAA,99c274AA,519bc2AA";
	$url .= "&chls=2,5,0|2,5,0|2,5,0";
} else {
	//encode the histogram
	$max = max($histogram_all);
	$encoded_all  = 	encodeHistogram($histogram_all, $max);
	//build up the url more
	$url .= "&chd=s:".$encoded_all;
	$url .= "&chco=AAAAAA";
	$url .= "&chls=2,5,0";
	$url .= "&chm=B,AAAAAA,0,0,0";
}
$url .= "&chs=".$width."x".$height;


if ($type == "html") echo "<img src='".$url."'>";
elseif ($type == "js") echo "function getSrc(){return '".$url."'}";
elseif ($type == "image") {
	// send the right headers
	header("Content-Type: image/png");
	// dump the picture and stop the script
	readfile($url);
}

//cache the output
cache($caching, $filename);

//output flush
ob_end_flush();


/**
  * Caches the putput if necessary
  */
function cache($caching, $filename) {	
	//cache the output before outputting it
	if ($caching) {
		// open the cache file for writing
		$fp = fopen($filename, 'w'); 

		// save the contents of output buffer to the file
		fwrite($fp, ob_get_contents());

		// close the 
		fclose($fp);
	}
}

/**
  * This functions encodes the historgram data to a GChart compatible encoding
  */

function encodeHistogram($histogram, $max) {

	// Port of JavaScript from http://code.google.com/apis/chart/
	// http://james.cridland.net/code

	// A list of encoding characters to help later, as per Google's example
	$simpleEncoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$chartData = "";
	for ($i = 0; $i < count($histogram); $i++) {
	   	$currentValue = $histogram[$i];
		
    	if ($currentValue > -1) {
   			$chartData.=substr($simpleEncoding,61*($currentValue/$max),1);
	    }
	    else {
	    	$chartData.='_';
	    }
	}

	// Return the chart data 
	return $chartData;
}


/**
  * Gets the mime of a remote file
  */
function getMime($link) {
	//try and check the mime type
	try {
		//unless the link is empty
		if ($link != "") {
			//try and open the link (read only)
			@$file = fopen($link, "r");
			//if the file is not readable, return false
			if (!$file) {
				return false;
			} 
			//if the file is readable, check the mime type
			else {
				//get the metadata of the file
				$wrapper = stream_get_meta_data($file);
				//get the file headers
				$headers = $wrapper['wrapper_data'];
			
				//loop through the headers and search for the content type
				foreach ($headers as $header) {
					//if the content type matches the provided content type, return true
					if (stripos($header, 'Content-Type')!==false ) {
						return substr($header, 14);
					}
				}	
				//if we exited the loop, clearly no correct header was found, and return false
				return false;
			}
		}
	}
	//return false if fetching the file fails
	catch (Exception $e) {
		return false;
	}
}
?>
