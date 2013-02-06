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

/**
 * Display the settings in the administraton area
 */
function template_oneall_social_login_config ()
{
	global $txt, $context, $scripturl, $modSettings;

	?>
	
		<form method="post" name="creator" id="creator" action="<?php echo $scripturl; ?>?action=oasl" accept-charset="<?php echo $context['character_set']; ?>">
			<div class="cat_bar" id="oasl_api_connection_handler">
				<h3 class="catbg">
					<span class="ie6_header floatleft"><?php echo $txt['oasl_api_connection_handler']; ?></span>
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl>
						<dt>
							<strong><?php echo $txt['oasl_api_connection_method']; ?></strong>
						</dt>
						<dd>
							<input type="radio" id="oasl_api_handler_curl" name="oasl_api_handler" value="curl"<?php echo ($modSettings['oasl_api_handler'] <> 'fsockopen' ? ' checked="checked"' : ''); ?> />
							<label for="oasl_api_handler_curl"><?php echo $txt['oasl_api_connection_use_curl']; ?> <strong>(<?php echo $txt['oasl_default']; ?>)</strong></label>
							<div class="description"><?php echo $txt['oasl_api_connection_use_curl_desc']; ?></div>
						</dd>
						<dt>&nbsp;</dt>
						<dd>
							<input type="radio" id="oasl_api_handler_fsockopen" name="oasl_api_handler" value="fsockopen"<?php echo ($modSettings['oasl_api_handler'] == 'fsockopen' ? ' checked="checked"' : ''); ?> />
							<label for="oasl_api_handler_fsockopen"><?php echo $txt['oasl_api_connection_use_fsockopen']; ?></label>
							<div class="description"><?php echo $txt['oasl_api_connection_use_fsockopen_desc']; ?></div>
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<dl>
						<dt>
							<strong><?php echo $txt['oasl_api_connection_port']; ?></strong>
						</dt>
						<dd>
							<input type="radio" id="oasl_api_port_443" name="oasl_api_port" value="443"<?php echo ($modSettings['oasl_api_port'] <> 80 ? ' checked="checked"' : ''); ?> />
							<label for="oasl_api_port_443"><?php echo $txt['oasl_api_connection_port_443']; ?> <strong>(<?php echo $txt['oasl_default']; ?>)</strong></label>
							<div class="description"><?php echo $txt['oasl_api_connection_port_443_desc']; ?></div>
						</dd>
						<dt>&nbsp;</dt>
						<dd>
							<input type="radio" id="oasl_api_port_80" name="oasl_api_port" value="80"<?php echo ($modSettings['oasl_api_port'] == 80 ? ' checked="checked"' : ''); ?> />
							<label for="oasl_api_port_80"><?php echo $txt['oasl_api_connection_port_80']; ?></label>
							<div class="description"><?php echo $txt['oasl_api_connection_port_80_desc']; ?></div>
						</dd>
					</dl>
				</div>
				<div>
					<input type="button" class="button_submit" id="oasl_autodetect_button" value="<?php echo $txt['oasl_api_connection_autodetect']; ?>" />
					<?php
						if ($modSettings['oasl_action'] == 'autodetect')
						{
							switch ($modSettings['oasl_status'])
							{
								case 'success':
									echo '<span class="oasl_success_message">' . $txt['oasl_api_connection_autodetect_success'] . '</span>';
								break;

								case 'error':
									echo '<span class="oasl_error_message">' . $txt['oasl_api_connection_autodetect_error'] . '</span>';
								break;
							}
						}
						else
						{
							echo '<span class="oasl_info_message">' . $txt['oasl_api_connection_autodetect_wait'] . '</span>';
						}
					?>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<div class="cat_bar" id="oasl_api_settings">
				<h3 class="catbg">
					<span class="ie6_header floatleft"><?php echo $txt['oasl_api_settings']; ?></span>
				</h3>
			</div>
			<div class="windowbg2">
				<div class="oasl_info_box information">
					<?php echo $txt['oasl_api_credentials']; ?> <a href="https://app.oneall.com/applications/" target="_blank"><?php echo $txt['oasl_api_credentials_get']; ?></a>
				</div>
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl>
						<dt>
							<label for="oasl_api_subdomain"><strong><?php echo $txt['oasl_api_subdomain']; ?></strong></label>
						</dt>
						<dd>
							<input type="text" id="oasl_api_subdomain" name="oasl_api_subdomain" size="50" value="<?php echo htmlspecialchars($modSettings['oasl_api_subdomain']); ?>" />
						</dd>
						<dt>
							<label for="oasl_api_key"><strong><?php echo $txt['oasl_api_public_key']; ?></strong></label>
						</dt>
						<dd>
							<input type="text" id="oasl_api_key" name="oasl_api_key" size="50" value="<?php echo htmlspecialchars($modSettings['oasl_api_key']); ?>" />
						</dd>
						<dt>
							<label for="oasl_api_secret"><strong><?php echo $txt['oasl_api_private_key']; ?></strong></label>
						</dt>
						<dd>
							<input type="text" id="oasl_api_secret" name="oasl_api_secret" size="50" value="<?php echo htmlspecialchars($modSettings['oasl_api_secret']); ?>" />
						</dd>
					</dl>
				</div>
				<div>
					<input type="button" class="button_submit" id="oasl_verify_button" value="<?php echo $txt['oasl_api_verify']; ?>" />
					<?php
						if ($modSettings['oasl_action'] == 'verify')
						{
							switch ($modSettings['oasl_status'])
							{
								case 'success':
									echo '<span class="oasl_success_message">' . $txt['oasl_api_verify_success'] . '</span>';
								break;

								case 'error_not_all_fields_filled_out':
									echo '<span class="oasl_error_message">' . $txt['oasl_api_verify_missing'] . '</span>';
								break;

								case 'error_communication':
								case 'error_selected_handler_faulty':
									echo '<span class="oasl_error_message">' . $txt['oasl_api_verify_error_handler'] . '</span>';
								break;

								case 'error_subdomain_wrong':
									echo '<span class="oasl_error_message">' . $txt['oasl_api_verify_error_subdomain'] . '</span>';
								break;

								case 'error_subdomain_wrong_syntax':
									echo '<span class="oasl_error_message">' . $txt['oasl_api_verify_error_syntax'] . '</span>';
								break;

								case 'error_authentication_credentials_wrong':
									echo '<span class="oasl_error_message">' . $txt['oasl_api_verify_error_keys'] . '</span>';
								break;
							}
						}
						else
						{
							echo '<span class="oasl_info_message">' . $txt['oasl_api_verify_wait'] . '</span>';
						}
						?>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft"><?php echo $txt['oasl_enable_networks']; ?></span>
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl>
						<?php
							foreach ($modSettings['oasl_providers'] AS $provider)
							{
								echo '
									<dt class="oasl_provider_row">
										<label for="oasl_provider_' . $provider . '"><span class="oasl_provider oasl_provider_' . $provider . '">' . ucwords(strtolower($provider)) . '</span></label>
										<input type="checkbox" id="oasl_provider_ ' . $provider . '" name="oasl_enabled_providers[]" value="' . $provider . '"' . ((in_array($provider, $modSettings['oasl_enabled_providers'])) ? 'checked="checked"' : '') .' />
										<label for="oasl_provider_' . $provider . '">' . $txt['oasl_enable'] . ' <strong>' . ucwords(strtolower($provider)) . '</strong></label>
									</dd>
									<dd>&nbsp;</dd>';
							}
						?>
					</dl>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft"><?php echo $txt['oasl_settings']; ?></span>
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl>
						<dt>
							<label for="oasl_settings_login_caption"><strong><?php echo $txt['oasl_settings_login_text']; ?></strong></label>
						</dt>
						<dd>
							<input type="text" id="oasl_settings_login_caption" name="oasl_settings_login_caption" size="50" value="<?php echo htmlspecialchars($modSettings['oasl_settings_login_caption']); ?>" />
						</dd>
						<dt>
							<label for="oasl_settings_registration_caption"><strong><?php echo $txt['oasl_settings_register_text']; ?></strong></label>
						</dt>
						<dd>
							<input type="text" id="oasl_settings_registration_caption" name="oasl_settings_registration_caption" size="50" value="<?php echo htmlspecialchars($modSettings['oasl_settings_registration_caption']); ?>" />
						</dd>

						<dt>
							<label for="oasl_settings_profile_caption"><strong><?php echo $txt['oasl_settings_profile_text']; ?></strong></label>
						</dt>
						<dd>
							<input type="text" id="oasl_settings_profile_caption" name="oasl_settings_profile_caption" size="50" value="<?php echo htmlspecialchars($modSettings['oasl_settings_profile_caption']); ?>" />
						</dd>
						<dt>
							<label for="oasl_settings_profile_desc"><strong><?php echo $txt['oasl_settings_profile_desc']; ?></strong></label>
						</dt>
						<dd>
							<input type="text" id="oasl_settings_profile_desc" name="oasl_settings_profile_desc" size="50" value="<?php echo htmlspecialchars($modSettings['oasl_settings_profile_desc']); ?>" />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<dl>
						<dt>
							<strong><?php echo $txt['oasl_settings_social_avatar']; ?></strong><br />
							<span class="smalltext"><?php echo $txt['oasl_settings_social_avatar_desc']; ?></span>
						</dt>
						<dd>
							<input type="checkbox" id="oasl_settings_use_avatars" name="oasl_settings_use_avatars" value="1"<?php echo (!empty($modSettings['oasl_settings_use_avatars']) ? ' checked="checked"' : ''); ?> />
							<label for="oasl_settings_use_avatars"><?php echo $txt['oasl_settings_social_avatar_yes']; ?></label>
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<dl>
						<dt>
							<strong><?php echo $txt['oasl_settings_social_link']; ?></strong><br />
							<span class="smalltext"><?php echo $txt['oasl_settings_social_link_desc']; ?></span>
						</dt>
						<dd>
							<input type="checkbox" id="oasl_settings_link_accounts" name="oasl_settings_link_accounts" value="1"<?php echo (!empty($modSettings['oasl_settings_link_accounts']) ? ' checked="checked"' : ''); ?> />
							<label for="oasl_settings_link_accounts"><?php echo $txt['oasl_settings_social_link_yes']; ?></label>
						</dd>
					</dl>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<hr class="hrcolor clear" />
			<div class="righttext">
				<input type="submit" class="button_submit" value="<?php echo $txt['oasl_save_settings']; ?>" />
				<input type="hidden" name="sc" value="<?php echo $context['session_id']; ?>" />
				<input type="hidden" id="oasl_sa" name="sa" value="save" />
			</div>
		</form>
 	<?php
}

?>