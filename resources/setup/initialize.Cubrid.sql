CREATE TABLE [##_dbPrefix_##]Attachments (
  blogid integer default 0 NOT NULL ,
  parent integer default 0 NOT NULL,
  name varchar(64) default '' NOT NULL,
  label varchar(64) default '' NOT NULL,
  mime varchar(32) default '' NOT NULL,
  "size" integer default 0 NOT NULL,
  width integer default 0 NOT NULL,
  height integer default 0 NOT NULL,
  attached integer default 0 NOT NULL,
  downloads integer default 0 NOT NULL,
  enclosure integer default 0 NOT NULL,
  PRIMARY KEY  (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]BlogSettings (
  blogid integer default 0 NOT NULL,
  name varchar(32) default '' NOT NULL,
  "value" varchar NOT NULL,
  PRIMARY KEY (blogid, name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]BlogStatistics (
  blogid integer default 0 NOT NULL,
  visits integer default 0 NOT NULL,
  PRIMARY KEY  (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Categories (
  blogid integer default 0 NOT NULL,
  id integer NOT NULL,
  parent integer default NULL,
  name varchar(127) default '' NOT NULL,
  priority integer default 0 NOT NULL,
  entries integer default 0 NOT NULL,
  entriesInLogin integer default 0 NOT NULL,
  label varchar(255) default '' NOT NULL,
  visibility integer default 2 NOT NULL,
  bodyid varchar(20) default NULL,
  PRIMARY KEY (blogid,id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Comments (
  blogid integer default 0 NOT NULL,
  replier integer default NULL,
  id integer NOT NULL,
  openid varchar(128) default '' NOT NULL,
  entry integer default 0 NOT NULL,
  parent integer default NULL,
  name varchar(80) default '' NOT NULL,
  password varchar(32) default '' NOT NULL,
  homepage varchar(80) default '' NOT NULL,
  secret integer default 0 NOT NULL,
  comment varchar NOT NULL,
  ip varchar(15) default '' NOT NULL,
  written integer default 0 NOT NULL,
  isfiltered integer default 0 NOT NULL,
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]Comments_blogid_idx ON [##_dbPrefix_##]Comments (blogid);
CREATE INDEX [##_dbPrefix_##]Comments_entry_idx ON [##_dbPrefix_##]Comments (entry);
CREATE INDEX [##_dbPrefix_##]Comments_parent_idx ON [##_dbPrefix_##]Comments (parent);
CREATE INDEX [##_dbPrefix_##]Comments_isfiltered_idx ON [##_dbPrefix_##]Comments (isfiltered);
CREATE TABLE [##_dbPrefix_##]TrashComments (
  blogid integer default 0 NOT NULL,
  replier integer default NULL,
  id integer NOT NULL,
  openid varchar(128) default '' NOT NULL,
  entry integer default 0 NOT NULL,
  parent integer default NULL,
  name varchar(80) default '' NOT NULL,
  password varchar(32) default '' NOT NULL,
  homepage varchar(80) default '' NOT NULL,
  secret integer default 0 NOT NULL,
  comment varchar NOT NULL,
  ip varchar(15) default '' NOT NULL,
  written integer default 0 NOT NULL,
  isfiltered integer default 0 NOT NULL,
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]TrashComments_blogid_idx ON [##_dbPrefix_##]TrashComments (blogid);
CREATE INDEX [##_dbPrefix_##]TrashComments_entry_idx ON [##_dbPrefix_##]TrashComments (entry);
CREATE INDEX [##_dbPrefix_##]TrashComments_parent_idx ON [##_dbPrefix_##]TrashComments (parent);
CREATE INDEX [##_dbPrefix_##]TrashComments_isfiltered_idx ON [##_dbPrefix_##]TrashComments (isfiltered);
CREATE TABLE [##_dbPrefix_##]CommentsNotified (
  blogid integer default 0 NOT NULL,
  replier integer default NULL,
  id integer NOT NULL,
  entry integer default 0 NOT NULL,
  parent integer default NULL,
  name varchar(80) default '' NOT NULL,
  password varchar(32) default '' NOT NULL,
  homepage varchar(80) default '' NOT NULL,
  secret integer default 0 NOT NULL,
  comment varchar NOT NULL,
  ip varchar(15) default '' NOT NULL,
  written integer default 0 NOT NULL,
  modified integer default 0 NOT NULL,
  siteid integer default 0 NOT NULL,
  isnew integer default 1 NOT NULL,
  url varchar(255) default '' NOT NULL,
  remoteid integer default 0 NOT NULL,
  entrytitle varchar(255) default '' NOT NULL,
  entryurl varchar(255) default '' NOT NULL,
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]CommentsNotified_blogid_idx ON [##_dbPrefix_##]CommentsNotified (blogid);
CREATE INDEX [##_dbPrefix_##]CommentsNotified_entry_idx ON [##_dbPrefix_##]CommentsNotified (entry);
CREATE TABLE [##_dbPrefix_##]CommentsNotifiedQueue (
  blogid integer default 0 NOT NULL,
  id integer NOT NULL,
  commentid integer default 0 NOT NULL,
  sendstatus integer default 0 NOT NULL,
  checkdate integer default 0 NOT NULL,
  written integer default 0 NOT NULL,
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]CommentsNotifiedQueue_commentid_idx ON [##_dbPrefix_##]CommentsNotifiedQueue (commentid);
CREATE TABLE [##_dbPrefix_##]CommentsNotifiedSiteInfo (
  id integer NOT NULL,
  title varchar(255) default '' NOT NULL,
  name varchar(255) default '' NOT NULL,
  url varchar(255) default '' NOT NULL,
  modified integer default 0 NOT NULL,
  PRIMARY KEY  (id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]CommentsNotifiedSiteInfo_url_idx ON [##_dbPrefix_##]CommentsNotifiedSiteInfo (url);
CREATE TABLE [##_dbPrefix_##]DailyStatistics (
  blogid integer default 0 NOT NULL,
  datemark integer default 0 NOT NULL,
  visits integer default 0 NOT NULL,
  PRIMARY KEY  (blogid,datemark)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Entries (
  blogid integer default 0 NOT NULL,
  userid integer default 0 NOT NULL,
  id integer NOT NULL,
  draft integer default 0 NOT NULL,
  visibility smallint default 0 NOT NULL,
  starred integer default 1 NOT NULL,
  category integer default 0 NOT NULL,
  title varchar(255) default '' NOT NULL,
  slogan varchar(255) default '' NOT NULL,
  content varchar NOT NULL,
  contentformatter varchar(32) DEFAULT '' NOT NULL,
  contenteditor varchar(32) DEFAULT '' NOT NULL,
  location varchar(255) default '/' NOT NULL,
  latitude float default NULL,
  longitude float default NULL,
  password varchar(32) default NULL,
  acceptcomment integer default 1 NOT NULL,
  accepttrackback integer default 1 NOT NULL,
  published integer default 0 NOT NULL,
  created integer default 0 NOT NULL,
  modified integer default 0 NOT NULL,
  comments integer default 0 NOT NULL,
  trackbacks integer default 0 NOT NULL,
  pingbacks integer default 0 NOT NULL,
  PRIMARY KEY (blogid, id, draft, category, published)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]Entries_visibility_idx ON [##_dbPrefix_##]Entries (visibility);
CREATE INDEX [##_dbPrefix_##]Entries_userid_idx ON [##_dbPrefix_##]Entries (userid);
CREATE INDEX [##_dbPrefix_##]Entries_published_idx ON [##_dbPrefix_##]Entries (published);
CREATE INDEX [##_dbPrefix_##]Entries_id_category_visibility_idx ON [##_dbPrefix_##]Entries (id, category, visibility);
CREATE INDEX [##_dbPrefix_##]Entries_blogid_published_idx ON [##_dbPrefix_##]Entries (blogid, published);
CREATE TABLE [##_dbPrefix_##]EntriesArchive (
  blogid integer default 0 NOT NULL,
  userid integer default 0 NOT NULL,
  id integer NOT NULL,
  visibility smallint default 0 NOT NULL,
  category integer default 0 NOT NULL,
  title varchar(255) default '' NOT NULL,
  slogan varchar(255) default '' NOT NULL,
  content varchar NOT NULL,
  contentformatter varchar(32) DEFAULT '' NOT NULL,
  contenteditor varchar(32) DEFAULT '' NOT NULL,
  location varchar(255) default '/' NOT NULL,
  latitude float default NULL,
  longitude float default NULL,
  password varchar(32) default NULL,
  created integer default 0 NOT NULL,
  PRIMARY KEY (blogid, id, created)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]EntriesArchive_visibility_idx ON [##_dbPrefix_##]EntriesArchive (visibility);
CREATE INDEX [##_dbPrefix_##]EntriesArchive_blogid__id_idx ON [##_dbPrefix_##]EntriesArchive (blogid, id);
CREATE INDEX [##_dbPrefix_##]EntriesArchive_userid_blogid_idx ON [##_dbPrefix_##]EntriesArchive (userid, blogid);
CREATE TABLE [##_dbPrefix_##]FeedGroupRelations (
  blogid integer default 0 NOT NULL,
  feed integer default 0 NOT NULL,
  groupid integer default 0 NOT NULL,
  PRIMARY KEY  (blogid,feed,groupid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedGroups (
  blogid integer default 0 NOT NULL,
  id integer default 0 NOT NULL,
  title varchar(255) default '' NOT NULL,
  PRIMARY KEY  (blogid,id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedItems (
  id integer default 1 NOT NULL,
  feed integer default 0 NOT NULL,
  author varchar(255) default '' NOT NULL,
  permalink varchar(255) default '' NOT NULL,
  title varchar(255) default '' NOT NULL,
  description varchar NOT NULL,
  tags varchar(255) default '' NOT NULL,
  enclosure varchar(255) default '' NOT NULL,
  written integer default 0 NOT NULL,
  PRIMARY KEY  (id)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]FeedItems_feed_idx ON [##_dbPrefix_##]FeedItems (feed);
CREATE INDEX [##_dbPrefix_##]FeedItems_written_idx ON [##_dbPrefix_##]FeedItems (written);
CREATE INDEX [##_dbPrefix_##]FeedItems_permalink_idx ON [##_dbPrefix_##]FeedItems (permalink);
CREATE TABLE [##_dbPrefix_##]FeedReads (
  blogid integer default 0 NOT NULL,
  item integer default 0 NOT NULL,
  PRIMARY KEY  (blogid,item)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedSettings (
  blogid integer default 0 NOT NULL,
  updatecycle integer default 120 NOT NULL,
  feedlife integer default 30 NOT NULL,
  loadimage integer default 1 NOT NULL,
  allowscript integer default 2 NOT NULL,
  newwindow integer default 1 NOT NULL,
  PRIMARY KEY  (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedStarred (
  blogid integer default 0 NOT NULL,
  item integer default 0 NOT NULL,
  PRIMARY KEY  (blogid,item)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Feeds (
  id integer default 1 NOT NULL,
  xmlurl varchar(255) default '' NOT NULL,
  blogURL varchar(255) default '' NOT NULL,
  title varchar(255) default '' NOT NULL,
  description varchar(255) default '' NOT NULL,
  "language" varchar(5) default 'en-US' NOT NULL,
  modified integer default 0 NOT NULL,
  PRIMARY KEY  (id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Filters (
  id integer default 1 NOT NULL,
  blogid integer default 0 NOT NULL,
  filtertype varchar(11) default 'content' NOT NULL,
  pattern varchar(255) default '' NOT NULL,
  PRIMARY KEY (id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]Filters_blogid_filtertype_pattern_idx ON [##_dbPrefix_##]Filters (blogid, filtertype, pattern);
CREATE TABLE [##_dbPrefix_##]Lines (
  id integer default 0 NOT NULL,
  blogid integer default 0 NOT NULL,
  root varchar(11) default 'default' NOT NULL, 
  category varchar(11) default 'public' NOT NULL,
  author varchar(32) default '' NOT NULL, 
  content varchar(512) default '' NOT NULL,
  permalink varchar(128) default '' NOT NULL, 
  created integer default 0 NOT NULL,
  PRIMARY KEY (id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]Lines_blogid_created_idx ON [##_dbPrefix_##]Lines (blogid, created);
CREATE INDEX [##_dbPrefix_##]Lines_blogid_category_created_idx ON [##_dbPrefix_##]Lines (blogid, category, created);
CREATE TABLE [##_dbPrefix_##]Links (
  pid integer default 0 NOT NULL,
  blogid integer default 0 NOT NULL,
  id integer default 0 NOT NULL,
  category integer default 0 NOT NULL,
  name varchar(255) default '' NOT NULL,
  url varchar(255) default '' NOT NULL,
  rss varchar(255) default '' NOT NULL,
  written integer default 0 NOT NULL,
  visibility smallint default 2 NOT NULL,
  xfn varchar(128) default '' NOT NULL,
  PRIMARY KEY (pid)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]Links_blogid_url_idx ON [##_dbPrefix_##]Links (blogid, url);
CREATE TABLE [##_dbPrefix_##]LinkCategories (
  pid integer default 0 NOT NULL,
  blogid integer default 0 NOT NULL,
  id integer default 0 NOT NULL,
  name varchar(128) NOT NULL,
  priority integer default 0 NOT NULL,
  visibility smallint default 2 NOT NULL,
  PRIMARY KEY (pid)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]LinkCategories_blogid_id_idx ON [##_dbPrefix_##]LinkCategories (blogid, id);
CREATE TABLE [##_dbPrefix_##]OpenIDUsers (
  blogid integer default 0 NOT NULL,
  openid varchar(128) NOT NULL,
  delegatedid varchar(128) default NULL,
  firstlogin integer default NULL,
  lastlogin integer default NULL,
  logincount integer default NULL,
  openidinfo varchar,
  PRIMARY KEY  (blogid,openid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]PageCacheLog (
  blogid integer default 0 NOT NULL,
  name varchar(255) default '' NOT NULL,
  "value" varchar NOT NULL,
  PRIMARY KEY (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Plugins (
  blogid integer default 0 NOT NULL,
  name varchar(255) default '' NOT NULL,
  settings varchar,
  PRIMARY KEY  (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Properties (
  blogid integer default 0 NOT NULL,
  namespace varchar(32) default '' NOT NULL,
  "keyname" varchar(32) default '' NOT NULL,
  "value" varchar NOT NULL,
  PRIMARY KEY (blogid, namespace, keyname)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]RefererLogs (
  blogid integer default 0 NOT NULL,
  host varchar(64) default '' NOT NULL,
  url varchar(255) default '' NOT NULL,
  referred integer default 0 NOT NULL
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]RefererLogs_blogid_referred_idx ON [##_dbPrefix_##]RefererLogs (blogid, referred);
CREATE TABLE [##_dbPrefix_##]RefererStatistics (
  blogid integer default 0 NOT NULL,
  host varchar(64) default '' NOT NULL,
  "count" integer default 0 NOT NULL,
  PRIMARY KEY  (blogid,host)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]RefererStatistics_blogid_count_idx ON [##_dbPrefix_##]RefererStatistics (blogid, "count");
CREATE TABLE [##_dbPrefix_##]ReservedWords (
  word varchar(16) default '' NOT NULL,
  PRIMARY KEY  (word)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]ServiceSettings (
  name varchar(32) default '' NOT NULL,
  "value" varchar NOT NULL,
  PRIMARY KEY  (name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]SessionVisits (
  id varchar(32) default '' NOT NULL,
  address varchar(15) default '' NOT NULL,
  blogid integer default 0 NOT NULL,
  PRIMARY KEY  (id,address,blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Sessions (
  id varchar(32) default '' NOT NULL,
  address varchar(15) default '' NOT NULL,
  userid integer default NULL,
  preexistence integer default NULL,
  privilege varchar default NULL,
  server varchar(64) default '' NOT NULL,
  request varchar(255) default '' NOT NULL,
  referer varchar(255) default '' NOT NULL,
  timer float default 0 NOT NULL,
  created integer default 0 NOT NULL,
  updated integer default 0 NOT NULL,
  expires integer default 0 NOT NULL,
  PRIMARY KEY  (id,address)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]Sessions_updated_idx ON [##_dbPrefix_##]Sessions (updated);
CREATE TABLE [##_dbPrefix_##]SkinSettings (
  blogid integer default 0 NOT NULL,
  name varchar(32) default '' NOT NULL,
  "value" varchar NOT NULL,
  PRIMARY KEY (blogid, name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]TagRelations (
  blogid integer default 0 NOT NULL,
  tag integer default 0 NOT NULL,
  entry integer default 0 NOT NULL,
  PRIMARY KEY  (blogid, tag, entry)
) [##_charset_##];
CREATE INDEX TagRelations_blogid_idx ON [##_dbPrefix_##]TagRelations (blogid);
CREATE TABLE [##_dbPrefix_##]Tags (
  id integer default 1 NOT NULL,
  name varchar(255) default '' NOT NULL,
  PRIMARY KEY (id)
) [##_charset_##];
CREATE UNIQUE INDEX Tags_name_idx ON [##_dbPrefix_##]Tags (name);
CREATE TABLE [##_dbPrefix_##]Privileges (
  blogid integer default 1 NOT NULL,
  userid integer default 1 NOT NULL,
  acl integer default 0 NOT NULL,
  created integer default 0 NOT NULL,
  lastlogin integer default 0 NOT NULL,
  PRIMARY KEY (blogid,userid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]RemoteResponseLogs (
  blogid integer default 0 NOT NULL,
  id integer NOT NULL,
  entry integer default 0 NOT NULL,
  responsetype varchar(10) default 'trackback' NOT NULL,
  url varchar(255) default '' NOT NULL,
  written integer default 0 NOT NULL,
  PRIMARY KEY  (blogid, entry, id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]RemoteResponseLogs_blogid_id_idx ON [##_dbPrefix_##]RemoteResponseLogs (blogid, id);
CREATE TABLE [##_dbPrefix_##]RemoteResponses (
  id integer NOT NULL,
  blogid integer default 0 NOT NULL,
  entry integer default 0 NOT NULL,
  responsetype varchar(10) default 'trackback' NOT NULL,
  url varchar(255) default '' NOT NULL,
  writer integer default NULL,
  site varchar(255) default '' NOT NULL,
  subject varchar(255) default '' NOT NULL,
  excerpt varchar(255) default '' NOT NULL,
  ip varchar(15) default '' NOT NULL,
  written integer default 0 NOT NULL,
  isfiltered integer default 0 NOT NULL,
  PRIMARY KEY (blogid, id)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]RemoteResponses_isfiltered_idx ON [##_dbPrefix_##]RemoteResponses (isfiltered);
CREATE INDEX [##_dbPrefix_##]RemoteResponses_blogid_isfiltered_written_idx ON [##_dbPrefix_##]RemoteResponses (blogid, isfiltered, written);
CREATE TABLE [##_dbPrefix_##]TrashRemoteResponses (
  id integer NOT NULL,
  blogid integer default 0 NOT NULL,
  entry integer default 0 NOT NULL,
  responsetype varchar(10) default 'trackback' NOT NULL,
  url varchar(255) default '' NOT NULL,
  writer integer default NULL,
  site varchar(255) default '' NOT NULL,
  subject varchar(255) default '' NOT NULL,
  excerpt varchar(255) default '' NOT NULL,
  ip varchar(15) default '' NOT NULL,
  written integer default 0 NOT NULL,
  isfiltered integer default 0 NOT NULL,
  PRIMARY KEY (blogid, id)
) [##_charset_##];
CREATE INDEX [##_dbPrefix_##]TrashRemoteResponses_isfiltered_idx ON [##_dbPrefix_##]TrashRemoteResponses (isfiltered);
CREATE INDEX [##_dbPrefix_##]TrashRemoteResponses_blogid_isfiltered_written_idx ON [##_dbPrefix_##]TrashRemoteResponses (blogid, isfiltered, written);
CREATE TABLE [##_dbPrefix_##]Users (
  userid integer default 1 NOT NULL,
  loginid varchar(64) default '' NOT NULL,
  password varchar(32) default NULL,
  name varchar(32) default '' NOT NULL,
  created integer default 0 NOT NULL,
  lastlogin integer default 0 NOT NULL,
  host integer default 0 NOT NULL,
  PRIMARY KEY  (userid)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]Users_loginid_idx ON [##_dbPrefix_##]Users (loginid);
CREATE UNIQUE INDEX [##_dbPrefix_##]Users_name_idx ON [##_dbPrefix_##]Users (name);
CREATE TABLE [##_dbPrefix_##]UserSettings (
  userid integer default 0 NOT NULL,
  name varchar(32) default '' NOT NULL,
  "value" varchar NOT NULL,
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
  content varchar NOT NULL,
  PRIMARY KEY (id)
) [##_charset_##];
CREATE UNIQUE INDEX [##_dbPrefix_##]Widgets_blogid_idx ON [##_dbPrefix_##]Widgets (blogid);
CREATE TABLE [##_dbPrefix_##]XMLRPCPingSettings (
  blogid integer default 0 NOT NULL,
  url varchar(255) default '' NOT NULL,
  pingtype varchar(32) default 'xmlrpc' NOT NULL,
  PRIMARY KEY (blogid)
) [##_charset_##];
