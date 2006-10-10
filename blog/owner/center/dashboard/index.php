<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/headerA.php';
require ROOT . '/lib/piece/owner/contentMenuA0.php';

trashVan();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['pos'])) $_GET['pos'] = $_POST['pos'];
	if (isset($_POST['rel'])) $_GET['rel'] = $_POST['rel'];
}

if (isset($_REQUEST['edit'])) {
?>
<script src="<?php echo $service['path'];?>/script/dojo/dojo.js" type="text/javascript"></script>
<script type="text/javascript">
	dojo.require("dojo.dnd.HtmlDragAndDrop");
</script>
<?php
}
?>

<script type="text/javascript">
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
	unset($_GET['pos']);
	unset($_GET['rel']);
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
	unset($_GET['pos']);
	unset($_GET['rel']);
	$middlepos = (count($centerMappings) + 1)/2;
	array_splice($centerMappings, $middlepos , 0, array(array('plugin' => 'TatterToolsSeperator')));
	$newlayout = $addedlayout = $centerMappings;
}

if ((isset($_GET['pos'])) && (($_GET['pos'] < 0) || ($_GET['pos']) >= count($newlayout))) {
	unset($_GET['pos']);
	unset($_GET['rel']);
}

$modified = false;
if (isset($_GET['pos']) && is_numeric($_GET['pos'])) {
	if (isset($_GET['rel']) && is_numeric($_GET['rel']) && (is_numeric($_GET['rel']))) {
		$newpos = $_GET['pos'] + $_GET['rel'];
		if ($newpos < 0) $newpos = 0;
		if ($newpos >= count($newlayout)) $newpos = count($newlayout) - 1;
		$item = array_splice($newlayout, $_GET['pos'], 1);
		array_splice($newlayout, $newpos, 0, $item);
		$modified = true;
	}
}

if ((count($centerMappings) > 0) || (count($addedlayout) > 0) || ($modified == true)) {
	setUserSetting('centerLayout', serialize($newlayout));
}

unset($addedlayout);
unset($layout);
unset($oldcenterlayout);

$existSeperator = false;
$positionCounter = 0;
$secondposition = 0;
echo '<div id="dojo_boardbar0" class="panel">';
foreach ($newlayout as $mapping) {
	if ($mapping['plugin'] == 'TatterToolsSeperator') {
		echo '</div><div id="dojo_boardbar1" class="panel">';
		$existSeperator = true;
		$secondposition = $positionCounter;
	} else {
?>
		<div id="<?php echo $mapping['plugin'];?>" class="section">
			<h3>
				<?php echo $mapping['title'];?> 
<?php
		if (isset($_REQUEST['edit'])) {
?>
				
				<a id="<?php echo $mapping['plugin'];?>dojoup" href="<?php echo $blogURL;?>/owner/center/dashboard?pos=<?php echo $positionCounter ?>&amp;rel=-1&edit">
					<?php echo _T("위로"); ?></a>
				<a id="<?php echo $mapping['plugin'];?>dojodown" href="<?php echo $blogURL;?>/owner/center/dashboard?pos=<?php echo $positionCounter ?>&amp;rel=1&edit">
					<?php echo _T("아래로"); ?></a>
<?php
		}
?>
			</h3>
			<?php echo handleCenters($mapping);?>
		</div>
<?php
	}
	$positionCounter++;
}
echo '</div>';
if ($existSeperator == false) {
	echo '<div id="dojo_boardbar1" class="panel"></div>';
	$secondposition = $positionCounter;
}

if (!isset($_REQUEST['edit'])) {
?>
		<div class="button-box">
			<input type="submit" class="input-button" value="<?php echo _t('편집');?>" onclick="window.location.href='<?php echo $blogURL;?>/owner/center/dashboard?edit'; return false;" >
		</div>
<?php
}
?>
	</div>
</form>

<?php
if (isset($_REQUEST['edit'])) {
?>

<script type="text/javascript">
	DragPanel = function(node, type) {
		dojo.dnd.HtmlDragSource.call(this, node, type);
	}
	dojo.inherits(DragPanel, dojo.dnd.HtmlDragSource);
	
	DropPanel = function(node, type) {
		dojo.dnd.HtmlDropTarget.call(this, node, type);
	}
	dojo.inherits(DropPanel, dojo.dnd.HtmlDropTarget);
	
	var globalChker = true;
	
	function reordering() {
		var pos = 0;
		var pNode = document.getElementById('dojo_boardbar0').firstChild;
		while (pNode != null) {
			if (pNode.className == "section") pNode.pos = pos++;
			pNode = pNode.nextSibling;
		}
		document.getElementById('dojo_boardbar1').plusposition = pos++;
		pNode = document.getElementById('dojo_boardbar1').firstChild;
		while (pNode != null) {
			if (pNode.className == "section") pNode.pos = pos++;
			pNode = pNode.nextSibling;
		}
	}
	dojo.lang.extend(DropPanel, {
		onDrop: function(e) {
			this.parentMethod = DropPanel.superclass.onDrop;
			var retVal = this.parentMethod(e);
			delete this.parentMethod;
			
			if ((retVal == true) && (globalChker == true)) {
				var node = e.dragObject.domNode;
				var prevNode = node.previousSibling;
				var insertposition = 0;
				while (prevNode != null) {
					if (prevNode.className == "section") break;
					prevNode = prevNode.previousSibling;
				}
				if (prevNode != null) {
					insertposition = prevNode.pos + 1;
				} else {
					insertposition = this.domNode.plusposition + 1;
				}
				var rel = insertposition - node.pos;
				if (insertposition > node.pos) rel--;
				if (rel == 0) return retVal;
				var requestURL = "<?php echo $blogURL;?>/owner/center/dashboard?pos=" + node.pos.toString() + "&rel=" + rel.toString();
				
				var request = new HTTPRequest("POST", requestURL);
				request.onSuccess = function () {
				}
				request.onError = function () {
					globalChker = false;
				}
				request.onVerify = function () {
					return true;
				}
				request.send();
				reordering();
			}
			return retVal;
		}
	});
	

	var pan0 = new DropPanel(document.getElementById('dojo_boardbar0'), ["dashboard"]);
	document.getElementById('dojo_boardbar0').plusposition = -1;
	var pan1 = new DropPanel(document.getElementById('dojo_boardbar1'), ["dashboard"]);
	document.getElementById('dojo_boardbar1').plusposition = <?php echo $secondposition;?>;

<?php
$positionCounter = 0;
foreach ($newlayout as $mapping) {
	if ($mapping['plugin'] != 'TatterToolsSeperator') {
?>
		document.getElementById('<?php echo $mapping['plugin'];?>').pos = <?php echo $positionCounter;?>;
		new DragPanel(document.getElementById('<?php echo $mapping['plugin'];?>'), ["dashboard"]);
		
		
		document.getElementById('<?php echo $mapping['plugin'];?>dojoup').parentNode.removeChild(document.getElementById('<?php echo $mapping['plugin'];?>dojoup'));
		document.getElementById('<?php echo $mapping['plugin'];?>dojodown').parentNode.removeChild(document.getElementById('<?php echo $mapping['plugin'];?>dojodown'));
		<?php
	}
	$positionCounter++;
}
?>

</script>

<?php
	}
?>
						
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
