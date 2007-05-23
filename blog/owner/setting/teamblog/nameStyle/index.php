<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'bold'=>array('string'),
		'italic'=>array('string'),
		'color'=>array('string'),
		'size'=>array('int'),
		'pos'=>array('int','default'=>null),
		'style'=>array('string','default'=>null),
		'is_style'=>array('string','default'=>null)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();

function changeNameStyle($owner,$_POST){
	global $database, $_SESSION;
	
	$admin = $_SESSION['admin'];
	
	$style = $_POST['style'];
	$pos = $_POST['pos'];
	$is_style = $_POST['is_style'];
	
	if($owner == $admin && empty($pos))
		return false;

	if($owner != $admin && !empty($pos))
		return false;
	
	if($owner == $admin && empty($style))
		return false;
		
	if($owner != $admin && !empty($style))
		return false;

	if($owner != $admin){
		$result = DBQuery::query("SELECT font_style FROM `{$database['prefix']}Teamblog` WHERE `teams`='$owner' and userid='$owner'");
		$res = mysql_fetch_array($result);
		$font_style = $res['font_style'] & 2;
		$isname = $res['font_style'] & 4;
		
		if(!empty($font_style) || !empty($isname))
			return false;
	}

	$bold = $_POST['bold'];
	$italic = $_POST['italic'];
	$size = $_POST['size'];
	$color = $_POST['color'];
	

	if(!strlen($bold) || !strlen($italic) || !strlen($size) || !strlen($color))
		return false;

  $font_bold = 0;
  if($bold == "true") $font_bold += 1;
  if($italic == "true") $font_bold += 2;
  
  $font_style = 0;
  if($owner == $admin){
  	if($style == "true") $font_style +=2;
  	
  	if($is_style == "true") $font_style += 1;
  	if($pos == 1) $font_style +=4;
  	else if($pos == 2) $font_style += 8;
  	else if($pos == 4) $font_style += 16;
  }

	$sql="UPDATE `{$database['prefix']}Teamblog` SET `font_style`='$font_style', `font_bold`='$font_bold', `font_size`='$size', `font_color`='$color'  WHERE `userid` = '$admin' and `teams`='$owner'";
	return DBQuery::execute($sql);
}

if (changeNameStyle($owner,$_POST)) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>