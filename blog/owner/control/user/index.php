<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
$IV = array(
	'GET' => array(
		'page' => array('number','min'=>1,'default'=>1),
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
	function showUserList(page) {
		var request = new HTTPRequest(blogURL + "/owner/control/action/user/index/?page="+page);
		request.onVerify = function () {
			document.getElementById("container-user-list").innerHTML='';
			var resultResponse = this.getText("/response/result");
			var resultRow = resultResponse.split('*');
			if (resultRow.length == 1)
			tempTable = '';
			else {
				field = resultRow[0];
				tempInfo = document.createElement("div");
				tempInfo.id = "page-navigation";
				tempSpan = document.createElement("span");
				tempSpan.id = "page-list";
				tempSpan.innerHTML = field;
				tempInfo.appendChild(tempSpan);
				field = resultRow[1];
				tempSpan = document.createElement("span");
				tempSpan.id = "total-count";
				tempSpan.innerHTML = "총 " + field + "명의 사용자";
				tempInfo.appendChild(tempSpan);

				tempSpan.id = "total-count";
				tempTable = '';
				tempTable = document.createElement("TABLE");
				tempThead = document.createElement("THEAD");
				Tbody_container = document.createElement("TBODY");
				
				tempTable.id = "table-user-list";
				tempTable.className = "data-inbox";
				tempTable.setAttribute("cellpadding", 0);
				tempTable.setAttribute("cellspacing", 0);					

				Tr_container = document.createElement("TR");
				tempTh_0 = document.createElement("TH");
				tempTh_1 = document.createElement("TH");
				tempTh_2 = document.createElement("TH");
				tempTh_3 = document.createElement("TH");
				tempTh_4 = document.createElement("TH");
				tempTh_5 = document.createElement("TH");
				
				tempCheckBox = document.createElement("input");
				tempCheckBox.type = "checkbox";
				tempCheckBox.onclick = function() { selectCheckBoxAll("table-user-list",this.checked); };
				tempTh_0.appendChild(tempCheckBox);
				tempTh_1.innerHTML = "<?php echo _t('ID');?>";
				tempTh_2.innerHTML = "<?php echo _t('E-mail');?>";	
				tempTh_3.innerHTML = "<?php echo _t('이름');?>";
				tempTh_4.innerHTML = "<?php echo _t('마지막 접속 시간');?>";
				tempTh_5.innerHTML = "<?php echo _t('Actions');?>";
				
				Tr_container.appendChild(tempTh_0);
				Tr_container.appendChild(tempTh_1);
				Tr_container.appendChild(tempTh_2);
				Tr_container.appendChild(tempTh_3);
				Tr_container.appendChild(tempTh_4);
				Tr_container.appendChild(tempTh_5);
				tempThead.appendChild(Tr_container);
				tempTable.appendChild(tempThead);

				for (var i=2; i<resultRow.length-1 ; i++) {

					field = resultRow[i].split(',');

					Tr_container = document.createElement("TR");
					Td_checkbox = document.createElement("TD");
					Td_id = document.createElement("TD");
					Td_loginid = document.createElement("TD");
					Td_name = document.createElement("TD");
					Td_date = document.createElement("TD");
					Td_action = document.createElement("TD");
					
					Tr_container.id = "HostList_" + field[0];
					
					Td_id.innerHTML = field[0];
					Td_name.innerHTML = field[2];
					Td_date.innerHTML = field[3];
					Td_action.className = "action";

					tempCheckBox = document.createElement("input");
					tempCheckBox.id = Tr_container.id+"_check";
					tempCheckBox.type = "checkbox";
					Td_checkbox.appendChild(tempCheckBox);
					
					tempLink = document.createElement("A");
					tempLink.setAttribute("href",blogURL + "/owner/control/user/" + field[0]);
					tempLink.innerHTML = field[1];
					Td_loginid.appendChild(tempLink);
					
					tempLink = document.createElement("A");
					tempLink.className = "remove-button button";
					tempLink.id = "rb_" + field[0];
					tempLink.setAttribute("href", "#void");
					tempLink.onclick = function() { deleteUser(this.id.substr(3)); return false; };
					tempLink.setAttribute("title", "<?php echo _t('이 블로그를 삭제합니다.');?>");

					tempSpan = document.createElement("SPAN");
					tempSpan.className = "text";
					tempSpan.innerHTML = "<?php echo _t('삭제');?>";
					
					tempLink.appendChild(tempSpan);
					Td_action.appendChild(tempLink);				
					
					Tr_container.appendChild(Td_checkbox);
					Tr_container.appendChild(Td_id);
					Tr_container.appendChild(Td_loginid);
					Tr_container.appendChild(Td_name);
					Tr_container.appendChild(Td_date);
					Tr_container.appendChild(Td_action);
					Tbody_container.appendChild(Tr_container);
					}
					tempTable.appendChild(Tbody_container);
				}
			if (tempTable != '' ) {
				document.getElementById("container-user-list").appendChild(tempTable);
				document.getElementById("container-user-list").appendChild(tempInfo);
			}
			return true;
		}
		request.send();
	}
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
showUserList(page);
// ]]> </script>

<?php require ROOT . '/lib/piece/owner/footer.php';?>
