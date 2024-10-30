<?php
/**
 * Class ValidationsTrait
 *
 * @package  Ecomerciar\Clip\Helper\ValidationsTrait
 */

namespace Ecomerciar\Clip\Helper;

use Ecomerciar\Clip\Sdk\ClipSdk;
use Ecomerciar\Clip\Gateway\WC_Clip;
/**
 * Validations Trait
 */
trait ValidationsTrait {

	/**
	 * Validate HTML
	 *
	 * @param bool   $bool Value to test.
	 * @param string $message_ok Message to show if $bool is true.
	 * @param string $message_error Message to show if $bool is false.
	 *
	 * @return Array
	 */
	public static function validate_html( $bool, $message_ok, $message_error ) {
		return $bool
			? self::VALIDATION_OK_ICON . $message_ok
			: self::VALIDATION_ERROR_ICON . $message_error;
	}

	/**
	 * Validate Credentials
	 *
	 * @return bool
	 */
	public static function validate_credentials() {

		$sdk = new ClipSdk(
			self::get_option( 'api_key' ),
			self::get_option( 'api_secret' )
		);

		$firstOnboarding = get_option( 'clip_first_onboarding', false );

		if ( ! $firstOnboarding ) {
			$ret = $sdk->request_first_deposit();
			update_option( 'clip_first_onboarding', $ret );
			return $ret;
		} else {
			return $sdk->validate_receipt();
		}

	}

	/**
	 * Validate Credentials HTML
	 *
	 * @return bool
	 */
	public static function validate_credentials_html() {
		return self::validate_html(
			self::validate_credentials(),
			__( 'Valid credentials.', 'clip' ),
			__( 'Invalid credentials.', 'clip' )
		);
	}

	/**
	 * Validations All Html (print erros)
	 *
	 * @return string
	 */
	public static function validate_all_html() {
		global $current_section;
		if ( 'wc_clip' === $current_section ) {
			return '<p>' . self::validate_credentials_html() . '</p>';
		}
		return '';
	}
}
