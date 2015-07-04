<?php

function Recaptcha_AddInputValidatorRule($target, $mother) {
	$signed_in = (doesHaveOwnership() || doesHaveMembership());
	if ($mother == 'interface/blog/comment/add/' || $mother == 'interface/blog/comment/comment/') {
		$target['POST']['g-recaptcha-response'] = array('string', 'default' => '', 'mandatory' => !$signed_in);
	}
	return $target;
}

function Recaptcha_Header($target) {
    $context = Model_Context::getInstance();
    $config = $context->getProperty('plugin.config');
	if (!is_null($config) && isset($config['siteKey'])) {
		$target .= <<<EOS
<script type="text/javascript">

var recaptcha_widgets = {};
function recaptcha_addControl(f, entryId) {
	var $ = jQuery;
	var blockId = 'comment_recaptcha_' + entryId;
	var widgetId;
	if ($('#' + blockId).length > 0) {
		if (recaptcha_widgets[entryId] != undefined)
			grecaptcha.reset(recaptcha_widgets[entryId]);
		return;
	}
	$(f).find('textarea').after('<div style="margin: 5pt 0 5pt 0" id="' + blockId + '"></div>');
	widgetId = grecaptcha.render(blockId, {
		'sitekey': '{$config['siteKey']}'
	});
	recaptcha_widgets[entryId] = widgetId;
}

function recaptcha_checkForms() {
	var $ = jQuery;
	var _entryIds = entryIds;
	if ($('#tt-body-guestbook').length > 0) {
		_entryIds = [0];
	}
	$.each(_entryIds, function(idx, entryId) {
		var v = $('#entry' + entryId + 'Comment:visible');
		var f = $('form[id=entry' + entryId + 'WriteComment]');
		if (f.length > 0 && v.length > 0)
			recaptcha_addControl(f, entryId);
	});
}

var recaptcha_waitTrials;
var recaptcha_waitTimer = null;
function recaptcha_waitForElement(selector, cb) {
	var $ = jQuery;
	recaptcha_waitTrials = 0;
	var finder = function() {
		var o = $(selector);
		if (o.length > 0) {
			window.clearInterval(recaptcha_waitTimer);
			recaptcha_waitTimer = null;
			cb(o);
		} else {
			recaptcha_waitTrials ++;
			if (recaptcha_waitTrials > 25) {
				alert('Cannot find required elements to insert the reCAPTCHA control.');
				window.clearInterval(recaptcha_waitTimer);
				recaptcha_waitTimer = null;
			}
		}
	};
	recaptcha_waitTimer = window.setInterval(finder, 200);
}
</script>
<script src="https://www.google.com/recaptcha/api.js?render=explicit&amp;onload=recaptcha_checkForms" async defer></script>
EOS;
	}
	return $target;
}

function Recaptcha_CCHeader($target) {
    $context = Model_Context::getInstance();
    $config = $context->getProperty('plugin.config');

	if (!is_null($config) && isset($config['siteKey'])) {
		$target .= <<<EOS
<script type="text/javascript">
var recaptcha_waitTimer = null;
function recaptcha_init() {
	var $ = jQuery;
	if (!doesHaveOwnership) {
		$('form').find('textarea').after('<div style="margin: 5pt 0 5pt 0" id="comment_recaptcha"></div>');
		grecaptcha.render('comment_recaptcha', {
			'sitekey': '{$config['siteKey']}'
		});
		var scope = (window.location !== window.parent.location ? window.parent : window);
		if(scope == window.parent) {
			recaptcha_waitTimer = scope.setInterval(function() {
				var v = $('#comment_recaptcha');
				if (v.length > 0) {
					resizeDialog(0,parseInt(v.outerHeight(true)),true);
					scope.clearInterval(recaptcha_waitTimer);
				}
			}, 200);
		} else {
			recaptcha_waitTimer = window.setInterval(function() {
				var v = $('#comment_recaptcha');
				if (v.length > 0) {
					window.resizeBy(0, v.outerHeight(true));
					window.clearInterval(recaptcha_waitTimer);
				}
			}, 200);
		}
	}
}
</script>
<script src="https://www.google.com/recaptcha/api.js?render=explicit&amp;onload=recaptcha_init" async defer></script>
EOS;
	}
	return $target;
}

function Recaptcha_Footer($target) {
	$context = Model_Context::getInstance();
	$config = $context->getProperty('plugin.config');
	if (!is_null($config) && isset($config['siteKey'])) {
		$target .= <<<EOS
<script type="text/javascript">
(function($) {
$(document).ready(function() {
	if (!doesHaveOwnership) {
		$('a[id^=commentCount]').click(function(e) {
			var entryId = $(e.target).attr('id').match(/(\d+)/)[1];
			$('#entry' + entryId + 'Comment').empty(); // prevent interference with previously shown controls.
			if ($('#entry' + entryId + 'Comment:visible').length > 0) {
				/* The comment view is opened. */
				if (recaptcha_waitTimer != null) {
					window.clearInterval(recaptcha_waitTimer);
					recaptcha_waitTimer = null;
				}
				recaptcha_waitForElement('form[id=entry' + entryId + 'WriteComment]', function(f) {
					recaptcha_addControl(f, entryId);
				});
			} else {
				/* The comment view is closed. */
				if (recaptcha_waitTimer != null) {
					window.clearInterval(recaptcha_waitTimer);
					recaptcha_waitTimer = null;
				}
				if (recaptcha_widgets[entryId] != undefined)
					delete recaptcha_widgets[entryId];
			}
		});
	}
});
})(jQuery);
</script>
EOS;
	}
	return $target;
}

function Recaptcha_ConfigHandler($data) {
	return true;
}

function Recaptcha_AddingCommentHandler($target, $mother)
{
    $context = Model_Context::getInstance();
    $config = $context->getProperty('plugin.config');
	if (doesHaveOwnership() || doesHaveMembership()) return true;  /* Skip validation if signed-in. */
	if (!is_null($config) && isset($config['secretKey'])) {
		$recaptcha_response = $_POST["g-recaptcha-response"];
		$reqURL = "https://www.google.com/recaptcha/api/siteverify?secret={$config['secretKey']}&response={$recaptcha_response}";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $reqURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($ch);
		curl_close($ch);
		if ($output === false) {
			Respond::PrintResult(array('error' => 2, 'description' => 'Cannot connect to the Google reCAPTCHA server.'));
			return false;
		} else {
			$resp = json_decode($output, true);
			if ($resp['success'] === true) {
				/* Yay! The user is human. */
				return true;
			} else {
				$err = implode(' ', $resp['error-codes']);
				if (strpos($err, 'missing-input-secret') !== false) {
					Respond::PrintResult(array('error' => 2, 'description' => 'Missing reCAPTCHA secret key!'));
				} elseif (strpos($err, 'missing-input-response') !== false) {
					Respond::PrintResult(array('error' => 2, 'description' => 'Missing reCAPTCHA response!'));
				} elseif (strpos($err, 'invalid-input-secret') !== false) {
					Respond::PrintResult(array('error' => 2, 'description' => 'Invalid reCAPTCHA secret key.'));
				} elseif (strpos($err, 'invalid-input-response') !== false) {
					Respond::PrintResult(array('error' => 2, 'description' => 'Invalid reCAPTCHA response.'));
				}
			}
		}
		/* It seems to be a robot! Silently fail. */
		return false;
	}
	/* If the plugin is not configured yet, bypass validation. */
	return true;
}

/* vim: set noet ts=4 sts=4 sw=4: */
?>
