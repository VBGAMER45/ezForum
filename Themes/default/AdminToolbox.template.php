<?php

/**
 * Admin Toolbox, little functions that are a big PITA
 *
 * @package Admin Toolbox
 * @license This Source Code is subject to the terms of the Mozilla Public License
 * version 2.0 (the "License"). You can obtain a copy of the License at
 * http://mozilla.org/MPL/2.0/.
 *
 * @version 1.0
 */

// Template for the admin toolbox maintenance tasks.
function template_toolbox_main()
{
	global $context, $settings, $txt, $scripturl;

	// If the task has finished or created an error tell the user.
	if (!empty($context['maintenance_finished']))
		echo '
			<div class="maintenance_finished">
				', sprintf($txt['maintain_done'], $context['maintenance_finished']), '
			</div>';
	elseif (!empty($context['maintenance_error']))
		echo '
			<div class="errorbox">
				', $context['maintenance_error'], '
			</div>';

	// Start off with our toolbox options
	$context['tabindex'] = 0;
	echo '
	<div id="admin_toolbox">

		<div class="cat_bar">
			<h3 class="catbg">', $txt['toolbox_title'], '</h3>
		</div>
		<p class="windowbg description">', $txt['toolbox_info'], '</p>

		<div class="cat_bar">
			<h3 class="catbg">', $txt['toolbox_recount'], '</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=toolbox;sa=recount" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['toolbox_recount_info'], '</p>
					<span><input type="submit" tabindex="', $context['tabindex']++, '" value="', $txt['toolbox_run_now'], '" class="button_submit" /></span>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</div>
			<span class="botslice"><span></span></span>
		</div>

		<div class="cat_bar">
			<h3 class="catbg">', $txt['toolbox_merge'], '</h3>
		</div>
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=toolbox;sa=validate" method="post" accept-charset="', $context['character_set'], '">
					', $txt['toolbox_merge_info'], '
					<br /><br />
					<span class="upperframe"><span></span></span>
					<div class="roundframe">', $txt['toolbox_merge_selection'], '</div>
					<span class="lowerframe"><span></span></span>
					<br />
					<dl class="settings">
						<dt>
							<label for="merge_to">', $txt['toolbox_merge_to'], ':</label>
						</dt>
						<dd>
							<input type="text" name="merge_to" id="merge_to" tabindex="', $context['tabindex']++, '" value="" size="25" class="input_text" /><a href="', $scripturl, '?action=findmember;input=merge_to;delim=null;' . $context['session_var'] . '=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">&nbsp;<img alt="" src="', $settings['images_url'], '/icons/assist.gif" class="icon" /></a>
							<div id="merge_to_container"></div>
						</dd>

						<dt>
							<label for="merge_from">', $txt['toolbox_merge_from'], ':</label>
						</dt>
						<dd>
							<input type="text" name="merge_from" id="merge_from" tabindex="', $context['tabindex']++, '" value="" size="25" class="input_text" /><a href="', $scripturl, '?action=findmember;input=merge_from;delim=null;' . $context['session_var'] . '=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">&nbsp;<img alt="" src="', $settings['images_url'], '/icons/assist.gif" class="icon" /></a>
							<div id="merge_from_container"></div>
						</dd>

						<dt>
							<a id="setting_adjustuser" href="', $scripturl, '?action=helpadmin;help=', $txt['adjustuser_help'], '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" class="icon" alt="', $txt['help'], '" /></a><span><label for="adjustuser">', $txt['toolbox_adjustuser'], '</label></span>
						</dt>
						<dd>
							<input type="checkbox" name="adjustuser" tabindex="', $context['tabindex']++, '" id="adjustuser"'. (!empty($_POST['adjustuser']) ? ' checked="checked"' : '') . ' class="input_check" />
						</dd>

						<dt>
							<a id="setting_deluser" href="', $scripturl, '?action=helpadmin;help=', $txt['deluser_help'], '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" class="icon" alt="', $txt['help'], '" /></a><span><label for="deluser">', $txt['toolbox_deluser'], '</label></span>
						</dt>
						<dd>
							<input type="checkbox" name="deluser" tabindex="', $context['tabindex']++, '" id="deluser"'. (!empty($_POST['deluser']) ? ' checked="checked"' : '') . ' class="input_check" />
						</dd>

					</dl>
					<span><input type="submit" tabindex="', $context['tabindex']++, '" value="', $txt['toolbox_run_now'], '" class="button_submit" /></span>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</div>
			<span class="botslice"><span></span></span>
		</div>

		<div class="cat_bar">
			<h3 class="catbg">', $txt['toolbox_inactive'], '</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=toolbox;sa=inactive" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['toolbox_inactive_info'], '</p>
					<span>', $txt['toolbox_inactive_days'], ' <input type="text" size="3" name="inactive_days" id="inactive_days" value="" /></span><br />
					<span><input type="submit" tabindex="', $context['tabindex']++, '" value="', $txt['toolbox_run_now'], '" class="button_submit" /></span>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</div>
			<span class="botslice"><span></span></span>
		</div>';

	// only show this one to the cool kids
	if ($context['admintoolbox_database'])
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['toolbox_stats'], '</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=toolbox;sa=statsvalidate" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['toolbox_stats_info'], '</p>
					<span><input type="submit" tabindex="', $context['tabindex']++, '" value="', $txt['toolbox_run_now'], '" class="button_submit" /></span>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</div>
			<span class="botslice"><span></span></span>
		</div>';

echo '
	</div>
	<br class="clear" />';

	// Auto suggest script
	echo '
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/suggest.js?fin20"></script>
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/toolbox.js?fin20"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		var oToolBoxTo = new smf_ToolBox({
			sSelf: \'oToolBoxTo\',
			sSessionId: \'', $context['session_id'], '\',
			sSessionVar: \'', $context['session_var'], '\',
			sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
			sTextViewItem: \'', $txt['autosuggest_view_item'], '\',
			sToControlId: \'merge_to\',
			sContainer: \'merge_to_container\',
			sPostName: \'merge_to_id\',
			sSuggestId: \'to_suggest\',
			aToRecipients: [
			]
		});
		var oToolBoxFrom = new smf_ToolBox({
			sSelf: \'oToolBoxFrom\',
			sSessionId: \'', $context['session_id'], '\',
			sSessionVar: \'', $context['session_var'], '\',
			sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
			sTextViewItem: \'', $txt['autosuggest_view_item'], '\',
			sToControlId: \'merge_from\',
			sContainer: \'merge_from_container\',
			sPostName: \'merge_from_id\',
			sSuggestId: \'from_suggest\',
			aToRecipients: [
			]
		});
	// ]]></script>';
}

function template_toolbox_validate()
{
	global $context, $txt, $scripturl, $settings, $boardurl;

	echo '
	<div id="admin_toolbox">

		<div class="cat_bar">
			<h3 class="catbg">', $txt['toolbox_title'], '</h3>
		</div>
		<p class="windowbg description">', $txt['toolbox_MergeMembersValidate_info'], '</p>

		<div class="cat_bar">
			<h3 class="catbg">', $txt['toolbox_validate'], '</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=toolbox;sa=merge" method="post" accept-charset="', $context['character_set'], '">
					', $txt['toolbox_merge_info'], '
					<br /><br />
					<div class="floatright" style="width:49%">
						<div class="cat_bar">
							<h3 class="catbg">', $txt['toolbox_to'], '</h3>
						</div>
						<span class="upperframe"><span></span></span>
						<div class="roundframe">',
							$txt['name'], ': <a href="', $boardurl, '/index.php?action=profile;u=', $context['merge_to']['id_member'], '">', $context['merge_to']['real_name'], '</a><br />',
							$txt['userid'], ': ', $context['merge_to']['id_member'], '<br />',
							$txt['username'], ': ', $context['merge_to']['member_name'], '<br />',
							$txt['email'], ': ', $context['merge_to']['email_address'], '<br />',
							$txt['lastLoggedIn'], ': ' . (empty($context['merge_to']['last_login']) ? $txt['never'] : timeformat($context['merge_to']['last_login'])) . '<br />',
							$context['adjustuser'] ? '<br />' . $txt['toolbox_adjust_true'] : '<br /><br />', '
						</div>
						<span class="lowerframe"><span></span></span>
					</div>

					<div class="float:left" style="width:49%">
						<div class="cat_bar">
							<h3 class="catbg">', $txt['toolbox_from'], '</h3>
						</div>
						<span class="upperframe"><span></span></span>
						<div class="roundframe">',
							$txt['name'], ': <a href="', $boardurl, '/index.php?action=profile;u=', $context['merge_from']['id_member'], '">', $context['merge_from']['real_name'], '</a><span class="floatright"><img src="', $settings['images_url'], '/admin/change_menu2.png" height="15" alt=">" style="padding-top:3px" /></span><br />',
							$txt['userid'], ': ', $context['merge_from']['id_member'], '<span class="floatright"><img src="', $settings['images_url'], '/admin/change_menu2.png" height="15" alt=">" style="padding-top:3px" /></span><br />',
							$txt['username'], ': ', $context['merge_from']['member_name'], '<span class="floatright"><img src="', $settings['images_url'], '/admin/change_menu2.png" height="15" alt=">" style="padding-top:3px" /></span><br />',
							$txt['email'], ': ', $context['merge_from']['email_address'], '<span class="floatright"><img src="', $settings['images_url'], '/admin/change_menu2.png" height="15" alt=">" style="padding-top:3px" /></span><br />',
							$txt['lastLoggedIn'], ': ' . (empty($context['merge_from']['last_login']) ? $txt['never'] : timeformat($context['merge_from']['last_login'])) . '<span class="floatright"><img src="', $settings['images_url'], '/admin/change_menu2.png" height="15" alt=">" style="padding-top:3px" /></span><br />',
							$context['deluser'] ? '<br />' . $txt['toolbox_del_true'] : '<br /><br />', '
						</div>
						<span class="lowerframe"><span></span></span>
					</div>

					<span><input type="submit" value="', $txt['toolbox_run_now'], '" class="button_submit" /></span>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="merge_from" id="merge_from" value="', $context['merge_from']['id_member'], '" />
					<input type="hidden" name="merge_to" id="merge_to" value="', $context['merge_to']['id_member'], '" />
					<input type="hidden" name="adjustuser" id="adjustuser" value="', $context['adjustuser'], '" />
					<input type="hidden" name="deluser" id="deluser" value="', $context['deluser'], '" />

				</form>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';
}

function template_toolbox_stats_rebuild()
{
	global $context, $txt, $scripturl, $settings;

	echo '
	<div id="admin_toolbox">

		<div class="cat_bar">
			<h3 class="catbg">', $txt['toolbox_title'], '</h3>
		</div>

		<p class="windowbg description">', $txt['toolbox_StatsValidate_info'], '</p>

		<div class="cat_bar">
			<h3 class="catbg">', $txt['toolbox_stats_validate'], '</h3>
		</div>

		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=toolbox;sa=stats" method="post" accept-charset="', $context['character_set'], '">
					', $txt['toolbox_stats_info1'], '<br /><br />', $txt['toolbox_stats_info2'], '<br /><br />', $txt['toolbox_stats_info3'], '<br /><br />';

	// Data needs to have an approriate rebuild option selected if its missing
	if (!empty($context['stats']['most_on_coeff']))
	{
		echo '
					<span class="upperframe"><span></span></span>
					<div class="roundframe">
						<span>
							<br />
							<img align="top" border="0" src="' . $settings['images_url'] . '/warn.gif" alt="" />&nbsp;', sprintf($txt['toolbox_stats_warn'], $context['stats']['message_start_date'], $context['stats']['stat_start_date']), '
						</span>
						<br />
						<br />
						<dl class="settings">
						<dt>
							<label>', $txt['toolbox_rebuild_select'], '</label>
							<select name="id_type" id="id_type" size="1">';

		// Loop and show the drop down.
		foreach ($context['toolbox_rebuild_option'] as $key => $option)
			echo '
								<option title="', $option['name'], '" value="', $option['id'], '" ', isset($_REQUEST['toolbox_id']) &&  $_REQUEST['toolbox_id'] == $option['id'] ? 'selected="selected"' : '', '>', $option['name'], '</option>';

		echo '
							</select>
						</dt>
						<dd>
							<span id="option_desc" ></span><br /><br />';

		// and the descriptions for them, hidden and used by javascript to fill in the above empty span
		foreach($context['toolbox_rebuild_option'] as $desc)
			echo '
							<span id="option_desc_', $desc['id'], '" style="display:none">', $desc['desc'], '</span>';

		echo '
						</dd>
						</dl>
					</div>
				<span class="lowerframe"><span></span></span>';
	}

	echo '
				<span><input type="submit" value="', $txt['toolbox_run_now'], '" class="button_submit" /></span>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="hidden" name="stats_data" id="stats_data" value="', htmlspecialchars(serialize($context['stats'])), '" />
				</form>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';

// Some javascript to make the form interactive
echo '
<script type="text/javascript"><!-- // --><![CDATA[

var type = document.getElementById(\'id_type\');
mod_addEvent(type, \'change\', toggledbTrigger);
toggledbTrigger();

// page event control
function mod_addEvent(control, ev, fn) {
	if (control.addEventListener)
	{
		control.addEventListener(ev, fn, false);
	}
	else if (control.attachEvent)
	{
		control.attachEvent(\'on\'+ev, fn);
	}
}

// show the descriptions that match the selection
function toggledbTrigger() {
	var desc = document.getElementById(\'option_desc_\' + type.value).firstChild.data;
	document.getElementById(\'option_desc\').innerHTML = desc;

	if (type.value == 0)
		document.getElementById(\'warn\').style.visibility = \'visible\';
	else
		document.getElementById(\'warn\').style.visibility = \'hidden\';
}
// ]]></script>';

}

?>