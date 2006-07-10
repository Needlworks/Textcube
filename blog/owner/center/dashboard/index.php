<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/headerA.php';
require ROOT . '/lib/piece/owner/contentMenuA0.php';
?>
						<form method="post" action="<?=$blogURL?>/owner/center/dashboard">
							<div id="part-center-dashboard" class="part">
								<h2 class="caption"><span class="main-text"><?php echo _t(TATTERTOOLS_NAME.'에 대한 여러가지 정보를 봅니다')?></span></h2>
							</div>
						</form>
<?
require ROOT . '/lib/piece/owner/footer1.php';
?>
