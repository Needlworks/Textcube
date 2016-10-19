<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class Paging {
    static function init($url, $prefix = '?page=') {
        return array('url' => rtrim($url, '?'), 'prefix' => $prefix, 'postfix' => '', 'total' => 0, 'pages' => 0, 'page' => 0, 'before' => array(), 'after' => array());
    }

    static function getPagingView(& $paging, & $template, & $itemTemplate, $useCache = false, $mode = 'href') {
        $ctx = Model_Context::getInstance();
        if (($paging === false) || empty($paging['page'])) {
            $paging['url'] = NULL;
            $paging['onclick'] = NULL;
            $paging['prefix'] = NULL;
            $paging['postfix'] = NULL;
            $paging['total'] = NULL;
            $paging['pages'] = 1;
            $paging['page'] = 1;
            $paging['next'] = NULL;
        }
        $url = str_replace('/%3F/', '/?/', URL::encode($paging['url'], $ctx->getProperty('service.useEncodedURL')));
        $prefix = $paging['prefix'];
        $postfix = isset($paging['postfix']) ? $paging['postfix'] : '';
        ob_start();
        if (isset($paging['first'])) {
            $itemView = "$itemTemplate <span class=\"interword\">...</span> ";
            Utils_Misc::dress('paging_rep_link_num', '<span>1</span>', $itemView, $useCache);
            Utils_Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='") . "$url$prefix{$paging['first']}$postfix'", $itemView, $useCache);
            print ($itemView);
        } else {
            if ($paging['page'] > 5) {
                $itemView = "$itemTemplate <span class=\"interword\">...</span> ";
                Utils_Misc::dress('paging_rep_link_num', '<span>1</span>', $itemView, $useCache);
                Utils_Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='") . "$url{$prefix}1$postfix'", $itemView, $useCache);
                print ($itemView);
            }
        }
        if (isset($paging['before'])) {
            $page = $paging['page'] - count($paging['before']);
        } else {
            $page = $paging['page'] < 5 ? 1 : $paging['page'] - 4;
        }
        if (isset($paging['before'])) {
            foreach ($paging['before'] as $value) {
                $itemView = $itemTemplate;
                Utils_Misc::dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useCache);
                Utils_Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='") . "$url$prefix$value$postfix'", $itemView, $useCache);
                print ($itemView);
                $page++;
            }
        } else {
            for ($i = 0; ($i < 4) && ($page < $paging['page']); $i++) {
                $itemView = $itemTemplate;
                Utils_Misc::dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useCache);
                Utils_Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='") . "$url$prefix$page$postfix'", $itemView, $useCache);
                print ($itemView);
                $page++;
            }
        }
        if (($page == $paging['page']) && ($page <= $paging['pages'])) {
            $itemView = $itemTemplate;
            Utils_Misc::dress('paging_rep_link_num', "<span class=\"selected\" >$page</span>", $itemView, $useCache);
            Utils_Misc::dress('paging_rep_link', '', $itemView, $useCache);
            print ($itemView);
            $page++;
        }
        if (isset($paging['before'])) {
            foreach ($paging['after'] as $value) {
                $itemView = $itemTemplate;
                Utils_Misc::dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useCache);
                Utils_Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='") . "$url$prefix$value$postfix'", $itemView, $useCache);
                print ($itemView);
                $page++;
            }
        } else {
            for ($i = 0; ($i < 4) && ($page <= $paging['pages']); $i++) {
                $itemView = $itemTemplate;
                Utils_Misc::dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useCache);
                Utils_Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='") . "$url$prefix$page$postfix'", $itemView, $useCache);
                print ($itemView);
                $page++;
            }
        }
        if (isset($paging['last'])) {
            $itemView = " <span class=\"interword\">...</span> $itemTemplate";
            Utils_Misc::dress('paging_rep_link_num', "<span>{$paging['pages']}</span>", $itemView, $useCache);
            Utils_Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='") . "$url$prefix{$paging['last']}$postfix'", $itemView, $useCache);
            print ($itemView);
        } else {
            if (($paging['pages'] - $paging['page']) > 4) {
                $itemView = " <span class=\"interword\">...</span> $itemTemplate";
                Utils_Misc::dress('paging_rep_link_num', "<span>{$paging['pages']}</span>", $itemView, $useCache);
                Utils_Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='") . "$url$prefix{$paging['pages']}$postfix'", $itemView, $useCache);
                print ($itemView);
            }
        }
        $itemsView = ob_get_contents();
        ob_end_clean();
        $view = $template;
        Utils_Misc::dress('prev_page', isset($paging['prev']) ? ($mode == 'href' ? "href=\"" : "href=\"#\" onclick=\"") . "$url$prefix{$paging['prev']}$postfix\" rel=\"prev\"" : '', $view, $useCache);
        Utils_Misc::dress('prev_page_title', isset($paging['prev_title']) ? $paging['prev_title'] : '', $view, $useCache);
        Utils_Misc::dress('paging_rep', $itemsView, $view, $useCache);
        Utils_Misc::dress('next_page', isset($paging['next']) ? ($mode == 'href' ? "href=\"" : "href=\"#\" onclick=\"") . "$url$prefix{$paging['next']}$postfix\" rel=\"next\"" : '', $view, $useCache);
        Utils_Misc::dress('next_page_title', isset($paging['next_title']) ? $paging['next'] : '', $view, $useCache);
        Utils_Misc::dress('no_more_prev', isset($paging['prev']) ? '' : 'no-more-prev', $view, $useCache);
        Utils_Misc::dress('no_more_next', isset($paging['next']) ? '' : 'no-more-next', $view, $useCache);

        return $view;
    }

    static function fetch($sqlmodel, $page, $count, $url = null, $prefix = '?page=', $countItem = null, $onclick = null) {
        $context = Model_Context::getInstance();
        if ($url === null) {
            $url = $context->getProperty('uri.folder');
        }
        $paging = array('url' => $url, 'prefix' => $prefix, 'postfix' => '', 'onclick' => $onclick);
        if (empty($sqlmodel)) {
            return array(array(), $paging);
        }

        if (gettype($sqlmodel) == "object" && get_class($sqlmodel) == "DBModel") { // It's DBModel.
            $isDBModel = true;
        } else { // It's SQL
            $isDBModel = false;
        }

        if ($isDBModel) {
            $order = $sqlmodel->getOrder();
            $sqlmodel->unsetOrder();
            $paging['total'] = $sqlmodel->getSize(); // get record size
            $sqlmodel->setOrder($order['attribute'], $order['order']);
        } else { // It's SQL
            if (preg_match('/\s(FROM.*)(ORDER BY.*)$/si', $sqlmodel, $matches)) {
                $from = $matches[1];
                $paging['total'] = POD::queryCell("SELECT COUNT(*) $from");
            } else {
                return array(array(), $paging);
            }
        }
        if ($paging['total'] === null) {
            return array(array(), $paging);
        }
        if (empty($count)) {
            $count = 1;
        }
        $paging['pages'] = intval(ceil($paging['total'] / $count));
        $paging['page'] = is_numeric($page) ? $page : 1;
        if ($paging['page'] > $paging['pages']) {
            $paging['page'] = $paging['pages'];
            if ($paging['pages'] > 0) {
                $paging['prev'] = $paging['pages'] - 1;
            }
        }
        if ($paging['page'] > 1) {
            $paging['prev'] = $paging['page'] - 1;
        }
        if ($paging['page'] < $paging['pages']) {
            $paging['next'] = $paging['page'] + 1;
        }
        $offset = ($paging['page'] - 1) * $count;
        if ($offset < 0) {
            $offset = 0;
        }
        if ($countItem !== null) {
            $count = $countItem;
        }

        if ($isDBModel) {
            $sqlmodel->setLimit($count, $offset);
            $result = $sqlmodel->getAll(); // Prevent Object poisoning by lazy evaluation of DBModel recycling.
            return array($result, $paging);
        } else {
            return array(POD::queryAll("$sqlmodel LIMIT $count OFFSET $offset"), $paging);
        }
    }

    /** Legacy methods **/

    static function initPaging($url, $prefix = '?page=') {
        return self::init($url, $prefix);
    }

    static function fetchWithPaging($sql, $page, $count, $url = null, $prefix = '?page=', $countItem = null, $onclick = null) {
        return self::fetch($sql, $page, $count, $url, $prefix, $countItem, $onclick);
    }
}

?>
