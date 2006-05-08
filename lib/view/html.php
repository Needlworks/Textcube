<?php

function printSimpleHtmlHeader($title) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title><?php echo $title?></title>
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
