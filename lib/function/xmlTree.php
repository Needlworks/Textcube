<?

class XmlTree {
	var $parser;
	var $tree;
	var $cdata = false;

	function XmlTree() {
	}
 function close() {
		xml_parser_free($this->parser);
		unset($tree);
	}
 function parse($xml, $encoding = false) {
		if ($encoding === false) {
			preg_match('/encoding="([^"]*)"/i', $xml, $matches);
			if (strtoupper($matches[1]) != 'UTF-8')
				$xml = iconvWrapper($matches[1], 'UTF-8', $xml);
		} else {
			if (!isUTF8($xml))
				$xml = iconvWrapper($encoding, 'UTF-8', $xml);
		}
		$this->parser = xml_parser_create();
		xml_set_object($this->parser, $this);
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_element_handler($this->parser, '_openElement', '_closeElement');
		xml_set_character_data_handler($this->parser, '_cdata');
		xml_set_default_handler($this->parser, '_default');
		$this->tree = array();
		xml_parse($this->parser, $xml);
		return (xml_get_error_code($this->parser) == XML_ERROR_NONE ? true : false);
	}
 function getValue($path) {
		if ($path{0} !== '/')
			return;
		$directives = explode('/', $path);
		$cursor = & $this->tree;
		array_shift($directives);
		$directive = array_shift($directives);
		if (!isset($cursor[$directive]))
			return;
		$cursor = & $cursor[$directive];
		$enum = true;
		while (count($directives) > 0) {
			$directive = array_shift($directives);
			if (is_numeric($directive)) {
				if ($enum)
					return;
				if (!isset($cursor[$directive]))
					return;
				$cursor = & $cursor[$directive];
				$enum = true;
			} else {
				if ($enum) {
					if (!isset($cursor[$directive]))
						return;
					$cursor = & $cursor[$directive];
				} else {
					if (!isset($cursor[0][$directive]))
						return;
					$cursor = & $cursor[0][$directive];
				}
				$enum = false;
			}
		}
		if (!$enum) {
			if (!isset($cursor[0]))
				return;
			$cursor = & $cursor[0];
		}
		return (isset($cursor['value']) ? $cursor['value'] : '');
	}
 function getAttribute($path, $name) {
		if ($path{0} !== '/')
			return;
		$directives = explode('/', $path);
		$cursor = & $this->tree;
		array_shift($directives);
		$directive = array_shift($directives);
		if (!isset($cursor[$directive]))
			return;
		$cursor = & $cursor[$directive];
		$enum = true;
		while (count($directives) > 0) {
			$directive = array_shift($directives);
			if (is_numeric($directive)) {
				if ($enum)
					return;
				if (!isset($cursor[$directive]))
					return;
				$cursor = & $cursor[$directive];
				$enum = true;
			} else {
				if ($enum) {
					if (!isset($cursor[$directive]))
						return;
					$cursor = & $cursor[$directive];
				} else {
					if (!isset($cursor[0][$directive]))
						return;
					$cursor = & $cursor[0][$directive];
				}
				$enum = false;
			}
		}
		if (!$enum) {
			if (!isset($cursor[0]))
				return;
			$cursor = & $cursor[0];
		}
		return isset($cursor['attributes'][$name]) ? $cursor['attributes'][$name] : null;
	}
 function _openElement($parser, $name, $attributes) {
		$node = array();
		if (!empty($attributes))
			$node['attributes'] = $attributes;
		array_push($this->tree, $node);
	}
 function _closeElement($parser, $name) {
		$node = array_pop($this->tree);
		$parent = array_pop($this->tree);
		if ($parent !== null) {
			if (!isset($parent[$name]))
				$parent[$name] = array();
			array_push($parent[$name], $node);
			array_push($this->tree, $parent);
		} else
			$this->tree[$name] = $node;
	}
 function _cdata($parser, $data) {
		if (!$this->cdata)
			$data = trim($data);
		if (strlen($data) == 0)
			return;
		$node = array_pop($this->tree);
		if (isset($node['value']))
			$node['value'] .= $data;
		else
			$node['value'] = $data;
		array_push($this->tree, $node);
	}
 function _default($parser, $data) {
		if ($data == '<![CDATA[')
			$this->cdata = true;
		else if (($data == ']]>') && $this->cdata)
			$this->cdata = false;
	}
}
?>