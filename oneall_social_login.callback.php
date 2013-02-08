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

// Allow Guests
$ssi_guest_access = true;
// Security Check.
if (file_exists (dirname (__FILE__) . '/SSI.php') && !defined ('SMF'))
{
	require_once(dirname (__FILE__) . '/SSI.php');
}
elseif (!defined ('SMF'))
{
	die ('<strong>Unable to execute:</strong> Please make sure that you have installed Social Login correctly.');
}

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
	}

	// Error
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
 * Launch callback handler.
 */
if (!empty ($_POST ['oa_action']) && !empty ($_POST ['connection_token']))
{
	if ($_POST ['oa_action'] == 'social_login')
	{
		oneall_social_login_login_callback ();
		exit ();
	}
	elseif ($_POST ['oa_action'] == 'social_link')
	{
		oneall_social_login_link_callback ();
		exit ();
	}
}

// The url has likely been called directly
redirectexit ();
?>