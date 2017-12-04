<?php

/**
 * ezForum http://www.ezforum.com
 * Copyright 2011-2015 ezForum
 * License: BSD
 *
 * Based on:
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/*	Show a list of members or a selection of members.

	void ViewMembers()
		- the main entrance point for the Manage Members screen.
		- called by ?action=admin;area=viewmembers.
		- requires the moderate_forum permission.
		- loads the ManageMembers template and ManageMembers language file.
		- calls a function based on the given sub-action.

	void ViewMemberlist()
		- shows a list of members.
		- called by ?action=admin;area=viewmembers;sa=all or ?action=admin;area=viewmembers;sa=query.
		- requires the moderate_forum permission.
		- uses the view_members sub template of the ManageMembers template.
		- allows sorting on several columns.
		- handles deletion of selected members.
		- handles the search query sent by ?action=admin;area=viewmembers;sa=search.

	void SearchMembers()
		- search the member list, using one or more criteria.
		- called by ?action=admin;area=viewmembers;sa=search.
		- requires the moderate_forum permission.
		- uses the search_members sub template of the ManageMembers template.
		- form is submitted to action=admin;area=viewmembers;sa=query.

	void MembersAwaitingActivation()
		- show a list of members awaiting approval or activation.
		- called by ?action=admin;area=viewmembers;sa=browse;type=approve or
		  ?action=admin;area=viewmembers;sa=browse;type=activate.
		- requires the moderate_forum permission.
		- uses the admin_browse sub template of the ManageMembers template.
		- allows instant approval or activation of (a selection of) members.
		- list can be sorted on different columns.
		- form submits to ?action=admin;area=viewmembers;sa=approve.

	void AdminApprove()
		- handles the approval, rejection, activation or deletion of members.
		- called by ?action=admin;area=viewmembers;sa=approve.
		- requires the moderate_forum permission.
		- redirects to ?action=admin;area=viewmembers;sa=browse with the same parameters
		  as the calling page.

	int ezcdatediff(int old)
		- nifty function to calculate the number of days ago a given date was.
		- requires a unix timestamp as input, returns an integer.
		- the returned number of days is based on the forum time.
*/

function ViewMembers()
{
	global $txt, $scripturl, $context, $modSettings, $smcFunc;

	$subActions = array(
		'all' => array('ViewMemberlist', 'moderate_forum'),
		'approve' => array('AdminApprove', 'moderate_forum'),
		'browse' => array('MembersAwaitingActivation', 'moderate_forum'),
		'search' => array('SearchMembers', 'moderate_forum'),
		'query' => array('ViewMemberlist', 'moderate_forum'),
	);

	// Default to sub action 'index' or 'settings' depending on permissions.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'all';

	// We know the sub action, now we know what you're allowed to do.
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	// Load the essentials.
	loadLanguage('ManageMembers');
	loadTemplate('ManageMembers');

	// Get counts on every type of activation - for sections and filtering alike.
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS total_members, is_activated
		FROM {db_prefix}members
		WHERE is_activated != {int:is_activated}
		GROUP BY is_activated',
		array(
			'is_activated' => 1,
		)
	);
	$context['activation_numbers'] = array();
	$context['awaiting_activation'] = 0;
	$context['awaiting_approval'] = 0;
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['activation_numbers'][$row['is_activated']] = $row['total_members'];
	$smcFunc['db_free_result']($request);

	foreach ($context['activation_numbers'] as $activation_type => $total_members)
	{
		if (in_array($activation_type, array(0, 2)))
			$context['awaiting_activation'] += $total_members;
		elseif (in_array($activation_type, array(3, 4, 5)))
			$context['awaiting_approval'] += $total_members;
	}

	// For the page header... do we show activation?
	$context['show_activate'] = (!empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1) || !empty($context['awaiting_activation']);

	// What about approval?
	$context['show_approve'] = (!empty($modSettings['registration_method']) && $modSettings['registration_method'] == 2) || !empty($context['awaiting_approval']) || !empty($modSettings['approveAccountDeletion']);

	// Setup the admin tabs.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['admin_members'],
		'help' => 'view_members',
		'description' => $txt['admin_members_list'],
		'tabs' => array(),
	);

	$context['tabs'] = array(
		'viewmembers' => array(
			'label' => $txt['view_all_members'],
			'description' => $txt['admin_members_list'],
			'url' => $scripturl . '?action=admin;area=viewmembers;sa=all',
			'is_selected' => $_REQUEST['sa'] == 'all',
		),
		'search' => array(
			'label' => $txt['mlist_search'],
			'description' => $txt['admin_members_list'],
			'url' => $scripturl . '?action=admin;area=viewmembers;sa=search',
			'is_selected' => $_REQUEST['sa'] == 'search' || $_REQUEST['sa'] == 'query',
		),
		'approve' => array(
			'label' => sprintf($txt['admin_browse_awaiting_approval'], $context['awaiting_approval']),
			'description' => $txt['admin_browse_approve_desc'],
			'url' => $scripturl . '?action=admin;area=viewmembers;sa=browse;type=approve',
			'is_selected' => false,
		),
		'activate' => array(
			'label' => sprintf($txt['admin_browse_awaiting_activate'], $context['awaiting_activation']),
			'description' => $txt['admin_browse_activate_desc'],
			'url' => $scripturl . '?action=admin;area=viewmembers;sa=browse;type=activate',
			'is_selected' => false,
			'is_last' => true,
		),
	);

	// Sort out the tabs for the ones which may not exist!
	if (!$context['show_activate'] && ($_REQUEST['sa'] != 'browse' || $_REQUEST['type'] != 'activate'))
	{
		$context['tabs']['approve']['is_last'] = true;
		unset($context['tabs']['activate']);
	}
	if (!$context['show_approve'] && ($_REQUEST['sa'] != 'browse' || $_REQUEST['type'] != 'approve'))
	{
		if (!$context['show_activate'] && ($_REQUEST['sa'] != 'browse' || $_REQUEST['type'] != 'activate'))
			$context['tabs']['search']['is_last'] = true;
		unset($context['tabs']['approve']);
	}

	$subActions[$_REQUEST['sa']][0]();
}

// View all members.
function ViewMemberlist()
{
	global $txt, $scripturl, $context, $modSettings, $sourcedir, $smcFunc, $user_info;

	// Set the current sub action.
	$context['sub_action'] = $_REQUEST['sa'];

	loadLanguage('Profile');
	// Are we performing a mass action?
	if (isset($_POST['maction_on_members']) && isset($_POST['maction']) && !empty($_POST['delete']))
	{
		checkSession();
		// Clean the input if delete or ban.
		$members = array();
		foreach ($_POST['delete'] as $key => $value)
		{
			// Don't delete nor ban yourself, idiot.
			if ($_POST['maction'] != 'pgroup' && $_POST['maction'] != 'agroup')
			{
				if ($value != $user_info['id'])
					$members[] = $value;
			}
			else
				$members[] = $value;
		}

		if (!empty($members))
		{
			// Are we performing a delete?
			if ($_POST['maction'] == 'delete' && allowedTo('profile_remove_any'))
			{
				foreach ($members as $memID)
				{
					// Code duplication FTW!!!
					// From Profile-Actions.php, function deleteAccount2
					if ($_POST['maction_remove_type'] != 'none' && allowedTo('moderate_forum'))
					{
						// Include RemoveTopics - essential for this type of work!
						require_once($sourcedir . '/RemoveTopic.php');

						// First off we delete any topics the member has started - if they wanted topics being done.
						if ($_POST['maction_remove_type'] == 'topics')
						{
							// Fetch all topics started by this user within the time period.
							$request = $smcFunc['db_query']('', '
								SELECT t.id_topic
								FROM {db_prefix}topics AS t
								WHERE t.id_member_started = {int:selected_member}',
								array(
									'selected_member' => $memID,
								)
							);
							$topicIDs = array();
							while ($row = $smcFunc['db_fetch_assoc']($request))
								$topicIDs[] = $row['id_topic'];
							$smcFunc['db_free_result']($request);

							// Actually remove the topics.
							// !!! This needs to check permissions, but we'll let it slide for now because of moderate_forum already being had.
							removeTopics($topicIDs);
						}

						// Now delete the remaining messages.
						$request = $smcFunc['db_query']('', '
							SELECT m.id_msg
							FROM {db_prefix}messages AS m
								INNER JOIN {db_prefix}topics AS t ON (t.id_topic = m.id_topic
									AND t.id_first_msg != m.id_msg)
							WHERE m.id_member = {int:selected_member}',
							array(
								'selected_member' => $memID,
							)
						);
						// This could take a while... but ya know it's gonna be worth it in the end.
						while ($row = $smcFunc['db_fetch_assoc']($request))
						{
							if (function_exists('apache_reset_timeout'))
								@apache_reset_timeout();

							removeMessage($row['id_msg']);
						}
						$smcFunc['db_free_result']($request);
					}
				}

				// Delete all the selected members.
				require_once($sourcedir . '/Subs-Members.php');
				deleteMembers($members, true);
			}
			// Are we changing groups?
			elseif (($_POST['maction'] == 'pgroup' || $_POST['maction'] == 'agroup') && allowedTo('manage_membergroups'))
			{
				$groups = array('p', 'a');
				foreach($groups as $group){
					if ($_POST['maction'] == $group . 'group' && !empty($_POST['new_membergroup']))
					{
						$type = $group == 'p' ? 'force_primary' : 'only_additional';

						// Change all the selected members' group.
						require_once($sourcedir . '/Subs-Membergroups.php');
						if($_POST['new_membergroup'] != -1)
							addMembersToGroup($members, $_POST['new_membergroup'], $type, true);
						else
							removeMembersFromGroups($members,null,true);
					}
				}
			}
			// Are we banning?
			elseif(($_POST['maction'] == 'ban_names' || $_POST['maction'] == 'ban_mails' || $_POST['maction'] == 'ban_ips' || $_POST['maction'] == 'ban_names_mails') && allowedTo('manage_bans'))
			{
				require_once($sourcedir . '/ManageBans.php');

				$id_ban = $smcFunc['db_query']('', '
					SELECT id_ban_group
					FROM {db_prefix}ban_groups
					WHERE name = \'' . $modSettings['users_mass_action_ban_name'] . '\'
					LIMIT 1',
					array()
				);
				if ($smcFunc['db_num_rows']($id_ban) != 0)
					list($ban_group_id) = $smcFunc['db_fetch_row']($id_ban);
				else
					$ban_group_id = null;

				$smcFunc['db_free_result']($id_ban);

				$_REQUEST['bg'] = $ban_group_id;
				$mactions = $_POST['maction'] == 'ban_names_mails' ? array('ban_names', 'ban_mails') : array($_POST['maction']);

				foreach ($mactions as $maction) {
					$checkIPs = false;
					switch ($maction) {
						case 'ban_names':
							$what = 'member_name';
							$post_ban = 'user';
							$_POST['ban_suggestion'][] = 'user';
							$_POST['bantype'] = 'user_ban';
							break;
						case 'ban_mails':
							$what = 'email_address';
							$post_ban = 'email';
							$_POST['ban_suggestion'][] = 'email';
							$_POST['bantype'] = 'email_ban';
							break;
						case 'ban_ips':
							$checkIPs = true;
							$what = 'member_ip';
							$post_ban = !empty($ban_group_id) ? 'ip' : 'main_ip';
							$_POST['ban_suggestion'][] = 'main_ip';
							$_POST['bantype'] = 'ip_ban';
							break;
						default:
							return false;
					}
					$request = $smcFunc['db_query']('', '
						SELECT id_member, member_name, ' . $what . '
						FROM {db_prefix}members
						WHERE id_member IN ({array_int:id_members})',
						array(
							'id_members' => $members,
					));

					$_POST['expiration'] = 'never';
					$_POST['full_ban'] = 1;
					$_POST['reason'] = !empty($modSettings['users_mass_action_ban_name']) ? $modSettings['users_mass_action_ban_name'] : 'Mass ban';
					$_POST['ban_name'] = !empty($modSettings['users_mass_action_ban_name']) ? $modSettings['users_mass_action_ban_name'] : 'Mass ban';
					$_POST['notes'] = '';

					while ($row = $smcFunc['db_fetch_assoc']($request))
					{
						if ($checkIPs) {
							$ip_parts = ip2range($row[$what]);
							if (users_mass_action_checkExistingTriggerIP($ip_parts, $row[$what]))
								continue;

							$_POST['ip'] = $row[$what];
						}
						$_POST['add_new_trigger'] = !empty($ban_group_id) ? 1 : null;
						$_POST['add_ban'] = empty($ban_group_id) ? 1 : null;
						$_POST[$post_ban] = $row[$what];
						$_REQUEST['u'] = $row['id_member'];

						BanEdit();
						if(empty($ban_group_id)){
							$id_ban = $smcFunc['db_query']('', '
								SELECT id_ban_group
								FROM {db_prefix}ban_groups
								WHERE name = \'Mass bans\'
								LIMIT 1',
								array()
							);
							if($smcFunc['db_num_rows']($id_ban)!=0)
								list($ban_group_id) = $smcFunc['db_fetch_row']($id_ban);
							else
								$ban_group_id = null;
							$smcFunc['db_free_result']($id_ban);
						}
					}
					$smcFunc['db_free_result']($request);
				}
			}
		}
	}

	if ($context['sub_action'] == 'query' && empty($_POST))
	{
		if (!empty($_REQUEST['params']))
		{
			$_POST += safe_unserialize(base64_decode($_REQUEST['params']));
		}
		elseif ($context['browser']['is_ie'] && !empty($_SESSION['params']))
		{
			$_POST += $_SESSION['params'];
			unset($_SESSION['params']);
		}
	}

	// Check input after a member search has been submitted.
	if ($context['sub_action'] == 'query')
	{
		// Retrieving the membergroups and postgroups.
		$context['membergroups'] = array(
			array(
				'id' => 0,
				'name' => $txt['membergroups_members'],
				'can_be_additional' => false
			)
		);
		$context['postgroups'] = array();

		$request = $smcFunc['db_query']('', '
			SELECT id_group, group_name, min_posts
			FROM {db_prefix}membergroups
			WHERE id_group != {int:moderator_group}
			ORDER BY min_posts, CASE WHEN id_group < {int:newbie_group} THEN id_group ELSE 4 END, group_name',
			array(
				'moderator_group' => 3,
				'newbie_group' => 4,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if ($row['min_posts'] == -1)
				$context['membergroups'][] = array(
					'id' => $row['id_group'],
					'name' => $row['group_name'],
					'can_be_additional' => true
				);
			else
				$context['postgroups'][] = array(
					'id' => $row['id_group'],
					'name' => $row['group_name']
				);
		}
		$smcFunc['db_free_result']($request);

		// Some data about the form fields and how they are linked to the database.
		$params = array(
			'mem_id' => array(
				'db_fields' => array('id_member'),
				'type' => 'int',
				'range' => true
			),
			'age' => array(
				'db_fields' => array('birthdate'),
				'type' => 'age',
				'range' => true
			),
			'posts' => array(
				'db_fields' => array('posts'),
				'type' => 'int',
				'range' => true
			),
			'reg_date' => array(
				'db_fields' => array('date_registered'),
				'type' => 'date',
				'range' => true
			),
			'last_online' => array(
				'db_fields' => array('last_login'),
				'type' => 'date',
				'range' => true
			),
			'gender' => array(
				'db_fields' => array('gender'),
				'type' => 'checkbox',
				'values' => array('0', '1', '2'),
			),
			'activated' => array(
				'db_fields' => array('CASE WHEN is_activated IN (1, 11) THEN 1 ELSE 0 END'),
				'type' => 'checkbox',
				'values' => array('0', '1'),
			),
			'membername' => array(
				'db_fields' => array('member_name', 'real_name'),
				'type' => 'string'
			),
			'email' => array(
				'db_fields' => array('email_address'),
				'type' => 'string'
			),
			'website' => array(
				'db_fields' => array('website_title', 'website_url'),
				'type' => 'string'
			),
			'location' => array(
				'db_fields' => array('location'),
				'type' => 'string'
			),
			'ip' => array(
				'db_fields' => array('member_ip'),
				'type' => 'string'
			),
			'messenger' => array(
				'db_fields' => array('icq','yim'),
				'type' => 'string'
			)
		);
		$range_trans = array(
			'--' => '<',
			'-' => '<=',
			'=' => '=',
			'+' => '>=',
			'++' => '>'
		);

		// !!! Validate a little more.

		// Loop through every field of the form.
		$query_parts = array();
		$where_params = array();
		foreach ($params as $param_name => $param_info)
		{
			// Not filled in?
			if (!isset($_POST[$param_name]) || $_POST[$param_name] === '')
				continue;

			// Make sure numeric values are really numeric.
			if (in_array($param_info['type'], array('int', 'age')))
				$_POST[$param_name] = (int) $_POST[$param_name];
			// Date values have to match the specified format.
			elseif ($param_info['type'] == 'date')
			{
				// Check if this date format is valid.
				if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $_POST[$param_name]) == 0)
					continue;

				$_POST[$param_name] = strtotime($_POST[$param_name]);
			}

			// Those values that are in some kind of range (<, <=, =, >=, >).
			if (!empty($param_info['range']))
			{
				// Default to '=', just in case...
				if (empty($range_trans[$_POST['types'][$param_name]]))
					$_POST['types'][$param_name] = '=';

				// Handle special case 'age'.
				if ($param_info['type'] == 'age')
				{
					// All people that were born between $lowerlimit and $upperlimit are currently the specified age.
					$datearray = getdate(forum_time());
					$upperlimit = sprintf('%04d-%02d-%02d', $datearray['year'] - $_POST[$param_name], $datearray['mon'], $datearray['mday']);
					$lowerlimit = sprintf('%04d-%02d-%02d', $datearray['year'] - $_POST[$param_name] - 1, $datearray['mon'], $datearray['mday']);
					if (in_array($_POST['types'][$param_name], array('-', '--', '=')))
					{
						$query_parts[] = ($param_info['db_fields'][0]) . ' > {string:' . $param_name . '_minlimit}';
						$where_params[$param_name . '_minlimit'] = ($_POST['types'][$param_name] == '--' ? $upperlimit : $lowerlimit);
					}
					if (in_array($_POST['types'][$param_name], array('+', '++', '=')))
					{
						$query_parts[] = ($param_info['db_fields'][0]) . ' <= {string:' . $param_name . '_pluslimit}';
						$where_params[$param_name . '_pluslimit'] = ($_POST['types'][$param_name] == '++' ? $lowerlimit : $upperlimit);

						// Make sure that members that didn't set their birth year are not queried.
						$query_parts[] = ($param_info['db_fields'][0]) . ' > {date:dec_zero_date}';
						$where_params['dec_zero_date'] = '0004-12-31';
					}
				}
				// Special case - equals a date.
				elseif ($param_info['type'] == 'date' && $_POST['types'][$param_name] == '=')
				{
					$query_parts[] = $param_info['db_fields'][0] . ' > ' . $_POST[$param_name] . ' AND ' . $param_info['db_fields'][0] . ' < ' . ($_POST[$param_name] + 86400);
				}
				else
					$query_parts[] = $param_info['db_fields'][0] . ' ' . $range_trans[$_POST['types'][$param_name]] . ' ' . $_POST[$param_name];
			}
			// Checkboxes.
			elseif ($param_info['type'] == 'checkbox')
			{
				// Each checkbox or no checkbox at all is checked -> ignore.
				if (!is_array($_POST[$param_name]) || count($_POST[$param_name]) == 0 || count($_POST[$param_name]) == count($param_info['values']))
					continue;

				$query_parts[] = ($param_info['db_fields'][0]) . ' IN ({array_string:' . $param_name . '_check})';
				$where_params[$param_name . '_check'] = $_POST[$param_name];
			}
			else
			{
				// Replace the wildcard characters ('*' and '?') into MySQL ones.
				$parameter = strtolower(strtr($smcFunc['htmlspecialchars']($_POST[$param_name], ENT_QUOTES), array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_')));

				$query_parts[] = '(' . implode( ' LIKE {string:' . $param_name . '_normal} OR ', $param_info['db_fields']) . ' LIKE {string:' . $param_name . '_normal})';
				$where_params[$param_name . '_normal'] = '%' . $parameter . '%';
			}
		}

		// Set up the membergroup query part.
		$mg_query_parts = array();

		// Primary membergroups, but only if at least was was not selected.
		if (!empty($_POST['membergroups'][1]) && count($context['membergroups']) != count($_POST['membergroups'][1]))
		{
			$mg_query_parts[] = 'mem.id_group IN ({array_int:group_check})';
			$where_params['group_check'] = $_POST['membergroups'][1];
		}

		// Additional membergroups (these are only relevant if not all primary groups where selected!).
		if (!empty($_POST['membergroups'][2]) && (empty($_POST['membergroups'][1]) || count($context['membergroups']) != count($_POST['membergroups'][1])))
			foreach ($_POST['membergroups'][2] as $mg)
			{
				$mg_query_parts[] = 'FIND_IN_SET({int:add_group_' . $mg . '}, mem.additional_groups) != 0';
				$where_params['add_group_' . $mg] = $mg;
			}

		// Combine the one or two membergroup parts into one query part linked with an OR.
		if (!empty($mg_query_parts))
			$query_parts[] = '(' . implode(' OR ', $mg_query_parts) . ')';

		// Get all selected post count related membergroups.
		if (!empty($_POST['postgroups']) && count($_POST['postgroups']) != count($context['postgroups']))
		{
			$query_parts[] = 'id_post_group IN ({array_int:post_groups})';
			$where_params['post_groups'] = $_POST['postgroups'];
		}

		// Construct the where part of the query.
		$where = empty($query_parts) ? '1' : implode('
			AND ', $query_parts);

		if ($context['browser']['is_ie'])
		{
			// Cache the results in the session and avoid IE's 2083 character limit.
			$_SESSION['params'] = $_POST;
			$search_params = null;
		}
		else
			$search_params = base64_encode(serialize($_POST));
	}
	else
		$search_params = null;

	// Construct the additional URL part with the query info in it.
	$context['params_url'] = $context['sub_action'] == 'query' ? ';sa=query;params=' . $search_params : '';

	// Get the title and sub template ready..
	$context['page_title'] = $txt['admin_members'];

	$listOptions = array(
		'id' => 'member_list',
		'items_per_page' => $modSettings['defaultMaxMembers'],
		'base_href' => $scripturl . '?action=admin;area=viewmembers' . $context['params_url'],
		'default_sort_col' => 'user_name',
		'get_items' => array(
			'file' => $sourcedir . '/Subs-Members.php',
			'function' => 'list_getMembers',
			'params' => array(
				isset($where) ? $where : '1=1',
				isset($where_params) ? $where_params : array(),
			),
		),
		'get_count' => array(
			'file' => $sourcedir . '/Subs-Members.php',
			'function' => 'list_getNumMembers',
			'params' => array(
				isset($where) ? $where : '1=1',
				isset($where_params) ? $where_params : array(),
			),
		),
		'columns' => array(
			'id_member' => array(
				'header' => array(
					'value' => $txt['member_id'],
				),
				'data' => array(
					'db' => 'id_member',
					'class' => 'windowbg',
					'style' => 'text-align: center;',
				),
				'sort' => array(
					'default' => 'id_member',
					'reverse' => 'id_member DESC',
				),
			),
			'user_name' => array(
				'header' => array(
					'value' => $txt['username'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . strtr($scripturl, array('%' => '%%')) . '?action=profile;u=%1$d">%2$s</a>',
						'params' => array(
							'id_member' => false,
							'member_name' => false,
						),
					),
				),
				'sort' => array(
					'default' => 'member_name',
					'reverse' => 'member_name DESC',
				),
			),
			'display_name' => array(
				'header' => array(
					'value' => $txt['display_name'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . strtr($scripturl, array('%' => '%%')) . '?action=profile;u=%1$d">%2$s</a>',
						'params' => array(
							'id_member' => false,
							'real_name' => false,
						),
					),
				),
				'sort' => array(
					'default' => 'real_name',
					'reverse' => 'real_name DESC',
				),
			),
			'email' => array(
				'header' => array(
					'value' => $txt['email_address'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="mailto:%1$s">%1$s</a>',
						'params' => array(
							'email_address' => true,
						),
					),
					'class' => 'windowbg',
				),
				'sort' => array(
					'default' => 'email_address',
					'reverse' => 'email_address DESC',
				),
			),
			'ip' => array(
				'header' => array(
					'value' => $txt['ip_address'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . strtr($scripturl, array('%' => '%%')) . '?action=trackip;searchip=%1$s">%1$s</a>',
						'params' => array(
							'member_ip' => false,
						),
					),
				),
				'sort' => array(
					'default' => 'INET_ATON(member_ip)',
					'reverse' => 'INET_ATON(member_ip) DESC',
				),
			),
			'last_active' => array(
				'header' => array(
					'value' => $txt['viewmembers_online'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						// Calculate number of days since last online.
						if (empty($rowData[\'last_login\']))
							$difference = $txt[\'never\'];
						else
						{
							$num_days_difference = ezcdatediff($rowData[\'last_login\']);

							// Today.
							if (empty($num_days_difference))
								$difference = $txt[\'viewmembers_today\'];

							// Yesterday.
							elseif ($num_days_difference == 1)
								$difference = sprintf(\'1 %1$s\', $txt[\'viewmembers_day_ago\']);

							// X days ago.
							else
								$difference = sprintf(\'%1$d %2$s\', $num_days_difference, $txt[\'viewmembers_days_ago\']);
						}

						// Show it in italics if they\'re not activated...
						if ($rowData[\'is_activated\'] % 10 != 1)
							$difference = sprintf(\'<em title="%1$s">%2$s</em>\', $txt[\'not_activated\'], $difference);

						return $difference;
					'),
				),
				'sort' => array(
					'default' => 'last_login DESC',
					'reverse' => 'last_login',
				),
			),
			'posts' => array(
				'header' => array(
					'value' => $txt['member_postcount'],
				),
				'data' => array(
					'db' => 'posts',
				),
				'sort' => array(
					'default' => 'posts',
					'reverse' => 'posts DESC',
				),
			),
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $user_info;

						return \'<input type="checkbox" name="delete[]" value="\' . $rowData[\'id_member\'] . \'" class="input_check" \' . ($rowData[\'id_member\'] == $user_info[\'id\'] || $rowData[\'id_group\'] == 1 || in_array(1, explode(\',\', $rowData[\'additional_groups\'])) ? \'disabled="disabled"\' : \'\') . \' />\';
					'),
					'class' => 'windowbg',
					'style' => 'text-align: center',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=viewmembers' . $context['params_url'],
			'include_start' => true,
			'include_sort' => true,
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
				<select name="maction" onchange="this.form.new_membergroup.disabled = (this.options[this.selectedIndex].value != \'pgroup\' && this.options[this.selectedIndex].value != \'agroup\');this.form.maction_remove_type.disabled = (this.options[this.selectedIndex].value != \'delete\');">
					<option value="">--------</option>
					<option value="delete">' . $txt['admin_delete_members'] . '</option>
					<option value="pgroup">' . $txt['admin_change_primary_membergroup'] . '</option>
					<option value="agroup">' . $txt['admin_change_secondary_membergroup'] . '</option>
					<option value="ban_names">' . $txt['admin_ban_usernames'] . '</option>
					<option value="ban_mails">' . $txt['admin_ban_useremails'] . '</option>
					<option value="ban_names_mails">' . $txt['admin_ban_usernames_and_emails'] . '</option>
					<option value="ban_ips">' . $txt['admin_ban_userips'] . '</option>
				</select>
				<select onchange="if(this.value==-1){if(!confirm(\'' . $txt['confirm_remove_membergroup'] . '\')){this.value=0;}}" name="new_membergroup" id="new_membergroup" disabled="disabled">' .
				createGroupsList() . '</select>
				<select name="maction_remove_type" id="maction_remove_type" disabled="disabled">
					<option value="none">' . $txt['deleteAccount_none'] . '</option>
					<option value="posts">' . $txt['deleteAccount_all_posts'] . '</option>
					<option value="topics">' . $txt['deleteAccount_topics'] . '</option>
				</select>
				<input type="submit" name="maction_on_members" value="' . $txt['quick_mod_go'] . '" onclick="return confirm(\'' . $txt['quickmod_confirm'] . '\');" class="button_submit" />',
				'style' => 'text-align: right;',
			),
		),
	);

	// Without not enough permissions, don't show 'delete members' checkboxes.
	if (!allowedTo('profile_remove_any'))
		unset($listOptions['cols']['check'], $listOptions['form'], $listOptions['additional_rows']);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'member_list';
}

// Search the member list, using one or more criteria.
function SearchMembers()
{
	global $context, $txt, $smcFunc;

	// Get a list of all the membergroups and postgroups that can be selected.
	$context['membergroups'] = array(
		array(
			'id' => 0,
			'name' => $txt['membergroups_members'],
			'can_be_additional' => false
		)
	);
	$context['postgroups'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT id_group, group_name, min_posts
		FROM {db_prefix}membergroups
		WHERE id_group != {int:moderator_group}
		ORDER BY min_posts, CASE WHEN id_group < {int:newbie_group} THEN id_group ELSE 4 END, group_name',
		array(
			'moderator_group' => 3,
			'newbie_group' => 4,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($row['min_posts'] == -1)
			$context['membergroups'][] = array(
				'id' => $row['id_group'],
				'name' => $row['group_name'],
				'can_be_additional' => true
			);
		else
			$context['postgroups'][] = array(
				'id' => $row['id_group'],
				'name' => $row['group_name']
			);
	}
	$smcFunc['db_free_result']($request);

	$context['page_title'] = $txt['admin_members'];
	$context['sub_template'] = 'search_members';
}

// List all members who are awaiting approval / activation
function MembersAwaitingActivation()
{
	global $txt, $context, $scripturl, $modSettings, $smcFunc;
	global $sourcedir;

	// Not a lot here!
	$context['page_title'] = $txt['admin_members'];
	$context['sub_template'] = 'admin_browse';
	$context['browse_type'] = isset($_REQUEST['type']) ? $_REQUEST['type'] : (!empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1 ? 'activate' : 'approve');
	if (isset($context['tabs'][$context['browse_type']]))
		$context['tabs'][$context['browse_type']]['is_selected'] = true;

	// Allowed filters are those we can have, in theory.
	$context['allowed_filters'] = $context['browse_type'] == 'approve' ? array(3, 4, 5) : array(0, 2);
	$context['current_filter'] = isset($_REQUEST['filter']) && in_array($_REQUEST['filter'], $context['allowed_filters']) && !empty($context['activation_numbers'][$_REQUEST['filter']]) ? (int) $_REQUEST['filter'] : -1;

	// Sort out the different sub areas that we can actually filter by.
	$context['available_filters'] = array();
	foreach ($context['activation_numbers'] as $type => $amount)
	{
		// We have some of these...
		if (in_array($type, $context['allowed_filters']) && $amount > 0)
			$context['available_filters'][] = array(
				'type' => $type,
				'amount' => $amount,
				'desc' => isset($txt['admin_browse_filter_type_' . $type]) ? $txt['admin_browse_filter_type_' . $type] : '?',
				'selected' => $type == $context['current_filter']
			);
	}

	// If the filter was not sent, set it to whatever has people in it!
	if ($context['current_filter'] == -1 && !empty($context['available_filters'][0]['amount']))
		$context['current_filter'] = $context['available_filters'][0]['type'];

	// This little variable is used to determine if we should flag where we are looking.
	$context['show_filter'] = ($context['current_filter'] != 0 && $context['current_filter'] != 3) || count($context['available_filters']) > 1;

	// The columns that can be sorted.
	$context['columns'] = array(
		'id_member' => array('label' => $txt['admin_browse_id']),
		'member_name' => array('label' => $txt['admin_browse_username']),
		'email_address' => array('label' => $txt['admin_browse_email']),
		'member_ip' => array('label' => $txt['admin_browse_ip']),
		'date_registered' => array('label' => $txt['admin_browse_registered']),
	);

	// Are we showing duplicate information?
	if (isset($_GET['showdupes']))
		$_SESSION['showdupes'] = (int) $_GET['showdupes'];
	$context['show_duplicates'] = !empty($_SESSION['showdupes']);

	// Determine which actions we should allow on this page.
	if ($context['browse_type'] == 'approve')
	{
		// If we are approving deleted accounts we have a slightly different list... actually a mirror ;)
		if ($context['current_filter'] == 4)
			$context['allowed_actions'] = array(
				'reject' => $txt['admin_browse_w_approve_deletion'],
				'ok' => $txt['admin_browse_w_reject'],
			);
		else
			$context['allowed_actions'] = array(
				'ok' => $txt['admin_browse_w_approve'],
				'okemail' => $txt['admin_browse_w_approve'] . ' ' . $txt['admin_browse_w_email'],
				'require_activation' => $txt['admin_browse_w_approve_require_activate'],
				'reject' => $txt['admin_browse_w_reject'],
				'rejectemail' => $txt['admin_browse_w_reject'] . ' ' . $txt['admin_browse_w_email'],
			);
	}
	elseif ($context['browse_type'] == 'activate')
		$context['allowed_actions'] = array(
			'ok' => $txt['admin_browse_w_activate'],
			'okemail' => $txt['admin_browse_w_activate'] . ' ' . $txt['admin_browse_w_email'],
			'delete' => $txt['admin_browse_w_delete'],
			'deleteemail' => $txt['admin_browse_w_delete'] . ' ' . $txt['admin_browse_w_email'],
			'remind' => $txt['admin_browse_w_remind'] . ' ' . $txt['admin_browse_w_email'],
		);

	// Create an option list for actions allowed to be done with selected members.
	$allowed_actions = '
			<option selected="selected" value="">' . $txt['admin_browse_with_selected'] . ':</option>
			<option value="" disabled="disabled">-----------------------------</option>';
	foreach ($context['allowed_actions'] as $key => $desc)
		$allowed_actions .= '
			<option value="' . $key . '">' . $desc . '</option>';

	// Setup the Javascript function for selecting an action for the list.
	$javascript = '
		function onSelectChange()
		{
			if (document.forms.postForm.todo.value == "")
				return;

			var message = "";';

	// We have special messages for approving deletion of accounts - it's surprisingly logical - honest.
	if ($context['current_filter'] == 4)
		$javascript .= '
			if (document.forms.postForm.todo.value.indexOf("reject") != -1)
				message = "' . $txt['admin_browse_w_delete'] . '";
			else
				message = "' . $txt['admin_browse_w_reject'] . '";';
	// Otherwise a nice standard message.
	else
		$javascript .= '
			if (document.forms.postForm.todo.value.indexOf("delete") != -1)
				message = "' . $txt['admin_browse_w_delete'] . '";
			else if (document.forms.postForm.todo.value.indexOf("reject") != -1)
				message = "' . $txt['admin_browse_w_reject'] . '";
			else if (document.forms.postForm.todo.value == "remind")
				message = "' . $txt['admin_browse_w_remind'] . '";
			else
				message = "' . ($context['browse_type'] == 'approve' ? $txt['admin_browse_w_approve'] : $txt['admin_browse_w_activate']) . '";';
	$javascript .= '
			if (confirm(message + " ' . $txt['admin_browse_warn'] . '"))
				document.forms.postForm.submit();
		}';

	$listOptions = array(
		'id' => 'approve_list',
		'items_per_page' => $modSettings['defaultMaxMembers'],
		'base_href' => $scripturl . '?action=admin;area=viewmembers;sa=browse;type=' . $context['browse_type'] . (!empty($context['show_filter']) ? ';filter=' . $context['current_filter'] : ''),
		'default_sort_col' => 'date_registered',
		'get_items' => array(
			'file' => $sourcedir . '/Subs-Members.php',
			'function' => 'list_getMembers',
			'params' => array(
				'is_activated = {int:activated_status}',
				array('activated_status' => $context['current_filter']),
				$context['show_duplicates'],
			),
		),
		'get_count' => array(
			'file' => $sourcedir . '/Subs-Members.php',
			'function' => 'list_getNumMembers',
			'params' => array(
				'is_activated = {int:activated_status}',
				array('activated_status' => $context['current_filter']),
			),
		),
		'columns' => array(
			'id_member' => array(
				'header' => array(
					'value' => $txt['member_id'],
				),
				'data' => array(
					'db' => 'id_member',
					'class' => 'windowbg',
					'style' => 'text-align: center;',
				),
				'sort' => array(
					'default' => 'id_member',
					'reverse' => 'id_member DESC',
				),
			),
			'user_name' => array(
				'header' => array(
					'value' => $txt['username'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . strtr($scripturl, array('%' => '%%')) . '?action=profile;u=%1$d">%2$s</a>',
						'params' => array(
							'id_member' => false,
							'member_name' => false,
						),
					),
				),
				'sort' => array(
					'default' => 'member_name',
					'reverse' => 'member_name DESC',
				),
			),
			'email' => array(
				'header' => array(
					'value' => $txt['email_address'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="mailto:%1$s">%1$s</a>',
						'params' => array(
							'email_address' => true,
						),
					),
					'class' => 'windowbg',
				),
				'sort' => array(
					'default' => 'email_address',
					'reverse' => 'email_address DESC',
				),
			),
			'ip' => array(
				'header' => array(
					'value' => $txt['ip_address'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . strtr($scripturl, array('%' => '%%')) . '?action=trackip;searchip=%1$s">%1$s</a>',
						'params' => array(
							'member_ip' => false,
						),
					),
				),
				'sort' => array(
					'default' => 'INET_ATON(member_ip)',
					'reverse' => 'INET_ATON(member_ip) DESC',
				),
			),
			'hostname' => array(
				'header' => array(
					'value' => $txt['hostname'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $modSettings;

						return host_from_ip($rowData[\'member_ip\']);
					'),
					'class' => 'smalltext',
				),
			),
			'date_registered' => array(
				'header' => array(
					'value' => $txt['date_registered'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						return timeformat($rowData[\'date_registered\']);
					'),
				),
				'sort' => array(
					'default' => 'date_registered DESC',
					'reverse' => 'date_registered',
				),
			),
			'duplicates' => array(
				'header' => array(
					'value' => $txt['duplicates'],
					// Make sure it doesn't go too wide.
					'style' => 'width: 20%',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $scripturl, $txt;

						$member_links = array();
						foreach ($rowData[\'duplicate_members\'] as $member)
						{
							if ($member[\'id\'])
								$member_links[] = \'<a href="\' . $scripturl . \'?action=profile;u=\' . $member[\'id\'] . \'" \' . (!empty($member[\'is_banned\']) ? \'style="color: red;"\' : \'\') . \'>\' . $member[\'name\'] . \'</a>\';
							else
								$member_links[] = $member[\'name\'] . \' (\' . $txt[\'guest\'] . \')\';
						}
						return implode (\', \', $member_links);
					'),
					'class' => 'smalltext',
				),
			),
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="todoAction[]" value="%1$d" class="input_check" />',
						'params' => array(
							'id_member' => false,
						),
					),
					'class' => 'windowbg',
					'style' => 'text-align: center',
				),
			),
		),
		'javascript' => $javascript,
		'form' => array(
			'href' => $scripturl . '?action=admin;area=viewmembers;sa=approve;type=' . $context['browse_type'],
			'name' => 'postForm',
			'include_start' => true,
			'include_sort' => true,
			'hidden_fields' => array(
				'orig_filter' => $context['current_filter'],
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
					<div class="floatleft">
						[<a href="' . $scripturl . '?action=admin;area=viewmembers;sa=browse;showdupes=' . ($context['show_duplicates'] ? 0 : 1) . ';type=' . $context['browse_type'] . (!empty($context['show_filter']) ? ';filter=' . $context['current_filter'] : '') . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . ($context['show_duplicates'] ? $txt['dont_check_for_duplicate'] : $txt['check_for_duplicate']) . '</a>]
					</div>
					<div class="floatright">
						<select name="todo" onchange="onSelectChange();">
							' . $allowed_actions . '
						</select>
						<noscript><input type="submit" value="' . $txt['go'] . '" class="button_submit" /></noscript>
					</div>',
			),
		),
	);

	// Pick what column to actually include if we're showing duplicates.
	if ($context['show_duplicates'])
		unset($listOptions['columns']['email']);
	else
		unset($listOptions['columns']['duplicates']);

	// Only show hostname on duplicates as it takes a lot of time.
	if (!$context['show_duplicates'] || !empty($modSettings['disableHostnameLookup']))
		unset($listOptions['columns']['hostname']);

	// Is there any need to show filters?
	if (isset($context['available_filters']) && count($context['available_filters']) > 1)
	{
		$filterOptions = '
			<strong>' . $txt['admin_browse_filter_by'] . ':</strong>
			<select name="filter" onchange="this.form.submit();">';
		foreach ($context['available_filters'] as $filter)
			$filterOptions .= '
				<option value="' . $filter['type'] . '"' . ($filter['selected'] ? ' selected="selected"' : '') . '>' . $filter['desc'] . ' - ' . $filter['amount'] . ' ' . ($filter['amount'] == 1 ? $txt['user'] : $txt['users']) . '</option>';
		$filterOptions .= '
			</select>
			<noscript><input type="submit" value="' . $txt['go'] . '" name="filter" class="button_submit" /></noscript>';
		$listOptions['additional_rows'][] = array(
			'position' => 'above_column_headers',
			'value' => $filterOptions,
			'style' => 'text-align: center;',
		);
	}

	// What about if we only have one filter, but it's not the "standard" filter - show them what they are looking at.
	if (!empty($context['show_filter']) && !empty($context['available_filters']))
		$listOptions['additional_rows'][] = array(
			'position' => 'above_column_headers',
			'value' => '<strong>' . $txt['admin_browse_filter_show'] . ':</strong> ' . $context['available_filters'][0]['desc'],
			'class' => 'smalltext',
			'style' => 'text-align: left;',
		);

	// Now that we have all the options, create the list.
	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);
}

// Do the approve/activate/delete stuff
function AdminApprove()
{
	global $txt, $context, $scripturl, $modSettings, $sourcedir, $language, $user_info, $smcFunc;

	// First, check our session.
	checkSession();

	require_once($sourcedir . '/Subs-Post.php');

	// We also need to the login languages here - for emails.
	loadLanguage('Login');

	// Sort out where we are going...
	$browse_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : (!empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1 ? 'activate' : 'approve');
	$current_filter = (int) $_REQUEST['orig_filter'];

	// If we are applying a filter do just that - then redirect.
	if (isset($_REQUEST['filter']) && $_REQUEST['filter'] != $_REQUEST['orig_filter'])
		redirectexit('action=admin;area=viewmembers;sa=browse;type=' . $_REQUEST['type'] . ';sort=' . $_REQUEST['sort'] . ';filter=' . $_REQUEST['filter'] . ';start=' . $_REQUEST['start']);

	// Nothing to do?
	if (!isset($_POST['todoAction']) && !isset($_POST['time_passed']))
		redirectexit('action=admin;area=viewmembers;sa=browse;type=' . $_REQUEST['type'] . ';sort=' . $_REQUEST['sort'] . ';filter=' . $current_filter . ';start=' . $_REQUEST['start']);

	// Are we dealing with members who have been waiting for > set amount of time?
	if (isset($_POST['time_passed']))
	{
		$timeBefore = time() - 86400 * (int) $_POST['time_passed'];
		$condition = '
			AND date_registered < {int:time_before}';
	}
	// Coming from checkboxes - validate the members passed through to us.
	else
	{
		$members = array();
		foreach ($_POST['todoAction'] as $id)
			$members[] = (int) $id;
		$condition = '
			AND id_member IN ({array_int:members})';
	}

	// Get information on each of the members, things that are important to us, like email address...
	$request = $smcFunc['db_query']('', '
		SELECT id_member, member_name, real_name, email_address, validation_code, lngfile
		FROM {db_prefix}members
		WHERE is_activated = {int:activated_status}' . $condition . '
		ORDER BY lngfile',
		array(
			'activated_status' => $current_filter,
			'time_before' => empty($timeBefore) ? 0 : $timeBefore,
			'members' => empty($members) ? array() : $members,
		)
	);

	$member_count = $smcFunc['db_num_rows']($request);

	// If no results then just return!
	if ($member_count == 0)
		redirectexit('action=admin;area=viewmembers;sa=browse;type=' . $_REQUEST['type'] . ';sort=' . $_REQUEST['sort'] . ';filter=' . $current_filter . ';start=' . $_REQUEST['start']);

	$member_info = array();
	$members = array();
	// Fill the info array.
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$members[] = $row['id_member'];
		$member_info[] = array(
			'id' => $row['id_member'],
			'username' => $row['member_name'],
			'name' => $row['real_name'],
			'email' => $row['email_address'],
			'language' => empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile'],
			'code' => $row['validation_code']
		);
	}
	$smcFunc['db_free_result']($request);

	// Are we activating or approving the members?
	if ($_POST['todo'] == 'ok' || $_POST['todo'] == 'okemail')
	{
		// Approve/activate this member.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET validation_code = {string:blank_string}, is_activated = {int:is_activated}
			WHERE is_activated = {int:activated_status}' . $condition,
			array(
				'is_activated' => 1,
				'time_before' => empty($timeBefore) ? 0 : $timeBefore,
				'members' => empty($members) ? array() : $members,
				'activated_status' => $current_filter,
				'blank_string' => '',
			)
		);

		// Do we have to let the integration code know about the activations?
		if (!empty($modSettings['integrate_activate']))
		{
			foreach ($member_info as $member)
				call_integration_hook('integrate_activate', array($member['username']));
		}

		// Check for email.
		if ($_POST['todo'] == 'okemail')
		{
			foreach ($member_info as $member)
			{
				$replacements = array(
					'NAME' => $member['name'],
					'USERNAME' => $member['username'],
					'PROFILELINK' => $scripturl . '?action=profile;u=' . $member['id'],
					'FORGOTPASSWORDLINK' => $scripturl . '?action=reminder',
				);

				$emaildata = loadEmailTemplate('admin_approve_accept', $replacements, $member['language']);
				sendmail($member['email'], $emaildata['subject'], $emaildata['body'], null, null, false, 0);
			}
		}
	}
	// Maybe we're sending it off for activation?
	elseif ($_POST['todo'] == 'require_activation')
	{
		require_once($sourcedir . '/Subs-Members.php');

		// We have to do this for each member I'm afraid.
		foreach ($member_info as $member)
		{
			// Generate a random activation code.
			$validation_code = generateValidationCode();

			// Set these members for activation - I know this includes two id_member checks but it's safer than bodging $condition ;).
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}members
				SET validation_code = {string:validation_code}, is_activated = {int:not_activated}
				WHERE is_activated = {int:activated_status}
					' . $condition . '
					AND id_member = {int:selected_member}',
				array(
					'not_activated' => 0,
					'activated_status' => $current_filter,
					'selected_member' => $member['id'],
					'validation_code' => $validation_code,
					'time_before' => empty($timeBefore) ? 0 : $timeBefore,
					'members' => empty($members) ? array() : $members,
				)
			);

			$replacements = array(
				'USERNAME' => $member['name'],
				'ACTIVATIONLINK' => $scripturl . '?action=activate;u=' . $member['id'] . ';code=' . $validation_code,
				'ACTIVATIONLINKWITHOUTCODE' => $scripturl . '?action=activate;u=' . $member['id'],
				'ACTIVATIONCODE' => $validation_code,
			);

			$emaildata = loadEmailTemplate('admin_approve_activation', $replacements, $member['language']);
			sendmail($member['email'], $emaildata['subject'], $emaildata['body'], null, null, false, 0);
		}
	}
	// Are we rejecting them?
	elseif ($_POST['todo'] == 'reject' || $_POST['todo'] == 'rejectemail')
	{
		require_once($sourcedir . '/Subs-Members.php');
		deleteMembers($members);

		// Send email telling them they aren't welcome?
		if ($_POST['todo'] == 'rejectemail')
		{
			foreach ($member_info as $member)
			{
				$replacements = array(
					'USERNAME' => $member['name'],
				);

				$emaildata = loadEmailTemplate('admin_approve_reject', $replacements, $member['language']);
				sendmail($member['email'], $emaildata['subject'], $emaildata['body'], null, null, false, 1);
			}
		}
	}
	// A simple delete?
	elseif ($_POST['todo'] == 'delete' || $_POST['todo'] == 'deleteemail')
	{
		require_once($sourcedir . '/Subs-Members.php');
		deleteMembers($members);

		// Send email telling them they aren't welcome?
		if ($_POST['todo'] == 'deleteemail')
		{
			foreach ($member_info as $member)
			{
				$replacements = array(
					'USERNAME' => $member['name'],
				);

				$emaildata = loadEmailTemplate('admin_approve_delete', $replacements, $member['language']);
				sendmail($member['email'], $emaildata['subject'], $emaildata['body'], null, null, false, 1);
			}
		}
	}
	// Remind them to activate their account?
	elseif ($_POST['todo'] == 'remind')
	{
		foreach ($member_info as $member)
		{
			$replacements = array(
				'USERNAME' => $member['name'],
				'ACTIVATIONLINK' => $scripturl . '?action=activate;u=' . $member['id'] . ';code=' . $member['code'],
				'ACTIVATIONLINKWITHOUTCODE' => $scripturl . '?action=activate;u=' . $member['id'],
				'ACTIVATIONCODE' => $member['code'],
			);

			$emaildata = loadEmailTemplate('admin_approve_remind', $replacements, $member['language']);
			sendmail($member['email'], $emaildata['subject'], $emaildata['body'], null, null, false, 1);
		}
	}

	// Back to the user's language!
	if (isset($current_language) && $current_language != $user_info['language'])
	{
		loadLanguage('index');
		loadLanguage('ManageMembers');
	}

	// Log what we did?
	if (!empty($modSettings['modlog_enabled']) && in_array($_POST['todo'], array('ok', 'okemail', 'require_activation', 'remind')))
	{
		$log_action = $_POST['todo'] == 'remind' ? 'remind_member' : 'approve_member';
		$log_inserts = array();
		foreach ($member_info as $member)
		{
			$log_inserts[] = array(
				time(), 3, $user_info['id'], $user_info['ip'], $log_action,
				0, 0, 0, serialize(array('member' => $member['id'])),
			);
		}
		$smcFunc['db_insert']('',
			'{db_prefix}log_actions',
			array(
				'log_time' => 'int', 'id_log' => 'int', 'id_member' => 'int', 'ip' => 'string-16', 'action' => 'string',
				'id_board' => 'int', 'id_topic' => 'int', 'id_msg' => 'int', 'extra' => 'string-65534',
			),
			$log_inserts,
			array('id_action')
		);
	}

	// Although updateStats *may* catch this, best to do it manually just in case (Doesn't always sort out unapprovedMembers).
	if (in_array($current_filter, array(3, 4)))
		updateSettings(array('unapprovedMembers' => ($modSettings['unapprovedMembers'] > $member_count ? $modSettings['unapprovedMembers'] - $member_count : 0)));

	// Update the member's stats. (but, we know the member didn't change their name.)
	updateStats('member', false);

	// If they haven't been deleted, update the post group statistics on them...
	if (!in_array($_POST['todo'], array('delete', 'deleteemail', 'reject', 'rejectemail', 'remind')))
		updateStats('postgroups', $members);

	redirectexit('action=admin;area=viewmembers;sa=browse;type=' . $_REQUEST['type'] . ';sort=' . $_REQUEST['sort'] . ';filter=' . $current_filter . ';start=' . $_REQUEST['start']);
}

function ezcdatediff($old)
{
	// Get the current time as the user would see it...
	$forumTime = forum_time();

	// Calculate the seconds that have passed since midnight.
	$sinceMidnight = date('H', $forumTime) * 60 * 60 + date('i', $forumTime) * 60 + date('s', $forumTime);

	// Take the difference between the two times.
	$dis = time() - $old;

	// Before midnight?
	if ($dis < $sinceMidnight)
		return 0;
	else
		$dis -= $sinceMidnight;

	// Divide out the seconds in a day to get the number of days.
	return ceil($dis / (24 * 60 * 60));
}


function createGroupsList()
{
    /**
    * User Mass Actions (uma)
    *
    * @package uma
    * @author emanuele
    * @copyright 2011 emanuele, Simple Machines
    * @license http://www.simplemachines.org/about/smf/license.php BSD
    *
    */
	global $context, $smcFunc, $user_info, $sourcedir, $txt;

	$selects = '';

	require_once($sourcedir . '/Profile-Modify.php');
	loadLanguage('Profile');
	profileLoadGroups();

	// Better remove admin membergroup...and set it to a "remove all"
	$context['member_groups'][1] = array(
		'id' => -1,
		'name' => $txt['remove_all'],
		'is_primary' => 0,
	);
	// no primary is tricky...
	$context['member_groups'][0] = array(
		'id' => 0,
		'name' => '',
		'is_primary' => 1,
	);

	foreach($context['member_groups'] as $member_group){
		$selects .= '
			<option value="' . $member_group['id'] . '"' . ($member_group['is_primary'] ? ' selected="selected"' : '') . '>
				' . $member_group['name'] . '
			</option>';
	}
	return $selects;
}

function users_mass_action_checkExistingTriggerIP($ip_array, $fullip = '')
{
    /**
    * User Mass Actions (uma)
    *
    * @package uma
    * @author emanuele
    * @copyright 2011 emanuele, Simple Machines
    * @license http://www.simplemachines.org/about/smf/license.php BSD
    *
    */
	global $smcFunc, $user_info;

	if (count($ip_array) == 4)
		$values = array(
			'ip_low1' => $ip_array[0]['low'],
			'ip_high1' => $ip_array[0]['high'],
			'ip_low2' => $ip_array[1]['low'],
			'ip_high2' => $ip_array[1]['high'],
			'ip_low3' => $ip_array[2]['low'],
			'ip_high3' => $ip_array[2]['high'],
			'ip_low4' => $ip_array[3]['low'],
			'ip_high4' => $ip_array[3]['high'],
		);
	else
		return true;

	// Again...don't ban yourself!!
	if (!empty($fullip) && ($user_info['ip'] == $fullip || $user_info['ip2'] == $fullip))
		return true;

	$request = $smcFunc['db_query']('', '
		SELECT bg.id_ban_group, bg.name
		FROM {db_prefix}ban_groups AS bg
		INNER JOIN {db_prefix}ban_items AS bi ON
			(bi.id_ban_group = bg.id_ban_group)
			AND ip_low1 = {int:ip_low1} AND ip_high1 = {int:ip_high1}
			AND ip_low2 = {int:ip_low2} AND ip_high2 = {int:ip_high2}
			AND ip_low3 = {int:ip_low3} AND ip_high3 = {int:ip_high3}
			AND ip_low4 = {int:ip_low4} AND ip_high4 = {int:ip_high4}
		LIMIT 1',
		$values
	);
	if ($smcFunc['db_num_rows']($request) != 0)
		$ret = true;
	else
		$ret = false;
	$smcFunc['db_free_result']($request);

	return $ret;
}

?>