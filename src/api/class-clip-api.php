<?php
/**
 * Class CLipApi
 *
 * @package  Ecomerciar\Clip\Api\ClipApi
 */

namespace Ecomerciar\Clip\Api;

use Ecomerciar\Clip\Helper\Helper;
defined( 'ABSPATH' ) || exit();
/**
 * Clip API Class
 */
class ClipApi extends ApiConnector implements ApiInterface {
	
	/**
	 * Class Constructor
	 *
	 * @param array $settings Clip Settings Object.
	 */
	public function __construct( array $settings = array() ) {
		$this->api_key    = $settings['api_key'];
		$this->api_secret = $settings['api_secret'];
		$this->debug      = $settings['debug'];
	}

	/**
	 *  Get Base Url
	 *
	 * @return String
	 */
	public function get_base_url() {
		return \Clip::API_BASE_URL;
	}
}
