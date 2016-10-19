<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function doesHaveOpenIDPriv(&$comment) {
    $blogid = getBlogId();
    $openid = Acl::getIdentity('openid');

    if (!$comment['secret'] || !$openid) {
        return false;
    }
    if ($comment['openid'] == $openid) {
        return true;
    }
    if (empty($comment['parent'])) {
        return false;
    }

    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $comment['parent']);
    $pool->setQualifier('openid', 'eq', $openid, true);
    $row = $pool->getRow();
    return !empty($row);
}

function decorateComment(&$comment) {
    $authorized = doesHaveOwnership();
    $comment['hidden'] = false;
    $comment['name'] = htmlspecialchars($comment['name']);
    $comment['comment'] = htmlspecialchars($comment['comment']);
    if ($comment['secret'] == 1) {
        if ($authorized) {
            $comment['comment'] = '<span class="hiddenCommentTag_content">' . _text('[비밀댓글]') . '</span> ' . $comment['comment'];
        } else {
            if (!doesHaveOpenIDPriv($comment)) {
                $comment['hidden'] = true;
                $comment['name'] = '<span class="hiddenCommentTag_name">' . _text('비밀방문자') . '</span>';
                $comment['homepage'] = '';
                $comment['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
            } else {
                $comment['name'] = '<span class="hiddenCommentTag_name">' . _text('비밀방문자') . '</span>' . $comment['name'];
            }
        }
    }
}

function getCommentsWithPagingForOwner($blogid, $category, $name, $ip, $search, $page, $count, $isGuestbook = false, $filter_till = null) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $postfix = '';

    if ($category > 0) {
        $pool->reset("Categories");
        $pool->setQualifier("parent", "eq", $category);
        $categories = $pool->getColumn("id");
        array_push($categories, $category);
    }

    $pool->reset("Comments");
    $pool->setAlias("Comments", "c");
    $extension = array(
        array("c.blogid", "eq", "e.blogid"),
        array("c.entry", "eq", "e.id"),
        array("e.draft", "eq", 0)
    );
    if (!$isGuestbook && !Acl::check("group.editors")) {
        array_push($extension, array("e.userid", "eq", getUserId()));
    }
    $pool->setProjection("c.*", "e.title", "c2.name AS parentName");
    $pool->setAlias("Entries", "e");
    $pool->join("Entries", "left", $extension);
    $pool->join("Comments c2", "left", array(array("c.parent", "eq", "c2.id"), array("c.blogid", "eq", "c2.blogid")));
    $pool->setQualifier("c.blogid", "eq", $blogid);
    if (is_null($filter_till)) {
        $pool->setQualifier("c.isfiltered", "eq", 0);
    } else {
        $pool->setQualifier("c.isfiltered", ">", $filter_till);
    }

    if ($category > 0) {
        $pool->setQualifier("e.category", "hasoneof", $categories);
        $postfix .= '&amp;category=' . rawurlencode($category);
    } else {
        $pool->setQualifier("e.category", ">=", 0);
    }
    if (!empty($name)) {
        $pool->setQualifier("c.name", "eq", $name, true);
        $postfix .= '&amp;name=' . rawurlencode($name);
    }
    if (!empty($ip)) {
        $pool->setQualifier("c.ip", "eq", $ip, true);
        $postfix .= '&amp;ip=' . rawurlencode($ip);
    }
    if (!empty($search)) {
        $search = escapeSearchString($search);
        $searchRequest = array(
            array("c.name", "like", $search, true),
            "OR",
            array("c.homepage", "like", $search, true),
            "OR",
            array("c.comment", "like", $search, true)
        );
        $pool->setQualifierSet($searchRequest);
        $postfix .= '&amp;search=' . rawurlencode($search);
    }
    $pool->setOrder("c.written", "desc");
    list($comments, $paging) = Paging::fetch($pool, $page, $count);
    if (strlen($postfix) > 0) {
        $postfix .= '&amp;withSearch=on';
        $paging['postfix'] .= $postfix;
    }

    return array($comments, $paging);
}

function getGuestbookWithPagingForOwner($blogid, $name, $ip, $search, $page, $count) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $postfix = '&amp;status=guestbook';


    $pool->reset("Comments");
    $pool->setAlias("Comments", "c");
    $pool->join("Comments c2", "left", array(array("c.parent", "eq", "c2.id"), array("c.blogid", "eq", "c2.blogid")));
    $pool->setQualifier("c.blogid", "eq", $blogid);
    $pool->setQualifier("c.entry", "eq", 0);
    $pool->setQualifier("c.isfiltered", "eq", 0);
    $pool->setProjection("c.*", "c2.name AS parentName");
    if (!empty($name)) {
        $pool->setQualifier("c.name", "eq", $name, true);
        $postfix .= '&amp;name=' . rawurlencode($name);
    }
    if (!empty($ip)) {
        $pool->setQualifier("c.ip", "eq", $ip, true);
        $postfix .= '&amp;ip=' . rawurlencode($ip);
    }
    if (!empty($search)) {
        $search = escapeSearchString($search);
        $searchRequest = array(
            array("c.name", "like", $search, true),
            "OR",
            array("c.homepage", "like", $search, true),
            "OR",
            array("c.comment", "like", $search, true)
        );
        $pool->setQualifierSet($searchRequest);
        $postfix .= '&amp;search=' . rawurlencode($search);
    }

    $pool->setOrder("c.written", "desc");
    list($comments, $paging) = Paging::fetch($pool, $page, $count);
    if (strlen($postfix) > 0) {
        $postfix .= '&amp;withSearch=on';
        $paging['postfix'] .= $postfix;
    }

    return array($comments, $paging);
}

function getCommentsNotifiedWithPagingForOwner($blogid, $category, $name, $ip, $search, $page, $count) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();

    $postfix = '';

    if (empty($name) && empty($ip) && empty($search)) {
        $pool->init("CommentsNotified");
        $pool->setAlias("CommentsNotified", "c");
        $pool->setAlias("CommentsNotifiedSiteInfo", "csiteinfo");
        $pool->join("CommentsNotifiedSiteInfo", "left", array(
            array("c.siteid", "eq", "csiteinfo.id")
        ));
        $pool->setQualifier("c.blogid", "eq", $blogid);
        $pool->setQualifier("c.parent", "eq", null);
        $pool->setOrder("c.modified", "desc");
        $pool->setProjection("c.*", "csiteinfo.title AS siteTitle", "csiteinfo.name AS nickname", "csiteinfo.url AS siteUrl", "csiteinfo.modified AS siteModified");

    } else {
        if (!empty($search)) {
            $search = escapeSearchString($search);
        }
        $pool->init("CommentsNotified");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("parent", "neq", null);
        if (!empty($name)) {
            $pool->setQualifier("name", "eq", $name, true);
        }
        if (!empty($ip)) {
            $pool->setQualifier("ip", "eq", $ip, true);
        }
        if (!empty($search)) {
            $pool->setQualifierSet(
                array("name", "like", $search, true),
                "OR",
                array("homepage", "like", $search, true),
                "OR",
                array("comment", "like", $search, true)
            );
        }
        $childList = array_unique($pool->getColumn("parent"));

        $pool->init("CommentsNotified");
        $pool->setAlias("CommentsNotified", "c");
        $pool->setAlias("CommentsNotifiedSiteInfo", "csiteinfo");
        $pool->join("CommentsNotifiedSiteInfo", "left", array(
            array("c.siteid", "eq", "csiteinfo.id")
        ));
        $pool->setQualifier("c.blogid", "eq", $blogid);
        if (count($childList) != 0) {
            $pool->setQualifierSet(array(
                array("c.parent", "eq", null),
                "OR",
                array("c.id", "hasoneof", $childList)
            ));
        } else {
            $pool->setQualifier("c.parent", "eq", null);
        }

        $pool->setQualifier("c.parent", "eq", null);
        $pool->setOrder("c.modified", "desc");
        $pool->setProjection("c.*", "csiteinfo.title AS siteTitle", "csiteinfo.name AS nickname", "csiteinfo.url AS siteUrl", "csiteinfo.modified AS siteModified");

        if (!empty($name)) {
            $pool->setQualifier("c.name", "eq", $name, true);
            $postfix .= '&amp;name=' . rawurlencode($name);
        }
        if (!empty($ip)) {
            $pool->setQualifier("c.ip", "eq", $ip, true);
            $postfix .= '&amp;ip=' . rawurlencode($ip);
        }
        if (!empty($search)) {
            $pool->setQualifierSet(
                array("c.name", "like", $search, true),
                "OR",
                array("c.homepage", "like", $search, true),
                "OR",
                array("c.comment", "like", $search, true)
            );
            $postfix .= '&amp;search=' . rawurlencode($search);
        }
    }

    list($comments, $paging) = Paging::fetch($pool, $page, $count);
    if (strlen($postfix) > 0) {
        $postfix .= '&amp;withSearch=on';
        $paging['postfix'] .= $postfix;
    }

    return array($comments, $paging);
}

function getCommentCommentsNotified($parent) {
    $context = Model_Context::getInstance();
    $comments = array();
    $authorized = doesHaveOwnership();
    $pool = DBModel::getInstance();
    $pool->init("CommentsNotified");
    $pool->setAlias("CommentsNotified", "c");
    $pool->setAlias("CommentsNotifiedSiteInfo", "csiteinfo");
    $pool->join("CommentsNotifiedSiteInfo", "left", array(
        array("c.siteid", "eq", "csiteinfo.id")
    ));
    $pool->setQualifier("c.blogid", "eq", getBlogId());
    $pool->setQualifier("c.parent", "eq", $parent);
    $pool->setOrder("c.written", "asc");
    if ($result = $pool->getAll("c.*,csiteinfo.title AS siteTitle,csiteinfo.name AS nickname, csiteinfo.url AS siteUrl, csiteinfo.modified AS siteModified")) {
        foreach ($result as $comment) {
            if (($comment['secret'] == 1) && !$authorized) {
                if (!doesHaveOpenIDPriv($comment)) {
                    $comment['name'] = '';
                    $comment['homepage'] = '';
                    $comment['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
                }
            }
            array_push($comments, $comment);
        }
    }
    return $comments;
}

function getCommentsWithPagingByEntryId($blogid, $entryId, $page, $count, $url = null, $prefix = '?page=', $postfix = '', $countItem = null, $order = 'DESC') {

    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $comments = array();

    $pool->reset("Comments");
    $pool->setQualifier("blogid", "eq", $blogid);
    if ($entryId != -1) {
        $pool->setQualifier("entry", "eq", $entryId);
    } else {
        $pool->setQualifier("entry", ">", 0);
    }
    $pool->setQualifier("parent", "eq", null);
    $pool->setQualifier("isfiltered", "eq", 0);
    $pool->setOrder("written", ($order == 'DESC' ? "DESC" : "ASC"));
    $pool->setProjection("*");
    list($comments, $paging) = Paging::fetch($pool, $page, $count, $url, $prefix, $countItem);
    $paging['postfix'] = $postfix;
    $comments = coverComments($comments);
    return array($comments, $paging);
}

function getCommentsWithPaging($blogid, $page, $count, $url = null, $prefix = '?page=', $postfix = '', $countItem = null) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $comments = array();

    $pool->reset("Comments");
    $pool->setAlias("Comments", "r");
    $pool->join("Entries", "inner", array(array("r.blogid", "eq", "e.blogid"), array("r.entry", "eq", "e.id"), array("e.draft", "eq", 0)));
    $pool->setAlias("Entries", "e");
    $pool->join("Categories", "left", array(array("e.blogid", "eq", "c.blogid"), array("e.category", "eq", "c.id")));
    $pool->setAlias("Categories", "c");
    $pool->setQualifier("r.blogid", "eq", $blogid);
    $pool->setQualifier("e.draft", "eq", 0);
    $pool->setQualifier("r.parent", "eq", null);
    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">=", 2);
    }
    $pool->setQualifier("r.entry", ">", 0);
    $pool->setQualifier("r.isfiltered", "eq", 0);
    $pool->setOrder("r.written", "desc");
    $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    list($comments, $paging) = Paging::fetch($pool, $page, $count, $url, $prefix, $countItem);
    $paging['postfix'] = $postfix;
    $comments = coverComments($comments);
    return array($comments, $paging);
}

function getCommentsWithPagingForGuestbook($blogid, $page, $count) {
    return getCommentsWithPagingByEntryId($blogid, 0, $page, $count);
}

function getCommentAttributes($blogid, $id, $attributeNames) {
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    return $pool->getRow($attributeNames);
}

function getComments($entry, $order = 'ASC') {
    $comments = array();
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $context->getProperty('blog.id'));
    $pool->setQualifier('entry', 'eq', $entry);
    $pool->setQualifier('parent', 'eq', NULL);
    $pool->setQualifier('isfiltered', 'eq', 0);
    if ($entry == 0) {
        $pool->setOrder('written', 'desc');
    } else {
        if ($order == 'DESC') {
            $pool->setOrder('id', 'desc');
        } else {
            $pool->setOrder('id', 'asc');
        }
    }
    if ($result = $pool->getAll()) {
        $comments = coverComments($result);
    }
    return $comments;
}

function coverComments($comments) {
    $result = array();
    $authorized = doesHaveOwnership();

    foreach ($comments as $comment) {
        if (($comment['secret'] == 1) && !$authorized) {
            if (!doesHaveOpenIDPriv($comment)) {
                $comment['name'] = '';
                $comment['homepage'] = '';
                $comment['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
            }
        }
        if (!empty($comment['replier'])) {
            $comment['homepage'] = User::getHomepage($comment['replier']);
        }
        array_push($result, $comment);
    }
    return $result;
}

function getCommentComments($parent, $parentComment = null) {
    $comments = array();
    $authorized = doesHaveOwnership();

    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', getBlogId());
    $pool->setQualifier('parent', 'eq', $parent);
    $pool->setQualifier('isfiltered', 'eq', 0);
    $pool->setOrder('written');

    $row = $pool->getRow();

    if ($result = $pool->getAll()) {
        if ($parentComment == null) {
            $pool->reset('Comments');
            $pool->setQualifier('blogid', 'eq', getBlogId());
            $pool->setQualifier('id', 'eq', $parent);
            $parentComment = $pool->getRow();
        }
        $parentByOpenid = !empty($parentComment['openid']);
        foreach ($result as $comment) {
            if (($comment['secret'] == 1) && !$authorized) {
                if (!doesHaveOpenIDPriv($comment)) {
                    $comment['name'] = '';
                    $comment['homepage'] = '';
                    $comment['comment'] =
                        $parentByOpenid ?
                            _text('비밀글의 작성자만 읽을 수 있는 댓글입니다.') :
                            _text('관리자만 볼 수 있는 댓글입니다.');
                }
            }
            if (!empty($comment['replier'])) {
                $comment['homepage'] = User::getHomepage($comment['replier']);
            }
            array_push($comments, $comment);
        }
    }
    return $comments;
}

function isCommentWriter($blogid, $commentid) {
    if (!doesHaveMembership()) {
        return false;
    }
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $commentid);
    $pool->setQualifier('replier', 'eq', getUserId());
    return $pool->doesExist('replier');
}

function getComment($blogid, $id, $password, $restriction = true) {
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);

    if ($restriction == true) {
        if (!doesHaveOwnership()) {
            if (doesHaveMembership()) {
                $pool->setQualifier('replier', 'eq', getUserId());
            } else {
                $pool->setQualifier('password', 'eq', md5($password), true);
            }
        }
    }
    if ($result = $pool->getRow()) {
        if ($restriction != true) {
            $result['password'] = null;
        } // scope.
        return $result;
    }
    return false;
}

function getCommentList($blogid, $search) {
    $list = array('title' => "$search", 'items' => array());
    $search = escapeSearchString($search);

    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setAlias("Comments", "c");
    $pool->setAlias("Entries", "e");
    $pool->join("Entries", "inner", array(
        array("c.entry", "eq", "e.id"),
        array("c.blogid", "eq", "e.blogid"),
        array("e.draft", "eq", 0)
    ));
    $pool->setQualifier('c.blogid', 'eq', $blogid);
    $pool->setQualifier('c.entry', '>', 0);
    $pool->setQualifier('parent', 'eq', NULL);
    $pool->setQualifier('isfiltered', 'eq', 0);
    $pool->setQualifierSet(
        array("c.comment", "like", $search, true),
        "OR",
        array("c.name", "like", $search, true)
    );
    if (doesHaveOwnership()) {
        $pool->setQualifier("c.secret", "eq", 0);
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    $pool->setOrder("c.written", "asc");

    if ($entry == 0) {
        $pool->setOrder('written', 'desc');
    } else {
        if ($order == 'DESC') {
            $pool->setOrder('id', 'desc');
        } else {
            $pool->setOrder('id', 'asc');
        }
    }
    if ($result = $pool->getAll()) {
        $comments = coverComments($result);
    }

    if ($result = $pool->getAll("c.id, c.entry, c.parent, c.name, c.comment, c.written, e.slogan")) {
        foreach ($result as $comment)
            array_push($list['items'], $comment);
    }
    return $list;
}

function updateCommentsOfEntry($blogid, $entryId) {
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('entry', 'eq', $entryId);
    $pool->setQualifier('isfiltered', 'eq', 0);
    $commentCount = $pool->getCell('COUNT(*)');

    $pool->reset('Entries');
    $pool->setAttribute('comments', $commentCount);
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $entryId);
    $pool->update();
    if ($entryId >= 0) {
        CacheControl::flushEntry($entryId);
    }
    return $commentCount;
}

function sendCommentPing($entryId, $permalink, $name, $homepage) {
    return true;
    /*	Legacy code. TODO : delete after confirm.
        global $blog;
        $blogid = getBlogId();
        if($slogan = POD::queryCell("SELECT slogan
            FROM ".$context->getProperty('database.prefix')."Entries
            WHERE blogid = $blogid
                AND id = $entryId
                AND draft = 0
                AND visibility = 3
                AND acceptcomment = 1")) {
            $rpc = new XMLRPC();
            $rpc->url = TEXTCUBE_SYNC_URL;
            $summary = array(
                'permalink' => $permalink,
                'name' => $name,
                'homepage' => $homepage
            );
            $rpc->async = true;
            $rpc->call('sync.comment', $summary);
        }*/
}

function addComment($blogid, & $comment) {
    $pool = DBModel::getInstance();

    $openid = Acl::getIdentity('openid');
    $filtered = 0;

    if (!doesHaveOwnership()) {
        if (!Filter::isAllowed($comment['homepage'])) {
            if (Filter::isFiltered('ip', $comment['ip'])) {
                $blockType = "ip";
                $filtered = 1;
            } else {
                if (Filter::isFiltered('name', $comment['name'])) {
                    $blockType = "name";
                    $filtered = 1;
                } else {
                    if (Filter::isFiltered('url', $comment['homepage'])) {
                        $blockType = "homepage";
                        $filtered = 1;
                    } elseif (Filter::isFiltered('content', $comment['comment'])) {
                        $blockType = "comment";
                        $filtered = 1;
                    } elseif (!Acl::check("group.writers") && !$openid &&
                        Setting::getBlogSettingGlobal('AddCommentMode', '') == 'openid'
                    ) {
                        $blockType = "openidonly";
                        $filtered = 1;
                    } else {
                        if (!fireEvent('AddingComment', true, $comment)) {
                            $blockType = "etc";
                            $filtered = 1;
                        }
                    }
                }
            }
        }
    }

    $comment['homepage'] = stripHTML($comment['homepage']);
    $comment['name'] = Utils_Unicode::lessenAsEncoding($comment['name'], 80);
    $comment['homepage'] = Utils_Unicode::lessenAsEncoding($comment['homepage'], 80);
    $comment['comment'] = Utils_Unicode::lessenAsEncoding($comment['comment'], 65535);

    if (!doesHaveOwnership() && $comment['entry'] != 0) {
        $pool->reset('Entries');
        $pool->setQualifier('blogid', 'eq', $blogid);
        $pool->setQualifier('id', 'eq', $comment['entry']);
        $pool->setQualifier('draft', 'eq', 0);
        $pool->setQualifier('visibility', 'b', 0);
        $pool->setQualifier('acceptcomment', 'eq', 1);
        $result = $pool->getCount();
        if (!$result || $result == 0) {
            return false;
        }
    }
    $parent = $comment['parent'] == null ? null : $comment['parent'];
    $userid = getUserId();
    if (!empty($userid)) {
        $comment['replier'] = $userid;
        $name = User::getName($userid);
        $password = '';
        $homepage = User::getHomepage($userid);
		if (empty($homepage)) {
		    if ($openid) {
	            $homepage = $openid;
			} else {
				$homepage = '';
			}
        } 
    } else {
        $comment['replier'] = null;
        $name = $comment['name'];
        $password = empty($comment['password']) ? '' : md5($comment['password']);
        $homepage = $comment['homepage'];
    }
    $comment0 = $comment['comment'];
    $filteredAux = ($filtered == 1 ? Timestamp::getUNIXtime() : 0);
    $insertId = getCommentsMaxId() + 1;

    $pool->reset('Comments');
    $pool->setAttribute('blogid', $blogid);
    $pool->setAttribute('replier', $comment['replier']);
    $pool->setAttribute('id', $insertId);
    if (is_null($openid)) {
        $pool->setAttribute('openid', '', true);
    } else {
        $pool->setAttribute('openid', $openid, true);
    }
    $pool->setAttribute('entry', $comment['entry']);
    $pool->setAttribute('parent', $parent);
    $pool->setAttribute('name', $name, true);
    $pool->setAttribute('password', $password, true);
    $pool->setAttribute('homepage', $homepage, true);
    $pool->setAttribute('secret', $comment['secret']);
    $pool->setAttribute('comment', $comment0, true);
    $pool->setAttribute('ip', $comment['ip'], true);
    $pool->setAttribute('written', Timestamp::getUNIXtime());
    $pool->setAttribute('isfiltered', $filteredAux);
    $result = $pool->insert();

    if ($result) {
        $id = $insertId;
        if ($filtered != 1) {
            CacheControl::flushCommentRSS($comment['entry']);
            CacheControl::flushDBCache('comment');
            if ($parent != 'null' && $comment['secret'] < 1) {
                $insertId = getCommentsNotifiedQueueMaxId() + 1;
                $pool->reset('CommentsNotifiedQueue');
                $pool->setAttribute('blogid', $blogid);
                $pool->setAttribute('id', $insertId);
                $pool->setAttribute('commentid', $id);
                $pool->setAttribute('sendstatus', 0);
                $pool->setAttribute('checkdate', 0);
                $pool->setAttribute('written', Timestamp::getUNIXtime());
                $pool->insert();
            }
            updateCommentsOfEntry($blogid, $comment['entry']);
            fireEvent($comment['entry'] ? 'AddComment' : 'AddGuestComment', $id, $comment);
            return $id;
        } else {
            return $blockType;
        }
    }
    return false;
}

function updateComment($blogid, $comment, $password) {
    $openid = Acl::getIdentity('openid');
    if (!doesHaveOwnership()) {
        // if filtered, only block and not send to trash
        if (!Filter::isAllowed($comment['homepage'])) {
            if (Filter::isFiltered('ip', $comment['ip'])) {
                return 'blocked';
            }
            if (Filter::isFiltered('name', $comment['name'])) {
                return 'blocked';
            }
            if (Filter::isFiltered('url', $comment['homepage'])) {
                return 'blocked';
            }
            if (Filter::isFiltered('content', $comment['comment'])) {
                return 'blocked';
            }
            if (!fireEvent('ModifyingComment', true, $comment)) {
                return 'blocked';
            }
        }
    }

    $pool = DBModel::getInstance();

    $comment['homepage'] = stripHTML($comment['homepage']);
    $comment['name'] = Utils_Unicode::lessenAsEncoding($comment['name'], 80);
    $comment['homepage'] = Utils_Unicode::lessenAsEncoding($comment['homepage'], 80);
    $comment['comment'] = Utils_Unicode::lessenAsEncoding($comment['comment'], 65535);

    $guestcomment = false;
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $comment['id']);
    $pool->setQualifier('replier', 'eq', NULL);
    if ($pool->doesExist()) {
        $guestcomment = true;
    }

    $pool->reset('Comments');

    $setPassword = '';
    $userid = getUserId();
    if (!empty($userid)) {
        $comment['replier'] = $userid;
        $name = User::getName($userid);
        $homepage = User::getHomepage($userid);
        $pool->setAttribute('password', '', true);
        if (empty($homepage) && $openid) {
            $homepage = $openid;
        }
    } else {
        $name = $comment['name'];
        if ($comment['password'] !== true) {
            $pool->setAttribute('password', (empty($comment['password']) ? '' : md5($comment['password'])), true);
        }
        $homepage = $comment['homepage'];
    }
    $comment0 = $comment['comment'];

    $wherePassword = '';
    if (!doesHaveOwnership()) {
        if ($guestcomment == false) {
            if (!doesHaveMembership()) {
                return false;
            }
            $pool->setQualifier('replier', 'eq', $userid);
        } else {
            if (empty($password) && $openid) {
                $pool->setQualifier('openid', 'eq', $openid, true);
            } else {
                $pool->setQualifier('password', 'eq', md5($password), true);
            }
        }
    }

    $replier = is_null($comment['replier']) ? NULL : $comment['replier'];

    $pool->setAttribute('name', $name, true);
    $pool->setAttribute('homepage', $homepage, true);
    $pool->setAttribute('secret', $comment['secret']);
    $pool->setAttribute('comment', $comment0, true);
    $pool->setAttribute('ip', $comment['ip'], true);
    $pool->setAttribute('written', Timestamp::getUNIXtime());
    $pool->setAttribute('isfiltered', $comment['isfiltered']);
    $pool->setAttribute('replier', $replier);
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $comment['id']);
    $result = $pool->update();

    if ($result) {
        CacheControl::flushCommentRSS($comment['entry']); // Assume blogid = current blogid.
        CacheControl::flushDBCache('comment');
        return true;
    } else {
        return false;
    }
}

function deleteComment($blogid, $id, $entry, $password) {
    if (!is_numeric($id)) {
        return false;
    }
    if (!is_numeric($entry)) {
        return false;
    }


    $pool = DBModel::getInstance();

    $guestcomment = false;
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    $pool->setQualifier('replier', 'eq', NULL);
    if ($pool->doesExist()) {
        $guestcomment = true;
    }

    $wherePassword = '';
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    $pool->setQualifier('entry', 'eq', $entry);
    if (!doesHaveOwnership()) {
        if (Acl::getIdentity('openid') && empty($password)) {
            $pool->setQualifier('openid', Acl::getIdentity('openid'), true);
        } else {
            if ($guestcomment == false) {
                if (!doesHaveMembership()) {
                    return false;
                }
                $pool->setQualifier('replier', 'eq', getUserId());
            } else {
                $pool->setQualifier('password', 'eq', md5($password), true);
            }
        }
    }
    if ($pool->getCount()) {
        CacheControl::flushCommentRSS($entry);
        CacheControl::flushDBCache('comment');
        updateCommentsOfEntry($blogid, $entry);
        return true;
    }
    return false;
}

function trashComment($blogid, $id, $entry, $password) {
    if (!doesHaveOwnership()) {
        return false;
    }
    if (!is_numeric($id)) {
        return false;
    }
    if (!is_numeric($entry)) {
        return false;
    }

    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setAttribute('isfiltered', Timestamp::getUNIXtime());
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    $pool->setQualifier('entry', 'eq', $entry);
    $affected = $pool->update('count');

    $pool->reset('Comments');
    $pool->setAttribute('isfiltered', Timestamp::getUNIXtime());
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('parent', 'eq', $id);
    $pool->setQualifier('entry', 'eq', $entry);

    $affectedChildren = $pool->update('count');

    if ($affected + $affectedChildren > 0) {
        CacheControl::flushCommentRSS($entry);
        CacheControl::flushDBCache('comment');
        filterTrashComments($blogid);
        updateCommentsOfEntry($blogid, $entry);
        return true;
    }
    return false;
}

function filterTrashComments($blogid, $id = null) {
    if (!doesHaveOwnership()) {
        return false;
    }
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    if (is_null($id)) {
        $pool->setQualifier('blogid', 'eq', $blogid);
        $pool->setQualifier('isfiltered', '>', 0);
        $ids = $pool->getColumn('id');
        if (!empty($ids)) {
            foreach ($ids as $id) {
                moveCommentToTrash($blogid, $id);
            }
        }
    } else {
        moveCommentToTrash($blogid, $id);
    }
}

function moveCommentToTrash($blogid, $id) {
    // TODO: implement 'copy' method to DBModel and rewrite.
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    $comment = $pool->getRow();
    // Write into trashcan
    $pool->reset('TrashComments');
    $pool->setAttribute('blogid', $comment['blogid']);
    $pool->setAttribute('id', $comment['id']);
    $pool->setAttribute('entry', $comment['entry']);
    $pool->setAttribute('parent', $comment['parent']);
    $pool->setAttribute('openid', $comment['openid'], true);
    $pool->setAttribute('name', $comment['name'], true);
    $pool->setAttribute('password', $comment['password'], true);
    $pool->setAttribute('homepage', $comment['homepage'], true);
    $pool->setAttribute('secret', $comment['secret']);
    $pool->setAttribute('comment', $comment['comment'], true);
    $pool->setAttribute('ip', $comment['ip'], true);
    $pool->setAttribute('written', $comment['written']);
    $pool->setAttribute('isfiltered', Timestamp::getUNIXtime());
    $result = $pool->insert();
    if ($result) {
        $pool->reset('Comments');
        $pool->setQualifier('blogid', 'eq', $blogid);
        $pool->setQualifier('id', 'eq', $id);
        $pool->setQualifier('entry', 'eq', $comment['entry']);
        return $pool->delete();
    } else {
        return false;
    }
}

function revertTrashToComment($blogid, $id) {
    // TODO: implement 'copy' method to DBModel and rewrite.
    // BUG:  ID field could conflict with newly inserted ids.
    $pool->reset('TrashComments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    $comment = $pool->getRow();
    // Write into trashcan
    $pool->reset('Comments');
    $pool->setAttribute('blogid', $comment['blogid']);
    $pool->setAttribute('id', $comment['id']);
    $pool->setAttribute('entry', $comment['entry']);
    $pool->setAttribute('parent', $comment['parent']);
    $pool->setAttribute('openid', $comment['openid'], true);
    $pool->setAttribute('name', $comment['name'], true);
    $pool->setAttribute('password', $comment['password'], true);
    $pool->setAttribute('homepage', $comment['homepage'], true);
    $pool->setAttribute('secret', $comment['secret']);
    $pool->setAttribute('comment', $comment['comment'], true);
    $pool->setAttribute('ip', $comment['ip'], true);
    $pool->setAttribute('written', $comment['written']);
    $pool->setAttribute('isfiltered', Timestamp::getUNIXtime());
    $result = $pool->insert();
    if ($result) {
        $pool->reset('TrashComments');
        $pool->setQualifier('blogid', 'eq', $blogid);
        $pool->setQualifier('id', 'eq', $id);
        $pool->setQualifier('entry', 'eq', $comment['entry']);
        return $pool->delete();
    } else {
        return false;
    }
}

function getRecentComments($blogid, $count = false, $isGuestbook = false, $guestShip = false) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $comments = array();

    $pool->init("Comments");
    if (!(doesHaveOwnership() && !$guestShip)) {
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    $pool->setAlias("Comments", "r");
    $pool->setAlias("Entries", "e");
    $joint = array(
        array("r.blogid", "eq", "e.blogid"),
        array("r.entry", "eq", "e.id"),
        array("e.draft", "eq", 0));

    if (doesHaveOwnership() && !$guestShip) {
        if (!$isGuestbook && !Acl::check("group.editors")) {
            array_push($joint, array("e.userid", "eq", getUserId()));
        }
        $pool->join("Entries", "inner", $joint);
        $pool->setQualifier("r.blogid", "eq", $blogid);
    } else {
        $pool->setAlias("Categories", "c");
        $pool->join("Entries", "inner", $joint);
        $pool->join("Categories", "left outer", array(
            array("e.blogid", "eq", "c.blogid"),
            array("e.category", "eq", "c.id")
        ));
        $pool->setQualifier("r.blogid", "eq", $blogid);
        $pool->setQualifier("e.draft", "eq", 0);
        $pool->setQualifier("e.visibility", ">=", 2);
    }
    if ($isGuestbook != false) {
        $pool->setQualifier("r.entry", "eq", 0);
    } else {
        $pool->setQualifier("r.entry", ">", 0);
    }
    $pool->setQualifier("r.isfiltered", "eq", 0);

    $pool->setOrder("r.written", "desc");
    $pool->setLimit(($count != false ? $count : $context->getProperty('skin.commentsOnRecent')));;
    if ($result = $pool->getAll("r.*, e.title, e.slogan", array("usedbcache" => true, "dbprefix" => 'comment'))) {
        foreach ($result as $comment) {
            if (($comment['secret'] == 1) && !doesHaveOwnership()) {
                if (!doesHaveOpenIDPriv($comment)) {
                    $comment['name'] = _text('비밀방문자');
                    $comment['homepage'] = '';
                    $comment['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
                }
            }
            array_push($comments, $comment);
        }
    }
    return $comments;
}

function getRecentGuestbook($blogid, $count = false) {
    $context = Model_Context::getInstance();
    $comments = array();
    $pool = DBModel::getInstance();
    $pool->init("Comments");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("entry", "eq", 0);
    $pool->setQualifier("isfiltered", "eq", 0);
    $pool->setOrder("written", "desc");
    $pool->setLimit(($count != false ? $count : $context->getProperty('skin.commentsOnRecent')));

    if ($result = $pool->getAll()) {
        foreach ($result as $comment) {
            if (($comment['secret'] == 1) && !doesHaveOwnership()) {
                if (!doesHaveOpenIDPriv($comment)) {
                    $comment['name'] = '';
                    $comment['homepage'] = '';
                    $comment['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
                }
            }
            array_push($comments, $comment);
        }
    }
    return $comments;
}

function getGuestbookPageById($blogid, $id) {
    return getCommentPageById($blogid, 0, $id);
}

function getCommentPageById($blogid, $entryId, $commentId) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $pool->init("Comments");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("entry", "eq", $entryId);
    $pool->setQualifier("isfiltered", "eq", 0);
    $pool->setQualifier("parent", "eq", null);
    $pool->setOrder("written", "desc");
    $totalGuestbookId = $pool->getColumn("id");
    $order = array_search($commentId, $totalGuestbookId);
    if ($order == false) {
        $pool->init("Comments");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("entry", "eq", $entryId);
        $pool->setQualifier("isfiltered", "eq", 0);
        $pool->setQualifier("id", "eq", $commentId);
        $parentCommentId = $pool->getCell("parent");
        if ($parentCommentId != false) {
            $order = array_search($parentCommentId, $totalGuestbookId);
        } else {
            return false;
        }
    }
    $base = ($entryId == 0 ? $context->getProperty('skin.commentsOnGuestbook') : $context->getProperty('skin.commentsOnEntry'));
    return intval($order / $base) + 1;
}

function deleteCommentInOwner($blogid, $id) {
    if (!is_numeric($id)) {
        return false;
    }
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    $entryId = $pool->getCell('entry');
    if ($pool->delete()) {
        $pool->unsetQualifier('id');
        $pool->setQualifier('parent', 'eq', $id);
        if ($pool->delete()) {
            CacheControl::flushCommentRSS($entryId);
            updateCommentsOfEntry($blogid, $entryId);
            return true;
        }
    }
    return false;
}

function trashCommentInOwner($blogid, $id) {
    if (!is_numeric($id)) {
        return false;
    }
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    $entryId = $pool->getCell('entry');
    $pool->setAttribute('isfiltered', Timestamp::getUNIXtime());
    if ($pool->update()) {
        $pool->unsetQualifier('id');
        $pool->setQualifier('parent', 'eq', $id);
        if ($pool->update()) {
            CacheControl::flushCommentRSS($entryId);
            CacheControl::flushDBCache('comment');
            filterTrashComments($blogid, $id);
            updateCommentsOfEntry($blogid, $entryId);
            return true;
        }
    }
    return false;
}

function trashCommentInOwnerByIP($blogid, $ip) {
    $pool = DBModel::getInstance();
    $pool->reset('Comments');

    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('ip', 'eq', $ip, true);
    $ids = $pool->getColumn('id');

    foreach ($ids as $id) {
        trashCommentInOwner($blogid, $id);
    }
    return true;
}

function revertCommentInOwner($blogid, $id) {
    if (!is_numeric($id)) {
        return false;
    }
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    $entryId = $pool->getCell('entry');
    $parent = $pool->getCell('parent');
    $pool->setAttribute('isfiltered', 0);
    if ($pool->update()) {
        if (!is_null($parent)) {
            $pool->setQualifier('id', 'eq', $parent);
        }
        if (is_null($parent) || $pool->update()) {
            CacheControl::flushCommentRSS($entryId);
            updateCommentsOfEntry($blogid, $entryId);
            return true;
        }
    }
    return false;
}

function deleteCommentNotifiedInOwner($blogid, $id) {
    if (!is_numeric($id)) {
        return false;
    }

    fireEvent('DeleteCommentNotified', $id);

    $pool = DBModel::getInstance();
    $pool->reset('CommentsNotified');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    $entryId = $pool->getCell('entry');
    if ($pool->delete()) {
        $pool->unsetQualifier('id');
        $pool->setQualifier('parent', 'eq', $id);
        if ($pool->delete()) {
            updateCommentsOfEntry($blogid, $entryId);
            CacheControl::flushCommentNotifyRSS();
            return true;
        }
    }
    return false;
}

function notifyComment() {
    $blogid = getBlogId();
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $pool->init("CommentsNotifiedQueue");
    $pool->setAlias("CommentsNotifiedQueue", "CNQ");
    $pool->setAlias("Comments", "CN");
    $pool->join("Comments", "left", array(
        array("CNQ.commentid", "eq", "CN.id")
    ));
    $pool->setQualifier("CNQ.sendstatus", "eq", 0);
    $pool->setQualifier("CN.parent", "neq", null);
    $pool->setOrder("CNQ.id", "asc");
    $pool->setLimit(1);
    $queue = $pool->getRow("CN.*,
				CNQ.id AS queueId,
				CNQ.commentid AS commentid,
				CNQ.sendstatus AS sendstatus,
				CNQ.checkdate AS checkdate,
				CNQ.written  AS queueWritten");
    if (empty($queue) && empty($queue['queueId'])) {
        return false;
    }
    $pool->init("Comments");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $queue['commentid']);
    $comments = $pool->getRow();
    if (empty($comments['parent']) || $comments['secret'] == 1) {
        $pool->init("CommentsNotifiedQueue");
        $pool->setQualifier("id", "eq", $queue['queueId']);
        $pool->delete();
        return false;
    }
    $pool->init("Comments");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $queue['parent']);
    $parentComments = $pool->getRow();
    if (empty($parentComments['homepage'])) {
        $pool->init("CommentsNotifiedQueue");
        $pool->setQualifier("id", "eq", $queue['queueId']);
        $pool->delete();
        return false;
    }
    $pool->init("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $comments['entry']);
    $entry = $pool->getRow();

    if (is_null($entry)) {
        $r1_comment_check_url = rawurlencode($context->getProperty('uri.default') . "/guestbook/" . $parentComments['id'] . "#guestbook" . $parentComments['id']);
        $r2_comment_check_url = rawurlencode($context->getProperty('uri.default') . "/guestbook/" . $comments['id'] . "#guestbook" . $comments['id']);
        $entry['title'] = _textf('%1 블로그의 방명록', $context->getProperty('blog.title'));
        $entryPermaLink = $context->getProperty('uri.default') . "/guestbook/";
        $entry['id'] = 0;
    } else {
        $r1_comment_check_url = rawurlencode($context->getProperty('uri.default') . "/" . ($context->getProperty('blog.useSloganOnPost') ? "entry/{$entry['slogan']}" : $entry['id']) . "#comment" . $parentComments['id']);
        $r2_comment_check_url = rawurlencode($context->getProperty('uri.default') . "/" . ($context->getProperty('blog.useSloganOnPost') ? "entry/{$entry['slogan']}" : $entry['id']) . "#comment" . $comments['id']);
        $entryPermaLink = $context->getProperty('uri.default') . "/" . ($context->getProperty('blog.useSloganOnPost') ? "entry/{$entry['slogan']}" : $entry['id']);
    }

    $data = "url=" . rawurlencode($context->getProperty('uri.default')) . "&mode=fb" . "&s_home_title=" . rawurlencode($context->getProperty('blog.title')) . "&s_post_title=" . rawurlencode($entry['title']) . "&s_name=" . rawurlencode($comments['name']) . "&s_no=" . rawurlencode($comments['entry']) . "&s_url=" . rawurlencode($entryPermaLink) . "&r1_name=" . rawurlencode($parentComments['name']) . "&r1_no=" . rawurlencode($parentComments['id']) . "&r1_pno=" . rawurlencode($comments['entry']) . "&r1_rno=0" . "&r1_homepage=" . rawurlencode($parentComments['homepage']) . "&r1_regdate=" . rawurlencode($parentComments['written']) . "&r1_url=" . $r1_comment_check_url . "&r2_name=" . rawurlencode($comments['name']) . "&r2_no=" . rawurlencode($comments['id']) . "&r2_pno=" . rawurlencode($comments['entry']) . "&r2_rno=" . rawurlencode($comments['parent']) . "&r2_homepage=" . rawurlencode($comments['homepage']) . "&r2_regdate=" . rawurlencode($comments['written']) . "&r2_url=" . $r2_comment_check_url . "&r1_body=" . rawurlencode($parentComments['comment']) . "&r2_body=" . rawurlencode($comments['comment']);
    if (strpos($parentComments['homepage'], "http://") === false) {
        $homepage = 'http://' . $parentComments['homepage'];
    } else {
        $homepage = $parentComments['homepage'];
    }
    $request = new HTTPRequest('POST', $homepage);
    $request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
    $request->content = $data;
    if ($request->send()) {
        $xmls = new XMLStruct();
        if ($xmls->open($request->responseText)) {
            $result = $xmls->selectNode('/response/error/');
            if ($result['.value'] != '1' && $result['.value'] != '0') {
                $homepage = rtrim($homepage, '/') . '/index.php';
                $request = new HTTPRequest('POST', $homepage);
                $request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
                $request->content = $data;
                if ($request->send()) {
                }
            }
        }
    }
    $pool->init("CommentsNotifiedQueue");
    $pool->setQualifier("id", "eq", $queue['queueId']);
    $pool->delete();
}

function receiveNotifiedComment($post) {
    if (empty($post['mode']) || $post['mode'] != 'fb') {
        return 1;
    }
    $context = Model_Context::getInstance();

    CacheControl::flushCommentNotifyRSS();
    $post = fireEvent('ReceiveNotifiedComment', $post);
    if ($post === false) {
        return 7;
    }

    $pool = DBModel::getInstance();
    $blogid = getBlogId();
    $title = Utils_Unicode::lessenAsEncoding($post['s_home_title'], 255);
    $name = Utils_Unicode::lessenAsEncoding($post['s_name'], 255);
    $entryId = $post['s_no'];
    $homepage = Utils_Unicode::lessenAsEncoding($post['url'], 255);
    $entryurl = $post['s_url'];
    $entrytitle = $post['s_post_title'];
    $parent_id = $post['r1_no'];
    $parent_name = Utils_Unicode::lessenAsEncoding($post['r1_name'], 80);
    $parent_parent = $post['r1_rno'];
    $parent_homepage = Utils_Unicode::lessenAsEncoding($post['r1_homepage'], 80);
    $parent_written = $post['r1_regdate'];
    $parent_comment = $post['r1_body'];
    $parent_url = Utils_Unicode::lessenAsEncoding($post['r1_url'], 255);
    $child_id = $post['r2_no'];
    $child_name = Utils_Unicode::lessenAsEncoding($post['r2_name'], 80);
    $child_parent = $post['r2_rno'];
    $child_homepage = Utils_Unicode::lessenAsEncoding($post['r2_homepage'], 80);
    $child_written = $post['r2_regdate'];
    $child_comment = $post['r2_body'];
    $child_url = Utils_Unicode::lessenAsEncoding($post['r2_url'], 255);

    $pool->reset('CommentsNotifiedSiteInfo');
    $pool->setQualifier('url', 'eq', $homepage);
    $siteid = $pool->getCell('id');

    if (empty($siteid)) {
        $insertId = getCommentsNotifiedSiteInfoMaxId() + 1;
        $pool->reset('CommentsNotifiedSiteInfo');
        $pool->setAttribute('id', $insertId);
        $pool->setAttribute('title', $title, true);
        $pool->setAttribute('name', $name, true);
        $pool->setAttribute('url', $homepage, true);
        $pool->setAttribute('modified', Timestamp::getUNIXtime());
        if ($pool->insert()) {
            $siteid = $insertId;
        } else {
            return 2;
        }
    }
    $pool->reset('CommentsNotified');
    $pool->setQualifier('entry', 'eq', $entryId);
    $pool->setQualifier('siteid', 'eq', $siteid);
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('remoteid', 'eq', $parent_id);
    $parentId = $pool->getCell('id');
    if (empty($parentId)) {
        $insertId = getCommentsNotifiedMaxId() + 1;

        $pool->reset('CommentsNotified');
        $pool->setAttribute('blogid', $blogid);
        $pool->setAttribute('replier', NULL);
        $pool->setAttribute('id', $insertId);
        $pool->setAttribute('entry', $entryId);
        $pool->setAttribute('parent', (empty($parent_parent) ? NULL : $parent_parent));
        $pool->setAttribute('name', $parent_name, true);
        $pool->setAttribute('password', '', true);
        $pool->setAttribute('homepage', $parent_homepage, true);
        $pool->setAttribute('secret', '', true);
        $pool->setAttribute('comment', $parent_comment, true);
        $pool->setAttribute('ip', '', true);
        $pool->setAttribute('written', $parent_written, true);
        $pool->setAttribute('modified', Timestamp::getUNIXtime());
        $pool->setAttribute('siteid', $siteid);
        $pool->setAttribute('isnew', 1);
        $pool->setAttribute('url', $parent_url, true);
        $pool->setAttribute('remoteid', $parent_id);
        $pool->setAttribute('entrytitle', $entrytitle, true);
        $pool->setAttribute('entryurl', $entryurl, true);

        if (!$pool->insert()) {
            return 3;
        }
        $parentId = $insertId;
    }
    $pool->reset('CommentsNotified');
    $pool->setQualifier('siteid', 'eq', $siteid);
    $pool->setQualifier('remoteid', 'eq', $child_id);
    if ($pool->getCount() > 0) {
        return 4;
    }
    $insertId = getCommentsNotifiedMaxId() + 1;

    $pool->reset('CommentsNotified');
    $pool->setAttribute('blogid', $blogid);
    $pool->setAttribute('replier', NULL);
    $pool->setAttribute('id', $insertId);
    $pool->setAttribute('entry', $entryId);
    $pool->setAttribute('parent', $parentId);
    $pool->setAttribute('name', $child_name, true);
    $pool->setAttribute('password', '', true);
    $pool->setAttribute('homepage', $child_homepage, true);
    $pool->setAttribute('secret', '', true);
    $pool->setAttribute('comment', $child_comment, true);
    $pool->setAttribute('ip', '', true);
    $pool->setAttribute('written', $child_written, true);
    $pool->setAttribute('modified', Timestamp::getUNIXtime());
    $pool->setAttribute('siteid', $siteid);
    $pool->setAttribute('isnew', 1);
    $pool->setAttribute('url', $child_url, true);
    $pool->setAttribute('remoteid', $child_id);
    $pool->setAttribute('entrytitle', $entrytitle, true);
    $pool->setAttribute('entryurl', $entryurl, true);
    if (!$pool->insert()) {
        return 5;
    }
    $pool->reset('CommentsNotified');
    $pool->setAttribute('modified', Timestamp::getUNIXtime());
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $parentId);
    if (!$pool->update()) {
        return 6;
    }
    return 0;
}

function getCommentCount($blogid, $entryId = null) {
    $pool = DBModel::getInstance();
    $pool->reset('Entries');
    $pool->setQualifier('blogid', 'eq', $blogid);
    if (is_null($entryId)) {
        $pool->setQualifier('draft', 'eq', 0);
        return $pool->getCell('SUM(comments)');
    }
    $pool->setQualifier('id', 'eq', $entryId);
    $pool->setQualifier('draft', 'eq', 0);
    return $pool->getCell('comments');
}

function getGuestbookCount($blogid) {
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('entry', 'eq', 0);
    return $pool->getCount('id');
}

function getCommentCountPart($commentCount, &$skin) {
    $noneCommentMessage = $skin->noneCommentMessage;
    $singleCommentMessage = $skin->singleCommentMessage;

    if ($commentCount == 0 && !empty($noneCommentMessage)) {
        dress('article_rep_rp_cnt', 0, $noneCommentMessage);
        $commentView = $noneCommentMessage;
    } else {
        if ($commentCount == 1 && !empty($singleCommentMessage)) {
            dress('article_rep_rp_cnt', 1, $singleCommentMessage);
            $commentView = $singleCommentMessage;
        } else {
            $commentPart = $skin->commentCount;
            dress('article_rep_rp_cnt', $commentCount, $commentPart);
            $commentView = $commentPart;
        }
    }

    return array("rp_count", $commentView);
}

function getCommentsMaxId($blogid = null) {
    if (is_null($blogid)) {
        $blogid = getBlogId();
    }
    $pool = DBModel::getInstance();
    $pool->reset('Comments');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $maxId = $pool->getCell('max(id)');
    return empty($maxId) ? 0 : $maxId;
}

function getCommentsNotifiedMaxId($blogid = null) {
    if (is_null($blogid)) {
        $blogid = getBlogId();
    }
    $pool = DBModel::getInstance();
    $pool->reset('CommentsNotified');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $maxId = $pool->getCell('max(id)');
    return empty($maxId) ? 0 : $maxId;
}

function getCommentsNotifiedQueueMaxId($blogid = null) {
    if (is_null($blogid)) {
        $blogid = getBlogId();
    }
    $pool = DBModel::getInstance();
    $pool->reset('CommentsNotifiedQueue');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $maxId = $pool->getCell('max(id)');
    return empty($maxId) ? 0 : $maxId;
}

function getCommentsNotifiedSiteInfoMaxId($blogid = null) {
    if (is_null($blogid)) {
        $blogid = getBlogId();
    }
    $pool = DBModel::getInstance();
    $pool->reset('CommentsNotifiedSiteInfo');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $maxId = $pool->getCell('max(id)');
    return empty($maxId) ? 0 : $maxId;
}

?>
