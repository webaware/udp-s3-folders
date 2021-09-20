<?php
/*
Plugin Name: UDP S3 Folders
Update URI: udp-s3-folders
Description: add Amazon S3 path folders for different backups on UpdraftPlus
Version: 0.0.2
Author: WebAware
Author URI: https://shop.webaware.com.au/
*/

/*
copyright (c) 2021 WebAware Pty Ltd (email : support@webaware.com.au)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

namespace webawareau\udp_s3_folders;

use UpdraftPlus_BackupModule_s3;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * intercept UpdraftPlus backup options to modify the path based on conditions
 * @param array $options
 * @param UpdraftPlus_BackupModule $module
 * @return array
 */
add_filter('updraftplus_backupmodule_get_options', function($options, $module) {
	if ($module instanceof UpdraftPlus_BackupModule_s3) {
		// set folder to weekly if it's Sunday, else daily
		$folder = 0 === (int) date('w') ? 'weekly' : 'daily';

		// if running from wp-cli, check for specific backup labels which require their own folder
		if (is_wpcli()) {
			$label = get_wpcli_label();

			switch ($label) {

				case 'db':
				case 'longdb':
					$folder = $label;
					break;

			}
		}

		$options['path'] = rtrim($options['path'], '/') . "/$folder";
	}
	return $options;
}, 10, 2);

/**
 * get label option from wp-cli
 * @return string
 */
function get_wpcli_label() {
	global $argv;

	$label = '';

	foreach ($argv as $arg) {
		$arg = explode('=', $arg, 2);
		if (count($arg) === 2 && $arg[0] === '--label') {
			$label = $arg[1];
		}
	}

	return $label;
}

/**
 * check to see if running from wp-cli
 * @return bool
 */
function is_wpcli() {
	return defined('WP_CLI') && WP_CLI;
}
