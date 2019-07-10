<?php
/**
*
* @package phpBB Extension - Download Limit
* @copyright (c) 2019 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'DOWNLOADLIMIT_ALLOW'						=> 'Enable Download limit',
	'DOWNLOADLIMIT_ALLOW_EXPLAIN'				=> 'If this option is set to Yes, cron on users will be On.',
	'DOWNLOADLIMIT_TIME_VALUE'					=> 'Set time period for prune/delete users',
	'DOWNLOADLIMIT_TIME_VALUE_EXPLAIN'			=> 'This option will set the prune/delete timer. Default is 24 hours.',
	'DOWNLOADLIMIT_HOURS'	=> array(
		1 => 'Hour',
		2 => 'Hours',
	),
	'DOWNLOADLIMIT_POSTS'						=> 'Set download count',
	'DOWNLOADLIMIT_POSTS_EXPLAIN'				=> 'Set download count for users. Default is 5.',
	'DOWNLOADLIMIT_SAVED'						=> 'Download limit settings saved.',
	'DOWNLOADLIMIT_GROUP_EXCEPTIONS'			=> 'Group exception(s)',
	'DOWNLOADLIMIT_GROUP_EXCEPTIONS_EXPLAIN'	=> 'Exclude the groups here that not will be pruned.<br />Select multiple groups by holding <samp>CTRL</samp> (or <samp>&#8984;CMD</samp> on Mac) and clicking.',
	'DOWNLOADLIMIT_EXT'							=> 'Allowed extensions',
	'DOWNLOADLIMIT_EXT_EXPLAIN'					=> 'Allowed extensions to include, separated by a comma (Example: 7z,ace,bz2,gtar,gz,rar,tar,tgz,torrent,zip)',
]);
