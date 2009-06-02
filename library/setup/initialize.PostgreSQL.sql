CREATE TABLE [##_dbPrefix_##]Attachments (
  blogid integer NOT NULL default '0',
  parent integer NOT NULL default '0',
  name varchar(32) NOT NULL default '',
  label varchar(64) NOT NULL default '',
  mime varchar(32) NOT NULL default '',
  size integer NOT NULL default '0',
  width integer NOT NULL default '0',
  height integer NOT NULL default '0',
  attached integer NOT NULL default '0',
  downloads integer NOT NULL default '0',
  enclosure integer NOT NULL default '0',
  PRIMARY KEY  (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]BlogSettings (
  blogid integer NOT NULL default '0',
  name varchar(32) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY (blogid, name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]BlogStatistics (
  blogid integer NOT NULL default '0',
  visits integer NOT NULL default '0',
  PRIMARY KEY  (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Categories (
  blogid integer NOT NULL default '0',
  id integer NOT NULL,
  parent integer default NULL,
  name varchar(127) NOT NULL default '',
  priority integer NOT NULL default '0',
  entries integer NOT NULL default '0',
  "entriesInLogin" integer NOT NULL default '0',
  label varchar(255) NOT NULL default '',
  visibility integer NOT NULL default '2',
  bodyId varchar(20) default NULL,
  PRIMARY KEY (blogid,id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Comments (
  blogid integer NOT NULL default '0',
  replier integer default NULL,
  id integer NOT NULL,
  openid varchar(128) NOT NULL default '',
  entry integer NOT NULL default '0',
  parent integer default NULL,
  name varchar(80) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  homepage varchar(80) NOT NULL default '',
  secret integer NOT NULL default '0',
  comment text NOT NULL,
  ip varchar(15) NOT NULL default '',
  written integer NOT NULL default '0',
  "isFiltered" integer NOT NULL default '0',
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE INDEX Comments_blogid_idx ON [##_dbPrefix_##]Comments (blogid);
CREATE INDEX Comments_entry_idx ON [##_dbPrefix_##]Comments (entry);
CREATE INDEX Comments_parent_idx ON [##_dbPrefix_##]Comments (parent);
CREATE INDEX Comments_isFiltered_idx ON [##_dbPrefix_##]Comments ("isFiltered");
CREATE TABLE [##_dbPrefix_##]CommentsNotified (
  blogid integer NOT NULL default '0',
  replier integer default NULL,
  id integer NOT NULL,
  entry integer NOT NULL default '0',
  parent integer default NULL,
  name varchar(80) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  homepage varchar(80) NOT NULL default '',
  secret integer NOT NULL default '0',
  comment text NOT NULL,
  ip varchar(15) NOT NULL default '',
  written integer NOT NULL default '0',
  modified integer NOT NULL default '0',
  "siteId" integer NOT NULL default '0',
  "isNew" integer NOT NULL default '1',
  url varchar(255) NOT NULL default '',
  "remoteId" integer NOT NULL default '0',
  "entryTitle" varchar(255) NOT NULL default '',
  "entryUrl" varchar(255) NOT NULL default '',
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE INDEX CommentsNotified_blogid_idx ON [##_dbPrefix_##]CommentsNotified (blogid);
CREATE INDEX CommentsNotified_entry_idx ON [##_dbPrefix_##]CommentsNotified (entry);
CREATE TABLE [##_dbPrefix_##]CommentsNotifiedQueue (
  blogid integer NOT NULL default '0',
  id integer NOT NULL,
  "commentId" integer NOT NULL default '0',
  "sendStatus" integer NOT NULL default '0',
  "checkDate" integer NOT NULL default '0',
  written integer NOT NULL default '0',
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE UNIQUE INDEX CommentsNotifiedQueue_commentId_idx ON [##_dbPrefix_##]CommentsNotifiedQueue ("commentId");
CREATE TABLE [##_dbPrefix_##]CommentsNotifiedSiteInfo (
  id integer NOT NULL,
  title varchar(255) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  modified integer NOT NULL default '0',
  PRIMARY KEY  (id)
) [##_charset_##];
CREATE UNIQUE INDEX CommentsNotifiedSiteInfo_url_idx ON [##_dbPrefix_##]CommentsNotifiedSiteInfo (url);
CREATE TABLE [##_dbPrefix_##]DailyStatistics (
  blogid integer NOT NULL default '0',
  date integer NOT NULL default '0',
  visits integer NOT NULL default '0',
  PRIMARY KEY  (blogid,date)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Entries (
  blogid integer NOT NULL default '0',
  userid integer NOT NULL default '0',
  id integer NOT NULL,
  draft integer NOT NULL default '0',
  visibility integer NOT NULL default '0',
  starred integer NOT NULL default '1',
  category integer NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  slogan varchar(255) NOT NULL default '',
  content text NOT NULL,
  "contentFormatter" varchar(32) DEFAULT '' NOT NULL,
  "contentEditor" varchar(32) DEFAULT '' NOT NULL,
  location varchar(255) NOT NULL default '/',
  password varchar(32) default NULL,
  "acceptComment" integer NOT NULL default '1',
  "acceptTrackback" integer NOT NULL default '1',
  published integer NOT NULL default '0',
  created integer NOT NULL default '0',
  modified integer NOT NULL default '0',
  comments integer NOT NULL default '0',
  trackbacks integer NOT NULL default '0',
  PRIMARY KEY (blogid, id, draft, category, published)
) [##_charset_##];
CREATE INDEX Entries_visibility_idx ON [##_dbPrefix_##]Entries (visibility);
CREATE INDEX Entries_userid_idx ON [##_dbPrefix_##]Entries (userid);
CREATE INDEX Entries_published_idx ON [##_dbPrefix_##]Entries (published);
CREATE INDEX Entries_id_category_visibility_idx ON [##_dbPrefix_##]Entries (id, category, visibility);
CREATE INDEX Entries_blogid_published_idx ON [##_dbPrefix_##]Entries (blogid, published);
CREATE TABLE [##_dbPrefix_##]EntriesArchive (
  blogid integer NOT NULL default '0',
  userid integer NOT NULL default '0',
  id integer NOT NULL,
  visibility integer NOT NULL default '0',
  category integer NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  slogan varchar(255) NOT NULL default '',
  content text NOT NULL,
  "contentFormatter" varchar(32) DEFAULT '' NOT NULL,
  "contentEditor" varchar(32) DEFAULT '' NOT NULL,
  location varchar(255) NOT NULL default '/',
  password varchar(32) default NULL,
  created integer NOT NULL default '0',
  PRIMARY KEY (blogid, id, created)
) [##_charset_##];
CREATE INDEX EntriesArchive_visibility_idx ON [##_dbPrefix_##]EntriesArchive (visibility);
CREATE INDEX EntriesArchive_blogid__id_idx ON [##_dbPrefix_##]EntriesArchive (blogid, id);
CREATE INDEX EntriesArchive_userid_blogid_idx ON [##_dbPrefix_##]EntriesArchive (userid, blogid);
CREATE TABLE [##_dbPrefix_##]FeedGroupRelations (
  blogid integer NOT NULL default '0',
  feed integer NOT NULL default '0',
  "groupId" integer NOT NULL default '0',
  PRIMARY KEY  (blogid,feed,"groupId")
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedGroups (
  blogid integer NOT NULL default '0',
  id integer NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  PRIMARY KEY  (blogid,id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedItems (
  id integer NOT NULL default 1,
  feed integer NOT NULL default '0',
  author varchar(255) NOT NULL default '',
  permalink varchar(255) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  description text NOT NULL,
  tags varchar(255) NOT NULL default '',
  enclosure varchar(255) NOT NULL default '',
  written integer NOT NULL default '0',
  PRIMARY KEY  (id)
) [##_charset_##];
CREATE INDEX FeedItems_feed_idx ON [##_dbPrefix_##]FeedItems (feed);
CREATE INDEX FeedItems_written_idx ON [##_dbPrefix_##]FeedItems (written);
CREATE INDEX FeedItems_permalink_idx ON [##_dbPrefix_##]FeedItems (permalink);
CREATE TABLE [##_dbPrefix_##]FeedReads (
  blogid integer NOT NULL default '0',
  item integer NOT NULL default '0',
  PRIMARY KEY  (blogid,item)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedSettings (
  blogid integer NOT NULL default '0',
  "updateCycle" integer NOT NULL default '120',
  "feedLife" integer NOT NULL default '30',
  "loadImage" integer NOT NULL default '1',
  "allowScript" integer NOT NULL default '2',
  "newWindow" integer NOT NULL default '1',
  PRIMARY KEY  (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedStarred (
  blogid integer NOT NULL default '0',
  item integer NOT NULL default '0',
  PRIMARY KEY  (blogid,item)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Feeds (
  id integer NOT NULL default 1,
  "xmlURL" varchar(255) NOT NULL default '',
  "blogURL" varchar(255) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  language varchar(5) NOT NULL default 'en-US',
  modified integer NOT NULL default '0',
  PRIMARY KEY  (id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Filters (
  id integer NOT NULL default 1,
  blogid integer NOT NULL default '0',
  type varchar(11) NOT NULL default 'content',
  pattern varchar(255) NOT NULL default '',
  PRIMARY KEY (id)
) [##_charset_##];
CREATE UNIQUE INDEX Filters_blogid_type_pattern_idx ON [##_dbPrefix_##]Filters (blogid, type, pattern);
CREATE TABLE [##_dbPrefix_##]Links (
  pid integer NOT NULL default '0',
  blogid integer NOT NULL default '0',
  id integer NOT NULL default '0',
  category integer NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  rss varchar(255) NOT NULL default '',
  written integer NOT NULL default '0',
  visibility integer NOT NULL default '2',
  xfn varchar(128) NOT NULL default '',
  PRIMARY KEY (pid)
) [##_charset_##];
CREATE UNIQUE INDEX Links_blogid_url_idx ON [##_dbPrefix_##]Links (blogid, url);
CREATE TABLE [##_dbPrefix_##]LinkCategories (
  pid integer NOT NULL default '0',
  blogid integer NOT NULL default '0',
  id integer NOT NULL default '0',
  name varchar(128) NOT NULL,
  priority integer NOT NULL default '0',
  visibility integer NOT NULL default '2',
  PRIMARY KEY (pid)
) [##_charset_##];
CREATE UNIQUE INDEX LinkCategories_blogid_id_idx ON [##_dbPrefix_##]LinkCategories (blogid, id);
CREATE TABLE [##_dbPrefix_##]OpenIDUsers (
  blogid integer NOT NULL default '0',
  openid varchar(128) NOT NULL,
  delegatedid varchar(128) default NULL,
  "firstLogin" integer default NULL,
  "lastLogin" integer default NULL,
  "loginCount" integer default NULL,
  data text,
  PRIMARY KEY  (blogid,openid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]PageCacheLog (
  blogid integer NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Plugins (
  blogid integer NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  settings text,
  PRIMARY KEY  (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]RefererLogs (
  blogid integer NOT NULL default '0',
  host varchar(64) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  referred integer NOT NULL default '0'
) [##_charset_##];
CREATE INDEX RefererLogs_blogid_referred_idx ON [##_dbPrefix_##]RefererLogs (blogid, referred);
CREATE TABLE [##_dbPrefix_##]RefererStatistics (
  blogid integer NOT NULL default '0',
  host varchar(64) NOT NULL default '',
  count integer NOT NULL default '0',
  PRIMARY KEY  (blogid,host)
) [##_charset_##];
CREATE INDEX RefererStatistics_blogid_count_idx ON [##_dbPrefix_##]RefererStatistics (blogid, count);
CREATE TABLE [##_dbPrefix_##]ReservedWords (
  word varchar(16) NOT NULL default '',
  PRIMARY KEY  (word)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]ServiceSettings (
  name varchar(32) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY  (name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]SessionVisits (
  id varchar(32) NOT NULL default '',
  address varchar(15) NOT NULL default '',
  blogid integer NOT NULL default '0',
  PRIMARY KEY  (id,address,blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Sessions (
  id varchar(32) NOT NULL default '',
  address varchar(15) NOT NULL default '',
  userid integer default NULL,
  preexistence integer default NULL,
  data text default NULL,
  server varchar(64) NOT NULL default '',
  request varchar(255) NOT NULL default '',
  referer varchar(255) NOT NULL default '',
  timer float NOT NULL default '0',
  created integer NOT NULL default '0',
  updated integer NOT NULL default '0',
  PRIMARY KEY  (id,address)
) [##_charset_##];
CREATE INDEX Sessions_updated_idx ON [##_dbPrefix_##]Sessions (updated);
CREATE TABLE [##_dbPrefix_##]SkinSettings (
  blogid integer NOT NULL default '0',
  skin varchar(32) NOT NULL default 'coolant',
  "entriesOnRecent" integer NOT NULL default '10',
  "commentsOnRecent" integer NOT NULL default '10',
  "commentsOnGuestbook" integer NOT NULL default '5',
  "archivesOnPage" integer NOT NULL default '5',
  "tagsOnTagbox" integer NOT NULL default '10',
  "tagboxAlign" integer NOT NULL default '1',
  "trackbacksOnRecent" integer NOT NULL default '5',
  "expandComment" integer NOT NULL default '1',
  "expandTrackback" integer NOT NULL default '1',
  "recentNoticeLength" integer NOT NULL default '30',
  "recentEntryLength" integer NOT NULL default '30',
  "recentCommentLength" integer NOT NULL default '30',
  "recentTrackbackLength" integer NOT NULL default '30',
  "linkLength" integer NOT NULL default '30',
  "showListOnCategory" integer NOT NULL default '1',
  "showListOnArchive" integer NOT NULL default '1',
  "showListOnTag" integer NOT NULL default '1',
  "showListOnAuthor" integer NOT NULL default '1',
  "showListOnSearch" integer NOT NULL default '1',
  tree varchar(32) NOT NULL default 'base',
  "colorOnTree" varchar(6) NOT NULL default '000000',
  "bgColorOnTree" varchar(6) NOT NULL default '',
  "activeColorOnTree" varchar(6) NOT NULL default 'FFFFFF',
  "activeBgColorOnTree" varchar(6) NOT NULL default '00ADEF',
  "labelLengthOnTree" integer NOT NULL default '30',
  "showValueOnTree" integer NOT NULL default '1',
  PRIMARY KEY  (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]TagRelations (
  blogid integer NOT NULL default '0',
  tag integer NOT NULL default '0',
  entry integer NOT NULL default '0',
  PRIMARY KEY  (blogid, tag, entry)
) [##_charset_##];
CREATE INDEX TagRelations_blogid_idx ON [##_dbPrefix_##]TagRelations (blogid);
CREATE TABLE [##_dbPrefix_##]Tags (
  id integer NOT NULL default 1,
  name varchar(255) NOT NULL default '',
  PRIMARY KEY (id)
) [##_charset_##];
CREATE UNIQUE INDEX Tags_name_idx ON [##_dbPrefix_##]Tags (name);
CREATE TABLE [##_dbPrefix_##]TrackbackLogs (
  blogid integer NOT NULL default '0',
  id integer NOT NULL,
  entry integer NOT NULL default '0',
  url varchar(255) NOT NULL default '',
  written integer NOT NULL default '0',
  PRIMARY KEY  (blogid, entry, id)
) [##_charset_##];
CREATE UNIQUE INDEX TrackbackLogs_blogid_id_idx ON [##_dbPrefix_##]TrackbackLogs (blogid, id);
CREATE TABLE [##_dbPrefix_##]Trackbacks (
  id integer NOT NULL,
  blogid integer NOT NULL default '0',
  entry integer NOT NULL default '0',
  url varchar(255) NOT NULL default '',
  writer integer default NULL,
  site varchar(255) NOT NULL default '',
  subject varchar(255) NOT NULL default '',
  excerpt varchar(255) NOT NULL default '',
  ip varchar(15) NOT NULL default '',
  written integer NOT NULL default '0',
  "isFiltered" integer NOT NULL default '0',
  PRIMARY KEY (blogid, id)
) [##_charset_##];
CREATE INDEX Trackbacks_isFiltered_idx ON [##_dbPrefix_##]Trackbacks ("isFiltered");
CREATE INDEX Trackbacks_blogid_isFiltered_written_idx ON [##_dbPrefix_##]Trackbacks (blogid, "isFiltered", written);
CREATE TABLE [##_dbPrefix_##]Users (
  userid integer NOT NULL default 1,
  loginid varchar(64) NOT NULL default '',
  password varchar(32) default NULL,
  name varchar(32) NOT NULL default '',
  created integer NOT NULL default '0',
  "lastLogin" integer NOT NULL default '0',
  host integer NOT NULL default '0',
  PRIMARY KEY  (userid)
) [##_charset_##];
CREATE UNIQUE INDEX Users_loginid_idx ON [##_dbPrefix_##]Users (loginid);
CREATE UNIQUE INDEX Users_name_idx ON [##_dbPrefix_##]Users (name);
CREATE TABLE [##_dbPrefix_##]UserSettings (
  userid integer NOT NULL default '0',
  name varchar(32) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY (userid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]XMLRPCPingSettings (
  blogid integer NOT NULL default 0,
  url varchar(255) NOT NULL default '',
  type varchar(32) NOT NULL default 'xmlrpc',
  PRIMARY KEY (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Teamblog (
  blogid integer NOT NULL default 1,
  userid integer NOT NULL default 1,
  acl integer NOT NULL default 0,
  created integer NOT NULL default 0,
  "lastLogin" integer NOT NULL default 0,
  PRIMARY KEY (blogid,userid)
) [##_charset_##];