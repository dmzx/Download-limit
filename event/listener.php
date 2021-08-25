<?php
/**
*
* @package phpBB Extension - Download Limit
* @copyright (c) 2019 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlimit\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\config\config;
use phpbb\template\template;
use phpbb\user;
use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;
use phpbb\config\db_text;

class listener implements EventSubscriberInterface
{
	/** @var config */
	protected $config;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var driver_interface */
	protected $db;

	/** @var request_interface */
	protected $request;

	/** @var db_text */
	protected $config_text;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	* The database tables
	*
	* @var string
	*/
	protected $downloadlimit_table;

	/**
	* Constructor
	*
	* @param config					$config
	* @param template				$template
	* @param user					$user
	* @param driver_interface		$db
	* @param request_interface		$request
	* @param db_text				$config_text
	* @param string					$root_path
	* @param string					$php_ext
	* @param string			 		$downloadlimit_table
	*
	*/
	public function __construct(
		config $config,
		template $template,
		user $user,
		driver_interface $db,
		request_interface $request,
		db_text $config_text,
		$root_path,
		$php_ext,
		$downloadlimit_table
	)
	{
		$this->config 					= $config;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->db 						= $db;
		$this->request 					= $request;
		$this->config_text 				= $config_text;
		$this->root_path 				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->downloadlimit_table 		= $downloadlimit_table;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'								=> 'load_language_on_setup',
			'core.parse_attachments_modify_template_data' 	=> 'parse_attachments_modify_template_data',
			'core.download_file_send_to_browser_before'		=> 'download_file_send_to_browser_before',
			'core.memberlist_view_profile'					=> 'memberlist_view_profile',
			'core.viewtopic_get_post_data'					=> 'viewtopic_get_post_data',
		);
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'dmzx/downloadlimit',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function parse_attachments_modify_template_data($event)
	{
		$block_array = $event['block_array'];

		if ($this->get_dlrecordcount() >= $this->config['downloadlimit_posts'] && in_array($event['attachment']['extension'], $this->allowed_extensions()))
		{
			$block_array['S_FILE'] = false;

			$this->template->assign_vars([
				'S_FILE_NO' 			=> true,
				'DOWNLOADLIMIT_MESSAGE' => $this->user->lang('DOWNLOADLIMIT_MESSAGE', $this->config['downloadlimit_posts'], ($this->config['downloadlimit_gc'] / 3600)),
			]);
		}
		else
		{
			$this->template->assign_vars([
				'S_FILE_NO' 			=> false,
			]);
		}
		$event['block_array'] = $block_array;
	}

	public function download_file_send_to_browser_before($event)
	{
		if ($this->user->data['is_registered'] && in_array($event['attachment']['extension'], $this->allowed_extensions()))
		{
			$sql = 'SELECT u.*
				FROM ' . USERS_TABLE . ' u
				WHERE ' . $this->db->sql_in_set('group_id', explode(',', $this->config['downloadlimit_group_exceptions']), true) . '
				AND user_id = ' . (int) $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$allow_users = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($allow_users)
			{
				$attach_id = $this->request->variable('id', 0);

				$sql = 'SELECT *
					FROM ' . $this->downloadlimit_table . '
					WHERE user_id = ' . (int) $this->user->data['user_id'] . '
						AND file_id = ' . (int) $attach_id;
				$result = $this->db->sql_query($sql);
				$dlrecord = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$dlrecord)
				{
					$sql_ary = [
						'user_id'					=> $this->user->data['user_id'],
						'file_id'					=> $attach_id,
						'downloadslog_counter_user'	=> 1,
						'down_date'					=> time(),
					];

					$sql = 'INSERT INTO ' . $this->downloadlimit_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
					$this->db->sql_query($sql);
				}
				else
				{
					$downloadslog_counter_user = $dlrecord['downloadslog_counter_user'] + 1;

					$sql_ary = [
						'downloadslog_counter_user'	=> $downloadslog_counter_user,
						'down_date'					=> time(),
					];

					$sql_insert = 'UPDATE ' . $this->downloadlimit_table . '
						SET	' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE user_id = ' . (int) $this->user->data['user_id'] . '
							AND file_id = ' . (int) $attach_id;
					$this->db->sql_query($sql_insert);
				}
			}

			if (($this->get_dlrecordcount() - 1) >= $this->config['downloadlimit_posts'])
			{
				$message = $this->user->lang('DOWNLOADLIMIT_MESSAGE', $this->config['downloadlimit_posts'], ($this->config['downloadlimit_gc'] / 3600)) . '<br><br><a href="' . append_sid("{$this->root_path}index.{$this->php_ext}") . '">&laquo; ' . $this->user->lang['DOWNLOADLIMIT_RETURN_INDEX'] . '</a>';
				trigger_error($message);
			}
		}
	}

	public function memberlist_view_profile($event)
	{
		if ($this->config['downloadlimit_allow'] && ($this->get_dlrecordcount() > 0))
		{
			$this->template->assign_vars([
				'S_DOWNLOADLIMIT_MESSAGE_TRUE'	=> true,
				'DOWNLOADLIMIT_MESSAGE'			=> $this->user->lang('DOWNLOADLIMIT_COUNTS', $this->get_dlrecordcount(), ($this->config['downloadlimit_gc'] / 3600)),
			]);
		}
	}

	public function viewtopic_get_post_data($event)
	{
		if ($this->config['downloadlimit_allow'] && ($this->get_dlrecordcount() > 0))
		{
			if ($this->get_dlrecordcount() >= $this->config['downloadlimit_posts'])
			{
				$this->template->assign_vars([
					'S_DOWNLOADLIMIT_MESSAGE_REACHED'	=> true,
					'S_DOWNLOADLIMIT_MESSAGE_TRUE'		=> false,
				]);
			}
			else
			{
				$this->template->assign_vars([
					'S_DOWNLOADLIMIT_MESSAGE_TRUE'	=> true,
					'DOWNLOADLIMIT_MESSAGE'			=> $this->user->lang('DOWNLOADLIMIT_COUNTS', $this->get_dlrecordcount(), ($this->config['downloadlimit_gc'] / 3600)),
				]);
			}
		}
	}

	private function get_dlrecordcount()
	{
		$sql = 'SELECT u.*
			FROM ' . USERS_TABLE . ' u
			WHERE ' . $this->db->sql_in_set('group_id', explode(',', $this->config['downloadlimit_group_exceptions']), true) . '
			AND user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$allow_users = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($allow_users)
		{
			$sql = 'SELECT SUM(downloadslog_counter_user) AS dlrecordcount
				FROM ' . $this->downloadlimit_table . '
				WHERE user_id = ' . (int) $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$dlrecordcount = (int) $this->db->sql_fetchfield('dlrecordcount');
			$this->db->sql_freeresult($result);

			return $dlrecordcount;
		}
		else
		{
			return null;
		}
	}

	private function allowed_extensions()
	{
		$allowed_extensions = [];

		$allowed_extensions_list = $this->config_text->get_array([
			'downloadlimit_ext',
		]);

		$allowed_extensions = explode(',', $allowed_extensions_list['downloadlimit_ext']);

		return $allowed_extensions;
	}
}
