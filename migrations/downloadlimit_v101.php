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

class downloadlimit_v101 extends migration
{

	static public function depends_on()
	{
		return [
			'\dmzx\downloadlimit\migrations\downloadlimit_install',
		];
	}

	public function update_data()
	{
		return [
			// Update config
			['config.update', ['downloadlimit_version', '1.0.1']],
		];
	}
}
