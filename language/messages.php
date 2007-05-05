<?php
define('ROOT', '..');
require ROOT . '/lib/includeForBlog.php';

// TODO: generalize for multiple language support e.g. skin language
$setting = getBlogSetting($owner);
require $setting['language'].'.php';

header('Content-Type: text/javascript');
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
