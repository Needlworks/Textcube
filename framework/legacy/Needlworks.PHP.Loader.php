<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/** Binders */
function requireModel($name) {
    $context = Model_Context::getInstance();
    if (!in_array($name, $context->getProperty('import.model',array()))) {
        include_once(ROOT . "/library/model/$name.php");
        $context->setPropertyItem('import.model', $name);
    }
}

function requireComponent($name) { return true; } // Legacy code for plugins. TODO: move to legacy support code.

function requireView($name) {
    $context = Model_Context::getInstance();
    if (!in_array($name, $context->getProperty('import.view',array()))) {
        include_once(ROOT . "/library/view/$name.php");
        $context->setPropertyItem('import.view', $name);
    }
}

function requireLibrary($name) {
    $context = Model_Context::getInstance();
    if (!in_array($name, $context->getProperty('import.library',array()))) {
        include_once(ROOT . "/library/$name.php");
        $context->setPropertyItem('import.library', $name);
    }
}

function requireModule() {
    $context = Model_Context::getInstance();
    $args = func_get_args();
    if (empty($args)) {
        return false;
    }
    foreach ($args as $libPath) {
        $paths = explode(".", $libPath);
        if (end($paths) == "*") {
            array_pop($paths);
            foreach (new DirectoryIterator(ROOT . '/library/' . implode("/", $paths)) as $fileInfo) {
                if ($fileInfo->isFile()) {
                    require_once($fileInfo->getPathname());
                }
            }
        } else {
            if (!in_array($name, $context->getProperty('import.module',array()))) {
                require_once ROOT . '/library/' . $str_replace(".", "/", $libPath) . ".php";
                $context->setPropertyItem('import.module', $libPath);
            }
        }
    }
    return true;
}

/** Autoload components */
class Autoload_Legacy {
    private function initialize() {
        $this->db = array(
            'POD', 'DBQuery');
        $this->data = array(
            'Attachment', 'BlogSetting', 'BlogStatistics', 'Category', 'Comment', 'CommentNotified',
            'CommentNotifiedSiteInfo', 'DailyStatistics', 'DataMaintenance', 'Feed',
            'Filter', 'GuestComment', 'Keyword', 'Link', 'LinkCategories', 'Notice', 'PluginSetting', 'Post',
            'RefererLog', 'RefererStatistics', 'ServiceSetting', 'SkinSetting', 'SubscriptionLog',
            'SubscriptionStatistics', 'Tag', 'Trackback', 'TrackbackLog', 'UserInfo', 'UserSetting'
        );
        $this->model = array(
            'Message', 'Paging', 'PluginCustomConfig', 'Statistics', 'User'
        );
        $this->base = array(
            'HTTPRequest', 'XMLRPC', 'XMLRPCFault',
            'XMLCustomType', 'XMLTree', 'Pop3', 'CommunicationFeed');
        $this->function = array(
            'Image', 'Setting', 'Respond', 'Misc');
        $this->openid = array(
            'OpenID', 'OpenIDSession', 'OpenIDConsumer');
        $this->control = array(
            'Session', 'RSS');
    }

    public function load($name) {
        global $service, $database;
        $name = ucfirst($name);
        if (!isset($this->data)) {
            $this->initialize();
        }
        if (in_array($name, $this->data)) {
            require_once(ROOT . "/framework/legacy/Textcube.Data." . $name . ".php");
        } else {
            if (in_array($name, $this->model)) {
                require_once(ROOT . "/framework/legacy/Textcube.Model." . $name . ".php");
            } else {
                if (in_array($name, $this->base)) {
                    if (in_array($name, array('XMLRPC', 'XMLRPCFault', 'XMLCustomType'))) {
                        require_once(ROOT . "/framework/legacy/Needlworks.PHP.XMLRPC.php");
                    } else {
                        require_once(ROOT . "/framework/legacy/Needlworks.PHP." . $name . ".php");
                    }
                } else {
                    if (in_array($name, $this->function)) {
                        require_once(ROOT . "/framework/legacy/Textcube.Function." . $name . ".php");
                    } else {
                        if (in_array($name, $this->openid)) {
                            require_once(ROOT . "/framework/legacy/Textcube.Control.Openid.php");
                        } else {
                            if (in_array($name, $this->control)) {
                                if ($name == 'Session' && isset($service['memcached']) && $service['memcached'] == true) {
                                    require_once(ROOT . "/framework/legacy/Textcube.Control." . $name . ".Memcached.php");
                                } else {
                                    require_once(ROOT . "/framework/legacy/Textcube.Control." . $name . ".php");
                                }
                            } else {
                                if (in_array($name, array('Syndication'))) {
                                    require_once(ROOT . "/framework/legacy/Eolin.API.Syndication.php");
                                } else {
//			if(defined('TCDEBUG')) print "TC: Unregisterred auto load class from legacy repository : $name<br/>\n";
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

$autoloadInstance_Legacy = new Autoload_Legacy();
spl_autoload_register(array($autoloadInstance_Legacy, 'load'));
?>
