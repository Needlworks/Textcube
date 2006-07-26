<?php
$url = rtrim(isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['SCRIPT_NAME'], '/');
header("Location: $url/center/dashboard");
?>