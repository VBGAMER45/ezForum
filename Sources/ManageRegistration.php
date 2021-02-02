<?php

/**
 * ezForum http://www.ezforum.com
 * Copyright 2011 ezForum
 * License: BSD
 *
 * Based on:
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0.18
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file helps the administrator setting registration settings and policy
	as well as allow the administrator to register new members themselves.

	void RegCenter()
		- entrance point for the registration center.
		- accessed by ?action=admin;area=regcenter.
		- requires either the moderate_forum or the admin_forum permission.
		- loads the Login language file and the Register template.
		- calls the right function based on the subaction.

	void AdminRegister()
		- a function to register a new member from the admin center.
		- accessed by ?action=admin;area=regcenter;sa=register
		- requires the moderate_forum permission.
		- uses the admin_register sub template of the Register template.
		- allows assigning a primary group to the member being registered.

	void EditAgreement()
		- allows the administrator to edit the registration agreement, and
		  choose whether it should be shown or not.
		- accessed by ?action=admin;area=regcenter;sa=agreement.
		- uses the Admin template and the edit_agreement sub template.
		- requires the admin_forum permission.
		- uses the edit_agreement administration area.
		- writes and saves the agreement to the agreement.txt file.

	void SetReserve()
		- set the names under which users are not allowed to register.
		- accessed by ?action=admin;area=regcenter;sa=reservednames.
		- requires the admin_forum permission.
		- uses the reserved_words sub template of the Register template.

	void ModifyRegistrationSettings()
		- set general registration settings and Coppa compliance settings.
		- accessed by ?action=admin;area=regcenter;sa=settings.
		- requires the admin_forum permission.
*/

// Main handling function for the admin approval center
function RegCenter()
{
	global $modSettings, $context, $txt, $scripturl;

	// Old templates might still request this.
	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'browse')
		redirectexit('action=admin;area=viewmembers;sa=browse' . (isset($_REQUEST['type']) ? ';type=' . $_REQUEST['type'] : ''));

	$subActions = array(
		'register' => array('AdminRegister', 'moderate_forum'),
		'agreement' => array('EditAgreement', 'admin_forum'),
		'policy' => array('EditPrivacyPolicy', 'admin_forum'),
		'reservednames' => array('SetReserve', 'admin_forum'),
		'settings' => array('ModifyRegistrationSettings', 'admin_forum'),
	);

	// Work out which to call...
	$context['sub_action'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (allowedTo('moderate_forum') ? 'register' : 'settings');

	// Must have sufficient permissions.
	isAllowedTo($subActions[$context['sub_action']][1]);

	// Loading, always loading.
	loadLanguage('Login');
	loadTemplate('Register');

	// Next create the tabs for the template.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['registration_center'],
		'help' => 'registrations',
		'description' => $txt['admin_settings_desc'],
		'tabs' => array(
			'register' => array(
				'description' => $txt['admin_register_desc'],
			),
			'agreement' => array(
				'description' => $txt['registration_agreement_desc'],
			),
			'policy' => array(
				'description' => $txt['privacy_policy_desc'],
			),
			'reservednames' => array(
				'description' => $txt['admin_reserved_desc'],
			),
			'settings' => array(
				'description' => $txt['admin_settings_desc'],
			)
		)
	);

	// Finally, get around to calling the function...
	$subActions[$context['sub_action']][0]();
}

// This function allows the admin to register a new member by hand.
function AdminRegister()
{
	global $txt, $context, $sourcedir, $scripturl, $smcFunc, $modSettings;

	if (!empty($_POST['regSubmit']))
	{
		checkSession();

		foreach ($_POST as $key => $value)
			if (!is_array($_POST[$key]))
				$_POST[$key] = htmltrim__recursive(str_replace(array("\n", "\r"), '', $_POST[$key]));

		$regOptions = array(
			'interface' => 'admin',
			'username' => $_POST['user'],
			'email' => $_POST['email'],
			'password' => $_POST['password'],
			'password_check' => $_POST['password'],
			'check_reserved_name' => true,
			'check_password_strength' => false,
			'check_email_ban' => false,
			'send_welcome_email' => isset($_POST['emailPassword']) || empty($_POST['password']),
			'require' => isset($_POST['emailActivate']) ? 'activation' : 'nothing',
			'memberGroup' => empty($_POST['group']) || !allowedTo('manage_membergroups') ? 0 : (int) $_POST['group'],
		);

		if (empty($_POST['requireAgreement']) && empty($modSettings['force_gdpr']))
			$regOptions['theme_vars']['agreement_accepted'] = time();

		if (empty($_POST['requirePolicyAgreement']) && empty($modSettings['force_gdpr']))
			$regOptions['theme_vars']['policy_accepted'] = time();

		require_once($sourcedir . '/Subs-Members.php');
		$memberID = registerMember($regOptions);
		if (!empty($memberID))
		{
			$context['new_member'] = array(
				'id' => $memberID,
				'name' => $_POST['user'],
				'href' => $scripturl . '?action=profile;u=' . $memberID,
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $memberID . '">' . $_POST['user'] . '</a>',
			);
			$context['registration_done'] = sprintf($txt['admin_register_done'], $context['new_member']['link']);
		}
	}

	// Basic stuff.
	$context['sub_template'] = 'admin_register';
	$context['page_title'] = $txt['registration_center'];

	// Load the assignable member groups.
	if (allowedTo('manage_membergroups'))
	{
		$request = $smcFunc['db_query']('', '
			SELECT group_name, id_group
			FROM {db_prefix}membergroups
			WHERE id_group != {int:moderator_group}
				AND min_posts = {int:min_posts}' . (allowedTo('admin_forum') ? '' : '
				AND id_group != {int:admin_group}
				AND group_type != {int:is_protected}') . '
				AND hidden != {int:hidden_group}
			ORDER BY min_posts, CASE WHEN id_group < {int:newbie_group} THEN id_group ELSE 4 END, group_name',
			array(
				'moderator_group' => 3,
				'min_posts' => -1,
				'admin_group' => 1,
				'is_protected' => 1,
				'hidden_group' => 2,
				'newbie_group' => 4,
			)
		);
		$context['member_groups'] = array(0 => $txt['admin_register_group_none']);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['member_groups'][$row['id_group']] = $row['group_name'];
		$smcFunc['db_free_result']($request);
	}
	else
		$context['member_groups'] = array();
}

// I hereby agree not to be a lazy bum.
function EditAgreement()
{
	global $txt, $boarddir, $context, $modSettings, $smcFunc, $user_info;

	$context['force_gdpr'] = !empty($modSettings['force_gdpr']);

	// By default we look at agreement.txt.
	$context['current_agreement'] = '';

	// Is there more than one to edit?
	$context['editable_agreements'] = array(
		'' => $txt['admin_agreement_default'],
	);

	// Get our languages.
	getLanguages();

	// Try to figure out if we have more agreements.
	foreach ($context['languages'] as $lang)
	{
		if (file_exists($boarddir . '/agreement.' . $lang['filename'] . '.txt'))
		{
			$context['editable_agreements']['.' . $lang['filename']] = $lang['name'];
			// Are we editing this?
			if (isset($_POST['agree_lang']) && $_POST['agree_lang'] == '.' . $lang['filename'])
				$context['current_agreement'] = '.' . $lang['filename'];
		}
	}

	$agreement_lang = empty($context['current_agreement']) ? 'default' : substr($context['current_agreement'], 1);

	$context['agreement'] = file_exists($boarddir . '/agreement' . $context['current_agreement'] . '.txt') ? str_replace("\r", '', file_get_contents($boarddir . '/agreement' . $context['current_agreement'] . '.txt')) : '';

	if (isset($_POST['agreement']) && str_replace("\r", '', $_POST['agreement']) != $context['agreement'])
	{
		checkSession();

		// Off it goes to the agreement file.
		$fp = fopen($boarddir . '/agreement' . $context['current_agreement'] . '.txt', 'w');
		fwrite($fp, str_replace("\r", '', $_POST['agreement']));
		fclose($fp);

		if (!isset($_POST['minor_edit']) || !empty($modSettings['force_gdpr']))
		{
			$agreement_settings['agreement_updated_' . $agreement_lang] = time();

			// Writing it counts as agreeing to it, right?
			$smcFunc['db_insert']('replace',
				'{db_prefix}themes',
				array('id_member' => 'int', 'id_theme' => 'int', 'variable' => 'string', 'value' => 'string'),
				array($user_info['id'], 1, 'agreement_accepted', time()),
				array('id_member', 'id_theme', 'variable')
			);
			logAction('agreement_updated', array('language' => $context['editable_agreements'][$context['current_agreement']]), 'admin');
			logAction('agreement_accepted', array('applicator' => $user_info['id']), 'user');

			updateSettings($agreement_settings);
		}

		$context['agreement'] = str_replace("\r", '', $_POST['agreement']);
	}

	$context['agreement_info'] = sprintf($txt['admin_agreement_info'], empty($modSettings['agreement_updated_' . $agreement_lang]) ? $txt['never'] : timeformat($modSettings['agreement_updated_' . $agreement_lang]));

	$context['agreement'] = $smcFunc['htmlspecialchars']($context['agreement']);
	$context['warning'] = is_writable($boarddir . '/agreement' . $context['current_agreement'] . '.txt') ? '' : $txt['agreement_not_writable'];

	$context['sub_template'] = 'edit_agreement';
	$context['page_title'] = $txt['registration_agreement'];
}

// Set reserved names/words....
function SetReserve()
{
	global $txt, $context, $modSettings;

	// Submitting new reserved words.
	if (!empty($_POST['save_reserved_names']))
	{
		checkSession();

		// Set all the options....
		updateSettings(array(
			'reserveWord' => (isset($_POST['matchword']) ? '1' : '0'),
			'reserveCase' => (isset($_POST['matchcase']) ? '1' : '0'),
			'reserveUser' => (isset($_POST['matchuser']) ? '1' : '0'),
			'reserveName' => (isset($_POST['matchname']) ? '1' : '0'),
			'reserveNames' => str_replace("\r", '', $_POST['reserved'])
		));
	}

	// Get the reserved word options and words.
	$modSettings['reserveNames'] = str_replace('\n', "\n", $modSettings['reserveNames']);
	$context['reserved_words'] = explode("\n", $modSettings['reserveNames']);
	$context['reserved_word_options'] = array();
	$context['reserved_word_options']['match_word'] = $modSettings['reserveWord'] == '1';
	$context['reserved_word_options']['match_case'] = $modSettings['reserveCase'] == '1';
	$context['reserved_word_options']['match_user'] = $modSettings['reserveUser'] == '1';
	$context['reserved_word_options']['match_name'] = $modSettings['reserveName'] == '1';

	// Ready the template......
	$context['sub_template'] = 'edit_reserved_words';
	$context['page_title'] = $txt['admin_reserved_set'];
}

// This function handles registration settings, and provides a few pretty stats too while it's at it.
function ModifyRegistrationSettings($return_config = false)
{
	global $txt, $context, $scripturl, $modSettings, $sourcedir;
	global $language, $boarddir;

	// This is really quite wanting.
	require_once($sourcedir . '/ManageServer.php');

	// Do we have at least default versions of the agreement and privacy policy?
	$agreement = file_exists($boarddir . '/agreement.' . $language . '.txt') || file_exists($boarddir . '/agreement.txt');
	$policy = !empty($modSettings['policy_' . $language]);

	$config_vars = array(
			array('select', 'registration_method', array($txt['setting_registration_standard'], $txt['setting_registration_activate'], $txt['setting_registration_approval'], $txt['setting_registration_disabled'])),
			array('check', 'enableOpenID'),
			array('check', 'notify_new_registration'),
			array('check', 'send_welcomeEmail'),
		'',
			array('check', 'requireAgreement', 'text_label' => $txt['admin_agreement'], 'value' => !empty($modSettings['force_gdpr']) ? 1 : $modSettings['requireAgreement'], 'disabled' => !empty($modSettings['force_gdpr'])),
			array('warning', empty($agreement) ? 'error_no_agreement' : ''),
			array('check', 'requirePolicyAgreement', 'text_label' => $txt['admin_privacy_policy'], 'value' => !empty($modSettings['force_gdpr']) ? 1 : $modSettings['requirePolicyAgreement'], 'disabled' => !empty($modSettings['force_gdpr'])),
			array('warning', empty($policy) ? 'error_no_privacy_policy' : ''),
		'',
			array('int', 'coppaAge', 'subtext' => $txt['setting_coppaAge_desc'], 'onchange' => 'checkCoppa();'),
			array('select', 'coppaType', array($txt['setting_coppaType_reject'], $txt['setting_coppaType_approval']), 'onchange' => 'checkCoppa();'),
			array('large_text', 'coppaPost', 'subtext' => $txt['setting_coppaPost_desc']),
			array('text', 'coppaFax'),
			array('text', 'coppaPhone'),
		'',
			array('check', 'announcements_default', 'disabled' => empty($modSettings['allow_disableAnnounce']) || !empty($modSettings['force_gdpr']), 'value' => !empty($modSettings['force_gdpr']) ? 0 : (empty($modSettings['allow_disableAnnounce']) ? 1 : !empty($modSettings['announcements_default']))),
	);

	if ($return_config)
		return $config_vars;

	// Setup the template
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $txt['registration_center'];

	if (isset($_GET['save']))
	{
		checkSession();

		// Are there some contacts missing?
		if (!empty($_POST['coppaAge']) && !empty($_POST['coppaType']) && empty($_POST['coppaPost']) && empty($_POST['coppaFax']))
			fatal_lang_error('admin_setting_coppa_require_contact');

		// Post needs to take into account line breaks.
		$_POST['coppaPost'] = str_replace("\n", '<br />', empty($_POST['coppaPost']) ? '' : $_POST['coppaPost']);

		// GDPR requires these settings to have certain values
		if (!empty($modSettings['force_gdpr']))
		{
			$_POST['requireAgreement'] = 1;
			$_POST['requirePolicyAgreement'] = 1;
			$_POST['announcements_default'] = 0;
		}
		elseif (empty($modSettings['allow_disableAnnounce']))
			$_POST['announcements_default'] = 1;

		saveDBSettings($config_vars);

		redirectexit('action=admin;area=regcenter;sa=settings');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=regcenter;save;sa=settings';
	$context['settings_title'] = $txt['settings'];

	// Define some javascript for COPPA.
	$context['settings_post_javascript'] = '
		function checkCoppa()
		{
			var coppaDisabled = document.getElementById(\'coppaAge\').value == 0;
			document.getElementById(\'coppaType\').disabled = coppaDisabled;

			var disableContacts = coppaDisabled || document.getElementById(\'coppaType\').options[document.getElementById(\'coppaType\').selectedIndex].value != 1;
			document.getElementById(\'coppaPost\').disabled = disableContacts;
			document.getElementById(\'coppaFax\').disabled = disableContacts;
			document.getElementById(\'coppaPhone\').disabled = disableContacts;
		}
		checkCoppa();';

	// Turn the postal address into something suitable for a textbox.
	$modSettings['coppaPost'] = !empty($modSettings['coppaPost']) ? preg_replace('~<br ?/?' . '>~', "\n", $modSettings['coppaPost']) : '';

	prepareDBSettingContext($config_vars);
}

// Sure, you can sell my personal info for profit (...or not)
function EditPrivacyPolicy()
{
	global $txt, $boarddir, $context, $modSettings, $smcFunc, $user_info;

	$context['force_gdpr'] = !empty($modSettings['force_gdpr']);

	// By default, edit the current language's policy
	$context['current_policy_lang'] = $user_info['language'];

	// We need a policy for every language
	getLanguages();

	foreach ($context['languages'] as $lang)
	{
		$context['editable_policies'][$lang['filename']] = $lang['name'];

		// Are we editing this one?
		if (isset($_POST['policy_lang']) && $_POST['policy_lang'] == $lang['filename'])
			$context['current_policy_lang'] = $lang['filename'];
	}

	$context['policy'] = empty($modSettings['policy_' . $context['current_policy_lang']]) ? '' : $modSettings['policy_' . $context['current_policy_lang']];

	if (isset($_POST['policy']))
	{
		checkSession();

		// Make sure there are no creepy-crawlies in it
		$policy_text = $smcFunc['htmlspecialchars'](str_replace("\r", '', $_POST['policy']));

		$policy_settings = array(
			'policy_' . $context['current_policy_lang'] => $policy_text,
		);

		if ($policy_text != $context['policy'] && (!isset($_POST['minor_edit']) || !empty($modSettings['force_gdpr'])))
		{
			$policy_settings['policy_updated_' . $context['current_policy_lang']] = time();

			// Writing it counts as agreeing to it, right?
			$smcFunc['db_insert']('replace',
				'{db_prefix}themes',
				array('id_member' => 'int', 'id_theme' => 'int', 'variable' => 'string', 'value' => 'string'),
				array($user_info['id'], 1, 'policy_accepted', time()),
				array('id_member', 'id_theme', 'variable')
			);
			logAction('policy_updated', array('language' => $context['editable_policies'][$context['current_policy_lang']]), 'admin');
			logAction('policy_accepted', array('applicator' => $user_info['id']), 'user');
		}

		updateSettings($policy_settings);

		$context['policy'] = $policy_text;
	}

	$context['policy_info'] = sprintf($txt['admin_agreement_info'], empty($modSettings['policy_updated_' . $context['current_policy_lang']]) ? $txt['never'] : timeformat($modSettings['policy_updated_' . $context['current_policy_lang']]));

	$context['sub_template'] = 'edit_privacy_policy';
	$context['page_title'] = $txt['privacy_policy'];
}

?>