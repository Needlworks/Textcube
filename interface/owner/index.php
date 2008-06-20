<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)


require ROOT . '/library/includeForBlog.php';
$refererURI = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
// Redirect.
$_SESSION['refererURI'] = $refererURI;
header("Location: $blogURL/owner/center/dashboard");
?>
