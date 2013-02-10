<?php
/*
This SMF modification is subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this SMF modification except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
*/
/*---------------------------------------------------------------------------------
*	SMFSIMPLE Bookmarks														 	  *
*	Author: SSimple Team - 4KSTORE										          *
*	Powered by www.smfsimple.com												  *
***********************************************************************************/
function template_main()
{
	global $context, $settings, $txt, $scripturl, $modSettings, $user_info;
	//Tags cloud
	if (!empty($modSettings['tag_cloud_enabled']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">'.$txt['tags_cloud_title'].'</h3>
		</div>
		<div class="windowbg2">
			<div class="content">
				<div id="tagcloud">';
				$class = array();
				if (!empty($context['terms']))
				{
					foreach ($context['terms'] as $term)
					{
						$percent = floor(($term['counter'] / $context['maximum']) * 100);
						if ($percent < 20)
							$class['name'] = 'smallest';
						elseif ($percent >= 20 and $percent < 40)
							$class['name'] = 'small';
						elseif ($percent >= 40 and $percent < 60)
							$class['name'] = 'medium';
						elseif ($percent >= 60 and $percent < 80)
							$class['name'] = 'large';
						else
							$class['name'] = 'largest';

						$class['color'] = (!empty($modSettings['tag_cloud_'.$class['name'].'_color'])) ? 'color:'.$modSettings['tag_cloud_'.$class['name'].'_color'].';' : '';
						$class['opacity'] = (!empty($modSettings['tag_cloud_'.$class['name'].'_opacity'])) ? 'opacity:'.$modSettings['tag_cloud_'.$class['name'].'_opacity'].';' : '';
						$class['fontsize'] = (!empty($modSettings['tag_cloud_'.$class['name'].'_fontsize'])) ? 'font-size:'.$modSettings['tag_cloud_'.$class['name'].'_fontsize'].';' : '';

						echo '
						<span style="',!empty($class['opacity']) ? $class['opacity'] : '' ,'" class="'.$class['name'].'">
							<a style="',!empty($class['color']) ? $class['color'] : '' ,' ',!empty($class['fontsize']) ? $class['fontsize'] : '','" href="'.$scripturl .'?action=tags;sa=search;id_tag='.$term['id'].'">'.$term['term'].'</a>
						</span>';
					}
				}
				else
					echo '
					<div style="text-align:center;">
						'.$txt['tags_no_tags'].'
					</div>';

				echo '
				</div>
			</div>
		</div>';
	}
	//Tags List
	if (!empty($modSettings['tag_list_enabled']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">'.$txt['tags_list_title'].' - '.$txt['tags_list_title_total'].' ',!empty($context['totaltags']) ? $context['totaltags'] : '0' ,'</h3>
		</div>
		<div class="windowbg2">
			<div class="content">';
		if (!empty($context['tagsList']))
		{
			$tagcount = 0;
			echo '<ul id="tags_list">';
			foreach ($context['tagsList'] as $taglist)
			{
				echo '
				<li class="tag_col_'.$tagcount.'">
					<a href="'.$scripturl .'?action=tags;sa=search;id_tag='.$taglist['id_tag'].'">'.$taglist['name'].' ',!empty($modSettings['tag_list_show_count']) ? '('.$taglist['count'].')' : '','</a>';
				if ($user_info['is_admin'])
					echo '
					<a onclick="return confirm(\''.$txt['tags_delete_tag_confirmation'].'\');" title="'.$txt['tags_delete_tag'].'" href="', $scripturl, '?action=tags;sa=deletetag;id_tag='.$taglist['id_tag'].'"><img style="height:12px;" src="', $settings['default_images_url'], '/buttons/delete.gif" alt="" /></a>';
				echo '
				</li>';

				if ($tagcount > 2)
					$tagcount = 0;

				else
					$tagcount++;
			}
			echo '</ul>';
		}
		else
			echo '
			<div style="text-align:center;">
				'.$txt['tags_no_tags'].'
			</div>';
		echo '</div>
		</div>';
	}
	
	if (empty($modSettings['tag_list_enabled']) && empty($modSettings['tag_cloud_enabled']))
		redirectexit('');
}

function template_tag_search()
{
	global $context, $txt, $modSettings;
	if (!empty($modSettings['tag_enabled']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">'.$txt['tags_search_title'].' ',!empty($context['tag_name']) ? ' - '. $context['tag_name'] : '','</h3>
		</div>
		<div class="windowbg2">
			<div class="content">';
				if (!empty($context['tagsearch']))
				{
					if (!empty($context['page_index']))
						echo '
						<div class="pagesection" style="margin-bottom: 5px;">
							<span>', $txt['pages'], ': ', $context['page_index'], '</span>
						</div>';

					foreach ($context['tagsearch'] as $tag)
					{
						echo '
						<div class="tag_search_list">
							<div class="tag_search_avatar">
								'.$tag['avatar'].'
							</div>
							<div class="tag_content_info">
								<div class="tag_search_subject">
									<a href="'.$tag['topic_href'].'">'.$tag['subject'].'</a>
								</div>
								<a href="'.$tag['board_href'].'">'.$tag['board_name'].'</a> <span style="font-size:0.8em;">'.$txt['started_by'].' <a href="'.$tag['member_href'].'">'.$tag['real_name'].'</a></span>
							</div>
						</div>';
					}
				}
				echo '				
			</div>			
		</div>
		';
	}
	else
		redirectexit('');
}

//Admin Part:
function template_tagging_settings()
{
	global $context, $scripturl, $txt, $modSettings;
	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				'.$txt['tags_admin_title'].' - '.$txt['tags_admin_title_main'].'
			</h3>
		</div>
		<form method="post" action="', $scripturl, '?action=admin;area=taggingsystem;sa=main" accept-charset="', $context['character_set'], '">
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							<label for="tags_admin_main_enabled">', $txt['tags_admin_main_enabled'], '</label>
						</dt>
						<dd>
							<input type="checkbox" value="1" name="tag_enabled" ',!empty($modSettings['tag_enabled']) ? 'checked="checked"' : '',' />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<dl class="settings">
						<dt>
							<label for="tags_admin_main_required">', $txt['tags_admin_main_required'], '</label>
						</dt>
						<dd>
							<input type="checkbox" value="1" name="tag_required" ',!empty($modSettings['tag_required']) ? 'checked="checked"' : '',' />
						</dd>
						<dt>
							<label for="tags_admin_main_board_tags">', $txt['tags_admin_main_board_tags'], '</label><br />
							<span style="font-size: smaller;">', $txt['tags_admin_main_board_tags_desc'], '</span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_board_disabled']) ? $modSettings['tag_board_disabled'] : '','" name="tag_board_disabled" />
						</dd>
						<dt>
							<span><label for="tags_admin_main_max_tags">', $txt['tags_admin_main_max_tags'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_max_per_topic']) ? $modSettings['tag_max_per_topic'] : '','" name="tag_max_per_topic" />
						</dd>
						<dt>
							<span><label for="tags_admin_main_min_length_tag">', $txt['tags_admin_main_min_length_tag'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_min_length']) ? $modSettings['tag_min_length'] : '','" name="tag_min_length" />
						</dd>
						<dt>
							<span><label for="tags_admin_main_max_length_tag">', $txt['tags_admin_main_max_length_tag'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_max_length']) ? $modSettings['tag_max_length'] : '','" name="tag_max_length" />
						</dd>
						<dt>
							<span><label for="tags_admin_main_max_suggested">', $txt['tags_admin_main_max_suggested'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_max_suggested']) ? $modSettings['tag_max_suggested'] : '','" name="tag_max_suggested" />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<dl class="settings">
						<dt>
							<label for="tags_admin_main_enabled_related_topics">', $txt['tags_admin_main_enabled_related_topics'], '</label>
						</dt>
						<dd>
							<input type="checkbox" value="1" name="tag_enabled_related_topics" ',!empty($modSettings['tag_enabled_related_topics']) ? 'checked="checked"' : '',' />
						</dd>
						<dt>
							<span><label for="tags_admin_main_max_related_topics">', $txt['tags_admin_main_max_related_topics'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_max_related_topics']) ? $modSettings['tag_max_related_topics'] : '','" name="tag_max_related_topics" />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="submit" name="save" value="',$txt['tags_save_btn'],'" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}

function template_tagging_settings_list_cloud()
{
	global $context, $scripturl, $txt, $modSettings;
	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				'.$txt['tags_admin_title'].' - '.$txt['tags_admin_list_cloud_title'].'
			</h3>
		</div>
		<form method="post" action="', $scripturl, '?action=admin;area=taggingsystem;sa=list_cloud" accept-charset="', $context['character_set'], '">
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							<label for="tags_admin_cloud_enabled">', $txt['tags_admin_cloud_enabled'], '</label>
						</dt>
						<dd>
							<input type="checkbox" value="1" name="tag_cloud_enabled" ',!empty($modSettings['tag_cloud_enabled']) ? 'checked="checked"' : '',' />
						</dd>
						<dt>
							<span><label for="tags_admin_cloud_limit">', $txt['tags_admin_cloud_limit'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_limit']) ? $modSettings['tag_cloud_limit'] : '','" name="tag_cloud_limit" />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<dl class="settings">
						<dt>
							<span><label for="tags_admin_cloud_smallest_color">', $txt['tags_admin_cloud_smallest_color'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_smallest_color']) ? $modSettings['tag_cloud_smallest_color'] : '','" name="tag_cloud_smallest_color" />
						</dd>
						<dt>
							<span><label for="tags_admin_cloud_smallest_opacity">', $txt['tags_admin_cloud_smallest_opacity'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_smallest_opacity']) ? $modSettings['tag_cloud_smallest_opacity'] : '','" name="tag_cloud_smallest_opacity" />
						</dd>
						<dt>
							<span><label for="tags_admin_cloud_smallest_fontsize">', $txt['tags_admin_cloud_smallest_fontsize'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_smallest_fontsize']) ? $modSettings['tag_cloud_smallest_fontsize'] : '','" name="tag_cloud_smallest_fontsize" />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<dl class="settings">
						<dt>
							<span><label for="tags_admin_cloud_small_color">', $txt['tags_admin_cloud_small_color'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_small_color']) ? $modSettings['tag_cloud_small_color'] : '','" name="tag_cloud_small_color" />
						</dd>
						<dt>
							<span><label for="tags_admin_cloud_small_opacity">', $txt['tags_admin_cloud_small_opacity'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_small_opacity']) ? $modSettings['tag_cloud_small_opacity'] : '','" name="tag_cloud_small_opacity" />
						</dd>
						<dt>
							<span><label for="tags_admin_cloud_small_fontsize">', $txt['tags_admin_cloud_small_fontsize'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_small_fontsize']) ? $modSettings['tag_cloud_small_fontsize'] : '','" name="tag_cloud_small_fontsize" />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<dl class="settings">
						<dt>
							<span><label for="tags_admin_cloud_medium_color">', $txt['tags_admin_cloud_medium_color'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_medium_color']) ? $modSettings['tag_cloud_medium_color'] : '','" name="tag_cloud_medium_color" />
						</dd>
						<dt>
							<span><label for="tags_admin_cloud_medium_opacity">', $txt['tags_admin_cloud_medium_opacity'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_medium_opacity']) ? $modSettings['tag_cloud_medium_opacity'] : '','" name="tag_cloud_medium_opacity" />
						</dd>
						<dt>
							<span><label for="tags_admin_cloud_medium_fontsize">', $txt['tags_admin_cloud_medium_fontsize'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_medium_fontsize']) ? $modSettings['tag_cloud_medium_fontsize'] : '','" name="tag_cloud_medium_fontsize" />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<dl class="settings">
						<dt>
							<span><label for="tags_admin_cloud_large_color">', $txt['tags_admin_cloud_large_color'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_large_color']) ? $modSettings['tag_cloud_large_color'] : '','" name="tag_cloud_large_color" />
						</dd>
						<dt>
							<span><label for="tags_admin_cloud_large_opacity">', $txt['tags_admin_cloud_large_opacity'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_large_opacity']) ? $modSettings['tag_cloud_large_opacity'] : '','" name="tag_cloud_large_opacity" />
						</dd>
						<dt>
							<span><label for="tags_admin_cloud_large_fontsize">', $txt['tags_admin_cloud_large_fontsize'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_large_fontsize']) ? $modSettings['tag_cloud_large_fontsize'] : '','" name="tag_cloud_large_fontsize" />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<dl class="settings">
						<dt>
							<span><label for="tags_admin_cloud_largest_color">', $txt['tags_admin_cloud_largest_color'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_largest_color']) ? $modSettings['tag_cloud_largest_color'] : '','" name="tag_cloud_largest_color" />
						</dd>
						<dt>
							<span><label for="tags_admin_cloud_largest_opacity">', $txt['tags_admin_cloud_largest_opacity'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_largest_opacity']) ? $modSettings['tag_cloud_largest_opacity'] : '','" name="tag_cloud_largest_opacity" />
						</dd>
						<dt>
							<span><label for="tags_admin_cloud_largest_fontsize">', $txt['tags_admin_cloud_largest_fontsize'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_cloud_largest_fontsize']) ? $modSettings['tag_cloud_largest_fontsize'] : '','" name="tag_cloud_largest_fontsize" />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<hr class="hrcolor clear" />
					<dl class="settings">
						<dt>
							<span><label for="tags_admin_list_enabled">', $txt['tags_admin_list_enabled'], '</label></span>
						</dt>
						<dd>
							<input type="checkbox" value="1" name="tag_list_enabled" ',!empty($modSettings['tag_list_enabled']) ? 'checked="checked"' : '',' />
						</dd>
						<dt>
							<span><label for="tags_admin_list_show_count">', $txt['tags_admin_list_show_count'], '</label></span>
						</dt>
						<dd>
							<input type="checkbox" value="1" name="tag_list_show_count" ',!empty($modSettings['tag_list_show_count']) ? 'checked="checked"' : '',' />
						</dd>
						<dt>
							<span><label for="tags_admin_search_paginate_limit">', $txt['tags_admin_search_paginate_limit'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['tag_search_paginate_limit']) ? $modSettings['tag_search_paginate_limit'] : '','" name="tag_search_paginate_limit" />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="submit" name="save" value="',$txt['tags_save_btn'],'" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}