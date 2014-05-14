/* ATTENTION: You don't need to run or use this file!  The upgrade.php script does everything for you! */


/******************************************************************************/
--- Adding new board specific features.
/******************************************************************************/

---# Implementing board redirects.
ALTER TABLE {$db_prefix}boards
ADD COLUMN redirect varchar(255) NOT NULL default '';
---#

---# Implementing deny_member_groups.
ALTER TABLE {$db_prefix}boards
ADD COLUMN deny_member_groups varchar(255) NOT NULL default '';
---#

---# Implementing show_rssicon.
ALTER TABLE {$db_prefix}boards
ADD COLUMN show_rssicon tinyint(1) unsigned NOT NULL default '0';
---#

---# Implementing last_pruned.
ALTER TABLE {$db_prefix}boards
ADD COLUMN last_pruned int(10) unsigned NOT NULL default '0';
---#

---# Implementing prune_frequency.
ALTER TABLE {$db_prefix}boards
ADD COLUMN prune_frequency int(10) unsigned NOT NULL default '0';
---#

---# Implementing prune_frequency.
ALTER TABLE {$db_prefix}boards
ADD COLUMN member_groups_deny varchar(255) NOT NULL default '';
---#

/******************************************************************************/
--- Log Online Updates for geoIP
/******************************************************************************/

---# Implementing longitude .
ALTER TABLE {$db_prefix}log_online 
ADD COLUMN longitude decimal(18,15) DEFAULT '0.000000000000000';
---#

---# Implementing latitude.
ALTER TABLE {$db_prefix}log_online 
ADD COLUMN latitude decimal(18,15) DEFAULT '0.000000000000000';
---#

---# Implementing city 
ALTER TABLE {$db_prefix}log_online 
ADD COLUMN city varchar(255) NOT NULL;
---#

---# Implementing cc 
ALTER TABLE {$db_prefix}log_online 
ADD COLUMN cc char(2) NOT NULL;
---#

/******************************************************************************/
--- Adding new member specific features.
/******************************************************************************/

---# Implementing skype  
ALTER TABLE {$db_prefix}members 
ADD COLUMN skype varchar(255) NOT NULL default '';
---#

---# Implementing facebook   
ALTER TABLE {$db_prefix}members 
ADD COLUMN facebook varchar(155) NOT NULL default '';
---#

---# Implementing twitter    
ALTER TABLE {$db_prefix}members 
ADD COLUMN twitter varchar(155) NOT NULL default '';
---#

---# Implementing linkedin    
ALTER TABLE {$db_prefix}members 
ADD COLUMN linkedin varchar(155) NOT NULL default '';
---#

---# Implementing googleplus    
ALTER TABLE {$db_prefix}members 
ADD COLUMN googleplus varchar(155) NOT NULL default '';
---#

---# Implementing myspace    
ALTER TABLE {$db_prefix}members 
ADD COLUMN myspace varchar(155) NOT NULL default '';
---#

---# Implementing youtube     
ALTER TABLE {$db_prefix}members 
ADD COLUMN youtube varchar(155) NOT NULL default '';
---#

---# Implementing deviantart    
ALTER TABLE {$db_prefix}members 
ADD COLUMN deviantart varchar(155) NOT NULL default '';
---#

---# Implementing pinterest    
ALTER TABLE {$db_prefix}members 
ADD COLUMN pinterest varchar(155) NOT NULL default '';
---#

---# Implementing passwd_expiredate    
ALTER TABLE {$db_prefix}members 
ADD COLUMN passwd_expiredate int(10) unsigned NOT NULL default '0';
---#

---# Implementing email_bounced     
ALTER TABLE {$db_prefix}members 
ADD COLUMN email_bounced tinyint(4) NOT NULL default '0';
---#

---# Implementing email_mentions    
ALTER TABLE {$db_prefix}members 
ADD COLUMN email_mentions tinyint(4) NOT NULL default '0';
---#

---# Implementing unread_mentions    
ALTER TABLE {$db_prefix}members 
ADD COLUMN unread_mentions int(11) NOT NULL default '1';
---#


/******************************************************************************/
--- Adding new forum settings.
/******************************************************************************/

---# Resetting settings_updated.
REPLACE INTO {$db_prefix}settings
	(variable, value)
VALUES
	('settings_updated', '0'),
	('disableTemplateEval', '1'),
	('disableHostnameLookup', '1'),
	('countChildPosts', '1'),
	('custom_avatar_enabled', '1'),
	('custom_avatar_dir', '{$boarddir}/useravatars'),
	('custom_avatar_url', '{$boardurl}/useravatars'),
	('enableReportPM', '1'),
	('approveAccountDeletion', '1'),
	('messageIcons_enable', '1'),
	('smiley_enable', '1'),
	('solvemedia_enabled', '0'),
	('solvemedia_publickey', ''),
	('solvemedia_privatekey', ''),
	('solvemedia_hashkey', ''),
	('solvemedia_theme', 'white'),
	('solvemedia_lang', 'en'),
	('stopforumspam_enabled', '1'),
	('stopforumspam_checkip', '1'),
	('stopforumspam_checkemail', '1'),
	('stopforumspam_checkusername', '1'),
	('recaptcha_enabled', '0'),
	('recaptcha_publickey', ''),
	('recaptcha_privatekey', ''),
	('recaptcha_theme', 'white'),
	('recaptcha_lang', 'en'),
	('ls_securehash_expire_minutes', '30'),
	('ls_allowed_login_attempts', '5'),
	('ls_allowed_login_attempts_mins', '60'),
	('ls_login_retry_minutes', '15'),
	('ls_allow_ip_security', '0'),
	('ls_send_mail_failed_login', '1'),
	('apmt_taskFrequency', '15'),
	('apmt_numberOfBoards', '5'),
	('oasl_api_handler', 'curl'),
	('oasl_api_port', '443'),
	('oasl_api_subdomain', ''),
	('oasl_api_key', ''),
	('oasl_api_secret', ''),
	('oasl_settings_login_caption', 'Login with your social network'),
	('oasl_settings_registration_caption', 'Or simply register using your social network account'),
	('oasl_settings_profile_caption', 'Social Networks'),
	('oasl_settings_profile_desc', 'Link your forum account to one or more social network accounts.'),
	('oasl_settings_link_accounts', '1'),
	('oasl_settings_use_avatars', '1'),
	('oasl_providers', 'facebook,twitter,google,linkedin,yahoo,github,foursquare,youtube,skyrock,openid,wordpress,hyves,paypal,livejournal,steam,windowslive,blogger,disqus,stackexchange,vkontakte,odnoklassniki,mailru'),
	('oasl_enabled_providers', 'facebook,twitter,google,linkedin'),
	('oasl_settings_ask_for_email','1'),
	('relatedTopicsEnabled', '1'),
	('relatedTopicsCount', '5'),
	('relatedIndex', 'fulltext'),
	('relatedIgnoredboards', ''),
	('posthistoryEnabled','1'),
	('pretty_enable_filters', '0'),
	('pretty_bufferusecache', '0'),
	('anti_spam_links_nolinks', '0'),
	('anti_spam_links_newbielinks', '0'),
	('anti_spam_links_nofollowlinks', '0'),
	('anti_spam_links_guests', '0'),
	('ca_enabled', '1'),
	('ca_cache', ''),
	('ca_menu_cache', 'a:0:{}'),
	('tag_enabled', '1'),
	('tag_required', '0'),
	('tag_board_disabled', ''),
	('tag_max_per_topic', '8'),
	('tag_min_length', '3'),
	('tag_max_length', '15'),
	('tag_max_suggested', '4'),
	('tag_enabled_related_topics', '1'),
	('tag_max_related_topics', '5'),
	('tag_cloud_enabled', '1'),
	('tag_cloud_limit', '30'),
	('tag_cloud_smallest_color', ''),
	('tag_cloud_smallest_opacity', ''),
	('tag_cloud_smallest_fontsize', ''),
	('tag_cloud_small_color', ''),
	('tag_cloud_small_opacity', ''),
	('tag_cloud_small_fontsize', ''),
	('tag_cloud_medium_color', ''),
	('tag_cloud_medium_opacity', ''),
	('tag_cloud_medium_fontsize', ''),
	('tag_cloud_large_color', ''),
	('tag_cloud_large_opacity', ''),
	('tag_cloud_large_fontsize', ''),
	('tag_cloud_largest_color', ''),
	('tag_cloud_largest_opacity', ''),
	('tag_cloud_largest_fontsize', ''),
	('tag_list_enabled', '1'),
	('tag_list_show_count', '1'),
	('tag_search_paginate_limit', '15'),
	('search_min_char', '2'),
	('enable_allow_deny', '1'),
	('rp_mod_enable', '0'),
	('rp_mod_enable_calendar', '0'),
	('guests_sendtopic_require_captcha','1'),
	('descriptivelinks_enabled', '0'),
	('descriptivelinks_title_url', '1'),
	('descriptivelinks_title_internal', '1'),
	('descriptivelinks_title_bbcurl', '1'),
	('descriptivelinks_title_url_count', '5'),
	('descriptivelinks_title_url_generic', 'home,index,page title,default,login,logon,welcome'),
	('descriptivelinks_title_url_length', '80'),
	('mentions_email_default', '1'),
	('mentions_remove_days', '7');
---#

---# Adjusting calendar maximum year...
---{
if (!isset($modSettings['cal_maxyear']) || $modSettings['cal_maxyear'] == '2010')
{
	upgrade_query("
		REPLACE INTO {$db_prefix}settings
			(variable, value)
		VALUES
			('cal_maxyear', '2030')");
}
---}
---#





/******************************************************************************/
--- Adding support for IPv6...
/******************************************************************************/

---# Adding new columns to ban items...
ALTER TABLE {$db_prefix}ban_items
ADD COLUMN ip_low5 smallint(255) unsigned NOT NULL DEFAULT '0',
ADD COLUMN ip_high5 smallint(255) unsigned NOT NULL DEFAULT '0',
ADD COLUMN ip_low6 smallint(255) unsigned NOT NULL DEFAULT '0',
ADD COLUMN ip_high6 smallint(255) unsigned NOT NULL DEFAULT '0',
ADD COLUMN ip_low7 smallint(255) unsigned NOT NULL DEFAULT '0',
ADD COLUMN ip_high7 smallint(255) unsigned NOT NULL DEFAULT '0',
ADD COLUMN ip_low8 smallint(255) unsigned NOT NULL DEFAULT '0',
ADD COLUMN ip_high8 smallint(255) unsigned NOT NULL DEFAULT '0';
---#

---# Changing existing columns to ban items...
ALTER TABLE {$db_prefix}ban_items
CHANGE ip_low1 ip_low1 smallint(255) unsigned NOT NULL DEFAULT '0',
CHANGE ip_high1 ip_high1 smallint(255) unsigned NOT NULL DEFAULT '0',
CHANGE ip_low2 ip_low2 smallint(255) unsigned NOT NULL DEFAULT '0',
CHANGE ip_high2 ip_high2 smallint(255) unsigned NOT NULL DEFAULT '0',
CHANGE ip_low3 ip_low3 smallint(255) unsigned NOT NULL DEFAULT '0',
CHANGE ip_high3 ip_high3 smallint(255) unsigned NOT NULL DEFAULT '0',
CHANGE ip_low4 ip_low4 smallint(255) unsigned NOT NULL DEFAULT '0',
CHANGE ip_high4 ip_high4 smallint(255) unsigned NOT NULL DEFAULT '0';
---#



/******************************************************************************/
--- Adding Scheduled Tasks Data.
/******************************************************************************/


---# Populating Scheduled Task Table...
INSERT IGNORE INTO {$db_prefix}scheduled_tasks
	(id_task, next_time, time_offset, time_regularity, time_unit, disabled, task)
VALUES
	(1, 0, 0, 2, 'h', 0, 'approval_notification'),
	(2, 0, 0, 7, 'd', 0, 'auto_optimize'),
	(3, 0, 60, 1, 'd', 0, 'daily_maintenance'),
	(5, 0, 0, 1, 'd', 0, 'daily_digest'),
	(6, 0, 0, 1, 'w', 0, 'weekly_digest'),
	(8, 0, 0, 1, 'd', 1, 'birthdayemails'),
	(9, 0, 0, 1, 'w', 0, 'weekly_maintenance'),
	(10, 0, 120, 1, 'd', 1, 'paid_subscriptions'),
	(11, 0, 0, 15, 'm', 0, 'apmt_prunetopics_task'),
	(12,1317434580, 97380, 5, 'w', 0, 'geoIP');
---#



/******************************************************************************/
--- Installing new default theme...
/******************************************************************************/


---#
---{
// Update the name of the default theme in the database.
		upgrade_query("
			UPDATE {$db_prefix}themes
			SET value = 'ezForum Default Theme - Curve'
			WHERE id_theme = 1
				AND variable = 'name'");
---}
---#





/******************************************************************************/
--- Create a repository for the javascript files from Simple Machines...
/******************************************************************************/

---# Add in the files to get from 

REPLACE INTO {$db_prefix}admin_info_files
	(id_file, filename, path, parameters, data, filetype)
VALUES
	(1, 'current-version.js', '/ezc/', 'version=%3$s', '', 'text/javascript'),
	(2, 'detailed-version.js', '/ezc/', 'language=%1$s&version=%3$s', '', 'text/javascript'),
	(3, 'latest-news.js', '/ezc/', 'language=%1$s&format=%2$s', '', 'text/javascript'),
	(4, 'latest-packages.js', '/ezc/', 'language=%1$s&version=%3$s', '', 'text/javascript'),
	(5, 'latest-smileys.js', '/ezc/', 'language=%1$s&version=%3$s', '', 'text/javascript'),
	(6, 'latest-support.js', '/ezc/', 'language=%1$s&version=%3$s', '', 'text/javascript'),
	(7, 'latest-themes.js', '/ezc/', 'language=%1$s&version=%3$s', '', 'text/javascript');

---#




/******************************************************************************/
--- Adding settings for attachments and avatars.
/******************************************************************************/

---# Add new security settings for attachments and avatars...
---{

// Don't do this if we've done this already.
if (!isset($modSettings['attachment_image_reencode']))
{
	// Enable image re-encoding by default.
	upgrade_query("
		REPLACE INTO {$db_prefix}settings
			(variable, value)
		VALUES
			('attachment_image_reencode', '1')");
}
if (!isset($modSettings['attachment_image_paranoid']))
{
	// Disable draconic checks by default.
	upgrade_query("
		REPLACE INTO {$db_prefix}settings
			(variable, value)
		VALUES
			('attachment_image_paranoid', '0')");
}
if (!isset($modSettings['avatar_reencode']))
{
	// Enable image re-encoding by default.
	upgrade_query("
		REPLACE INTO {$db_prefix}settings
			(variable, value)
		VALUES
			('avatar_reencode', '1')");
}
if (!isset($modSettings['avatar_paranoid']))
{
	// Disable draconic checks by default.
	upgrade_query("
		REPLACE INTO {$db_prefix}settings
			(variable, value)
		VALUES
			('avatar_paranoid', '0')");
}

---}
---#

---# Add other attachment settings...
---{
if (!isset($modSettings['attachment_thumb_png']))
{
	// Make image attachment thumbnail as PNG by default.
	upgrade_query("
		REPLACE INTO {$db_prefix}settings
			(variable, value)
		VALUES
			('attachment_thumb_png', '1')");
}

---}
---#


/******************************************************************************/
--- Geo IP tables...
/******************************************************************************/

---# Table structure for table `geoip_blocks`
CREATE TABLE IF NOT EXISTS {$db_prefix}geoip_blocks (
  locid int(10) unsigned NOT NULL,
  country char(2) NOT NULL,
  region char(2) NOT NULL,
  city varchar(255) NOT NULL,
  postalcode char(5) NOT NULL,
  latitude float NOT NULL,
  longitude float NOT NULL,
  dmacode int(10) unsigned NOT NULL,
  areacode int(10) unsigned NOT NULL,
  PRIMARY KEY (locid)
) ENGINE=MyISAM{$db_collation};
---#


---# Table structure for table `geoip_countries`
CREATE TABLE IF NOT EXISTS {$db_prefix}geoip_countries (
  ci tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  cc char(2) NOT NULL,
  cn varchar(255) NOT NULL,
  PRIMARY KEY (ci),
  KEY cc (cc)
) ENGINE=MyISAM{$db_collation};
---#

---# Table structure for table `geoip_ip`
CREATE TABLE IF NOT EXISTS {$db_prefix}geoip_ip (
  start int(10) unsigned NOT NULL,
  end int(10) unsigned NOT NULL,
  locid int(10) unsigned NOT NULL,
  KEY end (end)
) ENGINE=MyISAM{$db_collation};
---#

---# Table structure for table `geoip_regions`
CREATE TABLE IF NOT EXISTS {$db_prefix}geoip_regions (
  cc char(2) NOT NULL,
  rc char(2) NOT NULL,
  rn varchar(255) NOT NULL,
  KEY rc (rc),
  KEY cc (cc)
) ENGINE=MyISAM{$db_collation};
---#

/******************************************************************************/
--- Login Security tables...
/******************************************************************************/

---# Table structure for table `login_security`
CREATE TABLE IF NOT EXISTS {$db_prefix}login_security
(
id_member mediumint(8) unsigned NOT NULL, 
allowedips text,
lastfailedlogintime int(10) unsigned NOT NULL default '0',
lockedaccountuntiltime int(10) unsigned NOT NULL default '0',
secureloginhash tinytext,
secureloginhashexpiretime int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (id_member)
) ENGINE=MyISAM{$db_collation};
---#


---# Table structure for table `login_security_log`
CREATE TABLE IF NOT EXISTS {$db_prefix}login_security_log
(
id_log mediumint(8) NOT NULL auto_increment,
id_member mediumint(8) unsigned NOT NULL default '0',
date int(10) unsigned NOT NULL default '0',
ip tinytext,
PRIMARY KEY  (id_log) 
) ENGINE=MyISAM{$db_collation};
---#

/******************************************************************************/
--- member_logins  tables...
/******************************************************************************/

---# Table structure for table `member_logins`
CREATE TABLE IF NOT EXISTS {$db_prefix}member_logins (
  id_login int(10) NOT NULL auto_increment,
  id_member mediumint(8) NOT NULL default '0',
  time int(10) NOT NULL default '0',
  ip varchar(255) NOT NULL default '0',
  ip2 varchar(255) NOT NULL default '0',
  PRIMARY KEY (id_login),
  KEY id_member (id_member),
  KEY time (time)
) ENGINE=MyISAM{$db_collation};
---#



/******************************************************************************/
--- member_logins  tables...
/******************************************************************************/

---# Table structure for table `member_logins`
CREATE TABLE IF NOT EXISTS {$db_prefix}member_logins (
  id_login int(10) NOT NULL auto_increment,
  id_member mediumint(8) NOT NULL default '0',
  time int(10) NOT NULL default '0',
  ip varchar(255) NOT NULL default '0',
  ip2 varchar(255) NOT NULL default '0',
  PRIMARY KEY (id_login),
  KEY id_member (id_member),
  KEY time (time)
) ENGINE=MyISAM{$db_collation};
---#


/******************************************************************************/
--- Mentions System tables...
/******************************************************************************/

---# Table structure for table `log_mentions`
CREATE TABLE {$db_prefix}log_mentions (
  `id_post` int(11) NOT NULL,
  `id_member` int(11) NOT NULL,
  `id_mentioned` int(11) NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  `unseen` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_post`,`id_member`,`id_mentioned`)
) ENGINE=MyISAM{$db_collation};

---#

/******************************************************************************/
---  OneAll Social  tables...
/******************************************************************************/


---# Table structure for table `oasl_users`
CREATE TABLE IF NOT EXISTS {$db_prefix}oasl_users (
  id_oasl_user int(10) unsigned NOT NULL auto_increment,
  id_member int(10) unsigned NOT NULL default '0',
  user_token char(40) NOT NULL,
  PRIMARY KEY  (id_oasl_user),
  KEY id_member (id_member),
  UNIQUE KEY user_token (user_token)
) ENGINE=MyISAM{$db_collation};
---#

---# Table structure for table `oasl_identities`
CREATE TABLE IF NOT EXISTS {$db_prefix}oasl_identities (
  id_oasl_identity int(10) unsigned NOT NULL auto_increment,
  id_oasl_user int(10) unsigned NOT NULL default '0',
  identity_token char(40) NOT NULL,
  PRIMARY KEY  (id_oasl_identity),
  UNIQUE KEY identity_token (identity_token)
) ENGINE=MyISAM{$db_collation};
---#

/******************************************************************************/
---  Pretty Urls  tables...
/******************************************************************************/

---# Table structure for table `pretty_topic_urls`
CREATE TABLE IF NOT EXISTS  {$db_prefix}pretty_topic_urls
(
id_topic mediumint NOT NULL ,
pretty_url varchar(80) NOT NULL ,
PRIMARY KEY (id_topic),
UNIQUE pretty_url (pretty_url)) 
ENGINE=MyISAM{$db_collation};
---#

---# Table structure for table `pretty_urls_cache`
CREATE TABLE IF NOT EXISTS  {$db_prefix}pretty_urls_cache
(
url_id varchar(255) NOT NULL ,
replacement varchar(255) NOT NULL ,
PRIMARY KEY (url_id)) ENGINE=MyISAM{$db_collation};
---#

/******************************************************************************/
---  Profile Comments tables...
/******************************************************************************/

---# Table structure for table `profile_comments`
CREATE TABLE IF NOT EXISTS {$db_prefix}profile_comments (
  comment_id int(11) unsigned NOT NULL auto_increment,
  comment_profile int(11) unsigned NOT NULL,
  comment_poster_id int(11) unsigned NOT NULL,
  comment_poster varchar(50) NOT NULL,
  comment_title varchar(60) NOT NULL,
  comment_body text NOT NULL,
  PRIMARY KEY  (comment_id)
) ENGINE=MyISAM{$db_collation};
---#

/******************************************************************************/
---  Related Topics tables...
/******************************************************************************/

---# Table structure for table `related_topics`
CREATE TABLE IF NOT EXISTS  {$db_prefix}related_topics (
  id_topic_first int(10) unsigned NOT NULL,
  id_topic_second int(10) unsigned NOT NULL,
  score float unsigned NOT NULL,
  PRIMARY KEY  (id_topic_first,id_topic_second)
) ENGINE=MyISAM{$db_collation};
---#


---# Table structure for table `related_subjects`
CREATE TABLE IF NOT EXISTS  {$db_prefix}related_subjects (
  id_topic int(10) unsigned NOT NULL,
  subject tinytext NOT NULL,
  score float unsigned NOT NULL,
  PRIMARY KEY (id_topic),
  FULLTEXT KEY subject (subject)
);
---#

/******************************************************************************/
---  Restrict Post tables...
/******************************************************************************/

---# Restrict Post
CREATE TABLE IF NOT EXISTS {$db_prefix}restrict_posts (
  id_board smallint(5) unsigned NOT NULL,
  id_group smallint(5) NOT NULL,
  max_posts_allowed int(10) unsigned NOT NULL default '0',
  timespan int(10) unsigned NOT NULL default '1'
) ENGINE=MyISAM{$db_collation};
---#

/******************************************************************************/
---  Tagging System tables...
/******************************************************************************/



---# Table structure for table `tags`
CREATE TABLE IF NOT EXISTS {$db_prefix}tags (
  id_tag mediumint(8) NOT NULL auto_increment,
  tag tinytext NOT NULL,
  PRIMARY KEY  (id_tag)
) ENGINE=MyISAM{$db_collation};
---#

---# Table structure for table `tags_topic`
CREATE TABLE IF NOT EXISTS {$db_prefix}tags_topic (
  id mediumint(8) NOT NULL auto_increment,
  id_tag mediumint(8) NOT NULL default '0',
  id_topic mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (id)
) ENGINE=MyISAM{$db_collation};
---#

/******************************************************************************/
---  Final Theme Options...
/******************************************************************************/

---# Setting up Final Theme Options
INSERT IGNORE INTO {$db_prefix}themes (id_member, id_theme, variable, value) VALUES (-1, 1, 'display_quick_reply', '2');
INSERT IGNORE  INTO {$db_prefix}themes (id_member, id_theme, variable, value) VALUES (-1, 1, 'posts_apply_ignore_list', '1');
INSERT IGNORE  INTO {$db_prefix}themes (id_member, id_theme, variable, value) VALUES (-1, 1, 'copy_to_outbox', '1');
INSERT IGNORE  INTO {$db_prefix}themes (id_member, id_theme, variable, value) VALUES (-1, 1, 'use_sidebar_menu', '1');
INSERT IGNORE  INTO {$db_prefix}themes (id_member, id_theme, variable, value) VALUES (-1, 1, 'return_to_post', '1');
INSERT IGNORE  INTO {$db_prefix}themes (id_member, id_theme, variable, value) VALUES (-1, 1, 'display_quick_mod', '1');
---#

/******************************************************************************/
--- Cleaning up integration hooks
/******************************************************************************/
---# Deleting integration hooks
DELETE FROM {$db_prefix}settings
WHERE variable LIKE 'integrate_%';
---#