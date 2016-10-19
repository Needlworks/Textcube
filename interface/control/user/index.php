<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'page' => array('number','min'=>1,'default'=>1)
	) 
);

require ROOT . '/library/preprocessor.php';
$page=(isset($_GET['page']) && $_GET['page'] >= 1 ? $_GET['page'] : 1 );
$context = Model_Context::getInstance();
$context->setProperty('service.admin_script','control.js');
require ROOT . '/interface/common/control/header.php';

requirePrivilege('group.creators');

$page = $_GET['page'];

?>
	<div id="part-create-newuser" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('새 사용자 등록'); ?></span></h2>
		
		<form onsubmit="return false;">
			<fieldset>
				<dl>
					<dt><?php echo _t('이름'); ?></dt>
					<dd><input type="text" id="ui-name" name="ui-name" /></dd>
					<dt><?php echo _t('이메일').' ('._t('로그인 ID').')';?></dt>
					<dd><input type="text" id="ui-email" name="ui-email" /></dd>
				</dl>
			</fieldset>
			<div class="button-box">
				<a class="button" href="#void" onclick="sendUserAddInfo(document.getElementById('ui-name').value,document.getElementById('ui-email').value); return false;"><?php echo _t('새 사용자 등록');?></a>
			</div>
		</form>
	</div>
	
	<div id="part-user-list" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('사용자 목록');?></span></h2>
		
		<div id="container-user-list" class="part">
			<table id="table-user-list" class="data-inbox">
				<thead>
					<tr>
						<th><?php echo _t('사용자 ID');?></th>
						<th><?php echo _t('로그인 ID');?></th>
						<th><?php echo _t('이름');?></th>
						<th><?php echo _t('최근 로그인');?></th>
						<th><?php echo _t('임시 암호');?></th>
					</tr>
				</thead>
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
					<tr id="table-user-list_<?php echo $row['userid'];?>">
						<td><?php echo $row['userid']?></td>
						<td><a href="<?php echo $context->getProperty('uri.blog');?>/control/user/detail/<?php echo $row['userid']?>"><?php echo $row['loginid'];?></a></td>
						<td><?php echo $row['name']?></td>
						<td><?php echo ($row['lastlogin']?date("Y/m/d H:i:s T",$row['lastlogin']):'<span class="warning">'._t('아직 로그인하지 않았습니다.').'</span>');?></td>
						<td><?php if(empty($row['lastlogin']) || null !== Setting::getUserSettingGlobal('AuthToken',null,$row['userid'])) echo Setting::getUserSettingGlobal('AuthToken',null,$row['userid']);?></td>
					</tr>
<?php
	}
}
?>
				</tbody>
			</table>
		</div>
	</div>
	
<?php
$paging = array('url' => "", 'prefix' => '?page=', 'postfix' => '', 'total' => 0, 'pages' => 0, 'page' => 0);
$paging['pages'] = $pages;
$paging['page'] = $page ;
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[##_paging_rep_link_num_##]</a>';
?>
	<div id="page-navigation">
		<span id="page-list"><?php echo Paging::getPagingView($paging, $pagingTemplate, $pagingItemTemplate);?></span>
		<span id="total-count"><?php echo _f('총 %1명의 사용자',$usercount);?></span>
	</div>
	
<?php 
$page=(isset($_GET['page']) ? $_GET['page'] : 1 );
// end header
?>
<?php require ROOT . '/interface/common/control/footer.php';?>
