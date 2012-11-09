# Histogram

A bookmarklet+webservice that allows anyone to determine the histogram of a photo on a website.

More details are on http://histogram.cgb.im/

## Example

### Original image

`http://farm4.static.flickr.com/3122/2806124294_08cc5efc74.jpg`

![Image](http://farm4.static.flickr.com/3122/2806124294_08cc5efc74.jpg)

### Histogram

`http://histogram.cgb.im/?type=image&rgb=true&image=http://farm4.static.flickr.com/3122/2806124294_08cc5efc74.jpg`

![Histogram](https://chart.googleapis.com/chart?cht=ls&chd=s:AAAAAAAABBCCDDEFFGGHHHHGGFEEEDDEEEFFHIJLOQUZaYXWXXYURRRQMLJIHFFFEEDDCCCCCBCBBBBBBBBBB,AAAAAAAAAAAABBBCCDDEFGHIJJKKLLLLMMLLLKJIHGGFEEDDDDDCDEFHJLOQYmoeZYZZVSTTQMKIGEFECCCBC,AAAAAAAAABBBCCDDEEEFFFFFFFFFGGHHIIJJKKLLLLLKKJIHFEDCBBBBAAAABBEGMScr9qmnifeVNIFFFDCBB&chco=c21f1fAA,99c274AA,519bc2AA&chls=2,5,0%7C2,5,0%7C2,5,0&chs=353x175)

## Usage

### Bookmarklet

Install the bookmarklet from http://histogram.cgb.im/

### Server

You can install the server on your own machine. It is written in Sinatra.

The server is accessible on `http://histogram.cgb.im/?image=[image_url_]&rgb=[true|false]&height=[pixels]&type=[image|html|js]`

* `image`: The image to process
* `rgb`: `true` or `false`, wether to return a RGB histogram or a grayscale one
* `height`: height in pixels up to 385. Width is automatically set to a nice ratio
* `type`: the type of response requested, `image` redirects to the Google charts image, `html` renders a HTML image tag, and `js` is a callback used by the bookmarklet.

## Known problems

* The bookmarklet needs some love as it seems to be broken on many sites. It hasn't been rewritten in ages.

## Changelog

* **2012-11-09** Rewritten in Ruby/Sinatra

## License

See LICENSE