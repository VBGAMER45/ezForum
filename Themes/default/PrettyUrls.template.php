<?php
//	Version: 1.0RC; PrettyUrls

//	Pretty URLs chrome
function template_pretty_chrome_above()
{
	global $context;

	echo '
<div id="chrome">
	<h1>', $context['pretty']['chrome']['title'], '</h1>
	<div id="chrome_main">';

	if (isset($context['pretty']['chrome']['admin']))
	{
		//	The subactions menu
		echo '
		<ul id="chrome_menu">';
		foreach ($context['pretty']['chrome']['menu'] as $id => $item)
			echo '
			<li><a href="', $item['href'], '" class="', $id, '" title="', $item['title'], '"><span>', $item['title'], '</span></a></li>';

		//	Title and caption
		echo '
		</ul>
		<h2>', $context['pretty']['chrome']['page_title'], '</h2>
		<p id="chrome_caption">', $context['pretty']['chrome']['caption'], '</p>';

		//	Any notices?
		if (isset($context['pretty']['chrome']['notice']))
			echo '
		<p id="chrome_notice">', $context['pretty']['chrome']['notice'], '</p>';
	}
}

function template_pretty_chrome_below()
{
	echo '
	</div>
</div>';
}

//	Mini template for successful mod installs
function template_pretty_install()
{
	global $scripturl, $txt;

	echo '
		<p>', $txt['pretty_install_success'], '</p>
		<p><a href="', $scripturl, '?action=admin;area=pretty">', $txt['pretty_install_continue'], '</a></p>';
}

//	Lets show some news (and more!)
function template_pretty_news()
{
	global $txt;

	echo '
		<h3>', $txt['pretty_chrome_menu_news'], '</h3>
		<div id="chrome_news">', $txt['ajax_in_progress'], '</div>
		<h3>', $txt['pretty_version'], '</h3>
		<p>', $txt['pretty_current_version'], ': 1.0</p>
		<p>', $txt['pretty_latest_version'], ': <span id="chrome_latest">', $txt['ajax_in_progress'], '</span></p>';
}

//	It should be easy and fun to manage this mod
function template_pretty_settings()
{
	global $context, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=admin;area=pretty;sa=settings;save" method="post" accept-charset="', $context['character_set'], '">
			<fieldset>
				<legend>', $txt['pretty_core_settings'], '</legend>
				<label for="pretty_enable">', $txt['pretty_enable'], '</label>
				<input type="hidden" name="pretty_enable" value="0" />
				<input type="checkbox" name="pretty_enable" id="pretty_enable"', ($context['pretty']['settings']['enable'] ? ' checked="checked"' : ''), ' />
				<br />
				<label for="pretty_skipactions">', $txt['pretty_skipactions'], '</label>
				<input type="text" name="pretty_skipactions" id="pretty_skipactions" value="', (isset($modSettings['pretty_skipactions']) ? $modSettings['pretty_skipactions'] : ''), '" />
				<br />
				<span class="smalltext">',$txt['pretty_skipactions_note'],'</span><br />
				<label for="pretty_bufferusecache">', $txt['pretty_bufferusecache'], '</label>
				<input type="checkbox" name="pretty_bufferusecache" id="pretty_bufferusecache"', ($modSettings['pretty_bufferusecache'] ? ' checked="checked"' : ''), ' />
			
				
			</fieldset>
			<fieldset>
				<legend>', $txt['pretty_filters'], '</legend>';

	//	Display the filters
	foreach ($context['pretty']['filters'] as $id => $filter)
		echo '
				<div>
					<input type="checkbox" name="pretty_filter_', $id, '" id="pretty_filter_', $id, '"', ($filter['enabled'] ? ' checked="checked"' : ''), ' />
					<label for="pretty_filter_', $id, '">', $filter['title'], '</label>
					<p>', $filter['description'], '</p>
				</div>';

	echo '
			</fieldset>

			<fieldset>
				<input type="submit" value="', $txt['pretty_save'], '" />
			</fieldset>
		</form>';
}

// Show a short list of rewritten test URLs
function template_pretty_test_rewrites()
{
	global $context, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=admin;area=pretty;sa=test;save" method="post">
			<fieldset>', $context['pretty']['chrome']['linklist'], '</fieldset>
			<fieldset>
				<input type="submit" value="', $txt['pretty_enable'], '" />
			</fieldset>
		</form>';
}

//	Forum out of whack?
function template_pretty_maintenance()
{
	global $context, $scripturl, $txt;

	if (isset($context['pretty']['maintenance_tasks']))
	{
		echo '
		<ul>';
		foreach ($context['pretty']['maintenance_tasks'] as $task)
			echo '
			<li>', $task, '</li>';
		echo '
		</ul>';
	}
	else
		echo '
		<p><a href="', $scripturl, '?action=admin;area=pretty;sa=maintenance;run">', $txt['pretty_run_maintenance'], '</a></p>';
}

//	To make it easier to edit that nasty filters array
function template_pretty_filters()
{
	global $context, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=admin;area=pretty;sa=filters;save" method="post" accept-charset="', $context['character_set'], '">
			<textarea id="pretty_json_filters" name="pretty_json_filters" rows="20">', $context['pretty']['json_filters'], '</textarea>
			<input type="submit" value="', $txt['pretty_save'], '" />
		</form>';
}

?>
