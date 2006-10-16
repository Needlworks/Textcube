<?php
define('ROOT', '../../../..');

require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/headerB.php';
require ROOT . '/lib/piece/owner/contentMenuB1.php';


if (empty($_POST['listedPluginStatus'])) {
	$listType = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'listedPluginStatus'");
	$_POST['listedPluginStatus'] = ($listType == false) ? array("activated", "deactivated") : explode("|", $listType);
} else if (is_array($_POST['listedPluginStatus'])) {
	sort($_POST['listedPluginStatus']);
	if ($_POST['listedPluginStatus'] != array("activated") && $_POST['listedPluginStatus'] != array("deactivated") && $_POST['listedPluginStatus'] != array("activated", "deactivated")) {
		$_POST['listedPluginStatus'] = array("activated", "deactivated");
	}
} else {
	$_POST['listedPluginStatus'] = array("activated", "deactivated");
}

if (!DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'listedPluginStatus'")) {
	DBQuery::execute("INSERT `{$database['prefix']}UserSettings` (`user`, `name`, `value`) VALUES ($owner, 'listedPluginStatus', '".implode("|", $_POST['listedPluginStatus'])."')");
} else {
	DBQuery::execute("UPDATE `{$database['prefix']}UserSettings` SET `value` = '".implode("|", $_POST['listedPluginStatus'])."' WHERE `user` = $owner AND `name` = 'listedPluginStatus'");
}
?>
						<script type="text/javascript">
							//<![CDATA[
								function clearPluginTable(plugin, num) {
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/plugin/dbsetting/clear");
									request.onSuccess = function() {
										alert("<?php echo _t('해당 테이블의 데이터가 삭제되었습니다.');?>");
										changeList();
									}
									request.onError = function() {
										alert("<?php echo _t('테이블의 데이터를 지우지 못했습니다.');?>");
									}
									request.send("name=" + plugin);
								}

								function deletePluginTable(plugin, num) {
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/plugin/dbsetting/delete");
									request.onSuccess = function() {
										alert("<?php echo _t('해당 테이블이 삭제되었습니다.');?>");
										changeList();
									}
									request.onError = function() {
										alert("<?php echo _t('테이블의 데이터를 지우지 못했습니다.');?>");
									}
									request.send("name=" + plugin);
								}

								function changeList() {
									document.getElementById("part-plugin-table-list").submit();
								}

								window.addEventListener("load", execLoadFunction, false);
								
								function execLoadFunction() {
									removeItselfById('submit-button-box');
								}
							//]]>
						</script>
						
						<form id="part-plugin-table-list" class="part" method="post" action="<?php echo $blogURL."/owner/plugin/tableSetting";?>">
							<h2 class="caption"><span class="main-text"><?php echo _t('플러그인이 생성한 테이블입니다');?></span></h2>
							
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('플러그인이 생성한 테이블입니다. 테이블의 데이터를 삭제할 수 있습니다.');?></p>
							</div>

							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="title"><span class="text"><?php echo _t('테이블 이름');?></span></th>
										<th class="dependency"><span class="text"><?php echo _t('의존성');?></span></th>
										<th class="clean"><span class="text"><?php echo _t('데이터 삭제');?></span></th>
										<th class="delete"><span class="text"><?php echo _t('테이블 삭제');?></span></th>
									</tr>
								</thead>
								<tbody>
<?php
$plugins = array();
$pluginAttrs = array();

$dir = dir(ROOT . '/plugins/');
while ($plugin = $dir->read()) {
	if (!ereg('^[[:alnum:] _-]+$', $plugin))
		continue;
	if (!is_dir(ROOT . '/plugins/' . $plugin))
		continue;
	if (!file_exists(ROOT . "/plugins/$plugin/index.xml"))
		continue;
	$xmls = new XMLStruct();
	if (!$xmls->open(file_get_contents(ROOT . "/plugins/$plugin/index.xml"))) {
		continue;
	} else {
		for ($i = 0; $pluginTable = $xmls->getValue("/plugin/storage/table[$i]/name"); $i++) {
			if(array_key_exists($pluginTable,$pluginAttrs)) {
				$dependency = array(
					"title" => htmlspecialchars($xmls->getValue('/plugin/title[lang()]'))
				);
				array_push($pluginAttrs[$pluginTable], $dependency);
			} else {
				$dependency = array(
					"title" => htmlspecialchars($xmls->getValue('/plugin/title[lang()]'))
				);
				$pluginAttrs[$pluginTable] = array();
				$plugins[$pluginTable] = array();
				array_push($pluginAttrs[$pluginTable], $dependency);
				array_push($plugins[$pluginTable],$pluginTable);
			}
			if ($xmls->doesExist('/plugin/binding/adminMenu'))
				array_push($pluginAttrs[$pluginTable]['scope'], 'admin');
			if ($xmls->doesExist('/plugin/binding/tag'))
				array_push($pluginAttrs[$pluginTable]['scope'], 'blog');
			if ($xmls->doesExist('/plugin/binding/center'))
				array_push($pluginAttrs[$pluginTable]['scope'], 'dashboard');
			if ($xmls->doesExist('/plugin/binding/listener'))
				array_push($pluginAttrs[$pluginTable]['scope'], 'global');
			if ($xmls->doesExist('/plugin/binding/sidebar'))
				array_push($pluginAttrs[$pluginTable]['scope'], 'sidebar');
		}
	}
}

$arrayKeys = array_keys($plugins);
$rowCount = 0;

for ($i=0; $i<count($arrayKeys); $i++) {
	$pluginTable = $arrayKeys[$i];
	
	$title = $pluginAttrs[$pluginTable]['title'];
	$tablename = $pluginTable;
	if (count($scope) == 0)
		$scope = array('none');


	$className = ($rowCount % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($plugins) - 1) ? ' last-line' : '';
	$className .= $active ? ' active-class' : ' inactive-class';
?>
									<tr class="<?php echo $className;?>" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td class="title"><?php echo $tablename;?></td>
										<td class="dependency"><?php
foreach($pluginAttrs[$pluginTable] as $tables) {
	echo $tables['title'];
};?></td>
										<td class="clean"><a id="plugin<?php echo $i;?>Link" class="active-class" href="#void" onclick="clearPluginTable('<?php echo $pluginDir;?>',<?php echo $i;?>)" title="<?php echo _t('이 테이블의 데이터를 삭제합니다.');?>"><span class="text"><?php echo _t('삭제');?></span></a></td>
										<td class="delete"><a id="plugin<?php echo $i;?>Link" class="active-class" href="#void" onclick="clearPluginTable('<?php echo $pluginDir;?>',<?php echo $i;?>)" title="<?php echo _t('이 테이블을 삭제합니다.');?>"><span class="text"><?php echo _t('삭제');?></span></a></td>
									</tr>
<?php
	$rowCount++;
}
?>
								</tbody>
							</table>
						</form>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>