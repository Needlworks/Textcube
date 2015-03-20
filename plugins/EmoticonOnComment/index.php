<?php
function EmoticonOnComment_main($target, $mother) {
    $context = Model_Context::_getInstance();
    $url = $context->getProperty("plugin.uri");
    $emoticons = array(
        ':)' => '<img src="' . $url . '/emoticon01.gif" alt=":)" />',
        ';)' => '<img src="' . $url . '/emoticon01.gif" alt=";)" />',
        ':P' => '<img src="' . $url . '/emoticon02.gif" alt=":P" />',
        '8D' => '<img src="' . $url . '/emoticon03.gif" alt="8D" />',
        ':(' => '<img src="' . $url . '/emoticon04.gif" alt=":(" />',
        '--;' => '<img src="' . $url . '/emoticon05.gif" alt="--;" />'
    );
    foreach ($emoticons as $key => $value)
        $target = str_replace($key, $value, $target);
    return $target;
}

?>