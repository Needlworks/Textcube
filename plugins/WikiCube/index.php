<?php
/* WikiCube
   ----------------------------------
   Version 0.15
   Starts at        : Apr. 5, 2006
   Last modified at : July. 27, 2015 (WIP)

   Jeongkyu Shin.
   E-mail : inureyes@gmail.com


 For the detail, visit http://forest.nubimaru.com/entry/WikiCube

 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 15. Prepare for raw editor support.
 14. 2.0-compatible fixes.
 13. user-custom link added. [[printWord|realLink]]
 12. category link added.
 11. tag link added.
 10. error page post added.
 09. basic functions.
*/

function WikiCube_FormatContent($target, $mother) {
    $context = Model_Context::getInstance();
    $config = $context->getProperty('plugin.config');

    if (empty($config['mode'])) {
        $config['mode'] = 'entry';
    }

    $pattern = array(
        '/\[\[(.*?)\|(.*?)\]\]/' => '<a href="' . $context->getProperty('uri.blog') . '/' . $config['mode'] . '/$2' . '">$1</a>',
        '/\[\[tg:(.*?)\]\]/' => '<a href="' . $context->getProperty('uri.blog') . '/tag/$1' . '">$1</a>',
        '/\[\[ct:(.*?)\]\]/' => '<a href="' . $context->getProperty('uri.blog') . '/category/$1' . '">$1</a>',
        '/\[\[(.*?)\]\]/' => '<a href="' . $context->getProperty('uri.blog') . '/' . $config['mode'] . '/$1' . '">$1</a>'
    );
    foreach ($pattern as $original => $replaced)
        $target = preg_replace($original, $replaced, $target);

    return $target;
}

function WikiCube_FormatErrorPage($target) {
    $context = Model_Context::getInstance();
    $config = $context->getProperty('plugin.config');
    if (empty($config['mode'])) {
        $config['mode'] = 'entry';
    }

    $additional = '<div style="border:none;width:100%;text-align:center;"><a href="' . $context->getProperty('uri.blog') .
        '/owner/entry/post?slogan=' . $context->getProperty('suri.value') .
        ($config['mode'] == 'entry' ? '' : '&category=-3') .
        '">' . _text('Empty page. Click here to add a new entry.') . '</a></div>';
    return $target . $additional;
}

function WikiCube_AddButton($target) {
    $result = '';
    $context = Model_Context::getInstance();
    if ($context->getProperty('suri.directive') == '/owner/entry/post' || $context->getProperty('suri.directive') == '/owner/entry/edit') {
        ob_start();
        ?>
        <script type="text/javascript">
            editor.addCommand('wikicubeAddLink', function () {
                if (editor.editormode == 'wysiwyg') {
                  selectedContent = editor.selection.getContent();
                  editor.execCommand('mceInsertContent', false, "[[" + selectedContent + "]]");
                }
            });
            editor.addButton('wikicubeAddWikiLink', {
                title: 'Add Wiki Link',
                cmd: 'wikicubeAddLink',
                icon: 'link'
            });
            editor.addCommand('wikicubeAddTagLink', function () {
                if (editor.editormode == 'wysiwyg') {
                    selectedContent = editor.selection.getContent();
                    editor.execCommand('mceInsertContent', false, "[[tg:" + selectedContent + "]]");
                }
            });
            editor.addButton('wikicubeAddTagLink', {
                title: 'Add Wiki Tag Link',
                cmd: 'wikicubeAddTagLink',
                icon: 'bookmark'
            });
            editor.addCommand('wikicubeAddCategoryLink', function () {
                if (editor.editormode == 'wysiwyg') {
                    selectedContent = editor.selection.getContent();
                    editor.execCommand('mceInsertContent', false, "[[ct:" + selectedContent + "]]");
                }
            });
            editor.addButton('wikicubeAddCategoryLink', {
                title: 'Add Wiki Link',
                cmd: 'wikicubeAddCategoryLink',
                icon: 'link'
            });
            editor.settings.toolbar2 = editor.settings.toolbar2 + ' wikicubeAddWikiLink';
            editor.settings.toolbar2 = editor.settings.toolbar2 + ' wikicubeAddTagLink';
            editor.settings.toolbar2 = editor.settings.toolbar2 + ' wikicubeAddCategoryLink';
            //editor.render();
        </script>
        <?php
        $result = ob_get_contents();
        ob_end_clean();
    }
    return $target . $result;
}

function WikiCube_DataHandler($data) {
    $context = Model_Context::getInstance();
    $config = $context->getProperty('plugin.config');

    if (!array_key_exists('mode', $config)) {
        return false;
    }
    return true;
}

?>
