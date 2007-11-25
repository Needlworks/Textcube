<?php
define('ROOT', '../../../../../..');
require ROOT . '/lib/includeForBlog.php';
require ROOT . '/lib/piece/owner/libForControl.php';
global $blogid,$database;
$page=(isset($_GET['page']) && $_GET['page'] >= 1 ? $_GET['page'] : 1 );

$bloglist = DBQuery::queryColumn("SELECT blogid,name FROM `{$database['prefix']}BlogSettings` WHERE name = 'name' ORDER BY blogid ASC LIMIT " . ($page-1)*25 . " ,25");
$blogcount = DBQuery::queryCount("SELECT blogid,name FROM `{$database['prefix']}BlogSettings` WHERE name = 'name'");

$pages = (int)(($blogcount-0.5) / 25)+1;
if ($pages<$page) {
	printRespond(array('error' => -2,'result' => $pages));
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
		$result = DBQuery::queryAll("SELECT * FROM `{$database['prefix']}BlogSettings` WHERE blogid = {$bid}");
 		foreach($result as $row) {
 			$bsetting[$row['name']] = $row['value'];
 		}
		$bsetting['owner']= DBQuery::queryCell("SELECT userid FROM `{$database['prefix']}teamblog` WHERE acl & ".BITWISE_OWNER." != 0 AND blogid = " . $bid);
 		$tempString.=$bid.",";
		$tempString.=$bsetting['name'].",";
		$tempString.=$bsetting['title'].",";
		$tempString.=getUserName($bsetting['owner']).",";
		$tempString.=getUserEmail($bsetting['owner'])."*";
	}
	if($tempString!=''){
		$resultString .= $tempString;
		printRespond(array('error' => 0, 'result' => $resultString));
	}
	else {
		printRespond(array('error' => -2,'result' => $paging['pages']));
	}
}
else {
	printRespond(array('error' => -1, 'result' => mysql_error()));
}
?>
