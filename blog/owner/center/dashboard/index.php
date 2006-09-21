<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/headerA.php';
require ROOT . '/lib/piece/owner/contentMenuA0.php';
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
$i = 0;
foreach ($centerMappings as $mapping) {
	$i++;
?>
								<div id="<?php echo $mapping['plugin'];?>" class="section">
									<h3><?php echo $mapping['title'];?></h3>
<?php echo handleCenters($mapping);?>
								</div>
<?php
	if (($i % 2) == 0) {
		echo '<hr style="visibility:hidden" />';
	}
}
?>
							</div>
						</form>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
