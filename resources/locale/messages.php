<?php
if (!defined('ROOT')) {
	header('HTTP/1.1 403 Forbidden');
	header("Connection: close");
	exit;
}

// TODO: generalize for multiple language support e.g. skin language
$setting = Setting::getBlogSettingsGlobal($blogid);
require 'owner/'.$setting['language'].'.php';

echo "__text = {\n";
foreach ($__text as $key => $value) {
	$key = str_replace("\n", "\\n", addslashes($key));
	$value = str_replace("\n", "\\n", addslashes($value));
	echo "\t'$key': '$value',\n";
}
echo <<<EOT
	'': '' // dummy
};

function _t(msg) {
	return __text[msg] || msg;
}
EOT;
?>
