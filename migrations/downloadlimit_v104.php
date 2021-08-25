<?php
/**
*
* @package phpBB Extension - Download Limit
* @copyright (c) 2019 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlimit\migrations;

class downloadlimit_v104 extends \phpbb\db\migration\migration
{

	static public function depends_on()
	{
		return [
			'\dmzx\downloadlimit\migrations\downloadlimit_v103',
		];
	}

	public function update_data()
	{
		return [
			// Update config
			['config.update', ['downloadlimit_version', '1.0.4']],
		];
	}
}