<?
if (doesHaveMembership()) {
	$user = array('id' => getUserId());
	list($user['name']) = fetchQueryRow("select name from {$database['prefix']}Users where userid = {$user['id']}");
	list($user['blog'], $user['timezone']) = fetchQueryRow("select name, timezone from {$database['prefix']}BlogSettings where owner = {$user['id']}");
	$user['homepage'] = getBlogURL($user['blog']);
} else {
	$user = null;
}
?>