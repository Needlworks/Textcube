<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// global variables for Category information cache
global $__gCacheCategoryTree, $__gCacheCategoryRaw, $__gCacheCategoryVisibilityList;

$__gCacheCategoryTree = array();
$__gCacheCategoryRaw = array();
$__gCacheCategoryVisibilityList = array();

function getCategoryId($blogid, $name, $parentName = false) {
	requireComponent('Needlworks.Cache.PageCache');

	global $__gCacheCategoryRaw;

	if(empty($__gCacheCategoryRaw)) getCategories($blogid, 'raw'); //To cache category information.
	if($result = MMCache::queryRow($__gCacheCategoryRaw,'name',$name)) {
		if($parentName == false) {
			return $result['id'];
		} else {
			$parent = MMCache::queryRow($__gCacheCategoryRaw,'name',$parentName);
			if($parent['id'] == $result['parent']) return $result['id'];
		}
	}
	return null;
}

function getCategoryIdByLabel($blogid, $label) {
	requireComponent('Needlworks.Cache.PageCache');

	global $__gCacheCategoryRaw;
	if (empty($label))
		return 0;

	if(empty($__gCacheCategoryRaw)) getCategories($blogid, 'raw'); //To cache category information.
	if($result = MMCache::queryRow($__gCacheCategoryRaw,'label',$label))
		return $result['id'];
	else return null;
}

function getCategoryNameById($blogid, $id) {
	requireComponent('Needlworks.Cache.PageCache');

	global $__gCacheCategoryRaw;

	if(empty($__gCacheCategoryRaw)) getCategories($blogid, 'raw'); //To cache category information.
	if($result = MMCache::queryRow($__gCacheCategoryRaw,'id',$id))
		return $result['name'];
	else return _text('전체');
}

function getCategoryBodyIdById($blogid, $id) {
	requireComponent('Needlworks.Cache.PageCache');

	global $__gCacheCategoryRaw;

	if(empty($__gCacheCategoryRaw)) getCategories($blogid, 'raw'); //To cache category information.
	$result = MMCache::queryRow($__gCacheCategoryRaw,'id',$id);
	if (($id === 0) || ($result == '') || ($id === null))
		return 'tt-body-category';
	else return $result['bodyId'];
}

function getEntriesCountByCategory($blogid, $id) {
	requireComponent('Needlworks.Cache.PageCache');

	global $__gCacheCategoryRaw;

	if(empty($__gCacheCategoryRaw)) getCategories($blogid, 'raw'); //To cache category information.
	$result = MMCache::queryRow($__gCacheCategoryRaw,'id',$id);
	if (($id === 0) || ($result == '') || ($id === null)) {
		return 0;
	} else {
		if(doesHaveOwnership() && Acl::check('group.editors')) return $result['entriesInLogin'];
		else return $result['entries'];
	}
}

function getCategoryLabelById($blogid, $id) {
	requireComponent('Needlworks.Cache.PageCache');

	global $__gCacheCategoryRaw;

	if ($id === null)
		return '';

	if(empty($__gCacheCategoryRaw)) getCategories($blogid, 'raw'); //To cache category information.
	if($result = MMCache::queryRow($__gCacheCategoryRaw,'id',$id))
		return $result['label'];
	else return _text('분류 전체보기');
}

function getCategoryLinkById($blogid, $id) {
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

function getCategories($blogid, $format = 'tree') {
	global $database;
	global $__gCacheCategoryTree, $__gCacheCategoryRaw;
	if($format == 'tree' && !empty($__gCacheCategoryTree))
		return $__gCacheCategoryTree;
	else if($format == 'raw' && !empty($__gCacheCategoryRaw))
		return $__gCacheCategoryRaw;
	$rows = Data_IAdapter::queryAllWithCache("SELECT *
			FROM {$database['prefix']}Categories
			WHERE blogid = $blogid
				AND id >= 0
			ORDER BY parent, priority");
	$categories = array();
	if( empty($rows) ) {
		$rows = array();
	}
	if($format == 'raw') {
		foreach ($rows as $category) {
			$categories[$category['id']] = $category;
		}
		$__gCacheCategoryRaw = $categories;
		return $categories;
	}
	foreach ($rows as $category) {
		if ($category['parent'] == null) {
			$category['children'] = array();
			$categories[$category['id']] = $category;
		} else if (isset($categories[$category['parent']])) {
			array_push($categories[$category['parent']]['children'], $category);
		}
	}
	$__gCacheCategoryTree = $categories;
	return $categories;
}

function getCategoryVisibilityList($blogid, $mode = 'private') {
	requireComponent('Needlworks.Cache.PageCache');

	global $__gCacheCategoryVisibilityList, $__gCacheCategoryRaw;

	if(!array_key_exists($mode,$__gCacheCategoryVisibilityList)) {
		switch($mode) {
			case 'public':
				$visibility = 2;
				break;
			case 'private':
			default:
				$visibility = 1;
		}
		if(empty($__gCacheCategoryRaw)) getCategories($blogid, 'raw'); //To cache category information.
		if($list = MMCache::queryColumn($__gCacheCategoryRaw,'visibility',$visibility,'id')) {
			$__gCacheCategoryVisibilityList[$mode] = $list;
		} else {
			$__gCacheCategoryVisibilityList[$mode] = array();
		}
	}
	return $__gCacheCategoryVisibilityList[$mode];
}

function getPrivateCategoryExclusionQuery($blogid) {
	$exclusionList = getCategoryVisibilityList($blogid, 'private');
	if(empty($exclusionList)) return '';
	return '  AND e.category NOT IN ('.implode(',',$exclusionList).')';
}

function getCategoriesSkin() {
	global $service;
	$setting = getSkinSetting(getBlogId());
	$skin = array('name' => "{$setting['skin']}",
			'url'               => $service['path'] . "/skin/tree/{$setting['tree']}",
			'labelLength'       => $setting['labelLengthOnTree'],
			'showValue'         => $setting['showValueOnTree'],
			'itemColor'         => "{$setting['colorOnTree']}",
			'itemBgColor'       => "{$setting['bgColorOnTree']}",
			'activeItemColor'   => "{$setting['activeColorOnTree']}",
			'activeItemBgColor' => "{$setting['activeBgColorOnTree']}", );
	return $skin;
}

function getParentCategoryId($blogid, $id) {
	requireComponent('Needlworks.Cache.PageCache');
	global $__gCacheCategoryRaw;

	if(empty($__gCacheCategoryRaw)) getCategories($blogid, 'raw'); //To cache category information.
	if($result = MMCache::queryRow($__gCacheCategoryRaw,'id',$id))
		return $result['parent'];
	return null;
}

function getChildCategoryId($blogid, $id) {
	requireComponent('Needlworks.Cache.PageCache');
	global $__gCacheCategoryRaw;

	if(empty($__gCacheCategoryRaw)) getCategories($blogid, 'raw'); //To cache category information.
	if($result = MMCache::queryColumn($__gCacheCategoryRaw,'parent',$id,'id'))
		return $result;
	return null;
}

function getNumberChildCategory($id = null) {
	global $database;
	$sql = "SELECT * FROM {$database['prefix']}Categories WHERE blogid = ".getBlogId()." AND parent " . ($id === null ? 'IS NULL' : "= $id");
	//$result = Data_IAdapter::queryRow($sql);
	return Data_IAdapter::queryCell($sql);
}

function getNumberEntryInCategories($id) {
	global $database;
	return Data_IAdapter::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE blogid = ".getBlogId()." AND draft = 0 AND category " . ($id === null ? 'IS NULL' : "= $id"));
}

function addCategory($blogid, $parent, $name, $id = null, $priority = null) {
	global $database;

	if (empty($name))
		return false;
	if (!is_null($parent) && !Validator::id($parent))
		return false;
	if(!is_null($id) && !Validator::isInteger($id,0)) {
		return false;
	}
	if($priority !== null && !Validator::isInteger($priority,0)) {
		return false;
	}

	if (!is_null($parent)) {
		$label = Data_IAdapter::queryCell("SELECT name FROM {$database['prefix']}Categories WHERE blogid = $blogid AND id = $parent");
		if ($label === null)
			return false;
		$label .= '/' . $name;
	} else {
		$parent = 'NULL';
		$label = $name;
	}

	$label = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($label, 255));
	$name = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($name, 127));

	if($parent == 'NULL') {
		$parentStr = 'AND parent is null';
	} else {
		$parentStr = "AND parent = $parent";
	}

	$sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE blogid = $blogid AND name = '$name' $parentStr";

	if (Data_IAdapter::queryCell($sql) > 0)
		return false;

	if(!is_null($priority)) {
		if(Data_IAdapter::queryExistence("SELECT * FROM {$database['prefix']}Categories WHERE blogid = $blogid AND priority = $priority")) {
			return false;
		} else {
			$newPriority = $priority;
		}
	} else {
		$newPriority = Data_IAdapter::queryCell("SELECT MAX(priority) FROM {$database['prefix']}Categories WHERE blogid = $blogid") + 1;
	}

	// Determine ID.
	if(!is_null($id)) {
		$sql = "SELECT * FROM {$database['prefix']}Categories WHERE blogid = $blogid AND id = $id";
		if(Data_IAdapter::queryExistence($sql)) {
			return false;
		} else {
			$newId = $id;
		}
	} else {
		$newId = Data_IAdapter::queryCell("SELECT MAX(id) FROM {$database['prefix']}Categories WHERE blogid = $blogid") + 1;
	}

	$result = Data_IAdapter::query("INSERT INTO {$database['prefix']}Categories (blogid, id, parent, name, priority, entries, entriesInLogin, label, visibility) VALUES ($blogid, $newId, $parent, '$name', $newPriority, 0, 0, '$label', 2)");
	updateEntriesOfCategory($blogid);
	return $result ? true : false;
}

function deleteCategory($blogid, $id) {
	global $database;

	if (!is_numeric($id))
		return false;
	CacheControl::flushCategory($id);
	Data_IAdapter::execute("DELETE FROM {$database['prefix']}Categories WHERE blogid = $blogid AND id = $id");
	updateEntriesOfCategory($blogid);
	return true;
}

function modifyCategory($blogid, $id, $name, $bodyid) {
	global $database;
	requireModel('blog.feed');
	if($id==0) checkRootCategoryExistence($blogid);
	if ((empty($name)) && (empty($bodyid)))
		return false;
	$row = Data_IAdapter::queryRow("SELECT p.name, p.id
		FROM {$database['prefix']}Categories c
		LEFT JOIN {$database['prefix']}Categories p ON c.parent = p.id
		WHERE c.blogid = $blogid AND c.id = $id");
	$label = $row['name'];
//	$parentId = $row['id'];
//	if (!empty($parentId)) {
//		$parentStr = "AND parent = $parentId";
//	} else
//		$parentStr = 'AND parent is null';
	$name = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($name, 127));
	$bodyid = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($bodyid, 20));
	if(Data_IAdapter::queryExistence("SELECT name
		FROM {$database['prefix']}Categories
		WHERE blogid = $blogid AND name = '".$name."' AND bodyId = '".$bodyid."'"))
		return false;
	$label = Data_IAdapter::escapeString(UTF8::lessenAsEncoding(empty($label) ? $name : "$label/$name", 255));
	$sql = "SELECT *
		FROM {$database['prefix']}Categories
		WHERE blogid = $blogid
			AND id = $id";
	// $sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE blogid = $blogid AND name='$name' $parentStr";
	if(Data_IAdapter::queryExistence($sql) == false)
		return false;

	$result = Data_IAdapter::query("UPDATE {$database['prefix']}Categories
		SET name = '$name',
			label = '$label',
			bodyId = '$bodyid'
		WHERE blogid = $blogid
			AND id = $id");
	if ($result)
		clearFeed();
	updateEntriesOfCategory($blogid);
	CacheControl::flushCategory($id);
	return $result ? true : false;
}

function updateEntriesOfCategory($blogid, $id = - 1) {
	global $database;
	$result = Data_IAdapter::queryAll("SELECT * FROM {$database['prefix']}Categories WHERE blogid = $blogid AND parent IS NULL");
	foreach($result as $row) {
		$parent = $row['id'];
		$parentName = UTF8::lessenAsEncoding($row['name'], 127);
		$row['name'] = Data_IAdapter::escapeString($parentName);
		$countParent = Data_IAdapter::queryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 AND visibility > 0 AND category = $parent");
		$countInLoginParent = Data_IAdapter::queryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 AND category = $parent");
		$result2 = Data_IAdapter::queryAll("SELECT * FROM {$database['prefix']}Categories WHERE blogid = $blogid AND parent = $parent");
		foreach ($result2 as $rowChild) {
			$label = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($parentName . '/' . $rowChild['name'], 255));
			$rowChild['name'] = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($rowChild['name'], 127));
			$countChild = Data_IAdapter::queryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 AND visibility > 0 AND category = {$rowChild['id']}");
			$countInLogInChild = Data_IAdapter::queryCell("SELECT COUNT(id) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 AND category = {$rowChild['id']}");
			Data_IAdapter::query("UPDATE {$database['prefix']}Categories SET entries = $countChild, entriesInLogin = $countInLogInChild, `label` = '$label' WHERE blogid = $blogid AND id = {$rowChild['id']}");
			$countParent += $countChild;
			$countInLoginParent += $countInLogInChild;
		}
		Data_IAdapter::query("UPDATE {$database['prefix']}Categories SET entries = $countParent, entriesInLogin = $countInLoginParent, `label` = '{$row['name']}' WHERE blogid = $blogid AND id = $parent");
	}
	if($id >=0) CacheControl::flushCategory($id);
	clearCategoryCache();
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
//	$parentParent = '';
	$myIsHaveChild = '';
	$nextId = '';
//	$nextParentId = '';
	$nextPriority = '';
	$sql = "SELECT
				_parent.id AS parentId,
				_parent.priority AS parentPriority,
				_parent.parent AS parentParent,
				_my.priority AS myPriority,
				_my.parent AS myParent
			FROM {$database['prefix']}Categories AS _my
				LEFT JOIN {$database['prefix']}Categories AS _parent ON _parent.id = _my.parent
			WHERE _my.id = $id AND _my.blogid = $blogid";
	$row = Data_IAdapter::queryRow($sql);
	$myParent = is_null($row['myParent']) ? 'NULL' : $row['myParent'];
	$parentId = is_null($row['parentId']) ? 'NULL' : $row['parentId'];
	$parentPriority = is_null($row['parentPriority']) ? 'NULL' : $row['parentPriority'];
//	$parentParent = is_null($row['parentParent']) ? 'NULL' : $row['parentParent'];
	$myPriority = $row['myPriority'];
	$sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE parent = $myId AND blogid = $blogid";
	$myIsHaveChild = (Data_IAdapter::queryCell($sql) > 0) ? true : false;
	$aux = $parentId == 'NULL' ? 'parent is null' : "parent = $parentId";
	$sql = "SELECT id, parent, priority FROM {$database['prefix']}Categories WHERE $aux AND blogid = $blogid AND id != 0 AND priority $sign $myPriority ORDER BY priority $arrange LIMIT 1";
//	$canMove = (Data_IAdapter::queryCount($sql) > 0) ? true : false;
	$row = Data_IAdapter::queryRow($sql);
	$nextId = is_null($row['id']) ? 'NULL' : $row['id'];
//	$nextParentId = is_null($row['parent']) ? 'NULL' : $row['parent'];
	$nextPriority = is_null($row['priority']) ? 'NULL' : $row['priority'];
	// 이동할 자신이 1 depth 카테고리일 때.
	if ($myParent == 'NULL') {
		// 자신이 2 depth를 가지고 있고, 위치를 바꿀 대상 카테고리가 있는 경우.
		if ($myIsHaveChild && $nextId != 'NULL') {
			$sql = "UPDATE {$database['prefix']}Categories
						SET
							priority = $myPriority
						WHERE
							id = $nextId AND blogid = $blogid";
			Data_IAdapter::query($sql);
			$sql = "UPDATE {$database['prefix']}Categories
						SET
							priority = $nextPriority
						WHERE
							id = $myId AND blogid = $blogid";
			Data_IAdapter::query($sql);
		// 자신이 2 depth를 가지지 않은 1 depth 카테고리이거나, 위치를 바꿀 대상이 없는 경우.
		} else {
			// 위치를 바꿀 대상 카테고리에 같은 이름이 존재하는지 판별.
			$myName = Data_IAdapter::queryCell("SELECT `name` FROM `{$database['prefix']}Categories` WHERE `id` = $myId AND blogid = $blogid");
			$overlapCount = Data_IAdapter::queryCell("SELECT count(*) FROM `{$database['prefix']}Categories` WHERE `name` = '$myName' AND `parent` = $nextId AND blogid = $blogid");
			// 같은 이름이 없으면 이동 시작.
			if ($overlapCount == 0) {
				$sql = "UPDATE {$database['prefix']}Categories
							SET
								parent = $nextId
							WHERE
								id = $myId AND blogid = $blogid";
				Data_IAdapter::query($sql);
				$sql = "SELECT id, priority FROM {$database['prefix']}Categories WHERE parent = $nextId AND blogid = $blogid ORDER BY priority DESC";
				$row = Data_IAdapter::queryRow($sql);
				$nextId = is_null($row['id']) ? 'NULL' : $row['id'];
				$nextPriority = is_null($row['priority']) ? 'NULL' : $row['priority'];
				if ($nextId != 'NULL') {
					$sql = "UPDATE {$database['prefix']}Categories
								SET
									priority = " . max($nextPriority, $myPriority) . "
								WHERE
									id = $nextId AND blogid = $blogid";
					Data_IAdapter::query($sql);
					$sql = "UPDATE {$database['prefix']}Categories
								SET
									priority = " . min($nextPriority, $myPriority) . "
								WHERE
									id = $myId AND blogid = $blogid";
					Data_IAdapter::query($sql);
				}
			// 같은 이름이 있으면.
			} else {
				$sql = "UPDATE {$database['prefix']}Categories
							SET
								priority = $myPriority
							WHERE
								id = $nextId AND blogid = $blogid";
				Data_IAdapter::query($sql);
				$sql = "UPDATE {$database['prefix']}Categories
							SET
								priority = $nextPriority
							WHERE
								id = $myId AND blogid = $blogid";
				Data_IAdapter::query($sql);
			}
		}
	// 이동할 자신이 2 depth일 때.
	} else {
		// 위치를 바꿀 대상이 1 depth이면.
		if ($nextId == 'NULL') {
			$myName = Data_IAdapter::escapeString(Data_IAdapter::queryCell("SELECT `name` FROM `{$database['prefix']}Categories` WHERE `id` = $myId and `blogid` = $blogid"));
			$overlapCount = Data_IAdapter::queryCell("SELECT count(*) FROM `{$database['prefix']}Categories` WHERE `name` = '$myName' AND `parent` IS NULL AND `blogid` = $blogid");
			// 1 depth에 같은 이름이 있으면 2 depth로 직접 이동.
			if ($overlapCount > 0) {
				$sql = "SELECT `id`, `parent`, `priority` FROM `{$database['prefix']}Categories` WHERE `parent` IS NULL AND `blogid` = $blogid AND `priority` $sign $parentPriority ORDER BY `priority` $arrange";
				$result = Data_IAdapter::queryAll($sql);
				foreach($result as $row) {
					$nextId = $row['id'];
//					$nextParentId = $row['parent'];
					$nextPriority = $row['priority'];

					// 위치를 바꿀 대상 카테고리에 같은 이름이 존재하는지 판별.
					$myName = Data_IAdapter::escapeString(Data_IAdapter::queryCell("SELECT `name` FROM `{$database['prefix']}Categories` WHERE `id` = $myId AND `blogid` = $blogid"));
					$overlapCount = Data_IAdapter::queryCell("SELECT count(*) FROM `{$database['prefix']}Categories` WHERE `name` = '$myName' AND `parent` = $nextId AND `blogid` = $blogid");
					// 같은 이름이 없으면 이동 시작.
					if ($overlapCount == 0) {
						$sql = "UPDATE `{$database['prefix']}Categories`
									SET
										`parent` = $nextId
									WHERE
										`id` = $myId AND `blogid` = $blogid";
						Data_IAdapter::query($sql);
							break;
					}
				}
			// 같은 이름이 없으면 1 depth로 이동.
			} else {
				$sql = "UPDATE {$database['prefix']}Categories SET parent = NULL WHERE id = $myId AND blogid = $blogid";
				Data_IAdapter::query($sql);
				$sql = "SELECT id, priority FROM {$database['prefix']}Categories WHERE parent is null AND blogid = $blogid AND priority $sign $parentPriority ORDER BY priority $arrange";
				$row = Data_IAdapter::queryRow($sql);
				$nextId = is_null($row['id']) ? 'NULL' : $row['id'];
				$nextPriority = is_null($row['priority']) ? 'NULL' : $row['priority'];
				if ($nextId == 'NULL') {
					$operator = ($direction == 'up') ? '-' : '+';
					$sql = "UPDATE {$database['prefix']}Categories SET priority = $parentPriority $operator 1 WHERE id = $myId AND blogid = $blogid";
					Data_IAdapter::query($sql);
					return;
				}
				if ($direction == 'up') {
					$aux = "SET priority = priority+1 WHERE priority >= $parentPriority AND blogid = $blogid";
					$aux2 = "SET priority = $parentPriority WHERE id = $myId AND blogid = $blogid";
				} else {
					$aux = "SET priority = priority+1 WHERE priority >= $nextPriority AND blogid = $blogid";
					$aux2 = "SET priority = $nextPriority WHERE id = $myId AND blogid = $blogid";
				}
				$sql = "UPDATE {$database['prefix']}Categories $aux";
				Data_IAdapter::query($sql);
				$sql = "UPDATE {$database['prefix']}Categories $aux2";
				Data_IAdapter::query($sql);
			}
		// 위치를 바꿀 대상이 2 depth이면 위치 교환.
		} else {
			$sql = "UPDATE {$database['prefix']}Categories
						SET
							priority = $myPriority
						WHERE
							id = $nextId AND blogid = $blogid";
			Data_IAdapter::query($sql);
			$sql = "UPDATE {$database['prefix']}Categories
						SET
							priority = $nextPriority
						WHERE
							id = $myId AND blogid = $blogid";
			Data_IAdapter::query($sql);
		}
	}
	updateEntriesOfCategory($blogid);
	CacheControl::flushCategory($id);
}

function checkRootCategoryExistence($blogid) {
	global $database;
	$sql = "SELECT count(*) FROM {$database['prefix']}Categories WHERE blogid = $blogid AND id = 0";
	if(!(Data_IAdapter::queryCell($sql))) {
		$name = _text('전체');
		$result = addCategory($blogid,null,$name,0);
		return $result ? true : false;
	}
	return false;
}

function getCategoryVisibility($blogid, $id) {
	$categories = getCategories($blogid,'raw');
	if( isset($categories[$id]) ) {
		if( isset( $categories[$id]['visibility'] ) ) {
			return $categories[$id]['visibility'];
		}
	}
	return 2;
}

function getParentCategoryVisibility($blogid, $id) {
	if($id == 0) return false;
	$categories = getCategories($blogid,'raw');
	$parentId = $categories[$id]['parent'];
	if(!isset($parentId) || $parentId == NULL) return false;
	$parentVisibility = $categories[$parentId]['visibility'];
	if (empty($parentVisibility))
		return 2;
	else
		return $parentVisibility;
}

function setCategoryVisibility($blogid, $id, $visibility) {
	global $database;
	requireModel('blog.feed');
	if($id == 0) return false;
	$parentVisibility = getParentCategoryVisibility($blogid, $id);
	if ($parentVisibility!==false && $parentVisibility < 2) return false; // return without changing if parent category is set to hidden.
	$result = Data_IAdapter::query("UPDATE {$database['prefix']}Categories
		SET visibility = $visibility
		WHERE blogid = $blogid
			AND id = $id");
	if ($result && $visibility == 1) $result = setChildCategoryVisibility($blogid, $id, $visibility);
	if ($result)
		clearFeed();
	updateEntriesOfCategory($blogid);
	CacheControl::flushCategory($id);
	return $result ? $visibility : false;
}

function setChildCategoryVisibility($blogid, $id, $visibility) {
	global $database;
	if($id == 0) return false;
	$childCategories = Data_IAdapter::queryColumn("SELECT id
		FROM {$database['prefix']}Categories WHERE blogid = $blogid AND parent = $id");
	if($childCategories!=false) {
		foreach($childCategories as $childCategory) {
			$result = Data_IAdapter::query("UPDATE {$database['prefix']}Categories
				SET visibility = $visibility
				WHERE blogid = $blogid AND id = $childCategory");
			if($result == false) return false;
		}
		return $result ? $visibility : false;
	}
	return $visibility;
}

function clearCategoryCache() {
	global $__gCacheCategoryTree, $__gCacheCategoryRaw;
	if(isset($__gCacheCategoryTree))
		$__gCacheCategoryTree = array();
	if(isset($__gCacheCategoryRaw))
		$__gCacheCategoryRaw = array();
}
?>
