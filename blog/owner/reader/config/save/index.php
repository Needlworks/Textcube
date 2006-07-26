<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
respondResultPage(setReaderSetting($owner, $_POST));
?>