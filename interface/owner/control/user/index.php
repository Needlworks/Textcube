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

global $blogURL,$database;
$page=(isset($_GET['page']) ? $_GET['page'] : 1 );
?>
<script type="text/javascript"> // <![CDATA[
	var page = <?php echo $page;?>;
// ]]> </script>
<?php
// end header
?>
<h2 class="caption"><span class="main-text"><?php echo _t('새 사용자 등록'); ?></span></h2>
<div id=container-add-user>
<form onsubmit="return false;">
<span id="sgtOwner"></span><?php echo _t('이름'); ?> : <input type=text name='ui-name' id='ui-name'>
<span id="sgtOwner"></span><?php echo _t('이메일'); ?> : <input type=text name='ui-email' id='ui-email'>
<input type=submit value="<?php echo _t("새 사용자 등록");?>" onclick="sendUserAddInfo(document.getElementById('ui-name').value,document.getElementById('ui-email').value);return false;">
</form>
</div>

<h2 class="caption"><span class="main-text" onclick="toggleLayer('form_addUser'); return false">User List</span></h2>
<div id=container-user-list>
</div> <!--userlist-->
<script type="text/javascript"> // <![CDATA[
	var ctlTableObj = new ctlUser('container-user-list');
	ctlTableObj.setPage(page);
	ctlTableObj.showTable();
// ]]> </script>

<?php require ROOT . '/lib/piece/owner/footer.php';?>
