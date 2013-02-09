<?php
global $modSettings;
$txt['tags_menu_btn'] = 'Tags';
$txt['tags_save_btn'] = 'Save';
//Admin Area
$txt['tags_admin_title'] = 'Tagging System';
$txt['tags_admin_title_main'] = 'General Configuration';
$txt['tags_admin_list_cloud_title'] = 'List and cloud Tags';
$txt['tags_admin_desc_main'] = 'From here you can set the general configuration for tagging system mod';
$txt['tags_admin_main_enabled'] = 'Enabled Mod';
$txt['tags_admin_main_required'] = 'Tags are required';
$txt['tags_admin_main_board_tags'] = 'Disable tags for this boards';
$txt['tags_admin_main_board_tags_desc'] = 'Put id numbers of boards separated with comma';
$txt['tags_admin_main_max_tags'] = 'Maximum number of tags per topic';
$txt['tags_admin_main_min_length_tag'] = 'Minimum Tag Length';
$txt['tags_admin_main_max_length_tag'] = 'Maximum Tag Length';
$txt['tags_admin_main_max_suggested'] = 'Maximum number of suggested tags';
$txt['tags_admin_main_enabled_related_topics'] = 'Enabled Related Topics by Tags';
$txt['tags_admin_main_max_related_topics'] = 'Maximum related topics to show';
$txt['tags_admin_cloud_enabled'] = 'Show the Tag Cloud';
$txt['tags_admin_cloud_limit'] = 'Limits of tags to show in the cloud';
$txt['tags_admin_cloud_smallest_color'] = 'Color for smallest tags';
$txt['tags_admin_cloud_smallest_opacity'] = 'Opacity for smallest tags';
$txt['tags_admin_cloud_smallest_fontsize'] = 'Font size for smallest tags';
$txt['tags_admin_cloud_small_color'] = 'Color for small tags';
$txt['tags_admin_cloud_small_opacity'] = 'Opacity for small tags';
$txt['tags_admin_cloud_small_fontsize'] = 'Font size for small tags';
$txt['tags_admin_cloud_medium_color'] = 'Color for medium tags';
$txt['tags_admin_cloud_medium_opacity'] = 'Opacity for medium tags';
$txt['tags_admin_cloud_medium_fontsize'] = 'Font size for medium tags';
$txt['tags_admin_cloud_large_color'] = 'Color for large tags';
$txt['tags_admin_cloud_large_opacity'] = 'Opacity for large tags';
$txt['tags_admin_cloud_large_fontsize'] = 'Font size for large tags';
$txt['tags_admin_cloud_largest_color'] = 'Color for largest tags';
$txt['tags_admin_cloud_largest_opacity'] = 'Opacity for largest tags';
$txt['tags_admin_cloud_largest_fontsize'] = 'Font size for largest tags';
$txt['tags_admin_list_enabled'] = 'Show the List of tags';
$txt['tags_admin_list_show_count'] = 'Show the amount for each tag';
$txt['tags_admin_search_paginate_limit'] = 'Number of topics per page on search';
//List&Cloud
$txt['tags_list_title'] = 'All Tags';
$txt['tags_list_title_total'] = 'Total: ';
$txt['tags_cloud_title'] = 'Tag Cloud';
$txt['tags_search_title'] = 'Search';
$txt['tags_delete_tag'] = 'delete tag';
$txt['tags_delete_tag_confirmation'] = 'Are you sure that you want delete this tag?';
$txt['tags_no_tags'] = 'No tags to display';
//errors:
$txt['error_tags_exceeded'] = 'Maximum of tags exceeded, the limit is: '.$modSettings['tag_max_per_topic'].'';
$txt['error_tags_required'] = 'Tags are required';
$txt['error_tags_max_length'] = 'One of your new tags exceeded the maximum length: '.$modSettings['tag_max_length'].'';
$txt['error_tags_min_length'] = 'One of your new tags have less than '.$modSettings['tag_max_length'].' letters';
//Display:
$txt['tags_topic'] = 'Tags for this topic:';
$txt['tags_related_title'] = 'Topics Releated By Tags';