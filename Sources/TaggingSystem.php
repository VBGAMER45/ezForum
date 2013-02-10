<?php
/*
This SMF modification is subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this SMF modification except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
*/
/*---------------------------------------------------------------------------------
*	SMFSIMPLE Tagging System												 	  *
*	Author: SSimple Team - 4KSTORE										          *
*	Powered by www.smfsimple.com												  *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

function TaggingSystemAdmin()
{
	global $context, $txt;
	isAllowedTo('admin_forum');
	loadTemplate('Tagging');
	loadLanguage('Tagging');

	$subActions = array(
		'main' => 'TaggingSettingsMain',
		'list_cloud' => 'TaggingSettingsListCloud',
	);
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['tags_admin_title'],
		'description' => $txt['tags_admin_desc_main'],
		'tabs' => array(
			'main' => array(
				'description' => $txt['tags_admin_desc_main'],
			),
			'list_cloud' => array(
				'description' => $txt['tags_admin_list_cloud_title'],
			),
		),
	);
	$sa = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';
	$context['sub_action'] = $sa;
	if (!empty($subActions[$sa]))
		call_user_func($subActions[$sa]);
}

function TaggingSettingsMain()
{
	global $context, $txt;
	loadLanguage('Tagging');

	if (!empty($_POST['save']))
	{
		$tagging_settings = array(
			'tag_enabled' => !empty($_POST['tag_enabled']) ? '1' : '0',
			'tag_required' => !empty($_POST['tag_required']) ? '1' : '0',
			'tag_board_disabled' => !empty($_POST['tag_board_disabled']) ? (string) $_POST['tag_board_disabled'] : '',
			'tag_max_per_topic' => !empty($_POST['tag_max_per_topic']) ? (int) $_POST['tag_max_per_topic'] : '',
			'tag_min_length' => !empty($_POST['tag_min_length']) ? (int) $_POST['tag_min_length'] : '',
			'tag_max_length' => !empty($_POST['tag_max_length']) ? (int) $_POST['tag_max_length'] : '',
			'tag_max_suggested' => !empty($_POST['tag_max_suggested']) ? (int) $_POST['tag_max_suggested'] : '',
			'tag_enabled_related_topics' => !empty($_POST['tag_enabled_related_topics']) ? '1' : '0',
			'tag_max_related_topics' => !empty($_POST['tag_max_related_topics']) ? (int) $_POST['tag_max_related_topics'] : '',
		);

		if (!empty($_POST['tag_board_disabled']))
		{
			$tmp = explode(',', $_POST['tag_board_disabled']);
			$tmp = array_unique(array_map('intval', $tmp));
			$_POST['tag_board_disabled'] = implode(',', $tmp);
		}
		updateSettings($tagging_settings);
		redirectexit('action=admin;area=taggingsystem;sa=main;sesc='.$context['session_id']);
	}
	$context['sub_template'] = 'tagging_settings';
	$context['page_title'] = $txt['tags_admin_title'];
}

function TaggingSettingsListCloud()
{
	global $context, $txt;

	loadLanguage('Tagging');

	if (!empty($_POST['save']))
	{
		$tagging_settings = array(
			'tag_cloud_enabled' => !empty($_POST['tag_cloud_enabled']) ? '1' : '0',
			'tag_cloud_limit' => !empty($_POST['tag_cloud_limit']) ? (int) $_POST['tag_cloud_limit'] : 30,
			'tag_cloud_smallest_color' => !empty($_POST['tag_cloud_smallest_color']) ? (string) $_POST['tag_cloud_smallest_color'] : '',
			'tag_cloud_smallest_opacity' => !empty($_POST['tag_cloud_smallest_opacity']) ? (float) $_POST['tag_cloud_smallest_opacity'] : '',
			'tag_cloud_smallest_fontsize' => !empty($_POST['tag_cloud_smallest_fontsize']) ? (string) $_POST['tag_cloud_smallest_fontsize'] : '',
			'tag_cloud_small_color' => !empty($_POST['tag_cloud_small_color']) ? (string) $_POST['tag_cloud_small_color'] : '',
			'tag_cloud_small_opacity' => !empty($_POST['tag_cloud_small_opacity']) ? (float) $_POST['tag_cloud_small_opacity'] : '',
			'tag_cloud_small_fontsize' => !empty($_POST['tag_cloud_small_fontsize']) ? (string) $_POST['tag_cloud_small_fontsize'] : '',
			'tag_cloud_medium_color' => !empty($_POST['tag_cloud_medium_color']) ? (string) $_POST['tag_cloud_medium_color'] : '',
			'tag_cloud_medium_opacity' => !empty($_POST['tag_cloud_medium_opacity']) ? (float) $_POST['tag_cloud_medium_opacity'] : '',
			'tag_cloud_medium_fontsize' => !empty($_POST['tag_cloud_medium_fontsize']) ? (string) $_POST['tag_cloud_medium_fontsize'] : '',
			'tag_cloud_large_color' => !empty($_POST['tag_cloud_large_color']) ? (string) $_POST['tag_cloud_large_color'] : '',
			'tag_cloud_large_opacity' => !empty($_POST['tag_cloud_large_opacity']) ? (float) $_POST['tag_cloud_large_opacity'] : '',
			'tag_cloud_large_fontsize' => !empty($_POST['tag_cloud_large_fontsize']) ? (string) $_POST['tag_cloud_large_fontsize'] : '',
			'tag_cloud_largest_color' => !empty($_POST['tag_cloud_largest_color']) ? (string) $_POST['tag_cloud_largest_color'] : '',
			'tag_cloud_largest_opacity' => !empty($_POST['tag_cloud_largest_opacity']) ? (float) $_POST['tag_cloud_largest_opacity'] : '',
			'tag_cloud_largest_fontsize' => !empty($_POST['tag_cloud_largest_fontsize']) ? (string) $_POST['tag_cloud_largest_fontsize'] : '',
			'tag_list_enabled' => !empty($_POST['tag_list_enabled']) ? '1' : '0',
			'tag_list_show_count' => !empty($_POST['tag_list_show_count']) ? '1' : '0',
			'tag_search_paginate_limit' => !empty($_POST['tag_search_paginate_limit']) ? (int) $_POST['tag_search_paginate_limit'] : 15,
		);

		if (!empty($_POST['tag_board_disabled']))
		{
			$tmp = explode(',', $_POST['tag_board_disabled']);
			$tmp = array_unique(array_map('intval', $tmp));
			$_POST['tag_board_disabled'] = implode(',', $tmp);
		}
		updateSettings($tagging_settings);
		redirectexit('action=admin;area=taggingsystem;sa=list_cloud;sesc='.$context['session_id']);
	}
	$context['sub_template'] = 'tagging_settings_list_cloud';
	$context['page_title'] = $txt['tags_admin_list_cloud_title'];
}

function TaggingSystemMain()
{
	global $modSettings, $context, $settings, $sourcedir, $txt;

	loadLanguage('Tagging');
	loadTemplate('Tagging');

	$subActions = array(
		'search' => 'searchTags',
		'deletetag' => 'deleteTags',
		'suggest' => 'suggestTagsAjaxs'
	);

	$sa = !empty($_GET['sa']) ? $_GET['sa'] : '';
	if (!empty($subActions[$sa]))
		call_user_func($subActions[$sa]);

	tagList();
	tagCloud();

	$context['page_title'] = $txt['tags_menu_btn'];
}

function suggestTagsAjaxs()
{
	global $smcFunc, $modSettings, $context;

	loadTemplate('Tagging');
	$initial_req = !empty($_POST['consulta']) ? (string) $smcFunc['db_escape_string']($smcFunc['strtolower']($_POST['consulta'])) : '';
	$context['tags_suggests'] = array();
	$modSettings['tag_max_suggested'] = !empty($modSettings['tag_max_suggested']) ? (int) $modSettings['tag_max_suggested'] : 3;

	if (!empty($initial_req))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_tag, tag
			FROM {db_prefix}tags
			WHERE tag LIKE "'.$initial_req.'%"
			LIMIT '.$modSettings['tag_max_suggested'].''
		);
		while($row = $smcFunc['db_fetch_assoc']($request))
			$context['tags_suggests'][$row['id_tag']] = $row['tag'];

		$smcFunc['db_free_result']($request);
	}

	if (!empty($context['tags_suggests']))
	{
		$list = '';
		foreach ($context['tags_suggests'] as $id => $tagname)
			$list .= '<li class="opcion" id="' . $id . '">'.$tagname.'</li>';
	}

	echo (!empty($list)) ? '<ul class="opciones">' . $list . '</ul>' : '';
	obExit(false);
}

function deleteTags()
{
	global $smcFunc, $user_info;

	$id_tag = !empty($_GET['id_tag']) ? (int) $_GET['id_tag'] : '';

	if (!empty($id_tag) && $user_info['is_admin'])
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}tags
			WHERE id_tag = {int:id_tag}',
			array(
				'id_tag' => $id_tag,
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}tags_topic
			WHERE id_tag = {int:id_tag}',
			array(
				'id_tag' => $id_tag,
			)
		);
	}
	redirectexit('action=tags');
}

function deleteTagsTopics($topic)
{
	global $smcFunc;

	$topic = !empty($topic) ? (int) $topic : '';

	if (!empty($topic))
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}tags_topic
			WHERE id_topic = {int:id_topic}',
			array(
				'id_topic' => $topic,
			)
		);
	}
}

function editTags($id_topic)
{
	global $smcFunc;

	$oldtags = '';
	$id_topic = !empty($id_topic) ? (int) $id_topic : '';

	if (!empty($id_topic))
	{
		$request = $smcFunc['db_query']('', '
			SELECT tt.id_topic, tt.id, tt.id_tag, ta.id_tag, ta.tag
			FROM {db_prefix}tags_topic as tt
			INNER JOIN {db_prefix}tags AS ta ON tt.id_tag = ta.id_tag
			WHERE tt.id_topic = {int:id_topic}',
			array(
				'id_topic' => $id_topic,
			)
		);

		$editTags = array();

		while($row = $smcFunc['db_fetch_assoc']($request))
		{
			$editTags[] = array(
				'id' => $row['id'],
				'id_tag' => $row['id_tag'],
				'tag' => $row['tag']
			);
		}
		$smcFunc['db_free_result']($request);

		if (!empty($editTags))
			foreach ($editTags as $et)
				$oldtags .= $et['tag'].' ';
	}

	return $oldtags;
}

function searchTags()
{
	global $txt, $smcFunc, $context, $scripturl, $modSettings, $settings;

	loadLanguage('Tagging');
	loadTemplate('Tagging');

	$id_tag = !empty($_REQUEST['id_tag']) ? (int) $_REQUEST['id_tag'] : '';

	if (!empty($id_tag))
	{
		$count = $smcFunc['db_query']('', '
			SELECT id_tag
			FROM {db_prefix}tags_topic
			WHERE id_tag = {int:id_tag}',
			array(
			  'id_tag' => $id_tag,
			)
		);

		$start = (!empty($_REQUEST['start'])) ? (int) $_REQUEST['start'] : 0;
		$numbooks =  $smcFunc['db_num_rows']($count);
		$perpage = (!empty($modSettings['tag_search_paginate_limit'])) ? (int) $modSettings['tag_search_paginate_limit'] : $numbooks;
		$smcFunc['db_free_result']($count);
		$context['page_index'] = constructPageIndex($scripturl . '?action=tags;sa=search;id_tag='.$id_tag.'', $start, $numbooks, $perpage);

		//Pagination END
		$request = $smcFunc['db_query']('', '
			SELECT tt.id, tt.id_tag, tt.id_topic, ta.tag, b.name as board_name, m.id_msg, m.subject, mem.id_member, mem.real_name, mem.avatar,
			top.id_topic, top.id_board, top.id_first_msg, top.id_member_started,
			IFNULL(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type
			FROM {db_prefix}tags_topic as tt
			INNER JOIN {db_prefix}topics AS top ON tt.id_topic = top.id_topic
			INNER JOIN {db_prefix}tags AS ta ON tt.id_tag = ta.id_tag
			INNER JOIN {db_prefix}messages AS m ON top.id_first_msg = m.id_msg
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = top.id_board)
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = top.id_member_started)
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = top.id_member_started)
			WHERE tt.id_tag = {int:id_tag} AND {query_see_board}
			ORDER BY m.subject ASC
			'.(($perpage < 0)  ? '' : 'LIMIT '.$start.','.$perpage.'').'',
			array(
				'id_tag' => $id_tag,
			)
		);

		$context['tagsearch'] = array();
		$context['tag_name'] = '';

		while($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['tag_name'] = $row['tag'];
			$context['tagsearch'][] = array(
				'id' => $row['id'],
				'id_topic' => $row['id_topic'],
				'board_name' => $row['board_name'],
				'subject' => $row['subject'],
				'real_name' => $row['real_name'],
				'member_href' => $scripturl . '?action=profile;u=' . $row['id_member_started'],
				'topic_href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
				'avatar' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? '<img width="50px" height="50px" src="' . (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" border="0" />' : '<img width="50px" height="50px" src="' . $settings['images_url'] . '/noavatar.png" alt="" />') : (stristr($row['avatar'], 'http://') ? '<img width="50px" height="50px" src="' . $row['avatar'] . '" alt="" border="0" />' : '<img width="50px" height="50px" src="' . $modSettings['avatar_url'] . '/' . $smcFunc['htmlspecialchars']($row['avatar']) . '" alt="" border="0" />'),
				'board_href' => $scripturl . '?board=' . $row['id_board'] . '.0'
			);
		}

		$smcFunc['db_free_result']($request);
	}

	$context['sub_template'] = 'tag_search';
	$context['page_title'] = $txt['tags_search_title'];
}

function tagList()
{
	global $smcFunc, $context;

	$request = $smcFunc['db_query']('', '
		SELECT tt.id, tt.id_tag, COUNT(tt.id_tag) AS TOTAL_TAGS, ta.id_tag, ta.tag
		FROM {db_prefix}tags_topic as tt
		INNER JOIN {db_prefix}tags AS ta ON tt.id_tag = ta.id_tag
		GROUP BY tt.id_tag
		ORDER BY ta.tag ASC'
	);
	$context['tagsList'] = array();

	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($row['TOTAL_TAGS'] > 0)
			$context['tagsList'][] = array(
				'id' => $row['id'],
				'id_tag' => $row['id_tag'],
				'name' => $row['tag'],
				'count' => $row['TOTAL_TAGS']
			);
	}
	$context['totaltags'] = $smcFunc['db_num_rows']($request);
	$smcFunc['db_free_result']($request);
}

function tagCloud()
{
	global $smcFunc, $context, $modSettings;

	$context['terms'] = array();
	$maximum = 0;

	$limit_tags = !empty($modSettings['tag_cloud_limit']) ? $modSettings['tag_cloud_limit'] : 30;

	$request = $smcFunc['db_query']('', '
		SELECT tt.id, tt.id_tag, COUNT(tt.id_tag) AS TOTAL_TAGS, ta.id_tag, ta.tag
		FROM {db_prefix}tags_topic as tt
		INNER JOIN {db_prefix}tags AS ta ON tt.id_tag = ta.id_tag
		GROUP BY tt.id_tag
		ORDER BY TOTAL_TAGS DESC
		LIMIT '.$limit_tags.''
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$counter = $row['TOTAL_TAGS'];

		if ($counter > $maximum)
			$maximum = $counter;

		$context['terms'][] = array(
			'term' => $row['tag'],
			'id' => $row['id_tag'],
			'counter' => $row['TOTAL_TAGS'],
		);
	}
	shuffle($context['terms']);
	$context['maximum'] = $maximum;
}

function errorsTags()
{
	global $modSettings, $smcFunc;
	loadLanguage('Tagging');

	$errorsTags = array();

	if (!empty($modSettings['tag_required']) && (empty($_POST['tags']) && empty($_POST['tags_news']))) //Check if there is tags if required is active
           $errorsTags[] = 'tags_required';

	if (isset($_POST['tags']) || isset($_POST['tags_news']))
	{
		$tags = !empty($_POST['tags']) ? count($_POST['tags']) : 0;
		$tags_news = !empty($_POST['tags_news']) ? count($_POST['tags_news']) : 0;
		$total_tags = $tags + $tags_news;

		if (!empty($modSettings['tag_max_per_topic']) && ($total_tags > $modSettings['tag_max_per_topic']))	//Check the limits of tags
			$errorsTags[] = 'tags_exceeded';

		if (!empty($_POST['tags_news']))
		{
			foreach ($_POST['tags_news'] as $tag)
			{
				$lengthword = $smcFunc['strlen']($tag);
				$max = !empty($modSettings['tag_max_length']) ? (int) $modSettings['tag_max_length'] : 0;
				$min = !empty($modSettings['tag_min_length']) ? (int) $modSettings['tag_min_length'] : 0;

				if ($max < $lengthword && !empty($max))
					$errorsTags[] = 'tags_max_length';

				if ($min > $lengthword && !empty($min))
					$errorsTags[] = 'tags_min_length';
			}
		}
	}
		return $errorsTags;
}

function postTags()
{
	global $modSettings, $smcFunc, $topic;

	if (!empty($modSettings['tag_enabled']))
	{
		// Step 1: Select tags that are duplicateds..
        $reviewed_tags = careTagString($_POST['tags_news']);
		if (!empty($reviewed_tags))
		{
        
			$request = $smcFunc['db_query']('', '
				SELECT id_tag, tag
				FROM {db_prefix}tags
				WHERE tag IN ({array_string:tags_news})',
				array(
					'tags_news' => $reviewed_tags,
				)
			);
			// Step 2: delete duplicate tags and add the new ones
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				if (in_array($row['tag'],$reviewed_tags))
				{
					$deleteTag = array_search($row['tag'],$reviewed_tags,false);
					array_splice($reviewed_tags, $deleteTag, 1);
					$_POST['tags'][] = $row['id_tag']; //agrega
				}
			}
			$smcFunc['db_free_result']($request);

			//Step 3: Insert the really new tags
			if (!empty($reviewed_tags))
			{
				foreach ($reviewed_tags as $tag_name)
				{
					$smcFunc['db_insert']('',
					'{db_prefix}tags',
						array(
							'tag' => 'string',
						),
						array(
							(string)$tag_name
						),
						array('id_tag')
					);
					//Adding the new ids from new tags
					$_POST['tags'][]  = $smcFunc['db_insert_id']('{db_prefix}tags', 'id_tag');
				}
			}
		}
		if (!empty($_POST['tags']))
		{
			//Step 4: avoid duplicates and save the others ids
			$ids = array_unique($_POST['tags']); //delete duplicates
			foreach ($ids as $id_tag)
			{
				$smcFunc['db_insert']('',
				'{db_prefix}tags_topic',
					array(
						'id_tag' => 'int', 'id_topic' => 'int',
					),
					array(
						(int)$id_tag, (int)$topic
					),
					array('id_tag')
				);
			}
		}
	}
}

function taggingRelated()
{
	global $modSettings, $context, $smcFunc, $topic, $scripturl;

	$context['tagsinfo'] = array();
	$tagsinfo = array();
	if (!empty($modSettings['tag_enabled']))
	{
		//Search the tags for this nice topic...
		$request = $smcFunc['db_query']('', '
			SELECT tt.id, tt.id_tag, tt.id_topic, ta.tag
			FROM {db_prefix}tags_topic as tt
			INNER JOIN {db_prefix}tags AS ta ON tt.id_tag = ta.id_tag
			WHERE tt.id_topic = {int:topic}',
			array(
				'topic' => $topic,
			)
		);
		while($row = $smcFunc['db_fetch_assoc']($request))
		{
			$tagsinfo = &$context['tagsinfo'][];
			$tagsinfo['id'] = $row['id'];
			$tagsinfo['id_tag'] = $row['id_tag'];
			$tagsinfo['tag'] = $row['tag'];
			$tagsinfo['id_topic'] = $row['id_topic'];
		}
		$smcFunc['db_free_result']($request);

		//Show related topics by tags..
		if (!empty($context['tagsinfo']) && !empty($modSettings['tag_enabled_related_topics']))
		{
			$limit_topics = !empty($modSettings['tag_max_related_topics']) ? (int) $modSettings['tag_max_related_topics'] : 5;
			$limit_topics++;
			$related_topics_tags_ids = array();
			$context['tagsrelated'] = array();
			$tagsrelated = array();

			foreach ($context['tagsinfo'] as $tags)
				$related_topics_tags_ids[] = $tags['id_tag'];

			if (!empty($related_topics_tags_ids))
			{
				$request = $smcFunc['db_query']('', '
					SELECT tt.id, tt.id_tag, tt.id_topic, b.name as board_name, m.id_msg, m.subject, mem.id_member, mem.real_name, COUNT(tt.id_topic) AS TOTAL_TAGS,
					top.id_topic, top.id_board, top.id_first_msg, top.id_member_started, top.num_replies, top.num_views
					FROM {db_prefix}tags_topic as tt
					INNER JOIN {db_prefix}topics AS top ON tt.id_topic = top.id_topic
					INNER JOIN {db_prefix}messages AS m ON top.id_first_msg = m.id_msg
					INNER JOIN {db_prefix}boards AS b ON (b.id_board = top.id_board)
					INNER JOIN {db_prefix}members AS mem ON (mem.id_member = top.id_member_started)
					WHERE tt.id_tag IN ({array_int:tag_id})
					AND {query_see_board}
					GROUP BY tt.id_topic
					ORDER BY TOTAL_TAGS DESC
					LIMIT {int:limit_topics}',
					array(
						'tag_id' => $related_topics_tags_ids,
						'limit_topics' => $limit_topics,
					)
				);
				while($row = $smcFunc['db_fetch_assoc']($request))
				{
					if ($row['id_topic'] != $topic)
					{
						$tagsrelated = &$context['tagsrelated'][];
						$tagsrelated['id'] = $row['id'];
						$tagsrelated['id_topic'] = $row['id_topic'];
						$tagsrelated['board_name'] = $row['board_name'];
						$tagsrelated['num_views'] = $row['num_views'];
						$tagsrelated['num_replies'] = $row['num_replies'];
						$tagsrelated['subject'] = $row['subject'];
						$tagsrelated['real_name'] = $row['real_name'];
						$tagsrelated['member_href'] = $scripturl . '?action=profile;u=' . $row['id_member_started'];
						$tagsrelated['topic_href'] = $scripturl . '?topic=' . $row['id_topic'] . '.0';
						$tagsrelated['board_href'] = $scripturl . '?board=' . $row['id_board'] . '.0';
					}
				}
			}
		}
	}
}

function careTagString($tag_array)
{
    $review = array();
    foreach ($tag_array as $tag)
    {
        $tag = strtolower($tag);
        $review[] = preg_replace('/[^a-z0-9\-]/', '', $tag);
    }
    return $review;
}