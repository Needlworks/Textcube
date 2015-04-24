<?php
/* Test Suite for Textcube
   ----------------------------------
   Version 0.1
   Starts at        : Apr. 19, 2015
   Last modified at : Apr. 20, 2015

   Jeongkyu Shin.
   E-mail : inureyes@gmail.com


 This plugin enables test functions and services for testers.

 For the detail, visit http://github.com/Needlworks/Textcube-TestSuite

 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

### Short changelog

 * 0.1 Base code.

### TODO
 * Add stand-alone autoupdate without preinstalled git binary.
 * Exception handling during git process (usually permission issue)
*/

/* Functions */
function TestSuite_upgrade_repos($target, $mother) {
    $context = Model_Context::getInstance();
    $config = $context->getProperty('plugin.config');

    if (empty($config['repos'])) {
        $config['repos'] = 'unstable';
    }
    require_once 'library/PHPGit/Repository.php';

    $repo = new PHPGit_Repository(ROOT);
    $repo->git('fetch');

    switch ($config['repos']) {
        case 'master':
            $repo->git('checkout master');
            break;
        case 'unstable':
            $repo->git('checkout tags/latest-unstable');
            break;
        case 'stable':
            $repo->git('checkout tags/latest-stable');
            break;
    }
    return $repo->git('pull');
    //return $target;
}

function TestSuite_upgrade_repos_via_user() {
    if (doesHaveOwnership()) {
        $result = TestSuite_upgrade_repos(null, null);
        if($result){
            Respond::ResultPage(0);
        }
    }
    Respond::Resultpage(-1);
}

/* User interfaces */
function TestSuite_manual_upgrade_button($target) {
    /* TODO : add manual upgrade button on dashboard */
    $context = Model_Context::getInstance();
	$blogURL = $context->getProperty('uri.blog');
	$config = $context->getProperty('plugin.config');
	$repos = $config['repos'];
    $view = <<<EOS
    <script type="text/javascript" src="{$blogURL}/plugin/TestSuiteForTextcube.js"></script>
	<div id="TestSuite-manual-upgrade">
		<h4>Update Textcube Source</h4>
        <p>Current branch : {$repos}</p>
        <button class="input-button" value="Update source" onclick="TestSuite_manual_upgrade();return false;">Update source code</button>
    </div>
EOS;
    return $view;
}

/* Validators */
function TestSuite_manual_upgrade_javascript() {
    $context = Model_Context::getInstance();
	$blogURL = $context->getProperty('uri.blog');
	$view = <<< EOS
/// Copyright (C) Jeongkyu Shin. / Needlworks
//<![CDATA[
function TestSuite_manual_upgrade() {
	/// Set default values.
	var request = new HTTPRequest("POST", "{$blogURL}/plugin/TestSuiteManualUpdate/");
	request.onSuccess = function () {
		PM.removeRequest(this);
		PM.showMessage(_t("업데이트가 완료되었습니다"), "center", "bottom");
	}
	request.onError = function() {
		PM.removeRequest(this);
		PM.showErrorMessage(_t("업데이트하지 못했습니다"), "center", "bottom");
	}
	PM.addRequest(request, _t("소스코드를 업데이트하고 있습니다."));
	request.send();
	return true;
}
//]]>
EOS;
	header('Content-Type: text/javascript; charset=UTF-8');
	echo $view;
}


function TestSuite_DataHandler($data) {
    $context = Model_Context::getInstance();
    $config = $context->getProperty('plugin.config');
	return true;
    if (!array_key_exists('repos', $config)) {
        return false;
    } else {
        return true;
    }
}

?>
