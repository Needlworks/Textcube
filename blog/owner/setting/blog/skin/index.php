<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (!empty($_POST['adminSkin']) && file_exists(ROOT."/style/admin/{$_POST['adminSkin']}/index.xml"))
	setUserSetting("adminSkin", $_POST['adminSkin']);
if (!empty($_POST['editorTemplate']) && file_exists(ROOT."/skin/{$_POST['editorTemplate']}/skin.html"))
	setUserSetting("visualEditorTemplate", $_POST['editorTemplate']);

header("Location: ".$_SERVER['HTTP_REFERER']);
?>