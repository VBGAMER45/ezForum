<?php
/**
 * I release this mod and all the code in it to anyone who wants to use it in hopes that it may be found useful.  Attribution is not necessary,
 * nor do you need to provide a link back to my website.  You are free to use this mod for any purpose, including commercial works,
 * redistribution and derivative works.  The only contingency is that the link back to the LED icon set (http://led24.de/iconset/) must remain, as this mod uses two icons
 * from that set. Namely comment_delete.png and comment_edit.png. Both images are found in the root directory of the package. All other
 * images are made by myself and can be used freely. Lastly, this mod comes with no guarantees that it will work well on all servers and
 * configurations, and I will not be held responsible for damages, expenses or problems that may have been caused by the mod's use.
**/
if (!defined('SMF'))
	exit;

class profile_ajax
{

	private $context;
	private $settings;
	private $txt;
	private $db;
	private $user;
	
	public function __construct()
	{
		global $context, $user_info, $settings, $txt, $smcFunc;
		
		$this->context = $context;
		$this->settings = $settings;
		$this->txt = $txt;
		$this->db = $smcFunc;
		$this->user = $user_info;
			
		$this->actions = array(
			'add_comment',
			'delete_comment',
			'update_comment',
			'return_comment_data',
			'load_new_comments',
			'load_older'
		);
	}
	
	public function init()
	{
		if (empty($_GET['profile_ajax']))
			return false;
		
		$action = $_GET['profile_ajax'];
		if (in_array($action, $this->actions))
			call_user_func(array($this, $action));
		else
			echo 'invalid_action';
		
		exit();
	}
	
	private function load_new_comments()
	{
		global $memberContext, $sourcedir;
		require_once($sourcedir . '/Security.php');
		
		$most_recent = isset($_POST['last_id']) ? (int)$_POST['last_id'] : 0;
		$pid = isset($_POST['profile_id']) ? (int)$_POST['profile_id'] : 0;
		
		if ($this->context['user']['is_logged'] && ($pid > 0))
		{
			$q = $this->db['db_query']('', '
				SELECT comment_id, comment_poster_id, comment_poster, comment_title, comment_body
				FROM {db_prefix}profile_comments
				WHERE comment_profile = {int:profile_id}
					AND comment_id > {int:most_recent}
				ORDER BY comment_id DESC
				LIMIT 25',
				array ('profile_id' => $pid, 'most_recent' => $most_recent)
			);
			
			while ($row = $this->db['db_fetch_assoc']($q))
			{
				if (!isset($memberContext[$row['comment_poster_id']]))
				{
					loadMemberData(array($row['comment_poster_id']), false, 'normal');
					loadMemberContext($row['comment_poster_id']);
				}
				$new['comments'][] = array(
					'id' => $row['comment_id'],
					'poster_id' => $row['comment_poster_id'],
					'poster_name' => $row['comment_poster'],
					'poster_avatar' => !empty($memberContext[$row['comment_poster_id']]['avatar']['href']) ? $memberContext[$row['comment_poster_id']]['avatar']['href'] : 'http://www.gravatar.com/avatar/' . md5(strtolower($this->context['user']['email'])) . '?r=G&d=mm&s=85',
					'title' => $row['comment_title'],
					'body' => parse_bbc($row['comment_body']),
					'can_modify' => allowedTo('pc_can_modify_any') || (($this->user['id'] == $row['comment_poster_id']) && (allowedTo('pc_can_modify_own'))) ? true : false,
					'can_delete' => allowedTo('pc_can_delete_any') || (($this->user['id'] == $row['comment_poster_id']) && (allowedTo('pc_can_delete_own'))) ? true : false
				);
			}
			
			$new['latest_post'] = (int) $new['comments'][0]['id'];
			echo json_encode($new);
		}
	}
	
	private function load_older()
	{
		global $memberContext;
		
		if (!isset($_POST['starting_point'], $_POST['profile']))
			return false;

		$start = (int) $_POST['starting_point'];
		$pid = (int) $_POST['profile'];

		if ($this->context['user']['is_logged'])
		{
			$query = $this->db['db_query']('', '
				SELECT comment_id, comment_poster_id, comment_poster, comment_title, comment_body
				FROM {db_prefix}profile_comments
				WHERE comment_profile = {int:profile_id}
					AND comment_id < {int:start}
				ORDER BY comment_id DESC
				LIMIT 20',
				array(
					'profile_id' => $pid,
					'start' => $start
				)
			);
		
			$response = array();
			$response['comments'] = array();
			
			while ($row = $this->db['db_fetch_assoc']($query))
			{
				if (!isset($memberContext[$row['comment_poster_id']]))
				{
					loadMemberData(array($row['comment_poster_id']), false, 'normal');
					loadMemberContext($row['comment_poster_id']);
				}
					
				$response['comments'][] = array(
					'id' => $row['comment_id'],
					'poster_id' => $row['comment_poster_id'],
					'poster_name' => $row['comment_poster'],
					'poster_avatar' => !empty($memberContext[$row['comment_poster_id']]['avatar']['href']) ? $memberContext[$row['comment_poster_id']]['avatar']['href'] : 'http://www.gravatar.com/avatar/' . md5(strtolower($this->context['user']['email'])) . '?r=G&d=mm&s=85',
					'title' => $row['comment_title'],
					'body' => parse_bbc($row['comment_body']),
					'can_modify' => allowedTo('pc_can_modify_any') || (($this->user['id'] == $row['comment_poster_id']) && (allowedTo('pc_can_modify_own'))) ? true : false,
					'can_delete' => allowedTo('pc_can_delete_any') || (($this->user['id'] == $row['comment_poster_id']) && (allowedTo('pc_can_delete_own'))) ? true : false
				);
			}
			
			$response['total'] = count($response['comments']);
			
			if (!empty($response['comments']))
			{
				$response['status'] = 'loaded';
				$response['oldest'] = end($response['comments']);
			}
			
			else
				$response['status'] = 'no_more';
		}
		else
			$response['status'] = 'permission_denied';
		
		echo json_encode($response);
	}
	
	private function add_comment()
	{
		if (!isset($_POST['c_title'], $_POST['c_body'], $_POST['profile_id']))
			return false;
			
		$title = (string) $this->db['htmltrim']($this->db['htmlspecialchars']($_POST['c_title'], ENT_QUOTES));
		$body = (string) $this->db['htmltrim']($this->db['htmlspecialchars']($_POST['c_body'], ENT_QUOTES));
		$id = (int) $_POST['profile_id'];
		
		if (empty($title) || empty($body) || $id <= 0)
			exit ('empty_value');
		
		if (allowedTo('pc_can_comment'))
		{
			checkSession('post');
			
			$this->db['db_insert']('insert', '{db_prefix}profile_comments',
				array(
					'comment_profile' => 'int',
					'comment_poster_id' => 'int',
					'comment_poster' => 'string',
					'comment_title' => 'string',
					'comment_body' => 'string'
				),
				array(
					$id,
					$this->user['id'],
					$this->user['name'],
					$title,
					$body
				),
				array(
					'comment_id'
				)
			);
			echo 'comment_added';
		}
		else
			echo 'permission_denied';
	}
	
	private function delete_comment()
	{
		if (empty($_POST['cid']))
			return false;
		
		if ($this->context['user']['is_logged'])
		{
			checkSession('post');
			
			$cid = (int) $_POST['cid'];
			
			$query = $this->db['db_query']('', '
				SELECT comment_poster_id
				FROM {db_prefix}profile_comments
				WHERE comment_id = {int:cid}
				LIMIT 1',
				array('cid' => $cid)
			);
			
			while ($row = $this->db['db_fetch_row']($query))
				$posterid = $row[0];
			
			if (allowedTo('pc_can_delete_any') || ($posterid == $this->user['id'] && allowedTo('pc_can_delete_own')))
			{
				$this->db['db_query']('', '
					DELETE FROM {db_prefix}profile_comments
					WHERE comment_id = {int:cid}',
					array('cid' => $cid)
				);
				echo 'deleted';
			}
			else
				echo 'permission_denied';
		}
		else
			echo 'not_logged';
	}
	
	private function update_comment()
	{
		global $member_context;
		
		if (!isset($_POST['comment_id'], $_POST['comment_title'], $_POST['comment_body']))
			return false;
			
		$id = (int) $_POST['comment_id'];
		$title = (string) $this->db['htmltrim']($this->db['htmlspecialchars']($_POST['comment_title'], ENT_QUOTES));
		$body = (string) $this->db['htmltrim']($this->db['htmlspecialchars']($_POST['comment_body'], ENT_QUOTES));
		
		if (empty($title) || empty($body) || $id < 0)
			return false;
		
		if ($this->context['user']['is_logged'])
		{
			checkSession('post');
			
			$query = $this->db['db_query']('', '
				SELECT comment_poster_id
				FROM {db_prefix}profile_comments
				WHERE comment_id = {int:comment_id}
				LIMIT 1',
				array('comment_id' => $id)
			);
			
			while ($row = $this->db['db_fetch_assoc']($query))
			{
				$comment = array(
					'id' => $id,
					'poster_id' => $row['comment_poster_id'],
					'title' => $title,
					'body' => parse_bbc($body)
				);
			}
				
			if (($this->user['id'] == $comment['poster_id'] && allowedTo('pc_can_modify_own')) || allowedTo('pc_can_modify_any'))
			{
				$this->db['db_query']('', '
					UPDATE {db_prefix}profile_comments
					SET comment_title = {string:comment_title}, comment_body = {string:comment_body}
					WHERE comment_id = {int:comment_id}',
					array(
						'comment_id' => $id,
						'comment_title' => $title,
						'comment_body' => $body
					)
				);
				
				$response = array(
					'status' => 'updated',
					'data' => $comment
				);
			}
			else
			{
				$response = array(
					'status' => 'permission_denied',
				);
			}
		}
		else
		{
			$response = array(
				'status' => 'not_logged',
			);
		}
		
		echo json_encode($response);
		return true;
	}
	
	private function return_comment_data()
	{
		if (!isset($_POST['id']) || $_POST['id'] <= 0)
			return false;

		$id = (int) $_POST['id'];
		$bbc = !empty($_POST['bbc']) ? true : false;
		
		$query = $this->db['db_query']('', '
			SELECT comment_poster_id
			FROM {db_prefix}profile_comments
			WHERE comment_id = {int:comment_id}
			LIMIT 1',
			array('comment_id' => $id)
		);
		
		while ($row = $this->db['db_fetch_row']($query))
			$creator_id = (int) $row[0];
		
		if (($this->user['id'] == $creator_id && allowedTo('pc_can_modify_own')) || allowedTo('pc_can_modify_any'))
		{
			$return = $this->db['db_query']('', '
				SELECT comment_id, comment_title, comment_body
				FROM {db_prefix}profile_comments
				WHERE comment_id= {int:comment_id}
				LIMIT 1',
				array('comment_id' => $id)
			);
			
			while ($comment_info = $this->db['db_fetch_assoc']($return))
			{
				$data = array(
					'id' => $comment_info['comment_id'],
					'title' => $comment_info['comment_title'],
					'body' => $bbc === true ? parse_bbc($comment_info['comment_body']) : $comment_info['comment_body']
				);
			}
			
			echo json_encode($data);
		}
		else
			echo json_encode('permission_denied');
		
		return true;
	}
}

$ajax = new profile_ajax;
$ajax->init();