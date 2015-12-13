<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class Respond {
    static function ResultPage($errorResult) {
        if (is_array($errorResult) && count($errorResult) < 2) {
            $errorResult = array_shift($errorResult);
        }
        if (is_array($errorResult)) {
            $error = $errorResult[0];
            $errorMsg = $errorResult[1];
        } else {
            $error = $errorResult;
            $errorMsg = '';
        }
        if ($error === true) {
            $error = 0;
        } else {
            if ($error === false) {
                $error = 1;
            }
        }
        header('Content-Type: text/xml; charset=utf-8');
        print ("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<response>\n<error>$error</error>\n<message><![CDATA[$errorMsg]]></message></response>");
        exit;
    }

    static function PrintResult($result, $useCDATA = true) {
        header('Content-Type: text/xml; charset=utf-8');
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $xml .= "<response>\n";
        $xml .= Respond::PrintValue($result, $useCDATA);
        $xml .= "</response>\n";
        die($xml);
    }

    static function NotFoundPage($isAjaxCall = false) {
        if ($isAjaxCall) {
            Respond::ResultPage(-1);
            exit;
        }
        header('HTTP/1.1 404 Not Found');
        header("Connection: close");
        exit;
    }

    static function ForbiddenPage() {
        header('HTTP/1.1 403 Forbidden');
        header("Connection: close");
        exit;
    }

    static function MessagePage($message) {
        global $service;
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?php echo TEXTCUBE_NAME; ?></title>
            <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" type="text/css" href="<?php echo $service['path']; ?>/resources/style/owner.css"/>
        </head>
        <body id="body-message-page">
        <div class="message-box">
            <h1><?php echo TEXTCUBE_NAME; ?></h1>

            <div class="message"><?php echo $message; ?></div>
            <div class="button-box">
                <input type="button" class="input-button" value="<?php echo _text('이전'); ?>"
                       onclick="window.history.go(-1)"/>
            </div>
        </div>
        </body>
        </html>
        <?php
        exit;
    }

    static function AlertPage($message) {
        ?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo TEXTCUBE_NAME; ?></title>
	<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
	<script type="text/javascript">
		//<![CDATA[
			alert("<?php echo $message; ?>");
		//]]>
	</script>
</head>
</html>
<?php
        exit;
    }

    static function ErrorPage($message = null, $buttonValue = null, $buttonLink = null, $isAjaxCall = false) {
        global $service;
        if ($isAjaxCall) {
            Respond::ResultPage(-1);
            exit;
        }
        ?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo TEXTCUBE_NAME; ?></title>
	<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path']; ?>/resources/style/owner.css" />
	<script type="text/javascript">
	//<![CDATA[
		var scope = (window.location !== window.parent.location ? window.parent : window);
	//]]>
	</script>
</head>
<body id="body-message-page">
	<div class="message-box">
		<h1><?php echo TEXTCUBE_NAME; ?></h1>

		<div class="message"><?php echo $message; ?></div>
		<div class="button-box">
			<input type="button" class="input-button" value="<?php echo !empty($buttonValue) ? $buttonValue : _text('이전'); ?>" onclick="<?php echo !empty($buttonLink) ? 'scope.location.href=\'' . $buttonLink . '\'' : 'scope.history.go(-1)'; ?>" />
		</div>
	</div>
</body>
</html>
<?php
        exit;
    }

    static function NoticePage($message, $redirection) {
        ?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo TEXTCUBE_NAME; ?></title>
	<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
	<script type="text/javascript">
		//<![CDATA[
			alert("<?php echo $message; ?>");
			window.location.href = "<?php echo $redirection; ?>";
		//]]>
	</script>
</head>
</html>
<?php
        exit;
    }

    static function PrintValue($array, $useCDATA = true) {
        $xml = '';
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_null($value)) {
                    continue;
                } else {
                    if (is_array($value)) {
                        if (is_numeric($key)) {
                            $xml .= Respond::PrintValue($value, $useCDATA) . "\n";
                        } else {
                            $xml .= "<$key>" . Respond::PrintValue($value, $useCDATA) . "</$key>\n";
                        }
                    } else {
                        if ($useCDATA) {
                            $xml .= "<$key><![CDATA[" . Respond::escapeCData($value) . "]]></$key>\n";
                        } else {
                            $xml .= "<$key>" . htmlspecialchars($value) . "</$key>\n";
                        }
                    }
                }
            }
        }
        return $xml;
    }

    static function escapeJSInAttribute($str) {
        return htmlspecialchars(str_replace(array('\\', '\r', '\n', '\''), array('\\\\', '\\r', '\\n', '\\\''), $str));
    }

    static function escapeCData($str) {
        return str_replace(']]>', ']]&gt;', $str);
    }
}

?>
