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
	control.innerHTML = '(' + (this.offset + 1) + '/' + this.numImages + ') <a href="#" onclick="document.getElementById(\'' + this.containerId + '\').instance.prev(); return false" style="border: 0px"><img src="' + servicePath + '/image/gallery_prev.gif" width="20" height="16" alt="PREVIOUS" style="vertical-align: middle"/></a> <a href="#" onclick="document.getElementById(\'' + this.containerId + '\').instance.showImagePopup1(); return false" style="border: 0px"><img src="' + servicePath + '/image/gallery_enlarge.gif" width="70" height="19" alt="ZOOM" style="vertical-align: middle"/></a> <a href="#" onclick="document.getElementById(\'' + this.containerId + '\').instance.next(); return false" style="border: 0px"><img src="' + servicePath + '/image/gallery_next.gif" width="20" height="16" alt="NEXT" style="vertical-align: middle"/></a>';

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
	image.style.cursor = "pointer";

	return image;
};

TTGallery.prototype.getCaption = function()
{
	var captionText = this.caption[this.offset];
	captionText = captionText.replace(new RegExp("&amp;?", "gi"), "&");
	captionText = captionText.replace(new RegExp("&lt;?", "gi"), "<");
	captionText = captionText.replace(new RegExp("&gt;?", "gi"), ">");
	captionText = captionText.replace(new RegExp("&quot;?", "gi"), "\"");
	captionText = captionText.replace(new RegExp("&#39;?", "gi"), "'");
	
	var caption = document.createElement("div");
	caption.style.textAlign = "center";
	caption.style.marginTop = "8px";
	caption.style.color = "#627e89";
	caption.className = "galleryCaption";
	caption.appendChild(document.createTextNode(captionText));

	return caption;
};

TTGallery.prototype.show = function(offset)
{
	if(this.numImages == 0) {
		var div = document.createElement("div");
		div.style.textAlign = "center";
		div.style.color = "#888";
		div.style.margin = "10px auto";
		div.style.font = "bold 2em/1 Verdana, Sans-serif";
		div.innerHTML = "NO IMAGES";
		this.container.appendChild(div);	
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
	open_img(this.src[this.offset]);
};