<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getCategoryId($blogid, $name, $parentName = false) {
	global $database;
	
	$name = mysql_tt_escape_string($name);
	if ($parentName === false)
		$sql = "SELECT id FROM {$database['prefix']}Categories WHERE owner = $blogid AND name = '$name'";
	else {
		$parentName = mysql_tt_escape_string($parentName);
		$sql = "SELECT c.id FROM {$database['prefix']}Categories c LEFT JOIN {$database['prefix']}Categories c2 ON c.parent = c2.id AND c.owner = c2.owner WHERE c.owner = $blogid AND c.name = '$name' AND c2.name = '$parentName'";
	}
	return DBQuery::queryCell($sql);
}

function getCategoryIdByLabel($blogid, $label) {
	global $database;
	if (empty($label))
		return 0;
	$label = mysql_tt_escape_string($label);
	return DBQuery::queryCell("SELECT id FROM {$database['prefix']}Categories WHERE owner = $blogid AND label = '$label'");
}

function getCategoryNameById($blogid, $id) {
	global $database;
	$result = DBQuery::queryCell("SELECT name FROM {$database['prefix']}Categories WHERE owner = $blogid AND id = $id");
	if (is_null($result))
		return _text('전체');
	else
		return $result;
}

function getCategoryBodyIdById($blogid, $id) {
	global $database;
	$result = DBQuery::queryCell("SELECT bodyId FROM {$database['prefix']}Categories WHERE owner = $blogid AND id = $id");
	if (($id === 0) || ($result == '') || ($id === null))
		return 'tt-body-category';
	return $result;
}

function getCategoryLabelById($blogid, $id) {
	global $database;
	if ($id === null)
		return '';
//	if ($id === 0)
//		return _text('분류 전체보기');
	return DBQuery::queryCell("SELECT label FROM {$database['prefix']}Categories WHERE owner = $blogid AND id = $id");
}

function getCategoryLinkById($blogid, $id) {
	global $database;
	if (($id === null) || ($id === 0))
		return '';
	$result = getCategoryNameById($blogid,$id);
	if($children = getParentCategoryId($blogid, $id)) {
		$result = rawurlencode(htmlspecialchars(escapeURL(getCategoryNameById($blogid,$children)))).'/'.rawurlencode(htmlspecialchars(escapeURL($result)));
	} else {
		$result = rawurlencode(htmlspecialchars(escapeURL($result)));
	}
	return $result;
}	

function getCategories($blogid) {
	global $database;
	$rows = DBQuery::queryAll("SELECT * FROM {$database['prefix']}Categories WHERE owner = $blogid AND id > 0 ORDER BY parent, priority");
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
	global $database, $service;
	$sql = "SELECT * FROM {$database['prefix']}SkinSettings WHERE blogid = ".getBlogId();
	$setting = DBQuery::queryRow($sql);
	$skin = array('name' => "{$setting['skin']}", 
			'url'               => $service['path'] . "/image/tree/{$setting['tree']}", 
			'labelLength'       => $setting['labelLengthOnTree'], 
			'showValue'         => $setting['showValueOnTree'], 
			'itemColor'         => "{$setting['colorOnTree']}", 
			'itemBgColor'       => "{$setting['bgColorOnTree']}", 
			'activeItemColor'   => "{$setting['activeColorOnTree']}", 
			'activeItemBgColor' => "{$setting['activeBgColorOnTree']}", );
	return $skin;
}

function getParentCategoryId($blogid, $id) {
	global $database;
	return DBQuery::queryCell("SELECT parent FROM {$database['prefix']}Categories WHERE owner = $blogid AND id = $id");
}

function getNumberChildCategory($id = null) {
	global $database;
	$sql = "SELECT * FROM {$database['prefix']}Categories WHERE owner = ".getBlogId()." AND parent " . ($id == null ? 'IS NULL' : "= $id");
	$result = DBQuery::queryRow($sql);
	return DBQuery::queryCell($sql);
}

function getNumberEntryInCategories($id) {
	global $database;
	return DBQuery::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE owner = ".getBlogId()." AND draft = 0 AND category " . ($id == null ? 'IS NULL' : "= $id"));
}

function addCategory($blogid, $parent, $name) {
	global $database;
	
	if (empty($name))
		return false;
	if (!is_null($parent) && !Validator::id($parent))
		return false;
	if ($parent !== null) {
		$label = DBQuery::queryCell("SELECT name FROM {$database['prefix']}Categories WHERE owner = $blogid AND id = $parent");
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

	$sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE owner = $blogid AND name = '$name' $parentStr";
	
	if (DBQuery::queryCell($sql) > 0)
		return false;

	$newPriority = DBQuery::queryCell("SELECT MAX(priority) FROM {$database['prefix']}Categories WHERE owner = $blogid") + 1;
	$newId = DBQuery::queryCell("SELECT MAX(id) FROM {$database['prefix']}Categories WHERE owner = $blogid") + 1;
	$result = DBQuery::query("INSERT INTO {$database['prefix']}Categories (owner, id, parent, name, priority, entries, entriesInLogin, label, visibility) VALUES ($blogid, $newId, $parent, '$name', $newPriority, 0, 0, '$label', 2)");
	updateEntriesOfCategory($blogid);
	return $result ? true : false;
}

function deleteCategory($blogid, $id) {
	global $database;
	
	if (!is_numeric($id))
		return false;
	return DBQuery::execute("DELETE FROM {$database['prefix']}Categories WHERE owner = $blogid AND id = $id");
}

function modifyCategory($blogid, $id, $name, $bodyid) {
	global $database;
	if($id==0) checkRootCategoryExistence($blogid);
	if ((empty($name)) && (empty($bodyid)))
		return false;
	$row = DBQuery::queryRow("SELECT p.name, p.id FROM {$database['prefix']}Categories c LEFT JOIN {$database['prefix']}Categories p ON c.parent = p.id WHERE c.owner = $blogid AND c.id = $id");
	$label = $row['name'];
	$parentId = $row['id'];	
	if (!empty($parentId)) {
		$parentStr = "AND parent = $parentId";
	} else
		$parentStr = 'AND parent is null';
	
	$label = mysql_tt_escape_string(mysql_lessen(empty($label) ? $name : "$label/$name", 255));
	$name = mysql_tt_escape_string(mysql_lessen($name, 127));
	$sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE owner = $blogid AND id=$id";
	// $sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE owner = $blogid AND name='$name' $parentStr";	
	if(DBQuery::queryCell($sql) == false)
		return false;
	$bodyid = mysql_tt_escape_string(mysql_lessen($bodyid, 20));
	
	$result = DBQuery::query("UPDATE {$database['prefix']}Categories SET name = '$name', label = '$label', bodyId = '$bodyid'  WHERE owner = $blogid AND id = $id");
	if ($result && (mysql_affected_rows() > 0))
		clearRSS();
	updateEntriesOfCategory($blogid);
	return $result ? true : false;
}

function updateEntriesOfCategory($blogid, $id = - 1) {
	global $database;
	$result = DBQuery::query("SELECT * FROM {$database['prefix']}Categories WHERE owner = $blogid AND parent IS NULL");
	while ($row = mysql_fetch_array($result)) {
		$parent = $row['id'];
		$parentName = mysql_lessen($row['name'], 127);
		$row['name'] = mysql_tt_escape_string($parentName);
		$countParent = DBQuery::queryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE owner = $blogid AND draft = 0 AND visibility > 0 AND category = $parent");
		$countInLoginParent = DBQuery::queryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE owner = $blogid AND draft = 0 AND category = $parent");
		$result2 = DBQuery::query("SELECT * FROM {$database['prefix']}Categories WHERE owner = $blogid AND parent = $parent");
		while ($rowChild = mysql_fetch_array($result2)) {
			$label = mysql_tt_escape_string(mysql_lessen($parentName . '/' . $rowChild['name'], 255));
			$rowChild['name'] = mysql_tt_escape_string(mysql_lessen($rowChild['name'], 127));
			$countChild = DBQuery::queryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE owner = $blogid AND draft = 0 AND visibility > 0 AND category = {$rowChild['id']}");
			$countInLogInChild = DBQuery::queryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE owner = $blogid AND draft = 0 AND category = {$rowChild['id']}");
			DBQuery::query("UPDATE {$database['prefix']}Categories SET entries = $countChild, entriesInLogin = $countInLogInChild, `label` = '$label' WHERE owner = $blogid AND id = {$rowChild['id']}");
			$countParent += $countChild;
			$countInLoginParent += $countInLogInChild;
		}
		DBQuery::query("UPDATE {$database['prefix']}Categories SET entries = $countParent, entriesInLogin = $countInLoginParent, `label` = '{$row['name']}' WHERE owner = $blogid AND id = $parent");
	}
	return true;
}

function moveCategory($blogid, $id, $direction) {
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
			WHERE _my.id = $id AND _my.owner = $blogid";
	$result = DBQuery::query($sql);
	$row = mysql_fetch_array($result);
	$myParent = is_null($row['myParent']) ? 'NULL' : $row['myParent'];
	$parentId = is_null($row['parentId']) ? 'NULL' : $row['parentId'];
	$parentPriority = is_null($row['parentPriority']) ? 'NULL' : $row['parentPriority'];
	$parentParent = is_null($row['parentParent']) ? 'NULL' : $row['parentParent'];
	$myPriority = $row['myPriority'];
	$sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE parent = $myId AND owner = $blogid";
	$myIsHaveChild = (mysql_result(DBQuery::query($sql), 0, 0) > 0) ? true : false;
	$aux = $parentId == 'NULL' ? 'parent is null' : "parent = $parentId";
	$sql = "SELECT id, parent, priority FROM {$database['prefix']}Categories WHERE $aux AND owner = $blogid AND priority $sign $myPriority ORDER BY priority $arrange LIMIT 1";
	$result = DBQuery::query($sql);
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
							id = $nextId AND owner = $blogid";
			DBQuery::query($sql);
			$sql = "UPDATE {$database['prefix']}Categories
						SET
							priority = $nextPriority
						WHERE
							id = $myId AND owner = $blogid";
			DBQuery::query($sql);
		// 자신이 2 depth를 가지지 않은 1 depth 카테고리이거나, 위치를 바꿀 대상이 없는 경우.
		} else {
			// 위치를 바꿀 대상 카테고리에 같은 이름이 존재하는지 판별.
			$myName = DBQuery::queryCell("SELECT `name` FROM `{$database['prefix']}Categories` WHERE `id` = $myId");
			$overlapCount = DBQuery::queryCell("SELECT count(*) FROM `{$database['prefix']}Categories` WHERE `name` = '$myName' AND `parent` = $nextId");
			// 같은 이름이 없으면 이동 시작.
			if ($overlapCount == 0) {
				$sql = "UPDATE {$database['prefix']}Categories
							SET
								parent = $nextId
							WHERE
								id = $myId AND owner = $blogid";
				DBQuery::query($sql);
				$sql = "SELECT id, priority FROM {$database['prefix']}Categories WHERE parent = $nextId AND owner = $blogid ORDER BY priority LIMIT 1";
				$result = DBQuery::query($sql);
				$row = mysql_fetch_array($result);
				$nextId = is_null($row['id']) ? 'NULL' : $row['id'];
				$nextPriority = is_null($row['priority']) ? 'NULL' : $row['priority'];
				if ($nextId != 'NULL') {
					$sql = "UPDATE {$database['prefix']}Categories
								SET
									priority = " . max($nextPriority, $myPriority) . "
								WHERE
									id = $nextId AND owner = $blogid";
					DBQuery::query($sql);
					$sql = "UPDATE {$database['prefix']}Categories
								SET
									priority = " . min($nextPriority, $myPriority) . "
								WHERE
									id = $myId AND owner = $blogid";
					DBQuery::query($sql);
				}
			// 같은 이름이 있으면.
			} else {
				$sql = "UPDATE {$database['prefix']}Categories
							SET
								priority = $myPriority
							WHERE
								id = $nextId AND owner = $blogid";
				DBQuery::query($sql);
				$sql = "UPDATE {$database['prefix']}Categories
							SET
								priority = $nextPriority
							WHERE
								id = $myId AND owner = $blogid";
				DBQuery::query($sql);
			}
		}
	// 이동할 자신이 2 depth일 때.
	} else {
		// 위치를 바꿀 대상이 1 depth이면.
		if ($nextId == 'NULL') {
			$myName = mysql_tt_escape_string(DBQuery::queryCell("SELECT `name` FROM `{$database['prefix']}Categories` WHERE `id` = $myId"));
			$overlapCount = DBQuery::queryCell("SELECT count(*) FROM `{$database['prefix']}Categories` WHERE `name` = '$myName' AND `parent` IS NULL");
			// 1 depth에 같은 이름이 있으면 2 depth로 직접 이동.
			if ($overlapCount > 0) {
				$sql = "SELECT `id`, `parent`, `priority` FROM `{$database['prefix']}Categories` WHERE `parent` IS NULL AND `owner` = $blogid AND `priority` $sign $parentPriority ORDER BY `priority` $arrange";
				$result = DBQuery::query($sql);
				while ($row = mysql_fetch_array($result)) {
					$nextId = $row['id'];
					$nextParentId = $row['parent'];
					$nextPriority = $row['priority'];
					
					// 위치를 바꿀 대상 카테고리에 같은 이름이 존재하는지 판별.
					$myName = mysql_tt_escape_string(DBQuery::queryCell("SELECT `name` FROM `{$database['prefix']}Categories` WHERE `id` = $myId"));
					$overlapCount = DBQuery::queryCell("SELECT count(*) FROM `{$database['prefix']}Categories` WHERE `name` = '$myName' AND `parent` = $nextId");
					// 같은 이름이 없으면 이동 시작.
					if ($overlapCount == 0) {
						$sql = "UPDATE `{$database['prefix']}Categories`
									SET
										`parent` = $nextId
									WHERE
										`id` = $myId AND `owner` = $blogid";
						DBQuery::query($sql);
							break;
					}
				}
			// 같은 이름이 없으면 1 depth로 이동.
			} else {
				$sql = "UPDATE {$database['prefix']}Categories SET parent = NULL WHERE id = $myId AND owner = $blogid";
				DBQuery::query($sql);
				$sql = "SELECT id, priority FROM {$database['prefix']}Categories WHERE parent is null AND owner = $blogid AND priority $sign $parentPriority ORDER BY priority $arrange LIMIT 1";
				$result = DBQuery::query($sql);
				$row = mysql_fetch_array($result);
				$nextId = is_null($row['id']) ? 'NULL' : $row['id'];
				$nextPriority = is_null($row['priority']) ? 'NULL' : $row['priority'];
				if ($nextId == 'NULL') {
					$operator = ($direction == 'up') ? '-' : '+';
					$sql = "UPDATE {$database['prefix']}Categories SET priority = $parentPriority $operator 1 WHERE id = $myId AND owner = $blogid";
					DBQuery::query($sql);
					return;
				}
				if ($direction == 'up') {
					$aux = "SET priority = priority+1 WHERE priority >= $parentPriority AND owner = $blogid";
					$aux2 = "SET priority = $parentPriority WHERE id = $myId AND owner = $blogid";
				} else {
					$aux = "SET priority = priority+1 WHERE priority >= $nextPriority AND owner = $blogid";
					$aux2 = "SET priority = $nextPriority WHERE id = $myId AND owner = $blogid";
				}
				$sql = "UPDATE {$database['prefix']}Categories $aux";
				DBQuery::query($sql);
				$sql = "UPDATE {$database['prefix']}Categories $aux2";
				DBQuery::query($sql);
			}
		// 위치를 바꿀 대상이 2 depth이면 위치 교환.
		} else {
			$sql = "UPDATE {$database['prefix']}Categories
						SET
							priority = $myPriority
						WHERE
							id = $nextId AND owner = $blogid";
			DBQuery::query($sql);
			$sql = "UPDATE {$database['prefix']}Categories
						SET
							priority = $nextPriority
						WHERE
							id = $myId AND owner = $blogid";
			DBQuery::query($sql);
		}
	}
	updateEntriesOfCategory($blogid);
}

function checkRootCategoryExistence($blogid) {
	global $database;
	$sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE owner = $blogid AND id = 0";
	if(!(DBQuery::queryCell($sql))) {
		$name = _text('전체');
		addCategory($blogid,null,'tempRootCategory');
		$id = DBQuery::queryCell("SELECT MAX(id) FROM {$database['prefix']}Categories WHERE owner = $blogid");
		$result = DBQuery::query("UPDATE {$database['prefix']}Categories SET id = 0 AND name = '$name' AND priority = 1 WHERE owner = $blogid AND id = $id LIMIT 1");
		return $result ? true : false;
	}
	return false;
}

function getCategoryVisibility($blogid, $id) {
	global $database;
	$result = DBQuery::queryCell("SELECT visibility FROM {$database['prefix']}Categories WHERE owner = $blogid AND id = $id");
	if ($result==false)
		return 2;
	else
		return $result;
}

function getParentCategoryVisibility($blogid, $id) {
	global $database;
	if($id == 0) return false;
	$parentId = DBQuery::queryCell("SELECT parent FROM {$database['prefix']}Categories WHERE owner = $blogid AND id = $id");
	if($parentId == NULL) return false;
	$parentVisibility = DBQuery::queryCell("SELECT visibility FROM {$database['prefix']}Categories WHERE owner = $blogid AND id = $parentId");
	if ($parentVisibility == false)
		return 2;
	else
		return $parentVisibility;
}

function setCategoryVisibility($blogid, $id, $visibility) {
	global $database;
	if($id == 0) return false;
	$parentVisibility = getParentCategoryVisibility($blogid, $id);
	if ($parentVisibility && $parentVisibility < 2) return false; // return without changing if parent category is set to hidden.
	$result = DBQuery::query("UPDATE {$database['prefix']}Categories SET visibility = $visibility WHERE owner = $blogid AND id = $id");
	if ($result && $visibility == 1) $result = setChildCategoryVisibility($blogid, $id, $visibility);
	if ($result)
		clearRSS();
	updateEntriesOfCategory($blogid);
	return $result ? $visibility : false;
}

function setChildCategoryVisibility($blogid, $id, $visibility) {
	global $database;
	if($id == 0) return false;
	$childCategories = DBQuery::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE owner = $blogid AND parent = $id");
	if($childCategories!=false) {
		foreach($childCategories as $childCategory) {
			$result = DBQuery::query("UPDATE {$database['prefix']}Categories SET visibility = $visibility WHERE owner = $blogid AND id = $childCategory");
			if($result == false) return false;
		}
		return $result ? $visibility : false;
	}
	return $visibility;
}
?>
