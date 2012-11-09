javascript:
(function(){
  // VARIABLES

  //this is where all your remote files are stored, please change this to your own server url
  var host = "http://histogram.cgb.im/"; //don't forget the trailing slash
  //change this to false if you don't want a RGB histogram
  var show_rgb = true;

  // END OF VARIABLES

  // THIS IS WHERE THE REAL CODE STARTS

  //this will keep a pre-created checkbox that can be loaded in the loader
  var rgb_checkbox = null;

  //loads the floating div that will hold all elements. this div will float with the page
  loadFloatingDiv();
  // loads the informational loader div
  loadLoader();
  //this function will also call binImages when it is loaded
  bindImages();


  // load the floating div that will make sure this stays on the page

  function loadFloatingDiv() {
    //only load this once
    if (!document.getElementById('histogram_floating'))
    {
      //create the element
      var floating = document.createElement('div');
      //make it nice a floaty
      floating.style.position = 'absolute';
      floating.style.width = '100%';
      floating.style.height = '20px';
      floating.style.zIndex = '1999';
      floating.id = 'histogram_floating';

      //load it in the document
      document.getElementsByTagName('body')[0].appendChild(floating);

      //scroll it to the right position
      reposition();

      //make sure to reposition whenever the window is scrolled
      window.onscroll = function(e) { reposition() };
    }
  }

  // loads the info box that is shown to the user

  function loadLoader() {
    //only load this once
    if (!document.getElementById('histogram_loader'))
    {
      //create a div element
      var loader = document.createElement('div');
      //set some of the css elements
      loader.style.cssFloat = 'left';
      loader.style.margin = '10px';
      loader.id = 'histogram_loader';
      loader.style.padding = "5px";
      loader.style.backgroundColor = "#FFFFFF";
      loader.style.color = "#888888";
      loader.style.border = '10px solid #CCCCCC';
      //load it in the document
      document.getElementById('histogram_floating').appendChild(loader);
    }
  }

  // sets a new message in the loader

  function setLoader(message) {
    if(document.getElementById('histogram_loader')) {
    //don't rely on prototypejs here as it might be not laoded yet
      document.getElementById('histogram_loader').innerHTML = message;
    }
  }

  // This function binds a remote call to any image

  function bindImages() {
    //first also init the checkbox for determining rgb or grayscale histograms
    initRGBCheck();
    //set the loader message
    setLoader('binding images');
    //get all the images
    var images = document.getElementsByTagName('img');
    //loop through all
    for (var i=0;i<images.length;i++) {
      //avoid flickr beach balls
      if (images[i].src != 'http://l.yimg.com/g/images/spaceball.gif'
        && images[i].id != 'histogram_img') {
        //bind the onlcick function
        images[i].onclick = function (e) { loadHistogram(e); return false; };
        images[i].style.cursor = "pointer";
      } else {
        remove(images[i]);
      }

    }
    setLoader('please select a photo to process by clicking on it. <strong>RGB?</strong>&nbsp;');
    document.getElementById('histogram_loader').appendChild(rgb_checkbox);
  }


  // This method unbinds all custom onclicks

  function unBindImages() {
    //get all the images
    var images = document.getElementsByTagName('img');
    //loop through all
    for (var i=0;i<images.length;i++) {
      //avoid unbinding the histogram
      if (images[i].id != 'histogram_img') {
        //bind the onlclick function
        images[i].onclick = function (e) { return true; };
        images[i].style.cursor = "default";
      }
    }
  }


  // This function does a remote call to calculate the histogram, and then shows the histogram

  function loadHistogram(e) {
    //first we are going to figure out what the image was that was clicked
    var target;
    //use the event param, and try to set it if it doesn't exist
    if (!e) var e = window.event;
    if (e.target) target = e.target;
    else if (e.srcElement) target = e.srcElement;
    //now get the src
    var src = encodeURIComponent(target.src);

    var url = host+"?type=js&rgb="+show_rgb+"&image="+src;

    setLoader('processing image');

    //create a script element
    var scr = document.createElement('script');
    //set the remote source of the file
     scr.src = url;
    //append the script to the document head
     document.getElementsByTagName('head')[0].appendChild(scr);


    //wait for the script to load
      scr.onreadystatechange = function () {
          if (scr.readyState == 'complete') {
              var histogram = getSrc();
        showHistogram(histogram);
        }
      };
    //this is the same as above but solves cross-browser issues
      scr.onload = function () {
          var histogram = getSrc();
      showHistogram(histogram);
      };

    //return all images to their previous state
    unBindImages();
  }

  // This method is called by the remote script to show the histogram

  function showHistogram(img_src) {
    setLoader('loading histogram');

    if (img_src == null) {
      setLoader('sorry, but no histogram could be loaded for this image');
      return;
    }

    //don't load this element twice
    if (document.getElementById('histogram_img'))  {
      document.getElementById('histogram_img').src = img_src;
    }
    else {
      //create a image element
      var image = document.createElement('img');
      //set the remote source of the image
       image.src = img_src;
      image.style.margin = '10px';
      image.style.cssFloat = 'right';
      image.style.border = '10px solid #CCCCCC';
      image.style.cursor = 'pointer';
      image.id =   'histogram_img';

      image.onclick = function(e) {
        remove(document.getElementById('histogram_floating'));
      };

      document.getElementById('histogram_floating').appendChild(image);
    }

    setLoader('histogram loaded, press anywhere in histogram to exit');
  }


    function reposition ()
  {
    var top = f_scrollTop()+"px";
    document.getElementById('histogram_floating').style.top = top;
  }

  // Returns the scroll position of the page

  function f_scrollTop() {
    return f_filterResults (
      window.pageYOffset ? window.pageYOffset : 0,
      document.documentElement ? document.documentElement.scrollTop : 0,
      document.body ? document.body.scrollTop : 0
    );
  }

  // Used by f_scrollTop to determine the position

  function f_filterResults(n_win, n_docel, n_body) {
    var n_result = n_win ? n_win : 0;
    if (n_docel && (!n_result || (n_result > n_docel)))
      n_result = n_docel;
    return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
  }

  // Creates the checkbox to determine what kind of histogram to show

  function initRGBCheck() {
    var checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.id = 'histogram_checkbox';
    checkbox.checked = show_rgb;
    checkbox.onclick = function (e) {
      var target;
      //use the event param, and try to set it if it doesn't exist
      if (!e) var e = window.event;
      if (e.target) target = e.target;
      else if (e.srcElement) target = e.srcElement;

      show_rgb = target.checked;
    };

    rgb_checkbox = checkbox;
  }

  // quick copy of the prototypejs code to remove an element

    function remove(element) {
      element.parentNode.removeChild(element);
    }

  // END OF CODE
})
()