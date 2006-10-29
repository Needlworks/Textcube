<?php
/* Recent Replies plugin for Tattertools 1.1
   ----------------------------------
   Version 1.0
   Tatter and Friends development team.

   Creator          : Peris
   Maintainer       : Peris, inureyes, graphittie

   Created at       : 2006.7.25
   Last modified at : 2006.10.10

 This plugin shows recent eeplies on 'quilt'.
 For the detail, visit http://forum.tattertools.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/

// lib/model/comment.php : 367 line
function _getRecentComments($owner) {
	global $skinSetting, $database;
	$comments = array();
	$sql = doesHaveOwnership() ? "SELECT * FROM {$database['prefix']}Comments WHERE owner = $owner AND entry>0 AND isFiltered = 0 ORDER BY written DESC LIMIT {$skinSetting['commentsOnRecent']}" : "SELECT r.* FROM {$database['prefix']}Comments r, {$database['prefix']}Entries e WHERE r.owner = $owner AND r.owner = e.owner AND r.entry = e.id AND e.draft = 0 AND e.visibility > 0 AND entry > 0 AND isFiltered = 0 ORDER BY r.written DESC LIMIT {$skinSetting['commentsOnRecent']}";
	if ($result = mysql_query($sql)) {
		while ($comment = mysql_fetch_array($result)) {
			if (($comment['secret'] == 1) && !doesHaveOwnership()) {
				$comment['name'] = '';
				$comment['homepage'] = '';
				$comment['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
			}
			array_push($comments, $comment);
		}
	}
	return $comments;
}

// lib/view/view.php : 906 line
function _getRecentCommentsView($comments, $template) {
	requireComponent("Eolin.PHP.Core");
	requireComponent("Tattertools.Function.misc");
	global $blogURL, $skinSetting;
	ob_start();
	foreach ($comments as $comment) {
		$view = "$template";
		misc::dress('rctrp_rep_link', "$blogURL/{$comment['entry']}#comment{$comment['id']}", $view);
		misc::dress('rctrp_rep_desc', htmlspecialchars(UTF8::lessenAsEm($comment['comment'], 30)), $view);
		misc::dress('rctrp_rep_time', fireEvent('ViewRecentCommentDate', Timestamp::formatTime($comment['written'])), $view);
		misc::dress('rctrp_rep_name', htmlspecialchars(UTF8::lessenAsEm($comment['name'],10)), $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

// lib/piece/blog/end.php : 48 line
function CT_RecentRP_Default($target) {
	global $owner;

	$target .= '<ol>'.CRLF;
	$target .= _getRecentCommentsView(_getRecentComments($owner),'											<li><span class="date" style="display: block; font-family: Verdana, 돋움, Dotum, Tahoma, \'Lucida Grande\', sans-serif; font-size: 0.9em;">[##_rctrp_rep_time_##]</span> <a href="[##_rctrp_rep_link_##]">[##_rctrp_rep_desc_##]</a> <span class="name" style="color: #ABABAB;">[##_rctrp_rep_name_##]</span></li>'.CRLF);
	$target .= '										</ol>'.CRLF;

	return $target;
}
?>
