<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
if(changeCategoryOfEntries($owner,$_POST['targets'], $_POST['category'])) { 
	respondResultPage(0);
} else {
	respondResultPage(1);
}
?>