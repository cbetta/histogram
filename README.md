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

Drag the bookmark below to your bookmarks toolbar to use the bookmarklet.

<a href="javascript:
(function(){
/*
   * To convert this code into a true bookmarklet, use http://subsimple.com/bookmarklets/jsbuilder.htm
   */
var host=&quot;http://histogram.cgb.im/&quot;;
var show_rgb=true;
var rgb_checkbox=null;
loadFloatingDiv();
loadLoader();
bindImages();
function loadFloatingDiv(){
if(!document.getElementById('histogram_floating')){
var floating=document.createElement('div');
floating.style.position='absolute';
floating.style.width='100%';
floating.style.height='20px';
floating.style.zIndex='1999';
floating.id='histogram_floating';
document.getElementsByTagName('body')[0].appendChild(floating);
reposition();
window.onscroll=function(e){
reposition()
}
;
}
}
function loadLoader(){
if(!document.getElementById('histogram_loader')){
var loader=document.createElement('div');
loader.style.cssFloat='left';
loader.style.margin='10px';
loader.id='histogram_loader';
loader.style.padding=&quot;5px&quot;;
loader.style.backgroundColor=&quot;#FFFFFF&quot;;
loader.style.color=&quot;#888888&quot;;
loader.style.border='10px solid #CCCCCC';
document.getElementById('histogram_floating').appendChild(loader);
}
}
function setLoader(message){
if(document.getElementById('histogram_loader')){
document.getElementById('histogram_loader').innerHTML=message;
}
}
function bindImages(){
initRGBCheck();
setLoader('binding images');
var images=document.getElementsByTagName('img');
for(var i=0;i<images.length;i++){
if(images[i].src!='http://l.yimg.com/g/images/spaceball.gif'
&&images[i].id!='histogram_img'){
images[i].onclick=function (e){
loadHistogram(e);
return false;
}
;
images[i].style.cursor=&quot;pointer&quot;;
}
else{
remove(images[i]);
}
}
setLoader('please select a photo to process by clicking on it. <strong>RGB?</strong>&nbsp;');
document.getElementById('histogram_loader').appendChild(rgb_checkbox);
}
function unBindImages(){
var images=document.getElementsByTagName('img');
for(var i=0;i<images.length;i++){
if(images[i].id!='histogram_img'){
images[i].onclick=function (e){
return true;
}
;
images[i].style.cursor=&quot;default&quot;;
}
}
}
function loadHistogram(e){
var target;
if(!e)
var e=window.event;
if(e.target)
target=e.target;
else
if(e.srcElement)
target=e.srcElement;
var src=encodeURIComponent(target.src);
var url=host+&quot;?type=js&rgb=&quot;+show_rgb+&quot;&image=&quot;+src;
setLoader('processing image');
var scr=document.createElement('script');
scr.src=url;
document.getElementsByTagName('head')[0].appendChild(scr);
scr.onreadystatechange=function (){
if(scr.readyState=='complete'){
var histogram=getSrc();
showHistogram(histogram);
}
}
;
scr.onload=function (){
var histogram=getSrc();
showHistogram(histogram);
}
;
unBindImages();
}
function showHistogram(img_src){
setLoader('loading histogram');
if(img_src==null){
setLoader('sorry, but no histogram could be loaded for this image');
return;
}
if(document.getElementById('histogram_img')){
document.getElementById('histogram_img').src=img_src;
}
else{
var image=document.createElement('img');
image.src=img_src;
image.style.margin='10px';
image.style.cssFloat='right';
image.style.border='10px solid #CCCCCC';
image.style.cursor='pointer';
image.id='histogram_img';
image.onclick=function(e){
remove(document.getElementById('histogram_floating'));
}
;
document.getElementById('histogram_floating').appendChild(image);
}
setLoader('histogram loaded, press anywhere in histogram to exit');
}
function reposition(){
var top=f_scrollTop()+&quot;px&quot;;
document.getElementById('histogram_floating').style.top=top;
}
function f_scrollTop(){
return f_filterResults(
window.pageYOffset?window.pageYOffset:0,
document.documentElement?document.documentElement.scrollTop:0,
document.body?document.body.scrollTop:0
);
}
function f_filterResults(n_win,n_docel,n_body){
var n_result=n_win?n_win:0;
if(n_docel&&(!n_result||(n_result>n_docel)))
n_result=n_docel;
return n_body&&(!n_result||(n_result>n_body))?n_body:n_result;
}
function initRGBCheck(){
var checkbox=document.createElement('input');
checkbox.type='checkbox';
checkbox.id='histogram_checkbox';
checkbox.checked=show_rgb;
checkbox.onclick=function (e){
var target;
if(!e)
var e=window.event;
if(e.target)
target=e.target;
else
if(e.srcElement)
target=e.srcElement;
show_rgb=target.checked;
}
;
rgb_checkbox=checkbox;
}
function remove(element){
element.parentNode.removeChild(element);
}
}
)()" name="bmklink">Histogram it!</a>

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