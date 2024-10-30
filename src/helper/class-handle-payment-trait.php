<?php
/**
 * HandlePayment Trait
 *
 * @package Ecomerciar\Clip\Helper
 */

namespace Ecomerciar\Clip\Helper;

use Ecomerciar\Clip\Sdk\ClipSdk;
use Ecomerciar\Clip\Gateway\WC_Clip;

trait HandlePaymentTrait {

	/**
	 * Gets Payment Response
	 *
	 * @param string $order_id Order Id Data.
	 * @param string $status Status for testing purpouses.
	 */
	public static function handle_payment( $order_id, $status = '' ) {
		if ( null !== $order_id ) {
			$order      = wc_get_order( $order_id );
			$order_data = $order->get_meta( '_CLIP_PAYMENT_ID' );
		}
		if ( isset( $order_data ) ) {

			$options    = Helper::get_options( \Clip::GATEWAY_ID );
			$sdk        = new ClipSdk( $options['api_key'], $options['api_secret'] );
			$response   = $sdk->get_payment_data( $order_data );
			$receipt_no = isset( $response[ 'receipt_no' ] )? $response[ 'receipt_no' ] : '';
			$prevStatus = $order->get_meta( '_CLIP_PAYMENT_STATUS' );
			if ( $prevStatus !== $response['status'] ) {

				if ( 'CHECKOUT_COMPLETED' === $response['status'] || 'CHECKOUT_COMPLETED' === $status ) {
					$order->payment_complete();
					if ( !empty( $receipt_no ) ) {
						$order->add_order_note(
							sprintf(
								/* translators: %s: System Flag */
								esc_html__( 'Clip - Approved Payment. Receipt: %s. ID https://receipt.clip.mx/%s', 'clip' ),
								// esc_html__( 'Clip - Approved Payment. Receipt: %s. ID https://stagereceipt.clip.mx/%s', 'clip' ),
								$receipt_no,
								$order_data
							)
						);
					} else {
						$order->add_order_note(
							sprintf(
								/* translators: %s: System Flag */
								esc_html__( 'Clip - Approved Payment. ID https://receipt.clip.mx/%s', 'clip' ),
								// esc_html__( 'Clip - Approved Payment. ID https://stagereceipt.clip.mx/%s', 'clip' ),
								$order_data
							)
						);
					}
				}
				if ( 'CHECKOUT_CANCELLED' === $response['status'] || 'CHECKOUT_CANCELLED' === $status ) {
					$order->add_order_note(
						sprintf(
							/* translators: %s: System Flag */
							esc_html__( 'Clip - Cancelled Payment. ID %s', 'clip' ),
							$order_data
						)
					);
				}
				if ( 'CHECKOUT_EXPIRED' === $response['status'] || 'CHECKOUT_EXPIRED' === $status ) {
					$order->add_order_note(
						sprintf(
							/* translators: %s: System Flag */
							esc_html__( 'Clip - Expired Payment. ID %s', 'clip' ),
							$order_data
						)
					);
				}

				if ( 'CHECKOUT_PENDING' === $response['status'] || 'CHECKOUT_PENDING' === $status ) {
					$order->add_order_note(
						sprintf(
							/* translators: %s: System Flag */
							esc_html__( 'Clip - Pending Payment. ID %s', 'clip' ),
							$order_data
						)
					);
				}

				$order->update_meta_data(
					\Clip::META_CLIP_RECEIPT_NO,
					$receipt_no
				);
				$order->update_meta_data(
					\Clip::META_CLIP_PAYMENT_STATUS,
					empty( $status ) ? $response['status'] : $status
				);
				$order->save();

				return true;
			}
		}
		return false;
	}

}
