<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/headerA.php';
require ROOT . '/lib/piece/owner/contentMenuA0.php';
if (false) {
	fetchConfigVal();
}
?>
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
