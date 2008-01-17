<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$IV = array(
	'GET' => array(
		'page' => array('int', 'default' => 1)
	)
);

require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();


global $database;
$page=(isset($_GET['page']) && $_GET['page'] >= 1 ? $_GET['page'] : 1 );

$usercount = POD::queryCell("SELECT COUNT(userid) FROM `{$database['prefix']}Users` WHERE 1");

$pages = (int)((0.5+$usercount) / 25)+1;

if ($pages<$page) {
	respond::PrintResult(array('error' => -2,'result' => $pages));
}

$paging = array('url' => "", 'prefix' => '?page=', 'postfix' => '', 'total' => 0, 'pages' => 0, 'page' => 0);
$paging['pages'] = $pages;
$paging['page'] = $page ;
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
$userlist = POD::queryAll("SELECT * FROM `{$database['prefix']}Users` WHERE 1 ORDER BY userid LIMIT ". ($page-1)*25 .", 25");

if($userlist){
	$resultString=getPagingView($paging, $pagingTemplate, $pagingItemTemplate)."*";
	$resultString.=$usercount."*";
	$tempString='';
	foreach($userlist as $row) {
		$tempString.=$row['userid'].",";
		$tempString.=$row['loginid'].",";
		$tempString.=$row['name'].",";
		$tempString.=($row['lastLogin']?date("Y/m/d H:i:s T",$row['lastLogin']):"")."*";
		}
	if($tempString!=''){
		$resultString.=substr($tempString,0,-1);
		respond::PrintResult(array('error' => 0, 'result' => $resultString));
	}
	else {
		respond::PrintResult(array('error' => -2));
	}
}
else {
	respond::PrintResult(array('error' => -1, 'result' => mysql_error()));
}

?>
