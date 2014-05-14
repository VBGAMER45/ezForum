<?php
/**
 * Main mentions file for @mentions mod for SMF
 *
 * @author Shitiz Garg <mail@dragooon.net>
 * @copyright 2014 Shitiz Garg
 * @license Simplified BSD (2-Clause) License
 */

/**
 * Callback for integrate_bbc_codes
 *
 * @param array &$bbc_tags
 * @return void
 */
function mentions_bbc(array &$bbc_tags)
{
	global $scripturl;

	$bbc_tags[] = array(
		'tag' => 'member',
		'type' => 'unparsed_equals',
		'before' => '<a href="' . $scripturl . '?action=profile;u=$1" class="mention">@',
		'after' => '</a>',
	);
}

/**
 * Callback for integrate_emnu_buttons
 *
 * @param array &$menu_buttons
 * @return void
 */
function mentions_menu(array &$menu_buttons)
{
	global $txt, $scripturl, $smcFunc, $user_info, $user_settings;

	loadLanguage('Mentions');

	$menu_buttons['profile']['sub_buttons']['mentions'] = array(
		'title' => $txt['mentions'] . (!empty($user_settings['unread_mentions']) ? ' [' . $user_settings['unread_mentions'] . ']' : ''),
		'href' => $scripturl . '?action=profile;area=mentions',
		'show' => true,
	);
	$menu_buttons['profile']['title'] .=  (!empty($user_settings['unread_mentions']) ? ' [' . $user_settings['unread_mentions'] . ']' : '');
}

/**
 * Hook callback for integrate_profile_areas
 *
 * @param array $profile_areas
 * @return void
 */
function mentions_profile_areas(array &$profile_areas)
{
	global $txt;

	loadLanguage('Mentions');

	$profile_areas['info']['areas']['mentions'] = array(
		'label' => $txt['mentions'],
		'enabled' => true,
		'file' => 'Mentions.php',
		'function' => 'Mentions_Profile',
		'permission' => array(
			'own' => 'profile_view_own',
			'any' => 'profile_identity_any',
		),
	);
}

/**
 * Hook callback for integrate_load_permissions
 *
 * @param array &$permissionGroups
 * @param array &$permissionList
 * @param array &$leftPermissionGroups
 * @param array &$hiddenPermissions
 * @param array &$relabelPermissions
 * @return void
 */
function mentions_permissions(array &$permissionGroups, array &$permissionList, array &$leftPermissionGroups, array &$hiddenPermissions, array &$relabelPermissions)
{
	loadLanguage('Mentions');

	$permissionList['membergroup']['mention_member'] = array(false, 'general', 'view_basic_info');
}

/**
 * Parses a post, actually looks for mentions and stores then in $msgOptions
 * We can't actually store them here if we don't have the ID of the post
 *
 * Names are tagged by "@<username>" format in post, but they can contain
 * any type of character up to 60 characters length. So we extract, starting from @
 * up to 60 characters in length (or if we encounter a line break) and make
 * several combination of strings after splitting it by anything that's not a word and join
 * by having the first word, first and second word, first, second and third word and so on and
 * search every name.
 *
 * One potential problem with this is something like "@Admin Space" can match
 * "Admin Space" as well as "Admin", so we sort by length in descending order.
 * One disadvantage of this is that we can only match by one column, hence I've chosen
 * real_name since it's the most obvious.
 *
 * If there's an @ symbol within the name, it is counted in the ongoing string and a new
 * combination string is started from it as well in order to account for all the possibilities.
 * This makes the @ symbol to not be required to be escaped
 *
 * @param array &$msgOptions
 * @param array &$topicOptions
 * @param array &$posterOptions
 * @return void
 */
function mentions_process_post(&$msgOptions, &$topicOptions, &$posterOptions)
{
	global $smcFunc, $user_info;

	// Undo some of the preparse code action
	$body = htmlspecialchars_decode(preg_replace('~<br\s*/?\>~', "\n", str_replace('&nbsp;', ' ', $msgOptions['body'])), ENT_QUOTES);

	$matches = array();
	$string = str_split($body);
	$depth = 0;
	foreach ($string as $char)
	{
		if ($char == '@')
		{
			$depth++;
			$matches[] = array();
		}
		elseif ($char == "\n")
			$depth = 0;

		for ($i = $depth; $i > 0; $i--)
		{
			if (count($matches[count($matches) - $i]) > 60)
			{
				$depth--;
				break;
			}
			$matches[count($matches) - $i][] = $char;
		}
	}

	foreach ($matches as $k => $match)
		$matches[$k] = substr(implode('', $match), 1);

	// Names can have spaces, or they can't...we try to match every possible
	if (empty($matches) || !allowedTo('mention_member'))
		return;

	// Names can have spaces, other breaks, or they can't...we try to match every possible
	// combination.
	$names = array();
	foreach ($matches as $match)
	{
		$match = preg_split('/([^\w])/', $match, -1, PREG_SPLIT_DELIM_CAPTURE);

		for ($i = 1; $i <= count($match); $i++)
			$names[] = implode('', array_slice($match, 0, $i));
	}

	$names = array_unique(array_map('trim', $names));

	// Get the membergroups this message can be seen by
	$request = $smcFunc['db_query']('', '
		SELECT b.member_groups
		FROM {db_prefix}boards AS b
		WHERE id_board = {int:board}',
		array(
			'board' => $topicOptions['board'],
		)
	);
	list ($member_groups) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
	$member_groups = explode(',', $member_groups);
	foreach ($member_groups as $k => $group)
		// Dunno why
		if (strlen($group) == 0)
			unset($member_groups[$k]);

	// Attempt to fetch all the valid usernames along with their required metadata
	$request = $smcFunc['db_query']('', '
		SELECT id_member, real_name, email_mentions, email_address, unread_mentions, id_group, id_post_group, additional_groups
		FROM {db_prefix}members
		WHERE real_name IN ({array_string:names})
		ORDER BY LENGTH(real_name) DESC
		LIMIT {int:count}',
		array(
			'names' => $names,
			'count' => count($names),
		)
	);
	$members = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$members[$row['id_member']] = array(
			'id' => $row['id_member'],
			'real_name' => $row['real_name'],
			'email_mentions' => $row['email_mentions'],
			'email_address' => $row['email_address'],
			'unread_mentions' => $row['unread_mentions'],
			'groups' => array_unique(array_merge(array($row['id_group'], $row['id_post_group']), explode(',', $row['additional_groups']))),
		);
	$smcFunc['db_free_result']($request);

	if (empty($members))
		return;

	// Replace all the tags with BBCode ([member=<id>]<username>[/member])
	$msgOptions['mentions'] = array();
	foreach ($members as $member)
	{
		if (stripos($msgOptions['body'], '@' . $member['real_name']) === false
			|| (!in_array(1, $member['groups']) && count(array_intersect($member['groups'], $member_groups)) == 0))
			continue;

		$msgOptions['body'] = str_ireplace('@' . $member['real_name'], '[member=' . $member['id'] . ']' . $member['real_name'] . '[/member]', $msgOptions['body']);

		// Why would an idiot mention themselves?
		if ($user_info['id'] == $member['id'])
			continue;

		$msgOptions['mentions'][] = $member;
	}
}

/**
 * Takes mention_process_post's arrays and calls mention_store
 *
 * @param array $mentions
 * @param int $id_post
 * @param string $subject
 * @param bool $approved
 * @return void
 */
function mentions_process_store(array $mentions, $id_post, $subject, $approved = true)
{
	global $smcFunc, $txt, $user_info, $scripturl;

	foreach ($mentions as $mention)
	{
		// Store this quickly
		$smcFunc['db_insert']('replace',
			'{db_prefix}log_mentions',
			array('id_post' => 'int', 'id_member' => 'int', 'id_mentioned' => 'int', 'time' => 'int'),
			array($id_post, $user_info['id'], $mention['id'], time()),
			array('id_post', 'id_member', 'id_mentioned')
		);

		if (!empty($mention['email_mentions']) && $approved)
		{
			$replacements = array(
				'POSTNAME' => $subject,
				'MENTIONNAME' => $mention['real_name'],
				'MEMBERNAME' => $user_info['name'],
				'POSTLINK' => $scripturl . '?msg=' . $id_post,
			);

			loadLanguage('Mentions');

			$subject = str_replace(array_keys($replacements), array_values($replacements), $txt['mentions_subject']);
			$body = str_replace(array_keys($replacements), array_values($replacements), $txt['mentions_body']);
			sendmail($mention['email_address'], $subject, $body);
		}

		if ($approved)
			updateMemberData($mention['id'], array('unread_mentions' => $mention['unread_mentions'] + 1));
	}
}

/**
 * Handles approved post's mentions, mostly handles notifications
 *
 * @param array $msgs
 * @return void
 */
function mentions_process_approved(array $msgs)
{
	global $smcFunc, $txt, $scripturl, $txt;

	loadLanguage('Mentions');

	// Grab the appropriate mentions
	$request = $smcFunc['db_query']('', '
		SELECT msg.id_msg, msg.subject, mem.real_name, ment.real_name AS mentioned_name,
			ment.email_mentions, ment.email_address, ment.unread_mentions, ment.id_member
		FROM {db_prefix}log_mentions AS lm
		  INNER JOIN {db_prefix}messages AS msg ON (msg.id_msg = lm.id_post)
		  INNER JOIN {db_prefix}members AS mem ON (lm.id_member = mem.id_member)
		  INNER JOIN {db_prefix}members AS ment ON (lm.id_mentioned = ment.id_member)
		WHERE lm.id_post IN ({array_int:messages})',
		array(
			'messages' => $msgs,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($row['email_mentions']))
		{
			$replacements = array(
				'POSTNAME' => $row['subject'],
				'MENTIONNAME' => $row['mentioned_name'],
				'MEMBERNAME' => $row['real_name'],
				'POSTLINK' => $scripturl . '?msg=' . $row['id_msg'],
			);

			$subject = str_replace(array_keys($replacements), array_values($replacements), $txt['mentions_subject']);
			$body = str_replace(array_keys($replacements), array_values($replacements), $txt['mentions_body']);
			sendmail($row['email_address'], $subject, $body);
		}

		updateMemberData($row['id_member'], array('unread_mentions' => $row['unread_mentions'] + 1));
	}

	$smcFunc['db_free_result']($request);
}

/**
 * JS for mentions while posting
 *
 * @return void
 */
function mentions_post_scripts()
{
	global $settings, $context;

	if (!allowedTo('mention_member'))
		return;

	$context['insert_after_template'] .= '
		<script type="text/javascript">
			var jquery_url = "//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js";
			var atwho_url = "' . $settings['default_theme_url'] . '/scripts/jquery.atwho.js";
			var smf_sessid = "' . $context['session_id'] . '";
			var smf_sessvar = "' . $context['session_var'] . '";
		</script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/mentions.js"></script>
		<link rel="stylesheet" type="text/css" href="' . (file_exists($settings['theme_dir'] . '/css/mentions.css') ? $settings['theme_url'] : $settings['default_theme_url']) . '/css/mentions.css" />';
}

/**
 * Scheduled task for removing mentions older than x days
 *
 * @return void
 */
function scheduled_removeMentions()
{
	global $modSettings, $smcFunc;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}log_mentions
		WHERE time < {int:time}
		  AND unseen = 0',
		array(
			'time' => time() - ((!empty($modSettings['mentions_remove_days']) ? $modSettings['mentions_remove_days'] : 0) * 86400),
		)
	);
}

/**
 * Handles the profile area for mentions
 *
 * @param int $memID
 * @return void
 */
function Mentions_Profile($memID)
{
	global $smcFunc, $sourcedir, $txt, $context, $modSettings, $user_info, $scripturl;

	loadLanguage('Mentions');

	if (!empty($_POST['save']) && $user_info['id'] == $memID)
		updateMemberData($memID, array('email_mentions' => (bool) !empty($_POST['email_mentions'])));

	if ($memID == $user_info['id'])
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}log_mentions
			SET unseen = 0
			WHERE id_mentioned = {int:member}',
			array(
				'member' => $user_info['id'],
			)
		);
		updateMemberData($user_info['id'], array('unread_mentions' => 0));
	}

	$request = $smcFunc['db_query']('', '
		SELECT emaiL_mentions
		FROM {db_prefix}members
		WHERE id_member = {int:member}',
		array(
			'member' => $memID,
		)
	);
	list ($email_mentions) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result'];

	// Set the options for the list component.
	$listOptions = array(
		'id' => 'mentions_list',
		'title' => sprintf($txt['mentions_profile_title'], $context['member']['name']),
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=profile;area=tracking;sa=user;u=' . $memID,
		'default_sort_col' => 'time',
		'get_items' => array(
			'function' => 'list_getMentions',
			'params' => array(
				'lm.id_mentioned = {int:current_member}',
				array('current_member' => $memID),
			),
		),
		'get_count' => array(
			'function' => 'list_getMentionsCount',
			'params' => array(
				'lm.id_mentioned = {int:current_member}',
				array('current_member' => $memID),
			),
		),
		'columns' => array(
			'subject' => array(
				'header' => array(
					'value' => $txt['mentions_post_subject'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?msg=%d">%s</a>',
						'params' => array(
							'id_post' => false,
							'subject' => false,
						),
					),
				),
				'sort' => array(
					'default' => 'msg.subject DESC',
					'reverse' => 'msg.subject ASC',
				),
			),
			'by' => array(
				'header' => array(
					'value' => $txt['mentions_member'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=profile;u=%d">%s</a>',
						'params' => array(
							'id_member' => false,
							'real_name' => false,
						),
					),
				),
			),
			'time' => array(
				'header' => array(
					'value' => $txt['mentions_post_time'],
				),
				'data' => array(
					'db' => 'time',
				),
				'sort' => array(
					'default' => 'lm.time DESC',
					'reverse' => 'lm.time ASC',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=profile;area=mentions',
			'include_sort' => true,
			'include_start' => true,
			'hidden_fields' => array(
				'save' => true,
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'bottom_of_list',
				'value' => '<label for="email_mentions">' . $txt['email_mentions'] . ':</label> <input type="checkbox" name="email_mentions" value="1" onchange="this.form.submit()"' . ($email_mentions ? ' checked' : '') . ' />',
			),
		),
	);

	// Create the list for viewing.
	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['default_list'] = 'mentions_list';
	$context['sub_template'] = 'show_list';
}

function list_getMentionsCount($where, $where_vars = array())
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(lm.id_mentioned) AS mentions_count
		FROM {db_prefix}log_mentions AS lm
		WHERE ' . $where,
		$where_vars
	);
	list ($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $count;
}

function list_getMentions($start, $items_per_page, $sort, $where, $where_vars = array())
{
	global $smcFunc, $txt, $scripturl;

	// Get a list of error messages from this ip (range).
	$request = $smcFunc['db_query']('', '
		SELECT
			lm.id_post, lm.id_mentioned, lm.id_member, lm.time,
			mem.real_name, msg.subject
		FROM {db_prefix}log_mentions AS lm
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = lm.id_member)
			INNER JOIN {db_prefix}messages AS msg ON (msg.id_msg = lm.id_post)
			INNER JOIN {db_prefix}topics AS t ON (t.id_topic = msg.id_topic)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
		WHERE ' . $where . '
			AND {query_see_board}
			AND msg.approved = 1
		ORDER BY ' . $sort . '
		LIMIT ' . $start . ', ' . $items_per_page,
		$where_vars
	);
	$mentions = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$row['time'] = timeformat($row['time']);
		$mentions[] = $row;
	}
	$smcFunc['db_free_result']($request);

	return $mentions;
}

/**
 * Hook callback for integrate_register
 *
 * @param array &$register_options
 * @param array &$theme_vars
 * @return void
 */
function mentions_register(array &$register_options, array &$theme_vars)
{
	global $modSettings;
	$register_options['register_vars']['email_mentions'] = !empty($modSettings['mentions_email_default']) ? 1 : 0;
}

/**
 * Function for managing mention's settings
 *
 * @param bool $return_config
 * @return array
 */
function ModifyMentionsSettings($return_config = false)
{
	global $txt, $scripturl, $context, $settings, $sc, $modSettings, $smcFunc;

	loadLanguage('Mentions');

	$modSettings['mentions_email_default_now'] = 0;

	$config_vars = array(
		array('desc', 'mentions_permissions_notice'),
		array('int', 'mentions_remove_days'),
		array('check', 'mentions_email_default'),
		array('check', 'mentions_email_default_now'),
	);

	if ($return_config)
		return $config_vars;

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();

		if (!empty($_POST['mentions_email_default_now']))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}members
				SET email_mentions = 1',
				array()
			);

		saveDBSettings($config_vars);
		redirectexit('action=admin;area=modsettings;sa=mentions');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=mentions';
	$context['settings_title'] = $txt['mentions'];

	prepareDBSettingContext($config_vars);
}