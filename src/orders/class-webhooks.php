<?php
/**
 * Class Webhooks
 *
 * @package  Ecomerciar\Clip\Orders\Webhooks
 */

namespace Ecomerciar\Clip\Orders;

use Ecomerciar\Clip\Helper\Helper;
use Ecomerciar\Clip\Sdk\ClipSdk;
use Ecomerciar\Clip\Gateway\WC_Clip;

defined( 'ABSPATH' ) || exit();

/**
 * WebHook's base Class
 */
class Webhooks {

	const OK    = 'HTTP/1.1 200 OK';
	const ERROR = 'HTTP/1.1 500 ERROR';

	/**
	 * Receives the webhook and check if it's valid to proceed
	 *
	 * @param string $data Webhook json Data for testing purpouses.
	 *
	 * @return bool
	 */
	public static function listener( string $data = null ) {

		// Takes raw data from the request.
		if ( is_null( $data ) || empty( $data ) ) {
			$json = file_get_contents( 'php://input' );
		} else {
			$json = $data;
		}

		Helper::log( 'Webhook received' );
		Helper::log(
			__FUNCTION__ .
				__( '- Webhook received from Clip:', 'clip' ) .
				$json
		);

		$process = self::process_webhook( $json );

		if ( is_null( $data ) ) {
			// Real Webhook.
			if ( $process ) {
				if ( defined( 'TEST_CLIP_RUNNING' ) && TEST_CLIP_RUNNING ) {
					return true;
				} else {
					header( self::OK );
				}
			} else {
				if ( defined( 'TEST_CLIP_RUNNING' ) && TEST_CLIP_RUNNING ) {
					return false;
				} else {
					header( self::ERROR );
					wp_die(
						esc_html__( 'WooCommerce Clip Webhook not valid.', 'clip' ),
						'Clip Webhook',
						array( 'response' => 500 )
					);
				}
			}
		} else {
			// For testing purpouse.
			return $process;
		}
	}


	/**
	 * Process Webhook
	 *
	 * @param json $json Webhook data for.
	 *
	 * @return bool
	 */
	public static function process_webhook( $json ) {
		// Converts it into a PHP object.
		$data = json_decode( $json, true );

		if ( self::validate_input( $data ) ) {
			$order_id = self::get_order_id( $data );
			return Helper::handle_payment( $order_id );
		} else {
			return false;
		}

	}


	/**
	 * Get Order Id from Data Json
	 *
	 * @param array $data Webhook data.
	 *
	 * @return int
	 */
	private static function get_order_id( array $data ) {
		if ( isset( $data['payment_request_id'] ) ) {
			$clip_id = filter_var( $data['payment_request_id'], FILTER_SANITIZE_STRING );
			return Helper::find_order_by_itemmeta_value(
				\Clip::META_ORDER_PAYMENT_ID,
				$clip_id
			);
		}

	}

	/**
	 * Validates the incoming webhook
	 *
	 * @param array $data Webhook data to be validated.
	 *
	 * @return bool
	 */
	private static function validate_input( array $data ) {
		$return = true;
		$data   = wp_unslash( $data );

		if ( ! isset( $data['payment_request_id'] ) || empty( $data['payment_request_id'] ) ) {
			Helper::log(
				__FUNCTION__ .
					__( '- Webhook received without payment_request_id.', 'clip' )
			);
			$return = false;
		}
		if ( ! isset( $data['resource_status'] ) || empty( $data['resource_status'] ) ) {
			Helper::log(
				__FUNCTION__ .
					__( '- Webhook received without status.', 'clip' )
			);
			$return = false;
		} else {
			Helper::log(
				__FUNCTION__ .

				sprintf(
					/* translators: %s: System Flag */
					__(
						'- Webhook received status: "%s" .',
						'clip'
					),
					esc_html( $data['resource_status'] )
				)
			);
		}

		if ( $return ) {
			/*Tiene Clip como medio de pago?*/
			$order_id = self::get_order_id( $data );
			if ( empty( $order_id ) || is_null( $order_id ) || ! is_int( $order_id ) ) {
				Helper::log(
					__FUNCTION__ .
						__(
							'- Webhook received without order related.',
							'clip'
						)
				);
				$return = false;
			}
		}
		return $return;
	}


}
