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


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// CALLBACK HANDLER
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Social Link Callback Handler
 */
function oneall_social_login_link_callback ()
{
	// Nothing to do if these haven't been set.
	if (!empty ($_POST ['connection_token']) AND !empty ($_POST ['oa_action']) && $_POST ['oa_action'] == 'social_link')
	{
		// Global vars.
		global $boarddir, $sourcedir, $user_settings, $context, $modSettings, $smcFunc;

		// Include the OneAll Toolbox.
		require_once($sourcedir . '/OneallSocialLogin.sdk.php');

		// Some security checks.
		if (!empty ($_REQUEST ['oasl_uid']) && !empty ($context ['user'] ['is_logged']) && !empty ($context ['user'] ['id']) && $context ['user'] ['id'] == $_REQUEST ['oasl_uid'])
		{
			// The current user
			$id_member = $context ['user'] ['id'];

			// API Connection Handler.
			$oasl_api_handler = (!empty ($modSettings ['oasl_api_handler']) && $modSettings ['oasl_api_handler'] == 'fsockopen') ? 'fsockopen' : 'curl';
			$oasl_api_port = (!empty ($modSettings ['oasl_api_port']) && $modSettings ['oasl_api_port'] == 80) ? 80 : 443;

			// API Settings.
			$oasl_api_key = !empty ($modSettings ['oasl_api_key']) ? $modSettings ['oasl_api_key'] : '';
			$oasl_api_secret = !empty ($modSettings ['oasl_api_key']) ? $modSettings ['oasl_api_secret'] : '';
			$oasl_api_subdomain = !empty ($modSettings ['oasl_api_subdomain']) ? $modSettings ['oasl_api_subdomain'] : '';

			// Resource.
			$oasl_api_resource_url = ($oasl_api_port == 80 ? 'http' : 'https') . '://' . $oasl_api_subdomain . '.api.oneall.com/connections/' . $_POST ['connection_token'] . '.json';

			// Get the connection details.
			$result = oneall_social_login_do_api_request ($oasl_api_handler, $oasl_api_resource_url, array ('api_key' => $oasl_api_key, 'api_secret' => $oasl_api_secret), 15);

			// Check API result.
			if (is_object ($result) && property_exists ($result, 'http_code') && $result->http_code == 200 && property_exists ($result, 'http_data'))
			{
				// Decode the Social Profile Data.
				$social_data = json_decode ($result->http_data);
				if (is_object ($social_data))
				{
					// Extract the social network profile data data.
					$data = $social_data->response->result->data;

					// Check for plugin status.
					if (is_object ($data) && property_exists ($data, 'plugin') && $data->plugin->key == 'social_link' && $data->plugin->data->status == 'success')
					{
						// Identity.
						$identity = $data->user->identity;
						$identity_token = $identity->identity_token;

						// User.
						$user = $data->user;
						$user_token = $user->user_token;

						// Default status.
						$status_flag = null;
						$status_action = null;

						// Get the id of the linked user - Can be empty.
						$id_member_for_user_token = oneall_social_login_get_id_member_for_user_token ($user_token);

						// Link identity.
						if ($data->plugin->data->action == 'link_identity')
						{
							// The user already has a user_token.
							if (is_numeric ($id_member_for_user_token))
							{
								// The user_token is already connected to this user.
								if ($id_member_for_user_token == $id_member)
								{
									//Update the connection.
									if (oneall_social_login_link_tokens_to_id_member ($id_member, $user_token, $identity_token) === true)
									{
										$status_flag = 'success';
										$status_action = 'linked';
									}
								}
								// The user_token is connected to a different user.
								else
								{
									$status_flag = 'error';
									$status_action = 'linked_to_another_user';
								}
							}
							// The user does not have a user_token yet.
							else
							{
								// Link the user_token_token to this user.
								if (oneall_social_login_link_tokens_to_id_member ($id_member, $user_token, $identity_token) === true)
								{
									$status_flag = 'success';
									$status_action = 'linked';
								}
							}
						}
						// UnLink identity.
						elseif ($data->plugin->data->action == 'unlink_identity')
						{
							// The user already has a user_token
							if (is_numeric ($id_member_for_user_token))
							{
								//Was connected to this user
								if ($id_member_for_user_token == $id_member)
								{
									// Unlink the user_token from this user.
									if (oneall_social_login_unlink_identity_token ($identity_token) === true)
									{
										$status_flag = 'success';
										$status_action = 'unlinked';
									}
								}
								// The given user_token it connected to a different user.
								else
								{
									$status_flag = 'error';
									$status_action = 'linked_to_another_user';
								}
							}
							// The user does not have a user_token yet.
							else
							{
								$status_flag = 'success';
								$status_action = 'unlinked';
							}
						}
						//Redirect to account
						redirectexit ('action=profile;area=account;oasl_status=' . $status_flag . ';oasl_action=' . $status_action . '#oasl_social_link');
					}
				}
			}
		}

		// The user has probably hit the back button in the browser.
		redirectexit ('action=profile;area=account#oasl_social_link');
	}

	// No POST data found.
	redirectexit ();
}


/**
 * Social Login Callback Handler
 */
function oneall_social_login_login_callback ()
{
	// Nothing to do if these haven't been set.
	if (!empty ($_POST ['connection_token']) && !empty ($_POST ['oa_action']) && $_POST ['oa_action'] == 'social_login')
	{
		// Global vars.
		global $boarddir, $sourcedir, $user_settings, $context, $modSettings, $smcFunc, $db_character_set;

		// Include the OneAll Toolbox.
		require_once($sourcedir . '/OneallSocialLogin.sdk.php');

		//Source
		$oasl_source = ! empty ($_REQUEST ['oasl_source']) ? $_REQUEST ['oasl_source'] : null;

		// API Connection Handler.
		$oasl_api_handler = (!empty ($modSettings ['oasl_api_handler']) && $modSettings ['oasl_api_handler'] == 'fsockopen') ? 'fsockopen' : 'curl';
		$oasl_api_port = (!empty ($modSettings ['oasl_api_port']) && $modSettings ['oasl_api_port'] == 80) ? 80 : 443;

		// API Settings.
		$oasl_api_key = !empty ($modSettings ['oasl_api_key']) ? $modSettings ['oasl_api_key'] : '';
		$oasl_api_secret = !empty ($modSettings ['oasl_api_key']) ? $modSettings ['oasl_api_secret'] : '';
		$oasl_api_subdomain = !empty ($modSettings ['oasl_api_subdomain']) ? $modSettings ['oasl_api_subdomain'] : '';

		// Resource.
		$oasl_api_resource_url = ($oasl_api_port == 80 ? 'http' : 'https') . '://' . $oasl_api_subdomain . '.api.oneall.com/connections/' . $_POST ['connection_token'] . '.json';

		// Get the connection details.
		$result = oneall_social_login_do_api_request ($oasl_api_handler, $oasl_api_resource_url, array ('api_key' => $oasl_api_key, 'api_secret' => $oasl_api_secret), 15);

		//Extract Data
		if (is_array (($data = oneall_social_login_extract_social_network_profile ($result))))
		{
			// Save the social network data in a session.
			$_SESSION ['oasl_session_open'] = 1;
			$_SESSION ['oasl_session_time'] = time();
			$_SESSION ['oasl_social_data'] = serialize($data);

			// Get the user identifier for a given token.
			$id_member_tmp = oneall_social_login_get_id_member_for_user_token ($data['user_token']);

			// This user already exists.
			if (is_numeric ($id_member_tmp))
			{
				$id_member = $id_member_tmp;
			}
			// This is a new user.
			else
			{
				// Account linking is enabled.
				if (!empty ($modSettings ['oasl_settings_link_accounts']))
				{
					// Account linking only works if the email address has been verified.
					if (!empty ($data['user_email']) && $data['user_email_is_verified'] === true)
					{
						// Try to read the existing user account
						if (($id_member_tmp = oneall_social_login_get_id_member_for_email_address ($data['user_email'])) !== false)
						{
							// Tie the user_token to the newly created member.
							if (oneall_social_login_link_tokens_to_id_member ($id_member_tmp, $data['user_token'], $data['identity_token']) === true)
							{
								$id_member = $id_member_tmp;
							}
						}
					}
				}
			}

			// The account already exists.
			if (!empty ($id_member))
			{
				// What is being done?
				$action = 'login';
			}
			// Create a user new account.
			else
			{
				// Prevent registration through login form.
				if ($oasl_source == 'login')
				{
					redirectexit ('action=login;oasl_err=user_does_not_exist');
				}

				// What is being done?
				$action = 'register';

				// Either the social network provides no email address at all, or it's a duplicate and we don't have account linking enabled.
				if (empty ($data['user_email']) || oneall_social_login_get_id_member_for_email_address ($data ['user_email']) !== false)
				{
					// Create a bogus email address.
					if (empty ($modSettings ['oasl_settings_ask_for_email']))
					{
						$data ['user_email'] = oneall_social_login_create_rand_email_address ();
					}
					// Ask the user to enter his real email address.
					else
					{
						redirectexit ('action=oasl_registration');
					}
				}

				// Create a new account.
				$id_member = oneall_social_login_create_user ($data);
			}

			// Login.
			if (!empty ($id_member) AND oneall_social_login_login_user ($id_member))
			{
				if ($action == 'login')
				{
					redirectexit ();
				}
				else
				{
					redirectexit ('action=profile');
				}
			}
		}
	}

	// Error
	redirectexit ();
}


/**
 * Social Login Callback Switch
 */
function oneall_social_login_callback ()
{
	if (!empty ($_POST ['oa_action']) && !empty ($_POST ['connection_token']))
	{
		if ($_POST ['oa_action'] == 'social_login')
		{
			oneall_social_login_login_callback ();
			exit();
		}
		elseif ($_POST ['oa_action'] == 'social_link')
		{
			oneall_social_login_link_callback ();
			exit();
		}
	}

	// Page has probably been opened directly in the browser.
	redirectexit ();
}


/**
 * Form to gather additional data
 */
function oneall_social_login_registration ()
{
	// Makw sure we have a social network profile in the session.
	if (isset($_SESSION) && !empty($_SESSION ['oasl_session_open']) && !empty($_SESSION ['oasl_social_data']))
	{
		// Setup global forum vars.
		global $txt, $boarddir, $sourcedir, $user_settings, $context, $modSettings, $smcFunc;

		// Include the OneAll SDK.
		require_once($sourcedir . '/OneallSocialLogin.sdk.php');

		// User Action
		$sa = !empty($_REQUEST['sa']) ? $_REQUEST['sa'] : '';

		// Restore data.
		$data = @unserialize($_SESSION ['oasl_social_data']);

		// Check format.
		if (is_array($data))
		{
			// Load language file.
			loadLanguage('OneallSocialLogin');

			// Load template.
			loadtemplate('OneallSocialLogin.registration');

			// Set sub template.
			$context['sub_template'] = 'oneall_social_login_registration';

			// Set page title.
			$context['page_title'] = $txt['oasl_register_title'];

			//Provider name for template
			$modSettings['oasl_provider'] = $data['identity_provider'];

			// Formular submitted.
			if ($sa == 'confirm')
			{
				// Security Check.
				checkSession('post');

				// Read form values.
				$oasl_user_email = !empty($_POST['email_address']) ? trim(strtolower($_POST['email_address'])) : '';
				$oasl_user_email_public = !empty($_POST['public_email_address']) ? 1 : 0;

				//Restore for form.
				$modSettings['email_address'] = !empty($_POST['email_address']) ? $_POST['email_address'] : '';
				$modSettings['public_email_address'] = !empty($_POST['public_email_address']) ? 1 : 0;

				//Error Container
				$context['oasl_registration_errors'] = array();

				// The email address is empty.
				if (strlen ($oasl_user_email) == 0)
				{
					$context['oasl_registration_errors'][] = $txt['oasl_register_email_empty'];
				}
				// The email address is already taken.
				elseif (oneall_social_login_get_id_member_for_email_address ($oasl_user_email) !== false)
				{
					$context['oasl_registration_errors'][] = $txt['oasl_register_email_exists'];
				}
				// The email address is invalid.
				elseif ( ! preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $oasl_user_email))
				{
					$context['oasl_registration_errors'][] = $txt['oasl_register_email_invalid'];
				}
				// The email address is not empty and unique.
				else
				{
					//Use email to create account
					$data['user_email'] = $oasl_user_email;
					$data['hide_email'] = empty ($oasl_user_email_public) ? 1 : 0;

					// Create a new account.
					$id_member = oneall_social_login_create_user ($data);
					if (!empty ($id_member) AND oneall_social_login_login_user ($id_member))
					{
						// Remove plugin session data
						oneall_social_login_clear_session();

						//Redirect to forum index
						redirectexit ();
					}
				}
			}
			//First call of the page
			else
			{
				//Restore for form.
				$modSettings['email_address'] = !empty($data['user_email']) ? $data['user_email'] : '';
				$modSettings['public_email_address'] = 1;
			}
		}
	}
	//No open Social Login session
	else
	{
		// Remove plugin session data
		oneall_social_login_clear_session();

		//Redirect to forum index
		redirectexit ();
	}
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ADMINISTRATION
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
	{
		$oasl_api_subdomain = $matches[1];
	}

	// Update Settings.
	$values = array();
	$values['oasl_api_subdomain'] = $oasl_api_subdomain;
	$values['oasl_api_key'] = $oasl_api_key;
	$values['oasl_api_secret'] = $oasl_api_secret;
	updateSettings($values);

	//Check if all fields have been filled out
	if (empty($oasl_api_key) || empty($oasl_api_secret) || empty($oasl_api_subdomain))
	{
		$status = 'error_not_all_fields_filled_out';
	}
	else
	{
		// Read settings.
		$oasl_api_connection_handler = (!empty($_POST['oasl_api_handler']) && $_POST['oasl_api_handler'] == 'fsockopen') ? 'fsockopen' : 'curl';
		$oasl_api_connection_use_https = (!empty($_POST['oasl_api_port']) && $_POST['oasl_api_port'] == 80) ? false : true;

		// Check connection handler.
		if ($oasl_api_connection_handler == 'fsockopen')
		{
			$status = !oneall_social_login_check_fsockopen($oasl_api_connection_use_https) ? 'error_selected_handler_faulty' : '';
		}
		else
		{
			$status = !oneall_social_login_check_curl($oasl_api_connection_use_https) ? 'error_selected_handler_faulty' : '';
		}

		//If we have a status then we have a problem
		if (empty($status))
		{
			//Check subdomain format
			if (!preg_match("/^[a-z0-9\-]+$/i", $oasl_api_subdomain))
			{
				$status = 'error_subdomain_wrong_syntax';
			}
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
	loadtemplate('OneallSocialLogin.admin', array('OneallSocialLogin.admin'));

	// Set sub template.
	$context['sub_template'] = 'oneall_social_login_config';

	// Set page title.
	$context['page_title'] = $txt['oasl_title'];

	// Set page headers.
	$context['html_headers'] .= '
		<script type="text/javascript">
			function oasl_config_init ()
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
		</script>';

	//Add page.
	$context[$context['admin_menu_name']]['tab_data'] = array('title' => 'OneAll Social Login', 'description' => '', 'tabs' => array('settings' => array('description' => $txt['oasl_settings_descr'])));

	// Setup api connection values.
	$modSettings['oasl_api_handler'] = (!empty($modSettings['oasl_api_handler']) && $modSettings['oasl_api_handler'] == 'fsockopen') ? 'fsockopen' : 'curl';
	$modSettings['oasl_api_port'] = (!empty($modSettings['oasl_api_port']) && $modSettings['oasl_api_port'] == 80) ? 80 : 443;

	foreach (array ('action', 'status', 'api_subdomain', 'api_key', 'api_secret', 'settings_login_caption', 'settings_ask_for_email', 'settings_registration_caption', 'settings_profile_caption', 'settings_profile_desc') AS $field)
	{
		if (!isset ($modSettings['oasl_'.$field]))
		{
			$modSettings['oasl_'.$field] = '';
		}
	}

	// Setup available providers
	$available_providers = array();
	if (!empty($modSettings['oasl_providers']))
	{
		$providers = explode(',', trim($modSettings['oasl_providers']));
		foreach ($providers AS $provider)
		{
			if (strlen(trim($provider)) > 0)
			{
				$available_providers[] = strtolower($provider);
			}
		}
	}
	$modSettings['oasl_providers'] = $available_providers;

	// Setup enabled providers.
	$enabled_providers = array();
	if (!empty($modSettings['oasl_enabled_providers']))
	{
		$providers = explode(',', trim($modSettings['oasl_enabled_providers']));
		foreach ($providers AS $provider)
		{
			if (in_array($provider, $available_providers))
			{
				$enabled_providers[] = strtolower($provider);
			}
		}
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
	$oasl_settings_ask_for_email = !empty($_POST['oasl_settings_ask_for_email']) ? 1 : 0;

	// Full domain entered.
	if (preg_match("/([a-z0-9\-]+)\.api\.oneall\.com/i", $oasl_api_subdomain, $matches))
	{
		$oasl_api_subdomain = $matches[1];
	}

	// Enabled Providers.
	$oasl_enabled_providers = array();
	if (isset($_POST['oasl_enabled_providers']) && is_array($_POST['oasl_enabled_providers']))
	{
		foreach ($_POST['oasl_enabled_providers'] AS $provider)
		{
			$oasl_enabled_providers[] = trim(strtolower($provider));
		}
	}

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
	$values['oasl_settings_ask_for_email'] = $oasl_settings_ask_for_email;

	// Enabled Providers.
	$values['oasl_enabled_providers'] = implode(',', $oasl_enabled_providers);

	// Update Settings.
	updateSettings($values);

	// Redirect to the administration area.
	redirectexit('action=admin;area=oasl;sa=settings;oasl_action=saved');
}

?>