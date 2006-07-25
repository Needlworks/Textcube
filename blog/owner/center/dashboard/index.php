<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/headerA.php';
require ROOT . '/lib/piece/owner/contentMenuA0.php';

ob_start();
?>
						<form method="post" action="<?=$blogURL?>/owner/center/dashboard">
							<div id="part-center-dashboard" class="part">
								<h2 class="caption"><span class="main-text"><?php echo _t('태터툴즈에 대한 여러가지 정보를 봅니다')?></span></h2>
							</div>
						</form>
						<div>[##_CT_RecentPS_Default_##]</div>						
<?
$contents = ob_get_contents();
ob_end_clean();

handleCenters($contents);
echo removeAllTags($contents);

require ROOT . '/lib/piece/owner/footer1.php';
?>