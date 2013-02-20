<?php
/**
 * I release this mod and all the code in it to anyone who wants to use it in hopes that it may be found useful.  Attribution is not necessary,
 * nor do you need to provide a link back to my website.  You are free to use this mod for any purpose, including commercial works,
 * redistribution and derivative works.  The only contingency is that the link back to the LED icon set (http://led24.de/iconset/) must remain, as this mod uses two icons
 * from that set. Namely comment_delete.png and comment_edit.png. Both images are found in the root directory of the package. All other
 * images are made by myself and can be used freely. Lastly, this mod comes with no guarantees that it will work well on all servers and
 * configurations, and I will not be held responsible for damages, expenses or problems that may have been caused by the mod's use.
**/
function template_include_javascript()
{
	global $context, $txt, $settings, $scripturl;
	
	echo '
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		// Comments List.
		var total_comments = ', $context['total_comments'], ';
		var oldest_shown = ', !empty($context['oldest_shown']) ? $context['oldest_shown']['id'] : 0, ';
		var most_recent = ', $context['most_recent'], ';
		', !empty($context['oldest_shown']) ? 'var oldest_shown = ' . $context['oldest_shown']['id'] . ';' : '', '
		var profileid = ', $context['profile_id'], ';
		var sessionid = \'', $context['session_id'], '\';
		
		var pc_title = \'', $txt['pc_title'], '\';
		var pc_reply = \'', $txt['pc_reply'], '\';
		var pc_submit = \'', $txt['pc_submit'], '\';
		var pc_cancel = \'', $txt['pc_cancel'], '\';
		var pc_showmore = \'', $txt['pc_show_more'], '\';
		//]]>
	</script>
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/profile_comments.js"></script>';
}

function template_profile_comments()
{
	global $context, $txt, $settings, $scripturl;
	
	echo '
	<div id="profile_comments">
		<div class="cat_bar">
			<h3 class="catbg">
				<img id="comment_loader" src="', $settings['images_url'], '/comment_loader.gif" alt="" />
				<img id="comment_success" src="', $settings['images_url'], '/comment_success.png" alt="" />
				<img id="comment_error" src="', $settings['images_url'], '/comment_error.png" alt="" />
				', $txt['pc_title'], '
			</h3>
		</div>';
	
	if (allowedTo('pc_can_comment'))
		echo '
		<div id="form_container" class="windowbg">
			<form action="', $scripturl, '" method="post" onsubmit="return pc.add_comment();">
				<input type="text" id="input_title" />
				<textarea id="input_body" rows="1" cols="1"></textarea>
				<input type="hidden" id="profile_id" value="', $context['profile_id'], '" />
				<input id="input_submit" type="submit" value="', $txt['pc_submit'], '" />
				<input type="submit" id="input_cancel" onclick="ui.hide_reply(); return false;" value="', $txt['pc_cancel'], '" />
			</form>
			<br class="clear" />
			<span class="botslice"><span></span></span>
		</div>
		<input type="submit" onclick="ui.show_reply();" id="show_reply_button" class="windowbg" value="&#94; ', $txt['pc_reply'], '" />
		<br class="clear" />';
	
	echo '
		<div id="comments">';
	if (!empty($context['profile_comments']))
	{
		foreach ($context['profile_comments'] as $c)
		{
			echo '
			<div id="comment_', $c['id'], '" class="comment_container">
				<div class="comment_user_info">
					<a href="', $scripturl, '?action=profile;u=', $c['poster_id'], '">
						<h2>', $c['poster_name'], '</h2>
					</a>
					<img class="comment_avatar" src="', $c['poster_avatar'], '" alt="" />
				</div>
				<div class="comment_body windowbg2">
					<div class="comment_action_bar">';
					
			if ($c['can_modify'])
				echo '
						<a class="comment_modify" href="javascript:pc.show_modify(', $c['id'], ');">
							<img src="', $settings['images_url'], '/comment_edit.png" alt="" />
						</a>';
			if ($c['can_delete'])
				echo '
						<a class="comment_delete" href="javascript:pc.delete_comment(', $c['id'], ');">
							<img src="', $settings['images_url'], '/comment_delete.png" alt="" />
						</a>';
			echo '
						<h3>', $c['title'], '</h3>
					</div>
					', $c['body'], '
				</div>
			</div>';
		}
	}
	echo '
		</div>';
	
	if ($context['total_comments'] > 20)
		echo'
		<input type="submit" value="&#94; ', $txt['pc_show_more'], '" id="show_more_button" class="windowbg" onclick="pc.load_older(oldest_shown);" />';
		
	echo '
	</div>';
}
