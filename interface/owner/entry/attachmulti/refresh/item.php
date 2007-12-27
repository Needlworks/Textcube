<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.attachment");
$entryId = $suri['id'];
$attachments = getAttachments($blogid, $entryId, 'label');
$initialFileListForFlash = '';
$enclosureFileName = '';
?>
<select size="8" name="fileList" id="fileList" multiple="multiple" style="width:415px;" onchange="selectAttachment();">
<?php
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
	}
	$value = htmlspecialchars(getAttachmentValue($attachment));
	$label = htmlspecialchars(getPrettyAttachmentLabel($attachment));
?>
		        <option  <?php echo $style;?> value="<?php echo $value;?>">
	            <?php echo $label;?>
	            </option>
                <?php
}
?>
</select>
