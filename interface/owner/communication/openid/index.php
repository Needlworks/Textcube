<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define( 'OPENID_REGISTERS', 10 );
require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/owner/header.php';

$context = Model_Context::getInstance();

/* Fetch registerred openid */
$openid_list = array();
for( $i=0; $i<OPENID_REGISTERS; $i++ )
{
    $openid_identity = Setting::getUserSetting( "openid." . $i ,null,true);
    if( !empty($openid_identity) ) {
        array_push( $openid_list, $openid_identity );
    }
}

$pool = DBModel::getInstance();
$menu_url = $context->getProperty('uri.host') . $context->getProperty('uri.blog') . "/owner/communication/openid";
$menu1 = $menu_url . "?mode=1";
$menu2 = $menu_url . "?mode=3";
$menu3 = $menu_url . "?mode=5";
$menu4 = $menu_url . "?mode=7";

$pool->init("OpenIDUsers");
$pool->setOrder("lastlogin","desc");

$mode = preg_replace( '/.*?mode=(\d)/', '\1', $_SERVER["QUERY_STRING"]);
if( !is_numeric($mode) ) { $mode = 7; };
switch( $mode )
{
case 2:
	$menu2 = $menu_url . "?mode=3";
    $pool->setOrder("delegatedid","asc");
	break;
case 3:
	$menu2 = $menu_url . "?mode=2";
    $pool->setOrder("delegatedid","desc");
	break;
case 4:
	$menu3 = $menu_url . "?mode=5";
    $pool->setOrder("logincount","asc");
	break;
case 5:
	$menu3 = $menu_url . "?mode=4";
    $pool->setOrder("logincount","desc");
	break;
case 6:
	$menu4 = $menu_url . "?mode=7";
    $pool->setOrder("lastlogin","asc");
	break;
case 7:
	$menu4 = $menu_url . "?mode=6";
    $pool->setOrder("lastlogin","desc");
	break;
case 0:
	$menu1 = $menu_url . "?mode=1";
    $pool->setOrder("openid","asc");
	break;
case 1:
	$menu1 = $menu_url . "?mode=0";
    $pool->setOrder("openid","desc");
	break;
}

?>

						<div id="part-openid-loginhistory" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('오픈아이디로 로그인한 사람들의 목록입니다')?></span></h2>
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('이 블로그에 오픈아이디로 로그인하여 글을 남긴 사람들의 기록입니다.').' '._t('댓글을 남긴 아이디와 그에 연결된 오픈아이디를 동시에 확인할 수 있습니다.').'<br />'._t('아이디와 오픈아이디의 대조를 통하여 아이디의 사칭 여부를 판별할 수 있습니다.');?></p>
							</div>
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="site"><span class="text"><a href="<?php echo $menu1?>"><?php echo _t('오픈아이디 주소(이름)')?></a></span></th>
										<th class="site"><span class="text"><a href="<?php echo $menu2?>"><?php echo _t('위임주소')?></a></span></th>
										<th class="site"><span class="text"><a href="<?php echo $menu3?>"><?php echo _t('로그인 회수')?></a></span></th>
										<th class="site"><span class="text"><a href="<?php echo $menu4?>"><?php echo _t('마지막 로그인')?></a></span></th>
									</tr>
								</thead>
								<tbody>
<?php
$rec = $pool->getAll();
for ($i=0; $i<count($rec); $i++) {
    $record = $rec[$i];
    $data = unserialize($record['data']);
    $nickname = "({$data['nickname']})";

    $className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
    $className .= ($i == sizeof($rec) - 1) ? ' last-line' : '';
?>
									<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td><?php echo "{$record['openid']} {$nickname}";?></td>
										<td><?php echo $record['delegatedid'];?></td>
										<td><?php echo $record['logincount'];?></td>
										<td><?php echo Timestamp::format5($record['lastlogin']);?></td>
									</tr>
<?php
}
?>
								</tbody>
							</table>
						</div>
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
