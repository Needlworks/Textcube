<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
header("Content-type: application");
header("Content-Disposition: attachment; filename=tatter_reader_feed_" . date("Ymd") . ".opml");
header("Content-Description: PHP4 Generated Data");
$writer = fetchQueryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = $owner");
echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
?>
<opml version="1.0">
<head>
<title>Tattertools 1.0 Reader Feeds</title>
<ownerName><?=htmlspecialchars($writer)?></ownerName>
<ownerEmail></ownerEmail>
</head>
<body>
<?
foreach (getFeeds($owner) as $feed) {
?>
<outline text="<?=escapeJSInAttribute($feed['title'])?>" description="<?=escapeJSInAttribute($feed['description'])?>" htmlUrl="<?=escapeJSInAttribute($feed['blogURL'])?>" title="<?=escapeJSInAttribute($feed['title'])?>" type="rss" version="RSS" xmlUrl="<?=escapeJSInAttribute($feed['xmlURL'])?>" />
<?
}
?>
</body>
</opml>