<?php
// Version: 2.0; index

global $forum_copyright, $forum_version, $webmaster_email, $scripturl, $context, $boardurl, $modSettings;

// Locale (strftime, pspell_new) and spelling. (pspell_new, can be left as '' normally.)
// For more information see:
//   - http://www.php.net/function.pspell-new
//   - http://www.php.net/function.setlocale
// Again, SPELLING SHOULD BE '' 99% OF THE TIME!!  Please read this!
$txt['lang_locale'] = 'en_US';
$txt['lang_dictionary'] = 'en';
$txt['lang_spelling'] = 'american';

// Ensure you remember to use uppercase for character set strings.
$txt['lang_character_set'] = 'ISO-8859-1';
// Character set and right to left?
$txt['lang_rtl'] = false;
// Capitalize day and month names?
$txt['lang_capitalize_dates'] = true;

$txt['days'] = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
$txt['days_short'] = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
// Months must start with 1 => 'January'. (or translated, of course.)
$txt['months'] = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$txt['months_titles'] = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$txt['months_short'] = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

$txt['time_am'] = 'am';
$txt['time_pm'] = 'pm';

$txt['newmessages0'] = 'is new';
$txt['newmessages1'] = 'are new';
$txt['newmessages3'] = 'New';
$txt['newmessages4'] = ',';

$txt['admin'] = 'Admin';
$txt['moderate'] = 'Moderate';

$txt['save'] = 'Save';

$txt['modify'] = 'Modify';
$txt['forum_index'] = '%1$s - Index';
$txt['members'] = 'Members';
$txt['board_name'] = 'Board name';
$txt['posts'] = 'Posts';

$txt['member_postcount'] = 'Posts';
$txt['no_subject'] = '(No subject)';
$txt['view_profile'] = 'View Profile';
$txt['guest_title'] = 'Guest';
$txt['author'] = 'Author';
$txt['on'] = 'on';
$txt['remove'] = 'Remove';
$txt['start_new_topic'] = 'Start new topic';

$txt['login'] = 'Login';
// Use numeric entities in the below string.
$txt['username'] = 'Username';
$txt['password'] = 'Password';

$txt['username_no_exist'] = 'That username does not exist.';
$txt['no_user_with_email'] = 'There are no usernames associated with that email.';

$txt['board_moderator'] = 'Board Moderator';
$txt['remove_topic'] = 'Remove Topic';
$txt['topics'] = 'Topics';
$txt['modify_msg'] = 'Modify message';
$txt['name'] = 'Name';
$txt['email'] = 'Email';
$txt['subject'] = 'Subject';
$txt['message'] = 'Message';
$txt['redirects'] = 'Redirects';
$txt['quick_modify'] = 'Modify Inline';

$txt['choose_pass'] = 'Choose password';
$txt['verify_pass'] = 'Verify password';
$txt['position'] = 'Position';

$txt['profile_of'] = 'View the profile of';
$txt['total'] = 'Total';
$txt['posts_made'] = 'Posts';
$txt['website'] = 'Website';
$txt['register'] = 'Register';
$txt['warning_status'] = 'Warning Status';
$txt['user_warn_watch'] = 'User is on moderator watch list';
$txt['user_warn_moderate'] = 'User posts join approval queue';
$txt['user_warn_mute'] = 'User is banned from posting';
$txt['warn_watch'] = 'Watched';
$txt['warn_moderate'] = 'Moderated';
$txt['warn_mute'] = 'Muted';

$txt['message_index'] = 'Message Index';
$txt['news'] = 'News';
$txt['home'] = 'Home';

$txt['lock_unlock'] = 'Lock/Unlock Topic';
$txt['post'] = 'Post';
$txt['error_occured'] = 'An Error Has Occurred!';
$txt['at'] = 'at';
$txt['logout'] = 'Logout';
$txt['started_by'] = 'Started by';
$txt['replies'] = 'Replies';
$txt['last_post'] = 'Last post';
$txt['admin_login'] = 'Administration Login';
// Use numeric entities in the below string.
$txt['topic'] = 'Topic';
$txt['help'] = 'Help';
$txt['terms_and_policies'] = 'Terms and Policies';
$txt['notify'] = 'Notify';
$txt['unnotify'] = 'Unnotify';
$txt['notify_request'] = 'Do you want a notification email if someone replies to this topic?';
// Use numeric entities in the below string.
$txt['regards_team'] = 'Regards,' . "\n" . 'The ' . $context['forum_name'] . ' Team.';
$txt['notify_replies'] = 'Notify of replies';
$txt['move_topic'] = 'Move Topic';
$txt['move_to'] = 'Move to';
$txt['pages'] = 'Pages';
$txt['users_active'] = 'Users active in past %1$d minutes';
$txt['personal_messages'] = 'Personal Messages';
$txt['reply_quote'] = 'Reply with quote';
$txt['reply'] = 'Reply';
$txt['reply_noun'] = 'Reply';
$txt['approve'] = 'Approve';
$txt['approve_all'] = 'approve all';
$txt['awaiting_approval'] = 'Awaiting Approval';
$txt['attach_awaiting_approve'] = 'Attachments awaiting approval';
$txt['post_awaiting_approval'] = 'Note: This message is awaiting approval by a moderator.';
$txt['there_are_unapproved_topics'] = 'There are %1$s topics and %2$s posts awaiting approval in this board. Click <a href="%3$s">here</a> to view them all.';

$txt['msg_alert_none'] = 'No messages...';
$txt['msg_alert_you_have'] = 'you have';
$txt['msg_alert_messages'] = 'messages';
$txt['remove_message'] = 'Remove this message';

$txt['online_users'] = 'Users Online';
$txt['personal_message'] = 'Personal Message';
$txt['jump_to'] = 'Jump to';
$txt['go'] = 'go';
$txt['are_sure_remove_topic'] = 'Are you sure you want to remove this topic?';
$txt['yes'] = 'Yes';
$txt['no'] = 'No';

$txt['search_end_results'] = 'End of results';
$txt['search_on'] = 'on';

$txt['search'] = 'Search';
$txt['all'] = 'All';

$txt['back'] = 'Back';
$txt['password_reminder'] = 'Password reminder';
$txt['topic_started'] = 'Topic started by';
$txt['title'] = 'Title';
$txt['post_by'] = 'Post by';
$txt['memberlist_searchable'] = 'Searchable list of all registered members.';
$txt['welcome_member'] = 'Please welcome';
$txt['admin_center'] = 'Administration Center';
$txt['last_edit'] = 'Last Edit';
$txt['notify_deactivate'] = 'Would you like to deactivate notification on this topic?';

$txt['recent_posts'] = 'Recent Posts';

$txt['location'] = 'Location';
$txt['gender'] = 'Gender';
$txt['date_registered'] = 'Date Registered';

$txt['recent_view'] = 'View the most recent posts on the forum.';
$txt['recent_updated'] = 'is the most recently updated topic';

$txt['male'] = 'Male';
$txt['female'] = 'Female';

$txt['error_invalid_characters_username'] = 'Invalid character used in Username.';

$txt['welcome_guest'] = 'Welcome, <strong>%1$s</strong>. Please <a href="' . $scripturl . '?action=login">login</a> or <a href="' . $scripturl . '?action=register">register</a>.';
$txt['login_or_register'] = 'Please <a href="' . $scripturl . '?action=login">login</a> or <a href="' . $scripturl . '?action=register">register</a>.';
$txt['welcome_guest_activate'] = '<br />Did you miss your <a href="' . $scripturl . '?action=activate">activation email</a>?';
$txt['hello_member'] = 'Hey,';
// Use numeric entities in the below string.
$txt['hello_guest'] = 'Welcome,';
$txt['welmsg_hey'] = 'Hey,';
$txt['welmsg_welcome'] = 'Welcome,';
$txt['welmsg_please'] = 'Please';
$txt['select_destination'] = 'Please select a destination';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['posted_by'] = 'Posted by';

$txt['icon_smiley'] = 'Smiley';
$txt['icon_angry'] = 'Angry';
$txt['icon_cheesy'] = 'Cheesy';
$txt['icon_laugh'] = 'Laugh';
$txt['icon_sad'] = 'Sad';
$txt['icon_wink'] = 'Wink';
$txt['icon_grin'] = 'Grin';
$txt['icon_shocked'] = 'Shocked';
$txt['icon_cool'] = 'Cool';
$txt['icon_huh'] = 'Huh';
$txt['icon_rolleyes'] = 'Roll Eyes';
$txt['icon_tongue'] = 'Tongue';
$txt['icon_embarrassed'] = 'Embarrassed';
$txt['icon_lips'] = 'Lips sealed';
$txt['icon_undecided'] = 'Undecided';
$txt['icon_kiss'] = 'Kiss';
$txt['icon_cry'] = 'Cry';

$txt['moderator'] = 'Moderator';
$txt['moderators'] = 'Moderators';

$txt['mark_board_read'] = 'Mark Topics as Read for this Board';
$txt['views'] = 'Views';
$txt['new'] = 'New';

$txt['view_all_members'] = 'View All Members';
$txt['view'] = 'View';

$txt['viewing_members'] = 'Viewing Members %1$s to %2$s';
$txt['of_total_members'] = 'of %1$s total members';

$txt['forgot_your_password'] = 'Forgot your password?';

$txt['date'] = 'Date';
// Use numeric entities in the below string.
$txt['from'] = 'From';
$txt['check_new_messages'] = 'Check for new messages';
$txt['to'] = 'To';

$txt['board_topics'] = 'Topics';
$txt['members_title'] = 'Members';
$txt['members_list'] = 'Members List';
$txt['new_posts'] = 'New Posts';
$txt['old_posts'] = 'No New Posts';
$txt['redirect_board'] = 'Redirect Board';

$txt['sendtopic_send'] = 'Send';
$txt['report_sent'] = 'Your report has been sent successfully.';

$txt['time_offset'] = 'Time Offset';
$txt['or'] = 'or';

$txt['no_matches'] = 'Sorry, no matches were found';

$txt['notification'] = 'Notification';

$txt['your_ban'] = 'Sorry %1$s, you are banned from using this forum!';
$txt['your_ban_expires'] = 'This ban is set to expire %1$s.';
$txt['your_ban_expires_never'] = 'This ban is not set to expire.';
$txt['ban_continue_browse'] = 'You may continue to browse the forum as a guest.';

$txt['mark_as_read'] = 'Mark ALL messages as read';

$txt['hot_topics'] = 'Hot Topic (More than %1$d replies)';
$txt['very_hot_topics'] = 'Very Hot Topic (More than %1$d replies)';
$txt['locked_topic'] = 'Locked Topic';
$txt['normal_topic'] = 'Normal Topic';
$txt['participation_caption'] = 'Topic you have posted in';

$txt['go_caps'] = 'GO';

$txt['print'] = 'Print';
$txt['profile'] = 'Profile';
$txt['topic_summary'] = 'Topic Summary';
$txt['not_applicable'] = 'N/A';
$txt['message_lowercase'] = 'message';
$txt['name_in_use'] = 'This name is already in use by another member.';

$txt['total_members'] = 'Total Members';
$txt['total_posts'] = 'Total Posts';
$txt['total_topics'] = 'Total Topics';

$txt['mins_logged_in'] = 'Minutes to stay logged in';

$txt['preview'] = 'Preview';
$txt['always_logged_in'] = 'Always stay logged in';

$txt['logged'] = 'Logged';
// Use numeric entities in the below string.
$txt['ip'] = 'IP';

$txt['www'] = 'WWW';

$txt['by'] = 'by';

$txt['hours'] = 'hours';
$txt['days_word'] = 'days';

$txt['newest_member'] = ', our newest member.';

$txt['search_for'] = 'Search for';

$txt['icq'] = 'ICQ';
$txt['icq_title'] = 'ICQ Messenger';
$txt['yim'] = 'YIM';
$txt['yim_title'] = 'Yahoo Instant Messenger';

$txt['maintain_mode_on'] = 'Remember, this forum is in \'Maintenance Mode\'.';

$txt['read'] = 'Read';
$txt['times'] = 'times';

$txt['forum_stats'] = 'Forum Stats';
$txt['latest_member'] = 'Latest Member';
$txt['total_cats'] = 'Total Categories';
$txt['latest_post'] = 'Latest Post';

$txt['you_have'] = 'You\'ve got';
$txt['click'] = 'Click';
$txt['here'] = 'here';
$txt['to_view'] = 'to view them.';

$txt['total_boards'] = 'Total Boards';

$txt['print_page'] = 'Print Page';

$txt['valid_email'] = 'This must be a valid email address.';

$txt['geek'] = 'I am a geek!!';
$txt['info_center_title'] = '%1$s - Info Center';

$txt['send_topic'] = 'Send this topic';

$txt['sendtopic_title'] = 'Send the topic &quot;%1$s&quot; to a friend.';
$txt['sendtopic_sender_name'] = 'Your name';
$txt['sendtopic_sender_email'] = 'Your email address';
$txt['sendtopic_receiver_name'] = 'Recipient\'s name';
$txt['sendtopic_receiver_email'] = 'Recipient\'s email address';
$txt['sendtopic_comment'] = 'Add a comment';

$txt['allow_user_email'] = 'Allow users to email me';

$txt['check_all'] = 'Check all';

// Use numeric entities in the below string.
$txt['database_error'] = 'Database Error';
$txt['try_again'] = 'Please try again.  If you come back to this error screen, report the error to an administrator.';
$txt['file'] = 'File';
$txt['line'] = 'Line';
// Use numeric entities in the below string.
$txt['tried_to_repair'] = 'ezForum has detected and automatically tried to repair an error in your database.  If you continue to have problems, or continue to receive these emails, please contact your host.';
$txt['database_error_versions'] = '<strong>Note:</strong> It appears that your database <em>may</em> require an upgrade. Your forum\'s files are currently at version %1$s, while your database is at version %2$s. The above error might possibly go away if you execute the latest version of upgrade.php.';
$txt['template_parse_error'] = 'Template Parse Error!';
$txt['template_parse_error_message'] = 'It seems something has gone sour on the forum with the template system.  This problem should only be temporary, so please come back later and try again.  If you continue to see this message, please contact the administrator.<br /><br />You can also try <a href="javascript:location.reload();">refreshing this page</a>.';
$txt['template_parse_error_details'] = 'There was a problem loading the <tt><strong>%1$s</strong></tt> template or language file.  Please check the syntax and try again - remember, single quotes (<tt>\'</tt>) often have to be escaped with a slash (<tt>\\</tt>).  To see more specific error information from PHP, try <a href="' . $boardurl . '%1$s">accessing the file directly</a>.<br /><br />You may want to try to <a href="javascript:location.reload();">refresh this page</a> or <a href="' . $scripturl . '?theme=1">use the default theme</a>.';

$txt['today'] = '<strong>Today</strong> at ';
$txt['yesterday'] = '<strong>Yesterday</strong> at ';
$txt['new_poll'] = 'New poll';
$txt['poll_question'] = 'Question';
$txt['poll_vote'] = 'Submit Vote';
$txt['poll_total_voters'] = 'Total Members Voted';
$txt['shortcuts'] = 'shortcuts: hit alt+s to submit/post or alt+p to preview';
$txt['shortcuts_firefox'] = 'shortcuts: hit shift+alt+s to submit/post or shift+alt+p to preview';
$txt['poll_results'] = 'View results';
$txt['poll_lock'] = 'Lock Voting';
$txt['poll_unlock'] = 'Unlock Voting';
$txt['poll_edit'] = 'Edit Poll';
$txt['poll'] = 'Poll';
$txt['one_day'] = '1 Day';
$txt['one_week'] = '1 Week';
$txt['one_month'] = '1 Month';
$txt['forever'] = 'Forever';
$txt['quick_login_dec'] = 'Login with username, password and session length';
$txt['one_hour'] = '1 Hour';
$txt['moved'] = 'MOVED';
$txt['moved_why'] = 'Please enter a brief description as to<br />why this topic is being moved.';
$txt['board'] = 'Board';
$txt['in'] = 'in';
$txt['sticky_topic'] = 'Sticky Topic';

$txt['delete'] = 'Delete';

$txt['your_pms'] = 'Your Personal Messages';

$txt['kilobyte'] = 'kB';

$txt['more_stats'] = '[More Stats]';

// Use numeric entities in the below three strings.
$txt['code'] = 'Code';
$txt['code_select'] = '[Select]';
$txt['quote_from'] = 'Quote from';
$txt['quote'] = 'Quote';

$txt['merge_to_topic_id'] = 'ID of target topic';
$txt['split'] = 'Split Topic';
$txt['merge'] = 'Merge Topics';
$txt['subject_new_topic'] = 'Subject For New Topic';
$txt['split_this_post'] = 'Only split this post.';
$txt['split_after_and_this_post'] = 'Split topic after and including this post.';
$txt['select_split_posts'] = 'Select posts to split.';
$txt['new_topic'] = 'New Topic';
$txt['split_successful'] = 'Topic successfully split into two topics.';
$txt['origin_topic'] = 'Origin Topic';
$txt['please_select_split'] = 'Please select which posts you wish to split.';
$txt['merge_successful'] = 'Topics successfully merged.';
$txt['new_merged_topic'] = 'Newly Merged Topic';
$txt['topic_to_merge'] = 'Topic to be merged';
$txt['target_board'] = 'Target board';
$txt['target_topic'] = 'Target topic';
$txt['merge_confirm'] = 'Are you sure you want to merge';
$txt['with'] = 'with';
$txt['merge_desc'] = 'This function will merge the messages of two topics into one topic. The messages will be sorted according to the time of posting. Therefore the earliest posted message will be the first message of the merged topic.';

$txt['set_sticky'] = 'Set topic sticky';
$txt['set_nonsticky'] = 'Set topic non-sticky';
$txt['set_lock'] = 'Lock topic';
$txt['set_unlock'] = 'Unlock topic';

$txt['search_advanced'] = 'Advanced search';

$txt['security_risk'] = 'MAJOR SECURITY RISK:';
$txt['not_removed'] = 'You have not removed ';
$txt['not_removed_extra'] ='%1$s is a backup of %2$s that was not generated by ezForum. It can be accessed directly and used to gain unauthorised access to your forum. You should delete it immediately.';

$txt['cache_writable_head'] = 'Performance Warning';
$txt['cache_writable'] = 'The cache directory is not writable - this will adversely affect the performance of your forum.';

$txt['page_created'] = 'Page created in ';
$txt['seconds_with'] = ' seconds with ';
$txt['queries'] = ' queries.';

$txt['report_to_mod_func'] = 'Use this function to inform the moderators and administrators of an abusive or wrongly posted message.<br /><em>Please note that your email address will be revealed to the moderators if you use this.</em>';

$txt['online'] = 'Online';
$txt['offline'] = 'Offline';
$txt['pm_online'] = 'Personal Message (Online)';
$txt['pm_offline'] = 'Personal Message (Offline)';
$txt['status'] = 'Status';

$txt['go_up'] = 'Go Up';
$txt['go_down'] = 'Go Down';

$forum_copyright = '<a href="https://www.ezforum.com" title="free forum software" target="_blank" class="new_win">ezForum &copy; 2021</a>';

$txt['birthdays'] = 'Birthdays:';
$txt['events'] = 'Events:';
$txt['birthdays_upcoming'] = 'Upcoming Birthdays:';
$txt['events_upcoming'] = 'Upcoming Events:';
// Prompt for holidays in the calendar, leave blank to just display the holiday's name.
$txt['calendar_prompt'] = '';
$txt['calendar_month'] = 'Month:';
$txt['calendar_year'] = 'Year:';
$txt['calendar_day'] = 'Day:';
$txt['calendar_event_title'] = 'Event Title';
$txt['calendar_event_options'] = 'Event Options';
$txt['calendar_post_in'] = 'Post In:';
$txt['calendar_edit'] = 'Edit Event';
$txt['event_delete_confirm'] = 'Delete this event?';
$txt['event_delete'] = 'Delete Event';
$txt['calendar_post_event'] = 'Post Event';
$txt['calendar'] = 'Calendar';
$txt['calendar_link'] = 'Link to Calendar';
$txt['calendar_upcoming'] = 'Upcoming Calendar';
$txt['calendar_today'] = 'Today\'s Calendar';
$txt['calendar_week'] = 'Week';
$txt['calendar_week_title'] = 'Week %1$d of %2$d';
$txt['calendar_numb_days'] = 'Number of Days:';
$txt['calendar_how_edit'] = 'how do you edit these events?';
$txt['calendar_link_event'] = 'Link Event To Post:';
$txt['calendar_confirm_delete'] = 'Are you sure you want to delete this event?';
$txt['calendar_linked_events'] = 'Linked Events';
$txt['calendar_click_all'] = 'click to see all %1$s';

$txt['moveTopic1'] = 'Post a redirection topic';
$txt['moveTopic2'] = 'Change the topic\'s subject';
$txt['moveTopic3'] = 'New subject';
$txt['moveTopic4'] = 'Change every message\'s subject';
$txt['move_topic_unapproved_js'] = 'Warning! This topic has not yet been approved.\\n\\nIt is not recommended that you create a redirection topic unless you intend to approve the post immediately following the move.';

$txt['theme_template_error'] = 'Unable to load the \'%1$s\' template.';
$txt['theme_language_error'] = 'Unable to load the \'%1$s\' language file.';

$txt['parent_boards'] = 'Child Boards';

$txt['smtp_no_connect'] = 'Could not connect to SMTP host';
$txt['smtp_port_ssl'] = 'SMTP port setting incorrect; it should be 465 for SSL servers.';
$txt['smtp_bad_response'] = 'Couldn\'t get mail server response codes';
$txt['smtp_error'] = 'Ran into problems sending Mail. Error: ';
$txt['mail_send_unable'] = 'Unable to send mail to the email address \'%1$s\'';

$txt['mlist_search'] = 'Search For Members';
$txt['mlist_search_again'] = 'Search again';
$txt['mlist_search_email'] = 'Search by email address';
$txt['mlist_search_messenger'] = 'Search by messenger nickname';
$txt['mlist_search_group'] = 'Search by position';
$txt['mlist_search_name'] = 'Search by name';
$txt['mlist_search_website'] = 'Search by website';
$txt['mlist_search_results'] = 'Search results for';
$txt['mlist_search_by'] = 'Search by %1$s';
$txt['mlist_menu_view'] = 'View the memberlist';

$txt['attach_downloaded'] = 'downloaded';
$txt['attach_viewed'] = 'viewed';
$txt['attach_times'] = 'times';

$txt['settings'] = 'Settings';
$txt['never'] = 'Never';
$txt['more'] = 'more';

$txt['hostname'] = 'Hostname';
$txt['you_are_post_banned'] = 'Sorry %1$s, you are banned from posting and sending personal messages on this forum.';
$txt['ban_reason'] = 'Reason';

$txt['tables_optimized'] = 'Database tables optimized';

$txt['add_poll'] = 'Add poll';
$txt['poll_options6'] = 'You may only select up to %1$s options.';
$txt['poll_remove'] = 'Remove Poll';
$txt['poll_remove_warn'] = 'Are you sure you want to remove this poll from the topic?';
$txt['poll_results_expire'] = 'Results will be shown when voting has closed';
$txt['poll_expires_on'] = 'Voting closes';
$txt['poll_expired_on'] = 'Voting closed';
$txt['poll_change_vote'] = 'Remove Vote';
$txt['poll_return_vote'] = 'Voting options';
$txt['poll_cannot_see'] = 'You cannot see the results of this poll at the moment.';

$txt['quick_mod_approve'] = 'Approve selected';
$txt['quick_mod_remove'] = 'Remove selected';
$txt['quick_mod_lock'] = 'Lock/Unlock selected';
$txt['quick_mod_sticky'] = 'Sticky/Unsticky selected';
$txt['quick_mod_move'] = 'Move selected to';
$txt['quick_mod_merge'] = 'Merge selected';
$txt['quick_mod_markread'] = 'Mark selected read';
$txt['quick_mod_go'] = 'Go!';
$txt['quickmod_confirm'] = 'Are you sure you want to do this?';

$txt['spell_check'] = 'Spell Check';

$txt['quick_reply'] = 'Quick Reply';
$txt['quick_reply_desc'] = 'With <em>Quick-Reply</em> you can write a post when viewing a topic without loading a new page. You can still use bulletin board code and smileys as you would in a normal post.';
$txt['quick_reply_warning'] = 'Warning: this topic is currently locked! Only admins and moderators can reply.';
$txt['quick_reply_verification'] = 'After submitting your post you will be directed to the regular post page to verify your post %1$s.';
$txt['quick_reply_verification_guests'] = '(required for all guests)';
$txt['quick_reply_verification_posts'] = '(required for all users with less than %1$d posts)';
$txt['wait_for_approval'] = 'Note: this post will not display until it\'s been approved by a moderator.';

$txt['notification_enable_board'] = 'Are you sure you wish to enable notification of new topics for this board?';
$txt['notification_disable_board'] = 'Are you sure you wish to disable notification of new topics for this board?';
$txt['notification_enable_topic'] = 'Are you sure you wish to enable notification of new replies for this topic?';
$txt['notification_disable_topic'] = 'Are you sure you wish to disable notification of new replies for this topic?';

$txt['report_to_mod'] = 'Report to moderator';
$txt['issue_warning_post'] = 'Issue a warning because of this message';

$txt['unread_topics_visit'] = 'Recent Unread Topics';
$txt['unread_topics_visit_none'] = 'No unread topics found since your last visit.  <a href="' . $scripturl . '?action=unread;all">Click here to try all unread topics</a>.';
$txt['unread_topics_all'] = 'All Unread Topics';
$txt['unread_replies'] = 'Updated Topics';

$txt['who_title'] = 'Who\'s Online';
$txt['who_and'] = ' and ';
$txt['who_viewing_topic'] = ' are viewing this topic.';
$txt['who_viewing_board'] = ' are viewing this board.';
$txt['who_member'] = 'Member';

// No longer used by default theme, but for backwards compat
$txt['powered_by_php'] = 'Powered by PHP';
$txt['powered_by_mysql'] = 'Powered by MySQL';
$txt['valid_css'] = 'Valid CSS!';

// Current footer strings
$txt['valid_html'] = 'Valid HTML 4.01!';
$txt['valid_xhtml'] = 'Valid XHTML 1.0!';
$txt['wap2'] = 'WAP2';
$txt['rss'] = 'RSS';
$txt['xhtml'] = 'XHTML';
$txt['html'] = 'HTML';

$txt['guest'] = 'Guest';
$txt['guests'] = 'Guests';
$txt['user'] = 'User';
$txt['users'] = 'Users';
$txt['hidden'] = 'Hidden';
$txt['buddy'] = 'Buddy';
$txt['buddies'] = 'Buddies';
$txt['most_online_ever'] = 'Most Online Ever';
$txt['most_online_today'] = 'Most Online Today';

$txt['merge_select_target_board'] = 'Select the target board of the merged topic';
$txt['merge_select_poll'] = 'Select which poll the merged topic should have';
$txt['merge_topic_list'] = 'Select topics to be merged';
$txt['merge_select_subject'] = 'Select subject of merged topic';
$txt['merge_custom_subject'] = 'Custom subject';
$txt['merge_enforce_subject'] = 'Change the subject of all the messages';
$txt['merge_include_notifications'] = 'Include notifications?';
$txt['merge_check'] = 'Merge?';
$txt['merge_no_poll'] = 'No poll';

$txt['response_prefix'] = 'Re: ';
$txt['current_icon'] = 'Current Icon';
$txt['message_icon'] = 'Message Icon';

$txt['smileys_current'] = 'Current Smiley Set';
$txt['smileys_none'] = 'No Smileys';
$txt['smileys_forum_board_default'] = 'Forum/Board Default';

$txt['search_results'] = 'Search Results';
$txt['search_no_results'] = 'Sorry, no matches were found';

$txt['totalTimeLogged1'] = 'Total time logged in: ';
$txt['totalTimeLogged2'] = ' days, ';
$txt['totalTimeLogged3'] = ' hours and ';
$txt['totalTimeLogged4'] = ' minutes.';
$txt['totalTimeLogged5'] = 'd ';
$txt['totalTimeLogged6'] = 'h ';
$txt['totalTimeLogged7'] = 'm';

$txt['approve_thereis'] = 'There is';
$txt['approve_thereare'] = 'There are';
$txt['approve_member'] = 'one member';
$txt['approve_members'] = 'members';
$txt['approve_members_waiting'] = 'awaiting approval.';

$txt['notifyboard_turnon'] = 'Do you want a notification email when someone posts a new topic in this board?';
$txt['notifyboard_turnoff'] = 'Are you sure you do not want to receive new topic notifications for this board?';
$txt['notifyboard_subscribed'] = '%1$s has been subscribed to new topic notifications for this board.';
$txt['notifyboard_unsubscribed'] = '%1$s has been unsubscribed from new topic notifications for this board.';

$txt['notifytopic_subscribed'] = '%1$s has been subscribed to new reply notifications for this topic.';
$txt['notifytopic_unsubscribed'] = '%1$s has been unsubscribed from new reply notifications for this topic.';

$txt['notify_announcements'] = 'Allow the administrators to send me important news by email';
$txt['notifyannouncements_prompt'] = 'Do you want to receive forum newsletters, announcements and important notifications by email?';
$txt['notifyannouncements_subscribed'] = '%1$s has been subscribed to forum newsletters, announcements and important notifications.';
$txt['notifyannouncements_unsubscribed'] = '%1$s has been unsubscribed from forum newsletters, announcements and important notifications.';

$txt['unsubscribe_announcements_plain'] = 'To unsubscribe from forum newsletters, announcements and important notifications, follow this link:<br />%1$s';
$txt['unsubscribe_announcements_html'] = '<span style="font-size:small"><a href="%1$s">Unsubscribe</a> from forum newsletters, announcements and important notifications.</span>';

$txt['activate_code'] = 'Your activation code is';

$txt['find_members'] = 'Find Members';
$txt['find_username'] = 'Name, username, or email address';
$txt['find_buddies'] = 'Show Buddies Only?';
$txt['find_wildcards'] = 'Allowed Wildcards: *, ?';
$txt['find_no_results'] = 'No results found';
$txt['find_results'] = 'Results';
$txt['find_close'] = 'Close';

$txt['unread_since_visit'] = 'Show unread posts since last visit.';
$txt['show_unread_replies'] = 'Show new replies to your posts.';

$txt['change_color'] = 'Change Color';

$txt['quickmod_delete_selected'] = 'Remove Selected';

// In this string, don't use entities. (&amp;, etc.)
$txt['show_personal_messages'] = 'You have received one or more new personal messages.\\nWould you like to open a new window to view them?';

$txt['previous_next_back'] = '&laquo; previous';
$txt['previous_next_forward'] = 'next &raquo;';

$txt['movetopic_auto_board'] = '[BOARD]';
$txt['movetopic_auto_topic'] = '[TOPIC LINK]';
$txt['movetopic_default'] = 'This topic has been moved to ' . $txt['movetopic_auto_board'] . ".\n\n" . $txt['movetopic_auto_topic'];

$txt['upshrink_description'] = 'Shrink or expand the header.';

$txt['mark_unread'] = 'Mark unread';

$txt['ssi_not_direct'] = 'Please don\'t access SSI.php by URL directly; you may want to use the path (%1$s) or add ?ssi_function=something.';
$txt['ssi_session_broken'] = 'SSI.php was unable to load a session!  This may cause problems with logout and other functions - please make sure SSI.php is included before *anything* else in all your scripts!';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['preview_title'] = 'Preview post';
$txt['preview_fetch'] = 'Fetching preview...';
$txt['preview_new'] = 'New message';
$txt['error_while_submitting'] = 'The following error or errors occurred while posting this message:';
$txt['error_old_topic'] = 'Warning: this topic has not been posted in for at least %1$d days.<br />Unless you\'re sure you want to reply, please consider starting a new topic.';

$txt['split_selected_posts'] = 'Selected posts';
$txt['split_selected_posts_desc'] = 'The posts below will form a new topic after splitting.';
$txt['split_reset_selection'] = 'reset selection';

$txt['modify_cancel'] = 'Cancel';
$txt['mark_read_short'] = 'Mark Read';

$txt['pm_short'] = 'My Messages';
$txt['pm_menu_read'] = 'Read your messages';
$txt['pm_menu_send'] = 'Send a message';

$txt['hello_member_ndt'] = 'Hello';

$txt['unapproved_posts'] = 'Unapproved Posts (Topics: %1$d, Posts: %2$d)';

$txt['ajax_in_progress'] = 'Loading...';

$txt['mod_reports_waiting'] = 'There are currently %1$d moderator reports open.';

$txt['view_unread_category'] = 'Unread Posts';
$txt['verification'] = 'Verification';
$txt['visual_verification_description'] = 'Type the letters shown in the picture';
$txt['visual_verification_sound'] = 'Listen to the letters';
$txt['visual_verification_request_new'] = 'Request another image';

// Sub menu labels
$txt['summary'] = 'Summary';
$txt['account'] = 'Account Settings';
$txt['forumprofile'] = 'Forum Profile';

$txt['modSettings_title'] = 'Features and Options';
$txt['package'] = 'Package Manager';
$txt['errlog'] = 'Error Log';
$txt['edit_permissions'] = 'Permissions';
$txt['mc_unapproved_attachments'] = 'Unapproved Attachments';
$txt['mc_unapproved_poststopics'] = 'Unapproved Posts and Topics';
$txt['mc_reported_posts'] = 'Reported Posts';
$txt['modlog_view'] = 'Moderation Log';
$txt['calendar_menu'] = 'View Calendar';

//!!! Send email strings - should move?
$txt['send_email'] = 'Send Email';
$txt['send_email_disclosed'] = 'Note this will be visible to the recipient.';
$txt['send_email_subject'] = 'Email Subject';

$txt['ignoring_user'] = 'You are ignoring this user.';
$txt['show_ignore_user_post'] = 'Show me the post.';

$txt['spider'] = 'Spider';
$txt['spiders'] = 'Spiders';
$txt['openid'] = 'OpenID';

$txt['downloads'] = 'Downloads';
$txt['filesize'] = 'Filesize';
$txt['subscribe_webslice'] = 'Subscribe to Webslice';

// Restore topic
$txt['restore_topic'] = 'Restore Topic';
$txt['restore_message'] = 'Restore';
$txt['quick_mod_restore'] = 'Restore Selected';

// Editor prompt.
$txt['prompt_text_email'] = 'Please enter the email address.';
$txt['prompt_text_ftp'] = 'Please enter the ftp address.';
$txt['prompt_text_url'] = 'Please enter the URL you wish to link to.';
$txt['prompt_text_img'] = 'Enter image location';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['autosuggest_delete_item'] = 'Delete Item';

// Debug related - when $db_show_debug is true.
$txt['debug_templates'] = 'Templates: ';
$txt['debug_subtemplates'] = 'Sub templates: ';
$txt['debug_language_files'] = 'Language files: ';
$txt['debug_stylesheets'] = 'Style sheets: ';
$txt['debug_files_included'] = 'Files included: ';
$txt['debug_kb'] = 'KB.';
$txt['debug_show'] = 'show';
$txt['debug_cache_hits'] = 'Cache hits: ';
$txt['debug_cache_seconds_bytes'] = '%1$ss - %2$s bytes';
$txt['debug_cache_seconds_bytes_total'] = '%1$ss for %2$s bytes';
$txt['debug_queries_used'] = 'Queries used: %1$d.';
$txt['debug_queries_used_and_warnings'] = 'Queries used: %1$d, %2$d warnings.';
$txt['debug_query_in_line'] = 'in <em>%1$s</em> line <em>%2$s</em>, ';
$txt['debug_query_which_took'] = 'which took %1$s seconds.';
$txt['debug_query_which_took_at'] = 'which took %1$s seconds at %2$s into request.';
$txt['debug_show_queries'] = '[Show Queries]';
$txt['debug_hide_queries'] = '[Hide Queries]';

// Stop Forum Spam
$txt['stopforumspam_configure'] = 'StopForumSpam.com - Anti Spam Filter';
$txt['stopforumspam_configure_desc'] = 'Uses the StopForumSpam.com system to block spam bots using email, IP address, and username.';
$txt['stopforumspam_enabled'] = 'Enable Stop Forum Spam';
$txt['stopforumspam_checkip'] = 'Check IP Address';
$txt['stopforumspam_checkemail'] = 'Check Email Address';
$txt['stopforumspam_checkusername'] = 'Check Username';
$txt['stopforumspam_err_youareaspammer'] = 'Your details match that of a known spammer. If this is not the case please contact an administrator of the forum.';

// SolveMedia
$txt['solvemedia_settings'] = 'Solve Media Puzzle Verification System';
$txt['solvemedia_settings_desc'] = 'Use the Solve Media Puzzle Verification System. Get Solve Media API keys <a href="http://portal.solvemedia.com/portal/public/signup" target="_blank">here</a>.';
$txt['solvemedia_enabled'] = 'Use Solve Media Puzzle Verification System';
$txt['solvemedia_theme'] = 'Solve Media Theme';
$txt['solvemedia_theme_purple'] = 'Purple';
$txt['solvemedia_theme_black'] = 'Black';
$txt['solvemedia_theme_red'] = 'Red';
$txt['solvemedia_theme_white'] = 'White';
$txt['solvemedia_publickey'] = 'Solve Media Public Key';
$txt['solvemedia_privatekey'] = 'Solve Media Private Key';
$txt['solvemedia_hashkey'] = 'Solve Media Hash Key';
$txt['solvemedia_no_key_question'] = 'Don\'t have Solve Media API keys? Visit <a href="https://portal.solvemedia.com/portal/public/signup" target="_blank">SolveMedia.com</a>';
$txt['solvemedia_get_key'] = 'Get your Solve Media API keys here.';
$txt['solvemedia_lang'] = 'Solve Media Language';
$txt['solvemedia_lang_en'] = 'English';
$txt['solvemedia_lang_es'] = 'Spanish';
$txt['solvemedia_lang_fr'] = 'French';
$txt['solvemedia_lang_it'] = 'Italian';
$txt['solvemedia_lang_de'] = 'German';
$txt['solvemedia_lang_ca'] = 'Catalan';
$txt['solvemedia_lang_pl'] = 'Polish';
$txt['solvemedia_lang_hu'] = 'Hungarian';
$txt['solvemedia_lang_sv'] = 'Swedish';
$txt['solvemedia_lang_no'] = 'Norwegian';
$txt['solvemedia_lang_pt'] = 'Portuguese';
$txt['solvemedia_lang_nl'] = 'Dutch';
$txt['solvemedia_lang_tr'] = 'Turkish';
$txt['solvemedia_lang_ja'] = 'Japanese';
$txt['solvemedia_lang_yi'] = 'Yiddish';
$txt['solvemedia_pleaseverify'] = 'Please verify that you are human by following the instructions and completing the puzzle below:';
$txt['error_wrong_solvemedia_verification'] = 'You did not complete the Solve Media puzzle correctly.';


// reCaptcha
$txt['recaptcha_settings'] = 'reCAPTCHA Verification System';
$txt['recaptcha_settings_desc'] = 'Use the reCAPTCHA Verification System. Get reCAPTCHA API keys <a href="http://www.google.com/recaptcha" target="_blank">here</a>.';
$txt['recaptcha_enabled'] = 'Use reCAPTCHA Verification System';
$txt['recaptcha_publickey'] = 'reCAPTCHA Public Key';
$txt['recaptcha_privatekey'] = 'reCAPTCHA Private Key';
$txt['recaptcha_lang'] = 'Language';
$txt['recaptcha_theme'] = 'Theme';
$txt['recaptcha_theme_red'] = 'Red';
$txt['recaptcha_theme_white'] = 'White';
$txt['recaptcha_theme_blackglass'] = 'Blackglass';
$txt['recaptcha_theme_clean'] = 'Clean';
$txt['recaptcha_lang_en'] = 'English';
$txt['recaptcha_lang_es'] = 'Spanish';
$txt['recaptcha_lang_fr'] = 'French';
$txt['recaptcha_lang_it'] = 'Italian';
$txt['recaptcha_lang_de'] = 'German';
$txt['recaptcha_lang_pt'] = 'Portuguese';
$txt['recaptcha_lang_nl'] = 'Dutch';
$txt['recaptcha_lang_tr'] = 'Turkish';
$txt['recaptcha_lang_ru'] = 'Russian';
$txt['recaptcha_pleaseverify'] = 'Please verify that you are human by solving the CAPTCHA below.';
$txt['error_wrong_recaptcha_verification'] = 'You did not enter the CAPTCHA correctly.';


// emanuele Mobile Device Detect
$txt['mobile_theme_id'] = 'Theme to use for mobile devices';

// Begin Login Security Text Strings
$txt['ls_login_security'] = 'Login Security';
$txt['ls_invalid_ip'] = 'Login failed. This account is protected by ip address. If you are the owner of this account you can reset this by creating a <a href="%link">secure login link</a> sent to your email address.';
$txt['ls_account_locked'] = 'Account Locked due to failed logins. This account has been locked until %min. If you are the owner of this account you can reset this by creating a <a href="%link">secure login link</a> sent to your email address.';
$txt['ls_secure_email_subject'] = 'Secure Login Link';
$txt['ls_secure_email_body'] = 'Hello %name,
A secure login link has been requested for your account.
If you requested this link please follow the link below to login into your account.

%link

This link expires in %min minutes.

Requesters IP address: %ip';

$txt['ls_matched_members'] = 'Matched forum members with same ip address:';

$txt['ls_failed_email_subject'] = 'Failed Login Attempt';
$txt['ls_failed_email_body'] = 'Hello %name,
We have detected a failed login attempt on your account.

%membermatches

IP address of the failed login attempt: %ip
';

// Settings
$txt['ls_securehash_expire_minutes'] = 'Secure Login Link Expire time in minutes';
$txt['ls_allowed_login_attempts'] = 'Number of allowed login attempts';
$txt['ls_allowed_login_attempts_mins'] = 'Login attempt check time range in minutes';
$txt['ls_login_retry_minutes'] = 'Account locked retry minutes';
$txt['ls_allow_ip_security'] = 'Allow users to protect their account by ip address';
$txt['ls_send_mail_failed_login'] = 'Send email on failed login attempt';

$txt['ls_current_ip_address'] = 'Current IP Address: ';
$txt['ls_ip_address_protection'] = 'IP Address Account Protection';
$txt['ls_ip_address_protection_note'] = 'You can allow multiple ips by separating them with a comma';

// END  Login Security Text Strings

// Social Sharing Icons
$txt['smi_buddies_title'] = 'Social Networks';
$txt['smi_facebook_title'] = 'Facebook';
$txt['smi_facebook_desc'] = 'Input your Facebook username.';
$txt['smi_myspace_title'] = 'MySpace';
$txt['smi_myspace_desc'] = 'Input your MySpace username.';
$txt['smi_twitter_title'] = 'Twitter';
$txt['smi_twitter_desc'] = 'Input your Twitter username.';
$txt['smi_youtube_title'] = 'Youtube';
$txt['smi_youtube_desc'] = 'Input your Youtube username.';
$txt['smi_deviantart_title'] = 'DeviantArt';
$txt['smi_deviantart_desc'] = 'Input your DeviantArt username.';
$txt['smi_pinterest_title'] = 'Pinterest';
$txt['smi_pinterest_desc'] = 'Input your Pinterest username.';
$txt['smi_googleplus_title'] = 'Google+';
$txt['smi_googleplus_desc'] = 'Input your Google+ User ID.';
$txt['smi_linkedin_title'] = 'LinkedIn';
$txt['smi_linkedin_desc'] = 'Copy & Paste your LinkedIn profile link.';
$txt['smi_skype_title'] = 'Skype';
$txt['smi_skype_desc'] = 'Input your Skype username.';

/**
* User Mass Actions (uma)
*
* @package uma
* @author emanuele
* @copyright 2011 emanuele, Simple Machines
* @license http://www.simplemachines.org/about/smf/license.php BSD
*
*/
$txt['admin_change_primary_membergroup'] = 'Change primary member group';
$txt['admin_change_secondary_membergroup'] = 'Change/add additional member group';
$txt['confirm_remove_membergroup'] = 'Selecting this all the membergroups will be removed! Are you sure?';
$txt['confirm_change_primary_membergroup'] = 'Are you sure you want to change the primary group of the selected members?';
$txt['confirm_change_secondary_membergroup'] = 'Are you sure you want to change the additional group of the selected members?';
$txt['admin_ban_usernames'] = 'Ban by usernames';
$txt['admin_ban_useremails'] = 'Ban by email addresses';
$txt['admin_ban_userips'] = 'Ban by IPs';
$txt['admin_ban_usernames_and_emails'] = 'Ban by usernames and email addresses';
$txt['users_mass_action_ban_name'] = 'Name of the ban list to be used for mass ban actions';


// OneAll Social Login (https://docs.oneall.com/plugins/)
$txt['oasl_title'] = 'OneAll Social Login';
$txt['oasl_config'] = 'Configuration';
$txt['oasl_settings_descr'] = 'OneAll Social Login Settings';
$txt['oasl_user_does_not_exist'] = "<strong>This social network has not yet been linked to an account.</strong><br /><br />Please use the registration form to create a new account. If you already have an account, open your profile settings to connect the social network to it.";

// Added by Related Topics
$txt['admin_related_topic'] = 'Related Topics';
$txt['admin_related_topics_information'] = 'Information';
$txt['admin_related_topics_settings'] = 'Settings';
$txt['admin_related_topics_methods'] = 'Methods';

$txt['related_topics_admin_title'] = 'Related Topics';
$txt['related_topics_admin_desc'] = '';


$txt['related_version_info'] = 'Version Information';
$txt['related_installed_version'] = 'Installed Version';
$txt['related_latest_version'] = 'Latest Version';

$txt['related_topics_ignored_boards'] = 'Ignored Boards';

$txt['related_topics_methods_title'] = 'Methods';
$txt['related_topics_methods'] = 'Select methods used for determining Related Topics<div class="smalltext">Rebuild of index is required after changing these settings</div>';

$txt['related_topics_index'] = 'Index';
$txt['related_topics_rebuild'] = 'Rebuild Indexes';
$txt['related_topics_rebuild_desc'] = 'Use this after changing settings or to build initial cache';

$txt['relatedFulltext'] = 'Fulltext';

$txt['related_topics_settings_title'] = 'Related Topics';
$txt['relatedTopicsEnabled'] = 'Enable Related Topics';
$txt['relatedTopicsCount'] = 'How many related topics to show';

$txt['no_methods_selected'] = 'You haven\'t selected which methods to use to determine related topics';

$txt['related_topics'] = 'Related Topics';
// END Added by Related Topics

// Post History Start
$txt['core_settings_item_posthistory'] = 'Post History';
$txt['core_settings_item_posthistory_desc'] = 'Store history of edits of message content into database.';

$txt['view_post_history'] = 'View Edits of Post';
$txt['title_view_post_history'] = 'Viewing Post History for - %1$s';

$txt['ph_last_edit'] = 'Latest Edit By';
$txt['ph_last_time'] = 'Edit Time';
$txt['ph_view_edit'] = 'View Post';

$txt['ph_original_edit'] = 'original';
$txt['ph_current_edit'] = 'current';
$txt['ph_current_original_edit'] = 'current, original';

$txt['ph_no_edits'] = 'No one has edited this post yet';

$txt['compare_selected'] = 'Compare Selected';
$txt['restore'] = 'Restore';

$txt['permissionname_posthistory_view'] = 'View Edit History of Post';
$txt['permissionhelp_posthistory_view'] = 'Allows user to view past versions of post.';
$txt['permissionname_posthistory_view_own'] = 'Own post';
$txt['permissionname_posthistory_view_any'] = 'Any post';
$txt['permissionname_posthistory_restore'] = 'Restore older version';
$txt['permissionhelp_posthistory_restore'] = 'Allows user to start editing from older version of post.';
$txt['permissionname_posthistory_restore_own'] = 'Own post';
$txt['permissionname_posthistory_restore_any'] = 'Any post';
$txt['permissionname_simple_posthistory_view_own'] = 'View edit history of their own post';
$txt['permissionname_simple_posthistory_view_any'] = 'View edit history of someone else\'s post';
$txt['permissionname_simple_posthistory_restore_own'] = 'Restore older version of own post';
$txt['permissionname_simple_posthistory_restore_any'] = 'Restore older versions of someone else\'s post';

$txt['cannot_posthistory_view_any'] = 'You are not allowed to view history of this post!';
$txt['cannot_posthistory_restore_own'] = 'You are not allowed to start editing from older version of your posts!';
$txt['cannot_posthistory_restore_any'] = 'You are not allowed to start editing from older version of someone else\'s post!';
// Post History END

// geoIP menu tabs
// Spuds BSD
$txt['geoIP'] = 'Geo-IP';
$txt['geoIPMap'] = 'Map Settings';
$txt['geoIPMain'] = 'Geo-IP';
$txt['geoIPSettings'] = 'Registration Settings';
$txt['geoIPOnlineMap'] = 'Online Member Map';
$txt['geoIP_info'] = 'GeoIP information for IP';
$txt['permissionname_geoIP_view'] = 'View Who\'s Online Map';
$txt['permissionhelp_geoIP_view'] = 'Allow the members to view the geoIP Whos Online Map.  If not set, these members will not see the map.';
$txt['permissionname_geoIP_viewdetail'] = 'Identify Pin owner in the Online Map';
$txt['permissionhelp_geoIP_viewdetail'] = 'Allow the members to view which member a pin belongs to on the geoIP Whos Online Map.  If not set, these members will not see the pin details.';
$txt['scheduled_task_desc_geoIP'] = 'Attempts to retrieve and install the latest database (of the type you installed) from Maxmind';
$txt['scheduled_task_geoIP'] = 'geoIP Database Update';
$txt['cannot_geoIP_view'] = 'Sorry, you are not allowed to view the Whos Online Map.';



$txt['anti_spam_links'] = 'Anti-Spam-Links Mod Settings';
$txt['error_anti_spam_links_nolinks_guest'] = 'Sorry, guests are not allowed to post external links.';
$txt['error_anti_spam_links_nolinks_member'] = 'Sorry, you are not allowed to post external links.';
$txt['anti_spam_links_newbielink'] = ' newbielink:';
$txt['anti_spam_links_nonactive'] = '[nonactive]';
$txt['anti_spam_links_newbielinks_info'] = 'To curb spam posts, external links are [nonactive] until %1$s posts';
$txt['anti_spam_links_nofollowlinks_info'] = 'To curb spam posts, external links are set [nofollow] (which means no pagerank) until %1$s posts';
$txt['anti_spam_links_nofollow'] = '[nofollow]';
$txt['anti_spam_links_nolinks'] = 'Post count under which members cannot post external links';
$txt['anti_spam_links_newbielinks'] = 'Post count under which members external links are shown [nonactive] and without http://';
$txt['anti_spam_links_nofollowlinks'] = 'Post count under which members external links are set [nofollow]';
$txt['anti_spam_links_zero_disable'] = '[Excludes any ' . $boardurl . ' links]<br />(Use 0 to disable)';
$txt['anti_spam_links_guests'] = 'Guests... ';
$txt['anti_spam_links_guests_opt0'] = '(disable for guests)';
$txt['anti_spam_links_guests_opt1'] = 'can not post links';
$txt['anti_spam_links_guests_opt2'] = 'links are shown [nonactive]';
$txt['anti_spam_links_guests_opt3'] = 'links are set [nofollow]';


// Custom Action Mod
$txt['custom_action_shorttitle'] = 'Custom Actions';
$txt['core_settings_item_ca'] = 'Custom Actions';
$txt['core_settings_item_ca_desc'] = 'With custom actions you can create custom pages wrapped in your forum\'s theme.';
$txt['custom_action_desc'] = 'From this page you can create and modify your own custom pages.';
$txt['custom_action_title'] = 'Custom Actions';
$txt['custom_action_title_sub'] = 'Sub-Actions For "%1$s"';
$txt['custom_action_none'] = 'You have not created any custom actions yet!';
$txt['custom_action_none_sub'] = 'You have not created any sub-actions for the "%1$s" action yet!';
$txt['custom_action_name'] = 'Action Name';
$txt['custom_action_type'] = 'Type';
$txt['custom_action_type_0'] = 'HTML';
$txt['custom_action_type_1'] = 'BBC';
$txt['custom_action_type_2'] = 'PHP';
$txt['custom_action_sub_actions'] = 'Sub-Actions';
$txt['custom_action_enabled'] = 'Enabled';
$txt['custom_action_make_new'] = 'New Action';
$txt['custom_action_make_new_sub'] = 'New Sub-Action';
$txt['custom_action_not_found'] = 'The requested action was not found.';
$txt['custom_action_invalid_url'] = 'Action URLs may only contain letters, numbers and underscores.';
$txt['custom_action_settings'] = 'Action Settings:';
$txt['custom_action_url_desc'] = 'This may only contain letters, numbers and underscores.';
$txt['custom_action_permissions_mode'] = 'Permissions Mode';
$txt['custom_action_permissions_mode_0'] = 'Visible To Everyone';
$txt['custom_action_permissions_mode_1'] = 'Visible To Selected Groups';
$txt['custom_action_permissions_mode_2'] = 'Same As Parent Action';
$txt['custom_action_body'] = 'Body';
$txt['custom_action_body_html'] = 'HTML Body';
$txt['custom_action_body_php'] = 'Template Code';
$txt['custom_action_delete_sure'] = 'Are you sure you want to delete this action?';
$txt['custom_action_header'] = 'HTML Headers';
$txt['custom_action_source'] = 'Source File Code';
$txt['custom_action_source_desc'] = 'This code will be evaluated before any templates are displayed. If you don\'t understand this you should just put all your code in the template code box. No output should be displayed here.';
$txt['custom_action_header_desc'] = 'This code will be displayed in the header section.';
$txt['custom_action_body_html_desc'] = 'This code will be displayed in the body section.';
$txt['custom_action_body_php_desc'] = 'You should display all output here.';
$txt['custom_action_body_desc'] = 'This is the body of your custom page.';
$txt['custom_action_url'] = 'Action URL';
$txt['custom_action_settings_code'] = 'Action Code:';
$txt['custom_action_menu'] = 'Show Menu Button';

// Tagging system
$txt['tags_menu_btn'] = 'Tags';
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


// Header Unapproved.
$txt['total_unapproved_topics'] = 'Total Unapproved Topics';
$txt['total_unapproved_posts'] = 'Total Unapproved Posts';

// Make PM unread emanuele BSD
$txt['mark_unread_all'] = 'Mark Conversation unread';

$txt['guests_sendtopic_require_captcha'] = 'Guests must pass verification when sending a topic';


// Mentions System
$txt['mentions'] = 'Mentions';
$txt['scheduled_task_removeMentions'] = 'Remove seen mentions';
$txt['scheduled_task_desc_removeMentions'] = 'Automatically removes seen mentions older than the specified days';

?>