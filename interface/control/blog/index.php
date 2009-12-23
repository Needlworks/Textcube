<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'GET' => array(
		'page' => array('number','min'=>1,'default'=>1)
	) 
);

$service['admin_script']='control.js';
require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/control/header.php';

requirePrivilege('group.creators');

global $blogURL;
$page = $_GET['page'];
?>
	<div id="part-create-newblog" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('새 블로그 만들기'); ?></span></h2>
		
		<form onsubmit="return false;">
			<fieldset>
				<dl>
					<dt><label for="bi-owner-loginid"><?php echo _t('소유자'); ?></label>
					<dd id="suggestContainer"><input id="bi-owner-loginid" class="input-text" name="location" value="<?php echo getUserEmail(1);?>" /></dd>
					<dt><label for="bi-identify"><?php echo _t('블로그 구분자'); ?></label></dt>
					<dd><input type="text" id="bi-identify" name="bi-identify" /></dd>
				</dl>
			</fieldset>
			<div class="button-box">
				<a class="button" href="#void" onclick="sendBlogAddInfo(ctlUserSuggestObj.getValue(),document.getElementById('bi-identify').value); return false;"><?php echo _t("새 블로그 생성");?></a>
			</div>
		</form>
	</div>
	<div id="part-blog-list" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('블로그 목록');?></span></h2>
		
<?php
if ( $service['type'] == "single" ) {
?>
		<p class="message"><?php echo _t('현재 단일 블로그 모드 텍스트큐브가 설정되어 있습니다. 단일 블로그 모드에서는 대표 블로그 만이 외부에 보여집니다.')?></p>
<?php
}
?>
		
		<table id="table-blog-list" class="data-inbox">
			<thead>
				<tr>
				<th><?php echo _t('블로그 ID')?></th>
				<th><?php echo _t('블로그 구분자')?></th>
				<th><?php echo _t('블로그 제목')?></th>
				<th><?php echo _t('블로그 소유자')?></th>
<?php if ( $service['type'] != "single" ) {?>
				<th><?php echo _t('바로 가기')?></th>
<?php }?>
				</tr>
			</thead>
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
		$bsetting['owner']= POD::queryCell("SELECT userid FROM `{$database['prefix']}Privileges` WHERE acl & ".BITWISE_OWNER." != 0 AND blogid = " . $itemBlogId);
?>
				<tr id="table-blog-list_<?php echo $itemBlogId?>">
					<td>
						<?php echo $itemBlogId?>
					</td>
					<td>
						<a href="<?php echo $blogURL?>/control/blog/detail/<?php echo $itemBlogId?>"><?php echo $bsetting['name']?></a>
					</td>
					<td>
						<?php echo $bsetting['title']?>
					</td>
					<td>
						<?php echo User::getName($bsetting['owner'])."(".User::getEmail($bsetting['owner']).")";?>
					</td><?php if ( $service['type'] != "single" ) {?>
					<td class="name">
						<a href="<?php echo getDefaultUrl($itemBlogId);?>"><?php echo _t("보기");?></a>
					</td><?php }?>
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
require ROOT . '/interface/common/control/footer.php';
?>
<script type="text/javascript">
//<![CDATA[
	try {
		document.getElementById("suggestContainer").innerHTML = '';
		var ctlUserSuggestObj = new ctlUserSuggest(document.getElementById("suggestContainer"),  false);
		ctlUserSuggestObj.setValue("<?php echo getUserEmail(1);?>");	
	} catch (e) {
		document.getElementById("suggestContainer").innerHTML = '<input type="text" id="bi-owner-loginid" name="location" value="" />';
	}
//]]>
</script> 
