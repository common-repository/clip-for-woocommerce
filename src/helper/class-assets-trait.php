<?php
/**
 * Assets Trait
 *
 * @package Ecomerciar\Clip\Helper
 */

namespace Ecomerciar\Clip\Helper;

trait AssetsTrait {

	/**
	 * Gets Assets Folder URL
	 *
	 * @return string
	 */
	public static function get_assets_folder_url() {
		return plugin_dir_url( \Clip::MAIN_FILE ) . 'assets';
	}

}
