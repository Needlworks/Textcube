<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class DataMaintenance {
	/*@static@*/
	function removeAll($removeAttachments = true) {
		global $database;
		$blogid = getBlogId();	
		$tags = POD::queryColumn("SELECT DISTINCT tag FROM {$database['prefix']}TagRelations WHERE blogid = $blogid");
		
		POD::query("UPDATE {$database['prefix']}BlogStatistics SET visits = 0 WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}DailyStatistics WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}Categories WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}Attachments WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}Comments WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}CommentsNotified WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}RemoteResponseLogs WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}TagRelations WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}Entries WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}LinkCategories WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}Links WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}RefererLogs WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}RefererStatistics WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}Plugins WHERE blogid = $blogid");
		//POD::query("DELETE FROM {$database['prefix']}UserSettings WHERE user = $blogid");
		
		POD::query("DELETE FROM {$database['prefix']}Filters WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}FeedStarred WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}FeedReads WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}FeedGroupRelations WHERE blogid = $blogid");
		POD::query("DELETE FROM {$database['prefix']}FeedGroups WHERE blogid = $blogid AND id <> 0");
		
		if (count($tags) > 0) 
		{
			$tagliststr = implode(', ', $tags);
			$nottargets = POD::queryColumn("SELECT DISTINCT tag FROM {$database['prefix']}TagRelations WHERE tag in ( $tagliststr )");
			if (count($nottargets) > 0) {
				$nottargetstr	= implode(', ', $nottargets);
				POD::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr ) AND id NOT IN ( $nottargetstr )");
			} else {
				POD::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr ) ");
			}
		}
		
		if (file_exists(ROOT . "/cache/rss/$blogid.xml"))
			unlink(ROOT . "/cache/rss/$blogid.xml");
		
		if ($removeAttachments) {
			Path::removeFiles(Path::combine(ROOT, 'attach', $blogid));
			POD::query("UPDATE {$database['prefix']}BlogSettings SET logo = '' WHERE blogid = $blogid");
		}
	}
}
?>
