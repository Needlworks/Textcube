<?php
class RSS {
	function refresh() {
		global $owner;
		@unlink(ROOT . "/cache/rss/$owner.xml");
	}
}
?>