<?php
/**
 * Template Page Onboarding
 *
 * @package  Ecomerciar\Clip\Templates
 */

$settings_url     = $args['settings_url'];
$woo_settings_url = $args['woo_settings_url'];
?>
<h3>
	<?php esc_html_e( 'If you do not see the panel correctly, enter the following link:', 'clip' ); ?> 
	<a href='<?php echo esc_url( $woo_settings_url ); ?>'> 
		<?php esc_html_e( 'WooCommerce > Settings > Payments > Clip', 'clip' ); ?> 
	</a>
</h3>
<iframe id="clip" src="<?php echo esc_url( $settings_url ); ?>" title="OnBoarding"></iframe>
<style>
	.notice{
	display:none;
	}

	#wpwrap{
		background-color: white;
	}
	iframe#clip {
		height: 100%;
		min-height: 95vh;
		width: 100%;
		box-sizing: border-box;
	}
</style>
<script>
	jQuery(document).ready( function( $ ){
		window.addEventListener('message', function(event) {
			var data = JSON.parse(event.data);

			var dataToSend = {
				action: 'clip_save_settings',
				apiKey : data.api_key,
				apiSecret: data.api_secret,
				wpClipNonce: data.wp_clip_nonce
			};

			$.post(
				data.url + "/wp-admin/admin-ajax.php",
				dataToSend,
				function (response) {
					if (response.success) {
						console.log( response )
					} else {
						console.log( 'onFailurePost' )
						console.log( response.data )
					}
				}
			);

		});

		$("#clip").ready( function(){
			if( $("#clip")[0].title == 'OnBoarding' ){
				console.log("ok");
			} else {
				console.log("error");
			}
		});		
	});
</script>
