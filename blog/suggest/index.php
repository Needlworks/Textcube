<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
$IV = array(
	'GET' => array(
		'id' => array('string', 'default' => false),
		'cursor' => array('int', 'default' => false),
		'filter' => array('string', 'default' => '1')
	)
);
require ROOT . '/lib/include.php';
header('Content-Type: text/xml; charset=utf-8');
$id = isset($_GET['id']) ? $_GET['id'] : false;
$cursor = isset($_GET['cursor']) ? $_GET['cursor'] : false;
$filter = isset($_GET['filter']) ? $_GET['filter'] : '1';
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
echo "<response";
if ($id !== false)
	echo " id=\"$id\"";
if ($cursor !== false)
	echo " cursor=\"$cursor\"";
echo ">\r\n";
$tags = array();
foreach (suggestLocalTags($owner, $filter) as $tag)
	echo "<tag>" . htmlspecialchars($tag) . "</tag>\r\n";
echo "</response>\r\n";
?>
