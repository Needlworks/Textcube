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
$i = 0;
$total = count($centerMappings);
$sepPosition = $total/2;
foreach ($centerMappings as $mapping) {
	if (($i == 0) || ($i == $sepPosition)) {
		echo '<div id="dojo_boardbar' , $i / $sepPosition , '" class="panel">';
	}
?>
								<div id="<?php echo $mapping['plugin'];?>" class="section">
									<h3><?php echo $mapping['title'];?></h3>
									<?php echo handleCenters($mapping);?>
								</div>
<?php
	if (($i == $sepPosition - 1) || ($i == $total - 1)) {
		echo '</div>';
	}
	$i++;
}
?>
							</div>
						</form>

<script type="text/javascript">
	new dojo.dnd.HtmlDropTarget(document.getElementById('dojo_boardbar0'), ["dashboard"]);
	<?php if ($i > 1) { ?>
	new dojo.dnd.HtmlDropTarget(document.getElementById('dojo_boardbar1'), ["dashboard"]);
	<?php } ?>
<?php
foreach ($centerMappings as $mapping) {
?>
	new dojo.dnd.HtmlDragSource(document.getElementById('<?php echo $mapping['plugin'];?>'), ["dashboard"]);
<?php
}
?>

</script>
						
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
