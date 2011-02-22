<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getKeywordCount($blogid) {
	global $database;
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	return POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 $visibility AND category = -1");
}

function getKeywordNames($blogid) {
	global $database;
	$names = array();
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	$names = POD::queryColumn("SELECT title FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 $visibility AND category = -1 ORDER BY char_length(title) DESC");
	return $names;
}

function getKeywords($blogid) {
	global $database;
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	return POD::queryAll("SELECT * 
		FROM {$database['prefix']}Entries 
		WHERE blogid = $blogid 
			AND draft = 0 $visibility 
			AND category = -1 
		ORDER BY title ASC");
}

function getKeywordsWithPaging($blogid, $search, $page, $count) {
	global $database, $folderURL, $suri;
	$aux = '';
	if (($search !== true) && $search) {
		$search = POD::escapeString($search);
		$aux = "AND (title LIKE '%$search%' OR content LIKE '%$search%')";
	}

	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	$sql = "SELECT * 
		FROM {$database['prefix']}Entries 
		WHERE blogid = $blogid 
			AND draft = 0 $visibility 
			AND category = -1 $aux 
		ORDER BY published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getKeyword($blogid, $keyword) {	
	return getKeylogByTitle($blogid, $keyword);
}

function getKeylogByTitle($blogid, $title) {	
	global $database;
	$title = POD::escapeString($title);
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	return POD::queryRow("SELECT * 
			FROM {$database['prefix']}Entries 
			WHERE blogid = $blogid 
				AND draft = 0 $visibility 
				AND category = -1 
				AND title = '$title' 
			ORDER BY published DESC");
}

function getEntriesByKeyword($blogid, $keyword) {	
	global $database;
	$keyword = POD::escapeString($keyword);
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 1';
	return POD::queryAll("SELECT id, userid, title, category, comments, published 
			FROM {$database['prefix']}Entries 
			WHERE blogid = $blogid 
				AND draft = 0 $visibility 
				AND category >= 0 
				AND (title LIKE '%$keyword%' OR content LIKE '%$keyword%')
			ORDER BY published DESC");
}

class KeywordBinder {
	var $_replaceOnce;
	var $_binded = array();

	function KeywordBinder($replaceOnce = true) {
		$this->_replaceOnce = $replaceOnce;
	}

	function replace($matches) {
		$keyword = $matches[0];
		if (!$this->_replaceOnce || !array_key_exists($keyword, $this->_binded)) {
			$this->_binded[$keyword] = null;
			$keyword = fireEvent('BindKeyword', $keyword);
		}
		return $keyword;
	}
}

function bindKeywords($keywords, $content) {
	if(empty($keywords)) return $content;

	// split all HTML/TTML tags and CDATAs
	$result = preg_split('@(
		# <ns:elem or </ns:elem
		</?([A-Za-z0-9-:]+)
		# whitespaces preceding attributes
		(?:\s+
			(?:
				# quotations (e.g. ="blah" or =`blah`)
				=\s*([\'"`]).*?\3
			|
				# =nospacehere or raw character (e.g. ! or =blah)
				[^>]+
			)*
		)?
		# end of element
		>
		# redundant closure need to keep num of capturing patterns to 4
		()
	|
		# TTML pattern
		\[\#\#_.*?_\#\#]
	)@x', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

	$pattern = array();
	foreach ($keywords as $keyword)
		$pattern[] = preg_quote($keyword, '/');
	$pattern = '/(?<![a-zA-Z\x80-\xff])(?:'.implode('|',$pattern).')/'; // ��ҹ��� ���� �� Ű������ �ܾ� ù�Ӹ� ó��

	// list of unbindable & (always) singleton elements
	$unbindables = array('a', 'object', 'applet', 'select', 'option', 'optgroup', 'textarea',
		'button', 'isindex', 'title', 'meta', 'base', 'link', 'style', 'head', 'script', 'embed',
		'address', 'pre', 'param');
	$singletons = array('br', 'hr', 'img', 'input');

	$stack = array(); // outer element first, inner element last
	$buf = '';
	$i = 0;
	$bindable = true;
	$binder = new KeywordBinder();
	while (true) {
		if ($bindable) {
			$buf .= preg_replace_callback($pattern, array($binder, 'replace'), $result[$i]);
		} else {
			$buf .= $result[$i];
		}

		if (++$i >= count($result)) break;
		if ($result[$i]{0} == '<') {
			// now we have delimeter pattern from $result[$i] to $result[$i+3]
			$tagname = strtolower($result[$i+1]);
			if ($result[$i]{1} == '/') {
				// closing tag
				$index = array_search($tagname, $stack);
				if ($index === false) {
					// if there is no opening tag
					//$stack = array();
				} else {
					// if there is any opening tag, close it and pops them from the stack
					array_splice($stack, 0, $index + 1);
					$bindable = (count(array_intersect($stack, $unbindables)) > 0 ? false : true);
				}
			} else {
				// opening tag or empty element (singleton) tag
				// note: empty element tag always endswith '/>', without whitespace between '/' and '>' (XML spec 3.1)
				if (substr($result[$i], -2) != '/>' && !in_array($tagname, $singletons)) {
					// ... is not singleton tag.
					array_unshift($stack, $tagname);
					$bindable = ($bindable && !in_array($tagname, $unbindables));
				}
			}
			$buf .= $result[$i];
			$i += 4;
		} else {
			// TTML pattern
			$buf .= $result[$i++];
		}
	}

	return $buf;
}
?>
