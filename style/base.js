/* Initialize / Finalize *****************************************************/
var tt_base	= new Object();
var tt_init_funcs = new Array();
var tt_finish_funcs = new Array();

tt_base =
{
	init: function()
	{
		for (var i = 0; i < tt_init_funcs.length; i++) tt_init_funcs[i]();
	},
	
	finish: function()
	{
		for (var i = 0; i < tt_finish_funcs.length; i++) tt_finish_funcs[i]();
	}
};

window.onload = tt_base.init;
window.unload = tt_base.finish;

/* Browser Detector **********************************************************/
tt_base.browserDetector =
{
	browser : null,
	version	: null,
	os		: null,
	agent	: navigator.userAgent.toLowerCase(),
	place	: null,
	str		: null,
	css		: false,

	detect: function()
	{
		if (this.checkIt('konqueror')) {
			this.browser = 'Konqueror';
			this.os = 'Linux';
		} else if (this.checkIt('safari')) {
			this.browser = 'Safari';
		} else if (this.checkIt('omniweb')) {
			this.browser = 'OmniWeb';
		} else if (this.checkIt('opera')) {
			this.browser = 'Opera';
		} else if (this.checkIt('webtv')) {
			this.browser = 'WebTV';
		} else if (this.checkIt('icab')) {
			this.browser = 'iCab';
		} else if (this.checkIt('msie')) {
			this.browser = 'Internet Explorer';
		} else if (this.checkIt('firefox')) {
			this.browser = 'Firefox';
		} else if (this.checkIt('netscape')) {
			this.browser = 'Netscape';
		} else if (!this.checkIt('compatible')) {
			var rev_offset = this.agent.indexOf('rv:') + 1;

			this.browser = 'Mozilla';
			this.version = this.agent.substring(rev_offset, rev_offset + 3);
		} else {
			this.browser = 'Unknown';
		}

		if (!this.version) {
			this.version = this.agent.charAt(this.place + this.str.length);
		}

		if (!this.os) {
			if (this.checkIt('linux')) this.os = "Linux";
			else if (this.checkIt('x11')) this.os = "Unix";
			else if (this.checkIt('mac')) this.os = "Mac";
			else if (this.checkIt('win')) this.os = "Windows";
			else this.os = "Unknown";
		}

		this.css = this.checkCSS();
	},

	checkCSS: function()
	{
		var wrp	= document.getElementById('temp-wrap');
		var position = 'static';

		if (document.defaultView && document.defaultView.getComputedStyle) {
			position = document.defaultView.getComputedStyle(wrp, null).getPropertyValue('position');
		} else if (wrp.currentStyle) {
			position = wrp.currentStyle.position;
		}

		return ('absolute' == position)
	},

	checkIt: function(string)
	{
		this.place = this.agent.indexOf(string) + 1;
		this.str = string;

		return this.place;
	}
};