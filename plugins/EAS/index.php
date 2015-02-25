<?php
/* Frypan Anti-spam Service adapter for Textcube
   ---------------------------------------------
   Version 2.1
   Tatter Network Foundation development team / Needlworks.

   Creator          : Gendoh
   Maintainer       : inureyes

   Created at       : 2006.6.8
   Last modified at : 2015.2.25

 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function FAS_Call($type, $name, $title, $url, $content)
{
	$context = Model_Context::getInstance();
	$pool = DBModel::getInstance();

	$blogstr = $context->getProperty('uri.host').$context->getProperty('uri.blog');

	$DDosTimeWindowSize = 300;

	$rpc = new XMLRPC();
	$rpc->url = 'http://antispam.textcube.org/RPC/';
	if ($rpc->call('checkSpam', $blogstr, $type, $name, $title, $url, $content, $_SERVER['REMOTE_ADDR']) == false)
	{
		// call fail
		// Do Local spam check with "Thief-cat algorithm"
		$count = 0;

		if ($type == 2) // Trackback Case
		{
			$storage = "RemoteResponses";
			$pool->reset($storage);

			$pool->setQualifier("url","eq",$url,true);
			$pool->setQualifier("isfiltered",">",0);

			if ($cnt = $pool->getCount("id")) {
				$count += $cnt;
			}

		} else { // Comment Case
			$storage = "Comments";
			$pool->reset($storage);
			$pool->setQualifier("comment","eq",$$content,true);
			$pool->setQualifier("name","eq",$name,true);
			$pool->setQualifier("homepage","eq",$url,true);
			$pool->setQualifier("isfiltered",">",0);

			if ($cnt = $pool->getCount("id")) {
				$count += $cnt;
			}
		}

		// Check IP
		$pool->reset($storage);
		$pool->setQualifier("ip","eq",$_SERVER['REMOTE_ADDR'],true);
		$pool->setQualifier("written",">",Timestamp::getUNIXtime()-$DDosTimeWindowSize);

		if ($cnt = $pool->getCount("id")) {
			$count += $cnt;
		}

		if ($count >= 10) {
			return false;
		}

		return true;
	}

	if (!is_null($rpc->fault)) {
		// FAS has some problem
		return true;
	}

	if ($rpc->result['result'] == true) {
		return false; // it's spam
	}

	return true;
}

function FAS_AddingTrackback($target, $mother)
{
	return $target && FAS_Call(2, $mother['site'], $mother['title'], $mother['url'], $mother['excerpt']);
}

function FAS_AddingComment($target, $mother)
{
	if ($mother['secret'] ==  true) // it's secret(only owner can see it)
	{
		// Don't touch
		return $target;
	}

	$type = 1; // comment
	if ($mother['entry'] == 0) $type = 3; // guestbook

	return $target && FAS_Call($type, $mother['name'], '', $mother['homepage'], $mother['comment']);
}

?>
