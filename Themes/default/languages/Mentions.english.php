<?php
/**
 * Language file for mentions
 *
 * @author Shitiz Garg <mail@dragooon.net>
 * @copyright 2014 Shitiz Garg
 * @license Simplified BSD (2-Clause) License
 */

global $txt, $context;

$txt['mentions_subject'] = 'MENTIONNAME, you have been mentioned at a post in ' . $context['forum_name'];
$txt['mentions_body'] = 'Hello MENTIONNAME!

MEMBERNAME mentioned you in the post "POSTNAME", you can view the post at POSTLINK

Regards,
' . $context['forum_name'];
$txt['mentions'] = 'Mentions';
$txt['mentions_profile_title'] = 'Posts mentioning %s';
$txt['mentions_post_subject'] = 'Subject';
$txt['mentions_member'] = 'Mentioned By';
$txt['mentions_post_time'] = 'Mentioned Time';
$txt['permissionname_mention_member'] = 'Mention members';
$txt['permissionhelp_mention_member'] = 'Allow members to tag other members and alert them via mentioning them via @username syntax';
$txt['email_mentions'] = 'E-mail mention notifications';
$txt['mentions_remove_days'] = 'Remove mentions older than these days<div class="smalltext">This option will remove mentions which are seen and older than the specified amount of days. Make sure to enable the <a href="' . $scripturl . '?action=admin;area=scheduledtasks">Scheduled Task here</a></div>';
$txt['mentions_email_default'] = 'Enable mentions e-mail by default<div class="smalltext">Check this if you\'d like to have new users receive e-mail notification for their mentions by default (only applies to new registrations)</div>';
$txt['mentions_permissions_notice'] = 'Please remember to set permissions to allow individual members to mention others';
$txt['mentions_email_default_now'] = 'Enable mentions e-mail for current members<div class="smalltext">Only check this if you\'d like existing members to have their e-mails enabled</div>';