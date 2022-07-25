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

// Request addition fields during the social login registration
function template_oneall_social_login_registration ()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Any errors?
	if (!empty($context['oasl_registration_errors']) AND is_array ($context['oasl_registration_errors']))
	{
		echo '
					<div class="register_error">
						<span>'. $txt['oasl_register_errors']. '</span>
						<ul class="reset">
							<li>'.implode ('</li><il>',$context['oasl_registration_errors']).'</li>
						</ul>
					</div>';
	}

	?>
  	<form action="<?php echo  $scripturl; ?>?action=oasl_registration" method="post" accept-charset="<?php echo $context['character_set'];?>">
			<div class="cat_bar">
				<h3 class="catbg"><?php echo str_replace ('{provider}', $modSettings['oasl_provider'], $txt['oasl_register_connected']); ?></h3>
			</div>
			<span class="upperframe"><span></span></span>
			<div class="roundframe">
				<p><?php echo str_replace ('{provider}', $modSettings['oasl_provider'], $txt['oasl_register_complete_profile']); ?></p>
			</div>
			<span class="lowerframe"><span></span></span>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<fieldset class="content">
					<dl class="register_form">
						<dt>
							<strong><label for="email_address"><?php echo $txt['oasl_register_email'];?>:</label></strong>
						</dt>
						<dd>
							<input type="text" name="email_address" size="30" id="email_address" tabindex="<?php echo ($context['tabindex']++);?>" value="<?php echo ! empty ($modSettings['email_address']) ? htmlspecialchars($modSettings['email_address']) : '';?>" class="input_text" />
						</dd>
						<dt>
							<strong><label for="public_email_address"><?php echo $txt['oasl_register_email_public'];?>:</label></strong>
						</dt>
						<dd>
							<input type="checkbox" name="public_email_address" id="public_email_address" value="1" tabindex="<?php echo ($context['tabindex']++);?>" class="input_check" <?php echo ! empty ($modSettings['public_email_address']) ? 'checked="checked"' : ''; ?>/>
						</dd>
					</dl>
				</fieldset>
				<span class="botslice"><span></span></span>
			</div>
			<div id="confirm_buttons">
				<input type="submit" name="confirm_information" value="<?php echo $txt['oasl_register_confirm'];?>" class="button_submit" />
				<input type="hidden" name="sc" value="<?php echo $context['session_id']; ?>" />
				<input type="hidden" name="sa" value="confirm" />
			</div>
		</form>
	<?php
}


?>