<?php
/**
 * Main Onboarding Class
 *
 * @package Ecomerciar\Clip\Onboarding
 */

namespace Ecomerciar\Clip\Onboarding;

use Ecomerciar\Clip\Helper\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Main Onboarding Class
 */
class Main {


	/**
	 * Register Onboarding Page
	 */
	public static function register_onboarding_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Clip', 'clip' ),
			__( 'Clip', 'clip' ),
			'manage_woocommerce',
			'wc-clip-onboarding',
			array( __CLASS__, 'content' )
		);
		return true;
	}

	/**
	 * Get content
	 */
	public static function content() {
		$data    = array();
		$siteUrl = get_site_url();
		$nonce   = wp_create_nonce( \Clip::GATEWAY_ID );

		$currentLanguage = get_bloginfo( 'language' );
		$language_slug   = '/en/';
		if ( 0 === strpos( $currentLanguage, 'es' ) ) {
			$language_slug = '/es/';
		}

		$frontUrl = \Clip::ONBOARDING_URL . $language_slug;

		$frontUrl = add_query_arg( 'ecommerce', 'woo', $frontUrl );
		$frontUrl = add_query_arg( 'wp-nonce', $nonce, $frontUrl );
		$frontUrl = add_query_arg( 'wp-base-url', $siteUrl, $frontUrl );

		$data['settings_url']     = $frontUrl;
		$data['woo_settings_url'] = esc_url( get_admin_url( null, 'admin.php?page=wc-settings&tab=checkout&section=wc_clip' ) );

		helper::get_template_part( 'page', 'onboarding', $data );
		return true;
	}

}
