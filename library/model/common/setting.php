<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getDefinedTableNames() {
    $context = Model_Context::getInstance();
    $prefix = $context->getProperty('database.prefix');
    return array(
        "{$prefix}Attachments",
        "{$prefix}BlogSettings",
        "{$prefix}BlogStatistics",
        "{$prefix}Categories",
        "{$prefix}Comments",
        "{$prefix}CommentsNotified",
        "{$prefix}CommentsNotifiedQueue",
        "{$prefix}CommentsNotifiedSiteInfo",
        "{$prefix}DailyStatistics",
        "{$prefix}Entries",
        "{$prefix}EntriesArchive",
        "{$prefix}FeedGroupRelations",
        "{$prefix}FeedGroups",
        "{$prefix}FeedItems",
        "{$prefix}FeedReads",
        "{$prefix}OpenIDUsers",
        "{$prefix}Feeds",
        "{$prefix}FeedSettings",
        "{$prefix}FeedStarred",
        "{$prefix}Filters",
        "{$prefix}Lines",
        "{$prefix}Links",
        "{$prefix}LinkCategories",
        "{$prefix}PageCacheLog",
        "{$prefix}Plugins",
        "{$prefix}Privileges",
        "{$prefix}RefererLogs",
        "{$prefix}RefererStatistics",
        "{$prefix}ReservedWords",
        "{$prefix}ServiceSettings",
        "{$prefix}Sessions",
        "{$prefix}SessionVisits",
        "{$prefix}SkinSettings",
        "{$prefix}TagRelations",
        "{$prefix}Tags",
        "{$prefix}TeamEntryRelations",
        "{$prefix}RemoteResponseLogs",
        "{$prefix}RemoteResponses",
        "{$prefix}Users",
        "{$prefix}UserSettings",
        "{$prefix}Widgets",
        "{$prefix}XMLRPCPingSettings");
}

?>
