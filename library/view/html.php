<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function printSimpleHtmlHeader($title) {
?>
    <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
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
    <?php
    }

    function printScriptEnd() {
    ?>
</script>
<?php
}
?>
