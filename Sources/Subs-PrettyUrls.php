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


//	Version: 1.0RC; Subs-PrettyUrls

if (!defined('SMF'))
	die('Hacking attempt...');

//	Generate a pretty URL from a given text
function pretty_generate_url($text)
{
	global $modSettings, $txt;

	//	Do you know your ABCs?
	$characterHash = array (
		'a'	=>	array ('a', 'A', 'à', 'À', 'á', 'Á', 'â', 'Â', 'ã', 'Ã', 'ä', 'Ä', 'å', 'Å', 'ª', 'ą', 'Ą', 'а', 'А', 'ạ', 'Ạ', 'ả', 'Ả', 'Ầ', 'ầ', 'Ấ', 'ấ', 'Ậ', 'ậ', 'Ẩ', 'ẩ', 'Ẫ', 'ẫ', 'Ă', 'ă', 'Ắ', 'ắ', 'Ẵ', 'ẵ', 'Ặ', 'ặ', 'Ằ', 'ằ', 'Ẳ', 'ẳ', 'а', 'А'),
		'ae'	=>	array ('æ', 'Æ'),
		'b'	=>	array ('b', 'B', 'б', 'Б'),
		'c'	=>	array ('c', 'C', 'ç', 'Ç', 'ć', 'Ć', 'č', 'Č', 'ц', 'Ц'),
		'd'	=>	array ('d', 'D', 'Ð', 'đ', 'Đ', 'ď', 'Ď', 'д', 'Д'),
		'e'	=>	array ('e', 'E', 'è', 'È', 'é', 'É', 'ê', 'Ê', 'ë', 'Ë', 'ę', 'Ę', 'е', 'Е', 'ё', 'Ё', 'э', 'Э', 'Ẹ', 'ẹ', 'Ẻ', 'ẻ', 'Ẽ', 'ẽ', 'Ề', 'ề', 'Ế', 'ế', 'Ệ', 'ệ', 'Ể', 'ể', 'Ễ', 'ễ', 'ε', 'Ε', 'ě', 'Ě', 'е', 'Е'),
		'f'	=>	array ('f', 'F', 'ф', 'Ф'),
		'g'	=>	array ('g', 'G', 'ğ', 'Ğ', 'г', 'Г'),
		'h'	=>	array ('h', 'H', 'х', 'Х'),
		'i'	=>	array ('i', 'I', 'ì', 'Ì', 'í', 'Í', 'î', 'Î', 'ï', 'Ï', 'ı', 'İ', 'Ị', 'ị', 'Ỉ', 'ỉ', 'Ĩ', 'ĩ', 'Ι', 'ι', 'и', 'И'),
		'j'	=>	array ('j', 'J', 'й', 'Й'),
		'k'	=>	array ('k', 'K', 'к', 'К', 'κ', 'Κ', 'к', 'К'),
		'l'	=>	array ('l', 'L', 'ł', 'Ł', 'л', 'Л'),
		'm'	=>	array ('m', 'M', 'м', 'М', 'Μ', 'м', 'М'),
		'n'	=>	array ('n', 'N', 'ñ', 'Ñ', 'ń', 'Ń', 'ň', 'Ň', 'н', 'Н'),
		'o'	=>	array ('o', 'O', 'ò', 'Ò', 'ó', 'Ó', 'ô', 'Ô', 'õ', 'Õ', 'ö', 'Ö', 'ø', 'Ø', 'º', 'о', 'О', 'Ọ', 'ọ', 'Ỏ', 'ỏ', 'Ộ', 'ộ', 'Ố', 'ố', 'Ỗ', 'ỗ', 'Ồ', 'ồ', 'Ổ', 'ổ', 'Ơ', 'ơ', 'Ờ', 'ờ', 'Ớ', 'ớ', 'Ợ', 'ợ', 'Ở', 'ở', 'Ỡ', 'ỡ', 'ο', 'Ο', 'о', 'О'),
		'p'	=>	array ('p', 'P', 'п', 'П'),
		'q'	=>	array ('q', 'Q'),
		'r'	=>	array ('r', 'R', 'ř', 'Ř', 'р', 'Р'),
		's'	=>	array ('s', 'S', 'ş', 'Ş', 'ś', 'Ś', 'š', 'Š', 'с', 'С'),
		'ss'	=>	array ('ß'),
		't'	=>	array ('t', 'T', 'т', 'Т', 'τ', 'Τ', 'ţ', 'Ţ', 'ť', 'Ť', 'т', 'Т'),
		'u'	=>	array ('u', 'U', 'ù', 'Ù', 'ú', 'Ú', 'û', 'Û', 'ü', 'Ü', 'Ụ', 'ụ', 'Ủ', 'ủ', 'Ũ', 'ũ', 'Ư', 'ư', 'Ừ', 'ừ', 'Ứ', 'ứ', 'Ự', 'ự', 'Ử', 'ử', 'Ữ', 'ữ', 'ů', 'Ů', 'у', 'У'),
		'v'	=>	array ('v', 'V', 'в', 'В'),
		'w'	=>	array ('w', 'W'),
		'x'	=>	array ('x', 'X', '×'),
		'y'	=>	array ('y', 'Y', 'ý', 'Ý', 'ÿ', 'Ỳ', 'ỳ', 'Ỵ', 'ỵ', 'Ỷ', 'ỷ', 'Ỹ', 'ỹ', 'ы', 'Ы'),
		'z'	=>	array ('z', 'Z', 'ż', 'Ż', 'ź', 'Ź', 'ž', 'Ž', 'Ζ', 'з', 'З'),
		'jo' => array ('ё', 'Ё'),
		'zh' => array ('ж', 'Ж'),
		'ch' => array ('ч', 'Ч'),
		'sh' => array ('ш', 'Ш'),
		'sch' => array ('щ', 'Щ'),
		'eh' => array ('э', 'Э'),
		'yu' => array ('ю', 'Ю'),
		'ya' => array ('я', 'Я'),
		'' => array ('ъ', 'Ъ', 'ь', 'Ь', '?', '«', '»', ':', '&', '+', '@', '%', '^', '№', '#'),
		'-'	=>	array ('-', ' ', '.', ','),
		'_'	=>	array ('_'),
		'!'	=>	array ('!'),
		'~'	=>	array ('~'),
		'*'	=>	array ('*'),
		"\x12"	=>	array ("'", '"'),
		'('	=>	array ('(', '{', '['),
		')'	=>	array (')', '}', ']'),
		'$'	=>	array ('$'),
		'0'	=>	array ('0'),
		'1'	=>	array ('1', '¹'),
		'2'	=>	array ('2', '²'),
		'3'	=>	array ('3', '³'),
		'4'	=>	array ('4'),
		'5'	=>	array ('5'),
		'6'	=>	array ('6'),
		'7'	=>	array ('7'),
		'8'	=>	array ('8'),
		'9'	=>	array ('9'),
	);

	//	Get or detect the database encoding, firstly from the settings or language files
	if (isset($modSettings['global_character_set']))
		$encoding = strtoupper($modSettings['global_character_set']);
	else if (isset($txt['lang_character_set']))
		$encoding = strtoupper($txt['lang_character_set']);
	//	or try checking UTF-8 conformance
	else if (preg_match('~.~su', $text))
		$encoding = 'UTF-8';
	//	or sadly... we may have to assume Latin-1
	else
		$encoding = 'ISO-8859-1';

	//	If the database encoding isn't UTF-8 and multibyte string functions are available, try converting the text to UTF-8
	if ($encoding != 'UTF-8' && function_exists('mb_convert_encoding'))
		$text = mb_convert_encoding($text, 'UTF-8', $encoding);
	//	Or maybe we can convert with iconv
	else if ($encoding != 'UTF-8' && function_exists('iconv'))
		$text = iconv($encoding, 'UTF-8', $text);
	//	Fix Turkish
	else if ($encoding == 'ISO-8859-9')
	{
		$text = str_replace(array("\xD0", "\xDD", "\xDE", "\xF0", "\xFD", "\xFE"), array('g', 'i', 's', 'g', 'i', 's'), $text);
		$text = utf8_encode($text);
	}
	//	Latin-1 can be converted easily
	else if ($encoding == 'ISO-8859-1')
		$text = utf8_encode($text);

	//	Change the entities back to normal characters
	$text = str_replace(array('&amp;', '&quot;'), array('&', '"'), $text);
	$prettytext = '';

	//	Split up $text into UTF-8 letters
	preg_match_all("~.~su", $text, $characters);
	foreach ($characters[0] as $aLetter)
	{
		foreach ($characterHash as $replace => $search)
		{
			//	Found a character? Replace it!
			if (in_array($aLetter, $search))
			{
				$prettytext .= $replace;
				break;
			}
		}
	}
	//	Remove unwanted '-'s
	$prettytext = preg_replace(array('~^-+|-+$~', '~-+~'), array('', '-'), $prettytext);
	return $prettytext;
}

//	URL maintenance
function pretty_run_maintenance($installing = false)
{
	global $boarddir, $context, $modSettings, $smcFunc;

	$context['pretty']['maintenance_tasks'] = array();

	//	Get the array of actions
	$indexphp = file_get_contents($boarddir . '/index.php');
	preg_match('~actionArray\\s*=\\s*array[^;]+~', $indexphp, $actionArrayText);
	preg_match_all('~\'([^\']+)\'\\s*=>~', $actionArrayText[0], $actionArray, PREG_PATTERN_ORDER);
	$context['pretty']['action_array'] = $actionArray[1];
	
	$context['pretty']['action_array'][] = 'forum';
	
    if (function_exists('call_integration_hook'))
	{
		$dummy = array();
		call_integration_hook('integrate_actions', array(&$dummy));
		$context['pretty']['action_array'] += array_keys($dummy);
	}
	$context['pretty']['maintenance_tasks'][] = 'Updating the array of actions';

	//	Update the list of boards
	//	Get the current pretty board urls, or make new arrays if there are none
	$pretty_board_urls = isset($modSettings['pretty_board_urls']) ? unserialize($modSettings['pretty_board_urls']) : array();
	$pretty_board_lookup_old = isset($modSettings['pretty_board_lookup']) ? unserialize($modSettings['pretty_board_lookup']) : array();

	//	Fix old boards by replacing ' with \x12
	$pretty_board_urls = str_replace("'", "\x12", $pretty_board_urls);
	$pretty_board_lookup = array();
	foreach ($pretty_board_lookup_old as $board => $id)
		$pretty_board_lookup[str_replace("'", "\x12", $board)] = $id;

	//	Fix old topics too
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}pretty_topic_urls
		SET pretty_url = REPLACE(pretty_url, {string:old_quote}, {string:new_quote})',
	array(
		'old_quote' => "'",
		'new_quote' => "\x12",
		'db_error_skip' => true,
	));
	$context['pretty']['maintenance_tasks'][] = 'Fixing any old boards and topics with broken quotes';

	//	Get the board names
	$query = $smcFunc['db_query']('', "
		SELECT id_board, name
		FROM {db_prefix}boards");

	//	Process each board
	while ($row = $smcFunc['db_fetch_assoc']($query))
	{
		//	Don't replace the board urls if they already exist
		if (!isset($pretty_board_urls[$row['id_board']]) || $pretty_board_urls[$row['id_board']] == '' || in_array($row['id_board'], $pretty_board_lookup) === false)
		{
			$pretty_text = pretty_generate_url($row['name']);
			//	We need to have something to refer to this board by...
			if ($pretty_text == '')
				//	... so use 'bID_BOARD'
				$pretty_text = 'b' . $row['id_board'];
			//	Numerical or duplicate URLs aren't allowed!
			if (is_numeric($pretty_text) || isset($pretty_board_lookup[$pretty_text]) || in_array($pretty_text, $context['pretty']['action_array']))
				//	Add suffix '-ID_BOARD' to the pretty url
				$pretty_text .= ($pretty_text != '' ? '-' : 'b') . $row['id_board'];
			//	Update the arrays
			$pretty_board_urls[$row['id_board']] = $pretty_text;
			$pretty_board_lookup[$pretty_text] = $row['id_board'];
		}
		//	Current board URL is the same as an action
		elseif (in_array($pretty_board_urls[$row['id_board']], $context['pretty']['action_array']))
		{
			$pretty_text = $pretty_board_urls[$row['id_board']] . '-' . $row['id_board'];
			$pretty_board_urls[$row['id_board']] = $pretty_text;
			$pretty_board_lookup[$pretty_text] = $row['id_board'];
		}
	}
	$smcFunc['db_free_result']($query);
	$context['pretty']['maintenance_tasks'][] = 'Updating board URLs';

	//	Update the database
	updateSettings(array(
		'pretty_action_array' => serialize($context['pretty']['action_array']),
		'pretty_board_lookup' => serialize($pretty_board_lookup),
		'pretty_board_urls' => serialize($pretty_board_urls),
	));

	//	Update the filter callbacks
	pretty_update_filters($installing);
	$context['pretty']['maintenance_tasks'][] = 'Update the filters';
}

//	Update the database based on the installed filters and build the .htaccess file
function pretty_update_filters($installing = false)
{
	global $boarddir, $boardurl, $context, $modSettings, $smcFunc;

	//	Get the settings
	$prettyFilters = unserialize($modSettings['pretty_filters']);
	$filterSettings = array();
	$rewrites = array();
	foreach ($prettyFilters as $id => $filter)
		//	Get the important data from enabled filters
		if ($filter['enabled'])
		{
			if (isset($filter['filter']))
				$filterSettings[$filter['filter']['priority']] = $filter['filter']['callback'];
			if (isset($filter['rewrite']))
				$rewrites[$filter['rewrite']['priority']] = array(
					'id' => $id,
					'rule' => $filter['rewrite']['rule'],
				);
		}

	// Build the new .htaccess file
	$htaccess = '# PRETTYURLS MOD BEGINS
# Pretty URLs mod
# https://www.smfhacks.com/prettyurls-seo-pro.php
# .htaccess file generated automatically on: ' . date('F j, Y, G:i') . '

RewriteEngine on';

	// Check if we'll need a RewriteBase rule
	// Thanks heaps to Silverstripe!
	// http://open.silverstripe.com/ticket/2903
	$base = dirname($_SERVER['SCRIPT_NAME']);
	if (defined('DIRECTORY_SEPARATOR'))
		$base = str_replace(DIRECTORY_SEPARATOR, '/', $base);
	else
		$base = str_replace("\\", '/', $base);
	if ($base != '.')
		$htaccess .= "\nRewriteBase " . $base;

	//	Output the rules
	ksort($rewrites);
	foreach ($rewrites as $rule)
	{
		$htaccess .= "\n\n# Rules for: " . $rule['id'] . "\n";
		if (is_array($rule['rule']))
			$htaccess .= implode("\n", $rule['rule']);
		else
			$htaccess .= $rule['rule'];
	}
	$htaccess .= "\n\n# PRETTYURLS MOD ENDS";

	//	Fix the Root URL
	if (preg_match('`' . $boardurl . '/(.*)`', $modSettings['pretty_root_url'], $match))
		$htaccess = str_replace('ROOTURL', $match[1] . '/', $htaccess);
	else
		$htaccess = str_replace('ROOTURL', '', $htaccess);

	//	Actions
	if (strpos($htaccess, '#ACTIONS') !== false)
	{
		//	Put them in groups of 8
		$action_array = str_replace('.', '\\.', $context['pretty']['action_array']);
		$groups = array_chunk($action_array, 8);
		//	Construct the rewrite rules
		$lines = array();
		foreach ($groups as $group)
			$lines[] = 'RewriteRule ^('. implode('|', $group) .')/?$ ./index.php?pretty;action=$1 [L,QSA]';
		$actions_rewrite = implode("\n", $lines);
		$htaccess = str_replace('#ACTIONS', $actions_rewrite, $htaccess);
	}

	// Check if there is already a .htaccess file
	if (file_exists($boarddir . '/.htaccess'))
	{
		// If we can't write to it, disable the filters!
		if (!is_writable($boarddir . '/.htaccess'))
		{
			unset($context['template_layers']['pretty_chrome']);
			updateSettings(array('pretty_enable_filters' => '0'));

			if ($installing)
				return;
			else
				fatal_lang_error('pretty_cant_write_htaccess', false);
		}

		// Backup the old .htaccess file
		@copy($boarddir . '/.htaccess', $boarddir . '/.htaccess.backup');

		// Replace the old with the new, if we can
		$oldHtaccess = file_get_contents($boarddir . '/.htaccess');
		$pattern = '~# PRETTYURLS MOD BEGINS.+# PRETTYURLS MOD ENDS~s';
		if (preg_match($pattern, $oldHtaccess, $match))
			$htaccess = str_replace($match[0], $htaccess, $oldHtaccess);
	}

	// Output the new .htaccess file
	$handle = fopen($boarddir . '/.htaccess', 'w');
	fwrite($handle, $htaccess);
	fclose($handle);

	//	Update the settings table
	ksort($filterSettings);
	updateSettings(array('pretty_filter_callbacks' => serialize($filterSettings)));

	//	Clear the URLs cache
	$smcFunc['db_query']('truncate_table', "
		TRUNCATE {db_prefix}pretty_urls_cache");

	//	Don't rewrite anything for this page
	$modSettings['pretty_enable_filters'] = false;
}

//	Format a JSON string
//	From http://au2.php.net/manual/en/function.json-encode.php#80339
function pretty_json($json)
{
	$tab = "    ";
	$new_json = "";
	$indent_level = 0;
	$in_string = false;
	$len = strlen($json);

	for($c = 0; $c < $len; $c++)
	{
		$char = $json[$c];
		if ($char == '"')
		{
			if($c > 0 && $json[$c - 1] != '\\')
				$in_string = !$in_string;
			$new_json .= $char;
		}
		else if ($in_string)
			$new_json .= $char;
		else if ($char == '{' || $char == '[')
		{
			$indent_level++;
			$new_json .= $char . "\n" . str_repeat($tab, $indent_level);
		}
		else if ($char == '}' || $char == ']')
		{
			$indent_level--;
			$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
		}
		else if ($char == ',')
			$new_json .= ",\n" . str_repeat($tab, $indent_level);
		else if ($char == ':')
			$new_json .= ": ";
		else
			$new_json .= $char;
	}

	return $new_json;
}

function InstallPrettyURLS()
{
	global $boardurl, $modSettings;
	
	//	Default filter settings
	$prettyFilters = array(
	'boards' => array(
		'description' => 'Rewrite Board URLs',
		'enabled' => 1,
		'filter' => array(
			'priority' => 35,
			'callback' => 'pretty_urls_board_filter',
		),
		'rewrite' => array(
			'priority' => 50,
			'rule' => array(
				'RewriteRule ^ROOTURL([-_!~*\'()$a-zA-Z0-9]+)/?$ ./index.php?pretty;board=$1.0 [L,QSA]',
				'RewriteRule ^ROOTURL([-_!~*\'()$a-zA-Z0-9]+)/([0-9]*)/?$ ./index.php?pretty;board=$1.$2 [L,QSA]',
			),
		),
		'test_callback' => 'pretty_boards_test',
		'title' => 'Boards',
	),
	'topics' => array(
		'description' => 'Rewrite Topic URLs',
		'enabled' => 1,
		'filter' => array(
			'priority' => 40,
			'callback' => 'pretty_urls_topic_filter',
		),
		'rewrite' => array(
			'priority' => 55,
			'rule' => array(
				'RewriteRule ^ROOTURL([-_!~*\'()$a-zA-Z0-9]+)/([-_!~*\'()$a-zA-Z0-9]+)/?$ ./index.php?pretty;board=$1;topic=$2.0 [L,QSA]',
				'RewriteRule ^ROOTURL([-_!~*\'()$a-zA-Z0-9]+)/([-_!~*\'()$a-zA-Z0-9]+)/([0-9]*|msg[0-9]*|new)/?$ ./index.php?pretty;board=$1;topic=$2.$3 [L,QSA]',
			),
		),
		'test_callback' => 'pretty_topics_test',
		'title' => 'Topics',
	),
	'actions' => array(
		'description' => 'Rewrite Action URLs (ie, index.php?action=something)',
		'enabled' => 0,
		'filter' => array(
			'priority' => 55,
			'callback' => 'pretty_urls_actions_filter',
		),
		'rewrite' => array(
			'priority' => 45,
			'rule' => '#ACTIONS',	//	To be replaced in pretty_update_filters()
		),
		'test_callback' => 'pretty_actions_test',
		'title' => 'Actions',
	),
	'profiles' => array(
		'description' => 'Rewrite Profile URLs. As this uses the Username of an account rather than it\'s Display Name, it may not be desirable to your users.',
		'enabled' => 0,
		'filter' => array(
			'priority' => 50,
			'callback' => 'pretty_profiles_filter',
		),
		'rewrite' => array(
			'priority' => 40,
			'rule' => 'RewriteRule ^profile/([^/]+)/?$ ./index.php?pretty;action=profile;user=$1 [L,QSA]',
		),
		'test_callback' => 'pretty_profiles_test',
		'title' => 'Profiles',
	),
);




//	Add the pretty_root_url and pretty_enable_filters settings:
$pretty_root_url = isset($modSettings['pretty_root_url']) ? $modSettings['pretty_root_url'] : $boardurl;
//$pretty_enable_filters = isset($modSettings['pretty_enable_filters']) ? $modSettings['pretty_enable_filters'] : 0;

//	Update the settings table
updateSettings(array(
	'pretty_enable_filters' => 0,
	'pretty_filters' => serialize($prettyFilters),
	'pretty_root_url' => $pretty_root_url,
	'pretty_urls_installed' => '1',
	'pretty_action_array' => '',
	'pretty_board_urls' => '',
));

//	Run maintenance
pretty_run_maintenance(true);

}


?>
