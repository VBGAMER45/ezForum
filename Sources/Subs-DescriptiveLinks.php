<?php

/**
 * -----------------------------------------------------------------------------------------------
 * Descriptive Links Mod for Simple Machines Forum (SMF) V2.0
 *
 * @package Descriptive Links
 * @author Spuds
 * @license This Source Code is subject to the terms of the Mozilla Public License
 * version 2.0 (the "License"). You can obtain a copy of the License at
 * http://mozilla.org/MPL/2.0/.
 *
 * Instructions for Use:
 * Install the mod and hope it works ...
 * -----------------------------------------------------------------------------------------------
 */

if (!defined('SMF'))
	die('Hacking Attempt...');

/**
 * Searches a post for all links and trys to replace them with the destinations page title
 * - Uses database querys for internal links and web request for external links
 * - Will not change links if they resolve to names in the admin disabled list
 * - truncates long titles per admin panel settings
 * - Works with aeva
 * - updates the link in the link in the message body itself
 * - user permission to allow disabling of this option for a given message.
 *
 * @global type $modSettings
 * @global type $smcFunc
 * @global type $sourcedir
 * @global type $scripturl
 * @global type $boardurl
 * @global type $context
 * @param type $message
 * @param type $id_msg
 * @return type
 */
function Add_title_to_link(&$message, $id_msg = -1)
{
	global $modSettings, $smcFunc, $sourcedir, $scripturl, $boardurl, $context;

	// If asked, lets create a nice title for the link [url=ddd]great title[/url]
	if (!empty($modSettings['descriptivelinks_title_url']))
	{
		// Get the generic names that we will never allow a link to convert to
		$links_title_generic_names = isset($modSettings['descriptivelinks_title_url_generic']) ? explode(',', $modSettings['descriptivelinks_title_url_generic']) : '';

		// Aeva converts links as part of its onposting operation, namely [url]$1[/url] & [url=http://$1]$1[/url]
		// we need to revert these for this mod to work and put them back if needed
		// also we do this if convert URLs (bbc url) is enabled in the admin panel
		if (!empty($modSettings['aeva_enable']) || !empty($modSettings['descriptivelinks_title_bbcurl']))
		{
			// maybe its [url=http://bbb.bbb.bbb]bbb.bbb.bbb[/url]
			$pre_urls = array();
			preg_match_all("~\[url=(http(?:s)?:\/\/(.+?))\](?:http(?:s)?:\/\/)?(.+?)\[/url\]~si" . ($context['utf8'] ? 'u' : ''), $message, $pre_urls, PREG_SET_ORDER);
			foreach ($pre_urls as $url_check)
			{
				if (isset($url_check[2]) && isset($url_check[3]) && ($url_check[2] === $url_check[3]))
				{
					// the link IS the same as the title ... so set it to be a non bbc link so we can work on it
					$url_check[2] = trim((strpos($url_check[2], 'http://') === false && strpos($url_check[2], 'https://') === false) ? 'http://' . $url_check[2] : $url_check[2]);
					$message = str_replace($url_check[0], $url_check[2], $message);
				}
			}

			// maybe it just like [url]bbb.bbb.bbb[/url]
			preg_match_all("~\[url\](http(?:s)?:\/\/(.+?))\[/url\]~si", $message, $pre_urls, PREG_SET_ORDER);
			foreach ($pre_urls as $url_check)
			{
				// Just a bbc link, maybe the user or maybe aeva ... either way back to a non bbc link it goes
				$message = str_replace($url_check[0], $url_check[1], $message);
			}
		}

		// Find all (non bbc) links in this message and wrap them in a custom bbc url tag so we can review em.
		$message = preg_replace('~((?:(?<=[\s>\.\(;\'"]|^)(https?:\/\/))|(?:(?<=[\s>\'<]|^)www\.[^ \[\]\(\)\n\r\t]+)|((?:(?<=[\s\n\r\t]|^))(?:[012]?[0-9]{1,2}\.){3}[012]?[0-9]{1,2})\/)([^ \[\]\(\),"\'<>\n\r\t]+)([^\. \[\]\(\),;"\'<>\n\r\t])|((?:(?<=[\s\n\r\t]|^))(?:[012]?[0-9]{1,2}\.){3}[012]?[0-9]{1,2})~i' . ($context['utf8'] ? 'u' : ''), '[%url]$0[/url%]', $message);

		// Find the special bbc urls that we just created, if any, so we can run through them and get titles
		$urls = array();
		preg_match_all("~\[%url\](.+?)\[/url%\]~ism", $message, $urls);
		if (!empty($urls[0]))
		{
			// timeout on getting the url ... don't want to get stuck waiting
			$timeout = ini_get('default_socket_timeout');
			@ini_set('default_socket_timeout', 3);

			// init
			$conversions = 0;
			$internal = !empty($modSettings['queryless_urls']) ? $boardurl : $scripturl;
			require_once ($sourcedir . '/Subs-Package.php');

			// Look at all these links !
			foreach ($urls[1] as $url)
			{
				// Make sure the link is lower case and leads with http:// so fetch web data does not drop a spacely space sprocket
				$url_temp = str_replace(array('HTTP://', 'HTTPS://'), array('http://', 'https://'), $url);
				$url_return = $url_modified = trim((strpos($url_temp, 'http://') === false && strpos($url_temp, 'https://') === false) ? 'http://' . $url_temp : $url_temp);

				// make sure there is a trailing '/' *when needed* so fetch_web_data does not blow a cogswell cog
				$urlinfo = parse_url($url_modified);
				if (!isset($urlinfo['path']))
					$url_modified .= '/';

				// If our counter has exceeded the allowed number of conversions then put the remaining urls back to what they were and finish
				if (!empty($modSettings['descriptivelinks_title_url_count']) && $conversions++ >= $modSettings['descriptivelinks_title_url_count'])
				{
					if (isset($modSettings['aeva_enable']))
						$message = preg_replace('`\[%url\]' . preg_quote($url) . '\[/url%\]`', '[url=' . $url_return . ']' . $url . '[/url]', $message);
					else
						$message = preg_replace('`\[%url\]' . preg_quote($url) . '\[/url%\]`', $url, $message);
					continue;
				}

				// Get the title from the web or if an internal link from the database ;)
				$request = false;
				if (stripos($url_modified, $internal) !== false)
				{
					// internal link it is, give the counter back, its a freebie
					if (!empty($modSettings['descriptivelinks_title_internal']))
					{
						$request = Load_topic_subject($url_modified);
						$conversions--;
					}
				}
				else
				{
					// make sure this has the DNA of an html link and not a file
					$check = isset($urlinfo['path']) ? pathinfo($urlinfo['path']) : array();

					// looks like an extesion, 4 or less characters, then it needs to be htmlish
					if (isset($check['extension']) && !isset($check['extension'][4]) && (!in_array($check['extension'], array('htm', 'html', '', '//', 'php'))))
						$request = false;
					else
						// external links are good too, but protect against those double encoded pasted links
						$request = fetch_web_data(un_htmlspecialchars(un_htmlspecialchars($url_modified)));
				}

				// request went through and there is a page title in the result
				if ($request !== false && preg_match('~<title>(.+?)</title>~ism', $request, $matches))
				{
					// Decode and undo htmlspecial first so we can clean this dirty baby
					$title = trim(html_entity_decode(un_htmlspecialchars($matches[1])));

					// remove crazy stuff we find in title tags, what are those web masterbaters thinking?
					$title = str_replace(array('&mdash;', "\n", "\t"), array('-', ' ', ' '), $title);
					$title = preg_replace('~\s{2,30}~', ' ', $title);

					// Utf8 chars in the title but not utf8 on the board?
					$title = (!$context['utf8'] && mb_detect_encoding($title, 'UTF-8', true)) ? utf8_decode($title) : $title;

					// Some titles are just tooooooooo long
					$title = wordwrap($title, $modSettings['descriptivelinks_title_url_length'], '<br />', true);
					$junk = explode('<br />', $title, 2);
					$title = trim($junk[0]);

					// Make sure we did not get a turd title, makes the link even worse, plus no one likes turds
					if (!empty($title) && array_search(strtolower($title), $links_title_generic_names) === false) 
					{
						// protect special characters and our database
						$title = $smcFunc['htmlspecialchars'](stripslashes($title), ENT_QUOTES);

						// Update the link with the title we found
						$message = preg_replace('`\[%url\]' . preg_quote($url) . '\[/url%\]`', '[url=' . $url_return . ']' . $title . '[/url]', $message);
					}
					else
					{
						// generic title, like welcome, or home, etc ... lets set things back to the way they were
						if (isset($modSettings['aeva_enable']))
							$message = preg_replace('`\[%url\]' . preg_quote($url) . '\[/url%\]`', '[url=' . $url_return . ']' . $url . '[/url]', $message);
						else
							$message = preg_replace('`\[%url\]' . preg_quote($url) . '\[/url%\]`', $url, $message);
					}
				}
				else
				{
					// No title or an error, back to the original we go...
					if (isset($modSettings['aeva_enable']))
						$message = preg_replace('`\[%url\]' . preg_quote($url) . '\[/url%\]`', '[url=' . $url_return . ']' . $url . '[/url]', $message);
					else
						$message = preg_replace('`\[%url\]' . preg_quote($url) . '\[/url%\]`', $url, $message);
				}

				// pop the connection to keep it alive
				$server_version = $smcFunc['db_server_info']();
			}

			// Put the server socket timeout back to what is was originally
			@ini_set('default_socket_timeout', $timeout);
		}
	}
	return;
}

/**
 * Called by Add_title_to_link to resolve the name of internal links
 * - returns the topic or post subject if its a message link
 * - returns the board name if its a board link
 *
 * @global type $smcFunc
 * @global type $scripturl
 * @global type $txt
 * @global type $context
 * @param type $url
 * @return boolean|string
 */
function Load_topic_subject($url)
{
	global $smcFunc, $scripturl, $txt, $context, $modSettings, $user_info;

	// lets pull out the topic number and possibly a message number for this link
	//
	// http://xxx/index.php?topic=5.0
	// http://xxx/index.php/topic,5.msg9.html#msg9
	// -or-
	// http://xxx/index.php/topic,5.0.html
	// http://xxx/index.php?topic=5.msg10#msg10
	// -or-
	// http://xxx/index.php?board=1.0
	//
	$pattern_message = preg_quote($scripturl) . '[\/?]{1}topic[\=,]{1}(\d{1,8})(.msg\d{1,8})?';
	$pattern_board = preg_quote($scripturl) . '[\/?]{1}board[\=,]{1}(\d{1,8})';
	$title = false;

	// Find the topic or message number in this link
	$match = array();
	if (preg_match('`' . $pattern_message . '`i' . ($context['utf8'] ? 'u' : ''), $url, $match))
	{
		// found the topic number .... lets get the subject
		if (isset($match[2]))
			$match[2] = str_replace('.msg', '', $match[2]);
		else
			$match[2] = '';

		// off to the database we go, convert this link to the message title, check for any hackyness as well, such as
		// the message is on a board they can see, not in the recycle bin, is approved, etc, so we show only what they can see.
		$request = $smcFunc['db_query']('', '
			SELECT
				m.subject
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = ' . (($match[2] != '') ? '{int:message_id}' : 't.id_first_msg') . ')
				LEFT JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)
			WHERE t.id_topic = {int:topic_id} && {query_wanna_see_board}' . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? '
			AND b.id_board != {int:recycle_board}' : '') . '
			AND m.approved = {int:is_approved}
			LIMIT 1',
			array(
				'topic_id' => $match[1],
				'message_id' => $match[2],
				'recycle_board' => $modSettings['recycle_board'],
				'is_approved' => 1,
			)
		);

		// Hummm bad info in the link
		if ($smcFunc['db_num_rows']($request) == 0)
			return false;

		// Found the topic data, load the subject er I mean title !
		list($title) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		// clean it up a bit
		$title = trim(str_replace($txt['response_prefix'], '', $title));
		$title = '<title>' . $title . '</title>';
	}
	elseif (preg_match('`' . $pattern_board . '`i' . ($context['utf8'] ? 'u' : ''), $url, $match))
	{
		// found a board number .... lets get the board name
		$request = $smcFunc['db_query']('', '
			SELECT
				b.name
			FROM {db_prefix}boards as b
			WHERE b.id_board = {int:board_id} AND {query_wanna_see_board}' . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? '
			AND b.id_board != {int:recycle_board}' : '') . '
			LIMIT 1',
			array(
				'board_id' => $match[1],
				'recycle_board' => $modSettings['recycle_board'],
			)
		);

		// nothing found, nothing gained
		if ($smcFunc['db_num_rows']($request) == 0)
			return false;

		// Found the board name, load the name for the title
		list($title) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		// and make it look good
		$title = trim(str_replace($txt['response_prefix'], '', $title));
		$title = '<title>' . $title . '</title>';
	}

	return $title;
}
?>