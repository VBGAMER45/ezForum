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

$ssi_guest_access = true;
// Security Check.
if (file_exists (dirname (__FILE__) . '/SSI.php') && !defined ('SMF'))
	require_once(dirname (__FILE__) . '/SSI.php');
elseif (!defined ('SMF'))
	die ('<strong>Unable to execute:</strong> Please make sure that you have installed Social Login correctly.');


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
					//Extract data
					$data = $social_data->response->result->data;

					// Check for plugin status.
					if (is_object ($data) && property_exists ($data, 'plugin') && $data->plugin->key == 'social_link' && $data->plugin->data->status == 'success')
					{
						$identity = $data->user->identity;
						$identity_token = $identity->identity_token;

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
				$identity = $social_data->response->result->data->user->identity;
				$identity_token = $identity->identity_token;

				$user = $social_data->response->result->data->user;
				$user_token = $user->user_token;

				// Parse Social Profile Data.
				$user_first_name = !empty ($identity->name->givenName) ? $identity->name->givenName : '';
				$user_last_name = !empty ($identity->name->familyName) ? $identity->name->familyName : '';
				$user_location = !empty ($identity->currentLocation) ? $identity->currentLocation : '';
				$user_constructed_name = trim ($user_first_name . ' ' . $user_last_name);
				$user_picture = !empty ($identity->pictureUrl) ? $identity->pictureUrl : '';
				$user_thumbnail = !empty ($identity->thumbnailUrl) ? $identity->thumbnailUrl : '';
				$user_about_me = !empty ($identity->aboutMe) ? $identity->aboutMe : '';

				// Fullname.
				if (!empty ($identity->name->formatted))
					$user_full_name = $identity->name->formatted;
				elseif (!empty ($identity->name->displayName))
					$user_full_name = $identity->name->displayName;
				else
					$user_full_name = $user_constructed_name;

				// Preferred Username.
				if (!empty ($identity->preferredUsername))
					$user_login = $identity->preferredUsername;
				elseif (!empty ($identity->displayName))
					$user_login = $identity->displayName;
				else
					$user_login = $user_full_name;

				// Email Address.
				$user_email = '';
				if (property_exists ($identity, 'emails') && is_array ($identity->emails))
				{
					$user_email_is_verified = false;
					while ($user_email_is_verified !== true && (list(, $email) = each ($identity->emails)))
					{
						$user_email = $email->value;
						$user_email_is_verified = ($email->is_verified == '1');
					}
				}

				// Website/Homepage.
				if (!empty ($identity->profileUrl))
					$user_website = $identity->profileUrl;
				elseif (!empty ($identity->urls [0]->value))
					$user_website = $identity->urls [0]->value;
				else
					$user_website = '';

				// Gender
				$user_gender = 0;
				if (!empty ($identity->gender))
				{
					switch ($identity->gender)
					{
						case 'male':
							$user_gender = 1;
							break;

						case 'female':
							$user_gender = 2;
							break;
					}
				}

				// Get the user identifier for a given token.
				$id_member_tmp = oneall_social_login_get_id_member_for_user_token ($user_token);

				// This user already exists.
				if (is_numeric ($id_member_tmp))
					$id_member = $id_member_tmp;
				// This is a new user.
				else
				{
					// Account linking is enabled.
					if (!empty ($modSettings ['oasl_settings_link_accounts']))
					{
						// Account linking only works if the email address has been verified
						if (!empty ($user_email) && $user_email_is_verified === true)
						{
							// Try to read the existing user account
							if (($id_member_tmp = oneall_social_login_get_id_member_for_email_address ($user_email)) !== false)
							{
								// Tie the user_token to the newly created member.
								if (oneall_social_login_link_tokens_to_id_member ($id_member_tmp, $user_token, $identity_token) === true)
								{
									$id_member = $id_member_tmp;
								}
							}
						}
					}
				}

				// Login the user.
				if (!empty ($id_member))
				{
					// What is being done?
					$action = 'login';
				}
				//Create a new account.
				else
				{
					// What is being done?
					$action = 'register';

					// Registration functions.
					require_once($sourcedir . '/Subs-Members.php');

					// Build User fields.
					$regOptions = array ();
					$regOptions ['password'] = substr (md5 (mt_rand ()), 0, 8);
					$regOptions ['password_check'] = $regOptions ['password'];
					$regOptions ['auth_method'] = 'password';
					$regOptions ['interface'] = 'guest';

					// Email address is provided.
					if (!empty ($user_email))
					{
						$regOptions ['email'] = $user_email;
						$regOptions ['hide_email'] = 0;
					}
					// Email address is not provided.
					else
					{
						$regOptions ['email'] = oneall_social_login_create_rand_email_address ();
						$regOptions ['hide_email'] = 1;
					}

					// We need a unique email address.
					while (oneall_social_login_get_id_member_for_email_address ($regOptions ['email']) !== false)
					{
						$regOptions ['email'] = oneall_social_login_create_rand_email_address ();
						$regOptions ['hide_email'] = 1;
					}

					// Additional user fields.
					$regOptions ['extra_register_vars'] ['website_url'] = $user_website;
					$regOptions ['extra_register_vars'] ['gender'] = $user_gender;
					$regOptions ['extra_register_vars'] ['location'] = $user_location;
					$regOptions ['extra_register_vars'] ['real_name'] = $user_full_name;
					$regOptions ['extra_register_vars'] ['personal_text'] = $user_about_me;

					// Social Network Avatar
					if (!empty ($modSettings ['oasl_settings_use_avatars']) && !empty ($user_picture))
						$regOptions ['extra_register_vars'] ['avatar'] = $user_picture;

					// We don't need activation.
					$regOptions ['require'] = 'nothing';

					// Do not check the password strength.
					$regOptions ['check_password_strength'] = false;

					// Compute a unique username.
					$regOptions ['username'] = $user_login;
					if (isReservedName ($regOptions ['username']))
					{
						$i = 1;
						do
						{
							$user_login_tmp = $regOptions ['username'] . ($i++);
						}
						while (isReservedName ($user_login_tmp));
						$regOptions ['username'] = $user_login_tmp;
					}

					// Cut if username is too long.
					$regOptions ['username'] = substr ($regOptions ['username'], 0, 25);

					// Encode.
					if (!$context['utf8'])
					{
						$regOptions ['extra_register_vars'] ['location'] = utf8_decode($regOptions ['extra_register_vars'] ['location']);
						$regOptions ['extra_register_vars'] ['real_name'] = utf8_decode($regOptions ['extra_register_vars'] ['real_name']);
						$regOptions ['extra_register_vars'] ['personal_text'] = utf8_decode($regOptions ['extra_register_vars'] ['personal_text']);
						$regOptions ['username'] = utf8_decode($regOptions ['username']);
					}

					//Other settings
					$modSettings ['disableRegisterCheck'] = true;
					$user_info ['is_guest'] = true;

					// Create a new user account.
					$id_member = registerMember ($regOptions);
					if (is_numeric ($id_member))
					{
						// Tie the tokens to the newly created member.
						oneall_social_login_link_tokens_to_id_member ($id_member, $user_token, $identity_token);
					}
				}


				// Login.
				if (!empty ($id_member))
				{
					// Read user data.
					$request = $smcFunc ['db_query'] ('', '
						SELECT passwd, id_member, id_group, lngfile, is_activated, email_address, additional_groups, member_name, password_salt, openid_uri, passwd_flood
						FROM {db_prefix}members
						WHERE id_member = {int:id_member} LIMIT 1',
						array (
							'id_member' => $id_member,
						)
					);
					$user_settings = $smcFunc ['db_fetch_assoc'] ($request);
					$smcFunc ['db_free_result'] ($request);

					if (!empty ($user_settings ['id_member']))
					{
						// Login.
						require_once($sourcedir . '/LogInOut.php');
						DoLogin ();

						//Redirect
						if ($action == 'login')
							redirectexit ();
						else
							redirectexit ('action=profile');
					}
				}
			}

		}
	}

	// Error
	redirectexit ();
}

// Launch callback handler.
if (defined ('SMF') && !empty ($_POST ['oa_action']) && !empty ($_POST ['connection_token']))
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