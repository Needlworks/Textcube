<?php
/* Referer keywords for Textcube & TatterTools
   ----------------------------------
   Version 1.2+
   Tatter and Friends.

   Creator          : Chiri (http://moonmelody.com)
   Maintainer       : gendoh (http://gendoh.com)

   Created at       : 2006.9.13
   Last modified at : 2008.3.19 by gendoh (http://gendoh.com)
                      2008.3.21 by Chiri (http://moonmelody.com)
 
 This plugin shows referer keyword statistics for a week on administration menu.
 For the detail, visit http://forum.tattersite.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/

function array_sort($array, $type='asc'){
   $result=array();
   foreach($array as $var => $val){
       $set=false;
       foreach($result as $var2 => $val2){
           if($set==false){
               if($val>$val2 && $type=='desc' || $val<$val2 && $type=='asc'){
                   $temp=array();
                   foreach($result as $var3 => $val3){
                       if($var3==$var2) $set=true;
                       if($set){
                           $temp[$var3]=$val3;
                           unset($result[$var3]);
                       }
                   }
                   $result[$var]=$val;    
                   foreach($temp as $var3 => $val3){
                       $result[$var3]=$val3;
                   }
               }
           }
       }
       if(!$set){
           $result[$var]=$val;
       }
   }
   return $result;
}

function unified_decode_processor($matches)
{
	$grab = array();
	if (preg_match('@^%([[:alnum:]][[:alnum:]])$@', $matches[0], $grab) > 0) {
		if (hexdec($grab[1]) == 0) return ' '; // 0x00은 공백으로 처리
		return chr(hexdec($grab[1]));
	}
	if (preg_match('@^%u([[:alnum:]][[:alnum:]][[:alnum:]][[:alnum:]])$@', $matches[0], $grab) > 0) {
		$value = hexdec($grab[1]); 

		if ($value == 0) return ' '; // 0x00은 공백으로 처리
		if ($value < 0x0080) { // 7bit -> 1byte
			return chr($value);
		}
		if ($value < 0x0800) { // 11bit -> 2byte ( 110xxxxx 10xxxxxx )
			return chr((($value & 0x07c0) >> 6) | 0xc0) . chr(($value & 0x3f) | 0x80);
		}
		// 16bit --> 3byte ( 1110xxxx 10xxxxxx 10xxxxxx )
		return chr((($value & 0xf000) >> 12) | 0xe0)
				. chr((($value & 0x0fc0) >> 6) | 0x80)
				. chr(($value & 0x3f) | 0x80);
	}
	return $matches[0]; // 번역이 안되는 놈들은 그대로 출력하자
}

function unified_decode($string)
{
	return preg_replace_callback(
		'@(%u[[:alnum:]]{4}|%[[:alnum:]]{2})@', 
		'unified_decode_processor', 
		str_replace('+', ' ', $string) // '+'를 먼저 처리한다.
	);
}

function tostring($text){
return iconv('UTF-16LE', 'UTF-8', chr(hexdec(substr($text[1], 2, 2))).chr(hexdec(substr($text[1], 0, 2))));
}

function urlutfchr($text){
return urldecode(preg_replace_callback('/%u([[:alnum:]]{4})/', 'tostring', $text));
}

function bringSearchWord($originalURL,$originalHost){
	$matches = array();
	$decodedURL = '';
	$decodedKeyword = '';
//	$originalURL = urlutfchr($originalURL);
	if(preg_match('/\W(q|query|k|keyword|search|stext|nlia|aqa|wd)(?:=|%3D)([^&]+)/i', $originalURL, $matches)){
		$decodedKeyword = unified_decode($matches[2]);
		$decodedURL = unified_decode($originalURL);}
	else if(strpos($originalHost, 'images.google.') !== false && preg_match('/%3Fsearch%3D([^&]+)/i', $originalURL, $matches)) {
		$decodedKeyword = unified_decode($matches[1]);
		$decodedURL = unified_decode($originalURL);}
	else if(strpos($originalURL, 'yahoo') !== false && preg_match('/\Wp=([^&]+)/i', $originalURL, $matches)){
		$decodedKeyword = unified_decode($matches[1]);
		$decodedURL = unified_decode($originalURL);}
	else if(preg_match('@/search/(?:\w+/)*([^/?]+)@i', $originalURL, $matches)){
		$decodedKeyword = unified_decode($matches[1]);
		$decodedURL = unified_decode($originalURL);}
if(!UTF8::validate($decodedKeyword)){
	$decodedKeyword = UTF8::correct(UTF8::bring($decodedKeyword));
	$decodedURL = UTF8::correct(UTF8::bring($decodedURL));}

	return array($decodedKeyword, $decodedURL);
}


function refererkeyword()
{
global $pluginMenuURL, $pluginSelfParam, $configVal;

$more = false;

	if (defined('TEXTCUBE_NAME')) {
		requireComponent('Textcube.Model.Statistics');
		requireComponent('Textcube.Function.misc');
	} else {
		requireComponent('Tattertools.Model.Statistics');
		requireComponent('Tattertools.Function.misc');
	}

	$data = Setting::fetchConfigVal( $configVal);


	$showURL = 0;
	$limitRank = 5;
	$Filtering = array();

	if( !is_null( $data ) ){
		$showURL = $data['showURL'];
		$limitRank = $data['limitRank'];
		$Filtering = preg_split("/[\s,]+/",$data['WordFiltering']);
	}

if (!empty($_POST['showURL'])) $showURL = $_POST['showURL'];

if (!empty($_POST['showKeywordlistLight'])) $limitRank = $_POST['showKeywordlistLight'];




$refereres = Statistics::getRefererLogs();
$keywordlist = array();
$wordlist = array();
$record = array();
$refererURL = array();
$totalpassedkeyword = 0;

for ($i=0; $i<sizeof($refereres); $i++) {
	$record = $refereres[$i];
	if ($i==0) $referredend = $record['referred'];
	$keyword = "";
	$passthiskeyword = 0;

	list ($keyword, ) = bringSearchWord($record['url'],$record['host']);
	foreach ($Filtering as $FilterWord) {
		if (strpos($keyword, $FilterWord) !== false) {
			$passthiskeyword = 1;
			$totalpassedkeyword++;
			break 1;
		}
	}
	
	if ($passthiskeyword == 0) {
	
	if (array_key_exists($keyword, $keywordlist)) {
		$refererURL[$keyword][$keywordlist[$keyword]] = $record['url'];
		$keywordlist[$keyword]++;
	}
	elseif ($keyword) { $keywordlist[$keyword] = 1; $refererURL[$keyword][0] = $record['url'];}

	$word = split(" ", $keyword);
	foreach ($word as $maira){
		if (array_key_exists($maira, $wordlist)) {
			$wordlist[$maira]++;
		}
		elseif ($maira) { $wordlist[$maira] = 1; }
	}
	
	}
}
$referredstart = array_key_exists('referred', $record) ? $record['referred'] : null;
?>
					 			<h2 class="caption"><span class="main-text"><?php echo _t('리퍼러 검색어 통계')." (".Timestamp::formatDate($referredstart)." ~ ".Timestamp::formatDate($referredend).")";?></span></h2>

						 		<div id="statistics-counter-inbox" class="data-inbox">
									<div class="title">
										<span class="label"><span class="text"><?php echo _t('총 검색어 개수');?></span></span>
										<span class="divider"> : </span>
										<span id="total"><?php echo count($keywordlist);?></span>
										<span class="divider"> : </span>
										<span class="label"><span class="text"><?php echo _t('총 리퍼러 개수');?></span></span>
										<span class="divider"> : </span>
										<span id="total"><?php echo sizeof($refereres);?></span>
										<span class="divider"> : </span>
<?php
if ($totalpassedkeyword > 0) {
?>
										<span class="label"><span class="text"><?php echo _t('필터링된 리퍼러 개수');?></span></span>
										<span class="divider"> : </span>
										<span id="total"><?php echo $totalpassedkeyword;?></span>
										<span class="divider"> : </span>
<?php
}
?>
									</div>
<form id="refererkeyword-option" class="part" method="post" action="<?php echo $pluginMenuURL;?>">
									<div class="title">
										<span class="label"><?php echo _t('검색어 순위 출력은');?></span>
										<span class="label"><select name="showKeywordlistLight" onchange="document.getElementById('refererkeyword-option').submit()">
										<option value="5"<?php if ($limitRank == 5 || $limitRank == 0) echo " selected=\"selected\"";?>><?php echo _t('5위까지만 출력합니다');?></option>
										<option value="10"<?php if ($limitRank == 10) echo " selected=\"selected\"";?>><?php echo _t('10위까지만 출력합니다');?></option>
										<option value="15"<?php if ($limitRank == 15) echo " selected=\"selected\"";?>><?php echo _t('15위까지만 출력합니다');?></option>
										<option value="3939"<?php if ($limitRank == 3939) echo " selected=\"selected\"";?>><?php echo _t('모든 순위를 출력합니다');?></option>
										</select></span>
									</div>
									<div class="title">
										<span class="label"><span class="text"><?php echo _t('각 검색어가 검출된 주소를');?></span></span>
										<span class="label"><select name="showURL" onchange="document.getElementById('refererkeyword-option').submit()">
										<option value="0"<?php if ($showURL == 0 || $showURL == "") echo " selected=\"selected\"";?>><?php echo _t('출력하지 않습니다');?></option>
										<option value="1"<?php if ($showURL == 1) echo " selected=\"selected\"";?>><?php echo _t('출력합니다');?></option>
										</select></span>

									</div>
</form>
								</div>

							<hr class="hidden" />

							<div id="part-statistics-cloud" class="part">
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="site"><span class="text"><?php echo _t('검색어에 주로 쓰인 단어');?></span></th>
									</tr>
								</thead>
								<tbody>
									<tr class="even-line inactive-class">
										<td class="keywordcloud">
<?php


srand ((float)microtime()*1000000);

$wordlist = array_sort($wordlist,'desc');
$original_wordlist = $wordlist;
$wordkeys = array_keys($wordlist);

$i = 0;
$cloudstyle = array();
foreach ($wordkeys as $wordwork) {
	if (($wordlist[$wordwork]) < 2) {
	unset ($wordlist[$wordwork]);
	unset ($wordkeys[$i]);
	}
	if (($wordlist[$wordwork]) > 50) $cloudstyle[$i] = "cloud1";
	elseif (($wordlist[$wordwork]) > 25) $cloudstyle[$i] = "cloud2";
	elseif (($wordlist[$wordwork]) > 15) $cloudstyle[$i] = "cloud3";
	elseif (($wordlist[$wordwork]) > 6) $cloudstyle[$i] = "cloud4";
	else $cloudstyle[$i] = "cloud5";
	$i++;
}
if (count($wordkeys) <= 10) {$wordlist = $original_wordlist; $wordkeys = array_keys($wordlist);}

//shuffle ($wordkeys);
$beforewordvalue = '';
$wordrank = 0;


for ($i=0; $i<sizeof($wordlist); $i++) {
	$wordkey = $wordkeys[$i];
	$wordvalue = $wordlist[$wordkey];
	$wordkey = str_replace("\"", "&quot;",$wordkeys[$i]);
	if ($wordvalue != $beforewordvalue){
		if ($wordrank == 15) break;
		$wordrank++;
		$beforewordvalue = $wordvalue;
	}
	
	$wordclassName = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$wordclassName .= ($i == sizeof($wordlist) - 1) ? ' last-line' : '';

	echo "<a class=\"".$cloudstyle[$i]."\">".htmlspecialchars($wordkey)."</a>&nbsp;&nbsp;";
}
?>
									 </td>
									</tr>
								</tbody>
							</table>
						</div>

								<hr class="hidden" />


							<div id="part-statistics-log" class="part">
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="number"><span class="text"><?php echo _t('순위');?></span></th>
										<th class="number"><span class="text"><?php echo _t('개수');?></span></th>
										<th class="searchwordtitle"><span class="text"><?php echo _t('검색어명');?></span></th>
									</tr>
								</thead>
								<tbody>
<?php


$keywordlist = array_sort($keywordlist,'desc');
$keywordkeys = array_keys($keywordlist);
$beforekeywordvalue = '';
$rank = array();
$samerank = 1;
$rankcount = 0;

// 머리가 나빠서 죄송합니다... orz
for ($i=0; $i<sizeof($keywordlist); $i++) {
	$keywordkey = $keywordkeys[$i];
	$keywordvalue = $keywordlist[$keywordkey];
//	$keywordkey = str_replace("\"", "&quot;",$keywordkeys[$i]);
	if ($keywordvalue != $beforekeywordvalue){
		$rankcount++;
		$rank[$i] = $rankcount;
		$beforekeywordvalue = $keywordvalue;
	}
	else {
		$rank[$i] = $rankcount;
	}
}

$eversamerankTotal = array_count_values ($rank);
$rankcount = 0;

for ($i=0; $i<sizeof($keywordlist); $i++) {
	$keywordkey = $keywordkeys[$i];
	$keywordvalue = $keywordlist[$keywordkey];
//	$keywordkey = str_replace("\"", "&quot;",$keywordkeys[$i]);
	if ($keywordvalue != $beforekeywordvalue){
		$rankcount++;
		$beforekeywordvalue = $keywordvalue;
		$samerank = 1;
	}
	else $samerank++;

	if ($limitRank != 3939) {
	if ($rankcount >= 6 && $limitRank == 5) break;
	if ($rankcount >= 11 && $limitRank == 10) break;
	if ($rankcount >= 16 && $limitRank == 15) break;
	}
	
	$RefererURLthiskeyword = $refererURL[$keywordkey];
	if ($eversamerankTotal[$rankcount] == 1) $viewSameRank = '';
	else $viewSameRank = " (".$samerank."／".$eversamerankTotal[$rankcount].")";

?>
									<tr onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td class="rank"><?php echo $rankcount._t('위').$viewSameRank;?></td>
										<td class="rank"><?php echo $keywordvalue._t('개');?></td>
										<td class="address"><?php echo htmlspecialchars($keywordkey);?></td>
									</tr>
	<?php if ($showURL == 1) {
		$j = 0;
		foreach (array_unique ($RefererURLthiskeyword) as $splitRefererURL) {
			$urlClassName = ($j == sizeof(array_unique ($RefererURLthiskeyword)) - 1) ? '' : 'noBorderBottom';
			list (, $decodeURL) = bringSearchWord($splitRefererURL);
?>
									<tr>
										<td class="<?php echo $urlClassName; ?>"></td>
										<td class="<?php echo $urlClassName; ?>"></td>
										<td class="refererurl">
<?php
			echo "<a href=\"".Misc::escapeJSInAttribute($splitRefererURL)."\" onclick=\"window.open(this.href); return false;\">".UTF8::lessenAsEm(htmlspecialchars($decodeURL), 90)."</a>";
			$j++;
		}
		
	}?>
										</td>
									</tr>
<?php
}
?>
								</tbody>
							</table>
						</div>
						<div class="clear"></div>
<?php
}

function refererkeyword_DataSet($DATA){
	if (defined('TEXTCUBE_NAME')) {
		requireComponent('Textcube.Function.misc');
	} else {
		requireComponent('Tattertools.Function.misc');
	}

	$cfg = Setting::fetchConfigVal($DATA);

	return true;
}
?>
