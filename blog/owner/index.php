<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

if(isset($service['useFastCGI']) && $service['useFastCGI'] == true) {
	$url = rtrim(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'], '/');
	$url = preg_replace('/\?[\w\&=]+/', '', $url);
} else {
	$url = rtrim(isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['SCRIPT_NAME'], '/');
}
header("Location: $url/center/dashboard");
?>
