<?php
/**
 * Copyright 2012 OneAll, LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 */

if (!defined('SMF'))
{
	die('You are not allowed to access this file directly');
}

/**
 * Administration Area
 */
function oneall_social_login_config ()
{
	$sa = !empty($_REQUEST['sa']) ? $_REQUEST['sa'] : '';

	switch ($sa)
	{
		case 'autodetect';
			oneall_social_login_autodetect_api_connection();
			break;

		case 'verify';
			oneall_social_login_verify_api_settings();
			break;

		case 'save':
			oneall_social_login_config_save();
			break;

		default:
			oneall_social_login_config_show();
			break;
	}
}

/**
 * Autodetect API Connection Handler
 */
function oneall_social_login_autodetect_api_connection ()
{
	global $boarddir, $sourcedir;

	// Only for administrators.
	isAllowedTo('admin_forum');

	// Security Check.
	checkSession('post');

	// Include the OneAll SDK.
	require_once($sourcedir . '/OneallSocialLogin.sdk.php');

	// Initialize.
	$oasl_api_handler = null;
	$oasl_api_port = null;

	// Check CURL HTTPS - Port 443.
	if (oneall_social_login_check_curl(true) === true)
	{
		$oasl_api_handler = 'curl';
		$oasl_api_port = 443;
	}
	// Check CURL HTTP - Port 80.
	elseif (oneall_social_login_check_curl(false) === true)
	{
		$oasl_api_handler = 'curl';
		$oasl_api_port = 80;
	}
	// Check FSOCKOPEN HTTPS - Port 443.
	elseif (oneall_social_login_check_fsockopen(true) == true)
	{
		$oasl_api_handler = 'fsockopen';
		$oasl_api_port = 443;
	}
	// Check FSOCKOPEN HTTP - Port 80.
	elseif (oneall_social_login_check_fsockopen(false) == true)
	{
		$oasl_api_handler = 'fsockopen';
		$oasl_api_port = 80;
	}

	// Update Settings.
	if (!empty($oasl_api_handler) && !empty($oasl_api_port))
	{
		//Update
		$values = array();
		$values['oasl_api_handler'] = $oasl_api_handler;
		$values['oasl_api_port'] = $oasl_api_port;
		updateSettings($values);

		//Set status
		$status = 'success';
	}
	else
	{
		// Set status.
		$status = 'error';
	}

	// Redirect to the administration area.
	redirectexit('action=admin;area=oasl;sa=settings;oasl_action=autodetect;oasl_status=' . $status . '#oasl_api_connection_handler');
}


/**
 * Verify API Settings
 */
function oneall_social_login_verify_api_settings ()
{
	global $boarddir, $sourcedir;

	// Only for administrators
	isAllowedTo('admin_forum');

	// Security Check.
	checkSession('post');

	// Include the OneAll SDK.
	require_once($sourcedir . '/OneallSocialLogin.sdk.php');

	//Default
	$status = null;

	// Read settings.
	$oasl_api_subdomain = !empty($_POST['oasl_api_subdomain']) ? trim(strtolower($_POST['oasl_api_subdomain'])) : '';
	$oasl_api_key = !empty($_POST['oasl_api_key']) ? trim($_POST['oasl_api_key']) : '';
	$oasl_api_secret = !empty($_POST['oasl_api_secret']) ? trim($_POST['oasl_api_secret']) : '';

	// Full domain entered.
	if (preg_match("/([a-z0-9\-]+)\.api\.oneall\.com/i", $oasl_api_subdomain, $matches))
		$oasl_api_subdomain = $matches[1];

	// Update Settings.
	$values = array();
	$values['oasl_api_subdomain'] = $oasl_api_subdomain;
	$values['oasl_api_key'] = $oasl_api_key;
	$values['oasl_api_secret'] = $oasl_api_secret;
	updateSettings($values);

	//Check if all fields have been filled out
	if (empty($oasl_api_key) || empty($oasl_api_secret) || empty($oasl_api_subdomain))
		$status = 'error_not_all_fields_filled_out';
	else
	{
		// Read settings.
		$oasl_api_connection_handler = (!empty($_POST['oasl_api_handler']) && $_POST['oasl_api_handler'] == 'fsockopen') ? 'fsockopen' : 'curl';
		$oasl_api_connection_use_https = (!empty($_POST['oasl_api_port']) && $_POST['oasl_api_port'] == 80) ? false : true;

		// Check connection handler.
		if ($oasl_api_connection_handler == 'fsockopen')
			$status = !oneall_social_login_check_fsockopen($oasl_api_connection_use_https) ? 'error_selected_handler_faulty' : '';
		else
			$status = !oneall_social_login_check_curl($oasl_api_connection_use_https) ? 'error_selected_handler_faulty' : '';


		//If we have a status then we have a problem
		if (empty($status))
		{
			//Check subdomain format
			if (!preg_match("/^[a-z0-9\-]+$/i", $oasl_api_subdomain))
				$status = 'error_subdomain_wrong_syntax';
			else
			{
				//Domain
				$oasl_api_domain = $oasl_api_subdomain . '.api.oneall.com';

				//Connection to
				$api_resource_url = ($oasl_api_connection_use_https ? 'https' : 'http') . '://' . $oasl_api_domain . '/tools/ping.json';

				//Get connection details
				$result = oneall_social_login_do_api_request($oasl_api_connection_handler, $api_resource_url, array('api_key' => $oasl_api_key, 'api_secret' => $oasl_api_secret), 15);
				$result_tag = (is_object($result) && property_exists($result, 'http_code') && property_exists($result, 'http_data')) ? $result->http_code : 'error';


				//Parse result
				switch ($result_tag)
				{
					//Success
					case 200:
					//Set status
						$status = 'success';
						break;

					//Authentication Error
					case 401:
						$status = 'error_authentication_credentials_wrong';
						break;

					//Wrong Subdomain
					case 404:
						$status = 'error_subdomain_wrong';
						break;

					//Other error
					default:
						$status = 'error_communication';
						break;
				}
			}
		}
	}

	// Redirect to the administration area.
	redirectexit('action=admin;area=oasl;sa=settings;oasl_action=verify;oasl_status=' . $status . '#oasl_api_settings');
}


/**
 * Show administration area settings
 */
function oneall_social_login_config_show ()
{
	global $txt, $context, $modSettings;

	// Only for administrators
	isAllowedTo('admin_forum');

	// Load language file.
	loadLanguage('OneallSocialLogin');

	// Load template.
	loadtemplate('OneallSocialLogin', array('OneallSocialLogin'));

	// Set sub template.
	$context['sub_template'] = 'oneall_social_login_config';

	// Set page title.
	$context['page_title'] = $txt['oasl_title'];

	// Set page headers.
	$context['html_headers'] .= '
		<script type="text/javascript">
			function oasl_init ()
			{
				var oasl_button, oasl_form, oasl_sa;

				oasl_sa = document.getElementById(\'oasl_sa\');
				oasl_form = document.getElementById(\'creator\');

				oasl_button = document.getElementById(\'oasl_autodetect_button\');
				oasl_button.onclick = function () {
					oasl_sa.value = \'autodetect\';
					oasl_form.submit();
				};

				oasl_button = document.getElementById(\'oasl_verify_button\');
				oasl_button.onclick = function () {
					oasl_sa.value = \'verify\';
					oasl_form.submit();
				};
			}
			window.setTimeout(\'oasl_init();\', 1);
		</script>';

	//Add page.
	$context[$context['admin_menu_name']]['tab_data'] = array('title' => 'OneAll Social Login', 'description' => '', 'tabs' => array('settings' => array('description' => $txt['oasl_settings_descr'])));

	// Setup api connection values.
	$modSettings['oasl_api_handler'] = (!empty($modSettings['oasl_api_handler']) && $modSettings['oasl_api_handler'] == 'fsockopen') ? 'fsockopen' : 'curl';
	$modSettings['oasl_api_port'] = (!empty($modSettings['oasl_api_port']) && $modSettings['oasl_api_port'] == 80) ? 80 : 443;

	foreach (array ('action', 'status', 'api_subdomain', 'api_key', 'api_secret', 'settings_login_caption', 'settings_registration_caption', 'settings_profile_caption', 'settings_profile_desc') AS $field)
		if (!isset ($modSettings['oasl_'.$field]))
			$modSettings['oasl_'.$field] = '';

	// Setup available providers
	$available_providers = array();
	if (!empty($modSettings['oasl_providers']))
	{
		$providers = explode(',', trim($modSettings['oasl_providers']));
		foreach ($providers AS $provider)
			if (strlen(trim($provider)) > 0)
				$available_providers[] = strtolower($provider);
	}
	$modSettings['oasl_providers'] = $available_providers;

	// Setup enabled providers.
	$enabled_providers = array();
	if (!empty($modSettings['oasl_enabled_providers']))
	{
		$providers = explode(',', trim($modSettings['oasl_enabled_providers']));
		foreach ($providers AS $provider)
			if (in_array($provider, $available_providers))
				$enabled_providers[] = strtolower($provider);
	}
	$modSettings['oasl_enabled_providers'] = $enabled_providers;

	// Setup action triggers
	if (!empty($_REQUEST['oasl_action']) AND !empty($_REQUEST['oasl_status']) AND in_array($_REQUEST['oasl_action'], array('autodetect', 'verify')))
	{
		$modSettings['oasl_action'] = $_REQUEST['oasl_action'];
		$modSettings['oasl_status'] = $_REQUEST['oasl_status'];
	}
}


/**
 * Save administration area settings
 */
function oneall_social_login_config_save ()
{
	// Only for administrators
	isAllowedTo('admin_forum');

	// Security Check.
	checkSession('post');

	// API Connection Handler.
	$oasl_api_handler = (!empty($_POST['oasl_api_handler']) && $_POST['oasl_api_handler'] == 'fsockopen') ? 'fsockopen' : 'curl';
	$oasl_api_port = (!empty($_POST['oasl_api_port']) && $_POST['oasl_api_port'] == 80) ? 80 : 443;

	// API Settings.
	$oasl_api_key = !empty($_POST['oasl_api_key']) ? trim($_POST['oasl_api_key']) : '';
	$oasl_api_secret =  !empty($_POST['oasl_api_key']) ? trim($_POST['oasl_api_secret']) : '';
	$oasl_api_subdomain =  !empty($_POST['oasl_api_subdomain']) ? strtolower(trim($_POST['oasl_api_subdomain'])) : '';

	// Additional Settings.
	$oasl_settings_login_caption =  !empty($_POST['oasl_settings_login_caption']) ? trim($_POST['oasl_settings_login_caption']) : '';
	$oasl_settings_registration_caption =  !empty($_POST['oasl_settings_registration_caption']) ? trim($_POST['oasl_settings_registration_caption']) : '';
	$oasl_settings_profile_caption =  !empty($_POST['oasl_settings_profile_caption']) ? trim($_POST['oasl_settings_profile_caption']) : '';
	$oasl_settings_profile_desc =  !empty($_POST['oasl_settings_profile_desc']) ? trim($_POST['oasl_settings_profile_desc']) : '';
	$oasl_settings_link_accounts = !empty($_POST['oasl_settings_link_accounts']) ? 1 : 0;
	$oasl_settings_use_avatars = !empty($_POST['oasl_settings_use_avatars']) ? 1 : 0;

	// Full domain entered.
	if (preg_match("/([a-z0-9\-]+)\.api\.oneall\.com/i", $oasl_api_subdomain, $matches))
		$oasl_api_subdomain = $matches[1];

	// Enabled Providers.
	$oasl_enabled_providers = array();
	if (isset($_POST['oasl_enabled_providers']) && is_array($_POST['oasl_enabled_providers']))
		foreach ($_POST['oasl_enabled_providers'] AS $provider)
			$oasl_enabled_providers[] = trim(strtolower($provider));

	// API Settings.
	$values = array();
	$values['oasl_api_handler'] = $oasl_api_handler;
	$values['oasl_api_port'] = $oasl_api_port;
	$values['oasl_api_subdomain'] = $oasl_api_subdomain;
	$values['oasl_api_key'] = $oasl_api_key;
	$values['oasl_api_secret'] = $oasl_api_secret;

	// Additional Settings.
	$values['oasl_settings_profile_caption'] = $oasl_settings_profile_caption;
	$values['oasl_settings_profile_desc'] = $oasl_settings_profile_desc;
	$values['oasl_settings_login_caption'] = $oasl_settings_login_caption;
	$values['oasl_settings_registration_caption'] = $oasl_settings_registration_caption;
	$values['oasl_settings_link_accounts'] = $oasl_settings_link_accounts;
	$values['oasl_settings_use_avatars'] = $oasl_settings_use_avatars;

	// Enabled Providers.
	$values['oasl_enabled_providers'] = implode(',', $oasl_enabled_providers);

	// Update Settings.
	updateSettings($values);

	// Redirect to the administration area.
	redirectexit('action=admin;area=oasl;sa=settings;oasl_action=saved');
}

?>