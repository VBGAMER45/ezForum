<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines
 * @copyright 2018 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0.16
 */

// The main sub template - show the agreement and/or privacy policy
function template_main()
{
	global $context, $scripturl, $txt;

	if (!empty($context['accept_doc']))
		echo '
	<form action="', $scripturl, '?action=acceptagreement;doc=', $context['accept_doc'], '" method="post">';

	if (!empty($context['agreement']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['agreement' . ($context['can_accept_agreement'] ? '_updated' : '')], '</h3>
		</div>';

		if ($context['can_accept_agreement'])
		{
			echo '
		<p class="description">
			', $txt['agreement_updated_desc'], '
		</p>';
		}
		elseif (!empty($context['agreement_accepted_date']))
		{
			echo '
		<p class="description">
			', sprintf($txt['agreement_accepted'], timeformat($context['agreement_accepted_date'], false)), '
		</p>';
		}

		echo '
		<span class="upperframe"><span></span></span>
		<div class="roundframe">
			<div>', $context['agreement'], '</div>
		</div>
		<span class="lowerframe"><span></span></span>';
	}

	if (!empty($context['policy']))
	{
		if (!empty($context['agreement']))
			echo '<br />';

		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['privacy_policy' . ($context['can_accept_privacy_policy'] ? '_updated' : '')], '</h3>
		</div>';

		if ($context['can_accept_privacy_policy'])
		{
			echo '
		<p class="description">
			', $txt['privacy_policy_updated_desc'], '
		</p>';
		}
		elseif (!empty($context['privacy_policy_accepted_date']))
		{
			echo '
		<p class="description">
			', sprintf($txt['privacy_policy_accepted'], timeformat($context['privacy_policy_accepted_date'], false)), '
		</p>';
		}

		echo '
		<span class="upperframe"><span></span></span>
		<div class="roundframe">
			<div>', $context['policy'], '</div>
		</div>
		<span class="lowerframe"><span></span></span>';
	}

	if (!empty($context['accept_doc']))
		echo '
		<div id="confirm_buttons">
			<input type="submit" value="', $txt['agree'], '" class="button_submit" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</div>
	</form>';
}

?>