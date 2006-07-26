<?php
class Tag {
	/*@static@*/
	function getId($tag, $add = false) {
		global $database, $owner;
		if ($result = mysql_query("SELECT id FROM {$database['prefix']}Tags WHERE name = '" . mysql_escape_string($tag) . "'")) {
			if ($row = mysql_fetch_row($result)) {
				mysql_free_result($result);
				return $row[0];
			}
			mysql_free_result($result);
		}
		if ($add) {
			$tag = Tag::make($tag);
			if (empty($tag))
				return null;
			if ($result = mysql_query("INSERT INTO {$database['prefix']}Tags VALUES(NULL,'" . mysql_escape_string($tag) . "')"))
				return mysql_insert_id();
		}
		return null;
	}
	
	/*@static@*/
	function make($tag) {
		$tag = trim($tag);
		$tag = str_replace('&quot;', '"', $tag);
		$tag = str_replace('&#39;', '\'', $tag);
		$tag = preg_replace('/ +/', ' ', $tag);
		$tag = preg_replace('/[\x00-\x1f]|[\x7f]/', '', $tag);
		$tag = preg_replace('/^(-|\s)+/', '', $tag);
		$tag = preg_replace('/(-|\s)+$/', '', $tag);
		return $tag;
	}
}
?>