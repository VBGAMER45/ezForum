<?php
//	Version: 1.0RC; PrettyUrls-Tests
//	A file for filter tests to be placed in

if (!defined('SMF'))
	die('Hacking attempt...');

global $boarddir;
require_once($boarddir . '/SSI.php');

//	Test action URLs
function pretty_actions_test()
{
	global $scripturl, $txt;

	// Just return a few
	return array(
		'<a href="' . $scripturl . '?action=help">' . $txt['help'] . '</a>',
		'<a href="' . $scripturl . '?action=search">' . $txt['search'] . '</a>',
		'<a href="' . $scripturl . '?action=mlist">' . $txt['members_title'] . '</a>',
	);
}

//	Test board URLs
function pretty_boards_test()
{
	global $scripturl, $txt;

	// Get the 3 top boards
	$boards = ssi_topBoards(3, 'array');
	$return = array();
	foreach ($boards as $board)
		$return[] = $board['link'];
	return $return;
}

//	Test profile URLs
function pretty_profiles_test()
{
	global $scripturl, $txt;

	// Get the 3 top posters/spammers
	$boards = ssi_topPoster(3, 'array');
	$return = array();
	foreach ($boards as $board)
		$return[] = $board['link'];
	return $return;
}


//	Test topic URLs
function pretty_topics_test()
{
	global $scripturl, $txt;

	// Get the 3 top topics
	$boards = ssi_topTopicsReplies(3, 'array');
	$return = array();
	foreach ($boards as $board)
		$return[] = $board['link'];
	return $return;
}

?>
