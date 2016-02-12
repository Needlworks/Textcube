<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/** Legacy Binders TODO: move to legacy support code. */
function requireModel($name) {
    return importlib('model.'.$name);
}

function requireComponent($name) { return true; } // Legacy code for plugins.

function requireView($name) {
    return importlib('view.'.$name);
}

function requireLibrary($name) {
    return importlib($name);
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
            'Paging', 'PluginCustomConfig', 'Statistics', 'User'
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
        } elseif (in_array($name, $this->model)) {
            require_once(ROOT . "/framework/legacy/Textcube.Model." . $name . ".php");
        } elseif (in_array($name, $this->base)) {
            if (in_array($name, array('XMLRPC', 'XMLRPCFault', 'XMLCustomType'))) {
                require_once(ROOT . "/framework/legacy/Needlworks.PHP.XMLRPC.php");
            } else {
                require_once(ROOT . "/framework/legacy/Needlworks.PHP." . $name . ".php");
            }
        } elseif (in_array($name, $this->function)) {
            require_once(ROOT . "/framework/legacy/Textcube.Function." . $name . ".php");
        } elseif (in_array($name, $this->openid)) {
            require_once(ROOT . "/framework/legacy/Textcube.Control.Openid.php");
        } elseif (in_array($name, $this->control)) {
            if ($name == 'Session' && isset($service['memcached']) && $service['memcached'] == true) {
                require_once(ROOT . "/framework/legacy/Textcube.Control." . $name . ".Memcached.php");
            } else {
                require_once(ROOT . "/framework/legacy/Textcube.Control." . $name . ".php");
            }
        } else {
//			if(defined('TCDEBUG')) print "TC: Unregisterred auto load class from legacy repository : $name<br/>\n";
        }
    }
}

$autoloadInstance_Legacy = new Autoload_Legacy();
spl_autoload_register(array($autoloadInstance_Legacy, 'load'));
?>
