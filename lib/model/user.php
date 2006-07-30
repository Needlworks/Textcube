<?php
if (doesHaveMembership()) {
	$user = array('id' => getUserId());
	$user['name'] = DBQuery::queryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = {$user['id']}");
	$user['homepage'] = getDefaultURL($user['id']);
} else {
	$user = null;
}
?>
