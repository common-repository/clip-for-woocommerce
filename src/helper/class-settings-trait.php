<?php
/**
 * Class SettingsTrait
 *
 * @package  Ecomerciar\Clip\Helper\SettingsTrait
 */

namespace Ecomerciar\Clip\Helper;

/**
 * Settings Trait
 */
trait SettingsTrait {

	/**
	 * Gets a plugin option
	 *
	 * @param string  $key Key value searching for.
	 * @param boolean $default A dafault value in case Key is not founded.
	 *
	 * @return mixed
	 */
	public static function get_option( string $key, $default = false ) {
		return isset( self::get_options()[ $key ] ) &&
			! empty( self::get_options()[ $key ] )
			? self::get_options()[ $key ]
			: $default;
	}

	/**
	 * Get options
	 *
	 * @param string $gateway Gateway Name.
	 *
	 * @return Array
	 */
	public static function get_options( $gateway = 'wc_clip' ) {
		$option = get_option( 'woocommerce_' . $gateway . '_settings' );
		return array(
			'enabled'                 => isset( $option['enabled'] ) ? $option['enabled'] : 'no',
			'title'                   => isset( $option['title'] ) ? $option['title'] : __( 'Pay with Clip', 'clip' ),
			'description'             => isset( $option['description'] ) ? $option['description'] : __( 'Accept payments using Clip.', 'clip' ),

			'api_key'                 => isset( $option['wc_clip_api_key'] )
				? $option['wc_clip_api_key']
				: '',
				
			'api_secret'              => isset( $option['wc_clip_api_secret'] )
				? $option['wc_clip_api_secret']
				: '',

			'wc_clip_payment_options' => isset( $option['wc_clip_payment_options'] )
			? $option['wc_clip_payment_options']
			: 'ocash',
			
			'wc_clip_expiration_hours'  => isset( $option['wc_clip_expiration_hours'] )
				? $option['wc_clip_expiration_hours']
				: '',

			'wc_clip_banner_enabled'  => isset( $option['wc_clip_banner_enabled'] )
			? $option['wc_clip_banner_enabled']
			: 'no',

			'debug'                   => isset( $option['wc_clip_log_enabled'] )
				? $option['wc_clip_log_enabled']
				: 'no',
		);
	}

	/**
	 * Set options
	 *
	 * @param string $key Key value searching for.
	 * @param string $value A value to be setted.
	 * @param string $gateway Gateway Name.
	 */
	public static function set_option( string $key, string $value, string $gateway = 'wc_clip' ) {
		$option                      = get_option( 'woocommerce_' . $gateway . '_settings' );
		$option[ 'wc_clip_' . $key ] = $value;
		update_option( 'woocommerce_' . $gateway . '_settings', $option );
	}

}
