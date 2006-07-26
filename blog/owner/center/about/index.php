<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/headerA.php';
require ROOT . '/lib/piece/owner/contentMenuA2.php';
?>
						<form method="post" action="<?php echo $blogURL?>/owner/center/about">
							<div id="part-center-about" class="part">
								<h2 class="caption"><span class="main-text"><?php echo  _t('태터툴즈')?></span></h2>
							</div>
					        <?php echo  TATTERTOOLS_NAME?> : 	
					        <?php echo  TATTERTOOLS_VERSION?>
						</form>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
