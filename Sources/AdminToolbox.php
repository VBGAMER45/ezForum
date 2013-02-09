<?php

/**
 * Admin Toolbox, little functions that are a big PITA
 *
 * @package Admin Toolbox
 * @license This Source Code is subject to the terms of the Mozilla Public License
 * version 2.0 (the "License"). You can obtain a copy of the License at
 * http://mozilla.org/MPL/2.0/.
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * AdminToolbox()
 *
 * Main entry point for the manage toolbox options
 * @return
 */
function AdminToolbox()
{
	global $txt, $context, $smcFunc;

	// You absolutely must be an admin to be in here, and for some that still is not a good idea ;)
	isAllowedTo('admin_forum');

	// Need something so folks can see what we are saying since its important
	loadLanguage('AdminToolbox');
	loadTemplate('AdminToolbox');

	// Subactions
	$subActions = array(
		'main' => array(
			'function' => 'ToolboxRoutine',
			'template' => 'toolbox_main',
		),
		'recount' => array(
			'function' => 'RecountAllMemberPosts',
			'template' => 'toolbox_main',
		),
		'inactive' => array(
			'function' => 'MarkInactiveRead',
			'template' => 'toolbox_main',
		),
		'merge' => array(
			'function' => 'MergeMembers',
			'template' => 'toolbox_main',
		),
		'stats' => array(
			'function' => 'StatsRecount',
			'template' => 'toolbox_main',
		),
		'statsvalidate' => array(
			'function' => 'StatsMakeupData',
			'template' => 'toolbox_stats_rebuild',
		),
		'validate' => array(
			'function' => 'MergeMembersValidate',
			'template' => 'toolbox_validate',
		),
	);

	// Pick a valid one or set our default ...
	if (isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]))
		$subAction = $_REQUEST['sa'];
	else
		$subAction = 'main';

	// Set a few things.
	$context['admintoolbox_database'] = $smcFunc['db_title'] == 'MySQL';
	$context['page_title'] = $txt['toolbox_title'];
	$context['sub_template'] = !empty($subActions[$subAction]['template']) ? $subActions[$subAction]['template'] : '';
	$context['sub_action'] = $subAction;

	// Finally fall through to what we are doing.
	$subActions[$subAction]['function']();
}

/**
 * ToolboxRoutine()
 *
 * Supporting function for the routine toolbox area.
 * @return
 */
function ToolboxRoutine()
{
	global $context, $txt, $settings;

	// if we have any messages to show-em ... well set-em
	if (isset($_REQUEST['done']) && isset($txt['toolbox_' . $_REQUEST['done']]))
		$context['maintenance_finished'] = $txt['toolbox_' . $_REQUEST['done']];
	elseif (isset($_REQUEST['error']) && isset($txt['toolbox_' . $_REQUEST['error']]))
		$context['maintenance_error'] = $txt['toolbox_' . $_REQUEST['error']];

	// replace those standard info boxes with something less ... well .... look away I'm hideous
	$context['html_headers'] .= '
	<style type="text/css">
	.errorbox {
		background:#ffe4e9 url(' . $settings['images_url'] . '/warning_mute.gif) center no-repeat;
		background-position:8px 50%;
		text-align:left;
		border-top:2px solid #cc3344;
		border-bottom:2px solid #cc3344;
		padding:1em 10px 1em 30px
	}
	.maintenance_finished {
		background:#c5ffb8 url(' . $settings['images_url'] . '/warning_watch.gif) center no-repeat;
		background-position:8px 50%;
		text-align:left;
		border-top:1px solid #ffd324;
		border-bottom:1px solid #ffd324;
		border-right:0px;
		border-left:0px;
		padding:1em 10px 1em 30px;
	}
	</style>';
}

/**
 * RecountAllMemberPosts()
 *
 * - recounts all posts for members found in the message table
 * - updates the members post count record in the members talbe
 * - honors the boards post count flag
 * - does not count posts in the recyle bin
 * - zeros post counts for all members with no posts in the message table
 * - runs as a delayed loop to avoid server overload
 * - uses the not_done template in Admin.template
 *
 * @return
 */
function RecountAllMemberPosts()
{
	global $txt, $context, $modSettings, $smcFunc;

	// keep the bad monkeys away
	isAllowedTo('admin_forum');
	checkSession('request');

	// Set up to the context.
	$context['page_title'] = $txt['not_done_title'];
	$context['continue_countdown'] = 3;
	$context['continue_post_data'] = '';
	$context['continue_get_data'] = '';
	$context['sub_template'] = 'not_done';

	// init
	$increment = 200;
	$_REQUEST['start'] = !isset($_REQUEST['start']) ? 0 : (int) $_REQUEST['start'];

	// Ask for some extra time, big boards may take a bit
	@set_time_limit(600);

	// Only run this query if we don't have the total.
	if (!isset($_SESSION['total_members']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(DISTINCT m.id_member)
			FROM ({db_prefix}messages AS m, {db_prefix}boards AS b)
			WHERE m.id_member != 0
				AND b.count_posts = 0
				AND m.id_board = b.id_board',
			array(
			)
		);

		// save it so we don't do this again for this recount round.
		list ($_SESSION['total_members']) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
	}

	// Lets get a group of members and determine their post counts (from the boards that have posts count enabled of course).
	$request = $smcFunc['db_query']('', '
		SELECT /*!40001 SQL_NO_CACHE */ m.id_member, COUNT(m.id_member) AS posts
		FROM ({db_prefix}messages AS m, {db_prefix}boards AS b)
		WHERE m.id_member != {int:zero}
			AND b.count_posts = {int:zero}
			AND m.id_board = b.id_board' . (!empty($modSettings['recycle_enable']) ? '
			AND b.id_board != {int:recycle}' : '') . '
		GROUP BY m.id_member
		LIMIT {int:start}, {int:number}',
		array(
			'zero' => 0,
			'start' => $_REQUEST['start'],
			'number' => $increment,
			'recycle' => $modSettings['recycle_board'],
		)
	);
	$total_rows = $smcFunc['db_num_rows']($request);
	
	// Update the count for this group
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET posts = {int:posts}
			WHERE id_member = {int:row}',
			array(
				'row' => $row['id_member'],
				'posts' => $row['posts'],
			)
		);
	}
	$smcFunc['db_free_result']($request);

	// Continue?
	if ($total_rows == $increment)
	{
		$_REQUEST['start'] += $increment;
		$context['continue_get_data'] = '?action=admin;area=toolbox;sa=recount;start=' . $_REQUEST['start'] . ';' . $context['session_var'] . '=' . $context['session_id'];
		$context['continue_percent'] = round(100 * $_REQUEST['start'] / $_SESSION['total_members']);
		if (function_exists('apache_reset_timeout'))
			apache_reset_timeout();
		return;
	}

	// final steps place all members who have posts in the message table in a temp table
	$createTemporary = $smcFunc['db_query']('', '
		CREATE TEMPORARY TABLE {db_prefix}tmp_maint_recountposts (
			id_member mediumint(8) unsigned NOT NULL default {string:string_zero},
			PRIMARY KEY (id_member)
		)
		SELECT m.id_member
		FROM ({db_prefix}messages AS m,{db_prefix}boards AS b)
		WHERE m.id_member != {int:zero}
			AND b.count_posts = {int:zero}
			AND m.id_board = b.id_board ' . (!empty($modSettings['recycle_enable']) ? '
			AND b.id_board != {int:recycle}' : '') . '
		GROUP BY m.id_member',
		array(
			'zero' => 0,
			'string_zero' => '0',
			'db_error_skip' => true,
			'recycle' => $modSettings['recycle_board'],
		)
	) !== false;

	if ($createTemporary)
	{
		// outer join the members table on the temporary table finding all the members that have a post count but *no* posts in the message table
		$request = $smcFunc['db_query']('', '
			SELECT mem.id_member, mem.posts
			FROM {db_prefix}members AS mem
			LEFT OUTER JOIN {db_prefix}tmp_maint_recountposts AS res
			ON res.id_member = mem.id_member
			WHERE res.id_member IS null
				AND mem.posts != {int:zero}',
			array(
				'zero' => 0,
			)
		);

		// set the post count to zero for any delinquents we may have found
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}members
				SET posts = {int:zero}
				WHERE id_member = {int:row}',
				array(
					'row' => $row['id_member'],
					'zero' => 0,
				)
			);
		}
		$smcFunc['db_free_result']($request);
	}

	// all done
	unset($_SESSION['total_members']);
	redirectexit('action=admin;area=toolbox;done=recount');
}

/**
 * RecountMemberPosts()
 *
 * Takes an array of users and recounts just their post totals
 * @param mixed $members
 */
function RecountMemberPosts($members)
{
	global $modSettings, $smcFunc;

	// keep the bad monkeys away
	isAllowedTo('admin_forum');
	checkSession('request');

	// Can't do it if there's no info, duh
	if (empty($members))
		return;

	// It must be an array
	if (!is_array($members))
		$members = array($members);

	// Lets get their post counts
	$request = $smcFunc['db_query']('', '
		SELECT m.id_member, COUNT(m.id_member) AS posts
		FROM ({db_prefix}messages AS m, {db_prefix}boards AS b)
		WHERE m.id_member IN ({array_int:members})
			AND b.count_posts = {int:zero}
			AND m.id_board = b.id_board' . (!empty($modSettings['recycle_enable']) ? ('
			AND b.id_board != ' . $modSettings['recycle_board']) : ''),
		array(
			'zero' => 0,
			'members' => $members,
		)
	);

	// Update the count for these members
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET posts = {int:posts}
			WHERE id_member = {int:row}',
			array(
				'row' => $row['id_member'],
				'posts' => $row['posts'],
			)
		);
	}
	$smcFunc['db_free_result']($request);
}

/**
 * MarkInactiveRead()
 *
 * Mark inactive users as having read everything
 *
 */
function MarkInactiveRead()
{
	global $txt, $context, $modSettings, $smcFunc, $db_prefix;

	// yup ... no flying monkeys
	isAllowedTo('admin_forum');
	checkSession('request');

	// Set up to the context.
	$context['page_title'] = $txt['not_done_title'];
	$context['continue_countdown'] = 5;
	$context['continue_post_data'] = '';
	$context['continue_get_data'] = '';
	$context['sub_template'] = 'not_done';

	// init our run
	$increment = 100;
	$_REQUEST['start'] = !isset($_REQUEST['start']) ? 0 : (int) $_REQUEST['start'];

	$not_visited = time() - (!empty($_POST['inactive_days']) ? $_POST['inactive_days'] : 60) * 24 * 3600;
	$members = array();
	$inserts = array();

	// Ask for some extra time
	@set_time_limit(600);

	// Only run these specific querys on the first loop
	if (!isset($_SESSION['boards']) || !isset($_SESSION['total_members']))
	{
		$boards = array();

		// First thing's first - get all the boards in the system
		$request = $smcFunc['db_query']('', '
			SELECT id_board
			FROM {db_prefix}boards',
			array(
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$boards[$row['id_board']] = $row['id_board'];
		$smcFunc['db_free_result']($request);

		// get all the members from the log_topics table who have not visted in xx time
		$request = $smcFunc['db_query']('', '
			SELECT DISTINCT lt.id_member
			FROM {db_prefix}log_topics as lt
				LEFT JOIN {db_prefix}members as m ON (m.id_member = lt.id_member)
			WHERE lt.id_topic > {int:zero} ' . ($not_visited == 0 ? '' : ' AND m.last_login < {int:not_visited} '),
			array(
				'zero' => 0,
				'not_visited' => $not_visited,
			)
		);

		// the total number of lurkers we have to deal with ;)
		$total_members = $smcFunc['db_num_rows']($request);

		// do the first batch since we made just made the query :/
		$total_rows = ($total_members > $increment) ? $increment : $total_members;
		for ($i = 0; $i < $total_rows; $i++)
		{
			$row = $smcFunc['db_fetch_assoc']($request);
			$members[] = $row['id_member'];

			// mark every board for this member as read
			foreach ($boards as $board)
				$inserts[] = array($modSettings['maxMsgID'], $row['id_member'], $board);
		}
		$smcFunc['db_free_result']($request);

		// set it for use in other loops
		$_SESSION['boards'] = $boards;
		$_SESSION['total_members'] = $total_members;
	}
	else
	{
		$boards = $_SESSION['boards'];
		$total_members = $_SESSION['total_members'];

		// Load a group of members from the log_topics table who have not been active  ...
		$request = $smcFunc['db_query']('', '
			SELECT DISTINCT lt.id_member
			FROM {db_prefix}log_topics AS lt
				LEFT JOIN {db_prefix}members AS m ON (m.id_member = lt.id_member)
			WHERE lt.id_topic > {int:zero} ' . ($not_visited == 0 ? '' : ' AND m.last_login < {int:not_visited} ') . '
			LIMIT {int:start}, {int:number}',
			array(
				'start' => 0,
				'zero' => 0,
				'number' => $increment,
				'not_visited' => $not_visited,
			)
		);
		$total_rows = $smcFunc['db_num_rows']($request);

		// Build the querys for this bunch of ummm .... slackers
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$members[] = $row['id_member'];

			// mark the boards as read for this member
			foreach ($boards as $board)
				$inserts[] = array($modSettings['maxMsgID'], $row['id_member'], $board);
		}
		$smcFunc['db_free_result']($request);
	}

	// Do the updates in a route 44 big gulp.
	if (!empty($inserts))
	{
		// Update log_mark_read and log_boards for these members just like they hit mark all as read
		$smcFunc['db_insert']('replace',
			'{db_prefix}log_mark_read',
			array('id_msg' => 'int', 'id_member' => 'int', 'id_board' => 'int'),
			$inserts,
			array('id_board', 'id_member')
		);

		$smcFunc['db_insert']('replace',
			'{db_prefix}log_boards',
			array('id_msg' => 'int', 'id_member' => 'int', 'id_board' => 'int'),
			$inserts,
			array('id_board', 'id_member')
		);
	}

	// and now remove the useless log_topics data, for these members, since these inactive members just read everything super fast
	if (!empty($members))
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_topics
			WHERE id_topic > {int:zero}
				AND id_member IN ({array_int:members})',
			array(
				'zero' => 0,
				'members' => $members,
			)
		);
	}

	// Continue?
	if ($total_rows == $increment)
	{
		$_REQUEST['start'] += $increment;
		$context['continue_get_data'] = '?action=admin;area=toolbox;sa=inactive;start=' . $_REQUEST['start'] . ';' . $context['session_var'] . '=' . $context['session_id'];
		$context['continue_percent'] = round(100 * ($_REQUEST['start']  / $_SESSION['total_members']));

		// really would like to keep runing mr. apache
		if (function_exists('apache_reset_timeout'))
			apache_reset_timeout();
		return;
	}

	// optimize the one table that should have gone down in size, assuming its not innodb of course
	ignore_user_abort(true);
	db_extend();
	$real_prefix = preg_match('~^(`?)(.+?)\\1\\.(.*?)$~', $db_prefix, $match) === 1 ? $match[3] : $db_prefix;
	$smcFunc['db_optimize_table']($real_prefix . 'log_topics');

	// all done
	unset($_SESSION['boards'], $_SESSION['total_members']);
	redirectexit('action=admin;area=toolbox;done=inactive');
}

/**
  * StatsMakeupData()
  *
  * Check for the condition where message/topic/registration data predates the stats data
  * Needed to build some value for Hits and Most online for those ealier dates
  * Zero, Current Average, or Balanced options
  *
  * @return
 */
function StatsMakeupData()
{
	global $txt, $context, $smcFunc;

	// Need to be the admin for this operation
	isAllowedTo('admin_forum');

	// not getting hacky with it are ya bum?
	if ($smcFunc['db_title'] !== 'MySQL')
		redirectexit('action=admin;area=toolbox;');

	// The oldest message date
	$request = $smcFunc['db_query']('', '
		SELECT MIN(id_msg), date(FROM_UNIXTIME(poster_time))
		FROM {db_prefix}messages
		LIMIT 1',
		array(
		)
	);
	list ($dummy, $message_start_date) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// When the daily stats started, plus some totals in case we need them
	$result = $smcFunc['db_query']('', '
		SELECT
			SUM(most_on) AS most_on, MIN(date) AS stat_start_date, SUM(hits) AS hits, SUM(posts) as posts
		FROM {db_prefix}log_activity',
		array(
		)
	);
	$stats = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	// for the averages option
	$total_days_up = ceil((time() - strtotime($stats['stat_start_date'])) / (60 * 60 * 24));
	$stats['total_days_up'] = $total_days_up;
	$stats['most_on'] = ceil($stats['most_on'] / $total_days_up);
	$stats['hits'] = ceil($stats['hits'] / $total_days_up);

	// is there a notable datediff between the daily stats and message data?  Then we may need to "make some stuff up"tm to fill the gaps
	if (round((strtotime($stats['stat_start_date']) - strtotime($message_start_date)) / 86400) > 0)
	{
		// Get the existing monthly statistics data
		$result = $smcFunc['db_query']('', '
			SELECT
				SUM(most_on) AS most_on, SUM(hits) AS hits, date
				FROM {db_prefix}log_activity
				GROUP BY EXTRACT(YEAR_MONTH FROM (date))
				ORDER BY date ASC',
			array(
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			// build the hits, most on and days delta arrays
			$stats_data_hits[] = $row['hits'];
			$stats_data_most_on[] = $row['most_on'];
			$stats_data_delta[] = round((strtotime($row['date']) - strtotime($message_start_date)) / 86400);
		}
		$smcFunc['db_free_result']($result);

		/*
		 * - If we have enough data, drop first and last months as its start up and shutdown noise (incomplete months) in the data
		 * - If data is very lagged, then add in a new entry point to influence the curve fit coefficents to minimize b intercept
		 * - With the data determine curve fit coefficients for the data line so we can use them to build other values, we are using exponetial
		 *   should you choose linear, you will need to change the equations in the statsrecount function
		 */
		$missing_months = count($stats_data_hits);
		if ($missing_months >= 4)
		{
			$stats_data_hits = array_slice($stats_data_hits, 1, count($stats_data_hits) - 2);
			if ($missing_months >= 12)
				array_unshift($stats_data_hits, 1);

			$stats_data_most_on = array_slice($stats_data_most_on, 1, count($stats_data_most_on) - 2);
			if ($missing_months >= 12)
				array_unshift($stats_data_most_on, 1);

			$stats_data_delta = array_slice($stats_data_delta, 1, count($stats_data_delta) - 2);
			if ($missing_months >= 12)
				array_unshift($stats_data_delta, 0);
		}

		$stats['most_on_coeff'] = (linear_regression($stats_data_delta, $stats_data_most_on, true));
		$stats['hits_coeff'] = (linear_regression($stats_data_delta, $stats_data_hits, true));
	}

	$stats['message_start_date'] = $message_start_date;
	$context['stats'] = $stats;

	// Data rebuild Options, based on what we can do
	if ($total_days_up == 1)
		$context['toolbox_rebuild_option'] = array(
			array('id' => 1, 'value' => 'bypass', 'name' => $txt['toolbox_skip'], 'desc' => $txt['toolbox_skip_desc']),
			array('id' => 2, 'value' => 'zero', 'name' => $txt['toolbox_zero'], 'desc' => $txt['toolbox_zero_desc']),
		);
	else
		$context['toolbox_rebuild_option'] = array(
			array('id' => 1, 'value' => 'bypass', 'name' => $txt['toolbox_skip'], 'desc' => $txt['toolbox_skip_desc']),
			array('id' => 2, 'value' => 'zero', 'name' => $txt['toolbox_zero'], 'desc' => $txt['toolbox_zero_desc']),
			array('id' => 3, 'value' => 'average', 'name' => $txt['toolbox_average'], 'desc' => $txt['toolbox_average_desc']),
			array('id' => 4, 'value' => 'balanced', 'name' => $txt['toolbox_balanced'], 'desc' => $txt['toolbox_balanced_desc']),
		);
}

/**
 * Best fit (least squares) linear regression for of $y = $m * $x + $b
 * Optional exponential curve fit in the form of $y = $b * pow($m, $x)
 *
 * @param $x array x-coords
 * @param $y array y-coords
 *
 * @returns array() m=>slope, b=>intercept
 */
function linear_regression($x, $y, $power = false)
{
	// number of data points
	$n = count($x);

	// arrays need to be the same size and have more than 1 point for da math to work
	if ($n != count($y) || $n == 1)
		return array('m' => 0, 'b' => 0);

	// convert Y data to logs only if doing an exponential fit
	if ($power)
	{
		foreach ($y as $key => $value)
			$y[$key] = log10($value);
	}

	// calculate sums
	$x_sum = array_sum($x);
	$y_sum = array_sum($y);
	$xx_sum = 0;
	$xy_sum = 0;

	// and the sum of the squares
	foreach ($x as $key => $value)
	{
		$xy_sum += ($value * $y[$key]);
		$xx_sum += ($value * $value);
	}

	// slope aka 'm'
	$divisor = (($n * $xx_sum) - ($x_sum * $x_sum));
	if ($divisor == 0)
		$m = 0;
	else
		$m = (($n * $xy_sum) - ($x_sum * $y_sum)) / $divisor;

	// intercept aka 'b'
	$b = ($y_sum - ($m * $x_sum)) / $n;

	// adjust linear fit of log data back to power coefficients
	if ($power)
	{
		$m = pow(10, $m);
		$b = pow(10, $b);
	}

	// return coefficients
	return array('m' => $m, 'b' => $b);
}

/**
 * StatsRecount()
 *
 * Recount the daily posts and topics for the stats page
 * Recount the daily users registered
 * Updates the log activity table with the new counts
 * *mysql only*, others are welcome to port it to other schemas ... some hints
 *  - GROUP BY EXTRACT(YEAR_MONTH FROM needs to be done as two parts, year and then month for PostgreSQL
 *  - TIME_TO_SEC needs to be changed
 *  - ON DUPLICATE KEY UPDATE replaced with a loop of inserts and if it fails an update instead or other such missery
 *
 * @return
 */
function StatsRecount()
{
	global $txt, $context, $smcFunc, $modSettings, $db_prefix;

	// Who's there, oh its just you
	isAllowedTo('admin_forum');
	checkSession('request');

	// How did you get here, it implausible !
	if ($smcFunc['db_title'] !== 'MySQL')
		redirectexit('action=admin;area=toolbox;');

	// init this pass
	$inserts = array();

	// Set up to the context.
	$context['page_title'] = $txt['not_done_title'];
	$context['continue_countdown'] = 3;
	$context['continue_post_data'] = '';
	$context['continue_get_data'] = '';
	$context['sub_template'] = 'not_done';

	// Only do these steps on the first loop
	if (!isset($_SESSION['start_date_int']))
	{
		// no data from the post page, you get bounced
		$stats = array();
		$stats = unserialize(stripslashes(htmlspecialchars_decode($_POST['stats_data'])));
		if (empty($stats))
			redirectexit('action=admin;area=toolbox');

		// keep each pass at the 4 month level ...
		$_SESSION['months_per_loop'] = 4;

		// choose how to rebuild any missing daily hits and most on-line
		$_POST['id_type'] = isset($_POST['id_type']) ? (int) $_POST['id_type'] : 1;
		switch ($_POST['id_type'])
		{
			case 1:
				$rebuild_method = 'bypass';
				$start_date = $stats['stat_start_date'];
				break;
			case 2:
				$rebuild_method = 'zero';
				$start_date = $stats['message_start_date'];
				break;
			case 3:
				$rebuild_method = 'average';
				$start_date = $stats['message_start_date'];
				break;
			case 4:
				$rebuild_method = 'balanced';
				$start_date = $stats['message_start_date'];
				break;
			default:
				$rebuild_method = 'bypass';
				$start_date = $stats['stat_start_date'];
				break;
		}

		// Start building data from this point forward, Start at a month boundary to make the loops easy
		list($start_year, $start_month) = explode('-', $start_date);
		$_SESSION['start_date_str'] = $start_year . '-' . $start_month . '-' . '01';
		$_SESSION['start_date_int'] = strtotime($_SESSION['start_date_str']);

		// this is the actual start date from which we *do* count
		$_SESSION['original_date_str'] = $start_date;
		$_SESSION['original_date_int'] = strtotime($start_date);
		$stats['stat_start_date_int'] = strtotime($stats['stat_start_date']);

		// account for the sql date not being the same as the server local date, wacky but I've seen it
		$request = $smcFunc['db_query']('', '
			SELECT TIME_TO_SEC(timediff(NOW(), UTC_TIMESTAMP()))',
			array(
			)
		);
		list($sql_offset) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$sql_offset = date('Z') - $sql_offset;

		// total offset for the query time adjustment
		$total_offset = $sql_offset + ($modSettings['time_offset'] * 3600);

		// save this, might need it ;)
		$_SESSION['stats'] = $stats;
		$_SESSION['total_offset'] = $total_offset;
		$_SESSION['rebuild_method'] = $rebuild_method;
	}

	// Loop Datesssssss
	$start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : $_SESSION['start_date_int'];
	$end_date = strtotime('+' . $_SESSION['months_per_loop'] . ' month -1 second', $start_date);
	$total_offset = $_SESSION['total_offset'];

	// Count the number of distinct topics and total messages for date range combo.
	$request = $smcFunc['db_query']('', '
		SELECT poster_time, COUNT(t.id_topic) AS topics, COUNT(DISTINCT(id_msg)) AS posts, DATE(from_unixtime(poster_time + ' . $total_offset . ')) AS REALTIME
		FROM {db_prefix}messages AS m
		LEFT JOIN {db_prefix}topics AS t ON (t.id_first_msg = m.id_msg)
		LEFT JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board)
		WHERE poster_time >= {int:start_date} AND poster_time <= {int:end_date} AND b.count_posts = 0
		GROUP BY REALTIME
		ORDER BY REALTIME',
		array(
			'end_date' => $end_date + ($modSettings['time_offset'] * 3600),
			'start_date' => $start_date + ($modSettings['time_offset'] * 3600),
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// data is in the range we are collecting
		if (($row['poster_time'] + ($modSettings['time_offset'] * 3600)) >= $_SESSION['original_date_int'])
		{
			$date_id = $row['REALTIME'];

			if (isset($inserts[$date_id]))
			{
				$inserts[$date_id]['topics'] += $row['topics'];
				$inserts[$date_id]['messages'] += $row['posts'];
			}
			else
				$inserts[$date_id] = array('topics' => (int) $row['topics'], 'messages' => (int) $row['posts'], 'date' => sprintf('\'%1$s\'', $date_id), 'registers' => 0);
		}
	}
	$smcFunc['db_free_result']($request);

	// and now count the number of registrations per day
	$request = $smcFunc['db_query']('', '
		SELECT count(is_activated) AS registers, date_registered
		FROM {db_prefix}members
		WHERE is_activated = {int:activated} AND date_registered >= {int:start_date} AND date_registered <= {int:end_date}
		GROUP BY date_registered',
		array(
			'activated' => 1,
			'end_date' =>  $end_date + $modSettings['time_offset'],
			'start_date' => $start_date  + $modSettings['time_offset'],
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// data is in the range we are collecting
		if ($row['date_registered'] >= $_SESSION['original_date_int'])
		{
			// keep those day boundarys our you will have a mess
			$date_id = strftime('%Y-%m-%d', $row['date_registered'] - $modSettings['time_offset']);
			if (isset($inserts[$date_id]))
				$inserts[$date_id]['registers'] += $row['registers'];
			else
				$inserts[$date_id] = array('topics' => 0, 'messages' => 0, 'date' => sprintf('\'%1$s\'', $date_id), 'registers' => (int) $row['registers']);
		}
	}
	$smcFunc['db_free_result']($request);

	// thats all the "real" data we can rebuild, now lets fill in the hits/most data based on rebuild options
	$temp_time = $start_date;
	$end_time = $end_date;
	while ($end_time >= $temp_time)
	{
		$start_time = $temp_time;
		$array_time = strftime('%Y-%m-%d', $start_time);
		if ($start_time >= $_SESSION['original_date_int'] && (isset($inserts[$array_time])))
		{
			// based on user choice calculate (nice way to say take a guess) the daily hits and most on line data
			switch ($_SESSION['rebuild_method'])
			{
				case 'average':
					$hits = $_SESSION['stats']['hits'];
					$most = $_SESSION['stats']['most_on'];
					break;
				case 'balanced':
					// As the curve fit approaches the real data points, the curve will begin to expand rapidly.
					// limiter is a linear fit based on the averages data to prevent this fly away and help the blend point of the
					// exponetial curve in to the existing data.  I'd draw a picture here but ascii art is not my thing :)
					// but we are estimating missing data here, lies, damn lies and statistics! ;)
					$limiter = abs(((($start_date - $_SESSION['original_date_int'])) / 86400) / ((time() - $_SESSION['original_date_int']) / 86400));
					$x = round(($start_time - $_SESSION['original_date_int']) / 86400);
					$hits = min(floor($_SESSION['stats']['hits_coeff']['b'] * pow($_SESSION['stats']['hits_coeff']['m'], $x)), floor($_SESSION['stats']['hits'] * $limiter));
					$most = min(floor($_SESSION['stats']['most_on_coeff']['b'] * pow($_SESSION['stats']['most_on_coeff']['m'], $x)), floor($_SESSION['stats']['most_on'] * $limiter));
					$most = (empty($most)) ? 1 : (int) $most;
					$hits = (empty($hits)) ? 1 : (int) $hits;
					break;
				case 'zero':
				default:
					$hits = 0;
					$most = 0;
					break;
			}
			$inserts[$array_time] = array_merge($inserts[$array_time], array('hits' => $hits, 'most_on' => $most));
		}

		// increment the date
		$temp_time = strtotime('+1 day', $start_time);
	}

	// All the data has been magically sanitised, while it was built, so create the insert values
	ksort($inserts);
	$insertRows = array();
	foreach ($inserts as $dataRow)
		$insertRows[] = '(' .  implode(',', $dataRow) . ')';

	// We have data now to insert / update ....
	if (!empty($insertRows))
	{
		// Slam-A-Jama, all the inserts and updates in one big chunk, compliments of ON DUPLICATE KEY UPDATE, blissfully mysql only
		$smcFunc['db_query']('', '
			INSERT INTO ' . $db_prefix . 'log_activity ' .
			'(`' . implode('`, `', array('topics', 'posts', 'date', 'registers', 'hits', 'most_on')) . '`)
			VALUES ' . implode(', ', $insertRows) .
			' ON DUPLICATE KEY UPDATE `topics` = VALUES(topics), `posts` = VALUES(posts), `registers` = VALUES(registers)',
			array(
				'security_override' => true,
				'db_error_skip' => false,
			)
		);
	}

	// Continue?
	$context['continue_percent'] = round(100 * (max(0, (($start_date - $_SESSION['original_date_int'])) / 86400) / ((time() - $_SESSION['original_date_int']) / 86400)));
	if ($start_date <= time())
	{
		$_REQUEST['start_date'] = strtotime('+' . $_SESSION['months_per_loop'] . ' month', $start_date);
		$context['continue_get_data'] = '?action=admin;area=toolbox;sa=stats;start_date=' . $_REQUEST['start_date'] . ';' . $context['session_var'] . '=' . $context['session_id'];

		if (function_exists('apache_reset_timeout'))
			apache_reset_timeout();
		return;
	}

	// all done
	unset($_SESSION['start_date_str'], $_SESSION['start_date_int'], $_SESSION['original_date_str'], $_SESSION['original_date_int'], $_SESSION['stats'], $_SESSION['rebuild_method'], $_SESSION['total_offset']);

	// although not necessary some will like this table to remain in order so it looks good in phpmyadmin
	$smcFunc['db_query']('', '
		ALTER TABLE {db_prefix}log_activity
		ORDER BY date',
		array(
		)
	);

	redirectexit('action=admin;area=toolbox;done=stats');
}

/**
 * MergeMembersValidate()
 *
 * Support function for merge users
 * Validate the to and from users as existing and other validity checks
 * Prepares context for display so user can approve the merge
 *
 * @return
 */
function MergeMembersValidate()
{
	global $smcFunc, $user_info, $user_profile, $context, $txt;

	// Need to be the admin for this operation
	isAllowedTo('admin_forum');
	checkSession('post');

	// Sanitize as needed, the _id post vars are set by the autosuggest script, if found move them to normal post
	$_POST['merge_to'] = empty($_POST['merge_to_id']) ? 0 : (int) $_POST['merge_to_id'];
	$_POST['merge_from'] = empty($_POST['merge_from_id']) ? 0 : (int) $_POST['merge_from_id'];

	// Did they use the search icon -or- enter a number directly in to the box or some combo there off ...
	if (empty($_POST['merge_to']) || empty($_POST['merge_from']))
	{
		// The post fields are obfuscated to prevent the browser from auto populating them, so we need to go find them
		reset($_POST);
		$count = 0;
		foreach ($_POST as $key => $value)
		{
			if (strpos($key, 'dummy_') !== false)
			{
				if ($count == 0)
					$merge_to = $smcFunc['htmltrim']($value);
				else
					$merge_from = $smcFunc['htmltrim']($value);
				$count++;
			}
			if ($count > 1)
				break;
		}

		// If not autosuggest populated, and we found the post field, use it
		if (empty($_POST['merge_to']))
			$merge_to = ($merge_to != '') ? $merge_to : 0;
		if (empty($_POST['merge_from']))
			$merge_from = ($merge_from != '') ? $merge_from : 0;

		// supplied numbers did they, then assume they are userid's
		if (!empty($merge_from) && is_numeric($merge_from))
			$_POST['merge_from'] = (int) $merge_from;
		if (!empty($merge_to) && is_numeric($merge_to))
			$_POST['merge_to'] = (int) $merge_to;

		// Perhaps some text instead, then we search on the name to get the number
		if (!empty($merge_to) || !empty($merge_from))
		{
			$query = 'real_name = ';
			$query .= (!empty($merge_to)) ? "'$merge_to'" : '';
			$query .= (!empty($merge_from) && !empty($merge_to)) ? " OR real_name = '$merge_from'" : (!empty($merge_from) ? "'$merge_from'" : '');
			$query_limit = (!empty($merge_from) && !empty($merge_to)) ? 3 : 2;

			// validate these are member names
			$request = $smcFunc['db_query']('', '
				SELECT id_member, real_name
				FROM {db_prefix}members
				WHERE {raw:query}
				LIMIT {int:limit}',
				array(
					'merge_to' => $merge_to,
					'merge_from' => $merge_from,
					'limit' => $query_limit,
					'query' => $query,
				)
			);

			// if we got back more than $query_limit rows, then the names are not unique, this *should* not happen, but those
			// little mod-ers can cause this since there is no uniqness in the database on this col to prevent it so we check to be sure.
			if ($smcFunc['db_num_rows']($request) == $query_limit - 1)
			{
				while ($row = $smcFunc['db_fetch_assoc']($request))
				{
					if (trim($row['real_name']) === trim($merge_to))
						$_POST['merge_to'] = (int) $row['id_member'];
					elseif (trim($row['real_name']) === trim($merge_from))
						$_POST['merge_from'] = (int) $row['id_member'];
				}
			}
			$smcFunc['db_free_result']($request);
		}
	}

	// Now validate whatever we found, first you simply can't do this to a zero or blank
	if (empty($_POST['merge_to']) || empty($_POST['merge_from']))
		redirectexit('action=admin;area=toolbox;error=zeroid');

	// Not a good idea with the admin account either
	if (($_POST['merge_to'] == 1) || ($_POST['merge_from'] == 1))
		redirectexit('action=admin;area=toolbox;error=adminid');

	// And it cant be the same id
	if ($_POST['merge_to'] == $_POST['merge_from'])
		redirectexit('action=admin;area=toolbox;error=sameid');

	// And these members must exist
	$check = loadMemberData(array($_POST['merge_to'], $_POST['merge_from']), false, 'minimal');
	if (empty($check) || count($check) != 2)
		redirectexit('action=admin;area=toolbox;error=badid');

	// And you can't delete the ID you are currently using, moron
	if (isset($_POST['deluser']) && ($_POST['merge_from'] == $user_info['id']))
		redirectexit('action=admin;area=toolbox;error=baddelete');

	// Data looks valid, so lets make them hit enter to continue ... we want to be sure about this !
	$context['page_title'] = $txt['toolbox_mergeuser_check'];
	$context['merge_to'] = $user_profile[$_POST['merge_to']];
	$context['merge_from'] = $user_profile[$_POST['merge_from']];
	$context['adjustuser'] = isset($_POST['adjustuser']) ? 1 : 0;
	$context['deluser'] = isset($_POST['deluser']) ? 1 : 0;
}

/**
 * MergeTwoUsers()
 *
 * Merge two users ids in to a single account
 * Optionally remove the source user
 * Optionally merge key profile information
 * Call RecountMemberPosts on completion
 *
 * @return
*/
function MergeMembers()
{
	global $txt, $context, $sourcedir, $smcFunc, $user_profile;

	// Need to be the admin for this operation
	isAllowedTo('admin_forum');
	checkSession('request');

	// Set up to the context.
	$context['page_title'] = $txt['not_done_title'];
	$context['continue_countdown'] = 3;
	$context['continue_post_data'] = '';
	$context['continue_get_data'] = '';
	$context['sub_template'] = 'not_done';

	// init our run
	$steps = !isset($_SESSION['steps']) ? 5 : $_SESSION['steps'];
	$name = '';
	$_REQUEST['step'] = !isset($_REQUEST['step']) ? 0 : (int) $_REQUEST['step'];

	// Ask for some extra time, never hurts ;)
	@set_time_limit(600);

	// Only need to do this at the start of our run
	if (!isset($_SESSION['dstid']) || !isset($_SESSION['srcid']))
	{
		$_POST['merge_to'] = empty($_POST['merge_to']) ? 0 : (int) $_POST['merge_to'];
		$_POST['merge_from'] = empty($_POST['merge_from']) ? 0 : (int) $_POST['merge_from'];

		// we support *some* mods, lets see if they are installed or not
		$mods_installed = toolbox_check_mods();

		// add any extra steps to our counter so we can show a reflective progress bar
		$steps += count($mods_installed);

		// set it for use in other loops
		$_SESSION['mods_installed'] = $mods_installed;
		$_SESSION['steps'] = $steps;
		$_SESSION['dstid'] = $_POST['merge_to'];
		$_SESSION['srcid'] = $_POST['merge_from'];
		$_SESSION['deluser'] = (int) $_POST['deluser'];
		$_SESSION['adjustuser'] = (int) $_POST['adjustuser'];
	}

	// lets mergerize the bums !!
	$dstid = $_SESSION['dstid'];
	$srcid = $_SESSION['srcid'];
	$mods_installed = $_SESSION['mods_installed'];

	// Merge Topics
	if ($_REQUEST['step'] == 1)
	{
		// Update the topics owners
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}topics
			SET id_member_started = {int:dstid}
			WHERE id_member_started = {int:srcid}',
			array(
				'dstid' => $dstid,
				'srcid' => $srcid
			)
		);

		// Update the topics updated by member field as well
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}topics
			SET id_member_updated = {int:dstid}
			WHERE id_member_updated = {int:srcid}',
			array(
				'dstid' => $dstid,
				'srcid' => $srcid
			)
		);
	}

	// Merge Posts
	if ($_REQUEST['step'] == 2)
	{
		// Move post ownership from the source id to the destination id
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}messages
			SET id_member = {int:dstid}
			WHERE id_member = {int:srcid}',
			array(
				'dstid' => $dstid,
				'srcid' => $srcid
			)
		);
	}

	// Merge Attachments
	if ($_REQUEST['step'] == 3)
	{
		// The new ID get all the old ids attachments as well
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}attachments
			SET id_member = {int:dstid}
			WHERE id_member = {int:srcid}',
			array(
				'dstid' => $dstid,
				'srcid' => $srcid
			)
		);
	}

	// Merge Private Messages
	if ($_REQUEST['step'] == 4)
	{
		// First the ones you sent
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}personal_messages
			SET id_member_from = {int:dstid}
			WHERE id_member_from = {int:srcid}',
			array(
				'dstid' => $dstid,
				'srcid' => $srcid
			)
		);

		// and now what you have recieved ... but a bit more work is required here :(
		// Get all of the current PMs for these users
		$request = $smcFunc['db_query']('', '
			SELECT id_pm, id_member
			FROM {db_prefix}pm_recipients
			WHERE id_member = {int:dstid} OR id_member = {int:srcid}',
			array(
				'dstid' => $dstid,
				'srcid' => $srcid,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$current_pms[$row['id_member']][] = $row['id_pm'];
		$smcFunc['db_free_result']($request);
		
		// If they don't already have them, they get them, otherwise they loose them
		$current_dst_pms = isset($current_pms[$dstid]) ? $current_pms[$dstid] : array();
		$current_src_pms = isset($current_pms[$srcid]) ? $current_pms[$srcid] : array();
		
		// all of the src pms that the dst does not have or one they do already have
		$move_pms = array_diff($current_src_pms, $current_dst_pms); 
		$remove_pms = array_intersect($current_src_pms, $current_dst_pms);
		
		// And now you get the ones you do not already have 
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}pm_recipients
			SET id_member = {int:dstid}
			WHERE id_member = {int:srcid}' . (empty($move_pms) ? '' : '
				AND (FIND_IN_SET(id_pm, {string:move_pms}) != 0)'),
			array(
				'dstid' => $dstid,
				'srcid' => $srcid,
				'move_pms' => implode(',', $move_pms),
			)
		);
		
		// if we are not removing this user, then we need to adjust the pm totals
		if (empty($_SESSION['deluser']))
		{
			// And now remove the ones that were already there
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}pm_recipients
				WHERE id_member = {int:srcid}' . (empty($remove_pms) ? '' : '
					AND (FIND_IN_SET(id_pm, {string:remove_pms}) != 0)'),
				array(
					'srcid' => $srcid,
					'remove_pms' => implode(',', $remove_pms),
				)
			);
			
			updateMemberData($srcid, array('instant_messages' => 0, 'unread_messages' => 0));
		}
	}

	// Some misc things, like Calendar Events, Polls, other 'Lists'
	if ($_REQUEST['step'] == 5)
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}calendar
			SET id_member = {int:dstid}
			WHERE id_member = {int:srcid}',
			array(
				'dstid' => $dstid,
				'srcid' => $srcid
			)
		);

		// Did you start some polls under the old ID?
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}polls
			SET id_member = {int:dstid}
			WHERE id_member = {int:srcid}',
			array(
				'dstid' => $dstid,
				'srcid' => $srcid
			)
		);

		// Your old buddy is now your new buddy
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET buddy_list = TRIM(BOTH \',\' FROM REPLACE(CONCAT(\',\', buddy_list, \',\'), \',{int:srcid},\', \',{int:dstid},\'))
			WHERE FIND_IN_SET({int:srcid}, buddy_list)',
			array(
				'dstid' => $dstid,
				'srcid' => $srcid
			)
		);

		// and buttheads are still buttheads :)
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET pm_ignore_list = TRIM(BOTH \',\' FROM REPLACE(CONCAT(\',\', pm_ignore_list, \',\'), \',{int:srcid},\', \',{int:dstid},\'))
			WHERE FIND_IN_SET({int:srcid}, pm_ignore_list)',
			array(
				'dstid' => $dstid,
				'srcid' => $srcid
			)
		);
	}

	// Done with SMF standard changes, now on to the mods
	if ($_REQUEST['step'] > 5 && !empty($mods_installed))
	{
		$name = array_pop($mods_installed);
		$_SESSION['mods_installed'] = $mods_installed;
		$sa = 'toolbox_merge_' . $name;
		$sa();
	}

	// Continue?
	if ($_REQUEST['step'] <= $steps)
	{
		// what sub step did we just complete?
		$context['substep_continue_percent'] = 100;
		$context['substep_title'] = isset($txt['toolbox_merge_' . $_REQUEST['step']]) ? $txt['toolbox_merge_' . $_REQUEST['step']] : (isset($txt['toolbox_merge_' . $name]) ? $txt['toolbox_merge_' . $name] : '');
		$context['substep_enabled'] = !empty($context['substep_title']);

		// current progress
		$_REQUEST['step']++;
		$context['continue_get_data'] = '?action=admin;area=toolbox;sa=merge;step=' . $_REQUEST['step'] . ';' . $context['session_var'] . '=' . $context['session_id'];
		$context['continue_percent'] = round(100 * ($_REQUEST['step']  / ($steps + 1)));

		// really would like to keep runing
		if (function_exists('apache_reset_timeout'))
			apache_reset_timeout();
		return;
	}

	// all done, now we munge the user data together in a Frankenstein sort of way :P
	if (!empty($_SESSION['adjustuser']))
	{
		// some munging we will do .... combine some info, and use src id info **IF** dst id info is not set for other, max values for other
		$new_data = array();
		loadMemberData(array($srcid, $dstid), false, 'profile');

		// Combine where it makes sense, and we have data
		if (!empty($user_profile[$srcid]['buddy_list']))
		{
			if (!empty($user_profile[$dstid]['buddy_list']))
				$new_data['buddy_list'] = implode(',', array_unique(array_merge(explode($user_profile[$dstid]['buddy_list'], ','), explode($user_profile[$srcid]['buddy_list'], ','))));
			else
				$new_data['buddy_list'] = $user_profile[$srcid]['buddy_list'];
		}
		if (!empty($user_profile[$srcid]['pm_ignore_list']))
		{
			if (!empty($user_profile[$dstid]['pm_ignore_list']))
				$new_data['pm_ignore_list'] = implode(',', array_unique(array_merge(explode($user_profile[$dstid]['pm_ignore_list'], ','), explode($user_profile[$srcid]['pm_ignore_list'], ','))));
			else
				$new_data['pm_ignore_list'] = $user_profile[$srcid]['pm_ignore_list'];
		}
		if (!empty($user_profile[$srcid]['ignore_boards']))
		{
			if (!empty($user_profile[$dstid]['ignore_boards']))
				$new_data['ignore_boards'] = implode(',', array_unique(array_merge(explode($user_profile[$dstid]['ignore_boards'], ','), explode($user_profile[$srcid]['ignore_boards'], ','))));
			else
				$new_data['ignore_boards'] = $user_profile[$srcid]['ignore_boards'];
		}

		// Combine values together in other cases
		$new_data['karma_bad'] = $user_profile[$dstid]['karma_bad'] + $user_profile[$srcid]['karma_bad'];
		$new_data['karma_good'] = $user_profile[$dstid]['karma_good'] + $user_profile[$srcid]['karma_good'];
		$new_data['total_time_logged_in'] = $user_profile[$dstid]['total_time_logged_in'] + $user_profile[$srcid]['total_time_logged_in'];

		// or just the use old (src) data if new (dst) data does not exist,
		$new_data['date_registered'] = min($user_profile[$dstid]['date_registered'], $user_profile[$srcid]['date_registered']);
		$new_data['personal_text'] = empty($user_profile[$dstid]['personal_text']) ? $user_profile[$srcid]['personal_text'] : $user_profile[$dstid]['personal_text'];
		$new_data['gender'] = empty($user_profile[$dstid]['gender']) ? $user_profile[$srcid]['gender'] : $user_profile[$dstid]['gender'];
		$new_data['birthdate'] = ($user_profile[$dstid]['birthdate'] == '0001-01-01') ? $user_profile[$srcid]['birthdate'] : $user_profile[$dstid]['birthdate'];
		$new_data['website_title'] = empty($user_profile[$dstid]['website_title']) ? $user_profile[$srcid]['website_title'] : $user_profile[$dstid]['website_title'];
		$new_data['website_url'] = empty($user_profile[$dstid]['website_url']) ? $user_profile[$srcid]['website_url'] : $user_profile[$dstid]['website_url'];
		$new_data['location'] = empty($user_profile[$dstid]['location']) ? $user_profile[$srcid]['location'] : $user_profile[$dstid]['location'];
		$new_data['icq'] = empty($user_profile[$dstid]['icq']) ? $user_profile[$srcid]['icq'] : $user_profile[$dstid]['icq'];
		$new_data['aim'] = empty($user_profile[$dstid]['aim']) ? $user_profile[$srcid]['aim'] : $user_profile[$dstid]['aim'];
		$new_data['yim'] = empty($user_profile[$dstid]['yim']) ? $user_profile[$srcid]['yim'] : $user_profile[$dstid]['yim'];
		$new_data['msn'] = empty($user_profile[$dstid]['msn']) ? $user_profile[$srcid]['msn'] : $user_profile[$dstid]['msn'];
		$new_data['hide_email'] = empty($user_profile[$dstid]['hide_email']) ? $user_profile[$srcid]['hide_email'] : $user_profile[$dstid]['hide_email'];
		$new_data['show_online'] = empty($user_profile[$dstid]['show_online']) ? $user_profile[$srcid]['show_online'] : $user_profile[$dstid]['show_online'];
		$new_data['avatar'] = empty($user_profile[$dstid]['avatar']) ? $user_profile[$srcid]['avatar'] : $user_profile[$dstid]['avatar'];
		$new_data['signature'] = empty($user_profile[$dstid]['signature']) ? $user_profile[$srcid]['signature'] : $user_profile[$dstid]['signature'];
		$new_data['pm_email_notify'] = empty($user_profile[$dstid]['pm_email_notify']) ? $user_profile[$srcid]['pm_email_notify'] : $user_profile[$dstid]['pm_email_notify'];
		$new_data['notify_announcements'] = empty($user_profile[$dstid]['notify_announcements']) ? $user_profile[$srcid]['notify_announcements'] : $user_profile[$dstid]['notify_announcements'];
		$new_data['notify_regularity'] = empty($user_profile[$dstid]['notify_regularity']) ? $user_profile[$srcid]['notify_regularity'] : $user_profile[$dstid]['notify_regularity'];
		$new_data['notify_send_body'] = empty($user_profile[$dstid]['notify_send_body']) ? $user_profile[$srcid]['notify_send_body'] : $user_profile[$dstid]['notify_send_body'];
		$new_data['notify_types'] = empty($user_profile[$dstid]['notify_types']) ? $user_profile[$srcid]['notify_types'] : $user_profile[$dstid]['notify_types'];

		// update the new ID with the combined user data
		updateMemberData($dstid, $new_data);
	}

	// say bu-bye to the old id?
	if (!empty($_SESSION['deluser']))
	{
		require_once($sourcedir . '/Subs-Members.php');
		deleteMembers($srcid);
	}

	// recount cause we just changed thigs up
	if (!empty($_SESSION['deluser']))
		RecountMemberPosts($dstid);
	else
		RecountMemberPosts(array($dstid, $srcid));

	// clean up and move on
	unset($_SESSION['dstid'], $_SESSION['srcid'], $_SESSION['mods_installed'], $_SESSION['steps'], $_SESSION['deluser'], $_SESSION['adjustuser']);
	redirectexit('action=admin;area=toolbox;done=merge');
}

/**
 * toolbox_check_mods()
 *
 * Sees if the mod tables are installed
 * Used during the merge user operation
 *
 * @return
 */
function toolbox_check_mods()
{
	global $smcFunc, $db_prefix;

	$mods_installed = array();

	// Get all the tables related to this smf install
	db_extend();
	$real_prefix = preg_match('~^(`?)(.+?)\\1\\.(.*?)$~', $db_prefix, $match) === 1 ? $match[3] : $db_prefix;
	$smf_tables = $smcFunc['db_list_tables'](false, $real_prefix . '%');

	// well that was easy, now do the mod tables exist?
	$checkfor_mods = array(
		'aeva' => array($real_prefix . 'aeva_media', $real_prefix . 'aeva_comments', $real_prefix . 'aeva_albums'),
		'links' => array($real_prefix . 'links', $real_prefix . 'links_rating'),
		'drafts' => array($real_prefix . 'post_drafts'),
		'bookmarks' => array($real_prefix . 'bookmarks'),
	);

	// do all the needed tables for the mod exist in the master list?
	foreach ($checkfor_mods as $mod_name => $mod_dna)
	{
		if (count(array_intersect($smf_tables, $mod_dna)) == count($mod_dna))
			$mods_installed[] = $mod_name;
	}

	return $mods_installed;
}

/**
 * toolbox_merge_links()
 *
 * Does the merging of SMF Link users
 *
 * @return
 */
function toolbox_merge_links()
{
	global $smcFunc;

	$dstid = $_SESSION['dstid'];
	$srcid = $_SESSION['srcid'];

	// First link ownership
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}links
		SET ID_MEMBER = {int:dstid}
		WHERE ID_MEMBER = {int:srcid}',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid
		)
	);

	// Then link ratings
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}links_rating
		SET ID_MEMBER = {int:dstid}
		WHERE ID_MEMBER = {int:srcid}',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid
		)
	);
}

/**
 * toolbox_merge_bookmarks()
 *
 * Does the mergring of bookmarks
 *
 * @return
 */
function toolbox_merge_bookmarks()
{
	global $smcFunc;

	$dstid = $_SESSION['dstid'];
	$srcid = $_SESSION['srcid'];

	// Then link ratings
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}bookmarks
		SET id_member = {int:dstid}
		WHERE id_member = {int:srcid}',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid
		)
	);
}

/**
 * toolbox_merge_drafts()
 *
 * Merge drafts as well
 *
 * @return
 */
function toolbox_merge_drafts()
{
	global $smcFunc;

	$dstid = $_SESSION['dstid'];
	$srcid = $_SESSION['srcid'];

	// Then link ratings
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}post_drafts
		SET id_member = {int:dstid}
		WHERE id_member = {int:srcid}',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid
		)
	);
}

/**
 * toolbox_merge_aeva()
 *
 * Oofta, merges all the Aeva album information, I hope I have them all !
 *
 * @return
 */
function toolbox_merge_aeva()
{
	global $smcFunc;

	$dstid = $_SESSION['dstid'];
	$srcid = $_SESSION['srcid'];

	// The new owners name is needed in some places, just do it once now
	$request = $smcFunc['db_query']('', '
		SELECT member_name
		FROM {db_prefix}members
		WHERE id_member = {int:dstid}
		LIMIT 1',
		array(
			'dstid' => $dstid,
		)
	);
	list($dstname) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// First album ownership
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}aeva_albums
		SET album_of = {int:dstid}
		WHERE album_of = {int:srcid}',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid
		)
	);

	// And now update album permissions by removing srcid ID from allowed members, allowed_write, denied_write, denied_members
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}aeva_albums
		SET allowed_members = TRIM(BOTH \',\' FROM REPLACE(CONCAT(\',\', allowed_members, \',\'), \',{int:srcid},\', \',{int:dstid},\'))
		WHERE FIND_IN_SET({int:srcid}, allowed_members)',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid
		)
	);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}aeva_albums
		SET allowed_write = TRIM(BOTH \',\' FROM REPLACE(CONCAT(\',\', allowed_write, \',\'), \',{int:srcid},\', \',{int:dstid},\'))
		WHERE FIND_IN_SET({int:srcid}, allowed_write)',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid
		)
	);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}aeva_albums
		SET denied_write = TRIM(BOTH \',\' FROM REPLACE(CONCAT(\',\', denied_write, \',\'), \',{int:srcid},\', \',{int:dstid},\'))
		WHERE FIND_IN_SET({int:srcid}, denied_write)',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid
		)
	);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}aeva_albums
		SET denied_members = TRIM(BOTH \',\' FROM REPLACE(CONCAT(\',\', denied_members, \',\'), \',{int:srcid},\', \',{int:dstid},\'))
		WHERE FIND_IN_SET({int:srcid}, denied_members)',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid
		)
	);

	// who made and who edited the comments
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}aeva_comments
		SET id_member = {int:dstid}
		WHERE id_member = {int:srcid}',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid
		)
	);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}aeva_comments
		SET last_edited_by = {int:dstid}, last_edited_name = {string:dstname}
		WHERE last_edited_by = {int:srcid}',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid,
			'dstname' => $dstname
		)
	);

	// And now the actual album items and who edited them as well
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}aeva_media
		SET id_member = {int:dstid}, member_name = {string:dstname}
		WHERE id_member = {int:srcid}',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid,
			'dstname' => $dstname
		)
	);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}aeva_media
		SET last_edited_by = {int:dstid}, last_edited_name = {string:dstname}
		WHERE last_edited_by = {int:srcid}',
		array(
			'dstid' => $dstid,
			'srcid' => $srcid,
			'dstname' => $dstname
		)
	);
}
?>