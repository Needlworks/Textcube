<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class BlogSetting {
    function __construct() {
        $this->reset();
    }

    function reset() {
        $this->error =
        $this->name =
        $this->secondaryDomain =
        $this->defaultDomain =
        $this->title =
        $this->description =
        $this->banner =
        $this->useSloganOnPost =
        $this->useSloganOnCategory =
        $this->useSloganOnTag =
        $this->entriesOnPage =
        $this->entriesOnList =
        $this->postsOnFeed =
        $this->publishWholeOnFeed =
        $this->acceptGuestComment =
        $this->acceptcommentOnGuestComment =
        $this->language =
        $this->timezone =
            null;
    }

    function load($fields = '*') {
        global $database;
        $blogid = getBlogId();
        $this->reset();
        $query = DBModel::getInstance();
        $query->reset('BlogSettings');
        if ($query->doesExist()) {
            $query->setQualifier('blogid', 'equals', $blogid);
            $blogSettings = $query->getAll('name,value');
            if (isset($blogSettings)) {
                foreach ($blogSettings as $blogSetting) {
                    $name = $blogSetting['name'];
                    $value = $blogSetting['value'];
                    switch ($name) {
                        case 'logo':
                            $name = 'banner';
                            break;
                        case 'entriesOnPage':
                            $name = 'postsOnPage';
                            break;
                        case 'entriesOnList':
                            $name = 'postsOnList';
                            break;
                        case 'entriesOnRSS':
                            $name = 'postsOnFeed';
                            break;
                        case 'publishWholeOnRSS':
                            $name = 'publishWholeOnFeed';
                            break;
                        case 'allowWriteOnGuestbook':
                            $name = 'acceptGuestComment';
                            break;
                        case 'allowWriteDblCommentOnGuestbook':
                            $name = 'acceptcommentOnGuestComment';
                            break;
                        case 'defaultDomain':
                        case 'useSloganOnPost':
                        case 'useSloganOnCategory':
                        case 'useSloganOnTag':
                        case 'acceptGuestComment':
                        case 'acceptcommentOnGuestComment':
                            $value = $value ? true : false;
                            break;
                    }
                    $this->$name = $value;
                }
            }
            return true;
        }
        return false;
    }

    function save() {
        global $database;
        importlib('model.common.setting');

        if (isset($this->name)) {
            $this->name = trim($this->name);
            if (!BlogSetting::validateName($this->name)) {
                return $this->_error('name');
            }
            Setting::setBlogSettingGlobal('name', $this->name);
        }
        if (isset($this->secondaryDomain)) {
            $this->secondaryDomain = trim($this->secondaryDomain);
            if (!Validator::domain($this->secondaryDomain)) {
                return $this->_error('secondaryDomain');
            }
            Setting::setBlogSettingGlobal('secondaryDomain', $this->secondaryDomain);
        }
        if (isset($this->defaultDomain)) {
            Setting::setBlogSettingGlobal('defaultDomain', Validator::getBit($this->defaultDomain));
        }
        if (isset($this->title)) {
            $this->title = trim($this->title);
            Setting::setBlogSettingGlobal('title', $this->title);
        }
        if (isset($this->description)) {
            $this->description = trim($this->description);
            Setting::setBlogSettingGlobal('description', $this->description);
        }
        if (isset($this->banner)) {
            if ((strlen($this->banner) != 0) && !Validator::filename($this->banner)) {
                return $this->_error('banner');
            }
            Setting::setBlogSettingGlobal('logo', $this->banner);
        }
        if (isset($this->useSloganOnPost)) {
            Setting::setBlogSettingGlobal('useSloganOnPost', Validator::getBit($this->useSloganOnPost));
        }
        if (isset($this->useSloganOnCategory)) {
            Setting::setBlogSettingGlobal('useSloganOnCategory', Validator::getBit($this->useSloganOnCategory));
        }
        if (isset($this->useSloganOnTag)) {
            Setting::setBlogSettingGlobal('useSloganOnTag', Validator::getBit($this->useSloganOnTag));
        }
        if (isset($this->postsOnPage)) {
            if (!Validator::number($this->postsOnPage, 1)) {
                return $this->_error('postsOnPage');
            }
            Setting::setBlogSettingGlobal('entriesOnPage', $this->postsOnPage);
        }
        if (isset($this->postsOnList)) {
            if (!Validator::number($this->postsOnList, 1)) {
                return $this->_error('postsOnList');
            }
            Setting::setBlogSettingGlobal('entriesOnList', $this->postsOnList);
        }
        if (isset($this->postsOnFeed)) {
            if (!Validator::number($this->postsOnFeed, 1)) {
                return $this->_error('postsOnFeed');
            }
            Setting::setBlogSettingGlobal('entriesOnRSS', $this->postsOnFeed);
        }
        if (isset($this->publishWholeOnFeed)) {
            Setting::setBlogSettingGlobal('publishWholeOnRSS', Validator::getBit($this->publishWholeOnFeed));
        }
        if (isset($this->acceptGuestComment)) {
            Setting::setBlogSettingGlobal('allowWriteOnGuestbook', Validator::getBit($this->acceptGuestComment));
        }
        if (isset($this->acceptcommentOnGuestComment)) {
            Setting::setBlogSettingGlobal('allowWriteDblCommentOnGuestbook', Validator::getBit($this->acceptcommentOnGuestComment));
        }
        if (isset($this->language)) {
            if (!Validator::language($this->language)) {
                return $this->_error('language');
            }
            Setting::setBlogSettingGlobal('language', $this->language);
        }
        if (isset($this->timezone)) {
            if (empty($this->timezone)) {
                return $this->_error('timezone');
            }
            Setting::setBlogSettingGlobal('timezone', $this->timezone);
        }
        return true;
    }

    function escape($escape = true) {
        $this->name = Validator::escapeXML(@$this->name, $escape);
        $this->secondaryDomain = Validator::escapeXML(@$this->secondaryDomain, $escape);
        $this->title = Validator::escapeXML(@$this->title, $escape);
        $this->description = Validator::escapeXML(@$this->description, $escape);
    }

    /*@static@*/
    function setTimezone($timezone) {
        if (Timezone::set($timezone)) {
            $setting = new BlogSetting();
            $setting->timezone = $timezone;
            return $setting->save();
        }
    }

    /*@static@*/
    function validateName($name) {
        return preg_match('/^[a-zA-Z0-9]+$/', $name);
    }

    function _error($error) {
        $this->error = $error;
        return false;
    }
}

?>
