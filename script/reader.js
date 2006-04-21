function TTReader()
{
	this.blogURL = blogURL;
	this.servicePath = servicePath;

	this.optionForceLoadImage = false;
	this.optionForceNewWindow = false;

	this.editingGroupId = 0;
	this.editingFeedId = 0;

	this.selectedGroup = 0;
	this.selectedFeed = 0;
	this.selectedEntry = 0;

	this.selectedGroupObject = null;
	this.selectedFeedObject = null;
	this.selectedEntryObject = null;

	this.unreadOnly = false;
	this.starredOnly = false;
	this.searchKeyword = "";

	this.isPannelCollapsed = false;

	this.resizeStart = 0;
	this.resizeScroll = 0;
	this.resizeHeight = 0;

	this.entriesShown = 0;
	this.entriesTotal = 0;

	this.areEntriesLoading = false;
	
	this.floatingListOffset = 0;
}

TTReader.prototype.setShownEntries = function(count)
{
	this.entriesShown = count;
	document.getElementById("entriesShown").innerHTML = count;
}

TTReader.prototype.setTotalEntries = function(count)
{
	this.entriesTotal = count;
	document.getElementById("entriesTotal").innerHTML = count;
}

TTReader.prototype.setBlogTitle = function(title)
{
	document.getElementById("blogTitle").innerHTML = title;
}

TTReader.prototype.togglePannel = function(event)
{
	try {
		event = STD.event(event);
		event.returnValue = false;
		event.cancelBubble = true;
	} catch(e) { }

	if(this.isPannelCollapsed)
	{
		document.getElementById("pannel").style.display = "block";
		document.getElementById("toggleButton").src = this.servicePath + "/image/owner/reader/barHide.gif";
		setPersonalization("readerPannelVisibility", 1);
	}
	else
	{
		document.getElementById("pannel").style.display = "none";
		document.getElementById("toggleButton").src = this.servicePath + "/image/owner/reader/barShow.gif";
		setPersonalization("readerPannelVisibility", 0);
	}
	document.getElementById("floatingList").style.top = "0px";
	this.floatingListOffset = document.getElementById("floatingList").offsetTop;
	this.isPannelCollapsed = !this.isPannelCollapsed;
}

TTReader.prototype.toggleConfigure = function()
{
	if(document.getElementById("groupsAndFeeds").style.display == "none") {
		document.getElementById("groupsAndFeeds").style.display = "block";
		document.getElementById("configure").style.display = "none";
	}
	else {
		if(this.isPannelCollapsed)
			this.togglePannel();
		document.getElementById("groupsAndFeeds").style.display = "none";
		document.getElementById("configure").style.display = "block";
	}
	document.getElementById("floatingList").style.top = "0px";
	this.floatingListOffset = document.getElementById("floatingList").offsetTop;
}

TTReader.prototype.startResizing = function(event)
{
	event = STD.event(event);
	if(event.target.tagName == "TD" && !Reader.isPannelCollapsed) {
		STD.addEventListener(document);
		document.addEventListener("selectstart", Reader.returnFalse, false);
		document.addEventListener("mousemove", Reader.doResizing, false);
		Reader.resizeStart = event.clientY;
		Reader.resizeScroll = STD.getScrollTop();
		Reader.resizeHeight = parseInt(document.getElementById("groupBox").style.height);
	}
}

TTReader.prototype.returnFalse = function()
{
	return false;
}

TTReader.prototype.finishResizing = function()
{
	if(Reader.resizeStart != 0) {
		STD.removeEventListener(document);
		document.removeEventListener("mousemove", Reader.doResizing, false);
		document.removeEventListener("selectstart", Reader.returnFalse, false);
		setPersonalization("readerPannelHeight", parseInt(document.getElementById("groupBox").style.height));
		Reader.resizeStart = 0;
		document.getElementById("floatingList").style.top = "0px";
		this.floatingListOffset = document.getElementById("floatingList").offsetTop;
	}
}

TTReader.prototype.doResizing = function(event)
{
	event = STD.event(event);
	var height = Reader.resizeHeight + (event.clientY - Reader.resizeStart) + (STD.getScrollTop() - Reader.resizeScroll);
	if(height >= 100) {
		document.getElementById("groupBox").style.height = height + "px";
		document.getElementById("feedBox").style.height = height + "px";
	}
}

TTReader.prototype.selectGroup = function(caller, id)
{
	this.refreshFeedList(id);
	this.refreshEntryList(id, 0);
	this.refreshEntry(id, 0, 0);
	this.selectedGroup = id;
	document.getElementById("groupList0").style.backgroundColor = "";
	if(this.selectedGroupObject != null)
		this.selectedGroupObject.style.backgroundColor = "";
	this.selectedGroupObject = caller.parentNode;
	this.selectedGroupObject.style.backgroundColor = "#CDE3FF";
}

TTReader.prototype.selectFeed = function(caller, id)
{
	this.refreshEntryList(this.selectedGroup, id);
	this.refreshEntry(this.selectedGroup, id, 0);
	this.selectedFeed = id;
	if(this.selectedFeedObject != null)
		this.selectedFeedObject.style.backgroundColor = "";		
	this.selectedFeedObject = caller.parentNode;
	this.selectedFeedObject.style.backgroundColor = "#CDE3FF";
}

TTReader.prototype.selectEntry = function(id)
{
	this.refreshEntry(this.selectedGroup, this.selectedFeed, id);
	this.selectedEntry = id;
	if(this.selectedEntryObject != null)
		this.selectedEntryObject.style.backgroundColor = "";
	this.selectEntryObject(id);
}

TTReader.prototype.selectEntryObject = function(id)
{
	var caller = document.getElementById("entryTitleList" + id);
	if(caller) {
		this.selectedEntryObject = caller.parentNode;
		this.selectedEntryObject.style.backgroundColor = "#FFFFFF";
		var list = document.getElementById("listup");
		if(this.floatingListOffset == 0)
			this.setListPosition();
		this.startScroll("listup", getOffsetTop(caller) - getOffsetTop(list) - (list.offsetHeight / 4));
	}
	else
		setTimeout("Reader.selectEntryObject(" + id + ")", 100);
}

TTReader.prototype.startScroll = function(id, offset)
{
	var obj = document.getElementById(id);
	if(typeof(offset) != "undefined")
		obj.scrollTarget = Math.min(Math.max(parseInt(offset), 0), obj.scrollHeight);
	if(obj.scrollTop != obj.scrollTarget) {
		obj.scrollTop += Math.ceil((obj.scrollTarget - obj.scrollTop) / 5);
		obj.scrollTop += (obj.scrollTop > obj.scrollTarget) ? -1 : 0;
		if(obj.scrollTop < (obj.scrollHeight - obj.offsetHeight))
			setTimeout("Reader.startScroll('" + id + "')", 10);
	}
}

TTReader.prototype.refreshFeedGroup = function()
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/view/group/");
	request.onSuccess = function () {
		Reader.selectedGroup = 0;
		Reader.selectedGroupObject = null;
		document.getElementById("groupBox").innerHTML = this.getText("/response/view");
	}
	request.onError= function () {
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showMessage(s_unknownError + " (refreshFeedGroup)", "center", "bottom");
		}
	}
	request.send("group=0&starred=" + (this.starredOnly?1:0) + "&keyword=" + this.searchKeyword);
}

TTReader.prototype.refreshFeedList = function(group)
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/view/feed/");
	request.onSuccess = function () {
		Reader.selectedFeed = 0;
		Reader.selectedFeedObject = null;
		document.getElementById("feedBox").innerHTML = this.getText("/response/view");
	}
	request.onError= function () {
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showMessage(s_unknownError + " (refreshFeedList)", "center", "bottom");
		}
	}
	request.send("group=" + group + "&starred=" + (this.starredOnly?1:0) + "&keyword=" + this.searchKeyword);
}

TTReader.prototype.refreshEntryList = function(group, feed)
{	
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/view/entries/");
	request.onSuccess = function () {
		document.getElementById("listup").innerHTML = this.getText("/response/view");
		Reader.selectedEntry = this.getText("/response/firstEntryId");
		try {
			Reader.selectedEntryObject = document.getElementById("entryTitleList" + Reader.selectedEntry).parentNode;
		} catch(e) {
			Reader.selectedEntryObject = null;
		}
	}
	request.onError= function () {
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showMessage(s_unknownError + " (refreshEntryList)", "center", "bottom");
		}
	}
	request.send("group=" + group + "&feed=" + feed + "&unread=" + (this.unreadOnly?1:0) + "&starred=" + (this.starredOnly?1:0) + "&keyword=" + this.searchKeyword);
}

TTReader.prototype.imageLoadingFailed = function(event)
{
	event = STD.event(event);
	var obj = event.target;
	if(obj.nodeType == 9)
		obj = event.currentTarget;
	
	STD.removeEventListener(obj);
	obj.removeEventListener("error", Reader.imageLoadingFailed, false);

	obj.src = Reader.blogURL + "/owner/reader/imageLoader/?url=" + encodeURIComponent(obj.src);
}

TTReader.prototype.doPostProcessingOnEntry = function()
{
	if(Reader.optionForceLoadImage) {
		var nodes = searchChildNodes(document.getElementById("entryBody"), "img");
		for(var i=0; i<nodes.length; i++) {
			if(nodes[i].getAttribute("trying") != "trying") {
				nodes[i].setAttribute("trying", "trying");
				STD.addEventListener(nodes[i]);
				nodes[i].addEventListener("error", Reader.imageLoadingFailed, false);
			}
		}
	}
	if(Reader.optionForceNewWindow) {
		var nodes = searchChildNodes(document.getElementById("entryBody"), "a");
		for(var i=0; i<nodes.length; i++) {
			nodes[i].target = "_blank";
		}
	}
}

TTReader.prototype.refreshEntry = function(group, feed, entry)
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/view/entry/");
	request.onSuccess = function () {
		Reader.selectedEntry = this.getText("/response/id");
		document.getElementById("blogTitle").innerHTML = this.getText("/response/blog");
		document.getElementById("entry").innerHTML = this.getText("/response/view");
		try {
			document.getElementById("entryTitleList" + Reader.selectedEntry).className = "read";
		} catch(e) { }
		
		Reader.setListPosition(true);
		Reader.doPostProcessingOnEntry();
		window.scrollTo(0, getOffsetTop(document.getElementById("scrollPoint")));
	}
	request.onError= function () {
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showMessage(s_unknownError + " (refreshEntry)", "center", "bottom");
		}
	}
	request.send("group=" + group + "&feed=" + feed + "&entry=" + entry + "&unread=" + (this.unreadOnly?1:0) + "&starred=" + (this.starredOnly?1:0) + "&keyword=" + this.searchKeyword);
}

TTReader.prototype.prevEntry = function()
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/view/entry/next/");
	request.onSuccess = function () {
		if(this.getText("/response/id") != 0) {
			Reader.selectedEntry = this.getText("/response/id");
			Reader.selectEntry(Reader.selectedEntry);
		}
		else {
			PM.showMessage(s_notFoundPrevPost, "center", "bottom");
		}
	}
	request.onError= function () {
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showMessage(s_unknownError + " (prevEntry)", "center", "bottom");
		}
	}
	request.send("group=" + this.selectedGroup + "&feed=" + this.selectedFeed + "&entry=" + this.selectedEntry + "&unread=" + (this.unreadOnly?1:0) + "&starred=" + (this.starredOnly?1:0) + "&keyword=" + this.searchKeyword);
}

TTReader.prototype.nextEntry = function()
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/view/entry/previous/");
	request.onSuccess = function () {
		if(this.getText("/response/id") != 0) {
			if(document.getElementById("entryTitleList" + this.getText("/response/id")) == null)
				Reader.listScroll(true);
			Reader.selectedEntry = this.getText("/response/id");
			Reader.selectEntry(Reader.selectedEntry);
		}
		else {
			PM.showMessage(s_notFoundNextPost, "center", "bottom");
		}
	}
	request.onError= function () {
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showMessage(s_unknownError + " (nextEntry)", "center", "bottom");
		}
	}
	request.send("group=" + this.selectedGroup + "&feed=" + this.selectedFeed + "&entry=" + this.selectedEntry + "&unread=" + (this.unreadOnly?1:0) + "&starred=" + (this.starredOnly?1:0) + "&keyword=" + this.searchKeyword);
}

TTReader.prototype.addGroup = function(title)
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/group/add/");
	request.onSuccess = function () {
		Reader.selectedGroup = 0;
		Reader.selectedGroupObject = null;
		document.getElementById("groupBox").innerHTML = this.getText("/response/view");
		PM.removeRequest(this);
		PM.showMessage(s_groupAdded, "center", "bottom");
	}
	request.onError= function () {
		PM.removeRequest(this);
		switch(parseInt(this.getText("/response/error")))
		{
			case 1:
				alert(s_enterFeedName);
				break;
			case 2:
				alert(s_groupExists);
				break;
			default:
				PM.showMessage(s_unknownError + " (addGroup)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_addingGroup);
	request.send("title=" + encodeURIComponent(title) + "&current=" + this.selectedGroup);
}

TTReader.prototype.editGroup = function(id, title)
{
	this.editingGroupId = id;

	document.getElementById("groupList").style.display = "none";
	document.getElementById("groupAdder").style.display = "none";
	document.getElementById("groupEditor").style.display = "block";
	document.getElementById("changeGroupTitle").value = title;
	document.getElementById("changeGroupTitle").select();
}

TTReader.prototype.editGroupExecute = function()
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/group/edit/");
	request.onSuccess = function () {
		Reader.selectedGroup = 0;
		Reader.selectedGroupObject = null;
		document.getElementById("groupBox").innerHTML = this.getText("/response/view");
		PM.removeRequest(this);
		PM.showMessage(s_groupModified, "center", "bottom");
	}
	request.onError= function () {
		PM.removeRequest(this);
		switch(parseInt(this.getText("/response/error")))
		{
			case 1:
				alert(s_enterGroupName);
				break;
			case 2:
				break;
			default:
				PM.showMessage(s_unknownError + " (editGroupExecute)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_editingGroup);
	request.send("id=" + this.editingGroupId + "&title=" + encodeURIComponent(document.getElementById("changeGroupTitle").value) + "&current=" + this.selectedGroup);
}

TTReader.prototype.deleteGroup = function()
{
	if(!confirm(s_confirmDelete))
		return;
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/group/delete/");
	request.onSuccess = function () {
		Reader.selectedGroup = 0;
		Reader.selectedGroupObject = null;
		document.getElementById("groupBox").innerHTML = this.getText("/response/view");
		PM.removeRequest(this);
		PM.showMessage(s_groupRemoved, "center", "bottom");
	}
	request.onError= function () {
		PM.removeRequest(this);
		switch(parseInt(this.getText("/response/error")))
		{
			case 1:
				alert(s_groupNotFound);
				break;
			default:
				PM.showMessage(s_unknownError + " (deleteGroup)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_removingGroup);
	request.send("id=" + this.editingGroupId + "&current=" + this.selectedGroup);
}

TTReader.prototype.addFeed = function(url)
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/feed/add/");
	request.onSuccess = function () {
		Reader.selectedFeed = 0;
		Reader.selectedFeedObject = null;
		document.getElementById("feedBox").innerHTML = this.getText("/response/view");
		PM.showMessage(s_feedAdded, "center", "bottom");
		PM.removeRequest(this);
		Reader.refreshEntryList(this.selectedGroup, this.selectedFeed);
		Reader.refreshEntry(this.selectedGroup, this.selectedFeed, 0);
	}
	request.onError= function () {
		PM.removeRequest(this);
		switch(parseInt(this.getText("/response/error")))
		{
			case 1:
				alert(s_feedExists);
				break;
			case 2:
				alert(s_conNotConnect);
				break;
			case 3:
				alert(s_feedBroken);
				break;
			default:
				PM.showMessage(s_unknownError + " (addFeed)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_requestFeed);
	request.send("group=" + this.selectedGroup + "&url=" + encodeURIComponent(url));
}

TTReader.prototype.editFeed = function(id, url)
{
	this.editingFeedId = id;

	document.getElementById("feedList").style.display = "none";
	document.getElementById("feedAdder").style.display = "none";
	document.getElementById("feedEditor").style.display = "block";
	document.getElementById("changeFeedGroup").value = this.selectedGroup;
	document.getElementById("changeFeedURL").value = url;
	document.getElementById("changeFeedURL").select();	
}

TTReader.prototype.editFeedExecute = function()
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/feed/edit/");
	request.onSuccess = function () {
		Reader.selectedFeed = 0;
		Reader.selectedFeedObject = null;
		document.getElementById("feedBox").innerHTML = this.getText("/response/view");
		PM.removeRequest(this);
		PM.showMessage(s_feedModified, "center", "bottom");
	}
	request.onError= function () {
		PM.removeRequest(this);
		switch(parseInt(this.getText("/response/error")))
		{
			case 1:
				alert(s_enterFeedName);
				break;
			case 2:
				break;
			default:
				PM.showMessage(s_unknownError + " (editFeedExecute)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_editingFeed);
	request.send("id=" + this.editingFeedId + "&old_group=" + this.selectedGroup + "&new_group=" + document.getElementById("changeFeedGroup").value + "&url=" + encodeURIComponent(document.getElementById("changeFeedURL").value));
}

TTReader.prototype.deleteFeed = function()
{
	if(!confirm(s_confirmDelete))
		return;
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/feed/delete/");
	request.onSuccess = function () {
		Reader.selectedFeed = 0;
		Reader.selectedFeedObject = null;
		document.getElementById("feedBox").innerHTML = this.getText("/response/view");
		PM.removeRequest(this);
		PM.showMessage(s_feedRemoved, "center", "bottom");
		Reader.refreshEntryList(this.selectedGroup, this.selectedFeed);
		Reader.refreshEntry(this.selectedGroup, this.selectedFeed, 0);
	}
	request.onError= function () {
		PM.removeRequest(this);
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showMessage(s_unknownError + " (deleteFeed)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_removingFeed);
	request.send("id=" + this.editingFeedId + "&group=" + this.selectedGroup);
}

TTReader.prototype.saveSetting = function()
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/config/save/");
	request.onSuccess = function () {
		document.getElementById("groupsAndFeeds").style.display = "block";
		document.getElementById("configure").style.display = "none";
		PM.showMessage(s_saved, "center", "bottom");
	}
	request.onError= function () {
		PM.removeRequest(this);
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showMessage(s_unknownError + " (saveSetting)", "center", "bottom");
		}
	}
	var f = document.forms[0];
	var updateCycle = f.updateCycle ? f.updateCycle.value : "";
	var feedLife = f.feedLife ? f.feedLife.value : "";
	var loadImage = f.loadImage ? (f.loadImage[0].checked ? 1 : 2) : "";
	var allowScript = f.allowScript ? (f.allowScript[0].checked ? 1 : 2) : "";
	var newWindow = f.newWindow ? (f.newWindow[0].checked ? 1 : 2) : "";
	request.send("updateCycle=" + updateCycle + "&feedLife=" + feedLife + "&loadImage=" + loadImage + "&allowScript=" + allowScript + "&newWindow=" + newWindow);
	Reader.optionForceLoadImage = (f.loadImage[1] && f.loadImage[1].checked) ? true : false;
	Reader.optionForceNewWindow = (f.newWindow[1] && f.newWindow[1].checked) ? true : false;
}

TTReader.prototype.markAsUnread = function(id)
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/mark/unread/");
	request.presetProperty(document.getElementById("entryTitleList" + id), "className", "unread");
	request.onSuccess = function () {
		PM.showMessage(s_markedAsUnread, "center", "bottom");
	}
	request.send("id=" + id);
}

TTReader.prototype.toggleStarred = function(id)
{
	if(typeof(id) == "undefined")
		id = this.selectedEntry;

	if(document.getElementById("star" + id)) {
		if(document.getElementById("star" + id).starred == "On") {
			var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/mark/unstar/");
			request._ttreader = this;
			request.onSuccess = function() {
				if(document.getElementById("star" + id)) {
					document.getElementById("star" + id).src = this._ttreader.servicePath + "/image/owner/reader/iconStarOff.gif";
					document.getElementById("star" + id).starred = "Off";
				}
			}
		}
		else {
			var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/mark/star/");
			request._ttreader = this;
			request.onSuccess = function() {
				if(document.getElementById("star" + id)) {
					document.getElementById("star" + id).src = this._ttreader.servicePath + "/image/owner/reader/iconStarOn.gif";
					document.getElementById("star" + id).starred = "On";
				}
			}
		}
		request.send("id=" + id);
	}
}

TTReader.prototype.showUnreadOnly = function()
{
	this.unreadOnly = true;
	this.starredOnly = false;
	this.searchKeyword = "";
	this.refreshFeedGroup();
	this.refreshFeedList(this.selectedGroup);
	this.refreshEntryList(this.selectedGroup, this.selectedFeed);
	this.refreshEntry(this.selectedGroup, this.selectedFeed, 0);
}

TTReader.prototype.showStarredOnly = function()
{
	this.unreadOnly = false;
	this.searchKeyword = "";

	if(this.starredOnly) {
		this.starredOnly = false;
		document.getElementById("starredOnlyIndicator").src = this.servicePath + "/image/owner/reader/iconStarOff.gif";
	}
	else {
		this.starredOnly = true;
		document.getElementById("starredOnlyIndicator").src = this.servicePath + "/image/owner/reader/iconStarOn.gif";
	}
	this.refreshFeedGroup();
	this.refreshFeedList(0);
	this.refreshEntryList(0, 0);
	this.refreshEntry(0, 0, 0);
}

TTReader.prototype.showSearch = function()
{
	this.starredOnly = false;
	this.searchKeyword = document.getElementById("keyword").value;
	this.refreshFeedGroup();
	this.refreshFeedList(0);
	this.refreshEntryList(0, 0);
	this.refreshEntry(0, 0, 0);
}

TTReader.prototype.listScroll = function(force)
{
	var caller = document.getElementById("listup");
	if((caller.offsetHeight + caller.scrollTop > caller.scrollHeight - 100 || force == 1) && this.entriesShown < this.entriesTotal && !this.areEntriesLoading) {
		this.areEntriesLoading = true;
		document.getElementById("feedLoadingIndicator").style.display = "block";
		var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/view/entries/more/");
		request.onSuccess = function () {
			PM.removeRequest(this);
			Reader.areEntriesLoading = false;
			Reader.setShownEntries(Reader.entriesShown + parseInt(this.getText("/response/count")));
			document.getElementById("feedLoadingIndicator").style.display = "none";
			var div = document.createElement("div");
			div.innerHTML = this.getText("/response/view");
			document.getElementById("additionalFeedContainer").appendChild(div);
			if(Reader.entriesShown == Reader.entriesTotal)			
				document.getElementById("iconMoreEntries").style.display = "none";
		}
		request.onError= function () {
			PM.removeRequest(this);
			Reader.areEntriesLoading = false;
			document.getElementById("feedLoadingIndicator").style.display = "none";
			switch(parseInt(this.getText("/response/error")))
			{
				default:
					PM.showMessage(s_unknownError + " (listScroll)", "center", "bottom");
			}
		}
		PM.addRequest(request, s_loadingList);
		request.send("group=" + this.selectedGroup + "&feed=" + this.selectedFeed + "&unread=" + (this.unreadOnly?1:0) + "&starred=" + (this.starredOnly?1:0) + "&keyword=" + this.searchKeyword + "&loaded=" + this.entriesShown);			
	}
}

TTReader.prototype.setListPosition = function(setTop)
{
	var list = document.getElementById("floatingList");
	if(setTop == true) {
		list.style.top = "0px";
		this.setListPosition();
	}
	else {
		if(this.floatingListOffset == 0)
			this.floatingListOffset = list.offsetTop;
		if(STD.getScrollTop() > this.floatingListOffset - 6)
			list.style.top = (STD.getScrollTop() - this.floatingListOffset) + 6 + "px";
		else
			list.style.top = "0px";
	}
}

TTReader.prototype.openEntryInNewWindow = function()
{
	window.open(document.getElementById("entryPermalink").href);
}

TTReader.prototype.updateAllFeeds = function()
{
	var frame = document.getElementById("hiddenFrame");
	frame.src = this.blogURL + "/owner/reader/update/?__T__=" + (new Date().getTime());
}

TTReader.prototype.importOPMLUpload = function()
{
	document.forms[0].submit();
}

TTReader.prototype.importOPMLURL = function()
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/opml/import/url/");
	request.onSuccess = function () {
		PM.removeRequest(this);
		Reader.refreshFeedGroup();
		Reader.refreshFeedList(0);
		Reader.refreshEntryList(0, 0);
		Reader.refreshEntry(0, 0, 0);
		alert(this.getText("/response/total") + s_opmlUploadComplete);
	}
	request.onError= function () {
		PM.removeRequest(this);
		switch(parseInt(this.getText("/response/error")))
		{
			case 1:
				alert(s_conNotConnect);
				break;
			case 2:
				alert(s_xmlBroken);
				break;
			case 3:
				alert(s_opmlBroken);
				break;
			default:
				PM.showMessage(s_unknownError + " (importOPMLURL)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_loadingOPML);
	request.send("url=" + encodeURIComponent(document.getElementById("opmlRequestValue").value));
}

TTReader.prototype.exportOPML = function()
{
	window.location = this.blogURL + "/owner/reader/opml/export/";
}