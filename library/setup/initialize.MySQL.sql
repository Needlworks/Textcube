CREATE TABLE [##_dbPrefix_##]Attachments (
  blogid int(11) NOT NULL default '0',
  parent int(11) NOT NULL default '0',
  name varchar(32) NOT NULL default '',
  label varchar(64) NOT NULL default '',
  mime varchar(32) NOT NULL default '',
  size int(11) NOT NULL default '0',
  width int(11) NOT NULL default '0',
  height int(11) NOT NULL default '0',
  attached int(11) NOT NULL default '0',
  downloads int(11) NOT NULL default '0',
  enclosure tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]BlogSettings (
  blogid int(11) NOT NULL default '0',
  name varchar(32) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY (blogid, name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]BlogStatistics (
  blogid int(11) NOT NULL default '0',
  visits int(11) NOT NULL default '0',
  PRIMARY KEY  (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Categories (
  blogid int(11) NOT NULL default '0',
  id int(11) NOT NULL,
  parent int(11) default NULL,
  name varchar(127) NOT NULL default '',
  priority int(11) NOT NULL default '0',
  entries int(11) NOT NULL default '0',
  entriesInLogin int(11) NOT NULL default '0',
  label varchar(255) NOT NULL default '',
  visibility tinyint(4) NOT NULL default '2',
  bodyid varchar(20) default NULL,
  PRIMARY KEY (blogid,id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Comments (
  blogid int(11) NOT NULL default '0',
  replier int(11) default NULL,
  id int(11) NOT NULL,
  openid varchar(128) NOT NULL default '',
  entry int(11) NOT NULL default '0',
  parent int(11) default NULL,
  name varchar(80) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  homepage varchar(80) NOT NULL default '',
  secret int(1) NOT NULL default '0',
  comment text NOT NULL,
  ip varchar(15) NOT NULL default '',
  written int(11) NOT NULL default '0',
  isfiltered int(11) NOT NULL default '0',
  PRIMARY KEY  (blogid, id),
  KEY blogid (blogid),
  KEY entry (entry),
  KEY parent (parent),
  KEY isfiltered (isfiltered)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]CommentsNotified (
  blogid int(11) NOT NULL default '0',
  replier int(11) default NULL,
  id int(11) NOT NULL,
  entry int(11) NOT NULL default '0',
  parent int(11) default NULL,
  name varchar(80) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  homepage varchar(80) NOT NULL default '',
  secret int(1) NOT NULL default '0',
  comment text NOT NULL,
  ip varchar(15) NOT NULL default '',
  written int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  siteid int(11) NOT NULL default '0',
  isnew int(1) NOT NULL default '1',
  url varchar(255) NOT NULL default '',
  remoteid int(11) NOT NULL default '0',
  entrytitle varchar(255) NOT NULL default '',
  entryurl varchar(255) NOT NULL default '',
  PRIMARY KEY  (blogid, id),
  KEY blogid (blogid),
  KEY entry (entry)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]CommentsNotifiedQueue (
  blogid int(11) NOT NULL default '0',
  id int(11) NOT NULL,
  commentid int(11) NOT NULL default '0',
  sendstatus int(1) NOT NULL default '0',
  checkdate int(11) NOT NULL default '0',
  written int(11) NOT NULL default '0',
  PRIMARY KEY  (blogid, id),
  UNIQUE KEY commentid (commentid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]CommentsNotifiedSiteInfo (
  id int(11) NOT NULL,
  title varchar(255) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY url (url),
  UNIQUE KEY id (id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]DailyStatistics (
  blogid int(11) NOT NULL default '0',
  date int(11) NOT NULL default '0',
  visits int(11) NOT NULL default '0',
  PRIMARY KEY  (blogid,date)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Entries (
  blogid int(11) NOT NULL default '0',
  userid int(11) NOT NULL default '0',
  id int(11) NOT NULL,
  draft tinyint(1) NOT NULL default '0',
  visibility tinyint(4) NOT NULL default '0',
  starred tinyint(4) NOT NULL default '1',
  category int(11) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  slogan varchar(255) NOT NULL default '',
  content mediumtext NOT NULL,
  contentformatter varchar(32) DEFAULT '' NOT NULL,
  contenteditor varchar(32) DEFAULT '' NOT NULL,
  location varchar(255) NOT NULL default '/',
  password varchar(32) default NULL,
  acceptcomment int(1) NOT NULL default '1',
  accepttrackback int(1) NOT NULL default '1',
  published int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  comments int(11) NOT NULL default '0',
  trackbacks int(11) NOT NULL default '0',
  PRIMARY KEY (blogid, id, draft, category, published),
  KEY visibility (visibility),
  KEY userid (userid),
  KEY published (published),
  KEY id (id, category, visibility),
  KEY blogid (blogid, published)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]EntriesArchive (
  blogid int(11) NOT NULL default '0',
  userid int(11) NOT NULL default '0',
  id int(11) NOT NULL,
  visibility tinyint(4) NOT NULL default '0',
  category int(11) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  slogan varchar(255) NOT NULL default '',
  content mediumtext NOT NULL,
  contentformatter varchar(32) DEFAULT '' NOT NULL,
  contenteditor varchar(32) DEFAULT '' NOT NULL,
  location varchar(255) NOT NULL default '/',
  password varchar(32) default NULL,
  created int(11) NOT NULL default '0',
  PRIMARY KEY (blogid, id, created),
  KEY visibility (visibility),
  KEY blogid (blogid, id),
  KEY userid (userid, blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedGroupRelations (
  blogid int(11) NOT NULL default '0',
  feed int(11) NOT NULL default '0',
  groupid int(11) NOT NULL default '0',
  PRIMARY KEY  (blogid,feed,groupid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedGroups (
  blogid int(11) NOT NULL default '0',
  id int(11) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  PRIMARY KEY  (blogid,id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedItems (
  id int(11) NOT NULL auto_increment,
  feed int(11) NOT NULL default '0',
  author varchar(255) NOT NULL default '',
  permalink varchar(255) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  description text NOT NULL,
  tags varchar(255) NOT NULL default '',
  enclosure varchar(255) NOT NULL default '',
  written int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY feed (feed),
  KEY written (written),
  KEY permalink (permalink)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedReads (
  blogid int(11) NOT NULL default '0',
  item int(11) NOT NULL default '0',
  PRIMARY KEY  (blogid,item)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedSettings (
  blogid int(11) NOT NULL default '0',
  updatecycle int(11) NOT NULL default '120',
  feedlife int(11) NOT NULL default '30',
  loadimage int(11) NOT NULL default '1',
  allowscript int(11) NOT NULL default '2',
  newwindow int(11) NOT NULL default '1',
  PRIMARY KEY  (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]FeedStarred (
  blogid int(11) NOT NULL default '0',
  item int(11) NOT NULL default '0',
  PRIMARY KEY  (blogid,item)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Feeds (
  id int(11) NOT NULL auto_increment,
  xmlurl varchar(255) NOT NULL default '',
  blogURL varchar(255) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  language varchar(5) NOT NULL default 'en-US',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Filters (
  id int(11) NOT NULL auto_increment,
  blogid int(11) NOT NULL default '0',
  type enum('content','ip','name','url') NOT NULL default 'content',
  pattern varchar(255) NOT NULL default '',
  PRIMARY KEY (id),
  UNIQUE KEY blogid (blogid, type, pattern)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Links (
  pid int(11) NOT NULL default '0',
  blogid int(11) NOT NULL default '0',
  id int(11) NOT NULL default '0',
  category int(11) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  rss varchar(255) NOT NULL default '',
  written int(11) NOT NULL default '0',
  visibility tinyint(4) NOT NULL default '2',
  xfn varchar(128) NOT NULL default '',
  PRIMARY KEY (pid),
  UNIQUE KEY blogid (blogid,url)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]LinkCategories (
  pid int(11) NOT NULL default '0',
  blogid int(11) NOT NULL default '0',
  id int(11) NOT NULL default '0',
  name varchar(128) NOT NULL,
  priority int(11) NOT NULL default '0',
  visibility tinyint(4) NOT NULL default '2',
  PRIMARY KEY (pid),
  UNIQUE KEY blogid (blogid, id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]OpenIDUsers (
  blogid int(11) NOT NULL default '0',
  openid varchar(128) NOT NULL,
  delegatedid varchar(128) default NULL,
  firstlogin int(11) default NULL,
  lastlogin int(11) default NULL,
  logincount int(11) default NULL,
  data text,
  PRIMARY KEY  (blogid,openid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]PageCacheLog (
  blogid int(11) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Plugins (
  blogid int(11) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  settings text,
  PRIMARY KEY  (blogid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]RefererLogs (
  blogid int(11) NOT NULL default '0',
  host varchar(64) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  referred int(11) NOT NULL default '0'
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]RefererStatistics (
  blogid int(11) NOT NULL default '0',
  host varchar(64) NOT NULL default '',
  count int(11) NOT NULL default '0',
  PRIMARY KEY  (blogid,host)
) [##_charset_##];
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
  blogid int(11) NOT NULL default '0',
  PRIMARY KEY  (id,address,blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Sessions (
  id varchar(32) NOT NULL default '',
  address varchar(15) NOT NULL default '',
  userid int(11) default NULL,
  preexistence int(11) default NULL,
  data text default NULL,
  server varchar(64) NOT NULL default '',
  request varchar(255) NOT NULL default '',
  referer varchar(255) NOT NULL default '',
  timer float NOT NULL default '0',
  created int(11) NOT NULL default '0',
  updated int(11) NOT NULL default '0',
  PRIMARY KEY  (id,address),
  KEY updated (updated)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]SkinSettings (
  blogid int(11) NOT NULL default '0',
  skin varchar(32) NOT NULL default 'coolant',
  entriesonrecent int(11) NOT NULL default '10',
  commentsonrecent int(11) NOT NULL default '10',
  commentsonguestbook int(11) NOT NULL default '5',
  archivesonpage int(11) NOT NULL default '5',
  tagsontagbox tinyint(4) NOT NULL default '10',
  tagboxalign tinyint(4) NOT NULL default '1',
  trackbacksonrecent int(11) NOT NULL default '5',
  expandcomment int(1) NOT NULL default '1',
  expandtrackback int(1) NOT NULL default '1',
  recentnoticelength int(11) NOT NULL default '30',
  recententrylength int(11) NOT NULL default '30',
  recentcommentlength int(11) NOT NULL default '30',
  recenttrackbacklength int(11) NOT NULL default '30',
  linklength int(11) NOT NULL default '30',
  showlistoncategory tinyint(4) NOT NULL default '1',
  showlistonarchive tinyint(4) NOT NULL default '1',
  showlistontag tinyint(4) NOT NULL default '1',
  showlistonauthor tinyint(4) NOT NULL default '1',
  showlistonsearch int(1) NOT NULL default '1',
  tree varchar(32) NOT NULL default 'base',
  colorontree varchar(6) NOT NULL default '000000',
  bgcolorontree varchar(6) NOT NULL default '',
  activecolorontree varchar(6) NOT NULL default 'FFFFFF',
  activebgcolorontree varchar(6) NOT NULL default '00ADEF',
  labellengthontree int(11) NOT NULL default '30',
  showvalueontree int(1) NOT NULL default '1',
  PRIMARY KEY  (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]TagRelations (
  blogid int(11) NOT NULL default '0',
  tag int(11) NOT NULL default '0',
  entry int(11) NOT NULL default '0',
  PRIMARY KEY  (blogid, tag, entry),
  KEY blogid (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Tags (
  id int(11) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY name (name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]TrackbackLogs (
  blogid int(11) NOT NULL default '0',
  id int(11) NOT NULL,
  entry int(11) NOT NULL default '0',
  url varchar(255) NOT NULL default '',
  written int(11) NOT NULL default '0',
  PRIMARY KEY  (blogid, entry, id),
  UNIQUE KEY id (blogid, id)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Trackbacks (
  id int(11) NOT NULL,
  blogid int(11) NOT NULL default '0',
  entry int(11) NOT NULL default '0',
  url varchar(255) NOT NULL default '',
  writer int(11) default NULL,
  site varchar(255) NOT NULL default '',
  subject varchar(255) NOT NULL default '',
  excerpt varchar(255) NOT NULL default '',
  ip varchar(15) NOT NULL default '',
  written int(11) NOT NULL default '0',
  isfiltered int(11) NOT NULL default '0',
  PRIMARY KEY (blogid, id),
  KEY isfiltered (isfiltered),
  KEY blogid (blogid, isfiltered, written)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Users (
  userid int(11) NOT NULL auto_increment,
  loginid varchar(64) NOT NULL default '',
  password varchar(32) default NULL,
  name varchar(32) NOT NULL default '',
  created int(11) NOT NULL default '0',
  lastlogin int(11) NOT NULL default '0',
  host int(11) NOT NULL default '0',
  PRIMARY KEY  (userid),
  UNIQUE KEY loginid (loginid),
  UNIQUE KEY name (name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]UserSettings (
  userid int(11) NOT NULL default '0',
  name varchar(32) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY (userid,name)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]XMLRPCPingSettings (
  blogid int(11) NOT NULL default 0,
  url varchar(255) NOT NULL default '',
  type varchar(32) NOT NULL default 'xmlrpc',
  PRIMARY KEY (blogid)
) [##_charset_##];
CREATE TABLE [##_dbPrefix_##]Teamblog (
  blogid int(11) NOT NULL default 1,
  userid int(11) NOT NULL default 1,
  acl int(11) NOT NULL default 0,
  created int(11) NOT NULL default 0,
  lastlogin int(11) NOT NULL default 0,
  PRIMARY KEY (blogid,userid)
) [##_charset_##];
