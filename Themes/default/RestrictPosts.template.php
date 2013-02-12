<?php

/**
* @package manifest file for Restrict Boards per post
* @version 1.0.1
* @author Joker (http://www.simplemachines.org/community/index.php?action=profile;u=226111)
* @copyright Copyright (c) 2012, Siddhartha Gupta
* @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

/*
* Version: MPL 1.1
*
* The contents of this file are subject to the Mozilla Public License Version
* 1.1 (the "License"); you may not use this file except in compliance with
* the License. You may obtain a copy of the License at
* http://www.mozilla.org/MPL/
*
* Software distributed under the License is distributed on an "AS IS" basis,
* WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
* for the specific language governing rights and limitations under the
* License.
*
* The Initial Developer of the Original Code is
*  Joker (http://www.simplemachines.org/community/index.php?action=profile;u=226111)
* Portions created by the Initial Developer are Copyright (C) 2012
* the Initial Developer. All Rights Reserved.
*
* Contributor(s):
*
*/

function template_rp_admin_info() {
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<span class="ie6_header floatleft">', $txt['rp_admin_panel'] ,'</span>
		</h3>
	</div>
	<p class="windowbg description">', isset($context['restrict_posts']['tab_desc']) ? $context['restrict_posts']['tab_desc'] : $txt['rp_general_desc'] ,'</p>';
	
	// The admin tabs.
		echo '
	<div id="adm_submenus">
		<ul class="dropmenu">';
	
		// Print out all the items in this tab.
		$menu_buttons = $context[$context['admin_menu_name']]['tab_data'];
		foreach ($menu_buttons['tabs'] as $sa => $tab)
		{
			echo '
			<li>
				<a class="', ($menu_buttons['active_button'] == $tab['url']) ? 'active ' : '', 'firstlevel" href="', $scripturl, '?action=admin;area=restrictposts;sa=', $tab['url'],'"><span class="firstlevel">', $tab['label'], '</span></a>
			</li>';
		}
	
		// the end of tabs
		echo '
		</ul>
	</div><br class="clear" />';

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $context['restrict_posts']['tab_name'] ,'
		</h3>
	</div>';
}

function template_rp_admin_post_setting_panel()
{
	global $context, $txt, $scripturl;

	template_rp_admin_info();

	echo '
	<div id="admincenter">
		<form action="'. $scripturl .'?action=admin;area=restrictposts;sa=savepostsettings" method="post" accept-charset="UTF-8">
			<div class="windowbg2">
				<span class="topslice"><span></span></span>';
	
				foreach ($context['restrict_posts']['board_info'] as $board_info)
				{
					echo '
					<fieldset style="width: 95%; margin: 0 auto; margin-bottom: 20px;">';
	
					echo '
					<legend class="global_perm_heading" id="'. $board_info['id_board']. '">' . $board_info['board_name'] . '</legend>';
	
					if (empty($board_info['groups_data'])) {
						echo $txt['rp_no_groups_found'];
					}
	
					else {
						foreach ($board_info['groups_data'] as $key => $group)
						{
							//print_r($group);
							echo '
							<div style="width: 25%; float: left">
								<label for="' . $group['id_group'] . '">' . $group['group_name'] . '</label>
							</div>';

							echo '
							<input type="text" name="' . $board_info['id_board'] . '_posts_'.$group['id_group'].'" id="" value="', $group['max_posts_allowed'] ,'" class="input_text" placeholder="'. $txt['rp_max_posts'] .'" />';
							echo '
							<input type="text" name="' . $board_info['id_board'] . '_timespan_'.$group['id_group'].'" id="" value="', $group['timespan'] ,'" class="input_text" placeholder="'. $txt['rp_time_limit'] .'" /><br />';
						}
					}
			
					echo '
					</fieldset>';
				}
	
					echo '
					<input type="submit" name="submit" value="', $txt['rp_submit'], '" tabindex="', $context['tabindex']++, '" class="button_submit" />';
	
				echo '
				<span class="botslice"><span></span></span>
			</div>
	
		</form>
	</div>
	<br class="clear">';
}


function template_rp_admin_general_setting_panel()
{
	global $context, $txt, $scripturl;

	template_rp_admin_info();

	echo '
	<div id="admincenter">
		<form action="'. $scripturl .'?action=admin;area=restrictposts;sa=savegeneralsettings" method="post" accept-charset="UTF-8">
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
					<div class="content">';
	
					foreach ($context['config_vars'] as $config_var) {
						echo '
						<dl class="settings">
							<dt>
								<span>'. $txt[$config_var['name']] .'</span>';
								if (isset($config_var['subtext']) && !empty($config_var['subtext'])) {
									echo '
									<br /><span class="smalltext">', $config_var['subtext'] ,'</span>';
								}
							echo '
							</dt>
							<dd>
								<input type="checkbox" name="', $config_var['name'], '" id="', $config_var['name'], '"', ($config_var['value'] ? ' checked="checked"' : ''), ' value="1" class="input_check" />
							</dd>
						</dl>';
					}
	
					echo '
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="submit" name="submit" value="', $txt['rp_submit'], '" tabindex="', $context['tabindex']++, '" class="button_submit" />';
		
					echo '
					</div>
				<span class="botslice"><span></span></span>
			</div>
	
		</form>
	</div>
	<br class="clear">';
}

?>