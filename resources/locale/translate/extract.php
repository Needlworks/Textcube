<?php
/***
	Textcube language file generator
	================================
	0.1
	Jeongkyu Shin <inureyes@gmail.com>

	This CLI program generates language resource file for textcube.

	NOTE: _t, _f         uses administration panel locale setting.
	      _text, _textf  uses blog local setting.
*/

define('CRLF', "\r\n");
define('ROOT',dirname(__FILE__).'/../../..');

$lang = "ja";
$interfaceType = "owner";
$addLocation = false;
$interface_location = array(
		"owner" => array(ROOT . "/interface/owner",ROOT . "/interface/common/owner",ROOT . "/library/model"),
		"blog" => array(ROOT . "/interface/blog", ROOT . "/interface/common/blog",ROOT . "/library/model"),
		"control" => array(ROOT . "/interface/control",ROOT . "/interface/common/control", ROOT . "/library/model"),
		"setup" => array(ROOT . "/setup.php"),
		"checkup" => array(ROOT . "/interface/blog/checkup.php"),
		"all" => array(ROOT . "/interface", ROOT. "/library", ROOT. "/resources")
);
$interface_exception = array(
	"owner" => array(ROOT . "/interface/blog/checkup.php"),
	"blog"=>array(ROOT."/library/model/common/reader.php"),
	"control"=>array(),
	"setup"=>array(),
	"checkup"=>array()
);

$resource_condition = array(
	"owner" => '[tf]|text|textf',
	"blog" => 'text|textf',
	"checkup" => '[tf]',
	"control" => '[tf]',
	"setup" => '[tf]'
);
/*
$languageTable = array(
	"ko" => "한국어",
	"en" => "English"
);
*/
$NEW__text = array();
$NEW__text_location = array();
$directories = $interface_location[$interfaceType];
while (!empty($directories)) {
	$directory = array_shift($directories);
	$dir = dir($directory);
	while ($entry = $dir->read()) {
		if (substr($entry, 0, 1) == '.')
			continue;
		if (is_dir("$directory/$entry")) {
			if (($entry != 'language') && ($entry != 'test'))
				array_push($directories, "$directory/$entry");
		} else if ((substr($entry, strlen($entry) - 4, 4) == '.php') && is_file("$directory/$entry") && !in_array("$directory/$entry",$interface_exception[$interfaceType])) {
			$contents = file_get_contents("$directory/$entry");
			if (preg_match_all('/_('.$resource_condition[$interfaceType].')\((\'([^\']|\'(?<=\\\\))+\')/', $contents, $matches)) {
				foreach ($matches[2] as $text) {
					$NEW__text[$text] = $text;
					$NEW__text__location[$text] = "$directory/$entry";
				}
			}
			if (preg_match_all('/_('.$resource_condition[$interfaceType].')\(("([^"]|"(?<=\\\\))+")/', $contents, $matches)) {
				foreach ($matches[2] as $text) {
					$NEW__text[$text] = $text;
					$NEW__text__location[$text] = "$directory/$entry";
				}
			}
		}
	}
	$dir->close();
}
sort($NEW__text);
$output = '';
if (!empty($lang)) {
	$head = file_get_contents(ROOT . "/resources/locale/description/" . $lang  . ".php");
	if ($lang != 'ko') {
		require ROOT . "/resources/locale/" . $interfaceType . "/en.php"; // Fill blanks with English locale.
		$__text_english_locale = $__text;
	}
	require ROOT . "/resources/locale/" . $interfaceType . "/" . $lang  . ".php";
	$counter = array('translated'=>0,'left'=>0);
	$output .= $head;
	//echo '<?php', (array_key_exists($lang, $languageTable) ? ' // '.$languageTable[$lang]: ''), CRLF;
	foreach ($NEW__text as $text) {
		eval('$index = ' . $text . ';');
		if (!empty($__text[$index]) && ($__text[$index] != $index)) {
			if (array_key_exists($index,$__text_english_locale) && $__text_english_locale[$index] == $__text[$index]) {  // It is derived from English locale
				$markup_as_left = ' // From English locale. ';
				$counter['left']++;
			} else {
				$markup_as_left = '';
				$counter['translated']++;
			}
			if (strpos($index, "\n") !== false) {
				$output .= '$__text['. $text. '] = "'. $__text[$index]. '";'. $markup_as_left.($addLocation ? ' // '.$NEW__text__location[$text] : ''). CRLF;
			} else {
				$output .= '$__text['. $text. '] = \''. $__text[$index]. '\';'. $markup_as_left.($addLocation ? ' // '.$NEW__text__location[$text] : ''). CRLF;
			}
		} else {
			$output .= '//$__text['. $text. '] = '. $text{0}. $text{0}. ';'. ($addLocation ? ' // '.$NEW__text__location[$text] : ''). CRLF;
			$counter['left']++;
		}
	}
	$output .= '// '.array_sum(array_values($counter)). " total, ". $counter['translated']. " translated, ". $counter['left']. " left.".CRLF;
	$output .= '?>'.CRLF;
} else {  // new language file.
	$output .= '<?php'. CRLF;
	foreach ($NEW__text as $text)
	$output .= '//$__text['. $text. '] = '. $text{0}. $text{0}. ';'. CRLF;
	$output .= '?>';
}
echo $output;
?>
