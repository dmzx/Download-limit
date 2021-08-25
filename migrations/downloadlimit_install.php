<?php
/**
*
* @package phpBB Extension - Download Limit
* @copyright (c) 2019 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlimit\migrations;

use phpbb\db\migration\migration;

class downloadlimit_install extends migration
{
	public function update_data()
	{
		return [
			// Add config
			['config.add', ['downloadlimit_version', '1.0.0']],
			['config.add', ['downloadlimit_allow', 0]],
			['config.add', ['downloadlimit_posts', 5]],
			['config.add', ['downloadlimit_gc', 86400]],
			['config.add', ['downloadlimit_last_gc', '0', true]],
			['config.add', ['downloadlimit_group_exceptions', '4, 5', '0']],

			// ACP module
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_DOWNLOADLIMIT_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_DOWNLOADLIMIT_TITLE',
				[
					'module_basename'	=> '\dmzx\downloadlimit\acp\acp_downloadlimit_module',
				],
			]],
		];
	}

	public function update_schema()
	{
		return [
			'add_tables'	=> [
				$this->table_prefix . 'downloadlimit'	=> [
					'COLUMNS'	=> [
					'downloadslog_id'			=> ['UINT', null, 'auto_increment'],
					'user_id'					=> ['VCHAR:8', ''],
					'file_id'					=> ['VCHAR:8', ''],
					'down_date'					=> ['INT:11', 0],
					'downloadslog_counter_user'	=> ['UINT', null],
				],
				'PRIMARY_KEY' => 'downloadslog_id',
			],
		]];
	}

	public function revert_schema()
	{
		return [
			'drop_tables'	=> [
				$this->table_prefix . 'downloadlimit',
			],
		];
	}
}
