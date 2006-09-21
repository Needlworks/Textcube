<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/headerA.php';
require ROOT . '/lib/piece/owner/contentMenuA0.php';

?>
<script src="<?php echo $service['path'];?>/script/dojo/dojo.js" type="text/javascript"></script>
<script type="text/javascript">
	dojo.require("dojo.dnd.HtmlDragAndDrop");
</script>

<script type="text/javascript">
	DragPanel = function(node, type) {
		dojo.dnd.HtmlDragSource.call(this, node, type);
	}
		
	dojo.inherits(DragPanel, dojo.dnd.HtmlDragSource);
	
	dojo.lang.extend(DragPanel, {
		onDragStart: function() {
			this.parentMethod = DragPanel.superclass.onDragStart;
			var dragObj = this.parentMethod();
			delete this.parentMethod;
			dragObj.disableX = true;
			return dragObj;
		}
	});


<?php
if (!file_exists(ROOT . '/cache/CHECKUP')) {
?>
	window.addEventListener("load", checkTattertoolsVersion, false);
	function checkTattertoolsVersion() {
		if (confirm("<?php echo _t('버전업 체크를 위한 파일을 생성합니다. 지금 생성하시겠습니까?');?>"))
			window.location.href = "<?php echo $blogURL;?>/checkup";
	}
<?php
} else if (file_get_contents(ROOT . '/cache/CHECKUP') != TATTERTOOLS_VERSION) {
?>
	window.addEventListener("load", checkTattertoolsVersion, false);
	function checkTattertoolsVersion() {
		if (confirm("<?php echo _t('태터툴즈 시스템 점검이 필요합니다. 지금 점검하시겠습니까?');?>"))
			window.location.href = "<?php echo $blogURL;?>/checkup";
	}
<?php
}
if (false) {
	fetchConfigVal();
}
?>
</script>
	
	
	
<form method="post" action="<?php echo $blogURL;?>/owner/center/dashboard">
	<div id="part-center-dashboard" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('조각보를 봅니다');?></span></h2>
<?php

$layout = getUserSetting('centerLayout', '');
$newlayout = array();
$addedlayout = array();
$oldcenterlayout = array();

if (count($centerMappings) == 0) {
	$layout = '';
	setUserSetting('centerLayout', '');
}

if ((!empty($layout)) && (($oldcenterlayout = unserialize($layout)) != false) ){
	
	foreach($oldcenterlayout as $item) {
		if ($item['plugin'] == 'TatterToolsSeperator') {
			array_push($newlayout, $item);
		} else if (($pos = array_search($item, $centerMappings, true)) !== false) {
			array_push($newlayout, $item);
			unset($centerMappings[$pos]);
		} else {
			array_push($addedlayout, $item);
		}
	}
	
	$newlayout = array_merge($newlayout, $centerMappings);
} else if (count($centerMappings) > 0) {
	$middlepos = (count($centerMappings) + 1)/2;
	array_splice($centerMappings, $middlepos , 0, array(array('plugin' => 'TatterToolsSeperator')));
	$newlayout = $addedlayout = $centerMappings;
}

if ((count($centerMappings) > 0) || (count($addedlayout) > 0)) {
	setUserSetting('centerLayout', serialize($newlayout));
}

unset($addedlayout);
unset($layout);
unset($oldcenterlayout);

$existSeperator = false;
echo '<div id="dojo_boardbar0" class="panel">';
foreach ($newlayout as $mapping) {
	if ($mapping['plugin'] == 'TatterToolsSeperator') {
		echo '</div><div id="dojo_boardbar1" class="panel">';
		$existSeperator = true;
	} else {
?>
		<div id="<?php echo $mapping['plugin'];?>" class="section">
			<h3><?php echo $mapping['title'];?></h3>
			<?php echo handleCenters($mapping);?>
		</div>
<?php
	}
}
echo '</div>';
if ($existSeperator == false) {
	echo '<div id="dojo_boardbar1" class="panel"></div>';
}

?>
	</div>
</form>

<script type="text/javascript">
	new dojo.dnd.HtmlDropTarget(document.getElementById('dojo_boardbar0'), ["dashboard"]);
	new dojo.dnd.HtmlDropTarget(document.getElementById('dojo_boardbar1'), ["dashboard"]);

<?php
foreach ($newlayout as $mapping) {
	if ($mapping['plugin'] != 'TatterToolsSeperator') {
?>
		new DragPanel(document.getElementById('<?php echo $mapping['plugin'];?>'), ["dashboard"]);
<?php
	}
}
?>

</script>
						
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
