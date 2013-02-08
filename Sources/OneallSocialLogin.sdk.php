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

//OneAll Social Login Version
define('OASL_VERSION', '2.1');


/**
 * Removes any session data stored by the plugin.
 */
function oneall_social_login_clear_session()
{
	foreach (array('session_open', 'session_time', 'social_data', 'origin') AS $key)
	{
		$key = 'oasl_' . $key;

		if (isset($_SESSION [$key]))
		{
			unset($_SESSION [$key]);
		}
	}
}


/**
 * Extracts the social network data from a result-set returned by the OneAll API.
 */
function oneall_social_login_extract_social_network_profile ($social_data)
{
	// Check API result.
	if (is_object ($social_data) && property_exists ($social_data, 'http_code') && $social_data->http_code == 200 && property_exists ($social_data, 'http_data'))
	{
		// Decode the social network profile Data.
		$social_data = json_decode ($social_data->http_data);

		// Make sur that the data has beeen decoded properly
		if (is_object ($social_data))
		{
			// Container for user data
			$data = array();

			// Save the social network data in a session.
			$_SESSION ['oasl_session_open'] = 1;
			$_SESSION ['oasl_session_time'] = time();
			$_SESSION ['oasl_social_data'] = serialize($social_data);

			// Parse Social Profile Data.
			$identity = $social_data->response->result->data->user->identity;

			$data['identity_token'] = $identity->identity_token;
			$data['identity_provider'] = $identity->source->name;

			$data['user_token'] = $social_data->response->result->data->user->user_token;
			$data['user_first_name'] = !empty ($identity->name->givenName) ? $identity->name->givenName : '';
			$data['user_last_name'] = !empty ($identity->name->familyName) ? $identity->name->familyName : '';
			$data['user_location'] = !empty ($identity->currentLocation) ? $identity->currentLocation : '';
			$data['user_constructed_name'] = trim ($data['user_first_name'] . ' ' . $data['user_last_name']);
			$data['user_picture'] = !empty ($identity->pictureUrl) ? $identity->pictureUrl : '';
			$data['user_thumbnail'] = !empty ($identity->thumbnailUrl) ? $identity->thumbnailUrl : '';
			$data['user_about_me'] = !empty ($identity->aboutMe) ? $identity->aboutMe : '';

			// Birthdate - SMF expects YYYY-DD-MM
			if ( ! empty ($identity->birthday) && preg_match ('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', $identity->birthday, $matches))
			{
				$data['user_birthdate'] =  str_pad($matches[3], 4, '0', STR_PAD_LEFT);
				$data['user_birthdate'] .= '-'. str_pad ($matches[2], 2, '0' , STR_PAD_LEFT);
				$data['user_birthdate'] .= '-'. str_pad ($matches[1], 2, '0' , STR_PAD_LEFT);
			}
			else
			{
				$data['user_birthdate'] = '0001-01-01';
			}

			// Fullname.
			if (!empty ($identity->name->formatted))
			{
				$data['user_full_name'] = $identity->name->formatted;
			}
			elseif (!empty ($identity->name->displayName))
			{
				$data['user_full_name'] = $identity->name->displayName;
			}
			else
			{
				$data['user_full_name'] = $data['user_constructed_name'];
			}

			// Preferred Username.
			if (!empty ($identity->preferredUsername))
			{
				$data['user_login'] = $identity->preferredUsername;
			}
			elseif (!empty ($identity->displayName))
			{
				$data['user_login'] = $identity->displayName;
			}
			else
			{
				$data['user_login'] = $data['user_full_name'];
			}

			// Email Address.
			$data['user_email'] = '';
			if (property_exists ($identity, 'emails') && is_array ($identity->emails))
			{
				$data['user_email_is_verified'] = false;
				while ($data['user_email_is_verified'] !== true && (list(, $obj) = each ($identity->emails)))
				{
					$data['user_email'] = $obj->value;
					$data['user_email_is_verified'] = !empty ($obj->is_verified);
				}
			}

			// Website/Homepage.
			$data['user_website'] = '';
			if (!empty ($identity->profileUrl))
			{
				$data['user_website'] = $identity->profileUrl;
			}
			elseif (!empty ($identity->urls [0]->value))
			{
				$data['user_website'] = $identity->urls [0]->value;
			}

			// Gender
			$data['user_gender'] = 0;
			if (!empty ($identity->gender))
			{
				switch ($identity->gender)
				{
					case 'male':
						$data['user_gender'] = 1;
						break;

					case 'female':
						$data['user_gender'] = 2;
						break;
				}
			}

			return $data;
		}
	}
	return false;
}


/**
 * Logs a given user in.
 */
function oneall_social_login_login_user ($id_member)
{
	// Setup global forum vars.
	global $txt, $boarddir, $sourcedir, $user_settings, $context, $modSettings, $smcFunc;

	// Check identifier.
	if (is_numeric ($id_member) AND $id_member > 0)
	{
		// Read user data.
		$request = $smcFunc ['db_query'] ('', '
			SELECT passwd, id_member, id_group, lngfile, is_activated, email_address, additional_groups, member_name, password_salt, openid_uri, passwd_flood
			FROM {db_prefix}members
			WHERE id_member = {int:id_member} LIMIT 1',
				array (
						'id_member' => intval ($id_member)
				)
		);
		$user_settings = $smcFunc ['db_fetch_assoc'] ($request);
		$smcFunc ['db_free_result'] ($request);

		// Do we have a valid member here?
		if (!empty ($user_settings ['id_member']))
		{
			// Login.
			require_once($sourcedir . '/LogInOut.php');
			DoLogin ();

			//Done
			return true;
		}
	}

	//Error
	return false;
}

/**
 * Creates a new user based on the given data.
 */
function oneall_social_login_create_user (Array $data)
{
	if (is_array ($data) && ! empty ($data['user_token']) && ! empty ($data['identity_token']))
	{
		// Global vars.
		global $boarddir, $sourcedir, $user_settings, $context, $modSettings, $smcFunc;

		// Registration functions.
		require_once($sourcedir . '/Subs-Members.php');

		// Build User fields.
		$regOptions = array ();
		$regOptions ['password'] = substr (md5 (mt_rand ()), 0, 8);
		$regOptions ['password_check'] = $regOptions ['password'];
		$regOptions ['auth_method'] = 'password';
		$regOptions ['interface'] = 'guest';

		// Email address is provided.
		if (!empty ($data['user_email']))
		{
			$regOptions ['email'] = $data['user_email'];
			$regOptions ['hide_email'] = ! isset ($data['hide_email']) ? 0 : $data['hide_email'];
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
		$regOptions ['extra_register_vars'] ['website_url'] = $data['user_website'];
		$regOptions ['extra_register_vars'] ['gender'] = $data['user_gender'];
		$regOptions ['extra_register_vars'] ['location'] = $data['user_location'];
		$regOptions ['extra_register_vars'] ['real_name'] = (! empty ($data['user_full_name']) ? $data['user_full_name'] : $data['user_login']);
		$regOptions ['extra_register_vars'] ['personal_text'] = $data['user_about_me'];


		$regOptions ['extra_register_vars'] ['birthdate'] = $data['user_birthdate'];

		// Social Network Avatar
		if (!empty ($modSettings ['oasl_settings_use_avatars']) && !empty ($data['user_picture']))
		{
			$regOptions ['extra_register_vars'] ['avatar'] = $data['user_picture'];
		}

		// We don't need activation.
		$regOptions ['require'] = 'nothing';

		// Do not check the password strength.
		$regOptions ['check_password_strength'] = false;

		// Compute a unique username.
		$regOptions ['username'] = $data['user_login'];
		if (isReservedName ($regOptions ['username']))
		{
			$i = 1;
			do
			{
				$tmp = $regOptions ['username'] . ($i++);
			}
			while (isReservedName ($tmp));
			$regOptions ['username'] = $tmp;
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
			oneall_social_login_link_tokens_to_id_member ($id_member, $data['user_token'], $data['identity_token']);
		}

		//Done
		return $id_member;
	}

	//Error
	return false;
}


/**
 * Links the user/identity tokens to an id_member.
 */
function oneall_social_login_link_tokens_to_id_member ($id_member, $user_token, $identity_token)
{
	global $smcFunc;

	// Make sure that that the id_member exists.
	$request = $smcFunc['db_query']('', 'SELECT id_member FROM {db_prefix}members WHERE id_member = {int:id_member} LIMIT 1', array('id_member' => $id_member));
	$row_member = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// The user account has been found!
	if (!empty($row_member['id_member']))
	{
		// Read the entry for the given user_token.
		$request = $smcFunc['db_query']('', 'SELECT id_oasl_user, id_member FROM {db_prefix}oasl_users WHERE user_token = {string:user_token} LIMIT 1', array('user_token' => $user_token));
		$oasl_user = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		// The user_token exists but is linked to another user.
		if (!empty ($oasl_user['id_oasl_user']) && $oasl_user['id_member'] != $id_member)
		{
			// Drop the oasl_user entry.
			$smcFunc['db_query']('', 'DELETE FROM {db_prefix}oasl_users WHERE id_oasl_user = {int:id_oasl_user}', array('id_oasl_user' => $oasl_user['id_oasl_user']));

			// Drop the oasl_identities entries.
			$smcFunc['db_query']('', 'DELETE FROM {db_prefix}oasl_identities WHERE id_oasl_user = {int:id_oasl_user}', array('id_oasl_user' => $oasl_user['id_oasl_user']));

			// Reset the identifier to create a new one.
			$oasl_user['id_oasl_user'] = null;
		}

		// The user_token either does not exist or has been reset.
		if (empty($oasl_user['id_oasl_user']))
		{
			// Link user_token to id_member.
			$smcFunc['db_insert']('insert', '{db_prefix}oasl_users', array('id_member' => 'int', 'user_token' => 'string'), array($id_member, $user_token), array('id_member', 'user_token'));

			// Identifier of the newly created user_token entry.
			$oasl_user['id_oasl_user'] = $smcFunc['db_insert_id'] ('{db_prefix}oasl_users', 'id_oasl_user');
		}

		// Read the entry for the given identity_token.
		$request = $smcFunc['db_query']('', 'SELECT id_oasl_identity,id_oasl_user,identity_token FROM {db_prefix}oasl_identities WHERE identity_token = {string:identity_token} LIMIT 1', array('identity_token' => $identity_token));
		$oasl_identity = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		// The identity_token exists but is linked to another user_token.
		if (!empty ($oasl_identity['id_oasl_identity']) && $oasl_identity['id_oasl_user'] != $oasl_user['id_oasl_user'])
		{
			// Drop the oasl_identities entries.
			$smcFunc['db_query']('', 'DELETE FROM {db_prefix}oasl_identities WHERE id_oasl_identity = {int:id_oasl_identity}', array('id_oasl_identity' => $oasl_identity['id_oasl_identity']));

			// Reset the identifier to create a new one.
			$oasl_identity['id_oasl_identity'] = null;
		}

		// The identity_token either does not exist or has been reset.
		if (empty($oasl_identity['id_oasl_identity']))
		{
			// Add identity.
			$smcFunc['db_insert']('insert', '{db_prefix}oasl_identities', array('id_oasl_user' => 'int', 'identity_token' => 'string'), array($oasl_user['id_oasl_user'], $identity_token), array('id_oasl_user', 'identity_token'));

			// Identifier of the newly created identity_token entry.
			$oasl_identity['id_oasl_identity'] = $smcFunc['db_insert_id'] ('{db_prefix}oasl_identities', 'id_oasl_identity');
		}

		// Done.
		return true;
	}

	//An error occured
	return false;
}


/**
 * UnLinks the identity from an id_member
 */
function oneall_social_login_unlink_identity_token ($identity_token)
{
	global $smcFunc;

	// Drop the user_identities entry.
	$smcFunc['db_query']('', 'DELETE FROM {db_prefix}oasl_identities WHERE identity_token = {string:identity_token}', array('identity_token' => $identity_token));
	return ($smcFunc['db_affected_rows'] () > 0) ;
}


/**
 * Get the user_token for a given id_member
 */
function oneall_social_login_get_user_token_for_id_member ($id_member)
{
	global $smcFunc;

	// Check if not empty.
	if ( ! is_numeric ($id_member) || $id_member < 1)
	{
		return false;
	}

	//Get the user_token for a given id_member.
	$request = $smcFunc['db_query']('', 'SELECT user_token FROM {db_prefix}oasl_users WHERE id_member = {int:id_member} LIMIT 1', array('id_member' => $id_member));
	$row_oasl_user = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Either return the user_token or false if none has been found.
	return !empty($row_oasl_user['user_token']) ? $row_oasl_user['user_token'] : false;
}


/**
 * Get the user identifier for a given token
 */
function oneall_social_login_get_id_member_for_user_token ($user_token)
{
	global $smcFunc;

	//Get the user identifier for a given token
	$request = $smcFunc['db_query']('', 'SELECT id_oasl_user, id_member FROM {db_prefix}oasl_users WHERE user_token = {string:user_token} LIMIT 1', array('user_token' => $user_token));
	$row_oasl_user = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// We have found an entry
	if (!empty($row_oasl_user['id_oasl_user']))
	{
		// Check if the user account exists.
		$request = $smcFunc['db_query']('', 'SELECT id_member FROM {db_prefix}members WHERE id_member = {int:id_member} LIMIT 1', array('id_member' => $row_oasl_user['id_member']));
		$row_member = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		// The user account exists, return it's identifier.
		if (!empty($row_member['id_member']))
			return $row_member['id_member'];

		//Delete the wrongly linked user_token.
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}oasl_users WHERE id_oasl_user = {int:id_oasl_user}', array('id_oasl_user' => $row_oasl_user['id_oasl_user']));

		//Delete the wrongly linked identity_token.
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}oasl_identities WHERE id_oasl_user = {int:id_oasl_user}', array('id_oasl_user' => $row_oasl_user['id_oasl_user']));
	}

	//No entry found
	return false;
}


/**
 * Get the user identifier for a given email address
 */
function oneall_social_login_get_id_member_for_email_address ($email_address)
{
	global $smcFunc;

	// Check if not empty.
	if (strlen (trim ($email_address)) == 0)
	{
		return false;
	}

	// Check if the user account exists.
	$request = $smcFunc['db_query']('', 'SELECT id_member FROM {db_prefix}members WHERE email_address = {string:email_address} LIMIT 1', array('email_address' => $email_address));
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Either return the id_member or false if none has been found.
	return !empty($row['id_member']) ? $row['id_member'] : false;
}


/**
 * Create a random email
 */
function oneall_social_login_create_rand_email_address ()
{
	do
	{
		$email_address = md5(uniqid(mt_rand(10000, 99000))) . "@example.com";
	}
	while (oneall_social_login_get_id_member_for_email_address($email_address) !== false);
	return $email_address;
}


/**
 * Send an API request by using the given handler
 */
function oneall_social_login_do_api_request ($handler, $url, $options = array(), $timeout = 15)
{
	switch ($handler)
	{
		case 'fsockopen':
			return oneall_social_login_fsockopen_request($url, $options, $timeout);

		case 'curl':
		default:
			return oneall_social_login_curl_request($url, $options, $timeout);
	}
}


/**
 * Check if CURL can be used to communicate with the OneAll API
 */
function oneall_social_login_check_curl ($secure = true)
{
	if (in_array('curl', get_loaded_extensions()) && function_exists('curl_exec'))
	{
		$result = oneall_social_login_curl_request(($secure ? 'https' : 'http') . '://www.oneall.com/ping.html');
		return (is_object($result) && property_exists($result, 'http_code') && $result->http_code == 200 && property_exists($result, 'http_data') && strtolower($result->http_data) == 'ok');
	}

	//  CURL is not installed.
	return false;
}


/**
 * Send a CURL request to the OneAll API
 */
function oneall_social_login_curl_request ($url, $options = array(), $timeout = 10)
{
	//Store the result
	$result = new stdClass();

	//Send request
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($curl, CURLOPT_VERBOSE, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_USERAGENT, 'SocialLogin ' . OASL_VERSION . ' SMF (+http://www.oneall.com/)');

	// BASIC AUTH?
	if (isset($options['api_key']) && isset($options['api_secret']))
	{
		curl_setopt($curl, CURLOPT_USERPWD, $options['api_key'] . ":" . $options['api_secret']);
	}

	//Make request
	if (($http_data = curl_exec($curl)) !== false)
	{
		$result->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$result->http_data = $http_data;
		$result->http_error = null;
	}
	else
	{
		$result->http_code = -1;
		$result->http_data = null;
		$result->http_error = curl_error($curl);
	}

	//Done
	return $result;
}


/**
 * Checks if fsockopen can be used to communicate with the OneAll API
 */
function oneall_social_login_check_fsockopen ($secure = true)
{
	$result = oneall_social_login_fsockopen_request(($secure ? 'https' : 'http') . '://www.oneall.com/ping.html');
	return (is_object($result) && property_exists($result, 'http_code') && $result->http_code == 200 && property_exists($result, 'http_data') && strtolower($result->http_data) == 'ok');
}


/**
 * Sends an fsockopen request to the OneAll API
 */
function oneall_social_login_fsockopen_request ($url, $options = array(), $timeout = 15)
{
	//Store the result
	$result = new stdClass();

	//Make that this is a valid URL
	if (($uri = parse_url($url)) == false)
	{
		$result->http_code = -1;
		$result->http_data = null;
		$result->http_error = 'invalid_uri';
		return $result;
	}

	//Make sure we can handle the schema
	switch ($uri['scheme'])
	{
		case 'http':
			$port = (isset($uri['port']) ? $uri['port'] : 80);
			$host = ($uri['host'] . ($port != 80 ? ':' . $port : ''));
			$fp = @fsockopen($uri['host'], $port, $errno, $errstr, $timeout);
			break;

		case 'https':
			$port = (isset($uri['port']) ? $uri['port'] : 443);
			$host = ($uri['host'] . ($port != 443 ? ':' . $port : ''));
			$fp = @fsockopen('ssl://' . $uri['host'], $port, $errno, $errstr, $timeout);
			break;

		default:
			$result->http_code = -1;
			$result->http_data = null;
			$result->http_error = 'invalid_schema';
			return $result;
			break;
	}

	//Make sure the socket opened properly
	if (!$fp)
	{
		$result->http_code = -$errno;
		$result->http_data = null;
		$result->http_error = trim($errstr);
		return $result;
	}

	//Construct the path to act on
	$path = (isset($uri['path']) ? $uri['path'] : '/');
	if (isset($uri['query']))
		$path .= '?' . $uri['query'];

	//Create HTTP request
	$defaults = array('Host' => "Host: $host", 'User-Agent' => 'User-Agent: SocialLogin ' . OASL_VERSION . ' SMF (+http://www.oneall.com/)');

	// BASIC AUTH?
	if (isset($options['api_key']) && isset($options['api_secret']))
	{
		$defaults['Authorization'] = 'Authorization: Basic ' . base64_encode($options['api_key'] . ":" . $options['api_secret']);
	}

	//Build and send request
	$request = 'GET ' . $path . " HTTP/1.0\r\n";
	$request .= implode("\r\n", $defaults);
	$request .= "\r\n\r\n";

	if (fwrite($fp, $request))
	{
		//Set timeout and blocking
		stream_set_blocking ($fp, false);
		stream_set_timeout ($fp, $timeout);

		//Check for timeout
		$fp_info = stream_get_meta_data ($fp);

		//Fetch response
		$response = '';
		while (!$fp_info['timed_out'] && !feof($fp))
		{
			// Read data.
			$response .= fread($fp, 1024);

			// Get meta data (which has timeout info).
			$fp_info = stream_get_meta_data ($fp);
		}

		// Close connection.
		fclose($fp);

		//Timed out?
		if ( !$fp_info['timed_out'])
		{
			// Parse response.
			list($response_header, $response_body) = explode("\r\n\r\n", $response, 2);
			$result->http_data = $response_body;

			// Parse header.
			$response_header = preg_split("/\r\n|\n|\r/", $response_header);
			list($header_protocol, $header_code, $header_status_message) = explode(' ', trim(array_shift($response_header)), 3);
			$result->http_code = $header_code;

			// Return result.
			return $result;
		}
		else
		{
			$result->http_code = -1;
			$result->http_data = null;
			$result->http_error = 'request_timed_out';
			return $result;
		}
	}
	else
	{
		$result->http_code = -1;
		$result->http_data = null;
		$result->http_error = 'request_blocked';
		return $result;
	}
}

?>