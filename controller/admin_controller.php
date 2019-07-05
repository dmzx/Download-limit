<?php
/**
*
* @package phpBB Extension - Download Limit
* @copyright (c) 2019 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlimit\controller;

use phpbb\config\config;
use phpbb\template\template;
use phpbb\log\log_interface;
use phpbb\user;
use phpbb\request\request_interface;
use phpbb\db\driver\driver_interface;

class admin_controller
{
	/** @var config */
	protected $config;

	/** @var template */
	protected $template;

	/** @var log_interface */
	protected $log;

	/** @var user */
	protected $user;

	/** @var request_interface */
	protected $request;

	/** @var driver_interface */
	protected $db;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor
	 *
	 * @param config				$config
	 * @param template				$template
	 * @param log_interface			$log
	 * @param user					$user
	 * @param request_interface		$request
	 * @param driver_interface		$db
	 *
	 */
	public function __construct(
		config $config,
		template $template,
		log_interface $log,
		user $user,
		request_interface $request,
		driver_interface $db
	)
	{
		$this->config 			= $config;
		$this->template 		= $template;
		$this->log 				= $log;
		$this->user 			= $user;
		$this->request 			= $request;
		$this->db				= $db;
	}

	public function display_options()
	{
		add_form_key('acp_downloadlimit');

		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('acp_downloadlimit'))
			{
				trigger_error('FORM_INVALID');
			}

			// Set the options the user configured
			$this->set_options();

			// Add option settings change action to the admin log
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DOWNLOADLIMIT_SAVED');

			trigger_error($this->user->lang('DOWNLOADLIMIT_SAVED') . adm_back_link($this->u_action));
		}

		$sql = 'SELECT group_id, group_type, group_name
			FROM ' . GROUPS_TABLE;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);

		$downloadlimit_group_exceptions_options = '';

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['group_name'] != 'BOTS')
			{
				$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $this->user->lang['G_' . $row['group_name']] : $row['group_name'];

				if (in_array($row['group_id'], explode(',', $this->config['downloadlimit_group_exceptions'])))
				{
					$downloadlimit_group_exceptions_options .= '<option value="' . $row['group_id'] . '" selected="selected">' . $group_name . '</option>';
				}
				else
				{
					$downloadlimit_group_exceptions_options .= '<option value="' . $row['group_id'] . '">' . $group_name . '</option>';
				}
			}
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars(array(
			'U_ACTION'							=> $this->u_action,
			'DOWNLOADLIMIT_ALLOW'				=> $this->config['downloadlimit_allow'],
			'DOWNLOADLIMIT_GC'			 		=> $this->config['downloadlimit_gc'] / 3600,
			'DOWNLOADLIMIT_HOURS'				=> $this->user->lang('DOWNLOADLIMIT_HOURS', $this->config['downloadlimit_gc'] / 3600),
			'DOWNLOADLIMIT_POSTS'				=> $this->config['downloadlimit_posts'],
			'DOWNLOADLIMIT_VERSION'				=> $this->config['downloadlimit_version'],
			'DOWNLOADLIMIT_GROUP_EXCEPTIONS' 	=> $downloadlimit_group_exceptions_options,
		));
	}

	protected function set_options()
	{
		$downloadlimit_group_exceptions = $this->request->variable('downloadlimit_group_exceptions', array(0 => 0));

		$this->config->set('downloadlimit_allow', $this->request->variable('downloadlimit_allow', 1));
		$this->config->set('downloadlimit_gc', (int) $this->request->variable('downloadlimit_gc', 0) * 3600);
		$this->config->set('downloadlimit_posts', (int) $this->request->variable('downloadlimit_posts', ''));
		$this->config->set('downloadlimit_group_exceptions', implode(',' ,$downloadlimit_group_exceptions));
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
