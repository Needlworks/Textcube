<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class FeedGroup {
	/*@static@*/
	function getId($name, $add = false) {
		global $database;
		$name = UTF8::lessenAsEncoding($name);
		if (empty($name))
			return 0;
		$query = new TableQuery($database['prefix'] . 'FeedGroups');
		$query->setQualifier('blogid', getBlogId());
		$query->setQualifier('title', $name, true);
		$id = $query->getCell('id');
		if (is_null($id) && $add) {
			$query->unsetQualifier('title');
			$id = $query->getCell('MAX(id) + 1');
			$query->setQualifier('id', $id);
			$query->setQualifier('title', $name, true);
			if ($query->insert())
				return $id;
			else
				return null;
		}
		return $id;
	}

	/*@static@*/
	function getName($id) {
		global $database;
		if (!Validator::number($id, 0))
			return null;
		if ($id == 0)
			return '';
		$query = new TableQuery($database['prefix'] . 'FeedGroups');
		$query->setQualifier('blogid', getBlogId());
		$query->setQualifier('id', $id);
		return $query->getCell('title');
	}
}
?>