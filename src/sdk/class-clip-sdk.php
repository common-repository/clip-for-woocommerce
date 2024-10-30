<?php
/**
 * Class ClipSdk
 *
 * @package  Ecomerciar\Clip\Helper\ClipSdk
 */

namespace Ecomerciar\Clip\Sdk;

use Ecomerciar\Clip\Api\ClipApi;
use Ecomerciar\Clip\Helper\Helper;

/**
 * Main Class Clip Sdk.
 */
class ClipSdk {

	/**
	 * Defines Clip API Key
	 *
	 * @var string $api_key
	 */
	private string $api_key;
	/**
	 * Defines Clip API Secret
	 *
	 * @var string $api_secret API Secret;
	 */
	private string $api_secret;
	/**
	 * Defines Clip API Token
	 *
	 * @var string $api_token API Token;
	 */
	private string $api_token;
	/**
	 * Defines Debug flag
	 *
	 * @var bool $debug Debug flag ;
	 */
	private bool $debug;

	const JSON = 'application/json';

	/**
	 * Constructor.
	 *
	 * @param string  $api_key Clip API Key.
	 * @param string  $api_secret Clip API Secret.
	 * @param boolean $debug Debug Switch.
	 */
	public function __construct(
		string $api_key,
		string $api_secret,
		bool $debug = false
	) {
		$this->api_key    = $api_key;
		$this->api_secret = $api_secret;
		$this->api        = new ClipApi(
			array(
				'api_key'    => $api_key,
				'api_secret' => $api_secret,
				'debug'      => $debug,
			)
		);
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$this->api_token = 'Basic ' . base64_encode( $api_key . ':' . $api_secret );
		$this->debug     = $debug;
	}



	/**
	 * Validate_receipt
	 *
	 * @return bool
	 */
	public function validate_receipt() {
		try {
			$res = $this->api->get(
				'/payments/receipt-no/5mUV5Dt',
				array(),
				array(
					'Authorization' => $this->api_token,
					'accept'        => 'application/vnd.com.payclip.v2+json',
				)
			);

		} catch ( \Exception $e ) {
			Helper::log_error( __FUNCTION__ . ': ' . $e->getMessage() );
			return array();
		}
		if ( ! empty( $this->handle_response( $res, __FUNCTION__ )['query'] ) ) {
			return true;
		}
		Helper::set_option( 'api_key', '' );
		Helper::set_option( 'api_secret', '' );
		return false;
	}

	/**
	 * Create dummy deposit just to validate credentias (only first time)
	 *
	 * @return array
	 */
	public function request_first_deposit() {

		$data_to_send = array(
			'amount'               => floatval( number_format( floatval( 3 ) , 2 , '.', '') ),
			'currency'             => 'MXN',
			'purchase_description' => 'Authenticate woocommerce',
			'redirection_url'      => array(
				'success' => get_site_url(),
				'error'   => get_site_url(),
				'default' => get_site_url(),
			),
			'metadata'             => array(
				'me_reference_id' => 'authenticate woocommerce',
				'customer_info'   => array(
					'name'  => 'Alejandro Lee',
					'email' => 'buyer@hotmail.com',
					'phone' => 5520686868,
				),
				'source'          => 'woocommerce',
			),
			'override_settings'    => array(
				'payment_method' => array( 'CASH', 'CARD' ),
			),
			'webhook_url'          => get_site_url() . '/wc-api/wc-clip',
		);

		try {
			$res = $this->api->post(
				'/checkout',
				$data_to_send,
				array(
					'content-type' => self::JSON,
					'accept'       => 'application/vnd.com.payclip.v2+json',
					'x-api-key'    => $this->api_token,
				)
			);
		} catch ( \Exception $e ) {
			Helper::log_error( __FUNCTION__ . ': ' . $e->getMessage() );
			return array();
		}
		return $this->handle_response( $res, __FUNCTION__ );
	}


		/**
		 * Create deposit
		 *
		 * @param int $order_id ID for WC Order.
		 *
		 * @return array
		 */
	public function request_deposit( $order_id ) {
		$order           = wc_get_order( $order_id );
		$payment_options = Helper::get_option( 'wc_clip_payment_options', false );
		$expiration = Helper::get_option('wc_clip_expiration_hours', 72);


		$current_datetime = new \DateTime('now', new \DateTimeZone('UTC'));
		$new_datetime = $current_datetime->modify("+$expiration hours");
		$expiresAt = $new_datetime->format('Y-m-d\TH:i:s\Z');


		switch ( $payment_options ) {
			case 'CASH':
				$payment_options = array( 'CASH' );
				break;
			case 'CARD':
				$payment_options = array( 'CARD' );
				break;
			default:
				$payment_options = array( 'CASH', 'CARD' );
				break;
		};

		$billing_states  = WC()->countries->get_states( $order->get_billing_country() );
		$billing_state   = ! empty( $billing_states[ $order->get_billing_state() ] ) ? $billing_states[ $order->get_billing_state() ] : '';
		$billing_country = ! empty( WC()->countries->countries[ $order->get_billing_country() ] ) ? WC()->countries->countries[ $order->get_billing_country() ] : '';

		$shipping_states  = WC()->countries->get_states( $order->get_shipping_country() );
		$shipping_state   = ! empty( $shipping_states[ $order->get_shipping_state() ] ) ? $shipping_states[ $order->get_shipping_state() ] : '';
		$shipping_country = ! empty( WC()->countries->countries[ $order->get_shipping_country() ] ) ? WC()->countries->countries[ $order->get_shipping_country() ] : '';

		$data_to_send = array(
			'amount'               => floatval( number_format( floatval( $order->get_total() ) , 2 , '.', '') ) ,			
			'currency'             => $order->get_currency(),
			// Compra en tienda 'purchase_description' => __( 'Shop at ', 'clip' ) . get_bloginfo( 'name' ) . ' #' . $order_id,
			'purchase_description' => __( 'Compra en tienda', 'clip' ),
			'redirection_url'      => array(
				'success' => $order->get_checkout_order_received_url(),
				'error'   => $order->get_checkout_order_received_url(),
				'default' => $order->get_checkout_order_received_url(),
			),
			'expires_at'           => $expiresAt,
			'metadata'             => array(
				'me_reference_id'  => 'WC' . $order_id . '-' . wp_generate_uuid4(),
				'customer_info'    => array(
					'name'  => $order->get_billing_first_name(),
					'email' => $order->get_billing_email(),
					'phone' => $order->get_billing_phone(),
				),
				'source'           => 'woocommerce',
				'billing_address'  => array(
					'street'          => ! empty( $order->get_billing_address_1() ) ? $order->get_billing_address_1() : '',
					'outdoor_number'  => '',
					'interior_number' => '',
					'locality'        => '',
					'city'            => ! empty( $order->get_billing_city() ) ? $order->get_billing_city() : '',
					'state'           => $billing_state,
					'zip_code'        => ! empty( $order->get_billing_postcode() ) ? $order->get_billing_postcode() : '',
					'country'         => $billing_country,
					'reference'       => '',
					'between_streets' => '',
					'floor'           => '',
				),
				'shipping_address' => array(
					'street'          => ! empty( $order->get_shipping_address_1() ) ? $order->get_shipping_address_1() : '',
					'outdoor_number'  => '',
					'interior_number' => '',
					'locality'        => '',
					'city'            => ! empty( $order->get_shipping_city() ) ? $order->get_shipping_city() : '',
					'state'           => $shipping_state,
					'zip_code'        => ! empty( $order->get_shipping_postcode() ) ? $order->get_shipping_postcode() : '',
					'country'         => $shipping_country,
					'reference'       => '',
					'between_streets' => '',
					'floor'           => '',
				),

			),

			'override_settings'    => array(
				'payment_method' => $payment_options,
			),
			'webhook_url'          => get_site_url() . '/wc-api/wc-clip',
		);

		if ( empty( $data_to_send['billing_address']['street'] ) ) {
			unset( $data_to_send['billing_address']['street'] );
		}
		if ( empty( $data_to_send['billing_address']['outdoor_number'] ) ) {
			unset( $data_to_send['billing_address']['outdoor_number'] );
		}
		if ( empty( $data_to_send['billing_address']['locality'] ) ) {
			unset( $data_to_send['billing_address']['locality'] );
		}
		if ( empty( $data_to_send['billing_address']['city'] ) ) {
			unset( $data_to_send['billing_address']['city'] );
		}
		if ( empty( $data_to_send['billing_address']['state'] ) ) {
			unset( $data_to_send['billing_address']['state'] );
		}
		if ( empty( $data_to_send['billing_address']['zip_code'] ) ) {
			unset( $data_to_send['billing_address']['zip_code'] );
		}
		if ( empty( $data_to_send['billing_address']['country'] ) ) {
			unset( $data_to_send['billing_address']['country'] );
		}
		if ( empty( $data_to_send['billing_address']['reference'] ) ) {
			unset( $data_to_send['billing_address']['reference'] );
		}
		if ( empty( $data_to_send['billing_address']['between_streets'] ) ) {
			unset( $data_to_send['billing_address']['between_streets'] );
		}
		if ( empty( $data_to_send['billing_address']['floor'] ) ) {
			unset( $data_to_send['billing_address']['floor'] );
		}

		if ( $order->get_billing_address_1() !== $order->get_shipping_address_1() ) {

			if ( empty( $data_to_send['shipping_address']['street'] ) ) {
				unset( $data_to_send['shipping_address']['street'] );
			}
			if ( empty( $data_to_send['shipping_address']['outdoor_number'] ) ) {
				unset( $data_to_send['shipping_address']['outdoor_number'] );
			}
			if ( empty( $data_to_send['shipping_address']['locality'] ) ) {
				unset( $data_to_send['shipping_address']['locality'] );
			}
			if ( empty( $data_to_send['shipping_address']['city'] ) ) {
				unset( $data_to_send['shipping_address']['city'] );
			}
			if ( empty( $data_to_send['shipping_address']['state'] ) ) {
				unset( $data_to_send['shipping_address']['state'] );
			}
			if ( empty( $data_to_send['shipping_address']['zip_code'] ) ) {
				unset( $data_to_send['shipping_address']['zip_code'] );
			}
			if ( empty( $data_to_send['shipping_address']['country'] ) ) {
				unset( $data_to_send['shipping_address']['country'] );
			}
			if ( empty( $data_to_send['shipping_address']['reference'] ) ) {
				unset( $data_to_send['shipping_address']['reference'] );
			}
			if ( empty( $data_to_send['shipping_address']['between_streets'] ) ) {
				unset( $data_to_send['shipping_address']['between_streets'] );
			}
			if ( empty( $data_to_send['shipping_address']['floor'] ) ) {
				unset( $data_to_send['shipping_address']['floor'] );
			}
		} else {
			unset( $data_to_send['shipping_address'] );
		}

		try {
			$res = $this->api->post(
				'/checkout',
				$data_to_send,
				array(
					'content-type' => self::JSON,
					'accept'       => 'application/vnd.com.payclip.v2+json',
					'x-api-key'    => $this->api_token,
				)
			);
		} catch ( \Exception $e ) {
			Helper::log_error( __FUNCTION__ . ': ' . $e->getMessage() );
			return false;
		}

		return $this->handle_response( $res, __FUNCTION__ );
	}



		/**
		 * Get payment data
		 *
		 * @param string $payment_request_id Clip Payment Intention.
		 *
		 * @return array
		 */
	public function get_payment_data( $payment_request_id ) {
		try {
			$res = $this->api->get(
				'/checkout/' . $payment_request_id,
				array(),
				array(
					'content-type' => self::JSON,
					'accept'       => 'application/vnd.com.payclip.v2+json',
					'x-api-key'    => $this->api_token,
				)
			);
		} catch ( \Exception $e ) {
			Helper::log_error( __FUNCTION__ . ': ' . $e->getMessage() );
			return array();
		}
		return $this->handle_response( $res, __FUNCTION__ );
	}

	/**
	 * Request Refund
	 * 
	 */
	public function request_refund(string $id, float $amount, string $reason = ''){
		$order = wc_get_order( $id ); 
		$payment_id = $order->get_meta( \Clip::META_CLIP_RECEIPT_NO );
		$data_to_send = array(
			'reference' => array( 
				'type'	=> 'receipt',
				'id'	=> $payment_id
			),
			'amount'	=> floatval( number_format( floatval( $amount ) , 2 , '.', '') ),
			'reason'	=> $reason
		);
		try {
			$res = $this->api->post(
				'/refunds/',
				$data_to_send,
				array(
					'content-type' => self::JSON,
					'accept'       => 'application/vnd.com.payclip.v2+json',
					'x-api-key'    => $this->api_token,
					'Authorization' => $this->api_token,
				)
			);
		} catch ( \Exception $e ) {
			Helper::log_error( __FUNCTION__ . ': ' . $e->getMessage() );
			return array();
		}
		return $this->handle_response( $res, __FUNCTION__ );
	}

	/**
	 * Handle Response
	 *
	 * @param array  $response Response data.
	 * @param string $function_name Function function is calling from.
	 *
	 * @return array
	 */
	protected function handle_response(
		$response = array(),
		string $function_name = ''
	) {
		if ( 'request_first_deposit' === $function_name ) {
			return ( isset( $response['status'] ) && 'CHECKOUT_CREATED' === $response['status'] );
		}
		return $response;
	}

}
