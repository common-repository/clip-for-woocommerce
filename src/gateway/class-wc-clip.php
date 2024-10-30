<?php
/**
 * Class WC_Clip
 *
 * @package  Ecomerciar\Clip\Gateway\WC_Clip
 */

namespace Ecomerciar\Clip\Gateway;

use Ecomerciar\Clip\Helper\Helper;
use Ecomerciar\Clip\Sdk\ClipSdk;

defined( 'ABSPATH' ) || class_exists( '\WC_Payment_Gateway' ) || exit();

/**
 * Main Class Clip Payment.
 */
class WC_Clip extends \WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = \Clip::GATEWAY_ID;
		$this->has_fields         = false;
		$this->method_title       = __( 'Clip', 'clip' );
		$this->method_description = __( 'Accept payments using Clip.', 'clip' );

		// Define user set variables.
		$this->title        = __( 'Clip', 'clip' );
		$this->instructions = $this->get_option(
			$this->description,
			$this->method_description
		);
		$this->icon         = Helper::get_assets_folder_url() . '/img/logotype_clip_primary.svg';

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
		$this->supports[] = 'products';
		$this->supports[] = 'refunds';

		$currentLanguage = get_bloginfo( 'language' );
		$language_slug   = 'english';

		if ( 0 === strpos( $currentLanguage, 'es' ) ) {
			$language_slug = 'spanish';
		}

		$this->banner_enabled = $this->get_option( 'wc_clip_banner_enabled' );

		switch ( $this->get_option( 'wc_clip_payment_options' ) ) {
			case 'CASH':
				$this->banner_clip = esc_url( Helper::get_assets_folder_url() . '/img/cash_' . esc_attr( $language_slug ) . '.svg' );
				$this->title       = __( 'Pay with cash', 'clip' );
				break;
			case 'CARD':
				$this->banner_clip = esc_url( Helper::get_assets_folder_url() . '/img/cards_' . esc_attr( $language_slug ) . '.svg' );
				$this->title       = __( 'Pay with card', 'clip' );
				break;
			default:
				$this->banner_clip = esc_url( Helper::get_assets_folder_url() . '/img/cards_cash_' . esc_attr( $language_slug ) . '.svg' );
				$this->title       = __( 'Pay with card or cash', 'clip' );
				break;
		}

		$this->description     = __( 'The No. 1 payment platform in Mexico', 'clip' );
		$this->payment_options = $this->get_option( 'wc_clip_payment_options' );
		$this->log_enabled     = $this->get_option( 'wc_clip_log_enabled' );
		$this->api_key         = $this->get_option( 'wc_clip_api_key' );
		$this->api_secret      = $this->get_option( 'wc_clip_api_secret' );
		$this->sdk             = new ClipSdk( $this->api_key, $this->api_secret );

		global $current_section;
		if ( \Clip::GATEWAY_ID === $current_section ) {

			$this->enqueue_settings_js();

		}

		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);
		add_action(
			'woocommerce_thankyou_' . $this->id,
			array(
				$this,
				'thankyou_page',
			)
		);

	}

	/**
	 * Add fields to pre-select method of payment
	 */
	public function payment_fields() {
		?>
		<style>
			.banner_clip {
				width: 600px !important;
				max-height: 600px !important;
			}
			.p_clip {
				font-size: 15px;
			}
			.woocommerce .wc_payment_method.payment_method_wc_clip label img{
				max-width: 25px;
			}
		</style> 			
		<fieldset>
			<legend>
				<p class="p_clip"><?php echo esc_html( $this->description ); ?> </p>
			</legend>
			<?php if ($this->banner_enabled === 'yes') : ?>
				<img class="banner_clip" src="<?php echo esc_url( $this->banner_clip ); ?>" alt="">
			<?php endif; ?>
		</fieldset>
		<?php
	}


	/**
	 * Output for the order received page.
	 *
	 * @param string $order_id Order Id.
	 */
	public function thankyou_page( $order_id ) {
		// Nothing to add, but required to avoid Warnings.

		Helper::handle_payment( $order_id );
	}

	/**
	 * Enqueue_settings_js
	 */
	private function enqueue_settings_js() {
		?>
		<style>
			.logotype_clip {
				width: 30px;
				height: auto;
				position: relative;
				bottom: -8px;
				border-right: 10px solid transparent;
			}
		</style>
		<?php
		wc_enqueue_js(
			'var url = "' . esc_url( Helper::get_assets_folder_url() . '/img/logotype_clip_primary.svg' ) . '";
			jQuery(document).ready(
				function( $ ) {
					$(".logotype_clip").remove();
					var image = new Image();
					image.src = url;
					image.classList.add("logotype_clip");
					$("h2", $(".wrap.woocommerce")).first().prepend(image);
				});'
		);
	}

	/**
	 * Initialize Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = include 'settings.php';
	}

	/**
	 * Process the payment and return the result
	 *
	 * @param int $order_id ID of Woo Order.
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$payment_nonce = wp_create_nonce( \Clip::GATEWAY_ID );
		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => add_query_arg( 'clip_nonce', $payment_nonce, add_query_arg( 'clip_cta', true, $order->get_checkout_payment_url( true ) ) ),
		);
	}


	/**
	 * Set if Clip must be available or not
	 *
	 * @param Array $available_gateways Array of Available Gateways.
	 *
	 * @return Array
	 */
	public static function available_payment_method( $available_gateways ) {
		if ( ! WC()->customer ) {
			return $available_gateways;
		}

		if ( 'MXN' !== Helper::get_currency() && isset( $available_gateways[ \Clip::GATEWAY_ID ] ) ) {
			unset( $available_gateways[ \Clip::GATEWAY_ID ] );
		}

		return $available_gateways;
	}

	/**
	 * Process Refunds
	 * 
	 * @param int $order_id Order to Refund.
	 * @param float $amount Amount to Refund.
	 * @param string $reason
	 * 
	 * @return bool
	 */
	public function process_refund( $order_id, $amount = NULL, $reason = '' ) {      

		if ( empty( $order_id ) ) return false;

		if ( empty( $amount ) ) {
			return new \WP_Error( 'wc-order', sprintf( __( 'Clip: Skipping ZERO value refund for Order ID %s.', 'clip'), $order_id ) );						
		}
		
        $order = wc_get_order($order_id);
        $order_data = $order->get_data();

        $response = $this->sdk->request_refund( $order_id, $amount, $reason );
		if ( isset( $response[ 'code_message' ] ) ) {
			$message = "";
			if ( 'AI1801' === $response[ 'code_message' ] ) {
				$message = __( 'Clip: The refund amount is greater that the original.' ) ;     
			}
			if ( 'AI1802' === $response[ 'code_message' ] ) {
				$message = __( 'Clip: The refund amount, plus previous refunds, is greater than the original amount.' ) ;     
			}
			if ( 'AI1803' === $response[ 'code_message' ] ) {
				$message = __( 'Clip: The refund date has expired.', 'clip'  ) ;     
			}
			if ( 'AI1804' === $response[ 'code_message' ] ) {
				$message = __( 'Clip: Refund declined.', 'clip'  ) ;     
			}
			if ( 'AI1805' === $response[ 'code_message' ] ) {
				$message = __( 'Clip: Refunds are disabled.', 'clip'  ) ;     
			}
			if ( 'AI1806' === $response[ 'code_message' ] ) {
				$message = __( 'Clip: Refund is disabled for payments with MSI and MCI.', 'clip'  ) ;     
			}
			if ( 'AI1807' === $response[ 'code_message' ] ) {
				$message = __( 'Clip: Refund in process for this transaction. Please try again later.', 'clip'  ) ;     
			}
			if ( 'AI1400' === $response[ 'code_message' ] ) {
				$message = __( 'Clip: Insufficient funds to make the refund.', 'clip'  ) ;     
			}
			return new \WP_Error( 'wc-order', $message );									
		} elseif( ! isset( $response[ 'id' ] ) ) {
			return new \WP_Error( 'wc-order', __( 'Clip: Unauthorized.', 'clip'  ) );		
		} else { 
			$order->add_order_note( sprintf( __( 'Clip: Refund requested. Id: %s .', 'clip' ), $response[ 'id' ] ) );           
		}
		$order->save(); 		
 
     	return true;

    }

}