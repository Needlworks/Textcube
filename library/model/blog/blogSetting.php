<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function setBlogTitle($blogid, $title) {
    $context = Model_Context::getInstance();
    if ($title == $context->getProperty('blog.title')) {
        return true;
    }
    if (Setting::setBlogSetting('title', Utils_Unicode::lessenAsEncoding($title, 255), true) === false) {
        return false;
    }
    $context->setProperty('blog.title', $title);
    importlib('model.blog.feed');
    importlib('blogskin');
    clearFeed();
    CacheControl::flushSkin();
    return true;
}

function setBlogDescription($blogid, $description) {
    $context = Model_Context::getInstance();
    if ($description == $context->getProperty('blog.description')) {
        return true;
    }
    if (Setting::setBlogSettingGlobal('description', Utils_Unicode::lessenAsEncoding($description, 255)) === false) {
        return false;
    }
    $context->setProperty('blog.description', $description);
    importlib('model.blog.feed');
    importlib('blogskin');
    clearFeed();
    CacheControl::flushSkin();
    return true;
}

function setBlogTags($blogid, $tags) {
    if (isset($tags)) {
        Setting::setBlogSettingGlobal('blogTags', $tags);
        return true;
    }
    return false;
}

function getBlogTags($blogid) {
    if ($tags = Setting::getBlogSettingGlobal('blogTags')) {
        return $tags;
    }
    return null;
}

function removeBlogLogo($blogid) {
    $context = Model_Context::getInstance();
    $skin = new Skin($context->getProperty('skin.skin'));
    importlib('model.blog.attachment');

    if (Setting::setBlogSettingGlobal('logo', '') === false) {
        return false;
    } else {
        deleteAttachment($blogid, -1, $context->getProperty('blog.logo'));
        $context->setProperty('blog.logo', '');
        $skin->purgeCache();
        return true;
    }
}

function changeBlogLogo($blogid, $file) {
    $context = Model_Context::getInstance();
    $skin = new Skin($context->getProperty('skin.skin'));
    importlib('model.blog.attachment');
    if (($attachment = addAttachment($blogid, -1, $file)) === false) {
        return false;
    }
    if (strncmp($attachment['mime'], 'image/', 6) != 0) {
        deleteAttachment($blogid, -1, $attachment['name']);
        return false;
    }
    if (Setting::setBlogSettingGlobal('logo', $attachment['name'])) {
        deleteAttachment($blogid, -1, $context->getProperty('blog.logo'));
        $skin->purgeCache();
        return true;
    }
    return false;
}

function checkBlogName($name) {
    return preg_match('/^[-a-zA-Z0-9]+$/', $name);
}

function setPrimaryDomain($blogid, $name) {
    importlib('model.blog.feed');
    $name = Utils_Unicode::lessenAsEncoding(strtolower(trim($name)), 32);
    if ($name == $blog['name']) {
        return 0;
    }
    if (!checkBlogName($name)) {
        return 1;
    }
    $pool = DBModel::getInstance();
    $pool->reset('ReservedWords');
    $pool->setQualifier('word', 'like', $name, true);
    if ($pool->getCell('count(*)') > 0) {
        return 2;
    }
    $pool->reset('BlogSettings');
    $pool->setQualifier('name', 'equals', 'name', true);
    $pool->setQualifier('value', '=', $name, true);
    if ($pool->getCount('*') > 0) {
        return 3;
    }
    if (Setting::setBlogSettingGlobal('name', $name)) {
        $blog['name'] = $name;
        clearFeed();
    } else {
        return 0;
    }
}

function setSecondaryDomain($blogid, $domain) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();

    importlib('model.blog.feed');
    $domain = Utils_Unicode::lessenAsEncoding(strtolower(trim($domain)), 64);
    if ($domain == $context->getProperty('blog.secondaryDomain')) {
        return 0;
    }
    if (empty($domain)) {
        Setting::setBlogSettingGlobal('secondaryDomain', '');
    } else {
        if (Validator::domain($domain)) {
            $pool->reset("BlogSettings");
            $pool->setQualifier("blogid", "neq", $blogid);
            $pool->setQualifier("name", "eq", 'secondaryDomain', true);
            $pool->setQualifierSet(array("value", "eq", $domain, true), 'OR', array("value", "eq", (substr($domain, 0, 4) == 'www.' ? substr($domain, 4) : 'www.' . $domain), true));
            if ($pool->doesExist()) {
                return 1;
            }
            Setting::setBlogSettingGlobal('secondaryDomain', $domain);
        } else {
            return 2;
        }
    }
    $context->setProperty('blog.secondaryDomain', $domain);
    clearFeed();
    return 0;
}

function setDefaultDomain($blogid, $default) {
    $context = Model_Context::getInstance();
    $default = $default == 1 ? 1 : 0;
    if ($context->getProperty('blog.secondaryDomain') && $default == 1) {
        return false;
    }
    if ($default == $context->getProperty('blog.defaultDomain')) {
        return true;
    }
    if (Setting::setBlogSettingGlobal('defaultDomain', $default) === false) {
        return false;
    }
    $context->setProperty('blog.defaultDomain', $default);
    importlib('model.blog.feed');
    clearFeed();
    return true;
}

function useBlogSlogan($blogid, $useSloganOnPost, $useSloganOnCategory, $useSloganOnTag) {
    $context = Model_Context::getInstance();
    $useSloganOnPost = $useSloganOnPost ? 1 : 0;
    $useSloganOnCategory = $useSloganOnCategory ? 1 : 0;
    $useSloganOnTag = $useSloganOnTag ? 1 : 0;
    if ($useSloganOnPost == $context->getProperty('blog.useSloganOnPost')
        && $useSloganOnCategory == $context->getProperty('blog.useSloganOnCategory')
        && $useSloganOnTag == $context->getProperty('blog.useSloganOnTag')
    ) {
        return true;
    }
    /*	if(Setting::setBlogSettingGlobal('useSloganOnPost',$useSlogan) === false
        || Setting::setBlogSettingGlobal('useSloganOnCategory',$useSlogan) === false
        || Setting::setBlogSettingGlobal('useSloganOnTag',$useSlogan) === false
            ) {
            return false;
        }*/
    Setting::setBlogSettingGlobal('useSloganOnPost', $useSloganOnPost);
    Setting::setBlogSettingGlobal('useSloganOnCategory', $useSloganOnCategory);
    Setting::setBlogSettingGlobal('useSloganOnTag', $useSloganOnTag);

    $context->setProperty('blog.useSloganOnPost', $useSloganOnPost);
    $context->setProperty('blog.useSloganOnCategory', $useSloganOnCategory);
    $context->setProperty('blog.useSloganOnTag', $useSloganOnTag);

    importlib('model.blog.feed');
    CacheControl::flushCategory();
    CacheControl::flushEntry();
    CacheControl::flushTag();
    fireEvent('ToggleBlogSlogan', null, $useSloganOnPost);
    clearFeed();
    return true;
}

function setEntriesOnRSS($blogid, $entriesOnRSS) {
    $context = Model_Context::getInstance();
    importlib('model.blog.feed');
    if ($entriesOnRSS == $context->getProperty('blog.entriesOnRSS')) {
        return true;
    }
    if (Setting::setBlogSettingGlobal('entriesOnRSS', $entriesOnRSS) === false) {
        return false;
    }
    $context->setProperty('blog.entriesOnRSS', $entriesOnRSS);
    clearFeed();
    return true;
}

function setCommentsOnRSS($blogid, $commentsOnRSS) {
    $context = Model_Context::getInstance();
    if ($commentsOnRSS == $context->getProperty('blog.commentsOnRSS')) {
        return true;
    }
    if (Setting::setBlogSettingGlobal('commentsOnRSS', $commentsOnRSS) === false) {
        return false;
    }
    $context->setProperty('blog.commentsOnRSS', $commentsOnRSS);
    $cache = pageCache::getInstance();
    $cache->name = 'commentRSS';
    $cache->purge();
    return true;
}

function setBlogLanguage($blogid, $language, $blogLanguage) {
    $context = Model_Context::getInstance();
    if (($language == $context->getProperty('blog.language')) && ($blogLanguage == $context->getProperty('blog.blogLanguage'))) {
        return true;
    }
    $language = Utils_Unicode::lessenAsEncoding($language, 5);
    $blogLanguage = Utils_Unicode::lessenAsEncoding($blogLanguage, 5);
    if (Setting::setBlogSettingGlobal('language', $language) && Setting::setBlogSettingGlobal('blogLanguage', $blogLanguage)) {
        $context->setProperty('blog.language', $language);
        $context->setProperty('blog.blogLanguage', $blogLanguage);
        importlib('model.blog.feed');
        clearFeed();
        return true;
    } else {
        return false;
    }
}

function setGuestbook($blogid, $write, $comment) {
    if (!is_numeric($write) || !is_numeric($comment)) {
        return false;
    }
    if (Setting::setBlogSettingGlobal('allowWriteOnGuestbook', $write) && Setting::setBlogSettingGlobal('allowWriteDblCommentOnGuestbook', $comment)) {
        return true;
    } else {
        return false;
    }
}

function addBlog($blogid, $userid, $identify) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();

    if (empty($userid)) {
        $userid = 1; // If no userid, choose the service administrator.
    } else {
        $pool->reset('Users');
        $pool->setQualirifer('userid', 'eq', $userid);
        if (!$pool->doesExist('userid')) {
            return 3;
        }  // 3: No user exists with specific userid
    }

    if (!empty($blogid)) { // If blogid,
        $pool->reset('BlogSettings');
        $pool->setQualirifer('blogid', 'eq', $blogid);
        if (!$pool->doesExist('blogid')) {
            return 2;
        } // 2: No blog exists with specific blogid

        // Thus, blog and user exists. Now combine both.
        $pool->reset('Privileges');
        $pool->setAttribute('blogid', $blogid);
        $pool->setAttribute('userid', $userid);
        $pool->setAttribute('acl', 0);
        $pool->setAttribute('created', Timestamp::getUNIXtime());
        $pool->setAttribute('lastlogin', 0);
        $result = $pool->insert();
        return $result;
    } else { // If no blogid, create a new blog.
        if (!preg_match('/^[a-zA-Z0-9]+$/', $identify)) {
            return 4;
        } // Wrong Blog name
        $identify = POD::escapeString(Utils_Unicode::lessenAsEncoding($identify, 32));

        $blogName = $identify;

        $pool->reset('ReservedWords');
        $pool->setQualifier('word', 'eq', $blogName, true);
        $result = $pool->getCount();

        if ($result && $result > 0) {
            return 60;    // Reserved blog name.
        }

        $pool->reset('BlogSettings');
        $pool->setQualifier('name', 'eq', 'name', true);
        $pool->setQualifier('value', 'eq', $blogName, true);

        $result = $pool->getCount('value');

        if ($result && $result > 0) {
            return 61; // Same blogname is already exists.
        }
        $pool->reset('BlogSettings');
        $blogid = $pool->getCell('max(blogid)') + 1;

        $basicInformation = array(
            'name' => $identify,
            'defaultDomain' => 0,
            'title' => '',
            'description' => '',
            'logo' => '',
            'logoLabel' => '',
            'logoWidth' => 0,
            'logoHeight' => 0,
            'useFeedViewOnCategory' => 1,
            'useSloganOnPost' => 1,
            'useSloganOnCategory' => 1,
            'useSloganOnTag' => 1,
            'entriesOnPage' => 10,
            'entriesOnList' => 10,
            'entriesOnRSS' => 10,
            'commentsOnRSS' => 10,
            'publishWholeOnRSS' => 1,
            'publishEolinSyncOnRSS' => 1,
            'allowWriteOnGuestbook' => 1,
            'allowWriteDblCommentOnGuestbook' => 1,
            'acceptComments' => 1,
            'acceptTrackbacks' => 1,
            'visibility' => 2,
            'created' => Timestamp::getUNIXtime(),
            'language' => $context->getProperty('service.language'),
            'blogLanguage' => $context->getProperty('service.language'),
            'timezone' => $context->getProperty('service.timezone'));
        $isFalse = false;
        foreach ($basicInformation as $fieldname => $fieldvalue) {
            if (Setting::setBlogSettingDefault($fieldname, $fieldvalue, $blogid) === false) {
                $isFalse = true;
            }
        }
        if ($isFalse == true) {
            $pool->reset('BlogSettings');
            $pool->setQualifier('blogid', 'eq', $blogid);
            $pool->delete();
            return 12;
        }
        $pool->reset('SkinSettings');
        $pool->setAttribute('blogid', $blogid);
        $pool->setAttribute('name', 'skin', true);
        $pool->setAttribute('value', $context->getProperty('service.skin'), true);
        if (!$pool->insert()) {
            deleteBlog($blogid);
            return 13;
        }
        $pool->reset('FeedSettings');
        $pool->setAttribute('blogid', $blogid);

        if (!$pool->insert()) {
            deleteBlog($blogid);
            return 62;
        }

        $pool->reset('FeedGroups');
        $pool->setAttribute('blogid', $blogid);
        $pool->setAttribute('id', 0);

        if (!$pool->insert()) {
            deleteBlog($blogid);
            return 62;
        }

        Setting::setBlogSettingGlobal('defaultEditor', 'modern', $blogid);
        Setting::setBlogSettingGlobal('defaultFormatter', 'ttml', $blogid);

        //Combine user and blog.
        $pool->reset('Privileges');
        $pool->setAttribute('blogid', $blogid);
        $pool->setAttribute('userid', $userid);
        $pool->setAttribute('acl', 16);
        $pool->setAttribute('created', Timestamp::getUNIXtime());
        $pool->setAttribute('lastlogin', 0);
        if ($pool->insert()) {
            setDefaultPost($blogid, $userid);
            return true;
        } else {
            return 65;
        }
    }
    //return true; // unreachable code
}

function getDefaultPostContent() {
    return _t('<p>텍스트큐브 사용을 환영합니다. 텍스트큐브(Textcube) 는 웹에서 자신의 생각이나 일상을 기록하고 표현하기 위한 도구입니다. 강력한 글 관리와 편집 기능을 통하여 쉽고 빠르게 글을 작성하고 알릴 수 있습니다. 또한 통합된 소통 기능및 RSS 바깥글 읽기 기능을 통하여 다양한 사람들과 간단하게 의견을 주고 받을 수 있습니다.</p><p>또한 텍스트큐브는 플러그인과 테마 시스템을 통하여 다양한 기능을 추가하거나 스킨을 바꾸고 편집할 수 있습니다. 뿐만 아니라 OpenID, microformat 지원 등의 기술적인 부분 및 다국어 지원을 포함한 강력한 저작 도구입니다.</p><p>사용하며 도움말이 필요할 때는 관리자 메뉴의 우측 상단의 도우미 링크를 누르시면 도움말을 보실 수 있습니다. 기타 자세한 정보는 http://www.textcube.org 를 방문해서 확인하실 수 있습니다.</p><p>이 글은 새 블로그에 자동으로 적힌 글입니다. 관리자 화면에서 언제든지 지우셔도 됩니다.</p>'); // 언어팩에서 자유롭게 메세지를 변경할 수 있도록 함.
}

function setDefaultPost($blogid, $userid) {
    importlib('model.blog.entry');
    $entry = array();
    $entry['category'] = 0;
    $entry['visibility'] = 2;
    $entry['location'] = '/';
    $entry['tag'] = '';
    $entry['title'] = _t('환영합니다!');
    $entry['slogan'] = 'welcome';
    $entry['contentformatter'] = 'ttml';
    $entry['contenteditor'] = 'tinyMCE';
    $entry['starred'] = 0;
    $entry['acceptcomment'] = 1;
    $entry['accepttrackback'] = 1;
    $entry['published'] = null;
    $entry['firstEntry'] = true;
    $entry['content'] = getDefaultPostContent();
    return addEntry($blogid, $entry, $userid);
}

function getInvited($userid) {
    $pool = DBModel::getInstance();
    $pool->reset('Users');
    $pool->setQualifier('host', 'eq', $userid);
    $pool->setOrder('created', 'ASC');
    return $pool->getAll();
}

function getBlogName($blogid) {
    $pool = DBModel::getInstance();
    $pool->reset('BlogSettings');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('name', 'eq', 'name', true);
    return $pool->getCell('value');
}

function getAuthToken($userid) {
    $query = DBModel::getInstance();
    $query->reset('UserSettings');
    $query->setQualifier('userid', 'equals', $userid);
    $query->setQualifier('name', 'equals', 'AuthToken', true);
    return $query->getCell('value');
}

function sendInvitationMail($blogid, $userid, $name, $comment, $senderName, $senderEmail) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();

    if (empty($blogid)) {
        $pool->reset('BlogSettings');
        $blogid = $pool->getCell('max(blogid)'); // If no blogid, get the latest created blogid.
    }
    $email = User::getEmail($userid);
    $pool->reset('Users');
    $pool->setQualifier('userid', 'eq', $userid);
    $password = getCell('password');

    $authtoken = getAuthToken($userid);
    $blogName = getBlogName($blogid);

    if (empty($email)) {
        return 1;
    }
    if (!preg_match('/^[^@]+@([-a-zA-Z0-9]+\.)+[-a-zA-Z0-9]+$/', $email)) {
        return 2;
    }
    if (empty($name)) {
        $name = User::getName($userid);
    }

    if (strcmp($email, Utils_Unicode::lessenAsEncoding($email, 64)) != 0) {
        return 11;
    }

    //$loginid = POD::escapeString(Utils_Unicode::lessenAsEncoding($email, 64));
    $name = addslashes(Utils_Unicode::lessenAsEncoding($name, 32));

    //$headers = 'From: ' . encodeMail($senderName) . '<' . $senderEmail . ">\n" . 'X-Mailer: ' . TEXTCUBE_NAME . "\n" . "MIME-Version: 1.0\nContent-Type: text/html; charset=utf-8\n";
    if (empty($name)) {
        $subject = _textf('귀하를 %1님이 초대합니다', $senderName);
    } else {
        $subject = _textf('%1님을 %2님이 초대합니다', $name, $senderName);
    }
    $message = file_get_contents(ROOT . "/resources/style/letter/letter.html");
    $message = str_replace('[##_title_##]', _text('초대장'), $message);
    $message = str_replace('[##_content_##]', $comment, $message);
    $message = str_replace('[##_images_##]', $context->getProperty('uri.service') . "/resources/style/letter", $message);
    $message = str_replace('[##_link_##]', getInvitationLink(getBlogURL($blogName), $email, $password, $authtoken), $message);
    $message = str_replace('[##_go_blog_##]', getBlogURL($blogName), $message);
    $message = str_replace('[##_link_title_##]', _text('블로그 바로가기'), $message);
    if (empty($name)) {
        $message = str_replace('[##_to_##]', '', $message);
    } else {
        $message = str_replace('[##_to_##]', _text('받는 사람') . ': ' . $name, $message);
    }
    $message = str_replace('[##_sender_##]', _text('보내는 사람') . ': ' . $senderName, $message);
    $ret = sendEmail($senderName, $senderEmail, $name, $email, $subject, $message);
    if ($ret !== true) {
        return array(14, $ret[1]);
    }
    return true;
}

function getInvitationLink($url, $email, $password, $authtoken) {
    return $url . '/login?loginid=' . rawurlencode($email) . '&password=' . rawurlencode($authtoken) . '&requestURI=' . rawurlencode($url . "/owner/setting/account?password=" . rawurlencode($password));
}

function cancelInvite($userid, $clean = true) {
    $pool = DBModel::getInstance();
    $pool->reset('Users');
    $pool->setQualifier('userid', 'eq', $userid);
    $pool->setQualifier('lastlogin', 'eq', 0);
    if ($pool->getCount() === 0) {
        return false;
    }
    $pool->unsetQualifier('lastlogin');
    $pool->setQualifier('host', 'eq', getUserId());
    if ($pool->getCount() === 0) {
        return false;
    }

    $blogidWithOwner = User::getOwnedBlogs($userid);
    foreach ($blogidWithOwner as $blogids) {
        if (deleteBlog($blogids) === false) {
            return false;
        }
    }
    $pool->reset('Privileges');
    $pool->setQualifier('userid', 'eq', $userid);
    if ($clean && !$pool->getAll()) {
        User::removePermanent($userid);
    }
    return true;
}

function changePassword($userid, $pwd, $prevPwd, $forceChange = false) {
    $pool = DBModel::getInstance();
    $pool->reset('UserSettings');
    $context = Model_Context::getInstance();

    if (!strlen($pwd) || (!strlen($prevPwd) && !$forceChange)) {
        return false;
    }
    if ($forceChange === true) {
        $pwd = md5($pwd);
        $pool->reset('UserSettings');
        $pool->setQualifier('userid', 'eq', $userid);
        $pool->setQualifier('name', 'eq', 'AuthToken', true);
        @$pool->delete(1);
        $pool->reset('Users');
        $pool->setAttribute('password', $pwd, true);
        $pool->setQualifier('userid', 'eq', $userid);
        return $pool->update();
    }
    $pool->reset("Users");
    $pool->setQualifier("userid", "eq", $userid);
    if ((strlen($prevPwd) == 32) && preg_match('/[0-9a-f]/i', $prevPwd)) {
        $pool->setQualifierSet(array(
            array("password", "eq", md5($prevPwd)),
            "OR",
            array("password", "eq", $prevPwd)
        ));
    } else {
        $pool->setQualifier("password", "eq", md5($prevPwd));
    }
    $count = $pool->getCount();
    if ($count == 0) {
        return false;
    }
    $pwd = md5($pwd);
    $pool->reset('UserSettings');
    $pool->setQualifier('userid', 'eq', $userid);
    $pool->setQualifier('name', 'eq', 'AuthToken', true);
    @$pool->delete(1);
    $pool->reset('Users');
    $pool->setQualifier('userid', 'eq', $userid);
    $pool->setAttribute('password', $pwd, true);
    return $pool->update();
}

function changeAPIKey($userid, $key) {
    if ($key) {
        return Setting::setUserSettingGlobal('APIKey', $key, $userid);
    } else {
        return Setting::removeUserSettingGlobal('APIKey', $userid);
    }
}

function deleteBlog($blogid) {
    if ($blogid == 1) {
        return false;
    }
    $pool = DBModel::getInstance();
    $targets = array('BlogSettings', 'SkinSettings', 'FeedSettings', 'FeedGroups', 'Privileges');
    $result = true;
    foreach ($targets as $t) {
        $pool->reset($t);
        $pool->setQualifier('blogid', 'eq', $blogid);
        $result = $pool->delete() && $result;
    }
    return $result;
}

function removeBlog($blogid) {
    $pool = DBModel::getInstance();
    $context = Model_Context::getInstance();
    if (Setting::getServiceSetting("defaultBlogId", 1, true) == $blogid) {
        return false;
    }

    $targets = array('Attachments', 'BlogSettings', 'BlogStatistics', 'Categories', 'Comments', 'CommentsNotified',
        'CommentsNotifiedQueue', 'DailyStatistics', 'Entries', 'EntriesArchive', 'FeedGroups', 'FeedReads', 'FeedStarred',
        'FeedSettings', 'Filters', 'Links', 'LinkCategories', 'PageCacheLog', 'Plugins', 'RefererLogs', 'RefererStatistics',
        'RemoteResponses', 'RemoteResponseLogs', 'SkinSettings', 'TagRelations', 'Privileges', 'XMLRPCPingSettings');
    //Clear Tables
    foreach ($targets as $t) {
        $pool->reset($t);
        $pool->setQualifier('blogid', 'eq', $blogid);
        $pool->delete();
    }
    //Delete Tags
    $pool->reset("TagRelations");
    $pool->setQualifier("blogid", "eq", $blogid);
    $tags = $pool->getColumn("tag", "DISTINCT");
    if (count($tags) > 0) {
        $pool->reset("TagRelations");    // Tag id used at deleted blog.
        $pool->setQualifier("tag", "hasoneof", $tags);
        $nottargets = $pool->getColumn("tag", "DISTINCT");    // Tag id used at other blogs.
        if (count($nottargets) > 0) {
            $pool->reset("Tags");
            $pool->setQualifier("id", "hasoneof", $tags);
            $pool->setQualifier("id", "hasnoneof", $nottargets);
            $pool->delete();
        } else {
            $pool->reset("Tags");
            $pool->setQualifier("id", "hasoneof", $tags);
            $pool->delete();
        }
    }

    //Delete Feeds
    $pool->reset("FeedGroupRelations");
    $pool->setQualifier("blogid", "eq", $blogid);
    $feeds = $pool->getColumn("feeds", "DISTINCT");

    if (count($feeds) > 0) {
        foreach ($feeds as $feedId) {
            deleteFeed($blogid, $feedId);
        }
    }

    //Clear Plugin Database
    // TODO : encapsulate with 'value'
    $pool->reset("ServiceSettings");
    $pool->setQualifier("name", "like", "Database_");
    $plugintablesraw = $pool->getAll();
    foreach ($plugintablesraw as $table) {
        $pool->reset(substr($table['name'], 9));
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->delete();
    }

    //Clear RSS Cache
    if (file_exists(__TEXTCUBE_CACHE_DIR__ . "/rss/$blogid.xml")) {
        unlink(__TEXTCUBE_CACHE_DIR__ . "/rss/$blogid.xml");
    }

    //Delete Attachments
    Path::removeFiles(Path::combine(ROOT, 'attach', $blogid));

    return true;
}

function setSmtpServer($useCustomSMTP, $smtpHost, $smtpPort) {
    if (empty($useCustomSMTP)) {
        Setting::setServiceSettingGlobal('useCustomSMTP', 0);
        return true;
    }
    if (!Setting::setServiceSettingGlobal('useCustomSMTP', 1)) {
        return false;
    }
    if (!Setting::setServiceSettingGlobal('smtpHost', $smtpHost)) {
        return false;
    }
    if (!Setting::setServiceSettingGlobal('smtpPort', $smtpPort)) {
        return false;
    }
    return true;
}

function setDefaultBlog($blogid) {
    if (!Acl::check("group.creators")) {
        return false;
    }
    $result = Setting::setServiceSettingGlobal("defaultBlogId", $_GET['blogid']);
    return $result;
}

?>
