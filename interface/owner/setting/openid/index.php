<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define( 'OPENID_REGISTERS', 10 );
require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
global $database, $blogURL, $hostURL;

$menu_url = $hostURL . $blogURL . "/owner/setting/openid";
$menu1 = $menu_url . "&amp;mode=1";
$menu2 = $menu_url . "&amp;mode=3";
$menu3 = $menu_url . "&amp;mode=5";
$menu4 = $menu_url . "&amp;mode=7";
$order = "order by lastLogin desc";

$mode = preg_replace( '/.*?mode=(\d)/', '\1', $_SERVER["QUERY_STRING"]);
if( !is_numeric($mode) ) { $mode = 7; };
switch( $mode )
{
case 2:
	$menu2 = $menu_url . "&amp;mode=3"; $order = "order by delegatedid asc";
	break;
case 3:
	$menu2 = $menu_url . "&amp;mode=2"; $order = "order by delegatedid desc";
	break;
case 4:
	$menu3 = $menu_url . "&amp;mode=5"; $order = "order by loginCount asc";
	break;
case 5:
	$menu3 = $menu_url . "&amp;mode=4"; $order = "order by loginCount desc";
	break;
case 6:
	$menu4 = $menu_url . "&amp;mode=7"; $order = "order by lastLogin asc";
	break;
case 7:
	$menu4 = $menu_url . "&amp;mode=6"; $order = "order by lastLogin desc";
	break;
case 0:
	$menu1 = $menu_url . "&amp;mode=1"; $order = "order by openid asc";
	break;
case 1:
	$menu1 = $menu_url . "&amp;mode=0"; $order = "order by openid desc";
	break;
}

$openidonlycomment = misc::getBlogSettingGlobal( "AddCommentMode", "" );
if( $openidonlycomment == 'openid' ) {
	$openidonlycomment = "checked='checked'";
} else {
	$openidonlycomment = "";
}

$openidlogodisplay = misc::getBlogSettingGlobal( "OpenIDLogoDisplay", 0 );
if( $openidlogodisplay ) {
	$openidlogodisplay = "checked='checked'";
} else {
	$openidlogodisplay = "";
}

/* Fetch registerred openid */
$openid_list = array();
for( $i=0; $i<OPENID_REGISTERS; $i++ )
{
	$openid_identity = getUserSetting( "openid." . $i );
	if( !empty($openid_identity) ) {
		array_push( $openid_list, $openid_identity );
	}
}
?>
	<script type="text/javascript">
		//<![CDATA[
	function save() {
		try {
			var oonly = document.getElementById( 'openidonlycomment' );
			oonly = oonly.checked ? "1" : "0";
			var ologo = document.getElementById( 'openidlogodisplay' );
			ologo = ologo.checked ? "1" : "0";

			var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/openid/change");
			request.onSuccess = function() {
				PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
			}
			request.onError = function() {
				alert("<?php echo _t('저장하지 못했습니다.');?>");
			}
			request.send("openidonlycomment="+oonly+"&openidlogodisplay="+ologo);
		} catch(e) {
		}
	}
	function setDelegate() {
		try {
			if( !delegatedid ) {
				alert( "<?php echo _t('블로그 주소를 오픈아이디로 사용하지 않습니다.') ?>");
			}

			var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/openid/delegate?openid_identifier=" + escape(delegatedid));
			request.onSuccess = function() {
				PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
			}
			request.onError = function() {
				alert("<?php echo _t('저장하지 못했습니다.');?>");
			}
			request.send("");
		} catch(e) {
		}
	}
	//]]>
	</script>
	
	<div id="part-setting-admin" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('댓글/방명록 설정')?></span></h2>
		<form action="<?php echo $blogURL;?>/owner/setting/openid/change" method="post">
		<fieldset class="container">
		<div class="data-inbox">
			<dl><dd>
			<input id="openidonlycomment" type="checkbox" name="openidonlycomment" <?php echo $openidonlycomment?> />
			<label for="openidonlycomment"><?php echo _t('오픈아이디로 로그인을 해야만 댓글 및 방명록을 쓸 수 있습니다.') ?></label>
			</dd></dl>
			<dl><dd>
			<input id="openidlogodisplay" type="checkbox" name="openidlogodisplay" <?php echo $openidlogodisplay?> />
			<label for="openidlogodisplay"><?php echo _t('오픈아이디로 로그인하여 쓴 댓글/방명록에 오픈아이디 아이콘을 표시합니다.') ?></label>
			</dd></dl>
			<div class="button-box">
				<input type="submit" class="save-button input-button" value="<?php echo _t('변경하기');?>" onclick="save(); return false;" />
			</div>
		</div>
		</fieldset>
		</form>
	</div>
<?php
	if( Acl::check( 'group.owners' ) ) { /* 블로그 주소를 오픈아이디로 사용 */ 
?>
	
	<div id="part-openid-blogaddress" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('블로그 주소를 오픈아이디로 사용')?></span></h2>
		<table class="data-inbox" cellspacing="0" cellpadding="0">
			<tbody>
				<tr class="site">
					<td>
<?php
		$currentDelegate = misc::getBlogSettingGlobal( 'OpenIDDelegate', '' );
?>
						<select id="openid_for_delegation">
<?php
		print "<option value='' >" . _t('블로그 주소를 오픈아이디로 사용하지 않음') . "</option>";
		foreach( $openid_list as $openid_identity ) {
			$selected = '';
			if( $openid_identity == $currentDelegate ) {
				$selected = "selected";
			}
			print "<option value='$openid_identity' $selected>" . $openid_identity . "</option>";
		}
?>
						</select>
						<input type="button" onclick="setDelegate(); return false" value="<?php echo _t('확인') ?>" class="save-button input-button" />
					</td>
				</tr>
				<tr>
					<td>
						<span class="text"><?php echo sprintf( _t('블로그 주소(%s)를 소유자 계정에 연결된 오픈아이디 중 하나에 위임하여 오픈아이디로 사용할 수 있습니다.'), "$hostURL$blogURL"); ?>
						</span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<div id="part-openid-blogaddress" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('소유자 계정에 연결된 오픈아이디 목록')?></span></h2>
		<table class="data-inbox" cellspacing="0" cellpadding="0">
			<tbody>
<?php
		foreach( $openid_list as $openid_identity ) {
			print "<tr class='site'><td>" . $openid_identity . "</td></tr>";
		}
		if( empty( $openid_list ) ) {
			print "<tr class='site'><td>";
			print _t('소유자 계정에 연결된 오픈아이디가 없습니다');
			print "</td></tr>";
		}
			print "<tr class='site'><td><a href='$blogURL/owner/setting/account'><b>";
			print _t('소유자 계정에 오픈아이디 연결하기');
			print "</b></a></td></tr>";
?>
			</tbody>
		</table>
	</div>
<?php
} /* 소유자 계정 확인 */
?>
	
	<div class="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('오픈아이디 로그인 목록')?></span></h2>
	
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
$sql="select * from {$database['prefix']}OpenIDUsers $order";
$rec = POD::queryAll( $sql );
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
					<td><?php echo $record['loginCount'];?></td>
					<td><?php echo Timestamp::format5($record['lastLogin']);?></td>
				</tr>
<?php
}
?>
			</tbody>
		</table>
	</div>
