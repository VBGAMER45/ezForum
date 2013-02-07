<?php
/*
With the Custom Action Mod you can create custom pages that are wrapped in your forum's theme. You have the option to use sub-actions, select which groups can view the pages and whether to show a menu button. You can access this mod's settings in Admin -> Configuration -> Features and Options -> Custom Actions.

This modification is licensed under BSD License (http://www.opensource.org/licenses/bsd-license.php)

Copyright (c) 2010, 2011, winrules, Mave, Norv

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
The names of the contributors may be not used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/


/**********************************************************************************
* CustomAction.php                                                                *
***********************************************************************************
* Software Version:           3.0                                                 *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

function ViewCustomAction()
{
	global $context, $smcFunc, $db_prefix, $txt;

	// So which custom action is this?
	$request = $smcFunc['db_query']('', '
		SELECT id_action, name, permissions_mode, action_type, header, body
		FROM {db_prefix}custom_actions
		WHERE url = {string:url}
			AND enabled = 1',
		array(
			'url' => $context['current_action'],
		)
	);

	$context['action'] = $smcFunc['db_fetch_assoc']($request);

	$smcFunc['db_free_result']($request);

	// By any chance are we in a sub-action?
	if (!empty($_REQUEST['sa']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_action, name, permissions_mode, action_type, header, body
			FROM {db_prefix}custom_actions
			WHERE url = {string:url}
				AND enabled = 1
				AND id_parent = {int:id_parent}',
			array(
				'id_parent' => $context['action']['id_action'],
				'url' => $_REQUEST['sa'],
			)
		);

		if ($smcFunc['db_num_rows']($request) != 0)
		{
			$sub = $smcFunc['db_fetch_assoc']($request);

			$smcFunc['db_free_result']($request);

			$context['action']['name'] = $sub['name'];
			// Do we have our own permissions?
			if ($sub['permissions_mode'] != 2)
			{
				$context['action']['id_action'] = $sub['id_action'];
				$context['action']['permissions_mode'] = $sub['permissions_mode'];
			}
			$context['action']['action_type'] = $sub['action_type'];
			$context['action']['header'] = $sub['header'];
			$context['action']['body'] = $sub['body'];
		}
	}

	// Are we even allowed to be here?
	if ($context['action']['permissions_mode'] == 1)
	{
		// Standard message, please.
		$txt['cannot_ca_' . $context['action']['id_action']] = '';
		isAllowedTo('ca_' . $context['action']['id_action']);
	}

	// Do this first to allow it to be overwritten by PHP source file code.
	$context['page_title'] = $context['action']['name'];

	switch ($context['action']['action_type'])
	{
	// Any HTML headers?
	case 0:
		$context['html_headers'] .= $context['action']['header'];
		break;
	// Do we need to parse any BBC?
	case 1:
		$context['action']['body'] = parse_bbc($context['action']['body']);
		break;
	// We have some more stuff to do for PHP actions.
	case 2:
		fixPHP($context['action']['header']);
		fixPHP($context['action']['body']);

		eval($context['action']['header']);
	}

	// Get the templates sorted out!
	loadTemplate('CustomAction');
	$context['sub_template'] = 'view_custom_action';
}

// Get rid of any <? or <?php at the start of code.
function fixPHP(&$code)
{
	$code = preg_replace('~^\s*<\?(php)?~', '', $code);
}

?>