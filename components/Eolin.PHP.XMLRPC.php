<?php
class XMLRPC {
	var $url, $methodName, $params, $result, $fault;
	
	function XMLRPC() {
		$this->_registry = array();
	}
	
	function call() {
		requireComponent('Eolin.PHP.HTTPRequest');
		if (func_num_args() < 1)
			return false;
		$request = new HTTPRequest();
		$request->method = 'POST';
		$request->url = $this->url;
		$request->contentType = 'text/xml';
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
		if (!$request->send()) 
			return false;

		if ($request->getResponseHeader('Content-Type') != 'text/xml') 
			return false;
		$xmls = new XMLStruct();
		$xmls->open($request->responseText);
		if ($xmls->error)
			return false;
			
		if (isset($xmls->struct['methodResponse'][0]['fault'][0]['value']))
			$this->fault = $this->_decodeValue($xmls->struct['methodResponse'][0]['fault'][0]['value'][0]);
		else if (isset($xmls->struct['methodResponse'][0]['params'][0]['param'][0]['value']))
			$this->result = $this->_decodeValue($xmls->struct['methodResponse'][0]['params'][0]['param'][0]['value'][0]);
		else
			return false;
		return true;
	}
	
	function registerMethod($method, $function) {
		$this->_registry[$method] = $function;
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
		if ($param !== null) {
			echo '<param>';
			$this->_encodeValue($param);
			echo '</param>';
		}
		echo '</params></methodResponse>';
	}
	
	function sendFault($code = 0, $string = 'Error') {
		header('Content-Type: text/xml');
		echo '<methodResponse><fault><value><struct><member><name>faultCode</name><value><i4>';
		echo $code;
		echo '</i4></value></member><member><name>faultString</name><value><string>';
		echo $string;
		echo '</string></value></member></struct></value></fault></methodResponse>';
	}
	
	function _encodeValue($value) {
		echo '<value>';
		if (is_int($value)) {
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
			echo htmlspecialchars($value);
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
}

class XMLRPCFault {
	var $code, $string;

	function XMLRPCFault($code = 0, $string = 'Error') {
		$this->code = $code;
		$this->string = $string;
	}
}
?>