<?php
/**
 * Class CountryCurrencyTrait
 *
 * @package  Ecomerciar\Clip\Helper\DebugTrait
 */

namespace Ecomerciar\Clip\Helper;

/**
 * Database Trait
 */
trait CountryCurrencyTrait {

	/**
	 * Return the current transaction currency
	 * - Supports WOOCS currency switcher
	 */
	public static function get_currency() {
		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS;
			$currency = strtoupper( $WOOCS->storage->get_val( 'woocs_current_currency' ) );
		} else {
			$currency = get_woocommerce_currency();
		}
		return $currency;
	}
	/**
	 * Get list of countries
	 */
	public static function get_countries() {
		return array(
			'MX' => 'MXN',  // Mexico.
			'AR' => 'ARS',  // Argentina.
		);
	}

	/**
	 * Check if valid country
	 *
	 * @param string $country Country Code.
	 */
	public static function validate_country_code( $country ) {
		return isset( self::get_countries()[ $country ] );
	}

	/**
	 * Check if valid country/currency
	 *
	 * @param string $country Country Code.
	 * @param string $currency Currency Code.
	 */
	public static function validate_country_currency_code( $country, $currency ) {
		$ret = false;
		if ( self::validate_country_code( $country ) ) {
			$ret = ( self::get_countries()[ $country ] === $currency );
		}
		return $ret;
	}


}
