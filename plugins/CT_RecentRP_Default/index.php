<?php
/* Recent Replies plugin for Textcube 1.10
   ---------------------------------------
   Version 1.10.3
   Tatter and Friends development team.

   Creator          : Peris
   Maintainer       : Peris, inureyes, graphittie
   Editor			: J.Parker

   Created at       : 2006.7.25
   Last modified at : 2015.2.16

 This plugin shows recent eeplies on 'quilt'.
 For the detail, visit http://forum.tattersite.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/

function RecentRP_getRecentComments($blogid) {
	global $configVal;
	$data = Setting::fetchConfigVal($configVal);
	$context = Model_Context::getInstance();
	$comments = array();
	$limitLine = ($data['repliesList'])?$data['repliesList']:$context->getProperty('skin.commentsOnRecent');

	$pool = DBModel::getInstance();
	$pool->reset('Comments');
	$pool->setQualifier('blogid','equals',intval($context->getProperty('blog.id')));
	$pool->setQualifier('isfiltered','equals',0);
	$pool->setQualifier('entry','neq',0);
	if ($data['repliesChk'] != 1) $pool->setQualifier('replier',null);
	$pool->setOrder('written','DESC');
	$pool->setLimit($limitLine);
	$result = $pool->getAll();

	foreach ($result as $comment) {
		if ($data['repliesChk'] == 2) {
			$pool->reset('Comments');
			$pool->setQualifier('blogid','equals',$context->getProperty('blog.id'));
			$pool->setQualifier('parent','equals',$comment['id']);
			$row = $pool->getCount();
			$comment['replier'] = ($row)?"<img src=\"".$context->getProperty("plugin.uri")."/replier.gif\" width=\"11\" height=\"9\" align=\"top\" style=\"margin-left:2px;\" alt=\"\" />":"";
		} else {
			$comment['replier'] = "";
		}
		$comment['secret'] = ($comment['secret'] == 1)?"<img src=\"".$context->getProperty("plugin.uri")."/secret.gif\" width=\"9\" height=\"11\" style=\"margin-left:2px;\" alt=\"\" />":"";
		array_push($comments, $comment);
	}
	return $comments;
}

function RecentRP_getRecentCommentsView($comments, $template) {
	global $contentContainer;
	$context = Model_Context::getInstance();
	ob_start();
	foreach ($comments as $comment) {
		$view = "$template";
		Misc::dress('rctrp_rep_link', $context->getProperty('uri.blog')."/{$comment['entry']}#comment{$comment['id']}", $view);
		
		$contentContainer["recent_comment_{$comment['id']}"] = htmlspecialchars(UTF8::lessenAsEm(strip_tags($comment['comment']), 30));
		Misc::dress('rctrp_rep_desc', setTempTag("recent_comment_{$comment['id']}"), $view);
		Misc::dress('rctrp_rep_desc', htmlspecialchars(UTF8::lessenAsEm(strip_tags($comment['comment']), 30)), $view);
		Misc::dress('rctrp_rep_time', fireEvent('ViewRecentCommentDate', Timestamp::format3($comment['written'])), $view);
		Misc::dress('rctrp_rep_name', htmlspecialchars(UTF8::lessenAsEm(strip_tags($comment['name']),10)).$comment['secret'].$comment['replier'], $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

// library/piece/blog/end.php : 48 line
function CT_RecentRP_Default($target) {
	$context = Model_Context::getInstance();
	$target .= '<ol>'.CRLF;
	$target .= RecentRP_getRecentCommentsView(RecentRP_getRecentComments($context->getProperty('blog.id')),'											<li><span class="date" style="display: block; font-family: Verdana, 돋움, Dotum, Tahoma, \'Lucida Grande\', sans-serif; font-size: 0.9em;">[##_rctrp_rep_time_##]</span> <a href="[##_rctrp_rep_link_##]">[##_rctrp_rep_desc_##]</a> <span class="name" style="color: #ABABAB;">[##_rctrp_rep_name_##]</span></li>'.CRLF);
	$target .= '										</ol>'.CRLF;

	return revertTempTags($target);
}

function CT_RecentRP_Default_DataSet($DATA){
	$cfg = Setting::fetchConfigVal($DATA);
	return true;
}
?>
