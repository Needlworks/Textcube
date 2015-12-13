<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
if (empty($suri['value']))
	Respond::NotFoundPage();
if (!$attachment = getAttachmentByOnlyName($blogid, $suri['value']))
	Respond::NotFoundPage();
$fp = fopen(__TEXTCUBE_ATTACH_DIR__."/$blogid/{$attachment['name']}", 'rb');
if (!$fp)
	Respond::NotFoundPage();
$fstat = fstat($fp);
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	$modifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
	if ($modifiedSince && ($modifiedSince >= $fstat['mtime'])) {
		fclose($fp);
		header('HTTP/1.1 304 Not Modified');
		header('Connection: close');
		exit;
	}
}

ini_set('zlib.output_compression', 'off');
header('Content-Disposition: attachment; filename="' . rawurlencode(Utils_Unicode::bring($attachment['label'])) . '"');
header('Content-Transfer-Encoding: binary');
header('Last-Modified: ' . Timestamp::getRFC1123GMT($fstat['mtime']));
header('Content-Length: ' . $fstat['size']);
header('Content-Type: ' . $attachment['mime']);
header('Cache-Control: private');
header('Pragma: no-cache'); 
header('Connection: close');
fpassthru($fp);
fclose($fp);
downloadAttachment($attachment['name']);
?>
