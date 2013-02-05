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
	die('You are not allowed to access this file directly');

//OneAll Social Login Version
define('OASL_VERSION', '1.5');

/**
 * Links the user/identity tokens to an id_member
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
		curl_setopt($curl, CURLOPT_USERPWD, $options['api_key'] . ":" . $options['api_secret']);

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
		$defaults['Authorization'] = 'Authorization: Basic ' . base64_encode($options['api_key'] . ":" . $options['api_secret']);

	//Build and send request
	$request = 'GET ' . $path . " HTTP/1.0\r\n";
	$request .= implode("\r\n", $defaults);
	$request .= "\r\n\r\n";
	fwrite($fp, $request);

	//Fetch response
	$response = '';
	while (!feof($fp))
		$response .= fread($fp, 1024);

	// Close connection.
	fclose($fp);

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

?>