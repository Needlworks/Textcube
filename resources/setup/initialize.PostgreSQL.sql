CREATE TABLE [##_dbPrefix_##]Attachments (
  blogid integer NOT NULL default 0,
  parent integer NOT NULL default 0,
  name varchar(64) NOT NULL default '',
  label varchar(64) NOT NULL default '',
  mime varchar(32) NOT NULL default '',
  size integer NOT NULL default 0,
  width integer NOT NULL default 0,
  height integer NOT NULL default 0,
  attached integer NOT NULL default 0,
  downloads integer NOT NULL default 0,
  enclosure integer NOT NULL default 0,
  PRIMARY KEY  (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]BlogSettings (
  blogid integer NOT NULL default 0,
  name varchar(32) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY (blogid, name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]BlogStatistics (
  blogid integer NOT NULL default 0,
  visits integer NOT NULL default 0,
  PRIMARY KEY  (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Categories (
  blogid integer NOT NULL default 0,
  id integer NOT NULL,
  parent integer default NULL,
  name varchar(127) NOT NULL default '',
  priority integer NOT NULL default 0,
  entries integer NOT NULL default 0,
  entriesinlogin integer NOT NULL default 0,
  label varchar(255) NOT NULL default '',
  visibility integer NOT NULL default 2,
  bodyid varchar(20) default NULL,
  PRIMARY KEY (blogid,id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Comments (
  blogid integer NOT NULL default 0,
  replier integer default NULL,
  id integer NOT NULL,
  openid varchar(128) NOT NULL default '',
  entry integer NOT NULL default 0,
  parent integer default NULL,
  name varchar(80) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  homepage varchar(80) NOT NULL default '',
  secret integer NOT NULL default 0,
  comment text NOT NULL,
  ip varchar(15) NOT NULL default '',
  written integer NOT NULL default 0,
  isfiltered integer NOT NULL default 0,
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]Comments_blogid_idx ON [##_dbPrefix_##]Comments (blogid);
CREATE INDEX [##_dbPrefix_##]Comments_entry_idx ON [##_dbPrefix_##]Comments (entry);
CREATE INDEX [##_dbPrefix_##]Comments_parent_idx ON [##_dbPrefix_##]Comments (parent);
CREATE INDEX [##_dbPrefix_##]Comments_isfiltered_idx ON [##_dbPrefix_##]Comments (isfiltered);
CREATE TABLE [##_dbPrefix_##]CommentsNotified (
  blogid integer NOT NULL default 0,
  replier integer default NULL,
  id integer NOT NULL,
  entry integer NOT NULL default 0,
  parent integer default NULL,
  name varchar(80) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  homepage varchar(80) NOT NULL default '',
  secret integer NOT NULL default 0,
  comment text NOT NULL,
  ip varchar(15) NOT NULL default '',
  written integer NOT NULL default 0,
  modified integer NOT NULL default 0,
  siteid integer NOT NULL default 0,
  isnew integer NOT NULL default '1',
  url varchar(255) NOT NULL default '',
  remoteid integer NOT NULL default 0,
  entrytitle varchar(255) NOT NULL default '',
  entryurl varchar(255) NOT NULL default '',
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]CommentsNotified_blogid_idx ON [##_dbPrefix_##]CommentsNotified (blogid);
CREATE INDEX [##_dbPrefix_##]CommentsNotified_entry_idx ON [##_dbPrefix_##]CommentsNotified (entry);
CREATE TABLE [##_dbPrefix_##]CommentsNotifiedQueue (
  blogid integer NOT NULL default 0,
  id integer NOT NULL,
  commentid integer NOT NULL default 0,
  sendstatus integer NOT NULL default 0,
  checkdate integer NOT NULL default 0,
  written integer NOT NULL default 0,
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]CommentsNotifiedQueue_commentid_idx ON [##_dbPrefix_##]CommentsNotifiedQueue (commentid);
CREATE TABLE [##_dbPrefix_##]CommentsNotifiedSiteInfo (
  id integer NOT NULL,
  title varchar(255) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  modified integer NOT NULL default 0,
  PRIMARY KEY  (id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]CommentsNotifiedSiteInfo_url_idx ON [##_dbPrefix_##]CommentsNotifiedSiteInfo (url);
CREATE TABLE [##_dbPrefix_##]DailyStatistics (
  blogid integer NOT NULL default 0,
  datemark integer NOT NULL default 0,
  visits integer NOT NULL default 0,
  PRIMARY KEY  (blogid,datemark)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Entries (
  blogid integer NOT NULL default 0,
  userid integer NOT NULL default 0,
  id integer NOT NULL,
  draft integer NOT NULL default 0,
  visibility integer NOT NULL default 0,
  starred integer NOT NULL default '1',
  category integer NOT NULL default 0,
  title varchar(255) NOT NULL default '',
  slogan varchar(255) NOT NULL default '',
  content text NOT NULL,
  contentformatter varchar(32) DEFAULT '' NOT NULL,
  contenteditor varchar(32) DEFAULT '' NOT NULL,
  location varchar(255) NOT NULL default '/',
  latitude float default NULL,
  longitude float default NULL,
  password varchar(32) default NULL,
  acceptcomment integer NOT NULL default '1',
  accepttrackback integer NOT NULL default '1',
  published integer NOT NULL default 0,
  created integer NOT NULL default 0,
  modified integer NOT NULL default 0,
  comments integer NOT NULL default 0,
  trackbacks integer NOT NULL default 0,
  pingbacks integer NOT NULL default 0,
  PRIMARY KEY (blogid, id, draft, category, published)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]Entries_visibility_idx ON [##_dbPrefix_##]Entries (visibility);
CREATE INDEX [##_dbPrefix_##]Entries_userid_idx ON [##_dbPrefix_##]Entries (userid);
CREATE INDEX [##_dbPrefix_##]Entries_published_idx ON [##_dbPrefix_##]Entries (published);
CREATE INDEX [##_dbPrefix_##]Entries_id_category_visibility_idx ON [##_dbPrefix_##]Entries (id, category, visibility);
CREATE INDEX [##_dbPrefix_##]Entries_blogid_published_idx ON [##_dbPrefix_##]Entries (blogid, published);
CREATE TABLE [##_dbPrefix_##]EntriesArchive (
  blogid integer NOT NULL default 0,
  userid integer NOT NULL default 0,
  id integer NOT NULL,
  visibility integer NOT NULL default 0,
  category integer NOT NULL default 0,
  title varchar(255) NOT NULL default '',
  slogan varchar(255) NOT NULL default '',
  content text NOT NULL,
  contentformatter varchar(32) DEFAULT '' NOT NULL,
  contenteditor varchar(32) DEFAULT '' NOT NULL,
  location varchar(255) NOT NULL default '/',
  latitude float default NULL,
  longitude float default NULL,
  password varchar(32) default NULL,
  created integer NOT NULL default 0,
  PRIMARY KEY (blogid, id, created)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]EntriesArchive_visibility_idx ON [##_dbPrefix_##]EntriesArchive (visibility);
CREATE INDEX [##_dbPrefix_##]EntriesArchive_blogid__id_idx ON [##_dbPrefix_##]EntriesArchive (blogid, id);
CREATE INDEX [##_dbPrefix_##]EntriesArchive_userid_blogid_idx ON [##_dbPrefix_##]EntriesArchive (userid, blogid);
CREATE TABLE [##_dbPrefix_##]FeedGroupRelations (
  blogid integer NOT NULL default 0,
  feed integer NOT NULL default 0,
  groupid integer NOT NULL default 0,
  PRIMARY KEY  (blogid,feed,groupid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedGroups (
  blogid integer NOT NULL default 0,
  id integer NOT NULL default 0,
  title varchar(255) NOT NULL default '',
  PRIMARY KEY  (blogid,id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedItems (
  id integer NOT NULL default 1,
  feed integer NOT NULL default 0,
  author varchar(255) NOT NULL default '',
  permalink varchar(255) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  description text NOT NULL,
  tags varchar(255) NOT NULL default '',
  enclosure varchar(255) NOT NULL default '',
  written integer NOT NULL default 0,
  PRIMARY KEY  (id)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]FeedItems_feed_idx ON [##_dbPrefix_##]FeedItems (feed);
CREATE INDEX [##_dbPrefix_##]FeedItems_written_idx ON [##_dbPrefix_##]FeedItems (written);
CREATE INDEX [##_dbPrefix_##]FeedItems_permalink_idx ON [##_dbPrefix_##]FeedItems (permalink);
CREATE TABLE [##_dbPrefix_##]FeedReads (
  blogid integer NOT NULL default 0,
  item integer NOT NULL default 0,
  PRIMARY KEY  (blogid,item)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedSettings (
  blogid integer NOT NULL default 0,
  updatecycle integer NOT NULL default 120,
  feedlife integer NOT NULL default 30,
  loadimage integer NOT NULL default 1,
  allowscript integer NOT NULL default 2,
  newwindow integer NOT NULL default 1,
  PRIMARY KEY  (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedStarred (
  blogid integer NOT NULL default 0,
  item integer NOT NULL default 0,
  PRIMARY KEY  (blogid,item)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Feeds (
  id integer NOT NULL default 1,
  xmlurl varchar(255) NOT NULL default '',
  blogurl varchar(255) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  language varchar(5) NOT NULL default 'en-US',
  modified integer NOT NULL default 0,
  PRIMARY KEY  (id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Filters (
  id integer NOT NULL default 1,
  blogid integer NOT NULL default 0,
  filtertype varchar(11) NOT NULL default 'content',
  pattern varchar(255) NOT NULL default '',
  PRIMARY KEY (id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]Filters_blogid_filtertype_pattern_idx ON [##_dbPrefix_##]Filters (blogid, filtertype, pattern);
CREATE TABLE [##_dbPrefix_##]Lines (
  id integer NOT NULL default 0,
  blogid integer NOT NULL default 0,
  root varchar(11) NOT NULL default 'default', 
  category varchar(11) NOT NULL default 'public',
  author varchar(32) NOT NULL default '',  
  content varchar(512) NOT NULL default '', 
  permalink varchar(128) NOT NULL default '', 
  created integer NOT NULL default 0,
  PRIMARY KEY (id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]Lines_blogid_created_idx ON [##_dbPrefix_##]Lines (blogid, created);
CREATE INDEX [##_dbPrefix_##]Lines_blogid_category_created_idx ON [##_dbPrefix_##]Lines (blogid, category, created);
CREATE TABLE [##_dbPrefix_##]Links (
  pid integer NOT NULL default 0,
  blogid integer NOT NULL default 0,
  id integer NOT NULL default 0,
  category integer NOT NULL default 0,
  name varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  rss varchar(255) NOT NULL default '',
  written integer NOT NULL default 0,
  visibility integer NOT NULL default '2',
  xfn varchar(128) NOT NULL default '',
  PRIMARY KEY (pid)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]Links_blogid_url_idx ON [##_dbPrefix_##]Links (blogid, url);
CREATE TABLE [##_dbPrefix_##]LinkCategories (
  pid integer NOT NULL default 0,
  blogid integer NOT NULL default 0,
  id integer NOT NULL default 0,
  name varchar(128) NOT NULL,
  priority integer NOT NULL default 0,
  visibility integer NOT NULL default 2,
  PRIMARY KEY (pid)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]LinkCategories_blogid_id_idx ON [##_dbPrefix_##]LinkCategories (blogid, id);
CREATE TABLE [##_dbPrefix_##]OpenIDUsers (
  blogid integer NOT NULL default 0,
  openid varchar(128) NOT NULL,
  delegatedid varchar(128) default NULL,
  firstlogin integer default NULL,
  lastlogin integer default NULL,
  logincount integer default NULL,
  openidinfo text,
  PRIMARY KEY  (blogid,openid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]PageCacheLog (
  blogid integer NOT NULL default 0,
  name varchar(255) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Plugins (
  blogid integer NOT NULL default 0,
  name varchar(255) NOT NULL default '',
  settings text,
  PRIMARY KEY  (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Privileges (
  blogid integer NOT NULL default 1,
  userid integer NOT NULL default 1,
  acl integer NOT NULL default 0,
  created integer NOT NULL default 0,
  lastlogin integer NOT NULL default 0,
  PRIMARY KEY (blogid,userid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]RefererLogs (
  blogid integer NOT NULL default 0,
  host varchar(64) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  referred integer NOT NULL default 0
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]RefererLogs_blogid_referred_idx ON [##_dbPrefix_##]RefererLogs (blogid, referred);
CREATE TABLE [##_dbPrefix_##]RefererStatistics (
  blogid integer NOT NULL default 0,
  host varchar(64) NOT NULL default '',
  count integer NOT NULL default 0,
  PRIMARY KEY  (blogid,host)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]RefererStatistics_blogid_count_idx ON [##_dbPrefix_##]RefererStatistics (blogid, count);
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
  blogid integer NOT NULL default 0,
  PRIMARY KEY  (id,address,blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Sessions (
  id varchar(32) NOT NULL default '',
  address varchar(15) NOT NULL default '',
  userid integer default NULL,
  preexistence integer default NULL,
  privilege text default NULL,
  server varchar(64) NOT NULL default '',
  request varchar(255) NOT NULL default '',
  referer varchar(255) NOT NULL default '',
  timer float NOT NULL default 0,
  created integer NOT NULL default 0,
  updated integer NOT NULL default 0,
  PRIMARY KEY  (id,address)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]Sessions_updated_idx ON [##_dbPrefix_##]Sessions (updated);
CREATE TABLE [##_dbPrefix_##]SkinSettings (
  blogid integer NOT NULL default 0,
  name varchar(32) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY (blogid, name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]TagRelations (
  blogid integer NOT NULL default 0,
  tag integer NOT NULL default 0,
  entry integer NOT NULL default 0,
  PRIMARY KEY  (blogid, tag, entry)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]TagRelations_blogid_idx ON [##_dbPrefix_##]TagRelations (blogid);
CREATE TABLE [##_dbPrefix_##]Tags (
  id integer NOT NULL default 1,
  name varchar(255) NOT NULL default '',
  PRIMARY KEY (id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]Tags_name_idx ON [##_dbPrefix_##]Tags (name);
CREATE TABLE [##_dbPrefix_##]RemoteResponseLogs (
  blogid integer NOT NULL default 0,
  id integer NOT NULL,
  entry integer NOT NULL default 0,
  responsetype varchar(10) NOT NULL default 'trackback',
  url varchar(255) NOT NULL default '',
  written integer NOT NULL default 0,
  PRIMARY KEY  (blogid, entry, id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]RemoteResponseLogs_blogid_id_idx ON [##_dbPrefix_##]RemoteResponseLogs (blogid, id);
CREATE TABLE [##_dbPrefix_##]RemoteResponses (
  id integer NOT NULL,
  blogid integer NOT NULL default 0,
  entry integer NOT NULL default 0,
  responsetype varchar(10) NOT NULL default 'trackback',
  url varchar(255) NOT NULL default '',
  writer integer default NULL,
  site varchar(255) NOT NULL default '',
  subject varchar(255) NOT NULL default '',
  excerpt varchar(255) NOT NULL default '',
  ip varchar(15) NOT NULL default '',
  written integer NOT NULL default 0,
  isfiltered integer NOT NULL default 0,
  PRIMARY KEY (blogid, id)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]RemoteResponses_isfiltered_idx ON [##_dbPrefix_##]RemoteResponses (isfiltered);
CREATE INDEX [##_dbPrefix_##]RemoteResponses_blogid_isfiltered_written_idx ON [##_dbPrefix_##]RemoteResponses (blogid, isfiltered, written);
CREATE TABLE [##_dbPrefix_##]Users (
  userid integer NOT NULL default 1,
  loginid varchar(64) NOT NULL default '',
  password varchar(32) default NULL,
  name varchar(32) NOT NULL default '',
  created integer NOT NULL default 0,
  lastlogin integer NOT NULL default 0,
  host integer NOT NULL default 0,
  PRIMARY KEY  (userid)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]Users_loginid_idx ON [##_dbPrefix_##]Users (loginid);
CREATE UNIQUE INDEX [##_dbPrefix_##]Users_name_idx ON [##_dbPrefix_##]Users (name);
CREATE TABLE [##_dbPrefix_##]UserSettings (
  userid integer NOT NULL default 0,
  name varchar(32) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY (userid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Widgets (
  id integer default 1 NOT NULL,
  blogid integer default 1 NOT NULL,
  title varchar(64) default 'Widget' NOT NULL,
  author varchar(32) default 'Textcube' NOT NULL,
  email varchar(32) DEFAULT NULL,
  screenshot varchar(128) DEFAULT NULL,
  thumbnail varchar(128) DEFAULT NULL,
  titleurl varchar(128) DEFAULT NULL,
  authorlink varchar(128) DEFAULT NULL,
  authorlocation varchar(32) DEFAULT NULL,
  authorphoto varchar(128) DEFAULT NULL,
  height integer DEFAULT NULL,
  scrolling integer default 0,
  feature varchar(32) default 'opensocial',
  content text NOT NULL,
  PRIMARY KEY (id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]Widgets_blogid_idx ON [##_dbPrefix_##]Widgets (blogid);
CREATE TABLE [##_dbPrefix_##]XMLRPCPingSettings (
  blogid integer NOT NULL default 0,
  url varchar(255) NOT NULL default '',
  pingtype varchar(32) NOT NULL default 'xmlrpc',
  PRIMARY KEY (blogid)
) [##_charset_##];
