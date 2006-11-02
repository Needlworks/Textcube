<?php

function EAS_Call($type, $name, $title, $url, $content)
{
	requireComponent('Eolin.PHP.Core');
	requireComponent('Eolin.PHP.XMLRPC');
	
	global $hostURL, $blogURL;
	
	$blogstr = $hostURL . $blogURL;
	
	$rpc = new XMLRPC();
	$rpc->url = 'http://antispam.eolin.com/RPC/index.php';
	if ($rpc->call('checkSpam', $blogstr, $type, $name, $title, $url, $content, $_SERVER['REMOTE_ADDR']) == false) 
	{
		// call fail
		return true;
	}
	
	if (!is_null($rpc->fault)) {
		// EAS has some problem
		return true;
	}
	
	if ($rpc->result['result'] == true) {
		return false; // it's spam
	}
	
	return true;
}

function EAS_AddingTrackback($target, $mother)
{
	return EAS_Call(2, $mother['site'], $mother['title'], $mother['url'], $mother['excerpt']);
}

function EAS_AddingComment($target, $mother)
{
	global $owner, $user;
	if ($mother['secret'] ==  true) // it's secret(only owner can see it)
	{
		// Don't touch
		return $target;
	}
	
	$type = 1; // comment
	if ($mother['entry'] == 0) $type = 3; // guestbook
	
	return $target && EAS_Call($type, $mother['name'], '', $mother['homepage'], $mother['comment']);
}

?>