<?php
/* Recent Replies plugin for Textcube 1.6
   ----------------------------------
   Version 1.6
   Tatter and Friends development team.

   Creator          : Peris
   Maintainer       : Peris, inureyes, graphittie
   Editor			: J.Parker

   Created at       : 2006.7.25
   Last modified at : 2008.2.25

 This plugin shows recent eeplies on 'quilt'.
 For the detail, visit http://forum.tattersite.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/

// library/model/comment.php : 367 line
function _getRecentComments($blogid) {
	global $skinSetting, $database, $configVal, $pluginURL;
	$data = fetchConfigVal($configVal);
	$comments = array();
	$repliesChk = ($data['repliesChk'] == 1)?"":" AND replier is NULL ";
	$limitLine = ($data['repliesList'])?$data['repliesList']:$skinSetting['commentsOnRecent'];
	$sql = "SELECT * FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry>0 AND isFiltered = 0 {$repliesChk} ORDER BY written DESC LIMIT {$limitLine}";
	if ($result = POD::query($sql)) {
		while ($comment = POD::fetch($result)) {
			if ($data['repliesChk'] == 2) {
				$row = POD::queryCell("select count(*) from {$database['prefix']}Comments where blogid = $blogid AND parent = ".$comment['id']);
				$comment['replier'] = ($row)?"<img src=\"{$pluginURL}/replier.gif\" width=\"11\" height=\"9\" align=\"top\" style=\"margin-left:2px;\" alt=\"\" />":"";
			}else{$comment['replier'] = "";}
			$comment['secret'] = ($comment['secret'] == 1)?"<img src=\"{$pluginURL}/secret.gif\" width=\"9\" height=\"11\" style=\"margin-left:2px;\" alt=\"\" />":"";
			array_push($comments, $comment);
		}
	}
	return $comments;
}

// library/view/view.php : 906 line
function _getRecentCommentsView($comments, $template) {
	requireComponent("Eolin.PHP.Core");
	requireComponent("Textcube.Function.misc");
	global $blogURL, $skinSetting, $contentContainer;
	ob_start();
	foreach ($comments as $comment) {
		$view = "$template";
		misc::dress('rctrp_rep_link', "$blogURL/{$comment['entry']}#comment{$comment['id']}", $view);
		
		$contentContainer["recent_comment_{$comment['id']}"] = htmlspecialchars(UTF8::lessenAsEm(strip_tags($comment['comment']), 30));
		misc::dress('rctrp_rep_desc', setTempTag("recent_comment_{$comment['id']}"), $view);
		misc::dress('rctrp_rep_desc', htmlspecialchars(UTF8::lessenAsEm(strip_tags($comment['comment']), 30)), $view);
		misc::dress('rctrp_rep_time', fireEvent('ViewRecentCommentDate', Timestamp::format3($comment['written'])), $view);
		misc::dress('rctrp_rep_name', htmlspecialchars(UTF8::lessenAsEm(strip_tags($comment['name']),10)).$comment['secret'].$comment['replier'], $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

// library/piece/blog/end.php : 48 line
function CT_RecentRP_Default($target) {
	global $blogid;

	$target .= '<ol>'.CRLF;
	$target .= _getRecentCommentsView(_getRecentComments($blogid),'											<li><span class="date" style="display: block; font-family: Verdana, 돋움, Dotum, Tahoma, \'Lucida Grande\', sans-serif; font-size: 0.9em;">[##_rctrp_rep_time_##]</span> <a href="[##_rctrp_rep_link_##]">[##_rctrp_rep_desc_##]</a> <span class="name" style="color: #ABABAB;">[##_rctrp_rep_name_##]</span></li>'.CRLF);
	$target .= '										</ol>'.CRLF;

	return revertTempTags($target);
}

function CT_RecentRP_Default_DataSet($DATA){
	requireComponent('Textcube.Function.Setting');
	$cfg = setting::fetchConfigVal($DATA);
	return true;
}
?>
