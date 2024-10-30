<?php
/**
 * Class PostCheckout
 *
 * @package  Ecomerciar\Clip\Gateway\PostCheckout
 */

namespace Ecomerciar\Clip\Gateway;

use Ecomerciar\Clip\Helper\Helper;
use Ecomerciar\Clip\Sdk\ClipSdk;
use \WC_Payment_Gateway;
use Ecomerciar\Clip\Gateway\WC_Clip;

defined( 'ABSPATH' ) || exit();
/**
 * Post Checkout Page Controller
 */
class PostCheckout {

	/**
	 * Run Action
	 *
	 * @param int $order_id ID for WC Order.
	 *
	 * @return bool
	 */
	public static function render( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( \Clip::GATEWAY_ID !== $order->get_payment_method() ) {
			return false;
		}

		?>
		<div id="alert_text"></div>
		<span class="cta-clip-post-checkout" style="font-size:110%">
		<?php echo esc_html__( 'Click the button to complete the purchase', 'clip' ); ?>
		<span>
		<div class="checkout-top-div-container post-checkout">
		<a id="clip-cta" class="button btn btn-primary">
			<span>
				<?php echo esc_html__( 'Pay with ', 'clip' ); ?> 
			</span>
			<img src="<?php echo esc_url( Helper::get_assets_folder_url() ); ?>/img/logotype_clip_primary.svg" alt="<?php echo esc_attr__( 'Clip', 'clip' ); ?>">
		</a>
		<a id="select-gateway" class="button btn btn-secondary" href="<?php echo esc_url( $order->get_checkout_payment_url( false ) ); ?>">
			<span>
				<?php echo esc_html__( 'Select another payment method ', 'clip' ); ?> 
			</span>			
		</a>
		</div>		
		<style>			
			#clip-cta{
				min-width: 225px;
				background: #bdbdbd;
				border-radius: 5px;
				color: white;		
				text-align:center;			
			}
			#clip-cta *{
				display: inline-block;
			}
			#clip-cta img{
				max-height: 35px;				
				vertical-align:middle;
				margin: -5px;
				margin-left: 6px;
			}

			.clip-container-spinner img{
				animation: rotation 1s infinite linear;
			}
			@keyframes rotation {
				from {
					transform: rotate(0deg);
				}
				to {
					transform: rotate(359deg);
				}
			}
			#clip-container{
				z-index: 9999 !important;
			}
		</style>
		<?php
			$clip_cta_flag = isset( $_GET['clip_cta'] ) ? 'true' : 'false';
		if ( ! isset( $_GET['clip_nonce'] ) ) {
			$clip_cta_flag = 'false';
		} else {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['clip_nonce'] ) ), \Clip::GATEWAY_ID ) ) {
				$clip_cta_flag = 'false';
			}
		}
		?>
		<script type="text/javascript">
			var wc_clip_settings = {
				action: "clip_request_deposit_action",
				ajax_url : "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>",
				order_id : "<?php echo esc_html( $order_id ); ?>",
				spinner_id : 'clip-container-spinner',
				spinner_url : '<?php echo esc_url( Helper::get_assets_folder_url() ); ?>/img/loading-spinner.png',
				ajax_nonce : "<?php echo esc_attr( wp_create_nonce( \Clip::GATEWAY_ID ) ); ?>", 
				//phpcs:ignore WordPress.Security.NonceVerification.Recommended
				clip_cta_flag: <?php echo esc_html( $clip_cta_flag ); ?>, 
			}
		</script>
		<?php

		wp_enqueue_script( 'clip-gateway' );

		return true;
	}
}
