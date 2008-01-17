<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

require ROOT . '/lib/includeForBlogOwner.php';


requireStrictRoute();

global $blogid, $database;
$page=(isset($_GET['page']) && $_GET['page'] >= 1 ? $_GET['page'] : 1 );
$row=(isset($_GET['rows']) && $_GET['rows'] >= 1 ? $_GET['rows'] : 25 );

$bloglist = POD::queryColumn("SELECT blogid,name FROM `{$database['prefix']}BlogSettings` WHERE name = 'name' ORDER BY blogid ASC LIMIT " . ($page-1)*$row . " ,$row");
$blogcount = POD::queryCount("SELECT blogid,name FROM `{$database['prefix']}BlogSettings` WHERE name = 'name'");

$pages = (int)(($blogcount-0.5) / $row)+1;
if ($pages<$page) {
	respond::PrintResult(array('error' => -2,'result' => $pages));
}

$paging = array('url' => "", 'prefix' => '?page=', 'postfix' => '', 'total' => 0, 'pages' => 0, 'page' => 0);
$paging['pages'] = $pages;
$paging['page'] = $page ;
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';

if($bloglist){
    $resultString = getPagingView($paging, $pagingTemplate, $pagingItemTemplate)."*";
    $resultString .= $blogcount."*";
	$tempString = "";
    foreach($bloglist as $bid) {
		$result = POD::queryAll("SELECT * FROM `{$database['prefix']}BlogSettings` WHERE blogid = {$bid}");
 		foreach($result as $row) {
 			$bsetting[$row['name']] = $row['value'];
 		}
		$bsetting['owner']= POD::queryCell("SELECT userid FROM `{$database['prefix']}teamblog` WHERE acl & ".BITWISE_OWNER." != 0 AND blogid = " . $bid);
 		$tempString.=$bid.",";
		$tempString.=$bsetting['name'].",";
		$tempString.=$bsetting['title'].",";
		$tempString.=User::getName($bsetting['owner'])."(".User::getEmail($bsetting['owner']).")*";
	}
	if($tempString!=''){
		$resultString .= substr($tempString,0,-1);
		respond::PrintResult(array('error' => 0, 'result' => $resultString));
	}
	else {
		respond::PrintResult(array('error' => -2,'result' => $paging['pages']));
	}
}
else {
	respond::PrintResult(array('error' => -1, 'result' => mysql_error()));
}
?>
