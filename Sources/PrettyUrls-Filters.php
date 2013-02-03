<?php
//	Version: 1.0; PrettyUrls-Filters
//	A file for filter extensions to be placed in

if (!defined('SMF'))
	die('Hacking attempt...');

//	Rewrite the buffer with Pretty URLs!
function pretty_rewrite_buffer($buffer)
{
	global $boardurl, $context, $modSettings, $smcFunc;
	
	if (!empty($modSettings['pretty_bufferusecache']))
	{
		$buffer = pretty_rewrite_buffer_fromcache($buffer);
		return $buffer;
	}
	

	//	Remove the script tags now
	$context['pretty']['scriptID'] = 0;
	$context['pretty']['scripts'] = array();
	$buffer = preg_replace_callback('~<script.+?</script>~s', 'pretty_scripts_remove', $buffer);

	//	Find all URLs in the buffer
	$context['pretty']['search_patterns'][] = '~(<a[^>]+href=|<link[^>]+href=|<form[^>]+?action=)(\"[^\"#]+|\'[^\'#]+)~';
	$urls_query = array();
	$uncached_urls = array();
	foreach ($context['pretty']['search_patterns'] as $pattern)
	{
		preg_match_all($pattern, $buffer, $matches, PREG_PATTERN_ORDER);
		foreach ($matches[2] as $match)
		{
			//	Rip out everything that shouldn't be cached
			$match = preg_replace(array('~^[\"\']|PHPSESSID=[^;]+|(se)?sc=[^;]+|' . $context['session_var'] . '=[^;]+~', '~\"~', '~;+|=;~', '~\?;~', '~\?$|;$|=$~'), array('', '%22', ';', '?', ''), $match);

			// Absolutise relative URLs
			if (!preg_match('~^[a-zA-Z]+:|^#|@~', $match) && SMF != 'SSI')
				$match = $boardurl . '/' . $match;

			// Replace $boardurl with something a little shorter
			$url_id = str_replace($boardurl, '`B', $match);
			
			if (substr($url_id,0,7) == 'mailto:')
				continue;
			if (substr($url_id,0,10) == 'javascript')
				continue;

			$urls_query[] = $url_id;
			$uncached_urls[$url_id] = array(
				'url' => $match,
				'url_id' => $url_id
			);
		}
	}

	//	Procede only if there are actually URLs in the page
	if (count($urls_query) != 0)
	{
		$urls_query = array_keys(array_flip($urls_query));
		//	Retrieve cached URLs
		$context['pretty']['cached_urls'] = array();
		$query = $smcFunc['db_query']('', '
			SELECT url_id, replacement
			FROM {db_prefix}pretty_urls_cache
			WHERE url_id IN ({array_string:urls})',
			array('urls' => $urls_query));
		while ($row = $smcFunc['db_fetch_assoc']($query))
		{
			// Put the full $boardurl back in
			$context['pretty']['cached_urls'][$row['url_id']] = str_replace('`B', $boardurl, $row['replacement']);
			unset($uncached_urls[$row['url_id']]);
		}
		$smcFunc['db_free_result']($query);

		//	If there are any uncached URLs, process them
		if (count($uncached_urls) != 0)
		{
			//	Run each filter callback function on each URL
			$filter_callbacks = unserialize($modSettings['pretty_filter_callbacks']);
			foreach ($filter_callbacks as $callback)
				$uncached_urls = call_user_func($callback, $uncached_urls);

			//	Fill the cached URLs array
			$cache_data = array();
			foreach ($uncached_urls as $url_id => $url)
			{
				if (!isset($url['replacement']))
					$url['replacement'] = $url['url'];
				$url['replacement'] = str_replace("\x12", '\'', $url['replacement']);
				$url['replacement'] = preg_replace(array('~\"~', '~;+|=;~', '~\?;~', '~\?$|;$|=$~'), array('%22', ';', '?', ''), $url['replacement']);
				$context['pretty']['cached_urls'][$url_id] = $url['replacement'];

				// Cache only the URLs which will fit, but replace $boardurl first, that will help!
				if (strlen($url_id) < 256 && strlen($url['replacement']) < 256)
					$cache_data[] = array($url_id, str_replace($boardurl, '`B', $url['replacement']));
			}

			//	Cache these URLs in the database
			if (count($cache_data) != 0)
				$smcFunc['db_insert']('replace',
					'{db_prefix}pretty_urls_cache',
					array('url_id' => 'string', 'replacement' => 'string'),
					$cache_data,
					array('url_id'));
		}

		//	Put the URLs back into the buffer
		$context['pretty']['replace_patterns'][] = '~(<a[^>]+href=|<link[^>]+href=|<form[^>]+?action=)(\"[^\"]+\"|\'[^\']+\')~';
		foreach ($context['pretty']['replace_patterns'] as $pattern)
			$buffer = preg_replace_callback($pattern, 'pretty_buffer_callback', $buffer);
	}

	//	Restore the script tags
	if ($context['pretty']['scriptID'] > 0)
		$buffer = preg_replace_callback("~\x14([0-9]+)\x14~", 'pretty_scripts_restore', $buffer);

	// Return the changed buffer.
	return $buffer;
}

function pretty_rewrite_buffer_fromcache($buffer)
{
	global $boardurl, $context, $modSettings;

	// Function by nend
	// http://www.simplemachines.org/community/index.php?topic=146969.msg3277889#msg3277889
	

	
	//	Remove the script tags now
	$context['pretty']['scriptID'] = 0;
	$context['pretty']['scripts'] = array();
	$buffer = preg_replace_callback('~<script.+?</script>~s', 'pretty_scripts_remove', $buffer);

	//	Find all URLs in the buffer
	$context['pretty']['search_patterns'][] = '~(<a[^>]+href=|<link[^>]+href=|<form[^>]+?action=)(\"[^\"#]+|\'[^\'#]+)~';
	$urls_query = array();
	$uncached_urls = array();
	foreach ($context['pretty']['search_patterns'] as $pattern)
	{
		preg_match_all($pattern, $buffer, $matches, PREG_PATTERN_ORDER);
		foreach ($matches[2] as $match)
		{
			//	Rip out everything that shouldn't be cached
			$match = preg_replace(array('~^[\"\']|PHPSESSID=[^;]+|(se)?sc=[^;]+|' . $context['session_var'] . '=[^;]+~', '~\"~', '~;+|=;~', '~\?;~', '~\?$|;$|=$~'), array('', '%22', ';', '?', ''), $match);

			// Absolutise relative URLs
			if (!preg_match('~^[a-zA-Z]+:|^#|@~', $match) && SMF != 'SSI')
				$match = $boardurl . '/' . $match;

			// Replace $boardurl with something a little shorter
			$url_id = str_replace($boardurl, '`B', $match);

			if (substr($url_id,0,7) == 'mailto:')
				continue;
			if (substr($url_id,0,10) == 'javascript')
				continue;

			$urls_query[] = $url_id;
			$uncached_urls[$url_id] = array(
				'url' => $match,
				'url_id' => $url_id
			);
		}
	}

	//	Procede only if there are actually URLs in the page
	if (count($urls_query) != 0)
	{
		$urls_query = array_keys(array_flip($urls_query));
		//	Retrieve cached URLs
		$context['pretty']['cached_urls'] = array();

		// Load file cache
		$cache_data = array(); //moved this to merge, can just use the cached urls context but this will do for now.
		if (($data = cache_get_data(strtr('pretty-'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"],'/' ,'--'))) != null) {
			foreach($data as $id => $url) {
				$context['pretty']['cached_urls'][$id] = $url;
				$cache_data[] = array($id, $url);
				unset($uncached_urls[$id]);
			}
		}

		//	If there are any uncached URLs, process them
		if (count($uncached_urls) != 0)
		{
			//	Run each filter callback function on each URL
			$filter_callbacks = unserialize($modSettings['pretty_filter_callbacks']);
			foreach ($filter_callbacks as $callback)
				$uncached_urls = call_user_func($callback, $uncached_urls);

			//	Fill the cached URLs array
//			$cache_data = array();
			foreach ($uncached_urls as $url_id => $url)
			{
				if (!isset($url['replacement']))
					$url['replacement'] = $url['url'];
				$url['replacement'] = str_replace("\x12", '\'', $url['replacement']);
				$url['replacement'] = preg_replace(array('~\"~', '~;+|=;~', '~\?;~', '~\?$|;$|=$~'), array('%22', ';', '?', ''), $url['replacement']);
				$context['pretty']['cached_urls'][$url_id] = $url['replacement'];

				// Cache only the URLs which will fit, but replace $boardurl first, that will help!
//				if (strlen($url_id) < 256 && strlen($url['replacement']) < 256 && stristr($url['replacement'], $boardurl))
//				$cache_data[] = array($url_id, str_replace($boardurl, '`B', $url['replacement']));
				$cache_data[] = array($url_id, $url['replacement']);
			}


			// File based caching
			if (count($cache_data) != 0)
				cache_put_data(strtr('pretty-'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"],'/' ,'--'), $cache_data);
		}

		//	Put the URLs back into the buffer
		$context['pretty']['replace_patterns'][] = '~(<a[^>]+href=|<link[^>]+href=|<form[^>]+?action=)(\"[^\"]+\"|\'[^\']+\')~';
		foreach ($context['pretty']['replace_patterns'] as $pattern)
			$buffer = preg_replace_callback($pattern, 'pretty_buffer_callback', $buffer);
	}

	//	Restore the script tags
	if ($context['pretty']['scriptID'] > 0)
		$buffer = preg_replace_callback("~\x14([0-9]+)\x14~", 'pretty_scripts_restore', $buffer);

	// Return the changed buffer.
	return $buffer;
}

//	Remove and save script tags
function pretty_scripts_remove($match)
{
	global $context;

	$context['pretty']['scriptID']++;
	$context['pretty']['scripts'][$context['pretty']['scriptID']] = $match[0];
	return "\x14" . $context['pretty']['scriptID'] . "\x14";
}

//	A callback function to replace the buffer's URLs with their cached URLs
function pretty_buffer_callback($matches)
{
	global $boardurl, $context;

	// Is this URL in an attribute, and so will need new quotes?
	$addQuotes = preg_match('~^[\"\']~', $matches[2]);

	//	Remove those annoying quotes
	$matches[2] = preg_replace('~^[\"\']|[\"\']$~', '', $matches[2]);

	//	Store the parts of the URL that won't be cached so they can be inserted later
	preg_match('~PHPSESSID=[^;#&]+~', $matches[2], $PHPSESSID);
	preg_match('~(se)?sc=[^;#]+~', $matches[2], $sesc);
	preg_match('~' . $context['session_var'] . '=[^;#]+~', $matches[2], $session_var);
	preg_match('~#.*~', $matches[2], $fragment);

	//	Rip out everything that won't have been cached
	$cacheableurl = preg_replace(array('~PHPSESSID=[^;#]+|(se)?sc=[^;#]+|' . $context['session_var'] . '=[^;#]+|#.*$~', '~\"~', '~;+|=;~', '~\?;~', '~\?$|;$|=$~'), array('', '%22', ';', '?', ''), $matches[2]);

	// Absolutise relative URLs
	if (!preg_match('~^[a-zA-Z]+:|@~', $cacheableurl) && !($cacheableurl == '' && isset($fragment[0])) && SMF != 'SSI')
		$cacheableurl = $boardurl . '/' . $cacheableurl;

	// Replace $boardurl with something a little shorter
	$url_id = str_replace($boardurl, '`B', $cacheableurl);

	//	Stitch everything back together, clean it up and return
	$replacement = isset($context['pretty']['cached_urls'][$url_id]) ? $context['pretty']['cached_urls'][$url_id] : $cacheableurl;
	$replacement .= (strpos($replacement, '?') === false ? '?' : ';') . (isset($PHPSESSID[0]) ? $PHPSESSID[0] : '') . ';' . (isset($sesc[0]) ? $sesc[0] : '') . (isset($session_var[0]) ? $session_var[0] : '') . (isset($fragment[0]) ? $fragment[0] : '');
	$replacement = preg_replace(array('~;+|=;~', '~\?;~', '~\?#|;#|=#~', '~\?$|&amp;$|;$|#$|=$~'), array(';', '?', '#', ''), $replacement);
	return $matches[1] . ($addQuotes ? '"' : '') . $replacement . ($addQuotes ? '"' : '');
}

//	Put the script tags back
function pretty_scripts_restore($match)
{
	global $context;

	return $context['pretty']['scripts'][(int) $match[1]];
}

//	Filter miscellaneous action urls
function pretty_urls_actions_filter($urls)
{
	global $boardurl, $context, $modSettings, $scripturl;
	
	$skip_actions = array();
	if (isset($modSettings['pretty_skipactions']))
		$skip_actions = explode(",",$modSettings['pretty_skipactions']);

	$pattern = '`' . $scripturl . '(.*)action=([^;]+)`S';
	$replacement = $boardurl . '/$2/$1';
	foreach ($urls as $url_id => $url)
		if (!isset($url['replacement']))
			if (preg_match($pattern, $url['url'], $matches))
			{
				// Don't rewrite these actions
				if (!empty($skip_actions))
					if (in_array($matches[2],$skip_actions))
						continue;
				
				if (in_array($matches[2], $context['pretty']['action_array']))
					$urls[$url_id]['replacement'] = preg_replace($pattern, $replacement, $url['url']);
			}
	return $urls;
}

//	Filter topic urls
function pretty_urls_topic_filter($urls)
{
	global $context, $modSettings, $scripturl, $smcFunc, $sourcedir;

	$pattern = '`' . $scripturl . '(.*[?;&])topic=([.a-zA-Z0-9]+)(.*)`S';
	$query_data = array();
	foreach ($urls as $url_id => $url)
	{
		//	Get the topic data ready to query the database with
		if (!isset($url['replacement']))
			if (preg_match($pattern, $url['url'], $matches))
			{
				if (strpos($matches[2], '.') !== false)
					list ($urls[$url_id]['topic_id'], $urls[$url_id]['start']) = explode('.', $matches[2]);
				else
				{
					$urls[$url_id]['topic_id'] = $matches[2];
					$urls[$url_id]['start'] = '0';
				}
				$urls[$url_id]['topic_id'] = (int) $urls[$url_id]['topic_id'];
				$urls[$url_id]['match1'] = $matches[1];
				$urls[$url_id]['match3'] = $matches[3];
				$query_data[] = $urls[$url_id]['topic_id'];
			}
	}

	//	Query the database with these topic IDs
	if (count($query_data) != 0)
	{
		//	Look for existing topic URLs
		$query_data = array_keys(array_flip($query_data));
		$topicData = array();
		$unpretty_topics = array();

		$query = $smcFunc['db_query']('', '
			SELECT t.id_topic, t.id_board, p.pretty_url
			FROM {db_prefix}topics AS t
				LEFT JOIN {db_prefix}pretty_topic_urls AS p ON (t.id_topic = p.id_topic)
			WHERE t.id_topic IN ({array_int:topic_ids})',
			array('topic_ids' => $query_data));

		while ($row = $smcFunc['db_fetch_assoc']($query))
			if (isset($row['pretty_url']))
				$topicData[$row['id_topic']] = array(
					'pretty_board' => (isset($context['pretty']['board_urls'][$row['id_board']]) ? $context['pretty']['board_urls'][$row['id_board']] : $row['id_board']),
					'pretty_url' => $row['pretty_url'],
				);
			else
				$unpretty_topics[] = $row['id_topic'];
		$smcFunc['db_free_result']($query);

		//	Generate new topic URLs if required
		if (count($unpretty_topics) != 0)
		{
			require_once($sourcedir . '/Subs-PrettyUrls.php');

			//	Get the topic subjects
			$new_topics = array();
			$new_urls = array();
			$query_check = array();
			$existing_urls = array();
			$add_new = array();

			$query = $smcFunc['db_query']('', '
				SELECT t.id_topic, t.id_board, m.subject
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
				WHERE t.id_topic IN ({array_int:topic_ids})',
				array('topic_ids' => $unpretty_topics));

			while ($row = $smcFunc['db_fetch_assoc']($query))
				$new_topics[] = array(
					'id_topic' => $row['id_topic'],
					'id_board' => $row['id_board'],
					'subject' => $row['subject'],
				);
			$smcFunc['db_free_result']($query);

			//	Generate URLs for each new topic
			foreach ($new_topics as $row)
			{
				$pretty_text = substr(pretty_generate_url($row['subject']), 0, 80);
				//	A topic in the recycle board doesn't deserve a proper URL
				if (($modSettings['recycle_enable'] && $row['id_board'] == $modSettings['recycle_board']) || $pretty_text == '')
					//	Use 'tID_TOPIC' as a pretty url
					$pretty_text = 't' . $row['id_topic'];
				//	No duplicates and no numerical URLs - that would just confuse everyone!
				if (in_array($pretty_text, $new_urls) || is_numeric($pretty_text))
					//	Add suffix '-ID_TOPIC' to the pretty url
					$pretty_text = substr($pretty_text, 0, 70) . '-' . $row['id_topic'];
				$query_check[] = $pretty_text;
				$new_urls[$row['id_topic']] = $pretty_text;
			}

			//	Find any duplicates of existing URLs
			$query = $smcFunc['db_query']('', '
				SELECT pretty_url
				FROM {db_prefix}pretty_topic_urls
				WHERE pretty_url IN ({array_string:new_urls})',
				array('new_urls' => $query_check));
			while ($row = $smcFunc['db_fetch_assoc']($query))
				$existing_urls[] = $row['pretty_url'];
			$smcFunc['db_free_result']($query);

			//	Finalise the new URLs ...
			foreach ($new_topics as $row)
			{
				$pretty_text = $new_urls[$row['id_topic']];
				//	Check if the new URL is already in use
				if (in_array($pretty_text, $existing_urls))
					$pretty_text = substr($pretty_text, 0, 70) . '-' . $row['id_topic'];
				$add_new[] = array($row['id_topic'], $pretty_text);
				//	Add to the original array of topic URLs
				$topicData[$row['id_topic']] = array(
					'pretty_board' => (isset($context['pretty']['board_urls'][$row['id_board']]) ? $context['pretty']['board_urls'][$row['id_board']] : $row['id_board']),
					'pretty_url' => $pretty_text,
				);
			}
			//	... and add them to the database!
			$smcFunc['db_insert']('',
				'{db_prefix}pretty_topic_urls',
				array('id_topic' => 'int', 'pretty_url' => 'string'),
				$add_new,
				array());
		}

		//	Build the replacement URLs
		foreach ($urls as $url_id => $url)
			if (isset($url['topic_id']) && isset($topicData[$url['topic_id']]))
			{
				$start = $url['start'] != '0' || is_numeric($topicData[$url['topic_id']]['pretty_url']) ? $url['start'] . '/' : '';
				$urls[$url_id]['replacement'] = $modSettings['pretty_root_url'] . '/' . $topicData[$url['topic_id']]['pretty_board'] . '/' . $topicData[$url['topic_id']]['pretty_url'] . '/' . $start . $url['match1'] . $url['match3'];
			}
	}
	return $urls;
}

//	Filter board urls
function pretty_urls_board_filter($urls)
{
	global $scripturl, $modSettings, $context;

	$pattern = '`' . $scripturl . '(.*[?;&])board=([.0-9]+)(.*)`S';
	foreach ($urls as $url_id => $url)
		//	Split out the board URLs and replace them
		if (!isset($url['replacement']))
			if (preg_match($pattern, $url['url'], $matches))
			{
				if (strpos($matches[2], '.') !== false)
					list ($board_id, $start) = explode('.', $matches[2]);
				else
				{
					$board_id = $matches[2];
					$start = '0';
				}
				$board_id = (int) $board_id;
				$start = $start != '0' ? $start . '/' : '';
				$urls[$url_id]['replacement'] = $modSettings['pretty_root_url'] . '/' . (isset($context['pretty']['board_urls'][$board_id]) ? $context['pretty']['board_urls'][$board_id] : $board_id) . '/' . $start . $matches[1] . $matches[3];
			}
	return $urls;
}

//	Filter profiles
function pretty_profiles_filter($urls)
{
	global $boardurl, $scripturl, $smcFunc;

	$pattern = '`' . $scripturl . '(.*)action=profile;u=([0-9]+)(.*)`S';
	$query_data = array();
	foreach ($urls as $url_id => $url)
	{
		//	Get the profile data ready to query the database with
		if (!isset($url['replacement']))
			if (preg_match($pattern, $url['url'], $matches))
			{
				$urls[$url_id]['profile_id'] = (int) $matches[2];
				$urls[$url_id]['match1'] = $matches[1];
				$urls[$url_id]['match3'] = $matches[3];
				$query_data[] = $urls[$url_id]['profile_id'];
			}
	}

	//	Query the database with these profile IDs
	if (count($query_data) != 0)
	{
		$query = $smcFunc['db_query']('', '
			SELECT id_member, member_name
			FROM {db_prefix}members
			WHERE id_member IN ({array_int:member_ids})',
			array('member_ids' => $query_data));

		$memberNames = array();
		while ($row = $smcFunc['db_fetch_assoc']($query))
			$memberNames[$row['id_member']] = rawurlencode($row['member_name']);
		$smcFunc['db_free_result']($query);

		//	Build the replacement URLs
		foreach ($urls as $url_id => $url)
			if (isset($url['profile_id']))
				if (strpos($memberNames[$url['profile_id']], '%2F') !== false)
					$urls[$url_id]['replacement'] = $boardurl . '/profile/' . $url['match1'] . 'user=' . $memberNames[$url['profile_id']] . $url['match3'];
				else
					$urls[$url_id]['replacement'] = $boardurl . '/profile/' . $memberNames[$url['profile_id']] . '/' . $url['match1'] . $url['match3'];
	}
	return $urls;
}

?>
