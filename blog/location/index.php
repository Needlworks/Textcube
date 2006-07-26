<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
$locatives = getLocatives($owner);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/locatives.php';
require ROOT . '/lib/piece/blog/end.php';
?>