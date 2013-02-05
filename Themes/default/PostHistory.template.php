<?php
/**
 * 
 * @package PostHistory
 * @version 1.0.1
 */

/**
 *
 */
function template_list_edits()
{
	global $context, $settings, $options, $scripturl, $txt;

	if ($context['is_popup'])
		template_ph_popup_above();

	echo '
	<div class="title_bar">
		<h3 class="titlebg">
			', $context['ph_topic']['msg_subject'], '
		</h3>
	</div>
	<form action="', $scripturl, '?action=posthistory;msg=', $_REQUEST['msg'], ';topic=', $context['current_topic'], '.0', $context['is_popup'] ? ';popup' : '', '" method="post">
		<table class="table_grid" cellspacing="0" width="100%">
			<thead>
				<tr class="catbg">
					<th scope="col" class="smalltext first_th" colspan="2"></td>
					<th scope="col" class="smalltext">', $txt['ph_last_edit'], '</td>
					<th scope="col" class="smalltext">', $txt['ph_last_time'], '</td>
					<th scope="col" class="smalltext last_th"</td>
				</tr>';
	
	// First we check if moderators have been lazy
	if (empty($context['post_history']))
		echo '
				<tr>
					<th scope="col" class="smalltext first_th"></td>
					<th scope="col" class="smalltext">', $txt['ph_no_edits'], '</td>
					<th scope="col" class="smalltext last_th"></td>
				</tr>
			</thead>';
	else
	{
		echo '
			</thead>
			<tbody>';
			
		$alternate = false;
		
		foreach ($context['post_history'] as $edit)
		{
			echo '
				<tr class="windowbg', $alternate ? '2' : '', '">
					<td><input type="radio" name="compare_to" value="', $edit['id'], '" /></td>
					<td>', !empty($edit['id_prev']) ? '<input type="radio" name="edit" value="' . $edit['id'] . '" />' : '', '</td>
					<td>', $edit['name'], '</td>
					<td>
						', $edit['time'], '
						', $edit['is_current'] || $edit['is_original'] ? '(' . $txt['ph_' . ($edit['is_current'] ? 'current_' : '') . ($edit['is_original'] ? 'original_' : '') . 'edit'] . ')' : '', '
					</td>
					<td>
						<a href="', $edit['href'], '">', $txt['ph_view_edit'], '</a>';
			
			if (!empty($edit['restore_href']))
				echo ' | <a href="', $edit['restore_href'], '" ', $context['is_popup'] ? ' onclick="return gotoAndClose(this.href);"' : '', '>', $txt['restore'], '</a>';
						
			echo '
					</td>
				</tr>';
			
			$alternate = !$alternate;
		}
		
		echo '
				<tr class="titlebg">
					<td colspan="5" align="right"><input class="button_submit" type="submit" value="', $txt['compare_selected'], '"></td>
				</tr>
			</tbody>';
	}
	
	echo '
		</table>
	</form>';
	
	if ($context['is_popup'])
		template_ph_popup_below();
}

/**
 *
 */
function template_view_edit()
{
	global $context, $settings, $options, $scripturl, $txt;

	if ($context['is_popup'])
		template_ph_popup_above();
		
	echo '
	<div class="title_bar">
		<h3 class="titlebg">
			', $context['ph_topic']['msg_subject'], '
		</h3>
	</div>
	<em>', $txt['ph_last_edit'], ': ', $context['current_edit']['name'], ' (', $context['current_edit']['time'], ')</em><br />
	<div class="windowbg">
		<span class="topslice"><span></span></span>
		<div class="content">
			', $context['current_edit']['body'], '<br /><br />';
	
	if (!empty($context['current_edit']['restore_href']))
		echo '
			<div style="text-align: center"><a href="', $context['current_edit']['restore_href'], '"', $context['is_popup'] ? ' onclick="return gotoAndClose(this.href);"' : '', '>', $txt['restore'], '</a></div>';
				
	echo '
		</div>
		<span class="botslice"><span></span></span>
	</div>';
	
	if ($context['is_popup'])
		template_ph_popup_below();
}

/**
 *
 */
function template_compare_edit()
{
	global $context, $settings, $options, $scripturl, $txt;

	if ($context['is_popup'])
		template_ph_popup_above();
			
	echo '
		<div class="title_bar">
			<h3 class="titlebg">
				', $context['ph_topic']['msg_subject'], '
			</h3>
		</div>
		<em>', $txt['ph_last_edit'], ': ', $context['current_edit']['name'], ' (', $context['current_edit']['time'], ')</em><br />
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="edit_changes content">';
			
	foreach ($context['edit_changes'] as $change)
	{
		if (!is_array($change))
			echo $change;
		else
		{
			if (!empty($change['d']))
				echo '<del>', implode('', $change['d']), '</del>';
			if (!empty($change['i']))
				echo '<ins>', implode('', $change['i']), '</ins>';
		}
	}
				
	echo '
		</div>
		<span class="botslice"><span></span></span>
	</div>';
	
	if ($context['is_popup'])
		template_ph_popup_below();
}

/**
 *
 */
function template_ph_popup_above()
{
	global $context, $settings, $options, $txt;
	
	// Since this is a popup of its own we need to start the html, etc.
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<meta name="robots" content="noindex" />
		<title>', $context['page_title'], '</title>
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index.css" />
		<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js"></script>
		<script type="text/javascript"><!-- // --><![CDATA[
			function gotoAndClose(url)
			{
				window.opener.location = url;
				self.close();
				
				return false;
			}
		// ]]></script>
	</head>
	<body id="help_popup" style="background: white">';
}

/**
 *
 */
function template_ph_popup_below()
{
	global $context, $settings, $options, $txt;

	echo '
		<div style="text-align: center">
			<a href="javascript:self.close();">', $txt['close_window'], '</a>
		</div>
	</body>
</html>';
}

?>