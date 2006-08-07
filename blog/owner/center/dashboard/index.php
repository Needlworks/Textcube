<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/headerA.php';
require ROOT . '/lib/piece/owner/contentMenuA0.php';
?>
						<div method="post" action="<?php echo $blogURL;?>/owner/center/dashboard">
							<div id="part-center-dashboard" class="part">
								<h2 class="caption"><span class="main-text"><?php echo _t('태터툴즈에 대한 여러가지 정보를 봅니다');?></span></h2>
							
<?php
foreach ($centerMappings as $mapping) {
?>
								<div id="<?php echo $mapping['plugin'];?>" class="section">
									<h3><?php echo $mapping['title'];?></h3>
<?php echo handleCenters($mapping);?>
								</div>
<?php
}
?>
							</div>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>