<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'page' => array('number','min'=>1,'default'=>1)
	) 
);

require ROOT . '/lib/includeForBlogOwner.php';
$page=(isset($_GET['page']) && $_GET['page'] >= 1 ? $_GET['page'] : 1 );
$service['admin_script']='control.js';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
global $blogURL;
$page = $_GET['page'];

?>
<h2 class="caption"><span class="main-text"><?php echo _t('새 사용자 등록'); ?></span></h2>
<div id=container-add-user>
<form onsubmit="return false;">
<span id="sgtOwner"></span><?php echo _t('이름'); ?> : <input type=text name='ui-name' id='ui-name'>
<span id="sgtOwner"></span><?php echo _t('이메일'); ?> : <input type=text name='ui-email' id='ui-email'>
<input type=submit value="<?php echo _t("새 사용자 등록");?>" onclick="sendUserAddInfo(document.getElementById('ui-name').value,document.getElementById('ui-email').value);return false;">
</form>
</div>
<h2 class="caption"><span class="main-text">User List</span></h2>
<div id=container-user-list class='part'>
<table class="data-inbox" id="table-user-list" cellpadding="0" cellspacing="0">
<thead><tr><th><?php echo _t('사용자 ID');?></th><th><?php echo _t('로그인 ID');?></th><th><?php echo _t('이름');?></th><th><?php echo _t('마지막 로그인');?></th></tr></thead>
<tbody>
<?php
$row = 25;
$userlist = POD::queryAll("SELECT * FROM `{$database['prefix']}Users` WHERE 1 ORDER BY userid LIMIT ". ($page-1) * $row .", ". $row);
$usercount = POD::queryCount("SELECT userid FROM `{$database['prefix']}Users` WHERE 1");

$pages = (int)(($usercount-0.5) / $row)+1;

if($userlist){
	$tempString = "";
    foreach($userlist as $row) {
		?>

<tr id="table-user-list_<?php echo $row['userid']?>">
	<td>
		<?php echo $row['userid']?>
	</td>
	<td>
		<a href="<?php echo $blogURL?>/owner/control/user/detail/<?php echo $row['userid']?>"><?php echo $row['loginid']?></a>
	</td>
	<td>
		<?php echo $row['name']?>
	</td>
	<td>
		<?php echo ($row['lastLogin']?date("Y/m/d H:i:s T",$row['lastLogin']):"")?>
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
	<span id="total-count"><?php echo _f('총 %1명의 사용자',$usercount);?></span>
</div>

<?php 
$page=(isset($_GET['page']) ? $_GET['page'] : 1 );
// end header
?>
<?php require ROOT . '/lib/piece/owner/footer.php';?>
