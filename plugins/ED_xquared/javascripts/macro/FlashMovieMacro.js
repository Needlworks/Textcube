/**
 * @requires macro/Base.js
 */
xq.macro.FlashMovieMacro = xq.Class(xq.macro.Base,
	/**
	 * Flash movie macro
	 * 
	 * @name xq.macro.FlashMovieMacro
	 * @lends xq.macro.FlashMovieMacro.prototype
	 * @extends xq.macro.Base
	 * @constructor
	 */
	{
	initFromHtml: function() {
		this.params.html = this.html;
	},
	initFromParams: function() {
		if(xq.macro.FlashMovieMacro.skipProviderValidation) return true;
		if(!xq.macro.FlashMovieMacro.recognize(this.params.html)) throw "Unknown src";
	},
	createHtml: function() {
		return this.params.html;
	}
});

xq.macro.FlashMovieMacro.skipProviderValidation = false;

xq.macro.FlashMovieMacro.whiteList = {
	tvpot: /http:\/\/flvs\.daum\.net\/flvPlayer\.swf\?/,
	youtube: /http:\/\/(?:www\.)?youtube\.com\/v\//,
	youtube2: /http:\/\/(?:www\.)?youtube\.com\/p\//,
	pandoratv: /http:\/\/flvr\.pandora\.tv\/flv2pan\/flvmovie\.dll/,
	pandoratv2: /http:\/\/imgcdn\.pandora\.tv\/gplayer\/pandora\_EGplayer\.swf\?/,
	mncast: /http:\/\/dory\.mncast\.com\/mncastPlayer\.swf\?/,
	yahoo: /http:\/\/d\.yimg\.com\//,
	mgoon: /http:\/\/play\.mgoon\.com\/Video\//,
	slideshare: /http:\/\/static\.slidesharecdn\.com\/swf\//,
	vimeo: /http:\/\/(?:www\.)?vimeo\.com\/moogaloop\.swf\?/,
	storyq: /http:\/\/filefarm\.storyq\.net\/SlideView\.swf\?/,
	mandki: /http:\/\/www\.mandki\.com\/mandki\/viewer\.swf\?/,
	andu: /http:\/\/andu\.hanafos\.com\/home\/play\/playgw\.asp\?/,
	plaync: /http:\/\/static\.plaync\.co\.kr\/plaza\/pcc\/view\/viewskin\_s\.swf\?movie\_id\=/,
	naver: /http:\/\/serviceapi\.nmv\.naver\.com\/flash\/NFPlayer\.swf\?vid\=/,
	migame: /http:\/\/free\.migame\.tv\/UploadComponent\/FLVPlayer\/player/,
	scribd: /http:\/\/d\.scribd\.com\/ScribdViewer\.swf\?/,
	dailymotion: /http:\/\/(?:www\.)?dailymotion\.com\/swf\//
};

xq.macro.FlashMovieMacro.recognize = function(html) {
	if(xq.macro.FlashMovieMacro.skipProviderValidation) return true;
	
	var providers = xq.macro.FlashMovieMacro.whiteList;
	
	for(var id in providers) {
		if(html.match(providers[id])) return true;
	}
	
	return false;
}