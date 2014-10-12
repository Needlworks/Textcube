<?php
/* WikiCube
   ----------------------------------
   Version 0.13
   Starts at        : Apr. 5, 2006
   Last modified at : Jan. 9, 2011
   
   jeongkyu Shin.
   E-mail : inureyes@gmail.com


 For the detail, visit http://forest.nubimaru.com/entry/WikiCube

 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 13. user-custom link added. [[printWord|realLink]]
 12. category link added.
 11. tag link added.
 10. error page post added.
 09. basic functions.
*/

function WikiCube_FormatContent($target, $mother) {
	global $configVal;
	$config = Setting::fetchConfigVal($configVal);
	if(empty($config['mode'])) $config['mode'] = 'entry';

	$context = Model_Context::getInstance();
	$pattern = array(
		'/\[\[(.*?)\|(.*?)\]\]/' => '<a href="'.$context->getProperty('uri.blog').'/'.$config['mode'].'/$2'.'">$1</a>',
		'/\[\[tg:(.*?)\]\]/' => '<a href="'.$context->getProperty('uri.blog').'/tag/$1'.'">$1</a>',
		'/\[\[ct:(.*?)\]\]/' => '<a href="'.$context->getProperty('uri.blog').'/category/$1'.'">$1</a>',
		'/\[\[(.*?)\]\]/' => '<a href="'.$context->getProperty('uri.blog').'/'.$config['mode'].'/$1'.'">$1</a>'
	);
    foreach ($pattern as $original => $replaced)
        $target = preg_replace($original, $replaced, $target);

	return $target;
}

function WikiCube_FormatErrorPage($target) {
	global $configVal;
	$config = Setting::fetchConfigVal($configVal);
	if(empty($config['mode'])) $config['mode'] = 'entry';

	$context = Model_Context::getInstance();
	$additional = '<div style="border:none;width:100%;text-align:center;"><a href="'.$context->getProperty('uri.blog').
		'/owner/entry/post?slogan='.$context->getProperty('suri.value').
		($config['mode'] == 'entry' ? '' : '&category=-3').	
		'">'._text('Empty page. Click here to add a new entry.').'</a></div>';
	return $target.$additional;
}
?>
