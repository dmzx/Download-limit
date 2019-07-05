<?php
/**
*
* @package phpBB Extension - Download Limit
* @copyright (c) 2019 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlimit\acp;

class acp_downloadlimit_info
{
	function module()
	{
		return [
			'filename'	=> '\dmzx\downloadlimit\acp\acp_downloadlimit_module',
			'title'		=> 'ACP_DOWNLOADLIMIT_TITLE',
			'modes'		=> [
				'settings'	=> ['title' => 'ACP_DOWNLOADLIMIT_SETTINGS', 'auth' => 'ext_dmzx/downloadlimit && acl_a_board', 'cat' => ['ACP_DOWNLOADLIMIT_TITLE']],
			],
		];
	}
}
