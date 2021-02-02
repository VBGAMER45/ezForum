<?php

/**
 * ezForum http://www.ezforum.com
 * Copyright 2011 ezForum
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

/*	This file contains just the functions that turn on and off notifications to
	topics or boards. The following two functions are included:

	void Notify()
		- is called to turn off/on notification for a particular topic.
		- must be called with a topic specified in the URL.
		- uses the Notify template (main sub template.) when called with no sa.
		- the sub action can be 'on', 'off', or nothing for what to do.
		- requires the mark_any_notify permission.
		- upon successful completion of action will direct user back to topic.
		- is accessed via ?action=notify.

	void BoardNotify()
		- is called to turn off/on notification for a particular board.
		- must be called with a board specified in the URL.
		- uses the Notify template. (notify_board sub template.)
		- only uses the template if no sub action is used. (on/off)
		- requires the mark_notify permission.
		- redirects the user back to the board after it is done.
		- is accessed via ?action=notifyboard.

	void AnnouncementsNotify()
		- is called to turn off/on notification for newsletters, announcements, etc.
		- uses the Notify template. (notify_board sub template.)
		- asks for a sub action (on/off) if none was specified.
		- shows a success message after it is done.
		- is accessed via ?action=notifyannoucements.

	void getMemberWithToken(type = '')
		- verifies a subscribe/unsubscribe token, then returns some basic member info.
		- the type parameter is used to indicate what sort of notification the token is for.
		- shows a fatal error message to the user and dies if the token is invalid.

	void createUnsubscribeToken(int memID, string email, type = '', itemID = 0)
		- builds a subscribe/unsubscribe token.
		- each token is unique to a member's email and to the thing they want to unsubscribe from
*/

// Turn on/off notifications...
function Notify()
{
	global $scripturl, $txt, $topic, $user_info, $context, $smcFunc;

	// Are they trying a token-based anonymous request?
	if (isset($_REQUEST['u']) && isset($_REQUEST['token']))
	{
		$member_info = getMemberWithToken('topic');
		$skipCheckSession = true;
	}
	// Otherwise, this is for the current user.
	else
	{
		// Make sure they aren't a guest or something - guests can't really receive notifications!
		is_not_guest();
		isAllowedTo('mark_any_notify');

		$member_info = $user_info;
	}

	// Make sure the topic has been specified.
	if (empty($topic))
		fatal_lang_error('not_a_topic', false);

	// What do we do?  Better ask if they didn't say..
	if (empty($_GET['sa']))
	{
		// Load the template, but only if it is needed.
		loadTemplate('Notify');

		// Find out if they have notification set for this topic already.
		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}log_notify
			WHERE id_member = {int:current_member}
				AND id_topic = {int:current_topic}
			LIMIT 1',
			array(
				'current_member' => $member_info['id'],
				'current_topic' => $topic,
			)
		);
		$context['notification_set'] = $smcFunc['db_num_rows']($request) != 0;
		$smcFunc['db_free_result']($request);

		if ($member_info['id'] !== $user_info['id'])
			$context['notify_info'] = array(
				'u' => $member_info['id'],
				'token' => $_REQUEST['token'],
			);

		// Set the template variables...
		$context['topic_href'] = $scripturl . '?topic=' . $topic . '.' . $_REQUEST['start'];
		$context['start'] = $_REQUEST['start'];
		$context['page_title'] = $txt['notification'];

		return;
	}
	elseif ($_GET['sa'] == 'on')
	{
		if (empty($skipCheckSession))
			checkSession('get');

		// Attempt to turn notifications on.
		$smcFunc['db_insert']('ignore',
			'{db_prefix}log_notify',
			array('id_member' => 'int', 'id_topic' => 'int'),
			array($member_info['id'], $topic),
			array('id_member', 'id_topic')
		);
	}
	else
	{
		if (empty($skipCheckSession))
			checkSession('get');

		// Just turn notifications off.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_notify
			WHERE id_member = {int:current_member}
				AND id_topic = {int:current_topic}',
			array(
				'current_member' => $member_info['id'],
				'current_topic' => $topic,
			)
		);
	}

	// If this request wasn't for the current user, just show a confirmation message.
	if ($member_info['id'] !== $user_info['id'])
	{
		loadTemplate('Notify');
		$context['page_title'] = $txt['notification'];
		$context['sub_template'] = 'notify_pref_changed';
		$context['notify_success_msg'] = sprintf($txt['notifytopic' . ($_GET['sa'] == 'on' ? '_subscribed' : '_unsubscribed')], $member_info['email']);
		return;
	}

	// Send them back to the topic.
	redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

function BoardNotify()
{
	global $scripturl, $txt, $board, $user_info, $context, $smcFunc;

	// Unsubscribing with a token.
	if (isset($_REQUEST['u']) && isset($_REQUEST['token']))
	{
		$member_info = getMemberWithToken('board');
		$skipCheckSession = true;
	}
	// No token, so try with the current user.
	else
	{
		// Permissions are an important part of anything ;).
		is_not_guest();
		isAllowedTo('mark_notify');

		$member_info = $user_info;
	}

	// You have to specify a board to turn notifications on!
	if (empty($board))
		fatal_lang_error('no_board', false);

	// No subaction: find out what to do.
	if (empty($_GET['sa']))
	{
		// We're gonna need the notify template...
		loadTemplate('Notify');

		// Find out if they have notification set for this topic already.
		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}log_notify
			WHERE id_member = {int:current_member}
				AND id_board = {int:current_board}
			LIMIT 1',
			array(
				'current_board' => $board,
				'current_member' => $member_info['id'],
			)
		);
		$context['notification_set'] = $smcFunc['db_num_rows']($request) != 0;
		$smcFunc['db_free_result']($request);

		if ($member_info['id'] !== $user_info['id'])
			$context['notify_info'] = array(
				'u' => $member_info['id'],
				'token' => $_REQUEST['token'],
			);

		// Set the template variables...
		$context['board_href'] = $scripturl . '?board=' . $board . '.' . $_REQUEST['start'];
		$context['start'] = $_REQUEST['start'];
		$context['page_title'] = $txt['notification'];
		$context['sub_template'] = 'notify_board';

		return;
	}
	// Turn the board level notification on....
	elseif ($_GET['sa'] == 'on')
	{
		if (empty($skipCheckSession))
			checkSession('get');

		// Turn notification on.  (note this just blows smoke if it's already on.)
		$smcFunc['db_insert']('ignore',
			'{db_prefix}log_notify',
			array('id_member' => 'int', 'id_board' => 'int'),
			array($member_info['id'], $board),
			array('id_member', 'id_board')
		);
	}
	// ...or off?
	else
	{
		if (empty($skipCheckSession))
			checkSession('get');

		// Turn notification off for this board.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_notify
			WHERE id_member = {int:current_member}
				AND id_board = {int:current_board}',
			array(
				'current_board' => $board,
				'current_member' => $member_info['id'],
			)
		);
	}

	// Probably a guest, so just show a confirmation message.
	if ($member_info['id'] !== $user_info['id'])
	{
		loadTemplate('Notify');
		$context['page_title'] = $txt['notification'];
		$context['sub_template'] = 'notify_pref_changed';
		$context['notify_success_msg'] = sprintf($txt['notifyboard' . ($_GET['sa'] == 'on' ? '_subscribed' : '_unsubscribed')], $member_info['email']);
		return;
	}

	// Back to the board!
	redirectexit('board=' . $board . '.' . $_REQUEST['start']);
}

function AnnouncementsNotify()
{
	global $scripturl, $txt, $board, $user_info, $context, $smcFunc;

	if (isset($_REQUEST['u']) && isset($_REQUEST['token']))
	{
		$member_info = getMemberWithToken('announcements');
		$skipCheckSession = true;
	}
	else
	{
		is_not_guest();
		$member_info = $user_info;
	}

	loadTemplate('Notify');
	$context['page_title'] = $txt['notification'];

	// Ask what they want to do.
	if (empty($_GET['sa']))
	{
		$context['sub_template'] = 'notify_announcements';

		if ($member_info['id'] !== $user_info['id'])
			$context['notify_info'] = array(
				'u' => $member_info['id'],
				'token' => $_REQUEST['token'],
			);

		return;
	}

	// We don't tolerate imposters around here.
	if (empty($skipCheckSession))
		checkSession('get');

	// Update their announcement notification preference.
	updateMemberData($member_info['id'], array('notify_announcements' => $_GET['sa'] == 'on' ? '1' : '0'));

	$context['sub_template'] = 'notify_pref_changed';
	$context['notify_success_msg'] = sprintf($txt['notifyannouncements' . ($_GET['sa'] == 'on' ? '_subscribed' : '_unsubscribed')], $member_info['email']);
}

// Verifies the token, then returns some member info
function getMemberWithToken($type)
{
	global $smcFunc, $board, $topic, $modSettings;

	// Keep it sanitary, folks
	$id_member = !empty($_REQUEST['u']) ? (int) $_REQUEST['u'] : 0;

	// We can't do anything without these
	if (empty($id_member) || empty($_REQUEST['token']))
		fatal_lang_error('unsubscribe_invalid', false);

	// Get the user info we need
	$request = $smcFunc['db_query']('', '
		SELECT id_member AS id, email_address AS email
		FROM {db_prefix}members
		WHERE id_member = {int:id_member}',
		array(
			'id_member' => $id_member,
		)
	);
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('unsubscribe_invalid', false);
	$member_info = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// What token are we expecting?
	$expected_token = createUnsubscribeToken($member_info['id'], $member_info['email'], $type, in_array($type, array('board', 'topic')) && !empty($$type) ? $$type : 0);

	// Don't do anything if the token they gave is wrong
	if ($_REQUEST['token'] !== $expected_token)
		fatal_lang_error('unsubscribe_invalid', false);

	// At this point, we know we have a legitimate unsubscribe request
	return $member_info;
}

function createUnsubscribeToken($memID, $email, $type = '', $itemID = 0)
{
	$token_items = implode(' ', array($memID, $email, $type, $itemID));

	// When the message is public and the key is secret, an HMAC is the appropriate tool.
	$token = hash_hmac('sha256', $token_items, get_auth_secret(), true);

	// When using an HMAC, 80 bits (10 bytes) is plenty for security.
	$token = substr($token, 0, 10);

	// Use base64 (with URL-friendly characters) to make the token shorter.
	return strtr(base64_encode($token), array('+' => '_', '/' => '-', '=' => ''));
}

?>