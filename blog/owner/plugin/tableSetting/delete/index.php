<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'REQUEST' => array(
		'name' => array('string'),
		'type' => array('int', 'min' => 1, 'max' => 2),
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if ($owner != 1) {
	respondResultPage(1);
	exit;
}

$prefix = $database['prefix'];
$definedTables = array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedReads", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}Filters", "{$prefix}Links", "{$prefix}Plugins", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}ServiceSettings", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}TrackbackLogs", "{$prefix}Trackbacks", "{$prefix}Users", "{$prefix}UserSettings");


if ($_REQUEST['type'] == 1) { // type 1, plugin

	$values = explode('/', $_REQUEST['name'], 2);
	$pluginname = $values[0];
	$version = $values[1];

	$query = "select name from {$database['prefix']}ServiceSettings WHERE value = '" . mysql_tt_escape_string($_REQUEST['name']) . "'";
	
	$plugintablesraw = DBQuery::queryColumn($query);
	if (count($plugintablesraw) <= 0) {
		respondResultPage(1);
		exit;
	}
	$plugintables = array();
	foreach($plugintablesraw as $dbname) {
		$origname = $dbname;
		$dbname = $database['prefix'] . substr($dbname, 9);
		if (!doesExistTable($dbname)) {
			respondResultPage(1);
			exit;
		}
		if (in_array($dbname , $definedTables)) {
			respondResultPage(1);
			exit;
		}
		array_push($plugintables, $dbname);
		$query = "delete from {$database['prefix']}ServiceSettings WHERE name = '$origname' AND value = '" . mysql_tt_escape_string($_REQUEST['name']) . "'";
		DBQuery::execute($query);
	}
	$result = true;
	foreach($plugintables as $dbname) {
		$query = "DROP TABLE {$dbname}";
		$result = DBQuery::execute($query) && $result;
	}
	
	deactivatePlugin($pluginname);
	
	if ($result == false) {
		respondResultPage(1);
		exit;
	}
} else { // type 2, table

	$dbname = $_REQUEST['name'];
	if (strncmp($dbname, $database['prefix'], strlen($database['prefix'])) != 0) {
		respondResultPage(1);
		exit;
	}
	if (in_array($dbname , $definedTables)) {
		respondResultPage(1);
		exit;
	}
	if (!doesExistTable($dbname)) {
		respondResultPage(1);
		exit;
	}
	
	$query = "DROP TABLE {$dbname}";
	DBQuery::execute($query);
}

respondResultPage(0);
?>
