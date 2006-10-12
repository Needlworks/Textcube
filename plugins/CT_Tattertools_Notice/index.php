<?php
function CT_Tattertools_Notice($target) {
	global $service;
	requireComponent("Eolin.PHP.Core");
	$noticeURL = 'http://blog.tattertools.com/rss';
	list($result, $feed, $xml) = getRemoteFeed($noticeURL);
	if ($result == 0) {
		$xmls = new XMLStruct();
		$noticeEntries = array();
		if ($xmls->open($xml, $service['encoding'])) {
			if ($xmls->getAttribute('/rss', 'version')) {
				for ($i = 0; $link = $xmls->getValue("/rss/channel/item[$i]/link"); $i++) {
					$item = array('permalink' => rawurldecode($link));
					$item['title'] = $xmls->getValue("/rss/channel/item[$i]/title");
					if ($xmls->getValue("/rss/channel/item[$i]/pubDate"))
						$item['written'] = parseDate($xmls->getValue("/rss/channel/item[$i]/pubDate"));
					else if ($xmls->getValue("/rss/channel/item[$i]/dc:date"))
						$item['written'] = parseDate($xmls->getValue("/rss/channel/item[$i]/dc:date"));
					else
						$item['written'] = 0;
					array_push($noticeEntries, $item);
				}
			} else if ($xmls->getAttribute('/feed', 'version')) {
				for ($i = 0; $link = $xmls->getValue("/feed/entry[$i]/id"); $i++) {
					for ($j = 0; $rel = $xmls->getAttribute("/feed/entry[$i]/link[$j]", 'rel'); $j++) {
						if($rel == 'alternate') {
							$link = $xmls->getAttribute("/feed/entry[$i]/link[$j]", 'href');
							break;
						}
					}
					$item = array('permalink' => rawurldecode($link));
					$item['author'] = $xmls->getValue("/feed/entry[$i]/author/name");
					$item['title'] = $xmls->getValue("/feed/entry[$i]/title");
					$item['written'] = parseDate($xmls->getValue("/feed/entry[$i]/issued"));
					array_push($noticeEntries, $item);
				}
			} else if ($xmls->getAttribute('/rdf:RDF', 'xmlns')) {
				for ($i = 0; $link = $xmls->getValue("/rdf:RDF/item[$i]/link"); $i++) {
					$item = array('permalink' => rawurldecode($link));
					$item['author'] = $xmls->getValue("/rdf:RDF/item[$i]/dc:creator");
					$item['title'] = $xmls->getValue("/rdf:RDF/item[$i]/title");
					$item['written'] = parseDate($xmls->getValue("/rdf:RDF/item[$i]/dc:date"));
					array_push($noticeEntries, $item);
				}
			}
		}	
		
		if (count($noticeEntries) > 0) {
			$target .= '<ol>'.CRLF;
			$i = 0;
			foreach($noticeEntries as $item) {
				$target .= '<li>'.CRLF;
				$target .= '<a href="' .$item['permalink'].'">'.CRLF;
				$target .= '<span>'.Timestamp::formatDate($item['written']).'</span>'.CRLF;
				$target .= '<span>'.UTF8::lessenAsEm(htmlspecialchars($item['title']),30).'</span>'.CRLF;
				$target .= '</a>'.CRLF;
				$target .= '</li>'.CRLF;
				if($i>3) break;
				else $i++;
			}
			$target .= '</ol>'.CRLF;
			//$target .= '<ul>'.CRLF;
			//$target .= '<li><span> from '.$noticeURL.'</span></li>'.CRLF;
			//$target .= '</ul>'.CRLF;
		} else {
			$target .= _t('공지사항이 없습니다.');
		}
	} else {
		$target .= _t('공지사항을 가져올 수 없습니다. 잠시 후 다시 시도해 보심시오');
	}

	unset($feed);
	unset($xmls);
	unset($noticeEntries);
	return $target;
}
?>