<?php
/* URL keeper for Textcube 2.0
----------------------------------
Version 1.1
By Needlworks / TNF

Created at       : 2006.11.23
Last modified at : 2015.07.05
 
This plugin keeps original permalink.
For the detail, visit http://forum.tattersite.com/ko

General Public License
http://www.gnu.org/licenses/gpl.html

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

function URLkeeper($target)
{
	$context = Model_Context::getInstance();
	$data = $context->getProperty('plugin.config');
	$config  = $data['viewForm'];
	$target .= '
<script type="text/javascript">
//<![CDATA[

		window.onload = function(){
		var type = navigator.appName
		var lang;
		var msg;
		var myurl = location.href;
		var config = "'.$config.'";
	
		if (type=="Netscape")
			lang = navigator.language
		else
			lang = navigator.userLanguage
		
		// 국가코드에서 앞 2글자만 자름
		var lang = lang.substr(0,2)
		// 한글인 경우
		if (lang == "ko")
			msg = " 원래 주소인 "+myurl+" 로 접속해주세요.";
		// 다른 언어인 경우
		else
			msg =  "please, visit directly via "+myurl;
		try {
			if(top != self){
				if (config == "1") {
					window.open(myurl,"_top");
				}else{
				if (confirm(msg)) window.open(myurl,"_top");
				}
			}
		} catch (e) {
		}	
		}
//]]>
</script>
'.CRLF;
	return $target;
}
?>
