<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getCategoryId($owner, $name, $parentName = false) {
	global $database;
	
	$name = mysql_tt_escape_string($name);
	if ($parentName === false)
		$sql = "SELECT id FROM {$database['prefix']}Categories WHERE owner = $owner AND name = '$name'";
	else {
		$parentName = mysql_tt_escape_string($parentName);
		$sql = "SELECT c.id FROM {$database['prefix']}Categories c LEFT JOIN {$database['prefix']}Categories c2 ON c.parent = c2.id AND c.owner = c2.owner WHERE c.owner = $owner AND c.name = '$name' AND c2.name = '$parentName'";
	}
	return fetchQueryCell($sql);
}

function getCategoryIdByLabel($owner, $label) {
	global $database;
	if (empty($label))
		return 0;
	$label = mysql_tt_escape_string($label);
	return fetchQueryCell("SELECT id FROM {$database['prefix']}Categories WHERE owner = $owner AND label = '$label'");
}

function getCategoryNameById($owner, $id) {
	global $database;
	$result = fetchQueryCell("SELECT name FROM {$database['prefix']}Categories WHERE owner = $owner AND id = $id");
	if (is_null($result))
		return _text('전체');
	else
		return $result;
}

function getCategoryBodyIdById($owner, $id) {
	global $database;
	$result = fetchQueryCell("SELECT bodyId FROM {$database['prefix']}Categories WHERE owner = $owner AND id = $id");
	if (($id === 0) || ($result == '') || ($id === null))
		return 'tt-body-category';
	return $result;
}

function getCategoryLabelById($owner, $id) {
	global $database;
	if ($id === null)
		return '';
//	if ($id === 0)
//		return _text('분류 전체보기');
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
	$rows = fetchQueryAll("SELECT * FROM {$database['prefix']}Categories WHERE owner = $owner AND id > 0 ORDER BY parent, priority");
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
	$skin = array('name' => "{$setting['skin']}", 'url' => $service['path'] . "/image/tree/{$setting['tree']}", 'labelLength' => $setting['labelLengthOnTree'], 'showValue' => $setting['showValueOnTree'], 'itemColor' => "{$setting['colorOnTree']}", 'itemBgColor' => "{$setting['bgColorOnTree']}", 'activeItemColor' => "{$setting['activeColorOnTree']}", 'activeItemBgColor' => "{$setting['activeBgColorOnTree']}", );
	return $skin;
}

function getParentCategoryId($owner, $id) {
	global $database;
	return fetchQueryCell("SELECT parent FROM {$database['prefix']}Categories WHERE owner = $owner AND id = $id");
}

function getNumberChildCategory($id = null) {
	global $database, $owner;
	$sql = "SELECT * FROM {$database['prefix']}Categories WHERE owner = $owner AND parent " . ($id == null ? 'IS NULL' : "= $id");
	$result = fetchQueryRow($sql);
	return fetchQueryCell($sql);
}

function getNumberEntryInCategories($id) {
	global $database, $owner;
	return fetchQueryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND category " . ($id == null ? 'IS NULL' : "= $id"));
}

function addCategory($owner, $parent, $name) {
	global $database;
	
	if (empty($name))
		return false;
	if (!is_null($parent) && !Validator::id($parent))
		return false;
	if ($parent !== null) {
		$label = fetchQueryCell("SELECT name FROM {$database['prefix']}Categories WHERE owner = $owner AND id = $parent");
		if ($label === null)
			return false;
		$label .= '/' . $name;
	} else {
		$parent = 'NULL';
		$label = $name;
	}

	$label = mysql_tt_escape_string(mysql_lessen($label, 255));
	$name = mysql_tt_escape_string(mysql_lessen($name, 127));

	if($parent == 'NULL') {
		$parentStr = 'AND parent is null';
	} else {
		$parentStr = "AND parent = $parent";
	}

	$sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE owner = $owner AND name = '$name' $parentStr";
	
	if (fetchQueryCell($sql) > 0)
		return false;

	$newPriority = fetchQueryCell("SELECT MAX(priority) FROM {$database['prefix']}Categories WHERE owner = $owner") + 1;
	$result = mysql_query("INSERT INTO {$database['prefix']}Categories (owner, id, parent, name, priority, entries, entriesInLogin, label) VALUES ($owner, NULL, $parent, '$name', $newPriority, 0, 0, '$label')");
	updateEntriesOfCategory($owner);
	return $result ? true : false;
}

function deleteCategory($owner, $id) {
	global $database;
	
	if (!is_numeric($id))
		return false;
	return executeQuery("DELETE FROM {$database['prefix']}Categories WHERE owner = $owner AND id = $id");
}

function modifyCategory($owner, $id, $name, $bodyid) {
	global $database;
	if($id==0) checkRootCategoryExistence($owner);
	if ((empty($name)) && (empty($bodyid)))
		return false;
	$sql = "SELECT p.name, p.id FROM {$database['prefix']}Categories c LEFT JOIN {$database['prefix']}Categories p ON c.parent = p.id WHERE c.owner = $owner AND c.id = $id";
	$row = fetchQueryRow($sql);	
	$label = $row['name'];
	$parentId = $row['id'];	
	if (!empty($parentId)) {
		$parentStr = "AND parent = $parentId";
	} else
		$parentStr = 'AND parent is null';
	
	$label = mysql_tt_escape_string(mysql_lessen(empty($label) ? $name : "$label/$name", 255));
	$name = mysql_tt_escape_string(mysql_lessen($name, 127));
	$sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE owner = $owner AND id=$id";
	// $sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE owner = $owner AND name='$name' $parentStr";	
	if(DBQuery::queryCell($sql) == false)
		return false;
	$bodyid = mysql_tt_escape_string(mysql_lessen($bodyid, 20));
	
	$result = mysql_query("UPDATE {$database['prefix']}Categories SET name = '$name', label = '$label', bodyId = '$bodyid'  WHERE owner = $owner AND id = $id");
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
		$parentName = mysql_lessen($row['name'], 127);
		$row['name'] = mysql_tt_escape_string($parentName);
		$countParent = fetchQueryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND visibility > 0 AND category = $parent");
		$countInLoginParent = fetchQueryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND category = $parent");
		$result2 = mysql_query("SELECT * FROM {$database['prefix']}Categories WHERE owner = $owner AND parent = $parent");
		while ($rowChild = mysql_fetch_array($result2)) {
			$label = mysql_tt_escape_string(mysql_lessen($parentName . '/' . $rowChild['name'], 255));
			$rowChild['name'] = mysql_tt_escape_string(mysql_lessen($rowChild['name'], 127));
			$countChild = fetchQueryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND visibility > 0 AND category = {$rowChild['id']}");
			$countInLogInChild = fetchQueryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND category = {$rowChild['id']}");
			mysql_query("UPDATE {$database['prefix']}Categories SET entries = $countChild, entriesInLogin = $countInLogInChild, `label` = '$label' WHERE owner = $owner AND id = {$rowChild['id']}");
			$countParent += $countChild;
			$countInLoginParent += $countInLogInChild;
		}
		mysql_query("UPDATE {$database['prefix']}Categories SET entries = $countParent, entriesInLogin = $countInLoginParent, `label` = '{$row['name']}' WHERE owner = $owner AND id = $parent");
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
	$nextParentId = '';
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
	$sql = "SELECT id, parent, priority FROM {$database['prefix']}Categories WHERE $aux AND owner = $owner AND priority $sign $myPriority ORDER BY priority $arrange LIMIT 1";
	$result = mysql_query($sql);
	$canMove = (mysql_num_rows($result) > 0) ? true : false;
	$row = mysql_fetch_array($result);
	$nextId = is_null($row['id']) ? 'NULL' : $row['id'];
	$nextParentId = is_null($row['parent']) ? 'NULL' : $row['parent'];
	$nextPriority = is_null($row['priority']) ? 'NULL' : $row['priority'];
	// 이동할 자신이 1 depth 카테고리일 때.
	if ($myParent == 'NULL') {
		// 자신이 2 depth를 가지고 있고, 위치를 바꿀 대상 카테고리가 있는 경우.
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
		// 자신이 2 depth를 가지지 않은 1 depth 카테고리이거나, 위치를 바꿀 대상이 없는 경우.
		} else {
			// 위치를 바꿀 대상 카테고리에 같은 이름이 존재하는지 판별.
			$myName = fetchQueryCell("SELECT `name` FROM `{$database['prefix']}Categories` WHERE `id` = $myId");
			$overlapCount = fetchQueryCell("SELECT count(*) FROM `{$database['prefix']}Categories` WHERE `name` = '$myName' AND `parent` = $nextId");
			// 같은 이름이 없으면 이동 시작.
			if ($overlapCount == 0) {
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
			// 같은 이름이 있으면.
			} else {
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
			}
		}
	// 이동할 자신이 2 depth일 때.
	} else {
		// 위치를 바꿀 대상이 1 depth이면.
		if ($nextId == 'NULL') {
			$myName = mysql_tt_escape_string(fetchQueryCell("SELECT `name` FROM `{$database['prefix']}Categories` WHERE `id` = $myId"));
			$overlapCount = fetchQueryCell("SELECT count(*) FROM `{$database['prefix']}Categories` WHERE `name` = '$myName' AND `parent` IS NULL");
			// 1 depth에 같은 이름이 있으면 2 depth로 직접 이동.
			if ($overlapCount > 0) {
				$sql = "SELECT `id`, `parent`, `priority` FROM `{$database['prefix']}Categories` WHERE `parent` IS NULL AND `owner` = $owner AND `priority` $sign $parentPriority ORDER BY `priority` $arrange";
				$result = mysql_query($sql);
				while ($row = mysql_fetch_array($result)) {
					$nextId = $row['id'];
					$nextParentId = $row['parent'];
					$nextPriority = $row['priority'];
					
					// 위치를 바꿀 대상 카테고리에 같은 이름이 존재하는지 판별.
					$myName = mysql_tt_escape_string(fetchQueryCell("SELECT `name` FROM `{$database['prefix']}Categories` WHERE `id` = $myId"));
					$overlapCount = fetchQueryCell("SELECT count(*) FROM `{$database['prefix']}Categories` WHERE `name` = '$myName' AND `parent` = $nextId");
					// 같은 이름이 없으면 이동 시작.
					if ($overlapCount == 0) {
						$sql = "UPDATE `{$database['prefix']}Categories`
									SET
										`parent` = $nextId
									WHERE
										`id` = $myId AND `owner` = $owner";
						mysql_query($sql);
							break;
					}
				}
			// 같은 이름이 없으면 1 depth로 이동.
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
		// 위치를 바꿀 대상이 2 depth이면 위치 교환.
		} else {
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
		}
	}
	updateEntriesOfCategory($owner);
}

function checkRootCategoryExistence($owner) {
	global $database;
	$sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE owner = $owner AND id = 0";
	if(!(fetchQueryCell($sql))) {
		$name = _text('전체');
		addCategory($owner,null,'tempRootCategory');
		$id = fetchQueryCell("SELECT MAX(id) FROM {$database['prefix']}Categories WHERE owner = $owner");
		$result = mysql_query("UPDATE {$database['prefix']}Categories SET id = 0 AND name = '$name' AND priority = 1 WHERE owner = $owner AND id = $id LIMIT 1");
		return $result ? true : false;
	}
	return false;
}

function getCategoryVisibility($owner, $id) {
	global $database;
	$result = fetchQueryCell("SELECT visibility FROM {$database['prefix']}Categories WHERE owner = $owner AND id = $id");
	if (is_null($result))
		return 2;
	else
		return $result;
}

function setCategoryVisibility($owner, $id, $visibility) {
	global $database;
	if($id == 0) return false;
	$result = mysql_query("UPDATE {$database['prefix']}Categories SET visibility = $visibility WHERE owner = $owner AND id = $id");
	if ($result && (mysql_affected_rows() > 0))
		clearRSS();
	updateEntriesOfCategory($owner);
	return $result ? $visibility : false;
}
?>