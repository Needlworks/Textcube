<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
header("Content-type: application");
header("Content-Disposition: attachment; filename=textcube_reader_feed_" . date("Ymd") . ".opml");
header("Content-Description: PHP4 Generated Data");
$writer = User::getName();
echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
?>
<opml version="1.0">
<head>
<title>Textcube <?php echo TEXTCUBE_VERSION;?> Reader Feeds</title>
<ownerName><?php echo htmlspecialchars($writer);?></ownerName>
<ownerEmail><?php echo User::getEmail();?></ownerEmail>
</head>
<body>
<?php
foreach (getFeeds($blogid) as $feed) {
$feed['title'] = str_replace('\\\'', '\'', escapeJSInAttribute($feed['title']));
$feed['description'] = str_replace('\\\'', '\'', escapeJSInAttribute($feed['description']));
?>
<outline text="<?php echo $feed['title'];?>" description="<?php echo $feed['description'];?>" htmlUrl="<?php echo escapeJSInAttribute($feed['blogURL']);?>" title="<?php echo $feed['title'];?>" type="rss" version="RSS" xmlUrl="<?php echo escapeJSInAttribute($feed['xmlurl']);?>" />
<?php
}
?>
</body>
</opml>
