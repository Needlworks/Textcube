<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$entryId = $suri['id'];
$attachments = getAttachments($owner, $entryId);
$initialFileListForFlash = '';
$enclosureFileName = '';
?>
<select size="8" name="fileList" id="fileList" multiple="multiple" style="width:415px;" onchange="selectAttachment();">
<?
foreach ($attachments as $i => $attachment) {
	if (strpos($attachment['mime'], 'application') !== false) {
		$class = 'class="MimeApplication"';
	} else if (strpos($attachment['mime'], 'audio') !== false) {
		$class = 'class="MimeAudio"';
	} else if (strpos($attachment['mime'], 'image') !== false) {
		$class = 'class="MimeImage"';
	} else if (strpos($attachment['mime'], 'message') !== false) {
		$class = 'class="MimeMessage"';
	} else if (strpos($attachment['mime'], 'model') !== false) {
		$class = 'class="MimeModel"';
	} else if (strpos($attachment['mime'], 'multipart') !== false) {
		$class = 'class="MimeMultipart"';
	} else if (strpos($attachment['mime'], 'text') !== false) {
		$class = 'class="MimeText"';
	} else if (strpos($attachment['mime'], 'video') !== false) {
		$class = 'class="MimeVideo"';
	} else {
		$class = '';
	}
	if ($attachment['enclosure'] == 1) {
		$style = 'style="background-color:#c6a6e7; color:#000000"';
		$enclosureFileName = $attachment['name'];
	} else {
		$style = '';
		$prefix = '';
	}
	$value = htmlspecialchars(getAttachmentValue($attachment));
	$label = $prefix . htmlspecialchars(getPrettyAttachmentLabel($attachment));
?>
		        <option  <?=$style?> value="<?=$value?>">
	            <?=$label?>
	            </option>
                <?
}
?>
</select>