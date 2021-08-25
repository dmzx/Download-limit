<?php
/**
*
* @package phpBB Extension - Download Limit
* @copyright (c) 2019 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlimit\cron;

use phpbb\config\config;
use phpbb\cron\task\base;
use phpbb\db\driver\driver_interface;
use phpbb\log\log_interface;
use phpbb\user;

class downloadlimit_prune extends base
{
	/** @var user */
	protected $user;

	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var log_interface */
	protected $log;

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
	 * @param user				$user
	 * @param config			$config
	 * @param driver_interface	$db
	 * @param log_interface		$log
	 * @param string			$root_path
	 * @param string			$php_ext
	 * @param config			$config
	 * @param string			$downloadlimit_table
	 */
	public function __construct(
		user $user,
		config $config,
		driver_interface $db,
		log_interface $log,
		$root_path,
		$php_ext,
		$downloadlimit_table
	)
	{
		$this->user						= $user;
		$this->config					= $config;
		$this->db						= $db;
		$this->log						= $log;
		$this->root_path				= $root_path;
		$this->php_ext					= $php_ext;
		$this->downloadlimit_table 		= $downloadlimit_table;
	}

	public function run()
	{
		$inactive_time = time() - $this->config['downloadlimit_gc'];

		$sql = 'SELECT u.*
			FROM ' . USERS_TABLE . ' u
			WHERE ' . $this->db->sql_in_set('group_id', explode(',', $this->config['downloadlimit_group_exceptions']), true) . '
			AND u.user_id <> ' . ANONYMOUS . '
			AND u.user_type = ' . USER_NORMAL;
		$results = $this->db->sql_query($sql);

		$allow_users = [];

		while ($row = $this->db->sql_fetchrow($results))
		{
			$allow_users[(int) $row['user_id']] = $row['username'];
		}
		$this->db->sql_freeresult($results);

		if ($allow_users)
		{
			$sql = 'DELETE FROM ' . $this->downloadlimit_table . '
				WHERE down_date < ' . (int) $inactive_time;
			$result = $this->db->sql_query($sql);
			$this->db->sql_freeresult($result);
		}
		$this->config->set('downloadlimit_last_gc', time());
	}

	public function is_runnable()
	{
		return $this->config['downloadlimit_allow'];
	}

	public function should_run()
	{
		return $this->config['downloadlimit_last_gc'] < time() - $this->config['downloadlimit_gc'];
	}
}
