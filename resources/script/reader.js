/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

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
	
	// message
	this.feedUpdating = '피드 업데이트 중';
	this.feedFailure = '잘못된 피드';
	this.feedUpdate = '피드 업데이트';
}

TTReader.prototype.setShownEntries = function(count)
{
	this.entriesShown = count;
	getObject("entriesShown").innerHTML = count;
}

TTReader.prototype.setTotalEntries = function(count)
{
	this.entriesTotal = count;
	getObject("entriesTotal").innerHTML = count;
}

TTReader.prototype.setBlogTitle = function(title)
{
	getObject("blogTitle").innerHTML = title;
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
		getObject("pannel").style.display = "block";
		getObject("toggleButton").className = "pannel-show";
		getObject("toggleButton").innerHTML = '<span class="text">' + hide_str + '<\/span>';
		setUserSetting("readerPannelVisibility", 1);
	}
	else
	{
		getObject("pannel").style.display = "none";
		getObject("toggleButton").className = "pannel-hide";
		getObject("toggleButton").innerHTML = '<span class="text">' + show_str + '<\/span>';
		setUserSetting("readerPannelVisibility", 0);
	}
	getObject("floatingList").style.top = "0px";
	this.floatingListOffset = getObject("floatingList").offsetTop;
	this.isPannelCollapsed = !this.isPannelCollapsed;
}

TTReader.prototype.toggleConfigure = function()
{
	if(getObject("groupsAndFeeds").style.display == "none") {
		getObject("groupsAndFeeds").style.display = "block";
		getObject("configure").style.display = "none";
		getObject("settingLabel").innerHTML = '<span class="text">' + configureLabel + '<\/span>';
		getObject("settingLabel").parentNode.className = 'configureText';
	}
	else {
		if(this.isPannelCollapsed)
			this.togglePannel();
		getObject("groupsAndFeeds").style.display = "none";
		getObject("configure").style.display = "block";
		getObject("settingLabel").innerHTML = '<span class="text">' + pannelLabel + '<\/span>';
		getObject("settingLabel").parentNode.className = 'feedListText';
	}
	getObject("floatingList").style.top = "0px";
	this.floatingListOffset = getObject("floatingList").offsetTop;
}

TTReader.prototype.startResizing = function(event)
{
	event = STD.event(event);
	if(event.target.tagName == "DIV" && !Reader.isPannelCollapsed) {
		STD.addEventListener(document);
		document.addEventListener("selectstart", Reader.returnFalse, false);
		document.addEventListener("mousemove", Reader.doResizing, false);
		Reader.resizeStart = event.clientY;
		Reader.resizeScroll = STD.getScrollTop();
		Reader.resizeHeight = parseInt(getObject("groupBox").style.height);
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
		setUserSetting("readerPannelHeight", parseInt(getObject("groupBox").style.height));
		Reader.resizeStart = 0;
		getObject("floatingList").style.top = "0px";
		this.floatingListOffset = getObject("floatingList").offsetTop;
	}
}

TTReader.prototype.doResizing = function(event)
{
	event = STD.event(event);
	var height = Reader.resizeHeight + (event.clientY - Reader.resizeStart) + (STD.getScrollTop() - Reader.resizeScroll);
	if(height >= 100) {
		getObject("groupBox").style.height = height + "px";
		getObject("feedBox").style.height = height + "px";
	}
}

TTReader.prototype.selectGroup = function(caller, id)
{
	this.refreshFeedList(id);
	this.refreshEntryList(id, 0);
	this.refreshEntry(id, 0, 0);
	this.selectedGroup = id;
	getObject("groupList0").className = getObject("groupList0").className.replace(' active-class', ' inactive-class');
	if(this.selectedGroupObject != null)
		this.selectedGroupObject.className = this.selectedGroupObject.className.replace(' active-class', ' inactive-class');
	this.selectedGroupObject = caller.parentNode.parentNode;
	this.selectedGroupObject.className = this.selectedGroupObject.className.replace(' rollover-class', ' active-class');
	extraClass = this.selectedGroupObject.className;
}

TTReader.prototype.selectFeed = function(caller, id)
{
	this.refreshEntryList(this.selectedGroup, id);
	this.refreshEntry(this.selectedGroup, id, 0);
	this.selectedFeed = id;
	if(this.selectedFeedObject != null)
		this.selectedFeedObject.className = this.selectedFeedObject.className.replace(' active-class', ' inactive-class');		
	this.selectedFeedObject = caller;
	this.selectedFeedObject.className = this.selectedFeedObject.className.replace(' rollover-class', ' active-class');
	extraClass = this.selectedFeedObject.className;
}

TTReader.prototype.selectEntry = function(id)
{
	this.refreshEntry(this.selectedGroup, this.selectedFeed, id);
	tempClass = document.getElementById("entrytitleList" + this.selectedEntry).className;
	document.getElementById("entrytitleList" + this.selectedEntry).className = tempClass.replace(/active-class/, "inactive-class");
	this.selectedEntry = id;
	this.selectEntryObject(id);
}

TTReader.prototype.selectEntryObject = function(id)
{
	var caller = getObject("entrytitleList" + id);
	if(caller) {
		this.selectedEntryObject = caller;
		this.selectedEntryObject.className = "read active-class";
		var list = getObject("listup");
		if(this.floatingListOffset == 0)
			this.setListPosition();
		this.startScroll("listup", getOffsetTop(caller) - getOffsetTop(list) - (list.offsetHeight / 4));
	}
	else
		setTimeout("Reader.selectEntryObject(" + id + ")", 100);
}

TTReader.prototype.startScroll = function(id, offset)
{
	var obj = getObject(id);
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
		getObject("groupBox").innerHTML = this.getText("/response/view");
	}
	request.onError= function () {
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showErrorMessage(s_unknownError + " (refreshFeedGroup)", "center", "bottom");
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
		getObject("feedBox").innerHTML = this.getText("/response/view");
	}
	request.onError= function () {
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showErrorMessage(s_unknownError + " (refreshFeedList)", "center", "bottom");
		}
	}
	request.send("group=" + group + "&starred=" + (this.starredOnly?1:0) + "&keyword=" + this.searchKeyword);
}

TTReader.prototype.refreshEntryList = function(group, feed)
{	
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/view/entries/");
	request.onSuccess = function () {
		getObject("listup").innerHTML = this.getText("/response/view");
		Reader.selectedEntry = this.getText("/response/firstEntryId");
		try {
			Reader.selectedEntryObject = getObject("entrytitleList" + Reader.selectedEntry).parentNode;
		} catch(e) {
			Reader.selectedEntryObject = null;
		}
		Reader.setShownEntries(parseInt(this.getText("/response/entriesShown")));
		Reader.setTotalEntries(parseInt(this.getText("/response/entriesTotal")));
	}
	request.onError= function () {
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showErrorMessage(s_unknownError + " (refreshEntryList)", "center", "bottom");
		}
	}
	request.send("group=" + (group == undefined ? 0  : group) + "&feed=" + ( feed == undefined ? 0 : feed) + "&unread=" + (this.unreadOnly?1:0) + "&starred=" + (this.starredOnly?1:0) + "&keyword=" + this.searchKeyword);
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
		var nodes = searchChildNodes(getObject("entryBody"), "img");
		for(var i=0; i<nodes.length; i++) {
			if(nodes[i].getAttribute("trying") != "trying") {
				nodes[i].setAttribute("trying", "trying");
				STD.addEventListener(nodes[i]);
				nodes[i].addEventListener("error", Reader.imageLoadingFailed, false);
			}
		}
	}
	if(Reader.optionForceNewWindow) {
		var nodes = searchChildNodes(getObject("entryBody"), "a");
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
		getObject("blogTitle").innerHTML = this.getText("/response/blog");
		getObject("entry").innerHTML = this.getText("/response/view");
		try {
			getObject("entrytitleList" + Reader.selectedEntry).className = "read active-class";
		} catch(e) { }
		
		Reader.setListPosition(true);
		Reader.doPostProcessingOnEntry();
		window.scrollTo(0, getOffsetTop(getObject("scrollPoint")));
	}
	request.onError= function () {
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showErrorMessage(s_unknownError + " (refreshEntry)", "center", "bottom");
		}
	}
	request.send("group=" + (group == undefined ? 0 : group) + "&feed=" + ( feed == undefined ? 0 : feed) + "&entry=" + entry + "&unread=" + (this.unreadOnly?1:0) + "&starred=" + (this.starredOnly?1:0) + "&keyword=" + this.searchKeyword);
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
			PM.showErrorMessage(s_notFoundPrevPost, "center", "bottom");
		}
	}
	request.onError= function () {
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showErrorMessage(s_unknownError + " (prevEntry)", "center", "bottom");
		}
	}
	request.send("group=" + this.selectedGroup + "&feed=" + this.selectedFeed + "&entry=" + this.selectedEntry + "&unread=" + (this.unreadOnly?1:0) + "&starred=" + (this.starredOnly?1:0) + "&keyword=" + this.searchKeyword);
}

TTReader.prototype.nextEntry = function()
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/view/entry/previous/");
	request.onSuccess = function () {
		if(this.getText("/response/id") != 0) {
			if(getObject("entrytitleList" + this.getText("/response/id")) == null)
				Reader.listScroll(true);
			Reader.selectedEntry = this.getText("/response/id");
			Reader.selectEntry(Reader.selectedEntry);
		}
		else {
			PM.showErrorMessage(s_notFoundNextPost, "center", "bottom");
		}
	}
	request.onError= function () {
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showErrorMessage(s_unknownError + " (nextEntry)", "center", "bottom");
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
		getObject("groupBox").innerHTML = this.getText("/response/view");
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
				PM.showErrorMessage(s_unknownError + " (addGroup)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_addingGroup);
	request.send("title=" + encodeURIComponent(title) + "&current=" + this.selectedGroup);
}

TTReader.prototype.editGroup = function(id, title)
{
	this.editingGroupId = id;

	getObject("groupList").style.display = "none";
	getObject("groupAdder").style.display = "none";
	getObject("groupEditor").style.display = "block";
	getObject("changeGroupTitle").value = title;
	getObject("changeGroupTitle").select();
}

TTReader.prototype.cancelEditGroup = function()
{
	getObject("groupList").style.display = "";
	getObject("groupAdder").style.display = "";
	getObject("groupEditor").style.display = "none";
}

TTReader.prototype.editGroupExecute = function()
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/group/edit/");
	request.onSuccess = function () {
		Reader.selectedGroup = 0;
		Reader.selectedGroupObject = null;
		getObject("groupBox").innerHTML = this.getText("/response/view");
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
				PM.showErrorMessage(s_unknownError + " (editGroupExecute)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_editingGroup);
	request.send("id=" + this.editingGroupId + "&title=" + encodeURIComponent(getObject("changeGroupTitle").value) + "&current=" + this.selectedGroup);
}

TTReader.prototype.deleteGroup = function()
{
	if(!confirm(s_confirmDelete))
		return;
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/group/delete/");
	request.onSuccess = function () {
		Reader.selectedGroup = 0;
		Reader.selectedGroupObject = null;
		getObject("groupBox").innerHTML = this.getText("/response/view");
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
				PM.showErrorMessage(s_unknownError + " (deleteGroup)", "center", "bottom");
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
		getObject("feedBox").innerHTML = this.getText("/response/view");
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
				PM.showErrorMessage(s_unknownError + " (addFeed)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_requestFeed);
	request.send("group=" + this.selectedGroup + "&url=" + encodeURIComponent(url));
}

TTReader.prototype.editFeed = function(id, url)
{
	this.editingFeedId = id;

	getObject("feedList").style.display = "none";
	getObject("feedAdder").style.display = "none";
	getObject("feedEditor").style.display = "block";
	getObject("changeFeedGroup").value = this.selectedGroup;
	getObject("changeFeedURL").value = url;
	getObject("changeFeedURL").select();	
}

TTReader.prototype.cancelEditFeed = function()
{
	getObject("feedList").style.display = "";
	getObject("feedAdder").style.display = "";
	getObject("feedEditor").style.display = "none";
}

TTReader.prototype.editFeedExecute = function()
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/feed/edit/");
	request.onSuccess = function () {
		Reader.selectedFeed = 0;
		Reader.selectedFeedObject = null;
		getObject("feedBox").innerHTML = this.getText("/response/view");
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
				PM.showErrorMessage(s_unknownError + " (editFeedExecute)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_editingFeed);
	request.send("id=" + this.editingFeedId + "&old_group=" + this.selectedGroup + "&new_group=" + getObject("changeFeedGroup").value + "&url=" + encodeURIComponent(getObject("changeFeedURL").value));
}

TTReader.prototype.deleteFeed = function()
{
	if(!confirm(s_confirmDelete))
		return;
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/feed/delete/");
	request.onSuccess = function () {
		Reader.selectedFeed = 0;
		Reader.selectedFeedObject = null;
		getObject("feedBox").innerHTML = this.getText("/response/view");
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
				PM.showErrorMessage(s_unknownError + " (deleteFeed)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_removingFeed);
	request.send("id=" + this.editingFeedId + "&group=" + this.selectedGroup);
}

TTReader.prototype.saveSetting = function()
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/config/save/");
	request.onSuccess = function () {
		getObject("groupsAndFeeds").style.display = "block";
		getObject("configure").style.display = "none";
		getObject("settingLabel").innerHTML = '<span class="text">' + configureLabel + '<\/span>';
		PM.showMessage(s_saved, "center", "bottom");
	}
	request.onError= function () {
		PM.removeRequest(this);
		switch(parseInt(this.getText("/response/error")))
		{
			default:
				PM.showErrorMessage(s_unknownError + " (saveSetting)", "center", "bottom");
		}
	}
	var f = document.getElementById('reader-section');
	var updateCycle = f.updateCycle ? f.updateCycle.value : "";
	var feedLife = f.feedLife ? f.feedLife.value : "";
	var loadImage = f.loadImage ? (document.getElementById('loadImage1').checked ? 1 : 2) : "";
	var allowScript = f.allowScript ? (f.allowScript[0].checked ? 1 : 2) : "";
	var newWindow = f.newWindow ? (f.newWindow[0].checked ? 1 : 2) : "";
	request.send("updateCycle=" + updateCycle + "&feedLife=" + feedLife + "&loadImage=" + loadImage + "&allowScript=" + allowScript + "&newWindow=" + newWindow);
	Reader.optionForceLoadImage = (document.getElementById('loadImage1') && document.getElementById('loadImage1').checked) ? true : false;
	Reader.optionForceNewWindow = (f.newWindow[1] && f.newWindow[1].checked) ? true : false;
}

TTReader.prototype.markAsUnread = function(id)
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/mark/unread/");
	request.presetProperty(getObject("entrytitleList" + id), "className", "unread active-class");
	request.onSuccess = function () {
		PM.showMessage(s_markedAsUnread, "center", "bottom");
	}
	request.send("id=" + id);
}

TTReader.prototype.markAsReadAll = function()
{
	var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/mark/allread/");
	request.onSuccess = function () {
		Reader.showUnreadOnly();
		PM.showMessage(s_markedAsReadAll, "center", "bottom");
	}
	request.send();
}

TTReader.prototype.toggleStarred = function(id)
{
	if(typeof(id) == "undefined")
		id = this.selectedEntry;

	if(getObject("star" + id)) {
		if(getObject("star" + id).className.match('-on-')) {
			var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/mark/unstar/");
			request._ttreader = this;
			request.onSuccess = function() {
				getObject("star" + id).className = getObject("star" + id).className.replace('-on-', '-off-');
				getObject("star" + id).innerHTML = '<span class="text">' + disscrapedPostText + '<\/span>';
			}
		} else {
			var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/action/mark/star/");
			request._ttreader = this;
			request.onSuccess = function() {
				getObject("star" + id).className = getObject("star" + id).className.replace('-off-', '-on-');
				getObject("star" + id).innerHTML = '<span class="text">' + scrapedPostText + '<\/span>';
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
		getObject("starredOnlyIndicator").className = "scrap-off-icon";
	}
	else {
		this.starredOnly = true;
		getObject("starredOnlyIndicator").className = "scrap-on-icon";
	}
	this.refreshFeedGroup();
	this.refreshFeedList(0);
	this.refreshEntryList(0, 0);
	this.refreshEntry(0, 0, 0);
}

TTReader.prototype.showSearch = function()
{
	this.starredOnly = false;
	this.searchKeyword = getObject("keyword").value;
	this.refreshFeedGroup();
	this.refreshFeedList(0);
	this.refreshEntryList(0, 0);
	this.refreshEntry(0, 0, 0);
}

TTReader.prototype.listScroll = function(force)
{
	var caller = getObject("listup");
	if((caller.offsetHeight + caller.scrollTop > caller.scrollHeight - 100 || force == 1) && this.entriesShown < this.entriesTotal && !this.areEntriesLoading) {
		this.areEntriesLoading = true;
		getObject("feedLoadingIndicator").style.display = "block";
		var request = new HTTPRequest("POST", this.blogURL + "/owner/reader/view/entries/more/");
		request.onSuccess = function () {
			PM.removeRequest(this);
			Reader.areEntriesLoading = false;
			Reader.setShownEntries(Reader.entriesShown + parseInt(this.getText("/response/count")));
			getObject("feedLoadingIndicator").style.display = "none";
			var div = document.createElement("div");
			div.innerHTML = this.getText("/response/view");
			getObject("additionalFeedContainer").appendChild(div);
			if(Reader.entriesShown == Reader.entriesTotal)			
				getObject("iconMoreEntries").style.display = "none";
		}
		request.onError= function () {
			PM.removeRequest(this);
			Reader.areEntriesLoading = false;
			getObject("feedLoadingIndicator").style.display = "none";
			switch(parseInt(this.getText("/response/error")))
			{
				default:
					PM.showErrorMessage(s_unknownError + " (listScroll)", "center", "bottom");
			}
		}
		PM.addRequest(request, s_loadingList);
		request.send("group=" + this.selectedGroup + "&feed=" + this.selectedFeed + "&unread=" + (this.unreadOnly?1:0) + "&starred=" + (this.starredOnly?1:0) + "&keyword=" + this.searchKeyword + "&loaded=" + this.entriesShown);			
	}
}

TTReader.prototype.setListPosition = function(setTop)
{
	var list = getObject("floatingList");
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
	window.open(getObject("entryPermalink").href);
}

TTReader.prototype.updateFeed = function(id, message)
{
	getObject("iconFeedStatus" + id).className = "updating-button button";
	getObject("iconFeedStatus" + id).innerHTML = '<span>' + this.feedUpdating + '<\/span>';
	var request = new HTTPRequest("GET", this.blogURL + "/owner/reader/update/" + id);
	request.onSuccess = function () {
		getObject("iconFeedStatus" + id).className = "update-button button";
		getObject("iconFeedStatus" + id).innerHTML = '<span>' + this.feedUpdate + '<\/span>';
		Reader.refreshFeedList(Reader.selectedGroup);
		Reader.refreshEntryList(Reader.selectedGroup, Reader.selectedFeed);
		PM.showMessage(message, "center", "bottom");
	}
	request.onError= function () {
		getObject("iconFeedStatus" + id).className = "failure-button button";
		getObject("iconFeedStatus" + id).innerHTML = '<span>' + this.feedFailure + '<\/span>';
		switch(parseInt(this.getText("/response/error")))
		{
			case 1:
				alert(s_xmlBroken);
				break;
			case 2:
				alert(s_conNotConnect);
				break;
			case 3:
				alert(s_feedBroken);
				break;
			default:
				PM.showErrorMessage(s_unknownError + " (updateFeed)", "center", "bottom");
		}
	}
	request.send();
}

TTReader.prototype.updateAllFeeds = function()
{
	var frame = getObject("hiddenFrame");
	frame.src = this.blogURL + "/owner/reader/update/?__T__=" + (new Date().getTime());
}

TTReader.prototype.importOPMLUpload = function()
{
	document.getElementById("opml-section").submit();
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
		if(this.getText("/response/total") == 1) {
			alert(this.getText("/response/total") + s_opmlUploadCompleteSingle);
		} else {
			alert(this.getText("/response/total") + s_opmlUploadCompleteMulti);
		}
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
				PM.showErrorMessage(s_unknownError + " (importOPMLURL)", "center", "bottom");
		}
	}
	PM.addRequest(request, s_loadingOPML);
	request.send("url=" + encodeURIComponent(getObject("opmlRequestValue").value));
}

TTReader.prototype.exportOPML = function()
{
	window.location = this.blogURL + "/owner/reader/opml/export/";
}
