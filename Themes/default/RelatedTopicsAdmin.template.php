<?php
/**
 * Related Topics
 *
 * @package RelatedTopics
 * @version 1.4
 * @license http://madjoki.com/smf-mods/license/ New-BSD
 */


function template_related_topics_admin_main()
{
	global $context, $modSettings, $txt, $related_version;

	echo '
	<div class="tborder floatleft" style="width: 69%;">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['related_latest_news'], '</h3>
		</div>
		<div class="windowbg2 smallpadding">
			<span class="topslice"><span></span></span>
			<div id="related_news" style="overflow: auto; height: 18ex;" class="windowbg2 smallpadding">
				', $txt['related_news_unable_to_connect'], '
			</div>
		</div>
	</div>
	<div class="tborder floatright" style="width: 30%;">
		<div class="cat_bar">
			<h3 class="catbg headerpadding">', $txt['related_version_info'], '</h3>
		</div>
		<div class="windowbg2 smallpadding">
			<span class="topslice"><span></span></span>
			<div style="overflow: auto; height: 18ex;" class="windowbg2 smallpadding">
				', $txt['related_installed_version'], ': <span id="related_installed_version">', $related_version, '</span><br />			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>
	<div style="clear: both"></div>';

}

function template_related_topics_admin_methods()
{
	global $context, $modSettings, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=admin;area=relatedtopics;sa=methods;save" method="post" accept-charset="', $context['character_set'], '">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['related_topics_ignored_boards'], '</h3>
		</div>
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="settings">';

	foreach ($context['categories'] as $cat)
	{
		echo '
					<dt>', $cat['name'], '</dt>
					<dd>';

		foreach ($cat['boards'] as $id => $board)
			echo '
						<input type="checkbox" id="ignored_boards_', $id, '" name="ignored_boards[]" value="', $id, '"', $board['selected'] ? ' checked="checked"' : '', '/> <label for="ignored_boards_', $id, '">', $board['name'], '</label><br />';

		echo '
					</dd>';
	}

	echo '
					<dt>', $txt['related_topics_methods'], '</dt>
					<dd>';

	foreach ($context['related_methods'] as $id => $method)
		echo '
								<input type="checkbox" id="method_', $id, '" name="related_methods[]" value="', $id, '"', !$method['supported'] ? ' disabled="disabled"' : '', $method['selected'] ? ' checked="checked"' : '', '/> <label for="method_', $id, '">', $method['name'], '</label><br />';

	echo '
					</dd>
				</dl>
				<hr class="hrcolor clear">
				<div class="righttext">
					<input type="submit" class="button_submit" value="', $txt['save'], '">
				</div>
			</div>
			<span class="botslice"><span></span></span>
		</div>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form><br /><br />';

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['related_topics_index'], '</h3>
	</div>
	<div class="windowbg2">
		<span class="topslice"><span></span></span>

		<a href="', $scripturl, '?action=admin;area=relatedtopics;sa=buildIndex">', $txt['related_topics_rebuild'], '</a><br />
		<span class="smalltext">', $txt['related_topics_rebuild_desc'], '</span>

		<span class="botslice"><span></span></span>
	</div><br />';
}

?>