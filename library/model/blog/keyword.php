<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getKeywordCount($blogid) {
    $pool = DBModel::getInstance();
    $pool->reset('Entries');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('draft', 'eq', 0);
    if (doesHaveOwnership()) {
        $pool->setQualifier('visibility', 'b', 0);
    }
    $pool->setQualifier('category', 'eq', -1);
    return $pool->getCount('*');
}

function getKeywordNames($blogid) {
    $pool = DBModel::getInstance();
    $pool->reset('Entries');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('draft', 'eq', 0);
    if (doesHaveOwnership()) {
        $pool->setQualifier('visibility', 'b', 0);
    }
    $pool->setQualifier('category', 'eq', -1);
    $pool->setOrder('char_length(title)', 'DESC');
    return $pool->getColumn('title');
}

function getKeywords($blogid) {
    $pool = DBModel::getInstance();
    $pool->reset('Entries');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('draft', 'eq', 0);
    if (doesHaveOwnership()) {
        $pool->setQualifier('visibility', 'b', 0);
    }
    $pool->setQualifier('category', 'eq', -1);
    $pool->setOrder('title', 'ASC');
    return $pool->getAll('*');
}

function getKeywordsWithPaging($blogid, $search, $page, $count) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $aux = '';
    if (($search !== true) && $search) {
        $pool->setQualifierSet(array(
            array("title", "like", $search, true),
            "OR",
            array("content", "like", $search, true)
        ));
    }
    if (!doesHaveOwnership()) {
        $pool->setQualifier("visibility", ">", 0);
    }
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("draft", "eq", 0);
    $pool->setQualifier("category", "eq", -1);
    $pool->setOrder("published", "desc");
    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'));
}

function getKeyword($blogid, $keyword) {
    return getKeylogByTitle($blogid, $keyword);
}

function getKeylogByTitle($blogid, $title) {
    $pool = DBModel::getInstance();
    $pool->reset('Entries');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('draft', 'eq', 0);
    if (doesHaveOwnership()) {
        $pool->setQualifier('visibility', 'b', 0);
    }
    $pool->setQualifier('category', 'eq', -1);
    $pool->setQualifier('title', 'eq', $title, true);
    $pool->setOrder('published', 'DESC');
    return $pool->getRow('*');
}

function getEntriesByKeyword($blogid, $keyword) {
    $pool = DBModel::getInstance();
    $pool->reset('Entries');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('draft', 'eq', 0);
    if (doesHaveOwnership()) {
        $pool->setQualifier('visibility', 'b', 1);
    }
    $pool->setQualifier('category', 'beq', 0);
    $pool->setQualifierSet(
        array('title', 'like', $keyword, true),
        'OR',
        array('content', 'like', $keyword, true));
    $pool->setOrder('published', 'DESC');
    return $pool->getRow('id,userid,title,category,comments,published');
}

class KeywordBinder {
    var $_replaceOnce;
    var $_binded = array();

    function __construct($replaceOnce = true) {
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
    if (empty($keywords)) {
        return $content;
    }

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
    $pattern = '/(?<![a-zA-Z\x80-\xff])(?:' . implode('|', $pattern) . ')/'; // ��ҹ��� ���� �� Ű������ �ܾ� ù�Ӹ� ó��

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

        if (++$i >= count($result)) {
            break;
        }
        if ($result[$i]{0} == '<') {
            // now we have delimeter pattern from $result[$i] to $result[$i+3]
            $tagname = strtolower($result[$i + 1]);
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
