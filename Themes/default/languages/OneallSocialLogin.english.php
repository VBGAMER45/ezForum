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


// OneAll Social Login (https://docs.oneall.com/plugins/)
$txt['oasl_default'] = 'Default';
$txt['oasl_follow_twitter'] = '<a href="http://www.twitter.com/oneall" target="_blank">Follow us on Twitter</a> to stay informed about updates';
$txt['oasl_read_documentation'] = '<a href="http://docs.oneall.com/plugins/guide/social-login-smf/" target="_blank">Read the online documentation</a> for more information';
$txt['oasl_contact_us'] = '<a href="http://www.oneall.com/company/contact-us/" target="_blank">Contact us</a> if you have feedback or need assistance';
$txt['oasl_other_plugins'] = 'We also have turnkey plugins for <a href="http://docs.oneall.com/plugins/" target="_blank">WordPress, Drupal, phpBB and Joomla</a> amongst others';
$txt['oasl_api_connection_handler'] = 'API Connection Handler';
$txt['oasl_api_connection_method'] = 'API Connection Handler:';
$txt['oasl_api_connection_use_curl'] = 'Use PHP CURL to communicate with the OneAll API';
$txt['oasl_api_connection_use_curl_desc'] = 'Using CURL is recommended but it might be disabled on some servers';
$txt['oasl_api_connection_use_fsockopen'] = 'Use PHP FSOCKOPEN to communicate with the OneAll API';
$txt['oasl_api_connection_use_fsockopen_desc'] = 'Try using FSOCKOPEN if you encounter any problems with CURL';
$txt['oasl_api_connection_port'] = 'API Connection Port:';
$txt['oasl_api_connection_port_80'] = 'Communication via HTTP on port 80';
$txt['oasl_api_connection_port_80_desc'] = 'Using port 80 is a bit faster and does not require OpenSSL to be installed';
$txt['oasl_api_connection_port_443'] = 'Communication via HTTPS on port 443';
$txt['oasl_api_connection_port_443_desc'] = 'Using port 443 is more secure than using port 80 but you might have to install OpenSSL on your server';
$txt['oasl_api_connection_autodetect'] = 'Click here to autodetect the API Connection Handler';
$txt['oasl_api_connection_autodetect_success'] = 'The API Connection Handler has been detected and updated successfully!';
$txt['oasl_api_connection_autodetect_error'] = 'Autodetection Error! Please install PHP CURL and allow outbound requests on port 80 or 443 in your firewall.';
$txt['oasl_api_connection_autodetect_wait'] = 'The autodetection can take a couple of seconds ...';
$txt['oasl_api_settings'] = 'API Settings';
$txt['oasl_api_credentials'] = 'API Credentials:';
$txt['oasl_api_credentials_get'] = 'Click here to create and view your API Credentials';
$txt['oasl_api_subdomain'] = 'API Subdomain:';
$txt['oasl_api_public_key'] = 'API Public Key:';
$txt['oasl_api_private_key'] = 'API Private Key:';
$txt['oasl_api_verify'] = 'Click here to verify the API Settings';
$txt['oasl_api_verify_success'] =  'The API Settings have been verified and updated successfully!';
$txt['oasl_api_verify_missing'] = 'You have to fill out all of the fields before verifying your settings.';
$txt['oasl_api_verify_error_handler'] = 'The API connection could not be established. Please try to use the autodetection above.';
$txt['oasl_api_verify_error_subdomain'] = 'The subdomain does not exist. Have you filled it out correctly?';
$txt['oasl_api_verify_error_syntax'] = 'The subdomain has an invalid syntax.';
$txt['oasl_api_verify_error_keys'] = 'The subdomain is correct but one or both keys are invalid.';
$txt['oasl_api_verify_wait'] = 'The verification can take a couple of seconds ...';
$txt['oasl_enable_networks'] = 'Enable the social networks that you would like to use';
$txt['oasl_enable']  = 'Enable';
$txt['oasl_settings'] = 'Additional Settings';
$txt['oasl_settings_login_text'] = 'Use the following description on the login page:';
$txt['oasl_settings_register_text'] = 'Use the following description on the registration page:';
$txt['oasl_settings_profile_text'] = 'Use the following title on the users\' profile pages:';
$txt['oasl_settings_profile_desc'] = 'Use the following description on the users\' profile pages:';
$txt['oasl_settings_social_avatar'] = 'Use the user picture from the social network as forum avatar?';
$txt['oasl_settings_social_avatar_desc'] = 'Only works with social networks that provide a user picture.';
$txt['oasl_settings_social_avatar_yes'] = 'Yes, use the social network pictures as avatars.';
$txt['oasl_settings_social_link'] = 'Try to link new social network accounts to existing user accounts?';
$txt['oasl_settings_social_link_desc'] = 'For security reasons this only works with social networks that provide a <strong>verified</strong> email address.';
$txt['oasl_settings_social_link_yes'] = 'Yes, try to link accounts by using the email addresses.';
$txt['oasl_save_settings'] = 'Save Settings';

?>