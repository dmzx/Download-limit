<?php
/**
*
* @package phpBB Extension - Download Limit
* @copyright (c) 2019 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlimit\migrations;

class downloadlimit_v102 extends \phpbb\db\migration\migration
{

	static public function depends_on()
	{
		return [
			'\dmzx\downloadlimit\migrations\downloadlimit_v101',
		];
	}

	public function update_data()
	{
		return [
			// Update config
			['config.update', ['downloadlimit_version', '1.0.2']],
			// Add config_text
			['config_text.add',	['downloadlimit_ext', $this->downloadlimit_exts()]],
		];
	}

	private function downloadlimit_exts()
	{
		$downloadlimit_exts = [
			'7z','ace','bz2','gtar','gz','rar','tar','tgz','torrent','zip'
		];
		$downloadlimit_exts = implode(",", $downloadlimit_exts);

		return $downloadlimit_exts;
	}
}
