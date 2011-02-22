<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

class XMLTree {
	var $tree, $error;
	
	function XMLTree($xml = null, $encoding = null) {
		if (!is_null($xml))
			$this->open($xml, $encoding);
	}
	
	function open($xml, $encoding = null) {
		if (!empty($encoding) && (strtolower($encoding) != 'utf-8') && !UTF8::validate($xml)) {
			if (preg_match('/^<\?xml[^<]*\s+encoding=["\']?([\w-]+)["\']?/', $xml, $matches)) {
				$encoding = $matches[1];
				$xml = preg_replace('/^(<\?xml[^<]*\s+encoding=)["\']?[\w-]+["\']?/', '$1"utf-8"', $xml, 1);
			}
			if (strcasecmp($encoding, 'utf-8')) {
				$xml = UTF8::bring($xml, $encoding);
				if (is_null($xml)) {
					$this->error = XML_ERROR_UNKNOWN_ENCODING;
					return false;
				}
			}
		} else {
			if (substr($xml, 0, 3) == "\xEF\xBB\xBF")
				$xml = substr($xml, 3);
		}
		$p = xml_parser_create();
		xml_set_object($p, $this);
		xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
		xml_set_element_handler($p, 'o', 'c');
		xml_set_character_data_handler($p, 'd');
		xml_set_default_handler($p, 'x');
		$this->tree = array('children' => array());
		$this->_cursor = &$this->tree;
		$this->_cdata = false;
		xml_parse($p, $xml);
		unset($this->_cursor);
		unset($this->_cdata);
		$this->error = xml_get_error_code($p);
		xml_parser_free($p);
		return ($this->error == XML_ERROR_NONE);
	}
	
	function & selectNode($path) {
		$p = explode('/', $path);
		if (array_shift($p) != '') {
			$ret = null;
			return $ret;
		}
		$c = &$this->tree;
		while ($d = array_shift($p)) {
			$o = 0;
			if ($d{strlen($d) - 1} == ']') {
				@list($d, $o) = split('\[', $d, 2);
				if ($o === null) {
					$ret = null;
					return $ret;
				}
				$o = substr($o, 0, strlen($o) - 1);
				if (!is_numeric($o)) {
					$ret = null;
					return $ret;
				}
			}
			for ($i = 0; $i < count($c["children"]); $i++) {
				if (isset($c['children'][$i]['name']) && ($c['children'][$i]['name'] == $d)) {
					if ($o == 0) {
						$c = &$c['children'][$i];
						$i = true;
						break;
					}
					$o--;
				}
			}
			if ($i !== true) {
				$ret = null;
				return $ret;
			}
				
		}
		return $c;
	}
	
	function doesExist($path) {
		return (!is_null($this->selectNode($path)));
	}
	
	function getAttribute($path, $name, $default = null) {
		$n = &$this->selectNode($path);
		if ((!is_null($n)) && isset($n['attributes'][$name]))
			return $n['attributes'][$name];
		else
			return $default;
	}

	function getValue($path) {
		$n = &$this->selectNode($path);
		if (is_null($n))
			return null;
		switch (count($n['children'])) {
			case 0:
				return '';
			case 1:
				if (is_string($n['children'][0]))
					return $n['children'][0];
				else
					return null;
			default:
				return null;
		}
	}
	
	function getText($path, $recursively = true) {
		$n = &$this->selectNode($path);
		if (is_null($n))
			return null;
		ob_start();
		$this->_getText($n, $recursively);
		$t = ob_get_contents();
		ob_end_clean();
		return $t;
	}

	function getChildCount($path) {
		$n = &$this->selectNode($path);
		if (is_null($n))
			return null;
		return count($n['children']);
	}

/// Private functions
/// PHP4 does not support private methods. Thus these methods are access-free.
/// We DO NOT recommend to use these methods using direct-access

	/// Recursively attach XML Documentation object
	function leap(&$cursor) {
		if(!is_array($cursor)) { // terminal.
			$this->_xmlcontent .= $cursor;
			return;
		} else {	// Non-terminal
			$this->_xmlcontent .= '<'.$cursor['name'];
			if(isset($cursor['attributes'])) {
				foreach($cursor['attributes'] as $type => $value) {
					$this->_xmlcontent .= ' '.$type.'="'.$value.'"';
				}
			}
			$this->_xmlcontent .= '>';
			if(isset($cursor['children'])) {
				if(!is_array($cursor['children'])) {
					$this->_xmlcontent .= $cursor['children'];
				} else {
					for($i = 0; $i < count($cursor['children']); $i++) {
						$this->leap(&$cursor['children'][$i]);
					}
				}				
			}
			$this->_xmlcontent .= '</'.$cursor['name'].'>';
		}
	}
	/// Generate XML contents based on current tree
	function generate() {
		$this->_cursor = &$this->tree;
		$this->_xmlcontent = '<?xml version="1.0" encoding="utf-8"?>';
		if(isset($this->_cursor['children'])) {
			for($i = 0; $i < count($this->_cursor['children']); $i++) {
				$this->leap(&$this->_cursor['children'][$i]);
			}
		}
	}
		
	/// Set the value to specific path.
	function setValue($path, $content, $type = null) {
		if (!$this->doesExist($path)) {
			$this->createNode($path);
		}
		$n = &$this->selectNode($path);
		array_push($n['children'], $content);
		if(!is_null($type)) $this->addAttribute($path, 'type', $type);
	}
	
	/// Create node to documentation tree
	function createNode($path) {
		$this->_cursor = &$this->tree;
		$branch = explode('/',ltrim($path,'/'));
		foreach($branch as &$fork) {
			$growth = true;
			if(isset($this->_cursor['children'])) {
				for($count = 0; $count < count($this->_cursor['children']); $count++) {
					if(isset($this->_cursor['children'][$count]['name']) && $this->_cursor['children'][$count]['name'] == $fork) {
						$growth = false;
						break;
					}
				}
			} else {
				$this->_cursor['children'] = array();
			}
			if($growth) {
				array_push($this->_cursor['children'], array('name' => $fork, 'children' => array()));
				$this->_cursor = &$this->_cursor['children'][count($this->_cursor['children']) - 1];
			} else {
				$this->_cursor = &$this->_cursor['children'][$count];
			}
		}
		$this->_cursor = &$this->tree;
	}
	
	/// Adds attribute to path.
	function addAttribute($path, $name, $value) {
		$n = &$this->selectNode($path);
		if(!isset($n['attributes'])) $n['attributes'] = array();
		$n['attributes'][$name] = $value;
	}
	
	function o($p, $n, $a) {
		if (empty($a))
			array_push($this->_cursor['children'], array('name' => $n, 'children' => array(), '_' => &$this->_cursor));
		else
			array_push($this->_cursor['children'], array('name' => $n, 'attributes' => $a, 'children' => array(), '_' => &$this->_cursor));
		$this->_cursor = &$this->_cursor['children'][count($this->_cursor['children']) - 1];
	}

	function c($p) {
		$c = &$this->_cursor;
		$this->_cursor = &$this->_cursor['_'];
		unset($c['_']);
	}
	
	function d($p, $d) {
		if (!$this->_cdata)
			$d = trim($d);
		if (strlen($d) == 0)
			return;
		array_push($this->_cursor['children'], $d);
	}

	function _getText(&$n, $r) {
		for ($i = 0; $i < count($n['children']); $i++) {
			if (is_string($n['children'][$i])) {
				if (ob_get_length() > 0)
					print ' ';
				print $n['children'][$i];
			}
			else if ($r)
				$this->_getText($n['children'][$i], $r);
		}
	}
	
	function x($p, $d) {
		if ($d == '<![CDATA[')
			$this->_cdata = true;
		else if (($d == ']]>') && $this->_cdata)
			$this->_cdata = false;
	}
}
?>
