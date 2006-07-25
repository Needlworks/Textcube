<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/headerA.php';
require ROOT . '/lib/piece/owner/contentMenuA0.php';
?>
						<form method="post" action="<?=$blogURL?>/owner/center/dashboard">
							<div id="part-center-dashboard" class="part">
								<h2 class="caption"><span class="main-text"><?php echo _t('태터툴즈에 대한 여러가지 정보를 봅니다')?></span></h2>
							</div>
						</form>					
<?php
foreach ($centerMappings as $mapping) {
?>
						<div id="<?=$mapping['plugin']?>"><h3><?=$mapping['title']?></h3><?=handleCenters($mapping)?></div><hr />
<?php
}

require ROOT . '/lib/piece/owner/footer1.php';
?>