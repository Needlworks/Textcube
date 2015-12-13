<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

if(isset($suri['id'])) {
	if ($feed = POD::queryRow("SELECT *
		FROM {$database['prefix']}Feeds
		WHERE id = {$suri['id']}")) {
		Respond::ResultPage(updateFeed($feed));
		exit;
	} else {
		Respond::ResultPage(-1);
		exit;
	}
}

set_time_limit(360);
ob_implicit_flush();
?>
<!DOCTYPE html>
<html>
<head>
		<meta charset="utf-8">
		<title>Update all feeds</title>
		<script type="text/javascript" src="<?php echo $context->getProperty('uri.service');?>/resources/script/common3.min.js"></script>
		<script type="text/javascript">
			//<![CDATA[
				var servicePath = "<?php echo $context->getProperty('service.path');?>";
				var blogURL = "<?php echo $context->getProperty('uri.blog');?>";
				var adminSkin = "<?php echo $context->getProperty('panel.skin');?>";
			//]]>
		</script>
	</head>
	<body>
		<?php echo str_repeat('<!-- flush buffer -->', 400);?>
		<script type="text/javascript">
			//<![CDATA[
				var progress = parent.document.getElementById("progress");
				progress.innerHTML = "(0%)";
			//]]>
		</script>
<?php
$feeds = POD::queryAll("SELECT f.*
		FROM {$database['prefix']}Feeds f,
			{$database['prefix']}FeedGroups g,
			{$database['prefix']}FeedGroupRelations gr
		WHERE g.blogid = $blogid
			AND gr.feed = f.id
			AND gr.blogid = g.blogid
			AND gr.groupid = g.id
		ORDER BY f.title");
$count = 0;
foreach ($feeds as $feed) {
?>
		<script type="text/javascript">
			//<![CDATA[
				var icon = parent.document.getElementById("iconFeedStatus<?php echo $feed['id'];?>");
				if(icon) {
					try{
						parent.Reader.startScroll("feedBox", getOffsetTop(icon) - getOffsetTop(parent.document.getElementById("feedBox")) - 50);
					} catch(e) {alert(e.message);}
					icon.src = servicePath + "/resources/style/default/image/reader/iconUpdateIng.gif";
				}
			//]]>
		</script>
<?php
	$count++;
	$result = updateFeed($feed);
?>
		<script type="text/javascript">
			//<![CDATA[
				/* update complete : [<?php echo $result;?>] <?php echo $feed['xmlurl'];?> */
				if(icon) {
					switch(<?php echo $result;?>) {
						case 0:
							icon.src = servicePath + "/resources/style/default/image/reader/iconUpdate.gif";
							break;
						default:
							icon.src = servicePath + "/resources/style/default/image/reader/iconFailure.gif";
					}
				}
				progress.innerHTML = "(<?php echo sprintf('%.1f', $count * 100 / sizeof($feeds));?>%)";
			//]]>
		</script>
<?php
}
?>
		<script type="text/javascript">
			//<![CDATA[
				parent.Reader.refreshFeedList(parent.Reader.selectedGroup);
				parent.Reader.refreshEntryList(parent.Reader.selectedGroup, parent.Reader.selectedFeed);
				setTimeout("parent.document.getElementById('progress').innerHTML = ''", 1000);
			//]]>
		</script>
	</body>
</html>
