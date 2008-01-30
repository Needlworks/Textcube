<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$IV = array(
	'GET' => array(
		'page' => array('number','min'=>1,'default'=>1)
	) 
);

$service['admin_script']='control.js';

require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';

global $blogURL;
$page = $_GET['page'];

if ( $service['type'] == "single" ) {
?>
<div class="main-explain-box">
	<p class="explain"><?php echo _t('현재 단일 블로그 모드 텍스트 큐브가 설정되어 있습니다. 단일 블로그 모드에서는 대표 블로그 만이 외부에 보여집니다.')?></p>
</div>	
<?php
}
?>
<h2 class="caption"><span class="main-text"><?php echo _t('새 블로그 만들기'); ?></span></h2>
<div id=container-add-blog>
<form onsubmit="return false;">
<span class="label"><?php echo _t('소유자'); ?> : </span>
<span id="sgtOwner"><input type="text" class="bi-owner-loginid" name="location" value="<?php echo getUserEmail(1);?>" /></span>&nbsp;<?php echo _t('블로그 구분자'); ?> : <input type=text name='bi-identify' id='bi-identify'>
<input type=submit value="<?php echo _t("새 블로그 생성");?>" onclick="sendBlogAddInfo(ctlUserSuggestObj.getValue(),document.getElementById('bi-identify').value);return false;">
</form>
</div>
<h2 class="caption"><span class="main-text">Blog List</span></h2>
<div id=container-blog-list class='part'>
<table class="data-inbox" id="table-blog-list" cellpadding="0" cellspacing="0">
<thead><tr><th><?php echo _t('블로그 ID')?></th><th><?php echo _t('블로그 구분자')?></th><th><?php echo _t('블로그 제목')?></th><th><?php echo _t('블로그 소유자')?></th></tr></thead>
<tbody>
<?php
$row = 25;

$bloglist = POD::queryColumn("SELECT blogid,name FROM `{$database['prefix']}BlogSettings` WHERE name = 'name' ORDER BY blogid ASC LIMIT " . ($page-1)*$row . " ,$row");
$blogcount = POD::queryCount("SELECT blogid FROM `{$database['prefix']}BlogSettings` WHERE name = 'name'");

$pages = (int)(($blogcount-0.5) / $row)+1;
if ($pages<$page) {
	printRespond(array('error' => -2,'result' => $pages));
}
if($bloglist){
	$tempString = "";
    foreach($bloglist as $itemBlogId) {
		$result = POD::queryAll("SELECT * FROM `{$database['prefix']}BlogSettings` WHERE blogid = {$itemBlogId}");
 		foreach($result as $row) {
 			$bsetting[$row['name']] = $row['value'];
 		}
		$bsetting['owner']= POD::queryCell("SELECT userid FROM `{$database['prefix']}TeamBlog` WHERE acl & ".BITWISE_OWNER." != 0 AND blogid = " . $itemBlogId);
		?>

<tr id="table-blog-list_<?php echo $itemBlogId?>">
	<td>
		<?php echo $itemBlogId?>
	</td>
	<td>
		<a href="<?php echo $blogURL?>/owner/control/blog/detail/<?php echo $itemBlogId?>"><?php echo $bsetting['name']?></a>
	</td>
	<td>
		<?php echo $bsetting['title']?>
	</td>
	<td>
		<?php echo User::getName($bsetting['owner'])."(".User::getEmail($bsetting['owner']).")";?>
	</td>
</tr>
<?php
	}
}
?>
</tbody>
</table>
</div>
<?php
$paging = array('url' => "", 'prefix' => '?page=', 'postfix' => '', 'total' => 0, 'pages' => 0, 'page' => 0);
$paging['pages'] = $pages;
$paging['page'] = $page ;
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
?>
<div id="page-navigation">
	<span id="page-list"><?php echo getPagingView($paging, $pagingTemplate, $pagingItemTemplate);?></span>
	<span id="total-count"><?php echo _f('총 %1개의 블로그',$blogcount);?></span>
</div>
<?php 
require ROOT . '/lib/piece/owner/footer.php';
?>
<script type="text/javascript">
//<![CDATA[
	try {
		document.getElementById("sgtOwner").innerHTML = '';
		var ctlUserSuggestObj = new ctlUserSuggest(document.getElementById("sgtOwner"),  false);
		ctlUserSuggestObj.setInputClassName("bi-owner-loginid");
		ctlUserSuggestObj.setValue("<?php echo getUserEmail(1);?>");	
	} catch (e) {
		document.getElementById("sgtOwner").innerHTML = '<input type="text" class="bi-owner-loginid" name="location" value="" />';
	}
//]]>
</script> 
