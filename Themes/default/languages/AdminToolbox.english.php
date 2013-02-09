<?php
// Version: 2.0; ManageToolbox

$txt['toolbox_title']  = 'Admin Toolbox';
$txt['toolbox_run_now'] = 'Run task now';
$txt['lastLoggedIn'] = 'Last Active';
$txt['userid'] = 'User ID';

$txt['toolbox_recount'] = 'Recount User Posts';
$txt['toolbox_recount_info'] = 'Run this function if your user post counts need updating.  It will recount all (countable) posts made by each user and then update their post count totals';

$txt['toolbox_stats'] = 'Rebuild Daily Statistics';
$txt['toolbox_stats_info'] = 'Run this function if your daily posts, topics and registration statistics need updating.  This may be necessary if you were not collecting daily stats from the start, or if you imported your data from another system.';
$txt['toolbox_StatsValidate_info'] = 'There is missing data, you must decide how the stats rebuild should proceed';
$txt['toolbox_stats_validate'] = 'Confirm Stats Rebuilding';
$txt['toolbox_stats_info1'] = 'This function recounts the Daily Posts, Daily New Topics and Daily Registrations.  This data is used by the Forum History and General Status areas.  Rebuilding this data may be necessary if you were not collecting daily stats from the beginning, or if you imported your data from another system.';
$txt['toolbox_stats_info2'] = '<strong>***NOTICE***</strong> It is strongly recommended that you make a copy of your log_activity table so you can back out any changes in the event you should want or need to.';
$txt['toolbox_stats_info3'] = '<strong>***NOTICE***</strong> This function <strong>will change</strong> the above totals, how much will vary from site to site.  The updated values will reflect what *is* in your database, not *what occurred* as a history.  As an example you have 2 new topics and 3 posts in each topic.  Later you delete one post from one topic and later the entire other topic.  Your old stats will still show 2 new topics and 6 new posts, running this function will change that to just 1 new topic 2 new posts.';
$txt['toolbox_stats_warn'] = 'The system found data in the message table starting from %s, your statistics start on %s.  You have several options on how to handle the lost data for Daily Page Views and Daily Most on Line as that data <strong>can not be rebuilt</strong>.  The options are presented below, and the potential side effects are listed.';
$txt['toolbox_skip'] = 'Bypass';
$txt['toolbox_skip_desc'] = 'Do not add in any missing data.  This will just validate, and correct if necessary, the number of daily posts/topics/registrations in the activity log table, no new lines will be added.';
$txt['toolbox_zero'] = 'Zero';
$txt['toolbox_zero_desc'] = 'This will rebuild your stats starting from the earliest message date, correctly rebuilding messages/topics and registrations totals per day, but use 0 as the value for the missing data in page views and most on-line.  This will have the effect of lowering your averages for these values.';
$txt['toolbox_average'] = 'Average';
$txt['toolbox_average_desc'] = 'This will rebuild your stats starting from the date of the earliest message, correctly rebuilding messages/topics and registrations totals per day, but will use the sites current averages for the missing page views and most on-line data. This will help maintain your sites current averages for page views and most on-line, but the overall average values will decrease somewhat';
$txt['toolbox_balanced'] = 'Balanced';
$txt['toolbox_balanced_desc'] = 'This will rebuild your stats from the beginning, correctly rebuilding messages/topics and registrations totals per day but will use a balanced approach to fill in the missing page views and most on-line data.  This estimates missing values (linear regression) based on the sites current values.  Older dates will have lower values and the values will increase with newer dates. This will lower your current averages depending on how much data needs to be rebuilt but the data will look the most natural.';
$txt['toolbox_rebuild_select'] = 'How to rebuild missing data';

$txt['toolbox_merge'] = 'Merge Users';
$txt['toolbox_to'] = 'Merging To (Destination)';
$txt['toolbox_from'] = 'Moving From (Source)';
$txt['toolbox_merge_info'] = 'This will merge two user ID\'s in to a single user, by moving data associated with the source user to the destination user, this includes: Posts, Topics (started by), Attachments, PM\'s (sent and received), Calendar Events, Polls (started by).<br /><br />For certain modifications it will also move the data associated with the modification, these include: Aeva (albums, items, comments, ratings), Bookmarks, <a href="http://custom.simplemachines.org/mods/index.php?mod=381">SMF Links</a> (ownership and ratings) &amp; <a href="http://custom.simplemachines.org/mods/index.php?mod=2621">Drafts</a>';
$txt['toolbox_merge_selection'] = 'Begin by entering the users name in the box, a selection list will appear from which you must choose the member name.  Alternatively you can directly enter the user id (number) in the box or use the search icon to search for a member to select.  When you select Run Task Now, a confirmation screen will appear with your choices before the action proceeds.';
$txt['deluser_help'] = 'This will remove the from/source user after the merge operation is complete.  You don\'t have to do this now but it is recommended at some point';
$txt['adjustuser_help'] = 'This optional step will combine some of the source user profile information in to the destination users profile.  It will among other things keep the earliest join date, combine karma totals, combine total time logged in, use the source id\'s profile settings for personal text, website, etc only if those profile fields are not filled in for the destination user (ensuring no data is overwritten)';
$txt['toolbox_adjust_true'] = 'User profile information will be combined';
$txt['toolbox_del_true'] = 'User will be deleted after the merging operation';

$txt['toolbox_merge_to'] = 'Enter the name of the user data will be <strong>ADDED TO</strong>';
$txt['toolbox_merge_from'] = 'Enter the name of the user data will be <strong>REMOVED FROM</strong>';
$txt['autosuggest_view_item'] = 'Select to view this member profile';
$txt['toolbox_adjustuser'] = 'Allow combining of profile information as needed';
$txt['toolbox_deluser'] = 'Allow deleting the source user after merging';

$txt['toolbox_inactive'] = 'Mark as Read';
$txt['toolbox_inactive_info'] = 'This function will mark all boards/topics read for users inactive for more than the defined number of days';
$txt['toolbox_inactive_days'] = 'Number of days a user must be inactive (default is 60)';

$txt['toolbox_MergeMembersValidate_info'] = 'Please validate that you want to perform this user merge as it is <strong>not</strong> reversible';
$txt['toolbox_mergeuser_check'] = 'validate user merge';
$txt['toolbox_validate'] = 'Confirm User Merging';

$txt['toolbox_info'] = 'Recount Post totals, merge members and perform other specialized tasks with these tools.';
$txt['toolbox_done'] = 'The Admin Toolbox task \'%1$s\' was executed successfully.';
$txt['toolbox_zeroid'] = 'Admin Toolbox error: Supplied user id\'s can not be empty.';
$txt['toolbox_adminid'] = 'Admin Toolbox error: This action is not available on the base admin account';
$txt['toolbox_sameid'] = 'Admin Toolbox error: The source and destination ID\'s can not be the same.';
$txt['toolbox_badid'] = 'Admin Toolbox error: One or both of the supplied ID\'s does not exist.';
$txt['toolbox_baddelete'] = 'Admin Toolbox error: Deleting the ID you are using is not permitted.';

$txt['toolbox_merge_0'] = 'Preparing Data';
$txt['toolbox_merge_1'] = 'Merging Topics';
$txt['toolbox_merge_2'] = 'Merging Posts';
$txt['toolbox_merge_3'] = 'Merging Attachments';
$txt['toolbox_merge_4'] = 'Merging Private Messages';
$txt['toolbox_merge_5'] = 'Merging Calendar Events, Polls';
$txt['toolbox_merge_aeva'] = 'Merging Aeva Albums';
$txt['toolbox_merge_drafts'] = 'Merging Drafts';
$txt['toolbox_merge_bookmarks'] = 'Merging Bookmarks';
$txt['toolbox_merge_links'] = 'Merging Links';

$txt['permissionname_admintoolbox'] = 'Access the Admin Toolbox';
$txt['permissionhelp_admintoolbox'] = 'Allow the members with this permission to access the Admin Toolbox.  Why would you do this?';

?>