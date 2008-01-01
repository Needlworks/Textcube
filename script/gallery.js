/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
function TTGallery(containerId)
{
	this.containerId = containerId;
	this.container = document.getElementById(this.containerId);
	this.container.style.filter = "progid:DXImageTransform.Microsoft.Fade(duration=0.3, overlap=1.0)";
	this.container.style.textAlign = "center";
	this.container.style.width = "100%";
	this.container.instance = this;

	this.numImages = 0;
	this.imageLoaded = 0;
	this.offset = 0;

	this.src = new Array();
	this.caption = new Array();
	this.width = new Array();
	this.height = new Array();
	this.imageCache = new Array();
	
	this.nextText = "NEXT";
	this.prevText = "PREVIOUS";
	this.enlargeText = "ZOOM";
	this.altText = "gellery image";
	
	this.container = null;
};

TTGallery.prototype.appendImage = function(src, caption, width, height)
{
	this.numImages++;

	var imageCache = new Image();
	imageCache.src = src;
	imageCache.onload = function() { var tmp = this.src; };

	this.imageCache[this.imageCache.length] = src;

	this.src[this.src.length] = src;
	this.width[this.width.length] = width;
	this.height[this.height.length] = height;
	this.caption[this.caption.length] = caption;
};

TTGallery.prototype.getControl = function()
{
	var control = document.createElement("div");
	control.style.marginBottom = "10px";
	control.className = "galleryControl";
	control.style.color = "#777";
	control.style.font = "bold 0.9em Verdana, Sans-serif";
	control.innerHTML = '(' + (this.offset + 1) + '/' + this.numImages + ') <a href="#void" onclick="document.getElementById(\'' + this.containerId + '\').instance.prev(); return false;"><img src="' + servicePath + '/image/gallery/gallery_prev.gif" style="vertical-align: middle;" alt="' + this.prevText + '" \/><\/a> <a href="#void" onclick="document.getElementById(\'' + this.containerId + '\').instance.showImagePopup1(); return false;"><img src="' + servicePath + '/image/gallery/gallery_enlarge.gif" style="vertical-align: middle;" alt="' + this.enlargeText + '" \/><\/a> <a href="#void" onclick="document.getElementById(\'' + this.containerId + '\').instance.next(); return false;"><img src="' + servicePath + '/image/gallery/gallery_next.gif" style="vertical-align: middle;" alt="' + this.nextText + '" \/><\/a>';

	return control;
};

TTGallery.prototype.getImage = function()
{
	var image = document.createElement("img");
	image.instance = this;
	image.src = this.src[this.offset];
	image.width = this.width[this.offset];
	image.height = this.height[this.offset];
	image.onclick = this.showImagePopup2;
	image.alt = this.altText;
	image.style.cursor = "pointer";

	return image;
};

TTGallery.prototype.getCaption = function()
{
	var captionText = this.caption[this.offset];
	captionText = captionText.replace(new RegExp("&lt;?", "gi"), "<");
	captionText = captionText.replace(new RegExp("&gt;?", "gi"), ">");
	captionText = captionText.replace(new RegExp("&quot;?", "gi"), "\"");
	captionText = captionText.replace(new RegExp("&#39;?", "gi"), "'");
	captionText = captionText.replace(new RegExp("&amp;?", "gi"), "&");

	captionText = captionText.replace(new RegExp("&lt;?", "gi"), "<");
	captionText = captionText.replace(new RegExp("&gt;?", "gi"), ">");
	captionText = captionText.replace(new RegExp("&quot;?", "gi"), "\"");
	captionText = captionText.replace(new RegExp("&#39;?", "gi"), "'");
	captionText = captionText.replace(new RegExp("&amp;?", "gi"), "&");
	
	var caption = document.createElement("div");
	caption.style.textAlign = "center";
	caption.style.marginTop = "8px";
	caption.className = "galleryCaption";
	caption.appendChild(document.createTextNode(captionText));

	return caption;
};

TTGallery.prototype.show = function(offset)
{
    this.container = document.getElementById(this.containerId);
    
	if(this.numImages == 0) {
		var div = document.createElement("div");
		div.style.textAlign = "center";
		div.style.color = "#888";
		div.style.margin = "10px auto";
		div.style.font = "bold 2em/1 Verdana, Sans-serif";
		div.innerHTML = "NO IMAGES";
		this.container.appendChild(div);
		this.container = null;
		return;
	}

	if(typeof offset == "undefined")
		this.offset = 0;
	else
	{
		if(offset < 0)
			this.offset = this.numImages -1;
		else if(offset >= this.numImages)
			this.offset = 0;
		else
			this.offset = offset;
	}

	if(this.container.filters)
		this.container.filters[0].Apply();

	this.container.innerHTML = "";
	this.container.appendChild(this.getControl());
	this.container.appendChild(this.getImage());
	this.container.appendChild(this.getCaption());

	if(this.container.filters)
		this.container.filters[0].Play();
		
	this.container = null;
};

TTGallery.prototype.prev = function()
{
	this.show(this.offset-1);
};

TTGallery.prototype.next = function()
{
	this.show(this.offset+1);
};

TTGallery.prototype.showImagePopup1 = function()
{
	this.showImagePopup();
};

TTGallery.prototype.showImagePopup2 = function()
{
	this.instance.showImagePopup();
};

TTGallery.prototype.showImagePopup = function(offset)
{
	try {
		open_img(this.src[this.offset]);
	} catch(e) {
		window.open(this.src[this.offset]); 
	}
};
