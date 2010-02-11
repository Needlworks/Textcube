<?php
require_once ('lib/twitter.lib.php');	

// Twitter text source link converting
function getTextLinkConvert($textLink) {
	$returnLink = "";
	if (preg_match("@(?:&lt;|')a[^>]+?href=(?:&quot;|')(.*?)(?:&quot;|')(?:&gt;|')(.*?)(?:&lt;|')\/a(?:&gt;|')@i", $textLink, $match)) {
		$returnLink = '<a href="' . $match[1] .'" title="' . $match[2] .'" onclick="window.open(this.href); return false;">' . $match[2] .'</a>';
	} else if (preg_match("@<a[^>]+?href=['\"](.+)['\"]>(.*)</a>@Usi", $textLink, $match)) {
		$returnLink = '<a href="' . $match[1] .'" title="' . $match[2] .'" onclick="window.open(this.href); return false;">' . $match[2] .'</a>';
	} else {
		$returnLink = $textLink;
	}
	return $returnLink;
}

// Twitter text body converting
function getTextBodyConvert($text) {
	global $pluginMenuURL;
	if (empty($pluginMenuURL)) {
		$pluginMenuURL = getPluginMenuURL();
	}

	preg_match_all('@((http|https|ftp)://[^\s"\']+)@i', $text, $temp);
	for ($i=0; $i<count($temp[0]); $i++) {
		$text = str_replace($temp[0][$i], '<a href="' . $temp[1][$i] . '" title="' . $temp[1][$i] . '" onclick="window.open(this.href); return false;">' . UTF8::lessenAsEm($temp[1][$i], 30) . '</a>', $text);
	}

	preg_match_all('@\@{1}(\w+)@i', $text, $temp);
	for ($i=0; $i<count($temp[0]); $i++) {
		$text = str_replace($temp[0][$i], '@<a href="http://twitter.com/' . $temp[1][$i] . '" title="' . $temp[1][$i] . '" onclick="window.open(this.href); return false;">' . $temp[1][$i] . '</a>', $text);
	}

	preg_match_all('@\#{1}(\w+)@i', $text, $temp);
	for ($i=0; $i<count($temp[0]); $i++) {
		$text = str_replace($temp[0][$i], '<a href="' . $pluginMenuURL . '&menu=search&q=%23' . $temp[1][$i] . '" title="' . $temp[0][$i] . '">' . $temp[0][$i] . '</a>', $text);
	}

	return $text;
}

// Twitter plugin menu url
function getPluginMenuURL() {
	global $pluginURL, $blogURL;
	$temp = explode("/" , $pluginURL);
	$plugin = $temp[(count($temp)-1)];
	$pluginMenuURL = $blogURL . '/owner/plugin/adminMenu?name=' . $plugin . '/PN_Twitter_Management';
	return $pluginMenuURL;
}

// Twitter date convert
function getTwitterDateConvert($published) {
	$nowDate = time();
	$published = strtotime($published);
	$dateConvert = getCovertDate(($nowDate - $published), $published);
	return $dateConvert; 
} 

function getCovertDate($reqTime, $published) {
    $dateConvert = '';

	if ($reqTime < 0 ) {
		$dateConvert = 'less than 5 seconds ago';
		return $dateConvert;
	}

	$month = floor($reqTime / 2592000);
	$reqTime %= 2592000;
	$day = floor($reqTime / 86400);
	$reqTime %= 86400;
	$hour = floor($reqTime / 3600);
	$reqTime %= 3600;
	$minute = floor($reqTime / 60);
	$reqTime %= 60;
	$second = $reqTime;

    if ($day > 1) { 
		$dateConvert = date('g:i A M jS', $published);
	} else if($day > 0 && $day <=3) {
		$dateConvert = 'about ' . $day . ' ' . ($day > 1 ? 'days' : 'day') . ' ago';
	} else if($hour > 0) {
		$dateConvert = 'about ' . $hour . ' ' . ($hour > 1 ? 'hours' : 'hour') . ' ago';
	} else if($minute > 0) {
		$dateConvert = $minute . ' ' . ($minute > 1 ? 'minutes' : 'minute') . ' ago';
	} else if($second >= 0) {
		if($second >= 0 && $second < 5) {
			$dateConvert = 'less than 5 seconds ago';
		} else if($second >= 5 && $second < 10) {
			$dateConvert = 'less than 10 seconds ago';
		} else if($second >= 10 && $second < 20) {
			$dateConvert = 'less than 20 seconds ago';
		} else if($second >= 20 && $second < 30) {
			$dateConvert = 'half a minute ago';
		} else if($second >= 30 && $second < 60) {
			$dateConvert = 'less than a minute ago';
		}
	}
    return $dateConvert;
}

// Twitter Recipients List
function getTwitterRecipientsList($target) {
	global $service, $pluginURL, $blogURL, $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);
	$tw = new Twitter($data['username'], $data['password']);
	$recipientsList = $tw->getRecipientsList(array('twttr'=>'true'));

	header('Content-Type: application/x-json; charset=UTF-8');
	echo $recipientsList;
	flush();
}

// Twitter Create Favorites
function getCreateFavorites($target) {
	global $service, $pluginURL, $blogURL, $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);
	$id = (isset($_POST['id']) && !empty($_POST['id'])) ? $_POST['id'] : '';
	$tw = new Twitter($data['username'], $data['password']);
	$cFav = $tw->createFavorite($id);
	$cFavID = !empty($cFav->id) ? $cFav->id : '';
	$favorited = $cFav->favorited;
	header('Content-Type: text/xml; charset=UTF-8');
	echo '<?xml version="1.0" encoding="utf-8"?><response><favoriteID><![CDATA[' . $cFavID . ']]></favoriteID><favorited><![CDATA[' . $favorited . ']]></favorited></response>';
	flush();
}

// Twitter Destroy Favorites
function getDestroyFavorites($target) {
	global $service, $pluginURL, $blogURL, $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);
	$id = (isset($_POST['id']) && !empty($_POST['id'])) ? $_POST['id'] : '';
	$tw = new Twitter($data['username'], $data['password']);
	$dFav = $tw->destroyFavorite($id);
	$dFavID = !empty($dFav->id) ? $dFav->id : '';
	$favorited = $dFav->favorited;
	header('Content-Type: text/xml; charset=UTF-8');
	echo '<?xml version="1.0" encoding="utf-8"?><response><favoriteID><![CDATA[' . $dFavID . ']]></favoriteID><favorited><![CDATA[' . $favorited . ']]></favorited></response>';
	flush();
}

// Twitter Search keyword highlight
function getBindSearchHighlight($keyword, $content) {
    $src = array("/", "|");
    $dst = array("\/", "\|");
    if (!trim($keyword)) return $content;
	$tempContent = str_replace($src, $dst, quotemeta($keyword));
	$tempKeyword = $tempContent . "(?![^<]*>)";
    return preg_replace("/($tempKeyword)/i", "<span class='highlight'>\\1</span>", $content);
}

// Twitter Saved Searches open or close
function getSavedSearchesOpenClose($target) {
	$savedSearchesView = (isset($_POST['openclose']) && !empty($_POST['openclose'])) ? $_POST['openclose'] : 'open';
	Setting::setBlogSetting("saved_searches", $savedSearchesView);
	header('Content-Type: text/xml; charset=UTF-8');
	echo '<?xml version="1.0" encoding="utf-8"?><response><savedSearchesView><![CDATA[' . $savedSearchesView . ']]></savedSearchesView></response>';
	flush();
}

// Twitter Create Saved Searches
function getCreateSavedSearches($target) {
	global $service, $pluginURL, $blogURL, $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);

	$q = (isset($_POST['qdata']) && !empty($_POST['qdata'])) ? $_POST['qdata'] : '';
	$result = '';
	if (!empty($data['username']) && !empty($data['password'])) {
		if (!empty($q)) {
			$tw = new Twitter($data['username'], $data['password']);
			$createSave = $tw->createSavedSearches(array('q'=>$q));
			$errorMSG = empty($createSave->error) ? "0" : $createSave->error;
			$saved_id = "";
			if ($errorMSG == "0") {
				$saved_id = $createSave->id;
				$saved_query = $createSave->query;
			}

			header('Content-Type: text/xml; charset=UTF-8');
			$result  = '<?xml version="1.0" encoding="utf-8"?><response>';
			$result .= '<error>' . $errorMSG . '</error>';
			$result .= '<savedID>' . $saved_id . '</savedID>';
			$result .= '<savedQuery>' . $saved_query . '</savedQuery>';
			$result .= '</response>';
			echo $result;
			flush();
		}
	}
}

// Twitter Destroy Saved Searches
function getDestroySavedSearches($target) {
	global $service, $pluginURL, $blogURL, $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);

	$id = (isset($_POST['qdata']) && !empty($_POST['qdata'])) ? $_POST['qdata'] : '';
	$result = '';
	if (!empty($data['username']) && !empty($data['password'])) {
		if (!empty($id)) {
			$tw = new Twitter($data['username'], $data['password']);
			$destroySave = $tw->destroySavedSearches($id);
			$errorMSG = empty($destroySave->error) ? "0" : $destroySave->error;
			$saved_id = "";
			if ($errorMSG == "0") {
				$saved_id = $destroySave->id;
				$saved_query = $destroySave->query;
			}

			header('Content-Type: text/xml; charset=UTF-8');
			$result  = '<?xml version="1.0" encoding="utf-8"?><response>';
			$result .= '<error>' . $errorMSG . '</error>';
			$result .= '<savedID>' . $saved_id . '</savedID>';
			$result .= '<savedQuery>' . $saved_query . '</savedQuery>';
			$result .= '</response>';
			echo $result;
			flush();
		}
	}
}

// Twitter management (Admin->center->Twitter management)
function PN_Twitter_Management() {
	global $service, $pluginURL, $blogURL, $pluginMenuURL, $pluginName, $handler, $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);

	$savedSearchesView = !is_null(Setting::getBlogSetting("saved_searches")) ? Setting::getBlogSetting("saved_searches") : "open";
	$menu = (isset($_GET['menu']) && !empty($_GET['menu'])) ? $_GET['menu'] : 'friends';
	$user_id = (isset($_GET['user_id']) && !empty($_GET['user_id'])) ? $_GET['user_id'] : '';
	$seleteTab = (isset($_GET['seleteTab']) && !empty($_GET['seleteTab'])) ? $_GET['seleteTab'] : 'inbox';
	$search_q = (isset($_GET['q']) && !empty($_GET['q'])) ? $_GET['q'] : '';

	$twFlagCHK = "";
	$twFlagMSG = "";
	if (empty($data['username']) || empty($data['password'])) {
		$twFlagCHK = "disabled";
		$twFlagMSG = " * 트위터 사용자 정보를 설정해야 합니다.\n * 아이디와 비밀번호를 입력하세요.\n";
	}
?>
	<script type="text/javascript">
		var viewMode = "full"; 
		var nowmenu = "<?php echo $menu;?>";
		var pluginMenuURL = "<?php echo $pluginMenuURL;?>";
		var puser_id = "<?php echo $user_id;?>";
		var seleteTab = "<?php echo $seleteTab;?>";
		var listLength = "<?php echo $data['listLength'];?>";
	</script>
	<script type="text/javascript" src="<?php echo $pluginURL;?>/script/jquery.preview.text.js"></script>
	<script type="text/javascript" src="<?php echo $pluginURL;?>/script/jquery.livequery.js"></script>
	<script type="text/javascript" src="<?php echo $pluginURL;?>/script/twitter.js"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $pluginURL;?>/style/twitter.css" />
<?php
	if ($twFlagCHK != "disabled") {
		$tw = new Twitter($data['username'], $data['password']);
		
		$acount = $tw->verifyCredentials();
		$acount_id = $acount->id;
		$acount_name = $acount->name;
		$acount_screen_name = $acount->screen_name;
		$acount_link = "http://twitter.com/" . $acount_screen_name;
		$acount_image = $acount->profile_image_url;
		$following_count = $acount->friends_count;
		$followers_count = $acount->followers_count;
		$updates_count = $acount->statuses_count;
		$description = $acount->description;
?>
		<div id="part-twitter-info" class="part">
			<h2 class="caption"><span class="main-text">트위터 정보</span></h2>
			<div class="data-inbox">
				<div class="tw_sidebar">
					<div class="acount_info">
						<a href="<?php echo $acount_link;?>" title="<?php echo $acount_name;?>" onclick="return false;">
							<img src="<?php echo $acount_image;?>" alt="<?php echo $acount_name;?> - profile image" class="acount_img" />
							<span class="acount_name"><?php echo $acount_screen_name;?></span>
							<div class="clear"></div>
						</a>
					</div>
					<div class="follow_info">
						<a href="<?php echo $pluginMenuURL;?>&menu=following" title="See who you’re following" class="following">
							<span id="following_count" class="stats_count"><?php echo $following_count;?></span>
							<span class="label">Following</span>
						</a>
						<a href="<?php echo $pluginMenuURL;?>&menu=followers" title="See who’s following you" class="followers">
							<span id="followers_count" class="stats_count"><?php echo $followers_count;?></span>
							<span class="label">Followers</span>
						</a>
						<a href="<?php echo $pluginMenuURL;?>&menu=my" title="See all your updates" class="updates">
							<span id="updates_count" class="stats_count"><?php echo $updates_count;?></span>
							<span class="label">Updates</span>
						</a>
						<div class="clear"></div>
					</div>
					<div class="desc_info">
						<div class="description"><?php echo $description;?></div>
					</div>
					<div class="side_menu">
						<a href="<?php echo $pluginMenuURL;?>&menu=friends" title="my friends" class="sideMenu <?php echo ($menu == 'friends' ? ' selectMenu' : '');?>">All friends</a>
						<a href="<?php echo $pluginMenuURL;?>&menu=my" title="my profile" class="sideMenu <?php echo ($menu == 'my' ? ' selectMenu' : '');?>"><?php echo $acount_screen_name;?></a>
						<a href="<?php echo $pluginMenuURL;?>&menu=replies" title="my replies" class="sideMenu <?php echo ($menu == 'replies' ? ' selectMenu' : '');?>">@<?php echo $acount_screen_name;?></a>
						<a href="<?php echo $pluginMenuURL;?>&menu=direct" title="my direct messages" class="sideMenu <?php echo ($menu == 'direct' ? ' selectMenu' : '');?>">Direct Messages</a>
						<a href="<?php echo $pluginMenuURL;?>&menu=favorites" title="my favorites" class="sideMenu <?php echo ($menu == 'favorites' ? ' selectMenu' : '');?>">Favorites</a>
					</div>
					<div class="custom_search">
						<div class="clear"></div>
						<form id="sidebar_search" class="search" method="get" action="<?php echo $pluginMenuURL;?>&menu=search" onsubmit="return false;">
							<input type="hidden" name="name" value="<?php echo $pluginName."/".$handler;?>" />
							<input type="hidden" name="menu" value="search" />
							<input id="sidebar_search_q" class="search_q round-left" type="text" value="<?php echo $search_q;?>" title="Search" name="q" />
							<a href="#" id="sidebar_search_submit" class="search_submit round-right" title="Search"> </a>
						</form>
						<div class="clear"></div>
					</div>
					<div class="saved_searches <?php echo ($savedSearchesView == 'close' ? 'collapsed' : '');?>">
						<div class="saved_searches_list">Saved Searches</div>
<?php
						$searchesList = '';
						$savedMatchID = '';
						$searches = $tw->savedSearches();
						$searchesList = '<ul id="my_saved_searches" class="searches_list ' . ($savedSearchesView == 'close' ? 'searches_hide' : '') . '">';
						if (count($searches) > 0) {
							foreach ($searches as $search) {
								$searchesList .= '<li id="search_' . $search->id . '" class="search_list">';
								$searchesList .= '<a href="' . $pluginMenuURL . '&menu=search&q=' . urlencode($search->query) . '" title="' . $search->name . '" ' . (strtolower($search_q) == strtolower($search->query) ? 'class="selectMenu"' : '') . ' >' . $search->name . '</a>';
								$searchesList .= '</li>';
								if (strtolower($search_q) == strtolower($search->query)) {
									$savedMatchID = $search->id;
								}
							}
						}
						$searchesList .= '</ul>';
						echo $searchesList;
?>
					</div>
<?php
//					<div class="friends_photo">
//						//following friends
//						$user_photo = "";
//						$limit = 0;
//						$option = array('page'=>1);
//						$friends = $tw->getFriends($option);
//						foreach ($friends as $user) {
//							$limit++;
//							$user_name = $user->name;
//							$user_screen_name = $user->screen_name;
//							$user_image = $user->profile_image_url;
//							$user_image = str_replace('_normal', '_mini', $user_image);
//							$user_link = "http://twitter.com/" . $user_screen_name;
//							$user_photo .= '<a href="' . $user_link . '" class="friend_photo" title="' . $user_name . '"><img src="' . $pluginURL . '/images/blank.gif" alt="' . $user_name . ' - profile image." style="background:url('.$user_image.') center center #ffffff no-repeat;" /></a>';
//							if ($limit == 30) break;
//						}
//						echo $user_photo;
//						<div class="following"><a href="< ?php echo $pluginMenuURL;? >&menu=following" class="sideMenu">View All...</a></div>
//					</div>
?>
				</div>
			</div>
		</div>
		<hr class="hidden" />

		<div id="part-twitter-contents" class="part">
			<h2 class="caption"><span class="main-text">트위터</span></h2>
			<div id="content-box">
				<div class="top_bar">
					<h3>
<?php
					if ($menu == "direct") {
						$doingTitle = 'Send <select id="direct_message_user_id" class="userSelect"></select> a direct message.';
					} else {
						$doingTitle = "What are you doing?";
					}
?>
						<label class="doing" for="status"><?php echo $doingTitle;?></label>
					</h3>
					<span id="textLength" class="char-counter">140자 남음</span>
				</div>
				<div id="twUpdateForm">
					<input type="hidden" id="disabledChk" value="<?php echo $twFlagCHK;?>" />
					<input type="hidden" id="in_reply_to_status_id" value="" />
					<input type="hidden" id="direct_message_to_user_id" value="" />
					<textarea id="update_body" class="textarea_full" <?php echo $twFlagCHK;?>><?php echo $twFlagMSG;?></textarea><br />
					<div class="submit_bar">
						<div class="shorten_line">
							<input type="text" id="ShortenURL" onkeypress="if (event.keyCode == 13) { return false; }" <?php echo $twFlagCHK;?> class="shortenLink" style="" />
							<input id="link_button" type="button" value="짧은 URL 생성" <?php echo $twFlagCHK;?> class="shortenURLCreate tw_button" />
						</div>
						<div class="submit_line">
							<input id="update_button" type="button" value="전송하기" <?php echo $twFlagCHK;?> class="tw_button" />
						</div>
					</div>
				</div>
				<div class="clear"></div>
				<div id="timeline_heading">
<?php
	$menuLabel = "All friends";
	if ($menu == 'friends') {
		$menuLabel = $acount_screen_name . "'s all friends";
	} else if ($menu == 'my') {
		$menuLabel = $acount_screen_name . "'s Updates";
	} else if ($menu == 'replies') {
		$menuLabel = "@" . $acount_screen_name . "'s Replies";;
	} else if ($menu == 'direct') {
		$menuLabel = $acount_screen_name . "'s Direct messages";
	} else if ($menu == 'favorites') {
		$menuLabel = $acount_screen_name . "'s Favorites";
	} else if ($menu == 'following') {
		$menuLabel = $acount_screen_name . "'s Following users";
	} else if ($menu == 'followers') {
		$menuLabel = $acount_screen_name . "'s Followers users";
	} else if ($menu == 'search') {
		$menuLabel = "Real-time results for '" . $search_q . "'";
	}
?>
					<h3 id="heading">
					<?php 
						echo $menuLabel;
						if ($menu == 'search') {
							$savedHtml  = '<span id="searchLink">';
							if (empty($savedMatchID)) {
								$savedHtml .= '<a href="#" id="saved_link" class="saveSearchLink " title="' . $search_q . '">Save this search</a>';
							} else {
								$savedHtml .= '<a href="#" id="saved_link" class="deleteSearchLink" rel="' . $savedMatchID . '" title="' . $search_q . '">Remove this saved search</a>';
							}
							$savedHtml .= '</span>';
							echo $savedHtml;
						}
					?>
					</h3>
				</div>
<?php
	if ($menu == "direct") {
?>
				<ul id="dm_tabs" class="tabMenu">
					<li id="inbox_tab">
						<a class="msgTab <?php echo ($seleteTab == "inbox" ? "seletedTab" : "");?>" href="<?php echo $pluginMenuURL;?>&menu=direct&seleteTab=inbox">Inbox</a>
					</li>
					<li id="sent_tab" class="">
						<a class="msgTab <?php echo ($seleteTab == "sent" ? "seletedTab" : "");?>" href="<?php echo $pluginMenuURL;?>&menu=direct&seleteTab=sent">Sent</a>
					</li>
				</ul>
				<div class="clear"></div>
<?php 
	}
?>
				<ul id="Times-Mirror" class="statuses <?php echo ($menu == "direct" ? "directMessage" : "");?>">
<?php
				//friends Timeline
				$user_photo = "";
				$limit = 0;
				
				if ($menu == 'search') {
					$optionNext = array('q'=>$search_q, 'page'=>2, 'rpp'=>$data['listLength']);
					$option = array('q'=>$search_q, 'page'=>1, 'rpp'=>$data['listLength']);
				} else {
					$optionNext = array('page'=>2, 'count'=>$data['listLength']);
					$option = array('page'=>1, 'count'=>$data['listLength']);
				}

				if ($menu == 'friends') {
					$timelineNext = $tw->getFriendsTimeline($optionNext);
					$timeline = $tw->getFriendsTimeline($option);
					count($timeline);
					$timelineResultsNext = $timelineNext;
					$timelineResults = $timeline;
				} else if ($menu == 'my') {
					$timelineNext = $tw->getUserTimeline($optionNext);
					$timeline = $tw->getUserTimeline($option);
					$timelineResultsNext = $timelineNext;
					$timelineResults = $timeline;
				} else if ($menu == 'replies') {
					$timelineNext = $tw->getReplies($optionNext);
					$timeline = $tw->getReplies($option);
					$timelineResultsNext = $timelineNext;
					$timelineResults = $timeline;
				} else if ($menu == 'direct') {
					if ($seleteTab == "inbox") {
						$timelineNext = $tw->getMessages($optionNext);
						$timeline = $tw->getMessages($option);
						$timelineResultsNext = $timelineNext;
						$timelineResults = $timeline;
					} else {
						$timelineNext = $tw->getSentMessages($optionNext);
						$timeline = $tw->getSentMessages($option);
						$timelineResultsNext = $timelineNext;
						$timelineResults = $timeline;
					}
				} else if ($menu == 'favorites') {
					$timelineNext = $tw->getFavorites($optionNext);
					$timeline = $tw->getFavorites($option);
					$timelineResultsNext = $timelineNext;
					$timelineResults = $timeline;
				} else if ($menu == 'following') {
					$timelineNext = $tw->getFriends(array('page'=>2));
					$timeline = $tw->getFriends(array('page'=>1));
					$timelineResultsNext = $timelineNext;
					$timelineResults = $timeline;
				} else if ($menu == 'followers') {
					$timelineNext = $tw->getFollowers(array('page'=>2));
					$timeline = $tw->getFollowers(array('page'=>1));
					$timelineResultsNext = $timelineNext;
					$timelineResults = $timeline;
				} else if ($menu == 'search') {
					$timelineNext = $tw->getSearch($optionNext, 'json');
					$timeline = $tw->getSearch($option, 'json');
					$timelineResultsNext = $timelineNext->results;
					$timelineResults = $timeline->results;
				}
				$nextPagingCount = count($timelineResultsNext);
				foreach ($timelineResults as $status) {
					if ($menu == 'direct') {
						$status_id = $status->id;
						if ($seleteTab == "inbox") {
							$user_id = $status->sender->id;
							$user_name = $status->sender->name;
							$user_screen_name = $status->sender->screen_name;
							$user_link = "https://twitter.com/{$user_screen_name}";
							$profile_image = $status->sender->profile_image_url;
						} else {
							$user_id = $status->recipient->id;
							$user_name = $status->recipient->name;
							$user_screen_name = $status->recipient->screen_name;
							$user_link = "https://twitter.com/{$user_screen_name}";
							$profile_image = $status->recipient->profile_image_url;
						}
						$text = $status->text;
						$text_convert = getTextBodyConvert($text);
						$source = getTextLinkConvert($status->source);
						$published = getTwitterDateConvert($status->created_at);
						$published_link = "https://twitter.com/{$user_screen_name}/status/{$status_id}";
						$in_reply_to_status_id = "";
						$in_reply_to_screen_name = "";
						$in_reply_to_link = "";
					} else if ($menu == 'following' || $menu == 'followers') {
						$user_id = $status->id;
						$user_name = $status->name;
						$user_screen_name = $status->screen_name;
						$user_link = "https://twitter.com/{$user_screen_name}";
						$profile_image = $status->profile_image_url;
						$profile_image = str_replace('_normal.', '_mini.', $profile_image);
						$location = !empty($status->location) ? ":: " . $status->location : "";
						$following = !empty($status->following) ? $status->following : 'false';
						$description = $status->description;
					} else if ($menu == 'search') {
						$status_id = $status->id;
						$user_id = $status->from_user_id;
						$user_name = $status->from_user;
						$user_screen_name = $status->from_user;
						$user_link = "https://twitter.com/{$user_screen_name}";
						$profile_image = $status->profile_image_url;
						$text = $status->text;
						$text_convert = getTextBodyConvert($text);
						$text_convert =	getBindSearchHighlight($search_q, $text_convert);
						$source = getTextLinkConvert($status->source);
						$published = getTwitterDateConvert($status->created_at);
						$published_link = "https://twitter.com/{$user_screen_name}/status/{$status_id}";
					} else {
						$status_id = $status->id;
						$user_id = $status->user->id;
						$user_name = $status->user->name;
						$user_screen_name = $status->user->screen_name;
						$user_link = "https://twitter.com/{$user_screen_name}";
						$profile_image = $status->user->profile_image_url;
						$text = $status->text;
						$text_convert = getTextBodyConvert($text);
						$source = getTextLinkConvert($status->source);
						$published = getTwitterDateConvert($status->created_at);
						$published_link = "https://twitter.com/{$user_screen_name}/status/{$status_id}";
						$favorited = $status->favorited;
						$in_reply_to_status_id = !empty($status->in_reply_to_status_id) ? $status->in_reply_to_status_id : "";
						$in_reply_to_screen_name = !empty($status->in_reply_to_screen_name) ? $status->in_reply_to_screen_name : "";
						$in_reply_to_link = !empty($in_reply_to_status_id) ? '&nbsp;<a href="http://twitter.com/' . $in_reply_to_screen_name . '/status/' . $in_reply_to_status_id . '" title="in reply to ' . $in_reply_to_screen_name . '" onclick="window.open(this.href); return false;">in reply to ' . $in_reply_to_screen_name . '</a>' : "";
					}

					$metaActionHtml  = "";
					if (!in_array($menu, array("direct", 'following','followers'))) {
						$metaActionHtml .= '<a id="favorite_' . $status_id . '" href="#" class="fav-action ' . ($favorited == 'true' ? 'fav' : 'non-fav') . '" title="' . ($favorited == 'true' ? 'un-' : '') . 'favorite this update"></a>';
					}
					if (($data['username'] != $user_screen_name) && !in_array($menu, array("direct", 'following','followers'))) {
						$metaActionHtml .= '<a id="retweet_' . $status_id . '" href="#" class="retweet" rel="' . $user_screen_name . '" title="retweet this update"></a>';
					}
					if ($data['username'] != $user_screen_name  && $menu != "direct") {
						$metaActionHtml .= '<a id="reply_' . $status_id . '" href="#" class="reply" rel="' . $user_screen_name . '" title="reply to ' . $user_screen_name . '"></a>';
					} 
					if ($menu == "direct" || $menu == "followers") {
						$metaActionHtml .= '<a id="message_user_' . $user_id . '" href="#" class="message" rel="' . $user_screen_name . '" title="message to ' . $user_screen_name . '"></a>';
					}
					if (($data['username'] == $user_screen_name) || $menu == "direct") {
						$metaActionHtml .= '<a href="#" class="delete" rel="' . $status_id . '" title="delete this update"></a>';
					}
					if (($menu == 'following' || $menu == 'followers') && $following == 'true') {
						$metaActionHtml .= '<a id="following_' . $user_id . '" href="#" class="follow_act unfollow" title="Unfollow ' . $user_screen_name . '"></a>';
						if ($menu == 'followers') {
							$metaActionHtml .= '<span id="followingMSG_' . $user_id . '" class="following"></span>';
						}
					} else if ($menu == 'followers' && $following == 'false') {
						$metaActionHtml .= '<a id="followers_' . $user_id . '" href="#" class="follow_act follow" title="Follow ' . $user_screen_name . '"></a>';
						$metaActionHtml .= '<span id="followingMSG_' . $user_id . '" class="following fhide"></span>';
					}

					if ($menu == 'following' || $menu == 'followers') {
?>
					<li id="user_<?php echo $user_id;?>">
						<div class="listable">
							<span class="thumb thumbmini">
								<a class="url" href="<?php echo $user_link;?>" title="<?php echo $user_name;?>" onclick="window.open(this.href); return false;">
									<img class="photo mini" src="<?php echo $profile_image;?>" alt="<?php echo $user_name;?>"/>
								</a>
							</span>
						</div>
						<span class="status-body bodymini">
							<strong>
								<a class="screen-name" title="<?php echo $user_name;?>" href="<?php echo $user_link;?>" onclick="window.open(this.href); return false;"><?php echo $user_screen_name;?></a>
							</strong>
							<span class="entry-content desc"><?php echo UTF8::lessenAsEm(UTF8::convert($description,'utf-8'), 100, '...');?></span>
							<div class="meta_info">
								<span class="meta">
									<span class="user_name"><?php echo $user_name;?></span>
									<span><?php echo $location;?></span>
								</span>
								<span class="meta_action">
									<?php echo $metaActionHtml;?>
								</span>
								<div class="clear"></div>
							</div>
						</span>
					</li>
<?php
					} else {
?>
					<li id="status_<?php echo $status_id;?>">
						<div class="listable">
							<span class="thumb">
								<a class="url" href="<?php echo $user_link;?>">
									<img class="photo" src="<?php echo $profile_image;?>" alt="<?php echo $user_name;?>"/>
								</a>
							</span>
						</div>
						<span class="status-body">
							<strong>
								<a class="screen-name" title="<?php echo $user_name;?>" href="<?php echo $user_link;?>"><?php echo $user_screen_name;?></a>
							</strong>
							<span id="content_<?php echo $status_id;?>" class="entry-content"><?php echo $text_convert;?></span>
							<div class="meta_info">
								<span class="meta">
									<?php if ($menu != 'direct') { ?><a class="entry-date" rel="bookmark" href="<?php echo $published_link;?>" onclick="window.open(this.href); return false;"><?php } ?>
										<span class="published"><?php echo $published;?></span>
									<?php if ($menu != 'direct') { ?></a><?php } ?>
									<?php if ($menu != 'direct') { ?>
									<span>from <?php echo $source;?> <?php echo $in_reply_to_link;?></span>
									<?php } ?>
								</span>
								<span class="meta_action">
									<?php echo $metaActionHtml;?>
								</span>
								<div class="clear"></div>
							</div>
						</span>
					</li>
<?php
					}
				}
?>
				</ul>
				<div id="pagination">
				<?php
					if ($nextPagingCount > 0) {
						if ($menu == "direct") {
							$paramTab = "&seleteTab=" . ($seleteTab == "inbox" ? "inbox" : "sent");
						} else if ($menu == "search") {
							$paramTab = "&q=" . urlencode($search_q);
						} else {
							$paramTab = "";
						}
				?>
					<a href="<?php echo $blogURL;?>/plugin/twitterUpateMorePaging/?page=2&menu=<?php echo $menu;?><?php echo $paramTab;?>" id="more" class="round more">MORE</a>
				<?php
					}
				?>
			</div>
			</div>
		</div>
<?php
	} else {
?>
		<div id="part-error" class="part">
			<h2 class="caption"><span class="main-text">트위터 환경설정 오류</span></h2>
			<div class="data-inbox">
				<p>트위터 사용자 정보를 설정해야 합니다. 아이디와 비밀번호를 다시 확인해보세요.</p>
			</div>
		</div>
<?php
	}
?>
		<div class="clear"></div>
<?php
}

// Twitter more paging
function getTwitterUpateMorePaging($target) {
	global $service, $pluginURL, $blogURL, $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);

	$page = (isset($_GET['page']) && !empty($_GET['page'])) ? $_GET['page'] : '1';
	$menu = (isset($_GET['menu']) && !empty($_GET['menu'])) ? $_GET['menu'] : 'friends';
	$seleteTab = (isset($_GET['seleteTab']) && !empty($_GET['seleteTab'])) ? $_GET['seleteTab'] : 'inbox';
	$search_q = (isset($_GET['q']) && !empty($_GET['q'])) ? $_GET['q'] : '';

	$tw = new Twitter($data['username'], $data['password']);

	$user_photo = "";
	$limit = 0;
	if ($menu == 'search') {
		$optionNext = array('q'=>$search_q, 'page'=>($page+1), 'rpp'=>$data['listLength']);
		$option = array('q'=>$search_q, 'page'=>$page, 'rpp'=>$data['listLength']);
	} else {	
		$optionNext = array('page'=>($page+1), 'count'=>$data['listLength']);
		$option = array('page'=>$page, 'count'=>$data['listLength']);
	}
	if ($menu == 'friends') {
		$timelineNext = $tw->getFriendsTimeline($optionNext);
		$timeline = $tw->getFriendsTimeline($option);
		$timelineResultsNext = $timelineNext;
		$timelineResults = $timeline;
	} else if ($menu == 'my') {
		$timelineNext = $tw->getUserTimeline($optionNext);
		$timeline = $tw->getUserTimeline($option);
		$timelineResultsNext = $timelineNext;
		$timelineResults = $timeline;
	} else if ($menu == 'replies') {
		$timelineNext = $tw->getReplies($optionNext);
		$timeline = $tw->getReplies($option);
		$timelineResultsNext = $timelineNext;
		$timelineResults = $timeline;
	} else if ($menu == 'direct') {
		if ($seleteTab == "inbox") {
			$timelineNext = $tw->getMessages($optionNext);
			$timeline = $tw->getMessages($option);
			$timelineResultsNext = $timelineNext;
			$timelineResults = $timeline;
		} else {
			$timelineNext = $tw->getSentMessages($optionNext);
			$timeline = $tw->getSentMessages($option);
			$timelineResultsNext = $timelineNext;
			$timelineResults = $timeline;
		}
	} else if ($menu == 'favorites') {
		$timelineNext = $tw->getFavorites($optionNext);
		$timeline = $tw->getFavorites($option);
		$timelineResultsNext = $timelineNext;
		$timelineResults = $timeline;
	} else if ($menu == 'following') {
		$timelineNext = $tw->getFriends(array('page'=>($page+1)));
		$timeline = $tw->getFriends(array('page'=>$page));
		$timelineResultsNext = $timelineNext;
		$timelineResults = $timeline;
	} else if ($menu == 'followers') {
		$timelineNext = $tw->getFollowers(array('page'=>($page+1)));
		$timeline = $tw->getFollowers(array('page'=>$page));
		$timelineResultsNext = $timelineNext;
		$timelineResults = $timeline;
	} else if ($menu == 'search') {
		$timelineNext = $tw->getSearch($optionNext, 'json');
		$timeline = $tw->getSearch($option, 'json');
		$timelineResultsNext = $timelineNext->results;
		$timelineResults = $timeline->results;
	}

	$morePagingHtml = "";
	$nextPagingCount = count($timelineResultsNext);

	foreach ($timelineResults as $status) {
		if ($menu == 'direct') {
			if ($seleteTab == "inbox") {
				$user_id = $status->sender->id;
				$user_name = $status->sender->name;
				$user_screen_name = $status->sender->screen_name;
				$user_link = "https://twitter.com/{$user_screen_name}";
				$profile_image = $status->sender->profile_image_url;
			} else {
				$user_id = $status->recipient->id;
				$user_name = $status->recipient->name;
				$user_screen_name = $status->recipient->screen_name;
				$user_link = "https://twitter.com/{$user_screen_name}";
				$profile_image = $status->recipient->profile_image_url;
			}
			$text = $status->text;
			$text_convert = getTextBodyConvert($text);
			$source = getTextLinkConvert($status->source);
			$published = getTwitterDateConvert($status->created_at);
			$published_link = "https://twitter.com/{$user_screen_name}/status/{$status_id}";
			$in_reply_to_status_id = "";
			$in_reply_to_screen_name = "";
			$in_reply_to_link = "";
		} else if ($menu == 'following' || $menu == 'followers') {
			$user_id = $status->id;
			$user_name = $status->name;
			$user_screen_name = $status->screen_name;
			$user_link = "https://twitter.com/{$user_screen_name}";
			$profile_image = $status->profile_image_url;
			$profile_image = str_replace('_normal.', '_mini.', $profile_image);
			$location = $status->location;
			$following = !empty($status->following) ? $status->following : 'false';
			$description = $status->description;
		} else if ($menu == 'search') {
			$status_id = $status->id;
			$user_id = $status->from_user_id;
			$user_name = $status->from_user;
			$user_screen_name = $status->from_user;
			$user_link = "https://twitter.com/{$user_screen_name}";
			$profile_image = $status->profile_image_url;
			$text = getTextLinkConvert($status->text);
			$text_convert = getTextBodyConvert($text);
			$text_convert =	getBindSearchHighlight($search_q, $text_convert);
			$source = getTextLinkConvert($status->source);
			$published = getTwitterDateConvert($status->created_at);
			$published_link = "https://twitter.com/{$user_screen_name}/status/{$status_id}";
		} else {
			$status_id = $status->id;
			$user_id = $status->user->id;
			$user_name = $status->user->name;
			$user_screen_name = $status->user->screen_name;
			$user_link = "https://twitter.com/{$user_screen_name}";
			$profile_image = $status->user->profile_image_url;
			$text = $status->text;
			$text_convert = getTextBodyConvert($text);
			$source = getTextLinkConvert($status->source);
			$published = getTwitterDateConvert($status->created_at);
			$published_link = "https://twitter.com/{$user_screen_name}/status/{$status_id}";
			$favorited = $status->favorited;
			$in_reply_to_status_id = !empty($status->in_reply_to_status_id) ? $status->in_reply_to_status_id : "";
			$in_reply_to_screen_name = !empty($status->in_reply_to_screen_name) ? $status->in_reply_to_screen_name : "";
			$in_reply_to_link = !empty($in_reply_to_status_id) ? '&nbsp;<a href="http://twitter.com/' . $in_reply_to_screen_name . '/status/' . $in_reply_to_status_id . '" title="in reply to ' . $in_reply_to_screen_name . '" onclick="window.open(this.href); return false;">in reply to ' . $in_reply_to_screen_name . '</a>' : "";
		}

		$metaActionHtml  = "";
		if (!in_array($menu, array("direct", 'following','followers'))) {
			$metaActionHtml .= '<a href="#" class="fav-action ' . ($favorited == 'true' ? 'fav' : 'non-fav') . '" title="' . ($favorited == 'true' ? 'un-' : '') . 'favorite this update"></a>';
		}
		if (($data['username'] != $user_screen_name) && !in_array($menu, array("direct", 'following','followers'))) {
			$metaActionHtml .= '<a id="retweet_' . $status_id . '" href="#" class="retweet" rel="' . $user_screen_name . '" title="retweet this update"></a>';
		}
		if ($data['username'] != $user_screen_name  && $menu != "direct") {
			$metaActionHtml .= '<a id="reply_' . $status_id . '" href="#" class="reply" rel="' . $user_screen_name . '" title="reply to ' . $user_screen_name . '"></a>';
		}
		if ($menu == "direct" || $menu == "followers") {
			$metaActionHtml .= '<a href="#" class="message" rel="' . $user_screen_name . '" title="message to ' . $user_screen_name . '"></a>';
		}
		if (($data['username'] == $user_screen_name) || $menu == "direct") {
			$metaActionHtml .= '<a href="#" class="delete" rel="' . $status_id . '" title="delete this update"></a>';
		}
		if (($menu == 'following' || $menu == 'followers') && $following == 'true') {
			$metaActionHtml .= '<a id="following_' . $user_id . '" href="#" class="follow_act unfollow" title="Unfollow ' . $user_screen_name . '"></a>';
			if ($menu == 'followers') {
				$metaActionHtml .= '<span id="followingMSG_' . $user_id . '" class="following"></span>';
			}
		} else if ($menu == 'followers' && $following == 'false') {
			$metaActionHtml .= '<a id="followers_' . $user_id . '" href="#" class="follow_act follow" title="Follow ' . $user_screen_name . '"></a>';
			$metaActionHtml .= '<span id="followingMSG_' . $user_id . '" class="following fhide"></span>';
		}

		if ($menu == 'following' || $menu == 'followers') {
			$morePagingHtml .= '<li id="user_' . $user_id .'">'.CRLF;
			$morePagingHtml .= '	<div class="listable">'.CRLF;
			$morePagingHtml .= '		<span class="thumb thumbmini">'.CRLF;
			$morePagingHtml .= '			<a class="url" href="' . $user_link . '" onclick="window.open(this.href); return false;">'.CRLF;
			$morePagingHtml .= '				<img class="photo mini" src="' . $profile_image . '" alt="' . $user_name . '"/>'.CRLF;
			$morePagingHtml .= '			</a>'.CRLF;
			$morePagingHtml .= '		</span>'.CRLF;
			$morePagingHtml .= '	</div>'.CRLF;
			$morePagingHtml .= '	<span class="status-body bodymini">'.CRLF;
			$morePagingHtml .= '		<strong>'.CRLF;
			$morePagingHtml .= '			<a class="screen-name" title="' . $user_name . '" href="' . $user_link . '" onclick="window.open(this.href); return false;">' . $user_screen_name . '</a>'.CRLF;
			$morePagingHtml .= '		</strong>'.CRLF;
			$morePagingHtml .= '		<span class="entry-content desc">' . UTF8::lessenAsEm(UTF8::convert($description,'utf-8'), 100, '...') . '</span>'.CRLF;
			$morePagingHtml .= '		<div class="meta_info">'.CRLF;
			$morePagingHtml .= '			<span class="meta">'.CRLF;
			$morePagingHtml .= '				<span class="user_name">' . $user_name . '</span>'.CRLF;
			$morePagingHtml .= '				<span>| ' . $location . '</span>'.CRLF;
			$morePagingHtml .= '			</span>'.CRLF;
			$morePagingHtml .= '			<span class="meta_action">'.CRLF;
			$morePagingHtml .= '				' . $metaActionHtml .CRLF;
			$morePagingHtml .= '			</span>'.CRLF;
			$morePagingHtml .= '			<div class="clear"></div>'.CRLF;
			$morePagingHtml .= '		</div>'.CRLF;
			$morePagingHtml .= '	</span>'.CRLF;
			$morePagingHtml .= '</li>'.CRLF;
		} else {
			$morePagingHtml .= '<li id="status_' . $status_id .'">'.CRLF;
			$morePagingHtml .= '	<div class="listable">'.CRLF;
			$morePagingHtml .= '		<span class="thumb">'.CRLF;
			$morePagingHtml .= '			<a class="url" href="' . $user_link . '">'.CRLF;
			$morePagingHtml .= '				<img class="photo" src="' . $profile_image . '" alt="' . $user_name . '"/>'.CRLF;
			$morePagingHtml .= '			</a>'.CRLF;
			$morePagingHtml .= '		</span>'.CRLF;
			$morePagingHtml .= '	</div>'.CRLF;
			$morePagingHtml .= '	<span class="status-body">'.CRLF;
			$morePagingHtml .= '		<strong>'.CRLF;
			$morePagingHtml .= '			<a class="screen-name" title="' . $user_name . '" href="' . $user_link . '" onclick="window.open(this.href); return false;">' . $user_screen_name . '</a>'.CRLF;
			$morePagingHtml .= '		</strong>'.CRLF;
			$morePagingHtml .= '		<span id="content_' . $status_id .'" class="entry-content">' . $text_convert . '</span>'.CRLF;
			$morePagingHtml .= '		<div class="meta_info">'.CRLF;
			$morePagingHtml .= '			<span class="meta">'.CRLF;
			if ($menu != 'direct') {
				$morePagingHtml .= '				<a class="entry-date" rel="bookmark" href="' . $published_link . '">'.CRLF;
			}
			$morePagingHtml .= '					<span class="published">' . $published . '</span>'.CRLF;
			if ($menu != 'direct') {
				$morePagingHtml .= '				</a>'.CRLF;
			}
			if ($menu != 'direct') {
				$morePagingHtml .= '				<span>from ' . $source . ' ' . $in_reply_to_link . '</span>'.CRLF;
			}
			$morePagingHtml .= '			</span>'.CRLF;
			$morePagingHtml .= '			<span class="meta_action">'.CRLF;
			$morePagingHtml .= '				' . $metaActionHtml .CRLF;
			$morePagingHtml .= '			</span>'.CRLF;
			$morePagingHtml .= '			<div class="clear"></div>'.CRLF;
			$morePagingHtml .= '		</div>'.CRLF;
			$morePagingHtml .= '	</span>'.CRLF;
			$morePagingHtml .= '</li>'.CRLF;
		}
	}
	$morePagination = "";
	if ($nextPagingCount > 0) {
		if ($menu == "direct") {
			$paramTab = "&seleteTab=" . ($seleteTab == "inbox" ? "inbox" : "sent");
		} else if ($menu == "search") {
			$paramTab = "&q=" . urlencode($search_q);
		} else {
			$paramTab = "";
		}
		$morePagination = '<a href="' . $blogURL . '/plugin/twitterUpateMorePaging/?page=' . ($page+1) . '&menu=' . $menu . $paramTab .'" id="more" class="round more">MORE</a>';
	}
	header('Content-Type: text/xml; charset=UTF-8');
	echo '<?xml version="1.0" encoding="utf-8"?><response><morePaging><![CDATA[' . $morePagingHtml . ']]></morePaging><pagination><![CDATA[' . $morePagination . ']]></pagination></response>';
	flush();
}

function getTwitterPostUpdate($target) {
	global $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);
	$menu = (isset($_POST['menu']) && !empty($_POST['menu'])) ? $_POST['menu'] : "";
	$update_body = (isset($_POST['body']) && !empty($_POST['body'])) ? $_POST['body'] : "";
	$reply_to = (isset($_POST['reply_to']) && !empty($_POST['reply_to'])) ? $_POST['reply_to'] : null;
//	$update_body .= $data['hashTag'] == "1" ? " " . $data['hashTags'] : "";
	$result = '';
	if (!empty($data['username']) && !empty($data['password'])) {
		if (!empty($update_body)) {
			$updatedHtml = "";
			$tw = new Twitter($data['username'], $data['password']);
			$updated = $tw->updateStatus($update_body, $reply_to);

			$status_id = $updated->id;
			$user_id = $updated->user->id;
			$user_name = $updated->user->name;
			$user_screen_name = $updated->user->screen_name;
			$user_link = "https://twitter.com/{$user_screen_name}";
			$profile_image = $updated->user->profile_image_url;
			$text = $updated->text;
			$text_convert = getTextBodyConvert($text);
			$source = getTextLinkConvert($updated->source);
			$published = getTwitterDateConvert($updated->created_at);
			$published_link = "https://twitter.com/{$user_screen_name}/status/{$status_id}";
			$favorited = $updated->favorited;
			$in_reply_to_status_id = !empty($updated->in_reply_to_status_id) ? $updated->in_reply_to_status_id : "";
			$in_reply_to_screen_name = !empty($updated->in_reply_to_screen_name) ? $updated->in_reply_to_screen_name : "";
			$in_reply_to_link = !empty($in_reply_to_status_id) ? '&nbsp;<a href="http://twitter.com/' . $in_reply_to_screen_name . '/status/' . $in_reply_to_status_id . '" title="in reply to ' . $in_reply_to_screen_name . '" onclick="window.open(this.href); return false;">in reply to ' . $in_reply_to_screen_name . '</a>' : "";

			$metaActionHtml  = "";
			if ($menu != "direct") {
				$metaActionHtml .= '<a href="#" class="fav-action ' . ($favorited == 'true' ? 'fav' : 'non-fav') . '" title="' . ($favorited == 'true' ? 'un-' : '') . 'favorite this update"></a>';
			}
			if (($data['username'] != $user_screen_name) && $menu != "direct") {
				$metaActionHtml .= '<a id="retweet_' . $status_id . '" href="#" class="retweet" rel="' . $user_screen_name . '" title="retweet this update"></a>';
			}
			if ($data['username'] != $user_screen_name  && $menu != "direct") {
				$metaActionHtml .= '<a id="reply_' . $status_id . '" href="#" class="reply" rel="' . $user_screen_name . '" title="reply to ' . $user_screen_name . '"></a>';
			}
			if ($menu == "direct" || $menu == "followers") {
				$metaActionHtml .= '<a href="#" class="message" rel="' . $user_screen_name . '" title="message to ' . $user_screen_name . '"></a>';
			}
			if (($data['username'] == $user_screen_name) || $menu == "direct") {
				$metaActionHtml .= '<a href="#" class="delete" rel="' . $status_id . '" title="delete this update"></a>';
			}

			$updatedHtml .= '<li id="status_' . $status_id .'" class="updatePost">'.CRLF;
			$updatedHtml .= '	<div class="listable">'.CRLF;
			$updatedHtml .= '		<span class="thumb">'.CRLF;
			$updatedHtml .= '			<a class="url" href="' . $user_link . '">'.CRLF;
			$updatedHtml .= '				<img class="photo" src="' . $profile_image . '" alt="' . $user_name . '"/>'.CRLF;
			$updatedHtml .= '			</a>'.CRLF;
			$updatedHtml .= '		</span>'.CRLF;
			$updatedHtml .= '	</div>'.CRLF;
			$updatedHtml .= '	<span class="status-body">'.CRLF;
			$updatedHtml .= '		<strong>'.CRLF;
			$updatedHtml .= '			<a class="screen-name" title="' . $user_name . '" href="' . $user_link . '">' . $user_screen_name . '</a>'.CRLF;
			$updatedHtml .= '		</strong>'.CRLF;
			$updatedHtml .= '		<span id="content_' . $status_id .'" class="entry-content">' . $text_convert . '</span>'.CRLF;
			$updatedHtml .= '		<div class="meta_info">'.CRLF;
			$updatedHtml .= '			<span class="meta">'.CRLF;
			$updatedHtml .= '				<a class="entry-date" rel="bookmark" href="' . $published_link . '" onclick="window.open(this.href); return false;">'.CRLF;
			$updatedHtml .= '					<span class="published">' . $published . '</span>'.CRLF;
			$updatedHtml .= '				</a>'.CRLF;
			$updatedHtml .= '				<span>from ' . $source . ' ' . $in_reply_to_link . '</span>'.CRLF;
			$updatedHtml .= '			</span>'.CRLF;
			$updatedHtml .= '			<span class="meta_action">'.CRLF;
			$updatedHtml .= '				' . $metaActionHtml .CRLF;;
			$updatedHtml .= '			</span>'.CRLF;
			$updatedHtml .= '			<div class="clear"></div>'.CRLF;
			$updatedHtml .= '		</div>'.CRLF;
			$updatedHtml .= '	</span>'.CRLF;
			$updatedHtml .= '</li>'.CRLF;

			header('Content-Type: text/xml; charset=UTF-8');
			$result  = '<?xml version="1.0" encoding="utf-8"?><response>';
			$result .= '<error>0</error>';
			$result .= '<id>' . $status_id . '</id>';
			$result .= '<updatedStatus><![CDATA[' . $updatedHtml . ']]></updatedStatus>';
			$result .= '</response>';
			echo $result;
			flush();
		}
	}
}

function getTwitterDestroyStatus($target) {
	global $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);
	$id = (isset($_POST['id']) && !empty($_POST['id'])) ? $_POST['id'] : "";

	$result = '';
	if (!empty($data['username']) && !empty($data['password'])) {
		if (!empty($id)) {
			$tw = new Twitter($data['username'], $data['password']);
			$status = $tw->destroyStatus($id);
			$errorMSG = empty($status->error) ? "0" : $status->error;
			$status_id = "";
			if ($errorMSG == "0") {
				$status_id = $status->id;
			}

			header('Content-Type: text/xml; charset=UTF-8');
			$result  = '<?xml version="1.0" encoding="utf-8"?><response>';
			$result .= '<error>' . $errorMSG . '</error>';
			$result .= '<statusID>' . $status_id . '</statusID>';
			$result .= '</response>';
			echo $result;
			flush();
		}
	}
}

function getTwitterNewMessage($target) {
	global $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);
	$menu = (isset($_POST['menu']) && !empty($_POST['menu'])) ? $_POST['menu'] : "";
	$text = (isset($_POST['text']) && !empty($_POST['text'])) ? $_POST['text'] : "";
	$user = (isset($_POST['user']) && !empty($_POST['user'])) ? $_POST['user'] : "";

	$result = '';
	if (!empty($data['username']) && !empty($data['password'])) {
		if (!empty($text)) {
			$updatedHtml = "";
			$tw = new Twitter($data['username'], $data['password']);
			$message = $tw->newMessage($user, $text);
			$errorMSG = empty($message->error) ? "0" : $message->error;
			$message_id = "";
			$messageHtml = "";
			if ($errorMSG == "0") {
				$message_id = $message->id;
				$message_text = $message->text;
				$message_convert = getTextBodyConvert($message_text);
				$published = getTwitterDateConvert($message->created_at);

				$sender_id = $message->sender->id;
				$sender_name = $message->sender->name;
				$sender_screen_name = $message->sender->screen_name;
				$sender_link = "https://twitter.com/{$sender_screen_name}";
				$sender_profile_image = $message->sender->profile_image_url;

				$recipient_id = $message->recipient->id;
				$recipient_name = $message->recipient->name;
				$recipient_screen_name = $message->recipient->screen_name;
				$recipient_link = "https://twitter.com/{$recipient_screen_name}";
				$recipient_profile_image = $message->recipient->profile_image_url;

				if ($menu == "direct" || $menu == "followers") {
					$metaActionHtml .= '<a href="#" class="message" rel="' . $user_screen_name . '" title="message to ' . $user_screen_name . '"></a>';
				}
				if (($data['username'] == $sender_screen_name) || $menu == "direct") {
					$metaActionHtml .= '<a href="#" class="delete" rel="' . $message_id . '" title="delete this update"></a>';
				}

				$messageHtml .= '<li id="status_' . $message_id .'" class="updatePost">'.CRLF;
				$messageHtml .= '	<div class="listable">'.CRLF;
				$messageHtml .= '		<span class="thumb">'.CRLF;
				$messageHtml .= '			<a class="url" href="' . $recipient_link . '">'.CRLF;
				$messageHtml .= '				<img class="photo" src="' . $recipient_profile_image . '" alt="' . $recipient_name . '"/>'.CRLF;
				$messageHtml .= '			</a>'.CRLF;
				$messageHtml .= '		</span>'.CRLF;
				$messageHtml .= '	</div>'.CRLF;
				$messageHtml .= '	<span class="status-body">'.CRLF;
				$messageHtml .= '		<strong>'.CRLF;
				$messageHtml .= '			<a class="screen-name" title="' . $recipient_name . '" href="' . $recipient_link . '">' . $recipient_screen_name . '</a>'.CRLF;
				$messageHtml .= '		</strong>'.CRLF;
				$messageHtml .= '		<span id="content_' . $message_id .'" class="entry-content">' . $message_convert . '</span>'.CRLF;
				$messageHtml .= '		<div class="meta_info">'.CRLF;
				$messageHtml .= '			<span class="meta">'.CRLF;
				$messageHtml .= '				<span class="published">' . $published . '</span>'.CRLF;
				$messageHtml .= '			</span>'.CRLF;
				$messageHtml .= '			<span class="meta_action">'.CRLF;
				$messageHtml .= '				' . $metaActionHtml .CRLF;;
				$messageHtml .= '			</span>'.CRLF;
				$messageHtml .= '			<div class="clear"></div>'.CRLF;
				$messageHtml .= '		</div>'.CRLF;
				$messageHtml .= '	</span>'.CRLF;
				$messageHtml .= '</li>'.CRLF;
			}

			header('Content-Type: text/xml; charset=UTF-8');
			$result  = '<?xml version="1.0" encoding="utf-8"?><response>';
			$result .= '<error>' . $errorMSG . '</error>';
			$result .= '<id>' . $message_id . '</id>';
			$result .= '<messageStatus><![CDATA[' . $messageHtml . ']]></messageStatus>';
			$result .= '</response>';
			echo $result;
			flush();
		}
	}
}

function getTwitterDestroyMessage($target) {
	global $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);
	$id = (isset($_POST['id']) && !empty($_POST['id'])) ? $_POST['id'] : "";

	$result = '';
	if (!empty($data['username']) && !empty($data['password'])) {
		if (!empty($id)) {
			$updatedHtml = "";
			$tw = new Twitter($data['username'], $data['password']);
			$message = $tw->destroyMessage($id);
			$errorMSG = empty($message->error) ? "0" : $message->error;
			$message_id = "";
			$messageHtml = "";
			if ($errorMSG == "0") {
				$message_id = $message->id;
			}

			header('Content-Type: text/xml; charset=UTF-8');
			$result  = '<?xml version="1.0" encoding="utf-8"?><response>';
			$result .= '<error>' . $errorMSG . '</error>';
			$result .= '<messageID>' . $message_id . '</messageID>';
			$result .= '</response>';
			echo $result;
			flush();
		}
	}
}

function getTwitterCreateFriendship($target) {
	global $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);
	$menu = (isset($_POST['menu']) && !empty($_POST['menu'])) ? $_POST['menu'] : "";
	$user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $_POST['user_id'] : "";

	$result = '';
	if (!empty($data['username']) && !empty($data['password'])) {
		if (!empty($user_id)) {
			$tw = new Twitter($data['username'], $data['password']);
			$friendship = $tw->createFriendship($user_id);
			$errorMSG = empty($friendship->error) ? "0" : $friendship->error;
			$friendshipID = "";
			if ($errorMSG == "0") {
				$friendshipID = $friendship->id;
				$following = $friendship->following;
				$user_screen_name = $friendship->screen_name;
			}

			header('Content-Type: text/xml; charset=UTF-8');
			$result  = '<?xml version="1.0" encoding="utf-8"?><response>';
			$result .= '<error>' . $errorMSG . '</error>';
			$result .= '<id>' . $friendshipID . '</id>';
			$result .= '<following>' . $following . '</following>';
			$result .= '<name><![CDATA[' . $user_screen_name . ']]></name>';
			$result .= '</response>';
			echo $result;
			flush();
		}
	}
}

function getTwitterDestroyFriendship($target) {
	global $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);
	$menu = (isset($_POST['menu']) && !empty($_POST['menu'])) ? $_POST['menu'] : "";
	$user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $_POST['user_id'] : "";

	$result = '';
	if (!empty($data['username']) && !empty($data['password'])) {
		if (!empty($user_id)) {
			$updatedHtml = "";
			$tw = new Twitter($data['username'], $data['password']);
			$friendship = $tw->destroyFriendship($user_id);
			$errorMSG = empty($friendship->error) ? "0" : $friendship->error;
			$friendshipID = "";
			if ($errorMSG == "0") {
				$friendshipID = $friendship->id;
				$following = $friendship->following;
				$user_screen_name = $friendship->screen_name;
			}

			header('Content-Type: text/xml; charset=UTF-8');
			$result  = '<?xml version="1.0" encoding="utf-8"?><response>';
			$result .= '<error>' . $errorMSG . '</error>';
			$result .= '<id>' . $friendshipID . '</id>';
			$result .= '<following>' . $following . '</following>';
			$result .= '<name><![CDATA[' . $user_screen_name . ']]></name>';
			$result .= '</response>';
			echo $result;
			flush();
		}
	}
}


// Twitter update (Admin->center->dashboard->Twitter update widget)
function CT_Twitter_updating($target){
	global $service, $pluginURL, $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);

	$twFlagCHK = "";
	$twFlagMSG = "";
	if(empty($data['username']) || empty($data['password'])){
		$twFlagCHK = "disabled";
		$twFlagMSG = " * 트위터 사용자 정보를 설정해야 합니다.\n * 아이디와 비밀번호를 입력하세요.\n";
	}
	ob_start();
?>
	<script type="text/javascript">var viewMode = "mini";</script>
	<script type="text/javascript" src="<?php echo $pluginURL;?>/script/jquery.preview.text.js"></script>
	<script type="text/javascript" src="<?php echo $pluginURL;?>/script/twitter.js"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $pluginURL;?>/style/twitter.css" />
	<div id="twCP_outer" style="border:1px solid #d7d7d7; margin-top:5px; width:298px;"><div id="twCP_inner" style="border:4px solid #f4f4f4; padding:4px;">
		<div id="twUpdateForm">
			<input type="hidden" id="disabledChk" value="<?php echo $twFlagCHK;?>" />
			<input type="hidden" id="in_reply_to_status_id" name="in_reply_to_status_id" value="" />
			<textarea id="update_body" <?php echo $twFlagCHK;?>><?php echo $twFlagMSG;?></textarea><br />
			<div align="right">
				<input type="text" id="ShortenURL" onkeypress="if (event.keyCode == 13) { return false; }" <?php echo $twFlagCHK;?> class="shortenLink" style="" />
				<input id="link_button" type="button" value="짧은 URL 생성" <?php echo $twFlagCHK;?> class="shortenURLCreate tw_button" />
			</div>
			<div id="submit" align="right">
				<span id="textLength">140자 남음</span>&nbsp;
				<input  type="button" value="전송하기" <?php echo $twFlagCHK;?> class="update_button_mini tw_button" />
			</div>
		</div>
	</div></div>
<?php
	$target = ob_get_contents();
	ob_end_clean();
	return $target;
}

function getBitlyShortenURLCreate($target) {
	$longURL = (isset($_POST['link']) && !empty($_POST['link'])) ? $_POST['link'] : "";
	$resultShortenURL = getTwitterBitlyShortenURL($longURL);
	header('Content-Type: text/xml; charset=UTF-8');
	echo '<?xml version="1.0" encoding="utf-8"?><response><error>' . $resultShortenURL['error'] . '</error><shortenURL><![CDATA[' . $resultShortenURL['shortUrl'] . ']]></shortenURL></response>';
	flush();
}

function getUpdatingNewPostToTwitter($target, $mother) {
	global $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);

	$blogid = getBlogId();
	if (!empty($data['username']) && !empty($data['password']) && $data['twitterUpdate'] == "public_synd") {
		if ($data['updateText'] == 'title') {
			$content = getTwitterContent($blogid, $target, 'title');
		} else {
			$content = getTwitterContent($blogid, $target, 'content');
		}
		if (in_array($content['visibility'], array("2", "3"))) {
			$tw = new Twitter($data['username'], $data['password']);
			$updated = $tw->updateStatus($content['body']);
		}
	}
	return $target;
}

function getUpdatingNewLineToTwitter($target, $mother) {
	global $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);

	if (!empty($data['username']) && !empty($data['password'])) {
		$content = UTF8::lessenAsEncoding($mother->content,140);
		$tw = new Twitter($data['username'], $data['password']);
		$updated = $tw->updateStatus($content);
	}
	return $target;
}

function getUpdatingNewPostToTwitterSynd($target, $mother) {
	global $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);

	$blogid = getBlogId();
	if (!empty($data['username']) && !empty($data['password']) && $data['twitterUpdate'] == "syndicate") {
		if ($data['updateText'] == 'title') {
			$content = getTwitterContent($blogid, $target, 'title');
		} else {
			$content = getTwitterContent($blogid, $target, 'content');
		}
		if (in_array($content['visibility'], array("2", "3"))) {
			$tw = new Twitter($data['username'], $data['password']);
			$updated = $tw->updateStatus($content['body']);
		}
	}
	return $target;
}

function getTwitterContent($blogid, $entryId, $updateText='title') {
	global $blog, $service, $defaultURL, $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);

	$content = array();
	$tempContentTags = "";
	$entry = getEntry($blogid, $entryId);
	$tempPermalink = $defaultURL . ($blog['useSloganOnPost'] ? "/entry/" . URL::encode($entry['slogan'], $service['useEncodedURL']) : "/" . $entry['id']);
	$resultShortenURL = getTwitterBitlyShortenURL($tempPermalink);
	$tempContentTags = $data['hashTag'] == "1" ? $data['hashTags'] : "";
	$tempContentTags .= " - " . ($resultShortenURL['error'] == "0" ? $resultShortenURL['shortUrl'] : $tempPermalink);
	
	$tempContentLimit = 140 - (strlen($tempContentTags) + 5);
	if ($updateText == 'title') {
		$tempContent  = UTF8::lessenAsEncoding($entry['title'], $tempContentLimit, '...');
	} else {
		$tempContent  = UTF8::lessenAsEncoding(stripHTML(getEntryContentView($blogid, $entry['id'], $entry['content'], $entry['contentFormatter'])), $tempContentLimit, '...');
	}

	$content['body'] = $tempContent . " " . $tempContentTags;
	$content['visibility'] = $entry['visibility'];
	return $content;
}

function getTwitterBitlyShortenURL($longURL) {
	global $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);

	$result = array();
	if (!empty($longURL)) {
		$tw = new Twitter($data['username'], $data['password']);
		$params = array();
		$params['login']	= "textcube";							//It's key for plugin. It does not change.(by jparker2.0@gmail.com)
		$params['apiKey']	= "R_fcfc2c0c70d30aca174fa771c415d2cb"; //It's key for plugin. It does not change.(by jparker2.0@gmail.com)
		$params['longUrl']	= $longURL;
		$shorten = $tw->bitShortenURL($params, 'xml');
		$resultError = $shorten->results->nodeKeyVal->errorCode;
		$result['error'] = empty($resultError) ? $shorten->errorCode : $resultError;
		if ($result['error'] == "0") {
			$result['shortUrl'] = $shorten->results->nodeKeyVal->shortUrl;
		}
	}
	return $result;
}

function getCurrentBlogVersion() {
	$currentVersion = file_get_contents(ROOT . '/cache/CHECKUP');
	$currentVersion = substr($currentVersion, 0, 3);
	return $currentVersion;
}

function getJQueryCheckLoad($target) {
	global $pluginURL;
	if (getCurrentBlogVersion() < 1.8) {
		$target .= '<script type="text/javascript" src="' . $pluginURL . '/script/jquery-1.3.2.min.js"></script>'.CRLF;
		$target .= '<script type="text/javascript">jQuery.noConflict();</script>'.CRLF;
	}
	return $target;
}

function getTwitterInitConfigVal(&$data) {
	$data['username'] = isset($data['username']) && !empty($data['username']) ? $data['username'] : "";
	$data['password'] = isset($data['password']) && !empty($data['password']) ? $data['password'] : "";
	$data['hashTag'] = isset($data['hashTag']) && !empty($data['hashTag']) ? $data['hashTag'] : "";
	$data['hashTags'] = isset($data['hashTags']) && !empty($data['hashTags']) ? $data['hashTags'] : "";
	$data['twitterUpdate'] = isset($data['twitterUpdate']) && !empty($data['twitterUpdate']) ? $data['twitterUpdate'] : "none";
	$data['updateText'] = isset($data['updateText']) && !empty($data['updateText']) ? $data['updateText'] : "title";
	$data['listLength'] = isset($data['listLength']) && !empty($data['listLength']) ? $data['listLength'] : "10";
	$data['synchronizeLine'] = isset($data['synchronizeLine']) && !empty($data['synchronizeLine']) ? $data['synchronizeLine'] : false;
}

function getTwitterDataSet($DATA) {
	$cfg = Setting::fetchConfigVal($DATA);
	$tw = new Twitter($cfg['username'], $cfg['password']);
	$res = $tw->getUserTimeline();
	if (!empty($res->error)) {
		return "::Authentication error::\n\n  Username && Password requires authentication.  \n  (" . $res->error . ")";
	}

	return true;
}

/************************/
// Synchronizing twitter
function synchronizeTwitterWithLine() {
	global $configVal;
	$data = Setting::fetchConfigVal($configVal);
	getTwitterInitConfigVal($data);
	if(empty($data['synchronizeLine'])) return false;

	$page = 1;
	$tw = new Twitter($data['username'], $data['password']);
	$limit = 0;
	
	$option = array('page'=>$page, 'count'=>40);

	$timeline = $tw->getUserTimeline($option);
	$timelineResults = $timeline;

	$line = Model_Line::getInstance();
	
	foreach ($timelineResults as $status) {
		$status_id = $status->id;
		$user_id = $status->user->id;
		$user_name = $status->user->name;
		$user_screen_name = $status->user->screen_name;
		$user_link = "https://twitter.com/{$user_screen_name}";
		$text = $status->text;
		$text_convert = getTextBodyConvert($text);
		$source = getTextLinkConvert($status->source);
		$published = getTwitterDateConvert($status->created_at);
		$published_link = "https://twitter.com/{$user_screen_name}/status/{$status_id}";
		
		$line->reset();
		$line->setFilter(array('root','equals','Twitter',true));
		$line->setFilter(array('content','equals',$text_convert,true));
		$line->setFilter(array('created','>',Timestamp::getUNIXtime() - 3600));
		$line->setLimit(1);
		if(!$line->get('id')) {
			$line->reset();
			$line->created = strtotime($status->created_at);
			$line->content = $text_convert;
			$line->author = $user_name;
			$line->permalink = $published_link;
			$line->root = 'Twitter';
			$line->category = 'public';
			$line->add();
		}
	}
	flush();
}
?>