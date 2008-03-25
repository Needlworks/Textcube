<?php
/* Referer keywords for Textcube & TatterTools
   ----------------------------------
   Version 1.2
   Tatter and Friends.

   Creator          : 치리 (http://mahodou.pe.kr/tt)
   Maintainer       : gendoh (http://gendoh.com)

   Created at       : 2006.9.13
   Last modified at : 2008.3.19 by gendoh (http://gendoh.com)
 
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

function refererkeyword()
{
requireComponent('Textcube.Model.Statistics');

$refereres = Statistics::getRefererLogs();
$keywordlist = array();
$record = array();

for ($i=0; $i<sizeof($refereres); $i++) {
	$record = $refereres[$i];
	if ($i==0) $referredend = $record['referred'];
		$keyword = "";
		$matches = array();
		if(preg_match('/\W(q|query|k|keyword|search|stext|nlia|aqa|wd)(?:=|%3D)([^&]+)/i', $record['url'], $matches)) {
			$keyword = unified_decode($matches[2]);
		}
		else if(strpos($record['host'], 'images.google.') !== false && preg_match('/%3Fsearch%3D([^&]+)/i', $record['url'], $matches)) {
			$keyword = unified_decode($matches[1]);
		}
		else if(strpos($record['url'], 'yahoo.') !== false && preg_match('/\Wp=([^&]+)/i', $record['url'], $matches)) {
			$keyword = unified_decode($matches[1]);
		}
		else if(preg_match('@/search/(?:\w+/)*([^/?]+)@i', $record['url'], $matches)) {
			$keyword = unified_decode($matches[1]);
		}
		
		if(!UTF8::validate($keyword))
			$keyword = UTF8::correct(UTF8::bring($keyword));

	if (array_key_exists($keyword, $keywordlist)) {
		$keywordlist[$keyword]++;
	}
	elseif ($keyword) { $keywordlist[$keyword] = 1; }

}
$referredstart = array_key_exists('referred', $record) ? $record['referred'] : null;
if (!isset($referredend)) $referredend = $referredstart; 
?>
				 		<div id="part-statistics-visitor" class="part">
					 		<h2 class="caption"><span class="main-text"><?php echo _t('키워드 통계')." (".Timestamp::formatDate($referredstart)." ~ ".Timestamp::formatDate($referredend)." )";?></span></h2>
					 		
							<div id="statistics-counter-inbox" class="data-inbox">
								<div class="title">
									<span class="label"><span class="text"><?php echo _t('총 키워드 개수');?></span></span>
									<span class="divider"> : </span>
									<span id="total"><?php echo count($keywordlist);?></span>
								</div>
							</div>	
						</div>
						
						<hr class="hidden" />
						
						<div id="part-statistics-log" class="part">
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="number"><span class="text"><?php echo _t('순위');?></span></th>
										<th class="number"><span class="text"><?php echo _t('개수');?></span></th>
										<th class="site"><span class="text"><?php echo _t('키워드명');?></span></th>
									</tr>
								</thead>
								<tbody>
<?php


$keywordlist = array_sort($keywordlist,'desc');
$keywordkeys = array_keys($keywordlist);
$beforekeywordvalue = '';
$rank = 0;
for ($i=0; $i<sizeof($keywordlist); $i++) {
	$keywordkey = $keywordkeys[$i];
	$keywordvalue = $keywordlist[$keywordkey];
	if ($keywordvalue != $beforekeywordvalue){
		$rank++;
		$beforekeywordvalue = $keywordvalue;
	}
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($keywordlist) - 1) ? ' last-line' : '';
?>
									<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td class="rank"><?php echo $rank.".";?></td>
										<td class="rank"><?php echo $keywordvalue;?></td>
										<td class="address"><?php echo htmlspecialchars($keywordkey);?></td>
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
?>