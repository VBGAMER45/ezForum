<?php
/*
Pretty URLs licence
===================
Copyright (c) 2006-2009 The Pretty URLs Contributors (http://code.google.com/p/prettyurls/source/browse/trunk/CONTRIBUTORS)
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice,
this list of conditions, and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice,
this list of conditions, and the following disclaimer in the
documentation and/or other materials provided with the distribution.
* Neither the name of the author of this software nor the name of
contributors to this software may be used to endorse or promote products
derived from this software without specific prior written consent.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
*/


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
