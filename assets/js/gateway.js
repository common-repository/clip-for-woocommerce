/**
 * Generate Clip Payment Link
 *
 * @package  Ecomerciar\Clip\Assets\Js
 */

'use strict'
jQuery( document ).ready(
	function () {
		(function ($, settings) {

			var showSpinner = function () {
				var htmlString =
				"<div class='" +
				settings.spinner_id +
				"'><div class='" +
				settings.spinner_id +
				"-center'><span class='spinner is-active'><img src='" +
				settings.spinner_url +
				"'></span></div></div>"
				htmlString     =
				htmlString +
				'<style> .' +
				settings.spinner_id +
				'{z-index:99999;width: 100%;height: 100%;position: fixed;top: 0;left: 0;opacity: 0.4;background-color:#ccc;text-align: center; } .' +
				settings.spinner_id +
				'-center{position: absolute;top: 50%; left: 50%; transform: translate(-50%, -50%); } .' +
				settings.spinner_id +
				' .spinner{ vertical-align: middle; }' +
				settings.spinner_id +
				' * img{z-index:99999;}</style>'

				$( 'body' ).prepend( htmlString )
			}

			var removeSpinner = function () {
				jQuery( '.' + settings.spinner_id ).remove()
			}

			var cta = function () {
				showSpinner()

				var dataToSend = {
					action: settings.action,
					order_id: settings.order_id,
					nonce: settings.ajax_nonce,
				}

				$.post(
					settings.ajax_url,
					dataToSend,
					function (response) {

						if (response.success) {

							console.log( 'clip-cta' )
							console.log( response )
							window.location.href = response.data;
							removeSpinner()
						} else {
							console.log( 'onFailurePost' )
							console.log( response.data )

							$( "#clip-cta" ).addClass( "disabled" );
							$( "#alert_text" ).addClass( "woocommerce-info" );
							$( '.woocommerce-info' ).html( response.data );
							removeSpinner()
						}
					}
				)

			}
			$( '#clip-cta' ).click(
				function () {
					cta()
				}
			)

			if (settings.clip_cta_flag) {
				cta()
			}
		})( jQuery, wc_clip_settings )
	}
)
