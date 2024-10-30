<?php
/**
 * Plugin Name: Clip for WooCommerce
 * Description: Payment gateway for WooCommerce
 * Version: 1.1.3
 * Requires PHP: 7.0
 * Author: Clip
 * Author URI: https://www.clip.mx/
 * Text Domain: clip
 * WC requires at least: 4
 * WC tested up to: 5.0
 *
 * @package Ecomerciar\Clip\Clip
 */

use Ecomerciar\Clip\Helper\Helper;
use Ecomerciar\Clip\Sdk\ClipSdk;
use Ecomerciar\Clip\Blocks\ClipBlocks;

defined( 'ABSPATH' ) || exit();

add_action( 'plugins_loaded', array( 'Clip', 'init' ) );
add_action( 'activated_plugin', array( 'Clip', 'activation' ) );
add_action( 'deactivated_plugin', array( 'Clip', 'deactivation' ) );


if ( ! class_exists( 'Clip' ) ) {


/**
 * Plugin's base Class
 */
class Clip {

	const VERSION                  = '1.1.3';
	const PLUGIN_NAME              = 'Clip';
	const MAIN_FILE                = __FILE__;
	const MAIN_DIR                 = __DIR__;
	const GATEWAY_ID               = 'wc_clip';
	const META_ORDER_PAYMENT_ID    = '_CLIP_PAYMENT_ID';
	const META_CLIP_PAYMENT_STATUS = '_CLIP_PAYMENT_STATUS';
	const META_CLIP_RECEIPT_NO     = '_CLIP_RECEIPT_NO';
	const ONBOARDING_URL           = 'https://clip-onboarding.conexa.ai';
	//const ONBOARDING_URL = 'https://clip-woocommerce-stage.conexa.ai';
	const API_BASE_URL = 'https://api-gw.payclip.com';
	//const API_BASE_URL = 'https://stageapi-gw.payclip.com';

	/**
	 * Checks system requirements
	 *
	 * @return bool
	 */
	public static function check_system() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$system = self::check_components();

		if ( $system['flag'] ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			echo '<div class="notice notice-error is-dismissible">'
			. '<p>' .
				sprintf(
					/* translators: %s: System Flag */
					esc_html__(
						'<strong>%1$s</strong> Requiere al menos %2$s versión %3$s o superior.',
						'clip'
					),
					esc_html( self::PLUGIN_NAME ),
					esc_html( $system['flag'] ),
					esc_html( $system['version'] )
				) .
				'</p>'
			. '</div>';
			return false;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			echo '<div class="notice notice-error is-dismissible">'
			. '<p>' .
				sprintf(
					/* translators: %s: System Flag */
					esc_html__(
						'WooCommerce debe estar activo antes de usar <strong>%s</strong>',
						'clip'
					),
					esc_html( self::PLUGIN_NAME )
				) .
				'</p>'
			. '</div>';
			return false;
		}
		return true;
	}

	/**
	 * Check the components required for the plugin to work (PHP, WordPress and WooCommerce)
	 *
	 * @return array
	 */
	private static function check_components() {
		global $wp_version;
		$flag    = false;
		$version = false;

		if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
			$flag    = 'PHP';
			$version = '7.0';
		} elseif ( version_compare( $wp_version, '5.4', '<' ) ) {
			$flag    = 'WordPress';
			$version = '5.4';
		} elseif (
			! defined( 'WC_VERSION' ) ||
			version_compare( WC_VERSION, '3.8.0', '<' )
		) {
			$flag    = 'WooCommerce';
			$version = '3.8.0';
		}

		return array(
			'flag'    => $flag,
			'version' => $version,
		);
	}

	/**
	 * Inits our plugin
	 *
	 * @return bool
	 */
	public static function init() {
		if ( ! self::check_system() ) {
			return false;
		}
		// phpcs:disable
        spl_autoload_register(
			function ( $class ) {
				// Plugin base Namespace.
				if ( strpos( $class, 'Clip' ) === false ) {
					return;
				}
				$class     = str_replace( '\\', '/', $class );
				$parts     = explode( '/', $class );
				$classname = array_pop( $parts );
                if (  $classname === 'Clip') {
					return;
				}
				$filename = $classname;
                $filename = str_replace( 'Clip', 'Clip', $filename );
				$filename = str_replace( 'WooCommerce', 'Woocommerce', $filename );
				$filename = str_replace( 'WC_', 'Wc', $filename );
				$filename = str_replace( 'WC', 'Wc', $filename );
				$filename = preg_replace( '/([A-Z])/', '-$1', $filename );
				$filename = 'class' . $filename;
				$filename = strtolower( $filename );
				$folder   = strtolower( array_pop( $parts ) );				
				require_once plugin_dir_path( __FILE__ ) . 'src/' . $folder . '/' . $filename . '.php';
			}
		);
		// phpcs:enable  
		include_once __DIR__ . '/hooks.php';
		Helper::init();
		self::load_textdomain();
		return true;
	}

	/**
	 * Load Text Domain for Clip
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'clip', false, basename( dirname( __FILE__ ) ) . '/i18n/languages' );
	}

	/**
	 * Create a link to the settings page, in the plugins page
	 *
	 * @param array $links Links for plugin.
	 * @return array
	 */
	public static function create_settings_link( array $links ) {
		$link =
			'<a href="' .
			esc_url(
				get_admin_url(
					null,
					'admin.php?page=wc-settings&tab=checkout&section=wc_clip'
				)
			) .
			'">' .
			__( 'Settings', 'clip' ) .
			'</a>';
		array_unshift( $links, $link );
		return $links;
	}

	/**
	 * Adds our payment method to WooCommerce
	 *
	 * @param array $gateways Gateways setted on Woo.
	 * @return array
	 */
	public static function add_payment_method( $gateways ) {
		$gateways[] = '\Ecomerciar\Clip\Gateway\WC_Clip';
		return $gateways;
	}

	/**
	 * Activation Plugin Actions
	 *
	 * @param string $plugin Plugin Name.
	 * @return bool
	 */
	public static function activation( $plugin ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}
		self::redirect_to_onboarding_on_activation( $plugin );
	}

	/**
	 * Redirects to onboarding page on register_activation_hook
	 *
	 * @param string $plugin Plugin Name.
	 * @return bool
	 */
	public static function redirect_to_onboarding_on_activation( $plugin ) {
		if ( plugin_basename( self::MAIN_FILE ) === $plugin ) {
			wp_safe_redirect(
				admin_url(
					esc_url(
						'admin.php?page=wc-clip-onboarding'
					)
				)
			);
			exit();
		}
		return true;
	}


	/**
	 * Registers all scripts to be loaded laters
	 *
	 * @return void
	 */
	public static function register_front_scripts() {
		wp_register_script(
			'clip-gateway',
			Helper::get_assets_folder_url() . '/js/gateway.js',
			array( 'jquery' ),
			'1.0.6',
			true
		);
	}

	/**
	 * Deactivation Plugin Actions
	 *
	 * @param int $plugin comment about this variable.
	 * @return void
	 */
	public static function deactivation( $plugin ) {
		delete_option( 'clip_first_onboarding' );
	}



}

	// --- HPOS WooCommerce Compatibility
	add_action( 'before_woocommerce_init', function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} );

	/**
	 * Custom function to declare compatibility with cart_checkout_blocks feature 
	*/
	function clip_declare_cart_checkout_blocks_compatibility() {
		// Check if the required class exists
		if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
			// Declare compatibility for 'cart_checkout_blocks'
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
		}
	}
	// Hook the custom function to the 'before_woocommerce_init' action
	add_action('before_woocommerce_init', 'clip_declare_cart_checkout_blocks_compatibility');

	// Hook the custom function to the 'woocommerce_blocks_loaded' action
	add_action( 'woocommerce_blocks_loaded', 'clip_register_order_approval_payment_method_type' );

	/**
	 * Custom function to register a payment method type
	 *
	 */
	function clip_register_order_approval_payment_method_type() {
		// Check if the required class exists
		if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			return;
		}

		// Hook the registration function to the 'woocommerce_blocks_payment_method_type_registration' action
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				// Register an instance of My_Custom_Gateway_Blocks
				$payment_method_registry->register( new ClipBlocks );
			}
		);
	}


}

