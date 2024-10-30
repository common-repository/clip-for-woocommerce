<?php
/**
 * Class Request Deposit Action
 *
 * @package  Ecomerciar\Clip\Gateway\RequestDepositAction
 */

namespace Ecomerciar\Clip\Gateway;

use Ecomerciar\Clip\Helper\Helper;
use Ecomerciar\Clip\Sdk\ClipSdk;
use Ecomerciar\Clip\Gateway\WC_Clip;
/**
 * Orders Base Action Class
 */
abstract class RequestDepositAction {

	/**
	 * Run Action
	 *
	 * @param int $order_id ID for WC Order.
	 *
	 * @return array
	 */
	public static function run( $order_id ) {

		$order = wc_get_order( $order_id );

		$options  = Helper::get_options( \Clip::GATEWAY_ID );
		$sdk      = new ClipSdk( $options['api_key'], $options['api_secret'] );
		$response = $sdk->request_deposit( $order_id );
		if ( isset( $response['status'] ) && 'CHECKOUT_CREATED' === $response['status'] ) {
			$order->add_order_note(
				sprintf(
					/* translators: %s: System Flag */
					esc_html__( 'Clip payment_request_url created. ID %s', 'clip' ),
					$response['payment_request_url']
				)
			);
			$order->add_order_note(
				sprintf(
					/* translators: %s: System Flag */
					esc_html__( 'Clip payment_request_id created. ID %s', 'clip' ),
					$response['payment_request_id']
				)
			);
			$order->update_meta_data(
				\Clip::META_ORDER_PAYMENT_ID,
				$response['payment_request_id']
			);
			$order->save();
		} else {
			if ( isset( $response['code_message'] ) && '004' === $response['code_message'] ) {
				$order->add_order_note(
					sprintf(
						esc_html__( 'Clip: Requested maximum payment limit reached.', 'clip' )
					)
				);

			} else {
				$order->add_order_note(
					sprintf(
						esc_html__( 'Clip: It was not possible to create a payment_link.', 'clip' )
					)
				);

			}
		}

		$response = isset( $response['payment_request_url'] ) ? $response['payment_request_url'] : '';

		return $response;
	}

	/**
	 * Validates Post parameters for Ajax Request
	 *
	 * @return bool/string
	 */
	public static function validate_ajax_request() {
		$errorCd = '';
		if ( ! isset( $_POST['nonce'] ) ) {
			$errorCd = 'missing nonce';
		} else {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), \Clip::GATEWAY_ID ) ) {
				$errorCd = 'nonce';
			}
		}
		if ( ! isset( $_POST['order_id'] ) ) {
			$errorCd = 'missing order_id';
		} else {
			if ( empty( $_POST['order_id'] ) ) {
				$errorCd = 'order_id';
			}

			$order_id = filter_var( wp_unslash( $_POST['order_id'] ), FILTER_SANITIZE_NUMBER_INT );
			$order    = wc_get_order( $order_id );
			if ( ! $order ) {
				$errorCd = 'not order';
			}

			$payment_method = $order->get_payment_method();
			if ( empty( $payment_method ) ) {
				$errorCd = 'not payment method';
			}

			if ( \Clip::GATEWAY_ID !== $payment_method ) {
				$errorCd = 'not clip';
			}
		}

		if ( ! empty( $errorCd ) ) {
			return $errorCd;
		}

		return true;
	}















	/**
	 * Ajax Callback
	 */
	public static function ajax_callback_wp() {

		$ret_validate = static::validate_ajax_request();
		if ( true !== $ret_validate ) {
			if ( defined( 'TEST_CLIP_RUNNING' ) && TEST_CLIP_RUNNING ) {
				return false;
			} else {
				wp_send_json_error( $ret_validate );
			}
		}
		//phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['order_id'] ) ) {
			$order_id = filter_var( wp_unslash( $_POST['order_id'] ), FILTER_SANITIZE_NUMBER_INT );
		}
		$ret = static::run( $order_id );
		if ( $ret ) {
			if ( defined( 'TEST_CLIP_RUNNING' ) && TEST_CLIP_RUNNING ) {
				return true;
			} else {
				wp_send_json_success( $ret );
			}
		} else {
			if ( defined( 'TEST_CLIP_RUNNING' ) && TEST_CLIP_RUNNING ) {
				return false;
			} else {
				$res = __( 'Sorry, this order is invalid and cannot be paid. Contact customer service.', 'clip' );
				wp_send_json_error( $res );
			}
		}
		return false;
	}
}
