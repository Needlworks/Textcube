<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class TData {
	/*@static@*/
	function removeAll($removeAttachments = true) {
		global $database;
		$blogid = getBlogId();
		Data_IAdapter::query("UPDATE {$database['prefix']}BlogStatistics SET visits = 0 WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}DailyStatistics WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}Categories WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}Attachments WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}Comments WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}RemoteResponseLogs WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}TagRelations WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}Entries WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}Links WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}Filters WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}RefererLogs WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}RefererStatistics WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}Plugins WHERE blogid = $blogid");
		
		Data_IAdapter::query("DELETE FROM {$database['prefix']}FeedStarred WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}FeedReads WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}FeedGroupRelations WHERE blogid = $blogid");
		Data_IAdapter::query("DELETE FROM {$database['prefix']}FeedGroups WHERE blogid = $blogid");
		
		if (file_exists(ROOT . "/cache/rss/$blogid.xml"))
			unlink(ROOT . "/cache/rss/$blogid.xml");
		
		if ($removeAttachments)
			Path::removeFiles(Path::combine(ROOT, 'attach', $blogid));
	}
}
?>
