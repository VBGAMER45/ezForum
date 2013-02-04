<?php
/**
 * Auto Prune Moved Topics (apmt)
 *
 * @package apmt
 * @author emanuele
 * @copyright 2011 emanuele, Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 0.1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 *
 * Hooks
 *
 */
function apmt_add_settings (&$config_vars)
{
	global $modSettings;

	$config_vars[] = array('text', 'apmt_taskFrequency');
	$config_vars[] = array('text', 'apmt_numberOfBoards');

	if (isset($_GET['save']))
	{
		$_POST['apmt_taskFrequency'] = (int) $_POST['apmt_taskFrequency'];
		$_POST['apmt_taskFrequency'] = empty($_POST['apmt_taskFrequency']) ? 15 : $_POST['apmt_taskFrequency'];
		$_POST['apmt_numberOfBoards'] = (int) $_POST['apmt_numberOfBoards'];
		$_POST['apmt_numberOfBoards'] = empty($_POST['apmt_numberOfBoards']) ? 5 : $_POST['apmt_numberOfBoards'];
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}scheduled_tasks
			SET time_regularity = {int:taskFrequency}
			WHERE task = {string:task_func}',
			array(
				'task_func' => 'apmt_prunetopics',
				'taskFrequency' => $_POST['apmt_taskFrequency'],
		));
	}
}

/**
 *
 * Functions
 *
 */
function scheduled_apmt_prunetopics ()
{
	global $smcFunc, $modSettings, $sourcedir;

	$request = $smcFunc['db_query']('', '
		SELECT id_board, last_pruned, prune_frequency
		FROM {db_prefix}boards
		WHERE (last_pruned + prune_frequency) < {int:current_time}
			AND redirect = {string:empty_string}
			AND prune_frequency > 0
		LIMIT {int:boards_limit}',
		array(
			'current_time' => time(),
			'empty_string' => '',
			'boards_limit' => !empty($modSettings['apmt_numberOfBoards']) ? $modSettings['apmt_numberOfBoards'] : 5,
	));

	$boards_to_prune = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		if ($row['last_pruned'] < time())
			$boards_to_prune[$row['id_board']] = $row;

	if (empty($boards_to_prune))
		return;

	require_once($sourcedir . '/RemoveTopic.php');

	// For this round these are done and that's all.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}boards
		SET last_pruned = {int:current_time}
		WHERE id_board IN ({array_int:boards_list})',
		array(
			'current_time' => time(),
			'boards_list' => array_keys($boards_to_prune),
	));

	$topics = array();
	foreach ($boards_to_prune as $board)
	{
		$request = $smcFunc['db_query']('', '
			SELECT t.id_topic
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_last_msg)
			WHERE m.poster_time < {int:poster_time_limit}
				AND m.icon = {string:icon}
				AND t.locked = {int:locked}
				AND t.id_board = {int:boards}',
			array(
				'boards' => $board['id_board'],
				'poster_time_limit' => time() - $board['prune_frequency'],
				'icon' => 'moved',
				'locked' => 1,
		));
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$topics[] = $row['id_topic'];
		$smcFunc['db_free_result']($request);
	}
	removeTopics($topics, false, true);
}

?>