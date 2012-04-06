<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function printSimpleHtmlHeader($title) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?php echo $title;?></title>
</head>
<body>
<?php
}

function printSimpleHtmlFooter() {
?>
</body>
</html>
<?php
}

function printScriptStart() {
?>
<script type="text/javascript">
//<![CDATA[
<?php
}

function printScriptEnd() {
?>
//]]>
</script>
<?php
}
?>
