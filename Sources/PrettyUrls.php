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




//	Version: 1.0RC; PrettyUrls

if (!defined('SMF'))
	die('Hacking attempt...');

//	Shell for all the Pretty URL interfaces
function PrettyInterface()
{
	global $context, $scripturl, $settings, $txt;

	//	Keep the critters out
	isAllowedTo('admin_forum');

	//	Default templating stuff
	loadTemplate('PrettyUrls');
	if (loadLanguage('PrettyUrls') == false)
		loadLanguage('PrettyUrls', 'english');

	//	Shiny chrome interface
	$context['template_layers']['pretty_chrome'] = 'pretty_chrome';
	$context['html_headers'] .= '
	<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/pretty/chrome.css" media="screen,projection" />';
	$context['pretty']['chrome'] = array(
		'admin' => true,
		'menu' => array(
			'settings' => array(
				'href' => $scripturl . '?action=admin;area=pretty;sa=settings',
				'title' => $txt['pretty_chrome_menu_settings'],
			),
			'maintenance' => array(
				'href' => $scripturl . '?action=admin;area=pretty;sa=maintenance',
				'title' => $txt['pretty_chrome_menu_maintenance'],
			),
		),
		'title' => $txt['pretty_chrome_title'],
	);

	//	What can we do today?
	$subActions = array(
		'filters' => 'pretty_edit_filters',
		'maintenance' => 'pretty_maintenance',
		'settings' => 'pretty_manage_settings',
		'test' => 'pretty_test_rewrites',
	);
	if (isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]))
		call_user_func($subActions[$_REQUEST['sa']]);
	else
		pretty_manage_settings();
}

//	An interface to manage the settings and filters
function pretty_manage_settings()
{
	global $context, $modSettings, $sourcedir, $txt;

/*
	//	Core settings
	$context['pretty']['settings']['core'] = array(
		array(
			'id' => 'pretty_enable_filters',
			'label' => $txt['pretty_enable'],
			'type' => 'text',
			'value' => $modSettings['pretty_enable_filters'],
		),
	);
*/

	//	Load the filters data
	$context['pretty']['filters'] = unserialize($modSettings['pretty_filters']);

	//	Are we saving settings now?
	if (isset($_REQUEST['save']))
	{
		//	Get each filter from the form and save them
		foreach ($context['pretty']['filters'] as $id => $filter)
			$context['pretty']['filters'][$id]['enabled'] = isset($_POST['pretty_filter_' . $id]) ? 1 : 0;

		updateSettings(array('pretty_filters' => serialize($context['pretty']['filters'])));

		//	Update the filters too, but don't force pretty_enable_filters off
		require_once($sourcedir . '/Subs-PrettyUrls.php');
		$enabled = !empty($modSettings['pretty_enable_filters']);
		pretty_update_filters();
		$modSettings['pretty_enable_filters'] = $enabled;
		
		$_POST['pretty_skipactions'] = strtolower($_POST['pretty_skipactions']);
		$_POST['pretty_skipactions'] = trim($_POST['pretty_skipactions']);
		
		
		$pretty_bufferusecache = isset($_REQUEST['pretty_bufferusecache']) ? 1 : 0;
		updateSettings(
		array(
		'pretty_skipactions' => $_POST['pretty_skipactions'],
		'pretty_bufferusecache' => $pretty_bufferusecache,
		));

		// If you want to turn rewriting on you must test that it will work first!
		if (!$enabled && $_POST['pretty_enable'])
			redirectexit('action=admin;area=pretty;sa=test');

			
		// Update the enabled setting
		updateSettings(array('pretty_enable_filters' => $_POST['pretty_enable']));

		//	All finished now!
		$_SESSION['pretty']['notice'] = 'Settings saved';
		redirectexit('action=admin;area=pretty;sa=settings');
	}

	//	Action-specific chrome
	$context['page_title'] = $txt['pretty_chrome_page_title_settings'];
	$context['sub_template'] = 'pretty_settings';
	$context['pretty']['chrome']['page_title'] = $txt['pretty_chrome_menu_settings'];
	$context['pretty']['chrome']['caption'] = $txt['pretty_chrome_caption_settings'];

	//	Load the settings up
	$context['pretty']['settings']['enable'] = !empty($modSettings['pretty_enable_filters']);

	//	Any notices?
	if (isset($_SESSION['pretty']['notice']))
	{
		$context['pretty']['chrome']['notice'] = $_SESSION['pretty']['notice'];
		unset($_SESSION['pretty']['notice']);
	}
}

// Test whether the rewrites will work
function pretty_test_rewrites()
{
	global $context, $modSettings, $sourcedir, $txt;

	//	Yes they work, so turn them on!
	if (isset($_REQUEST['save']))
	{
		updateSettings(array('pretty_enable_filters' => '1'));

		//	All finished now!
		$_SESSION['pretty']['notice'] = 'Settings saved';
		redirectexit('action=admin;area=pretty;sa=settings');
	}

	require_once($sourcedir . '/PrettyUrls-Filters.php');
	require_once($sourcedir . '/PrettyUrls-Tests.php');

	//	Load the filters and get their test links
	$filters = unserialize($modSettings['pretty_filters']);
	$linklist = '';
	foreach ($filters as $id => $filter)
		if ($filter['enabled'] && isset($filter['test_callback']))
			$linklist .= '<h3>' . $filter['title'] . '</h3><p>' . implode('</p><p>', call_user_func($filter['test_callback'])) . '</p>';

	// Rewrite just these few test links
	$context['pretty']['chrome']['linklist'] = pretty_rewrite_buffer($linklist);

	//	Action-specific chrome
	$context['page_title'] = $txt['pretty_chrome_page_title_settings'];
	$context['sub_template'] = 'pretty_test_rewrites';
	$context['pretty']['chrome']['page_title'] = $txt['pretty_chrome_menu_settings'];
	$context['pretty']['chrome']['caption'] = $txt['pretty_chrome_caption_tests'];
}

//	Interface for URL maintenance
function pretty_maintenance()
{
	global $context, $sourcedir, $txt;

	//	Run the maintenance tasks
	if (isset($_REQUEST['run']))
	{
		require_once($sourcedir . '/Subs-PrettyUrls.php');
		pretty_run_maintenance();
	}

	//	Action-specific chrome
	$context['page_title'] = $txt['pretty_chrome_page_title_maintenance'];
	$context['sub_template'] = 'pretty_maintenance';
	$context['pretty']['chrome']['page_title'] = $txt['pretty_chrome_menu_maintenance'];
	$context['pretty']['chrome']['caption'] = $txt['pretty_chrome_caption_maintenance'];
}

//	Interface to edit the filters array
function pretty_edit_filters()
{
	global $context, $modSettings, $sourcedir, $txt;

	//	Check the JSON extension is installed
	if (!function_exists('json_encode'))
	{
		unset($context['template_layers']['pretty_chrome']);
		fatal_lang_error('pretty_no_json', false);
	}

	//	Save the filters array
	if (isset($_REQUEST['save']))
	{
		//	Try to process the edited JSON array
		$json_filters = (isset($_POST['pretty_json_filters'])) ? $_POST['pretty_json_filters'] : '';
		$json_filters = stripslashes($json_filters);
		$filters_array = json_decode($json_filters, true);

		//	Was that successful or not?
		if ($filters_array == NULL)
		{
			$_SESSION['pretty']['notice'] = 'There was an error with the JSON array you submitted';
			$_SESSION['pretty']['json_filters'] = $json_filters;
		}
		else
		{
			require_once($sourcedir . '/Subs-PrettyUrls.php');
			updateSettings(array('pretty_filters' => serialize($filters_array)));
			pretty_update_filters();
			$_SESSION['pretty']['notice'] = 'Filters saved and updated';
		}

		redirectexit('action=admin;area=pretty;sa=filters');
	}

	//	Action-specific chrome
	$context['page_title'] = $txt['pretty_chrome_page_title_filters'];
	$context['sub_template'] = 'pretty_filters';
	$context['pretty']['chrome']['page_title'] = $txt['pretty_chrome_title_filters'];
	$context['pretty']['chrome']['caption'] = $txt['pretty_chrome_caption_filters'];

	if (isset($_SESSION['pretty']['json_filters']))
	{
		//	We're working on something already
		$context['pretty']['json_filters'] = $_SESSION['pretty']['json_filters'];
		unset($_SESSION['pretty']['json_filters']);
	}
	else
	{
		//	Convert the filters array to JSON and format it nicely
		require_once($sourcedir . '/Subs-PrettyUrls.php');
		$context['pretty']['json_filters'] = json_encode(unserialize($modSettings['pretty_filters']));
		$context['pretty']['json_filters'] = pretty_json($context['pretty']['json_filters']);
		$context['pretty']['json_filters'] = str_replace('\/', '/', $context['pretty']['json_filters']);
	}

	//	Any new notices?
	if (isset($_SESSION['pretty']['notice']))
	{
		$context['pretty']['chrome']['notice'] = $_SESSION['pretty']['notice'];
		unset($_SESSION['pretty']['notice']);
	}
}

?>
