<?php
/* Syndicate to Eolin for Textcube 1.8
----------------------------------
Version 1.8
By Jeongkyu Shin (inureyes at gmail dot com), Needlworks / TNF

Created at       : 2009.10.09
Last modified at : 2009.10.09

General Public License
http://www.gnu.org/licenses/gpl.html

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
function SyndicateToEolin_Create ($target, $mother) {
	SyndicateToEolin($target, $mother, 'create');
	return $target;
}

function SyndicateToEolin_Modify ($target, $mother) {
	SyndicateToEolin($target, $mother, 'modify');
	return $target;
}

function SyndicateToEolin_Delete ($target, $mother) {
	SyndicateToEolin($target, $mother, 'delete');
	return $target;
}
/**
 * @brief Syndicating routine.
 * @see Tag, User, DBModel, Model_Context
 */	
function SyndicateToEolin ($entryId, $entry, $mode) {
	$context = Model_Context::getInstance();
	$blogid = $context->getProperty('blog.id');
	
	$rpc = new XMLRPC();
	$rpc->url = 'http://ping.eolin.com/';

	$summary = array('blogURL' => $context->getProperty('uri.default'), 'syncURL' => $context->getProperty('uri.default')."/plugin/abstractToEolin?entryId=$entryId");
	if($mode == 'create') {
		$summary['blogTitle'] = $context->getProperty('blog.title');
		$summary['language']  = $context->getProperty('blog.language');
		$summary['permalink'] = $context->getProperty('uri.default')."/".($context->getProperty('blog.useSloganOnPost') ? "entry/{$entry['slogan']}": $entry['id']);
		$summary['title']     = UTF8::lessenAsByte($entry['title'],255);
		$summary['content']   = UTF8::lessenAsByte(stripHTML(getEntryContentView($blogid, $entry['id'], $entry['content'], $entry['contentformatter'])), 1023, '');
		$summary['author'] = User::authorName($entry['userid'],$entryId);
		$summary['tags'] = Tag::getTagsWithEntryId($blogid, $entry);
		$summary['location'] = $entry['location'];
		$summary['written'] = Timestamp::getRFC1123($entry['published']);
	}
	return $rpc->call("sync.$mode", $summary);
}
/**
 * @brief Send abstract about specific entry.
 * @see Tag, User, DBModel, Model_Context
 */	
function sendAbstractToEolin () {
	// TODO : Rewrite routines to fit Textcube 1.8 or later.
	requireModel('blog.category');
	$entryId = $_GET['entryId'];
	$context = Model_Context::getInstance();
	$blogid = $context->getProperty('blog.id');
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<response>\r\n";

	list($allComments, $allTrackbacks) = POD::queryRow("SELECT 
			SUM(comments), SUM(trackbacks) 
			FROM {$context->getProperty('database.prefix')}Entries 
			WHERE blogid = ".$blogid." AND draft = 0 AND visibility = 3", 'num');
	if($entry = POD::queryRow("SELECT e.*, c.name AS categoryName 
				FROM {$context->getProperty('database.prefix')}Entries e 
				LEFT JOIN {$context->getProperty('database.prefix')}Categories c ON e.blogid = c.blogid AND e.category = c.id 
				WHERE e.blogid = ".$blogid." AND e.id = ".$entryId." AND e.draft = 0 AND e.visibility = 3".getPrivateCategoryExclusionQuery($blogid))) {
					
		header('Content-Type: text/xml; charset=utf-8');
		echo '<version>1.1</version>', "\r\n";
		echo '<status>1</status>', "\r\n";
		echo '<blog>', "\r\n";
		echo '<generator>'.TEXTCUBE_NAME.'/'.TEXTCUBE_VERSION.'</generator>', "\r\n";
		echo '<language>', htmlspecialchars($context->getProperty('blog.language')), '</language>', "\r\n";
		echo '<url>', htmlspecialchars($context->getProperty('uri.default')), '</url>', "\r\n";
		echo '<title>', htmlspecialchars($context->getProperty('blog.title')), '</title>', "\r\n";
		echo '<description>', htmlspecialchars($context->getProperty('blog.description')), '</description>', "\r\n";
		echo '<comments>', $allComments, '</comments>', "\r\n";
		echo '<trackbacks>', $allTrackbacks, '</trackbacks>', "\r\n";
		echo '</blog>', "\r\n";
		echo '<entry>', "\r\n";
		echo '<permalink>', htmlspecialchars($context->getProperty('uri.default')."/".($context->getProperty('blog.useSloganOnPost') ? "entry/{$entry['slogan']}": $entry['id'])), '</permalink>', "\r\n";
		echo '<title>', htmlspecialchars($entry['title']), '</title>', "\r\n";
		echo '<content>', htmlspecialchars(getEntryContentView($blogid, $entryId, $entry['content'], $entry['contentformatter'])), '</content>', "\r\n";
		echo '<author>', htmlspecialchars(User::authorName($entry['userid'],$entryId)), '</author>', "\r\n";
		echo '<category>', htmlspecialchars($entry['categoryName']), '</category>', "\r\n";
		$tags = Tag::getTagsWithEntryId($blogid, $entry);
		foreach($tags as $tag) {
			echo '<tag>', htmlspecialchars($tag), '</tag>', "\r\n";
		}
		echo '<location>', htmlspecialchars($entry['location']), '</location>', "\r\n";
		echo '<comments>', $entry['comments'], '</comments>', "\r\n";
		echo '<trackbacks>', $entry['trackbacks'], '</trackbacks>', "\r\n";
		echo '<written>', Timestamp::getRFC1123($entry['published']), '</written>', "\r\n";
		foreach(getAttachments($blogid, $entry['id']) as $attachment) {
			echo '<attachment>', "\r\n";
			echo '<mimeType>', $attachment['mime'], '</mimeType>', "\r\n";
			echo '<filename>', $attachment['label'], '</filename>', "\r\n";
			echo '<length>', $attachment['size'], '</length>', "\r\n";
			echo '<url>', $defaultURL, '/attachment/', $attachment['name'], '</url>', "\r\n";
			echo '</attachment>', "\r\n";
		}
		echo '</entry>', "\r\n";
	} else {
 		echo '<version>1.1</version>', "\r\n", '<status>0</status>', "\r\n";
	}
	echo "</response>";	
}
?>