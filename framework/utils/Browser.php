<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

final class Utils_Browser extends Singleton {
    private $browserName;

    function __construct() {
        $this->browserName = null;
        $this->machineName = null;
    }

    public function getBrowserName() {
        /// Blocking (is in development)
        $ctx = Model_Context::getInstance();
        if ($ctx->getProperty('service.usemobileadmin', true) === false) {
            return 'unknown';
        }
        if (!is_null($this->browserName)) {
            return $this->browserName;
        }
        if(isset($_SERVER['HTTP_USER_AGENT'])) {
            if(strpos($_SERVER['HTTP_USER_AGENT'],'iPhone') ||
                strpos($_SERVER['HTTP_USER_AGENT'],'iPod') ||
                strpos($_SERVER['HTTP_USER_AGENT'],'Mobile Safari') ||
                (strpos($_SERVER['HTTP_USER_AGENT'],'AppleWebkit')!== false &&
                    (strpos($_SERVER['HTTP_USER_AGENT'],'SymbianOS')!== false ||	// Nokia
                        strpos($_SERVER['HTTP_USER_AGENT'],'Pre')!== false))){ 	// Palm pre
                $this->browserName = 'MobileSafari';
            } else if(strpos($_SERVER['HTTP_USER_AGENT'],'Android')) {
                $this->browserName = 'Android';
            } else if(strpos($_SERVER['HTTP_USER_AGENT'],'Firefox') ||
                strpos($_SERVER['HTTP_USER_AGENT'],'iceweasel') ||
                strpos($_SERVER['HTTP_USER_AGENT'],'Minefield')) {
                $this->browserName = 'Firefox';
            } else if(strpos($_SERVER['HTTP_USER_AGENT'],'Safari')) {
                $this->browserName = 'Safari';
            } else if(strpos($_SERVER['HTTP_USER_AGENT'],'Chrome')) {
                if (stripos($_SERVER['HTTP_USER_AGENT'],'mobile')!==false) {
                    $this->browserName = 'MobileChrome';
                } else {
                    $this->browserName = 'Chrome';
                }
            } else if (strpos($_SERVER['HTTP_USER_AGENT'],'Webkit')) {
                $this->browserName = 'Webkit';
            } else if (strpos($_SERVER['HTTP_USER_AGENT'],'IEMobile')) {
                $this->browserName = 'IEMobile';
            } else if (strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident')) {
                $this->browserName = 'IE';
            } else if (strpos($_SERVER['HTTP_USER_AGENT'],'Opera Mini')) {
                $this->browserName = 'OperaMini';
            } else if (strpos($_SERVER['HTTP_USER_AGENT'],'Opera')) {
                $this->browserName = 'Opera';
            } else if (strpos($_SERVER['HTTP_USER_AGENT'],'AvantGo')) {	// Avantgo (palm)
                $this->browserName = 'AvantGo';
            } else if (strpos($_SERVER['HTTP_USER_AGENT'],'DoCoMo')) {	// DoCoMo Phones
                $this->browserName = 'DoCoMo';
            } else if (strpos($_SERVER['HTTP_USER_AGENT'],'Minimo')) {	// Firefox mini
                $this->browserName = 'Minimo';
            } else if (strpos($_SERVER['HTTP_USER_AGENT'],'Maemo')) {	// Firefox mini
                $this->browserName = 'Maemo';
            } else if (strpos($_SERVER['HTTP_USER_AGENT'],'BlackBerry')!== false) {	// Blackberry
                $this->browserName = 'BlackBerry';
            } else if (strpos($_SERVER['HTTP_USER_AGENT'],'POLARIS')!== false) {	// LGE Phone
                $this->browserName = 'Polaris';
            } else if (stripos($_SERVER['HTTP_USER_AGENT'],'mobile')!==false) {
                $this->browserName = 'MobileOther';
            } else {
                $this->browserName = 'unknown';
            }
        }
        return $this->browserName;
    }

    public function getVersion() {
    }

    public function isMobile() {
        return (in_array($this->getBrowserName(), array('MobileSafari', 'Android', 'Maemo', 'OperaMini', 'Minimo', 'DoCoMo', 'AvantGo', 'BlockBerry')));
    }

    public function isSafari() {
        return (in_array($this->getBrowserName(), array('Safari', 'MobileSafari')) ? true : false);
    }

    public function isIE() {
        return ($this->getBrowserName() == 'IE' ? true : false);
    }

    public function isFirefox() {
        return ($this->getBrowserName() == 'Firefox' ? true : false);
    }

    public function isChrome() {
        return (in_array($this->getBrowserName(), array('Chrome','MobileChrome')) ? true : false);
    }

    public function isOpera() {
        return ($this->getBrowserName() == 'Opera' ? true : false);
    }

    function __destruct() {
        // Nothing to do: destruction of this class means the end of execution
    }
}

?>
