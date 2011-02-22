<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
	function replace_num_entity($ord)
	{
		$ord = $ord[1];
		if (preg_match('/^x([0-9a-f]+)$/i', $ord, $match))
		{
			$ord = hexdec($match[1]);
		}
		else
		{
			$ord = intval($ord);
		}

		$no_bytes = 0;
		$byte = array();
		if ($ord < 128)
		{
			return chr($ord);
		}
		elseif ($ord < 2048)
		{
			$no_bytes = 2;
		}
		elseif ($ord < 65536)
		{
			$no_bytes = 3;
		}
		elseif ($ord < 1114112)
		{
			$no_bytes = 4;
		}
		else
		{
			return;
		}
		switch($no_bytes)
		{
			case 2:
			{
				$prefix = array(31, 192);
				break;
			}
			case 3:
			{
				$prefix = array(15, 224);
				break;
			}
			case 4:
			{
				$prefix = array(7, 240);
			}
		}
		for ($i = 0; $i < $no_bytes; $i++)
		{
			$byte[$no_bytes - $i - 1] = (($ord & (63 * pow(2, 6 * $i))) / pow(2, 6 * $i)) & 63 | 128;
		}
		$byte[0] = ($byte[0] & $prefix[0]) | $prefix[1];
		$ret = '';
		for ($i = 0; $i < $no_bytes; $i++)
		{
			$ret .= chr($byte[$i]);
		}
		return $ret;
	}
		
class XMLRPC {
	var $url, $async = false, $methodName, $params, $result, $fault;
	
	var $useOldXmlRPC = false; // for 2003-before-version

	function XMLRPC() {
		$this->_registry = array();
	}
	
	function call() {
		if (func_num_args() < 1)
			return false;
		$request = new HTTPRequest();
		$request->method = 'POST';
		$request->url = $this->url;
		$request->contentType = 'text/xml';
		$request->async = $this->async;
		ob_start();
		echo '<?xml version="1.0" encoding="utf-8"?><methodCall><methodName>' . func_get_arg(0) . '</methodName><params>';
		for ($i = 1; $i < func_num_args(); $i++) {
			echo '<param>';
			echo $this->_encodeValue(func_get_arg($i));
			echo '</param>';
		}
		echo '</params></methodCall>';
		$request->content = ob_get_contents();
		ob_end_clean();
		if (!$request->send()) {
			return false;
		}
		if ($this->async) {
			return true;
		}
		if ((!is_null($request->getResponseHeader('Content-Type'))) && ($request->getResponseHeader('Content-Type') != 'text/xml'))  {
			return false;
		}
		$xmls = new XMLStruct();
		$request->responseText = preg_replace_callback('/&#([0-9a-fx]+);/mi', 'replace_num_entity', $request->responseText);
		$xmls->open($request->responseText);
		if ($xmls->error) {
			return false;
		}
			
		if (isset($xmls->struct['methodResponse'][0]['fault'][0]['value']))
			$this->fault = $this->_decodeValue($xmls->struct['methodResponse'][0]['fault'][0]['value'][0]);
		else if (isset($xmls->struct['methodResponse'][0]['params'][0]['param'][0]['value']))
			$this->result = $this->_decodeValue($xmls->struct['methodResponse'][0]['params'][0]['param'][0]['value'][0]);
		else
			return false;
		return true;
	}
	
	function registerMethod($method, $function) {
		if( function_exists( $function ) ) {
		$this->_registry[$method] = $function;
	}
	}
	
	function receive($xml = null) {
		if (empty($xml)) {
			if (empty($_SERVER['CONTENT_TYPE']) || empty($GLOBALS['HTTP_RAW_POST_DATA']) || ($_SERVER['CONTENT_TYPE'] != 'text/xml'))
				return false;
			$xmls = new XMLStruct();
			if ($xmls->open($GLOBALS['HTTP_RAW_POST_DATA']) == false) {
				return false;
			}
		} else {
			$xmls = new XMLStruct();
			if ($xmls->open($xml) == false) {
				return false;
			}
		}
		if ($xmls->error) {
			return false;
		}
		if (!isset($xmls->struct['methodCall'][0]['methodName'][0]['.value'])) {
			return false;
		}
		$this->methodName = $xmls->struct['methodCall'][0]['methodName'][0]['.value'];
		$params = $xmls->selectNodes('/methodCall/params/param');
		$this->params = array();
		for ($i = 0; $i < count($params); $i++) {
			if (!isset($params[$i]['value']))
				return false;
			array_push($this->params, $this->_decodeValue($params[$i]['value'][0]));
			
		}
		
		if (isset($this->_registry[$this->methodName])) {
			$result = call_user_func_array($this->_registry[$this->methodName], $this->params);
			if (is_a($result, 'XMLRPCFault'))
				$this->sendFault($result->code, $result->string);
			else
				$this->sendResponse($result);
		} else {
			$this->sendFault(1, 'Method was not found');
		}
		return true;
	}
	
	function sendResponse($param = null) {
		header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="utf-8"?><methodResponse><params>';
		if (!is_null($param)) {
			echo '<param>';
			$this->_encodeValue($param);
			echo '</param>';
		}
		echo '</params></methodResponse>';
	}
	
	function sendFault($code = 0, $string = 'Error') {
		header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="utf-8"?>';
		echo '<methodResponse><fault><value><struct><member><name>faultCode</name><value><i4>';
		echo $code;
		echo '</i4></value></member><member><name>faultString</name><value><string>';
		echo $string;
		echo '</string></value></member></struct></value></fault></methodResponse>';
	}
	
	function _encodeValue($value) {
		echo '<value>';
		if (is_a($value, 'XMLCustomType')) {
			echo "<{$value->name}>";
			echo $value->value;
			echo "</{$value->name}>";
		} else if (is_int($value)) {
			echo '<i4>';
			echo $value;
			echo '</i4>';
		} else if (is_double($value)) {
			echo '<double>';
			echo $value;
			echo '</double>';
		} else if (is_bool($value)) {
			echo '<boolean>';
			echo ($value ? '1' : '0');
			echo '</boolean>';
		} else if (is_array($value)) {
			for ($i = 0; $i < count($value); $i++) {
				if (!isset($value[$i]))
					break;
			}
			if ($i < count($value)) {
				echo '<struct>';
				foreach ($value as $name => $data) {
					echo '<member><name>';
					echo $name;
					echo '</name>';
					$this->_encodeValue($data);
					echo '</member>';
				}
				echo '</struct>';
			} else {
				echo '<array><data>';
				for ($i = 0; $i < count($value); $i++)
					$this->_encodeValue($value[$i]);
				echo '</data></array>';
			}
		} else if ((strlen($value) == 17) && ($value{8} == 'T') && ($value{11} == ':') && ($value{14} == ':')) {
			echo '<dateTime.iso8601>';
			echo $value;
			echo '</dateTime.iso8601>';
		} else {
			echo '<string>';
			if ($this->useOldXmlRPC == true) {
				echo XMLRPC::encodingStringEx($value);
			} else {
			echo htmlspecialchars($value);
			}
			echo '</string>';
		}
		echo '</value>';
	}
	
	function _decodeValue(&$value) {
		if (isset($value[0])) {
			return null;
		}
		list($type) = array_keys($value);
		switch ($type) {
			case '.value':
				return $value['.value'];
			case 'i4':
			case 'int':
				return intval($value[$type][0]['.value']);
			case 'boolean':
				return ($value[$type][0]['.value'] == '1');
			case 'string':
			case 'dateTime.iso8601':
				return $value[$type][0]['.value'];
			case 'double':
				return doubleval($value[$type][0]['.value']);
			case 'base64':
				return base64_decode($value[$type][0]['.value']);
			case 'array':
				if (!isset($value['array'][0]['data'][0]['value']))
					return null;
				if (isset($value['array'][0]['data'][0]['value'][0])) {
					$array = array();
					for ($i = 0; $i < count($value['array'][0]['data'][0]['value']); $i++)
						array_push($array, $this->_decodeValue($value['array'][0]['data'][0]['value'][$i]));
					return $array;
				} else {
					return array($this->_decodeValue($value['array'][0]['data'][0]['value']));
				}
			case 'struct':
				if (!isset($value['struct'][0]['member']))
					return null;
				if (isset($value['struct'][0]['member'][0])) {
					$struct = array();
					for ($i = 0; $i < count($value['struct'][0]['member']); $i++) {
						if (!isset($value['struct'][0]['member'][$i]['name'][0]['.value']) || !isset($value['struct'][0]['member'][$i]['value']))
							return null;
						$struct[$value['struct'][0]['member'][$i]['name'][0]['.value']] = $this->_decodeValue($value['struct'][0]['member'][$i]['value'][0]);
					}
					return $struct;
				} else {
					if (!isset($value['struct'][0]['member'][0]['name'][0]['.value']) || !isset($value['struct'][0]['member'][0]['value']))
						return null;
					return array($value['struct'][0]['member'][$i]['name'][0]['.value'] => $this->_decodeValue($value['struct'][0]['member'][$i]['value'][0]));
				}
		}
		return null;
	}
	
 	function encodingStringEx($text)
    {
		$l = strlen($text);
		$retString = '';
        // ### TODO: Use a buffer rather than going character by
        // ### character to scale better for large text sizes.
        //char[] buf = new char[32];
        for ($i = 0; $i < $l; $i++)
        {
            $c = $text{$i};
            switch ($c)
            {
            case '\t':
            case '\n':
                $retString .= $c;
                break;
            case '\r':
                // Avoid normalization of CR to LF.                
                $retString .= "&#" . ord($c) . ';';
                break;
            case '<':
                $retString .= '&lt;';
                break;
            case '>':
                $retString .= '&gt;';
                break;
            case '&':
                $retString .= '&amp;';
                break;
            default:
                // Though the XML spec requires XML parsers to support
                // Unicode, not all such code points are valid in XML
                // documents.  Additionally, previous to 2003-06-30
                // the XML-RPC spec only allowed ASCII data (in
                // <string> elements).  For interoperability with
                // clients rigidly conforming to the pre-2003 version
                // of the XML-RPC spec, we entity encode characters
                // outside of the valid range for ASCII, too.

                // Replace the code point with a character reference.
				$high = ord($text{$i});
				$corrected = '';
				if ($high < 0x20) { // Special Characters.
					$corrected = '?';
				} else if ($high < 0x80) { // 1byte.
					$corrected = $text{$i};
				} else if ($high <= 0xC1) {
					$corrected = '?';
				} else if ($high < 0xE0) { // 2byte.
					if (($i + 1 >= $l) || (($text{$i + 1} & "\xC0") != "\x80"))
						$corrected = '?';
					else
						$corrected = '&#' . ((ord($text{$i}) & 0x1f) * 0x40 + (ord($text{$i + 1}) & 0x3f)) . ';'; 
					$i += 1;
				} else if ($high < 0xF0) { // 3byte.
					if (($i + 2 >= $l) || (($text{$i + 1} & "\xC0") != "\x80") || (($text{$i + 2} & "\xC0") != "\x80"))
						$corrected = '?';
					else
						$corrected = '&#' . (((ord($text{$i}) & 0x0f) * 0x40 + (ord($text{$i + 1})& 0x3f))  * 0x40 + (ord($text{$i + 2}) & 0x3f)) . ';'; 
					$i += 2;
				} else if ($high < 0xF5) { // 4byte.
					if (($i + 3 >= $l) || (($text{$i + 1} & "\xC0") != "\x80") || (($text{$i + 2} & "\xC0") != "\x80") || (($text{$i + 3} & "\xC0") != "\x80"))
						$corrected = '?';
					else
						$corrected = '&#' . ((((ord($text{$i}) & 0x07) * 0x40 + (ord($text{$i + 1}) & 0x3f)) * 0x40 + (ord($text{$i + 2}) & 0x3f ) ) * 0x40 + (ord($text{$i + 3}) & 0x3f)) . ';'; 
					$i += 3;
				} else { // F5~FF is invalid by RFC3629.
					$corrected = '?';
				}
                
                $retString .= $corrected;
            }
        }
        
        return $retString;
    }   
}

class XMLRPCFault {
	var $code, $string;

	function XMLRPCFault($code = 0, $string = 'Error') {
		$this->code = $code;
		$this->string = $string;
	}
}

class XMLCustomType {
	var $value, $name;
	
	function XMLCustomType($varString, $varName) {
		$this->name = $varName;
		$this->value = $varString;
	}
}
?>
