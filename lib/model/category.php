<?

function getCategoryId($owner, $name, $parentName = false) {
	global $database;
	if ($parentName === false)
		$sql = "SELECT id FROM {$database['prefix']}Categories WHERE owner = $owner AND name = '$name'";
	else
		$sql = "SELECT c.id FROM {$database['prefix']}Categories c LEFT JOIN {$database['prefix']}Categories c2 ON c.parent = c2.id AND c.owner = c2.owner WHERE c.owner = $owner AND c.name = '$name' AND c2.name = '$parentName'";
	return fetchQueryCell($sql);
}

function getCategoryIdByLabel($owner, $label) {
	global $database;
	if (empty($label))
		return 0;
	$label = mysql_escape_string($label);
	return fetchQueryCell("SELECT id FROM {$database['prefix']}Categories WHERE owner = $owner AND label = '$label'");
}

function getCategoryNameById($owner, $id) {
	global $database;
	if ($id === null)
		return '';
	if ($id === 0)
		return _t('분류 전체보기');
	return fetchQueryCell("SELECT name FROM {$database['prefix']}Categories WHERE owner = $owner AND id = $id");
}

function getCategoryLabelById($owner, $id) {
	global $database;
	if ($id === null)
		return '';
	if ($id === 0)
		return _t('분류 전체보기');
	return fetchQueryCell("SELECT label FROM {$database['prefix']}Categories WHERE owner = $owner AND id = $id");
}

function getCategoryLinkById($owner, $id) {
	global $database;
	if (($id === null) || ($id === 0))
		return '';
	$result = getCategoryNameById($owner,$id);
	if($children = getParentCategoryId($owner, $id)) {
		$result = rawurlencode(htmlspecialchars(escapeURL(getCategoryNameById($owner,$children)))).'/'.rawurlencode(htmlspecialchars(escapeURL($result)));
	} else {
		$result = rawurlencode(htmlspecialchars(escapeURL($result)));
	}
	return $result;
}	

function getCategories($owner) {
	global $database;
	$rows = fetchQueryAll("SELECT * FROM {$database['prefix']}Categories WHERE owner = $owner ORDER BY parent, priority");
	$categories = array();
	foreach ($rows as $category) {
		if ($category['parent'] == null) {
			$category['children'] = array();
			$categories[$category['id']] = $category;
		} else if (isset($categories[$category['parent']]))
			array_push($categories[$category['parent']]['children'], $category);
	}
	return $categories;
}

function getCategoriesSkin() {
	global $database;
	global $owner, $service;
	$sql = "select * from {$database['prefix']}SkinSettings where owner = $owner";
	$setting = fetchQueryRow($sql);
	$skin = array('name' => "{$setting['skin']}", 'url' => $service['path'] . "/image/tree/{$setting['tree']}", 'labelLength' => $setting['labelLengthOnTree'], 'showValue' => $setting['showValueOnTree'], 'bgColor' => "{$setting['bgColorOnTree']}", 'itemColor' => "{$setting['colorOnTree']}", 'itemBgColor' => "{$setting['bgColorOnTree']}", 'activeItemColor' => "{$setting['activeColorOnTree']}", 'activeItemBgColor' => "{$setting['activeBgColorOnTree']}", );
	return $skin;
}

function getParentCategoryId($owner, $id) {
	global $database;
	return fetchQueryCell("SELECT parent FROM {$database['prefix']}Categories WHERE owner = $owner AND id = $id");
}

function getNumberChildCategory($id = null) {
	global $database;
	return fetchQueryCell("SELECT COUNT(*) FROM {$database['prefix']}Categories WHERE parent " . ($id == null ? 'IS NULL' : "= $id"));
}

function getNumberEntryInCategories($id) {
	global $database, $owner;
	return fetchQueryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND category " . ($id == null ? 'IS NULL' : "= $id"));
}

function addCategory($owner, $parent, $name) {
	global $database;
	if (empty($name))
		return false;
	if ($parent !== null) {
		$label = fetchQueryCell("SELECT name FROM {$database['prefix']}Categories WHERE owner = $owner AND parent IS NULL");
		if ($label === null)
			return false;
		$label .= '/' . $name;
	} else {
		$parent = 'NULL';
		$label = $name;
	}
	$label = mysql_escape_string($label);
	if (fetchQueryCell("SELECT count(*) FROM {$database['prefix']}Categories WHERE owner = $owner AND label = '$label'") > 0)
		return false;
	$name = mysql_escape_string($name);
	$newPriority = fetchQueryCell("SELECT MAX(priority) FROM {$database['prefix']}Categories WHERE owner = $owner") + 1;
	$result = mysql_query("INSERT INTO {$database['prefix']}Categories (owner, id, parent, name, priority, entries, entriesInLogin, label) VALUES ($owner, NULL, $parent, '$name', $newPriority, 0, 0, '$label')");
	updateEntriesOfCategory($owner);
	return $result ? true : false;
}

function deleteCategory($owner, $id) {
	global $database;
	return executeQuery("DELETE FROM {$database['prefix']}Categories WHERE owner = $owner AND id = $id");
}

function modifyCategory($owner, $id, $name) {
	global $database;
	if (empty($name))
		return false;
	$label = fetchQueryCell("SELECT p.name FROM {$database['prefix']}Categories c JEFT JOIN {$database['prefix']}Categories p ON c.owner = p.owner AND c.parent = p.id WHERE owner = $owner AND id = $id");
	$label = empty($label) ? $name : "$label/$name";
	$name = mysql_escape_string($name);
	$label = mysql_escape_string($label);
	$result = mysql_query("UPDATE {$database['prefix']}Categories SET name = '$name', label = '$label' WHERE owner = $owner AND id = $id");
	if ($result && (mysql_affected_rows() > 0))
		clearRSS();
	updateEntriesOfCategory($owner);
	return $result ? true : false;
}

function updateEntriesOfCategory($owner, $id = - 1) {
	global $database;
	$result = mysql_query("SELECT * FROM {$database['prefix']}Categories WHERE owner = $owner AND parent IS NULL");
	while ($row = mysql_fetch_array($result)) {
		$parent = $row['id'];
		$parentName = mysql_escape_string($row['name']);
		$countParent = fetchQueryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND visibility > 0 AND category = $parent");
		$countInLoginParent = fetchQueryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND category = $parent");
		$result2 = mysql_query("SELECT * FROM {$database['prefix']}Categories WHERE owner = $owner AND parent = $parent");
		while ($rowChild = mysql_fetch_array($result2)) {
			$rowChild['name'] = mysql_escape_string($rowChild['name']);
			$countChild = fetchQueryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND visibility > 0 AND category = {$rowChild['id']}");
			$countInLogInChild = fetchQueryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND category = {$rowChild['id']}");
			mysql_query("UPDATE {$database['prefix']}Categories SET entries = $countChild, entriesInLogin = $countInLogInChild, `label` = '$parentName/{$rowChild['name']}' WHERE owner = $owner AND id = {$rowChild['id']}");
			$countParent += $countChild;
			$countInLoginParent += $countInLogInChild;
		}
		mysql_query("UPDATE {$database['prefix']}Categories SET entries = $countParent, entriesInLogin = $countInLoginParent, `label` = '$parentName' WHERE owner = $owner AND id = $parent");
	}
	return true;
}

function moveCategory($owner, $id, $direction) {
	global $database;
	if ($direction == 'up') {
		$sign = '<';
		$arrange = 'DESC';
	} else {
		$sign = '>';
		$arrange = 'ASC';
	}
	$myId = $id;
	$myPriority = '';
	$myParent = '';
	$parentId = '';
	$parentPriority = '';
	$parentParent = '';
	$myIsHaveChild = '';
	$nextId = '';
	$nextPriority = '';
	$sql = "SELECT 
				_parent.id AS parentId,
				_parent.priority AS parentPriority,
				_parent.parent AS parentParent,
				_my.priority AS myPriority,
				_my.parent AS myParent
			FROM {$database['prefix']}Categories AS _my 
				LEFT JOIN {$database['prefix']}Categories AS _parent ON _parent.id = _my.parent 
			WHERE _my.id = $id AND _my.owner = $owner";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	$myParent = is_null($row['myParent']) ? 'NULL' : $row['myParent'];
	$parentId = is_null($row['parentId']) ? 'NULL' : $row['parentId'];
	$parentPriority = is_null($row['parentPriority']) ? 'NULL' : $row['parentPriority'];
	$parentParent = is_null($row['parentParent']) ? 'NULL' : $row['parentParent'];
	$myPriority = $row['myPriority'];
	$sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE parent = $myId AND owner = $owner";
	$myIsHaveChild = (mysql_result(mysql_query($sql), 0, 0) > 0) ? true : false;
	$aux = $parentId == 'NULL' ? 'parent is null' : "parent = $parentId";
	$sql = "SELECT id, priority FROM {$database['prefix']}Categories WHERE $aux AND owner = $owner AND priority $sign $myPriority ORDER BY priority $arrange LIMIT 1";
	$result = mysql_query($sql);
	$canMove = (mysql_num_rows($result) > 0) ? true : false;
	$row = mysql_fetch_array($result);
	$nextId = is_null($row['id']) ? 'NULL' : $row['id'];
	$nextPriority = is_null($row['priority']) ? 'NULL' : $row['priority'];
	if ($myParent == 'NULL') {
		if ($myIsHaveChild && $nextId != 'NULL') {
			$sql = "UPDATE {$database['prefix']}Categories
						SET
							priority = $myPriority
						WHERE
							id = $nextId AND owner = $owner";
			mysql_query($sql);
			$sql = "UPDATE {$database['prefix']}Categories
						SET
							priority = $nextPriority
						WHERE
							id = $myId AND owner = $owner";
			mysql_query($sql);
		} else {
			$sql = "UPDATE {$database['prefix']}Categories
						SET
							parent = $nextId
						WHERE
							id = $myId AND owner = $owner";
			mysql_query($sql);
			$sql = "SELECT id, priority FROM {$database['prefix']}Categories WHERE parent = $nextId AND owner = $owner ORDER BY priority LIMIT 1";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
			$nextId = is_null($row['id']) ? 'NULL' : $row['id'];
			$nextPriority = is_null($row['priority']) ? 'NULL' : $row['priority'];
			if ($nextId != 'NULL') {
				$sql = "UPDATE {$database['prefix']}Categories
							SET
								priority = " . max($nextPriority, $myPriority) . "
							WHERE
								id = $nextId AND owner = $owner";
				mysql_query($sql);
				$sql = "UPDATE {$database['prefix']}Categories
							SET
								priority = " . min($nextPriority, $myPriority) . "
							WHERE
								id = $myId AND owner = $owner";
				mysql_query($sql);
			}
		}
	} else {
		if ($nextId != 'NULL') {
			$sql = "UPDATE {$database['prefix']}Categories
						SET
							priority = $myPriority
						WHERE
							id = $nextId AND owner = $owner";
			mysql_query($sql);
			$sql = "UPDATE {$database['prefix']}Categories
						SET
							priority = $nextPriority
						WHERE
							id = $myId AND owner = $owner";
			mysql_query($sql);
		} else {
			$sql = "UPDATE {$database['prefix']}Categories SET parent = NULL WHERE id = $myId AND owner = $owner";
			mysql_query($sql);
			$sql = "SELECT id, priority FROM {$database['prefix']}Categories WHERE parent is null AND owner = $owner AND priority $sign $parentPriority ORDER BY priority $arrange LIMIT 1";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
			$nextId = is_null($row['id']) ? 'NULL' : $row['id'];
			$nextPriority = is_null($row['priority']) ? 'NULL' : $row['priority'];
			if ($nextId == 'NULL') {
				$operator = ($direction == 'up') ? '-' : '+';
				$sql = "UPDATE {$database['prefix']}Categories SET priority = $parentPriority $operator 1 WHERE id = $myId AND owner = $owner";
				mysql_query($sql);
				return;
			}
			if ($direction == 'up') {
				$aux = "SET priority = priority+1 WHERE priority >= $parentPriority AND owner = $owner";
				$aux2 = "SET priority = $parentPriority WHERE id = $myId AND owner = $owner";
			} else {
				$aux = "SET priority = priority+1 WHERE priority >= $nextPriority AND owner = $owner";
				$aux2 = "SET priority = $nextPriority WHERE id = $myId AND owner = $owner";
			}
			$sql = "UPDATE {$database['prefix']}Categories $aux";
			mysql_query($sql);
			$sql = "UPDATE {$database['prefix']}Categories $aux2";
			mysql_query($sql);
		}
	}
	updateEntriesOfCategory($owner);
}
?>
