<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$context = Model_Context::getInstance();
$confirmString = '';

if (empty($comment['name'])) {
    if (isset($_SESSION['openid']['nickname'])) {
        $comment['name'] = $_SESSION['openid']['nickname'];
    } else {
        if (isset($_COOKIE[$context->getProperty('service.cookie_prefix') . 'guestName'])) {
            $comment['name'] = $_COOKIE[$context->getProperty('service.cookie_prefix') . 'guestName'];
        }
    }
}
if ((empty($comment['homepage']) || $comment['homepage'] == 'http://')) {
    if (isset($_SESSION['openid']['homepage'])) {
        $comment['homepage'] = $_SESSION['openid']['homepage'];
    } else {
        if (isset($_COOKIE[$context->getProperty('service.cookie_prefix') . 'guestHomepage']) && $_COOKIE[$context->getProperty('service.cookie_prefix') . 'guestHomepage'] != 'http://') {
            $comment['homepage'] = $_COOKIE[$context->getProperty('service.cookie_prefix') . 'guestHomepage'];
        }
    }
}

$context = Model_Context::getInstance();
$pageHeadTitle = $pageTitle;
if (Acl::getIdentity('openid')) {
    $pageHeadTitle = $pageTitle;
    $pageTitle = "$pageTitle ( <img src=\"" . $context->getProperty('service.path') . "/resources/image/icon_openid.gif\" style=\"position:static;\" height=\"16\" width=\"16\"> " . OpenID::getDisplayName(Acl::getIdentity('openid')) . ")";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pageHeadTitle; ?></title>
    <meta charset="UTF-8" name="viewport" content="initial-scale=1.0, user-scalable=no">
    <link rel="stylesheet" type="text/css" media="screen"
          href="<?php echo $context->getProperty('service.path') . $context->getProperty('panel.skin'); ?>/popup-comment.css"/>
    <script type="text/javascript">
        var servicePath = "<?php echo $context->getProperty('service.path');?>";
        var serviceURL = "<?php echo $context->getProperty('uri.service');?>";
        var blogURL = "<?php echo $context->getProperty('uri.blog');?>";
        var adminSkin = "<?php echo $context->getProperty('panel.skin');?>";
        var doesHaveOwnership = <?php echo doesHaveOwnership() ? 'true' : 'false';?>;
    </script>
    <script type="text/javascript"
            src="<?php echo(doesHaveOwnership() ? $context->getProperty('service.path') . '/resources' : $context->getProperty('service.resourcepath')); ?>/script/common3.min.js"></script>
    <script type="text/javascript">
        function submitComment() {
            var oForm = document.commentToComment;
            var oButton = document.getElementById('commentSubmit');
            oButton.value = '<?php echo escapeJSInCData(_text('저장중'));?>';
            var tempOnClick = oButton.onclick;
            oButton.onclick = 'return false;';
            trimAll(oForm);
            <?php
            if (!doesHaveMembership()) {
            ?>
            if (!checkValue(oForm.name, '<?php echo escapeJSInCData(_text('이름을 입력해 주십시오.'));?>')) {
                oButton.value = '<?php echo _text('완료');?>';
                oButton.onclick = tempOnClick;
                return false;
            }
            <?php
            }
            ?>
            if (!checkValue(oForm.comment, '<?php echo escapeJSInCData(_text('댓글을 입력해 주십시오.'));?>')) {
                oButton.value = '<?php echo _text('완료');?>';
                oButton.onclick = tempOnClick;
                return false;
            }
            oForm.submit();
        }
        function confirmOverwrite() {
            return confirm("<?php echo escapeJSInCData(_text('관리자가 방문객의 댓글을 수정하시면 작성자 이름을 관리자 아이디로 덮어 쓰게 됩니다.')).escapeJSInCData('계속 하시겠습니까?');?>");
        }
    </script>
    <?php echo fireEvent('REPLY_head_end', '', $comment); ?>
</head>
<?php
if (doesHaveOwnership()) {
    $writerClass = ' class="admin-comment"';
} else {
    $writerClass = '';
}

if (!doesHaveMembership()) {
?>
<body<?php echo $writerClass; ?> onload="document.commentToComment.name.focus()">
<?php
} else {
?>
<body<?php echo $writerClass; ?> onload="document.commentToComment.comment.focus()">
<?php
}
?>
<form name="commentToComment" method="post"
      action="<?php echo($_POST['mode'] == 'edit' ? $context->getProperty('uri.blog') . '/comment/delete/' . $context->getProperty('suri.id') : $context->getProperty('suri.url'); ?>">
    <input type="hidden" name="mode" value="commit"/>
    <input type="hidden" name="oldPassword" value="<?php echo isset($_POST['password']) ? $_POST['password'] : ''; ?>"/>
    <a onclick="closeDialog();" href="#" class="close-button"><span>X</span></a>

    <div id="comment-reply-box">
        <img
            src="<?php echo $context->getProperty('service.path') . $context->getProperty('panel.skin'); ?>/image/img_comment_popup_logo.gif"
            alt="<?php echo _text('텍스트큐브 로고'); ?>"/>

        <div class="title"><span class="text" id="title"><?php echo $pageTitle; ?></span></div>
        <?php
        if ($viewMode == 'comment') {
            $parent = getComment(getBlogId(), $context->getProperty('suri.id'), null, false);
            if (($parent['secret'] == 1) && !doesHaveOwnership()) {
                $parent['name'] = $parent['written'] = $parent['comment'] = _t('[비밀댓글]');
            }
            ?>
            <div id="original-reply-box">
                <ul class="main-comment">
                    <li><span class="name"><?php echo htmlspecialchars($parent['name']); ?></span></li>
                    <li><span class="date"><?php echo Timestamp::format5($parent['written']); ?></span></li>
                    <li><p class="contents"><?php echo htmlspecialchars($parent['comment']); ?></p></li>
                </ul>
                <?php
                $children = getCommentComments($parent['id']);
                if (!empty($children)) {
                    ?>
                    <ul class="child-comments">
                        <?php
                        foreach ($children as $child) {
                            if (($child['secret'] == 1) && !doesHaveOwnership()) {
                                $child['name'] = $child['written'] = $child['comment'] = _t('[비밀댓글]');
                            }
                            ?>
                            <li>
                                <ul>
                                    <li><span class="name"><?php echo htmlspecialchars($child['name']); ?></span></li>
                                    <li><span class="date"><?php echo Timestamp::format5($child['written']); ?></span>
                                    </li>
                                    <li><p class="contents"><?php echo htmlspecialchars($child['comment']); ?></p></li>
                                </ul>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                <?php
                }
                ?>
            </div>
        <?php
        }
        ?>
        <div id="command-box">
            <?php
            if (!doesHaveOwnership()) {
                if (!doesHaveMembership()) {
                    ?>
                    <dl class="name-line">
                        <dt><label for="name"><?php echo _text('이름'); ?></label></dt>
                        <dd><input type="text" id="name" class="input-text" name="name"
                                   value="<?php echo htmlspecialchars($comment['name']); ?>"/></dd>
                    </dl>
                    <?php
                    if (!Acl::getIdentity('openid')) { ?>
                        <dl class="password-line">
                            <dt><label for="password"><?php echo _text('비밀번호'); ?></label></dt>
                            <dd><input type="password" class="input-text" id="password" name="password"
                                       value="<?php echo isset($_POST['password']) ? $_POST['password'] : ''; ?>"/></dd>
                        </dl>
                    <?php } ?>
                    <dl class="homepage-line">
                        <dt><label for="homepage"><?php echo _text('홈페이지'); ?></label></dt>
                        <dd><input type="text" class="input-text" id="homepage" name="homepage"
                                   value="<?php echo(empty($comment['homepage']) ? 'http://' : htmlspecialchars($comment['homepage'])); ?>"/>
                        </dd>
                    </dl>
                <?php
                }
                ?>
                <dl class="secret-line">
                    <dd>
                        <input type="checkbox" class="checkbox" id="secret"
                               name="secret"<?php echo($comment['secret'] ? ' checked="checked"' : false); ?> />
                        <label for="secret"><?php echo _text('비밀글로 등록'); ?></label>
                    </dd>
                </dl>
            <?php
            }

            if (doesHaveOwnership() && array_key_exists('replier', $comment) && (is_null($comment['replier']) || ($comment['replier'] != getUserId()))) {
                $confirmString = "if( confirmOverwrite() )";
            }
            ?>
            <dl class="content-line">
                <dt><label for="comment"><?php echo _text('내용'); ?></label></dt>
                <dd><textarea id="comment" name="comment" cols="45" rows="9"
                              style="height: <?php echo (!doesHaveOwnership() && !doesHaveOwnership()) ? 150 : 242; ?>px;"><?php echo htmlspecialchars($comment['comment']); ?></textarea>
                </dd>
            </dl>

            <div class="button-box">
                <input type="button" class="input-button" id="commentSubmit" value="<?php echo _text('완료'); ?>"
                       onclick="<?php echo $confirmString; ?> submitComment();return false;"/>
            </div>
        </div>
    </div>
    <?php if (Acl::getIdentity('openid')) { ?>
        <input name="openidedit" type="hidden" value="1"/>
    <?php } ?>
</form>
<?php echo fireEvent('REPLY_body_end', '', $comment); ?>
</body>
</html>
