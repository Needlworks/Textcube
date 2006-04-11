<?
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
		$xmls = new XMLStruct($request->responseText);
		if ($xmls->error)
			return false;
		if (isset($xmls->struct['methodResponse']['fault']['value']))
			$this->fault = $this->_decodeValue($xmls->struct['methodResponse']['fault']['value']);
		else if (isset($xmls->struct['methodResponse']['params']['param']['value']))
			$this->result = $this->_decodeValue($xmls->struct['methodResponse']['params']['param']['value']);
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
			$xmls = new XMLStruct($GLOBALS['HTTP_RAW_POST_DATA']);
		} else
			$xmls = new XMLStruct($xml);
		if ($xmls->error)
			return false;
		if (!isset($xmls->struct['methodCall']['methodName']['.value']))
			return false;
		$this->methodName = $xmls->struct['methodCall']['methodName']['.value'];
		$params = $xmls->selectNodes('/methodCall/params/param');
		$this->params = array();
		for ($i = 0; $i < count($params); $i++) {
			if (!isset($params[$i]['value']))
				return false;
			array_push($this->params, $this->_decodeValue($params[$i]['value']));
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
			echo htmlspecialchars($value);
		}
		echo '</value>';
	}
	
	function _decodeValue(&$value) {
		if (isset($value[0]))
			return null;
		list($type) = array_keys($value);
		switch ($type) {
			case '.value':
				return $value['.value'];
			case 'i4':
			case 'int':
				return intval($value[$type]['.value']);
			case 'boolean':
				return ($value[$type]['.value'] == '1');
			case 'string':
			case 'dateTime.iso8601':
				return $value[$type]['.value'];
			case 'double':
				return doubleval($value[$type]['.value']);
			case 'base64':
				return base64_decode($value[$type]['.value']);
			case 'array':
				if (!isset($value['array']['data']['value']))
					return null;
				if (isset($value['array']['data']['value'][0])) {
					$array = array();
					for ($i = 0; $i < count($value['array']['data']['value']); $i++)
						array_push($array, $this->_decodeValue($value['array']['data']['value'][$i]));
					return $array;
				} else {
					return array($this->_decodeValue($value['array']['data']['value']));
				}
			case 'struct':
				if (!isset($value['struct']['member']))
					return null;
				if (isset($value['struct']['member'][0])) {
					$struct = array();
					for ($i = 0; $i < count($value['struct']['member']); $i++) {
						if (!isset($value['struct']['member'][$i]['name']['.value']) || !isset($value['struct']['member'][$i]['value']))
							return null;
						$struct[$value['struct']['member'][$i]['name']['.value']] = $this->_decodeValue($value['struct']['member'][$i]['value']);
					}
					return $struct;
				} else {
					if (!isset($value['struct']['member']['name']['.value']) || !isset($value['struct']['member']['value']))
						return null;
					return array($value['struct']['member'][$i]['name']['.value'] => $this->_decodeValue($value['struct']['member'][$i]['value']));
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