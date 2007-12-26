<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define('ROOT', '../../../..');
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

?>
<script type="text/javascript"> // <![CDATA[

var page = <?php echo $page;?>;
function ctlRefresh() {
	ctlTableObj.refreshTable();
}

function deleteBlog(bid) {
	if (!confirm(_t('되돌릴 수 없습니다.\t\n\n계속 진행하시겠습니까?'))) return false;
	var request = new HTTPRequest(blogURL + "/owner/control/action/blog/delete/"+bid);
	request.onSuccess = function() {
		PM.showMessage(_t('선택된 블로그가 삭제되었습니다.'), "center", "top");
		ctlTableObj.refreshTable();
	//	showBlogList(page);
	}
	request.onError = function() {
		alert(_t('블로그 삭제에 실패하였습니다.'));
	}
	request.send();
}
// ]]> </script>
<h2 class="caption"><span class="main-text"><?php echo _t('새 블로그 만들기'); ?></span></h2>
<div id=container-add-blog>
<form onsubmit="return false;">
<span class="label"><?php echo _t('소유자'); ?> : </span>
<span id="sgtOwner"></span><?php echo _t('블로그 식별자'); ?> : <input type=text name='bi-identify' id='bi-identify'>
<input type=submit value="<?php echo _t("새 블로그 생성");?>" onclick="sendBlogAddInfo(ctlUserSuggestObj.getValue(),document.getElementById('bi-identify').value);return false;">
</form>
</div>

<h2 class="caption"><span class="main-text">Blog List</span></h2>
<div id=container-blog-list class='part'></div>
<?php 
require ROOT . '/lib/piece/owner/footer.php';
?>
<script type="text/javascript">
//<![CDATA[
	try {
		var ctlUserSuggestObj = new ctlUserSuggest(document.getElementById("sgtOwner"),  false);
		ctlUserSuggestObj.setInputClassName("bi-owner-loginid");
		ctlUserSuggestObj.setValue("<?php echo getUserEmail(1);?>");	
	} catch (e) {
		document.getElementById("sgtOwner").innerHTML = '<input type="text" class="bi-owner-loginid" name="location" value="" />';
	}
	var ctlTableObj = new ctlBlog('container-blog-list');
	ctlTableObj.setPage(page);
	ctlTableObj.showTable();
//]]>
</script> 
