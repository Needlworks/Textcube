CREATE TABLE [##_dbPrefix_##]Attachments (
  blogid integer default 0 NOT NULL ,
  parent integer default 0 NOT NULL,
  name varchar(32) default '' NOT NULL,
  label varchar(64) default '' NOT NULL,
  mime varchar(32) default '' NOT NULL,
  filesize integer default 0 NOT NULL,
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
  value varchar NOT NULL,
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
  bodyId varchar(20) default NULL,
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
  isFiltered integer default 0 NOT NULL,
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE INDEX Comments_blogid_idx ON [##_dbPrefix_##]Comments (blogid);
CREATE INDEX Comments_entry_idx ON [##_dbPrefix_##]Comments (entry);
CREATE INDEX Comments_parent_idx ON [##_dbPrefix_##]Comments (parent);
CREATE INDEX Comments_isFiltered_idx ON [##_dbPrefix_##]Comments (isFiltered);
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
  siteId integer default 0 NOT NULL,
  isNew integer default 1 NOT NULL,
  url varchar(255) default '' NOT NULL,
  remoteId integer default 0 NOT NULL,
  entryTitle varchar(255) default '' NOT NULL,
  entryUrl varchar(255) default '' NOT NULL,
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE INDEX CommentsNotified_blogid_idx ON [##_dbPrefix_##]CommentsNotified (blogid);
CREATE INDEX CommentsNotified_entry_idx ON [##_dbPrefix_##]CommentsNotified (entry);
CREATE TABLE [##_dbPrefix_##]CommentsNotifiedQueue (
  blogid integer default 0 NOT NULL,
  id integer NOT NULL,
  commentId integer default 0 NOT NULL,
  sendStatus integer default 0 NOT NULL,
  checkDate integer default 0 NOT NULL,
  written integer default 0 NOT NULL,
  PRIMARY KEY  (blogid, id)
) [##_charset_##];
CREATE UNIQUE INDEX CommentsNotifiedQueue_commentId_idx ON [##_dbPrefix_##]CommentsNotifiedQueue (commentId);
CREATE TABLE [##_dbPrefix_##]CommentsNotifiedSiteInfo (
  id integer NOT NULL,
  title varchar(255) default '' NOT NULL,
  name varchar(255) default '' NOT NULL,
  url varchar(255) default '' NOT NULL,
  modified integer default 0 NOT NULL,
  PRIMARY KEY  (id)
) [##_charset_##];
CREATE UNIQUE INDEX CommentsNotifiedSiteInfo_url_idx ON [##_dbPrefix_##]CommentsNotifiedSiteInfo (url);
CREATE UNIQUE INDEX CommentsNotifiedSiteInfo_id_idx ON [##_dbPrefix_##]CommentsNotifiedSiteInfo (id);
CREATE TABLE [##_dbPrefix_##]DailyStatistics (
  blogid integer default 0 NOT NULL,
  date integer default 0 NOT NULL,
  visits integer default 0 NOT NULL,
  PRIMARY KEY  (blogid,date)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Entries (
  blogid integer default 0 NOT NULL,
  userid integer default 0 NOT NULL,
  id integer NOT NULL,
  draft integer default 0 NOT NULL,
  visibility integer default 0 NOT NULL,
  starred integer default 1 NOT NULL,
  category integer default 0 NOT NULL,
  title varchar(255) default '' NOT NULL,
  slogan varchar(255) default '' NOT NULL,
  content varchar NOT NULL,
  contentFormatter varchar(32) DEFAULT '' NOT NULL,
  contentEditor varchar(32) DEFAULT '' NOT NULL,
  location varchar(255) NOT NULL default '/',
  password varchar(32) default NULL,
  acceptComment integer default 1 NOT NULL,
  acceptTrackback integer default 1 NOT NULL,
  published integer default 0 NOT NULL,
  created integer default 0 NOT NULL,
  modified integer default 0 NOT NULL,
  comments integer default 0 NOT NULL,
  trackbacks integer default 0 NOT NULL,
  PRIMARY KEY (blogid, id, draft, category, published)
) [##_charset_##];
CREATE INDEX Entries_visibility_idx ON [##_dbPrefix_##]Entries (visibility);
CREATE INDEX Entries_userid_idx ON [##_dbPrefix_##]Entries (userid);
CREATE INDEX Entries_published_idx ON [##_dbPrefix_##]Entries (published);
CREATE INDEX Entries_id_category_visibility_idx ON [##_dbPrefix_##]Entries (id, category, visibility);
CREATE INDEX Entries_blogid_published_idx ON [##_dbPrefix_##]Entries (blogid, published);
CREATE TABLE [##_dbPrefix_##]EntriesArchive (
  blogid integer default 0 NOT NULL,
  userid integer default 0 NOT NULL,
  id integer NOT NULL,
  visibility integer default 0 NOT NULL,
  category integer default 0 NOT NULL,
  title varchar(255) default '' NOT NULL,
  slogan varchar(255) default '' NOT NULL,
  content varchar NOT NULL,
  contentFormatter varchar(32) DEFAULT '' NOT NULL,
  contentEditor varchar(32) DEFAULT '' NOT NULL,
  location varchar(255) NOT NULL default '/',
  password varchar(32) default NULL,
  created integer default 0 NOT NULL,
  PRIMARY KEY (blogid, id, created)
) [##_charset_##];
CREATE INDEX EntriesArchive_visibility_idx ON [##_dbPrefix_##]EntriesArchive (visibility);
CREATE INDEX EntriesArchive_blogid__id_idx ON [##_dbPrefix_##]EntriesArchive (blogid, id);
CREATE INDEX EntriesArchive_userid_blogid_idx ON [##_dbPrefix_##]EntriesArchive (userid, blogid);
CREATE TABLE [##_dbPrefix_##]FeedGroupRelations (
  blogid integer default 0 NOT NULL,
  feed integer default 0 NOT NULL,
  groupId integer default 0 NOT NULL,
  PRIMARY KEY  (blogid,feed,groupId)
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
CREATE INDEX FeedItems_feed_idx ON [##_dbPrefix_##]FeedItems (feed);
CREATE INDEX FeedItems_written_idx ON [##_dbPrefix_##]FeedItems (written);
CREATE INDEX FeedItems_permalink_idx ON [##_dbPrefix_##]FeedItems (permalink);
CREATE TABLE [##_dbPrefix_##]FeedReads (
  blogid integer default 0 NOT NULL,
  item integer default 0 NOT NULL,
  PRIMARY KEY  (blogid,item)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedSettings (
  blogid integer default 0 NOT NULL,
  updateCycle integer NOT NULL default '120',
  feedLife integer NOT NULL default '30',
  loadImage integer default 1 NOT NULL,
  allowScript integer NOT NULL default '2',
  newWindow integer default 1 NOT NULL,
  PRIMARY KEY  (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedStarred (
  blogid integer default 0 NOT NULL,
  item integer default 0 NOT NULL,
  PRIMARY KEY  (blogid,item)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Feeds (
  id integer default 1 NOT NULL,
  xmlURL varchar(255) default '' NOT NULL,
  blogURL varchar(255) default '' NOT NULL,
  title varchar(255) default '' NOT NULL,
  description varchar(255) default '' NOT NULL,
  language varchar(5) NOT NULL default 'en-US',
  modified integer default 0 NOT NULL,
  PRIMARY KEY  (id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Filters (
  id integer default 1 NOT NULL,
  blogid integer default 0 NOT NULL,
  type varchar(11) NOT NULL default 'content',
  pattern varchar(255) default '' NOT NULL,
  PRIMARY KEY (id)
) [##_charset_##];
CREATE UNIQUE INDEX Filters_blogid_type_pattern_idx ON [##_dbPrefix_##]Filters (blogid, type, pattern);
CREATE TABLE [##_dbPrefix_##]Links (
  pid integer default 0 NOT NULL,
  blogid integer default 0 NOT NULL,
  id integer default 0 NOT NULL,
  category integer default 0 NOT NULL,
  name varchar(255) default '' NOT NULL,
  url varchar(255) default '' NOT NULL,
  rss varchar(255) default '' NOT NULL,
  written integer default 0 NOT NULL,
  visibility integer NOT NULL default '2',
  xfn varchar(128) default '' NOT NULL,
  PRIMARY KEY (pid)
) [##_charset_##];
CREATE UNIQUE INDEX Links_blogid_url_idx ON [##_dbPrefix_##]Links (blogid, url);
CREATE TABLE [##_dbPrefix_##]LinkCategories (
  pid integer default 0 NOT NULL,
  blogid integer default 0 NOT NULL,
  id integer default 0 NOT NULL,
  name varchar(128) NOT NULL,
  priority integer default 0 NOT NULL,
  visibility integer NOT NULL default '2',
  PRIMARY KEY (pid)
) [##_charset_##];
CREATE UNIQUE INDEX LinkCategories_blogid_id_idx ON [##_dbPrefix_##]LinkCategories (blogid, id);
CREATE TABLE [##_dbPrefix_##]OpenIDUsers (
  blogid integer default 0 NOT NULL,
  openid varchar(128) NOT NULL,
  delegatedid varchar(128) default NULL,
  firstLogin integer default NULL,
  lastLogin integer default NULL,
  loginCount integer default NULL,
  data text,
  PRIMARY KEY  (blogid,openid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]PageCacheLog (
  blogid integer default 0 NOT NULL,
  name varchar(255) default '' NOT NULL,
  value varchar NOT NULL,
  PRIMARY KEY (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Plugins (
  blogid integer default 0 NOT NULL,
  name varchar(255) default '' NOT NULL,
  settings text,
  PRIMARY KEY  (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]RefererLogs (
  blogid integer default 0 NOT NULL,
  host varchar(64) default '' NOT NULL,
  url varchar(255) default '' NOT NULL,
  referred integer NOT NULL default 0
) [##_charset_##];
CREATE INDEX RefererLogs_blogid_referred_idx ON [##_dbPrefix_##]RefererLogs (blogid, referred);
CREATE TABLE [##_dbPrefix_##]RefererStatistics (
  blogid integer default 0 NOT NULL,
  host varchar(64) default '' NOT NULL,
  count integer default 0 NOT NULL,
  PRIMARY KEY  (blogid,host)
) [##_charset_##];
CREATE INDEX RefererStatistics_blogid_count_idx ON [##_dbPrefix_##]RefererStatistics (blogid, count);
CREATE TABLE [##_dbPrefix_##]ReservedWords (
  word varchar(16) default '' NOT NULL,
  PRIMARY KEY  (word)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]ServiceSettings (
  name varchar(32) default '' NOT NULL,
  value varchar NOT NULL,
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
  data varchar default NULL,
  server varchar(64) default '' NOT NULL,
  request varchar(255) default '' NOT NULL,
  referer varchar(255) default '' NOT NULL,
  timer float default 0 NOT NULL,
  created integer default 0 NOT NULL,
  updated integer default 0 NOT NULL,
  PRIMARY KEY  (id,address)
) [##_charset_##];
CREATE INDEX Sessions_updated_idx ON [##_dbPrefix_##]Sessions (updated);
CREATE TABLE [##_dbPrefix_##]SkinSettings (
  blogid integer default 0 NOT NULL,
  skin varchar(32) NOT NULL default 'coolant',
  entriesOnRecent integer NOT NULL default '10',
  commentsOnRecent integer NOT NULL default '10',
  commentsOnGuestbook integer NOT NULL default '5',
  archivesOnPage integer NOT NULL default '5',
  tagsOnTagbox integer NOT NULL default '10',
  tagboxAlign integer default 1 NOT NULL,
  trackbacksOnRecent integer NOT NULL default '5',
  expandComment integer default 1 NOT NULL,
  expandTrackback integer default 1 NOT NULL,
  recentNoticeLength integer NOT NULL default '30',
  recentEntryLength integer NOT NULL default '30',
  recentCommentLength integer NOT NULL default '30',
  recentTrackbackLength integer NOT NULL default '30',
  linkLength integer NOT NULL default '30',
  showListOnCategory integer default 1 NOT NULL,
  showListOnArchive integer default 1 NOT NULL,
  showListOnTag integer default 1 NOT NULL,
  showListOnAuthor integer default 1 NOT NULL,
  showListOnSearch integer default 1 NOT NULL,
  tree varchar(32) NOT NULL default 'base',
  colorOnTree varchar(6) NOT NULL default '000000',
  bgColorOnTree varchar(6) default '' NOT NULL,
  activeColorOnTree varchar(6) NOT NULL default 'FFFFFF',
  activeBgColorOnTree varchar(6) NOT NULL default '00ADEF',
  labelLengthOnTree integer NOT NULL default '30',
  showValueOnTree integer default 1 NOT NULL,
  PRIMARY KEY  (blogid)
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
CREATE TABLE [##_dbPrefix_##]TrackbackLogs (
  blogid integer default 0 NOT NULL,
  id integer NOT NULL,
  entry integer default 0 NOT NULL,
  url varchar(255) default '' NOT NULL,
  written integer default 0 NOT NULL,
  PRIMARY KEY  (blogid, entry, id)
) [##_charset_##];
CREATE UNIQUE INDEX TrackbackLogs_blogid_id_idx ON [##_dbPrefix_##]TrackbackLogs (blogid, id);
CREATE TABLE [##_dbPrefix_##]Trackbacks (
  id integer NOT NULL,
  blogid integer default 0 NOT NULL,
  entry integer default 0 NOT NULL,
  url varchar(255) default '' NOT NULL,
  writer integer default NULL,
  site varchar(255) default '' NOT NULL,
  subject varchar(255) default '' NOT NULL,
  excerpt varchar(255) default '' NOT NULL,
  ip varchar(15) default '' NOT NULL,
  written integer default 0 NOT NULL,
  isFiltered integer default 0 NOT NULL,
  PRIMARY KEY (blogid, id)
) [##_charset_##];
CREATE INDEX Trackbacks_isFiltered_idx ON [##_dbPrefix_##]Trackbacks (isFiltered);
CREATE INDEX Trackbacks_blogid_isFiltered_written_idx ON [##_dbPrefix_##]Trackbacks (blogid, isFiltered, written);
CREATE TABLE [##_dbPrefix_##]Users (
  userid integer default 1 NOT NULL,
  loginid varchar(64) default '' NOT NULL,
  password varchar(32) default NULL,
  name varchar(32) default '' NOT NULL,
  created integer default 0 NOT NULL,
  lastLogin integer default 0 NOT NULL,
  host integer default 0 NOT NULL,
  PRIMARY KEY  (userid)
) [##_charset_##];
CREATE UNIQUE INDEX Users_loginid_idx ON [##_dbPrefix_##]Users (loginid);
CREATE UNIQUE INDEX Users_name_idx ON [##_dbPrefix_##]Users (name);
CREATE TABLE [##_dbPrefix_##]UserSettings (
  userid integer default 0 NOT NULL,
  name varchar(32) default '' NOT NULL,
  value varchar NOT NULL,
  PRIMARY KEY (userid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]XMLRPCPingSettings (
  blogid integer default 0 NOT NULL,
  url varchar(255) default '' NOT NULL,
  type varchar(32) NOT NULL default 'xmlrpc',
  PRIMARY KEY (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Teamblog (
  blogid integer default 1 NOT NULL,
  userid integer default 1 NOT NULL,
  acl integer default 0 NOT NULL,
  created integer default 0 NOT NULL,
  lastLogin integer default 0 NOT NULL,
  PRIMARY KEY (blogid,userid)
) [##_charset_##];
