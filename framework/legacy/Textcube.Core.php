<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/* This component contains 'User', 'Blog' and 'Transaction' class.
   NOTE : Classes described below are actually not objects. Usually they are static.*/

class User {

    static function getName($userid = null) {
        $context = Model_Context::getInstance();

        if (empty($userid)) {
            $userid = getUserId();
        }
        $userNames = $context->getProperty('cache.UserNames',array());
        if (array_key_exists($userid, $userNames)) {
            return $userNames[$userid];
        }
        $pool = DBModel::getInstance();
        $pool->reset("Users");
        $pool->setQualifier("userid", "eq", $userid);
        $userNames[$userid] = $pool->getCell("name");
        $context->setProperty('cache.UserNames',$userNames);
        return $userNames[$userid];
    }

    static function getInfo($userid = null) {
        $pool = DBModel::getInstance();
        $pool->init("Users");
        $pool->setQualifier("userid", "eq", $userid);
        return $pool->getRow();
    }

    static function getUserIdByName($name) {
        $context = Model_Context::getInstance();
        if (!isset($name)) {
            return getUserId();
        }
        $userNames = $context->getProperty('cache.UserNames',array());

        $userid = array_search($name, $userNames);
        if (!empty($userid)) {
            return $userid;
        }
        $pool = DBModel::getInstance();
        $pool->reset("Users");
        $pool->setQualifier("name", "eq", $name, true);
        $userid = $pool->getCell("userid");
        $userNames[$userid] = $name;
        $context->setProperty('cache.UserNames',$userNames);

        return $userid;
    }

    static function getUserNamesOfBlog($blogid) {
        // TODO : Caching with global cache component. (Usually it is not changing easily.)
        $pool = DBModel::getInstance();
        $pool->reset('Privileges');
        $pool->setQualifier('blogid', 'eq', $blogid);
        $authorIds = $pool->getColumn('userid');
        $pool->reset('Users');
        $pool->setQualifier('userid', 'hasOneOf', $authorIds);
        return $pool->getAll('userid,name');
    }

    static function getBlogOwnerName($blogid) {
        return User::getName(User::getBlogOwner($blogid));
    }

    static function getBlogOwner($blogid) {
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();
        $pool->reset("Privileges");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("acl", ">", 15);
        $ownerUserId = $pool->getCell("userid");
        return $ownerUserId;
    }

    static function getEmail($userid = null) {
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();
        if (!isset($userid)) {
            $userid = getUserId();
        }
        $pool->reset("Users");
        $pool->setQualifier("userid", "eq", $userid);
        return $pool->getCell("loginid");
    }

    static function getUserIdByEmail($loginid = null) {
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();
        $loginid = trim($loginid);
        if (!isset($loginid)) {
            return null;
        }

        $pool->reset("Users");
        $pool->setQualifier("loginid", "eq", $loginid, true);
        return $pool->getCell("userid");
    }

    static function getBlogs($userid = null) {
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();
        if (!isset($userid)) {
            $userid = getUserId();
        }
        $pool->reset("Privileges");
        $pool->setQualifier("userid", "eq", $userid);
        return $pool->getColumn("blogid");
    }

    static function getOwnedBlogs($userid = null) {
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();
        if (!isset($userid)) {
            $userid = getUserId();
        }
        $pool->reset("Privileges");
        $pool->setQualifier("userid", "eq", $userid);
        $pool->setQualifier("acl", ">", 15);
        return $pool->getColumn("blogid");
    }

    static function getHomepageType($userid = null) {
        if (!isset($userid)) {
            $userid = getUserId();
        }
        $info = unserialize(Setting::getUserSettingGlobal('userLinkInfo', '', $userid));
        if (!empty($info)) {
            $type = $info['type'];
        }
        if (empty($type)) {
            $type = "default";
        }
        return $type;
    }

    static function getHomepage($userid = null) {
        if (!isset($userid) || empty($userid)) {
            $userid = getUserId();
        }
        $info = unserialize(Setting::getUserSettingGlobal('userLinkInfo', '', $userid));
        if (is_null($info)) {
            $info = array('type' => 'default');
        }
        switch ($info['type']) {
            case "external" :
                $homepage = $info['url'];
                break;
            case "internal" :
                $homepage = getDefaultURL($info['blogid']);
                break;
            case "author" :
                $homepage = getDefaultURL($info['blogid']) . "/author/" . URL::encode(User::getName($userid));
                break;
            case "default" :
            default :
                $homepage = null;
        }
        return $homepage;
    }

    static function setHomepage($type, $homepage, $blogid = null, $userid = null) {
        $types = array("internal", "author", "external", "default");
        if (!isset($userid)) //TODO : 현재 로그인 사용자의 homepage만 변경가능.setUserSetting함수 특성.
        {
            $userid = getUserId();
        }
        $info['blogid'] = is_null($blogid) ? getBlogId() : $blogid;
        $info['url'] = is_null($homepage) ? null : $homepage;
        if (in_array($type, $types)) {
            $info['type'] = $type;
            switch ($type) {
                case "internal" :
                case "author" :
                    $info['url'] = null;
                    break;
                case "external" :
                    $info['blogid'] = null;
                    break;
                case "default" :
                default :
                    $info['url'] = null;
                    $info['blogid'] = null;
            }
        } else {
            return false;
        }
        $homepage = serialize($info);
        if (Setting::setUserSettingGlobal("userLinkInfo", $homepage, $userid)) {
            return true;
        }
        return false;
    }

    static function confirmPassword($userid = null, $password) {
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();

        if (empty($userid)) {
            $userid = getUserId();
        }
        $password = md5($password);
        $pool->reset("Users");
        $pool->setQualifier("userid", "eq", $userid);
        $pool->setQualifier("password", "eq", $password, true);
        return $pool->doesExist("userid");
    }

    static function authorName($blogid = null, $entryId) {
        if (is_null($blogid)) {
            $blogid = getBlogId();
        }

        // Read userId of entry from relation table.
        $userid = getUserIdOfEntry($blogid, $entryId);
        if (isset($userid)) {
            return User::getName($userid);
        } else {
            return false;
        }
    }

    static function changeBlog() {
        $context = Model_Context::getInstance();

        $blogList = User::getBlogs();
        if (count($blogList) == 0) {
            return;
        }

        $changeBlogView = str_repeat(TAB, 6) . "<select id=\"blog-list\" onchange=\"location.href='" . $context->getProperty("uri.blog") . "/owner/network/teamblog/changeBlog/?blogid='+this.value\">" . CRLF;
        foreach ($blogList as $info) {
            $title = Utils_Unicode::lessen(Setting::getBlogSettingGlobal("title", null, $info, true), 30);
            $title = ($title ? $title : _f('%1 님의 블로그', User::getBlogOwnerName($info)));
            $changeBlogView .= str_repeat(TAB, 7) . '<option value="' . $info . '"';
            if ($info == $context->getProperty('blog.id')) {
                $changeBlogView .= ' selected="selected"';
            }
            $changeBlogView .= '>' . $title . '</option>' . CRLF;
        }
        $changeBlogView .= str_repeat(TAB, 6) . '</select>' . CRLF;
        return $changeBlogView;
    }

    static function changeSetting($userid, $email, $nickname) {
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();

        if (strcmp($email, Utils_Unicode::lessenAsEncoding($email, 64)) != 0) {
            return false;
        }
        $email = POD::escapeString(Utils_Unicode::lessenAsEncoding($email, 64));
        $nickname = POD::escapeString(Utils_Unicode::lessenAsEncoding($nickname, 32));
        if ($email == '' || $nickname == '') {
            return false;
        }
        $pool->reset("Users");
        $pool->setQualifier("name", "eq", $nickname, true);
        $pool->setQualifier("userid", "neq", $userid);
        if ($pool->doesExist()) {
            return false;
        } else {
            $pool->reset("Users");
            $pool->setQualifier("userid", "eq", $userid);
            $pool->setAttribute("loginid", $email, true);
            $pool->setAttribute("name", $nickname, true);
            $result = $pool->update();
            if (!$result) {
                return false;
            } else {
                return true;
            }
        }
    }

    static function add($email, $name) {
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();
        if (empty($email)) {
            return 1;
        }
        if (!preg_match('/^[^@]+@([-a-zA-Z0-9]+\.)+[-a-zA-Z0-9]+$/', $email)) {
            return 2;
        }

        if (strcmp($email, Utils_Unicode::lessenAsEncoding($email, 64)) != 0) {
            return 11;
        }

        $loginid = Utils_Unicode::lessenAsEncoding($email, 64);
        $name = Utils_Unicode::lessenAsEncoding($name, 32);
        $password = User::__generatePassword();
        $authtoken = md5(User::__generatePassword());
        $pool->reset("Users");
        $pool->setQualifier("loginid", "eq", $loginid, true);
        if ($pool->doesExist()) {
            return 9;    // User already exists.
        }
        $pool->reset("Users");
        $pool->setQualifier("name", "eq", $name, true);
        if ($pool->getCount()) {
            $name = $name . '.' . time();
        }

        $pool->reset("Users");
        $pool->setAttribute("userid", User::__getMaxUserId() + 1);
        $pool->setAttribute("loginid", $loginid, true);
        $pool->setAttribute("password", md5($password), true);
        $pool->setAttribute("name", $name, true);
        $pool->setAttribute("created", Timestamp::getUNIXtime());
        $pool->setAttribute("lastlogin", 0);
        $pool->setAttribute("host", getUserId());
        $result = $pool->insert();
        if (empty($result)) {
            return 11;
        }
        $pool->reset("UserSettings");
        $pool->setAttribute("userid", User::getUserIdByEmail($loginid));
        $pool->setAttribute("name", 'AuthToken', true);
        $pool->setAttribute("value", $authtoken, true);
        $result = $pool->insert();
        if (empty($result)) {
            return 11;
        }
        return true;
    }

    /*@static@*/
    function remove($userid) {
        $pool = DBModel::getInstance();

        if ($userid == 1) {
            return false;
        }
        if (!isset($userid)) {
            return false;
        }
        $blogs = User::getOwnedBlogs($userid);
        $pool->reset("Comments");
        $pool->setAttribute("replier", NULL);
        $pool->setQualifier("replier", "eq", $userid);
        $pool->update();

        foreach ($blogs as $ownedBlog) {
            Blog::changeOwner($ownedBlog, 1); // 관리자 uid로 변경
        }
        $blogs = User::getBlogs($userid);
        foreach ($blogs as $joinedBlog) {
            Blog::deleteUser($joinedBlog, $userid);
        }
        User::removePermanent($userid);
        return true;
    }

    static function removePermanent($userid) {
        $pool = DBModel::getInstance();
        $pool->reset("UserSettings");
        $pool->setQualifier("userid", "eq", $userid);
        $pool->setQualifier("name", "eq", 'AuthToken', true);
        if ($pool->delete()) {
            $pool->reset("Users");
            $pool->setQualifier("userid", "eq", $userid);
            return $pool->delete();
        } else {
            return false;
        }
    }

    static function __generatePassword() {
        return strtolower(substr(base64_encode(rand(0x10000000, 0x70000000)), 3, 8));
    }

    /*@private static@*/
    function __getMaxUserId() {
        $pool = DBModel::getInstance();
        $pool->reset("Users");
        $maxId = $pool->getCell("max(userid)");
        if ($maxId) {
            return $maxId;
        } else {
            return 0;
        }
    }
}

class Blog {
    /*@static@*/
    function changeOwner($blogid, $userid) {
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();
        $pool->reset("Privileges");
        $pool->setAttribute("acl", 3);
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("acl", "eq", BITWISE_OWNER);
        $pool->update();

        $pool->reset("Privileges");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("userid", "eq", $userid);
        $acl = $pool->getCell("acl");

        if ($acl === null) { // If there is no ACL, add user into the blog.
            $pool->reset("Privileges");
            $pool->setAttribute("blogid", $blogid);
            $pool->setAttribute("userid", $userid);
            $pool->setAttribute("acl", BITWISE_OWNER);
            $pool->setAttribute("created", Timestamp::getUNIXtime());
            $pool->setAttribute("lastlogin", 0);
            $pool->insert();
        } else {
            $pool->reset("Privileges");
            $pool->setAttribute("acl", BITWISE_OWNER);
            $pool->setQualifier("blogid", "eq", $blogid);
            $pool->setQualifier("userid", "eq", $userid);
            $pool->update();
        }
        return true;
    }

    /*@static@*/
    /* TODO : remove model dependency (addBlog, sendInvitationMail) */
    function addUser($email, $name, $comment, $senderName, $senderEmail) {
        importlib('model.blog.user');
        importlib('model.blog.blogSetting');

        $blogid = getBlogId();
        if (empty($email)) {
            return 1;
        }
        if (!preg_match('/^[^@]+@([-a-zA-Z0-9]+\.)+[-a-zA-Z0-9]+$/', $email)) {
            return array(2, _t('이메일이 바르지 않습니다.'));
        }

        $isUserExists = User::getUserIdByEmail($email);
        if (empty($isUserExists)) { // If user is not exist
            User::add($email, $name);
        }
        $userid = User::getUserIdByEmail($email);
        $result = addBlog(getBlogId(), $userid, null);
        if ($result === true) {
            return sendInvitationMail(getBlogId(), $userid, User::getName($userid), $comment, $senderName, $senderEmail);
        }
        return $result;
    }

    /*@static@*/
    function deleteUser($blogid = null, $userid, $clean = true) {
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();

        if ($blogid == null) {
            $blogid = getBlogId();
        }
        $pool->reset("Entries");
        $pool->setAttribute("userid", User::getBlogOwner($blogid));
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("userid", "eq", $userid);
        $pool->update();

        // Delete ACL relation.
        $pool->reset("Privileges");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("userid", "eq", $userid);
        if (!$pool->delete()) {
            return false;
        }
        // And if there is no blog related to the specific user, delete user.
        $pool->reset("Privileges");
        $pool->setQualifier("userid", "eq", $userid);

        if ($clean && !$pool->getAll()) {
            User::removePermanent($userid);
        }
        return true;
    }

    /*@static@*/
    function changeACLofUser($blogid, $userid, $ACLtype, $switch) {  // Change user priviledge on the blog.
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();

        if (empty($ACLtype) || empty($userid)) {
            return false;
        }
        $pool->reset("Privileges");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("userid", "eq", $userid);
        $acl = $pool->getCell("acl");
        if ($acl === null) { // If there is no ACL, add user into the blog.
            $name = User::getName($userid);
            $pool->reset("Privileges");
            $pool->setAttribute("blogid", $blogid);
            $pool->setAttribute("userid", $userid);
            $pool->setAttribute("acl", 0);
            $pool->setAttribute("created", Timestamp::getUNIXtime());
            $pool->setAttribute("lastlogin", 0);
            $pool->insert();
            $acl = 0;
        }
        $bitwise = null;
        switch ($ACLtype) {
            case 'admin':
                $bitwise = BITWISE_ADMINISTRATOR;
                break;
            case 'editor':
                $bitwise = BITWISE_EDITOR;
                break;
            default:
                return false;
        }
        if ($switch) {
            $acl |= $bitwise;
        } else {
            $acl &= ~$bitwise;
        }
        $pool->reset("Privileges");
        $pool->setAttribute("acl", $acl);
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("userid", "eq", $userid);
        return $pool->update();
    }
}

class Transaction {
    function pickle($data) {
        $pickle_dir = ROOT . DS . "cache" . DS . "pickle" . DS;
        if (!file_exists($pickle_dir)) {
            mkdir($pickle_dir);
        }
        $tid = sprintf("%010dP%s", time(), md5(microtime(true)));
        while (file_exists($pickle_dir . $tid)) {
            $tid = sprintf("%010dP%s", time(), md5(microtime(true)));
            usleep(50);
        }
        file_put_contents($pickle_dir . $tid, serialize($data));
        return $tid;
    }

    function unpickle($tid) {
        $pickle_file = ROOT . DS . "cache" . DS . "pickle" . DS . $tid;
        $data = unserialize(file_get_contents($pickle_file));
        unlink($pickle_file);
        return $data;
    }

    function repickle($tid, & $data) {
        if (empty($tid)) {
            return;
        }
        $_SESSION['pickle'][$tid] = $data;
        $pickle_dir = ROOT . DS . "cache" . DS . "pickle" . DS;
        if (!file_exists($pickle_dir)) {
            mkdir($pickle_dir);
        }
        file_put_contents($pickle_dir . $tid, serialize($data));
    }

    function taste($tid) {
        $pickle_file = ROOT . DS . "cache" . DS . "pickle" . DS . $tid;
        if (!file_exists($pickle_file)) {
            return null;
        }
        $data = unserialize(file_get_contents($pickle_file));
        return $data;
    }

    function clear() {
        return;
    }

    function gc() {
        return;
    }

    function debug($tid = null) {
        return;
    }
}

?>
