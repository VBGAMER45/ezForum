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
--- Adding new forum settings.
/******************************************************************************/

---# Resetting settings_updated.
REPLACE INTO {$db_prefix}settings
	(variable, value)
VALUES
	('settings_updated', '0'),
	('last_mod_report_action', '0'),
	('search_floodcontrol_time', '5'),
	('next_task_time', UNIX_TIMESTAMP());
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
	(next_time, time_offset, time_regularity, time_unit, disabled, task)
VALUES
	(0, 0, 2, 'h', 0, 'approval_notification'),
	(0, 0, 7, 'd', 0, 'auto_optimize'),
	(0, 60, 1, 'd', 0, 'daily_maintenance'),
	(0, 0, 1, 'd', 0, 'daily_digest'),
	(0, 0, 1, 'w', 0, 'weekly_digest'),
	(0, 0, 1, 'd', 1, 'birthdayemails'),
	(0, 120, 1, 'd', 0, 'paid_subscriptions');
---#



/******************************************************************************/
--- Installing new default theme...
/******************************************************************************/


---#
// Update the name of the default theme in the database.
		upgrade_query("
			UPDATE {$db_prefix}themes
			SET value = 'ezForum Default Theme - Curve'
			WHERE id_theme = 1
				AND variable = 'name'");

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






