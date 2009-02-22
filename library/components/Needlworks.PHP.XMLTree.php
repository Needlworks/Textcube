<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

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
		static $null_node = null;
		$p = explode('/', $path);
		if (array_shift($p) != '')
			return $null_node;
		$c = &$this->tree;
		
		while ($d = array_shift($p)) {
			$o = 0;
			if ($d{strlen($d) - 1} == ']') {
				@list($d, $o) = explode('[', $d, 2);
				if (is_null($o))
					return null;
				$o = substr($o, 0, strlen($o) - 1);
				if (!is_numeric($o))
					return null;
			}
			for ($i = 0; $i < count($c); $i++) {
				if (isset($c['children'][$i]['name']) && ($c['children'][$i]['name'] == $d)) {
					if ($o <= 1) {
						$c = &$c['children'][$i];
						$i = true;
						break;
					}
					$o--;
				}
			}
			if ($i !== true)
				return $null_node;
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
