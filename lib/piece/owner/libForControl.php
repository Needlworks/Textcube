<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getUserName($userid) {
        global $database;
        return DBQuery::queryCell("SELECT name
                FROM {$database['prefix']}Users
                WHERE userid = ".$userid);
}

function removeBlog($blogid) {
	global $database;
	$tags = DBQuery::queryColumn("SELECT DISTINCT tag FROM {$database['prefix']}TagRelations WHERE blogid = $blogid");
	$feeds = DBQuery::queryColumn("SELECT DISTINCT feeds FROM {$database['prefix']}FeedGroupRelations WHERE blogid = $blogid");

	//Clear Tables
	mysql_query("DELETE FROM {$database['prefix']}Attachments WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}BlogSettings WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}BlogStatistics WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}Categories WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}Comments WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}CommentsNotified WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}CommentsNotifiedQueue WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}DailyStatistics WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}Entries WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}EntriesArchive WHERE blogid = $blogid");
//	mysql_query("DELETE FROM {$database['prefix']}FeedGroupRelations WHERE blogid = $blogid"); 
	mysql_query("DELETE FROM {$database['prefix']}FeedGroups WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}FeedReads WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}FeedStarred WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}FeedSettings WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}Filters WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}Links WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}PageCachelog WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}Plugins WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}RefererLogs WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}RefererStatistics WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}SkinSettings WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}TagRelations WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}TeamBlog WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}Trackbacks WHERE blogid = $blogid");
	mysql_query("DELETE FROM {$database['prefix']}TrackbackLogs WHERE blogid = $blogid");
	
	//Delete Tags
	if (count($tags) > 0) 
	{
		$tagliststr = implode(', ', $tags);
		$nottargets = DBQuery::queryColumn("SELECT DISTINCT tag FROM {$database['prefix']}TagRelations WHERE tag in ( $tagliststr )");
		if (count($nottargets) > 0) {
			$nottargetstr	= implode(', ', $nottargets);
			DBQuery::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr ) AND id NOT IN ( $nottargetstr )");
		} else {
			DBQuery::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr ) ");
		}
	}
	//Delete Feeds
	if (count($feeds) > 0) 
	{
		foreach($feeds as $feedId)
		{
			deleteFeed($blogid,$feedId);
		}
	}

	//Clear Plugin Database
	$query = "SELECT name, value FROM {$database['prefix']}ServiceSettings WHERE name like 'Database\_%'";
	$plugintablesraw = DBQuery::queryAll($query);
	$plugintables = array();
	foreach($plugintablesraw as $table) {
		$dbname = $database['prefix'] . substr($table['name'], 9);
		mysql_query("DELETE FROM {$database['prefix']}{$dbname} WHERE blogid = $blogid");
	}

	//Clear RSS Cache
	if (file_exists(ROOT . "/cache/rss/$blogid.xml"))
		unlink(ROOT . "/cache/rss/$blogid.xml");

	//Delete Attachments
	Path::removeFiles(Path::combine(ROOT, 'attach', $blogid));

	return true;
}

?>
