<?php
function CT_TNF_Stories($target) {
	global $service;
	requireComponent("Eolin.PHP.Core");
	$noticeURL = 'http://blog.tatterstory.net/rss';
	list($result, $feed, $xml) = CT_TNF_Stories_getRemoteFeed($noticeURL);
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
				$target .= '<a href="' .$item['permalink'].'" onclick="return openLinkInNewWindow(this);" >'.CRLF;
				$target .= '<span class="date">'.Timestamp::formatDate($item['written']).'</span>'.CRLF;
				$target .= '<span class="title">'.UTF8::lessenAsEm(htmlspecialchars($item['title']),30).'</span>'.CRLF;
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
			$target .= _t('<p>공지사항이 없습니다.</p>');
		}
	} else {
		$target .= _t('<p style="border: 1px solid #EEEEEE; height: 75px; padding-top: 45px; text-align: center;">공지사항을 가져올 수 없습니다.<br />잠시 후 다시 시도해 주십시오.</p>');
	}

	unset($feed);
	unset($xmls);
	unset($noticeEntries);
	return $target;
}

function CT_TNF_Stories_getRemoteFeed($url) {
	global $service;
	$xml = fireEvent('GetRemoteFeed', null, $url);
	if (empty($xml)) {
		requireComponent('Eolin.PHP.HTTPRequest');
		$request = new HTTPRequest($url);
		$request->timeout = 3;
		if (!$request->send())
			return array(2, null, null);
		$xml = $request->responseText;
	}
	$feed = array('xmlURL' => $url);
	$xmls = new XMLStruct();
	if (!$xmls->open($xml, $service['encoding'])) {
		if(preg_match_all('/<link .*?rel\s*=\s*[\'"]?alternate.*?>/i', $xml, $matches)) {
			foreach($matches[0] as $link) {
				$attributes = getAttributesFromString($link);
				if(isset($attributes['href'])) {
					$urlInfo = parse_url($url);
					$rssInfo = parse_url($attributes['href']);
					$rssURL = false;
					if(isset($rssInfo['scheme']) && $rssInfo['scheme'] == 'http')
						$rssURL = $attributes['href'];
					else if(isset($rssInfo['path'])) {
						if($rssInfo['path']{0} == '/')
							$rssURL = "{$urlInfo['scheme']}://{$urlInfo['host']}{$rssInfo['path']}";							
						else
							$rssURL = "{$urlInfo['scheme']}://{$urlInfo['host']}".(isset($urlInfo['path']) ? rtrim($urlInfo['path'], '/') : '').'/'.$rssInfo['path'];
					}
					if($rssURL && $url != $rssURL)
						return getRemoteFeed($rssURL);
				}
			}
		}
		return array(3, null, null);
	}
	if ($xmls->getAttribute('/rss', 'version')) {
		$feed['blogURL'] = $xmls->getValue('/rss/channel/link');
		$feed['title'] = $xmls->getValue('/rss/channel/title');
		$feed['description'] = $xmls->getValue('/rss/channel/description');
		if (Validator::language($xmls->getValue('/rss/channel/language')))
			$feed['language'] = $xmls->getValue('/rss/channel/language');
		else if (Validator::language($xmls->getValue('/rss/channel/dc:language')))
			$feed['language'] = $xmls->getValue('/rss/channel/dc:language');
		else
			$feed['language'] = 'en-US';
		$feed['modified'] = gmmktime();
	} else if ($xmls->getAttribute('/feed', 'version')) {
		$feed['blogURL'] = $xmls->getAttribute('/feed/link', 'href');
		$feed['title'] = $xmls->getValue('/feed/title');
		$feed['description'] = $xmls->getValue('/feed/tagline');
		if(Validator::language($xmls->getAttribute('/feed', 'xml:lang')))
			$feed['language'] = $xmls->getAttribute('/feed', 'xml:lang');
		else
			$feed['language'] = 'en-US';
		$feed['modified'] = gmmktime();
	} else if ($xmls->getAttribute('/rdf:RDF', 'xmlns')) {
		if($xmls->getAttribute('/rdf:RDF/channel/link', 'href'))
			$feed['blogURL'] = $xmls->getAttribute('/rdf:RDF/channel/link', 'href');
		else if($xmls->getValue('/rdf:RDF/channel/link'))
			$feed['blogURL'] = $xmls->getValue('/rdf:RDF/channel/link');
		else
			$feed['blogURL'] = '';
		$feed['title'] = $xmls->getValue('/rdf:RDF/channel/title');
		$feed['description'] = $xmls->getValue('/rdf:RDF/channel/description');
		if(Validator::language($xmls->getValue('/rdf:RDF/channel/dc:language')))
			$feed['language'] = $xmls->getValue('/rdf:RDF/channel/dc:language');
		else if(Validator::language($xmls->getAttribute('/rdf:RDF', 'xml:lang')))
			$feed['language'] = $xmls->getAttribute('/rdf:RDF', 'xml:lang');
		else
			$feed['language'] = 'en-US';
		$feed['modified'] = gmmktime();
	} else
		return array(3, null, null);

	$feed['blogURL'] = mysql_tt_escape_string(mysql_lessen(UTF8::correct($feed['blogURL'])));
	$feed['title'] = mysql_tt_escape_string(mysql_lessen(UTF8::correct($feed['title'])));
	$feed['description'] = mysql_tt_escape_string(mysql_lessen(UTF8::correct(stripHTML($feed['description']))));

	return array(0, $feed, $xml);
}
?>
